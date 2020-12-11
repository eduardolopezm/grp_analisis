<?php
/**
 * Captura del Devengado
 *
 * @category
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Modelos para las operaciones de la Captura del Devengado
 */
//
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
$funcion=2443;
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";
$añoGeneral = '2017';//date('Y');
$dataJsonMeses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

if ($option == 'cargarInfoRetenciones') {
    $datosClave = $_POST['datosClave'];
    $datosClaveAdecuacion = $_POST['datosClaveAdecuacion'];
    $txtFechaInicio = $_POST['txtFechaInicio'];
    $txtFechaFin = $_POST['txtFechaFin'];

    $txtFechaInicio = date_create($txtFechaInicio);
    $txtFechaInicio = date_format($txtFechaInicio, 'Y-m-d');

    $txtFechaFin = date_create($txtFechaFin);
    $txtFechaFin = date_format($txtFechaFin, 'Y-m-d');

    $type = 298;
    $transno = -100;

    $info = array();
    $SQL = "SELECT
    SUM(chartdetailsbudgetlog.qty) as qty,
    chartdetailsbudgetlog.period,
    chartdetailsbudgetlog.cvefrom,
    chartdetailsbudgetlog.ln_ue,
    chartdetailsbudgetlog.nu_id_compromiso,
    chartdetailsbudgetlog.nu_id_devengado,
    chartdetailsbudgetlog.nu_idret,
    'Reduccion' as tipoMovimiento
    -- , chartdetailsbudgetlog.*
    FROM tb_pagos
    JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.type = tb_pagos.nu_type AND chartdetailsbudgetlog.transno = tb_pagos.nu_transno
    WHERE 
    chartdetailsbudgetlog.nu_idret != 0
    AND chartdetailsbudgetlog.sn_disponible = 1
    AND chartdetailsbudgetlog.nu_tipo_movimiento = 260
    AND tb_pagos.dtm_fecha between '".$txtFechaInicio." 00:00:00' AND '".$txtFechaFin." 23:59:59'
    GROUP BY chartdetailsbudgetlog.cvefrom, chartdetailsbudgetlog.nu_id_devengado, chartdetailsbudgetlog.nu_idret
    HAVING qty < 0";
    $ErrMsg = "No se obtuvieron los presupuestos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $clave = $myrow['cvefrom'];
        $account = "";
        $legalid = "";
        $period = "";
        $tipoAfectacion = "";
        $tipoMovimiento = $myrow['tipoMovimiento'];
        $infoDatos = fnInfoPresupuesto($db, $clave, $period, $account, $legalid, $datosClave, $datosClaveAdecuacion, $tipoAfectacion, $type, $transno, $tipoMovimiento, '', '', '', 0, $myrow['nu_id_devengado'], $myrow['nu_id_compromiso'], $myrow['nu_idret']);

        // Validar si tiene disponible en la retención
        $tieneDisponible = 0;
        foreach ($infoDatos as $datosInfo) {
            // echo "\n accountcode: ".$datosInfo['accountcode'];
            foreach ($dataJsonMeses as $nameMes) {
                // Recorrer meses
                // echo "\n ".$nameMes.": ".$datosInfo[$nameMes.'Retenciones'];
                if ($datosInfo[$nameMes.'Retenciones'] != number_format(0, $_SESSION['DecimalPlaces'], '.', '')) {
                    // Tiene disponible
                    $tieneDisponible = 1;
                }
            }
        }
        // print_r($infoDatos);

        if ($tieneDisponible == 1) {
            // Si tiene disponible agregarlo
            $info[] = $infoDatos;
        }
    }

    $reponse['datos'] = $info;

    if (count($info) == 0) {
        // No tiene registros
        $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se encuentró información para realizar el proceso</p>';
    }

    $contenido = $reponse;//array('datos' => $info);
    $result = true;
}

if ($option == 'existeCompromiso') {
    $idcompromiso = $_POST['idcompromiso'];
    $selectTipo = $_POST['selectTipo'];

    $info = array();
    $SQL = "SELECT 
    tb_compromiso.nu_type,
    tb_compromiso.nu_transno,
    tb_compromiso.nu_estatus,
    tb_botones_status.statusname
    FROM tb_compromiso 
    LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_compromiso.nu_estatus AND tb_botones_status.sn_funcion_id = tb_compromiso.sn_funcion_id
    WHERE tb_compromiso.nu_id_compromiso = '".$idcompromiso."'
    AND tb_compromiso.nu_tipo = 'O'";
    // AND tb_compromiso.nu_estatus = '4'
    $ErrMsg = "No se obtuvo información del compromiso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $myrow = DB_fetch_array($TransResult);
        if ($myrow ['nu_estatus'] == '4') {
            // Compromiso autorizado
            if (!empty($selectTipo)) {
                // Si tiene tipo de operación a realizar
                $sqlWhere = "";
                if ($selectTipo == '295') {
                    // Pago de adquisiciones
                    $sqlWhere = " AND tb_cat_partidaspresupuestales_partidaespecifica.ccap NOT IN (2, 3, 5) ";
                } else if ($selectTipo == '296') {
                    // Pago de subsidios
                    $sqlWhere = " AND tb_cat_partidaspresupuestales_partidaespecifica.ccap NOT IN (4) ";
                }

                $SQL = "SELECT
                distinct
                chartdetailsbudgetlog.cvefrom,
                chartdetailsbudgetbytag.partida_esp,
                tb_cat_partidaspresupuestales_partidaespecifica.ccap,
                tb_cat_partidaspresupuestales_capitulo.ccapmiles
                FROM tb_compromiso
                JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.type = tb_compromiso.nu_type AND chartdetailsbudgetlog.transno = tb_compromiso.nu_transno
                JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = chartdetailsbudgetlog.cvefrom
                JOIN tb_cat_partidaspresupuestales_partidaespecifica ON tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = chartdetailsbudgetbytag.partida_esp
                JOIN tb_cat_partidaspresupuestales_capitulo ON tb_cat_partidaspresupuestales_capitulo.ccap = tb_cat_partidaspresupuestales_partidaespecifica.ccap
                WHERE
                tb_compromiso.nu_estatus = '4'
                AND tb_compromiso.nu_tipo != 'D'
                AND tb_compromiso.nu_id_compromiso = '".$idcompromiso."' ".$sqlWhere."
                ORDER BY idmov ASC";
                $ErrMsg = "No se obtuvo información del detalle del compromiso";
                $TransResultClaves = DB_query($SQL, $db, $ErrMsg);
                if (DB_num_rows($TransResultClaves) > 0) {
                    // Tiene capitulos no aceptados
                    $result = false;
                    if (DB_num_rows($TransResultClaves) == 1) {
                        // Solo es un capitulo no aceptado
                        $myrow2 = DB_fetch_array($TransResultClaves);
                        $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Compromiso '.$idcompromiso.' tiene el capítulo: '.$myrow2['ccapmiles'].'</p>';
                    } else {
                        $capitulos = "";
                        while ($myrow2 = DB_fetch_array($TransResultClaves)) {
                            if ($capitulos == "") {
                                $capitulos = $myrow2["ccapmiles"];
                            } else {
                                $capitulos .= ", ".$myrow2["ccapmiles"];
                            }
                        }
                        $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Compromiso '.$idcompromiso.' tiene los capítulos: '.$capitulos.'</p>';
                    }
                } else {
                    // Los capitulos aceptados
                    $info['type'] = $myrow ['nu_type'];
                    $info['transno'] = $myrow ['nu_transno'];
                    $result = true;
                }
            } else {
                // Seleccionar tipo de operación
                $result = false;
                $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Compromiso '.$idcompromiso.' se encuentra '.$myrow ['statusname'].'</p>';
            }
        } else {
            // No se encuentra autorizado
            $result = false;
            $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Compromiso '.$idcompromiso.' se encuentra '.$myrow ['statusname'].'</p>';
        }
    } else {
        // No existe compromiso
        $result = false;
        $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Compromiso '.$idcompromiso.' no existe</p>';
    }

    $contenido = array('datos' => $info);
}

