<?php
/**
 * Vista de solicitud de almacen.
 *
 * @category     ABC
 * @package      ap_grp
 * @author       Arturo Lopez Peña <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 12/10/2017
 * Fecha Modificación: 16/08/2017
 * Se realizan operación de solicitud de almacen.
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');


$PageSecurity = 4;

include "includes/SecurityUrl.php";
require 'includes/DefinePOClass.php';
require 'includes/session.inc';

$title   = "";
$funcion = 2292;
$title= traeNombreFuncion($funcion, $db,'Solicitud de Bienes al Almacén');

/*if (isset($_GET['ModifyOrderNumber'])) {
$req = $_GET['ModifyOrderNumber'];
}*/
/*if (isset($_GET['ModifyOrderNumber'])) {
$title = _('Modificar Captura Requisición') . ' ' . $_GET['ModifyOrderNumber'];
//$req = $_GET['ModifyOrderNumber'];
} else {
$title = _('Captura Requisición');
}*/

require 'includes/header.inc';
//$funcion = 2265;
require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';


//Librerias GRID
require 'javascripts/libreriasGrid.inc';

/*$enc = new Encryption;
$url = "&SelectedUser=>" . $myrow[0] . "&funcion=>1";
$url = $enc->encode($url);
$liga= "URL=" . $url;*/

$permisomostrar=0;
$permisoCapturista=0;
$permisomVal=0;
$permisoAut=0;

function fnChecarPerfilPorFuncion($db)
{
    $perfil='capt';
    $contador=0;
    //$permisomostrarVal= Havepermission($_SESSION ['UserID'], 2287, $db);
    //$permisomostrarAut= Havepermission($_SESSION ['UserID'], 2288, $db);
    //$permisomostrarAlm= Havepermission($_SESSION ['UserID'], 2293, $db);

    if(Havepermission($_SESSION ['UserID'], 2297, $db)==1) { //validador
        $perfil="val";
       $contador++;
    }elseif(Havepermission($_SESSION ['UserID'], 2290, $db)==1) {//autorizador
        $perfil="aut";
        $contador++;
    }elseif(Havepermission($_SESSION ['UserID'], 2293, $db)==1) {//almacenista
        $contador++;
        $perfil="alm";
    }

   if($contador>1){
    $perfil="todos";
   }
    return $perfil;
}
$perfil=fnChecarPerfilPorFuncion($db);
  switch ($perfil) {
    case 'capt':
        $permisoCapturista=1;
        break;

    case 'val':
     $permisomVal=1;
        break;

    case 'aut':
        $permisoAut=1;
        break;

    case 'alm':
       $permisomostrar=1;
        break;
  
    default:
    
        break;
    }
//$permisomostrar= Havepermission($_SESSION ['UserID'], 2293, $db);//2293 permiso de elementos  visibles


?>



<!--<script type="text/javascript" src="javascripts/solicitud_almacen.js"></script>-->



<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
<div id="msjValidacion" name="msjValidacion"></div>

