<?php

/**
 *
 * PHP versions 4 and 5 (PHP4.3 upper)
 *
 * @author Tohru Ochiai <kyukoh@gmail.com>
 * @version $Revision: 0.2.0 $
 */
class Framework_Helper_Mail
{
    protected $to_          = null;
    protected $subject_     = null;
    protected $body_        = null;
    protected $from_        = null;
        
    /**
     * singletonインスタンス
     */
    protected static $instance_ = null;
    
    // {{{ protected function __construct()
    
    /**
     * 外部からのコンストラクタ呼び出しをブロック
     */
    protected function __construct()
    {
        mb_language("Japanese");
        mb_internal_encoding("UTF-8");
    }
    
    // }}}
    
    // {{{ public static function getInstance()
    
    /**
     * 初回呼び出し時はinstanceを作り、2度目以降は前に作ったのを返す
     */
    public static function getInstance()
    {
        if ( !isset( self::$instance_)) {
            self::$instance_ = new static();
        }
        return self::$instance_;
    }
    
    // }}}
    
    public function setBody($body)
    {
        $this->body_ = $body;
    }

    public function setTo($to)
    {
        $this->to_ = $to;
    }
    
    public function setSubject($subject)
    {
        $this->subject_ = $subject;
    }
    
    public function setFrom($from)
    {
        $this->from_ = $from;
    }
    
    public function setOptions($options)
    {
        if(array_key_exists('to', $options)) {
            $this->setTo($options['to']);
        }
        if(array_key_exists('subject', $options)) {
            $this->setSubject($options['subject']);
        }
        if(array_key_exists('body', $options)) {
            $this->setBody($options['body']);
        }
        if(array_key_exists('from', $options)) {
            $this->setFrom($options['from']);
        }
    }
    
    public function send($options = null)
    {
        if($options) {
            $this->setOptions($options);
        }
        
        $headers = "From: {$this->from_}" . "\r\n";
        mb_send_mail(
                $this->to_, 
                $this->subject_, 
                $this->body_, 
                $headers
        );
    }
}
?>
