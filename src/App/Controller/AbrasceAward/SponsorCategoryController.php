<?php
/**
 * Created by PhpStorm.
 * User: albertovieiradelima
 * Date: 27/01/15
 * Time: 09:50
 */
namespace App\Controller\AbrasceAward;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use App\Model;

/**
 * Controller Provider de SponsorCategory
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class SponsorCategoryController extends \App\Controller\BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $sponsorcategoryController = $app['controllers_factory'];

        $sponsorcategoryController->get('/sponsorcategories', array($this, 'sponsorcategoriesAction'))->bind('sponsorcategories');
        $sponsorcategoryController->post('/get-sponsorcategory', array($this, 'getSponsorCategoryAction'))->bind('get-sponsorcategory');
        $sponsorcategoryController->post('/sponsorcategory/insert', array($this, 'insertSponsorCategoryAction'))->bind('new-sponsorcategory');
        $sponsorcategoryController->post('/sponsorcategory/update', array($this, 'updateSponsorCategoryAction'))->bind('edit-sponsorcategory');
        $sponsorcategoryController->post('/sponsorcategory/delete', array($this, 'deleteSponsorCategoryAction'))->bind('remove-sponsorcategory');

        return $sponsorcategoryController;
    }

    /**
     * sponsorcategory action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function sponsorcategoriesAction(Application $app)
    {
        return $app['twig']->render('admin/abrasce-award/sponsorcategories.twig', array("modal" => "admin/abrasce-award/sponsorcategory-modal.twig", "navigate" => "Categorias"));
    }

    /**
     * getSponsorCategory action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getSponsorCategoryAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['aa_sponsor_category']->getAll();
            $sponsorcategories = $app['aa_sponsor_category']->fetch_all(MYSQLI_NUM);
            foreach ($sponsorcategories as $key => $sponsorcategory) {
                $sponsorcategories[$key][2] = $sponsorcategories[$key][2] == "active" ?  "Ativo" : "Inativo";
            }

            $data = array("data" => $sponsorcategories);
            return $app->json($data);

        } else {

            $app['aa_sponsor_category']->getById((int)$post['id']);
            $sponsorcategory = $app['aa_sponsor_category']->fetch();

            if (is_array($sponsorcategory)) {
                $result = array(
                    'success' => true,
                    'sponsorcategory' => $sponsorcategory
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
     * SponsorCategory Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertSponsorCategoryAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['name']) {
            return $this->error($app, 'O Nome deve ser preenchido.');
        }

        $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;

        if ($app['aa_sponsor_category']->insert($post['name'], $status)) {
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
     * SponsorCategory Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateSponsorCategoryAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Categoria não encontrado.');
        }

        if (!$post['name']) {
            return $this->error($app, 'O Nome deve ser preenchido.');
        }

        $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;

        if ($app['aa_sponsor_category']->update("name = '{$post['name']}', status = {$status}", (int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Categoria alterada com sucesso.'
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
     * SponsorCategory Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteSponsorCategoryAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id'] || preg_replace("/[^0-9]/", "", $post['id']) == "") {
            return $this->error($app, 'Categoria não encontrada.');
        }

        if ($app['aa_sponsor_category']->delete((int)$post['id'])) {
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