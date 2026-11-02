<?php

namespace App\EventListener;

use App\Site\Transliterator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Serves the Latin-script Serbian pages by transliterating the rendered
 * Cyrillic HTML. Markup, URLs and attribute names are ASCII, so only the
 * Serbian text changes.
 */
#[AsEventListener(event: KernelEvents::RESPONSE)]
final readonly class LatinScriptListener
{
    public function __construct(
        private Transliterator $transliterator,
    ) {
    }

    public function __invoke(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if ('sr_Latn' !== $event->getRequest()->getLocale()
            || !str_starts_with((string) $response->headers->get('Content-Type', 'text/html'), 'text/html')
            || false === $response->getContent()
        ) {
            return;
        }

        $response->setContent($this->transliterator->toLatin($response->getContent()));
    }
}
