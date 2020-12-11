<?php
/**
 * panel de activo fijo
 *
 * @category ABC
 * @package ap_grp
 * @author Jorge Cesar Garcia Baltazar <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/07/2017
 * Fecha Modificación: 31/07/2017
 * Se realizan operación pero el Alta, Baja y Modificación
 */

$PageSecurity=5;
include('includes/session.inc');
$funcion=2308;
$title = traeNombreFuncion($funcion, $db);
include('includes/header.inc');
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

//Librerias GRID
include('javascripts/libreriasGrid.inc');
?>

<div id="OperacionMensaje" name="OperacionMensaje"></div>

<!-- <ul class="nav nav-tabs">
        <li id="primerset" class="active"><a href="#set1" data-toggle="tab" style="width: '50%'">Asignación de resguardos a un empleado</a>
        </li>
        <li id="segundoset" ><a href="#set2" data-toggle="tab">Administración de resguardos</a>
        </li>
    </ul> -->


<!-- <form>
    <div class="radio">
      <label><input type="radio" name="optradio">Activos sin resguardo</label>
    </div>
    <div class="radio">
      <label><input type="radio" name="optradio">Activos con resguardo</label>
    </div>
    <div class="radio">
      <label><input type="radio" name="optradio" disabled>Option 3</label>
    </div>
  </form>
 -->
<!-- <div class="tab-content"> -->
  <!--  -->

