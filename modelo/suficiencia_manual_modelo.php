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
$funcion=2302;
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
    //Ya se agrego en la funcion lo del año fiscal
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

    $info = array();
    $SQL = "
    SELECT 
    distinct chartdetailsbudgetlog.cvefrom,
    CASE WHEN chartdetailsbudgetlog.qty < 0 THEN 'Reduccion' ELSE 'Ampliacion' END as tipoMovimiento
    FROM chartdetailsbudgetlog
    WHERE chartdetailsbudgetlog.type = '".$type."'
    AND chartdetailsbudgetlog.transno = '".$transno."'
    AND chartdetailsbudgetlog.nu_tipo_movimiento = '263'
    AND chartdetailsbudgetlog.qty != 0
    AND chartdetailsbudgetlog.qty < 0
    ";
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
        /*echo "Info Datos: \n";
        var_dump($infoDatos);
        echo "\n";*/
        $info[] = $infoDatos;
    }

    //Obtener nombre de estatus
    $statusname = "";
    $estatus = "";
    $legalid = "";
    $tagref = "";
    $justificacion = "";
    $ln_ue = "";
    $SQL = "SELECT legalbusinessunit.legalid, tags.tagref, chartdetailsbudgetlog.estatus, tb_botones_status.statusname, tb_suficiencias.sn_description, tb_suficiencias.ln_ue
            FROM chartdetailsbudgetlog
            JOIN tags ON tags.tagref = chartdetailsbudgetlog.tagref
            JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
            JOIN tb_suficiencias ON tb_suficiencias.nu_type = chartdetailsbudgetlog.type AND tb_suficiencias.nu_transno = chartdetailsbudgetlog.transno
            LEFT JOIN tb_botones_status ON tb_botones_status.statusid = chartdetailsbudgetlog.estatus AND tb_botones_status.sn_funcion_id = '".$funcion."'
            WHERE 
            chartdetailsbudgetlog.type = '".$type."'
            AND chartdetailsbudgetlog.transno = '".$transno."'
            LIMIT 1";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $legalid = $myrow['legalid'];
        $tagref = $myrow['tagref'];
        $estatus = $myrow['estatus'];
        $statusname = $myrow['statusname'];
        $justificacion = $myrow['sn_description'];
        $ln_ue = $myrow['ln_ue'];
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
    $reponse['fechaCaptura'] = date('d-m-Y');
    $reponse['justificacion'] = $justificacion;

    $contenido = $reponse;//array('datos' => $info);
    $result = true;
}

