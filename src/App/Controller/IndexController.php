<?php

namespace App\Controller;

use App\Util\BS2Crypt;
use App\Util\FormUtil;
use App\Util\DateUtil;

use Silex\Application;
use Silex\ControllerProviderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Util\Token;
use App\Util\ResponseError;

use OpenBoleto\Banco\Bradesco;
use OpenBoleto\Agente;

use Knp\Snappy\Pdf;

/**
 * Controller Provider do Administrador
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 * @author Renato Peterman <renato.pet@gmail.com>
 * @author Victor Hugo Martins <victorhugo.pmartins@gmail.com>
 */
class IndexController implements ControllerProviderInterface {
    /**
     * Connect action
     *
     * @param \Silex\Application $app
     * @return $app['controllers_factory']
     */
    public function connect(Application $app) {

        $indexController = $app['controllers_factory'];

        $indexController->get('/', array($this, 'indexAction'))->bind('index');
        $indexController->get('/admin', array($this, 'adminAction'))->bind('admin');
        $indexController->get('/site/busca', array($this, 'searchAction'))->bind('site-busca');
        $indexController->get('/site/busca/noticias', array($this, 'searchNewsAction'))->bind('site-busca-noticias');
        $indexController->get('/site/busca/noticias-associado', array($this, 'searchNewsAssociateAction'))->bind('site-busca-noticias-associado');
        $indexController->get('/site/eventos/{year}', array($this, 'eventsAction'))->value('year', date('Y'))->bind('eventos');
        $indexController->get('/site/busca/eventos', array($this, 'searchEventsAction'))->bind('site-busca-eventos');
        $indexController->get('/site/fale-com-abrasce', array($this, 'contactAction'))->bind('fale-conosco');
        $indexController->get('/site/sobre-a-abrasce/{title}', array($this, 'aboutAction'))->assert('title', '.*');
        $indexController->get('/site/revistas/{id}', array($this, 'magazineAction'))->assert('id', '.*');
        $indexController->get('/site/associado/{page}', array($this, 'associadoAction'))->value('page');
        $indexController->post('/site/get-shopping-address', array($this, 'getShoppingAddressAction'))->bind('get-shopping-address-action');
        $indexController->post('/site/premio-abrasce/inscricao/edit/save', array($this, 'premioAbrasceInscricaoEditSaveAction'))->bind('premio-abrasce-inscricao-edit-save');
        $indexController->get('/site/premio-abrasce/inscricao/edit', array($this, 'premioAbrasceInscricaoEditAction'))->bind('premio-abrasce-inscricao-edit');
        $indexController->post('/site/premio-abrasce/inscricao/save', array($this, 'premioAbrasceInscricaoSaveAction'));
        $indexController->get('/site/premio-abrasce/inscricao/{id}/{idAward}', array($this, 'premioAbrasceInscricaoAction'))->assert('id', '\d+')->assert('idAward', '\d+');
        $indexController->post('/site/premio-abrasce/auth', array($this, 'premioAbrasceAuthAction'))->bind('premio-abrasce-auth');
        $indexController->get('/site/premio-abrasce/{id}/{idAward}', array($this, 'premioAbrasceAction'))->value('id', false)->value('idAward', false);
        $indexController->get('/site/guia-de-shoppings/{id}', array($this, 'guiaShoppingAction'))->assert('id', '.*');
        $indexController->get('/site/shopping/{id}', array($this, 'infoShoppingAction'))->assert('id', '.*');
        $indexController->get('/site/guia-de-fornecedores/{id}', array($this, 'guiaSupplierAction'))->assert('id', '.*');
        $indexController->get('/site/fornecedor/{id}', array($this, 'infoSupplierAction'))->assert('id', '.*');
        $indexController->get('/site/publicacoes/{id}', array($this, 'storeAction'))->assert('id', '.*');
        $indexController->get('/site/noticias/{year}/{month}', array($this, 'newsAction'))->value('year')->value('month')->bind('site-noticias');
        $indexController->get('/site/noticia/{id}', array($this, 'newsViewAction'))->value('id', false);
        $indexController->get('/site/noticias-associado/{year}/{month}', array($this, 'newsAssociateAction'))->value('year')->value('month')->bind('site-noticias-associado');
        $indexController->get('/site/noticia-associado/{id}', array($this, 'newsAssociateViewAction'))->value('id', false);
        $indexController->get('/site/inauguracoes/{id}', array($this, 'inaugurationsAction'))->assert('id', '.*');
        $indexController->post('/site/newsletter', array($this, 'newsletterAction'))->bind('novo-newsletter');
        $indexController->post('/site/fale-com-abrasce/insert', array($this, 'insertContactusAction'))->bind('novo-fale-conosco');
        $indexController->post('/site/seja-associado/insert', array($this, 'insertAssociateAction'))->bind('novo-seja-associado');
        $indexController->post('/site/pedido', array($this, 'orderAction'));
        $indexController->get('/site/boleto-premio/{hash}/{id}/{pdf}', array($this, 'boletoAwardAction'))->value('hash')->value('id')->value('pdf')->bind('boletoAward');
        $indexController->get('/site/premio-abrasce/inscricao/send', array($this, 'premioAbrasceInscricaoSendAction'))->bind('premio-abrasce-inscricao-send');
        $indexController->get('/site/monitoramento/{page}', array($this, 'monitoramentoAction'))->value('page')->bind('site-monitoramento');
        $indexController->post('/site/event-discount-coupon/get-data/{fk_event}', array($this, 'getCouponAction'));

        return $indexController;
    }

    /**
     * Index
     * 
     * @param \Silex\Application $app, Request $request
     */
    public function indexAction(Application $app, Request $request) {

        //event
        $app['event']->getHomeEvents();
        $events = $app['event']->fetch_all();
        $app['event']->close();

        //news
        $app['feed']->getHomeFeeds(\App\Enum\FeedTypeEnum::NOTICIA);
        $news = $app['feed']->fetch_all();
        $app['feed']->close();

        $app['feed']->getHomeFeeds(\App\Enum\FeedTypeEnum::ASSOCIADO);
        $spacepartners = $app['feed']->fetch_all();
        $app['feed']->close();

        //magazines
        $app['magazine']->getHomeMagazines();
        $magazine = $app['magazine']->fetch();
        $app['magazine']->close();

        //topimages
        $app['banner']->getTopImagesByActive();
        $topimages = $app['banner']->fetch_all();
        $app['banner']->getBannersByActive();
        $banners = $app['banner']->fetch_all();
        $app['banner']->close();

        return $app['twig']->render('site/index.twig', array(
            'events' => $events, 
            'news' => $news, 
            'spacepartners' => $spacepartners,
            'magazine' => $magazine,
            'topimages' => $topimages,
            'banners' => $banners
        ));
    }

