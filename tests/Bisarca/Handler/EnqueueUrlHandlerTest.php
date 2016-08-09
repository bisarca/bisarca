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

use Bisarca\Command\EnqueueUrlCommand;
use PHPUnit_Framework_TestCase;
use SplQueue;

/**
 * @covers Bisarca\Handler\EnqueueUrlHandler
 * @group unit
 */
class EnqueueUrlHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EnqueueUrlHandler
     */
    protected $object;

    /**
     * @var SplQueue
     */
    protected $queue;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->queue = $this->createMock(SplQueue::class);

        $this->object = new EnqueueUrlHandler($this->queue);
    }

    public function testHandle()
    {
        $url = 'http://www.example.com/';

        $command = EnqueueUrlCommand::fromUrl($url);

        $this->queue
            ->expects($this->once())
            ->method('push')
            ->with($this->equalTo($url));

        $this->object->handle($command);
    }
}
