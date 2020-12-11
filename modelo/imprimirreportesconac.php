<?php
/**
 * Panel Visualizar Reportes
 *
 * @category Panel
 * @package ap_grp
 * @author Desarrollo <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Visualizar reportes conac y ldf
 */

error_reporting(0);

include 'includes/session.inc';
$funcion = 2261;
$title = traeNombreFuncion($funcion, $db);
include 'includes/header.inc';

include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
//Librerias GRID
include 'javascripts/libreriasGrid.inc';
?>
<script type="text/javascript" src="javascripts/imprimirreportesconac.js?v=<?= rand();?>"></script>
<!-- Nav tabs -->


<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
            Filtros de Búsqueda
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
                  <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioRazonSocial()">
                    <option value='0'>Seleccionar...</option>
                  </select>
              </div>
          </div>
          <!-- <br> -->
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadNegocio(), fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora')">
                  </select>
              </div>
          </div>
          <br>
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>UE: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadEjecutora" name="selectUnidadEjecutora[]" class="form-control selectUnidadEjecutoraAAA">
                  </select>
              </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Año del reporte: </label></span>
              </div>
              <div class="col-md-9">
                <select id="selectAnio" name="selectAnio[]" class="form-control selectAnio">
                </select>
              </div> 
          </div>
          <br>
          <div class="form-inline row">
            <div class="col-md-3">
                  <span><label>Reporte: </label></span>
            </div>
            <div class="col-md-9">
                  <select id="selectReportes" name="selectReportes" class="form-control selectReportes" >
                  </select>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <component-date-label label="Fecha Inicial: " id="txtFechaInicial" name="txtFechaInicial" placeholder="Fecha Inicial" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
          <br>
          <component-date-label label="Fecha Final: " id="txtFechaFinal" name="txtFechaFinal" placeholder="Fecha Final" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
        </div>
        <div class="row"></div>
        <div align="center">
          <br>
          <component-button type="button" id="btnImprimir" name="btnImprimir" onclick="fnAbrirReporte()" value="Ver Reporte"></component-button>
        </div>
      </div>
  </div>
</div>

<!--<component-button type="button" id="btnImprimirCONAC2" name="btnImprimirCONAC2" onclick="fnAbrirReporte('PrintSituacionFinanciera.php?PrintPDF=1&reporte=estadodeactividades')" value="Imprimir Reporte de Estado de Actividades"></component-button>
<br>
<component-button type="button" id="btnImprimirCONAC3" name="btnImprimirCONAC3" onclick="fnAbrirReporte('PrintSituacionFinanciera.php?PrintPDF=1&reporte=estadohaciendapublica')" value="Imprimir Reporte de Estado de Hacienda Pública"></component-button>
<br>
<component-button type="button" id="btnImprimirCONAC4" name="btnImprimirCONAC4" onclick="fnAbrirReporte('PrintSituacionFinanciera.php?PrintPDF=1&reporte=estadoanaliticodelactivo')" value="Estado Analítico del Activo"></component-button>
<br>-->

<div id="adentro" style="margin-bottom: 28%;">
<div id="viewReporte">
    <iframe data="" id="objectContent" width="100%" height="800px" onload="" type="application/pdf" class="hidden">
        <!-- <embed src="" id="embedContent" type="application/pdf" /> -->
    </iframe>
</div>
</div>

<?php
include 'includes/footer_Index.inc';
?>
