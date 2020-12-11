<?php
/**
 * REPORTE DE PAGOS A PROVEEDORES
 *
 * @category
 * @package      ap_grp
 * @author       Arturo Lopez Peña <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 11/08/2017
 * Fecha Modificación: 11/08/2017
 * Vista para el proceso de REPORTE DE PAGOS A PROVEEDORES
 */
//print (date("d-m-Y"));

$PageSecurity = 5;

require 'includes/session.inc';

//$title = _('REPORTE DE PAGOS A PROVEEDORES');

$funcion = 244;
$title= traeNombreFuncion($funcion, $db);
/*
echo $_SESSION['UserID'];
echo "prueba";
echo $_SESSION['Login']; */
require 'includes/header.inc';
require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';
$permiso = Havepermission($_SESSION ['UserID'], 244, $db); // tenia 2006

$visibleCheques= Havepermission($_SESSION ['UserID'], 2339, $db);
// if(Havepermission($_SESSION ['UserID'],2339 , $db)==1) { //validador
//        $visibleCheques=1; 
//     }

require 'javascripts/libreriasGrid.inc';
?>

<script type="text/javascript" src="javascripts/GeneralPaymentsDetailsV2.js?<?php echo rand(); ?>"></script>

<script type="text/javascript" src="javascripts/GeneralPaymentsDetailsV2_calendario.js?<?php echo rand(); ?>"></script>

<script type="text/javascript" src="javascripts/Subir_Archivos.js"> </script>

<style type="text/css">
  .panelTitulo {
    font-size: 25px; 
    font-style: bold;
  }
</style>

<style>
 #calendar {
  margin-top: 40px;
  text-align: center;
  font-size: 14px;
  font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
  }
 #calendar {
  width: 324px;
  margin: 0 auto;
  }
</style>
<style type="text/css">
#dateDesde .bootstrap-datetimepicker-widget table td.disabled,
#dateDesde .bootstrap-datetimepicker-widget table td.disabled:hover {
  background: none;
  color: #CC0000; /*dias deshabilidatdos */
  cursor: not-allowed;
}

#dateDesde .bootstrap-datetimepicker-widget table td{
 
  color: #1B693F; /*dias normarles */
  
}

</style>

       <style type="text/css">
    .seccionArribaCalendario
    {
        
        text-align:right;
        font-size: 12px;
      
        color:#1B693F !important;
        text-align: right;
        
    }

    .seccionEnMedioCalendario
    {
        
        text-align:left;
        font-size: 12px;
       
        
    }
    .seccionInferiorCalendario
    {

        text-align:left;
        font-size: 12px;
        
         
    }
   

</style>


<div align="left">
    <div class="panel panel-default" >
        <div class="panel-body" >

        <ul class="nav nav-tabs">
                        <li class="active" ><a class="bgc10" data-toggle="tab" href="#bandeja">Bandeja de entrada</a></li>
                      <li ><a id ="calendarioPestana" class="bgc10" data-toggle="tab" href="#programados">Programados</a></li>
             <!-- <li><a class="bgc10" data-toggle="tab" href="#subirCsv"> Subir CSV </a></li>
                   <li><a class="bgc10" data-toggle="tab" href="#subidosCLC">CLC subidos</a></li>-->
                        <?php
                        if ($visibleCheques==1) {
                        ?>
                        <li><a class="bgc10" data-toggle="tab" href="#cancelarCheque">Cancelar Cheque</a></li>
                        <?php } ?>

                      <!-- <li><a class="bgc10" data-toggle="tab" href="#cancelarCHOPTR">Búsqueda</a></li>-->

                       <!--  <li><a class="bgc10" data-toggle="tab" href="#Movimientos">Movimientos</a></li>-->
                      
          
        </ul>
    <div class="tab-content">
        <div id="programados" class="tab-pane fade">
        <!--<h3>Programados</h3>-->
       <div id='calendar'></div>
<br> <br>
<div class="col-xs-6 col-md-6 text-right"> 
<div class="col-md-4"> 
 <div class="input-group" style="font-size: 22px !important;">
    <span class="input-group-addon" style="background: none; border: none;"> Mes: </span>
     <select id="selectMeses1" name="selectMeses1" class="form-control selectMeses1">
    </select>
  </div>
</div>
  <div class="col-md-4"> 
   <div class="input-group" style="font-size: 22px !important;">
       <span class="input-group-addon" style="background: none; border: none;"> Año: </span>
      <select id="selectAnio" name="selectAnio" class="form-control selectAnio">
        
      </select>

  </div>
  </div>

</div>
<br><br>

  <div id="pagosnuevosDetectados" class="col-xs-12 col-md-12 btn text-center" style="background-color:#f2dede !important;color:#a94442; display: none;"><h4>Se detecto nuevo(s) pagos programados <u style="color:blue"> agregar al calendario</u>.</h4></div> <br><br>

        
       <div class="col-md-12" id="calendarioAbajo"> </div>

