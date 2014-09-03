<?php
    namespace Thin;
    set_time_limit(false);

    require_once __DIR__ . DIRECTORY_SEPARATOR . 'init.php';
    require_once APPLICATION_PATH . DS . 'Bootstrap.php';

    Bootstrap::cli();
