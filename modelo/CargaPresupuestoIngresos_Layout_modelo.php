<?php
/**
 * Carga de Presupuesto Inicial
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 25/09/2017
 * Fecha Modificación: 25/09/2017
 * Se realizan operaciones para la carga del presupuesto incial de Ingreso y Egreso
 */

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
$funcion=1376;
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

if ($option == 'datosConfiguracion') {
    $idConfig = $_POST['idConfig'];
    $contenido = "";
    $info = array();

    $letraArray = array();
    $numletras = 0;
    $letraArray2 = array();
    $numletras2 = 0;
    $letraArray2[] = "";

    for ($i=65; $i<=90; $i++) {
        $letra = chr($i);
        $letraArray[] = ($letra);
        $letraArray2[] = ($letra);
    }

    $titulo = '<h3>Información del Presupuesto</h3>';
    $contenidoHtml .= '<table class="table table-bordered">';
    $header = "<tr>";
    $body = "<tr>";

    $header .= '<td>Columnas</td>';
    $body .= '<td>Información</td>';
    
    $SQL = "SELECT nombre as texto FROM budgetConfigClave WHERE sn_activo = '1' AND idClavePresupuesto = '".$idConfig."' ORDER BY orden ASC";
    $ErrMsg = "No se obtuvo la configuración del presupuesto";
    $transResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($transResult)) {
        $header .= '<td>'.$letraArray[$numletras].'</td>';
        $body .= '<td>'.$myrow ['texto'].'</td>';
        $numletras ++;
    }

    $header .= '<td>'.$letraArray[$numletras].'</td>';
    $body .= '<td>Original</td>';
    $numletras ++;

    $SQL = "SELECT cat_Months.mes as texto
            FROM periods 
            LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
            WHERE periods.lastdate_in_period like '%".date('Y')."%' 
            ORDER BY periods.lastdate_in_period asc";
    $ErrMsg = "No se obtuvo la Información de los meses";
    $transResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($transResult)) {
        if ($numletras < sizeof($letraArray)) {
            $header .= '<td>'.$letraArray2[$numletras2].$letraArray[$numletras].'</td>';
            $body .= '<td>'.$myrow ['texto'].'</td>';

            $numletras ++;
        } else {
            $numletras = 0;
            $numletras2 ++;

            $header .= '<td>'.$letraArray2[$numletras2].$letraArray[$numletras].'</td>';
            $body .= '<td>'.$myrow ['texto'].'</td>';

            $numletras ++;
        }
    }

    $body .= "</tr>";
    $header .= "</tr>";
    $contenidoHtml .= $header . $body . '</table>';

    $contenido =  array('titulo' => $titulo, 'contenido' => $contenidoHtml );
    $result = true;
}

function fnInsertGeneralLogPresupuesto($db, $cvefrom, $qty, $type, $transno, $tagref, $period, $disponible = 1, $partida = "", $account = "", $noOficio = "")
{
    $SQL="INSERT INTO chartdetailsbudgetlog (
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
        numero_oficio
        )
        VALUES (
        '".$_SESSION['UserID']."',
        '".$qty."',
        '".$cvefrom."',
        '".$type."',
        '".$transno."',
        '".$account."',
        '".$tagref."',        
        '".$period."',      
        '".$partida."',        
        '".$disponible."',
        '".$noOficio."'
        )";
    $ErrMsg = "No se agrego log al presupuesto ";
    $transResult2 = DB_query($SQL, $db, $ErrMsg);

    return true;
}

