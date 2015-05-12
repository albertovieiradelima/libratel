<?php

// path config
define('PATH_ROOT', dirname(__DIR__));
define('PATH_CACHE', PATH_ROOT . '/cache');
define('PATH_LOG', PATH_ROOT . '/log');
define('PATH_PUBLIC', PATH_ROOT . '/public');
define('PATH_SRC', PATH_ROOT . '/src');
define('PATH_VENDOR', PATH_ROOT . '/vendor');
define('PATH_VIEWS', PATH_ROOT . '/views');
define('PATH_ASSETS', PATH_PUBLIC . '/assets');
define('PATH_UPLOAD', PATH_PUBLIC . '/uploads');
define('PATH_UPLOAD_SHOPPING', PATH_UPLOAD . '/shopping');
define('PATH_UPLOAD_SUPPLIER', PATH_UPLOAD . '/supplier');

// autoload
require_once PATH_VENDOR . '/autoload.php';

// app init
$app = new Silex\Application();

require PATH_SRC . '/config.php';
require PATH_SRC . '/app.php';
require PATH_SRC . '/routes.php';

// development
if (php_sapi_name() == "cli") {
    
    // In cli-mode
    set_time_limit(0);
    
    $consoleApp = $app['console'];
    $consoleApp->add(new \App\Console\SyncDbCommand());
    $consoleApp->run();
    
} else {
    
    // Not in cli-mode
    $app->run();
    
}

// production
// $app['http_cache']->run();