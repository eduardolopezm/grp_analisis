<?php
/**
 * Captura de la Requisición
 *
 * @category     ABC
 * @package      ap_grp
 * @author       Luis Aguilar Sandoval <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Se realizan operación de captura de la Requisición.
 */
////
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
$PageSecurity = 4;

include "includes/SecurityUrl.php";
require 'includes/DefinePOClass.php';
require 'includes/session.inc';

$title   = "";
$funcion = 46;
//$title = "Requisici&oacute;n";
$title = "Transferencias Entrada";

/*if (isset($_GET['ModifyOrderNumber'])) {
$req = $_GET['ModifyOrderNumber'];
}*/
/*if (isset($_GET['ModifyOrderNumber'])) {
$title = _('Modificar Captura Requisición') . ' ' . $_GET['ModifyOrderNumber'];
//$req = $_GET['ModifyOrderNumber'];
} else {
$title = _('Captura Requisición');
}*/

require 'includes/header.inc';
//$funcion = 2265;
require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';


//Librerias GRID
// require 'javascripts/libreriasGrid.inc';
//$permiso = Havepermission ( $_SESSION ['UserID'], 2257, $db );
//
//
    /*$enc = new Encryption;
    $url = "&SelectedUser=>" . $myrow[0] . "&funcion=>1";
    $url = $enc->encode($url);
    $liga= "URL=" . $url;*/

$periodoRequisicion = GetPeriod(date('d/m/Y'), $db);
$datoNoReq = 0;
$datoIdRequisicion = 0;

if (isset($_GET['ModifyOrderNumber'])) {
    $datoIdRequisicion = $_GET['ModifyOrderNumber'];
}

if (isset($_GET['idrequisicion'])) {
    $datoNoReq = $_GET['idrequisicion'];
}

$enc = new Encryption;
        $url = "&ModifyOrderNumber=>" . $_GET['ModifyOrderNumber'] . "&idrequisicion=> ". $_GET['idrequisicion'];
        $url = $enc->encode($url);
        $liga= "URL=" . $url;

echo "<input type='text' id='txtOrderno' class='hide' value='".$datoIdRequisicion."'>";
?>

<script type="text/javascript">
  $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});
  var noRequisicionGeneral = '<?php echo $datoIdRequisicion; ?>';
  var periodoReq = '<?php echo $periodoRequisicion; ?>';
  var urlCReq = '<?php echo $liga; ?>';
  var producto = "B";
  var servicio = "D";
  var instrumental = "I";

</script>
<script type="text/javascript" src="javascripts/StockLocTransferReceive_V.js?v=<?php echo rand(); ?>"></script>
<div id="urlEncriptadaRequisicion" class="hide"><input id="urlEncriptadaRequisicionInput" type="text"></div>
<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
<div id="msjValidacion" name="msjValidacion"></div>
<div id="modal-obs">
  <div id="modal-obs-content">
    <div id="modal-obs-top">
      <div class="navbar navbar-inverse navbar-static-top">
        <div class="col-md-12 menu-usuario">
          <span data-dismiss="modal" id="closeModalRequi" class="closeModalRequi glyphicon glyphicon-remove"></span>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <div class="nav navbar-nav">
            <div class="title-header">
              <div id="ModalGeneral_Titulo" name="ModalGeneral_Titulo">
                <h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Observaciones</p></h3>
              </div>
            </div>
          </div>
        </div>
        <div class="linea-verde"></div>
      </div>
    </div>
    <div id="modal-obs-main">
      <ul class="nav nav-tabs" role="tablist">
        <li id="noExistencia" role="presentation" class="active">
          <a href="#idNoExistenciaModalTab" aria-controls="Existencia" role="tab" data-toggle="tab" title="No Existencia" class="bgc10">No Existencia</a>
        </li>
        <li id="suficienciaPresupuestal" role="presentation">
          <a href="#idSufPresupuestalModalTab" aria-controls="Suficiencia" role="tab" data-toggle="tab" title="Suficiencia Presupuestal" class="bgc10">Suficiencia Presupuestal</a>
        </li>
        <li id="solicitudAlmacen" role="presentation">
          <a href="#idSolAlmacenModalTab" aria-controls="Solicitud" role="tab" data-toggle="tab" title="Solicitud Almacén" class="bgc10">Solicitud Almacén</a>
        </li>
      </ul>
      <div id="modal-obs-Tab-content" class="tab-content">
        <div id="idNoExistenciaModalTab" role="tabpanel" class="p5 noExistenciaModalTab tab-pane active"></div>
        <div id="idSufPresupuestalModalTab" role="tabpanel" class="p5 sufPresupuestalModalTab tab-pane"></div>
        <div id="idSolAlmacenModalTab" role="tabpanel" class="p5 solAlmacenModalTab tab-pane"></div>
      </div>
    </div>
    <div id="modal-obs-foot">
      <button id="btnCloseModalRequi" class="closeModalRequi botonVerde">Cerrar</button>
    </div>
  </div>
</div>