<!--<div class="modal fade" id="ModalInfoDia" name="ModalInfoDia" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
    <div class="modal-content">
      <div class="navbar navbar-inverse navbar-static-top">
        <!--Contenido Encabezado-->
       <!-- <div class="col-md-sm menu-usuario">
          <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <div class="nav navbar-nav">
            <div class="title-header">
              <div id="ModalInfoDia_Titulo" name="ModalInfoDia_Titulo"></div>
            </div>
          </div>
        </div>
        <div class="linea-verde"></div>
      </div>
      <div class="modal-body" id="ModalInfoDia_Mensaje" name="ModalInfoDia_Mensaje">
        <!--Mensaje o contenido-->
        <!-- <div id="mensajesValidaciones" name="mensajesValidaciones"></div>
         <div id="ligaCLC" style="display: none;"> </div>
          <div name="divTablaDia" id="divTablaDia">        
                    <div name="divDatosDia" id="divDatosDia"></div>
                </div>
      </div>
      <div class="modal-footer">
                <!--
                <component-button type="button" id="btnGenerarCLC" name="btnGenerarCLC" onclick="fnGeneraCLC()" value="Generar CLCs"></component-button>-->
        <!--<component-button type="button" id="btnCerrar" name="btnCerrar" data-dismiss="modal" value="Cerrar"></component-button>
      </div>
    </div>
  </div>
</div>-->



</div><!-- fin tab programados-->


    <div id="bandeja" class="tab-pane fade in active">
      <!--<h3>Bandeja de entrada</h3>-->
            <br>
     <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelCriteriosBusqueda" aria-expanded="true" aria-controls="collapseOne">
            <b>Criterios de filtrado</b>
          </a>
        </div>
      </h4>
    </div>

    <div id="PanelCriteriosBusqueda" name="PanelCriteriosBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
      <div class="panel-body">
        <div class="col-md-4">
          <div class="form-inline row hide">
              <div class="col-md-3">
                  <span><label>Dependencia: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioDependeciaGeneral('selectRazonSocial', 'selectUnidadNegocio')" multiple="true"></select>
              </div>
          </div>

          <br>

          <div class="form-inline row">
              <div class="col-md-3" style="vertical-align: middle;">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio[]" class="form-control selectUnidadNegocio" multiple="multiple" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')">                    
                  </select>
              </div>
          </div>
          <br>
          <div class="form-inline row">
              <div class="col-md-3" style="vertical-align: middle;">
                  <span><label>UE: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora"> 
                      <option value="-1">Seleccionar...</option>
                  </select>
              </div>
          </div>
          <br>
          <component-number-label label="No. Compromiso:" id="txtNoCompromiso" name="txtNoCompromiso" value="" placeholder="No. Compromiso" title="No. Compromiso"></component-number-label>
          <br>
          <component-number-label label="No. Devengado:" id="txtNoDevengado" name="txtNoDevengado" value="" placeholder="No. Devengado" title="No. Devengado"></component-number-label>
        </div>
        
        <div class="col-md-4 pt20">
      <div class="form-inline row">
        <div class="col-md-3 col-xs-12">
          <span><label>Proveedor: </label></span>
        </div>
        <div class="col-md-9 col-xs-12">
          <input type="text" id="txtProv" name="txtProv" placeholder="Proveedor" title="" onkeyup="" onkeypress="" maxlength="100" onpaste="return false" class="form-control" style="width: 100%;">
        </div>
      </div>
      <br>
            <div class="form-inline row">
                <div class="col-md-3" style="vertical-align: middle;">
                    <span><label>Operación: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectOperacionTesoreria" name="selectOperacionTesoreria[]" class="form-control selectOperacionTesoreria" multiple="multiple">
                    </select>
                </div>
            </div>
            <br>
            <component-number-label label="Folio Cheque:" id="txtReciboNo" name="txtReciboNo" value="" placeholder="Folio Cheque" title="Folio Cheque"></component-number-label>
            <br>
            <component-number-label label="Folio Transferencia:" id="txtReciboNoTransferencia" name="txtReciboNoTransferencia" value="" placeholder="Folio Transferencia" title="Folio Transferencia"></component-number-label>
            <!-- <div class="input-group">           
              <span class="input-group-addon" style="background: none; border: none;"><b> Estatus: </b></span>
              <select id="selectEstatusGeneral" name="selectEstatusGeneral[]" class="form-control selectEstatusGeneral" multiple="multiple" data-funcion="<?= $funcion ?>"></select>
            </div>-->
            <!--  <component-text-label label="Estatus:" id="txtEstatus" name="txtEstatus" placeholder="Estatus" title="txtEstatus"></component-text-label>-->
        </div>
    <div class="col-md-4 pt20">
      <component-date-label label="Desde:" id="dateDesde" name="dateDesde" placeholder="Desde fecha" title="DesdeFecha"></component-date-label>
      <br>
      <component-date-label label="Hasta:" id="dateHasta" name="dateHasta" placeholder="Hasta fecha" title="HastaFecha"></component-date-label>
      <br>
      <div class="form-inline row">
        <div class="col-md-3" style="vertical-align: middle;">
          <span><label>Estatus: </label></span>
        </div>
        <div class="col-md-9">
          <select class="estatusTesoSelect" id="estatusTesoSelect" name="estatusTesoSelect">
            <option value="-2">Seleccionar</option>
            <!-- <option value="-1">Todos</option> -->
            <option value="0">Pendiente de pago</option>
            <option value="1">Programado</option>
            <option value="2">Autorizado</option>
            <option value="3">Pagado</option>
          </select>
        </div>
      </div>
    </div>
       <br> <br>
      </div>
        <!--<div class="panel-footer">-->
         <!-- <div class="col-xs-6 col-md-6 text-right">
           <component-button id="btnBuscar" name="btnBuscar" value="Filtrar" onclick="fnBuscarDatos(1)" class="glyphicon glyphicon-search"></component-button>
             </div>-->
        <!--</div>-->
   <!-- </div>
