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
//
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 5;
$funcion = 2263;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db, 'Adecuación Presupuestal');
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

$transno = 0;
if (isset($_GET['transno'])) {
    $transno = $_GET['transno'];
}

$fechaActualAde = date('d-m-Y');
$autorizarGeneral = 0;
$permisoEditarEstCapturado = 0; // Havepermission($_SESSION ['UserID'], 2283, $db);
$soloActFoliosAutorizada = 0;
// if (isset($_GET['autorizar'])) {
//     // Se va autorizar y deshabilitar pagina
//     $autorizarGeneral = $_GET['autorizar'];
// }
$estatusAdecuacionGeneral = "";
$SQL = "SELECT estatus FROM chartdetailsbudgetlog WHERE type=250 and transno = '".$transno."' LIMIT 1";
$transResult = DB_query($SQL, $db);
while ($myrow = DB_fetch_array($transResult)) {
    $estatusAdecuacionGeneral = $myrow['estatus'];
}

if ($estatusAdecuacionGeneral == '7') {
    // $estatusAdecuacionGeneral == '6' ||
    // Si es estatus 6 o 7 deshabilitar todo
    $autorizarGeneral = 1;
}

if ($estatusAdecuacionGeneral == 3 && Havepermission($_SESSION ['UserID'], 2258, $db) == 0) {
    // Si es estatus 3 y no tiene permiso para modificar
    $autorizarGeneral = 1;
    if (Havepermission($_SESSION ['UserID'], 2281, $db) == 1
        || Havepermission($_SESSION ['UserID'], 2332, $db) == 1) {
        // Si tiene permiso a los siguiente estatus es perfil mayor
        $autorizarGeneral = 0;
    }
} elseif ($estatusAdecuacionGeneral == 4 && Havepermission($_SESSION ['UserID'], 2281, $db) == 0) {
    // Si es estatus 4 y no tiene permiso para modificar
    $autorizarGeneral = 1;
    if (Havepermission($_SESSION ['UserID'], 2332, $db) == 1) {
        // Si tiene permiso a los siguiente estatus es perfil mayor
        $autorizarGeneral = 0;
    }
} elseif ($estatusAdecuacionGeneral == 5 && Havepermission($_SESSION ['UserID'], 2332, $db) == 0) {
    // Si es estatus 5 y no tiene permiso para modificar
    $autorizarGeneral = 1;
} elseif ($estatusAdecuacionGeneral == 6 && Havepermission($_SESSION ['UserID'], 2332, $db) == 0) {
    // Si es estatus 6 y no tiene permiso para modificar
    $autorizarGeneral = 1;
}

if (Havepermission($_SESSION ['UserID'], 2332, $db) == 1) {
    // $autorizarGeneral == 1 &&
    // Permiso Autorizar Adecuacion
    $permisoEditarEstCapturado = 1;
}

if ($estatusAdecuacionGeneral == '7' && Havepermission($_SESSION ['UserID'], 2283, $db) == 1) {
    // Permiso para modificar datos de Adecuacion Autorizada
    $soloActFoliosAutorizada = 1;
}

if ($estatusAdecuacionGeneral == '0') {
    // Si esta cancelada solo mostrar informacion
    $autorizarGeneral = 1;
    $soloActFoliosAutorizada = 0;
}

$mesActualAdecuacion = date('m');
$anioActualAdecuacion = date('Y');

if ($_SESSION['UserID'] == 'desarrollo') {
    // echo "<br>autorizarGeneral: ".$autorizarGeneral;
    // echo "<br>permisoEditarEstCapturado: ".$permisoEditarEstCapturado;
    // echo "<br>estatusAdecuacionGeneral: ".$estatusAdecuacionGeneral;
    // echo "<br>soloActFoliosAutorizada: ".$soloActFoliosAutorizada;
}
?>

