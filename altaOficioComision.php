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
 * Panel para la captura de oficios de comision
 * @file: altaOficioComision.php
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
$title= traeNombreFuncion($funcion, $db, 'Captura de Oficio de Comisión');
require 'includes/header.inc';
require 'javascripts/libreriasGrid.inc';
# comprobación de modificación o generación
$folio = empty($_GET['folio'])? '' : $_GET['folio'];
$estatusAnexo = empty($_GET['estatusAnexo'])? '' : $_GET['estatusAnexo'];
$titulo = !empty($folio)? "<b>MODIFICACIONES DE OFICIO</b>" : "<b>CAPTURA DE OFICIO DE COMISIÓN</b>";

$ocultaDepencencia = 'hidden';
?>
<link rel="stylesheet" href="css/listabusqueda.css" /><!-- Estilos para el auto complete -->
<script src="javascripts/altaOficioComision.js?v=<?= rand();?>"></script>
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
                            <strong>Datos Generales</strong>
                        </a>
                    </div>
                </h4>
            </div>
            <div id="datosGenerales" name="datosGenerales" class="panel-collapse collapse in">
                <div class="panel-body">
                    <div id="formGenerales">
                        <div class="row">
                            <!-- dependencia, UR, UE, folio -->
                            <div class="col-md-3">
                                <div class="form-inline row <?= $ocultaDepencencia ?>">
                                    <div class="col-md-3">
                                        <span><label>Dependencia: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <!-- <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial"
                                        onchange="fnCambioDependeciaGeneral('selectRazonSocial', 'selectUnidadNegocio')" multiple></select> -->
                                    </div>
                                </div><!-- .form-inline .row -->

                                <br class="<?= $ocultaDepencencia ?>">

                                <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>UR: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="selectUnidadNegocio" name="selectUnidadNegocio" data-todos="true" class="form-control selectUnidadNegocio"
                                        onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')"></select>
                                    </div>
                                </div><!-- .form-inline .row -->

                                <br>

                                <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>UE: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" data-todos="true" class="form-control selectUnidadEjecutora"></select>
                                    </div>
                                </div><!-- .form-inline .row -->

                                <br>

                                <component-text-label label="Oficio No:" name="noOficio" id="noOficio" maxlength="20"></component-text-label>

                            </div><!-- -col-md-4 -->

                            <!-- empleado, rfc, puesto -->
                            <div class="col-md-3">

                                <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>Empleado: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="idEmpleado" name="idEmpleado" class="form-control"></select>
                                    </div>
                                </div><!-- .form-inline .row -->

                                <br>

                                <div class="form-inline row">
                                    <div class="col-sm-3" style="vertical-align:middle"><label class="control-label">RFC:</label></div>
                                    <div class="col-sm-9"><input type="text" class="form-control w100p" name="eRFC" id="eRFC" readOnly ></div>
                                </div><!-- .form-inline .row -->

                                <br>

                                <div class="form-inline row">
                                    <div class="col-sm-3" style="vertical-align:middle"><label class="control-label">Puesto:</label></div>
                                    <div class="col-sm-9"><input type="text" class="form-control w100p" name="Epuesto" id="Epuesto" readOnly ></div>
                                </div><!-- .form-inline .row -->
                            </div>

                            <!-- tipo viatico, fecha inicio, fecha fin, -->
                            <div class="col-md-3">
                                <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>Tipo Viático: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="tipoGasto" name="tipoGasto" class="form-control tipoGasto" data-todos="true">
                                            <option value="0">Seleccionar...</option>
                                            <option value="1">Nacional</option>
                                            <option value="2">Internacional</option>
                                        </select>
                                    </div>
                                </div>

                                <br>

                                <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>Fecha Inicio: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <component-date-feriado2 label="Fecha inicio"  id="fechaInicio" name="fechaInicio" class="w100p" placeholder="Fecha inicio" disabled="disabled"></component-date-feriado2>
                                    </div>
                                </div><!-- .form-inline.row -->

                                <br>

                                <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>Fecha Término: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <component-date-feriado2 label="Fecha termino"  id="fechaTermino" name="fechaTermino" class="w100p" placeholder="Fecha término" disabled="disabled"></component-date-feriado2>
                                    </div>
                                </div><!-- .form-inline.row -->

                            </div><!-- .col-md-3 -->

                            <div class="col-md-3 SelectsALaDerecha">
                                <div class="form-inline row">
                                   <div class="col-md-4">
                                      <label><input type="checkbox" id="homologar" name="homologar" class="vertical-align:middle;position: relative; bottom: 1px;"> Homologar </label>
                                   </div>
                                   <div class="col-md-8">
                                      <select id="idEmpleadoHomologar" name="idEmpleadoHomologar" class="form-control" style="width: 100%; overflow: hidden; text-overflow: ellipsis;" disabled="disabled">
                                          <option>Seleccionar...</option>
                                      </select>
                                   </div>    
                                </div> <!-- .form-inline row --> 
                            </div> <!-- .col-md-3 -->
                        </div> <!-- .row -->
                        <br>
                        <div class="row">
                                <div class="col-md-1 col-xs-12">
                                    <label>Objetivo comisión</label>
                                </div>
                                <div class="col-md-11 col-xs-12">
                                    <component-textarea id="txtAreaObs" name="txtAreaObs" rows="5" placeholder="Objetivo de Comisión"></component-textarea>
                                </div>
                            <!-- <div class="col-sm-12">
                            </div> -->
                        </div>
                    </div><!-- #formGenerales -->
                </div><!-- .panel-body -->
            </div>
        </div><!-- .panel .panel-default -->
    </div><!-- .panel-group -->
