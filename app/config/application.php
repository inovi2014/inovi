<?php
	namespace Thin;

	/* GENERAL */
	$config = array(
		'database' => array(
			'adapter'     		=> 'mysql',
			'host'        		=> 'localhost',
			'username'    		=> 'root',
			'password'    		=> 'root',
			'dbname'      		=> 'ajf',
			'port'        		=> 3306,
			'driver'      		=> 'pdo_mysql',
			'proxy_namespace'	=> 'ThinDoctrine',
			'proxy_dir'			=> APPLICATION_PATH . DS . 'models' . DS . 'Doctrine' . DS . 'Proxies',
		),
		'application' => array(
			'plugins_dir'     => __DIR__ . '/../../app/plugins/',
			'helpers_dir'     => __DIR__ . '/../../app/plugins/',
			'library_dir'     => __DIR__ . '/../../app/lib/',
			'view'			  => array(
				'cache'		  => true,
				'cacheTtl'	  => 7200,
			),
		),
		'mailer' => array(
			'host'      => 'smtp.mandrillapp.com',
            'login'     => 'clementdharcourt@albumblog.com',
            'password'  => 'BT99CIkPBCsoWX5otYpo9g',
            'port'      => 587,
            'secure'    => null,
            'auth'      => true,
            'debug'     => false
		),
		'mongo' => array(
			'hostname'   => 'localhost',
		    'replicaSet' => false,
		    'db'         => SITE_NAME,
		    // 'username'   => '',
		    // 'password'   => '',
		)
	);

	/* PRODUCTION ENVIRONMENT */
	$production = array();

	/* TESTING ENVIRONMENT */
	$testing = array();

	/* DEVELOPMENT ENVIRONMENT */
	$development = array(
		'application' => array(
			'view' => array(
				'cache' => false,
			),
		),
	);

	/* LOCAL ENVIRONMENT */
	$local = File::exists(__DIR__ . DS . 'local.php')
	? include __DIR__ . DS . 'local.php'
	: array();
	$config = arrayMergeRecursive($config, $local);

	$application = new Container;
	$configEnv = Config::get('application.env', APPLICATION_ENV);
	$configEnv = $$configEnv;
	$config = arrayMergeRecursive($config, $configEnv);
	return $application->populate($config);
