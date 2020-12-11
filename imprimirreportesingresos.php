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
$funcion = 2521;
$title = traeNombreFuncion($funcion, $db);
include 'includes/header.inc';

include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
//Librerias GRID
include 'javascripts/libreriasGrid.inc';
?>
<script type="text/javascript" src="javascripts/imprimirreportesingresos.js?v=<?= rand();?>"></script>

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
                  <select id="selectReportes" name="selectReportes" class="selectGeneral" onchange="fnCambioReporteIngreso()">
                    <option value="">Seleccione una opción...</option>

                    <?php if (Havepermission($_SESSION ['UserID'], 2529, $db) == 1): ?>
                      <option value="rptConcentradoIngresosDiario"><?php echo "2529 - ".traeNombreFuncion(2529, $db); ?></option>
                    <?php endif ?>

                    <?php if (Havepermission($_SESSION ['UserID'], 2527, $db) == 1): ?>
                      <option value="rptCorteCajaGeneral"><?php echo "2527 - ".traeNombreFuncion(2527, $db); ?></option>
                    <?php endif ?>

                    <?php if (Havepermission($_SESSION ['UserID'], 2526, $db) == 1): ?>
                      <option value="rptControlDeFoliosCajas"><?php echo "2526 - ".traeNombreFuncion(2526, $db); ?></option>
                    <?php endif ?>

                    <?php if (Havepermission($_SESSION ['UserID'], 2530, $db) == 1): ?>
                      <option value="rptAcumuladoMensualIngresos"><?php echo "2530 - ".traeNombreFuncion(2530, $db); ?></option>
                    <?php endif ?>

                    <?php if (Havepermission($_SESSION ['UserID'], 2525, $db) == 1): ?>
                      <option value="rptInformeDiarioIngresos"><?php echo "2525 - ".traeNombreFuncion(2525, $db); ?></option>
                    <?php endif ?>

                    <?php if (Havepermission($_SESSION ['UserID'], 2533, $db) == 1): ?>
                      <option value="rptFolioCancelado"><?php echo "2533 - ".traeNombreFuncion(2533, $db); ?></option>
                    <?php endif ?>

                    <?php if (Havepermission($_SESSION ['UserID'], 2528, $db) == 1): ?>
                      <option value="rptCorteDiario"><?php echo "2528 - ".traeNombreFuncion(2528, $db); ?></option>
                    <?php endif ?>

                    <?php if (Havepermission($_SESSION ['UserID'], 2531, $db) == 1): ?>
                      <option value="rptIngresosObjPrincipal"><?php echo "2531 - ".traeNombreFuncion(2531, $db); ?></option>
                    <?php endif ?>

                    <?php if (Havepermission($_SESSION ['UserID'], 2532, $db) == 1): ?>
                      <option value="rptIngresosObjParcial"><?php echo "2532 - ".traeNombreFuncion(2532, $db); ?></option>
                    <?php endif ?>

                    <?php if (Havepermission($_SESSION ['UserID'], 2534, $db) == 1): ?>
                      <option value="rptIngresosObjParcialDetallado"><?php echo "2534 - ".traeNombreFuncion(2534, $db); ?></option>
                    <?php endif ?>

                    <?php if (Havepermission($_SESSION ['UserID'], 2545, $db) == 1): ?>
                      <option value="rptResumenIngresosDiario"><?php echo "2545 - ".traeNombreFuncion(2545, $db); ?></option>
                    <?php endif ?>

                    <?php if (Havepermission($_SESSION ['UserID'], 2546, $db) == 1): ?>
                      <option value="rptIngresosPorSemana"><?php echo "2546 - ".traeNombreFuncion(2546, $db); ?></option>
                    <?php endif ?>

                    <?php if (Havepermission($_SESSION ['UserID'], 2547, $db) == 1): ?>
                      <option value="rptIngresosDiarioPagos"><?php echo "2547 - ".traeNombreFuncion(2547, $db); ?></option>
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

        <div class="col-md-4" id="divSelectUrFiltros" style="display: none;">
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
        </div>
        
        <div id="divSelectObjPrincipalFiltros" style="display: none;">
          <div class="col-md-4" >
            <div class="form-inline row">
                <div class="col-md-3">
                    <span><label>Objeto Principal: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectObjetoPrincipal" name="selectObjetoPrincipal" class="form-control selectObjetoPrincipal selectGeneral" multiple="true" onchange="fnCambioObjetoPrincipalGeneral('selectObjetoPrincipal', 'selectObjetoParcial');"></select>
                </div>
            </div>
          </div>
          <div class="col-md-4" >
            <div class="form-inline row">
                <div class="col-md-3">
                    <span><label>Objeto Parcial: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="selectObjetoParcial" name="selectObjetoParcial" class="form-control selectGeneral" multiple="true"></select>
                </div>
            </div>
          </div>
        </div>
        
        <div class="row"></div>
        <div align="center">
          <br>
          <component-button type="button" id="btnImprimir" name="btnImprimir" onclick="fnAbrirReporte()" value="Ver Reporte"></component-button>
        </div>
      </div>
  </div>
</div>

<!--<component-button type="button" id="btnImprimirCONAC2" name="btnImprimirCONAC2" onclick="fnAbrirReporte('PrintSituacionFinanciera.php?PrintPDF=1&reporte=estadodeactividades')" value="Imprimir Reporte de Estado de Actividades"></component-button>
<br>
<component-button type="button" id="btnImprimirCONAC3" name="btnImprimirCONAC3" onclick="fnAbrirReporte('PrintSituacionFinanciera.php?PrintPDF=1&reporte=estadohaciendapublica')" value="Imprimir Reporte de Estado de Hacienda Pública"></component-button>
<br>
<component-button type="button" id="btnImprimirCONAC4" name="btnImprimirCONAC4" onclick="fnAbrirReporte('PrintSituacionFinanciera.php?PrintPDF=1&reporte=estadoanaliticodelactivo')" value="Estado Analítico del Activo"></component-button>
<br>-->

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
