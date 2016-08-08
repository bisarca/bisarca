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

use PHPUnit_Framework_TestCase;

/**
 * @covers Bisarca\Command\UrlAwareTrait
 * @group unit
 */
class UrlAwareTraitTest extends PHPUnit_Framework_TestCase
{
    use UrlAwareTraitTestTrait;

    /**
     * @var UrlAwareTrait
     */
    protected $object;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->object = $this
            ->getMockBuilder(UrlAwareTrait::class)
            ->getMockForTrait();
    }
}
