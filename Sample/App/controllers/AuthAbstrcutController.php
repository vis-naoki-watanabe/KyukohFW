<?php
class AuthAbstructController extends AbstructController
{
    public function init()
    {
        parent::init();

        if(!Auth::isLogin()) {
            $this->redirect('/index');
        }
    }
}
