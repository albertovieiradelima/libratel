<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller Provider do Crmall
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 * @author Victor Hugo Martins <victorhugo.pmartins@gmail.com>
 */
class CrmallController implements ControllerProviderInterface {

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app) {

        $indexController = $app['controllers_factory'];

        $indexController->match('/shopping/banner/{id}', array($this, 'shoppingBannerAction'))->assert('id', '.*');
        $indexController->match('/shopping/logo/{id}', array($this, 'shoppingLogoAction'))->assert('id', '.*');
        $indexController->match('/supplier/logo/{id}', array($this, 'supplierLogoAction'))->assert('id', '.*');
        $indexController->match('/supplier/{id}', array($this, 'supplierAction'))->assert('id', '.*');
        $indexController->match('/category/{id}', array($this, 'categoryAction'))->assert('id', '.*');
        $indexController->post('/costumer/{event}', array($this, 'costumerAction'));
        $indexController->match('/responsible/{cpf}', array($this, 'costumerAction'))->assert('cpf', '.*');

        return $indexController;
    }

    /**
     * Shopping action
     * @param \Silex\Application $app
     */
    public function shoppingAction(Application $app, $id) {

        // Na filiação do tipo 2 é para trazer o selo da ABRASCE

        if (is_numeric($id)) {
            $app['shopping']->getShoppingById($id);
        } else if ((strlen($id) == 1) && ($id >= 'A' && $id <= 'Z')) {
            $app['shopping']->getShoppingByAZ($id);
        } else if (strlen($id) == 2) { // UF
             $app['shopping']->getShoppingByUF($id);
        } else {
            $app['shopping']->getShoppingAll();
        }

        $shopping = $app['shopping']->fetch_all();
        $rows = $app['shopping']->rows();
        $app['shopping']->close();

        $result = array(
            'success' => true,
            'count' => $rows,
            'shopping' => $shopping
        );

        return $app->json($result);
    }

    /**
     * Shopping Banner
     * @param \Silex\Application $app
     */
    public function shoppingBannerAction(Application $app, $id) {

        if (is_numeric($id)) {
            $app['shopping']->getShoppingBannerById($id);
            $image = $app['shopping']->fetch_all();
            $app['shopping']->close();
        }

        if ($image[0]['banner'] && file_exists(PATH_UPLOAD_SHOPPING . '/' . $image[0]['banner'])) {
            $banner = file_get_contents(PATH_UPLOAD_SHOPPING . '/' . $image[0]['banner']);
            $file = pathinfo(PATH_UPLOAD_SHOPPING . '/' . $image[0]['banner']);
        } else {
            $banner = file_get_contents(PATH_ASSETS . '/img/banner_info_shopping.jpg');
            $file = pathinfo(PATH_ASSETS . '/img/banner_info_shopping.jpg');
        }

        $type = 'image/' . $file['extension'];

        $response = new Response();
        $response->headers->set('Content-type', $type);
        $response->setContent($banner);

        return $response;
    }

    /**
     * Shopping Logo 
     * @param \Silex\Application $app
     */
    public function shoppingLogoAction(Application $app, $id) {

        if (is_numeric($id)) {
            $app['shopping']->getShoppingLogoById($id);
            $image = $app['shopping']->fetch_all();
            $app['shopping']->close();
        }

        if ($image[0]['logo'] && file_exists(PATH_UPLOAD_SHOPPING . '/' . $image[0]['logo'])) {
            $logo = file_get_contents(PATH_UPLOAD_SHOPPING . '/' . $image[0]['logo']);
            $file = pathinfo(PATH_UPLOAD_SHOPPING . '/' . $image[0]['logo']);
        } else {
            $logo = file_get_contents(PATH_ASSETS . '/img/logo_info_shopping.png');
            $file = pathinfo(PATH_ASSETS . '/img/banner_info_shopping.jpg');
        }

        $type = 'image/' . $file['extension'];

        $response = new Response();
        $response->headers->set('Content-type', $type);
        $response->setContent($logo);

        return $response;
    }

    /**
     * SupplierLogo 
     * @param \Silex\Application $app
     */
    public function supplierLogoAction(Application $app, $id) {

        if (is_numeric($id)) {
            $app['supplier']->getSupplierLogoById($id);
            $image = $app['supplier']->fetch_all();
            $app['supplier']->close();
        }

        if ($image[0]['logo'] && file_exists(PATH_UPLOAD_SUPPLIER . '/' . $image[0]['logo'])) {
            $logo = file_get_contents(PATH_UPLOAD_SUPPLIER . '/' . $image[0]['logo']);
            $file = pathinfo(PATH_UPLOAD_SUPPLIER . '/' . $image[0]['logo']);
        } else {
            $logo = file_get_contents(PATH_ASSETS . '/img/logo_info_shopping.png');
            $file = pathinfo(PATH_ASSETS . '/img/banner_info_shopping.jpg');
        }

        $type = 'image/' . $file['extension'];

        $response = new Response();
        $response->headers->set('Content-type', $type);
        $response->setContent($logo);

        return $response;
    }

    /**
     * Category
     * @param \Silex\Application $app
     */
    public function categoryAction(Application $app, $id) {

        if (is_numeric($id)) {
            $app['supplier']->getSupplierByCategory($id);
        } else {
            $app['supplier']->getSupplierCategoryAll();
        }

        $category = $app['supplier']->fetch_all();
        $rows = $app['supplier']->rows();
        $app['supplier']->close();

        $result = array(
            'success' => true,
            'count' => $rows,
            'category' => $category
        );

        return $app->json($result);
    }

    /**
     * Supply
     * @param \Silex\Application $app
     */
    public function supplierAction(Application $app, $id) {

        if (is_numeric($id)) {
            $app['supplier']->getSupplierById($id);
        } else if ((strlen($id) == 1) && ($id >= 'A' && $id <= 'Z')) {
            $app['supplier']->getSupplierByAZ($id);
        } else {
            $app['supplier']->getSupplierAll();
        }

        $supplier = $app['supplier']->fetch_all();
        $rows = $app['supplier']->rows();
        $app['supplier']->close();

        $result = array(
            'success' => true,
            'count' => $rows,
            'category' => $supplier
        );

        return $app->json($result);
    }

    /**
     * Costumer
     * @param \Silex\Application $app
     */
    public function costumerAction(Application $app, Request $request,$event) {
        try{
            $post = $request->request->all();
            $document=$post['document'];

            $app['event']->getById($event);
            $event = $app['event']->fetch();

            if (strlen($document) <= 14) {
                $app['crmall_costumer']->getCostumerByCPF($document);
            } else {
                $app['crmall_costumer']->getCostumerByCNPJ($document);
            }

            $costumer = $app['crmall_costumer']->fetch_all();
            $app['crmall_costumer']->close();

            if($event['exclusive_associated']!='0' && $costumer[0]['FILIACAO']!='2'){
                throw new \Exception('O evento é exclusivo a associados');
            }

            $result = array(
                'success' => true,
                'costumer' => isset($costumer[0])?$costumer[0]:[]
            );

            return $app->json($result);
        }catch(\Exception $e){
            $result = array('success'=>false,'error'=>$e->getMessage());

            return $app->json($result);
        }
    }

    /**
     * Responsible
     * @param \Silex\Application $app
     */
    public function responsibleAction(Application $app, $cpf) {
        $app['crmall_costumer']->getResponsibleByCPF($cpf);

        $responsible = $app['crmall_costumer']->fetch_all();
        $rows = $app['crmall_costumer']->rows();
        $app['crmall_costumer']->close();

        $result = array(
            'success' => true,
            'count' => $rows,
            'responsible' => $responsible
        );

        return $app->json($result);
    }

    /**
     * Get Image Base64 Type (.png | .jpg | .gif)
     * @param \Silex\Application $app
     */
    private function getImageBaseType($ext) {

        if ($ext == '.png') {
            return 'data:image/png;base64,';
        } else if ($ext == '.jpg') {
            return 'data:image/jpg;base64,';
        } else if ($ext == '.gif') {
            return 'data:image/gif;base64,';
        } else {
            return 'data:image/jpg;base64,';
        }
    }

    /**
     * Get Image Type (.png | .jpg | .gif)
     * @param \Silex\Application $app
     */
    private function getImageType($ext) {

        if ($ext == '.png') {
            return 'image/png';
        } else if ($ext == '.jpg') {
            return 'image/jpeg';
        } else if ($ext == '.gif') {
            return 'image/gif';
        } else {
            return 'image/jpeg';
        }
    }

}