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

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
$PageSecurity = 4;

include "includes/SecurityUrl.php";
require 'includes/DefinePOClass.php';
require 'includes/session.inc';

$title   = "";
$funcion = 2265;
//$title = "Requisici&oacute;n";
$title = "Requisiciones";

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
require 'javascripts/libreriasGrid.inc';
//$permiso = Havepermission ( $_SESSION ['UserID'], 2257, $db );
//
//
    /*$enc = new Encryption;
    $url = "&SelectedUser=>" . $myrow[0] . "&funcion=>1";
    $url = $enc->encode($url);
    $liga= "URL=" . $url;*/

$periodoRequisicion = GetPeriod(date('d/m/Y'), $db);
$datoidReq = 0;
$datoAnexo = 0;

if (isset($_GET['ModifyOrderNumber'])) {
    $datoAnexo = $_GET['ModifyOrderNumber'];
}

if (isset($_GET['idrequisicion'])) {
    $datoidReq = $_GET['idrequisicion'];
}

$enc = new Encryption;
        $url = "&ModifyOrderNumber=>" . $_GET['ModifyOrderNumber'] . "&idrequisicion=> ". $_GET['idrequisicion'];
        $url = $enc->encode($url);
        $liga= "URL=" . $url;

echo "<input type='text' id='txtOrderno' class='hide' value='".$datoAnexo."'>";
?>

<script type="text/javascript">
  $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();   
});
  var noRequisicionGeneral = '<?php echo $datoAnexo; ?>';
  var periodoReq = '<?php echo $periodoRequisicion; ?>';
  var urlCReq = '<?php echo $liga; ?>';
  var producto = "B";
  var servicio = "D";

</script>
<script type="text/javascript" src="javascripts/Captura_Requisicion.js"></script>
<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
<div id="msjValidacion" name="msjValidacion"></div>
<!-- <div id="idTableReqValidacion" name="idTableReqValidacion">
  <div id="idTableHeader"></div>
  <div id="idTableContReq" name="idTableContReq"></div>
  <div id="idTableReqBotones" name="idTableReqBotones"></div>
