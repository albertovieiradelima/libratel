<?php
// debug
$app['debug'] = false;

// check errors
if ($app['debug']) {
    // show errors
    ini_set('display_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE);
} else {
    // disabled errors
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('error_log', PATH_LOG . '/error.log');
}

// Local
$app['locale'] = 'pt_BR.utf-8';
$app['session.default_locale'] = $app['locale'];
$app['session.timezone'] = 'America/Sao_Paulo';

// Set the default time zone
date_default_timezone_set($app['session.timezone']);

// Set the default locale
setlocale(LC_ALL, $app['locale']);

// variável de ambiente usada para acentuação no oracle
// putenv("NLS_LANG=PORTUGUESE_BRAZIL.AL32UTF8");

// Cache
$app['cache.path'] = PATH_CACHE;

// Http cache
$app['http_cache.cache_dir'] = $app['cache.path'] . '/http';

// Twig cache
$app['twig.options.cache'] = $app['cache.path'] . '/twig';
