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

namespace Bisarca\Handler;

use Bisarca\Command;
use League\Tactician\CommandBus;
use Symfony\Component\DomCrawler\Crawler;

class ExtractionHandler
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param Command\ExtractionCommand $command
     */
    public function handle(Command\ExtractionCommand $command)
    {
        $content = $command->getContent();
        $url = $command->getUrl();
        $crawler = new Crawler($content, $url);

        $crawler
            ->filter('a:not([rel*="nofollow"])')
            ->each(function (Crawler $node) {
                return $this->nodePass($node);
            });
    }

    /**
     * Node pass.
     *
     * @param Crawler $node
     */
    private function nodePass(Crawler $node)
    {
        $uri = $node
            ->link()
            ->getUri();

        if (!preg_match('#^https?://.+#', $uri)) {
            return;
        }

        $this->commandBus->handle(
            Command\EnqueueUrlCommand::fromUrl($uri)
        );
    }
}
