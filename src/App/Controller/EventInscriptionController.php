<?php

namespace App\Controller;

use App\Util\FormUtil;
use App\Util\DateUtil;

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Util\ResponseError;

use OpenBoleto\Banco\Bradesco;
use OpenBoleto\Agente;

use Knp\Snappy\Pdf;

use App\Model;

/**
 * Controller Provider de Event
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class EventInscriptionController  extends BaseController
{

    protected $sending_email = 'inscricoes@portaldoshopping.com.br';

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {
        $EventInscriptionController  = $app['controllers_factory'];

        $EventInscriptionController->get('/{id}', array($this, 'eventsViewAction'))->value('id', false);
        $EventInscriptionController->get('/inscricao/{id}', array($this, 'eventInscricaoAction'))->assert('id', '\d+');
        $EventInscriptionController->get('/participantes/{register_id}', array($this, 'eventParticipantesAction'));
        $EventInscriptionController->get('/participantes/remove/{register}/{id}', array($this, 'removeParticipanteAction'));
        $EventInscriptionController->post('/participantes/novo', array($this, 'addParticipantesEventAction'));
        $EventInscriptionController->post('/inscricao/save', array($this, 'eventInscricaoSaveAction'));
        $EventInscriptionController->post('/inscricao/finalizar/{register}', array($this, 'endEventAction'));
        $EventInscriptionController->get('/inscricao/visualizar/{register}', array($this, 'viewInscriptionAction'));
        $EventInscriptionController->get('/boleto/{hash}/{id}/{pdf}/{new}', array($this, 'boletoEventAction'))->value('hash')->value('id')->value('pdf')->value('new')->bind('boletoEvent');
        $EventInscriptionController->post('/interesse', array($this, 'eventsInteresseAction'));
        
        return $EventInscriptionController ;
    }

    /**
     * Visualisar Evento
     *
     * @param \Silex\Application $app, Request $request
     * @return \Twig\eventos.twig
     */
    public function eventsViewAction(Application $app, $id) {
        // List all
        $app['event']->getById($id);
        $entity = $app['event']->fetch();

        $datas = $app['event_charge_period']->getByDate(date('Y-m-d'),$id);

        $entity['associated_price'] = $datas['associated_price'];
        $entity['standard_price'] = $datas['standard_price'];

        $timestamp = strtotime($entity['start_date']);
        $year   = date('Y', $timestamp);
        $month  = date('m', $timestamp);
        $monthName = DateUtil::getMonthName($month);

        return $app['twig']->render('site/eventos.twig', array('month' => $month, 'month_name' => $monthName, 'year' => $year, 'entity' => $entity));
    }

    /**
     * Interesse em Evento
     * @param \Silex\Application $app, Request $request
     * @return \Twig\eventos.twig
     */
    public function eventsInteresseAction(Application $app, Request $request) {
        $post = $request->request->all();

        if (!isset($post['register'])) {
            $result = array(
                'success' => false,
                'message' => 'Evento inválido.'
            );
            return $app->json($result);
        }

        // seta os dados em comum para apresentação
        $formData = array('Nome' => 'Nome', 'Cargo' => 'Cargo', 'Telefone' => 'Telefone',
            'CPF' => 'CPF', 'Empresa' => 'Empresa', 'Email' => 'E-mail');

        // dados especiais de validação
        $specialData = array('Email' => 'email');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $formData, "- %f<br>\n", $specialData);

        // verifica se os dados são válidos
        if ($valid === true) {

            // SMTP Configuration
            $smtp = $app['setup']->getSMTPById();

            $app['swiftmailer.options'] = array(
                'host' => $smtp['smtp_host'],
                'port' => $smtp['smtp_port'],
                'username' => $smtp['smtp_user'],
                'password' => $smtp['smtp_pass']
            );

            // configura parâmetros para enviar e-mail
            $message = \Swift_Message::newInstance()
                ->setSubject('Portal ABRASCE - Interesse em Cursos / Eventos')
                ->setFrom(array($post['Email']))
                ->setTo(array($this->sending_email))
                ->setBody($app['twig']->render('email/evento-interesse.twig', array('data' => $post, 'register' => $post['register'], 'date' => $post['data'])), 'text/html');

            // envia e-mail
            $app['mailer']->send($message, $failures);

            if (!$failures) {

                $result = array(
                    'success' => true,
                    'message' => 'Sua solicitação foi realizada com sucesso.<br><br>Um de nossos consultores entrará em contato com maiores informações.<br><br>Obrigado!'
                );

                // salva os dados no banco
                $app['subscriber']->insertSubscriber($post);

            } else {

                $result = array(
                    'success' => false,
                    'message' => 'Ocorreu um erro ao enviar a sua solicitação.<br><br>Por favor, tente novamente ou entre em contato conosco.'
                );
            }
        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Inscrição
     *
     * @param \Silex\Application $app, Request $request
     * @return \Twig\eventos-inscricao.twig
     */
    public function eventInscricaoAction(Application $app, $id) {
        $app['event']->getById($id);
        $entity = $app['event']->fetch();

        $states = FormUtil::getStatesArray();

        return $app['twig']->render('site/eventos-inscricao.twig',array('states'=>$states,'entity'=>$entity));
    }

    /**
     * POST Inscrição de evento
     *
     * @param \Silex\Application $app, Request $request
     * @return \Twig\eventos-participantes.twig
     */
    public function eventInscricaoSaveAction(Application $app, Request $request) {
        try{
            $post = $request->request->all();

            $coupon = $app['event_discount_coupon']->getById($post['fk_coupon']);
            if($coupon == null || $coupon['used_number']>=$coupon['maximum_number']){
                $post['fk_coupon']='';
            }

            $post['status']=4;
            $register_id = $app['event_registration'] ->insert($post);

            return $app->redirect("/site/evento/participantes/$register_id");
        }catch(\Exception $e){
            return $app['twig']->render('errors/error.twig',array('error'=>$e->getMessage(),'code'=>''));
        }
    }

    /**
     * Listagem de Participantes
     *
     * @param \Silex\Application $app, Request $request
     * @return \Twig\eventos-participantes.twig
     */
    public function eventParticipantesAction(Application $app, $register_id) {
        try{
            $app['event']->getEventRegistered($register_id);
            $evento = $app['event']->fetch();

            if($evento==null){
                throw new \Exception("Registro no evento não encontrado");
            }

            $participantes = $app['event_registration_participants']->getAllByRegister($register_id);

            $evento['fk_event_registration']=$register_id;
            $evento['company_filiation']=='2'?$evento['price']=$evento['associated_price']:$evento['price']=$evento['standard_price'];

            $evento['total_price'] = $this->calcPrice(sizeof($participantes),$evento);
            $evento['price']=number_format($evento['price'],2,',','.');

            return $app['twig']->render('site/eventos-participantes.twig',array('evento'=>$evento,'participantes'=>$participantes));
        }catch (\Exception $e){
            return $app['twig']->render('errors/error.twig',array('error'=>$e->getMessage(),'code'=>''));
        }
    }

    /**
     * Revome participante na inscrição
     *
     * @param \Silex\Application $app, Request $request
     * @return \Twig\eventos-participantes.twig
     */
    public function removeParticipanteAction(Application $app, $register, $id) {
        try{
            $app['event_registration_participants']->delete($id);
            return $app->redirect("/site/evento/participantes/$register");
        }catch (\Exception $e){
            return $app['twig']->render('errors/error.twig',array('error'=>$e->getMessage(),'code'=>''));
        }
    }

    /**
     * calcula o valor total da inscrição
     * @param $qtd_participantes
     * @param $evento
     * @return string
     */
    private function calcPrice($qtd_participantes,$evento){
        $total_price = $qtd_participantes * $evento['price'];
        $coupons_disp = $evento['coupon_max'] - $evento['coupon_used'];

        if($evento['fk_coupon']!='' && $qtd_participantes >= (int)$evento['coupon_min']  && $qtd_participantes <= (int)$coupons_disp){
            $discount = $qtd_participantes * $evento['discount'];
            return number_format($total_price - (($discount * $total_price)/100),2,',','.');
        }else{
            return number_format($total_price,2,',','.');
        }
    }

    /**
     * Finaliza a inscrição no evento ou curso
     * @param Application $app
     * @param Resquest $request
     * @param $register
     */
    public function endEventAction(Application $app, $register){
        try{
            $app['event']->getEventRegistered($register);
            $evento = $app['event']->fetch();

            if($evento==null){
                throw new \Exception("Registro no evento não encontrado");
            }

            $participantes = $app['event_registration_participants']->getAllByRegister($register);
            $evento['company_filiation']=='2'?$evento['price']=$evento['associated_price']:$evento['price']=$evento['standard_price'];
            $price = $this->calcPrice(sizeof($participantes),$evento);

            if($evento['fk_coupon']!=''){
                $qtd_participantes = sizeof($participantes);
                $coupons_disp = $evento['coupon_max'] - $evento['coupon_used'];

                if($qtd_participantes >=(int)$evento['coupon_min'] && $qtd_participantes <= (int)$coupons_disp){
                    $used = $qtd_participantes + (int)$evento['coupon_used'];
                    $app['event_discount_coupon']->update(array('used_number'=>$used),$evento['fk_coupon']);
                }else{
                    $updateData['fk_coupon']='';
                }
            }

            $updateData['status'] = 3;
            $updateData['created_date'] = date('Y-m-d H:i:s');
            $updateData['invoice_number'] = '25/' . str_pad($register, 11, '0', STR_PAD_LEFT) . '-0';
            $updateData['invoice_value'] = str_replace(['.',','],['','.'],$price);

            $app['event_registration']->update($updateData,$register);

            $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/site/evento/boleto/";
            $boleto = $url . hash('sha512', $register, false) . '/' . $register . '/1';

            $event_register = $app['event_registration']->getById($register);
            $this->sendEmailSubscription($app,$register,$boleto);

            return $app->redirect("/site/evento/inscricao/visualizar/$register");
        }catch (\Exception $e){
            return $app['twig']->render('errors/error.twig',array('error'=>$e->getMessage(),'code'=>''));
        }
    }

    /**
     * Visualiza a inscrição no evento ou curso
     * @param Application $app
     * @param Resquest $request
     * @param $register
     */
    public function viewInscriptionAction(Application $app, $register){
        try{
            $app['event']->getEventRegistered($register);
            $evento = $app['event']->fetch();
            $evento['start_date'] = $this->formatDate($evento['start_date'], "d/m/Y");
            $evento['end_date'] = $this->formatDate($evento['end_date'], "d/m/Y");

            if($evento==null){
                throw new \Exception("Registro no evento não encontrado");
            }

            $participantes = $app['event_registration_participants']->getAllByRegister($register);

            $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/site/evento/boleto/";
            $boleto = $url . hash('sha512', $register, false) . '/' . $register . '/1';

            $event_register = $app['event_registration']->getById($register);

            return $app['twig']->render('site/eventos-finalizado.twig',array('evento'=>$evento,'registro'=>$event_register,'link_boleto' => $boleto,'participantes'=>$participantes));
        }catch (\Exception $e){
            return $app['twig']->render('errors/error.twig',array('error'=>$e->getMessage(),'code'=>''));
        }
    }

    /**
     * POST novo participante
     * Listagem de Participantes
     *
     * @param \Silex\Application $app, Request $request
     * @return \Twig\eventos-participantes.twig
     */
    public function addParticipantesEventAction(Application $app, Request $request) {
        try {
            $post = $request->request->all();

            $app['event_registration_participants']->insert($post);
            $register = $post['fk_event_registration'];

            return $app->redirect("/site/evento/participantes/$register");
        }catch (\Exception $e){
            return $app['twig']->render('errors/error.twig',array('error'=>$e->getMessage(),'code'=>$e->getCode()));
        }
    }

    /**
     * BoletoEvent action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function boletoEventAction(Application $app, $hash, $id, $pdf = 0,$new) {
            $new!=null?$check = $id.$new:$check = $id;

            if (hash('sha512', $check, false) != $hash) {
                return $app->abort(Response::HTTP_NOT_FOUND);
            }

            $boleto = $app['setup']->getDataBoletoById();

            if (!$boleto) {
                return $app->abort(Response::HTTP_BAD_REQUEST, 'Dados do Cedente não encontrado.');
            }

            // Pega os dados da cobrança, período de vencimento e o valor da inscrição
            $reg = $app['event_registration']->getById($id);

            if (!$reg) {
                return $app->abort(Response::HTTP_BAD_REQUEST, 'Dados de Cobrança e Vencimento não encontrado.');
            }

            $sacado = new Agente($reg['billing_name'], $reg['billing_document'], $reg['billing_address'], $reg['billing_zip'], $reg['billing_city'], $reg['billing_state']);
            $cedente = new Agente($boleto['cedente_name'], $boleto['cedente_cnpj'], $boleto['cedente_address'], $boleto['cedente_zip'], $boleto['cedente_city'], $boleto['cedente_state']);

            if($new!=null){
                $date = date('Y-m-d',$new);
                $dueDate = new \DateTime($date);
            }else{
                $dueDate = new \DateTime('now');

                if ($reg['billing_days_to_due'] > 0) {
                    $dueDate->modify("+{$reg['days_invoice']} days");
                } else {
                    $dueDate->modify('+10 days');
                }
            }

            $app['event_registration']->update(array('invoice_due_date'=>$dueDate->format('Y-m-d H:i:s')),$id);

            $boleto = new Bradesco(array(
                'dataVencimento' => $dueDate,
                'valor' => $reg['invoice_value'],
                'sequencial' => $id, // Id sequencial do nosso número
                'sacado' => $sacado,
                'cedente' => $cedente,
                'agencia' => $boleto['cedente_agencia'], // Até 4 dígitos
                'conta' => $boleto['cedente_conta'], // Até 8 dígitos
                'convenio' => 1234, // 4, 6 ou 7 dígitos
                'carteira' => $boleto['cedente_carteira'],
                'contaDv' => $boleto['cedente_conta_dv'],
                'agenciaDv' => $boleto['cedente_agencia_dv'],
                'descricaoDemonstrativo' => array( // Até 5
                    $boleto['cedente_label1'],
                    $boleto['cedente_label2'],
                    $boleto['cedente_label3']
                ),
                'instrucoes' => array( // Até 8
                    $boleto['cedente_label4'],
                    $boleto['cedente_label5'],
                    $boleto['cedente_label6']
                )
            ));

            if ($pdf) {
                $options = array(
                    'page-size' => 'A4',
                    'margin-left' => '12.5',
                    'margin-right' => '12.5',
                    'password' => '123'
                );

                $knpdf = new Pdf(null, $options);
                $knpdf->setBinary('/usr/local/bin/wkhtmltopdf');

                // Expressão usada para remover as instruções de impressão
                // $html = preg_replace('!<div\s+class="noprint info">.*?</div>!is', '', $boleto->getOutput());

                $stream = $knpdf->getOutputFromHtml($boleto->getOutput());

                return new Response($stream, 200, array('Content-Type' => 'application/pdf'));
            }

            return new Response($boleto->getOutput());
    }

    /**
     * Send E-mail Subscription
     * @param $id
     * @return array
     */
    public function sendEmailSubscription($app, $id, $boleto) {
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
            ->setSubject('Portal ABRASCE - Inscrição em '. strtoupper($event['title']))
            ->setFrom(array($this->sending_email))
            ->setTo(array($reg['responsible_email']))
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