</div>-->


                    <!--f-->
                 <div class="col-xs-12 col-md-12 text-center">
                      <br>
                            <component-button id="btnBuscar" name="btnBuscar" value="Filtrar" onclick="fnBuscarDatos()" class="glyphicon glyphicon-search"></component-button>
                            <br>
                    </div>
                    <!--buscar-->



                </div>
                </div><!--fin pannel criterios de busqueda -->


                <!-- Bandeja de entrada -->
        
                <!-- pagos-->
                <input type="hidden" id ="idpp" name="idpp" value="">
                <br>
                 <div id="OperacionMensaje" name="OperacionMensaje"> </div>

              

                 <div name="divTabla" id="divTabla">        
                    <div name="divDatos" id="divDatos"></div>
                </div>

                  <div id="menuAcciones" class="col-xs-12 col-md-12 text-center"> 
                    <br>
                    <component-button id="btnProgramarPago" name="btnProgramarPago" value="Programar Pago" onclick="fnProgramarPagoModal()" class="glyphicon glyphicon-calendar" > </component-button>

                    <component-button id="btnProgramarPago" name="btnProgramarPago" value="Reprogramar Pago" onclick="fnReProgramarPagoModal()" class="glyphicon glyphicon-edit"> </component-button>
                        
                    <component-button id="btnAutorizador" name="btnAutorizador" value="Autorizar" onclick="fnAutorizar()" class="glyphicon glyphicon-flag"> </component-button>

                    <component-button id="btnPagar" name="btnPagar" value="Pagar" onclick="fnPagarModal()" class=" glyphicon glyphicon-usd"> </component-button>
                    
                    <?php if (Havepermission($_SESSION['UserID'], 2399, $db) == 1) : ?>
                        <component-button id="btnReversareversar" name="btnReversareversar" value="Rechazar" onclick="fnOperacionesReversa()" class="glyphicon glyphicon-arrow-left"> </component-button>
                    <?php endif ?>
                  
                 <!-- <component-button id="btnVerificarRadicado" name="btnVerificarRadicado" value="Verificar radicado" onclick="" class=" glyphicon glyphicon-usd"> </component-button>-->

                </div><br> 

               <!--fin datos pagos-->
                <br>
              <!--panel totales -->
               <div id="pagosTotales" clas="col-md-12">
               <!-- <div clas="col-xs-12 col-md-12"> <h3>Totales </h3> </div>-->
                <div class="col-md-4"> <!--col total monto -->
                <br>
                    <!--<div class="input-group input-group-lg">
                        <span class="input-group-addon" id="sizing-addon1">Subtotal $</span>
                        <label class="form-control" id="lbTotalMonto" name="lbTotalMonto"> </label>
                        <br>

                        <!--  <component-label-text label="Total Monto:" id="lbTotalMonto" name="lbTotalMonto" value="" title="Total Monto 1"></component-label-text>-->

                   <!-- </div>-->
                 <br>
                </div> <!--fin col total monto -->

                <div class="col-md-4"><!-- col total iva monto -->
                <br>
                   <!-- <div class="input-group input-group-lg">
                        <span class="input-group-addon" id="sizing-addon1">IVA $</span>
                        <label class="form-control" id="lbTotalIva" name="lbTotalIva"> </label>
                        <br>
                    </div>
                 <br>
                </div>--> <!--fin col total iva monto -->

                <div class="col-md-4"><!-- col total iva monto -->
                <br>
                    <div class="input-group input-group-lg">
                        <!--<span class="input-group-addon" id="sizing-addon1">Total (Subtotal + IVA) $</span>-->
                        <!-- <span class="input-group-addon" id="sizing-addon1">Total $</span>
                        <label class="form-control" id="lbTotal" name="lbTotal"> </label>
                        <br>-->
                    </div>
                 <br>
                </div> <!--fin col total iva monto -->

                </div> <!-- fin  totales-->
                <br>


              <!--panel totales aplicados-->
              <!-- rem      <div id="totalesAplicados" clas="col-md-12">
                    <h4>Aplicados </h4>
                    <div class="col-md-4">
                    <br>
                        <div class="input-group input-group-lg">
                            <span class="input-group-addon" id="sizing-addon1">Total Monto$</span>
                            <label class="form-control" id="lbTotalMontoFact" name="lbTotalMontoFact"> </label>
                            <br>
    
                        </div>  
                        <br>                   
                    </div><!--fin col total monto -->

               <!-- rem  <br>
                <div class="col-md-4"><!-- col total iva monto -->
              <!-- rem  <br>
                    <div class="input-group input-group-lg">
                        <span class="input-group-addon" id="sizing-addon1">Total IVA$</span>
                        <label class="form-control" id="lbTotalIvaFact" name="lbTotalIvaFact"> </label>
                        <br>
                    </div>
                 <br>
                </div> <!--fin col total iva monto -->
              <!-- rem  <br>
                <div class="col-md-4"><!-- col total iva monto -->
            <!-- rem    <br>
                    <div class="input-group input-group-lg">
                        <span class="input-group-addon" id="sizing-addon1">Total Pago(Monto + IVA)$</span>
                        <label class="form-control" id="lbTotalFact" name="lbTotalFact"> </label>
                        <br>
                    </div>
                 <br>
                </div> <!--fin col total iva monto --> 

            <!-- rem    </div> <!-- fin panel de totales aplicados-->
            <!-- rem    <br>

                 <!--panel totales  pendientes-->
             <!-- rem   <div id="totalesPendientes" clas="col-md-12">
                    <h4>Pendientes </h4>
                    <div class="col-md-4"> <!--col total monto -->
               <!-- rem     <br>
                    
                    <div class="input-group input-group-lg">
                        <span class="input-group-addon" id="sizing-addon1">Total Monto$</span>
                        <label class="form-control" id="lbTotalMontoPendiente" name="lbTotalMontoPendiente"> </label>
                        <br>
                    </div>
                    <br>
                    </div> <!--fin col total monto -->

                <!-- rem    <div class="col-md-4"><!-- col total iva monto -->
               <!-- rem     <br>
                    <div class="input-group input-group-lg">
                        <span class="input-group-addon" id="sizing-addon1">Total IVA$</span>
                        <label class="form-control" id="lbTotalIvaPendiente" name="lbTotalIvaPendiente"> </label>
                        <br>
                    </div>
                    <br>
                    </div> <!--fin col total iva monto -->

                <!-- rem    <div class="col-md-4"><!-- col total iva monto -->
              <!-- rem      <br>
                    <div class="input-group input-group-lg">
                        <span class="input-group-addon" id="sizing-addon1">Total Pago(Monto + IVA)$</span>
                        <label class="form-control" id="lbTotalPendiente" name="lbTotalPendiente"> </label>
                        <br>
                    </div>
                    <br>
                    </div> <!--fin col total iva monto -->
             <!-- rem   </div> <!-- fin panel de totales pendientes-->

                </div><!-- fin bandeja de entrada tab-->
                



    <!--
