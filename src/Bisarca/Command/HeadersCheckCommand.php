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

class HeadersCheckCommand
{
    use UrlAwareTrait;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * Gets the value of headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Sets the value of headers.
     *
     * @param array $headers The headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @param array  $headers
     * @param string $url
     *
     * @return HeadersCheckCommand
     */
    public static function fromHeadersAndUrl(array $headers, string $url): HeadersCheckCommand
    {
        $instance = new self();
        $instance->setHeaders($headers);
        $instance->setUrl($url);

        return $instance;
    }
}
