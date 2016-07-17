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
            ['', 1],
            ['<body><a href="/foo">bar</a></body>', 2],
            ['<body></body>', 1],
            [
                '<body>
                    <a href="/foo">bar</a>
                    <p>
                        <a href="/bar">foo</a>
                    </p>
                    <img src="/img"/>
                </body>',
                3,
            ],
            [
                '<body>
                    <a href="/foo">bar</a>
                    <p>
                        <a href="javascript:foo();">foo</a>
                    </p>
                    <img src="/img"/>
                </body>',
                2,
            ],
        ];
    }
}
