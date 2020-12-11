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
$funcion = 2524;

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
<script type="text/javascript" src="javascripts/estadoCuentaContribuyente.js?v=<?= rand();?>"></script>


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
            </div>
			<div class="col-md-4">
				<div class="form-inline row" >
					<div class="col-md-3">
					<span><label>Tipo Descarga: </label></span>
					</div>
					<div class="col-md-9"> 
					<select id="tipoDescarga" name="tipoDescarga" class="form-control tipoDescarga" >
						<option value="" label="Seleccione una opción">Seleccione una opción</option>
						<option value="p" label="PDF" selected>PDF</option>
						<option value="x" label="Excel">Excel</option>
					</select>
					</div>
				</div>
			</div>
			<div class="col-md-4 hidden">
            	<component-date-label label="Fecha Inicial: " id="txtFechaInicialFilter" name="txtFechaInicialFilter" placeholder="Fecha Inicial" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
            </div>
			<div class="col-md-4">
				<component-date-label label="Fecha: " id="txtFechaFinal" name="txtFechaFinal" placeholder="Fecha" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
            </div>
			<div class="row"></div>
			<br>
           
			</div>

						<!-- Botones -->
						<br>
						<div class="row">
							<div class="col-xs-12">
								<component-button type="button" id="printStatus" name="btnBuscar" class="glyphicon glyphicon-search" value="Ver Reporte" ></component-button>
							</div>
						</div>
					</form>
				</div><!-- .panel-body -->
			</div><!-- .panel-collapse -->
		</div><!-- .panel -->
	</div><!-- .panel-group -->
</div><!-- / Encabezado --> 

<!-- tabla de busqueda -->





<?php require 'includes/footer_Index.inc'; ?>
