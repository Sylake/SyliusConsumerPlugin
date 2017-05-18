<?php

namespace spec\Sylake\SyliusConsumerPlugin\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class TranslationsSpec extends ObjectBehavior
{
    function it_throws_invalid_argument_exception_if_locale_is_invalid()
    {
        $this->beConstructedWith([
            'invalidlocale' => 'translations',
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_invalid_argument_exception_if_translation_is_invalid()
    {
        $this->beConstructedWith([
            'en_US' => ['array', 'is', 'invalid'],
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_does_not_throw_any_exceptions_if_translations_are_valid()
    {
        $this->beConstructedWith([
            'en' => 'Tree',
            'pl_PL' => 'Drzewo',
        ]);

        $this->shouldImplement(\Traversable::class);
    }
}
