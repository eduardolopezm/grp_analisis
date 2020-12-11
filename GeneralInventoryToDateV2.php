<?php
/**
 * Reporte Invetarios
 *
 * @category	Reporte
 * @package		ap_grp
 * @author		Jonathan Cendejas Torres <[<email address>]>
 * @license		[<url>] [name]
 * @version		GIT: [<description>]
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Reporte de Inventarios con diferente nivel de detalle
 */

/* DECLARACION DE VARIABLES */
$PageSecurity = 8;
$PathPrefix = './';
$funcion = 703;

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

if(isset($_POST['fechaHasta'])){
	$fechaFin = date("Y-m-d", strtotime($_POST['fechaHasta']));
}else{
	$fechaFin = date("Y-m-d");
}
?>
<script type="text/javascript" src="javascripts/GeneralInventoryToDateV2.js?v=<?= rand();?>"></script>

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
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>UR: </label></span>
									</div>
									<div class="col-md-9">
										<select id="selectUnidadNegocioFiltro" name="selectUnidadNegocioFiltro" class="form-control selectUnidadNegocio" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutoraFiltro')">
											<option value="0">Seleccionar...</option>
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
											<option value="0">Seleccionar...</option>
										</select>
									</div>
								</div>
								<br>

								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Sólo con existencias: </label></span>
									</div>
									<div class="col-md-9">
										<select id="SoloExistencias" name="SoloExistencias" class="form-control SoloExistencias"> 
											<option value="-1">Seleccionar...</option>
											<option value="1">Con Existencias</option>
											<option value="2">Sin Existencias</option>
										</select>
									</div>
								</div>
								<br>

							</div><!-- -col-md-4 -->

							<div class="col-md-4">
								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Partida Específica: </label></span>
									</div>
									<div class="col-md-9">
										<select id="selPartida" name="selPartida" class="form-control selPartida" multiple="multiple">
										</select>
									</div>
								</div>
								<br>

								<component-listado-label label="Clave producto:" id="claveprod" name="claveprod" placeholder="Clave producto"></component-listado-label>
								<br>

								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Almacén: </label></span>
									</div>
									<div class="col-md-9">
										<select id="selAlmacen" name="selAlmacen" class="form-control selAlmacen" multiple="multiple">
										</select>
									</div>
								</div>
								<br>

							</div><!-- -col-md-4 -->

							<div class="col-md-4">
								<component-date-label label="Hasta: " id="fechaHasta" name="fechaHasta" placeholder="Hasta Fecha" title="Hasta Fecha" value="<?= (date("d-m-Y", strtotime($fechaFin))); ?>"></component-date-label>
								<br>

							</div><!-- -col-md-4 -->
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
			&shy;
		</div>
	</div>
</div><!-- .row -->

<!-- Modal Agregar/Modificar -->
<div class="modal fade" id="modalUsoGeneral" name="modalUsoGeneral" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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
			</div>
			<div class="modal-footer">
			</div>
		</div>
	</div>
</div>

<?php require 'includes/footer_Index.inc'; ?>
