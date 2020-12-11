<?php
/**
 * consumos a una fecha vista
 *
 * @category     vista de consumos a la fecha
 * @package      ap_grp
 * @author       Arturo Lopez Peña  <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 25/04/2018
 * Fecha Modificación: 25/04/2018
 *
 */
// ini_set('display_errors', 1);
//  ini_set('log_errors', 1);
//  error_reporting(E_ALL);

$PageSecurity = 2;
include 'includes/session.inc';
//$title = _('Mantenimiento Tipo de Gasto');
$funcion=2373;

$title= traeNombreFuncion($funcion, $db);
include 'includes/header.inc';
include( "includes/SecurityUrl.php");
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
include 'javascripts/libreriasGrid.inc';

$perfil='';
$contador=0;
$estatus=0;
$inicio='';
$fin='';
if(Havepermission($_SESSION ['UserID'], 2378, $db)==1) { //validador
    $perfil="capt";
   $contador++;
}elseif(Havepermission($_SESSION ['UserID'], 2380, $db)==1) {//autorizador
    $perfil="val";
    $contador++;
}elseif(Havepermission($_SESSION ['UserID'], 2390, $db)==1) {//almacenista
    $contador++;
    $perfil="aut";
}

$modificarfecha= Havepermission($_SESSION ['UserID'], 2494, $db);

if(isset($_GET["estatus"]) ){
    $estatus=$_GET["estatus"];
}
if(isset($_GET["estatus"]) ){
    $estatus=$_GET["estatus"];
}
if(isset($_GET["inicio"]) ){
    $inicio=$_GET["inicio"];
}
if(isset($_GET["fin"]) ){
    $fin=$_GET["fin"];
}


?>

