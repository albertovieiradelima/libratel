<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de Magazine
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class MagazineController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $magazineController = $app['controllers_factory'];

        $magazineController->get('/magazines', array($this, 'magazinesAction'))->bind('magazines');
        $magazineController->post('/get-magazines', array($this, 'getMagazinesAction'))->bind('get-magazines');
        $magazineController->post('/magazine/insert', array($this, 'insertMagazineAction'))->bind('new-magazine');
        $magazineController->post('/magazine/update', array($this, 'updateMagazineAction'))->bind('edit-magazine');
        $magazineController->post('/magazine/delete', array($this, 'deleteMagazineAction'))->bind('remove-magazine');

        return $magazineController;
    }

    /**
     * magazines action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function magazinesAction(Application $app)
    {
        return $app['twig']->render('admin/magazines.twig', array("modal" => "admin/magazine-modal.twig", "navigate" => "Revistas"));
    }

    /**
     * getMagazines action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getMagazinesAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['magazine']->getAll('id, publication, title, image, status');
            $magazines = $app['magazine']->fetch_all(MYSQLI_NUM);
            foreach ($magazines as $key => $magazine) {
                $magazines[$key][3] = $magazine[3] != "" ? "<img class='img-thumbnail' alt='60x50' src='/uploads/magazines/" . $magazine[3] . "' data-holder-rendered='true' style='width: 60px; height: 50px;'>" : "";
                $magazines[$key][4] = $magazine[4] == "active" ?  "Ativo" : "Inativo";
            }

            $array = array("data" => $magazines);
            return $app->json($array);

        } else {

            $app['magazine']->getById((int)$post['id']);
            $magazine = $app['magazine']->fetch();

            if (is_array($magazine)) {
                $result = array(
                    'success' => true,
                    'magazine' => $magazine
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar a revista.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * Magazine Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertMagazineAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $commonData = array('publication' => 'Publicação', 'title' => 'Titulo', 'sinopse' => 'Sinopse', 
            'description' => 'Descrição');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $fileName = null;

            if ($file['image']) {

                $fileName = $this->uploadImage($file['image'], 'magazines');
            }

            if ($app['magazine']->insert($post['publication'], $post['title'], $fileName, $post['sinopse'], $post['description'], date('Y-m-d H:i:s'), $status)) {
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
     * Magazine Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateMagazineAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $commonData = array('id' => 'Revista não encontrada', 'publication' => 'Publicação', 'title' => 'Titulo', 'sinopse' => 'Sinopse', 
            'description' => 'Descrição');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $date = date('Y-m-d H:i:s');
            $fileName = null;

            if ($post['image'] && $file['image']) {

                if ($app['magazine']->getById((int)$post['id'])) {
                    
                    $magazine = $app['magazine']->fetch();
                    $this->deleteImage($magazine["image"], 'magazines');
                }

                $fileName = $this->uploadImage($file['image'], 'magazines');

            } else if (!$post['image']){

                if ($app['magazine']->getById((int)$post['id'])) {
                    $magazine = $app['magazine']->fetch();
                    $this->deleteImage($magazine["image"], 'magazines');
                }

            } else {
                $fileName = $post['image'];
            }

            if ($app['magazine']->update("publication = '{$post['publication']}', title = '{$post['title']}', image = '{$fileName}', sinopse = '{$post['sinopse']}', description = '{$post['description']}', date = '{$date}', status = {$status}", (int)$post['id'])) {
                $result = array(
                    'success' => true,
                    'message' => 'Revista alterada com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar a revista.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Magazine Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteMagazineAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Revista não encontrada.');
        }

        if ($app['magazine']->getById((int)$post['id'])) {
            $magazine = $app['magazine']->fetch();
            $this->deleteImage($magazine["image"], 'magazines');
        }

        if ($app['magazine']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Revista removida com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover a revista.'
            );
        }

        return $app->json($result);
    }
    
}