<div id="subirCsv" class="tab-pane fade">
    <br>
    <h3>Administración de archivos CSV</h3> 
       
         <br>-->
<!--<component-administrador-archivos funcion="244"  tipo="20" trans="4" idcomponente="" esmultiple="0"> </component-administrador-archivos>-->

<!--
<br/> <br/>

</div>--> <!-- fin tab de carga de CSV-->





</div><!-- si quito este no me da las rayas-->

    
<div id="subidosCLC" class="tab-pane fade">
    <br>
    <h3>Administración de CLCs</h3> 
       
         <br>
<!--<component-layouts-generados id="ad1" funcion="244" tipo="20" trans="all" > </component-layouts-generados>-->
<component-administrador-archivos funcion="244"  tipo="20" trans="5" idcomponente="" esmultiple="1"> </component-administrador-archivos>

<br/> <br/> <br/>

</div> <!-- fin tab de carga de CSV-->

  <!-- inicio de cancelacion de chueque-->
    <?php
    if ($visibleCheques==1) {
    ?>
  <div id="cancelarCheque" class="tab-pane fade">
   
      <!--<h3>Bandeja de entrada</h3>-->
            <br>
     <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelCriteriosBusqueda" aria-expanded="true" aria-controls="collapseOne">
            <b>Criterios de filtrado</b>
          </a>
        </div>
      </h4>
    </div>

    <div id="PanelCriteriosBusqueda" name="PanelCriteriosBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
      <div class="panel-body">
         <div class="col-xs-12 col-md-12"> 

        <div class="col-xs-12 col-md-4" id="bancoCancelar">  
          <!--
        <component-text-label label="Banco: " id="txtBanco" name="txtBanco" placeholder="Banco" maxlength="12"></component-text-label>-->
        </div>

         <div class="col-xs-12 col-md-4">  
        <component-text-label label="No. Cuenta: " id="txtCuenta" name="txtCuenta" placeholder="No. Cuenta" maxlength="12"></component-text-label>
        </div>

         <div class="col-xs-12 col-md-4">  
        <component-date-label label="Desde: " id="desdeCancelar" name="fechaCancelar" placeholder="Fecha" maxlength="12"></component-date-label>
        <br> 
         <component-date-label label="Hasta: " id="hastaCancelar" name="fechaCancelar" placeholder="Fecha" maxlength="12"></component-date-label><br><br>
        </div>
      
      </div>

      <div class="col-xs-12 col-md-12"> 
    
            <div class="col-xs-12 col-md-4">  
            <component-text-label label="Folio Cheque : " id="txtNoCheque" name="txtNoCheque" placeholder="No. Cheque" maxlength="12"></component-text-label>
            </div>

      </div>

      <div class="col-xs-12 col-md-12"> 
 
           <!-- <div class="col-xs-12 col-md-4">  
            <component-textarea-label label="Observaciones: " id="txtObsCancelar" name="txtObsCancelar" placeholder="Observaciones" maxlength="12"></component-textarea-label>
            </div>-->
            
      </div>

      <div class="col-xs-12  col-md-12"> 
          <div class="col-xs-12  col-md-4"> 
           <!-- <component-button id="btnAutorizador" name="btnAutorizador" value="Imprimir" onclick=""> </component-button>-->
            </div>
             <div class="col-xs-12  col-md-4"> 
            <!--<component-button id="btnAutorizador" name="btnAutorizador" value="Autorizar" onclick=""> </component-button>-->
            </div>

            

       </div>
        
      </div>
    


                    <!--f-->
                 <div class="col-xs-6 col-md-6 text-right">
                      <br>
                            <component-button id="btnBuscar1" name="btnBuscar1" value="Filtrar" onclick="fnChequeBuscar()" class="glyphicon glyphicon-search"></component-button>
                    </div>
                    <!--buscar-->



                </div>
                </div><!--fin pannel criterios de busqueda -->
               <div class="col-xs-12 col-md-12" id="infoCancelarCheque" style="display:none;">  
                  <div id="tablaChequesCR">  
                    <div id="datosChequesCR"> </div>
                  </div>

                  <div class="col-xs-12 col-md-12 text-center">
                  <button class="btn botonVerde glyphicon glyphicon-trash" id="btnCancelarCheque"> Cancelar </button>  
                 <!-- <button class="btn botonVerde glyphicon glyphicon-trash" id="btnCancelarCheque"> Reposición </button> --> 
                  <button class="btn botonVerde glyphicon glyphicon-saved" id="btnGenerarCheque"> Generar folio cheque </button>  
                  
                  
                  </div>
                </div>

      </div> <!-- fin cancelacion-->
    <?php } ?>
  <!--<div id="cancelarCheque" class="tab-pane fade">
      <br>
      <h3>Cancelación de  cheque</h3> 
      <br>
      <br/> 
      <div class="col-xs-12 col-md-12"> 

        <div class="col-xs-12 col-md-4">  
        <component-text-label label="Banco: " id="txtBanco" name="txtBanco" placeholder="Banco" maxlength="12"></component-text-label>
        </div>

         <div class="col-xs-12 col-md-4">  
        <component-text-label label="No. Cuenta: " id="txtCuenta" name="txtCuenta" placeholder="No. Cuenta" maxlength="12"></component-text-label>
        </div>

         <div class="col-xs-12 col-md-4">  
        <component-date-feriado label="Fecha: " id="fechaCancelar" name="fechaCancelar" placeholder="Fecha" maxlength="12"></component-date-feriado>
        </div>
      
      </div>

      <div class="col-xs-12 col-md-12"> 
            <br>
            <div class="col-xs-12 col-md-4">  
            <component-text-label label="No. Cheque Cancelado: " id="txtCancelado" name="txtCancelado" placeholder="No. Cheque Cancelado" maxlength="12"></component-text-label>
            </div>

      </div>

      <div class="col-xs-12 col-md-12"> 
            <br>
            <div class="col-xs-12 col-md-4">  
            <component-textarea-label label="Observaciones: " id="txtObsCancelar" name="txtObsCancelar" placeholder="Observaciones" maxlength="12"></component-textarea-label>
            </div>
            
      </div>

      <div class="col-xs-12  col-md-12"> 
          <div class="col-xs-12  col-md-4"> 
            <component-button id="btnAutorizador" name="btnAutorizador" value="Imprimir" onclick=""> </component-button>
            </div>
             <div class="col-xs-12  col-md-4"> 
            <component-button id="btnAutorizador" name="btnAutorizador" value="Autorizar" onclick=""> </component-button>
            </div>

            

       </div>
    
  </div>--> <!-- fin cnacelacion de chueque-->


  <!--inicio cancelarCHOPTR -->
 <!-- <div id="cancelarCHOPTR" class="tab-pane fade">
      <br>
      <h3>Búsqueda</h3> 
      <br>
      <br/> 
      <div class="col-xs-12 col-md-12"> 

        <div class="col-xs-12 col-md-4">  
        <component-text-label label="Banco: " id="txtBanco" name="txtBanco" placeholder="Banco" maxlength="12"></component-text-label>
        </div>

         <div class="col-xs-12 col-md-4">  
        <component-text-label label="No. Cuenta: " id="txtCuenta" name="txtCuenta" placeholder="No. Cuenta" maxlength="12"></component-text-label>
        </div>

         <div class="col-xs-12 col-md-4">  
        <component-date-feriado label="Fecha: " id="fechaCancelar" name="fechaCancelar" placeholder="Fecha" maxlength="12"></component-date-feriado>
        </div>
      
      </div>

      <div class="col-xs-12 col-md-12"> 
            <br>
            <div class="col-xs-12 col-md-4">  
            <component-text-label label="No. Cheque : " id="txt" name="txt" placeholder="No. Cheque" maxlength="12"></component-text-label>
            </div>

            <div class="col-xs-12 col-md-4">  
            <component-text-label label="No. Operacion Pago : " id="" name="txt" placeholder="No. Cheque" maxlength="12"></component-text-label>
            </div>


             <div class="col-xs-12 col-md-4">  
            <component-text-label label="Transferencia: " id="" name="txt" placeholder="No. Cheque" maxlength="12"></component-text-label>
            </div>

      </div>

       <div class="col-xs-12 col-md-12"> 
             <div class="col-xs-12 col-md-6">  
        <component-date-label label="Fecha Desde: " id="fechaCancelar" name="fechaCancelar" placeholder="Fecha" maxlength="12"></component-date-label>
        </div>

           <div class="col-xs-12 col-md-6">  
        <component-date-label label="Fecha Hasta: " id="fechaCancelar" name="fechaCancelar" placeholder="Fecha" maxlength="12"></component-date-label>
        </div>
       </div>

      

      <div class="col-xs-12  col-md-12"> 
          <div class="col-xs-12  col-md-4"> 
            <component-button id="btnAutorizador" name="btnAutorizador" value="Consultar" onclick=""> </component-button>
            </div>    

       </div>
    
  </div>--> <!-- fin <!-- cancelarCHOPTR-->


    <!--inicio cancelarCHOPTR -->
  <div id="Movimientos" class="tab-pane fade">
      <br>
      <h3>Búsqueda</h3> 
      <br>
      <br/>

      <div class="col-xs-12 col-md-12"> 

       </div>
    
  </div> <!-- fin <!-- cancelarCHOPTR-->

