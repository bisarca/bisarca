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
