<?php

namespace spec\Sylake\SyliusConsumerPlugin\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class TranslationsSpec extends ObjectBehavior
{
    function it_ignores_invalid_locales()
    {
        $this->beConstructedWith([
            'invalidlocale' => 'translations',
        ]);

        $this->shouldIterateAs([]);
    }

    function it_ignores_invalid_translations()
    {
        $this->beConstructedWith([
            'en_US' => ['array', 'is', 'invalid'],
        ]);

        $this->shouldIterateAs([]);
    }

    function it_does_not_throw_any_exceptions_if_translations_are_valid()
    {
        $this->beConstructedWith([
            'en' => 'Tree',
            'pl_PL' => 'Drzewo',
        ]);

        $this->shouldIterateAs([
            'en' => 'Tree',
            'pl_PL' => 'Drzewo',
        ]);
    }
}
