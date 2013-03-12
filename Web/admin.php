<?php
use Web\Admin\Form as AdminForm;
 $admin = $app['controllers_factory'];
 $admin->get('/', function(Silex\Application $app) { 
    return  $app['twig']->render('@admin/index.twig');
            
     } );
 
 $admin->get('/settings', function(Silex\Application $app) { 
     return $app['twig']->render('@admin/settings.twig');
            
     })->bind("admin_settings");
$admin->get('/content/{table}/add',function (Silex\Application $app, $table) {
    
        $form = $app['form.factory']->create(new AdminForm\TableType($app, $table));
   
   
   return $app['twig']->render('@admin/table.twig', array('form'=>$form->createView()))   ;
    
})->convert('table', function ($table) { return  "content_". $table;})->bind("admin_content");

return $admin;


