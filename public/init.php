<?php
    umask(0000);

    if (version_compare(PHP_VERSION, '5.4.0', "<")) {
        throw new Exception('You need at least PHP 5.4.0 version to use this framework.');
    }

    defined('SITE_NAME')        || define('SITE_NAME', (getenv('SITE_NAME') ? getenv('SITE_NAME') : 'project'));

    defined('APPLICATION_PATH') || define('APPLICATION_PATH',   realpath(dirname(__FILE__) . '/../app'));
    defined('CONFIG_PATH')      || define('CONFIG_PATH',        realpath(dirname(__FILE__) . '/../app/config'));
    defined('CACHE_PATH')       || define('CACHE_PATH',         realpath(dirname(__FILE__) . '/../app/storage/cache'));
    defined('LOGS_PATH')        || define('LOGS_PATH',          realpath(dirname(__FILE__) . '/../app/storage/logs'));
    defined('TMP_PATH')         || define('TMP_PATH',           realpath(dirname(__FILE__) . '/../app/storage/tmp'));
    defined('STORAGE_DIR')      || define('STORAGE_DIR',        realpath(dirname(__FILE__) . '/../app/storage'));
    defined('TMP_PUBLIC_PATH')  || define('TMP_PUBLIC_PATH',    realpath(dirname(__FILE__) . '/tmp'));

    // Define path to libs directory
    defined('LIBRARIES_PATH')   || define('LIBRARIES_PATH', realpath(dirname(__FILE__) . '/../inc'));

    // Define application environment
    defined('APPLICATION_ENV')  || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

    define('DS', DIRECTORY_SEPARATOR);
    define('PS', PATH_SEPARATOR);
    define('DEFAULT_LANGUAGE', 'fr');

    define('STORAGE_PATH', STORAGE_DIR . DS . SITE_NAME);

    // Ensure library/ is on include_path
    set_include_path(implode(PS, array(
        LIBRARIES_PATH,
        get_include_path()
    )));

    $debug = 'production' != APPLICATION_ENV;

    require_once 'Thin/Loader.php';

    if (!is_dir(STORAGE_PATH)) {
        $createDir = Thin\File::mkdir(STORAGE_PATH, 0777);
        if (!$createDir) {
            throw new Thin\Exception("You must give 777 rights to " . STORAGE_PATH);
        }
        $file = STORAGE_PATH . DS . time();
        $createFile = Thin\File::create($file);
        if (!$createFile) {
            throw new Thin\Exception("You must give 777 rights to " . STORAGE_PATH);
        } else {
            Thin\File::delete($file);
        }
    }
