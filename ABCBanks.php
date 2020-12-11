<?php
/**
 * Viaticos
 *
 * @category panel
 * @package  ap_grp
 * @author   Japheth Calzada López
 * @license  [<url>] [name]
 * @version  GIT: <1234>
 * @link     (target, link)
 * Fecha creacion: 14/08/2018
 * Fecha Modificacion: 14/08/2018
 * ABC Bancos
 * @file: ANCBanks.php
 */

/* DECLARACION DE VARIABLES */
$PageSecurity = 1;
//$PageSecurity = 4;
$PathPrefix = './';
$funcion = 1304;

/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
$title= traeNombreFuncion($funcion, $db, 'Altas, Bajas y Modificaciones de Bancos');
require 'includes/header.inc';
require 'javascripts/libreriasGrid.inc';

$ocultaDepencencia = 'hidden';
?>
<link rel="stylesheet" href="css/listabusqueda.css" /><!-- Estilos para el auto complete -->
<script src="javascripts/ABCBanks.js?v=<?= rand();?>"></script>
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
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>Banco: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                            <select id="banco" name="banco[]" class="form-control banco" multiple="multiple">
                                               
                                            </select>
                                        </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>Nombre: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="descripcion" name="descripcion[]" class="form-control descripcion" multiple="multiple">
                                        </select>
                                    </div>
                                </div>
                            </div><!-- .col-md-4 -->
							<div class="col-md-4">
                                <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>Clave: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="clave" name="clave[]" class="form-control clave" multiple="multiple">
                                        </select>
                                    </div>
                                </div><!-- .form-inline .row -->

                                <br>
                            </div>
                            <div class="row"></div>
                            <div align="center">
                                <br>
                                <component-button type="button" id="btnBusqueda" name="btnBusqueda" class="glyphicon glyphicon-search" value="Buscar"></component-button>
                            </div>
                        </div> <!-- .row -->
                        <br>
                        <div name="divTabla" id="divTabla">
                            <div name="divContenidoTabla" id="divContenidoTabla"></div>
                        </div> 
                        <br>
                        <div class="panel panel-default">
                            <div class="panel-body" align="center" id="divBotones" name="divBotones">
                            <!-- <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="index.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a> -->
                                <a id="linkNuevo" name="linkNuevo" href="ABCBanks_V.php" class="btn btn-default botonVerde glyphicon glyphicon-plus"> Nuevo</a>
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
