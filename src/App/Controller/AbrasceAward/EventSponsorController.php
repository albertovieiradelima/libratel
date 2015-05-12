<?php

namespace App\Controller\AbrasceAward;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Created by PhpStorm.
 * User: albertovieiradelima
 * Date: 29/01/15
 * Time: 09:18
 */
class EventSponsorController extends \App\Controller\BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->post('/event-sponsor/{id}/get-data', array($this, 'getDataAction'))->value('id', false)->bind('event-sponsor-get-data');
        $controller->post('/get-event-sponsor-orders/{id}', array($this, 'getEventSponsorOrdersAction'))->value('id', false)->bind('get-event-sponsor-orders');
        $controller->post('/event-sponsor/insert', array($this, 'insertAction'))->bind('event-sponsor-insert');
        $controller->post('/event-sponsor/update', array($this, 'updateAction'))->bind('event-sponsor-update');
        $controller->post('/event-sponsor/delete', array($this, 'deleteAction'))->bind('event-sponsor-delete');
        $controller->get('/event-sponsor/{id}', array($this, 'indexAction'))->value('id', false)->bind('event-sponsor');

        return $controller;
    }

    /**
     *  Event Sponsor management action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function indexAction(Application $app, Request $request, $id)
    {
        if(!$id){
            throw new \Exception('ID inválido');
        }

        $app['aa_event']->getAll();
        $events = $app['aa_event']->fetch_all();

        $app['aa_event']->getById((int)$id);
        $event = $app['aa_event']->fetch();

        if(!$event){
            throw new \Exception('Evento não encontrado');
        }

        return $app['twig']->render('admin/abrasce-award/event-sponsor.twig', array("modal" => 'admin/abrasce-award/event-sponsor-modal.twig', "navigate" => "Patrocinadores do Evento", "event" => $event, "events" => $events));
    }

    /**
     * getData action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getDataAction(Application $app, Request $request, $id)
    {

        if(!$id){
            return $app->json(array(
                'success' => false,
                'message' => 'Evento não informado'
            ));
        }

        $post = $request->request->all();
        $objId = $post['id'];
        $fk_event = $id;

        if (!$objId) {

            $data = $app['aa_event_sponsor']->getAllForDT(null, $fk_event);
            return $app->json(array(
                'data' => $data
            ));

        } else {

            try{

                $obj = $app['aa_event_sponsor']->getById((int)$objId);
                if(!$obj){
                    throw new \Exception('Registro não encontrado');
                }

                return $app->json(array(
                    'success' => true,
                    'data' => $obj
                ));

            }catch(\Exception $ex){

                return $app->json(array(
                    'success' => false,
                    'message' => $ex->getMessage()
                ));

            }

        }

    }

    /**
     * getOrder action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getEventSponsorOrdersAction(Application $app, Request $request, $id)
    {

        $app['aa_event_sponsor']->getCountActive($id);
        $orders = $app['aa_event_sponsor']->fetch();

        if (is_array($orders)) {
            $result = array(
                'success' => true,
                'orders' => $orders
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível trazer as ordens.'
            );
        }

        return $app->json($result);

    }

    /**
     * Event Sponsor Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertAction(Application $app, Request $request)
    {
        try{

            $post = $request->request->all();

            $app['aa_event_sponsor']->getAllForDT(null, $post['fk_event']);
            $event_sponsors = $app['aa_event_sponsor']->fetch_all(MYSQLI_NUM);

            foreach ($event_sponsors as $key => $event_sponsor) {
                if($event_sponsors[$key][1] >= $post['order']){
                    $new_order = (int)$event_sponsors[$key][1] + 1;
                    if ($app['aa_event_sponsor']->update("aa_event_sponsor.order = '{$new_order}'", (int)$event_sponsors[$key][0])) {
                        continue;
                    } else {
                        $result = array(
                            'success' => false,
                            'message' => 'Erro ao tentar ordenar os patrocinadores.'
                        );
                        return $app->json($result);
                    }
                }
            }

            $app['aa_event_sponsor']->insert($post);

            return $app->json(array(
                'success' => true,
                'message' => 'Patrocinador adicionado com sucesso!'
            ));

        }catch(\Exception $ex){
            return $this->error($app, $ex->getMessage());
        }
    }

    /**
     * Event Sponsor Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateAction(Application $app, Request $request)
    {

        try{

            $post = $request->request->all();
            $data = array(
                'fk_event' => $post['fk_event'],
                'fk_sponsor' => $post['fk_sponsor'],
                'fk_sponsor_category' => $post['fk_sponsor_category'],
                'order' => $post['order']
            );
            $order = null;

            if ($event_sponsor = $app['aa_event_sponsor']->getById((int)$post['id'])) {
                $order = $event_sponsor["order"];
            }

            if($app['aa_event_sponsor']->update($data, $post['id'])){

                $app['aa_event_sponsor']->get('SELECT id, aa_event_sponsor.order FROM aa_event_sponsor WHERE id <> '. (int)$post["id"] .' ORDER BY aa_event_sponsor.order;');
                $sponsors = $app['aa_event_sponsor']->fetch_all(MYSQLI_NUM);

                $new_order = $post['order'];
                if($order != $post['order']) {
                    foreach ($sponsors as $key => $sponsor) {
                        if($sponsors[$key][1] >= $post['order']) {
                            $new_order++;
                            $data = array(
                                'order' => $new_order
                            );
                            if ($app['aa_event_sponsor']->update($data, (int)$sponsors[$key][0])) {
                                continue;
                            } else {
                                $result = array(
                                    'success' => false,
                                    'message' => 'Erro ao tentar ordenar os patrocinadores.'
                                );
                                return $app->json($result);
                            }
                        }
                    }
                }

                return $app->json(array(
                    'success' => true,
                    'message' => 'Patrocinador alterado com sucesso!'
                ));

            }


        }catch(\Exception $ex){
            return $this->error($app, $ex->getMessage());
        }
    }

    /**
     * Event Sponsor Delete
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

        $ent = $app['aa_event_sponsor']->getById((int)$post['id']);
        if (!$ent) {
            return $this->error($app, 'Registro não encontrado.');
        }

        if ($app['aa_event_sponsor']->delete((int)$post['id'])) {
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