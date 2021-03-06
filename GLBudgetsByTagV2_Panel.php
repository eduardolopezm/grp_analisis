<?php
/**
 * Adecuaciones Presupuestales
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 08/08/2017
 * Fecha Modificación: 08/08/2017
 * Vista para el proceso del panel de adecuaciones presupuestales
 */
//
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 5;
$funcion = 2263;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db);

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

// Permiso para validar si tiene usuario DGPOP
$permisoUsarLayoutGeneral = Havepermission($_SESSION['UserID'], 2331, $db);

//Librerias GRID
include('javascripts/libreriasGrid.inc');

$_SESSION['noCaptura'] = "0";

?>

<script type="text/javascript">
  var permisoUsarLayoutGeneral = '<?php echo $permisoUsarLayoutGeneral; ?>';
</script>

<link rel="stylesheet" href="css/listabusqueda.css" />

<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
<!-- target="_blank" -->
<a href="GLBudgetsByTagV2.php" name="Link_Adecuaciones" id="Link_Adecuaciones" class="btn btn-primary" style="width: 200px; display: none;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Adecuaciones</a>

<div align="left">
  <!--Panel Busqueda-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3">
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
                  <select id="selectRazonSocial" name="selectRazonSocial[]" class="form-control selectRazonSocial" onchange="fnCambioRazonSocial()" multiple="multiple">
                  </select>
              </div>
          </div>
          <!-- <br> -->
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio[]" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadNegocio()" multiple="multiple"> 
                  </select>
              </div>
          </div>
          <br>
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Clase: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectTipoDoc" name="selectTipoDoc[]" class="form-control selectTipoDocumentoAdecuaciones" onchange="fnCambioTipoAdecuacion(this)" multiple="multiple"></select>
              </div>
          </div>
          <br>
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Tipo Sol: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectTipoSolicitud" name="selectTipoSolicitud[]" class="form-control selectTipoSolicitud" multiple="multiple"></select>
              </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Estatus: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectEstatus" name="selectEstatus[]" class="form-control selectEstatusAdecuaciones" multiple="multiple">
                    <!-- <option value="-1">Seleccionar...</option> -->
                  </select>
              </div><br>
          </div>
          <!-- <br>
          <component-text-label label="Folio: " id="txtFolio" name="txtFolio" placeholder="Folio" title="Folio"></component-text-label> -->
          <input type="hidden" id="txtFolio" name="txtFolio" />
          <br>
          <component-number-label label="Folio: " id="txtNoCaptura" name="txtNoCaptura" placeholder="Folio" title="Folio"></component-number-label>
        </div>
        <div class="col-md-4">
          <component-date-label label="Desde: " id="txtFechaDesde" name="txtFechaDesde" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
          <br>
          <component-date-label label="Hasta: " id="txtFechaHasta" name="txtFechaHasta" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
          <!-- <br>
          <component-text-label label="No. Oficio: " id="txtNoOficio" name="txtNoOficio" placeholder="No. Oficio" title="No. Oficio"></component-text-label> -->
        </div>
        <div class="row"></div>
        <div align="center">
          <br>
          <component-button type="button" id="btnBusqueda" name="btnBusqueda" class="glyphicon glyphicon-search" onclick="fnObtenerAdecuaciones()" value="Filtrar"></component-button>
        </div>
      </div>
    </div>
  </div>

  <div name="divTabla" id="divTabla">
    <div name="divContenidoTabla" id="divContenidoTabla"></div>
  </div>
  
  <br>
  <div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
      <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="index.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>
      <component-button type="button" id="btnNuevo" name="btnNuevo" class="glyphicon glyphicon-plus" onclick="fnVentanaAdecuaciones()" value="Nuevo"></component-button>
    </div>
  </div>
</div>

<script type="text/javascript" src="javascripts/Subir_Archivos.js?<?php echo rand(); ?>"> </script>
<script type="text/javascript" src="javascripts/GLBudgetsByTagV2_Panel.js?<?php echo rand(); ?>"></script>
<script type="text/javascript" src="javascripts/layout_general.js?<?php echo rand(); ?>"></script>

<?php
include 'includes/footer_Index.inc';
?>