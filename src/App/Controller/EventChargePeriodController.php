<?php

namespace App\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

use App\Model;

/**
 * Controller Provider de Event Charge Period
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class EventChargePeriodController extends \App\Controller\BaseController
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

        $controller->get('/event-charge-period/{id}', array($this, 'indexAction'))->value('id', false)->bind('event-charge-period');
        $controller->post('/event-charge-period/{id}/get-data', array($this, 'getDataAction'))->value('id', false)->bind('event-charge-period-get-data');
        $controller->post('/event-charge-period/insert', array($this, 'insertAction'))->bind('event-charge-period-insert');
        $controller->post('/event-charge-period/update', array($this, 'updateAction'))->bind('event-charge-period-update');
        $controller->post('/event-charge-period/delete', array($this, 'deleteAction'))->bind('event-charge-period-delete');

        return $controller;
    }

    /**
     *  Event Charge Period management action
     *
     * @param \Silex\Application $app
     * @return mixed
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

        return $app['twig']->render('admin/event-charge-period.twig', array("modal" => 'admin/event-charge-period-modal.twig', "navigate" => "Faixas de Cobrança", "event" => $event, "events" => $events));
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
                'message' => 'Evento não informado.'
            ));
        }

        $post = $request->request->all();
        $objId = $post['id'];
        $fk_event = $id;

        if (!$objId) {

            $data = $app['event_charge_period']->getAllByEvent($fk_event);
            foreach ($data as $key => $event) {
                $data[$key][1] = $this->formatDate($event[1], "d/m/Y");
                $data[$key][2] = $this->formatDate($event[2], "d/m/Y");
                $data[$key][3] = $this->moneyFormatReal($event[3]);
                $data[$key][4] = $this->moneyFormatReal($event[4]);
            }

            return $app->json(array(
                'data' => $data
            ));

        } else {
        try{

                $obj = $app['event_charge_period']->getById((int)$objId);

                if (!$obj) {
                    throw new \Exception('Registro não encontrado');
                }

                $obj["start_date"] = $this->formatDate($obj["start_date"], "d/m/Y");
                $obj["end_date"] = $this->formatDate($obj["end_date"], "d/m/Y");
                $obj["associated_price"] = $this->moneyFormatReal($obj["associated_price"]);
                $obj["standard_price"] = $this->moneyFormatReal($obj["standard_price"]);

                return $app->json(array(
                    'success' => true,
                    'data' => $obj
                ));

            } catch (\Exception $ex) {

                return $app->json(array(
                    'success' => false,
                    'message' => $ex->getMessage()
                ));

            }
        }
    }

    /**
     * Event Charge Period Registration
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function insertAction(Application $app, Request $request)
    {
        try {

            $post = $request->request->all();

            $commonData = array('fk_event' => 'Evento', 'associated_price' => 'Valor de associado', 'standard_price' => 'Valor não associado',
                'start_date' => 'Data de início', 'end_date' => 'Data de término');

            // validação de dados
            $validator = new \App\Util\Validator();
            $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

            if ($valid === true) {

                $post['start_date'] = $this->formatDateTimeMysql($post['start_date']);
                $post['end_date'] = $this->formatDateTimeMysql($post['end_date']);
                $post['associated_price'] = $this->moneyFormatDecimal($post['associated_price']);
                $post['standard_price'] = $this->moneyFormatDecimal($post['standard_price']);

                $app['event_charge_period']->insert($post);

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
     * Event Charge Period Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateAction(Application $app, Request $request)
    {

        try {

            $post = $request->request->all();
            $data = array(
                'fk_event' => $post['fk_event'],
                'start_date' => $this->formatDateTimeMysql($post['start_date']),
                'end_date' => $this->formatDateTimeMysql($post['end_date']),
                'associated_price' => $this->moneyFormatDecimal($post['associated_price']),
                'standard_price' => $this->moneyFormatDecimal($post['standard_price'])
            );

            if ($app['event_charge_period']->update($data, $post['id'])) {

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
     * Event Charge Period Delete
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

        if ($app['event_charge_period']->delete((int)$post['id'])) {
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