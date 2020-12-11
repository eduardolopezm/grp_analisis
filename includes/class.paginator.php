<?php
class Paginator {
	
	var $current;
	var $rows;
	var $start_row;
	var $total_data;
	
    function Paginator($current_page, $totaldata, $rows_page = 10) {
        $this->rows = $rows_page;
        $this->total_data = $totaldata;
        if($current_page < 1 or $current_page > $this->getTotalPage()) {
            $this->current = 1;
        } else {
            $this->current = $current_page;
        }
    }
    
    function getTotalPage() {
        $total = ceil($this->total_data / $this->rows);
        return $total;
    }
    
    function getLimit() {
        if($this->current <= 1) {
            $this->start_row = 0;
        } else {
            $this->start_row = $this->rows*($this->current-1);
        }
        return ' LIMIT '.$this->start_row.','.$this->rows;
    }
    
    function getNext() {
        if($this->current < $this->getTotalPage()) {
            return $this->current + 1;
        }
        return $this->getTotalPage();
    }
    
    function getPrevious() {
        if($this->current > 1) {
            return $this->current - 1;
        }
        return $this->current;
    }
    
    function getPages() {
        $first = false; $last = false;
        if($this->getTotalPage() == 0) {
            return false;
        } else if($this->getTotalPage() > 10) {
            for($i=1; $i <= $this->getTotalPage(); $i++) {
                if($i == $this->current) {
                    $page[] = array(
                        'link' => false,
                        'page'    => $i
                    );
                } else if($i < $this->current-3 and $i > 3 and $i < $this->current+5 ) {
                    if(!$first) {
                        $page[] = array(
                            'link'    => false,
                            'page'    => '...',
                        );
                    }
                    $first = true;
                } else if($i < $this->getTotalPage()-3 and $i > $this->current+5) {
                    if(!$last){
                        $page[] = array(
                            'link'    => false,
                            'page'    => '...',
                        );
                    }
                    $last = true;
                } else {
                    $page[] = array(
                        'link' => true,
                        'page' => $i
                    );
                }
            }
        }
        else{
            for($i=1; $i<= $this->getTotalPage();$i++) {
                if($i == $this->current) {
                    $page[] = array(
                        'link'    => false,
                        'page'    => $i
                    );
                } else {
                    $page[] = array(
                        'link'    => true,
                        'page'    => $i
                    );
                }
            }
        }
        return $page;
    }
    
    function navigation($url=null, $attr=array()) {
        $parm = '';
        if(is_array($attr)) {
            foreach($attr as $name => $val) {
                $parm .= ' '.$name.'="'.$val.'"';
            }
        }
        $nav = null;
        if($this->getPrevious()) {
            $nav .= '<a href="'.$url.'&page='.$this->getPrevious().'" '.$parm.'>Anterior</a> | ';
        }
        if($this->getPages()) {
            foreach($this->getPages() as $name => $val) {
                if($val['page'] != 1){
                    $nav .= ', ';
                }
                if($val['link']) {
                    $nav .= '<a href="'.$url.'&page='.$val['page'].'" '.$parm.'>'.$val['page'].'</a>';
                } else {
                    $nav .= '<b>'.$val['page'].'</b>';
                }
            }
        }
        if($this->getNext()) {
            $nav .= ' | <a href="'.$url.'&page='.$this->getNext().'" '.$parm.'>Siguiente</a>';
        }
        return $nav;
    }
}
