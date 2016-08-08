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
