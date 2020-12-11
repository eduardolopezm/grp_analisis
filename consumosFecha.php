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

$title= traeNombreFuncion($funcion, $db, "PAAAS");
include 'includes/header.inc';
include( "includes/SecurityUrl.php");
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
include 'javascripts/libreriasGrid.inc';

// disable  becuase  change funcionality  in button´s Consumos
//$soloConsumos=false;

// if(isset($_GET["soloConsumos"])) {
//   if($_GET["soloConsumos"]=='true'){
//     $soloConsumos=true;
//    }
// }

?>

<div class="row">
    <div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title row">
                <div class="col-md-3 col-xs-3 text-left">
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelCriteriosBusqueda" aria-expanded="true" aria-controls="collapseOne">
                        <b>Criterios de filtrado</b>
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
                            <div class="col-md-12">
                               
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
                            </div> <!-- .col-md-12 -->
                        </div> <!-- .col-md-4 -->
                        <div class="row " id="r1">
                            <div class="col-xs-12 col-md-6 pt10 plr30">
                                <input id="dateDesde2" type="hidden" value="" name="dateDesde2">
                                <component-date-label label="Desde:" id="dateDesde" name="dateDesde" placeholder="Desde fecha" title="DesdeFecha"></component-date-label>
                            </div>
                            <div class="col-xs-12 col-md-6 pt20 plr30">
                                <!--
                                <component-date-label label="Hasta:" id="dateHasta" name="dateHasta" placeholder="Hasta fecha" title="HastaFecha"></component-date-label>-->
                                 <component-date-disable-next-year label="Hasta:" id="dateHasta" name="dateHasta" placeholder="Hasta fecha" title="HastaFecha"></component-date-disable-next-year >
                            </div>
                        
                        </div>
                        <div class="row" id="r3">

                        <div class="col-xs-12 col-md-4  pt20 pl35">
                            <div class="row" style="text-align: left;">
                                <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                                    <span><label>Partida: </label></span>
                                </div>
                                <div class="col-xs-9 col-md-9">
                                    <select id="selPartida" name="selPartida" class="selPartida form-control">;



                                    </select>
                                </div>
                            </div>
                        </div>

                       <!--  <div class="col-xs-12 col-md-6  pt20 pl35">
                            <div class="row" style="text-align: left;">
                                <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                                    <span><label>Programa: </label></span>
                                </div>
                                <div class="col-xs-9 col-md-9">
                                    <select id="selectProgramaPresupuestario" name="selectProgramaPresupuestario" class="selectProgramaPresupuestario form-control">



                                    </select>
                                </div>
                            </div>
                        </div>
 -->
                        </div>
                    </div>
                    <!--  fin panel body-->
            </div>
        </div>
    </form>
        <!-- fin panel-->
        <div class="col-xs-12  col-md-12 text-center">
            <!--  fila  botones-->
            <div class="col-xs-12 col-md-12 text-center">
                <component-button type="button" id="filtrar" name="btnFiltrar" class="glyphicon glyphicon-search" onclick="return false;" value="Filtrar"></component-button>
            </div>
        </form>
            <!--<div class="col-xs-12 col-md-6 text-center"><component-button type="button" id="btnExportaExcel" name="exportaExcel" class="" onclick="return false;" value="Exportar a Excel"></component-button></div>-->
        </div>
        <!-- fin  fila botones-->
    </div>
    <!--- fin  row-->
    <div class="row">
   <br><br>
    </div>
    <div class="" >
    <div class="" >

        <ul class="nav nav-tabs">

            <li class="active" style="min-width: 150px;max-width:150px; font-size: 13px;" ><a class="bgc10" data-toggle="tab" href="#bienes">Bienes</a></li>
            <li  style="min-width: 150px;max-width:150px; font-size: 13px;">
               <a id ="" class="bgc10" data-toggle="tab" href="#memoria">Memoria de cálculo</a>
             </li>
        </ul>
        <br>
        <div class="tab-content col-md-12">
            <div id="bienes" class="tab-pane active">

            <div class="row ">
            <div class="table-responsive"><!-- -->

            <table class="table table-striped table-bordered table-hover" id="tablaBienes">
             
                <thead class="bgc8 ftc2 text-center" style="vertical-align:middle; font-size: 12px;">
                <!-- <?php
                //if($soloConsumos==false){
                ?> -->

                     <th ><button class="btn btn-xs" style="color: #000; text-align: center;" onclick="" id="addBien" type="button"><span class="glyphicon glyphicon-plus"  ></span></button></th>

               <!--  <?php 
                
               // }

                ?> -->
                

                <th class="text-center">UR</th><th class="text-center">UE</th><th class="text-center">Partida</th><th class="text-center">Clave</th>
                <th class="text-center">Descripción</th><th class="text-center">UM</th><th class="text-center">Consumo total</th>
                <th class="text-center">Ultimo Costo</th>
                <th class="text-center">Importe</th>
                <th class="text-center">Ene</th><th class="text-center">Feb</th><th class="text-center">Mar</th><th class="text-center">Abr</th>
                <th class="text-center">May</th><th class="text-center">Jun</th><th class="text-center">Jul</th><th class="text-center">Ago</th>
                <th class="text-center">Oct</th><th class="text-center">Sep</th><th class="text-center">Nov</th><th class="text-center">Dic</th>
                </thead>
                <tbody>

                </tbody>
            </table>
      
            </div>
            </div>
            <br>


            </div>
            <div id="memoria" class="tab-pane fade">

                 <div class="row ">
                    <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" id="tablaServicios">
                <thead class="bgc8 ftc2 text-center" style="vertical-align:middle; font-size: 12px;">
                <!-- <?php
               // if($soloConsumos==false){
                ?> -->
                <th ><button class="btn btn-xs" style="color: #000; text-align: center;" onclick="" id="addServicios" type="button"><span class="glyphicon glyphicon-plus"  ></span></button></th>
              <!--   <?php 
               // }
                ?> -->

               <th class="text-center">UR</th><th class="text-center">UE</th><th class="text-center">Partida</th><th class="text-center">Clave</th>
                <th class="text-center">Descripción</th><th class="text-center">UM</th><th class="text-center">Consumo total</th>
                <th class="text-center">Ultimo Costo</th>
                <th class="text-center">Importe</th>
                <th class="text-center">Ene</th><th class="text-center">Feb</th><th class="text-center">Mar</th><th class="text-center">Abr</th>
                <th class="text-center">May</th><th class="text-center">Jun</th><th class="text-center">Jul</th><th class="text-center">Ago</th>
                <th class="text-center">Oct</th><th class="text-center">Sep</th><th class="text-center">Nov</th><th class="text-center">Dic</th>
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
    </div><!--no borrar es parte para pintar el pie de pagina-->
