<?php
/**
 * Created by PhpStorm.
 * User: albertovieira
 * Date: 5/4/15
 * Time: 11:54 AM
 */

namespace App\Controller;

use Silex\Application;

use App\Model;

/**
 * Controller Provider de Event Report Tracking
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class EventReportTrackingController extends \App\Controller\BaseController
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

        $controller->get('/event-report-tracking/{id}', array($this, 'indexAction'))->value('id', false)->bind('event-report-tracking');
        $controller->post('/event-report-tracking/{id}/get-data', array($this, 'getDataAction'))->value('id', false)->bind('event-report-tracking-get-data');

        return $controller;
    }

    /**
     *  Event Report Tracking management action
     *
     * @param \Silex\Application $app
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function indexAction(Application $app, $id)
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

        return $app['twig']->render('admin/event-report-tracking.twig', array("navigate" => "Relatório de Acompanhamento", "event" => $event, "events" => $events));
    }

    /**
     * getData action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getDataAction(Application $app, $id)
    {
        if(!$id){

            return $app->json(array(
                'success' => false,
                'message' => 'Evento não informado.'
            ));

        } else {

            $fk_event = $id;

            $data = $app['event']->getReportTrackingByEvent($fk_event);
            foreach ($data as $key => $item) {
                $data[$key][5] = $item[5] == 'male' ? 'Masculino' : 'Feminino';
                $data[$key][22] = $this->formatDate($item[22], "d/m/Y");
                $data[$key][23] = $this->formatDate($item[23], "d/m/Y");
                $data[$key][31] = $item[31] == '2' ? 'associado' : 'não-associado';
            }

            return $app->json(array(
                'data' => $data
            ));

        }

    }

}