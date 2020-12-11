<?php
/**
 * Utilería Catálogos
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 08/08/2017
 * Fecha Modificación: 08/08/2017
 * Utilería Cargar Catálogos
 */

$PageSecurity=15;
$funcion=408;
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');
$title = traeNombreFuncion($funcion, $db);
include('includes/header.inc');

ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

//echo GetPeriod('17/08/2017', $db);

ini_set('memory_limit', '5096M');
set_time_limit(1500);

set_include_path(implode(PATH_SEPARATOR, array(realpath('lib/PHPExcel-1.8/Classes/PHPExcel/'), get_include_path(),)));
require_once("lib/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php");

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$txtFechaDesde = date('d-m-Y');
if (isset($_POST['txtFechaDesde'])) {
    $txtFechaDesde = $_POST['txtFechaDesde'];
}

$txtFechaHasta = date('d-m-Y');
if (isset($_POST['txtFechaHasta'])) {
    $txtFechaHasta = $_POST['txtFechaHasta'];
}

$txtFechaDesdeFiltro = date_format(date_create($txtFechaDesde), 'Y-m-d')." 00:00:00";
$txtFechaHastaFiltro = date_format(date_create($txtFechaHasta), 'Y-m-d')." 23:59:59";

$checkVerConsulta = 0;
if (isset($_POST['checkVerConsulta'])) {
    $checkVerConsulta = 1;
}

$checkAgruparFecha = 0;
if (isset($_POST['checkAgruparFecha'])) {
    $checkAgruparFecha = 1;
}

$tablaEncabezado = "";
$tablaBody = "";