<div class="container" id="idContentRequisicion">
  <div id="top-cotainer" class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 panel panel-default p0 m0">
      <div class="panel-heading h35" role="tab" id="headingOne">
        <h4 class="panel-title">
          <div class="fl text-left">
            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#idPanelRequisiciones" aria-expanded="true" aria-controls="collapseOne">
            </a>
          </div>
          <div id="idStatusReq" class="fr text-right hide ftc7">
            <span>Estatus de la Requisición: </span><input type="hidden" id="idperfilusr">
            <label id="statusReq" style="display: none;"></label>
            <label id="statusReqVisual"></label>
          </div>
        </h4>
      </div>
      <div id="idPanelEntrada" class="row panel-collapse collapse in ptb5" role="tabpanel" aria-labelledby="headingOne">
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3" style="vertical-align: middle;">
                        <span><label>Almacén Destino: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="almacen" name="almacen[]" class="form-control almacen" >
                            
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3" style="vertical-align: middle;">
                        <span><label>Tipo de Bien: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="tipoBien" name="tipoBien[]" class="form-control tipoBien" >
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3" style="vertical-align: middle;">
                        <span><label>Folio: </label></span>
                    </div>
                    <div class="col-md-9">
                       
                    </div>
                </div>
            </div>
      </div>
    </div>
  </div>
  <br/>
  <div id="main-container" class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 p0 m0">
      <!-- Tab panes -->
      <div id="idTab-content" class="tab-content">
        <div id="idArticuloContainerTab" role="tabpanel" class="instrumentalContainerTab tab-pane active">
          <nav id="idNavArticulos" class="row nav bgc8 fts10 borderGray w100p ftc2">
            <div id="navArticulos" class="col-lg-12 col-md-12 col-sm-12 p0 m0">
              <div class="w5p pt3">
                <!-- onclick="fnValidaExistenciaRequisicion(producto);" -->
                <span id="idBtnAgregarArticulo" class="glyphicon glyphicon-plus btn btn-default btn-xs"></span>
              </div>
              <div class="w5p"><label >Nº</label></div>
              <div class="w10p"><label >Partida</label></div>
              <div class="w10p"><label >Clave artículo</label></div>
              <!-- @NOTE: Cambio de tamaño conforme a solicitud @date:11.04.18 -->
              <!-- <div class="w40p"><label >Artículo</label></div> -->
              <div class="w20p"><label >Artículo</label></div>
              <div class="w10p"><label >Unidad Medida</label></div>
              <div class="w5p"><label >Cantidad</label></div>
              <!-- @NOTE: Cambio de tamaño conforme a solicitud @date:11.04.18 -->
              <!-- <div class="w5p"><label >Precio</label></div> -->
              <div class="w8p"><label >Cantidad Transferida</label></div>
              <!-- @NOTE: Cambio de tamaño conforme a solicitud @date:11.04.18 -->
              <!-- <div class="w5p"><label >Total</label></div> -->
              <div class="w25p"><label >observaciones</label></div>

            </div>
          </nav>
          <div id="idMainListContentArticulo" class="borderGray m0 p0">
          </div>
        </div>
      </div>
    </div>
  </div>
  <br/>
  <div id="foot-container" class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 p0 m0">
      <div class="panel panel-default">
        <br>
        <div class="panel-body" align="center" id="divBotones" name="divBotones" >
          <button type="button" id="idBtnGuardarCR" name="btnGuardarCR"  class="btn btn-default botonVerde glyphicon glyphicon-floppy-disk hide">&nbsp;Guardar</button>
          <button type="button" id="idBtnCancelarCR" name="btnCancelarCR" onclick="fnCancelarRequisicion()" class="btn btn-default botonVerde glyphicon glyphicon-trash hide">&nbsp;Cancelar</button>
          <button type="button" id="idBtnRegresarCR" name="btnRegresarCR" onclick="fnRegresarPanelRequisicion()" class="btn btn-default botonVerde glyphicon glyphicon-home">&nbsp;Regresar</button>
        </div>
      </div>
    </div>
  </div>
  <!--Modal -->
  <div class="modal fade" id="ModalCR" name="ModalCR" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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
                <div id="ModalCR_Titulo" name="ModalCR_Titulo"></div>
              </div>
            </div>
          </div>
          <div class="linea-verde"></div>
        </div>
        <div class="modal-body pt0" id="ModalCR_Mensaje" name="ModalCR_Mensaje">
          <!--Mensaje o contenido-->
          <div id="idModalAnexo" class="w100p h400 overflowY m0 p0">
              <div name="divTablaAnexo" id="divTablaAnexo" class="h400 m0 p0">
                  <div name="divAnexoTabla" id="divAnexoTabla" class="w100p h400 overflowY m0 p0"></div>
              </div>
          </div>

        </div>
        <div class="modal-footer">

        </div>
      </div>
    </div>
  </div>
</div>
<!-- <script type="text/javascript" src="javascripts/layout_general.js?<?php echo rand(); ?>"></script> -->

<!-- <script type="text/javascript" src="javascripts/Subir_Archivos.js?<?php echo rand(); ?>"></script> -->


<?php
require 'includes/footer_Index.inc';
?>
