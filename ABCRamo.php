<?php
/**
 * ABC de Ramo
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/07/2017
 * Fecha Modificación: 31/07/2017
 * Se realizan operación pero el Alta, Baja y Modificación
 */

$PageSecurity=5;
include('includes/session.inc');
$funcion=2246;
$title = traeNombreFuncion($funcion, $db);
include('includes/header.inc');
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

//Librerias GRID
include('javascripts/libreriasGrid.inc');
?>

<script type="text/javascript" src="javascripts/ABCRamo.js"></script>
<!-- tabla de busqueda -->
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
        <!--Mensaje o contenido-->
        <div id="msjValidacion" name="msjValidacion"></div>
        <component-text-label label="RA: " id="txtClave" name="txtClave" placeholder="RA" maxlength="2" onkeypress="return soloNumeros(event)" onchange="fnVerificarClave()"></component-text-label>
        <br>
        <component-text-label label="Descripción: " id="txtDescripcion" name="txtDescripcion" placeholder="Descripción"></component-text-label>
      </div>
      <div class="modal-footer">
        <component-text type="hidden" id="txtClaveId" name="txtClaveId" placeholder="UE"></component-text>
        <component-button type="button" id="btn" name="btn" onclick="fnAgregar()" value="Guardar"></component-button>
        <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
      </div>
    </div>
  </div>
</div>
<!--Modal Eliminar -->
<div class="modal fade" id="ModalUREliminar" name="ModalUREliminar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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
      <div class="modal-body" id="ModalUREliminar_Mensaje" name="ModalUREliminar_Mensaje">
        <!--Mensaje o contenido-->
      </div>
      <div class="modal-footer">
        <component-text type="hidden" label="UE: " id="txtClaveEliminar" name="txtClaveEliminar" placeholder="UE"></component-text>
        <component-button type="button" id="btn" name="btn" onclick="fnEliminarEjecuta()" data-dismiss="modal" value="Eliminar"></component-button>
        <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
      </div>
    </div>
  </div>
</div>
<?php
include 'includes/footer_Index.inc';
?>