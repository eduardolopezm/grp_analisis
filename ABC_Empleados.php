<?php
/**
 * ABC de Empleados
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

$PageSecurity = 5;
include('includes/session.inc');
$funcion=2370;
$title = traeNombreFuncion($funcion, $db,'Catálogo de Empleados');
include "includes/SecurityUrl.php";
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');
$info=array();

include 'javascripts/libreriasGrid.inc';
?>

<script type="text/javascript" src="javascripts/ABC_Empleados.js"></script>

<div class="col-xs-12 col-md-12 text-left"> 

<div class="panel panel-default">

    <div role="tab" id="headingOne" class="panel-heading text-left">
        <h4 class="panel-title row">
            <div class="col-md-3 col-xs-3">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#datosPrincipales" aria-expanded="true" aria-controls="collapseOne" class="collapsed"><span class="glyphicon glyphicon-chevron-down"></span>
               Datos de empleado
                </a>
            </div>
        </h4>
    </div>
    <div id="datosPrincipales" name="datosPrincipales" role="tabpanel" aria-labelledby="headingOne" class="panel-collapse collapse in"><br>

<form id="empleadoAlta">
<div class="text-left container">

  <div class="row clearfix" id="fila1">
    <div class="col-md-4">
      <div class="form-inline row hide">
        <div class="col-md-3">
         <span><label>Dependencia: </label></span>
        </div>
        <div class="col-md-9">
         <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioDependeciaGeneral('selectRazonSocial', 'selectUnidadNegocio')" multiple="true"></select>
        </div>
      </div>

      <div class="form-inline row">
        <div class="col-md-3">
         <span><label>UR: </label></span>
        </div>
        <div class="col-md-9">
          <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio"> 
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
    </div>

    <div class="col-md-4">
        <component-text-label label="Clave de Empleado:" id="sn_clave_empleado" name="sn_clave_empleado" placeholder="Clave de empleado" title="Clave de Empleado" maxlength="10" value=""></component-text-label>
      <br>

        <?php
        $a=1;
        $result=DB_query('SELECT * FROM tb_cat_puesto ORDER BY id_nu_puesto ASC', $db);
        echo '
        <div class="row" style="text-align: left;">
                <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                    <span><label>Puesto: </label></span>
                </div>
                <div class="col-xs-9 col-md-9">
                    <select id="id_nu_puesto" name="id_nu_puesto" class="form-control id_nu_puesto">
                    <option value="0">Seleccionar..</option>';

        while ($myrow = DB_fetch_array($result)) {
            // if ($_POST['CurrCode'] == $myrow['currabrev']) {
            //     echo '<option selected VALUE=' . $myrow['currabrev'] . '>' . $myrow['currency'] . '</option>';
            // } else {
            echo '<option VALUE="' . $myrow['id_nu_puesto'] .'"'. ( ( array_key_exists('id_nu_puesto', $_POST) ? $_POST['id_nu_puesto'] : "" ) == $myrow['id_nu_puesto'] ? " selected" : "" ) .'>' .$myrow['id_nu_puesto'] ." - ". $myrow['sn_codigo'] . '</option>';
            //}
            //$a++;
        }
        DB_data_seek($result, 0);
        echo '</select>
            </div>
        </div>'
        ?>
    </div>

    <div class="col-md-4">
        <component-text-label label="RFC:" id="sn_rfc" name="sn_rfc" placeholder="RFC" title="RFC" maxlength="13" value="" ></component-text-label>
      <br>

        <component-text-label label="CURP:" id="sn_curp" name="sn_curp" placeholder="CURP" title="CURP" maxlength="18" value=""></component-text-label>
    </div>
  </div>

  <div class="row clearfix" id="fila3">
    <div class="col-xs-12 col-md-4 pt20" id="" style="">
      <component-text-label label="Nombre:" id="ln_nombre" name="ln_nombre" placeholder="Nombre" title="Nombre" maxlength="250" value=""></component-text-label>
    </div>

    <div class="col-xs-12 col-md-4 pt20" id="" style="">
      <component-text-label label="Primer apellido:" id="sn_primer_apellido" name="sn_primer_apellido" placeholder="Primer apellido" title="Apellido Paterno" maxlength="50" value=""></component-text-label>
    </div>

    <div class="col-xs-12 col-md-4 pt20" id="" style="">
      <component-text-label label="Segundo apellido:" id="sn_segundo_apellido" name="sn_segundo_apellido" placeholder="Segundo apellido" title="Apellido Materno" maxlength="50" value=""></component-text-label>
    </div>

  </div><!-- fin fila3-->
  <br>

  <div class="row clearfix" id="fila4">

    <div class="col-md-4">
      <div class="row" style="text-align: left;">
        <div class="col-md-3" style="vertical-align: middle;">
          <span><label>Cuenta contable: </label></span>
        </div>
        <div class="col-md-9">
          <input type="text" class="form-control buscarCuenta" placeholder="Cuenta contable" class="form-control" style="width:100%" id="cuenta__cuentasCon" autocomplete="off" />
          <input type="hidden" name="cuentasCon" id="cuentasCon" value="" required>
          <div id ="sugerencia-cuentasCon" style="position:absolute; z-index:999; display:block;"></div>
        </div>
        <!-- <div class="col-xs-3 col-md-3" style="">
          <span><label>Cuenta contable: </label></span>
        </div>
        <div class="col-xs-9 col-md-9 ">
          <select id="cuentasCon" name="cuentasCon" class="cuentasCon" required></select>
        </div> -->
      </div>
    </div>

    <div class="col-md-4">
      <div class="row" style="text-align: left;">
        <div class="col-xs-3 col-md-3" style="">
            <span><label>Estatus: </label></span>
        </div>
        <div class="col-xs-9 col-md-9 ">
          <select id="activoEmp" name="activoEmp" class="activoEmp form-control">
            <option value="-1">Seleccionar..</option>
            <option value="1">Activo</option>
            <option value="2">Inactivo</option>
          </select>
        </div>
      </div>
    </div>
  </div><!-- fin fila4-->

  <!--<div class="row clearfix" id="fila5">

    <div class="col-xs-12 col-md-4 pt20" id="" style="">
      <div id="bancoExistentes"></div>
    </div>

    <div class="col-xs-12 col-md-4 pt20" id="" style="">
      <component-text-label label="No. cuenta:" id="cuenta" name="cuenta" placeholder="No. cuenta" title="cuenta" maxlength="10" value="" onkeypress="return soloNumeros(event)"></component-text-label>
      <input type="hidden" id="valClabe" />
    </div>

    <div class="col-xs-12 col-md-4 pt20" id="" style="">
      <component-text-label label="CLABE interbancaria:" id="clabe" name="clabe" placeholder="CLABE interbancaria" title="Clabe interbancaria"  maxlength="18" value="" onkeypress="return soloNumeros(event)" ></component-text-label>
    </div>

  </div>--><!-- fin fila5-->


</div><!-- fin container-->
<component-text type="hidden" label="ID:" id="id_nu_empleado" name="id_nu_empleado" placeholder="ID Empleado" title="IDEmpleado" maxlength="10" value=""></component-text-label>
</form>

</div> <!-- fin container4-->



<div class="row text-center pt150"> 
<br> <br><?php if($_POST['mod']==0){ ?>
  <button onclick="" class="btn botonVerde glyphicon glyphicon-floppy-disk" id="btnGuardarEmp"> Guardar </button>
  <button type="button" id="idBtnCancelarCR" name="btnCancelarCR" onclick="document.getElementById('empleadoAlta').reset()" class="btn btn-default botonVerde glyphicon glyphicon-remove"> Cancelar</button>
  <?php } ?>
  <button onclick="fnRegresar()" class="btn botonVerde glyphicon glyphicon-arrow-left"> Regresar</button>
</div>




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

 
</div><!-- Hasta AQUI -->


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
$sql = 'SELECT descripcion,ccapmiles FROM tb_cat_partidaspresupuestales_capitulo WHERE ccapmiles IN(3000);';
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
<script type="text/javascript">
  // Preparar listas de búsqueda
  window.cuentasMenores = new Array();
  window.textoMenores = new Array();

  window.cuentasMayores = new Array();
  window.textoMayores = new Array();

  window.buscadores = [ "cuentasCon" ];
  <?php
  if(isset($_POST['idEmp'])){ 
  ?>

  $( document ).ready(function() {
      window.idEmp = <?= "'".$_POST['idEmp']."'"; ?>;
      fnFormatoSelectGeneral(".tipotercero");
      fnFormatoSelectGeneral(".partidapresupuestal");
      fnFormatoSelectGeneral(".CurrCode");
      fnFormatoSelectGeneral(".PaymentTerms");
      fnFormatoSelectGeneral(".Typeid");
      fnFormatoSelectGeneral(".regimen");
      fnFormatoSelectGeneral(".partida1F");
      fnFormatoSelectGeneral(".partida2FF");
      fnFormatoSelectGeneral(".EstadoSel");
      fnFormatoSelectGeneral(".Address3");
      fnFormatoSelectGeneral(".activoEmp");
      fnFormatoSelectGeneral(".id_nu_puesto");
    $('#panelBancos').show();
    $('#panelPartidas').show();
    
    $("#btnGuardarEmp").prop('onclick', null);
    $("#btnGuardarEmp").attr('onclick', 'fnModificar()');
     
      var url ="modelo/SelectEmpleado_Modelo.php";
      console.log("mostrarInfoProv");
    
      //Opcion para operacion
      dataObj = { 
              proceso: 'empleado',
              idEmp: <?php echo "'".$_POST['idEmp']."'"; ?>
            };
      //Obtener datos de las bahias
      $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType:"json",
            url:url,
            data:dataObj
        })
      .done(function( data ) {
          if(data.result){
              datos=data.contenido.datosEmp;
              console.log(datos);
              partidas=data.contenido.partidas;
              var cuentacontablejd=data.contenido.cuenta;

              $('#sn_clave_empleado').val(datos[0].claveempleado);
              $('#id_nu_empleado').val(datos[0].idEmp);
              $('#ln_nombre').val(datos[0].nombre);
              $('#sn_primer_apellido').val(datos[0].apPat);
              $('#sn_segundo_apellido').val(datos[0].apMat);
              $('#sn_rfc').val(datos[0].rfc);
              $('#sn_curp').val(datos[0].curp);
              $('#id_nu_puesto').multiselect('select',datos[0].puesto);
              $('#selectUnidadNegocio').multiselect('select',datos[0].ur);
              $('#selectUnidadEjecutora').multiselect('select',datos[0].ue);
              //fnSeleccionarDatosSelect("selectUnidadNegocio",datos[0].ur);
              //fnSeleccionarDatosSelect("selectUnidadEjecutora",datos[0].ue);
              
              /*fnSeleccionarDatosSelect("tipotercero",datos[0].tercero);
              fnSeleccionarDatosSelect("Typeid",datos[0].tipoid);
              fnSeleccionarDatosSelect("PaymentTerms",datos[0].terminos);*/
              fnSeleccionarDatosSelect("activoEmp", datos[0].activo==1 ? 1 : 2 );

               //fnSeleccionarDatosSelect("EstadoSel",datos[0].ad4);


              //fnMunicipio(datos[0].ad3);
              //console.log(datos[0].ad3);
               //console.log(datos[0].ad4 +"--------");
              //fnSeleccionarDatosSelect("CurrCode",datos[0].tipocambio);
              //$('#sn_clave_empleado').('disable');
              if(datos[0].claveempleado){
                $('#sn_clave_empleado').prop('readonly', true);
                $('#sn_clave_empleado').attr('readonly', true);
              }
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

              $("#cuentasCon").val(cuentacontablejd);
              $("#cuenta__cuentasCon").val(cuentacontablejd);

          }
      })
      .fail(function(result) {
          var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
           muestraModalGeneral(4, titulo,"Hubo un error al mostrar los datos del empleado"); 
          console.log("ERROR");
          console.log( result );
      });
      //// Se agregan líneas para vaciar las partidas precargadas
      setTimeout(function () {
        $("#partidagenerica option:selected").prop("selected", false);
        $("#partidagenerica").multiselect('rebuild');
      }, 5000);
  });

  //fnCuentasContablesProv(<?php //echo "'".$_POST['idSupp']."'"; ?>);
  fnCountsBanksProv(<?php echo "'".$_POST['idEmp']."'"; ?>);
  fnPartidas(<?php echo "'".$_POST['idEmp']."'"; ?>);



  <?php
  } else{ ?>
    
    $(document).ready(function(){

      fnFormatoSelectGeneral(".tipotercero");
      fnFormatoSelectGeneral(".partidapresupuestal");
      fnFormatoSelectGeneral(".CurrCode");
      //fnFormatoSelectGeneral(".PaymentTerms");
      //fnFormatoSelectGeneral(".Typeid");
      fnFormatoSelectGeneral(".regimen");
      fnFormatoSelectGeneral(".partida1F");
      fnFormatoSelectGeneral(".partida2FF");
      fnFormatoSelectGeneral(".EstadoSel");
      fnFormatoSelectGeneral(".Address3");
      fnFormatoSelectGeneral(".activoEmp");
      fnFormatoSelectGeneral(".id_nu_puesto");

      fnSeleccionarDatosSelect("activoEmp",1);

       $("#btnGuardarEmp").prop('onclick', null);
       $("#btnGuardarEmp").attr('onclick', 'fnGuardar()');
    });
  <?php
  } ?>
</script>
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
  fnBloquearDivs('datosPrincipales');
</script>
<?php } ?>
