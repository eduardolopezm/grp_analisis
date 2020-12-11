<?php

$PageSecurity = 8;
$funcion=110;

include('includes/session.inc');
$title = _('Balanza de Comprobación');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');
include('includes/AccountSectionsDef.inc');
include("includes/SecurityUrl.php"); 
setlocale(LC_ALL, 'es_ES');

$tagref= $_GET['selectUnidadNegocio'];
$unidadejecutora= $_GET['selectUnidadEjecutora'];
$nivelDes=$_GET['NivelDesagregacion'];
$NivelDesagregacionTexto = '';
if (isset($_GET['NivelDesagregacionTexto'])) {
    $NivelDesagregacionTexto = '_'.$_GET['NivelDesagregacionTexto'];
}
$noZeroes=$_GET['noZeroes'];
$txtFiltroCuentas=$_GET['txtFiltroCuentas'];

$nombreArchivo = traeNombreFuncionGeneral($funcion, $db) . '_' . date('dmY').$NivelDesagregacionTexto;

$nombreArchivo = str_replace(" ", "_", $nombreArchivo);

//Inicio de exportación en Excel
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=$nombreArchivo.xls");
header("Pragma: no-cache");
header("Expires: 0");

$SQLTag = "";
if (!empty($tagref)) {
    $SQLTag = " AND gltrans.tag ='".$tagref."'";
    $SQLGroupby=$SQLGroupby.', tags.tagref ';
}

$SQLNivel = "";
if ($nivelDes<>0) {
    $SQLNivel= ' AND chartmaster.nu_nivel >= '.$nivelDes.' ';
}

$SQLUE = "";

if(isset($_GET['selectUnidadEjecutora']) and $_GET['selectUnidadEjecutora'] !=""){
	$SQLUE= " AND gltrans.ln_ue IN ( ".$_GET['selectUnidadEjecutora']." )";
}



if($_GET['cmbTipoBalanza'] == 1){
    $_GET['FromPeriod'] = ($_GET['ToPeriod'] - 1);
}

