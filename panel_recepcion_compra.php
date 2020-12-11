<?php
/**
 * Panel de Recepcion de Ordenes de Compra
 *
 * @category panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/12/2017
 * Fecha Modificación: 01/12/2017
 * Vista para el proceso de Recepcion de Ordenes de Compra
 */

$PageSecurity = 5;
$funcion = 2313;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db);

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
include('javascripts/libreriasGrid.inc');

// declaracion de permisos
$permisoNuevaOrden= Havepermission($_SESSION['UserID'], 1238, $db);
$permisoConsolidar= Havepermission($_SESSION['UserID'], 2272, $db);

//echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';
?>

<div id="divMensajeOperacion" name="divMensajeOperacion"></div>

<!--Panel Busqueda-->
<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelOrdenesCompra" aria-expanded="true" aria-controls="collapseOne">
            <b>Criterios de filtrado</b>
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelOrdenesCompra" name="PanelOrdenesCompra" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
      <div class="panel-body">

        <div class="row clearfix">
          <div class="col-md-4">
            <div class="form-inline row" style="display: none;">
                <div class="col-md-3">
                    <span><label>Dependencia: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioDependeciaGeneral('selectRazonSocial', 'selectUnidadNegocio')" multiple="true"></select>
                </div>
            </div>
            <!-- <br> -->
            <div class="form-inline row">
                <div class="col-md-3" style="vertical-align: middle;">
                    <span><label>UR: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectUnidadNegocio" name="selectUnidadNegocio[]" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora')" multiple="multiple">                    
                    </select>
                </div>
            </div>
          </div>
          <div class="col-md-4">
            <component-number-label label="Folio Requisición:" id="txtNumeroRequisicion" name="txtNumeroRequisicion" value="" placeholder="Folio Requisición" title="Folio Requisición"></component-number-label>
          </div>
          <div class="col-md-4">
            <component-date-label id="txtFechaInicio" label="Desde: " value="<?= date("d-m-Y")?>"></component-date-label>
          </div>
        </div>
        <br>
        <div class="row clearfix">
          <div class="col-md-4">
            <div class="form-inline row">
                <div class="col-md-3" style="vertical-align: middle;">
                    <span><label>UE: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectUnidadEjecutora" name="selectUnidadEjecutora[]" class="form-control selectUnidadEjecutora" multiple="multiple">
                    </select>
                </div>
            </div>
          </div>
          <div class="col-md-4">
            <component-number-label label="Folio Orden Compra:" id="txtOrdenCompra" name="txtOrdenCompra" value="" placeholder="Folio Orden Compra" title="Folio Orden Compra"></component-number-label>
          </div>
          <div class="col-md-4">
            <component-date-label id="txtFechaFin" label="Hasta: " value="<?= date("d-m-Y")?>"></component-date-label>
          </div>
        </div>
        <br>
        <div class="row clearfix">
          <div class="col-md-4">
            <component-text-label label="Código Proveedor:" id="txtCodigoProveedor" name="txtCodigoProveedor" value="" title="Código Proveedor:" ></component-text-label>
          </div>
          <div class="col-md-4">
            <component-text-label label="Nombre Proveedor:" id="txtNombreProveedor" name="txtNombreProveedor" value=""></component-text-label>
          </div>
          <div class="col-md-4">
          </div>
        </div>
        
        <div class="col-md-12">
            <br>
            <component-button type="submit" id="btnBuscarOrdenes" name="btnBuscarOrdenes" class="glyphicon glyphicon-search" onclick="return false;" value="Filtrar"></component-button>
            <?php
            // if ($permisoNuevaOrden == 1) {
            //     echo '<component-button type="submit" id="btnNuevaOrden" name="btnNuevaOrden" class="glyphicon glyphicon-copy"
            //         onclick="return false;" value="Nueva Orden Compra"></component-button>';
            // }
            // if ($permisoConsolidar == 1) {
            //     echo '<component-button type="submit" id="btnConsolidarRequisiciones" name="btnConsolidarRequisiciones" class="glyphicon glyphicon-screenshot" onclick="return false;" value="Consolidar Requisiciones"></component-button>';
            // }
            ?>
        </div>
      </div>
    </div>
</div>

<div name="divTabla" id="divTabla">
  <div name="divCatalogo" id="divCatalogo"></div>
</div>

<br>
<div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
        <a href="" name="Link_NuevoGeneral" id="Link_NuevoGeneral" class="btn btn-primary" style="width: 200px; display: none;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Link Operación</a>
    </div>
</div>
<?php

//echo '</form>';

?>
<script type="text/javascript" src="javascripts/panel_recepcion_compra.js"></script>
<?php
include 'includes/footer_Index.inc';