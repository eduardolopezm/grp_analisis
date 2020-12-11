<?php 
/**
 * ABC de Fuente de Financiamiento
 *
 * @category ABC
 * @package ap_grp
 * @author Jesùs Reyes Santos  <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 07/11/2019
 * Fecha Modificación: 07/11/2019
 * Se realizan operación pero el Alta, Baja y Modificación
 */
session_start();
$PageSecurity = 5;
include 'includes/session.inc';
//$title = _('Mantenimiento Subfunción');
$funcion = 2537;
$title = traeNombreFuncion($funcion, $db);

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
include 'javascripts/libreriasGrid.inc';
?>

<script type="text/javascript" src="javascripts/abcMetaConcepto.js"></script>

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
      <div id="divMensajeOperacion" name="divMensajeOperacion" class="m10"></div>
      <div class="modal-body" id="ModalUR_Mensaje" name="ModalUR_Mensaje">
        <!--Mensaje o contenido-->
        <div id="msjValidacion" name="msjValidacion"></div>
        <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Objeto Principal: </label></span>
                    </div>
                    <div class="col-md-9">
                    <input type="hidden" id="txtIdMeta" name="txtIdMeta">
                    <select id="selectObjetoPrincipalUsuarios" name="selectObjetoPrincipalUsuarios" class="selectObjetoPrincipalUsuarios selectGeneral "></select>
                    </div>
                </div> 
            </div>
            
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Mes: </label></span>
                    </div>
                    <div class="col-md-9">
                    <select id="selectMes" name="selectMes" class="form-control selectGeneral">
                            <option value="" selected >Sin selección...</option>
                            <option value="01">Enero</option>
                            <option value="02">Febrero</option>
                            <option value="03">Marzo</option>
                            <option value="04">Abril</option>
                            <option value="05">Mayo</option>
                            <option value="06">Junio</option>
                            <option value="07">Julio</option>
                            <option value="08">Agosto</option>
                            <option value="09">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                    </select>
                    </div>
                </div>
            </div>


            <div class="col-md-4">
            <component-number-label label="Año: " id="nuAnio" name="nuAnio" placeholder="Año" value="<?php echo $_SESSION['ejercicioFiscal'] ?>"></component-number-label>
            </div>

            

            <div class="row"></div>
            <br>
            <br>

            <div class="col-md-4">
                <component-decimales-label label="% Meta: " id="porMeta" name="porMeta" placeholder="% Meta" maxlength="3" ></component-decimales-label>
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
        <component-text type="hidden" label="Descuentos: " id="nu_id_meta" name="nu_id_meta" placeholder="Finalidad"></component-text>
        <component-button type="button" id="btn" name="btn" onclick="fnEliminarEjecuta()" value="Eliminar"></component-button>
        <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
      </div>
    </div>
  </div>
</div>

<!-- Criterios de Busqueda -->
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
       
       
        <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Objeto Principal: </label></span>
                    </div>
                    <div class="col-md-9">
                    <select id="selectPrincipal" name="selectPrincipal" class="form-control selectObjetoPrincipalUsuarios selectGeneral " onchange="fnFinalidad()"></select>
                    </div>
                </div> 
            </div>

            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Mes: </label></span>
                    </div>
                    <div class="col-md-9">
                    <select id="selectM" name="selectM" class="form-control selectGeneral">
                            <option value="" selected >Sin selección...</option>
                            <option value="01">Enero</option>
                            <option value="02">Febrero</option>
                            <option value="03">Marzo</option>
                            <option value="04">Abril</option>
                            <option value="05">Mayo</option>
                            <option value="06">Junio</option>
                            <option value="07">Julio</option>
                            <option value="08">Agosto</option>
                            <option value="09">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                    </select>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
            <component-number-label label="Año: " id="nuAn" name="nuAn" placeholder="Año"></component-number-label>
            </div>

        <div class="row"></div>
        <div align="center">
          <br>
          <component-button type="button" id="btnBusqueda" name="btnBusqueda" onclick="fnBuscar()" class="glyphicon glyphicon-search" value="Filtrar"></component-button> 
        </div>
      </div>
    </div>
  </div>

<!-- .row -->

<!-- tabla de busqueda -->
<div class="row container-fluid">
  <div name="divTabla" id="divTabla">
    <div name="divContenidoTabla" id="divContenidoTabla"></div>
  </div>
</div>

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