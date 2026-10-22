<?php

namespace Unusualify\Modularous\Services;

use InvalidArgumentException;
use Unusualify\Modularous\Support\CoverageAnalyzer;

/**
 * Coverage Service
 *
 * High-level service for code coverage analysis and reporting.
 * This is the real implementation behind the Coverage facade.
 */
class CoverageService
{
    private CoverageAnalyzer $analyzer;

    private string $cloverDir;

    private string $cloverName;

    private static ?self $instance = null;

    public function __construct(?string $cloverDir = null, ?string $cloverName = null)
    {
        $this->cloverDir = $cloverDir ?? config('modularous-coverage.clover_dir');
        $this->cloverName = $cloverName ?? config('modularous-coverage.clover_name');
        $this->initializeAnalyzer();
    }

    /**
     * Create a new instance with custom clover path
     * Useful for one-off analyses with different coverage files
     */
    public static function make(?string $cloverDir = null, ?string $cloverName = null): self
    {
        return new self($cloverDir, $cloverName);
    }

    /**
     * Get or create singleton instance
     */
    public static function instance(): self
    {
        return self::$instance ??= app(self::class);
    }

    /**
     * Reset the singleton instance
     */
    public static function clearInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Set a different clover path
     */
    public function setCloverPath(string $path): self
    {
        $this->cloverDir = $path;
        $this->initializeAnalyzer();

        return $this;
    }

    /**
     * Set a different clover name
     */
    public function setCloverName(string $name): self
    {
        $this->cloverName = $name;
        $this->initializeAnalyzer();

        return $this;
    }

    /**
     * Filter analysis by specific files
     */
    public function filterByFiles(array $files): self
    {
        $this->analyzer->filterByFiles($files);

        return $this;
    }

    /**
     * Set coverage threshold
     */
    public function setCoverageThreshold(float $threshold): self
    {
        $this->analyzer->setCoverageThreshold($threshold);

        return $this;
    }

    /**
     * Skip magic methods
     */
    public function skipMagicMethods(bool $skip = true): self
    {
        $this->analyzer->skipMagicMethods($skip);

        return $this;
    }

    /**
     * Skip private methods
     */
    public function skipPrivateMethods(bool $skip = true): self
    {
        $this->analyzer->skipPrivateMethods($skip);

        return $this;
    }

    /**
     * Skip protected methods
     */
    public function skipProtectedMethods(bool $skip = true): self
    {
        $this->analyzer->skipProtectedMethods($skip);

        return $this;
    }

    /**
     * Analyze coverage
     */
    public function analyze(): array
    {
        return $this->analyzer->analyze();
    }

    /**
     * Analyze a specific file
     */
    public function analyzeFile(string $filePath): array
    {
        return $this->analyzer->analyzeFile($filePath);
    }

    /**
     * Get coverage for a specific method
     */
    public function getMethodCoverage(string $filePath, string $methodName): ?array
    {
        return $this->analyzer->getMethodCoverage($filePath, $methodName);
    }

    /**
     * Get errors from analyzer
     */
    public function getErrors(): array
    {
        return $this->analyzer->getErrors();
    }

    /**
     * Check if analyzer has errors
     */
    public function hasErrors(): bool
    {
        return $this->analyzer->hasErrors();
    }

    // ==================== HIGH-LEVEL METHODS ====================

    /**
     * Analyze files changed in git compared to base branch
     */
    public function git(string $baseBranch = '11.x'): array
    {
        $changedFiles = $this->getGitChangedFiles($baseBranch);

        if (empty($changedFiles)) {
            return [];
        }

        dd($this->filterByFiles($changedFiles), $changedFiles, $this->filterByFiles($changedFiles)->analyze());

        return $this->filterByFiles($changedFiles)->analyze();
    }

    /**
     * Get uncovered methods (0% coverage)
     */
    public function uncovered(array $files = []): array
    {
        $this->setCoverageThreshold(0.0);

        if (! empty($files)) {
            $this->filterByFiles($files);
        }

        return $this->analyze();
    }

    /**
     * Get methods with partial coverage (below threshold)
     */
    public function partial(float $threshold = 50.0, array $files = []): array
    {
        $this->setCoverageThreshold($threshold);

        if (! empty($files)) {
            $this->filterByFiles($files);
        }

        return $this->analyze();
    }

