<?php
/**
 * Pagina de consulta de ordenes de compra
 *
 * @category ABC
 * @package ap_grp
 * @author Armando Barrientos Martinez <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/08/2017
 * Fecha Modificación: 21/08/2017
 */
//
include 'includes/session.inc';
$PageSecurity = 2;
$title= "";
$funcion = 1371;

$title= traeNombreFuncion($funcion, $db);

include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include_once 'includes/mail.php';
//Librerias GRID
include 'javascripts/libreriasGrid.inc';

// declaracion de permisos
$permisoNuevaOrden= Havepermission($_SESSION['UserID'], 1238, $db);
$permisoConsolidar= Havepermission($_SESSION['UserID'], 2272, $db);

if (isset($_GET['Orders'])) {
    $Orders = $_GET['Orders'];
} else if (isset($_POST['Orders'])) {
    $Orders = $_POST['Orders'];
} else {
    $Orders = array();
}

if (empty($_POST) == false) {
    $_SESSION['PostSearchData'] = $_POST;
}

if (empty($_SESSION['PostSearchData']) == false) {
    $_POST = $_SESSION['PostSearchData'];
}

if (!isset($_POST['NombreProveedor'])) {
    $_POST['NombreProveedor'] = '*';
}

if (!isset($_POST['ClaveProveedor'])) {
    $_POST['ClaveProveedor'] = '*';
}

if (!isset($_POST['ClaveUserID'])) {
    $_POST['ClaveUserID'] = '*';
}

if (isset($_GET['SelectedStockItem'])) {
    $SelectedStockItem = trim($_GET['SelectedStockItem']);
} elseif (isset($_POST['SelectedStockItem'])) {
    $SelectedStockItem = trim($_POST['SelectedStockItem']);
}

if (isset($_GET['OrderNumber'])) {
    $OrderNumber = trim($_GET['OrderNumber']);
} elseif (isset($_POST['OrderNumber'])) {
    $OrderNumber = trim($_POST['OrderNumber']);
}
if (isset($_GET['SelectedSupplier'])) {
    $SelectedSupplier = trim($_GET['SelectedSupplier']);
} elseif (isset($_POST['SelectedSupplier'])) {
    $SelectedSupplier = trim($_POST['SelectedSupplier']);
}

if (isset($_GET['UnidNeg'])) {
    $UnidNeg = trim($_GET['UnidNeg']);
} elseif (isset($_POST['UnidNeg'])) {
    $UnidNeg = trim($_POST['UnidNeg']);
}

if (isset($_GET['SupplierOrderNo'])) {
    $SupplierOrderNo = trim($_GET['SupplierOrderNo']);
} else if (isset($_POST['SupplierOrderNo'])) {
    $SupplierOrderNo = trim($_POST['SupplierOrderNo']);
} else {
    $SupplierOrderNo = '';
}

if (isset($_GET['legalid'])) {
    $_POST['legalid'] = trim($_GET['legalid']);
} else if (isset($_POST['legalid'])) {
    $_POST['legalid'] = trim($_POST['legalid']);
} else {
    $_POST['legalid'] = '*';
}

if (isset($_GET['SupplierOrderNoSearch'])) {
    $SupplierOrderNoSearch = trim($_GET['SupplierOrderNoSearch']);
} else if (isset($_POST['SupplierOrderNoSearch'])) {
    $SupplierOrderNoSearch = trim($_POST['SupplierOrderNoSearch']);
} else {
    $SupplierOrderNoSearch = '';
}
$InputErro = 0;
if (isset($_POST['Assign'])) {
    if (empty($SupplierOrderNo)) {
        prnMsg(_('No hay un n�mero de orden proveedor definido.'), 'error');
        $InputError = 1;
    }

    if (empty($Orders)) {
        prnMsg(_('No hay ordenes de compra seleccionadas'), 'error');
        $InputError = 1;
    }
}

if (isset($_POST['Assign']) && $InputError == 0) {
    $SQL = " UPDATE purchorders
			 SET supplierorderno = '$SupplierOrderNo'
			 WHERE orderno IN (" . implode(',', $Orders) . ")";
    DB_query($SQL, $db);
    // Reset form values
    $SupplierOrderNo = '';
    $Orders          = array();
}

//echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';
if (isset($_POST['ResetPart'])) {
    unset($SelectedStockItem);
}
if (isset($_POST['FromYear'])) {
    $FromYear = $_POST['FromYear'];
} elseif (isset($_GET['FromYear'])) {
    $FromYear = $_GET['FromYear'];
} else {
    $FromYear = date('Y');
}
if (isset($_POST['FromMes'])) {
    $FromMes = $_POST['FromMes'];
} elseif (isset($_GET['FromMes'])) {
    $FromMes = $_GET['FromMes'];
} else {
    $FromMes = date('m');
}

if (isset($_POST['FromDia'])) {
    $FromDia = $_POST['FromDia'];
} elseif (isset($_GET['FromDia'])) {
    $FromDia = $_GET['FromDia'];
} else {
    $FromDia = date('d');
}

if (isset($_POST['ToYear'])) {
    $ToYear = $_POST['ToYear'];
} elseif (isset($_GET['ToYear'])) {
    $ToYear = $_GET['ToYear'];
} else {
    $ToYear = date('Y');
}

if (isset($_POST['ToMes'])) {
    $ToMes = $_POST['ToMes'];
} elseif (isset($_GET['ToMes'])) {
    $ToMes = $_GET['ToMes'];
} else {
    $ToMes = date('m');
}
if (isset($_POST['ToDia'])) {
    $ToDia = $_POST['ToDia'];
} elseif (isset($_GET['ToDia'])) {
    $ToDia = $_GET['ToDia'];
} else {
    $ToDia = date('d');
}
if (isset($_GET['SearchOrders'])) {
    $_POST['SearchOrders'] = $_GET['SearchOrders'];
}
if (isset($_GET['Stat'])) {
    $_POST['Stat'] = $_GET['Stat'];
}

