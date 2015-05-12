<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de Feed
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class FeedController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $feedController = $app['controllers_factory'];

        $feedController->get('/feeds', array($this, 'feedsAction'))->bind('feeds');
        $feedController->post('/get-feeds', array($this, 'getFeedsAction'))->bind('get-feeds');
        $feedController->post('/feeds/insert', array($this, 'insertFeedAction'))->bind('new-feed');
        $feedController->post('/feeds/update', array($this, 'updateFeedAction'))->bind('edit-feed');
        $feedController->post('/feeds/delete', array($this, 'deleteFeedAction'))->bind('remove-feed');

        return $feedController;
    }

    /**
     * feeds action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function feedsAction(Application $app)
    {
        return $app['twig']->render('admin/feeds.twig', array("modal" => "admin/feed-modal.twig", "navigate" => "Notícias"));
    }

    /**
     * getFeed action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getFeedsAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['feed']->getAll("id, title, image, date, status", \App\Enum\FeedTypeEnum::NOTICIA);
            $feeds = $app['feed']->fetch_all(MYSQLI_NUM);
            foreach ($feeds as $key => $feed) {
                $feeds[$key][2] = $feed[2] != "" ? "<img class='img-thumbnail' alt='60x50' src='/uploads/feeds/" . $feed[2] . "' data-holder-rendered='true' style='width: 60px; height: 50px;'>" : "";
                $feeds[$key][3] = $this->formatDate($feed[3], "d/m/Y");
                $feeds[$key][4] = $feed[4] == "active" ?  "Ativo" : "Inativo";
            }

            $array = array("data" => $feeds);
            return $app->json($array);

        } else {

            $app['feed']->getById((int)$post['id']);
            $feed = $app['feed']->fetch();
            $feed["date"] = $this->formatDate($feed["date"], "d/m/Y");

            if (is_array($feed)) {
                $result = array(
                    'success' => true,
                    'feed' => $feed
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar a notícia.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * Feed Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertFeedAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $commonData = array('title' => 'Titulo', 'description' => 'Descrição', 'date' => 'Data');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $fileName = null;
            $thumbName = null;

            if ($file['image']) {
                $fileName = $this->uploadImage($file['image'], 'feeds');
            }

            if ($file['thumb']) {
                $thumbName = $this->uploadThumb($file['thumb'], 'feeds');

                if($thumbName == false){
                    return $this->error($app, 'A Thumb deve ter largura e altura de 80px .');
                }
            }

            if ($app['feed']->insert($post['title'], $post['description'], $fileName, $thumbName, \App\Enum\FeedTypeEnum::NOTICIA, $this->formatDateMysql($post['date']), $status)) {
                $result = array(
                    'success' => true,
                    'message' => 'Notícia registrado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar a Notícia. Verifique se a notícia já foi cadastrado.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Feed Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateFeedAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $commonData = array('id' => 'Notícia não encontrada', 'title' => 'Titulo', 'description' => 'Descrição', 'date' => 'Data');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === 'on' ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $fileName = null;
            $thumbName = null;

            if ($app['feed']->getById((int)$post['id'])) {
                $feed = $app['feed']->fetch();

                if ($post['image'] && $file['image']) {
                    $this->deleteImage($feed["image"], 'feeds');
                    $fileName = $this->uploadImage($file['image'], 'feeds');
                } else if (!$post['image']) {
                    $this->deleteImage($feed["image"], 'feeds');
                } else {
                    $fileName = $post['image'];
                }

                if ($post['thumb'] && $file['thumb']) {
                    $this->deleteImage($feed['thumb'], 'feeds');
                    $thumbName = $this->uploadThumb($file['thumb'], 'feeds');
                } else if (!$post['thumb']) {
                    $this->deleteImage($feed['thumb'], 'feeds');
                } else {
                    $thumbName = $post['thumb'];
                }
            }

            if ($app['feed']->update("title = '{$post['title']}', description = '{$post['description']}', image = '{$fileName}', thumb = '{$thumbName}', date = '{$this->formatDateMysql($post['date'])}', status = {$status}", (int)$post['id'])) {
                $result = array(
                    'success' => true,
                    'message' => 'Notícia alterado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar a notícia.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Feed Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteFeedAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Notícia não encontrada.');
        }

        if ($app['feed']->getById((int)$post['id'])) {
            $feed = $app['feed']->fetch();
            $this->deleteImage($feed["image"], 'feeds');
            $this->deleteImage($feed["thumb"], 'feeds');
        }

        if ($app['feed']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Notícia removida com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover a notícia.'
            );
        }

        return $app->json($result);
    }

}