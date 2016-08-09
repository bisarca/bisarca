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
 * @covers Bisarca\Command\ExtractionCommand
 * @group unit
 */
class ExtractionCommandTest extends PHPUnit_Framework_TestCase
{
    use ContentAwareTraitTestTrait;
    use UrlAwareTraitTestTrait;

    /**
     * @var ExtractionCommand
     */
    protected $object;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->object = new ExtractionCommand();
    }

    public function testFromContentAndURL()
    {
        $content = sha1(mt_rand());
        $url = 'http://www.example.com/';

        $object = ExtractionCommand::fromContentAndUrl($content, $url);

        $extracted = $object->getUrl();

        $this->assertSame($content, $object->getContent());
        $this->assertInstanceOf(Uri::class, $extracted);
        $this->assertEquals($url, $extracted);
    }
}