if (isset($_POST['cmbProceso']) && $_POST['cmbProceso'] == 'cbmVerMovs') {
    $sqlGroup = "";
    if (isset($_POST['checkAgruparFecha'])) {
        $sqlGroup = "GROUP BY fecha";
    }
$SQL = "
-- Contabilidad
SELECT
SUM(gltrans.amount) as total,
round(SUM(gltrans.amount),2) as total2,
DATE_FORMAT(gltrans.trandate, '%Y-%m-%d') as fecha,
'6 Contabilidad' as proceso
FROM gltrans
WHERE gltrans.trandate between '".$txtFechaDesdeFiltro."' AND '".$txtFechaHastaFiltro."'
AND gltrans.account like '1.1%'
".$sqlGroup."
UNION
-- Presupuesto
SELECT
sum(chartdetailsbudgetlog.qty) as total,
round(sum(chartdetailsbudgetlog.qty),2) as total2,
DATE_FORMAT(chartdetailsbudgetlog.datemov, '%Y-%m-%d') as fecha,
'7 Presupuesto' as proceso
FROM chartdetailsbudgetlog
WHERE chartdetailsbudgetlog.datemov between '".$txtFechaDesdeFiltro."' AND '".$txtFechaHastaFiltro."'
AND chartdetailsbudgetlog.nu_tipo_movimiento = '311'
".$sqlGroup."
UNION
-- Recibos
SELECT
sum(ovamount) as total,
round(sum(ovamount),2) as total2,
DATE_FORMAT(debtortrans.trandate, '%Y-%m-%d') as fecha,
'4 Recibos' as proceso
FROM debtortrans
WHERE
debtortrans.trandate between '".$txtFechaDesdeFiltro."' AND '".$txtFechaHastaFiltro."'
AND type = 12
".$sqlGroup."
UNION
-- Recibos Detalle
SELECT
sum(tb_debtortrans_forma_pago.nu_cantidad) as total,
round(sum(tb_debtortrans_forma_pago.nu_cantidad),2) as total2,
DATE_FORMAT(debtortrans.trandate, '%Y-%m-%d') as fecha,
'5 Recibos Detalles' as proceso
FROM debtortrans
JOIN tb_debtortrans_forma_pago on tb_debtortrans_forma_pago.nu_type = debtortrans.type AND tb_debtortrans_forma_pago.nu_transno = debtortrans.transno
WHERE
debtortrans.trandate between '".$txtFechaDesdeFiltro."' AND '".$txtFechaHastaFiltro."'
AND type = 12
".$sqlGroup."
UNION
-- Pases de cobro
SELECT
SUM((salesorderdetails.unitprice * salesorderdetails.quantity) - ((salesorderdetails.unitprice * salesorderdetails.quantity) * salesorderdetails.discountpercent)) as total,
ROUND(SUM((salesorderdetails.unitprice * salesorderdetails.quantity) - ((salesorderdetails.unitprice * salesorderdetails.quantity) * salesorderdetails.discountpercent)),2) as total2,
DATE_FORMAT(debtortrans.trandate, '%Y-%m-%d') as fecha,
'3 Pases' as proceso
FROM debtortrans
JOIN custallocns ON custallocns.transid_allocfrom = debtortrans.id
JOIN debtortrans debtortransFactura ON debtortransFactura.id = custallocns.transid_allocto
JOIN salesorders ON salesorders.orderno = debtortransFactura.order_
JOIN salesorderdetails ON salesorderdetails.orderno = salesorders.orderno
WHERE
debtortrans.trandate between '".$txtFechaDesdeFiltro."' AND '".$txtFechaHastaFiltro."'
AND debtortrans.type = 12
".$sqlGroup."
UNION
-- Pases de cobro descuento
SELECT
SUM(((salesorderdetails.unitprice * salesorderdetails.quantity) * salesorderdetails.discountpercent)) as total,
ROUND(SUM(((salesorderdetails.unitprice * salesorderdetails.quantity) * salesorderdetails.discountpercent)),2) as total2,
DATE_FORMAT(debtortrans.trandate, '%Y-%m-%d') as fecha,
'2 Pases Descuento' as proceso
FROM debtortrans
JOIN custallocns ON custallocns.transid_allocfrom = debtortrans.id
JOIN debtortrans debtortransFactura ON debtortransFactura.id = custallocns.transid_allocto
JOIN salesorders ON salesorders.orderno = debtortransFactura.order_
JOIN salesorderdetails ON salesorderdetails.orderno = salesorders.orderno
WHERE
debtortrans.trandate between '".$txtFechaDesdeFiltro."' AND '".$txtFechaHastaFiltro."'
AND debtortrans.type = 12
".$sqlGroup."
UNION
-- Pases de cobro subtotal
SELECT
SUM((salesorderdetails.unitprice * salesorderdetails.quantity)) as total,
ROUND(SUM(((salesorderdetails.unitprice * salesorderdetails.quantity))),2) as total2,
DATE_FORMAT(debtortrans.trandate, '%Y-%m-%d') as fecha,
'1 Pases Subtotal' as proceso
FROM debtortrans
JOIN custallocns ON custallocns.transid_allocfrom = debtortrans.id
JOIN debtortrans debtortransFactura ON debtortransFactura.id = custallocns.transid_allocto
JOIN salesorders ON salesorders.orderno = debtortransFactura.order_
JOIN salesorderdetails ON salesorderdetails.orderno = salesorders.orderno
WHERE
debtortrans.trandate between '".$txtFechaDesdeFiltro."' AND '".$txtFechaHastaFiltro."'
AND debtortrans.type = 12
".$sqlGroup."
ORDER BY fecha ASC, proceso ASC
;";
    
    if (isset($_POST['checkVerConsulta'])) {
        echo "<pre>".$SQL."</pre>";
    }

    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $tablaEncabezado .= '
    <tr>
        <th scope="col" style="text-align: center;">#</th>
        <th scope="col" style="text-align: center;">Fecha</th>
        <th scope="col" style="text-align: center;">Total</th>
        <th scope="col" style="text-align: center;">Total Redondeado</th>
        <th scope="col" style="text-align: center;">Proceso</th>
    </tr>';

    $numRegistros = 1;
    $fechaAnterior = "";
    while ($myrow = DB_fetch_array($TransResult)) {
        if ((!empty($fechaAnterior)) && ($fechaAnterior != $myrow['fecha'])) {
            $tablaBody .= '
            <tr class="table-active">
                <th scope="row" style="text-align: center;" colspan="4"></th>
            </tr>';
        }
        
        $tablaBody .= '
        <tr>
            <th scope="row" style="text-align: center;">'.$numRegistros.'</th>
            <td style="text-align: center;">'.$myrow['fecha'].'</td>
            <td style="text-align: center;">'.$myrow['total'].'</td>
            <td style="text-align: center;">'.number_format($myrow['total'], 2, '.', '').'</td>
            <td style="text-align: center;">'.$myrow['proceso'].'</td>
        </tr>';
        $fechaAnterior = $myrow['fecha'];

        $numRegistros ++;
    }
}

