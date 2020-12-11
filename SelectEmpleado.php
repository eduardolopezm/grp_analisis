<?php
/**
 * Operaciones de Empleado
 *
 * @category Proceso
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 27/11/2017
 * Fecha Modificación: 27/11/2017
 * Muestra las opciones e información de los empleados
 */

$PageSecurity = 2;
include 'includes/session.inc';
$funcion = 2370;
$title= traeNombreFuncion($funcion,$db,'Catálogo de Empleados');

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
include 'javascripts/libreriasGrid.inc';

$ocultaDepencencia = 'hidden';
?>
<div id="OperacionMensaje" name="OperacionMensaje"> </div>
<script type="text/javascript">
  var tituloExcel = "<?= $title; ?>".split(" ").join("_")+"_"+( new Date().getDate()<10 ? "0" : "" )+new Date().getDate()+( new Date().getMonth()<10 ? "0" : "" )+new Date().getMonth()+new Date().getFullYear();
  
  //fnFormatoSelectGeneral(".regimen");
</script>
<script type="text/javascript" src="javascripts/SelectEmpleado.js"></script>
<div class="row">
  <!--Panel Busqueda-->

  <div class="panel panel-default">
     <div class="panel-heading h35" role="tab" id="headingOne">
        <h4 class="panel-title">
           <div class="fl text-left">
              <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelCriteriosBusqueda" aria-expanded="true" aria-controls="collapseOne">
                  <b>Criterios de Filtrado</b>
              </a>
           </div>
        </h4>
     </div>
     <!-- .panel-heading -->
     <!-- <div id="closeTab" class="panel-collapse collapse in"> -->
     <div id="PanelCriteriosBusqueda" name="PanelCriteriosBusqueda" class="panel-collapse collapse in">
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
                       <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')">
                          <option value="-1">Seleccionar...</option>
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
                 <br>
                 <component-text-label label="Nombre:" id="nombreCompleto" name="nombreCompleto" placeholder="Nombre" title="Nombre"></component-text-label >
              </div><!-- -col-md-4 -->
              <!-- clave empleado, estatus -->
              <div class="col-md-4">
                 <component-text-label label="Clave de empleado:" id="claveempleado" name="claveempleado" placeholder="Clave de empleado" title="Clave de empleado"></component-text-label >
                 <br>
                 <div class="form-inline row">
                    <div class="col-md-3" style="vertical-align: middle;">
                       <span><label>Estatus: </label></span>
                    </div>
                    <div class="col-md-9">
                       <select id="estatus" name="estatus" class="estatus form-control">
                          <option value="-1">Seleccionar..</option>
                          <option value="1">Activo</option>
                          <option value="0">Inactivo</option>
                       </select>
                    </div>
                 </div>
              </div>
              <!-- -col-md-4 -->
              <!-- rfc, curp -->
              <div class="col-md-4">
                <component-text-label label="RFC:" id="rfc" name="rfc" placeholder="RFC" title="RFC"></component-text-label >
                <br>
                <component-text-label label="CURP:" id="curp" name="curp" placeholder="CURP" title="CURP"></component-text-label >

              </div>
              <!-- -col-md-4 -->
              
              <!-- Buscador en función a Nombre, Apellido Paterno y Apellido Materno -->
              <!--<div class="row">
                  <div class="col-xs-12 col-md-4 pt20">
                    <component-text-label label="Nombre:" id="nombre" name="nombre" placeholder="Nombre" title="Nombre"></component-text-label >
                  </div>

                  <div class="col-xs-12 col-md-4 pt20">
                    <component-text-label label="Apellido Paterno:" id="apPat" name="apPat" placeholder="Apellido Paterno" title="ApellidoPaterno"></component-text-label >
                  </div>
                  
                  <div class="col-xs-12 col-md-4 pt20">
                    <component-text-label label="Apellido Materno:" id="apMat" name="apMat" placeholder="Apellido Materno" title="ApellidoMaterno"></component-text-label >
                  </div>
              </div>-->
           </div>
           <div class="row">
              <component-button type="submit" id="filtrar" name="filtrar" class="glyphicon glyphicon-search" onclick="return false;" value="Filtrar"></component-button>
           </div>
        </div>
        <!-- .panel-body -->
     </div>
     <!-- .panel-collapse -->
  </div>
  <!-- .panel --><!--Termina Panel Busqueda-->
 </div>

  <div align="center">
    <br><br>
  </div>

<div name="tablaEmp" id="tablaEmp">
  <div name="datosEmp" id="datosEmp"></div>
</div>


 <component-button type="button" id="btnAgregar" name="btnAgregar"  value="Nuevo" class="glyphicon glyphicon-plus"></component-button>
  <!-- <component-button type="button" id="btnCuenta" name="btnCuenta"  value="Agregar Cuenta" class="glyphicon glyphicon-plus"></component-button> -->


<div class="modal fade" id="ModalCuentaCon" name="ModalCuentaCon" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
    <div class="modal-content">
      <div class="navbar navbar-inverse navbar-static-top">
       
        <div class=" menu-usuario">
          <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <div class="nav navbar-nav">
            <div class="title-header">
              <div id="ModalCuentaCon_Titulo" name="ModalCuentaCon_Titulo"></div>
            </div>
          </div>
        </div>
        <div class="linea-verde"></div>
      </div>
      <div class="modal-body" id="ModalInfoPago_Mensaje" name="ModalInfoPago_Mensaje">
    <div  >
         
            <input type="hidden" value=""  id="bankAccount" name="bankAccount[]" />
           <div id="parteSelec" class=" row clearfix text-left">
            <div class="col-xs-12 col-md-12">  
                <div class="col-xs-3 col-md-3" style="vertical-align: middle;"> 
            <span><label>Cuenta contable: </label></span>
            </div> 
        <div class="col-xs-9 col-md-9"><select id="cuentasCon" required></select></div><br> </div>


    <div class="col-xs-12 col-md-12"> <br>  <div class="col-xs-3 col-md-3" style="vertical-align: middle;"> 
            <span><label>Tipo de operación DIOT: </label></span>
            </div> 
        <div class="col-xs-9 col-md-9"><select id="diotSel" required></select></div> <br></div> <br> 
    <div class="col-xs-12 col-md-12 text-left ml5"> <br> <component-text-label label="Concepto a desplegar :" id="conceptocc" name="conceptocc"  placeholder="Concepto a desplegar" title="conceptocc" maxlength="70" value=""></component-text-label> </div><br>


    <br>
    </div>
              
              <div id="messageError" style="background-color:#f2dede !important;color:#a94442; display: none;"> <h4>Faltan  datos</h4> </div>
        <!-- fin contenido-->
      </div>
      <br> <br> <br>
      <div class="modal-footer">
      <!--contenido -->
    
      <div class="col-xs-6 col-md-6 text-right"> 
      <div id="procesandoPagoEspere"> </div> <br>
  
      

     <button class="btn bgc8" style="color:white; font-weight: bold;" id="btnSaveCC">Guardar </button> 

    <button class="btn bgc8" style="color:white; font-weight: bold;" id="btnCerrarPago" name="btnCerrarPago" data-dismiss="modal" >Cerrar</button>

      </div><!-- fin moddal -->

        </div>

        <!--fin contenido footer -->
      </div>
    </div>
  </div>
</div>


<?php
include 'includes/footer_Index.inc';
?>
