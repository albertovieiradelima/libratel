<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de Subscriber
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class SubscriberController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $subscriberController = $app['controllers_factory'];

        $subscriberController->get('/subscribers', array($this, 'subscribersAction'))->bind('subscribers');
        $subscriberController->post('/get-subscribers', array($this, 'getSubscribersAction'))->bind('get-subscribers');
        $subscriberController->post('/get-subscriber-events', array($this, 'getEventsAction'))->bind('get-subscriber-events');
        $subscriberController->post('/subscriber/insert', array($this, 'insertSubscriberAction'))->bind('new-subscriber');
        $subscriberController->post('/subscriber/update', array($this, 'updateSubscriberAction'))->bind('edit-subscriber');
        $subscriberController->post('/subscriber/delete', array($this, 'deleteSubscriberAction'))->bind('remove-subscriber');

        return $subscriberController;
    }

    /**
     * subscriber action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function subscribersAction(Application $app)
    {
        return $app['twig']->render('admin/subscribers.twig', array("modal" => "admin/subscriber-modal.twig", "navigate" => "Pedidos de Inscrição"));
    }

    /**
     * getSubscribers action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getEventsAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        $app['event']->getAll('id, title');
        $events = $app['event']->fetch_all();

        return $app->json($events);

    }

    /**
     * getSubscribers action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getSubscribersAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['subscriber']->getAll('s.id, s.date, s.name, s.email, e.title, e.type, s.status');
            $subscribers = $app['subscriber']->fetch_all(MYSQLI_NUM);
            foreach ($subscribers as $key => $subscriber) {
                $subscribers[$key][1] = $this->formatDate($subscriber[1], 'd/m/Y - H:i:s');
                $subscribers[$key][6] = $subscribers[$key][6] == 'registered' ? 'Inscrito' : 'Não inscrito';
            }

            $data = array("data" => $subscribers);
            return $app->json($data);

        } else {

            $app['subscriber']->getById((int)$post['id']);
            $subscriber = $app['subscriber']->fetch();
            $subscriber['date'] = $this->formatDate($subscriber['date'], 'd/m/Y - H:i:s');

            if (is_array($subscriber)) {
                $result = array(
                    'success' => true,
                    'subscriber' => $subscriber
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar o pedido de inscrição.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * Subscriber Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertSubscriberAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        $commonData = array('fk_event' => 'Evento ou Curso não informado', 'name' => 'Nome', 'email' => 'E-mail', 
            'cpf' => 'CPF', 'phone' => 'Telefone', 'job' => 'Cargo', 'business' => 'Empresa');

        // dados especiais de validação
        $specialData = array('email' => 'email');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            
            if ($app['subscriber']->insert(
            $post['fk_event'], 
            $post['name'], 
            $post['email'], 
            $post['cpf'], 
            $post['phone'], 
            $post['job'], 
            $post['business'], 
            date('Y-m-d H:i:s'),
            $status
            )) {
                $result = array(
                    'success' => true,
                    'message' => 'Pedido de Inscrição registrado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar seu pedido de inscrição. Verifique se o mesmo já foi cadastrado.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }
        
        return $app->json($result);
    }

    /**
     * Subscriber Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateSubscriberAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        $commonData = array('id' => 'Inscrição não encontrada', 'fk_event' => 'Evento ou Curso não informado', 'name' => 'Nome', 
            'email' => 'E-mail', 'cpf' => 'CPF', 'phone' => 'Telefone', 'job' => 'Cargo', 'business' => 'Empresa');

        // dados especiais de validação
        $specialData = array('email' => 'email');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;

            if ($app['subscriber']->update("
                fk_event = '{$post['fk_event']}', 
                name = '{$post['name']}', 
                cpf = '{$post['cpf']}', 
                email = '{$post['email']}', 
                phone = '{$post['phone']}', 
                job = '{$post['job']}', 
                business = '{$post['business']}', 
                status = {$status}", (int)$post['id'])) {
                $result = array(
                    'success' => true,
                    'message' => 'Pedido de Inscrição alterado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível alterar o pedido de inscrição.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Subscriber Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteSubscriberAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Pedido de Inscrição não encontrado.');
        }

        if ($app['subscriber']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Pedido de Inscrição removido com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o pedido de inscrição.'
            );
        }

        return $app->json($result);
    }
    

}