<?php
/**
 * ABC de Almacén
 *
 * @category	panel
 * @package		ap_grp
 * @author		Jesus Reyes Santos <[<email address>]>
 * @license		[<url>] [name]
 * @version		GIT: [<description>]
 * Fecha Creación: 25/09/2017
 * Fecha Modificación: 25/09/2017
 * Se realizan operación pero el Alta, Baja y Modificación. Mediante las validaciones creadas para la operación seleccionada
 */

/* DECLARACION DE VARIABLES */
$PageSecurity = 5;
$PathPrefix = './';
$funcion = 2307;

/* INCLUSIÓN DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/SecurityUrl.php');
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
$title = traeNombreFuncion($funcion, $db);

# Carga de archivos secundarios
$_GET['modal'] = true;
include('includes/header.inc');
require 'javascripts/libreriasGrid.inc';

# función para ocultamiento de dependencia
$ocultaDepencencia = 'hidden';

$id_bienes = "";
if(isset($_GET['id_bienes'])) {
	$id_bienes = $_GET['id_bienes'];
} elseif(isset($_POST['id_bienes'])) {
	$id_bienes = $_POST['id_bienes'];
}

if(isset($_POST['guardar'])){

	$rutaServidor="uploads";
    $rutaTemporal= $_FILES['imagen']['tmp_name'];
    $nombreImagen= $_FILES['imagen']['name'];
    $rutaDestino= $rutaServidor.'/'.$nombreImagen;
    move_uploaded_file($rutaTemporal, $rutaDestino);
    
    $id = (isset($_POST['id_adjunto'])) ? $_POST['id_adjunto'] : null;
	$nombre = (isset($_POST['nombre'])) ? $_POST['nombre'] : null;
	$id2 = (isset($_POST['id_adjunto3'])) ? $_POST['id_adjunto3'] : null;
    
    // echo $id;
    // echo "<br>". $nombre;
	// echo "<br>".$rutaDestino;

	if($id = null || $id = " " || $id = 0 ){
		$SQL = "INSERT INTO `fixedassets_adjuntos` (`assetid`, `adjunto`, `nombre`, `ind_activo`)
		VALUES ('".$id2."', '".$rutaDestino."', '".$nombre."', 1)";
		$ErrMsg = "No se agrego la informacion de ". $nombre;
		$TransResult = DB_query($SQL, $db, $ErrMsg);

	}else{

		$SQL = "INSERT INTO `fixedassets_adjuntos` (`assetid`, `adjunto`, `nombre`, `ind_activo`)
		VALUES ('".$id."', '".$rutaDestino."', '".$nombre."', 1)";
		$ErrMsg = "No se agrego la informacion de ". $nombre;
		$TransResult = DB_query($SQL, $db, $ErrMsg);
	}
	
	

	?>

    <input id="id_adjunto" name="id_adjunto" class="form-control hidden"   value="<?php echo $_POST['id_adjunto']; ?>" type="text" style="width: 100%;">
	<input id="id_adjuntoTabla" name="id_adjuntoTabla" class="form-control hidden"   value="<?php echo $_POST['id_adjunto3']; ?>" type="text" style="width: 100%;">

<?php
}

?>

<!-- <link rel="stylesheet" href="css/listabusqueda.css" />
<script type="text/javascript" src="lib/bootstrap/js/3.3.6/bootstrap.min.js"></script>
<link rel="stylesheet" href="css/v3/librerias/jquery-ui/jquery-ui-1.11.4/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="css/v3/librerias/bootstrap-3.3.7/css/bootstrap.min.css"> -->
<script type="text/javascript" src="javascripts/abcAdjuntos.js"></script>



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
                <form method="POST" action="abcAdjuntos.php" enctype="multipart/form-data">

					<div class="modal-body OverdeSelectsenModales fix-min-height" id="forma" name="forma" >
						<div class="row">
							<!-- UR, UE -->
					<div class="col-md-4 col-xs-4">
						<div class="form-inline row">
							<div class="col-md-3 col-xs-12">
								<span><label>Identificador: </label></span>
							</div>
							<div class="col-md-9 col-xs-12">
								<input id="id_adjunto" class="form-control hidden"  name="id_adjunto" value="<?php echo $id_bienes; ?> " type="text" style="width: 100%;">
								<input id="id_adjunto2" class="form-control"  name="id_adjunto2"  value="<?php echo $id_bienes; ?>" readonly type="text" style="width: 100%;">
								<input id="id_adjunto3" class="form-control hidden"  name="id_adjunto3" type="text" style="width: 100%;">
							</div>
						</div>
						<br>
					</div>


					<div class="col-md-4 col-xs-4"> 
					<label for="exampleInputEmail1">Archivo</label>
					<input class="form-control" type="file" id="imagen" name="imagen" size="50" />
					</div>

	

					<div class="col-md-4 col-xs-4"> 
					<component-text-label label="Nombre del Archivo:" id="nombre" name="nombre" placeholder="Nombre del Archivo"></component-text-label>
					</div>
			

						<!-- Botones -->
						<br>
						<div class="row">
							<div class="col-xs-12">
                                <!-- <component-button type="button" id="nuevo" class="glyphicon glyphicon-copy" value="Nuevo"></component-button> -->
								<button type="submit" class="btn botonVerde" name="guardar" id="guardar">Guardar</button>

							</div>
						</div>
					</div>

                </form>
				</div><!-- .panel-body -->
			</div><!-- .panel-collapse -->
		</div><!-- .panel -->
	</div><!-- .panel-group -->
</div><!-- / Encabezado --> 





<!--Modal Eliminar -->
<div class="modal fade" id="ModalUREliminar" name="ModalUREliminar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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
              <h3><span class="glyphicon glyphicon-info-sign"></span> Información</h3>
            </div>
          </div>
        </div>
        <div class="linea-verde"></div>
      </div>
      <div class="modal-body" id="ModalUREliminar_Mensaje" name="ModalUREliminar_Mensaje">
        <!--Mensaje o contenido-->
      </div>
      <div class="modal-footer">
        <component-text type="hidden" label="Finalidad: " id="idEliminar" name="idEliminar" placeholder="Finalidad"></component-text>
        <component-text type="hidden" label="Fución: " id="ruta" name="ruta" placeholder="Función"></component-text>
		<component-text type="hidden" label="Fución: " id="idTabla" name="idTabla" placeholder="Función"></component-text>
        <component-button type="button" id="btn" name="btn" onclick="fnEliminarEjecuta()" value="Eliminar"></component-button>
        <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
      </div>
    </div>
  </div>
</div>


<!-- tabla de busqueda -->
<div class="row">
  <div name="divTabla" id="divTabla" style = "width: 95% !important;">
    <div name="divContenidoTabla" id="divContenidoTabla"></div>
  </div>
</div><!-- .row -->

<?php require 'includes/footer_Index.inc'; ?>
