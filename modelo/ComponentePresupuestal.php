<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ComponentePresupuestal
 *
 * @author guillermo
 */
class ComponentePresupuestal {
    //put your code here
    private $cp;
    private $descripcion;
    private $tabla = "tb_componente_presupuestal";
    
    
    public function getTabla() {
        return $this->tabla;
    }

        
    public function getCp() {
        return $this->cp;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setCp($cp) {
        $this->cp = $cp;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }
    
    public function exchangeArray($data){
        $this->cp  = (isset($data["cp"])) ? $data["cp"] : null;
        $this->descripcion  = (isset($data["descripcion"])) ? $data["descripcion"] : null;
    }
    
    public function getArrayCopy(){
        return get_object_vars($this);
    }


}
