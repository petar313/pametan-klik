<?php

namespace App\Site;

/**
 * Serbian Cyrillic to Latin. The mapping is one-to-one per letter, so the
 * Latin site never drifts from the Cyrillic source.
 */
final class Transliterator
{
    private const array MAP = [
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Ђ' => 'Đ', 'Е' => 'E', 'Ж' => 'Ž',
        'З' => 'Z', 'И' => 'I', 'Ј' => 'J', 'К' => 'K', 'Л' => 'L', 'Љ' => 'Lj', 'М' => 'M', 'Н' => 'N',
        'Њ' => 'Nj', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'Ћ' => 'Ć', 'У' => 'U',
        'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Č', 'Џ' => 'Dž', 'Ш' => 'Š',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'ђ' => 'đ', 'е' => 'e', 'ж' => 'ž',
        'з' => 'z', 'и' => 'i', 'ј' => 'j', 'к' => 'k', 'л' => 'l', 'љ' => 'lj', 'м' => 'm', 'н' => 'n',
        'њ' => 'nj', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'ћ' => 'ć', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'č', 'џ' => 'dž', 'ш' => 'š',
    ];

    public function toLatin(string $text): string
    {
        // Љ, Њ and Џ become LJ, NJ and DŽ inside an all-caps word ("ЉУБАВ" => "LJUBAV").
        $text = preg_replace_callback(
            '/[ЉЊЏ](?=[\p{Lu}])|(?<=[\p{Lu}])[ЉЊЏ]/u',
            static fn (array $m): string => mb_strtoupper(self::MAP[$m[0]]),
            $text,
        );

        return strtr($text, self::MAP);
    }
}
