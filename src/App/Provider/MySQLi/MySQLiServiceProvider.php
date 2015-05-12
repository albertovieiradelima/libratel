<?php

namespace App\Provider\MySQLi;

use Silex\Application;
use Silex\ServiceProviderInterface;

use App\Provider\MySQLi\MySQLi;

/**
 * A simple MySQLi service provider
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class MySQLiServiceProvider implements ServiceProviderInterface {

    /**
     * Register
     * @param Application $app
     * @return \MySQLi
     */
    public function register(Application $app) {

        $app['mysqli'] = function () use ($app) {

            if (!isset($app['mysqli.options'])) {
                $app->abort('mysqli.options is not defined');
            }

            // get config
            $config = $app['mysqli.options'];

            $mysqli = new \mysqli($config['hostname'], $config['username'], $config['password'], $config['database']);

            $mysqli->set_charset($config['charset']);

            return $mysqli;
        };
    }

    /**
     * Boot
     * @param Application $app
     */
    public function boot(Application $app) {
        // ...
    }

}
