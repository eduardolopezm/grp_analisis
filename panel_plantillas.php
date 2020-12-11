<?php
/**
 * Administracion de todos los layouts
 *
 * @category     Archivos
 * @package      ap_grp
 * @author       Arturo Lopez Peña  <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 22/11/2017
 * Fecha Modificación: 22/11/2017
 * Administracion de los layouts
 */

$PageSecurity = 5;
require 'includes/session.inc';

$funcion = 2311;
$title= traeNombreFuncion($funcion, $db);


require 'includes/header.inc';

require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
require 'javascripts/libreriasGrid.inc';

?>
<script type="text/javascript" src="javascripts/layout_general.js"></script>

<script type="text/javascript" src="javascripts/Subir_Archivos.js"></script>
<script type="text/javascript" src="javascripts/panel_plantillas.js"></script>




<div class="row"><!-- row1-->
<div class="col-xs-12 col-md-12"> 
 <div class="panel panel-default"><!-- Datos de la empresa -->
    <div role="tab" id="headingOne" class="panel-heading text-left">
        <h4 class="panel-title row">
            <div class="col-md-3 col-xs-3">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelPlantillas" aria-expanded="false" aria-controls="collapse" class="collapsed"><span class="glyphicon glyphicon-chevron-down"></span>
                    Criterios de filtrado
                </a>
            </div>
        </h4>
    </div>
    <div id="PanelPlantillas" name="PanelPlantillas" role="tabpanel" aria-labelledby="headingOne" class="panel-collapse collapse in"><br>

        <div class=" container">

          <div class="col-md-4">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Dependencia: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnTraeUnidadesResponsables(this.value, 'selectUnidadNegocio')"></select>
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
        </div>
        
        <div class="col-md-4">
           <!-- <component-number-label label="Tipo documento:" id="txtNumeroRequisicion" name="txtNumeroRequisicion" value=""></component-number-label>-->
            <span class="input-group-addon" style="background: none; border: none;"><b>Tipo documento:</b></span>
          <div id="comboDocumentos">  </div>
           <br>
            <!--<div class="input-group">
                <div class="col-md-3">            
                  <span class="input-group-addon" style="background: none; border: none;"><b>Solo Layouts: 
               
                  <input type="checkbox" name="eslayout" id="eslayout">
                </b></span>

                </div>
               
            </div>-->
        </div>
        <div class="col-md-4">
          <component-date-label label="Fecha desde:" id="dateDesde" name="dateDesde" placeholder="Desde fecha" title="DesdeFecha"></component-date-label>
          <br>
          <component-date-label label="Fecha  hasta:" id="dateHasta" name="dateHasta" placeholder="Hasta fecha" title="HastaFecha"></component-date-label>
        </div>
        
  </div>
        <!--<div class="panel-footer">-->
            <component-button  id="filtrar" name="filtrar" class="glyphicon glyphicon-search" onclick="fnfiltrar();" value="Filtrado"></component-button>
          
          <!--Inicio container-->
              
          <!--fin container-->


        <br>
        </div><!--container-->
</div>
</div>
</div><!--fin row 1-->

<div class="row text-center" > <br> 

   <div class="col-xs-12 col-md-3"> 

   </div>

  <!-- <div class="col-xs-12 col-md-9 text-left"> 
         
                <div class="col-xs-12 col-md-3"> 
                 <b> Selecciona Layout necesario:</b>
               </div>

              <div id="combo" class="col-xs-12 col-md-3"> 
              
              </div>

               <div id="" class="col-xs-12 col-md-3 text-center"> 
                <button id="descargarLayout" class="btn bgc8" style="color: #fff;" >Descargar Layout </button>
             </div>
    </div>-->

    

</div>



<div class="row text-left"><!--row2--><br> 
  <!--<div class="cargarArchivosComponente">-->
  
  <input id="esMultiple" name="esMultiple" value="1" type="hidden"> 
  <input name="componente" id="componenteArchivos" type="hidden"> 
  <input id="funcionArchivos" name="funcionArchivos" value="all" type="hidden">
  <input id="tipoArchivo" value="all" type="hidden"> 
  <input id="transnoArchivo" value="" type="hidden"> 
   
   <div id="mensajeArchivos">
   
   </div>
   <!--descarga de plantillas sin contenido-->
<!--<div id="subirArchivos" class="">
        <div class="col-md-12" style="color: rgb(255, 255, 255) !important;">
          
          <div class="">

          <div id="tipoInputFile">
            
          </div> 
         <button id="cuadroDialogoCarga" onclick="fnCargarArchivos()" class="btn bgc8">
          <span class="glyphicon glyphicon-file"></span> 
           Cargar archivo(s)                
         </button> 


          <br> <br>
           <button id="enviarArchivosMultiples" class="btn bgc8" style="display: none;">
           Subir
         </button> <br> <br>
       </div>

        <br>

      </div> 

        <div id="muestraAntesdeEnviar" class="col-md-12 col-xs-12">
          
        </div>

         <br> <br>

   </div>--> <!-- descarga de plantillas sin contenido-->
    

    

    <div name="divTablaArchivos" id="divTablaArchivos" class="col-md-12 col-xs-12">
        
        <div name="divDatosArchivos" class="col-md-12 col-xs-12" id="divDatosArchivos">
          
        </div>

    </div>
    <div class="modal fade" id="ModalBorrarArchivos"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
           <div class="modal-dialog" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
                 <div class="modal-content">
                     <div class="navbar navbar-inverse navbar-static-top">
                              <div class="col-md-lg menu-usuario">
                        <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
                    </div>
                    <div id="navbar" class="navbar-collapse collapse">
                        <div class="nav navbar-nav">
                            <div class="title-header">
                                <div id="ModalBorrarArchivos_Titulo" ></div>
                            </div>
                        </div>
                    </div>
                    <div class="linea-verde"></div>
                </div>
                <div class="modal-body" id="ModalBorrarArchivos_Mensaje">
                    <div class="col-md-9" id="listaBorrarArchivos" >
                        <h3>¿Desea borrar los archivos seleccionados?</h3>
                    </div>
                </div>
                <br> <br> <br>
                <div class="modal-footer">
                    <div class="col-xs-6 col-md-6 text-right">
                        <div id="procesandoPagoEspere"> </div> <br>


                        <button id="btnConfirmarEliminar" name="btnConfirmarEliminar" type="button" title="" class="btn btn-default botonVerde"  onclick="fnProcesosArchivosSubidos('eliminar','19')" >
                            Eliminar
                        </button>

                       <button id="btnCerrarConfirma" name="ElementoDefault" type="button" title="" onclick="" class="btn btn-default botonVerde" data-dismiss="modal" style="font-weight: bold;">&nbsp;Cancelar</button>



                    </div>
                </div>
            </div>
        </div>
    </div>
<!--</div>--><br><br>
 <div id="enlaceDescarga" class="col-md-12 col-xs-12 ">
       
     </div> 
 <div class="col-md-12 col-xs-12" id="accionesArchivos" style="color: rgb(255, 255, 255) !important; display: none;">

       <!--<div class="col-md-3"><button id="eliminarMultiples" onclick="fnBorrarConfirmaArch()" class="btn bgc8">Eliminar</button>
         <br>
       </div>-->
       <div class="col-md-3"><br>
          <button id="descargarMultiples" onclick="fnProcesosArchivosSubidos('descargar')" class="btn bgc8">Descargar</button> 
        <br><br>
      </div>
    </div>

</div><!-- fin row2-->
</div>
<?php
require 'includes/footer_Index.inc';
?>