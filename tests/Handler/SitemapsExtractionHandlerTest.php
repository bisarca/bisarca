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

use Bisarca\Command\SitemapsExtractionCommand;
use League\Tactician\CommandBus;
use PHPUnit_Framework_TestCase;

/**
 * @covers Bisarca\Handler\SitemapsExtractionHandler
 * @group unit
 */
class SitemapsExtractionHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SitemapsExtractionHandler
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

        $this->object = new SitemapsExtractionHandler($this->commandBus);
    }

    /**
     * @param string $content
     * @param int    $handles
     *
     * @dataProvider handleDataProvider
     */
    public function testHandle($content, $handles)
    {
        $url = 'http://www.example.com/';
        $command = SitemapsExtractionCommand::fromContentAndUrl($content, $url);

        $this->commandBus
            ->expects($this->exactly($handles))
            ->method('handle');

        $this->object->handle($command);
    }

    /**
     * @return array
     */
    public function handleDataProvider()
    {
        return [
            ['', 0],
            ['<body><a href="/foo">bar</a></body>', 0],
            ['<body></body>', 0],
        ];
    }
}