</div>
                    <div class="panel-body p0 m0" id="divBotones" name="divBotones">

                        <button id="home1" class="btn btn-default botonVerde glyphicon glyphicon-home" style="color: #fff;"> Regresar</button>
                      
                        <!-- <?php
                         //if($soloConsumos==false){
                        ?> -->
                        <button id="btnGuardar" type="button" class="glyphicon glyphicon-floppy-saved btn btn-default botonVerde">Guardar</button>
                        <!-- <?php
                        // }else{
                            ?> -->
                             <button id="btnProjected" type="button" class="glyphicon  glyphicon glyphicon-stats btn btn-default botonVerde">  Proyectar</button>
                         <!-- <?php 
                         //}
                        ?> -->
                    </div>

</div>
<!--fin de algun div en el header-->

<?php
include 'includes/footer_Index.inc';
?>

<script type="text/javascript" src="javascripts/consumosFecha.js?<?php echo rand(); ?>"></script>

<?php 
  //  if($soloConsumos==true){
?>
<script type="text/javascript">
    window.flagConsume=true;
</script>
<?php
    //}else{ 
?>
<script type="text/javascript">
    window.flagConsume=false;
</script>
    <?php

   // }
   
?>  
<?php
if(isset($_GET["folio"])) {?>
      <script type="text/javascript">
        window.seeDetail=true;
        window.numberScene=<?php echo $_GET["folio"]; ?>
     </script>
<?php
}
?>
<!--
 <script type="text/javascript" src="javascripts/consumosFecha.js>"></script>-->
