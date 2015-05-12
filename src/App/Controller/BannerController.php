<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de Banner
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class BannerController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $bannerController = $app['controllers_factory'];

        $bannerController->get('/banners', array($this, 'bannersAction'))->bind('banners');
        $bannerController->post('/get-banners', array($this, 'getBannersAction'))->bind('get-banners');
        $bannerController->post('/banner/insert', array($this, 'insertBannerAction'))->bind('new-banner');
        $bannerController->post('/banner/update', array($this, 'updateBannerAction'))->bind('edit-banner');
        $bannerController->post('/banner/delete', array($this, 'deleteBannerAction'))->bind('remove-banner');

        return $bannerController;
    }

    /**
     * banner action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function bannersAction(Application $app)
    {
        return $app['twig']->render('admin/banners.twig', array("modal" => "admin/banner-modal.twig", "navigate" => "Banners"));
    }

    /**
     * getBanner action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getBannersAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['banner']->getAll('id, date, image, link, status', \App\Enum\BannerTypeEnum::BANNER);
            $banners = $app['banner']->fetch_all(MYSQLI_NUM);
            foreach ($banners as $key => $banner) {
                $banners[$key][1] = $this->formatDate($banners[$key][1], "d/m/Y");
                $banners[$key][2] = $banners[$key][2] != "" ? "<img class='img-thumbnail' alt='60x50' src='/uploads/banners/" . $banner[2] . "' data-holder-rendered='true' style='width: 60px; height: 50px;'>" : "";
                $banners[$key][4] = $banners[$key][4] == "active" ?  "Ativo" : "Inativo";
            }

            $data = array("data" => $banners);
            return $app->json($data);

        } else {

            $app['banner']->getById((int)$post['id']);
            $banner = $app['banner']->fetch();

            if (is_array($banner)) {
                $result = array(
                    'success' => true,
                    'banner' => $banner
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar o banner.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * Banner Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertBannerAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $error = '';

        if ($file['image'] == false) {
            $error .= '- Imagem<br>';
        } 

        if (!$post['link']) {
            $error .= '- Link<br>';
        }

        if(empty($error) == false){
            $error = 'Os campos:<br><br>'.$error.'<br>Devem ser preenchidos.';
            return $this->error($app, $error);
        }

        $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
        $fileName = null;

        if ($file['image']) {
            
            $fileInfo = getimagesize($file['image']->getFileInfo()->getPathName());

            if($fileInfo[0] >= 565 && $fileInfo[1] >= 193){
                $fileName = $this->uploadImage($file['image'], 'banners');
            } else {
                return $this->error($app, 'Banner deve ter largura de 565px e altura de 193px no mínimo.');
            }
        }

        if ($app['banner']->insert(
            $fileName,
            $post['link'],
            null,
            \App\Enum\BannerTypeEnum::BANNER,
            date('Y-m-d H:i:s'),
            $status,
            $post['title'],
            $post['description']
        )) {
            $result = array(
                'success' => true,
                'message' => 'Banner registrado com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível criar o banner. Verifique se o mesmo já foi cadastrado.'
            );
        }

        return $app->json($result);
    }

    /**
     * Banner Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateBannerAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $error = '';

        if (!$post['id']) {
            $error .= '- Banner não encontrado<br>';
        }

        if (!$post['image']) {
            $error .= '- Imagem<br>';
        } 

        if (!$post['link']) {
            $error .= '- Link<br>';
        }

        if(empty($error) == false){
            $error = 'Os campos:<br><br>'.$error.'<br>Devem ser preenchidos.';
            return $this->error($app, $error);
        }

        $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
        $fileName = null;

        if ($app['banner']->getById((int)$post['id'])) {
            $banner = $app['banner']->fetch();

            if ($post['image'] && $file['image']) {
                $fileInfo = getimagesize($file['image']->getFileInfo()->getPathName());
                if($fileInfo[0] >= 565 && $fileInfo[1] >= 193){
                    $this->deleteImage($topimage["image"], 'banners');
                    $fileName = $this->uploadImage($file['image'], 'banners');
                } else {
                    return $this->error($app, 'Imagem deve ter largura de 565px e altura de 193px no mínimo.');
                }
            } else if (!$post['image']) {
                $this->deleteImage($banner["image"], 'banners');
            } else {
                $fileName = $post['image'];
            }

        }

        if ($app['banner']->update("image = '{$fileName}', link = '{$post['link']}', status = {$status}, title = '{$post['title']}', description = '{$post['description']}'", (int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Banner alterado com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível criar o banner.'
            );
        }

        return $app->json($result);
    }

    /**
     * Banner Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteBannerAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Banner não encontrado.');
        }

        if ($app['banner']->getById((int)$post['id'])) {
            $banner = $app['banner']->fetch();
            $this->deleteImage($banner["image"], 'banners');
        }

        $app['banner']->getById((int)$post['id']);
        $banner = $app['banner']->fetch();

        if ($app['banner']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Banner removido com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o banner.'
            );
        }

        return $app->json($result);
    }
    
}