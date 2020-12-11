<?php
/**
 * ABC de Almacén
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Se realizan operación pero el Alta, Baja y Modificación
 */

$PageSecurity = 8;
include('includes/session.inc');
$funcion=30;
$title = traeNombreFuncion($funcion, $db,'Catálogo de Proveedores');
include "includes/SecurityUrl.php";
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');
$info=array();

include 'javascripts/libreriasGrid.inc';
?>

<script type="text/javascript" src="javascripts/suppliers_alta.js?<?php echo rand(); ?>"></script>

<div class="col-xs-12 col-md-12 text-left"> 

<div class="panel panel-default">

    <div role="tab" id="headingOne" class="panel-heading text-left">
        <h4 class="panel-title row">
            <div class="col-md-3 col-xs-3">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#datosPrincipales" aria-expanded="true" aria-controls="collapseOne" class="collapsed"><span class="glyphicon glyphicon-chevron-down"></span>
               Datos proveedor
                </a>
            </div>
        </h4>
    </div>
    <div id="datosPrincipales" name="datosPrincipales" role="tabpanel" aria-labelledby="headingOne" class="panel-collapse collapse in"><br>

<form id="proveedorAlta">
<div class="text-left container">



 <div class="row clearfix" id="fila1">

  <div class="col-xs-12 col-md-4 pt20">
    <component-text-label label="Código:" id="SupplierID" name="SupplierID" placeholder="Código" title="Código" maxlength="10" value=""></component-text-label>
</div>


<div class="col-xs-12 col-md-4 pt20">

    <div class="row" style="text-align: left;">
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
</div>

<div class="col-xs-12 col-md-4  pt20">
        <div class="row" style="text-align: left;">
            <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                <span><label>Tipo proveedor: </label></span>
            </div>
            <div class="col-xs-9 col-md-9">
              <select id="tipotercero" name="tipotercero" class="form-control selectTipoProveedor">
              </select>
            </div>
        </div>
    </div>

<!--  <div class="col-xs-12 col-md-4 pt20" id="moralRazonSocial" style="display: none;" >
        <component-text-label label="Razón Social:" id="moralSuppname" name="moralSuppName" 
    placeholder="Razón Social" title="Razón Social" maxlength="180"
    value=""></component-text-label>

    </div> -->

 </div><!-- fin fila1-->

<div class="row clearfix" id="fila2">
  <div class="col-xs-12 col-md-4 pt20" id="" style="">
    <component-text-label label="Nombre/Razón Social:" id="SuppName" name="SuppName" placeholder="Nombre/Razón Social" title="Nombre" maxlength="180" value=""></component-text-label>
  </div>
  <div class="col-xs-12 col-md-4  pt20 clRFC" style=""  id="fisicaRfc">
    <component-text-label label="RFC:" id="taxid" name="taxid" placeholder="RFC" title="RFC" maxlength="13" value="" ></component-text-label>
  </div>
  <div class="col-xs-12 col-md-4  pt20 clRFC" style="display: none;"  id="moralRfc">
    <component-text-label label="RFC:" id="moraltaxid" name="moraltaxid" placeholder="RFC" title="RFC" maxlength="12" value=""></component-text-label>
  </div>
  <div class="col-xs-12 col-md-4  pt20" style=""  id="fisicaCurp">
    <component-text-label label="CURP:" id="curp" name="curp" placeholder="CURP" title="CURP" maxlength="18" value=""></component-text-label>
  </div>
</div><!--fila2-->

<div class="row clearfix" id="fila2">
  <div class="col-xs-12 col-md-8 pt20" id="" style="">
    <component-text-label label="Representante legal:" id="representanteLegal" name="representanteLegal" placeholder="Representante legal" maxlength="50" value=""></component-text-label>
  </div>
  <div class="col-xs-12 col-md-4 pt20" id="" style="">
    <div class="form-inline row">
        <div class="col-md-3 col-xs-12" >
            <label>TESOFE: </label>
        </div>
        <div class="col-md-9 col-xs-12">
            <input type="checkbox" id="checkTesofe" name="checkTesofe" value="0" placeholder="TESOFE" title="TESOFE" class="form-control" />
        </div>
    </div>
  </div>
