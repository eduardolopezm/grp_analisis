<?php
/**
 * Panel de Ministración.
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link /ministracion.php
 * Fecha Creación: 11.06.18
 * Se genera el presente programa para la visualización de la información
 * de la ministración.
 */

/* DECLARACION DE VARIABLES */
$PageSecurity = 1;
$PathPrefix = './';
$funcion = 90;

/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
require($PathPrefix . 'includes/session.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
include 'includes/mail.php';
$title= traeNombreFuncion($funcion, $db);
require 'includes/header.inc';
require 'javascripts/libreriasGrid.inc';

$ocultaDepencencia = 'hidden';
$UserId="";

if (isset($_GET['idUsuario'])) {
    $UserId = $_GET['idUsuario'];
}

?>

<script>
    window.UserId = <?= "'".$UserId."'" ?>;
</script>

<script type="text/javascript" src="javascripts/layout_general.js"></script>

<div class="row">
    <div class="col-sm-6 col-sm-offset-3" id="mensaje"></div>
</div>

<div class="row">

    <div class="col-md-12 col-md-4">
        <ul class="nav nav-tabs">
            <li class="active"> <a data-toggle="tab" href="#datosGenerales" class="bgc10" aria-expanded="false">Datos Generales</a> </li>
        </ul><!-- .nav .nav-tabs -->
        <div class="tab-content">
            <div class="tab-pane active" id="datosGenerales" style="text-align: left;">
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel-group" >
                            <div class="panel panel-default">
                                <div class="panel-heading h35" role="tab" id="headingOne">
                                    <h4 class="panel-title">
                                    <div class="fl text-left">
                                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#Panelviaticos" aria-expanded="true" aria-controls="collapseOne">
                                            <b>Datos Usuario</b>
                                        </a>
                                    </div>
                                    </h4>
                                </div>

                                <div class="panel-body panelConfiguracion">
                                    <form id="frmDatosUsuarios" enctype="multipart/form-data">

                                        <div class="col-md-12">
                                            <component-text-label label="Usuario:" id="txtUsuario" name="txtUsuario" placeholder="Usuario" maxlength="60"></component-text-label>  
                                        </div>
                                        
                                        <div class="col-md-12">
                                            <br>
                                            <component-text-label type="password" label="Contraseña:" id="txtContrasena" name="txtContrasena" placeholder="Contraseña" maxlength="60"></component-text-label>  
                                        </div>
                                        
                                        <div class="col-md-12">
                                            <br>
                                            <component-text-label label="Nombre Completo:" id="txtNombreUsuario" name="txtNombreUsuario" placeholder="Nombre Usuario" maxlength="60"></component-text-label>  
                                        </div>

                                        <div class="col-md-12">
                                            <component-text-label label="Teléfono:" id="txtTelefono" name="txtTelefono" placeholder="Teléfono" maxlength="60"></component-text-label>  
                                        </div>
                                        
                                        <div class="col-md-12">
                                            <br>
                                            <component-text-label label="Email:" id="txtEmail" name="txtEmail" placeholder="Email" maxlength="60"></component-text-label>  
                                        </div>
                                        
                                        <div class="col-md-12">
                                            <br>
                                            <component-number-label label="Caja:" id="nuCaja" name="nuCaja" placeholder="Caja" maxlength="60"></component-number-label>  
                                        </div>
                                        
                                        <div class="col-md-12">
                                            <br>
                                            <div class="form-inline row">
                                                <div class="col-md-3" style="vertical-align: middle;">
                                                    <span><label>Objetos Principal: </label></span>
                                                </div>
                                                <div class="col-md-9">
                                                <select id="selectObjetoPrincipalUsuarios" name="selectObjetoPrincipalUsuarios[]" class="form-control selectObjetoPrincipalUsuarios selectGeneral" multiple="true"></select>
                                                <!-- <select id="retencionesProveedor" name="retencionesProveedor" class="retencionesProveedor form-control" multiple="true"> -->
                                                </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <br>
                                            <div class="form-inline row">
                                                <div class="col-md-3" style="vertical-align: middle;">
                                                    <span><label>Estatus: </label></span>
                                                </div>
                                                <div class="col-md-9">
                                                    <select id="selectEstatusUsuario" name="selectEstatusUsuario" class="form-control selectEstatusUsuario" data-todos="true">
                                                    </select>
                                                    
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <br>
                                            <component-text-label label="Departamento:" id="txtDepartamento" name="txtDepartamento" placeholder="Departamento" maxlength="60"></component-text-label>  
                                        </div>

                                        <div class="col-md-12">
                                            <br>
                                            <div class="form-inline row">
                                                <div class="col-md-3" style="vertical-align: middle;">
                                                    <span><label>Imagen: </label></span>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="file" id="inputFile" name="inputFile">
                                                    <p class="help-block">Imagen para el usuario.</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-12 text-center" id="divImagenUsuario" >
                                            <img class="img-circle" id="imgFoto" src="" style="width: 20%;  height: auto;">
                                        </div>
                                        <br>
                                    </form>
                                </div>
                            </div>
                        </div><!-- .panel-group -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id= "divContenedorCheck" class="col-sm-12 col-md-8">
        <ul class="nav nav-tabs">
            <li class="active"> <a data-toggle="tab" href="#datosPerfil" class="bgc10" aria-expanded="false">Configuración Perfil</a> </li>
            <li> <a data-toggle="tab" href="#datosFunciones" class="bgc10" aria-expanded="false">Configuración Funciones</a> </li>
            <li> <a data-toggle="tab" href="#datosAlmacenes" class="bgc10" aria-expanded="false">Configuración Almacén</a> </li>
            <li> <a data-toggle="tab" href="#datosPresupuestal" class="bgc10" aria-expanded="false">Configuración Presupuestal</a> </li>
        </ul><!-- .nav .nav-tabs -->

        <div class="tab-content">

            <!-- perfil -->
            <div class="tab-pane active" id="datosPerfil" style="text-align: left;">
                <br>
                <div class="">
                    <div class="row">
                    
                    <div class="col-sm-12 col-md-4">
                            <div class="panel-group" id="accordionUR" role="tablist" aria-multiselectable="true">
                                <div class="panel panel-default">
                                    <div class="panel-heading h35" role="tab" id="headingOneUR">
                                        <h4 class="panel-title">
                                        <div class="fl text-left">
                                            <a  role="button" data-toggle="collapse" data-parent="#accordionUR" href="#collapseOneUR" aria-expanded="true" aria-controls="collapseOneUR">
                                                <b>Unidades Responsable</b>
                                            </a>
                                        </div>
                                        <div class="text-right" id="checkUR">
                                            <span style="cursor: pointer; display: none;"  class="glyphicon glyphicon-check checkTodo" data-panelcheck="panelUR" data-panel="checkUR" aria-hidden="true" ></span>
                                            <span style="cursor: pointer;"  class="glyphicon glyphicon-unchecked uncheckTodo" data-panelcheck="panelUR" data-panel="checkUR" aria-hidden="true"></span>
                                        </div>
                                        </h4>
                                    </div>
                                    <div id="collapseOneUR" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOneUR">
                                        <!-- <div  class="col-md-12 text-right"> Almacén Default</div> -->
                                        <div class="panel-body panelConfiguracion" id="panelUR">
                                            
                                        </div>
                                    </div>
                                </div>
                        </div><!-- .panel-group UR -->
                        </div>

                        <!-- <div class="col-sm-12 col-md-4">
                            <div class="panel-group" id="accordionUR" role="tablist" aria-multiselectable="true">
                                <div class="panel panel-default">
                                    <div class="panel-heading h35" role="tab" id="headingOneUR">
                                        <h4 class="panel-title">
                                        <div class="fl text-left">
                                            <a  role="button" data-toggle="collapse" data-parent="#accordionUR" href="#collapseOneUR" aria-expanded="true" aria-controls="collapseOneUR">
                                                <b>Unidades Responsable</b>
                                            </a>
                                        </div>
                                        <div class="text-right" id="checkUR">
                                            <span style="cursor: pointer; display: none;"  class="glyphicon glyphicon-check checkTodo" data-panelcheck="panelUR" data-panel="checkUR" aria-hidden="true" ></span>
                                            <span style="cursor: pointer;"  class="glyphicon glyphicon-unchecked uncheckTodo" data-panelcheck="panelUR" data-panel="checkUR" aria-hidden="true"></span>
                                        </div>
                                        </h4>
                                    </div>
                                    <div id="collapseOneUR" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOneUR"> -->
                                        <!-- .Aqui se llena la tabla UR -->
                                        <!-- <div class="panel-body panelConfiguracion" id="panelUR">
                                        </div>
                                    </div>
                                </div> -->
                            <!--</div> .panel-group UR -->
                        <!-- </div> -->

                        <div class="col-sm-12 col-md-4">
                            <div class="panel-group" id="accordionUE" role="tablist" aria-multiselectable="true">
                                <div class="panel panel-default">
                                    <div class="panel-heading h35" role="tab" id="headingOneUE">
                                        <h4 class="panel-title">
                                            <div class="fl text-left">
                                                <a role="button" data-toggle="collapse" data-parent="#accordionUE" href="#collapseOneUE" aria-expanded="true" aria-controls="collapseOneUE">
                                                    <b>Unidades Ejecutoras</b>
                                                </a>
                                            </div>
                                            <div class="text-right" id="checkUE">
                                                <span style="cursor: pointer; display: none;"  class="glyphicon glyphicon-check checkTodo"  data-panelcheck="panelUE" data-panel="checkUE" aria-hidden="true" ></span>
                                                <span style="cursor: pointer;"  class="glyphicon glyphicon-unchecked uncheckTodo"  data-panelcheck="panelUE" data-panel="checkUE" aria-hidden="true"></span>
                                            </div>
                                        </h4>
                                    </div>
                                    <div id="collapseOneUE" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOneUE">
                                        <div class="panel-body panelConfiguracion" id="panelUE" >
                                        </div>
                                    </div>
                                </div>
                            </div><!-- .panel-group UE -->
                        </div>

                        <div class="col-sm-12 col-md-4">
                            <div class="panel-group" id="accordionPerfiles" role="tablist" aria-multiselectable="true">
                                <div class="panel panel-default">
                                    <div class="panel-heading h35" role="tab" id="headingOnePerfiles">
                                        <h4 class="panel-title">
                                        <div class="fl text-left">
                                            <a role="button" data-toggle="collapse" data-parent="#accordionPerfiles" href="#collapseOnePerfil" aria-expanded="true" aria-controls="collapseOnePerfil">
                                                <b>Perfiles de Usuario</b>
                                            </a>
                                        </div>
                                        <div class="text-right" id="checkPerfil">
                                            <span style="cursor: pointer; display: none;"  class="glyphicon glyphicon-check checkTodo" data-panelcheck="panelPerfiles" data-panel="checkPerfil" aria-hidden="true" ></span>
                                            <span style="cursor: pointer;"  class="glyphicon glyphicon-unchecked uncheckTodo" data-panelcheck="panelPerfiles" data-panel="checkPerfil" aria-hidden="true"></span>
                                        </div>
                                        </h4>
                                    </div>
                                    <div id="collapseOnePerfil" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOnePerfiles">
                                        <div class="panel-body panelConfiguracion" id="panelPerfiles">
                                        </div>
                                    </div>
                                </div>
                            </div><!-- .panel-group Perfil -->
                        </div>

                    </div>
                </div>
            </div><!-- .tab-pane #datosPerfil -->

            <!-- funciones -->
            <div class="tab-pane" id="datosFunciones"  style="text-align: left;">
                <br>
                <div class="">
                    <div class="row" id="panelesFunciones">
                    </div>
                </div>
                
            </div><!-- .tab-pane #datosFunciones -->

            <!-- funciones -->
            <div class="tab-pane" id="datosAlmacenes"  style="text-align: left;">
                <br>
                <div class="">
                    <div class="row" id="panelesAlmacenes">
                        <div class="col-sm-12 col-md-6">
                            <div class="panel-group" id="accordionAlmacen" role="tablist" aria-multiselectable="true">
                                <div class="panel panel-default">
                                    <div class="panel-heading h35" role="tab" id="headingOneAlmacen">
                                        <h4 class="panel-title">
                                        <div class="fl text-left">
                                            <a  role="button" data-toggle="collapse" data-parent="#accordionAlmacen" href="#collapseOneAlmacen" aria-expanded="true" aria-controls="collapseOneAlmacen">
                                                <b>Almacenes</b>
                                            </a>
                                        </div>
                                        <div class="text-right" id="checkAlmacen">
                                            <span style="cursor: pointer; display: none;"  class="glyphicon glyphicon-check checkTodo" data-panelcheck="panelAlmacen" data-panel="checkAlmacen" aria-hidden="true" ></span>
                                            <span style="cursor: pointer;"  class="glyphicon glyphicon-unchecked uncheckTodo" data-panelcheck="panelAlmacen" data-panel="checkAlmacen" aria-hidden="true"></span>
                                        </div>
                                        </h4>
                                    </div>
                                    <div id="collapseOneAlmacen" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOneAlmacen">
                                        <!-- <div  class="col-md-12 text-right"> Almacén Default</div> -->
                                        <div class="panel-body panelConfiguracion" id="panelAlmacen">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div><!-- .panel-group UR -->
                        </div>
                    </div>
                </div>
            </div><!-- .tab-pane #datosFunciones -->

            <!-- presupuestal -->
            <div class="tab-pane" id="datosPresupuestal"  style="text-align: left;">
                <br>
                <div class="">
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <div class="panel-group" id="accordionCapitulos" role="tablist" aria-multiselectable="true">
                                <div class="panel panel-default">
                                    <div class="panel-heading h35" role="tab" id="headingOneCapitulos">
                                        <h4 class="panel-title">
                                        <div class="fl text-left">
                                            <a role="button" data-toggle="collapse" data-parent="#accordionCapitulos" href="#collapseOneCapitulos" aria-expanded="true" aria-controls="collapseOneCapitulos">
                                                <b>Capitulos</b>
                                            </a>
                                        </div>
                                        <div class="text-right" id="checkCaitulo">
                                            <span style="cursor: pointer; display: none;"  class="glyphicon glyphicon-check checkTodo" data-panelcheck="panelCapitulos" data-panel="checkCaitulo" aria-hidden="true" ></span>
                                            <span style="cursor: pointer;"  class="glyphicon glyphicon-unchecked uncheckTodo" data-panelcheck="panelCapitulos" data-panel="checkCaitulo" aria-hidden="true"></span>
                                        </div>
                                        </h4>
                                    </div>
                                    <div id="collapseOneCapitulos" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOneCapitulos">
                                        <div class="panel-body panelConfiguracion" id="panelCapitulos">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div><!-- .panel-group Capitulos -->
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="panel-group" id="accordionPartidas" role="tablist" aria-multiselectable="true">
                                <div class="panel panel-default">
                                    <div class="panel-heading h35" role="tab" id="headingOnePartidas">
                                        <h4 class="panel-title">
                                        <div class="fl text-left">
                                            <a role="button" data-toggle="collapse" data-parent="#accordionPartidas" href="#collapseOnePartidas" aria-expanded="true" aria-controls="collapseOnePartidas">
                                                <b>Partida Especifica</b>
                                            </a>
                                        </div>
                                        <div class="text-right" id="checkPartidaEspecifica">
                                            <span style="cursor: pointer; display: none;"  class="glyphicon glyphicon-check checkTodo" data-panelcheck="panelPartidasEspecificas" data-panel="checkPartidaEspecifica" aria-hidden="true" ></span>
                                            <span style="cursor: pointer;"  class="glyphicon glyphicon-unchecked uncheckTodo" data-panelcheck="panelPartidasEspecificas" data-panel="checkPartidaEspecifica" aria-hidden="true"></span>
                                        </div>
                                        </h4>
                                    </div>
                                    <div id="collapseOnePartidas" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOnePartidas">
                                        <div class="panel-body panelConfiguracion" id="panelPartidasEspecificas">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div><!-- .panel-group Capitulos -->
                        </div>
                    </div>
                </div>
            </div><!-- .tab-pane #datosPresupuestal -->

        </div><!-- .tab-content -->
    </div>
</div>

<div class="row text-center">
    <component-button type="button" id="btn-restablecer" class="glyphicon glyphicon-refresh"  value="Restablecer"></component-button>
    <component-button type="button" id="btn-guardar" class="glyphicon glyphicon-floppy-disk"  value="Guardar"></component-button>
    <component-button type="button" id="btn-regresar" class="glyphicon glyphicon-arrow-left"  value="Regresar"></component-button>
    <br>
    <br>
</div>


<script src="javascripts/usuarioDetalle.js?v=<?= rand(); ?>"></script>

<?php require 'includes/footer_Index.inc'; ?>
