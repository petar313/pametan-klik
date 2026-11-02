<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Yaml\Yaml;

/**
 * Permanent redirects from the old static site, so search engines and
 * bookmarks land on the new pages. The map lives in config/legacy_redirects.yaml.
 */
final class LegacyRedirectController extends AbstractController
{
    private const array GONE_PREFIXES = ['flash/', 'Scripts/'];

    public function __construct(
        #[Autowire('%kernel.project_dir%/config/legacy_redirects.yaml')]
        private readonly string $mapFile,
    ) {
    }

    #[Route('/{path}', name: 'legacy_redirect', requirements: ['path' => '.+\.(?:html?|php|swf)'], methods: ['GET', 'HEAD'], priority: -10)]
    public function __invoke(string $path): Response
    {
        $target = Yaml::parseFile($this->mapFile)[$path] ?? null;

        if ('gone' === $target || array_any(self::GONE_PREFIXES, static fn (string $p): bool => str_starts_with($path, $p))) {
            return new Response('', Response::HTTP_GONE);
        }

        if (!\is_array($target)) {
            throw $this->createNotFoundException();
        }

        $route = $target['route'];
        unset($target['route']);

        return new RedirectResponse($this->generateUrl($route, $target), Response::HTTP_MOVED_PERMANENTLY);
    }
}
