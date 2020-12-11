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

require 'includes/header.inc';
require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';

require 'javascripts/libreriasGrid.inc';
$numeroestatus=0;

$permisomostrar=0;
$permisoCapturista=0;
$permisomVal=0;
$permisoAut=0;

if(isset($_POST['estatus'])){
  $numeroestatus=$_POST['estatus'];
}

function fnChecarPerfilPorFuncion($db){
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
<script type="text/javascript" src="javascripts/solicitudAlmacen.js"></script>

<style> 
/*.anchoTd{
  width: px !important;
}*/
.desp .btn{
   max-width: 300px !important;
}
</style>
<script type="text/javascript">
$(function(){
  estatusNumero=$('#numeroestatus').val();
if (visible == 1) {

  $(".multiselect-native-select").prop('disabled',true);
  $(".multiselect").prop('disabled',true);
  $("#btnGuardar").css({"display":"none"});
  $("#cancelarSolicitud").css({"display":"none"});
}
if ((ed1 == 1) && (estatusNumero > 24)) { //40

  $(".multiselect-native-select").prop('disabled',true);
  $(".multiselect").prop('disabled',true);
  $("#txtAreaObs").prop('disabled', true);
  $("#btnGuardar").css({"display":"none"});
  $("#cancelarSolicitud").css({"display":"none"});

}
if ((ed2 == 1 )&& ((estatusNumero > 24) || (estatusNumero > 41))) { //40

  $(".multiselect-native-select").prop('disabled',true);
  $(".multiselect").prop('disabled',true);
  
  
}
if((ed2==1) &&((estatusNumero==30) || (estatusNumero > 41))){
  $("#btnGuardar").css({"display":"none"});
  $("#cancelarSolicitud").css({"display":"none"});
  $("#txtAreaObs").prop('disabled', true);
}
                           
if ((ed3 == 1) && ( (estatusNumero==30) ||estatusNumero==65 ) ) {

  $(".multiselect-native-select").prop('disabled',true);
  $(".multiselect").prop('disabled',true);
  $("#txtAreaObs").prop('disabled', true);
  $("#btnGuardar").css({"display":"none"});
  $("#cancelarSolicitud").css({"display":"none"});

}

});
</script>
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
                        <span id="numeroFolio"> </span>
                        
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
                           
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <component-textarea label="Observaciones: " id="txtAreaObs" name="txtAreaObs" rows="5" cols="" class="w100p" placeholder="Observaciones" maxlength="151"></component-textarea>
                            </div>
                        </div>
                    </div>

               
            </div>
        </div>
    </div>
    
   
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
        <form id="solicitud">
            <table class="table table-striped table-bordered" id="tablaArticulosSolicitud" style="color:#333;">
                <thead class="header-verde" >
                <?php if( $numeroestatus=='65'  ){

                } else{ ?>
                <th ><button class="btn btn-xs" style="color: #000; text-align: center;" onclick="" id="btnAgregarFila" type="button"><span class="glyphicon glyphicon-plus"  ></span></button></th>
                <?php }
               // echo $perfil;
                ?>

                <th style=" max-width: 100px !important;">No.</th> 
                <th style=" max-width: 300px !important;">Partida</th> 
                <th style=" max-width: 300px !important;">Clave artículo</th>
                <th style=" max-width: 300px !important;">Artículo</th>
                <th style=" max-width: 50px !important;"> U.M.</th>
                <th style=" max-width: 100px !important;">Cantidad solicitada</th>
                
                </thead>
                <tbody>
                </tbody>


            </table>
          </form>
        </div>
      
       <div> 
        <input type="hidden" value="" id="idDetalleSolicitud" />
        <input type="hidden" value="" id="numeroestatus" />
       

      </div>

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
                        <button id="btnGuardar" class="btn btn-default botonVerde glyphicon glyphicon-floppy-saved" style="color: #fff;"> Guardar</button> 

                          <button id="home1" class="btn btn-default botonVerde glyphicon glyphicon-home" style="color: #fff;"> Regresar</button> 
                           <button id="cancelarSolicitud" class="btn btn-default botonVerde glyphicon glyphicon-trash" style="color: #fff;"> Cancelar</button> 
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

<div id="ModalGeneral1" name="ModalGeneral1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false" class="modal fade ui-draggable out" style="display: none; padding-left: 0px;">


    <div role="document" name="ModalGeneral1Tam" id="ModalGeneral1Tam" class="modal-dialog ui-draggable-handle modal-md">
        <div class="modal-content"><div class="navbar navbar-inverse navbar-static-top">

                <div class="col-md-12 menu-usuario"><span data-dismiss="modal" class="glyphicon glyphicon-remove"></span></div>

                <div id="navbar" class="navbar-collapse collapse"><div class="nav navbar-nav"><div class="title-header"><div id="ModalGeneral1_Titulo" name="ModalGeneral1_Titulo"><h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3></div></div></div></div> <div class="linea-verde"></div>

            </div>
            <div class="modal-body">
                
                <input type="hidden" id="tipoMg" value="">
                <input type="hidden" id="accionMg" value="">
                <div id="ModalGeneral1_Advertencia" name="ModalGeneral1_Advertencia"></div> <div id="ModalGeneral1_Mensaje" name="ModalGeneral1_Mensaje">


                </div>
            </div> 
            <div class="modal-footer"><div id="ModalGeneral1_Pie" name="ModalGeneral1_Pie">            <div class="input-group pull-right">                <button class="btn btn-default botonVerde" onclick="" id="confirmacionModalGeneral1">Si</button>      
                        <button class="btn btn-default botonVerde" data-dismiss="modal" id="btnCerrarModalGeneral1" name="btnCerrarModalGeneral1">No</button>            </div></div>
            </div>
        </div>
    </div>
</div>

  </div>

<?php
require 'includes/footer_Index.inc';
?>
<script type="text/javascript">
 
function fnDetalleSolicitud(solicitud, estatusNumero, cargaDatos = 0,ur,desdeFuera=0) {
    $('#idDetalleSolicitud').val(solicitud);
    $('#numeroestatus').val(estatusNumero);
    <?php if($_POST['detallesolicitud']) {

      
      ?>
        
      if ((cargaDatos == 1) &&(desdeFuera==1)) {
         clvFinal='';
         descFinal='';
            //fnCargaVariables();
               // partidas=getPartida();
          
            setTimeout(function(){ 
              dataObj = {
              proceso: 'detalleSolicitud',
              solicitud: solicitud,
              almacen:$("#selectAlmacen").val(),
              ur:ur
          };
         
          $.ajax({
                  method: "POST",
                  dataType: "json",
                  url: "modelo/almacenModelo.php",
                  data: dataObj,
                  cache: false,
                  async: false
          })
          .done(function(data) {
          if (data.result) {
           // ocultaCargandoGeneral();
            detalle = data.contenido.detalle
            fnSeleccionarDatosSelect("selectUnidadEjecutora",detalle[0].ue);
            // $('#selectUnidadEjecutora').selectpicker('val', detalle[0].ue);
            // $('.selectUnidadEjecutora').css("display", "none");
            
            if(detalle[0].ue!="undefined"){
                      console.log("ue " +detalle[0].ue);
            } 
           
             for (i in detalle) {

              <?php if($numeroestatus=='65'){?>
               
               ant=cuentaFilas;
               fnAgregarFilaInputs(ant);
               $('#numero'+ant).html(detalle[i].renglon);
               $('#partida'+ant).val(detalle[i].partida);
               $('#clave'+ant).val(detalle[i].clave);
               
               $('#descripArt'+ant).val(detalle[i].descripcion);
               $("#um"+ant).val(detalle[i].unidad_medida);
               $("#cantidadSolicitada"+ant).val(detalle[i].cantidad); //

              <?php // capturista puede modificar
              }elseif( ($permisoCapturista==1) &&($numeroestatus==24) ){
                ?>
                //detalle sin bloquear
                ant=cuentaFilas;

              fnAgregarFila(ant,1);
              $('#partida'+ant).selectpicker('val',detalle[i].partida);
              $('#partida'+ant).multiselect('refresh');
              $('.partida').css("display", "none");
              $('#selectUnidadEjecutora').multiselect('disable');
              $('#selectAlmacen').multiselect('disable');



               options= fnCrearSelectDetalle(detalle[i].clave,detalle[i].clave);
               fnFormatoSelect('#clave'+ant, options);

               options1= fnCrearSelectDetalle(detalle[i].descripcion,detalle[i].descripcion);
               fnFormatoSelect('#descripArt'+ant, options1);

               $("#um"+ant).val(detalle[i].unidad_medida);
               $("#cantidadSolicitada"+ant).val(detalle[i].cantidad);
               //fin detalle sin bloquear
                      //// fin capturista puede modificar
                <?php
              }else if(($permisoCapturista==1) &&($numeroestatus>24)){ ?>

               $("#"+nombreTabla+"  thead th:eq(" + 0 + ")").css("display","none");
               ant=cuentaFilas;
               fnAgregarFilaInputs(ant);
               $('#numero'+ant).html(detalle[i].renglon);
               $('#partida'+ant).val(detalle[i].partida);
               $('#clave'+ant).val(detalle[i].clave);

               $('#descripArt'+ant).val(detalle[i].descripcion);
               $("#um"+ant).val(detalle[i].unidad_medida);
               $("#cantidadSolicitada"+ant).val(detalle[i].cantidad);
               


              <?php
                // validador puede modificar
              }elseif( ($permisomVal==1) && (($numeroestatus==24) || ($numeroestatus==41))   ){
                ?>
                //detalle sin bloquear
                ant=cuentaFilas;

              fnAgregarFila(ant,1);
              $('#partida'+ant).selectpicker('val',detalle[i].partida);
              $('#partida'+ant).multiselect('refresh');
              $('.partida').css("display", "none");
              $('#selectUnidadEjecutora').multiselect('disable');
              $('#selectAlmacen').multiselect('disable');



               options= fnCrearSelectDetalle(detalle[i].clave,detalle[i].clave);
               fnFormatoSelect('#clave'+ant, options);

               options1= fnCrearSelectDetalle(detalle[i].descripcion,detalle[i].descripcion);
               fnFormatoSelect('#descripArt'+ant, options1);

               $("#um"+ant).val(detalle[i].unidad_medida);
               $("#cantidadSolicitada"+ant).val(detalle[i].cantidad);
               //fin detalle sin bloquear
                      //// fin validador puede modificar
                <?php
              }else if( ($permisomVal==1) &&( ($numeroestatus>24) || ($numeroestatus>41) )){ ?>
               $("#"+nombreTabla+"  thead th:eq(" + 0 + ")").css("display","none");
               ant=cuentaFilas;
               fnAgregarFilaInputs(ant);
               $('#numero'+ant).html(detalle[i].renglon);
               $('#partida'+ant).val(detalle[i].partida);
               $('#clave'+ant).val(detalle[i].clave);

               $('#descripArt'+ant).val(detalle[i].descripcion);
               $("#um"+ant).val(detalle[i].unidad_medida);
               $("#cantidadSolicitada"+ant).val(detalle[i].cantidad);
               


              <?php
              }elseif( ($permisoAut==1) && (($numeroestatus==24) || ($numeroestatus==41) || ($numeroestatus==43))   ){
                ?>
                //detalle sin bloquear
                ant=cuentaFilas;

              fnAgregarFila(ant,1);
              $('#partida'+ant).selectpicker('val',detalle[i].partida);
              $('#partida'+ant).multiselect('refresh');
              $('.partida').css("display", "none");
              $('#selectUnidadEjecutora').multiselect('disable');
              $('#selectAlmacen').multiselect('disable');


               options= fnCrearSelectDetalle(detalle[i].clave,detalle[i].clave);
               fnFormatoSelect('#clave'+ant, options);

               options1= fnCrearSelectDetalle(detalle[i].descripcion,detalle[i].descripcion);
               fnFormatoSelect('#descripArt'+ant, options1);

               $("#um"+ant).val(detalle[i].unidad_medida);
               $("#cantidadSolicitada"+ant).val(detalle[i].cantidad);
               //fin detalle sin bloquear
                      //// fin validador puede modificar
                <?php
              }else if( ($permisoAut==1) &&( ($numeroestatus>24) || ($numeroestatus>41) || ($numeroestatus==43) )){ ?>
               $("#"+nombreTabla+"  thead th:eq(" + 0 + ")").css("display","none");
               ant=cuentaFilas;
               fnAgregarFilaInputs(ant);
               $('#numero'+ant).html(detalle[i].renglon);
               $('#partida'+ant).val(detalle[i].partida);
               $('#clave'+ant).val(detalle[i].clave);

               $('#descripArt'+ant).val(detalle[i].descripcion);
               $("#um"+ant).val(detalle[i].unidad_medida);
               $("#cantidadSolicitada"+ant).val(detalle[i].cantidad);
               


              <?php
              }elseif($permisomostrar==1){?>

               $("#"+nombreTabla+"  thead th:eq(" + 0 + ")").css("display","none");
               ant=cuentaFilas;
               fnAgregarFilaInputs(ant,24);
               $('#numero'+ant).html(detalle[i].renglon);
               $('#partida'+ant).val(detalle[i].partida); 
               $('#clave'+ant).val(detalle[i].clave);

               $('#descripArt'+ant).val(detalle[i].descripcion);
               $("#um"+ant).val(detalle[i].unidad_medida);
               $("#cantidadSolicitada"+ant).val(detalle[i].cantidad);
               $("#cantidadEntregada"+ant).val(detalle[i].cantidadentregada);
               console.log(detalle[i].cantidadentregada);
               $("#cantidadDisponible"+ant).val(detalle[i].disponible);
               


               <?php 
              }else{

              }
              ?>
              
              
             }// fin for

             $("#numeroFolio").empty();
             $("#numeroFolio").append('Folio:<span class="folius">' +solicitud + '</span>');
             $("#txtAreaObs").empty();
             $("#txtAreaObs").val(detalle[0].observaciones);

             if(visible==1){
                 
                 $("#"+nombreTabla).find('th').eq(6).after('<th id="entregadosth" valign="middle">Disponible</th>');
                 $("#"+nombreTabla).find('th').eq(7).after('<th id="entregadosth" valign="middle">Entregados</th>');
                 $("#"+nombreTabla).find('th').eq(8).after('<th id="entregadosth" valign="middle">Faltan</th>');
                 $("#"+nombreTabla).find('th').eq(9).after('<th id="entregadosth" valign="middle">Cantidad a entregar</th>');
                 
              }
              
          }else{
           // ocultaCargandoGeneral();
          }
          })
              .fail(function(result) {
                  console.log("Error en leer detalle " + solicitud);
                 // ocultaCargandoGeneral();
                        
              });
             },900);                 
             
             
     
      }// fin carga desde fuera
    <?php }?>// fin detalle existe
setTimeout(function(){ 
 rowCount = $('#tablaArticulosSolicitud >tbody >tr').length;
  console.log(rowCount);
  if(rowCount==0){
    
  }
  },1200); 

  }// fin cierre funcion
 
<?php if($_POST['detallesolicitud']) {?>
  muestraCargandoGeneral();
  fnDetalleSolicitud(<?php echo "'".$_POST['detallesolicitud']."','".$_POST['estatus']."'"; ?>,'1',<?php echo "'".$_POST['ur']."','1'"; ?>);
  

  
<?php } ?>


</script>


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


