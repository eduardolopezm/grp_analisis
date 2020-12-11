<?php
/* CREADO POR Desarrollador   09/NOV/2011
17/AGOSTO/2012  - Elimine el campo de proyectos para tener categorias genericas
*/
include 'includes/session.inc';
$title = _('Agregar Datos Random a GLTRANS');
$funcion = 2261;
include 'includes/header.inc';

include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
//Librerias GRID
include 'javascripts/libreriasGrid.inc';
?>
<script type="text/javascript" src="javascripts/agregarGLTRANS.js"></script>
<!-- Nav tabs -->
    <div class="input-group">
      <div id="R1P001"></div>
      <span class="input-group-addon" style="background: none; border: none;"> Cuenta contable a agregar </span>
      <select id="selectTodos" name="selectTodos[]" class="form-control selectTodos"  onchange="fnSeleccionarValorDelReporte()" >
      </select>
    </div>
    <br>

<div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <div class="col-md-4">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Dependencía: </label></span>
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
                  <span><label>UE Gasto: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnSeleccionarValorDelReporte()">
                  </select>
              </div>
          </div>
          <br>
          
          <component-button type="button" id="btnGrabar" name="btnGrabar" onclick="fnGrabarConfiguracionSituacionFinanciera()" value="Agregar información a GLTRANS de la cuenta seleccionada"></component-button>

          <br>
          <div id="gridDatos"> </div>


        
        </div>

      </div>
    </div>
  

      

<div class="tabbable boxed parentTabs">

</div>


<!--
-->

<?php
include 'includes/footer_Index.inc';
?>