<?php

namespace Tests\Sylake\SyliusConsumerPlugin\Model;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sylake\SyliusConsumerPlugin\Model\Translations;

final class TranslationsTest extends TestCase
{
    /**
     * @test
     */
    public function it_represents_translations()
    {
        $translations = [
            'en' => 'Tree',
            'pl_PL' => 'Drzewo',
        ];

        $traversedTranslations = [];
        foreach (new Translations($translations) as $locale => $translation) {
            $traversedTranslations[$locale] = $translation;
        }

        Assert::assertSame($translations, $traversedTranslations);
    }
}
