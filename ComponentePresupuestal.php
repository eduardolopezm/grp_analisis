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
$funcion=2479;
$title = traeNombreFuncion($funcion, $db,'Agregar Componente Presupuestal');
include "includes/SecurityUrl.php";
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');
$info=array();

include 'javascripts/libreriasGrid.inc';
?>

<script type="text/javascript" src="javascripts/convenios_alta.js?<?php echo rand(); ?>"></script>

<div class="col-xs-12 col-md-12 text-left"> 


<div name="tablaComponentePresupuestal" id="tablaComponentePresupuestal">
  <div name="datosComponentePresupuestal" id="tablaComponentePresupuestal"></div>
</div>

<div class="row text-center pt40"> 

 <button id="btnComponentesPresup" class="btn botonVerde"> Nuevo</button>
</div>


<div class="modal fade" id="ModalComponentesPresup" name="ModalComponentesPresup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
        <div class="modal-content">
            <div class="navbar navbar-inverse navbar-static-top">
                <div class=" menu-usuario">
                    <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <div class="nav navbar-nav">
                        <div class="title-header">
                            <div id="ModalComponentesPresup_Titulo" name="ModalComponentesPresup_Titulo"></div>
                        </div>
                    </div>
                </div>
                <div class="linea-verde"></div>
            </div>
            <div class="modal-body" id="ModalInfoPago_Mensaje" name="ModalInfoPago_Mensaje">
                <div class="row clearfix">
                    <div class="row clearfix col-xs-12 col-md-12">
                        <div class="col-xs-12 col-md-12 text-left" style="background-color:#f2dede !important;color:#a94442; display: none;" id="valMsgErrorComponentePresupuestal" > <br>
                        </div>
                         <div class="col-xs-12 col-md-12 text-left" style="background-color:#00ff40 !important;color:#090; display: none;" id="valMsgSuccessComponentePresupuestal" > <br>  
                        </div>
                    </div>
                    <div>
                        <!--AQUI PONER-->
                        <div id="datosPrincipales" name="datosPrincipales" role="tabpanel" aria-labelledby="headingOne" class="panel-collapse collapse in"><br>

                            <form id="componentePresupuestalAlta">
                                <div class="text-left container">



                                    <div class="row clearfix" id="fila1">

                                        <div class="col-xs-12 col-md-12 pt20">
                                            <component-text-label label="CP:" id="cp" name="cp" title="CP" placeholder='CP' value=""></component-text-label>
                                        </div>
                                    </div><!-- fin fila1-->

                                    <div class="row clearfix" id="fila1">
                                        <div class="col-xs-12 col-md-12  pt20" style=""  id="">
                                            <component-text-label label="Descripción:" id="descripcion" name="descripcion" placeholder="Descripción" title="Descripción" value="" ></component-text-label>
                                        </div>

                                    </div><!-- fin fila1-->


                                </div><!-- fin container-->
                            </form>




                        </div>
                    </div> <!-- fin container1-->
                </div><!--fin panel datosPrincipales-->
                        

                    

                    <!--fin contenido-->
            
                
                <br> <br> <br>
                <div class="modal-footer">
                    <!--contenido -->

                    <div class="col-xs-6 col-md-6 text-right">
                        <div id="procesandoPagoEspere"> </div> <br>



                        <button class="btn bgc8" style="color:white; font-weight: bold;" id="btnSaveComponentePresupuestal">Guardar </button>

                        <button class="btn bgc8" style="color:white; font-weight: bold;" id="btnCerrarPago" name="btnCerrarTipoConvenio" data-dismiss="modal" >Cerrar</button>

                    </div> <!--fin moddal -->

                </div>
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
    fnMostrarDatosComponentePresupuestal();
});
</script>