</div><!-- .row -->

<!-- control presuestal -->
<div class="row">
    <div class="panel-group">
            <div class="panel panel-default">
            <div class="panel-heading h35">
                <h4 class="panel-title">
                    <div class="fl text-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#controlPresupuestal" aria-expanded="true" aria-controls="collapseOne">
                            <strong>Control Presupuestal</strong>
                        </a>
                    </div>
                </h4>
            </div>
            <div id="controlPresupuestal" name="controlPresupuestal" class="panel-collapse collapse in">
                <div class="panel-body">
                    <div id="formPresupuestal">
                        <div class="row">
                            <!-- tipo comision,  transporte, pernocta % -->
                            <div class="col-sm-12" style="margin-bottom: 15px;">
                                <div class="col-md-4">
                                    <div class="form-inline row">
                                        <div class="col-md-3" style="vertical-align: middle;">
                                            <span><label>Tipo de Comisión: </label></span>
                                        </div>
                                        <div class="col-md-9">
                                            <select id="tipoSol" name="tipoSol" class="form-control tipoSol" data-todos="true">
                                                <option value="0">Seleccionar...</option>
                                                <option value="1">Nacional</option>
                                                <option value="2">Internacional</option>
                                            </select>
                                        </div>
                                    </div><!-- .form-inline .row -->
                                </div>
                                <div class="col-md-4">
                                    <div class="form-inline row">
                                        <div class="col-md-3" style="vertical-align: middle;">
                                            <span><label>Transporte: </label></span>
                                        </div>
                                        <div class="col-md-9">
                                            <select id="trasnporte" name="trasnporte" class="form-control trasnporte">
                                                <option value="0">Seleccionar...</option>
                                                <!--<option value="1">Vehículo Particular</option>
                                                <option value="2">Vehículo Oficial</option>
                                                <option value="3">Transporte Terrestre</option>
                                                <option value="4">Pasaje Aéreo</option> -->
                                            </select>
                                        </div>
                                    </div><!-- .form-inline.row -->
                                </div>
                                <div class="col-md-4">
                                    <component-number-label label="Pernocta %:" id="cantPernocta" name="cantPernocta" max="50"></component-number-label>
                                    <input type="hidden" id="idNuViaticos" name="idNuViaticos" value="identificadorViaticos" >
                                </div>
                            </div><!-- .col-sm-12 -->
                        </div>
                    </div><!-- #formPresupuestal -->
                </div><!-- .panel-body -->
            </div>
        </div><!-- .panel .panel-default -->
    </div><!-- .panel-group -->
</div><!-- .row -->

