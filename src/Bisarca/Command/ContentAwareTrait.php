<?php

/*
 * Bisarca: the open source network crawling framework.
 * Copyright (C) 2016 Emanuele Minotto
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Bisarca\Command;

trait ContentAwareTrait
{
    /**
     * @var string
     */
    private $content = '';

    /**
     * Gets the value of content.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Sets the value of content.
     *
     * @param string $content The content.
     */
    public function setContent(string $content)
    {
        $this->content = $content;
    }

    /**
     * @param string $content
     *
     * @return static
     */
    public static function fromContent(string $content)
    {
        $instance = new static();
        $instance->setContent($content);

        return $instance;
    }
}
