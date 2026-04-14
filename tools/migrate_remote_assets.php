<?php

declare(strict_types=1);

/**
 * Migra enlaces remotos de portada/PDF a almacenamiento local.
 *
 * Uso:
 *   php tools/migrate_remote_assets.php --base-url=/biblioteca_superarse/public
 *   php tools/migrate_remote_assets.php --tipo=tesis --dry-run
 *   php tools/migrate_remote_assets.php --limit=200
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script solo puede ejecutarse en CLI.\n");
    exit(1);
}

require_once dirname(__DIR__) . '/app/Models/Database.php';

$options = getopt('', [
    'base-url::',
    'tipo::',
    'limit::',
    'dry-run',
    'timeout::',
]);

$baseUrl = isset($options['base-url']) ? trim((string) $options['base-url']) : '/biblioteca_superarse/public';
$baseUrl = normalizeBaseUrlArg(rtrim($baseUrl, '/'));

$tipoInput = strtolower(trim((string) ($options['tipo'] ?? 'all')));
$limit = isset($options['limit']) ? max(1, (int) $options['limit']) : 0;
$dryRun = array_key_exists('dry-run', $options);
$timeout = isset($options['timeout']) ? max(5, (int) $options['timeout']) : 25;

$tipoMap = [
    'all' => [1, 2, 3],
    'libros' => [1],
    'tesis' => [2],
    'publicaciones' => [3],
];

if (!isset($tipoMap[$tipoInput])) {
    fwrite(STDERR, "Valor inválido para --tipo. Usa: all, libros, tesis, publicaciones\n");
    exit(1);
}

$rootDir = dirname(__DIR__);
$portadasDir = $rootDir . '/public/uploads/portadas';
$archivosDir = $rootDir . '/public/uploads/archivos';

ensureDir($portadasDir);
ensureDir($archivosDir);

$db = Database::getConnection();
$tipoIds = $tipoMap[$tipoInput];

$inClause = implode(',', array_fill(0, count($tipoIds), '?'));
$sql = "SELECT id, tipo_id, codigo, portada, link_archivo
        FROM items_biblioteca
        WHERE deleted_at IS NULL
          AND tipo_id IN ($inClause)
        ORDER BY id ASC";

if ($limit > 0) {
    $sql .= ' LIMIT ' . $limit;
}

$stmt = $db->prepare($sql);
$stmt->execute($tipoIds);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($rows);
$updated = 0;
$skipped = 0;
$errors = 0;

echo "Iniciando migración de activos remotos...\n";
echo "Tipo: {$tipoInput} | Registros: {$total} | Dry-run: " . ($dryRun ? 'SI' : 'NO') . "\n\n";

foreach ($rows as $row) {
    $id = (int) $row['id'];
    $tipoId = (int) $row['tipo_id'];
    $codigo = sanitizeCodeBase((string) ($row['codigo'] ?? ''), $id);
    $oldPortada = trim((string) ($row['portada'] ?? ''));
    $oldPdf = trim((string) ($row['link_archivo'] ?? ''));

    $newPortada = $oldPortada;
    $newPdf = $oldPdf;
    $changed = false;

    try {
        if (shouldDownload($oldPortada)) {
            $imgResult = downloadAndStore(
                $oldPortada,
                $portadasDir,
                'portada',
                $id,
                $tipoId,
                $codigo,
                $timeout,
                !$dryRun
            );

            if ($imgResult['success']) {
                $newPortada = buildPublicPath($baseUrl, '/uploads/portadas/' . $imgResult['filename']);
                $changed = true;
                echo "[OK] #{$id} portada migrada -> {$newPortada}\n";
            } else {
                $errors++;
                echo "[ERR] #{$id} portada: {$imgResult['message']}\n";
            }
        }

        if (shouldDownload($oldPdf)) {
            $pdfResult = downloadAndStore(
                $oldPdf,
                $archivosDir,
                'pdf',
                $id,
                $tipoId,
                $codigo,
                $timeout,
                !$dryRun
            );

            if ($pdfResult['success']) {
                $newPdf = buildPublicPath($baseUrl, '/uploads/archivos/' . $pdfResult['filename']);
                $changed = true;
                echo "[OK] #{$id} pdf migrado -> {$newPdf}\n";
            } else {
                $errors++;
                echo "[ERR] #{$id} pdf: {$pdfResult['message']}\n";
            }
        }

        if ($changed) {
            if ($dryRun) {
                $updated++;
                echo "[DRY] #{$id} se actualizaría en BD.\n";
            } else {
                $up = $db->prepare('UPDATE items_biblioteca SET portada = ?, link_archivo = ? WHERE id = ?');
                $up->execute([$newPortada, $newPdf, $id]);
                $updated++;
                echo "[DB] #{$id} actualizado.\n";
            }
        } else {
            $skipped++;
        }
    } catch (Throwable $e) {
        $errors++;
        echo "[ERR] #{$id} excepción: {$e->getMessage()}\n";
    }
}

echo "\nResumen:\n";
echo "- Procesados: {$total}\n";
echo "- Actualizados: {$updated}\n";
echo "- Omitidos: {$skipped}\n";
echo "- Errores: {$errors}\n";

exit(0);

function ensureDir(string $dir): void
{
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException("No se pudo crear directorio: {$dir}");
    }
}

function shouldDownload(string $value): bool
{
    if ($value === '') {
        return false;
    }

    $value = trim($value);

    if (strpos($value, '/uploads/portadas/') !== false || strpos($value, '/uploads/archivos/') !== false) {
        return false;
    }

    return isRemoteUrl($value);
}

function isRemoteUrl(string $url): bool
{
    if (strpos($url, '//') === 0) {
        $url = 'https:' . $url;
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true);
}

function buildPublicPath(string $baseUrl, string $suffix): string
{
    if ($baseUrl === '') {
        return $suffix;
    }
    return $baseUrl . $suffix;
}

function normalizeBaseUrlArg(string $baseUrl): string
{
    // En Git Bash de Windows, "/algo" puede convertirse a
    // "C:/Program Files/Git/algo". Lo revertimos a "/algo".
    if (preg_match('#^[A-Za-z]:/Program Files/Git/(.+)$#', $baseUrl, $m) === 1) {
        return '/' . ltrim($m[1], '/');
    }

    return $baseUrl;
}

function downloadAndStore(string $url, string $targetDir, string $kind, int $itemId, int $tipoId, string $codeBase, int $timeout, bool $saveFile): array
{
    $normalizedUrl = normalizeUrl($url);
    if ($normalizedUrl === null) {
        return ['success' => false, 'message' => 'URL inválida'];
    }

    $download = httpDownload($normalizedUrl, $timeout);
    if (!$download['success']) {
        return ['success' => false, 'message' => $download['message']];
    }

    $bytes = $download['bytes'];
    if ($bytes === '' || strlen($bytes) === 0) {
        return ['success' => false, 'message' => 'Respuesta vacía'];
    }

    if ($kind === 'portada') {
        $mime = detectMimeFromBytes($bytes);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (!isset($allowed[$mime])) {
            return ['success' => false, 'message' => 'MIME de imagen no permitido: ' . ($mime ?: 'desconocido')];
        }

        $filename = buildFilenameFromCode($targetDir, $codeBase, $allowed[$mime], $itemId);
        $path = $targetDir . '/' . $filename;
        if ($saveFile) {
            file_put_contents($path, $bytes);

            if ($mime !== 'image/gif') {
                resizeAndCompress($path, $mime);
            }
        }

        return ['success' => true, 'filename' => $filename];
    }

    $mime = detectMimeFromBytes($bytes);
    if ($mime !== 'application/pdf' && !looksLikePdf($bytes)) {
        return ['success' => false, 'message' => 'El archivo descargado no parece PDF. MIME: ' . ($mime ?: 'desconocido')];
    }

    $filename = buildFilenameFromCode($targetDir, $codeBase, 'pdf', $itemId);
    $path = $targetDir . '/' . $filename;
    if ($saveFile) {
        file_put_contents($path, $bytes);
    }

    return ['success' => true, 'filename' => $filename];
}

function normalizeUrl(string $url): ?string
{
    $url = trim($url);
    if ($url === '') {
        return null;
    }

    if (strpos($url, '//') === 0) {
        $url = 'https:' . $url;
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return null;
    }

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    if (!in_array($scheme, ['http', 'https'], true)) {
        return null;
    }

    return $url;
}

function httpDownload(string $url, int $timeout): array
{
    if (function_exists('curl_init')) {
        $origin = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);
        $browserUA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36';

        $attempts = [
            [
                'verifyPeer' => true,
                'verifyHost' => 2,
                'userAgent' => 'BibliotecaMigrator/1.0',
                'headers' => [
                    'Accept: */*',
                ],
            ],
            [
                'verifyPeer' => true,
                'verifyHost' => 2,
                'userAgent' => $browserUA,
                'headers' => [
                    'Accept: application/pdf,application/octet-stream,*/*;q=0.8',
                    'Referer: ' . $origin . '/',
                    'Origin: ' . $origin,
                ],
            ],
            [
                'verifyPeer' => false,
                'verifyHost' => 0,
                'userAgent' => $browserUA,
                'headers' => [
                    'Accept: application/pdf,application/octet-stream,*/*;q=0.8',
                    'Referer: ' . $origin . '/',
                    'Origin: ' . $origin,
                ],
            ],
        ];

        $lastMessage = 'No se pudo descargar la URL';

        foreach ($attempts as $idx => $cfg) {
            $resp = curlDownloadOnce(
                $url,
                $timeout,
                $cfg['verifyPeer'],
                $cfg['verifyHost'],
                $cfg['userAgent'],
                $cfg['headers']
            );

            if ($resp['success']) {
                return ['success' => true, 'bytes' => $resp['bytes']];
            }

            $lastMessage = $resp['message'];

            // Si el servidor bloquea por estado HTTP, continuamos a siguiente intento.
            // Si falla por SSL o red, también probamos siguiente intento.
            if ($idx < count($attempts) - 1) {
                continue;
            }
        }

        return ['success' => false, 'message' => $lastMessage];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => $timeout,
            'header' => "User-Agent: BibliotecaMigrator/1.0\r\n",
            'follow_location' => 1,
            'max_redirects' => 5,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    if ($body === false) {
        return ['success' => false, 'message' => 'No se pudo descargar la URL'];
    }

    return ['success' => true, 'bytes' => $body];
}

