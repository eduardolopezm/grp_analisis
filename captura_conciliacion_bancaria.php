<?php

/*
 * @category Panel
 * @package ap_grp
 * @author <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 23/07/2018
 * Fecha Modificación: 23/07/2018
 * Vista para Captura de Conciliacion Bancaria
*/

$PageSecurity = 5;
$funcion = 501;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db, "Conciliación Bancaria");
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

$permiso = Havepermission($_SESSION ['UserID'], 501, $db);

//Librerias GRID Para Visualizar
include('javascripts/libreriasGrid.inc');

?>

<svg xmlns="http://www.w3.org/2000/svg" style="display: none">
    <symbol id="checkmark" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-miterlimit="10" fill="none"  d="M22.9 3.7l-15.2 16.6-6.6-7.1">
        </path>
    </symbol>
</svg>


<link rel="stylesheet" href="css/listabusqueda.css" />

<div aling="left">
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
                          <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora');" multiple="true">
                              <option value='-1' selected> Sin selección </option>
                          </select>
                      </div>
                  </div>
                  <br>
                  <div class="form-inline row">
                      <div class="col-md-3">
                          <span><label>UE: </label></span>
                      </div>
                      <div class="col-md-9">
                          <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" multiple="true">
                              <option value='-1' selected> Sin selección </option>
                          </select>
                      </div>
                  </div>
                  <br>
                  <div class="form-inline row">
                      <div class="col-md-3">
                          <span><label>Año: </label></span>
                      </div>
                      <div class="col-md-9">
                          <select id="selectYears" name="selectYears" class="form-control selectYears">
                              <option value='-1' selected> Sin selección </option>
                          </select>
                      </div>
                  </div>
                  <br>

              </div>
              <div class="col-md-4">
                  <div class="form-inline row">
                      <div class="col-md-3">
                          <span><label>Banco </label></span>
                      </div>
                      <div class="col-md-9">
                          <select id="selectBanco" name="selectBanco" class="form-control selectBanco" onchange="bankAccount(this);">
                              <option value='-1' selected> Sin selección </option>
                          </select>
                      </div>
                  </div>
                  <br>
                  <div class="form-inline row">
                      <div class="col-md-3">
                          <span><label>Cuenta CLABE: </label></span>
                      </div>
                      <div class="col-md-9">
                          <select id="selectClave" name="selectClave" class="form-control selectClave">
                              <option value='-1' selected> Sin selección </option>
                          </select>
                      </div>
                  </div>
                  <br>
                  <div class="form-inline row">
                      <div class="col-md-3">
                          <span><label>Mes: </label></span>
                      </div>
                      <div class="col-md-9">
                          <select id="selectMonthS" name="selectMonthS" class="form-control selectMonthS selectMeses" onchange="mounthORdate(this);">
                              <option value='-1'> Sin selección </option>
                          </select>
                      </div>
                  </div>
                  <br>
                  <div class="row"></div>
                  <div align="center">
                      <br>
                      <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="panel_conciliacion_bancaria.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>

                      <component-button type="button" id="btnBusqueda" name="btnBusqueda" class="glyphicon glyphicon-search" onclick="viewResultSearch();" value="Filtrar"></component-button>
                  </div>
              </div>
              <div class="col-md-4">
                  <component-date-label label="Fecha Captura: " id="txtFechaCaptura" name="txtFechaCaptura" value="<?php echo date('d-m-Y'); ?>" readonly></component-date-label>
                  <br>
                  <!--<component-date-label label="Dia Inicio: " id="txtDiaInicio" name="txtDiaInicio" value=""></component-date-label>-->
                  <div class="form-inline row">
                      <div class="col-md-3">
                          <label>Fecha Inicio:</label>
                      </div>
                      <div class="col-md-9 col-xs-12">
                          <div class="input-group" id="fechaHoyAdelantes" name="fechaHoyAdelantes" data-date-format="dd-mm-yyyy">
                              <input type="text" id="txtDiaInicio" name="txtDiaInicio" placeholder="Fecha Inicio" title="Fecha Inicio" class="form-control" value="" />
                              <span class="input-group-addon">
                          <span class="glyphicon glyphicon-calendar"></span>
                      </span>
                          </div>
                      </div>
                  </div>
                  <br>
                  <!--<component-date-label label="Dia Fin: " id="txtFechaFin" name="txtFechaFin" value=""></component-date-label>-->
                  <div class="form-inline row">
                      <div class="col-md-3">
                          <label>Fecha Final:</label>
                      </div>
                      <div class="col-md-9 col-xs-12">
                          <div class="input-group" id="fechaHoyAdelantes" name="fechaHoyAdelantes" data-date-format="dd-mm-yyyy">
                              <input type="text" id="txtFechaFin" name="txtFechaFin" placeholder="Fecha Final" title="Fecha FInal" class="form-control" value="" />
                              <span class="input-group-addon">
                          <span class="glyphicon glyphicon-calendar"></span>
                      </span>
                          </div>
                      </div>
                  </div>
                 <!-- <br>
                  <component-number-label label="Saldo BANCO:" id="txtSaldoBanco" name="txtSaldoBanco" placeholder="Num. de Transferencia" title="Saldo BANCO" value="" ></component-number-label>-->
                  <br>
              </div>
          </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">

        </div>
        <div id="PanelReducciones" name="PanelReducciones" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" style="overflow-x:scroll;overflow-y:scroll;">
            <!-- <div class="panel-body" style="overflow-x:scroll;overflow-y:scroll;"> -->
            <div id="divReducciones" name="divReducciones"></div>
            <div id="divMensajeOperacionReducciones" name="divMensajeOperacionReducciones"></div>
            <table class="table table-bordered" name="tablaConciliacion" id="tablaConciliacion">
                <thead>
                    <tr class="header-verde">
                        <th style="text-align:center;">Fecha</th>
                        <th style="text-align:center;">UR</th>
                        <th style="text-align:center;">UE</th>
                        <th style="text-align:center;">Folio</th>
                        <th style="text-align:center;">Transacción</th>
                        <th style="text-align:center;">Referencia</th>
                        <th style="text-align:center;">Clave de Rastreo</th>
                        <th style="text-align:center;">Numero de Referencia</th>
                        <th style="text-align:center;">Importe</th>
                        <th style="text-align:center;">Observaciones / Justificación</th>
                    </tr>
                </thead>
                <tbody id="tablaContenidoConciliacion"></tbody>
            </table>
            <!-- </div> -->
        </div>
    </div>
    <br><br>
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title row">
                <div class="col-md-3 col-xs-3">
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelReducciones" aria-expanded="true" aria-controls="collapseOne">
                        <b>Estado de Cuenta</b>
                    </a>
                </div>
            </h4>
        </div>
        <div class="panel-body text-left">
            <!-- begin upload file -->
            <div class="col-xs-12 col-md-12 pt20">

                <div class="soloCargarArchivos" id="uploadFilesDiv">
                    <input type="hidden" id="esMultiple" name="esMultiple" value="1">
                    <input type="hidden" value="" name="componente" id="componenteArchivos"/>
                    <input type="hidden" value="2387" id="funcionArchivos" name="funcionArchivos"/>
                    <input  type="hidden"  value="285" id="tipoArchivo"/>
                    <input  type="hidden"  value="" id="transnoArchivo"/>
                    <input  type="hidden"  value="" id="numberScene"/>
                    <div id="mensajeArchivos"> </div>

                    <form enctype="multipart/form-data" id="fileinfoX" name="fileinfoX">

                    <div  id="subirArchivos"  class="col-md-12">

                        <div>
                            <label for="tipoInput"  style=" padding: 6px 12px !important; border-radius: 3px 3px 3px 3px !important; background: #1B693F !important; color:#fff !important;  border:0px solid #fff !important; cursor: pointer !important;">
                                <i class="glyphicon glyphicon-file"></i> Cargar Archivo
                            </label>
                            <input type="file" name="tipoInput" id="tipoInput" style='display: none;'>
                        </div>

                        <br>
                        <div class="col-md-12 text-center">
                            <div id="muestraAntesdeEnviar" class="" style="display: none;"> <!-- show files upload   style="color:#fff !important;"     -->
                                <table class="table table-striped border" id="tablaDetallesArchivos" style="border:solid 1px #eee;">
                                    <thead class="bgc8 text-center" style="color:#fff;">
                                    <th> </th>
                                    <th class="text-center">Nombre</th>
                                    <!-- <th class="text-center">Tamaño</th>   -->
                                    <th> </th>
                                    </thead>
                                    <tbody id="demo">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div> <!--end  upload  file -->
        </div>
    </div>
    <br>
    <div class="row"></div>
    <div align="center">
      <div class="col-lg-12">
          <div class="col-md-4">
              <label for="selectElabora">Elaboró</label>
              <select id="selectElabora" name="selectElabora" class="form-control selectElabora">
                  <option value='-1'> Sin selección </option>
              </select>
          </div>
          <div class="col-md-4">
              <label for="selectValido">Validó</label>
              <select id="selectValido" name="selectValido" class="form-control selectValido">
                  <option value='-1'> Sin selección </option>
              </select>
          </div>
          <div class="col-md-4">
              <label for="selectAuth">Autorizó</label>
              <select id="selectAuth" name="selectAuth" class="form-control selectAuth">
                  <option value='-1'> Sin selección </option>
              </select>
          </div>
      </div>
    </div>
    <br><br><br><br><br>
    <div class="row"></div>
    <div align="center">
        <component-button type="button" id="btnConciliar" name="btnConciliar" class="" onclick="conciliar();" value="Conciliar"></component-button>
    </div>
    <br>
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
        </div>
        <div id="PanelArchivosBanco" name="PanelArchivosBanco" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" style="overflow-x:scroll;overflow-y:scroll;">
            <!-- <div class="panel-body" style="overflow-x:scroll;overflow-y:scroll;"> -->
            <!-- <div id="divReducciones" name="divReducciones"></div>-->

           <table class="table table-bordered" name="tablaConciliacion" id="tablaArchivoBancos">
               <thead id="headertable">
                   <tr class="header-verde">
                       <th style="text-align:center;"></th>
                       <th style="text-align:center;">Fecha Operación</th>
                       <th style="text-align:center;">UR</th>
                       <th style="text-align:center;">UE</th>
                       <th style="text-align:center;">Folio</th>
                       <th style="text-align:center;">Código Transacción</th>
                       <th style="text-align:center;">Referencia</th>
                       <th style="text-align:center;">Clave de Rastreo</th>
                       <th style="text-align:center;">Numero de Referencia</th>
                       <th style="text-align:center;">Importe</th>
                       <th style="text-align:center;">Movimiento</th>
                       <th style="text-align:center;">Descripción Detallada</th>
                   </tr>
               </thead>
               <tbody id="contenidoTablaArchivos"></tbody>
           </table>
           <!-- </div> -->
        </div>
    </div>

</div>

<script type="text/javascript" src="javascripts/captura_conciliacion_bancaria.js?<?php echo rand(); ?>"></script>
    <script type="text/javascript" src="javascripts/xlsx.full.min.js"></script>


<?php
include 'includes/footer_Index.inc';