<?php
/**
 * ABC de Configuración
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 20/09/2018
 * Fecha Modificación: 20/09/2018
 * Se realizan operación Modificación
 */

$PageSecurity=5;
include('includes/session.inc');
$funcion=1256;
$title = traeNombreFuncion($funcion, $db);
include('includes/header.inc');
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

//Librerias GRID
include('javascripts/libreriasGrid.inc');
?>

<script type="text/javascript" src="javascripts/ABCConfig.js"></script>
<!-- tabla de busqueda -->
<div class="row">
  <div name="divTabla" id="divTabla">
    <div name="divCatalogo" id="divCatalogo"></div>
  </div>
</div><!-- .row -->
<br>
<?php
include 'includes/footer_Index.inc';
?>