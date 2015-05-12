<?php

namespace App\Controller\AbrasceAward;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de Awards
 *
 * @author Renato Peterman <renato.peterman@crmall.com>
 */
class AwardController extends \App\Controller\BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->get('/awards', array($this, 'awardsAction'))->bind('aa-awards');
        $controller->post('/awards/get-awards', array($this, 'getAwardsAction'))->bind('aa-awards-get-awards');
        $controller->post('/awards/insert', array($this, 'insertAction'))->bind('aa-awards-insert');
        $controller->post('/awards/update', array($this, 'updateAction'))->bind('aa-awards-update');
        $controller->post('/awards/delete', array($this, 'deleteAction'))->bind('aa-awards-delete');
        
        return $controller;
    }

    /**
     * award management action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function awardsAction(Application $app)
    {
        return $app['twig']->render('admin/abrasce-award/award.twig', array("modal" => 'admin/abrasce-award/award-modal.twig', "navigate" => "Prêmios"));
    }

    /**
     * get register action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getAwardsAction(Application $app, Request $request)
    {
        
        $post = $request->request->all();
        $id = $post['id'];

        if (!$id) {

            $data = $app['aa_award']->getAllForDT(array(
                'description'
            ));
            return $app->json(array(
                'data' => $data
            ));

        } else {

            try{
                
                $obj = $app['aa_award']->getById((int)$id);
                if(!$obj){
                    throw new \Exception('Registro não encontrado');
                }

                return $app->json(array(
                    'success' => true,
                    'data' => $obj
                ));

            }catch(\Exception $ex){

                return $app->json(array(
                    'success' => false,
                    'message' => $ex->getMessage()
                ));
                
            }
            
        }
        
    }

    /**
     * Insert action
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertAction(Application $app, Request $request)
    {
        try{
            
            $post = $request->request->all();
            $app['aa_award']->insert($post);
            
            return $app->json(array(
                'success' => true,
                'message' => 'Prêmio salvo com sucesso!'
            ));
            
        }catch(\Exception $ex){
            return $this->error($app, $ex->getMessage());
        }
    }

    /**
     * Update action
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateAction(Application $app, Request $request)
    {

        try{
            
            $post = $request->request->all();
            $data = array(
                'name' => $post['name'],
                'description' => $post['description'],
                'code' => $post['code'],
                'inactive' => $post['inactive']
            );
            $app['aa_award']->update($data, $post['id']);
            
            return $app->json(array(
                'success' => true,
                'message' => 'Prêmio salvo com sucesso!'
            ));
            
        }catch(\Exception $ex){
            return $this->error($app, $ex->getMessage());
        }
    }

    /**
     * Delete action
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Registro não encontrado.');
        }

        $ent = $app['aa_award']->getById((int)$post['id']);
        if (!$ent) {
            return $this->error($app, 'Registro não encontrado.');
        }

        if ($app['aa_award']->delete((int)$post['id'])) {
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