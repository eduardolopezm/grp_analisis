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
<script type="text/javascript" src="javascripts/abcAtributosContrato.js?v=<?= rand();?>"></script>



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
					<div class="modal-body OverdeSelectsenModales fix-min-height" id="forma" name="forma" >
						<div class="row">
							<!-- UR, UE -->
							<div class="col-md-6 col-xs-6">
                <div class="form-inline row">
                    <div class="col-md-3 col-xs-12">
                        <span><label>Contrato: </label></span>
                    </div>
                    <div class="col-md-9 col-xs-12">
						<input id="contrato" class="form-control hidden"  name="contrato" value="<?php echo $_GET['id_contratos']; ?>" type="text" style="width: 100%;">
						<input id="objetoPrincipal" class="form-control"  value="<?php echo $_GET['id_contratos']; ?> - <?php echo $_GET['id_loccode']; ?>" readonly type="text" style="width: 100%;">
                    </div>
                </div>
				<br>
            </div>


            <div class="col-md-6 col-xs-6"> 
            	<component-text-label label="Etiqueta: " id="etiqueta" name="etiqueta" placeholder="Etiqueta"></component-text-label>
            </div>
						</div>

						<!-- Botones -->
						<br>
						<div class="row">
							<div class="col-xs-12">
                                <!-- <component-button type="button" id="nuevo" class="glyphicon glyphicon-copy" value="Nuevo"></component-button> -->
								<button type="button" class="btn botonVerde" id="guardar">Guardar</button>

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
	<div name="contenedorTabla" id="contenedorTabla" style = "width: 95% !important;">
		<div name="tablaGrid" id="tablaGrid"></div>
	</div>
</div><!-- .row -->




<?php require 'includes/footer_Index.inc'; ?>