</div>

 <div class="row clearfix" id="fila3" > 

  <!-- <div class="col-xs-12 col-md-4  pt20">
    <component-text-label label="Estado:" id="Address4" name="Address4" 
    placeholder="Estado" title="Estado" maxlength="40"
    value=""></component-text-label>
  </div> -->

   <div class="col-xs-12 col-md-4 pt20">
    <?php
    $a=1;
    $result=DB_query('SELECT * FROM tb_cat_entidad_federativa ORDER BY id_nu_entidad_federativa ASC', $db);
    echo '
    <div class="row" style="text-align: left;">
            <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                <span><label>Estado: </label></span>
            </div>
            <div class="col-xs-9 col-md-9">
                <select id="EstadoSel" name="Address4" class="form-control EstadoSel">
                <option value="0">Seleccionar..</option>';

    while ($myrow = DB_fetch_array($result)) {
        // if ($_POST['CurrCode'] == $myrow['currabrev']) {
        //     echo '<option selected VALUE=' . $myrow['currabrev'] . '>' . $myrow['currency'] . '</option>';
        // } else {
        echo '<option VALUE=' . $myrow['id_nu_entidad_federativa'] . '>' .$myrow['id_nu_entidad_federativa'] ." - ". $myrow['ln_nombre_entidad_federativa'] . '</option>';
        //}
        //$a++;
    }
    DB_data_seek($result, 0);
    echo '</select>
        </div>
    </div>'
    ?>
</div>


    <div class="col-xs-12 col-md-4 pt20"> <!-- tambien se toma. como ciudad-->
    <!-- <component-text-label label="Municipio:" id="Address3" name="Address3"
                          placeholder="Municipio" title="Municipio" maxlength="40"
                          value=""></component-text-label> -->

            <div class="row" style="text-align: left;">
            <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                <span><label>Municipio: </label></span>
            </div>
            <div class="col-xs-9 col-md-9" id="Address3">
                <select id="Address31" name="Address31" class="form-control Address31">
                <option value="0">Seleccionar..</option>';
                
       </select>
        </div>
    </div>
