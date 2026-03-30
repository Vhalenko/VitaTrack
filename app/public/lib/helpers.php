<?php
// backend/config/helpers.php

function setCORSHeaders(): void {
    header('Access-Control-Allow-Origin: http://localhost:5173'); // Vite dev server
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function jsonResponse(mixed $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function jsonError(string $message, int $status = 400): void {
    jsonResponse(['error' => $message], $status);
}

function getRequestBody(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

function generateToken(int $userId): string {
    $payload = base64_encode(json_encode([
        'user_id' => $userId,
        'exp'     => time() + (60 * 60 * 24 * 7), // 7 days
        'iat'     => time(),
    ]));
    $secret    = 'CHANGE_THIS_SECRET_KEY_IN_PRODUCTION';
    $signature = base64_encode(hash_hmac('sha256', $payload, $secret, true));
    return $payload . '.' . $signature;
}

function verifyToken(string $token): ?array {
    $secret = 'CHANGE_THIS_SECRET_KEY_IN_PRODUCTION';
    $parts  = explode('.', $token);
    if (count($parts) !== 2) return null;

    [$payload, $signature] = $parts;
    $expectedSig = base64_encode(hash_hmac('sha256', $payload, $secret, true));
    if (!hash_equals($expectedSig, $signature)) return null;

    $data = json_decode(base64_decode($payload), true);
    if (!$data || $data['exp'] < time()) return null;

    return $data;
}

function requireAuth(): array {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!str_starts_with($authHeader, 'Bearer ')) {
        jsonError('Unauthorized', 401);
    }
    $token = substr($authHeader, 7);
    $data  = verifyToken($token);
    if (!$data) {
        jsonError('Invalid or expired token', 401);
    }
    return $data;
}