<?php

$header = <<<EOF
This file is part of the bisarca/bisarca package.

(c) Emanuele Minotto <minottoemanuele@gmail.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

$config = Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
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
                ->in(__DIR__.'/src/')
                ->in(__DIR__.'/tests/')
        );
}

return $config;
