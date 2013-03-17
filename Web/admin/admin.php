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


$admin->get('/content/{table}', function (Request $request, Silex\Application $app, $table) {
            $repo = new SilexF\Repository\GenericRepository($app['db'], $table);
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


            return $app['twig']->render('table_content.twig', array('contents' => $table_content, "table" => $table, "pager" => $pager));
        })->convert('table', function ($table) {
            return "content_" . $table;
        })->bind('admin_table_content');



return $admin;


