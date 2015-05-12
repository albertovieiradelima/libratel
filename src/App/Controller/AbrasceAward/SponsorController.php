<?php
/**
 * Created by PhpStorm.
 * User: albertovieiradelima
 * Date: 27/01/15
 * Time: 11:41
 */

namespace App\Controller\AbrasceAward;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use App\Model;

/**
 * Controller Provider de Sponsor
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class SponsorController extends \App\Controller\BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $sponsorController = $app['controllers_factory'];

        $sponsorController->get('/sponsors', array($this, 'sponsorsAction'))->bind('sponsor');
        $sponsorController->post('/get-sponsors', array($this, 'getSponsorsAction'))->bind('get-sponsors');
        $sponsorController->post('/sponsor/insert', array($this, 'insertSponsorAction'))->bind('new-sponsor');
        $sponsorController->post('/sponsor/update', array($this, 'updateSponsorAction'))->bind('edit-sponsor');
        $sponsorController->post('/sponsor/delete', array($this, 'deleteSponsorAction'))->bind('remove-sponsor');

        return $sponsorController;
    }

    /**
     * sponsor action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function sponsorsAction(Application $app)
    {
        return $app['twig']->render('admin/abrasce-award/sponsors.twig', array("modal" => "admin/abrasce-award/sponsor-modal.twig", "navigate" => "Patrocinadores"));
    }

    /**
     * getSponsor action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getSponsorsAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['aa_sponsor']->getAll('id, name, logo, status');
            $sponsors = $app['aa_sponsor']->fetch_all(MYSQLI_NUM);
            foreach ($sponsors as $key => $sponsor) {
                $sponsors[$key][2] = $sponsors[$key][2] != "" ? "<img class='img-thumbnail' alt='60x50' src='/uploads/sponsors/" . $sponsor[2] . "' data-holder-rendered='true' style='width: 60px; height: 50px;'>" : "";
                $sponsors[$key][3] = $sponsors[$key][3] == "active" ?  "Ativo" : "Inativo";
            }

            $data = array("data" => $sponsors);
            return $app->json($data);

        } else {

            $app['aa_sponsor']->getById((int)$post['id']);
            $sponsor = $app['aa_sponsor']->fetch();

            if (is_array($sponsor)) {
                $result = array(
                    'success' => true,
                    'sponsor' => $sponsor
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar o patrocinador.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * getOrder action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getSponsorOrdersAction(Application $app, Request $request)
    {

        $app['aa_sponsor']->getCountActive();
        $orders = $app['aa_sponsor']->fetch();

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
     * Sponsor Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertSponsorAction(Application $app, Request $request)
    {
        $post = $request->request->all();
        $file = $request->files->all();

        $commonData = array('name' => 'Nome', 'description' => 'Descrição');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n");

        if ($valid === true) {

            $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $fileName = null;

            if ($file['image']) {

                $fileName = $this->uploadImage($file['image'], 'sponsors');
            }

//            $app['aa_sponsor']->getAll('id, aa_sponsor.order');
//            $sponsors = $app['aa_sponsor']->fetch_all(MYSQLI_NUM);
//
//            foreach ($sponsors as $key => $sponsor) {
//                if($sponsors[$key][1] >= $post['order']){
//                    $new_order = (int)$sponsors[$key][1] + 1;
//                    if ($app['aa_sponsor']->update("aa_sponsor.order = '{$new_order}'", (int)$sponsors[$key][0])) {
//                        continue;
//                    } else {
//                        $result = array(
//                            'success' => false,
//                            'message' => 'Erro ao tentar ordenar os patrocinadores.'
//                        );
//                        return $app->json($result);
//                    }
//                }
//            }

            if ($app['aa_sponsor']->insert(
                $post['name'],
                $fileName,
                $post['description'],
                $post['link'],
                $status
            )) {
                $result = array(
                    'success' => true,
                    'message' => 'Patrocinador registrado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar o Patrocinador. Verifique se o mesmo já foi cadastrado.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Sponsor Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateSponsorAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $commonData = array('id' => 'Patrocinador não encontrado', 'name' => 'Nome', 'description' => 'Descrição');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n");

        if ($valid === true) {

            $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $fileName = null;
//            $order = null;

            if ($app['aa_sponsor']->getById((int)$post['id'])) {
                $sponsor = $app['aa_sponsor']->fetch();
//                $order = $sponsor["order"];

                if ($post['image'] && $file['image']) {
                    $this->deleteImage($sponsor["image"], 'sponsors');
                    $fileName = $this->uploadImage($file['image'], 'sponsors');
                } else if (!$post['image']) {
                    $this->deleteImage($sponsor["image"], 'sponsors');
                } else {
                    $fileName = $post['image'];
                }
            }

            if ($app['aa_sponsor']->update("
                name = '{$post['name']}',
                logo = '{$fileName}',
                description = '{$post['description']}',
                link = '{$post['link']}',
                status = {$status}
            ", (int)$post['id']
            )) {

//                $app['aa_sponsor']->get('SELECT id, aa_sponsor.order FROM aa_sponsor WHERE id <> '. (int)$post["id"] .' ORDER BY aa_sponsor.order;');
//                $sponsors = $app['aa_sponsor']->fetch_all(MYSQLI_NUM);
//
//                $new_order = $post['order'];
//                if($order != $post['order']) {
//                    foreach ($sponsors as $key => $sponsor) {
//                        if($sponsors[$key][1] >= $post['order']) {
//                            $new_order++;
//                            if ($app['aa_sponsor']->update("aa_sponsor.order = '{$new_order}'", (int)$sponsors[$key][0])) {
//                                continue;
//                            } else {
//                                $result = array(
//                                    'success' => false,
//                                    'message' => 'Erro ao tentar ordenar os patrocinadores.'
//                                );
//                                return $app->json($result);
//                            }
//                        }
//                    }
//                }

                $result = array(
                    'success' => true,
                    'message' => 'Patrocinador alterado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar o patrocinador.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Sponsor Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteSponsorAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Patrocinador não encontrado.');
        }

        if ($app['aa_sponsor']->getById((int)$post['id'])) {
            $sponsor = $app['aa_sponsor']->fetch();
            $this->deleteImage($sponsor["image"], 'sponsors');
        }

        if ($app['aa_sponsor']->delete((int)$post['id']))  {
            $result = array(
                'success' => true,
                'message' => 'Patrocinador removido com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o patrocinador.'
            );
        }

        return $app->json($result);
    }

}