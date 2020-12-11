<?php
/**
 * Carga de Mega Póliza Contable
 *
 * @category ABC
 * @package ap_grp
 * @author Japheth Calzada López <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creaci�n: 20/09/2018
 */

 
/* DECLARACION DE VARIABLES */
$PageSecurity = 1;
//$PageSecurity = 4;
$PathPrefix = './';
$funcion = 2497;

/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
$title= traeNombreFuncion($funcion, $db,'Panel de Servicios Personales');
require 'includes/header.inc';
require 'javascripts/libreriasGrid.inc';

?>
<link rel="stylesheet" href="css/listabusqueda.css" /><!-- Estilos para el auto complete -->
<script src="javascripts/nominaNuevo.js?v=<?= rand();?>"></script>

<div id="form-add">

<!-- datos generales -->
<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">            
            <div class="panel-heading h35" role="tab" id="headingOne">
                <h4 class="panel-title">
                    <div class="fl text-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#idPanelRequisiciones" aria-expanded="true" aria-controls="collapseOne">
                            <b>Encabezado</b>
                        </a>
                    </div>
                    <div id="idStatusReq" class="fr text-right  ftc7">
                        <span>Folio : </span><input type="hidden" id="idperfilusr">
                        <label id="statusReq" style="display: none;"></label>
                        <label id="statusReqVisual"></label>
                    </div>
                 </h4>
            </div>
            <div id="datosGenerales" name="datosGenerales" class="panel-collapse collapse in">
                <div class="panel-body">
					<div class="col-md-4">
          			    <br>
					<div class="form-inline row">
						<div class="col-md-3 pt10" style="vertical-align: middle;">
							<span><label>Mes: </label></span>
						</div>
						<div class="col-md-9">
							<select id="selectMes" name="selectMes" class="form-control selectMeses"> 
								<option value="-1">Seleccionar...</option>
							</select>
						</div>
					</div>
					<br>
					<div class="form-inline row">
						<div class="col-md-3 pt10" style="vertical-align: middle;">
							<span><label>Tipo de Nomina: </label></span>
						</div>
						<div class="col-md-9">
							<select id="tipoNomina" name="TipoNomina" class="form-control tipoNomina" >
							    <option value="">Seleccionar...</option>
								<option value="1">Extraordinaria</option>
								<option value="2">Ordinaria</option>
							</select>
						</div>
					</div>
					<div id ="extraordinaria" class="form-inline row"  style="display:none;">
						<div class="col-md-3 pt10" style="vertical-align: middle;">
							<span><label>No. Extraordinaria: </label></span>
						</div>
						<div class="col-md-9">
							<select id="noExtraordinaria" name="noExtraordinaria" class="form-control tipoNomina" >
							    <option value="">Seleccionar...</option>
							</select>
						</div>
					</div>
					<br>
					
        	</div>
       		<div class="col-md-4 pt20">
				<div class="col-md-3 col-lg-3 pt10 text-left" style="vertical-align: middle;">
					<span><label><b> No. quincena: </b></label></span>
				</div>
				<div class="col-md-9 col-lg-9">
					<select id="noQuincena" name="noQuincena[]" class="form-control noQuincena">
					</select>
                    <br>
                    <br><component-date-label id="txtFechaInicio" label="Desde: " value="<?= date("d-m-Y")?>"></component-date-label><br>
                    <component-date-label id="txtFechaFin" label="Hasta: " value="<?= date("d-m-Y")?>"></component-date-label>
				</div>  
            </div>
        <div class="col-md-4 pt20">
            <div class="form-inline row mt20">
            <component-date-label id="txtFechaCaptura" label="Fecha de Captura: " value="<?= date("d-m-Y")?>"></component-date-label>
          	</div>
        </div>
    </div><!-- .panel-body -->
	</div>
		<form enctype="multipart/form-data" id="loadFile" method="post"> 
			<label for="inpt-upload" class="btn btn-primary botonVerde glyphicon glyphicon-file" id="label-upload"> Cargar</label>
			<component-text type="file" id="inpt-upload" class="hidden" accept=".csv" ></component-text>
        </form>
        <!--</div>-->
    </div>
            </div>
			<div class="row" id="Totales">
			
				<table class="table table-striped border" id="tbl-filesToUp" style="border:solid 1px #eee;">
					<thead class="bgc8" style="color:#fff;">
						<tr>
							<th>Total Percepciones</th>
							<th ><input type="text" id="totalPercepciones"></th>
							<th >Total Deducciones </th>
							<th > <input type="text" id="totalDeducciones"></th>
							<th>Total Neto </th>
							<th> <input type="text" id="totalNeto"></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>

			<div name="divTabla" id="divTabla">
				<div name="divContenidoTabla" id="divContenidoTabla"></div>
			</div>
	<br>
			<!-- tabla de documentos >
		
			<div class="">
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
								<div class="row">
									<div class="col-sm-12">
										<div class="col-sm-4 text-left contenedorBotonesCarga">
											<label for="inpt-upload-exp" class="btn btn-primary botonVerde glyphicon glyphicon-file" id="label-upload"> Cargar</label>
											<component-text type="file" id="inpt-upload-exp" class="hidden" accept="application/pdf, text/csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-powerpoint, application/vnd.openxmlformats-officedocument.presentationml.presentation, image/bmp, image/gif, image/jpeg, image/png" multiple></component-text>
										</div>
									</div>
								</div> 
								
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
								</div>
								-->
							</div>
						</div>
					</div>
				</div>
			</div>
		<br>
	<div class="panel panel-default">
		<div class="panel-body" align="center" id="divBotones" name="divBotones">   
			<a id="guardarProd" name="guardarProd"  class="btn btn-default botonVerde glyphicon glyphicon-arrow-right"> Procesar</a>
			<!--<a id="cancelar" name="cancelar"  class="btn btn-default botonVerde glyphicon glyphicon-remove-sign"> Cancelar</a>-->
     		<a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="nomina.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>
		</div>
	</div>
        </div><!-- .panel .panel-default -->
    </div><!-- .panel-group -->
</div><!-- .row -->
</div>

<?php require 'includes/footer_Index.inc'; ?>