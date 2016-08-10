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
