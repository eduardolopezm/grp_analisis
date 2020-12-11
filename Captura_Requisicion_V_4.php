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

$PageSecurity = 4;

include "includes/SecurityUrl.php";
require 'includes/DefinePOClass.php';
require 'includes/session.inc';

$title   = "";
$funcion = 2265;
$title = "Requisiciones";


require 'includes/header.inc';
require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';




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
<script type="text/javascript" src="javascripts/Captura_Requisicion_V_4.js?v=<?php echo rand(); ?>"></script>
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
        <li id="noExistencia" role="presentation" class="active" style="display: none;">
          <a href="#idNoExistenciaModalTab" aria-controls="Existencia" role="tab" data-toggle="tab" title="No Existencia" class="bgc10">No Existencia</a>
        </li>
        <li id="suficienciaPresupuestal" role="presentation">
          <a href="#idSufPresupuestalModalTab" aria-controls="Suficiencia" role="tab" data-toggle="tab" title="Suficiencia Presupuestal" class="bgc10">Suficiencia Presupuestal</a>
        </li>
        <li id="solicitudAlmacen" role="presentation" style="display: none;">
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
              <b>Encabezado</b>
            </a>
          </div>
          <div style="display: none;" id="idStatusReq" class="fr text-right hide ftc7">
            <span>Estatus de la Requisición: </span><input type="hidden" id="idperfilusr">
            <label id="statusReq" style="display: none;"></label>
            <label id="statusReqVisual"></label>
          </div>
        </h4>
      </div>
      <div id="idPanelRequisiciones" class="row panel-collapse collapse in ptb5" role="tabpanel" aria-labelledby="headingOne">
        <div id="top-left-container" class="col-lg-8 col-md-8 col-sm-8">
            <div class="form-inline row hide">
              <div class="col-md-3">
                  <span><label>Dependencia: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnTraeUnidadesResponsables(this.value, 'selectUnidadNegocio')">
                  </select>
              </div>
            </div>
            <br>
            <div class="form-inline row">
              <div class="col-md-3 pt10 text-right">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')">
                  </select>
              </div>
            </div>
            <br>
          <div class="form-inline row">
              <div class="col-md-3 pt10 text-right" style="vertical-align: middle;">
                  <span><label>UE: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" data-todos="true" class="form-control selectUnidadEjecutora" data-almacen="1" onchange="">
                  </select>
              </div>
          </div>
            <br>
            <div class="obs-container">
              <div class="row">
                  <div class="col-lg-3 col-md-3 col-sm-3 pt20 text-right">
                    <label class=""><b>Observaciones:</b> </label>
                  </div>
                  <div class="col-lg-9 col-md-9 col-sm-9">
                    <component-textarea label="Observaciones: " id="txtAreaObs" name="txtAreaObs" rows="2" class="w100p" placeholder="Observaciones"></component-textarea>
                  </div>
              </div>
            </div>
            <br>
            <div class="obs-container">
              <div class="row">
                  <div class="col-lg-3 col-md-3 col-sm-3 pt20 text-right">
                    <label class=""><b>Código Expediente:</b> </label>
                  </div>
                  <div class="col-lg-9 col-md-9 col-sm-9">
                    <component-text label="Código Expediente:" id="codigoExpediente" name="codigoExpediente" placeholder="Código Expediente" title="Código Expediente" value="" maxlength="20"></component-text>
                  </div>
              </div>
            </div>
        </div>
        <div id="top-right-container" class="col-lg-4 col-md-4 col-sm-4">
            <div class="row">
              <label  id="" class="col-lg-3 col-md-3 col-sm-3 pt5">Folio: </label>
              <div class="text-center col-lg-8 col-md-8 col-sm-8">
                <label  id="idtxtRequisicionView" class="wA bgc12 mt5 fts22 ftc8 borderGray borderRadius plr10" ></label>
              </div>
              <input type="text" class="hide" id="idtxtNoRequisicion" name="idtxtNoRequisicion" />
              <input type="text" class="hide" id="idtxtRequisicion" name="idtxtRequisicion" />
            </div>
            <!-- <component-number-label class="pr10" onclick="" label="Nº Requisición: " id="idtxtRequisicionView" name="idtxtRequisicion" placeholder="Nº Requisición" value=""></component-number-label> -->
            <br>
            <component-date-label data-periodo ='<?php $periodoReq ?>' label="Fecha Elaboración: " id="idFechaElaboracion" name="fechaElaboracion" placeholder="Fecha Elaboracion"></component-date-label>
            <br>
            <!-- <component-date-label label="Fecha Requerida: " id="idFechaEntrega" name="fechaEntrega" placeholder="Fecha de Entrega"></component-date-label> -->
            <div id="idContentFechaEntrega" class="row p0 m0">
              <label class="col-lg-3 col-md-3 col-sm-3 p0 pr25 m0" >Fecha Requerida: </label>
              <component-date-feriado class="pr10" id="idFechaEntrega" name="fechaEntrega" placeholder="Fecha de Entrega" title="Fecha de Entrega"></component-date-feriado>
            </div>
            <!-- <div id="idContentDividirAsignacion" class="row p0 m0">
              <label class="col-lg-3 col-md-3 col-sm-3 pr25">Dividir asignación: </label>
              <input class="col-lg-9 col-md-9 col-sm-9 mt15" type="checkbox" id="dividirAsignacion" name="dividirAsignacion">
            </div> -->
            <div id="idContentAnexoTecnico" class="row pt10 m0">
              <label class="w40p fl text-left">Anexo Técnico: </label>
              <div class="w10p fl"></div>
              <input class="w20p fl text-right" type="checkbox" id="anexoTecnicoCheck" name="anexoTecnicoCheck">
            </div>
            <div id="idContentAnexoTecnico" class="row pt10 m0">
              <div class="form-inline row">
                <div class="col-md-3" style="vertical-align: middle;">
                  <span><label>Uso de CFDI:</label></span>
                </div>
                <div class="col-md-9">
                  <select id="selectCFDI" name="selectCFDI" class="form-control selectCFDI"> 
                    <option value="-1" selected="">Seleccionar...</option>
                  </select>
                </div>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>
  <br/>
  <div id="main-container" class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 p0 m0">
      <!-- Nav tabs -->
      <ul class="nav nav-tabs" role="tablist">
        <li id="idArticuloTap" role="presentation" class="active">
          <a href="#idArticuloContainerTab" aria-controls="Articulos" role="tab" data-toggle="tab" title="Articulos" class="bgc10">Artículos</a>
        </li>
        <li id="idServicioTap" role="presentation">
          <a href="#idServicioContainerTab" aria-controls="Servicios" role="tab" data-toggle="tab" title="Servicios" class="bgc10">Servicios</a>
        </li>
        <li id="idInstrumentalesTap" role="presentation" class="hide">
          <a href="#idInstrumentalContainerTab" aria-controls="Instrumental" role="tab" data-toggle="tab" title="Instrumentales" class="bgc10">Instrumentales</a>
        </li>
      </ul>
      <!-- Tab panes -->
      <div id="idTab-content" class="tab-content">
        <div id="idArticuloContainerTab" role="tabpanel" class="instrumentalContainerTab tab-pane active">
          <nav id="idNavArticulos" class="row nav bgc8 fts10 borderGray w100p ftc2">
            <div id="navArticulos" class="col-lg-12 col-md-12 col-sm-12 p0 m0">
              <div class="w5p pt3">
                <!-- onclick="fnValidaExistenciaRequisicion(producto);" -->
                <span id="idBtnAgregarArticulo" class="glyphicon glyphicon-plus btn btn-default btn-xs"></span>
              </div>
              <div class="w2p"><label >Nº</label></div>
              <div class="w10p"><label >Partida</label></div>
              <div class="w10p"><label >Clave</label></div>
              <!-- @NOTE: Cambio de tamaño conforme a solicitud @date:11.04.18 -->
              <!-- <div class="w40p"><label >Artículo</label></div> -->
              <div class="w25p"><label >Artículo</label></div>
              <div class="w10p"><label >Descripción</label></div>
              <div class="w5p"><label >U.Medida</label></div>
              <div class="w5p"><label >Cantidad</label></div>
              <!-- @NOTE: Cambio de tamaño conforme a solicitud @date:11.04.18 -->
              <!-- <div class="w5p"><label >Precio</label></div> -->
              <div class="w8p"><label >Precio</label></div>
              <!-- @NOTE: Cambio de tamaño conforme a solicitud @date:11.04.18 -->
              <!-- <div class="w5p"><label >Total</label></div> -->
              <div class="w9p"><label >Total</label></div>
              <!-- <div class="w10p"><label >Almacen</label></div> -->
              <!-- @NOTE: Cambio de tamaño conforme a solicitud @date:11.04.18 -->
              <!-- <div class="w10p"><label >Renglón</label></div> -->
              <div class="w8p"><label >Renglón</label></div>
            </div>
          </nav>
          <div id="idMainListContentArticulo" class="borderGray m0 p0">
          </div>
        </div>
        <div id="idServicioContainerTab" role="tabpanel" class="servicioContainerTab tab-pane" >
          <nav id="idNavServicios" class="row nav bgc8 fts10 borderGray w100p ftc2">
                <div id="navServicios" class="col-lg-12 col-md-12 col-sm-12 p0 m0">
                  <div class="w5p pt3">
                    <!-- onclick="fnValidaExistenciaRequisicion(servicio)" -->
                    <span id="idBtnAgregarServicio" class="glyphicon glyphicon-plus btn btn-default btn-xs"></span>
                  </div>
                  <div class="w5p"><label >Nº</label></div>
                  <div class="w10p"><label >Partida</label></div>
                  <div class="w20p"><label >Descripcion Partida</label></div>
                  <div class="w40p"><label >Descripción Servicio</label></div>
                  <div class="w5p"><label >Cantidad</label></div>
                  <!-- @NOTE: Cambio de tamaño conforme a solicitud @date:11.04.18 -->
                  <!-- <div class="w5p"><label >Precio</label></div> -->
                  <div class="w7p"><label >Precio</label></div>
                  <!-- @NOTE: Cambio de tamaño conforme a solicitud @date:11.04.18 -->
                  <!-- <div class="w10p"><label >Renglón</label></div> -->
                  <div class="w8p"><label >Renglón</label></div>
                </div>
          </nav>
          <div id="idMainListContentServicio" class="borderGray m0 p0">
          </div>
        </div>
        <div id="idInstrumentalContainerTab" role="tabpanel" class="instrumentalContainerTab tab-pane ">
          <nav id="idNavInstrumental" class="row nav bgc8 fts10 borderGray w100p ftc2">
            <div id="navInstrumental" class="col-lg-12 col-md-12 col-sm-12 p0 m0">
              <div class="w5p pt3">
                <span id="idBtnAgregarInstrumental" class="glyphicon glyphicon-plus btn btn-default btn-xs" ></span>
              </div>
              <div class="w5p"><label >Nº</label></div>
              <div class="w10p"><label >Partida</label></div>
              <div class="w10p"><label >Clave</label></div>
              <div class="w50p"><label >Instrumentales</label></div>
              <!-- <div class="w5p"><label >U.Medida</label></div> -->
              <!-- <div class="w5p"><label >Cantidad</label></div> -->
              <div class="w10p"><label >Precio</label></div>
              <!-- <div class="w5p"><label >Total</label></div> -->
              <div class="w10p"><label >Renglón</label></div>
            </div>
          </nav>
          <div id="idMainListContentInstrumental" class="borderGray m0 p0">
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
          <button type="button" id="idBtnGuardarCR" name="btnGuardarCR"  class="btn btn-default botonVerde glyphicon glyphicon-floppy-disk">&nbsp;Guardar</button>
          <button type="button" id="idBtnCancelarCR" name="btnCancelarCR" onclick="fnCancelarRequisicion()" class="btn btn-default botonVerde glyphicon glyphicon-trash">&nbsp;Cancelar</button>
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
