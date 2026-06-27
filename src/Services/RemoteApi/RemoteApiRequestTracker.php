<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi;

class RemoteApiRequestTracker
{
    /** @var array<string, int> */
    private array $countsByUrl = [];

    private int $total = 0;

    public function reset(): void
    {
        $this->countsByUrl = [];
        $this->total = 0;
    }

    public function record(string $url): void
    {
        $normalized = $this->normalizeUrl($url);
        $this->countsByUrl[$normalized] = ($this->countsByUrl[$normalized] ?? 0) + 1;
        $this->total++;
    }

    public function total(): int
    {
        return $this->total;
    }

    /**
     * @return array<string, int>
     */
    public function countsByUrl(): array
    {
        return $this->countsByUrl;
    }

    /**
     * @return array{total: int, by_url: array<string, int>}
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'by_url' => $this->countsByUrl,
        ];
    }

    /**
     * @return array{total: int, by_url: array<string, int>}
     */
    public function flush(): array
    {
        $stats = $this->toArray();
        $this->reset();

        return $stats;
    }

    private function normalizeUrl(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false) {
            return $url;
        }

        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'] ?? 'localhost';
        $path = rtrim($parts['path'] ?? '/', '/');

        return sprintf('%s://%s%s', $scheme, $host, $path === '' ? '/' : $path);
    }
}
