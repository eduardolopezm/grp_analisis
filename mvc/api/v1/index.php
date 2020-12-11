<?php
/*
$PathPrefix = "../../.././";
ob_start();
include_once $PathPrefix . 'includes/session.inc';
ob_end_clean();
*/ 
//
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);

/**** Archivos para usar funciones del ERP ****/

// Variable usada en alguno de los includes se debe cambiar eso
// algun dia :(
/*$_SESSION['DatabaseName'] = "";
$_SESSION['UserID'] = "admin";
$_SESSION['DefaultDateFormat'] = "d/m/Y";*/
// Este valor tiene por default la tabla config en todas
// las bases de datos
//$_SESSION['MonthsAuditTrail'] = 3;

$PathPrefix = "../../.././";

// Estas funciones deberian estar en una clase la que controla la conexion 
/*
ob_start();
include_once $PathPrefix . 'includes/session.inc';
ob_end_clean();
*/
session_start();
ob_start();
// $_SESSION['UserID'] = "admin";
// $_SESSION['DatabaseName'] = "erpjibe";
require_once $PathPrefix . 'includes/ConnectDB_mysqli_function.php';
require_once $PathPrefix . 'includes/SQL_CommonFunctions.inc';
require_once $PathPrefix . 'includes/NCreditoDirectaAnticipo.inc';
require_once $PathPrefix . 'includes/DateFunctions.inc';
require_once $PathPrefix . 'includes/XSAInvoicing.inc';
require_once $PathPrefix . 'includes/SendInvoicingV6_0.php';
require_once '../.././Numbers/Words.php';

/*include_once($PathPrefix . 'includes/fpdf.php');
include_once($PathPrefix . 'includes/fpdi.php');
include_once($PathPrefix . 'companies/'.$_SESSION['DatabaseName'].'/PDFCotizacionTemplateV2.inc');*/

/**** Archivos para usar funciones del ERP ******/

require '../../.././includes/Slim/Slim.php';

\Slim\Slim::registerAutoloader();


$app = new \Slim\Slim();

$app->config('debug', true);

/**
 * Imprime el parametro $response como un objeto JSON
 * @param  Integer $status_code Estatus HTTP de la solicitudx
 * @param  Array   $response    Array que necesita ser convertido a un objeto JSON
 */
function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();

    $app->status($status_code);
    $app->contentType('application/json');

    echo json_encode($response);
}

/*OBTENER PEDIDO DE VENTA DEFAULT*/
$app->get('/getdefaultsalesorders', function() {
    $pathprefix = "../.././";
    
    require_once $pathprefix . 'dao/Salesordersdao.php';

    $objeto = new Salesordersdao();
    $json = $objeto->Setdefaultsalesorder();
    echoResponse(200, $json);

});

/*BUSQUEDA DE METODOS DE PAGO*/
$app->get('/paymentmethods', function() {
    $pathprefix = "../.././";
    
    require_once $pathprefix . 'dao/Salesordersdao.php';

    $objeto = new Salesordersdao();
    $json = $objeto->Setpaymentmethods();
    echoResponse(200, $json);

});

/*BUSQUEDA DE TIPO DE COMPROBANTE*/
$app->get('/getTipoComprobante', function() {
    $pathprefix = "../.././";
    
    require_once $pathprefix . 'dao/Salesordersdao.php';

    $objeto = new Salesordersdao();
    $json = $objeto->SetTipoComprobante();
    echoResponse(200, $json);

});

/*BUSQUEDA DE USO DE CFDI*/
$app->get('/getUsoCFDI', function() {
    $pathprefix = "../.././";
    
    require_once $pathprefix . 'dao/Salesordersdao.php';

    $objeto = new Salesordersdao();
    $json = $objeto->SetUsoCFDI();
    echoResponse(200, $json);

});

/*BUSQUEDA DE USO DE CFDI*/
$app->get('/getMetodoPago', function() {
    $pathprefix = "../.././";
    
    require_once $pathprefix . 'dao/Salesordersdao.php';

    $objeto = new Salesordersdao();
    $json = $objeto->SetMetodoPago();
    echoResponse(200, $json);

});

/*OBTENER CLIENTES POR ID*/
$app->get('/sucursales/:id', function($id) {
    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Custbranchdao.php';
    require_once $pathprefix . 'model/Custbranch.php';

    $objeto = new Custbranchdao();
    $json = $objeto->Custbranchbyid($id);
    echoResponse(200, $json);

});

/*OBTENER DETALLES DE PRODUCTO X ID*/
$app->get('/getproduct/:id', function($id) {
    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Stockmasterdao.php';
    require_once $pathprefix . 'model/Stockmaster.php';

    $objeto = new Stockmasterdao();
    $json = $objeto->Stockbyid($id);
    echoResponse(200, $json);
});

/*BUSQUEDA DE PRODUCTOS POR DESCRIPCION*/
$app->get('/getquotationbydesc/:description', function($description) {
    $pathprefix = "../.././";
    
    require_once $pathprefix . 'dao/Salesordersdao.php';
    
    $objeto = new Salesordersdao();
    $json = $objeto->Searchquotationsbydesc($description);
    echoResponse(200, $json);

});

/*BUSQUEDA DE PRODUCTOS POR DESCRIPCION*/
$app->get('/getproducts/:description/:tagref/:loccode/:currency/:typeabbrev', function($description, $tagref, $loccode, $currency, $typeabbrev) {
    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Stockmasterdao.php';
    
    $objeto = new Stockmasterdao();
    $json = $objeto->Searchproducts($description,$tagref,$loccode,$currency,$typeabbrev);
    echoResponse(200, $json);

});