    /**
     * Generate JSON report
     */
    public function json(?array $files = null, bool $prettyPrint = true): string
    {
        if ($files !== null) {
            $this->filterByFiles($files);
        }

        $results = $this->analyze();

        $report = [
            'timestamp' => now()->toIso8601String(),
            'total_methods' => count($results),
            'methods' => $results,
            'statistics' => $this->calculateStatistics($results),
        ];

        $flags = $prettyPrint ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0;

        return json_encode($report, $flags);
    }

    /**
     * Save report to file
     */
    public function save(string $outputPath, ?array $files = null, string $format = 'json'): bool
    {
        $content = match ($format) {
            'json' => $this->json($files),
            'markdown' => $this->markdown($files),
            'html' => $this->html($files),
            default => throw new InvalidArgumentException("Unsupported format: {$format}")
        };

        // Ensure directory exists
        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($outputPath, $content) !== false;
    }

    /**
     * Generate markdown report
     */
    public function markdown(?array $files = null): string
    {
        if ($files !== null) {
            $this->filterByFiles($files);
        }

        $results = $this->analyze();
        $stats = $this->calculateStatistics($results);

        $md = "# Coverage Analysis Report\n\n";
        $md .= '**Generated:** ' . now()->toDateTimeString() . "\n\n";
        $md .= "## Summary\n\n";
        $md .= "- **Total Uncovered Methods:** {$stats['total_methods']}\n";
        $md .= "- **Total Files:** {$stats['total_files']}\n";
        $md .= "- **Total Uncovered Lines:** {$stats['total_uncovered_lines']}\n\n";

        if (empty($results)) {
            $md .= "✅ **All methods are covered!**\n";

            return $md;
        }

        $md .= "## Uncovered Methods\n\n";

        $groupedByFile = $this->groupByFile($results);

        foreach ($groupedByFile as $file => $methods) {
            $md .= "### `{$file}`\n\n";

            foreach ($methods as $method) {
                $md .= "#### ❌ `{$method['method']}()`\n\n";
                $md .= "- **Class:** `{$method['class']}`\n";
                $md .= "- **Coverage:** {$method['coverage']}%\n";
                $md .= "- **Uncovered Lines:** {$method['lines']['uncovered']}/{$method['lines']['total']}\n\n";
            }
        }

        return $md;
    }