if ($option == 'cargarLayout') {
    $idConfig = $_POST['idConfig'];
    $fechaPoliza = date('Y-m-d');//$_POST['fechaPoliza'];
    $tipoPresupuesto = $_POST['tipoPresupuesto'];
    $filaInicio = $_POST['filaInicio'];
    $noOficio = $_POST['noOficio'];

    $contenidoHtml = "";
    if (isset($_FILES['archivos'])) {
        $File_Arcchivos = $_FILES['archivos']['name'];

        ini_set('memory_limit', '1024M');
        set_include_path(implode(PATH_SEPARATOR, array(realpath('../lib/PHPExcel-1.8/Classes/PHPExcel/'), get_include_path(),)));
        require_once("../lib/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php");

        // Cargo la hoja de cálculo
        $objPHPExcel = PHPExcel_IOFactory::load($_FILES['archivos']['tmp_name']);

        //Asigno la hoja de calculo activa
        $objPHPExcel->setActiveSheetIndex(0);
        //Obtengo el numero de filas del archivo
        $numRows = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();

        $letraArray = array();
        $numletras = 0;
        $letraArray2 = array();
        $numletras2 = 0;
        $letraArray2[] = "";

        for ($i=65; $i<=90; $i++) {
            $letra = chr($i);
            $letraArray[] = ($letra);
            $letraArray2[] = ($letra);
        }

        $contenidoHtml .= '<div class="panel-body" style="overflow: scroll;">';
        $contenidoHtml .= '<table class="table table-bordered">';
        $header = "<tr>";
        $header .= '<td>Fila</td>';
        $body = "";
        for ($i = $filaInicio; $i <= $numRows; $i++) { // $numRows
            $claveCreada = "";
            $tagref = "";
            $anio = date('Y');
            $partida = "";
            $sqlCampos = "";
            $sqlValores = "";
            $errores = 0;
            $numletras = 0;
            $numletras2 = 0;
            $body .= '<tr>';
            $body .= '<td>'.$i.'</td>';
            $SQL = "SELECT campoPresupuesto, tabla, campo, nombre
                FROM budgetConfigClave WHERE sn_activo = '1' AND idClavePresupuesto = '".$idConfig."' ORDER BY orden ASC";
            $ErrMsg = "No se obtuvo la configuración del presupuesto";
            $transResult = DB_query($SQL, $db, $ErrMsg);
            while ($myrow = DB_fetch_array($transResult)) {
                $dato = trim($objPHPExcel->getActiveSheet()->getCell($letraArray[$numletras].$i)->getCalculatedValue());
                $numletras ++;
                if ($myrow['campoPresupuesto'] == 'tagref') {
                    $tagref = $dato;
                }
                if ($myrow['campoPresupuesto'] == 'anho') {
                    $anio = $dato;
                }
                if ($myrow['campoPresupuesto'] == 'partida_esp') {
                    $partida = $dato;
                }
                if (empty($claveCreada)) {
                    $claveCreada .= $dato;
                } else {
                    $claveCreada .= '-'.$dato;
                }
                if (empty($sqlCampos)) {
                    $sqlCampos .= $myrow['campoPresupuesto'];
                    $sqlValores .= "'".$dato."'";
                } else {
                    $sqlCampos .= ', '.$myrow['campoPresupuesto'];
                    $sqlValores .= ", '".$dato."'";
                }
                if (!empty($myrow['tabla']) && !empty($myrow['campo'])) {
                    // Validacion por catalogo
                    if ($myrow['tabla'] == 'g_cat_ramo' && $myrow['campo'] == 'cve_ramo') {
                    }
                    $SQL = "SELECT ".$myrow['campo']." FROM ".$myrow['tabla']." WHERE ".$myrow['campo']." = '".$dato."'";
                    $ErrMsg = "No se obtuvo la configuración del presupuesto";
                    $transResult2 = DB_query($SQL, $db, $ErrMsg);
                    if (DB_num_rows($transResult2)==0) {
                        //$errores = 1;
                        $body .= '<td><p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i></p>'.'</td>';
                    } else {
                        $body .= '<td><p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>'.'</td>';
                    }
                } else {
                    // No tiene configuración
                    $body .= '<td><p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>'.'</td>';
                }

                if ($filaInicio == $i) {
                    $header .= '<td>'.$myrow['nombre'].'</td>';
                }
            }

            $totalPoliza = 0;
            $dato = trim($objPHPExcel->getActiveSheet()->getCell($letraArray[$numletras].$i)->getCalculatedValue());
            $numletras ++;
            if (!empty($dato)) {
                $dato = str_replace(',', '', $dato);
                if (!is_numeric($dato)) {
                    $dato = 0;
                } else {
                    $totalPoliza = $dato;
                }
            }
            if ($filaInicio == $i) {
                $header .= '<td>Original</td>';
            }
            $body .= '<td>'.$dato.'</td>';
            $sqlCampos .= ', budget, original';
            $sqlValores .= ", '".$dato."', '".$dato."'";

            $type = 49;
            $transno = 0;
            // Campos de la base
            $datosMeses = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
            for ($numMes=0; $numMes < sizeof($datosMeses); $numMes++) {
                if ($numletras < sizeof($letraArray)) {
                    $dato = trim($objPHPExcel->getActiveSheet()->getCell($letraArray2[$numletras2].$letraArray[$numletras].$i)->getCalculatedValue());
                    $numletras ++;
                } else {
                    $numletras = 0;
                    $numletras2 ++;
                    $dato = trim($objPHPExcel->getActiveSheet()->getCell($letraArray2[$numletras2].$letraArray[$numletras].$i)->getCalculatedValue());
                    $numletras ++;
                }

                if (!empty($dato)) {
                    $dato = str_replace(',', '', $dato);
                    if (!is_numeric($dato)) {
                        $dato = 0;
                    }
                }
                if ($filaInicio == $i) {
                    $header .= '<td>'.$datosMeses [$numMes].'</td>';
                }
                $body .= '<td>'.$dato.'</td>';

                $sqlCampos .= ', '.$datosMeses [$numMes];
                $sqlValores .= ", '".$dato."'";

                if ($errores == 0 && $dato > 0) {
                    // Agregar al log
                    $mes = $numMes+1; // Se agrega 1 ya que inicia en 0
                    if (strlen($mes) == "1") {
                        $mes = '0'.$mes;
                    }
                    if ($transno == 0) {
                        $transno = GetNextTransNo($type, $db);
                    }
                    $period = GetPeriod('15/'.$mes.'/'.$anio, $db);
                    $account = "";
                    $res = fnInsertGeneralLogPresupuesto($db, $claveCreada, $dato, $type, $transno, $tagref, $period, 1, $partida, $account, $noOficio);
                }
            }

            if ($filaInicio == $i) {
                $header .= '<td>Proceso</td>';
            }

            if ($errores == 0) {
                // No tiene Error
                $SQL = "INSERT INTO chartdetailsbudgetbytag (".$sqlCampos.", accountcode, idClavePresupuesto, sn_inicial, txt_userid)
                VALUES (".$sqlValores.", '".$claveCreada."', '".$idConfig."', '1', '".$_SESSION['UserID']."')";
                $ErrMsg = "No se obtuvo la Clave Presupuestal ".$claveCreada;
                $transResult2 = DB_query($SQL, $db, $ErrMsg);
                
                $msjPoliza = "";
                if ($totalPoliza > 0 && $tipoPresupuesto == 2) {
                    // Poliza para Egreso
                    if ($transno == 0) {
                        $transno = GetNextTransNo($type, $db);
                    }
                    $fechapoliza = date('Y-m-d');
                    $period = GetPeriod(date('d/m/Y'), $db);
                    $referencia = "Presupuesto Aprobado No Oficio ".$noOficio;
                    $res = GeneraMovimientoContablePresupuesto(49, 'APROBADO', 'MODIFICADO', $transno, $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '');
                } else if ($tipoPresupuesto == 1) {
                    $msjPoliza = ", Sin definición para generar poliza de Ingreso";
                }
                $body .= '<td><p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i>
                    Información Agregada'.$msjPoliza.'</p></td>';
            } else {
                // Tiene Error
                $body .= '<td><p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Información en los catálogos no completa</p></td>';
            }

            $body .= '</tr>';
        }

        $header .= "</tr>";
        $contenidoHtml .= $header . $body . '</table></div>';
    }

    $contenido =  $contenidoHtml;
    $result = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
