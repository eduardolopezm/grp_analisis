<?php
/**
 * Captura Conciliacion Bancaria
 *
 * @category Panel
 * @package ap_grp
 * @author Jose Raul Lopez Vazquez <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 21/07/2018
 * Fecha Modificación: 21/07/2018
 * Modelo para el panel de Captura Aviso de Reintegros
 */

$PageSecurity = 1;
$PathPrefix = '../';
$PathPrefix2 = '../../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
$funcion=501;
include $PathPrefix."includes/SecurityUrl.php";
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";

$insertarArchivos = '';
//$idbanksS = 0;

//define('SUBIDAARCHIVOS', realpath(dirname(__FILE__)) . $PathPrefix2 . 'archivosEstadosCuentas/');
define('SUBIDAARCHIVOS', realpath(dirname(__FILE__)) . '/' . $PathPrefix . 'archivosEstadosCuentas/');
header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

// Variables Tipo GET

//if($_SESSION['UserID'] == 'desarrollo'){
/* echo '<br>P:'.GetPeriod($_POST['DefaultReceivedDate'], $db);
 echo '<br>P:'.$PeriodNo;
 echo '<br>Fecha:'.$_POST['DefaultReceivedDate'];
echo "\n ".
*/

//  print_r($querySQL);
//  echo "\n\n";
//  exit();
// }

