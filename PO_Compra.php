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

require 'includes/DefinePOClass.php';
require 'includes/session.inc';
$PageSecurity = 4;
$funcion=29;

$title = traeNombreFuncion($funcion, $db);

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
require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';


//Librerias GRID
require 'javascripts/libreriasGrid.inc';
$periodoRequisicion = GetPeriod(date('d/m/Y'), $db);
?>
<script type="text/javascript">
  var idRequisicionGeneral = '<?php if (isset($_GET['ModifyOrderNumber'])) {echo $_GET['ModifyOrderNumber'];} else {echo $x = 0;}?>';
  var noRequisicionGeneral = '<?php if (isset($_GET['idrequisicion'])) {echo $_GET['idrequisicion'];} else {echo $x = 0;}?>';
  var periodoReq = '<?php echo $periodoRequisicion ?>';
  var producto = "B";
  var servicio = "D";
</script>
<!-- <script src="ckeditor/ckeditor.js"></script> -->
<script type="text/javascript" src="javascripts/PO_Compra.js"></script>

<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
<div id="msjValidacion" name="msjValidacion"></div>

<div class="container p0 m0">
    <div id="busquedaProveedor">
      <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
          <h4 class="panel-title row">
            <div class="col-md-6 col-xs-6 text-left">
              <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBuscarPorveedor" aria-expanded="true" aria-controls="collapseOne">
                <b>Datos de proveedor</b>
              </a>
            </div>
          </h4>
        </div>
        <div id="PanelBuscarPorveedor" name="PanelBuscarPorveedor" class="row panel-collapse collapse in p5" role="tabpanel" aria-labelledby="headingOne">
          <div class="col-md-6 text-left">
                <label>&nbsp;Nombre o Razon Social:</label>
                <component-text id="Keywords" name="Keywords" placeholder="Nombre Proveedor"></component-text>
          </div>
          <div class="col-md-2 text-left">
                <label>&nbsp;Codigo Proveedor:</label>
                <component-text id="SuppCode" name="SuppCode" placeholder="Codigo Proveedor"></component-text>
          </div>
          <div class="col-md-2 text-left">
                <label>&nbsp;RFC:</label>
                <component-text id="SuppTaxid" name="SuppTaxid" placeholder="RFC" maxlength="13"></component-text>
          </div>
          <div class="col-md-2">
                <br>
               <component-button type="submit" onclick="fnMuestraProveedores()" id="SearchSuppliers" name="SearchSuppliers" value="Buscar" class="glyphicon glyphicon-search"></component-button>
          </div>
        </div>
      </div>
    </div>
    <div id="datosProveedor" class="hide">
      <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingTwo">
          <h4 class="panel-title row">
            <div class="col-md-6 col-xs-6 text-left">
              <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelDatosPorveedor" aria-expanded="true" aria-controls="collapseOne">
                <b>Datos de proveedor</b>
              </a>
            </div>
          </h4>
        </div>
        <div id="PanelDatosPorveedor" name="PanelDatosPorveedor" class="panel-collapse collapse in p5" role="tabpanel" aria-labelledby="headingTwo">
          <div id="infoProveedor" class="hide">
                <div class="w10p">
                  <label>&nbsp;Codigo Proveedor: </label>
                  <label id="codePro"></label>
                  <input id="idCodePro" type="text" class="hide">
                </div>
                <div class="w10p">
                  <label>&nbsp;Nombre Proveedor: </label>
                  <label id="nomPro"></label>
                  <input id="idNomPro" type="text" class="hide">
                </div>
                <div class="w10p">
                  <label>&nbsp;RFC: </label>
                  <label id="rfcPro"></label>
                  <input id="idRFCPro" type="text" class="hide">
                </div>
                <div class="w10p">
                  <label>&nbsp;Colonia: </label>
                  <label id="colPro"></label>
                  <input id="idColPro" type="text" class="hide">
                </div>
                <div class="w10p">
                  <label>&nbsp;Calle: </label>
                  <label id="callePro"></label>
                  <input id="idCallePro" type="text" class="hide">
                </div>
                <div class="w10p">
                  <label>&nbsp;Ciudad: </label>
                  <label id="ciudadPro"></label>
                  <input id="idCiudadPro" type="text" class="hide">
                </div>
                <div class="w10p">
                  <label>&nbsp;Estado: </label>
                  <label id="edoPro"></label>
                  <input id="idEdoPro" type="text" class="hide">
                </div>
                <div class="w10p">
                  <label>&nbsp;Moneda: </label>
                  <label id="monedaPro"></label>
                  <input id="idMonedaPro" type="text" class="hide">
                </div>
          </div>
          <div name="divTabla2" id="divTabla2" class="hide">
              <div name="divProveedorTabla" id="divProveedorTabla"></div>
          </div>
        </div>
      </div>
    </div>
    <div id="datosRequisicion">
      <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingThree">
          <h4 class="panel-title row">
              <div class="col-md-6 col-xs-6 text-left pt5">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelDatosRequisicion" aria-expanded="true" aria-controls="collapseOne">
                  <b>Datos Requisición</b>
                </a>
              </div>
              <div class="col-md-6 col-xs-6 text-right">
                <div class="btn btn-default btn-xs" onclick="fnMostrarAgregarElemento()">
                  <b>Agregar Elemento</b>
                </div>
              </div>
          </h4>
        </div>
        <div id="PanelDatosRequisicion" name="PanelDatosRequisicion" class="panel-collapse collapse in p5" role="tabpanel" aria-labelledby="headingThree">
          <div name="divTabla" id="divTabla">
            <div name="divRequisicionTabla" id="divRequisicionTabla"></div>
          </div>
        </div>
      </div>
    </div>
    <div id="agregarElemento" class="hide">
      <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingFour">
          <h4 class="panel-title row">
            <div class="col-md-6 col-xs-6 text-left pt5">
              <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelAgregarElemento" aria-expanded="true" aria-controls="collapseOne">
                <b>Agregar Elemento</b>
              </a>
            </div>
          </h4>
        </div>
        <div id="PanelAgregarElemento" name="PanelAgregarElemento" class="row panel-collapse collapse in p5" role="tabpanel" aria-labelledby="headingFour">
          <div id="agerarElemento-container" class="row p0 m0">
            <div class="col-lg-12 col-md-12 col-sm-12">
              <!-- Nav tabs -->
              <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active">
                  <a href="#idArticuloContainerTab" aria-controls="Articulos" role="tab" data-toggle="tab" title="Articulos" class="bgc10">Articulos</a>
                </li>
                <li role="presentation">
                  <a href="#idServicioContainerTab" aria-controls="Servicios" role="tab" data-toggle="tab" title="Servicios" class="bgc10">Servicios</a>
                </li>
              </ul>
              <!-- Tab panes -->
              <div id="idTab-content" class="tab-content">
                <div id="idArticuloContainerTab" role="tabpanel" class="articuloContainerTab tab-pane active">
                  <nav id="idNavArticulos" class="row nav bgc8 fts10 borderGray w100p ftc2">
                    <div id="navArticulos" class="col-lg-12 col-md-12 col-sm-12 p0 m0">
                      <div class="w5p pt3">
                        <span id="idBtnAgregarArticulo" class="btn btn-default btn-xs glyphicon glyphicon-plus-sign" onclick="fnAgregarElementoRequisicion(producto,idRequisicionGeneral)"></span>
                      </div>
                      <div class="w5p"><label for="">Nº</label></div>
                      <div class="w10p"><label for="">Partida</label></div>
                      <div class="w10p"><label for="">Clave</label></div>
                      <div class="w30p"><label for="">Articulo</label></div>
                      <div class="w5p"><label for="">U.Medida</label></div>
                      <div class="w5p"><label for="">Cantidad</label></div>
                      <div class="w5p"><label for="">Precio</label></div>
                      <div class="w5p"><label for="">Total</label></div>
                      <div class="w10p"><label for="">Almacen</label></div>
                      <div class="w10p"><label for="">Renglón</label></div>
                    </div>
                  </nav>
                  <div id="idMainListContentArticulo" class="borderGray">
                  </div>
                </div>
                <div id="idServicioContainerTab" class="servicioContainerTab tab-pane" >
                  <nav id="idNavServicios" class="row nav bgc8 fts10 borderGray w100p ftc2">
                        <div id="navServicios" class="col-lg-12 col-md-12 col-sm-12 p0 m0">
                          <div class="w5p pt3">
                            <span id="idBtnAgregarServicio" class="btn btn-default btn-xs glyphicon glyphicon-plus-sign" onclick="fnAgregarElementoRequisicion(servicio,idRequisicionGeneral)"></span>
                          </div>
                          <div class="w5p"><label for="">Nº</label></div>
                          <div class="w10p"><label for="">Clave</label></div>
                          <div class="w20p"><label for="">Partida</label></div>
                          <div class="w40p"><label for="">Descripción Servicio</label></div>
                          <div class="w5p"><label for="">Cantidad</label></div>
                          <div class="w5p"><label for="">Precio</label></div>
                          <div class="w10p"><label for="">Renglón</label></div>
                        </div>
                  </nav>
                  <div id="idMainListContentServicio" class="borderGray">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>

<?php
require 'includes/footer_Index.inc';
?>
