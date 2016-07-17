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
