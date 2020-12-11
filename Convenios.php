<?php
/**
 * ABC de Almacén
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Se realizan operación pero el Alta, Baja y Modificación
 */

$PageSecurity = 8;
include('includes/session.inc');
$funcion=2477;
$title = traeNombreFuncion($funcion, $db,'Agregar Convenios');
include "includes/SecurityUrl.php";
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');
$info=array();

include 'javascripts/libreriasGrid.inc';
?>

<script type="text/javascript" src="javascripts/convenios_alta.js?<?php echo rand(); ?>"></script>

<div class="col-xs-12 col-md-12 text-left"> 

<div name="tablaConvenios" id="tablaConvenios">
  <div name="datosConvenios" id="datosConvenios"></div>
</div>

<div class="row text-center pt40"> 
<button id="btnConvenios" class="btn botonVerde"> Nuevo</button>
</div>

<div class="modal fade" id="ModalConvenio" name="ModalConvenio" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
        <div class="modal-content">
            <div class="navbar navbar-inverse navbar-static-top">
                <div class=" menu-usuario">
                    <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <div class="nav navbar-nav">
                        <div class="title-header">
                            <div id="ModalConvenio_Titulo" name="ModalConvenio_Titulo"></div>
                        </div>
                    </div>
                </div>
                <div class="linea-verde"></div>
            </div>
            <div class="modal-body" id="ModalInfoPago_Mensaje" name="ModalInfoPago_Mensaje">
                <div class="row clearfix">
                    <div class="row clearfix col-xs-12 col-md-12">
                        <div class="col-xs-12 col-md-12 text-left" style="background-color:#f2dede !important;color:#a94442; display: none;" id="valMsgErrorConvenio" > <br>
                        </div>
                         <div class="col-xs-12 col-md-12 text-left" style="background-color:#00ff40 !important;color:#090; display: none;" id="valMsgSuccessConvenio" > <br>  
                        </div>
                    </div>
                    <div>
                        <!--AQUI PONER-->
                        <div id="datosPrincipales" name="datosPrincipales" role="tabpanel" aria-labelledby="headingOne" class="panel-collapse collapse in"><br>

                            <form id="conveniosAlta">
                                <div class="text-left container">
                                    <div class="row clearfix" id="fila1">

                                        <div class="col-xs-12 col-md-6 pt20">
                                        </div>

                                        <div class="col-xs-12 col-md-6  pt20 clRFC" style=""  id="fisicaRfc">
                                            <component-text-label label="Septimo Nivel CC:" id="sncc" name="sncc" placeholder="Septimo Nivel Cuenta Contable" title="Septimo Nivel CC" value="" ></component-text-label>
                                        </div>

                                    </div><!-- fin fila1-->
                                    
                                    
                                    <div class="row clearfix" id="fila1">

                                        <div class="col-xs-12 col-md-6 pt20">
                                            <component-text-label label="Año:" id="anio" name="anio" title="Año" maxlength="4" value="<?php echo date("Y") ?>" readonly></component-text-label>
                                        </div>

                                        <div class="col-xs-12 col-md-6  pt20 clRFC" style=""  id="fisicaRfc"><?php
                                            // <component-text-label label="PP:" id="pp" name="pp" placeholder="Programa Presupuestal" title="PP" value="" ></component-text-label>
                                            $result = DB_query("SELECT `pe` AS `cp`, CONCAT(`pe`, ' - ', `descripcion`) AS `comp` FROM `tb_cat_programa_extrapresupuestario` WHERE `activo` = '1'", $db);
                                            echo '
    <div class="row" style="text-align: left;">
            <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                <span><label>PE: </label></span>
            </div>
            <div class="col-xs-9 col-md-9">
                <select id="pp" name="pp" class="form-control">
                <option value="0">Seleccionar..</option>';

                                            while ($myrow = DB_fetch_array($result)) {

                                                echo '<option VALUE=' . $myrow['cp'] . '>' . $myrow['comp'] . '</option>';
                                            }
                                            DB_data_seek($result, 0);
                                            echo '</select>
        </div>
    </div>'
                                            ?>
                                        </div>

                                    </div><!-- fin fila1-->

                                    <div class="row clearfix" id="fila2">

                                        <div class="col-xs-12 col-md-6 pt20">
                                            <component-text-label label="Ramo:" id="ramo" name="ramo" title="Ramo" value="08-Secretaría de Agricultura, Ganadería, Desarrollo Rural, Pesca y Alimentación" readonly></component-text-label>
                                        </div>


                                        <div class="col-xs-12 col-md-6 pt20">

                                            <?php
                                            $result = DB_query('SELECT cp,concat(cp,"-",descripcion) as comp FROM tb_componente_presupuestal', $db);
                                            echo '
    <div class="row" style="text-align: left;">
            <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                <span><label>CP: </label></span>
            </div>
            <div class="col-xs-9 col-md-9">
                <select id="cpSel" name="cp" class="form-control">
                <option value="0">Seleccionar..</option>';

                                            while ($myrow = DB_fetch_array($result)) {

                                                echo '<option VALUE=' . $myrow['cp'] . '>' . $myrow['comp'] . '</option>';
                                            }
                                            DB_data_seek($result, 0);
                                            echo '</select>
        </div>
    </div>'
                                            ?>
                                        </div>

                                    </div><!-- fin fila2-->



                                    <div class="row clearfix" id="fila3">

                                        <div class="col-xs-12 col-md-6 pt20">
                                            <component-text-label label="UR:" id="ur" name="ur" title="UR" value="I6L-Fideicomiso de Riesgo Compartido" readonly></component-text-label>
                                        </div>

                                        <div class="col-xs-12 col-md-6  pt20" style=""  id="">
                                            <component-text-label label="Clave:" id="clave" name="clave" placeholder="Clave" title="Clave" value="" ></component-text-label>
                                        </div>

                                    </div><!-- fin fila3-->

                                    <div class="row clearfix" id="fila4">

                                        <div class="col-xs-12 col-md-6 pt20">

                                            <?php
                                            $result = DB_query('SELECT ue,concat(ue,"-",desc_ue) as comp FROM tb_cat_unidades_ejecutoras where active = 1 ORDER BY ue ASC', $db);
                                            echo '
    <div class="row" style="text-align: left;">
            <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                <span><label>UE: </label></span>
            </div>
            <div class="col-xs-9 col-md-9">
                <select id="ueSel" name="ue" class="form-control">
                <option value="0">Seleccionar..</option>';

                                            while ($myrow = DB_fetch_array($result)) {

                                                echo '<option VALUE=' . $myrow['ue'] . '>' . $myrow['comp'] . '</option>';
                                            }
                                            DB_data_seek($result, 0);
                                            echo '</select>
        </div>
    </div>'
                                            ?>
                                        </div>

                                        <div class="col-xs-12 col-md-6  pt20" style=""  id="">
                                            <component-text-label label="Descripcion:" id="descripcion" name="descripcion" placeholder="Descripción" title="Descripción" value="" ></component-text-label>
                                        </div>

                                    </div><!-- fin fila4-->

                                    <div class="row clearfix" id="fila5">
                                        <div class="col-xs-12 col-md-6 pt20">

                                            <?php
                                            $result = DB_query('SELECT tipo_convenio,concat(tipo_convenio,"-",descripcion) as comp FROM tb_tipo_convenio', $db);
                                            echo '
    <div class="row" style="text-align: left;">
            <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                <span><label>Tipo de Convenio: </label></span>
            </div>
            <div class="col-xs-9 col-md-9">
                <select id="idConvenioSel" name="tipo_convenio" class="form-control">
                <option value="0">Seleccionar..</option>';

                                            while ($myrow = DB_fetch_array($result)) {

                                                echo '<option VALUE=' . $myrow['tipo_convenio'] . '>' . $myrow['comp'] . '</option>';
                                            }
                                            DB_data_seek($result, 0);
                                            echo '</select>
        </div>
    </div>'
                                            ?>
                                        </div>

                                        <div class="col-xs-12 col-md-6  pt20 clRFC" style=""  id="">
                                            <div class="row">
                                            <div class="col-xs-3 col-md-3" style="vertical-align: middle">
                                                <span><label>Fecha Inicio: </label></span>
                                            </div>
                                            <div class="col-xs-9 col-md-9">
                                                <component-date-feriado2 id="fechaInicio" name="fechaInicio" class="w100p" placeholder="Fecha inicio" value=""></component-date-feriado>                                
                                            </div>
                                            </div>
                                        </div>

                                    </div><!-- fin fila5-->

                                    <div class="row clearfix" id="fila6">
                                        <div class="col-xs-12 col-md-6  pt20" style=""  id="">
                                            <div class="row" style="text-align: left;">
                                                <div class="col-xs-3 col-md-3" style="vertical-align: middle">
                                                <span><label>Estatus: </label></span>
                                            </div>
                                            <div class="col-xs-12 col-md-9">
                                                <select id="estatus" name="estatus" class="activoSupp form-control">;
                                                    <option value="-1">Seleccionar..</option>
                                                    <option value="1">Activo</option>
                                                    <option value="0">Inactivo</option>
                                                </select>
                                            </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-xs-12 col-md-6  pt20 clRFC" style=""  id="">
                                                <div class="row">
                                                    <div class="col-xs-3 col-md-3" style="vertical-align: middle">
                                                        <span><label>Fecha Fin: </label>
                                                    </div>
                                                    <div class="col-xs-9 col-md-9">
                                                        <component-date-feriado2 id="fechaFin" name="fechaFin" class="w100p" placeholder="Fecha fin" value=""></component-date-feriado>                                
                                                    </div>      
                                                </div>
                                            </div>

                                        
                                            
                                            
                                            
                                        
                                    </div><!-- fin fila6-->
                                </div><!-- fin container-->
                            </form>




                        </div> <!-- fin container1-->
                    </div><!--fin panel datosPrincipales-->

                    </div>

                    <!--fin contenido-->
                </div>
                <br> <br> <br>
                <div class="modal-footer">
                    <!--contenido -->

                    <div class="col-xs-6 col-md-6 text-right">
                        <div id="procesandoPagoEspere"> </div> <br>



                        <button class="btn bgc8" style="color:white; font-weight: bold;" id="btnSaveConvenio">Guardar </button>

                        <button class="btn bgc8" style="color:white; font-weight: bold;" id="btnCerrarPago" name="btnCerrarTipoConvenio" data-dismiss="modal" >Cerrar</button>

                    </div> <!--fin moddal -->

                </div>

                <!--fin contenido footer -->
            </div>
        </div>
    </div>





