<?php
/**
 * Modelo del Panel de Alta de Factura de Ordenes de Compra
 *
 * @category panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/12/2017
 * Fecha Modificación: 01/12/2017
 * Modelo para el proceso de Alta de Factura de Ordenes de Compra
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//
$PageSecurity = 2;
$funcion=2314;
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
include($PathPrefix."includes/SecurityUrl.php");

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

if ($option == 'traeOrdenesCompras') {
    $info = array();
    $condicion= " 1=1 ";
    $fechaini= date("Y-m-d", strtotime($_POST["fechainicio"]));
    $fechafin= date("Y-m-d", strtotime($_POST["fechafin"]));
    $dependencia= $_POST["dependencia"];
    $unidadres= $_POST["unidadres"];
    $idrequisicion= $_POST["requisicion"];
    $idproveedor= $_POST["idproveedor"];
    $nomproveedor= $_POST["nomproveedor"];
    $estatus= $_POST["estatus"];
    $funcion= $_POST["funcion"];
    $noOrdenCompra = $_POST['noOrdenCompra'];

    $seleccionar= "";

    // separar la seleccion multiple de las unidades responsables
    if (is_array($unidadres)) {
        $unidadres= implode(",", $unidadres);
    }

    // separar la seleccion multiple de estatus
    if (is_array($estatus)) {
        $estatus= implode(",", $estatus);
        $estatus.= ",'Autorizado'";
    } else if (!empty($estatus)) {
        $estatus= "'".$estatus."'";
    }

    if (!empty($fechaini) && !empty($fechafin)) {
        $condicion .= " AND purchorders.orddate >= '".$fechaini." 00:00:00' AND purchorders.orddate <='".$fechafin." 23:59:59' ";
    } elseif (!empty($fechaDesde)) {
        $condicion .= " AND purchorders.orddate >= '".$fechaini." 00:00:00' ";
    } elseif (!empty($fechaHasta)) {
        $condicion .= " AND purchorders.orddate <='".$fechafin." 23:59:59' ";
    }

    $condicion .= " AND purchorders.requisitionno!= 0 AND purchorders.requisitionno!= '' ";

    if (!empty($unidadres) && !strpos("@".$unidadres, "-1")) {
        $condicion.= " AND tags.tagref IN (".$unidadres.") ";
    } else {
        $condicion.= " AND tags.legalid='".$dependencia."' AND tags.tagref IN (SELECT tagref FROM sec_unegsxuser WHERE userid= '".$_SESSION["UserID"]."') ";
    }

    if (!empty($idproveedor)) {
        $condicion.= " AND purchorders.supplierno LIKE '%".$idproveedor."%' ";
    }

    if (!empty($nomproveedor)) {
        $condicion.= " AND suppliers.suppname LIKE '%".$nomproveedor."%' ";
    }

    if (!empty($estatus) && !strpos("@".$estatus, "-1")) {
        $condicion.= " AND purchorders.status  IN (".$estatus.") ";
    }

    if (!empty($idrequisicion) && intval($idrequisicion)!= 0) {
        $condicion .= " AND purchorders.requisitionno= '".$idrequisicion."' ";
    }

    if (!empty(trim($noOrdenCompra))) {
        $condicion .= " AND purchorders.realorderno = '".$noOrdenCompra."' ";
    }
    //if ($registro["status"] == 'Completed' && $registro["completed"] == '1' && ($registro["quantityord"] != $registro["qtyinvoiced"])) {}
    // Solo Ordenes de compra para facturar (Recepcion de productos completa)
    $condicion .= " AND purchorders.status = 'Completed' AND purchorderdetails.completed = '1' ";
    $condicionHaving = " HAVING quantityord <> qtyinvoiced ";

    // Consulta para extraer los datos para el panel
    $consulta= "SELECT locationname,
				purchorders.orderno,
				IF(purchorders.supplierorderno IS NULL, 'NA', purchorders.supplierorderno) AS supplierorderno,
				suppliers.suppname,
				suppliers.supplierid,
				DATE_FORMAT(purchorders.orddate,'%Y/%m/%d') as orddate,
				purchorders.initiator,
				tb_botones_status.sn_nombre_secundario,
				purchorders.requisitionno,
				purchorders.allowprint,
				purchorders.tagref,
				purchorders.currcode,
				sum(case when purchorderdetails.quantityord < purchorderdetails.qtyinvoiced THEN 0 ELSE purchorderdetails.quantityord - purchorderdetails.qtyinvoiced end) as productosfacturados,
				SUM((purchorderdetails.unitprice*purchorderdetails.quantityord)*(1-(discountpercent1/100))*(1-(discountpercent2/100))*(1-(discountpercent3/100))) AS ordervalue,
				SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as productospendientes,
				SUM(purchorderdetails.quantityrecd) as productosrecibidos,
				'' as foliofiscal,
				purchorders.wo,
				purchorders.autorizausuario,
				DATE_FORMAT(purchorders.autorizafecha,'%Y/%m/%d') as fechaauto,
				tags.legalid,
				tags.tagref,
				DATE_FORMAT(purchorders.deliverydate,'%d/%m/%Y') as fecharequerida,
				purchorders.comments, purchorders.realorderno,
                purchorderdetails.completed,
                SUM(purchorderdetails.quantityord) AS quantityord,
                SUM(purchorderdetails.qtyinvoiced) AS qtyinvoiced,
                purchorders.status,
                tb_botones_status.sn_funcion_id,
                tags.tagdescription
				FROM purchorders
				INNER JOIN purchorderdetails ON purchorders.orderno = purchorderdetails.orderno AND purchorderdetails.status NOT IN(0,3)
                INNER JOIN tb_botones_status ON purchorders.status= tb_botones_status.statusname AND tb_botones_status.sn_flag_disponible=1 AND (tb_botones_status.sn_funcion_id= '1371' OR (tb_botones_status.sn_funcion_id=2265 AND tb_botones_status.statusname='Autorizado'))
				INNER JOIN tags on purchorders.tagref=tags.tagref
				INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
				INNER JOIN legalbusinessunit on tags.legalid=legalbusinessunit.legalid
				LEFT JOIN locations ON purchorders.intostocklocation = locations.loccode
				LEFT JOIN areas on areas.areacode=tags.areacode
				WHERE ".$condicion."
                GROUP BY locationname,
                purchorders.orderno,
                supplierorderno,
                suppliers.suppname,
                suppliers.supplierid,
                orddate,
                purchorders.initiator,
                tb_botones_status.sn_nombre_secundario,
                purchorders.requisitionno,
                purchorders.allowprint,
                purchorders.tagref,
                purchorders.currcode,
                foliofiscal,
                purchorders.wo,
                purchorders.autorizausuario,
                fechaauto,
                tags.legalid,
                tags.tagref,
                fecharequerida,
                purchorders.comments, purchorders.realorderno,
                purchorderdetails.completed,
                purchorders.status, tb_botones_status.sn_funcion_id, tags.tagdescription
                ".$condicionHaving."
				ORDER BY purchorders.requisitionno desc";
    // INNER JOIN tb_botones_status ON purchorders.status= tb_botones_status.statusname AND tb_botones_status.sn_flag_disponible=1 AND (sn_funcion_id= '".$funcion."' OR (sn_funcion_id=2265 AND tb_botones_status.statusname='Autorizado'))
    // echo "<pre>".$consulta;
    // exit();
    $ErrMsg = "No se pudo obtener la consulta de requisiciones";
    $resultado = DB_query($consulta, $db, $ErrMsg);

    while ($registro= DB_fetch_array($resultado)) {
        $seleccionar= '<input type="checkbox" id="checkbox_'.$registro['requisitionno'].'" name="checkbox_'.$registro['requisitionno'].'" title="Seleccionar" value="'.$registro['requisitionno'].'" />';

        $opciones = "";
        if ($registro['sn_funcion_id'] == '1371') {
            // Impresion OC
            // &OrderNo=107&tipodocto=555&Tagref=100&legalid=1
            $enc = new Encryption;
            $url = "&OrderNo=>".$registro['orderno']."&tipodocto=>555&Tagref=>".$registro['tagref']."&legalid=>".$registro['legalid'];
            $url = $enc->encode($url);
            $liga= "URL=" . $url;

            $opciones .= "<a id='idBtnImpresionOC' target='_blank' href='./PO_PDFPurchOrder.php?".$liga."'><span class=''></span>Imprimir</a><br>";
        }

        if ($registro["status"] == 'Authorised' && $registro["completed"] == '0') {
            // Rececpción OC
            $registro["sn_nombre_secundario"] = "Compra Autorizada";
            // $url = "&PONumber=".$registro['orderno'];
            $enc = new Encryption;
            $url = "&PONumber=>".$registro['orderno'];
            $url = $enc->encode($url);
            $liga= "URL=" . $url;

            $opciones .= "<a id='idBtnRecepcionOC' target='_blank' href='./GoodsReceived.php?".$liga."'><span class=''></span>Recibir</a>";
        }

        if ($registro["status"] == 'Completed' && $registro["completed"] == '1' && ($registro["quantityord"] != $registro["qtyinvoiced"])) {
            // Alta de Factura
            $registro["sn_nombre_secundario"] = "Compra Recibida";
            // $url = "&SupplierID=".$registro['supplierid']."&unidadnegocio=".$registro['tagref'];//."&GoodRecived=YES";
            $enc = new Encryption;
            $url = "&SupplierID=>".$registro['supplierid']."&unidadnegocio=>".$registro['tagref'];
            $url = $enc->encode($url);
            $liga= "URL=" . $url;

            $opciones .= "<a id='idBtnAltaFacturaOC' target='_blank' href='./SupplierInvoice.php?".$liga."'><span class=''></span> Facturar</a>";
        }

        $info[] = array(
            "idrequisicion"=> $registro["requisitionno"],
            "numerorequisicion"=> $registro["requisitionno"],
            "idproveedor" => $registro["supplierid"],
            "nombreproveedor" => ($registro["suppname"]),
            "estatus" => $registro["sn_nombre_secundario"],
            "totalrequisicion" => $registro["ordervalue"],
            "totalrequisicion2" => $registro["ordervalue"],
            "seleccionar" => $seleccionar,
            "fecharequerida" => $registro["fecharequerida"],
            "observaciones" => $registro["comments"],
            "orderno" => $registro["orderno"],
            "ordencompra" => $registro["realorderno"],
            "tagdescription" => $registro["tagref"] . " - " . $registro["tagdescription"],
            "compra" => $opciones
        );
    }

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'nombreExcel' => $nombreExcel);
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
