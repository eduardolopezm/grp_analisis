<?php
/**
 * Suficiencia Manual
 *
 * @category
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Modelos para las operaciones de Suficiencia Manual
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
$funcion=2409;
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

if ($option == 'existeCompromiso') {
    $idcompromiso = $_POST['idcompromiso'];

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
            $info['type'] = $myrow ['nu_type'];
            $info['transno'] = $myrow ['nu_transno'];
            $result = true;
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

if ($option == 'infoProveedor') {
    $supplierid = $_POST['txtProveedor'];

    $info = array();
    $SQL = "SELECT taxid, ln_representante_legal, CONCAT(supplierid, ' - ', suppname) as suppname 
    FROM suppliers WHERE supplierid = '".$supplierid."'";
    $ErrMsg = "No se obtuvieron las URG";
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
    $ErrMsg = "No se obtuvieron los presupuestos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $clave = $myrow['cvefrom'];
        $account = "";
        $legalid = "";
        $period = "";
        $tipoAfectacion = "";
        $tipoMovimiento = $myrow['tipoMovimiento'];
        $infoDatos = fnInfoPresupuesto($db, $clave, $period, $account, $legalid, $datosClave, $datosClaveAdecuacion, $tipoAfectacion, $type, $transno, $tipoMovimiento);
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

    $SQL = "SELECT legalbusinessunit.legalid, tags.tagref, tb_botones_status.statusname, 
    tb_compromiso.nu_estatus,
    tb_compromiso.txt_justificacion, 
    tb_compromiso.ln_ue,
    DATE_FORMAT(tb_compromiso.dtm_fecha, '%d-%m-%Y') as dtm_fecha,
    DATE_FORMAT(tb_compromiso.dtm_fecha_inicio, '%d-%m-%Y') as dtm_fecha_inicio,
    DATE_FORMAT(tb_compromiso.dtm_fecha_fin, '%d-%m-%Y') as dtm_fecha_fin,
    DATE_FORMAT(tb_compromiso.dtm_fecha_firma, '%d-%m-%Y') as dtm_fecha_firma,
    tb_compromiso.nu_id_compromiso,
    tb_compromiso.sn_contrato,
    tb_compromiso.nu_tipo,
    tb_compromiso.supplierid
    FROM chartdetailsbudgetlog
    JOIN tags ON tags.tagref = chartdetailsbudgetlog.tagref
    JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    JOIN tb_compromiso ON tb_compromiso.nu_type = chartdetailsbudgetlog.type AND tb_compromiso.nu_transno = chartdetailsbudgetlog.transno
    LEFT JOIN tb_botones_status ON tb_botones_status.statusid = chartdetailsbudgetlog.estatus AND tb_botones_status.sn_funcion_id = '".$funcion."'
    WHERE 
    chartdetailsbudgetlog.type = '".$type."'
    AND chartdetailsbudgetlog.transno = '".$transno."'
    LIMIT 1";
    $SQL = "SELECT legalbusinessunit.legalid, tags.tagref, tb_botones_status.statusname, 
    tb_compromiso.nu_estatus,
    tb_compromiso.txt_justificacion, 
    tb_compromiso.ln_ue,
    DATE_FORMAT(tb_compromiso.dtm_fecha, '%d-%m-%Y') as dtm_fecha,
    DATE_FORMAT(tb_compromiso.dtm_fecha_inicio, '%d-%m-%Y') as dtm_fecha_inicio,
    DATE_FORMAT(tb_compromiso.dtm_fecha_fin, '%d-%m-%Y') as dtm_fecha_fin,
    DATE_FORMAT(tb_compromiso.dtm_fecha_firma, '%d-%m-%Y') as dtm_fecha_firma,
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
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $legalid = $myrow['legalid'];
        $tagref = $myrow['tagref'];
        $estatus = $myrow['nu_estatus'];
        $statusname = $myrow['statusname'];
        $justificacion = $myrow['txt_justificacion'];
        $ln_ue = $myrow['ln_ue'];

        $fechaCaptura = $myrow['dtm_fecha'];
        $fechaInicio = $myrow['dtm_fecha_inicio'];
        $fechaFinal = $myrow['dtm_fecha_fin'];
        $fechaFirma = $myrow['dtm_fecha_firma'];
        $txtIdCompromiso = $myrow['nu_id_compromiso'];
        $txtContratoConvenio = $myrow['sn_contrato'];
        $selectTipo = $myrow['nu_tipo'];
        $txtProveedor = $myrow['supplierid'];
    }

    if (empty($justificacion)) {
        $justificacion = "";
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
    $reponse['fechaInicio'] = $fechaInicio;
    $reponse['fechaFinal'] = $fechaFinal;
    $reponse['fechaFirma'] = $fechaFirma;
    $reponse['txtIdCompromiso'] = $txtIdCompromiso;
    $reponse['txtContratoConvenio'] = $txtContratoConvenio;
    $reponse['selectTipo'] = $selectTipo;
    $reponse['txtProveedor'] = $txtProveedor;

    $contenido = $reponse;//array('datos' => $info);
    $result = true;
}

/**
 * Función para obtener la información del tipo de compromiso
 * @param  [type] $db   Base de Datos
 * @param  string $type Tipo de Compromiso
 * @return [type]       Información del tipo de compromiso
 */
