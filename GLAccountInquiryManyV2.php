<?php


/**
 * consolidadesciones
 *
 * @category     consolidaciones requisicion
 * @package      ap_grp
 * @author       Arturo Lopez Peña  <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 25/04/2018
 * Fecha Modificación: 25/04/2018
 * Se crea la consoliacion de las requisiciones
 */
 // ini_set('display_errors', 1);
 //  ini_set('log_errors', 1);
 //  error_reporting(E_ALL);

$PageSecurity = 5;
$funcion = 503;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db);
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
?>

<div class="row container-fluid">

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
          <div class="panel-body text-left">
            <form id="criteriosFrm"> 
              <div class="row clearfix"> 
                <div class="row" id="r0"> 
                    <div class="col-xs-12 col-md-6 pt20">
                        <div class="col-md-3 pt10" style="vertical-align: middle;">
                            <span><label>UR: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select id="selectUnidadNegocio" name="selectUnidadNegocio[]" class="form-control selectUnidadNegocio"  onchange="fnTraeUnidadesEjecutoras(this.value, 'selectUnidadEjecutora')" multiple="true">                    
                            </select>
                        </div> 
                    </div>
                    <div class="col-xs-12 col-md-6 pt20">
                        <div class="col-md-3 pt10" style="vertical-align: middle;">
                            <span><label>UE: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select id="selectUnidadEjecutora" name="selectUnidadEjecutora[]" class="form-control selectUnidadEjecutora" multiple="true"> 
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row" id="r1">
                  <div class="col-xs-12 col-md-6 pt20">
                     <div class="col-md-3">
                        <span><label>Cuenta desde: </label></span>
                     </div>
                     <div class="col-md-9 col-xs-12">
                        <component-listado-label label="" id="cuentaDesde" name="cuentaDesde" placeholder="Cuenta desde"></component-listado-label>
                     </div>
                  </div>
                  <div class="col-xs-12 col-md-6 pt20">
                     <div class="col-md-3">
                        <span><label>Cuenta hasta: </label></span>
                     </div>
                     <div class="col-md-9 col-xs-12">
                        <component-listado-label label="" id="cuentaHasta" name="cuentaHasta" placeholder="Cuenta hasta"></component-listado-label>
                     </div>
                  </div>
                </div>
                <div class="row " id="r3"> 
                  <div class="col-xs-12 col-md-6 pt20 plr30">  
                    <component-date-label label="Desde:" id="dateDesde" name="dateDesde"  value ="<?php echo date('d-m-Y'); ?>" placeholder="Desde fecha" title="DesdeFecha"></component-date-label>
                  </div>
                  <div class="col-xs-12 col-md-6 pt20 plr30">  
                    <component-date-label label="Hasta:" id="dateHasta" name="dateHasta"  value ="<?php echo date('d-m-Y'); ?>" placeholder="Hasta fecha" title="HastaFecha"></component-date-label>
                  </div>
                </div>
              </div>
              <br>
              <div class="col-xs-12  col-md-12 text-center">
                    <div class="col-xs-12 col-md-12 text-center">  
                    <component-button type="button" id="filtrar" name="btnMuestraBalanza" class="glyphicon glyphicon-search" onclick="return false;" value="Filtrar"></component-button>
                    <a class="glyphicon glyphicon-save-file btn btn-default botonVerde" href="#" id="test" onClick="javascript:fnExcelReport();">Exportar a Excel</a>
                    <a class="glyphicon glyphicon-save-file btn btn-default botonVerde" href="javascript:;" id="test2" onClick="javascript:fnExportPDF();">Exportar a PDF</a>
                  </div>
               </div>
          </div>
      </div>
  </div><!-- fin panel-->
</div><!--- fin  row-->
<div class="row container-fluid"> 
  <div id="tablaMovs"> 
    <div id="datosMovs">  
    </div>
  </div>

</div>

<script src="javascripts/jspdf.min.js"></script>
<script src="javascripts/jspdf.plugin.autotable.min.js"></script>
<script type="text/javascript" src="javascripts/GLAccountInquiryManyV2.js?<?php echo rand(); ?>"></script>
<script>
 
  function fnExportPDF(){
      console.log('exportPDF');
    var doc = new jsPDF('l')
    // It can parse html:
    doc.setFontSize(18)
    doc.text("Auxiliar de Mayor", 14, 20)

    doc.autoTable({ 
        html: '#my-table',
        startY: doc.previousAutoTable.finalY + 15,
        startY: 25,
        columnStyles: { 0: { halign: 'center', fillColor: [0, 255, 0] } }, // Cells in first column centered and green

        // Default for all columns
        styles: { overflow: 'linebreak', cellWidth: 'wrap' },
        // Override the default above for the text column
        // columnStyles: { text: { cellWidth: number = 'auto' } },
        columnStyles: {
            0: {cellWidth: 30},
            1: {cellWidth: 20},
            2: {cellWidth: 20},
            3: {cellWidth: 100},
            4: {cellWidth: 30},
            5: {cellWidth: 30},
            6: {cellWidth: 30},
            // etc
        }
    })
    
    doc.save('table.pdf')

  }
function fnExcelReport() {
     var tab_text = '<html <meta charset="UTF-8"> xmlns:x="urn:schemas-microsoft-com:office:excel">';
     tab_text = tab_text + '<head><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
     tab_text = tab_text + '<x:Name>Test Sheet</x:Name>';
     tab_text = tab_text + '<x:WorksheetOptions><x:Panes></x:Panes></x:WorksheetOptions></x:ExcelWorksheet>';
     tab_text = tab_text + '</x:ExcelWorksheets></x:ExcelWorkbook></xml></head><body>';
     tab_text = tab_text + "<table border='1px'>";
     
    //get table HTML code
     tab_text = tab_text + $('#my-table').html();
     tab_text = tab_text + '</table></body></html>';

     var data_type = 'data:application/vnd.ms-excel;';
     
     var ua = window.navigator.userAgent;
     var msie = ua.indexOf("MSIE ");
     //For IE
     if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
          if (window.navigator.msSaveBlob) {
          var blob = new Blob([tab_text], {type: "application/csv;charset=utf-8;"});
          navigator.msSaveBlob(blob, 'Auxiliar de Mayor.xls');
          }
     } 
    //for Chrome and Firefox 
    else {
     $('#test').attr('href', data_type + ', ' + encodeURIComponent(tab_text));
     $('#test').attr('download', 'Auxiliar de Mayor.xls');
    }


    }
</script>

<?php

include 'includes/footer_Index.inc';
?>