</div>


   

 <div class="col-xs-12 col-md-4 pt20">
    <component-text-label label="C.P:" id="Address5" name="Address5" 
    placeholder="C.P" title="C.P" maxlength="5"
    value="" onkeypress="return soloNumeros(event)" ></component-text-label>
  </div>

 </div><!--fin fila3-->


  <div class="row clearfix" id="fila4" > 

     <div class="col-xs-12 col-md-4 pt20"> 
     <component-text-label label="Colonia:" id="Address2" name="Address2" 
    placeholder="Colonia" title="Colonia" maxlength="40"
    value=""></component-text-label>
    </div>


    <div class="col-xs-12 col-md-4 pt20">
    <component-text-label label="Calle:" id="Address1" name="Address1"
                          placeholder="Calle" title="Calle" maxlength="40"
                          value=""></component-text-label>
    </div>


   
 <div class="col-xs-12 col-md-4 pt20">
    <component-text-label label="No. Exterior:" id="exterior" name="exterior" 
    placeholder="No. Exterior:"  maxlength="5"
    value="" onkeypress="return validaNumLetrasGuion(event)" ></component-text-label>
  </div>


  </div><!-- fin fila4-->


  <div class="row clearfix" id="fila5" > 


   
 <div class="col-xs-12 col-md-4 pt20">
    <component-text-label label="No. Interior:" id="interior" name="interior" 
    placeholder="No. Interior:"  maxlength="5"
    value="" onkeypress="return validaNumLetrasGuion(event)" ></component-text-label>
  </div>

     <div class="col-xs-12 col-md-4 pt20"> 

     <component-text-label label="Teléfono:" id="Address6" name="Address6" 
    placeholder="Teléfono" title="Teléfono" onkeypress="return soloNumeros(event)" maxlength="10"
    value=""></component-text-label>

    </div>


     <div class="col-xs-12 col-md-4 pt20">
      <component-text-label label="Email:" id="Email" name="Email"  placeholder="Email" title="Email" maxlength="100"
    value=""></component-text-label>
    
  <!--   <div class="form-inline row">
        <div class="col-md-3 col-xs-12" >
            <span><label>Email</label></span>
        </div>
        <div class="col-md-9 col-xs-12">
            <input type="email" id="Email" name="Email" value="Email" placeholder="Email"  required class="form-control"  />
        </div>
    </div><br> -->

   </div>


  </div> <!-- fin fila5-->

  <div class="row clearfix" id="fila6" >
    <div class="col-xs-12 col-md-4 pt20">
        <?php
        $result=DB_query('SELECT currency, currabrev FROM currencies ORDER BY rate DESC', $db);
        echo '
        <div class="row" style="text-align: left;">
                <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                    <span><label>Moneda: </label></span>
                </div>
                <div class="col-xs-9 col-md-9">
                    <select id="CurrCode" name="CurrCode" class="form-control CurrCode">';
        while ($myrow = DB_fetch_array($result)) {
            // if ($_POST['CurrCode'] == $myrow['currabrev']) {
            //     echo '<option selected VALUE=' . $myrow['currabrev'] . '>' . $myrow['currency'] . '</option>';
            // } else {
            echo '<option VALUE=' . $myrow['currabrev'] . '>' . $myrow['currency'] . '</option>';
            //}
        }
        DB_data_seek($result, 0);
        echo '</select>
            </div>
        </div>'
        ?>
    </div>

    <div class="col-xs-12 col-md-4 pt20">
      <div class="row" style="text-align: left;">
        <div class="col-xs-3 col-md-3" style="">
          <span><label>Cuenta contable: </label></span>
        </div>
        <div class="col-xs-9 col-md-9">
          <input type="text"  class="form-control buscarCuenta"  placeholder="Recepción Cargo" class="form-control" style="width:100%" id="cuenta__cuentasCon" autocomplete="off" />
          <input type="hidden" name="cuentasCon" id="cuentasCon" value="">
          <div id ="sugerencia-cuentasCon" style="position:absolute; z-index:999; display:block;"></div>
          <!--<select id="cuentasCon" name="cuentasCon" class="cuentasCon" required></select>-->
        </div>
      </div>
    </div>
    <!-- terminos pago-->

    <div class="col-xs-12 col-md-4 pt20">
      <div class="row" style="text-align: left;">
          <div class="col-xs-3 col-md-3" style="">
            <span><label>Activo: </label></span>
          </div>
          <div class="col-xs-9 col-md-9">
            <select id="activoSupp" name="activoSupp" class="activoSupp form-control">;
              <option value="-1">Seleccionar..</option>
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
      </div>
    </div>
  </div>

  <div class="row clearfix" id="fila6" >
    <div class="col-xs-12 col-md-4 pt20">
      <div class="row" style="text-align: left;">
          <div class="col-xs-3 col-md-3" style="">
            <span><label>Retenciones: </label></span>
          </div>
          <div class="col-xs-9 col-md-9">
            <select id="retencionesProveedor" name="retencionesProveedor" class="retencionesProveedor form-control" multiple="true">
            </select>
          </div>
      </div>
    </div>
  </div>
  

</div><!-- fin container-->
</form>



<div class="row text-center pt150"> 
<br> <br>
<?php if($_POST['mod']==0){ ?>
 <button onclick="" class="btn botonVerde" id="btnGuardarSupp"> Guardar Datos</button>
<?php }?>

</div>

</div> <!-- fin container1-->




</div><!--fin panel datosPrincipales-->


