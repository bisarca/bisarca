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

use Bisarca\Command\CheckRobotsTxtCommand;
use Bisarca\Configuration;
use Bisarca\RobotsTxt\Parser;
use Exception;
use GuzzleHttp\Client;

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

        $robotsUrl = sprintf('http://%s/robots.txt', $url->getHost());

        try {
            $content = (string) $this->client
                ->request('GET', $robotsUrl)
                ->getBody();
        } catch (Exception $exception) {
            // content isn't available, than the bot can access
            return;
        }

        $parser = new Parser();
        $rulesets = $parser->parse($content);

        $path = $url->getPath();

        if (!$rulesets->isUserAgentAllowed(Configuration::AGENT, $path)) {
            throw new Exception();
        }
    }
}
