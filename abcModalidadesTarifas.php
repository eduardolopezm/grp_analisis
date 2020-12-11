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
$funcion = 2508;

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
<script type="text/javascript" src="javascripts/abcModalidadesTarifas.js?v=<?= rand();?>"></script>


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
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Objeto Principal: </label></span>
                    </div>
                    <div class="col-md-9">
                    <input type="hidden" id="txtIdDetalle" name="txtIdDetalle">
                    <select id="selectObjetoPrincipalFiltro" name="selectObjetoPrincipalFiltro" class="form-control selectObjetoPrincipal selectGeneral " onchange="fnFinalidadFuncion('Filtro')"></select>
                    </div>
                </div> 
            </div>

            <div class="col-md-6">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Objeto Parcial: </label></span>
                    </div>
                    <div class="col-md-9">
                    <select id="selectObjetoParcialFiltro" name="selectObjetoParcialFiltro" class="form-control selectGeneral"></select>
                    </div>
                </div>
            </div>

            <div class="row"></div>
            <br>
            <br>
            <div class="col-md-6"> 
            <component-number-label label="Año: " id="txtAnioFiltro" name="txtAnioFiltro" placeholder="Año"></component-number-label>
            </div>
						</div>

						<!-- Botones -->
						<br>
						<div class="row">
							<div class="col-xs-12">
								<component-button type="button" id="btnBuscar" name="btnBuscar" class="glyphicon glyphicon-search" value="Buscar" onclick="fnMostrarDatos()"></component-button>
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
					<input class="hidden" name="typeabbrev" id="typeabbrev" value="L1">
					<input class="hidden" name="currabrev" id="currabrev" value="<?php echo $_SESSION['DefaultCurrencySale']; ?>">

                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Objeto Principal: </label></span>
                    </div>
                    <div class="col-md-9">
                    <input type="hidden" id="txtIdDetalle" name="txtIdDetalle">
                    <select id="selectObjetoPrincipal" name="selectObjetoPrincipal" class="form-control selectObjetoPrincipal " onchange="fnFinalidadFuncion('')">
					</select>
                    </div>
                </div> 
            </div>

            <div class="col-md-6">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Objeto Parcial: </label></span>
                    </div>
                    <div class="col-md-9">
                    <select id="selectObjetoParcial" name="selectObjetoParcial" class="form-control selectGeneral"></select>
                    </div>
                </div>
            </div>

            <div class="row"></div>
				<br>
				<div class="col-md-6">
					<div class="form-inline row">
						<div class="col-md-3">
							<span><label>Año: </label></span>
						</div>
						<div class="col-md-9">
						<select id="anio" name="anio" class="form-control selectGeneral " onchange="fnTipoTarifasFuncion()">
						<option value="2020">2020</option>
						</select>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-inline row">
						<div class="col-md-3">
							<span><label>Tipo: </label></span>
						</div>
						<div class="col-md-3">
							<select id="type" name="type" class="form-control selectGeneral selectTipoTarifas"></select>
						</div>
						<div class="col-md-2 col-xs-12">
							<label>Rango: </label>
						</div>
						<div class="col-md-4 col-xs-12">
							<input style="float: left;" name="isRango" id="isRango" type="checkbox" class="custom-control-input" >
						</div>
					</div>
				</div>
            

            <div class="row"></div>
			<br>
			<div class="col-md-6">
				<component-decimales-label label="Valor: " id="importe" name="importe" placeholder="Valor"></component-decimales-label>
			</div>
			<br>
			<div class="col-md-6">
					<component-decimales-label label="Rango inicial: " id="rangoInicial" name="rangoInicial" placeholder="Rango inicial"></component-decimales-label>
				</div>
            <div class="row"></div>

				<div class="col-md-6">
					<component-decimales-label label="Rango final: " id="rangoFinal" name="rangoFinal" placeholder="Rango final"></component-decimales-label>
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