<div class="panel panel-default" id="panelBancos" style="display: none;">

    <div role="tab" id="headingOne" class="panel-heading text-left">
        <h4 class="panel-title row">
            <div class="col-md-3 col-xs-3">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#cuentasBancos" aria-expanded="true" aria-controls="collapse" class="collapsed"><span class="glyphicon glyphicon-chevron-down"></span>
               Cuentas bancos
                </a>
            </div>
        </h4>
    </div>
    <div id="cuentasBancos" name="cuentasBancos" role="tabpanel" aria-labelledby="headingOne" class="panel-collapse collapse in"><br>

<div class="text-left container">


<div name="tablaCuentasBancarias" id="tablaCuentasBancarias">
  <div name="datosCuentasBancarias" id="datosCuentasBancarias"></div>
</div>


<div class="col-xs-12 col-md-12 text-center">
  <br>
<?php if($_POST['mod']==0){ ?>
<component-button type="button" id="btnCuentaBank" name="btnCuentaBank"  value="Agregar Cuenta bancaria" class="glyphicon glyphicon-plus"></component-button>
<?php } ?>
</div>

</div>
</div><!-- fin cuentas bancos-->


</div> <!-- fin fila -->



<div class="panel panel-default" id="panelPartidas" style="display: none;">

    <div role="tab" id="headingOne" class="panel-heading text-left">
        <h4 class="panel-title row">
            <div class="col-md-3 col-xs-3">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#partidas" aria-expanded="true" aria-controls="collapse" class="collapsed"><span class="glyphicon glyphicon-chevron-down"></span>
               Partidas
                </a>
            </div>
        </h4>
    </div>
    <div id="partidas" name="partidas" role="tabpanel" aria-labelledby="headingOne" class="panel-collapse collapse in"><br>

<div class="text-left container">


<div name="tablaPartidas" id="tablaPartidas">
  <div name="datosPartidas" id="datosPartidas"></div>
</div>


<div class="col-xs-12 col-md-12 text-center">
  <br>
  <?php if($_POST['mod']==0){ ?>
<component-button type="button" id="btnPartida" name="btn"  value="Agregar Partida" class="glyphicon glyphicon-plus"></component-button>
<?} ?>
</div>

</div>
</div><!-- fin cuentas bancos-->


</div> <!-- fin fila -->


<div class="row text-center pt40"> 

 <button onclick="fnRegresar()" class="btn botonVerde"> Regresar</button>
</div>


<div class="modal fade" id="ModalCuentaBank" name="ModalCuentaBank" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
        <div class="modal-content">
            <div class="navbar navbar-inverse navbar-static-top">

                <div class=" menu-usuario">
                    <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <div class="nav navbar-nav">
                        <div class="title-header">
                            <div id="ModalCuentaBank_Titulo" name="ModalCuentaBank_Titulo"></div>
                        </div>
                    </div>
                </div>
                <div class="linea-verde"></div>
            </div>
            <div class="modal-body" id="ModalInfoPago_Mensaje" name="ModalInfoPago_Mensaje">
              <div class="row clearfix">
              <div class="row clearfix col-xs-12 col-md-12">
               

              <div class="col-xs-12 col-md-12 text-left" style="background-color:#f2dede !important;color:#a94442; display: none;" id="valMsgCountBank" > <br>
                
              </div>

             

            </div>

                <div>

                    <div class="col-md-4" id="">

                        <div id="bancoExistentes"> </div>

                    </div>

                    <div class="col-xs-12 col-md-4">
                        <component-text-label label="Referencia:" id="ref" name="ref"
                                              placeholder="Referencia" title="ref" maxlength="105"
                                              value=""></component-text-label>
                        <br>
                       

                    </div>


                    <div class="col-xs-12 col-md-4">
                        <component-text-label label="No. cuenta:" id="cuenta" name="cuenta"
                                              placeholder="No. cuenta" title="cuenta" maxlength="10"
                                              value="" onkeypress="return soloNumeros(event)"></component-text-label>
                                              <input type="hidden" id="valClabe" />

                    </div>

                    <div class="col-xs-12 col-md-10 text-center">

                          <component-text-label label="CLABE interbancaria:" id="clabe" name="clabe"
                                              placeholder="CLABE interbancaria" title="Clabe interbancaria"  maxlength="18"
                                              value="" onkeypress="return soloNumeros(event)" ></component-text-label>

                                              <br>
                     </div>

                   </div>

                    <!-- fin contenido-->
                </div>
                <br> <br> <br>
                <div class="modal-footer">
                    <!--contenido -->

                    <div class="col-xs-6 col-md-6 text-right">
                        <div id="procesandoPagoEspere"> </div> <br>



                        <button class="btn bgc8" style="color:white; font-weight: bold;" id="btnSaveCBank">Guardar </button>

                        <button class="btn bgc8" style="color:white; font-weight: bold;" id="btnCerrarPago" name="btnCerrarPago" data-dismiss="modal" >Cerrar</button>

                    </div><!-- fin moddal -->

                </div>

                <!--fin contenido footer -->
            </div>
        </div>
    </div>
