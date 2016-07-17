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

namespace Bisarca\Handler;

use Bisarca\Command\CheckRobotsTxtCommand;
use Exception;
use GuzzleHttp\Client;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @covers Bisarca\Handler\CheckRobotsTxtHandler
 * @group unit
 */
class CheckRobotsTxtHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CheckRobotsTxtHandler
     */
    protected $object;

    /**
     * @var Client
     */
    protected $client;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->client = $this->createMock(Client::class);

        $this->object = new CheckRobotsTxtHandler($this->client);
    }

    public function testHandle()
    {
        $url = 'http://www.example.com/';
        $command = CheckRobotsTxtCommand::fromUrl($url);

        $response = $this->createMock(ResponseInterface::class);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn('');

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo('http://www.example.com/robots.txt')
            )
            ->willReturn($response);

        $this->object->handle($command);
    }

    public function testHandleException()
    {
        $url = 'http://www.example.com/';
        $command = CheckRobotsTxtCommand::fromUrl($url);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->will($this->throwException(new Exception()));

        $this->object->handle($command);
    }

    public function testHandleDisallow()
    {
        $url = 'http://www.example.com/';
        $command = CheckRobotsTxtCommand::fromUrl($url);

        $response = $this->createMock(ResponseInterface::class);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn("User-Agent: *\nDisallow: /\n");

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo('http://www.example.com/robots.txt')
            )
            ->willReturn($response);

        $this->setExpectedException(Exception::class);
        $this->object->handle($command);
    }
}