if(isset($_GET['option'])) {

    $option = $_GET['option'];

    if ($option == 'listMonth') {

        $info = array();
        $listMes = "SELECT u_mes, mes FROM cat_Months";

        $ErrMsg = "No se obtuvo los Registros";

        $TransResult = DB_query($listMes, $db, $ErrMsg);

        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array('id' => $myrow['u_mes'], 'mes' => $myrow['mes']);
        }

        $list = array('month' => $info);
        $result = true;

        $dataObjMonth = array('meses' => $list, 'result' => $result);
        echo json_encode($dataObjMonth);

    }

    if($option == 'listAnho'){

        $info = array();
        $listMes = "SELECT DISTINCT DATE_FORMAT(lastdate_in_period, '%Y') AS anhos FROM periods";

        $ErrMsg = "No se obtuvo los Registros";

        $TransResult = DB_query($listMes, $db, $ErrMsg);

        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array('id' => $myrow['anhos'], 'anho' => $myrow['anhos']);
        }

        $list = array('years' => $info);
        $result = true;

        $dataObjYear = array('year' => $list, 'result' => $result);
        echo json_encode($dataObjYear);

    }

 if($option == 'listBank'){

        $info = array();

      /*  $SQL="SELECT
           distinct bankaccounts.accountcode as cuenta,
           bankaccountname as banco,
           bankaccounts.currcode as moneda
           FROM bankaccounts, chartmaster, tagsxbankaccounts, sec_unegsxuser
           WHERE bankaccounts.accountcode=chartmaster.accountcode 
           AND bankaccounts.accountcode = tagsxbankaccounts.accountcode 
           AND tagsxbankaccounts.tagref = sec_unegsxuser.tagref 
           AND sec_unegsxuser.userid = '". $_SESSION['UserID'] ."'";

        $SQL = $SQL . ' GROUP BY bankaccountname,
            bankaccounts.accountcode,
            bankaccounts.currcode';


        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ( $myrow = DB_fetch_array($TransResult) ){
            $info[] = array('banco' =>$myrow['banco'],'cuenta' =>$myrow['cuenta']);

        }

        $contenido = array('dtaBanks' => $info);
        $result = true;*/


        $SQL = "SELECT bank_id, bank_shortdescription, bank_active FROM banks WHERE bank_active = 1";

        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ( $myrow = DB_fetch_array($TransResult) ){
            $info[] = array('id'=>$myrow['bank_id'], 'name'=>$myrow['bank_shortdescription']);

        }

        $contenido = array('dtaBanks' => $info);
        $result = true;


        $dataObjBanks = array('banks' => $contenido, 'result' => $result);
        echo json_encode($dataObjBanks);
    }


    if($option == 'listAccount'){

        $info = array();
        $idBank = $_GET['idBanks'];

        $SQL = "SELECT accountcode, bankaccountnumber, bankid FROM bankaccounts WHERE bankid = '".$idBank."'";

        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ( $myrow = DB_fetch_array($TransResult) ){
            $info[] = array('id'=>$myrow['accountcode'], 'number'=>$myrow['bankaccountnumber']);

        }

        $contenido = array('dtaBanksacounts' => $info);
        $result = true;


        $dataObjBanks = array('accounts' => $contenido, 'result' => $result);
        echo json_encode($dataObjBanks);

    }

     if($option == 'elaborated'){

        $urs = $_GET['urs'];

        $info = array();

        $SQL = "SELECT  tb_detalle_firmas.id_nu_detalle_firmas as id,CONCAT(tb_detalle_firmas.titulo,' ',tb_empleados.ln_nombre,' ',tb_empleados.sn_primer_apellido ,' ',tb_empleados.sn_segundo_apellido) as firmante,
                tb_detalle_firmas.informacion, tb_reporte_firmas.id_dafault
                FROM tb_cat_reportes_conac tb_cat_firmas
                LEFT JOIN tb_reportes_conac_firmas tb_conf_firmas on tb_cat_firmas.id_nu_reportes_conac = tb_conf_firmas.id_nu_reportes_conac and tb_conf_firmas.ur = '".$urs."'
                LEFT JOIN tb_reporte_firmas on tb_conf_firmas.id_nu_reportes_conac_firmas = tb_reporte_firmas.id_nu_reportes_conac_firmas
                LEFT JOIN tb_detalle_firmas on tb_reporte_firmas.id_nu_detalle_firmas  = tb_detalle_firmas.id_nu_detalle_firmas
                LEFT JOIN tb_empleados ON tb_detalle_firmas.id_nu_empleado = tb_empleados.id_nu_empleado
                WHERE sn_tipo = 'conciliacion-elaboro'";


        $ErrMsg = "No se obtuvieron los capitulos de las partidas presupuestales";
        //echo $SQL;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array( 'id' => $myrow ['id'], 'descripcion' => $myrow ['firmante'],'default' => $myrow['id_dafault']);
        }
        //echo "aqui";

        $contenido = array('datos' => $info);
        //print_r($contenido);
        $result = true;


        $dataObjElab = array('elaboro' => $contenido, 'result' => $result);
        echo json_encode($dataObjElab);

    }

     if($option == 'valid'){

        $urs = $_GET['urs'];

        $info = array();

        $SQL = "SELECT  tb_detalle_firmas.id_nu_detalle_firmas as id,CONCAT(tb_detalle_firmas.titulo,' ',tb_empleados.ln_nombre,' ',tb_empleados.sn_primer_apellido ,' ',tb_empleados.sn_segundo_apellido) as firmante,
                tb_detalle_firmas.informacion, tb_reporte_firmas.id_dafault
                FROM tb_cat_reportes_conac tb_cat_firmas
                LEFT JOIN tb_reportes_conac_firmas tb_conf_firmas on tb_cat_firmas.id_nu_reportes_conac = tb_conf_firmas.id_nu_reportes_conac and tb_conf_firmas.ur = '".$urs."'
                LEFT JOIN tb_reporte_firmas on tb_conf_firmas.id_nu_reportes_conac_firmas = tb_reporte_firmas.id_nu_reportes_conac_firmas
                LEFT JOIN tb_detalle_firmas on tb_reporte_firmas.id_nu_detalle_firmas  = tb_detalle_firmas.id_nu_detalle_firmas
                LEFT JOIN tb_empleados ON tb_detalle_firmas.id_nu_empleado = tb_empleados.id_nu_empleado
                WHERE sn_tipo = 'conciliacion-valido'";


        $ErrMsg = "No se obtuvieron los capitulos de las partidas presupuestales";
        //echo $SQL;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array( 'id' => $myrow ['id'], 'descripcion' => $myrow ['firmante'],'default' => $myrow['id_dafault']);
        }
        //echo "aqui";

        $contenido = array('datos' => $info);
        //print_r($contenido);
        $result = true;


        $dataObjValid = array('valido' => $contenido, 'result' => $result);
        echo json_encode($dataObjValid);

    }

    if($option == 'authe'){

        $urs = $_GET['urs'];

        $info = array();

        $SQL = "SELECT  tb_detalle_firmas.id_nu_detalle_firmas as id,CONCAT(tb_detalle_firmas.titulo,' ',tb_empleados.ln_nombre,' ',tb_empleados.sn_primer_apellido ,' ',tb_empleados.sn_segundo_apellido) as firmante,
                tb_detalle_firmas.informacion, tb_reporte_firmas.id_dafault
                FROM tb_cat_reportes_conac tb_cat_firmas
                LEFT JOIN tb_reportes_conac_firmas tb_conf_firmas on tb_cat_firmas.id_nu_reportes_conac = tb_conf_firmas.id_nu_reportes_conac and tb_conf_firmas.ur = '".$urs."'
                LEFT JOIN tb_reporte_firmas on tb_conf_firmas.id_nu_reportes_conac_firmas = tb_reporte_firmas.id_nu_reportes_conac_firmas
                LEFT JOIN tb_detalle_firmas on tb_reporte_firmas.id_nu_detalle_firmas  = tb_detalle_firmas.id_nu_detalle_firmas
                LEFT JOIN tb_empleados ON tb_detalle_firmas.id_nu_empleado = tb_empleados.id_nu_empleado
                WHERE sn_tipo = 'conciliacion-autorizo'";


        $ErrMsg = "No se obtuvieron los capitulos de las partidas presupuestales";
        //echo $SQL;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array( 'id' => $myrow ['id'], 'descripcion' => $myrow ['firmante'],'default' => $myrow['id_dafault']);
        }
        //echo "aqui";

        $contenido = array('datos' => $info);
        //print_r($contenido);
        $result = true;


        $dataObjAuth = array('autorizo' => $contenido, 'result' => $result);
        echo json_encode($dataObjAuth);

    }


    // Buaqueda de Datos Bancarios Para la Conciliacion

 if($option == 'searchDataBanks'){

        try{

            $ur = mysqli_real_escape_string($db, $_GET['ur']);
            $ue = mysqli_real_escape_string($db, $_GET['ue']);
            $year = mysqli_real_escape_string($db, $_GET['year']);
            $banks = mysqli_real_escape_string($db, $_GET['banks']);
            $account = $_GET['account'];
            $mount = mysqli_real_escape_string($db, $_GET['mount']);
            $dateCap = $_GET['dateCap'];
            $StarDate = $_GET['dayini'];
            $EndDate = $_GET['dayend'];
            $sfirco = $_GET['sfirco'];
            $sbank = $_GET['sbank'];

            $sqlWhere = "";
            // $columnasNombres = '';
            // $columnasNombresGrid = '';

            /* if ($banks != '') {
                 $sqlWhere .= " AND  = ".$banks."";
             }*/

            /*    if ($account != '') {
                    $sqlWhere .= " AND  = ".$account."";
             }*/

            if ($ur != '') {
                $sqlWhere .= " AND banktrans.tagref = '".$ur."'";
            }

            if ($ue != '') {
                $sqlWhere .= " AND banktrans.ln_ue = '".$ue."'";
            }

            if ($year != '') {
                $sqlWhere .= " AND DATE_FORMAT(banktrans.transdate, '%Y') = ".$year."";
            }

            if($mount != '-1'){
                $sqlWhere .= " AND DATE_FORMAT(banktrans.transdate, '%m') = ".$mount."";
            }

            if($mount == '-1'){

                if (!empty($StarDate) && !empty($EndDate)) {
                    $StarDate = date_create($StarDate);
                    $StarDate = date_format($StarDate, 'Y-m-d');

                    $EndDate = date_create($EndDate);
                    $EndDate = date_format($EndDate, 'Y-m-d');

                    $sqlWhere .= " AND banktrans.transdate between '" . $StarDate . " 00:00:00' AND '" . $EndDate . " 23:59:59'";

                } elseif (!empty($StarDate)) {
                    $StarDate = date_create($StarDate);
                    $StarDate = date_format($StarDate, 'Y-m-d');

                    $sqlWhere .= " AND banktrans.transdate >= '" . $StarDate . " 00:00:00'";

                } elseif (!empty($EndDate)) {
                    $EndDate = date_create($EndDate);
                    $EndDate = date_format($EndDate, 'Y-m-d');

                    $sqlWhere .= " AND banktrans.transdate <= '" . $EndDate . " 23:59:59'";
                }

            }


            $info = array();


            /*$querySQL = "SELECT banktrans.banktransid, banktrans.tagref, banktrans.ln_ue, banktrans.chequeno, banktrans.banktranstype, banktrans.ref, banktrans.amount, banktrans.transdate, banktrans.bankact,
                               supptrans.transno AS transnoSUPP, supptrans.type AS typeSUPP, banktrans.transno AS transnoBAN, banktrans.nu_type AS typeBAN, supptrans.txt_referencia
                        FROM banktrans
                        LEFT JOIN supptrans ON supptrans.transno = banktrans.transno AND supptrans.type = banktrans.nu_type           
                        WHERE banktrans.bankact = '".$account."' ".$sqlWhere." AND banktrans.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                        ORDER BY banktrans.transdate ASC
                        UNION
                        SELECT banktrans.banktransid, banktrans.tagref, banktrans.ln_ue, banktrans.chequeno, banktrans.banktranstype, banktrans.ref, banktrans.amount, banktrans.transdate, banktrans.bankact,
                               banktrans.transno AS transnoBAN, banktrans.nu_type AS typeBAN, tb_radicacion.num_transferencia AS txt_referencia
                        FROM banktrans
                        LEFT JOIN tb_radicacion ON tb_radicacion.folio = banktrans.transno
                        WHERE banktrans.bankact = '".$account."' ".$sqlWhere." AND banktrans.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'";*/

            $querySQL = "SELECT banktrans.banktransid, banktrans.tagref, banktrans.ln_ue, banktrans.chequeno,
                                banktrans.banktranstype, banktrans.ref, banktrans.amount, banktrans.transdate, 
                                banktrans.bankact, 
                                supptrans.transno AS transnoSUPP,
                                supptrans.type AS typeSUPP, 
                                banktrans.transno AS transnoBAN,
                                banktrans.nu_type AS typeBAN, supptrans.txt_referencia, 
                                supptrans.txt_clave_rastreo, tb_radicacion.num_transferencia AS num_radicado_transfer, tb_ministracion.num_transferencia AS num_ministrado_transfer 
                         FROM banktrans 
                         LEFT JOIN supptrans ON supptrans.transno = banktrans.transno AND supptrans.type = banktrans.nu_type 
                         LEFT JOIN tb_radicacion ON tb_radicacion.folio = banktrans.transno AND banktrans.nu_type = 292 
                         LEFT JOIN tb_ministracion ON tb_ministracion.folio = banktrans.transno AND banktrans.nu_type = 291
                         WHERE banktrans.bankact = '".$account."' ".$sqlWhere." 
                         AND banktrans.batchconciliacion IS NULL 
                         AND 
                            CASE 
                               WHEN banktrans.nu_type = 291
                                THEN banktrans.amount > 0
                                  WHEN banktrans.nu_type = 292
                                    THEN banktrans.amount < 0
                               ELSE
                                 banktrans.amount    
                            END
                         AND banktrans.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                         ORDER BY banktrans.transdate ASC";


                    /*     banktrans.bankact = '1.1.1.2.1.09.0001.0002.0001'   AND banktrans.batchconciliacion = NULL AND tb_radicacion.nu_anio_fiscal  AND tb_ministracion.nu_anio_fiscal
                         AND banktrans.tagref = 'I6L'
                         AND banktrans.ln_ue = 09
                         AND DATE_FORMAT(banktrans.transdate, '%Y') = 2018
                         AND DATE_FORMAT(banktrans.transdate, '%m') = 10
                         AND banktrans.nu_anio_fiscal = '2018'
                         ";*/

                        //LEFT JOIN tb_radicacion ON tb_radicacion.folio = banktrans.transno
                        //LEFT JOIN tb_ministracion ON tb_ministracion.folio = banktrans.transno  // supptrans.txt_clave_rastreo   ORDER BY banktrans.transdate ASC AND tb_radicacion.estatus = 5 AND tb_ministracion.estatus = 5

            $ErrMsg = "Error Al Consultar la base de Datos";
            $queryResult = DB_query($querySQL, $db, $ErrMsg);

            while ($myrow = DB_fetch_array($queryResult)) {

                if($myrow['amount']<0){
                    $monto=($myrow['amount'])*(-1);
                }else{
                    $monto= $myrow['amount'];
                }


                if($myrow['typeBAN'] == 292){
                    $referens = $myrow['num_radicado_transfer'];
                }else{
                    if($myrow['typeBAN'] == 291){
                        $referens = $myrow['num_ministrado_transfer'];
                    }else{
                        $referens = $myrow['txt_referencia'];
                    }
                }


                  /*  if($myrow['num_transferencia'] != null || $myrow['num_transferencia'] != ''){
                        $referens = $myrow['num_transferencia'];
                    }else{
                        $referens = $myrow['txt_referencia'];
                    }
                  */

                $fechDate = date("d-m-Y", strtotime($myrow['transdate']));

                $info[] = array(
                    'id' => $myrow['banktransid'],
                    'fecha' => $fechDate,
                    'Ur' => $myrow['tagref'],
                    'Ue' => $myrow['ln_ue'],
                    'folio' => $myrow['chequeno'],
                    'transaccion' => $myrow['banktranstype'],
                    'referencia' => $myrow['ref'],
                    'importe' => $monto,
                    'supptransTransno' => $myrow['transnoSUPP'],
                    'supptransType' => $myrow['typeSUPP'],
                    'banktransTransno' => $myrow['transnoBAN'],
                    'banktransNutype' => $myrow['typeBAN'],
                    'clave_rastreo' => $myrow['txt_clave_rastreo'],
                    'referenciaSUPP' => $referens,
                    'ctaCont' => $myrow['bankact'],
                    'qtytrue' => $myrow['amount']

                );

            }





            $contenido = array('dtaTable' => $info);
            $result = true;

            $dataObjTable = array('dtaTableContent' => $contenido, 'result' => $result);
            echo json_encode($dataObjTable);

        }catch (Exception $error){

            $msg = array('message' => $error->getMessage(), 'tipo'=>'error');
            echo json_encode($msg);

        }

 }// Fin Search

    // Folio para Conciliacion Bancaria

    if($option == 'secuenceFolioConciliation'){

         $queryFolio = "SELECT * FROM systypesinvtrans WHERE typeid = 2200";

         $ErrMsg = "Error Al Consultar la base de Datos";
         $queryResultFolio = DB_query($queryFolio, $db, $ErrMsg);

         while ($myrow = DB_fetch_array($queryResultFolio)) {
               $Nfolio = $myrow['typeno'];
         }

         $Nfolio = $Nfolio + 1;

         $dtaNextFolio = array('folio' => $Nfolio);
         echo json_encode($dtaNextFolio);

    }

} // fin GET Option General