if (!isset($_POST['Stat'])) {
    $_POST['Stat'] = '*';
}

if (!isset($_POST['legalid'])) {
    $_POST['legalid'] = '0';
}
if (!isset($_POST['unidadnegocio'])) {
    $_POST['unidadnegocio'] = '0';
}

$fechaini = rtrim($FromYear) . '-' . rtrim($FromMes) . '-' . rtrim($FromDia);
$fechafin = rtrim($ToYear) . '-' . rtrim($ToMes) . '-' . rtrim($ToDia);

$fechainic  = mktime(0, 0, 0, rtrim($FromMes), rtrim($FromDia), rtrim($FromYear));
$fechafinc  = mktime(0, 0, 0, rtrim($ToMes), rtrim($ToDia), rtrim($ToYear));
$InputError = 0;

if ($fechainic > $fechafinc) {
    $InputError = 1;
    prnMsg(_('La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha'), 'error');
} else {
    $InputError = 0;
}

?>

<div id="divMensajeOperacion" name="divMensajeOperacion"></div>

<!--Panel Busqueda-->
<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3 text-left">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelOrdenesCompra" aria-expanded="true" aria-controls="collapseOne">
            <b>Criterios de filtrado</b>
          </a>
        </div>
      </h4>
    </div>
    <div id="PanelOrdenesCompra" name="PanelOrdenesCompra" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" data-funcion="<?= $funcion ?>">
      <div class="panel-body">
        
        <!--Izquierda-->
        <div class="col-md-4 text-left">
          <div class="form-inline row" style="display: none;">
              <div class="col-md-3">
                  <span><label>Dependencia: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectRazonSocial" name="selectRazonSocial" class="form-control selectRazonSocial" onchange="fnCambioDependeciaGeneral('selectRazonSocial', 'selectUnidadNegocio')" multiple="true"></select>
              </div>
          </div>
          <!-- <br> -->
          <div class="form-inline row">
              <div class="col-md-3" style="vertical-align: middle;">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadNegocio" name="selectUnidadNegocio[]" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora')" multiple="multiple">
                  </select>
              </div>
          </div>
          <br>
          <div class="form-inline row">
              <div class="col-md-3" style="vertical-align: middle;">
                  <span><label>UE: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadEjecutora" name="selectUnidadEjecutora[]" class="form-control selectUnidadEjecutora" multiple="multiple">
                  </select>
              </div>
          </div>
          <br>
          <div class="form-inline row">
              <div class="col-md-3" style="vertical-align: middle;">
                  <span><label>Estatus: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selEstatusRequisicion" name="selEstatusRequisicion[]" class="form-control selectEstatusOC" multiple="multiple"></select>
              </div>
          </div>
          <br>
          <component-text-label label="Código Expediente:" id="codigoExpediente" name="codigoExpediente" placeholder="Código Expediente" title="Código Expediente" value="" maxlength="20"></component-text-label>
        </div>
        <!--Medio-->
        <div class="col-md-4 text-left">
          <component-number-label label="Folio:" id="txtFolioCompra" name="txtFolioCompra" value="" placeholder="Folio" title="Folio"></component-number-label>
          <br>
          <component-number-label label="Folio Requisición:" id="txtNumeroRequisicion" name="txtNumeroRequisicion" value="" placeholder="Folio Requisición" title="Folio Requisición"></component-number-label>
          <br>
          <component-text-label label="Código Proveedor:" id="txtCodigoProveedor" name="txtCodigoProveedor" value=""></component-text-label>
        </div>
        <!--Derecha-->
        <div class="col-md-4 text-left">
            <component-date-label id="txtFechaInicio" label="Desde: " value="<?= date("d-m-Y")?>"></component-date-label>
            <br>
            <component-date-label id="txtFechaFin" label="Hasta: " value="<?= date("d-m-Y")?>"></component-date-label>
            <br>
            <component-text-label label="Nombre Proveedor:" id="txtNombreProveedor" name="txtNombreProveedor" value=""></component-text-label>
        </div>
        
        <div class="col-md-12">
            <br>
            <component-button type="submit" id="btnBuscarOrdenes" name="btnBuscarOrdenes" class="glyphicon glyphicon-search" onclick="return false;" value="Filtrar"></component-button>
            
            <?php
            
            /*if ($permisoNuevaOrden == 1) {
                echo '<component-button type="submit" id="btnNuevaOrden" name="btnNuevaOrden" class="glyphicon glyphicon-copy" 
                    onclick="return false;" value="Nueva Orden Compra"></component-button>';
            }*/
            
            ?>
        </div>
      </div>
    </div>
</div>

<div name="divTabla" id="divTabla">
  <div name="divCatalogo" id="divCatalogo"></div>
</div>

<br>
<div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
        <a href="" name="Link_NuevoGeneral" id="Link_NuevoGeneral" class="btn btn-primary" style="width: 200px; display: none;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Link Operación</a>
    </div>
</div>
<?php

//echo '</form>';

include 'includes/footer_Index.inc';

?>

<script type="text/javascript" src="javascripts/panel_ordenes_compra.js"></script>

<script type="text/javascript">
function selAll(obj){
    var j = document.getElementById('j').value;
    //alert("valor de :" + j);
    for(i=1; i<=j; i++){
        concatenar = "chk" + i;
        chkobj = document.getElementById(concatenar);
        if(chkobj != null){
            chkobj.checked = obj.checked;
        }
    }

}

</script>
