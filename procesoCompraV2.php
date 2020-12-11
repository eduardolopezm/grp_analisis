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

$PageSecurity = 5;
require 'includes/session.inc';
$funcion = 2265;
$title= traeNombreFuncion($funcion, $db, 'Proceso de compra');
//$title = _('');
//$tituloAlternativo='Consolidaciones';
require 'includes/header.inc';

require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
require 'javascripts/libreriasGrid.inc';


?>
<!--<link rel="stylesheet" href="css/proceso_compra.css">
<link href='https://fonts.googleapis.com/css?family=PT+Sans+Caption:400,700' rel='stylesheet' type='text/css'>
  -->
  <link rel="stylesheet" type="text/css" href="css/estilosProcesoCompraV2.css">
<script type="text/javascript" src="javascripts/Subir_Archivos.js"> </script>
<div class="row"> 
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
          
          <div class="form-inline row hide">
              <div class="col-md-3">
                  <span><label>Dependencia: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioDependeciaGeneral('selectRazonSocial', 'selectUnidadNegocio')" multiple="true"></select>
              </div>
          </div>
        </div>
        
        <div class="col-md-4 pt20">
            <component-number-label label="Número de requisción:" id="txtNumeroRequi" name="txtReciboNo" value=""></component-number-label><br>
           <!-- <div class="input-group">           
                <span class="input-group-addon" style="background: none; border: none;"><b> Estatus: </b></span>
                <select id="selectEstatusGeneral" name="selectEstatusGeneral[]" class="form-control selectEstatusGeneral" multiple="multiple" data-funcion="<?= $funcion ?>"></select>
            </div>-->


             
                 

        </div>
        <div class="col-md-4">
          <component-date-label label="Desde:" id="dateDesde" name="dateDesde" placeholder="Desde fecha" title="DesdeFecha"></component-date-label>
          <br>
          <component-date-label label="Hasta:" id="dateHasta" name="dateHasta" placeholder="Hasta fecha" title="HastaFecha"></component-date-label>
        </div>
        
      </div>
        <!--<div class="panel-footer">-->
         <!-- <div class="col-xs-6 col-md-6 text-right">
           <component-button id="btnBuscar" name="btnBuscar" value="Filtrar" onclick="fnBuscarDatos(1)" class="glyphicon glyphicon-search"></component-button>
             </div>-->
        <!--</div>-->
   <!-- </div>
</div>-->


                    <!--f-->
                 <div class="col-xs-6 col-md-6 text-right">
                      <br>
                            <component-button id="btnBuscar" name="btnBuscar" value="Filtrar" onclick="" class="glyphicon glyphicon-search"></component-button>
                    </div>
                    <!--buscar-->



                </div>
                </div><!--fin pannel criterios de busqueda -->

		<!--<h1>Proveedores sugeridos</h1>-->
	<div id="tablaTipos">
    	<div id="datosTipos"> </div>
	</div>
<!--</div>-->

<div id="datosRequi"> 

</div>

<div id="provSug"> 

</div>

<div id="datosCuadroComparativo">  
    
</div>
<!-- cargar datos cotizacion-->

  <div class="soloCargarArchivos" id="divCargarCotizacion" style="display: none;"> 
    <input type="hidden" id="esMultiple" name="esMultiple" value="0">
    <input type="hidden" value="" name="componente" id="componenteArchivos"/>
    <input type="hidden" value="2424" id="funcionArchivos" name="funcionArchivos"/>
    <input  type="hidden"  value="cotizacion" id="tipoArchivo"/>
    <input  type="hidden"  value="2424" id="transnoArchivo"/>
    <div id="mensajeArchivos"> </div>
    <div  id="subirArchivos"  class="col-md-12">
        <div class="col-md-12" style="color:#fff !important;">
            <div class="col-md-6">
                <div id="tipoInputFile"> </div>
                <!--<input type="file"  class="btn bgc8"  :name="archivos','[]"  id="cargarMultiples"  multiple="multiple"  style="display: none;"/>-->
                <button  class="btn bgc8" id="cuadroDialogoCarga" onclick="fnCargarArchivos()">
                    <span class="glyphicon glyphicon-file"></span>
                    Cargar archivo(s)
                </button >
                <!-- -->
            <div  class="btn bgc8 cerrarDetalle" style="color:white;">
       <span class="glyphicon glyphicon-remove" ></span> Atras </div>
                <!-- -->
                <br>
                <br/>
                <button id="enviarArchivosMultiples" class="btn bgc8" style="display: none;" >Subir</button>
                <br/>
                <br/>
            </div>
            <br>
        </div>
        <div id="muestraAntesdeEnviar" class="col-md-12 col-xs-12"> </div>
        <br/> <br/>
    </div>


  <div class="col-xs-12 col-md-12" > <!-- datos cotizacion-->

    <div class="datosCotizacionExcel">

    </div>

    <!--boton-->
    <div id="divGuardarCotizacion" style="display: none;"> 
        <component-button type="submit" id="btnGuardarProvSug" name="btnGuardarProvSug" class="glyphicon glyphicon-copy" onclick="return false;" value="Guardar">
                            
                        </component-button>
    </div><!-- boton -->
    
  </div> <!--fin datos cotizacion -->
</div>
<!--fin cargar  datos cotizacion-->


  <div class="soloCargarArchivos" id="divCargarContrato" style="display: none;"> 
  
</div>
<!--fin cargar de  contrato-->

<div class="col-md-12 text-center panel" id="botones"> 
      <button id="btnSolicitarCotizacion" class="btn btn-default botonVerde glyphicon glyphicon-play-circle" style="color: #fff;">Solicitar cotización</button>
      <button id="btnregresar" class="btn btn-default botonVerde glyphicon glyphicon-home" style="color: #fff;">Regresar</button>
        <button id="btnCargarCotizacionDiv" class="btn btn-default botonVerde   glyphicon glyphicon-list-alt" style="color: #fff;">Cargar cotización</button>

         <button id="btnCuadroComparativo" class="btn btn-default botonVerde    glyphicon glyphicon-usd" style="color: #fff;">Cuadro comparativo</button>
         
         <button id="btnCargarContrato" class="btn btn-default botonVerde    glyphicon glyphicon-usd" style="color: #fff;">Cargar contrato</button>
         
 </div>

</div>


<?php
require 'includes/footer_Index.inc';
?>

<script type="text/javascript" src="javascripts/procesoCompraV2.js">  </script>








