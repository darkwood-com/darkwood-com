<?php

declare(strict_types=1);

namespace App\Enum;

use Symfony\Component\Emoji\EmojiTransliterator;

enum ArticleReactionEmoji: string
{
    case Plus1 = 'plus1';
    case Heart = 'heart';
    case Smile = 'smile';
    case Party = 'party';
    case Rocket = 'rocket';
    case Eyes = 'eyes';
    case Clapclap = 'clapclap';
    case Mindblown = 'mindblown';

    public function textShortCode(): string
    {
        return match ($this) {
            self::Plus1 => '+1',
            self::Heart => 'heart',
            self::Smile => 'smile',
            self::Party => 'tada',
            self::Rocket => 'rocket',
            self::Eyes => 'eyes',
            self::Clapclap => 'clap',
            self::Mindblown => 'exploding_head',
        };
    }

    public function emojifyCatalog(): string
    {
        return self::Mindblown === $this ? 'github' : 'text';
    }

    public function glyph(): string
    {
        static $transliterators = [];

        $catalog = $this->emojifyCatalog().'-emoji';
        $transliterator = $transliterators[$catalog] ??= EmojiTransliterator::create($catalog);
        $result = $transliterator->transliterate(':'.$this->textShortCode().':');

        if (str_starts_with($result, ':')) {
            return $this->fallbackGlyph();
        }

        return $result;
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        return self::cases();
    }

    public static function tryFromRequest(?string $value): ?self
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return self::tryFrom($value);
    }

    private function fallbackGlyph(): string
    {
        return match ($this) {
            self::Plus1 => '👍',
            self::Heart => '❤️',
            self::Smile => '😄',
            self::Party => '🎉',
            self::Rocket => '🚀',
            self::Eyes => '👀',
            self::Clapclap => '👏',
            self::Mindblown => '🤯',
        };
    }
}
