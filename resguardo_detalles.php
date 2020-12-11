<?php
/**
 * Panel de anexo técnico.
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link /anexoTecnico.php
 * Fecha Creación: 04.05.18
 * Se genera el presente programa para la visualización de la información
 * de los resguardos a nivel detalle.
 */
//
/* DECLARACION DE VARIABLES */
//
$PageSecurity = 1;
$PathPrefix = './';
$funcion = 2308;

/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
// capa

$title= traeNombreFuncion($funcion, $db);
require 'includes/header.inc';
require 'javascripts/libreriasGrid.inc';

if (isset($_GET['Folio'])) {
    $int_folio_resguardo = $_GET['Folio'];
}else{
  $int_folio_resguardo='';
}

echo "<input type='text' id='txtFolioResguardo' class='hide' value='".$int_folio_resguardo."'>";

?>

<script type="text/javascript">
	$(document).ready(function(){
	    $('[data-toggle="tooltip"]').tooltip();
	});

	var int_folio_resguardo = '<?php echo $int_folio_resguardo; ?>';


</script>

<script type="text/javascript" src="javascripts/layout_general.js"></script>

<!-- Filtros de Búsqueda -->
<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading h35">
                <h4 class="panel-title">
                    <!-- <a data-toggle="collapse" href="#closeTab"> <strong>CRITERIOS DE BÚSQUEDA</strong> </a> -->
                    <div class="fl text-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#closeTab" aria-expanded="true" aria-controls="collapseOne">
                            <b>Encabezado</b>
                        </a>
                    </div>
                </h4>
            </div><!-- .panel-heading -->
            <div id="divCloseTab" class="panel-collapse collapse in">
                <div class="panel-body">
                	<form id="frmResguardoEncabezado">
                		<div class="row">
                			<!-- UR, UE -->
				            <div class="col-md-4">
				                <div class="form-inline row">
				                  <div class="col-md-3" style="vertical-align: middle;">
				                    <span><label>UR: </label></span>
				                  </div>
				                  <div class="col-md-9">
				                    <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" data-todos="true"></select>
				                  </div>
				                </div>

				                <br>
				                <div class="form-inline row">
				                  <div class="col-md-3" style="vertical-align: middle;">
				                    <span><label>UE: </label></span>
				                  </div>
				                  <div class="col-md-9">
				                    <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control " data-todos="true" ></select>
				                  </div>
				                </div>
				                <br>
				                <div class="form-inline row">
			                        <div class="col-md-3" style="vertical-align: middle;">
			                          <span><label>Observaciones: </label></span>
			                        </div>
			                        <div class="col-md-9">
                                <textarea  id="txtObservaciones" name="txtObservaciones" placeholder="Observaciones" title="Observaciones" rows="2" class="form-control w100p" style="resize: vertical;"></textarea>
			                        </div>
			                    </div>
				            </div><!-- -col-md-4 -->

				            <!-- Empleado, Folio -->
				            <div class="col-md-4">

				                <div class="form-inline row">
				                  <div class="col-md-3" style="vertical-align: middle;">
				                    <span><label>Folio: </label></span>
				                  </div>
				                  <div class="col-md-9">
				                    <input class="form-control w100p" type="text" id="txtFolio" name="txtFolio" min="0" placeholder="Folio" value="<?= $int_folio_resguardo; ?>" readonly>
				                  </div>
				                </div>
				                <br>
				                <div class="form-inline row">
				                  <div class="col-md-3" style="vertical-align: middle;">
				                    <span><label>Empleado: </label></span>
				                  </div>
				                  <div class="col-md-9">
				                    <select id="selectEmpleadotab2" name="selectEmpleadotab2" class="form-control" data-todos="true">
                              <option value="-1">Sin Seleccion</option>
				                    </select>
				                  </div>
				                </div>
                        <br>
                        <div class="form-inline row">
                          <div class="col-md-3" style="vertical-align: middle;">
                            <span><label>Estatus: </label></span>
                          </div>
                          <div class="col-md-9">
                            <input class="form-control w100p" type="text" id="txtEstatusRes" name="txtEstatusRes" 
                            placeholder="Estatus Resguardo" readonly>
                          </div>
                        </div>
				            </div><!-- -col-md-4 -->
				            <!-- Fecha Registro, Fecha ultima modificacion -->
				             <div class="col-md-4">
				                <component-date-label label="Fecha Registro:" id="txtFechaInicial" name="txtFechaInicial" placeholder="Desde" title="Desde" value="<?= date('d-m-Y');?>" readonly></component-date-label>
				                <br>
                        <div id="divFechaModificacion" > <!-- style="display: none" -->
                          <component-date-label label="Fecha Modificación:" id="txtFechaFinal" name="txtFechaFinal" placeholder="Hasta" title="Hasta" value="" readonly></component-date-label>
                        </div>
                        <br>
                        <div class="form-inline row">
                              <div class="col-md-3" style="vertical-align: middle;">
                                <span><label>Ubicación Empleado: </label></span>
                              </div>
                              <div class="col-md-9">
                                <!-- <component-textarea label="Ubicación Empleado: " id="txtUbicacion" name="txtUbicacion" rows="2" class="form-control w100p" placeholder="Ubicación Empleado" maxlength="300" style=" resize:vertical;" readonly></component-textarea> -->
                                <textarea id="txtUbicacion" name="txtUbicacion" placeholder="Ubicación Empleado" title="Ubicación Empleado" rows="2" class="form-control w100p" style=" resize:vertical;"></textarea>
                              </div>
                          </div>
			                    
				            </div><!-- -col-md-4 -->

                		</div>
                	</form>
                </div><!-- .panel-body -->
            </div><!-- .panel-collapse -->
        </div><!-- .panel -->
    </div><!-- .panel-group -->