<div class="container">
    <div id="top-cotainer" class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 panel panel-default p0 mb10">
            <div class="panel-heading h35" role="tab" id="headingOne">
                <h4 class="panel-title">
                    <div class="fl text-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#idPanelRequisiciones" aria-expanded="true" aria-controls="collapseOne">
                            <b>Encabezado</b>

                        </a>
                    </div>
                    <div  class="fr text-right ftc7">
                        <span id="numero_folio"> </span>
                        
                    </div>
                </h4>
            </div>
            <div id="idPanelRequisiciones" class="row panel-collapse collapse in ptb5" role="tabpanel" aria-labelledby="headingOne">
                <div id="top-left-container" class="col-lg-8 col-md-8 col-sm-8">
                    <div class="form-inline row hide">
                        <div class="col-md-3">
                            <span><label>Dependencia: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioRazonSocial()">
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="form-inline row">
                        <div class="col-md-3">
                            <span><label>UR: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadNegocio()">
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="form-inline row" id="2323" >  
                                 <div class="col-md-3" style="vertical-align: middle;">
                                <span><label>UE: </label></span>
                                </div>
                          <div class="col-md-9">
                              <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora"> 
                                  <option value="-1">Seleccionar...</option>
                              </select>
                          </div>
                    </div>
                    <div class="form-inline row" style="display:none" id="2424" >  
                                 <div class="col-md-3" style="vertical-align: middle;">
                                <span><label>UE: </label></span>
                                </div>
                          <div class="col-md-9">
                              <input type="text"  id="selectUnidadEjecutora2" name="selectUnidadEjecutora2" class="form-control"> 
                            
                          </div>
                    </div>
                    <div class="form-inline row" style="display:none" id="2425" >  
                                 <div class="col-md-3" style="vertical-align: middle;">
                                <span><label>UE: </label></span>
                                </div>
                          <div class="col-md-9">
                              <select id="selectUnidadEjecutora1" name="selectUnidadEjecutora1" class="form-control selectUnidadEjecutora1"> 
                                 
                              </select>
                          </div>
                    </div>
                    <br>
                    <div class="form-inline row">
                        <div class="col-md-3">
                              <span><label>Almacén: </label></span>
                        </div>

                         <div class="col-md-9"> 
                        <select id="selectAlmacen" name="selectAlmacen" class="form-control selectAlmacen" > 
                       </select>
                         </div>
                    </div>
                </div>
                <div id="top-right-container" class="col-lg-4 col-md-4 col-sm-4 m0 p0">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <label class=""><b>Observaciones:</b> </label>
                </div>
                    <div class="obs-container pr25">
                        <div class="row">
                            <!--<div class="col-lg-3 col-md-3 col-sm-3 pt20">
                                <label class=""><b>Observaciones:</b> </label>
                            </div>-->
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <component-textarea label="Observaciones: " id="txtAreaObs" name="txtAreaObs" rows="5" cols="" class="w100p" placeholder="Observaciones" maxlength="151"></component-textarea>
                            </div>
                        </div>
                    </div>

                  <!--  <div class="row">-->
                       <!-- <label for="" id="" class="col-lg-3 col-md-3 col-sm-3 pt5">Nº Requisición: </label>
                        <div class="text-center col-lg-8 col-md-8 col-sm-8">
                            <label for="" id="idtxtRequisicionView" class=" bgc12 mt5 fts22 ftc8 borderGray borderRadius plr10" style="width: auto;"></label>
                        </div>-->
                       <!-- <input type="text" class="hide" id="idtxtNoRequisicion">
                        <input type="text" class="hide" id="idtxtRequisicion">
                    </div>-->

                    <!-- <component-number-label class="pr10" onclick="" label="Nº Requisición: " id="idtxtRequisicionView" name="idtxtRequisicion" placeholder="Nº Requisición" value=""></component-number-label> -->
                    <!-- <br>-->
                    <!--<component-date-label data-periodo ='<?php $periodoRequisicion ?>' label="Fecha Elaboración: " id="idFechaElaboracion" name="fechaElaboracion" placeholder="Fecha Elaboracion"></component-date-label>
                    <br>-->
                    <!-- <component-date-label label="Fecha Requerida: " id="idFechaEntrega" name="fechaEntrega" placeholder="Fecha de Entrega"></component-date-label> -->
                    <!--<div id="idContentFechaEntrega" class="row p0 m0">
                        <label class="col-lg-3 col-md-3 col-sm-3 pr25" for="">Fecha Requerida: </label>
                        <component-date-feriado class="pr10" id="idFechaEntrega" name="fechaEntrega" placeholder="Fecha de Entrega" title="Fecha de Entrega"></component-date-feriado>
                    </div>-->
               <!-- </div>-->
            </div>
        </div>
    </div>
    
    <!--<div id="main-container" class="row">-->
      <br/>
     

    </div><!--top container -->

     <div class="row"> 
     <div id="mini" style="display:none;"> 
     <select id="selectCveArticuloX" name="selectCveArticuloX" class="claveArticulo1"  required></select>
     </div>
     <div id="mini2" style="display:none;"> 
     <select id="selectArticuloX" name="selectArticuloX" class="claveDescripcion1" required></select>
     </div>
    <!-- <h5>ANTES </h5>-->
      <div class=""> <!--el table-responsive afecta a los selct -->
            <table class="table table-striped table-bordered" id="tablaArticulosSolicitud" style="color:#333;">
                <thead class="theadtablaArticulo" style="color: #fff; text-align: center; background-color:#1B693F; " >
                <th ><button class="btn btn-xs" style="color: #000; text-align: center;" onclick="" id="btnAgregar" ><span class="glyphicon glyphicon-plus" ></span></button></th>
                <th>No.</th>
                <!--<th>CABMS</th>-->
                <!--<th>Partida</th>-->
                <th>Clave <br> artículo</th>
                <th>Artículo</th>
                <th>U.M.</th>
                <th>Cantidad<br>solicitada</th>
                <?php if($permisomostrar==1){?>
                  <th> Disponible</th>
                <?php }?>
                 <!--cantida -->
                 <?php if($permisomostrar==1){?>
                  <th>Cantidad <br>a entregar</th>
                  <th>Cantidad<br>faltante</th>
                 <?php }else{ ?>
                  <!--<h4>Prueba </h4>-->
                 <?php }?>
                 
               <!-- <th>Precio</th>
                <th>Total</th>-->
                </thead>
                <tbody>
                </tbody>


            </table>
        </div>
      
       <div> 
        <input type="hidden" value="" id="idDetalleSolicitud" />
        <input type="hidden" value="" id="numeroestatus" />
       

      </div>
   <!-- </div> id="main-container" -->
    <br/>
    <div id="foot-container" class="row">
        <div class="col-xs-12 col-md-12">
        <!--<button class="btn bgc8" style="color: #fff;" id="enviarSolcitudAlmacen" type="submit">Enviar solicitud</button>-->
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12">
            <!--<div class="panel panel-default">
                <br>
                <div class="panel-body" align="center" id="divBotones" name="divBotones"></div>
            </div>-->
            <div class="panel panel-default">
                <?php if($permisomostrar==1){?>
                 <div class="panel-body p0 m0" id="divBotones" name="divBotones">
                    
                          <button id="home1" class="btn btn-default botonVerde glyphicon glyphicon-home" style="color: #fff;"> Regresar</button> 
                      </div>
                  
                <?php }else{?>
                <div class="panel-body p0 m0" id="divBotones" name="divBotones">
                    
                    <div class="text-center">
                        <button id="guardar_solicitud" class="btn btn-default botonVerde glyphicon glyphicon-floppy-saved" style="color: #fff;"> Guardar</button> 

                          <button id="home1" class="btn btn-default botonVerde glyphicon glyphicon-home" style="color: #fff;"> Regresar</button> 
                           <button id="cancelar_solicitud" class="btn btn-default botonVerde glyphicon glyphicon-trash" style="color: #fff;"> Cancelar</button> 
                    </div>
                    
                </div>
                <div class="panel-body p0 m0" id="nopermitido"  style="display: none;">
                    
                    <div class="text-center">
                      

                          <button id="home2" class="btn btn-default botonVerde glyphicon glyphicon-home" style="color: #fff;"> Regresar</button> 
                          
                    </div>
                    
                </div>
                <?php }?>
            </div>
        </div>
    </div>
   
