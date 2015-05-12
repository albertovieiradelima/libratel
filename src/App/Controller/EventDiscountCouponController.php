<?php
/**
 * Created by PhpStorm.
 * User: albertovieira
 * Date: 3/30/15
 * Time: 6:14 PM
 */

namespace App\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

use App\Model;

/**
 * Controller Provider de Event Discount Coupon
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class EventDiscountCouponController extends \App\Controller\BaseController
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

        $controller->get('/event-discount-coupon', array($this, 'indexAction'))->bind('event-discount-coupon');
        $controller->post('/event-discount-coupon/get-data', array($this, 'getDataAction'))->bind('event-discount-coupon-get-data');
        $controller->post('/event-discount-coupon/get-event-data', array($this, 'getEventDataAction'))->bind('event-discount-coupon-get-event-data');
        $controller->post('/event-discount-coupon/insert', array($this, 'insertAction'))->bind('event-discount-coupon-insert');
        $controller->post('/event-discount-coupon/update', array($this, 'updateAction'))->bind('event-discount-coupon-update');
        $controller->post('/event-discount-coupon/delete', array($this, 'deleteAction'))->bind('event-discount-coupon-delete');

        return $controller;
    }

    /**
     *  Event Discount Coupon management action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function indexAction(Application $app)
    {
        return $app['twig']->render('admin/event-discount-coupon.twig', array("modal" => 'admin/event-discount-coupon-modal.twig', "navigate" => "Cupons de Desconto"));
    }

    /**
     * getData action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getDataAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $data = $app['event_discount_coupon']->getAll('c.id, c.created_date, e.title, c.company, c.contact, c.minimum_number, c.maximum_number, c.used_number, c.expiration_date');
            foreach ($data as $key => $cupom) {
                $data[$key][1] = $this->formatDate($cupom[1], "d/m/Y");
                $data[$key][8] = $this->formatDate($cupom[8], "d/m/Y");
            }

            return $app->json(array(
                'data' => $data
            ));

        } else {

            try {

                $obj = $app['event_discount_coupon']->getById($post['id']);

                if (!$obj) {
                    throw new \Exception('Registro não encontrado');
                }

                $obj["expiration_date"] = $this->formatDate($obj["expiration_date"], "d/m/Y");

                return $app->json(array(
                    'success' => true,
                    'coupon' => $obj
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
     * Event Discount coupon Registration
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function insertAction(Application $app, Request $request)
    {
        try {

            $post = $request->request->all();
            $coupon = null;

            $commonData = array('fk_event' => 'Evento', 'company' => 'Empresa', 'contact' => 'Contato', 'minimum_number' => 'Quantidade mínima',
                'maximum_number' => 'Quantidade máxima', 'discount_participant' => 'Percentual de desconto', 'expiration_date' => 'Validade');

            // dados especiais de validação
            $specialData = array('email' => 'email');

            // validação de dados
            $validator = new \App\Util\Validator();
            $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

            if ($valid === true) {

                $app['event']->getById($post['fk_event']);
                $event = $app['event']->fetch();
                if($event){
                    $title = explode(" ", $event['title']);
                    foreach ($title as $value) {
                        $coupon.= substr($value,0,1);
                    }
                }

                $post['id'] = strtoupper($coupon).(time()+mt_rand());
                $post['expiration_date'] = $this->formatDateMysql($post['expiration_date']);
                $post['created_date'] = date("Y-m-d");

                $app['event_discount_coupon']->insert($post);

                return $app->json(array(
                    'success' => true,
                    'message' => 'Cupom de desconto gerado com sucesso!'
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
     * Event Discount coupon Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateAction(Application $app, Request $request)
    {

        try {

            $post = $request->request->all();

            $commonData = array('fk_event' => 'Evento', 'company' => 'Empresa', 'contact' => 'Contato', 'minimum_number' => 'Quantidade mínima',
                'maximum_number' => 'Quantidade máxima', 'discount_participant' => 'Percentual de desconto', 'expiration_date' => 'Validade');

            // dados especiais de validação
            $specialData = array('email' => 'email');

            // validação de dados
            $validator = new \App\Util\Validator();
            $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

            if ($valid === true) {

                $data = array(
                    'fk_event' => $post['fk_event'],
                    'company' => $post['company'],
                    'email' => $post['email'],
                    'contact' => $post['contact'],
                    'minimum_number' => $post['minimum_number'],
                    'maximum_number' => $post['maximum_number'],
                    'discount_participant' => $post['discount_participant'],
                    'expiration_date' => $this->formatDateTimeMysql($post['expiration_date']),
                    'observations' => $post['observations']
                );

                $app['event_discount_coupon']->update($data, $post['id']);

                return $app->json(array(
                    'success' => true,
                    'message' => 'Cupom de desconto alterado com sucesso!'
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
     * Event Discount coupon Delete
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

        if ($app['event_discount_coupon']->delete($post['id'])) {
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

    /**
     * getData action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getEventDataAction(Application $app)
    {

        $app['event']->getAll('id, title');
        $data = $app['event']->fetch_all();

        if ($data) {

            $result = $data;
        } else {

            $result = array(
                'success' => false,
                'message' => 'Nenhum registro encontrado.'
            );
        }

        return $app->json($result);
    }

}