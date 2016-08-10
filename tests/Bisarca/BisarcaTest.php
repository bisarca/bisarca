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

namespace Bisarca;

use League\Container\Container;
use League\Tactician\CommandBus;
use PHPUnit_Framework_TestCase;
use SplQueue;

/**
 * @covers Bisarca\Bisarca
 * @group unit
 */
class BisarcaTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Bisarca
     */
    protected $object;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->object = new Bisarca();
    }

    public function testGetContainer()
    {
        $this->assertInstanceOf(
            Container::class,
            $this->object->getContainer()
        );
    }

    /**
     * @depends testGetContainer
     */
    public function testSetContainer()
    {
        $container = $this->createMock(Container::class);

        $this->object->setContainer($container);

        $this->assertSame($container, $this->object->getContainer());
    }

    /**
     * @depends testSetContainer
     */
    public function testLoadUrl()
    {
        $handler = $this->createMock(CommandBus::class);

        $handler
            ->expects($this->once())
            ->method('handle');

        $this->object
            ->getContainer()
            ->add(CommandBus::class, $handler);

        $this->object->loadUrl('http://www.example.com/');
    }

    /**
     * @depends testLoadUrl
     */
    public function testLoadNext()
    {
        $handler = $this->createMock(CommandBus::class);
        $container = $this->object->getContainer();

        $handler
            ->expects($this->exactly(2))
            ->method('handle');

        $queue = new SplQueue();
        $queue->enqueue('http://www.iana.org/domains/example');

        $container->add(CommandBus::class, $handler);
        $container->add(SplQueue::class, $queue);

        $this->object->loadUrl('http://www.example.com/');
        $this->object->loadNext();
    }
}
