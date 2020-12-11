<?php
/**
 * Suficiencia Manual
 *
 * @category
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Vista para el proceso de Suficiencia Manual
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 5;
$funcion = 2302;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db, 'Suficiencia Presupuestal');
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

// Variable para permiso de autorizacion
$usuarioOficinaCentral = Havepermission($_SESSION ['UserID'], 2305, $db);

$transno = 0;
if (isset($_GET['transno'])) {
    $transno = $_GET['transno'];
}

$type = 263;
if (isset($_GET['type'])) {
    $type = $_GET['type'];
}

$fechaActualAde = date('d-m-Y');
$autorizarGeneral = 0; // Variable deshabilitar general
$permisoEditarEstCapturado = 0; // Havepermission($_SESSION ['UserID'], 2283, $db);
$soloActFoliosAutorizada = 0;

$estatusAdecuacionGeneral = "";
$tipoSuficiencia = 0;
$suficienciaCancelada = 0;
$funcionPermisoMod = 0;
$nuRequisicionSuf = 0;

$SQL = "
SELECT tb_suficiencias.nu_estatus, tb_suficiencias.nu_tipo, tb_suficiencias.sn_cancel, tb_botones_statusSig.functionid, purchorders.requisitionno
FROM tb_suficiencias 
LEFT JOIN tb_botones_status ON tb_botones_status.sn_funcion_id = tb_suficiencias.sn_funcion_id AND tb_botones_status.statusid = tb_suficiencias.nu_estatus
LEFT JOIN tb_botones_status tb_botones_statusSig ON tb_botones_statusSig.sn_funcion_id = tb_suficiencias.sn_funcion_id AND tb_botones_statusSig.statusid = tb_botones_status.sn_estatus_siguiente
LEFT JOIN purchorders ON purchorders.orderno = tb_suficiencias.sn_orderno
WHERE nu_type = '".$type."' and nu_transno = '".$transno."'";
$transResult = DB_query($SQL, $db);
while ($myrow = DB_fetch_array($transResult)) {
    $estatusAdecuacionGeneral = $myrow['nu_estatus'];
    $tipoSuficiencia = $myrow['nu_tipo'];
    $suficienciaCancelada = $myrow['sn_cancel'];
    $funcionPermisoMod = $myrow['functionid'];
    $nuRequisicionSuf = $myrow['requisitionno'];
}

// $tipoSuficiencia == '1' ||
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

if ($tipoSuficiencia == 1) {
    // Suficiencia Automatica
}

if (empty($tipoSuficiencia)) {
    // Es suficiencia manual
    $tipoSuficiencia = 2;
}

if (empty(trim($suficienciaCancelada))) {
    $suficienciaCancelada = 0;
}

if ($suficienciaCancelada == 1) {
    // Si es rechazada habilitar edición
    $autorizarGeneral = 0;
}

if (empty($nuRequisicionSuf)) {
    $nuRequisicionSuf = 0;
}

$mesActualAdecuacion = date('m');

if ($_SESSION['UserID'] == 'desarrollo') {
    // echo "<br>transno: ".$transno;
    // echo "<br>type: ".$type;
    // echo "<br>autorizarGeneral: ".$autorizarGeneral;
    // echo "<br>permisoEditarEstCapturado: ".$permisoEditarEstCapturado;
    // echo "<br>estatusAdecuacionGeneral: ".$estatusAdecuacionGeneral;
    // echo "<br>soloActFoliosAutorizada: ".$soloActFoliosAutorizada;
    // echo "<br>suficienciaCancelada: ".$suficienciaCancelada;
    // echo "<br>tipoSuficiencia: ".$tipoSuficiencia;
    // echo "<br>nuRequisicionSuf: ".$nuRequisicionSuf;
    // echo "<br>mesActualAdecuacion: ".$mesActualAdecuacion;
    // echo "<br>usuarioOficinaCentral: ".$usuarioOficinaCentral;
}
?>

<script type="text/javascript">
  var transno = <?php echo $transno; ?>;
  var type = <?php echo $type; ?>;
  var fechaActualAde = '<?php echo $fechaActualAde; ?>';
  var autorizarGeneral = '<?php echo $autorizarGeneral; ?>';
  var permisoEditarEstCapturado = '<?php echo $permisoEditarEstCapturado; ?>';
  var soloActFoliosAutorizada = '<?php echo $soloActFoliosAutorizada; ?>';
  var suficienciaCancelada = '<?php echo $suficienciaCancelada; ?>';
  var tipoSuficiencia = '<?php echo $tipoSuficiencia; ?>';
  var nuRequisicionSuf = '<?php echo $nuRequisicionSuf; ?>';
  var mesActualAdecuacion = '<?php echo $mesActualAdecuacion; ?>';
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
          <div class="form-inline row" style="display: none;">
              <div class="col-md-3">
                  <span><label>Ramo CR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectRamoCr" name="selectRamoCr" class="form-control selectRamo">
                    <option value='-1'>Seleccionar...</option>
                  </select>
              </div>
          </div>
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
                  <span><label>UE: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" onchange="fnObtenerPresupuestoBusqueda()">
                    <option value="-1">Seleccionar...</option>
                  </select>
              </div>
          </div>
        </div>
        <div class="col-md-4">
          <component-date-label label="Fecha de Expedición:" id="txtFechaCaptura" name="txtFechaCaptura" placeholder="Fecha de Expedición" title="Fecha de Expedición" value="<?php echo $fechaActualAde; ?>" disabled="true"></component-date-label>
        </div>
        <div class="row"></div>
        <div class="col-md-12">
          <br>
          <!-- <component-textarea-label label="Justificación: " id="txtJustificacion" name="txtJustificacion" placeholder="Justificación" title="Justificación" cols="3" rows="2"></component-textarea-label> -->
          <div class="form-inline row">
              <div class="col-md-3 col-xs-12">
                  <label>Justificación:</label>
              </div>
              <div class="col-md-9 col-xs-12">
                  <textarea id="txtJustificacion" name="txtJustificacion" placeholder="Justificación" title="Justificación" class="form-control btn-block" style="width: 100%;" cols="3" rows="2" maxlength="5000"></textarea>
              </div>
          </div><br>
        </div>
      </div>
    </div>
  </div>

  <div id="panelSuficienciaInfo" name="panelSuficienciaInfo" style="display: none;">
    <!--Panel Reducciones-->
    <div class="panel panel-default">
      <div class="panel-heading" role="tab" id="headingOne">
        <h4 class="panel-title row">
          <div class="col-md-3 col-xs-3">
            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelReduccionesInfo" aria-expanded="true" aria-controls="collapseOne">
              Información a Cancelar
            </a>
          </div>
          <div class="col-md-6 col-xs-6">
            
          </div>
          <div class="col-md-3 col-xs-3">
            <div class="input-group">
              <span class="input-group-addon" style="background: none; border: none; font-size: 20px; text-align: right;">Total $ </span>
              <span class="input-group-addon" style="background: none; border: none; font-size: 20px; text-align: left;" id="txtTotalReduccionesInfo" name="txtTotalReduccionesInfo"> 0 </span>
            </div>
          </div>
        </h4>
      </div>
      <div id="PanelReduccionesInfo" name="PanelReduccionesInfo" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" style="overflow-x:scroll;overflow-y:scroll;">
        <!-- <div class="panel-body" style="overflow-x:scroll;overflow-y:scroll;"> -->
          <div id="divReduccionesInfo" name="divReduccionesInfo"></div>
          <div id="divMensajeOperacionReduccionesInfo" name="divMensajeOperacionReduccionesInfo"></div>
          <table class="table table-bordered" name="tablaReduccionesInfo" id="tablaReduccionesInfo">
            <tbody></tbody>
          </table>
        <!-- </div> -->
      </div>
    </div>
  </div>
  
  <!--Panel Reducciones-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-1 col-xs-1">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelReducciones" aria-expanded="true" aria-controls="collapseOne">
            Selección
          </a>
        </div>
        <div class="col-md-8 col-xs-8">
          <div class="input-group">
            <span class="input-group-addon" style="background: none; border: none;">Buscar:</span>
            <component-text label="Buscar: " id="txtBuscarReducciones" name="txtBuscarReducciones" placeholder="Buscar Presupuesto" title="Buscar Presupuesto" onkeypress="return fnObtenerPresupuestoEnter(event, 'tablaReducciones', 1)"></component-text> 
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
        <div id="divReducciones" name="divReducciones"></div>
        <div id="divMensajeOperacionReducciones" name="divMensajeOperacionReducciones"></div>
        <table class="table table-bordered" name="tablaReducciones" id="tablaReducciones">
          <tbody></tbody>
        </table>
      <!-- </div> -->
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
        <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="suficiencia_manual_panel.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>
    </div>
  </div>
</div>

<script type="text/javascript" src="javascripts/suficiencia_manual.js?<?php echo rand(); ?>"></script>

<?php
include 'includes/footer_Index.inc';
?>