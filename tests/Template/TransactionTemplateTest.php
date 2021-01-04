<?php

declare(strict_types=1);

namespace byrokrat\accounting\Template;

use byrokrat\accounting\Exception\LogicException;
use Prophecy\Argument;

class TransactionTemplateTest extends \PHPUnit\Framework\TestCase
{
    use \Prophecy\PhpUnit\ProphecyTrait;

    public function testExceptionOnInvalidDimension()
    {
        $this->expectException(LogicException::class);
        new TransactionTemplate(dimensions: [null]);
    }

    public function testExceptionOnInvalidAttribute()
    {
        $this->expectException(LogicException::class);
        new TransactionTemplate(attributes: [null]);
    }

    public function testTranslateStrings()
    {
        $translator = $this->prophesize(TranslatorInterface::class);
        $translator->translate('foo')->willReturn('bar');

        $original = new TransactionTemplate(
            transactionDate: 'foo',
            description: 'foo',
            signature: 'foo',
            amount: 'foo',
            quantity: 'foo',
            account: 'foo',
        );

        $translated = $original->translate($translator->reveal());

        $this->assertSame('foo', $original->transactionDate);
        $this->assertSame('foo', $original->description);
        $this->assertSame('foo', $original->signature);
        $this->assertSame('foo', $original->amount);
        $this->assertSame('foo', $original->quantity);
        $this->assertSame('foo', $original->account);

        $this->assertSame('bar', $translated->transactionDate);
        $this->assertSame('bar', $translated->description);
        $this->assertSame('bar', $translated->signature);
        $this->assertSame('bar', $translated->amount);
        $this->assertSame('bar', $translated->quantity);
        $this->assertSame('bar', $translated->account);
    }

    public function testTranslateDimensions()
    {
        $translator = $this->prophesize(TranslatorInterface::class);
        $translator->translate('foo')->willReturn('bar');
        $translator->translate(Argument::any())->willReturn('');

        $original = new TransactionTemplate(dimensions: ['foo']);

        $translated = $original->translate($translator->reveal());

        $this->assertSame(['foo'], $original->dimensions);
        $this->assertSame(['bar'], $translated->dimensions);
    }

    public function testTranslateAttributes()
    {
        $translator = $this->prophesize(TranslatorInterface::class);
        $translator->translate('foo')->willReturn('bar');
        $translator->translate(Argument::any())->willReturn('');

        $original = new TransactionTemplate(attributes: [new AttributeTemplate(key: 'foo')]);

        $translated = $original->translate($translator->reveal());

        $this->assertSame('foo', $original->attributes[0]->key);
        $this->assertSame('bar', $translated->attributes[0]->key);
    }
}
