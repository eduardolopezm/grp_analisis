<?php
/**
 * Panel Suficiencia Manual
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Vista para el proceso del panel de Suficiencia Manual
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//
$PageSecurity = 5;
$funcion = 2345;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db);

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
//$permiso = Havepermission($_SESSION ['UserID'], 2257, $db);

//Librerias GRID
include('javascripts/libreriasGrid.inc');

$transno = 0;
if (isset($_GET['transno'])) {
    $transno = $_GET['transno'];
}

$type = 283;
if (isset($_GET['type'])) {
    $type = $_GET['type'];
}
// echo "<br>type: ".$type;
// echo "<br>transno: ".$transno;
?>

<script type="text/javascript">
  var typeDoc = '<?php echo $type; ?>';
  var transnoDoc = '<?php echo $transno; ?>';
</script>

<link rel="stylesheet" href="css/listabusqueda.css" />

<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
<!-- target="_blank" -->
<a href="suficiencia_manual.php" name="Link_NuevoGeneral" id="Link_NuevoGeneral" class="btn btn-primary" style="width: 200px; display: none;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Link Nuevo</a>

<div align="left">
  <!--Panel Busqueda-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
            <b>Gestión de Pólizas</b>
          </a>
        </div>
        <div class="col-md-2 col-xs-12 pull-right">
          <span class="pull-right" style="margin-right: 40px;">Folio <b id="txtNoCaptura" name="txtNoCaptura" style="margin-left: 20px;"></b></span>
        </div>
      </h4>
    </div>
    <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <!-- Filtros Principales -->
        <div class="row clearfix">
          <div class="col-md-4">
            <div class="form-inline row" style="display: none;">
                <div class="col-md-3">
                    <span><label>Dependencia: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioRazonSocial()">
                    </select>
                </div>
            </div>
            <!-- <br> -->
            <div class="form-inline row">
                <div class="col-md-3">
                    <span><label>UR: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora')"> 
                      <option value='-1'>Seleccionar...</option>
                    </select>
                </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-inline row">
                <div class="col-md-3">
                    <span><label>UE: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora">
                      <option value='-1'>Seleccionar...</option>
                    </select>
                </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-inline row">
                <div class="col-md-3">
                    <span><label>Tipo Póliza: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectPoliza" name="selectPoliza" class="form-control selectTipoPolizaSeguros" onchange="fnCambiaTipoPoliza(this)">
                    </select>
                </div>
            </div>
          </div>
        </div>
        <br/>
        <!-- Filtros Póliza -->
        <div class="row clearfix">
          <div class="col-md-4">
            <component-text-label label="Folio Póliza:" id="txtFolio" name="txtFolio" placeholder="Folio Póliza" title="Folio Póliza"></component-text-label>
          </div>
          <div class="col-md-4">
            <div class="form-inline row">
                <div class="col-md-3">
                    <span><label>Estatus Póliza: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectEstatusPoliza" name="selectEstatusPoliza" class="form-control selectEstatusPoliza">
                    </select>
                </div>
            </div>
          </div>
          <div class="col-md-4">
          </div>
        </div>
        <br>
        <div class="row clearfix">
          <div class="col-md-4">
            <div class="form-inline row">
                <div class="col-md-3">
                    <span><label>Aseguradora: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectAseguradora" name="selectAseguradora" class="form-control selectAseguradoraSeguros">
                    </select>
                </div>
            </div>
            <br>
            <div class="form-inline row">
                <div class="col-md-3">
                    <span><label>Cobertura: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectCobertura" name="selectCobertura" class="form-control selectCoberturaSeguros">
                    </select>
                </div>
            </div>
          </div>
          <div class="col-md-4">
            <component-decimales-label label="Deducible:" id="txtDeducible" name="txtDeducible" placeholder="% Deducible" title="% Deducible" value="0"></component-decimales-label>
            <br>
            <component-decimales-label label="Co-aseguro:" id="txtCoAseguro" name="txtCoAseguro" placeholder="% Co-aseguro" title="% Co-aseguro" value="0"></component-decimales-label>
          </div>
          <div class="col-md-4">
            <component-date-label label="Desde: " id="txtFechaDesde" name="txtFechaDesde" placeholder="Desde" title="Desde" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
            <br>
            <component-date-label label="Hasta: " id="txtFechaHasta" name="txtFechaHasta" placeholder="Hasta" title="Hasta" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
          </div>
        </div>
        <br>
        <div id="divFormuralioExtra">
        </div>
        <div class="row"></div>
        <div align="center">
          <component-button type="button" id="btnAgregarDetalle" name="btnAgregarDetalle" value="Agregar" class="glyphicon glyphicon-plus"></component-button>
        </div>
      </div>
    </div>
  </div>
  
  <div class=""> <!-- table-responsive -->
    <table class="table table-bordered" name="tablaDetalle" id="tablaDetalle">
      <tbody></tbody>
    </table>
  </div>
  <br>
  <div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
      <component-button type="button" id="btnGuardarInfo" name="btnGuardarInfo" value="Guardar" class="glyphicon glyphicon-floppy-disk"></component-button>
      <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="gestionPolizasPanel.php" class="btn btn-default botonVerde glyphicon glyphicon-arrow-left"> Regresar</a>
      
    </div>
  </div>
</div>

<script type="text/javascript" src="javascripts/gestionPolizas.js"></script>

<?php
include 'includes/footer_Index.inc';
?>
<script type="text/javascript">
    // Aplicar formato del SELECT
    fnFormatoSelectGeneral(".selectTipoPolizaSeguros");
</script>