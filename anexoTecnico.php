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
$PageSecurity = 1;
$PathPrefix = './';
$funcion = 2322;
/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
$title= traeNombreFuncion($funcion, $db,'Panel de Anexo Técnico');
require 'includes/header.inc';
require 'javascripts/libreriasGrid.inc';
$ocultaDepencencia = 'hidden';
?>
<script>
    var nuFuncion = <?= $funcion ?>;
</script>
<script type="text/javascript" src="javascripts/layout_general.js"></script>
<script src="javascripts/anexoTecnico.js?v=<?= rand(); ?>"></script>
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
                                    <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" data-todos="true"   onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')"></select>
                                    <!-- <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" multiple="multiple" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')"></select> -->
                                </div>
                            </div>

                            <br>
                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>UE: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" data-todos="true" ></select>
                                </div>
                            </div>
                        </div><!-- -col-md-4 -->
                        <!-- folio, estatus -->
                        <div class="col-md-4">
                            <component-number-label label="Folio:" id="numeroFolio" name="numeroFolio" value=""></component-number-label>
                            <br>
                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>Estatus: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="status" name="selectEstatusGeneral" class="form-control"></select>
                                    <!-- <select id="selectEstatusGeneral" name="selectEstatusGeneral" class="form-control selectEstatusGeneral"  data-funcion="<?= $funcion ?>"></select> -->
                                </div>
                            </div>
                        </div><!-- -col-md-4 -->
                        <!-- fechas -->
                        <div class="col-md-4">
                            <component-date-label label="Desde:" id="dateDesde" name="dateDesde" placeholder="Desde" title="Desde" value="<?= date('d-m-Y');?>"></component-date-label>
                            <br>
                            <component-date-label label="Hasta:" id="dateHasta" name="dateHasta" placeholder="Hasta" title="Hasta" value="<?= date('d-m-Y');?>"></component-date-label>
                        </div><!-- -col-md-4 -->
                    </div>
                    <div class="row">
                        <component-button type="button" id="btn-search" class="glyphicon glyphicon-search"  value="Filtrar"></component-button>
                        <!-- <button class="btn btn-primary btn-green" id="btn-search"><i class="fa fa-search"></i>&nbsp;Filtrar</button> -->
                        <!-- FIXME agregar comportamiento para los tipos de permisos -->
                        <!-- <component-button type="button" id="btn-modal-upload" class="glyphicon glyphicon-file" value="Cargar Archivo"></component-button> -->
                        <!-- <component-button type="button" id="from-existing" class="glyphicon glyphicon-file" value="Partir de un Existente"></component-button> -->
                    </div>
                </div><!-- .panel-body -->
            </div><!-- .panel-collapse -->
        </div><!-- .panel -->
    </div><!-- .panel-group -->
</div><!-- .row -->

<!-- MODAL -->
<div class="modal fade" id="modal-upload" name="modal-upload" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="navbar navbar-inverse navbar-static-top">
                <!--Contenido Encabezado-->
                <div class="col-md-12 menu-usuario">
                    <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <div class="nav navbar-nav">
                        <div class="title-header"> Carga Masiva de Anexos Técnicos </div>
                    </div>
                </div>
                <div class="linea-verde"></div>
            </div>
            <div class="modal-body">
                <?php include $PathPrefix . 'includes/cargaArchivosAnexoTecnico.inc'; ?>
            </div>
            <div class="modal-footer">
                <component-button type="button" data-dismiss="modal" class="glyphicon glyphicon-trash" value="Cerrar"></component-button>
            </div>
        </div>
    </div>
</div><!-- MODAL -->

<!-- tabla de busqueda -->
<div class="row">
    <div id="tabla">
        <div id="datos"></div>
    </div>
</div><!-- .row -->

<!-- botones de accion -->
<div class="row pt10">
    <div class="panel panel-default">
        <div class="panel-body" align="center">
            <component-button type="button" id="nuevo" class="glyphicon glyphicon-copy"  value="Nuevo"></component-button>
            <span id="areaBotones" name="areaBotones"></span><!-- BOTONES SEGUN PERMISOS -->
            <!-- <button class="btn btn-primary btn-green" id="nuevo"><i class="glyphicon glyphicon-copy"></i>&nbsp;Nuevo</button> -->
        </div>
    </div>
</div><!-- .row -->

<?php require 'includes/footer_Index.inc'; ?>
