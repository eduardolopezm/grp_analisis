<?php
/**
 * Estructura Programatica
 *
 * @category panel
 * @package  ap_grp
 * @author   Pedro Paramo
 * @version  GIT: <28faa925cd57f3489955b2480a293bca345e1417>
 * @file     abcRelacionPpPartida.php
 * Fecha creacion: 29/12/2017
 * Fecha Modificacion: 29/12/2017
 * Panel para la administracion de viaticos
 */

/* DECLARACION DE VARIABLES */
$PageSecurity = 1;
$PathPrefix = './';
// $funcion = 2338;
$funcion = 2355;

/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
$title=traeNombreFuncion($funcion, $db);

# Carga de archivos secundarios
require 'includes/header.inc';
require 'javascripts/libreriasGrid.inc';

# función de ocultado de dependencia
$ocultaDepencencia = 'hidden';
?>
<script type="text/javascript" src="javascripts/abcRelacionPpPartida.js?v=<?= rand();?>"></script>
<!-- tabla de busqueda -->
<div class="row">
    <div id="contenedorTabla">
        <div id="tablaGrid"></div>
    </div>
</div><!-- .row -->

<!-- botones de accion -->
<div class="row pt10">
    <div class="panel panel-default">
        <div class="panel-body" align="center">
            <component-button type="button" id="nuevo" class="glyphicon glyphicon-copy"  value="Nuevo"></component-button>
        </div>
    </div>
</div><!-- .row -->

<!-- Modal -->
<div class="modal fade ui-draggable" id="modalRelacionPpPartida" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="navbar navbar-inverse navbar-static-top">
                <div class="col-md-12 menu-usuario">
                    <span data-dismiss="modal" class="glyphicon glyphicon-remove"></span>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <div class="nav navbar-nav">
                        <div class="title-header">
                            <div id="tituloModal"></div>
                        </div>
                    </div>
                </div>
                <div class="linea-verde"></div>
            </div>
            <div class="modal-body" id="forma">
                <div class="row">
                    <div class="col-md-12">

                        <!-- pp -->
                        <div class="form-inline row">
                            <div class="col-md-3" style="vertical-align: middle;"> <span><label>PP: </label></span> </div>
                            <div class="col-md-9">
                                <select id="pp" name="pp" class="form-control selectProgramaPresupuestario" data-todos="true">
                                    <option value="0">Seleccione una opción</option>
                                </select>
                            </div>
                        </div>

                        <br>

                        <!-- partida -->
                        <div class="form-inline row">
                            <div class="col-md-3" style="vertical-align: middle;"> <span><label>Partida: </label></span> </div>
                            <div class="col-md-9">
                                <select id="partida" name="partida" class="form-control selectPartidaEspesifica" data-todos="true">
                                    <option value="0">Seleccione una opción</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <span>
                    <button type="button" class="btn botonVerde" data-dismiss="modal" id="guardar">Guardar</button>
                    <button type="button" class="btn botonVerde" data-dismiss="modal">Cancelar</button>
                </span>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer_Index.inc'; ?>
