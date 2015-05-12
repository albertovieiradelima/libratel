<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de SpacePartner
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class SpacePartnerController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $spacepartnerController = $app['controllers_factory'];

        $spacepartnerController->get('/spacepartners', array($this, 'spacepartnersAction'))->bind('spacepartners');
        $spacepartnerController->post('/get-spacepartners', array($this, 'getSpacePartnersAction'))->bind('get-spacepartners');
        $spacepartnerController->post('/spacepartners/insert', array($this, 'insertSpacePartnerAction'))->bind('new-spacepartner');
        $spacepartnerController->post('/spacepartners/update', array($this, 'updateSpacePartnerAction'))->bind('edit-spacepartner');
        $spacepartnerController->post('/spacepartners/delete', array($this, 'deleteSpacePartnerAction'))->bind('remove-spacepartner');

        return $spacepartnerController;
    }

    /**
     * spacepartners action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function spacepartnersAction(Application $app)
    {
        return $app['twig']->render('admin/spacepartners.twig', array("modal" => "admin/spacepartner-modal.twig", "navigate" => "Espaço do Associado"));
    }

    /**
     * getSpacePartner action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getSpacePartnersAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['space_partner']->getAll("id, title, image, date, status", \App\Enum\FeedTypeEnum::ASSOCIADO);
            $spacepartners = $app['space_partner']->fetch_all(MYSQLI_NUM);
            foreach ($spacepartners as $key => $spacepartner) {
                $spacepartners[$key][2] = $spacepartner[2] != "" ? "<img class='img-thumbnail' alt='60x50' src='/uploads/feeds/" . $spacepartner[2] . "' data-holder-rendered='true' style='width: 60px; height: 50px;'>" : "";
                $spacepartners[$key][3] = $this->formatDate($spacepartner[3], "d/m/Y");
                $spacepartners[$key][4] = $spacepartner[4] == "active" ?  "Ativo" : "Inativo";
            }

            $array = array("data" => $spacepartners);
            return $app->json($array);

        } else {

            $app['space_partner']->getById((int)$post['id']);
            $spacepartner = $app['space_partner']->fetch();
            $spacepartner["date"] = $this->formatDate($spacepartner["date"], "d/m/Y");

            if (is_array($spacepartner)) {
                $result = array(
                    'success' => true,
                    'spacepartner' => $spacepartner
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
     * SpacePartner Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertSpacePartnerAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $commonData = array('title' => 'Titulo', 'description' => 'Descrição', 
            'date' => 'Data');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === 'on' ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
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

            if ($app['space_partner']->insert($post['title'], $post['description'], $fileName, $thumbName, \App\Enum\FeedTypeEnum::ASSOCIADO, $this->formatDateMysql($post['date']), $status)) {
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
     * SpacePartner Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateSpacePartnerAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $commonData = array('id' => 'Notícia não encontrada', 'title' => 'Titulo', 'description' => 'Descrição', 
            'date' => 'Data');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === 'on' ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $fileName = null;
            $thumbName = null;

            if ($app['space_partner']->getById((int)$post['id'])) {
                $spacepartner = $app['space_partner']->fetch();

                if ($post['image'] && $file['image']) {
                    $this->deleteImage($spacepartner["image"], 'feeds');
                    $fileName = $this->uploadImage($file['image'], 'feeds');
                } else if (!$post['image']) {
                    $this->deleteImage($spacepartner["image"], 'feeds');
                } else {
                    $fileName = $post['image'];
                }

                if ($post['thumb'] && $file['thumb']) {
                    $this->deleteImage($spacepartner['thumb'], 'feeds');
                    $thumbName = $this->uploadThumb($file['thumb'], 'feeds');
                } else if (!$post['thumb']) {
                    $this->deleteImage($spacepartner['thumb'], 'feeds');
                } else {
                    $thumbName = $post['thumb'];
                }
            }

            if ($app['space_partner']->update("title = '{$post['title']}', description = '{$post['description']}', image = '{$fileName}', thumb = '{$thumbName}', date = '{$this->formatDateMysql($post['date'])}', status = {$status}", (int)$post['id'])) {
                $result = array(
                    'success' => true,
                    'message' => 'Notícia alterada com sucesso.'
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
     * SpacePartner Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteSpacePartnerAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Notícia não encontrada.');
        }

        if ($app['space_partner']->getById((int)$post['id'])) {
            $spacepartner = $app['space_partner']->fetch();
            $this->deleteImage($spacepartner["image"], 'feeds');
            $this->deleteImage($spacepartner["thumb"], 'feeds');
        }

        if ($app['space_partner']->delete((int)$post['id'])) {
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