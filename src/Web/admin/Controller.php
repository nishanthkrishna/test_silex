<?php

namespace  Web\admin;



class  Controller  {
    
  private $twig;
  
  public function __construct(\Twig_Environment $twig) {
        $this->twig = $twig;
    }
    
     public function settingsAction()
    {
         
         return $this->twig->render('@admin/settings.twig');
       // return "settings action";
        
    }
    
    public function indexAction() {
        
            return $this->twig->render('@admin/index.twig');
    }
    
    
}
 

