<?php

/**
 * This file is part of byrokrat/accounting.
 *
 * byrokrat/accounting is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * byrokrat/accounting is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with byrokrat/accounting. If not, see <http://www.gnu.org/licenses/>.
 *
 * Copyright 2016-21 Hannes Forsgård
 */

declare(strict_types=1);

namespace byrokrat\accounting\Sie4\Parser;

use byrokrat\accounting\AttributableInterface;
use byrokrat\accounting\Dimension\AccountInterface;
use byrokrat\accounting\Verification\VerificationInterface;
use byrokrat\accounting\Verification\Verification;
use byrokrat\amount\Currency;

class AbstractParser
{
    /** @var array<mixed> */
    protected array $parsedItems = [];

    /** @var array<mixed> */
    protected array $parsedAttributes = [];

    public function __construct(
        private Logger $logger,
        private AccountBuilder $accountBuilder,
        private CurrencyBuilder $currencyBuilder,
        private DimensionBuilder $dimensionBuilder
    ) {}

    protected function resetInternalState(): void
    {
        $this->parsedAttributes = [];
        $this->parsedItems = [];
    }

    /**
     * @return array<mixed>
     */
    protected function getParsedAttributes(): array
    {
        return $this->parsedAttributes;
    }

    /**
     * @return array<mixed>
     */
    protected function getParsedItems(): array
    {
        return $this->parsedItems;
    }

    protected function getAccountBuilder(): AccountBuilder
    {
        return $this->accountBuilder;
    }

    protected function getCurrencyBuilder(): CurrencyBuilder
    {
        return $this->currencyBuilder;
    }

    protected function getDimensionBuilder(): DimensionBuilder
    {
        return $this->dimensionBuilder;
    }

    protected function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @param string $series The series verification should be a part of
     * @param array<array> $transactionData
     * @TODO The $transactionData structure needs to be documentet or preferably refactored
     * @TODO Why not pass $verificationId as int
     */
    protected function createVerification(
        string $series,
        string $verificationId,
        \DateTimeImmutable $transactionDate,
        string $description = '',
        \DateTimeImmutable $registrationDate = null,
        string $signature = '',
        array $transactionData = []
    ): VerificationInterface {
        $transactions = [];

        foreach ($transactionData as $data) {
            // @TODO check the data integrity here! includes class name..
            // @TODO why not use templating here?
            // @TODO pass using named arguments
            $transactions[] = new $data['type'](
                intval($verificationId),
                $data['date'] ?: $transactionDate,
                $data['description'] ?: $description,
                $data['signature'] ?: $signature,
                $data['amount'],
                $data['quantity'],
                $data['account'],
                $data['dimensions']
            );
        }

        return new Verification(
            id: intval($verificationId),
            transactionDate: $transactionDate,
            registrationDate: $registrationDate,
            description: $description,
            signature: $signature,
            transactions: $transactions,
            attributes: ['series' => $series]
        );
    }

    /**
     * Helper that writes an attribute to an attributable
     */
    protected function writeAttribute(AttributableInterface $attr, string $key, mixed $value, string $year = ''): void
    {
        if ('' == $year || '0' == $year) {
            $attr->setAttribute($key, $value);
        }

        if ('' != $year) {
            $attr->setAttribute("{$key}[$year]", $value);
        }
    }

    /**
     * Assert that $expr is thruthy and log a warning if not
     */
    protected function assert(mixed $expr, string $failureMessage): bool
    {
        if ($expr) {
            return true;
        }

        $this->getLogger()->log('warning', $failureMessage);

        return false;
    }

    /**
     * Assert that $expr is an array and log warning if not
     */
    protected function assertArray(mixed $expr, string $failureMessage = 'Expected a set of values'): bool
    {
        return $this->assert(is_array($expr), $failureMessage);
    }

    /**
     * Assert that $expr is a boolen and log warning if not
     */
    protected function assertBool(mixed $expr, string $failureMessage = 'Expected bool (1 or 0)'): bool
    {
        return $this->assert(is_bool($expr), $failureMessage);
    }

    /**
     * Assert that $expr is an integer and log warning if not
     */
    protected function assertInt(mixed $expr, string $failureMessage = 'Expected integer'): bool
    {
        return $this->assert(is_int($expr), $failureMessage);
    }

    /**
     * Assert that $expr is a string and log warning if not
     */
    protected function assertString(mixed $expr, string $failureMessage = 'Expected string'): bool
    {
        return $this->assert(is_string($expr), $failureMessage);
    }

    /**
     * Assert that $expr is an account and log warning if not
     */
    protected function assertAccount(mixed $expr, string $failureMessage = 'Expected account'): bool
    {
        return $this->assert(is_object($expr) && $expr instanceof AccountInterface, $failureMessage);
    }

    /**
     * Assert that $expr is a monetary amount and log warning if not
     */
    protected function assertAmount(mixed $expr, string $failureMessage = 'Expected monetary amount'): bool
    {
        return $this->assert(is_object($expr) && $expr instanceof Currency, $failureMessage);
    }

    /**
     * Assert that $expr is a Date and log warning if not
     */
    protected function assertDate(mixed $expr, string $failureMessage = 'Expected date'): bool
    {
        return $this->assert(is_object($expr) && $expr instanceof \DateTimeImmutable, $failureMessage);
    }
}
