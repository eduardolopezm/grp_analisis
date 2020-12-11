<?php
/**
 * Disposición Final de Bienes
 *
 * @category
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 16/10/2018
 * Fecha Modificación: 16/10/2018
 * Vista para el proceso de la Disposición Final de Bienes
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 5;
$funcion = 2487;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db, 'Programas de Disposicion Final Patrimonial');
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

// Variable para permiso de autorizacion
$usuarioOficinaCentral = Havepermission($_SESSION ['UserID'], 2417, $db);

$transno = 0;
if (isset($_GET['transno'])) {
    $transno = $_GET['transno'];
}

$type = 1003;
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
SELECT tb_Fixed_Baja_Patrimonial.nu_estatus, tb_botones_statusSig.functionid
FROM tb_Fixed_Baja_Patrimonial 
LEFT JOIN tb_botones_status ON tb_botones_status.sn_funcion_id = tb_Fixed_Baja_Patrimonial.sn_funcion_id AND tb_botones_status.statusid = tb_Fixed_Baja_Patrimonial.nu_estatus
LEFT JOIN tb_botones_status tb_botones_statusSig ON tb_botones_statusSig.sn_funcion_id = tb_Fixed_Baja_Patrimonial.sn_funcion_id AND tb_botones_statusSig.statusid = tb_botones_status.sn_estatus_siguiente
WHERE tb_Fixed_Baja_Patrimonial.nu_type = '".$type."' and tb_Fixed_Baja_Patrimonial.nu_transno = '".$transno."'";
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
  <div id="PanelPrincipal" class="panel panel-default">
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
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora')">
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
                  <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" onchange="">
                    <option value="-1">Seleccionar...</option>
                  </select>
              </div>
          </div>
          <br>
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Disposición Final: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectDisposicionFinal" name="selectDisposicionFinal" class="form-control selectDisposicionFinal" onchange="">
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
                  <span><label>Tipo de Bien: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectTipo" name="selectTipo" class="form-control selectTipoBien" onchange="">
                    <option value='-1' selected>Seleccionar...</option>
                  </select>
              </div>
          </div>
          <br>
          <div class="row">
            <div class="col-md-3">
              <label>Folio de Baja:</label>
            </div>
            <div class="col-md-9">
              <div class="input-group">
                <input type="text" class="form-control validanumericos" name="txtFolioBaja" id="txtFolioBaja" placeholder="Folio de Baja" title="Folio de Baja" value=""/>
                <span class="input-group-btn">
                  <button class="btn botonVerde" type="button" id="btnBuscarFolioBaja" name="btnBuscarFolioBaja"><i class="glyphicon glyphicon-search" style="color: #ffffff"></i></button>
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <component-date-label label="Fecha de Expedición:" id="txtFechaCaptura" name="txtFechaCaptura" placeholder="Fecha de Expedición" title="Fecha de Expedición" value="<?php echo $fechaActualAde; ?>" disabled="true"></component-date-label>
          <br>
          <component-label-text label="Estatus:" id="txtEstatus" name="txtEstatus" value=""></component-label-text>
        </div>
        <div class="row"></div>
        <div class="col-md-8">
          <br>
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

    <!--  cargar archivos -->
<div id="divPanelArchivos" class="panel panel-default">
  <div class="panel-heading" role="tab" id="headingOne">
    <h4 class="panel-title row">
      <div class="col-md-3 col-xs-3 text-left">
        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelAddarchivo" aria-expanded="true" aria-controls="collapseOne">
            <b>Archivos</b>
        </a>
      </div>
      <div  class="fr text-right ftc7">
        <span id="numeroFolio"></span>
      </div>
    </h4>
  </div>

  <div id="PanelAddarchivo" name="PanelAddarchivo" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">

      <div class="panel-body text-left">
          
        <!-- begin upload file -->
        <div class="col-xs-12 col-md-12 pt20">  
           <div class="soloCargarArchivos" id="uploadFilesDiv"> 
              <input type="hidden" id="esMultiple" name="esMultiple" value="1">
              <input type="hidden" value="" name="componente" id="componenteArchivos"/>
              <input type="hidden" value="2387" id="funcionArchivos" name="funcionArchivos"/>
              <input  type="hidden"  value="285" id="tipoArchivo"/>
              <input  type="hidden"  value="" id="transnoArchivo"/>
                <input  type="hidden"  value="" id="numberScene"/>
              <div id="mensajeArchivos"> </div>
              <div  id="subirArchivos"  class="col-md-12">
                  <div  style="color:#fff !important;">
                      <div class="col-md-6">
                          <div id="tipoInputFile"> </div> <!-- Set type to upload one or many files -->
                          <!--<input type="file"  class="btn bgc8"  :name="archivos','[]"  id="cargarMultiples"  multiple="multiple"  style="display: none;"/>-->
                          <button  class="btn bgc8" id="btnUploadFile" onclick="">
                              <span class="glyphicon glyphicon-file"></span>
                              Cargar oficio(s)
                          </button >

                            <button id="descargarMultiples" onclick="fnProcesosArchivosSubidos('descargar')" class="btn bgc8" style="display: none;">Descargar</button> 
                          <!-- -->

                          <!-- -->
                          <br>
                          <br>
                      </div>
                      <br>
                  </div>
                  
                  <div id="muestraAntesdeEnviar" class="" style="display: none;"> <!-- show files upload -->
                      <table class="table table-striped border" id="tablaDetallesArchivos" style="border:solid 1px #eee;">
                        <thead class="bgc8 text-center" style="color:#fff;">
                            <th class="text-center">Nombre</th>
                            <th class="text-center">Observaciones</th>
                            <th> </th> 
                        </thead>
                        <tbody>
                        </tbody>
                      </table>
                  </div>
              </div>
          
        </div>
      </div> <!--end  upload  file -->
    </div>
  </div>
</div><!--  cargar archivos -->
  
  <!--Panel Reducciones-->
  <div id="PanelPrincipalDetalle" class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-1 col-xs-1">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelReducciones" aria-expanded="true" aria-controls="collapseOne">
            Selección
          </a>
        </div>
        <div class="col-md-8 col-xs-8">
        </div>
        <div class="col-md-3 col-xs-3">
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
    <table class="table table-bordered" name="tablaReduccioneTotales" id="tablaReduccioneTotales">
      <tbody></tbody>
    </table>
  </div>

  <div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
      <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="disposicionPatrimonialPanel.php" class="btn btn-default botonVerde glyphicon glyphicon-arrow-left"> Regresar</a>
    </div>
    

  </div>
</div>

<script type="text/javascript" src="javascripts/disposicionPatrimonial.js?<?php echo rand(); ?>"></script>

<?php
include 'includes/footer_Index.inc';
?>