</div>




<div class="modal fade" id="ModalPartidas" name="ModalPartidas" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
        <div class="modal-content">
            <div class="navbar navbar-inverse navbar-static-top">

                <div class=" menu-usuario">
                    <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <div class="nav navbar-nav">
                        <div class="title-header">
                            <div id="ModalPartidas_Titulo" name="ModalPartidas_Titulo"></div>
                        </div>
                    </div>
                </div>
                <div class="linea-verde"></div>
            </div>
            <div class="modal-body" id="ModalInfoPago_Mensaje" name="ModalInfoPago_Mensaje">
                <div class="row clearfix">
<form name="partidasForm" id="partidasForm"> 
      <?php
$sql = 'SELECT descripcion,ccapmiles FROM tb_cat_partidaspresupuestales_capitulo WHERE ccapmiles IN(2000,3000,5000);';
$result = DB_query($sql, $db);

echo '<div class="col-xs-12 col-md-12 pt20">
    <div class="row" style="text-align: left;">
            <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                <span><label>Capítulo: </label></span>
            </div>
            <div class="col-xs-9 col-md-9">

                <select id="partidapresupuestal" name="partidapresupuestal[]" class="partidapresupuestal" multiple="multiple" >';

                while ($myrow = DB_fetch_array($result)) {

/*  este no* if ($myrow['taxgroupid'] == $_POST['TaxGroup']) {
     echo '<option selected VALUE=';
 } else {
     echo '<option VALUE=';
 }
 echo $myrow['taxgroupid'] . '>' . $myrow['taxgroupdescription'] . '</option>'; este no*/

$n=str_replace("000","", $myrow['ccapmiles']);

 echo '<option value="'.$myrow['ccapmiles'].'">'.$n." - ".$myrow['descripcion'].'</option>';

}

echo '</select>

</div>
</div>
</div>';


?>

<div class="col-xs-12 col-md-12 pt20">
    <div class="row" style="text-align: left; " id="divConcepto">
        <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
            <span><label>Concepto : </label></span>
        </div>
        <div class="col-xs-9 col-md-9" id="presupuestalconceptoespacio">

          <select id="partida1F" name="" class="partida1F form-control">;

                <option value="0">Seleccionar..</option>

            </select>

        </div>
    </div>
</div>

<div class="col-xs-12 col-md-12 pt20">
    <div class="row" style="text-align: left;">
        <div class="col-xs-3 col-md-3" style="vertical-align: middle;" id="divPartida">
            <span><label>Partida especifica: </label></span>
        </div>
        <div class="col-xs-9 col-md-9" id="partidagenericaespacio">

          <select id="partida2FF" name="" class="partida2FF form-control">;

                <option value="0">Seleccionar..</option>

            </select>
                
        </div>
    </div>
