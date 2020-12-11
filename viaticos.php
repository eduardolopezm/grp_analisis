<?php
/**
 * Viaticos
 *
 * @category panel
 * @package  ap_grp
 * @author   Luis Aguilar Sandoval
 * @license  [<url>] [name]
 * @version  GIT: <1234>
 * @link     (target, link)
 * Fecha creacion: 29/12/2017
 * Fecha Modificacion: 29/12/2017
 * Panel para la administracion de viaticos
 */

/* DECLARACION DE VARIABLES */
$PageSecurity = 1;
//$PageSecurity = 4;
$PathPrefix = './';
// $funcion = 2318;
$funcion = 2338;

/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
$title= traeNombreFuncion($funcion, $db, 'Panel de Viáticos');
// Declaracion de permisos
$permisoNuevoOficio= Havepermission($_SESSION['UserID'], 2271, $db);

# permisos para ver botones en comrpobación por que la pendeja función fnObtenerBotones_Funcion no sirve bien
$permisoAvanzar = Havepermission($_SESSION['UserID'], 2329, $db);
$permisoRechazar = Havepermission($_SESSION['UserID'], 2328, $db);
$permisoAutorizar = Havepermission($_SESSION['UserID'], 2330, $db);
$permisoCancelar = Havepermission($_SESSION['UserID'], 2337, $db);

# Carga de archivos secundarios
require 'includes/header.inc';
require 'javascripts/libreriasGrid.inc';

$ocultaDepencencia = 'hidden';
?>
<script>
    var nuFuncion = <?= $funcion ?>;
</script>
<script type="text/javascript" src="javascripts/viaticos.js?v=<?= rand();?>"></script>
<div class="row">
   <div id="mensajes"></div>
