<?php
/**
 * Mantenimiento de Pólizas
 *
 * @category Reporte
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 06/11/2017
 * Fecha Modificación: 06/11/2017
 * Mantenimiento de Pólizas
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$funcion      = 371;
$PageSecurity = 3;
$debug        = false;
include 'includes/session.inc';

$title = traeNombreFuncion($funcion, $db);
if (isset($_POST['PrintEXCEL'])) {
} else {
    include 'includes/header.inc';
}
include 'includes/SQL_CommonFunctions.inc';
include 'includes/SecurityFunctions.inc';
include "includes/SecurityUrl.php";

//Librerias GRID
include('javascripts/libreriasGrid.inc');

$msg = '';

//var_dump($_POST);

if (isset($_POST['FromYear'])) {
    $FromYear = $_POST['FromYear'];
} else {
    $FromYear = date('Y');
}

if (isset($_POST['FromMes'])) {
    $FromMes = $_POST['FromMes'];
} else {
    $FromMes = date('m');
}

if (isset($_POST['FromDia'])) {
    $FromDia = $_POST['FromDia'];
} else {
    $FromDia = date('d');
}

if (isset($_POST['ToYear'])) {
    $ToYear = $_POST['ToYear'];
} else {
    $ToYear = date('Y');
}

if (isset($_POST['ToMes'])) {
    $ToMes = $_POST['ToMes'];
} else {
    $ToMes = date('m');
}
if (isset($_POST['ToDia'])) {
    $ToDia = $_POST['ToDia'];
} else {
    $ToDia = date('d');
}

$fechaini = rtrim($FromYear) . '-' . rtrim($FromMes) . '-' . rtrim($FromDia);
$fechafin = rtrim($ToYear) . '-' . rtrim($ToMes) . '-' . rtrim($ToDia);

$fechaini = date('d-m-Y');
if (isset($_POST['txtFechaDesde'])) {
    $fechaini = $_POST['txtFechaDesde'];
}
//echo "<br>fechaini: ".$fechaini;

$fechafin = date('d-m-Y');
if (isset($_POST['txtFechaHasta'])) {
    $fechafin = $_POST['txtFechaHasta'];
}
//echo "<br>fechafin: ".$fechafin;

if (isset($_GET['txtpolizano'])) {
    $_POST['txtpolizano'] = $_GET['txtpolizano'];
}
if (isset($_GET['cbounidadnegocio'])) {
    $_POST['cbounidadnegocio'] = $_GET['cbounidadnegocio'];
}
if (isset($_GET['cbotipopoliza'])) {
    $_POST['cbotipopoliza'] = $_GET['cbotipopoliza'];
}

if (isset($_POST['txtpolizano'])) {
    $recibono = $_POST['txtpolizano'];
} else {
    $recibono = '';
}
if (isset($_POST['cbounidadnegocio'])) {
    $unidadnegocio = $_POST['cbounidadnegocio'];
} else {
    $unidadnegocio = 0;
}
if (isset($_POST['cbotipopoliza'])) {
    $tipopoliza = $_POST['cbotipopoliza'];
} else {
    $tipopoliza = -1;
}

if (isset($_POST['btnEliminar'])) {
    include 'includes/ReporteGLJournal.inc';
}

//******************** P E R M I S O S ***************************************
//->Permiso para ocultar los campos de modificar y eliminar
$permisoocultarcamposdeedicion = Havepermission($_SESSION['UserID'], 1488, $db);
$restriccionmodificarpoliza    = Havepermission($_SESSION['UserID'], 1623, $db);
$restriccionnuevapoliza        = Havepermission($_SESSION['UserID'], 1624, $db);
$perdespolizasSfecha           = Havepermission($_SESSION['UserID'], 1631, $db);

//****************************************************************************
echo '<form action=' . $_SERVER['PHP_SELF'] . ' method=post name=form1>';
?>
<div align="left">
  <!--Panel Busqueda-->
  <div class="panel panel-default">
      <div class="panel-heading" role="tab" id="headingOne">
        <h4 class="panel-title row">
          <div class="col-md-3 col-xs-3 text-left">
            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
             <b>Criterios de filtrado</b>
            </a>
          </div>
        </h4>
      </div>
      <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
        <div class="panel-body">
          <div class="col-md-4">
              <div class="form-inline row" style="display: none;">
                <div class="col-md-3">
                    <span><label>Dependencia: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="cborazon" name="cborazon[]" class="form-control selectRazonSocial" multiple="multiple" onchange="fnCambioDependeciaGeneral('cborazon', 'cbounidadnegocio')">
                    </select>
                </div>
              </div>
              <!-- <br> -->
              <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>UR: </label></span>
                  </div>
                  <div class="col-md-9">
                      <select id="cbounidadnegocio" name="cbounidadnegocio[]" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadResponsableGeneral('cbounidadnegocio','selectUnidadEjecutora')" multiple="multiple"> 
                      </select>
                  </div>
              </div>
              <br>
              <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>UE: </label></span>
                  </div>
                  <div class="col-md-9">
                      <select id="selectUnidadEjecutora" name="selectUnidadEjecutora[]" class="form-control selectUnidadEjecutora" multiple="multiple">
                      </select>
                  </div>
              </div>
          </div>
          <div class="col-md-4">
              <div class="form-inline row">
                <div class="col-md-3">
                    <span><label>Tipo Operación: </label></span>
                </div>
                <div class="col-md-9">
                  <select id="cbotipopoliza" name="cbotipopoliza[]" class="form-control selectDocumentosPoliza" multiple="multiple">
                  </select>
              </div>
            </div>
            <br>
            <component-number-label label="No. Operación: " id="txtpolizano" name="txtpolizano" placeholder="No. Operación" title="No. Operación" value="<?php echo $polizano; ?>"></component-number-label>
            <br>
            <component-number-label label="Folio Póliza: " id="txtpolizanoFolio" name="txtpolizanoFolio" placeholder="Folio Póliza" title="Folio Póliza" value="<?php echo $polizano; ?>"></component-number-label>
          </div>
          <div class="col-md-4">
              <component-date-label label="Desde: " id="txtFechaDesde" name="txtFechaDesde" value="<?php echo $fechaini; ?>"></component-date-label>
              <br>
              <component-date-label label="Hasta: " id="txtFechaHasta" name="txtFechaHasta" value="<?php echo $fechafin; ?>"></component-date-label>
          </div>
          <div class="row"></div>
          <div align="center">
            <component-button type="button" id="buscar" name="buscar" class="glyphicon glyphicon-search" value="Filtrar" onclick="fnObtenerInformacion()"></component-button>
          </div>
        </div>
      </div>
  </div>
</div>

<div name="divTabla" id="divTabla">
    <div name="divContenidoTabla" id="divContenidoTabla"></div>
</div>
<?php
$url = "&NewJournal=>Yes";
$enc = new Encryption;
$url = $enc->encode($url);
$liga= "URL=" . $url;
?>
<br>
<div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
        <a href="<?php echo 'GLJournal.php?'.$liga; ?>" name="Link_NuevoGeneral" id="Link_NuevoGeneral" class="btn btn-primary" style="width: 200px; display: none;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Link Nuevo</a>
        <component-button type="button" id="btnNuevo" name="btnNuevo" class="glyphicon glyphicon-plus" onclick="fnVentanaNuevo()" value="Nuevo"></component-button>
    </div>
</div>
<?php

?>
<script type="text/javascript" src="javascripts/reporteGLJournal.js"></script>
<?php
include 'includes/footer_Index.inc';
?>
<script type="text/javascript">
function selAll(obj){
    var I = document.getElementById('I').value;
    //alert("valor de :" + I);
    for(i=0;i<I;i++){
        concatenar = "chk" + i
        chkobj = document.getElementById(concatenar);
        if(chkobj != null){
            chkobj.checked = obj.checked;
        }
    }
}
fnFormatoSelectGeneral(".cbotipopoliza");
fnFormatoSelectGeneral(".cborazon");
fnFormatoSelectGeneral(".cbounidadnegocio");
console.log("Mensaje Fin Pagina");
</script>