</div><!-- / Filtros de Búsqueda -->


<!--  cargar archivos -->
<div class="row">
<div id="divPanelArchivos" class="panel panel-default ">
  <div class="panel-heading" role="tab" id="headingOne">
    <h4 class="panel-title row">
      <div class="col-md-3 col-xs-3 text-left">
        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelAddarchivo" aria-expanded="true" aria-controls="collapseOne">
            <b>Archivos</b>
        </a>
      </div>
      <div  class="fr text-right ftc7">
        <span id="numeroFolio"></span>
      </div>
    </h4>
  </div>

  <div id="PanelAddarchivo" name="PanelAddarchivo" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">

      <div class="panel-body text-left">
          
        <!-- begin upload file -->
        <div class="col-xs-12 col-md-12 pt20">  
           <div class="soloCargarArchivos" id="uploadFilesDiv"> 
              <input type="hidden" id="esMultiple" name="esMultiple" value="1">
              <input type="hidden" value="" name="componente" id="componenteArchivos"/>
              <input type="hidden" value="2387" id="funcionArchivos" name="funcionArchivos"/>
              <input  type="hidden"  value="285" id="tipoArchivo"/>
              <input  type="hidden"  value="" id="transnoArchivo"/>
                <input  type="hidden"  value="" id="numberScene"/>
              <div id="mensajeArchivos"> </div>
              <div  id="subirArchivos"  class="col-md-12">
                  <div  style="color:#fff !important;">
                      <div class="col-md-6">
                          <div id="tipoInputFile"> </div> <!-- Set type to upload one or many files -->
                          <!--<input type="file"  class="btn bgc8"  :name="archivos','[]"  id="cargarMultiples"  multiple="multiple"  style="display: none;"/>-->
                          <button  class="btn bgc8" id="btnUploadFile" onclick="">
                              <span class="glyphicon glyphicon-file"></span>
                              Cargar oficio(s)
                          </button >

                            <button id="descargarMultiples" onclick="fnProcesosArchivosSubidos('descargar')" class="btn bgc8" style="display: none;">Descargar</button> 
                          <!-- -->

                          <!-- -->
                          <br>
                          <br>
                      </div>
                      <br>
                  </div>
                  
                  <div id="muestraAntesdeEnviar" class="" style="display: none;"> <!-- show files upload -->
                      <table class="table table-striped border" id="tablaDetallesArchivos" style="border:solid 1px #eee;">
                        <thead class="bgc8 text-center" style="color:#fff;">
                            <th class="text-center">Nombre</th>
                            <th class="text-center">Observaciones</th>
                            <th> </th> 
                        </thead>
                        <tbody>
                        </tbody>
                      </table>
                  </div>
              </div>
          
        </div>
      </div> <!--end  upload  file -->
    </div>
  </div>
