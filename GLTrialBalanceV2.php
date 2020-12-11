<?php
/**
 * Balanza de Comprobación
 *
 * @category
 * @package ap_grp
 * @author Desarrollo <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/02/2018
 * Fecha Modificación: 01/02/2018
 * Vista para el reporte de la Balanza de Comprobación
 */

//Subir a Capa

$PageSecurity = 8;
$funcion=110;
include('includes/session.inc');
$title = _('Balanza de Comprobación');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');
//include('includes/AccountSectionsDef.inc'); //this reads in the Accounts Sections array
setlocale(LC_ALL, 'es_ES');

# incliciones extra
include('./Numbers/Words.php');

$D = "";

if (isset($_POST['FromPeriod']) and isset($_POST['ToPeriod']) and $_POST['FromPeriod'] > $_POST['ToPeriod']) {

    if($_POST['cmbTipoBalanza']=='1'){

    }else{
        prnMsg(_('El período seleccionado hasta es posterior al período desde! Favor de re-seleccionar los periodós del reporte'), 'error');
        $_POST['SelectADifferentPeriod']=_('Seleccione un período diferente');
    }

}
# En caso de que se envie la impresión no se cargan los headers
/************************************** 
INCLUCION DE PANEL DE BUSQUEDA
**************************************/
if (!isset($_POST['PrintEXCEL'])) {
    include('includes/header.inc');
    echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">'; ?>
<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
<!--Panel Busqueda-->

<?php
$arrPostUE="";

if(isset($_POST['selectUnidadEjecutora']) and $_POST['selectUnidadEjecutora'] !=""){
    if(count($_POST['selectUnidadEjecutora']) > 0 and !empty($_POST['selectUnidadEjecutora'])) {
        $ue = array_map(function ($value) {
                return "" . $value . "";
        }, $_POST['selectUnidadEjecutora']);
        $arrPostUE =  implode(',', $ue); 
    }
} 

$styleTipoBalanza="";

/* Permiso para habilitar el boton de exportar a excel */
$permisoExcel = Havepermission($_SESSION['UserID'],2391, $db);
$permisoExcel=1; // No tomar en cuenta el permiso para excel

if(isset($_POST['txtFiltroCuentas']) and $_POST['txtFiltroCuentas'] !=""){
    echo "string:".$_POST['txtFiltroCuentas'];
}
$jsTipoPoliza=0;
if(isset($_POST['cmbTipoBalanza']) and $_POST['cmbTipoBalanza']!=""){
    $jsTipoPoliza=$_POST['cmbTipoBalanza'];
}
?>

<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingTwo">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBalanza" aria-expanded="true" aria-controls="collapseTwo">
            <b>Criterios de filtrado</b>
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelBalanza" name="PanelBalanza" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingTwo">
        <div class="panel-body row">
            <div class="col-md-4">
                <div class="row mb10 hide">
                    <input type='hidden' name='txtUE' id='txtUE' value='<?php echo $arrPostUE; ?>' class='form-control input-md' />
                    <div class="col-md-3">
                        <span><label>Dependencia: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="selectRazonSocial" name="legalid" class="form-control selectRazonSocial" onchange="fnTraeUnidadesResponsables(this.value, 'selectUnidadNegocio')" data-todos="true">
                        </select>
                    </div>
                </div>
                <div class="row mb10">
                    <div class="col-md-3 pt10">
                        <span><label>UR: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')" data-todos="true">
                            
                        </select>
                    </div>
                </div>
                <div class="row mb10">
                    <div class="col-md-3 pt10">
                        <span><label>UE: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="selectUnidadEjecutora" name="selectUnidadEjecutora[]" multiple="multiple" class="form-control selectUnidadEjecutora" data-todos="true">
                        </select>
                    </div>
                </div>
                <br>
                <div class="row mb10">
                    <div class="col-md-3"><span class="generalSpan">Tipo de Balanza:</span></div>
                    <div class="col-md-9">
                        <select id="cmbTipoBalanza" class="form-control  cmbTipoBalanza" name="cmbTipoBalanza">
                            <option  value="0" <?php echo ($_POST['cmbTipoBalanza']=='0' ) ? 'selected ' : ''; ?>>6 Columnas</option>
                            <option  value="1" <?php echo ($_POST['cmbTipoBalanza']=='1' ) ? 'selected ' : ''; ?>>8 Columnas</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="row mb10 hide">
                    <div class="col-md-3"><span class="generalSpan">Tipo de Cuenta:</span></div>
                    <div class="col-md-9">
                        <select id="idAccounttype" class="form-control mb10 idAccounttype" name="accounttype">
                            <option  value="0">Nivel Auxiliar</option>
                            <option  value="1">Nivel Secciones del Catalogo</option>
                            <option  value="2">Nivel de Mayor</option>
                        </select>
                    </div>
                </div>
                <div class="row mb10">
                    <div class="col-md-3"><span class="generalSpan">Tipo de Movimiento:</span></div>
                    <div class="col-md-9">
                        <select id="idNoZeroes" class="form-control mb10 idNoZeroes" name="noZeroes">
                            <option  value="0" <?php echo ($_POST['noZeroes']=='0' ) ? 'selected ' : ''; ?>>Todas las cuentas</option>
                            <option  value="4" <?php echo ($_POST['noZeroes']=='4' ) ? 'selected ' : ''; ?>>Saldo o Movimiento dif a cero</option>
                            <!-- <option  value="1">Solo cuentas con movimientos</option>
                            <option  value="2">Saldo actual NO Cero</option>
                            <option  value="3">Saldo y Movimiento dif a cero</option> -->
                        </select></div>
                </div>
                <div class="row mb10">
                    <div class="col-md-3 text-left"><span class="generalSpan">Nivel de Desagregación:</span></div>
                    <div class="col-md-9">
                        <select id="NivelDesagregacion" class="form-control mb10 NivelDesagregacion" name="NivelDesagregacion">
                            <?php
                                # se agrega nivel de dessagregacion dinamico
                                echo obtenNivelDesagregacion($_POST['NivelDesagregacion']); ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                
                <div id="divPeriodoDesde" class="row mb10 <?php echo ($_POST['cmbTipoBalanza']=='1' ) ? 'hide ' : ''; ?>">
                    <?php
                    if (Date('m') > $_SESSION['YearEnd']) {
                        //Dates in SQL format
                        $DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')));
                        $FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')));
                    } else {
                        $DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')-1));
                        $FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')-1));
                    }
                    echo '<div class="col-md-3 pt10"><span class="generalSpan">' . _('Desde:') . '</span></div>';
                    echo '<div class="col-md-9"><SELECT id="idFromPeriod" class="form-control FromPeriod" name="FromPeriod">';
                    $nextYear = date("Y-m-d", strtotime("+1 Year"));
                    $presentYear = date("Y-m-d");

                    //$sql = "SELECT periodno, lastdate_in_period FROM periods where lastdate_in_period < '$nextYear' ORDER BY periodno DESC";
                    $sql = "SELECT periodno, lastdate_in_period, 
                                    case when periodno like '%.5' then ' Cierre Anual ' else '' end as cierre
                            FROM periods 
                            WHERE date_format(lastdate_in_period,'%Y') >= (DATE_FORMAT(curdate(),'%Y')-4)  and date_format(lastdate_in_period,'%Y') <= DATE_FORMAT(curdate(),'%Y')  
                            ORDER BY periodno DESC;";
                    //echo "<pre>sql:".$sql."</pre>";
                    $Periods = DB_query($sql, $db);


                    while ($myrow=DB_fetch_array($Periods, $db)) {
                        if (isset($_POST['FromPeriod']) and $_POST['FromPeriod']!='') {
                            if ($_POST['FromPeriod']== $myrow['periodno']) {
                                echo '<option SELECTED VALUE="' . $myrow['periodno'] . '">' .MonthAndYearFromSQLDate($myrow['lastdate_in_period']) .$myrow['cierre'] .'</option>';
                            } else {
                                echo '<option VALUE="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) .$myrow['cierre'] .'</option>';
                            }
                        } else {
                            if ($myrow['lastdate_in_period']==$DefaultFromDate) {
                                echo '<option SELECTED VALUE="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) .$myrow['cierre'].'</option>';
                            } else {
                                echo '<option VALUE="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) .$myrow['cierre'] .'</option>';
                            }
                        }
                    }
                        echo '</SELECT></div>';
                    ?>
                </div>
                <div class="row mb10">
                    <?php
                    if (!isset($_POST['ToPeriod']) or $_POST['ToPeriod']=='') {
                        $lastDate = date("Y-m-d", mktime(0, 0, 0, Date('m')+1, 0, Date('Y')));
                        $sql = "SELECT periodno FROM periods where lastdate_in_period = '$lastDate'";
                        $MaxPrd = DB_query($sql, $db);
                        $MaxPrdrow = DB_fetch_row($MaxPrd);
                        $DefaultToPeriod = (int) ($MaxPrdrow[0]);
                    } else {
                        $DefaultToPeriod = $_POST['ToPeriod'];
                    }
                    echo '<div class="col-md-3 pt10"><span class="generalSpan">' . _('Hasta:') .'</span></div>';
                    echo '<div class="col-md-9"><SELECT id="idToPeriod" class="form-control ToPeriod" Name="ToPeriod">';

                    $RetResult = DB_data_seek($Periods, 0);

                    while ($myrow=DB_fetch_array($Periods, $db)) {
                        if ($myrow['periodno']==$DefaultToPeriod) {
                            echo '<option SELECTED VALUE="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) .$myrow['cierre'].'</option>';
                        } else {
                            echo '<option VALUE ="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) .$myrow['cierre'].'</option>';
                        }
                    }
                    echo '</SELECT></div>'; ?>
                </div>

            </div>

        </div>
    </div>
</div>


<!--Panel Busqueda-->
<?php
}// fin panel de busqueda
/************************************** INCLUCION DE PANEL DE BUSQUEDA   **************************************/
        # calculo de los periodos

