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
   
$PageSecurity = 5;
include('includes/session.inc');
$funcion = 1376;
$title = traeNombreFuncion($funcion, $db);
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

ini_set('memory_limit', '3068M');
set_time_limit(600);

if (isset($_POST['btnCargarSubmit'])) {
    $idConfig = $_POST['selectConfiguracionClaveLayout'];
    $fechaPoliza = date('Y-m-d');//$_POST['fechaPoliza'];
    $tipoPresupuesto = $_POST['selectTipoPresupuesto'];
    
    $filaInicio = $_POST['txtFilaInicio'];
    $noOficio = $_POST['txtNoOficio'];
    $accountAbono = '';
    $accountCargo = '';
    $tipoMovPre = '';
    $referencia = '';

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
        prnMsg("No existen preferencias de empresa configuradas...", "error"); // error
        exit();
    }

    $erroresVal = 0;

    $type = 49;
    if ($tipoPresupuesto == 2) {
        // Movimientos Egreso
        $accountAbono = 'APROBADO';
        $accountCargo = 'POREJERCER';
        $tipoMovPre = '251';
        $type = 49;
    } else if ($tipoPresupuesto == 1) {
        // Movimientos Ingreso
        $accountAbono = 'INGRESO_EJECUTAR';
        $accountCargo = 'INGRESO_APROBADO';
        $tipoMovPre = '309';
        $type = 52;
    } else {
        $erroresVal = 1;
        prnMsg("Seleccionar Tipo de Presupuesto", "error");
    }

    if (empty($idConfig) || $idConfig == '-1') {
        $erroresVal = 1;
        prnMsg("Seleccionar Configuración del Presupuesto", "error");
    }

    if ($_FILES['txtCatalogo']['name'] == null) {
        $erroresVal = 1;
        prnMsg("Seleccionar Archivo del Presupuesto", "error");
    }

    $contenidoHtml = "";
    if (isset($_FILES['txtCatalogo']) && $erroresVal == 0) {
        set_include_path(implode(PATH_SEPARATOR, array(realpath('lib/PHPExcel-1.8/Classes/PHPExcel/'), get_include_path(),)));
        require_once("lib/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php");

        $File_Arcchivos = $_FILES['txtCatalogo']['name'];

        // Cargo la hoja de cálculo
        $objPHPExcel = PHPExcel_IOFactory::load($_FILES['txtCatalogo']['tmp_name']);

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

        $infoCampos = array();
        $SQL = "SELECT campoPresupuesto, tabla, campo, nombre, orden
                FROM budgetConfigClave WHERE sn_activo = '1' AND idClavePresupuesto = '".$idConfig."' ORDER BY orden ASC";
        $ErrMsg = "No se obtuvo la configuración del presupuesto";
        $transResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($transResult)) {
            $infoCampos[] = array( 'campoPresupuesto' => $myrow ['campoPresupuesto'],
                'tabla' => $myrow ['tabla'],
                'campo' => $myrow ['campo'],
                'nombre' => $myrow ['nombre'] );
        }

        $contenidoHtml .= '<div class="panel-body" style="overflow: scroll; height: 500px;">';
        $contenidoHtml .= '<table class="table table-bordered">';
        $header = "<tr>";
        $header .= '<td>Fila</td>';
        $body = "";
        $sqlPresupuesto = "";
        $sqlPresupuestoLog = "";
        $numeroRegistros = 1;
        $transno = 0;
        $transno = GetNextTransNo($type, $db);

        // Array para folios agrupados (UR-UE)
        $infoFolios = array();

        for ($i = $filaInicio; $i <= $numRows; $i++) { // $numRows
            $claveCreada = "";
            $tagref = "";
            $anio = date('Y');
            $partida = "";
            $aux1 = "";
            $ueCadena = "";
            $sqlCampos = "";
            $sqlValores = "";
            $errores = 0;
            $numletras = 0;
            $numletras2 = 0;
            $body .= '<tr>';
            $body .= '<td>'.$i.'</td>';
            for ($intC=0; $intC < sizeof($infoCampos); $intC++) {
                $dato = trim($objPHPExcel->getActiveSheet()->getCell($letraArray[$numletras].$i)->getCalculatedValue());
                $numletras ++;
                if ($infoCampos[$intC]['campoPresupuesto'] == 'tagref') {
                    $tagref = $dato;
                }
                if ($infoCampos[$intC]['campoPresupuesto'] == 'anho') {
                    $anio = $dato;
                }
                if ($infoCampos[$intC]['campoPresupuesto'] == 'partida_esp') {
                    $partida = $dato;
                }
                if ($infoCampos[$intC]['campoPresupuesto'] == 'ln_aux1') {
                    $aux1 = $dato;
                }
                if ($infoCampos[$intC]['campoPresupuesto'] == 'ue') {
                    $ueCadena = $dato;
                }
                if ($infoCampos[$intC]['campoPresupuesto'] == 'cve_ramo' && strlen($dato) == 1) {
                    // Si el ramo tiene 1 caracter ponemos 0
                    $dato = '0'.$dato;
                }
                if (empty($claveCreada)) {
                    $claveCreada .= $dato;
                } else {
                    $claveCreada .= '-'.$dato;
                }
                if (empty($sqlCampos)) {
                    $sqlCampos .= $infoCampos[$intC]['campoPresupuesto'];
                    $sqlValores .= "'".$dato."'";
                } else {
                    $sqlCampos .= ', '.$infoCampos[$intC]['campoPresupuesto'];
                    $sqlValores .= ", '".$dato."'";
                }
                if (!empty($infoCampos[$intC]['tabla']) && !empty($infoCampos[$intC]['campo'])) {
                    // Validacion por catalogo
                    if ($infoCampos[$intC]['tabla'] == 'g_cat_ramo' && $infoCampos[$intC]['campo'] == 'cve_ramo') {
                    }
                    $SQL = "SELECT ".$infoCampos[$intC]['campo']." FROM ".$infoCampos[$intC]['tabla']." WHERE ".$infoCampos[$intC]['campo']." = '".$dato."'";
                    $ErrMsg = "No se obtuvo la configuración del presupuesto";
                    $transResult2 = DB_query($SQL, $db, $ErrMsg);
                    if (DB_num_rows($transResult2)==0) {
                        $errores = 1;
                        $body .= '<td><p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i></p>'.$dato.'</td>';
                    } else {
                        $body .= '<td><p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>'.'</td>';
                    }
                } else if ($infoCampos[$intC]['campoPresupuesto'] != 'anho') {
                    // No tiene configuración
                    $errores = 1;
                    $body .= '<td><p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i></p>'.'</td>';
                } else {
                    $body .= '<td><p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>'.'</td>';
                }

                if ($filaInicio == $i) {
                    $header .= '<td>'.$infoCampos[$intC]['nombre'].'</td>';
                }
            }

            if ($filaInicio == $i) {
                $header .= '<td>Existe</td>';
            }

            // $datoUE = fnObtenerUnidadEjecutoraClave($db, $claveCreada);
            $datoUE = "";
            if (!empty($aux1)) {
                $SQL = "SELECT ue FROM tb_cat_unidades_ejecutoras WHERE ln_aux1 = '".$aux1."'";
                $ErrMsg = "No se obtuvo la configuración del presupuesto";
                $transResultUe = DB_query($SQL, $db, $ErrMsg);
                $myrowUe = DB_fetch_array($transResultUe);
                $datoUE = $myrowUe ['ue'];
            } else if (!empty($ueCadena)) {
                $datoUE = $ueCadena;
            }
            // echo "<br>claveCreada: ".$claveCreada;
            // exit();
            
            $claveCreada = str_replace('.', '-', $claveCreada);

            $SQL = "SELECT accountcode FROM chartdetailsbudgetbytag WHERE accountcode = '".$claveCreada."'";
            $ErrMsg = "No se obtuvo la configuración del presupuesto";
            $transResult2 = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($transResult2) > 0) {
                $errores = 1;
                $body .= '<td><p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i></p>'.'</td>';
            } else {
                $body .= '<td><p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i></p>'.'</td>';
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

            // $type = 49;
            // $transno = 0;
            // Campos de la base
            $datosMeses = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');

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
                $folioPolizaUe = fnObtenerFolioUeGeneral($db, $tagref, $datoUE, $type);
                $infoFolios[] = array(
                    'tagref' => $tagref,
                    'ue' => $datoUE,
                    'type' => $type,
                    'transno' => $transno,
                    'folioPolizaUe' => $folioPolizaUe
                );
            }


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
                } else {
                    $dato = 0;
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
                        // $transno = GetNextTransNo($type, $db);
                    }
                    //$period = GetPeriod('15/'.$mes.'/'.$anio, $db);
                    $period = fnGetPeriodSinValidar('15/'.$mes.'/'.$anio, $db);
                    $account = '';
                    $disponible = 1;
                    //$res = fnInsertGeneralLogPresupuesto($db, $claveCreada, $dato, $type, $transno, $tagref, $period, 1, $partida, $account, $noOficio);
                    if (empty($sqlPresupuestoLog)) {
                        $sqlPresupuestoLog .= "
                        (
                        '".$_SESSION['UserID']."',
                        '".$dato."',
                        '".$claveCreada."',
                        '".$type."',
                        '".$transno."',
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
                        )";
                    } else {
                        $sqlPresupuestoLog .= "
                        , (
                        '".$_SESSION['UserID']."',
                        '".$dato."',
                        '".$claveCreada."',
                        '".$type."',
                        '".$transno."',
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
                        )";
                    }
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
                // $transResult2 = DB_query($SQL, $db, $ErrMsg);
                
                if (empty($sqlPresupuesto)) {
                    $sqlPresupuesto .= "(".$sqlValores.", '".$claveCreada."', '".$idConfig."', '1', '".$_SESSION['UserID']."')";
                } else {
                    $sqlPresupuesto .= ", (".$sqlValores.", '".$claveCreada."', '".$idConfig."', '1', '".$_SESSION['UserID']."')";
                }
                
                $msjPoliza = "";
                if ($totalPoliza > 0) {
                    // Poliza para Egreso
                    if ($transno == 0) {
                        // $transno = GetNextTransNo($type, $db);
                    }
                    $fechapoliza = date('Y-m-d');
                    $period = GetPeriod(date('d/m/Y'), $db);
                    $referencia = "Presupuesto Aprobado No Oficio ".$noOficio;
                    // $datoUE = fnObtenerUnidadEjecutoraClave($db, $claveCreada);
                    $res = GeneraMovimientoContablePresupuesto($type, $accountAbono, $accountCargo, $transno, $period, $totalPoliza, $tagref, $fechapoliza, $claveCreada, $referencia, $db, false, '', '', '', $datoUE, 1, 0, $folioPolizaUe);
                }
                $body .= '<td><p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i>
                    Información Agregada'.$msjPoliza.'</p></td>';
            } else {
                // Tiene Error
                $body .= '<td><p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Información en los catálogos no completa</p></td>';
            }

            $body .= '</tr>';

            // if ($numeroRegistros == 100) {
                if (!empty($sqlPresupuesto)) {
                    $SQL = "INSERT INTO chartdetailsbudgetbytag (".$sqlCampos.", accountcode, idClavePresupuesto, sn_inicial, txt_userid)
                    VALUES ".$sqlPresupuesto;
                    $ErrMsg = "No se obtuvo la Clave Presupuestal ";
                    $transResult2 = DB_query($SQL, $db, $ErrMsg);
                    $sqlPresupuesto = "";
                }
                if (!empty($sqlPresupuestoLog)) {
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
                        numero_oficio,
                        datemov,
                        fecha_captura,
                        dtm_aplicacion,
                        nu_tipo_movimiento,
                        ln_ue
                        )
                        VALUES ".$sqlPresupuestoLog;
                    $ErrMsg = "No se agrego log al presupuesto ";
                    $transResult2 = DB_query($SQL, $db, $ErrMsg);
                    $sqlPresupuestoLog = "";
                }

                $numeroRegistros = 1;
            // }

            $numeroRegistros ++;
        }

        // Ejectutar Insert, por si quedan despues del ciclo
        if (!empty($sqlPresupuesto)) {
            $SQL = "INSERT INTO chartdetailsbudgetbytag (".$sqlCampos.", accountcode, idClavePresupuesto, sn_inicial, txt_userid)
            VALUES ".$sqlPresupuesto;
            $ErrMsg = "No se obtuvo la Clave Presupuestal ";
            $transResult2 = DB_query($SQL, $db, $ErrMsg);
            $sqlPresupuesto = "";
        }
        if (!empty($sqlPresupuestoLog)) {
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
                numero_oficio,
                datemov,
                fecha_captura,
                dtm_aplicacion,
                nu_tipo_movimiento,
                ln_ue
                )
                VALUES ".$sqlPresupuestoLog;
            $ErrMsg = "No se agrego log al presupuesto ";
            $transResult2 = DB_query($SQL, $db, $ErrMsg);
            $sqlPresupuestoLog = "";
        }

        $header .= "</tr>";
        $contenidoHtml .= $header . $body . '</table></div>';
    }

    $contenido =  $contenidoHtml;
    echo $contenido;

    // echo "<br>";
    // var_dump($infoFolios);

    //prnMsg("Proceso Finalizado", "success"); // error
}
?>