</div>
</form>


                    <!-- fin contenido-->
                </div>
                <br> <br> <br>
                <div class="modal-footer">
                    <!--contenido -->

                    <div class="col-xs-6 col-md-6 text-right">
                        <div id="procesandoPagoEspere"> </div> <br>



                        <button class="btn bgc8" style="color:white; font-weight: bold;" id="btnSavePartida" onclick="fnGuardarPartidas()">Guardar </button>

                        <button class="btn bgc8" style="color:white; font-weight: bold;" id="btnCerrarPago" name="btnCerrarPago" data-dismiss="modal" >Cerrar</button>

                    </div><!-- fin moddal -->

                </div>

                <!--fin contenido footer -->
            </div>
        </div>
    </div>
</div>

</div>

<div id="ModalGeneral1" name="ModalGeneral1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false" class="modal fade ui-draggable out" style="display: none; padding-left: 0px;">


    <div role="document" name="ModalGeneral1Tam" id="ModalGeneral1Tam" class="modal-dialog ui-draggable-handle modal-md"><div class="modal-content"><div class="navbar navbar-inverse navbar-static-top">

        <div class="col-md-12 menu-usuario"><span data-dismiss="modal" class="glyphicon glyphicon-remove"></span></div>

        <div id="navbar" class="navbar-collapse collapse"><div class="nav navbar-nav"><div class="title-header"><div id="ModalGeneral1_Titulo" name="ModalGeneral1_Titulo"><h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3></div></div></div></div> <div class="linea-verde"></div>

    </div> <div class="modal-body">
    <input type="hidden" id="tipoMg" value="">
    <input type="hidden" id="accionMg" value="">
      <div id="ModalGeneral1_Advertencia" name="ModalGeneral1_Advertencia"></div> <div id="ModalGeneral1_Mensaje" name="ModalGeneral1_Mensaje">
    

    </div></div> <div class="modal-footer"><div id="ModalGeneral1_Pie" name="ModalGeneral1_Pie">            <div class="input-group pull-right">                <button class="btn btn-default botonVerde" onclick="" id="confirmacionModalGeneral1">Si</button>                <button class="btn btn-default botonVerde" data-dismiss="modal" id="btnCerrarModalGeneral1" name="btnCerrarModalGeneral1">No</button>            </div></div></div></div></div></div>
<!--fin modal-->
</div>
<?php
require 'includes/footer_Index.inc';
?>

