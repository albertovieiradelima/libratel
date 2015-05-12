<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de Store
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class StoreController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $storeController = $app['controllers_factory'];

        $storeController->get('/store', array($this, 'storeAction'))->bind('store');
        $storeController->post('/get-store', array($this, 'getStoreAction'))->bind('get-store');
        $storeController->post('/store/insert', array($this, 'insertStoreAction'))->bind('new-store');
        $storeController->post('/store/update', array($this, 'updateStoreAction'))->bind('edit-store');
        $storeController->post('/store/delete', array($this, 'deleteStoreAction'))->bind('remove-store');

        return $storeController;
    }

    /**
     * store action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function storeAction(Application $app)
    {
        return $app['twig']->render('admin/store.twig', array("modal" => "admin/store-modal.twig", "navigate" => "Títulos"));
    }

    /**
     * getStore action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getStoreAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['store']->getAll();
            $store = $app['store']->fetch_all(MYSQLI_NUM);
            foreach ($store as $key => $item) {
                $store[$key][2] = $store[$key][2] != "" ? "<img class='img-thumbnail' alt='60x50' src='/uploads/store/" . $item[2] . "' data-holder-rendered='true' style='width: 60px; height: 50px;'>" : "";
                $store[$key][5] = $this->moneyFormatReal($item[5]);
                $store[$key][6] = $store[$key][6] == "active" ?  "Ativo" : "Inativo";
            }

            $array = array("data" => $store);
            return $app->json($array);

        } else {

            $app['store']->getById((int)$post['id']);
            $store = $app['store']->fetch();

            if (is_array($store)) {
                $store["price"] = $this->moneyFormatReal($store["price"]);
                $result = array(
                    'success' => true,
                    'store' => $store
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar o título.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * Store Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertStoreAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $commonData = array('fk_store_category' => 'Categoria', 'title' => 'Titulo', 'sinopse' => 'Sinopse', 
            'year' => 'Ano', 'price' => 'Preço');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $price =  $this->moneyFormatDecimal($post['price']);
            $fileName = null;

            if ($file['image']) {

                $fileName = $this->uploadImage($file['image'], 'store');
            }

            if ($app['store']->insert($post['fk_store_category'], $post['title'], $fileName, $post['sinopse'], $post['year'], $price, $status)) {
                $result = array(
                    'success' => true,
                    'message' => 'Título registrado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar o Título. Verifique se o mesmo já foi cadastrado.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Store Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateStoreAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $commonData = array('id' => 'Título não encontrado', 'fk_store_category' => 'Categoria', 'title' => 'Titulo', 'sinopse' => 'Sinopse', 
            'year' => 'Ano', 'price' => 'Preço');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $price =  $this->moneyFormatDecimal($post['price']);
            $fileName = null;

            if ($app['store']->getById((int)$post['id'])) {
                $store = $app['store']->fetch();

                if ($post['image'] && $file['image']) {
                    $this->deleteImage($store["image"], 'store');
                    $fileName = $this->uploadImage($file['image'], 'store');
                } else if (!$post['image']) {
                    $this->deleteImage($store["image"], 'store');
                } else {
                    $fileName = $post['image'];
                }
            }

            if ($app['store']->update("fk_store_category = '{$post['fk_store_category']}', title = '{$post['title']}', image = '{$fileName}', sinopse = '{$post['sinopse']}', year = '{$post['year']}', price = '{$price}', status = {$status}", (int)$post['id'])) {
                $result = array(
                    'success' => true,
                    'message' => 'Titulo alterado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar o título.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Store Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteStoreAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Titulo não encontrado.');
        }

        if ($app['store']->getById((int)$post['id'])) {
            $store = $app['store']->fetch();
            $this->deleteImage($store["image"], 'store');
        }

        if ($app['store']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Título removido com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o título.'
            );
        }

        return $app->json($result);
    }

}