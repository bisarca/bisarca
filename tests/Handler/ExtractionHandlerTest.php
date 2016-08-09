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

use Bisarca\Command\ExtractionCommand;
use League\Tactician\CommandBus;
use PHPUnit_Framework_TestCase;

/**
 * @covers Bisarca\Handler\ExtractionHandler
 * @group unit
 */
class ExtractionHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ExtractionHandler
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

        $this->object = new ExtractionHandler($this->commandBus);
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
        $command = ExtractionCommand::fromContentAndUrl($content, $url);

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
            ['<body><a href="/foo">bar</a></body>', 1],
            ['<body></body>', 0],
            [
                '<body>
                    <a href="/foo">bar</a>
                    <p>
                        <a href="/bar">foo</a>
                    </p>
                    <img src="/img"/>
                </body>',
                2,
            ],
            [
                '<body>
                    <a href="/foo">bar</a>
                    <p>
                        <a href="javascript:foo();">foo</a>
                    </p>
                    <img src="/img"/>
                </body>',
                1,
            ],
        ];
    }
}
