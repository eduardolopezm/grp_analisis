<?php
/**
 * Locations.php
 * 
 * @category    Modelo
 * @package     ap_grp/modelo
 * @author      JP
 * @version     1.0.0
 * @date:       10.03.18
 *
 * Programa para afectación de páneles, captura y administración de la información de los catálogos.
 * 
 */
session_start();


include ('config_cronjob.php');
include ('android_functions.php');
$host = '23.111.130.190';
$mysqlport = "3306";
$dbuser = 'desarrollo';
$dbpassword = 'p0rtAli70s';
$android_enable = true;
$_SESSION ['DatabaseName'] = 'ap_grp_demo';
$dbsocket = '/home/mysql/mysql.sock';
$dbsocket = '/var/lib/mysql/mysql.sock';
//
// echo "<br>host: ".$host;
// echo "<br>dbuser: ".$dbuser;
// echo "<br>dbpassword: ".$dbpassword;
// echo "<br>DatabaseName: ".$_SESSION ['DatabaseName'];

/* DECLARACIÓN DE VARIABLES */
$PageSecurity = 8;
$PathPrefix = '../';
$funcion = 2508;
$contratoSelected = '';

/* INCLUSIÓN DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
// include($PathPrefix . 'includes/SecurityUrl.php');
include($PathPrefix . 'includes/ConnectDB.inc');
// include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');


/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/
/**
 * Función para la busqueda de la información que llenará la tabla principal
 * @param   [DBInstance]    $db     Instancia de la base de datos
 * @return  [Array]         $data   Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */

/**
 * Función para el guardado de la información de los ítems generados
 * @param   [DBInstance]    $db     Instancia de la base de datos
 * @return  [Array]         $data   Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */

/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/

/* EJECUCIÓN DE FUNCIONES */
$data = call_user_func_array('store',[$db]);
/* MODIFICACIÓN DE HEADER */
header('Content-type:application/json;charset=utf-8');
/* ENVÍO DE INFORMACIÓN */
echo json_encode($data);

/*********************************************** FUNCIONES ÚTILES ***********************************************/


/**
 * Función para la obtención del sql para genrar datos o items
 * @param   Array       $info Datos a ser procesados
 * @return  String      Sql para la ejecución de la generación de datos
 */

