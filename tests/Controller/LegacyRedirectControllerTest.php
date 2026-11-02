<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LegacyRedirectControllerTest extends WebTestCase
{
    /** Every URL the old site listed in urllist.txt redirects once, to a page that exists. */
    #[DataProvider('provideOldUrls')]
    public function testOldUrlRedirectsPermanently(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);

        if ('/' === $path) {
            self::assertResponseIsSuccessful();

            return;
        }

        self::assertResponseStatusCodeSame(301);
        $location = $client->getResponse()->headers->get('Location');
        $client->request('GET', $location);
        self::assertResponseIsSuccessful(\sprintf('%s redirects to %s, which does not load.', $path, $location));
    }

    public static function provideOldUrls(): iterable
    {
        foreach (file(__DIR__.'/../fixtures/urllist.txt', \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES) as $url) {
            $path = parse_url($url, \PHP_URL_PATH);
            yield $path => [$path];
        }
    }

    public function testRedirectTargets(): void
    {
        $client = static::createClient();

        foreach ([
            '/srp/deca/cet.html' => '/deca/cet',
            '/eng/kids/chat.htm' => '/en/kids/chat',
            '/esp/los%20padres/iniciopadres.htm' => '/es/padres',
            '/eng/parents/contact.php' => '/en/contact',
        ] as $old => $new) {
            $client->request('GET', $old);
            self::assertResponseRedirects($new, 301);
        }
    }

    #[DataProvider('provideGoneUrls')]
    public function testJunkIsGone(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);

        self::assertResponseStatusCodeSame(410);
    }

    public static function provideGoneUrls(): iterable
    {
        yield ['/eng/test.html'];
        yield ['/brojac.htm'];
        yield ['/flash/zaglavlje4_en.swf'];
        yield ['/Scripts/mail.php'];
    }

    public function testUnknownOldPageIs404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/srp/nepostoji.htm');

        self::assertResponseStatusCodeSame(404);
    }
}
