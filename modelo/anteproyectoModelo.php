<?php
/**
 * Anteproyecto Captura
 *
 * @category
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Modelos para las operaciones de Anteproyecto Captura
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
$funcion=2386;
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

if ($option == 'validarAnte') {
    $anio = $_POST['anio'];

    $contenido = '';
    $res = true;

    $SQL = "SELECT tb_ante_principal.nu_anio, tb_ante_principal.nu_transno, tb_botones_status.statusname
    FROM tb_ante_principal
    JOIN tb_botones_status ON tb_botones_status.sn_funcion_id = '".$funcion."' AND tb_botones_status.statusid = tb_ante_principal.nu_estatus
    WHERE tb_ante_principal.nu_estatus = 5 AND tb_ante_principal.nu_anio = '".$anio."'";
    $ErrMsg = "No se obtuvo anteproyecto autorizado";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $myrow = DB_fetch_array($TransResult);
        $res = false;
        $contenido = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ya existe un anteproyecto autorizado para el año '.$anio.', Folio '.$myrow['nu_transno'].'</p>';
    }

    $result = $res;
}

if ($option == 'obtenerPresupuesto') {
    $clavepresupuestal = $_POST['clave'];
    $info = array();
    $res = true;

    $SQL = "SELECT legalbusinessunit.legalid, tags.tagref, chartdetailsbudgetbytag.*
            FROM chartdetailsbudgetbytag
            JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
            JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
            WHERE chartdetailsbudgetbytag.accountcode IN (".$clavepresupuestal.")";
    $ErrMsg = "No se obtuvieron los presupuestos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info2 = array();
        $config = $myrow['idClavePresupuesto'];

        $SQL = "SELECT campoPresupuesto, nombre, orden, tabla, campo, nu_programatica, nu_programatica_orden, nu_economica, nu_economica_orden, nu_administrativa, nu_administrativa_orden, nu_relacion_partida, nu_relacion_partida_orden, sn_clave_corta, nu_clave_corta_orden, sn_clave_larga, nu_clave_larga_orden
        FROM budgetConfigClave
        WHERE idClavePresupuesto = '".$config."'
        ORDER BY orden ASC";
        $resultClave = DB_query($SQL, $db, $ErrMsg);
        while ($rowClave = DB_fetch_array($resultClave)) {
            $arr = array(
                    "nombreCampo" => $rowClave ['campoPresupuesto'],
                    "nombre" => $rowClave ['nombre'],
                    "valor" => $myrow [$rowClave ['campoPresupuesto']],
                    "sn_clave_corta" => $rowClave ['sn_clave_corta'],
                    "nu_clave_corta_orden" => $rowClave ['nu_clave_corta_orden'],
                    "sn_clave_larga" => $rowClave ['sn_clave_larga'],
                    "nu_clave_larga_orden" => $rowClave ['nu_clave_larga_orden'],
                    "nu_tam_est_ejer" => $rowClave ['nu_tam_est_ejer'],
                    "tablaValidar" => $rowClave ['tabla'],
                    "campoValidar" => $rowClave ['campo'],
                    "nu_programatica" => $rowClave ['nu_programatica'],
                    "nu_programatica_orden" => $rowClave ['nu_programatica_orden'],
                    "nu_economica" => $rowClave ['nu_economica'],
                    "nu_economica_orden" => $rowClave ['nu_economica_orden'],
                    "nu_administrativa" => $rowClave ['nu_administrativa'],
                    "nu_administrativa_orden" => $rowClave ['nu_administrativa_orden'],
                    "nu_relacion_partida" => $rowClave ['nu_relacion_partida'],
                    "nu_relacion_partida_orden" => $rowClave ['nu_relacion_partida_orden']
                );

            array_push($info2, $arr);
        }

        $info3 = array();
        $arr = array(
            "Enero" => $myrow ['enero'],
            "Febrero" => $myrow ['febrero'],
            "Marzo" => $myrow ['marzo'],
            "Abril" => $myrow ['abril'],
            "Mayo" => $myrow ['mayo'],
            "Junio" => $myrow ['junio'],
            "Julio" => $myrow ['julio'],
            "Agosto" => $myrow ['agosto'],
            "Septiembre" => $myrow ['septiembre'],
            "Octubre" => $myrow ['octubre'],
            "Noviembre" => $myrow ['noviembre'],
            "Diciembre" => $myrow ['diciembre']
        );
        array_push($info3, $arr);

        if (empty($myrow['txt_descripcion'])) {
            // Si esta vacio
            $myrow['txt_descripcion'] = '';
        }

        $info[] = array(
            'accountcode' => $myrow ['accountcode'],
            'claveInfo' => $info2,
            'mesesInfo' => $info3,
            'totalAnual' => $myrow['original'],
            'justificacion' => $myrow['txt_descripcion']
        );
    }

    if (empty($info)) {
        $Mensaje = "No se encontró la información para la Clave Presupuestal ".$clave;
        $res = false;
    }

    $contenido = array('datos' => $info);
    $result = $res;
}

if ($option == 'obtenerPresupuestosBusqueda') {
    $year = $_POST['year'];
    $year --;

    $info = array();

    $SQL = "
    SELECT
    distinct 
    chartdetailsbudgetbytag.*
    FROM chartdetailsbudgetbytag
    JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
    JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
    WHERE 1 = 1 
    AND chartdetailsbudgetbytag.original <> 0
    AND chartdetailsbudgetbytag.anho = '".$year."'
    AND sec_unegsxuser.userid = '".$_SESSION['UserID']."' 
    AND SUBSTRING(chartdetailsbudgetbytag.partida_esp, 1, 1) IN (SELECT SUBSTRING(sec_capituloxuser.sn_capitulo, 1, 1) FROM sec_capituloxuser WHERE sec_capituloxuser.sn_userid = '".$_SESSION['UserID']."')
    AND chartdetailsbudgetbytag.ln_aux1 IN (SELECT CONCAT(tagref, ue) FROM tb_sec_users_ue WHERE userid = '".$_SESSION['UserID']."')
    AND chartdetailsbudgetbytag.partida_esp IN (SELECT partidacalculada FROM tb_sec_users_partida WHERE userid = '".$_SESSION['UserID']."')
    ORDER BY chartdetailsbudgetbytag.accountcode ASC ";
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

        $info[] = $datos;
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'validarEstructuras') {
    $estProgramatica = $_POST['estProgramatica'];
    $estEconomica = $_POST['estEconomica'];
    $estAdministrativa = $_POST['estAdministrativa'];
    $estPartida = $_POST['estPartida'];
    $mensajeInicial = $_POST['mensajeInicial'];
    $jsonInfoClaves = $_POST['jsonInfoClaves'];

    $contenido = '';
    $res = true;

    // Validar si existe la clave
    $numRegistros = compruebaClaveProgramatica($db, $estProgramatica);
    if ($numRegistros == 0) {
        $res = false;
        $contenido .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$mensajeInicial.' Estructura Programática no encontrada '.$estProgramatica.'</p>';
    }
    // Validar si existe la clave
    $numRegistros = compruebaClaveEconomica($db, $estEconomica);
    if ($numRegistros == 0) {
        $res = false;
        $contenido .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$mensajeInicial.' Estructura Económica no encontrada '.$estEconomica.'</p>';
    }
    // Validar si existe la clave
    $numRegistros = compruebaClaveAdministrativa($db, $estAdministrativa);
    if ($numRegistros == 0) {
        $res = false;
        $contenido .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$mensajeInicial.' Estructura Administrativa no encontrada '.$estAdministrativa.'</p>';
    }
    // Validar si existe la clave
    $numRegistros = compruebaClaveRelacionPpPartida($db, $estPartida);
    if ($numRegistros == 0) {
        $res = false;
        $contenido .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$mensajeInicial.' Relación PP-Partida no encontrada '.$estPartida.'</p>';
    }

    // print_r($jsonInfoClaves);
    $infoNoEncontrada = '';
    foreach ($jsonInfoClaves as $datos) {
        // echo "\n nombreCampo: ".$datos['nombreCampo'];
        if (!empty($datos['tablaValidar'])) {
            // Si tiene tabla para validar
            $SQL = "SELECT ".$datos['campoValidar']." FROM ".$datos['tablaValidar']." WHERE 
            ".$datos['campoValidar']." = '".$datos['valor']."'";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($TransResult) == 0) {
                $res = false;
                if ($infoNoEncontrada == '') {
                    $infoNoEncontrada = $datos['nombre'];
                } else {
                    $infoNoEncontrada .= ', '.$datos['nombre'];
                }
            }
        }
    }
    
    if ($infoNoEncontrada != '') {
        $res = false;
        $contenido .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$mensajeInicial.' no existe la información de: '.$infoNoEncontrada.'</p>';
    }

    $result = $res;
}

if ($option == 'cargarInfoNoCaptura') {
    $type = $_POST['type'];
    $transno = $_POST['transno'];

    // Info de encabezado
    $SQL = "SELECT 
    tb_ante_principal.nu_type,
    tb_ante_principal.nu_transno,
    DATE_FORMAT(tb_ante_principal.dtm_fecha, '%d-%m-%Y') as fecha_captura,
    tb_ante_principal.amt_importe,
    tb_ante_principal.txt_descripcion,
    tb_ante_principal.nu_estatus,
    tb_ante_principal.nu_anio,
    tb_ante_principal.nu_paaas,
    tb_ante_principal.ln_validacion,
    tb_ante_principal.nu_ue,
    tb_ante_principal.nu_clavePresupuesto,
    tb_ante_principal.nu_fase,
    tb_ante_principal.nu_val_justificacion
    FROM tb_ante_principal
    WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'
    ORDER BY tb_ante_principal.nu_transno DESC";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $reponse['type'] = $myrow['nu_type'];
        $reponse['transno'] = $myrow['nu_transno'];
        // $reponse['totalGeneral'] = $myrow['amt_importe'];
        $reponse['fechaCaptura'] = $myrow['fecha_captura'];
        $reponse['usarPaaas'] = $myrow['nu_paaas'];
        $reponse['anio'] = $myrow['nu_anio'];
        $reponse['descripcion'] = $myrow['txt_descripcion'];
        $reponse['estatus'] = $myrow['nu_estatus'];
        $reponse['validacion'] = $myrow['ln_validacion'];
        $reponse['usarUe'] = $myrow['nu_ue'];
        $reponse['configClavePresupuesto'] = $myrow['nu_clavePresupuesto'];
        $reponse['usarSoloUnaFase'] = $myrow['nu_fase'];
        $reponse['validarJustificacion'] = $myrow['nu_val_justificacion'];
    }

    // Info Capitulo
    $infoCapitulo = array();
    $SQL = "SELECT id_mov, sn_capitulo, amt_importe 
    FROM tb_ante_capitulos 
    WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'
    ORDER BY id_mov ASC";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $infoCapitulo[] = array( 'value' => $myrow ['amt_importe'], 'texto' => $myrow ['sn_capitulo'] );
    }

    // Info Autorizada
    $infoAutorizada = array();
    $infoAutorizadaVar = 1;

    // Info Unidad Responsable
    $infoUr = array();
    $SQL = "SELECT id_mov, sn_tagref, sn_capitulo, amt_importe, ln_validacion, nu_estatus, amt_importe_general 
    FROM tb_ante_ur 
    WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'
    AND sn_capitulo IN (SELECT sec_capituloxuser.sn_capitulo FROM sec_capituloxuser WHERE sec_capituloxuser.sn_userid = '".$_SESSION['UserID']."')
    ORDER BY id_mov ASC";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $total = 0;
    $num = 0;
    while ($myrow = DB_fetch_array($TransResult)) {
        $infoUr[] = array( 'value' => $myrow ['amt_importe'], 'texto' => $myrow ['sn_tagref'],
        'value2' => $myrow['sn_capitulo'] );

        $editar = 1;
        if ($myrow['nu_estatus'] == '1') {
            $editar = 0;
            $infoAutorizadaVar = 0;
        }
        $infoAutorizada[] = array( 'ur' => $myrow ['sn_tagref'], 'ue' => '', 'capitulo' => $myrow['sn_capitulo'], 'estatus' => $editar );

        $reponse['validacion'] = $myrow['ln_validacion'];
        // $reponse['estatus'] = $myrow['nu_estatus'];
        $total = $total + $myrow['amt_importe'];
        $num ++;
    }
    if ($num > 0) {
        $reponse['totalGeneral'] = $total;
    }

    // Info Unidad Ejecutora
    $infoUe = array();
    $SQL = "SELECT id_mov, sn_tagref, sn_ue, sn_capitulo, amt_importe, ln_validacion, nu_estatus, amt_importe_general 
    FROM tb_ante_ue 
    WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'
    AND sn_capitulo IN (SELECT sec_capituloxuser.sn_capitulo FROM sec_capituloxuser WHERE sec_capituloxuser.sn_userid = '".$_SESSION['UserID']."')
    AND CONCAT(sn_tagref, sn_ue) IN (SELECT CONCAT(tagref, ue) FROM tb_sec_users_ue WHERE userid = '".$_SESSION['UserID']."') 
    ORDER BY id_mov ASC";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $total = 0;
    $num = 0;
    while ($myrow = DB_fetch_array($TransResult)) {
        $infoUe[] = array( 'value' => $myrow ['amt_importe'], 'texto' => $myrow ['sn_tagref'],
        'value2' => $myrow['sn_ue'], 'capitulo' => $myrow['sn_capitulo'] );

        $editar = 1;
        if ($myrow['nu_estatus'] == '1') {
            $editar = 0;
            $infoAutorizadaVar = 0;
        }
        $infoAutorizada[] = array( 'ur' => $myrow ['sn_tagref'], 'ue' => $myrow['sn_ue'], 'capitulo' => $myrow['sn_capitulo'], 'estatus' => $editar );

        $reponse['validacion'] = $myrow['ln_validacion'];
        // $reponse['estatus'] = $myrow['nu_estatus'];
        $total = $total + $myrow['amt_importe'];
        $num ++;
    }
    if ($num > 0) {
        $reponse['totalGeneral'] = $total;
    }

    // Info Claves Anual
    $infoClaveAnual = array();
    $SQL = "SELECT 
    tb_ante_claves.* 
    FROM tb_ante_claves
    WHERE tb_ante_claves.nu_type = '".$type."' AND tb_ante_claves.nu_transno = '".$transno."'
    AND SUBSTRING(tb_ante_claves.partida_esp, 1, 1) IN (SELECT SUBSTRING(sec_capituloxuser.sn_capitulo, 1, 1) FROM sec_capituloxuser WHERE sec_capituloxuser.sn_userid = '".$_SESSION['UserID']."')
    AND tb_ante_claves.ln_aux1 IN (SELECT CONCAT(tagref, ue) FROM tb_sec_users_ue WHERE userid = '".$_SESSION['UserID']."') 
    AND tb_ante_claves.partida_esp IN (SELECT partidacalculada FROM tb_sec_users_partida WHERE userid = '".$_SESSION['UserID']."')
    ORDER BY tb_ante_claves.budgetid ASC";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        // Obtener registros de la clave separada
        $info = array();
        $config = $myrow['idClavePresupuesto'];

        $SQL = "SELECT campoPresupuesto, nombre, orden, tabla, campo, nu_programatica, nu_programatica_orden, nu_economica, nu_economica_orden, nu_administrativa, nu_administrativa_orden, nu_relacion_partida, nu_relacion_partida_orden, sn_clave_corta, nu_clave_corta_orden, sn_clave_larga, nu_clave_larga_orden
        FROM budgetConfigClave
        WHERE idClavePresupuesto = '".$config."'
        ORDER BY orden ASC";
        $resultClave = DB_query($SQL, $db, $ErrMsg);
        while ($rowClave = DB_fetch_array($resultClave)) {
            $arr = array(
                    "nombreCampo" => $rowClave ['campoPresupuesto'],
                    "nombre" => $rowClave ['nombre'],
                    "valor" => $myrow [$rowClave ['campoPresupuesto']],
                    "sn_clave_corta" => $rowClave ['sn_clave_corta'],
                    "nu_clave_corta_orden" => $rowClave ['nu_clave_corta_orden'],
                    "sn_clave_larga" => $rowClave ['sn_clave_larga'],
                    "nu_clave_larga_orden" => $rowClave ['nu_clave_larga_orden'],
                    "nu_tam_est_ejer" => $rowClave ['nu_tam_est_ejer'],
                    "tablaValidar" => $rowClave ['tabla'],
                    "campoValidar" => $rowClave ['campo'],
                    "nu_programatica" => $rowClave ['nu_programatica'],
                    "nu_programatica_orden" => $rowClave ['nu_programatica_orden'],
                    "nu_economica" => $rowClave ['nu_economica'],
                    "nu_economica_orden" => $rowClave ['nu_economica_orden'],
                    "nu_administrativa" => $rowClave ['nu_administrativa'],
                    "nu_administrativa_orden" => $rowClave ['nu_administrativa_orden'],
                    "nu_relacion_partida" => $rowClave ['nu_relacion_partida'],
                    "nu_relacion_partida_orden" => $rowClave ['nu_relacion_partida_orden']
                );

            array_push($info, $arr);
        }

        $info3 = array();
        $arr = array(
            "Enero" => $myrow ['enero'],
            "Febrero" => $myrow ['febrero'],
            "Marzo" => $myrow ['marzo'],
            "Abril" => $myrow ['abril'],
            "Mayo" => $myrow ['mayo'],
            "Junio" => $myrow ['junio'],
            "Julio" => $myrow ['julio'],
            "Agosto" => $myrow ['agosto'],
            "Septiembre" => $myrow ['septiembre'],
            "Octubre" => $myrow ['octubre'],
            "Noviembre" => $myrow ['noviembre'],
            "Diciembre" => $myrow ['diciembre']
        );
        array_push($info3, $arr);

        $infoClaveAnual[] = array(
            'accountcode' => $myrow ['accountcode'],
            'claveInfo' => $info,
            'mesesInfo' => $info3,
            'totalAnual' => $myrow['original'],
            'justificacion' => $myrow['txt_descripcion']
        );
    }

    if (empty($reponse['totalGeneral'])) {
        // si esta vacio el total general
        $reponse['totalGeneral'] = 0;
    }

    $reponse['datosCapitulos'] = $infoCapitulo;
    $reponse['datosUnidadResponsable'] = $infoUr;
    $reponse['datosUnidadEjecutora'] = $infoUe;
    $reponse['datosClaveAnual'] = $infoClaveAnual;

    $reponse['infoAutorizada'] = $infoAutorizada;
    $reponse['infoAutorizadaVar'] = $infoAutorizadaVar;

    $contenido = $reponse;
    $result = true;
}

/**
 * Función para generar la cadena del CSV a descargar
 * @param  [type] $db              Base de datos
 * @param  [type] $type            Tipo de documento u operación
 * @param  [type] $transno         Folio de la operación
 * @param  [type] $cadenaCSV       Tipo de CSV: 1 - Clave Corta, 2 -  Clave Larga
 * @param  [type] $usarSoloUnaFase Si es solo una fase se agregan menses
 * @return [type]                  Cadena con la información
 */
