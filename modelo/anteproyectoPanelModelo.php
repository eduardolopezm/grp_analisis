<?php
/**
 * Panel Anteproyecto
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 04/06/2018
 * Fecha Modificación: 04/06/2018
 * Modelos para las operaciones del panel de anteproyecto
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
include $PathPrefix."includes/SecurityUrl.php";
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

$option = $_POST['option'];

if ($option == 'rechazarDetalleAnte') {
    $dataJsonNoCapturaSeleccionados = $_POST['dataJsonNoCapturaSeleccionados'];

    $mensajeInfo = '';
    $info = array();
    $result = true;
    $mensajeCorrecto = '';
    $type = '';
    $transno = '';

    foreach ($dataJsonNoCapturaSeleccionados as $datosClave) {
        // Obtener tipo CSV
        $usarUe = 0;
        $estatusActual = 1;
        $config = 0;
        $SQL = "SELECT nu_ue, nu_estatus, nu_clavePresupuesto FROM tb_ante_principal WHERE nu_type = '".$datosClave ['type']."' AND nu_transno = '".$datosClave ['transno']."'";
        $ErrMsg = "No se Actualizaron Registros del Folio ".$datosClave ['transno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $usarUe = $myrow['nu_ue'];
            $estatusActual = $myrow['nu_estatus'];
            $config = $myrow['nu_clavePresupuesto'];
        }

        $type = $datosClave ['type'];
        $transno = $datosClave ['transno'];

        if ($estatusActual == 5) {
            // Ya se encuentra autorizado el presupuesto
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Ya se ha autorizado el folio '.$datosClave ['transno'].'</p>';
            $result = false;
        } else if ($errores == 0) {
            // Realizar operaciones
            if ($usarUe == 1) {
                // Registros por UE
                $tabla = "tb_ante_ue";
                $SQL = "UPDATE tb_ante_ue
                JOIN tb_botones_status ON tb_botones_status.statusid = tb_ante_ue.nu_estatus AND tb_botones_status.sn_funcion_id = '2386'
                SET tb_ante_ue.nu_estatus = tb_botones_status.sn_estatus_anterior
                WHERE
                tb_ante_ue.nu_type = '".$datosClave ['type']."'
                AND tb_ante_ue.nu_transno = '".$datosClave ['transno']."'
                AND tb_ante_ue.sn_capitulo = '".$datosClave ['capitulo']."'
                AND tb_ante_ue.sn_tagref = '".$datosClave ['ur']."'
                AND tb_ante_ue.sn_ue = '".$datosClave ['ue']."'";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
            } else {
                // Registros por UR
                $tabla = "tb_ante_ur";
                $SQL = "UPDATE tb_ante_ur
                JOIN tb_botones_status ON tb_botones_status.statusid = tb_ante_ur.nu_estatus AND tb_botones_status.sn_funcion_id = '2386'
                SET tb_ante_ur.nu_estatus = tb_botones_status.sn_estatus_anterior
                WHERE
                tb_ante_ur.nu_type = '".$datosClave ['type']."'
                AND tb_ante_ur.nu_transno = '".$datosClave ['transno']."'
                AND tb_ante_ur.sn_capitulo = '".$datosClave ['capitulo']."'
                AND tb_ante_ur.sn_tagref = '".$datosClave ['ur']."'";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
            }
        }

        $mensajeCorrecto = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p> Actualización para el Folio '.$datosClave['transno'].' correcta</p></div>';

        $info[] = array(
            'transno' => $datosClave ['transno']
        );
    }

    if ($result) {
        // Si es proceso correcto
        $mensajeInfo = $mensajeCorrecto;
    }

    $reponse['type'] = $type;
    $reponse['transno'] = $transno;
    $reponse['estatus'] = $statusid;
    $reponse['mensaje'] = $mensajeInfo;
    $reponse['datos'] = $info;

    $contenido = $reponse;
}

if ($option == 'detalleFolio') {
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $info = array();

    $usarUe = 0;
    $SQL = "SELECT nu_ue FROM tb_ante_principal WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'";
    $ErrMsg = "No se Actualizaron Registros del Folio ".$transno;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $usarUe = $myrow['nu_ue'];
    }

    $tabla = "";
    $sqlJoinUe = "";
    if ($usarUe == 1) {
        // Registros por UE
        $tabla = "tb_ante_ue";
        $cp = "IFNULL(tabla.sn_ue,'') AS sn_ue,";
        $sqlJoinUe = "JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = tabla.sn_tagref AND tb_sec_users_ue.ue = tabla.sn_ue AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'";
    } else {
        // Registros por UR
        $tabla = "tb_ante_ur";
        $cp = "'' as sn_ue,";
    }

    $SQL = "SELECT 
    IFNULL((tabla.amt_importe),0) AS amt_importe,
    tabla.sn_capitulo,
    ".$cp."
    tabla.sn_tagref,
    tb_cat_partidaspresupuestales_capitulo.descripcion,
    tb_botones_status.statusname,
    DATE_FORMAT(tabla.dtm_fecha, '%d-%m-%Y') as fechaMod,
    www_users.realname,
    tabla.nu_estatus
    FROM ".$tabla." tabla
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tabla.sn_tagref AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    ".$sqlJoinUe."
    JOIN sec_capituloxuser ON sec_capituloxuser.sn_capitulo = tabla.sn_capitulo AND sec_capituloxuser.sn_userid = '".$_SESSION['UserID']."'
    JOIN tb_cat_partidaspresupuestales_capitulo ON tb_cat_partidaspresupuestales_capitulo.ccapmiles = tabla.sn_capitulo
    JOIN tb_botones_status ON tb_botones_status.sn_funcion_id = '".$funcion."' AND tb_botones_status.statusid = tabla.nu_estatus
    JOIN www_users ON www_users.userid = tabla.sn_userid
    WHERE tabla.nu_type = '".$type."' AND tabla.nu_transno = '".$transno."'
    ORDER BY sn_tagref, sn_ue, sn_capitulo, descripcion ASC
    ";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'ur' => $myrow['sn_tagref'],
            'ue' => $myrow['sn_ue'],
            'importe' => $myrow ['amt_importe'],
            'capitulo' => $myrow ['sn_capitulo'],
            'capituloNombre' => $myrow ['sn_capitulo'].' - '.$myrow ['descripcion'],
            'estatus' => $myrow ['nu_estatus'],
            'estatusNombre' => $myrow ['statusname'],
            'fechaMod' => $myrow ['fechaMod'],
            'usuarioNombre' => $myrow ['realname'],
            'type' => $type,
            'transno' => $transno
        );
    }

    // Variable para permiso de autorizacion
    $permisoDetalle = Havepermission($_SESSION ['UserID'], 2496, $db);

    $contenido = array('datos' => $info, 'detalle' => $permisoDetalle);
    $result = true;
}

if ($option == 'autorizarEstatus') {
    $dataJsonNoCapturaSeleccionados = $_POST['dataJsonNoCapturaSeleccionados'];
    $statusid = $_POST['statusid'];

    $mensajeInfo = '';
    $info = array();
    $datosMeses = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');

    foreach ($dataJsonNoCapturaSeleccionados as $datosClave) {
        // Obtener tipo CSV
        $usarUe = 0;
        $estatusActual = 1;
        $config = 0;
        $SQL = "SELECT nu_ue, nu_estatus, nu_clavePresupuesto FROM tb_ante_principal WHERE nu_type = '".$datosClave ['type']."' AND nu_transno = '".$datosClave ['transno']."'";
        $ErrMsg = "No se Actualizaron Registros del Folio ".$datosClave ['transno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $usarUe = $myrow['nu_ue'];
            $estatusActual = $myrow['nu_estatus'];
            $config = $myrow['nu_clavePresupuesto'];
        }

        $tabla = "";
        if ($usarUe == 1) {
            // Registros por UE
            $tabla = "tb_ante_ue";
            $cp = "IFNULL(tabla.sn_ue,'') AS sn_ue,";
        } else {
            // Registros por UR
            $tabla = "tb_ante_ur";
            $cp = "'' as sn_ue,";
        }

        $SQL = "SELECT 
        IFNULL((tabla.amt_importe),0) AS amt_importe,
        tabla.sn_capitulo,
        ".$cp."
        tabla.sn_tagref,
        tb_cat_partidaspresupuestales_capitulo.descripcion,
        DATE_FORMAT(tabla.dtm_fecha, '%d-%m-%Y') as fechaMod,
        tabla.nu_estatus
        FROM ".$tabla." tabla
        JOIN tb_cat_partidaspresupuestales_capitulo ON tb_cat_partidaspresupuestales_capitulo.ccapmiles = tabla.sn_capitulo
        WHERE tabla.nu_type = '".$datosClave ['type']."' AND tabla.nu_transno = '".$datosClave ['transno']."'
        ORDER BY sn_tagref, sn_ue, sn_capitulo, descripcion ASC
        ";
        $ErrMsg = "No se obtuvieron los botones para el proceso";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            if ($myrow['nu_estatus'] != 4) {
                // No puede ser autorizado
                $errores = 1;
                if ($usarUe == 1) {
                    // Registros por UE
                    $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> UR: '.$myrow['sn_tagref'].', UE: '.$myrow['sn_ue'].', Capítulo: '.$myrow['sn_capitulo'].' - '.$myrow['descripcion'].' no se ha autorizado para el folio '.$datosClave ['transno'].'</p>';
                } else {
                    // Registros por UR
                    $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> UR: '.$myrow['sn_tagref'].', Capítulo: '.$myrow['sn_capitulo'].' - '.$myrow['descripcion'].' no se ha autorizado para el folio '.$datosClave ['transno'].'</p>';
                }
            }
        }

        $arreglo_cuentaspresupuesto= array(); // arreglo para guardar las cuentas contables asociadas el tipo del movimiento
        $consulta = "SELECT gllink_presupuestalingreso,
                        gllink_presupuestalingresoEjecutar,
                        gllink_presupuestalingresoModificado,
                        gllink_presupuestalingresoDevengado,
                        gllink_presupuestalingresoRecaudado,
                        gllink_presupuestalegreso, 
                        gllink_presupuestalegresoEjercer, 
                        gllink_presupuestalegresoModificado,
                        gllink_presupuestalegresocomprometido, 
                        gllink_presupuestalegresodevengado, 
                        gllink_presupuestalegresoejercido,
                        gllink_presupuestalegresopagado
                    FROM companies
                    ORDER BY coycode
                    LIMIT 1";
        $resultado = DB_query($consulta, $db);
        if ($renglon = DB_fetch_array($resultado)) {
            $arreglo_cuentaspresupuesto["INGRESO_APROBADO"]= $renglon["gllink_presupuestalingreso"];
            $arreglo_cuentaspresupuesto["INGRESO_EJECUTAR"]= $renglon["gllink_presupuestalingresoEjecutar"];
            $arreglo_cuentaspresupuesto["INGRESO_DEVENGADO"]= $renglon["gllink_presupuestalingresoDevengado"];
            $arreglo_cuentaspresupuesto["INGRESO_RECAUDADO"]= $renglon["gllink_presupuestalingresoRecaudado"];
            $arreglo_cuentaspresupuesto["INGRESO_MODIFICADO"]= $renglon["gllink_presupuestalingresoModificado"];
            $arreglo_cuentaspresupuesto["APROBADO"]= $renglon["gllink_presupuestalegreso"];
            $arreglo_cuentaspresupuesto["POREJERCER"]= $renglon["gllink_presupuestalegresoEjercer"];
            $arreglo_cuentaspresupuesto["MODIFICADO"]= $renglon["gllink_presupuestalegresoModificado"];
            $arreglo_cuentaspresupuesto["COMPROMETIDO"]= $renglon["gllink_presupuestalegresocomprometido"];
            $arreglo_cuentaspresupuesto["DEVENGADO"]= $renglon["gllink_presupuestalegresodevengado"];
            $arreglo_cuentaspresupuesto["EJERCIDO"]= $renglon["gllink_presupuestalegresoejercido"];
            $arreglo_cuentaspresupuesto["PAGADO"]= $renglon["gllink_presupuestalegresopagado"];
        } else {
            $errores = 1;
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> No existe configuración en preferencias de empresa</p>';
        }

        // Validar que las claves que se van a agregar no existan en la tabla del presupuesto
        $SQL = "SELECT 
        tb_ante_claves.accountcode
        FROM tb_ante_claves
        JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = tb_ante_claves.accountcode
        WHERE tb_ante_claves.nu_type = '".$datosClave ['type']."' AND tb_ante_claves.nu_transno = '".$datosClave ['transno']."'";
        $ErrMsg = "No se validaron las claves del anteproyecto con claves en el sistema";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) > 0) {
            // claves repetidas en para agregan
            $errores = 1;
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Existen '.DB_num_rows($TransResult).' claves del anteproyecto '.$datosClave ['transno'].' que ya se encuentran en el sistema</p>';
        }

        if ($estatusActual == 5) {
            // Ya se encuentra autorizado el presupuesto
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Ya se ha autorizado el folio '.$datosClave ['transno'].'</p>';
        } else if ($errores == 0) {
            // Realizar operaciones
            $infoConfiguracion = array();
            $SQL = "SELECT campoPresupuesto, nombre, orden, tabla, campo, nu_programatica, nu_programatica_orden, nu_economica, nu_economica_orden, nu_administrativa, nu_administrativa_orden, nu_relacion_partida, nu_relacion_partida_orden, sn_clave_corta, nu_clave_corta_orden, sn_clave_larga, nu_clave_larga_orden
            FROM budgetConfigClave
            WHERE idClavePresupuesto = '".$config."'
            ORDER BY orden ASC";
            $resultClave = DB_query($SQL, $db, $ErrMsg);
            while ($rowClave = DB_fetch_array($resultClave)) {
                $arr = array(
                        "nombreCampo" => $rowClave ['campoPresupuesto'],
                        "nombre" => $rowClave ['nombre']
                    );

                array_push($infoConfiguracion, $arr);
            }

            $sqlCampos = "fecha_modificacion, fecha_captura, fecha_sistema, accountcode, budget, original, idClavePresupuesto, sn_inicial, txt_userid ";
            foreach ($infoConfiguracion as $datosConfig) {
                // Campos del presupuesto
                $sqlCampos .= ', '.$datosConfig['nombreCampo'];
            }

            for ($numMes=0; $numMes < sizeof($datosMeses); $numMes++) {
                // Datos de los meses
                $sqlCampos .= ', '.$datosMeses [$numMes];
            }

            $SQL = "SELECT * FROM tb_ante_claves WHERE nu_type = '".$datosClave ['type']."' AND nu_transno = '".$datosClave ['transno']."'";
            $ErrMsg = "No se obtuvo información para mostrar mensaje del proceso ";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            
            $SQLPrincipal = "INSERT INTO chartdetailsbudgetbytag (".$sqlCampos.")
            VALUES ";
            // echo "\n SQLPrincipal: ".$SQLPrincipal;
            $SQLPrincipalDatos = "";

            $accountAbono = 'APROBADO';
            $accountCargo = 'POREJERCER';
            $tipoMovPre = '251';
            
            $SQLLog = "INSERT INTO chartdetailsbudgetlog (
            userid,
            qty,
            cvefrom,
            type,
            transno,
            account,
            tagref,
            period,
            partida_esp,
            sn_disponible,
            numero_oficio,
            datemov,
            fecha_captura,
            dtm_aplicacion,
            nu_tipo_movimiento,
            ln_ue
            )
            VALUES ";
            // echo "\n SQLLog: ".$SQLLog;
            $SQLLogDatos = "";

            // Array para folios agrupados (UR-UE)
            $infoFolios = array();

            while ($myrow = DB_fetch_array($TransResult)) {
                // Registros Tabla principal
                if ($SQLPrincipalDatos != '') {
                    $SQLPrincipalDatos .= ', ';
                }

                $SQLPrincipalDatos .= "(NOW(), NOW(), NOW(),'".$myrow['accountcode']."', '".$myrow['budget']."', '".$myrow['original']."',
                '".$myrow['idClavePresupuesto']."', '1', '".$_SESSION['UserID']."'";

                $tagref = "";
                $partida = "";
                $anio = "";
                $disponible = 1;
                $noOficio = "";
                $datoUE = "";
                $totalPoliza = $myrow ['original'];
                foreach ($infoConfiguracion as $datosConfig) {
                    // Campos del presupuesto
                    $SQLPrincipalDatos .= ", '".$myrow[$datosConfig['nombreCampo']]."'";

                    if ($datosConfig['nombreCampo'] == 'tagref') {
                        $tagref = $myrow[$datosConfig['nombreCampo']];
                    }
                    if ($datosConfig['nombreCampo'] == 'anho') {
                        $anio = $myrow[$datosConfig['nombreCampo']];
                    }
                    if ($datosConfig['nombreCampo'] == 'partida_esp') {
                        $partida = $myrow[$datosConfig['nombreCampo']];
                    }
                    if ($datosConfig['nombreCampo'] == 'ln_aux1') {
                        $datoUE = $myrow[$datosConfig['nombreCampo']];
                    }
                }

                $datoUE = str_replace($tagref, "", $datoUE);

                // Ver si existe folio para movimientos
                $folioPolizaUe = 0;
                foreach ($infoFolios as $datosFolios) {
                    // Recorrer para ver si exi
                    if ($datosFolios['tagref'] == $tagref && $datosFolios['ue'] == $datoUE) {
                        // Si existe
                        $type = $datosFolios['type'];
                        $transno = $datosFolios['transno'];
                        $folioPolizaUe = $datosFolios['folioPolizaUe'];
                    }
                }
                if ($folioPolizaUe == 0) {
                    // Si no existe folio sacar folio
                    // $transno = GetNextTransNo($type, $db);
                    // Folio de la poliza por unidad ejecutora
                    $folioPolizaUe = fnObtenerFolioUeGeneral($db, $tagref, $datoUE, $datosClave ['type']);
                    $infoFolios[] = array(
                        'tagref' => $tagref,
                        'ue' => $datoUE,
                        'type' => $datosClave ['type'],
                        'transno' => $datosClave ['transno'],
                        'folioPolizaUe' => $folioPolizaUe
                    );
                }

                for ($numMes=0; $numMes < sizeof($datosMeses); $numMes++) {
                    // Datos de los meses
                    $cantidad = $myrow[$datosMeses [$numMes]];
                    $SQLPrincipalDatos .= ", '".$cantidad."'";

                    $mes = $numMes+1; // Se agrega 1 ya que inicia en 0
                    if (strlen($mes) == "1") {
                        $mes = '0'.$mes;
                    }
                    
                    $period = fnGetPeriodSinValidar('15/'.$mes.'/'.$anio, $db);

                    if ($SQLLogDatos != '') {
                        $SQLLogDatos .= ', ';
                    }

                    $SQLLogDatos .= "
                    (
                    '".$_SESSION['UserID']."',
                    '".$cantidad."',
                    '".$myrow['accountcode']."',
                    '".$datosClave ['type']."',
                    '".$datosClave ['transno']."',
                    '".$arreglo_cuentaspresupuesto[$accountCargo]."',
                    '".$tagref."',        
                    '".$period."',      
                    '".$partida."',        
                    '".$disponible."',
                    '".$noOficio."',
                    NOW(),
                    NOW(),
                    NOW(),
                    '".$tipoMovPre."',
                    '".$datoUE."'
                    )
                    ";
                }

                $SQLPrincipalDatos .= ")";

                $fechapoliza = date('Y-m-d');
                $period = GetPeriod(date('d/m/Y'), $db);
                $referencia = "Presupuesto Aprobado";
                // $datoUE = fnObtenerUnidadEjecutoraClave($db, $claveCreada);
                $res = GeneraMovimientoContablePresupuesto($datosClave ['type'], $accountAbono, $accountCargo, $datosClave ['transno'], $period, $totalPoliza, $tagref, $fechapoliza, $myrow['accountcode'], $referencia, $db, false, '', '', '', $datoUE, 1, 0, $folioPolizaUe);
            }

            // echo "\n SQLLogDatos: ".$SQLLogDatos;
            $TransResult = DB_query($SQLPrincipal.$SQLPrincipalDatos, $db, $ErrMsg);
            $TransResult = DB_query($SQLLog.$SQLLogDatos, $db, $ErrMsg);

            $SQL = "UPDATE tb_ante_principal SET nu_estatus = '".$statusid."'
            WHERE nu_type = '".$datosClave ['type']."' AND nu_transno = '".$datosClave ['transno']."'";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $SQL = "UPDATE tb_ante_ur SET nu_estatus = '".$statusid."'
            WHERE nu_type = '".$datosClave ['type']."' AND nu_transno = '".$datosClave ['transno']."'";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $SQL = "UPDATE tb_ante_ue SET nu_estatus = '".$statusid."'
            WHERE nu_type = '".$datosClave ['type']."' AND nu_transno = '".$datosClave ['transno']."'";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            // Mensaje Configurado
            $SQL = "SELECT sn_mensaje_opcional, sn_mensaje_opcional2 FROM tb_botones_status WHERE sn_funcion_id in (".$funcion.") AND statusid = '".$statusid."'";
            $ErrMsg = "No se obtuvo información para mostrar mensaje del proceso ";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            $msjConfigurado = "";
            while ($myrow = DB_fetch_array($TransResult)) {
                if ($statusid == '99') {
                    $msjConfigurado = $myrow['sn_mensaje_opcional2'];
                } else {
                    $msjConfigurado = $myrow['sn_mensaje_opcional'];
                }
            }

            if (empty($msjConfigurado)) {
                $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Se ha autorizado el folio '.$datosClave['transno'].'</p>';
            } else {
                $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> '.str_replace("XXX", $datosClave ['transno'], $msjConfigurado).'</p>';
            }
        }

        $info[] = array(
            'transno' => $datosClave ['transno']
        );
    }

    $reponse['estatus'] = $statusid;
    $reponse['mensaje'] = $mensajeInfo;
    $reponse['datos'] = $info;

    $contenido = $reponse;
    $result = true;
}

if ($option == 'actualizarEstatus') {
    $statusid = $_POST['estatus'];
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
    $jsonInfoAnual = $_POST['jsonInfoAnual'];
    $usarSoloUnaFase = $_POST['usarSoloUnaFase'];
    $validarJustificacion = $_POST['validarJustificacion'];
    $generarCsv = $_POST['generarCsv'];

    $mensajeInfo = '';
    $info = array();

    $datosClave ['type'] = $type;
    $datosClave ['transno'] = $transno;

    $statusActualizacion = $statusid;
    $errores = 0;

    $tabla = "";
    $sqlWhere = "";
    if ($usarUe == 1) {
        // Registros por UE
        $tabla = "tb_ante_ue";
        $sqlWhere = " AND CONCAT(sn_tagref, sn_ue) IN (SELECT CONCAT(tagref, ue) FROM tb_sec_users_ue WHERE userid = '".$_SESSION['UserID']."') ";
    } else {
        // Registros por UR
        $tabla = "tb_ante_ur";
    }

    $estatusRegistro = '';
    $SQL = "SELECT distinct nu_estatus FROM ".$tabla." WHERE nu_type = '".$datosClave ['type']."' AND nu_transno = '".$datosClave ['transno']."'
    AND sn_capitulo IN (SELECT sec_capituloxuser.sn_capitulo FROM sec_capituloxuser WHERE sec_capituloxuser.sn_userid = '".$_SESSION['UserID']."')".$sqlWhere;
    $result = DB_query($SQL, $db, $ErrMsg);
    while ($row = DB_fetch_array($result)) {
        // Obtener estatus
        $estatusRegistro = $row['nu_estatus'];
    }

    if ($estatusRegistro == 4) {
        // Si tiene errores
        $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Actualización para el Folio '.$datosClave['transno'].' no es posible en el estatus actual</p>';
        $statusActualizacion = $estatusRegistro;
    } else {
        // Realizar operación
        if ($statusid == '99') {
            // Obtener estatus anterior al actual
            $SQL = "SELECT sn_estatus_anterior FROM tb_botones_status WHERE sn_funcion_id in (".$funcion.") AND statusid = '".$estatusRegistro."'";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            while ($myrow = DB_fetch_array($TransResult)) {
                $statusActualizacion = $myrow['sn_estatus_anterior'];
            }

            if ($usarSoloUnaFase == 1 && $statusActualizacion == 2) {
                // Solo una fase, regresar a captura
                $statusActualizacion = 1;
            }
        }

        $SQL = "UPDATE ".$tabla." as tabla
        SET tabla.nu_estatus = '".$statusActualizacion."'
        WHERE tabla.nu_type = '".$datosClave ['type']."' AND tabla.nu_transno = '".$datosClave ['transno']."'
        AND tabla.sn_capitulo IN (SELECT sec_capituloxuser.sn_capitulo FROM sec_capituloxuser WHERE sec_capituloxuser.sn_userid = '".$_SESSION['UserID']."')".$sqlWhere;
        $ErrMsg = "No se Actualizaron Registros del No. Captura ".$datosClave ['transno'];
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $info[] = array(
            'transno' => $datosClave ['transno']
        );

        // Mensaje Configurado
        $SQL = "SELECT sn_mensaje_opcional, sn_mensaje_opcional2 FROM tb_botones_status WHERE sn_funcion_id in (".$funcion.") AND statusid = '".$statusActualizacion."'";
        $ErrMsg = "No se obtuvo información para mostrar mensaje del proceso ";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $msjConfigurado = "";
        while ($myrow = DB_fetch_array($TransResult)) {
            if ($statusid == '99') {
                $msjConfigurado = $myrow['sn_mensaje_opcional2'];
            } else {
                $msjConfigurado = $myrow['sn_mensaje_opcional'];
            }
        }

        if (empty($msjConfigurado)) {
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Actualización para el folio '.$datosClave['transno'].'</p>';
        } else {
            $mensajeInfo .= '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> '.str_replace("XXX", $datosClave ['transno'], $msjConfigurado).'</p>';
        }
    }

    $reponse['estatus'] = $statusActualizacion;
    $reponse['mensaje'] = $mensajeInfo;
    $reponse['datos'] = $info;

    $contenido = $reponse;
    $result = true;
}

if ($option == 'obtenerInformacion') {
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $fechaDesde = $_POST['fechaDesde'];
    $fechaHasta = $_POST['fechaHasta'];
    $ue = $_POST['ue'];
    $tipoSuficiencia = $_POST['tipoSuficiencia'];
    $folio = $_POST['folio'];

    $sqlWhere = "";

    if (!empty($legalid)) {
        // $sqlWhere .= " AND tags.legalid IN (".$legalid.") ";
    }

    if (!empty($tagref) and $tagref != '-1') {
        // $sqlWhere .= " AND tb_ante_principal.sn_tagref IN (".$tagref.") ";
    }

    if (!empty($fechaDesde) && !empty($fechaHasta)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND tb_ante_principal.dtm_fecha between '".$fechaDesde." 00:00:00' AND '".$fechaHasta." 23:59:59' ";
    } elseif (!empty($fechaDesde)) {
        $fechaDesde = date_create($fechaDesde);
        $fechaDesde = date_format($fechaDesde, 'Y-m-d');

        $sqlWhere .= " AND tb_ante_principal.dtm_fecha >= '".$fechaDesde." 00:00:00' ";
    } elseif (!empty($fechaHasta)) {
        $fechaHasta = date_create($fechaHasta);
        $fechaHasta = date_format($fechaHasta, 'Y-m-d');

        $sqlWhere .= " AND tb_ante_principal.dtm_fecha <= '".$fechaHasta." 23:59:59' ";
    }

    if ($ue != '') {
        // $sqlWhere .= " AND tb_ante_principal.ln_ue IN (".$ue.") ";
    }

    if ($folio != '') {
        $sqlWhere .= " AND tb_ante_principal.nu_transno = '".$folio."' ";
    }

    $sqlWhere .= " AND tb_ante_principal.nu_anio = '".$_SESSION['ejercicioFiscal']."' ";

    $info = array();
    $SQL = "
    SELECT 
    tb_ante_principal.nu_type,
    tb_ante_principal.nu_transno,
    DATE_FORMAT(tb_ante_principal.dtm_fecha, '%d-%m-%Y') as fecha_captura,
    tb_ante_principal.amt_importe,
    tb_ante_principal.txt_descripcion,
    tb_ante_principal.nu_estatus,
    CONCAT(tb_ante_principal.sn_userid, ' - ', www_users.realname) as realname,
    tb_ante_principal.ln_validacion,
    tb_botones_status.statusname
    FROM tb_ante_principal
    JOIN www_users ON www_users.userid = tb_ante_principal.sn_userid
    LEFT JOIN tb_botones_status ON tb_botones_status.sn_funcion_id = '".$funcion."' AND tb_botones_status.statusid = tb_ante_principal.nu_estatus
    WHERE 1 = 1 ".$sqlWhere."
    ORDER BY tb_ante_principal.nu_transno DESC
    ";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $operacion = "";
        $seleccionar = "";

        if (1 == 1) {
            // Si no es 0 = Cancelado, 4 = Autorizado
            $seleccionar = '<input type="checkbox" id="checkbox_'.$myrow ['nu_transno'].'" name="checkbox_'.$myrow ['nu_transno'].'" title="Seleccionar" value="'.$myrow ['statusid'].'" onchange="fnValidarProcesoCambiarEstatus()" />';
        }

        $urlGeneral = "&transno=>" . $myrow['nu_transno'] . "&type=>" . $myrow['nu_type'];
        $enc = new Encryption;
        $url = $enc->encode($urlGeneral);
        $liga= "URL=" . $url;
        $operacion = '<a type="button" id="btnAbrirDetalle_'.$myrow['nu_transno'].'" name="btnAbrirDetalle_'.$myrow['nu_transno'].'" href="anteproyecto.php?'.$liga.'" title="Ver Anteproyecto" style="color: blue;">'.$myrow ['nu_transno'].'</a>'; // target="_blank"
        $detalle = '<a type="button" id="btnAbrirDetalleModal_'.$myrow['nu_transno'].'" name="btnAbrirDetalleModal_'.$myrow['nu_transno'].'" onclick="fnVerDetalleModal(\''.$myrow['nu_type'].'\', \''.$myrow['nu_transno'].'\')" title="Detalle Anteproyecto" style="color: blue;">Detalle</a>';

        $info[] = array(
           // 'sel' => $seleccionar,
            'id1' =>false,
            'nu_type' => $myrow ['nu_type'],
            'nu_transno' => $myrow ['nu_transno'],
            'operacion' => $operacion,
            'detalle' => $detalle,
            'total' => ($myrow ['amt_importe'] != "" ? abs(number_format($myrow ['amt_importe'], $_SESSION['DecimalPlaces'], '.', '')) : 0),
            'total2' => ($myrow ['amt_importe'] != "" ? abs(number_format($myrow ['amt_importe'], $_SESSION['DecimalPlaces'], '.', '')) : 0),
            'sn_description' => $myrow['txt_descripcion'],
            'realname' => $myrow ['realname'],
            'fecha_captura' => $myrow ['fecha_captura'],
            'statusid' => $myrow['nu_estatus'],
            'statusname' => $myrow['statusname'],
            'validacion' => $myrow['ln_validacion']
        );
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    // $columnasNombres .= "{ name: 'sel', type: 'string' },";
    /*0*/  $columnasNombres .= "{ name: 'id1', type: 'bool'},";
    /*1*/  $columnasNombres .= "{ name: 'nu_transno', type: 'string' },";
    /*2*/  $columnasNombres .= "{ name: 'operacion', type: 'string' },";
    /*3*/  $columnasNombres .= "{ name: 'detalle', type: 'string' },";
    /*4*/  $columnasNombres .= "{ name: 'statusname', type: 'string' },";
    /*5*/  $columnasNombres .= "{ name: 'fecha_captura', type: 'string' },";
    /*6*/  $columnasNombres .= "{ name: 'sn_description', type: 'string' },";
    /*7*/  $columnasNombres .= "{ name: 'realname', type: 'string' },";
    /*8*/  $columnasNombres .= "{ name: 'total', type: 'string' },";
    /*9*/  $columnasNombres .= "{ name: 'total2', type: 'string' },";
    /*10*/  $columnasNombres .= "{ name: 'nu_type', type: 'string' },";
    /*11*/  $columnasNombres .= "{ name: 'validacion', type: 'string' },";
    /*12*/  $columnasNombres .= "{ name: 'statusid', type: 'string' }";
    /*0*/  $columnasNombres .= "]";

    // Columnas para el GRID
    $colResumenTotal= ", aggregates: [{'<b>Total</b>' :".
                            "function (aggregatedValue, currentValue) {".
                                "var total = currentValue;".
                                "return aggregatedValue + total;".
                            "}".
                        "}] ";
    $columnasNombresGrid .= "[";
    // $columnasNombresGrid .= " { text: 'Sel', datafield: 'sel', width: '3%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: '', datafield: 'id1', width: '3%', editable: true, cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'nu_transno', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'operacion', width: '10%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Detalle', datafield: 'detalle', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'statusname', width: '12%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Modificación', datafield: 'fecha_captura', width: '10%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Justificación', datafield: 'sn_description', width: '40%', editable: false, cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Usuario', datafield: 'realname', width: '20%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Monto Total', datafield: 'total', width: '18%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false".$colResumenTotal." },";
    $columnasNombresGrid .= " { text: 'Monto Total', datafield: 'total2', width: '18%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: true },";
    $columnasNombresGrid .= " { text: 'nu_type', datafield: 'nu_type', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'validacion', datafield: 'validacion', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'statusid', datafield: 'statusid', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true }";
    $columnasNombresGrid .= "]";
    
    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
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

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