<div class="row">
    <div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title row">
                <div class="col-md-3 col-xs-3 text-left">
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelCriteriosBusqueda" aria-expanded="true" aria-controls="collapseOne">
                        <b>Datos  PAAS</b>
                    </a>
                </div>
                 <div  class="fr text-right ftc7">
                        <span id="numeroFolio"></span>
                        
                </div>
            </h4>
           
        </div>
         <div id="PanelCriteriosBusqueda" name="PanelCriteriosBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
             <form id="criteriosFrm">
            <div class="panel-body text-left">
               <div class="form-inline row hide">
                      <div class="col-md-3">
                          <span><label>Dependencia: </label></span>
                      </div>
                      <div class="col-md-9">
                          <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioDependeciaGeneral('selectRazonSocial', 'selectUnidadNegocio')" multiple="true"></select>
                      </div>
                    
                      <input id="selectRazonSocial2" type="hidden" value="" name="selectRazonSocial2">
                </div>
                    <div class="row clearfix">
                        <div class="col-md-4" id="r0">
                           
                               
                                <div class="row form-inline">
                                    <div class="col-md-3 pt10" style="vertical-align: middle;">
                                        <input id="selectUnidadNegocio2" type="hidden" value="" name="selectUnidadNegocio2">
                                        <span>
                                            <label>UR: </label>
                                        </span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="selectUnidadNegocio" name="selectUnidadNegocio[]" class="form-control selectUnidadNegocio" multiple="multiple" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')"></select>
                                    </div>
                                 </div>

                            <br>
                            <div class="pt15">
                                <component-date-label label="Fecha inicio:" id="dateDesde" name="dateDesde" placeholder="Fecha inicio" title="DesdeFecha"></component-date-label>
                            </div>

                          
                        </div> <!-- .col-md-4 -->

                        <div class="col-xs-12 col-md-4" id="r1">

                             <div class="row form-inline">
                                    <div class="col-md-3 pt10" style="vertical-align: middle;">
                                        <span>
                                            <label>UE: </label>
                                        </span>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora">
                                            <option value="-1">Seleccionar...</option>
                                        </select>
                                    </div>
                                </div>
                                <br>
                                <div class="pt15">
                              <component-date-disable-next-year label="Fecha término:" id="dateHasta" name="dateHasta" placeholder="Fecha término" title="HastaFecha"></component-date-disable-next-year >
                                </div>
                            
                        </div>

                        <div class="col-xs-12 col-md-4" id="r2">
                            <!--<component-text-label label="Oficio:" id="numberFolio" name="numberFolio" placeholder="Oficio" title="Código" maxlength="10" value="" class="oficio" max="50"></component-text-label>-->
                            <div class="pt5 " style="font-size: 13px !important;">  
                             <div class="col-md-3 pt10" style="vertical-align: middle;">
                                        <span>
                                            <label>Oficio: </label>
                                        </span>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text"  class="form-control oficio" name="oficio" id="numberFolio" max="23">
                                    </div>
                            </div>
                             <div class="pt15 " style="font-size: 13px !important; height: 5px;">  

                             </div>

                            <div class="pt50 " style="font-size: 13px !important;">
                                <div class="col-md-3 col-xs-12"> 
                                 <span  style="background: none; border: none;"><b> Año:</b> </span>
                              </div>
                                <div class="col-md-9 col-xs-12">
                                <select id="selectAnio" name="selectAnio" class="form-control selectAnio">
                                </select>
                                </div>
                          </div>
  
                        </div>

                        

                    <div class="col-xs-12 col-md-12"> 
                        <br>
                        <div id="commentsDiv" class="pt20">
                            <div class="col-md-12 col-xs-12">
                                <label class=""><b>Observaciones:</b> </label>
                            </div>
                                <div class="obs-container pr25">
                                    <div class="row">
                                       
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <component-textarea label="Observaciones: " id="txtAreaObs" name="txtAreaObs" rows="5" cols="" class="w100p" placeholder="Observaciones" ></component-textarea>
                                        </div>
                                    </div>
                                </div>

                           
                        </div>

                    </div>
                        <br>


                    </div>
                    <!--  fin panel body-->
                    
            </div>
        </div>
    </form>
        <!-- fin panel-->


      
                    
    </div>


    <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
        <h4 class="panel-title row">
            <div class="col-md-3 col-xs-3 text-left">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelAddarchivo" aria-expanded="true" aria-controls="collapseOne">
                    <b>Agregar archivos</b>
                </a>
            </div>
            <div  class="fr text-right ftc7">
                <span id="numeroFolio"></span>

            </div>
        </h4>

    </div>
    <div id="PanelAddarchivo" name="PanelAddarchivo" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">

        <div class="panel-body text-left">
            
            <!-- begin upload file -->
                      <div class="col-xs-12 col-md-12 pt20">  
                         <div class="soloCargarArchivos" id="uploadFilesDiv"> 
                            <input type="hidden" id="esMultiple" name="esMultiple" value="0">
                            <input type="hidden" value="" name="componente" id="componenteArchivos"/>
                            <input type="hidden" value="2373" id="funcionArchivos" name="funcionArchivos"/>
                            <input  type="hidden"  value="285" id="tipoArchivo"/>
                            <input  type="hidden"  value="" id="transnoArchivo"/>
                              <input  type="hidden"  value="" id="numberScene"/>
                            <div id="mensajeArchivos"> </div>
                            <div  id="subirArchivos"  class="col-md-12">
                                <div  style="color:#fff !important;">
                                    <div class="col-md-6">
                                        <div id="tipoInputFile"> </div> <!-- Set type to upload one or many files -->
                                        <!--<input type="file"  class="btn bgc8"  :name="archivos','[]"  id="cargarMultiples"  multiple="multiple"  style="display: none;"/>-->
                                        <button  class="btn bgc8" id="btnUploadFile" onclick="">
                                            <span class="glyphicon glyphicon-file"></span>
                                            Cargar oficio(s)
                                        </button >

                                          <button id="descargarMultiples" onclick="fnProcesosArchivosSubidos('descargar')" class="btn bgc8" style="display: none;">Descargar</button> 
                                        <!-- -->

                                        <!-- -->
                                        <br>
                                        <br/>
                                        <!--<button id="enviarArchivosMultiples" class="btn bgc8" style="display: none;" >Subir</button>-->
                                        <br/>
                                        <br/>
                                    </div>
                                    <br>
                                </div>
                                
                                <div id="muestraAntesdeEnviar" class="" style="display: none;"> <!-- show files upload -->

                                    <table class="table table-striped border" id="tablaDetallesArchivos" style="border:solid 1px #eee;">
                                        <thead class="bgc8 text-center" style="color:#fff;">
                                            <th class="text-center">Nombre</th>
                                            <th class="text-center">Tamaño</th>  
                                            <th> </th> 
                                        </thead>
                                          <tbody>
                                         </tbody>
                                        </table>

                                </div>
                                <br/> <br/>
                            </div>
                        
                        </div>
                        
                    


                     <div name="tablaArchivosPaaas" id="tablaArchivosPaaas" class="col-md-12 col-xs-12">
                          <div name="divDatosArchivosPaaas" class="col-md-12 col-xs-12" id="divDatosArchivosPaaas"></div>
                        </div>
                    </div> <!--end  upload  file -->


        </div>

    </div>
