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
$funcion = 2510;

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

<link rel="stylesheet" href="css/listabusqueda.css" />
<link rel="stylesheet" href="css/v3/librerias/jquery-ui/jquery-ui-1.11.4/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="css/v3/librerias/bootstrap-3.3.7/css/bootstrap.min.css">
<script type="text/javascript" src="javascripts/paseCobro.js?v=<?= rand();?>"></script>
<input id="contratosID" type="text" class="hidden" value="<?php echo $_GET['id_contratos']; ?>">



<!--Modal Agregar/Modificar -->
<div class="modal fade" id="ModalUR" name="ModalUR" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
    <div class="modal-content">
      <div class="navbar navbar-inverse navbar-static-top">
        <!--Contenido Encabezado-->
        <div class="col-md-12 menu-usuario">
          <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <div class="nav navbar-nav">
            <div class="title-header">
              <div id="ModalUR_Titulo" name="ModalUR_Titulo"></div>
            </div>
          </div>
        </div>
        <div class="linea-verde"></div>
      </div>
      <div id="divMensajeOperacion" name="divMensajeOperacion"></div>
      <div class="modal-body" id="ModalUR_Mensaje" name="ModalUR_Mensaje">
        <!--Mensaje o contenido-->
        <div id="msjValidacion" name="msjValidacion"></div>
		
					
					
    
            <div class="col-md-6">
			<input type="hidden" id="txtIdAdminContrato" name="txtIdAdminContrato" placeholder="id">
            <component-decimales-label label="Importe: "  id="txtImporte" name="txtImporte" placeholder="Importe"></component-decimales-label>
            </div>

			<div class="col-md-6">
            <component-text-label label="Motivo de Cambio: " id="txtComentario" name="txtComentario" placeholder="Motivo de cambio"></component-text-label>
            </div>
          
            </br>
            </br>


      </div>

      
      <div class="modal-footer">
        <component-button type="button" id="btn" name="btn" onclick="fnModificarImporte()" value="Guardar"></component-button>
        <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
      </div>
    </div>
  </div>
</div>


<!-- botones de accion -->
<div class="row pt10">
	<div class="panel panel-default">
		<div class="panel-body" align="center">
			<div class="row">
				<div class="col-md-4 col-xs-6">
					<component-text-label  label="Folio Contrato:" value="<?php echo $_GET['id_contratos']?>" readonly></component-text-label>
				</div>
				<div class="col-md-4 col-xs-6">
					<component-text-label id ="contribuyente" label="Contribuyente:"  value="<?php echo $_GET['name']?>" readonly></component-text-label>
				</div>
			</div>
			<br>										
			<!-- <button type="button" class="btn botonVerde glyphicon glyphicon glyphicon-chevron-left" id="regresar"> Regresar</button> -->
			<component-button type="button" id="nuevo" class="glyphicon glyphicon-copy" value="Generar Pase de Cobro"></component-button>
		</div>
	</div> 
</div><!-- .row -->

<!-- tabla de busqueda -->
<div class="row">
	<div name="contenedorTabla " id="contenedorTabla" style = "width: 95% !important;">
		<div name="tablaGrid" id="tablaGrid"></div>
	</div>
</div><!-- .row -->




<?php require 'includes/footer_Index.inc'; ?>
