<?php

/*
 * This file is part of the bisarca/bisarca package.
 *
 * (c) Emanuele Minotto <minottoemanuele@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
