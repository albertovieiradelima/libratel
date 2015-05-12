<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de Login
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class AdminController implements ControllerProviderInterface {
    protected $sending_email = 'noreply@portaldoshopping.com.br';
   // protected $admin_email = 'sarah@abrasce.com.br';
    protected $admin_email = 'andre.paulena@crmall.com';

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app) {

        $userController = $app['controllers_factory'];

        $userController->get('/login', array($this, 'loginAction'))->bind('login');
        $userController->get('/logout', array($this, 'logoutAction'))->bind('logout');
        $userController->get('/check-email/{hash}', array($this, 'checkEmailAction'))->value('hash');
        $userController->post('/cadastro', array($this, 'signUpAction'));
        $userController->get('/nova-senha/{hash}', array($this, 'newPasswordAction'))->value('hash')->bind('nova-senha');
        $userController->post('/send-redefine/{hash}', array($this, 'sendRedefineAction'))->value('hash')->bind('send-redefine');

        return $userController;
    }

    /**
     * Login action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function loginAction(Application $app) {

        return $app['twig']->render('user/login.twig');
    }

    /**
     * Logout action
     *
     * @param \Silex\Application $app
     * @return type
     */
    public function logoutAction(Application $app) {
        $app['session']->clear();

        return $app->redirect($app['url_generator']->generate('login'));
    }

    /**
     * New Password action
     * @param \Silex\Application $app
     * @return mixed
     */
    public function newPasswordAction(Application $app,$hash=null) {
        if($hash != null) {
            $data = $this->checkUserByHash($app, $hash);
        }

        return $app['twig']->render('user/new-password.twig',array('data'=>$data));
    }

    /**
     * Post to send e-mail and change password
     * @param Application $app
     * @param Request $request
     * @param null $hash
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function sendRedefineAction(Application $app,Request $request,$hash=null){
        try{
            $post = $request->request->all();

            if($hash != null) {
                $data = $this->checkUserByHash($app, $hash);

                if(isset($data['error']) || $data['username']!= $post['username']){
                    return $app->json(["success"=>false,"msg"=>isset($data['error'])?$data['error']:'Nome de usuário não encontrado']);
                }

                $password = $app['security.encoder.digest']->encodePassword($post['new-password'], '');
                $app['user']->update("password = '{$password}'",$data['id']);

                $date = new \DateTime('NOW');

                $body = "<p>Prezado(a) {$data['user']}</p><br><p> Sua senha foi redefinida com sucesso.<br>
                    Data {$date->format('d/m/Y')}  às {$date->format('H:i:s')}.</p>";

                $this->sendEmail($app,$body,$data['email'],'Redefinição de Senha - ABRASCE');

                return $app->json(['success'=>true,'msg'=>'Sua senha foi alterada com sucesso']);
            }else{
                $app['user']->getByUserName($post['username']);
                $user = $app['user']->fetch();

                if($user == null){
                    return $app->json(["success"=>false,"msg"=>"Nome de usuário não encontrado"]);
                }

                $date = new \DateTime('NOW');
                $date->modify('+1 hour');

                $hash = sprintf("%s_%s_%s",hash('sha512',$user['id'].$user['password']),$user['id'],$date->getTimestamp());
                $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/admin/nova-senha/{$hash}";

                $email = explode("@",$user['email']);

                for($i=0;$i<=(int)strlen($email[0])/2;$i++){
                    $email[0][$i]='*';
                }

                $email = implode('@',$email);

                $body = "<p>Prezado(a) {$user['fullname']}</p><br><p>Clique <a href=\"{$url}\" target=\"_blank\">aqui</a> para redefinir sua senha, caso não tenha solicitado a redefição ignore esse e-mail,
                    o link será válido até o dia {$date->format('d/m/Y')}  às {$date->format('H:i:s')}.</p>";

                if($this->sendEmail($app,$body,$user['email'],'Redefinição de Senha - ABRASCE') == 1){
                    return $app->json(['success'=>true,'msg'=>"Um e-mail de redefinição de senha foi enviado para {$email}"]);
                }else{
                    return $app->json(["success"=>false,"msg"=>"Ocorreu um erro ao enviar o e-mail"]);
                }
            }
        }catch (\Exception $e){
            return $app->json(['success'=>false,'msg'=>"Ocorreu um erro, tente novamente mais tarde ou entre em contato."]);
        }
    }

    /**
     * Check if the hash is valid
     *
     * @param $app
     * @param $hash
     * @return array
     */
    private function checkUserByHash($app,$hash){
        $check = explode('_',$hash);

        if(strtotime('NOW') < $check[2]){
            $app['user']->getById($check[1]);
            $user = $app['user']->fetch();

            if($user!=null && hash('sha512',$user['id'].$user['password']) == $check[0]){
                return ['id'=>$user['id'], 'user'=>$user['fullname'],'username'=>$user['username'],'hash'=>$hash,'email'=>$user['email']];
            }else{
                return ['error'=>'Endereço inválido, por favor, tente novamente.'];
            }

        }else{
            return ['error'=>'Seu link expirou, por favor, tente novamente.'];
        }
    }

    /**
     * Send e-mail
     * @param $app
     * @param string $bodyMessage
     * @param $email
     * @return int
     */
    private function sendEmail($app,$bodyMessage='',$email,$subject=''){
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

        // configura parâmetros para enviar e-mail
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array($this->sending_email))
            ->setTo($email)
            ->setBody($app['twig']->render('email/email-default.twig',array('message'=>$bodyMessage)), 'text/html');

        // envia e-mail
       return $mailer->send($message);
    }

    /**
     * User initial registration
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function signUpAction(Application $app, Request $request){
        $post = $request->request->all();

        $app['user']->getByCriteria("username = '{$post['username']}' OR email = '{$post['email']}' OR cpf='{$post['cpf']}'");
        $user = $app['user']->fetch();

        if($user != null){
            return $app->json(['success'=>false,'msg'=>"Esse e-mail ou nome de usuário já estão cadastrados"]);
        }else{
            $password = $app['security.encoder.digest']->encodePassword($post['password'], '');

            if($app['user']->insert($post['fullname'], $post['email'], $post['username'], $password, 'perfil.png', 3,$post['cpf'],$post['phone'],$post['job'],$post['area'])){
                $app['user']->getByCriteria("username = '{$post['username']}'");
                $user = $app['user']->fetch();

                $hash = sprintf("%s_%s",hash('sha512',$user['id'].$user['password'].$user['status']),$user['id']);
                $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/admin/check-email/{$hash}";

                $body = "<p>Prezado(a) {$post['fullname']}</p><br><p>Clique <a href=\"{$url}\" target=\"_blank\">aqui</a> para confirmar seu cadastro na ABRASCE,
                        caso não tenha feito esse cadastro, por favor, ignore esse e-mail.</p>";

                $this->sendEmail($app,$body,$user['email'],'Cadastro ABRASCE - Confirmação');

                return $app->json(['success'=>true,'msg'=>"Um e-mail de confirmação foi enviado ao endereço cadastrado, obrigado."]);
            }else{
                return $app->json(['success'=>false,'msg'=>"Ocorreu um erro inesperado, tente novamente mais tarde ou entre em contato"]);
            }
        }
    }

    /**
     * Confirm e-mail registration
     * @param Application $app
     * @param null $hash
     */
    public function checkEmailAction(Application $app,$hash=null){
        $check = explode('_',$hash);

        $app['user']->getById($check[1]);
        $user = $app['user']->fetch();

        if($user!=null && hash('sha512',$user['id'].$user['password'].$user['status']) == $check[0]){
            $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/admin/users";

            $body="<p>Nova solicitação de cadastro de usuário</p><br><p><strong>Nome: </strong>{$user['fullname']}</p><p><strong>E-mail: </strong>{$user['email']}</p><br>
                  <p>Para ativá-lo, <a target='_blank' href='{$url}'>clique aqui</a> e acesse o painel admin do portal ABRASCE</p>";

            if($app['user']->update(sprintf("status = %s",\App\Enum\SituationStatusEnum::INACTIVE),$user['id'])){
                $this->sendEmail($app,$body,$this->admin_email,"Solicitação de cadastro de usuário");
                return $app['twig']->render('user/check-email.twig',array('msg'=>'Obrigado, seu e-mail foi validado e uma soliticação enviada à ABRASCE, entraremos em contato em breve.'));
            }else{
                return $app['twig']->render('user/check-email.twig',array('msg'=>'Ocorreu um erro com a sua solicitação, por favor, tente novamente mais tarde.'));
            }
        }else{
            return $app['twig']->render('user/check-email.twig',array('msg'=>'Ocorreu um erro com a sua solicitação, por favor, tente novamente mais tarde.'));
        }
    }
}