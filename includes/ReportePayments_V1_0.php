<?php
/**
 * Reporte de Cheques y transferencias
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 03/10/2018
 * Fecha Modificación: 03/10/2018
 * Reporte de Cheques y transferencias
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//
$PageSecurity = 5;
$funcion = 370;
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
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Medio de Pago: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectTipoPago" name="selectTipoPago[]" class="form-control selectTipoPagoTeso" multiple="multiple">
                  </select>
              </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Banco: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectBancos" name="selectBancos[]" class="form-control selectBancos" multiple="multiple" onchange="fnCambioBancos()">
                  </select>
              </div>
          </div>
          <br>
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Cuenta CLABE: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectBancosCuentas" name="selectBancosCuentas[]" class="form-control selectBancosCuentas" multiple="multiple">
                  </select>
              </div>
          </div>
          <br>
          <component-number-label label="Folio:" id="txtFolioSuficiencia" name="txtFolioSuficiencia" placeholder="Folio" title="Folio" value=""></component-number-label>
        </div>
        <div class="col-md-4">
          <component-date-label label="Desde: " id="txtFechaDesde" name="txtFechaDesde" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
          <br>
          <component-date-label label="Hasta: " id="txtFechaHasta" name="txtFechaHasta" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
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
  
</div>

<script type="text/javascript" src="javascripts/ReportePayments_V1_0.js?<?php echo rand(); ?>"></script>

<?php
include 'includes/footer_Index.inc';
?>