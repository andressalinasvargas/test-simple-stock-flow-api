<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\DTO\AuthResultView;
use App\Application\Ports\Outbound\TokenGenerator;
use App\Domain\Model\User;
use DateTimeImmutable;
use DateTimeZone;

final class JwtTokenGenerator implements TokenGenerator
{
    private string $secret;
    private int $ttlSeconds;

    public function __construct(?string $secret = null, int $ttlSeconds = 86400)
    {
        $this->secret = $secret ?? (string) env('JWT_SIGNING_KEY', 'simple-stock-flow-jwt-super-secret-key-minimum-32-bytes');
        $this->ttlSeconds = $ttlSeconds;
    }

    public function generate(User $user): AuthResultView
    {
        $now = time();
        $exp = $now + $this->ttlSeconds;

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'sub' => $user->getId(),
            'unique_name' => $user->getUsername()->getValue(),
            'role' => $user->getRole()->getValue(),
            'iat' => $now,
            'exp' => $exp,
        ];

        $b64Header = $this->base64UrlEncode((string) json_encode($header));
        $b64Payload = $this->base64UrlEncode((string) json_encode($payload));

        $signature = hash_hmac('sha256', "{$b64Header}.{$b64Payload}", $this->secret, true);
        $b64Signature = $this->base64UrlEncode($signature);

        $token = "{$b64Header}.{$b64Payload}.{$b64Signature}";
        $expiresAt = (new DateTimeImmutable("@{$exp}"))
            ->setTimezone(new DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s\Z');

        return new AuthResultView(
            $token,
            $expiresAt,
            $user->getUsername()->getValue(),
            $user->getRole()->getValue()
        );
    }

    public function validate(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$b64Header, $b64Payload, $b64Signature] = $parts;

        $expectedSig = hash_hmac('sha256', "{$b64Header}.{$b64Payload}", $this->secret, true);
        $expectedB64Sig = $this->base64UrlEncode($expectedSig);

        if (!hash_equals($expectedB64Sig, $b64Signature)) {
            return null;
        }

        $payloadJson = $this->base64UrlDecode($b64Payload);
        $payload = json_decode($payloadJson, true);
        if (!is_array($payload)) {
            return null;
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return [
            'sub' => (string) ($payload['sub'] ?? ''),
            'unique_name' => (string) ($payload['unique_name'] ?? ''),
            'username' => (string) ($payload['unique_name'] ?? $payload['username'] ?? ''),
            'role' => (string) ($payload['role'] ?? ''),
        ];
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padLen = 4 - $remainder;
            $data .= str_repeat('=', $padLen);
        }
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}

