<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
  error_reporting(E_ALL);
  ini_set('display_errors', '1');
  ini_set('log_errors', 1);
  ini_set('error_log', dirname(__FILE__) . '/error_log.txt');// */


$funcion = 1798;

$PageSecurity = 3;
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');


$title = _('Reporte Subcategoria X Semana');
if (!isset($_POST ['PrintEXCEL'])) {
    include('includes/header.inc');
    echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title . '<br>';
}

if (isset($_POST['FromYear'])) {
    $FromYear = $_POST['FromYear'];
} elseif (isset($_GET['FromYear'])) {
    $FromYear = $_GET['FromYear'];
} else {
    $FromYear = date('Y');
}


if ((isset($_POST ["checkBanco"])) or ! isset($_POST ['BankAccount'])) {
    $checkbanco = 'checked';
}
if ((isset($_POST ["checkCxC"])) or ! isset($_POST ['BankAccount'])) {
    $checkCxC = 'checked';
}
if ((isset($_POST ["checkCxP"])) or ! isset($_POST ['BankAccount'])) {
    $checkCxP = 'checked';
}
if ((isset($_POST ["checkP"])) or ! isset($_POST ['BankAccount'])) {
    $checkP = 'checked';
}  $checkP = 'checked';

if (isset($_POST['thislegalid']) AND strlen($_POST['thislegalid']) > 0) {
    $thislegalid = $_POST['thislegalid'];
} elseif (isset($_GET['thislegalid'])) {
    $thislegalid = ' ' . $_GET['thislegalid'];
} else {
    $thislegalid = '';
    if (isset($_POST['legalid'])) {
        for ($i = 0; $i <= count($_POST['legalid']) - 1; $i++) {
            //echo 'empresa:' . $_POST['legalid'][$i] . '<br>';
            if ($i == 0)
                $thislegalid = $thislegalid . "" . $_POST['legalid'][$i] . "";
            else
                $thislegalid = $thislegalid . "," . $_POST['legalid'][$i] . "";
        }
    } else {
        $thislegalid = '-1';
    }
}

if (trim($thislegalid) == '') {
    $thislegalid = '-1';
}


if (!isset($_POST['legalid'])){
    $_POST['legalid'] = '0';
}

$LegalidWhereCond = "";
if (empty ( $_POST ['legalid'] ) == FALSE) {
    if (in_array ( '0', $_POST ['legalid'] ) == FALSE) {
        foreach ( $_POST ['legalid'] as $varlegalid ) {
            $legals [] = $varlegalid;
        }
        $LegalidWhereCond = implode ( ',', $legals );
    }else{
        $LegalidWhereCond = '0';
    }
}else{
    $LegalidWhereCond = '0';
}





if ((isset($_POST ["checkBanco"])) or ! isset($_POST ['BankAccount'])) {
    $checkbanco = 'checked';
}
if ((isset($_POST ["checkCxC"])) or ! isset($_POST ['BankAccount'])) {
    $checkCxC = 'checked';
}
if ((isset($_POST ["checkCxP"])) or ! isset($_POST ['BankAccount'])) {
    $checkCxP = 'checked';
}
if ((isset($_POST ["checkP"])) or ! isset($_POST ['BankAccount'])) {
    $checkP = 'checked';
}
/* * ****OBTIENE SEMANAS***** */
$sqlsemanas = "SELECT semana, 
					min(fecha) as fechainicio, 
					max(fecha) as fechafin,
					CASE WHEN Now() >= min(fecha) and Now() <= max(fecha) THEN 1 ELSE 0 END as semanaact 
					FROM DWH_Tiempo t 
					WHERE Anio = '" . $FromYear . "'
			  		GROUP BY semana	
			  		ORDER BY semana";
$ErrMsg = _('');
$DbgMsg = _('');
$ResultSemanas = DB_query($sqlsemanas, $db, $ErrMsg, $DbgMsg);
$arrsemanas = array();
$arrfechainiciosemana = array();
$arrfechafinsemana = array();
$arrfechacortainiciosemana = array();
$arrfechacortafinsemana = array();
$indice = -1;
$semanainicial = 0;
$semanafinal = 0;
$semanaactual = 0;
while ($myrowsem = DB_fetch_array($ResultSemanas)) {
    if ($semanainicial == 0) {
        $semanainicial = $myrowsem['semana'];
    }
    $semanafinal = $myrowsem['semana'];
    if ($myrowsem['semanaact'] == '1') {
        $semanaactual = $myrowsem['semana'];
    }
    $indice++;
    $arrsemanas[$indice] = $myrowsem['semana'];
    $arrfechainiciosemana[$indice] = $myrowsem['fechainicio'];
    $arrfecha = explode('-', substr($myrowsem['fechainicio'], 0, 10));
    $arrfechacortainiciosemana[$indice] = $arrfecha[2] . "-" . $arrfecha[1];

    $arrfechafinsemana[$indice] = $myrowsem['fechafin'];
    $arrfecha = explode('-', substr($myrowsem['fechafin'], 0, 10));
    $arrfechacortafinsemana[$indice] = $arrfecha[2] . "-" . $arrfecha[1];
}

if (!isset($_POST['fromweek'])) {
    if (($semanaactual - 6) < $semanainicial) {
        $_POST['fromweek'] = $semanainicial;
    } else {
        $_POST['fromweek'] = $semanaactual - 5;
    }
}
if (!isset($_POST['toweek'])) {
    if (($semanaactual + 6) > $semanafinal) {
        $_POST['toweek'] = $semanaactual;
    } else {
        $_POST['toweek'] = $semanaactual + 5;
    }
}


/* * ************************ */

$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
$SQL = $SQL . " FROM sec_unegsxuser u,tags t
						JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
$SQL = $SQL . " WHERE u.tagref = t.tagref ";
$SQL = $SQL . " and u.userid = '" . $_SESSION ['UserID'] . "'
			  	GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.legalid";
$Rsresult = DB_query($SQL, $db);



$SQL = 'SELECT bankaccountname,
			bankaccounts.accountcode,
			bankaccounts.currcode
		FROM bankaccounts, sec_unegsxuser, tagsxbankaccounts
				JOIN tags ON tagsxbankaccounts.tagref = tags.tagref
		WHERE bankaccounts.accountcode = tagsxbankaccounts.accountcode and
			tagsxbankaccounts.tagref = sec_unegsxuser.tagref and
			sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '" and
			tags.legalid in (' . $LegalidWhereCond . ')
			and bankaccounts.accountcode <> "101084"		
		GROUP BY bankaccountname,
		bankaccounts.accountcode,
		bankaccounts.currcode';

//echo "<br>BANKS" . $SQL;
$ErrMsg = _('Las cuentas de cheques no se pudieron recuperar porque');
$DbgMsg = _('El SQL utilizado para recuperar las cuentas de cheques fue');
$AccountsResults = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