    /**
     * Inaugurações de Shoppings
     * 
     * @param \Silex\Application $app, Request $request
     * @return \Twig\inauguracoes.twig
     */
    public function inaugurationsAction(Application $app, $id) {

        $app['inauguration']->getByCategory($id);
        date_default_timezone_set('America/Sao_Paulo');
        $inaugurations = $app['inauguration']->fetch_all();
        $app['inauguration']->close();

        // get categories
        $app['inauguration_category']->getAllThemActive();
        $categories = $app['inauguration_category']->fetch_all();
        $app['inauguration_category']->close();

        // get category
        if($id){
            $app['inauguration_category']->getById($id);
            $category = $app['inauguration_category']->fetch();
            $app['inauguration_category']->close();    
        }

        return $app['twig']->render('site/inauguracoes.twig', array(
            'category' => $category, 
            'inaugurations' => $inaugurations, 
            'categories' => $categories
        ));

    }

    /**
     * Eventos
     * 
     * @param \Silex\Application $app, Request $request
     * @return \Twig\evento.twig
     */
    public function eventsAction(Application $app, $year) {
        if (!$year) {
            $year = date('Y');
        }
        
        $params = array(
            'year' => $year
        );
        
        // List all
        $app['event']->getAllThemActiveByYear($params);
        $list = $app['event']->fetch_all();

        $lista = array();
        foreach ($list as $evento) {
            $date = DateUtil::formatDateMysqlToView($evento['start_date']);
            $partes = explode("/", $date);
            $mes = $partes[1];
            $monthName = DateUtil::getMonthName($mes);
            $lista[$monthName]['month_name'] = $monthName;
            $lista[$monthName][] = $evento;
        }


        return $app['twig']->render('site/eventos.twig', array('list' => $lista, 'year' => $year, 'entity' => false));
    }

    /**
     * Busca de eventos 
     * @param Application $app
     * @param Request $request
     * @return type
     */
     public function searchEventsAction(Application $app, Request $request) {
         $query = $request->query->get('q', false);
         $page = $request->query->get('page', 1);
         $limit = 10; # fixed rows limit

         if ($query) {

             $app['event']->count($query);
             $data = $app['event']->fetch();
             $rows = (int) $data['count'];
             $pages = 1;

             if ($rows > $limit) {

                 $pages = ceil($rows / $limit);
                 $offset = ($page - 1)  * $limit;
                 $app['event']->search($query, $offset, $limit);
                 $list = $app['event']->fetch_all();

             } else {
                 $app['event']->search($query);
                 $list = $app['event']->fetch_all();
             }
         }

         return $app['twig']->render('site/busca-eventos.twig', array(
             'query' => $query,
             'list' => $list,
             'pages' => $pages,
             'page' => $page,
             'rows' => $rows
         ));
     }

    /**
     * Associado
     * 
     * @param \Silex\Application $app, Request $request
     * @return \Twig\associado.twig
     */
    public function associadoAction(Application $app, $page = "beneficios") {
        if ($page == "beneficios") {
            return $app['twig']->render('site/associado.twig');
        } else {
            return $app['twig']->render('site/seja-associado.twig');
        }
    }

    /**
     * Monitoramento
     *
     * @param \Silex\Application $app, Request $request
     * @return \Twig\page.twig
     */
    public function monitoramentoAction(Application $app, $page) {
        switch ($page)
        {
            case 'numeros-do-setor':
                return $app['twig']->render('site/numeros-do-setor.twig', array('page' => 'numeros-do-setor'));
                break;
            case 'numeros-regionais':
                return $app['twig']->render('site/numeros-regionais.twig', array('page' => 'numeros-regionais'));
                break;
            case 'numeros-dos-estados':
                return $app['twig']->render('site/numeros-dos-estados.twig', array('page' => 'numeros-dos-estados'));
                break;
            case 'numeros-nas-capitais':
                return $app['twig']->render('site/numeros-nas-capitais.twig', array('page' => 'numeros-nas-capitais'));
                break;
            case 'evolucao-do-setor':
                return $app['twig']->render('site/evolucao-do-setor.twig', array('page' => 'evolucao-do-setor'));
                break;
            case 'desempenho-da-industria':
                return $app['twig']->render('site/desempenho-da-industria.twig', array('page' => 'desempenho-da-industria'));
                break;
            case 'definicoes-e-convencoes':
                return $app['twig']->render('site/definicoes-e-convencoes.twig', array('page' => 'definicoes-e-convencoes'));
                break;
            default:
                return $app['twig']->render('site/numeros-do-setor.twig', array('page' => 'numeros-do-setor'));
                break;
        }

    }

    /**
     * Guia de Shoppings
     * 
     * @param \Silex\Application $app, Request $request
     * @return \Twig\shopping.twig
     */
    public function guiaShoppingAction(Application $app, $id) {

        if ((strlen($id) == 1) && ($id >= 'A' && $id <= 'Z')) {
            $app['shopping']->getShoppingByAZ($id); // A-Z
            $title = "- {$id}";
        } else if (strlen($id) == 2) { // UF
            $app['shopping']->getShoppingByUF($id);
            $title  = "- {$id}";
        } else {
            $app['shopping']->getShoppingAll();
            $title  = "DE A-Z";
        }

        $shoppings = $app['shopping']->fetch_all();

        $rows = $app['shopping']->rows();
        $app['shopping']->close();

        return $app['twig']->render('site/guia-shopping.twig', array(
            'shoppings' => $shoppings,
            'rows' => $rows,
            'title' => $title,
            'id' => $id
        ));
    }

