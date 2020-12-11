<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Accountxconvenio
 *
 * @author guillermo
 */
class Accountxconvenio {
    //put your code here
    private $clave;
    private $descripcion;
    private $accountcode;
    private $supplierid;
    private $estatus;
    
    
    public function getEstatus() {
        return $this->estatus;
    }

    public function setEstatus($estatus) {
        $this->estatus = $estatus;
    }

        
    public function getClave() {
        return $this->clave;
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

    public function setClave($clave) {
        $this->clave = $clave;
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
    
    public function exchangeArray($data){
        
        $this->clave = (isset($data["clave"])) ? $data["clave"] : null;
        $this->descripcion = (isset($data["descripcion"])) ? $data["descripcion"] : null;
        $this->accountcode = (isset($data["accountcode"])) ? $data["accountcode"] : null;
        $this->supplierid = (isset($data["supplierid"])) ? $data["supplierid"] : null;
        $this->estatus = (isset($data["estatus"])) ? $data["estatus"] : 1;
    }
    
    
    public function getArrayCopy(){
        return get_object_vars($this);
    }


}
