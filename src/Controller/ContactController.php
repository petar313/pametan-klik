<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactController extends AbstractController
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator,
        #[Autowire(service: 'limiter.contact_form')]
        private readonly RateLimiterFactoryInterface $contactLimiter,
        #[Autowire('%env(CONTACT_FROM)%')]
        private readonly string $from,
        #[Autowire('%env(CONTACT_TO)%')]
        private readonly string $to,
    ) {
    }

    #[Route(path: [
        'sr_Cyrl' => '/kontakt',
        'sr_Latn' => '/lat/kontakt',
        'en' => '/en/contact',
        'es' => '/es/contacto',
    ], name: 'contact', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A filled honeypot means a bot: look successful, send nothing.
            if ('' === (string) $form->get('website')->getData()) {
                if (!$this->contactLimiter->create($request->getClientIp())->consume()->isAccepted()) {
                    $this->addFlash('error', 'contact.rate_limited');

                    return $this->redirectToRoute('contact');
                }

                $this->send($form->getData());
            }

            $this->addFlash('success', 'contact.sent');

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact.html.twig', ['form' => $form]);
    }

    /** @param array{name: string, email: string, message: string} $data */
    private function send(array $data): void
    {
        // From is our own address so the mail passes SPF/DMARC; the visitor goes in Reply-To.
        $email = (new Email())
            ->from(new Address($this->from, 'pametanklik.rs'))
            ->to($this->to)
            ->replyTo(new Address($data['email'], $data['name']))
            ->subject($this->translator->trans('contact.subject', locale: 'sr_Latn'))
            ->text(\sprintf("%s <%s>\n\n%s", $data['name'], $data['email'], $data['message']));

        $this->mailer->send($email);
    }
}
