<?php

namespace Tests\Unit;

use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * تست اسکریپت standalone ساخت بج پوشش از گزارش Clover
 *
 * CI این اسکریپت را بعد از phpunit --coverage-clover اجرا می‌کند و خروجی‌اش
 * (JSON قالب endpoint بج shields.io) روی شاخهٔ coverage-badge پوش می‌شود —
 * این تست‌ها قرارداد JSON و رنگ‌ها را روی یک Clover نمونه قفل می‌کنند.
 */
class CoverageBadgeTest extends TestCase
{
    /** گزارش Clover نمونه با پوشش دلخواه (درصد covered/statements) */
    private function fixtureClover(int $statements, int $covered): string
    {
        $path = tempnam(sys_get_temp_dir(), 'clover-test-');

        file_put_contents($path, <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <coverage generated="1762233600">
          <project name="/app">
            <file name="/app/Shop.php">
              <line num="10" type="stmt" count="1"/>
              <line num="20" type="stmt" count="0"/>
            </file>
            <metrics files="1" loc="20" ncloc="20" statements="{$statements}" coveredstatements="{$covered}"/>
          </project>
        </coverage>
        XML);

        return $path;
    }

    /** @return array{0: int, 1: string, 2: string} [exitCode, stdout, stderr] */
    private function runScript(array $arguments): array
    {
        $process = new Process(array_merge([PHP_BINARY, base_path('scripts/coverage-badge.php')], $arguments));
        $process->run();

        return [$process->getExitCode(), trim($process->getOutput()), trim($process->getErrorOutput())];
    }

    public function test_badge_json_reflects_the_coverage_percentage(): void
    {
        $out = tempnam(sys_get_temp_dir(), 'badge-');
        [$code, $stdout] = $this->runScript([$this->fixtureClover(200, 162), $out]);

        $this->assertSame(0, $code);
        $this->assertStringContainsString('coverage: 81%', $stdout);

        $badge = json_decode((string) file_get_contents($out), true);

        $this->assertSame(1, $badge['schemaVersion']);
        $this->assertSame('coverage', $badge['label']);
        $this->assertSame('81%', $badge['message']);
        $this->assertSame('green', $badge['color']);
    }

    public function test_color_thresholds_red_yellow_green(): void
    {
        $out = tempnam(sys_get_temp_dir(), 'badge-');

        // قرمز: زیر ۵۰٪
        $this->runScript([$this->fixtureClover(100, 30), $out]);
        $this->assertSame('red', json_decode((string) file_get_contents($out), true)['color']);

        // زرد: بین ۵۰ و ۷۹
        $this->runScript([$this->fixtureClover(100, 65), $out]);
        $this->assertSame('yellow', json_decode((string) file_get_contents($out), true)['color']);

        // سبز: ۸۰ و بالاتر
        $this->runScript([$this->fixtureClover(100, 80), $out]);
        $this->assertSame('green', json_decode((string) file_get_contents($out), true)['color']);
    }

    public function test_missing_clover_file_fails(): void
    {
        [$code, , $err] = $this->runScript(['/nonexistent/clover.xml']);

        $this->assertSame(1, $code);
        $this->assertStringContainsString('Clover', $err);
    }

    public function test_zero_statements_report_fails(): void
    {
        $out = tempnam(sys_get_temp_dir(), 'badge-');
        [$code, , $err] = $this->runScript([$this->fixtureClover(0, 0), $out]);

        $this->assertSame(1, $code);
        $this->assertStringContainsString('statements=0', $err);
    }
}
