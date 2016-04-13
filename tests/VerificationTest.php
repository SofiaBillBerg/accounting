<?php

namespace byrokrat\accounting;

use byrokrat\amount\Amount;

class VerificationTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetText()
    {
        $v = new Verification();
        $v->setText('test');
        $this->assertEquals($v->getText(), 'test');
    }

    public function testSetGetDate()
    {
        $v = new Verification('test');
        $now = new \DateTime();
        $this->assertTrue($v->getDate() <= $now);

        $v = new Verification('test', $now);
        $this->assertTrue($v->getDate() == $now);

        $v = new Verification('test');
        $v->setDate($now);
        $this->assertTrue($v->getDate() == $now);
    }

    public function testGetTransactions()
    {
        $bank = new Account\Asset(1920, 'Bank');
        $income = new Account\Earning(3000, 'Income');
        $trans = array(
            new Transaction($bank, new Amount('100')),
            new Transaction($bank, new Amount('200')),
            new Transaction($income, new Amount('-300')),
        );
        $v = new Verification('test');
        foreach ($trans as $t) {
            $v->addTransaction($t);
        }
        $this->assertEquals($trans, $v->getTransactions());
    }

    public function testGetAccounts()
    {
        $bank = new Account\Asset(1920, 'Bank');
        $income = new Account\Earning(3000, 'Income');

        $trans = array(
            new Transaction($bank, new Amount('100')),
            new Transaction($bank, new Amount('200')),
            new Transaction($income, new Amount('-300')),
        );

        $v = new Verification('test');
        foreach ($trans as $t) {
            $v->addTransaction($t);
        }

        $a = $v->getAccounts();

        $this->assertEquals(2, count($a));
        $this->assertTrue(isset($a[1920]));
        $this->assertTrue(isset($a[3000]));
    }

    public function testIsBalanced()
    {
        $bank = new Account\Asset(1920, 'Bank');
        $income = new Account\Earning(3000, 'Income');

        //A balanced verification
        $trans = array(
            new Transaction($bank, new Amount('100')),
            new Transaction($bank, new Amount('200')),
            new Transaction($income, new Amount('-300')),
        );
        $v = new Verification('test');
        foreach ($trans as $t) {
            $v->addTransaction($t);
        }
        $this->assertTrue($v->isBalanced());

        // A unbalanced verification
        $trans = array(
            new Transaction($bank, new Amount('100')),
            new Transaction($income, new Amount('-300')),
        );
        $v = new Verification('test');
        foreach ($trans as $t) {
            $v->addTransaction($t);
        }
        $this->assertFalse($v->isBalanced());
    }

    public function testGetDifference()
    {
        $bank = new Account\Asset(1920, 'Bank');
        $income = new Account\Earning(3000, 'Income');

        //A balanced verification
        $trans = array(
            new Transaction($bank, new Amount('100')),
            new Transaction($bank, new Amount('200')),
            new Transaction($income, new Amount('-300')),
        );
        $v = new Verification('test');
        foreach ($trans as $t) {
            $v->addTransaction($t);
        }
        $this->assertEquals((string)new Amount('0'), (string)$v->getDifference());

        // A negaitve verification
        $trans = array(
            new Transaction($bank, new Amount('100')),
            new Transaction($income, new Amount('-300')),
        );
        $v = new Verification('test');
        foreach ($trans as $t) {
            $v->addTransaction($t);
        }
        $this->assertEquals((string)new Amount('-200'), (string)$v->getDifference());

        // A positive verification
        $trans = array(
            new Transaction($bank, new Amount('200')),
            new Transaction($income, new Amount('-100')),
        );
        $v = new Verification('test');
        foreach ($trans as $t) {
            $v->addTransaction($t);
        }
        $this->assertEquals((string)new Amount('100'), (string)$v->getDifference());
    }
}
