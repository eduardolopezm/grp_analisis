<?php
/**
 * Matriz Pagado de Gastos
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
$PageSecurity = 11;
$PathPrefix = './';
$funcion = 2325;

/* INCLUSIÓN DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'includes/SecurityUrl.php');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
$title = traeNombreFuncion($funcion, $db);

# Carga de archivos secundarios
include('includes/header.inc');
require 'javascripts/libreriasGrid.inc';

# función para ocultamiento de dependencia
$ocultaDepencencia = 'hidden';
?>
<script type="text/javascript" src="javascripts/matriz_pagado.js?v=<?= rand();?>"></script>

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
							<!-- <div class="col-md-6">
								<div class="form-inline row">
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
										<select id="selectUnidadEjecutoraFiltro" name="selectUnidadEjecutoraFiltro" class="form-control selectUnidadEjecutora" multiple="multiple">
										</select>
									</div>
								</div>
								<br>

								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>PP: </label></span>
									</div>
									<div class="col-md-9">
										<select id="busquedaPP" name="busquedaPP" class="form-control selectProgramaPresupuestario" multiple="multiple">
										</select>
									</div>
								</div>
								<br>

							</div> -->
							
							<!-- -col-md-4 -->

							<div class="col-md-4">
								<component-number-label label="Partida genérica:" id="busquedaConcepto" name="busquedaConcepto" placeholder="Partida genérica" title="Partida genérica" value="" maxlength=3></component-number-label>
								<br>
							</div>
							<div class="col-md-4">
								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Descripción: </label></span>
									</div>
									<div class="col-md-9" style="text-align: left;">
										<span id="DescripcionPartida">Sin partida seleccionada</span>
									</div>
								</div>
								<br>
							</div>

								<!--<component-text-label label="Descripción:" id="busquedaDesc" name="busquedaDesc" placeholder="Descripción" title="Descripción" value=""></component-text-label>
								<br>-->
							<div class="col-md-4">
								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Tipo de Gasto: </label></span>
									</div>
									<div class="col-md-9">
										<select id="busquedaTipoGasto" name="busquedaTipoGasto" class="form-control selectTipoGasto">
											<option value="-1">Seleccionar...</option>
										</select>
									</div>
								</div>
								<br>
							</div>

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
								<component-button type="button" id="btnBuscar" name="btnBuscar" class="glyphicon glyphicon-search" value="Filtrar" onclick="fnMostrarDatos(true)"></component-button>
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
<div class="modal fade ui-draggable" id="modalUsoGeneral" name="modalUsoGeneral" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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
					<div class="col-md-4">

						<!-- DEPENDENCIA -->
						<!-- crear el componente -->
						<div class="form-inline row hidden">
							<div class="col-md-3">
								<span><label>Dependencia: </label></span>
							</div>
							<div class="col-md-9">
								<select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioDependeciaGeneral('selectRazonSocial', 'selectUnidadNegocio')" multiple="true"></select>
							</div>
						</div>
						<br class="hidden">

						<!-- UR -->
						<!-- crear el componente -->
						<!-- <div class="form-inline row">
							<div class="col-md-3">
								<span><label>UR: </label></span>
							</div>
							<div class="col-md-9">
								<select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio"> 
									<option value="-1">Seleccionar...</option>
								</select>
							</div>
						</div>
						<br> -->

						<!-- UE -->
						<!-- crear el componente -->
						<!-- <div class="form-inline row">
							<div class="col-md-3" style="vertical-align: middle;">
								<span><label>UE: </label></span>
							</div>
							<div class="col-md-9">
								<select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora"> 
									<option value="-1">Seleccionar...</option>
								</select>
							</div>
						</div>
						<br> -->

						<!-- PROGRAMA PRESUPUESTARIO -->
						<!-- crear el componente -->
						<!-- <div class="form-inline row">
							<div class="col-md-3" style="vertical-align: middle;">
								<span><label>PP: </label></span>
							</div>
							<div class="col-md-9">
								<select id="txtpp" name="txtpp" class="form-control selectProgramaPresupuestario"> 
									<option value="-1">Seleccionar...</option>
								</select>
							</div>
						</div>
						<br> -->

						<!-- TIPO DE GASTO -->
						<!-- crear el componente -->
						<div class="form-inline row">
							<div class="col-md-3" style="vertical-align: middle;">
								<span><label>Tipo de Gasto: </label></span>
							</div>
							<div class="col-md-9">
								<select id="nu_tipo_gasto" name="nu_tipo_gasto" class="form-control selectTipoGasto">
									<option value="-1">Seleccionar...</option>
								</select>
							</div>
						</div>
						<br>

						<!-- ESTATUS -->
						<!-- crear el componente -->
						<div class="form-inline row">
							<div class="col-md-3" style="vertical-align: middle;">
								<span><label>Estatus: </label></span>
							</div>
							<div class="col-md-9">
								<select id="ind_activo" name="ind_activo" class="form-control ind_activo">
									<option value="-1">Seleccionar...</option>
									<option value="1">Activo</option>
									<option value="0">Inactivo</option>
								</select>
							</div>
						</div>
						<br>

					</div>

					<div class="col-md-4">

						<!-- PARTIDA GENÉRICA -->
						<!-- crear el componente -->
						<component-number-label label="Partida Genérica:" id="categoryid" name="categoryid" placeholder="Partida Genérica" title="Partida Genérica" maxlength="3" value="" onchange="fnTraeInformacionPartida(this)"></component-number-label>
						<br>

						<!-- DESCRIPCIÓN -->
						<!-- crear el componente -->
						<component-textarea-label label="Descripción: " id="categorydescription" name="categorydescription" placeholder="Descripción" title="Descripción" cols="3" rows="4" maxlength="50" value="" ></component-textarea-label>
						<br>

					</div>

					<div class="col-md-4" id="ColumnaDerecha">

						<!-- CUENTA CARGO -->
						<!-- crear el componente -->
						<component-listado-label label="Cuenta Cargo:" id="stockact" name="stockact" placeholder="Cuenta Cargo"></component-listado-label>
						<br>

						<!-- CUENTA ABONO -->
						<!-- crear el componente -->
						<component-listado-label label="Cuenta Abono:" id="accountegreso" name="accountegreso" placeholder="Cuenta Abono"></component-listado-label>
						<br>
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
