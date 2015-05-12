<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de Client
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class ClientController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $clientController = $app['controllers_factory'];

        $clientController->get('/clients', array($this, 'clientsAction'))->bind('clients');
        $clientController->post('/get-clients', array($this, 'getClientsAction'))->bind('get-clients');
        $clientController->post('/client/insert', array($this, 'insertClientAction'))->bind('new-client');
        $clientController->post('/client/update', array($this, 'updateClientAction'))->bind('edit-client');
        $clientController->post('/client/delete', array($this, 'deleteClientAction'))->bind('remove-client');

        return $clientController;
    }

    /**
     * client action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function clientsAction(Application $app)
    {
        return $app['twig']->render('admin/clients.twig', array("modal" => "admin/client-modal.twig", "navigate" => "Clientes"));
    }

    /**
     * getClients action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getClientsAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['client']->getAll('id, name, cpf_cnpj, email, phone, type, status');
            $clients = $app['client']->fetch_all(MYSQLI_NUM);
            foreach ($clients as $key => $client) {
                $clients[$key][6] = $clients[$key][6] == 'active' ? 'Ativo' : 'Inativo';
            }

            $data = array("data" => $clients);
            return $app->json($data);

        } else {

            $app['client']->getById((int)$post['id']);
            $client = $app['client']->fetch();
            $client['date'] = $this->formatDate($client['date'], 'd/m/Y - H:i:s');
            $client['type'] = $client['type'] == 'Física' ? '1' : '2';

            if (is_array($client)) {
                $result = array(
                    'success' => true,
                    'client' => $client
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar o contato.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * Client Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertClientAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        $commonData = array('name' => 'Nome', 'cpf_cnpj' => 'Cpf ou Cnpj', 'email' => 'E-mail', 
            'phone' => 'Telefone', 'cep' => 'CEP', 'address' => 'Endereço', 'number' => 'Número', 
            'neighborhood' => 'Bairro', 'city' => 'Cidade', 'state' => 'Estado');

        // dados especiais de validação
        $specialData = array('email' => 'email');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;

            if ($app['client']->insert(
                $post['name'], 
                $post['nickname'], 
                $post['cpf_cnpj'], 
                $post['rg_ie'], 
                $post['email'], 
                $post['phone'], 
                $post['cep'], 
                $post['address'], 
                $post['number'], 
                $post['complement'], 
                $post['neighborhood'], 
                $post['city'], 
                $post['state'], 
                $post['type'], 
                date('Y-m-d H:i:s'),
                $status
            )) {
                $result = array(
                    'success' => true,
                    'message' => 'Cliente registrado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar o Cliente. Verifique se o mesmo já foi cadastrado.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Client Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateClientAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        $commonData = array('id' => 'Contato não encontrado', 'name' => 'Nome', 'cpf_cnpj' => 'Cpf ou Cnpj', 'email' => 'E-mail', 
            'phone' => 'Telefone', 'cep' => 'CEP', 'address' => 'Endereço', 'number' => 'Número', 
            'neighborhood' => 'Bairro', 'city' => 'Cidade', 'state' => 'Estado');

        // dados especiais de validação
        $specialData = array('email' => 'email');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;

            if ($app['client']->update("
                name = '{$post['name']}', 
                nickname = '{$post['nickname']}', 
                cpf_cnpj = '{$post['cpf_cnpj']}', 
                rg_ie = '{$post['rg_ie']}', 
                email = '{$post['email']}', 
                phone = '{$post['phone']}', 
                cep = '{$post['cep']}', 
                address = '{$post['address']}', 
                number = '{$post['number']}', 
                complement = '{$post['complement']}', 
                neighborhood = '{$post['neighborhood']}', 
                city = '{$post['city']}', 
                state = '{$post['state']}', 
                type = '{$post['type']}', 
                status = {$status}", (int)$post['id'])) {
                $result = array(
                    'success' => true,
                    'message' => 'Cliente alterado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível alterar o cliente.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Client Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteClientAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Cliente não encontrado.');
        }

        if ($app['client']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Cliente removido com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o cliente.'
            );
        }

        return $app->json($result);
    }
    

}