function store($db){

    $str_json = file_get_contents('php://input');

    $datos = json_decode($str_json);

    $data['success'] = true;
    $data['msg']  = "";
    $data['pasecobro'] = 0;

    $SQL = "SET NAMES 'utf8'";
    $TransResult = DB_query($SQL, $db);

    foreach ($datos as $valor){
        // reemplazar comillas
        $valor->comentarios  = str_replace('"', '', $valor->comentarios );
        $valor->comentarios  = str_replace("'", "", $valor->comentarios );
        $valor->pagador  = str_replace('"', '', $valor->pagador );
        $valor->pagador  = str_replace("'", "", $valor->pagador );

        $contribuyente = '';
        $nombre = '';
        $cel = '';
        $email = '';
        $add1 = '';
        $add2 = '';
        $add3 = '';
        $add4 = '';
        $add5 = '';
        $add6 = '';
        $SQL = "SELECT
        debtorsmaster.debtorno,
        debtorsmaster.name,
        debtorsmaster.address1,
        debtorsmaster.address1,
        debtorsmaster.address2,
        debtorsmaster.address3,
        debtorsmaster.address4,
        debtorsmaster.address5,
        debtorsmaster.address6,
        debtorsmaster.telefonocelular,
        custbranch.email
        FROM debtorsmaster 
        LEFT JOIN custbranch ON debtorsmaster.debtorno = custbranch.debtorno
        WHERE debtorsmaster.debtorno = '$valor->ic'";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $contribuyente = $myrow ['debtorno'];
            $nombre = $myrow ['name'];
            $add1 = $myrow ['address1'];
            $add2 = $myrow ['address2'];
            $add3 = $myrow ['address3'];
            $add4 = $myrow ['address4'];
            $add5 = $myrow ['address5'];
            $add6 = $myrow ['address6'];
            $cel = $myrow ['telefonocelular'];
            $email = $myrow ['email'];
        }
        if($contribuyente != '' || $contribuyente != null || $contribuyente != 0){
            // Obtener y validar UR
            $tagref = "";
            $SQL = "SELECT ur FROM tb_cat_unidades_ejecutoras WHERE ue = '".$valor->ue."'";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($TransResult) > 0) {
                while ($myrow = DB_fetch_array($TransResult)) {
                    $tagref = $myrow ['ur'];
                }

                // Obtener y validar Objeto principal
                $SQL = "SELECT loccode FROM locations WHERE tipo = 'ObjetoPrincipal' AND loccode = '".$valor->objPrincipal."'";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                if (DB_num_rows($TransResult) > 0) {
                    // Obtener y validar objeto parcial
                    $stockid = $valor->objParcial;
                    $SQL = "SELECT stockid FROM stockmaster WHERE tipo_dato = 2 AND stockid = '".$stockid."'";
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                    if (DB_num_rows($TransResult) > 0) {
                        // Obtener y validar lista de precios
                        $typeabbrev = "";
                        $SQL = "SELECT
                        salestypes.typeabbrev,
                        prices.isRango,
                        prices.price,
                        prices.norango_inicial,
                        prices.norango_final
                        FROM salestypes
                        JOIN prices ON prices.typeabbrev = salestypes.typeabbrev
                        WHERE salestypes.anio = '".date('Y')."'
                        AND prices.stockid = '".$stockid."'";
                        $TransResult = DB_query($SQL, $db, $ErrMsg);
                        if (DB_num_rows($TransResult) > 0) {
                            $myrowPrices = DB_fetch_array($TransResult);
                            $typeabbrev = $myrowPrices ['typeabbrev'];
                            $msjPrecios = "";
                            if ($myrowPrices ['isRango'] == 1) {
                                // Validar rango
                                if (($valor->precio < $myrowPrices ['norango_inicial']) || ($valor->precio > $myrowPrices ['norango_final'])) {
                                    $msjPrecios = "El precio ".$valor->precio." esta fuera de rango. Rango Inicial ".$myrowPrices ['norango_inicial'].", Rango Final ".$myrowPrices ['norango_final'];
                                }
                            } else {
                                // Validar precio
                                if ($valor->precio != $myrowPrices ['price']) {
                                    $msjPrecios = "El precio ".$valor->precio." es diferente a la Tarifa ".$myrowPrices ['price'];
                                }
                            }

                            if (empty($msjPrecios)) {
                                $orderno = GetNextTransNo(30, $db);

                                $sql ="INSERT INTO `salesorders` 
                                (`orderno`, 
                                `debtorno`, 
                                `branchcode`, 
                                `customerref`, 
                                `buyername`, 
                                `comments`, 
                                `orddate`, 
                                `ordertype`, 
                                `shipvia`, 
                                `deladd1`, 
                                `deladd2`, 
                                `deladd3`, 
                                `deladd4`, 
                                `deladd5`, 
                                `deladd6`, 
                                `contactphone`, 
                                `contactemail`, 
                                `deliverto`, 
                                `deliverblind`, 
                                `freightcost`, 
                                `fromstkloc`, 
                                `deliverydate`, 
                                `quotedate`, 
                                `confirmeddate`, 
                                `printedpackingslip`, 
                                `datepackingslipprinted`, 
                                `quotation`, 
                                `placa`, 
                                `serie`, 
                                `kilometraje`, 
                                `salesman`, 
                                `tagref`, 
                                `taxtotal`, 
                                `totaltaxret`, 
                                `currcode`, 
                                `paytermsindicator`, 
                                `contract_type`, 
                                `advance`, 
                                `UserRegister`, 
                                `typeorder`, 
                                `refundpercentsale`, 
                                `vehicleno`, 
                                `idtarea`, 
                                `contid`, 
                                `codigobarras`, 
                                `idprospect`, 
                                `nopedido`, 
                                `noentrada`, 
                                `extratext`, 
                                `noremision`, 
                                `totalrefundpercentsale`, 
                                `puestaenmarcha`, 
                                `paymentname`, 
                                `nocuenta`, 
                                `deliverytext`, 
                                `estatusprocesing`, 
                                `serviceorder`, 
                                `usetype`, 
                                `statuscancel`, 
                                `fromcr`, 
                                `ordenprioridad`, 
                                `discountcard`,
                                `payreference`, 
                                `app_cotizador`, 
                                `ln_ue`, 
                                `id_parka_infraccion`, 
                                `ln_tagref_pase`, 
                                `ln_ue_pase`, 
                                `txt_pagador`,
                                `sn_servicio_web`)
                                VALUES (
                                ".$orderno.", 
                                '".$valor->ic."',
                                '".$valor->ic."', 
                                '', 
                                NULL, 
                                '".($valor->comentarios)."', 
                                '".(date("Y-m-d"))."', 
                                '".$typeabbrev."', 
                                1, 
                                '".$add1."', 
                                '".$add2."', 
                                '".$add3."', 
                                '".$add4."', 
                                '".$add5."', 
                                '".$add6."', 
                                '".$cel."', 
                                '".$email."', 
                                '".$nombre."', 
                                1, 
                                0, 
                                '".$valor->objPrincipal."', 
                                '0000-00-00 00:00:00', 
                                '".(date("Y-m-d"))."',
                                '".(date("Y-m-d"))."', 
                                0, 
                                '0000-00-00', 
                                1, 
                                '', 
                                '', 
                                0, 
                                '', 
                                '".$tagref."', 
                                0, 
                                0, 
                                'MXN', 
                                '01', 
                                0, 
                                0, 
                                'wsPaseCobro', 
                                0, 
                                0, 
                                0, 
                                0, 
                                0, 
                                NULL, 
                                0, 
                                '', 
                                '', 
                                '', 
                                '', 
                                0, 
                                '', 
                                'Efectivo', 
                                'No Identificado', 
                                '', 
                                0, 
                                NULL, 
                                0, 
                                0, 
                                NULL, 
                                0, 
                                '', 
                                '', 
                                0, 
                                '".$valor->ue."', 
                                NULL, 
                                '".$tagref."', 
                                '".$valor->ue."',
                                '".$valor->pagador."',
                                '1')";
                                try {
                                    $result = DB_query($sql, $db);
                                    if($result == true){
                                        $sqlDetails ="INSERT INTO `salesorderdetails` 
                                        (`orderlineno`, 
                                        `orderno`, 
                                        `stkcode`, 
                                        `fromstkloc`, 
                                        `qtyinvoiced`, 
                                        `unitprice`, 
                                        `quantity`, 
                                        `alto`, 
                                        `ancho`, 
                                        `calculatepricebysize`, 
                                        `largo`, 
                                        `quantitydispatched`, 
                                        `ADevengar`, 
                                        `Facturado`, 
                                        `Devengado`, 
                                        `XFacturar`, 
                                        `AFacturar`, 
                                        `XDevengar`, 
                                        `nummes`, 
                                        `refundpercent`, 
                                        `saletype`, 
                                        `estimate`, 
                                        `discountpercent`, 
                                        `discountpercent1`, 
                                        `discountpercent2`, 
                                        `actualdispatchdate`, 
                                        `completed`, 
                                        `narrative`, 
                                        `itemdue`, 
                                        `poline`, 
                                        `warranty`, 
                                        `salestype`, 
                                        `servicestatus`, 
                                        `pocost`, 
                                        `idtarea`, 
                                        `cashdiscount`, 
                                        `showdescrip`, 
                                        `readOnlyValues`, 
                                        `modifiedpriceanddiscount`, 
                                        `totalrefundpercent`, 
                                        `qtylost`, 
                                        `datelost`, 
                                        `woline`, 
                                        `stkmovid`, 
                                        `userlost`, 
                                        `localidad`, 
                                        `stockidKIT`, 
                                        `anticipo`, 
                                        `numPredial`, 
                                        `id_administracion_contratos`, `amt_descuento`)
                                        VALUES (
                                        0, 
                                        ".$orderno.",  
                                        '".$stockid."', 
                                        '".$valor->objPrincipal."',  
                                        0, 
                                        '".$valor->precio."', 
                                        1, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        1, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        '0000-00-00 00:00:00', 
                                        0, 
                                        '', 
                                        '".(date("Y-m-d"))."', 
                                        '', 
                                        0, 
                                        '".$typeabbrev."', 
                                        0, 
                                        NULL, 
                                        0, 
                                        0, 
                                        '1', 
                                        0, 
                                        0, 
                                        0, 
                                        NULL, 
                                        NULL, 
                                        NULL, 
                                        0, 
                                        NULL, 
                                        NULL, 
                                        NULL, 
                                        NULL, 
                                        '', 
                                        0, 
                                        0)";
                                        try {
                                            $result = DB_query($sqlDetails, $db);
                                            if($result == true){
                                                $data['success'] = true;
                                                $data['msg'] = "Se ha generado el pase de cobro: ".$orderno." exitosamente.";
                                                $data['pasecobro'] = $orderno;
                                            }
                                        } catch (Exception $e) {
                                            // captura del error
                                            $data['success'] = false;
                                            $data['msg'] = $e->getMessage();
                                            DB_Txn_Rollback($db);
                                        }
                                    }
                                } catch (Exception $e) {
                                    // captura del error
                                    $data['success'] = false;
                                    $data['msg'] = $e->getMessage();
                                    DB_Txn_Rollback($db);
                                }
                            } else {
                                $data['success'] = false;
                                $data['msg']  = $msjPrecios;
                            }
                        } else {
                            $data['success'] = false;
                            $data['msg']  = "Sin configuración de precio para".$stockid;
                        }
                    } else {
                        $data['success'] = false;
                        $data['msg']  = "El objeto parcial : ".$valor->objParcial." no existe.";
                    }
                } else {
                    $data['success'] = false;
                    $data['msg']  = "El objeto principal : ".$valor->objPrincipal." no existe.";
                }
            } else {
                $data['success'] = false;
                $data['msg']  = "La ue: ".$valor->ue." no existe.";
            }                
        }else{
            $data['success'] = false;
            $data['msg']  = "El contribuyente: ".$valor->ic." no existe.";
        }
    }

    return $data;
}
