<?php

namespace App\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

use App\Model;

/**
 * Controller Provider de Event Registration
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class EventRegistrationController extends \App\Controller\BaseController
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

        $controller->get('/event-registration/{id}', array($this, 'indexAction'))->value('id', false)->bind('event-registration');
        $controller->post('/event-registration/{id}/get-data', array($this, 'getDataAction'))->value('id', false)->bind('event-registration-get-data');
        $controller->post('/event-registration/update', array($this, 'updateAction'))->bind('event-registration-update');
        $controller->get('/event-registration/register/{id}', array($this, 'getRegisterAction'))->value('id', false)->bind('event-registration-register');
        $controller->post('/event-registration/resend-boleto/', array($this, 'resendBoletoAction'));

        return $controller;
    }

    /**
     *  Event Registration management action
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

        return $app['twig']->render('admin/event-registration.twig', array("modal" => 'admin/event-registration-modal.twig', "navigate" => "Inscrições do Evento/Curso", "event" => $event, "events" => $events));
    }


    /**
     * Recupera o e-mail e prepara o form para reenvio de boletos
     * @param Application $app
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getRegisterAction(Application $app,$id){
        try{
            $reg = $app['event_registration']->getById($id);

            // Data de vencimento
            $dueDate = new \DateTime('now');

            // Modifica a data de vencimento do boleto
            if ($reg['billing_days_to_due'] > 0) {
                $dueDate->modify("+{$reg['days_invoice']} days");
            } else {
                $dueDate->modify('+10 days');
            }

            return $app->json(array(
                'success' => true,
                'data' => array('email'=>$reg['responsible_email'],'dueDate'=>$dueDate->format('d/m/Y'))
            ));
        }catch (\Exception $e){
            return $this->error($app, $e->getMessage());
        }
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

        if (!$objId) {

            try {

                $data = $app['event_registration']->getAllByEvent($fk_event);
                foreach ($data as $key => $event) {
                    $data[$key][1] = $this->formatDate($event[1], "d/m/Y");
                    $data[$key][5] = $this->formatDate($event[5], "d/m/Y");
                    $data[$key][6] = $this->moneyFormatReal($event[6]);
                    $data[$key][8] = null;
                    if ($event[7] == 'pendente') {
                        $button = '
                            <div class="btn-group">
                              <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-cog"></i> Opções <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu options" role="menu">
                                <li><a href="javascript:void(0)" onclick="newInvoice('.$event[0].')"><i class="fa fa-barcode"></i> Enviar Novo Boleto</a></li>
                                <li class="divider"></li>
                                <li><a href="javascript:void(0)" onclick="confirmEventRegistration('.$event[0].')"><i class="fa fa-check-circle"></i> Confirmar Inscrição</a></li>
                                <li><a href="javascript:void(0)" onclick="cancelEventRegistration('.$event[0].')"><i class="fa fa-minus-circle"></i> Cancelar Inscrição</a></li>
                              </ul>
                            </div>';
                        $data[$key][8] = $button;
                    }

                }

                return $app->json(array(
                    'data' => $data
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
     * Event Registration Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateAction(Application $app, Request $request)
    {

        try {

            $post = $request->request->all();
            $data = array(
                'status' => $post['status'],
                'invoice_payment_date' => $this->formatDateMysql($post['invoice_payment_date'])
            );

            if ($app['event_registration']->update($data, $post['id'])) {

                return $app->json(array(
                    'success' => true,
                    'message' => 'Confirmação realizada com sucesso!'
                ));

            }


        } catch (\Exception $ex) {
            return $this->error($app, $ex->getMessage());
        }
    }

    /**
     * Reenvia boleto com a nova data e novo e-mail
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */

    public function resendBoletoAction(Application $app,Request $request){
        try{

            $post = $request->request->all();

            $commonData = array('invoice_payment_date' => 'Data de vencimento', 'email' => 'E-mail');

            // dados especiais de validação
            $specialData = array('email' => 'email');

            // validação de dados
            $validator = new \App\Util\Validator();
            $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

            if ($valid === true) {

                $invoice_date = strtotime($this->formatDateMysql($post['invoice_payment_date']));

                $id = $post['id'];
                $hash = $id . $invoice_date;
                $email = $post['email'];

                $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/site/evento/boleto/";
                $boleto = $url . hash('sha512', $hash, false) . '/' . $id . '/1/' . $invoice_date;

                $this->sendEmailSubscription($app, $id, $boleto, $email);

                return $app->json(array(
                    'success' => true,
                    'message' => 'Boleto enviado com sucesso!'
                ));

            } else {
                $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
                return $this->error($app, $message);
            }

        }catch(\Exception $e){
            return $this->error($app, $e->getMessage());
        }

    }

    /**
     * Send E-mail Subscription
     * @param $id
     * @return array
     */
    public function sendEmailSubscription($app, $id, $boleto,$email) {
        // Pega os dados da cobrança
        $reg = $app['event_registration']->getById($id);

        if (!$reg) {
            return $app->abort(Response::HTTP_BAD_REQUEST, 'Dados não encontrados.');
        }

        // Pega o período de vencimento e o valor da inscrição
        $app['event']->getById($reg['fk_event']);
        $event = $app['event']->fetch();

        if (!$event) {
            return $app->abort(Response::HTTP_BAD_REQUEST, 'Evento não encontrado.');
        }

        // SMTP Configuration
        $smtp = $app['setup']->getSMTPById();

        $app['swiftmailer.options'] = array(
            'host' => $smtp['smtp_host'],
            'port' => $smtp['smtp_port'],
            'username' => $smtp['smtp_user'],
            'password' => $smtp['smtp_pass']
        );

        $transport = \Swift_SmtpTransport::newInstance($smtp['smtp_host'], $smtp['smtp_port'], 'tls')
            ->setUsername($smtp['smtp_user'])
            ->setPassword($smtp['smtp_pass']);

        $mailer = \Swift_Mailer::newInstance($transport);

        setlocale( LC_ALL, 'pt_BR', 'pt_BR.iso-8859-1', 'pt_BR.utf-8', 'portuguese' );
        date_default_timezone_set( 'America/Sao_Paulo' );
        $data = strftime( '%A, %d de %B de %Y às %H:%M', strtotime( date( 'Y-m-d H:i' ) ) );

        // configura parâmetros para enviar e-mail
        $message = \Swift_Message::newInstance()
            ->setSubject('Portal ABRASCE - Inscrição em '.strtoupper($event['title']))
            ->setFrom(array($smtp['smtp_email']))
            ->setTo(array($email))
            ->setBody($app['twig']->render('email/inscricao-evento.twig', array('evento' => $event, 'data'=>ucfirst($data), 'registration' => $reg, 'boleto' => $boleto)), 'text/html');

        // envia e-mail
        if ($mailer->send($message) == 1) {
            $result = array(
                'success' => true,
                'message' => 'Sua inscrição foi efetivada com sucesso!',
                'email' => $reg['responsible_email']
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Ocorreu um erro ao enviar e-mail da sua inscrição.<br><br>Por favor, tente novamente ou entre em contato conosco.',
                'email' => false
            );
        }

        return $result;
    }

}