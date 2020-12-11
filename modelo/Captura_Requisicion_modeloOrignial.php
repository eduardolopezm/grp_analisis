<?php
/**
 * Modelo para el ABC de Finalidad
 *
 * @category ABC
 * @package ap_grp
 * @author Luis Aguilar Sandoval <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Se realizan operación pero el Alta, Baja y Modificación. Mediante las validaciones creadas para la operación seleccionada
 */

 //ini_set('display_errors', 1);
 //ini_set('log_errors', 1);
 //error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

session_start();
$PageSecurity = 4;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
/*
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix.'abajo.php');
include 'includes/DefinePOClass.php';
include 'includes/session.inc';

include 'includes/header.inc';
$funcion = 29;

if ($abajo) {
include($PathPrefix . 'includes/LanguageSetup.php');
}

include($PathPrefix.'includes/SecurityFunctionsHeader.inc');
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc'); */


include($PathPrefix.'abajo.php');
include($PathPrefix.'config.php');
include($PathPrefix.'includes/ConnectDB.inc');

if ($abajo) {
include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion = 2265;
include($PathPrefix.'includes/SecurityFunctionsHeader.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');
include($PathPrefix . 'includes/DateFunctions.inc');



$ErrMsg = _( '' );
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
$TransResult = DB_query ( $SQL, $db );

$option = $_POST['option'];
$proveedor = '111111';
$cvePresupuestal = 'ClavePresupuestal';
$usuarioIniciador = $_SESSION['UserID'];
$usuarioAutorizador = 'Autorizador';
$statusRequisicion = 'Creada';
$periodo = GetPeriod(date('d/m/Y'), $db);
$type = 19;

if ($option == 'loadRequisicion') {
    $req = $_POST['req'];
    $info = array();
    $SQL = "SELECT 
    CONCAT(legalbusinessunit.legalid,' - ',legalbusinessunit.legalname) as rs, 
    CONCAT(tags.tagref,' - ',tags.tagdescription) as ue,  
    purchorders.orderno as idr, purchorders.requisitionno as noReq,
    purchorders.validfrom as fechaCreacion, purchorders.deliverydate as fechadelivery, 
    purchorders.comments as comments
FROM purchorders 
JOIN tags on (purchorders.tagref = tags.tagref) 
JOIN legalbusinessunit on (legalbusinessunit.legalid = tags.legalid )
WHERE purchorders.orderno = '$req'";
    $ErrMsg = "No se encontro la Requisición: ".$req;
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $info[] = array( 
            'rs' => $myrow ['rs'],
            'ue' => $myrow ['ue'],
            'fechaCreacion' => $myrow ['fechaCreacion'],
            'fechadelivery' => $myrow ['fechadelivery'], 
            'comments' => $myrow ['comments'],
            'idr' => $myrow ['idr'],
            'noReq' => $myrow ['noReq']);
    }
    $contenido = array('datos' => $info);
    $result = true;
    
}

if ($option == 'obtenerPresupuesto') {
    $clave = $_POST['clave'];

    $period = GetPeriod(date('d/m/Y'), $db);

    $nombreMes = "";
    $SQL = "SELECT periods.periodno, periods.lastdate_in_period, cat_Months.mes as mesName
                FROM periods 
            LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
            WHERE periods.lastdate_in_period like '%".$myrow ['anho']."%' AND periods.periodno = '".$period."'
            ORDER BY periods.lastdate_in_period asc";
    $resultPeriods = DB_query ( $SQL, $db, $ErrMsg );
    while ( $rowPeriods = DB_fetch_array ( $resultPeriods ) ) {
        $nombreMes = $rowPeriods ['mesName'];
    }

    $res = true;

    $info = fnInfoPresupuesto($db, $clave, $period);

    if (empty($info)) {
        $Mensaje = "No se encontró la información para la Clave Presupuestal ".$clave;
        $res = false;
    }

    $contenido = array('datos' => $info, 'nombreMes' => $nombreMes);
    $result = $res;
}

if ($option == 'mostrarPartidaCveArticulo') {
    $cvePA = $_POST['dato'];
    $tagref = $_POST['datotagref'];
    //$periodo = GetPeriod(date('dd/mm/YY'), $db);
    //$periodo = GetPeriod(date('d/m/Y'), $db);
    //$periodo = '35';
    $info = array();
    $SQL = "SELECT 
    tb_partida_articulo.partidaEspecifica AS idPartidaEspecifica,
    tb_partida_articulo.descPartidaEspecifica AS descPartidaEspecifica,
    stockmaster.stockid AS idProducto,
    stockmaster.description AS descripcionProducto,
    stockmaster.units AS unidad,
    IF(stockcostsxlegal.lastcost >= 0 ,stockcostsxlegal.lastcost,'0')  AS precioEstimado,
    IF(almacen.existencia > 0,'Disponible','No disponible') AS existencia, 
    clave.cvefrom AS cvePresupuestal
FROM tb_partida_articulo
INNER JOIN stockmaster ON tb_partida_articulo.eq_stockid= stockmaster.eq_stockid
LEFT JOIN stockcostsxlegal 
    ON stockmaster.stockid =  stockcostsxlegal.stockid 
    AND stockcostsxlegal.legalid IN(SELECT legalid FROM tags WHERE tagref='$tagref')
LEFT JOIN (SELECT stockid, SUM(quantity) AS existencia FROM locstock 
INNER JOIN sec_loccxusser ON locstock.loccode= sec_loccxusser.loccode AND userid='".$_SESSION['UserID']."' 
GROUP BY stockid) AS almacen ON stockmaster.stockid= almacen.stockid
INNER JOIN (SELECT DISTINCT cvefrom FROM chartdetailsbudgetlog WHERE chartdetailsbudgetlog.tagref='$tagref' AND chartdetailsbudgetlog.partida_esp= '$cvePA' AND chartdetailsbudgetlog.period= '$periodo') AS clave ON 1=1
WHERE  tb_partida_articulo.partidaEspecifica = '$cvePA' 
AND stockmaster.mbflag = 'B'";
    $ErrMsg = "No se obtuvo el Articulo";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $info[] = array( 
            'idProducto' => $myrow ['idProducto'], 
            'descripcionProducto' => $myrow ['descripcionProducto'],
            'unidad' => $myrow ['unidad'], 
            'precioEstimado' => $myrow ['precioEstimado'], 
            'idPartidaEspecifica' => $myrow ['idPartidaEspecifica'],
            'descPartidaEspecifica' => $myrow ['descPartidaEspecifica'],
            'existencia' => $myrow ['existencia'],
            'cvePresupuestal' => $myrow ['cvePresupuestal']  );
    }

    $contenido = array('datos' => $info);
    $result = true;
}


