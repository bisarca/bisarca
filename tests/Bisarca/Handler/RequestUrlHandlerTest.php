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

use Bisarca\Command\RequestUrlCommand;
use League\Tactician\CommandBus;
use PHPUnit_Framework_TestCase;

/**
 * @covers Bisarca\Handler\RequestUrlHandler
 * @group unit
 */
class RequestUrlHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var RequestUrlHandler
     */
    protected $object;

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->commandBus = $this->createMock(CommandBus::class);

        $this->object = new RequestUrlHandler($this->commandBus);
    }

    public function testHandle()
    {
        $url = 'http://www.example.com/';

        $command = RequestUrlCommand::fromUrl($url);

        $this->commandBus
            ->expects($this->exactly(2))
            ->method('handle');

        $this->object->handle($command);
    }
}
