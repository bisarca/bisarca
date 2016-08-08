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