</div><!--  cargar archivos -->
</div>

<!-- Tabla de contenido -->
<!-- <div class="row">
	<div name="divTabla" id="divTabla">
	    <div name="divDetalle" id="divDetalle"></div>
	</div> 
</div> --><!-- / Tabla de contenido -->

  <div id="main-container" class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 p0 m0">
      <!-- Nav tabs -->
      <ul class="nav nav-tabs" role="tablist">
        <li id="idArticuloTap" role="presentation" class="active">
          <a href="#idArticuloContainerTab" aria-controls="Articulos" role="tab" data-toggle="tab" title="Articulos" class="bgc10">Activos Fijos</a>
        </li>
      </ul>
      <!-- Tab panes -->
      <div id="idTab-content" class="tab-content">
        <div id="idArticuloContainerTab" role="tabpanel" class="instrumentalContainerTab tab-pane active">
          <nav id="idNavArticulos" class="row nav bgc8 fts10 borderGray w100p ftc2">
            <div id="navArticulos" class="col-lg-12 col-md-12 col-sm-12 p0 m0">
              <div class="w3p pt2">
                <!-- onclick="fnValidaExistenciaRequisicion(producto);" -->
                <span id="btnAgregarActivo" class="glyphicon glyphicon-plus btn btn-default btn-xs"></span>
              </div>
              <div class="w15p"><label >Número Inventario</label></div>
              <div class="w7p"><label >Tipo Bien</label></div>
              <div class="w20p"><label >Descripción</label></div>
              <div class="w7p"><label >Estatus</label></div>
              <div class="w7p"><label >Fecha Registro</label></div>
              <div class="w7p"><label >Fecha Baja</label></div>
              <div class="w7p"><label >Km. Inicial</label></div>
              <div class="w7p"><label >Km. Final</label></div>
              <div class="w20p"><label >Observación</label></div>
            </div>
          </nav>
          <div id="idDivActivosFijos" class="borderGray m0 p0">
          </div>
        </div>
      </div>
    </div>
  </div>

<br>
<!-- Botones -->
<div class="row">
	<div class="panel panel-default">
	    <div class="panel-body" align="center" id="divBotones" name="divBotones">
        <!-- <component-button type="button" id="guardarProd" name="guardarProd" value="Agregar" class="glyphicon glyphicon-floppy-disk" data-toggle="modal" data-target="#ModalUR"></component-button> -->   
	    	<component-button type="button" id="guardarResguardo" name="guardarResguardo" value="Guardar" class="glyphicon glyphicon-floppy-disk"></component-button>   
        <component-button type="button" id="btnCancelarCampos" name="btnCancelarCampos" value="Cancelar" class="glyphicon glyphicon-remove"></component-button>  
        <component-button type="button" id="btnBajaActivo" name="btnBajaActivo" value="Baja" class="glyphicon glyphicon-minus hide"></component-button>  
        
	      <a id="linkResguardos" name="linkResguardos" href="activofijo_resguardos.php" class="btn btn-default botonVerde glyphicon glyphicon-arrow-left"> Regresar</a>
	    </div>
	</div> 
