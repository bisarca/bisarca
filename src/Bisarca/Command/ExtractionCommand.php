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

class ExtractionCommand
{
    use ContentAwareTrait;
    use UrlAwareTrait;

    /**
     * @param string $content
     * @param string $url
     *
     * @return ExtractionCommand
     */
    public static function fromContentAndUrl(string $content, string $url): ExtractionCommand
    {
        $instance = new self();
        $instance->setContent($content);
        $instance->setUrl($url);

        return $instance;
    }
}