<?php
if(isset($_POST['idSupp'])){ 
?>
<script type="text/javascript">

$( document ).ready(function() {
    //fnFormatoSelectGeneral(".tipotercero");
    fnFormatoSelectGeneral(".partidapresupuestal");
    fnFormatoSelectGeneral(".CurrCode");
    //fnFormatoSelectGeneral(".PaymentTerms");
    //fnFormatoSelectGeneral(".Typeid");
    fnFormatoSelectGeneral(".regimen");
    fnFormatoSelectGeneral(".partida1F");
    fnFormatoSelectGeneral(".partida2FF");
    fnFormatoSelectGeneral(".EstadoSel");
    fnFormatoSelectGeneral(".Address3");
    fnFormatoSelectGeneral(".activoSupp");
  $('#panelBancos').show();
  $('#panelPartidas').show();
  
  $("#btnGuardarSupp").prop('onclick', null);
  $("#btnGuardarSupp").attr('onclick', 'fnModificar()');
   
    var url ="modelo/SelectSupplierModelo.php";
  
    //Opcion para operacion
    dataObj = { 
            proceso: 'proveedor',
            idSupp: <?php echo "'".$_POST['idSupp']."'"; ?>
          };
    //Obtener datos de las bahias
    $.ajax({
          method: "POST",
          dataType:"json",
          url:url,
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){
            datos=data.contenido.datosProv;
            partidas=data.contenido.partidas;
            cuenta=data.contenido.cuenta;
            retenciones=data.contenido.retenciones;

            setTimeout(function(){ 
              $('#SupplierID').val(datos[0].idSupp); 
              $('#SuppName').val(datos[0].nombre);
              $('#Address1').val(datos[0].ad1);
              $('#Address2').val(datos[0].ad2);
              //$('#muni').val(datos[0].ad3);
              $('#Address4').val(datos[0].ad4);
              $('#Address5').val(datos[0].ad5);
              $('#Address6').val(datos[0].ad6);
              $('#Email').val(datos[0].email);
              $('#banco').val(datos[0].banco);
              $('#ref').val(datos[0].ref);
              $('#cuenta').val(datos[0].cuenta);
              $('#desdeprov').val(datos[0].desdeSupp);
              $('#representanteLegal').val(datos[0].represnetante);
              $('#interior').val(datos[0].interior);
              $('#exterior').val(datos[0].exterior);
              //fnSeleccionarDatosSelect("tipotercero",datos[0].tercero);
              $('#tipotercero').val(''+datos[0].tipoid);
              $('#tipotercero').multiselect('rebuild');

              //fnSeleccionarDatosSelect("Typeid",datos[0].tipoid);
              //fnSeleccionarDatosSelect("PaymentTerms",datos[0].terminos);
              fnSeleccionarDatosSelect("activoSupp",datos[0].activo);

              if (datos[0].tesofe == 1) {
                $('#checkTesofe').attr('checked', true);
              } else {
                $('#checkTesofe').attr('checked', false);
              }

              fnSeleccionarDatosSelect("regimen",datos[0].tipoPersona);
              if(datos[0].tipoPersona=='2'){
                 $('#moraltaxid').val(datos[0].rfc);
                 $('#moralRfc').show();

                 $('#fisicaCurp').hide();
                //$('#fisicaNombre').hide();
                 $('#fisicaRfc').hide();

              }else{
                $('#taxid').val(datos[0].rfc);
                $('#curp').val(datos[0].curp);
              }

              fnSeleccionarDatosSelect("EstadoSel",datos[0].ad4);


              fnMunicipio(datos[0].ad3);
              //console.log(datos[0].ad3);
               //console.log(datos[0].ad4 +"--------");
              //fnSeleccionarDatosSelect("CurrCode",datos[0].tipocambio);
              //$('#SupplierID').('disable');
              $('#SupplierID').prop('readonly', true);
              $('#SupplierID').attr('readonly', true);
              var capitulos= new Array();
              var conceptos= new Array();
              
              if(partidas.length>0){
                for (a in partidas){
                  aux= partidas[a];
                  aux=aux.substring(0, 1);
                  capitulos.push(aux+"000");

                  aux1= partidas[a];
                  aux1=aux1.substring(0, 2);
                  conceptos.push(aux1+"00");
                }
                //console.log(capitulos);
                var capitulosUnicos = [];
                $.each(capitulos, function(i, el){
                    if($.inArray(el,capitulosUnicos) === -1) capitulosUnicos.push(el);
                });

                $('#'+'partidapresupuestal').selectpicker('val', capitulosUnicos );
                $('#'+'partidapresupuestal').multiselect('refresh');
                $('.'+'partidapresupuestal').css("display", "none");
            
                fnMultiplesCapitulo(capitulosUnicos,conceptos);
                fnMultiplesConceptos(conceptos,partidas);
              }

              // console.log("retenciones: "+JSON.stringify(retenciones));
              if (retenciones.length > 0) {
                // tiene retenciones configuradas
                var retencionesSelect = [];
                for (var key in retenciones) {
                  retencionesSelect.push(retenciones[key].retencion);
                }

                $('#'+'retencionesProveedor').selectpicker('val', retencionesSelect );
                $('#'+'retencionesProveedor').multiselect('refresh');
                $('.'+'retencionesProveedor').css("display", "none");
              }
              //fnSeleccionarDatosSelect("cuentasCon",cuenta);
              $('#cuenta__cuentasCon').val(cuenta);
              $('#cuentasCon').val(cuenta);
            }, 500);
        }
    })
    .fail(function(result) {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo,"Hubo un error al mostrar los datos del proveedor"); 
        console.log("ERROR");
        console.log( result );
    });
    //// Se agregan líneas para vaciar las partidas precargadas
    setTimeout(function () {
      $("#partidapresupuestal option:selected").prop("selected", false);
      $("#partidapresupuestal").multiselect('rebuild');
      $("#presuuestaloncepto option:selected").prop("selected", false);
      $("#presuuestaloncepto").multiselect('rebuild');
      $("#partidagenerica option:selected").prop("selected", false);
      $("#partidagenerica").multiselect('rebuild');
    }, 5000);
});

