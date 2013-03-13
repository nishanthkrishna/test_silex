<?php

use Web\Form as FormTable;
use Symfony\Component\HttpFoundation\Request;

$admin = $app['controllers_factory'];


$admin->get('/settings', function(Silex\Application $app) {
            return $app['twig']->render('settings.twig');
        })->bind("settings");
        
$admin->match('/content/{table}/add', function (Request $request, Silex\Application $app, $table) {
            $form = $app['form.factory']->create(new FormTable\TableType($app, $table));

            if ('POST' == $request->getMethod()) {
                $form->bind($request);
                if ($form->isValid()) {
                    $data = $form->getData();
                    $app['db']->insert($table, $data);
                    return $app->redirect($app['url_generator']->generate('admin_table_content', array("table" => str_replace("content_", '', $table))));
                }
            }

            return $app['twig']->render('table_form.twig', array('form' => $form->createView()));
        })->convert('table', function ($table) {
            return "content_" . $table;
        });
        

$admin->get('/content/{table}', function (Request $request, Silex\Application $app, $table) {
            $table_content = $app['db']->fetchAll("SELECT * FROM $table");
            return $app['twig']->render('table_content.twig', array('contents' => $table_content));
        })->convert('table', function ($table) {
            return "content_" . $table;
        })->bind('admin_table_content');



return $admin;