if(isset($_POST['fileOption'])){

    if($_POST['fileOption'] == 'upfiles'){

        $file_name = "";

        $file_name = "file"."_".date('Ymd_his')."_"."ccl ";
        $name_visible = $_FILES['inp']['name'];
        $file_tmp =$_FILES['inp']['tmp_name'];

        $extension = end(explode(".", $_FILES['inp']['name']));

        $file_name=basename(str_replace(" ", "", $file_name));
        $name_visible=str_replace(" ", "", $name_visible);
        $file_type = $_FILES['inp']['type'];

        //$file_name.".".$extension

        $insertarArchivos = "('".$_SESSION['UserID']."','".$file_type."','".$file_name.".".$extension."','archivosEstadosCuentas/".$file_name.".".$extension."','".$funcion."',0,'".$name_visible."','".$idbanksS."'),";

        $searchName = $file_name.".".$extension;
        moverArchivo($file_name.".".$extension,$_FILES['inp']['tmp_name'],SUBIDAARCHIVOS);

        $insertarArchivos = substr($insertarArchivos, 0, -1);

        if($insertarArchivos !=""){

            $SQLFILE = "INSERT INTO tb_archivos (ln_userid, sn_tipo, ln_nombre_interno_archivo, txt_url, nu_funcion, nu_tipo_sys, ln_nombre, nu_trasnno) VALUES ".$insertarArchivos;

            $ErrMsg = "Problemas al guardar el archivos de radicacion.";
            $result = DB_query($SQLFILE, $db, $ErrMsg);

            if($result){

                $qr = "SELECT * FROM tb_archivos WHERE ln_nombre_interno_archivo = '".$searchName."'";

                $ErrMsg = "Error Al Consultar la base de Datos";
                $trquery = DB_query($qr, $db, $ErrMsg);

                while ($myrow = DB_fetch_array($trquery)) {
                   // global $idbanksS;
                    $idbanksS = $myrow['nu_id_documento'];
                }

            }
        }

        $dataObjSearch = $idbanksS;
        echo json_encode($dataObjSearch);

    }

}

