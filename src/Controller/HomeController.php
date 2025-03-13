<?php

namespace App\Controller;

use Twig\Environment;
use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    #[Route('/', name: 'default-home')]
    public function defaultHome(): Response
    {
        return new Response($this->twig->render('home.html.twig'));
    }

    #[Route('/{_locale}', name: 'home', requirements: ['_locale' => 'sr|en|es'])]
    public function home($locale = 'sr'): Response
    {
        return new Response($this->twig->render('home.html.twig'));
    }



    #[Route('/{_locale}/contact', name: 'contact', requirements: ['_locale' => 'sr|en|es'])]
    public function kontakt(Request $request): Response
    {

        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle form submission, e.g., send an email

            $this->addFlash('success', 'Your message has been sent.');

            return $this->redirectToRoute('contact', ['_locale' => $request->getLocale()]);
        }

        return $this->render('contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}