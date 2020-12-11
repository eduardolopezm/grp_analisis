<?php
/**
 * ABC de Almacén
 *
 * @category	panel
 * @package		ap_grp
 * @author		Jonathan Cendejas Torres <[<email address>]>
 * @license		[<url>] [name]
 * @version		GIT: [<description>]
 * Fecha Creación: 25/09/2017
 * Fecha Modificación: 25/09/2017
 * Se realizan operación pero el Alta, Baja y Modificación. Mediante las validaciones creadas para la operación seleccionada
 */

/* DECLARACION DE VARIABLES */
$PageSecurity = 8;
$PathPrefix = './';
$funcion = 2504;
 
/* INCLUSIÓN DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/SecurityUrl.php');
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
$title = traeNombreFuncion($funcion, $db);

# Carga de archivos secundarios
include('includes/header.inc');
require 'javascripts/libreriasGrid.inc';

# función para ocultamiento de dependencia
$ocultaDepencencia = 'hidden';
?>

<!-- <link rel="stylesheet" href="css/listabusqueda.css" />
<script type="text/javascript" src="lib/bootstrap/js/3.3.6/bootstrap.min.js"></script>
<link rel="stylesheet" href="css/v3/librerias/jquery-ui/jquery-ui-1.11.4/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="css/v3/librerias/bootstrap-3.3.7/css/bootstrap.min.css"> -->
<script type="text/javascript" src="javascripts/abcObjetoPrincipal.js?v=<?= rand();?>"></script>

<style>
	#ModalGeneral{
		z-index: 9999 !important;
    	background: rgba(0, 0, 0, 0.35) !important;
	}

</style>

<!-- Filtros -->
<div class="row">
	<div class="panel-group">
		<div class="panel panel-default">
			<div class="panel-heading h35">
				<h4 class="panel-title">
					<!-- <a data-toggle="collapse" href="#closeTab"> <strong>CRITERIOS DE BÚSQUEDA</strong> </a> -->
					<div class="fl text-left">
						<a role="button" data-toggle="collapse" data-parent="#accordion" href="#closeTab" aria-expanded="true" aria-controls="collapseOne">
							<b>Filtros de Búsqueda</b>
						</a>
					</div>
				</h4>
			</div><!-- .panel-heading -->
			<div id="closeTab" class="panel-collapse collapse in">
				<div class="panel-body">
					<form id="frmFiltroActivos">
						<div class="row">
							<!-- UR, UE -->
							<div class="col-md-6">
								<!-- <div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>UR: </label></span>
									</div>
									<div class="col-md-9">
										<select id="selectUnidadNegocioFiltro" name="selectUnidadNegocioFiltro" class="form-control selectUnidadNegocio" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutoraFiltro')">
											<option value="-1">Seleccionar...</option>
										</select>
									</div>
								</div>
								<br>

								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>UE: </label></span>
									</div>
									<div class="col-md-9">
										<select id="selectUnidadEjecutoraFiltro" name="selectUnidadEjecutoraFiltro" class="form-control selectUnidadEjecutora">
											<option value="-1">Seleccionar...</option>
										</select>
									</div>
								</div>
								<br> -->

								<component-number-label label="Clave:" id="busquedaClave" name="busquedaClave" placeholder="Clave" title="Clave" value="" maxlength=6></component-number-label>
								<br>

							</div><!-- -col-md-4 -->

							<div class="col-md-6">

								<component-text-label label="Descripción:" id="busquedaDesc" name="busquedaDesc" placeholder="Descripción" title="Descripción" value=""></component-text-label>
								<br>

								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Estatus: </label></span>
									</div>
									<div class="col-md-9">
										<select id="busquedaStatus" name="busquedaStatus" class="form-control TaxProvince">
											<option value="-1">Sin Seleccionar</option>
											<option value="Activo">Activo</option>
											<option value="Inactivo">Inactivo</option>
										</select>
									</div>
								</div>
								<br>

								<!--<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Línea: </label></span>
									</div>
									<div class="col-md-9">
										<select id="lineaDesc" name="lineaDesc" class="form-control lineaDesc">
											<option value="-1">Seleccionar...</option>
										</select>
									</div>
								</div>
								<br>-->
							</div><!-- -col-md-4 -->

							<!--<div class="col-md-4">-->
								<!-- CUENTA CARGO -->
								<!-- crear el componente -->
								<!--<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Cuenta Cargo: </label></span>
									</div>
									<div class="col-md-9">
										<select id="StockAct" name="StockAct" class="form-control stockact">
											<option value="-1">Seleccionar...</option>
										</select>
									</div>
								</div>
								<br>-->

								<!-- CUENTA ABONO -->
								<!-- crear el componente -->
								<!--<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Cuenta Abono: </label></span>
									</div>
									<div class="col-md-9">
										<select id="AccountEgreso" name="AccountEgreso" class="form-control accountegreso">
											<option value="-1">Seleccionar...</option>
										</select>
									</div>
								</div>
								<br>-->

								<!--<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Estatus: </label></span>
									</div>
									<div class="col-md-9">
										<select id="busquedaEstatus" name="busquedaEstatus" class="form-control busquedaEstatus">
											<option value="-1" selected>Seleccionar...</option>
											<option value="1">Activo</option>
											<option value="2">Inactivo</option>
										</select>
									</div>
								</div>
								<br>-->
							<!--</div>--><!-- -col-md-4 -->
						</div>

						<!-- Botones -->
						<br>
						<div class="row">
							<div class="col-xs-12">
								<component-button type="button" id="btnBuscar" name="btnBuscar" class="glyphicon glyphicon-search" value="Filtrar" onclick="fnMostrarDatos()"></component-button>
							</div>
						</div>
					</form>
				</div><!-- .panel-body -->
			</div><!-- .panel-collapse -->
		</div><!-- .panel -->
	</div><!-- .panel-group -->
</div><!-- / Encabezado --> 

<!-- tabla de busqueda -->
<div class="row">
	<div name="contenedorTabla" id="contenedorTabla">
		<div name="tablaGrid" id="tablaGrid"></div>
	</div>
</div><!-- .row -->

<!-- botones de accion -->
<div class="row pt10">
	<div class="panel panel-default">
		<div class="panel-body" align="center">
			<component-button type="button" id="nuevo" class="glyphicon glyphicon-copy" value="Nuevo"></component-button>
		</div>
	</div>
</div><!-- .row -->

<!-- Modal Agregar/Modificar -->
<div class="modal fade" id="modalUsoGeneral" name="modalUsoGeneral" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><!-- <div class="modal fade ui-draggable" id="modalUsoGeneral" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog"> -->
	<div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam" style="width: 90%">
		<div class="modal-content">
			<div class="navbar navbar-inverse navbar-static-top">
				<!--Contenido Encabezado-->
				<div class="col-md-12 menu-usuario">
					<span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
				</div>
				<div id="navbar" class="navbar-collapse collapse">
					<div class="nav navbar-nav">
						<div class="title-header">
							<div id="tituloModal" name="tituloModal"></div>
						</div>
					</div>
				</div>
				<div class="linea-verde"></div>
			</div>
			<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
			<div class="modal-body OverdeSelectsenModales" id="forma" name="forma">
				<div class="row">
					<!--Mensaje o contenido-->
					<div id="msjValidacion" name="msjValidacion"></div>
					<div class="col-md-6 col-xs-12">
						<!-- DEPENDENCIA -->
						<!-- crear el componente -->
						<div class="form-inline row hidden">
							<div class="col-md-3">
								<span><label>Dependencia: </label></span>
							</div>
							<div class="col-md-9">
								<select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioDependeciaGeneral('selectRazonSocial', 'tag')" multiple="true"></select>
							</div>
						</div>
						<br class="hidden">
						<input class="hidden" id="type" name="type" value="ObjetoPrincipal">

						<!-- UR -->
						<!-- crear el componente cat&amp;#1072;logo -->
						<div class="form-inline row">
							<div class="col-md-3">
								<span><label>UR: </label></span>
							</div>
							<div class="col-md-9">
								<select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora')">  
									<option value="00">Seleccionar...</option>
								</select>

							</div>
						</div>
						<br>

						<!-- UE -->
						<!-- crear el componente -->
						<div class="form-inline row">
							<div class="col-md-3" style="vertical-align: middle;">
								<span><label>UE: </label></span>
							</div>
							<div class="col-md-9">
								<select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora"> 
									<option value="00">Seleccionar...</option>
								</select>
							</div>
						</div>
						
						<!-- <div class="col-md-4">
						<div class="form-inline row">
							<div class="col-md-3">
								<span><label>UR: </label></span>
							</div>
							<div class="col-md-9">
								<select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora')">
								</select>
							</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-inline row">
							<div class="col-md-3">
								<span><label>UE: </label></span>
							</div>
							<div class="col-md-9">
								<select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" >
								</select> 
							</div>
						</div>
						</div> -->
						<br>
						<component-text-label label="Clave:" id="LocCode" name="LocCode" placeholder="Clave" title="Clave" maxlength="6" value=""></component-text-label>
						<br>
						<component-text-label label="Descripción:" id="LocationName" name="LocationName" placeholder="Nombre" title="Nombre" value=""></component-text-label>
						<br>
						<div class="form-inline row">
							<div class="col-md-3" style="vertical-align: middle;">
								<span><label>Estatus: </label></span>
							</div>
							<div class="col-md-9">
								<select id="status" name="status" class="form-control TaxProvince">
									<option value="Activo">Activo</option>
									<option value="Inactivo">Inactivo</option>
								</select>
							</div>
						</div>
						
					</div>

					<div class="col-md-6 col-xs-12">
						<div class="form-inline row">
							<div class="col-md-12 col-xs-12">
								<div class="col-md-3 col-xs-12"><span><label>Producto:</label></span></div>
								<div class="col-md-5 col-xs-12">
									<input type="text" id="txtNombre" name="txtNombre" placeholder="Producto/Servicio" title="" onkeyup="" onkeypress="" maxlength="100" onpaste="return false" class="form-control" style="width: 100%;" autocomplete="off" value="93161702">
								</div>
								<div class="col-md-4 col-xs-12">
									<button id="btnCheck" name="btnCheck" type="button" title="" onclick="fnTraeInformacionProducto()" class="glyphicon glyphicon-search btn btn-default botonVerde" style="font-weight: bold;" autocomplete="off">&nbsp;Compobar</button>
								</div>
							</div>
						</div>
						<!-- <component-text-label label="Producto: "   name='txtNombre' id='txtNombre'  placeholder="Producto/Servicio" value="<?php echo $_POST['txtNombre'] ;?>"></component-text-label>
						<component-button type="button" id="btnCheck" name="btnCheck" class="glyphicon glyphicon-search" value="Compobar" onclick="fnTraeInformacionProducto()"></component-button> -->
								
						<br>

						<div class="form-inline row">
							<div class="col-md-3" style="vertical-align: middle;">
								<span><label>Descripción: </label></span>
							</div>
							<div class="col-md-9" style="text-align: left;">
								<span id="productDescription">Sin Producto/Servicio seleccionado</span>
							</div>
						</div>

						<input type="hidden" id="stockID">
						<br>
						<!-- crear el componente -->
						<!-- <div class="form-inline row">
							<div class="col-md-3" style="vertical-align: middle;">
								<span><label>Producto/Servicio: </label></span>
							</div>
							<div class="col-md-9">
								<select id="selectStock" name="selectStock" class="form-control selectStock"> 
									<option value="-1">Seleccionar...</option>
								</select>
							</div>
						</div>
						<br> -->
						<!-- crear el componente -->
						<br>
						<div class="form-inline row">
							<div class="col-md-3" style="vertical-align: middle;">
								<span><label>Unidad de Medida: </label></span>
							</div>
							<div class="col-md-9">
								<select id="selectUnidad" name="selectUnidad" class="form-control selectUnidad"> 
									<option value="-1">Seleccionar...</option>
								</select>
							</div>
						</div>
						<br>
						<!-- crear el componente -->
						<div class="form-inline row">
							<div class="col-md-3" style="vertical-align: middle;">
								<span><label>Método de Pago: </label></span>
							</div>
							<div class="col-md-9">
								<select id="selectPago" name="selectPago" class="form-control selectPago"> 
									<option value="-1">Seleccionar...</option>
								</select>
							</div>
						</div>
						<br>
						<component-text-label label="Leyenda:" id="leyenda" name="leyenda" placeholder="Leyenda" title="Leyenda" value=""></component-text-label>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn botonVerde" id="guardar">Guardar</button>
				<button type="button" class="btn botonVerde" data-dismiss="modal">Cancelar</button>
			</div>
		</div>
	</div>
</div>

<?php require 'includes/footer_Index.inc'; ?>
