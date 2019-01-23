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
 * Copyright 2016-19 Hannes Forsgård
 */

declare(strict_types = 1);

namespace byrokrat\accounting\Dimension;

use byrokrat\accounting\Container;

/**
 * Facilitates the creation of account objects
 */
class AccountFactory
{
    public function createAccount(string $number, string $description = ''): AccountInterface
    {
        $iNumber = intval($number);

        if ($iNumber < 2000) {
            return new AssetAccount($number, $description);
        }

        if ($iNumber < 3000) {
            return new DebtAccount($number, $description);
        }

        if ($iNumber < 4000) {
            return new EarningAccount($number, $description);
        }

        return new CostAccount($number, $description);
    }

    public function createAccounts(array $definitions): Container
    {
        $accounts = [];

        foreach ($definitions as $number => $description) {
            $accounts[] = $this->createAccount((string)$number, (string)$description);
        }

        return new Container(...$accounts);
    }
}
