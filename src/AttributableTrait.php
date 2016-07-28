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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with byrokrat/accounting. If not, see <http://www.gnu.org/licenses/>.
 *
 * Copyright 2016 Hannes Forsgård
 */

declare(strict_types = 1);

namespace byrokrat\accounting;

/**
 * Implements setAttribute and getAttribute
 */
trait AttributableTrait
{
    /**
     * @var array Registered attributes
     */
    private $attributes = [];

    /**
     * Implements Attributable::setAttribute
     */
    public function setAttribute(string $name, $value)
    {
        $this->attributes[strtolower($name)] = $value;
    }

    /**
     * Implements Attributable::getAttribute
     */
    public function getAttribute(string $name)
    {
        return $this->attributes[strtolower($name)] ?? '';
    }

    /**
     * Implements Attributable::getAttributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
