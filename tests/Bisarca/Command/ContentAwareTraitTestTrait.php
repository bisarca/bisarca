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

namespace Bisarca\Command;

trait ContentAwareTraitTestTrait
{
    public function testGetContent()
    {
        $content = $this->object->getContent();

        $this->assertInternalType('string', $content);
        $this->assertEmpty($content);
    }

    /**
     * @depends testGetContent
     */
    public function testSetContent()
    {
        $content = sha1(mt_rand());

        $this->object->setContent($content);

        $this->assertSame($content, $this->object->getContent());
    }

    /**
     * @depends testSetContent
     */
    public function testFromContent()
    {
        $content = sha1(mt_rand());
        $class = get_class($this->object);

        $object = $class::fromContent($content);

        $this->assertSame($content, $object->getContent());
    }
}
