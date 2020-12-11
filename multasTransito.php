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
$funcion = 2522;
 
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
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>
<link rel="stylesheet" href="css/v3/librerias/jquery-ui/jquery-ui-1.11.4/jquery-ui.min.css">
<script type="text/javascript" src="javascripts/multasTransito.js?v=<?= rand();?>"></script>


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
										<span><label>UR: </label></span>
									</div>
									<div class="col-md-9">
										<select id="selectUnidadNegocioFiltro" name="selectUnidadNegocioFiltro[]" class="form-control selectGeneral" >
										<option value="14000">14000 - Secretaria de Tránsito y Vialidad</option>
										</select>
									</div>
								</div>
							</div>

			<div class="col-md-4"> 
            	<component-text-label label="Placa: " id="txtPlaca" name="txtPlaca" placeholder="Placa"></component-text-label>
            </div>
			<div class="col-md-4">
            	<component-date-label label="Fecha Inicial: " id="txtFechaInicialFilter" name="txtFechaInicialFilter" placeholder="Fecha Inicial" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
            </div>
            <div class="row"></div>
            <br>
            <br>
			<div class="col-md-4">
              <div class="form-inline row">
					<div class="col-md-3">
						<span><label>UE: </label></span>
					</div>
					<div class="col-md-9">
						<select id="selectUnidadEjecutoraFiltro" name="selectUnidadEjecutoraFiltro[]" class="form-control selectGeneral" >
						<option value="14001">14001 - Seguridad Publica y Vialidad</option>
						</select> <!-- multiple="multiple" data-todos="true" -->
					</div>
				</div>
            </div>
			<div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Estatus: </label></span>
                    </div>
                    <div class="col-md-9">
                    <select id="selectEstatus" name="selectEstatus" class="form-control  selectGeneral">
                            <option value="">Sin selección...</option>
                            <option value="Pendiente" selected>Pendiente</option>
							<option value="Pagado">Pagado</option>
                            <option value="Cancelado">Cancelado</option>
							
                    </select>
                    </div>
                </div>
            </div>
			<div class="col-md-4">
				<component-date-label label="Fecha Final: " id="txtFechaFinal" name="txtFechaFinal" placeholder="Fecha Final" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
            </div>
			<div class="row"></div>
			<br>
			<div class="col-md-4">
				<component-text-label label="Folio: " id="folioFiltro" name="folioFiltro" placeholder="Folio"></component-text-label>
			</div>
			<div class="col-md-4">
				<component-text-label label="Contrato: " id="contratoFiltro" name="contratoFiltro" placeholder="Contrato"></component-text-label>
			</div>
			<div class="col-md-4">
				<component-text-label label="Receptor: " id="receptorFiltro" name="receptorFiltro" placeholder="Receptor"></component-text-label>
			</div>
			<div class="row"></div>

			<br>
			<div class="col-md-4">
				<component-text-label label="Infractor: " id="infractorFiltro" name="infractorFiltro" placeholder="Infractor"></component-text-label>
			</div>
			</div>

						<!-- Botones -->
						<br>
						<div class="row">
							<div class="col-xs-12">
								<component-button type="button" id="btnBuscar" name="btnBuscar" class="glyphicon glyphicon-search" value="Filtrar" onclick="fnMostrarDatos()"></component-button>
								<component-button type="button" id="btnPase" class="glyphicon glyphicon-copy" value="Generar Pase de Cobro"></component-button>
								<component-button type="button" id="nuevo" class="glyphicon glyphicon-copy" value="Nuevo"></component-button>
								<component-button type="button" id="permissParka" class="glyphicon glyphicon-lock" value="Parquimetros"></component-button>
								<component-button type="button" id="printStatus" class="glyphicon glyphicon-print" value="Imprimir Estado" style="float:right;"></component-button>


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
					<input type="text" name="idconfContrato" class="hidden" id="idconfContrato" value="4">
				<!-- <div class="col-md-6 hidden">
					<div class="form-inline row">
						<div class="col-md-3">
							<span><label>UR: </label></span>
						</div>
						<div class="col-md-9">
							<select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio">
							</select>
						</div>
						</div>
				</div> -->
				<div class="col-md-6">
						<div class="form-inline row">
						<div class="col-md-3">
							<span><label>UE: </label></span>
						</div>
						<div class="col-md-9">
							<select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora14001" >
							</select> <!-- multiple="multiple" data-todos="true" -->
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="col-md-6">
						<component-date-label label="Fecha Inicial: " id="txtFechaInicial" name="txtFechaInicial" placeholder="Fecha Inicial" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
					</div>
					<div class="col-md-6">
						<component-text-label class="atributo" data-atributo="45" label="Hora: " id="hora" name="hora" placeholder="Hora"></component-text-label>
					</div>
				</div>
				<div class="row"></div>
				<br>

				<!-- <div class="col-md-6">
					<div class="row">
						<div class="col-md-3">
						<label id="lblCompromiso">Contribuyente:</label>
						</div>
						<div class="col-md-9">
						<div class="input-group">
							<input type="text" class="form-control validanumericos" name="contribuyente" id="contribuyente" placeholder="Contribuyente" title="Contribuyente" value="" />
							<input type="text" id="contribuyenteID" name="contribuyenteID" class="hidden">
							<span class="input-group-btn">
							<button class="btn" style="background-color: rgb(114, 115, 119) !important;" type="button" id="btnBuscarContribuyente" name="btnBuscarCompromiso"><i class="glyphicon glyphicon-search" style="color: #ffffff"></i></button>
							</span>
						</div>
						</div>
					</div>
				</div> -->
				<div class="col-md-6">
						<div class="row">
							<div class="col-md-3">
							<label id="lblCompromiso">Contribuyente:</label>
							</div>
							<div class="col-md-9">
							<div class="input-group">
								<input type="text" class="form-control validanumericos" name="contribuyente" id="contribuyente" placeholder="Contribuyente" title="Contribuyente" value="" />
								<input type="text" id="contribuyenteID" name="contribuyenteID" class="hidden">
								<span class="input-group-btn">
								<button class="btn" style="background-color: rgb(114, 115, 119) !important;" type="button" id="btnBuscarContribuyente" name="btnBuscarCompromiso"><i class="glyphicon glyphicon-search" style="color: #ffffff"></i></button>
								</span>
							</div>
							</div>
						</div>
					</div>
				<div class="col-md-6">
					<component-text-label class="atributo" data-atributo="16" label="Placa: " id="placa" name="placa" placeholder="Placa"></component-text-label>
				</div>
				<div class="row"></div>
					<br>
				<div class="col-md-6">
					<component-text-label class="atributo" data-atributo="18" label="Folio: " id="folio" name="folio" placeholder="Folio"></component-text-label>
				</div>
				<div class="col-md-6">
					<component-text-label class="atributo" data-atributo="17" label="Receptor: " id="receptor" name="receptor" placeholder="Receptor"></component-text-label>
				</div>
				<div class="row"></div>
				<br>
				<div class="col-md-6">
					<component-text-label class="atributo" data-atributo="19" label="Garantía: " id="garantia" name="garantia" placeholder="Garantía"></component-text-label>
				</div>
				<div class="col-md-6">
					<component-text-label class="atributo" data-atributo="30" label="Infractor: " id="infractor" name="infractor" placeholder="Infractor"></component-text-label>
				</div>
				<div class="row"></div>
				<br>
				<div class="col-md-6">
					<div>
						<input class="form-control" type="text" id="myInput" onkeyup="myFunction()" label="Buscar: " placeholder="Buscar">
					</div>
				</div>
				<div class="col-md-6">
					<div class="total">
						<p>TOTAL: <span id="total">$0.00</span></p>
					</div>
  				</div>
				<div class="row"></div>

				<div class="col-md-6">
					<div style="height: 152px; overflow-y: scroll;">
						<ul id="sortable1" class="connectedSortable">
						</ul>
					</div>
				</div>

				<div class="col-md-6">
					<div style="height: 152px; overflow-y: scroll;">
						<div class="descrip-parcials">
							<ul id="sortable2" class="connectedSortable"></ul>
						</div>
						<div class="price-parcials">
							<ul id="sortable3" class="connectedSortable"></ul>
						</div>
					</div>
				</div>
				<div class="row"></div>
				<br>
				<div class="col-md-12">
					<component-text-label class="atributo" data-atributo="31" name="descripcion" id="descripcion" label="Descripción: " placeholder="Descripción"></component-text-label>
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