</div>

<div id="ModalGeneral1" name="ModalGeneral1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false" class="modal fade ui-draggable out" style="display: none; padding-left: 0px;">


    <div role="document" name="ModalGeneral1Tam" id="ModalGeneral1Tam" class="modal-dialog ui-draggable-handle modal-md"><div class="modal-content"><div class="navbar navbar-inverse navbar-static-top">

        <div class="col-md-12 menu-usuario"><span data-dismiss="modal" class="glyphicon glyphicon-remove"></span></div>

        <div id="navbar" class="navbar-collapse collapse"><div class="nav navbar-nav"><div class="title-header"><div id="ModalGeneral1_Titulo" name="ModalGeneral1_Titulo"><h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3></div></div></div></div> <div class="linea-verde"></div>

    </div> <div class="modal-body">
    <input type="hidden" id="tipoMg" value="">
    <input type="hidden" id="accionMg" value="">
      <div id="ModalGeneral1_Advertencia" name="ModalGeneral1_Advertencia"></div> <div id="ModalGeneral1_Mensaje" name="ModalGeneral1_Mensaje">
    

    </div></div> <div class="modal-footer"><div id="ModalGeneral1_Pie" name="ModalGeneral1_Pie">            <div class="input-group pull-right">                <button class="btn btn-default botonVerde" onclick="" id="confirmacionModalGeneral1">Si</button>                <button class="btn btn-default botonVerde" data-dismiss="modal" id="btnCerrarModalGeneral1" name="btnCerrarModalGeneral1">No</button>            </div></div></div></div></div></div>
<!--fin modal-->

<?php
require 'includes/footer_Index.inc';
?>

<script type="text/javascript">
$(document).ready(function(e){
    fnMostrarDatosConvenio();
});
</script>



