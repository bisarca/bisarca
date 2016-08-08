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