</div>
<!-- inicio de panel -->
<div class="panel panel-default">
   <div class="panel-body">
      <ul class="nav nav-tabs">
         <li class="active"> <a data-toggle="tab" href="#bandeja" class="bgc10" aria-expanded="true">Bandeja de entrada</a> </li>
         <li> <a id="calendarioPestana" data-toggle="tab" href="#comprobacion" class="bgc10" aria-expanded="false">Comprobación de Viáticos</a> </li>
      </ul><!-- .nav .nav-tabs -->
      <div class="tab-content">
         <div class="tab-pane active" id="bandeja">
            <br>
            <div class="/*row*/" id="contenedorViaticos">
               <div class="panel-group">
                  <div class="panel panel-default">
                     <div class="panel-heading h35" role="tab" id="headingOne">
                        <h4 class="panel-title">
                           <div class="fl text-left">
                              <a role="button" data-toggle="collapse" data-parent="#accordion" href="#Panelviaticos" aria-expanded="true" aria-controls="collapseOne">
                                  <b>Criterios de Filtrado</b>
                              </a>
                           </div>
                        </h4>
                     </div>
                     <!-- .panel-heading -->
                     <!-- <div id="closeTab" class="panel-collapse collapse in"> -->
                     <div id="Panelviaticos" name="Panelviaticos" class="panel-collapse collapse in">
                        <div class="panel-body">
                           <div class="row" id="form-search">
                              <!-- dependencia, UR, UE -->
                              <div class="col-md-4">
                                 <div class="form-inline row <?= $ocultaDepencencia ?>">
                                    <div class="col-md-3">
                                       <span><label>Dependencia: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                       <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial"
                                       onchange="fnCambioDependeciaGeneral('selectRazonSocial', 'selectUnidadNegocio')"></select>
                                    </div>
                                 </div>

                                 <br class="<?= $ocultaDepencencia ?>">

                                 <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                       <span><label>UR: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                       <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio"
                                       onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')"></select>
                                    </div>
                                 </div>
                                 <br>
                                 <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                       <span><label>UE: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                       <select id="selectUnidadEjecutora" name="selectUnidadEjecutora"
                                       class="form-control selectUnidadEjecutora"></select>
                                    </div>
                                 </div>
                              </div><!-- -col-md-4 -->
                              <!-- folio, estatus -->
                              <div class="col-md-4">
                                 <component-text-label label="Oficio No:" id="numeroSolicitud" name="numeroSolicitud" value=""></component-text-label>
                                 <br>
                                 <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                       <span><label>Estatus: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                       <select id="selectEstatus" name="selectEstatus" class="form-control w100p selectEstatus" data-funcion="<?= $funcion ?>"></select>
                                    </div>
                                 </div>
                              </div>
                              <!-- -col-md-4 -->
                              <!-- fechas -->
                              <div class="col-md-4">
                                 <!-- <component-date-label label="Desde:" id="fechaIni" name="fechaIni" placeholder="Desde" title="Desde" value="<?php #echo date('j-m-Y');?>"></component-date-label> -->
                                 <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>Desde: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                       <component-date-feriado2 id="fechaIni" name="fechaIni" class="w100p" placeholder="Fecha inicio" value="<?php echo date('j-m-Y');?>"></component-date-feriado>
                                    </div>
                                </div><!-- .form-inline.row -->
                                 <br>
                                 <!-- <component-date-label label="Hasta:" id="fechaFin" name="fechaFin" placeholder="Hasta" title="Hasta" value="<?php #echo date('j-m-Y');?>"></component-date-label> -->
                                 <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>Hasta: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                       <component-date-feriado2 id="fechaFin" name="fechaFin" class="w100p" placeholder="Fecha inicio" value="<?php echo date('j-m-Y');?>"></component-date-feriado>
                                    </div>
                                </div><!-- .form-inline.row -->

                              </div>
                              <!-- -col-md-4 -->
                           </div>
                           <div class="row">
                              <component-button type="submit" id="btnSearch" name="btnSearch" class="glyphicon glyphicon-search" value="Filtrar"></component-button>
                           </div>
                        </div>
                        <!-- .panel-body -->
                     </div>
                     <!-- .panel-collapse -->
                  </div>
                  <!-- .panel -->
                  <!-- <div name="tablaViaticos" id="tablaViaticos">
                     <div name="datosViaticos" id="datosViaticos"> </div>
                  </div> -->
               </div><!-- .panel-group -->
            </div> <!-- .row -->
            <div class="/*row*/">
               <div id="datosViaticos">
                  <div id="tablaViaticos"></div>
               </div>
            </div><!-- .row -->
            <div class="row pt10" id="botonera">
               <?php
                  if ($permisoNuevoOficio==1) {
                     echo '<component-button type="submit" id="btnNuevoficio" name="btnNuevoficio" class="glyphicon glyphicon-copy" onclick="return false;" value="Nuevo"></component-button>';
                  }
               ?>
               <span id="areaBotones" name="areaBotones"></span><!-- BOTONES SEGUN PERMISOS -->
            </div><!-- .row -->
         </div><!-- .tab-pane # bandeja -->

         <div class="tab-pane" id="comprobacion">
            <br>
            <div class="/*row*/" id="contenedorViaticos">
               <div class="panel-group">
                  <div class="panel panel-default">
                     <div class="panel-heading h35" role="tab" id="headingOne">
                        <h4 class="panel-title">
                           <div class="fl text-left">
                              <a role="button" data-toggle="collapse" data-parent="#accordion" href="#Panelviaticos" aria-expanded="true" aria-controls="collapseOne">
                                  <b>Criterios de Filtrado</b>
                              </a>
                           </div>
                        </h4>
                     </div>
                     <!-- .panel-heading -->
                     <!-- <div id="closeTab" class="panel-collapse collapse in"> -->
                     <div id="Panelviaticos" name="Panelviaticos" class="panel-collapse collapse in">
                        <div class="panel-body">
                           <div class="row" id="form-search-terminadas">
                              <!-- dependencia, UR, UE -->
                              <div class="col-md-4">
                                 <div class="form-inline row <?= $ocultaDepencencia ?>">
                                    <div class="col-md-3">
                                       <span><label>Dependencia: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                       <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial"
                                       onchange="fnCambioDependeciaGeneral('selectRazonSocial', 'selectUnidadNegocio')"></select>
                                    </div>
                                 </div>

                                 <br class="<?= $ocultaDepencencia ?>">

                                 <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                       <span><label>UR: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                       <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio"
                                       onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')"></select>
                                    </div>
                                 </div>
                                 <br>
                                 <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                       <span><label>UE: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                       <select id="selectUnidadEjecutora" name="selectUnidadEjecutora"
                                       class="form-control selectUnidadEjecutora"></select>
                                    </div>
                                 </div>
                              </div><!-- -col-md-4 -->
                              <!-- folio, estatus -->
                              <div class="col-md-4">
                                 <component-number-label label="Oficio No:" id="numeroSolicitud" name="numeroSolicitud" value=""></component-number-label>
                                 <br>
                                 <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                       <span><label>Estatus: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                       <select id="selectEstatusTerminadas" name="selectEstatusTerminadas" class="form-control w100p selectEstatusTerminadas" data-funcion="<?= $funcion ?>"></select>
                                    </div>
                                 </div>
                              </div>
                              <!-- -col-md-4 -->
                              <!-- fechas -->
                              <div class="col-md-4">
                                 <!-- <component-date-label label="Desde:" id="fechaIni" name="fechaIni" placeholder="Desde" title="Desde" value="<?php #echo date('j-m-Y');?>"></component-date-label> -->
                                 <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>Desde: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                       <component-date-feriado2 id="fechaIni" name="fechaIni" class="w100p" placeholder="Fecha inicio" value="<?php echo date('j-m-Y');?>"></component-date-feriado>
                                    </div>
                                </div><!-- .form-inline.row -->
                                 <br>
                                 <!-- <component-date-label label="Hasta:" id="fechaFin" name="fechaFin" placeholder="Hasta" title="Hasta" value="<?php #echo date('j-m-Y');?>"></component-date-label> -->
                                 <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>Hasta: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                       <component-date-feriado2 id="fechaFin" name="fechaFin" class="w100p" placeholder="Fecha inicio" value="<?php echo date('j-m-Y');?>"></component-date-feriado>
                                    </div>
                                </div><!-- .form-inline.row -->

                              </div>
                              <!-- -col-md-4 -->
                           </div>
                           <div class="row">
                              <component-button type="submit" id="btnSearchTerminadas" name="btnSearchTerminadas" class="glyphicon glyphicon-search" value="Filtrar"></component-button>
                           </div>
                        </div>
                        <!-- .panel-body -->
                     </div>
                     <!-- .panel-collapse -->
                  </div>
                  <!-- .panel -->
                  <!-- <div name="tablaViaticos" id="tablaViaticos">
                     <div name="datosViaticos" id="datosViaticos"> </div>
                  </div> -->
               </div><!-- .panel-group -->
            </div> <!-- .row -->
            <!-- <br> -->
            <div id="datosViaticosTerminado">
               <div id="tablaViaticosTerminado"></div>
            </div>
            <!-- botones de accion para comprobacion -->
            <div class="row pt10">
               <!-- <span id="areaBotonesTerminada" name="areaBotonesTerminada"></span> -->
               <?php
                  # botón de rechazar
                  if($permisoRechazar!=0){
                     echo '<button type="button" id="rechazarTerminada" name="rechazarTerminada" class="btn btn-default botonVerde glyphicon glyphicon-floppy-remove">&nbsp;Rechazar</button>';
                  }
                  # botón de avanzar
                  if($permisoAvanzar!=0){
                     echo '<button type="button" id="avanzarTerminada" name="avanzarTerminada" class="btn btn-default botonVerde glyphicon glyphicon-forward">&nbsp;Avanzar</button>';
                  }
                  # botón de autorizar
                  if($permisoAutorizar!=0){
                     echo '<button type="button" id="autorizarTerminada" name="autorizarTerminada" class="btn btn-default botonVerde glyphicon glyphicon-flag">&nbsp;Autorizar</button>';
                  }
                  # botón de Cancelar
                  if($permisoCancelar!=0){
                     echo '<button type="button" id="cancelarTerminada" name="cancelarTerminada" class="btn btn-default botonVerde glyphicon glyphicon-trash">&nbsp;Cancelar</button>';
                  }
               ?>
            </div><!-- .row -->
         </div><!-- .tab-pane #comprobacion -->
      </div><!-- .tab-content -->
   </div>
</div>

<?php require 'includes/footer_Index.inc'; ?>
