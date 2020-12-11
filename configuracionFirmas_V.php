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
 * Vista para Captura de Configuración de firmas
 */


//Envio a capa
$PageSecurity = 11;
$funcion = 2404;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title ='Captura de Firmas';

include 'includes/header.inc';
header ("Cache-Control: no-cache, must-revalidate"); 
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
//$permiso = Havepermission($_SESSION ['UserID'], 2257, $db);

//Librerias GRID
include('javascripts/libreriasGrid.inc');

$idFirma    = '';
$modificar  = ''; 
$ver        = ''; 
if (isset($_GET['id'])) {
    $idFirma =trim($_GET['id']);
}
if (isset($_GET['ver'])) {
    $ver =$_GET['ver'];
}
elseif (isset($_POST['ver'])) {
    $ver = $_POST['ver'];
} else {
    $ver = '';
}


if (isset($_GET['modificar'])) {
    $modificar =$_GET['modificar'];
} 
?>
<script type="text/javascript">
  
  var idFirma     = '<?php echo $idFirma; ?>';
  var funcionVer  = '<?php echo $ver; ?>';
  var modificar   = '<?php echo $modificar; ?>';
  
</script>
<link rel="stylesheet" href="css/listabusqueda.css" />

<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">
    <div class="panel-heading h35">
        <h4 class="panel-title">
            <div class="fl text-left">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#datosGenerales" aria-expanded="true" aria-controls="collapseOne">
                    <strong> Información Agregar/Modificar</strong>
                </a>
            </div>
        </h4>
    </div>
    <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <div class="row clearfix">
            <div class="col-md-4">
                <div class="col-md-3" style="vertical-align: middle;">
                    <span><label>UR: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectUnidadNegocio" name="selectUnidadNegocio" data-todos="true" class="form-control selectUnidadNegocio"
                    onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')"></select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="col-md-3" style="vertical-align: middle;">
                    <span><label>UE: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" data-todos="true" class="form-control selectUnidadEjecutora"></select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                            <span><label>Reporte: </label></span>
                    </div>
                    <div class="col-md-9">
                            <select id="selectReportes" name="selectReportes" class="form-control selectReportesFirmas" >
                            </select>
                    </div>
                </div>
            </div>
        </div><br>
        <div name="divTabla" id="divTabla">
            <div name="divContenidoTabla" id="divContenidoTabla"></div>
        </div> 
        <br>
      </div>
    </div>
    <!--Modal/Modificar-->
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
            <div name="divMensajeOperacion" id="divMensajeOperacion" class="m10"></div>
            <div class="modal-body" id="ModalUR_Mensaje" name="ModalUR_Mensaje">
                <div id="mensajesValidaciones" name="mensajesValidaciones"></div>
                    <div class="form-inline row">
                        <div class="col-md-3 col-xs-12">
                            <span><label>UR: </label></span>
                        </div> 
                        <div class="col-md-9 col-xs-12">
                        <select id="UnidadNegocio" name="UnidadNegocio" data-todos="true" class="form-control selectUnidadNegocio"
                    onchange="fnTraeUnidadesEjecutoras(this.value, 'UnidadEjecutora')"></select>
                        </div>
                    </div><br>
                    <div class="form-inline row">
                        <div class="col-md-3 col-xs-12">
                            <span><label>UE: </label></span>
                        </div> 
                        <div class="col-md-9">
                    <select id="UnidadEjecutora" name="UnidadEjecutora" data-todos="true" class="form-control selectUnidadEjecutora"></select>
                </div>
                    </div>
                    <br>
                    <div class="form-inline row">
                        <div class="col-md-3 col-xs-12">
                            <span><label>Usuario: </label></span>
                        </div> 
                        <div class="col-md-9 col-xs-12">
                            <select id="selectUsuario" name="selectUsuario" data-todos="true" class="form-control selectUsuarioFirma"></select>
                        </div>
                    </div><br>
                    <div class="form-inline row">
                        <div class="col-md-3 col-xs-12">
                            <span><label>Puesto: </label></span>
                        </div> 
                        <div class="col-md-9 col-xs-12">
                            <select id="selectPuesto" name="selectPuesto" data-todos="true" class="form-control selectPuesto"></select>
                        </div>
                    </div>
                    <br>
                        <component-text-label label="Titulo: " id="titulo" name="titulo" placeholder="Titulo"></component-text-label>
                    <br>
                        <component-text-label label="Información: " id="informacion" name="informacion" placeholder="Información"></component-text-label>
                    <br>
                    <div class="modal-footer">
                        <component-button type="button" id="btn" name="btn" onclick="fnAgregar()" value="Guardar"></component-button>
                        <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar" ></component-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-body" align="center" id="divBotones" name="divBotones">
            <component-button type="submit" id="guardarProd" name="guardarProd" value="Guardar" class="glyphicon glyphicon-floppy-disk"></component-button>   
            <component-button type="button" id="firmante" name="firmante" onclick="fnAgregarCatalogoModal()" value="Agregar Firmante" class="glyphicon glyphicon-plus"></component-button> 
            <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="configuracionFirmas.php" class="btn btn-default botonVerde glyphicon glyphicon-arrow-left"> Regresar</a>
        
        </div>
    <!-- <component-button type="button" id="eliminar" name="eliminar" value="Eliminar" class="glyphicon glyphicon-remove-sign"></component-button> -->
  </div>
  <br>
  <br>
  <br>
  </div>
  </div>
</div>
<script type="text/javascript" src="javascripts/configuracionFirmas_V.js?v=<?= rand();?>"></script>
<script type="text/javascript" src="javascripts/imprimirreportesconac.js?v=<?= rand();?>"></script>
<?php
include 'includes/footer_Index.inc';
?>