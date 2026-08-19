<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ModuleRouteInspect;

/**
 * A single consistency / health finding for a module route.
 */
final class ModuleRouteInspectFinding
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public readonly string $severity,
        public readonly string $code,
        public readonly string $message,
        public readonly ?string $feature = null,
        public readonly array $meta = [],
    ) {
    }

    /**
     * @return array{severity: string, code: string, message: string, feature?: string, meta?: array<string, mixed>}
     */
    public function toArray(): array
    {
        $data = [
            'severity' => $this->severity,
            'code' => $this->code,
            'message' => $this->message,
        ];

        if ($this->feature !== null && $this->feature !== '') {
            $data['feature'] = $this->feature;
        }

        if ($this->meta !== []) {
            $data['meta'] = $this->meta;
        }

        return $data;
    }
}
