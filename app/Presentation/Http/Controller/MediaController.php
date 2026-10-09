<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Ports\Outbound\FileStorage;
use Symfony\Component\HttpFoundation\Response;

final class MediaController
{
    public function __construct(
        private readonly FileStorage $fileStorage
    ) {
    }

    public function show(string $key): Response
    {
        $binary = $this->fileStorage->get($key);

        if ($binary === null) {
            // E-15: 404 vacío con Content-Length: 0
            return response('', 404, ['Content-Length' => '0']);
        }

        $extension = strtolower(pathinfo($key, PATHINFO_EXTENSION));
        $contentType = match ($extension) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        return response($binary, 200, [
            'Content-Type' => $contentType,
            'Content-Length' => (string) strlen($binary),
        ]);
    }
}