<script type="text/javascript">
  var transno = <?php echo $transno; ?>;
  var fechaActualAde = '<?php echo $fechaActualAde; ?>';
  var autorizarGeneral = '<?php echo $autorizarGeneral; ?>';
  var permisoEditarEstCapturado = '<?php echo $permisoEditarEstCapturado; ?>';
  var soloActFoliosAutorizada = '<?php echo $soloActFoliosAutorizada; ?>';
  var mesActualAdecuacion = '<?php echo $mesActualAdecuacion; ?>';
  var anioActualAdecuacion = '<?php echo $anioActualAdecuacion; ?>';
  var estatusAdecuacionGeneral = '<?php echo $estatusAdecuacionGeneral; ?>';
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
      
        <div class="col-md-6">
          <div class="row clearfix">
            <div class="col-md-6">
              <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>Ramo CR: </label></span>
                  </div>
                  <div class="col-md-9">
                      <select id="selectRamoCr" name="selectRamoCr" class="form-control selectRamo" onchange="fnCambioRamo('selectRamoCr', 'selectRazonSocial'); fnObtenerPresupuestoBusqueda(1, 1);">
                        <option value='-1'>Seleccionar...</option>
                      </select>
                  </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>Ramo REC: </label></span>
                  </div>
                  <div class="col-md-9">
                      <select id="selectRamoRec" name="selectRamoRec" class="form-control selectRamo" onchange="fnCambioRamo('selectRamoRec', 'selectRazonSocialRec'); fnObtenerPresupuestoBusqueda(2, 1);">
                        <option value='-1'>Seleccionar...</option>
                      </select>
                  </div>
              </div>
            </div>
          </div><!-- fin primer  row clearfix-->
          
          <div class="row clearfix"> 
            <div class="col-md-6">
              <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>Unidad CR: </label></span>
                  </div>
                  <div class="col-md-9">
                      <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocioSinRes" onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora')">
                        <option value='-1' selected>Seleccionar...</option>
                      </select>
                  </div>
              </div>
              <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>UE CR: </label></span>
                  </div>
                  <div class="col-md-9">
                      <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" onchange="fnObtenerPresupuestoBusqueda()">
                    <option value="-1">Seleccionar...</option>
                  </select>
                  </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>Unidad REC: </label></span>
                  </div>
                  <div class="col-md-9">
                      <select id="selectUnidadNegocioRec" name="selectUnidadNegocioRec" class="form-control selectUnidadNegocioSinRes" onchange="">
                        <option value='-1' selected>Seleccionar...</option>
                      </select>
                  </div>
              </div>
            </div>
          </div><!--  fin segundo-->

          <div class="row clearfix"> 
            <div class="col-md-6">
              
            </div>
            <div class="col-md-6">
            </div>
          </div><!--  fin segundo-->

          <div class="row clearfix">
            <div class="col-md-6">
              <component-text-label label="CTR. INT:" id="txtCtrInt" name="txtCtrInt" placeholder="CTR. INT." title="CTR. INT."></component-text-label>
            </div>
            <div class="col-md-6">
              <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>T REG: </label></span>
                  </div>
                  <div class="col-md-9">
                      <select id="txtTipoReg" name="txtTipoReg" class="form-control selectTipoReg"></select>
                  </div>
              </div>
            </div>
          </div><!-- fin 3-->

          <div class="row clearfix"> 
            <div class="col-md-6">
              <component-text-label label="C Contable:" id="txtCentroContable" name="txtCentroContable" placeholder="Centro Contable" title="Centro Contable" value="000" disabled="true"></component-text-label>
            </div>
            <div class="col-md-6">
              <component-text-label label="DICT. UPI:" id="txtDicUpi" name="txtDicUpi" placeholder="DICT. UPI." title="DICT. UPI."></component-text-label>
            </div>
          </div><!-- fin 4-->

          <div class="row clearfix">  
            <div class="col-md-6">
              <component-text-label label="P SICOP:" id="txtProcesoSicop" name="txtProcesoSicop" placeholder="Proceso SICOP" title="Proceso SICOP" disabled="true"></component-text-label>
            </div>
            <div class="col-md-6">
              <component-text-label label="F MAP:" id="txtFolioMap" name="txtFolioMap" placeholder="Folio MAP" title="Folio MAP" disabled="true"></component-text-label>
            </div>
          </div><!-- fin 5-->

          <div class="row clearfix"> 
            <div class="col-md-6">
              <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>JUSR: </label></span>
                  </div>
                  <div class="col-md-9">
                      <select id="txtJusR" name="txtJusR" class="form-control selectJusR" onchange="fnCambioInfoCaptura()"></select>
                  </div>
              </div>
            </div>
            <div class="col-md-6">
              <component-date-label label="Fecha EXP:" id="txtFechaCaptura" name="txtFechaCaptura" placeholder="Fecha EXP" title="Fecha EXP" value="<?php echo $fechaActualAde; ?>"></component-date-label>
            </div>
          </div><!-- fin  6-->

          <div class="row clearfix"> 
            <div class="col-md-6">
              <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>Conc R23: </label></span>
                  </div>
                  <div class="col-md-9">
                      <select id="selectConcR23" name="selectConcR23" class="form-control selectConcR23" disabled="true" onchange="fnCambioInfoCaptura()"></select>
                  </div>
              </div>
            </div>
            <div class="col-md-6">
              <component-date-label label="Fecha APL:" id="txtFechaApl" name="txtFechaApl" placeholder="Fecha APL" title="Fecha APL" value="<?php echo $fechaActualAde; ?>" disabled="true" onchange="fnCambioInfoCaptura()"></component-date-label>
            </div>
          </div><!-- fin  7-->

          <div class="row clearfix"> 
            <div class="col-md-6">
              <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>Clase: </label></span>
                  </div>
                  <div class="col-md-9">
                      <select id="selectTipoDoc" name="selectTipoDoc" class="form-control selectTipoDocumentoAdecuaciones" onchange="fnCambioTipoAdecuacion(this); fnCambioInfoCaptura();"></select>
                  </div>
              </div>
              <component-label-text label="Estatus:" id="txtEstatus" name="txtEstatus"></component-label-text>
            </div>
            <div class="col-md-6">
              <div class="form-inline row">
                <div class="col-md-3">
                    <span><label>Tipo Sol: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectTipoSolicitud" name="selectTipoSolicitud" class="form-control selectTipoSolicitud" onchange="fnCambioTipoSolicitud(); fnCambioInfoCaptura();"></select>
                </div>
              </div>
            </div>
          </div><!-- fin 8-->

          <div class="row clearfix"> 
            <div class="col-md-12">
              <textarea id="txtJustificacion" name="txtJustificacion" placeholder="Justificación" title="Justificación" cols="3" rows="4" class="form-control" onchange="fnCambioInfoCaptura();"></textarea>
            </div>
          </div>
        </div><!--  fin  row principal-->

        <div class="col-md-6">  
          <div class="col-md-6" align="center" style="">
            <label>REDUCCIÓN</label>
            <div id="divFiltrosReduccion" name="divFiltrosReduccion"></div>
            <component-button id="btnBuscarReduccion" name="btnBuscarReduccion" value="Buscar" onclick="fnObtenerPresupuestoBusqueda(1)" class="glyphicon glyphicon-search" disabled="true"></component-button>
            <br><br>
            <label>TOTAL REDUCCIÓN</label>
            <label class="form-control" id="txtTotalReducciones" name="txtTotalReducciones"></label>
          </div>
          <div class="col-md-6" align="center" style="">
            <label>AMPLIACIÓN</label>
            <div id="divFiltrosAmpliacion" name="divFiltrosAmpliacion"></div>
            <component-button id="btnBuscarAmpliaciones" name="btnBuscarAmpliaciones" value="Buscar" onclick="fnObtenerPresupuestoBusqueda(2)" class="glyphicon glyphicon-search" disabled="true"></component-button>
            <br><br>
            <label>TOTAL AMPLIACIÓN</label>
            <label class="form-control" id="txtTotalAmpliaciones" name="txtTotalAmpliaciones"></label>
          </div>
        </div>

      </div>
    </div>
  </div>
  
  <!--Panel Reducciones-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelReducciones" aria-expanded="true" aria-controls="collapseOne">
            Reducciones
          </a>
        </div>
        <div class="col-md-9 col-xs-9">
          <div class="input-group">
            <span class="input-group-addon" style="background: none; border: none;">Buscar:</span>
            <component-text label="Buscar: " id="txtBuscarReducciones" name="txtBuscarReducciones" placeholder="Buscar Presupuesto" title="Buscar Presupuesto" onkeypress="return fnObtenerPresupuestoEnter(event, 'tablaReducciones', 1)" disabled="true"></component-text> 
          </div>
        </div>
        <div class="col-md-4 col-xs-4" style="display: none;">
          <div class="input-group">
            <span class="input-group-addon" style="background: none; border: none;"> Tipo: </span>
            <select id="cmbTipoReduccion" name="cmbTipoReduccion" class="form-control selectTipoAdecuacionReduccion" onchange="fnCambioTipoReduccion(this)" disabled="true"></select>
          </div>
        </div>
      </h4>
    </div>
    <div id="PanelReducciones" name="PanelReducciones" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" style="overflow-x:scroll;overflow-y:scroll;">
      <!-- <div class="panel-body" style="overflow-x:scroll;overflow-y:scroll;"> -->
        <div id="divReducciones" name="divReducciones"></div>
        <div id="divMensajeOperacionReducciones" name="divMensajeOperacionReducciones"></div>
        <table class="table table-bordered" name="tablaReducciones" id="tablaReducciones" style="margin-bottom: 80px;">
          <tbody></tbody>
        </table>
      <!-- </div> -->
    </div>
    <table class="table table-bordered" name="tablaReduccioneTotales" id="tablaReduccioneTotales">
      <tbody></tbody>
    </table>
  </div>
  
  <!--Panel Ampliaciones-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelAmpliaciones" aria-expanded="true" aria-controls="collapseOne">
            Ampliaciones
          </a>
        </div>
        <div class="col-md-9 col-xs-9">
          <div class="input-group">
            <span class="input-group-addon" style="background: none; border: none;">Buscar:</span>
            <component-text label="Buscar: " id="txtBuscarApliaciones" name="txtBuscarApliaciones" placeholder="Buscar Presupuesto" title="Buscar Presupuesto" onkeypress="return fnObtenerPresupuestoEnter(event, 'tablaAmpliaciones', 2)" disabled="true"></component-text>
          </div>
        </div>
      </h4>
    </div>
    <div id="PanelAmpliaciones" name="PanelAmpliaciones" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" style="overflow-x:scroll;overflow-y:scroll;">
      <!-- <div class="panel-body" style="overflow-x:scroll;overflow-y:scroll;"> -->
        <div id="divAmpliaciones" name="divAmpliaciones"></div>
        <div id="divMensajeOperacionAmpliaciones" name="divMensajeOperacionAmpliaciones"></div>
        <table class="table table-bordered" name="tablaAmpliaciones" id="tablaAmpliaciones" style="margin-bottom: 80px;">
          <tbody></tbody>
        </table>
      <!-- </div> -->
    </div>
    <table class="table table-bordered" name="tablaAmpliacionesTotales" id="tablaAmpliacionesTotales">
      <tbody></tbody>
    </table>
  </div>

  <div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
        <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="GLBudgetsByTagV2_Panel.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>
        <?php if (($autorizarGeneral == '0' || $permisoEditarEstCapturado == '1') && $soloActFoliosAutorizada == '0') : ?>
        <component-button id="btnValidaciones" name="btnValidaciones" value="Validar Reglas" onclick="fnValidaciones(1,1)" class="glyphicon glyphicon-thumbs-up"></component-button>
        <?php endif ?>
        <!-- <component-button id="btnValidaciones" name="btnValidaciones" value="Nuevo" onclick="fnNuevaClavePresupuestal()" class="glyphicon glyphicon-thumbs-up"></component-button> -->
    </div>
  </div>
</div>

<script type="text/javascript" src="javascripts/GLBudgetsByTagV2.js?<?php echo rand(); ?>"></script>
<script type="text/javascript" src="javascripts/abc_clave_presupuestal.js?<?php echo rand(); ?>"></script>

<?php
include 'includes/footer_Index.inc';
?>