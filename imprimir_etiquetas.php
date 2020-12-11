<?php

error_reporting(0);

include 'includes/session.inc';
$funcion = 2309;
$title = traeNombreFuncion($funcion, $db);
include 'includes/header.inc';

include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
//Librerias GRID
include 'javascripts/libreriasGrid.inc';
?>
<script type="text/javascript" src="javascripts/imprimir_etiquetas.js"></script>
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
        <div class="col-md-6">
         
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Dependencia: </label></span>
              </div>
              <div class="col-md-9"> 
                  <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioRazonSocial()">
                    <option value='0'>Seleccionar...</option>
                  </select>
              </div>
          </div>
          <br>
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadNegocio()">
                  </select>
              </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-inline row">
            <component-date-label label="Fecha de alta de activo inicial: " id="txtFechaInicial" name="txtFechaInicial" placeholder="Fecha Inicial"></component-date-label>
          <br>
          <component-date-label label="Fecha de alta de activo final: " id="txtFechaFinal" name="txtFechaFinal" placeholder="Fecha Final"></component-date-label>
          <br>
          
                    <component-button type="button" id="btnImprimir" name="btnImprimir" onclick="fnAbrirReporte()" value="Imprimir"></component-button>
          <br>
        </div>
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

<div id="adentro">
<div id="viewReporte"  > </div>
</div>

<!--
-->

<?php
include 'includes/footer_Index.inc';
?>