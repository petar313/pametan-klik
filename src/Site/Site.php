<?php

namespace App\Site;

/**
 * Languages, script variants and audience sections of the site.
 *
 * Serbian exists twice (Cyrillic and Latin) but has one set of content files:
 * the Latin pages are transliterated from the Cyrillic ones on the way out.
 */
final class Site
{
    public const string AUDIENCE_KIDS = 'kids';
    public const string AUDIENCE_PARENTS = 'parents';

    /** Locale => directory under content/ */
    public const array CONTENT_DIRS = [
        'sr_Cyrl' => 'sr',
        'sr_Latn' => 'sr',
        'en' => 'en',
        'es' => 'es',
    ];

    /** Locale => [audience => URL section] */
    public const array SECTIONS = [
        'sr_Cyrl' => [self::AUDIENCE_KIDS => 'deca', self::AUDIENCE_PARENTS => 'roditelji'],
        'sr_Latn' => [self::AUDIENCE_KIDS => 'deca', self::AUDIENCE_PARENTS => 'roditelji'],
        'en' => [self::AUDIENCE_KIDS => 'kids', self::AUDIENCE_PARENTS => 'parents'],
        'es' => [self::AUDIENCE_KIDS => 'ninos', self::AUDIENCE_PARENTS => 'padres'],
    ];

    /** Locale => value of <html lang> and hreflang */
    public const array HREFLANG = [
        'sr_Cyrl' => 'sr-Cyrl',
        'sr_Latn' => 'sr-Latn',
        'en' => 'en',
        'es' => 'es',
    ];

    public const string SECTION_REQUIREMENT = 'deca|roditelji|kids|parents|ninos|padres';

    public static function locales(): array
    {
        return array_keys(self::CONTENT_DIRS);
    }

    public static function audience(string $locale, string $section): ?string
    {
        $audience = array_search($section, self::SECTIONS[$locale] ?? [], true);

        return false === $audience ? null : $audience;
    }

    public static function section(string $locale, string $audience): string
    {
        return self::SECTIONS[$locale][$audience];
    }
}
