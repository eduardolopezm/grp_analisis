<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Accountxsubsidio
 *
 * @author guillermo
 */
class Accountxsubsidio {
    //put your code here
    private $cp;
    private $descripcion;
    private $accountcode;
    private $supplierid;
    private $estatus;
    
    public function getCp() {
        return $this->cp;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getAccountcode() {
        return $this->accountcode;
    }

    public function getSupplierid() {
        return $this->supplierid;
    }

    public function setCp($cp) {
        $this->cp = $cp;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setAccountcode($accountcode) {
        $this->accountcode = $accountcode;
    }

    public function setSupplierid($supplierid) {
        $this->supplierid = $supplierid;
    }
    
    public function getEstatus() {
        return $this->estatus;
    }

    public function setEstatus($estatus) {
        $this->estatus = $estatus;
    }

        
    public function exchangeArray($data){
        
        $this->cp = (isset($data["cp"])) ? $data["cp"] : null;
        $this->descripcion = (isset($data["descripcion"])) ? $data["descripcion"] : null;
        $this->accountcode = (isset($data["accountcode"])) ? $data["accountcode"] : null;
        $this->supplierid = (isset($data["supplierid"])) ? $data["supplierid"] : null;
        $this->estatus = (isset($data["estatus"])) ? $data["estatus"] : 1;
    }
    
    
    public function getArrayCopy(){
        return get_object_vars($this);
    }


}
