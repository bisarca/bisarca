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

use Bisarca\Command\EnqueueUrlCommand;
use SplQueue;

class EnqueueUrlHandler
{
    /**
     * @var SplQueue
     */
    private $queue;

    /**
     * @param SplQueue $queue
     */
    public function __construct(SplQueue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @param EnqueueUrlCommand $command
     */
    public function handle(EnqueueUrlCommand $command)
    {
        $this->queue->push($command->getUrl());
    }
}
