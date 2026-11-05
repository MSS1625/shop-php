<?php

namespace Tests\Unit;

use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * تست اسکریپت standalone استخراج یادداشت‌های انتشار از CHANGELOG
 *
 * اسکریپت در GitHub Actions (release.yml) بدنهٔ Release را از CHANGELOG.md
 * می‌سازد — همین تست‌ها روی یک CHANGELOG نمونه قراردادش را قفل می‌کنند.
 */
class ExtractReleaseNotesTest extends TestCase
{
    /** CHANGELOG نمونه با دو نسخه × دو زبان — همان قالب فایل واقعی */
    private function fixtureChangelog(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'changelog-test-');

        file_put_contents($path, <<<'MD'
        # 📋 Changelog

        ## 🇬🇧 English

        ## [1.0.0] — 2026-01-01

        ### 🚀 Added — first release

        - English body of 1.0.0
        - second line

        ## [2.0.0] — 2026-02-02

        ### 🚀 Added — second release

        - English body of 2.0.0

        ## 🇮🇷 فارسی

        ## [1.0.0] — ۲۰۲۶-۰۱-۰۱

        ### 🚀 اضافه شد — انتشار اول

        - بدنهٔ فارسی ۱.۰.۰

        ## [2.0.0] — ۲۰۲۶-۰۲-۰۲

        ### 🚀 اضافه شد — انتشار دوم

        - بدنهٔ فارسی ۲.۰.۰
        MD);

        return $path;
    }

    /** اجرای اسکریپت روی فایل نمونه و برگرداندن [exitCode, stdout, stderr] */
    private function runScript(array $arguments): array
    {
        $process = new Process(array_merge([PHP_BINARY, base_path('scripts/extract-release-notes.php')], $arguments));
        $process->run();

        return [$process->getExitCode(), trim($process->getOutput()), trim($process->getErrorOutput())];
    }

    public function test_extracts_both_language_bodies_for_the_version(): void
    {
        [$code, $out] = $this->runScript(['1.0.0', '--file='.$this->fixtureChangelog()]);

        $this->assertSame(0, $code);

        // بدنهٔ هر دو زبان می‌آید — با جداکنندهٔ ---
        $this->assertStringContainsString('### 🚀 Added — first release', $out);
        $this->assertStringContainsString('English body of 1.0.0', $out);
        $this->assertStringContainsString('### 🚀 اضافه شد — انتشار اول', $out);
        $this->assertStringContainsString('بدنهٔ فارسی ۱.۰.۰', $out);

        // سرفصل‌های نسخه و محتوای نسخهٔ دیگر نمی‌آیند
        $this->assertStringNotContainsString('## [1.0.0]', $out);
        $this->assertStringNotContainsString('2.0.0', $out);
        $this->assertStringContainsString(PHP_EOL.'---'.PHP_EOL, $out);
    }

    public function test_title_mode_returns_first_section_heading(): void
    {
        [$code, $out] = $this->runScript(['2.0.0', '--file='.$this->fixtureChangelog(), '--title']);

        $this->assertSame(0, $code);
        $this->assertSame('🚀 Added — second release', $out);
    }

    public function test_unknown_version_fails_with_a_helpful_error(): void
    {
        [$code, , $err] = $this->runScript(['9.9.9', '--file='.$this->fixtureChangelog()]);

        $this->assertSame(1, $code);
        $this->assertStringContainsString('9.9.9', $err);
    }

    public function test_real_changelog_contains_current_version(): void
    {
        // قرارداد با CHANGELOG واقعی: نسخهٔ جاری باید استخراج‌پذیر باشد
        [$code, $out] = $this->runScript(['2.15.0']);

        $this->assertSame(0, $code);
        $this->assertStringContainsString('### ', $out);
    }
}
