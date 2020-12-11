<?php
/**
 * Panel Visualizar Reportes
 *
 * @category Panel
 * @package ap_grp
 * @author Desarrollo <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Visualizar reportes conac y ldf
 */

error_reporting(0);

include 'includes/session.inc';
$funcion = 2538;
$title = traeNombreFuncion($funcion, $db);
include 'includes/header.inc';

include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
//Librerias GRID
include 'javascripts/libreriasGrid.inc';
?> 
<script type="text/javascript" src="javascripts/imprimirreportesegresos.js?v=<?= rand();?>"></script>

<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
            Filtros de Búsqueda
          </a>
        </div>
      </h4>
    </div>

    <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <div class="col-md-4">
          <div class="form-inline row">
            <div class="col-md-3">
                  <span><label>Reporte: </label></span>
            </div>
            <div class="col-md-9">
                  <select id="selectReportes" name="selectReportes" class="selectGeneral" onchange="fnCambioReporteEgresos()">
                    <option value="">Seleccione una opción...</option>

                    <?php if (Havepermission($_SESSION ['UserID'], 2539, $db) == 1): ?>
                      <option value="rptEgresosProveedores"><?php echo "2539 - ".traeNombreFuncion(2539, $db); ?></option>
                    <?php endif ?>

                    <?php if (Havepermission($_SESSION ['UserID'], 2540, $db) == 1): ?>
                      <option value="rptEgresosListadoSolped"><?php echo "2540 - ".traeNombreFuncion(2540, $db); ?></option>
                    <?php endif ?>

                    <?php if (Havepermission($_SESSION ['UserID'], 2541, $db) == 1): ?>
                      <option value="rptEgresosAnalisisProveedores"><?php echo "2541 - ".traeNombreFuncion(2541, $db); ?></option>
                    <?php endif ?>

                    <?php if (Havepermission($_SESSION ['UserID'], 2542, $db) == 1): ?>
                      <option value="rptEgresosPedidos"><?php echo "2542 - ".traeNombreFuncion(2542, $db); ?></option>
                    <?php endif ?>

                    <?php if (Havepermission($_SESSION ['UserID'], 2543, $db) == 1): ?>
                      <option value="rptEgresosPedidosProveedor"><?php echo "2543 - ".traeNombreFuncion(2543, $db); ?></option>
                    <?php endif ?>
                  </select>
            </div>
          </div> 
        </div>
        <div class="col-md-4">
          <component-date-label label="Fecha Inicial: " id="txtFechaInicial" name="txtFechaInicial" placeholder="Fecha Inicial" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
        </div>
        <div class="col-md-4">
          <component-date-label label="Fecha Final: " id="txtFechaFinal" name="txtFechaFinal" placeholder="Fecha Final" value="<?php echo date('d-m-Y'); ?>"></component-date-label>
        </div>
        
        <div class="row"></div>

        <br/>

        <div class="col-md-4">
          <div class="form-inline row" >
            <div class="col-md-3">
              <span><label>Tipo Descarga: </label></span>
            </div>
            <div class="col-md-9"> 
              <select id="tipoDescarga" name="tipoDescarga" class="form-control tipoDescarga" >
                <option value="" label="Seleccione una opción">Seleccione una opción</option>
                <option value="p" label="PDF" selected>PDF</option>
                <option value="x" label="Excel">Excel</option>
              </select>
            </div>
          </div>
        </div>

        <div class="col-md-4" id="divFiltroProveedor" style="display: none;">
          <component-text-label label="Proveedor / Beneficiario:" id="txtProveedor" name="txtProveedor" placeholder="Código/Nombre" title="Código/Nombre" value=""></component-text-label>
        </div>
        
        <div class="row"></div>

        <div align="center">
          <br>
          <component-button type="button" id="btnImprimir" name="btnImprimir" onclick="fnAbrirReporte()" value="Ver Reporte"></component-button>
        </div>
      </div>
  </div>
</div>

<div id="adentro" style="margin-bottom: 28%;">
  <div id="viewReporte">
      <iframe data="" id="objectContent" width="100%" height="800px" onload="" type="application/pdf" class="hidden">
          <!-- <embed src="" id="embedContent" type="application/pdf" /> -->
      </iframe>
  </div>
</div>

<?php
include 'includes/footer_Index.inc';
?>
