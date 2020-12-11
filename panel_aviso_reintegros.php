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
 * Vista para Panel de Avisos de Reintegros
*/

$PageSecurity = 5;
$funcion = 2412;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db,"Panel de Avisos de Reintegros");
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

$permiso = Havepermission($_SESSION ['UserID'], 2412, $db);

//Librerias GRID
include('javascripts/libreriasGrid.inc');


$updt = 0;
$transNO = 0;
$typeData = 0;
$urlGeneral = "&transno=>" . $transNO . "&type=>" . $typeData . "&upd=>" . $updt;
$enc = new Encryption;
$url = $enc->encode($urlGeneral);
$liga= "URL=" . $url;

?>

<link rel="stylesheet" href="css/listabusqueda.css" />

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
                  <!-- <div class="form-inline row" style="display: none;">
                       <div class="col-md-3">
                           <span><label>Dependencia: </label></span>
                       </div>
                       <div class="col-md-9">
                           <select id="selectRazonSocial" name="selectRazonSocial[]" class="form-control selectRazonSocial" onchange="fnCambioRazonSocial()" multiple="multiple">
                           </select>
                       </div>
                   </div> -->
                   <div class="form-inline row">
                       <div class="col-md-3">
                           <span><label>UR: </label></span>
                       </div>
                       <div class="col-md-9">
                           <select id="selectUnidadNegocio" name="selectUnidadNegocio[]" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora');" multiple="multiple">
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
                   <br>
                   <div class="form-inline row">
                       <div class="col-md-3">
                           <span><label>Estatus: </label></span>
                       </div>
                       <div class="col-md-9">
                           <select id="selectEstatusReintegro" name="selectEstatusReintegro[]" class="form-control selectEstatusReintegro" multiple="multiple">
                           </select>
                       </div>
                   </div>

               </div>


               <div class="col-md-4">
                   <component-number-label label="Folio:" id="txtFolioReintegro" name="txtFolioReintegro" placeholder="Folio" title="Folio" value=""></component-number-label>
                   <br>
                   <div class="form-inline row">
                       <div class="col-md-3">
                           <span><label>Tipo de Reintegro: </label></span>
                       </div>
                       <div class="col-md-9">
                           <select id="selectTipoReintegro" name="selectTipoReintegro[]" class="form-control selectTipoReintegro" multiple="multiple">
                           </select>
                       </div>
                   </div>
                   <br>
                   <div class="form-inline row">
                       <div class="col-md-3">
                           <span><label>Tipo de Operacion: </label></span>
                       </div>
                       <div class="col-md-9">
                           <select id="selectOperacionTesoreria" name="selectOperacionTesoreria[]" class="form-control selectOperacionTesoreria" multiple="multiple">
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
                   <component-button type="button" id="btnBusqueda" name="btnBusqueda" class="glyphicon glyphicon-search" onclick="viewResultSearch();" value="Filtrar"></component-button>
               </div>
           </div>
        </div>
    </div>

    <div name="divTabla" id="divTabla">
        <div name="divContenidoTabla" id="divContenidoTabla"></div>
    </div

    <br>
    <div class="panel panel-default">
        <div class="panel-body" align="center" id="divBotones" name="divBotones">
            <a id="btnNuevo" name="btnNuevo" href="captura_aviso_reintegros.php?<?php echo $liga?>" class="btn btn-default botonVerde glyphicon glyphicon-copy"> Nuevo</a>
        </div>
    </div>
</div>

<script type="text/javascript" src="javascripts/panel_aviso_reintegros.js"></script>

<?php
include 'includes/footer_Index.inc';