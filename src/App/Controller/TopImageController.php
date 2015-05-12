<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de TopImage
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class TopImageController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $topimageController = $app['controllers_factory'];

        $topimageController->get('/topimages', array($this, 'TopImagesAction'))->bind('topimages');
        $topimageController->post('/get-topimages', array($this, 'getTopimagesAction'))->bind('get-topimages');
        $topimageController->post('/get-topimage-orders', array($this, 'getTopImageOrdersAction'))->bind('get-topimage-orders');
        $topimageController->post('/topimage/insert', array($this, 'insertTopImageAction'))->bind('new-topimage');
        $topimageController->post('/topimage/update', array($this, 'updateTopImageAction'))->bind('edit-topimage');
        $topimageController->post('/topimage/delete', array($this, 'deleteTopImageAction'))->bind('remove-topimage');

        return $topimageController;
    }

    /**
     * topimage action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function TopImagesAction(Application $app)
    {
        return $app['twig']->render('admin/topimages.twig', array("modal" => "admin/topimage-modal.twig", "navigate" => "Imagens de Topo"));
    }

    /**
     * getTopImage action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getTopImagesAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['banner']->getAll('id, banner.order, image, link, status');
            $topimages = $app['banner']->fetch_all(MYSQLI_NUM);
            foreach ($topimages as $key => $topimage) {
                $topimages[$key][2] = $topimages[$key][2] != "" ? "<img class='img-thumbnail' alt='60x50' src='/uploads/banners/" . $topimage[2] . "' data-holder-rendered='true' style='width: 60px; height: 50px;'>" : "";
                $topimages[$key][4] = $topimages[$key][4] == "active" ?  "Ativo" : "Inativo";
            }

            $data = array("data" => $topimages);
            return $app->json($data);

        } else {

            $app['banner']->getById((int)$post['id']);
            $topimage = $app['banner']->fetch();

            if (is_array($topimage)) {
                $result = array(
                    'success' => true,
                    'topimage' => $topimage
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar a imagem.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * getTopImageType action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getTopImageOrdersAction(Application $app, Request $request)
    {

        $app['banner']->getCountActive();
        $orders = $app['banner']->fetch();

        if (is_array($orders)) {
            $result = array(
                'success' => true,
                'orders' => $orders
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível trazer as ordens.'
            );
        }

        return $app->json($result);
        
    }

    /**
     * TopImage Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertTopImageAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $error = '';

        if ($file['image'] == false) {
            $error .= '- Imagem<br>';
        } 

        if (!$post['order']) {
            $error .= '- Ordem<br>';
        }

        if(empty($error) == false){
            $error = 'Os campos:<br><br>'.$error.'<br>Devem ser preenchidos.';
            return $this->error($app, $error);
        }

        $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
        $fileName = null;

        if ($file['image']) {
            
            $fileInfo = getimagesize($file['image']->getFileInfo()->getPathName());

            if($fileInfo[0] >= 1024 && $fileInfo[1] >= 450){
                $fileName = $this->uploadImage($file['image'], 'banners');
            } else {
                return $this->error($app, 'Imagem deve ter largura de 1024px e altura de 450px no mínimo.');
            }
        }

        $app['banner']->getAll('id, banner.order');
        $topimages = $app['banner']->fetch_all(MYSQLI_NUM);

        foreach ($topimages as $key => $topimage) {
            if($topimages[$key][1] >= $post['order']){
                $new_order = (int)$topimages[$key][1] + 1;
                if ($app['banner']->update("banner.order = '{$new_order}'", (int)$topimages[$key][0])) {
                    continue;
                } else {
                    $result = array(
                        'success' => false,
                        'message' => 'Erro ao tentar ordenar as imagens.'
                    );
                    return $app->json($result);
                }
            }
        }

        if ($app['banner']->insert(
            $fileName,
            $post['link'],
            (int)$post['order'],
            \App\Enum\BannerTypeEnum::TOPIMAGE,
            date('Y-m-d H:i:s'),
            $status,
            $post['title'],
            $post['description']
        )) {
            $result = array(
                'success' => true,
                'message' => 'Imagem registrada com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível criar a imagem. Verifique se a mesma já foi cadastrada.'
            );
        }

        return $app->json($result);
    }

    /**
     * TopImage Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateTopImageAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $error = '';

        if (!$post['id']) {
            $error .= '- Imagem não encontrada<br>';
        }

        if (!$post['image']) {
            $error .= '- Imagem<br>';
        } 

        if (!$post['order']) {
            $error .= '- Ordem<br>';
        }

        if(empty($error) == false){
            $error = 'Os campos:<br><br>'.$error.'<br>Devem ser preenchidos.';
            return $this->error($app, $error);
        }

        $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
        $fileName = null;
        $order = null;

        if ($app['banner']->getById((int)$post['id'])) {
            $topimage = $app['banner']->fetch();
            $order = $topimage["order"];

            if ($post['image'] && $file['image']) {
                $fileInfo = getimagesize($file['image']->getFileInfo()->getPathName());
                if($fileInfo[0] >= 1024 && $fileInfo[1] >= 450){
                    $this->deleteImage($topimage["image"], 'banners');
                    $fileName = $this->uploadImage($file['image'], 'banners');
                } else {
                    return $this->error($app, 'Imagem deve ter largura de 1024px e altura de 450px no mínimo.');
                }
            } else if (!$post['image']) {
                $this->deleteImage($topimage["image"], 'banners');
            } else {
                $fileName = $post['image'];
            }

        }

        if ($app['banner']->update("image = '{$fileName}', link = '{$post['link']}', banner.order = '{$post['order']}', status = {$status}, title = '{$post['title']}', description = '{$post['description']}'", (int)$post['id'])) {

            $app['banner']->get('SELECT id, banner.order FROM banner WHERE id <> '. (int)$post["id"] .' AND type = '.\App\Enum\BannerTypeEnum::TOPIMAGE.' ORDER BY banner.order;');
            $topimages = $app['banner']->fetch_all(MYSQLI_NUM);

            $new_order = $post['order'];
            if($order != $post['order']) {
                foreach ($topimages as $key => $topimage) {
                    if($topimages[$key][1] >= $post['order']) {
                        $new_order++;
                        if ($app['banner']->update("banner.order = '{$new_order}'", (int)$topimages[$key][0])) {
                            continue;
                        } else {
                            $result = array(
                                'success' => false,
                                'message' => 'Erro ao tentar ordenar as imagens.'
                            );
                            return $app->json($result);
                        }
                    }
                }
            }

            $result = array(
                'success' => true,
                'message' => 'Imagem alterada com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível criar a imagem.'
            );
        }

        return $app->json($result);
    }

    /**
     * TopImage Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteTopImageAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Imagem não encontrada.');
        }

        if ($app['banner']->getById((int)$post['id'])) {
            $topimage = $app['banner']->fetch();
            $this->deleteImage($topimage["image"], 'banners');
        }

        $app['banner']->getById((int)$post['id']);
        $topimage = $app['banner']->fetch();

        if ($app['banner']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Imagem removida com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover a imagem.'
            );
        }

        return $app->json($result);
    }
    
}