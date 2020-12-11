<?php
/**
 * ABC de Ramo
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/07/2017
 * Fecha Modificación: 31/07/2017
 * Se realizan operación pero el Alta, Baja y Modificación
 */

$PageSecurity=5;
include('includes/session.inc');
$funcion=2269;
$title = traeNombreFuncion($funcion, $db);
include('includes/header.inc');
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
//
//Librerias GRID
include('javascripts/libreriasGrid.inc');
?>

<div id="divMensajeOperacion" name="divMensajeOperacion"></div>

<div align="left">
  
  <!--Panel Busqueda-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
            Búsqueda
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <div class="col-md-4">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Dependencia: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectRazonSocial" name="selectRazonSocial[]" class="form-control selectRazonSocial" onchange="fnCambioRazonSocialClaveNueva()">
                    <option value='-1'>Seleccionar...</option>
                  </select>
              </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>URG: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadNegocioClaveNueva()"> 
                    <option value='-1' selected>Seleccionar...</option>
                  </select>
              </div>
          </div>
        </div> 
        <div class="row"></div>
        <div align="center">
          <br>
          <component-button type="button" id="btnBusquedaNuevaClave" name="btnBusquedaNuevaClave" class="glyphicon glyphicon-search" onclick="fnBuscarClavesNuevas()" value="Buscar"></component-button>
          <component-button type="button" id="btnNuevaClave" name="btnNuevaClave" class="glyphicon glyphicon-plus" onclick="fnNuevaClavePresupuestal()" value="Nueva"></component-button>
        </div>
      </div>
    </div>
  </div>

</div>

<div name="divTablaClavesNuevasManuales" id="divTablaClavesNuevasManuales">
  <div name="divClavesNuevasManuales" id="divClavesNuevasManuales" class="divClavesNuevasManuales"></div>
</div>

<script type="text/javascript" src="javascripts/abc_clave_presupuestal.js"></script>
<?php
include 'includes/footer_Index.inc';
?>