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

/**
 * Configurations manager.
 */
class Configuration
{
    /**
     * cURL referer for the extraction of content.
     *
     * @var string
     */
    const REFERER = 'https://github.com/bisarca/bisarca';

    /**
     * Agent (used in the User Agent and the control of robots.txt).
     *
     * @var string
     */
    const AGENT = 'Bisarca';

    /**
     * Crawler User-Agent.
     *
     * @var string
     */
    private $userAgent;

    /**
     * Configurations initialization.
     */
    public function __construct()
    {
        $this->userAgent = sprintf(
            'Mozilla/5.0 (compatible; %s; +%s; Trident/4.0)',
            self::AGENT,
            self::REFERER
        );
    }

    /**
     * Gets the Crawler User-Agent.
     *
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * Sets the Crawler User-Agent.
     *
     * @param string $userAgent The user agent.
     */
    public function setUserAgent(string $userAgent)
    {
        $this->userAgent = $userAgent;
    }
}
