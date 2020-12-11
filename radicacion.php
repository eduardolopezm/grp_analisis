<?php
/**
 * Panel de Radicación.
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link /radicacion.php
 * Fecha Creación: 11.06.18
 * Se genera el presente programa para la visualización de la información
 * de la radicación.
 */

/* DECLARACION DE VARIABLES */
$PageSecurity = 1;
$PathPrefix = './';
$funcion = 2388;

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


$permisoAutorizador= Havepermission($_SESSION['UserID'], 2434, $db);
$permisoValidador= Havepermission($_SESSION['UserID'], 2431, $db);
$permisoCapturista= Havepermission($_SESSION['UserID'], 2430, $db);
$permisoAutorizadorOficinaCentral= Havepermission($_SESSION['UserID'], 2434, $db);

?>
<script>
    var nuFuncion = <?= $funcion ?>;
    var permisoAutorizador = <?= $permisoAutorizador ?>;
    var permisoValidador = <?= $permisoValidador ?>;
    var permisoCapturista = <?= $permisoCapturista ?>;
    var permisoAutorizadorOficinaCentral = <?= $permisoAutorizadorOficinaCentral ?>;
</script>
<script type="text/javascript" src="javascripts/layout_general.js"></script>

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

                            <br >
                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>UE: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" data-todos="true" multiple="true" ></select>
                                </div>
                            </div>
                            
                            <br>

                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>Estatus: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectEstatusMinistracion" name="selectEstatusMinistracion" class="form-control selectEstatusMinistracion" data-todos="true" multiple="true"></select>
                                </div>
                            </div>
                        </div><!-- -col-md-4 -->
                        
                        <div class="col-md-4">
                            
                            <component-text-label label="Folio:" id="txtFolio" name="txtFolio" placeholder="Folio" title="Folio" title="Folio"></component-text-label>  

                            <br>

                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>Programa Presupuestal: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectProgramaPresupuestal" name="selectProgramaPresupuestal" class="form-control selectProgramaPresupuestario" data-todos="true" multiple="true">
                                    </select>
                                </div>
                            </div>
                            <br>
                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>Mes Solicitado: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectMesMinistracion" name="selectMesMinistracion[]" class="form-control  selectMeses" data-todos="true" multiple="true"></select>
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
                        <br>
                        <component-button type="button" id="btn-search" class="glyphicon glyphicon-search"  value="Filtrar"></component-button>
                    </div>
                </div><!-- .panel-body -->
            </div><!-- .panel-collapse -->
        </div><!-- .panel -->
    </div><!-- .panel-group -->
</div><!-- .row -->

<!-- tabla de busqueda -->
 <div class="row">
    <div id="divtabla">
        <div id="divDatos"></div>
    </div>
</div><!--/ tabla de busqueda -->

<!-- <div id="datosViaticosTerminado">
   <div id="tablaViaticosTerminado"></div>
</div> -->

<!-- botones de accion -->
<div class="row pt10">
    <div class="panel panel-default">
        <div class="panel-body" align="center">
            <component-button type="button" id="btnNuevo" class="glyphicon glyphicon-plus"  value="Nuevo"></component-button>
            <span id="divCargarBotones" name="areaBotones"></span><!-- BOTONES SEGUN PERMISOS -->
            <component-button type="button" id="btnCancelar" name="btnCancelar" class="glyphicon glyphicon-trash"  value="Cancelar"></component-button>
        </div>
    </div>
</div><!-- .row -->

<script src="javascripts/radicacion.js?v=<?= rand(); ?>"></script>

<?php require 'includes/footer_Index.inc'; ?>
