<?php

/*
 * This file is part of the bisarca/bisarca package.
 *
 * (c) Emanuele Minotto <minottoemanuele@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bisarca;

use League\Container\Container;
use League\Container\ContainerAwareTrait;
use League\Container\ReflectionContainer;
use League\Tactician\CommandBus;
use League\Tactician\Container\ContainerLocator;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use League\Tactician\Plugins\LockingMiddleware;
use SplQueue;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Bisarca Crawler.
 *
 * Examinations are conducted in width,
 * extracting all possible information including: content, dom (DOMDocument),
 * headers, robots.txt, download speeds and data-satellite...
 */
class Bisarca
{
    use ContainerAwareTrait;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->container = new Container();
        $this->container->delegate(new ReflectionContainer());

        $queue = new SplQueue();
        $queue->setIteratorMode(SplQueue::IT_MODE_DELETE);

        $this->container->add(SplQueue::class, $queue);
        $this->container->add(Configuration::class);

        $handlerMiddleware = new CommandHandlerMiddleware(
            new ClassNameExtractor(),
            new ContainerLocator($this->container, $this->getServices()),
            new HandleInflector()
        );

        $this->container->add(CommandBus::class, new CommandBus([
            new LockingMiddleware(),
            $handlerMiddleware,
        ]));
    }

    /**
     * Main function of the class, parses and extracts the link to follow.
     *
     * @param string $url URL of the page to visit.
     */
    public function loadUrl(string $url)
    {
        $command = new Command\RequestUrlCommand();
        $command->setUrl($url);

        $this->container
            ->get(CommandBus::class)
            ->handle($command);
    }

    /**
     * Loading the next link in the history.
     */
    public function loadNext()
    {
        $this->loadUrl(
            $this->container
                ->get(SplQueue::class)
                ->dequeue()
        );
    }

    /**
     * @return array
     */
    private function getServices(): array
    {
        return [
            Command\RequestUrlCommand::class => Handler\RequestUrlHandler::class,
            Command\CheckRobotsTxtCommand::class => Handler\CheckRobotsTxtHandler::class,
            Command\LoadUrlCommand::class => Handler\LoadUrlHandler::class,
            Command\HeadersCheckCommand::class => Handler\HeadersCheckHandler::class,
            Command\EnqueueUrlCommand::class => Handler\EnqueueUrlHandler::class,
            Command\ExtractionCommand::class => Handler\ExtractionHandler::class,
            Command\SitemapsExtractionCommand::class => Handler\SitemapsExtractionHandler::class,
        ];
    }
}