    /**
     * Generate HTML report
     */
    public function html(?array $files = null): string
    {
        if ($files !== null) {
            $this->filterByFiles($files);
        }

        $results = $this->analyze();
        $stats = $this->calculateStatistics($results);

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coverage Analysis Report</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #e74c3c; padding-bottom: 10px; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 6px; border-left: 4px solid #e74c3c; }
        .stat-card h3 { margin: 0 0 10px 0; color: #666; font-size: 14px; text-transform: uppercase; }
        .stat-card .value { font-size: 32px; font-weight: bold; color: #333; }
        .method-card { background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 20px; margin: 15px 0; }
        .method-card h3 { margin: 0 0 15px 0; color: #e74c3c; }
        .method-info { display: grid; grid-template-columns: auto 1fr; gap: 10px 20px; }
        .method-info dt { font-weight: bold; color: #666; }
        .method-info dd { margin: 0; color: #333; }
        .file-section { margin: 30px 0; }
        .file-header { background: #34495e; color: white; padding: 15px; border-radius: 6px; margin-bottom: 15px; }
        .success { color: #27ae60; font-size: 18px; text-align: center; padding: 40px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Coverage Analysis Report</h1>
        <p><strong>Generated:</strong> {$stats['timestamp']}</p>

        <div class="summary">
            <div class="stat-card">
                <h3>Total Methods</h3>
                <div class="value">{$stats['total_methods']}</div>
            </div>
            <div class="stat-card">
                <h3>Total Files</h3>
                <div class="value">{$stats['total_files']}</div>
            </div>
            <div class="stat-card">
                <h3>Uncovered Lines</h3>
                <div class="value">{$stats['total_uncovered_lines']}</div>
            </div>
        </div>
HTML;

        if (empty($results)) {
            $html .= '<div class="success">✅ All methods are covered!</div>';
        } else {
            $groupedByFile = $this->groupByFile($results);

            foreach ($groupedByFile as $file => $methods) {
                $html .= "<div class='file-section'>";
                $html .= "<div class='file-header'><strong>📁 {$file}</strong></div>";

                foreach ($methods as $method) {
                    $html .= <<<METHOD
                    <div class="method-card">
                        <h3>❌ {$method['method']}()</h3>
                        <dl class="method-info">
                            <dt>Class:</dt>
                            <dd>{$method['class']}</dd>
                            <dt>Coverage:</dt>
                            <dd>{$method['coverage']}%</dd>
                            <dt>Lines:</dt>
                            <dd>{$method['lines']['uncovered']}/{$method['lines']['total']} uncovered</dd>
                        </dl>
                    </div>
METHOD;
                }

                $html .= '</div>';
            }
        }

        $html .= <<<'HTML'
    </div>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Check if PR meets coverage requirements
     */
    public function checkPR(string $baseBranch = 'main', bool $throwOnFailure = false): bool
    {
        $uncovered = $this->git($baseBranch);

        if (empty($uncovered)) {
            return true;
        }

        if ($throwOnFailure) {
            $count = count($uncovered);
            throw new \RuntimeException(
                "Coverage check failed: {$count} uncovered methods found in changed files"
            );
        }

        return false;
    }

    /**
     * Get coverage statistics
     */
    public function stats(?array $files = null): array
    {
        if ($files !== null) {
            $this->filterByFiles($files);
        }

        $results = $this->analyze();

        return $this->calculateStatistics($results);
    }

    // ==================== PRIVATE HELPER METHODS ====================

    /**
     * Initialize or reinitialize the analyzer
     */
    private function initializeAnalyzer(): void
    {
        $this->analyzer = new CoverageAnalyzer($this->cloverDir, $this->cloverName);
    }

    /**
     * Get changed files from git diff
     */
    private function getGitChangedFiles(string $baseBranch): array
    {
        $branchSegments = [
            'origin',
        ];

        if (preg_match('/^refs\/tags\//', $baseBranch)) {
            $branchSegments = [
                'refs',
                'tags',
            ];
            $branchSegments[] = preg_replace('/^refs\/tags\//', '', $baseBranch);
        } elseif (preg_match('/^refs\/heads\//', $baseBranch)) {
            $branchSegments[] = 'heads';
            $branchSegments[] = preg_replace('/^refs\/heads\//', '', $baseBranch);
        } elseif (preg_match('/^refs\/remotes\//', $baseBranch)) {
            $branchSegments[] = 'remotes';
            $baseBranch = preg_replace('/^refs\/remotes\//', '', $baseBranch);
        } elseif (preg_match('/^origin\//', $baseBranch)) {

            $branchSegments[] = preg_replace('/^origin\//', '', $baseBranch);
        }

        $baseRef = implode('/', $branchSegments);
        $commands = [];

        if ($this->cloverDir !== getcwd()) {
            $commands[] = "cd {$this->cloverDir}";
        }

        $commands[] = "git diff --name-only {$baseRef}...HEAD";
        $command = implode(' && ', $commands);
        $output = shell_exec($command);

        if ($output === null) {
            return [];
        }

        $files = array_filter(
            explode("\n", trim($output)),
            fn ($file) => str_ends_with($file, '.php') && ! empty($file)
        );

        return array_values($files);
    }

    /**
     * Calculate statistics from results
     */
    private function calculateStatistics(array $results): array
    {
        $files = array_unique(array_column($results, 'file'));

        $totalLines = array_sum(array_column(array_column($results, 'lines'), 'total'));
        $uncoveredLines = array_sum(array_column(array_column($results, 'lines'), 'uncovered'));

        return [
            'timestamp' => now()->toDateTimeString(),
            'total_methods' => count($results),
            'total_files' => count($files),
            'total_lines' => $totalLines,
            'total_uncovered_lines' => $uncoveredLines,
            'files' => $files,
        ];
    }

    /**
     * Group results by file
     */
    private function groupByFile(array $results): array
    {
        $grouped = [];

        foreach ($results as $result) {
            $file = $result['file'];
            if (! isset($grouped[$file])) {
                $grouped[$file] = [];
            }
            $grouped[$file][] = $result;
        }

        return $grouped;
    }

    public function getRelativePath(string $filePath): string
    {
        // Remove common prefixes like src/, app/, etc.
        $filePath = preg_replace('/^' . preg_quote($this->cloverDir . '/', '/') . '/', '', $filePath);

        $patterns = ['/^src\//', '/^app\//', '/^packages\//'];
        foreach ($patterns as $pattern) {
            $filePath = preg_replace($pattern, '', $filePath);
        }

        return $filePath;
    }

    public function getBaseDirectory(): string
    {
        return $this->cloverDir;
    }
}
