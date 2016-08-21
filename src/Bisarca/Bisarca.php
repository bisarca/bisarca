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
     * Default services.
     *
     * @var array
     */
    const DEFAULT_SERVICES = [
        Command\RequestUrlCommand::class => Handler\RequestUrlHandler::class,
        Command\CheckRobotsTxtCommand::class => Handler\CheckRobotsTxtHandler::class,
        Command\LoadUrlCommand::class => Handler\LoadUrlHandler::class,
        Command\HeadersCheckCommand::class => Handler\HeadersCheckHandler::class,
        Command\EnqueueUrlCommand::class => Handler\EnqueueUrlHandler::class,
        Command\ExtractionCommand::class => Handler\ExtractionHandler::class,
        Command\SitemapsExtractionCommand::class => Handler\SitemapsExtractionHandler::class,
    ];

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
            new ContainerLocator($this->container, self::DEFAULT_SERVICES),
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
     * @param string $url URL of the page to visit
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
}
