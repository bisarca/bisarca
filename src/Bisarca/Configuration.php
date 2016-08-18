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
     * @param string $userAgent The user agent
     */
    public function setUserAgent(string $userAgent)
    {
        $this->userAgent = $userAgent;
    }
}