if ($option == 'existeOficioComision') {
    $idcompromiso = $_POST['idcompromiso']; // Oficio de comision
    $selectTipo = $_POST['selectTipo'];

    $info = array();
    $SQL = "SELECT
    '501' as nu_type,
    tb_viaticos.sn_folio_solicitud,
    tb_viaticos.systypeno as nu_transno,
    tb_viaticos.id_nu_estatus as nu_estatus,
    tb_botones_status.namebutton as statusname
    FROM tb_viaticos
    LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_viaticos.id_nu_estatus AND tb_botones_status.sn_funcion_id = '2338' AND tb_botones_status.functionid = 0
    WHERE
    tb_viaticos.ind_tipo_gasto = 2
    AND tb_viaticos.sn_folio_solicitud = '".$idcompromiso."'";
    // AND tb_viaticos.id_nu_estatus = 8
    $ErrMsg = "No se obtuvo información del oficio de comisión";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $myrow = DB_fetch_array($TransResult);
        if ($myrow ['nu_estatus'] == '8') {
            // Oficio de comision autorizado
            $info['type'] = $myrow ['nu_type'];
            $info['transno'] = $myrow ['nu_transno'];
            $result = true;
        } else {
            // No se encuentra autorizado
            $result = false;
            $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Oficio de Comisión '.$idcompromiso.' se encuentra '.$myrow ['statusname'].'</p>';
        }
    } else {
        // No existe oficio de comisión
        $result = false;
        $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Oficio de Comisión '.$idcompromiso.' no existe</p>';
    }

    $contenido = array('datos' => $info);
}

if ($option == 'existeDevengado') {
    $idcompromiso = $_POST['idcompromiso']; // No Devengado
    $selectTipo = $_POST['selectTipo'];

    $info = array();
    $SQL = "SELECT
    tb_pagos.nu_type,
    tb_pagos.nu_id_devengado,
    tb_pagos.nu_transno,
    tb_pagos.nu_estatus,
    tb_botones_status.statusname
    FROM tb_pagos
    LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_pagos.nu_estatus AND tb_botones_status.sn_funcion_id = tb_pagos.sn_funcion_id
    WHERE
    tb_pagos.nu_type != '299'
    AND tb_pagos.nu_id_devengado = '".$idcompromiso."'";
    // AND tb_pagos.nu_estatus = 4
    $ErrMsg = "No se obtuvo información del número del devengado";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $myrow = DB_fetch_array($TransResult);
        if ($myrow ['nu_estatus'] == '4') {
            // Devengado autorizado
            // Validar si tiene documentos no cancelados
            $SQL = "SELECT
            supptrans.suppreference
            FROM supptrans
            WHERE
            supptrans.hold != '-2'
            AND supptrans.type = '".$myrow ['nu_type']."'
            AND supptrans.transno = '".$myrow ['nu_transno']."'";
            $ErrMsg = "No se obtuvo información de los documentos de tesoreria";
            $TransResultClaves = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($TransResultClaves) > 0) {
                // Tiene documentos por pagar
                $result = false;
                if (DB_num_rows($TransResultClaves) == 1) {
                    $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Devengado '.$idcompromiso.' tiene una operación pendiente en tesoreria. Para realizar el proceso se deber rechazada</p>';
                } else {
                    $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Devengado '.$idcompromiso.' tiene operaciones pendientes en tesoreria. Para realizar el proceso deben se rechazadas</p>';
                }
            } else {
                // No tiene documentos por pagar
                $info['type'] = $myrow ['nu_type'];
                $info['transno'] = $myrow ['nu_transno'];
                $result = true;
            }
        } else {
            // No se encuentra autorizado
            $result = false;
            $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Devengado '.$idcompromiso.' se encuentra '.$myrow ['statusname'].'</p>';
        }
    } else {
        // No existe no devengado
        $result = false;
        $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Devengado '.$idcompromiso.' no existe</p>';
    }

    $contenido = array('datos' => $info);
}

if ($option == 'infoProveedorTesofe') {
    $info = array();
    $SQL = "SELECT supplierid 
    FROM suppliers WHERE nu_tesofe = '1'";
    $ErrMsg = "No se obtuvo la información de la tesofe";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info['supplierid'] = $myrow ['supplierid'];
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'infoProveedor') {
    $supplierid = $_POST['txtProveedor'];

    $info = array();
    $SQL = "SELECT taxid, ln_representante_legal, CONCAT(supplierid, ' - ', suppname) as suppname 
    FROM suppliers WHERE supplierid = '".$supplierid."'";
    $ErrMsg = "No se obtuvo la información del proveedor ".$supplierid;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        // Existe proveedor
        $myrow = DB_fetch_array($TransResult);
        if (empty($myrow ['taxid'])) {
            // Rfc vacío
            $myrow ['taxid'] = '';
        }
        if (empty($myrow ['ln_representante_legal'])) {
            // Representante legal vacío
            $myrow ['ln_representante_legal'] = '';
        }
        $info['rfc'] = $myrow ['taxid'];
        $info['representante'] = $myrow ['ln_representante_legal'];
        $info['nombre'] = $myrow ['suppname'];

        // Obteber impuestos
        $datosInfoImpuestos = array();
        $SQL = "SELECT
        tb_suppliersRetencion.supplierid,
        tb_suppliersRetencion.idret,
        tb_retenciones.txt_descripcion,
        tb_retenciones.nu_porcentaje
        FROM tb_suppliersRetencion
        JOIN tb_retenciones ON tb_retenciones.id = tb_suppliersRetencion.idret
        WHERE tb_suppliersRetencion.supplierid = '".$supplierid."'";
        $ErrMsg = "No se obtuvo la información de las retenciones del proveedor";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $datosInfoImpuestos[] = array(
                'proveedor' => $myrow ['supplierid'],
                'retencion' => $myrow ['idret'],
                'descripcion' => $myrow ['txt_descripcion'],
                'porcentaje' => $myrow ['nu_porcentaje'],
                'total' => 0
            );
        }
        $info['datosInfoImpuestos'] = $datosInfoImpuestos;

        $datosCuentasClabe = array();
        $SQL = "SELECT
        tb_bancos_proveedores.nu_id as value,
        tb_bancos_proveedores.nu_clabe_interbancaria as texto
        FROM tb_bancos_proveedores
        WHERE
        tb_bancos_proveedores.ln_supplierid = '".$supplierid."'
        AND tb_bancos_proveedores.ln_activo = 1
        ORDER BY tb_bancos_proveedores.nu_clabe_interbancaria ASC";
        $ErrMsg = "No se obtuvo los Estatus";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $datosCuentasClabe[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
        }
        $info['datosCuentasClabe'] = $datosCuentasClabe;
        $result = true;
    } else {
        // No existe proveedor
        $result = false;
        $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Proveedor/Beneficiario '.$supplierid.' no existe</p>';
    }

    $contenido = array('datos' => $info);
}

