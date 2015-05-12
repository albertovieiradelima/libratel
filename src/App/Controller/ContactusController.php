<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de Contactus
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class ContactusController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $contactusController = $app['controllers_factory'];

        $contactusController->get('/contactus', array($this, 'contactusAction'))->bind('contactus');
        $contactusController->post('/get-contactus', array($this, 'getContactusAction'))->bind('get-contactus');
        $contactusController->post('/contactus/insert', array($this, 'insertContactusAction'))->bind('new-contactus');
        $contactusController->post('/contactus/update', array($this, 'updateContactusAction'))->bind('edit-contactus');
        $contactusController->post('/contactus/delete', array($this, 'deleteContactusAction'))->bind('remove-contactus');

        return $contactusController;
    }

    /**
     * contactus action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function contactusAction(Application $app)
    {
        return $app['twig']->render('admin/contactus.twig', array("modal" => "admin/contactus-modal.twig", "navigate" => "Fale conosco"));
    }

    /**
     * getContactus action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getContactusAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['contactus']->getAll("id, name, email, area, subject, date, status");
            $contactus = $app['contactus']->fetch_all(MYSQLI_NUM);
            foreach ($contactus as $key => $contact) {
                $contactus[$key][5] = $this->formatDate($contactus[$key][5], "d/m/Y - H:i:s");
                $contactus[$key][6] = $contactus[$key][6] == "read" ? "Lido" : "Não lido";
            }

            $array = array("data" => $contactus);
            return $app->json($array);

        } else {

            $app['contactus']->getById((int)$post['id']);
            $contactus = $app['contactus']->fetch();
            $contactus["date"] = $this->formatDate($contactus["date"], "d/m/Y - H:i:s");

            if (is_array($contactus)) {
                $result = array(
                    'success' => true,
                    'contactus' => $contactus
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
     * Contactus Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateContactusAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Contato não encontrado.');
        }

        $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;

        if ($app['contactus']->update("status = {$status}", (int)$post['id'])) {
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
     * Contactus Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteContactusAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Contato não encontrado.');
        }

        if ($app['contactus']->delete((int)$post['id'])) {
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