<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de Newsletter
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class NewsletterController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $newsletterController = $app['controllers_factory'];

        $newsletterController->get('/newsletter', array($this, 'newsletterAction'))->bind('newsletter');
        $newsletterController->post('/get-newsletter', array($this, 'getNewsletterAction'))->bind('get-newsletter');
        $newsletterController->post('/newsletter/update', array($this, 'updateNewsletterAction'))->bind('edit-newsletter');
        $newsletterController->post('/newsletter/delete', array($this, 'deleteNewsletterAction'))->bind('remove-newsletter');

        return $newsletterController;
    }

    /**
     * newsletter action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function newsletterAction(Application $app)
    {
        return $app['twig']->render('admin/newsletter.twig', array("modal" => "admin/newsletter-modal.twig", "navigate" => "Newsletter"));
    }

    /**
     * getNewsletter action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getNewsletterAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['newsletter']->getAll("id, email, date, status");
            $newsletters = $app['newsletter']->fetch_all(MYSQLI_NUM);
            foreach ($newsletters as $key => $newsletter) {
                $newsletters[$key][2] = $this->formatDate($newsletters[$key][2], "d/m/Y - h:i:s");
                $newsletters[$key][3] = $newsletters[$key][3] == "active" ?  "Ativo" : "Inativo";
            }

            $array = array("data" => $newsletters);
            return $app->json($array);

        } else {

            $app['newsletter']->getById((int)$post['id']);
            $newsletter = $app['newsletter']->fetch();
            $newsletter["date"] = $this->formatDate($newsletter["date"], "d/m/Y - h:i:s");

            if (is_array($newsletter)) {
                $result = array(
                    'success' => true,
                    'newsletter' => $newsletter
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar o newsletter.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * Newsletter Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateNewsletterAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Newsletter não encontrado.');
        }

        $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;

        if ($app['newsletter']->update("status = {$status}", (int)$post['id'])) {
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
     * Newsletter Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteNewsletterAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Newsletter não encontrado.');
        }

        if ($app['newsletter']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Newsletter removido com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o newsletter.'
            );
        }

        return $app->json($result);
    }
    

}