if (isset($_POST['cmbProceso']) && $_POST['cmbProceso'] == 'cbmCuadrarDias') {
    // Cuadrar documentos, factura y recibo de pago
    $SQL = "UPDATE debtortrans
    JOIN custallocns ON custallocns.transid_allocfrom = debtortrans.id
    JOIN debtortrans debtortransFactura ON debtortransFactura.id = custallocns.transid_allocto
    JOIN (
    SELECT
    SUM((salesorderdetails.unitprice * salesorderdetails.quantity) - ((salesorderdetails.unitprice * salesorderdetails.quantity) * salesorderdetails.discountpercent)) as Total,
    salesorders.orderno
    FROM salesorders
    JOIN salesorderdetails ON salesorderdetails.orderno = salesorders.orderno
    GROUP BY salesorders.orderno
    ) salesorders2 ON salesorders2.orderno = debtortransFactura.order_
    SET
    debtortrans.ovamount = (salesorders2.Total * -1),
    debtortrans.alloc = (salesorders2.Total * -1),
    debtortransFactura.ovamount = abs(salesorders2.Total),
    debtortransFactura.alloc = abs(salesorders2.Total),
    custallocns.amt = (salesorders2.Total)
    WHERE debtortrans.trandate between '".$txtFechaDesdeFiltro."' AND '".$txtFechaHastaFiltro."'
    AND debtortrans.type = 12";
    if (isset($_POST['checkVerConsulta'])) {
        echo "<pre>".$SQL."</pre>";
    }
    $ErrMsg = _ ( 'No stkcode were returned by the SQL because' );
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );

    // Cuadrar detalle del recibo de pago
    $SQL = "UPDATE debtortrans
    JOIN (
    SELECT
    count(*) as total,
    tb_debtortrans_forma_pago.nu_type,
    tb_debtortrans_forma_pago.nu_transno
    FROM tb_debtortrans_forma_pago
    GROUP BY tb_debtortrans_forma_pago.nu_type, tb_debtortrans_forma_pago.nu_transno
    HAVING total = 1
    ) tb_debtortrans_forma_pago ON tb_debtortrans_forma_pago.nu_type = debtortrans.type AND tb_debtortrans_forma_pago.nu_transno = debtortrans.transno
    JOIN tb_debtortrans_forma_pago tb_debtortrans_forma_pagoDet ON tb_debtortrans_forma_pagoDet.nu_type = debtortrans.type AND tb_debtortrans_forma_pagoDet.nu_transno = debtortrans.transno
    SET
    tb_debtortrans_forma_pagoDet.nu_cantidad = abs(debtortrans.ovamount)
    WHERE debtortrans.trandate between '".$txtFechaDesdeFiltro."' AND '".$txtFechaHastaFiltro."'
    AND debtortrans.type = 12
    ;";
    if (isset($_POST['checkVerConsulta'])) {
        echo "<pre>".$SQL."</pre>";
    }
    $ErrMsg = _ ( 'No stkcode were returned by the SQL because' );
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );

    // cuadrar contabilidad y presupuesto
