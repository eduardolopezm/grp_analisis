<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of chartmaster
 *
 * @author guillermo
 */
class Chartmaster {
    //put your code here
    private $accountcode;
    private $accountname;
    private $group_;
    private $naturaleza;
    private $tipo;
    private $accountnameing;
    private $sectionnameing;
    private $formula;
    private $groupcode;
    private $reporte_group;
    private $ln_clave;
    private $nu_nivel;
    private $ind_activo;
    private $tagref;
    private $tipoObjeto;
    
    
    public function getTipoObjeto() {
        return $this->tipoObjeto;
    }

    public function setTipoObjeto($tipoObjeto) {
        $this->tipoObjeto = $tipoObjeto;
    }
    
    public function getAccountcode() {
        return $this->accountcode;
    }

    public function getAccountname() {
        return $this->accountname;
    }

    public function getGroup_() {
        return $this->group_;
    }

    public function getNaturaleza() {
        return $this->naturaleza;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getAccountnameing() {
        return $this->accountnameing;
    }

    public function getSectionnameing() {
        return $this->sectionnameing;
    }

    public function getFormula() {
        return $this->formula;
    }

    public function getGroupcode() {
        return $this->groupcode;
    }

    public function getReporte_group() {
        return $this->reporte_group;
    }

    public function getLn_clave() {
        return $this->ln_clave;
    }

    public function getNu_nivel() {
        return $this->nu_nivel;
    }

    public function getInd_activo() {
        return $this->ind_activo;
    }

    public function getTagref() {
        return $this->tagref;
    }

    public function setAccountcode($accountcode) {
        $this->accountcode = $accountcode;
    }

    public function setAccountname($accountname) {
        $this->accountname = $accountname;
    }

    public function setGroup_($group_) {
        $this->group_ = $group_;
    }

    public function setNaturaleza($naturaleza) {
        $this->naturaleza = $naturaleza;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function setAccountnameing($accountnameing) {
        $this->accountnameing = $accountnameing;
    }

    public function setSectionnameing($sectionnameing) {
        $this->sectionnameing = $sectionnameing;
    }

    public function setFormula($formula) {
        $this->formula = $formula;
    }

    public function setGroupcode($groupcode) {
        $this->groupcode = $groupcode;
    }

    public function setReporte_group($reporte_group) {
        $this->reporte_group = $reporte_group;
    }

    public function setLn_clave($ln_clave) {
        $this->ln_clave = $ln_clave;
    }

    public function setNu_nivel($nu_nivel) {
        $this->nu_nivel = $nu_nivel;
    }

    public function setInd_activo($ind_activo) {
        $this->ind_activo = $ind_activo;
    }

    public function setTagref($tagref) {
        $this->tagref = $tagref;
    }
    
    public function exchangeArray($data){
        
        if($this->getTipoObjeto() == 0){
            $this->accountcode = (isset($data["accountcode"])) ? $data["accountcode"] : null;
            $this->accountname = (isset($data["accountname"])) ? $data["accountname"] : null;
            $this->group_ = (isset($data["group_"])) ? $data["group_"] : "PASIVO";
            $this->naturaleza = (isset($data["naturaleza"])) ? $data["naturaleza"] : '-1';
            $this->tipo = (isset($data["tipo"])) ? $data["tipo"] : 2;
            $this->accountnameing = (isset($data["accountnameing"])) ? $data["accountnameing"] : null;
            $this->sectionnameing = (isset($data["sectionnameing"])) ? $data["sectionnameing"] : null;
            $this->formula = (isset($data["formula"])) ? $data["formula"] : null;
            $this->groupcode = (isset($data["groupcode"])) ? $data["groupcode"] : null;
            $this->reporte_group = (isset($data["reporte_group"])) ? $data["reporte_group"] : null;
            $this->ln_clave = (isset($data["ln_clave"])) ? $data["ln_clave"] : "Gerencia";
            $this->nu_nivel = (isset($data["nu_nivel"])) ? $data["nu_nivel"] : 9;
            $this->ind_activo = (isset($data["ind_activo"])) ? $data["ind_activo"] : 1;
            $this->tagref = (isset($data["tagref"])) ? $data["tagref"] : 'I6L';
        }else{
            $this->accountcode = (isset($data["accountcode"])) ? $data["accountcode"] : null;
            $this->accountname = (isset($data["accountname"])) ? $data["accountname"] : null;
            $this->group_ = (isset($data["group_"])) ? $data["group_"] : "GASTOS Y OTRAS PERDIDAS";
            $this->naturaleza = (isset($data["naturaleza"])) ? $data["naturaleza"] : '1';
            $this->tipo = (isset($data["tipo"])) ? $data["tipo"] : 5;
            $this->accountnameing = (isset($data["accountnameing"])) ? $data["accountnameing"] : null;
            $this->sectionnameing = (isset($data["sectionnameing"])) ? $data["sectionnameing"] : null;
            $this->formula = (isset($data["formula"])) ? $data["formula"] : null;
            $this->groupcode = (isset($data["groupcode"])) ? $data["groupcode"] : null;
            $this->reporte_group = (isset($data["reporte_group"])) ? $data["reporte_group"] : null;
            $this->ln_clave = (isset($data["ln_clave"])) ? $data["ln_clave"] : "Gerencia";
            $this->nu_nivel = (isset($data["nu_nivel"])) ? $data["nu_nivel"] : 9;
            $this->ind_activo = (isset($data["ind_activo"])) ? $data["ind_activo"] : 1;
            $this->tagref = (isset($data["tagref"])) ? $data["tagref"] : 'I6L';
        }
        
        

    }
    
    
    public function getArrayCopy(){
        return get_object_vars($this);
    }


}
