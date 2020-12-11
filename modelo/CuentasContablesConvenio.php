<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$PageSecurity = 4;
$PathPrefix = '../';

include($PathPrefix. "includes/SecurityUrl.php");
include($PathPrefix.'abajo.php');
include($PathPrefix.'config.php');
include($PathPrefix.'includes/ConnectDB.inc');
include($PathPrefix.'includes/SecurityFunctionsHeader.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');
include('CuentaCont.php');
include('Dao.php');
include('Accountxconvenio.php');
include('Accountxsubsidio.php');
include('Accountxsupplier.php');


$datos = $_POST;



$opt = $datos["opcion"];

$result = true;
$error = array();
$contenido = array();

switch ($opt) {
    case 1:
        $nvaCuenta = array();
        $cuenta = explode(".", $datos["accountcode"]);
        if($datos["tipoS"] == 0){
            
            foreach ($cuenta as $key => $value) {
                if ($key <= 4) {
                    $nvaCuenta[] = $value;
                }
            }
            $consultaSQL = "select count(*) AS cuantos from chartmaster where accountcode like '" . implode(".", $nvaCuenta) . "%' "
                    . " and substring(accountcode,11,2) = '" . $cuenta[5] . "' and substring(accountcode,19,2) = '" . $cuenta[5] . "' "
                    . " and substring(accountcode,21,2) = '01'";
            
            
            $TransResult = DB_query($consultaSQL, $db, "");
            $r0 = DB_fetch_assoc($TransResult);
            
        }else{
            foreach ($cuenta as $key => $value) {
                if ($key <= 7) {
                    $nvaCuenta[] = $value;
                }
            }
            $consultaSQL = "select count(*) as cuantos from chartmaster where accountcode like '" .implode(".", $nvaCuenta)."%'";
            $TransResult = DB_query($consultaSQL, $db, "");
            $r1 = DB_fetch_assoc($TransResult);
        }
        

        $consultaDos = "select count(*) as cuantos from accountxsupplier where supplierid = '" . $datos['supplierid'] . "' and accountcode = '" . $datos['accountcode'] . "'";
        $TransResultDos = DB_query($consultaDos, $db, "");
        $r2 = DB_fetch_assoc($TransResultDos);
        
        if($datos["tipoS"] == 0){
            $conv = explode("-",$datos["convenio"]);
            $consultaTres = "select count(*) as cuantos from accountxconvenio where supplierid = '" . $datos['supplierid'] . "' and accountcode = '" . $datos['accountcode'] . "'"
                . "and clave = '" . $conv[0] . "'";
            
            $TransResultTres = DB_query($consultaTres, $db, "");
            $r3 = DB_fetch_assoc($TransResulTres);
        }else{
            $prep = explode("-", $datos["prep"]);
            $consultaCuatro = "select count(*) as cuantos from accountxsubsidio where supplierid = '" . $datos['supplierid'] . "' and accountcode = '" . $datos['accountcode'] . "'"
                . "and cp = '" . $prep[0] . "'";
            
        
            
            $TransResultCuatro = DB_query($consultaCuatro, $db, "");
            $r4 = DB_fetch_assoc($TransResulCuatro);
        }
        
        
        /*var_dump($r0);
        var_dump($r2);
        var_dump($r3);*/
        
        
        if(key_exists("cuantos", $r0) && $r0["cuantos"] == 0){
            $error["cero"] = "La cuenta contable no existe en el plan de cuentas con el formato". implode(".", $nvaCuenta)." Necesita Registrarla";
        }

        if (key_exists("cuantos", $r1) && $r1['cuantos'] == 0) {
            $error["uno"] = "La cuenta contable no existe en el plan de cuentas con el formato".implode(".", $nvaCuenta).". Necesita Registrarla";
        }

        if (key_exists("cuantos", $r2) && $r2['cuantos'] > 0) {
            $error["dos"] = "La cuenta contable ya esta asignada al beneficiario identificado con el código " . $datos['supplierid'];
        }

        if (key_exists("cuantos", $r3) && $r3['cuantos'] > 0) {
            $error["tres"] = "La cuenta contable ya esta asignada a un convenio";
        }
        
        if(key_exists("cuantos", $r4) && $r4['cuantos'] > 0){
            $error["cuatro"] = "La cuenta contable ya esta asignada a un subsidio";
        }

        if (key_exists("cero", $error) || key_exists("uno", $error) || key_exists("dos", $error) || key_exists("tres", $error) || key_exists("cuatro", $error)) {
            $result = false;
        } else {
            /*
             * PARA REGRESAR EL ULTIMO REGISTRO DE LA CUENTA
             * CONTABLE SERA CUANDO ESTA EXISTA EN EL CATALOGO
             * PERO NO ESTE ASIGNADA NI A UN BENEFICIARIO NI
             * A UN CONVENIO POR LO CUAL PROCEDEREMOS A 
             * BUSCAR EL ULTIMO REGISTRO DE ESA CUENTA
             */
            if($r0['cuantos'] > 0 || $r1['cuantos'] > 0){
                $cc = new CuentaCont($db);
                $cc->setTipo($datos["tipoS"]);
                $cc->setAccountCode($datos["accountcode"]);
                $cc->setAccountName($datos["suppname"]);
                $nuevaCuenta = $cc->traeUltimoRegistroCuenta();
                $insertCuenta = $cc->getNuevaTupla();
                //var_dump($nuevaCuenta);
                
                $Mensaje = "La cuenta ya existe en el catálogo el siguiente registro a ocupar es: ".$nuevaCuenta;
                $result = true;
                $agregarCuenta = true;
            }
            
        }

        break;
        
    default:
        
        $dao = new Dao();
        /*
         * LLEGANDO A ESTE PASO LO QUE HAY QUE REALIZAR
         * ES LA CARGA EN LAS TABLAS DE CHARTMASTER -
         * ACCOUNTXSUPPLIER Y segun sea el caso en accountxconvenio
         * o accountxsubsidio
         */
        
        $TransResult = DB_query($datos["insert"], $db, "");
        if($TransResult == false || is_null($TransResult)){
            $result = false;
            $Mensaje = "No se pudo registrar la cuenta contable.";
        }else{
            /*
             * INSERTAMOS PRIMERO EN ACCOUNT X SUPPLIER
             */
            $concepto = "";
            if($datos["tipoS"] == 0){
                $concepto = "Asignación de cuenta a beneficiario por convenio";
            }else{
                $concepto = "Asignación de cuenta a beneficiario por subsidio";
            }
            
            $axs = new Accountxsupplier();
            $axsarray = array(
                "accountcode" => $datos["accountcode"],
                "supplierid" => $datos["supplierid"],
                "concepto" => $concepto
            );

            $axs->exchangeArray($axsarray);

            $dao->setTabla("accountxsupplier");
            $TransResultDos = DB_query($dao->insert($axs->getArrayCopy()), $db, "");
            if (is_null($TransResultDos)) {
                $result = false;
                $Mensaje = "No se pudo registrar la cuenta ligada al beneficiario con código: " . $datos["supplierid"];
            } else {

                if ($datos["tipoS"] == 0) {
                    $extPrep = $datos["extprep"];
                    $conv = explode("-",$datos["convenio"]);
                    /*
                     * AQUI INSERTAMOS EN CONVENIO
                     */
                    $axc = new Accountxconvenio();
                    $axcarray = array(
                        "clave" => $conv[0],
                        "descripcion" => $conv[1],
                        "accountcode" => $datos["accountcode"],
                        "supplierid" => $datos["supplierid"]
                    );
                    $axc->exchangeArray($axcarray);
                    $dao->setTabla("accountxconvenio");
                    $TransResultTres = DB_query($dao->insert($axc->getArrayCopy()), $db, "");
                } else {
                    $prep = explode("-", $datos["prep"]);
                    /*
                     *  AQUI INSERTAMOS EN SUBSIDIO*
                     */
                    $axs = new Accountxsubsidio();
                    $axsarray = array(
                        "cp" => $prep[0],
                        "descripcion" => $prep[1],
                        "accountcode" => $datos["accountcode"],
                        "supplierid" => $datos["supplierid"]
    
                    );
                    $axs->exchangeArray($axsarray);
                    $dao->setTabla("accountxsubsidio");
                    $TransResultTres = DB_query($dao->insert($axs->getArrayCopy()), $db, "");
                }

                if (is_null($TransResultTres)) {
                    $result = false;
                    $Mensaje = "No se pudo registrar la cuenta ni el convenio. " . $datos["clave"];
                } else {
                    $consulta = "select * from @tabla@ where supplierid = '".$datos["supplierid"]."'";
                    
                    $datos["tipoS"] == 0 ? $consulta = str_replace("@tabla@", "accountxconvenio", $consulta) : $consulta = str_replace("@tabla@", "accountxsubsidio", $consulta);
                    $trans = DB_query($consulta, $db, "");
                    if (!is_null($trans)) {
                        while ($myrow = DB_fetch_array($trans)) {
                            $datos["tipoS"] == 0 ?
                            $info[] = array(
                                 "Clave" => $myrow["clave"],
                                "Descripcion" => $myrow["descripcion"],
                                'Cuenta' => $myrow["accountcode"],
                                'Estatus' => $myrow["estatus"] == 1 ? "Activo" : "Inactivo")
                            : $info[] = array(
                                "CP" => $myrow["cp"],
                                    "Descripcion" => $myrow["descripcion"],
                                'Cuenta' => $myrow["accountcode"],
                                'Estatus' => $myrow["estatus"] == 1 ? "Activo" : "Inactivo"
                            );
                        }
                        $result = true;
                        $insertCuenta = "";
                        $nuevaCuenta = "";
                        $tipoCuenta = "";
                        $agregarCuenta = false;
                        
                        $contenido = array("datos" => $info);
                    }else{
                        $Mensaje = "No se puedo ejecutar consulta.";
                        $result = false;
                    }
                }
            }
        }
        
        break;
}


$dataObj = array('info' =>"", 'contenido' => $contenido, 'result' => $result, 'RootPath' => "", 'ErrMsg' => $error, 'Mensaje' => $Mensaje, 'AgregarCuenta' => $agregarCuenta,
        'nuevaCuenta' => $nuevaCuenta, 'insertCuenta' => $insertCuenta);
echo json_encode($dataObj);

