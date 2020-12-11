<?php
/**
 * Modelo del Panel de Recepcion de Ordenes de Compra
 *
 * @category panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/12/2017
 * Fecha Modificación: 01/12/2017
 * Modelo para el proceso de Recepcion de Ordenes de Compra
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//
$PageSecurity = 2;
$funcion=2313;
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
include($PathPrefix .'includes/DateFunctions.inc');

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

if ($option == 'reversaOrdenCompra') {
    $dataJsonNoCapturaSeleccionados = $_POST['dataJsonNoCapturaSeleccionados'];
    $statusid = $_POST['statusid'];
    $tipoReversa = $_POST['tipoReversa'];

    $info = array();
    $mensajeErrores = "";
    $result = true;
    foreach ($dataJsonNoCapturaSeleccionados as $datosClave) {
        $actualizar = 1;
        
        if ($tipoReversa == 1) {
            // Reversar orden de compra autorizada
            // $trasnoPoliza = 0;
            // $SQL = "SELECT
            // MAX(gltrans.typeno) as transno
            // FROM gltrans
            // WHERE gltrans.type = 555 AND gltrans.purchno = '".$datosClave ['orderno']."'";
            // $result = DB_query($SQL, $db, $ErrMsg);
            // $myrow= DB_fetch_array($result);
            // $trasnoPoliza = $myrow['transno'];
            
            // Movimientos contrarios del log presupuestal
            $typeNuevo = 556;
            $transnoNuevo = GetNextTransNo($typeNuevo, $db);
            $PeriodNo = GetPeriod(date('d/m/Y'), $db);
            // fnInsertPresupuestoLogMovContrarios($db, 555, $trasnoPoliza, $typeNuevo, $transnoNuevo);
            
            // Folio de la poliza por unidad ejecutora
            $folioPolizaUe = 0; // fnObtenerFolioUeGeneral($db, $unidadnegocio, $_SESSION['SuppTrans']->unidadEjecutoraGeneral);

            // Obtener orderno original (Inicial)
            $ordernoOriginal = 0;
            $SQL = "SELECT MIN(purchorders.orderno) as ordernoOriginal 
            FROM purchorders 
            WHERE purchorders.requisitionno = '".$datosClave ['requisitionno']."'";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            $myrow = DB_fetch_array($TransResult);
            $ordernoOriginal = $myrow['ordernoOriginal'];

            // Obtener información de la suficiencia para movimientos contrarios
            $typeSuf = 0;
            $transnoSuf = 0;
            $periodSuf = 0;
            $SQL = "SELECT distinct tb_suficiencias.nu_type, tb_suficiencias.nu_transno, chartdetailsbudgetlog.period
            FROM tb_suficiencias 
            JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.type = tb_suficiencias.nu_type AND chartdetailsbudgetlog.transno = tb_suficiencias.nu_transno
            WHERE tb_suficiencias.nu_estatus <> '0' AND tb_suficiencias.sn_orderno = '".$ordernoOriginal."'";
            $ErrMsg = "No se obtuvieron los registros del Orden ".$ordernoOriginal;
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            $myrow = DB_fetch_array($TransResult);
            $typeSuf = $myrow['nu_type'];
            $transnoSuf = $myrow['nu_transno'];
            $periodSuf = $myrow['period'];

            $SQL = "
            SELECT 
            (pd.quantityord * pd.unitprice) AS total, 
            p.requisitionno as noreq, 
            p.tagref as tagref,  
            pd.clavepresupuestal as cvepresupuestal, 
            cdbt.partida_esp as partida, 
            p.nu_ue,
            p.comments,
            p.realorderno
            FROM purchorders p 
            JOIN purchorderdetails pd on (p.orderno = pd.orderno) 
            LEFT JOIN chartdetailsbudgetbytag cdbt on (p.tagref = cdbt.tagref and pd.clavepresupuestal = cdbt.accountcode ) 
            WHERE p.orderno = '".$datosClave ['orderno']."' AND pd.status = 2 AND pd.quantityord <> 0
            ";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            while ($myrow = DB_fetch_array($TransResult)) {
                $tagref = $myrow['tagref'];
                $clave = $myrow['cvepresupuestal'];
                $total= $myrow['total'];//$myrow['cantidad']*$myrow['precio'];
                $partida_esp = $myrow['partida'];
                $comments = $myrow['comments'];
                $realorderno = $myrow['realorderno'];
                $ue = fnObtenerUnidadEjecutoraClave($db, $clave);
                $fechaMov = date('Y-m-d');

                if ($folioPolizaUe == 0) {
                    // Obtener folio
                    $folioPolizaUe = fnObtenerFolioUeGeneral($db, $tagref, $ue, $typeNuevo);
                }
                
                // Movimiento contable
                $resultado= GeneraMovimientoContablePresupuesto(
                    $typeNuevo,
                    "COMPROMETIDO",
                    "POREJERCER",
                    $transnoNuevo,
                    $PeriodNo,
                    $total,
                    $tagref,
                    $fechaMov,
                    $clave,
                    $datosClave ['orderno'],
                    $db,
                    false,
                    '',
                    '',
                    'Reversa de Autorización Orden de Compra '.$realorderno.'. '.$comments,
                    $ue,
                    1,
                    0,
                    $folioPolizaUe
                );

                // Actualizar enlace poliza
                $sql = "UPDATE gltrans SET purchno = '".$datosClave ['orderno']."' 
                WHERE type = '".$typeNuevo."' AND typeno = '".$transnoNuevo."'";
                $result =DB_query($sql, $db);

                // Log Presupuesto
                $descriptionLog = "Reversa de Autorización Orden de Compra ".$realorderno.". Requisición ".$datosClave ['requisitionno'];
                // Se resta a la suficiencia el total
                $agregoLog = fnInsertPresupuestoLog($db, $typeSuf, $transnoSuf, $tagref, $clave, $periodSuf, $total * -1, 263, "", $descriptionLog, 0, '4', 2302, $ue); // Suficiencia Automatica
                $agregoLog = fnInsertPresupuestoLog($db, $typeNuevo, $transnoNuevo, $tagref, $clave, $periodSuf, $total * -1, 258, "", $descriptionLog, 1, '', 0, $ue); // Abono
                $agregoLog = fnInsertPresupuestoLog($db, $typeNuevo, $transnoNuevo, $tagref, $clave, $periodSuf, $total, 259, "", $descriptionLog, 1, '', 0, $ue); // Cargo

                $status = 'Autorizado';
                $sql = "UPDATE purchorders SET status = '".$status."', fecha_modificacion = current_timestamp() WHERE orderno = '".$datosClave ['orderno']."'";
                $result = DB_query($sql, $db, $ErrMsg);

                // Mensaje proceso correcto
                $mensajeErrores = "<p>Se realizó la reversa correctamente de la requisición ".$datosClave ['requisitionno']."</p>";
            }
        } else if ($tipoReversa == 2) {
            // Reversar recepecion de la orden de compra
            // Obtener Recepciones
            $SQL = "SELECT 
            grns.grnbatch,
            grns.grnno,
            po.orderno,
            grns.itemcode,
            grns.itemdescription,
            grns.deliverydate,
            qtyrecd,
            quantityinv,
            qtyrecd-quantityinv AS qtytoreverse,
            po.tagref,
            grns.grnbatch,
            pd.factorconversion,
            po.realorderno
            FROM grns 
            INNER JOIN purchorderdetails pd ON pd.podetailitem=grns.podetailitem
            INNER JOIN purchorders po ON po.orderno = pd.orderno
            LEFT JOIN locstock  ON po.intostocklocation = locstock.loccode and locstock.stockid=pd.itemcode and locstock.quantity > 0
            WHERE grns.SupplierId = '".$datosClave ['idproveedor']."'
            AND po.orderno = '".$datosClave ['orderno']."'
            AND qtyrecd>0";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            while ($myrow = DB_fetch_array($TransResult)) {
                // Validaciones para realizar operaciones de reversa
                $SQL = "SELECT grns.podetailitem,
                grns.grnbatch,
                grns.itemcode,
                grns.itemdescription,
                grns.deliverydate,
                DATE_FORMAT(grns.deliverydate,'%d/%m/%Y') as deliverydatedos,
                purchorderdetails.factorconversion,
                purchorderdetails.glcode,
                purchorders.orderno,
                purchorderdetails.podetailitem as orderdetail,
                grns.qtyrecd,
                grns.quantityinv,
                purchorderdetails.stdcostunit/purchorderdetails.factorconversion as stdcostunit,
                purchorders.intostocklocation,
                purchorders.orderno,
                tags.legalid,
                purchorderdetails.clavepresupuestal,
                purchorders.realorderno,
                locations.locationname,
                stockmaster.mbflag
                FROM grns, stockmaster, purchorderdetails, purchorders 
                JOIN tags ON purchorders.tagref = tags.tagref
                JOIN locations ON locations.loccode = purchorders.intostocklocation
                WHERE 
                grns.podetailitem = purchorderdetails.podetailitem
                AND grns.itemcode = stockmaster.stockid
                AND purchorders.orderno = purchorderdetails.orderno
                AND grnno = '".$myrow['grnno']."'";
                $Result = DB_query($SQL, $db, $ErrMsg);

                $GRN = DB_fetch_array($Result);
                $QtyToReverse = ($GRN['qtyrecd'] - $GRN['quantityinv'])*$GRN['factorconversion'];
                $factorconversion = $GRN['factorconversion'];
                $realorderno = $GRN['realorderno'];
                $clavepresupuestal = $GRN['clavepresupuestal'];

                if ($QtyToReverse == 0) {
                    // No existen recepciones
                    $result = false;
                    $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No existen recepciones de la requisición '.$datosClave ['requisitionno'].'</p>';
                }

                if ($GRN['mbflag'] == 'B') {
                    // Si es un ien, validar existencia para reversar
                    $SQL= "SELECT quantity - ontransit as quantity FROM locstock
                    WHERE stockid='" . $GRN['itemcode'] . "' AND loccode= '" . $GRN['intostocklocation'] . "'";
                    $Result = DB_query($SQL, $db, $ErrMsg);
                    if (DB_num_rows($Result) > 0) {
                        // Si tiene registros
                        $registro = DB_fetch_array($Result);
                        $cantidad = $registro["quantity"];
                        
                        if ($QtyToReverse > $cantidad) {
                            $result = false;
                            $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El bien '.$GRN['itemcode'].' - '.$GRN['itemdescription'].' cuenta con '.$cantidad.' en el almacén '.$GRN['intostocklocation'].' - '.$GRN['locationname'].' y se quiere tener '.$QtyToReverse.'</p>';
                        }
                    }
                }
            }

            if ($result) {
                // Reversar recepecion de la orden de compra si no hay errores
                // Movimientos contrarios del log presupuestal
                $typeNuevo = 800;
                $transnoNuevo = GetNextTransNo($typeNuevo, $db);
                $PeriodNo = GetPeriod(date('d/m/Y'), $db);

                $SQL = "SELECT 
                grns.grnbatch,
                grns.grnno,
                po.orderno,
                grns.itemcode,
                grns.itemdescription,
                grns.deliverydate,
                qtyrecd,
                quantityinv,
                qtyrecd-quantityinv AS qtytoreverse,
                po.tagref,
                grns.grnbatch,
                pd.factorconversion,
                po.realorderno,
                po.nu_ue
                FROM grns 
                INNER JOIN purchorderdetails pd ON pd.podetailitem=grns.podetailitem
                INNER JOIN purchorders po ON po.orderno = pd.orderno
                LEFT JOIN locstock  ON po.intostocklocation = locstock.loccode and locstock.stockid=pd.itemcode and locstock.quantity > 0
                WHERE grns.SupplierId = '".$datosClave ['idproveedor']."'
                AND po.orderno = '".$datosClave ['orderno']."'
                AND qtyrecd>0";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                $grnbatch = 0;
                $tagref = 0;
                $nu_ue = 0;
                while ($myrow = DB_fetch_array($TransResult)) {
                    // Reversar recepecion de la orden de compra
                    $grnbatch = $myrow['grnbatch'];
                    $tagref = $myrow['tagref'];
                    $nu_ue = $myrow['nu_ue'];

                    $SQL = "SELECT 
                    grns.podetailitem,
                    grns.grnbatch,
                    grns.itemcode,
                    grns.itemdescription,
                    grns.deliverydate,
                    DATE_FORMAT(grns.deliverydate,'%d/%m/%Y') as deliverydatedos,
                    purchorderdetails.factorconversion,
                    purchorderdetails.glcode,
                    purchorders.orderno,
                    purchorderdetails.podetailitem as orderdetail,
                    grns.qtyrecd,
                    grns.quantityinv,
                    purchorderdetails.stdcostunit/purchorderdetails.factorconversion as stdcostunit,
                    purchorders.intostocklocation,
                    purchorders.orderno,
                    tags.legalid,
                    purchorderdetails.clavepresupuestal,
                    purchorders.realorderno,
                    locations.locationname,
                    stockmaster.mbflag,
                    purchorders.tagref,
                    purchorders.nu_ue
                    FROM grns, stockmaster, purchorderdetails, purchorders 
                    JOIN tags ON purchorders.tagref = tags.tagref
                    JOIN locations ON locations.loccode = purchorders.intostocklocation
                    WHERE 
                    grns.podetailitem = purchorderdetails.podetailitem
                    AND grns.itemcode = stockmaster.stockid
                    AND purchorders.orderno = purchorderdetails.orderno
                    AND grnno = '".$myrow['grnno']."'";
                    $Result = DB_query($SQL, $db, $ErrMsg);

                    $GRN = DB_fetch_array($Result);
                    $QtyToReverse = ($GRN['qtyrecd'] - $GRN['quantityinv'])*$GRN['factorconversion'];
                    $factorconversion = $GRN['factorconversion'];
                    $realorderno = $GRN['realorderno'];
                    $clavepresupuestal = $GRN['clavepresupuestal'];

                    $PeriodNo = GetPeriodXLegal(ConvertSQLDate($GRN['deliverydate']), $GRN['legalid'], $db);

                    $SQL="SELECT quantity FROM locstock WHERE stockid='" . $GRN['itemcode'] . "' AND loccode= '" . $GRN['intostocklocation'] . "'";
                    $Result = DB_query($SQL, $db, $ErrMsg);
                    $QtyOnHandPrior = 0;
                    if (DB_num_rows($Result) == 1) {
                        $LocQtyRow = DB_fetch_row($Result);
                        $QtyOnHandPrior = $LocQtyRow[0];
                    }

                    $SQL = "UPDATE locstock
                    SET quantity = quantity - " . $QtyToReverse . "
                    WHERE stockid = '" . $GRN['itemcode'] . "'
                    AND loccode = '" . $GRN['intostocklocation'] . "'";
                    $Result = DB_query($SQL, $db, $ErrMsg);

                    $SQL = "INSERT INTO stockmoves (
                    stockid,
                    type,
                    transno,
                    loccode,
                    trandate,
                    prd,
                    reference,
                    qty,
                    standardcost,
                    newqoh,
                    tagref,
                    ln_ue
                    )
                    VALUES (
                    '" . $GRN['itemcode'] . "',
                    '" . $typeNuevo . "',
                    " . $transnoNuevo . ",
                    '" . $GRN['intostocklocation'] . "',
                    '" . $GRN['deliverydate'] . "',
                    " . $PeriodNo . ", 
                    '" . _('Reversa del proveedor:') . ' - ' . $datosClave ['idproveedor'] . ' - OC:' . $GRN['orderno'] .' - REC:'. $myrow['grnno'] . "',
                    " . -$QtyToReverse . ",
                    " . $GRN['stdcostunit'] . ",
                    " . ($QtyOnHandPrior - $QtyToReverse) . ",
                    '" . $GRN['tagref'] . "',
                    '" . $GRN['nu_ue'] . "'
                    )";
                    $Result = DB_query($SQL, $db, $ErrMsg);

                    /*******************************************************************************************************************/
                    /***** cambio de actualizacion para considerar no halla negativos en ordenes de compra******************************/
                    /******************************************************************************************************************/
                    $SQL = "UPDATE purchorders SET status='Authorised' 
                    WHERE orderno = " . $GRN['orderno'];
                    $Result=DB_query($SQL, $db, $ErrMsg);

                    $SQL = "UPDATE purchorderdetails SET quantityrecd = quantityrecd - " . ($QtyToReverse/$factorconversion) . ", completed = 0 
                    WHERE purchorderdetails.podetailitem = " . $GRN['podetailitem'];
                    $Result=DB_query($SQL, $db, $ErrMsg);

                    /*******************************************************************************************************************/
                    /***** Cancelacion de recepcion de productos para requisiciones de compra *****************************************/
                    /******************************************************************************************************************/
                    $SQL = "UPDATE grns SET qtyrecd = qtyrecd - " . ($QtyToReverse/$factorconversion) . " WHERE grns.grnno = " . $myrow['grnno'];
                    $Result=DB_query($SQL, $db, $ErrMsg);
                }

                // Folio de la poliza por unidad ejecutora
                $folioPolizaUe = fnObtenerFolioUeGeneral($db, $tagref, $nu_ue, $typeNuevo);

                fnInsertPolizaMovContrarios($db, 25, $grnbatch, $typeNuevo, $transnoNuevo, $folioPolizaUe);

                // Mensaje proceso correcto
                $mensajeErrores = "<p>Se realizó la reversa correctamente de la requisición ".$datosClave ['requisitionno'].". Folio de recepción ".$grnbatch."</p>";
            }
        }

        $info[] = array(
            'orderno' => $datosClave ['orderno'],
            'actualizar' => $actualizar
        );
    }
    
    $contenido = array('datos' => $info, 'mensajeErrores' => $mensajeErrores, 'tipoReversa' => $tipoReversa);
}

