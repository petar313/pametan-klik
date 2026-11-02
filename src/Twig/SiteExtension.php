<?php

namespace App\Twig;

use App\Site\Site;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Attribute\AsTwigFunction;

final readonly class SiteExtension
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    /** @return array<string, string> locale => hreflang, in language switcher order */
    #[AsTwigFunction('site_locales')]
    public function locales(): array
    {
        return Site::HREFLANG;
    }

    #[AsTwigFunction('hreflang')]
    public function hreflang(?string $locale = null): string
    {
        return Site::HREFLANG[$locale ?? $this->requestStack->getCurrentRequest()->getLocale()];
    }

    /** URL section of an audience ("kids", "parents") in the current locale */
    #[AsTwigFunction('section')]
    public function section(string $audience): string
    {
        return Site::section($this->requestStack->getCurrentRequest()->getLocale(), $audience);
    }
}