</div> -->

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
          <div id="idStatusReq" class="fr text-right hide ftc7">
            <span>Estatus de la Requisición: </span><input type="hidden" id="idperfilusr">
            <label id="statusReq"></label>
          </div>
        </h4>
      </div>
      <div id="idPanelRequisiciones" class="row panel-collapse collapse in ptb5" role="tabpanel" aria-labelledby="headingOne">
        <div id="top-left-container" class="col-lg-8 col-md-8 col-sm-8">
            <div class="form-inline row">
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
              <div class="col-md-3">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')">
                  </select>
              </div>
            </div>
            <br>
          <div class="form-inline row">
              <div class="col-md-3" style="vertical-align: middle;">
                  <span><label>UE: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora"> 
                  </select>
              </div>
          </div>
            <br>
            <div class="obs-container">
              <div class="row">
                  <div class="col-lg-3 col-md-3 col-sm-3 pt20">
                    <label class=""><b>Observaciones:</b> </label>
                  </div>
                  <div class="col-lg-9 col-md-9 col-sm-9">
                    <component-textarea label="Observaciones: " id="txtAreaObs" name="txtAreaObs" rows="2" class="w100p" placeholder="Observaciones"></component-textarea>
                  </div>
              </div>
            </div>
        </div>
        <div id="top-right-container" class="col-lg-4 col-md-4 col-sm-4">
            <div class="row">
              <label  id="" class="col-lg-3 col-md-3 col-sm-3 pt5">Nº Requisición: </label>
              <div class="text-center col-lg-8 col-md-8 col-sm-8">
                <label  id="idtxtRequisicionView" class="wA bgc12 mt5 fts22 ftc8 borderGray borderRadius plr10" ></label>
              </div>
              <input type="text" class="hide" id="idtxtNoRequisicion" name="idtxtNoRequisicion" />
              <input type="text" class="hide" id="idtxtRequisicion" name="idtxtRequisicion" />
            </div>
            <!-- <component-number-label class="pr10" onclick="" label="Nº Requisición: " id="idtxtRequisicionView" name="idtxtRequisicion" placeholder="Nº Requisición" value=""></component-number-label> -->
            <br>
            <component-date-label data-periodo ='<?php $periodoRequisicion ?>' label="Fecha Elaboración: " id="idFechaElaboracion" name="fechaElaboracion" placeholder="Fecha Elaboracion"></component-date-label>
            <br>
            <!-- <component-date-label label="Fecha Requerida: " id="idFechaEntrega" name="fechaEntrega" placeholder="Fecha de Entrega"></component-date-label> -->
            <div id="idContentFechaEntrega" class="row p0 m0">
              <label class="col-lg-3 col-md-3 col-sm-3 pr25" >Fecha Requerida: </label>
              <component-date-feriado class="pr10" id="idFechaEntrega" name="fechaEntrega" placeholder="Fecha de Entrega" title="Fecha de Entrega"></component-date-feriado>
            </div>
            <div id="idContentFechaEntrega" class="row p0 m0">
              <label class="col-lg-3 col-md-3 col-sm-3 pr25">Dividir asignación: </label>
              <input class="col-lg-9 col-md-9 col-sm-9 mt15" type="checkbox" id="dividirAsignacion" name="dividirAsignacion">
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
          <a href="#idArticuloContainerTab" aria-controls="Articulos" role="tab" data-toggle="tab" title="Articulos" class="bgc10">Articulos</a>
        </li>
        <li id="idServicioTap" role="presentation">
          <a href="#idServicioContainerTab" aria-controls="Servicios" role="tab" data-toggle="tab" title="Servicios" class="bgc10">Servicios</a>
        </li>
        <li id="idAnexoTap" role="presentation" class="hide">
          <a href="#idAnexoContainerTab" aria-controls="Anexo" role="tab" data-toggle="tab" title="Servicios" class="bgc10">Ánexo Técnico</a>
        </li>
      </ul>
      <!-- Tab panes -->
      <div id="idTab-content" class="tab-content">
        <div id="idArticuloContainerTab" role="tabpanel" class="articuloContainerTab tab-pane active">
          <nav id="idNavArticulos" class="row nav bgc8 fts10 borderGray w100p ftc2">
            <div id="navArticulos" class="col-lg-12 col-md-12 col-sm-12 p0 m0">
              <div class="w5p pt3">
                <span id="idBtnAgregarArticulo" class="glyphicon glyphicon-plus btn btn-default btn-xs" onclick="fnValidaExistenciaRequisicion(producto);"></span>
              </div>
              <div class="w5p"><label >Nº</label></div>
              <div class="w10p"><label >Partida</label></div>
              <div class="w10p"><label >Clave</label></div>
              <div class="w40p"><label >Articulo</label></div>
              <div class="w5p"><label >U.Medida</label></div>
              <div class="w5p"><label >Cantidad</label></div>
              <div class="w5p"><label >Precio</label></div>
              <div class="w5p"><label >Total</label></div>
              <!-- <div class="w10p"><label >Almacen</label></div> -->
              <div class="w10p"><label >Renglón</label></div>
            </div>
          </nav>
          <div id="idMainListContentArticulo" class="borderGray m0 p0">
          </div>
        </div>
        <div id="idServicioContainerTab" class="servicioContainerTab tab-pane" >
          <nav id="idNavServicios" class="row nav bgc8 fts10 borderGray w100p ftc2">
                <div id="navServicios" class="col-lg-12 col-md-12 col-sm-12 p0 m0">
                  <div class="w5p pt3">
                    <span id="idBtnAgregarServicio" class="glyphicon glyphicon-plus btn btn-default btn-xs" onclick="fnValidaExistenciaRequisicion(servicio)"></span>
                  </div>
                  <div class="w5p"><label >Nº</label></div>
                  <div class="w10p"><label >Clave</label></div>
                  <div class="w20p"><label >Partida</label></div>
                  <div class="w40p"><label >Descripción Servicio</label></div>
                  <div class="w5p"><label >Cantidad</label></div>
                  <div class="w5p"><label >Precio</label></div>
                  <div class="w10p"><label >Renglón</label></div>
                </div>
          </nav>
          <div id="idMainListContentServicio" class="borderGray m0 p0">
          </div>
        </div>
        <div id="idAnexoContainerTab" class="anexoContainerTab tab-pane" >
          <nav class="row nav bgc8 fts10 borderGray w100p ftc2">
            <div class="col-lg-12 col-md-12 col-sm-12 borderR pt5 h40">
              <div class="fts24"><span>Ánexo Técnico</span></span></div>
            </div>
          </nav>
          <div id="idMainListContentAnexo" class="borderGray row p0 m0">
            <div id="subirCsv" class="tab-pane col-lg-12 col-md-12 col-sm-12 p5 m0">
                <h3>Administración de archivos CSV</h3>
                <div class="row p0 m0">
                    <div class="clo-lg-12 col-md-12 col-sm-12 p5 text-center">
                      <span class="ftc8 fts14">Descargar Layout para el Ánexo Técnico</span>
                      <div id="idBtnDownAnexo" class="btn btn-default botonVerde glyphicon glyphicon-download-alt" onclick="fnGenerarLayoutRequisicion()"></div>
                    </div>
                </div>
                <!--<a href="/ap_grp/archivos/0prueba.png">prueba </a>-->
               <!-- <component-administrador-archivos funcion="2265" tipo="19" trans="" esmultiple="0"> </component-administrador-archivos>-->
               
               <!--anexos -->
