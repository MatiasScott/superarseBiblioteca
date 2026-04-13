<?php

class RequestSecurityHelper
{
    public static function enforceSameOriginJson(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $source = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';

        if ($source === '') {
            self::reject('Solicitud bloqueada por validacion CSRF');
        }

        $sourceParts = parse_url($source);
        $requestScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $requestHost = $_SERVER['HTTP_HOST'] ?? '';
        $requestParts = parse_url($requestScheme . '://' . $requestHost);

        $sameHost = ($sourceParts['host'] ?? null) === ($requestParts['host'] ?? null);
        $sameScheme = ($sourceParts['scheme'] ?? null) === ($requestParts['scheme'] ?? null);
        $samePort = ($sourceParts['port'] ?? null) === ($requestParts['port'] ?? null);

        if (!$sameHost || !$sameScheme || !$samePort) {
            self::reject('Origen no permitido');
        }
    }

    private static function reject(string $message): void
    {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}