if ($option == 'guardarSuficiencia') {
    $datosReducciones = $_POST['datosCapturaReducciones'];
    $datosAmpliaciones = $_POST['datosCapturaAmpliaciones'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $estatus = $_POST['estatus'];
    $fechaCaptura = $_POST['fechaCaptura'];
    $typeSuficiencia = 263;
    $description = "Manual";
    $functionSuf = $funcion;
    $suficienciaCancelada = $_POST['suficienciaCancelada'];
    $justificacion = $_POST['justificacion'];
    $ue = $_POST['ue'];
    $sn_orderno = 0;
    $requisitionno = 0;
    $nombreSuficiencia = "";
    $suficienciaNueva = 0;
    
    /*
     * CAMBIAR EL ESTATUS POR 4 Y VERIFICAR EN LOS DISTINTOS INSERTS
     * PARA VER EN DONDE PUEDE AFECTAR Y DE AHI VER QUE CAMBIE EL ESTATUS
     * A AUTORIZADO
     */
    

    if (empty($transno)) {
        $suficienciaNueva = 1;
        $transno = GetNextTransNo($type, $db);
        $datosPresupuesto['transno'] = $transno;
    }

    $tipoSuficiencia = 2;
    $SQL = "SELECT tb_suficiencias.nu_estatus, tb_suficiencias.nu_tipo, tb_suficiencias.sn_description, tb_suficiencias.sn_funcion_id, tb_suficiencias.nu_estatus, tb_suficiencias.sn_tagref, tb_suficiencias.sn_orderno, purchorders.requisitionno, tb_suficiencias.ln_ue, tb_suficiencias_cat.sn_nombre
    FROM tb_suficiencias 
    LEFT JOIN purchorders ON purchorders.orderno = tb_suficiencias.sn_orderno
    LEFT JOIN tb_suficiencias_cat ON tb_suficiencias_cat.nu_tipo = tb_suficiencias.nu_tipo
    WHERE tb_suficiencias.nu_type = '".$type."' and tb_suficiencias.nu_transno = '".$transno."'";
    $ErrMsg = "No se obtuvo el nombre del Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if ($myrow = DB_fetch_array($TransResult)) {
        $tipoSuficiencia = $myrow['nu_tipo'];
        $sn_orderno = $myrow['sn_orderno'];
        $requisitionno = $myrow['requisitionno'];
        $nombreSuficiencia = $myrow['sn_nombre'];

        if ($tipoSuficiencia == 1 || $tipoSuficiencia == 3) {
            // Datos Automatica y Manual derivada de Automatica
            $description = $myrow['sn_description'];
            $functionSuf = $myrow['sn_funcion_id'];
            $estatus = $myrow['nu_estatus'];
            $tagref = $myrow['sn_tagref'];
        }
    }

    if (empty(trim($sn_orderno))) {
        $sn_orderno = 0;
    }

    if ($tipoSuficiencia == 2) {
        // Si es manual poner la justificación
        $description = $justificacion;
    }

    // Agregar Datos Suficiencia
    fnAgregarSuficienciaGeneral($db, $type, $transno, $description, $estatus, $tagref, $tipoSuficiencia, $functionSuf, $sn_orderno, $ue);

    if (empty($tagref) or $tagref == '-1' or $tagref == '0') {
        $SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription 
                FROM sec_unegsxuser u, tags t 
                WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "'  
                ORDER BY t.tagref LIMIT 1 ";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $tagref = $myrow ['tagref'];
        }
    }

    if ($suficienciaCancelada != 1) {
        // Actualizar registros solo cuando no se va cancelar
        foreach ($datosReducciones as $datosClave) {
            $clave = $datosClave['accountcode'];
            $numMes = 1;
            foreach ($dataJsonMeses as $nameMes) {
                $periodo = GetPeriod('15/'.(strlen($numMes) == 1 ? '0'.$numMes : $numMes).'/'.$datosClave['año'], $db);
                $cantidad = $datosClave[$nameMes] * -1;
                $disponible = fnInfoPresupuesto($db, $clave, $periodo);
                foreach ($disponible as $dispo) {
                    if ($dispo[$nameMes] >= abs($cantidad)) {
                        // Solo registros si es Manual
                        fnAgregarPresupuestoGeneral($db, $type, $transno, $tagref, $clave, $periodo, $cantidad, $estatus, $datosClave['tipoAfectacion'], 'Reduccion', $datosClave['partida_esp'], $typeSuficiencia, $typeSuficiencia);
                    } else if (abs($cantidad) > 0) {
                        $result = false;
                        $Mensaje .= '<br>Sin disponible para el mes de '.$nameMes;
                    }
                }
                //echo "\n nameMes: ".$nameMes." - numMes: ".$numMes." - periodo: ".$periodo." - cantidad: ".$cantidad." - result: ".$result;
                $numMes ++;
            }
        }
    }
    
    if ($suficienciaNueva == 1) {
        // Mensaje SP nueva
        $Mensaje = "Se ha agregado exitosamente la suficiencia Presupuestal ".$nombreSuficiencia." ".$transno.".";
    } else {
        // Mensaje SP actualizada
        $Mensaje = "Se ha actualizado exitosamente la suficiencia Presupuestal ".$nombreSuficiencia." ".$transno.".";
    }

    if (empty($fechaCaptura)) {
        $fechaCaptura = date('d-m-Y');//'00-00-0000';
    }

    if ($tipoSuficiencia == 1 || $tipoSuficiencia == 3) {
        // Borrar registros en 0
        $SQL = "DELETE FROM chartdetailsbudgetlog 
                WHERE 
                chartdetailsbudgetlog.type = '".$type."' 
                AND chartdetailsbudgetlog.transno = '".$transno."'
                AND chartdetailsbudgetlog.qty = 0 ";
        $ErrMsg = "No se Actualizaron Registros en Generales";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }

    if ($tipoSuficiencia == 2 || $tipoSuficiencia == 3) {
        // Actualizacion Datos Generales
        $SQL = "UPDATE chartdetailsbudgetlog 
                SET 
                chartdetailsbudgetlog.sn_disponible = '0', 
                chartdetailsbudgetlog.tagref = '".$tagref."',
                chartdetailsbudgetlog.fecha_captura = NOW(),
                chartdetailsbudgetlog.sn_funcion_id = '".$functionSuf."',
                chartdetailsbudgetlog.txt_justificacion = '".$justificacion."'
                WHERE 
                chartdetailsbudgetlog.type = '".$type."' 
                AND chartdetailsbudgetlog.transno = '".$transno."'";
        $ErrMsg = "No se Actualizaron Registros en Generales";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    } else {
        // Actualizacion Justificación
        $SQL = "UPDATE chartdetailsbudgetlog 
                SET 
                chartdetailsbudgetlog.txt_justificacion = '".$justificacion."'
                WHERE 
                chartdetailsbudgetlog.type = '".$type."' 
                AND chartdetailsbudgetlog.transno = '".$transno."'";
        $ErrMsg = "No se Actualizaron Registros en Generales";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }

    $transnoNuevo = 0;
    if ($suficienciaCancelada == 1) {
        // Cancelar Suficiencia Actual
        fnAgregarSuficienciaGeneral($db, $type, $transno, $description, 0, $tagref, $tipoSuficiencia, $functionSuf, $sn_orderno, $ue);

        $transnoNuevo = GetNextTransNo($type, $db);
        /*$tipoSufNueva = 3;
        $estatusNueva = 3;*/
        $tipoSufNueva = 3;
        $estatusNueva = 4;

        // Obtener Registros de autorizaciones si se separo la orden de compra
        // Se deben obtener antes de matar los movimientos ya que se duplicaria la informacion
        $SQL = "SELECT type, transno, tagref, cvefrom, period, qty, nu_tipo_movimiento, partida_esp, description , sn_disponible, estatus, sn_funcion_id, ln_ue
        FROM chartdetailsbudgetlog WHERE type = '".$type."' AND transno = '".$transno."' AND description like 'Autorización Orden de Compra%'";
        $ErrMsg = "No se obtuvieron registros de Autorización para el folio ".$transno;
        $TransResultMovAutorizacion = DB_query($SQL, $db, $ErrMsg);

        $SQL = "SELECT type, transno, tagref, cvefrom, period, qty, nu_tipo_movimiento, partida_esp, description , sn_disponible, estatus, sn_funcion_id, ln_ue
        FROM chartdetailsbudgetlog WHERE type = '".$type."' AND transno = '".$transno."'";
        $ErrMsg = "No se Actualizaron Registros del Log Presupuestal";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        
        
        while ($myrow = DB_fetch_array($TransResult)) {
            // Cancelacion Suficiencia Actual
            $agrego = fnInsertPresupuestoLog($db, $myrow['type'], $myrow['transno'], $myrow['tagref'], $myrow['cvefrom'], $myrow['period'], $myrow['qty'] * -1, $myrow['nu_tipo_movimiento'], $myrow['partida_esp'], $myrow['description'], $myrow['sn_disponible'], $myrow['estatus'], $myrow['sn_funcion_id'], $myrow['ln_ue']);
        }
        
        // Agregar Registros con montos nuevos
        /*
         * MODIFICAR AQUI
         */
        $bandera = 0;
        $mensajeRegistro = 'Autorización Requisición '.$requisitionno;
        foreach ($datosReducciones as $datosClave) {
            $clave = $datosClave['accountcode'];
            $numMes = 1;
            foreach ($dataJsonMeses as $nameMes) {
                $periodo = GetPeriod('15/'.(strlen($numMes) == 1 ? '0'.$numMes : $numMes).'/'.$datosClave['año'], $db);
                
                if(key_exists($nameMes."Compra", $datosClave)){
                    $cantidad = $datosClave[$nameMes."Compra"] * -1;
                    $bandera = 1;
                }else{
                    if($bandera == 0){
                        $cantidad = $datosClave[$nameMes] * -1;
                    }
                }
                
                $disponible = fnInfoPresupuesto($db, $clave, $periodo);
                
                $ueClave = fnObtenerUnidadEjecutoraClave($db, $clave);
                foreach ($disponible as $dispo) {
                    if (abs($cantidad) > 0) {
                        // Agregar precomprometido
                        /*
                         * Cambian los tipos de movimientos
                         */
                        $agrego = fnInsertPresupuestoLog($db, $type, $transnoNuevo, $tagref, $clave, $periodo, $cantidad, 258, $datosClave['partida_esp'], $mensajeRegistro, 1, $estatusNueva, 0, $ueClave);
                        // Agregar suficiencia
                        $agrego = fnInsertPresupuestoLog($db, $type, $transnoNuevo, $tagref, $clave, $periodo, $cantidad, 263, $datosClave['partida_esp'], $mensajeRegistro, 0, $estatusNueva, $functionSuf, $ueClave);
                    }
                }
                $numMes ++;
                $cantidad = 0;
                if($bandera == 1){
                    $bandera = 0;
                    
                }
            }
            
        }

        // Agregar movimientos de autorizacion para nuevo folio de suficiencia
        //echo "Movimientos de autorización. Checar que estatus traen y ver si tambien se van a 4\n";
        while ($myrow = DB_fetch_array($TransResultMovAutorizacion)) {
            // Movimientos de Autorización
            $agrego = fnInsertPresupuestoLog($db, $type, $transnoNuevo, $myrow['tagref'], $myrow['cvefrom'], $myrow['period'], abs($myrow['qty']), $myrow['nu_tipo_movimiento'], $myrow['partida_esp'], $myrow['description'], $myrow['sn_disponible'], $myrow['estatus'], $myrow['sn_funcion_id'], $myrow['ln_ue']);
        }
        
        // Cancelar estatus del log completo
        $SQL = "UPDATE chartdetailsbudgetlog SET estatus = 0 WHERE type = '".$type."' AND transno = '".$transno."'";
        $TransResult = DB_query($SQL, $db);
        // Quitar cancelada
        $SQL = "UPDATE tb_suficiencias SET sn_cancel = 0 WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'";
        $TransResult = DB_query($SQL, $db);
        // No poner disponible la Suficiencia Nueva solo precomprometido
        $SQL = "UPDATE chartdetailsbudgetlog SET sn_disponible = 0 WHERE type = '".$type."' AND transno = '".$transno."' and nu_tipo_movimiento = 263";
        $TransResult = DB_query($SQL, $db);
        // Nueva Suficiencia
        $description = 'Manual Derivada de Automática. Requisición '.$requisitionno;
        fnAgregarSuficienciaGeneral($db, $type, $transnoNuevo, $description, $estatusNueva, $tagref, $tipoSufNueva, $functionSuf, $sn_orderno, $ue);
    }

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
    $datosPresupuesto['fechaCaptura'] = date('d-m-Y');
    $datosPresupuesto['transnoNuevo'] = $transnoNuevo;
    $datosPresupuesto['suficienciaCancelada'] = $suficienciaCancelada;

    $contenido = array('datos' => $datosPresupuesto);
    $result = true;
}
if ($option == 'obtenerMesRequisicion') {
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $mesName = "";
    $tipoSuficiencia = "";

    //Obtener nombre del mes
    $SQL = "SELECT tb_suficiencias.nu_estatus, tb_suficiencias.nu_tipo, cat_Months.mes as mesName
    FROM tb_suficiencias 
    LEFT JOIN chartdetailsbudgetlog ON chartdetailsbudgetlog.type = tb_suficiencias.nu_type AND chartdetailsbudgetlog.transno = tb_suficiencias.nu_transno
    LEFT JOIN periods ON periods.periodno = chartdetailsbudgetlog.period
    LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
    WHERE tb_suficiencias.nu_type = '".$type."' and tb_suficiencias.nu_transno = '".$transno."'
    AND chartdetailsbudgetlog.qty <> 0
    LIMIT 1";
    $ErrMsg = "No se obtuvo el nombre del Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $mesName = $myrow['mesName'];
        $tipoSuficiencia = $myrow['nu_tipo'];
    }

    $datosSuf['mesName'] = $mesName;
    $datosSuf['tipoSuficiencia'] = $tipoSuficiencia;

    $contenido = array('datos' => $datosSuf);
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