</div>
    <!--- fin  row-->
    <?php if(isset($_GET["folio"])) {?>



        <div class="panel panel-default" id="panelDetailDiv" style="display: none;">
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title row">
                <div class="col-md-3 col-xs-3 text-left">
                    <button class="glyphicon glyphicon-info-sign btn btn-default botonVerde" id="montosPorAsignarBtn"> </button>
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelDetail" aria-expanded="true" aria-controls="collapseOne">
                        <b>Detalle partidas agregadas</b>

                    </a>

                </div>
                <div  class="fr text-right ftc7">
                    <span id="numeroFolio"></span>

                </div>
            </h4>

        </div>
        <div id="PanelDetail" name="PanelDetail" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">

                <div class="panel-body text-left">
                    <div class="form-inline row hide">
                    </div>
                     <div class="col-md-12">
                    <div   style="overflow-y: scroll; overflow-x: scroll; max-height: 420px;">
                     
                     <div class="col-xs-12 col-md-12"> 

                     <div class="col-md-4"> 

                     <component-text-label label="Partida Seleccionada:" id="partidaSelDeta" name="" 
                     placeholder="Presupuesto por asignar" title="Presupuesto" maxlength="10" value="" readonly="true" class="text-left"></component-text-label>
                    </div>

                      <div class="col-md-4"> 
                     <component-text-label label="Presupuesto por asignar:" id="montoDetallePorAsignar" name="" 
                     placeholder="Presupuesto por asignar" title="Presupuesto" maxlength="10" value="" readonly="true" class="text-left"></component-text-label>
                    </div>


                    <div class="col-md-4"> 
                     <component-text-label label="Presupuesto asignado:" id="montoDetalleAsignado" name="" placeholder="Presupuesto asignado" title="Presupuesto" maxlength="10" value="" readonly="true" class="text-left"></component-text-label>
                      </div>

                    </div>

                    <table class="table table-striped table-bordered table-hover" id="assetsTableDetails">
                    <thead class="bgc8 ftc2 text-center" style="vertical-align:middle; font-size: 12px;">
                     <th >
                        
                      <!--  <button class="btn btn-xs" style="color: #000; text-align: center;" onclick="" id="addBien" type="button"><span class="glyphicon glyphicon-plus"  ></span></button>-->

                    </th> 
                     <th>  </th> 
                    <th class="text-center"> CLAVE CUCOP </th>
                    <th class="text-center"> DESCRIPCIÓN </th>
                    <th class="text-center"> PARTIDA </th>
                    <!--<th class="text-center"> PEF </th>-->
                    
                    
                    <th class="text-center"> VALOR ESTIMADO (SIN IVA)  </th>
                    <th class="text-center"> % IVA  </th>
                    <th class="text-center"> VALOR BRUTO  </th>
                    <th class="text-center"> VALOR MIPyMES   </th>
                    <th class="text-center"> VALOR NCTLC    </th>
                    <th class="text-center"> CANTIDAD    </th>
                    <th class="text-center"> UNIDAD MEDIDA   </th>
                    <th class="text-center"> CARACTER PROCEDIMIENTO  </th>
                    <th class="text-center"> ENTIDAD FEDERATIVA </th>
                    <th class="text-center"> PORCENTAJE 1ER TRIMESTRE    </th>
                    <th class="text-center"> PORCENTAJE 2DO TRIMESTRE    </th>
                    <th class="text-center"> PORCENTAJE 3ER TRIMESTRE    </th>
                    <th class="text-center"> PORCENTAJE 4TO TRIMESTRE    </th>
                    <th class="text-center"> FECHA REGISTRO  </th>
                    <th class="text-center"> PLURIANUAL  </th>
                    <th class="text-center"> AÑOS PLURIANUALES   </th>
                    <th class="text-center"> VALOR TOTAL PLURIANUAL  </th>
                    <th class="text-center"> CLAVE PROGRAMA FEDERAL  </th>
                    <th class="text-center"> FECHA INICIO OBRA   </th>
                    <th class="text-center"> FECHA FIN OBRA  </th>
                    <th class="text-center">    TIPO PROCEDIMIENTO  </th>

                </thead>

                <tbody>

                </tbody>
                        
                </table>

           <!--      <table class="table table-striped table-bordered table-hover"  id="servicesTableDetails">
                     <thead class="bgc8 ftc2 text-center" style="vertical-align:middle; font-size: 12px;">
                    <th >
                        <!--
                        <button class="btn btn-xs" style="color: #000; text-align: center;" onclick="" id="addBien" type="button"><span class="glyphicon glyphicon-plus"  ></span></button>-->
<!-- 
                    </th>
                    <th>  </th>
                    <th class="text-center"> CLAVE CUCOP </th>
                    <th class="text-center"> DESCRIPCIÓN </th>
                    <th class="text-center"> PARTIDA </th>
                    <th class="text-center"> PEF </th>
                    <th class="text-center"> VALOR ESTIMADO  </th>
                    <th class="text-center"> VALOR MIPyMES   </th>
                    <th class="text-center"> VALOR NCTLC    </th>
                    <th class="text-center"> CANTIDAD    </th>
                    <th class="text-center"> UNIDAD MEDIDA   </th>
                    <th class="text-center"> CARACTER PROCEDIMIENTO  </th>
                    <th class="text-center"> ENTIDAD FEDERATIVA </th>
                    <th class="text-center"> PORCENTAJE 1ER TRIMESTRE    </th>
                    <th class="text-center"> PORCENTAJE 2DO TRIMESTRE    </th>
                    <th class="text-center"> PORCENTAJE 3ER TRIMESTRE    </th>
                    <th class="text-center"> PORCENTAJE 4TO TRIMESTRE    </th>
                    <th class="text-center"> FECHA REGISTRO  </th>
                    <th class="text-center"> PLURIANUAL  </th>
                    <th class="text-center"> AÑOS PLURIANUALES   </th>
                    <th class="text-center"> VALOR TOTAL PLURIANUAL  </th>
                    <th class="text-center"> CLAVE PROGRAMA FEDERAL  </th>
                    <th class="text-center"> FECHA INICIO OBRA   </th>
                    <th class="text-center"> FECHA FIN OBRA  </th>
                    <th class="text-center">    TIPO PROCEDIMIENTO  </th>

                </thead>

                <tbody>

                </tbody>
                        
                </table>  -->
                    <!--  fin panel body-->
                    </div>
                </div>
                </div>
        </div>
  </div>

        <!-- fin panel-->
        <!-- fin panel-->

           <div class="row" style=" height: 50px;"> 
                <!-- only use for get space after PANEL-->
        </div>

        <!-- add more partidas-->
      <div class="panel panel-default" id="panelAddPArtidas1" style="display: none;">
    <div class="panel-heading" role="tab" id="headingOne">
        <h4 class="panel-title row">
            <div class="col-md-3 col-xs-3 text-left">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelAddPartida" aria-expanded="true" aria-controls="collapseOne">
                    <b>Agregar partidas</b>
                </a>
            </div>
            <div  class="fr text-right ftc7">
                <span id="numeroFolio"></span>

            </div>
        </h4>

    </div>
    <div id="PanelAddPartida" name="PanelAddPartida" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">

        <div class="panel-body text-left">


   <div class="col-md-12" style="width:100%"> 
    
  <div class="row" style=" height: 50px;"> 
                <!-- only use for get space after PANEL-->
                <input type="hidden" id="presupuestoPorAsignar">
                <input type="hidden" id="presupuestoAsignado">
                <input type="hidden" id="pefAnterior">
                
  </div>
   <div class="" >

        <ul class="nav nav-tabs">

    <li class="active" style="min-width: 150px;max-width:150px; font-size: 13px; display: none;" id="tabAssets" ><a class="bgc10" data-toggle="tab" href="#bienes">Bienes</a></li>
            <li  style="min-width: 150px;max-width:150px; font-size: 13px; " id="tabServices">
               <a id ="" class="bgc10" data-toggle="tab" href="#memoria">Servicios</a>
             </li>
        </ul>
        <br>
        <div class="tab-content col-md-12">
            <div id="bienes" class="tab-pane active" style="display: none;">
            <div class="col-xs-12 col-md-12"> 
                <div class="col-md-4">  

                <div class="col-md-3 col-xs-12"> 
                    <span  style="background: none; border: none;"><b>Partida específica:</b> </span>
                </div>

                    <div class="col-md-9 col-xs-12" >
                        <select id="partidasAssets" name="partidasAssets" class="form-control partidasAssets">
                        </select>
                    </div>
                
                </div>
                <div class="col-md-4">  
                    
                         <component-text-label label="Presupuesto por asignar:" id="porAsignarBieneTxt" name="porAsignarBienes" placeholder="Presupuesto por asignar" title="Presupuesto" maxlength="10" value="" readonly="true" class="text-left"></component-text-label>
                         <input type="hidden" name="" val="" id="presuBienOriginal">
                </div>

                 <div class="col-md-4">  
                    
                         <component-text-label label="Presupuesto asignado:" id="asignadoBienesTxt" name="asignadoBienesTxt" placeholder="Presupuesto asignado" title="Presupuesto" maxlength="10" value="" readonly="true" class="text-left"></component-text-label>
                         <input type="hidden" name="" val="" id="presuBienOriginal">
                </div>
                
            </div>
              <div class="col-xs-12 col-md-12 text-center"> 

                 
                 <component-button type="button" id="showAssets" name="showAssets" class="glyphicon glyphicon-search" onclick="return false;" value="Mostrar"></component-button>
       

            </div>

             <div class="row" style=" height: 50px;"> 
                <!-- only use for get space after table-->
            </div>

            <div class="col-md-12">
            <div class="" style="overflow-y: scroll; overflow-x: scroll; max-height: 420px;" id="assetsDiv" style="display: none;"><!-- -->

            <table class="table table-striped table-bordered table-hover" id="tablaBienes">
             
                <thead class="bgc8 ftc2 text-center" style="vertical-align:middle; font-size: 12px;">
                    <!--<th >
                        
                        <button class="btn btn-xs" style="color: #000; text-align: center;" onclick="" id="addBien" type="button"><span class="glyphicon glyphicon-plus"  ></span></button>-->

                   <!-- </th>-->
                    <th>  </th>
                    <th class="text-center"> CLAVE CUCOP </th>
                    <th class="text-center"> DESCRIPCIÓN </th>
                  <!--   <th class="text-center"> UR  </th>
                    <th class="text-center"> UE  </th>
                    <th class="text-center"> NÚMERO OFICIO   </th> -->
                    <th class="text-center"> PARTIDA </th>
                    <!--<th class="text-center"> PEF </th>-->
                    
                    <th class="text-center"> VALOR ESTIMADO (SIN IVA) </th>
                      <th class="text-center"> % IVA  </th>
                    <th class="text-center"> VALOR BRUTO  </th>
                    <th class="text-center"> VALOR MIPyMES   </th>
                    <th class="text-center"> VALOR NCTLC    </th>
                    <th class="text-center"> CANTIDAD    </th>
                    <th class="text-center"> UNIDAD MEDIDA   </th>
                    <th class="text-center"> CARACTER PROCEDIMIENTO  </th>
                    <th class="text-center"> ENTIDAD FEDERATIVA </th>
                    <th class="text-center"> PORCENTAJE 1ER TRIMESTRE    </th>
                    <th class="text-center"> PORCENTAJE 2DO TRIMESTRE    </th>
                    <th class="text-center"> PORCENTAJE 3ER TRIMESTRE    </th>
                    <th class="text-center"> PORCENTAJE 4TO TRIMESTRE    </th>
                    <th class="text-center"> FECHA REGISTRO  </th>
                    <th class="text-center"> PLURIANUAL  </th>
                    <th class="text-center"> AÑOS PLURIANUALES   </th>
                    <th class="text-center"> VALOR TOTAL PLURIANUAL  </th>
                    <th class="text-center"> CLAVE PROGRAMA FEDERAL  </th>
                    <th class="text-center"> FECHA INICIO OBRA   </th>
                    <th class="text-center"> FECHA FIN OBRA  </th>
                    <th class="text-center">    TIPO PROCEDIMIENTO  </th>

                </thead>
                <tbody>

                </tbody>
            </table>
      
            </div>
            </div>
            <br>


            </div>
            <div id="memoria" class="tab-pane fade">
            <div class="col-xs-12 col-md-12"> 
                <div class="col-md-4">  

                <div class="col-md-3 col-xs-12"> 
                    <span  style="background: none; border: none;"><b>Partida específica:</b> </span>
                </div>

                    <div class="col-md-9 col-xs-12" >
                        <select id="partidasServices"  class="form-control partidasAssets">
                        </select>
                    </div>
                
                </div>
                <div class="col-md-4">  
                    
                         <component-text-label label="Presupuesto por asignar:" id="porAsignarSer" name="porAsignarSer" placeholder="Presupuesto por asignar" title="Presupuesto" maxlength="10" value="" readonly="true" class="text-left"></component-text-label>
                         <input type="hidden" name="" val="" id="presuSerOriginal">
                </div>

                 <div class="col-md-4">  
                    
                         <component-text-label label="Presupuesto asignado:" id="asignadoSer" name="" placeholder="Presupuesto asignado" title="Presupuesto" maxlength="10" value="" readonly="true" class="text-left"></component-text-label>
                         <input type="hidden" name="" val="" id="presuSerOriginal">
                </div>
                
            </div>

            <div class="col-xs-12 text-center"> 

           
                 <component-button type="button" id="showServices" name="showServices" class="glyphicon glyphicon-search" onclick="return false;" value="Mostrar"></component-button>
            

             </div>
                 <div class="col-md-12">

                    <div class=""  style="overflow-y: scroll; overflow-x: scroll; max-height: 420px;" id="servicesDiv" style="display: none;">
                <table class="table table-striped table-bordered table-hover" id="tablaServicios">
                <thead class="bgc8 ftc2 text-center" style="vertical-align:middle; font-size: 12px;">
               
                <!--<th >
                    
                    <button class="btn btn-xs" style="color: #000; text-align: center;" onclick="" id="addServicios" type="button"><span class="glyphicon glyphicon-plus"  ></span></button>
                    -->
                <!--</th>-->
                <th>  </th>
                 <th class="text-center"> CLAVE CUCOP </th>
                    <th class="text-center"> DESCRIPCIÓN </th>
                    <!-- <th class="text-center"> UR  </th>
                    <th class="text-center"> UE  </th>
                    <th class="text-center"> NÚMERO OFICIO   </th> -->
                    <th class="text-center"> PARTIDA </th>
                   <!-- <th class="text-center"> PEF </th>-->
                    <th class="text-center"> VALOR ESTIMADO (SIN IVA) </th>
                      <th class="text-center"> % IVA  </th>
                    <th class="text-center"> VALOR BRUTO  </th>
                    <th class="text-center"> VALOR MIPyMES   </th>
                    <th class="text-center"> VALOR NCTLC    </th>
                    <th class="text-center"> CANTIDAD    </th>
                    <th class="text-center"> UNIDAD MEDIDA   </th>
                    <th class="text-center"> CARACTER PROCEDIMIENTO  </th>
                    <th class="text-center"> ENTIDAD FEDERATIVA </th>
                    <th class="text-center"> PORCENTAJE 1ER TRIMESTRE    </th>
                    <th class="text-center"> PORCENTAJE 2DO TRIMESTRE    </th>
                    <th class="text-center"> PORCENTAJE 3ER TRIMESTRE    </th>
                    <th class="text-center"> PORCENTAJE 4TO TRIMESTRE    </th>
                    <th class="text-center"> FECHA REGISTRO  </th>
                    <th class="text-center"> PLURIANUAL  </th>
                    <th class="text-center"> AÑOS PLURIANUALES   </th>
                    <th class="text-center"> VALOR TOTAL PLURIANUAL  </th>
                    <th class="text-center"> CLAVE PROGRAMA FEDERAL  </th>
                    <th class="text-center"> FECHA INICIO OBRA   </th>
                    <th class="text-center"> FECHA FIN OBRA  </th>
                    <th class="text-center">    TIPO PROCEDIMIENTO  </th>
                </thead>
                <tbody>

                </tbody>
            </table>

             <br>
              </div>
             <!--
            <button id="addServicios" type="button" class="glyphicon glyphicon-plus btn btn-default botonVerde">Agregar</button>-->
            </div>


            </div>
        </div>




        </div>

    </div> <!-- end add more partidas col-md-12-->

               </div>
                </div>
                </div>
      
    <?php }?>
 
