<?php
/**
 * Pagina para realizar transferencias de salida
 *
 * @category Almacen
 * @package ap_grp
 * @author Armando Barrientos Martinez <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creaci�n: 04/08/2017
 * Fecha Modificaci�n: 23/08/2018
 */

 
/* DECLARACION DE VARIABLES */
$PageSecurity = 1;
//$PageSecurity = 4;
$PathPrefix = './';
$funcion = 45;

/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
$title= traeNombreFuncion($funcion, $db,'Transferencias Salida');
require 'includes/header.inc';
require 'javascripts/libreriasGrid.inc';

// Declaracion de permisos
$permisoNueva = Havepermission($_SESSION['UserID'], 2450, $db);
$permisoAvanzar = Havepermission($_SESSION['UserID'], 2451, $db);
$permisoRechazar = Havepermission($_SESSION['UserID'], 2453, $db);
$permisoAutorizar = Havepermission($_SESSION['UserID'], 2453, $db);
$permisoCancelar = Havepermission($_SESSION['UserID'], 2453, $db);

?>
<link rel="stylesheet" href="css/listabusqueda.css" /><!-- Estilos para el auto complete -->
<script src="javascripts/StockLocTransfer.js?v=<?= rand();?>"></script>

<div id="form-add">

<!-- datos generales -->
<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading h35">
                <h4 class="panel-title">
                    <div class="fl text-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#datosGenerales" aria-expanded="true" aria-controls="collapseOne">
                            <strong>Criterios de filtrado</strong>
                        </a>
                    </div>
                </h4>
            </div>
            <div id="datosGenerales" name="datosGenerales" class="panel-collapse collapse in">
                <div class="panel-body">
					<div class="col-md-4">
						<div class="form-inline row hide">
							<div class="col-md-3">
								<span><label>Dependencia: </label></span>
							</div>
							<div class="col-md-9">
								<select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioDependeciaGeneral('selectRazonSocial', 'selectUnidadNegocio')" multiple="true"></select>
							</div>
						</div>
          			    <br>

					<div class="form-inline row">
						<div class="col-md-3 pt10" style="vertical-align: middle;">
							<span><label>UR: </label></span>
						</div>
						<div class="col-md-9">
							<select id="selectUnidadNegocio" name="selectUnidadNegocio[]" class="form-control selectUnidadNegocio" multiple="multiple" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')">                    
							</select>
						</div>
					</div>
					<br>
					<div class="form-inline row">
						<div class="col-md-3 pt10" style="vertical-align: middle;">
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
						<div class="col-md-3 pt10" style="vertical-align: middle;">
							<span><label>Tipo de Bien: </label></span>
						</div>
						<div class="col-md-9 col-lg-9">
							<select id="selectTipoProducto" name="selectTipoProducto[]" class="form-control selectTipoProducto " multiple="multiple" data-funcion="<?= $funcion ?>"></select>
						</div>
					</div>
        		</div>
       		<div class="col-md-4 pt20">
				<div class="form-inline row">
					<div class="col-md-3 col-lg-3 text-left">
						<span><label>Folio:</label></span>
					</div>
					<div class="col-md-9 col-lg-9">
						<input class="form-control w100p" type="number" id="txtNumeroTransferencia" name="txtNumeroTransferencia" min="0">
					</div>
				</div>
         	<div class="form-inline row mt20">
				<div class="col-md-3 col-lg-3 pt10 text-left" style="vertical-align: middle;">
					<span><label><b> Estatus: </b></label></span>
				</div>
				<div class="col-md-9 col-lg-9">
					<select id="selectStatus" name="selectStatus[]" class="form-control selectEstatusRequisiciones" multiple="multiple" data-funcion="<?= $funcion ?>"></select>
				</div>
          	</div>
        </div>
        <div class="col-md-4 pt20">
            <component-date-label id="txtFechaInicio" label="Desde: " value="<?= date("d-m-Y")?>"></component-date-label><br>
            <component-date-label id="txtFechaFin" label="Hasta: " value="<?= date("d-m-Y")?>"></component-date-label>
        </div>
    </div><!-- .panel-body -->
	</div>
        <!--<div class="panel-footer">-->
            <component-button type="submit" id="btnBusqueda" name="btnBusqueda" class="glyphicon glyphicon-search" onclick="return false;" value="Filtrar"></component-button>
            
        <!--</div>-->
    </div>
            </div>
			<div name="divTabla" id="divTabla">
				<div name="divContenidoTabla" id="divContenidoTabla"></div>
			</div>
			<br>
			<div class="panel panel-default">
				<div class="panel-body" align="center" id="divBotones" name="divBotones">
					<?php
						if ($permisoNueva==1) {
							echo '<component-button type="submit" id="btNuevaTransferencia" name="btNuevaTransferencia" class="glyphicon glyphicon-copy"
										onclick="return false;" value="Nueva"></component-button>';
						}
						# botón de avanzar
						if($permisoAvanzar!=0 && $permisoAutorizar == 0){
							echo '<button type="button" id="Avanzar" name="Avanzar" class="btn btn-default botonVerde glyphicon glyphicon-forward">&nbsp;Avanzar</button>';
						}
						# botón de autorizar
						if($permisoAutorizar!=0){
							echo '<button type="button" id="autorizarTerminada" name="autorizarTerminada" class="btn btn-default botonVerde glyphicon glyphicon-flag">&nbsp;Autorizar</button>';
						 }
						if($permisoRechazar!=0){
							echo '<button type="button" id="rechazarTerminada" name="rechazarTerminada" class="btn btn-default botonVerde glyphicon glyphicon-floppy-remove">&nbsp;Rechazar</button>';
						 }
						 # botón de Cancelar
						 if($permisoCancelar!=0){
							echo '<button type="button" id="cancelarTerminada" name="cancelarTerminada" class="btn btn-default botonVerde glyphicon glyphicon-trash">&nbsp;Cancelar</button>';
						 }
						?>
				</div>
			</div>
        </div><!-- .panel .panel-default -->
    </div><!-- .panel-group -->
</div><!-- .row -->
</div>

<?php require 'includes/footer_Index.inc'; ?>