<!-- Itinerario -->
<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading h35">
                <h4 class="panel-title">
                    <div class="fl text-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#datosItinerario" aria-expanded="true" aria-controls="collapseOne">
                            <strong>Itinerario</strong>
                        </a>
                    </div>
                </h4>
            </div>
            <div id="datosItinerario" name="datosItinerario" class="panel-collapse collapse in">
                <div class="panel-body">
                    <div class="row">
                        <span id="claveP" class="col-sm-12">
                            <!-- <component-text-label label="Clave Presupuestal:" name="clavePresupuestal" id="clavePresupuestal" onpaste="return true;"></component-text-label> -->
                            <div class="form-inline row">
                                    <div class="col-md-3" style="vertical-align: middle;">
                                        <span><label>Clave Presupuestal: </label></span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="clavePresupuestal" name="clavePresupuestal" class="form-control clavePresupuestal">
                                            <option value="0">Seleccionar...</option>
                                        </select>
                                    </div>
                                </div><!-- .form-inline.row -->
                        </span>
                    </div>

                    <br>

                    <div id="cabecera">
                        <nav class="row nav bgc8 fts10 borderGray w100p ftc2">
                            <div class="w4p  falsaCabeceraTabla"><label></label></div>
                            <div class="w80p  falsaCabeceraTabla"><label></label></div>
                            <div class="w6p  falsaCabeceraTabla"><label>TOTAL:</label></div>
                            <div class="w10p  falsaCabeceraTabla"><label id="TotalComision"></label></div>
                        </nav>
                        <nav class="row nav bgc8 fts10 borderGray w100p ftc2">
                            <div class="col-lg-12 col-md-12 col-sm-12 p0 m0">
                                <?php if(empty($estatusAnexo)){ ?>
                                    <div class="w3p pt3  falsaCabeceraTabla">
                                            <span id="add" class="btn btn-default btn-xs glyphicon glyphicon-plus"></span>
                                    </div>
                                <?php } // if $estatusAnexo ?>
                                <div class="w3p  falsaCabeceraTabla"> Nª </div>
                                <span id="tipoNacionalArea">
                                    <div class="w17p  falsaCabeceraTabla"><label>Entidad</label></div>
                                    <div class="w17p  falsaCabeceraTabla"><label>Municipio</label></div>
                                </span>
                                <span id="tipoInterArea" class="hidden">
                                    <div class="w34p  falsaCabeceraTabla"><label>País</label></div>
                                </span>
                                <div class="w12p  falsaCabeceraTabla"><label>Fecha Inicio</label></div>
                                <div class="w12p  falsaCabeceraTabla"><label>Fecha Término</label></div>
                                <div id="zonaEconomica" class="w7p  falsaCabeceraTabla"><label style="margin-top: -9px;">Zona<br>Económica</label></div>
                                <div id="cuota" class="w7p  falsaCabeceraTabla"><label style="margin-top: -9px;">Cuota/Unidades diaria</label></div>
                                <div id="dias"  class="w6p  falsaCabeceraTabla"><label>Días</label></div>
                                <div class="w6p  falsaCabeceraTabla"><label>Noches</label></div>
                                <div id="importe" class="w10p falsaCabeceraTabla"><label>Importe</label></div>
                            </div>
                        </nav>
                        <div id="tbl-itinerario"><!-- contenedor de la pagina principal --></div>
                    </div><!-- .row -->
                </div><!-- .panel-body -->
            </div>
        </div><!-- .panel .panel-default -->
    </div><!-- .panel-group -->
</div><!-- .row -->

</div>




<!-- botonera -->
<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-body text-center">
                <!-- botones de acción -->
                <?php if(!empty($folio)){ ?>
                    <component-button type="button" id="btn-update" class="glyphicon glyphicon-floppy-disk"  value="Guardar"></component-button>
                <?php }else if(empty($folio)){ ?>
                    <component-button type="button" id="btn-add" class="glyphicon glyphicon-floppy-disk"  value="Guardar"></component-button>
                <?php }// end if ?>
                <component-button type="button" class="glyphicon glyphicon-trash regresar"  value="Cancelar"></component-button>
                <component-button type="button" class="glyphicon glyphicon-home regresar"  value="Regresar"></component-button>
            </div><!-- .panel-body -->
        </div><!-- .panel -->
    </div><!-- .panel-group -->
</div><!-- .row -->

<?php require 'includes/footer_Index.inc'; ?>
