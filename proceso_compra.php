<?php
/**
 * consolidadesciones
 *
 * @category     proceso de. compra
 * @package      ap_grp
 * @author       Arturo Lopez Peña  <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 20/12/2017
 * Fecha Modificación: 21/12/2017
 * Se crea el procesp de la compra
 */
 // ini_set('display_errors', 1);
 //  ini_set('log_errors', 1);
 //  error_reporting(E_ALL);
 //  

$scriptCarga='';
$estatusRequisicion='1';
$costo=$_POST['monto'];
$requisicion=$_POST['requisicion'];

function  fnSaberProcesoArealizar($estatusRequisicion){
   $script='';
   $script='proceso_compra.js';
return $script;
}



$PageSecurity = 5;
require 'includes/session.inc';
$funcion = 2292;
$title= traeNombreFuncion($funcion, $db, 'Proceso de compra');
//$title = _('');
//$tituloAlternativo='Consolidaciones';
require 'includes/header.inc';

require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
require 'javascripts/libreriasGrid.inc';
//$permisoAlmacenista= Havepermission($_SESSION ['UserID'], 2293, $db);//2293 permisos almacenista

$scriptCarga=fnSaberProcesoArealizar($estatusRequisicion); //"proceso_compra.js";

if(isset($_POST['monto'])&&isset($_POST['requisicion'])){

?>
<link rel="stylesheet" href="css/proceso_compra.css">
<link href='https://fonts.googleapis.com/css?family=PT+Sans+Caption:400,700' rel='stylesheet' type='text/css'>
  
<!---  recibir datos y acuerdo el estatus carga los scripts. necesarios. dependiendo. del proceo de compra-->

<!--
<script type="text/javascript" src="javascripts/pre_configuracion_proceso_comopra.js"></script>-->



<!---  recibir datos -->

<div class="row"> 
    <!-- work flow-->
    <div class="container_work_flow_grp"> 
   <!-- <div class="workFlow_grp">  -->
    <div class="col-md-12 col-xs-12">


    <div class="arrow-steps_grp espacio_work_flow">
<?php //echo basename('../archivos/'."xprueba".'.xlsx'); ?>
              <div class="step " id="pasoInicio" > <span> Paso 1. Configuración Proceso
   </span> </div>
              <div class="step" id="pasoProvesug"> <span>Paso 2. Proveedores Sugeridos</span> </div>
              <div class="step" id="solCoti"> <span>Paso 3. Solicitud de Cotización</span> </div>
              
              <div class="step" id="recepCoti"> <span>Paso 4. Cuadro Comparativo</span> </div>
               <div class="step" id="recepCoti"> <span>Paso 5. Orden de Compra</span> </div>
    </div>
            <!--<div class="nav clearfix">
            <a href="#" class="prev">Previous</a>
            <a href="#" class="next pull-right">Next</a>
            </div>-->
    </div>
    </div> <!-- wor flow-->


</div>

<div class="row text-center" id="inicioProceso" style="">

   <!-- <div class="col-xs-12 col-md-12 text-center">-->
        
        <div class="col-xs-12 col-md-6">
          <span>Cantidad total  de la requisición  </span>
         <component-text placeholder="<?php echo $costo; ?>" maxlength="50"
            value="<?php echo $costo; ?>"  class="text-left" id="costoRequi"></component-text>
        </div>

        <div class="col-xs-12 col-md-6">
           <span>Número de la requisición </span>
         <component-text placeholder="<?php echo $requisicion; ?>" maxlength="50"
            value="<?php echo $requisicion; ?>" class="text-left"></component-text>
        </div>
        <div class="col-xs-12 col-md-12" >

            <b id="configuracion">  </b>

        </div>
      
            <div class="table-responsive col-xs-12 col-md-12" id="datosRequisicion" >  

            </div> 
      

    

    <div> 
      <input type="hidden" id="requiNum" name="requiNum" value="<?php echo $requisicion; ?>">
    </div>
    <!--  parametros de evaluacion-->
           

          <div class="col-md-12" id="procesoActual" >

          </div>
      <!--  fin parametros de. evaluacion-->

      <!-- carga de archivos para cotizacion-->
      <div style="display: none;" id="cargaCotizacion"> 
        <b>Una vez que reciba los archivos de cotización suba aquí. </b>
          <div class="soloCargarArchivos"> 
    <input type="hidden" id="esMultiple" name="esMultiple" value="0">
    <input type="hidden" value="" name="componente" id="componenteArchivos"/>
    <input type="hidden" value="2424" id="funcionArchivos" name="funcionArchivos"/>
    <input  type="hidden"  value="cotizacion" id="tipoArchivo"/>
    <input  type="hidden"  value="2424" id="transnoArchivo"/>
    <div id="mensajeArchivos"> </div>
    <div  id="subirArchivos"  class="col-xs-12 col-md-12">
        <div class="col-xs-12 col-md-12" style="color:#fff !important;">
            <div class="col-xs-6 col-md-6">
                <div id="tipoInputFile"> </div>
                <!--<input type="file"  class="btn bgc8"  :name="archivos','[]"  id="cargarMultiples"  multiple="multiple"  style="display: none;"/>-->
                <button  class="btn bgc8" id="cuadroDialogoCarga" 
onclick="fnCargarArchivos()"
                >
                    <span class="glyphicon glyphicon-file"></span>
                    Cargar archivo(s)
                </button >
                <br>
                <br/>
                <button id="enviarArchivosMultiples" class="btn bgc8" style="display: none;" >Subir</button>
                <br/>
                <br/>
            </div>
            <br>
        </div>
        <div id="muestraAntesdeEnviar" class="col-xs-12 col-md-12"> </div>
        <br/> <br/>
    </div>
</div>

  <div class="col-xs-12 col-md-12" > 

    <div class="datosCotizacionExcel">

    </div>

    
  </div>
      </div>
      <!-- carga de archivos para cotizacion-->
          
  
   
    <div class="col-md-12 text-center panel" id="botones"> 
      <button id="btncomenzarprocesocompra" class="btn btn-default botonVerde glyphicon glyphicon-play-circle" style="color: #fff;">Comenzar</button>
       <button id="btnregresar" class="btn btn-default botonVerde glyphicon glyphicon-home" style="color: #fff;">Regresar</button>
    </div>

</div>


<!--no borrar es parte para pintar el pie de pagina-->



<?php
}else{
echo '<div class="col-xs-12 col-md-12 btn" style="background-color:#f2dede !important;color:#a94442;"><h4>No hay requisición para iniciar proceso.</h4></div>';
}


require 'includes/footer_Index.inc';
?>

<!--
<script type="text/javascript" src="javascripts/<?php// echo $scriptCarga;?>">  </script>-->

<script type="text/javascript" src="javascripts/Subir_Archivos.js">  </script>
<script type="text/javascript" src="javascripts/proceso_compra.js">  </script>