echo "<form name='FDatosA' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'>";
echo '<center><table width:100%>'
 . '<tr><td style="text-align:right"><b>Razon Social</b></td>';




echo '<td><select name="legalid[]" multiple>';
if (isset($_POST ['legalid']) and $_POST ['legalid'] == '0' OR in_array('0', $_POST ['legalid'])) {
    echo '<option selected value="0">Todas las Razones';
}else{
    echo '<option value="0">Todas las Razones';
}
while ($myrow = DB_fetch_array($Rsresult)) {
    if (isset($_POST ['legalid']) and $_POST ['legalid'] == $myrow ["legalid"] OR in_array($myrow ["legalid"], $_POST ['legalid'])) {
        echo '<option selected value=' . $myrow ['legalid'] . '>' . $myrow ['legalid'] . ' - ' . $myrow ['legalname'];
    } else {
        echo '<option value=' . $myrow ['legalid'] . '>' . $myrow ['legalid'] . ' - ' . $myrow ['legalname'];
    }
}
echo '</select>';
echo '</td>';
echo '</tr>'
 . '<tr><td style="text-align:right"><b>Cuenta de cheques</b></td><td><select name="BankAccount">';

echo "<option selected value='-1'>Todas las cuentas de cheques...</option>";
while ($myrow = DB_fetch_array($AccountsResults)) {
    /* list the bank account names */
    if (!isset($_POST['BankAccount']) AND $myrow['currcode'] == $_SESSION['CompanyRecord']['currencydefault']) {
        //$_POST['BankAccount']=$myrow['accountcode'];
    }
    if ($_POST['BankAccount'] == $myrow['accountcode']) {
        echo '<option selected VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
    } else {
        echo '<option VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
    }
}
echo '</select>';

echo '<td></tr>'
 . '<tr><td  style="text-align:right"><b>' . utf8_decode("X Año") . '</b></td><td><input name="FromYear" type="text" size="4" value=' . $FromYear . '></td></tr>';
echo '<tr><td  style="text-align:right"><b>' . _('De la Semana') . ':&nbsp;</b><td>';
echo '<select name="fromweek">';
$numsemanas = count($arrsemanas);
for ($xx = 0; $xx < $numsemanas; $xx++) {
    if ($arrsemanas[$xx] == $_POST['fromweek']) {
        echo '<option selected value="' . $arrsemanas[$xx] . '">' . add_cerosstring($arrsemanas[$xx], 2) . ' => ' . $arrfechacortainiciosemana[$xx] . '</option>';
    } else {
        echo '<option value="' . $arrsemanas[$xx] . '">' . add_cerosstring($arrsemanas[$xx], 2) . ' => ' . $arrfechacortainiciosemana[$xx] . '</option>';
    }
}
echo '</select>';
echo '&nbsp;<b>' . _('A la Semana') . ':&nbsp;</b>';
echo '<select name="toweek">';
for ($xx = 0; $xx < $numsemanas; $xx++) {
    if ($arrsemanas[$xx] == $_POST['toweek']) {
        echo '<option selected value="' . $arrsemanas[$xx] . '">' . add_cerosstring($arrsemanas[$xx], 2) . ' => ' . $arrfechacortafinsemana[$xx] . '</option>';
    } else {
        echo '<option value="' . $arrsemanas[$xx] . '">' . add_cerosstring($arrsemanas[$xx], 2) . ' => ' . $arrfechacortafinsemana[$xx] . '</option>';
    }
}
echo '</select>';
echo '</td><tr>';



echo '<TD COLSPAN="4">
 <tr><td colspan="4"><input type="submit" name="consultar" value="Consultar"></td>'
 . '<td><input type="submit" name="Excel" value="Exportar Excels" ></td></tr>'
 . '</table></center>';


echo '</form>';

