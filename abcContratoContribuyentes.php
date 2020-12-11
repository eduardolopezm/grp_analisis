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
$funcion = 2510;

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

$modContribuyente = Havepermission($_SESSION ['UserID'], 2548, $db);
?>

<script type="text/javascript">
	var modContribuyente = '<?php echo $modContribuyente; ?>';
</script>

<link rel="stylesheet" href="css/listabusqueda.css" />
<link rel="stylesheet" href="css/v3/librerias/jquery-ui/jquery-ui-1.11.4/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="css/v3/librerias/bootstrap-3.3.7/css/bootstrap.min.css">
<script type="text/javascript" src="javascripts/abcContratoContribuyentes.js?v=<?= rand();?>"></script>

<!-- Filtros -->
<div class="row container-fluid">
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
										<select id="selectUnidadNegocioFiltro" name="selectUnidadNegocioFiltro" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocioFiltro','selectUnidadEjecutoraFiltro')">
											<option value="-1">Seleccionar...</option>
										</select>
									</div>
								</div>
								<br>
								<div class="form-inline row">
									<div class="col-md-3">
										<span><label>UE: </label></span>
									</div>
									<div class="col-md-9">
										<select id="selectUnidadEjecutoraFiltro" name="selectUnidadEjecutoraFiltro" class="form-control selectUnidadEjecutora" >
											<option value="-1">Seleccionar...</option>
										</select> <!-- multiple="multiple" data-todos="true" -->
									</div>
								</div>
								<br>
								<div class="form-inline row">
									<div class="col-md-3">
										<span><label>Estatus: </label></span>
									</div>
									<div class="col-md-9">
									<select id="txtEstatus" name="txtEstatus" class="form-control selectGeneral">
											<option value="-1">Selecciona una estatus</option>
											<option value="Pendiente">Pendiente</option>
											<option value="Pagado">Pagado</option>
									</select>
									</div>
                				</div>
							</div>

							<div class="col-md-4">
								<!-- CUENTA CARGO -->
								<!-- crear el componente -->
								<!-- <component-text-label label="Contribuyente:" id="contribuyenteFiltro" name="contribuyenteFiltro" placeholder="Contribuyente"></component-text-label>
								<input type="text" id="contribuyenteIDFiltro" name="contribuyenteIDFiltro" class="hidden" value="-1"> -->
									<div class="row">
										<div class="col-md-3">
										<label id="lblCompromiso">Contribuyente:</label>
										</div>
										<div class="col-md-9">
										<div class="input-group">
											<input type="text" class="form-control validanumericos" name="contribuyenteFiltro" id="contribuyenteFiltro" placeholder="Contribuyente" title="Contribuyente" value="" />
											<input type="text" id="contribuyenteIDFiltro" name="contribuyenteIDFiltro" class="hidden" value="-1">
											<span class="input-group-btn">
											<button class="btn" style="background-color: #1B693F !important;" type="button" id="btnBuscarContribuyenteFiltro" name="btnBuscarContribuyenteFiltro"><i class="glyphicon glyphicon-search" style="color: #ffffff"></i></button>
											</span>
										</div>
										</div>
									</div> 
								<br>
								<div class="form-inline row">
									<div class="col-md-3">
										<span><label>Contrato: </label></span>
									</div>
									<div class="col-md-9">
										<select id="selectContratosFiltro" name="selectContratosFiltro" class="form-control selectContrato" onchange="fnObtFiltrosContrato()">
										</select> <!-- multiple="multiple" data-todos="true" -->
									</div>
								</div>
								<br>
								<component-text-label label="Atributo 1:" id="txtAtributo" name="txtAtributo" placeholder="Atributo 1" title="atributo1" value=""></component-text-label>
								<!-- <component-text-label label="Atributo 2:" id="txtAtributo2" name="txtAtributo2" placeholder="Atributo 2" title="atributo2" value=""></component-text-label> -->
							</div>

							<div class="col-md-4"> 
								<component-date-label label="Fecha Inicial: " id="txtFechaInicial" name="txtFechaInicial" placeholder="Fecha Inicial" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
								<br>
								<component-date-label label="Fecha Final: " id="txtFechaFinal" name="txtFechaFinal" placeholder="Fecha Final" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
							</div>
						</div>

						<textarea id="txtFiltrosJson" name="txtFiltrosJson" style="display: none;"></textarea>
						<h3 align="left">Filtros por contrato seleccionado</h3>
						<div class="row" id="divFiltrosContratos"></div>

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


