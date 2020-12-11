<?php

/* $Revision: 1.13 $ */

$PageSecurity=15;
$PathPrefix = './';
$funcion = 1999;

include($PathPrefix . 'config.php');
include ('includes/session.inc');
//$title = _('Generación de Poliza de Cierre Anual');
$title= traeNombreFuncion($funcion, $db);
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');
$ocultaDepencencia = 'hidden';


//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//!!                                               !!
//!!             Version de la página:             !!
//!!           Z_YearlyClosureProcess.php          !!
//!!                                               !!
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

?>
<script type="text/javascript" src="javascripts/layout_general.js"></script>
<div class="row">
    <div class="col-sm-6 col-sm-offset-3" id="mensaje"></div>
</div>
<div class="row" style="margin-bottom:26%;">
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
                                </div>
                            </div>
                            <br>
                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>UE: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" ></select>
                                </div>
                            </div>
                        </div><!-- -col-md-4 -->

                        <div class="col-md-4">
                        	<div class="form-inline row ">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>Año a Cerrar: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectFromPeriod" name="selectFromPeriod" class="form-control selectFromPeriod"></select>
                                </div>
                            </div>
                            <br>
                            <component-text-label label="Folio:" id="txtFolio" name="txtFolio" placeholder="Folio"title="Folio" class=" hide"></component-text-label> 
                        </div>
                        <div class="col-md-4">
                        </div>

                    </div>
                    <div class="row">
                        <br>
                        <component-button type="button" id="btnGenerarPiliza" class="glyphicon glyphicon-paste"  value="Genera Poliza de Cierre"></component-button>
                    </div>
                </div><!-- .panel-body -->
            </div><!-- .panel-collapse -->
        </div><!-- .panel -->
    </div><!-- .panel-group -->
</div><!-- .row -->

<script src="javascripts/cierreContableAnual.js?v=<?= rand(); ?>"></script>


<?php require 'includes/footer_Index.inc'; ?>