function fnInformacionTipoCompromiso($db, $type = '')
{
    // Obtener informacion del tipo de documento
    $SQL = "SELECT * FROM tb_tipo_compromiso_cat WHERE nu_tipo = '".$type."'";
    $ErrMsg = "No se pudo obtener la información del tipo de compromiso ".$type;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $myrow = DB_fetch_array($TransResult);
    return $myrow;
}

if ($option == 'guardarOperacion') {
    $datosReducciones = $_POST['datosCapturaReducciones'];
    $datosAmpliaciones = $_POST['datosCapturaAmpliaciones'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $estatus = $_POST['estatus'];
    $fechaCaptura = $_POST['fechaCaptura'];
    $typeSuficiencia = 259;
    $fechaInicio = $_POST['fechaInicio'];
    $fechaFinal = $_POST['fechaFinal'];
    $fechaFirma = $_POST['fechaFirma'];
    $description = "Manual";
    $functionSuf = $funcion;
    $justificacion = $_POST['justificacion'];
    $ue = $_POST['ue'];
    $tipoSuficiencia = $_POST['selectTipo'];
    $txtIdCompromiso = $_POST['txtIdCompromiso'];
    $txtProveedor = $_POST['txtProveedor'];
    $txtContratoConvenio = $_POST['txtContratoConvenio'];
    $sn_orderno = 0;
    $requisitionno = 0;
    $nombreSuficiencia = "";
    $suficienciaNueva = 0;
    
    if (empty($transno)) {
        $suficienciaNueva = 1;
        $transno = GetNextTransNo($type, $db);
        $datosPresupuesto['transno'] = $transno;
    }

    if (empty(trim($sn_orderno))) {
        $sn_orderno = 0;
    }

    if (empty($txtIdCompromiso) && $tipoSuficiencia == 'O') {
        // Si es original obtener id de compromiso
        $txtIdCompromiso = GetNextTransNo(290, $db);
        if (strlen($txtIdCompromiso) < 6) {
            // Si es menos a 6 caracteres completar con ceros 0
            $cadena = $txtIdCompromiso;
            for ($i = strlen($txtIdCompromiso); $i < 6; $i++) {
                $cadena = '0'.$cadena;
            }
            $txtIdCompromiso = $cadena;
        }
    }
    
    $fechaInicio = date_create($fechaInicio);
    $fechaInicio = date_format($fechaInicio, 'Y-m-d');

    $fechaFinal = date_create($fechaFinal);
    $fechaFinal = date_format($fechaFinal, 'Y-m-d');

    $fechaFirma = date_create($fechaFirma);
    $fechaFirma = date_format($fechaFirma, 'Y-m-d');

    $SQL = "SELECT nu_type, nu_transno FROM tb_compromiso 
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
        $SQL = "INSERT INTO tb_compromiso
        (dtm_fecha,
        dtm_fecha_inicio,
        dtm_fecha_fin,
        dtm_fecha_firma,
        sn_userid,
        txt_justificacion,
        nu_type,
        nu_transno,
        nu_id_compromiso,
        sn_contrato,
        nu_estatus,
        sn_tagref,
        nu_tipo,
        supplierid,
        sn_funcion_id,
        ln_ue,
        nu_anio_fiscal
        )
        VALUES
        (NOW(), 
        '".$fechaInicio."', 
        '".$fechaFinal."', 
        '".$fechaFirma."', 
        '".$_SESSION['UserID']."', 
        '".$justificacion."', 
        '".$type."', 
        '".$transno."', 
        '".$txtIdCompromiso."',
        '".$txtContratoConvenio."',
        '".$estatus."', 
        '".$tagref."', 
        '".$tipoSuficiencia."',
        '".$txtProveedor."',
        ".$functionSuf.",
        '".$ue."',
        '".$_SESSION['ejercicioFiscal']."'
        )";
        $ErrMsg = "No se pudo almacenar la información";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    } else if ($sqlOperacion == 2) {
        // Actualizar datos
        $SQL = "UPDATE tb_compromiso SET 
        dtm_fecha_inicio = '".$fechaInicio."', 
        dtm_fecha_fin = '".$fechaFinal."', 
        dtm_fecha_firma = '".$fechaFirma."', 
        sn_userid = '".$_SESSION['UserID']."', 
        txt_justificacion = '".$justificacion."', 
        nu_id_compromiso = '".$txtIdCompromiso."',
        sn_contrato = '".$txtContratoConvenio."',
        nu_estatus = '".$estatus."',
        sn_tagref = '".$tagref."',
        nu_tipo = '".$tipoSuficiencia."',
        supplierid = '".$txtProveedor."',
        sn_funcion_id = ".$functionSuf.",
        ln_ue = '".$ue."'
        WHERE 
        nu_type = '".$type."' 
        AND nu_transno = '".$transno."'";
        $ErrMsg = "No se pudo almacenar la información";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }

    // Log en negativo para Original e Incremento, Decremento en positivo
    $operacionLog = -1;
    $ordenOperacion = 'DESC';
    $movimientoTipo = '';
    if ($tipoSuficiencia == 'D') {
        // Decremento
        $operacionLog = 1;
        $ordenOperacion = 'ASC';
        $movimientoTipo = 'Compromiso';
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
        $description = "Compromiso ".$transno." en tramite. Operación ".$tipoSuficiencia;
        $numMes = 1;
        foreach ($dataJsonMeses as $nameMes) {
            $periodo = GetPeriod('15/'.(strlen($numMes) == 1 ? '0'.$numMes : $numMes).'/'.$datosClave['año'], $db);
            $cantidad = $datosClave[$nameMes] * $operacionLog;

            if ($tipoSuficiencia == 'D') {
                // Decremento
                if ($numMes <= date('m')) {
                    // Si es mes menor o igual al actual, empezar del primer mes
                    $ordenOperacion = 'ASC';
                } else {
                    // Si es mayor al actual, empezar con el actual
                    $ordenOperacion = 'DESC';
                }
            }
            
            if (abs($cantidad) != 0) {
                // Si tiene cantidad
                $respuesta = fnInsertPresupuestoLogAcomulado($db, $type, $transno, $tagref, $clave, $periodo, $cantidad, $typeSuficiencia, "", $description, 0, $estatus, 0, $ln_ue, $ordenOperacion, 'disponible', $movimientoTipo, 'Reduccion');
            }
            
            $numMes ++;
        }
    }

    // Guardar número de compromiso en el log presupuestal
    $SQL = "UPDATE chartdetailsbudgetlog SET nu_id_compromiso = '".$txtIdCompromiso."' 
    WHERE type = '".$type."' and transno = '".$transno."'";
    $ErrMsg = "No se actualizó el número de compromiso en el log presupuestal";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    // Obtener informacion del compromiso
    $rowTipo = fnInformacionTipoCompromiso($db, $tipoSuficiencia);

    if ($tipoSuficiencia == 'O') {
        // Original
        $Mensaje = "Se ha guardado exitosamente el Compromiso número ".$txtIdCompromiso." con Folio ".$transno;
    } else {
        // Incremento o Decremento
        $Mensaje = "Se ha guardado exitosamente el ".$rowTipo['sn_nombre']." al Compromiso número ".$txtIdCompromiso." con Folio ".$transno;
    }

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
    $SQL = "SELECT statusid, CONCAT(statusid, ' - ', statusname) as statusname, statusname as statusname2
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
    $datosPresupuesto['transnoNuevo'] = $transnoNuevo;

    $datosPresupuesto['txtIdCompromiso'] = $txtIdCompromiso;

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