if ($option == 'obtenerPresupuesto') {
    $clave = $_POST['clave'];
    $account = $_POST['account'];
    $legalid = $_POST['legalid'];
    $datosClave = $_POST['datosClave'];
    $datosClaveAdecuacion = $_POST['datosClaveAdecuacion'];
    $tipoAfectacion = $_POST['tipoAfectacion'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $tipoMovimiento = $_POST['tipoMovimiento'];
    $period = "";

    $res = true;

    $info = fnInfoPresupuesto($db, $clave, $period, $account, $legalid, $datosClave, $datosClaveAdecuacion, $tipoAfectacion, $type, $transno, $tipoMovimiento);

    if (empty($info)) {
        $Mensaje = "No se encontró la información para la Clave Presupuestal ".$clave;
        $res = false;
    }

    $contenido = array('datos' => $info);
    $result = $res;
}

if ($option == 'cargarInfoNoCapturaCompromiso') {
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $datosClave = $_POST['datosClave'];
    $datosClaveAdecuacion = $_POST['datosClaveAdecuacion'];
    $idcompromiso = $_POST['idcompromiso'];
    $decremento = $_POST['decremento'];

    // Obtener periodo actual para compromisos
    $periodoActual = GetPeriod(date('d/m/y'), $db);

    $info = array();
    $SQL = "
    SELECT 
    distinct chartdetailsbudgetlog.cvefrom,
    CASE WHEN chartdetailsbudgetlog.qty < 0 THEN 'Reduccion' ELSE 'Ampliacion' END as tipoMovimiento,
    chartdetailsbudgetbytag.anho
    FROM chartdetailsbudgetlog
    JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = chartdetailsbudgetlog.cvefrom
    WHERE chartdetailsbudgetlog.type = '".$type."'
    AND chartdetailsbudgetlog.transno = '".$transno."'
    -- AND chartdetailsbudgetlog.nu_tipo_movimiento = '259'
    AND chartdetailsbudgetlog.qty != 0
    -- AND chartdetailsbudgetlog.qty < 0
    ORDER BY idmov ASC
    ";
    if (!empty($idcompromiso)) {
        // Cargar claves del original e incrementos, puros registros autorizados
        $SQL = "SELECT
        distinct
        chartdetailsbudgetlog.cvefrom,
        CASE WHEN chartdetailsbudgetlog.qty < 0 THEN 'Reduccion' ELSE 'Ampliacion' END as tipoMovimiento,
        chartdetailsbudgetbytag.anho
        FROM tb_compromiso
        JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.type = tb_compromiso.nu_type AND chartdetailsbudgetlog.transno = tb_compromiso.nu_transno
        JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = chartdetailsbudgetlog.cvefrom
        WHERE
        tb_compromiso.nu_estatus = '4'
        AND tb_compromiso.nu_tipo != 'D'
        AND tb_compromiso.nu_id_compromiso = '".$idcompromiso."'
        ORDER BY idmov ASC";
    }
    if ($type == '501') {
        // Si es comprobacion de viaticos
        $SQL = "SELECT
        distinct
        tb_viaticos.accountcode_general as cvefrom,
        'Reduccion' AS tipoMovimiento
        FROM tb_viaticos
        LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_viaticos.id_nu_estatus AND tb_botones_status.sn_funcion_id = '2338' AND tb_botones_status.functionid = 0
        WHERE
        tb_viaticos.ind_tipo_gasto = 2
        AND tb_viaticos.id_nu_estatus = 8
        AND tb_viaticos.sn_folio_solicitud = '".$idcompromiso."'
        AND tb_viaticos.systypeno = '".$transno."'
        ORDER BY tb_viaticos.id_nu_viaticos ASC";
    }
    if ($decremento == '1') {
        // Si es decremento de pagos
        $SQL = "SELECT
        distinct
        SUM(chartdetailsbudgetlog.qty) as qty,
        chartdetailsbudgetlog.period,
        chartdetailsbudgetlog.cvefrom,
        chartdetailsbudgetlog.ln_ue,
        chartdetailsbudgetlog.nu_id_compromiso,
        chartdetailsbudgetlog.nu_id_devengado,
        chartdetailsbudgetlog.nu_idret,
        'Reduccion' as tipoMovimiento
        FROM tb_pagos
        JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.type = tb_pagos.nu_type AND chartdetailsbudgetlog.transno = tb_pagos.nu_transno
        WHERE 
        chartdetailsbudgetlog.sn_disponible = 1
        AND chartdetailsbudgetlog.nu_tipo_movimiento = 260
        AND chartdetailsbudgetlog.type = '".$type."'
        AND chartdetailsbudgetlog.transno = '".$transno."'
        GROUP BY chartdetailsbudgetlog.cvefrom, chartdetailsbudgetlog.nu_id_devengado, chartdetailsbudgetlog.nu_idret
        HAVING qty < 0";
    }
    $ErrMsg = "No se obtuvieron los presupuestos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $clave = $myrow['cvefrom'];
        $account = "";
        $legalid = "";
        $period = "";
        $tipoAfectacion = "";
        $tipoMovimiento = $myrow['tipoMovimiento'];
        $nu_id_compromiso = "";
        $nu_id_devengado = "";
        $nu_idret = 0;
        if ($decremento == '1') {
            // Si es decremento de pagos
            $nu_id_compromiso = $myrow['nu_id_compromiso'];
            $nu_id_devengado = $myrow['nu_id_devengado'];
            $nu_idret = $myrow['nu_idret'];
        }
        $infoDatos = fnInfoPresupuesto($db, $clave, $period, $account, $legalid, $datosClave, $datosClaveAdecuacion, $tipoAfectacion, $type, $transno, $tipoMovimiento, '', '', '', 0, $nu_id_devengado, $nu_id_compromiso, $nu_idret);
        $info[] = $infoDatos;
    }

    //Obtener nombre de estatus
    $statusname = "";
    $estatus = "";
    $legalid = "";
    $tagref = "";
    $justificacion = "";
    $ln_ue = "";

    $fechaCaptura = date('d-m-Y');
    $fechaInicio = date('d-m-Y');
    $fechaFinal = date('d-m-Y');
    $fechaFirma = date('d-m-Y');
    $selectTipo = '';
    $txtIdCompromiso = '';
    $txtProveedor = '';
    $txtContratoConvenio = '';
    $nu_id_devengado = '';

    $SQL = "SELECT 
    legalbusinessunit.legalid, 
    tags.tagref, 
    tb_botones_status.statusname, 
    tb_compromiso.nu_estatus,
    tb_compromiso.txt_justificacion, 
    tb_compromiso.ln_ue,
    DATE_FORMAT(tb_compromiso.dtm_fecha, '%d-%m-%Y') as dtm_fecha,
    tb_compromiso.nu_id_compromiso,
    tb_compromiso.sn_contrato,
    tb_compromiso.nu_tipo,
    tb_compromiso.supplierid
    FROM tb_compromiso
    JOIN tags ON tags.tagref = tb_compromiso.sn_tagref
    JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_compromiso.nu_estatus AND tb_botones_status.sn_funcion_id = '".$funcion."'
    WHERE 
    tb_compromiso.nu_type = '".$type."'
    AND tb_compromiso.nu_transno = '".$transno."'";
    if ($type == '501') {
        // Si es comprobacion de viaticos
        $SQL = "SELECT
        legalbusinessunit.legalid, 
        tags.tagref, 
        tb_botones_status.namebutton as statusname,
        tb_viaticos.id_nu_estatus as nu_estatus,
        '' as txt_justificacion,
        tb_viaticos.id_nu_ue as ln_ue,
        DATE_FORMAT(NOW(), '%d-%m-%Y') as dtm_fecha,
        tb_viaticos.sn_folio_solicitud as nu_id_compromiso,
        '' as sn_contrato,
        '297' as nu_tipo,
        tb_empleados.sn_clave_empleado as supplierid,
        tb_viaticos.*
        FROM tb_viaticos
        JOIN tags ON tags.tagref = tb_viaticos.tagref
        JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
        LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_viaticos.id_nu_estatus AND tb_botones_status.sn_funcion_id = '2338' AND tb_botones_status.functionid = 0
        JOIN tb_empleados ON tb_empleados.id_nu_empleado = tb_viaticos.id_nu_empleado
        WHERE
        tb_viaticos.ind_tipo_gasto = 2
        AND tb_viaticos.id_nu_estatus = 8
        AND tb_viaticos.sn_folio_solicitud = '".$idcompromiso."'
        AND tb_viaticos.systypeno = '".$transno."'
        ORDER BY tb_viaticos.id_nu_viaticos ASC";
    }
    if ($decremento == '1') {
        // Si es decremento de pagos
        $SQL = "SELECT 
        legalbusinessunit.legalid, 
        tags.tagref, 
        tb_botones_status.statusname, 
        tb_pagos.nu_estatus,
        tb_pagos.txt_justificacion, 
        tb_pagos.ln_ue,
        DATE_FORMAT(tb_pagos.dtm_fecha, '%d-%m-%Y') as dtm_fecha,
        tb_pagos.nu_id_compromiso,
        tb_pagos.nu_id_devengado,
        tb_pagos.sn_contrato,
        '299' as nu_tipo,
        tb_pagos.supplierid
        FROM tb_pagos
        JOIN tags ON tags.tagref = tb_pagos.sn_tagref
        JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
        LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_pagos.nu_estatus AND tb_botones_status.sn_funcion_id = tb_pagos.sn_funcion_id
        WHERE 
        tb_pagos.nu_type = '".$type."'
        AND tb_pagos.nu_transno = '".$transno."'";
    }
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $legalid = $myrow['legalid'];
        $tagref = $myrow['tagref'];
        $estatus = $myrow['nu_estatus'];
        $statusname = $myrow['statusname'];
        $justificacion = $myrow['txt_justificacion'];
        $ln_ue = $myrow['ln_ue'];

        $fechaCaptura = $myrow['dtm_fecha'];
        $txtIdCompromiso = $myrow['nu_id_compromiso'];
        $txtContratoConvenio = $myrow['sn_contrato'];
        $selectTipo = $myrow['nu_tipo'];
        $txtProveedor = $myrow['supplierid'];
        $nu_id_devengado = $myrow['nu_id_devengado'];
    }

    if (empty($justificacion)) {
        $justificacion = "";
    }

    if ($decremento == '1') {
        // Si es decremento de pagos
        $txtIdCompromiso = $nu_id_devengado;
        $nu_id_devengado = '';
    }

    $reponse['datos'] = $info;
    $reponse['legalid'] = $legalid;
    $reponse['tagref'] = $tagref;
    $reponse['ln_ue'] = $ln_ue;
    $reponse['transno'] = $transno;
    $reponse['type'] = $type;
    $reponse['estatus'] = $estatus;
    $reponse['statusname'] = $statusname;
    $reponse['justificacion'] = $justificacion;

    $reponse['fechaCaptura'] = $fechaCaptura;
    
    $reponse['txtIdCompromiso'] = $txtIdCompromiso;
    $reponse['txtIdDevendago'] = '';
    $reponse['txtContratoConvenio'] = $txtContratoConvenio;
    $reponse['selectTipo'] = $selectTipo;
    $reponse['txtProveedor'] = $txtProveedor;

    $contenido = $reponse;//array('datos' => $info);
    $result = true;
}

