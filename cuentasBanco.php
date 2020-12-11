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
$funcion = 94;

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
<script type="text/javascript" src="javascripts/cuentasBanco.js?v=<?= rand();?>"></script>


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
							<div class="col-md-4">
								<div class="form-inline row">
									<div class="col-md-3">
										<span><label>Codigo GL Cuenta Banco: </label></span>
									</div>
									<div class="col-md-9">
										<select id="glAccountFiltro" name="glAccountFiltro" class="form-control  selectGlAccount" >
											<option value='-1' selected>Sin seleccion</option>
										</select>
									</div>
								</div> 
							</div>

							<div class="col-md-4">
								<div class="form-inline row">
									<div class="col-md-3">
										<span><label>Banco: </label></span>
									</div>
									<div class="col-md-9">
									<select id="bankFiltro" name="bankFiltro" class="form-control selectGeneral selectBank" data="todos">
										<option value='-1' selected>Sin seleccion</option>
									</select>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<component-text-label label="Nombre Cuenta Banco: " id="nameBankFiltro" name="nameBankFiltro" placeholder="Nombre Cuenta Banco"></component-text-label>
							</div>

            <div class="row"></div>
			<div class="col-md-4">
				<component-decimales-label label="Codigo Cuenta Banco:" id="claveBankFiltro" name="claveBankFiltro" placeholder="Codigo Cuenta Banco:"></component-decimales-label>
			</div>
			<div class="col-md-4">
				<component-decimales-label label="Numero Cuenta Banco:" id="numBankFiltro" name="numBankFiltro" placeholder="Numero Cuenta Banco:"></component-decimales-label>
			</div>
			<div class="col-md-4">
				<component-text-label label="Direccion Banco: " id="addressBankFiltro" name="addressBankFiltro" placeholder="Direccion Banco:"></component-text-label>
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
					<div class="col-md-6">
						<div class="form-inline row">
							<div class="col-md-3">
								<span><label>UR: </label></span>
							</div>
							<div class="col-md-9">
								<select id="selectUnidadNegocio" name="selectUnidadNegocio[]" class="form-control selectUnidadNegocio" multiple>
								</select>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-inline row">
							<div class="col-md-3">
								<span><label>Codigo GL Cuenta Banco: </label></span>
							</div>
							<div class="col-md-9">
								<select id="glAccount" name="glAccount" class="form-control  selectGlAccount" >
								</select>
							</div>
						</div> 
					</div>
			<div class="row"></div>
			<br>

            <div class="col-md-6">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Banco: </label></span>
                    </div>
                    <div class="col-md-9">
                    <select id="bank" name="bank" class="form-control selectGeneral selectBank"></select>
                    </div>
                </div>
            </div>

				<div class="col-md-6">
					<component-text-label label="Nombre Cuenta Banco: " id="nameBank" name="nameBank" placeholder="Nombre Cuenta Banco"></component-text-label>
				</div>
			<div class="row"></div>
			<br>
				<div class="col-md-6">
					<div class="form-inline row">
						<div class="col-md-3">
							<span><label>Moneda: </label></span>
						</div>
						<div class="col-md-3">
							<select id="currCode" name="currCode" class="form-control selectGeneral">
								<option value="MXN">MXN</option>
								<option value="USD">USD</option>
							</select>
						
						</div>
						<div class="col-md-3">
							<span><label>Default para Facturas: </label></span>
						</div>
						<div class="col-md-3">
							<select id="invoice" name="invoice" class="form-control selectGeneral">
								<option value="1">SI</option>
								<option value="0">NO</option>
							</select>
						
						</div>
					</div>
				</div>
            

            
			<div class="col-md-6">
				<component-decimales-label label="Codigo Cuenta Banco:" id="claveBank" name="claveBank" placeholder="Codigo Cuenta Banco:"></component-decimales-label>
			</div>
			<div class="row"></div>
			<br>
			<div class="col-md-6">
					<component-decimales-label label="Numero Cuenta Banco:" id="numBank" name="numBank" placeholder="Numero Cuenta Banco:"></component-decimales-label>
				</div>
				<div class="col-md-6">
					<component-text-label label="Direccion Banco: " id="addressBank" name="addressBank" placeholder="Direccion Banco:"></component-text-label>
				</div>
            <br>
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
