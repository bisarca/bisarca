<?php

/*
 * This file is part of the bisarca/bisarca package.
 *
 * (c) Emanuele Minotto <minottoemanuele@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