function curlDownloadOnce(string $url, int $timeout, bool $verifyPeer, int $verifyHost, string $userAgent, array $headers): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_USERAGENT => $userAgent,
        CURLOPT_SSL_VERIFYPEER => $verifyPeer,
        CURLOPT_SSL_VERIFYHOST => $verifyHost,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        return ['success' => false, 'message' => 'cURL error: ' . $error];
    }

    if ($status < 200 || $status >= 300) {
        return ['success' => false, 'message' => 'HTTP status ' . $status];
    }

    return ['success' => true, 'bytes' => $body];
}

function detectMimeFromBytes(string $bytes): ?string
{
    if (!function_exists('finfo_open')) {
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) {
        return null;
    }

    $mime = finfo_buffer($finfo, $bytes) ?: null;
    finfo_close($finfo);

    return is_string($mime) ? strtolower($mime) : null;
}

function looksLikePdf(string $bytes): bool
{
    return strncmp($bytes, "%PDF-", 5) === 0;
}

function sanitizeCodeBase(string $codigo, int $itemId): string
{
    $codigo = trim($codigo);
    if ($codigo === '') {
        return 'item_' . $itemId;
    }

    $sanitized = preg_replace('/[^A-Za-z0-9_-]+/', '_', $codigo);
    $sanitized = trim((string) $sanitized, '_');

    if ($sanitized === '') {
        return 'item_' . $itemId;
    }

    return $sanitized;
}