if (isset($_POST['consultar']) or isset($_POST['Excel'])) {
    if (!isset($_POST ['PrintEXCEL'])) {
        echo "<form name='FDatosB' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'><table>";
        echo '<input Name="FromMes" type=hidden value="' . $FromMes . '">';
        echo '<input type=hidden id=legallist name=thislegalid value="' . $thislegalid . '">';
    }

    
	
	if (isset($_POST ['PrintEXCEL'])) {

        header("Content-type: application/ms-excel");
        // replace excelfile.xls with whatever you want the filename to default to
        header("Content-Disposition: attachment; filename=ResumenflujoSemana.xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

        echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
        echo '<link rel="shortcut icon" href="' . $rootpath . '/favicon.ico" />';
        echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
    }


//  partelneta
//  	
	$saldoinicial = Traesaldoinicial($_POST['FromYear'], 0, $thislegalid, $funcion, $_POST ['BankAccount'], $checkbanco, $checkCxC, $checkCxP, $checkP, $_POST['fromweek'], $FechaProy, $db);
//echo "<br>SALDO INICIAL: " . $saldoinicial;

    $tabla0 = '<table border="1" cellpadding="2" cellspacing="0" width="100%">';
    $tabla0 = $tabla0 . '<tr style="background-color:#F3F781;">';
    $tabla0 = $tabla0 . '<td style="font-size:x-small; font-weight:bold; text-align:center;">' . _('') . '</td>';
    $arrtotalesxsemana = array();
    $arrtotalesbancosxsemana = array();
    $arrtotalesbproyeccionxsemana = array();
    
    $arrtotalesxsemanaxcat = array();
    $arrtotalesbancosxsemanaxcat = array();
    $arrtotalesproyeccionxsemanaxcat = array();
    
    $arrtotalesxsemanaxtipo = array();
    $arrtotalesbancosxsemanaxtipo = array();
    $arrtotalesproyeccionxsemanaxtipo = array();

    $arrsaldoinicial = array();
    $contsemanas = count($arrsemanas);
    for ($i = 0; $i < $contsemanas; $i++) {
        if (($arrsemanas[$i] >= $_POST['fromweek']) and ( $arrsemanas[$i] <= $_POST['toweek'])) {
            if (strlen($arrsemanas[$i]) == 1) {
                $strsemana = '0' . $arrsemanas[$i];
            } else {
                $strsemana = $arrsemanas[$i];
            }
            $tooltip = substr($arrfechainiciosemana[$i], 0, 10) . " " . _('Al') . " " . substr($arrfechafinsemana[$i], 0, 10);
            if ($arrsemanas[$i] == $semanaactual) {
                $tabla0 = $tabla0 . '<td nowrap style="font-size:x-small; font-weight:bold; text-align:center; background-color:white;" colspan="4">';
            } else {
                $tabla0 = $tabla0 . '<td nowrap style="font-size:x-small; font-weight:bold; text-align:center;" colspan="4">';
            }

            $tabla0 = $tabla0 . '<span title="' . $tooltip . '">';
            $tabla0 = $tabla0 . '*** ' . $strsemana . ' ***';
            $tabla0 = $tabla0 . '<br><span style="font-size:xx-small;">' . $arrfechacortainiciosemana[$i] . " AL " . $arrfechacortafinsemana[$i] . "</span>";
            $tabla0 = $tabla0 . '</span></td>';
            //$tabla0 = $tabla0 . '<td>Banco(Real)</td><td>Proyeccion</td><td>DIferencia $</td><td>Diferencia %</td>';
            $arrtotalesxsemana[$i] = 0;
            $arrtotalesbancosxsemana[$i] = 0;
            $arrtotalesbproyeccionxsemana[$i] = 0;

            $arrtotalesxsemanaxcat[$i] = 0;
            $arrtotalesbancosxsemanaxcat[$i] = 0;
            $arrtotalesproyeccionxsemanaxcat[$i] = 0;

            $arrtotalesxsemanaxtipo[$i] = 0;
            $arrtotalesbancosxsemanaxtipo[$i] = 0;
            $arrtotalesproyeccionxsemanaxtipo[$i] = 0;

            $arrsaldoinicial[$i] = 0;
        }
    }
    //$tabla0 = $tabla0 . '<td style="font-size:x-small; font-weight:bold; background-color:#FFFF00; text-align:center;">' . _('') . '</td>';
    $tabla0 = $tabla0 . '</tr>';

    $tabla0 = $tabla0 . '<tr style="background-color:#F3F781;">';
    $tabla0 = $tabla0 . '<td style="font-size:x-small; font-weight:bold; text-align:center;">' . _('') . '</td>';

    $contsemanas = count($arrsemanas);
    for ($i = 0; $i < $contsemanas; $i++) {
        if (($arrsemanas[$i] >= $_POST['fromweek']) and ( $arrsemanas[$i] <= $_POST['toweek'])) {

            $tabla0 = $tabla0 . '<td style="text-align:center;">' . _('Banco') . '<br>(' . _('Real') . ')</td><td style="text-align:center;">' . _('Proyeccion') . '</td><td style="text-align:center;">' . _('DIferencia') . ' $</td><td style="text-align:center;">' . _('Diferencia') . ' %</td>';
        }
    }
    //$tabla0 = $tabla0 . '<td style="font-size:x-small; font-weight:bold; background-color:#FFFF00; text-align:center;">' . _('') . '</td>';
    $tabla0 = $tabla0 . '</tr>';



    $ErrMsg = _('');
    $DbgMsg = _('');

    //$Result = DB_query($sqlgral, $db, $ErrMsg, $DbgMsg);
    $catant = '';
    $subcatante = '';
    $tipoant = '';
    $totalsubcategoria = 0;

    $totales = 0;

    $contsemanas = $_POST['toweek'];

    /**/


    $SQL = "SELECT 
    			we.subcategoryid as subcategoria,
 				sc.subcat_name, 
				sc.subcat_id, 
				we.weekno as Semana, 
    			IFNULL(ct.color,'#FAAC58') as colorcat,
				IFNULL(sc.color,'#FFFFFF') as colorsubcat, 
				ct.cat_name, 
				kc.kindcategory as tipo,
    			IFNULL(SUM(we.amount),0) AS proyeccion,
				SUM(X.saldo) AS bancos, 
    			we.yearno
				FROM weeklyestimation we
    				LEFT JOIN (
    					SELECT  DWH_Tiempo.semana,
    							DWH_Tiempo.Anio,
				 				IFNULL(banktrans_ext.subcat_id, 0) as subcategoria,
    							banktrans.tagref, 
								SUM(banktrans.amount*-1) as saldo
						FROM banktrans
				 			INNER JOIN DWH_Tiempo ON year(banktrans.transdate) = DWH_Tiempo.Anio
				 					and month(banktrans.transdate) = DWH_Tiempo.Mes
				 					and day(banktrans.transdate) = DWH_Tiempo.Dia
							LEFT JOIN banktrans_ext ON banktrans.banktransid = banktrans_ext.banktransid
				 			INNER JOIN tagsxbankaccounts ON tagsxbankaccounts.accountcode = banktrans.bankact
								AND tagsxbankaccounts.tagref = banktrans.tagref
								AND tagsxbankaccounts.tagref in (select tagref from tags where (legalid in (" . trim($LegalidWhereCond) . ") or '" . $LegalidWhereCond . "' = '0' ))
							LEFT JOIN supptrans ON banktrans.type = supptrans.type and banktrans.transno = supptrans.transno
							INNER JOIN tags ON tags.tagref=banktrans.tagref
							INNER JOIN systypescat ON systypescat.typeid=banktrans.type
							INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
						  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION ['UserID'] . "'
						  	LEFT JOIN chartmaster on chartmaster.accountcode=banktrans.bankact
						WHERE year(banktrans.transdate) = '" . $_POST['FromYear'] . "'
				 			AND DWH_Tiempo.semana >= " . $_POST['fromweek'] . "
				 			AND DWH_Tiempo.semana <= " . $_POST['toweek'] . "
							AND abs(banktrans.amount)!=0
						GROUP BY DWH_Tiempo.semana, DWH_Tiempo.Anio, IFNULL(banktrans_ext.subcat_id, 0), banktrans.tagref
					) AS X ON we.tagref = X.tagref 
									AND we.subcategoryid = X.subcategoria 
									AND we.weekno = X.Semana 
				 					AND we.yearno = X.Anio
		   			LEFT JOIN fjoSubCategory sc ON we.subcategoryid = sc.subcat_id
					LEFT JOIN fjoCategory ct ON sc.cat_id = ct.cat_id
					LEFT JOIN fjokindcategory kc ON ct.kindcategoryid = kc.kindcategoryid
                    INNER JOIN sec_unegsxuser  ON sec_unegsxuser.tagref = we.tagref AND sec_unegsxuser.userid = '" . $_SESSION ['UserID'] . "'
				WHERE we.weekno >= '" . $_POST['fromweek'] . "' 
						AND we.weekno <= '" . $_POST['toweek'] . "'
						AND we.yearno = '" . $_POST['FromYear'] . "'
						AND (we.amount <> 0 or X.saldo <> 0)
                        AND (we.legalid in (" . trim($LegalidWhereCond) . ") or '" . $LegalidWhereCond . "' = '0'  )
				GROUP BY we.weekno, we.yearno, we.subcategoryid";
   	$SQLGral = $SQL . " ORDER BY ct.order, sc.order, subcategoria,Semana ";

    //echo 'Linea: '.__LINE__.'<br><pre>'.$SQLGral;
    

    $Result = DB_query($SQLGral, $db);
	$amount = 0;
	$saldo = 0;
    while ($myrow = DB_fetch_array($Result)) {
        if ($myrow['Semana'] != '0') {
            $flagfincate = false;
            $flagfintipo = false;
            if ($tipoant != $myrow['tipo']) { /*             * ****CAMBIO DE TIPO******* */
                if ($subcatante != "") {
                    for ($i = $indice0; $i < $contsemanas; $i++) {
                        if ($arrsemanas[$i] == $semanaactual) {
                            $bgcolorsemanaactual = 'background-color:#F3F781;';
                        } else {
                            $bgcolorsemanaactual = '';
                        }
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
                    }
                    /*
                    if ($totalsubcategoria < 0) {
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; color:red; text-align:right;">' . number_format(abs($totalsubcategoria), 2) . '</td>';
                    } else {
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right;">' . number_format(abs($totalsubcategoria), 2) . '</td>';
                    }
                    */
                    $totales = $totales + $totalsubcategoria;
                    $tabla1 = $tabla1 . '</tr>';
                    $flagfincate = true;
                    $totalsubcategoria = 0;
                }
                if ($catant != "") {
                    $tabla1 = $tabla1 . '<tr style="background-color:#ECF6CE">';
                    $tabla1 = $tabla1 . '<td style="font-size:x-small; font-weight:bold; text-align:left;">' . _('ST ') . $catant . '</td>';
                    $totalxcategoria = 0;
                    for ($i = ($_POST['fromweek'] - 1); $i < $contsemanas; $i++) {
                        $totalxcategoria = $totalxcategoria + $arrtotalesxsemanaxcat[$i];

                        $diffxsemanaxcat = abs(abs($arrtotalesproyeccionxsemanaxcat[$i]) - abs($arrtotalesbancosxsemanaxcat[$i]));
                        if($arrtotalesproyeccionxsemanaxcat[$i] == 0 and $arrtotalesbancosxsemanaxcat[$i] != 0){
                            $diffporcxsemanaxcat = 100;
                        }elseif($arrtotalesproyeccionxsemanaxcat[$i] != 0 and $arrtotalesbancosxsemanaxcat[$i] == 0){
                            $diffporcxsemanaxcat = 100;
                        }else{
                            $diffporcxsemanaxcat = ($diffxsemanaxcat*100) / $arrtotalesproyeccionxsemanaxcat[$i];    
                        }

                        if ($arrtotalesproyeccionxsemanaxcat[$i] < 0) {
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($arrtotalesbancosxsemanaxcat[$i]), 2) . '</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($arrtotalesproyeccionxsemanaxcat[$i]), 2) . '</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($diffxsemanaxcat), 2) . '</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($diffporcxsemanaxcat), 2) . '%</td>';
                        } else {
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($arrtotalesbancosxsemanaxcat[$i]), 2) . '</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($arrtotalesproyeccionxsemanaxca[$i]), 2) . '</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($diffxsemanaxcat), 2) . '</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($diffporcxsemanaxcat), 2) . '%</td>';
                        }


                        $arrtotalesxsemanaxcat[$i] = 0;
                        $arrtotalesbancosxsemanaxcat[$i] = 0;
                        $arrtotalesproyeccionxsemanaxcat[$i] = 0;
                    }
                    /*
                    if ($totalxcategoria < 0) {
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right; color:red;">' . number_format(abs($totalxcategoria), 2) . '</td>';
                    } else {
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right;">' . number_format(($totalxcategoria), 2) . '</td>';
                    }
                    */
                    $tabla1 = $tabla1 . '</tr>';
                    $flagfintipo = true;
                }
                if ($tipoant != "") {
                    $tabla1 = $tabla1 . '<tr style="background-color:#0431B4; color:white;">';
                    $tabla1 = $tabla1 . '<td style="font-size:x-small; font-weight:bold; text-align:left; color:white;">' . _('TOTAL ') . $tipoant . '</td>';
                    $totalxtipo = 0;
                    for ($i = ($_POST['fromweek'] - 1); $i < $contsemanas; $i++) {
                        $totalxtipo = $totalxtipo + $arrtotalesxsemanaxtipo[$i];
                        
                        $diffxsemanaxtipo = abs(abs($arrtotalesproyeccionxsemanaxtipo[$i]) - abs($arrtotalesbancosxsemanaxtipo[$i]));
                        if($arrtotalesproyeccionxsemanaxtipo[$i] == 0 and $arrtotalesbancosxsemanaxtipo[$i] != 0){
                            $diffporcxsemanaxtipo = 100;
                        }elseif($arrtotalesproyeccionxsemanaxtipo[$i] != 0 and $arrtotalesbancosxsemanaxtipo[$i] == 0){
                            $diffporcxsemanaxtipo = 100;
                        }else{
                            $diffporcxsemanaxtipo = ($diffxsemanaxtipo*100) / $arrtotalesproyeccionxsemanaxtipo[$i];
                        }




                        if ($arrtotalesxsemanaxtipo[$i] < 0) {
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">(' . number_format(abs($arrtotalesbancosxsemanaxtipo[$i]), 2) . ')</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">(' . number_format(abs($arrtotalesproyeccionxsemanaxtipo[$i]), 2) . ')</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">(' . number_format(abs($diffxsemanaxtipo), 2) . ')</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">(' . number_format(abs($diffporcxsemanaxtipo), 2) . ')%</td>';
                        } else {
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">' . number_format(($arrtotalesbancosxsemanaxtipo[$i]), 2) . '</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">' . number_format(($arrtotalesproyeccionxsemanaxtipo[$i]), 2) . '</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">' . number_format(($diffxsemanaxtipo), 2) . '</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">' . number_format(($diffporcxsemanaxtipo), 2) . '%</td>';
                        }

                        $arrtotalesxsemanaxtipo[$i] = 0;
                        $arrtotalesbancosxsemanaxtipo[$i] = 0;
                        $arrtotalesproyeccionxsemanaxtipo[$i] = 0; 
                    }
                    /*
                    if ($totalxtipo < 0) {
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#0431B4; font-weight:bold; text-align:right; color:white;">(' . number_format(abs($totalxtipo), 2) . ')</td>';
                    } else {
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#0431B4; font-weight:bold; text-align:right; color:white;">' . number_format(($totalxtipo), 2) . '</td>';
                    }
                    */
                    $tabla1 = $tabla1 . '</tr>';
                }

                $tabla1 = $tabla1 . '<tr style="background-color:#0431B4;">';
                $tabla1 = $tabla1 . '<td style="font-size:x-small; font-weight:bold; text-align:left; color:white" colspan="' . ((($_POST['toweek'] - ($_POST['fromweek']-1))*4) + 1) . '">' . $myrow['tipo'] . '</td>';
                //$tabla1 = $tabla1 . '<td style="font-size:x-small; background-color:#0431B4; font-weight:bold; text-align:left;"></td>';

                $tabla1 = $tabla1 . '</tr>';
                $tipoant = $myrow['tipo'];
            }/*             * *****FIN CAMBIO DE TIPO******** */
            if ($catant != $myrow['cat_name']) {/*             * ****CAMBIO DE CATEGORIA***** */
                if ($subcatante != "" and $flagfincate == false) {
                    for ($i = $indice0; $i < $contsemanas; $i++) {
                        if ($arrsemanas[$i] == $semanaactual) {
                            $bgcolorsemanaactual = 'background-color:#F3F781;';
                        } else {
                            $bgcolorsemanaactual = '';
                        }
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
                    }
                    /*
                    if ($totalsubcategoria < 0) {
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; color:red; text-align:right;">' . number_format(abs($totalsubcategoria), 2) . '</td>';
                    } else {
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right;">' . number_format(abs($totalsubcategoria), 2) . '</td>';
                    }
                    */
                    $totales = $totales + $totalsubcategoria;
                    $tabla1 = $tabla1 . '</tr>';
                    $flagfincate = true;
                    $totalsubcategoria = 0;
                }
                if ($catant != "" and $flagfintipo == false) {
                    $tabla1 = $tabla1 . '<tr style="background-color:#ECF6CE">';
                    $tabla1 = $tabla1 . '<td style="font-size:x-small; font-weight:bold; text-align:left;">' . _('ST ') . $catant . '</td>';
                    $totalxcategoria = 0;
                    for ($i = ($_POST['fromweek'] - 1); $i < $contsemanas; $i++) {
                        $totalxcategoria = $totalxcategoria + $arrtotalesxsemanaxcat[$i];
                        
                        $diffxsemanaxcat = abs(abs($arrtotalesproyeccionxsemanaxcat[$i]) - abs($arrtotalesbancosxsemanaxcat[$i]));
                        if($arrtotalesproyeccionxsemanaxcat[$i] == 0 and $arrtotalesbancosxsemanaxcat[$i] != 0){
                            $diffporcxsemanaxcat = 100;
                        }elseif($arrtotalesproyeccionxsemanaxcat[$i] != 0 and $arrtotalesbancosxsemanaxcat[$i] == 0){
                            $diffporcxsemanaxcat = 100;
                        }else{
                            $diffporcxsemanaxcat = ($diffxsemanaxcat*100) / $arrtotalesproyeccionxsemanaxcat[$i];    
                        }

                        if ($arrtotalesproyeccionxsemanaxcat[$i] < 0) {
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($arrtotalesbancosxsemanaxcat[$i]), 2) . '</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($arrtotalesproyeccionxsemanaxcat[$i]), 2) . '</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($diffxsemanaxcat), 2) . '</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($diffporcxsemanaxcat), 2) . '%</td>';
                        } else {
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($arrtotalesbancosxsemanaxcat[$i]), 2) . '</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($arrtotalesproyeccionxsemanaxca[$i]), 2) . '</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($diffxsemanaxcat), 2) . '</td>';
                            $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($diffporcxsemanaxcat), 2) . '%</td>';
                        }
                        $arrtotalesbancosxsemanaxcat[$i] = 0;
                        $arrtotalesproyeccionxsemanaxcat[$i] = 0;
                        $arrtotalesxsemanaxcat[$i] = 0;
                    }
                    /*
                    if ($totalxcategoria < 0) {
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right; color:red;">' . number_format(abs($totalxcategoria), 2) . '</td>';
                    } else {
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right;">' . number_format(($totalxcategoria), 2) . '</td>';
                    }
                    */
                    $tabla1 = $tabla1 . '</tr>';
                }


                $tabla1 = $tabla1 . '<tr style="background-color:' . $myrow['colorcat'] . ';">';
                $tabla1 = $tabla1 . '<td style="font-size:x-small; font-weight:bold; text-align:left;" colspan="' . ((($_POST['toweek'] - ($_POST['fromweek']-1))*4) + 1) . '">' . $myrow['cat_name'] . '</td>';
                //$tabla1 = $tabla1 . '<td style="font-size:x-small; background-color:#FFFF00; font-weight:bold; text-align:left;"></td>';

                $tabla1 = $tabla1 . '</tr>';
                $catant = $myrow['cat_name'];
            }/*             * *****FIN CAMBIO DE CATEGORIA******** */
            if ($subcatante != $myrow['subcat_name']) {/*             * *****CAMBIO DE SUBCATEGORIA******** */
                if ($subcatante != "" and $flagfincate == false) {
                    for ($i = $indice0; $i < $contsemanas; $i++) {
                        if ($arrsemanas[$i] == $semanaactual) {
                            $bgcolorsemanaactual = 'background-color:#F3F781;';
                        } else {
                            $bgcolorsemanaactual = '';
                        }
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small;  text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small;  text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small;  text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small;  text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
                        
                        
                    }
                    /*
                    if ($totalsubcategoria < 0) {
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; color:red; text-align:right;">' . number_format(abs($totalsubcategoria), 2) . '</td>';
                    } else {
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right;">' . number_format(abs($totalsubcategoria), 2) . '</td>';
                    }
                    */
                    $totales = $totales + $totalsubcategoria;
                    $tabla1 = $tabla1 . '</tr>';
                    $totalsubcategoria = 0;
                }
                $tabla1 = $tabla1 . '<tr style="background-color:' . $myrow['colorsubcat'] . ';">';
                $tabla1 = $tabla1 . '<td nowrap style="font-size:x-small; font-weight:bold; text-align:left;">' . $myrow['subcat_name'] . '</td>';
                $subcatante = $myrow['subcat_name'];
                $indice0 = $_POST['fromweek'] - 1;
            }/*             * *****FIN CAMBIO DE SUBCATEGORIA******** */


            for ($i = $indice0; $i < $contsemanas; $i++) {
                if ($arrsemanas[$i] == $myrow['Semana']) {
                    $totalsubcategoria = $totalsubcategoria + ($myrow['saldo'] * -1);
                    $arrtotalesxsemana[$i] = $arrtotalesxsemana[$i] + ($myrow['saldo'] * -1);
                    $arrtotalesxsemanaxcat[$i] = $arrtotalesxsemanaxcat[$i] + ($myrow['saldo'] * -1);
                    $arrtotalesxsemanaxtipo[$i] = $arrtotalesxsemanaxtipo[$i] + ($myrow['saldo'] * -1);


                    $arrtotalesbancosxsemana[$i] += ($myrow['bancos'] * -1);
                    $arrtotalesbproyeccionxsemana[$i] += ($myrow['proyeccion'] * -1);
                    $arrtotalesbancosxsemanaxcat[$i] += ($myrow['bancos'] * -1);
                    $arrtotalesproyeccionxsemanaxcat[$i] += ($myrow['proyeccion'] * -1);
                    $arrtotalesbancosxsemanaxtipo[$i] += ($myrow['bancos'] * -1);
                    $arrtotalesproyeccionxsemanaxtipo[$i] += ($myrow['proyeccion'] * -1);

                    if ($arrsemanas[$i] == $semanaactual) {
                        $bgcolorsemanaactual = 'background-color:#F3F781;';
                    } else {
                        $bgcolorsemanaactual = '';
                    }

                    if (($myrow['bancos'] * -1) < 0) {
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; color:red; text-align:right; ' . $bgcolorsemanaactual . '">' . number_format(abs($myrow['bancos']), 2) . '</td>';
                    } else {
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">' . number_format(abs($myrow['bancos']), 2) . '</td>';
                    }

                    if (($myrow['proyeccion'] * -1) < 0) {
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; color:red; text-align:right; ' . $bgcolorsemanaactual . '">' . number_format(abs($myrow['proyeccion']), 2) . '</td>';
                    } else {
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">' . number_format(abs($myrow['proyeccion']), 2) . '</td>';
                    }
                    
                    if ($myrow['bancos'] < 0) {
                        $bancos = $myrow['bancos'] * -1;
                    }else{
                    	$bancos = $myrow['bancos'];
                    }
                    
                    if ($myrow['proyeccion'] < 0) {
                        $proyeccion = $myrow['proyeccion'] * -1;
                    }else{
                    	$proyeccion = $myrow['proyeccion'];
                    }
                    
                    $diff = abs(abs($proyeccion) - abs($bancos));
                    $total = $diff;
                    if($proyeccion == 0 and $bancos != 0){
                        $totalPOr = 100;
                    }elseif($proyeccion != 0 and $bancos == 0){
                        $totalPOr = 100;
                    }else{
                        $totalPOr = ($diff*100) / $proyeccion;    
                    }
                    

                    if($total){
                    	$tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">' . number_format($total, 2) . '</td>';   
                    }else{
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '"> 0.00 </td>';  
                    }
                    if($total){
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">' . number_format(($totalPOr), 2) . '%</td>';
                    }else{
                        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '"> 0.00% </td>';
                        
                        
                    }
                   //$tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '"> ' . $amount.'-'.$saldo . ' </td>';
                   
                    
                    $amount = 0;
                    $saldo = 0;
                    break;
                } else {
                    if ($arrsemanas[$i] == $semanaactual) {
                        $bgcolorsemanaactual = 'background-color:#F3F781;';
                    } else {
                        $bgcolorsemanaactual = '';
                    }
                    $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
                    $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
                    $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
                    $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
                    
                }
             
            }
            $indice0 = $i + 1;
       //     echo '$indice0->'.$indice0;
        }
    }/* FIN WHILE */
    for ($i = $indice0; $i < $contsemanas; $i++) {
       // echo 'semana Array '.$arrsemanas[$i].'actual '.$semanaactual.'<br>';
        if ($arrsemanas[$i] == $semanaactual) {
            $bgcolorsemanaactual = 'background-color:#F3F781;';
        } else {
            $bgcolorsemanaactual = '';
        }
        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
        $tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolorsemanaactual . '">0.00</td>';
    }
    /*
    if ($totalsubcategoria < 0) {
        $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; color:red; text-align:right;">' . number_format(abs($totalsubcategoria), 2) . '</td>';
    } else {
        $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right;">' . number_format(abs($totalsubcategoria), 2) . '</td>';
    }
    */
    if ($catant != "") {
        $tabla1 = $tabla1 . '<tr style="background-color:#ECF6CE">';
        $tabla1 = $tabla1 . '<td style="font-size:x-small; font-weight:bold; text-align:left;">' . _('ST ') . $catant . '</td>';
        $totalxcategoria = 0;
        for ($i = ($_POST['fromweek'] - 1); $i < $contsemanas; $i++) {
            $totalxcategoria = $totalxcategoria + $arrtotalesxsemanaxcat[$i];
            
            $diffxsemanaxcat = abs(abs($arrtotalesproyeccionxsemanaxcat[$i]) - abs($arrtotalesbancosxsemanaxcat[$i]));
            if($arrtotalesproyeccionxsemanaxcat[$i] == 0 and $arrtotalesbancosxsemanaxcat[$i] != 0){
                $diffporcxsemanaxcat = 100;
            }elseif($arrtotalesproyeccionxsemanaxcat[$i] != 0 and $arrtotalesbancosxsemanaxcat[$i] == 0){
                $diffporcxsemanaxcat = 100;
            }else{
                $diffporcxsemanaxcat = ($diffxsemanaxcat*100) / $arrtotalesproyeccionxsemanaxcat[$i];    
            }
            if ($arrtotalesproyeccionxsemanaxcat[$i] < 0) {
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($arrtotalesbancosxsemanaxcat[$i]), 2) . '</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($arrtotalesproyeccionxsemanaxcat[$i]), 2) . '</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($diffxsemanaxcat), 2) . '</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($diffporcxsemanaxcat), 2) . '</td>';
            } else {
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($arrtotalesbancosxsemanaxcat[$i]), 2) . '</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($arrtotalesproyeccionxsemanaxca[$i]), 2) . '</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($diffxsemanaxcat), 2) . '</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($diffporcxsemanaxcat), 2) . '</td>';
            }
        }
        /*
        if ($totalxcategoria < 0) {
            $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right; color:red;">' . number_format(abs($totalxcategoria), 2) . '</td>';
        } else {
            $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right;">' . number_format(($totalxcategoria), 2) . '</td>';
        }
        */
        $tabla1 = $tabla1 . '</tr>';
    }
    if ($tipoant != "") {
        $tabla1 = $tabla1 . '<tr style="background-color:#0431B4; color:white;">';
        $tabla1 = $tabla1 . '<td style="font-size:x-small; font-weight:bold; text-align:left; color:white;">' . _('TOTAL ') . $tipoant . '</td>';
        $totalxtipo = 0;
        for ($i = ($_POST['fromweek'] - 1); $i < $contsemanas; $i++) {
            
            $totalxtipo = $totalxtipo + $arrtotalesxsemanaxtipo[$i];
            
            $diffxsemanaxtipo = abs(abs($arrtotalesproyeccionxsemanaxtipo[$i]) - abs($arrtotalesbancosxsemanaxtipo[$i]));
            if($arrtotalesproyeccionxsemanaxtipo[$i] == 0 and $arrtotalesbancosxsemanaxtipo[$i] != 0){
                $diffporcxsemanaxtipo = 100;
            }elseif($arrtotalesproyeccionxsemanaxtipo[$i] != 0 and $arrtotalesbancosxsemanaxtipo[$i] == 0){
                $diffporcxsemanaxtipo = 100;
            }else{
                $diffporcxsemanaxtipo = ($diffxsemanaxtipo*100) / $arrtotalesproyeccionxsemanaxtipo[$i];
            }

            if ($arrtotalesxsemanaxtipo[$i] < 0) {
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">(' . number_format(abs($arrtotalesbancosxsemanaxtipo[$i]), 2) . ')</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">(' . number_format(abs($arrtotalesproyeccionxsemanaxtipo[$i]), 2) . ')</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">(' . number_format(abs($diffxsemanaxtipo), 2) . ')</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">(' . number_format(abs($diffporcxsemanaxtipo), 2) . ')</td>';
            } else {
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">' . number_format(($arrtotalesbancosxsemanaxtipo[$i]), 2) . '</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">' . number_format(($arrtotalesproyeccionxsemanaxtipo[$i]), 2) . '</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">' . number_format(($diffxsemanaxtipo), 2) . '</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">' . number_format(($diffporcxsemanaxtipo), 2) . '</td>';
            }

            $arrtotalesxsemanaxtipo[$i] = 0;
        }
        /*
        if ($totalxtipo < 0) {
            $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#0431B4; font-weight:bold; text-align:right; color:white;">(' . number_format(abs($totalxtipo), 2) . ')</td>';
        } else {
            $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#0431B4; font-weight:bold; text-align:right; color:white;">' . number_format(($totalxtipo), 2) . '</td>';
        }*/
        $tabla1 = $tabla1 . '</tr>';
    }

    /*
    $totales = $totales + $totalsubcategoria;
    $tablasi = '<tr style="background-color:#F5DA81;">';
    $tablasi = $tablasi . '<td style="font-size:x-small; font-weight:bold; text-align:center;">' . _('S.Inicial') . '</td>';
    $contsemanas = count($arrsemanas);
    $arrsaldoinicial[$_POST['fromweek'] - 1] = $saldoinicial;
    for ($i = ($_POST['fromweek'] - 1); $i < $contsemanas; $i++) {
        if (($arrsemanas[$i] >= $_POST['fromweek']) and ( $arrsemanas[$i] <= $_POST['toweek'])) {
            if ($i > ($_POST['fromweek'] - 1)) {
                $arrsaldoinicial[$i] = $arrsaldoinicial[$i - 1] + $arrtotalesxsemana[$i - 1];
            }
            if ($arrsaldoinicial[$i] < 0) {
                $tablasi = $tablasi . '<td nowrap style="background-color:#F5DA81; font-size:x-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($arrsaldoinicial[$i]), 2) . '</td>';
                $tablasi = $tablasi . '<td nowrap style="background-color:#F5DA81; font-size:x-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($arrsaldoinicial[$i]), 2) . '</td>';
                $tablasi = $tablasi . '<td nowrap style="background-color:#F5DA81; font-size:x-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($arrsaldoinicial[$i]), 2) . '</td>';
                $tablasi = $tablasi . '<td nowrap style="background-color:#F5DA81; font-size:x-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($arrsaldoinicial[$i]), 2) . '</td>';
            } else {
                $tablasi = $tablasi . '<td nowrap style="background-color:#F5DA81; font-size:x-small; font-weight:bold; text-align:right;">' . number_format($arrsaldoinicial[$i], 2) . '</td>';
                $tablasi = $tablasi . '<td nowrap style="background-color:#F5DA81; font-size:x-small; font-weight:bold; text-align:right;">' . number_format($arrsaldoinicial[$i], 2) . '</td>';
                $tablasi = $tablasi . '<td nowrap style="background-color:#F5DA81; font-size:x-small; font-weight:bold; text-align:right;">' . number_format($arrsaldoinicial[$i], 2) . '</td>';
                $tablasi = $tablasi . '<td nowrap style="background-color:#F5DA81; font-size:x-small; font-weight:bold; text-align:right;">' . number_format($arrsaldoinicial[$i], 2) . '</td>';
            }
        }
    }//
    if ($arrsaldoinicial[$_POST['fromweek'] - 1] < 0) {
        $tablasi = $tablasi . '<td style="font-size:x-small; font-weight:bold; background-color:#FFFF00; text-align:right; color:red;">' . number_format(abs($arrsaldoinicial[$_POST['fromweek'] - 1]), 2) . '</td>';
    } else {
        $tablasi = $tablasi . '<td style="font-size:x-small; font-weight:bold; background-color:#FFFF00; text-align:right;">' . number_format($arrsaldoinicial[$_POST['fromweek'] - 1], 2) . '</td>';
    }

    $tablasi = $tablasi . '</tr>';
    */

    $tabla1 = $tabla1 . '</tr>';
    $tabla1 = $tabla1 . '<tr style="background-color:#ECF6CE">';
    $tabla1 = $tabla1 . '<td style="font-size:x-small; font-weight:bold; text-align:center;">' . _('Saldo Final') . '</td>';
    for ($i = ($_POST['fromweek'] - 1); $i < $contsemanas; $i++) {

        $diffxsemana = abs(abs($arrtotalesbproyeccionxsemana[$i]) - abs($arrtotalesbancosxsemana[$i]));
        if($arrtotalesbproyeccionxsemana[$i] == 0 and $arrtotalesbancosxsemana[$i] != 0){
            $diffporcxsemana = 100;
        }elseif($arrtotalesbproyeccionxsemana[$i] != 0 and $arrtotalesbancosxsemana[$i] == 0){
            $diffporcxsemana = 100;
        }else{
            $diffporcxsemana = ($diffxsemana*100) / $arrtotalesbproyeccionxsemana[$i];
        }

        if (($arrsemanas[$i] >= $_POST['fromweek']) and ( $arrsemanas[$i] <= $_POST['toweek'])) {
            if (($arrtotalesbproyeccionxsemana[$i] + $arrsaldoinicial[$i]) < 0) {
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($arrtotalesbancosxsemana[$i] + $arrsaldoinicial[$i]), 2) . '</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($arrtotalesbproyeccionxsemana[$i] + $arrsaldoinicial[$i]), 2) . '</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($diffxsemana + $arrsaldoinicial[$i]), 2) . '</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($diffporcxsemana + $arrsaldoinicial[$i]), 2) . '%</td>';
            } else {
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($arrtotalesbancosxsemana[$i] + $arrsaldoinicial[$i]), 2) . '</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($arrtotalesbproyeccionxsemana[$i] + $arrsaldoinicial[$i]), 2) . '</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($diffxsemana + $arrsaldoinicial[$i]), 2) . '</td>';
                $tabla1 = $tabla1 . '<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($diffporcxsemana + $arrsaldoinicial[$i]), 2) . '%</td>';
            }
        }
    }
    
    if (($saldoinicial + $totales) < 0) {
        $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right; color:red;">' . number_format(abs($saldoinicial + $totales), 2) . '</td>';
    } else {
        $tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right;">' . number_format(($saldoinicial + $totales), 2) . '</td>';
    }
    
    $tabla1 = $tabla1 . '</tr>';

    $tabla1 = $tabla0 . $tablasi . $tabla1 . '</table>';
    echo $tabla1;
}
if (isset($_POST ['PrintEXCEL'])) {
    exit;
}

