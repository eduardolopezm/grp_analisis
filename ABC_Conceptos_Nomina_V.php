
<?php
/**
 * Conceptos de Nómina
 *
 * @category Panel
 * @package ap_grp
 * @author Japheth Calzada López <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 09/08/2019
 * Vista para Conceptos de Nómina
 */


//Envio a capa
$PageSecurity = 11;
$funcion = 3061;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
//$title = traeNombreFuncion($funcion, $db);
$title ='Conceptos de Nómina';
require 'includes/header.inc';

include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';


//Librerias GRID
include('javascripts/libreriasGrid.inc');

$idConcepto ='';

if (isset($_GET['id'])) {
    $idConcepto =trim($_GET['id']);
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
  
  var idConcepto = '<?php echo $idConcepto; ?>';
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
            <div class="row">
                <div class="col-md-4">
                    <div class="form-inline row">
                        <div class="col-md-3" style="vertical-align: middle;">
                            <span><label>PP: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select id="pp" name="pp[]" class="form-control pp" >
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-inline row">
                        <div class="col-md-3" style="vertical-align: middle;">
                            <span><label>Partida: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select id="partida" name="partida[]" class="form-control tipoSol" >
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-inline row">
                        <div class="col-md-3" style="vertical-align: middle;">
                            <span><label>Clave Concepto: </label></span>
                        </div>
                        <div class="col-md-9">
                            <input type= "text" id ="claveConcepto" name ="claveConcepto" class="form-control"  onkeypress="return soloNumeros(event)"  >
                        </div>
                    </div>
                </div>
                
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-inline row">
                        <div class="col-md-3" style="vertical-align: middle;">
                            <span><label>Concepto: </label></span>
                        </div>
                        <div class="col-md-9">
                        <input type="text" name = "concepto" id = "concepto" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-inline row">
                        <div class="col-md-3" style="vertical-align: middle;">
                            <span><label>Cuenta Contable: </label></span>
                        </div>
                        <div class="col-md-9">
                            <input type= "text" id ="cuentaContable" name ="cuentaContable" class="form-control"    >
                        </div>
                    </div>
                </div>
                <div class="col-md-4    ">
                    <div class="form-inline row">
                        <div class="col-md-3" style="vertical-align: middle;">
                            <span><label>Tipo Concepto: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select id="tipoConcepto" name="tipoConcepto[]" class="form-control pp" >
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
        <component-button type="submit" id="guardarProd" name="guardarProd" value="Guardar" class="btn btn-primary glyphicon glyphicon-floppy-disk"></component-button>   
        <component-button type="button" id="cancelar" name="cancelar" value="Cancelar" class="btn btn-danger glyphicon glyphicon-remove-sign"></component-button> 
      <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="ABC_Conceptos_Nomina.php" class="btn btn-default glyphicon glyphicon-share-alt"> Regresar</a>
      
    </div>
  </div>
  <br>
  <br>
  <br>


</div>
<script type="text/javascript" src="javascripts/ABC_Conceptos_Nomina_V.js"></script>

<?php require 'includes/footer_Index.inc'; ?>

