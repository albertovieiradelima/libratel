<?php

namespace App\Controller\AbrasceAward;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use App\Model;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller Provider de Registration
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class RegistrationController extends \App\Controller\BaseController
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

        $controller->get('/registrations/{id}', array($this, 'indexAction'))->value('id', false)->bind('registration');
        $controller->get('/registrations/project/{id}/{pdf}', array($this, 'registrationProjectAction'))->value('id')->value('pdf')->bind('registration-project');
        $controller->post('/registrations/{id}/get-data', array($this, 'getDataAction'))->value('id', false)->bind('registration-get-data');
        $controller->post('/registrations/{id}/info', array($this, 'getInfoAction'))->value('id', false)->bind('registration-get-info');
        $controller->post('/registrations/update', array($this, 'updateAction'))->bind('registration-update');

        return $controller;
    }

    /**
     *  Registration index action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function indexAction(Application $app, $id)
    {
        if (!$id) {
            $id = 1;
        }

        $app['aa_event']->getAll();
        $events = $app['aa_event']->fetch_all();
        if(count($events) == 1){
            $id = $events[0]['id'];
        }
        $app['aa_event']->getById((int)$id);
        $event = $app['aa_event']->fetch();

        return $app['twig']->render('admin/abrasce-award/registration.twig', array(
            "modal" => 'admin/abrasce-award/registration-modal.twig',
            "project_modal" => 'admin/abrasce-award/registration-project-modal.twig',
            "navigate" => "Inscrição do Evento",
            "event" => $event, 
            "events" => $events)
        );
    }

    public function registrationProjectAction(Application $app, $id, $pdf = 0) {

        $reg = $app['aa_registration']->getById($id);
        $shopping = $app['shopping']->getShoppingCriteriaById('fantasia', $id);
        $reg['shopping_name'] = $shopping['fantasia'];
        $fields = $app['aa_award_event_field']->getAll($reg['fk_award'], $reg['fk_event'], 'order', 'ASC');
        $award_event = $app['aa_award_event']->getById($reg['fk_award'], $reg['fk_event']);
        $values =  $app['aa_award_event_field_registration']->getValues($id);

        if($values && count($values) > 0){
            foreach($fields as &$obj){
                $obj['value'] = array_key_exists($obj['id'], $values) ? $values[ $obj['id'] ] : null;
            }
        }

        foreach ($fields as $key => $field) {
            if($field['type'] == 'file'){
                $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}";
                $path = '/uploads/premio-abrasce/' . $award_event['fk_event'] . '/' . $award_event['fk_award'] . '/' . $id . '/';
                $path .= $field['value'];
                if(file_exists('.'.$path) && $field['value'] != null){
                    $fields[$key]['value'] = $path;
                } else {
                    $fields[$key]['value'] = null;
                }
            }
        }


        $data = array(
            'idRegistration'        => $id,
            'reg_entity'            => $reg,
            'award_event'           => $award_event,
            'fields'                => $fields,
            'values'                => $values,
            'navigate'              => 'Projeto'
        );

        if ($pdf != 0) {

            $knpdf = new Pdf();
            $knpdf->setBinary('/usr/local/bin/wkhtmltopdf');

            $html = $app['twig']->render('admin/abrasce-award/registration-project.twig', $data);

            // Expressão usada para remover as instruções de impressão
            $regex = '#\<section class="recort"\>(.+?)\<\/section\>#s';

            preg_match($regex, $html, $matches);
            $stream = $knpdf->getOutputFromHtml('<html lang="pt-BR"><head><meta charset="utf-8"></head>'.$matches[1].'</html>');

            return new Response($stream, 200, array('Content-Type' => 'application/pdf'));

        }

        return $app['twig']->render('admin/abrasce-award/registration-project.twig', $data);
    }

   /**
     * getData action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getDataAction(Application $app, $id)
    {
        try {

            $registrations = $app['aa_registration']->getAllForDT(null, $id);

            foreach ($registrations as $key => $registration) {
                $registrations[$key][6] = $this->formatDate($registration[6], "d/m/Y");
                $button = '<div class="btn-group">
                              <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-cog"></i> Opções <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu options" role="menu">
                                <li><a href="registrations/project/'.(int)$registrations[$key][0].'"><i class="fa fa-file-text"></i> Visualizar Projeto</a></li>
                                <li><a href="/site/boleto-premio/'.hash('sha512', (int)$registrations[$key][0], false).'/'.(int)$registrations[$key][0].'" target="_blank"><i class="fa fa-barcode"></i> Gerar Boleto</a></li>
                                <li><a href="/site/boleto-premio/'.hash('sha512', (int)$registrations[$key][0], false).'/'.(int)$registrations[$key][0].'/1" target="_blank"><i class="fa fa-barcode"></i> Gerar Boleto em PDF</a></li>
                              </ul>
                            </div>';
                $registrations[$key][8] = $button;
            }

            return $app->json(array(
                'data' => $registrations
            ));

        } catch(\Exception $ex) {

            return $app->json(array(
                'success' => false,
                'message' => $ex->getMessage()
            ));
        }

    }

   /**
     * getInfo action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getInfoAction(Application $app, Request $request)
    {
        $post = $request->request->all();
        $id = $post['id'];

        try {

            $data = $app['aa_registration']->getInfoById((int)$id);

            // format datetime
            $data['invoice_due_date'] = $this->formatDate($data['invoice_due_date'], 'd/m/Y - H:i:s');

            if ($data['invoice_payment_date'] != '0000-00-00 00:00:00') {
                $data['invoice_payment_date'] = $this->formatDate($data['invoice_payment_date'], 'd/m/Y - H:i:s');
            } else {
                $data['invoice_payment_date'] = '';
            }

            if (!$data) {
                throw new \Exception('Registro não encontrado');
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
     * Registration Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateAction(Application $app, Request $request)
    {
        try {

            $post = $request->request->all();

            $data = array(
                'invoice_payment_date' => $this->formatDateTimeMysql($post['invoice_payment_date']),
                'billing_document_number' => $post['billing_document_number'],
                'billing_name' => $post['billing_name'],
                'billing_zip' => $post['billing_zip'],
                'billing_address' => $post['billing_address'],
                'billing_state' => $post['billing_state'],
                'billing_city' => $post['billing_city'],
                'status' => $post['status']
            );

            if ($app['aa_registration']->updateBilling($data, $post['id'])) {

                return $app->json(array(
                    'success' => true,
                    'message' => 'Inscrição alterada com sucesso!'
                ));

            }

        } catch(\Exception $ex) {
            return $this->error($app, $ex->getMessage());
        }
    }

}