</div>



<!-- modal  fin detalle info de pago del pago programando-->
<!--<div class="modal fade" id="ModalInfoCalendario" name="ModalInfoCalendario" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
    <div class="modal-content">
      <div class="navbar navbar-inverse navbar-static-top">
    
        <div class="col-md-12 menu-usuario">
          <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <div class="nav navbar-nav">
            <div class="title-header">
              <div id="ModalInfoCalendario_Titulo" name="ModalInfoCalendario_Titulo"></div>
            </div>
          </div>
        </div>
        <div class="linea-verde"></div>
      </div>
      <div class="modal-body" id="ModalInfoCalendario_Mensaje" name="ModalInfoCalendario_Mensaje">

         <div id="mensajesValidaciones" name="mensajesValidaciones"></div>
            <component-text-label label="UR: " id="txtURG" name="txtURG" placeholder="URG" maxlength="12"></component-text-label>
            <br>
             <component-textarea-label label="Observaciones: " id="txtObservaciones" name="txtObservaciones" placeholder="Observaciones"></component-textarea-label>
            <br>
            <component-text-label label="Factura: " id="txtReferencia" name="txtReferencia" placeholder="Factura"></component-text-label>
            <br>
            <component-text-label label="Proveedor: " id="txtProveedor" name="txtProveedor" placeholder="Proveedor"></component-text-label>
            <br>
            <component-text-label label="Monto: " id="txtMonto" name="txtMonto" placeholder="Monto"></component-text-label>
            <br>
            <component-text-label label="IVA: " id="txtIva" name="txtIva" placeholder="IVA"></component-text-label>
            <br>
           <component-text-label label="Total: " id="txtTotal" name="txtTotal" placeholder="Total"></component-text-label>
            <br>

           
      </div>
      <div class="modal-footer">

        <component-button type="button" id="btnCerrar" name="btnCerrar" data-dismiss="modal" value="Cerrar"></component-button>
      </div>
    </div>
  </div>
