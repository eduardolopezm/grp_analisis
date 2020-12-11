<?php
/**
 * abc de Activo fijo
 *
 * @category ABC
 * @package ap_grp
 * @author Jorge Cesar Garcia Baltazar <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/07/2017
 * Fecha Modificación: 31/07/2017
 * Se realizan operación pero el Alta, Baja y Modificación
 */
include "includes/SecurityUrl.php";
$PageSecurity=5;
include('includes/session.inc');
$funcion=2307;
$title = traeNombreFuncion($funcion, $db, 'Alta de Bien Patrimonial');

include('includes/header.inc');
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

//Librerias GRID
include('javascripts/libreriasGrid.inc');
?>

<!-- Panel -->
<div class="panel panel-default">


    <!-- Panel Heading -->
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
            Información Agregar/Modificar
          </a>
        </div>
      </h4>
    </div><!-- Panel Heading -->


    <!-- Panel Body -->
    <!-- <div id="divBloqueo" class="panel-body"> -->
      <!-- Div para bloquear -->
      <div id="PanelBusqueda" name="PanelBusqueda" class="text-left " role="tabpanel" aria-labelledby="headingOne" class="panel-collapse collapse in">

        <div id="divBloqueo" name="divBloqueo" class="text-left container-fluid">
          <br>
          <form id="frmDatos">
            <!-- Contenido de inputs -->
            <div class="col-md-12">

              <!-- UR, UE -->
              <div class="col-md-4">

                <!-- form-inline -->
                <div class="form-inline row hide">
                  <div class="col-md-3">
                    <span><label>Dependencia: </label></span>
                  </div>
                  <div class="col-md-9"> 
                    <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioRazonSocial()">
                      <option value='0'>Seleccionar...</option>
                    </select>
                  </div>
                </div><!-- form-inline -->
                
                <!-- form-inline -->
                <div class="form-inline row">
                  <div class="col-md-3">
                    <span><label>UR: </label></span>
                  </div>
                  <div class="col-md-9">
                    <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio">                    
                    </select>
                  </div>
                </div><!-- form-inline -->

                <br>

                <!-- form-inline -->
                <div class="form-inline row">
                  <div class="col-md-3">
                    <span><label>UE: </label></span>
                  </div>
                  <div class="col-md-9">
                    <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control"> 
                      
                    </select>
                  </div>
                </div><!-- form-inline -->

                <br>
              </div><!-- UR, UE -->

              <!-- Almacen, Categoria -->
              <div class="col-md-4">
                <!-- form-inline -->
                <div class="form-inline row">
                  <div class="col-md-3">
                    <span><label>Almacén: </label></span>
                  </div>
                  <div class="col-md-9"> 
                    <select id="selectAlmacen" name="selectAlmacen" class="form-control" >
                    </select>
                  </div>
                </div><!-- form-inline -->

                <br>

                <!-- form-inline -->
                <div class="form-inline row">
                  <div class="col-md-3" style="vertical-align: middle;">
                    <span><label>Partida Específica: </label></span>
                  </div>
                  <div class="col-md-9">
                    <select id="selectCategoriaActivo" name="selectCategoriaActivo" class="form-control selectCategoriaActivo" data-todos="true" ></select>
                  </div>
                </div><!-- form-inline -->
              </div>

              <!-- Proceso  -->
              <div class="col-md-4">  
                <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>Proceso: </label></span>
                  </div>
                  <div class="col-md-9"> 
                      <select id="selectProcesoContabilizarActivo" name="selectProcesoContabilizarActivo" class="form-control selectProcesoContabilizarActivo" onclick="fnSeleccionarProceso()" required="">
                        <option value='0'>Seleccionar...</option>
                      </select>
                  </div>
                </div>
                <br>
                <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>Clave CABMS: </label></span>
                  </div>
                  <div class="col-md-9"> 
                      <select id="selectClaveCABMS" name="selectClaveCABMS" class="form-control" required=""></select>
                  </div>
                </div>
              </div><!-- Proceso  -->

              <br>

            </div>

            <div class="col-md-12"> 
              <!-- Num. Inventario, clave bien,  -->
              
              <div class="col-md-4">
                <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>Tipo de Bien: </label></span>
                  </div>
                  <div class="col-md-9"> 
                      <select id="selectTipoBien" name="selectTipoBien" class="form-control selectTipoBien" required="">
                        <option value='0'>Seleccionar...</option>
                      </select>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <component-text-label label="Número de Inventario:" id="txtNumeroInventario" name="txtNumeroInventario" placeholder="Número de Inventario" title="Número de Inventario - se genera el número de inventario al guardar el activo" title="Número de Inventario" value="" maxlength="30" disabled></component-text-label>
                
              </div>  <!-- Num. Inventario, clave bien,  -->

              <!-- Descripcion Corta  -->
              <div class="col-md-4">  
                <component-text-label label="Descripción de Activos (Corta):" id="txtDescripcionCorta" name="txtDescripcionCorta" placeholder="Descripción corta" title="Descripción corta" value="" maxlength="50"></component-text-label>
                <br>
              </div><!-- Descripcion Corta  --> 

              <div class="col-md-4">
                <component-text-label label="Clave del Bien:" id="txtClaveBien" name="txtClaveBien" placeholder="Clave del Bien" title="Clave del bien" title="Clave del Bien" value="" maxlength="50"></component-text-label> 
              </div>

              <!-- Descripcion Larga  -->
              <div class="col-md-8">  
                <component-text-label label="Descripción de Activos (Larga):" id="txtDescripcionLarga" name="txtDescripcionLarga" placeholder="Descripción larga" title="Descripción larga" value="" maxlength="150"></component-text-label>
                <br>
              </div><!-- Descripcion Larga  -->

            </div>

            <!-- inventario, proveedor, asegurado por proveedor -->
            <div class="col-md-12">
              <div class="col-md-4">
                <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>Inventario: </label></span>
                  </div>
                  <div class="col-md-9"> 
                      <select id="selectTipoPropietario" name="selectTipoPropietario" class="form-control selectTipoPropietario">
                        <option value='0'>Seleccionar...</option>
                      </select>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <component-text-label label="Proveedor:" id="txtProveedor" name="txtProveedor" placeholder="Proveedor" title="Proveedor" value="" maxlength="300"></component-text-label> 
              </div>

              <div class="col-md-4">
                <div class="form-inline row">
                  <div class="col-md-7">
                      <span><label>Asegurado por Proveedor: </label></span> 
                  </div>
                  <div class="col-md-5"> 
                      <input type="checkbox" id="chkAsegurado" name="chkAsegurado" checked="true" />
                  </div>
                </div>
                <br>
              </div>

              <!-- numero factura, color, placas -->
              <div class="col-md-4">
                <component-text-label label="Número de Factura:" id="txtNumeroFactura" name="txtNumeroFactura" placeholder="Número de factura" title="Número de factura" value="" maxlength="50"></component-text-label>
              </div>

              <div class="col-md-4">
                <component-text-label label="Color:" id="txtColor" name="txtColor" placeholder="Color" title="Color" value="" maxlength="30"></component-text-label>
              </div>

              <div class="col-md-4">
                <component-text-label label="Placas:" id="txtPlacas" name="txtPlacas" placeholder="Placas" title="Placas" value="" maxlength="20"></component-text-label>
                <br>
              </div><!-- numero factura, color, placas -->
            </div>

            <!--  -->
            <div class="col-md-12">
              <div class="col-md-4">
                <component-text-label label="Tasa de depreciación:" id="txtTasaDepreciacion" name="txtTasaDepreciacion" placeholder="Tasa de depreciación" title="Tasa de depreciación:"value="" onkeypress = 'return soloNumeros(event)' maxlength="3" readonly></component-text-label>
              </div>

              <div class="col-md-4">
                <component-text-label label="Marca:" id="txtMarca" name="txtMarca"placeholder="Marca" title="Marca" value="" maxlength="50"></component-text-label>
              </div>

              <div class="col-md-4">
                <component-text-label label="Valor Factura:" id="txtCosto" name="txtCosto" placeholder="Valor Factura" title="Valor Factura" value="" onkeypress = 'return fnSoloNumeros(event)' maxlength="12"></component-text-label>
                <br>
              </div>
            </div>

            <!-- años vida util, modelo, fecha adquisicion -->
            <div class="col-md-12">
              <div class="col-md-4">
                <component-text-label label="Años de vida útil:" id="txtAniosVidaUtil" name="txtAniosVidaUtil" placeholder="Años de vida útil" title="Años de vida útil" value="" maxlength="4" readonly></component-text-label>
              </div>

              <div class="col-md-4"> 
                <component-text-label label="Modelo:" id="txtModelo" name="txtModelo" placeholder="Modelo" title="Modelo" value="" maxlength="50"></component-text-label>
              </div>

              <div class="col-md-4">
                <component-date-label label="Fecha de Adquisición: " id="txtFechaAdquisicion" name="txtFechaAdquisicion" placeholder="Fecha de Adquisición"></component-date-label>
              </div>
            </div>

            <!-- ubicacion, numero serie, fecha incorporacion -->
            <div class="col-md-12">
              <div class="col-md-4">
                <component-text-label label="Ubicación:" id="txtUbicacion" name="txtUbicacion" placeholder="Ubicación" title="Ubicación" title="Ubicación" value="" maxlength="300"></component-text-label> 
              </div>

              <div class="col-md-4">
                <component-text-label label="Número de serie:" id="txtNumeroSerie" name="txtNumeroSerie" placeholder="Número de serie" title="Número de serie" value="" maxlength="30"></component-text-label>
              </div>

              <div class="col-md-4">
                <component-date-label label="Fecha de Incorporación: " id="txtFechaIncorporacionPatrimonial" name="txtFechaIncorporacionPatrimonial" placeholder="Fecha de Incorporación Patrimonial"></component-date-label>
                  <br>
              </div>
            </div><!-- ubicacion, numero serie, fecha incorporacion -->

            <div class="col-md-12">
              <div class="col-md-4">

                  <!-- form-inline -->
                  <div class="form-inline row">
                    <div class="col-md-3">
                      <span><label>Estatus: </label></span>
                    </div>
                    <div class="col-md-9">
                      <select id="selectEstatus" name="selectEstatus" class="form-control"> 
                        <option value="1">Activo</option>                   
                        <option value="0">Inactivo</option>                   
                      </select>
                    </div>
                  </div><!-- form-inline -->
              </div>

              <div class="col-md-4">
                <component-text-label label="Año:" id="txtAnio" name="txtAnio" placeholder="Año" title="Año" title="Año" value="" maxlength="4" onkeypress = 'return fnSoloNumeros(event)'></component-text-label> 
                <br>
              </div>

            </div>

            <div class="col-md-12">
              <div class="col-md-8">
                <div class="form-inline row">
                  <div class="col-md-2">
                      <span><label>Observaciones: </label></span> 
                  </div>
                  <div class="col-md-10"> 
                      <textarea id="txtObservacion" name="txtObservacion" class="form-control" rows="3" style="width:100%"></textarea>
                      <br>
                  </div>
                </div>    
              </div>          
            </div>
            <div class="col-md-12">
              <br>
            </div>
            <br>

          </form>
          <br>



        </div>

      </div><!-- Div para bloquear -->


      

    <!-- </div> -->
    
