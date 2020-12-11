<?php
/**
 * Panel de Bienes y Servicios
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Vista para los Bienes y Servicios
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');


//Envio a capa
$PageSecurity = 11;
$funcion = 81;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
//$title = traeNombreFuncion($funcion, $db);
$title ='Registro de bienes y servicios';

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
//$permiso = Havepermission($_SESSION ['UserID'], 2257, $db);

//Librerias GRID
include('javascripts/libreriasGrid.inc');

$StockID = 0;

if (isset($_GET['StockID'])) {
    $StockID =trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
    $StockID =trim(strtoupper($_POST['StockID']));
} else {
    $StockID = '';
}
if (isset($_GET['mbflag'])) {
    $mbflag =$_GET['mbflag'];
} elseif (isset($_POST['mbflag'])) {
    $mbflag = $_POST['mbflag'];
} else {
    $mbflag = '';
}
if (isset($_GET['PartidaID'])) {
    $PartidaID = $_GET['PartidaID'];
} elseif (isset($_POST['PartidaID'])) {
    $PartidaID = $_POST['PartidaID'];
} else {
    $PartidaID = '';
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
  
  var StockID = '<?php echo $StockID; ?>';
  var PartidaID = '<?php echo $PartidaID; ?>';
  var Tipo = '<?php echo $mbflag; ?>';
  var funcionVer = '<?php echo $ver; ?>';

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
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Tipo: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="selectTipoProducto" name="selectTipoProducto" class="form-control selectTipoProducto">
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Partida Especifica: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="selectPartidaEspecifica" name="selectPartidaEspecifica" class="form-control selectPartidaEspecifica">
                            <option value="0">Sin Seleccción ...</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>CABMS: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="selectCabms" name="selectCabms" class="form-control selectCabms">
                            <option value="0">Sin Seleccción ...</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="row clearfix">
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Código: </label></span>
                    </div>
                    <div class="col-md-9">
                        <component-text id="StockID" name="StockID" maxlength="20" placeholder="Código" title="Código" value="" class="w100p form-control"></component-text>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Familias: </label></span>
                    </div>
                    <div class="col-md-9">
                        <input id="Familia" name="Familia" class="Familia form-control w100p" type="text" maxlength = "5" value="" placeholder="Familia">
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Estatus: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="status" name="status" class="form-control status">
                            <option value="0">Activo</option>
                            <option value="1">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="row clearfix">
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Unidad de Medida: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="units" name="units" class="form-control units">
                            <option value="0">Sin selección...</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <component-text-label label="Descripción Corta:" id="description" name="description" maxlength="50" placeholder="Descripción Corta" title="Descripción Corta" value="<?php echo $_POST['Description'] ?>"></component-text-label>
            </div>
            <div class="col-md-4">
                <component-textarea-label label="Descripción Larga: " id="longDescription" name="longDescription" placeholder="Descripción Larga" title="Descripción Larga" cols="3" rows="4" maxlength= "250" value="<?php echo $_POST['LongDescription'] ?>"></component-textarea-label>
            </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
        <component-button type="submit" id="guardarProd" name="guardarProd" value="Guardar" class="glyphicon glyphicon-floppy-disk"></component-button>   
        <component-button type="button" id="cancelar" name="cancelar" value="Cancelar" class="glyphicon glyphicon-remove-sign"></component-button> 
      <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="SelectProduct.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>
      
    </div>
    <!-- <component-button type="button" id="eliminar" name="eliminar" value="Eliminar" class="glyphicon glyphicon-remove-sign"></component-button> -->
  </div>
  <br>
  <br>
  <br>


</div>
<script type="text/javascript" src="javascripts/Stocks.js"></script>
<?php
include 'includes/footer_Index.inc';
?>