<?php

namespace App\Tests\Site;

use App\Site\Transliterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TransliteratorTest extends TestCase
{
    #[DataProvider('provideText')]
    public function testToLatin(string $cyrillic, string $latin): void
    {
        self::assertSame($latin, (new Transliterator())->toLatin($cyrillic));
    }

    public static function provideText(): iterable
    {
        yield 'all letters' => ['абвгдђежзијклљмнњопрстћуфхцчџш', 'abvgdđežzijklljmnnjoprstćufhcčdžš'];
        yield 'capital digraph in a word' => ['Љубав и Џеп', 'Ljubav i Džep'];
        yield 'all caps word' => ['КЛИКНИ ПАМЕТНО, ЉУДИ, ЏЕМПЕР', 'KLIKNI PAMETNO, LJUDI, DŽEMPER'];
        yield 'markup and Latin untouched' => ['<a href="/deca/cet">Чет</a> Facebook', '<a href="/deca/cet">Čet</a> Facebook'];
    }
}
