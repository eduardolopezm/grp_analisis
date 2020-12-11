<?php
/**
 * ABC de Ramo
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/07/2017
 * Fecha Modificación: 31/07/2017
 * Se realizan operación pero el Alta, Baja y Modificación
 */

$PageSecurity=5;
include('includes/session.inc');
$funcion=2307;
$title = traeNombreFuncion($funcion, $db);
include('includes/header.inc');
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

//Librerias GRID
include('javascripts/libreriasGrid.inc');
?>

<script type="text/javascript" src="javascripts/activofijo_list.js"></script>


<div id="OperacionMensaje" name="OperacionMensaje"></div>
<div align="center">
  <component-button type="button" id="btnAgregar" name="btnAgregar" onclick="window.location.href = 'activofijo.php'" value="Nuevo" class="glyphicon glyphicon-plus"></component-button>
  
  <br>
  <br>
</div>


<div name="divTabla" id="divTabla">
  <div id="divCatalogo" name="divCatalogo"></div>
</div>


<?php
include 'includes/footer_Index.inc';
