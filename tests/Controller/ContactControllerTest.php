<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ContactControllerTest extends WebTestCase
{
    public function testMessageIsSentToTheOffice(): void
    {
        $client = static::createClient();
        $this->submit($client, ['name' => 'Ana', 'email' => 'ana@example.com', 'message' => 'Pitanje o četu za decu.']);

        self::assertEmailCount(1);
        $email = self::getMailerMessage();
        self::assertEmailAddressContains($email, 'To', 'office@items.rs');
        self::assertEmailAddressContains($email, 'From', 'noreply@pametanklik.rs');
        self::assertEmailAddressContains($email, 'Reply-To', 'ana@example.com');
        self::assertEmailTextBodyContains($email, 'Pitanje o četu za decu.');

        self::assertResponseRedirects('/en/contact');
        $client->followRedirect();
        self::assertSelectorTextContains('.flash--success', 'Your message has been sent');
    }

    public function testHoneypotSendsNothing(): void
    {
        $client = static::createClient();
        $this->submit($client, ['name' => 'Bot', 'email' => 'bot@example.com', 'message' => 'Buy cheap things now!', 'website' => 'http://spam.example']);

        self::assertEmailCount(0);
        self::assertResponseRedirects('/en/contact');
    }

    public function testInvalidInputShowsErrors(): void
    {
        $client = static::createClient();
        $this->submit($client, ['name' => '', 'email' => 'not-an-email', 'message' => 'short']);

        self::assertEmailCount(0);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorCount(3, '.contact-form ul li');
    }

    private function submit(KernelBrowser $client, array $values): void
    {
        $crawler = $client->request('GET', '/en/contact');
        $form = $crawler->selectButton('Send')->form();
        foreach ($values as $field => $value) {
            $form['contact['.$field.']'] = $value;
        }
        $client->submit($form, [], ['HTTP_ORIGIN' => 'http://localhost']);
    }
}
