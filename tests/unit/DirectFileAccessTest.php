<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class DirectFileAccessTest extends TestCase
{
    private function runScript(string $code): array
    {
        $process = proc_open(array(PHP_BINARY, '-r', $code), array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        ), $pipes);
        self::assertIsResource($process);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        return array(proc_close($process), $output, $errors);
    }

    #[DataProvider('files')]
    public function testDirectExecutionStopsBeforeOutputOrWordPressCalls(string $file): void
    {
        $path = dirname(__DIR__, 2) . '/src/Hyyan/WPI/' . $file;
        list($exit, $output, $errors) = $this->runScript('require ' . var_export($path, true) . ';');
        self::assertSame(0, $exit);
        self::assertSame('', $output);
        self::assertSame('', $errors);
    }

    public static function files(): array
    {
        return array('status' => array('Admin/StatusReport.php'), 'social' => array('Views/social.php'));
    }

    public function testSocialTemplateKeepsExistingWidgetsInsideWordPress(): void
    {
        $path = dirname(__DIR__, 2) . '/src/Hyyan/WPI/Views/social.php';
        list($exit, $output, $errors) = $this->runScript("define('ABSPATH', '/');require " . var_export($path, true) . ';');
        self::assertSame(0, $exit);
        self::assertSame('', $errors);
        self::assertStringContainsString('twitter-share-button', $output);
        self::assertStringContainsString('fb-share-button', $output);
        self::assertStringContainsString('ghbtns.com/github-btn.html', $output);
    }
}
