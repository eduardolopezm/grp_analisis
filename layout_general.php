<?php
/**
 * Adecuaciones Presupuestales
 *
 * @category
 * @package ap_grp
 * @author Eduardo López Morales <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 08/08/2017
 * Fecha Modificación: 08/08/2017
 * Vista para el proceso de adecuaciones presupuestales
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 5;
$funcion = 2273;
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db);
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
$permiso = Havepermission($_SESSION ['UserID'], 2257, $db);

//Librerias GRID
include('javascripts/libreriasGrid.inc');

$typeGeneral = 0;
if (isset($_GET['type'])) {
    $typeGeneral = $_GET['type'];
}

$transnoGeneral = 0;
if (isset($_GET['transno'])) {
    $transnoGeneral = $_GET['transno'];
}

$funcionGeneral = 0;
if (isset($_GET['fn'])) {
    $funcionGeneral = $_GET['fn'];
}

$opcionGeneral = "";
if (isset($_GET['op'])) {
    $opcionGeneral = $_GET['op'];
}

?>

<link rel="stylesheet" href="css/listabusqueda.css" />

<script type="text/javascript">
  var typeGeneral = '<?php echo $typeGeneral; ?>';
  var transnoGeneral = '<?php echo $transnoGeneral; ?>';
  var funcionGeneral = '<?php echo $funcionGeneral; ?>';
  var opcionGeneral = '<?php echo $opcionGeneral; ?>';
</script>

<!-- <div class="row">
    <div class="col-md-6">
        <component-administrador-archivos value="245"> </component-administrador-archivos>
    </div>
</div> -->

<div id="divMensajeOperacion" name="divMensajeOperacion"></div>

<div align="left">

  <div name="divTabla" id="divTabla">
    <div name="divContenidoTabla" id="divContenidoTabla"></div>
  </div>
  
</div>

<script type="text/javascript" src="javascripts/layout_general.js"></script>
<script type="text/javascript" src="javascripts/Subir_Archivos.js"> </script>

<?php
include 'includes/footer_Index.inc';
?>