function fnGenerarCadenaCsv($db, $type, $transno, $cadenaCSV, $usarSoloUnaFase)
{
    // Funcion para generar cadena CSV
    // Ejemplo:
    // = '[{"Id":1,"UserName":"Sam Smith"},{"Id":2,"UserName":"Fred Frankly"},{"Id":1,"UserName":"Zachary Zupers"}]';
    $cadena = "";
    $encabezado = "";
    $numRegistros = 1;

    $SQL = "SELECT 
    tb_ante_claves.* 
    FROM tb_ante_claves
    WHERE tb_ante_claves.nu_type = '".$type."' AND tb_ante_claves.nu_transno = '".$transno."'
    AND SUBSTRING(tb_ante_claves.partida_esp, 1, 1) IN (SELECT SUBSTRING(sec_capituloxuser.sn_capitulo, 1, 1) FROM sec_capituloxuser WHERE sec_capituloxuser.sn_userid = '".$_SESSION['UserID']."')
    AND tb_ante_claves.ln_aux1 IN (SELECT CONCAT(tagref, ue) FROM tb_sec_users_ue WHERE userid = '".$_SESSION['UserID']."')
    AND tb_ante_claves.partida_esp IN (SELECT partidacalculada FROM tb_sec_users_partida WHERE userid = '".$_SESSION['UserID']."')
    ORDER BY tb_ante_claves.budgetid ASC";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        // Obtener registros de la clave separada
        $info = array();
        $config = $myrow['idClavePresupuesto'];

        if ($cadena == '') {
            $cadena .= '{';
        } else {
            $cadena .= ', {';
        }

        $cadenaInfo = '';
        if ($cadenaCSV == 1) {
            // CSV clave corta
            $SQL = "SELECT campoPresupuesto, nombre, nu_clave_corta_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$config."' AND sn_clave_corta = '1' ORDER BY nu_clave_corta_orden ASC ";
            $resultClave = DB_query($SQL, $db, $ErrMsg);
            while ($rowClave = DB_fetch_array($resultClave)) {
                if ($cadenaInfo == '') {
                    $cadenaInfo .= '"'.$rowClave ['nombre'].'" : "'.$myrow [$rowClave ['campoPresupuesto']].'"';
                } else {
                    $cadenaInfo .= ', "'.$rowClave ['nombre'].'" : "'.$myrow [$rowClave ['campoPresupuesto']].'"';
                }

                if ($numRegistros == 1) {
                    // Datos de Encabezado
                    if ($encabezado == '') {
                        $encabezado .= '"'.$rowClave ['nombre'].'" : "'.$rowClave ['nombre'].'"';
                    } else {
                        $encabezado .= ', "'.$rowClave ['nombre'].'" : "'.$rowClave ['nombre'].'"';
                    }
                }
            }
        } else {
            // CSV clave larga
            $SQL = "SELECT campoPresupuesto, nombre, nu_clave_larga_orden FROM budgetConfigClave WHERE idClavePresupuesto = '".$config."' AND sn_clave_larga = '1' ORDER BY nu_clave_larga_orden ASC ";
            $resultClave = DB_query($SQL, $db, $ErrMsg);
            while ($rowClave = DB_fetch_array($resultClave)) {
                if ($cadenaInfo == '') {
                    $cadenaInfo .= '"'.$rowClave ['nombre'].'" : "'.$myrow [$rowClave ['campoPresupuesto']].'"';
                } else {
                    $cadenaInfo .= ', "'.$rowClave ['nombre'].'" : "'.$myrow [$rowClave ['campoPresupuesto']].'"';
                }

                if ($numRegistros == 1) {
                    // Datos de Encabezado
                    if ($encabezado == '') {
                        $encabezado .= '"'.$rowClave ['nombre'].'" : "'.$rowClave ['nombre'].'"';
                    } else {
                        $encabezado .= ', "'.$rowClave ['nombre'].'" : "'.$rowClave ['nombre'].'"';
                    }
                }
            }
        }

        $cadenaInfo .= ', "Total" : "'.$myrow ['original'].'"';
        $encabezado .= ', "Total" : "Total"';

        if ($usarSoloUnaFase == 1) {
            // Solo una fase, agregar meses
            $cadenaInfo .= ', "Enero" : "'.$myrow ['enero'].'"';
            $cadenaInfo .= ', "Febrero" : "'.$myrow ['febrero'].'"';
            $cadenaInfo .= ', "Marzo" : "'.$myrow ['marzo'].'"';
            $cadenaInfo .= ', "Abril" : "'.$myrow ['abril'].'"';
            $cadenaInfo .= ', "Mayo" : "'.$myrow ['mayo'].'"';
            $cadenaInfo .= ', "Junio" : "'.$myrow ['junio'].'"';
            $cadenaInfo .= ', "Julio" : "'.$myrow ['julio'].'"';
            $cadenaInfo .= ', "Agosto" : "'.$myrow ['agosto'].'"';
            $cadenaInfo .= ', "Septiembre" : "'.$myrow ['septiembre'].'"';
            $cadenaInfo .= ', "Octubre" : "'.$myrow ['octubre'].'"';
            $cadenaInfo .= ', "Noviembre" : "'.$myrow ['noviembre'].'"';
            $cadenaInfo .= ', "Diciembre" : "'.$myrow ['diciembre'].'"';
            if ($numRegistros == 1) {
                // Datos de Encabezado
                $encabezado .= ', "Enero" : "Enero"';
                $encabezado .= ', "Febrero" : "Febrero"';
                $encabezado .= ', "Marzo" : "Marzo"';
                $encabezado .= ', "Abril" : "Abril"';
                $encabezado .= ', "Mayo" : "Mayo"';
                $encabezado .= ', "Junio" : "Junio"';
                $encabezado .= ', "Julio" : "Julio"';
                $encabezado .= ', "Agosto" : "Agosto"';
                $encabezado .= ', "Septiembre" : "Septiembre"';
                $encabezado .= ', "Octubre" : "Octubre"';
                $encabezado .= ', "Noviembre" : "Noviembre"';
                $encabezado .= ', "Diciembre" : "Diciembre"';
            }
        }

        $cadena .= $cadenaInfo.'}';

        $numRegistros ++;
    }

    $cadena = '[{'.$encabezado.'}, '.$cadena.']';

    return $cadena;
}