</div>-->
<!-- fin modal detalle info de pago del pago programando-->

<!--Programar pago-->

                <!-- pago a futuro
                <div id="OperacionMensaje" name="OperacionMensaje"> </div>
                Selecciona Chequera:
                <select name="BankAccount" id="BankAccount">
                <option value="" ></option>
                </select>
                <br>
                <span class="input-group-addon" style="background: none; border: none;"> Tipo pago: </span>-->
                <!--
                <select name="TipoPago" id="TipoPago">
                    <option value="Cheque" >Cheque</option>
                    <option value="Transferencia" >Transferencia</option>
                    <option value="Efectivo" >Efectivo</option>
                </select> -->
                <!--pago a futuro
                <select id="selectTipoPagoTesoreria" name="selectTipoPagoTesoreria[]" class="form-control selectTipoPagoTesoreria">
                          
                        </select>
                <br>

                 <component-text-label label="Número de Cheque: " id="numchequeuser" name="numchequeuser" placeholder="Número de Cheque" maxlength="255"></component-text-label>
                <br>

                <component-date-feriado label="Fecha pago :" id="FechaPago" name="FechaPago" placeholder="Fecha pago" title="Fecha pago"></component-date-label>
                <br>

                <component-text-label label="Concepto: " id="UnificarPagoDescripcion" name="UnificarPagoDescripcion" placeholder="Clave" maxlength="255"></component-text-label>
                <br>-->

                <!--<component-button id="btnProgramarPago" name="btnProgramarPago" value="Programar pago" onclick="fnProgramarPago()"></component-button>-->


