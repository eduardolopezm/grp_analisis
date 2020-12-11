<?php
/**
 * AbcGeneral.php
 * @category ABC
 * @package  ap_grp
 * @author 	 JP
 * @version  1.0.0
 * @date: 	 10.03.18
 *
 * Programa para la generación de paneles generales para la captura y administración de la información de los
 * catálogos y su respectiva administración.
 *
 */

/***************************** VARIABLES *****************************/
$PageSecurity=5;
$urlSave = '';
if(empty($_GET['URL'])){
	echo "<script>window.location.href = 'http://".$_SERVER ['HTTP_HOST']."/index.php';</script>";
	exit();
}
$urlSave = $_GET['URL'];

/***************************** INCLUCIONES Y PROCESAMIENTO *****************************/
# inclusiones principales
include('config.php');
include('includes/SecurityUrl.php');// extrae el numero de función

# extracción de la información de la url datos esperados $dominiogeneral
extract($_GET);
$funcion = $dominiogeneral;

# inclusiones secundarias
include('includes/session.inc');// funciones de la sesión
$title = traeNombreFuncion($dominiogeneral, $db);// extracción del titulo
include('includes/ConnectDB.inc');
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

# archivo con las funciones necesarias para la ejecución del programa
include('includes/AbcGeneral.inc');

# inclusión e impresión de la cabecera del sistema
include('includes/header.inc');

# Librerías GRID
include('javascripts/libreriasGrid.inc');

# extracción de la información del panel datos esperados $htmlPanel,$tblObj,$tblTitulo,$tblVisual,$tblExcel
extract(obtencionPanel($db, $dominiogeneral));

# fin php
?>
<script>
	// variables de tabla
	var title = '<?= $title; ?>',
		urlSave='<?= $urlSave;?>',
		tblObj=<?= $tblObj;?>,
		tblTitulo=<?= $tblTitulo;?>,
		tblVisual=<?= $tblVisual;?>,
		tblExcel=<?= $tblExcel;?>;
</script>
<script type="text/javascript" src="javascripts/AbcGeneral.js?v=<?= rand();?>"></script>
<?php
# impresión del contenido html generado a trabes base de datos
echo $htmlPanel;
include 'includes/footer_Index.inc';
?>
