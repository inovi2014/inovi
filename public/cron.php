#!/usr/bin/php
<?php
    namespace Thin;
    set_time_limit(false);

    require_once __DIR__ . DIRECTORY_SEPARATOR . 'init.php';
    require_once APPLICATION_PATH . DS . 'Bootstrap.php';

    Bootstrap::cli();

    if(empty ($argv[1])) {
        exit("L'argument cron n'a pas été livré à l’exécution.");
    } else {
        $cron = cron($argv);
    }
