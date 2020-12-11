<?php
/**
 * Panel Pagos Diversos (Compromisos, Directos, Viáticos, Subsidios, etc.)
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 13/08/2018
 * Fecha Modificación: 13/08/2018
 * Vista para el proceso del panel de Pagos Diversos (Compromisos, Directos, Viáticos, Subsidios, etc.)
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//
$PageSecurity = 5;
$funcion = 2443;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db);

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
//$permiso = Havepermission($_SESSION ['UserID'], 2257, $db);

//Librerias GRID
include('javascripts/libreriasGrid.inc');
?>

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
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio[]" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora')" multiple="multiple"> 
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
          <br>
          <component-text-label label="Proveedor / Beneficiario:" id="txtProveedor" name="txtProveedor" placeholder="Proveedor/Beneficiario" title="Proveedor/Beneficiario" value=""></component-text-label>
        </div>
        <div class="col-md-4">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Operación: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectTipoSuficiencia" name="selectTipoSuficiencia[]" class="form-control selectTipoOpeCompromiso" multiple="multiple">
                  </select>
              </div>
          </div>
          <br>
          <component-number-label label="Folio:" id="txtFolioSuficiencia" name="txtFolioSuficiencia" placeholder="Folio" title="Folio" value=""></component-number-label>
          <br>
          <component-number-label label="Número Devengado:" id="txtIdDevengado" name="txtIdDevengado" placeholder="Número Devengado" title="Número Devengado" value=""></component-number-label>
        </div>
        <div class="col-md-4">
          <component-date-label label="Desde: " id="txtFechaDesde" name="txtFechaDesde" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
          <br>
          <component-date-label label="Hasta: " id="txtFechaHasta" name="txtFechaHasta" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
          <br>
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Estatus: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectEstatusCompromiso" name="selectEstatusCompromiso[]" class="form-control selectEstatusCompromiso" multiple="multiple">
                  </select>
              </div>
          </div>
        </div>
        <div class="row"></div>
        <div align="center">
          <br>
          <component-button type="button" id="btnBusqueda" name="btnBusqueda" class="glyphicon glyphicon-search" onclick="fnObtenerRegistrosSuficiencia()" value="Filtrar"></component-button>
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
      <!-- <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="index.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a> -->
      <!-- <component-button type="button" id="btnNuevo" name="btnNuevo" class="glyphicon glyphicon-plus" onclick="fnVentanaSuficienciaManual()" value="Nuevo"></component-button> -->
      <a id="btnNuevo" name="btnNuevo" href="pagos.php" class="btn btn-default botonVerde glyphicon glyphicon-plus"> Nuevo</a>
    </div>
  </div>
</div>

<script type="text/javascript" src="javascripts/pagosPanel.js?<?php echo rand(); ?>"></script>

<?php
include 'includes/footer_Index.inc';
?>