<?php
/**
 * ABC PPI(Programa Proyecto Inversion)
 *
 * @category     ABC
 * @package      ap_grp
 * @author       Arturo Lopez Peña  <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/08/2017
 * Fecha Modificación: 03/10/2017
 * Se realizan operación pero el Alta, Baja y Modificación
 */

$PageSecurity = 5;
require 'includes/session.inc';
//$title = _('Mantenimiento Programa Proyecto de Inversión ');
$funcion = 2253;
$title= traeNombreFuncion($funcion, $db);
require 'includes/header.inc';
require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
require 'javascripts/libreriasGrid.inc';
?>
<div id="OperacionMensaje" name="OperacionMensaje"> </div>
<script type="text/javascript" src="javascripts/ABC_Ppi.js"></script>
<div class="row">
  <div name="divTabla" id="divTabla">
    <div name="divCatalogo" id="divCatalogo"></div>
  </div>
</div><!-- .row -->

<!-- botones de accion -->
<div class="row pt10">
  <div class="panel panel-default">
    <div align="center">
      <component-button type="button" id="btnAgregar" name="btnAgregar" onclick="fnAgregarCatalogoModal()" value="Nuevo" class="glyphicon glyphicon-plus"></component-button>
      <br><br>
    </div>
  </div>
</div><!-- .row --><!-- .row -->

<!--Modal Agregar/Modificar -->
<div class="modal fade ui-draggable" id="ModalPyin" name="ModalPyin" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
    <div class="modal-content">
      <div class="navbar navbar-inverse navbar-static-top">
        <!--Contenido Encabezado-->
        <div class="col-xs-12 col-md-12 menu-usuario">
          <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <div class="nav navbar-nav">
            <div class="title-header">
              <div id="ModalPyin_Titulo" name="ModalPyin_Titulo"></div>
            </div>
          </div>
        </div>
        <div class="linea-verde"></div>
      </div>
      <div name="divMensajeOperacion" id="divMensajeOperacion" class="m10"></div>

      <div class="modal-body" id="ModalPyin_Mensaje" name="ModalPyin_Mensaje">
        <!--Mensaje o contenido-->
        <div class="col-xs-12 col-md-12" style="text-align: left;"> 
          <component-text-label label="PPI: " id="txtPyin" name="txtPyin" placeholder="PPI" maxlength="11"></component-text-label>
        </div>
          <br> <br>
           <div class="col-xs-12 col-md-12" style="text-align: left;"> 
          <component-text-label label="Nombre: " id="txtNomb" name="txtNomb" placeholder="Nombre"></component-text-label>
        </div>
          <br><br>
            <div class="col-xs-12 col-md-12" style="text-align: left;"> 
          <component-text-label label="Descripción: " id="txtDescripcion" name="txtDescripcion" placeholder="Descripción"></component-text-label>
        </div>
           <br><br>
        
         <div class="col-xs-12 col-md-12" style="text-align: left;"> 
          <component-text-label label="Ramo: " id="txtRamo" name="txtRamo" placeholder="Ramo" maxlength="2" ></component-text-label>
        </div>
          <br><br>
         <div class="col-xs-12 col-md-12" style="text-align: left;"> 
          <component-text-label label="CUNR: " id="txtCunr" name="txtCunr" placeholder="CUNR" maxlength="3" ></component-text-label>
        </div>
          <br><br>
          <div class="col-xs-12 col-md-12" style="text-align: left;"> 
           <br>
          <div class="col-xs-6 col-md-6">
          <component-date-label label="Fecha inicio: " id="dateFecha" name="dateFecha" placeholder="Fecha inicio"></component-date-label>
          </div>
          <div class="col-xs-6 col-md-6">
          <component-date-label label="Fecha fin: " id="dateFechaFinal" name="dateFechaFinal" placeholder="Fecha fin"></component-date-label>
          </div>
           
       </div>
        <br>
        
         <!--
        <div id="fijarfechafinal" class="col-xs-3  col-md-3">-->
        
          <!--
          <div data-date-format="dd-mm-yyyy" class="input-group date componenteCalendarioClase"><input id="dateFechaFinal" name="dateFecha" placeholder="Fecha fin" title="" onkeyup="" onkeypress="" maxlength="10" onpaste="return false" class="form-control" style="width: 100%;" type="text"> <span class="input-group-addon" id="prueba24"><span class="glyphicon glyphicon-calendar"></span></span></div>-->
        <!--
          <div id="ultimo"> </div>
        <br>
          <div id="ultimof"> </div>

         </div>-->
        
        <br>  <br>
          <div class="col-xs-6 col-md-6" style="text-align: left;"> 
        <component-text-label label="Total: " id="txtTotal" name="txtTotal" maxlength="12" placeholder="Total" onkeypress="return fnsoloDecimalesGeneral(event, this)"></component-text-label>
      </div>
         <br><br><br>
          
            <div class="col-xs-6 col-md-6" style="text-align: left;"> 
          <component-text-label label="Inversión ejercida: " id="txtInv_ejercida" name="txtInv_ejercida" maxlength="12" placeholder="Inversion ejercida" onkeypress="return fnsoloDecimalesGeneral(event, this)"></component-text-label>
        </div>
         <br><br>
         <div class="col-xs-6 col-md-6" style="text-align: left;">
        <component-date-label label="Fecha activa: " id="dateFact" name="dateFacta" placeholder="Fecha activa"></component-date-label>
      </div>
         <br><br><br>
         

      </div>
      <div class="modal-footer">
        <component-button type="button" id="btn" name="btn" onclick="fnAgregar()" value="Guardar"></component-button>
        <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
      </div>
    </div>
  </div>
</div>
<!--Modal Eliminar -->
<div class="modal fade" id="ModalPyinEliminar" name="ModalPyinEliminar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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
              <h3><span class="glyphicon glyphicon-info-sign"></span> Información</h3>
            </div>
          </div>
        </div>
        <div class="linea-verde"></div>
      </div>
      <div class="modal-body" id="ModalPyinEliminar_Mensaje" name="ModalPyinEliminar_Mensaje">
        <!--Mensaje o contenido-->
      </div>
      <div class="modal-footer">
        <component-text type="hidden" label="PYIN: " id="txtPyinEliminar" name="txtPyinEliminar" placeholder="PYIN"></component-text>
        <component-button type="button" id="btnEliminar" name="btnEliminar" onclick="fnEliminarEjecuta()" value="Eliminar"></component-button>
        <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
      </div>
    </div>
  </div>
</div>

<div id="ventanita" title="Venta sagarpa">
 <!-- <p>Venta de pruena para sagarpa</p>-->
</div>

<?php
require 'includes/footer_Index.inc';
?>