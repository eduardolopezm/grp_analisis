<?php
/**
 * Carga misaba de estructuras
 *
 * @category 
 * @package ap_grp
 * @version 0.1
 * @file: altaMasivaEstructuras.php
 * Fecha Creación: 26/12/2017
 * Programa diseñada para realizar la carga de los catálogos de estructuras como son:
 * Programatica.
 * Economica.
 * Administrativa.
 * y la relación de pp la partida
 */

/* DECLARACION DE VARIABLES */
	$PageSecurity=5;
	$funcion=2244;
/* DECLARACION DE VARIABLES */

/* INCLUCION DE ARCHIVOS */
	include('includes/session.inc');
	$title = traeNombreFuncion($funcion, $db);
	include('includes/header.inc');
	include('includes/SecurityFunctions.inc');
	include('includes/SQL_CommonFunctions.inc');
/* INCLUCION DE ARCHIVOS */


/* INCLUCION DE ARCHIVOS DE LIBRERIAS */
include('javascripts/libreriasGrid.inc');
?>

<script src="javascripts/altaMasivaEstructurasControlador.js?v=0.0.1"></script>

<div class="row pb10">
	<div class="col-sm-12">
		<div class="col-sm-6 col-sm-offset-3">
			<form class="form-inline" id="form-carga">
				<div class="form-group">
					<lable>Seleccione un archivo</lable>
					<input type="file" class="form-control" id="archivos" name="archivos" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" style="border:none;" >
					<button class="btn btn-primary btn-green" id="btn-carga"><i class="fa fa-cloud-upload"></i> Cargar</button>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="row">
	<div id="mensaje" name="mensaje" class="jumbotron">
		<h3>Seleccione un archivo para comenzar</h3>
	</div>
</div>
<?php
	/* INCLUCION DEL FOOTER */
	include 'includes/footer_Index.inc';
?>