$SQL="";
    $SQL="SELECT    SUBSTRING_INDEX(accountcode,'.', ".$nivelDes.") as groupCode,
                    -- accountsection.sectionname,
                    accountgroups.groupname,
                    accountgroups.parentgroupname,
                    accountgroups.pandl,
                    chartmaster.accountcode ,
                    chartmaster.accountname,
                    chartmaster.tipo,
                    accountgroups.sequenceintb,
                    chartmaster.naturaleza,

                    coalesce(dtInicialApertura.prdInicial,0) as prdInicialApertura,
                    coalesce(dtInicialApertura.saldoInicialCargo,0) as prdInicialCargoApertura,
                    coalesce(dtInicialApertura.saldoInicialAbonos,0) as prdInicialAbonoApertura,

                    coalesce(dtInicial.prdInicial,0 ) + coalesce(dtMovimientos.saldoInicial,0) as prdInical,
                    coalesce(dtInicial.saldoInicialCargo,0) + coalesce(dtMovimientos.saldoInicialCargo,0) as prdInicialCargo,
                    coalesce(dtInicial.saldoInicialAbonos,0) + coalesce(dtMovimientos.saldoInicialAbonos,0) as prdInicialAbono,

                    coalesce(dtMovimientos.prdActual,0) as prdActual,
                    coalesce(dtMovimientos.prdCargos,0) as prdCargos,
                    coalesce(dtMovimientos.prdAbonos,0)  as prdAbonos,

                    coalesce(dtFinal.saldoFinalCargo,0) as saldoFinalCargo,
                    coalesce(dtFinal.saldoFinalAbonos,0) as saldoFinalAbonos,
                    coalesce(dtFinal.prdFinal,0) AS prdFinal,

                    coalesce(dtFinal.prdFinal,0) AS monthactual,
                    coalesce(dtFinal.prdFinal,0) AS monthbudget,
                    0 AS firstprdbudgetbfwd,
                    0 AS lastprdbudgetcfwd
        FROM chartmaster
        LEFT JOIN (SELECT SUBSTRING_INDEX(gltrans.account, '.', ".$nivelDes.") as account,
                            SUM(gltrans.amount) as prdActual,
                            SUM(CASE WHEN gltrans.type != 0 and  gltrans.amount >=0 THEN gltrans.amount ELSE 0 END) AS prdCargos,
                            SUM(CASE WHEN gltrans.type != 0 and gltrans.amount <0 THEN gltrans.amount*-1 ELSE 0 END) AS prdAbonos,
                            SUM(CASE WHEN gltrans.type = 0  THEN gltrans.amount ELSE 0 END) AS saldoInicial,
                            SUM(CASE WHEN gltrans.type = 0  and gltrans.amount >=0 THEN gltrans.amount ELSE 0 END) AS saldoInicialCargo,
                            SUM(CASE WHEN gltrans.type = 0  and gltrans.amount <0 THEN gltrans.amount ELSE 0 END) AS saldoInicialAbonos
                    FROM gltrans 
                    INNER JOIN sec_unegsxuser ON gltrans.tag = sec_unegsxuser.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
                    WHERE gltrans.periodno >= ".$_GET['FromPeriod']." 
                            AND gltrans.periodno <= ".$_GET['ToPeriod']." 
                            AND gltrans.account != ''
                            AND gltrans.posted = 1
                            AND SUBSTRING_INDEX(account, '.', ".$nivelDes.") in (select accountcode from chartmaster where chartmaster.nu_nivel=".$nivelDes.")
                            ".$SQLTag."
                            ".$SQLUE."
                    GROUP BY  SUBSTRING_INDEX(gltrans.account, '.', ".$nivelDes."))  as dtMovimientos
        ON chartmaster.accountcode = dtMovimientos.account
        
        LEFT JOIN (SELECT SUBSTRING_INDEX(account, '.', ".$nivelDes.") as account,
                            SUM(amount) as prdInicial,
                            SUM(CASE WHEN  gltrans.amount >=0 THEN gltrans.amount ELSE 0 END) AS saldoInicialCargo,
                            SUM(CASE WHEN  gltrans.amount <0 THEN gltrans.amount ELSE 0 END) AS saldoInicialAbonos
                    FROM  gltrans  
                    INNER JOIN sec_unegsxuser ON gltrans.tag = sec_unegsxuser.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
                    WHERE periodno < ".$_GET['FromPeriod']." 
                        AND gltrans.account != ''
                        AND gltrans.posted = 1
                        AND SUBSTRING_INDEX(account, '.', ".$nivelDes.") in (select accountcode from chartmaster where chartmaster.nu_nivel=".$nivelDes.")
                        ".$SQLTag."
                        ".$SQLUE."
                    group by SUBSTRING_INDEX(account, '.', ".$nivelDes.")) dtInicial
        ON chartmaster.accountcode = dtInicial.account

        LEFT JOIN (SELECT SUBSTRING_INDEX(account, '.', ".$nivelDes.") as account,
                            SUM(amount) as prdInicial,
                            SUM(CASE WHEN gltrans.type = 0 AND  gltrans.amount >=0 THEN gltrans.amount ELSE 0 END) AS saldoInicialCargo,
                            SUM(CASE WHEN gltrans.type = 0 AND gltrans.amount <0 THEN gltrans.amount ELSE 0 END) AS saldoInicialAbonos
                    FROM  gltrans  
                    INNER JOIN sec_unegsxuser ON gltrans.tag = sec_unegsxuser.tagref AND sec_unegsxuser.userid = 'desarrollo'
                    WHERE periodno <= ".$_GET['FromPeriod']." 
                        AND gltrans.type = 0 
                        AND gltrans.account != ''
                        AND gltrans.posted = 1
                        AND SUBSTRING_INDEX(account, '.', ".$nivelDes.") in (select accountcode from chartmaster where chartmaster.nu_nivel=".$nivelDes.")
                        ".$SQLTag."
                        ".$SQLUE."
                    group by SUBSTRING_INDEX(account, '.', ".$nivelDes.")) dtInicialApertura
        ON chartmaster.accountcode = dtInicialApertura.account

        LEFT JOIN (SELECT SUBSTRING_INDEX(account, '.', ".$nivelDes.") as account,
                    SUM(amount) as prdFinal,
                    SUM(CASE WHEN gltrans.amount >=0 THEN gltrans.amount ELSE 0 END) AS saldoFinalCargo,
                    SUM(CASE WHEN gltrans.amount <0 THEN gltrans.amount ELSE 0 END) AS saldoFinalAbonos
                    FROM  gltrans  
                    INNER JOIN sec_unegsxuser ON gltrans.tag = sec_unegsxuser.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
                    WHERE periodno <= ".$_GET['ToPeriod']." 
                        AND gltrans.account != ''
                        AND gltrans.posted = 1
                        AND SUBSTRING_INDEX(account, '.', ".$nivelDes.") in (select accountcode from chartmaster where chartmaster.nu_nivel=".$nivelDes.")
                        ".$SQLTag."
                        ".$SQLUE."
                    group by SUBSTRING_INDEX(account, '.', ".$nivelDes.")) dtFinal
        ON chartmaster.accountcode = dtFinal.account

        LEFT JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
        -- LEFT JOIN accountsection ON accountgroups.sectioninaccounts = accountsection.sectionid
        WHERE chartmaster.nu_nivel=".$nivelDes;

        if(isset($_GET['txtFiltroCuentas']) && $_GET['txtFiltroCuentas'] !=""){
            $SQL = $SQL . " AND CONCAT(chartmaster.accountcode,' ', chartmaster.accountname) like '%".$_GET['txtFiltroCuentas']."%' ";
        }

        if ($noZeroes==1) {
            $SQL = $SQL . ' HAVING (abs(prdCargos) > 0.1 OR abs(prdAbonos) > 0.1)';
        } elseif ($noZeroes==2) {
            $SQL = $SQL . ' HAVING (abs(prdFinal) > 0.1) ';
        } elseif ($noZeroes==3) {
            $SQL = $SQL . ' HAVING abs(prdFinal) > 0.1 and (abs(prdCargos) > 0.1 OR abs(prdAbonos) > 0.1) ';
        } elseif ($noZeroes==4) {
            $SQL = $SQL . ' HAVING abs(prdFinal) > 0.1 or (abs(prdCargos) > 0.1 OR abs(prdAbonos) > 0.1) ';
        }

        $SQL . " ORDER BY chartmaster.accountcode;";


