<?php
class Framework_Web_Auth
{
    const USER_CLASS = 'App_User';
    const LOGIN_KEY  = 'login';
            
    public static function login($uid, $password, $suffix = '_')
    {
        $user_class = static::USER_CLASS;
        
        $login_flag = false;

        $options = array(
            'uid' => $uid,
            'password' => md5($password)
        );
        $user = $user_class::getInstance($options);
        
        if($user) {
            $options = array(
                'id' => $user->id
            );
            self::setSession($options, $suffix);
            $login_flag = true;
        }        
        return $login_flag;
    }

    public static function loginEmail($email, $password, $suffix = '_')
    {
        $user_class = static::USER_CLASS;
        
        $login_flag = false;

        $options = array(
            'email' => $email,
            'password' => md5($password)
        );
        $user = $user_class::getInstance($options);
        
        if($user) {
            $options = array(
                'id' => $user->id
            );
            self::setSession($options, $suffix);
            $login_flag = true;
        }        
        return $login_flag;
    }
    
    public static function loginId($id, $suffix = '_')
    {
        $user_class = static::USER_CLASS;
        
        $login_flag = false;

        $options = array(
            'id' => $id
        );
        $user = $user_class::getInstance($options);
        
        if($user) {
            $options = array(
                'id' => $user->id
            );
            self::setSession($options, $suffix);
            $login_flag = true;
        }        
        return $login_flag;
    }
    
    public static function loginByOptions($options, $suffix = '_')
    {
        $user_class = static::USER_CLASS;
        
        $login_flag = false;

        $user = $user_class::getInstance($options);
        if($user) {
            $options = array(
                'id' => $user->id
            );
            self::setSession($options, $suffix);
            $login_flag = true;
        }
        return $login_flag;
    }

    public static function logout($suffix = '_')
    {
        self::setSession(null, $suffix);
    }

    public static function isLogin($suffix = '_')
    {
        $login_user = self::getSession($suffix);
        
        if( $login_user ) {
            return true;
        }
        
        return false;
    }
    
    public static function getUser($suffix = '_')
    {
        $user_class = static::USER_CLASS;
        
        if( !self::isLogin($suffix)) return null;
        
        $options_ = self::getSession($suffix);
        $options = array(
            'id' => App::choose($options_, 'id'),
        );
        $user = $user_class::getInstance($options);
        return $user;
    }
    
    /*
    public static function redirect()
    {
        //$user_class = static::USER_CLASS;
        
    }
     * 
     */
    
    public static function setSession($data, $suffix = '_')
    {
        $_SESSION[static::getLoginKey($suffix)] = $data?base64_encode(json_encode($data)):$data;
    }
    
    public static function getSession($suffix = '_')
    {
        $session = @$_SESSION[static::getLoginKey($suffix)];
        if($session && is_array($session)) {
            $session = null;
            unset($_SESSION[static::getLoginKey($suffix)]);
        }
        return $session?json_decode(base64_decode($session), true):$session;
    }
    
    public static function getLoginKey($suffix = '_')
    {
        $key = sprintf('%s_%s_%s', static::USER_CLASS, self::LOGIN_KEY, $suffix);
        return $key;
    }
}
