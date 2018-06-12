<?php

declare(strict_types = 1);

namespace byrokrat\accounting;

use byrokrat\amount\Amount;
use byrokrat\amount\Currency\SEK;
use Prophecy\Argument;

class VerificationTest extends \PHPUnit\Framework\TestCase
{
    use utils\InterfaceAssertionsTrait, utils\PropheciesTrait;

    public function testAccessingTransactions()
    {
        $transA = $this->prophesizeTransaction();
        $transA->setAttribute('ver_num', Argument::any())->shouldBeCalled();
        $transA = $transA->reveal();

        $transB = $this->prophesizeTransaction();
        $transB->setAttribute('ver_num', Argument::any())->shouldBeCalled();
        $transB = $transB->reveal();

        $this->assertSame(
            [$transA, $transB],
            (new Verification)->addTransaction($transA)->addTransaction($transB)->getTransactions()
        );
    }

    public function testAttributable()
    {
        $this->assertAttributable(new Verification);
    }

    public function testDateable()
    {
        $date = new \DateTimeImmutable();

        $this->assertDateable(
            $date,
            (new Verification)->setDate($date)
        );

        $this->assertTrue(
            (new Verification)->getDate() >= $date
        );
    }

    public function testRegistrationDateable()
    {
        $date = new \DateTimeImmutable();

        $this->assertSame(
            $date,
            (new Verification)->setRegistrationDate($date)->getRegistrationDate()
        );

        $this->assertSame(
            $date,
            (new Verification)->setDate($date)->getRegistrationDate(),
            'If registration date is not set the regular date should be returned'
        );
    }

    public function testDescribable()
    {
        $this->assertDescribable(
            'foobar',
            (new Verification)->setDescription('foobar')
        );
    }

    public function testNumerable()
    {
        $this->assertFalse((new Verification)->hasId());

        $this->assertSame(
            10,
            (new Verification)->setId(10)->getId()
        );

        $this->assertTrue((new Verification)->setId(1)->hasId());
    }

    public function testQueryable()
    {
        $trans = $this->prophesizeTransaction();
        $trans->setAttribute('ver_num', Argument::any())->shouldBeCalled();

        $this->assertSame(
            2,
            (new Verification)
                ->addTransaction($trans->reveal())
                ->addTransaction($trans->reveal())
                ->select()->transactions()->count()
        );
    }

    public function testSignable()
    {
        $this->assertSignableSignatureNotSet(new Verification);

        $this->assertSignable(
            $signature = 'signature',
            (new Verification)->setSignature($signature)
        );
    }

    public function transactionArithmeticsProvider()
    {
        return [
            // magnitude        balanced   transaction amounts...
            [new Amount('30'),  true,      new Amount('10'), new Amount('20'), new Amount('-30')],
            [new Amount('200'), true,      new Amount('200'), new Amount('-200')],
            [new SEK('300'),    true,      new SEK('100'), new SEK('200'), new SEK('-300')],
            [new SEK('200'),    true,      new SEK('200'), new SEK('-200')],
            [new Amount('0'),   false,     new Amount('20'), new Amount('-30')],
            [new Amount('0'),   false,     new Amount('200'), new Amount('-100')],
            [new Amount('0'),   false,     new Amount('10'), new Amount('-10'), new Amount('-10')],
            [new Amount('0'),   false,     new SEK('200'), new SEK('-300')],
            [new Amount('0'),   false,     new SEK('200'), new SEK('-100')],
            [new Amount('0'),   false,     new SEK('100'), new SEK('-100'), new SEK('-100')],
        ];
    }

    /**
     * @dataProvider transactionArithmeticsProvider
     */
    public function testTransactionArithmetics(Amount $magnitude, bool $balanced, Amount ...$amounts)
    {
        $verification = new Verification;

        foreach ($amounts as $amount) {
            $trans = $this->prophesizeTransaction($amount);
            $trans->setAttribute('ver_num', Argument::any())->shouldBeCalled();
            $verification->addTransaction($trans->reveal());
        }

        $this->assertSame($balanced, $verification->isBalanced());

        if ($balanced) {
            $this->assertEquals($magnitude, $verification->getMagnitude());
        }
    }

    public function testDeletedTransactionsDoesNotCount()
    {
        $verification = new Verification;

        $trans = $this->prophesizeDeletedTransaction(new Amount('100'));
        $trans->setAttribute('ver_num', Argument::any())->shouldBeCalled();

        $verification->addTransaction($trans->reveal());

        $this->assertSame(0, $verification->getMagnitude()->getInt());
    }

    public function testExceptionOnGetMagnitudeWithUnbalancedVerification()
    {
        $verification = new Verification;
        $verification->setId(1234);

        $trans = $this->prophesizeTransaction(new Amount('100'));
        $trans->setAttribute('ver_num', 1234)->shouldBeCalled();

        $this->expectException(Exception\RuntimeException::CLASS);
        $verification->addTransaction($trans->reveal())->getMagnitude();
    }

    public function testCastToString()
    {
        $transA = $this->prophesizeTransaction();
        $transA->setAttribute('ver_num', Argument::any())->shouldBeCalled();
        $transA->__toString()->willReturn('1234: 100');

        $transB = $this->prophesizeTransaction();
        $transB->setAttribute('ver_num', Argument::any())->shouldBeCalled();
        $transB->__toString()->willReturn('4321: -100');

        $this->assertSame(
            "[20170208] Verification\n * 1234: 100\n * 4321: -100",
            (string)(new Verification)
                ->addTransaction($transA->reveal())
                ->addTransaction($transB->reveal())
                ->setDate(new \DateTime('20170208'))
                ->setDescription('Verification')
        );
    }
}