if ($option == 'cargarInfoNoCaptura') {
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $datosClave = $_POST['datosClave'];
    $datosClaveAdecuacion = $_POST['datosClaveAdecuacion'];
    $idcompromiso = $_POST['idcompromiso'];

    // Obtener periodo actual para compromisos
    $periodoActual = GetPeriod(date('d/m/y'), $db);

    $info = array();
    $SQL = "
    SELECT 
    distinct chartdetailsbudgetlog.cvefrom,
    CASE WHEN chartdetailsbudgetlog.qty < 0 THEN 'Reduccion' ELSE 'Ampliacion' END as tipoMovimiento
    FROM chartdetailsbudgetlog
    JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = chartdetailsbudgetlog.cvefrom
    WHERE chartdetailsbudgetlog.type = '".$type."'
    AND chartdetailsbudgetlog.transno = '".$transno."'
    AND chartdetailsbudgetlog.nu_tipo_movimiento = '260'
    AND chartdetailsbudgetlog.qty != 0
    AND chartdetailsbudgetlog.qty < 0
    ORDER BY idmov ASC
    ";
    if ($type == '298' || $type == '299') {
        // Si es pago retenciones o decremento
        $SQL = "
        SELECT 
        distinct chartdetailsbudgetlog.cvefrom,
        CASE WHEN chartdetailsbudgetlog.qty < 0 THEN 'Reduccion' ELSE 'Ampliacion' END as tipoMovimiento,
        chartdetailsbudgetlog.nu_id_compromiso,
        chartdetailsbudgetlog.nu_id_devengado,
        chartdetailsbudgetlog.nu_idret
        FROM chartdetailsbudgetlog
        JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = chartdetailsbudgetlog.cvefrom
        WHERE chartdetailsbudgetlog.type = '".$type."'
        AND chartdetailsbudgetlog.transno = '".$transno."'
        AND chartdetailsbudgetlog.nu_tipo_movimiento = '260'
        AND chartdetailsbudgetlog.qty != 0
        -- AND chartdetailsbudgetlog.qty < 0
        ORDER BY idmov ASC
        ";
    }
    $ErrMsg = "No se obtuvieron los presupuestos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $clave = $myrow['cvefrom'];
        $account = "";
        $legalid = "";
        $period = "";
        $tipoAfectacion = "";
        $tipoMovimiento = $myrow['tipoMovimiento'];
        $nu_id_compromiso = '';
        $nu_id_devengado = '';
        $nu_idret = 0;
        if ($type == '298' || $type == '299') {
            // Si es pago retenciones
            $nu_id_compromiso = $myrow['nu_id_compromiso'];
            $nu_id_devengado = $myrow['nu_id_devengado'];
            $nu_idret = $myrow['nu_idret'];
        }
        $infoDatos = fnInfoPresupuesto($db, $clave, $period, $account, $legalid, $datosClave, $datosClaveAdecuacion, $tipoAfectacion, $type, $transno, $tipoMovimiento, '', '', '', 0, $nu_id_devengado, $nu_id_compromiso, $nu_idret);
        $info[] = $infoDatos;
    }

    //Obtener nombre de estatus
    $statusname = "";
    $estatus = "";
    $legalid = "";
    $tagref = "";
    $justificacion = "";
    $ln_ue = "";

    $fechaCaptura = date('d-m-Y');
    $fechaInicio = date('d-m-Y');
    $fechaFinal = date('d-m-Y');
    $fechaFirma = date('d-m-Y');
    $selectTipo = '';
    $txtIdCompromiso = '';
    $txtIdDevendago = '';
    $txtProveedor = '';
    $txtContratoConvenio = '';
    $selectClabe = '';
    $txtFactura = '';
    $txtFechaFactura = '';
    $txtFechaInicio = '';
    $txtFechaFin = '';
    $sn_folio_solicitud = '';

    $SQL = "SELECT legalbusinessunit.legalid, tags.tagref, tb_botones_status.statusname, 
    tb_pagos.nu_estatus,
    tb_pagos.txt_justificacion, 
    tb_pagos.ln_ue,
    DATE_FORMAT(tb_pagos.dtm_fecha, '%d-%m-%Y') as dtm_fecha,
    tb_pagos.nu_id_compromiso,
    tb_pagos.nu_id_devengado,
    tb_pagos.sn_contrato,
    tb_pagos.nu_type,
    tb_pagos.supplierid,
    tb_pagos.id_clabe,
    tb_pagos.sn_factura,
    DATE_FORMAT(tb_pagos.dtm_fecha_factura, '%d-%m-%Y') as dtm_fecha_factura,
    DATE_FORMAT(tb_pagos.dtm_fecha_inicio, '%d-%m-%Y') as dtm_fecha_inicio,
    DATE_FORMAT(tb_pagos.dtm_fecha_fin, '%d-%m-%Y') as dtm_fecha_fin,
    tb_pagos.sn_folio_solicitud
    FROM tb_pagos
    JOIN tags ON tags.tagref = tb_pagos.sn_tagref
    JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_pagos.nu_estatus AND tb_botones_status.sn_funcion_id = '".$funcion."'
    WHERE 
    tb_pagos.nu_type = '".$type."'
    AND tb_pagos.nu_transno = '".$transno."'";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $legalid = $myrow['legalid'];
        $tagref = $myrow['tagref'];
        $estatus = $myrow['nu_estatus'];
        $statusname = $myrow['statusname'];
        $justificacion = $myrow['txt_justificacion'];
        $ln_ue = $myrow['ln_ue'];

        $fechaCaptura = $myrow['dtm_fecha'];
        $txtIdCompromiso = $myrow['nu_id_compromiso'];
        $txtIdDevendago = $myrow['nu_id_devengado'];
        $txtContratoConvenio = $myrow['sn_contrato'];
        $selectTipo = $myrow['nu_type'];
        $txtProveedor = $myrow['supplierid'];
        $selectClabe = $myrow['id_clabe'];
        $txtFactura = $myrow['sn_factura'];
        $txtFechaFactura = $myrow['dtm_fecha_factura'];
        $txtFechaInicio = $myrow['dtm_fecha_inicio'];
        $txtFechaFin = $myrow['dtm_fecha_fin'];
        $sn_folio_solicitud = $myrow['sn_folio_solicitud'];
    }

    if (empty($justificacion)) {
        $justificacion = "";
    }

    if ($type == '297') {
        // PAgo de viaticos
        $txtIdCompromiso = $sn_folio_solicitud;
    }

    $reponse['datos'] = $info;
    $reponse['legalid'] = $legalid;
    $reponse['tagref'] = $tagref;
    $reponse['ln_ue'] = $ln_ue;
    $reponse['transno'] = $transno;
    $reponse['type'] = $type;
    $reponse['estatus'] = $estatus;
    $reponse['statusname'] = $statusname;
    $reponse['justificacion'] = $justificacion;

    $reponse['fechaCaptura'] = $fechaCaptura;
    
    $reponse['txtIdCompromiso'] = $txtIdCompromiso;
    $reponse['txtIdDevendago'] = $txtIdDevendago;
    $reponse['txtContratoConvenio'] = $txtContratoConvenio;
    $reponse['selectTipo'] = $selectTipo;
    $reponse['txtProveedor'] = $txtProveedor;
    $reponse['selectClabe'] = $selectClabe;
    $reponse['txtFactura'] = $txtFactura;
    $reponse['txtFechaFactura'] = $txtFechaFactura;
    $reponse['txtFechaInicio'] = $txtFechaInicio;
    $reponse['txtFechaFin'] = $txtFechaFin;

    $contenido = $reponse;//array('datos' => $info);
    $result = true;
}

