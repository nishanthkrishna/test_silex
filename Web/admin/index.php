<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Provider\FormServiceProvider;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => array('cache' => __DIR__ . '/cache/compilation_cache'),
));

$app['twig.loader.filesystem']->addPath(__DIR__ . '/views');


$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
                    $twig->addGlobal('pi', 3.14);
                    return $twig;
                }));



// service providers
$app->register(new FormServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.messages' => array(),
));

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->register(new Silex\Provider\SessionServiceProvider()); //SessionServiceProvider

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'host' => 'localhost',
        'dbname' => 'silex',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8',
    )
));

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'admin' => array(
            'pattern' => '^.*$',
            'anonymous' => true,
            'form' => array('login_path' => '/', 'check_path' => 'login_check'),
            'logout' => array('logout_path' => '/logout'),
            'users' => $app->share(function() use ($app) {
                        //var_dump($app['db']['mysql']);
                        return new Web\User\UserProvider($app['db']);
                    })
        ),
    ),
    'security.access_rules' => array(
        array('^/.+$', 'ROLE_ADMIN', 'http')
    )
));

$app->register(new Silex\Provider\ServiceControllerServiceProvider());




//$app['twig']->clearCacheFiles();
//

$app->get('/login', function(Request $request) use ($app) {
            return $app['twig']->render('login.twig', array(
                        'error' => $app['security.last_error']($request),
                        'last_username' => $app['session']->get('_security.last_username'),
                    ));
        })->bind("admin_login");




// controller admin
$app->mount('/', include 'admin.php');

$app->get('/', function(Request $request) use ($app) {
            return $app['twig']->render('login.twig', array(
                        'error' => $app['security.last_error']($request),
                        'last_username' => $app['session']->get('_security.last_username'),
                    ));
        });



$app['debug'] = true;



$app->boot();
$app->run();


