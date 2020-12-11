<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CuentaCont
 *
 * @author guillermo
 */

//include_once 'config2.php';
include_once 'ConnectDB_mysqli.inc';
include_once 'Chartmaster.php';
include_once 'Dao.php';



class CuentaCont {
    
    private $accountCode;
    private $accountName;
    private $tabla = "chartmaster";
    private $tablaConvenio = "tb_cat_convenio";
    private $nuevaTupla;
    private $tipo;
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    
    public function getAccountName() {
        return $this->accountName;
    }

    public function setAccountName($accountName) {
        $this->accountName = $accountName;
    }

        
    public function getDb() {
        return $this->db;
    }

    public function getTablaConvenio() {
        return $this->tablaConvenio;
    }

        
    public function getTipo() {
        return $this->tipo;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

        
    public function getNuevaTupla() {
        return $this->nuevaTupla;
    }

    public function setNuevaTupla($nuevaTupla) {
        $this->nuevaTupla = $nuevaTupla;
    }

        
    public function getTabla() {
        return $this->tabla;
    }
        
    public function getAccountCode() {
        return $this->accountCode;
    }

    public function setAccountCode($accountCode) {
        $this->accountCode = $accountCode;
    }
    
    
    public function traeUltimoRegistroCuenta(){
        
        $cuenta = explode(".", $this->getAccountCode());
        $nvaCuenta = array();
        $mensajeError = "";
        
        if(count($cuenta) == 9 && $this->getTipo() == 0){
            unset($cuenta[count($cuenta) - 1]);

            foreach ($cuenta as $key => $value) {
                if ($key <= 4) {
                    $nvaCuenta[] = $value;
                }
            }

            /*
             * PRIMERO BUSCAMOS EN LA TABLA
             * DE CONVENIO PARA VER SI NO ESTA ASIGNADA
             * AHI
             */
            
            /*$consultaCuentaConvenio = "select count(*) as cuantos from ". $this->getTablaConvenio()." where sncc = '".$cuenta[6]."'";
            
            $TransResultUno = DB_query($consultaCuentaConvenio, $db, "");
            
            $r1 = DB_fetch_assoc($TransResultUno);*/
            
            
            
            //if($r1["cuantos"] == 0){
            
           
                
                $consulta = "select CONVERT(SUBSTRING(accountcode,24,4),DECIMAL) as ultimo from " . $this->getTabla() . " where accountcode like '" . implode(".", $nvaCuenta) . "%' "
                        . " and substring(accountcode,11,2) = '" . $cuenta[5] . "' and substring(accountcode,19,2) = '".$cuenta[5]."' "
                        . " and substring(accountcode,21,2) = '01' and substring(accountcode,14,4) = '".$cuenta[6]."'"
                        . " order by CONVERT(SUBSTRING(accountcode,24,4),DECIMAL) desc limit 1";
                
                
                
                $TransResult = DB_query($consulta, $this->getDb(), "");
                $r = DB_fetch_assoc($TransResult);
                $nc = $this->regresa($r, $this->getTipo(),$cuenta);
                
            /*}else{
                $mensajeError .= "El septimo bloque de la cadena => ".$cuenta[6]." ya se encuentra asignado a un programa presupuestal. Necesita Registrar una nueva cuenta en el plan de cuentas";
                return $mensajeError;
            }*/
            
        }else{
            
            if(count($cuenta) == 9 && $this->getTipo() == 1){
                unset($cuenta[count($cuenta) - 1]);
                foreach ($cuenta as $key => $value) {
                if ($key <= 7) {
                    $nvaCuenta[] = $value;
                }
            }
            
            $consulta = "select CONVERT(SUBSTRING(accountcode,24,4),DECIMAL) as ultimo from ".$this->getTabla()." where accountcode like '" .implode(".", $nvaCuenta)."%' "
                    . " and substring(accountcode,11,2) = '".$cuenta[5]."' order by CONVERT(SUBSTRING(accountcode,24,4),DECIMAL) desc limit 1";
            
            
            
            $TransResult = DB_query($consulta, $this->getDb(), "");
            $r = DB_fetch_assoc($TransResult);
            $nc = $this->regresa($r, $this->getTipo(),$cuenta);
            
            }else{
                $mensajeError .= "<p>La cuenta contable no cumple con el formato ej. 2.1.6.2.1.xx.xxxx.xx01.xxxx para extrapresupuestal<br>"
                        . "o con el formatro 5.2.3.1.1.xx.0001.xxxx.xxxx para presupuestal</p>";
                 return $mensajeError;
            }
        }
        
        return $nc;
    }
    
    public function regresa($r, $tipo, $cuenta){
        
        
        
        $ultimo = (int) $r["ultimo"] + 1;
        $ultimaCuenta = $r["accountcode"];

        /*
         * CON ESTO FORMAMOS EL ULTIMO
         * BLOQUE
         */
        $nvacad = str_pad($ultimo, 4, '0', STR_PAD_LEFT);
        $nvaCuenta = implode(".", $cuenta) . "." . $nvacad;
        $r["accountcode"] = $nvaCuenta;
        $r["accountname"] = $this->getAccountName();
        $r["groupcode"] = implode(".",$cuenta);
        $r["ln_clave"] = $cuenta[5];
        $cm = new Chartmaster();
        $cm->setTipo($tipo);
        $cm->exchangeArray($r);
        $cm = $cm->getArrayCopy();
        unset($cm["tipoObjeto"]);

        $dao = new Dao();
        $dao->setTabla("chartmaster");
        $this->setNuevaTupla($dao->insert($cm));
        
        

        return $nvaCuenta;
    }


    
    
}