include('includes/footer.inc');

function Traesaldoinicial($anio, $semana, $legalid, $funcion, $BankAccount, $checkbanco, $checkCxC, $checkCxP, $checkP, $semanainicial, $FechaProy, $db) {

    $sqlfechainicial = "SELECT MIN(u_tiempo) as u_tiempo
						FROM DWH_Tiempo
						WHERE anio = '" . $anio . "'
							and semana = '" . $semanainicial . "'";
    $resultfechainicial = DB_query($sqlfechainicial, $db);
    if ($myrowfechainicial = DB_fetch_array($resultfechainicial, $db)) {
        $u_tiempoinicial = $myrowfechainicial['u_tiempo'];
    }

    $SQLBanco = "SELECT
					sum(banktrans.amount) as saldo
			FROM banktrans
				INNER JOIN DWH_Tiempo ON year(banktrans.transdate) = DWH_Tiempo.Anio
	 					and month(banktrans.transdate) = DWH_Tiempo.Mes
	 					and day(banktrans.transdate) = DWH_Tiempo.Dia
				INNER JOIN tagsxbankaccounts ON tagsxbankaccounts.accountcode = banktrans.bankact
					AND tagsxbankaccounts.tagref = banktrans.tagref
					AND tagsxbankaccounts.tagref in 
						(select tagref from tags where (legalid in (" . trim($legalid) . ") or '" . $legalid . "' = '-1' ))
				INNER JOIN chartmaster ON banktrans.bankact = chartmaster.accountcode
				INNER JOIN tags ON tags.tagref=banktrans.tagref
				INNER JOIN systypescat ON systypescat.typeid=banktrans.type
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref 
						AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			WHERE DWH_Tiempo.u_tiempo < '" . $u_tiempoinicial . "'
				AND abs(banktrans.amount)!=0";
    if (isset($legalid) && $legalid != '-1') {
        $SQLBanco = $SQLBanco . " AND tags.legalid in (" . trim($legalid) . ")";
    }

    if (isset($BankAccount) && $BankAccount != '-1') {
        $SQLBanco = $SQLBanco . " AND banktrans.bankact='" . $BankAccount . "'";
    }


    //echo '<pre>sql:'.$SQLBanco;.
    // Consulta movimientos de cxp

    $SQLCxP = "	SELECT
					sum((((supptrans.ovamount+supptrans.ovgst)-alloc)*-1)) as saldo
			FROM supptrans
				INNER JOIN DWH_Tiempo ON year(supptrans.promisedate) = DWH_Tiempo.Anio
	 					and month(supptrans.promisedate) = DWH_Tiempo.Mes
	 					and day(supptrans.promisedate) = DWH_Tiempo.Dia
				INNER JOIN systypesbyreport ON systypesbyreport.typedoc=supptrans.type and systypesbyreport.functionid=" . $funcion . "
				INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid
				INNER JOIN tags ON tags.tagref=supptrans.tagref
				INNER JOIN systypescat ON systypescat.typeid=supptrans.type
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			WHERE DWH_Tiempo.u_tiempo < '" . $u_tiempoinicial . "'
					AND abs(((supptrans.ovamount+supptrans.ovgst)-alloc))!=0
		 ";
    if (isset($legalid) && $legalid != '-1') {
        $SQLCxP = $SQLCxP . " AND tags.legalid in (" . trim($legalid) . ")";
    }
    // consulta movimientos de cxc
    $SQLCxC = "	SELECT
			sum(case when debtortrans.ovamount+debtortrans.ovgst>0 then ((debtortrans.ovamount+debtortrans.ovgst)-alloc)
			else  ((debtortrans.ovamount+debtortrans.ovgst)-alloc) end ) as saldo
			FROM debtortrans
				INNER JOIN DWH_Tiempo ON year(debtortrans.duedate) = DWH_Tiempo.Anio
	 					and month(debtortrans.duedate) = DWH_Tiempo.Mes
	 					and day(debtortrans.duedate) = DWH_Tiempo.Dia
				INNER JOIN systypesbyreport ON systypesbyreport.typedoc=debtortrans.type and systypesbyreport.functionid=" . $funcion . "
				INNER JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
				INNER JOIN tags ON tags.tagref=debtortrans.tagref
				INNER JOIN systypescat ON systypescat.typeid=debtortrans.type
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			WHERE DWH_Tiempo.u_tiempo < '" . $u_tiempoinicial . "'
					AND abs(((debtortrans.ovamount+debtortrans.ovgst)-alloc))!=0
		 ";
    if (isset($legalid) && $legalid != '-1') {
        $SQLCxC = $SQLCxC . " AND tags.legalid in (" . trim($legalid) . ")";
    }

    // consulta movimientos de proyeccion
    $SQLProy = "	SELECT
					sum(amount) as saldo
			FROM fjo_Movimientos
				INNER JOIN DWH_Tiempo ON year(fjo_Movimientos.fechapromesa) = DWH_Tiempo.Anio
	 					and month(fjo_Movimientos.fechapromesa) = DWH_Tiempo.Mes
	 					and day(fjo_Movimientos.fechapromesa) = DWH_Tiempo.Dia
				INNER JOIN tags ON tags.tagref=fjo_Movimientos.tagref
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			WHERE DWH_Tiempo.u_tiempo < '" . $u_tiempoinicial . "'
			  			AND  fjo_Movimientos.fechapromesa >= '" . $FechaProy . "' 
					AND abs(amount)!=0";
    if (isset($_POST['legalid']) && $_POST['legalid'] != '-1') {
        $SQLProy = $SQLProy . " AND tags.legalid in (" . trim($legalid) . ")";
    }

    $unionCXC = ' UNION ';
    if ($checkbanco != 'checked') {
        $SQLBanco = '';
        $unionCXC = '';
    }
    $unionCXP = ' UNION ';
    if ($checkCxC != 'checked') {
        $SQLCxC = '';
        $unionCXP = '';
    }
    $unionP = ' UNION ';
    if ($checkCxP != 'checked') {
        $SQLCxP = '';
        $unionP = '';
    }
    if ($checkP != 'checked') {
        $SQLProy = '';
    }
     //echo '<pre>sql:<br>'.$SQLProy;
    /**/
    $SQL = $SQLBanco . $unionCXC . $SQLCxC . $unionCXP . $SQLCxP . $unionP . $SQLProy;

    //$SQL = $SQLBanco . ' UNION ' . $SQLCxC . ' UNION ' . $SQLCxP  . ' UNION ' . $SQLProy;

    $validaunion = left(strrev($SQL), 6);
    if (trim($validaunion) == 'NOINU') {
        $sqllen = strlen($SQL) - 6;
        $SQL = left($SQL, $sqllen);
    }

    $SQLSaldo = "select sum(saldo) as saldofin from (";
    $SQLSaldo = $SQLSaldo . $SQL;
    $SQLSaldo = $SQLSaldo . ") as saldofin";

    //echo '<pre>sql:'.$SQLSaldo.'<br><br>';
    $Result = DB_query($SQLSaldo, $db);
    $Row = DB_fetch_row($Result);
    return $Row[0];
}

?>