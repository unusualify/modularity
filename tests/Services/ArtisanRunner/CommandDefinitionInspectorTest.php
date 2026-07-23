<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\ArtisanRunner;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Unusualify\Modularous\Services\ArtisanRunner\CommandDefinitionInspector;
use Unusualify\Modularous\Tests\TestCase;

class CommandDefinitionInspectorTest extends TestCase
{
    /** @test */
    public function it_maps_arguments_and_options_to_form_schema(): void
    {
        $command = new class extends Command
        {
            protected function configure(): void
            {
                $this->setName('demo:inspect')
                    ->setDescription('Demo command')
                    ->addArgument('path', InputArgument::REQUIRED, 'Target path')
                    ->addArgument('tags', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Tags')
                    ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force')
                    ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name');
            }
        };

        $schema = (new CommandDefinitionInspector)->inspect($command);

        $this->assertSame('demo:inspect', $schema['name']);
        $this->assertSame('Demo command', $schema['description']);

        $this->assertCount(2, $schema['arguments']);
        $this->assertSame('path', $schema['arguments'][0]['name']);
        $this->assertTrue($schema['arguments'][0]['required']);
        $this->assertSame('textarea', $schema['arguments'][0]['field']);
        $this->assertSame(['required'], $schema['arguments'][0]['rules']);

        $this->assertSame('tags', $schema['arguments'][1]['name']);
        $this->assertTrue($schema['arguments'][1]['is_array']);
        $this->assertFalse($schema['arguments'][1]['required']);

        $force = collect($schema['options'])->firstWhere('name', 'force');
        $this->assertNotNull($force);
        $this->assertSame('switch', $force['field']);
        $this->assertFalse($force['accept_value']);

        $name = collect($schema['options'])->firstWhere('name', 'name');
        $this->assertNotNull($name);
        $this->assertSame('textarea', $name['field']);
        $this->assertTrue($name['required']);
        $this->assertSame(['required'], $name['rules']);
    }
}
