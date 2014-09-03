<?php
    namespace Thin;

    container();
    Utils::cleanCache();

    $_SERVER['REQUEST_URI'] = isAke($_SERVER, 'REQUEST_URI', '/');
    Request::$foundation = \Symfony\Component\HttpFoundation\ThinRequest::createFromGlobals();

    Autoloader::registerNamespace('ThinService', APPLICATION_PATH . DS . 'services');
    Autoloader::registerNamespace('ThinHelper', APPLICATION_PATH . DS . 'helpers');
    Autoloader::registerNamespace('ThinPlugin', APPLICATION_PATH . DS . 'plugins');
    Autoloader::registerNamespace('ThinForm', APPLICATION_PATH . DS . 'forms');
    Autoloader::registerNamespace('ThinTask', APPLICATION_PATH . DS . 'tasks');
    Autoloader::registerNamespace('ThinHook', APPLICATION_PATH . DS . 'hooks');

    Config::init();

    date_default_timezone_set(Config::get('application.timezone', 'Europe/Paris'));

    $app = App::instance();

    $language = isAke(
        $_REQUEST,
        'thin_language',
        Config::get(
            'application.language',
            DEFAULT_LANGUAGE
        )
    );
    $app->setLang(with(new Lang($language)));

    $core = context('core');
    $core->set('app', $app);

    $core->log(function($message) {
        Log::info($message);
    });

    define('NL', "\n");
    define('THINSTART', microtime());

    if (Arrays::exists('SERVER_NAME', $_SERVER)) {
        $protocol = 'http';
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $protocol = 'https';
        }

        container()->setProtocol($protocol);

        $urlSite = "$protocol://" . $_SERVER["SERVER_NAME"] . "/";

        if (strstr($urlSite, '//')) {
            $urlSite = repl('//', '/', $urlSite);
            $urlSite = repl($protocol . ':/', $protocol . '://', $urlSite);
        }

        if (Inflector::upper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $tab = explode('\\', $urlSite);
            $r = '';
            foreach ($tab as $c => $v) {
                $r .= $v;
            }
            $r = repl('//', '/', $r);
            $r = repl($protocol . ':/', $protocol . '://', $r);
            $urlSite = $r;
        }
        container()->setNonRoot(false);

        if (null !== request()->getFromHtaccess()) {
            if ('true' == request()->getFromHtaccess() && !getenv('FROM_ROOT')) {
                $dir                        = $_SERVER['SCRIPT_NAME'];
                $htaccessDir                = repl(DS . 'web' . DS . 'index.php', '', $dir);
                $uri                        = $_SERVER['REQUEST_URI'];
                $uri                        = repl($htaccessDir . DS, '', $uri);
                $_SERVER['REQUEST_URI']     = DS . $uri;
                $urlSite                    .= repl(DS, '', $htaccessDir) . DS;
                container()->setNonRoot(true);
            }
        }

        $base_uri = substr(repl('public/index.php', '', $_SERVER['SCRIPT_NAME']), 1);
        if (substr($_SERVER['REQUEST_URI'], 0, strlen($base_uri) + 1) != '/' . $base_uri) {
            $base_uri = '';
        }

        Config::set('application.base_uri', $base_uri);

        $application = Config::get('application');
        $urlSite    .= $base_uri;

        Utils::set("urlsite", $urlSite);
        define('URLSITE', $urlSite);
        container()->setUrlsite(URLSITE);
        $core->setIsCli(false)->setUrlsite(URLSITE);
    } else {
        $core->setIsCli(true);
    }

    container()->setViewRedis(Config::get('application.view.redis', true));
    container()->setViewCache(Config::get('application.view.cache', false));
    container()->setViewCacheTtl(Config::get('application.view.cacheTtl', 7200));

    $core->redis(function() {
        static $i;
        if (null === $i) {
            if (!extension_loaded('redis')) {
                $i = new \Predis\Client;
            } else {
                $i = new \Redis();
                $i->connect('localhost', 6379);
            }
        }
        return $i;
    });

    event('redis', function() {
        return context()->redis();
    });

    $core->dispatch(function ($route) {
        if (!$route instanceof Container) return;
        container()->setRoute($route);
        $bundle         = $route->getBundle();
        if (!is_null($bundle)) {
            return Route::dispatchBundle($route);
        }
        $render         = $route->getRender();
        $tplDir         = $route->getTemplateDir();
        $controllerDir  = $route->getControllerDir();
        $module         = $route->getModule();
        $controller     = $route->getController();
        $action         = $route->getAction();
        $alert          = $route->getAlert();
        $page           = container()->getPage();
        $isCms          = !is_null($page);

        $render = is_null($render) ? $action : $render;

        $dirApps = realpath(APPLICATION_PATH);

        if (!empty($render)) {
            $tplMotor = $route->getTemplateMotor();

            $tplDir = empty($tplDir)
            ? realpath($dirApps . DS . 'modules' . DS . Inflector::lower($module) . DS . 'views')
            : $tplDir;

            $controllerDir  = empty($controllerDir)
            ? realpath($dirApps . DS . 'modules' . DS . Inflector::lower($module) . DS . 'controllers')
            : $controllerDir;

            $tpl = $tplDir . DS . Inflector::lower($controller) . DS . Inflector::lower($render) . '.phtml';
            $controllerFile = $controllerDir . DS . Inflector::lower($controller) . 'Controller.php';

            if (File::exists($controllerFile)) {
                if ('Twig' == $tplMotor) {
                    if (!class_exists('Twig_Autoloader')) {
                        require_once 'Twig/Autoloader.php';
                    }

                    $tab    = explode(DS, $tpl);
                    $file   = Arrays::last($tab);

                    $path   = repl(DS . $file, '', $tpl);

                    $loader = new \Twig_Loader_Filesystem($path);
                    $view   = new \Twig_Environment(
                        $loader,
                        array(
                            'cache'             => CACHE_PATH,
                            'debug'             => false,
                            'charset'           => 'utf-8',
                            'strict_variables'  => false
                        )
                    );
                    container()->setView($view);
                    require_once $controllerFile;

                    $controllerClass    = 'Thin\\' . Inflector::lower($controller) . 'Controller';
                    $controller         = new $controllerClass;
                    $controller->view   = $view;
                    container()->setController($controller);


                    $actions = get_class_methods($controllerClass);

                    container()->setAction($action);
                    container()->setActions($actions);

                    if (strstr($action, '-')) {
                        $words = explode('-', $action);
                        $newAction = '';
                        for ($i = 0; $i < count($words); $i++) {
                            $word = trim($words[$i]);
                            if ($i > 0) {
                                $word = ucfirst($word);
                            }
                            $newAction .= $word;
                        }
                        $action = $newAction;
                    }

                    $actionName = $action . 'Action';

                    if (Arrays::in('init', $actions)) {
                        $controller->init();
                    }

                    if (Arrays::in('preDispatch', $actions)) {
                        $controller->preDispatch();
                    }

                    if (!Arrays::in($actionName, $actions)) {
                        return context()->is404();
                    }

                    $controller->$actionName();

                    $params = null === container()->getViewParams() ? array() : container()->getViewParams();
                    echo $view->render($file, $params);
                    if (Arrays::in('postDispatch', $actions)) {
                        $controller->preDispatch();
                    }
                    /* stats */
                    if (null === container()->getNoShowStats() && null === $route->getNoShowStats()) {
                        echo View::showStats();
                    }
                } else {
                    if (File::exists($tpl)) {
                        $view = new View($tpl);
                        container()->setView($view);
                    }
                    require_once $controllerFile;

                    $controllerClass    = 'Thin\\' . Inflector::lower($controller) . 'Controller';
                    $controller         = new $controllerClass;
                    if (File::exists($tpl)) {
                        $controller->view   = $view;
                    }
                    container()->setController($controller);


                    $actions = get_class_methods($controllerClass);

                    container()->setAction($action);

                    if (strstr($action, '-')) {
                        $words = explode('-', $action);
                        $newAction = '';
                        for ($i = 0; $i < count($words); $i++) {
                            $word = trim($words[$i]);
                            if ($i > 0) {
                                $word = ucfirst($word);
                            }
                            $newAction .= $word;
                        }
                        $action = $newAction;
                    }

                    $actionName = $action . 'Action';

                    if (Arrays::in('init', $actions)) {
                        $controller->init();
                    }

                    if (Arrays::in('preDispatch', $actions)) {
                        $controller->preDispatch();
                    }

                    if (!Arrays::in($actionName, $actions)) {
                        return context()->is404();
                    }

                    $controller->$actionName();
                    if (File::exists($tpl)) {
                        $controller->view->render();
                    }

                    /* stats */
                    if (File::exists($tpl) && null === container()->getNoShowStats() && null === $route->getNoShowStats()) {
                        echo View::showStats();
                    }

                    if (Arrays::in('postDispatch', $actions)) {
                        $controller->preDispatch();
                    }

                    if (Arrays::in('exit', $actions)) {
                        $controller->exit();
                    }
                    container()->setIsDispatched(true);
                }
            } else {
                context()->is404();
            }
        }
    });

    $core->config(function() {
        static $settings = array();
        $name = Arrays::first(func_get_args());
        if (func_num_args() === 1) {
            if (Arrays::is($name)) {
                $settings = array_merge($settings, $name);
            } else {
                return isAke($settings, $name, null);
            }
        } else {
            $value = Arrays::last(func_get_args());
            $settings[$name] = $value;
        }
    });

    context('config')->load(function($name) {
        $file = APPLICATION_PATH . DS . 'config' . DS . Inflector::lower($name) . '.php';
        return File::exists($file) ? include $file : null;
    });

    context('config')->_get(function ($key) {
        if (is_string($key)) {
            $key = Inflector::lower($key);
            return Bootstrap::$bag['config']->$key;
        }
        return null;
    });

    context('config')->_set(function ($key, $value) {
        if (is_string($key)) {
            $key = Inflector::lower($key);
            Bootstrap::$bag['config']->$key = $value;
            Config::init();
        }
        return context('config');
    });

    $core->mailer(function() {
        $params = Config::get('mailer', array());
        if (!empty($params)) {
            $mail = new Smtp($params);
            return $mail;
        } else {
            context('mailer')->send(function () {
                $mailer = Arrays::last(func_get_args());
                return mail($mailer->get('to'), $mailer->get('subject'), $mailer->get('body'), "From: gplusquellec@free.fr");
            });
            return context('mailer');
        }
    });

    context('string')->utf8(function ($s) {
        $encoding = mb_detect_encoding($s,'UTF-8, ISO-8859-1, GBK');
        return ($encoding != 'UTF-8') ? iconv($encoding, 'utf-8', $s) : $s;
    });

    context('string')->stripslashes(function ($s) {
        if(is_array($s)) return $s;
        return (get_magic_quotes_gpc()) ? stripslashes(stripslashes($s)) : $s;
    });

    context('string')->plural(function ($count, $many, $one, $zero = '') {
        if($count == 1) return $one;
        else if($count == 0 && !empty($zero)) return $zero;
        else if($count == 0 && empty($zero)) return $one;
        else return $many;
    });


    $params = Bootstrap::$bag['config']->getDatabase();
    $dsn = Config::get('database.adapter', 'mysql')
    . ":dbname="
    . Config::get('database.dbname', SITE_NAME)
    . ";host="
    . Config::get('database.host', 'localhost');
    $params->setDsn($dsn);

    Entitydb::$driver = Kvdb::instance($params);


    context('nosql')->orm(function($entity) {
        $args = func_get_args();
        $ns = isset($args[1]) ? $args[1] : 'core';
        $ns = !is_string($ns) ? 'core' : $ns;
        if (is_string($entity) && is_string($entity)) {
            static $i = array();
            $db = isAke($i, $entity, null);
            if (is_null($db)) {
                $i[$entity] = $db = new Redistorage($entity, $ns);
            }
            return $db;
        }
    });

    $core->route(function(Container $route) {
        $routes         = container()->getRoutes();
        $routes         = is_null($routes) ? array() : $routes;

        $name           = $route->getName();
        $module         = $route->getModule();
        $controller     = $route->getController();
        $action         = $route->getAction();
        $bundle         = $route->getBundle();

        if ((is_null($module) && is_null($bundle)) || is_null($controller) || is_null($action)) {
            throw new Exception("This route is invalid.");
        }

        $name = is_null($name) ? "$module-$controller-$action" : $name;
        $routes[$name]  = $route;
        container()->setRoutes($routes);
    });

    $core->is404(function() {
        $ever = context()->get('ever404');
        if (true !== $ever) {
            $uri = substr(context()->get('uri'), 1);
            $paths = explode('/', $uri);
            if (count($paths) == 1) {
                $action     = Inflector::uncamelize(Arrays::first($paths));
                $controller = Config::get('application.default.controller', 'static');
                $module     = Config::get('application.default.module', 'www');
            } elseif (count($paths) == 2) {
                $controller = Inflector::lower(Arrays::first($paths));
                $action     = Inflector::uncamelize(Arrays::last($paths));
                $module     = Config::get('application.default.module', 'www');
            } elseif (count($paths) > 2) {
                $module     = Inflector::lower(Arrays::first($paths));
                $controller = Inflector::lower($paths[1]);
                $action     = Inflector::uncamelize($paths[2]);
                if (count($paths) > 3) {
                    array_shift($paths);
                    array_shift($paths);
                    array_shift($paths);

                    if (count($paths) > 1 && count($paths) % 2 == 0) {
                        for ($i = 0; $i < count($paths); $i += 2) {
                            $_REQUEST[trim($paths[$i])] = trim($paths[$i + 1]);
                        }
                    }
                }
            }
            if (!isset($module) || !isset($controller) || !isset($action)) {
                header("HTTP/1.0 404 Not Found");
                $route = new Container;
                $route->setModule('www')->setController('static')->setAction('page404');
                context()->set('ever404', true);
                return context()->dispatch($route);
            }
            $route = new Container;
            $route->setModule($module)->setController($controller)->setAction($action);
            context()->set('ever404', true);
            return context()->dispatch($route);
        } else {
            header("HTTP/1.0 404 Not Found");
            $route = new Container;
            $route->setModule('www')->setController('static')->setAction('page404');
            context()->set('ever404', true);
            return context()->dispatch($route);
        }
    });

    context('db')->model(function ($table) {
        $instance   = Arrays::last(func_get_args());
        $db         = Database::instance(
            Config::get('database.dbname', SITE_NAME),
            $table,
            Config::get('database.host', 'localhost'),
            Config::get('database.username', 'root'),
            Config::get('database.password', '')
        );
        return $db;
    });

    context('db')->extends(function (Database $model, $name, $cb) {
        $db     = $model->getDatabase();
        $table  = $model->getTable();

        $settings   = isAke(Database::$config, "$db.$table");
        $functions  = isAke($settings, 'functions');

        $functions[$name] = $cb;

        Database::$config["$db.$table"]['functions'] = $functions;
    });

    event('log', function($str) {
        error_log($str);
    });

    event('assign', function () {
        $route = Arrays::last(func_get_args());
        return context()->route($route);
    });

    event('nbm', function($entity) {
        $args = func_get_args();
        $ns = isset($args[1]) ? $args[1] : 'core';
        $ns = !is_string($ns) ? 'core' : $ns;
        if (is_string($entity) && is_string($ns)) {
            static $i = array();
            $db = isAke($i, $entity, null);
            if (is_null($db)) {
                $i[$entity] = $db = new Mongonode($entity, $ns);
            }
            return $db;
        }
    });

    event('jbm', function($entity) {
        $args = func_get_args();
        $ns = isset($args[1]) ? $args[1] : 'core';
        $ns = !is_string($ns) ? 'core' : $ns;
        if (is_string($entity) && is_string($ns)) {
            static $i = array();
            $db = isAke($i, $entity, null);
            if (is_null($db)) {
                $i[$entity] = $db = Dbjson\Dbjson::instance($ns, $entity);
            }
            return $db;
        }
    });

    event('ebm', function($entity) {
        $args = func_get_args();
        $ns = isset($args[1]) ? $args[1] : 'core';
        $ns = !is_string($ns) ? 'core' : $ns;
        if (is_string($entity) && is_string($ns)) {
            static $i = array();
            $db = isAke($i, $entity, null);
            if (is_null($db)) {
                $i[$entity] = $db = em($ns, $entity);
            }
            return $db;
        }
    });

    event('tbm', function($entity) {
        $args = func_get_args();
        $ns = isset($args[1]) ? $args[1] : 'core';
        $ns = !is_string($ns) ? 'core' : $ns;
        if (is_string($entity) && is_string($ns)) {
            static $i = array();
            $db = isAke($i, $entity, null);
            if (is_null($db)) {
                $i[$entity] = $db = new Nodedb($ns, $entity);
            }
            return $db;
        }
    });

    context('url')->to(function ($name, $args = array()) {
        $routes = container()->getRoutes();
        foreach ($routes as $route) {
            $nameRoute = $route->name;
            if (!is_null($nameRoute)) {
                if ($nameRoute == $name) {
                    $path = $route->path;
                    $args = $args instanceof Container ? $args->assoc() : !Arrays::is($args) ? array() : $args;
                    if (count($args)) {
                        $max = count($args);
                        for ($i = 1; $i <= $max; $i++) {
                            $keyParam = "param$i";
                            $param = $route->$keyParam;
                            $val = isAke($args, $param, 'undefined');
                            $path = strReplaceFirst('(.*)', $val, $path);
                        }
                    }
                    return trim(urldecode(URLSITE), '/') . $path;
                }
            }
        }
        return urldecode(URLSITE);
    });

    context('url')->make(function ($url) {
        if ($url[0] != '/' && !strstr($url, 'http')) {
            $base = Config::get('application.base_uri');
            $base = !strlen($base) ? '/' : $base;
            return $base . $url;
        } else {
            return $url;
        }
    });

    context('url')->linkTo(function ($name, $cnt, $linkArgs = array(), $args = array()) {
        $url = context('url')->to($name, $args);
        $link = '<a href="' . $url . '"';
        if (count($linkArgs)) {
            foreach ($linkArgs as $key => $value) {
                $link .= ' ' . $key . '="' . $value . '"';
            }
        }
        $link .= '>' . $cnt . '</a>';
        return $link;
    });

    context('url')->link(function ($url, $cnt, $linkArgs = array()) {
        if ($url[0] != '/' && !strstr($url, 'http')) {
            $base = Config::get('application.base_uri');
            $base = !strlen($base) ? '/' : $base;
            $link = '<a href="' . $base . $url . '"';
        } else {
            $link = '<a href="' . $url . '"';
        }
        if (count($linkArgs)) {
            foreach ($linkArgs as $key => $value) {
                $link .= ' ' . $key . '="' . $value . '"';
            }
        }
        $link .= '>' . $cnt . '</a>';
        return $link;
    });

    context('asset')->css(function ($url, $linkArgs = array()) {
        if ($url[0] != '/' && !strstr($url, 'http')) {
            $base = Config::get('application.base_uri');
            $base = !strlen($base) ? '/' : $base;
            $link = '<link rel="stylesheet" href="' . $base . $url . '"';
        } else {
            $link = '<link rel="stylesheet" href="' . $url . '"';
        }
        if (count($linkArgs)) {
            foreach ($linkArgs as $key => $value) {
                $link .= ' ' . $key . '="' . $value . '"';
            }
        }
        $link .= ' type="text/css" />';
        return $link;
    });

    context('asset')->js(function ($url, $linkArgs = array()) {
        if ($url[0] != '/' && !strstr($url, 'http')) {
            $base = Config::get('application.base_uri');
            $base = !strlen($base) ? '/' : $base;
            $link = '<script src="' . $base . $url . '"';
        } else {
            $link = '<script src="' . $url . '"';
        }
        if (count($linkArgs)) {
            foreach ($linkArgs as $key => $value) {
                $link .= ' ' . $key . '="' . $value . '"';
            }
        }
        $link .= ' type="text/javascript"></script>';
        return $link;
    });

    context('asset')->img(function ($url, $linkArgs = array()) {
        if ($url[0] != '/' && !strstr($url, 'http')) {
            $base = Config::get('application.base_uri');
            $base = !strlen($base) ? '/' : $base;
            $link = '<img src="' . $base . $url . '"';
        } else {
            $link = '<img src="' . $url . '"';
        }
        if (count($linkArgs)) {
            foreach ($linkArgs as $key => $value) {
                $link .= ' ' . $key . '="' . $value . '"';
            }
        }
        if (Arrays::exists('alt', $linkArgs)) {
            $tab = explode('/', $url);
            $link .= ' alt="' . Arrays::last($tab) . '"';
        }
        $link .= ' />';
        return $link;
    });

    context('url')->forward(function ($action, $controller = null, $module = null) {
        $route      = container()->getRoute();
        $module     = is_null($module) || !is_string($module) ? $route->getModule() : $module;
        $controller = is_null($controller) || !is_string($controller) ? $route->getController() : $controller;
        $newRoute = new Container;
        $newRoute->setModule($module)->setController($controller)->setAction($action);
        context()->dispatch($newRoute);
        exit;
    });

    context('url')->redirect(function ($action, $controller = null, $module = null) {
        $route      = container()->getRoute();
        $module     = is_null($module) || !is_string($module) ? $route->getModule() : $module;
        $controller = is_null($controller) || !is_string($controller) ? $route->getController() : $controller;
        if ($controller == 'static') {
            header('Location: ' . URLSITE . $module . '/' . Inflector::lower($action));
        } else {
            header('Location: ' . URLSITE . $module . '/' . Inflector::lower($controller) . '/' . Inflector::lower($action));
        }
        exit;
    });

    context('url')->actual(function () {
        return urlNow();
    });

    context('url')->root(function ($raw = false) {
        return false !== $raw ? trim(URLSITE, '/') : URLSITE;
    });

    context('cache')->_set(function($key, $value, $ttl = 3600) {
        if (is_string($key)) {
            $db = new Txtdb('cache');
            $db->expire($key, $value, $ttl);
        }
    });

    context('cache')->_get(function($key, $default = null) {
        if (is_string($key)) {
            $db = new Txtdb('cache');
            return $db->get($key, $default);
        }
        return $default;
    });

    $core->router(function() {
        $routes = container()->getRoutes();
        $base_uri = Config::get('application.base_uri', '');
        if (strlen($base_uri)) {
            $uri = strReplaceFirst($base_uri, '', $_SERVER['REQUEST_URI']);
        } else {
            $uri = $_SERVER['REQUEST_URI'];
        }

        $uri = strlen($uri) > 1 ? rtrim($uri, '/') : $uri;
        context()->set('uri', $uri);
        if (!empty($routes)) {
            foreach ($routes as $name => $route) {
                if (!$route instanceof Container) {
                    continue;
                }
                $path = $route->getPath();
                if (strlen($path) && $path == $uri) {
                    Router::make($route);
                    return true;
                }
                if (!strlen($path) && !strlen($uri)) {
                    if (null !== $route->getRedirect()) {
                        Router::redirect('/' . $route->getRedirect());
                    }
                    Router::make($route);
                    return true;
                }

                $matched = Router::match($path, $route, $uri);

                if (false === $matched || !count($matched)) {
                    continue;
                } else {
                    if (null !== $route->getRedirect()) {
                        Router::redirect('/' . $route->getRedirect());
                    }
                    Router::make($route);
                    return true;
                }
            }
        }
    });

    context()->isPost(function($except = array()) {
        if (count($_POST) && count($except)) {
            foreach ($except as $key) {
                if (Arrays::exists($key, $_POST)) {
                    unset($_POST[$key]);
                }
            }
        }
        return count($_POST) ? true : false;
    });

    event('bucket', function() {
        static $i;
        if (null === $i) {
            $i = new Bucket(SITE_NAME);
        }
        return $i;
    });

    context('memory')->keep(function($key, $value) {
        return Instancedata::set($key, $value);
    });

    context('memory')->give(function($key, $default = null) {
        return Instancedata::get($key, $default);
    });

    context('request')->param(function($key, $default = null) {
        return isAke($_REQUEST, $key, $default);
    });

    $db = new db;

    $functions = array();
    $functions['table'] = function($obj) {
        return $obj->nbm('nma_table')->find($obj->getTable());
    };
    $functions['field'] = function($obj) {
        return $obj->nbm('nma_field')->find($obj->getField());
    };

    container()->nbm('nma_structure')->config('functions', $functions);

    $functions = array();
    $functions['table'] = function($obj) {
        return $obj->ebm('ema_table')->find($obj->getTable());
    };
    $functions['field'] = function($obj) {
        return $obj->ebm('ema_field')->find($obj->getField());
    };

    $db->ebm('ema_structure')->config('functions', $functions);

    $functions = array();
    $functions['table'] = function($obj) {
        return $obj->tbm('core_table')->find($obj->getTable());
    };
    $functions['field'] = function($obj) {
        return $obj->tbm('core_field')->find($obj->getField());
    };

    $db->tbm('core_structure')->config('functions', $functions);

    // $functions = array();
    // $functions['save'] = function($obj) {
    //     return dd(hr('dd'));
    // };

    // em('core', 'test')->config('functions', $functions);
