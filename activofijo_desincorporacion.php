<?php

error_reporting(0);

include 'includes/session.inc';
$funcion = 2310;
$title = traeNombreFuncion($funcion, $db);
include 'includes/header.inc';

include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
//Librerias GRID
include 'javascripts/libreriasGrid.inc';
?>
<script type="text/javascript" src="javascripts/activofijo_desincorporacion.js"></script>
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
            <component-date-label label="Fecha inicial de probable desincorporación:*" id="txtFechaInicial" name="txtFechaInicial" placeholder="Fecha Inicial"></component-date-label>
          <br>
          <component-date-label label="Fecha final de probable desincorporación:*" id="txtFechaFinal" name="txtFechaFinal" placeholder="Fecha Final"></component-date-label>
          <br>

          * Fechas futuras pueden corresponder a fechas para anticiparse y buscar los activos que se van a desincorporar en un futuro cercano.
          <br>
          <br>
          
                    
          <br>
        </div>
      </div>
  </div>
  </div>
</div>


                    <component-button type="button" id="btnBuscar" name="btnBuscar" onclick="fnBuscar()" value="Buscar"></component-button>


<ul class="nav nav-tabs">
        <li id="primerset" class="active"><a href="#set1" data-toggle="tab" style="width: '50%'">Crear lote de desincorporación.</a>
        </li>
        <li id="segundoset" ><a href="#set2" data-toggle="tab">Administración (Historial) de activos desincorporados.</a>
        </li>
    </ul>                    

<!--<component-button type="button" id="btnImprimirCONAC2" name="btnImprimirCONAC2" onclick="fnAbrirReporte('PrintSituacionFinanciera.php?PrintPDF=1&reporte=estadodeactividades')" value="Imprimir Reporte de Estado de Actividades"></component-button>
<br>
<component-button type="button" id="btnImprimirCONAC3" name="btnImprimirCONAC3" onclick="fnAbrirReporte('PrintSituacionFinanciera.php?PrintPDF=1&reporte=estadohaciendapublica')" value="Imprimir Reporte de Estado de Hacienda Pública"></component-button>
<br>
<component-button type="button" id="btnImprimirCONAC4" name="btnImprimirCONAC4" onclick="fnAbrirReporte('PrintSituacionFinanciera.php?PrintPDF=1&reporte=estadoanaliticodelactivo')" value="Estado Analítico del Activo"></component-button>
<br>-->

<div class="tab-content">
  <div class="tab-pane fade active in" id="set1" style="width: '50%'">

        <div id="adentro">
        <div id="viewListaDeActivos"  > </div>
        </div>


    <component-button type="button" id="btnDesincorporar" name="btnDesincorporar" onclick="fnDesincorporar()" value="Desincorporar Seleccionados"></component-button>
  </div> <!-- set1 -->

  <div class="tab-pane" id="set2" style="width: '50%'">
    <div id="divTabla2"><div id="divDesincorporacion"></div></div>

<br>
<br>

    <div id="viewReporte"  > </div>




            
  </div> <!-- set2 -->
</div>
<!--
-->

<?php
include 'includes/footer_Index.inc';
?>