if ($option == 'guardarOperacion') {
    $datosReducciones = $_POST['datosCapturaReducciones'];
    $datosAmpliaciones = $_POST['datosCapturaAmpliaciones'];
    $datosImpuestos = $_POST['datosCapturaImpuestos'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $estatus = $_POST['estatus'];
    $fechaCaptura = $_POST['fechaCaptura'];
    $typeSuficiencia = 260;
    $description = "Manual";
    $functionSuf = $funcion;
    $justificacion = $_POST['justificacion'];
    $ue = $_POST['ue'];
    $tipoSuficiencia = $_POST['selectTipo'];
    $txtIdCompromiso = $_POST['txtIdCompromiso'];
    $txtIdDevendago = $_POST['txtIdDevendago'];
    $txtProveedor = $_POST['txtProveedor'];
    $txtContratoConvenio = $_POST['txtContratoConvenio'];
    $selectClabe = $_POST['selectClabe'];
    $txtFactura = $_POST['txtFactura'];
    $txtFechaFactura = $_POST['txtFechaFactura'];
    $txtFechaInicio = $_POST['txtFechaInicio'];
    $txtFechaFin = $_POST['txtFechaFin'];
    $sn_orderno = 0;
    $requisitionno = 0;
    $nombreSuficiencia = "";
    $suficienciaNueva = 0;

    $sn_folio_solicitud = "";
    if ($type == '297' ) {
        // Pago de viaticos
        $sn_folio_solicitud = $txtIdCompromiso;
        $txtIdCompromiso = "";
    }

    $txtFechaFactura = date_create($txtFechaFactura);
    $txtFechaFactura = date_format($txtFechaFactura, 'Y-m-d');

    $txtFechaInicio = date_create($txtFechaInicio);
    $txtFechaInicio = date_format($txtFechaInicio, 'Y-m-d');

    $txtFechaFin = date_create($txtFechaFin);
    $txtFechaFin = date_format($txtFechaFin, 'Y-m-d');
    
    if (empty($transno)) {
        $suficienciaNueva = 1;
        $transno = GetNextTransNo($type, $db);
        $datosPresupuesto['transno'] = $transno;
    }

    if (empty(trim($sn_orderno))) {
        $sn_orderno = 0;
    }

    if (empty($txtIdDevendago) || strlen($txtIdDevendago) < 5) {
        // Si es original obtener id de compromiso
        $txtIdDevendago = GetNextTransNo(301, $db);
        if (strlen($txtIdDevendago) < 5) {
            // Si es menos a 6 caracteres completar con ceros 0
            $cadena = $txtIdDevendago;
            for ($i = strlen($txtIdDevendago); $i < 5; $i++) {
                $cadena = '0'.$cadena;
            }
            $txtIdDevendago = $cadena;
        }
    }

    $SQL = "SELECT nu_type, nu_transno FROM tb_pagos 
            WHERE 
            nu_type = '".$type."' 
            AND nu_transno = '".$transno."' ";
    $ErrMsg = "No se pudo almacenar la información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) == 0) {
        $sqlOperacion = 1;
    } else {
        $sqlOperacion = 2;
    }
    if ($sqlOperacion == 1) {
        // Agregar datos
        $SQL = "INSERT INTO tb_pagos
        (dtm_fecha,
        sn_userid,
        txt_justificacion,
        nu_type,
        nu_transno,
        nu_id_compromiso,
        nu_id_devengado,
        sn_contrato,
        nu_estatus,
        sn_tagref,
        supplierid,
        sn_funcion_id,
        ln_ue,
        id_clabe,
        sn_factura,
        dtm_fecha_factura,
        dtm_fecha_inicio,
        dtm_fecha_fin,
        sn_folio_solicitud,
        nu_anio_fiscal
        )
        VALUES
        (NOW(), 
        '".$_SESSION['UserID']."', 
        '".$justificacion."', 
        '".$type."', 
        '".$transno."', 
        '".$txtIdCompromiso."',
        '".$txtIdDevendago."',
        '".$txtContratoConvenio."',
        '".$estatus."', 
        '".$tagref."', 
        '".$txtProveedor."',
        ".$functionSuf.",
        '".$ue."',
        '".$selectClabe."',
        '".$txtFactura."',
        '".$txtFechaFactura."',
        '".$txtFechaInicio."',
        '".$txtFechaFin."',
        '".$sn_folio_solicitud."',
        '".$_SESSION['ejercicioFiscal']."'
        )";
        $ErrMsg = "No se pudo almacenar la información";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    } else if ($sqlOperacion == 2) {
        // Actualizar datos
        $SQL = "UPDATE tb_pagos SET 
        sn_userid = '".$_SESSION['UserID']."', 
        txt_justificacion = '".$justificacion."', 
        nu_id_compromiso = '".$txtIdCompromiso."',
        nu_id_devengado = '".$txtIdDevendago."',
        sn_contrato = '".$txtContratoConvenio."',
        nu_estatus = '".$estatus."',
        sn_tagref = '".$tagref."',
        supplierid = '".$txtProveedor."',
        sn_funcion_id = ".$functionSuf.",
        ln_ue = '".$ue."',
        id_clabe = '".$selectClabe."',
        sn_factura = '".$txtFactura."',
        dtm_fecha_factura = '".$txtFechaFactura."',
        dtm_fecha_inicio = '".$txtFechaInicio."',
        dtm_fecha_fin = '".$txtFechaFin."',
        sn_folio_solicitud = '".$sn_folio_solicitud."'
        WHERE 
        nu_type = '".$type."' 
        AND nu_transno = '".$transno."'";
        $ErrMsg = "No se pudo almacenar la información";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }

    // Log en negativo para Original e Incremento, Decremento en positivo
    $operacionLog = -1;
    $ordenOperacion = 'DESC';
    $movimientoTipo = 'Compromiso';

    if ($type == '294') {
        // Si es directo validar con el diponible
        $movimientoTipo = '';
    }

    if ($type == '298') {
        // Si es retenciones
        $movimientoTipo = 'Retenciones';
    }

    if ($type == '299') {
        // Si es decremento
        $operacionLog = 1;
        $movimientoTipo = 'Devengado';
    }

    // Borrar registros en 0
    $SQL = "DELETE FROM chartdetailsbudgetlog 
    WHERE 
    chartdetailsbudgetlog.type = '".$type."' 
    AND chartdetailsbudgetlog.transno = '".$transno."'";
    $ErrMsg = "No se borraron los registros anteriores";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    // Actualizar registros solo cuando no se va cancelar
    foreach ($datosReducciones as $datosClave) {
        $clave = $datosClave['accountcode'];
        $ln_ue = fnObtenerUnidadEjecutoraClave($db, $clave);
        $description = "Devengado ".$txtIdDevendago." en tramite. Folio ".$transno;
        $numMes = 1;
        foreach ($dataJsonMeses as $nameMes) {
            $periodo = GetPeriod('15/'.(strlen($numMes) == 1 ? '0'.$numMes : $numMes).'/'.$datosClave['año'], $db);
            $cantidad = $datosClave[$nameMes] * $operacionLog;
            
            if (abs($cantidad) != 0) {
                // Si tiene cantidad
                $respuesta = fnInsertPresupuestoLogAcomulado($db, $type, $transno, $tagref, $clave, $periodo, $cantidad, $typeSuficiencia, "", $description, 0, $estatus, 0, $ln_ue, $ordenOperacion, 'disponible', $movimientoTipo, 'Reduccion', 1, $datosClave['noDevengado'], $datosClave['noCompromiso'], $datosClave['noRetencion']);
            }
            
            $numMes ++;
        }
    }

    // Borrar registros de impuestos
    $SQL = "DELETE FROM tb_pagos_retenciones 
    WHERE 
    tb_pagos_retenciones.nu_type = '".$type."' 
    AND tb_pagos_retenciones.nu_transno = '".$transno."'";
    $ErrMsg = "No se borraron los registros anteriores de los impuestos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    if (count($datosImpuestos) > 0) {
        // Si tiene retenciones almacenar información
        $SQL = "SELECT
        chartdetailsbudgetlog.*
        FROM chartdetailsbudgetlog
        WHERE
        chartdetailsbudgetlog.type = '".$type."' 
        AND chartdetailsbudgetlog.transno = '".$transno."'";
        $ErrMsg = "No se obtuvo el detalle del pago";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            // Recorrrer informacion para obtener el total de la retencion
            foreach ($datosImpuestos as $datosImp) {
                // Recorrrer retenciones
                $cantidad = ($myrow['qty'] * $datosImp['porcentaje']) / 100;
                $SQL = "INSERT INTO tb_pagos_retenciones
                (dtm_fecha,
                sn_userid,
                txt_justificacion,
                nu_type,
                nu_transno,
                nu_id_compromiso,
                nu_id_devengado,
                sn_contrato,
                nu_estatus,
                sn_tagref,
                supplierid,
                sn_funcion_id,
                ln_ue,
                nu_idret,
                nu_id_log,
                ln_clavepresupuestal,
                nu_period,
                nu_qty)
                VALUES
                (NOW(),
                '".$_SESSION['UserID']."',
                '".$justificacion."',
                '".$type."',
                '".$transno."',
                '".$txtIdCompromiso."',
                '".$txtIdDevendago."',
                '".$txtContratoConvenio."',
                '".$estatus."',
                '".$tagref."',
                '".$txtProveedor."',
                ".$functionSuf.",
                '".$ue."',
                '".$datosImp['retencion']."',
                '".$myrow['idmov']."',
                '".$myrow['cvefrom']."',
                '".$myrow['period']."',
                '".$cantidad."')";
                $ErrMsg = "No se guardo el detalle de la retención";
                $TransResult2 = DB_query($SQL, $db, $ErrMsg);

                // Restar a la cantidad total la retención
                $SQL = "UPDATE chartdetailsbudgetlog SET qty = qty + ".abs($cantidad)." WHERE idmov = '".$myrow['idmov']."'";
                $ErrMsg = "No se guardo el detalle 2 de la retención";
                $TransResult2 = DB_query($SQL, $db, $ErrMsg);

                $agregoLog = fnInsertPresupuestoLog($db, $type, $transno, $tagref, $myrow['cvefrom'], $myrow['period'], $cantidad, $typeSuficiencia, $myrow['partida_esp'], $myrow['description'], $myrow['sn_disponible'], $estatus, 0, $myrow['ln_ue'], $txtIdCompromiso, $txtIdDevendago, $datosImp['retencion']);
            }
        }
    }

    if ($type != '298' && $type != '299') {
        // Si no es retenciones y decrementos
        // Guardar número de compromiso en el log presupuestal
        $SQL = "UPDATE chartdetailsbudgetlog SET nu_id_compromiso = '".$txtIdCompromiso."', nu_id_devengado = '".$txtIdDevendago."'
        WHERE type = '".$type."' and transno = '".$transno."'";
        $ErrMsg = "No se actualizó el número de compromiso y devengado en el log presupuestal";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }

    $Mensaje = "Se ha guardado exitosamente el Devengado número ".$txtIdDevendago." con Folio ".$transno;

    // Borrar registros en 0
    $SQL = "DELETE FROM chartdetailsbudgetlog 
            WHERE 
            chartdetailsbudgetlog.type = '".$type."' 
            AND chartdetailsbudgetlog.transno = '".$transno."'
            AND chartdetailsbudgetlog.qty = 0 ";
    $ErrMsg = "No se Actualizaron Registros en Generales";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    //Datos generales
    $SQL = "SELECT legalbusinessunit.legalid, tags.tagref
            FROM chartdetailsbudgetlog 
            JOIN tags ON tags.tagref = chartdetailsbudgetlog.tagref
            JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
            WHERE type = '".$type."' AND transno = '".$transno."' ";
    $ErrMsg = "No se obtuvo el nombre del Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $legalid = $myrow['legalid'];
        $tagref = $myrow['tagref'];
    }

    //Obtener nombre de estatus
    $statusname = "";
    $SQL = "SELECT statusname as statusname, statusname as statusname2
            FROM tb_botones_status WHERE statusid = '".$estatus."' AND tb_botones_status.sn_funcion_id = '".$funcion."'";
    $ErrMsg = "No se obtuvo el nombre del Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $statusname = $myrow['statusname'];
    }

    $datosPresupuesto['legalid'] = $legalid;
    $datosPresupuesto['tagref'] = $tagref;
    $datosPresupuesto['transno'] = $transno;
    $datosPresupuesto['type'] = $type;
    $datosPresupuesto['estatus'] = $estatus;
    $datosPresupuesto['statusname'] = $statusname;

    if ($type == '297') {
        // Pago de viaticos
        $txtIdCompromiso = $sn_folio_solicitud;
    }

    $datosPresupuesto['txtIdCompromiso'] = $txtIdCompromiso;
    $datosPresupuesto['txtIdDevendago'] = $txtIdDevendago;

    $contenido = array('datos' => $datosPresupuesto);
    $result = true;
}

