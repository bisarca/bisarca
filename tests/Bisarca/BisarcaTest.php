<?php

/*
 * This file is part of the bisarca/bisarca package.
 *
 * (c) Emanuele Minotto <minottoemanuele@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
