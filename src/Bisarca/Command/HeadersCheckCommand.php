<?php

/*
 * This file is part of the bisarca/bisarca package.
 *
 * (c) Emanuele Minotto <minottoemanuele@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @param array $headers The headers.
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
