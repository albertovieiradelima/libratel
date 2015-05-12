<?php

namespace App\Service\Authentication;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * Custom Authentication Failure Handler
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class CustomAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface {

    public function __construct(Application $app) {

        $this->app = $app;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {

        $post = $request->request->all();

        $result = array(
            'success' => false,
            'error' => '',
        );

        if (!$post['username']) {
            $result['error'] = 'Usuário inválido.';
            return $this->app->json($result);
        }

        if (!$post['password']) {
            $result['error'] = 'Senha inválida.';
            return $this->app->json($result);
        }

        if ($exception->getMessage() == 'Bad credentials.') {
            $result['error'] = 'Acesso não autorizado.';
            return $this->app->json($result);
        }

        $result['error'] = $exception->getMessage();

        return $this->app->json($result);
    }
}
