<?php
/**
 * panel de activo fijo
 *
 * @category ABC 
 * @package ap_grp
 * @author Jorge Cesar Garcia Baltazar <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/07/2017
 * Fecha Modificación: 31/07/2017
 * Se realizan operación pero el Alta, Baja y Modificación
 */

$PageSecurity=5;
include('includes/session.inc');
$funcion=2307;
$title = traeNombreFuncion($funcion, $db);
//$title="Alta de Bienes Patrimoniales";
include('includes/header.inc');
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

//Librerias GRID
include('javascripts/libreriasGrid.inc');
?>

<script type="text/javascript" src="javascripts/activofijo_panel.js"></script>


<div id="OperacionMensaje" name="OperacionMensaje"></div>

<!-- Filtros -->
<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading h35">
                <h4 class="panel-title">
                    <!-- <a data-toggle="collapse" href="#closeTab"> <strong>CRITERIOS DE BÚSQUEDA</strong> </a> -->
                    <div class="fl text-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#closeTab" aria-expanded="true" aria-controls="collapseOne">
                            <b>Criterios de Filtrado</b>
                        </a>
                    </div>
                </h4>
            </div><!-- .panel-heading -->
            <div id="closeTab" class="panel-collapse collapse in">
                <div class="panel-body">
                	<form id="frmFiltroActivos">
                		<div class="row">
                			<!-- UR, UE -->
				            <div class="col-md-4">
				                <div class="form-inline row">
				                  <div class="col-md-3" style="vertical-align: middle;">
				                    <span><label>UR: </label></span>
				                  </div>
				                  <div class="col-md-9">
				                    <select id="selectUnidadNegocio" name="selectUnidadNegocio[]" class="form-control selectUnidadNegocio" multiple="multiple" data-todos="true"></select>
				                  </div>
				                </div>

				                <br>

				                <div class="form-inline row">
				                  <div class="col-md-3" style="vertical-align: middle;">
				                    <span><label>UE: </label></span>
				                  </div>
				                  <div class="col-md-9">
				                    <select id="selectUnidadEjecutora" name="selectUnidadEjecutora[]" class="form-control selectUnidadEjecutora" multiple="multiple" data-todos="true" ></select>
				                  </div>
				                </div>
				                <br>
				                <!-- form-inline -->
				                  <div class="form-inline row">
				                    <div class="col-md-3">
				                      <span><label>Estatus: </label></span>
				                    </div>
				                    <div class="col-md-9">
				                      <select id="selectEstatusActivo" name="selectEstatusActivo[]" class="form-control selectEstatusActivo" multiple="multiple" data-todos="true" >
				                        <option value="1">Activo</option>                   
				                        <option value="0">Inactivo</option>                   
				                      </select>
				                    </div>
				                  </div><!-- form-inline -->

				            </div><!-- -col-md-4 -->

				            <!-- Empleado, Folio -->
				            <div class="col-md-4">
				                <div class="form-inline row">
				                  <div class="col-md-3" style="vertical-align: middle;">
				                    <span><label>Número de Inventario: </label></span>
				                  </div>
				                  <div class="col-md-9">
				                    <select id="selectPatrimonio" name="selectPatrimonio[]" class="form-control selectPatrimonioGeneral" multiple="multiple" data-todos="true" ></select>
				                  </div>
				                </div>

				            	<br>

				          		<component-text-label label="Descripción:" id="txtDescripcion" name="txtDescripcion" placeholder="Descripción de Activo" title="Descripción de Activo" value=""></component-text-label>

				          		<br>

				                <div class="form-inline row">
				                  <div class="col-md-3">
				                      <span><label>Tipo de Bien: </label></span>
				                  </div>
				                  <div class="col-md-9"> 
				                      <select id="selectTipoBien" name="selectTipoBien[]" class="form-control selectTipoBien" required="" multiple="multiple" ata-todos="true">
				                      </select>
				                  </div>
				                </div>
              
				            </div><!-- -col-md-4 -->

				            <div class="col-md-4">
				            	<div class="form-inline row">
				                  <div class="col-md-3" style="vertical-align: middle;">
				                    <span><label>Partida Específica: </label></span>
				                  </div>
				                  <div class="col-md-9">
				                    <select id="selectCategoriaActivo" name="selectCategoriaActivo[]" class="form-control selectCategoriaActivo" multiple="multiple" data-todos="true" ></select>
				                  </div>
				                </div>
				            	<br>
				            	<div class="form-inline row">
				                  <div class="col-md-3" style="vertical-align: middle;">
				                    <span><label>Condición: </label></span>
				                  </div>
				                  <div class="col-md-9">
				                    <select id="selectEstatusPatrimonio" name="selectEstatusPatrimonio[]" class="form-control selectEstatusPatrimonio" multiple="multiple" data-todos="true" ></select>
				                  </div>
				                </div>
				                <br>
				                <div class="form-inline row">
				                  <div class="col-md-3">
				                    <span><label>Almacén: </label></span>
				                  </div>
				                  <div class="col-md-9"> 
				                    <select id="selectAlmacen" name="selectAlmacen[]" class="form-control" multiple="multiple" data-todos="true">
				                    </select>
				                  </div>
				                </div><!-- form-inline -->

				            </div>
				            <!-- Fechas -->
			              	<!-- <div class="col-md-4">
				                <component-date-label label="Desde:" id="txtFechaInicial" name="txtFechaInicial" placeholder="Desde" title="Desde" value="<?= date('d-m-Y');?>"></component-date-label>
				                <br>
				                <component-date-label label="Hasta:" id="txtFechaFinal" name="txtFechaFinal" placeholder="Hasta" title="Hasta" value="<?= date('d-m-Y');?>"></component-date-label>
			              	</div> --><!-- -col-md-4 -->
                		</div>

                		<!-- Botones -->
                		<br>
			            <div class="row">
			              <div class="col-xs-12">
			                <component-button  type="button" id="btnBuscar" name="btnBuscar" class="glyphicon glyphicon-search" value="Filtrar" onclick="fnMostrarDatos()"></component-button>
			              </div>
			            </div>
                	</form>
                </div><!-- .panel-body -->
            </div><!-- .panel-collapse -->
        </div><!-- .panel -->
    </div><!-- .panel-group -->
</div><!-- / Encabezado -->

<!-- Tabla de contenido -->
<div class="row">
	<div name="divTabla" id="divTabla">
	  <div id="divCatalogo" name="divCatalogo"></div>
	</div>
</div> <!-- / Tabla de contenido -->
<br>
<!-- Botones -->
<div class="row">
	<div class="panel panel-default">
	    <div class="panel-body" align="center" id="divBotones" name="divBotones">
	    	<component-button type="button" id="btnAgregar" name="btnAgregar" onclick="window.location.href = 'activofijo.php'" value="Nuevo" class="glyphicon glyphicon-plus"></component-button>
	    </div>
	</div> 
</div><!-- / Botones -->

<div name="esconde" id="esconde">

</div>

<?php
include 'includes/footer_Index.inc';
