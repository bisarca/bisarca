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

use Bisarca\Command;
use GuzzleHttp\Psr7\Uri;
use League\Tactician\CommandBus;
use Symfony\Component\DomCrawler\Crawler;
use tzfrs\Exceptions\GoogleSitemapParserException;
use tzfrs\GoogleSitemapParser;

class SitemapsExtractionHandler
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
     * @param Command\SitemapsExtractionCommand $command
     */
    public function handle(Command\SitemapsExtractionCommand $command)
    {
        $crawler = new Crawler($command->getContent(), $command->getUrl());

        $crawler
            ->filter('link[rel="sitemap"]')
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
        $uri = new Uri($node->getUri());
        $href = (string) Uri::resolve($uri, $node->attr('href'));

        if (!preg_match('#^https?://.+#', $href)) {
            return;
        }

        try {
            $parser = new GoogleSitemapParser($href);

            $parsed = $parser->parse();
            foreach ($parsed as $found) {
                $this->commandBus->handle(
                    Command\EnqueueUrlCommand::fromUrl($found)
                );
            }
        } catch (GoogleSitemapParserException $exception) {
            // missing sitemap or unable to fetch it
            return;
        }
    }
}