if ($option == 'eliminaPresupuesto') {
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $clave = $_POST['clave'];
    $ampliacionReduccion = $_POST['tipo'];
    $tipoAdecuacion = $_POST['tipoAdecuacion'];

    $SQL = "DELETE FROM chartdetailsbudgetlog 
            WHERE type = '".$type."' AND transno = '".$transno."' AND cvefrom = '".$clave."' ";
    $ErrMsg = "No se elimino la Clave Presupuestal";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $Mensaje = "Eliminación correcta de la Clave ".$clave;

    $contenido = $Mensaje;
    $result = true;
}

function fnBusquedaDefault($db, $sqlWhere)
{
    $info = array();

    $SQL = "
    SELECT
    distinct
    chartdetailsbudgetbytag.*
    FROM chartdetailsbudgetbytag
    JOIN tb_cat_partidaspresupuestales_partidaespecifica ON tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = chartdetailsbudgetbytag.partida_esp
    JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
    JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
    JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = chartdetailsbudgetbytag.tagref AND tb_sec_users_ue.ln_aux1 = chartdetailsbudgetbytag.ln_aux1
    WHERE 
    chartdetailsbudgetbytag.anho = '".$_SESSION['ejercicioFiscal']."'
    AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."' ".$sqlWhere."
    ORDER BY chartdetailsbudgetbytag.accountcode ASC";
    $ErrMsg = "No se obtuvieron los Presupuestos para la Búsqueda";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $value = "";
        $SQL = "SELECT campoPresupuesto FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow['idClavePresupuesto']."' AND adecuacion_presupuesto = '1' ";
        $TransResult2 = DB_query($SQL, $db, $ErrMsg);
        while ($myrow2 = DB_fetch_array($TransResult2)) {
            if (empty($value)) {
                $value = $myrow [$myrow2['campoPresupuesto']];
            } else {
                $value .= '-'.$myrow [$myrow2['campoPresupuesto']];
            }
        }

        $datos['value'] = $value;
        $datos['accountcode'] = $myrow ['accountcode'];
        $datos['valorLista'] = $value;

        $claveCorta = "";
        $SQL = "SELECT campoPresupuesto, nu_clave_corta_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' AND sn_clave_corta = '1' ORDER BY nu_clave_corta_orden ASC ";
        $resultClave = DB_query($SQL, $db, $ErrMsg);
        while ($rowClave = DB_fetch_array($resultClave)) {
            if (empty($claveCorta)) {
                $claveCorta = $myrow [$rowClave ['campoPresupuesto']];
            } else {
                $claveCorta .= "-".$myrow [$rowClave ['campoPresupuesto']];
            }
        }

        $datos['claveCorta'] = $claveCorta;

        $claveLarga = "";
        $SQL = "SELECT campoPresupuesto, nu_clave_larga_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$myrow ['idClavePresupuesto']."' AND sn_clave_larga = '1' ORDER BY nu_clave_larga_orden ASC ";
        $resultClave = DB_query($SQL, $db, $ErrMsg);
        while ($rowClave = DB_fetch_array($resultClave)) {
            if (empty($claveLarga)) {
                $claveLarga = $myrow [$rowClave ['campoPresupuesto']];
            } else {
                $claveLarga .= "-".$myrow [$rowClave ['campoPresupuesto']];
            }
        }

        $datos['claveLarga'] = $claveLarga;

        $info[] = $datos;
    }

    return $info;
}