<!---fin modal programar pago -->

<!-- modal  programar pago-->
<div class="modal fade" id="ModalProgramarPago" name="ModalProgramarPago" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="navbar navbar-inverse navbar-static-top">
        <!--Contenido Encabezado-->
        <div class="col-md-12 menu-usuario">
          <span class="glyphicon glyphicon-remove" data-dismiss="modal" id="btnCerrarIcono"></span>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <div class="nav navbar-nav">
            <div class="title-header">
              <div id="ModalProgramarPago_Titulo" name="ModalProgramarPago_Titulo"></div>
            </div>
          </div>
        </div>
        <div class="linea-verde"></div>
      </div>
      <div class="modal-body">
        <!--Mensajes Dentro del Modal-->
        <div id="mensajesValidaciones" name="mensajesValidaciones"></div>
        <!--Mensaje o contenido-->
        <div class="col-xs-12 col-md-6">
          <component-date-feriado label="Fecha pago:" id="FechaPago" name="FechaPago" placeholder="Fecha pago" title="Fecha pago"></component-date-feriado>
        </div>
        <div class="col-xs-12 col-md-6">
          <component-text-label label="Referencia:" id="txtReferenciaProgramar" name="txtReferenciaProgramar" placeholder="Referencia" title="Referencia" value=""></component-text-label>
        </div>
        <br><br>
      </div>
      <div class="modal-footer">
        <!-- <div class="col-xs-8 col-md-8 text-right">  -->
        <component-button type="button" id="btnFechaPago" name="btnFechaPago" onclick="fnProgramarPago()" value="Programar pago" class="glyphicon glyphicon-calendar"></component-button>
        <component-button type="button" id="btnCerrar" name="btnCerrar" data-dismiss="modal" value="Cerrar"></component-button>
        <!-- </div> -->
      </div>
    </div>
  </div>
</div>
<!-- fin programar pago -->

