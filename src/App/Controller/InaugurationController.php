<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de Inauguration
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 * @author Victor Hugo Martins <victorhugo.pmartins@gmail.com>
 */
class InaugurationController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $inaugurationController = $app['controllers_factory'];

        $inaugurationController->get('/inaugurations', array($this, 'inaugurationsAction'))->bind('inauguration');
        $inaugurationController->post('/get-inaugurations', array($this, 'getInaugurationsAction'))->bind('get-inaugurations');
        $inaugurationController->post('/inauguration/insert', array($this, 'insertInaugurationAction'))->bind('new-inauguration');
        $inaugurationController->post('/inauguration/update', array($this, 'updateInaugurationAction'))->bind('edit-inauguration');
        $inaugurationController->post('/inauguration/delete', array($this, 'deleteInaugurationAction'))->bind('remove-inauguration');

        return $inaugurationController;
    }

    /**
     * inauguration action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function inaugurationsAction(Application $app)
    {
        return $app['twig']->render('admin/inaugurations.twig', array("modal" => "admin/inauguration-modal.twig", "navigate" => "Shoppings"));
    }

    /**
     * getInauguration action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getInaugurationsAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['inauguration']->getAll();
            $inaugurations = $app['inauguration']->fetch_all(MYSQLI_NUM);
            foreach ($inaugurations as $key => $inauguration) {
//                $inaugurations[$key][2] = $inaugurations[$key][2] != "" ? "<img class='img-thumbnail' alt='60x50' src='/uploads/inaugurations/" . $inauguration[2] . "' data-holder-rendered='true' style='width: 60px; height: 50px;'>" : "";
                $inaugurations[$key][4] = $this->formatDate($inaugurations[$key][4], "d/m/Y");
                $inaugurations[$key][5] = $inaugurations[$key][5] == "open" ? "Inaugurado" : "A inaugurar";
            }

            $data = array("data" => $inaugurations);
            return $app->json($data);

        } else {

            $app['inauguration']->getById((int)$post['id']);
            $inauguration = $app['inauguration']->fetch();
            $inauguration['inauguration_date'] = $this->formatDate($inauguration['inauguration_date'], "d/m/Y");

            if (is_array($inauguration)) {
                $result = array(
                    'success' => true,
                    'inauguration' => $inauguration
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar o shopping.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * Inauguration Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertInaugurationAction(Application $app, Request $request)
    {
        $post = $request->request->all();
//        $file = $request->files->all();

        $commonData = array('fk_inauguration_category' => 'Categoria', 'shopping' => 'Shopping', 'city' => 'Cidade', 'state' => 'Estado', 
            'abl' => 'ABL', 'inauguration_date' => 'Data de Inauguração');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
//            $fileName = null;
//            if ($file['image']) {
//                $fileName = $this->uploadImage($file['image'], 'inaugurations');
//            }

            if ($app['inauguration']->insert($post['fk_inauguration_category'], $post['shopping'], $this->formatDateMysql($post['inauguration_date']), $post['city'], $post['state'], $post['abl'], $post['link'], $status)) {
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
     * Inauguration Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateInaugurationAction(Application $app, Request $request)
    {

        $post = $request->request->all();
//        $file = $request->files->all();

        $commonData = array('id' => 'Shopping não encontrado', 'fk_inauguration_category' => 'Categoria', 'shopping' => 'Shopping', 
            'inauguration_date' => 'Data de Inauguração', 'city' => 'Cidade', 'state' => 'Estado', 'abl' => 'ABL');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $fileName = null;

            if ($app['inauguration']->update("
                fk_inauguration_category = '{$post['fk_inauguration_category']}', 
                shopping = '{$post['shopping']}',
                inauguration_date = '{$this->formatDateMysql($post['inauguration_date'])}', 
                city = '{$post['city']}', 
                state = '{$post['state']}', 
                abl = '{$post['abl']}', 
                link = '{$post['link']}', 
                status = {$status}
            ", (int)$post['id'])) {
                $result = array(
                    'success' => true,
                    'message' => 'Shopping alterado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar o shopping.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Inauguration Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteInaugurationAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Shopping não encontrado.');
        }

//        if ($app['inauguration']->getById((int)$post['id'])) {
//            $inauguration = $app['inauguration']->fetch();
//            $this->deleteImage($inauguration["image"], 'inaugurations');
//        }

        if ($app['inauguration']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Shopping removido com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o shopping.'
            );
        }

        return $app->json($result);
    }

}