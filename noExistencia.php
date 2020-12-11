<?php
/**
 * Captura de la Requisición
 *
 * @category     ABC
 * @package      ap_grp
 * @author       Luis Aguilar Sandoval <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Se realizan operación de captura de la Requisición.
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
$PageSecurity = 4;

include "includes/SecurityUrl.php";
require 'includes/DefinePOClass.php';
require 'includes/session.inc';

$title   = "";
$funcion = 2320;
//$title = "Requisici&oacute;n";
$title = "No Existencia";

require 'includes/header.inc';
//$funcion = 2265;
require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';


//Librerias GRID
require 'javascripts/libreriasGrid.inc';
$periodoRequisicion = GetPeriod(date('d/m/Y'), $db);
$datoidReq = 0;
$datoidNoExtist = 0;

if (isset($_GET['idnoexist'])) {
    $datoidNoExtist = $_GET['idnoexist'];
}

if (isset($_GET['idrequisicion'])) {
    $datoidReq = $_GET['idrequisicion'];
}

$enc = new Encryption;
        $url = "&idnoexist=>" . $_GET['idnoexist'] . "&idrequisicion=> ". $_GET['idrequisicion'];
        $url = $enc->encode($url);
        $liga= "URL=" . $url;
?>
<script type="text/javascript">
  $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();   
});
  var idRequisicionGeneral = '<?php echo $datoidReq; ?>';
  var idNoExistenciaGeneral = '<?php echo $datoidNoExtist; ?>';
  var urlNoExist = '<?php echo $liga; ?>';
</script>
<script type="text/javascript" src="javascripts/noExistencia.js"></script>
<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
<div id="msjValidacion" name="msjValidacion"></div>

<div class="container" id="idContentNoExistecia">
  <div id="top-noExistencia">
    <div class="col-lg-12 col-md-12 col-sm-12 panel panel-default p0 mb20">
      <div class="panel-heading h35" role="tab" id="headingOne">
        <h4 class="panel-title">
          <div class="fl text-left">
            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#topNoExistencia" aria-expanded="true" aria-controls="collapseOne">
              <b>Encabezado</b>
            </a>
          </div>
          <div id="idStatusReq" class="fr text-right ftc7">
            <span>Estatus de la Requisición: </span><input type="hidden" id="idperfilusr">
            <label id="statusReq"></label>
          </div>
        </h4>
      </div>
      <div id="topNoExistencia" class="row panel-collapse collapse in ptb5" role="tabpanel" aria-labelledby="headingOne">
        <div id="top-left-container" class="col-lg-8 col-md-8 col-sm-8">
            <div class="form-inline row hide">
              <div class="col-md-3">
                  <span><label>Dependencia: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnTraeUnidadesResponsables(this.value, 'selectUnidadNegocio')">
                  </select>
              </div>
            </div>
            <br>
            <div class="form-inline row">
              <div class="col-md-3 pt10">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')">
                  </select>
              </div>
            </div>
            <br>
          <div class="form-inline row">
              <div class="col-md-3 pt10" style="vertical-align: middle;">
                  <span><label>UE: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora"> 
                  </select>
              </div>
          </div>
            <br>
            <div class="obs-container">
              <div class="row">
                  <div class="col-lg-3 col-md-3 col-sm-3 pt20">
                    <label class=""><b>Observaciones:</b> </label>
                  </div>
                  <div class="col-lg-9 col-md-9 col-sm-9">
                    <component-textarea label="Observaciones: " id="txtAreaObs" name="txtAreaObs" rows="2" class="w100p" placeholder="Observaciones"></component-textarea>
                  </div>
              </div>
            </div>
        </div>
        <div id="top-right-container" class="col-lg-4 col-md-4 col-sm-4 pt10">
            <div class="row">
              <label  id="" class="col-lg-3 col-md-3 col-sm-3 pt5">Folio: </label>
              <div class="text-center col-lg-8 col-md-8 col-sm-8">
                <label  id="idtxtNoExitenciaView" class="wA bgc12 mt5 fts22 ftc8 borderGray borderRadius plr10" ></label>
              </div>
              <input type="text" class="hide" id="idtxtNumNoExistencia" name="idtxtNumNoExistencia" value="<?php echo $datoidNoExtist; ?>" />
              <input type="text" class="hide" id="idtxtNoExistencia" name="idtxtNoExistencia" value="<?php echo $datoidNoExtist; ?>"/>
            </div>
            <br>
            <component-date-label label="Fecha Elaboración: " id="idFechaElaboracion" name="fechaElaboracion" placeholder="Fecha Elaboracion"></component-date-label>
            <br>
            <!-- <div id="idContentFechaEntrega" class="row p0 m0">
              <label class="col-lg-3 col-md-3 col-sm-3 pr25" >Fecha Requerida: </label>
              <component-date-feriado class="pr10" id="idFechaEntrega" name="fechaEntrega" placeholder="Fecha de Entrega" title="Fecha de Entrega"></component-date-feriado>
            </div> -->
        </div>
      </div>
    </div>
  </div>
  <div id="main-noExistencia" class="p0 mb20">
    <!-- <div name="divTabla" id="divTabla">
      <div name="divCatalogo" id="divCatalogo"></div>
    </div> -->
  </div>
  <div id="foot-noExistecia" class="m0 p20 text-center borderGray">
    <button type="button" id="idBtnRegresarNE" name="btnRegresarNE" onclick="fnRegresarPanelNoExistencia()" class="btn btn-default botonVerde glyphicon glyphicon-home">Regresar</button>
  </div>
</div>
<?php
require 'includes/footer_Index.inc';
?>