    /**
     * Info Shopping
     * 
     * @param \Silex\Application $app, Request $request
     * @return \Twig\info-shopping.twig
     */
    public function infoShoppingAction(Application $app, $id) {

        if (is_numeric($id)) {
            $app['shopping']->getShoppingById($id);
            $shopping = $app['shopping']->fetch_all();

            $rows = $app['shopping']->rows();

            // get shopping administrator
            $app['shopping']->getShoppingAdminById($id);
            $extra = $app['shopping']->fetch_all();

            // get shopping enterteiment
            $app['shopping']->getShoppingEntertainmentById($id);
            $entertainment = $app['shopping']->fetch_all();

            $app['shopping']->close();
        }

        return $app['twig']->render('site/info-shopping.twig', array(
            'shopping' => $shopping[0],
            'rows' => $rows, 
            'extra' => $extra[0],
            'entertainment' => $entertainment
        ));
    }

    /**
     * Guia de Fornecedores
     * 
     * @param \Silex\Application $app, Request $request
     * @return \Twig\shopping.twig
     */
    public function guiaSupplierAction(Application $app, $id) {

        if (is_numeric($id)) { // Category
            $app['supplier']->getSupplierByCategory($id);
            $title  = "";
        } else if ((strlen($id) == 1) && ($id >= 'A' && $id <= 'Z')) {
            $app['supplier']->getSupplierByAZ($id); // A-Z
            $title = "- {$id}";
        } else {
            $app['supplier']->getSupplierAll();
            $title  = "DE A-Z";
        }

        // get suppliers
        $suppliers = $app['supplier']->fetch_all();
        $rows = $app['supplier']->rows();
        $app['supplier']->close();

        // get categories
        $app['supplier']->getSupplierCategoryAll();
        $categories = $app['supplier']->fetch_all();
        $app['supplier']->close();

        return $app['twig']->render('site/guia-fornecedor.twig', array(
            'suppliers' => $suppliers,
            'rows' => $rows,
            'title' => $title,
            'id' => $id,
            'categories' => $categories
        ));
    }

    /**
     * Guia de Fornecedores
     * 
     * @param \Silex\Application $app, Request $request
     * @return \Twig\shopping.twig
     */
    public function infoSupplierAction(Application $app, $id) {

        if (is_numeric($id)) {
            $app['supplier']->getSupplierById($id);
            $supplier = $app['supplier']->fetch_all();

            $rows = $app['supplier']->rows();

            // get supplier contacts
            $app['supplier']->getSupplierContactById($id);
            $contacts = $app['supplier']->fetch_all();

            $app['supplier']->close();
        }

        return $app['twig']->render('site/info-fornecedor.twig', array(
            'supplier' => $supplier[0],
            'rows' => $rows, 
            'contacts' => $contacts
        ));
    }

    /**
     * User Administrator
     * 
     * @param \Silex\Application $app, Request $request
     * @return \Twig\admin.twig
     */
    public function adminAction(Application $app, Request $request) {

        $app['newsletter']->getCount();
        $newsletter = $app['newsletter']->fetch();
        $app['newsletter']->close();

        $app['contactus']->getCount();
        $contactus = $app['contactus']->fetch();
        $app['contactus']->close();

        $app['associate']->getCount();
        $associate = $app['associate']->fetch();
        $app['associate']->close();

        $app['subscriber']->getCount();
        $subscriber = $app['subscriber']->fetch();
        $app['subscriber']->close();

        return $app['twig']->render('admin/admin.twig', array(
            'subscriber' => $subscriber,
            'associate' => $associate,
            'contactus' => $contactus,
            'newsletter' => $newsletter,
            'navigate' => 'Dashboard'
        ));
    }

    /**
     * Newsletter Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function newsletterAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        if (!$post['email']) {
            return $this->error($app, 'O E-mail deve ser preenchido.');
        }
        
        // Escape
        $emailParam = $app['newsletter']->escapeString(trim($post['email']));
        
        // WARNING: PHP 5 only
        if (!filter_var($emailParam, FILTER_VALIDATE_EMAIL)) {
            return $this->error($app, 'E-mail inválido, por favor, forneça um endereço de e-mail válido');
        }
        
        // Check if user is registered
        $app['newsletter']->getByEmail($emailParam);
        if($app['newsletter']->rows() > 0){
            return $this->error($app, 'E-mail já cadastrado em nossa base de dados');
        }
        
        if ($app['newsletter']->insert($post['email'], date('Y-m-d H:i:s'))) {
            $result = array(
                'success' => true,
                'message' => 'E-mail registrado com sucesso.'
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Não foi possível registrar o E-mail. Verifique se o mesmo já foi cadastrado.'
            );
        }

        return $app->json($result);
    }

    /**
     * Publicações (store table)
     * 
     * @param \Silex\Application $app, Request $request
     * @return \Twig\publicacoes.twig
     */
    public function storeAction(Application $app, $id) {

        // get books
        $app['store']->getThemActiveByCategory($id);
        $books = $app['store']->fetch_all();
        $app['store']->close();

        // get categories
        $app['store_category']->getAllThemActive();
        $categories = $app['store_category']->fetch_all();
        $app['store_category']->close();

        return $app['twig']->render('site/publicacoes.twig', array(
            'id' => $id, 
            'books' => $books, 
            'categories' => $categories
        ));
    }

    /**
     * Publicações (Envio de Pedido)
     * 
     * @param \Silex\Application $app, Request $request
     * @return \Twig\publicacoes.twig
     */
    public function orderAction(Application $app, Request $request) {

        $post = $request->request->all();

        if(!$post['Items']){

            $result = array(
                'success' => false,
                'message' => 'Nenhuma quantidade inserida.<br>Por favor, informe a quantidade de pelo menos um item.'
            );
            return $app->json($result);
        } else {
            foreach ($post['Items'] as $key => $item) {
                $app['store']->getById($key);
                $store = $app['store']->fetch();
                $post['items'][] = array(
                    "id" => $key,
                    "title" => $store['title'],
                    "number" => $item
                );
            }
            unset($post['Items']);
        }

        // seta os dados por tipo de pessoa para apresentação
        if ($post['TipoPessoa'] == 'Fisica') {
            $typeData = array('Nome' => 'Nome', 'CPF' => 'CPF');
        } else {
            $typeData = array('Nome' => 'Razão Social', 'NomeFantasia' => 'Nome Fantasia');
        }

        // seta os dados em comum para apresentação
        $commonData = array('TipoPessoa' => 'Tipo de Pessoa', 'Email' => 'Email', 'Telefone' => 'Telefone', 
            'EntregaCEP' => 'CEP', 'EntregaEndereco' => 'Endereço', 'EntregaNumero' => 'Número', 
            'EntregaBairro' => 'Bairro', 'EntregaCidade' => 'Cidade', 'EntregaUF' => 'UF');

        // funde os dados dos arrays para seguir a mesma sequência do formulário
        $mergeData = array_merge($typeData, $commonData);

        // dados especiais de validação
        $specialData = array('Email' => 'email');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $mergeData, "- %f<br>\n", $specialData);

