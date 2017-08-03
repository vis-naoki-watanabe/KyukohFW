<?php
class AuthAbstractController extends AbstractController
{
    public function init()
    {
        parent::init();

        if(!Auth::isLogin()) {
            $this->redirect('/index');
        }
    }
}
