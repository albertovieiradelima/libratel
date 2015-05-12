<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Setup Controller Provider
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class SetupController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app) {

        $controller = $app['controllers_factory'];

        // Configurações de SMTP
        $controller->get('/setup/smtp', array($this, 'indexSMTPAction'))->bind('smtp-index');
        $controller->post('/setup/smtp/edit', array($this, 'editSMTPAction'))->bind('smtp-edit');
        $controller->post('/setup/smtp/update', array($this, 'updateSMTPAction'))->bind('smtp-update');

        // Configurações do Boleto
        $controller->get('/setup/boleto', array($this, 'indexBoletoAction'))->bind('boleto-index');
        $controller->post('/setup/boleto/edit', array($this, 'editBoletoAction'))->bind('boleto-edit');
        $controller->post('/setup/boleto/update', array($this, 'updateBoletoAction'))->bind('boleto-update');

        return $controller;
    }

    /**
     * SMTP index action
     *
     * @param \Silex\Application $app
     * @return view
     */
    public function indexSMTPAction(Application $app) {

        return $app['twig']->render('admin/smtp.twig', array("modal" => "admin/smtp-modal.twig", "navigate" => "SMTP"));
    }

    /**
     * SMTP edit action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function editSMTPAction(Application $app, Request $request)
    {
        $post = $request->request->all();
        $id = $post['id'];

        try {

            $data = $app['setup']->getSMTPById((int)$id);

            if (!$data) {
                throw new \Exception('Dados não encontrado');
            }

            return $app->json(array(
                'success' => true,
                'data' => $data
            ));

        } catch(\Exception $ex) {

            return $app->json(array(
                'success' => false,
                'message' => $ex->getMessage()
            ));
        }

    }

    /**
     * SMTP Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateSMTPAction(Application $app, Request $request)
    {
        try {

            $post = $request->request->all();

            $data = array(
                'smtp_host' => $post['smtp_host'],
                'smtp_port' => $post['smtp_port'],
                'smtp_user' => $post['smtp_user'],
                'smtp_pass' => $post['smtp_pass'],
                'smtp_email' => $post['smtp_email'],
                'smtp_name' => $post['smtp_name']
            );

            if ($app['setup']->updateSMTP($data, $post['id'])) {

                return $app->json(array(
                    'success' => true,
                    'message' => 'Configuração alterada com sucesso!'
                ));

            }

        } catch(\Exception $ex) {
            return $this->error($app, $ex->getMessage());
        }
    }

    /**
     * Boleto index action
     *
     * @param \Silex\Application $app
     * @return view
     */
    public function indexBoletoAction(Application $app) {

        return $app['twig']->render('admin/boleto.twig', array("modal" => "admin/boleto-modal.twig", "navigate" => "BOLETO"));
    }

    /**
     * Boleto edit action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function editBoletoAction(Application $app, Request $request)
    {
        $post = $request->request->all();
        $id = $post['id'];

        try {

            $data = $app['setup']->getDataBoletoById((int)$id);

            if (!$data) {
                throw new \Exception('Dados não encontrado');
            }

            return $app->json(array(
                'success' => true,
                'data' => $data
            ));

        } catch(\Exception $ex) {

            return $app->json(array(
                'success' => false,
                'message' => $ex->getMessage()
            ));
        }

    }

    /**
     * Boleto Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateBoletoAction(Application $app, Request $request)
    {
        try {

            $post = $request->request->all();

            $data = array(
                'cedente_name' => $post['cedente_name'],
                'cedente_cnpj' => $post['cnpj'],
                'cedente_address' => $post['cedente_address'],
                'cedente_zip' => $post['cedente_zip'],
                'cedente_city' => $post['cedente_city'],
                'cedente_state' => $post['cedente_state'],
                'cedente_agencia' => $post['cedente_agencia'],
                'cedente_agencia_dv' => $post['cedente_agencia_dv'],
                'cedente_conta' => $post['cedente_conta'],
                'cedente_conta_dv' => $post['cedente_conta_dv'],
                'cedente_carteira' => $post['cedente_carteira'],
                'cedente_label1' => $post['cedente_label1'],
                'cedente_label2' => $post['cedente_label2'],
                'cedente_label3' => $post['cedente_label3'],
                'cedente_label4' => $post['cedente_label4'],
                'cedente_label5' => $post['cedente_label5'],
                'cedente_label6' => $post['cedente_label6']
            );

            if ($app['setup']->updateDataBoleto($data, $post['id'])) {

                return $app->json(array(
                    'success' => true,
                    'message' => 'Configuração alterada com sucesso!'
                ));

            }

        } catch(\Exception $ex) {
            return $this->error($app, $ex->getMessage());
        }
    }

}