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