//fnCuentasContablesProv(<?php //echo "'".$_POST['idSupp']."'"; ?>);
fnCountsBanksProv(<?php echo "'".$_POST['idSupp']."'"; ?>);
fnPartidas(<?php echo "'".$_POST['idSupp']."'"; ?>);


</script>

<?php
} else{ ?>
<script type="text/javascript">
  
  $(document).ready(function(){

    //fnFormatoSelectGeneral(".tipotercero");
    fnFormatoSelectGeneral(".partidapresupuestal");
    fnFormatoSelectGeneral(".CurrCode");
    //fnFormatoSelectGeneral(".PaymentTerms");
    //fnFormatoSelectGeneral(".Typeid");
    fnFormatoSelectGeneral(".regimen");
    fnFormatoSelectGeneral(".partida1F");
    fnFormatoSelectGeneral(".partida2FF");
    fnFormatoSelectGeneral(".EstadoSel");
    fnFormatoSelectGeneral(".Address3");
    fnFormatoSelectGeneral(".activoSupp");

     $("#btnGuardarSupp").prop('onclick', null);
     $("#btnGuardarSupp").attr('onclick', 'fnGuardar()');
  });
</script>
<?php
} ?>
<?php if($_POST['mod']==0){?>
<script type="text/javascript">
  $(document).on('cellselect', ' #datosCuentasBancarias', function(event) {
    solicitudEnlace = event.args.datafield;
    fila = event.args.rowindex;
      event.preventDefault();
        event.stopPropagation();
    if (solicitudEnlace == 'desactivar') {
            
            accion='';
            idSupp = $('#datosCuentasBancarias').jqxGrid('getcellvalue', fila, 'idSupp');  
            cuenta = $('#datosCuentasBancarias').jqxGrid('getcellvalue', fila, 'cuenta');
            clabe = $('#datosCuentasBancarias').jqxGrid('getcellvalue', fila, 'clabe');
            desactivar = $('#datosCuentasBancarias').jqxGrid('getcellvalue', fila, 'desactivar');
            
            accion = $('<div>').append(desactivar).find('a').prop('class');
            //console.log(accion);
             
               console.log(accion);
              fnMuestraModal(accion,'cuenta',false);
            
    }
    return false;
});

$(document).on('cellselect', '#tablaPartidas > #datosPartidas', function(event) {
    solicitudEnlace = event.args.datafield;
    fila = event.args.rowindex;
    event.preventDefault();
    event.stopPropagation();
    if (solicitudEnlace == 'desactivar') {
            
            accion='';
            partida = $('#tablaPartidas > #datosPartidas').jqxGrid('getcellvalue', fila, 'partida');  
            desactivar = $('#tablaPartidas > #datosPartidas').jqxGrid('getcellvalue', fila, 'desactivar');
            
            accion = $('<div>').append(desactivar).find('a').prop('class');
            console.log(accion);
            fnMuestraModal(accion,'partida');    
            
    }
  return false;
});
</script>
<?php }else{ ?>
<script type="text/javascript">
  fnBloquearDivs('proveedorAlta');
</script>
<?php } ?>
