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

use Bisarca\Command\LoadUrlCommand;
use Bisarca\Configuration;
use GuzzleHttp\Client;
use League\Tactician\CommandBus;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @covers Bisarca\Handler\LoadUrlHandler
 * @group unit
 */
class LoadUrlHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var LoadUrlHandler
     */
    protected $object;

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->commandBus = $this->createMock(CommandBus::class);
        $this->client = $this->createMock(Client::class);
        $this->configuration = $this->createMock(Configuration::class);

        $this->object = new LoadUrlHandler(
            $this->commandBus,
            $this->client,
            $this->configuration
        );
    }

    public function testHandle()
    {
        $url = 'http://www.example.com/';
        $command = LoadUrlCommand::fromUrl($url);
        $response = $this->createMock(ResponseInterface::class);

        $response
            ->expects($this->once())
            ->method('getHeaders')
            ->willReturn([]);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->commandBus
            ->expects($this->exactly(2))
            ->method('handle');

        $this->object->handle($command);
    }
}