<!-- botones de accion -->
<div class="row pt10 container-fluid">
	<div class="panel panel-default">
		<div class="panel-body" align="center">
			<component-button type="button" id="nuevo" class="glyphicon glyphicon-copy" value="Nuevo"></component-button>
			<component-button type="button" id="allPase" class="glyphicon glyphicon-copy hidden" value="Generar Pase Todos" onclick="fnTodosPaseDeCobro()"></component-button>
			<component-button type="button" id="printStatus" class="glyphicon glyphicon-print" value="Imprimir Estado" style="float:right;"></component-button>
		</div>
	</div>
</div><!-- .row -->

<!-- tabla de busqueda -->
<div class="row container-fluid">
	<div name="contenedorTabla" id="contenedorTabla">
		<div name="tablaGrid" id="tablaGrid"></div>
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

					<div class="col-md-4">
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
					
					<!-- <div class="col-md-4">
						<component-text-label label="Contribuyente:" id="contribuyente" name ="contribuyente" placeholder="Contribuyente"></component-text-label>
						<input type="text" id="contribuyenteID" name="contribuyenteID" class="hidden">
					</div> -->
					<div class="col-md-4">
						<div class="row">
							<div class="col-md-3">
							<label id="lblCompromiso">Contribuyente:</label>
							</div>
							<div class="col-md-9">
							<div class="input-group">
								<input type="text" class="form-control validanumericos" name="contribuyente" id="contribuyente" placeholder="Contribuyente" title="Contribuyente" value="" />
								<input type="text" id="contribuyenteID" name="contribuyenteID" class="hidden">
								<span class="input-group-btn">
								<button class="btn" style="background-color: #1B693F !important;" type="button" id="btnBuscarContribuyente" name="btnBuscarCompromiso"><i class="glyphicon glyphicon-search" style="color: #ffffff"></i></button>
								</span>
							</div>
							</div>
						</div>
					</div>
					

					<div class="col-md-4">
						<div class="form-inline row">
							<div class="col-md-3">
								<span><label>Contrato: </label></span>
							</div>
							<div class="col-md-9">
								<select id="selectContratos" name="selectContratos" class="form-control selectContrato">
								</select> <!-- multiple="multiple" data-todos="true" -->
							</div>
						</div>
            		</div>

					<div class="row"></div>
					<br>
					

					<div class="col-md-4">
							<div class="form-inline row">
							<div class="col-md-3">
								<span><label>UE: </label></span>
							</div>
							<div class="col-md-9">
								<select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" >
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
                    <select id="selectEstatus" name="selectEstatus" class="form-control selectGeneral" >
                            <option value="Pendiente" selected>Pendiente</option>
                            <option value="Cancelado" selected>Cancelado</option>
                            <option value="Utilizado" selected>Utilizado</option>

                    </select>
                    </div>
                </div>
          	</div>

			  <div class="col-md-4">
					<component-date-label label="Fecha de Inicio: " id="dtFechaInicio" name="dtFechaInicio" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
		
				</div>

				<div class="row"></div>
					<br>

			<div class="col-md-4">  
            	<component-number-label label="Periodicidad:" id="nuPeriodicidad" name="nuPeriodicidad" placeholder="Periodicidad" title="Periodicidad" value=""></component-number-label>
          </div>

		  <div class="col-md-4">  
                  <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Tipo de Periodo: </label></span>
                    </div>
                    <div class="col-md-9">
                    <select id="selectTipoPeriodo" name="selectTipoPeriodo" class="form-control selectGeneral">
                            <option value="Mes">Mes</option>
                            <option value="Año">Año</option>
                    </select>
                    </div>
                </div>
			
          	</div>
		  
			  <div class="col-md-4">
					<!-- <component-date-label label="Fecha de Vigencia: " id="dtFechaVigencia" name="dtFechaVigencia" value="<?php echo date('d-m-Y'); ?>"></component-date-label> -->
					<div id="divAtributo1Captura" style="display: none;">
						<component-text-label label="Atributo:" id="txtAtributo1Val" name="txtAtributo1Val" placeholder="Atributo 2" title="Atributo 2" value=""></component-text-label>
						<input type="hidden" name="txtAtributo1ValId" id="txtAtributo1ValId" value="" />
					</div>
				</div>
				<div class="row"></div>
					<br>
					<div class="col-md-8"><div class="form-inline row"><div class="col-md-1 col-xs-12" style="
    width: 12%;
"><span><label>Descripción:</label></span></div> <div class="col-md-10 col-xs-12" style="
    width: 88%;
"><input type="text" id="description" name="description" placeholder="Descripción" title="Descripción" onkeyup="" onkeypress="" maxlength="100" onpaste="return false" class="form-control" style="width: 100%;"></div></div></div>
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
