<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Convenio
 *
 * @author guillermo
 */
class Convenio {
    //put your code here
    private $anio;
    private $ramo;
    private $ur;
    private $ue;
    private $tipo_convenio;
    private $estatus;
    private $sncc;
    private $pp;
    private $cp;
    private $descripcion;
    private $clave;
    private $fechaInicio;
    private $fechaFin;
    private $tabla = "tb_cat_convenio";
    
    
    public function getSncc() {
        return $this->sncc;
    }

    public function setSncc($sncc) {
        $this->sncc = $sncc;
    }

        
    public function getTabla() {
        return $this->tabla;
    }
    
    public function getRamo() {
        return $this->ramo;
    }

    public function getTipoConvenio() {
        return $this->tipo_convenio;
    }

    public function setRamo($ramo) {
        $this->ramo = $ramo;
    }

    public function setTipoConvenio($tipo_convenio) {
        $this->tipo_convenio = $tipo_convenio;
    }
    
    public function getAnio() {
        return $this->anio;
    }

    public function getUr() {
        return $this->ur;
    }

    public function getUe() {
        return $this->ue;
    }

    public function getEstatus() {
        return $this->estatus;
    }

    public function getPp() {
        return $this->pp;
    }

    public function getCp() {
        return $this->cp;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getClave() {
        return $this->clave;
    }

    public function getFechaInicio() {
        return $this->fechaInicio;
    }

    public function getFechaFin() {
        return $this->fechaFin;
    }

    public function setAnio($anio) {
        $this->anio = $anio;
    }

    public function setUr($ur) {
        $this->ur = $ur;
    }

    public function setUe($ue) {
        $this->ue = $ue;
    }

    public function setEstatus($estatus) {
        $this->estatus = $estatus;
    }

    public function setPp($pp) {
        $this->pp = $pp;
    }

    public function setCp($cp) {
        $this->cp = $cp;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setClave($clave) {
        $this->clave = $clave;
    }

    public function setFechaInicio($fechaInicio) {
        $this->fechaInicio = $fechaInicio;
    }

    public function setFechaFin($fechaFin) {
        $this->fechaFin = $fechaFin;
    }
    
    
    public function exchangeArray($data){
        if(key_exists("fechaInicio", $data) || key_exists("fechaFin", $data)){
            date_default_timezone_set("America/Mexico_City");
            $aux = explode("-", $data["fechaInicio"]);
            $aux2 = explode("-", $data["fechaFin"]);
            $data["fechaInicio"] = $aux[2]."-".$aux[1]."-".$aux[0];
            $data["fechaFin"] = $aux2[2]."-".$aux2[1]."-".$aux2[0];
            $date["fechaInicio"] = new DateTime(date('Y-m-d', $data["fechaInicio"]));
            $date["fechaFin"] = new DateTime(date('Y-m-d', $data["fechaFin"]));
            
        }
        
        $this->anio  = (isset($data["anio"])) ? $data["anio"] : null;
        $this->ramo = (String) (isset($data["ramo"])) ? $data["ramo"] : null;
        $this->ur  = (String) (isset($data["ur"])) ? $data["ur"] : null;
        $this->ue  = (String) (isset($data["ue"])) ? $data["ue"] : null;
        $this->tipo_convenio  = (String)(isset($data["tipo_convenio"])) ? $data["tipo_convenio"] : null;
        $this->estatus  = (isset($data["estatus"])) ? $data["estatus"] : null;
        $this->sncc = (String) (isset($data["sncc"])) ? $data["sncc"] : null;
        $this->pp  = (String) (isset($data["pp"])) ? $data["pp"] : null;
        $this->cp  = (String)(isset($data["cp"])) ? $data["cp"] : null;
        $this->descripcion  = (isset($data["descripcion"])) ? $data["descripcion"] : null;
        $this->clave  = (String)(isset($data["clave"])) ? $data["clave"] : null;
        $this->fechaInicio  = (isset($data["fechaInicio"])) ? $data["fechaInicio"] : null;
        $this->fechaFin  = (isset($data["fechaFin"])) ? $data["fechaFin"] : null;
        
    }
    
    
    public function getArrayCopy(){
        return get_object_vars($this);
    }


    
}
