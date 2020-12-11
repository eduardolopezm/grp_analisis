<?php

/**
 * Depreciacion de activos fijos.
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link /FixedAssetDepreciation.php
 * Fecha Creación: 26.05.18
 * Se genera el presente programa para la depreciacion del activo fijo.
 */

/* DECLARACION DE VARIABLES */
$PageSecurity = 3;
$PathPrefix = './';
$funcion = 1842;

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');

$title= traeNombreFuncion($funcion, $db);
require 'includes/header.inc';
require 'javascripts/libreriasGrid.inc';
$ocultaDepencencia = 'hidden';
$ViewTopic = 'FixedAssets';
$BookMark = 'AssetDepreciation';

$optionLegalid="";
$sqlLegalid="SELECT legalid,legalname FROM legalbusinessunit";
$resultLegalid = DB_query($sqlLegalid,$db);
$index=0;
//

if(DB_num_rows($resultLegalid) >=1){
    while ( $myrowLegalid = DB_fetch_array($resultLegalid)) {
        if(isset($_POST['legalid']) and $_POST['legalid'] !=""){
            if($_POST['legalid'] == $myrowLegalid['legalid'] ){
                $optionLegalid .= "<option value = '". $myrowLegalid['legalid'] ."' selected> ". $myrowLegalid['legalname'] ."</option>";
            }else{
                $optionLegalid .= "<option value = '". $myrowLegalid['legalid'] ."'> ". $myrowLegalid['legalname'] ."</option>";
            }
        }else{
            if($index ==0 ){
                $optionLegalid .= "<option value = '". $myrowLegalid['legalid'] ."' selected> ". $myrowLegalid['legalname'] ."</option>";
                $_POST['legalid']=$myrowLegalid['legalid'];
            }else{
                $optionLegalid .= "<option value = '". $myrowLegalid['legalid'] ."'> ". $myrowLegalid['legalname'] ."</option>";
            }
        }
        $index=$index+1;
        
    }

}

$cssProcesando="display:none;";
if(isset($_POST['CommitDepreciation'])){
    $cssProcesando="display:inline;";
}

?>

<script type="text/javascript" src="javascripts/layout_general.js"></script>


<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" >
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<link rel="stylesheet" href="javascripts/multiselect/bootstrap-multiselect.css" type="text/css">
<link rel="stylesheet" href="javascripts/DataTables/jquery.dataTables.min.css" type="text/css">
<link rel="stylesheet" href="javascripts/DataTables/dataTables.bootstrap.min.css" type="text/css">
<link rel="stylesheet" href="Angular1/bootstrap-multiselect.css" type="text/css" /> -->



<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'); ?>" method="POST" >
    <input type="hidden" id="postUR" name="postUR" value="<?php echo $_POST['selectUnidadNegocio']; ?>">
    <input type="hidden" id="postUE" name="postUE" value="<?php echo $_POST['selectUnidadEjecutora']; ?>">
<div class="">
    <!-- <div id="wrapper-container" name="wrapper-container" style="<?php echo $cssProcesando;?>"></div>
    <br />
    <center>
        <div id="procesando" style="<?php echo $cssProcesando;?>left:45%; top:15em; z-index:1000; position:absolute; " ><img src="images/loading.gif" width="100" height="100" style="vertical-align: middle;"></div>
    </center>-->
    <!-- <div style="text-align: right; margin-bottom: 20px;"><a href="<?php echo $RootPath; ?>/PO_SelectOSPurchOrder.php">Lista de ordenes de compra </a></div>  -->
    <div id="divHeaderConsultar" class="">
        <div class="panel-group">
            <div class="panel panel-default">
                <div class="panel-heading h35">
                    <h4 class="panel-title">
                        <!-- <a data-toggle="collapse" href="#closeTab"> <strong>CRITERIOS DE BÚSQUEDA</strong> </a> -->
                        <div class="fl text-left">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#closeTab" aria-expanded="true" aria-controls="collapseOne">
                                <b>Criterios de Filtrado</b>
                            </a>
                        </div>
                    </h4>
                </div><!-- .panel-heading -->
                <div id="closeTab" class="panel-collapse collapse in">
                    <div class="panel-body">
                        <div class="row" id="form-search">
                            <!-- dependencia, UR -->
                            <div class="col-md-4">

                                <div class="form-inline row <?= $ocultaDepencencia ?>">
                                    <div class="col-md-3">
                                        <span><label>Dependencia: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="legalid" name="legalid" class="form-control selectRazonSocial" onchange="fnCambioDependeciaGeneral('selectRazonSocial', 'selectUnidadNegocio')"></select>
                                    </div>
                                </div>

                                <br class="<?= $ocultaDepencencia ?>">

                                <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>UR: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" data-todos="true"   onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')"></select>
                                        <!-- <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" multiple="multiple" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')"></select> -->
                                    </div>
                                </div>
                            </div><!-- -col-md-4 -->

                            <!-- UE -->
                            <div class="col-md-4">
                                <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>UE: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" data-todos="true" ></select>
                                    </div>
                                </div>
                            </div><!-- -col-md-4 -->

                        </div>
                        <br>
                        <div class="row">
                            <component-button type="button" id="btnBuscarValidacion" name ="btnBuscarValidacion" class="glyphicon glyphicon-search"  value="Filtrar"></component-button>
                            <component-button type="submit" id="btnBuscar" name ="btnBuscar" class="glyphicon glyphicon-search hide"  value="Filtrar"></component-button>
                            <!-- <button class="btn btn-primary btn-green" id="btn-search"><i class="fa fa-search"></i>&nbsp;Filtrar</button> -->
                            <!-- FIXME agregar comportamiento para los tipos de permisos -->
                            <!-- <component-button type="button" id="btn-modal-upload" class="glyphicon glyphicon-file" value="Cargar Archivo"></component-button> -->
                            <!-- <component-button type="button" id="from-existing" class="glyphicon glyphicon-file" value="Partir de un Existente"></component-button> -->
                        </div>
                    </div><!-- .panel-body -->
                </div><!-- .panel-collapse -->
            </div><!-- .panel -->
        </div><!-- .panel-group -->
    </div><!-- .row -->
