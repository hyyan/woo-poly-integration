<?php
use Hyyan\WPI\Utilities;
use PHPUnit\Framework\TestCase;

final class CurrentUrlRequestTest extends TestCase
{
    private array $server;
    protected function setUp(): void { $this->server = $_SERVER; }
    protected function tearDown(): void { $_SERVER = $this->server; }

    public function testValidRequestHostPortPathAndQueryArePreserved(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.test:8098';
        $_SERVER['REQUEST_URI'] = '/de/shop/?filter_size=small&orderby=price';
        $_SERVER['HTTPS'] = 'on';
        self::assertSame('https://example.test:8098/de/shop/?filter_size=small&orderby=price', Utilities::getCurrentUrl());
    }
    public function testAbsentServerValuesReturnConfiguredHome(): void
    {
        unset($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
        self::assertSame(home_url('/'), Utilities::getCurrentUrl());
    }
    public function testArrayServerValuesReturnConfiguredHome(): void
    {
        $_SERVER['HTTP_HOST'] = array('invalid');
        $_SERVER['REQUEST_URI'] = array('/shop/');
        self::assertSame(home_url('/'), Utilities::getCurrentUrl());
    }
    public function testRequestControlCharactersAreRemoved(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.test';
        $_SERVER['REQUEST_URI'] = "/shop/?q=safe\r\ninjected";
        $_SERVER['HTTPS'] = 'off';
        self::assertSame('http://example.test/shop/?q=safeinjected', Utilities::getCurrentUrl());
    }
}