function moverArchivo($docName, $docTemp, $ubicacion){
    # comprobación y creación de la carpeta de ser necesario
    if(!file_exists($ubicacion)){
        crearCarpeta($ubicacion);
    }
    $name = $ubicacion . $docName;
    # comprobación de archivo subido
    if(is_uploaded_file($docTemp)) {
        # cambio de ubicación del archivo
        $conf = move_uploaded_file($docTemp, $name);
        @chown($name, 'root');
        @chgrp($name, 'root');
        return $conf;

    }
    return false;
}


function crearCarpeta($directorio){
    # crea el directorio indicado
    @mkdir($directorio);
}



if(isset($_POST['opt'])){

    if($_POST['opt'] == 'storeAccount'){

        try{

            $saveArray = $_POST['datafileBanks'];
            $folios = $_POST['fol'];
            $updateBanks = $_POST['dataBanks'];

            $filesURL = $_POST['urlgfiles'];

            $DoID = $_POST['DocumentID'];

            $mes = $_POST['ln_month'];
            $anho = $_POST['ln_anhos'];
            $inidate = $_POST['stardates'];
            $findate = $_POST['enddates'];
            $elabora = $_POST['elaboroCon'];
            $valid = $_POST['validoCon'];
            $auths = $_POST['autorizoCon'];

            for($x=0;$x<count($saveArray['conciliation']);$x++){

                $Fech = date_create($saveArray['conciliation'][$x]['fecha']);
                $Fech = date_format($Fech, 'Y-m-d');

                $Fec = date_create($saveArray['conciliation'][$x]['fechacambio']);
                $Fec = date_format($Fec, 'Y-m-d H:i:s');

                $FecConta = date_create($saveArray['conciliation'][$x]['fechacontable']);
                $FecConta = date_format($FecConta, 'Y-m-d');

                $sqlInsert = "INSERT INTO estadoscuentabancarios (legalid, cuenta, Fecha, Concepto, Retiros, depositos, conciliado, usuario, fechaCambio, tagref, batchconciliacion, fechacontable, nu_referencia) 
                           VALUES ('".$saveArray['conciliation'][$x]['legalid']."','".$saveArray['conciliation'][$x]['cuenta']."','".$Fech."','".$saveArray['conciliation'][$x]['Concepto']."','".$saveArray['conciliation'][$x]['retiros']."','".$saveArray['conciliation'][$x]['depositos']."','".$saveArray['conciliation'][$x]['conciliado']."','".$_SESSION['UserID']."','".$Fec."','".$saveArray['conciliation'][$x]['ur']."','".$saveArray['conciliation'][$x]['folioConc']."','".$FecConta."','".$saveArray['conciliation'][$x]['referencia']."')";

                $ErrMsg = "No se pudo almacenar la información";
                $TransResult = DB_query($sqlInsert, $db, $ErrMsg);

            }


            if($TransResult == true){

                for($j=0;$j<count($updateBanks['dtaTable']);$j++){

                    $SQLUpdate = "UPDATE banktrans SET batchconciliacion = '".$folios."' WHERE banktransid = '".$updateBanks['dtaTable'][$j]['id']."' ";

                    $ErrMsg = "No se pudo almacenar la información";
                    $TransResultUpdate = DB_query($SQLUpdate, $db, $ErrMsg);

                }

                $updateFolio = "UPDATE systypesinvtrans SET typeno = '".$folios."' WHERE typeid = 2200 ";

                $ErrMsg = "No se pudo almacenar la información";
                $TranstUpdateFolio = DB_query($updateFolio, $db, $ErrMsg);

                $updateFiles = "UPDATE tb_archivos SET nu_trasnno = '".$folios."' WHERE nu_id_documento = ".$DoID." AND nu_funcion = 501 ";

                $ErrMsg = "No se pudo almacenar la información";
                $TranstUpdateFile = DB_query($updateFiles, $db, $ErrMsg);

                 if($mes != '-1'){
                    //$months = $mes;

                    $monthSearch = $anho.'-'.$mes;

                      $sqlsearch = "SELECT lastdate_in_period FROM periods WHERE lastdate_in_period LIKE '%".$monthSearch."%'";

                    $ErrMsg = "Error Al Consultar la base de Datos";
                    $querys = DB_query($sqlsearch, $db, $ErrMsg);

                    while ($myrow = DB_fetch_array($querys)) {
                        // global $idbanksS;
                        $finalDD = $myrow['lastdate_in_period'];
                    }

                    $ini = $monthSearch.'-01';
                    $months = $mes;

                }else{

                    $months = '';
                    $anho = '';
                    $ini = date_create($inidate);
                    $ini = date_format($ini, 'Y-m-d');

                    $finalDD = date_create($findate);
                    $finalDD = date_format($finalDD, 'Y-m-d');
                }

                $sqlInsertHeader = "INSERT INTO tb_conciliacion_bancaria (estado_id, nu_mes, fecha_inicio, fecha_fin, nu_elaboro, nu_valido, nu_autorizo, fecha_captura, nu_anho) VALUES ('".$folios."', '".$months."', '".$ini."', '".$finalDD."','".$elabora."', '".$valid."', '".$auths."', '".$FecConta."', '".$anho."')";

                $ErrMsg = "No se pudo almacenar la información";
                $resultTrans = DB_query($sqlInsertHeader, $db, $ErrMsg);

            }

            $msg = array('message' => 'Registros Conciliados Correctamente','tipo'=>'success');
            echo json_encode($msg);

        }catch (Exception $error){


            $msg = array('message' => $error->getMessage(), 'tipo'=>'error');
            echo json_encode($msg);

        }


    }

}

