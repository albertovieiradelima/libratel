<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de Aboutus
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class AboutusController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $aboutusController = $app['controllers_factory'];

        $aboutusController->get('/aboutus', array($this, 'aboutusAction'))->bind('aboutus');
        $aboutusController->post('/get-aboutus', array($this, 'getAboutusAction'))->bind('get-aboutus');
        $aboutusController->post('/get-aboutus-types', array($this, 'getAboutusTypesAction'))->bind('get-aboutus-types');
        $aboutusController->post('/aboutus/insert', array($this, 'insertAboutusAction'))->bind('new-aboutus');
        $aboutusController->post('/aboutus/update', array($this, 'updateAboutusAction'))->bind('edit-aboutus');
        $aboutusController->post('/aboutus/delete', array($this, 'deleteAboutusAction'))->bind('remove-aboutus');

        return $aboutusController;
    }

    /**
     * aboutus action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function aboutusAction(Application $app)
    {
        return $app['twig']->render('admin/aboutus.twig', array("modal" => "admin/aboutus-modal.twig", "navigate" => "Sobre a Abrasce"));
    }

    /**
     * getAboutus action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getAboutusAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['aboutus']->getAll();
            $aboutus = $app['aboutus']->fetch_all(MYSQLI_NUM);
            foreach ($aboutus as $key => $about) {
                $aboutus[$key][2] = $aboutus[$key][2] != "" ? "<img class='img-thumbnail' alt='60x50' src='/uploads/aboutus/" . $about[2] . "' data-holder-rendered='true' style='width: 60px; height: 50px;'>" : "";
            }

            $array = array("data" => $aboutus);
            return $app->json($array);

        } else {

            $app['aboutus']->getById((int)$post['id']);
            $aboutus = $app['aboutus']->fetch();

            if (is_array($aboutus)) {
                $result = array(
                    'success' => true,
                    'aboutus' => $aboutus
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar o registro.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * getAboutusType action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getAboutusTypesAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        $app['aboutus']->getTypeUnused("id, title");
        $aboutusTypes = $app['aboutus']->fetch_all();

        if (is_array($aboutusTypes)) {
            $result = array(
                'success' => true,
                'aboutus_types' => $aboutusTypes
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível listar os tipos.'
            );
        }

        return $app->json($result);

    }

    /**
     * Aboutus Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertAboutusAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $commonData = array('title' => 'Titulo', 'description' => 'Descrição');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $fileName = null;

            if ($file['image']) {

                $fileName = $this->uploadImage($file['image'], 'aboutus');
            }

            if ($app['aboutus']->insert($post['title'], $fileName, $post['description'])) {
                $result = array(
                    'success' => true,
                    'message' => 'Registrado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível criar o registro. Verifique se o mesmo já foi cadastrado.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Aboutus Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateAboutusAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $commonData = array('id' => 'Registro não encontrado', 'title' => 'Titulo', 'description' => 'Descrição');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $fileName = null;

            if ($app['aboutus']->getById((int)$post['id'])) {
                $aboutus = $app['aboutus']->fetch();

                if ($post['image'] && $file['image']) {
                    $this->deleteImage($aboutus["image"], 'aboutus');
                    $fileName = $this->uploadImage($file['image'], 'aboutus');
                } else if (!$post['image']) {
                    $this->deleteImage($aboutus["image"], 'aboutus');
                } else {
                    $fileName = $post['image'];
                }
            }

            if ($app['aboutus']->update("title = '{$post['title']}', image = '{$fileName}', description = '{$post['description']}'", (int)$post['id'])) {
                $result = array(
                    'success' => true,
                    'message' => 'Registro alterado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível criar o registro.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Aboutus Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteAboutusAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Registro não encontrado.');
        }

        if ($app['aboutus']->getById((int)$post['id'], "Evento")) {
            $aboutus = $app['aboutus']->fetch();
            $this->deleteImage($aboutus["image"], 'aboutus');
        }

        $app['aboutus']->getById((int)$post['id']);
        $aboutus = $app['aboutus']->fetch();

        if ($app['aboutus']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Registro removido com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o registro.'
            );
        }

        return $app->json($result);
    }
    
}