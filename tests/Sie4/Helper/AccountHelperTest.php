<?php

declare(strict_types = 1);

namespace byrokrat\accounting\Sie4\Helper;

use byrokrat\accounting\utils\PropheciesTrait;
use byrokrat\accounting\Account;

/**
 * @covers \byrokrat\accounting\Sie4\Helper\AccountHelper
 */
class AccountHelperTest extends \PHPUnit_Framework_TestCase
{
    use PropheciesTrait;

    /**
     * @var ObjectProphecy Created in setup()
     */
    private $accountFactoryProphecy;

    /**
     * @var ObjectProphecy[] Account prophecies created by the account factory
     */
    private $accountProphecies = [];

    /**
     * @var Object Created in setup()
     */
    private $accountHelper;

    public function setup()
    {
        $this->accountProphecies = [];
        $this->accountFactoryProphecy = $this->prophesizeAccountFactory($this->accountProphecies);
        $this->accountHelper = $this->getMockForTrait(AccountHelper::CLASS);
        $this->accountHelper->setAccountFactory($this->accountFactoryProphecy->reveal());
    }

    public function testCreateAccount()
    {
        $this->accountHelper->onKonto(1234, 'foobar');
        $this->accountFactoryProphecy->createAccount(1234, 'foobar')->shouldHaveBeenCalled();
    }

    public function testGetAccount()
    {
        $this->assertSame(
            $this->accountHelper->onKonto(1234, 'foobar'),
            $this->accountHelper->getAccount(1234)
        );
    }

    public function testGetUnspecifiedAccount()
    {
        $this->assertSame(
            'UNSPECIFIED',
            $this->accountHelper->getAccount(1234)->getDescription()
        );
    }

    public function testEnhet()
    {
        $accountSpie = $this->prophesize(Account::CLASS);
        $accountSpie->setAttribute('unit', 'sek')->willReturn($accountSpie)->shouldBeCalled();
        $this->accountFactoryProphecy->createAccount(1234, 'UNSPECIFIED')->willReturn($accountSpie->reveal());
        $this->accountHelper->onEnhet(1234, 'sek');
    }

    public function testSru()
    {
        $accountSpie = $this->prophesize(Account::CLASS);
        $accountSpie->setAttribute('sru', 5678)->willReturn($accountSpie)->shouldBeCalled();
        $this->accountFactoryProphecy->createAccount(1234, 'UNSPECIFIED')->willReturn($accountSpie->reveal());
        $this->accountHelper->onSru(1234, 5678);
    }

    public function testSetAccountType()
    {
        $originalAccount = $this->accountHelper->getAccount(1234);
        $newAccount = $this->accountHelper->onKtyp(1234, 'S');

        $this->assertNotSame(
            $originalAccount,
            $newAccount
        );

        $this->assertNotInstanceOf(
            Account\Debt::CLASS,
            $originalAccount
        );

        $this->assertInstanceOf(
            Account\Debt::CLASS,
            $newAccount
        );

        $this->assertSame(
            $originalAccount->getNumber(),
            $newAccount->getNumber()
        );

        $this->assertSame(
            $originalAccount->getDescription(),
            $newAccount->getDescription()
        );

        $this->accountProphecies[1234]->getAttributes()->shouldHaveBeenCalled();

        $this->assertSame(
            $originalAccount->getAttributes(),
            $newAccount->getAttributes()
        );

        $this->assertSame(
            $newAccount,
            $this->accountHelper->getAccount(1234)
        );
    }

    public function testSetUnvalidAccountType()
    {
        $this->accountHelper->expects($this->exactly(2))
            ->method('registerError')
            ->with($this->anything());

        $originalAccount = $this->accountHelper->getAccount(1234);
        $newAccount = $this->accountHelper->onKtyp(1234, 'not-a-valid-account-type-identifier');

        $this->assertSame(
            $originalAccount,
            $newAccount
        );
    }
}
