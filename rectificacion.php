<?php
/**
 * Captura del Oficio de Rectificación
 *
 * @category
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 12/09/2018
 * Fecha Modificación: 12/09/2018
 * Vista para el proceso de la captura del Oficio de Rectificación
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 5;
$funcion = 2461;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db, 'Oficio de Rectificación');
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

// Variable para permiso de autorizacion
$usuarioOficinaCentral = Havepermission($_SESSION ['UserID'], 2417, $db);

$transno = 0;
if (isset($_GET['transno'])) {
    $transno = $_GET['transno'];
}

$type = 304;
if (isset($_GET['type'])) {
    $type = $_GET['type'];
}

$fechaActualAde = date('d-m-Y');
$autorizarGeneral = 0; // Variable deshabilitar general
$permisoEditarEstCapturado = 0; // Havepermission($_SESSION ['UserID'], 2283, $db);
$soloActFoliosAutorizada = 0;

$estatusAdecuacionGeneral = "";
$funcionPermisoMod = 0;

$SQL = "
SELECT tb_rectificaciones.nu_estatus, tb_botones_statusSig.functionid
FROM tb_rectificaciones 
LEFT JOIN tb_botones_status ON tb_botones_status.sn_funcion_id = tb_rectificaciones.sn_funcion_id AND tb_botones_status.statusid = tb_rectificaciones.nu_estatus
LEFT JOIN tb_botones_status tb_botones_statusSig ON tb_botones_statusSig.sn_funcion_id = tb_rectificaciones.sn_funcion_id AND tb_botones_statusSig.statusid = tb_botones_status.sn_estatus_siguiente
WHERE nu_type = '".$type."' and nu_transno = '".$transno."'";
$transResult = DB_query($SQL, $db);
while ($myrow = DB_fetch_array($transResult)) {
    $estatusAdecuacionGeneral = $myrow['nu_estatus'];
    $funcionPermisoMod = $myrow['functionid'];
}

if ($estatusAdecuacionGeneral == '0' || $estatusAdecuacionGeneral == '4') {
    // 0 = Cancelado, 4 = Autorizado
    $autorizarGeneral = 1;
}

if (!empty(trim($funcionPermisoMod)) && $funcionPermisoMod != '0') {
    // Validar si puede modificarla, Tiene permiso para el siguiente Estatus
    $permisoMod = Havepermission($_SESSION['UserID'], $funcionPermisoMod, $db);
    // echo "<br>funcionPermisoMod: ".$funcionPermisoMod;
    // echo "<br>permisoMod: ".$permisoMod;
    if ($permisoMod == '0') {
        // Solo mostrar informacion
        $autorizarGeneral = 1;
    }
}

$mesActualAdecuacion = date('m');
$yearActualAdecuacion = date('Y');
// echo "<br>mesActualAdecuacion: ".$mesActualAdecuacion;

if ($_SESSION['UserID'] == 'desarrollo') {
    // echo "<br>transno: ".$transno;
    // echo "<br>type: ".$type;
    // echo "<br>autorizarGeneral: ".$autorizarGeneral;
    // echo "<br>permisoEditarEstCapturado: ".$permisoEditarEstCapturado;
    // echo "<br>estatusAdecuacionGeneral: ".$estatusAdecuacionGeneral;
    // echo "<br>soloActFoliosAutorizada: ".$soloActFoliosAutorizada;
    // echo "<br>mesActualAdecuacion: ".$mesActualAdecuacion;
    // echo "<br>yearActualAdecuacion: ".$yearActualAdecuacion;
    // echo "<br>usuarioOficinaCentral: ".$usuarioOficinaCentral;
}
if ($_SESSION['ejercicioFiscal'] != date('Y')) {
    // Si no es año actual tomar el mes de diciembre
    $mesActualAdecuacion = 12;
    // echo "<br>mesActualAdecuacion if: ".$mesActualAdecuacion;
}
?>

<script type="text/javascript">
  var transno = <?php echo $transno; ?>;
  var type = <?php echo $type; ?>;
  var fechaActualAde = '<?php echo $fechaActualAde; ?>';
  var autorizarGeneral = '<?php echo $autorizarGeneral; ?>';
  var permisoEditarEstCapturado = '<?php echo $permisoEditarEstCapturado; ?>';
  var soloActFoliosAutorizada = '<?php echo $soloActFoliosAutorizada; ?>';
  var mesActualAdecuacion = '<?php echo $mesActualAdecuacion; ?>';
  var yearActualAdecuacion = '<?php echo $yearActualAdecuacion; ?>';
  var usuarioOficinaCentral = '<?php echo $usuarioOficinaCentral; ?>';
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
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora'), fnObtenerPresupuestoBusqueda()">
                    <option value='-1' selected>Seleccionar...</option>
                  </select>
              </div>
          </div>
          <br>
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>UE: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" onchange="fnObtenerPresupuestoBusqueda()">
                    <option value="-1">Seleccionar...</option>
                  </select>
              </div>
          </div>
          <!-- <br> -->
          <!-- onchange="fnCambioIdCompromiso(this.value)" -->
          <!-- <component-number-label label="Número de Compromiso:" id="txtIdCompromiso" name="txtIdCompromiso" placeholder="Número de Compromiso" title="Número de Compromiso" value="" disabled="true"></component-number-label>
          <div align="center">
            <component-button id="btnBuscarCompromiso" name="btnBuscarCompromiso" value="Mostrar Información" class="glyphicon glyphicon-search" disabled="true"></component-button>
          </div> -->
        </div>
        <div class="col-md-4">
          <div class="form-inline row" style="display: none;">
              <div class="col-md-3">
                  <span><label>Dependencia: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioRazonSocial('selectRazonSocial', 'selectUnidadNegocio')">
                    <option value='-1'>Seleccionar...</option>
                  </select>
              </div>
          </div>
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Operación: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectTipo" name="selectTipo" class="form-control selectTipoOpeCompromiso" onchange="fnCambioTipoOperacion(this.value)">
                  </select>
              </div>
          </div>
          <br>
          <div class="row">
            <div class="col-md-3">
              <label id="lblCompromiso">Folio del Pagado:</label>
            </div>
            <div class="col-md-9">
              <div class="input-group">
                <input type="text" class="form-control validanumericos" name="txtIdCompromiso" id="txtIdCompromiso" placeholder="Folio del Pagado" title="Folio del Pagado" value="" disabled="true" />
                <span class="input-group-btn">
                  <button class="btn" style="background-color: #1B693F !important;" type="button" id="btnBuscarCompromiso" name="btnBuscarCompromiso" disabled="true"><i class="glyphicon glyphicon-search" style="color: #ffffff"></i></button>
                </span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <component-date-label label="Fecha de Expedición:" id="txtFechaCaptura" name="txtFechaCaptura" placeholder="Fecha de Expedición" title="Fecha de Expedición" value="<?php echo $fechaActualAde; ?>" disabled="true"></component-date-label>
          <br>
          <component-date-label label="Fecha de Autorización:" id="txtFechaAutorizacion" name="txtFechaAutorizacion" placeholder="Fecha de Autorización" title="Fecha de Autorización" value="<?php echo $fechaActualAde; ?>" disabled="true"></component-date-label>
        </div>
        <div class="row"></div>
        <div class="col-md-8">
          <br>
          <!-- <component-textarea-label label="Justificación: " id="txtJustificacion" name="txtJustificacion" placeholder="Justificación" title="Justificación" cols="3" rows="2" maxlength="5000"></component-textarea-label> -->
          <div class="form-inline row">
              <div class="col-md-3 col-xs-12">
                  <label>Justificación:</label>
              </div>
              <div class="col-md-9 col-xs-12">
                  <textarea id="txtJustificacion" name="txtJustificacion" placeholder="Justificación" title="Justificación" class="form-control btn-block" style="width: 100%;" cols="3" rows="2" maxlength="5000"></textarea>
              </div>
          </div><br>
        </div>
        <div class="col-md-4" id="divRetencionesTotales" style="display: none;">
          <div class="form-inline row">
              <div class="col-md-3 col-xs-6">
                  <label style="font-size: 15px;">Retenciones $ </label>
              </div>
              <div class="col-md-9 col-xs-6">
                  <span class="input-group-addon" style="background: none; border: none; font-size: 20px; text-align: left;" id="txtTotalRetencion" name="txtTotalRetencion"> 0 </span>
              </div>
          </div><br>
          <div class="form-inline row">
              <div class="col-md-3 col-xs-6">
                  <label style="font-size: 15px;">Total $ </label>
              </div>
              <div class="col-md-9 col-xs-6">
                  <span class="input-group-addon" style="background: none; border: none; font-size: 20px; text-align: left;" id="txtTotalGeneral" name="txtTotalGeneral"> 0 </span>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!--Panel Ampliaciones-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-1 col-xs-1">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelAmpliaciones" aria-expanded="true" aria-controls="collapseOne">
            DICE
          </a>
        </div>
        <div class="col-md-8 col-xs-8">
        </div>
        <div class="col-md-3 col-xs-3">
          <div class="input-group">
            <span class="input-group-addon" style="background: none; border: none; font-size: 20px; text-align: right;">Total $ </span>
            <span class="input-group-addon" style="background: none; border: none; font-size: 20px; text-align: left;" id="txtTotalAmpliaciones" name="txtTotalAmpliaciones"> 0 </span>
          </div>
        </div>
      </h4>
    </div>
    <div id="PanelAmpliaciones" name="PanelAmpliaciones" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" style="overflow-x:scroll;overflow-y:scroll;">
      <!-- <div class="panel-body" style="overflow-x:scroll;overflow-y:scroll;"> -->
        <table class="table table-bordered" name="tablaAmpliaciones" id="tablaAmpliaciones">
          <tbody></tbody>
        </table>
      <!-- </div> -->
    </div>
    <table class="table table-bordered" name="tablaAmpliacionesTotales" id="tablaAmpliacionesTotales">
      <tbody></tbody>
    </table>
  </div>
  
  <!--Panel Reducciones-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-1 col-xs-1">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelReducciones" aria-expanded="true" aria-controls="collapseOne">
            DEBE DECIR
          </a>
        </div>
        <div class="col-md-8 col-xs-8">
          <div class="input-group">
            <span class="input-group-addon" style="background: none; border: none;">Buscar:</span>
            <component-text label="Buscar: " id="txtBuscarReducciones" name="txtBuscarReducciones" placeholder="Buscar Presupuesto" title="Buscar Presupuesto" onkeypress="return fnObtenerPresupuestoEnter(event, 'tablaReducciones', 1)" disabled="true"></component-text> 
          </div>
        </div>
        <div class="col-md-3 col-xs-3">
          <div class="input-group">
            <span class="input-group-addon" style="background: none; border: none; font-size: 20px; text-align: right;">Total $ </span>
            <span class="input-group-addon" style="background: none; border: none; font-size: 20px; text-align: left;" id="txtTotalReducciones" name="txtTotalReducciones"> 0 </span>
          </div>
        </div>
      </h4>
    </div>
    <div id="PanelReducciones" name="PanelReducciones" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" style="overflow-x:scroll;overflow-y:scroll;">
      <!-- <div class="panel-body" style="overflow-x:scroll;overflow-y:scroll;"> -->
        <table class="table table-bordered" name="tablaReducciones" id="tablaReducciones">
          <tbody></tbody>
        </table>
      <!-- </div> -->
    </div>
    <table class="table table-bordered" name="tablaReduccioneTotales" id="tablaReduccioneTotales">
      <tbody></tbody>
    </table>
  </div>

  <div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
        <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="rectificacionPanel.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>
    </div>
  </div>
</div>

<script type="text/javascript" src="javascripts/rectificacion.js?<?php echo rand(); ?>"></script>

<?php
include 'includes/footer_Index.inc';
?>
<script type="text/javascript">
  fnFormatoSelectGeneral(".selectClabe");
</script>