<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => array('cache' => __DIR__ . '/cache/compilation_cache')
));

$app['twig.loader.filesystem']->addPath(  __DIR__ . '/views/admin',"admin");
$app['twig.loader.filesystem']->addPath(  __DIR__ . '/views/blog',"blog");


// service providers

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->register(new Silex\Provider\SessionServiceProvider()); //SessionServiceProvider

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'dbs.options' => array(
        'mysql' => array(
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'dbname' => 'silex',
            'user' => 'root',
            'password' => '',
            'charset' => 'utf8',
        )
    ),
));

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'admin' => array(
            'pattern' => '^/admin',
           // 'anonymous' => true,
           // 'http' =>true,
           'form' => array('login_path' => '/login', 'check_path' => '/admin/login_check'),
           'logout' => array('logout_path' => '/admin/logout'),
            /*'users' => array(
                // raw password is foo
                'admin' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
            ),*/
            'users' =>$app->share(function() use ($app) {
                //var_dump($app['db']['mysql']);
                        return new Web\User\UserProvider($app['dbs']['mysql']);
            })
        ),
                  
    ),                    
'security.access_rules' => array(
    array('^/admin', 'ROLE_ADMIN', 'http'),
    array('^/fb', 'ROLE_FB'),
)

));

$app->register(new Silex\Provider\ServiceControllerServiceProvider());


//$app['twig']->clearCacheFiles();
//

$app->get('/login', function(Request $request) use ($app) {
            return $app['twig']->render('@admin/login.twig', array(
                        'error' => $app['security.last_error']($request),
                        'last_username' => $app['session']->get('_security.last_username'),
                    ));
        })->bind("admin_login");
        
 


// controller admin
$app->mount('/admin', include 'admin.php');



$app['debug'] = true;



$app->get('/', function () use ($app) {
            $blogPosts = $app['dbs']['mysql']->fetchAll('SELECT * FROM blog');
            return $app['twig']->render('@blog/index.twig', array('posts' => $blogPosts));
        });

$app->boot();
$app->run();


