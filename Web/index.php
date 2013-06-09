<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Provider\FormServiceProvider;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;


$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => array('cache' => __DIR__ . '/cache/compilation_cache'),
));
$app['twig.loader.filesystem']->addPath(__DIR__ . '/views');
$app->get('/', function(Request $request) use ($app) {
            return $app['twig']->render('index.twig');
 });



$app['debug'] = true;



$app->boot();
$app->run();


