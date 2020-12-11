<?php
/**
 * Viaticos
 *
 * @category proceso
 * @package  ap_grp
 * @author   Luis Aguilar Sandoval
 * @file:	 comprobacionOficioComision.php
 * Fecha creacion: 25/01/2017
 * pantalla de comprovacion de los oficios de comision genrados
 * consforme a un identificador
 */

/* DECLARACION DE VARIABLES */
$PageSecurity = 1;
//$PageSecurity = 4;
$PathPrefix = './';
$funcion = 2338;

/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
$title= traeNombreFuncion($funcion, $db, 'Comprobación de Viáticos');
require 'includes/header.inc';
require 'javascripts/libreriasGrid.inc';

# recepción del folio de la comisión
$folio = !empty($_GET['solicitud'])?$_GET['solicitud']:'';
$ur = !empty($_GET['ur'])?$_GET['ur']:'';
$identificador = !empty($_GET['solComision'])?$_GET['solComision']:'';
$estatusSolicitud = !empty($_GET['estatus'])?$_GET['estatus']:'';
$oculta = in_array($estatusSolicitud, [6,7])?'hidden':'';

?>
<!-- llamada del js -->
<script src="javascripts/comprobacionOficioComision.js?v=<?= rand();?>"></script>
<script>
	var folio = '<?= $folio; ?>';
	var identificador = '<?= $identificador; ?>';
	var nuFuncion = <?= $funcion ?>;
	var estatusSolicitud = '<?= $estatusSolicitud; ?>';
	// var ur = '<?= $ur; ?>';
</script>

<!-- informacion de la comision -->
<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading h35">
                <h4 class="panel-title">
                    <div class="fl text-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#closeTab" aria-expanded="true" aria-controls="collapseOne">
                            <!-- <b>Criterios de Filtrado</b> -->
                            <?= '<strong>Comprobación de Comisión</strong>'; ?>
                        </a>
                    </div>
                </h4>
            </div><!-- .panel-heading -->
            <div id="closeTab" name="closeTab" class="panel-collapse collapse in">
	            <div class="panel-body">
	            	<div class="row" id="form-info">
	            		<div class="">
	            			<!-- empleado, destino, conto a comprobar -->
	            			<div class="col-md-4">
	                            <component-text-label label="Empleado:" id="empleado" name="empleado" readonly></component-text-label>
	                            <br>
	                            <component-text-label label="Destino:" id="destino" name="destino" readonly></component-text-label>
	                            <br>
	                            <component-decimales-label label="Monto a Comprobar:" id="montoTotal" name="montoTotal" readonly></component-decimales-label>
	            			</div><!-- .col-ms-4 -->
	            			<!-- folio,  observaciones -->
	            			<div class="col-md-4">
	            				<div class="form-inline form-group row">
	                                <div class="col-md-3">
	                                    <span><label class="control-label">Folio: </label></span>
	                                </div>
	                                <div class="col-md-9">
	                                	<!-- <label  id="folioLabel" class="wA bgc12 mt5 fts22 ftc8 borderGray borderRadius plr10"><?= $folio; ?></label> -->
	                                    <input type="text" class="form-control w100p text-center" id="folio" name="folio" readonly value="<?= $folio; ?>" />
	                                </div>
	                            </div><!-- form-inline form-group row -->
	            			</div><!-- .col-ms-4 -->
	            			<!-- fehca inicio, fecha termino, monto comprobado -->
	            			<div class="col-md-4">
	            				<component-date-label label="Fecha Inicio:" id="dateDesde" name="dateDesde" class="w100p" value="<?= date('d/m/Y'); ?>" title="Fecha Inicio" readonly></component-date-label>
	                            <br>
	                            <component-date-label label="Fecha Termino:" id="dateHasta" name="dateHasta" class="w100p" value="<?= date('d/m/Y'); ?>" title="Fecha Termino" readonly></component-date-label>
	                            <br>
								<component-decimales-label label="Monto Comprobado:" id="montoComp" name="montoComp" readonly></component-decimales-label>
	            			</div><!-- .col-ms-4 -->
	            		</div>
	            	</div><!-- .row -->
            		<br>
            		<div class="row">
                        <div class="col-md-1 col-xs-12">
                            <label>Objetivo comisión</label>
                        </div>
                        <div class="col-md-11 col-xs-12">
                            <component-textarea id="observaciones" name="observaciones" rows="5" placeholder="Objetivo de Comisión" readonly="true"></component-textarea>
                        </div>
                    </div><!-- .row -->
	            </div>
            </div>
        </div><!-- .panel -->
    </div><!-- .panel-group -->
</div><!-- .row -->

