<?php
/**
 * Reporte de auxiliar de bancos.
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link modelo/auxiliarBancarioModelo.php
 * Fecha Creación: 11.06.17
 * Se genera el presente programa para la visualización de la información
 * del reporte del auxiliar de bancos.
 */
//

$PageSecurity = 1;
$PathPrefix = '../';
$funcion=2436;

//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include $PathPrefix . "includes/SecurityUrl.php";
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

header('Content-type: text/html; charset=ISO-8859-1');
$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

if(isset($_GET['optionSelect'])){
    if($_GET['optionSelect'] == 'listAccount'){


        $info = array();
        $idBank = $_GET['idBanks'];

        $SQL = "SELECT accountcode, bankaccountnumber, bankid FROM bankaccounts WHERE bankid = ".$idBank." ";

        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ( $myrow = DB_fetch_array($TransResult) ){
            $info[] = array('id'=>$myrow['accountcode'], 'number'=>$myrow['bankaccountnumber']);

        }

        $contenido = array('dtaBanksacounts' => $info);
        $result = true;


        $dataObjBanks = array('accounts' => $contenido, 'result' => $result);
        echo json_encode($dataObjBanks);

    }
}



if(isset($_GET['optionBank'])){

    if($_GET['optionBank'] == 'listBank'){

        $info = array();

        $SQL = "SELECT bank_id, bank_shortdescription, bank_active FROM banks WHERE bank_active = 1";

        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ( $myrow = DB_fetch_array($TransResult) ){
            $info[] = array('id'=>$myrow['bank_id'], 'name'=>$myrow['bank_shortdescription']);

        }

        $dtas = array('dtaBanks' => $info);
        $result = true;


        $dataObjBanks = array('banks' => $dtas, 'result' => $result);
        echo json_encode($dataObjBanks);

    }

}


/* ************************************ */

$option = $_POST['option'];

