<?php
class IndexController extends AbstructController
{
    public function actionBefore()
    {
        
    }
    
    public function indexAction()
    {    
        $this->render();
    }
    
    public function actionAfter()
    {      
    }
}