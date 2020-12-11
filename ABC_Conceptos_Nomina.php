<?php

/**
 * Conceptos
 *
 * @category panel
 * @package  ap_grp
 * @author   Japheth Calzada López
 * @license  [<url>] [name]
 * @version  GIT: <1234>
 * @link     (target, link)
 * Fecha creacion: 07/08/2019
 * Fecha Modificacion: 07/08/2019
 * Panel para Conceptos de Nomina
 * @file: ABC_Conceptos_Nomina.php
 */

/* DECLARACION DE VARIABLES */
$PageSecurity = 1;
//$PageSecurity = 4;
$PathPrefix = './';
$funcion = 3061;
//error_reporting(E_ALL);
//ini_set('display_errors', 1);    
/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
$title= traeNombreFuncion($funcion, $db, 'Conceptos de Nómina');
require 'includes/header.inc';

require 'javascripts/libreriasGrid.inc';
# comprobación de modificación o generación
$folio = empty($_GET['folio'])? '' : $_GET['folio'];
$estatusAnexo = empty($_GET['estatusAnexo'])? '' : $_GET['estatusAnexo'];
$titulo = !empty($folio)? "<b>ABC_Conceptos_Nomina</b>" : "<b>ABC_Conceptos_Nomina</b>"; 
$ocultaDepencencia = 'hidden';

?>
<link rel="stylesheet" href="css/listabusqueda.css" /><!-- Estilos para el auto complete -->
<script src="javascripts/ABC_Conceptos_Nomina.js?v=<?= rand();?>"></script>
<script>
    window.folio = "<?= $folio; ?>";
    var idFolio = "";
    <?php
        if(isset($_GET['idFolio']) && !empty($_GET['idFolio'])): ?>
         idFolio= <?=$_GET['idFolio']?>;
    <?php endif;?>
</script>
<div id="form-add">

<!-- datos generales -->
<div class="row">
       <div class="col-md-12">
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
                                    <!-- dependencia, UR, UE, folio -->
                                    <div class="col-md-4">
                                        <div class="form-inline row <?= $ocultaDepencencia ?>">
                                            <div class="col-md-3">
                                                <span><label>Dependencia: </label></span>
                                            </div>
                                            <div class="col-md-9">
                                            </div>
                                        </div><!-- .form-inline .row -->

                                        <br class="<?= $ocultaDepencencia ?>">
    
                                        <div class="form-inline row">
                                            <div class="col-md-3" style="vertical-align: middle;">
                                                <span><label>PP: </label></span>
                                            </div>
                                            <div class="col-md-9">
                                                <select id="pp" name="pp[]" class="form-control pp" multiple="multiple">
                                                </select>
                                            </div>
                                        </div><!-- .form-inline .row -->

                                        <br>
                                    </div>

                                    <!-- Tipo Comisión -->
                                    <div class="col-md-4">
                                        <div class="form-inline row">
                                            <div class="col-md-3" style="vertical-align: middle;">
                                                <span><label>Partida: </label></span>
                                            </div>
                                            <div class="col-md-9">
                                                    <select id="partida" name="partida[]" class="form-control partida" multiple="multiple">
                                                    
                                                    </select>
                                                </div>
                                        </div>
                                    </div>
                                    <!--Zona Economica -->
                                    <div class="col-md-4">
                                        <div class="form-inline row">
                                            <div class="col-md-3" style="vertical-align: middle;">
                                                <span><label>Concepto: </label></span>
                                            </div>
                                            <div class="col-md-9">
                                                <select id="concepto" name="concepto[]" class="form-control concepto" multiple="multiple">
                                                </select>
                                            </div>
                                        </div>
                                    </div><!-- .col-md-4 -->
                                    <div class="row"></div>
                                    <div align="center">
                                        <br>
                                        <component-button type="button" id="btnBusqueda" name="btnBusqueda" class="btn btn-primary glyphicon glyphicon-search" value="Filtrar"></component-button>
                                    </div>
                                </div> <!-- .row -->
                                <div name="divTabla" id="divTabla">
                                    <div name="divContenidoTabla" id="divContenidoTabla"></div>
                                </div> 
                                <br>
                                <div class="panel panel-default">
                                    <div class="panel-body" align="center" id="divBotones" name="divBotones">
                                    <!-- <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="index.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a> -->
                                        <a id="linkNuevo" name="linkNuevo" href="ABC_Conceptos_Nomina_V.php" class="btn btn-success glyphicon glyphicon-plus"> Nuevo</a>
                                    </div>
                                </div>
                            </div><!-- #formGenerales -->
                        </div><!-- .panel-body -->
                    </div>
                </div><!-- .panel .panel-default -->
            </div><!-- .panel-group -->
      </div>
</div><!-- .row -->


<?php
require 'includes/footer_Index.inc'; ?>
 