if ($option == 'validarReversaOrdenCompra') {
    $dataJsonNoCapturaSeleccionados = $_POST['dataJsonNoCapturaSeleccionados'];
    $statusid = $_POST['statusid'];

    $info = array();
    $mensajeErrores = "";
    $result = true;
    foreach ($dataJsonNoCapturaSeleccionados as $datosClave) {
        $actualizar = 1;
        // Reversar orden de compra autorizada
        $tipoReversa = 1;

        // Obtener estatus del registro
        $statusActual = '';
        $SQL = "SELECT purchorders.status FROM purchorders WHERE purchorders.orderno = '".$datosClave ['orderno']."'";
        $SQL = "SELECT 
        purchorders.status,
        purchorderdetails.completed,
        SUM(purchorderdetails.quantityord) AS quantityord,
        SUM(purchorderdetails.qtyinvoiced) AS qtyinvoiced
        FROM purchorders
        INNER JOIN purchorderdetails ON purchorders.orderno = purchorderdetails.orderno AND purchorderdetails.status NOT IN(0,3)
        WHERE purchorders.orderno = '".$datosClave ['orderno']."'
        GROUP BY purchorders.status, purchorderdetails.completed";
        $result = DB_query($SQL, $db, $ErrMsg);
        $myrow= DB_fetch_array($result);
        $statusActual = $myrow['status'];
        $completed = $myrow['completed'];
        $quantityord = $myrow['quantityord'];
        $qtyinvoiced = $myrow['qtyinvoiced'];

        if ($statusActual == 'Authorised') {
            // Si es una compra autorizada
            $tipoReversa = 1;
            $mensajeErrores = '<p>Se va a reversar la autorización de la orden de compra '.$datosClave ['ordencompra'].'</p>';
        } else {
            // Reversar recepcion de productos
            if ($statusActual == 'Pending') {
                // Validaciones
                $tipoReversa = 2;
                $mensajeErrores = '<p>Se va a reversar la recepción de la orden de compra '.$datosClave ['ordencompra'].'</p>';
            }

            if ($statusActual == 'Completed' && $completed == '1' && ($quantityord != $qtyinvoiced)) {
                // Alta de Factura
                $tipoReversa = 2;
                $mensajeErrores = '<p>Se va a reversar la recepción de la orden de compra '.$datosClave ['ordencompra'].'</p>';
                if ($qtyinvoiced != 0) {
                    // Ya existe facturas, no se puede reversar
                    $result = false;
                    $tipoReversa = 0;
                    $mensajeErrores = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se puede reversar la recepción de la orden de compra '.$datosClave ['ordencompra'].'. Existe factura de pago</p>';
                }
            } else if ($qtyinvoiced != 0) {
                // Ya existe facturas, no se puede reversar
                $result = false;
                $tipoReversa = 0;
                $mensajeErrores = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se puede reversar la recepción de la orden de compra '.$datosClave ['ordencompra'].'. Existe factura de pago</p>';
            }
        }

        $info[] = array(
            'orderno' => $datosClave ['orderno'],
            'actualizar' => $actualizar
        );
    }
    
    $contenido = array('datos' => $info, 'mensajeErrores' => $mensajeErrores, 'tipoReversa' => $tipoReversa);
}

