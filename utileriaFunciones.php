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
$funcion = 408;
include 'includes/session.inc';
$title = _('Agregar Nuevas Funciones y Permisos');

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
$permiso = Havepermission ( $_SESSION ['UserID'], 2257, $db );

$optionsSubmodulo = "";
$SQL = "SELECT submoduleid as value, CONCAT(submoduleid, ' - ', title) as texto FROM sec_submodules WHERE active = 1 AND ubicacion = 'Body'";
$ErrMsg = "No se obtuvieron los submodulos";
$TransResult = DB_query ( $SQL, $db, $ErrMsg );
while ( $myrow = DB_fetch_array ( $TransResult ) ) {
  $selected = "";
    if ($_POST['selectSubmodulo'] == $myrow['value']) {
      $selected = "selected";
    }
    $optionsSubmodulo .= '<option value="'.$myrow['value'].'" '.$selected.'>'.$myrow['texto'].'</option>';
}

$optionsCategorias = "";
$SQL = "SELECT categoryid as value, CONCAT(categoryid, ' - ', name) as texto FROM sec_categories WHERE active = 1";
$ErrMsg = "No se obtuvieron los submodulos";
$TransResult = DB_query ( $SQL, $db, $ErrMsg );
while ( $myrow = DB_fetch_array ( $TransResult ) ) {
    $selected = "";
    if ($_POST['selectCategoria'] == $myrow['value']) {
      $selected = "selected";
    }
    $optionsCategorias .= '<option value="'.$myrow['value'].'" '.$selected.'>'.$myrow['texto'].'</option>';
}

if (isset($_POST['btnNuevo'])) {
  if (empty(trim($_POST['txtNombre']))) {
    echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>Agregar nombre a '.$_POST['selectTipo'].'</p></div>';
  }else if ($_POST['selectTipo'] == 'Funcion' and empty(trim($_POST['txtNombreArchivo']))) {
    echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>La función requiere nombre de Archivo</p></div>';
  }else if ($_POST['selectTipo'] == 'Permiso' and !empty(trim($_POST['txtNombreArchivo']))) {
    echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>El permiso no necesita nombre de Archivo</p></div>';
  }else{
    $functionid = 0;
    $SQL = "SELECT MAX(functionid) +1 as functionid FROM sec_functions WHERE functionid between 2520 AND 2549";
    $ErrMsg = "No se obtuvieron los submodulos";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
      $functionid = $myrow['functionid'];
    }

    if ($functionid > 0) {
      $SQL = "INSERT INTO ap_grp.sec_functions (`functionid`, `submoduleid`, `title`, `active`, `url`, `categoryid`, `shortdescription`, `orderno`, `comments`, `type`, `freg`, `fmod`, `nofucntion`)
              VALUES
                ('".$functionid."', '".$_POST['selectSubmodulo']."', '".$_POST['txtNombre']."', 1, '".$_POST['txtNombreArchivo']."', '".$_POST['selectCategoria']."', '".$_POST['txtNombre']."', 1, '".$_POST['txtNombre']."', '".$_POST['selectTipo']."', NOW(), NOW(), 0);
              ";
      $ErrMsg = "No se agrego ".$_POST['selectTipo'];
      $TransResult = DB_query ( $SQL, $db, $ErrMsg );

      $SQL = "INSERT INTO ap_grp.sec_functions_new (`functionid`, `submoduleid`, `title`, `active`, `url`, `categoryid`, `shortdescription`, `orderno`, `comments`, `type`, `freg`, `fmod`, `nofucntion`)
              VALUES
                ('".$functionid."', '".$_POST['selectSubmodulo']."', '".$_POST['txtNombre']."', 1, '".$_POST['txtNombreArchivo']."', '".$_POST['selectCategoria']."', '".$_POST['txtNombre']."', 1, '".$_POST['txtNombre']."', '".$_POST['selectTipo']."', NOW(), NOW(), 0);
              ";
      $ErrMsg = "No se agrego ".$_POST['selectTipo'];
      $TransResult = DB_query ( $SQL, $db, $ErrMsg );

      echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>'.$_POST['selectTipo'].' '.$functionid.' se agrego correctamente a Desarrollo</p></div>';

      $SQL = "INSERT INTO ap_grp_CAPA.sec_functions (`functionid`, `submoduleid`, `title`, `active`, `url`, `categoryid`, `shortdescription`, `orderno`, `comments`, `type`, `freg`, `fmod`, `nofucntion`)
              VALUES
                ('".$functionid."', '".$_POST['selectSubmodulo']."', '".$_POST['txtNombre']."', 1, '".$_POST['txtNombreArchivo']."', '".$_POST['selectCategoria']."', '".$_POST['txtNombre']."', 1, '".$_POST['txtNombre']."', '".$_POST['selectTipo']."', NOW(), NOW(), 0);
              ";
      $ErrMsg = "No se agrego ".$_POST['selectTipo'];
      $TransResult = DB_query ( $SQL, $db, $ErrMsg );

      $SQL = "INSERT INTO ap_grp_CAPA.sec_functions_new (`functionid`, `submoduleid`, `title`, `active`, `url`, `categoryid`, `shortdescription`, `orderno`, `comments`, `type`, `freg`, `fmod`, `nofucntion`)
              VALUES
                ('".$functionid."', '".$_POST['selectSubmodulo']."', '".$_POST['txtNombre']."', 1, '".$_POST['txtNombreArchivo']."', '".$_POST['selectCategoria']."', '".$_POST['txtNombre']."', 1, '".$_POST['txtNombre']."', '".$_POST['selectTipo']."', NOW(), NOW(), 0);
              ";
      $ErrMsg = "No se agrego ".$_POST['selectTipo'];
      $TransResult = DB_query ( $SQL, $db, $ErrMsg );
      
      echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>'.$_POST['selectTipo'].' '.$functionid.' se agrego correctamente a CAPA</p></div>';
    }
  }
}

