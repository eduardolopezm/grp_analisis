<?php
/**
 * Oficio de Compra
 *
 * @category			proceso
 * @package				ap_grp
 * @author				Luis Aguilar Sandoval
 * @file:				oficioCompra.php
 * Fecha creación:		25/01/2017
 * pantalla de captura de oficios de compra generados
 * conforme a un identificador
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
$title = traeNombreFuncion($funcion, $db, "Proceso de Compra");

# Carga de archivos secundarios
require("includes/header.inc");
require("javascripts/libreriasGrid.inc");

# recepción del id del proceso de compra
$identificador = ( !empty($_GET['idCompra']) ? $_GET['idCompra'] : '' );
$estatus = "";

if($identificador){
	$sql = "SELECT pc.`id_nu_estatus` AS 'Estatus', pc.`orderno`, pc.`requisitionno`, pc.`sn_monto_requisicion`

			FROM `tb_proceso_compra` AS pc

			WHERE pc.`id_nu_compra` = '$identificador'";

	$datos = DB_fetch_array(DB_query($sql, $db));
	$estatus = $datos['Estatus'];

	$enc = new Encryption;
	$liga = "URL=".$enc->encode("&ModifyOrderNumber=>$datos[orderno]&idrequisicion=>$datos[requisitionno]");
}
?>
<script src="javascripts/oficioCompra.js?v=<?= rand();?>"></script>
<script>
	var	identificador = '<?= $identificador; ?>',
		montoRequisicion = parseFloat('<?= $datos['sn_monto_requisicion'] ? $datos['sn_monto_requisicion'] : 0 ; ?>'),
		estatus = '<?= $estatus; ?>';

	montoRequisicionTexto = parseFloat(montoRequisicion).toLocaleString(undefined, {
							minimumFractionDigits: 2,
							maximumFractionDigits: 2
						});
</script>

<div class="row">
	<div class="col-md-4">
		<div class="form-inline row">
			<div class="col-md-3">
				<span><label>Folio Requisición: </label></span>
			</div>
			<div class="col-md-9">
				<label id="folioRequisicion" class="wA bgc12 mt5 fts22 ftc8 borderGray borderRadius plr10" disabled="disabled">&nbsp;</label>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-inline row">
			<div class="col-md-3">
				<span><label>Folio Compra: </label></span>
			</div>
			<div class="col-md-9">
				<label id="folioCompra" class="wA bgc12 mt5 fts22 ftc8 borderGray borderRadius plr10" disabled="disabled">&nbsp;</label>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-inline row">
			<div class="col-md-3">
				<span><label>Estatus Compra: </label></span>
			</div>
			<div class="col-md-9">
				<label id="estatusCompra" class="wA bgc12 mt5 fts22 ftc8 borderGray borderRadius plr10" disabled="disabled">&nbsp;</label>
			</div>
		</div>
	</div>
</div>

<!-- Detalle de Registro -->
<div class="row">
	<div class="panel-group">
		<div class="panel panel-default">
			<div class="panel-heading h35">
				<h4 class="panel-title">
					<div class="fl text-left">
						<a role="button" data-toggle="collapse" data-parent="#accordion" href="#detalleRegistro" aria-expanded="true" aria-controls="collapseOne">
							<strong>Detalle de Registro</strong>
						</a>
					</div>
				</h4>
			</div><!-- .panel-heading -->
			<div id="detalleRegistro" name="detalleRegistro" class="panel-collapse collapse in">
				<div class="panel-body">
					<div class="row" id="form-info">
						<div class="">
							<!-- UR, UE, Partida Específica -->
							<div class="col-md-3">

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

								<!-- PARTIDA ESPECÍFICA -->
								<!-- crear el componente -->
								<component-text-label label="Partida Específica:" id="partidaEspecifica" name="partidaEspecifica" placeholder="Partida Específica" title="Partida Específica" value="" readonly></component-text-label>
								<!--<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Partida Específica: </label></span>
									</div>
									<div class="col-md-9">
										<select id="partidaEspecifica" name="partidaEspecifica" class="form-control partidaEspecifica"> 
											<option value="-1">Seleccionar...</option>
										</select>
									</div>
								</div>-->
								<br>

							</div>

							<!-- Expediente: Código, Descripción y Tipo -->
							<div class="col-md-3">

								<!-- CÓDIGO EXPEDIENTE -->
								<!-- crear el componente -->
								<component-text-label label="Código Expediente:" id="codigoExpediente" name="codigoExpediente" placeholder="Código Expediente" title="Código Expediente" value="" disabled="true"></component-text-label>
								<br>

								<!-- DESCRIPCIÓN EXPEDIENTE -->
								<!-- crear el componente -->
								<component-text-label label="Descripción Expediente:" id="descripcionExpediente" name="descripcionExpediente" placeholder="Descripción Expediente" title="Descripción Expediente" value=""></component-text-label>
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

							</div>

							<!-- Referencia Expediente, Tipo Contratación y Artículo -->
							<div class="col-md-3">

								<!-- REFERENCIA EXPEDIENTE -->
								<!-- crear el componente -->
								<component-text-label label="Referencia Expediente:" id="referenciaExpediente" name="referenciaExpediente" placeholder="Referencia Expediente" title="Referencia Expediente" value=""></component-text-label>
								<br>

								<!-- TIPO CONTRATACIÓN -->
								<!-- crear el componente -->
								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Tipo Contratación: </label></span>
									</div>
									<div class="col-md-9">
										<select id="tipoContratacion" name="tipoContratacion" class="form-control tipoContratacion"> 
											<option value="-1">Seleccionar...</option>
										</select>
									</div>
								</div>
								<br>

								<!-- ARTÍCULO -->
								<!-- crear el componente -->
								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Info.<br>Requisición: </label></span>
									</div>
									<div class="col-md-9">
										<?= "<a target='_blank' href='./Captura_Requisicion_V_3.php?$liga' style='color: blue; '><u>Ver detalle</u></a>\n"; ?>
									</div>
								</div>
								<br>

							</div>

							<!-- Fechas: Convocatoria, Inicio y Estimada -->
							<div class="col-md-3">

								<!-- FECHA CONVOCATORIA -->
								<!-- crear el componente -->
								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Fecha Convocatoria: </label></span>
									</div>
									<div class="col-md-9">
										<component-date-feriado2 label="Fecha Convocatoria" id="fechaConvocatoria" name="fechaConvocatoria" class="w100p" placeholder="Fecha Convocatoria" disabled="disabled"></component-date-feriado2>
									</div>
								</div>
								<br>

								<!-- FECHA INICIO -->
								<!-- crear el componente -->
								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Fecha Inicio: </label></span>
									</div>
									<div class="col-md-9">
										<component-date-feriado2 label="Fecha Inicio" id="fechaInicio" name="fechaInicio" class="w100p" placeholder="Fecha Inicio" disabled="disabled"></component-date-feriado2>
									</div>
								</div>
								<br>

								<!-- FECHA ESTIMADA -->
								<!-- crear el componente -->
								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Fecha Estimada: </label></span>
									</div>
									<div class="col-md-9">
										<component-date-feriado2 label="Fecha Estimada" id="fechaEstimada" name="fechaEstimada" class="w100p" placeholder="Fecha Estimada" disabled="disabled"></component-date-feriado2>
									</div>
								</div>
								<br>

							</div>
						</div>
					</div><!-- .row -->
					<br>
				</div>
			</div>
		</div><!-- .panel -->
	</div><!-- .panel-group -->
</div><!-- .row -->

<!-- tabla de documentos -->
<div class="row">
	<div class="panel-group">
		<div class="panel panel-default">
			<div class="panel-heading h35">
				<h4 class="panel-title">
					<div class="fl text-left">
						<a role="button" data-toggle="collapse" data-parent="#accordion" href="#documentos" aria-expanded="true" aria-controls="collapseOne">
							<strong>Expediente Electrónico</strong>
						</a>
					</div>
				</h4>
			</div>
			<div id="documentos" name="documentos" class="panel-collapse collapse in">
				<div class="panel-body">
					<div class="row<?= ( $estatus==4 ? " hidden" : "" ); ?>">
						<div class="col-sm-12">
							<div class="col-sm-4 text-left contenedorBotonesCarga">
								<label for="inpt-upload" class="btn btn-primary botonVerde glyphicon glyphicon-file" id="label-upload"> Seleccionar archivo</label>
								<component-text type="file" id="inpt-upload" class="hidden" accept="application/pdf, text/csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-powerpoint, application/vnd.openxmlformats-officedocument.presentationml.presentation, image/bmp, image/gif, image/jpeg, image/png" multiple></component-text>
								<component-button type="button" id="btn-upload" class="glyphicon glyphicon-file" value="Cargar"></component-button>
							</div>
						</div>
					</div><!-- .row -->
					<div class="row hidden" id="showFiles">
						<div class="col-sm-12">
							<table class="table table-striped border" id="tbl-filesToUp" style="border:solid 1px #eee;">
								<thead class="bgc8" style="color:#fff;">
									<tr>
										<th>Nombre</th>
										<th>Tamaño</th>
										<th>Tipo Archivo </th>
										<th>Anexos del Expediente </th>
										<th> </th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div><!-- .row-->
					<hr<?= ( $estatus==4 ? ' class="hidden"' : "" ); ?>>
					<ul class="nav nav-tabs">
						<li class="active"> <a data-toggle="tab" href="#administrativos" tabla="files-adm" class="bgc10" aria-expanded="true">Administrativos</a> </li>
						<li> <a data-toggle="tab" href="#seguimiento" tabla="files-seg" class="bgc10" aria-expanded="false">Seguimiento</a> </li>
						<li> <a data-toggle="tab" href="#otros" tabla="files-otr" class="bgc10" aria-expanded="false">Otros</a> </li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane active" id="administrativos">
							<div class="row">
								<div class="col-sm-12">
									<div id="content-files-adm">
										<div id="files-adm"></div>
									</div>
								</div>
							</div>
						</div>

						<div class="tab-pane" id="seguimiento">
							<div class="row">
								<div class="col-sm-12">
									<div id="content-files-seg">
										<div id="files-seg"></div>
									</div>
								</div>
							</div>
						</div>

						<div class="tab-pane" id="otros">
							<div class="row">
								<div class="col-sm-12">
									<div id="content-files-otr">
										<div id="files-otr"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Proceso de Contratación -->
<div class="row<?= ( $estatus==1 ? " hidden" : "" ); ?>">
	<div class="panel-group">
		<div class="panel panel-default">
			<div class="panel-heading h35">
				<h4 class="panel-title">
					<div class="fl text-left">
						<a role="button" data-toggle="collapse" data-parent="#accordion" href="#procesoContratacion" aria-expanded="true" aria-controls="collapseOne">
							<strong>Proceso de Contratación</strong>
						</a>
					</div>
				</h4>
			</div><!-- .panel-heading -->
			<div id="procesoContratacion" name="procesoContratacion" class="panel-collapse collapse in">
				<div class="panel-body" id="form-info-contrato">
					<div class="row">
						<div class="">
							<!-- Proveedor/Beneficiario, RFC, Representante Legal, CONTRATO/CONVENIO -->
							<div class="col-md-4">

								<!-- Proveedor/Beneficiario -->
								<!-- crear el componente -->
								<component-listado-label label="Proveedor/ Beneficiario:" id="proveedor" name="proveedor" placeholder="Proveedor/ Beneficiario"></component-listado-label>
								<br>

								<!-- RFC -->
								<!-- crear el componente -->
								<component-text-label label="RFC:" id="RFC" name="RFC" placeholder="RFC" title="RFC" value="" readonly></component-text-label>
								<br>

								<!-- REPRESENTANTE LEGAL -->
								<!-- crear el componente -->
								<component-text-label label="Representante Legal:" id="representanteLegal" name="representanteLegal" placeholder="Representante Legal" title="Representante Legal" value="" readonly></component-text-label>
								<br>

								<!-- Contrato/Convenio -->
								<!-- crear el componente -->
								<component-text-label label="Contrato/ Convenio:" id="contratoConvenio" name="contratoConvenio" placeholder="Contrato/Convenio" title="Contrato/Convenio" value=""></component-text-label>
								<br>

							</div>

							<!-- Fechas: Inicio, Fin y Firma, Duración del Contrato -->
							<div class="col-md-4">

								<!-- FECHA INI -->
								<!-- crear el componente -->
								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Fecha Inicio: </label></span>
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
										<span><label>Fecha Fin: </label></span>
									</div>
									<div class="col-md-9">
										<component-date-feriado2 label="Fecha Fin" id="fechaFin" name="fechaFin" class="w100p" placeholder="Fecha Fin" disabled="disabled"></component-date-feriado2>
									</div>
								</div>
								<br>

								<!-- FECHA FIRMA -->
								<!-- crear el componente -->
								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Fecha Firma: </label></span>
									</div>
									<div class="col-md-9">
										<component-date-feriado2 label="Fecha Firma" id="fechaFirma" name="fechaFirma" class="w100p" placeholder="Fecha Firma" disabled="disabled"></component-date-feriado2>
									</div>
								</div>
								<br>

								<!-- MONTO DE LA COMPRA -->
								<!-- crear el componente -->
								<component-decimales-label label="Monto de la Compra:" id="montoCompra" name="montoCompra" placeholder="Monto de la Compra" title="Monto de la Compra" value=""></component-decimales-label>
								<br>

							</div>

							<!-- Referencia Expediente, Tipo Contratación y Artículo -->
							<div class="col-md-4 ColumnaDerecha">

								<!-- PERIODO DE CONTRATO -->
								<!-- crear el componente -->
								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Periodo de Contrato: </label></span>
									</div>
									<div class="col-md-9">
										<select id="periodoContrato" name="periodoContrato" class="form-control periodoContrato"> 
											<option value="-1">Seleccionar...</option>
										</select>
									</div>
								</div>
								<br>

								<!-- DURACIÓN DEL CONTRATO -->
								<!-- crear el componente -->
								<div class="form-inline row">
									<div class="col-md-3" style="vertical-align: middle;">
										<span><label>Duración del Contrato: </label></span>
									</div>
									<div class="col-md-3">
										<input type="number" id="contratoDuracion" name="contratoDuracion" placeholder="Duración del Contrato" title="Duración del Contrato" onkeyup="" onkeypress="" maxlength="100" onpaste="return false" class="form-control" style="width: 100%;" min="0" step="1">
									</div>
									<div class="col-md-6">
										<select id="contratoDuracionUnidad" name="contratoDuracionUnidad" class="form-control contratoDuracionUnidad"> 
											<option value="-1">Seleccionar...</option>
											<option value="1">Año(s)</option>
											<option value="2">Mes(es)</option>
										</select>
									</div>
								</div>
								<br>

							</div>
						</div>
					</div><!-- .row -->

					<div class="row">
						<div class="col-md-1 col-xs-12">
							<label>Observaciones</label>
						</div>
						<div class="col-md-11 col-xs-12">
							<component-textarea id="observaciones" name="observaciones" rows="5" placeholder="Observaciones" readonly="true"></component-textarea>
						</div>
					</div>

				</div>
			</div>
		</div><!-- .panel -->
	</div><!-- .panel-group -->
</div><!-- .row -->

<!-- botones de accion -->
<div class="row pt10" id="botonera">
	<div class="panel-group">
		<div class="panel panel-default">
			<div class="panel-body">
				<!-- botones de acción --><?php if($estatus<3){ echo "\n";?>
				<component-button type="button" class="glyphicon glyphicon-floppy-disk" id="guardar" value="Guardar"></component-button><?php } echo "\n"; ?>
				<component-button type="button" class="glyphicon glyphicon-home regresar" value="Regresar" id="regresar" name="regresar"></component-button>
			</div>
		</div>
	</div>
</div><!-- .row -->
<?php require 'includes/footer_Index.inc'; ?>
