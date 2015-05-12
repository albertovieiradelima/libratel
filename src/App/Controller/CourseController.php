<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model;

/**
 * Controller Provider de Course
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class CourseController extends BaseController
{

    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app)
    {

        $courseController = $app['controllers_factory'];

        $courseController->get('/courses', array($this, 'coursesAction'))->bind('courses');
        $courseController->post('/get-courses', array($this, 'getCoursesAction'))->bind('get-courses');
        $courseController->post('/courses/insert', array($this, 'insertCourseAction'))->bind('new-courses');
        $courseController->post('/courses/update', array($this, 'updateCourseAction'))->bind('edit-courses');
        $courseController->post('/courses/delete', array($this, 'deleteCourseAction'))->bind('remove-courses');

        return $courseController;
    }

    /**
     * courses action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function coursesAction(Application $app)
    {
        return $app['twig']->render('admin/courses.twig', array("modal" => "admin/course-modal.twig", "navigate" => "Curso"));
    }

    /**
     * getCourses action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function getCoursesAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {

            $app['course']->getAll("id, title, number_vacancies, start_date, end_date, status", \App\Enum\EventTypeEnum::COURSE);
            $courses = $app['course']->fetch_all(MYSQLI_NUM);
            foreach ($courses as $key => $course) {
                $courses[$key][3] = $this->formatDate($courses[$key][3], "d/m/Y");
                $courses[$key][4] = $this->formatDate($courses[$key][4], "d/m/Y");
                $courses[$key][5] = $courses[$key][5] == "active" ?  "Ativo" : "Inativo";
                $button = '
                            <div class="btn-group">
                              <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-cog"></i> Opções <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu options" role="menu">
                                <li><a href="/admin/event-charge-period/'.$course[0].'"><i class="fa fa-barcode"></i>Faixa de Cobrança</a></li>
                                <li><a href="/admin/event-registration/'.$course[0].'"><i class="fa fa-user"></i> Gerenciar Inscrições</a></li>
                                <li><a href="/admin/event-registration-participants/'.$course[0].'"><i class="fa fa-group"></i>Gerenciar Participantes</a></li>
                                <li><a href="/admin/event-report-tracking/' . $course[0] . '"><i class="fa fa-file-text-o"></i>Relatório de Acompanhamento</a></li>
                              </ul>
                            </div>';
                $courses[$key][6] = $button;
            }

            $array = array("data" => $courses);
            return $app->json($array);

        } else {

            $app['course']->getById((int)$post['id']);
            $course = $app['course']->fetch();
            $course["start_date"] = $this->formatDate($course["start_date"], "d/m/Y");
            $course["end_date"] = $this->formatDate($course["end_date"], "d/m/Y");

            if (is_array($course)) {
                $result = array(
                    'success' => true,
                    'course' => $course
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível encontrar o curso.'
                );
            }

            return $app->json($result);

        }

    }

    /**
     * Course Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertCourseAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $commonData = array('title' => 'Titulo', 'description' => 'Descrição', 'start_date' => 'Data de início', 
            'end_date' => 'Data de término', 'starthour' => 'Hora de início', 'endhour' => 'Hora de término', 'local' => 'Local');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $inscription = $post['inscription'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $exclusive_associated = $post['exclusive_associated'] === 'on' ? true : false;
            $free_event = $post['free_event'] === 'on' ? true : false;

            $fileName = null;

            if ($file['image']) {

                $fileName = $this->uploadImage($file['image'], 'events');
            }

            if ($app['course']->insert(
                $post['title'],
                $post['description'],
                $fileName,
                \App\Enum\EventTypeEnum::COURSE,
                $this->formatDateMysql($post['start_date']),
                $this->formatDateMysql($post['end_date']),
                $post['starthour'],
                $post['endhour'],
                $post['local'],
                $inscription,
                $status,
                $post['site'],
                $exclusive_associated,
                $free_event,
                (int)$post['number_vacancies'],
                (int)$post['days_invoice'],
                $post['cancellation_policy']
            )) {
                $result = array(
                    'success' => true,
                    'message' => 'Curso registrado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar o Curso. Verifique se o curso já foi cadastrado.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Course Update
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function updateCourseAction(Application $app, Request $request)
    {

        $post = $request->request->all();
        $file = $request->files->all();

        $commonData = array('id' => 'Evento não encontrado', 'title' => 'Titulo', 'description' => 'Descrição', 'start_date' => 'Data de início', 
            'end_date' => 'Data de término', 'starthour' => 'Hora de início', 'endhour' => 'Hora de término', 'local' => 'Local');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            $status = $post['status'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $inscription = $post['inscription'] === "on" ? \App\Enum\SituationStatusEnum::ACTIVE : \App\Enum\SituationStatusEnum::INACTIVE;
            $exclusive_associated = $post['exclusive_associated'] === 'on' ? true : false;
            $free_event = $post['free_event'] === 'on' ? true : false;

            if ($inscription === \App\Enum\SituationStatusEnum::ACTIVE && !$post['exclusive_associated']) {
                if (!$chargePeriod = $app['event_charge_period']->getAllByEvent((int)$post['id'])) {
                    return $app->json(array(
                        'success' => false,
                        'message' => 'Permitir Incrições: negada!<br/>Este Curso não possui faixa de cobrança cadastrada.'
                    ));
                } else if (strtotime($chargePeriod[0][1]) > strtotime(date('Y-m-d')) || strtotime($chargePeriod[0][2]) < strtotime(date('Y-m-d'))) {
                    return $app->json(array(
                        'success' => false,
                        'message' => 'Permitir Incrições: negada!<br/>A faxia de cobrança encontrada não está programada para o período atual.'
                    ));
                }
            }

            $fileName = null;

            if ($post['image'] && $file['image']) {

                if ($app['course']->getById((int)$post['id'])) {
                    
                    $course = $app['course']->fetch();
                    $this->deleteImage($course["image"], 'events');
                }

                $fileName = $this->uploadImage($file['image'], 'events');

            } else if (!$post['image']){

                if ($app['course']->getById((int)$post['id'])) {
                    $course = $app['course']->fetch();
                    $this->deleteImage($course["image"], 'events');
                }

            } else {
                $fileName = $post['image'];
            }

            if ($app['course']->update("title = '{$post['title']}', description = '{$post['description']}', image = '{$fileName}', start_date = '{$this->formatDateMysql($post['start_date'])}', end_date = '{$this->formatDateMysql($post['end_date'])}', start_hour = '{$post['starthour']}', end_hour = '{$post['endhour']}', local = '{$post['local']}', inscription = {$inscription}, status = {$status}, site = '{$post['site']}', exclusive_associated = '{$exclusive_associated}', free_event = '{$free_event}', number_vacancies = '{$post['number_vacancies']}', days_invoice = '{$post['days_invoice']}', cancellation_policy = '{$post['cancellation_policy']}'", (int)$post['id'])) {
                $result = array(
                    'success' => true,
                    'message' => 'Curso alterado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar o curso.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * Course Delete
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function deleteCourseAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['id']) {
            return $this->error($app, 'Curso não encontrado.');
        }

        if ($app['course']->getById((int)$post['id'])) {
            $course = $app['course']->fetch();
            $this->deleteImage($course["image"], 'events');
        }

        if($app['subscriber']->getByEvent((int)$post['id'])) {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o curso, pois exitem pedidos de inscrições para o mesmo.'
            );
        } elseif ($app['course']->delete((int)$post['id'])) {
            $result = array(
                'success' => true,
                'message' => 'Curso removido com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível remover o curso.'
            );
        }

        return $app->json($result);
    }
    

}