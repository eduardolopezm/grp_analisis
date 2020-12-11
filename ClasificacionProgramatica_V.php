<?php
/**
 * Panel de Clasificación Progmatica
 *
 * @category Panel
 * @package ap_grp
 * @author Japheth Calzada López <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 11/07/2018
 * Vista para Captura de Clasificación Progmatica
 */


//Envio a capa
$PageSecurity = 11;
$funcion = 1543;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title ='Captura de Clasificación Programática';

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
//$permiso = Havepermission($_SESSION ['UserID'], 2257, $db);

//Librerias GRID
include('javascripts/libreriasGrid.inc');

$idClave ='';
$idGrupo ='';
if (isset($_GET['id'])) {
    $idClave =trim($_GET['id']);
}
if (isset($_GET['ver'])) {
    $ver =$_GET['ver'];
} elseif (isset($_POST['ver'])) {
    $ver = $_POST['ver'];
} else {
    $ver = '';
}
if (isset($_GET['idGrupo'])) {
    $idGrupo =trim($_GET['idGrupo']);
}

?>
<script type="text/javascript">
  
  var idClave     = '<?php echo $idClave; ?>';
  var funcionVer  = '<?php echo $ver; ?>';
  var idGrupo     = '<?php echo $idGrupo; ?>' ;
</script>
<link rel="stylesheet" href="css/listabusqueda.css" />

<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
<div align="left">
  <!--Panel Busqueda-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne" style="margin-left: 20px;">
            Información Agregar/Modificar
          </a>
      </h4>
    </div>
    <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <div class="row clearfix">
            <!--Clave-->
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3" style="vertical-align: middle;">
                        <span><label>Clave: </label></span>
                    </div>
                    <div class="col-md-9">
                        <input type ="text" id="clave" name="clave" class="form-control clave" maxlength="1" onkeypress="return fnSoloLetras(event);" onkeyup="mayus(this);" >
                    </div>
                </div><!-- .form-inline .row -->
            </div>
            <!--Programa-->
            <div class="col-md-4">
                <div class="">
                    <div class="col-md-2" style="vertical-align: middle;">
                        <span><label>Programa: </label></span>
                    </div>
                    <div class="col-md-10">
                        <!-- <select id="programa" name="programa[]" class="form-control clasfPrograma" >
                        </select> -->
                        <input type ="text" id="programa" name="programa" class="form-control" />
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="">
                    <div class="col-md-2" style="vertical-align: middle;">
                        <span><label>Grupo: </label></span>
                    </div>
                    <div class="col-md-10">
                        <select id="grupo" name="grupo[]" class="form-control clasfGrupo" >
                        </select>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
        <component-button type="submit" id="guardarProd" name="guardarProd" value="Guardar" class="glyphicon glyphicon-floppy-disk"></component-button>   
        <component-button type="button" id="cancelar" name="cancelar" value="Cancelar" class="glyphicon glyphicon-remove-sign"></component-button> 
      <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="ClasificacionProgramatica.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>
      
    </div>
    <!-- <component-button type="button" id="eliminar" name="eliminar" value="Eliminar" class="glyphicon glyphicon-remove-sign"></component-button> -->
  </div>
  <br>
  <br>
  <br>


</div>
<script type="text/javascript" src="javascripts/ClasificacionProgramatica_V.js"></script>
<?php
include 'includes/footer_Index.inc';
?>