</div>

  <div class="col-xs-12 col-md-12 pt20 text-center">
        <button id="comeBack" class="btn btn-default botonVerde glyphicon glyphicon-home" style="color: #fff;"> Regresar</button>
        <button id="btnGuardar" type="button" class="glyphicon glyphicon-floppy-saved btn btn-default botonVerde">Guardar</button>

        <?php
            // validar permiso para modificar fecha
            if ($modificarfecha){
                echo '<button id="btnChange" type="button" class="glyphicon glyphicon-floppy-saved btn btn-default botonVerde">Modificar Fechas</button>';
            }
        ?>

        <button id="btnNewDate" type="button" class="glyphicon glyphicon-floppy-saved btn btn-default botonVerde" style="display: none;">Guardar</button>

    </div>
  <!--   <div class="panel-body p0 m0" id="divBotones" name="divBotones">

        <button id="home1" class="btn btn-default botonVerde glyphicon glyphicon-home" style="color: #fff;"> Regresar</button>
      
        <button id="btnGuardar" type="button" class="glyphicon glyphicon-floppy-saved btn btn-default botonVerde">Guardar</button>
      
       <div class="col-xs-12 col-md-6 text-center"><component-button type="button" id="btnExportaExcel" name="exportaExcel" class="" onclick="return false;" value="Exportar a Excel"></component-button></div>
    </div> -->
