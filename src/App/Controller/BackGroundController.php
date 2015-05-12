<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de BackGround
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class BackGroundController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $backgroundController = $app['controllers_factory'];

        $backgroundController->get('/backgrounds', array($this, 'backgroundsAction'))->bind('backgrounds');
        $backgroundController->post('/get-backgrounds', array($this, 'getBackGroundsAction'))->bind('get-backgrounds');
        $backgroundController->post('/background/insert', array($this, 'insertBackGroundAction'))->bind('new-background');
        $backgroundController->post('/background/update', array($this, 'updateBackGroundAction'))->bind('edit-background');
        $backgroundController->post('/background/delete', array($this, 'deleteBackGroundAction'))->bind('remove-background');

        return $backgroundController;
    }

    /**
     * background action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function backgroundsAction(Application $app)
    {
        return $app['twig']->render('admin/backgrounds.twig', array("modal" => "admin/background-modal.twig", "navigate" => "Planos de Fundo"));
    }

    /**
     * getBackGround action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getBackGroundsAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['banner']->getAll('id, date, image, status', \App\Enum\BannerTypeEnum::BACKGROUND);
            $backgrounds = $app['banner']->fetch_all(MYSQLI_NUM);
            foreach ($backgrounds as $key => $background) {
                $backgrounds[$key][1] = $this->formatDate($backgrounds[$key][1], "d/m/Y");
                $backgrounds[$key][2] = $backgrounds[$key][2] != "" ? "<img class='img-thumbnail' alt='60x50' src='/uploads/banners/" . $background[2] . "' data-holder-rendered='true' style='width: 60px; height: 50px;'>" : "";
                $backgrounds[$key][3] = $backgrounds[$key][3] == "active" ?  "Ativo" : "Inativo";
            }

            $data = array("data" => $backgrounds);
            return $app->json($data);

        } else {

            $app['banner']->getById((int)$post['id']);
            $background = $app['banner']->fetch();

            if (is_array($background)) {
                $result = array(
                    'success' => true,
                    'background' => $background
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar o plano de fundo.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * BackGround Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertBackGroundAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $error = '';

        if ($file['image'] == false) {
            $error .= '- Imagem<br>';
        }

        if(empty($error) == false){
            $error = 'Os campos:<br><br>'.$error.'<br>Devem ser preenchidos.';
            return $this->error($app, $error);
        }

        $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
        $fileName = null;

        if ($file['image']) {
            
            $fileInfo = getimagesize($file['image']->getFileInfo()->getPathName());

            if($fileInfo[0] <= 400 && $fileInfo[1] <= 400){
                $fileName = $this->uploadImage($file['image'], 'banners');
            } else {
                return $this->error($app, 'Plano de Fundo deve ter largura de 400px e altura de 400px no máximo.');
            }
        }

        if ($app['banner']->insert(
            $fileName,
            null,
            null,
            \App\Enum\BannerTypeEnum::BACKGROUND,
            date('Y-m-d H:i:s'),
            $status
        )) {
            $result = array(
                'success' => true,
                'message' => 'Plano de Fundo registrado com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível criar o plano de fundo. Verifique se o mesmo já foi cadastrado.'
            );
        }

        return $app->json($result);
    }

    /**
     * BackGround Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateBackGroundAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $error = '';

        if (!$post['id']) {
            $error .= '- Plano de Fundo não encontrado<br>';
        }

        if (!$post['image']) {
            $error .= '- Imagem<br>';
        } 

        if(empty($error) == false){
            $error = 'Os campos:<br><br>'.$error.'<br>Devem ser preenchidos.';
            return $this->error($app, $error);
        }

        $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
        $fileName = null;

        if ($app['banner']->getById((int)$post['id'])) {
            $background = $app['banner']->fetch();

            if ($post['image'] && $file['image']) {
                $fileInfo = getimagesize($file['image']->getFileInfo()->getPathName());
                if($fileInfo[0] <= 400 && $fileInfo[1] <= 400){
                    $this->deleteImage($topimage["image"], 'banners');
                    $fileName = $this->uploadImage($file['image'], 'banners');
                } else {
                    return $this->error($app, 'Plano de Fundo deve ter largura de 400px e altura de 400px no máximo.');
                }
            } else if (!$post['image']) {
                $this->deleteImage($background["image"], 'banners');
            } else {
                $fileName = $post['image'];
            }

        }

        if ($app['banner']->update("image = '{$fileName}', status = {$status}", (int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Plano de Fundo alterado com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível criar o plano de fundo.'
            );
        }

        return $app->json($result);
    }

    /**
     * BackGround Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteBackGroundAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Plano de Fundo não encontrado.');
        }

        if ($app['banner']->getById((int)$post['id'])) {
            $background = $app['banner']->fetch();
            $this->deleteImage($background["image"], 'banner');
        }

        $app['banner']->getById((int)$post['id']);
        $background = $app['banner']->fetch();

        if ($app['banner']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Plano de Fundo removido com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o plano de fundo.'
            );
        }

        return $app->json($result);
    }
    
}