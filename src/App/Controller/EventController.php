<?php

namespace App\Controller;

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
class EventController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return mixed|\Silex\ControllerCollection $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $eventController = $app['controllers_factory'];

        $eventController->get('/events', array($this, 'eventsAction'))->bind('events');
        $eventController->post('/get-events', array($this, 'getEventsAction'))->bind('get-events');
        $eventController->post('/events/insert', array($this, 'insertEventAction'))->bind('new-events');
        $eventController->post('/events/update', array($this, 'updateEventAction'))->bind('edit-events');
        $eventController->post('/events/delete', array($this, 'deleteEventAction'))->bind('remove-events');

        return $eventController;
    }

    /**
     * events action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function eventsAction(Application $app)
    {
        return $app['twig']->render('admin/events.twig', array("modal" => "admin/event-modal.twig", "navigate" => "Eventos"));
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

            $app['event']->getAll("id, title, number_vacancies, start_date, end_date, status", \App\Enum\EventTypeEnum::EVENT);
            $events = $app['event']->fetch_all(MYSQLI_NUM);
            foreach ($events as $key => $event) {
                $events[$key][3] = $this->formatDate($events[$key][3], "d/m/Y");
                $events[$key][4] = $this->formatDate($events[$key][4], "d/m/Y");
                $events[$key][5] = $events[$key][5] == "active" ?  "Ativo" : "Inativo";
                $button = '
                            <div class="btn-group">
                              <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-cog"></i> Opções <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu options" role="menu">
                                <li><a href="/admin/event-charge-period/' . $event[0] . '"><i class="fa fa-barcode"></i>Faixa de Cobrança</a></li>
                                <li><a href="/admin/event-registration/' . $event[0] . '"><i class="fa fa-user"></i> Gerenciar Inscrições</a></li>
                                <li><a href="/admin/event-registration-participants/' . $event[0] . '"><i class="fa fa-group"></i>Gerenciar Participantes</a></li>
                                <li><a href="/admin/event-report-tracking/' . $event[0] . '"><i class="fa fa-file-text-o"></i>Relatório de Acompanhamento</a></li>
                              </ul>
                            </div>';
                $events[$key][6] = $button;
            }

            $array = array("data" => $events);
            return $app->json($array);

        } else {

            $app['event']->getById((int)$post['id']);
            $event = $app['event']->fetch();
            $event["start_date"] = $this->formatDate($event["start_date"], "d/m/Y");
            $event["end_date"] = $this->formatDate($event["end_date"], "d/m/Y");

            if (is_array($event)) {
                $result = array(
                    'success' => true,
                    'event' => $event
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar o evento.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * Event Registration
     *
     * @param \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function insertEventAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $commonData = array('title' => 'Titulo', 'description' => 'Descrição', 'startdate' => 'Data de início',
            'enddate' => 'Data de término', 'starthour' => 'Hora de início', 'endhour' => 'Hora de término', 'local' => 'Local');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $inscription = $post['inscription'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $exclusive_associated = $post['exclusive_associated'] === 'on' ? true : false;
            $free_event = $post['free_event'] === 'on' ? true : false;

            $fileName = null;

            if ($file['image']) {

                $fileName = $this->uploadImage($file['image'], 'events');
            }

            if ($app['event']->insert(
                $post['title'],
                $post['description'],
                $fileName,
                \App\Enum\EventTypeEnum::EVENT,
                $this->formatDateMysql($post['startdate']),
                $this->formatDateMysql($post['enddate']),
                $post['starthour'],
                $post['endhour'],
                $post['local'],
                $inscription,
                $status,
                $post['site'],
                $exclusive_associated,
                $free_event,
                (int)$post['number_vacancies'],
                (int)$post['days_invoice'],
                $post['cancellation_policy']
            )
            ) {
                $result = array(
                    'success' => true,
                    'message' => 'Evento registrado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar o Evento. Verifique se o evento já foi cadastrado.'
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
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateEventAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $commonData = array('id' => 'Evento não encontrado', 'title' => 'Titulo', 'description' => 'Descrição', 'startdate' => 'Data de início',
            'enddate' => 'Data de término', 'starthour' => 'Hora de início', 'endhour' => 'Hora de término', 'local' => 'Local');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === 'on' ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $inscription = $post['inscription'] === 'on' ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $exclusive_associated = $post['exclusive_associated'] === 'on' ? true : false;
            $free_event = $post['free_event'] === 'on' ? true : false;

            if ($inscription === \App\Enum\SituationStatusEnum::ACTIVE && !$post['exclusive_associated']) {
                if (!$chargePeriod = $app['event_charge_period']->getAllByEvent((int)$post['id'])) {
                    return $app->json(array(
                        'success' => false,
                        'message' => 'Permitir Incrições: negada!<br/>Este Evento não possui faixa de cobrança cadastrada.'
                    ));
                } else if (strtotime($chargePeriod[0][1]) > strtotime(date('Y-m-d')) || strtotime($chargePeriod[0][2]) < strtotime(date('Y-m-d'))) {
                    return $app->json(array(
                        'success' => false,
                        'message' => 'Permitir Incrições: negada!<br/>A faxia de cobrança encontrada não está programada para o período atual.'
                    ));
                }
            }


            $fileName = null;

            if ($post['image'] && $file['image']) {

                if ($app['event']->getById((int)$post['id'])) {

                    $event = $app['event']->fetch();
                    $this->deleteImage($event["image"], 'events');
                }

                $fileName = $this->uploadImage($file['image'], 'events');

            } else if (!$post['image']) {

                if ($app['event']->getById((int)$post['id'])) {
                    $event = $app['event']->fetch();
                    $this->deleteImage($event["image"], 'events');
                }

            } else {
                $fileName = $post['image'];
            }

            if ($app['event']->update("title = '{$post['title']}', description = '{$post['description']}', image = '{$fileName}', start_date = '{$this->formatDateMysql($post['startdate'])}', end_date = '{$this->formatDateMysql($post['enddate'])}', start_hour = '{$post['starthour']}', end_hour = '{$post['endhour']}', local = '{$post['local']}', inscription = {$inscription}, status = {$status}, site = '{$post['site']}', exclusive_associated = '{$exclusive_associated}', free_event = '{$free_event}', number_vacancies = '{$post['number_vacancies']}', days_invoice = '{$post['days_invoice']}', cancellation_policy = '{$post['cancellation_policy']}'", (int)$post['id'])) {
                $result = array(
                    'success' => true,
                    'message' => 'Evento alterado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar o evento.'
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
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteEventAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Evento não encontrado.');
        }

        if ($app['event']->getById((int)$post['id'])) {
            $event = $app['event']->fetch();

            if($registration = $app['event_registration']->getAllByEvent($event['id'])){
                if (is_array($registration) && count($registration) > 0) {
                    $result = array(
                        'success' => false,
                        'message' => 'Não foi possível remover o evento, pois existem inscricões vinculadas ao mesmo.'
                    );
                    return $app->json($result);
                }
            }
            $this->deleteImage($event['image'], 'events');
        }

        if ($app['event']->delete((int)$post['id'])) {
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