function buildFilenameFromCode(string $targetDir, string $codeBase, string $ext, int $itemId): string
{
    $ext = strtolower(trim($ext));
    $candidate = $codeBase . '.' . $ext;

    if (!file_exists($targetDir . '/' . $candidate)) {
        return $candidate;
    }

    $candidate = $codeBase . '_i' . $itemId . '.' . $ext;
    if (!file_exists($targetDir . '/' . $candidate)) {
        return $candidate;
    }

    $n = 2;
    while (true) {
        $candidate = $codeBase . '_i' . $itemId . '_' . $n . '.' . $ext;
        if (!file_exists($targetDir . '/' . $candidate)) {
            return $candidate;
        }
        $n++;
    }
}

function resizeAndCompress(string $path, string $mime): void
{
    if (!extension_loaded('gd')) {
        return;
    }

    $info = @getimagesize($path);
    if (!$info || $info[0] <= 0 || $info[1] <= 0) {
        return;
    }

    $origW = (int) $info[0];
    $origH = (int) $info[1];

    $maxW = 600;
    $maxH = 900;
    $scale = min(1.0, $maxW / $origW, $maxH / $origH);

    $newW = max(1, (int) round($origW * $scale));
    $newH = max(1, (int) round($origH * $scale));

    $src = null;
    if ($mime === 'image/jpeg') {
        $src = @imagecreatefromjpeg($path);
    } elseif ($mime === 'image/png') {
        $src = @imagecreatefrompng($path);
    } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
        $src = @imagecreatefromwebp($path);
    }

    if (!$src) {
        return;
    }

    $dst = imagecreatetruecolor($newW, $newH);
    if (!$dst) {
        imagedestroy($src);
        return;
    }

    if ($mime === 'image/png') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        if ($transparent !== false) {
            imagefill($dst, 0, 0, $transparent);
        }
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

    if ($mime === 'image/jpeg') {
        imagejpeg($dst, $path, 82);
    } elseif ($mime === 'image/png') {
        imagepng($dst, $path, 7);
    } elseif ($mime === 'image/webp' && function_exists('imagewebp')) {
        imagewebp($dst, $path, 82);
    }

    imagedestroy($src);
    imagedestroy($dst);
}
