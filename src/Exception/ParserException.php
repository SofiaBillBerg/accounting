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
 * Copyright 2016-17 Hannes Forsgård
 */

namespace byrokrat\accounting\Exception;

/**
 * Exception thrown when a parsing action fails
 */
class ParserException extends RuntimeException
{
    /**
     * @var string[] Registered log messages
     */
    private $log = [];

    /**
     * Load log messages at construct
     *
     * @param string[] $log
     */
    public function __construct(array $log)
    {
        $this->log = $log;
        parent::__construct(
            "Parsing failed due to the following issues:\n" . implode("\n", $this->getLog())
        );
    }

    /**
     * Get registered messages
     *
     * @return string[]
     */
    public function getLog(): array
    {
        return $this->log;
    }
}
