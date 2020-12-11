<?php
/** 
 * ABC de Fuente del Recurso
 *
 * @category ABC
 * @package ap_grp
 * @author Jesùs Reyes Santos <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Se realizan operación pero el Alta, Baja y Modificación 
 */
 
$PageSecurity = 8;
include 'includes/session.inc';  
//$title = _('Mantenimiento Función');
$funcion = 2510;
$title = traeNombreFuncion($funcion, $db);

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
include 'javascripts/libreriasGrid.inc';

?>
<link rel="stylesheet" href="css/listabusqueda.css" />

<script type="text/javascript" src="javascripts/abcPropiedadesAtributos.js?v=<?= rand();?>"></script>
<!-- tabla de busqueda -->


<!--Modal Eliminar -->
<div class="modal fade" id="ModalUREliminar" name="ModalUREliminar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document" name="ModalGeneralTam" id="ModalGeneralTam">
    <div class="modal-content">
      <div class="navbar navbar-inverse navbar-static-top">
        <!--Contenido Encabezado-->
        <div class="col-md-12 menu-usuario">
          <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <div class="nav navbar-nav">
            <div class="title-header">
              <h3><span class="glyphicon glyphicon-info-sign"></span> Información</h3>
            </div>
          </div>
        </div>
        <div class="linea-verde"></div>
      </div>
      <div class="modal-body" id="ModalUREliminar_Mensaje" name="ModalUREliminar_Mensaje">
        <!--Mensaje o contenido-->
      </div>
      <div class="modal-footer">
        <component-text type="hidden" label="Clave: " id="txtClaveEliminar" name="txtClaveEliminar" placeholder="Clave"></component-text>
        <component-text type="hidden" label="Fuente del Recurso: " id="txtFuenteEliminar" name="txtFuenteEliminar" placeholder="Fuente del Recurso"></component-text>
        <component-button type="button" id="btn" name="btn" onclick="fnEliminarEjecuta()" value="Eliminar"></component-button>
        <component-button type="button" id="btn" name="btn" data-dismiss="modal" value="Cerrar"></component-button>
      </div>
    </div>
  </div>
</div>

<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
<!-- target="_blank" -->
<div align="left">
  <!--Panel Busqueda-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
            <b>Criterios de registro</b>
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <div class="row clearfix">
        
        <input class="hidden" id="hiddenBack" class="form-control"  name="hiddenBack" value="<?php echo $_GET['hiddenBack']; ?>" type="text" style="width: 100%;" readonly>
        <input class="hidden" id="isUnique" class="form-control"  name="isUnique" value="<?php echo $_GET['isUnique']; ?>" type="text" style="width: 100%;" readonly>

        <div class="col-md-12">
            <div class="form-inline row">
                        <div class="col-md-3 col-xs-3">
                            <span><label>Tipo de Contrato: </label></span>
                        </div>
                        <div class="col-md-9 col-xs-9">
                            <input id="txtFolioConfiguracion" class="form-control"  name="txtFolioConfiguracion" value="<?php echo $_GET['conf']; ?>" type="text" style="width: 100%;" readonly>
                        </div>
                </div>
            </div>  

            <div class="col-md-6 col-xs-6">
            
            </div>    

            <div class="row"></div>
            <br>
          
 
            <div class="col-md-6 col-xs-6">
                <!-- <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Objeto Principal: </label></span>
                    </div>
                    <div class="col-md-9">
                        <input id="contrato" class="form-control hidden"  name="contrato" value="<?php // echo $_GET['id_contratos']; ?>" type="text" style="width: 100%;">
					    <input id="objetoPrincipal" class="form-control"  value="<?php  //echo  $_GET['id_contratos']; ?> - <?php // echo $_GET['id_loccode']; ?>" readonly type="text" style="width: 100%;">
                    </div>
                </div> -->
                <div class="form-inline row">
                        <div class="col-md-3 col-xs-3">
                            <span><label>Folio: </label></span>
                        </div>
                        <div class="col-md-9 col-xs-9">
                            <input id="txtFolioContrato" class="form-control"  name="txtFolioContrato" value="<?php echo $_GET['id_contratos']; ?>" type="text" style="width: 100%;" readonly>
                        </div>
                </div>
            </div>

            <div class="col-md-6 col-xs-6">
            <div class="form-inline row">
                        <div class="col-md-3 col-xs-3">
                            <span><label>Contribuyente: </label></span>
                        </div>
                        <div class="col-md-9 col-xs-9">
                            <input id="txtContribuyente" class="form-control"  name="txtContribuyente" value="<?php echo $_GET['name']; ?>" type="text" style="width: 100%;" readonly>
                        </div>
                </div>
            </div>    
            
            <div class="col-md-6 col-xs-6" style="display: none;">
            <div class="form-inline row">
                        <div class="col-md-3 col-xs-3">
                            <span><label>Folio de Configuración: </label></span>
                        </div>
                        <div class="col-md-9 col-xs-9">
                            <input id="txtFolioConfiguracion" class="form-control"  name="txtFolioConfiguracion" value="<?php echo $_GET['id_configuracion']; ?>" type="text" style="width: 100%;" readonly>
                        </div>
                </div>
            </div>   

            <div class="row"></div> 
            <br>
            

            
            <div name="tablaReducciones" id="tablaReducciones"></div>

            <div name="tablaFilas" id="tablaFilas"></div>

         
           
       
        <div class="row"></div>
		  <br>
		<div align="center">
			<button type="button" class="btn botonVerde glyphicon glyphicon glyphicon-chevron-left" id="regresar"> Regresar</button>
			<component-button class="btnNuevo" type="button" id="btn" name="btn" onclick="fnAlmacenarCaptura()" value="Guardar"></component-button>
			<component-button class="btnEditar" type="button" id="btn" name="btn" onclick="fnAlmacenarCapturaEditar()" value="Guardar"></component-button>
			<button type="button" class="btn botonVerde glyphicon glyphicon glyphicon-chevron-right" id="continuar"> Continuar</button>
		</div>
		  <!-- <div align="center" id="btnEditar">
          <br>
          <button type="button" class="btn botonVerde glyphicon glyphicon glyphicon-chevron-left" id="regresar"> Regresar</button>
          <component-button type="button" id="btn" name="btn" onclick="fnAlmacenarCapturaEditar()" value="Guardar"></component-button>
          <button type="button" class="btn botonVerde glyphicon glyphicon glyphicon-chevron-right" id="continuar"> Continuar</button>
        </div> -->
      </div>
    </div>
  </div>


<?php
include 'includes/footer_Index.inc';
?>