</div>


<?php
if(isset($_POST['btnBuscar']) or isset($_POST['CommitDepreciation'])){
    //echo var_dump($_POST);

    /*Get the last period depreciation (depn is transtype =44) was posted for */

    $sqlFecha="SELECT periods.lastdate_in_period, max(fixedassettrans.periodno)
                        FROM fixedassettrans 
                        INNER JOIN fixedassets 
                        ON  fixedassettrans.assetid = fixedassets.assetid
                        INNER JOIN periods
                        ON fixedassettrans.periodno=periods.periodno
                        WHERE transtype=44 ";
    
    if(isset($_POST['selectUnidadNegocio']) and $_POST['selectUnidadNegocio'] !=""){
        $sqlFecha .= " AND fixedassets.tagrefowner = '". $_POST['selectUnidadNegocio']."'" ;
    }

    if(isset($_POST['selectUnidadEjecutora']) and $_POST['selectUnidadEjecutora'] !=""){
        $sqlFecha .= " AND fixedassets.ue = '". $_POST['selectUnidadEjecutora']."'" ;
    }

    $sqlFecha .=" GROUP BY periods.lastdate_in_period
                  ORDER BY periods.lastdate_in_period DESC;";

    $result = DB_query($sqlFecha, $db);

    $LastDepnRun = DB_fetch_row($result);

    $AllowUserEnteredProcessDate = true;


    $_POST['LastUsePeriod']=0;
    if (DB_num_rows($result)==0) { //then depn has never been run yet?
        /*in this case default depreciation calc to the last day of last month - and allow user to select a period */
        if (!isset($_POST['ProcessDate'])) {
            $_POST['ProcessDate'] = Date($_SESSION['DefaultDateFormat'],mktime(0,0,0,date('m'),0,date('Y')));
        } else { //ProcessDate is set - make sure it is on the last day of the month selected
            if (!Is_Date($_POST['ProcessDate'])){
                prnMsg(_('The date is expected to be in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
                $InputError =true;
            }else {
                $_POST['ProcessDate'] = LastDayOfMonth($_POST['ProcessDate']);
            }
        }

    } else { //depn calc has been run previously
        $AllowUserEnteredProcessDate = false;
        $arrfecha = explode('-',$LastDepnRun[0]);
        $newdate = $arrfecha[0] . "/" . $arrfecha[1] . "/" . $arrfecha[2];  

        if (($arrfecha[1]) != 1){
            $diasmas = 30;
        }else{
            $diasmas = 28;
        }
        
        // echo "<br>NEW DATE: " . $newdate;
        // echo "<br>CONVERT: " . ConvertSQLDate($newdate);
        // echo "<br>CONVERT2: " . DateAdd(($newdate),'d',$diasmas);
        // echo "<br>DIAS MES: " . $diasmas;

        $_POST['ProcessDate'] = LastDayOfMonth(DateAdd(($newdate),'d',$diasmas));
        $_POST['LastUsePeriod']= $LastDepnRun[1];
        // echo "<br>PROCESS: " . $_POST['ProcessDate'];
    }

    $PeriodNoProcessDate = GetPeriod($_POST['ProcessDate'],$db);
    $PeriodNoNow = GetPeriod(date('d/m/Y'),$db);
    $InputError = false; //always hope for the best
    //echo "<br>".date('d/m/Y') ." ".$PeriodNoNow. "/". $PeriodNoProcessDate;

    $hideProcesar="";
?>

    <div class="row text-center">
        <div class="col-sm-12">
            
                <div class="form-inline form-group">
                    <?php if ($AllowUserEnteredProcessDate){ ?>
                        Procesar depreciación a esta fecha: &nbsp;
                        <component-date id="ProcessDate" name="ProcessDate" placeholder="Fecha depreciación" title="Fecha depreciación" value="<?= $_POST['ProcessDate'];?>" readonly></component-date>
                    <?php }else{ ?>
                            Fecha para procesar depreciación:&nbsp;<label><?php echo $_POST['ProcessDate']; ?></label>
                    <?php }?>

                    <?php

                        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                        //!!                                               !!
                        //!!        Esperar definicion si se usara.        !!
                        //!!                                               !!
                        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                        
                        $disabled="";
                        $hideProcesar="";
                        if (Date1GreaterThanDate2($_POST['ProcessDate'],Date($_SESSION['DefaultDateFormat']))){
                            $disabled="disabled";
                            $hideProcesar="hide";
                            
                            /*Se solicita abrir el rango de fecha para ejecutar la depreciacion 1 semana*/

                            $SQLValidacion = "SELECT  CASE 
                                                WHEN '".fnFormatoFechaYMD(Date($_SESSION['DefaultDateFormat']),'/')."' >= date_add('".fnFormatoFechaYMD($_POST['ProcessDate'],'/')."', INTERVAL -7 DAY) 
                                                THEN '1' 
                                                ELSE '0' 
                                                END as disable;";
                            
                            $rsValidacion = DB_query($SQLValidacion,$db);

                            $myrowValidacion = DB_fetch_array($rsValidacion);

                            if($myrowValidacion['disable'] == "1"){
                                $disabled="";
                                $hideProcesar="";
                            }

                        }

                        if ($PeriodNoProcessDate < $PeriodNoNow){
                            
                            $sqlRevisarPeriodo="SELECT * FROM periodsXlegal WHERE legalid='".$_POST['legalid']."' and periodno='".$PeriodNoProcessDate."' and status = 1";
                            $result=DB_query($sqlRevisarPeriodo,$db);

                            if (DB_num_rows($result)>=1) {
                                $disabled="disabled";
                                prnMsg(_('La depreciaci&oacute;n no sera procesada, la fecha de procesamiento es Menor a la actual y el Periodo se encuentra cerrado'),'warn');
                                $InputError =true;
                            }
                        }

                    ?>
                </div>
            
        </div>
        <div>
            
            <component-button type="submit" id="CommitDepreciation" name ="CommitDepreciation" class="glyphicon glyphicon-ok <?php echo $hideProcesar; ?>"  value="Generar Poliza Depreciación" <?php echo $disabled; ?>></component-button>

            <component-button type="button" id="btnPDF" name ="btnPDF" class="glyphicon glyphicon-circle-arrow-down btn btn-default botonVerde"  value="PDF" ></component-button>
            <component-button type="button" id="btnXLS" name ="btnXLS" class="glyphicon glyphicon-circle-arrow-down btn btn-default botonVerde"  value="Excel" ></component-button>
        </div>
    </div>


<?php
    /* Get list of assets for journal */
    // antes calculaba el tota:  SUM(CASE WHEN fixedassettrans.fixedassettranstype='cost' THEN fixedassettrans.amount ELSE 0 END) AS costtotal,
    $sql="SELECT fixedassets.assetid,
                fixedassets.description,
                fixedassets.barcode,
                fixedassets.serialno,
                fixedassets.eco,
                fixedassets.depntype,
                coalesce(fixedassets.depnrate,0) as depnrate ,
                fixedassets.datepurchased,
                fixedassetmatrizconversion.cargo AS accumdepnact,
                fixedassetmatrizconversion.abono AS depnact,
                tbcatespecifica.partidacalculada,
                tbcatespecifica.descripcion AS peDescripcion,
                fixedassets.cost AS costtotal,
                SUM(CASE WHEN fixedassettrans.transtype='44' THEN fixedassettrans.amount ELSE 0 END) AS depnbfwd,
                fixedassets.tagrefowner,
                fixedassetstatus.fixedassetstatus,
                fixedassets.status,
                -- fixedassets.fechaIncorporacionPatrimonial as dateiniuse,
                DATE_FORMAT(fixedassets.fechaIncorporacionPatrimonial, '%Y-%m-%d') as dateiniuse,
                timestampdiff(month,DATE_ADD( DATE_FORMAT(fixedassets.fechaIncorporacionPatrimonial, '%Y-%m-%d'), INTERVAL 1 MONTH ),curdate()) as dateiniuse2
                
            FROM fixedassets
            INNER JOIN tb_partida_articulo tpa
                ON fixedassets.cabm = tpa.eq_stockid
            INNER JOIN tb_cat_partidaspresupuestales_partidaespecifica tbcatespecifica
                ON tpa.partidaEspecifica = tbcatespecifica.partidacalculada
            LEFT JOIN fixedassetmatrizconversion
                ON fixedassets.contabilizado=fixedassetmatrizconversion.matrizid
            INNER JOIN tags 
                ON fixedassets.tagrefowner = tags.tagref
            INNER JOIN fixedassettrans
                ON fixedassets.assetid=fixedassettrans.assetid 
            LEFT JOIN fixedassetstatus 
                ON fixedassets.status = fixedassetstatus.fixedassetstatusid
            WHERE fixedassets.ownertype = 1 
                    AND fixedassets.active =  1 ";

        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        //!!   No filtrar por el momento por dependencia.  !!
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

        // if(isset($_POST['legalid']) and $_POST['legalid'] !=""){
        //     $sql .= " AND tags.legalid = ". $_POST['legalid'] ;
        // }

        if(isset($_POST['selectUnidadNegocio']) and $_POST['selectUnidadNegocio'] !=""){
            $sql .= " AND fixedassets.tagrefowner = '". $_POST['selectUnidadNegocio']."'" ;
        }

        if(isset($_POST['selectUnidadEjecutora']) and $_POST['selectUnidadEjecutora'] !=""){
            $sql .= " AND fixedassets.ue = '". $_POST['selectUnidadEjecutora']."'" ;
        }

        if (!isset($_POST['CommitDepreciation'])){
            if(isset($_POST['categoriesid'])){
                if(count($_POST['categoriesid']) > 0 and !empty($_POST['categoriesid'])) {
                    $categoriesid = array_map(function ($value) {
                            return "'" . $value . "'";
                    }, $_POST['categoriesid']);
                    $categoriesid =  implode(',', $categoriesid);
                    $sql .= " AND fixedassets.assetcategoryid IN (" . $categoriesid . ")";
                }
            }

            if(isset($_POST['periodno']) and $_POST['periodno'] !="ALL"){
                $sql .= " AND fixedassettrans.periodno <= ". $_POST['periodno'] ;
            }
        }
        

                
    $sql.=" GROUP BY fixedassets.assetid,
                fixedassets.description,
                fixedassets.depntype,
                fixedassets.depnrate,
                fixedassets.datepurchased,
                fixedassetmatrizconversion.cargo,
                fixedassetmatrizconversion.abono,
                tbcatespecifica.descripcion
            ORDER BY tbcatespecifica.partidacalculada, assetid;";

    if ($_SESSION ['UserID'] == "desarrollo") {
        //echo "<pre>".$sql."</pre>";
    }

    $AssetsResult=DB_query($sql,$db);

    if (Date1GreaterThanDate2($_POST['ProcessDate'],Date($_SESSION['DefaultDateFormat']))){
        //prnMsg(_('No depreciation will be committed as the processing date is beyond the current date. The depreciation run can only be run for periods prior to today'),'warn');
        echo "<br>";
        prnMsg(_('La depreciacion no sera procesada, la fecha de procesamiento es mayor a la actual. La depreciacion solo se ejecuta para periodos anteriores a la fecha actual'),'warn');
        $InputError =true;
    }

    if (isset($_POST['CommitDepreciation']) AND $InputError==false){
        $result = DB_Txn_Begin($db);
        $TransNo = GetNextTransNo(44, $db);
        $PeriodNo = GetPeriod($_POST['ProcessDate'],$db);
    }

    echo '<br /><div class="table-responsive"><table class ="table" cellpadding="5" colspan="7" border="1" style="border-collapse:collapse; border: 1px solid lightgray; width:95%;">';
    $Heading = '<thead class="header-verde">
                    <tr>
                        <td style="text-align:center; padding:5px;vertical-align: middle;">' . _('Número de Inventario') . '</td>
                        <td style="text-align:center; padding:5px;vertical-align: middle;">' . _('Número de Serie') . '</td>
                        <td style="text-align:center; padding:5px;vertical-align: middle;">' . _('Estatus') . '</td>
                        <td style="text-align:center; padding:5px;vertical-align: middle;">' . _('Descripci&oacute;n') . '</td>
                        <td style="text-align:center; padding:5px;vertical-align: middle;">' . _('MOI') . '</td>
                        <td style="text-align:center; padding:5px;vertical-align: middle;">' . _('Fecha de adquisici&oacute;n') . '</td>
                        <td style="text-align:center; padding:5px;vertical-align: middle;">' . _('Fecha de incorporación') . '</td>
                        <td style="text-align:center; padding:5px;vertical-align: middle;">' . _('MOI Remanente Depreciaci&oacute;n ') . '</td>
                        <td style="text-align:center; padding:5px;vertical-align: middle;">' .  _('Tasa de Depreciaci&oacute;n ') . '</td>
                        <td style="text-align:center; padding:5px;vertical-align: middle;">' . _('Depreciaci&oacute;n Mensual') . '</td>
                        <td style="text-align:center; padding:5px;vertical-align: middle;">' . _('Meses a Depreciar ') . '</td>
                        <td style="text-align:center; padding:5px;vertical-align: middle;">' . _('Meses Depreciado ') . '</td>
                        <td style="text-align:center; padding:5px;vertical-align: middle;">' . _('Depreciaci&oacute;n Acumulada') . '</td>
                        <td style="text-align:center; padding:5px;vertical-align: middle;">' . _('Meses Remanente  ') . '</td>
                        <td style="text-align:center; padding:5px;vertical-align: middle;">' . _('Mes Poliza  ') . '</td>
                        <td style="text-align:center; padding:5px;vertical-align: middle;">' . _('Poliza') . '</td>
                    </tr>
                </thead>';
    echo $Heading;

    $AssetCategoryDescription ='0';

    $TotalCost =0;
    $TotalAccumDepn=0;
    $TotalDepn = 0;
    $TotalCategoryCost = 0;
    $TotalCategoryAccumDepn =0;
    $TotalCategoryDepn = 0;
    $mesesADpreciar=0;
    $mesesDepreciados=0;
    $mesesRemanente=0;

    $RowCounter = 0;
    $k=0;


    if(isset($_POST['ProcessDate'])){
        $arrFormatProcessDate = explode('/',$_POST['ProcessDate']);
        $FormatProcessDate= $arrFormatProcessDate[2]."-".$arrFormatProcessDate[1]."-".$arrFormatProcessDate[0];
    }

    $activosDepreciacion=0;

    if(isset($_POST['CommitDepreciation']) and $InputError==false){
        $folioPolizaUE = fnObtenerFolioUeGeneral($db, $_POST['selectUnidadNegocio'],$_POST['selectUnidadEjecutora'], 291);
    }
      
    while ($AssetRow=DB_fetch_array($AssetsResult)) {

        if ($AssetCategoryDescription != $AssetRow['peDescripcion'] OR $AssetCategoryDescription =='0'){
            if ($AssetCategoryDescription !='0'){ //then print totals
                echo '<tr><th colspan="4" align="right" style="text-align:left; font-weight:700;">' . _('Total para ') . ' ' . $AssetCategoryDescription . ' </th>

                            <th class="number" style="text-align:right; font-weight:700;">' . number_format($TotalCategoryCost,$_SESSION['DecimalPlaces']) . '</th>
                            <th></th>
                            <th></th>
                            <th class="number" style="text-align:right; font-weight:700;">' . number_format($TotalCategoryAccumDepn,$_SESSION['DecimalPlaces']) . '</th>


                            <th class="number" style="text-align:right; font-weight:700;">' . number_format(($TotalCategoryCost-$TotalCategoryAccumDepn),$_SESSION['DecimalPlaces']) . '</th>
                            <th></th>
                            
                            <th class="number" style="text-align:right; font-weight:700;">' . number_format($TotalCategoryDepn,$_SESSION['DecimalPlaces']) . '</th>
                            <th colspan="5"></th>
                        </tr>';
                $RowCounter = 0;
            }
            echo '<tr>
                    <th colspan="16" style="text-align:center; padding:2px; font-size:14px; font-weight:700;">' . $AssetRow['peDescripcion']  . '</th>
                </tr>';
            $AssetCategoryDescription = $AssetRow['peDescripcion'];
            $TotalCategoryCost = 0;
            $TotalCategoryAccumDepn =0;
            $TotalCategoryDepn = 0;
        }
        $BookValueBfwd = $AssetRow['costtotal'] - $AssetRow['depnbfwd'];
        if ($AssetRow['depntype']==0){ //striaght line depreciation
            $DepreciationType = _('SL');
            $NewDepreciation = $AssetRow['costtotal'] * $AssetRow['depnrate']/100/12;
            if ($NewDepreciation > $BookValueBfwd){
                $NewDepreciation = $BookValueBfwd;
            }
        } else { //Diminishing value depreciation
            $DepreciationType = _('DV');
            $NewDepreciation = $BookValueBfwd * $AssetRow['depnrate']/100/12;
        }
        if (Date1GreaterThanDate2($AssetRow['datepurchased'],$_POST['ProcessDate'])){
            /*Over-ride calculations as the asset was not purchased at the date of the calculation!! */
            $NewDepreciation =0;
        }
        

        $arrfecha = explode('/',$_POST['ProcessDate']);

        $inicio=$AssetRow['dateiniuse']." 00:00:00";
        $fin= $arrfecha[2]."-".$arrfecha[1]."-".$arrfecha[0] ." 23:59:59";
         
        $datetime1=new DateTime($inicio);
        $datetime2=new DateTime($fin);
         
        # obtenemos la diferencia entre las dos fechas
        $interval=$datetime2->diff($datetime1);
         
        # obtenemos la diferencia en meses
        $intervalMeses=$interval->format("%m");
        
        # obtenemos la diferencia en años y la multiplicamos por 12 para tener los meses
        $intervalAnos = $interval->format("%y")*12;
        

        //$mesesDepreciados=($intervalMeses+$intervalAnos);
        
        $mesesDepreciados = $AssetRow['dateiniuse2'];

        $mesesADpreciar = number_format(( 12 / ($AssetRow['depnrate']/100)),$_SESSION['DecimalPlaces']) ;
        if($mesesDepreciados>$mesesADpreciar){
            $mesesDepreciados = $mesesADpreciar;
        }

        $mesesRemanente= $mesesADpreciar - $mesesDepreciados;


        if($mesesRemanente<=0){
            $mesesRemanente=0;
        }
         
        
        $RowCounter++;
        if ($RowCounter ==15){
            echo $Heading;
            $RowCounter =0;
        }
        if ($k==1){
            echo '<tr class="">';
            $k=0;
        } else {
            echo '<tr class="OddTableRows">';
            $k++;
        }

        if(isset($_POST['periodno']) and $_POST['periodno'] !="ALL" and !isset($_POST['CommitDepreciation'])){
            $sqlPoliza="SELECT * , DATE_FORMAT(transdate,'%M/%Y') AS finished FROM fixedassettrans WHERE transtype=44 AND assetid ='".$AssetRow['assetid']."' AND periodno='".$_POST['periodno']."';";
        }else{
            $sqlPoliza="SELECT transtype,transno,periodno,transdate,DATE_FORMAT(transdate,'%M/%Y') AS finished  FROM fixedassettrans WHERE id in (select max(id) from fixedassettrans where  transtype=44 AND assetid ='".$AssetRow['assetid']."');";
        }


        $PolizaResult=DB_query($sqlPoliza,$db);
        $reimprimir_poliza= "";
        $mesFinished = "";


        if(DB_num_rows($PolizaResult ) >=1){

            $myRowPoliza=DB_fetch_array($PolizaResult);

            $mesFinished = $myRowPoliza['finished'];
            $liga = "PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=44&TransNo=".$myRowPoliza['transno']."&periodo=".$myRowPoliza['perriodno']."&trandate=".$myRowPoliza['transdate']."";

            $reimprimir_poliza = "No. Poliza: ".$myRowPoliza['transno']."<br> <a TARGET='_blank' href='" . $liga . "'><img src='" . $rootpath . "/css/" . $theme . "/images/printer.png' title='" . _ ( 'Imprimir Poliza' ) . "' alt=''></a>";
        }

        $datepurchased = new DateTime($AssetRow['datepurchased']);
        $dateiniuse = new DateTime($AssetRow['dateiniuse']);

        echo '<td align="center">' . $AssetRow['barcode'] . '</td>
            <td align="center">' . $AssetRow['serialno'] . '</td>
            <td align="center">' . $AssetRow['fixedassetstatus'] . '</td>
            <td style="text-align:left;">' . $AssetRow['description'] . '</td>
            <td style="text-align:right;">' .number_format($AssetRow['costtotal'], $_SESSION['DecimalPlaces'], '.', '') . '</td>
            <td align="center" >' . $datepurchased->format('d/m/Y') . '</td>
            <td align="center" >' . $dateiniuse->format('d/m/Y') . '</td>
            <td style="text-align:right;">' . number_format($AssetRow['costtotal']-$AssetRow['depnbfwd'],$_SESSION['DecimalPlaces']) . '</td>
            <td style="text-align:right;">' . number_format($AssetRow['depnrate'] ,$_SESSION['DecimalPlaces']) . '</td>
            <td style="text-align:right;">' . number_format($NewDepreciation ,$_SESSION['DecimalPlaces']) . '</td>
            <td style="text-align:right;">' . $mesesADpreciar . '</td>
            <td style="text-align:right;">' . $mesesDepreciados . '</td>
            <td style="text-align:right;">' . number_format($AssetRow['depnbfwd'],$_SESSION['DecimalPlaces']) . '</td>
            <td style="text-align:right;">' . number_format($mesesRemanente,$_SESSION['DecimalPlaces']) . '</td>
            <td align="center">'.$mesFinished.'</td>
            <td align="center" nowrap>'.$reimprimir_poliza.'</td>
        </tr>';
        $TotalCategoryCost +=$AssetRow['costtotal'];
        $TotalCategoryAccumDepn +=$AssetRow['depnbfwd'];
        $TotalCategoryDepn +=$NewDepreciation;
        $TotalCost +=$AssetRow['costtotal'];
        $TotalAccumDepn +=$AssetRow['depnbfwd'];
        $TotalDepn +=$NewDepreciation;

        //VALIDAR CUANDO EL ACTIVO ENTRE EN EL MES CORRIENTE PERO SE TIENE QUE AGREGAR EN LA POLIZA DEL MES QUE VIENE NO EN LA DE ESE MES QUE ENTRO
        $arrfecha = explode('-',$AssetRow['dateiniuse']);
        $PeriodNoFechaIniUso = GetPeriod($arrfecha[2]."/".$arrfecha[1]."/".$arrfecha[0],$db);
        
        //echo "<br>". $PeriodNoFechaIniUso."/ ". $PeriodNoProcessDate;
        
        //echo "<br>". $_POST['CommitDepreciation'] .",".$NewDepreciation.",".$InputError;
        if (isset($_POST['CommitDepreciation']) AND $NewDepreciation !=0 AND $InputError==false ){
            //echo "<br>". $mesesADpreciar .",".$mesesDepreciados.",".$PeriodNoFechaIniUso.",".$PeriodNoProcessDate;
            if( $mesesADpreciar >= $mesesDepreciados and $PeriodNoFechaIniUso<= $PeriodNoProcessDate){

                $narrativa= "Depreciación mensual del activo con Número de inventario: ".$AssetRow['barcode'].", Numero de serie: ".$AssetRow['serialno'].", Descripción: ".$AssetRow['description']." con fecha de depreciación: ".$_POST['ProcessDate'];

                //debit depreciation expense
                
                

                $SQL = "INSERT INTO gltrans (type,
                                            typeno,
                                            trandate,
                                            periodno,
                                            account,
                                            narrative,
                                            amount, 
                                            tag,
                                            stockid,
                                            ln_ue,
                                            nu_folio_ue)
                                VALUES (44,
                                        '" . $TransNo . "',
                                        curdate(),
                                        '" . $PeriodNo . "',
                                        '" . $AssetRow['depnact'] . "',
                                        '" . $narrativa . "',
                                        '" . $NewDepreciation ."', 
                                        '" . $AssetRow["tagrefowner"]. "',
                                        '" . $AssetRow["assetid"]. "',
                                        '" . $_POST["selectUnidadEjecutora"]. "',
                                        '".$folioPolizaUE."'
                                    )";

                $ErrMsg = _('Cannot insert a depreciation GL entry for the depreciation because');
                $DbgMsg = _('The SQL that failed to insert the GL Trans record was');
                //echo "<pre>sql1:".$SQL."</pre>";
                $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

                $SQL = "INSERT INTO gltrans (type,
                                            typeno,
                                            trandate,
                                            periodno,
                                            account,
                                            narrative,
                                            amount,
                                            tag,
                                            stockid,
                                            ln_ue,
                                            nu_folio_ue)
                                VALUES (44,
                                        '" . $TransNo . "',
                                        curdate(),
                                        '" . $PeriodNo . "',
                                        '" . $AssetRow['accumdepnact'] . "',
                                        '" . $narrativa . "',
                                        '" . -$NewDepreciation ."', 
                                        '" . $AssetRow["tagrefowner"]. "',
                                        '" . $AssetRow["assetid"]. "',
                                        '" . $_POST["selectUnidadEjecutora"]. "',
                                        '".$folioPolizaUE."'
                                    )";
                //echo "<pre>sql2:".$SQL."</pre>";
                                      
                $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

                //insert the fixedassettrans record
                $SQL = "INSERT INTO fixedassettrans (assetid,
                                                    transtype,
                                                    transno,
                                                    transdate,
                                                    periodno,
                                                    inputdate,
                                                    fixedassettranstype,
                                                    amount,
                                                    fixedassetstatusid)
                                    VALUES ('" . $AssetRow['assetid'] . "',
                                                    '44',
                                                    '" . $TransNo . "',
                                                    '".$FormatProcessDate."',
                                                    '" . $PeriodNo . "',
                                                    '" . Date('Y-m-d') . "',
                                                    'depn',
                                                    '" . $NewDepreciation . "',".$AssetRow['status'].")";

                                                    //echo "<pre>".$SQL;
                //echo "<pre>sql3:".$SQL."</pre>";

                $ErrMsg = _('Cannot insert a fixed asset transaction entry for the depreciation because');
                $DbgMsg = _('The SQL that failed to insert the fixed asset transaction record was');
                $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

                /*now update the accum depn in fixedassets */
                $SQL = "UPDATE fixedassets SET accumdepn = accumdepn + " . $NewDepreciation  . "
                        WHERE assetid = '" . $AssetRow['assetid'] . "'";
                $ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE. The fixed asset accumulated depreciation could not be updated:');
                $DbgMsg = _('The following SQL was used to attempt the update the accumulated depreciation of the asset was:');
                $result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

                $activosDepreciacion++;

            }
        } //end if Committing the depreciation to DB
    } //end loop around the assets to calculate depreciation for
    echo '<tr><th colspan="4" align="right" style="text-align:left; font-weight:700;">' . _('Total para ') . ' ' . $AssetCategoryDescription . ' </th>

            <th class="number" style="text-align:right; font-weight:700;">' . number_format($TotalCategoryCost,$_SESSION['DecimalPlaces']) . '</th>
            <th></th>
            <th></th>
            <th class="number" style="text-align:right; font-weight:700;">' . number_format($TotalCategoryAccumDepn,$_SESSION['DecimalPlaces']) . '</th>


            <th class="number" style="text-align:right; font-weight:700;">' . number_format(($TotalCategoryCost-$TotalCategoryAccumDepn),$_SESSION['DecimalPlaces']) . '</th>
            <th></th>             
            <th class="number" style="text-align:right; font-weight:700;">' . number_format($TotalCategoryDepn,$_SESSION['DecimalPlaces']) . '</th>
            <th colspan="5"></th>
        </tr>';
    echo '<tr>
            <th colspan="4" align="right" style="text-align:left; font-weight:700;">' . _('TOTAL GENERAL') . ' </th>
            <th class="number" style="text-align:right; font-weight:700;">' . number_format($TotalCost,$_SESSION['DecimalPlaces']) . '</th>
            <th></th>
            <th></th>
            <th class="number" style="text-align:right; font-weight:700;">' . number_format($TotalAccumDepn,$_SESSION['DecimalPlaces']) . '</th>
            <th class="number" style="text-align:right; font-weight:700;">' . number_format(($TotalCost-$TotalAccumDepn),$_SESSION['DecimalPlaces']) . '</th>
            <th ></th>
            <th class="number" style="text-align:right; font-weight:700;">' . number_format($TotalDepn,$_SESSION['DecimalPlaces']) . '</th>
            <th colspan="5"></th>
        </tr>';


    echo '</table>
            </div>
            <hr />
            <br />';

    if(!$result){
        DB_Txn_Rollback($db);
    }

    if (isset($_POST['CommitDepreciation']) AND $InputError==false){
        $result = DB_Txn_Commit($db);
        //prnMsg(_('La Poliza de depreciacion') . ' ' . $TransNo . ' ' . _('ha sido creada'),'success');
        unset($_POST['ProcessDate']);
        echo '<input type="hidden" id="txtFolioGenerado" name="txtFolioGenerado" value="'.$folioPolizaUE.'" readonly>';
        echo '<input type="hidden" id="txtActivosGenerado" name="txtActivosGenerado" value="'.$activosDepreciacion.'" readonly>';
        //echo '<br /><a href="index.php">' ._('Regresar al Menu Principal') . '</a>';
        /*And post the journal too */
        include ('includes/GLPostingsV4.inc');
    } else {
        /*echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" id="form">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<br />
            <table class="selection" width="50%">
            <tr>';
        if ($AllowUserEnteredProcessDate){
            echo '<td>' . _('Procesar Depreciacion a esta fecha'). '&nbsp;:</td>
                <td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat']. '" required="required" name="ProcessDate" maxlength="10" size="11" value="' . $_POST['ProcessDate'] . '" /></td>';
        } else {
            echo '<td>' . _('Fecha para Procesar Depreciacion'). ':</td>
                <td>' . $_POST['ProcessDate']  . '</td>';
        }
        echo '<td><div class="centre"><input type="submit" name="CommitDepreciation" value="'._('Comprometer Depreciacion').'" /></div></td>
            </tr>
            </table>
            <br />
            </div>
            </form>';*/
    }
}

?>

</form>
<?
if (!isset($_POST['CommitDepreciation'])  ){
 echo '<div style="margin-bottom:32%;">'.$_POST['CommitDepreciation'].'</div>';
}
?>

<script type="text/javascript" src="javascripts/FixedAssetDepreciation.js"></script>

<?php require 'includes/footer_Index.inc'; 

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
?>


