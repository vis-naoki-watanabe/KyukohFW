<?php
class Framework_Paginator
{
    protected $page = 1;
    protected $max_page = 0;
    protected $max_count = 0;
    
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
    
    public function getListCount()
    {
        return $this->max_count;
    }

    public function setList( $list, $page, $step = 10 )
    {
        $this->max_count = $list->getCount();
	$this->max_page = ceil($list->getCount()/$step);
	$this->page = ($page>$this->max_page)?$this->max_page:$page;
	$ret = App::newList();
	$count = 1;
	foreach( $list as $value)
	{
	    $_page = ceil($count/$step);
	    $count++;
	    if( $_page != $page ) continue; 
	    $ret->add( $value );
	}
	return $ret;
    }
}
?>
