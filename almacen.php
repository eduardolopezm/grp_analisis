<?php
/**
 * 
 *
 * @category     almacen
 * @package      ap_grp
 * @author       Arturo Lopez Peña  <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 12/10/2017
 * Fecha Modificación: 15/10/2017
 * Se crea la consoliacion de las requisiciones
 */
 // ini_set('display_errors', 1);
 //  ini_set('log_errors', 1);
 //  error_reporting(E_ALL);
$PageSecurity = 5;
require 'includes/session.inc';
$funcion = 2291;
$title= traeNombreFuncion($funcion, $db);
//$title = _('');
//$tituloAlternativo='Consolidaciones';
require 'includes/header.inc';

require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
require 'javascripts/libreriasGrid.inc';

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

$permisoAlmacenista= Havepermission($_SESSION ['UserID'], 2293, $db);//2293 permisos almacenista
?>

<!--<script type="text/javascript" src="javascripts/consolidaciones.js"></script>-->

<div id="OperacionMensaje" name="OperacionMensaje"></div>

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
              <!-- 24 Capturada
                41 Por validar
                43 Por autorizar
                30 En almacén
                0 Canceladas -->
           <div class="form-inline row pt20">
              <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                  <span><label> Estatus: </label></span>
              </div>
              <div class="col-xs-9 col-md-9">
                  <select id="estatusSel" name="estatusSel" class="estatusSel form-control">;

                      <option value="-2">Seleccionar..</option>
                      <option value="-1">Todos</option>
                      <option value="24">Capturada</option>
                      <option value="41">Por Validar</option>
                      <option value="43">Por Autorizar</option>
                      <option value="30">En almacén</option>
                      <option value="66">Entrega Completa</option>
                      <option value="67">Entrega Parcial</option>
                      <option value="0">Cancelada</option>

                  </select>
              </div>
          </div>


        </div>
        
        <div class="col-md-4 pt20 text-left">
            <component-number-label label="Folio:" id="txtNumeroRequisicion" name="txtNumeroRequisicion" value=""></component-number-label><br>
              
              <div class="row" style="text-align: left;">
              <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                  <span><label> Tipo: </label></span>
              </div>
              <div class="col-xs-9 col-md-9">
                  <select id="tipoSol" name="tipoSol" class="tipoSol form-control">;

                      <option value="0">Seleccionar..</option>

                      <option value="1">Manual</option>

                      <option value="65">Automática</option>

                  </select>
              </div>
          </div>

        </div>
        <div class="col-md-4 pt20">
          <component-date-label label="Desde:" id="dateDesde" name="dateDesde" placeholder="Desde fecha" title="DesdeFecha"></component-date-label>
          <br>
          <component-date-label label="Hasta:" id="dateHasta" name="dateHasta" placeholder="Hasta fecha" title="HastaFecha"></component-date-label>
        </div>
        
      </div>
        <!--<div class="panel-footer">-->
            <component-button type="submit" id="filtrar" name="filtrar" class="glyphicon glyphicon-search" onclick="return false;" value="Filtrar"></component-button>
        <!--</div>-->
    </div>
</div>
<!--fin pannel criterios de busqueda -->

<!-- <div class="col-md-12"> -->

<!-- <table class="table table-striped table-bordered " id="almacensolicitudes">
                  <thead>
                  <th>UR</th>
                  <th>Fecha de solicitud</th>
                  <th> </th>
                   </thead>
                <tbody>
                </tbody>
                </table>-->
<div id="tablaAlmacen">
    <div id="datosAlmacen"> </div>
</div>

<br>

<div id="viewSolicitud"> </div>

<div id="divBotones" name="divBotones">
    <?php if ($permisoAlmacenista==1) {?>
    <button id="imprimirFormatoSolicitud" class="btn botonVerde glyphicon glyphicon-print" style="color: #fff;"> Imprimir</button>

    <?php } else { ?>
    <!--<h4>Prueba </h4>-->
    <?php }?>

    <button id="nuevaSolicitud" class="glyphicon glyphicon-copy btn btn-default botonVerde" style="color: #fff;"> Nueva</button>
</div>

<br>

<!-- </div>-->
<!-- modal-->
<div id="ModalGeneral1" name="ModalGeneral1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false" class="modal fade ui-draggable out" style="display: none; padding-left: 0px;">


    <div role="document" name="ModalGeneral1Tam" id="ModalGeneral1Tam" class="modal-dialog ui-draggable-handle modal-md"><div class="modal-content"><div class="navbar navbar-inverse navbar-static-top">
     <input type="hidden" id="estatusText" value="">
      <input type="hidden" id="estatusVal" value="">
      <input type="hidden" id="estatusBtn" value="">
        <div class="col-md-12 menu-usuario"><span data-dismiss="modal" class="glyphicon glyphicon-remove"></span></div>

        <div id="navbar" class="navbar-collapse collapse"><div class="nav navbar-nav"><div class="title-header"><div id="ModalGeneral1_Titulo" name="ModalGeneral1_Titulo"><h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3></div></div></div></div> <div class="linea-verde"></div>

    </div> <div class="modal-body"><div id="ModalGeneral1_Advertencia" name="ModalGeneral1_Advertencia"></div> <div id="ModalGeneral1_Mensaje" name="ModalGeneral1_Mensaje">

    </div></div> <div class="modal-footer"><div id="ModalGeneral1_Pie" name="ModalGeneral1_Pie">            <div class="input-group pull-right">                <button class="btn btn-default botonVerde" onclick="" id="confirmacionModalGeneral1">Si</button>                <button class="btn btn-default botonVerde" data-dismiss="modal" id="btnCerrarModalGeneral1" name="btnCerrarModalGeneral1">No</button>            </div></div></div></div></div></div>
<!--fin modal-->
</div>
<!--no borrar es parte para pintar el pie de pagina-->

<?php
require 'includes/footer_Index.inc';
?>
<script type="text/javascript">
  
    fnFormatoSelectGeneral(".tipoSol");
    fnFormatoSelectGeneral(".estatusSel");
</script>
<script type="text/javascript">

var esconde;
<?php
if ($permisoAlmacenista==0) {
?>
esconde=1;
<?php } else {?>
esconde=0;
<?php
}
?>
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

<script type="text/javascript" src="javascripts/almacen.js"></script>