<div id="oculto"></div>
<div class="tab-pane fade active in" id="set1" style="width: '50%'; Display: none;">

  <br>

  <div id='paso1' style="display: none;">

    <br>

    <div class="panel panel-default">
      <div class="panel-heading" role="tab" id="headingOne">
        <h4 class="panel-title row">
          <div class="col-md-6 col-xs-6 text-left">
            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">Resguardo actual del empleado</a>
            <h6>Nota: Puede quitar del resguardo actual los bienes listados dando click en "Eliminar" (Posteriormente presione el botón "Siguiente")</h6>
          </div>
        </h4>

        <br>
        <div name="divTablaResguardoEmpleado" id="divTablaResguardoEmpleado">
          <div id="divResguardoDelEmpleado" name="divResguardoDelEmpleado"></div>
        </div>
      </div>
    </div>

    <div align="center">
      <component-button type="button" id="btnPaso2" name="btnPaso2" value="Siguiente" onclick="fnPasarPaso2()" class="glyphicon glyphicon-plus"></component-button>

      <br>
      <br>
    </div>
  </div> <!-- paso1 -->


  <div id="paso2" style="display: none;">
    <div class="panel panel-default">
      <div class="panel-heading" role="tab" id="headingOne">
        <h4 class="panel-title row">
          <div class="col-md-6 col-xs-6 text-left">
            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
              Agregar los activos disponibles al resguardo del empleado
            </a>
            <h6>Nota: Marque el checkbox de los activos que desee agregar (Posteriormente presione el botón "Crear Resguardo" para crear e imprimir el nuevo resguardo)</h6>
          </div>
        </h4>
        <div name="divTabla" id="divTabla">
          <div id="divCatalogo" name="divCatalogo"></div>
        </div>

        <div align="center">
          <component-button type="button" id="btnRegresar" name="btnRegresar" value="Regresar" onclick="fnModificarResguardo(0)" class="glyphicon glyphicon-plus"></component-button>
          <component-button type="button" id="btnAgregar" name="btnAgregar" value="Crear resguardo" onclick="fnCrearResguardo()" class="glyphicon glyphicon-plus"></component-button>

          <br>
          <br>
        </div> 
      </div> 
    </div> 
  </div>

  <div class="tab-pane" id="set2" style="width: '50%'">
    <div class="panel panel-default">

      <div class="panel-heading" role="tab" id="headingOne">
        <h4 class="panel-title row">
          <div class="col-md-3 col-xs-3 text-left">
            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
              Criterios de Filtrado
            </a>
          </div>
        </h4>
      </div>
      <div id="divBusquedaTab2" name="divBusquedaTab2" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
        <div class="panel-body">
          <form id="frmFiltros">
            <!-- =========== <FILTROS> ============== -->
            <div class="row">

              <!-- UR, UE -->
              <div class="col-md-4">
                <div class="form-inline row">
                  <div class="col-md-3" style="vertical-align: middle;">
                    <span><label>UR: </label></span>
                  </div>
                  <div class="col-md-9">
                    <select id="selectUnidadNegocio" name="selectUnidadNegocio[]" class="form-control selectUnidadNegocio" multiple="multiple" data-todos="true"></select>
                  </div>
                </div>

                <br>
                <div class="form-inline row">
                  <div class="col-md-3" style="vertical-align: middle;">
                    <span><label>UE: </label></span>
                  </div>
                  <div class="col-md-9">
                    <select id="selectUnidadEjecutora" name="selectUnidadEjecutora[]" class="form-control selectUnidadEjecutora" multiple="multiple" data-todos="true" ></select>
                  </div>
                </div>
                <!-- <br>
                <div class="form-inline row">
                  <div class="col-md-3" style="vertical-align: middle;">
                    <span><label>Activo Fijo: </label></span>
                  </div>
                  <div class="col-md-9">
                    <select id="selectPatrimonio" name="selectPatrimonio[]" class="form-control selectPatrimonioGeneral" multiple="multiple" data-todos="true" ></select>
                  </div>
                </div> -->
              </div><!-- -col-md-4 -->

              <!-- Empleado, Folio -->
              <div class="col-md-4">

                <div class="form-inline row">
                  <div class="col-md-3" style="vertical-align: middle;">
                    <span><label>Folio: </label></span>
                  </div>
                  <div class="col-md-9">
                    <input class="form-control w100p" type="number" id="txtFolio" name="txtFolio" min="0" placeholder="Folio">
                  </div>
                </div>
                <br>

                <div class="form-inline row">
                  <div class="col-md-3" style="vertical-align: middle;">
                    <span><label>Estatus: </label></span>
                  </div>
                  <div class="col-md-9">
                    <select id="selectEstatusResguardo" name="selectEstatusResguardo[]" class="form-control selectEstatusResguardo" multiple="multiple" data-todos="true"></select>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <!-- <br> -->
                <div class="form-inline row">
                  <div class="col-md-3" style="vertical-align: middle;">
                    <span><label>Empleado: </label></span>
                  </div>
                  <div class="col-md-9">
                    <select id="selectEmpleadotab2" name="selectEmpleadotab2[]" class="form-control  selectEmpleados" multiple="multiple" data-todos="true">
                    </select>
                  </div>
                </div>
              </div><!-- -col-md-4 -->

              <!-- Fechas -->
              <!-- se busca por empleado -->
              <!-- <div class="col-md-4">
                <component-date-label label="Desde:" id="txtFechaInicial" name="txtFechaInicial" placeholder="Desde" title="Desde" value="<?= date('d-m-Y');?>"></component-date-label>
                <br>
                <component-date-label label="Hasta:" id="txtFechaFinal" name="txtFechaFinal" placeholder="Hasta" title="Hasta" value="<?= date('d-m-Y');?>"></component-date-label>
              </div> --><!-- -col-md-4 -->

            </div>
            <br>
            <br>
            <!-- Botones -->
            <div class="row">
              <div class="col-xs-12">
                <component-button  type="button" id="btnBuscar" name="btnBuscar" class="glyphicon glyphicon-search" value="Filtrar" onclick="fnMostrarDatos()"></component-button>
              </div>
            </div>
            <!-- =========== </FILTROS> ============== -->
          </form>
        </div> 
      </div> <!-- panel default -->
    </div>

    <div id="divTabla2">
      <div id="divResguardos"></div>
    </div>

    <br>
    <br>

    <!-- <div id="viewReporte" style="width: 800px; height: 350px " > </div> -->
  </div>

  <!-- botones de accion -->
  <div class="">
      <div class="panel panel-default">
          <div class="panel-body" align="center">
              <component-button type="button" id="nuevo" class="glyphicon glyphicon-plus"  onclick="fnAgregar()" value="Nuevo"></component-button>
              <span id="areaBotones" name="areaBotones"></span>
          </div>
      </div>
  </div><!-- .row -->

