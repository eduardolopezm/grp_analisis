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
$title= traeNombreFuncion($funcion, $db);
require 'includes/header.inc';
require 'javascripts/libreriasGrid.inc';
$ocultaDepencencia = 'hidden';
?>
<script>
    var nuFuncion = <?= $funcion ?>;
</script>
<script type="text/javascript" src="javascripts/layout_general.js"></script>
<script src="javascripts/mantenimientoActivoFijo.js?v=<?= rand(); ?>"></script>
<div class="row">
    <div class="col-sm-6 col-sm-offset-3" id="mensaje"></div>
</div>
<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading h35">
                <h4 class="panel-title">
                    <!-- <a data-toggle="collapse" href="#closeTab"> <strong>CRITERIOS DE BÚSQUEDA</strong> </a> -->
                    <div class="fl text-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#closeTab" aria-expanded="true" aria-controls="collapseOne">
                            <b>Criterios de Filtrado</b>
                        </a>
                    </div>
                </h4>
            </div><!-- .panel-heading -->
            <div id="closeTab" class="panel-collapse collapse in">
                <div class="panel-body">
                    <div class="row" id="form-search">
                        <!-- dependencia, UR, UE -->
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
                                    <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" data-todos="true" multiple="true"></select>
                                    <!-- <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" multiple="multiple" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')"></select> -->
                                </div>
                            </div>

                            <br>
                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>UE: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" data-todos="true" multiple="true"></select>
                                </div>
                            </div>

                            <br>
                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>Tipo de Mantenimiento: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectTipoMantenimiento" name="selectTipoMantenimiento" class="form-control selectTipoMantenimiento" data-todos="true" multiple="true"></select>
                                </div>
                            </div>
                        </div><!-- -col-md-4 -->

                        <!-- folio, estatus -->
                        <div class="col-md-4">
                            <component-number-label label="Folio:" id="txtFolio" name="txtFolio" value=""></component-number-label>
                            <br>
                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>Estatus: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectEstatusMantenimiento" name="selectEstatusMantenimiento" class="form-control selectEstatusMantenimiento" multiple="multiple" data-todos="true"></select>
                                </div>
                            </div>
                            <br>
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
                        </div><!-- -col-md-4 -->
                        
                        <!-- fechas -->
                        <div class="col-md-4">
                            <component-date-label label="Desde:" id="dpDesde" name="dpDesde" placeholder="Desde" title="Desde" value="<?= date('d-m-Y');?>"></component-date-label>
                            <br>
                            <component-date-label label="Hasta:" id="dpHasta" name="dpHasta" placeholder="Hasta" title="Hasta" value="<?= date('d-m-Y');?>"></component-date-label>
                        </div><!-- -col-md-4 -->
                        
                    </div>
                    <br>
                    <div class="row">
                        <component-button type="button" id="btnBuscar" name="btnBuscar" class="glyphicon glyphicon-search"  value="Filtrar"></component-button>
                    </div>
                </div><!-- .panel-body -->
            </div><!-- .panel-collapse -->
        </div><!-- .panel -->
    </div><!-- .panel-group -->
</div><!-- .row -->


<!-- tabla de busqueda -->
<div class="row">
    <div id="tablaMantenimiento">
        <div id="datosMantenimiento"></div>
    </div>
</div> <!-- .row -->

<!-- botones de accion -->
<div class="row pt10">
    <div class="panel panel-default">
        <div class="panel-body" align="center">
            <component-button type="button" id="btnNuevo" class="glyphicon glyphicon-copy"  value="Nuevo"></component-button>
            <component-button type="button" id="btnCancelar" name="btnCancelar" class="glyphicon glyphicon-remove"  value="Cancelar"></component-button>
            <span id="areaBotones" name="areaBotones"></span><!-- BOTONES SEGUN PERMISOS -->
            <!-- <button class="btn btn-primary btn-green" id="nuevo"><i class="glyphicon glyphicon-copy"></i>&nbsp;Nuevo</button> -->
        </div>
    </div>
</div><!-- .row -->

<?php require 'includes/footer_Index.inc'; ?>
