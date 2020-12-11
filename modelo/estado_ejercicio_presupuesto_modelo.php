<?php
/**
 * Adecuaciones Presupuestales
 *
 * @category
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 08/08/2017
 * Fecha Modificación: 08/08/2017
 * Modelos para las operaciones de Adecuaciones Presupuestales
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//
$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
$funcion=2275;
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

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

ini_set('memory_limit', '1024M');
set_time_limit(600);

$option = $_POST['option'];

if ($option == 'obtenerEstadoEjercicio') {
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $ue = $_POST['ue'];
    $tipoMovimiento = $_POST['tipoMovimiento'];
    $idClave = $_POST['idClave'];
    $selConfig = $_POST['selConfig'];
    $tipoInformacion = $_POST['tipoInformacion'];
    $meses = $_POST['meses'];

    $yearInfo = date('Y');

    // Obtener año de la configuración de la clave
    $SQL = "SELECT nu_anio FROM budgetConfigClave WHERE idClavePresupuesto = '".$idClave."' limit 1";
    $ErrMsg = "No se obtuvo el año de la Configuración de la Clave Presupuestal";
    $transResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($transResult)) {
        $yearInfo = $myrow['nu_anio'];
    }

    $sqlWherePeriodos = "";

    if (!empty($meses)) {
        $sqlWherePeriodos .= " AND DATE_FORMAT(periods.lastdate_in_period, '%m') IN (".$meses.") ";
    }

    $columnasRegistros = array();
    $SQL = "SELECT 
    systypescat.typeid,
    systypescat.ln_descripcion_corta as typename,
    periods.periodno,
    cat_Months.mes
    FROM systypescat
    JOIN periods ON DATE_FORMAT(periods.lastdate_in_period, '%Y') = '".$yearInfo."' AND LOCATE('.', periods.periodno) = '0'
    JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
    WHERE systypescat.typeid IN (".$tipoMovimiento.") ".$sqlWherePeriodos."
    GROUP BY systypescat.typeid, typename,  periods.periodno, cat_Months.mes";
    //echo "<pre> 1: ".$SQL;
    $ErrMsg = "No se obtuvieron las columnas";
    $transResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($transResult)) {
        $columnasRegistros[] = array('typeid' => $myrow['typeid'], 'typename' => $myrow['typename'],
        'periodno' => $myrow['periodno'], 'mes' => $myrow['mes']);
    }

    $sqlWhere = "";

    if (!empty($legalid)) {
        $sqlWhere .= " AND tags.legalid IN (".$legalid.") ";
    }

    if (!empty($tagref)) {
        $sqlWhere .= " AND chartdetailsbudgetbytag.tagref IN (".$tagref.") ";
    }

    if (!empty($ue)) {
        // $sqlWhere .= " AND SUBSTRING(chartdetailsbudgetbytag.ln_aux1, 4,4) IN (".$ue.") ";
        $sqlWhere .= " AND tb_cat_unidades_ejecutoras.ue IN (".$ue.") ";
    }

    if (!empty($idClave)) {
        $sqlWhere .= " AND chartdetailsbudgetbytag.idClavePresupuesto = '".$idClave."' ";
    }

    $sqlCamposClave = "";
    $sqlAgrupacion = "";
    $sqlOrdenar = "";
    foreach ($selConfig as $config) {
        $sqlCamposClave .= ", chartdetailsbudgetbytag.".$config['campoPresupuesto'];
        $sqlAgrupacion .= ", ".$config['campoPresupuesto'];
        if ($sqlOrdenar == '') {
            $sqlOrdenar .= $config['campoPresupuesto'];
        } else {
            $sqlOrdenar .= ", ".$config['campoPresupuesto'];
        }
    }

    $info = array();
    // Consulta: muestra solo claves con movimientos
    $SQL = "
    SELECT
    chartdetailsbudgetlog.cvefrom,
    chartdetailsbudgetlog.period as periodo,
    chartdetailsbudgetlog.nu_tipo_movimiento,
    systypescat.typename,
    SUM(chartdetailsbudgetlog.qty) as qty
    ".$sqlCamposClave."
    FROM chartdetailsbudgetlog
    JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = chartdetailsbudgetlog.cvefrom
    JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
    JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    JOIN systypescat ON systypescat.typeid = chartdetailsbudgetlog.nu_tipo_movimiento
    JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ln_aux1 = chartdetailsbudgetbytag.ln_aux1
    WHERE 
    chartdetailsbudgetlog.sn_disponible = '1'
    AND chartdetailsbudgetbytag.anho = '".$yearInfo."'
    AND chartdetailsbudgetlog.nu_tipo_movimiento IN (".$tipoMovimiento.") ".$sqlWhere."
    GROUP BY chartdetailsbudgetlog.cvefrom, periodo, chartdetailsbudgetlog.nu_tipo_movimiento, systypescat.typename ".$sqlAgrupacion."
    ORDER BY ".$sqlOrdenar.", chartdetailsbudgetlog.cvefrom, chartdetailsbudgetlog.nu_tipo_movimiento, periodo
    ";

    // Consulta: muestra claves sin movientos pone en 0
    // + SUM(ROUND(CASE WHEN totalDisponibleAcomulado.qty <> '' THEN totalDisponibleAcomulado.qty ELSE '0' END, 2))
    $SQL = "
    SELECT 
    chartdetailsbudgetbytag.accountcode as cvefrom,
    systypescat.typeid as nu_tipo_movimiento,
    systypescat.typename,
    periods.periodno as periodo,
    
    ROUND(CASE WHEN totalModificado.qty <> '' THEN totalModificado.qty ELSE '0' END, 2) as modificado,
    ROUND(CASE WHEN totalDisponible.qty <> '' THEN totalDisponible.qty ELSE '0' END, 2) as disponible,
    ROUND(CASE WHEN totalModificado.qty <> '' THEN totalModificado.qty ELSE '0' END, 2)
    +
    ROUND(CASE WHEN totalPorLiberar.qty <> '' THEN totalPorLiberar.qty ELSE '0' END, 2) as porliberar,
    ROUND(CASE WHEN totalPorRadicar.qty <> '' THEN totalPorRadicar.qty ELSE '0' END, 2) as porradicar,

    ROUND(CASE WHEN totalLiberadoDisponible.qty <> '' THEN totalLiberadoDisponible.qty ELSE '0' END, 2) as liberadodisponible,

    ROUND(CASE WHEN totalRadicadoDisponible.qty <> '' THEN totalRadicadoDisponible.qty ELSE '0' END, 2) as radicadodisponible,

    ROUND(CASE WHEN totalLog.qty <> '' THEN totalLog.qty ELSE '0' END, 2) as qty
    ".$sqlCamposClave."
    FROM chartdetailsbudgetbytag
    JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
    JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    JOIN systypescat ON systypescat.typeid IN (".$tipoMovimiento.")
    JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ln_aux1 = chartdetailsbudgetbytag.ln_aux1
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = chartdetailsbudgetbytag.tagref AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = chartdetailsbudgetbytag.tagref AND tb_sec_users_ue.ue = tb_cat_unidades_ejecutoras.ue AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'
    JOIN periods ON DATE_FORMAT(periods.lastdate_in_period, '%Y') = '".$yearInfo."' AND LOCATE('.', periods.periodno) = '0'
    LEFT JOIN (
    SELECT
    SUM(chartdetailsbudgetlog.qty) as qty,
    chartdetailsbudgetlog.cvefrom,
    chartdetailsbudgetlog.period as periodo,
    chartdetailsbudgetlog.nu_tipo_movimiento
    FROM chartdetailsbudgetlog
    WHERE chartdetailsbudgetlog.sn_disponible = '1'
    GROUP BY cvefrom, periodo, nu_tipo_movimiento
    ) as totalLog ON 
    totalLog.cvefrom = chartdetailsbudgetbytag.accountcode 
    AND totalLog.periodo = periods.periodno
    AND totalLog.nu_tipo_movimiento = systypescat.typeid

    LEFT JOIN (
    SELECT
    SUM(chartdetailsbudgetlog.qty) as qty,
    chartdetailsbudgetlog.cvefrom,
    chartdetailsbudgetlog.period as periodo
    FROM chartdetailsbudgetlog
    WHERE chartdetailsbudgetlog.sn_disponible = '1'
    AND chartdetailsbudgetlog.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where nu_estado_presupuesto = 1 AND nu_usar_modificado = 1)
    GROUP BY cvefrom, periodo
    ) as totalModificado ON 
    totalModificado.cvefrom = chartdetailsbudgetbytag.accountcode 
    AND totalModificado.periodo = periods.periodno
    
    LEFT JOIN (
    SELECT
    SUM(chartdetailsbudgetlog.qty) as qty,
    chartdetailsbudgetlog.cvefrom,
    chartdetailsbudgetlog.period as periodo
    FROM chartdetailsbudgetlog
    WHERE chartdetailsbudgetlog.sn_disponible = '1'
    AND chartdetailsbudgetlog.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where nu_estado_presupuesto = 1 AND nu_usar_disponible = 1)
    GROUP BY cvefrom, periodo
    ) as totalDisponible ON 
    totalDisponible.cvefrom = chartdetailsbudgetbytag.accountcode 
    AND totalDisponible.periodo = periods.periodno
    
    LEFT JOIN (
    SELECT
    SUM(chartdetailsbudgetlog.qty) as qty,
    chartdetailsbudgetlog.cvefrom,
    chartdetailsbudgetlog.period as periodo
    FROM chartdetailsbudgetlog
    WHERE chartdetailsbudgetlog.sn_disponible = '1'
    AND chartdetailsbudgetlog.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where nu_estado_presupuesto = 1 AND nu_usar_disponible = 1)
    GROUP BY cvefrom, periodo
    ) as totalDisponibleAcomulado ON 
    totalDisponibleAcomulado.cvefrom = chartdetailsbudgetbytag.accountcode 
    AND totalDisponibleAcomulado.periodo < periods.periodno
    
    LEFT JOIN (
    SELECT
    SUM(chartdetailsbudgetlog.qty) as qty,
    chartdetailsbudgetlog.cvefrom,
    chartdetailsbudgetlog.period as periodo
    FROM chartdetailsbudgetlog
    WHERE chartdetailsbudgetlog.sn_disponible = '1'
    AND chartdetailsbudgetlog.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where nu_usar_por_liberar = 1)
    GROUP BY cvefrom, periodo
    ) as totalPorLiberar ON 
    totalPorLiberar.cvefrom = chartdetailsbudgetbytag.accountcode 
    AND totalPorLiberar.periodo = periods.periodno
    
    LEFT JOIN (
    SELECT
    SUM(chartdetailsbudgetlog.qty * systypescat.naturalezacontable) as qty,
    chartdetailsbudgetlog.cvefrom,
    chartdetailsbudgetlog.period as periodo
    FROM chartdetailsbudgetlog
    JOIN systypescat ON systypescat.typeid = chartdetailsbudgetlog.nu_tipo_movimiento
    WHERE chartdetailsbudgetlog.sn_disponible = '1'
    AND chartdetailsbudgetlog.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where nu_usar_por_radicar = 1)
    GROUP BY cvefrom, periodo
    ) as totalPorRadicar ON 
    totalPorRadicar.cvefrom = chartdetailsbudgetbytag.accountcode 
    AND totalPorRadicar.periodo = periods.periodno

    LEFT JOIN (
    SELECT
    SUM(chartdetailsbudgetlog.qty) as qty,
    chartdetailsbudgetlog.cvefrom,
    chartdetailsbudgetlog.period as periodo
    FROM chartdetailsbudgetlog
    WHERE chartdetailsbudgetlog.sn_disponible = '1'
    AND chartdetailsbudgetlog.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where nu_usar_liberado_disp = 1)
    GROUP BY cvefrom, periodo
    ) as totalLiberadoDisponible ON 
    totalLiberadoDisponible.cvefrom = chartdetailsbudgetbytag.accountcode 
    AND totalLiberadoDisponible.periodo = periods.periodno

    LEFT JOIN (
    SELECT
    SUM(chartdetailsbudgetlog.qty) as qty,
    chartdetailsbudgetlog.cvefrom,
    chartdetailsbudgetlog.period as periodo
    FROM chartdetailsbudgetlog
    WHERE chartdetailsbudgetlog.sn_disponible = '1'
    AND chartdetailsbudgetlog.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where nu_usar_radicado_disp = 1)
    GROUP BY cvefrom, periodo
    ) as totalRadicadoDisponible ON 
    totalRadicadoDisponible.cvefrom = chartdetailsbudgetbytag.accountcode 
    AND totalRadicadoDisponible.periodo = periods.periodno

    WHERE
    chartdetailsbudgetbytag.anho = '".$yearInfo."' ".$sqlWhere." ".$sqlWherePeriodos."
    GROUP BY ".$sqlOrdenar.", cvefrom, nu_tipo_movimiento, typename, periodo
    ORDER BY ".$sqlOrdenar.", cvefrom, nu_tipo_movimiento, typename, periodo
    ";
    // JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
    // AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    // echo "\n";
    // echo "<pre> 2: ".$SQL;
    // echo "\n";
    // print_r($columnasRegistros);
    // exit();
    $ErrMsg = "No se obtuvieron los registros de las Claves Presupuestales";
    $transResult = DB_query($SQL, $db, $ErrMsg);

    // Columnas para el GRID
    $columnasNombres .= "[";
    // $columnasNombres .= "{ name: 'cvefrom', type: 'string' }";

    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    // $columnasNombresGrid .= " { text: 'Clave', datafield: 'cvefrom', width: '5%', hidden: true }";

    $numColClave = 0;
    $numColTipoMov = 0;
    $claveAnterior = "";
    $tipoMovAnterior = "";
    $datos = array();
    $datosClave = array();
    $numColumnasGeneral = 0;
    $numColTipoMovTotal = 0;
    $totalEstadoPresupuesto = 0;
    while ($myrow = DB_fetch_array($transResult)) {
        if ($claveAnterior != $myrow['cvefrom'] && $claveAnterior != "") {
            // Si la clave son diferentes poner columnas de totales
            $typeidAnt = "";
            foreach ($columnasRegistros as $columnas) {
                // Nombre y titulo de columan
                $nombreCol = $columnas['typeid'].'_'.$columnas['periodno'];
                $tituloCol = $columnas['typename'].substr($columnas['mes'], 0, 3);

                // Buscar cantitdad si viene en array o dejar 0
                $cantidad = 0;
                foreach ($datosClave as $datosCla) {
                    if ($columnas['typeid'] == $datosCla['nu_tipo_movimiento'] && $columnas['periodno'] == $datosCla['period']) {
                        $cantidad = ($datosCla['qty']);

                        if ($columnas['typeid'] == '255') {
                            // Si es 255-Modificado Autorizado
                            $cantidad = ($datosCla['modificado']);
                        }

                        if ($columnas['typeid'] == '264') {
                            // Si es 264-Disponible
                            $cantidad = ($datosCla['disponible']);
                        }

                        if ($columnas['typeid'] == '267') {
                            // Si es 267-Por Liberar
                            $cantidad = ($datosCla['porliberar']);
                        }

                        if ($columnas['typeid'] == '270') {
                            // Si es 270-Por Radicar
                            $cantidad = ($datosCla['porradicar']);
                        }

                        if ($columnas['typeid'] == '302') {
                            // Si es 302-Liberado Disponible
                            $cantidad = ($datosCla['liberadodisponible']);
                        }

                        if ($columnas['typeid'] == '303') {
                            // Si es 303-Radicado Disponible
                            $cantidad = ($datosCla['radicadodisponible']);
                        }

                        break;
                    }
                }

                // if ($columnas['typeid'] == '255' || $columnas['typeid'] == '264') {
                //     // Si es 255-Modificado Autorizado o 264-Disponible se saca de diferente manera la cantidad
                //     $sqlWhere = " AND chartdetailsbudgetlog.nu_tipo_movimiento IN (251, 253, 254) ";
                //     if ($columnas['typeid'] == '264') {
                //         $sqlWhere = " AND chartdetailsbudgetlog.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where nu_estado_presupuesto = 1 AND nu_usar_disponible = 1) ";
                //     }
                //     $SQL = "SELECT
                //     -- SUM(chartdetailsbudgetlog.qty) as total
                //     SUM(ROUND(chartdetailsbudgetlog.qty, 2)) as total
                //     FROM chartdetailsbudgetlog
                //     WHERE
                //     chartdetailsbudgetlog.sn_disponible = '1'
                //     AND chartdetailsbudgetlog.cvefrom = '".$claveAnterior."'
                //     AND chartdetailsbudgetlog.period = '".$columnas['periodno']."' ".$sqlWhere;
                //     $transDatos = DB_query($SQL, $db, $ErrMsg);
                //     while ($myrowDatos = DB_fetch_array($transDatos)) {
                //         $cantidad = abs($myrowDatos['total']);
                //     }
                // }

                // Asignar cantidad a la columna
                $datos[$nombreCol] = number_format($cantidad, $_SESSION['DecimalPlaces'], '.', '');

                if ($typeidAnt != $columnas['typeid'] && $typeidAnt != '') {
                    // Poner los totales del tipo de movimiento anterior
                    $nombreColTotal = 'Tot_'.$typeidAnt;
                    $datos[$nombreColTotal] = number_format($totalEstadoPresupuesto, $_SESSION['DecimalPlaces'], '.', '');
                    $totalEstadoPresupuesto = 0;
                }

                $totalEstadoPresupuesto = $totalEstadoPresupuesto + $cantidad;

                if ($typeidAnt != $columnas['typeid']) {
                    // Si son tipos de movimiento diferente poner renglon total
                    $nombreColTotal = 'Tot_'.$columnas['typeid'];
                    $tituloColTotal = 'Total '.$columnas['typename'];

                    $datos[$nombreColTotal] = 0;

                    if ($numColTipoMovTotal == 0) {
                        // Primera ves poner encabezado para tabla
                        $numColumnasGeneral ++;

                        $columnasNombres .= ", { name: '".$nombreColTotal."', type: 'float' } ";

                        $columnasNombresGrid .= ", { text: '".$tituloColTotal."', datafield: '".$nombreColTotal."', width: '10%', cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false } ";
                    }
                }

                if ($numColTipoMov == 0) {
                    // Primera ves poner encabezado para tabla
                    $numColumnasGeneral ++;

                    $columnasNombres .= ", { name: '".$nombreCol."', type: 'float' } ";

                    $columnasNombresGrid .= ", { text: '".$tituloCol."', datafield: '".$nombreCol."', width: '10%', cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false } ";
                }

                $typeidAnt = $columnas['typeid'];
            }

            if ($totalEstadoPresupuesto != 0 && $typeidAnt != '') {
                // Poner los totales del tipo de movimiento anterior el ultimo movimiento
                $nombreColTotal = 'Tot_'.$typeidAnt;
                $datos[$nombreColTotal] = number_format($totalEstadoPresupuesto, $_SESSION['DecimalPlaces'], '.', '');
                $totalEstadoPresupuesto = 0;
            }

            // Agregar datos
            $info[] = $datos;

            // Limpiar variables
            $datos = array();
            $datosClave = array();
            $numColTipoMov = 1;
            $numColTipoMovTotal = 1;
        }

        // Clave del presupuesto
        $datos['accountcode'] = $myrow['cvefrom'];

        // Datos seleccionados para mostrar
        foreach ($selConfig as $config) {
            $datos[$config['campoPresupuesto']] = $myrow[$config['campoPresupuesto']];

            if ($numColClave == 0) {
                $numColumnasGeneral ++;
                
                $columnasNombres .= ", { name: '".$config['campoPresupuesto']."', type: 'string' } ";

                $columnasNombresGrid .= ", { text: '".$config['nombre']."', datafield: '".$config['campoPresupuesto']."', width: '".$config['tamEstEjercicio']."%', cellsalign: 'center', align: 'center', hidden: false } ";
            }
        }

        // Datos del periodo y tipo de movimiento
        $datosClave[] = array(
            'qty' => abs($myrow['qty']),
            'disponible' => abs($myrow['disponible']),
            'modificado' => abs($myrow['modificado']),
            'porliberar' => abs($myrow['porliberar']),
            'porradicar' => abs($myrow['porradicar']),
            'liberadodisponible' => abs($myrow['liberadodisponible']),
            'radicadodisponible' => abs($myrow['radicadodisponible']),
            'period' => $myrow['periodo'],
            'nu_tipo_movimiento' => $myrow['nu_tipo_movimiento']
        );
        
        // Asigar clave y tipo de movimiento
        $claveAnterior = $myrow['cvefrom'];
        $tipoMovAnterior = $myrow['nu_tipo_movimiento'];

        $numColClave = 1;
    }

    if ($claveAnterior != $myrow['cvefrom'] && $claveAnterior != "") {
        // Si la clave son diferentes poner columnas de totales
        $typeidAnt = "";
        foreach ($columnasRegistros as $columnas) {
            // Nombre y titulo de columan
            $nombreCol = $columnas['typeid'].'_'.$columnas['periodno'];
            $tituloCol = $columnas['typename'].substr($columnas['mes'], 0, 3);

            // Buscar cantitdad si viene en array o dejar 0
            $cantidad = 0;
            foreach ($datosClave as $datosCla) {
                if ($columnas['typeid'] == $datosCla['nu_tipo_movimiento'] && $columnas['periodno'] == $datosCla['period']) {
                    $cantidad = ($datosCla['qty']);

                    if ($columnas['typeid'] == '255') {
                        // Si es 255-Modificado Autorizado
                        $cantidad = ($datosCla['modificado']);
                    }

                    if ($columnas['typeid'] == '264') {
                        // Si es 264-Disponible
                        $cantidad = ($datosCla['disponible']);
                    }

                    if ($columnas['typeid'] == '267') {
                        // Si es 267-Por Liberar
                        $cantidad = ($datosCla['porliberar']);
                    }

                    if ($columnas['typeid'] == '270') {
                        // Si es 270-Por Radicar
                        $cantidad = ($datosCla['porradicar']);
                    }
                    
                    break;
                }
            }

            // if ($columnas['typeid'] == '255' || $columnas['typeid'] == '264') {
            //     // Si es 255-Modificado Autorizado o 264-Disponible se saca de diferente manera la cantidad
            //     $sqlWhere = " AND chartdetailsbudgetlog.nu_tipo_movimiento IN (251, 253, 254) ";
            //     if ($columnas['typeid'] == '264') {
            //         $sqlWhere = " AND chartdetailsbudgetlog.nu_tipo_movimiento IN (SELECT typeid FROM systypescat where nu_estado_presupuesto = 1 AND nu_usar_disponible = 1) ";
            //     }
            //     $SQL = "SELECT SUM(chartdetailsbudgetlog.qty) as total
            //     FROM chartdetailsbudgetlog
            //     WHERE
            //     chartdetailsbudgetlog.sn_disponible = '1'
            //     AND chartdetailsbudgetlog.cvefrom = '".$claveAnterior."'
            //     AND chartdetailsbudgetlog.period = '".$columnas['periodno']."' ".$sqlWhere;
            //     $transDatos = DB_query($SQL, $db, $ErrMsg);
            //     while ($myrowDatos = DB_fetch_array($transDatos)) {
            //         $cantidad = abs($myrowDatos['total']);
            //     }
            // }

            // Asignar cantidad a la columna
            $datos[$nombreCol] = number_format($cantidad, $_SESSION['DecimalPlaces'], '.', '');

            if ($typeidAnt != $columnas['typeid'] && $typeidAnt != '') {
                // Poner los totales del tipo de movimiento anterior
                $nombreColTotal = 'Tot_'.$typeidAnt;
                $datos[$nombreColTotal] = number_format($totalEstadoPresupuesto, $_SESSION['DecimalPlaces'], '.', '');
                $totalEstadoPresupuesto = 0;
            }

            $totalEstadoPresupuesto = $totalEstadoPresupuesto + $cantidad;

            if ($typeidAnt != $columnas['typeid']) {
                // Si son tipos de movimiento diferente poner renglon total
                $nombreColTotal = 'Tot_'.$columnas['typeid'];
                $tituloColTotal = 'Total '.$columnas['typename'];

                $datos[$nombreColTotal] = 0;

                if ($numColTipoMovTotal == 0) {
                    // Primera ves poner encabezado para tabla
                    $numColumnasGeneral ++;

                    $columnasNombres .= ", { name: '".$nombreColTotal."', type: 'float' } ";

                    $columnasNombresGrid .= ", { text: '".$tituloColTotal."', datafield: '".$nombreColTotal."', width: '10%', cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false } ";
                }
            }

            if ($numColTipoMov == 0) {
                // Primera ves poner encabezado para tabla
                $numColumnasGeneral ++;

                $columnasNombres .= ", { name: '".$nombreCol."', type: 'float' } ";

                $columnasNombresGrid .= ", { text: '".$tituloCol."', datafield: '".$nombreCol."', width: '10%', cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false } ";
            }

            $typeidAnt = $columnas['typeid'];
        }

        if ($totalEstadoPresupuesto != 0 && $typeidAnt != '') {
            // Poner los totales del tipo de movimiento anterior el ultimo movimiento
            $nombreColTotal = 'Tot_'.$typeidAnt;
            $datos[$nombreColTotal] = number_format($totalEstadoPresupuesto, $_SESSION['DecimalPlaces'], '.', '');
            $totalEstadoPresupuesto = 0;
        }

        // Agregar datos
        $info[] = $datos;

        // Limpiar variables
        $datos = array();
        $datosClave = array();
        $numColTipoMov = 1;
        $numColTipoMovTotal = 1;
    }

    // Obtener todas la claves y agregar las no agregadas
    $SQL = "SELECT distinct accountcode 
    FROM chartdetailsbudgetbytag 
    JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
    WHERE anho = '2018'";
    
    $columnasNombres .= "]";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array(
        'datos' => $info,
        'columnasNombres' => $columnasNombres,
        'columnasNombresGrid' => $columnasNombresGrid,
        'nombreExcel' => $nombreExcel,
        'numColumnasGeneral' => $numColumnasGeneral,
        'tam' => count($info)
    );
    $result = true;
}

if ($option == 'mostrarTipo') {
    $tipo = $_POST['tipo'];
    $tipoPresupuesto = $_POST['tipoPresupuesto'];

    $sqlWhere = "";
    if ($tipo == '1') {
        // Presupuesto
        if ($tipoPresupuesto == 1) {
            // Ingreso
            $sqlWhere = " AND nu_estado_presupuesto_ingreso = '1' ";
        } else {
            // Egreso
            $sqlWhere = " AND nu_estado_presupuesto = '1' ";
        }
    } else if ($tipo == '2') {
        // Ministado
        $sqlWhere = " AND nu_estado_ministrado = '1' ";
    } else {
        // Radicado
        $sqlWhere = " AND nu_estado_radicado = '1' ";
    }

    $info = array();
    $SQL = "SELECT typeid as value, CONCAT(typeid, ' - ', typename) as texto 
    FROM systypescat 
    WHERE 1 = 1 ".$sqlWhere."
    ORDER BY typeid ASC";
    $ErrMsg = "No se obtuvo los Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
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
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'tagref' => $myrow ['tagref'], 'tagdescription' => $myrow ['tagdescription'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
