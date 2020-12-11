<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Accountxsupplier
 *
 * @author guillermo
 */
class Accountxsupplier {
    //put your code here
    private $accountcode;
    private $supplierid;
    private $concepto;
    private $u_typeoperation;
    private $deductibleflag;
    private $typeoperationdiot;
    private $flagdiot;
    
    public function getAccountcode() {
        return $this->accountcode;
    }

    public function getSupplierid() {
        return $this->supplierid;
    }

    public function getConcepto() {
        return $this->concepto;
    }

    public function getU_typeoperation() {
        return $this->u_typeoperation;
    }

    public function getDeductibleflag() {
        return $this->deductibleflag;
    }

    public function getTypeoperationdiot() {
        return $this->typeoperationdiot;
    }

    public function getFlagdiot() {
        return $this->flagdiot;
    }

    public function setAccountcode($accountcode) {
        $this->accountcode = $accountcode;
    }

    public function setSupplierid($supplierid) {
        $this->supplierid = $supplierid;
    }

    public function setConcepto($concepto) {
        $this->concepto = $concepto;
    }

    public function setU_typeoperation($u_typeoperation) {
        $this->u_typeoperation = $u_typeoperation;
    }

    public function setDeductibleflag($deductibleflag) {
        $this->deductibleflag = $deductibleflag;
    }

    public function setTypeoperationdiot($typeoperationdiot) {
        $this->typeoperationdiot = $typeoperationdiot;
    }

    public function setFlagdiot($flagdiot) {
        $this->flagdiot = $flagdiot;
    }
    
    public function exchangeArray($data){
        
        $this->accountcode = (isset($data["accountcode"])) ? $data["accountcode"] : null;
        $this->supplierid = (isset($data["supplierid"])) ? $data["supplierid"] : null;
        $this->concepto = (isset($data["concepto"])) ? $data["concepto"] : null;
        $this->u_typeoperation = (isset($data["u_typeoperation"])) ? $data["u_typeoperation"] : 0;
        $this->deductibleflag = (isset($data["deductibleflag"])) ? $data["deductibleflag"] : 0;
        $this->typeoperationdiot = (isset($data["typeoperationdiot"])) ? $data["typeoperationdiot"] : 0;
        $this->flagdiot = (isset($data["flagdiot"])) ? $data["flagdiot"] : null;
        
    }
    
    
    public function getArrayCopy(){
        return get_object_vars($this);
    }


}
