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
