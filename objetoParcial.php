<?php
/** 
 * ABC de Fuente del Recurso
 *
 * @category ABC 
 * @package ap_grp
 * @author Jesùs Reyes Santos <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Se realizan operación pero el Alta, Baja y Modificación 
 */

$PageSecurity = 5;
include 'includes/session.inc';  
//$title = _('Mantenimiento Función');
$funcion = 2505;
$title = traeNombreFuncion($funcion, $db);

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
include 'javascripts/libreriasGrid.inc';
?>
<script type="text/javascript" src="javascripts/objetoParcial.js"></script>
<!-- tabla de busqueda -->


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
      <div id="divMensajeOperacion" name="divMensajeOperacion"></div>
      <div class="modal-body" id="ModalUR_Mensaje" name="ModalUR_Mensaje">
        <!--Mensaje o contenido-->
        <div id="msjValidacion" name="msjValidacion"></div>

     
          <div class="col-md-6">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Objeto Principal: </label></span>
                    </div>
                    <div class="col-md-9">
                    <select id="selectObjetoPrincipal" name="selectObjetoPrincipal" class="form-control selectObjetoPrincipal selectGeneral"></select>
                    </div>
                </div>
            </div>
    
            <div class="col-md-6">
            <div id="divCalve">
            <component-text-label label="Clave: " max="9" maxlength="20" id="txtClave" name="txtClave" placeholder="Clave"></component-text-label>
            </div>
            <div id="divClaveFalsa">
            <component-text-label label="Clave: " max="9" maxlength="20" id="txtClaveFalsa" name="txtClaveFalsa" placeholder="Clave"></component-text-label>
            </div>
            </div>

            <div class="row"></div>
            <br>
            <br>

            <div class="col-md-6">
            <component-text-label label="Descripción: " id="txtDescripcion" name="txtDescripcion" placeholder="Descripción"></component-text-label>
            </div>

           
      
            <div class="col-md-6">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Estatus: </label></span>
                    </div>
                    <div class="col-md-9">
                    <select id="txtEstatus" name="txtEstatus" class="form-control txtEstatus selectGeneral">
                            <option value="1" selected>Activo</option>
                            <option value="0">Inactivo</option>
                    </select>
                    </div>
                </div>
            </div>

            <div class="row"></div>
            <br>
            <br>
            <div class="col-md-6">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Disminuye Ingreso: </label></span>
                    </div>
                    <div class="col-md-9">
                    <select id="txtIngreso" name="txtIngreso" class="form-control selectGeneral">
                            <option value="1" selected>Si</option>
                            <option value="0">No</option>
                    </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
            
            </div>
          
            </br>
            </br>


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
        <component-text type="hidden" label="Clave: " id="txtClaveEliminar" name="txtClaveEliminar" placeholder="Clave"></component-text>
        <component-text type="hidden" label="Fuente del Recurso: " id="txtFuenteEliminar" name="txtFuenteEliminar" placeholder="Fuente del Recurso"></component-text>
        <component-button type="button" id="btn" name="btn" onclick="fnEliminarEjecuta()" value="Eliminar"></component-button>
        <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
      </div>
    </div>
  </div>
</div>

<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
<!-- target="_blank" -->
<div align="left">
  <!--Panel Busqueda-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
            <b>Criterios de filtrado</b>
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <div class="row clearfix">
          <div class="col-md-6">
            <div class="form-inline row">
                <div class="col-md-3">
                    <span><label>Objeto Principal: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectObjetoP" name="selectObjetoP[]" class="form-control selectObjetoPrincipal" multiple="multiple">
                    </select>
                </div>
            </div>
          </div>
          <div class="col-md-6">
          <component-text-label label="Clave:" id="txtC" name="txtC" placeholder="Clave" title="Clave" value=""></component-text-label>
          </div>
          <div class="row"></div>
          <br>
          <br>
          <div class="col-md-6">  
            <component-text-label label="Descripción:" id="txtD" name="txtD" placeholder="Descripción" title="Descripción" value=""></component-text-label>
          </div>
        <div class="col-md-6">  
                  <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Estatus: </label></span>
                    </div>
                    <div class="col-md-9">
                    <select id="txtE" name="txtE" class="form-control txtE selectGeneral">
                            <option value="">Selecciona una estatus</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                    </select>
                    </div>
                </div>
                
          </div>
        </div>
        <div class="row"></div>
        <div align="center">
          <br>
          <component-button type="button" id="btnBusqueda" name="btnBusqueda" onclick="fnBuscar()" class="glyphicon glyphicon-search" value="Filtrar"></component-button> 
        </div>
      </div>
    </div>
  </div>






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

<?php
include 'includes/footer_Index.inc';
?>