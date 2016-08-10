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

use Bisarca\Command\HeadersCheckCommand;
use Exception;

class HeadersCheckHandler
{
    /**
     * @param HeadersCheckCommand $command
     */
    public function handle(HeadersCheckCommand $command)
    {
        $headers = $command->getHeaders();
        $headers = array_change_key_case($headers);

        foreach ($headers as $key => $values) {
            $headers[$key] = implode(', ', $values);
        }

        if (!isset($headers['content-type'])) {
            return;
        }

        if (!preg_match('#^text/x?html#', $headers['content-type'])) {
            throw new Exception('Format not accepted');
        }
    }
}
