<?php
/**
 * Consolidacio bancaria al cargar  datos
 *
 * @category     ABC
 * @package      ap_grp
 * @author       Arturo Lopez Peña  <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 15/02/2018
 * Fecha Modificación: 16/02/2018
 * Carga de estado de cuenta cvs para consolidacion bancaria
 */

$PageSecurity = 5;
require 'includes/session.inc';
$funcion = 2200;
$title= traeNombreFuncion($funcion, $db);
//$title = _('Cargar Estados de Cuenta Bancarios');
require 'includes/header.inc';
require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';
//Librerias GRID
require 'javascripts/libreriasGrid.inc';
?>

<script type="text/javascript" src="javascripts/bankMatchingV4.js"> </script>

<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelCriteriosBusqueda" aria-expanded="true" aria-controls="collapseOne">
            <b>Criterios de filtrado</b>
          </a>
        </div>
      </h4>
    </div>

    <div id="PanelCriteriosBusqueda" name="PanelCriteriosBusqueda" class="panel-collapse collapse in h250" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
      <div class="panel-body">
        <div class="col-md-4">
          <div class="form-inline row">
              <div class="col-md-3" style="vertical-align: middle;">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio[]" class="form-control selectUnidadNegocio" multiple="multiple" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')">                    
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
                      <option value="-1">Seleccionar...</option>
                  </select>
              </div>
          </div>
          
          <div class="form-inline row hide">
              <div class="col-md-3">
                  <span><label>Dependencia: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioDependeciaGeneral('selectRazonSocial', 'selectUnidadNegocio')" multiple="true"></select>
              </div>
          </div>
        </div>
        <div class="col-md-4"> 
          <div  id="banco">
              
          </div>
            <br>
           <!--<div  id="tipotransaccion">
              
          </div>-->
          <div class="col-xs-12 col-md-4 pt20">
                  <span><label>Movimiento: </label></span>
        </div>
         <div class="col-xs-12 col-md-8 pt20">
       
          <select name="Type" id="Type" class="typemov">
         <!-- <option value="*">Todos los movimientos </option>
          <option value="Receipts">Conciliar Ingresos y Depositos</option>
          <option value="Payments">Conciliar Egresos y Pagos </option>-->
          <option value="0">Todos los movimientos </option>
          <option value="1">Conciliados</option>
          <option value="2">No conciliados</option>
          </select>
        
      </div>

        </div>

        <div class="col-md-4">
          <component-date-label label="Desde:" id="dateDesde" name="dateDesde" placeholder="Desde fecha" title="DesdeFecha"></component-date-label>
          <br>
          <component-date-label label="Hasta:" id="dateHasta" name="dateHasta" placeholder="Hasta fecha" title="HastaFecha"></component-date-label>
        </div>
        
      </div>

     <div class="form-inline row col-xs-4 col-md-4">
        
        

    </div>

       


                    <!--f-->
               
                    <!--buscar-->
    </div>
</div><!--fin pannel criterios de busqueda -->

  <div class="col-xs-6 col-md-6 text-right">
                      <br>
                      <component-button id="btnBuscar" name="btnBuscar" value="Filtrar" onclick="" class="glyphicon glyphicon-search"></component-button>
  </div>

<div class="row">  
  <div id="datosConsolidacion">  
  
  </div>

    <div class="col-xs-12 col-md-12 text-center" id="leyendaEstado">  
   
  </div>

  <div id="tablaEstados"> 
      <div id="datosEstados">  </div>
   </div>

<br>  <br>
 <div class="col-xs-12 col-md-12 text-center" id="leyendaMovgrp">  
   
  </div>
    <div id="tablaMov"> 
      <div id="datosMov">  </div>
   </div>
    <div class="col-xs-12 col-md-12" id="btnBoton" style="display: none"> 
      <component-button id="btnCBank" name="btnCBank" value="Conciliar" onclick="" class=""></component-button>
   </div>
</div>
<?php
require 'includes/footer_Index.inc';
?>