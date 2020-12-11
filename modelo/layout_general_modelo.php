<?php
/**
 * Adecuaciones Presupuestales
 *
 * @category
 * @package ap_grp
 * @author Eduardo López Morales <[<email address>]>
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

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
//$funcion=2273;
//include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SecurityFunctionsHeader.inc');
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
$SQL1='';
header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$transResult = DB_query($SQL, $db);

$option = $_POST['option'];

function fnCrearLayoutFisico($ruta, $datos)
{
   //Generamos el csv de todos los datos
    if (!$handle = fopen($ruta, "w")) {
        //echo "Cannot open file";
        //$contenido .= '<br><p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se pudo generar el Layout, Transacción '.$datosTransno[$i].'</p>';
        $result = false;
    }
    if (fwrite($handle, utf8_decode($datos)) === false) {
        //echo "Cannot write to file";
        //$contenido .= '<br><p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se pudo generar el Layout, Transacción '.$datosTransno[$i].'</p>';
        $result = false;
    }
    fclose($handle);

    return true;
}

if ($option == 'generarLayout') {
    $nombreArchivoGeneral = "";
    $contenido = "";
    $nombreLayout="";
    $nombreLayoutBD="";

    if (isset($_POST['nombreLayout'])) {
        if ($_POST['nombreLayout']!='') {
             $nombreLayout=$_POST['nombreLayout'];
             $nombreArchivoGeneral = $nombreLayout;
        } else {
              //$nombreLayout='Layout_';
              $nombreArchivoGeneral = "Layout_Adec";
              $nombreLayout=$nombreArchivoGeneral;
        }
    } else {
        $nombreArchivoGeneral = "Layout_Adec";
        $nombreLayout= $nombreArchivoGeneral;
    }

    $funcion = $_POST['funcion'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $tipoLayout = $_POST['tipoLayout'];
    $guardar=$_POST['guardar'];
   
    /* Arturo Lopez Peña */
    if (isset($_POST['nombreLayoutBD'])) {
        if ($_POST['nombreLayoutBD']!='') {
            $nombreLayoutBD=$_POST['nombreLayoutBD'];
        } else {
            $nombreLayoutBD="Layout_Generado";
        }
    } else {
        $nombreLayoutBD="Layout_Generado";
    }
    /*fin Arturo Lopez Peña */

    $info = array();
    $SQL = "SELECT tb_layouts_config.txt_sql_salida 
            FROM tb_layouts_config 
            WHERE 
            tb_layouts_config.sn_funcion_id = '".$funcion."'
            AND tb_layouts_config.sn_type = '".$type."'
            AND tb_layouts_config.sn_activo = '1' LIMIT 1";
    $ErrMsg = "No se obtuvo la Configuración del Layout";
    $transResult = DB_query($SQL, $db, $ErrMsg);

    if (DB_num_rows($transResult) > 0) {
        while ($myrow = DB_fetch_array($transResult)) {
            $csv_end = "  
            ";
            $csv_sep = ",";
            $csv = "";
            $encabezado = "";
            $encabezadoNum = 0;

            // $nombreArchivoGeneral = "Layout_Adec_";
            //$datosTransno = explode(',', $transno);
            //for($i = 0; $i < sizeof($datosTransno); $i++) {
            //$transnoOperacion = $datosTransno[$i];

            foreach ($transno as $datosLayout) {
                $transnoOperacion = $datosLayout ['transno'];
                
                $nombreArchivo = $nombreArchivoGeneral.$transnoOperacion."_".date('dmY')."_".date('His').".csv";
                $csv_file = "../archivos/".$nombreArchivo;
                $linkDescarga = "archivos/".$nombreArchivo;

                $nombreArchivoGeneral .= "_".$transnoOperacion;

                $SQL = sprintf($myrow ['txt_sql_salida'], $type, $transnoOperacion);
                
                $ErrMsg = "No se obtuvo la Información para generar el Layout";
                $transResult2 = DB_query($SQL, $db, $ErrMsg);
                if (DB_num_rows($transResult2) > 0) {
                    while ($myrow2 = DB_fetch_array($transResult2)) {
                        //$info[] = array( 'statusid' => $myrow2 ['statusid'], 'statusname' => $myrow2 ['statusname'] );
                        $linea = "";
                        $SQL = "SELECT 
                                tb_layouts.ln_tabla,
                                tb_layouts.ln_campo_tabla,
                                tb_layouts.txt_descripcion
                                FROM tb_layouts
                                WHERE 
                                tb_layouts.nu_funcion = '".$funcion."'
                                AND tb_layouts.nu_tipo_doc = '".$type."'
                                AND tb_layouts.nu_activo = '1'
                                AND tb_layouts.nu_tipo_layout = '0'
                                ORDER BY tb_layouts.nu_orden ASC";
                        $ErrMsg = "No se obtuvo la Configuración del Layout";
                        $transResult3 = DB_query($SQL, $db, $ErrMsg);
                        while ($myrow3 = DB_fetch_array($transResult3)) {
                            if (!empty($myrow3 ['ln_campo_tabla'])) {
                                $datoCampo = " ";
                                if (trim($myrow2 [$myrow3 ['ln_campo_tabla']]) != '') {
                                    $datoCampo = $myrow2 [$myrow3 ['ln_campo_tabla']];
                                }
                                if ($type == '285' && $funcion == '2373' && $myrow3 ['ln_campo_tabla'] == 'dtm_fecharegistro') {
                                    // Si layout del paaas
                                    $datoCampo= date_create($myrow2 [$myrow3 ['ln_campo_tabla']]);
                                    $datoCampo= date_format($datoCampo, "d/m/Y");
                                }
                                if (empty($linea)) {
                                    $linea .= $datoCampo;
                                } else {
                                    $linea .= $csv_sep.$datoCampo;
                                }
                            }
                            if (empty($encabezado) and $encabezadoNum == 0) {
                                $encabezado .= $myrow3 ['txt_descripcion'];
                            } else if ($encabezadoNum == 0) {
                                $encabezado .= $csv_sep.$myrow3 ['txt_descripcion'];
                            }
                        }

                        $encabezadoNum = 1;

                        $csv .= $linea.$csv_end;
                    }
                } else {
                    // Solo encabezados
                    $SQL = "SELECT 
                            tb_layouts.ln_tabla,
                            tb_layouts.ln_campo_tabla,
                            tb_layouts.txt_descripcion
                            FROM tb_layouts
                            WHERE 
                            tb_layouts.nu_funcion = '".$funcion."'
                            AND tb_layouts.nu_tipo_doc = '".$type."'
                            AND tb_layouts.nu_activo = '1'
                            AND tb_layouts.nu_tipo_layout = '0'
                            ORDER BY tb_layouts.nu_orden ASC";
                    $ErrMsg = "No se obtuvo la Configuración del Layout";
                    $transResult3 = DB_query($SQL, $db, $ErrMsg);
                    while ($myrow3 = DB_fetch_array($transResult3)) {
                        if (empty($encabezado) and $encabezadoNum == 0) {
                            $encabezado .= $myrow3 ['txt_descripcion'];
                        } else if ($encabezadoNum == 0) {
                            $encabezado .= $csv_sep.$myrow3 ['txt_descripcion'];
                        }
                    }

                    $encabezadoNum = 1;
                }

                if ($tipoLayout == 1) {
                    $csv = $encabezado.$csv_end.$csv;
                    $crearArchivo = fnCrearLayoutFisico($csv_file, $csv);
                    if (!$crearArchivo) {
                        $contenido .= '<br><p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se pudo generar el Layout, Transacción '.$transnoOperacion.'</p>';
                    }
                    if ($guardar==0) {
                        // para guardar  datos  en tb archivos  cuando sea diferente de 0 es por que se esta descargando  solo el encabezado
                        $agrego = fnInsertPruebasDocumentos($db, $nombreArchivoGeneral, 'text/csv', $linkDescarga, $type, $funcion, $transnoOperacion, $nombreLayoutBD, 0);
                        if (!$agrego) {
                            $contenido .= '<br><p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se pudo almacenar la información del Layout, Transacción '.$transnoOperacion.'</p>';
                        }
                    }

                    $csv = "";
                    
                    $contenido .= '<br><a class="btn bgc8" style="color:#fff" href="'.$linkDescarga.'"> Click aqui para descargar '.$nombreLayout.', Transacción '.$transnoOperacion.'</a><br><br>';
                    $result = true;
                }
            }

            if ($tipoLayout != 1) {
                $nombreArchivoGeneral .= "_".date('dmY')."_".date('His').".csv";
                $csv = $encabezado.$csv_end.$csv;

                $csv_file = "../archivos/".$nombreArchivoGeneral;
                $linkDescarga = "archivos/".$nombreArchivoGeneral;

                $crearArchivo = fnCrearLayoutFisico($csv_file, $csv);
                if (!$crearArchivo) {
                    $contenido .= '<br><p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se pudo generar el Layout, Transacción '.$transnoOperacion.'</p>';
                }

                //for ($i = 0; $i < sizeof($datosTransno); $i++) {
                foreach ($transno as $datosLayout) {
                    $transnoOperacion = $datosLayout ['transno'];
                    $agrego = fnInsertPruebasDocumentos($db, $nombreArchivoGeneral, 'text/csv', $linkDescarga, $type, $funcion, $transnoOperacion, $nombreLayoutBD, 0);
                    if (!$agrego) {
                        $contenido .= '<br><p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se pudo almacenar la información del Layout, Transacción '.$transnoOperacion.'</p>';
                    }
                }

                $contenido .= '<br><a class="btn bgc8" style="color:#fff" href="'.$linkDescarga.'"> Click aqui para descargar '.$nombreLayout.'</a>';
                $result = true;
            }
        }
    } else {
        $contenido = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Sin Configuración para generar Layout de la Función '.$funcion.'</p>';
        $result = false;
    }
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
