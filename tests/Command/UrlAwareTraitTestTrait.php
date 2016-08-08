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

trait UrlAwareTraitTestTrait
{
    public function testGetUrl()
    {
        $this->assertInstanceOf(UriInterface::class, $this->object->getUrl());
    }

    /**
     * @depends testGetUrl
     */
    public function testSetUrl()
    {
        $url = 'http://www.example.com/';

        $this->object->setUrl($url);

        $extracted = $this->object->getUrl();

        $this->assertInstanceOf(Uri::class, $extracted);
        $this->assertEquals($url, $extracted);

        $url = new Uri($url);

        $this->object->setUrl($url);

        $extracted = $this->object->getUrl();

        $this->assertInstanceOf(Uri::class, $extracted);
        $this->assertEquals($url, $extracted);
    }

    /**
     * @depends testSetUrl
     */
    public function testFromUrl()
    {
        $url = 'http://www.example.com/';
        $class = get_class($this->object);

        $object = $class::fromUrl($url);
        $extracted = $object->getUrl();

        $this->assertInstanceOf(Uri::class, $extracted);
        $this->assertEquals($url, $extracted);

        $url = new Uri($url);

        $object = $class::fromUrl($url);
        $extracted = $object->getUrl();

        $this->assertInstanceOf(Uri::class, $extracted);
        $this->assertEquals($url, $extracted);
    }
}
