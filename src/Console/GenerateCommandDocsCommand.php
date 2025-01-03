<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Str;
use Symfony\Component\Console\Descriptor\MarkdownDescriptor;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class GenerateCommandDocsCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:generate:command:docs
        {--output= : Output directory for markdown files}
        {--f|force : Force overwrite existing files}
    ';

    protected $aliases = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract Laravel Console Documentation';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        // handle command
        $outputPath = $this->option('output') ?? get_modularity_vendor_path('docs/src/pages/advanced-guide/commands');
        $force = $this->option('force');

        if (! Str::startsWith($outputPath, '/')) {
            $outputPath = base_path($outputPath);
        }

        // Ensure output directory exists
        if (! is_dir($outputPath)) {
            mkdir($outputPath, 0755, true);
        }

        // Get all registered commands
        $commands = $this->getLaravel()->make('Illuminate\Contracts\Console\Kernel')->all();
        // Filter only modularity commands
        $commands = array_filter($commands, function ($command) {
            return Str::startsWith($command->getName(), 'modularity:') ||
                   Str::startsWith($command->getName(), 'mod:');
        });

        $commands = array_reduce($commands, function ($carry, $command) {
            $carry[$command->getName()] = $command;

            return $carry;
        }, []);

        // dd(
        //     array_map(function($command) {
        //         return $command->getDescription();
        //     }, $commands)
        // );
        foreach ($commands as $name => $command) {
            $this->info("Extracting documentation for: {$name}");

            try {
                // Create a buffered output to capture the help text
                $output = new BufferedOutput;

                // Run the help command for each command
                $input = new ArrayInput(['command_name' => $name]);
                $helpCommand = new \Symfony\Component\Console\Command\HelpCommand;
                $helpCommand->setCommand($command);

                // Use Markdown descriptor
                $descriptor = new MarkdownDescriptor;
                $descriptor->describe($output, $command, [
                    'format' => 'md',
                    'raw_text' => false,
                ]);

                $realName = $command->getName();

                // Get the help content
                $content = $output->fetch();

                $content = $this->formatMarkdown($content, $command);
                // Save to markdown file
                $_name = str_replace('modularity:', '', $realName);
                $filename = str_replace(':', '-', $_name) . '.md';

                $category = $this->getCommandCategory($command);

                if ($category !== 'Other') {
                    if (! is_dir($outputPath . '/' . $category)) {
                        mkdir($outputPath . '/' . $category, 0755, true);
                    }
                    $filepath = $outputPath . '/' . $category . '/' . $filename;
                } else {
                    $filepath = $outputPath . '/' . $filename;
                }

                if (file_exists($filepath) && ! $force) {
                    $this->info("Skipping {$filepath} because it already exists");

                    continue;
                }

                file_put_contents($filepath, $content);

                $this->info("Saved to: {$filepath}");
            } catch (\Exception $e) {
                $this->error("Error processing {$name}: " . $e->getMessage());
            }
        }

        $this->info('Documentation extraction completed!');

        return 0;
    }

    private function generateTableOfContents($commands, $outputDir)
    {
        $toc = "# Laravel Commands Documentation\n\n";
        $toc .= "## Available Commands\n\n";

        foreach ($commands as $name => $command) {
            $filename = str_replace(':', '-', $name) . '.md';
            $toc .= sprintf("- [%s](%s) - %s\n",
                $name,
                $filename,
                $command->getDescription()
            );
        }

        file_put_contents($outputDir . '/README.md', $toc);
    }

    private function formatMarkdown($content, $command)
    {
        $headerName = Str::headline(Str::replace(':', '_', Str::replace('modularity:', '', $command->getName())));
        $header = sprintf("# `%s`\n\n", $headerName);
        $header .= sprintf("> %s\n\n", $command->getDescription());

        // Add metadata
        $metadata = "## Command Information\n\n";
        $metadata .= sprintf("- **Signature:** `%s`\n", $command->getSynopsis());
        $metadata .= sprintf("- **Category:** %s\n", $this->getCommandCategory($command));

        // Add examples section
        $examples = $this->generateExamples($command);

        return $header . $metadata . "\n" . $examples . "\n" . $content;
    }

    private function getCommandCategory($command)
    {
        $name = $command->getName();

        // Define category patterns
        $utilName = str_replace('modularity:', '', $name);

        $categories = [
            'Generators' => [
                'generate',
                'make',
                'create',
            ],
            'Database' => [
                'migrate',
            ],
            'Cache' => [
                'cache',
                'flush',
            ],
            'Queue' => [
                'queue',
            ],
            'Setup' => [
                'setup',
                'install',
            ],
            'Assets' => [
                'build',
                'dev',
            ],
            'Composer' => [
                'composer',
            ],
            // Add more patterns as needed
        ];
        $category = 'Other';
        foreach ($categories as $c => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_starts_with($utilName, $pattern)) {
                    $category = $c;
                }
            }
        }

        return $category;
    }

    private function generateExamples($command)
    {
        $examples = "\n## Examples\n\n";
        $name = $command->getName();
        $definition = $command->getDefinition();

        // Get all arguments
        $arguments = $definition->getArguments();

        if (count($arguments) == 0) {
            $examples .= "### Basic Usage\n\n```bash\nphp artisan {$name}\n```\n\n";
        } else {
            $examples .= "### With Arguments\n\n```bash\n";
            $exampleWithArgs = "php artisan {$name}";
            foreach ($arguments as $argument) {
                $argName = mb_strtoupper($argument->getName());
                $exampleWithArgs .= " {$argName}";
            }
            $examples .= $exampleWithArgs . "\n```\n\n";
        }

        // Get all options
        $options = $definition->getOptions();
        if (count($options) > 0) {

            // Generate example with common options
            $exampleWithOptions = "php artisan {$name}";
            $exceptOptions = ['help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction', 'no-warnings', 'env'];

            $options = array_filter($options, function ($option) use ($exceptOptions) {
                return ! in_array($option->getName(), $exceptOptions);
            });
            if (count($options) > 0) {
                $examples .= "### With Options\n\n";
            }
            foreach ($options as $option) {
                // if (in_array($option->getName(), $exceptOptions)) continue;

                $optionName = $option->getName();
                $shortcut = $option->getShortcut();

                if ($option->acceptValue()) {
                    // For options that require values
                    $valueHint = mb_strtoupper($optionName);
                    if ($shortcut) {
                        $examples .= "```bash\n# Using shortcut\n";
                        $examples .= "php artisan {$name} -{$shortcut} {$valueHint}\n\n";
                        $examples .= "# Using full option name\n";
                        $examples .= "php artisan {$name} --{$optionName}={$valueHint}\n```\n\n";
                    } else {
                        $examples .= "```bash\nphp artisan {$name} --{$optionName}={$valueHint}\n```\n\n";
                    }
                } else {
                    // For boolean flags
                    if ($shortcut) {
                        $examples .= "```bash\n# Using shortcut\n";
                        $examples .= "php artisan {$name} -{$shortcut}\n\n";
                        $examples .= "# Using full option name\n";
                        $examples .= "php artisan {$name} --{$optionName}\n```\n\n";
                    } else {
                        $examples .= "```bash\nphp artisan {$name} --{$optionName}\n```\n\n";
                    }
                }
            }
        }

        // Add common combinations if there are both arguments and options
        if (count($arguments) > 0 && count($options) > 0) {
            $examples .= "### Common Combinations\n\n```bash\n";
            $commonExample = "php artisan {$name}";

            // Add first argument if exists
            if (! empty($arguments)) {
                $firstArg = array_key_first($arguments);
                $commonExample .= ' ' . mb_strtoupper($firstArg);
            }

            // Add common options
            foreach ($options as $option) {
                if ($option->getName() === 'help') {
                    continue;
                }
                if ($option->isValueRequired()) {
                    $commonExample .= " --{$option->getName()}=" . mb_strtoupper($option->getName());

                    break; // Just add one example option
                }
            }

            $examples .= $commonExample . "\n```\n";
        }

        return $examples;
    }
}