</div>
  </div>

<?php
require 'includes/footer_Index.inc';
?>
<!--
<script type="text/javascript" src="javascripts/solicitudAlmacen_v1.js"></script>-->

<script type="text/javascript" src="javascripts/solicitud_almacen.js"></script>
 <!-- lo movi aqui para dectar almacen pero hay que cehcar que funcione cuando se entre como detalle si no mover a la liena 77 y si no mover a linea 280-->

<?php
if(isset($_POST['detallesolicitud']))
{?>
<script type="text/javascript">
  //jQuery.ready();
    fnDetalleSolicitud(<?php echo "'".$_POST['detallesolicitud']."','".$_POST['estatus']."'"; ?>,'1',<?php echo "'".$_POST['ur']."','1'"; ?>);
</script>
<?php
}?>

<script type="text/javascript">
    var visible;
    var usuarioEntrega;
<?php
if($permisomostrar==1){
?>
visible=1;
usuarioEntrega="<?php echo $_SESSION ['UserID'];  ?>";
<?php    
}else{
?>
visible=0;
<?php }?>
</script>


<script type="text/javascript">
    var ed1;
<?php
if($permisoCapturista==1){
?>
 ed1=1;

<?php    
}else{
?>
ed1=0;
<?php }?>
</script>



<script type="text/javascript">
    var ed2;
<?php
if($permisomVal==1){
?>
 ed2=1;

<?php    
}else{
?>
ed2=0;
<?php }?>
</script>





<script type="text/javascript">
    var ed3;
<?php
if($permisoAut==1){
?>
 ed3=1;

<?php    
}else{
?>
ed3=0;
<?php }?>
</script>

<!--<script type="text/javascript" src="javascripts/solicitud_almacen.js"></script>--> 