<?php
/**
 * Adecuaciones Presupuestales
 *
 * @category
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 08/08/2017
 * Fecha Modificación: 08/08/2017
 * Vista para el proceso de adecuaciones presupuestales
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//
$PageSecurity = 5;
$funcion = 2275;
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db);

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
$permiso = Havepermission($_SESSION ['UserID'], 2257, $db);

//Librerias GRID
include('javascripts/libreriasGrid.inc');

$_SESSION['noCaptura'] = "0";

?>

<link rel="stylesheet" href="css/listabusqueda.css" />

<div id="divMensajeOperacion" name="divMensajeOperacion"></div>

<a href="GLBudgetsByTagV2.php" name="Link_Adecuaciones" id="Link_Adecuaciones" class="btn btn-primary" style="width: 200px; display: none;" target="_blank"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Adecuaciones</a>

<div align="left">

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li id="tabMinistrado" role="presentation">
      <a href="#tabMinistrado" aria-controls="Articulos" role="tab" data-toggle="tab" title="Articulos" class="bgc10">Ministrado</a>
    </li>
    <li id="tabPresupuesto" role="presentation" class="active">
      <a href="#tabPresupuesto" aria-controls="Servicios" role="tab" data-toggle="tab" title="Servicios" class="bgc10">Presupuesto</a>
    </li>
    <li id="tabradicado" role="presentation">
      <a href="#tabradicado" aria-controls="Instrumental" role="tab" data-toggle="tab" title="Instrumentales" class="bgc10">Radicado</a>
    </li>
  </ul>
  
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
          <div class="form-inline row" style="display: none;">
              <div class="col-md-3">
                  <span><label>Dependencia: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectRazonSocial" name="selectRazonSocial[]" class="form-control selectRazonSocial" onchange="fnCambioRazonSocial()" multiple="multiple">
                  </select>
              </div>
          </div>
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio[]" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora')" multiple="multiple"> 
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
                  <select id="selectUnidadEjecutora" name="selectUnidadEjecutora[]" class="form-control selectUnidadEjecutora" multiple="multiple">
                  </select>
              </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Meses: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectMeses" name="selectMeses[]" class="form-control selectMeses" multiple="multiple">
                  </select>
              </div>
          </div>
        </div>
        <div class="row"></div>
        <br>
        <div class="col-md-4">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Tipo: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectTipoPresupuesto" name="selectTipoPresupuesto" class="form-control selectTipoPresupuesto" onchange="fnConfigClavePresupuesto(this.value, 'selectConfigClave'); fnCargarTipoInformación('.selectTipoMovimiento', 1); fnLimpiarContenidoDiv('divSelConfigClave');">
                      <option value="-1">Seleccionar...</option>
                  </select>
              </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Configuración: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectConfigClave" name="selectConfigClave" class="form-control selectConfigClave selectGeneral" onchange="fnObtenerConfigClave()">
                    <option value="-1">Seleccionar...</option>
                  </select>
              </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Movimiento: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectTipo" name="selectTipo[]" class="form-control selectTipoMovimiento selectGeneral" multiple="multiple"></select>
              </div>
          </div>
        </div>
        <div class="row"></div>

        <div class="col-md-12" id="divTituloComponentes" name="divTituloComponentes" style="display: none;">
          <h4>Componentes de Clave</h4>
          <h5><input type="checkbox" id="checkTodoConfig" name="checkTodoConfig" onclick="javascript:fnSeleccionCheckbox(this);" checked="true"> Todos</h5>
        </div>
        <?php echo "<form action='" . $_SERVER['PHP_SELF'] . "?" . SID . "' method=post id='formSelect' name='formSelect'>"; ?>
        <div class="col-md-12" id="divSelConfigClave" name="divSelConfigClave">
        </div>
        <?php echo '</form>'; ?>
        <div class="row"></div>
        <div align="center">
          <br>
          <component-button type="button" id="btnBusqueda" name="btnBusqueda" class="glyphicon glyphicon-search" onclick="fnEstadoEjercicio()" value="Filtrar"></component-button>
        </div>
      </div>
    </div>
  </div>

  <div name="divTabla" id="divTabla">
    <div name="divContenidoTabla" id="divContenidoTabla"></div>
  </div>
</div>

<script type="text/javascript" src="javascripts/estado_ejercicio_presupuesto.js?<?php echo rand(); ?>"></script>

<?php
include 'includes/footer_Index.inc';
?>