<!--evento de pago -->
<div class="modal fade" id="ModalAutorizar" name="ModalAutorizar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
    <div class="modal-content">
      <div class="navbar navbar-inverse navbar-static-top">
        <div class=" menu-usuario">
          <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <div class="nav navbar-nav">
            <div class="title-header">
              <div id="ModalAutorizar_Titulo" name="ModalAutorizar_Titulo"></div>
            </div>
          </div>
        </div>
        <div class="linea-verde"></div>
      </div>
      <div class="modal-body" id="ModalInfoPago_Mensaje" name="ModalInfoPago_Mensaje" style="overflow-x:scroll;overflow-y:scroll;">
        <div id="mensajesValidacionesAutorizar" name="mensajesValidacionesAutorizar"></div>
        <div>
          <input type="hidden" value=""  id="bankAccount" name="bankAccount[]" />
          <input type="hidden" value=""  id="tipocambio" name="tipocambio"/>
          <input type="hidden" value=""  id="selMovimiento1" name="selMovimiento1[]"/>
          <input type="hidden" value ="" id="saldo" name="saldo[]"/>
          <input type="hidden" value="" id="transno" name="transno[]"/>
          <input type="hidden" value=""  id="status" name="status[]"/>
          <input type="hidden" value=""  id="supplierid"  name="supplierid[]"/>
          <input type="hidden" value=""  id="tagref" name="tagref[]"/>
          <input type="hidden" value=""  id="rate"  name="rate[]"/>
          <input type="hidden" value=""  id="idfactura" name="idfactura[]"/>
          <input type="hidden" value=""  id="diffonexch" name="diffonexch[]"/>
          <input type="hidden" value=""  id="foliorefe" name="foliorefe[]"/>
          <input type="hidden" value=" "  id="numchequeuser" name="numchequeuser"/>
          <div class="col-xs-12 col-md-6" id="banco2">  
          </div>
          <div class="col-xs-12 col-md-6">
            <span style="font-size: 14px;">Tipo pago </span>
            <select id="selectTipoPagoTesoreria" name="selectTipoPagoTesoreria[]" class="form-control selectTipoPagoTesoreria">  
            </select>
            <br><br>
          </div>
          <div class="col-xs-12 col-md-6">
            <component-text-label label="Clave de Rastreo:" id="txtClaveRastreo" name="txtClaveRastreo" placeholder="Clave de Rastreo" title="Clave de Rastreo" value="" disabled="true"></component-text-label>
          </div>
          <br><br><br><br>
          <div id="ligaPoliza"> </div>
          <div id="botonTipoTransferencia"> </div>
          <div id="tablaUnificarPago"><br><br> 
            <div id="datosUnificarPago"> 
            </div>
          </div>
          <div id="verDocumentoTrans"> </div>
          <br>
        </div>
        <br><br>
      </div>
      <div class="modal-footer">
        <div class="col-xs-6 col-md-6 text-right"> 
        </div>
        <button class="btn bgc8" style="display: none !important; color:white; font-weight: bold; " id="btnUnificarPago" >Unificar y Autorizar</button>
        <button class="btn bgc8" style="color:white; font-weight: bold;" id="btnAutorizarPagos" name="btnAutorizarPagos"  onclick="autorizarPagos()" >Autorizar</button>
        <button class="btn bgc8" style="color:white; font-weight: bold;" id="btnCerrarPago" name="btnCerrarPago" data-dismiss="modal" >Cerrar</button>
      </div>
    </div>
  </div>
</div>
<!--fin evento de pago -->

    
        </div> 
    </div>
    </div><!-- fin del left -->




<div class="modal fade" id="ModalCancelarCh" name="ModalCancelarCh" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-xs" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
    <div class="modal-content">
      <div class="navbar navbar-inverse navbar-static-top">
       
        <div class=" menu-usuario">
          <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <div class="nav navbar-nav">
            <div class="title-header">
              <div id="ModalCancelarCh_Titulo" name="ModalCancelarCh_Titulo">
                
                <h3>
                  <p><i class="glyphicon glyphicon-info-sign text-danger" >
                </i> Información</p>
              </h3>

              </div>
            </div>
          </div>
        </div>
        <div class="linea-verde"></div>
      </div>
      <div class="modal-body" id="ModalCancelarCh_Mensaje" name="ModalCancelarCh_Mensaje">
    <div  >
         
              
    <div >
    <div id="tipoCancel">
      <h5>Tipo de  cancelación </h5>
        <select id="selectTypeCancel" class="form-control selectTypeCancel" name="selectTypeCancel">
            <option value="0">Seleccionar ..</option>
            <option value="1">Cancelación por reposición de cheque </option>
            <option value="2">Cancelación total </option>

            </select> </div><h5>Justificación de la cancelación del cheque </h5> <textarea class="form-control" id="textAreaJusCan" rows="6"> </textarea> <br>
    </div>
        <!-- fin contenido-->
      </div>
      <br> <br> <br>
      <div class="modal-footer">
      <!--contenido -->
    
      <div class="col-xs-6 col-md-6 text-right"> 
     

      <div class="row clear-fix text-right">

      <button class="btn  botonVerde glyphicon glyphicon-trash" id="btnJusCancel">Cancelar cheque</button>
      <button class="btn botonVerde "   name="btnCerrarPago" data-dismiss="modal" >Cerrar</button>
     
     </div>

      </div><!-- fin moddal -->

        </div>

        <!--fin contenido footer -->
      </div>
    </div>
  </div>
</div>

<script type="text/javascript" src="javascripts/layout_general.js"></script>

<script type="text/javascript">

</script>

<?php
require 'includes/footer_Index.inc';

?>