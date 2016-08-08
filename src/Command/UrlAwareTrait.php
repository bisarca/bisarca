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

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

trait UrlAwareTrait
{
    /**
     * @var UriInterface
     */
    private $url;

    /**
     * @return UriInterface
     */
    public function getUrl(): UriInterface
    {
        return $this->url ?: new Uri();
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $this->url = new Uri($url);
    }

    /**
     * @param mixed $url
     *
     * @return static
     */
    public static function fromUrl(string $url)
    {
        $instance = new static();
        $instance->setUrl($url);

        return $instance;
    }
}
