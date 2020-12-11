<?php
/**
 * Consolidacio bancaria al cargar  datos
 *
 * @category     ABC
 * @package      ap_grp
 * @author       Arturo Lopez Peña  <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 15/02/2018
 * Fecha Modificación: 16/02/2018
 * Carga de estado de cuenta cvs para consolidacion bancaria
 */

$PageSecurity = 5;
require 'includes/session.inc';
$funcion = 501;
$title= traeNombreFuncion($funcion, $db);
//$title = _('Cargar Estados de Cuenta Bancarios');
require 'includes/header.inc';

require 'includes/SecurityFunctions.inc';
require 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
require 'javascripts/libreriasGrid.inc';

header('Content-Type: text/html; charset=UTF-8');
?>
<script type="text/javascript" src="javascripts/Subir_Archivos.js"> </script>
<script type="text/javascript" src="javascripts/consolidacionBancaria.js"> </script>

<div clas="col-xs-12 col-md-12" > 
  <div class="col-xs-12 col-md-4" id="banco"> 
  </div>

</div>

<div class="col-xs-6 col-md-6 text-right"> 
<div class="col-md-4"> 
 <div class="input-group" style="font-size: 22px !important;">
    <span class="input-group-addon" style="background: none; border: none;"><b> Mes:</b> </span>
     <select id="selectMeses" name="selectMeses" class="form-control selectMeses">
    </select>
  </div>
</div>
  <div class="col-md-4"> 
   <div class="input-group" style="font-size: 22px !important;">
       <span class="input-group-addon" style="background: none; border: none;"><b> Año:</b> </span>
      <select id="selectAnio" name="selectAnio" class="form-control selectAnio">
        
      </select>

  </div>
  </div>

</div>

<div class="col-xs-12 col-md-12"> 
   <br><br>
  <component-administrador-archivos funcion="501"  tipo="600" trans="all" idcomponente="" esmultiple="0"> </component-administrador-archivos>
</div>


<?php
require 'includes/footer_Index.inc';
?>