if ($option == 'mostrarCveArticuloDatos') {
    $cveArt = $_POST['datocveart'];
    $descArt = $_POST['datodescart'];
    $tagref = $_POST['datotagref'];
    $info = array();
    $SQL = "SELECT
    stockmaster.mbflag as tipo, 
    tb_partida_articulo.eq_stockid, 
    tb_partida_articulo.partidaEspecifica as idPartidaEspecifica, 
    tb_partida_articulo.descPartidaEspecifica as descPartidaEspecifica,
    stockmaster.stockid as idProducto, 
    stockmaster.description as descripcionProducto, 
    stockmaster.units as unidad,
    IF(stockcostsxlegal.lastcost >= 0 ,stockcostsxlegal.lastcost,'0')  as precioEstimado,  
    locations.tagref as dependencia, 
    locations.loccode as idAlmacen, 
    locations.locationname as almacen,
    locstock.stockid, 
    IF(locstock.quantity > 0, 'Disponible', 'No disponible') as existencia,
    chartdetailsbudgetbytag.tagref as tagref,
    chartdetailsbudgetbytag.accountcode as cvePresupuestal
FROM locstock
JOIN locations ON (locstock.loccode = locations.loccode)
JOIN stockmaster on (locstock.stockid = stockmaster.stockid)
JOIN tb_partida_articulo ON (stockmaster.eq_stockid =  tb_partida_articulo.eq_stockid)
JOIN chartdetailsbudgetbytag on (tb_partida_articulo.partidaEspecifica = chartdetailsbudgetbytag.partida_esp and  chartdetailsbudgetbytag.tagref = '$tagref')
INNER JOIN tags ON chartdetailsbudgetbytag.tagref= tags.tagref and tags.tagref = '$tagref'
LEFT JOIN stockcostsxlegal ON (stockmaster.stockid =  stockcostsxlegal.stockid)
WHERE  stockmaster.stockid = '$cveArt' OR stockmaster.description = '$descArt' AND stockmaster.mbflag = 'B' 
ORDER BY tb_partida_articulo.partidaEspecifica,stockmaster.stockid";
    $ErrMsg = "No se obtuvo el Articulo";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $info[] = array( 
            'idProducto' => $myrow ['idProducto'], 
            'descripcionProducto' => $myrow ['descripcionProducto'],
            'unidad' => $myrow ['unidad'], 
            'precioEstimado' => $myrow ['precioEstimado'], 
            'idPartidaEspecifica' => $myrow ['idPartidaEspecifica'],
            'descPartidaEspecifica' => $myrow ['descPartidaEspecifica'],
            'existencia' => $myrow ['existencia'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarPartidaCveServicio') {
    //$periodo = '35';
    //$periodo = GetPeriod(date('dd/mm/YY'), $db);
    $cvePA = $_POST['datoserv'];
    $tagref = $_POST['datotagref'];
    $info = array();
    $SQL = "SELECT 
    tb_partida_articulo.partidaEspecifica AS idPartidaEspecifica,
    tb_partida_articulo.descPartidaEspecifica AS descPartidaEspecifica,
    stockmaster.stockid AS idServicio,
    stockmaster.description AS descripcionServicio,
    IF(stockcostsxlegal.lastcost >= 0 ,stockcostsxlegal.lastcost,'0')  AS precioEstimado,
    clave.cvefrom AS cvePresupuestal
FROM tb_partida_articulo
INNER JOIN stockmaster ON tb_partida_articulo.eq_stockid= stockmaster.eq_stockid
LEFT JOIN stockcostsxlegal 
    ON stockmaster.stockid =  stockcostsxlegal.stockid 
    AND stockcostsxlegal.legalid IN(SELECT legalid FROM tags WHERE tagref='$tagref')
LEFT JOIN (SELECT stockid, SUM(quantity) AS existencia FROM locstock 
INNER JOIN sec_loccxusser ON locstock.loccode= sec_loccxusser.loccode AND userid='".$_SESSION['UserID']."' 
GROUP BY stockid) AS almacen ON stockmaster.stockid= almacen.stockid
INNER JOIN (SELECT DISTINCT cvefrom FROM chartdetailsbudgetlog WHERE chartdetailsbudgetlog.tagref='$tagref' AND chartdetailsbudgetlog.partida_esp= '$cvePA' AND chartdetailsbudgetlog.period= '$periodo') AS clave ON 1=1
WHERE  tb_partida_articulo.partidaEspecifica = '$cvePA'
AND stockmaster.mbflag = 'D'";
    $ErrMsg = "No se obtuvo el Servicio";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $info[] = array( 
            'idServicio' => $myrow ['idServicio'], 
            'descripcionServicio' => $myrow ['descripcionServicio'],
            'precioEstimado' => $myrow ['precioEstimado'], 
            'idPartidaEspecifica' => $myrow ['idPartidaEspecifica'],
            'descPartidaEspecifica' => $myrow ['descPartidaEspecifica'],
            'cantidad' => $myrow ['cantidad'],
            'cvePresupuestal' => $myrow ['cvePresupuestal']  );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarCveServicioDatos') {
    $cveServ = $_POST['datocveserv'];
    $descServ = $_POST['datodescserv'];
    $tagref = $_POST['datotagref'];
    $info = array();
    $SQL="SELECT 
    stockmaster.stockid as idServicio, 
    stockmaster.description as descripcionServicio, 
    stockmaster.mbflag as tipo,
    tb_partida_articulo.partidaEspecifica as idPartidaEspecifica, 
    tb_partida_articulo.descPartidaEspecifica as descPartidaEspecifica, 1 as cantidad,
    IF(stockcostsxlegal.lastcost >= 0 ,stockcostsxlegal.lastcost,'0')  as precioEstimado 
FROM stockmaster 
JOIN tb_partida_articulo ON (stockmaster.eq_stockid =  tb_partida_articulo.eq_stockid)
LEFT JOIN stockcostsxlegal ON (stockmaster.stockid =  stockcostsxlegal.stockid)
WHERE stockmaster.mbflag = 'D' AND stockmaster.stockid = '$cveServ' OR stockmaster.description = '$descServ'
ORDER BY tb_partida_articulo.partidaEspecifica,stockmaster.stockid;";
    $ErrMsg = "No se obtuvo el Articulo";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $info[] = array( 
            'idServicio' => $myrow ['idServicio'], 
            'descripcionServicio' => $myrow ['descripcionServicio'],
            'precioEstimado' => $myrow ['precioEstimado'], 
            'idPartidaEspecifica' => $myrow ['idPartidaEspecifica'],
            'descPartidaEspecifica' => $myrow ['descPartidaEspecifica'],
            'cantidad' => $myrow ['cantidad'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if($option == 'buscarRequisicion'){
    $requiExistente = $_POST['requi'];
    $info = array();
    $SQL="SELECT 
    purchorders.orderno as orderno , 
    MAX(purchorderdetails.orderlineno_ ) as orden, 
    purchorderdetails.deliverydate as fechaEnt
FROM purchorders
JOIN purchorderdetails on (purchorders.orderno = purchorderdetails.orderno)
WHERE purchorders.orderno = '$requiExistente'
GROUP BY purchorders.orderno ";
    $ErrMsg = "No se encontro ninguna Requisición ";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $info[] = array( 
            'orderno' => $myrow ['orderno'],
            'orden' => $myrow ['orden']);
    }
    $contenido = array('datos' => $info);

    $result = true;
    

}
if ($option == 'requisicionExistente'){
    $reqExistente = $_POST['reqExist'];
    $info = array();
    $SQL="SELECT 
    purchorders.orderno as orderno , 
    purchorders.requisitionno as noRequisition,
    MAX(purchorderdetails.orderlineno_ ) as orden, 
    purchorderdetails.deliverydate as fechaEnt
FROM purchorders
JOIN purchorderdetails on (purchorders.orderno = purchorderdetails.orderno)
WHERE purchorders.orderno = '$reqExistente'
GROUP BY purchorders.orderno ";
    $ErrMsg = "No se encontro ninguna Requisición ";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $info[] = array( 
            'orderno' => $myrow ['orderno'],
            'noRequisition' => $myrow ['noRequisition'],
            'orden' => $myrow ['orden'],
            'fechaEnt' => $myrow ['fechaEnt']);
    }
    $contenido = array('datos' => $info);

    $result = true;

}

if ($option == 'agregarCapturaRequisicion') {
    $nuevaRequisicion = "0";
    $tagref = $_POST['un'];
    $fElaboracion = date("Y-m-d",strtotime($_POST['fechaElabora']));
    $fentrega = date("Y-m-d",strtotime($_POST['fechaEntrega']));
    $razonS = $_POST['rs'];
    $obs = $_POST['obs'];

    $info = array();
    $SQL = "INSERT INTO purchorders 
        (
            supplierno,comments,rate,allowprint,initiator,requisitionno,intostocklocation,
            deladd1,deladd2,deladd3,deladd4,deladd5,deladd6,contact,version,realorderno,deliveryby,
            status,stat_comment,tagref,dateprinted,orddate,validfrom,validto,revised,deliverydate,lastUpdated,
            autorizafecha,fecha_modificacion,consignment,autorizausuario,capturausuario,solicitausuario,status_aurora,
            supplierorderno,currcode,wo,foliopurch,telephoneContact,refundpercentpurch,totalrefundpercentpurch,systypeorder,
            noag_ad,servicetype,clavepresupuestal,fileRequisicion
        ) VALUES
        (
            '$proveedor','$obs',1,1,'$usuarioIniciador','0',4,'dir1','dir2','dir3','dir4','dir5','dir6','','0.00','','','$statusRequisicion',concat(curdate(),' - Order $statusRequisicion ',curdate(),' - Creada: $usuarioIniciador'),'$tagref','0000-00-00 00:00:00',concat('$fElaboracion',' ',TIME(NOW())),'$fElaboracion','$fentrega','0000-00-00','$fentrega',current_timestamp(),'0000-00-00',current_timestamp(),'0','$usuarioAutorizador','$usuarioIniciador','$usuarioIniciador','','','MXN',0,'$nuevaRequisicion','',0,0,0,'',0,'',0
        )";
        $ErrMsg = "No se agrego la nueva Requisición";
        $TransResult = DB_query ( $SQL, $db, $ErrMsg );
        $nuevaRequisicion = DB_Last_Insert_ID($db, 'purchorders', 'OrderNo');
        //$requisitionno = GetNextTransNo(19, $db);
        if($nuevaRequisicion != 0 && !empty($nuevaRequisicion)){
            
                $SQL="UPDATE purchorders set comments = '$obs' WHERE  orderno = '$nuevaRequisicion' ";
                $ErrMsg = "No se pudo modificar la Requisición";
                $TransResult = DB_query ( $SQL, $db, $ErrMsg );
                /*$info[] = array( 
                    'orderno' => $nuevaRequisicion,
                    'requisitionno' => $requisitionno);*/
                $info[] = array( 
                    'orderno' => $nuevaRequisicion);
                //$contenido = "Se actualizo la Requisición con el número: " + $nuevaRequisicion;
                $contenido = array('datos' => $info);
                $result = true;     
        }else{
            $contenido = "Error";
            $result = false;
        }
        /*$info[] = array( 
            'orderno' => $nuevaRequisicion,
            'requisitionno' => $requisitionno);*/
        //$contenido = "Se agrego la Requisición con el número: " + $nuevaRequisicion;
        $contenido = array('datos' => $info);
        $result = true;
        
        
    
}

if ($option == 'agregarElementosRequisicion') {
    $numRequisicion = $_POST['noReq'];
    $deliverydate = date("Y-m-d",strtotime($_POST['fecEn']));
    $ordenElemento = $_POST['orden'];
    $info = array();
    $SQL="INSERT INTO purchorderdetails (
    orderno, itemcode,deliverydate,itemdescription,glcode,qtyinvoiced,unitprice,actprice,stdcostunit,quantityord,quantityrecd,
    shiptref,jobref,completed,itemno,uom,subtotal_amount,package,pcunit,nw,suppliers_partno,gw,cuft,total_quantity,total_amount,
    discountpercent1,discountpercent2,discountpercent3,narrative,justification,refundpercent,lastUpdated,totalrefundpercent,
    estimated_cost,orderlineno_,saleorderno_,wo,qtywo,womasterid,wocomponent,idgroup,typegroup,customs,pedimento,
    dateship,datecustoms,fecha_modificacion,inputport,factorconversion,invoice_rate,flagautoemision,clavepresupuestal, sn_descripcion_larga, renglon, status
) 
VALUES 
(
    '$numRequisicion','no_data','$deliverydate','descripcion','1.1.5.1.1','0','100','100','100','10','0',0,0,0,'','',0,'',0,0,'',0,0,'100','1000',0,0,0,'','',0,current_timestamp(),0,0,'$ordenElemento',0,0,0,'','',0,'','','','0000-00-00','0000-00-00',current_timestamp(),'',1,1,'0','$cvePresupuestal','descLarga','',''
)";
    $ErrMsg = "No se agrego el elemento a la Requisición: ".$numRequisicion;
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    
    $contenido = "Se agrego el elemento a la Requisición: ".$numRequisicion;

    $result = true;

}

if($option == 'mostrarRequisicion'){
    $requi = $_POST['requi'];
    //$o = $_POST['maxorden'];
    $info = array();
    $SQL = "SELECT 
    purchorderdetails.orderno AS idRequisicion, 
    tb_partida_articulo.partidaEspecifica AS idPartida, 
    tb_cat_partidaspresupuestales_partidaespecifica.descripcion AS descPartida,
    purchorderdetails.itemcode AS idItem, 
    purchorderdetails.itemdescription AS descItem, 
    stockmaster.units AS unidad, 
    stockmaster.mbflag AS tipo,
    purchorderdetails.unitprice AS precio, 
    purchorderdetails.quantityord AS cantidad,
    purchorderdetails.total_quantity AS total, 
    if(almacen.existencia = 0,'No Disponible','Disponible') AS existencia,
    purchorderdetails.orderlineno_ AS orden, 
    purchorderdetails.clavepresupuestal AS clavePresupuestal, 
    purchorderdetails.sn_descripcion_larga AS descLarga,
    purchorderdetails.renglon AS renglon
FROM purchorderdetails 
JOIN stockmaster on (stockmaster.stockid = purchorderdetails.itemcode )
JOIN tb_partida_articulo on (tb_partida_articulo.eq_stockid = stockmaster.eq_stockid)
JOIN tb_cat_partidaspresupuestales_partidaespecifica on (tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = tb_partida_articulo.partidaEspecifica)
LEFT JOIN (SELECT stockid, SUM(quantity) AS existencia FROM locstock INNER JOIN sec_loccxusser ON locstock.loccode= sec_loccxusser.loccode AND userid='".$_SESSION['UserID']."'
GROUP BY stockid) AS almacen ON stockmaster.stockid= almacen.stockid
WHERE purchorderdetails.orderno = '$requi' AND purchorderdetails.status ='2'
ORDER BY purchorderdetails.orderlineno_";
    $ErrMsg = "No se encontro la Requisición: ".$requi;
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $info[] = array( 
            'idRequisicion' => $myrow ['idRequisicion'],
            'idPartida' => $myrow ['idPartida'],
            'descPartida' => $myrow ['descPartida'],
            'idItem' => $myrow ['idItem'],
            'descItem' => $myrow ['descItem'], 
            'unidad' => $myrow ['unidad'],
            'tipo' => $myrow ['tipo'],
            'precio' => $myrow ['precio'],
            'cantidad' => $myrow ['cantidad'],
            'total' => $myrow ['total'],
            'existencia' => $myrow ['existencia'],
            'orden' => $myrow ['orden'],
            'clavePresupuestal' => $myrow ['clavePresupuestal'],
            'descLarga' => $myrow ['descLarga'],
            'renglon' => $myrow ['renglon'] );
    }
    $contenido = array('datos' => $info);

    $result = true;


}

if($option == 'modificarElementosRequisicion'){
    $req = $_POST['req'];
    $itemcode = $_POST['itemcode'];
    $fechent = $_POST['fechent'];
    $itemdesc = $_POST['itemdesc'];
    $price = $_POST['price'];
    $cantidad = $_POST['cantidad'];
    $order = $_POST['order'];
    $cvepre = $_POST['cvepre'];
    $almacen = $_POST['almacen'];
    $comments = $_POST['comments'];
    $total_quantity = ($cantidad * $price);
    $total_amount = ($total_quantity * $price);
    $longText = $_POST['longText'];
    $renglon = $_POST['renglon'];
    $info = array();
    echo $longText;
    //$SQL = "";
    $SQL="UPDATE purchorderdetails 
    SET itemcode = '$itemcode', itemdescription= '$itemdesc', unitprice = '$price',actprice = '$price',
    stdcostunit = '$price',quantityord ='$cantidad', itemno = '$itemcode', total_quantity = '$total_quantity', total_amount ='$total_amount', fecha_modificacion = current_timestamp(), clavepresupuestal = '$cvepre', status = 1, sn_descripcion_larga = '$longText', renglon = '$renglon'
    WHERE orderno = '$req' AND orderlineno_ = '$order'";

    $ErrMsg = "No se pudo modificar la Requisición: ".$req;
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    
    $contenido = "Se Modifico la Requisición: ".$req;

    $result = true;

}

if ($option == 'eliminarElementosRequisicion') {

    $nreq = $_POST['noReq'];
    $orden = $_POST['orden'];
    $info = array();
    //$SQL = "DELETE FROM purchorderdetails WHERE orderno = '$nreq' AND orderlineno_ = '$orden'";
    $SQL = "UPDATE purchorderdetails SET status = 3, orderlineno_ = 0  WHERE orderno = '$nreq' AND orderlineno_ = '$orden'";
    $ErrMsg = "No se elimino el elemento a la Requisición: ".$nreq;
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    
    $contenido = "Se elimino el elemento a la Requisición: ".$nreq;

    $result = true;
}

if ($option == 'mostrarCvePresupuestal') {
    $tagrefCveP = $_POST['un'];
    $info = array();
    $SQL="SELECT accountcode,anho,cve_ramo,tagref,ue,cppt,partida_esp FROM chartdetailsbudgetbytag where tgref = '$tagrefCveP'";
    $ErrMsg = "No se obtuvo la Clave Presupuestal";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $info[] = array( 
            'accountcode' => $myrow ['accountcode']);
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarUnidadNegocio') {
    $legalid = $_POST['legalid'];

    $sqlWhere = "";
    if ($legalid != 0 && !empty($legalid)) {
        $sqlWhere = " AND t.legalid IN(".$legalid.") ";
    }
    $info = array();
    $SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription 
            FROM sec_unegsxuser u,tags t 
            join areas ON t.areacode = areas.areacode  
            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' " . $sqlWhere . "
            ORDER BY t.tagref, areas.areacode ";
    $ErrMsg = "No se obtuvieron las URG";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $info[] = array( 'tagref' => $myrow ['tagref'], 'tagdescription' => $myrow ['tagdescription'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'obtenerBotones') {
    $info = array();
    $SQL = "SELECT 
            tb_botones_status.statusid,
            tb_botones_status.statusname,
            tb_botones_status.namebutton,
            tb_botones_status.functionid,
            tb_botones_status.adecuacionPresupuestal,
            tb_botones_status.clases
            FROM tb_botones_status
            JOIN sec_profilexuser ON sec_profilexuser.userid = '".$_SESSION['UserID']."'
            JOIN sec_funxprofile ON sec_funxprofile.profileid = sec_profilexuser.profileid
            WHERE 
            (tb_botones_status.sn_captura_requisicion = 1)
            AND
            (tb_botones_status.functionid = sec_funxprofile.functionid 
            OR 
            tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid)
            )
            GROUP BY tb_botones_status.functionid
            ";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $info[] = array( 
            'statusid' => $myrow ['statusid'], 
            'statusname' => $myrow ['statusname'], 
            'namebutton' => $myrow ['namebutton'],
            'functionid' => $myrow ['functionid'],
            'clases' => $myrow ['clases']
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if($option == 'guardarRequisicionNueva'){
    $req = $_POST['noReq'];
    $status = $_POST['status'];
    $comments = $_POST['comments'];
    $fechaFrom = date("Y-m-d",strtotime($_POST['fechaFrom']));
    $fechaTo = date("Y-m-d",strtotime($_POST['fechaTo']));
    $requisitionno = GetNextTransNo(19, $db);

    $info = array();
    $SQL="UPDATE  purchorders
JOIN purchorderdetails on (purchorders.orderno = purchorderdetails.orderno)
SET purchorders.status = '$status',purchorders.requisitionno = '$requisitionno', purchorders.comments = '$comments',
    purchorders.validfrom = '$fechaFrom', purchorders.validto = '$fechaTo', purchorders.deliverydate = '$fechaTo', 
    purchorders.fecha_modificacion = current_timestamp(), purchorderdetails.fecha_modificacion = current_timestamp(),
    purchorderdetails.status = 2
WHERE purchorders.orderno  = '$req' and purchorderdetails.status = 1";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    
    $contenido = $requisitionno;
    $result = true;
 }

if($option == 'guardarRequisicion'){
    $req = $_POST['noReq'];
    $status = $_POST['status'];
    $comments = $_POST['comments'];
    $fechaFrom = date("Y-m-d",strtotime($_POST['fechaFrom']));
    $fechaTo = date("Y-m-d",strtotime($_POST['fechaTo']));
    $info = array();
    $SQL = "UPDATE  purchorders
JOIN purchorderdetails on (purchorders.orderno = purchorderdetails.orderno)
SET purchorders.status = '$status', purchorders.comments = '$comments',purchorders.validfrom = '$fechaFrom', 
purchorders.validto = '$fechaTo', purchorders.deliverydate = '$fechaTo', purchorders.fecha_modificacion = current_timestamp(), 
purchorderdetails.fecha_modificacion = current_timestamp(), purchorderdetails.status = 2
WHERE purchorders.orderno  = '$req' and purchorderdetails.status in (1,3) and purchorderdetails.orderlineno_ > 0 ";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    //$contenido = array('datos' => $info);
    $contenido = $requisitionno;
    $result = true;
 }
if($option == 'cancelarRequisicion'){
    $req = $_POST['noReq'];
    $status = $_POST['status'];

    $info = array();
    /*$SQL = "UPDATE purchorders SET status = '$status', fecha_modificacion = current_timestamp() where orderno  = '$req'";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );*/
    $SQL = "UPDATE purchorderdetails SET status = 3, orderlineno_= 0, fecha_modificacion = current_timestamp() where orderno  = '$req' and status = 1";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    $contenido = "Se cancelaron todos los elementos no guardados en la requsisicone ";
    $result = true;
 }

if($option == 'avanzarRequisicion'){
    $req = $_POST['noReq'];
    $status = $_POST['status'];
    $info = array();
    $SQL = "UPDATE purchorders SET status = '$status', fecha_modificacion = current_timestamp() where orderno  = '$req' and status = 'Capturado'";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    $contenido = "Se Avanzo la Requisición: ".$req;
    $result = true;
}
if($option == 'rechazarRequisicion'){
    $req = $_POST['noReq'];
    $info = array();
    $SQL = "SELECT 
    orderno, requisitionno, status, 
    if(sn_estatus_anterior=1,'Capturado',if(sn_estatus_anterior=2,'Validar',if(sn_estatus_anterior=3,'PorAutorizar',if(sn_estatus_anterior=4,'Autorizado',0)))) as sn_estatus_anterior, 
    if(sn_estatus_siguiente=1,'Capturado',if(sn_estatus_siguiente=2,'Validar',if(sn_estatus_siguiente=3,'PorAutorizar',if(sn_estatus_siguiente=4,'Autorizado',0)))) as sn_estatus_siguiente
    FROM purchorders p 
    JOIN tb_botones_status tbs on (p.status = tbs.statusname) 
    WHERE orderno = '$req'";
    $ErrMsg = "No se pudo rechazar la requisición";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $statusNuevo = $myrow['sn_estatus_anterior'];
        //echo '<br>'$statusNuevo;
        /*$info[] = array( 
            'orderno' => $myrow ['orderno'], 
            'requisitionno' => $myrow ['requisitionno'], 
            'status' => $myrow ['status'],
            'sn_estatus_anterior' => $myrow ['sn_estatus_anterior'],
            'sn_estatus_siguiente' => $myrow ['sn_estatus_siguiente']
        );*/
        $SQL = "UPDATE purchorders SET status = '$statusNuevo' WHERE orderno = '$req'";
        $ErrMsg = "No se pudo rechazar la requisición";
        $TransResult = DB_query ( $SQL, $db, $ErrMsg );
        //$contenido = array('statusNuevo' => $statusNuevo);
    }
    $contenido = "Se Rechazo la Requisición: ".$req;
    $result = true;
}

if($option == 'reIndexar'){
    $idReq = $_POST['idReq'];
    $info = array();
    $SQL = "SELECT orderlineno_ FROM purchorderdetails 
    WHERE orderno = '$idReq' AND orderlineno_ <> 0 
    ORDER BY orderlineno_";
    $ErrMsg = "No se pudo reindexar";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    $x = 0;
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        //$myrow = DB_fetch_array ( $TransResult );
        $info = array( $myrow ['orderlineno_'] );
        foreach ($info as $key => $value) {
            $x = $x + 1;
            //echo $value;
            //echo "<br>";
            //echo "index: ". $x;
            //echo "<br>";
            $SQL2 ="UPDATE purchorderdetails SET orderlineno_ = '$x' where orderno = '$idReq' and orderlineno_ = '$value'";
            $ErrMsg2 = "No se pudo reindexar";
            $TransResult2 = DB_query ( $SQL2, $db, $ErrMsg2 ); 
        }
    }
    $contenido = "Exito";
    $result = true;

}
if($option == 'limpiar'){
    $idReq = $_POST['idReq'];
    $info = array();
    $SQL = "UPDATE purchorderdetails SET status = 2 WHERE status = 3 AND orderno = '$idReq' and orderlineno_ > 0";
    $ErrMsg = "No se pudo limpiar los registros";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    $SQL2 = "UPDATE purchorderdetails SET orderlineno_ = 0 WHERE status <> 2 AND orderno = '$idReq'";
    $ErrMsg2 = "No se pudo limpiar los registros";
    $TransResult2 = DB_query ( $SQL2, $db, $ErrMsg2 );
    $SQL3 = "UPDATE purchorderdetails SET status = 0 WHERE orderno = '$idReq' AND orderlineno_ = 0";
    $ErrMsg3 = "No se pudo limpiar los registros";
    $TransResult3 = DB_query ( $SQL3, $db, $ErrMsg3 );
    $contenido = "Exito...";
    $result = true;

}
if($option == 'buscarStatusReq'){
    $idReq = $_POST['idReq'];
    $info = array();
    $SQL = "SELECT status FROM purchorders WHERE orderno = '$idReq'";
    $ErrMsg = "No se encontro un status";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $info[] = array( 
            'status' => $myrow ['status']
        );
    }
    $contenido = array('datos' => $info);
    $result = true;
}
if($option == 'validarRequisicion'){
    $req = $_POST['noReq'];
    $status = $_POST['status'];
    $info = array();
    $SQL = "UPDATE purchorders SET status = '$status', fecha_modificacion = current_timestamp() where orderno  = '$req' and status = 'Capturado'";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    $contenido = "Se Valido la Requisición: ".$req;
    $result = true;

}
if($option == 'autorizarRequisicion'){
    $req = $_POST['noReq'];
    $status = $_POST['status'];
    $tagref= $_POST['tagref'];
    $info = array();
    $SQL = "UPDATE purchorders SET status = '$status', fecha_modificacion = current_timestamp() where orderno  = '$req' and status = 'PorAutorizar'";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    $SQL2 = "SELECT 
    p.orderno as idreq, p.requisitionno as noreq, p.status as statusreq, p.tagref as tagref,  
    pd.clavepresupuestal as cvepresupuestal,pd.itemcode,pd.itemdescription as itemdescription, pd.total_quantity as cantidad,
    cdbt.partida_esp as partida
    FROM purchorders p 
    JOIN purchorderdetails pd on (p.orderno = pd.orderno) 
    JOIN chartdetailsbudgetbytag cdbt on (p.tagref = cdbt.tagref and pd.clavepresupuestal = cdbt.accountcode ) 
    WHERE p.orderno = '$req'";

    $ErrMsg2 = "No se obtuvieron los botones para el proceso";
    $TransResult2 = DB_query ( $SQL2, $db, $ErrMsg2 );
    while ( $myrow = DB_fetch_array ( $TransResult2 ) ) {
        $req = $myrow['idreq'];
        $tagref = $myrow['tagref'];
        $clave = $myrow['cvepresupuestal'];
        $cantidad = $myrow['cantidad'];
        $partida_esp = $myrow['partida'];
        $description = $myrow['itemdescription'];
        $validacion = fnInsertPresupuestoLog($db, $type, $req, $tagref, $clave, $periodo, $cantidad*(-1), 263, $partida_esp, $description="insercion presupuesto partida: ".$partida_esp." con producto: ".$description);
    }
    $contenido = "Se Autorizo la Requisición: ".$req;
    $result = true;

}
if($option == 'porAutorizarRequisicion'){
    $req = $_POST['noReq'];
    $status = $_POST['status'];
    $info = array();
    $SQL = "UPDATE purchorders SET status = '$status', fecha_modificacion = current_timestamp() where orderno  = '$req' and status = 'Validar'";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    $contenido = "Se actualizo el status de la Requisición: ".$req;
    $result = true;

}
if($option == 'buscarPerfilUsr'){
    
    $info = array();
    //$SQL = "SELECT userid FROM sec_profilexuser WHERE profileid IN (9,10,11)";
    $SQL = "SELECT userid, profileid FROM sec_profilexuser WHERE userid = '".$_SESSION['UserID']."'";
    $ErrMsg = "No se encontro un perfil";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $info[] = array( 
            'userid' => $myrow ['userid'],
            'profileid' => $myrow ['profileid']
        );
    }
    $contenido = array('datos' => $info);
    $result = true;
}