if ($option == 'obtenerPresupuestosBusqueda') {
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $ue = $_POST['ue'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $filtrosClave = $_POST['filtrosClave'];
    
    $sqlWhere = "";
    if ($legalid != "" and $legalid != '-1') {
        $sqlWhere .= " AND legalbusinessunit.legalid = '".$legalid."' ";
    }
    if ($tagref != "" and $tagref != '-1') {
        $sqlWhere .= " AND tags.tagref = '".$tagref."' ";
    }

    if ($ue != "" and $ue != '-1') {
        $sqlWhere .= " AND tb_sec_users_ue.ue = '".$ue."' ";
    }

    if (!empty($filtrosClave)) {
        foreach ($filtrosClave as $datosClave) {
            if ($datosClave['valor'] != '-1') {
                // echo "\n campoPresupuesto: ".$datosClave['campoPresupuesto'];
                $sqlWhere .= " AND chartdetailsbudgetbytag.".$datosClave['campoPresupuesto']." = '".$datosClave['valor']."' ";
            }
        }
    }

    $info = array();

    $info = fnBusquedaDefault($db, $sqlWhere);

    $contenido = array('datos' => $info);
    $result = true;
}



if ($option == 'mostrarUnidadNegocio') {
    $legalid = $_POST['legalid'];

    $sqlWhere = "";
    if ($legalid != '-1' and !empty($legalid)) {
        $sqlWhere = " AND t.legalid IN(".$legalid.") ";
    }
    $info = array();
    $SQL = "SELECT  t.tagref as value, CONCAT(t.tagref, ' - ', t.tagdescription) as texto, t.tagref
            FROM sec_unegsxuser u,tags t 
            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' ".$sqlWhere." 
            ORDER BY t.tagref ";
    $ErrMsg = "No se obtuvieron las URG";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'obtenerBotones') {
    $autorizarGeneral = $_POST['autorizarGeneral'];
    $soloActFoliosAutorizada = $_POST['soloActFoliosAutorizada'];
    $sqlWhere = " AND tb_botones_status.statusid <> '98' ";
    if ($autorizarGeneral == '1') {
        // Si es autorizar solo mostrar rechazar y autorizar
        $sqlWhere = " AND tb_botones_status.statusid IN ('5', '99') ";
    }

    if ($soloActFoliosAutorizada == '1') {
        // Estatus 7 todo finalizado
        $sqlWhere = " AND tb_botones_status.statusid IN ('98') ";
    }

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
            AND (tb_botones_status.sn_adecuacion_presupuestal = 1)
            AND
            (tb_botones_status.functionid = sec_funxprofile.functionid 
            OR 
            tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid AND sec_funxuser.userid = '".$_SESSION['UserID']."' AND sec_funxuser.permiso = 1)
            ) ".$sqlWhere."
            ORDER BY tb_botones_status.functionid ASC
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

if ($option == 'mostrarRazonSocial') {
    $ramo = $_POST['ramo'];
    $sqlWhere = "";
    if (!empty($ramo) and $ramo != '-1') {
        $sqlWhere = " AND legalbusinessunit.cve_ramo IN (".$ramo.") ";
    }
    $info = array();
    // GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname
    $SQL = "SELECT distinct legalbusinessunit.legalid, CONCAT(legalbusinessunit.legalid, ' - ', legalbusinessunit.legalname) as legalname 
            FROM sec_unegsxuser u, tags t 
            JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid 
            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' " . $sqlWhere . "
            ORDER BY legalbusinessunit.legalid ";
    $ErrMsg = "No se obtuvieron las razones sociales";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['legalid'], 'texto' => $myrow ['legalname'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
