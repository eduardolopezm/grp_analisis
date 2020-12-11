<?php
/**
 * Preferencías de la Empresa
 *
 * @category Configuración
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 20/09/2017
 * Fecha Modificación: 20/09/2017
 * Configuración de movimientos contables
 */

$PageSecurity=5;
include('includes/session.inc');
$funcion=87;
$title = traeNombreFuncion($funcion, $db);
include('includes/header.inc');
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

//Librerias GRID
include('javascripts/libreriasGrid.inc');
?>

<script type="text/javascript" src="javascripts/companyPreferences.js?<?php echo rand(); ?>"></script>

<link rel="stylesheet" href="css/listabusqueda.css" />

<!-- tabla de busqueda -->
<div class="row">
  <div name="divTabla" id="divTabla">
    <div name="divCatalogo" id="divCatalogo"></div>
  </div>
</div><!-- .row -->

<!-- botones de accion -->
<div class="row pt10">
  <div class="panel panel-default">
    <div align="center">
      <component-button type="button" id="btnAgregar" name="btnAgregar" onclick="" value="Nuevo" class="glyphicon glyphicon-plus"></component-button>
      <br><br>
    </div>
  </div>
</div><!-- .row --><!-- .row -->
<?php
include 'includes/footer_Index.inc';
?>