<!-- Itinerario -->
<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading h35">
                <h4 class="panel-title">
                    <div class="fl text-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#datosItinerario" aria-expanded="true" aria-controls="collapseOne">
                            <strong>Itinerario</strong>
                        </a>
                    </div>
                </h4>
            </div>
            <div id="datosItinerario" name="datosItinerario" class="panel-collapse collapse in">
                <div class="panel-body">

                    <div id="cabecera">
                        <nav class="row nav bgc8 fts10 borderGray w100p ftc2">
                            <div class="w3p  falsaCabeceraTabla"><label></label></div>
                            <div class="w80p  falsaCabeceraTabla"><label></label></div>
                            <div class="w6p  falsaCabeceraTabla"><label>TOTAL:</label></div>
                            <div class="w11p  falsaCabeceraTabla"><label id="TotalComision"></label></div>
                        </nav>
                        <nav class="row nav bgc8 fts10 borderGray w100p ftc2">
                            <div class="col-lg-12 col-md-12 col-sm-12 p0 m0">
                                <div class="w3p  falsaCabeceraTabla"> Nª </div>
                                <span id="tipoNacionalArea">
                                    <div class="w25p  falsaCabeceraTabla"><label>Entidad</label></div>
                                    <div class="w25p  falsaCabeceraTabla"><label>Municipio</label></div>
                                </span>
                                <span id="tipoInterArea" class="hidden">
                                    <div class="w50p  falsaCabeceraTabla"><label>País</label></div>
                                </span>
                                <div class="w12p  falsaCabeceraTabla"><label>Fecha Inicio</label></div>
                                <div class="w12p  falsaCabeceraTabla"><label>Fecha Término</label></div>
                                <div id="dias"  class="w6p  falsaCabeceraTabla"><label>Días</label></div>
                                <div class="w6p  falsaCabeceraTabla"><label>Noches</label></div>
                                <div id="importe" class="w11p falsaCabeceraTabla"><label>Importe</label></div>
                            </div>
                        </nav>
                        <div id="tbl-itinerario"><!-- contenedor de la pagina principal --></div>
                    </div><!-- .row -->
                </div><!-- .panel-body -->
            </div>
        </div><!-- .panel .panel-default -->
    </div><!-- .panel-group -->
</div><!-- .row -->

<!-- tabla de documentos fiscales -->
<div class="row">
	<div class="panel-group">
		<div class="panel panel-default">
			<div class="panel-heading h35">
				<h4 class="panel-title">
                    <div class="fl text-left">
                    	<a role="button" data-toggle="collapse" data-parent="#accordion" href="#documentosFiscalesTab" aria-expanded="true" aria-controls="collapseOne">
							<strong>Documentos Fiscales</strong>
                    	</a>
                    </div>
                </h4>
			</div>
			<div id="documentosFiscalesTab" name="documentosFiscalesTab" class="panel-collapse collapse in">
				<div class="panel-body">
					<div class="row">
						<div class="col-sm-12">
							<div class="col-sm-4 text-left contenedorBotonesCarga">
								<label for="inpt-upload" class="btn btn-primary botonVerde glyphicon glyphicon-file" id="label-upload"> Seleccionar archivo</label>
								<component-text type="file" id="inpt-upload" class="hidden" accept="text/xml, application/zip, application/x-zip, application/octet-stream, application/x-zip-compressed" multiple></component-text>
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
										<th> </th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div><!-- .row-->
					<hr>
					* Para capturar observaciones o el monto comprobado, haga clic en la celda que desea modificar.
					<div class="row">
						<div class="col-sm-12">
							<div id="content-files">
								<div id="files-fiscales"></div>
							</div>
						</div>
					</div><!-- .row-->
				</div>
			</div>
		</div>
	</div>
</div>

<!-- tabla de documentos no fiscales -->
<div class="row">
	<div class="panel-group">
		<div class="panel panel-default">
			<div class="panel-heading h35">
				<h4 class="panel-title">
                    <div class="fl text-left">
                    	<a role="button" data-toggle="collapse" data-parent="#accordion" href="#documentosNoFiscalesTab" aria-expanded="true" aria-controls="collapseOne">
							<strong>Documentos No Fiscales</strong>
                    	</a>
                    </div>
                </h4>
			</div>
			<div id="documentosNoFiscalesTab" name="documentosNoFiscalesTab" class="panel-collapse collapse in">
				<div class="panel-body">
					<div class="row">
						<div class="col-sm-12">
							<div class="col-sm-4 text-left contenedorBotonesCarga">
								<label for="inpt-no-fiscal" class="btn btn-primary botonVerde glyphicon glyphicon-file" id="label-not-upload"> Seleccionar archivo</label>
								<component-text type="file" id="inpt-no-fiscal" class="hidden" accept="application/pdf" ></component-text>
								<component-button type="button" id="btn-no-upload" class="glyphicon glyphicon-file" value="Cargar"></component-button>
							</div>
						</div>
					</div>
					<div class="row hidden" id="showFilesNoFiscal">
						<div class="col-sm-12">
							<table class="table table-striped border" id="tbl-filesToUpNoFiscal" style="border:solid 1px #eee;">
								<thead class="bgc8" style="color:#fff;">
									<tr>
										<th>Nombre</th>
										<th>Tamaño</th>
										<th>Tipo Archivo </th>
										<th> </th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div><!-- .row-->
					<hr>
					* Para capturar observaciones o el monto comprobado, haga clic en la celda que desea modificar.
					<div class="row">
						<div class="col-sm-12">
							<div id="content-noFiscales">
								<div id="files-no-fiscales"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- botones de accion -->
<div class="row pt10" id="botonera">
	<div class="panel-group">
		<div class="panel panel-default">
			<div class="panel-body">
   				<component-button type="button" class="glyphicon glyphicon-home regresar"  value="Regresar" id="regresar" name="regresar"></component-button>
			</div>
		</div>
	</div>
</div><!-- .row -->
<?php require 'includes/footer_Index.inc'; ?>
