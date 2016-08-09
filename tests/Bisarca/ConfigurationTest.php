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

use PHPUnit_Framework_TestCase;

/**
 * @covers Bisarca\Configuration
 * @group unit
 */
class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Configuration
     */
    protected $object;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->object = new Configuration();
    }

    public function testGetUserAgent()
    {
        $this->assertNotNull($this->object->getUserAgent());
    }

    /**
     * @depends testGetUserAgent
     */
    public function testSetUserAgent()
    {
        $userAgent = sha1(mt_rand());

        $this->object->setUserAgent($userAgent);

        $this->assertSame($userAgent, $this->object->getUserAgent());
    }
}
