<?php

namespace Unusualify\Modularity\Support;

use Unusualify\Modularity\Traits\Pretending;
use Unusualify\Modularity\Traits\Verbosity;

class RegexReplacement
{
    use Pretending, Verbosity;

    public function __construct(
        protected string $path,
        protected string $pattern,
        protected string $data,
        protected string $directory_pattern = '**/*.php',
        public bool $quiet = false,
        $verbose = null,
        bool $test = false,
    ) {

        $this->setPretending($test);
        $this->setVerbosity($verbose);

        if ($quiet) {
            $this->setVerbosity('quiet');
        }
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setDirectoryPattern($directory_pattern)
    {
        $this->directory_pattern = $directory_pattern;
    }

    public function displayPatternMatches($file)
    {
        $content = file_get_contents($file);

        if (!$content) {
            return false;
        }

        $matches = [];
        preg_match_all($this->pattern, $content, $matches, PREG_OFFSET_CAPTURE);

        if ($this->isQuiet()) {
            return;
        }
        if (!empty($matches[0])) {

            if($this->isVerbose() || $this->pretending()){
                echo "\n\033[33m--------------------------------------------\033[0m";
            }

            if($this->isVerbose() || $this->pretending()){
                echo "\n\033[36mFile to be processed: " . $file . "\n";
            }else{
                echo "\033[36m " . $file ."\n";
            }

            if ($this->isVerbose()) {
                echo "\033[32mMatches Found:\033[0m";
            }

            if ($this->isVerbose() || $this->pretending()) {

                if($this->isVerbose()){
                    // Display the matches
                    foreach ($matches[0] as $idx => $match) {
                        $lineNumber = substr_count(substr($content, 0, $match[1]), "\n") + 1;
                        $contextStart = max(0, $match[1] - 50);
                        $contextLength = min(strlen($content) - $contextStart, $match[1] - $contextStart + strlen($match[0]) + 50);
                        $context = substr($content, $contextStart, $contextLength);

                        echo "\n\033[34mLine {$lineNumber}\033[0m\n";

                        // Split context into lines and add line numbers
                        $lines = explode("\n", $context);
                        $startLine = $lineNumber - substr_count(substr($context, 0, strpos($context, $match[0])), "\n");

                        // Find the line containing the match start and end
                        $matchStartLine = null;
                        $matchEndLine = null;
                        $matchPos = strpos($context, $match[0]);
                        $matchStartLine = substr_count(substr($context, 0, $matchPos), "\n");
                        $matchEndLine = $matchStartLine + substr_count($match[0], "\n");

                        foreach ($lines as $i => $line) {
                            $currentLine = $startLine + $i;
                            if ($i >= $matchStartLine && $i <= $matchEndLine) {
                                // Line is within the match range - color the whole line
                                echo sprintf("%4d | \033[44m%s\033[0m\n", $currentLine, $line);
                            } else {
                                echo sprintf("%4d | %s\n", $currentLine, $line);
                            }
                        }
                    }
                }

                if ($this->isVeryVerbose() || $this->pretending()) {
                    echo "\n\033[32mReplacement Preview:\033[0m\n";

                    // Get original and replaced content
                    $originalLines = explode("\n", $content);
                    $replacedContent = preg_replace($this->pattern, $this->data, $content);
                    $replacedLines = explode("\n", $replacedContent);

                    // Compare and show differences
                    $maxLines = max(count($originalLines), count($replacedLines));
                    for ($i = 0; $i < $maxLines; $i++) {
                        $originalLine = isset($originalLines[$i]) ? $originalLines[$i] : '';
                        $replacedLine = isset($replacedLines[$i]) ? $replacedLines[$i] : '';

                        if ($originalLine !== $replacedLine) {
                            if ($originalLine) {
                                echo sprintf("%4d | \033[31m-%s\033[0m\n", $i + 1, $originalLine);
                            }
                            if ($replacedLine) {
                                echo sprintf("%4d | \033[32m+%s\033[0m\n", $i + 1, $replacedLine);
                            }
                        } else {
                            echo sprintf("%4d | %s\n", $i + 1, $originalLine);
                        }
                    }
                }
            }

        }
    }

    public function replacePatternFile($file)
    {
        $content = file_get_contents($file);

        if (!$content) {
            return false;
        }

        $this->displayPatternMatches($file);

        $replacedContent = preg_replace($this->pattern, $this->data, $content);

        if (!$this->pretending()) {
            file_put_contents($file, $replacedContent);
        }

        return true;
    }

    public function run()
    {
        $directory = new \RecursiveDirectoryIterator($this->path);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = [];

        // Convert glob pattern to regex pattern
        $regex = '#' . str_replace(
            ['*', '?'],
            ['[^/]*', '.'],
            $this->directory_pattern
        ) . '#';

        foreach ($iterator as $file) {
            if ($file->isFile() && preg_match($regex, $file->getPathname())) {
                $files[] = $file->getPathname();
            }
        }

        foreach ($files as $file) {
            if ($this->pretending) {
                $this->displayPatternMatches($file);
            } else {
                $this->replacePatternFile($file);
            }
        }

        return true;
    }


}
