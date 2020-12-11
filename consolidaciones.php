<?php
/**
 * consolidadesciones
 *
 * @category     consolidaciones requisicion
 * @package      ap_grp
 * @author       Arturo Lopez Peña  <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 20/09/2017
 * Fecha Modificación: 04/10/2017
 * Se crea la consoliacion de las requisiciones
 */
$PageSecurity = 5;
require 'includes/session.inc';
$funcion = 2272;
$title= traeNombreFuncion($funcion,$db,'Consolidaciones');
//$title = _('');
//$tituloAlternativo='Consolidaciones';
require 'includes/header.inc';

require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
require 'javascripts/libreriasGrid.inc';
?>

<script type="text/javascript" src="javascripts/consolidaciones.js"></script>

<div id="OperacionMensaje" name="OperacionMensaje"></div>
<div align="center">
 
</div>



<!--Criterios de busqueda-->
<div class="panel panel-default" >
        <div class="panel-body" >

        <ul class="nav nav-tabs">
                        
                     <li class="active" ><a class="bgc10" data-toggle="tab" href="#crearconsolidaciones">Crear consolidaciones</a></li>
                     <!-- <li >
                        <a id ="" class="bgc10" data-toggle="tab" href="#consolidacionesHechas">Consolidaciones Hechas</a>
                      </li>-->
        </ul>
        <br>
<div class="tab-content">
        <div id="crearconsolidaciones" class="tab-pane active">

             <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title row">
                      <div class="" style="text-align: left;">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelCriteriosBusqueda" aria-expanded="false" aria-controls="collapse">
                               <span class="glyphicon glyphicon-chevron-down"></span> 
                               Consolidación de requisiciones
                            </a>
                        </div>
                       <!-- <div class="col-xs-12 col-md-12" style="text-align:center;">Artículos para consolidar </div>-->
                    </h4>
                </div><!--fin heading-->
                <div id="PanelCriteriosBusqueda" name="PanelCriteriosBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" style="">
                <br>
                <div class="panel-body">
                    <div class="row clearfix">
                    <div id="datosBusqueda" class="text-left col-xs-12 col-md-9">
                    
                    <div class="col-xs-12 col-md-3" >
                            <span><label> Partidas en requisiciones autorizadas: </label></span>
                        </div>
                        <div class="col-xs-12 col-md-9">
                            <select id="partidasSelect" name="partidasSelect" class="form-control">;

                            </select>
                        </div>
                </div> <!--fin datosBusquqeda-->
                <div class="text-left col-xs-12 col-md-3" > 

                  <button id="filtrarBtn" class="btn bgc8" style="color:#fff;"><span class="glyphicon glyphicon-search"> </span> Filtrar</button>
                </div>
              </div>
                
                   <!-- <div class="col-xs-3 col-md-3">
                      <span class="input-group-addon" style="background: none; border: none;"><b>Partidas:</b> </span>
                     </div>
                     <div class="col-xs-5 col-md-5"> 
                        <input type="text"  id="buscar-articulo-requisicion" placeholder="Artículo"  class="form-control input-sm" />
                    <input type="hidden" name="articuloAbuscar" id="articuloAbuscar" value="">
                     </div>
                     <div class="col-xs-4 col-md-4">    
                      <button id="buscarArticuloRequisiciones" class="btn bgc8" style="color:#fff;"><span class="glyphicon glyphicon-search"> </span> Filtrado</button>
                     </div>

                        <div id="sugerencia-articulo-requisicion"></div> 
                        <br>
                        <br>
                        
                        <div id="articuloBuscadoEnRequisiciones"> 
                        </div> <br>
                        
                      
                      <br>-->
            
                        
                        

            

              
                   
                  </div> <!-- fin panel body-->
                </div>
                </div><!--fin pannel criterios de busqueda -->

                <div class="col-md-12"> 

  <div name="divTablaArticulosParaConsolidar" id="divTablaArticulosParaConsolidar">
                <div name="divDatosRequisisiones" id="divDatosRequisisiones"></div>
              </div>
                <br>
               <button id="AgregarAPreConsolidacionBtn" class="btn bgc8" style="color:#fff; display: none;">Agregar a Consolidación</button> 

               <button id="guardarConsolidacion" class="btn bgc8" style="color:#fff; display: none;">Guardar Consolidación</button> 

              <button type="button" id="idBtnRegresarCR" name="btnRegresarCR" onclick="fnRegresarPanelRequisicion()" class="btn btn-default botonVerde glyphicon glyphicon-home">&nbsp;Regresar</button>

              <br><br>
              <div id="vistaConsolidadaPosible">
                <div id="datosPreConsolidadcion"> 

       <!--<table id="tablePreConsolidadcion" data-height="460">
        <thead>
            <tr>
                <th data-field="cantidad">Cantidad</th>
                <th data-field="articulo">codigo</th>
                <th data-field="descripcion">Descripcion</th>
            </tr>
        </thead>
      </table>--> 
    </div>
    </div>
