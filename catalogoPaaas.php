<?php
/**
 * Panel para el PAAAS
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link /catalogoPaaas.php
 * Fecha Creación: 12 de mayo del 2018
 * Se genera el presente programa para la visualización de la información
 * de los esenarios que se encuentran generados.
 */
/* DECLARACION DE VARIABLES */
//
$PageSecurity = 1;
$PathPrefix = './';
$funcion = 2373;
/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');

$title= traeNombreFuncion($funcion, $db, 'Panel de PAAAS');

require 'includes/header.inc';
require 'javascripts/libreriasGrid.inc';

$ocultaDepencencia = 'hidden';

$contador=0;

if(Havepermission($_SESSION ['UserID'], 2389, $db)==1) { // autorizador
    $contador++;
}

$userid = $_SESSION['UserID'];
$sql = "SELECT profileid FROM sec_profilexuser WHERE userid = '$userid'";
$resl = DB_query($sql, $db);
$p = DB_fetch_assoc($resl);
$p['profileid'];

if($p['profileid']==11){
   $contador++;   
}
//echo $p['profileid'];
# fin del segmento de PHP
$perfil='';
$contadorv1=0;
$estatus=0;
$inicio='';
$fin='';
   if(Havepermission($_SESSION ['UserID'], 2378, $db)==1) { //validador
        $perfil="capt";
       $contadorv1++;
    }elseif(Havepermission($_SESSION ['UserID'], 2380, $db)==1) {//autorizador
        $perfil="val";
        $contadorv1++;
    }elseif(Havepermission($_SESSION ['UserID'], 2390, $db)==1) {//almacenista
        $contadorv1++;
        $perfil="aut";
    }
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
    // echo "<br>perfil: ".$perfil;

?>

<script>
    var nuFuncion = '<?= $funcion; ?>';
</script>

<div class="row">
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading h35">
                <h4 class="panel-title">
                    <div class="fl text-left">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#closeTab" aria-expanded="true" aria-controls="collapseOne">
                            <b>Criterios de Filtrado</b>
                        </a>
                    </div>
                </h4>
            </div><!-- .panel-heading -->
            <div id="closeTab" class="panel-collapse collapse in">
                <div class="panel-body">
                    <div class="row" id="form-search">
                        <!-- dependencia, UR, UE -->
                        <div class="col-md-4">
                            <div class="form-inline row <?= $ocultaDepencencia ?>">
                                <div class="col-md-3">
                                    <span><label>Dependencia: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioDependeciaGeneral('selectRazonSocial', 'selectUnidadNegocio')"></select>
                                </div>
                            </div>

                            <br class="<?= $ocultaDepencencia ?>">

                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>UR: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" data-todos="true" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')"></select>
                                </div>
                            </div>

                            <br>
                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>UE: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" data-todos="true" ></select>
                                </div>
                            </div>
                        </div><!-- -col-md-4 -->

                        <!-- FOLIO, FECHA, FECHA CAPTURA -->
                        <div class="col-md-4">
                            <component-number-label label="Folio:" id="numeroFolio" name="numeroFolio" value=""></component-number-label>
                            <br>
                            <div class="form-inline row">
                                <div class="col-md-3" style="vertical-align: middle;">
                                    <span><label>Estatus: </label></span>
                                </div>
                                <div class="col-md-9">
                                    <select id="status" name="status" class="form-control">
                                        <option value="">Sin Seleccion</option>
                                    </select>
                                    <!-- <select id="selectEstatusGeneral" name="selectEstatusGeneral" class="form-control selectEstatusGeneral"  data-funcion="<?= $funcion ?>"></select> -->
                                </div>
                            </div>
                            <br>
                            <!-- <component-date-label label="Fecha Captura:" id="dateCaptura" name="dateCaptura" placeholder="Fecha Captura" title="Fecha Desde" value="<?= date('d-m-Y');?>"></component-date-label> -->
                        </div><!-- -col-md-4 -->
                        <!-- fechas -->
                        <div class="col-md-4">
                            <component-date-label label="Desde:" id="dateDesde" name="dateDesde" placeholder="Desde" title="Desde" value="<?= date('d-m-Y');?>"></component-date-label>
                            <br>
                            <component-date-label label="Hasta:" id="dateHasta" name="dateHasta" placeholder="Hasta" title="Hasta" value="<?= date('d-m-Y');?>"></component-date-label>
                        </div><!-- -col-md-4 -->
                    </div>
                    <div class="row">
                        <component-button type="button" id="btn-search" class="glyphicon glyphicon-search"  value="Filtrar"></component-button>
                    </div>
                </div><!-- .panel-body -->
            </div><!-- .panel-collapse -->
        </div><!-- .panel -->
    </div><!-- .panel-group -->
</div><!-- .row -->

<!-- tabla de busqueda -->
<div class="row">
    <div id="tabla">
        <div id="datos"></div>
    </div>
