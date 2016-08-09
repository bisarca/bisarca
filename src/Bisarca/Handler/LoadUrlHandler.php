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