</div>                 
</div>
     <div id="consolidacionesHechas" class="tab-pane fade"> 
       
        <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title row">
                        <div class="col-md-3 col-xs-3">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelCriteriosBusqueda" aria-expanded="false" aria-controls="collapse">
                               <span class="glyphicon glyphicon-chevron-down"></span> 
                               Búsqueda 
                            </a>
                        </div>
                    </h4>
                </div><!--fin heading-->
                <div id="PanelCriteriosBusqueda" name="PanelCriteriosBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" style="">
                <br>
                    <div id="datosBusqueda" style="font-size:14px; font-weight:bold;" class="row">
                    
                  <div class="container">
                    <div class="row"> 
                    <div class="col-xs-12 col-md-4" >
                        <component-date-label label="Fecha desde:" id="dateDesde" name="dateDesde" placeholder="Desde fecha" title="DesdeFecha"></component-date-label>
                    </div><!-- fin col -->

                    <div class="col-xs-12 col-md-4">
                    <component-date-label label="Fecha  hasta:" id="dateHasta" name="dateHasta" placeholder="Hasta fecha" title="HastaFecha"></component-date-label>
                    </div><!-- fin col -->

                    <div class="col-xs-12 col-md-4">
                        <component-text-label label="Partida:" id="txtPartida" name="txtPartida" placeholder="Partida" title="txtEstatus"></component-text-label>
                    </div>
                    </div>
                  <br/>
                

                   </div><!-- fin container-->
                    <br>
              
                    <div class="row"><br> <br> </div>
                      
                    </div> <!--fin datosBusquqeda-->

                    <!--f-->
                    <div class="col-xs-6 col-md-6 text-right">
                      <br>
                          <!--  <component-button id="btnBuscar" name="btnBuscar" value="Buscar" onclick="fnBuscarDatos(1)"></component-button>-->
                          <button id="btnFiltrarConsolidaciones" class="btn bgc8" style="color:#fff;" ><span class="glyphicon glyphicon-search"> </span> Filtrado</button>
                    </div>
                    <!--buscar-->



                </div>
                </div><!--fin pannel criterios de busqueda -->
        
         <div name="divTablaConsolidadas" id="divTablaConsolidadas">        
                    <div name="divDatosConsolidas" id="divDatosConsolidas">
                      
                    </div>
          </div>

        </div>                    
</div>

        
       
    

                  </div><!--no borrar es parte para pintar el pie de pagina-->
</div>


<div id="ModalGeneral1" name="ModalGeneral1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false" class="modal fade ui-draggable out" style="display: none; padding-left: 0px;">


    <div role="document" name="ModalGeneral1Tam" id="ModalGeneral1Tam" class="modal-dialog ui-draggable-handle modal-md"><div class="modal-content"><div class="navbar navbar-inverse navbar-static-top">

        <div class="col-md-12 menu-usuario"><span data-dismiss="modal" class="glyphicon glyphicon-remove"></span></div>

        <div id="navbar" class="navbar-collapse collapse"><div class="nav navbar-nav"><div class="title-header"><div id="ModalGeneral1_Titulo" name="ModalGeneral1_Titulo"><h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3></div></div></div></div> <div class="linea-verde"></div>

    </div> <div class="modal-body"><div id="ModalGeneral1_Advertencia" name="ModalGeneral1_Advertencia"></div> <div id="ModalGeneral1_Mensaje" name="ModalGeneral1_Mensaje">

    </div></div> <div class="modal-footer"><div id="ModalGeneral1_Pie" name="ModalGeneral1_Pie">            <div class="input-group pull-right">                <button class="btn btn-default botonVerde" onclick="" id="confirmacionModalGeneral1">Si</button>                <button class="btn btn-default botonVerde" data-dismiss="modal" id="btnCerrarModalGeneral1" name="btnCerrarModalGeneral1">No</button>            </div></div></div></div></div></div>
<?php
require 'includes/footer_Index.inc';
?>