</div><!-- / Botones -->

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
            <form id = "frmAgregarResguardo">
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
                        <select id="selectUnidadNegocio_modal" name="selectUnidadNegocio_modal" class="form-control " data-todos="true"></select>
                      </div>
                    </div>
                    <br>
                    <div class="form-inline row">
                      <div class="col-md-3" style="vertical-align: middle;">
                        <span><label>UE: </label></span>
                      </div>
                      <div class="col-md-9">
                        <select id="selectUnidadEjecutora_modal" name="selectUnidadEjecutora_modal" class="form-control " data-todos="true" ></select>
                      </div>
                    </div>
                    <br>
                    <div class="form-inline row">
                      <div class="col-md-3" style="vertical-align: middle;">
                        <span><label>Observaciones: </label></span>
                      </div>
                      <div class="col-md-9">
                        <component-textarea label="Observaciones: " id="txtObservaciones_modal" name="txtObservaciones_modal" rows="2" class="w100p" placeholder="Observaciones" ></component-textarea>

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
                        <select id="selectEmpleados_modal" name="selectEmpleados_modal" class="form-control " data-todos="true" ></select>
                      </div>
                    </div>
                    <br>
                    <div class="form-inline row">
                      <div class="col-md-3" style="vertical-align: middle;">
                        <span><label>Activo Fijo: </label></span>
                      </div>
                      <div class="col-md-9">
                        <select id="selectPatrimonio_modal" name="selectPatrimonio_modal" class="form-control selectPatrimonio" data-todos="true" ></select>
                      </div>
                    </div>
                  </div><!-- Empleado, Activo -->
                </div><!-- .row-->
              </div><!-- .form-inline -->
            </form> <!-- / form -->
          </div>
          <div class="modal-footer">
              <component-button type="button" data-dismiss="modal" class="glyphicon glyphicon-trash" value="Cerrar"></component-button>
              <component-button type="button" id="btnAgregarResguardo" name="btnAgregarResguardo" class="glyphicon glyphicon-ok" value="Guardar"></component-button>
          </div>
      </div>
  </div>
</div><!-- / MODAL AGREGAR RESGUARDO -->


<!-- MODAL AGREGAR RESGUARDO -->
<div class="modal fade" id="modalModificar" name="modalModificar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
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
                        <div id="modalModificar_Titulo" name="modalModificar_Titulo"></div>
                      </div>
                  </div>
              </div>
              <div class="linea-verde"></div>
          </div>
          <div class="modal-body">
            
          </div>
          <div class="modal-footer">
              <component-button type="button" data-dismiss="modal" class="glyphicon glyphicon-trash" value="Cerrar"></component-button>
              <component-button type="button" id="btnModificarResguardo" name="btnModificarResguardo" class="glyphicon glyphicon-ok" value="Guardar"></component-button>
          </div>
      </div>
  </div>
</div><!-- / MODAL AGREGAR RESGUARDO -->


<!-- MODAL BAJA RESGUARDO -->
<div class="modal fade" id="modalBajaActivo" name="modalBajaActivo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
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
                        <div id="modalBajaActivo_Titulo" name="modalBajaActivo_Titulo">Baja de activo</div>
                      </div>
                  </div>
              </div>
              <div class="linea-verde"></div>
          </div>
          <div id="divContenidoBaja" class="modal-body">

            
          </div>
          <div class="modal-footer">
              <component-button type="button" data-dismiss="modal" class="glyphicon glyphicon-trash" value="Cerrar"></component-button>
              <component-button type="button" id="btnConfirmarBajaActivoFijo" name="btnConfirmarBajaActivoFijo" class="glyphicon glyphicon-ok" value="Guardar"></component-button>
          </div>
      </div>
  </div>
</div><!-- / MODAL BAJA RESGUARDO -->

<script type="text/javascript" src="javascripts/resguardo_detalles.js"></script>

<?php
include 'includes/footer_Index.inc';