<?php

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
$funcion=2200;
include $PathPrefix."includes/SecurityUrl.php";
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

$SQL = '';

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);


// Variables Tipo GET

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

        $dataObjTipoReintegro = array('meses' => $list, 'result' => $result);
        echo json_encode($dataObjTipoReintegro);

    }

    if($option == 'search'){

        try{

            $Ur = $_GET['ur'];
            $Ue = $_GET['ue'];

            $StarDate = $_GET['starDate'];
            $EndDate = $_GET['endDate'];
            $Folio = $_GET['Folio'];
            $Month = $_GET['month'];

            $sqlWhere = "";
            $columnasNombres = '';
            $columnasNombresGrid = '';

            if ($Ur != '') {
                $sqlWhere .= " estadoscuentabancarios.tagref IN (" . $Ur . ")";
            }else{
                $sqlWhere .= " estadoscuentabancarios.tagref IN ('I6L')";
            }

            if ($Ue != '') {
                $sqlWhere .= " AND banktrans.ln_ue IN (" . $Ue . ")";
            }


            if ($Folio != '') {
                $sqlWhere .= " AND estadoscuentabancarios.batchconciliacion = " . $Folio . "";
            }



            if (!empty($StarDate) && !empty($EndDate)) {
                $StarDate = date_create($StarDate);
                $StarDate = date_format($StarDate, 'Y-m-d');

                $EndDate = date_create($EndDate);
                $EndDate = date_format($EndDate, 'Y-m-d');

                $sqlWhere .= " AND estadoscuentabancarios.fechacambio between '" . $StarDate . " 00:00:00' AND '" . $EndDate . " 23:59:59'";

            } elseif (!empty($StarDate)) {
                $StarDate = date_create($StarDate);
                $StarDate = date_format($StarDate, 'Y-m-d');

                $sqlWhere .= " AND estadoscuentabancarios.fechacambio >= '" . $StarDate . " 00:00:00'";

            } elseif (!empty($EndDate)) {
                $EndDate = date_create($EndDate);
                $EndDate = date_format($EndDate, 'Y-m-d');

                $sqlWhere .= " AND estadoscuentabancarios.fechacambio <= '" . $EndDate . " 23:59:59'";
            }

            // LEFT JOIN DATE_FORMAT(periods.lastdate_in_period, '%m')  ORDER BY estadoscuentabancarios.batchconciliacion DESC ";


            $info = array();

          /*  $querySQL = "SELECT estadoscuentabancarios.tagref, estadoscuentabancarios.batchconciliacion, estadoscuentabancarios.cuenta,estadoscuentabancarios.fechacambio,
                         banktrans.bankact, banktrans.ln_ue, banktrans.batchconciliacion, bankaccounts.accountcode, bankaccounts.bankaccountname, banktrans.transdate 
                         FROM estadoscuentabancarios 
                         LEFT JOIN banktrans ON banktrans.batchconciliacion = estadoscuentabancarios.batchconciliacion
                         LEFT JOIN bankaccounts ON bankaccounts.accountcode = estadoscuentabancarios.cuenta 
                         LEFT JOIN
                         WHERE " . $sqlWhere . "
                         GROUP BY banktrans.batchconciliacion";*/

            $querySQL = "SELECT  estadoscuentabancarios.tagref, estadoscuentabancarios.batchconciliacion AS Idexce, estadoscuentabancarios.cuenta, estadoscuentabancarios.fechacambio, banktrans.bankact, banktrans.ln_ue,
                         banktrans.batchconciliacion AS Idbank, bankaccounts.accountcode, bankaccounts.bankaccountname, bankaccounts.bankaccountnumber, banktrans.transdate, mesperiod.periodno, tb_archivos.txt_url
                         FROM estadoscuentabancarios 
                         LEFT JOIN banktrans ON banktrans.batchconciliacion = estadoscuentabancarios.batchconciliacion 
                         LEFT JOIN bankaccounts ON bankaccounts.accountcode = estadoscuentabancarios.cuenta
                         LEFT JOIN tb_archivos ON tb_archivos.nu_trasnno = banktrans.batchconciliacion AND tb_archivos.nu_funcion = 501
                         LEFT JOIN periods mesperiod ON DATE_FORMAT(mesperiod.lastdate_in_period, '%m') = DATE_FORMAT(banktrans.transdate, '%m')
                         JOIN sec_unegsxuser ON sec_unegsxuser.tagref = banktrans.tagref AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
                         JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = banktrans.tagref AND tb_sec_users_ue.ue = banktrans.ln_ue AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'                            
                         WHERE " . $sqlWhere . " AND DATE_FORMAT(mesperiod.lastdate_in_period, '%Y') = DATE_FORMAT(banktrans.transdate, '%Y') 
                         AND banktrans.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."'
                         GROUP BY banktrans.batchconciliacion";

            $ErrMsg = "Error Al Consultar la base de Datos";
            $TransResult = DB_query($querySQL, $db, $ErrMsg);

            while ($myrow = DB_fetch_array($TransResult)) {

                $urlImpresion = "&tagref=>" . $myrow['tagref'] . "&ln_ue=>" . $myrow['ln_ue'] . "&folio=>" . $myrow['Idbank'] . "&clave=>" . $myrow['accountcode'];

                $encImp = new Encryption;
                $urlImp = $encImp->encode($urlImpresion);
                $ligaImp = "URL=" . $urlImp;

                ///
                ///
                $encDown = new Encryption;
                $urlDown = $encDown->encode($urlImpresion);
                $ligaDown = $urlDown;
               // $period = GetPeriod(date('d/m/Y'), $db);
                $impresion = '<a type="button" id="btnImprimir'.$myrow['folio'].'" name="btnImprimir'.$myrow['folio'].'" href="reporte_conciliacion_bancaria.php?'.$ligaImp.'" title="Imprimir Detalle" target="_blank"><span class="glyphicon glyphicon glyphicon-print"></span></a>';

                $downloadFiles = '<a type="button" id="btnDown'.$myrow['folio'].'" name="btnDown'.$myrow['folio'].'" href="'.$myrow['txt_url'].'" title="Descargar Archivo"><span class="glyphicon glyphicon-save-file"></span></a>';

                $info[] = array(

                    'idCheck' => false,
                    'ur' => $myrow['tagref'],
                    'ue' => $myrow['ln_ue'],
                    'fecha_captura' => date('d-m-Y', strtotime($myrow['fechacambio'])),
                    //'folio' => $myrow['id'],
                    'folio' => $myrow['Idbank'],
                    'folioExcel' => $myrow['Idexce'],
                    'banco_origen' => $myrow['bankaccountname'],
                    'cuenta_clave' => $myrow['bankaccountnumber'],
                    'periodo' => $myrow['periodno'],
                    'imprimir' => $impresion,
                    'download' => $downloadFiles

            );

            }

            // Columnas para el GRID 'cuenta_clave' => $myrow['accountcode'],
            $columnasNombres .= "[";
            $columnasNombres .= "{ name: 'idCheck', type: 'bool'},";
            $columnasNombres .= "{ name: 'ur', type: 'string' },";
            $columnasNombres .= "{ name: 'ue', type: 'string' },";
            $columnasNombres .= "{ name: 'fecha_captura', type: 'string' },";
            $columnasNombres .= "{ name: 'folio', type: 'string' },";
            $columnasNombres .= "{ name: 'folioExcel', type: 'string' },";
            $columnasNombres .= "{ name: 'banco_origen', type: 'string' },";
            $columnasNombres .= "{ name: 'cuenta_clave', type: 'string' },";
            $columnasNombres .= "{ name: 'periodo', type: 'string' },";
            $columnasNombres .= "{ name: 'imprimir', type: 'string' },";
            $columnasNombres .= "{ name: 'download', type: 'string' }";
            $columnasNombres .= "]";

            $columnasNombresGrid .= "[";
            $columnasNombresGrid .= " { text: '', datafield: 'idCheck', width: '3%', editable: true, editable: true, cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
            $columnasNombresGrid .= " { text: 'UR', datafield: 'ur', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
            $columnasNombresGrid .= " { text: 'UE', datafield: 'ue', width: '8%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
            $columnasNombresGrid .= " { text: 'Fecha Captura', datafield: 'fecha_captura', width: '10%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
            $columnasNombresGrid .= " { text: 'Folio', datafield: 'folio', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
            $columnasNombresGrid .= " { text: 'Folio', datafield: 'folioExcel', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
            $columnasNombresGrid .= " { text: 'Banco de Origen', datafield: 'banco_origen', width: '16%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
            $columnasNombresGrid .= " { text: 'Cuenta Clabe', datafield: 'cuenta_clave', width: '20%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
            $columnasNombresGrid .= " { text: 'Periodo Conciliado', datafield: 'periodo', width: '13%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
            $columnasNombresGrid .= " { text: 'Imprimir', datafield: 'imprimir', width: '9%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
            $columnasNombresGrid .= " { text: 'Descargar', datafield: 'download', width: '9%', editable: false, cellsalign: 'center', align: 'center', hidden: false }";
            $columnasNombresGrid .= "]";


            $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)) . '_' . date('dmY');

            $result = true;
            $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid,'nombreExcel' => $nombreExcel);

            $dataObjSearch = array('contenido' => $contenido);
            echo json_encode($dataObjSearch);


        }catch (Exception $error){

        }

    }

} // fin GET Option General

