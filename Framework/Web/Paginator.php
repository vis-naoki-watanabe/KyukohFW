<?php
class Framework_Web_Paginator
{
    protected $page = 1;
    protected $step = 0;
    protected $count = 0;
    protected $max_page = 0;
    protected $max_count = 0;
    protected $url = '%s';
    
    public function getCurrentPage()
    {
	return $this->page;
    }
    
    public function getMaxPage()
    {
	return $this->max_page;
    }
    
    public function getPrevPage()
    {
	return ($this->page-1)>0?$this->page-1:null;
    }
    
    public function getNextPage()
    {
	return ($this->page+1)<=$this->max_page?$this->page+1:$this->getMaxPage();
    }

    // カレントページのスタートID
    public function getCurrentStart()
    {
        return (($this->page-1) * $this->step) + 1;
    }
    
    // カレントページのエンドID
    public function getCurrentEnd()
    {
        return $this->getCurrentStart() + $this->getCurrentCount() - 1;
    }
    
    // カレントページのID数
    public function getCurrentCount()
    {
        return $this->count;
    }
    
    public function getMaxCount()
    {
        return $this->max_count;
    }

    public function setList( $list, $page, $step = 10 )
    {
        $this->max_count = $list->getCount();
	$this->max_page = ceil($list->getCount()/$step);
	$this->page = ($page>$this->max_page)?$this->max_page:$page;
        $this->step = $step;
	$ret = App::newList();
	$count = 1;
	foreach( $list as $value)
	{
	    $_page = ceil($count/$step);
	    $count++;
	    if( $_page != $page ) continue; 
	    $ret->add( $value );
	}
        $this->count = $ret->getCount();
	return $ret;
    }
    
    public function setUrl($url)
    {
        $this->url = $url;
    }
    
    public function getUrl($page = '')
    {
        return sprintf($this->url, $page);
    }
}
?>
