<?php

namespace App\Content;

final readonly class Page
{
    public function __construct(
        /** Directory under content/, e.g. "sr" */
        public string $language,
        /** URL section ("deca", "kids", ...) or null for the language home page */
        public ?string $section,
        /** File name without .md; "index" for a home page */
        public string $slug,
        /** Shared by the translations of one page */
        public string $key,
        public string $title,
        public string $description,
        public string $html,
        /** Position in the section menu */
        public int $order = 0,
    ) {
    }
}
