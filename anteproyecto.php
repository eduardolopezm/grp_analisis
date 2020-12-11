<?php
/**
 * Anteproyecto Captura
 *
 * @category
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Vista para el proceso de Anteproyecto Captura
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 5;
$funcion = 2386;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db, 'Anteproyecto');
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

// Variable para permiso de autorizacion
$usuarioOficinaCentral = Havepermission($_SESSION ['UserID'], 2305, $db);

$transno = 0;
if (isset($_GET['transno'])) {
    $transno = $_GET['transno'];
}

$type = 49;
if (isset($_GET['type'])) {
    $type = $_GET['type'];
}

$totalVisual = '$ '.number_format(0, $_SESSION['DecimalPlaces'], '.', '');

// echo "<br>type: ".$type;
// echo "<br>transno: ".$transno;

$fechaActualAde = date('d-m-Y');
$anioActualAdecuacion = $_SESSION['ejercicioFiscal'];
?>
<script type="text/javascript">
  var transno = <?php echo $transno; ?>;
  var type = <?php echo $type; ?>;
</script>
<script type="text/javascript">
  var fechaActualAde = '<?php echo $fechaActualAde; ?>';
  var anioActualAdecuacion = '<?php echo $anioActualAdecuacion; ?>';
</script>

<link rel="stylesheet" href="css/listabusqueda.css" />

<div id="divMensajeOperacion" name="divMensajeOperacion"></div>

<div align="left">
  
  <!--Panel Busqueda-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
            ENCABEZADO
          </a>
        </div>
        <div class="col-md-2 col-xs-12 pull-right">
          <span class="pull-right" style="margin-right: 40px;">Folio <b id="txtNoCaptura" name="txtNoCaptura" style="margin-left: 20px;"></b></span>
        </div>
      </h4>
    </div>
    <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <div class="col-md-4">
          <component-number-label label="SHCP: $ " id="txtNumberDecimales" name="txtNumberDecimales" title="SHCP" placeholder="SHCP" value="0" onchange="fnCambioCantidadValidar(this), fnCambioCantidadGeneral(this)"></component-number-label>
        </div>
        <div class="col-md-3">
          <component-date-label label="Fecha:" id="txtFechaCaptura" name="txtFechaCaptura" placeholder="Fecha" title="Fecha" value="<?php echo $fechaActualAde; ?>" disabled="true" onchange="fnCambioFecha(this)"></component-date-label>
        </div>
        <div class="col-md-2" style="display: none;">
          <div class="form-inline row">
              <div class="col-md-5 col-xs-12">
                  <label>PAAAS: </label>
              </div>
              <div class="col-md-4 col-xs-12">
                  <input type="checkbox" id="checkPaaas" name="checkPaaas" value="1" placeholder="Utilizar PAAAS" title="Utilizar PAAAS" class="form-control" onchange="fnCambioUsarPaaas(this)" />
              </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-inline row">
              <div class="col-md-6 col-xs-12">
                  <label>Una Fase: </label>
              </div>
              <div class="col-md-3 col-xs-12">
                  <input type="checkbox" id="checkSoloFase" name="checkSoloFase" value="1" placeholder="Solo una Fase" title="Solo una Fase" class="form-control" onchange="fnCambioUsarSoloUnaFase(this)" <?php echo (Havepermission($_SESSION['UserID'], 2428, $db) == 0 ? ' disabled="true"' : ''); ?> />
              </div>
          </div>
        </div>
        <div class="col-md-1">
          <div class="form-inline row">
              <div class="col-md-5 col-xs-12">
                  <label>UE: </label>
              </div>
              <div class="col-md-4 col-xs-12">
                  <input type="checkbox" id="checkUE" name="checkUE" value="1" placeholder="Utilizar Unidad Ejecutora" title="Utilizar Unidad Ejecutora" class="form-control" onchange="fnCambioUsarUe(this)" <?php echo (Havepermission($_SESSION['UserID'], 2429, $db) == 0 ? ' disabled="true"' : ''); ?> />
              </div>
          </div>
        </div>
        <div class="col-md-2">
          <component-number-label label="Año:" id="txtAnio" name="txtAnio" placeholder="Año" title="Año" value="<?php echo $anioActualAdecuacion; ?>" maxlength="4" onchange="fnCambioAnio(this)" disabled="true"></component-number-label>
        </div>
        <div class="row"></div>
        <div class="col-md-8">
          <component-textarea-label label="Justificación: " id="txtJustificacion" name="txtJustificacion" placeholder="Justificación" title="Justificación" cols="3" rows="2" onchange="fnCambioDescriocion(this)"></component-textarea-label>
        </div>
        <div class="col-md-2">
          <div class="form-inline row">
              <div class="col-md-6 col-xs-12">
                  <label>Justificación: </label>
              </div>
              <div class="col-md-3 col-xs-12">
                  <input type="checkbox" id="checkValidarJustificación" name="checkValidarJustificación" value="1" placeholder="Justificación" title="Justificación" class="form-control" onchange="fnCambioUsarJustificacion(this)" />
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!--Panel UR-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelUnidadResponsable" aria-expanded="true" aria-controls="collapseOne">
            Unidad Responsable
          </a>
        </div>
        <div class="col-md-2 col-xs-12 pull-right">
          <span class="pull-right" style="margin-right: 40px;">Total <b id="divTotalUnidadResponsable" name="divTotalUnidadResponsable" style="margin-left: 20px;"><?php echo $totalVisual; ?></b></span>
        </div>
      </h4>
    </div>
    <!-- in -->
    <div id="PanelUnidadResponsable" name="PanelUnidadResponsable" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" style="overflow-x:scroll;overflow-y:scroll;">
      <!-- <div class="panel-body"> -->
        <table class="table table-bordered" name="tablaUnidadResponsable" id="tablaUnidadResponsable">
          <tbody></tbody>
        </table>
      <!-- </div> -->
    </div>
  </div>

  <!--Panel UE-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelUnidadEjecutora" aria-expanded="true" aria-controls="collapseOne">
            Unidad Ejecutora
          </a>
        </div>
        <div class="col-md-2 col-xs-12 pull-right">
          <span class="pull-right" style="margin-right: 40px;">Total <b id="divTotalUnidadEjecutora" name="divTotalUnidadEjecutora" style="margin-left: 20px;"><?php echo $totalVisual; ?></b></span>
        </div>
      </h4>
    </div>
    <!-- in -->
    <div id="PanelUnidadEjecutora" name="PanelUnidadEjecutora" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne" style="overflow-x:scroll;overflow-y:scroll;">
      <!-- <div class="panel-body"> -->
        <table class="table table-bordered" name="tablaUnidadEjecutora" id="tablaUnidadEjecutora">
          <tbody></tbody>
        </table>
      <!-- </div> -->
    </div>
  </div>

  <!--Panel Capitulos-->
  <div class="panel panel-default" style="display: none;">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelCapitulo" aria-expanded="true" aria-controls="collapseOne">
            Capítulos
          </a>
        </div>
        <div class="col-md-2 col-xs-12 pull-right">
          <span class="pull-right" style="margin-right: 40px;">Total <b id="divTotalCapitulo" name="divTotalCapitulo" style="margin-left: 20px;"><?php echo $totalVisual; ?></b></span>
        </div>
      </h4>
    </div>
    <!-- in -->
    <div id="PanelCapitulo" name="PanelCapitulo" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" style="overflow-x:scroll;overflow-y:scroll;">
      <!-- <div class="panel-body"> -->
        <table class="table table-bordered" name="tablaCapitulos" id="tablaCapitulos">
          <tbody></tbody>
        </table>
      <!-- </div> -->
    </div>
  </div>

  <!--Panel Desgloce Anual-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-4 col-xs-3">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelClavePresupuestalAnual" aria-expanded="true" aria-controls="collapseOne">
            Total Anual
          </a>
          <component-button id="btnAgregarClaveAnual" name="btnAgregarClaveAnual" class="glyphicon glyphicon-plus" value="Nuevo"></component-button>
          <component-button id="btnAgregarClaveAnterior" name="btnAgregarClaveAnterior" class="glyphicon glyphicon-plus" value="Anterior"></component-button>
          <!-- <button type="button" id="btnAgregarClaveAnual" name="btnAgregarClaveAnual" class="btn btn-default botonVerde glyphicon glyphicon-plus"> Nuevo</button>
          <button type="button" id="btnAgregarClaveAnterior" name="btnAgregarClaveAnterior" class="btn btn-default botonVerde glyphicon glyphicon-plus"> Anterior</button> -->
        </div>
        <div class="col-md-4">
          <div class="input-group">
            <span class="input-group-addon" style="background: none; border: none;">Buscar:</span>
            <input type="text" class="form-control" id="txtBuscarClavesAnual" name="txtBuscarClavesAnual" placeholder="Buscar Presupuesto" title="Buscar Presupuesto" onkeypress="return fnObtenerPresupuestoEnter(event, 'tablaAmpliaciones', 2)" />
            <!-- <component-text label="Buscar: " id="txtBuscarApliaciones" name="txtBuscarApliaciones" placeholder="Buscar Presupuesto" title="Buscar Presupuesto" onkeypress="return fnObtenerPresupuestoEnter(event, 'tablaAmpliaciones', 2)"></component-text> -->
          </div>
        </div>
        <div class="col-md-4 col-xs-12 pull-right">
          <span class="pull-right" style="margin-right: 40px;">Total <b id="divTotalAnual" name="divTotalAnual" style="margin-left: 20px;"><?php echo $totalVisual; ?></b></span>
        </div>
      </h4>
    </div>
    <!-- in -->
    <div id="PanelClavePresupuestalAnual" name="PanelClavePresupuestalAnual" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" style="overflow-x:scroll;overflow-y:scroll;">
      <!-- <div class="panel-body"> -->
        <div class="container-fluid">
          <br>
          <div class="col-md-4">
            <div class="form-inline row">
                <div class="col-md-3">
                    <span><label>Configuración: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectConfigClave" name="selectConfigClave" class="form-control selectConfigClave selectGeneral" onchange="fnCambioConfiguracionClave(this)">
                      <option value="-1">Seleccionar...</option>
                    </select>
                </div>
            </div>
          </div>
          <div class="col-md-4">
          </div>
          <div class="col-md-4">
          	<component-button id="btnExportarClaves" name="btnExportarClaves" value="Exportar a Excel" class="glyphicon glyphicon-download pull-right"></component-button>
          </div>
        </div>
        <br>
        <table class="table table-bordered" name="tablaClavePresupuestalAnual" id="tablaClavePresupuestalAnual" style="margin-bottom: 80px;">
          <tbody></tbody>
        </table>
      <!-- </div> -->
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
        <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="anteproyectoPanel.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>
        <component-button id="btnValidaciones" name="btnValidaciones" value="Validar Información" class="glyphicon glyphicon-thumbs-up"></component-button>
        <component-button id="btnDividirCantidadMeses" name="btnDividirCantidadMeses" value="Doceavas" class="glyphicon glyphicon-transfer"></component-button>
        <component-button id="btnCantidadMesesTotal" name="btnCantidadMesesTotal" value="Total Anual" class="glyphicon glyphicon-transfer"></component-button>
    </div>
  </div>
</div>

<script type="text/javascript" src="javascripts/anteproyecto.js?<?php echo rand(); ?>"></script>

<?php
include 'includes/footer_Index.inc';
?>