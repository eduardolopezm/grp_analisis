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
$funcion = 26;

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
<script type="text/javascript" src="javascripts/abcContribuyente.js?v=<?= rand();?>"></script>


<style>
	#ModalGeneral{
		z-index: 9999;
    	background: rgba(0, 0, 0, 0.35);
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
							<div class="col-md-4">
								<!-- <component-text-label label="A. Paterno/Razón Social:" id="paternRazonFiltro" name="paternRazonFiltro" placeholder="A. Paterno/Razón Social" title="A. Paterno/Razón Social" value=""></component-text-label>
								<br>
								<component-text-label label="A. Materno" id="maternoFiltro" name="maternoFiltro" placeholder="A. Materno" title="A. Materno" value=""></component-text-label>
								<br> -->
								<component-text-label label="IC" id="txtIcFiltro" name="txtIcFiltro" placeholder="IC" title="IC" value=""></component-text-label>
								<br>
								<component-text-label label="Apellido/Razón Social" id="apellidoFiltro" name="apellidoFiltro" placeholder="Apellido/Razón Social" title="Apellido/Razón Social" value=""></component-text-label>
								<br>
								<component-text-label label="Nombre" id="nombresFiltro" name="nombresFiltro" placeholder="Nombre(s)" title="Nombre(s)" value=""></component-text-label>
							</div><!-- -col-md-4 -->
							<div class="col-md-4">
								<!-- <component-text-label label="País" id="paisFiltro" name="paisFiltro" placeholder="País" title="País" value=""></component-text-label> -->
								<component-text-label label="RFC" id="rfcFiltro" name="rfcFiltro" placeholder="RFC" title="RFC"></component-text-label>
								<br>
								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>País: </label></span>
									</div>
									<div class="col-md-9">
										<select id="selectPaisFiltro" name="selectPaisFiltro" class="form-control selectPais" onchange="fnEstadoFuncion('Filtro')">
											<option value="-1">Sin Seleccionar</option>
										</select>
									</div>
								</div>
								
								<br>
								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Estado: </label></span>
									</div>
									<div class="col-md-9">
										<select id="selectEstadoFiltro" name="selectEstadoFiltro" class="form-control selectEstado" onchange="fnRegionFuncion('Filtro')">
											<option value="-1">Sin Seleccionar</option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Región: </label></span>
									</div>
									<div class="col-md-9">
										<select id="selectRegionFiltro" name="selectRegionFiltro" class="form-control selectRegion">
											<option value="-1">Seleccione un estado</option>
										</select>
									</div>
								</div>
								<br>
								<component-text-label label="Distrito" id="distritoFiltro" name="distritoFiltro" placeholder="Distrito" title="Distrito" value=""></component-text-label>
								<br>
								<component-text-label label="Población" id="poblacionFiltro" name="poblacionFiltro" placeholder="Población" title="Población" value=""></component-text-label>
							</div>
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
						<component-number-label label="Clave:" id="debtorno" name="debtorno" placeholder="Clave" title="Clave" value="" readonly></component-number-label>
						<br>
                        <div class="form-inline row">
                            <div class="col-md-3" style="vertical-align: middle;">
                                <span><label>Tipo De Persona: </label></span>
                            </div>
                            <div class="col-md-9">
                                <select id="typePerso" name="typePerso" class="form-control TaxProvince" onchange="fnTipoFuncion()">
                                    <option value="-1">Sin Seleccionar</option>
                                    <option value="Fisica">Fisica</option>
                                    <option value="Moral">Moral</option>
                                </select>
                            </div>
                        </div>
                        <br>
							<div class="form-inline row">
								<div class="col-md-3 col-xs-12"><span><label class="labelPater">A. Paterno/Razón Social:</label></span></div>
								<div class="col-md-9 col-xs-12">
									<input type="text" id="paternRazon" name="paternRazon" placeholder="A. Paterno/Razón Social" title="A. Paterno/Razón Social" onkeyup="" onkeypress="" maxlength="100" onpaste="return false" class="form-control" style="width: 100%;">
								</div>
							</div>
                            <!-- <component-text-label label="A. Paterno/Razón Social:" id="paternRazon" name="paternRazon" placeholder="A. Paterno/Razón Social" title="A. Paterno/Razón Social" value=""></component-text-label> -->
                        <br>
							<div style="margin-bottom: 20px;" class="form-inline row contentMaterno"><div class="col-md-3 col-xs-12"><span><label>A. Materno</label></span></div> <div class="col-md-9 col-xs-12"><input type="text" id="materno" name="materno" placeholder="A. Materno" title="A. Materno" onkeyup="" onkeypress="" maxlength="100" onpaste="return false" class="form-control" style="width: 100%;" autocomplete="off"></div></div>
							<div style="margin-bottom: 20px;" class="form-inline row contentNombres"><div class="col-md-3 col-xs-12"><span><label>Nombre(s)</label></span></div> <div class="col-md-9 col-xs-12"><input type="text" id="nombres" name="nombres" placeholder="Nombre(s)" title="Nombre(s)" onkeyup="" onkeypress="" maxlength="100" onpaste="return false" class="form-control" style="width: 100%;" autocomplete="off"></div></div>
                            <component-text-label label="RFC" id="rfc" name="rfc" placeholder="RFC" title="RFC" value="XXXX010101XXX"></component-text-label>
                        <br>
                        <!-- <div class="form-inline row">
							<div class="col-md-3" style="vertical-align: middle;">
								<span><label>Régimen fiscal:</label></span>
							</div>
							<div class="col-md-9">
								<select id="selectRegimenFiscal" name="selectRegimenFiscal" class="form-control selectRegimenFiscal"> 
									<option value="-1">Seleccionar...</option>
								</select>
							</div>
						</div>
                        <br> -->
                        <div class="form-inline row">
							<div class="col-md-3" style="vertical-align: middle;">
								<span><label>Uso de CFDI:</label></span>
							</div>
							<div class="col-md-9">
								<select id="selectCFDI" name="selectCFDI" class="form-control selectCFDI"> 
									<option value="-1">Seleccionar...</option>
								</select>
							</div>
						</div>
                        <br>
                            <component-text-label label="Num. Registro" id="numRegistro" name="numRegistro" placeholder="Num. Registro" title="Num. Registro" value=""></component-text-label>
                        <br>

                        <div class="form-inline row">
                            <div class="col-md-3" style="vertical-align: middle;">
                                <span><label>Comprobante Físcal: </label></span>
                            </div>
                            <div class="col-md-9">
                                <select id="reqComprobante" name="reqComprobante" class="form-control TaxProvince">
                                    <option value="-1">Sin Seleccionar</option>
                                    <option value="1">SI</option>
                                    <option value="0">NO</option>
                                </select>
                            </div>
                        </div>
                        <br>
                            <component-text-label label="Email" id="email" name="email" placeholder="Email" title="Email" value=""></component-text-label>
                        <br>

						<div class="form-inline row">
                            <div class="col-md-3" style="vertical-align: middle;">
                                <span><label>Tipo de dirección: </label></span>
                            </div>
                            <div class="col-md-9">
                                <select id="tipoDir" name="tipoDir" class="form-control TaxProvince">
                                    <option value="-1">Sin Seleccionar</option>
                                    <option value="Foránea">Foránea</option>
                                    <option value="Local">Local</option>
                                </select>
                            </div>
                        </div>

					</div>
					<div class="col-md-6 col-xs-12">
						<div class="form-inline row">
							<div class="col-md-3" style="vertical-align: middle;">
								<span><label>País: </label></span>
							</div>
							<div class="col-md-9">
								<select id="selectPais" name="selectPais" class="form-control selectPais" onchange="fnEstadoFuncion('')">
									<option value="-1">Sin Seleccionar</option>
								</select>
							</div>
						</div>
							<!-- <component-text-label label="Pais" id="pais" name="pais" placeholder="Pais" title="Pais" value=""></component-text-label> -->
                        <br>
						<div class="contentStateMX">
							<div class="form-inline row">
								<div class="col-md-3" style="vertical-align: middle;">
									<span><label>Estado: </label></span>
								</div>
								<div class="col-md-9">
									<select id="selectEstado" name="selectEstado" class="form-control selectEstado" onchange="fnRegionFuncion('')">
										<option value="-1">Sin Seleccionar</option>
									</select>
								</div>
							</div>
							<br>
							<div class="form-inline row">
								<div class="col-md-3" style="vertical-align: middle;">
									<span><label>Region: </label></span>
								</div>
								<div class="col-md-9">
									<select id="selectRegion" name="selectRegion" class="form-control selectRegion">
										<option value="-1">Seleccione un estado</option>
									</select>
								</div>
							</div>
						</div>
						<div class="contentState hidden">
							<component-text-label label="Estado:" id="estado" name="estado" placeholder="Estado" title="Estado" value=""></component-text-label>
							<br>
							<component-text-label label="Región:" id="region" name="region" placeholder="Región" title="Región" value=""></component-text-label>
						</div>
						<br>
						<component-text-label label="Distrito" id="distrito" name="distrito" placeholder="Distrito" title="Distrito" value=""></component-text-label>
                        <br>
						<component-text-label label="Población" id="poblacion" name="poblacion" placeholder="Población" title="Población" value=""></component-text-label>
                        <br>
						<component-text-label label="Calle" id="calle" name="calle" placeholder="Calle" title="Calle" value=""></component-text-label>
                        <br>
						<div class="form-inline row">
							<div class="col-md-3 col-xs-12"><span><label  style="margin-top: 4px;">No. Exterior</label></span></div>
							<div class="col-md-3 col-xs-12">
								<input type="text" id="numExt" name="numExt" placeholder="No. Exterior" title="No. Exterior" onkeyup="" onkeypress="" maxlength="100" onpaste="return false" class="form-control" style="width: 100%;" autocomplete="off">
							</div>
							<div class="col-md-3 col-xs-12"><span><label  style="margin-top: 4px;">No. Interior</label></span></div>
							<div class="col-md-3 col-xs-12">
								<input type="text" id="numInt" name="numInt" placeholder="No. Interior" title="No. Interior" onkeyup="" onkeypress="" maxlength="100" onpaste="return false" class="form-control" style="width: 100%;">
							</div>
						</div>
						<!-- <component-text-label label="No. Exterior" id="numExt" name="numExt" placeholder="No. Exterior" title="No. Exterior" value=""></component-text-label> -->
                        <!-- <br>
						<component-text-label label="No. Interior" id="numInt" name="numInt" placeholder="No. Interior" title="No. Interior" value=""></component-text-label> -->
                        <br>
						<component-text-label label="C.P" id="cp" name="cp" placeholder="C.P" title="C.P" value=""></component-text-label>
                        <br>
						<div class="form-inline row">
							<div class="col-md-3 col-xs-12"><span><label>Teléfono Fijo</label></span></div>
							<div class="col-md-5 col-xs-12">
								<input type="text" id="telefono" name="telefono" placeholder="Telefono Fijo" title="Telefono Fijo" onkeyup="" onkeypress="" maxlength="100" onpaste="return false" class="form-control" style="width: 100%;" autocomplete="off">
							</div>
							<div class="col-md-1 col-xs-12"><span><label style="margin-top: 4px;">Ext</label></span></div>
							<div class="col-md-3 col-xs-12">
								<input type="text" id="ext" name="ext" placeholder="Extensión" title="Extensión" onkeyup="" onkeypress="" maxlength="100" onpaste="return false" class="form-control" style="width: 100%;" autocomplete="off">
							</div>
						</div>
						
						<!-- <component-text-label label="Telefono Fijo" id="telefono" name="telefono" placeholder="Telefono Fijo" title="Telefono Fijo" value=""></component-text-label> -->
                        <!-- <br>
						<component-text-label label="Extensión" id="ext" name="ext" placeholder="Extensión" title="Extensión" value=""></component-text-label> -->
                        <br>
						<component-text-label label="Teléfono Movil" id="movil" name="movil" placeholder="Teléfono Movil" title="Teléfono Movil" value=""></component-text-label>
                        <br>
                        <div class="form-inline row">
                            <div class="col-md-3" style="vertical-align: middle;">
                                <span><label>Estatus: </label></span>
                            </div>
                            <div class="col-md-9">
                                <select id="activo" name="activo" class="form-control TaxProvince">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
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
