<?php
use Hyyan\WPI\Plugin;
use PHPUnit\Framework\TestCase;

final class SocialViewCompatibilityTest extends TestCase
{
    public function testSocialTemplateOffersOnlyAuthorAndProjectLinks(): void
    {
        self::assertFalse(is_admin());
        $output = Plugin::getView('social');
        self::assertStringContainsString('https://github.com/hyyan"', $output);
        self::assertStringContainsString('https://www.linkedin.com/in/hyyan/', $output);
        self::assertStringNotContainsString('twitter.com', $output);
        self::assertStringNotContainsString('facebook.com', $output);
        self::assertStringContainsString('https://github.com/hyyan/woo-poly-integration', $output);
        self::assertStringNotContainsString('<script', $output);
        self::assertStringNotContainsString('<iframe', $output);
    }
}
