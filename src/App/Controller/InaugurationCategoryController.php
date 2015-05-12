<?php

namespace App\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

use App\Model;

/**
 * Controller Provider de InaugurationCategory
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class InaugurationCategoryController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $inaugurationcategoryController = $app['controllers_factory'];

        $inaugurationcategoryController->get('/inaugurationcategories', array($this, 'inaugurationcategoriesAction'))->bind('inaugurationcategories');
        $inaugurationcategoryController->post('/get-inaugurationcategory', array($this, 'getInaugurationCategoryAction'))->bind('get-inaugurationcategory');
        $inaugurationcategoryController->post('/inaugurationcategory/insert', array($this, 'insertInaugurationCategoryAction'))->bind('new-inaugurationcategory');
        $inaugurationcategoryController->post('/inaugurationcategory/update', array($this, 'updateInaugurationCategoryAction'))->bind('edit-inaugurationcategory');
        $inaugurationcategoryController->post('/inaugurationcategory/delete', array($this, 'deleteInaugurationCategoryAction'))->bind('remove-inaugurationcategory');

        return $inaugurationcategoryController;
    }

    /**
     * inaugurationcategory action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function inaugurationcategoriesAction(Application $app)
    {
        return $app['twig']->render('admin/inaugurationcategories.twig', array("modal" => "admin/inaugurationcategory-modal.twig", "navigate" => "Categorias"));
    }

    /**
     * getInaugurationCategory action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getInaugurationCategoryAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['inauguration_category']->getAll();
            $inaugurationcategories = $app['inauguration_category']->fetch_all(MYSQLI_NUM);
            foreach ($inaugurationcategories as $key => $inaugurationcategory) {
                $inaugurationcategories[$key][2] = $inaugurationcategories[$key][2] == "active" ?  "Ativo" : "Inativo";
            }

            $data = array("data" => $inaugurationcategories);
            return $app->json($data);

        } else {

            $app['inauguration_category']->getById((int)$post['id']);
            $inaugurationcategory = $app['inauguration_category']->fetch();

            if (is_array($inaugurationcategory)) {
                $result = array(
                    'success' => true,
                    'inaugurationcategory' => $inaugurationcategory
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
     * InaugurationCategory Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertInaugurationCategoryAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['name']) {
            return $this->error($app, 'O Nome deve ser preenchido.');
        }

        $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;

        if ($app['inauguration_category']->insert($post['name'], $status)) {
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
     * InaugurationCategory Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateInaugurationCategoryAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Categoria não encontrado.');
        }

        if (!$post['name']) {
            return $this->error($app, 'O Nome deve ser preenchido.');
        }

        $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;

        if ($app['inauguration_category']->update("name = '{$post['name']}', status = {$status}", (int)$post['id'])) {
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
     * InaugurationCategory Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteInaugurationCategoryAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Categoria não encontrada.');
        }

        if ($app['inauguration_category']->delete((int)$post['id'])) {
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