        // verifica se os dados são válidos
        if ($valid === true) {

            // SMTP Configuration
            $smtp = $app['setup']->getSMTPById();

            $app['swiftmailer.options'] = array(
                'host' => $smtp['smtp_host'],
                'port' => $smtp['smtp_port'],
                'username' => $smtp['smtp_user'],
                'password' => $smtp['smtp_pass']
            );


            // configura parâmetros para enviar e-mail
            $message = \Swift_Message::newInstance()
                ->setSubject('Portal ABRASCE - Publicações/Pedido ('.$post['Nome'].')')
                ->setFrom(array('biblioteca@portaldoshopping.com.br' => 'Biblioteca ABRASCE'))
                ->setTo(array(
//                    'karina@abrasce.com.br' => $smtp['smtp_name'],
//                    'priscila@abrasce.com.br' => $smtp['smtp_name'],
//                    'fabiola@abrasce.com.br' => $smtp['smtp_name'],
                    'alberto.lima@crmall.com' => $smtp['smtp_name'],
                ))
                ->setBody($app['twig']->render('email/publicacoes-pedido.twig', array('data' => $post)), 'text/html');

            // envia e-mail
            $app['mailer']->send($message, $failures);

            if (!$failures) {

                $result = array(
                    'success' => true,
                    'message' => 'Sua solicitação foi realizada com sucesso.<br><br>Um de nossos consultores entrará em contato para conclusão do pedido.<br><br>Obrigado!',
                    'teste' => $post
                );
            } else {
//
                $result = array(
                    'success' => false,
                    'message' => 'Ocorreu um erro ao enviar o seu pedido.<br>Por favor, tente novamente ou entre em contato conosco.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    /**
     * About us
     * 
     * @param \Silex\Application $app, Request $request
     * @return \Twig\sobre-abrasce.twig
     */
    public function aboutAction(Application $app, $title) {

        $title = empty($title) ? "apresentacao" : $title;
        
        $app['aboutus']->getByTitle($title);
        $about = $app['aboutus']->fetch();
        $app['aboutus']->close();

        $app['aboutus']->getAll("title");
        $titles = $app['aboutus']->fetch_all();
        $app['aboutus']->close();


        return $app['twig']->render('site/sobre-abrasce.twig', array(
            'title' => strtoupper($about['title']),
            'titlesList' => $titles,
            'image' => $about['image'],
            'description' => $about['description']
        ));
    }

    /**
     * Magazines
     *
     * @param \Silex\Application $app, Request $request
     * @return \Twig\revistas.twig
     */
    public function magazineAction(Application $app, $id) {

        if(!$id){
            return $app['twig']->render('errors/404.twig', array());
        }

        $app['magazine']->getById($id);
        $magazine = $app['magazine']->fetch();
        $app['magazine']->close();

        $app['magazine']->getAllOrder("id, publication");
        $publications = $app['magazine']->fetch_all();
        $app['magazine']->close();


        return $app['twig']->render('site/revistas.twig', array(
            'magazine' => $magazine,
            'publication' => strtoupper($magazine['publication']),
            'publicationList' => $publications,
        ));
    }

    /**
     * Contact us
     *
     * @param \Silex\Application $app, Request $request
     * @return \Twig\fale-com-abrasce.twig
     */
    public function contactAction(Application $app, Request $request) {

        return $app['twig']->render('site/fale-com-abrasce.twig', array('fale_abrasce' => true));
    }

    /**
     * Contactus Registration
     *
     * @param \Silex\Application $app
     * @param \Symfony\Request $request
     */
    public function insertContactusAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        $commonData = array('area' => 'Área', 'subject' => 'Assunto', 'name' => 'Nome', 'email' => 'E-mail', 
            'phone' => 'Telefone', 'business' => 'Empresa', 'job' => 'Cargo', 'cep' => 'CEP', 'address' => 'Endereço', 'number' => 'Número', 
            'neighborhood' => 'Bairro', 'city' => 'Cidade', 'state' => 'Estado', 'message' => 'Mensagem');

        // dados especiais de validação
        $specialData = array('email' => 'email');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            if ($app['contactus']->insert(
                $post['area'], 
                $post['subject'], 
                $post['name'], 
                $post['email'], 
                $post['business'], 
                $post['job'], 
                $post['cep'], 
                $post['address'], 
                $post['number'], 
                $post['complement'], 
                $post['neighborhood'], 
                $post['city'], 
                $post['state'], 
                $post['phone'], 
                $post['message'], 
                date('Y-m-d H:i:s')
            )) {
                $result = array(
                    'success' => true,
                    'message' => 'Contato registrado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar o Contato. Verifique se o mesmo já foi cadastrado.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    public function searchAction(Application $app, Request $request) {

        return $app['twig']->render('site/busca.twig', array());
    }

    /**
     * Busca de noticias 
     * @param Application $app
     * @param Request $request
     * @return type
     */
    public function searchNewsAction(Application $app, Request $request) {
        
        $query = $request->query->get('q', false);
        $page = $request->query->get('page', 1);
        $limit = 10; # fixed rows limit
        
        if($query){
            
            $app['feed']->count($query, \App\Enum\FeedTypeEnum::NOTICIA);
            $data = $app['feed']->fetch();
            $rows = (int) $data['count'];
            $pages = 1;
            
            if($rows > $limit){
                
                $pages = ceil($rows / $limit);
                $offset = ($page - 1)  * $limit;
                $app['feed']->search($query, $offset, $limit, \App\Enum\FeedTypeEnum::NOTICIA);
                $list = $app['feed']->fetch_all();
                
            }else{
                $app['feed']->search($query, null, null, \App\Enum\FeedTypeEnum::NOTICIA);
                $list = $app['feed']->fetch_all();
            }
        }
        
        return $app['twig']->render('site/busca-noticias.twig', array(
            'query' => $query, 
            'list' => $list, 
            'pages' => $pages, 
            'page' => $page, 
            'rows' => $rows
        ));
    }

    public function searchNewsAssociateAction(Application $app, Request $request) {

        $query = $request->query->get('q', false);
        $page = $request->query->get('page', 1);
        $limit = 10; # fixed rows limit

        if($query){

            $app['feed']->count($query, \App\Enum\FeedTypeEnum::ASSOCIADO);
            $data = $app['feed']->fetch();
            $rows = (int) $data['count'];
            $pages = 1;

            if($rows > $limit){

                $pages = ceil($rows / $limit);
                $offset = ($page - 1)  * $limit;
                $app['feed']->search($query, $offset, $limit, \App\Enum\FeedTypeEnum::ASSOCIADO);
                $list = $app['feed']->fetch_all();

            }else{
                $app['feed']->search($query, null, null, \App\Enum\FeedTypeEnum::ASSOCIADO);
                $list = $app['feed']->fetch_all();
            }
        }

        return $app['twig']->render('site/busca-noticias-associado.twig', array(
            'query' => $query,
            'list' => $list,
            'pages' => $pages,
            'page' => $page,
            'rows' => $rows
        ));
    }

    /**
     * Associete Registration
     *
     * @param \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function insertAssociateAction(Application $app, Request $request)
    {

        $post = $request->request->all();

        $commonData = array('name' => 'Nome', 'email' => 'E-mail', 
            'phone' => 'Telefone', 'business' => 'Empresa', 'message' => 'Mensagem');

        // dados especiais de validação
        $specialData = array('email' => 'email');

        // validação de dados
        $validator = new \App\Util\Validator();
        $valid = $validator->validateData($post, $commonData, "- %f<br>\n", $specialData);

        if ($valid === true) {

            if ($app['associate']->insert(
                $post['email'], 
                $post['name'], 
                $post['phone'], 
                $post['business'], 
                $post['message'], 
                date('Y-m-d H:i:s')
            )) {
                $result = array(
                    'success' => true,
                    'message' => 'Contato registrado com sucesso.'
                );
            } else {
                $result = array(
                    'success' => false,
                    'message' => 'Não foi possível registrar o Contato. Verifique se o mesmo já foi cadastrado.'
                );
            }

        } else {
            $message = "Os campos:<br><br>{$valid}<br>Devem ser preenchidos.";
            return $this->error($app, $message);
        }

        return $app->json($result);
    }

    public function error($app, $message){

        $result = array(
            'success' => false,
            'message' => $message
        );
        return $app->json($result);
    }

    /**
     * Noticias
     * 
     * @param \Silex\Application $app, Request $request
     * @return \Twig\noticias.twig
     */
    public function newsAction(Application $app, $year, $month) {
        
        if(!$year){
            $year = "0";
        }

        if(!$month){
            $month = "0";
        }
        
        $params = array(
            'month' => $month,
            'year' => $year
        );
        
        // List all
        if (!$year && !$month) {
            $monthName = '';
            $app['feed']->getAll('*', \App\Enum\FeedTypeEnum::NOTICIA);
        } else {
            $monthName = DateUtil::getMonthName($month);
            $app['feed']->getAllByMonth($params, \App\Enum\FeedTypeEnum::NOTICIA);
        }

        $list = $app['feed']->fetch_all();

        return $app['twig']->render('site/noticias.twig', array(
            'list' => $list, 
            'month' => $month, 
            'month_name' => $monthName, 
            'year' => $year, 
            'entity' => false,
            'parans' => $params,
            'navigate' => 'noticias'
        ));
    }

    public function newsAssociateAction(Application $app, $year, $month) {

        if(!$year){
            $year = "0";
        }

        if(!$month){
            $month = "0";
        }

        $params = array(
            'month' => $month,
            'year' => $year
        );

        // List all
        if (!$year && !$month) {
            $monthName = '';
            $app['feed']->getAll('*', \App\Enum\FeedTypeEnum::ASSOCIADO);
        } else {
            $monthName = DateUtil::getMonthName($month);
            $app['feed']->getAllByMonth($params, \App\Enum\FeedTypeEnum::ASSOCIADO);
        }

        $list = $app['feed']->fetch_all();

        return $app['twig']->render('site/noticias-associado.twig', array(
            'list' => $list,
            'month' => $month,
            'month_name' => $monthName,
            'year' => $year,
            'entity' => false,
            'parans' => $params,
            'navigate' => 'noticias'
        ));
    }

    /**
     * Noticias
     * 
     * @param \Silex\Application $app, Request $request
     * @return \Twig\noticias.twig
     */
    public function newsViewAction(Application $app, $id) {

        // List all
        $app['feed']->getById($id);
        $entity = $app['feed']->fetch();

        $timestamp = strtotime($entity['date']);
        $year   = date('Y', $timestamp);
        $month  = date('m', $timestamp);
        $monthName = DateUtil::getMonthName($month);

        return $app['twig']->render('site/noticias.twig', array(
            'month' => $month,
            'month_name' => $monthName,
            'year' => $year,
            'entity' => $entity,
            'navigate' => 'noticias'
        ));
    }

    public function newsAssociateViewAction(Application $app, $id) {

        // List all
        $app['feed']->getById($id);
        $entity = $app['feed']->fetch();

        $timestamp = strtotime($entity['date']);
        $year   = date('Y', $timestamp);
        $month  = date('m', $timestamp);
        $monthName = DateUtil::getMonthName($month);

        return $app['twig']->render('site/noticias-associado.twig', array(
            'month' => $month,
            'month_name' => $monthName,
            'year' => $year,
            'entity' => $entity,
            'navigate' => 'noticias'
        ));
    }

    /**
 * Premio Abrasce
 *
 * @param \Silex\Application $app, Request $request
 * @return \Twig\premio-abrasce.twig
 */
    public function premioAbrasceAction(Application $app, $id, $idAward) {

        $shoppings = $app['shopping']->getShoppingAll();

        $app['aa_event']->getAllOrderByYear();
        $events = $app['aa_event']->fetch_all();

        if(!$id){
            $app['aa_event']->getLast();
        }else{
            $app['aa_event']->getById($id);
        }

        $event = $app['aa_event']->fetch();
        $app['aa_event']->close();

        $awards = $app['aa_award_event']->getAllActive($event['id']);

        $award = null;
        if($idAward){
            $award = $app['aa_award_event']->getById($idAward,$event['id']);
        }

        $app['aa_award_event']->close();

        return $app['twig']->render('site/premio-abrasce.twig', array(
            'events'    => $events,
            'event'     => $event,
            'awards'    => $awards,
            'award'     => $award,
            'shoppings' => $shoppings,
            'id_type'   => 12
        ));
    }

    /**
     * Inscrição Premio Abrasce
     *
     * @param \Silex\Application $app, Request $request
     * @return \Twig\premio-abrasce.twig
     */
    public function premioAbrasceInscricaoAction(Application $app, $id, $idAward) {

        $states = FormUtil::getStatesArray();

        $app['shopping']->getShoppingAll();
        $shoppings = $app['shopping']->fetch_all();
        $app['shopping']->close();

        $app['aa_event']->getAllOrderByYear();
        $events = $app['aa_event']->fetch_all();

        if(!$id){
            $app['aa_event']->getLast();
        }else{
            $app['aa_event']->getById($id);
        }

        $event = $app['aa_event']->fetch();
        $app['aa_event']->close();

        $awards = $app['aa_award_event']->getAll($event['id']);

        $award = null;
        if($idAward){
            $award = $app['aa_award_event']->getById($idAward,$event['id']);
        }

        $app['aa_award_event']->close();

        return $app['twig']->render('site/premio-abrasce-inscricao.twig', array(
            'states'    => $states,
            'shoppings' => $shoppings,
            'events'    => $events,
            'event'     => $event,
            'awards'    => $awards,
            'award'     => $award,
            'id_type'   => 12
        ));
    }

    /**
     * POST Inscrição Premio Abrasce
     *
     * @param \Silex\Application $app, Request $request
     * @return \Twig\premio-abrasce.twig
     */
    public function premioAbrasceInscricaoSaveAction(Application $app, Request $request) {

        $post = $request->request->all();

        // Get event
        $app['aa_event']->getById($post['fk_event']);
        $event = $app['aa_event']->fetch();

        $award = $app['aa_award']->getById($post['fk_award']);
        $awardEvent = $app['aa_award_event']->getById($post['fk_award'],$post['fk_event']);

        // Get Shopping
        $app['shopping']->getShoppingById($post['fk_shopping']);
        $shopping = $app['shopping']->fetch();

        // Check if billing address is different from shopping address
        if($post['billing_info'] == 'shopping') {
            $post['billing_document_number'] = $shopping['cnpj'];
            $post['billing_name'] = $shopping['fantasia'];
            $post['billing_zip'] = $post['shopping_zip'];
            $post['billing_state'] = $post['shopping_state'];
            $post['billing_city'] = $post['shopping_city'];
            $post['billing_address'] = $post['shopping_address'];
        }

        // Invoice data
        $dueDate = new \DateTime('now', new \DateTimeZone('America/Sao_Paulo'));
        $dueDate->modify("+{$awardEvent['billing_days_to_due']} days");
        $post['invoice_due_date'] = $dueDate->format('Y-m-d H:i:s');

        if($app['aa_registration']->getByShopping($post['fk_shopping'])){
            $post['invoice_value'] = ($awardEvent['registration_price'] / 2);
        } else {
            $post['invoice_value'] = $awardEvent['registration_price'];
        }

        // Set params
        $post['status'] = 'pending';
        $post['created_at'] = date('Y-m-d H:i:s');

        $regID = $app['aa_registration']->insert($post);

        $updateData = array();
        $updateData['invoice_number'] = '25/' . str_pad($regID, 11, '0', STR_PAD_LEFT) . '-0';
        $updateData['registration_number'] = $event['year'] . $award['code'] . str_pad($regID,5,'0',STR_PAD_LEFT);

        $app['aa_registration']->update($updateData, $regID);

        if ($post['action'] == 'continue') {
            // Auth registration edit and redirect to edit page

            $app['session']->set('projectid', $regID);

            return $app->redirect($app['url_generator']->generate('premio-abrasce-inscricao-edit'));
        }

        $mailer = array(
            'email' => false
        );

        $registration = $app['aa_registration']->getById($regID);

        $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/site/boleto-premio/";
        $boleto = $url . hash('sha512', $regID, false) . '/' . $regID . '/1';

        // Envia boleto e dados da inscrição
        if ($regID > 0) {
            $mailer = $this->sendEmailSubscription($app, $regID, $boleto);
        }

        return $app['twig']->render('site/premio-abrasce-inscricao-sucesso.twig', array(
            'event' => $event,
            'registration' => $registration,
            'link_boleto' => $boleto,
            'email' => $mailer['email']
        ));
    }

    public function premioAbrasceInscricaoEditAction(Application $app, Request $request){

        $regID  = $app['session']->get('projectid');
        $reg = $app['aa_registration']->getById($regID);
        $fields = $app['aa_award_event_field']->getAll($reg['fk_award'], $reg['fk_event'], 'order', 'ASC');
        $award_event = $app['aa_award_event']->getById($reg['fk_award'], $reg['fk_event']);
        $values =  $app['aa_award_event_field_registration']->getValues($regID);

        if($values && count($values) > 0) {
            foreach($fields as &$obj){
                $obj['value'] = array_key_exists($obj['id'], $values) ? $values[ $obj['id'] ] : null;
            }
        }

        return $app['twig']->render('site/premio-abrasce-inscricao-projeto.twig', array(
            'idRegistration'        => $regID,
            'reg_entity'            => $reg,
            'award_event'           => $award_event,
            'fields'                => $fields,
            'values'                => $values
        ));
    }

    public function premioAbrasceInscricaoEditSaveAction(Application $app, Request $request){

        $post = $request->request->all();
        $files = $request->files->all();

        $regID = $post['fk_registration'];

        foreach($post as $key => $value){
            if($key != 'fk_registration' && is_numeric($key)){
                $app['aa_award_event_field_registration']->setValue($key, $regID, $value);
            }
        }

        $reg = $app['aa_registration']->getById($regID);
        $award = $app['aa_award_event']->getById($reg['fk_award'], $reg['fk_event']);

        // Save files
        if($files && count($files) > 0) {

            foreach($files as $key => $file) {

                if($file){
                    $val = $app['aa_award_event_field_registration']->getById($key, $regID);
                    $path = PATH_PUBLIC . "/uploads/premio-abrasce/{$award['fk_event']}/{$award['fk_award']}/{$regID}/";

                    if($val && $val['value']){
                        $currentFile = $path . $val['value'];
                        if(file_exists($val)){
                            unlink($currentFile);
                        }
                    }

                    if(!file_exists($path)){
                        mkdir($path, 0777, true);
                    }

                    $fileNameOriginal = $file->getClientOriginalName();
                    $extension = strrchr($fileNameOriginal, '.');
                    $fileName = 'file_'. md5(microtime()) . strtolower($extension);

                    $file->move($path, $fileName);

                    $app['aa_award_event_field_registration']->setValue($key, $regID, $fileName);
                }
            }
        }

        // Get event
        $app['aa_event']->getById($reg['fk_event']);
        $event = $app['aa_event']->fetch();

        $mailer = array(
            'email' => false
        );

        $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/site/boleto-premio/";
        $boleto = $url . hash('sha512', $regID, false) . '/' . $regID . '/1';

        // Envia boleto e dados da inscrição
        if ($regID > 0) {
            $mailer = $this->sendEmailSubscription($app, $regID, $boleto);
        }

        $registration = $app['aa_registration']->getById($regID);

        $link_boleto = hash('sha512', $regID, false) . '/' . $regID;

        return $app['twig']->render('site/premio-abrasce-inscricao-sucesso.twig', array(
            'event' => $event,
            'registration' => $registration,
            'link_boleto' => $boleto,
            'email' => $mailer['email']
        ));
    }

    public function premioAbrasceAuthAction(Application $app, Request $request){

        try{

            $post = $request->request->all();

            // if(!$post['fk_shopping'] || !$post['registration_number']) {
            if(!$post['fk_shopping'] || !$post['registration_number'] || !$post['fk_award'] || !$post['fk_event']) {
                throw new \Exception('Dados inválido, por favor, forneça os dados corretamente');
            }

            $entity = $app['aa_registration']->getByRegistration($post['fk_award'],$post['fk_event'],$post['fk_shopping'],$post['registration_number']);
            if(!$entity){
                throw new \Exception('Inscrição não encontrada');
            }

            $app['session']->set('projectid', $entity['id']);

            $responseData['success'] = true;

        }catch(\Exception $ex){
            $responseData['success'] = false;
            $responseData['message'] = $ex->getMessage();
        }

        return $app->json($responseData);

    }

    public function getShoppingAddressAction(Application $app, Request $request){
        try{
            $post = $request->request->all();
            $app['shopping']->getShoppingById($post['fk_shopping']);
            $entity = $app['shopping']->fetch();

            if(!$entity){
                throw new \Exception('Shopping não encontrado');
            }

            $endereco = $entity['logradouro'];
            if($entity['numero']){
                if(strpos($entity['numero'], ',')){
                    $endereco .= $entity['numero'];
                }else{
                    $endereco .= ', ' . $entity['numero'];
                }

            }

            $data = array(
                'cnpj' => $entity['cnpj'],
                'fantasia' => $entity['fantasia'],
                'endereco' => $endereco,
                'cidade' => $entity['localidade'],
                'estado' => $entity['estado'],
                'cep' => $entity['cep']
            );

            $responseData['success'] = true;
            $responseData['data'] = $data;

        }catch(\Exception $ex){
            $responseData['success'] = false;
            $responseData['message'] = $ex->getMessage();
        }

        return $app->json($responseData);
    }

    /**
     * BoletoAward action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    public function boletoAwardAction(Application $app, $hash, $id, $pdf = 0) {

        if (hash('sha512', $id, false) != $hash) {
            return $app->abort(Response::HTTP_NOT_FOUND);
        }

        $boleto = $app['setup']->getDataBoletoById();

        if (!$boleto) {
            return $app->abort(Response::HTTP_BAD_REQUEST, 'Dados do Cedente não encontrado.');
        }

        // Pega os dados da cobrança
        $reg = $app['aa_registration']->getBillingById($id);

        if (!$reg) {
            return $app->abort(Response::HTTP_BAD_REQUEST, 'Dados de Cobrança não encontrado.');
        }

        // Pega o período de vencimento e o valor da inscrição
        $event = $app['aa_award_event']->getOrderById($reg['fk_award'], $reg['fk_event']);

        if (!$event) {
            return $app->abort(Response::HTTP_BAD_REQUEST, 'Data de Vencimento não encontrada.');
        }

        // Data de vencimento
        $dueDate = new \DateTime('now');

        // Modifica a data de vencimento do boleto
        if ($event['billing_days_to_due'] > 0) {
            $dueDate->modify("+{$event['billing_days_to_due']} days");
        } else {
            $dueDate->modify('+10 days');
        }

        $sacado = new Agente($reg['billing_name'], $reg['billing_document_number'], $reg['billing_address'], $reg['billing_zip'], $reg['billing_city'], $reg['billing_state']);
        $cedente = new Agente($boleto['cedente_name'], $boleto['cedente_cnpj'], $boleto['cedente_address'], $boleto['cedente_zip'], $boleto['cedente_city'], $boleto['cedente_state']);

        $boleto = new Bradesco(array(
            'dataVencimento' => $dueDate,
            'valor' => $event['registration_price'],
            'sequencial' => $id, // Id sequencial do nosso número
            'sacado' => $sacado,
            'cedente' => $cedente,
            'agencia' => $boleto['cedente_agencia'], // Até 4 dígitos
            'conta' =>  $boleto['cedente_conta'], // Até 8 dígitos
            'convenio' => 1234, // 4, 6 ou 7 dígitos
            'carteira' => $boleto['cedente_carteira'],
            'contaDv' => $boleto['cedente_conta_dv'],
            'agenciaDv' => $boleto['cedente_agencia_dv'],
            'descricaoDemonstrativo' => array( // Até 5
                $boleto['cedente_label1'],
                $boleto['cedente_label2'],
                $boleto['cedente_label3']
            ),
            'instrucoes' => array( // Até 8
                $boleto['cedente_label4'],
                $boleto['cedente_label5'],
                $boleto['cedente_label6']
            )
        ));

        if ($pdf) {

            $options = array(
                'page-size' => 'A4',
                'margin-left' => '12.5',
                'margin-right' => '12.5',
                'password' => '123'
            );

            $knpdf = new Pdf(null, $options);
            $knpdf->setBinary('/usr/local/bin/wkhtmltopdf');

            // Expressão usada para remover as instruções de impressão
            // $html = preg_replace('!<div\s+class="noprint info">.*?</div>!is', '', $boleto->getOutput());

            $stream = $knpdf->getOutputFromHtml($boleto->getOutput());

            return new Response($stream, 200, array('Content-Type' => 'application/pdf'));
        }

        return new Response($boleto->getOutput());
    }

    /**
     * BoletoEvent action
     *
     * @param \Silex\Application $app
     * @return mixed
     */
    /*
    public function boletoEventAction(Application $app, $hash, $id, $pdf = 0) {
        try {
            if (hash('sha512', $id, false) != $hash) {
                return $app->abort(Response::HTTP_NOT_FOUND);
            }

            $boleto = $app['setup']->getDataBoletoById();

            if (!$boleto) {
                return $app->abort(Response::HTTP_BAD_REQUEST, 'Dados do Cedente não encontrado.');
            }

            // Pega os dados da cobrança, período de vencimento e o valor da inscrição
            $reg = $app['event_registration']->getBillingById($id);

            if (!$reg) {
                return $app->abort(Response::HTTP_BAD_REQUEST, 'Dados de Cobrança e Vencimento não encontrado.');
            }

            $sacado = new Agente($reg['billing_name'], $reg['billing_document'], $reg['billing_address'], $reg['billing_zip'], $reg['billing_city'], $reg['billing_state']);
            $cedente = new Agente($boleto['cedente_name'], $boleto['cedente_cnpj'], $boleto['cedente_address'], $boleto['cedente_zip'], $boleto['cedente_city'], $boleto['cedente_state']);

            $due_date = new \DateTime($reg['invoice_due_date']);

            $boleto = new Bradesco(array(
                'dataVencimento' => $due_date,
                'valor' => $reg['invoice_value'],
                'sequencial' => $id, // Id sequencial do nosso número
                'sacado' => $sacado,
                'cedente' => $cedente,
                'agencia' => $boleto['cedente_agencia'], // Até 4 dígitos
                'conta' => $boleto['cedente_conta'], // Até 8 dígitos
                'convenio' => 1234, // 4, 6 ou 7 dígitos
                'carteira' => $boleto['cedente_carteira'],
                'contaDv' => $boleto['cedente_conta_dv'],
                'agenciaDv' => $boleto['cedente_agencia_dv'],
                'descricaoDemonstrativo' => array( // Até 5
                    $boleto['cedente_label1'],
                    $boleto['cedente_label2'],
                    $boleto['cedente_label3']
                ),
                'instrucoes' => array( // Até 8
                    $boleto['cedente_label4'],
                    $boleto['cedente_label5'],
                    $boleto['cedente_label6']
                )
            ));

            if ($pdf) {

                $options = array(
                    'page-size' => 'A4',
                    'margin-left' => '12.5',
                    'margin-right' => '12.5',
                    'password' => '123'
                );

                $knpdf = new Pdf(null, $options);
                $knpdf->setBinary('/usr/local/bin/wkhtmltopdf');

                // Expressão usada para remover as instruções de impressão
                // $html = preg_replace('!<div\s+class="noprint info">.*?</div>!is', '', $boleto->getOutput());

                $stream = $knpdf->getOutputFromHtml($boleto->getOutput());

                return new Response($stream, 200, array('Content-Type' => 'application/pdf'));
            }

            return new Response($boleto->getOutput());
        }catch (\Exception $e){
            var_dump($e->getMessage());
            exit;
        }
    }
    */

    /**
     * Send E-mail Subscription
     * @param $id
     * @return array
     */
    public function sendEmailSubscription($app, $id, $boleto) {

        // Pega os dados da cobrança
        $reg = $app['aa_registration']->getBillingById($id);

        if (!$reg) {
            return $app->abort(Response::HTTP_BAD_REQUEST, 'Dados de Cobrança não encontrado.');
        }

        // Pega o período de vencimento e o valor da inscrição
        $event = $app['aa_award_event']->getOrderById($reg['fk_award'], $reg['fk_event']);

        if (!$event) {
            return $app->abort(Response::HTTP_BAD_REQUEST, 'Evento não encontrado.');
        }

        // SMTP Configuration
        $smtp = $app['setup']->getSMTPById();

        $app['swiftmailer.options'] = array(
            'host' => $smtp['smtp_host'],
            'port' => $smtp['smtp_port'],
            'username' => $smtp['smtp_user'],
            'password' => $smtp['smtp_pass']
        );

        // Teste de envio usando nossos servidores
//        $transport = \Swift_SmtpTransport::newInstance('smtp.crmall.com', 587, 'tls')
//            ->setUsername('xxx@crmall.com')
//            ->setPassword('d3ve2014');

        $transport = \Swift_SmtpTransport::newInstance($smtp['smtp_host'], $smtp['smtp_port'], 'tls')
            ->setUsername($smtp['smtp_user'])
            ->setPassword($smtp['smtp_pass']);

        $mailer = \Swift_Mailer::newInstance($transport);

        // configura parâmetros para enviar e-mail
        $message = \Swift_Message::newInstance()
            ->setSubject('Portal ABRASCE - Inscrição Prêmio ABRASCE')
            ->setFrom(array($smtp['smtp_email']))
            ->setTo(array($reg['responsible_email']))
            ->setBody($app['twig']->render('email/inscricao-premio-abrasce.twig', array('event' => $event, 'registration' => $reg, 'boleto' => $boleto)), 'text/html');

//        $swiftAttachment = \Swift_Attachment::fromPath($pdfile);
//        $message->attach($swiftAttachment);

        // envia e-mail
        if ($mailer->send($message) == 1) {
            $result = array(
                'success' => true,
                'message' => 'Sua inscrição foi efetivada com sucesso!',
                'email' => $reg['responsible_email']
            );
        } else {
            $result = array(
                'success' => false,
                'message' => 'Ocorreu um erro ao enviar e-mail da sua inscrição.<br><br>Por favor, tente novamente ou entre em contato conosco.',
                'email' => false
            );
        }

        return $result;
    }

    /**
     * Check if cupom exists
     * @param $cupom
     * @return bool
     */
    public function getCouponAction(Application $app,Request $request, $fk_event){
        $post = $request->request->all();
        $data = $app['event_discount_coupon']->getById($post['id']);

        if($data['fk_event']!=$fk_event){
            return $app->json(array(
                'success'=>false,
                'error' => 'Cupom inválido'
            ));
        }
        if($data['used_number']>=$data['maximum_number']){
            return $app->json(array(
                'success'=>false,
                'error' => 'Esse cupom não possui mais descontos'
            ));
        }
        return $app->json(array(
            'success'=>true,
            'error' => 'Cupom válido'
        ));
    }

}