if ($option == 'predata') {
    $noreq=$_POST['noreq'];
  $info = array();
    //$SQL = "SELECT userid FROM sec_profilexuser WHERE profileid IN (9,10,11)";
    $SQL = "SELECT fecha_modificacion FROM purchorders WHERE orderno = '".$noreq."'";
    $ErrMsg = "No se encontro un requisicion";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );


    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
    $info[] = array( 
            'fecha_modificacion' => $myrow ['fecha_modificacion']
          
        );
    } 
    $contenido = array('predata' => $info);
    $result = true;    

}
if($option == 'muestraInfoAnexo'){
    $idReq = $_POST['idReq'];
    $info = array();
    /*$SQL = "SELECT nu_anexo, nu_tagref, nu_type, nu_requisicion, sn_area, nu_proceso, nu_partida, dt_fecha_creacion, ln_visto_bueno, ln_vobo_requiriente, sn_firma, txt_informacion_creacion, sn_revisado_por, sn_autorizado_por, txt_descripcion_antecedentes, txt_justificacion, ln_viabilidad FROM tb_cnfg_anexo_tecnico WHERE nu_requisicion = '$idReq'";*/
    $SQL="SELECT orderlineno_, 
nu_anexo, nu_tagref, nu_type, nu_requisicion, sn_area, nu_proceso, nu_partida, dt_fecha_creacion, ln_visto_bueno, ln_vobo_requiriente, sn_firma, txt_informacion_creacion, sn_revisado_por, sn_autorizado_por, txt_descripcion_antecedentes, txt_justificacion, ln_viabilidad, txt_bien_serevicio, txt_desc_bien_serevicio, nu_cantidad
FROM purchorderdetails pd
JOIN tb_cnfg_anexo_tecnico ant on (pd.orderno = ant.nu_requisicion)
WHERE orderno = '$idReq' and pd.status = 2 
GROUP BY nu_anexo, nu_partida";
    $ErrMsg = "No se encontro un requisicion";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        $info[] = array(
            //'id1'=>false, 
            'idanexo' => $myrow ['nu_anexo'], 
            'ur' => $myrow ['nu_tagref'], 
            'tipo' => $myrow ['nu_type'],
            'idrequisicion' => $myrow ['nu_requisicion'],
            'area' => $myrow ['sn_area'], 
            'idproceso' => $myrow ['nu_proceso'], 
            //'idpartida' => $myrow ['nu_partida'],
            'idpartida' => '<span id ="'.$myrow ['nu_anexo']."_".$myrow ['nu_partida'].'" onclick="fnAgregaRenglon('.$myrow ['nu_anexo'].','.$myrow ['orderlineno_'].','.$myrow ['nu_partida'].')" class="btn btn-default">'.$myrow ['nu_partida'].'</span>',
            //'idpartida' => '<span id ="'.$myrow ['nu_anexo']."_".$myrow ['nu_partida'].'" onclick="fnAgregaRenglon('.$myrow ['orderlineno_'].','.$myrow ['nu_partida'].')" class="btn btn-default">'.$myrow ['nu_partida'].'</span>',
            //'fecha_creacion' => $myrow ['dt_fecha_creacion'],date("Y-m-d",strtotime($_POST['fecEn']));
            'fecha_creacion' => date("Y-m-d",strtotime($myrow ['dt_fecha_creacion'])),
            'visto_bueno' => $myrow ['ln_visto_bueno'],
            'vobo_requiriente' => $myrow ['ln_vobo_requiriente'],
            'firma' => $myrow ['sn_firma'],
            'informacion_creacion' => $myrow ['txt_informacion_creacion'],
            'revisado_por' => $myrow ['sn_revisado_por'],
            'autorizado_por' => $myrow ['sn_autorizado_por'],
            'descripcion_antecedentes' => $myrow ['txt_descripcion_antecedentes'],
            'justificacion' => $myrow ['txt_justificacion'],
            'viabilidad' => $myrow ['ln_viabilidad'],
            'bienServicio' => $myrow ['txt_bien_serevicio'],
            'descripcion_bien_serv' => $myrow ['txt_desc_bien_serevicio'],
            'cantidad' => $myrow ['nu_cantidad']
        );
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    //$columnasNombres .= "{ name: 'id1', type: 'bool'},";
    $columnasNombres .= "{ name: 'idanexo', type: 'string' },";
    //$columnasNombres .= "{ name: 'idrequisicion', type: 'string' },";
    $columnasNombres .= "{ name: 'idpartida', type: 'string' },";
    //$columnasNombres .= "{ name: 'area', type: 'string' },";
    //$columnasNombres .= "{ name: 'idproceso', type: 'string' },";
    //$columnasNombres .= "{ name: 'fecha_creacion', type: 'string' },";
    //$columnasNombres .= "{ name: 'visto_bueno', type: 'string' },";
    //$columnasNombres .= "{ name: 'vobo_requiriente', type: 'string' },";
    //$columnasNombres .= "{ name: 'firma', type: 'string' },";
    //$columnasNombres .= "{ name: 'informacion_creacion', type: 'string' },";
    //$columnasNombres .= "{ name: 'revisado_por', type: 'string' },";
    //$columnasNombres .= "{ name: 'autorizado_por', type: 'string' },";
    //$columnasNombres .= "{ name: 'descripcion_antecedentes', type: 'string' }";
    //$columnasNombres .= "{ name: 'justificacion', type: 'string' },";
    //$columnasNombres .= "{ name: 'viabilidad', type: 'string' }";
    $columnasNombres .= "{ name: 'bienServicio', type: 'string' },";
    $columnasNombres .= "{ name: 'descripcion_bien_serv', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    //$columnasNombresGrid .= " { text:'#', datafield:'id1',editable: true,columntype: 'checkbox', width: '5%', cellsalign: 'center', align: 'center', editoptions:{value:'true:false'}},";
    $columnasNombresGrid .= " { text: 'Anexo', datafield: 'idanexo', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Partida', datafield: 'idpartida', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Bien/Servicio', datafield: 'bienServicio', width: '40%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'descripcion_bien_serv', width: '45%', cellsalign: 'center', align: 'center', hidden: false }";
    //$columnasNombresGrid .= " { text: 'Requisición', datafield: 'idrequisicion', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    //$columnasNombresGrid .= " { text: 'Area', datafield: 'area', width: '25%', hidden: false },";
    //$columnasNombresGrid .= " { text: 'Proceso', datafield: 'idproceso', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    //$columnasNombresGrid .= " { text: 'Partida', datafield: 'idpartida', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    //$columnasNombresGrid .= " { text: 'Fecha Creación', datafield: 'fecha_creacion', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    //$columnasNombresGrid .= " { text: 'Visto Bueno', datafield: 'visto_bueno', width: '10%', hidden: false },";
    //$columnasNombresGrid .= " { text: 'VoBo Requiriente', datafield: 'vobo_requiriente', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    //$columnasNombresGrid .= " { text: 'Firmas', datafield: 'firma', width: '5%', cellsalign: 'right', align: 'center', hidden: false },";
    //$columnasNombresGrid .= " { text: 'Información', datafield: 'informacion_creacion', width: '25%', cellsalign: 'center', align: 'center', hidden: false },";
    //$columnasNombresGrid .= " { text: 'Revisado por', datafield: 'revisado_por', width: '5%', cellsalign: 'right', align: 'center', hidden: false },";
    //$columnasNombresGrid .= " { text: 'Autorizado por', datafield: 'autorizado_por', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    //$columnasNombresGrid .= " { text: 'Antecedentes', datafield: 'descripcion_antecedentes', width: '25%', cellsalign: 'center', align: 'center', hidden: false }";
    //$columnasNombresGrid .= " { text: 'Justificación', datafield: 'justificacion', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    //$columnasNombresGrid .= " { text: 'Viabilidad', datafield: 'viabilidad', width: '10%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid);
    $result = true;

}
$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);

echo json_encode($dataObj);

?>