$SQL = "SELECT salesorderdetails.stkcode, 
((salesorderdetails.unitprice * salesorderdetails.quantity) - ((salesorderdetails.unitprice * salesorderdetails.quantity) * salesorderdetails.discountpercent)) as total,
TRUNCATE(((salesorderdetails.unitprice * salesorderdetails.quantity) - ((salesorderdetails.unitprice * salesorderdetails.quantity) * salesorderdetails.discountpercent)),0) as total0,
TRUNCATE(((salesorderdetails.unitprice * salesorderdetails.quantity) - ((salesorderdetails.unitprice * salesorderdetails.quantity) * salesorderdetails.discountpercent)),1) as total1,
TRUNCATE(((salesorderdetails.unitprice * salesorderdetails.quantity) - ((salesorderdetails.unitprice * salesorderdetails.quantity) * salesorderdetails.discountpercent)),2) as total2,
TRUNCATE(((salesorderdetails.unitprice * salesorderdetails.quantity) - ((salesorderdetails.unitprice * salesorderdetails.quantity) * salesorderdetails.discountpercent)),3) as total3,
tb_cat_objeto_detalle.clave_presupuestal AS clavePresupuestal,
tb_cat_objeto_detalle.cuenta_abono AS cuentaAbono,
tb_cat_objeto_detalle.cuenta_cargo AS cuentaCargo,
salesorderdetails.id_administracion_contratos,
chartdetailsbudgetbytag.tagref as tagrefClave,
tb_cat_unidades_ejecutoras.ue as ueClave,
tb_administracion_contratos.id_contrato,
debtortransFactura.id as idFactura,
debtortrans.id as idRecibo,
debtortrans.type,
debtortrans.transno,
salesorderdetailsTotal.totalRegistros
FROM salesorders
JOIN salesorderdetails ON salesorderdetails.orderno = salesorders.orderno
JOIN tb_cat_objeto_detalle ON tb_cat_objeto_detalle.stockid = salesorderdetails.stkcode
LEFT JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = tb_cat_objeto_detalle.clave_presupuestal
LEFT JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ln_aux1 = chartdetailsbudgetbytag.ln_aux1
LEFT JOIN tb_administracion_contratos ON tb_administracion_contratos.id_administracion_contratos = salesorderdetails.id_administracion_contratos
JOIN debtortrans debtortransFactura ON debtortransFactura.order_ = salesorders.orderno
JOIN custallocns ON custallocns.transid_allocto = debtortransFactura.id
JOIN debtortrans ON debtortrans.id = custallocns.transid_allocfrom
JOIN (
SELECT
COUNT(*) as totalRegistros,
salesorderdetails.orderno
FROM salesorderdetails
GROUP BY salesorderdetails.orderno
) salesorderdetailsTotal ON salesorderdetailsTotal.orderno = salesorders.orderno
WHERE 1 = 1
AND debtortrans.trandate between '".$txtFechaDesdeFiltro."' AND '".$txtFechaHastaFiltro."'";
    // salesorders
    $ErrMsg = _ ( 'No stkcode were returned by the SQL because' );
    if (isset($_POST['checkVerConsulta'])) {
        echo "<pre>".$SQL."</pre>";
    }
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );

    $numRegistros = 1;
    while ($myrow = DB_fetch_array($TransResult)) {
        $totalAmount = $myrow['total'];

        /* Inicia comentar
        $folioAmpliacion = 0;
        $folioDevengado = 0;
        $folioRecaudado = 0;
        $PeriodNo = "";

        $SQL = "SELECT
        DISTINCT
        gltrans.nu_folio_ue,
        gltrans.trandate,
        gltrans.periodno,
        gltrans.userid
        FROM gltrans
        WHERE 
        gltrans.type = '".$myrow['type']."' 
        AND gltrans.typeno = '".$myrow['transno']."'
        AND gltrans.clavepresupuestal = '".$myrow['clavePresupuestal']."'
        ORDER BY nu_folio_ue ASC";
        $TransResult2 = DB_query ( $SQL, $db, $ErrMsg );
        $numRegistros = 1;
        if (DB_num_rows($TransResult2) == 3) { 
            while($myrowFolios = DB_fetch_array($TransResult2)){
                $PeriodNo = $myrowFolios['periodno'];

                if ($numRegistros == 1) {
                    $folioAmpliacion = $myrowFolios['nu_folio_ue'];
                } else if ($numRegistros == 2) {
                    $folioDevengado = $myrowFolios['nu_folio_ue'];
                } else {
                    $folioRecaudado = $myrowFolios['nu_folio_ue'];
                }
                $numRegistros ++;
            }
        } else if (DB_num_rows($TransResult2) == 2) {
            while($myrowFolios = DB_fetch_array($TransResult2)){
                if ($numRegistros == 1) {
                    $folioDevengado = $myrowFolios['nu_folio_ue'];
                } else if ($numRegistros == 2) {
                    $folioRecaudado = $myrowFolios['nu_folio_ue'];
                }
                $numRegistros ++;
            } 
        } else if (DB_num_rows($TransResult2) == 1) {
            while($myrowFolios = DB_fetch_array($TransResult2)){
                $folioDevengado = $myrowFolios['nu_folio_ue'];
                $numRegistros ++;
            }
        }

        // Movimientos Contables y presupuestales
        $BatchNo = $myrow['transno'];
        if (!empty($folioAmpliacion) && $folioAmpliacion != 0 && 1 == 2) {
            // Movimientos póliza ampliacion
            $accountAbono = 'INGRESO_EJECUTAR';
            $accountCargo = 'INGRESO_MODIFICADO';
            $res = GeneraMovimientoContablePresupuesto(12, $accountAbono, $accountCargo, $BatchNo, $PeriodNo, $difEstimado, $urPoliza, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', $referencia, $uePoliza, 1, 0, $folioPolizaUeAmpliacion);

            $agregoLog = fnInsertPresupuestoLog($db, 12, $BatchNo, $urPoliza, $claveCreada, $PeriodNo, abs($difEstimado), 312, "", $referencia, 1, '', 0, $uePoliza); // Positivo
        }

        // if (!empty($folioAmpliacion) && $folioAmpliacion != 0 && 1 == 2) {
        // }
        
        // echo "<br>*************";
        // echo "<br>transno: ".$myrow['transno'];
        // echo "<br>clavePresupuestal: ".$myrow['clavePresupuestal'];
        // echo "<br>folioAmpliacion: ".$folioAmpliacion;
        // echo "<br>folioDevengado: ".$folioDevengado;
        // echo "<br>folioRecaudado: ".$folioRecaudado;
        // exit();
        Fin comentar */

        // total0
        // total1
        // total2

        // Contabilidad
        $sqlWhere = "AND TRUNCATE(abs(gltrans.amount), 1) = '".$myrow['total1']."'";
        if ($totalAmount < 0.1) {
            $sqlWhere = "AND TRUNCATE(abs(gltrans.amount), 3) = '".$myrow['total3']."'";
        }

        if ($myrow['totalRegistros'] == 1) {
            $sqlWhere = "";
        }

        $SQL = "UPDATE gltrans
        SET gltrans.amount = (".$totalAmount." * IF(gltrans.amount < 0, -1, 1))
        WHERE gltrans.type = '".$myrow['type']."'
        AND gltrans.typeno = '".$myrow['transno']."' ".$sqlWhere;
        $TransResult2 = DB_query ( $SQL, $db, $ErrMsg );

        // Presupuesto
        $sqlWhere = "AND TRUNCATE(abs(chartdetailsbudgetlog.qty), 1) = '".$myrow['total1']."'";
        if ($totalAmount < 0.1) {
            $sqlWhere = "AND TRUNCATE(abs(chartdetailsbudgetlog.qty), 3) = '".$myrow['total3']."'";
        }

        if ($myrow['totalRegistros'] == 1) {
            $sqlWhere = "";
        }

        $SQL = "UPDATE chartdetailsbudgetlog
        SET chartdetailsbudgetlog.qty = (".$totalAmount." * IF(chartdetailsbudgetlog.qty < 0, -1, 1))
        WHERE chartdetailsbudgetlog.type = '".$myrow['type']."'
        AND chartdetailsbudgetlog.transno = '".$myrow['transno']."' ".$sqlWhere;
        $TransResult2 = DB_query ( $SQL, $db, $ErrMsg );
    }
}
?>
<form id="form_input" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    <div align="left">
        <!--Panel Busqueda-->
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingOne">
                <h4 class="panel-title row">
                    <div class="col-md-3 col-xs-3">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
                            <b>Criterios de filtrado</b>
                        </a>
                    </div>
                </h4>
            </div>
            <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">
                    <div class="col-md-4">
                        <div class="form-inline row">
                            <div class="col-md-3">
                                <span><label>Reporte: </label></span>
                            </div>
                            <div class="col-md-9">
                                <select id="cmbProceso" name="cmbProceso" class="selectGeneral">
                                    <option value="cbmVerMovs">Ver Movimientos Agrupados</option>
                                    <option value="cbmCuadrarDias">Cuadrar Día</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <component-date-label label="Desde: " id="txtFechaDesde" name="txtFechaDesde" value="<?php echo $txtFechaDesde; ?>"></component-date-label>
                    </div>
                    <div class="col-md-4">
                        <component-date-label label="Hasta: " id="txtFechaHasta" name="txtFechaHasta" value="<?php echo $txtFechaHasta; ?>"></component-date-label>
                    </div>
                    <div class="row"></div>
                    <div class="col-md-4">
                        <div class="form-inline row">
                            <div class="col-md-3">
                                <span><label>Agrupar Fecha: </label></span>
                            </div>
                            <div class="col-md-9">
                                <input type="checkbox" id="checkAgruparFecha" name="checkAgruparFecha" value="1" <?php echo $checkAgruparFecha == '1' ? 'checked' : ''; ?>>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-inline row">
                            <div class="col-md-3">
                                <span><label>Ver Consultas: </label></span>
                            </div>
                            <div class="col-md-9">
                                <input type="checkbox" id="checkVerConsulta" name="checkVerConsulta" value="1" <?php echo $checkVerConsulta == '1' ? 'checked' : ''; ?>>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                    </div>
                    <div class="row"></div>
                    <div align="center">
                        <br>
                        <component-button type="submit" id="btnProceso" name="btnProceso" class="glyphicon glyphicon-search" value="Filtrar"></component-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<div align="center">
    <table class="table table-bordered" name="tablaContenido" id="tablaContenido">
        <thead><?php echo $tablaEncabezado; ?></thead>
        <tbody><?php echo $tablaBody; ?></tbody>
    </table>
</div>
<?php
include 'includes/footer_Index.inc';