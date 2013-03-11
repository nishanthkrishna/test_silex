<?php

$app['admin.controller'] = $app->share(function() use ($app) {
            return new Web\admin\Controller($app['twig']);
        });
$admin = $app['controllers_factory'];
$admin->get('/settings', 'admin.controller:settingsAction')->bind("admin");
$admin->get('/', "admin.controller:indexAction");

return $admin;


