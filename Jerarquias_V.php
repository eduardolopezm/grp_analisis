<?php
/**
 * Panel de Bienes y Servicios
 *
 * @category Panel
 * @package ap_grp
 * @author Japheth Calzada López <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 05/07/2018
 * Vista para Captura de Tabulador
 */


//Envio a capa
$PageSecurity = 11;
$funcion = 2402;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
//$title = traeNombreFuncion($funcion, $db);
$title ='Captura de Tabulador';

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
//$permiso = Havepermission($_SESSION ['UserID'], 2257, $db);

//Librerias GRID
include('javascripts/libreriasGrid.inc');

$idJerarquia ='';

if (isset($_GET['id'])) {
    $idJerarquia =trim($_GET['id']);
}
if (isset($_GET['ver'])) {
    $ver =$_GET['ver'];
} elseif (isset($_POST['ver'])) {
    $ver = $_POST['ver'];
} else {
    $ver = '';
}

?>
<script type="text/javascript">
  
  var idJerarquia = '<?php echo $idJerarquia; ?>';
  var funcionVer  = '<?php echo $ver; ?>';
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
            <!--Jerarquias-->
            <div class="col-md-3">
                <div class="form-inline row">
                    <div class="col-md-3" style="vertical-align: middle;">
                        <span><label>Jerarquía: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="jerarquia" name="jerarquia[]" class="form-control jerarquia" >
                        </select>
                    </div>
                </div><!-- .form-inline .row -->
            </div>
            <!--Tipo de Comisión-->
            <div class="col-md-3">
                <div class="form-inline row">
                    <div class="col-md-3" style="vertical-align: middle;">
                        <span><label>Tipo de Comisión: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="tipoSol" name="tipoSol[]" class="form-control tipoSol" >
                        </select>
                    </div>
                </div>
            </div>
            <!--Zona Economica -->
            <div class="col-md-3">
                <div class="form-inline row">
                    <div class="col-md-3" style="vertical-align: middle;">
                        <span><label>Zona Económica: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="tipoGasto" name="tipoGasto[]" class="form-control tipoGasto" >
                        </select>
                    </div>
                </div>
            </div><!-- .col-md-3 -->
            <!--Monto-->
            <div class="col-md-3">
                <div class="form-inline row">
                    <div class="col-md-3" style="vertical-align: middle;">
                        <span><label>Monto: </label></span>
                    </div>
                    <div class="col-md-9">
                        <input type= "text" id ="monto" name ="monto" class="form-control" onkeypress="return soloNumeros(event)">
                    </div>
                </div>
            </div><!-- .col-md-3 -->
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
        <component-button type="submit" id="guardarProd" name="guardarProd" value="Guardar" class="glyphicon glyphicon-floppy-disk"></component-button>   
        <component-button type="button" id="cancelar" name="cancelar" value="Cancelar" class="glyphicon glyphicon-remove-sign"></component-button> 
      <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="ABC_Jerarquias.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>
      
    </div>
    <!-- <component-button type="button" id="eliminar" name="eliminar" value="Eliminar" class="glyphicon glyphicon-remove-sign"></component-button> -->
  </div>
  <br>
  <br>
  <br>


</div>
<script type="text/javascript" src="javascripts/Jerarquias_V.js"></script>
<?php
include 'includes/footer_Index.inc';
?>