<?php
/**
 * ABC de Objeto Gasto (Partida especifica)
 *
 * @category ABC
 * @package ap_grp
 * @author Jorge Cesar Garcia Baltazar <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 21/08/2017
 * Fecha Modificación: 21/08/2017
 * Se realizan operación pero el Alta, Baja y Modificación
 */


include('includes/session.inc');
$funcion=1459;
$title = traeNombreFuncion($funcion, $db);
include('includes/header.inc');


include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
//Librerias GRID
include('javascripts/libreriasGrid.inc');
?>
<script type="text/javascript" src="javascripts/ABCObjetoGasto.js?v=<?= rand();?>"></script>
<div id="OperacionMensaje" name="OperacionMensaje"></div>
<!-- tabla de busqueda -->
<div class="row">
  <div name="divTabla" id="divTabla">
    <div id="divCatalogo" name="divCatalogo"></div>
  </div>
</div><!-- .row -->

<!-- botones de accion -->
<div class="row pt10">
  <div class="panel panel-default">
    <div align="center">
      <component-button type="button" id="btnAgregar" name="btnAgregar" onclick="fnAgregarCatalogoModal()" value="Nuevo" class="glyphicon glyphicon-plus"></component-button>

      <br>
      <br>
    </div>
  </div>
</div><!-- .row --><!-- .row -->


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
        <!--Mensaje o contenido-->

        <div class="input-group">
          <div id="capitulocapitulo"></div>
          <span class="input-group-addon" style="background: none; border: none;"> Capítulo </span>
        <select id="selectCapitulos" name="selectCapitulos[]" class="form-control selectCapitulos"></select>
      </div>
      <br>
      <div class="input-group">
        <div id="conceptoconcepto"></div>
        <span class="input-group-addon" style="background: none; border: none;"> Concepto </span>
      <select id="selectConceptos" name="selectConceptos[]" class="form-control selectConceptos"></select>
    </div>
    <br>
    <div class="input-group">
      <div id="generalgeneral"></div>
      <span class="input-group-addon" style="background: none; border: none;"> Partida Genérica </span>
    <select id="selectPartidasGenericas" name="selectPartidasGenericas[]" class="form-control selectPartidasGenericas" readonly></select>
  </div>
  <br>

  <component-number-label label="Partida Específica: " id="txtPartidaEspecifica" name="txtPartidaEspecifica" placeholder="Partida Específica" maxlength="2"></component-number-label>
  <br>
  <component-text-label label="Descripción: " id="txtNombre" name="txtNombre" placeholder="Descripción"></component-text-label>
</div>
<div class="modal-footer">
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
  <component-button type="button" id="btn" name="btn" onclick="fnEliminarEjecuta()" value="Eliminar"></component-button>
  <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
</div>
</div>
</div>
</div>
<?php
include 'includes/footer_Index.inc';
?>
