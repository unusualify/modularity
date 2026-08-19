<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ModuleRouteInspect;

/**
 * Suggested / runnable fix for an inspect finding (heal/remake contract).
 */
final class ModuleRouteInspectRemedy
{
    public const ACTION_REMAKE = 'remake';

    public const ACTION_MANUAL = 'manual';

    /**
     * @param  list<string>  $arguments
     * @param  array<string, bool|string|int|null>  $options
     */
    public function __construct(
        public readonly string $action,
        public readonly ?string $command = null,
        public readonly array $arguments = [],
        public readonly array $options = [],
        public readonly bool $safe = true,
        public readonly ?string $tip = null,
    ) {
    }

    /**
     * Shell-ready artisan invocation (for copy / --suggest).
     */
    public function artisanLine(bool $includeDryRun = true): string
    {
        if ($this->action !== self::ACTION_REMAKE || $this->command === null || $this->command === '') {
            return $this->tip ?? '';
        }

        $parts = ['php artisan', $this->command];
        foreach ($this->arguments as $argument) {
            $parts[] = $this->escapeArg((string) $argument);
        }

        foreach ($this->options as $name => $value) {
            if ($value === false || $value === null) {
                continue;
            }

            if ($name === 'dry-run' && ! $includeDryRun) {
                continue;
            }

            $flag = '--' . ltrim((string) $name, '-');
            if ($value === true) {
                $parts[] = $flag;

                continue;
            }

            $parts[] = $flag . '=' . $this->escapeArg((string) $value);
        }

        return implode(' ', $parts);
    }

    /**
     * @return array{
     *     action: string,
     *     command: ?string,
     *     arguments: list<string>,
     *     options: array<string, bool|string|int|null>,
     *     safe: bool,
     *     tip: ?string,
     *     artisan: string
     * }
     */
    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'command' => $this->command,
            'arguments' => $this->arguments,
            'options' => $this->options,
            'safe' => $this->safe,
            'tip' => $this->tip,
            'artisan' => $this->artisanLine(),
        ];
    }

    private function escapeArg(string $value): string
    {
        if ($value === '' || preg_match('/\s/', $value)) {
            return escapeshellarg($value);
        }

        return $value;
    }
}
