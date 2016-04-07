<?php
require 'vendor/autoload.php';

use Junty\Runner\JuntyRunner;

$junty = new JuntyRunner();

$junty->task('css', function () {
        $this->src('./*.md', "/README.md/")
            ->forStreams(function ($streams) {
                print_r(array_map(function ($stream) {
                    return $stream->getMetaData('uri');
                }, $streams));
            });
    });

return $junty;