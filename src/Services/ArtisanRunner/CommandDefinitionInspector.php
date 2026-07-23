<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ArtisanRunner;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class CommandDefinitionInspector
{
    /**
     * @return array{
     *     name: string,
     *     description: string,
     *     arguments: list<array<string, mixed>>,
     *     options: list<array<string, mixed>>
     * }
     */
    public function inspect(Command $command): array
    {
        $definition = $command->getDefinition();

        $arguments = [];
        foreach ($definition->getArguments() as $argument) {
            if ($argument->getName() === 'command') {
                continue;
            }

            $arguments[] = $this->mapArgument($argument);
        }

        $options = [];
        foreach ($definition->getOptions() as $option) {
            if (in_array($option->getName(), ['help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction', 'env'], true)) {
                continue;
            }

            $options[] = $this->mapOption($option);
        }

        return [
            'name' => $command->getName() ?? '',
            'description' => (string) $command->getDescription(),
            'arguments' => $arguments,
            'options' => $options,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapArgument(InputArgument $argument): array
    {
        $isArray = $argument->isArray();
        $required = $argument->isRequired();

        return [
            'name' => $argument->getName(),
            'description' => (string) $argument->getDescription(),
            'required' => $required,
            'is_array' => $isArray,
            'default' => $argument->getDefault(),
            'field' => 'textarea',
            'rules' => $required ? ['required'] : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapOption(InputOption $option): array
    {
        $isArray = $option->isArray();
        $required = $option->isValueRequired();

        if (! $option->acceptValue() && ! $isArray) {
            return [
                'name' => $option->getName(),
                'shortcut' => $option->getShortcut(),
                'description' => (string) $option->getDescription(),
                'required' => false,
                'is_array' => false,
                'accept_value' => false,
                'default' => false,
                'field' => 'switch',
                'rules' => [],
            ];
        }

        return [
            'name' => $option->getName(),
            'shortcut' => $option->getShortcut(),
            'description' => (string) $option->getDescription(),
            'required' => $required,
            'is_array' => $isArray,
            'accept_value' => true,
            'default' => $option->getDefault(),
            'field' => 'textarea',
            'rules' => $required ? ['required'] : [],
        ];
    }
}
