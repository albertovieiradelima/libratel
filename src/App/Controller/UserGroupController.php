<?php
/**
 * Created by PhpStorm.
 * User: albertovieira
 * Date: 5/5/15
 * Time: 8:51 AM
 */

namespace App\Controller;

use App\Enum\SituationStatusEnum;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

use App\Model;

/**
 * Controller Provider de UserGroup
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class UserGroupController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return mixed|\Silex\ControllerCollection $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $controller = $app['controllers_factory'];

        $controller->get('/user-group', array($this, 'userGroupAction'))->bind('user-group');
        $controller->post('/get-user-group', array($this, 'getUserGroupAction'))->bind('get-user-group');
        $controller->post('/get-user-group-file-category', array($this, 'getUserGroupFileCategoryAction'))->bind('get-user-group-file-category');
        $controller->post('/user-group/insert', array($this, 'insertUserGroupAction'))->bind('new-user-group');
        $controller->post('/user-group/update', array($this, 'updateUserGroupAction'))->bind('edit-user-group');
        $controller->post('/user-group/delete', array($this, 'deleteUserGroupAction'))->bind('remove-user-group');

        return $controller;
    }

    /**
     * usergroup action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function userGroupAction(Application $app)
    {
        return $app['twig']->render('admin/user-group.twig', array("modal" => "admin/user-group-modal.twig", "navigate" => "Grupos de Usuário",));
    }

    /**
     * getUserGroupFileCategory action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getUserGroupFileCategoryAction(Application $app)
    {

        $fileCategory = $app['file_category']->getAllActive();

        if ($fileCategory) {

            $result = $fileCategory;
        } else {

            $result = array(
                'success' => false,
                'message' => 'Nenhum registro encontrado.'
            );
        }

        return $app->json($result);
    }

    /**
     * getUserGroup action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getUserGroupAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $user_groups = $app['user_group']->getAll();

            foreach ($user_groups as $key => $user_group) {

                $user_groups[$key][2] = $user_group[2] == 1 ? "Ativo" : "Inativo";
            }

            $data = array("data" => $user_groups);
            return $app->json($data);

        } else {

            $user_group = $app['user_group']->getById((int)$post['id']);
            $userGroupFileCategory = $app['user_group_file_category']->getAllByUserGroup('fk_file_category' ,(int)$post['id']);

            if (is_array($user_group)) {

                $result = array(
                    'success' => true,
                    'user_group' => $user_group,
                    'user_group_file_category' => $userGroupFileCategory
                );
            } else {

                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar o grupo.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * UserGroup Registration
     *
     * @param \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function insertUserGroupAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['name']) {
            return $this->error($app, 'O Nome deve ser preenchido.');
        }

        $data = array(
            'name' => $post['name'],
            'status' => $post['status'] === "on" ? SituationStatusEnum::ACTIVE : SituationStatusEnum::INACTIVE
        );

        if ($user_group_id = $app['user_group']->insert($data)) {

            if (is_array($post['fk_file_category'])) {

                foreach ($post['fk_file_category'] as $file_category_selected) {

                    $data_category = array(
                        'fk_user_group' => $user_group_id,
                        'fk_file_category' => (int)$file_category_selected
                    );

                    $app['user_group_file_category']->insert($data_category);
                }
            }

            $result = array(
                'success' => true,
                'message' => 'Grupo de Usuário registrado com sucesso.'
            );
        } else {

            $result = array(
                'success' => false,
                'message' => 'Não foi possível registrar o Grupo de Usuário. Verifique se a mesma já foi cadastrado.'
            );
        }

        return $app->json($result);
    }

    /**
     * UserGroup Update
     *
     * @param \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateUserGroupAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Categoria não encontrado.');
        }

        if (!$post['name']) {
            return $this->error($app, 'O Nome deve ser preenchido.');
        }

        $data = array(
            'name' => $post['name'],
            'status' => $post['status'] === "on" ? SituationStatusEnum::ACTIVE : SituationStatusEnum::INACTIVE
        );

        if ($app['user_group']->update($data, (int)$post['id'])) {

            if (is_array($post['fk_file_category'])) {

                $app['user_group_file_category']->deleteByUserGroup((int)$post['id']);

                foreach ($post['fk_file_category'] as $file_category_selected) {

                    $data_category = array(
                        'fk_user_group' => (int)$post['id'],
                        'fk_file_category' => (int)$file_category_selected
                    );

                    $app['user_group_file_category']->insert($data_category);
                }
            }

            $result = array(
                'success' => true,
                'message' => 'Categoria alterado com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível alterar o grupo.'
            );
        }

        return $app->json($result);
    }

    /**
     * UserGroup Delete
     *
     * @param \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteUserGroupAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            return $this->error($app, 'Grupo de Usuário não encontrado.');
        } else if ($users = $app['user']->getAllByGroup('id', (int)$post['id'])) {

            if (is_array($users) && count($users) > 0) {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível remover o grupo, pois existem usuários vinculadas ao mesmo.'
                );
                return $app->json($result);
            }
        }

        if ($app['user_group']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Grupo de Usuário removida com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o grupo.'
            );
        }

        return $app->json($result);
    }

}