if ($option == 'guardarInformacion') {
    $estatus = $_POST['estatus'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $totalGeneral = $_POST['totalGeneral'];
    $fechaCaptura = $_POST['fechaCaptura'];
    $usarPaaas = $_POST['usarPaaas'];
    $anio = $_POST['anio'];
    $descripcion = $_POST['descripcion'];
    $validacion = $_POST['validacion'];
    $usarUe = $_POST['usarUe'];
    $configClavePresupuesto = $_POST['configClavePresupuesto'];
    $jsonCapitulos = $_POST['jsonCapitulos'];
    $jsonUr = $_POST['jsonUr'];
    $jsonUe = $_POST['jsonUe'];
    $jsonPartidas = $_POST['jsonPartidas'];
    $jsonInfoAnual = $_POST['jsonInfoAnual'];
    $usarSoloUnaFase = $_POST['usarSoloUnaFase'];
    $validarJustificacion = $_POST['validarJustificacion'];
    $generarCsv = $_POST['generarCsv'];
    $infoAutorizada = $_POST['infoAutorizada'];

    $ErrMsg = "";

    // echo "\n jsonCapitulos: \n";
    // print_r($jsonCapitulos);
    // echo "\n jsonUr: \n";
    // print_r($jsonUr);
    // echo "\n jsonUe: \n";
    // print_r($jsonUe);
    // echo "\n jsonPartidas: \n";
    // print_r($jsonPartidas);

    $cadenaCapitulos = "";
    $cadenaCapitulosCompleto = "";
    foreach ($jsonCapitulos as $datos) {
        // echo "\n value: ".$datos['value'];
        if ($cadenaCapitulos == "") {
            $cadenaCapitulos .= "'".substr($datos['value'], 0, 1)."'";
            $cadenaCapitulosCompleto .= "'".$datos['value']."'";
        } else {
            $cadenaCapitulos .= ", '".substr($datos['value'], 0, 1)."'";
            $cadenaCapitulosCompleto .= ", '".$datos['value']."'";
        }
    }
    // echo "\n cadenaCapitulos: ".$cadenaCapitulos;
    // echo "\n cadenaCapitulosCompleto: ".$cadenaCapitulosCompleto;

    $cadenaUr = "";
    foreach ($jsonUr as $datos) {
        // echo "\n value: ".$datos['value'];
        if ($cadenaUr == "") {
            $cadenaUr .= "'".$datos['value']."'";
        } else {
            $cadenaUr .= ", '".$datos['value']."'";
        }
    }
    // echo "\n cadenaUr: ".$cadenaUr;

    $cadenaUrUe = "";
    foreach ($jsonUe as $datos) {
        // echo "\n value: ".$datos['value'];
        if ($cadenaUrUe == "") {
            $cadenaUrUe .= "'".$datos['value'].$datos['value2']."'";
        } else {
            $cadenaUrUe .= ", '".$datos['value'].$datos['value2']."'";
        }
    }
    // echo "\n cadenaUrUe: ".$cadenaUrUe;

    $cadenaPartida = "";
    foreach ($jsonPartidas as $datos) {
        // echo "\n value: ".$datos['value'];
        if ($cadenaPartida == "") {
            $cadenaPartida .= "'".$datos['value']."'";
        } else {
            $cadenaPartida .= ", '".$datos['value']."'";
        }
    }
    // echo "\n cadenaPartida: ".$cadenaPartida;

    $cadenaCapitulosAutorizada = "";
    $cadenaCapitulosCompletoAutorizada = "";
    $cadenaUrAutorizada = "";
    $cadenaUrUeAutorizada = "";
    $cadenaUrUeCapAutorizada = "";
    foreach ($infoAutorizada as $datos) {
        if ($datos['estatus'] == '1') {
            // echo "\n capitulo: ".$datos['capitulo']." \n";
            // echo "\n ue: ".$datos['ue']." \n";
            if ($cadenaCapitulosAutorizada == "") {
                $cadenaCapitulosAutorizada .= "'".substr($datos['capitulo'], 0, 1)."'";
                $cadenaCapitulosCompletoAutorizada .= "'".$datos['capitulo']."'";
            } else {
                $cadenaCapitulosAutorizada .= ", '".substr($datos['capitulo'], 0, 1)."'";
                $cadenaCapitulosCompletoAutorizada .= ", '".$datos['capitulo']."'";
            }

            if ($cadenaUrAutorizada == "") {
                $cadenaUrAutorizada .= "'".$datos['ur']."'";
            } else {
                $cadenaUrAutorizada .= ", '".$datos['ur']."'";
            }

            if ($cadenaUrUeAutorizada == "") {
                $cadenaUrUeAutorizada .= "'".$datos['ur'].$datos['ue']."'";
            } else {
                $cadenaUrUeAutorizada .= ", '".$datos['ur'].$datos['ue']."'";
            }

            if ($cadenaUrUeCapAutorizada == "") {
                $cadenaUrUeCapAutorizada .= "'".$datos['ur'].$datos['ue'].$datos['capitulo']."'";
            } else {
                $cadenaUrUeCapAutorizada .= ", '".$datos['ur'].$datos['ue'].$datos['capitulo']."'";
            }
        }
    }

    if ($cadenaUrUeCapAutorizada == "") {
        $cadenaUrUeCapAutorizada = "''";
    }

    // echo "\n cadenaCapitulosAutorizada: ".$cadenaCapitulosAutorizada;
    // echo "\n cadenaCapitulosCompletoAutorizada: ".$cadenaCapitulosCompletoAutorizada;
    // echo "\n cadenaUrAutorizada: ".$cadenaUrAutorizada;
    // echo "\n cadenaUrUeAutorizada: ".$cadenaUrUeAutorizada;
    // echo "\n cadenaUrUeCapAutorizada: ".$cadenaUrUeCapAutorizada;

    // echo "\n\n";
    // print_r($infoAutorizada);
    // exit();

    if (empty($usarPaaas)) {
        $usarPaaas = 0;
    }

    if (empty($usarUe)) {
        $usarUe = 0;
    }

    if (empty($configClavePresupuesto)) {
        $configClavePresupuesto = 0;
    }

    if (empty($transno)) {
        // Nuevo registro
        $transno = GetNextTransNo($type, $db);
        
        $SQL = "INSERT INTO tb_ante_principal 
        (nu_type, nu_transno, dtm_fecha, amt_importe, sn_userid, txt_descripcion, nu_estatus, nu_anio, nu_paaas, ln_validacion, nu_ue, nu_clavePresupuesto, nu_fase, nu_val_justificacion) 
        VALUES 
        ('".$type."', '".$transno."', NOW(), '".$totalGeneral."', '".$_SESSION['UserID']."', '".$descripcion."', '".$estatus."', '".$anio."', '".$usarPaaas."', '".$validacion."', '".$usarUe."', '".$configClavePresupuesto."', '".$usarSoloUnaFase."', '".$validarJustificacion."')";
        $ErrMsg = "No se agrego registro de anteproyecto encabezado";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $Mensaje = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Se guardo la información con Folio '.$transno.'</p>';
    } else {
        // Actualizar registro
        $SQL = "UPDATE tb_ante_principal
        SET 
        dtm_fecha = NOW(),
        amt_importe = '".$totalGeneral."',
        sn_userid = '".$_SESSION['UserID']."',
        txt_descripcion = '".$descripcion."',
        nu_estatus = '".$estatus."',
        nu_anio = '".$anio."',
        nu_paaas = '".$usarPaaas."',
        ln_validacion = '".$validacion."',
        nu_ue = '".$usarUe."',
        nu_clavePresupuesto = '".$configClavePresupuesto."',
        nu_fase = '".$usarSoloUnaFase."',
        nu_val_justificacion = '".$validarJustificacion."'
        WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'
        ";
        $ErrMsg = "No se actualizo registro de anteproyecto encabezado";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $Mensaje = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Se actualizó la información con Folio '.$transno.'</p>';
    }

    if ($generarCsv != '0') {
        if ($usarSoloUnaFase == 1) {
            // Solo una fase
            $estatus = 3;
        } else {
            // Dos fases
            $estatus = 2;

            // Validar si ya se genero el CSV anual
            $tabla = "";
            $sqlWhere = "";
            if ($usarUe == 1) {
                // Registros por UE
                $tabla = "tb_ante_ue";
                $sqlWhere = " AND CONCAT(sn_tagref, sn_ue) IN (".$cadenaUrUe.") ";
                // SELECT CONCAT(tagref, ue) FROM tb_sec_users_ue WHERE userid = '".$_SESSION['UserID']."'
            } else {
                // Registros por UR
                $tabla = "tb_ante_ur";
            }

            $SQL = "SELECT distinct nu_estatus FROM ".$tabla." WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'
            AND sn_capitulo IN (".$cadenaCapitulosCompleto.")".$sqlWhere;
            // SELECT sec_capituloxuser.sn_capitulo FROM sec_capituloxuser WHERE sec_capituloxuser.sn_userid = '".$_SESSION['UserID']."'
            $result = DB_query($SQL, $db, $ErrMsg);
            while ($row = DB_fetch_array($result)) {
                // Obtener estatus
                if ($row['nu_estatus'] == 2) {
                    // si ya se genero el anual, generar el calendarizado
                    $estatus = 3;
                    $usarSoloUnaFase = 1;
                }
            }
        }
    }

    // Borrar registros anteriores del capitulo
    $SQL = "DELETE FROM tb_ante_capitulos WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'
    AND sn_capitulo IN (".$cadenaCapitulosCompleto.")";
    // SELECT sec_capituloxuser.sn_capitulo FROM sec_capituloxuser WHERE sec_capituloxuser.sn_userid = '".$_SESSION['UserID']."'
    $ErrMsg = "No se eliminaron los registros del capítulo";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    foreach ($jsonCapitulos as $datos) {
        // Agregar información de los cap¡tulos
        if (!empty($datos['select'])) {
            $SQL = "INSERT INTO tb_ante_capitulos 
            (nu_type, nu_transno, dtm_fecha, amt_importe, sn_userid, sn_capitulo)
            VALUES 
            ('".$type."', '".$transno."', NOW(), '".$datos['select']."', '".$_SESSION['UserID']."', '".$datos['value']."')
            ";
            $ErrMsg = "No se agrego registro de capítulo ".$datos['value'];
            $TransResult = DB_query($SQL, $db, $ErrMsg);
        }
    }

    // Borrar registros anteriores de unidad responsable
    $SQL = "DELETE FROM tb_ante_ur WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'
    AND sn_capitulo IN (".$cadenaCapitulosCompleto.")
    AND CONCAT(sn_tagref, sn_capitulo) NOT IN (".$cadenaUrUeCapAutorizada.")";
    // SELECT sec_capituloxuser.sn_capitulo FROM sec_capituloxuser WHERE sec_capituloxuser.sn_userid = '".$_SESSION['UserID']."'
    $ErrMsg = "No se eliminaron los registros de unidad responsable";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    if ($usarUe == 0) {
        // Usar a nivel UR
        foreach ($jsonUr as $datos) {
            // Agregar información de ur, capitulo
            $jsonUeCapitulo = $datos['Capitulo'];
            foreach ($jsonUeCapitulo as $datosCap) {
                if (!empty($datosCap['select']) && strpos($cadenaUrUeCapAutorizada, $datos['value'].$datosCap['value']) === false) {
                    // Si no esta vacio o no es cero
                    $SQL = "INSERT INTO tb_ante_ur 
                    (nu_type, nu_transno, dtm_fecha, amt_importe, sn_userid, sn_tagref, sn_capitulo, ln_validacion, nu_estatus, amt_importe_general)
                    VALUES 
                    ('".$type."', '".$transno."', NOW(), '".$datosCap['select']."', '".$_SESSION['UserID']."', '".$datos['value']."', '".$datosCap['value']."', '".$validacion."', '".$estatus."', '".$totalGeneral."')
                    ";
                    $ErrMsg = "No se agrego registro de unidad ejecutora ".$datos['value'];
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                }
            }
        }
    }

    // Borrar registros anteriores de unidad ejecutora
    $SQL = "DELETE FROM tb_ante_ue WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'
    AND sn_capitulo IN (".$cadenaCapitulosCompleto.")
    AND CONCAT(sn_tagref, sn_ue) IN (".$cadenaUrUe.")
    AND CONCAT(sn_tagref, sn_ue, sn_capitulo) NOT  IN (".$cadenaUrUeCapAutorizada.")";
    // SELECT sec_capituloxuser.sn_capitulo FROM sec_capituloxuser WHERE sec_capituloxuser.sn_userid = '".$_SESSION['UserID']."'
    // SELECT CONCAT(tagref, ue) FROM tb_sec_users_ue WHERE userid = '".$_SESSION['UserID']."'
    $ErrMsg = "No se eliminaron los registros de unidad ejecutora";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if ($usarUe == 1) {
        // Usar a nivel UE
        foreach ($jsonUe as $datos) {
            // Agregar información de ur, ue, capitulo
            $jsonUeCapitulo = $datos['Capitulo'];
            foreach ($jsonUeCapitulo as $datosCap) {
                if (!empty($datosCap['select']) && strpos($cadenaUrUeCapAutorizada, $datos['value'].$datos['value2'].$datosCap['value']) === false) {
                    // Si no esta vacio o no es cero
                    $SQL = "INSERT INTO tb_ante_ue 
                    (nu_type, nu_transno, dtm_fecha, amt_importe, sn_userid, sn_tagref, sn_ue, sn_capitulo, ln_validacion, nu_estatus, amt_importe_general)
                    VALUES 
                    ('".$type."', '".$transno."', NOW(), '".$datosCap['select']."', '".$_SESSION['UserID']."', '".$datos['value']."', '".$datos['value2']."', '".$datosCap['value']."', '".$validacion."', '".$estatus."', '".$totalGeneral."')
                    ";
                    $ErrMsg = "No se agrego registro de unidad ejecutora ".$datos['value'];
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                }
            }
        }
    }

    // Borrar registros anteriores de claves anuales
    $SQL = "DELETE FROM tb_ante_claves WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'
    AND SUBSTRING(tb_ante_claves.partida_esp, 1, 1) IN (".$cadenaCapitulos.")
    AND tb_ante_claves.ln_aux1 IN (".$cadenaUrUe.")
    AND tb_ante_claves.partida_esp IN (".$cadenaPartida.") ";
    // SELECT SUBSTRING(sec_capituloxuser.sn_capitulo, 1, 1) FROM sec_capituloxuser WHERE sec_capituloxuser.sn_userid = '".$_SESSION['UserID']."'
    // SELECT CONCAT(tagref, ue) FROM tb_sec_users_ue WHERE userid = '".$_SESSION['UserID']."'
    // SELECT partidacalculada FROM tb_sec_users_partida WHERE userid = '".$_SESSION['UserID']."'
    $ErrMsg = "No se eliminaron los registros de las claves anuales";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    foreach ($jsonInfoAnual as $datos) {
        $campos = "";
        $valores = "";
        $claveInfo = $datos['claveInfo'];
        foreach ($claveInfo as $datosClave) {
            if ($campos == '') {
                $campos .= "".$datosClave['nombreCampo'];
                $valores .= "'".$datosClave['valor']."'";
            } else {
                $campos .= ", ".$datosClave['nombreCampo'];
                $valores .= ", '".$datosClave['valor']."'";
            }
        }

        $mesesInfo = $datos['mesesInfo'];
        foreach ($mesesInfo as $datosMeses) {
            // Valores meses
            $campos .= ", enero, febrero, marzo, abril, mayo, junio, julio, agosto, septiembre, octubre, noviembre, diciembre";
            $valores .= ", '".$datosMeses['Enero']."'";
            $valores .= ", '".$datosMeses['Febrero']."'";
            $valores .= ", '".$datosMeses['Marzo']."'";
            $valores .= ", '".$datosMeses['Abril']."'";
            $valores .= ", '".$datosMeses['Mayo']."'";
            $valores .= ", '".$datosMeses['Junio']."'";
            $valores .= ", '".$datosMeses['Julio']."'";
            $valores .= ", '".$datosMeses['Agosto']."'";
            $valores .= ", '".$datosMeses['Septiembre']."'";
            $valores .= ", '".$datosMeses['Octubre']."'";
            $valores .= ", '".$datosMeses['Noviembre']."'";
            $valores .= ", '".$datosMeses['Diciembre']."'";
        }

        $SQL = "INSERT INTO tb_ante_claves (accountcode, txt_descripcion, budget, original, nu_type, nu_transno, idClavePresupuesto, txt_userid, ".$campos.", fecha_captura, fecha_sistema)
        VALUES ('".$datos['accountcode']."', '".$datos['justificacion']."', '".$datos['totalAnual']."', '".$datos['totalAnual']."', '".$type."', '".$transno."', '".$configClavePresupuesto."', '".$_SESSION['UserID']."', ".$valores.", NOW(), NOW())";
        $ErrMsg = "No se agrego registro de clave anual ".$datos['accountcode'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }

    // Obtener Total del anteproyecto
    $totalGeneral = 0;
    $SQL = "";
    if ($usarUe == 1) {
        // Usar a nivel UE
        $SQL = "SELECT SUM(amt_importe) as total FROM tb_ante_ue WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'";
    } else {
        // Usar a nivel UR
        $SQL = "SELECT SUM(amt_importe) as total FROM tb_ante_ur WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'";
    }
    $result = DB_query($SQL, $db, $ErrMsg);
    while ($row = DB_fetch_array($result)) {
        $totalGeneral = $row['total'];
    }

    if (empty($totalGeneral)) {
        $totalGeneral = 0;
    }

    // Actualizar total general
    $SQL = "UPDATE tb_ante_principal SET amt_importe = '".$totalGeneral."' WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'";
    $result = DB_query($SQL, $db, $ErrMsg);

    // Generar cadena para CSV (Clave Corta y Larga)
    $nombreCsv = "";
    $cadenaCSV = "";
    if ($generarCsv != '0') {
        $cadenaCSV = fnGenerarCadenaCsv($db, $type, $transno, $generarCsv, $usarSoloUnaFase);
        if ($generarCsv == 1) {
            // CSV clave corta
            $nombreCsv = "CSVCveCorta";
        } else {
            // CSV clave larga
            $nombreCsv = "CSVCveLarga";
        }

        if ($usarSoloUnaFase == 1) {
            // Solo una fase
            $estatus = 3;
        } else {
            // Dos fases
            $estatus = 2;
        }

        // Actualizar registro
        $SQL = "UPDATE tb_ante_principal SET nu_estatus = '1'
        WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'
        ";
        $ErrMsg = "No se actualizo registro de anteproyecto encabezado";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }
    
    $datosPresupuesto['transno'] = $transno;
    $datosPresupuesto['validacion'] = $validacion;
    $datosPresupuesto['estatus'] = $estatus;
    $datosPresupuesto['cadenaCSV'] = $cadenaCSV;
    $datosPresupuesto['nombreCsv'] = $nombreCsv;

    $contenido = array('datos' => $datosPresupuesto);
    $result = true;
}

if ($option == 'mostrarConfiguracionClave') {
    // Datos clave
    $config = $_POST['config'];

    $info = array();

    $SQL = "SELECT campoPresupuesto, nombre, orden, tabla, campo, nu_programatica, nu_programatica_orden, nu_economica, nu_economica_orden, nu_administrativa, nu_administrativa_orden, nu_relacion_partida, nu_relacion_partida_orden, sn_clave_corta, nu_clave_corta_orden, sn_clave_larga, nu_clave_larga_orden
    FROM budgetConfigClave
    WHERE idClavePresupuesto = '".$config."'
    ORDER BY orden ASC";
    $resultClave = DB_query($SQL, $db, $ErrMsg);
    while ($rowClave = DB_fetch_array($resultClave)) {
        $arr = array(
                "nombreCampo" => $rowClave ['campoPresupuesto'],
                "nombre" => $rowClave ['nombre'],
                "valor" => '',
                "sn_clave_corta" => $rowClave ['sn_clave_corta'],
                "nu_clave_corta_orden" => $rowClave ['nu_clave_corta_orden'],
                "sn_clave_larga" => $rowClave ['sn_clave_larga'],
                "nu_clave_larga_orden" => $rowClave ['nu_clave_larga_orden'],
                "nu_tam_est_ejer" => $rowClave ['nu_tam_est_ejer'],
                "tablaValidar" => $rowClave ['tabla'],
                "campoValidar" => $rowClave ['campo'],
                "nu_programatica" => $rowClave ['nu_programatica'],
                "nu_programatica_orden" => $rowClave ['nu_programatica_orden'],
                "nu_economica" => $rowClave ['nu_economica'],
                "nu_economica_orden" => $rowClave ['nu_economica_orden'],
                "nu_administrativa" => $rowClave ['nu_administrativa'],
                "nu_administrativa_orden" => $rowClave ['nu_administrativa_orden'],
                "nu_relacion_partida" => $rowClave ['nu_relacion_partida'],
                "nu_relacion_partida_orden" => $rowClave ['nu_relacion_partida_orden']
            );

        array_push($info, $arr);
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarDatosGenerales') {
    $info = array();
    // Datos Capitulo
    $infoCapitulo = array();
    $SQL = "SELECT distinct tb_cat_partidaspresupuestales_capitulo.ccapmiles as value, tb_cat_partidaspresupuestales_capitulo.descripcion as texto 
    FROM tb_cat_partidaspresupuestales_capitulo 
    JOIN sec_capituloxuser ON sec_capituloxuser.sn_capitulo = tb_cat_partidaspresupuestales_capitulo.ccapmiles
    WHERE tb_cat_partidaspresupuestales_capitulo.activo = 1 
    AND sec_capituloxuser.sn_userid = '".$_SESSION['UserID']."'
    ORDER BY CAST(tb_cat_partidaspresupuestales_capitulo.ccapmiles AS SIGNED) ASC";
    $ErrMsg = "No se obtuvo información del capítulo";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $infoCapitulo[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'], 'select' => 0 );
    }

    // Datos Unidad Responsable
    $infoUR = array();
    $SQL = "SELECT distinct  t.tagref as value, t.tagdescription as texto
    FROM sec_unegsxuser u, tags t
    WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "'
    ORDER BY t.tagref ASC
    ";
    $ErrMsg = "No se obtuvo información de las unidades responsable";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $infoUR[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'],
        'value2' => $myrow ['ccapmiles'], 'texto2' => $myrow ['descripcion'], 'select' => 0,
        'Capitulo' => $infoCapitulo );
    }

    // Datos Unidad Ejecutora
    $infoUE = array();
    $SQL = "SELECT distinct  t.tagref as value, t.tagdescription as texto, tb_cat_unidades_ejecutoras.ue, tb_cat_unidades_ejecutoras.desc_ue
    FROM sec_unegsxuser u, tags t
    JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ur = t.tagref AND tb_cat_unidades_ejecutoras.active = '1'
    JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = tb_cat_unidades_ejecutoras.ur AND tb_sec_users_ue.ue = tb_cat_unidades_ejecutoras.ue
    WHERE u.tagref = t.tagref 
    and u.userid = '" . $_SESSION['UserID'] . "'
    AND tb_sec_users_ue.userid = '" . $_SESSION['UserID'] . "'
    ORDER BY t.tagref , tb_cat_unidades_ejecutoras.ue ASC";
    $ErrMsg = "No se obtuvo información de las unidades ejecutoras";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $infoUE[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'],
        'value2' => $myrow ['ue'], 'texto2' => $myrow ['desc_ue'], 'select' => 0,
        'Capitulo' => $infoCapitulo );
    }

    // Datos Partida Especififca
    $infoPartidas = array();
    $SQL = "SELECT distinct  tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada as value, tb_cat_partidaspresupuestales_partidaespecifica.descripcion as texto
    FROM tb_cat_partidaspresupuestales_partidaespecifica
    JOIN tb_sec_users_partida ON tb_sec_users_partida.partidacalculada = tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada
    WHERE tb_cat_partidaspresupuestales_partidaespecifica.activo = 1
    AND tb_sec_users_partida.userid = '" . $_SESSION['UserID'] . "'
    ORDER BY value ASC, texto ASC";
    $ErrMsg = "No se obtuvo información de las partidas especififcas";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $infoPartidas[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $info['infoCapitulo'] = $infoCapitulo;
    $info['infoUR'] = $infoUR;
    $info['infoUE'] = $infoUE;
    $info['infoPartidas'] = $infoPartidas;

    // Permisos para modificar información de los paneles
    $info['perEncabezado'] = Havepermission($_SESSION['UserID'], 2425, $db);
    $info['perTechos'] = Havepermission($_SESSION['UserID'], 2426, $db);
    $info['perClaves'] = Havepermission($_SESSION['UserID'], 2427, $db);

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
            ORDER BY tb_botones_status.statusid ASC
            ";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        // Quitar espacios
        $nombre = str_replace(" ", "", $myrow ['namebutton']);
        // Quitar puntos
        $nombre = str_replace(".", "", $nombre);
        $info[] = array(
            'statusid' => $myrow ['statusid'],
            'statusname' => $myrow ['statusname'],
            'namebutton' => $myrow ['namebutton'],
            'namebutton2' => $nombre,
            'functionid' => $myrow ['functionid'],
            'clases' => $myrow ['clases']
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
