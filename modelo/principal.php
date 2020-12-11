<?php

/* 
 * To change this license header, choose License Headers in Project Properties.

 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once 'Convenio.php';
include_once 'TipoConvenio.php';
include_once 'ComponentePresupuestal.php';
include_once 'Dao.php';
//include_once 'config2.php';
include_once('../config.php');
$DefaultCompany = 'ap_grp';
$_SESSION['DatabaseName'] = $DefaultCompany;
include_once 'ConnectDB_mysqli.inc';
//include_once('../includes/ConnectDB.inc');

$datos = $_POST;



$dao = new Dao();
//echo "aqui";
//exit;

$tipo = $datos["tipo"];
$datosInsertar = array();




$objeto = null;
$info = array();
$result = true;

switch ($tipo) {
    case '1':
        $objeto = new TipoConvenio();
        $objeto->exchangeArray($datos);
        
        $datosInsertar = $objeto->getArrayCopy();
        
        break;
    
    case '1.1':        
        $objeto = new TipoConvenio();
        $consulta = "select * from ".$objeto->getTabla();
        
        $TransResult = DB_query($consulta, $db, "");
        if(!is_null($TransResult)){
            while ($myrow = DB_fetch_array($TransResult)){
                $info[] = array(
                    "Tipo"=> $myrow["tipo_convenio"],
                    "Descripcion" => $myrow["descripcion"],
                    'Modificar'=> '<a href="#" id="'.$myrow['tipo_convenio'].'"><span class="glyphicon glyphicon-edit"></span></a>',
                    'Eliminar'=>'<a href="#" id="'.$myrow['tipo_convenio'].'"><span class="glyphicon glyphicon-trash"></span></a>',
                );
            }
        }else{
            $result = false;
        }
        
        break;
        
    
    case '2':
        $objeto = new ComponentePresupuestal();
        $objeto->exchangeArray($datos);
        $datosInsertar = $objeto->getArrayCopy();
        break;
    
    case '2.1':
        $objeto = new ComponentePresupuestal();
        $consulta = "select * from ".$objeto->getTabla();
        $TransResult = DB_query($consulta, $db, "");
        if (!is_null($TransResult)) {
            while ($myrow = DB_fetch_array($TransResult)) {
                $info[] = array(
                    "CP" => $myrow["cp"],
                    "Descripcion" => $myrow["descripcion"],
                    'Modificar' => '<a href="#" id="' . $myrow['cp'] . '"><span class="glyphicon glyphicon-edit"></span></a>',
                    'Eliminar' => '<a href="#" id="' . $myrow['cp'] . '"><span class="glyphicon glyphicon-trash"></span></a>',
                );
            }
        } else{
            $result = false;
        }
        
    
        break;
    
    case '3.1':
        date_default_timezone_set("America/Mexico_City");
        $objeto = new Convenio();
        $consulta = "select * from ".$objeto->getTabla();
        $TransResult = DB_query($consulta, $db, "");
        if (!is_null($TransResult)) {
            while ($myrow = DB_fetch_array($TransResult)) {
                $info[] = array("Anio" => $myrow["anio"],
                    "Ramo" => $myrow["ramo"],
                    "UR" => $myrow["ur"],
                    "UE" => $myrow["ue"],
                    "Clave" => $myrow["clave"],
                    "SNCC" => $myrow["sncc"],
                    "PP" => $myrow["pp"],
                    "CP" => $myrow["cp"],
                    "Descripcion" => $myrow["descripcion"],
                    "Tipo Convenio" => $myrow["tipo_convenio"],
                    /*"Desde" => new DateTime(date('Y-m-d', $myrow["fechaInicio"])),
                    "Hasta" => new DateTime(date('Y-m-d', $myrow["fechaFin"])),*/
                    "Desde" => $myrow["fechaInicio"],
                    "Hasta" => $myrow["fechaFin"],
                    "Estatus" => $myrow["estatus"]
                );
            }
            
        } else {
            $result = false;
        }

        break;

    default:
        /*
         * VERIFICAMOS QUE EL SEPTIMO NIVEL DE LA CUENTA
         * CONTABLE NO ESTE ASIGNADO A NINGUN CONVENIO
         */
        $objeto = new Convenio();
        $consultaCuentaConvenio = "select count(*) as cuantos, clave from ". $objeto->getTabla()." where sncc = '".$datos["sncc"]."' AND `ur` = '$datos[ur]' AND `ue` = '$datos[ue]' AND `clave` = '$datos[clave]' AND `anio` = '$datos[anio]'";
        $TransResultUno = DB_query($consultaCuentaConvenio, $db, "");
        $r1 = DB_fetch_assoc($TransResultUno);
        
        if($r1["cuantos"] > 0){
            $Mensaje = "El septimo nivel de cuenta => ".$datos["sncc"]." ya se encuentra asignado al convenio con clave => ".$r1["clave"];
        }else{
            
            $objeto->exchangeArray($datos);
            $datosInsertar = $objeto->getArrayCopy();
        }
        
        break;
}


if($tipo == '1' || $tipo == '2' || $tipo == '3'){
    $dao->setTabla($objeto->getTabla());
    unset($datosInsertar["tabla"]);
    $insert = $dao->insert($datosInsertar);
    
    $SQL = "";
    $contenido = "";
    $RootPath = "";
    $ErrMsg = "";
    $Mensaje = "";
    
    
    $result = DB_query($insert, $db);
    if(is_null($result) || $result === false){
        $result = false;   
    }else{
        $Mensaje = "Dato cargado existosamente.";
    }

    
}else{
    
    $SQL = "";
    $contenido = "";
    $RootPath = "";
    $ErrMsg = "";
    $Mensaje = "No se pudo cargar el dato";
    
    if($result){
        $contenido = array("datos" => $info);
    }
    
    
}

$dataObj = array('info' =>'', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);

