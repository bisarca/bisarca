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

use Bisarca\Command;
use Bisarca\Configuration;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use League\Tactician\CommandBus;

class LoadUrlHandler
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param CommandBus    $commandBus
     * @param Client        $client
     * @param Configuration $configuration
     */
    public function __construct(
        CommandBus $commandBus,
        Client $client,
        Configuration $configuration
    ) {
        $this->commandBus = $commandBus;
        $this->client = $client;
        $this->configuration = $configuration;
    }

    /**
     * @param Command\LoadUrlCommand $command
     */
    public function handle(Command\LoadUrlCommand $command)
    {
        $url = $command->getUrl();
        $response = $this->client->request('GET', $url, $this->getOptions());

        $headersCheck = new Command\HeadersCheckCommand();
        $headersCheck->setHeaders($response->getHeaders());
        $headersCheck->setUrl($url);

        $this->commandBus->handle($headersCheck);

        $extraction = new Command\ExtractionCommand();
        $extraction->setContent((string) $response->getBody());
        $extraction->setUrl($url);

        $this->commandBus->handle($extraction);
    }

    /**
     * Gets HTTP client options.
     *
     * @return array
     */
    private function getOptions(): array
    {
        return [
            'allow_redirects' => [
                'max' => 10,
                'referer' => true,
            ],
            'cookies' => new CookieJar(),
            'headers' => [
                'User-Agent' => $this->configuration->getUserAgent(),
            ],
            'timeout' => 300,
        ];
    }
}