if($option == "obtenerAuxiliarBanco"){

	/*$SQL = "SELECT  banktrans.`banktransid`,
					banktrans.`type`,
					banktrans.`transno`,
					banktrans.`bankact`,
					banktrans.`ref`,
					banktrans.`amountcleared`,
					banktrans.`exrate`,
					banktrans.`functionalexrate`,
					date_format(banktrans.`transdate`,'%d-%m-%Y') as transdate,
					banktrans.`banktranstype`,
					banktrans.`amount`,
					banktrans.`currcode`,
					banktrans.`tagref`,
					banktrans.`beneficiary`,
					banktrans.`chequeno`,
					banktrans.`batchconciliacion`,
					banktrans.`usuario`,
					banktrans.`fechacambio`,
					banktrans.`matchperiodno`,
					banktrans.`numautorizacion`,
					banktrans.`fechabanco`,
					banktrans.`cuentadestino`,
					banktrans.`bancodestino`,
					banktrans.`rfcdestino`,
					banktrans.`ln_ue`,
					bankaccounts.`bankaccountname`
			FROM banktrans
			INNER JOIN bankaccounts ON banktrans.bankact = bankaccounts.accountcode
			WHERE 1=1 ";

	banktrans.tagref in ('I6L') AND banktrans.ln_ue in ('02') AND banktrans.transdate >= '2017-03-01' AND banktrans.transdate <= '2018-10-17' AND banktrans.bankact in ('1.1.1.2') AND chartdetailsbudgetbytag.cppt = 'M001' AND tb_cat_partidaspresupuestales_capitulo.ccap = '2'
            -- GROUP BY banktrans.transno
            ORDER BY banktrans.transdate DESC"

	*/

	$querySQL = '';

	$SQL = "SELECT DISTINCT banktrans.banktransid, banktrans.type, banktrans.transno, banktrans.bankact, banktrans.ref,
            banktrans.amountcleared, banktrans.exrate, banktrans.functionalexrate, date_format(banktrans.transdate,'%d-%m-%Y') as transdate, 
            banktrans.banktranstype, banktrans.amount, banktrans.currcode, banktrans.tagref, banktrans.beneficiary, banktrans.chequeno,
            banktrans.batchconciliacion, banktrans.usuario, banktrans.fechacambio, banktrans.matchperiodno, banktrans.numautorizacion,
            banktrans.`fechabanco`, banktrans.cuentadestino, banktrans.bancodestino, banktrans.rfcdestino, banktrans.ln_ue,
            bankaccounts.bankaccountname, bankaccounts.bankaccountnumber, supptrans.transno, supptrans.txt_referencia, supptrans.txt_clave_rastreo, banktrans.nu_type, chartdetailsbudgetbytag.cppt
            FROM banktrans 
            JOIN bankaccounts ON banktrans.bankact = bankaccounts.accountcode
            JOIN supptrans ON supptrans.transno = banktrans.transno AND supptrans.type = banktrans.nu_type
            JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.transno = banktrans.transno AND chartdetailsbudgetlog.type = banktrans.nu_type
            JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = chartdetailsbudgetlog.cvefrom
            JOIN tb_cat_partidaspresupuestales_partidaespecifica ON chartdetailsbudgetbytag.partida_esp = tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada
            JOIN tb_cat_partidaspresupuestales_capitulo ON tb_cat_partidaspresupuestales_partidaespecifica.ccap = tb_cat_partidaspresupuestales_capitulo.ccap
            WHERE 1=1";

	if(isset($_POST['selectUnidadNegocio']) and $_POST['selectUnidadNegocio'] !=""){
		$SQL .= " AND banktrans.tagref IN (".$_POST['selectUnidadNegocio'].") ";
	}

	if(isset($_POST['selectUnidadEjecutora']) and $_POST['selectUnidadEjecutora'] !=""){
		$SQL .= " AND banktrans.ln_ue IN (".$_POST['selectUnidadEjecutora'].") ";
	}

    if(isset($_POST['selectProgramaPresupuestal']) and $_POST['selectProgramaPresupuestal'] !=""){
        $SQL .= " AND chartdetailsbudgetbytag.cppt IN (".$_POST['selectProgramaPresupuestal'].") ";
    }

    if(isset($_POST['selectCapitulo']) and $_POST['selectCapitulo'] !=""){
        $SQL .= " AND tb_cat_partidaspresupuestales_capitulo.ccap IN (".$_POST['selectCapitulo'].") ";
    }

    if(isset($_POST['selectClave']) and $_POST['selectClave'] !=""){
        $SQL .= " AND banktrans.bankact IN (".$_POST['selectClave'].") ";
    }


	if(isset($_POST['dateDesde']) and $_POST['dateDesde'] !=""){
		$SQL .= " AND banktrans.transdate >= '".fnFormatoFechaYMD($_POST['dateDesde'],'-')."'";
	}

	if(isset($_POST['dateHasta']) and $_POST['dateHasta'] !=""){
		$SQL .= " AND banktrans.transdate <= '". fnFormatoFechaYMD($_POST['dateHasta'],'-'). "'";
	}

	$SQL .= " ORDER BY banktrans.banktransid ASC;";

	$ErrMsg = "No se obtuvieron los movimientos de auxiliar de bancos";
	//echo "sql:<pre>".$SQL;
    $TransResults = DB_query($SQL, $db, $ErrMsg);

    $abonos=0;
    $cargos=0;

    $tr = mysqli_num_rows($TransResults);
    $saldoINI = 0;
    $saldoFini = 0;

  //  $info = [];


    if($tr >= 1){

        $data =DB_fetch_array($TransResults);

        $qw="SELECT IFNULL(SUM(banktrans.amount),0) as qty, banktrans.banktransid as ids FROM banktrans WHERE banktrans.banktransid < ".$data['banktransid']." ";

        $ErrMsg = "No se obtuvieron los movimientos de auxiliar de bancos";
        //echo "sql:<pre>".$SQL;
        $rs = DB_query($qw, $db, $ErrMsg);

        $data2 = DB_fetch_array($rs);
        $saldoINI = $data2['qty'];

     /*    echo '<br>P:'.$tr;
         echo '<br>P:'.$data;
         echo '<br>Fecha:'.$data2;
         echo "\n ".
*/

    // print_r($data);
     //   exit();

    // for($i=0;$i<count($data);$i++){
       //  print_r($data[$i]);
        $TransResults = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResults)) {


            $abonos=0;
            $cargos=$myrow['amount'];

            $saldoFini = (float)$saldoINI + (float)$cargos;


            if($myrow ['amount'] > 0){
                $abonos=$myrow ['amount'];
                $cargos=0;
                $saldoFini = $saldoINI + $abonos;

            }


            $info[] = array(

                'banktransid' => $myrow['banktransid'],
                'ur' => $myrow['tagref'],
                'ue' => $myrow['ln_ue'],
                'pp' => $myrow['cppt'],
                'transdate' => $myrow['transdate'],
                'bankaccountname' => $myrow['bankaccountname'],
                'bankaccount' => $myrow['bankaccountnumber'],
                'concepto' => $myrow['ref'],
                'referencia' => $myrow['txt_referencia'],
                'folio' => $myrow['chequeno'],
                'folioExcel' => $myrow['banktransid'],
                'SaldoInicial' => abs(number_format($saldoINI, $_SESSION['DecimalPlaces'], '.', '')),
                'abonos' => abs(number_format($abonos, $_SESSION['DecimalPlaces'], '.', '')),
                'cargos' => abs(number_format($cargos, $_SESSION['DecimalPlaces'], '.', '')),
                'SaldoFinal' => abs(number_format($saldoFini, $_SESSION['DecimalPlaces'], '.', ''))

            );


            $saldoINI = $saldoFini;


        }


    }
    /*else{

        echo "No Hay Resultados";

    }*/


    /* Consultas Reintegros */



     if(isset($_POST['selectBanco']) and $_POST['selectBanco'] == ""){

             if(isset($_POST['selectUnidadNegocio']) and $_POST['selectUnidadNegocio'] !=""){
                 $querySQL .= " AND tb_refunds_notice.ur_id IN (".$_POST['selectUnidadNegocio'].") ";
             }

             if(isset($_POST['selectUnidadEjecutora']) and $_POST['selectUnidadEjecutora'] !=""){
                 $querySQL .= " AND tb_refunds_notice.ue_id IN (".$_POST['selectUnidadEjecutora'].") ";
             }

             if(isset($_POST['selectProgramaPresupuestal']) and $_POST['selectProgramaPresupuestal'] !=""){
                 $querySQL .= " AND chartdetailsbudgetbytag.cppt IN (".$_POST['selectProgramaPresupuestal'].") ";
             }

             if(isset($_POST['selectCapitulo']) and $_POST['selectCapitulo'] !=""){
                 $querySQL .= " AND tb_cat_partidaspresupuestales_capitulo.ccap IN (".$_POST['selectCapitulo'].") ";
             }


             if(isset($_POST['dateDesde']) and $_POST['dateDesde'] !=""){
                 $querySQL .= " AND tb_refunds_notice.issue_date >= '".fnFormatoFechaYMD($_POST['dateDesde'],'-')."'";
             }

             if(isset($_POST['dateHasta']) and $_POST['dateHasta'] !=""){
                 $querySQL .= " AND tb_refunds_notice.issue_date <= '". fnFormatoFechaYMD($_POST['dateHasta'],'-'). "'";
             }


            $SQLrefunds ="SELECT tb_refunds_notice.id, tb_refunds_notice.ur_id, tb_refunds_notice.ue_id, tb_refunds_notice.issue_date, DATE_FORMAT(tb_refunds_notice.auth_date,'%d-%m-%Y') AS authdate,
                                 tb_refunds_notice.refund_id, tb_refunds_notice.folio_viatics,
                                 tb_refunds_notice.folio_invoice_transfer, tb_refunds_notice.justification, tb_refunds_notice.status_refund,
                                 SUM(chartdetailsbudgetlog.qty) AS total, chartdetailsbudgetlog.description, chartdetailsbudgetlog.transno,
                                 chartdetailsbudgetlog.type, chartdetailsbudgetlog.period, tb_cat_unidades_ejecutoras.ue, tb_cat_refunds.name, chartdetailsbudgetlog.cvefrom, chartdetailsbudgetbytag.cppt
                                 FROM tb_refunds_notice 
                                 JOIN chartdetailsbudgetlog ON tb_refunds_notice.id = chartdetailsbudgetlog.transno AND chartdetailsbudgetlog.type = 293
                                 LEFT JOIN tb_cat_unidades_ejecutoras ON tb_refunds_notice.ue_id = tb_cat_unidades_ejecutoras.ue
                                 JOIN tb_cat_refunds ON tb_refunds_notice.refund_id = tb_cat_refunds.id
                                 JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = chartdetailsbudgetlog.cvefrom
                                 JOIN tb_cat_partidaspresupuestales_partidaespecifica ON chartdetailsbudgetbytag.partida_esp = tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada
                                 JOIN tb_cat_partidaspresupuestales_capitulo ON tb_cat_partidaspresupuestales_partidaespecifica.ccap = tb_cat_partidaspresupuestales_capitulo.ccap
                                 WHERE chartdetailsbudgetlog.type = 293 ".$querySQL." AND tb_refunds_notice.status_refund = 4
                                 GROUP BY tb_refunds_notice.id 
                                 ORDER BY tb_refunds_notice.id ASC";


             $ErrMsg = "No se obtuvieron los movimientos de auxiliar de bancos";
             //echo "sql:<pre>".$SQL;
             $TransResultQuery = DB_query($SQLrefunds, $db, $ErrMsg);

             while ($myrows = DB_fetch_array($TransResultQuery)) {

                 $info[] = array(

                     'banktransid' => $myrows['id'],
                     'ur' => $myrows['ur_id'],
                     'ue' => $myrows['ue_id'],
                     'pp' => $myrows['cppt'],
                     'transdate' => $myrows['authdate'],
                     'bankaccountname' => $myrows[''],
                     'bankaccount' => $myrows[''],
                     'concepto' => 'Reintegro: '.$myrows['justification'],
                     'referencia' => $myrows[''],
                     'folio' => $myrows['id'],
                     'folioExcel' => $myrows['id'],
                     'SaldoInicial' => $myrows[''],
                     'abonos' => ($myrows['total'] != "" ? abs(number_format($myrows['total'], $_SESSION['DecimalPlaces'], '.', '')) : 0),
                     'cargos' => 0,
                     'SaldoFinal' => $myrows['']

                 );

             }

     }



