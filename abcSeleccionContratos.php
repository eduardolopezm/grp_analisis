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

$PageSecurity = 5;
include 'includes/session.inc';  
//$title = _('Mantenimiento Función');
$funcion = 2517;
$title = traeNombreFuncion($funcion, $db);

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
include 'javascripts/libreriasGrid.inc';


?>
<link rel="stylesheet" href="css/listabusqueda.css" />

<script type="text/javascript" src="javascripts/abcSeleccionContratos.js"></script>

<!-- target="_blank" -->
<div align="left">
  <!--Panel Busqueda-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
            <b>Criterios de filtrado</b>
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <div class="row clearfix">

        <div class="col-md-4">
          <div class="form-inline row">
								<div class="col-md-3">
										<span><label>UR: </label></span>
								</div>
								<div class="col-md-9">
										<select id="selectUnidadNegocioFiltro" name="selectUnidadNegocioFiltro[]" class="form-control selectUnidadNegocio">
										</select>
								</div>
				  </div>
        </div>

        <div class="col-md-4">
              <div class="form-inline row">
									<div class="col-md-3">
										<span><label>UE: </label></span>
									</div>
									<div class="col-md-9">
										<select id="selectUnidadEjecutoraFiltro" name="selectUnidadEjecutoraFiltro[]" class="form-control selectUnidadEjecutora" >
										</select> <!-- multiple="multiple" data-todos="true" -->
									</div>
								</div>
            </div>

            <div class="col-md-4">
            <component-date-label label="Fecha Inicial: " id="txtFechaInicial" name="txtFechaInicial" placeholder="Fecha Inicial" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
            </div>

            <div class="row"></div>
            <br>
            <br>
          
            <div class="col-md-4">
            <div class="form-inline row">
									<div class="col-md-3">
										<span><label>Contrato: </label></span>
									</div>
									<div class="col-md-9">
										<select id="selectContratosFiltro" name="selectContratosFiltro[]" class="form-control selectContrato" >
										</select> <!-- multiple="multiple" data-todos="true" -->
									</div>
								</div>
            </div>

            <div class="col-md-4">
                <component-text-label label="Contribuyente:" id="contribuyenteFiltro" name="contribuyenteFiltro" placeholder="Contribuyente"></component-text-label>
								<input type="text" id="contribuyenteIDFiltro" name="contribuyenteIDFiltro" class="hidden" value="-1">

            </div>

            <div class="col-md-4">
								<component-date-label label="Fecha Final: " id="txtFechaFinal" name="txtFechaFinal" placeholder="Fecha Final" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
            </div>
        </div>
        <div class="row"></div>
        <div align="center">
          <br>
          <component-button type="button" id="btnBusqueda" name="btnBusqueda" onclick="fnBuscar()" class="glyphicon glyphicon-search" value="Filtrar"></component-button> 
        </div>
      </div>
    </div>
  </div>

  




<div class="row container-fluid">
  <div name="divTabla" id="divTabla">
    <div name="divCatalogo" id="divCatalogo"></div>
  </div>
</div><!-- .row -->

<?php
include 'includes/footer_Index.inc';
?>