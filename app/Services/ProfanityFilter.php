<?php

namespace App\Services;

class ProfanityFilter
{
    protected array $words = [];

    protected array $defaultWords = [
        "anjing",
        "babi",
        "bajingan",
        "banci",
        "bangsat",
        "bedebah",
        "brengsek",
        "kampret",
        "keparat",
        "kontol",
        "memek",
        "ngentot",
        "pecun",
        "perek",
        "sialan",
        "silit",
        "tai",
        "tolol",
        "goblok",
        "idiot",
        "bego",
        "shit",
        "fuck",
        "asshole",
        "bastard",
        "bitch",
        "dick",
        "piss",
        "slut",
        "whore",
        "crap",
        "4njing",
    ];

    public function __construct(?array $customWords = null)
    {
        if ($customWords !== null) {
            $this->words = $customWords;
        }
    }

    public function words(): array
    {
        return $this->words ?: $this->defaultWords;
    }

    public function containsProfanity(string $text): bool
    {
        $lower = mb_strtolower($text);

        foreach ($this->words() as $word) {
            $pattern = "/\b" . preg_quote(mb_strtolower($word), "/") . "\b/u";
            if (preg_match($pattern, $lower)) {
                return true;
            }
        }

        return false;
    }

    public function filter(string $text): string
    {
        foreach ($this->words() as $word) {
            $pattern = "/\b" . preg_quote(mb_strtolower($word), "/") . "\b/ui";
            $text = preg_replace_callback(
                $pattern,
                function ($matches) {
                    $match = $matches[0];
                    $keep = min(3, mb_strlen($match));
                    return mb_substr($match, 0, $keep) .
                        str_repeat("*", mb_strlen($match) - $keep);
                },
                $text,
            );
        }

        return $text;
    }
}
