<?php

namespace App\Controller\RestrictedArea;

use App\Enum\SituationStatusEnum;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de FileCategory
 *
 * @author Victor Hugo Martins <victorhugo.pmartins@gmail.com>
 */
class FileCategoryController extends \App\Controller\BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $filecategoryController = $app['controllers_factory'];

        $filecategoryController->get('/filecategories', array($this, 'fileCategoriesAction'))->bind('filecategories');
        $filecategoryController->post('/get-filecategory', array($this, 'getFileCategoryAction'))->bind('get-filecategory');
        $filecategoryController->post('/filecategory/insert', array($this, 'insertFileCategoryAction'))->bind('new-filecategory');
        $filecategoryController->post('/filecategory/update', array($this, 'updateFileCategoryAction'))->bind('edit-filecategory');
        $filecategoryController->post('/filecategory/delete', array($this, 'deleteFileCategoryAction'))->bind('remove-filecategory');

        return $filecategoryController;
    }

    /**
     * filecategory action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function fileCategoriesAction(Application $app)
    {
        return $app['twig']->render('admin/restricted-area/filecategories.twig', array("modal" => "admin/restricted-area/filecategory-modal.twig", "navigate" => "Categorias"));
    }

    /**
     * getfileCategory action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getFileCategoryAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $filecategories = $app['file_category']->getAll();
            foreach ($filecategories as $key => $filecategory) {
                $filecategories[$key][2] = $filecategories[$key][2] == 1 ? SituationStatusEnum::ACTIVE : SituationStatusEnum::INACTIVE;
            }

            $array = array("data" => $filecategories);
            return $app->json($array);

        } else {

            $app['file_category']->getById((int)$post['id']);
            $filecategory = $app['file_category']->fetch();

            if (is_array($filecategory)) {
                $result = array(
                    'success' => true,
                    'filecategory' => $filecategory
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
     * FileCategory Registration
     *
     * @param \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function insertFileCategoryAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['name']) {
            return $this->error($app, 'O Nome deve ser preenchido.');
        }

        $status = $post['status'] === "on" ? SituationStatusEnum::ACTIVE : SituationStatusEnum::INACTIVE;

        if ($app['file_category']->insert($post['name'], $status)) {
            $result = array(
                'success' => true,
                'message' => 'Categoria registrada com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível registrar a Categoria. Verifique se a mesma já foi cadastrada.'
            );
        }

        return $app->json($result);
    }

    /**
     * FileCategory Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateFileCategoryAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Categoria não encontrado.');
        }

        if (!$post['name']) {
            return $this->error($app, 'O Nome deve ser preenchido.');
        }

        $status = $post['status'] === "on" ? 1 : 2;

        if ($app['file_category']->update("name = '{$post['name']}', status = '{$status}'", (int)$post['id'])) {
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
     * FileCategory Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteFileCategoryAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Categoria não encontrada.');
        }

        if ($app['file_category']->delete((int)$post['id'])) {
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