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

$PageSecurity = 1;
include 'includes/session.inc';
$title = _('Menu Principal');
include 'includes/SecurityFunctionsHeader.inc';
include 'includes/header.inc';
include 'includes/SQL_CommonFunctions.inc';
?>

<?php
  //Librerias GRID
  include('javascripts/libreriasGrid.inc');
?>
<div class="row">
    <component-layouts-generados id="ad1" funcion="<?php echo $_GET['funcion'] ?>" tipo="<?php echo $_GET['type']?>" trans="<?php echo $_GET['transno'] ?>" > </component-layouts-generados>   	
</div>
<script type="text/javascript" src="javascripts/Subir_Archivos.js"> </script>
<?php
include 'includes/footer_Index.inc';
?>
<script type="text/javascript">
  $('#idBodyGeneral').css('background', '#FFFFFF');
  $('#divGeneralHeader').css('display', 'none');
  $('#divGeneralFooter').css('display', 'none');
  $("#divContenidoGeneral").removeClass("container");
  $("#divContenidoGeneralFuncion").removeClass("main-container");
  $("#divContenidoGeneralFuncion").removeClass("col-md-12");
</script>