</div><!-- .row -->

<!-- botones de accion -->
<div class="row pt10">
    <div class="panel panel-default">
        <div class="panel-body" align="center">
            <?php 
                if($contador>0){
                    echo '<component-button type="button" id="nuevo" class="glyphicon glyphicon-copy"  value="Nuevo"></component-button>';
                } 
            ?>
            <span id="areaBotones" name="areaBotones"></span><!-- BOTONES SEGUN PERMISOS -->
        </div>
    </div>
</div><!-- .row -->

<div id="ModalGeneral1" name="ModalGeneral1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false" class="modal fade ui-draggable out" style="display: none; padding-left: 0px;">


    <div role="document" name="ModalGeneral1Tam" id="ModalGeneral1Tam" class="modal-dialog ui-draggable-handle modal-md"><div class="modal-content"><div class="navbar navbar-inverse navbar-static-top">
     <input type="hidden" id="estatusText" value="">
      <input type="hidden" id="estatusVal" value="">
      <input type="hidden" id="estatusBtn" value="">
        <div class="col-md-12 menu-usuario"><span data-dismiss="modal" class="glyphicon glyphicon-remove"></span></div>

        <div id="navbar" class="navbar-collapse collapse"><div class="nav navbar-nav"><div class="title-header"><div id="ModalGeneral1_Titulo" name="ModalGeneral1_Titulo"><h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3></div></div></div></div> <div class="linea-verde"></div>

    </div> <div class="modal-body"><div id="ModalGeneral1_Advertencia" name="ModalGeneral1_Advertencia"></div> <div id="ModalGeneral1_Mensaje" name="ModalGeneral1_Mensaje">

    </div></div> <div class="modal-footer"><div id="ModalGeneral1_Pie" name="ModalGeneral1_Pie">            <div class="input-group pull-right">                <button class="btn btn-default botonVerde" onclick="" id="confirmacionModalGeneral1">Si</button>                <button class="btn btn-default botonVerde" data-dismiss="modal" id="btnCerrarModalGeneral1" name="btnCerrarModalGeneral1">No</button>            </div>
    </div>
    </div>
    </div>
</div>

</div>

<!-- inclicion de archivos js -->
<script type="text/javascript" src="javascripts/layout_general.js?v=<?= rand(); ?>"></script>
<script src="javascripts/catalogoPaaas.js?v=<?= rand(); ?>"></script>
<?php require 'includes/footer_Index.inc'; ?>

<script type="text/javascript">
    window.necesitaUE=true;
<?php 
    if($perfil=="capt"){
    ?>
    function validarEstatus(estatusAct,avance){
        var flag=false;

        switch (estatusAct) {
            case 5:
                flag = false;
                break;
            case 1:
                 if(avance==2){
                    flag=true;
                 }
                 if(avance==5){
                    flag=true;
                 }
                break;
            default :
                 flag=false;
           
        }
console.log("act",estatusAct," sig",avance, " flag",flag);
        return flag;
    }
    <?php
    }

    if($perfil=="val"){
    ?>

     function validarEstatus(estatusAct,avance){
        var flag=false;

        switch (estatusAct) {
            
            case 5:
                flag = false;
                break;
            case 1:
                 if(avance==1){
                    flag=true;
                 }
                 if(avance==2){
                    flag=true;
                 }
                 if(avance==3){
                    flag=true;
                 }
                 if(avance==5){
                    flag=true;
                 }
                 // act 1  sig 1  flag false
                 // act 1  sig 2  flag false
                 // act 1  sig 5  flag false
                 // 
                break;
             case 2:
                 if(avance==1){
                    flag=true;
                 }
                 if(avance==2){
                    flag=true;
                 }
                 if(avance==3){
                    flag=true;
                 }
                 if(avance==5){
                    flag=true;
                 }
                 
                //   act 2  sig 1  flag false
                //   act 2  sig 2  flag false
                //   act 2  sig 5  flag false
                break;
            
           
        }
        console.log("act",estatusAct," sig",avance, " flag",flag);
        return flag;
    }

    <?php
    }

     if($perfil=="aut"){
    ?>
     window.necesitaUE=false;
     function validarEstatus(estatusAct,avance){
        var flag=true;
        window.estatusSig=0;
        switch (estatusAct) {

            case 1:
                
                if(avance==2){
                     flag = false;
                }
                break;
             case 2:
                
                
                if(avance==2){
                     window.estatusSig=1;
                     flag = true;
                }
                break;

              case 4:
                    window.estatusSig=6;
                    flag=true;
                break;
            case 5:
                flag = false;
                break;
            case 6:
                    flag=false;
                break;
           
           
        }
       console.log("klskls act",estatusAct," sig",avance, " flag",flag);
        return flag;
    }

    <?php
    }

?>

</script>
