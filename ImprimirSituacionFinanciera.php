<?php
/* CREADO POR GONZALO ALVAREZ ZERECERO  09/NOV/2011
17/AGOSTO/2012 -desarrollo- Elimine el campo de proyectos para tener categorias genericas
*/
include 'includes/session.inc';
$title = _('Configuración de Reportes GRP');
include 'includes/header.inc';
$funcion = 2261;
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
//Librerias GRID
include 'javascripts/libreriasGrid.inc';
?>
<script type="text/javascript" src="javascripts/grp_configuracion_reportes.js"></script>
<!-- Nav tabs -->


<component-button type="button" id="btnImprimir" name="btnImprimir" onclick="window.open('PrintSituacionFinanciera.php?PrintPDF=1&reporte=situacionfinanciera')" value="Imprimir Reporte de Situación Financiera"></component-button>

<component-button type="button" id="btnImprimir" name="btnImprimir" onclick="window.open('PrintSituacionFinanciera.php?PrintPDF=1&reporte=estadodeactividades')" value="Imprimir Reporte de Estado de Actividades"></component-button>

<!--
-->

<?php
include 'includes/footer_Index.inc';
?>