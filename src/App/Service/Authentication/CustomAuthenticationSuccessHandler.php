<?php

namespace App\Service\Authentication;

use Silex\Application;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Custom Authentication Success Handler
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class CustomAuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler {

    protected $app = null;

    public function __construct(HttpUtils $httpUtils, array $options, Application $app) {

        parent::__construct($httpUtils, $options);

        $this->app = $app;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {

        // acesso autorizado, redireciona para o /admin
        $result = array(
            'success' => true,
            'message' => 'Acesso autorizado.',
            'redirect' => $this->app['url_generator']->generate('admin')
        );

        return $this->app->json($result);
    }

}