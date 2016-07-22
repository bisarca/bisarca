<?php

/*
 * Copyright (C) 2016 Emanuele Minotto
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public
 * License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Bisarca\Command;

use GuzzleHttp\Psr7\Uri;

trait UrlAwareTraitTestTrait
{
    public function testGetUrl()
    {
        $this->assertNull($this->object->getUrl());
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
    }
}
