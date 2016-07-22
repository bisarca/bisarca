<?php

$header = <<<EOF
Copyright (C) 2016 Emanuele Minotto

This program is free software: you can redistribute it and/or modify it
under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or (at your
option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public
License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

$config = Symfony\CS\Config\Config::create()
    ->fixers([
        'header_comment',
        'ordered_use',
        'phpdoc_order',
        'short_array_syntax',
        '-single_quote',
    ]);

if (null === $input->getArgument('path')) {
    $config
        ->finder(
            Symfony\CS\Finder\DefaultFinder::create()
                ->in(__DIR__.'/bin/')
                ->in(__DIR__.'/src/')
                ->in(__DIR__.'/tests/')
        );
}

return $config;