//     print_r($info); //($myrows['total'] != "" ? abs(number_format($myrow ['total'], $_SESSION['DecimalPlaces'], '.', '')) : 0),
 //    exit();

        // Columnas para el GRID
        $columnasNombres .= "[";
        $columnasNombres .= "{ name: 'banktransid', type: 'string'},";
        $columnasNombres .= "{ name: 'ur', type: 'string' },";
        $columnasNombres .= "{ name: 'ue', type: 'string' },";
        $columnasNombres .= "{ name: 'pp', type: 'string' },";
        $columnasNombres .= "{ name: 'transdate', type: 'string' },";
        $columnasNombres .= "{ name: 'bankaccountname', type: 'string' },";
        $columnasNombres .= "{ name: 'bankaccount', type: 'string' },";
        $columnasNombres .= "{ name: 'concepto', type: 'string' },";
        $columnasNombres .= "{ name: 'referencia', type: 'string' },";
        $columnasNombres .= "{ name: 'folio', type: 'string' },";
        $columnasNombres .= "{ name: 'folioExcel', type: 'string' },";
        $columnasNombres .= "{ name: 'SaldoInicial', type: 'string' },";
        $columnasNombres .= "{ name: 'abonos', type: 'string' },";
        $columnasNombres .= "{ name: 'cargos', type: 'string' },";
        $columnasNombres .= "{ name: 'SaldoFinal', type: 'string' }";
        $columnasNombres .= "]";

        $columnasNombresGrid .= "[";
        $columnasNombresGrid .= " { text: '', datafield: 'banktransid', width: '3%', editable: true, editable: true, cellsalign: 'center', align: 'center',hidden: true },";
        $columnasNombresGrid .= " { text: 'UR', datafield: 'ur', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
        $columnasNombresGrid .= " { text: 'UE', datafield: 'ue', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
        $columnasNombresGrid .= " { text: 'Programa Presupuestal', datafield: 'pp', width: '11%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
        $columnasNombresGrid .= " { text: 'Fecha', datafield: 'transdate', width: '8%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
        $columnasNombresGrid .= " { text: 'Banco', datafield: 'bankaccountname', width: '10%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
        $columnasNombresGrid .= " { text: 'CLABE', datafield: 'bankaccount', width: '13%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
        $columnasNombresGrid .= " { text: 'Concepto', datafield: 'concepto', width: '17%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
        $columnasNombresGrid .= " { text: 'Referencia', datafield: 'referencia', width: '12%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
        $columnasNombresGrid .= " { text: 'Folio', datafield: 'folio', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
        $columnasNombresGrid .= " { text: 'Folio', datafield: 'folioExcel', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
        $columnasNombresGrid .= " { text: 'Saldo Inicial', datafield: 'SaldoInicial', width: '10%', editable: false, cellsalign: 'center', align: 'center', cellsformat: 'C2', hidden: false },";
        $columnasNombresGrid .= " { text: 'Ingresos', datafield: 'abonos', width: '8%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false},";
        $columnasNombresGrid .= " { text: 'Egresos', datafield: 'cargos', width: '8%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false},";
        $columnasNombresGrid .= " { text: 'Saldo Final', datafield: 'SaldoFinal', width: '10%', editable: false, cellsalign: 'center', align: 'center', cellsformat: 'C2', hidden: false },";
        $columnasNombresGrid .= "]";

    //'transdate' => $myrow['transdate'],

    //'banktranstype' => $myrow['banktranstype'],
    //'beneficiary' => $myrow['beneficiary'],

    /*
        $columnasNombresGrid .= " { text: 'Monto Total', datafield: 'total', width: '8%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false" . $colResumenTotal . " },";
        $columnasNombresGrid .= " { text: 'Monto Total', datafield: 'totalExcel', width: '8%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: true},";
        $columnasNombresGrid .= " { text: 'Reintegro ID', datafield: 'idrefunds', width: '4%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
        $columnasNombresGrid .= " { text: 'Periodo', datafield: 'period', width: '4%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
        $columnasNombresGrid .= " { text: 'No Transfer', datafield: 'folioTransfer', width: '4%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
        $columnasNombresGrid .= " { text: 'tipo_reintegro', datafield: 'type_refund', width: '4%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
        $columnasNombresGrid .= " { text: 'Impresion', datafield: 'impresion', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: false }";
     */
    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)) . '_' . date('dmY');

 //   $contenido = array('datos' => $info);

   // $dataObj = array('sql' => $SQL, 'contenido' => $contenido, 'result' => $TransResult, 'ErrMsg' => $ErrMsg, 'DbgMsg' => $DbgMsg, 'Mensaje' => $Mensaje);
   // echo json_encode($dataObj);

    $result = true;
    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);

    $dataObjSearch = array('contenido' => $contenido);
    echo json_encode($dataObjSearch);



}


function fnFormatoFechaYMD($fecha,$separador){
    $fechaFormateada = "";

    if($fecha == ""){
        $fechaFormateada = "0000-00-00";
    }else{
        list($dia, $mes, $anio) = explode($separador, $fecha);
        $fechaFormateada = $anio.'-'.$mes.'-'.$dia;
    }
    
    return $fechaFormateada;
}