if ($option == 'traeOrdenesCompras') {
    $info = array();
    $condicion= " 1=1 ";
    $fechaini= $_POST["fechainicio"];// date("Y-m-d", strtotime($_POST["fechainicio"]));
    $fechafin= $_POST["fechafin"];//date("Y-m-d", strtotime($_POST["fechafin"]));
    $dependencia= $_POST["dependencia"];
    $unidadres= $_POST["unidadres"];
    $idrequisicion= $_POST["requisicion"];
    $idproveedor= $_POST["idproveedor"];
    $nomproveedor= $_POST["nomproveedor"];
    $estatus= $_POST["estatus"];
    $funcion= $_POST["funcion"];
    $noOrdenCompra = $_POST['noOrdenCompra'];
    $ue = $_POST['ue'];

    $seleccionar= "";

    // separar la seleccion multiple de la dependencia
    $datosDependencia = "";
    foreach ($dependencia as $key) {
        if (empty($datosDependencia)) {
            $datosDependencia .= "'".$key."'";
        } else {
            $datosDependencia .= ", '".$key."'";
        }
    }

    // separar la seleccion multiple de las unidades responsables
    $datosUR = "";
    foreach ($unidadres as $key) {
        if (empty($datosUR)) {
            $datosUR .= "'".$key."'";
        } else {
            $datosUR .= ", '".$key."'";
        }
    }

    // separar la seleccion multiple de las unidades ejecutoras
    $datosUE = "";
    foreach ($ue as $key) {
        if (empty($datosUE)) {
            $datosUE .= "'".$key."'";
        } else {
            $datosUE .= ", '".$key."'";
        }
    }

    // separar la seleccion multiple de estatus
    if (is_array($estatus)) {
        $estatus= implode(",", $estatus);
        $estatus.= ",'Autorizado'";
    } else if (!empty($estatus)) {
        $estatus= "'".$estatus."'";
    }

    if (!empty($fechaini) && !empty($fechafin)) {
        $fechaini = date_create($fechaini);
        $fechaini = date_format($fechaini, 'Y-m-d');

        $fechafin = date_create($fechafin);
        $fechafin = date_format($fechafin, 'Y-m-d');

        $condicion .= " AND purchorders.orddate between '".$fechaini." 00:00:00' AND '".$fechafin." 23:59:59' ";
    } elseif (!empty($fechaini)) {
        $fechaini = date_create($fechaini);
        $fechaini = date_format($fechaini, 'Y-m-d');

        $condicion .= " AND purchorders.orddate >= '".$fechaini." 00:00:00' ";
    } elseif (!empty($fechafin)) {
        $fechafin = date_create($fechafin);
        $fechafin = date_format($fechafin, 'Y-m-d');

        $condicion .= " AND purchorders.orddate <= '".$fechafin." 23:59:59' ";
    }

    $condicion .= " AND purchorders.requisitionno!= 0 AND purchorders.requisitionno!= '' ";

    if (!empty($datosDependencia)) {
        $condicion .= " AND tags.legalid IN (".$datosDependencia.") ";
    }

    if (!empty($datosUR)) {
        $condicion.= " AND tags.tagref IN (".$datosUR.") ";
    } else {
        $condicion.= " AND tags.tagref IN (SELECT tagref FROM sec_unegsxuser WHERE userid= '".$_SESSION["UserID"]."') ";
    }

    if (!empty($datosUE)) {
        $condicion .= " AND purchorders.nu_ue IN (".$datosUE.") ";
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
        // $condicion .= " AND purchorders.requisitionno like '%".$idrequisicion."%' ";
        $condicion .= " AND purchorders.requisitionno = '".$idrequisicion."' ";
    }

    if (!empty(trim($noOrdenCompra))) {
        // $condicion .= " AND purchorders.realorderno like '%".$noOrdenCompra."%' ";
        $condicion .= " AND purchorders.realorderno = '".$noOrdenCompra."' ";
    }

    // Solo Ordenes de compra con recepcion pendientes
    //$condicion .= " AND purchorders.status = 'Authorised' AND purchorderdetails.completed = '0' ";
    // Recepciones pendientes y facturas pendientes
    $condicion .= " AND (( (purchorders.status = 'Authorised' || purchorders.status = 'Pending') AND purchorderdetails.completed = '0') or (purchorders.status = 'Completed' AND purchorderdetails.completed = '1')) ";
    $condicionHaving = " HAVING quantityord <> qtyinvoiced ";

    // Consulta para extraer los datos para el panel
    $consulta= "SELECT locationname,
    purchorders.orderno,
    IF(purchorders.supplierorderno IS NULL, 'NA', purchorders.supplierorderno) AS supplierorderno,
    suppliers.suppname,
    suppliers.supplierid,
    DATE_FORMAT(purchorders.orddate,'%d/%m/%Y') as orddate,
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
    tags.tagdescription,
    purchorders.nu_ue,
    tb_cat_unidades_ejecutoras.desc_ue
    FROM purchorders
    INNER JOIN purchorderdetails ON purchorders.orderno = purchorderdetails.orderno AND purchorderdetails.status NOT IN(0,3)
    INNER JOIN tb_botones_status ON purchorders.status= tb_botones_status.statusname AND (tb_botones_status.sn_funcion_id= '1371' OR (tb_botones_status.sn_funcion_id=2265 AND tb_botones_status.statusname='Autorizado'))
    INNER JOIN tags on purchorders.tagref=tags.tagref
    INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
    INNER JOIN legalbusinessunit on tags.legalid=legalbusinessunit.legalid
    LEFT JOIN locations ON purchorders.intostocklocation = locations.loccode
    LEFT JOIN areas on areas.areacode=tags.areacode
    LEFT JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ur = purchorders.tagref AND tb_cat_unidades_ejecutoras.ue = purchorders.nu_ue
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = purchorders.tagref  AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    JOIN `tb_sec_users_ue` ON `tb_sec_users_ue`.`userid` = '".$_SESSION['UserID']."' AND purchorders.tagref  = `tb_sec_users_ue`.`tagref` AND  purchorders.nu_ue = `tb_sec_users_ue`.`ue`

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
    ORDER BY CAST(purchorders.requisitionno AS SIGNED) DESC ";
    // INNER JOIN tb_botones_status ON purchorders.status= tb_botones_status.statusname AND tb_botones_status.sn_flag_disponible=1 AND (sn_funcion_id= '".$funcion."' OR (sn_funcion_id=2265 AND tb_botones_status.statusname='Autorizado'))
    // echo "<pre>".$consulta;
    // exit();
    $ErrMsg = "No se pudo obtener la consulta de requisiciones";
    $resultado = DB_query($consulta, $db, $ErrMsg);

    while ($registro= DB_fetch_array($resultado)) {
        $seleccionar = '<input type="checkbox" id="checkbox_'.$registro ['orderno'].'" name="checkbox_'.$registro ['orderno'].'" title="Seleccionar" value="'.$registro ['orderno'].'" onchange="fnValidarProcesoCambiarEstatus()" />';

        $opcionesImp = "";
        $opciones = "";
        $urlProceso = "";

        if ($registro['sn_funcion_id'] == '1371') {
            // Impresion OC
            // &OrderNo=107&tipodocto=555&Tagref=100&legalid=1
            $enc = new Encryption;
            $url = "&OrderNo=>".$registro['orderno']."&tipodocto=>555&Tagref=>".$registro['tagref']."&legalid=>".$registro['legalid'];
            $url = $enc->encode($url);
            $liga= "URL=" . $url;

            $opcionesImp .= "<a id='idBtnImpresionOC' target='_blank' href='./PO_PDFPurchOrder.php?".$liga."'><span class='glyphicon glyphicon glyphicon-print'></span></a><br>";
        }

        if (($registro["status"] == 'Authorised' || $registro['status'] == 'Pending') && $registro["completed"] == '0') {
            // Rececpción OC
            //$registro["sn_nombre_secundario"] = "Compra Autorizada";
            // $url = "&PONumber=".$registro['orderno'];
            $enc = new Encryption;
            $url = "&PONumber=>".$registro['orderno'];
            $url = $enc->encode($url);
            $liga= "URL=" . $url;

            $opciones .= "<a id='idBtnRecepcionOC' target='_self' href='./GoodsReceived.php?".$liga."'><span class=''></span>Recibir</a>";
            $urlProceso = "GoodsReceived.php?".$liga;
        }

        if ($registro["status"] == 'Completed' && $registro["completed"] == '1' && ($registro["quantityord"] != $registro["qtyinvoiced"])) {
            // Alta de Factura
            $registro["sn_nombre_secundario"] = "Compra Recibida";
            if ($registro['qtyinvoiced'] != 0) {
                $registro["sn_nombre_secundario"] = "Factura Parcial";
            }
            
            // $url = "&SupplierID=".$registro['supplierid']."&unidadnegocio=".$registro['tagref'];//."&GoodRecived=YES";
            $enc = new Encryption;
            $url = "&SupplierID=>".$registro['supplierid']."&unidadnegocio=>".$registro['tagref'];
            $url = $enc->encode($url);
            $liga1= "URL=" . $url;

            $opciones .= "<a id='idBtnAltaFacturaOC' target='_self' href='./SupplierInvoice.php?".$liga."'><span class=''></span> Facturar</a>";
            $urlProceso = "SupplierInvoice.php?".$liga1;

            $enc = new Encryption;
            $url = "&moneda=>".$registro['currcode']."&unidadnegocio=>".$registro['tagref']."&legalid=>".$registro['legalid']."&SupplierID=>".$registro['supplierid']."&orderno=>".$registro["orderno"];
            $url = $enc->encode($url);
            $liga2= "URL=" . $url;
            $urlProceso = "SuppInvGRNs.php?".$liga2;
        }

        // Obtener Fecha de recepción
        $fechaRecepcion = "";
        $SQL = "SELECT distinct grns.deliverydate
        FROM purchorderdetails
        JOIN grns ON grns.podetailitem = purchorderdetails.podetailitem
        WHERE purchorderdetails.orderno IN (".$registro["orderno"].")
        ORDER BY grns.deliverydate";
        $ErrMsg = "No se pudo obtener fechas de recepción";
        $resultado2 = DB_query($SQL, $db, $ErrMsg);
        while ($registro2= DB_fetch_array($resultado2)) {
            if ($fechaRecepcion == "") {
                $fechaRecepcion = $registro2['deliverydate'];
            } else {
                $fechaRecepcion .= ', '.$registro2['deliverydate'];
            }
        }

        $info[] = array(
            'id1' =>false,
            "idrequisicion"=> $registro["requisitionno"],
            "numerorequisicion"=> $registro["requisitionno"],
            "idproveedor" => $registro["supplierid"],
            "nombreproveedor" => ($registro["suppname"]),
            "estatus" => $registro["sn_nombre_secundario"],
            "totalrequisicion" => $registro["ordervalue"],
            "totalrequisicion2" => $registro["ordervalue"],
            "seleccionar" => $seleccionar,
            "fechaCaptura" => $registro["orddate"],
            "fecharequerida" => $registro["fecharequerida"],
            "observaciones" => $registro["comments"],
            "orderno" => $registro["orderno"],
            "ordencompra" => $registro["realorderno"],
            "tagdescription" => $registro["tagref"],
            "uedescription" => $registro["nu_ue"],
            "fechaRecepcion" => $fechaRecepcion,
            "impresion" => $opcionesImp,
            "operacion" => $opciones,
            "urlProceso" => $urlProceso
        );
    }

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if ($option == 'validarEstatusOrdenCompra') {
    $dataJsonNoCapturaSeleccionados = $_POST['dataJsonNoCapturaSeleccionados'];
    $statusid = $_POST['statusid'];

    $info = array();
    $mensajeErrores = "";
    $result = true;
    foreach ($dataJsonNoCapturaSeleccionados as $datosClave) {
        $actualizar = 1;

        if ($statusid == '1') {
            // Recibir
            if (!fnValidarRecepcionOC($db, $datosClave ['orderno'])) {
                $result = false;
                $actualizar = 0;
                $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Requisición '.$datosClave ['requisitionno'].' ya fue Recibida</p>';
            }
        } else {
            // Facturar
            if (!fnValidarFacturaOC($db, $datosClave ['orderno'])) {
                if (!fnValidarRecepcionOC($db, $datosClave ['orderno'])) {
                    $result = false;
                    $actualizar = 0;
                    $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Requisición '.$datosClave ['requisitionno'].' ya fue Facturada</p>';
                } else {
                    $result = false;
                    $actualizar = 0;
                    $mensajeErrores .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Requisición '.$datosClave ['requisitionno'].' debe ser Recibida</p>';
                }
            }
        }

        $info[] = array(
            'orderno' => $datosClave ['orderno'],
            'actualizar' => $actualizar
        );
    }
    
    $contenido = array('datos' => $info, 'mensajeErrores' => $mensajeErrores);
}

if ($option == 'obtenerBotones') {
    $info = array();
    $SQL = "SELECT 
            distinct tb_botones_status.functionid,
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
            (tb_botones_status.sn_funcion_id = '".$funcion."')
            AND (tb_botones_status.sn_flag_disponible = 1)
            AND (tb_botones_status.sn_panel_adecuacion_presupuestal = 1)
            AND
            (tb_botones_status.functionid = sec_funxprofile.functionid 
            OR 
            tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid AND sec_funxuser.userid = '".$_SESSION['UserID']."' AND sec_funxuser.permiso = 1)
            ) 
            ORDER BY tb_botones_status.statusid ASC
            ";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
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

$dataObj = array(
    'sql' => '',
    'contenido' => $contenido,
    'result' => $result,
    'RootPath' => $RootPath,
    'ErrMsg' => $ErrMsg,
    'Mensaje' => $Mensaje);

echo json_encode($dataObj);
