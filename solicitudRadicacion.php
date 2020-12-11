<?php
/**
 * Solicitud de Radicacion
 *
 * @category
 * @package ap_grp
 * @author Desarrollo <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 11/06/2017
 * Fecha Modificación: 11/06/2017
 * Se genera el presente programa para la solicitud de la información
 * de la radicacion.
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = './';
$funcion = 2388;

/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
$title= traeNombreFuncion($funcion, $db,'Solicitud de Radicación');
require 'includes/header.inc';
require 'javascripts/libreriasGrid.inc';

if (isset($_GET['Folio'])) {
    $int_folio_radicacion = $_GET['Folio'];
}else{
  $int_folio_radicacion='';
}

if (isset($_GET['idRadicacion'])) {
    $int_id_radicacion = $_GET['idRadicacion'];
}else{
  $int_id_radicacion='';
}

 $anoFiscalAnterion = 0;

  if ($_SESSION['ejercicioFiscal'] != date('Y')) {
        $anoFiscalAnterion = 1;
  }

echo "<input type='text' id='txtFolioRadicacion' class='hide' value='".$int_folio_radicacion."'>";
echo "<input type='text' id='txtIdRadicacion' class='hide' value='".$int_id_radicacion."'>";
echo "<input type='text' id='txtIdStatusRadicacion' class='hide' value=''>";

$permisoAutorizador= Havepermission($_SESSION['UserID'], 2432, $db);
$permisoAutorizadorOficinaCentral= Havepermission($_SESSION['UserID'], 2434, $db);
$permisoValidador= Havepermission($_SESSION['UserID'], 2431, $db);
$permisoCapturista= Havepermission($_SESSION['UserID'], 2430, $db);

$cssOcicinaCentral="hide";
if($permisoAutorizadorOficinaCentral == 1){
  $cssOcicinaCentral="";
}

$cssOficio="readonly";
if($permisoAutorizadorOficinaCentral == 1){
  $cssOficio="";
}
if($permisoAutorizador == 1){
  $cssOficio="";
}

?>
<script>
    var permisoAutorizador = <?= $permisoAutorizador ?>;
    var permisoAutorizadorOficinaCentral = <?= $permisoAutorizadorOficinaCentral ?>;
    var permisoValidador = <?= $permisoValidador ?>;
    var permisoCapturista = <?= $permisoCapturista ?>;
        var anoFiscalAnterion = <?= $anoFiscalAnterion ?>;
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

        <!-- UR, UE, CLC -->
        <div class="col-md-4">
          <div class="form-inline row" style="display: none;">
            <div class="col-md-3">
                <span><label>Dependencia: </label></span>
            </div>
            <div class="col-md-9">
                <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioRazonSocial('selectRazonSocial', 'selectUnidadNegocio'),fnObtenerPresupuestoBusqueda()">
                  <option value='-1'>Seleccionar...</option>
                </select>
            </div>
          </div>
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio">
                    <option value='-1' selected>Seleccionar...</option>
                  </select>
              </div>
          </div>
          <br>
          <div class="form-inline row ">
              <div class="col-md-3">
                  <span><label>UE: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadEjecutora" name="selectUnidadEjecutora[]" class="form-control selectUnidadEjecutora" onchange = "fnObtenerBeneficiarios(); fnObtenerFirmantes()">
                    <option value="-1">Seleccionar...</option>
                  </select>
              </div>
          </div>
          <br>
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Beneficiario: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectBeneficiario" name="selectBeneficiario" class="form-control selectBeneficiario">
                    <option value="-1">Seleccionar...</option>
                  </select>
              </div>
          </div>
          <br>
          <component-text-label label="R. F. C. :" id="rfcBeneficiario" name="rfcBeneficiario" placeholder="R. F. C. Beneficiario" maxlength="13" readonly></component-text-label>   
          <br> 
          <component-text-label label="Cuenta Bancaria:" id="clabeBeneficiario" name="clabeBeneficiario" placeholder="Cuenta Bancaria Beneficiario" maxlength="22" readonly></component-text-label>

        </div><!-- / UR, UE, CLC -->

        <!-- Programa presupuestal, capitulos, mes -->
        <div class="col-md-4">
          <div class="form-inline row">
              <div class="col-md-3" style="vertical-align: middle;">
                  <span><label>Programa Presupuestal: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectProgramaPresupuestal" name="selectProgramaPresupuestal" class="form-control selectProgramaPresupuestario" data-todos="true">
                    <option value="-1">Seleccionar...</option>
                  </select>
              </div>
          </div>
          <br>
          <div class="form-inline row">
              <div class="col-md-3" style="vertical-align: middle;">
                  <span><label>Capítulo: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectCapitulos" name="selectCapitulos[]" class="form-control selectCapitulosMinistracion" data-todos="true" multiple="true"></select>
              </div>
          </div>
          <br>
          <div class="form-inline row">
            <div class="col-md-3" style="vertical-align: middle;">
                <span><label>Mes: </label></span>
            </div>
            <div class="col-md-9">
                <select id="selectMesRadicacion" name="selectMesRadicacion" class="form-control selectMeses" data-todos="true"></select>
            </div>
          </div>
          <br>
          <div class="form-inline row">
            <div class="col-md-3" style="vertical-align: middle;">
                <span><label>CLABE Concentradora: </label></span>
            </div>
            <div class="col-md-9">
                <select id="selectClabeConcentradora" name="selectClabeConcentradora" class="form-control selectClabeConcentradora" data-todos="true"></select>
            </div>
          </div>
          <br>
          <component-text-label label="Referencia / Transferencia: " id="numTransferencia" name="numTransferencia" placeholder="Referencia / Transferencia " maxlength="30" ></component-text-label>

        </div><!-- / Programa presupuestal, capitulos, mes -->

        <!-- fechas -->
        <div class="col-md-4">
            <component-date-label label="Fecha Elaboración:" id="dateElaboracion" name="dateElaboracion" placeholder="Fecha Elaboración" title="Fecha Elaboración" value="<?= date('d-m-Y');?>" disabled></component-date-label>
            <br class="<?php echo($cssOcicinaCentral) ?>">
            <div class="form-inline row <?php echo($cssOcicinaCentral) ?>">
                <div class="col-md-3 col-xs-12">
                    <label> Programación del Pago: </label>
                </div>
                <div class="col-md-9 col-xs-12">
                    <div class="input-group date" data-date-format="dd-mm-yyyy">
                        <input type="text" id="datePago" name="datePago" class="form-control" placeholder="Programación del Pago" title="Programación del Pago" />
                        <span id="idIconCalenderPago" class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
            <br>
            <div class="form-inline row">
                <div class="col-md-3 col-xs-12">
                    <label> Fecha Autorización: </label>
                </div>
                <div class="col-md-9 col-xs-12">
                    <div class="input-group date" data-date-format="dd-mm-yyyy">
                        <input type="text" id="dateAutorizacion" name="dateAutorizacion" class="form-control" placeholder="Fecha Autorización" title="Fecha Autorización" />
                        <span id="idIconCalender" class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
            <br>
            <div class="form-inline row">
                <div class="col-md-3">
                    <span><label>Estatus: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectEstatusRadicacion" name="selectEstatusRadicacion" class="form-control selectEstatusMinistracion"></select>
                </div>
            </div>
            <br>
            <component-text-label label="Número de Oficio: " id="numOficio" name="numOficio" placeholder="Número de Oficio" maxlength="40" <?= $cssOficio; ?>></component-text-label>

            
        </div><!-- -col-md-4 -->

        <div class="row"></div><br>
        
        <div class="col-md-8">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Justificación: </label></span>
              </div>
              <div class="col-md-9">
                <textarea id="txtJustificacion" name="txtJustificacion" placeholder="Justificación" title="Justificación" cols="83" rows="4" class="form-control"></textarea>
              </div>
          </div>
        </div>

        <!-- <div class="col-md-7">
          <component-textarea-label label="Justificación: " id="txtJustificacion" name="txtJustificacion" placeholder="Justificación" title="Justificación" cols="3" rows="2" maxlength= "250"></component-textarea-label>
        </div> -->
      </div>
    </div>
  </div>

  <!--  cargar archivos -->
  <div id="divPanelArchivos" style="display: none;" class="panel panel-default">
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
                              <!-- <th class="text-center">Tamaño</th>   -->
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

  <button id="btnObtenerClaves" name="btnObtenerClaves" class="btn botonVerde glyphicon glyphicon-search"> Obtener Claves</button>

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
              <span class="input-group-addon" style="background: none; border: none; font-size: 20px; text-align: left;" id="txtTotalRadicacionInfo" name="txtTotalRadicacionInfo"> 0.00 </span>
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
  <div id="divContenidoRadicacion" class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-1 col-xs-1">
          
        </div>
        <div class="col-md-8 col-xs-8">
          <!-- <div class="input-group">
            <span class="input-group-addon" style="background: none; border: none;">Buscar:</span>
            <component-text label="Buscar: " id="txtCapituloPartida" name="txtCapituloPartida" placeholder="Buscar Presupuesto" title="Buscar Presupuesto" onkeypress=""></component-text> 
          </div> -->
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
          <tbody ></tbody>
        </table>
      <!-- </div> -->
    </div>
  </div>
  <?php
  if($permisoAutorizador == 1){
  ?>
    <div class="panel panel-default">
      <div class="panel-body" align="center" id="divFirma" name="divFirma">
        <div class="text-center">
          <div class="col-md-4 col-md-offset-4">
            <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Firmante: </label></span>
              </div>
              <div class="col-md-9">
                <select id="selectFirmaRadicacion" name="selectFirmaRadicacion" class="form-control selectFirmaRadicacion">
                  <option value="-1">Seleccionar...</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?
  }
  ?>

  <div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
      <button id="btnGuardarRadicacion" name="btnGuardarRadicacion" class="btn btn-default botonVerde glyphicon glyphicon-floppy-disk"> Guardar</button>        
      <button id="btnCancelar" name="btnCancelar" style="display: none;" class="btn btn-default botonVerde glyphicon glyphicon-remove"> Cancelar</button>

      <?php if($permisoAutorizador == "1"){
        echo '<button id="btnAutorizar" name="btnAutorizar" style="display: none;" class="btn btn-default botonVerde glyphicon glyphicon-flag"> Autorizar</button>';
      }?>
      

        <a id="linkRegresar" name="linkRegresar" href="radicacion.php" class="btn btn-default botonVerde glyphicon glyphicon-arrow-left"> Regresar</a>
    </div>
  </div>
</div>

<script type="text/javascript" src="javascripts/solicitudRadicacion.js?<?php echo rand(); ?>"></script>

<?php
include 'includes/footer_Index.inc';
?>