<?php
/**
 * 
 *
 * @category panel
 * @package  ap_grp
 * @author   Japheth Calzada López
 * @license  [<url>] [name]
 * @version  GIT: <1234>
 * @link     (target, link)
 * Fecha creacion: 16/07/2018
 * Fecha Modificacion: 16/07/2018
 * Panel para Clasificación firmas
 * @file: configuracionFirmas.php
 */

/* DECLARACION DE VARIABLES */
$PageSecurity = 1;
//$PageSecurity = 4;
$PathPrefix = './';
// $funcion = 1345;2404
$funcion = 2404;

/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
$title= traeNombreFuncion($funcion, $db, 'Configuración Firmas');
require 'includes/header.inc';
header ("Cache-Control: no-cache, must-revalidate"); 
require 'javascripts/libreriasGrid.inc';
# comprobación de modificación o generación
$folio = empty($_GET['folio'])? '' : $_GET['folio'];
$estatusAnexo = empty($_GET['estatusAnexo'])? '' : $_GET['estatusAnexo'];
$ocultaDepencencia = 'hidden';
?>
<link rel="stylesheet" href="css/listabusqueda.css" /><!-- Estilos para el auto complete -->
<script src="javascripts/configuracionFirmas.js?v=<?= rand();?>"></script>
<script type="text/javascript" src="javascripts/imprimirreportesconac.js?v=<?= rand();?>"></script>
<script>
    window.folio = "<?= $folio; ?>";
    var idFolio = "";
    <?if(isset($_GET['idFolio']) && !empty($_GET['idFolio'])):?>
      idFolio= <?=$_GET['idFolio']?>;
    <?php endif;?>
</script>
<div id="form-add">

<!-- datos generales -->
<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading h35">
                <h4 class="panel-title">
                    <div class="fl text-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#datosGenerales" aria-expanded="true" aria-controls="collapseOne">
                            <strong>Criterios de filtrado</strong>
                        </a>
                    </div>
                </h4>
            </div>
            <div id="datosGenerales" name="datosGenerales" class="panel-collapse collapse in">
                <div class="panel-body">
                    <div id="formGenerales">
                        <div class="form-inline row">
                            <div class="col-md-4">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>UR: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectUnidadNegocio" name="selectUnidadNegocio" data-todos="true" class="form-control selectUnidadNegocio"
                                    onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')"></select>
                                </div>
                            </div>
                            <div class="col-md-4">
                            <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>UE: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="selectUnidadEjecutora" name="selectUnidadEjecutora[]" multiple="multiple"  data-todos="true" class="form-control selectUnidadEjecutora"></select>
                                    </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-inline row">
                                    <div class="col-md-3">
                                            <span><label>Reporte: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                            <select id="selectReportes" name="selectReportes" class="form-control selectReportesFirmas" >
                                            </select>
                                    </div>
                                </div>
                            </div><!-- .col-md-4 -->
                            <div class="row"></div>
                            <div align="center">
                                <br>
                                <component-button type="button" id="btnBusqueda" name="btnBusqueda" class="glyphicon glyphicon-search" value="Filtrar"></component-button>
                            </div>
                        </div> <!-- .row -->
                        <div name="divTabla" id="divTabla">
                            <div name="divContenidoTabla" id="divContenidoTabla"></div>
                        </div> 
                        <br>
                        <div class="panel panel-default">
                            <div class="panel-body" align="center" id="divBotones" name="divBotones">
                            <!-- <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="index.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a> -->
                                <a id="linkNuevo" name="linkNuevo" href="configuracionFirmas_V.php" class="btn btn-default botonVerde glyphicon glyphicon-plus"> Nuevo</a>
                            </div>
                        </div>
                    </div><!-- #formGenerales -->
                </div><!-- .panel-body -->
            </div>
        </div><!-- .panel .panel-default -->
    </div><!-- .panel-group -->
</div><!-- .row -->
</div>

<?php require 'includes/footer_Index.inc'; ?>