/*OBTIENE EXISTENCIAS POR ALMACENCES*/
$app->get('/getstockbylocation/:stockid/:location/:exis', function($stockid, $location, $exis) {
    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Stockmasterdao.php';
    
    $objeto = new Stockmasterdao();
    $json = $objeto->Getstockbylocation($stockid,$location,$exis);
    echoResponse(200, $json);
    
});

/*OBTIENE ANTICIPOS CLIENTE type 130*/
$app->get('/getAnticiposCliente/:branchcode/:tagref/:currency', function($branchcode, $tagref, $currency) {
    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Salesordersdao.php';
    
    $objeto = new Salesordersdao();
    $json = $objeto->SetAnticipoCliente($branchcode,$tagref,$currency);
    echoResponse(200, $json);
});

/*BUSQUEDA DE COTIZACIONES*/
$app->get('/getquotation/:orderno/', function($orderno) {
    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Salesorderdetailsdao.php';
    
    $json = array();

    $found = true;

    if($found) {
        $json['success'] = $found;
        $json['message'] = "Se encontro la cotizacion";

        $objeto = new Salesorderdetailsdao();
        $products = $objeto->getSalesOrderDetailsByOrderNo($orderno);

        $json['products'] = $products;
    } else {
        $json['success'] = $found;
        $json['message'] = "No se encontro una cotizacion con ese numero";
    }
    
    echoResponse(200, $json);
});

/*BUSQUEDA DE CLIENTES POR DESCRIPCION*/
$app->get('/getdebtors/:description/', function($description) {
    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Debtorsmasterdao.php';
    
    $objeto = new Debtorsmasterdao();
    $json = $objeto->Searchdebtors($description);
    echoResponse(200, $json);

});

/*OBTIENE PARTIDAS DE PEDIDO DE VENTA*/
$app->get('/getsalesorderdetail/:stockid/:tagref/:location/:currency', function($stockid, $tagref, $location, $currency) {
    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Salesorderdetailsdao.php';
    
    $objeto = new Salesorderdetailsdao();
    $json = $objeto->Getsalesorderdetails($stockid, $tagref, $location, $currency);
    echoResponse(200, $json);

});

/*OBTIENE UNIDADES DE NEGOCIO */
$app->get('/gettags', function() {
    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Tagsdao.php';
    
    $objeto = new Tagsdao();
    $json = $objeto->Gettags();
    echoResponse(200, $json);

});

/*OBTIENE LOCALIDADES DE ALMACEN */
$app->get('/getlocations/:tagref', function($tagref) {
    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Locationsdao.php';
    
    $objeto = new Locationsdao();
    $json = $objeto->Getlocations($tagref);
    echoResponse(200, $json);
});

/*OBTIENE METODO DE PAGO DE ALMACEN */
$app->get('/getlocationpayment/:loccode', function($loccode) {
    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Locationsdao.php';
    
    $objeto = new Locationsdao();
    $json = $objeto->GetlocationsPayment($loccode);
    echoResponse(200, $json);
});

/*****OBTIENE VENDEDORES*****/
$app->get('/getsalesman', function() {
    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Salesmandao.php';
    
    $objeto = new Salesmandao();
    $json = $objeto->Getsalesman();
    echoResponse(200, $json);
});

/*****OBTIENE LISTAS DE PRECIOS*****/
$app->get('/salestypes/:branchcode', function($branchcode) {
    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Salestypes.php';
    
    $objeto = new Salestypes();
    $json = $objeto->Getsalestypes($branchcode);
    echoResponse(200, $json);
});

/*OBTIENE DENOMINACION DE MONEDAS */
$app->get('/getcurrencydenominatios', function() {
    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Currencydenominationsdao.php';
    
    $objeto = new Currencydenominationsdao();
    $json = $objeto->Getdenominations();
    echoResponse(200, $json);
});

/*INSERTA PEDIDO DE VENTA***/
$app->post('/setsalesorders', function() use($app){

    global $db;

    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Salesordersdao.php';
    
    $body = $app->request->getBody();
    $params = json_decode($body); 

    $objeto = new Salesordersdao();
    $json = $objeto->Setsalesorders($params, $db);
    
    echoResponse('200', $json);
});

/*ENVIAR COTIZACION***/
$app->post('/sendmailquotation', function() use($app){

    global $db;

    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Functions.php';
    
    $body = $app->request->getBody();
    $params = json_decode($body); 

    $objeto = new Functions();
    $json = $objeto->SendQuotation($params, $db);
    
    echoResponse('200', $json);
});

/* Realizar traspaso y recepcion de productos*/
$app->post('/settransfer', function() use($app) {
    global $db;
    
    $pathprefix = "../.././";
    require_once $pathprefix . 'dao/Stockloctransferdao.php';
    
    $body = $app->request->getBody();
    $params = json_decode($body); 
    
    $objeto = new Stockloctransferdao();
    $json = $objeto->setTranferReceive($params);
    
    echoResponse('200', $json);
});

/* Obtiene si existe session activa */
$app->get('/getsession', function() {

    $success = true;

    if (empty($_SESSION['UserID'])) {
        $success = false;
    }

    $response['success'] = $success;

    $json = $response;

    echoResponse(200, $json);

});

$app->run();










