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

use Bisarca\Command\HeadersCheckCommand;
use Exception;
use PHPUnit_Framework_TestCase;

/**
 * @covers Bisarca\Handler\HeadersCheckHandler
 * @group unit
 */
class HeadersCheckHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var HeadersCheckHandler
     */
    protected $object;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->object = new HeadersCheckHandler();
    }

    /**
     * @param array $headers
     * @param bool  $expectedException
     *
     * @dataProvider handleDataProvider
     */
    public function testHandle(array $headers, $expectedException)
    {
        $command = new HeadersCheckCommand();
        $command->setHeaders($headers);

        if ($expectedException) {
            $this->setExpectedException(Exception::class);
        }

        $this->object->handle($command);
    }

    /**
     * @return array
     */
    public function handleDataProvider()
    {
        return [
            [['Content-Type' => ['text/html']], false],
            [['content-type' => ['text/html']], false],
            [['content-type' => ['text/xhtml']], false],
            [['content-type' => ['text/html', 'charset=utf-8']], false],
            [['content-type' => ['text/plain']], true],
            [[], false],
        ];
    }
}
