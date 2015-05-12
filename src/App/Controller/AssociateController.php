<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de Associate
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class AssociateController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $associateController = $app['controllers_factory'];

        $associateController->get('/associates', array($this, 'associatesAction'))->bind('associates');
        $associateController->post('/get-associates', array($this, 'getAssociatesAction'))->bind('get-associates');
        $associateController->post('/associates/update', array($this, 'updateAssociateAction'))->bind('edit-associates');
        $associateController->post('/associates/delete', array($this, 'deleteAssociateAction'))->bind('remove-associates');

        return $associateController;
    }

    /**
     * associates action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function associatesAction(Application $app)
    {
        return $app['twig']->render('admin/associates.twig', array("modal" => "admin/associate-modal.twig", "navigate" => "Contatos"));
    }

    /**
     * getAssociates action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getAssociatesAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['associate']->getAll("id, name, email, phone, date, status");
            $associates = $app['associate']->fetch_all(MYSQLI_NUM);
            foreach ($associates as $key => $associate) {
                $associates[$key][4] = $this->formatDate($associates[$key][4], "d/m/Y - H:i:s");
                $associates[$key][5] = $associates[$key][5] == "read" ? "Lido" : "Não lido";
            }

            $array = array("data" => $associates);
            return $app->json($array);

        } else {

            $app['associate']->getById((int)$post['id']);
            $associate = $app['associate']->fetch();
            $associate["date"] = $this->formatDate($associate["date"], "d/m/Y - H:i:s");

            if (is_array($associate)) {
                $result = array(
                    'success' => true,
                    'associate' => $associate
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar o associateo.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * Associate Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateAssociateAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Contato não encontrado.');
        }

        $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;

        if ($app['associate']->update("status = {$status}", (int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Contato alterado com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível alterar o contato.'
            );
        }

        return $app->json($result);
    }

    /**
     * Associate Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteAssociateAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Contato não encontrado.');
        }

        if ($app['associate']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Contato removido com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o contato.'
            );
        }

        return $app->json($result);
    }
    

}