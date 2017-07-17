<?php
class Framework_Web_Auth
{
    const LOGIN_KEY = 'login_user';
            
    public static function login($uid, $password)
    {
        $login_flag = false;

        $options = array(
            'uid' => $uid,
            'password' => md5($password)
        );
        $user = App_User::getInstance($options);
        
        if($user) {
            $options = array(
                'id' => $user->id
            );
            $_SESSION[self::LOGIN_KEY] = $options;
            $login_flag = true;
        }        
        return $login_flag;
    }

    public static function loginEmail($email, $password)
    {
        $login_flag = false;

        $options = array(
            'email' => $email,
            'password' => md5($password)
        );
        $user = App_User::getInstance($options);
        
        if($user) {
            $options = array(
                'id' => $user->id
            );
            $_SESSION[self::LOGIN_KEY] = $options;
            $login_flag = true;
        }        
        return $login_flag;
    }
    
    public static function loginId($id)
    {
        $login_flag = false;

        $options = array(
            'id' => $id
        );
        $user = App_User::getInstance($options);
        
        if($user) {
            $options = array(
                'id' => $user->id
            );
            $_SESSION[self::LOGIN_KEY] = $options;
            $login_flag = true;
        }        
        return $login_flag;
    }
    
    public static function loginByOptions($options)
    {
        $login_flag = false;

        $user = App_User::getInstance($options);
        if($user) {
            $options = array(
                'id' => $user->id
            );
            $_SESSION[self::LOGIN_KEY] = $options;
            $login_flag = true;
        }
        return $login_flag;
    }

    public static function logout()
    {
        $_SESSION[self::LOGIN_KEY] = null;
    }

    public static function isLogin()
    {
        $login_user = App::choose($_SESSION, self::LOGIN_KEY);
        App::debug("login check:key:". self::LOGIN_KEY);
        App::debug($login_user);
        if( $login_user ) {
            return true;
        }
        
        return false;
    }
    
    public static function getUser()
    {
        if( !self::isLogin()) return null;
        
        $options_ = $_SESSION[self::LOGIN_KEY];
        $options = array(
            'id' => App::choose($options_, 'id'),
        );
        $user = App_User::getInstance($options);
        return $user;
    }
    
    public static function redirect()
    {
        
    }
}
