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

use Bisarca\Command\CheckRobotsTxtCommand;
use Bisarca\Configuration;
use Exception;
use GuzzleHttp\Client;
use RobotsTxtParser;

class CheckRobotsTxtHandler
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param CheckRobotsTxtCommand $command
     */
    public function handle(CheckRobotsTxtCommand $command)
    {
        $url = $command->getUrl();

        try {
            $robotsUrl = sprintf('http://%s/robots.txt', $url->getHost());
            $content = (string) $this->client
                ->request('GET', $robotsUrl)
                ->getBody();

            $parser = new RobotsTxtParser($content);
            $parser->setUserAgent(Configuration::AGENT);
        } catch (Exception $exception) {
            return;
        }

        if ($parser->isDisallowed($url->getPath())) {
            throw new Exception();
        }
    }
}
