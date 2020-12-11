<?php

/*
 * @category Panel
 * @package ap_grp
 * @author Jose Raul Lopez Vazquez <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 23/07/2018
 * Fecha Modificación: 23/07/2018
 * Vista para Captura de Avisos de Reintegros
*/

$PageSecurity = 5;
$funcion = 2412;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db, "Avisos de Reintegro");
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

$permiso = Havepermission($_SESSION ['UserID'], 2412, $db);

if (isset($_GET['transno'])) {
    $transnoRefund = $_GET['transno'];
}

if (isset($_GET['type'])) {
    $typeRefund = $_GET['type'];
}

if(isset($_GET['upd'])){
    $updt = $_GET['upd'];
}

if(isset($_GET['typeUser'])){
    $loginUser = $_GET['typeUser'];
}


/*echo "\n ".$transnoRefund;
echo "\n ".$typeRefund;
echo "\n ".$loginUser;
echo "\n ".$updt;
echo "\n\n";

exit();*/

//Librerias GRID
include('javascripts/libreriasGrid.inc');

?>

    <script type="text/javascript">
        var transnoRef = <?php echo $transnoRefund; ?>;
        var typeRef = <?php echo $typeRefund; ?>;
        var upID = <?php echo $updt; ?>;
        var luser = <?php echo "'".$loginUser."'"; ?>;
    </script>

    <svg xmlns="http://www.w3.org/2000/svg" style="display: none">
        <symbol id="checkmark" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-miterlimit="10" fill="none"  d="M22.9 3.7l-15.2 16.6-6.6-7.1">
            </path>
        </symbol>
    </svg>


    <link rel="stylesheet" href="css/listabusqueda.css" />
    <!--
    <link rel="stylesheet" type="text/css" href="css/loadings.css">
    <div id="load"></div>
     <link rel="stylesheet" href="css/check.css" />
        -->


    <div aling="left">

        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingOne">
                <h4 class="panel-title row">
                    <div class="col-md-1 col-xs-1">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelEncabezado" aria-expanded="true" aria-controls="collapseOne">
                            Encabezado
                        </a>
                    </div>

                    <div class="col-md-5 col-xs-5 pull-right" id="folio_reintegro" style="display: none;">
                       <div class="col-md-9 text-right" style="color: #7F8C8D;"> Número de Folio: </div>
                        <div class="col-md-3 pull-right text-center" id="numero_de_reintegro" style="color: #7F8C8D;font-weight: 500;"></div>
                    </div>
                </h4>
            </div>
            <div id="PanelEncabezado" name="PanelEncabezado" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">
                    <div class="col-md-4">
                        <div class="form-inline row">
                            <div class="col-md-3">
                                <span><label>UR: </label></span>
                            </div>
                            <div class="col-md-9">
                                <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" style="width: 100% !important;">
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="form-inline row">
                            <div class="col-md-3">
                                <span><label>UE: </label></span>
                            </div>
                            <div class="col-md-9">
                                <select id="selectUnidadEjecutora" name="selectUnidadEjecutora[]" class="form-control selectUnidadEjecutora">
                                    <option value='-1' selected> Sin selección </option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div id="lineTESOFE" style="display: none;">
                            <br>
                            <component-text-label label="Línea de captura TESOFE:" id="txtLineTesofe" name="txtLineTesofe" placeholder="Línea de captura TESOFE" title="LCT" value="" maxlength="20" ></component-text-label>
                            <br>
                        </div>
                        <div id="CodigoRastreo" style="display: none;">
                            <component-text-label label="Codigo o Clave de Rastreo:" id="txtCodigoClaveRastreo" name="txtCodigoClaveRastreo" placeholder="Codigo o Clave de Rastreo" title="CDG" value="" ></component-text-label>
                        </div>
                        <div id="txtSIAFF" style="display: none">
                            <br>
                            <component-number-label label="Proceso SIAFF:" id="txtProcesoSIAFF" name="txtProcesoSIAFF" placeholder="Proceso SIAFF" title="SIAFF" value="" ></component-number-label>
                            <br>
                        </div>

                        <div id="NumTransfer" style="display: none;">
                            <br>
                            <component-number-label label="Num. de Transferencia:" id="txtNumeroTransf" name="txtNumeroTransf" placeholder="Num. de Transferencia" title="NTRANSF" value="" maxlength="40"></component-number-label>
                            <br>
                        </div>
                    </div>

                    <!-- Segunda Columna -->
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-inline row">
                                    <div class="col-md-3">
                                        <span><label>Tipo de Reintegro: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="selectTipoReintegro" name="selectTipoReintegro[]" class="form-control selectTipoReintegro" onchange="changedisabletypePayment(this);">
                                            <option value='-1' selected>Sin selección</option>
                                        </select>
                                    </div>
                                </div>
                                <br>
                                <div class="form-inline row">
                                    <div class="col-md-3">
                                        <span><label>Tipo de Operacion: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="selectOperacionTesoreria" name="selectOperacionTesoreria[]" class="form-control selectOperacionTesoreria" onchange="resettable();">
                                            <option value='-1' selected>Sin selección</option>
                                        </select>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="">Folio Ministrado / Radicado / Pago</label>
                                    </div>

                                    <div class="col-md-8" style="display: block" id="textONE">
                                        <div class="input-group">
                                            <input type="text" class="form-control validanumericos" name="txtFolioTransf" id="txtFolioTransf" placeholder="Folio o Transferencia" value="">
                                            <span class="input-group-btn">
                                <button class="btn" style="background-color: #1B693F !important;" type="button" id="btnBusqueda" name="btnBusqueda" onclick="searchFolioorTransf();"><i class="glyphicon glyphicon-search" style="color: #ffffff"></i></button>
                              </span>
                                        </div>
                                    </div>

                                    <div class="col-md-8" style="display: none" id="texttwo">
                                        <div class="input-group">
                                            <input type="text" class="form-control validanumericos" name="txtFolioTransfADD" id="txtFolioTransfADD" placeholder="Folio o Transferencia" value="">
                                            <span class="input-group-btn">
                                           <button class="btn" style="background-color: #1B693F !important;" type="button" id="btnBusquedaADD" name="btnBusquedaADD" onclick="searchFolioorTransfADD();"><i class="glyphicon glyphicon-search" style="color: #ffffff"></i></button>
                                       </span>
                                        </div>
                                    </div>
                                </div>
                                <br>
                            </div>
                            <div class="col-md-6">
                                <component-date-label label="Fecha de Expedicion: " id="txtFechaExp" name="txtFechaExp" value="<?php echo date('d-m-Y'); ?>" readonly></component-date-label>
                                <br>
                                <component-date-label label="Fecha de Autorizacion: " id="txtFechaAut" name="txtFechaAut" value="" readonly></component-date-label>
                                <br>
                            </div>
                        </div>
                        <br>
                        <div class="row">

                            <div class="col-md-2" id="labelDescription">
                                <label for="txtJustificacion">Justificación</label>
                            </div>
                            <div class="col-md-9">
                                <textarea id="txtJustificacion"  name="txtJustificacion" class="form-control" placeholder="Justificación" title="Justificación" rows="6"></textarea>
                            </div>

                        </div>
                        <br>
                        <div class="row" style="display: none">
                            <input type="text" name="ttalGeneral" id="ttalGeneral" value="" readonly>
                        </div>
                    </div>
                    <!-- Segunda Columna -->
                </div>
            </div>
        </div>
        <br>
        <!-- <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingOne">
                <h4 class="panel-title row">
                    <div class="col-md-3 col-xs-3">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
                            <b>CLAVES PRESUPUESTALES</b>
                        </a>
                    </div>
                </h4>
            </div>

            <table class="table table-bordered" name="tablaReducciones" id="tablaReducciones">
                <tbody></tbody>
            </table>
        </div> -->

        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingOne">
                <h4 class="panel-title row">
                    <div class="col-md-1 col-xs-1">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelReducciones" aria-expanded="true" aria-controls="collapseOne">
                            Selección
                        </a>
                    </div>
                    <div class="col-md-8 col-xs-8">
                    </div>
                    <div class="col-md-3 col-xs-3">
                        <div class="input-group">
                            <span class="input-group-addon" style="background: none; border: none; font-size: 20px; text-align: right;">Total $ </span>
                            <span class="input-group-addon" style="background: none; border: none; font-size: 20px; text-align: left;" id="txtTotalReducciones" name="txtTotalReducciones"></span>
                        </div>
                    </div>
                </h4>
            </div>
            <div id="PanelReducciones" name="PanelReducciones" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" style="overflow-x:scroll;overflow-y:scroll;">
                <!-- <div class="panel-body" style="overflow-x:scroll;overflow-y:scroll;"> -->
                <div id="divReducciones" name="divReducciones"></div>
                <div id="divMensajeOperacionReducciones" name="divMensajeOperacionReducciones"></div>
                <table class="table table-bordered" name="tablaReducciones" id="tablaReducciones">
                    <tbody></tbody>
                </table>
                <!-- </div> -->
            </div>
            <table class="table table-bordered" name="tablaReduccioneTotales" id="tablaReduccioneTotales">
                <tbody></tbody>
            </table>
        </div>

        <div class="panel panel-default">
            <div class="panel-body" align="center" id="divBotones" name="divBotones">
                <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="panel_aviso_reintegros.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="javascripts/captura_aviso_reintegros.js?<?php echo rand(); ?>"></script>

<?php
include 'includes/footer_Index.inc';