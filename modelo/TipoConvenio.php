<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TipoConvenio
 *
 * @author guillermo
 */
class TipoConvenio {
    //put your code here
    private $tipo_convenio;
    private $descripcion;
    private $tabla = "tb_tipo_convenio";
    
    public function getTabla() {
        return $this->tabla;
    }
    
    public function getTipoConvenio() {
        return $this->tipo_convenio;
    }

    public function setTipoConvenio($tipo_convenio) {
        $this->tipo_convenio = $tipo_convenio;
    }

        
    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }
    
    public function exchangeArray($data){
        $this->tipo_convenio = (isset($data["tipo_convenio"])) ? $data["tipo_convenio"] : null;
        $this->descripcion  = (isset($data["descripcion"])) ? $data["descripcion"] : null;
    }
    
    public function getArrayCopy(){
        return get_object_vars($this);
    }


}