<div id="divMensajeOperacion" name="divMensajeOperacion"></div>

<form id="form_input" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    <div align="left">
        <!--Panel Busqueda-->
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingOne">
              <h4 class="panel-title row">
              <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
                Filtros
              </a>
              </h4>
            </div>
            <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
              <div class="panel-body">
                <div class="col-md-4">
                    <div class="form-inline row">
                        <div class="col-md-3">
                            <span><label>Tipo: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select id="selectTipoPresupuesto" name="selectTipoPresupuesto" class="form-control selectTipoPresupuesto" onchange="fnConfigClavePresupuesto(this.value, 'selectConfiguracionClaveLayout'); fnLimpiarContenidoDiv('divDatosConfigTitulo'); fnLimpiarContenidoDiv('divDatosConfig');">
                                <option value="-1">Seleccionar...</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-inline row">
                        <div class="col-md-3">
                            <span><label>Configuración: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select id="selectConfiguracionClaveLayout" name="selectConfiguracionClaveLayout" class="form-control selectGeneral" onchange="fnObtenerConfiguracionClave()">
                                <option value="-1">Seleccionar...</option>
                            </select>
                        </div>
                    </div>
                </div> 
                <div class="col-md-4">
                    <component-text-label label="No. Oficio:" id="txtNoOficio" name="txtNoOficio" placeholder="No. Oficio" title="No. Oficio"></component-text-label>
                </div>
                <div class="row"></div>
                <br><br>
                <div class="col-md-4">
                    <component-number-label label="Fila Inicio: " id="txtFilaInicio" name="txtFilaInicio" placeholder="Fila Inicio" title="Fila Inicio" value="2"></component-number-label>
                </div>
                <div class="col-md-4">
                </div>
                <div class="col-md-4">
                </div>
              </div>
            </div>
        </div>
    </div>

    <div align="center" id="divDatosConfigTitulo" name="divDatosConfigTitulo"></div>
    <div class="panel-body" style="overflow: scroll;">
        <div id="divDatosConfig" name="divDatosConfig"></div>
    </div>

    <div align="center">
        <label>Cargar Archivo: </label>
        <input type="file" id="txtCatalogo" name="txtCatalogo" title="Seleccionar Catalogo" />
        <br><br>
        <button type="submit" name="btnCargarSubmit" id="btnCargarSubmit" style="display: none;">Cargar</button>
        <component-button id="btnCargarLayout" nama="btnCargarLayout" value="Cargar" onclick="fnCargarLayoutPrueba()" class="glyphicon glyphicon-thumbs-up"></component-button>
    </div>
</form>
<script type="text/javascript" src="javascripts/CargaPresupuestoIngresos_Layout.js?<?php echo rand(); ?>"></script>
<?php
include 'includes/footer_Index.inc';
?>