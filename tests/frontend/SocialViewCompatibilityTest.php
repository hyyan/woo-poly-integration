<?php
use Hyyan\WPI\Plugin;
use PHPUnit\Framework\TestCase;

final class SocialViewCompatibilityTest extends TestCase
{
    public function testSocialTemplateKeepsSharingActionsInsideWordPress(): void
    {
        self::assertFalse(is_admin());
        $output = Plugin::getView('social');
        self::assertStringContainsString('twitter.com/intent/tweet', $output);
        self::assertStringContainsString('facebook.com/sharer/sharer.php', $output);
        self::assertStringContainsString('https://github.com/hyyan/woo-poly-integration', $output);
        self::assertStringNotContainsString('<script', $output);
        self::assertStringNotContainsString('<iframe', $output);
    }
}
