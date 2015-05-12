<?php

namespace App\Provider\OCI8;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * A simple OCI8 service provider
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class OCI8ServiceProvider implements ServiceProviderInterface {

    /**
     * Register
     * @param Application $app
     * @return \oci_connect
     */
    public function register(Application $app) {

        $app['oci'] = function () use ($app) {

            if (!isset($app['oci.options'])) {
                throw new \Exception('oci.options is not defined');
            }

            // get config
            $config = $app['oci.options'];

            // connect to an oracle database
            $conn = @oci_connect($config['username'], $config['password'], $config['hostname'] . ':' . $config['port'] . '/' . $config['sid'], $config['charset']);

            if (!$conn) {
                $e = oci_error();
                throw new \Exception(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
            }

            return $conn;
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
