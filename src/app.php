<?php

use Silex\Application;
use Silex\Provider;
use Symfony\Component\HttpFoundation\Response;

// Google Analytics
// conta: analytics@portaldoshopping.com.br
// senha email: an4tps
// senha analyticts: abrasce!23

// Cache Service Provider
$app->register(new Provider\HttpCacheServiceProvider());

// Session Service Provider
$app->register(new Provider\SessionServiceProvider());

// Validator Service Provider
$app->register(new Provider\ValidatorServiceProvider());

// Shorthands for paths & urls
$app->register(new Provider\UrlGeneratorServiceProvider());

// Send Mail
$app->register(new Provider\SwiftmailerServiceProvider(), array(
    'swiftmailer.options' => array(
        'transport' => 'gmail',
        'encryption' => 'ssl',
        'auth_mode' => 'login'
    )
));

// Log definition
$app->register(new Provider\MonologServiceProvider(), array(
    'monolog.logfile' => PATH_LOG . '/app.log',
    'monolog.name'  => 'app',
    'monolog.level'   => 300 // = Logger::WARNING
));

// Template definition
$app->register(new Provider\TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => isset($app['twig.options.cache']) ? $app['twig.options.cache'] : false,
        'strict_variables' => true
    ),
    'twig.path' => array(PATH_VIEWS),
    'twig.options' => array('debug' => $app['debug'])
));

// $app['twig']->getExtension('core')->setTimezone('America/Sao_Paulo');
$app['twig']->addExtension(new Twig_Extensions_Extension_I18n());
$app['twig']->addExtension(new Twig_Extensions_Extension_Intl());
$app['twig']->addExtension(new TwigBase64\Base64Extension(PATH_PUBLIC));

// Extending Twig
$app['twig'] = $app->share($app->extend("twig", function (\Twig_Environment $twig, Silex\Application $app) {

    $twig->addExtension(new App\Util\TwigMask($app));
    $twig->addExtension(new App\Util\TwigUtil($app));
    return $twig;
}));

// MySQLi provider
$app->register(new App\Provider\MySQLi\MySQLiServiceProvider(), array(
    'mysqli.options' => array(
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => 'rigole',
        'database' => 'libratel',
        'charset'  => 'utf8'
    )
));

// default database
$app['db'] = $app['mysqli'];

// Security definition
$app->register(new Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'login' => array(
            'pattern' => '^/admin(/login|/logout|/nova-senha.*$|/send-redefine.*$|/cadastro|/check-email.*$)$',
        ),
        'admin' => array(
            'pattern' => '^/admin.*$',
            'anonymous' => false,
            'form' => array(
                'require_previous_session' => false,
                'login_path' => '/admin/login',
                'check_path' => '/admin/login_check',
                'username_parameter' => 'username',
                'password_parameter' => 'password',
            ),
            'logout' => array(
                'logout_path' => '/admin/logout',
            ),
            'users' => $app->share(function() use ($app) {

                return new App\Provider\UserProvider($app['mysqli']);
            }),
        ),
    ),
));

// Custom Authentication
$app['security.authentication.success_handler.admin'] = $app->share(function() use ($app) {
    // success authentication
    return new App\Service\Authentication\CustomAuthenticationSuccessHandler($app['security.http_utils'], array(), $app);
});

$app['security.authentication.failure_handler.admin'] = $app->share(function() use ($app) {
    // error authentication
    return new App\Service\Authentication\CustomAuthenticationFailureHandler($app);
});

// Share Models

$app['user'] = $app->share(function() use ($app) {
    return new App\Model\UserModel($app['db']);
});

$app['user_group'] = $app->share(function() use ($app) {
    return new App\Model\UserGroupModel($app['db']);
});

$app['user_group_file_category'] = $app->share(function() use ($app) {
    return new App\Model\UserGroupFileCategoryModel($app['db']);
});

$app['course'] = $app->share(function() use ($app) {
    return new App\Model\EventModel($app['db']);
});

$app['event'] = $app->share(function() use ($app) {
    return new App\Model\EventModel($app['db']);
});

$app['event_charge_period'] = $app->share(function() use ($app) {
    return new App\Model\EventChargePeriodModel($app['db']);
});

$app['event_discount_coupon'] = $app->share(function() use ($app) {
    return new App\Model\EventDiscountCouponModel($app['db']);
});

$app['event_registration'] = $app->share(function() use ($app) {
    return new App\Model\EventRegistrationModel($app['db']);
});

$app['event_registration_participants'] = $app->share(function() use ($app) {
    return new App\Model\EventRegistrationParticipantsModel($app['db']);
});

