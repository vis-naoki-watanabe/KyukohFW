<?php
class Framework_Base_DateTime extends DateTime
{
    protected $week_ = array(
        '日','月','火','水','木','金','土'
    );
    
    public static function getDateTimeList($year = null, $month = null)
    {
        $d = new DateTime();
        $d->setDate($year,$month, 1);
        $last_day = $d->format('t');
        
        $list = App::newList();
        for($i=1;$i<=$last_day;$i++)
        {
            $date = new static();
            $date->setDate($year,$month, $i);
            $list->add($date);
        }
        return $list;
    }
    
    public function format($format)
    {
        $week = App::choose($this->week_, parent::format('w'));
        $format = str_replace('JPW', $week, $format);
        return parent::format($format);
    }
    
    public function getTextColorClass($today_flag = true)
    {
        if($this->isToday()) return "txt-green";
        if(parent::format('w')==0) return "txt-pink";
        if(parent::format('w')==6) return "txt-cyan";
        return "";
    }
    
    public function isToday()
    {
        return parent::format('Ymd') == date('Ymd');
    }
    
    /**
     * @param int $month
     * @return $this
     */
    public function getNextMonth($month = 1)
    {
        $now = clone $this;
        $now->add(new DateInterval("P{$month}M"));
        if($this->format('j') != $now->format('j')) {
            $now->sub(new DateInterval("P1M"));
            $lastday = (new DateTime( 'last day of '.$now->format('Y-m') ))->format('j');
            $now->setDate($now->format('Y'), $now->format('m'), $lastday);
        }
        return $now;
    }
 
    /**
     * @param int $month
     * @return $this
     */
    public function getPrevMonth($month = 1)
    {
        $now = clone $this;
        $now->sub(new DateInterval("P{$month}M"));
        if($this->format('j') != $now->format('j')) {
            $now->sub(new DateInterval("P1M"));
            $lastday = (new DateTime( 'last day of '.$now->format('Y-m') ))->format('j');
            $now->setDate($now->format('Y'), $now->format('m'), $lastday);
        }
        return $now;
    }
    
    public function getGradeYear()
    {
        return self::_getGradeYear($this->format('Y'), $this->format('m'));
    }
    
    public static function _getGradeYear($year = null, $month = null)
    {
        if(!$year) {
            $year = date('Y');
        }
        if(!$month) {
            $month = date('m');
        }
        if($month>=1 && $month<=3) {
            $year--;
        }
        return $year;
    }
    
    public static function getGradeYearMonthList($year = null)
    {
        if(!$year) {
            $year = self::_getGradeYear();
        }
        
        $list = App::newList();
        for($i=0;$i<12;$i++)
        {
            $date = new self();
            $date->setDate($year, $i+4, 1);
            $list->add($date);
        }
        return $list;
    }
    
    
    // {{{ public function __get( $name )
    
    /**
     * GETTER アクセッサ
     * @param	string $name
     * @return	mixed
     * @throws	BadMethodCallException
     */
    public function __get( $name )
    {
        if(preg_match('/__(.*)__/', $name,$ret)) {
            $name = $ret[1];
        }
        
        // メッソッドが存在する場合はメソッド実行
        //$method = 'get'.ucfirst($name);
        $method = 'get'.App::Camelize($name);
        if( method_exists( $this, $method ) ) { return $this->$method();}
        
        return null;
    }

    // }}}

    public function getYmd()
    {
        return $this->format('Ymd');
    }
}