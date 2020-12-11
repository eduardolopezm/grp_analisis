<?php
/**
 * Viaticos
 *
 * @category			panel
 * @package				ap_grp
 * @author				Luis Aguilar Sandoval
 * @file:				procesoDeCompra.php
 * Fecha creación:		29/12/2017
 * Fecha Modificación:	29/12/2017
 * Panel para la administracion del proceso de compra
 */
//
/* DECLARACIÓN DE VARIABLES */
$PageSecurity = 11;
$PathPrefix = './';
$funcion = 2455;

/* INCLUSIÓN DE ARCHIVOS NECESARIOS */
include($PathPrefix . "config.php");
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . "includes/ConnectDB.inc");
require($PathPrefix . "includes/session.inc");
include($PathPrefix . "includes/SecurityFunctions.inc");
include($PathPrefix . "includes/SQL_CommonFunctions.inc");
$title = traeNombreFuncion($funcion, $db, "Panel del Proceso de Compra");

# Carga de archivos secundarios
require("includes/header.inc");
require("javascripts/libreriasGrid.inc");

$ocultaDepencencia = 'hidden';
?>
<script type="text/javascript" src="javascripts/procesoDeCompra.js?v=<?= rand();?>"></script>
<script>
	var nuFuncion = <?= $funcion ?>;
</script>

<!-- Filtros -->
<div class="row">
	<div id="mensajes"></div>
</div>

<!-- inicio de panel -->
<div class="row">
	<div class="panel-group">
		<div class="panel panel-default">
			<div class="panel-heading h35" role="tab" id="headingOne">
			<h4 class="panel-title">
				<div class="fl text-left">
					<a role="button" data-toggle="collapse" data-parent="#accordion" href="#Panelviaticos" aria-expanded="true" aria-controls="collapseOne">
						<b>Criterios de Filtrado</b>
					</a>
				</div>
			</h4>
			</div>
			<!-- .panel-heading -->
			<!-- <div id="closeTab" class="panel-collapse collapse in"> -->
			<div id="Panelviaticos" name="Panelviaticos" class="panel-collapse collapse in">
				<div class="panel-body">
					<div class="row" id="form-search">
						<!-- dependencia, UR, UE -->
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
							<div class="form-inline row">
								<div class="col-md-3">
									<span><label>UR: </label></span>
								</div>
								<div class="col-md-9">
									<select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio">
										<option value="-1">Seleccionar...</option>
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
										<option value="-1">Seleccionar...</option>
									</select>
								</div>
							</div>
							<br>

							<div class="form-inline row">
								<div class="col-md-3" style="vertical-align: middle;">
									<span><label>Estatus: </label></span>
								</div>
								<div class="col-md-9">
									<select id="selectEstatus" name="selectEstatus" class="form-control selectEstatus" data-funcion="<?= $funcion ?>">
										<option value="-1">Seleccionar...</option>
										<option value="1">Inicio</option>
										<option value="2">En Proceso</option>
										<option value="3">Concluido</option>
										<option value="4">Cancelado</option>
									</select>
								</div>
							</div>
						</div><!-- -col-md-4 -->
						<!-- folio, estatus -->
						<div class="col-md-4">
							<component-text-label label="Folio:" id="folio" name="folio" value=""></component-text-label>
							<br>

							<!-- TIPO EXPEDIENTE -->
							<!-- crear el componente -->
							<div class="form-inline row">
								<div class="col-md-3" style="vertical-align: middle;">
									<span><label>Tipo Expediente: </label></span>
								</div>
								<div class="col-md-9">
									<select id="tipoExpediente" name="tipoExpediente" class="form-control tipoExpediente"> 
										<option value="-1">Seleccionar...</option>
									</select>
								</div>
							</div>
							<br>
							<component-text-label label="Código Expediente:" id="codigoExpediente" name="codigoExpediente" placeholder="Código Expediente" title="Código Expediente" value="" maxlength="20"></component-text-label>
						</div>
						<!-- -col-md-4 -->
						<!-- fechas -->
						<div class="col-md-4">
							<!-- FECHA INI -->
							<!-- crear el componente -->
							<div class="form-inline row">
								<div class="col-md-3" style="vertical-align: middle;">
									<span><label>Desde: </label></span>
								</div>
								<div class="col-md-9">
									<component-date-feriado2 label="Fecha Inicio" id="fechaIni" name="fechaIni" class="w100p" placeholder="Fecha Inicio" disabled="disabled"></component-date-feriado2>
								</div>
							</div>
							<br>

							<!-- FECHA FIN -->
							<!-- crear el componente -->
							<div class="form-inline row">
								<div class="col-md-3" style="vertical-align: middle;">
									<span><label>Hasta: </label></span>
								</div>
								<div class="col-md-9">
									<component-date-feriado2 label="Fecha Fin" id="fechaFin" name="fechaFin" class="w100p" placeholder="Fecha Fin" disabled="disabled"></component-date-feriado2>
								</div>
							</div>

						</div>
						<!-- -col-md-4 -->
					</div>
					<div class="row">
						<component-button type="submit" id="btnSearch" name="btnSearch" class="glyphicon glyphicon-search" value="Filtrar"></component-button>
					</div>
				</div>
				<!-- .panel-body -->
			</div>
			<!-- .panel-collapse -->
		</div>
	</div><!-- .panel-group -->
</div> <!-- .row -->

<!-- tabla de búsqueda -->
<div class="row">
	<div id="datosCompras">
		<div id="tablaCompras"></div>
	</div>
</div><!-- .row -->

<!-- botones de acción -->
<div class="row pt10" id="botonera">
	<span id="areaBotones" name="areaBotones"></span><!-- BOTONES SEGUN PERMISOS -->
</div><!-- .row -->

<?php require 'includes/footer_Index.inc'; ?>
