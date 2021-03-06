<?php


/**
 * Operaciones de Proveedor
 *
 * @category Proceso
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 27/11/2017
 * Fecha Modificación: 27/11/2017
 * Muestra las opciones e información de los proveedores
 */


$PageSecurity = 2;
include 'includes/session.inc';
//$title = _('Mantenimiento Tipo de Gasto');
$funcion = 2476;

$title= traeNombreFuncion($funcion,$db,'Catálogo de Beneficiarios');
include 'includes/header.inc';

include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
include 'javascripts/libreriasGrid.inc';
?>
<div id="OperacionMensaje" name="OperacionMensaje"> </div>
<script type="text/javascript" src="javascripts/SelectBeneficiario.js?<?php echo rand(); ?>"></script>

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
        <div class="row"> 
          <div class="col-md-4">
            <component-text-label label="Código:" id="codigo" name="codigo" placeholder="Código" title="codigo"></component-text-label>
            <!--<component-date-label label="Hasta:" id="dateHasta" name="dateHasta" placeholder="Hasta fecha" title="HastaFecha"></component-date-label>-->
          </div>

          <div class="col-md-4" style="text-align: left;">
            <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                <span><label> Tipo persona: </label></span>
            </div>
            
            <div class="col-xs-9 col-md-9">
                <select id="regimen" name="regimen" class="regimen form-control">;
                    <option value="0">Seleccionar..</option>
                    <option value="1">Física</option>
                    <option value="2">Moral</option>
                </select>
            </div>
          </div>

          <div class="col-md-4">
            <component-text-label label="RFC:" id="rfc" name="rfc" placeholder="RFC" title="rfc"></component-text-label >
          </div>

          <div class="col-md-4">
          <!-- <component-text-label label="RFC:" id="rfc" name="rfc" placeholder="RFC" title="rfc"></component-text-label > -->
          </div>
        </div>

        <div class="row"> 
          <div class="col-md-4">
            <component-text-label label="Descripción:" id="txtDescripcion" name="txtDescripcion" placeholder="Descripción" title="Nombre o Razon Social de proveedor"></component-text-label>
            <!--<component-date-label label="Hasta:" id="dateHasta" name="dateHasta" placeholder="Hasta fecha" title="HastaFecha"></component-date-label>-->
          </div>
        </div>

        <!-- <div class="col-md-4">-->
          
         <!--  <div class="form-inline row">
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
          </div>-->
          
        <!-- </div> -->

        <!-- <div class="col-md-4"> columna  en medio -->
       
     <!-- <component-text-label label="RFC:" id="rfc" name="rfc" placeholder="RFC" title="rfc"></component-text-label > -->

    <!--  <div class="row pt20" style="text-align: left;">
        <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
            <span><label> Tipo persona: </label></span>
        </div>
        <div class="col-xs-9 col-md-9">
            <select id="regimen" name="regimen" class="regimen form-control">;

                <option value="0">Seleccionar..</option>

                <option value="1">Física</option>

                <option value="2">Moral</option>

            </select>
        </div>
    </div> -->
        <!--<component-text-label label="Cuenta contable:" id="cuenta" name="cuenta" placeholder="cuenta" title="DesdeFecha"></component-text-label > ad24-->

        <!--<component-text-label label="Domicilio:" id="domicilio" name="domicilio" placeholder="Domicilio" title="Domicilio" class="pt20"></component-text-label > AD24-->

        
        <!-- </div> -->

        <!-- <div class="col-md-4">
        <component-text-label label="Código:" id="codigo" name="codigo" placeholder="Código" title="codigo"></component-text-label>
       
          <component-date-label label="Hasta:" id="dateHasta" name="dateHasta" placeholder="Hasta fecha" title="HastaFecha"></component-date-label>
        </div>-->
        <div>
          <component-button type="submit" id="filtrar" name="filtrar" class="glyphicon glyphicon-search" onclick="return false;" value="Filtrar"></component-button>
        </div>
      </div>
    </div>
</div><!--fin pannel criterios de busqueda -->

<!-- Mostrar Tabla de datos -->
<div name="tablaSupp" id="tablaSupp">
  <div name="datosSupp" id="datosSupp"></div>
</div>

<form class='form-inline' action='modelo/Beneficiarios/main.php' method="POST" enctype="multipart/form-data" target='valid'>
    <div class="form-group">
        <label> CargaMasiva: </label>
        <input type="file" multiple='' name='files[]' id='files'/>
    </div>
</form>

<div class="embed-responsive embed-responsive-1by1">
    <iframe class="embed-responsive-item" name='valid'>
        
    </iframe>
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

<script type="text/javascript">
  fnFormatoSelectGeneral(".regimen");
</script>