if ($_SESSION['UserID'] == "desarrollo") {
    //echo '<pre>'.$SQL.'</pre>';
}

$AccountsResult = DB_query($SQL,$db,_('Ninguna cuenta contable se recupero por el SQL porque'),_('El SQL que fallo fue:'));

$sqlPeriodName="SELECT periodno, lastdate_in_period FROM periods where periodno=".($_GET['ToPeriod'])." ORDER BY periodno DESC limit 1;";
$resultPeriodName=DB_query($sqlPeriodName,$db);
$myrowPeriodName=DB_fetch_array($resultPeriodName);

$strEncabezado="";



?>
    <table id="tblCuentas" cellpadding="2" border="1" class="tableHeaderVerde">

        <tr>
            <th colspan="2"> 
            </th>

            <?php 
            if($_GET['cmbTipoBalanza'] == 1){ 
                $sqlPeriodAcumuladoName="SELECT periodno, lastdate_in_period FROM periods where periodno=".$_GET['FromPeriod']." ORDER BY periodno DESC limit 1;";
                $resultPeriodAcumuladoName=DB_query($sqlPeriodAcumuladoName,$db);
                $myrowPeriodAcumuladoName=DB_fetch_array($resultPeriodAcumuladoName);

                echo '  <th colspan = "2" class="text-center">Saldo Inicial</th>
                        <th colspan = "2" class="text-center">Saldo Acumulado <br> '.MonthAndYearFromSQLDate($myrowPeriodAcumuladoName['lastdate_in_period']).'</th>';
            }else{
                echo '<th colspan = "2" class="text-center">Saldo Inicial</th>';
            }
            ?>
            <th colspan = "2" class="text-center"><?php echo htmlentities('Movimientos Del Período', ENT_QUOTES, "UTF-8") ;?><br><?php echo MonthAndYearFromSQLDate($myrowPeriodName['lastdate_in_period']);?></th>
            <th colspan = "2" class="text-center">Saldo Final</th>
        </tr>
        <?php 
            $strEncabezado = '<tr class="datosFiltro" data-info="0">
                                <th class="text-center">C&oacute;digo Cuenta</th>
                                <th class="text-center">Nombre Cuenta</th>'

        ?>
        <tr >
            <th class="text-center">C&oacute;digo Cuenta</th>
            <th class="text-center">Nombre Cuenta</th>

            <?php 
            if($_GET['cmbTipoBalanza'] == 1){ 
                $strEncabezado .= '<th class="text-center">Deudora</th>
                        <th class="text-center">Acreedora</th>
                        <th class="text-center">Cargos</th>
                        <th class="text-center">Abonos</th>';

                echo '  <th class="text-center">Deudora</th>
                        <th class="text-center">Acreedora</th>
                        <th class="text-center">Cargos</th>
                        <th class="text-center">Abonos</th>';
            }else{
                $strEncabezado .= '<th class="text-center">Deudora</th>
                                    <th class="text-center">Acreedora</th>';

                echo '  <th class="text-center">Deudora</th>
                        <th class="text-center">Acreedora</th>';
            }

            $strEncabezado .= '<th class="text-center">Cargos</th>
                                <th class="text-center">Abonos</th>
                                <th class="text-center">Deudora</th>
                                <th class="text-center">Acreedora</th>
                            </tr>';
            ?>

            <th class="text-center">Cargos</th>
            <th class="text-center">Abonos</th>
            <th class="text-center">Deudora</th>
            <th class="text-center">Acreedora</th>
        </tr>

        <?php 
        $totalInicialCargo=0;
        $totalInicialAbono=0;
        $totalCargo=0;
        $totalAbono=0;
        $totalFinalCargo=0;
        $totalFinalAbono=0;

        $contadorEncabezado=0;
        $recorridoEncabezado=0;

        $resultInicialAperturaAcredor=0;
        $resultInicialAperturaDeudor=0;
        $resultInicialAcredor=0;
        $resultInicialDeudor=0;
        $resultFinalAcredor=0;
        $resultFinalDeudor=0;

        $numRegistros==DB_num_rows($AccountsResult);

        while ($rs=DB_fetch_array($AccountsResult)){
            $totalInicialCargo += $rs['prdInicialCargo'];
            $totalInicialAbono += $rs['prdInicialAbono'];
            $totalCargo += $rs['prdCargos'];
            $totalAbono += $rs['prdAbonos'];
            $totalFinalCargo += $rs['saldoFinalCargo'];
            $totalFinalAbono += $rs['saldoFinalAbonos'];
            $totalInicialCargoApertura += $rs['prdInicialCargoApertura'];
            $totalInicialAbonoApertura += $rs['prdInicialAbonoApertura'];
            $contadorEncabezado++;
            $recorridoEncabezado++;



            if($contadorEncabezado == 50 and $recorridoEncabezado != $numRegistros){
                $contadorEncabezado = 0;
                echo $strEncabezado;
            }

            echo "<tr class='datosFiltro' data-info='1' data-cuenta='".$rs['accountcode'] ." ".$rs['accountname']."'>
                    <td>". $rs['accountcode'] ." </td>
                    <td>". utf8_decode($rs['accountname'])." </td>";
            
            if($_GET['cmbTipoBalanza'] == 1){
                
                $prdInicialAperturaAcredor=0;
                $prdInicialAperturaDeudor=0;
                

                if($rs['prdInicialApertura']<0){
                    $prdInicialAperturaAcredor=$rs['prdInicialApertura'];
                    $prdInicialAperturaDeudor=0;
                    $resultInicialAperturaAcredor += $rs['prdInicialApertura'];
                    $resultInicialAperturaDeudor+=0;
                }else{
                    $prdInicialAperturaAcredor=0;
                    $prdInicialAperturaDeudor=$rs['prdInicialApertura'];
                    $resultInicialAperturaAcredor +=0;
                    $resultInicialAperturaDeudor+=$rs['prdInicialApertura'];
                }

                echo "<td style='text-align:right;'>". number_format($prdInicialAperturaDeudor, $_SESSION['DecimalPlaces'])." </td>
                      <td style='text-align:right;'>". number_format(abs($prdInicialAperturaAcredor ), $_SESSION['DecimalPlaces'])." </td>";
            }

            $prdInicialAcredor=0;
            $prdInicialDeudor=0;
            
            if($rs['prdInical'] < 0){
                $prdInicialAcredor=$rs['prdInical'];
                $prdInicialDeudor=0;
                $resultInicialAcredor+=$rs['prdInical'];
                $resultInicialDeudor+=0;
            }else{
                $prdInicialAcredor=0;
                $prdInicialDeudor=$rs['prdInical'];
                $resultInicialAcredor+=0;
                $resultInicialDeudor+=$rs['prdInical'];
            }

            echo "  <td style='text-align:right;'>". number_format($prdInicialDeudor, $_SESSION['DecimalPlaces'])." </td>
                    <td style='text-align:right;'>". number_format(abs($prdInicialAcredor), $_SESSION['DecimalPlaces'])."</td>";

            echo "  <td style='text-align:right;'>". number_format($rs['prdCargos'], $_SESSION['DecimalPlaces']) ."</td>
                    <td style='text-align:right;'>". number_format(abs($rs['prdAbonos']), $_SESSION['DecimalPlaces']) ."</td>";

            $prdFinalAcredor=0;
            $prdFinalDeudor=0;

            if($rs['prdFinal']<0){
                $prdFinalAcredor=$rs['prdFinal'];
                $prdFinalDeudor=0;
                $resultFinalAcredor+=$rs['prdFinal'];
                $resultFinalDeudor+=0;
            }  else{
                $prdFinalAcredor=0;
                $prdFinalDeudor=$rs['prdFinal'];
                $resultFinalAcredor+=0;
                $resultFinalDeudor+=$rs['prdFinal'];
            }

            echo "  <td style='text-align:right;'>". number_format($prdFinalDeudor, $_SESSION['DecimalPlaces']) ."</td>
                    <td style='text-align:right;'>". number_format(abs($prdFinalAcredor), $_SESSION['DecimalPlaces']) ."</td>";

            echo " </tr>";
        }

        echo ' <tr class="datosTotales">
                    <td colspan="2" style="text-align: right;"><strong> Verificaci&oacute;n de Totales </strong></td>';

        if($_GET['cmbTipoBalanza'] == 1){
            echo "  <td style='text-align:right;'>". number_format($resultInicialAperturaDeudor, $_SESSION['DecimalPlaces'])." </td>
                    <td style='text-align:right;'>". number_format(abs($resultInicialAperturaAcredor), $_SESSION['DecimalPlaces'])." </td>";
        }

        echo '      <td style="text-align: right;"> ' . number_format($resultInicialDeudor, $_SESSION['DecimalPlaces']) . '</td>
                    <td style="text-align: right;"> ' . number_format(abs($resultInicialAcredor), $_SESSION['DecimalPlaces']) . '</td>
                    <td style="text-align: right;"> ' . number_format($totalCargo, $_SESSION['DecimalPlaces']) . ' </td>
                    <td style="text-align: right;"> ' . number_format(abs($totalAbono), $_SESSION['DecimalPlaces']) . '</td>
                    <td style="text-align: right;"> ' . number_format($resultFinalDeudor, $_SESSION['DecimalPlaces']) . '</td>
                    <td style="text-align: right;"> ' . number_format(abs($resultFinalAcredor), $_SESSION['DecimalPlaces']) . '</td>
                </tr>';
    ?>

    </table>

