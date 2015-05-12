<?php

namespace App\Controller\AbrasceAward;

use App\Util\DateUtil;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de Award Event
 *
 * @author Renato Peterman <renato.peterman@crmall.com>
 */
class AwardEventController extends \App\Controller\BaseController
{

    protected $controllerBaseRoute = "award-event";
    protected $defaultModel;

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $this->defaultModel = $app['aa_award_event'];

        $controller->get("/{$this->controllerBaseRoute}", array($this, 'indexAction'))->bind("aa-{$this->controllerBaseRoute}");
        $controller->post("/{$this->controllerBaseRoute}/list/{id}", array($this, 'listAction'))->value('id',false)->bind("aa-{$this->controllerBaseRoute}-list");
        $controller->post("/{$this->controllerBaseRoute}/insert", array($this, 'insertAction'))->bind("aa-{$this->controllerBaseRoute}-insert");
        $controller->post("/{$this->controllerBaseRoute}/update", array($this, 'updateAction'))->bind("aa-{$this->controllerBaseRoute}-update");
        $controller->post("/{$this->controllerBaseRoute}/delete", array($this, 'deleteAction'))->bind("aa-{$this->controllerBaseRoute}-delete");
        
        return $controller;
    }

    /**
     * Award event management action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function indexAction(Application $app)
    {
        return $app['twig']->render('admin/abrasce-award/award.twig', array(
            "modal" => 'admin/abrasce-award/award-modal.twig',
            "navigate" => "Prêmio Evento",
            "awards" => $awards
        ));
    }

    /**
     * Get register action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function listAction(Application $app, Request $request, $id)
    {
        
        $post = $request->request->all();
        if($post['fk_event']){
            $fk_event = $post['fk_event'];
        }else{
            $fk_event = $id;
        }

        $fk_award = $post['fk_award'];

        if (!$fk_award) {

            $data = $this->defaultModel->getAllForDT(null, $fk_event);
            return $app->json(array(
                'data' => $data
            ));

        } else {

            try{
                
                $obj = $this->defaultModel->getById($fk_award, $fk_event);
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
            $file = $request->files->all();

            $post['registration_price']         = str_replace(',','', $post['registration_price']);
            $post['registration_date_begin']    = DateUtil::formatDatetimeStringToMysql($post['registration_date_begin']);
            $post['registration_date_end']      = DateUtil::formatDatetimeStringToMysql($post['registration_date_end']);

            // Save Logo
            if($file['logo']){
                $fileName = $this->uploadImage($file['logo'], 'award-event');
                $post['logo'] = $fileName;
            }

            if($file['banner']){
                $fileName = $this->uploadImage($file['banner'], 'award-event');
                $post['banner'] = $fileName;
            }

            $this->defaultModel->insert($post);

            // Get fields from AwardField and save to AwardEventField
            $fields = $app['aa_award_field']->getAll($post['fk_award']);
            if(count($fields) > 0){
                foreach($fields as $fieldObj){
                    $fieldObj['fk_event'] = $post['fk_event'];
                    $app['aa_award_event_field']->insert($fieldObj);
                }
            }
            
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

            $entity = $this->defaultModel->getById($post['fk_award'], $post['fk_event']);

            $registrationPrice  = str_replace(',','', $post['registration_price']);
            $dateBegin          = \DateTime::createFromFormat('d/m/Y H:i',$post['registration_date_begin']);
            $dateEnd            = \DateTime::createFromFormat('d/m/Y H:i',$post['registration_date_end']);

            $file = $request->files->all();

            $data = array(
                'title' => $post['title'],
                'description' => $post['description'],
                'registration_date_begin' => $dateBegin->format('Y-m-d H:i:s'),
                'registration_date_end' => $dateEnd->format('Y-m-d H:i:s'),
                'billing_days_to_due' => $post['billing_days_to_due'],
                'registration_price' => $registrationPrice
            );

            if($file['logo']){
                if($entity["logo"]){
                    $this->deleteImage($entity["logo"], 'award-event');
                }
                $fileName = $this->uploadImage($file['logo'], 'award-event');
                $data['logo'] = $fileName;
            }

            if($file['banner']){
                if($entity["banner"]){
                    $this->deleteImage($entity["banner"], 'award-event');
                }
                $fileName = $this->uploadImage($file['banner'], 'award-event');
                $data['banner'] = $fileName;
            }

            $this->defaultModel->update($data, $post['fk_award'], $post['fk_event']);
            
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

        $fk_award = (int) $post['fk_award'];
        $fk_event = (int) $post['fk_event'];

        if (!$fk_event || !$fk_award) {
            return $this->error($app, 'Registro não encontrado.');
        }

        $ent = $this->defaultModel->getById($fk_award, $fk_event);
        if (!$ent) {
            return $this->error($app, 'Registro não encontrado.');
        }

        // Delete fields
        $awardEvent = $app['aa_registration']->getByAwardEvent($fk_award, $fk_event);

        if(is_array($awardEvent) == false || count($awardEvent) == 0){

            $app['aa_award_event_field']->deleteAwardEventFields($fk_award, $fk_event);

            if ($this->defaultModel->delete($fk_award, $fk_event)) {

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
        } else {

            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o registro, existem inscrições vinculadas ao mesmo.'
            );
        }


        return $app->json($result);
    }
    
}