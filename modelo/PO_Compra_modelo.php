<?php
/**
 * Captura de la Requisición
 *
 * @category     ABC
 * @package      ap_grp
 * @author       Luis Aguilar Sandoval <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Se realizan operación de captura de la Requisición.
 */
$PageSecurity = 2;
$funcion=29;
$PathPrefix = '../';

session_start();

// incluir archivos de apoyo
include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

// declaracion de variables locales
$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

if ($option == 'traeRequicionesAutorizadas') {
    $idReq = $_POST['idReq'];
    $noReq = $_POST['noReq'];
    $info = array();
    $SQL = "SELECT 
    po.orderno as idRequisicion, po.requisitionno as noRequisicion, po.comments as comments,
    po.status as statusReq, po.tagref as tagref, po.validfrom as FechaIni, po.validto as fechaFin, 
    pod.itemcode as itemcode,pod.itemdescription as itemdescription, 
    po.currcode as currenci, pod.unitprice as precio, pod.quantityord as cantidad, pod.total_quantity as totalCantidad, 
    pod.orderlineno_ as orden, 
    pod.clavepresupuestal as clavepresupuestal, 
    pod.sn_descripcion_larga as descripcionLarga,pod.renglon as renglon, pod.status as statusItem
    FROM purchorders po
    JOIN purchorderdetails pod ON (po.orderno = pod.orderno)
    WHERE po.status = 'Autorizado' and pod.status = 2 and po.orderno = '$idReq' and po.requisitionno = '$noReq'
    ORDER BY pod.orderlineno_ ";
    $ErrMsg = "No se obtuvieron las Requisiciones";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'idRequisicion' => $myrow ['idRequisicion'],
            'noRquisicion' => $myrow ['noRequisicion'],
            'ur' => $myrow ['tagref'],
            'comments' => $myrow ['comments'],
            'fechaInicio' => $myrow ['FechaIni'],
            'fechaRequerida' => $myrow ['FechaFin'],
            'itemcode' => $myrow ['itemcode'],
            'itemdescription' => $myrow ['itemdescription'],
            'currenci' => $myrow ['currenci'],
            'precio' => $myrow ['precio'],
            'cantidad' => $myrow ['cantidad'],
            'total' => $myrow ['totalCantidad'],
            'orden' => $myrow ['orden'],
            'clavePresupuestal' => $myrow ['clavepresupuestal'],
            'descripcionLarga' => $myrow ['descripcionLarga'],
            'renglon' => $myrow ['renglon']
        );
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'orden', type: 'string' },";
    $columnasNombres .= "{ name: 'idRequisicion', type: 'string' },";
    $columnasNombres .= "{ name: 'noRquisicion', type: 'string' },";
    $columnasNombres .= "{ name: 'ur', type: 'string' },";
    $columnasNombres .= "{ name: 'comments', type: 'string' },";
    $columnasNombres .= "{ name: 'fechaInicio', type: 'string' },";
    $columnasNombres .= "{ name: 'fechaRequerida', type: 'string' },";
    $columnasNombres .= "{ name: 'itemcode', type: 'string' },";
    $columnasNombres .= "{ name: 'itemdescription', type: 'string' },";
    $columnasNombres .= "{ name: 'currenci', type: 'string' },";
    $columnasNombres .= "{ name: 'precio', type: 'string' },";
    $columnasNombres .= "{ name: 'cantidad', type: 'string' },";
    $columnasNombres .= "{ name: 'total', type: 'string' },";
    $columnasNombres .= "{ name: 'clavePresupuestal', type: 'string' },";
    $columnasNombres .= "{ name: 'descripcionLarga', type: 'string' },";
    $columnasNombres .= "{ name: 'renglon', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: '#', datafield: 'orden', width: '3%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'IdRequisicion', datafield: 'idRequisicion', width: '5%', cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'NoRequisicion', datafield: 'noRquisicion', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'ur', width: '3%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Observaciones', datafield: 'comments', width: '12%', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Elaboración', datafield: 'fechaInicio', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Requerida', datafield: 'fechaRequerida', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Codigo', datafield: 'itemcode', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'itemdescription', width: '20%', hidden: false },";
    $columnasNombresGrid .= " { text: 'Currenci', datafield: 'currenci', width: '3%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Precio', datafield: 'precio', width: '5%', cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false },";
    $columnasNombresGrid .= " { text: 'Cantidad', datafield: 'cantidad', width: '4%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Total', datafield: 'total', width: '5%', cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false },";
    $columnasNombresGrid .= " { text: 'Clavepresupuestal', datafield: 'clavePresupuestal', width: '13%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'DescripcionLarga', datafield: 'descripcionLarga', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Renglon', datafield: 'renglon', width: '5%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid);
    $result = true;
}
if ($option == 'traeProveedores') {
    $condicion = "";
    $nombreProveedor = $_POST['nombreProveedor'];
    $codeProveedor = $_POST['codeProveedor'];
    $rfcProveedor = $_POST['rfcProveedor'];
    if (!empty($nombreProveedor)) {
        $condicion.= " AND suppname LIKE '%".$nombreProveedor."%' ";
    }
    if (!empty($codeProveedor)) {
        $condicion.= " AND supplierid LIKE '%".$codeProveedor."%' ";
    }
    if (!empty($rfcProveedor)) {
        $condicion.= " AND taxid LIKE '%".$rfcProveedor."%' ";
    }

    $SQL="SELECT supplierid, suppname, address1, address2, address3, address4,currcode,taxid, p.terms FROM suppliers s, paymentterms p WHERE p.termsindicator=s.paymentterms ".$condicion." ORDER BY suppname ASC";
    $ErrMsg = "No se encontro proveedores";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            //'supplierid' => $myrow ['supplierid'],
            'supplierid' => //'<a class="bgc8 ftc2 borderRadius p5" onclick="fnSeleccionarProveedor(\''.$myrow ['supplierid'].'\')"><span>'.$myrow ['supplierid'].'</span></a>'
            '<input type="submit"  name="Select" id="Select" class="btn btn-default botonVerde m0 p0" value="' . $myrow['supplierid'] . '" >',
            'suppname' => $myrow ['suppname'],
            'address1' => $myrow ['address1'],
            'address2' => $myrow ['address2'],
            'address3' => $myrow ['address3'],
            'address4' => $myrow ['address4'],
            'currcode' => $myrow ['currcode'],
            'taxid' => $myrow ['taxid']
            //'seleccionar' => "<span onclick='fnOrdenCompra()' class='glyphicon glyphicon-flag'></span>"
            //'seleccionar' => '<a onclick="fnSeleccionarProveedor(\''.$myrow ['supplierid'].'\')"><span class="glyphicon glyphicon-bookmark"></span></a>'
        );
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'supplierid', type: 'string' },";
    $columnasNombres .= "{ name: 'suppname', type: 'string' },";
    $columnasNombres .= "{ name: 'taxid', type: 'string' },";
    $columnasNombres .= "{ name: 'address1', type: 'string' },";
    $columnasNombres .= "{ name: 'address2', type: 'string' },";
    $columnasNombres .= "{ name: 'address3', type: 'string' },";
    $columnasNombres .= "{ name: 'address4', type: 'string' },";
    $columnasNombres .= "{ name: 'currcode', type: 'string' }";
    //$columnasNombres .= "{ name: 'seleccionar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Proveedor', datafield: 'supplierid', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Nombre', datafield: 'suppname', width: '25%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'RFC', datafield: 'taxid', width: '12%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Colonia', datafield: 'address1', width: '12%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Calle', datafield: 'address2', width: '12%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Ciudad', datafield: 'address3', width: '12%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estado', datafield: 'address4', width: '12%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Moneda', datafield: 'currcode', width: '5%', cellsalign: 'center', align: 'center', hidden: false }";
    //$columnasNombresGrid .= " { text: 'Sel', datafield: 'seleccionar', width: '3%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid);
    $result = true;
}
if ($option == 'muestraProveedor') {
    $idProveedor = $_POST['supplierid'];
    $SQL="SELECT supplierid, suppname, address1, address2, address3, address4,currcode,taxid, p.terms FROM suppliers s, paymentterms p WHERE p.termsindicator=s.paymentterms and supplierid = '$idProveedor' ORDER BY suppname ASC";
    $ErrMsg = "No se encontro proveedor";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'supplierid' => $myrow ['supplierid'],
            'suppname' => $myrow ['suppname'],
            'address1' => $myrow ['address1'],
            'address2' => $myrow ['address2'],
            'address3' => $myrow ['address3'],
            'address4' => $myrow ['address4'],
            'currcode' => $myrow ['currcode'],
            'taxid' => $myrow ['taxid']
        );
    }
    $contenido = array('datos' => $info);
    $result = true;
}
if ($option == 'agregarElementosRequisicion') {
    $idRequisicion = $_POST['idReq'];
    $info = array();
    $SQL="SELECT po.orderno,max(pod.orderlineno_) as orden ,pod.deliverydate as deliverydate, po.tagref as tagref
FROM purchorderdetails pod 
JOIN purchorders po on(pod.orderno = po.orderno)
WHERE po.orderno = '$idRequisicion'
GROUP BY po.orderno  ";
    $ErrMsg = "error buscar elementos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $myrow = DB_fetch_array($TransResult);
    $maxOrden = $myrow['orden']+ 1;
    $deliverydate = $myrow['deliverydate'];
    $tagref = $myrow['tagref'];
    $SQL2 = "INSERT INTO purchorderdetails (
    orderno, itemcode,deliverydate,itemdescription,glcode,qtyinvoiced,unitprice,actprice,stdcostunit,quantityord,quantityrecd,
    shiptref,jobref,completed,itemno,uom,subtotal_amount,package,pcunit,nw,suppliers_partno,gw,cuft,total_quantity,total_amount,
    discountpercent1,discountpercent2,discountpercent3,narrative,justification,refundpercent,lastUpdated,totalrefundpercent,
    estimated_cost,orderlineno_,saleorderno_,wo,qtywo,womasterid,wocomponent,idgroup,typegroup,customs,pedimento,
    dateship,datecustoms,fecha_modificacion,inputport,factorconversion,invoice_rate,flagautoemision,clavepresupuestal, sn_descripcion_larga, renglon, status
) 
VALUES 
(
    '$idRequisicion','no_data','$deliverydate','descripcion','1.1.5.1.1','0','100','100','100','10','0',0,0,0,'999999999','',0,'',0,0,'',0,0,'100','1000',0,0,0,'','',0,current_timestamp(),0,0,'$maxOrden',0,0,0,'','',0,'','','','0000-00-00','0000-00-00',current_timestamp(),'',1,1,'0','cvePresupuestal','descLarga','',''
)";
    $ErrMsg2 = "error agregar elementos";
    $TransResult2 = DB_query($SQL2, $db, $ErrMsg2);
    
    $info[] = array(
                    'orden' => $maxOrden,
                    'tagref' => $tagref);
    
    $contenido = array('datos' => $info);
    $result = true;
}

$dataObj = array(
    'sql' => '',
    'contenido' => $contenido,
    'result' => $result,
    'RootPath' => $RootPath,
    'ErrMsg' => $ErrMsg,
    'Mensaje' => $Mensaje);

echo json_encode($dataObj);
