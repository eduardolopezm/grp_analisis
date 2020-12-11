<?php
/**
 * Panel de anexo técnico.
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link /anexoTecnico.php
 * Fecha Creación: 29.12.17
 * Se genera el presente programa para la visualización de la información
 * de los anexos técnicos que se generan para las inquisiciones.
 */
/* DECLARACION DE VARIABLES */
$PageSecurity = 3;
$PathPrefix = './';
$funcion = 1987;
/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
$title= traeNombreFuncion($funcion, $db,'Registrar Mantenimiento');
require 'includes/header.inc';
require 'javascripts/libreriasGrid.inc';
$ocultaDepencencia = 'hidden';

$folioMtto=0;

if(isset($_GET['Folio'])){
    $folioMtto=$_GET['Folio'];
}


?>
<script>
    var nuFuncion = <?= $funcion ?>;
    var folioMtto = <?= $folioMtto ?>;
</script>

<script type="text/javascript" src="javascripts/layout_general.js"></script>
<link rel="stylesheet" href="css/listabusqueda.css" />
<div class="row">
    <div class="col-sm-6 col-sm-offset-3" id="mensaje"></div>
</div>
<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading h35">
                <h4 class="panel-title">
                    <!-- <a data-toggle="collapse" href="#closeTab"> <strong>CRITERIOS DE BÚSQUEDA</strong> </a> -->
                    <div class="col-md-3 col-xs-3 fl text-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#closeTab" aria-expanded="true" aria-controls="collapseOne">
                            <b>Encabezado</b>
                        </a>
                    </div>
                    <div class="col-md-2 col-xs-12 pull-right">
                      <span class="pull-right" style="margin-right: 40px;">Folio <b id="txtNoCaptura" name="txtNoCaptura" style="margin-left: 20px;"></b></span>
                    </div>

                </h4>
            </div><!-- .panel-heading -->
            <div id="closeTab" class="panel-collapse collapse in">
                <div class="panel-body">
                    <div class="row" id="formSearch">
                        
                        <!-- dependencia, UR, UE, Almacen-->
                        <div class="col-md-4">
                            <div class="form-inline row <?= $ocultaDepencencia ?>">
                                <div class="col-md-3">
                                    <span><label>Dependencia: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioDependeciaGeneral('selectRazonSocial', 'selectUnidadNegocio')" multiple="true"></select>
                                </div>
                            </div>

                            <br class="<?= $ocultaDepencencia ?>">

                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>UR: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" data-todos="true" ></select>
                                    <!-- <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" multiple="multiple" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')"></select> -->
                                </div>
                            </div>

                            <br>
                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>UE: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" data-todos="true"></select>
                                </div>
                            </div>
                        </div><!-- -col-md-4 -->
                        
                        <!-- folio, estatus -->
                        <div class="col-md-4">
                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>Tipo de Bien: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectTipoBien" name="selectTipoBien" class="form-control selectTipoBien" required="">
                                        <option value='-1'>Seleccionar...</option>
                                    </select>
                                </div>
                            </div>
                            <br>
                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>Tipo de Mantenimiento: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectTipoMantenimiento" name="selectTipoMantenimiento" class="form-control selectTipoMantenimiento" data-todos="true">
                                        <option value='-1'>Seleccionar...</option>
                                    </select>
                                </div>
                            </div>
                        </div><!-- -col-md-4 -->
                        
                        <!-- fechas -->
                        <div class="col-md-4">
                            <component-date-label label="Fecha Captura:" id="dpFechaCaptura" name="dpFechaCaptura" placeholder="Fecha Captura" title="Fecha Captura" value="<?= date('d-m-Y');?>" readonly></component-date-label>
                            <br>
                            <component-date-label label="Programación del Mtto.:" id="dpProgramacionMtto" name="dpProgramacionMtto" placeholder="Programación del Mtto." title="Programación del Mtto." value="<?= date('d-m-Y');?>"></component-date-label>
                        </div><!-- -col-md-4 -->

                        <div class="col-md-4">
                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>Almacén: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectAlmacen" name="selectAlmacen" class="form-control " data-todos="true"></select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="form-inline row">
                                <div class="col-md-2" style="vertical-align: middle;">
                                    <span><label>Observaciones: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <textarea id="txtObservacion" name="txtObservacion" class="form-control" rows="3" style="width:100%"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- requisicion -->
                        <div class="col-md-4">
                            <component-text-label label="Requisición:" id="txtRequisicion" name="txtRequisicion" placeholder="Requisición" title="Requisición" title="Requisición" disabled></component-text-label> 
                        </div><!-- -col-md-4 -->
                        
                    </div>
                    <br>
                    
                </div><!-- .panel-body -->
            </div><!-- .panel-collapse -->
        </div><!-- .panel -->
    </div><!-- .panel-group -->
