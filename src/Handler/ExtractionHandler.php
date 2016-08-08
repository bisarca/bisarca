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

        $sitemapsExtraction = new Command\SitemapsExtractionCommand();
        $sitemapsExtraction->setContent($content);
        $sitemapsExtraction->setUrl($url);

        $this->commandBus->handle($sitemapsExtraction);
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