$app['feed'] = $app->share(function() use ($app) {
    return new App\Model\FeedModel($app['db']);
});

$app['space_partner'] = $app->share(function() use ($app) {
    return new App\Model\FeedModel($app['db']);
});

$app['associate'] = $app->share(function() use ($app) {
    return new App\Model\AssociateModel($app['db']);
});

$app['newsletter'] = $app->share(function() use ($app) {
    return new App\Model\NewsletterModel($app['db']);
});

$app['aboutus'] = $app->share(function() use ($app) {
    return new App\Model\AboutusModel($app['db']);
});

$app['contactus'] = $app->share(function() use ($app) {
    return new App\Model\ContactusModel($app['db']);
});

$app['store'] = $app->share(function() use ($app) {
    return new App\Model\StoreModel($app['db']);
});

$app['store_category'] = $app->share(function() use ($app) {
    return new App\Model\StoreCategoryModel($app['db']);
});

$app['magazine'] = $app->share(function() use ($app) {
    return new App\Model\MagazineModel($app['db']);
});

$app['client'] = $app->share(function() use ($app) {
    return new App\Model\ClientModel($app['db']);
});

$app['banner'] = $app->share(function() use ($app) {
    return new App\Model\BannerModel($app['db']);
});

$app['subscriber'] = $app->share(function() use ($app) {
    return new App\Model\SubscriberModel($app['db']);
});

$app['inauguration'] = $app->share(function() use ($app) {
    return new App\Model\InaugurationModel($app['db']);
});

$app['inauguration_category'] = $app->share(function() use ($app) {
    return new App\Model\InaugurationCategoryModel($app['db']);
});

$app['shopping'] = $app->share(function() use ($app) {
    return new App\Model\ShoppingModel($app['db']);
});

$app['supplier'] = $app->share(function() use ($app) {
    return new App\Model\SupplierModel($app['db']);
});

$app['aa_award'] = $app->share(function() use ($app) {
    return new App\Model\AbrasceAward\AwardModel($app['db']);
});

$app['aa_award_field'] = $app->share(function() use ($app) {
    return new App\Model\AbrasceAward\AwardFieldModel($app['db']);
});

$app['aa_award_event'] = $app->share(function() use ($app) {
    return new App\Model\AbrasceAward\AwardEventModel($app['db']);
});

$app['aa_award_event_field'] = $app->share(function() use ($app) {
    return new App\Model\AbrasceAward\AwardEventFieldModel($app['db']);
});

$app['aa_award_event_field_registration'] = $app->share(function() use ($app) {
    return new \App\Model\AbrasceAward\AwardEventFieldRegistrationModel($app['db']);
});

$app['aa_event'] = $app->share(function() use ($app) {
    return new App\Model\AbrasceAward\EventModel($app['db']);
});

$app['aa_sponsor'] = $app->share(function() use ($app) {
    return new App\Model\AbrasceAward\SponsorModel($app['db']);
});

$app['aa_sponsor_category'] = $app->share(function() use ($app) {
    return new App\Model\AbrasceAward\SponsorCategoryModel($app['db']);
});

$app['aa_event_sponsor'] = $app->share(function() use ($app) {
    return new App\Model\AbrasceAward\EventSponsorModel($app['db']);
});

$app['aa_registration'] = $app->share(function() use ($app) {
    return new App\Model\AbrasceAward\RegistrationModel($app['db']);
});

$app['setup'] = $app->share(function() use ($app) {
    return new App\Model\SetupModel($app['db']);
});

$app['file_category'] = $app->share(function() use ($app) {
    return new App\Model\RestrictedArea\FileCategoryModel($app['db']);
});

// User Authentication
$app['user_auth'] = $app->share(function() use ($app) {
    $app['user']->getByUsername($app['security']->getToken()->getUser()->getUsername());
    return $app['user']->fetch();
});

// Global error exception
$app->error(function (\Exception $e, $code) use ($app) {

    if ($app['debug']) {
        return;
    }

    // 404.twig, or 40x.twig, or 4xx.twig, or error.twig
    $templates = array(
        'errors/' . $code . '.twig',
        'errors/' . substr($code, 0, 2) . 'x.twig',
        'errors/' . substr($code, 0, 1) . 'xx.twig',
        'errors/error.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code, 'error' => $e->getMessage())), $code);
});

// Register Console provider
$app->register(new \Knp\Provider\ConsoleServiceProvider(), array(
    'console.name'              => 'ConsoleAbrasceApplication',
    'console.version'           => '1.0.0',
    'console.project_directory' => PATH_ROOT
));