</div><!-- .row -->

<!-- Panel de clave presupuestal -->
<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading h35">
                <h4 class="panel-title">
                    <!-- <a data-toggle="collapse" href="#closeTab"> <strong>CRITERIOS DE BÚSQUEDA</strong> </a> -->
                    <div class="fl text-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#closeTab" aria-expanded="true" aria-controls="collapseOne">
                            <b>Presupuesto</b>
                        </a>
                    </div>
                </h4>
            </div><!-- .panel-heading -->
            <div id="closeTab" class="panel-collapse collapse in">
                <div class="panel-body">
                    <div class="row">
                        <div id="divClavePresupuestal" class="col-md-12">
                            <div class="form-inline row">
                                <div class="col-md-1" style="vertical-align: middle;">
                                    <span><label>Clave Presupuestal: </label></span>
                                </div>
                                <div class="col-md-11">
                                    <input class="form-control" type="text" id="txtBuscarPresupuesto" name="txtBuscarPresupuesto" placeholder="Buscar Presupuesto" title="Buscar Presupuesto" title="Buscar Presupuesto" style="width: 100%" >
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div><!-- .panel-body -->
            </div><!-- .panel-collapse -->
        </div><!-- .panel -->
    </div><!-- .panel-group -->
</div><!-- .row -->


<!-- Panel Agregar Bienes -->
<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading h35">
                <h4 class="panel-title">
                    <!-- <a data-toggle="collapse" href="#closeTab"> <strong>CRITERIOS DE BÚSQUEDA</strong> </a> -->
                    <div class="fl text-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#closeTab" aria-expanded="true" aria-controls="collapseOne">
                            <b>Agregar Bienes</b>
                        </a>
                    </div>
                </h4>
            </div><!-- .panel-heading -->
            <div id="closeTab" class="panel-collapse collapse in">
                <div class="panel-body">
                    <div id="PanelAgregarActivoFijo" name="PanelAgregarActivoFijo" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" >
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>Partida Especifica: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="selectCategoriaActivo" name="selectCategoriaActivo" class="form-control selectCategoriaActivo" multiple="true" required="">
                                            <!-- <option value='-1'>Seleccionar...</option> -->
                                        </select>
                                    </div>
                                </div>
                            </div><!-- -col-md-4 -->
                            <div class="col-md-4">
                                <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>CABMS: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="selectClaveCABMS" name="selectClaveCABMS" class="form-control selectClaveCABMS" multiple="true" required="">
                                            <!-- <option value='-1'>Seleccionar...</option> -->
                                        </select>
                                    </div>
                                </div>
                            </div><!-- -col-md-4 -->
                            <div class="col-md-4">
                                <div class="form-inline row">
                                    <component-button type="button" id="btnObetenerPatrimonio" name="btnObetenerPatrimonio" class="glyphicon glyphicon-search"  value="Obtener Bienes"></component-button>
                                </div>
                            </div><!-- -col-md-4 -->
                        </div>
                        <br>
                        <table class="table table-bordered table-condensed" name="tablaActivoFijo" id="tablaActivoFijo">
                            <thead>
                                <tr class="header-verde">
                                    <th style="text-align:center;"></th>
                                    <th style="text-align:center;">#</th>
                                    <th style="text-align:center;">No. Inventario</th>
                                    <th style="text-align:center;">Clave Bien</th>
                                    <th style="text-align:center;">Marca</th>
                                    <th style="text-align:center;">Descripción</th>
                                    <th style="text-align:center;">Observación</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                      <!-- </div> -->
                    </div>
                    <br>
                </div><!-- .panel-body -->
            </div><!-- .panel-collapse -->
        </div><!-- .panel -->
    </div><!-- .panel-group -->
</div><!-- .row -->

<div class="row">
    
</div>

<!-- row de botones -->
<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-body" align="center" id="divBotones" name="divBotones">
              <component-button type="button" id="btnGuardar" name="btnGuardar" class="glyphicon glyphicon-floppy-disk"  value="Guardar"></component-button>
              <component-button type="button" id="btnFinalizar" name="btnFinalizar" class="glyphicon glyphicon-flag hide"  value="Finalizar"></component-button>
              <component-button type="button" id="btnCancelar" name="btnCancelar" class="glyphicon glyphicon-remove"  value="Cancelar"></component-button>
              <a id="linkResguardos" name="linkResguardos" href="mantenimientoActivoFijo.php" class="btn btn-default botonVerde glyphicon glyphicon-arrow-left"> Regresar</a>
            </div>
        </div>
    </div>
</div>

<!-- script -->
<script type="text/javascript" src="javascripts/mantenimientoActivoFijoDetalle.js?v=<?= rand(); ?>"></script>

<?php require 'includes/footer_Index.inc'; ?>