</div>
<!--fin de algun div en el header-->

<div id="ModalGeneral1" name="ModalGeneral1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false" class="modal fade ui-draggable out" style="display: none; padding-left: 0px;">
    <div role="document" name="ModalGeneral1Tam" id="ModalGeneral1Tam" class="modal-dialog ui-draggable-handle modal-md"><div class="modal-content"><div class="navbar navbar-inverse navbar-static-top">
     <input type="hidden" id="estatusText" value="">
      <input type="hidden" id="estatusVal" value="">
      <input type="hidden" id="estatusBtn" value="">
        <div class="col-md-12 menu-usuario"><span data-dismiss="modal" class="glyphicon glyphicon-remove"></span></div>
        <div id="navbar" class="navbar-collapse collapse"><div class="nav navbar-nav"><div class="title-header"><div id="ModalGeneral1_Titulo" name="ModalGeneral1_Titulo"><h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3></div></div></div></div> <div class="linea-verde"></div>

    </div> <div class="modal-body"><div id="ModalGeneral1_Advertencia" name="ModalGeneral1_Advertencia"></div><div id="ModalGeneral1_Mensaje" name="ModalGeneral1_Mensaje">

    </div></div> <div class="modal-footer"><div id="ModalGeneral1_Pie" name="ModalGeneral1_Pie">            <div class="input-group pull-right">                <button class="btn btn-default botonVerde" onclick="" id="confirmacionModalGeneral1">Si</button>                <button class="btn btn-default botonVerde" data-dismiss="modal" id="btnCerrarModalGeneral1" name="btnCerrarModalGeneral1">No</button>            </div>
    </div>
    </div>
    </div>
