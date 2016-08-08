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

class RequestUrlHandler
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
     * @param Command\RequestUrlCommand $command
     */
    public function handle(Command\RequestUrlCommand $command)
    {
        $this->commandBus->handle(
            Command\CheckRobotsTxtCommand::fromUrl($command->getUrl())
        );

        $this->commandBus->handle(
            Command\LoadUrlCommand::fromUrl($command->getUrl())
        );
    }
}
