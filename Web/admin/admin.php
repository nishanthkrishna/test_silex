<?php

use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\View\TwitterBootstrapView;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
                    $contentTypes = new SilexF\Admin\Page\ContentTypes($app['db']);
                    $twig->addGlobal('contentPages', $contentTypes->fetchAll());
                    return $twig;
                }));



$admin = $app['controllers_factory'];


$admin->get('/settings', function(Silex\Application $app) {
            return $app['twig']->render('settings.twig');
        })->bind("settings");

$admin->match('/content/{table}/add', function (Request $request, Silex\Application $app, $table) {
            $form = $app['form.factory']->create(new SilexF\Form\TableType($app, $table));
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


$admin->match('/content/{table}/edit/{id}', function (Request $request, Silex\Application $app, $table, $id) {

            $tableType = new SilexF\Form\TableType($app, $table);
            $form = $app['form.factory']->create($tableType);
            $primary_keys = $tableType->getPrimaryKeys();

            if (count($primary_keys) > 1) {
                throw new exception(" Edit for multiple keys not supported");
                return;
            } elseif (empty($primary_keys)) {
                
                throw new exception("Configure primary key");
            } else {

                $primary_key = $primary_keys[0];
            }

            if ('POST' == $request->getMethod()) {
                $form->bind($request);
                if ($form->isValid()) {
                    $data = $form->getData();
                    $app['db']->update($table, $data, array($primary_key => $id));
                    return $app->redirect($app['url_generator']->generate('admin_table_content', array("table" => str_replace("content_", '', $table))));
                }
            } else {
                $data = $app['db']->fetchAssoc("SELECT * FROM $table WHERE $primary_key = ?", array($id));
                $form->setData($data);
            }
            return $app['twig']->render('table_form.twig', array('form' => $form->createView()));
        })->convert('table', function ($table) {
            return "content_" . $table;
        })->convert('id', function ($id) {
            return (int) $id;
        })->bind("admin_table_edit_content");


$admin->get('/content/{table}', function (Request $request, Silex\Application $app, $table) {
            $repo = new SilexF\Repository\GenericRepository($app['db'], $table);
            $primary_keys = $repo->getPrimaryKeys();

            if (empty($primary_keys) || count($primary_keys) > 1) {
                $primary_key = ''; // disable edit for multiple keys; 
            } else {
                $primary_key = $primary_keys[0];
            }

            $page = $request->query->get('page', 1);
            $pagerfanta = $repo->pagerFetch();
            $pagerfanta->setMaxPerPage(5);
            $pagerfanta->setCurrentPage($page);
            $table_content = $pagerfanta->getCurrentPageResults();
            $view = new TwitterBootstrapView();
            $pager = $view->render($pagerfanta, function($page) use ($app, $table) {
                        return $app['url_generator']->generate('admin_table_content', array("page" => $page, "table" => str_replace("content_", '', $table)));
                    }, array(
                'proximity' => 3,
                    ));


            return $app['twig']->render('table_content.twig', array('contents' => $table_content, "table" => $table, "pager" => $pager, "primary_key" => $primary_key));
        })->convert('table', function ($table) {
            return "content_" . $table;
        })->bind('admin_table_content');



return $admin;


