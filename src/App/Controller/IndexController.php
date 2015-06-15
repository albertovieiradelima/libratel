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
        $indexController->get('/site/fale-com-abrasce', array($this, 'contactAction'))->bind('fale-conosco');
        $indexController->get('/site/sobre-a-abrasce/{title}', array($this, 'aboutAction'))->assert('title', '.*');
        $indexController->get('/site/revistas/{id}', array($this, 'magazineAction'))->assert('id', '.*');
        $indexController->get('/site/associado/{page}', array($this, 'associadoAction'))->value('page');
        $indexController->post('/site/get-shopping-address', array($this, 'getShoppingAddressAction'))->bind('get-shopping-address-action');
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
        $indexController->get('/site/monitoramento/{page}', array($this, 'monitoramentoAction'))->value('page')->bind('site-monitoramento');

        return $indexController;
    }

    /**
     * Index
     * 
     * @param \Silex\Application $app, Request $request
     */
    public function indexAction(Application $app, Request $request) {

        //news
        $app['feed']->getHomeFeeds(\App\Enum\FeedTypeEnum::NOTICIA);
        $news = $app['feed']->fetch();
        $news['description'] = substr($news['description'],0,200).'...';
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
            'news' => $news,
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

}