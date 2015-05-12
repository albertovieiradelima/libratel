<?php

namespace App\Controller\AbrasceAward;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de Event
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class EventController extends \App\Controller\BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $eventController = $app['controllers_factory'];

        $eventController->get('/events', array($this, 'eventsAction'))->bind('award-events');
        $eventController->post('/get-events', array($this, 'getEventsAction'))->bind('get-award-events');
        $eventController->post('/event/insert', array($this, 'insertEventAction'))->bind('new-award-event');
        $eventController->post('/event/update', array($this, 'updateEventAction'))->bind('edit-award-event');
        $eventController->post('/event/delete', array($this, 'deleteEventAction'))->bind('remove-award-event');

        return $eventController;
    }

    /**
     * event action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function eventsAction(Application $app)
    {
        $awards = $app['aa_award']->getAll();
        return $app['twig']->render('admin/abrasce-award/events.twig', array(
            "modal" => "admin/abrasce-award/event-modal.twig",
            "navigate" => "Cadastro",
            "awards" => $awards
        ));
    }

    /**
     * getEvents action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getEventsAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['aa_event']->getAll('id, title, year, start_date, end_date');
            $events = $app['aa_event']->fetch_all(MYSQLI_NUM);
            foreach ($events as $key => $event) {
                $events[$key][3] = $this->formatDate($events[$key][3], 'd/m/Y');
                $events[$key][4] = $this->formatDate($events[$key][4], 'd/m/Y');

                $button = '
                            <div class="btn-group">
                              <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-cog"></i> Opções <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu" role="menu">
                                <li><a href="javascript:void(0)" onclick="showModalAwardEvent('.$events[$key][0].')"><i class="fa fa-trophy"></i> Gerenciar Prêmios</a></li>
                                <li><a href="javascript:void(0)" onclick="showModalEventSponsor()"><i class="fa fa-users"></i> Gerenciar Patrocinadores</a></li>
                                <li><a href="javascript:void(0)" onclick="showModalEventGallery()"><i class="fa fa-picture-o"></i> Gerenciar Galerias</a></li>
                              </ul>
                            </div>';
                $events[$key][5] = $button;
            }

            return $app->json(array(
                "data" => $events
            ));

        } else {

            $app['aa_event']->getById((int)$post['id']);
            $event = $app['aa_event']->fetch();
            $event['start_date'] = $this->formatDate($event['start_date'], 'd/m/Y');
            $event['end_date'] = $this->formatDate($event['end_date'], 'd/m/Y');

            if (is_array($event)) {
                $result = array(
                    'success' => true,
                    'event' => $event
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar o contato.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * Event Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertEventAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        $commonData = array('title' => 'Título', 'description' => 'Descrição', 'year' => 'Ano', 
            'start_date' => 'Data Inicial', 'end_date' => 'Data Final');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            if ($app['aa_event']->insert(
                $post['title'], 
                $post['description'], 
                $post['year'], 
                $this->formatDateMysql($post['start_date']), 
                $this->formatDateMysql($post['end_date'])
            )) {
                $result = array(
                    'success' => true,
                    'message' => 'Evento registrado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar o Evente. Verifique se o mesmo já foi cadastrado.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Event Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateEventAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        $commonData = array('id' => 'Evento não encontrado', 'title' => 'Título', 'description' => 'Descrição', 'year' => 'Ano', 
            'start_date' => 'Data Inicial', 'end_date' => 'Data Final');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            if ($app['aa_event']->update("
                title = '{$post['title']}', 
                description = '{$post['description']}', 
                year = '{$post['year']}', 
                start_date = '{$this->formatDateMysql($post['start_date'])}', 
                end_date = '{$this->formatDateMysql($post['end_date'])}'", (int)$post['id'])) {
                $result = array(
                    'success' => true,
                    'message' => 'Evento alterado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível alterar o evento.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Event Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteEventAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Evento não encontrado.');
        }

        if ($app['aa_event']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Evento removido com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o evento.'
            );
        }

        return $app->json($result);
    }
    

}