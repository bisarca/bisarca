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
