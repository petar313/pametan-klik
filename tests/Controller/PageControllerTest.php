<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PageControllerTest extends WebTestCase
{
    #[DataProvider('providePages')]
    public function testPageRenders(string $url, string $lang, string $heading): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $url);

        self::assertResponseIsSuccessful();
        self::assertSame($lang, $crawler->filter('html')->attr('lang'));
        self::assertSelectorTextSame('h1', $heading);
    }

    public static function providePages(): iterable
    {
        yield ['/', 'sr-Cyrl', 'Паметан клик'];
        yield ['/deca', 'sr-Cyrl', 'За децу'];
        yield ['/deca/cet', 'sr-Cyrl', 'Чет'];
        yield ['/roditelji', 'sr-Cyrl', 'За родитеље'];
        yield ['/lat', 'sr-Latn', 'Pametan klik'];
        yield ['/lat/deca/cet', 'sr-Latn', 'Čet'];
        yield ['/en', 'en', 'Smart Click'];
        yield ['/en/kids/chat', 'en', 'Chat'];
        yield ['/en/parents', 'en', 'For parents'];
        yield ['/es', 'es', '¡Navega con inteligencia!'];
        yield ['/es/ninos/chat', 'es', 'Chat'];
        yield ['/es/padres', 'es', 'Para padres'];
        yield ['/kontakt', 'sr-Cyrl', 'Пишите нам'];
        yield ['/lat/kontakt', 'sr-Latn', 'Pišite nam'];
        yield ['/en/contact', 'en', 'Contact us'];
        yield ['/es/contacto', 'es', 'Contacto'];
    }

    public function testLatinPagesContainNoCyrillic(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lat/deca/cet');

        self::assertResponseIsSuccessful();
        self::assertDoesNotMatchRegularExpression('/\p{Cyrillic}/u', $client->getResponse()->getContent());
    }

    public function testAlternatesLinkTranslations(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/deca/cet');

        $alternates = [];
        foreach ($crawler->filter('link[rel="alternate"]') as $link) {
            $alternates[$link->getAttribute('hreflang')] = parse_url($link->getAttribute('href'), \PHP_URL_PATH);
        }

        self::assertSame([
            'sr-Cyrl' => '/deca/cet',
            'sr-Latn' => '/lat/deca/cet',
            'en' => '/en/kids/chat',
            'es' => '/es/ninos/chat',
            'x-default' => '/deca/cet',
        ], $alternates);
        self::assertSame('/deca/cet', parse_url($crawler->filter('link[rel="canonical"]')->attr('href'), \PHP_URL_PATH));
    }

    #[DataProvider('provideMissingPages')]
    public function testMissingPagesAre404(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        self::assertResponseStatusCodeSame(404);
    }

    public static function provideMissingPages(): iterable
    {
        yield 'section of another language' => ['/en/deca'];
        yield 'unknown page' => ['/deca/nepostoji'];
        yield 'index slug' => ['/deca/index'];
        yield 'unknown section' => ['/lat/nesto'];
    }
}
