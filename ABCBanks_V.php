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
 * Fecha Creación: 15/08/2018
 * Vista para Altas, Bajas y Modificaciones de Bancos
 */


//Envio a capa
$PageSecurity = 11;
$funcion = 1304;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
//$title = traeNombreFuncion($funcion, $db);
$title ='Altas, Bajas y Modificaciones de Bancos';

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
//$permiso = Havepermission($_SESSION ['UserID'], 2257, $db);

//Librerias GRID
include('javascripts/libreriasGrid.inc');

$idBanco   ='';
$modificar = '';
if (isset($_GET['id'])) {
    $idBanco =trim($_GET['id']);
}
if (isset($_GET['ver'])) {
    $ver =$_GET['ver'];
} elseif (isset($_POST['ver'])) {
    $ver = $_POST['ver'];
} else {
    $ver = '';
}

if (isset($_GET['modificar'])) {
    $modificar =$_GET['modificar'];
} elseif (isset($_POST['modificar'])) {
    $modificar = $_POST['modificar'];
} else {
    $modificar = '';
}

?>
<script type="text/javascript">
  
  var idBanco     = '<?php echo $idBanco; ?>';
  var funcionVer  = '<?php echo $ver; ?>';
  var modificar  = '<?php echo $modificar; ?>';
</script>
<link rel="stylesheet" href="css/listabusqueda.css" />

<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
<div align="left">
  <!--Panel Busqueda-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne" style="margin-left: 20px;">
          Altas, Bajas y Modificaciones de Bancos
          </a>
      </h4>
    </div>
    <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3" style="vertical-align: middle;">
                        <span><label>Banco: </label></span>
                    </div>
                    <div class="col-md-9" id ="contenedorBanco">
                         <?php 
                        if ( $ver != ''){?>
                            <select id="banco" name="banco[]" class="form-control banco" multiple="multiple">   
                            </select>
                        <?php } else{?>
                            <input type="text" id="txtBanco" name="txtBanco" class="form-control descripcion">
                        <?php } ?>
                        
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3" style="vertical-align: middle;">
                        <span><label>Nombre: </label></span>
                    </div>
                    <div class="col-md-9" id ="contenedorDescripcion">
                        <?php 
                        if ( $ver != ''){?>
                            <select id="descripcion" name="descripcion[]" class="form-control descripcion" >
                            </select>
                        <?php } else{?>
                            <input type="text" id="txtDescripcion" name="txtDescripcion" class="form-control descripcion">
                        <?php } ?>
                    </div>
                </div>
            </div><!-- .col-md-4 -->
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3" style="vertical-align: middle;">
                        <?php if ($modificar == ''){ ?>
                        <span><label>Clave: </label></span>
                        <?php } else {?>
                            <span><label>Estatus: </label></span>
                        <?php } ?>
                    </div>
                    <div class="col-md-9" id="contenedorClave">
                    <?php 
                        if ( $ver != ''){?>
                            <select id="clave" name="clave[]" class="form-control clave" multiple="multiple">
                            </select>
                        <?php } else if ($modificar != '') {?>
                            <select id="estatus" name="estatus" class="form-control estatus">
                            </select>
                        <?php } else { ?>
                            <input type="text" id="txtClave" name="txtClave" class="form-control clave" maxlength="3" onkeypress="return soloNumeros(event)">
                        <?php }  ?> 
                    </div>
                </div><!-- .form-inline .row -->

                <br>
            </div>              
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
        <component-button type="submit" id="guardarProd" name="guardarProd" value="Guardar" class="glyphicon glyphicon-floppy-disk"></component-button>   
        <!-- <component-button type="button" id="cancelar" name="cancelar" value="Cancelar" class="glyphicon glyphicon-remove-sign"></component-button>  -->
      <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="ABCBanks.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>
      
    </div>
    <!-- <component-button type="button" id="eliminar" name="eliminar" value="Eliminar" class="glyphicon glyphicon-remove-sign"></component-button> -->
  </div>
  <br>
  <br>
  <br>


</div>
<script type="text/javascript" src="javascripts/ABCBanks_V.js"></script>
<?php
include 'includes/footer_Index.inc';
?>