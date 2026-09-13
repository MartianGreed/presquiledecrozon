<?php

namespace App\Infrastructure\BunnyCDN;

/**
 * @phpstan-type Error array{Message: string}
 * @phpstan-type Content array<FileObject>
 */
final class Reply
{
    /**
     * @param Error|Content $content
     */
    public function __construct(
        private readonly int $statusCode,
        private readonly array $content,
    ) {
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return Error|Content
     */
    public function getContent(): array
    {
        return $this->content;
    }
}
