<?php

namespace App\Controller;

use App\Content\ContentRepository;
use App\Content\Page;
use App\Site\Site;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PageController extends AbstractController
{
    public function __construct(
        private readonly ContentRepository $content,
    ) {
    }

    #[Route(path: [
        'sr_Cyrl' => '/',
        'sr_Latn' => '/lat',
        'en' => '/en',
        'es' => '/es',
    ], name: 'home', methods: ['GET'])]
    public function home(string $_locale): Response
    {
        return $this->renderPage($_locale, null, 'index');
    }

    #[Route(path: [
        'sr_Cyrl' => '/{section}/{slug}',
        'sr_Latn' => '/lat/{section}/{slug}',
        'en' => '/en/{section}/{slug}',
        'es' => '/es/{section}/{slug}',
    ], name: 'page', requirements: ['section' => Site::SECTION_REQUIREMENT, 'slug' => '[a-z0-9-]+'], defaults: ['slug' => null], methods: ['GET'])]
    public function page(string $_locale, string $section, ?string $slug): Response
    {
        // The section home is /deca, never /deca/index.
        if ('index' === $slug) {
            throw $this->createNotFoundException();
        }

        return $this->renderPage($_locale, $section, $slug ?? 'index');
    }

    private function renderPage(string $locale, ?string $section, string $slug): Response
    {
        if (null !== $section && null === Site::audience($locale, $section)) {
            throw $this->createNotFoundException();
        }

        $page = $this->content->find(Site::CONTENT_DIRS[$locale], $section, $slug)
            ?? throw $this->createNotFoundException();

        return $this->render('page.html.twig', [
            'page' => $page,
            'audience' => null === $section ? null : Site::audience($locale, $section),
            'menu' => null === $section ? [] : $this->content->section($page->language, $section),
            'alternates' => $this->alternates($page),
        ]);
    }

    /** @return array<string, string> hreflang => absolute URL of the same page in every locale that has it */
    private function alternates(Page $page): array
    {
        $alternates = [];
        foreach (Site::locales() as $locale) {
            $translation = Site::CONTENT_DIRS[$locale] === $page->language
                ? $page
                : $this->content->findTranslation($page, Site::CONTENT_DIRS[$locale]);
            if (null === $translation) {
                continue;
            }

            $alternates[Site::HREFLANG[$locale]] = $this->urlFor($translation, $locale);
        }

        return $alternates;
    }

    private function urlFor(Page $page, string $locale): string
    {
        if (null === $page->section) {
            return $this->generateUrl('home', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $parameters = ['_locale' => $locale, 'section' => $page->section];
        if ('index' !== $page->slug) {
            $parameters['slug'] = $page->slug;
        }

        return $this->generateUrl('page', $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
