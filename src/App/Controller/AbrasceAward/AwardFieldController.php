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
class AwardFieldController extends \App\Controller\BaseController
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

        $controller->post('/award-field/{id}/get-data', array($this, 'getDataAction'))->value('id', false)->bind('aa-award-field-get-data');
        $controller->post('/award-field/insert', array($this, 'insertAction'))->bind('aa-award-field-insert');
        $controller->post('/award-field/update', array($this, 'updateAction'))->bind('aa-award-field-update');
        $controller->post('/award-field/delete', array($this, 'deleteAction'))->bind('aa-award-field-delete');
        $controller->get('/award-field/{id}', array($this, 'indexAction'))->value('id', false)->bind('aa-award-field');
        
        return $controller;
    }

    /**
     * award management action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function indexAction(Application $app, Request $request, $id)
    {
        if(!$id){
            throw new \Exception('ID inválido');
        }
        
        $awards = $app['aa_award']->getAll();
        $award = $app['aa_award']->getById((int)$id);
        if(!$award){
            throw new \Exception('Prêmio não encontrado');
        }
        
        return $app['twig']->render('admin/abrasce-award/award-field.twig', array("modal" => 'admin/abrasce-award/award-field-modal.twig', "navigate" => "Campos do Projeto", "award" => $award, "awards" => $awards));
    }

    /**
     * getAboutus action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getDataAction(Application $app, Request $request, $id)
    {
        
        if(!$id){
            return $app->json(array(
                'success' => false,
                'message' => 'Prêmio não informado'
            ));
        }
        
        $post = $request->request->all();
        $objId = $post['id'];
        $fk_award = $id;

        if (!$objId) {

            $data = $app['aa_award_field']->getAllForDT(null, $fk_award);
            return $app->json(array(
                'data' => $data
            ));

        } else {

            try{
                
                $obj = $app['aa_award_field']->getById((int)$objId);
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
     * Aboutus Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertAction(Application $app, Request $request)
    {
        try{
            
            $post = $request->request->all();
            
            $aa_award_field_id = $app['aa_award_field']->insert($post);

            if ($aa_event_id = $app['aa_event']->getAllId()){
                foreach ($aa_event_id as $event) {
//                    if($app['aa_award_event_field']->getByEvent($event['id'])){
                        $post['id'] = $aa_award_field_id;
                        $post['fk_event'] = $event['id'];
                        $app['aa_award_event_field']->insert($post);
//                    }
                }

            }

            return $app->json(array(
                'success' => true,
                'message' => 'Campo salvo com sucesso!'
            ));
            
        }catch(\Exception $ex){
            return $this->error($app, $ex->getMessage());
        }
    }

    /**
     * Aboutus Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateAction(Application $app, Request $request)
    {

        try{
            
            $post = $request->request->all();
            $data = array(
                'type' => $post['type'],
                'title' => $post['title'],
                'description' => $post['description'],
                'weight' => $post['weight'],
                'order' => $post['order'],
                'accept_filetypes' => $post['accept_filetypes'],
                'maxlength' => $post['maxlength']
                //'fk_award' => $post['fk_award']
            );
            
            $app['aa_award_field']->update($data, $post['id']);
            $app['aa_award_event_field']->update($data, $post['id']);
            
            return $app->json(array(
                'success' => true,
                'message' => 'Campo salvo com sucesso!'
            ));
            
        }catch(\Exception $ex){
            return $this->error($app, $ex->getMessage());
        }
    }

    /**
     * Aboutus Delete
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

        $ent = $app['aa_award_field']->getById((int)$post['id']);
        if (!$ent) {
            return $this->error($app, 'Registro não encontrado.');
        }

        if ($app['aa_award_field']->delete((int)$post['id'])) {

            if($aa_award_event_field = $app['aa_award_event_field']->getById((int)$post['id'])){

                if($aa_award_event_field_registration = $app['aa_award_event_field_registration']->getByAwardEventField($aa_award_event_field["id"])){

                    foreach ($aa_award_event_field_registration as $item) {

                        $app['aa_award_event_field_registration']->deleteByAwardEventField($item["fk_award_event_field"]);
                    }

                }

                $app['aa_award_event_field']->delete((int)$post['id']);
            }
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