</div>


  <!-- MODAL AGREGAR RESGUARDO -->
  <div class="modal fade" id="ModalUR" name="ModalUR" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
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
            <div class="modal-body">
              <div id="divMensajeOperacion" name="divMensajeOperacion"></div>
              <form id = "frmGenerarResguardo">
                <div class="form-inline">
                  <div class="row" id="modalMsg"></div>
                  <div class="row">
                    <!-- UR, UE -->
                    <div class="col-md-6">
                      <div class="form-inline row">
                        <div class="col-md-3" style="vertical-align: middle;">
                          <span><label>UR: </label></span>
                        </div>
                        <div class="col-md-9">
                          <select id="selectUnidadNegocio_modal" name="selectUnidadNegocio_modal" class="form-control selectUnidadNegocio" data-todos="true" >
                            <option value="-1">Seleccionar...</option>
                          </select> <!-- onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocio_modal', 'selectUnidadEjecutora_modal') -->
                        </div>
                      </div>
                      <br>
                      <div class="form-inline row">
                        <div class="col-md-3" style="vertical-align: middle;">
                          <span><label>UE: </label></span>
                        </div>
                        <div class="col-md-9">
                          <select id="selectUnidadEjecutora_modal" name="selectUnidadEjecutora_modal" class="form-control selectUnidadEjecutora" data-todos="true" >
                            <option value="-1">Seleccionar...</option>
                          </select>
                        </div>
                      </div>
                      <br>
                      <div class="form-inline row">
                        <div class="col-md-3" style="vertical-align: middle;">
                          <span><label>Observaciones: </label></span>
                        </div>
                        <div class="col-md-9">
                          <component-textarea label="Observaciones: " id="txtObservaciones" name="txtObservaciones" rows="2" class="w100p" placeholder="Observaciones" ></component-textarea>
                        </div>
                      </div>
                    </div><!-- UR, UE -->

                    <!-- Empleado, Activo -->
                    <div class="col-md-6">
                      <div class="form-inline row">
                        <div class="col-md-3" style="vertical-align: middle;">
                          <span><label>Empleado: </label></span>
                        </div>
                        <div class="col-md-9">
                          <select id="selectEmpleados_modal" name="selectEmpleados_modal" class="form-control selectEmpleados" data-todos="true" >
                            <option value="-1">Seleccionar...</option>
                          </select>
                        </div>
                      </div>
                      <br>
                      <div class="form-inline row">
                        <div class="col-md-3" style="vertical-align: middle;">
                          <span><label>Activo Fijo: </label></span>
                        </div>
                        <div class="col-md-9">
                          <select id="selectPatrimonio_modal" name="selectPatrimonio_modal" class="form-control" data-todos="true" >
                          </select>
                        </div>
                      </div>
                    </div><!-- Empleado, Activo -->
                  </div><!-- .row-->
                </div><!-- .form-inline -->
              </form> <!-- / form -->
            </div>
            <div class="modal-footer">
                <component-button type="button" data-dismiss="modal" class="glyphicon glyphicon-trash" value="Cerrar"></component-button>
                <component-button type="button" id="btnGenerarResguardo" name="btnGenerarResguardo" class="glyphicon glyphicon-ok" value="Guardar"></component-button>
            </div>
        </div>
    </div>
  </div><!-- / MODAL AGREGAR RESGUARDO -->


<div id="empleadowizard" style="display: none;">
  <div class="form-inline row">

    <div class="col-md-3">
      <span><label>Empleado : </label></span>
    </div>
    <div class="col-md-9"> 
      <select id="selectEmpleado" name="selectEmpleado" class="form-control selectEmpleado" >
        <option value='0'>Seleccionar...</option>
      </select>
    </div>
  </div>
  <br>
</div>


<script type="text/javascript" src="javascripts/activofijo_resguardo.js"></script>

<?php
include 'includes/footer_Index.inc';
