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

namespace Bisarca\Handler;

use Bisarca\Command\HeadersCheckCommand;
use Exception;
use PHPUnit_Framework_TestCase;

/**
 * @covers Bisarca\Handler\HeadersCheckHandler
 * @group unit
 */
class HeadersCheckHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var HeadersCheckHandler
     */
    protected $object;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->object = new HeadersCheckHandler();
    }

    /**
     * @param array $headers
     * @param bool  $expectedException
     *
     * @dataProvider handleDataProvider
     */
    public function testHandle(array $headers, $expectedException)
    {
        $command = new HeadersCheckCommand();
        $command->setHeaders($headers);

        if ($expectedException) {
            $this->setExpectedException(Exception::class);
        }

        $this->object->handle($command);
    }

    /**
     * @return array
     */
    public function handleDataProvider()
    {
        return [
            [['Content-Type' => ['text/html']], false],
            [['content-type' => ['text/html']], false],
            [['content-type' => ['text/xhtml']], false],
            [['content-type' => ['text/html', 'charset=utf-8']], false],
            [['content-type' => ['text/plain']], true],
            [[], false],
        ];
    }
}
