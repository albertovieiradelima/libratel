<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de StoreCategory
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class StoreCategoryController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $storecategoryController = $app['controllers_factory'];

        $storecategoryController->get('/storecategories', array($this, 'storecategoriesAction'))->bind('storecategories');
        $storecategoryController->post('/get-storecategory', array($this, 'getStoreCategoryAction'))->bind('get-storecategory');
        $storecategoryController->post('/storecategory/insert', array($this, 'insertStoreCategoryAction'))->bind('new-storecategory');
        $storecategoryController->post('/storecategory/update', array($this, 'updateStoreCategoryAction'))->bind('edit-storecategory');
        $storecategoryController->post('/storecategory/delete', array($this, 'deleteStoreCategoryAction'))->bind('remove-storecategory');

        return $storecategoryController;
    }

    /**
     * storecategory action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function storecategoriesAction(Application $app)
    {
        return $app['twig']->render('admin/storecategories.twig', array("modal" => "admin/storecategory-modal.twig", "navigate" => "Categorias"));
    }

    /**
     * getStoreCategory action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getStoreCategoryAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['store_category']->getAll();
            $storecategories = $app['store_category']->fetch_all(MYSQLI_NUM);
            foreach ($storecategories as $key => $storecategory) {
                $storecategories[$key][2] = $storecategories[$key][2] == "active" ?  "Ativo" : "Inativo";
            }

            $array = array("data" => $storecategories);
            return $app->json($array);

        } else {

            $app['store_category']->getById((int)$post['id']);
            $storecategory = $app['store_category']->fetch();

            if (is_array($storecategory)) {
                $result = array(
                    'success' => true,
                    'storecategory' => $storecategory
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar a categoria.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * StoreCategory Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertStoreCategoryAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['name']) {
            return $this->error($app, 'O Nome deve ser preenchido.');
        }

        $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;

        if ($app['store_category']->insert($post['name'], $status)) {
            $result = array(
                'success' => true,
                'message' => 'Categoria registrada com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível registrar a Categoria. Verifique se a mesma já foi cadastrado.'
            );
        }

        return $app->json($result);
    }

    /**
     * StoreCategory Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateStoreCategoryAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Categoria não encontrado.');
        }

        if (!$post['name']) {
            return $this->error($app, 'O Nome deve ser preenchido.');
        }

        $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;

        if ($app['store_category']->update("name = '{$post['name']}', status = {$status}", (int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Categoria alterado com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível alterar a categoria.'
            );
        }

        return $app->json($result);
    }

    /**
     * StoreCategory Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteStoreCategoryAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Categoria não encontrada.');
        }

        if ($app['store_category']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Categoria removida com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover a categoria.'
            );
        }

        return $app->json($result);
    }
    
}