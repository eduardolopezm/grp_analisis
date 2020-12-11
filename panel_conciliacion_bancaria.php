<?php

$PageSecurity = 5;
$funcion = 2200;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db,"Panel de Conciliación Bancaria");
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

$permiso = Havepermission($_SESSION ['UserID'], 2200, $db);

//Librerias GRID
include('javascripts/libreriasGrid.inc');

?>

    <link rel="stylesheet" href="css/listabusqueda.css" />

    <div align="left">
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
                                <span><label>UR: </label></span>
                            </div>
                            <div class="col-md-9">
                                <select id="selectUnidadNegocio" name="selectUnidadNegocio[]" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora');"  multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="form-inline row">
                            <div class="col-md-3">
                                <span><label>UE: </label></span>
                            </div>
                            <div class="col-md-9">
                                <select id="selectUnidadEjecutora" name="selectUnidadEjecutora[]" class="form-control selectUnidadEjecutora" multiple="multiple">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <component-number-label label="Folio:" id="txtFolio" name="txtFolio" placeholder="Folio" title="Folio" value=""></component-number-label>
                        <br>
                        <div class="form-inline row">
                            <div class="col-md-3">
                                <span><label>MES: </label></span>
                            </div>
                            <div class="col-md-9">
                                <select id="selectMonths" name="selectMonths[]" class="form-control selectMonths selectMeses" multiple="multiple">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <component-date-label label="Desde: " id="txtFechaDesde" name="txtFechaDesde" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
                        <br>
                        <component-date-label label="Hasta: " id="txtFechaHasta" name="txtFechaHasta" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
                        <br>
                    </div>
                    <div class="row"></div>
                    <div align="center">
                        <br>
                        <a id="btnNuevo" name="btnNuevo" href="captura_conciliacion_bancaria.php" class="btn btn-default botonVerde glyphicon glyphicon-copy"> Nuevo</a>
                        <component-button type="button" id="btnBusqueda" name="btnBusqueda" class="glyphicon glyphicon-search" onclick="loadRegister();" value="Filtrar"></component-button>
                    </div>
                </div>
            </div>
        </div>

        <div name="divTabla" id="divTabla">
            <div name="divContenidoTabla" id="divContenidoTabla"></div>
        </div

    </div>

    <script type="text/javascript" src="javascripts/panel_conciliacion_bancaria.js?<?php echo rand(); ?>"></script>

<?php
include 'includes/footer_Index.inc';