// Validaciones botones
$mostrarFormularioBotones = 0;
$tieneRegistros = 0;

if ((! isset($_POST['FromPeriod']) and ! isset($_POST['ToPeriod'])) or isset($_POST['SelectADifferentPeriod'])) {
    if (!isset($_POST['PrintEXCEL'])) {
        $mostrarFormularioBotones = 1;
        // echo '<div id="btnBalanzaComprobacion" class="row text-center" >';
        //     //echo '<div class="col-md-3"></div>';
        //     echo '<div class="col-md-12">
        //             <center>
        //                 <input class="glyphicon glyphicon-search btn btn-default botonVerde" type="submit" Name="ShowTB" Value="' . _('Muestra Balanza') .'">';
                    
        //     if($permisoExcel == 1){
        //         echo '<input class="glyphicon glyphicon-search btn btn-default botonVerde" type="button" Name="PrintEXCEL" Value="'. _('Exportar a Excel') .'" onclick="fnExportarTablaExcel(\'tblCuentas\',\'Balanza_de_Comprobacion\');">';
        //     }

        //     echo '  <br><br></center>
        //         </div>';
        //     //echo '<div class="col-md-3"></div>';
        // echo '</div>';
    }
} # cuanta con las variables de los periodos y se comprueba si cuenta con excel
else {
    # comprobacion de excel
    if (isset($_POST['PrintEXCEL'])) {
        header("Content-type: application/ms-excel");
        # replace excelfile.xls with whatever you want the filename to default to
        header("Content-Disposition: attachment; filename=Balanza_de_Comprobacion.xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

        echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
        echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
        echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
    } # comprobación no se necesita la impresión del excel
    else {
        echo '<INPUT TYPE=HIDDEN NAME="FromPeriod2" VALUE="' . $_POST['FromPeriod'] . '">
                <INPUT TYPE=HIDDEN NAME="ToPeriod2" VALUE="' . $_POST['ToPeriod'] . '">';

        echo "<INPUT type=hidden name=legalid value='".$_POST['legalid']."'>";
        echo "<INPUT type=hidden name=unidadnegocio value='".$_POST['unidadnegocio']."'>";
        echo "";
        
        $mostrarFormularioBotones = 1;

        # botones de accion se imprimen siempre y cuando no se a impresión
        // echo '<div id="btnBalanzaComprobacion" class="row">';
        //     //echo '<div class="col-md-3"></div>';
        //     echo '<div class="col-md-12">
        //             <center>
        //                 <input class="glyphicon glyphicon-search btn btn-default botonVerde" type="submit" Name="ShowTB" Value="' . _('Muestra Balanza') .'">';

        //     if($permisoExcel == 1){
        //         echo '<input class=" glyphicon glyphicon-search btn btn-default botonVerde" type="button" Name="PrintEXCEL" Value="' . _('Exportar a Excel') .'" onclick="fnExportarTablaExcel(\'tblCuentas\',\'Balanza_de_Comprobacion\');">';
        //     }

        //     echo    '<br><br></center>
        //         </div>';
        // //echo '<div class="col-md-3"></div>';
        // echo '</div>';
    }

    $NumberOfMonths = $_POST['ToPeriod'] - $_POST['FromPeriod'] + 1;

    $sql = 'SELECT lastdate_in_period FROM periods WHERE periodno=' . $_POST['ToPeriod'];
    $PrdResult = DB_query($sql, $db);
    $myrow = DB_fetch_row($PrdResult);
    $PeriodToDate = MonthAndYearFromSQLDate($myrow[0]);

    $sql = 'SELECT lastdate_in_period FROM periods WHERE periodno=' . $_POST['FromPeriod'];
    $PrdResult = DB_query($sql, $db);
    $myrow = DB_fetch_row($PrdResult);
    $PeriodFromDate = MonthAndYearFromSQLDate($myrow[0]);

    $RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];
    // niveles de filtro de informacion
    $tagref="";
    if(isset($_POST['selectUnidadNegocio']) and $_POST['selectUnidadNegocio']!=""  and $_POST['selectUnidadNegocio']!="-1"){
        $tagref= $_POST['selectUnidadNegocio'];
    }
    $unidadejecutora= $_POST['selectUnidadEjecutora'];

    $xArea=$_POST['xArea'];
    $noZeroes=$_POST['noZeroes'];
    $accounttype=$_POST['accounttype'];
    $arrlegalid = explode("_", $_POST['legalid']);
    $legalid=$arrlegalid[0];
    $SQLGroupby='';
    $nivelDes=$_POST['NivelDesagregacion'];

    $SQL = 'SELECT
            SUBSTRING_INDEX(accountcode, ".", '.$nivelDes.') as groupCode,
            -- sectionname, 
            groupname, parentgroupname, pandl, accountcode, accountname, tipo, sequenceintb, naturaleza,
            sum(prdActual) AS prdActual,
            sum(prdCargos) AS prdCargos,
            sum(prdAbonos) AS prdAbonos,
            sum(prdInicial) AS prdInicial,
            sum(firstprdbudgetbfwd) AS firstprdbudgetbfwd,
            sum(prdFinal) AS prdFinal,
            sum(prdFinal) AS monthactual,
            sum(prdFinal) AS monthbudget,
            sum(prdFinal) AS lastprdbudgetcfwd
            FROM (';

    $SQL = $SQL. 'SELECT
            -- accountsection.sectionname,
            accountgroups.groupname,
            accountgroups.parentgroupname,
            accountgroups.pandl,
            chartdetails.accountcode ,
            chartmaster.accountname,
            chartmaster.tipo,
            accountgroups.sequenceintb,
            chartmaster.naturaleza,';
    $SQL = $SQL.'
            Sum(CASE WHEN chartdetails.period=' . $_POST['FromPeriod'] . ' THEN chartdetails.bfwd ELSE 0 END) AS prdInicial,
            sum(chartdetails.actual) AS prdActual,
            sum(chartdetails.cargos) AS prdCargos,
            sum(chartdetails.abonos) AS prdAbonos,
            Sum(CASE WHEN chartdetails.period=' . $_POST['FromPeriod'] . ' THEN chartdetails.bfwdbudget ELSE 0 END) AS firstprdbudgetbfwd,
            Sum(CASE WHEN chartdetails.period=' . $_POST['ToPeriod'] . ' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS prdFinal,
            Sum(CASE WHEN chartdetails.period=' . $_POST['ToPeriod'] . ' THEN chartdetails.actual ELSE 0 END) AS monthactual,
            Sum(CASE WHEN chartdetails.period=' . $_POST['ToPeriod'] . ' THEN chartdetails.budget ELSE 0 END) AS monthbudget,
            Sum(CASE WHEN chartdetails.period=' . $_POST['ToPeriod'] . ' THEN chartdetails.bfwdbudget + chartdetails.budget ELSE 0 END) AS lastprdbudgetcfwd
        FROM chartdetails
        INNER JOIN
            (SELECT tags.legalid,tags.tagref
            -- , tb_cat_unidades_ejecutoras.ue
            FROM tags
                INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '"
                -- INNER JOIN tb_cat_unidades_ejecutoras ON tags.tagref=tb_cat_unidades_ejecutoras.ur
            ) as tags ON tags.tagref=chartdetails.tagref
            INNER JOIN chartmaster ON chartmaster.accountcode= chartdetails.accountcode
            LEFT JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
            -- LEFT JOIN accountsection ON accountgroups.sectioninaccounts = accountsection.sectionid
        WHERE chartdetails.period>=' .      $_POST['FromPeriod'] . ' 
        and chartdetails.period<=' . $_POST['ToPeriod'] . '
            AND chartmaster.group_<>"" ';

    if (!empty($tagref)) {
        $SQL = $SQL . " AND tags.tagref ='".$tagref."'";
        $SQLGroupby=$SQLGroupby.', tags.tagref ';
    }

    if ($xDepartamento<>0) {
        $SQL = $SQL . ' AND tags.u_department = '.$xDepartamento;
        $SQLGroupby=$SQLGroupby.', tags.u_department ';
    }

    if ($xRegion<>0) {
        $SQL = $SQL . ' AND tags.regioncode ='."'".$xRegion."'";
        $SQLGroupby=$SQLGroupby.', areas.regioncode ';
    }

    // if (!empty($unidadejecutora)) {
    //     // $SQL = $SQL . " AND tags.ue = '".$unidadejecutora."'";
    //     $ues = '';
    //     foreach ($unidadejecutora as $key => $value) {
    //         if ($key != 0) {
    //             $ues .= ',';
    //         }
    //         $ues .= " '$value' ";
    //     }
    //     $SQL = $SQL . " AND tags.ue IN ( ".$ues." )";
    // }

    if ($nivelDes<>0) {
        $SQL = $SQL . ' AND chartmaster.nu_nivel >= '.$nivelDes.' ';
        // $SQL = $SQL . ' AND LENGTH(chartmaster.accountcode)>='.($nivelDes + ($nivelDes-1));
        // if ($nivelDes == 4) {
        //     $SQL = $SQL . ' AND LENGTH(chartmaster.accountcode)>=7';
        // } elseif ($nivelDes == 5) {
        //     $SQL = $SQL . ' AND LENGTH(chartmaster.accountcode)>=9';
        // } elseif ($nivelDes == 6) {
        //     $SQL = $SQL . ' AND LENGTH(chartmaster.accountcode)>=11';
        // } elseif ($nivelDes == 7) {
        //     $SQL = $SQL . ' AND LENGTH(chartmaster.accountcode)>=13';
        // }

        //$SQLGroupby=$SQLGroupby.', chartdetails.accountcode ';
    }

    $SQL = $SQL . '
        GROUP BY 
            -- accountsection.sectionname,
            accountgroups.groupname,
            accountgroups.parentgroupname,
            accountgroups.pandl,
            chartdetails.accountcode ,
            chartmaster.accountname,
            chartmaster.tipo,
            accountgroups.sequenceintb,
            chartmaster.naturaleza';


    if ($noZeroes==1) {
        $SQL = $SQL . 'HAVING
                     (abs(prdCargos) > 0.1 OR abs(prdAbonos) > 0.1)';
    } elseif ($noZeroes==2) {
        $SQL = $SQL . ' HAVING (abs(prdFinal) > 0.1) ';
    } elseif ($noZeroes==3) {
        $SQL = $SQL . ' HAVING abs(prdFinal) > 0.1 and (abs(prdCargos) > 0.1 OR abs(prdAbonos) > 0.1) ';
    } elseif ($noZeroes==4) {
        $SQL = $SQL . ' HAVING abs(prdFinal) > 0.1 or (abs(prdCargos) > 0.1 OR abs(prdAbonos) > 0.1) ';
    }

    $SQL = $SQL.  " ORDER BY
                        chartdetails.accountcode,
                        accountgroups.sequenceintb";
    $SQL = $SQL .') as temp GROUP BY groupCode, 
    -- sectionname, 
    groupname, parentgroupname, pandl, accountcode ASC, accountname, tipo, sequenceintb, naturaleza';



    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!                                               !!
    //!!        Se cambia SQL por definicion.          !!
    //!!                                               !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

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
    $ues = '';
    if (!empty($unidadejecutora)) {
        // $SQL = $SQL . " AND tags.ue = '".$unidadejecutora."'";
        foreach ($unidadejecutora as $key => $value) {
            if ($key != 0) {
                $ues .= ',';
            }
            $ues .= " '$value' ";
        }
        $SQLUE= " AND gltrans.ln_ue IN ( ".$ues." )";
    }
    $filtroUEs = ( $ues ? "AND ( `chartmaster`.`ln_clave` IN ($ues) OR LENGTH(`chartmaster`.`ln_clave`) < 2 OR `chartmaster`.`ln_clave` LIKE '%.%' )" : "" );

    if($_POST['cmbTipoBalanza'] == 1){
        $_POST['FromPeriod'] = ($_POST['ToPeriod'] - 1);
    }

    $SQL="";
    $SQL="SELECT DISTINCT SUBSTRING_INDEX(accountcode,'.', ".$nivelDes.") as groupCode,
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
                    coalesce(dtMovimientos.prdAbonos,0) as prdAbonos,

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
                    WHERE gltrans.periodno >= ".$_POST['FromPeriod']." 
                            AND gltrans.periodno <= ".$_POST['ToPeriod']." 
                            AND gltrans.account != ''
                            AND gltrans.posted = 1
                            AND gltrans.periodno not like '%.5'
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
                    WHERE periodno <= ".($_POST['FromPeriod'] - 1)." 
                        AND YEAR(gltrans.trandate) =  (select YEAR(lastdate_in_period) from periods where periodno = ".$_POST['FromPeriod'].")
                        AND gltrans.account != ''
                        AND gltrans.posted = 1
                        AND gltrans.periodno not like '%.5'
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
                    INNER JOIN sec_unegsxuser ON gltrans.tag = sec_unegsxuser.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
                    WHERE periodno <= ".$_POST['FromPeriod']." 
                        AND gltrans.type = 0 
                        AND gltrans.account != ''
                        AND gltrans.posted = 1
                        AND gltrans.periodno not like '%.5'
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
                    WHERE periodno <= ".$_POST['ToPeriod']." 
                        AND YEAR(gltrans.trandate) =  (select YEAR(lastdate_in_period) from periods where periodno = ".$_POST['FromPeriod'].")
                        AND gltrans.account != ''
                        AND gltrans.posted = 1
                        AND gltrans.periodno not like '%.5'
                        AND SUBSTRING_INDEX(account, '.', ".$nivelDes.") in (select accountcode from chartmaster where chartmaster.nu_nivel=".$nivelDes.")
                        ".$SQLTag."
                        ".$SQLUE."
                    group by SUBSTRING_INDEX(account, '.', ".$nivelDes.")) dtFinal
        ON chartmaster.accountcode = dtFinal.account

        LEFT JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
        -- LEFT JOIN accountsection ON accountgroups.sectioninaccounts = accountsection.sectionid
        JOIN `tb_sec_users_ue` ON `tb_sec_users_ue`.`userid` LIKE '$_SESSION[UserID]'

        WHERE ( `chartmaster`.`ln_clave` = `tb_sec_users_ue`.`ue` OR LENGTH(`chartmaster`.`ln_clave`) <= 2 OR `chartmaster`.`ln_clave` LIKE '%.%' )
        AND `chartmaster`.`nu_nivel` = '$nivelDes'
        $filtroUEs";

        if ($noZeroes==1) {
            $SQL = $SQL . ' HAVING (abs(prdCargos) > 0.1 OR abs(prdAbonos) > 0.1)';
        } elseif ($noZeroes==2) {
            $SQL = $SQL . ' HAVING (abs(prdFinal) > 0.1) ';
        } elseif ($noZeroes==3) {
            $SQL = $SQL . ' HAVING abs(prdFinal) > 0.1 and (abs(prdCargos) > 0.1 OR abs(prdAbonos) > 0.1) ';
        } elseif ($noZeroes==4) {
            $SQL = $SQL . ' HAVING abs(prdFinal) > 0.1 or (abs(prdCargos) > 0.1 OR abs(prdAbonos) > 0.1) ';
        }

        $SQL .= " ORDER BY LENGTH(SUBSTRING_INDEX(accountcode,'.', 2)) ASC, SUBSTRING_INDEX(accountcode,'.', 2) ASC, chartmaster.accountcode ASC;";
    
    if ($_SESSION['UserID'] == "desarrollo3") {
        // echo '<pre>'.$SQL.'</pre>';
    }

    $AccountsResult = DB_query($SQL,$db,_('Ninguna cuenta contable se recupero por el SQL porque'),_('El SQL que fallo fue:'));

    $sqlPeriodName="SELECT periodno, lastdate_in_period FROM periods where periodno=".($_POST['ToPeriod'])." ORDER BY periodno DESC limit 1;";
    $resultPeriodName=DB_query($sqlPeriodName,$db);
    $myrowPeriodName=DB_fetch_array($resultPeriodName);

    $strEncabezado="";
    ?>

    <table id="tblCuentas" cellpadding="2" border="1" class="tableHeaderVerde">

        <tr>
            <th colspan="2"> 
                Buscar: 
                <input type="text" id="txtFiltroCuentas" name="txtFiltroCuentas"  value="" placeholder="" autocomplete="off"  style="color:black;width:170px;">
            </th>

            <?php 
            if($_POST['cmbTipoBalanza'] == 1){ 
                $sqlPeriodAcumuladoName="SELECT periodno, lastdate_in_period FROM periods where periodno=".$_POST['FromPeriod']." ORDER BY periodno DESC limit 1;";
                $resultPeriodAcumuladoName=DB_query($sqlPeriodAcumuladoName,$db);
                $myrowPeriodAcumuladoName=DB_fetch_array($resultPeriodAcumuladoName);

                echo '  <th colspan = "2" class="text-center">Saldo Inicial</th>
                        <th colspan = "2" class="text-center">Saldo Acumulado <br> '.MonthAndYearFromSQLDate($myrowPeriodAcumuladoName['lastdate_in_period']).'</th>';
            }else{
                echo '<th colspan = "2" class="text-center">Saldo Inicial</th>';
            }
            ?>
            <th colspan = "2" class="text-center">Movimientos Del Período<br><?php echo MonthAndYearFromSQLDate($myrowPeriodName['lastdate_in_period']);?></th>
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
            if($_POST['cmbTipoBalanza'] == 1){ 
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
            $tieneRegistros = 1;
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
                    <td>". $rs['accountname']." </td>";
            
            if($_POST['cmbTipoBalanza'] == 1){
                
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
                    <td colspan="2" style="text-align: right;"><strong> Verificación de Totales </strong></td>';

        if($_POST['cmbTipoBalanza'] == 1){
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
    <br>
    <br>
    <a href="R.php" name="Link_PrintPDF" id="Link_PrintPDF" class="btn btn-primary" style="width: 200px; display: none;" target="_blank"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> PDF</a>
    <?php


  //   $j = 1;
  //   $k=0; //row colour counter
  //   $ActGrp ='';
  //   $ParentGroups = array();
  //   $Level =1; //level of nested sub-groups
  //   $ParentGroups[$Level]='';
  //   $GrpActual =array(0);
  //   $GrpBudget =array(0);
  //   $GrpPrdActual =array(0);
  //   $GrpPrdBudget =array(0);

  //   $PeriodProfitLoss = 0;
  //   $PeriodBudgetProfitLoss = 0;
  //   $MonthProfitLoss = 0;
  //   $MonthBudgetProfitLoss = 0;
  //   $BFwdProfitLoss = 0;
  //   $CheckMonth = 0;
  //   $CheckBudgetMonth = 0;
  //   $CheckPeriodActual = 0;
  //   $CheckPeriodBudget = 0;

  //   echo $TableHeader;

  //   $totalInicial = 0;
  //   $totalInicialAcreedora = 0;
  //   $totalCargos = 0;
  //   $totalAbonos = 0;
  //   $totalFinal = 0;
  //   $totalFinalAcreedora = 0;

  //   $MAYORtotalCargos = 0;
  //   $MAYORtotalAbonos = 0;
  //   $MAYORtotalInicial = 0;
  //   $MAYORtotalInicialAcreedora = 0;
  //   $MAYORtotalFinal = 0;
  //   $MAYORtotalFinalAcreedora = 0;


  //   $seccionanterior = "";
  //   $seccionnaturaleza = 0;
  //   $lineas2 = 0;

  //   # meter un proceso para sacar los datos repetidos por cuenta pasarlos al constructor es decir el while
  //   $datosProcesadosArr = [];
  //   while ($rs=DB_fetch_array($AccountsResult)) {
  //       $accountname = '';
  //       $lenCuentaTemp = ($nivelDes*2)-1;
  //       # calculo de los niveles superiores
  //       if ($nivelDes == 6) {
  //           $lenCuentaTemp += 1;
  //       } else if ($nivelDes == 7) {
  //           $lenCuentaTemp += 4;
  //       } else if ($nivelDes == 8) {
  //           $lenCuentaTemp += 8;
  //       } else if ($nivelDes == 9) {
  //           $lenCuentaTemp += 10;
  //       }

  //       $oldCuentaTemp = substr($rs['accountcode'], 0, $lenCuentaTemp);
        
  //       $cuentaTemp = substr(
  //           implode('', explode('.', $rs['accountcode'])),
  //           0,
  //           $nivelDes
  //       );
        
  //       if ($rs['groupCode'] == '1.1.1.2') {
  //           // echo "<br>cuentaTemp: ".$cuentaTemp;
  //           // echo "<br>".$rs['accountcode'].": ".$rs['prdCargos'];
  //           // echo "<br>";
  //           // print_r($datosProcesadosArr);
  //           // echo "<br>";
  //       }
  //       # si no se encuentra en la lista se agrega. caso contrario se suman los totales
  //       $encontroDato = 0;
  //       foreach ($datosProcesadosArr as $key => $myrow) {
  //           if ($rs['groupCode'] == $myrow['groupCode']) {
  //               $encontroDato = 1;
  //           }
  //       }
  //       if ($encontroDato == 0) {
  //           $sql="SELECT DISTINCT `accountname` as name FROM `chartmaster` WHERE `accountcode` ='$oldCuentaTemp'";
  //           $result = DB_query($sql, $db);
  //           $accountname = DB_fetch_array($result)['name'];
  //           $datosProcesadosArr[$cuentaTemp] = [
  //               'groupCode'=>$rs['groupCode'],
  //               'sectionname'=>$rs['sectionname'],
  //               'groupname'=>$rs['groupname'],
  //               'parentgroupname'=>$rs['parentgroupname'],
  //               'pandl'=>$rs['pandl'],
  //               'accountcode'=>$oldCuentaTemp,
  //               'accountname'=>$accountname,
  //               'tipo'=>$rs['tipo'],
  //               'sequenceintb'=>$rs['sequenceintb'],
  //               'naturaleza'=>$rs['naturaleza'],
  //               'prdActual'=>$rs['prdActual'],
  //               'prdCargos'=>$rs['prdCargos'],
  //               'prdAbonos'=>$rs['prdAbonos'],
  //               'prdInicial'=>$rs['prdInicial'],
  //               'firstprdbudgetbfwd'=>$rs['firstprdbudgetbfwd'],
  //               'prdFinal'=>$rs['prdFinal'],
  //               'monthactual'=>$rs['monthactual'],
  //               'monthbudget'=>$rs['monthbudget'],
  //               'lastprdbudgetcfwd'=>$rs['lastprdbudgetcfwd']
  //           ];
  //       } else {
  //           $datosProcesadosArr[$cuentaTemp]['prdActual'] += $rs['prdActual'];
  //           $datosProcesadosArr[$cuentaTemp]['prdCargos'] += $rs['prdCargos'];
  //           $datosProcesadosArr[$cuentaTemp]['prdAbonos'] += $rs['prdAbonos'];
  //           $datosProcesadosArr[$cuentaTemp]['prdInicial'] += $rs['prdInicial'];
  //           $datosProcesadosArr[$cuentaTemp]['firstprdbudgetbfwd'] += $rs['firstprdbudgetbfwd'];
  //           $datosProcesadosArr[$cuentaTemp]['prdFinal'] += $rs['prdFinal'];
  //           $datosProcesadosArr[$cuentaTemp]['monthactual'] += $rs['monthactual'];
  //           $datosProcesadosArr[$cuentaTemp]['monthbudget'] += $rs['monthbudget'];
  //           $datosProcesadosArr[$cuentaTemp]['lastprdbudgetcfwd'] += $rs['lastprdbudgetcfwd'];
  //       }

  //       if ($rs['groupCode'] == '1.1.1.2') {
  //           // echo "<br>";
  //           // print_r($datosProcesadosArr[$cuentaTemp]);
  //           // echo "<br>";
  //       }
  //   }
  //   if ($_SESSION['UserID'] == 'desarrollo') {
  //       // echo "<br>";
  //       // print_r($datosProcesadosArr);
  //       // echo "<br>";
  //   }
  //   foreach ($datosProcesadosArr as $key => $myrow) {
  //       // while ($myrow=DB_fetch_array($AccountsResult)) {
  //       $lineas2 = $lineas2 + 1;

  //       if ($myrow['groupCode'] == '1.1.1.2') {
  //           // echo "<br>";
  //           // print_r($myrow);
  //           // echo "<br>";
  //       }

  //       //echo $myrow['sectionname'];
  //       if ($lineas2 == 1) {
  //           $seccionnaturaleza = $myrow['naturaleza'];
  //           if ($_POST['accounttype'] == "2") {
  //               $seccionanterior = $myrow['groupname'];
  //           } elseif ($_POST['accounttype'] == "1") {
  //               $seccionanterior = $myrow['sectionname'];
  //           }
  //       }

  //       if ($myrow['pandl']==1) {
  //           $AccountPeriodActual = $myrow['prdFinal'] - $myrow['prdInicial'];
  //           $AccountPeriodBudget = $myrow['lastprdbudgetcfwd'] - $myrow['firstprdbudgetbfwd'];
  //           $PeriodProfitLoss += $AccountPeriodActual;
  //           $PeriodBudgetProfitLoss += $AccountPeriodBudget;
  //           $MonthProfitLoss += $myrow['monthactual'];
  //           $MonthBudgetProfitLoss += $myrow['monthbudget'];
  //           $BFwdProfitLoss += $myrow['prdInicial'];
  //       } else { /*PandL ==0 its a balance sheet account */
  //           if ($myrow['accountcode']==$RetainedEarningsAct) {
  //               $AccountPeriodActual = $BFwdProfitLoss + $myrow['prdFinal'];
  //               $AccountPeriodBudget = $BFwdProfitLoss + $myrow['lastprdbudgetcfwd'] - $myrow['firstprdbudgetbfwd'];
  //           } else {
  //               $AccountPeriodActual = $myrow['prdFinal'];
  //               $AccountPeriodBudget = $myrow['prdInicial'] + $myrow['lastprdbudgetcfwd'] - $myrow['firstprdbudgetbfwd'];
  //           }
  //       }

  //       if (!isset($GrpActual[$Level])) {
  //           $GrpActual[$Level]=0;
  //       }
  //       if (!isset($GrpBudget[$Level])) {
  //           $GrpBudget[$Level]=0;
  //       }
  //       if (!isset($GrpPrdActual[$Level])) {
  //           $GrpPrdActual[$Level]=0;
  //       }
  //       if (!isset($GrpPrdBudget[$Level])) {
  //           $GrpPrdBudget[$Level]=0;
  //       }


  //       $GrpActual[$Level] +=$myrow['monthactual'];
  //       $GrpBudget[$Level] +=$myrow['monthbudget'];
  //       $GrpPrdActual[$Level] +=$AccountPeriodActual;
  //       $GrpPrdBudget[$Level] +=$AccountPeriodBudget;

  //       $CheckMonth += $myrow['monthactual'];
  //       $CheckBudgetMonth += $myrow['monthbudget'];
  //       $CheckPeriodActual += $AccountPeriodActual;
  //       $CheckPeriodBudget += $AccountPeriodBudget;

  //       if ($k==1) {
  //           echo '<tr class="EvenTableRows">';
  //           $k=0;
  //       } else {
  //           echo '<tr class="OddTableRows">';
  //           $k++;
  //       }
  //       $ActEnquiryURL = "<a target=blank_ href='$rootpath/GLAccountInquiryMany.php?" . SID . 'tag=' . $_POST['unidadnegocio'] .'&xRegion=' . $_POST['xRegion'] .'&FromPeriod=' . $_POST['FromPeriod'] .'&ToPeriod=' . $_POST['ToPeriod'] . '&legalid=' . $_POST['legalid'] . '&Account=' . $myrow['accountcode'] . "&Show=Yes'>" . $myrow['accountcode'] . '<a>';

  //       #imprecion del codigo cuenta | nombre cuenta
  //       printf('<td>%s</td> <td align=left>%s</td>', $myrow['accountcode'], $myrow['accountname']);

  //       if ($myrow['naturaleza']==1) {
  //           printf('<td nowrap  style="text-align:right">%s</td>', '$'.number_format($myrow['prdInicial'], $_SESSION['DecimalPlaces'], '.', ','));
  //           printf('<td nowrap  style="text-align:right">$ 0.00</td>');
  //           $totalInicial = $totalInicial + $myrow['prdInicial'];
  //       } else {
  //           printf('<td nowrap  style="text-align:right">$ 0.00</td>');
  //           printf('<td nowrap  style="text-align:right">%s</td>', '$'.number_format($myrow['prdInicial']*-1, $_SESSION['DecimalPlaces'], '.', ','));
  //           $totalInicialAcreedora = $totalInicialAcreedora + $myrow['prdInicial']*-1;
  //       }

  //      //  if ($myrow['naturaleza']==1) {
  //      //      printf(
  //      //          '<td nowrap   style="text-align:right">%s</td>
  //               // <td nowrap   style="text-align:right">%s</td>
  //               // <td nowrap   style="text-align:right">%s</td>
  //               // <td nowrap   style="text-align:right"></td>
  //               // </tr>',
  //      //          '$'.number_format($myrow['prdCargos'], $_SESSION['DecimalPlaces'], '.', ','),
  //      //          '$'.number_format($myrow['prdAbonos'], $_SESSION['DecimalPlaces'], '.', ','),
  //      //          'BB$'.number_format($myrow['prdFinal'], $_SESSION['DecimalPlaces'], '.', ',')
  //      //      );
  //      //      $totalFinal = $totalFinal + $myrow['prdFinal'];
  //      //  } else {
  //      //      printf(
  //      //          '<td nowrap   style="text-align:right">%s</td>
  //               // <td nowrap   style="text-align:right">%s</td>
  //               // <td  nowrap  style="text-align:right"></td>
  //               // <td nowrap   style="text-align:right">%s</td>
  //               // </tr>',
  //      //          '$'.number_format($myrow['prdCargos'], $_SESSION['DecimalPlaces'], '.', ','),
  //      //          '$'.number_format($myrow['prdAbonos'], $_SESSION['DecimalPlaces'], '.', ','),
  //      //          'AA$'.number_format($myrow['prdFinal']*-1, $_SESSION['DecimalPlaces'], '.', ',')
  //      //      );
  //      //      $totalFinalAcreedora = $totalFinalAcreedora + $myrow['prdFinal']*-1;
  //      //  }

  //       $totalCargo = 0;
  //       $totalAbono = 0;
  //       if ($myrow['naturaleza']==1) {
  //           // Cargo
  //           $totalCargo = $myrow['prdInicial'];// + $myrow['prdCargos'];
  //       } else {
  //           // Abono
  //           $totalAbono = ($myrow['prdInicial'] * -1);// + $myrow['prdAbonos'];
  //       }

  //       $totalCargo += $myrow['prdCargos'];
  //       $totalAbono += $myrow['prdAbonos'];

  //       $totalFinal = $totalFinal + $totalCargo;
  //       $totalFinalAcreedora = $totalFinalAcreedora + $totalAbono;

  //       if ($myrow['groupCode'] == '1.1.1.2') {
  //           // echo "<br>naturaleza: ".$myrow['naturaleza'];
  //           // echo "<br>prdCargos: ".$myrow['prdCargos'];
  //           // echo "<br>prdAbonos: ".$myrow['prdAbonos'];
  //       }

  //       printf(
  //           '<td nowrap   style="text-align:right">%s</td>
  //           <td nowrap   style="text-align:right">%s</td>
  //           <td nowrap   style="text-align:right">%s</td>
  //           <td nowrap   style="text-align:right">%s</td>
  //           </tr>',
  //           '$'.number_format($myrow['prdCargos'], $_SESSION['DecimalPlaces'], '.', ','),
  //           '$'.number_format($myrow['prdAbonos'], $_SESSION['DecimalPlaces'], '.', ','),
  //           '$'.number_format($totalCargo, $_SESSION['DecimalPlaces'], '.', ','),
  //           '$'.number_format($totalAbono, $_SESSION['DecimalPlaces'], '.', ',')
  //       );

  //       $totalCargos = $totalCargos + $myrow['prdCargos'];
  //       $totalAbonos = $totalAbonos + $myrow['prdAbonos'];

  //       $MAYORtotalCargos = $MAYORtotalCargos + $myrow['prdCargos'];
  //       $MAYORtotalAbonos = $MAYORtotalAbonos + $myrow['prdAbonos'];

  //       if ($_POST['accounttype'] == "2") {
  //           $seccionanterior = $myrow['groupname'];
  //       } elseif ($_POST['accounttype'] == "1") {
  //           $seccionanterior = $myrow['sectionname'];
  //       }

  //       $j++;
  //   }//end of while loop


  //   printf(
  //       '<tr bgcolor="#ffffff">
        //  <td COLSPAN=2><FONT COLOR=BLUE><B>' . _('Verificaci&oacute;n de Totales') . '</B></FONT></td>
        //  <td nowrap  colspan=2 style="text-align:left">%s</td>
        //  <td nowrap  colspan=2 style="text-align:left">%s</td>
        //  <td nowrap  colspan=2 style="text-align:left">%s</td>
        // </tr>',
  //       '$'.number_format($totalInicial, $_SESSION['DecimalPlaces'], '.', ','),
  //       '$'.number_format($totalCargos, $_SESSION['DecimalPlaces'], '.', ','),
  //       '$'.number_format($totalFinal, $_SESSION['DecimalPlaces'], '.', ',')
  //   );

  //   printf(
  //       '<tr bgcolor="#ffffff">
        //  <td COLSPAN=2><FONT COLOR=BLUE><B>' . _('Verificaci&oacute;n de Totales') . '</B></FONT></td>
        //  <td nowrap  colspan=2 style="text-align:right">%s</td>
        //  <td nowrap  colspan=2 style="text-align:right">%s</td>
        //  <td nowrap  colspan=2 style="text-align:right">%s</td>
        // </tr>',
  //       '$'.number_format($totalInicialAcreedora, $_SESSION['DecimalPlaces'], '.', ','),
  //       '$'.number_format($totalAbonos, $_SESSION['DecimalPlaces'], '.', ','),
  //       '$'.number_format($totalFinalAcreedora, $_SESSION['DecimalPlaces'], '.', ',')
  //   );
}

if ($mostrarFormularioBotones == 1) {
    echo '<div class="panel panel-default">';
    echo '<div class="panel-body" align="center" id="divBotones" name="divBotones">';
    
    // echo '<input class="glyphicon glyphicon-search btn btn-default botonVerde" type="submit" Name="ShowTB" Value="' . _('Muestra Balanza') .'">';
    echo '<component-button type="submit" id="ShowTB" name="ShowTB" class="glyphicon glyphicon-search" value="Filtrar"></component-button>';
    // echo "tieneRegistros: ".$tieneRegistros;
    if($permisoExcel == 1 && $tieneRegistros == 1){
        echo '<input class="glyphicon glyphicon-search btn btn-default botonVerde" type="button" Name="PrintEXCEL" Value="'. _('Exportar a Excel') .'" onclick="fnExportarTablaExcel(\'tblCuentas\',\'Balanza_de_Comprobacion\');">';
    }

    echo '</div>';
    echo '</div>';
}

# comprobación de impresión de de PDF
if (isset($_POST['PrintPDF'])) {
    // niveles de filtro de informacion
    $tagref=$_POST['unidadnegocio'];
    $xDepartamento=$_POST['xDepartamento'];
    $xArea=$_POST['xArea'];
    $xRegion=$_POST['xRegion'];
    $legalid=$_POST['legalid'];
    $noZeroes=$_POST['noZeroes'];
    $accounttype=$_POST['accounttype'];
    $Desde=$_POST['FromPeriod'];
    $Hasta=$_POST['ToPeriod'];
    $SQLGroupby='';
    $nivelDes = $_POST['NivelDesagregacion'] == 0;

    echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath ."/PDFGLTrialBalanceV2.php?&Desde=".$Desde."
    &Hasta=".$Hasta."&tagref=".$tagref."&legalid=".$legalid."&xArea=".$xArea."&xDepartamento=".$xDepartamento."
    &Region=".$xRegion."&noZeroes=".$noZeroes."'>";

    if (isset($_POST['PrintEXCEL'])) {
        exit;
    }
}
/************************************** INCLUCION DE PANEL DE BUSQUEDA   **************************************/
if (!isset($_POST['PrintEXCEL'])) { // IF A
    echo '</FORM>';
    ?>
    <script language=javascript>
        $(document).ready(function() {

            if ("<?php echo $_POST['selectUnidadNegocio']; ?>" !== "") {
                fnSeleccionarDatosSelect("selectUnidadNegocio", "<?php echo $_POST['selectUnidadNegocio']; ?>");
                fnTraeUnidadesEjecutoras("<?php echo $_POST['selectUnidadNegocio']; ?>", 'selectUnidadEjecutora');
                fnSeleccionarDatosSelect("selectUnidadEjecutora", "<?php echo $_POST['selectUnidadEjecutora']; ?>");
            }

            if($('#txtUE').val()!=""){
                var strUE = $('#txtUE').val();
                console.log(strUE);
                var arrUE = strUE.split(",");
                $("#selectUnidadEjecutora").selectpicker('val',arrUE);
                $("#selectUnidadEjecutora").multiselect('refresh');
            }

            fnFormatoSelectGeneral('#NivelDesagregacion');


            $("#cmbTipoBalanza").change(function (){
                if($("#cmbTipoBalanza").val() == 1){
                    $("#divPeriodoDesde").hide();
                }else{
                    $("#divPeriodoDesde").show();
                }
            });

            /*Funcionalidad para la busqueda dentro de la tabla.*/
            $('#txtFiltroCuentas').keyup(function (index){
                var strFiltro = $('#txtFiltroCuentas').val().toLowerCase();
                var contador = 0;
                var numCuenta="";
                var numDescripcion="";
                var blnInfo="";

                var totalInicialCargo = 0;
                var totalInicialAbono = 0;
                var totalCargo = 0;
                var totalAbono = 0;
                var totalFinalCargo = 0;
                var totalFinalAbono = 0;
                var totalInicialCargoApertura = 0;
                var totalInicialAbonoApertura = 0;

                var TipoBalanza = <?= $jsTipoPoliza; ?>;
                var numColumnas = 0;

                $('#tblCuentas').find(".datosFiltro").each(function (index) {

                    blnInfo=$(this).data('info');
                    
                    if(blnInfo=='1'){

                        /*Ya contine la concatenacion de cuenta con descripcion*/
                        numCuenta=$(this).data('cuenta').toLowerCase();

                        if(numCuenta.toString().indexOf(strFiltro.toString())>=0){
                            $(this).css("display", "");

                            /*Obtener el recalculado de las columnas filtradas*/
                            numColumnas=2;
                            if(TipoBalanza == 1){
                                totalInicialCargoApertura = parseFloat(totalInicialCargoApertura) + parseFloat($(this).find("td").eq(numColumnas).text().replace(',',''));
                                numColumnas++;
                                totalInicialAbonoApertura = parseFloat(totalInicialAbonoApertura) + parseFloat($(this).find("td").eq(numColumnas).text().replace(',',''));
                                numColumnas++;
                            }
                            totalInicialCargo = parseFloat(totalInicialCargo) + parseFloat($(this).find("td").eq(numColumnas).text().replace(',',''));
                            numColumnas++;

                            totalInicialAbono = parseFloat(totalInicialAbono) + parseFloat($(this).find("td").eq(numColumnas).text().replace(',',''));
                            numColumnas++;

                            totalCargo = parseFloat(totalCargo) + parseFloat($(this).find("td").eq(numColumnas).text().replace(',',''));
                            numColumnas++;

                            totalAbono = parseFloat(totalAbono) + parseFloat($(this).find("td").eq(numColumnas).text().replace(',',''));
                            numColumnas++;

                            totalFinalCargo = parseFloat(totalFinalCargo) + parseFloat($(this).find("td").eq(numColumnas).text().replace(',',''));
                            numColumnas++;

                            totalFinalAbono = parseFloat(totalFinalAbono) + parseFloat($(this).find("td").eq(numColumnas).text().replace(',',''));
                            

                            contador++;
                        }else{
                            $(this).css("display", "none");
                        }
                    }else{

                        $(this).addClass( "hide" );
                        $(this).css("display", "none");

                        if(index != 0 && contador>=49){
                            $(this).removeClass( "hide" );
                        }
                        contador=0;
                    }
                });

                $('#tblCuentas').find('.datosTotales').each(function (index) {
                    numColumnas=1;
                    if(TipoBalanza == 1){
                        $(this).find("td").eq(numColumnas).text(fnFormatoNumeroMX(totalInicialCargoApertura));
                        numColumnas++;
                        $(this).find("td").eq(numColumnas).text(fnFormatoNumeroMX(totalInicialAbonoApertura));
                        numColumnas++;
                    }

                    $(this).find("td").eq(numColumnas).text(fnFormatoNumeroMX(totalInicialCargo));
                    numColumnas++;
                    $(this).find("td").eq(numColumnas).text(fnFormatoNumeroMX(totalInicialAbono));
                    numColumnas++;
                    $(this).find("td").eq(numColumnas).text(fnFormatoNumeroMX(totalCargo));
                    numColumnas++;
                    $(this).find("td").eq(numColumnas).text(fnFormatoNumeroMX(totalAbono));
                    numColumnas++;
                    $(this).find("td").eq(numColumnas).text(fnFormatoNumeroMX(totalFinalCargo));
                    numColumnas++;
                    $(this).find("td").eq(numColumnas).text(fnFormatoNumeroMX(totalFinalCargo));
                    
                });
            });

        });

        function fnFormatoNumeroMX(monto){
            var strMonto="";
            
            if(parseFloat(monto) == '0' || monto ==""){
                strMonto = "0.00";
            }else{
                strMonto = new Intl.NumberFormat('es-MX').format(parseFloat(monto));
            }

            return strMonto;

        }

        function fnExportarTablaExcel(componente ='',nombreExcel='excel'){
            var UR = document.getElementById('selectUnidadNegocio').value;
            var UE = document.getElementById('selectUnidadEjecutora');
            var UEXLS = '';
            for ( var i = 0; i < UE.selectedOptions.length; i++) {
                //console.log( unidadesnegocio.selectedOptions[i].value);
                if (i == 0) {
                    UEXLS = UE.selectedOptions[i].value;
                }else{
                    UEXLS = UEXLS+", "+UE.selectedOptions[i].value;
                }
            }

            var tipoPoliza = document.getElementById('cmbTipoBalanza').value;
            var idNoZeroes = document.getElementById('idNoZeroes').value;
            var NivelDesagregacion = document.getElementById('NivelDesagregacion').value;
            var NivelDesagregacionTexto = $( "#NivelDesagregacion option:selected" ).text();
            var idFromPeriod = document.getElementById('idFromPeriod').value;
            var idToPeriod = document.getElementById('idToPeriod').value;
            var txtFiltroCuentas = document.getElementById('txtFiltroCuentas').value;
            
            var url = "&FromPeriod=>"+idFromPeriod+"&ToPeriod=>"+idToPeriod+"&selectUnidadNegocio=>"+UR+"&selectUnidadEjecutora=>"+UEXLS+"&cmbTipoBalanza=>"+tipoPoliza+"&noZeroes=>"+idNoZeroes+"&NivelDesagregacion=>"+NivelDesagregacion+"&NivelDesagregacionTexto=>"+NivelDesagregacionTexto+"&txtFiltroCuentas=>"+txtFiltroCuentas;

            var fd = new FormData();
            fd.append("option","encryptarURL");
            fd.append("url",url);

            $.ajax({
                async:false,
                url:"modelo/GLAccounts_modelo.php",
                type:'POST',
                data: fd, 
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (data) {
                    if(data.result){
                        var Link_PrintPDF = document.getElementById("Link_PrintPDF");
                        Link_PrintPDF.href = data.Mensaje;
                        Link_PrintPDF.click();
                    }
                }
            }); 

            
        }

setTimeout(function(){
    $("button[data-id='selectUnidadEjecutora']").parent().hide()
    $("button[data-id='selectUnidadEjecutora']").hide()
}, 900);
    </script>
<?php
    include('includes/footer_Index.inc');
}// IF A

?>

<?php
////////////////////////////////////////////
// funciones creadas apartir del 20.04.18 //
////////////////////////////////////////////

function obtenNivelDesagregacion($value = '')
{
    global $db;
    $html = '';
    $numberToWordInstance = new Numbers_Words();
    $sql = "SELECT MAX(`nu_nivel`) as niveles FROM `chartmaster`";
    $result = DB_query($sql, $db);
    $maxNivel = DB_fetch_array($result)['niveles'];
    for ($i=1; $i <= $maxNivel; $i++) {
        if($i == $value){
            $html .= '<option value="'.$i.'" selected>'.ucfirst(obtenNombreNuemroOrdinal($i)).' Nivel</option>';

        }else{
            $html .= '<option value="'.$i.'">'.ucfirst(obtenNombreNuemroOrdinal($i)).' Nivel</option>';

        }
    }
    return $html;
}

function obtenNombreNuemroOrdinal($numero)
{
    $arr = [
        1=>'primer','segundo','tercer','cuarto', 'quinto', 'sexto','séptimo','octavo','noveno','décimo','undécimo','duodécimo','decimotercero','decimocuarto',
        'decimoquinto','decimosexto','decimoséptimo','decimoctavo','decimonoveno','vigésimo','vigésimo primero','vigésimo segundo',
        'vigésimo tercero','vigésimo cuarto','vigésimo quinto','vigésimo sexto','vigésimo séptimo','vigésimo octavo',
        'vigésimo noveno','trigésimo'
    ];
    return $arr[$numero];
}

function fnPeriodo($default,$db){
    $option ="";
    $selected="";

    $sql = "SELECT periodno, lastdate_in_period 
            FROM periods 
            WHERE date_format(lastdate_in_period,'%Y') = DATE_FORMAT(curdate(),'%Y') 
            ORDER BY periodno DESC;";
    $rsPeriods = DB_query($sql, $db);

    while ($myrow = DB_fetch_array($rsPeriods)) {
      if($default = $myrow['periodno']){
        $selected="selected";
      }
        $option .= "<option value=".$myrow['periodno']." ".$selected."> 
                      ".MonthAndYearFromSQLDate($myrow['lastdate_in_period']).
                    "</option>";
      
      $selected="";
    }

    return $option;
}

?>
