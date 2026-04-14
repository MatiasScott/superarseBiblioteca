<?php

class UploadHelper
{
    /* =====================================================================
       PORTADAS (imágenes)
    ===================================================================== */

    public static function storeImage(string $fieldName, ?string $currentPath = null): array
    {
        if (!isset($_FILES[$fieldName])) {
            return ['success' => true, 'path' => $currentPath ?: null, 'uploaded' => false];
        }

        $file  = $_FILES[$fieldName];
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            return ['success' => true, 'path' => $currentPath ?: null, 'uploaded' => false];
        }

        if ($error !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'No se pudo subir la imagen.'];
        }

        $tmpPath = $file['tmp_name'] ?? '';
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            return ['success' => false, 'message' => 'Archivo de imagen invalido.'];
        }

        $maxBytes = 5 * 1024 * 1024;
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            return ['success' => false, 'message' => 'La imagen debe pesar maximo 5 MB.'];
        }

        $mime    = self::detectMimeType($tmpPath);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];

        if (!isset($allowed[$mime])) {
            return ['success' => false, 'message' => 'Formato de imagen no permitido. Use JPG, PNG, WEBP o GIF.'];
        }

        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/portadas';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            return ['success' => false, 'message' => 'No se pudo preparar la carpeta de imagenes.'];
        }

        $extension   = $allowed[$mime];
        $filename    = 'portada_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        $destination = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($tmpPath, $destination)) {
            return ['success' => false, 'message' => 'No se pudo guardar la imagen en el servidor.'];
        }

        // Redimensionar y comprimir (max 600×900 px)
        if ($mime !== 'image/gif') {
            self::resizeAndCompress($destination, $mime);
        }

        self::removePreviousLocalUpload($currentPath, 'portadas');

        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';

        return [
            'success'  => true,
            'path'     => $baseUrl . '/uploads/portadas/' . $filename,
            'uploaded' => true,
        ];
    }

    /* =====================================================================
       ARCHIVOS PDF
    ===================================================================== */

    public static function storePdf(string $fieldName, ?string $currentPath = null): array
    {
        if (!isset($_FILES[$fieldName])) {
            return ['success' => true, 'path' => $currentPath ?: null, 'uploaded' => false];
        }

        $file  = $_FILES[$fieldName];
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            return ['success' => true, 'path' => $currentPath ?: null, 'uploaded' => false];
        }

        if ($error !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'No se pudo subir el archivo PDF.'];
        }

        $tmpPath = $file['tmp_name'] ?? '';
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            return ['success' => false, 'message' => 'Archivo PDF invalido.'];
        }

        $maxBytes = 50 * 1024 * 1024; // 50 MB
        $size     = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            return ['success' => false, 'message' => 'El PDF debe pesar maximo 50 MB.'];
        }

        $mime = self::detectMimeType($tmpPath);
        if ($mime !== 'application/pdf') {
            return ['success' => false, 'message' => 'Solo se permiten archivos PDF.'];
        }

        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/archivos';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            return ['success' => false, 'message' => 'No se pudo preparar la carpeta de archivos.'];
        }

        $filename    = 'archivo_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.pdf';
        $destination = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($tmpPath, $destination)) {
            return ['success' => false, 'message' => 'No se pudo guardar el PDF en el servidor.'];
        }

        self::removePreviousLocalUpload($currentPath, 'archivos');

        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';

        return [
            'success'  => true,
            'path'     => $baseUrl . '/uploads/archivos/' . $filename,
            'uploaded' => true,
        ];
    }

    /* =====================================================================
       UTILIDADES PRIVADAS
    ===================================================================== */

    /**
     * Redimensiona (máx 600×900 px) y comprime la imagen en el disco.
     * Requiere extensión GD. Si GD no está disponible, no hace nada.
     */
    private static function resizeAndCompress(string $path, string $mime): void
    {
        if (!extension_loaded('gd')) {
            return;
        }

        $info = @getimagesize($path);
        if (!$info || $info[0] <= 0 || $info[1] <= 0) {
            return;
        }

        $origW = $info[0];
        $origH = $info[1];

        $maxW  = 600;
        $maxH  = 900;
        $scale = min(1.0, $maxW / $origW, $maxH / $origH);

        $newW = max(1, (int) round($origW * $scale));
        $newH = max(1, (int) round($origH * $scale));

        $src = null;
        switch ($mime) {
            case 'image/jpeg':
                $src = @imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $src = @imagecreatefrompng($path);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $src = @imagecreatefromwebp($path);
                }
                break;
        }

        if (!$src) {
            return;
        }

        $dst = imagecreatetruecolor($newW, $newH);
        if (!$dst) {
            imagedestroy($src);
            return;
        }

        // Preservar transparencia en PNG
        if ($mime === 'image/png') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            if ($transparent !== false) {
                imagefill($dst, 0, 0, $transparent);
            }
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($dst, $path, 82);
                break;
            case 'image/png':
                imagepng($dst, $path, 7);
                break;
            case 'image/webp':
                if (function_exists('imagewebp')) {
                    imagewebp($dst, $path, 82);
                }
                break;
        }

        imagedestroy($src);
        imagedestroy($dst);
    }

    private static function detectMimeType(string $tmpPath): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mime = finfo_file($finfo, $tmpPath) ?: '';
                finfo_close($finfo);
                if ($mime !== '') {
                    return $mime;
                }
            }
        }

        if (function_exists('mime_content_type')) {
            return mime_content_type($tmpPath) ?: '';
        }

        return '';
    }

    private static function removePreviousLocalUpload(?string $currentPath, string $folder): void
    {
        if (!$currentPath || strpos($currentPath, '/uploads/' . $folder . '/') === false) {
            return;
        }

        $filename = basename(parse_url($currentPath, PHP_URL_PATH) ?? '');
        if ($filename === '') {
            return;
        }

        $fullPath = dirname(__DIR__, 2) . '/public/uploads/' . $folder . '/' . $filename;
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
