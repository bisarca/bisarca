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

trait ContentAwareTrait
{
    /**
     * @var string
     */
    private $content = '';

    /**
     * Gets the value of content.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Sets the value of content.
     *
     * @param string $content The content.
     */
    public function setContent(string $content)
    {
        $this->content = $content;
    }

    /**
     * @param string $content
     *
     * @return static
     */
    public static function fromContent(string $content)
    {
        $instance = new static();
        $instance->setContent($content);

        return $instance;
    }
}
