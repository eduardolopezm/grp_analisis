<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Dao
 *
 * @author guillermo
 */
class Dao {
    
    private $regresaInsert = array();
    private $tabla = "suppliers";
    
    
    
    public function setTabla($tabla) {
        $this->tabla = $tabla;
    }

        
    public function getTabla() {
        return $this->tabla;
    }

        
    public function getRegresaInsert() {
        return $this->regresaInsert;
    }

    public function setRegresaInsert($regresaInsert) {
        $this->regresaInsert[] = $regresaInsert;
    }
    
    
    public function insert($datos = array()){
        if(key_exists("arregloValidaciones", $datos) && key_exists("listadoMunicipios", $datos)){
            unset($datos["arregloValidaciones"]);
            unset($datos["listadoMunicipios"]);
        }
        
        $insert = "INSERT INTO ".$this->getTabla()."(";

        $insert .= implode(",", array_keys($datos));

        $insert .= " ) VALUES (";
        foreach ($datos as $key => $value) {
            if(($this->regresaTipo($value)) == 0 && ($this->getTabla() == "tb_tipo_convenio" || $this->getTabla() == "tb_componente_presupuestal")){
                $value = "'".$value."'";
                $datos[$key] = $value;
            }else{
                $value = "'".$value."'";
                $datos[$key] = $value;
                
            }
        }
        $insert.=implode(",",$datos);
        $insert .= ")";
        
        return $insert;
    }

        
    public function insertFastPrepareTwo($datos = array(), $cuantos = 1000) {
        

        $insert = "INSERT INTO " . $this->getTabla() . "(";

        $insert .= implode(",", array_keys($datos[0]));

        $insert .= " ) VALUES ";
        $j = 0;
        $pivote = 0;
        $ban = 0;
        $acumulado = 0;

        $aux = $datos;
        $inicio = 1;
        $fin = 1;
        $insertAux = $insert;
        $total = count($datos);

        foreach ($datos as $key => $value) {
            $noDatos = count($value) - 1;

            if ($pivote <= $cuantos) {
                foreach ($value as $v) {
                    if ($ban == 0) {
                        $insert .= "( ";
                        if ($this->regresaTipo($v) == 0) {

                            $v = "" . $v . "";
                        }
                        $insert .= "\"" . $v . "\" ,";
                        $ban = 1;
                    } else {
                        if ($j < $noDatos) {
                            if ($this->regresaTipo($v) == 0) {
                                $v = "" . $v . "";
                            }
                            $insert .= "\"" . $v . "\" ,";
                        } else {
                            if ($pivote < $cuantos) {
                                if ($this->regresaTipo($v) == 0) {
                                    $v = "" . $v . "";
                                }
                                $insert .= "\"" . $v . "\" ),";
                            } else {
                                if ($this->regresaTipo($v) == 0) {
                                    $v = "" . $v . "";
                                }
                                $insert .= "\"" . $v . "\" )";
                            }
                            $ban = 0;
                        }
                    }
                    $j++;
                }
                $j = 0;
                $pivote++;
                $fin++;
            } else {
                $acumulado += $cuantos;
                $pivote = 0;
                $i = 0;
                $this->setRegresaInsert($insert);
                $insert = $insertAux;
            }
        }

        $insert = substr($insert, 0, -1);

        $this->setRegresaInsert($insert);
    }
    
    public function regresaTipo($dato){
        $isNum = 0;
        
        
        if(is_numeric($dato)){
            $isNum = 1;
        }
        return $isNum;
    }

}
