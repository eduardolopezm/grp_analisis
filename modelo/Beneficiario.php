<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Beneficiario
 *
 * @author guillermo
 */
class Beneficiario {
    //put your code here
    private $supplierid;
    private $ln_tipoPersona;
    private $suppname;
    private $taxid;
    private $ln_curp;
    private $ln_representante_legal;
    private $id_nu_entidad_federativa;
    private $id_nu_municipio;
    private $address1;//colonia
    private $address2;//calla
    private $address3;//municipio
    private $address4;//estado
    private $address5;//cp
    private $address6;//telefon
    private $nu_exterior;
    private $nu_interior;
    private $email;
    private $currcode;
    private $active;
    private $id_nu_tipo;
    private $arregloValidaciones = array();
    private $listadoMunicipios = array();
        
    public function __construct() {
        /*
         * AQUI TRAEMOS LOS CATALOGOS DE LAS ENTIDADES FEDERATIVAS Y MUNICIPIOS
         */
        $this->arregloValidaciones = array(
        "supplierid" => array("NoVacio" => ""),
        "ln_tipoPersona" => array("Compara" => array('F','G','M')),
        "suppname" => array("NoVacio"=> ""),
        "taxid" => array("ExpReg" => array("/^[A-Z]{4}\d{6}[[:alnum:]]{3}/","/^[A-Z]{4}\d{6}[[:alnum:]]{2}/")),
        "ln_curp" => array("ExpReg" => array("/^[A-Z]{4}\d{6}[HM](AS|BC|BS|CC|CS|CH|CL|CM|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[A-Z]{3}([A-Z]\d|\d{2})$/")),
        "ln_representante_legal" => array("NoVacio" => ""),
        "address1" => array("NoVacio" => ""),
        "address2" => array("NoVacio" => ""),
        "address3" => array("Consulta" => $this->listadoMunicipios),
        "address4" => array("Compara" => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,6,27,28,29,30,31,32)),
        "address5" => array("NoVacio" => ""),
        "address6" => array("NoVacio" => ""),
        "nu_exterior" => array("LongMax" => 10),
        "nu_interior" => array("LongMax" => 10),
        "address6" => array("NoVacio" => ""),
        "email" => array("NoVacio" => "", "LongMax" => 255),
        "currcode" => array("Compara" => "MXN"),
        "active" => array("Compara" => array(0,1)) 
        ); 
    
    }
    
    public function getSupplierid() {
        return $this->supplierid;
    }

    public function getLn_tipoPersona() {
        return $this->ln_tipoPersona;
    }

    public function getSuppname() {
        return $this->suppname;
    }

    public function getTaxid() {
        return $this->taxid;
    }

    public function getLn_curp() {
        return $this->ln_curp;
    }

    public function getLn_representante_legal() {
        return $this->ln_representante_legal;
    }

    public function getId_nu_entidad_federativa() {
        return $this->id_nu_entidad_federativa;
    }

    public function getId_nu_municipio() {
        return $this->id_nu_municipio;
    }

    public function getAddress1() {
        return $this->address1;
    }

    public function getAddress2() {
        return $this->address2;
    }

    public function getAddress3() {
        return $this->address3;
    }

    public function getAddress4() {
        return $this->address4;
    }

    public function getAddress5() {
        return $this->address5;
    }

    public function getAddress6() {
        return $this->address6;
    }

    public function getNu_exterior() {
        return $this->nu_exterior;
    }

    public function getNu_interior() {
        return $this->nu_interior;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getCurrcode() {
        return $this->currcode;
    }

    public function getActive() {
        return $this->active;
    }

    public function getArregloValidaciones() {
        return $this->arregloValidaciones;
    }

    public function getListadoMunicipios() {
        return $this->listadoMunicipios;
    }

    public function setSupplierid($supplierid) {
        $this->supplierid = $supplierid;
    }

    public function setLn_tipoPersona($ln_tipoPersona) {
        $this->ln_tipoPersona = $ln_tipoPersona;
    }

    public function setSuppname($suppname) {
        $this->suppname = $suppname;
    }

    public function setTaxid($taxid) {
        $this->taxid = $taxid;
    }

    public function setLn_curp($ln_curp) {
        $this->ln_curp = $ln_curp;
    }

    public function setLn_representante_legal($ln_representante_legal) {
        $this->ln_representante_legal = $ln_representante_legal;
    }

    public function setId_nu_entidad_federativa($id_nu_entidad_federativa) {
        $this->id_nu_entidad_federativa = $id_nu_entidad_federativa;
    }

    public function setId_nu_municipio($id_nu_municipio) {
        $this->id_nu_municipio = $id_nu_municipio;
    }

    public function setAddress1($address1) {
        $this->address1 = $address1;
    }

    public function setAddress2($address2) {
        $this->address2 = $address2;
    }

    public function setAddress3($address3) {
        $this->address3 = $address3;
    }

    public function setAddress4($address4) {
        $this->address4 = $address4;
    }

    public function setAddress5($address5) {
        $this->address5 = $address5;
    }

    public function setAddress6($address6) {
        $this->address6 = $address6;
    }

    public function setNu_exterior($nu_exterior) {
        $this->nu_exterior = $nu_exterior;
    }

    public function setNu_interior($nu_interior) {
        $this->nu_interior = $nu_interior;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setCurrcode($currcode) {
        $this->currcode = $currcode;
    }

    public function setActive($active) {
        $this->active = $active;
    }

    public function setArregloValidaciones($arregloValidaciones) {
        $this->arregloValidaciones = $arregloValidaciones;
    }

    public function setListadoMunicipios($listadoMunicipios) {
        $this->listadoMunicipios = $listadoMunicipios;
    }

    
        
    public function exchangeArray($data){
        $this->supplierid = (isset($data["supplierid"])) ? $data["supplierid"] : null;
        $this->ln_tipoPersona = (isset($data["ln_tipoPersona"])) ? $data["ln_tipoPersona"] : null;
        $this->suppname = (isset($data["suppname"])) ? $data["suppname"] : null;
        $this->taxid = (isset($data["taxid"])) ? $data["taxid"] : null;
        $this->ln_curp = (isset($data["ln_curp"])) ? $data["ln_curp"] : null;
        $this->ln_representante_legal = (isset($data["ln_representante_legal"])) ? $data["ln_representante_legal"] : null;
        $this->id_nu_entidad_federativa = (isset($data["id_nu_entidad_federativa"])) ? $data["id_nu_entidad_federativa"] : null;
        $this->id_nu_municipio = (isset($data["id_nu_municipio"])) ? $data["id_nu_municipio"] : null;
        $this->address1 = (isset($data["address1"])) ? $data["address1"] : null;     //colonia
        $this->address2 = (isset($data["address2"])) ? $data["address2"] : null;     //calla
        $this->address3 = (isset($data["address3"])) ? $data["address3"] : null;     //municipio
        $this->address4 = (isset($data["address4"])) ? $data["address4"] : null;     //estado
        $this->address5 = (isset($data["address5"])) ? $data["address5"] : null;     //cp
        $this->address6 = (isset($data["address6"])) ? $data["address6"] : null;     //telefono
        $this->nu_exterior = (isset($data["nu_exterior"])) ? $data["nu_exterior"] : null;
        $this->nu_interior = (isset($data["nu_interior"])) ? $data["nu_interior"] : null;
        $this->email = (isset($data["email"])) ? $data["email"] : null;
        $this->currcode = (isset($data["currcode"])) ? $data["currcode"] : null;
        $this->active = (isset($data["active"])) ? $data["active"] : null;
        $this->id_nu_tipo = (isset($data["id_nu_tipo"])) ? $data["id_nu_tipo"] : 3;
    }
    
    public function getArrayCopy(){
        return get_object_vars($this);
    }
    
    public function regresaValidaciones($key){
        
        return $this->getArregloValidaciones()[$key];
    }
    
    


    
}
