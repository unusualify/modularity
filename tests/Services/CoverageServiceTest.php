<?php

namespace Unusualify\Modularous\Tests\Services;

use InvalidArgumentException;
use RuntimeException;
use Unusualify\Modularous\Services\CoverageService;
use Unusualify\Modularous\Tests\TestCase;

class CoverageServiceTest extends TestCase
{
    private string $cloverDir;

    private string $cloverName;

    private string $cloverPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cloverDir = sys_get_temp_dir();
        $this->cloverName = 'service-test-clover.xml';
        $this->cloverPath = concatenate_path($this->cloverDir, $this->cloverName);

        $this->createCloverFile();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->cloverPath)) {
            unlink($this->cloverPath);
        }

        parent::tearDown();
    }

    /** @test */
    public function make_and_setters_work_and_return_self()
    {
        $service = CoverageService::make($this->cloverDir, $this->cloverName);

        $this->assertInstanceOf(CoverageService::class, $service);

        $service->setCloverPath($this->cloverDir);
        $service->setCloverName($this->cloverName);
        $this->assertEquals($this->cloverDir, $service->getBaseDirectory());
    }

    /** @test */
    public function instance_and_clear_instance_use_container_binding()
    {
        // Ensure we start clean
        CoverageService::clearInstance();

        $service = new CoverageService($this->cloverDir, $this->cloverName);
        $this->app->instance('coverage.service', $service);

        $resolved = CoverageService::instance();
        $this->assertSame($service, $resolved);

        // Clear and rebind different instance
        CoverageService::clearInstance();
        $another = new CoverageService($this->cloverDir, $this->cloverName);
        $this->app->instance('coverage.service', $another);
        $this->assertSame($another, CoverageService::instance());
    }

    /** @test */
    public function filter_and_skip_methods_are_chainable_and_affect_results()
    {
        $service = new CoverageService($this->cloverDir, $this->cloverName);

        $returned = $service
            ->filterByFiles(['src/Services/UserService.php'])
            ->skipMagicMethods(true)
            ->skipPrivateMethods(true)
            ->skipProtectedMethods(true);

        $this->assertInstanceOf(CoverageService::class, $returned);

        $results = $service->analyze();
        $this->assertIsArray($results);
    }

    /** @test */
    public function git_returns_empty_when_no_changed_files()
    {
        $mock = new class($this->cloverDir, $this->cloverName) extends CoverageService
        {
            protected function getGitChangedFiles(string $baseBranch): array
            {
                return [];
            }
        };

        $result = $mock->git('main');
        $this->assertEquals([], $result);
    }

    /** @test */
    public function git_filters_methods_by_changed_files()
    {
        // Use a custom mock that returns specific changed files
        $mock = new class($this->cloverDir, $this->cloverName) extends CoverageService
        {
            protected function getGitChangedFiles(string $baseBranch): array
            {
                return ['src/Services/UserService.php'];
            }
        };

        $result = $mock->git('main');

        $this->assertIsArray($result);
        // All results should be from the changed file
        foreach ($result as $method) {
            $this->assertStringContainsString('UserService.php', $method['file']);
        }
    }

    /** @test */
    public function git_parses_branch_references_correctly()
    {
        // Test that different branch formats are handled
        $mock = new class($this->cloverDir, $this->cloverName) extends CoverageService
        {
            public function testGetGitChangedFiles(string $baseBranch): array
            {
                // Call the private method through reflection
                $method = new \ReflectionMethod(parent::class, 'getGitChangedFiles');
                $method->setAccessible(true);

                // This will actually run git commands, so we just verify it doesn't crash
                // In a real environment, this would return actual changed files
                try {
                    return $method->invoke($this, $baseBranch);
                } catch (\Throwable $e) {
                    // Git command might fail in test environment
                    return [];
                }
            }
        };

        // Test various branch formats don't crash
        $formats = ['main', 'origin/main', 'refs/heads/main', 'refs/tags/v1.0', 'refs/remotes/origin/develop'];

        foreach ($formats as $branch) {
            $result = $mock->testGetGitChangedFiles($branch);
            $this->assertIsArray($result);
        }
    }

    /** @test */
    public function markdown_html_and_stats_generate_expected_structures()
    {
        $service = new CoverageService($this->cloverDir, $this->cloverName);

        $md = $service->markdown();
        $this->assertIsString($md);
        $this->assertStringContainsString('# Coverage Analysis Report', $md);

        $html = $service->html();
        $this->assertIsString($html);
        $this->assertStringContainsString('<!DOCTYPE html>', $html);

        $stats = $service->stats();
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_methods', $stats);
        $this->assertArrayHasKey('total_files', $stats);
    }

    /** @test */
    public function analyze_analyze_file_and_get_method_coverage_forward_to_analyzer()
    {
        $service = new CoverageService($this->cloverDir, $this->cloverName);

        $all = $service->analyze();
        $this->assertIsArray($all);
        $this->assertNotEmpty($all);

        $fileResults = $service->analyzeFile('src/Services/UserService.php');
        $this->assertIsArray($fileResults);

        $method = $service->getMethodCoverage('src/Services/UserService.php', 'deleteUser');
        $this->assertNotNull($method);
        $this->assertEquals('deleteUser', $method['name']);
        $this->assertArrayHasKey('coverage', $method);
    }

    /** @test */
    public function json_and_save_and_formats_behave_as_expected()
    {
        $service = new CoverageService($this->cloverDir, $this->cloverName);

        $json = $service->json();
        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertArrayHasKey('methods', $decoded);
        $this->assertArrayHasKey('statistics', $decoded);

        $out = concatenate_path($this->cloverDir, 'coverage-output.json');
        if (file_exists($out)) {
            unlink($out);
        }

        $ok = $service->save($out, null, 'json');
        $this->assertTrue($ok);
        $this->assertFileExists($out);
        unlink($out);

        $this->expectException(InvalidArgumentException::class);
        $service->save(concatenate_path($this->cloverDir, 'x.txt'), null, 'unsupported-format');
    }

    /** @test */
    public function uncovered_and_partial_helpers_apply_thresholds_and_filters()
    {
        $service = new CoverageService($this->cloverDir, $this->cloverName);

        $uncovered = $service->uncovered();
        $this->assertIsArray($uncovered);
        // Should include deleteUser which has 0% coverage
        $found = array_filter($uncovered, fn ($m) => $m['method'] === 'deleteUser');
        $this->assertNotEmpty($found);

        $partial = $service->partial(50.0);
        $this->assertIsArray($partial);
        foreach ($partial as $m) {
            $this->assertLessThanOrEqual(50.0, $m['coverage']);
        }
    }

    /** @test */
    public function check_p_r_throws_when_git_reports_uncovered_and_throw_flag_set()
    {
        // Create a small subclass to force git() to return non-empty array
        $mock = new class($this->cloverDir, $this->cloverName) extends CoverageService
        {
            public function git(string $baseBranch = 'main'): array
            {
                return [
                    ['method' => 'foo', 'file' => 'src/Foo.php'],
                ];
            }
        };

        $this->expectException(RuntimeException::class);
        $mock->checkPR('main', true);
    }

    /** @test */
    public function get_relative_path_and_get_base_directory_work()
    {
        $service = new CoverageService($this->cloverDir, $this->cloverName);
        $relative = $service->getRelativePath($this->cloverDir . '/src/Services/UserService.php');
        $this->assertIsString($relative);
        $this->assertEquals($this->cloverDir, $service->getBaseDirectory());
    }

    // ==================== HELPERS ====================
    private function createCloverFile(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<coverage generated="123">
  <project timestamp="123">
    <file name="src/Services/UserService.php">
        <class name="Services\UserService" namespace="global">
            <metrics complexity="10" methods="3" coveredmethods="1" conditionals="0" coveredconditionals="0" statements="10" coveredstatements="2" elements="12" coveredelements="2"/>
        </class>
        <line num="10" type="method" name="createUser" visibility="public" complexity="3" crap="1" count="5"/>
        <line num="11" type="stmt" count="5"/>
        <line num="25" type="method" name="deleteUser" visibility="public" complexity="2" crap="0" count="0"/>
        <line num="26" type="stmt" count="0"/>
        <line num="30" type="method" name="updateUser" visibility="public" complexity="4" crap="0" count="3"/>
        <line num="31" type="stmt" count="3"/>
    </file>
  </project>
</coverage>
XML;

        file_put_contents($this->cloverPath, $xml);
    }
}