</div>

</div>

<?php
include 'includes/footer_Index.inc';
?>

 <script type="text/javascript" src="javascripts/paas.js?<?php echo rand(); ?>">
     window.numberFunction='<?php echo $funcion; ?>'
 </script>

 <script type="text/javascript">
 
 <?php

if(isset($_GET["folio"])) {?>
window.bye=0;
window.seeDetail=true;
window.numberScene="<?php echo $_GET["folio"]; ?>";

    <?php
    function compararFecha($fecha){
        $flag='0';
        $fechaConvertir=$fecha;
        $fechaConvertir=str_replace("/", "-",  $fechaConvertir);
        $fechaConvertir=explode("-", $fechaConvertir);
        $nuevaFecha=$fechaConvertir[2]."-".$fechaConvertir[1]."-".$fechaConvertir[0];
        $fechaactual  = date("Y-m-d");
        $actual = date_create($fechaactual);

        $fechaComparar = date_create($nuevaFecha);

        $date1 =$fechaComparar;
        $date2 = $actual;

        if ($date1 > $date2){
            // echo $date1->format("Y-m-d")." es mayor ".$date2->format("Y-m-d");
            $flag=2;
        }elseif($date1<$date2){
            // echo $date1->format("Y-m-d")." es menor".$date2->format("Y-m-d");
            $flag=1;

        }else{
            $flag=0;
        }

        //echo  $nuevaFecha." dif".$diferencia;
        // if($nuevaFecha>$actual){
        //  echo " fecha dada mayor";
        // }else{
        //   echo  " fecha dada menor";
        // }

        $data=array();
        $data[]=$fechaComparar;
        $data[]=$actual;
        $data[]=$flag;
        return $data;
    }// fin retorno comparar fecha

    $inicio= $_GET["inicio"];
    $fin=$_GET["fin"];

    $inicioR=compararFecha($inicio);
    $finR=compararFecha($fin);

    if( (($inicioR[2]==1) || ($inicioR[2]==0)) && (($finR[2]==2)|| ($finR[2]==0) ) ){

        if($estatus!=6) {
            if(($perfil=='capt') &&(($estatus>1) || ($estatus==5) )){

                // print_r($estatus);

                ?>
                window.bye="<?php echo "24" ; ?>";
                window.help="<?php echo $estatus ?>";
                function fnRangeDates(){
                $("#assetsTableDetails > tbody > tr >td").find('input').attr("disabled",'true');
                $("#panelAddPArtidas1").hide();
                $("#btnGuardar").hide();
                $('#assetsTableDetails').find('tr').each(function() {
                    $(this).find('th').eq(0).css("display", "none");
                                        
                });
                 $('#assetsTableDetails').find('tr').each(function() {
                    $(this).find('td').eq(0).css("display", "none");
                                        
                    });
                }
                // ca
            <?php

            } // fin perfil cap

            if(($perfil=="val") &&(($estatus>2) || ($estatus==5) )){

                ?>
                window.bye="<?php echo "24" ; ?>";

                function fnRangeDates(){
                $("#dateDesde").val();
                $("#dateHasta").val();

                $("#assetsTableDetails > tbody > tr >td").find('input').attr("disabled",'true');
                $("#panelAddPArtidas1").hide();
                $("#btnGuardar").hide();
                $('#assetsTableDetails').find('tr').each(function() {
                    $(this).find('th').eq(0).css("display", "none");
                                        
                });
                 $('#assetsTableDetails').find('tr').each(function() {
                    $(this).find('td').eq(0).css("display", "none");
                                        
                    });
                }
              
            <?php

            }// fin validador
            if(($perfil=="aut") && ($estatus==5) ){

                ?>
              
                window.bye="<?php echo "24" ; ?>";

                function fnRangeDates(){
                $("#dateDesde").val();
                $("#dateHasta").val();

                $("#assetsTableDetails > tbody > tr >td").find('input').attr("disabled",'true');
                $("#panelAddPArtidas1").hide();
                $("#btnGuardar").hide();
                $('#assetsTableDetails').find('tr').each(function() {
                    $(this).find('th').eq(0).css("display", "none");
                                        
                });
                 $('#assetsTableDetails').find('tr').each(function() {
                    $(this).find('td').eq(0).css("display", "none");
                                        
                    });
               
                }
               

                <?php

            } // fin atu
        }// gestionado
    }else{
        //if(($perfil!="aut") && ($estatus==5) ){
        if($estatus!=6) {
             if(($perfil!="aut")){
            ?>
           
           window.bye="<?php echo "24" ; ?>";

           function fnRangeDates(){
            $("#dateDesde").val();
            $("#dateHasta").val();

            $("#assetsTableDetails > tbody > tr >td").find('input').attr("disabled",'true');
            $("#panelAddPArtidas1").hide();
            $("#btnGuardar").hide();
            muestraModalGeneral(4,'<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'Fuera de los rangos de fecha');
            $('#assetsTableDetails').find('tr').each(function() {
                    $(this).find('th').eq(0).css("display", "none");
                                        
                });
                 $('#assetsTableDetails').find('tr').each(function() {
                    $(this).find('td').eq(0).css("display", "none");
                                        
                    });
                 
            }

    <?php
        }else if(($perfil=="aut") && ($estatus==5) ){
            ?>

              window.bye="<?php echo "24" ; ?>";

                function fnRangeDates(){
                $("#dateDesde").val();
                $("#dateHasta").val();

                $("#assetsTableDetails > tbody > tr >td").find('input').attr("disabled",'true');
                $("#panelAddPArtidas1").hide();
                $("#btnGuardar").hide();
                $('#assetsTableDetails').find('tr').each(function() {
                    $(this).find('th').eq(0).css("display", "none");
                                        
                });
                 $('#assetsTableDetails').find('tr').each(function() {
                    $(this).find('td').eq(0).css("display", "none");
                                        
                 });
                 
                }
            <?php
        }
    }// fin gestionado
       // }// fin sin es diferente de autorizado
    }// fin else de  entra en fecha


if(($perfil!="aut")){
?>
 // disable  elements
    $('#dateDesde').attr('disabled','true');
    $('#dateHasta').attr('disabled','true');
    $('#numberFolio').attr('disabled','true');
    $('#selectAnio').multiselect('disable');
    $('#selectUnidadEjecutora').multiselect('disable');
<?php

}else{?>
    $('#selectUnidadEjecutora').multiselect('disable');
    $('#dateDesde').attr('disabled','true');
    $('#dateHasta').attr('disabled','true');

    function fnChangeRanges(){
        $('#dateDesde').removeAttr('disabled');
        $('#dateHasta').removeAttr('disabled');
        $("#panelAddPArtidas1").hide();
        $("#panelDetailDiv").hide();
        $("#btnGuardar").hide();
        window.finicio= $('#dateDesde').val();
        window.ffin=$('#dateHasta').val();
        window.fobs=$("#txtAreaObs").val();
    }

    function verificarFechasCambios(){
        factini=$('#dateDesde').val();
        factfin=$('#dateHasta').val();
        obs=$("#txtAreaObs").val();
        contador=0;
        msg='';

        if(window.ffin==factfin){
            contador++;
            msg+="<i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i>La fecha de termino no  hay cambiado<br>";
        }

        if(window.fobs==obs){
            contador++;
            msg+="<i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i>En observaciones debe poner la justificación del cambio de  fechas<br>";
        }

        if (($("#cargarMultiples").length) && ($("#cargarMultiples").val() == '')) {
            contador++;
            msg+="<i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i>Necesita subir un archivo de justificación<br>";
        }

        if (!($("#cargarMultiples").length) ) {
            contador++;
             msg+="<i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i>Necesita subir un archivo de justificación<br>";
        }

        if(contador>0){
            muestraModalGeneral(4,titulo,msg);
        }
     }
<?php
}

}// fin folio

?>


 </script>