echo '<form action=' . $_SERVER ['PHP_SELF'] . ' method=post name=form1>';
?>
<div align="left">
	<!--Panel Busqueda-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
            Información
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <div class="col-md-4">
          <component-text-label label="Nombre F/P: " id="txtNombre" name="txtNombre" placeholder="Nombre F/P" title="Nombre F/P" required="true" value="<?php echo $_POST['txtNombre']; ?>"></component-text-label>
          <br>
          <component-text-label label="Nombre Archivo: " id="txtNombreArchivo" name="txtNombreArchivo" placeholder="Nombre Archivo" title="Nombre Archivo" value="<?php echo $_POST['txtNombreArchivo']; ?>"></component-text-label>
        </div>
        <div class="col-md-4">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Submodulo: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectSubmodulo" name="selectSubmodulo" class="form-control" required="true"> 
                  <?php echo $optionsSubmodulo; ?>
                  </select>
              </div>
          </div>
          <br>
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Tipo: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectTipo" name="selectTipo" class="form-control" required="true"> 
                  	<option value="Funcion" <?php echo ($_POST['selectTipo'] == 'Funcion' ? 'selected' : ''); ?>>Función</option>
                  	<option value="Permiso" <?php echo ($_POST['selectTipo'] == 'Permiso' ? 'selected' : ''); ?>>Permiso</option>
                  </select>
              </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-inline row">
              <div class="col-md-3">
                  <span><label>Categoría: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectCategoria" name="selectCategoria" class="form-control" required="true">
                  	<?php echo $optionsCategorias; ?>
                  </select>
              </div>
          </div>
        </div>
        <div class="row"></div>
        <div align="center">
          <br>
          <component-button type="submit" id="btnNuevo" name="btnNuevo" class="glyphicon glyphicon-plus" value="Nuevo"></component-button>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
echo "</form>";
include 'includes/footer_Index.inc';
?>