<div class="cargarArchivosComponente">
  <input id="esMultiple" name="esMultiple" value="0" type="hidden"> 
  <input name="componente" id="componenteArchivos" type="hidden"> 
  <input id="funcionArchivos" name="funcionArchivos" value="2265" type="hidden">
   <input id="tipoArchivo" value="19" type="hidden"> 
   <input id="transnoArchivo" value="" type="hidden"> 
   <div id="mensajeArchivos">
   </div>
    <div id="subirArchivos" class="col-md-12">
    <div class="col-md-12" style="color: rgb(255, 255, 255) !important;">
      <div class="col-md-6"><div id="tipoInputFile">
        
      </div> 
      <button id="cuadroDialogoCarga" onclick="fnCargarArchivos()" class="btn bgc8"><span class="glyphicon glyphicon-file">
        
      </span>                    Cargar archivo(s)                </button> 
      <br> <br>
       <button id="enviarArchivosMultiples" class="btn bgc8" style="display: none;">
       Subir
     </button> <br> <br>
   </div>
    <br></div> <div id="muestraAntesdeEnviar" class="col-md-12 col-xs-12">
      
    </div>
     <br> <br>
   </div> 
     <div id="enlaceDescarga" class="col-md-12 col-xs-12">
       
     </div> 
     <div id="accionesArchivos" style="color: rgb(255, 255, 255) !important; display: none;">
      <div class="col-md-3"><button id="eliminarMultiples" onclick="fnBorrarConfirmaArch()" class="btn bgc8">Eliminar</button>
       <br>
     </div> 
     <div class="col-md-3">
      <button id="descargarMultiples" onclick="fnProcesosArchivosSubidos('descargar')" class="btn bgc8">Descargar</button> 
      <br></div>
    </div>

    <div name="divTablaArchivos" id="divTablaArchivos" class="col-md-12 col-xs-12">
        <div name="divDatosArchivos" class="col-md-12 col-xs-12" id="divDatosArchivos"></div>
        </div>
    <div class="modal fade" id="ModalBorrarArchivos"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
           <div class="modal-dialog" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
                 <div class="modal-content">
                     <div class="navbar navbar-inverse navbar-static-top">
                              <div class="col-md-lg menu-usuario">
                        <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
                    </div>
                    <div id="navbar" class="navbar-collapse collapse">
                        <div class="nav navbar-nav">
                            <div class="title-header">
                                <div id="ModalBorrarArchivos_Titulo" ></div>
                            </div>
                        </div>
                    </div>
                    <div class="linea-verde"></div>
                </div>
                <div class="modal-body" id="ModalBorrarArchivos_Mensaje">
                    <div class="col-md-9" id="listaBorrarArchivos" >
                        <h3>¿Desea borrar los archivos seleccionados?</h3>
                    </div>
                </div>
                <br> <br> <br>
                <div class="modal-footer">
                    <div class="col-xs-6 col-md-6 text-right">
                        <div id="procesandoPagoEspere"> </div> <br>


                        <button id="btnConfirmarEliminar" name="btnConfirmarEliminar" type="button" title="" class="btn btn-default botonVerde"  onclick="fnProcesosArchivosSubidos('eliminar','19')" >
                            Eliminar
                        </button>

                       <button id="btnCerrarConfirma" name="ElementoDefault" type="button" title="" onclick="" class="btn btn-default botonVerde" data-dismiss="modal" style="font-weight: bold;">&nbsp;Cancelar</button>



                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
               <!--Anexos-->

            </div> <!-- fin tab de carga de CSV-->
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
          <button type="button" id="idBtnGuardarCR" name="btnGuardarCR" onclick="fnGuardarRequisicion()" class="btn btn-default botonVerde glyphicon glyphicon-floppy-disk hide">Guardar</button>
          <button type="button" id="idBtnCancelarCR" name="btnCancelarCR" onclick="fnCancelarRequisicion()" class="btn btn-default botonVerde glyphicon glyphicon-trash hide">Cancelar</button>
          <button type="button" id="idBtnRegresarCR" name="btnRegresarCR" onclick="fnRegresarPanelRequisicion()" class="btn btn-default botonVerde glyphicon glyphicon-home">Regresar</button>
        </div>
      </div>
    </div>
  </div>
  <!-- <div id="idPopupEditor" style="display: none;">
    <div class="content-popup">
        <div class="popupEditorClose"><a href="#" id="idPopupEditorClose">x</a></div>
        <div>
          <h2>Anexo Tecnico</h2>
            <div id="idModalAnexo">
              <div name="divTablaAnexo" id="divTablaAnexo">
                  <div name="divAnexoTabla" id="divAnexoTabla"></div>
              </div>
            </div>
        </div>
    </div>
  </div>
  <div class="popup-overlay" style="display: none;"></div> -->
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
<script type="text/javascript" src="javascripts/layout_general.js"></script>

<script type="text/javascript" src="javascripts/Subir_Archivos.js"></script>


<?php
require 'includes/footer_Index.inc';
?>
