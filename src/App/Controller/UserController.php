<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de User
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class UserController extends BaseController {
    protected $sending_email = 'noreply@portaldoshopping.com.br';

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app) {

        $userController = $app['controllers_factory'];

        $userController->get('/users', array($this, 'usersAction'))->bind('users');
        $userController->post('/get-users', array($this, 'getUsersAction'))->bind('get-users');
        $userController->post('/users/insert', array($this, 'insertUserAction'))->bind('new-user');
        $userController->post('/users/update', array($this, 'updateUserAction'))->bind('edit-user');
        $userController->post('/users/delete', array($this, 'deleteUserAction'))->bind('remove-user');

        return $userController;
    }

    /**
     * Users action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function usersAction(Application $app) {

        return $app['twig']->render('admin/users.twig', array("modal" => "admin/user-modal.twig", "navigate" => "Usuários"));
    }

    /**
     * getUsers action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getUsersAction(Application $app, Request $request) {
        $post = $request->request->all();

        if (!$post['id']) {

            $app['user']->getAllUsers("id, fullname, email, roles, status");
            $users = $app['user']->fetch_all(MYSQLI_NUM);

            foreach ($users as $key => $user) {
                if($user[4] == "active"){
                    $users[$key][4] = "Ativo";
                }elseif($user[4] == "inactive"){
                    $users[$key][4] = "Inativo";
                }else{
                    $users[$key][4] = "Verificação de e-mail";
                }
            }

            return $app->json(array("data" => $users));

        } else {

            $app['user']->getById((int)$post['id']);
            $user = $app['user']->fetch();
            if(is_array($user)){
                $result = array(
                    'success' => true,
                    'user' => $user
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar o usuário.'
                );
            }
            
            return $app->json($result);

        }
        
    }

    /**
     * User Registration
     * 
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertUserAction(Application $app, Request $request) {

        $post = $request->request->all();

        $commonData = array('cpf'=>'CPF','name' => 'Nome', 'email' => 'E-mail',
            'username' => 'Usuário', 'password' => 'Senha','phone'=>'Telefone','job'=>'Cargo','area'=>'Área');

        // dados especiais de validação
        $specialData = array('email' => 'email');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        //Verifica se o usuário já existe
        $app['user']->getByCriteria("username = '{$post['username']}' OR email = '{$post['email']}' OR cpf='{$post['cpf']}'");
        $user = $app['user']->fetch();

        if($user != null){
            $check = [
                $user['username']==$post['username']?'<br>-Nome de usuário já existe':'',
                $user['email']==$post['email']?'<br>-Email já existe':'',
                $user['cpf']==$post['cpf']?'<br>-CPF já existe':'',
            ];

            return $this->error($app, implode('',$check));
        }

        if ($valid === true) {
            $status = $post['status'] === "true" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $avatar = $post['avatar']==''?'perfil.png':$post['avatar'];

            $password = $app['security.encoder.digest']->encodePassword($post['password'], '');

            if ($app['user']->insert($post['name'], $post['email'], $post['username'], $password, $avatar, $status,$post['cpf'],$post['phone'],$post['job'],$post['area'])) {
                $result = array(
                    'success' => true,
                    'message' => 'Usuário registrado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar o usuário. Verifique se o usuário já foi cadastrado.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * User Update
     * 
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateUserAction(Application $app, Request $request) {
        $post = $request->request->all();

        $commonData = array('cpf'=>'CPF','id' => 'Usuário não encontrado', 'name' => 'Nome', 'email' => 'E-mail',
            'username' => 'Usuário','phone'=>'Telefone','job'=>'Cargo','area'=>'Área');

        // dados especiais de validação
        $specialData = array('email' => 'email');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        //Verifica se o usuário já existe
        $app['user']->getByCriteria("(username = '{$post['username']}' OR email = '{$post['email']}' OR cpf='{$post['cpf']}') AND id <> {$post['id']} ");
        $user = $app['user']->fetch();

        if($user != null){
            $check = [
                $user['username']==$post['username']?'<br>-Nome de usuário já existe':'',
                $user['email']==$post['email']?'<br>-Email já existe':'',
                $user['cpf']==$post['cpf']?'<br>-CPF já existe':'',
            ];

            return $this->error($app, implode('',$check));
        }

        if ($valid === true) {
            $status = $post['status'] === "true" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $password = null;
                
            if ($post['password']) {
                $password = " , password = '".$app['security.encoder.digest']->encodePassword($post['password'], '')."'";
            }
            if ($app['user']->update("fullname = '{$post['name']}', email = '{$post['email']}', username = '{$post['username']}', avatar = '{$post['avatar']}', status = {$status}, phone = '{$post['phone']}', cpf = '{$post['cpf']}', job = '{$post['job']}', area = '{$post['area']}' ".$password, (int)$post['id'])) {
                $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/admin";

                if($status==\App\Enum\SituationStatusEnum::ACTIVE){
                    $body="<p>Prezado(a) {$post['name']}</p><br><p>Seu cadastro no portal ABRASCE foi ativado.</p>
                    <p><a target='_blank' href='{$url}'>Clique aqui</a> e acesse o painel admin do portal ABRASCE</p>";

                    $this->sendEmail($app,$body,$post['email'],'Confirmação de cadastro - ABRASCE');
                }

                $result = array(
                    'success' => true,
                    'message' => 'Usuário alterado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar o usuário.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * User Delete
     * 
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteUserAction(Application $app, Request $request) {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Usuário não encontrado.');
        }

        if ($app['user']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Usuário removido com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o usuário.'
            );
        }

        return $app->json($result);
    }

    /**
     * Send e-mail to reconfigure password
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

}