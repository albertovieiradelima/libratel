<?php

namespace App\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

use App\Model;

/**
 * Controller Provider de Event Registration Participants
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class EventRegistrationParticipantsController extends \App\Controller\BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return mixed|\Silex\ControllerCollection $app['controllers_factory']
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->get('/event-registration-participants/{id}', array($this, 'indexAction'))->value('id', false)->bind('event-registration-participants');
        $controller->post('/event-registration-participants/{id}/get-data', array($this, 'getDataAction'))->value('id', false)->bind('event-registration-participants-get-data');
        $controller->post('/event-registration-participants/insert', array($this, 'insertAction'))->bind('event-registration-participants-insert');
        $controller->post('/event-registration-participants/update', array($this, 'updateAction'))->bind('event-registration-participants-update');
        $controller->post('/event-registration-participants/delete', array($this, 'deleteAction'))->bind('event-registration-participants-delete');

        return $controller;
    }

    /**
     *  Event Registration Participants management action
     *
     * @param \Silex\Application $app
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function indexAction(Application $app, Request $request, $id)
    {
        if (!$id) {
            throw new \Exception('ID inválido');
        }

        $app['event']->getAll();
        $events = $app['event']->fetch_all();

        $app['event']->getById((int)$id);
        $event = $app['event']->fetch();

        if (!$event) {
            throw new \Exception('Evento não encontrado');
        }

        return $app['twig']->render('admin/event-registration-participants.twig', array("modal" => 'admin/event-registration-participants-modal.twig', "navigate" => "Participantes do Evento/Curso", "event" => $event, "events" => $events));
    }

    /**
     * getData action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getDataAction(Application $app, Request $request, $id)
    {

        if (!$id) {
            return $app->json(array(
                'success' => false,
                'message' => 'Evento não informado.'
            ));
        }

        $post = $request->request->all();
        $objId = $post['id'];
        $fk_event = $id;

        try {

            if (!$objId) {

                $data = $app['event_registration_participants']->getAllByEvent($fk_event);

                return $app->json(array(
                    'data' => $data
                ));

            } else {

                $obj = $app['event_registration_participants']->getById($post['id']);

                if (!$obj) {
                    throw new \Exception('Registro não encontrado');
                }

                return $app->json(array(
                    'success' => true,
                    'data' => $obj
                ));
            }

        } catch (\Exception $ex) {

            return $app->json(array(
                'success' => false,
                'message' => $ex->getMessage()
            ));

        }
    }

    /**
     * Event Registration Registration
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function insertAction(Application $app, Request $request)
    {
        try {

            $post = $request->request->all();

            $commonData = array('fk_event_registration' => 'Inscrição', 'certificate_name' => 'Nome para certificado', 'cpf' => 'CPF',
                'badge_name' => 'Nome para crachá', 'badge_company' => 'Empresa para crachá', 'job' => 'Cargo', 'area' => 'Área', 'phone' => 'Celular');

            $specialData = array('email' => 'E-mail');

            // validação de dados
            $validator = new \App\Util\Validator();
            $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

            if ($valid === true) {

                $event = $app['event']->getById((int)$post['fk_event'])->fetch();
                if ($event) {
                    $post['invoice_due_date'] = date('Y-m-d', strtotime(date('Y-m-d H:i:s') . ' + ' . $event['days_invoice'] . ' day'));
                }
                $post['created_date'] = date("Y-m-d H:i:s");
                $post['invoice_value'] = $this->moneyFormatDecimal($post['invoice_value']);

                $app['event_registration_participants']->insert($post);

                return $app->json(array(
                    'success' => true,
                    'message' => 'Faixa de conbrança adicionada com sucesso!'
                ));

            } else {
                $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
                return $this->error($app, $message);
            }

        } catch (\Exception $ex) {
            return $this->error($app, $ex->getMessage());
        }
    }

    /**
     * Event Registration Update
     *
     * @param \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateAction(Application $app, Request $request)
    {

        try {

            $post = $request->request->all();
            $data = array(
                'certificate_name' => $post['certificate_name'],
                'cpf' => $post['cpf'],
                'sex' => $post['sex'],
                'email' => $post['email'],
                'badge_name' => $post['badge_name'],
                'badge_company' => $post['badge_company'],
                'job' => $post['job'],
                'area' => $post['area'],
                'phone' => $post['phone']
            );

            if ($app['event_registration_participants']->update($data, $post['id'])) {

                return $app->json(array(
                    'success' => true,
                    'message' => 'Faixa de cobrança alterada com sucesso!'
                ));

            }


        } catch (\Exception $ex) {
            return $this->error($app, $ex->getMessage());
        }
    }

    /**
     * Event Registration Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Registro não encontrado.');
        }

        if ($app['event_registration_participants']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Registro removido com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o registro.'
            );
        }

        return $app->json($result);
    }

}