</div>

<div class="row">
  <div class="col-md-12">
    <br>
      <div class="row text-center pt150">
          <div align="center">
            <?php if (((isset($_GET['aEr'])) && ($_GET['aEr']!='true') )||  (!isset($_GET['aEr']))) {?>
                  <component-button type="button" id="btnGuardarActivo" name="btnGuardarActivo" data-dismiss="modal" onclick="if (fnAgregar()==false) { $('#ModalUR').modal('show'); }" class="glyphicon glyphicon-floppy-disk " value="Guardar"></component-button>
            <?php }




            if ((isset($_GET['aEr'])) && ($_GET['aEr']=='true')) { ?>
                  <component-button type="button" id="btnBorrarActivo" name="btnBorrarActivo" data-dismiss="modal" onclick="if (fnBorrarActivo()==false) { $('#ModalUR').modal('show'); }" value="Borrar"></component-button>

            <?php } ?>

            <component-button type="button" id="btnCancelar" name="btnCancelar" value="Cancelar" class="glyphicon glyphicon-remove"></component-button>

            <component-button type="button" id="btnLista" name="btnLista" onclick="window.location.href = 'activofijo_panel.php'" value="Regresar" class="glyphicon glyphicon-arrow-left"></component-button>
            <br>
            <br>
          </div>
      </div>
  </div>
</div>

<script type="text/javascript" src="javascripts/activofijo.js"></script>
<script type="text/javascript" src="javascripts/Subir_Archivos.js"> </script>

<?php
if (isset($_POST['AssetId']) or isset($_GET['AssetId'])) {
    echo '<script  type="text/javascript"> var ActivoFijoID = '.$_GET['AssetId'].'; </script>';
} else {
    echo '<script  type="text/javascript"> var ActivoFijoID = 0; </script>';
}

if (isset($_POST['ver']) or isset($_GET['ver'])) {
    echo '<script  type="text/javascript"> var getVer = 1; </script>';
} else {
    echo '<script  type="text/javascript"> var getVer = 0; </script>';
}

include 'includes/footer_Index.inc';

