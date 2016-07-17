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
use PHPUnit_Framework_TestCase;

/**
 * @covers Bisarca\Command\HeadersCheckCommand
 * @group unit
 */
class HeadersCheckCommandTest extends PHPUnit_Framework_TestCase
{
    use UrlAwareTraitTestTrait;

    /**
     * @var HeadersCheckCommand
     */
    protected $object;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->object = new HeadersCheckCommand();
    }

    public function testGetHeaders()
    {
        $this->assertInternalType('array', $this->object->getHeaders());
    }

    /**
     * @depends testGetHeaders
     */
    public function testSetHeaders()
    {
        $data = range(1, 10);
        shuffle($data);

        $this->object->setHeaders($data);

        $this->assertSame($data, $this->object->getHeaders());
    }

    /**
     * @depends testSetHeaders
     */
    public function testFromHeadersAndUrl()
    {
        $data = range(1, 10);
        shuffle($data);
        $url = 'http://www.example.com/';

        $object = HeadersCheckCommand::fromHeadersAndUrl($data, $url);

        $this->assertSame($data, $object->getHeaders());

        $extracted = $object->getUrl();

        $this->assertInstanceOf(Uri::class, $extracted);
        $this->assertEquals($url, $extracted);
    }
}
