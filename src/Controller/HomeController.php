<?php

namespace App\Controller;

use Twig\Environment;
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

    #[Route('/{_locale}/saveti-za-decu', name: 'saveti_deca', requirements: ['_locale' => 'sr|en|es'])]
    public function savetiDeca(): Response
    {
        return $this->render('pages/saveti_deca.html.twig', [
        ]);
    }

    #[Route('/{_locale}/saveti-za-roditelje', name: 'saveti_roditelji', requirements: ['_locale' => 'sr|en|es'])]
    public function savetiRoditelji(): Response
    {
        return $this->render('page.html.twig', [
            'template' => 'pages/saveti_roditelji.html.twig',
        ]);
    }

    #[Route('/{_locale}/kontakt', name: 'kontakt', requirements: ['_locale' => 'sr|en|es'])]
    public function kontakt(): Response
    {
        return $this->render('page.html.twig', [
            'template' => 'pages/kontakt.html.twig',
        ]);
    }
}