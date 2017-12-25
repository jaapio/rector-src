<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Dynamic\ParentTypehintedArgumentRector;

use Rector\Rector\Dynamic\ParentTypehintedArgumentRector;
use Rector\Testing\PHPUnit\AbstractConfigurableRectorTestCase;

final class ParentTypehintedArgumentRectorTest extends AbstractConfigurableRectorTestCase
{
    public function test(): void
    {
        $this->doTestFileMatchesExpectedContent(
            __DIR__ . '/Wrong/wrong.php.inc',
            __DIR__ . '/Correct/correct.php.inc'
        );

        $this->doTestFileMatchesExpectedContent(
            __DIR__ . '/Wrong/wrong2.php.inc',
            __DIR__ . '/Correct/correct2.php.inc'
        );
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config/rector.yml';
    }

    /**
     * @return string[]
     */
    protected function getRectorClasses(): array
    {
        return [ParentTypehintedArgumentRector::class];
    }
}
