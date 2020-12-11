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
$funcion = 2509;

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
<script type="text/javascript" src="javascripts/abcObjetosContrato.js?v=<?= rand();?>"></script>


<!-- Filtros -->
<div class="row">
	<div class="panel-group">
		<div class="panel panel-default">
			<div class="panel-heading h35">
				<h4 class="panel-title">
					<!-- <a data-toggle="collapse" href="#closeTab"> <strong>CRITERIOS DE BÚSQUEDA</strong> </a> -->
					<div class="fl text-left">
						<a role="button" data-toggle="collapse" data-parent="#accordion" href="#closeTab" aria-expanded="true" aria-controls="collapseOne">
							<b>Criterios de registro</b>
						</a>
					</div>
				</h4>
			</div><!-- .panel-heading -->
			<div id="closeTab" class="panel-collapse collapse in">
				<div class="panel-body">
					<div class="modal-body OverdeSelectsenModales" id="forma" name="forma">
						<!-- <input class="hidden" name="currabrev" id="currabrev" value="<?php echo $_SESSION['DefaultCurrencySale']; ?>"> -->
						<input type="hidden" value="<?php echo $_GET['id_loccode']; ?>" id="objetoPrincipal" name="objetoPrincipal">
						<input id="contrato" name="contrato" value="<?php echo $_GET['id_contratos']; ?>" type="text" class="hidden">
						<div class="row">
							<!-- UR, UE -->
							<div class="col-md-6 col-xs-6">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Objeto Principal: </label></span>
                    </div>
                    <div class="col-md-9">
                    <input type="hidden" id="txtIdDetalle" name="txtIdDetalle">
                    <select id="selectObjetoPrincipal" name="selectObjetoPrincipal" class="form-control selectObjetoPrincipal selectGeneral " onchange="fnFinalidadFuncion('')"></select>
                    </div>
                </div>
				<br>
				<component-decimales-label label="Valor Unitario:" id="valor" name="valor" placeholder="Valor Unitario"></component-decimales-label>
			</div>

            <div class="col-md-6 col-xs-6">

				<div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Objeto Parcial: </label></span>
                    </div>
                    <div class="col-md-9">
                    <select id="selectObjetoParcial" name="selectObjetoParcial" class="form-control selectGeneral"></select>
                    </div>
                </div>
            	
            </div>

            <div class="col-md-6 col-xs-6">
			<br>
            	<component-text-label label="Variable: " id="metros" name="metros" placeholder="Variable"></component-text-label>
            </div>
			<div class="col-md-6 col-xs-6">  
                  <div class="form-inline row">
				  <br>

                    <div class="col-md-3">
                        <span><label>Año: </label></span>
                    </div>
                    <div class="col-md-9">
                    <select id="anio" name="anio" class="form-control selectGeneral">
                            <option value="<?php echo date("Y"); ?>"><?php echo date("Y"); ?></option>
                    </select>
                    </div>
                </div>
			
          	</div>
			<div class="col-md-6 col-xs-6">  
				<div class="form-inline row">
					<br>

					<div class="col-md-3">
						<span><label>Estatus: </label></span>
					</div>
					<div class="col-md-9">
						<select id="selectEstatus" name="selectEstatus" class="form-control selectGeneral">
								<option value="1">Activo</option>
								<option value="0">Inactivo</option>
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-xs-6">
				<br>
				<component-number-label label="Descuento: " id="descuento" name="descuento" placeholder="Descuento"></component-number-label>
			</div>
			<div class="col-md-6 col-xs-6">
				<br>
				<component-number-label label="Fecha de Vigencia: " id="dtFechaInicio" name="dtFechaInicio" ></component-number-label>
			</div>
			<div class="col-md-6 col-xs-6">
				<br>
				<component-number-label label="Descuento Extraordinario: " id="descuentoExtraordinario" name="descuentoExtraordinario" placeholder="Descuento Extraordinario"></component-number-label>
            </div>
			<div class="col-md-6 col-xs-6">
				<br>
				<component-number-label label="Fecha de Vigencia: " id="dtFechaInicioExtra" name="dtFechaInicioExtra" ></component-number-label>
			</div>
			
						</div>

						<!-- Botones -->
						<br>
						<div class="row">
							<div class="col-xs-12">
                                <!-- <component-button type="button" id="nuevo" class="glyphicon glyphicon-copy" value="Nuevo"></component-button> -->
								<button type="button" class="btn botonVerde glyphicon glyphicon glyphicon-floppy-disk" id="guardar"> Guardar</button>

							</div>
						</div>
					</div>
				</div><!-- .panel-body -->
			</div><!-- .panel-collapse -->
		</div><!-- .panel -->
	</div><!-- .panel-group -->
</div><!-- / Encabezado --> 

<!-- tabla de busqueda -->
<div class="row">
	<div name="contenedorTabla" id="contenedorTabla" style = "width: 95% !important;" >
		<div name="tablaGrid" id="tablaGrid"></div>
	</div>
</div><!-- .row -->




<?php require 'includes/footer_Index.inc'; ?>
