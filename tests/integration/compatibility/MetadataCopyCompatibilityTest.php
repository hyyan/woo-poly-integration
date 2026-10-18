<?php
use Hyyan\WPI\Product\Meta;
use PHPUnit\Framework\TestCase;

final class MetadataCopyCompatibilityTest extends TestCase
{
    public function testLegacySynchronizationReceiverGetsNativeCopyArguments(): void
    {
        $original = PLL()->sync;
        $receiver = new class {
            public array $calls = array();
            public function copy_post_metas($from, $to, $lang, $sync = false): void
            {
                $this->calls[] = array($from, $to, $lang, $sync);
            }
        };
        try {
            PLL()->sync = $receiver;
            $handler = (new ReflectionClass(Meta::class))->newInstanceWithoutConstructor();
            $handler->syncProductMeta(11, 12, 'de', false);
            $handler->syncProductMeta(12, 11, 'en', true);
            self::assertSame(array(array(11, 12, 'de', false), array(12, 11, 'en', true)), $receiver->calls);
        } finally {
            PLL()->sync = $original;
        }
    }
}
