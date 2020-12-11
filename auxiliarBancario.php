<?php
/*
 Reporte Auxiliar Bancos.
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link /auxiliarBancario.php
 * Fecha Creación: 11.06.17
 * Se genera el presente programa para la visualización de la información
 * del reporte del auxiliar de bancos.
*/

/* DECLARACION DE VARIABLES */
$PageSecurity = 1;
$PathPrefix = './';
$funcion = 2436;

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

?>

<link rel="stylesheet" href="css/listabusqueda.css" />

<div align="left">
    <div  class="panel panel-default">
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
              <div  class="panel-body">
                  <form id="idElementosFiltros">
                     <div class="col-md-4">
                         <div class="form-inline row">
                             <div class="col-md-3" style="vertical-align: middle;">
                                 <span><label>UR: </label></span>
                             </div>
                             <div class="col-md-9">
                                 <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" data-todos="true"  onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora')" multiple></select>
                             </div>
                         </div>
                         <br>
                         <div class="form-inline row">
                             <div class="col-md-3" style="vertical-align: middle;">
                                 <span><label>UE: </label></span>
                             </div>
                             <div class="col-md-9">
                                 <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" multiple>
                                    <!-- <option value='-1' selected>Sin selección</option> -->
                                 </select>
                             </div>
                         </div>
                         <br>
                         <div class="form-inline row">
                             <div class="col-md-3" style="vertical-align: middle;">
                                 <span><label>Programa Presupuestal: </label></span>
                             </div>
                             <div class="col-md-9">
                                 <select id="selectProgramaPresupuestal" name="selectProgramaPresupuestal" class="form-control selectProgramaPresupuestario" multiple>
                                   <!-- <option value='-1' selected>Sin selección</option> -->
                                 </select>
                             </div>
                         </div>
                     </div>
                      <div class="col-md-4">
                          <div class="form-inline row">
                              <div class="col-md-3" style="vertical-align: middle;">
                                  <span><label>Capítulo: </label></span>
                              </div>
                              <div class="col-md-9">
                                  <select id="selectCapitulo" name="selectCapitulo" class="form-control selectCapitulos" multiple>
                                      <!-- <option value='-1' selected>Sin selección</option> -->
                                  </select>
                              </div>
                          </div>
                          <br>
                          <div class="form-inline row">
                              <div class="col-md-3" style="vertical-align: middle;">
                                  <span><label>Banco: </label></span>
                              </div>
                              <div class="col-md-9">
                                  <select id="selectBanco" name="selectBanco" class="form-control selectBanco" onchange="bankAccount(this);">
                                      <option value='-1' selected>Sin selección</option>
                                  </select>
                              </div>

                          </div>
                          <br>
                          <div class="form-inline row">
                              <div class="col-md-3">
                                  <span><label>Cuenta CLABE: </label></span>
                              </div>
                              <div class="col-md-9">
                                  <select id="selectClave" name="selectClave" class="form-control selectClave" multiple>
                                      <!-- <option value='-1' selected>Sin selección</option> -->
                                  </select>
                              </div>
                          </div>
                      </div>
                      <div class="col-md-4">
                          <component-date-label label="Desde:" id="dateDesde" name="dateDesde" placeholder="Desde" title="Desde" value="<?= date('d-m-Y');?>"></component-date-label>
                          <br>
                          <component-date-label label="Hasta:" id="dateHasta" name="dateHasta" placeholder="Hasta" title="Hasta" value="<?= date('d-m-Y');?>"></component-date-label>
                      </div>
                      <br>
                      <div class="row"></div>
                      <div align="center">
                          <br>
                          <component-button type="button" id="btn-search" class="glyphicon glyphicon-search"  value="Filtrar"></component-button>
                      </div>
                  </form>
              </div>
          </div>
    </div>
    <br>
    <div name="divTabla" id="divTabla">
        <div name="divContenidoTabla" id="divContenidoTabla"></div>
    </div
</div>

    <script type="text/javascript" src="javascripts/auxiliarBancario.js?<?php echo rand(); ?>"></script>

<?php
include 'includes/footer_Index.inc';
