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
?>

<link rel="stylesheet" href="css/listabusqueda.css" />
<link rel="stylesheet" href="css/v3/librerias/jquery-ui/jquery-ui-1.11.4/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="css/v3/librerias/bootstrap-3.3.7/css/bootstrap.min.css">
<script type="text/javascript" src="javascripts/infoPanelContratos.js?v=<?= rand();?>"></script>
<input id="contratosID" type="text" class="hidden" value="<?php echo $_GET['id_contratos']; ?>">


<!-- Filtros -->
<div class="row">
	<div class="panel-group" style = "width: 95% !important;">
		<div class="panel panel-default">
			<div class="panel-heading h35">
				<h4 class="panel-title">
					<!-- <a data-toggle="collapse" href="#closeTab"> <strong>CRITERIOS DE BÚSQUEDA</strong> </a> -->
					<div class="fl text-left">
						<a role="button" data-toggle="collapse" data-parent="#accordion" href="#closeTab" aria-expanded="true" aria-controls="collapseOne">
							<b>Criterios de Registro</b>
						</a>
					</div>
				</h4>
			</div><!-- .panel-heading -->
			<div id="closeTab" class="panel-collapse collapse in">
				<div class="panel-body">
					<form id="frmFiltroActivos">
						<div class="row">
							<!-- UR, UE -->
						

							<div class="col-md-4 col-xs-6">
								<!-- CUENTA CARGO -->
								<!-- crear el componente -->
								<component-text-label label="Contribuyente:" id="contribuyenteFiltro" name="contribuyenteFiltro" placeholder="Contribuyente"></component-text-label>

								<br>
								<component-text-label label="Folio Contrato:" id="contrato" name="contro" placeholder="Folio Contrato"></component-text-label>
							</div>

							<div class="col-md-4 col-xs-6">
								<component-text-label label="Periodicidad:" id="periodicidad" name="periodicidad" placeholder="Periodicidad"></component-text-label>
								<br>
								<component-text-label label="Tipo de Periodo:" id="tipoPeriodo" name="tipoPeriodo" placeholder="Tipo de Periodo"></component-text-label>
							</div>

							<!-- <div class="col-md-4 col-xs-6"> 
								<component-date-label label="Fecha Inicio: " id="txtFechaInicial" name="txtFechaInicial" placeholder="Fecha Inicial" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
							</div>
							<br>
							<div class="col-md-4 col-xs-6"> 
								<component-date-label label="Fecha Vigencia: " id="txtFechaFinal" name="txtFechaFinal" placeholder="Fecha Final" value="<?php echo date('d-m-Y'); ?>"></component-date-label>							</div>
							</div> -->

					</form>
				</div><!-- .panel-body -->
			</div><!-- .panel-collapse -->
		</div><!-- .panel -->
	</div><!-- .panel-group -->
</div><!-- / Encabezado --> 

<!-- botones de accion -->
<div class="row pt10">
	<div class="panel panel-default">
		<div class="panel-body" align="center">
			<button type="button" class="btn botonVerde glyphicon glyphicon glyphicon-chevron-left" id="regresar"> Regresar</button>
			<component-button type="button" id="nuevo" class="glyphicon glyphicon-copy" value="Generar Adeudos"></component-button>
			<button type="button" class="btn botonVerde glyphicon glyphicon glyphicon-chevron-right" id="continuar"> Continuar</button>
		</div>
	</div>
</div><!-- .row -->

<!-- tabla de busqueda -->
<div class="row">
	<div name="contenedorTabla " id="contenedorTabla" style = "width: 95% !important;">
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
								<select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio">
								</select>
							</div>
							</div>
					</div>
					
					<div class="col-md-4">
						<component-text-label label="Contribuyente:" id="contribuyente" name ="contribuyente" placeholder="Contribuyente"></component-text-label>
						<input type="text" id="contribuyenteID" name="contribuyenteID" class="hidden">
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
                    <select id="selectEstatus" name="selectEstatus" class="form-control selectGeneral" disabled>
                            <option value="Pendiente" selected>Pendiente</option>
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
					<component-date-label label="Fecha de Vigencia: " id="dtFechaVigencia" name="dtFechaVigencia" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
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
