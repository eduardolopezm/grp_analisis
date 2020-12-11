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
$title = "Panel No Existencia";

require 'includes/header.inc';
//$funcion = 2265;
require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';


//Librerias GRID
require 'javascripts/libreriasGrid.inc';
$periodoRequisicion = GetPeriod(date('d/m/Y'), $db);

?>

<script type="text/javascript">
  
  var periodoReq = '<?php echo $periodoRequisicion; ?>';


</script>
<script type="text/javascript" src="javascripts/panel_no_existencias.js?v=<?php echo(rand()); ?>"></script>
<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
<div id="msjValidacion" name="msjValidacion"></div>

<!--Panel Busqueda-->
<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelNoExistencias" aria-expanded="true" aria-controls="collapseOne">
            <b>Criterios de filtrado</b>
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelNoExistencias" name="PanelNoExistencias" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
      <div class="panel-body">
        <div class="col-md-4">
          <div class="form-inline row hide">
              <div class="col-md-3">
                  <span><label>Dependencia: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnTraeUnidadesResponsables(this.value, 'selectUnidadNegocio')"></select>
              </div>
          </div>
          <br>
          <div class="form-inline row">
              <div class="col-md-3 pt10" style="vertical-align: middle;">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio[]" class="form-control selectUnidadNegocio" multiple="multiple" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')">                    
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
                      <option value="-1">Seleccionar...</option>
                  </select>
              </div>
          </div>
        </div>
        
        <div class="col-md-4 pt20">
            <!-- <component-number-label label="Folio de No Existencia:" id="txtNumeroNoExistecia" name="txtNumeroNoExistecia" value=""></component-number-label> -->
            <div class="form-inline row">
              <div class="col-md-3 text-left">
                  <span><label>Folio: </label></span>
              </div>
              <div class="col-md-9">
                  <input class="form-control w100p" type="number" id="txtNumeroNoExistecia" name="txtNumeroNoExistecia" min="0">
              </div>
          </div>
          <div class="form-inline row mt20">
              <div class="col-md-3 col-lg-3 pt10 text-left" style="vertical-align: middle;">
                  <span><label><b> Estatus: </b></label></span>
              </div>
              <div class="col-md-9 col-lg-9">
                  <select id="selEstatusRequisicion" name="selEstatusRequisicion[]" class="form-control selectEstatusNoExistencia" multiple="multiple" data-funcion="2265"></select>
              </div>
          </div>
        </div>
        <div class="col-md-4 pt20">
            <component-date-label id="txtFechaInicio" label="Desde: " value="<?= date("d-m-Y")?>"></component-date-label><br>
            <component-date-label id="txtFechaFin" label="Hasta: " value="<?= date("d-m-Y")?>"></component-date-label>
        </div>
        
      </div>
        <!--<div class="panel-footer">-->
            <component-button type="submit" id="btnBuscarNoExistencia" name="btnBuscarNoExistencia" class="glyphicon glyphicon-search" onclick="return false;" value="Filtrar"></component-button>
            
        <!--</div>-->
    </div>
</div>

<!-- <div class="container">
  <div id="top-cotainer" class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 panel panel-default p0 m0">
      <div class="panel-heading h35" role="tab" id="headingOne">
        <h4 class="panel-title">
          <div class="fl text-left">
            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#idPanelRequisiciones" aria-expanded="true" aria-controls="collapseOne">
              <b>Encabezado</b>
            </a>
          </div>
          <div id="idStatusReq" class="fr text-right hide ftc7">
            <span>Estatus de la Requisición: </span><input type="hidden" id="idperfilusr">
            <label id="statusReq"></label>
          </div>
        </h4>
      </div>
      <div id="idPanelRequisiciones" class="row panel-collapse collapse in ptb5" role="tabpanel" aria-labelledby="headingOne">
        <div id="top-left-container" class="col-lg-8 col-md-8 col-sm-8">
            <div class="form-inline row">
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
              <div class="col-md-3">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')">
                  </select>
              </div>
            </div>
            <br>
          <div class="form-inline row">
              <div class="col-md-3" style="vertical-align: middle;">
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
        <div id="top-right-container" class="col-lg-4 col-md-4 col-sm-4">
            <component-date-label data-periodo ='<?php $periodoRequisicion ?>' label="Fecha Elaboración: " id="idFechaElaboracion" name="fechaElaboracion" placeholder="Fecha Elaboracion"></component-date-label>
            
            <div id="idContentFechaEntrega" class="row p0 m0">
              <label class="col-lg-3 col-md-3 col-sm-3 pr25" >Fecha Requerida: </label>
              <component-date-feriado class="pr10" id="idFechaEntrega" name="fechaEntrega" placeholder="Fecha de Entrega" title="Fecha de Entrega"></component-date-feriado>
            </div>
        </div>
      </div>
    </div>
  </div>
</div> -->
<div name="divTabla" id="divTabla">
    <div name="divCatalogo" id="divCatalogo"></div>
</div>

<?php
require 'includes/footer_Index.inc';
?>
