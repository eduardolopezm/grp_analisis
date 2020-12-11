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
    include('includes/session.inc');
    include('includes/SQL_CommonFunctions.inc');
    $funcion=408;
    include('includes/SecurityFunctions.inc');
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

if (isset($_POST['btnCargar'])) {
    $File_Arcchivos = $_FILES['txtCatalogo']['name'];
    if ($File_Arcchivos != '') {
        // Cargo la hoja de cálculo
        $objPHPExcel = PHPExcel_IOFactory::load($_FILES['txtCatalogo']['tmp_name']);

        //Asigno la hoja de calculo activa
        $objPHPExcel->setActiveSheetIndex(0);
        //Obtengo el numero de filas del archivo
        $numRows = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();

        if (isset($_POST ['checkUR'])) {
            for ($i = 4; $i <= $numRows; $i++) { // $numRows
                $clave = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                $descripcion = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();

                if (!empty($clave)) {
                    $sql = "SELECT * FROM tb_cat_unidades_responsables WHERE ur = '$clave' AND desc_ur = '$descripcion'";
                    $Result = DB_query($sql, $db);

                    if (DB_num_rows($ReFuntion) == 0) {
                        $sql = "INSERT INTO tb_cat_unidades_responsables(
                                        ur,
                                        desc_ur,
                                        active
                                        )
                                        VALUES(
                                        '".($clave)."',
                                        '".($descripcion)."',
                                        '1'
                                        )";
                        $Result = DB_query($sql, $db);

                        if ($Result == false) {
                            prnMsg("Al ingresar ".$clave, "error");
                        }
                    }
                }
            }
        } elseif (isset($_POST ['checkUE'])) {
            for ($i = 4; $i <= $numRows; $i++) { // $numRows
                $ue = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                $descripcion = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();

                if (!empty($ue)) {
                    $sql = "INSERT INTO tb_cat_unidades_ejecutoras(
                                    ur,
                                    ue,
                                    desc_ue,
                                    active,
                                    ln_aux1
                                    )
                                    VALUES(
                                    '".substr($ue, 0, 3)."',
                                    '".substr($ue, 3, 4)."',
                                    '".($descripcion)."',
                                    '1',
                                    '".$ue."'
                                    )";
                    $Result = DB_query($sql, $db);

                    if ($Result == false) {
                        prnMsg("Al ingresar ".$clave, "error");
                    }
                }
            }
        } elseif (isset($_POST ['checkRamo'])) {
            for ($i = 2; $i <= $numRows; $i++) { // $numRows
                $clave = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                $descripcion = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();

                if (!empty($clave)) {
                    $sql = "INSERT INTO g_cat_ramo(
                                    cve_ramo,
                                    desc_ramo,
                                    active
                                    )
                                    VALUES(
                                    '".($clave)."',
                                    '".($descripcion)."',
                                    '1'
                                    )";
                    $Result = DB_query($sql, $db);

                    if ($Result == false) {
                        prnMsg("Al ingresar ".$clave, "error");
                    }
                }
            }
        } elseif (isset($_POST ['checkURG'])) {
            for ($i = 4; $i <= $numRows; $i++) { // $numRows
                $clave = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                $descripcion = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();

                if (!empty($clave)) {
                    $sql = "INSERT INTO `tags` (`tagref`, `tagdescription`, `areacode`, `legalid`, `tagname`, `u_department`, `address1`, `address2`, `address3`, `address4`, `address5`, `address6`, `cp`, `typeinvoice`, `datechange`, `phone`, `logotag`, `email`, `typegroup`, `lastUpdated`, `typetax`, `showflujo`, `tagsupplier`, `tagdebtorno`, `typepack`, `allowpartialinvoice`, `pofootertext`, `agentextag`, `allowpartialnotecredit`, `preferential`, `tagactive`, `ln_tipo`, `nu_interior`)
                    VALUES
                        ('".$clave."', '".$descripcion."', 'ADM', '1', 'Secretaría', 124, 'CALLE', '10', 'CONOCIDO', 'Cd MEXICO', 'MEXICO', 'MEXICO', '12345', 4, NULL, NULL, NULL, NULL, 0, '1900-01-01 00:00:00', 0, 1, '', '', 1, '1', NULL, NULL, 1, 0, 1, NULL, NULL);
                    ";
                    $Result = DB_query($sql, $db);

                    if ($Result == false) {
                        prnMsg("Al ingresar ".$clave, "error");
                    }
                }
            }
        } elseif (isset($_POST ['checkPresupuesto'])) {
            $type = 1;
            $transno = 1;
            $tipoMovPre = $_POST['cmbTipoMov'];

            for ($i = 3; $i <= $numRows; $i++) { // $numRows
                $año = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                $ramo = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                $ur = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
                $ue = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
                $edo = $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();
                $fi = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();
                $fn = $objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue();
                $sf = $objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue();
                $rg = $objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue();
                $partida = $objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue();
                $pp = $objPHPExcel->getActiveSheet()->getCell('K'.$i)->getCalculatedValue();
                $cp = $objPHPExcel->getActiveSheet()->getCell('L'.$i)->getCalculatedValue();
                $ai = $objPHPExcel->getActiveSheet()->getCell('M'.$i)->getCalculatedValue();
                $tg = $objPHPExcel->getActiveSheet()->getCell('N'.$i)->getCalculatedValue();
                $ff = $objPHPExcel->getActiveSheet()->getCell('O'.$i)->getCalculatedValue();
                $geo = $objPHPExcel->getActiveSheet()->getCell('P'.$i)->getCalculatedValue();
                $ppi = $objPHPExcel->getActiveSheet()->getCell('Q'.$i)->getCalculatedValue();

                $original = $objPHPExcel->getActiveSheet()->getCell('R'.$i)->getCalculatedValue();
                
                $enero = $objPHPExcel->getActiveSheet()->getCell('S'.$i)->getCalculatedValue();
                $febrero = $objPHPExcel->getActiveSheet()->getCell('T'.$i)->getCalculatedValue();
                $marzo = $objPHPExcel->getActiveSheet()->getCell('U'.$i)->getCalculatedValue();
                $abril = $objPHPExcel->getActiveSheet()->getCell('V'.$i)->getCalculatedValue();
                $mayo = $objPHPExcel->getActiveSheet()->getCell('W'.$i)->getCalculatedValue();
                $junio = $objPHPExcel->getActiveSheet()->getCell('X'.$i)->getCalculatedValue();
                $julio = $objPHPExcel->getActiveSheet()->getCell('Y'.$i)->getCalculatedValue();
                $agosto = $objPHPExcel->getActiveSheet()->getCell('Z'.$i)->getCalculatedValue();
                $septiembre = $objPHPExcel->getActiveSheet()->getCell('AA'.$i)->getCalculatedValue();
                $octubre = $objPHPExcel->getActiveSheet()->getCell('AB'.$i)->getCalculatedValue();
                $noviembre = $objPHPExcel->getActiveSheet()->getCell('AC'.$i)->getCalculatedValue();
                $diciembre = $objPHPExcel->getActiveSheet()->getCell('AD'.$i)->getCalculatedValue();

                if (strlen($ramo) == 1) {
                    $ramo = '0'.$ramo;
                }

                $clave = $año."-".$ramo."-".$ur."-".$ue."-".$edo."-".
                        $fi."-".$fn."-".$sf."-".$rg."-".$partida."-".
                        $pp."-".$cp."-".$ai."-".$tg."-".$ff."-".
                        $geo."-".$ppi;

                if (!empty($clave)) {
                    // $sql = "INSERT INTO `chartdetailsbudgetbytag`
                    // (`accountcode`, `budget`,
                    // `anho`, `cve_ramo`, `tagref`, `ue`, `edo`,
                    // `id_finalidad`, `id_funcion`, `id_subfuncion`, `cprg`, `cain`,
                    // `cppt`, `cp`, `partida_esp`, `ctga`, `cfin`,
                    // `cgeo`, `pyin`, `original`, `enero`, `febrero`,
                    // `marzo`, `abril`, `mayo`, `junio`, `julio`,
                    // `agosto`, `septiembre`, `octubre`, `noviembre`, `diciembre`)
                    // VALUES
                    // ('".$clave."', '".str_replace(',', '', $original)."',
                    // '".$año."','".$ramo."','".$ur."','".$ue."','".$edo."',
                    // '".$fi."','".$fn."','".$sf."','".$rg."','".$ai."',
                    // '".$pp."','".$cp."','".$partida."','".$tg."','".$ff."',
                    // '".$geo."','".$ppi."','".str_replace(',', '', $original)."','".str_replace(',', '', $enero)."','".str_replace(',', '', $febrero)."',
                    // '".str_replace(',', '', $marzo)."','".str_replace(',', '', $abril)."','".str_replace(',', '', $mayo)."','".str_replace(',', '', $junio)."','".str_replace(',', '', $julio)."',
                    // '".str_replace(',', '', $agosto)."','".str_replace(',', '', $septiembre)."','".str_replace(',', '', $octubre)."','".str_replace(',', '', $noviembre)."','".str_replace(',', '', $diciembre)."'
                    // )";
                    // $Result = DB_query($sql, $db);
                    $sqlPresupuestoLog = "";

                    $periodo = fnGetPeriodSinValidar('15/01/'.$año, $db);
                    $sqlPresupuestoLog .= "
                     (
                    '".$_SESSION['UserID']."',
                    '".str_replace(',', '', $enero)."',
                    '".$clave."',
                    '".$type."',
                    '".$transno."',
                    '',
                    '".$ur."',        
                    '".$periodo."',      
                    '".$partida."',        
                    '1',
                    '',
                    NOW(),
                    NOW(),
                    NOW(),
                    '".$tipoMovPre."'
                    )";

                    $periodo = fnGetPeriodSinValidar('15/02/'.$año, $db);
                    $sqlPresupuestoLog .= "
                    , (
                    '".$_SESSION['UserID']."',
                    '".str_replace(',', '', $febrero)."',
                    '".$clave."',
                    '".$type."',
                    '".$transno."',
                    '',
                    '".$ur."',        
                    '".$periodo."',      
                    '".$partida."',        
                    '1',
                    '',
                    NOW(),
                    NOW(),
                    NOW(),
                    '".$tipoMovPre."'
                    )";

                    $periodo = fnGetPeriodSinValidar('15/03/'.$año, $db);
                    $sqlPresupuestoLog .= "
                    , (
                    '".$_SESSION['UserID']."',
                    '".str_replace(',', '', $marzo)."',
                    '".$clave."',
                    '".$type."',
                    '".$transno."',
                    '',
                    '".$ur."',        
                    '".$periodo."',      
                    '".$partida."',        
                    '1',
                    '',
                    NOW(),
                    NOW(),
                    NOW(),
                    '".$tipoMovPre."'
                    )";

                    $periodo = fnGetPeriodSinValidar('15/04/'.$año, $db);
                    $sqlPresupuestoLog .= "
                    , (
                    '".$_SESSION['UserID']."',
                    '".str_replace(',', '', $abril)."',
                    '".$clave."',
                    '".$type."',
                    '".$transno."',
                    '',
                    '".$ur."',        
                    '".$periodo."',      
                    '".$partida."',        
                    '1',
                    '',
                    NOW(),
                    NOW(),
                    NOW(),
                    '".$tipoMovPre."'
                    )";
                    
                    $periodo = fnGetPeriodSinValidar('15/05/'.$año, $db);
                    $sqlPresupuestoLog .= "
                    , (
                    '".$_SESSION['UserID']."',
                    '".str_replace(',', '', $mayo)."',
                    '".$clave."',
                    '".$type."',
                    '".$transno."',
                    '',
                    '".$ur."',        
                    '".$periodo."',      
                    '".$partida."',        
                    '1',
                    '',
                    NOW(),
                    NOW(),
                    NOW(),
                    '".$tipoMovPre."'
                    )";
                    
                    $periodo = fnGetPeriodSinValidar('15/06/'.$año, $db);
                    $sqlPresupuestoLog .= "
                    , (
                    '".$_SESSION['UserID']."',
                    '".str_replace(',', '', $junio)."',
                    '".$clave."',
                    '".$type."',
                    '".$transno."',
                    '',
                    '".$ur."',        
                    '".$periodo."',      
                    '".$partida."',        
                    '1',
                    '',
                    NOW(),
                    NOW(),
                    NOW(),
                    '".$tipoMovPre."'
                    )";

                    $periodo = fnGetPeriodSinValidar('15/07/'.$año, $db);
                    $sqlPresupuestoLog .= "
                    , (
                    '".$_SESSION['UserID']."',
                    '".str_replace(',', '', $julio)."',
                    '".$clave."',
                    '".$type."',
                    '".$transno."',
                    '',
                    '".$ur."',        
                    '".$periodo."',      
                    '".$partida."',        
                    '1',
                    '',
                    NOW(),
                    NOW(),
                    NOW(),
                    '".$tipoMovPre."'
                    )";

                    $periodo = fnGetPeriodSinValidar('15/08/'.$año, $db);
                    $sqlPresupuestoLog .= "
                    , (
                    '".$_SESSION['UserID']."',
                    '".str_replace(',', '', $agosto)."',
                    '".$clave."',
                    '".$type."',
                    '".$transno."',
                    '',
                    '".$ur."',        
                    '".$periodo."',      
                    '".$partida."',        
                    '1',
                    '',
                    NOW(),
                    NOW(),
                    NOW(),
                    '".$tipoMovPre."'
                    )";

                    $periodo = fnGetPeriodSinValidar('15/09/'.$año, $db);
                    $sqlPresupuestoLog .= "
                    , (
                    '".$_SESSION['UserID']."',
                    '".str_replace(',', '', $septiembre)."',
                    '".$clave."',
                    '".$type."',
                    '".$transno."',
                    '',
                    '".$ur."',        
                    '".$periodo."',      
                    '".$partida."',        
                    '1',
                    '',
                    NOW(),
                    NOW(),
                    NOW(),
                    '".$tipoMovPre."'
                    )";

                    $periodo = fnGetPeriodSinValidar('15/10/'.$año, $db);
                    $sqlPresupuestoLog .= "
                    , (
                    '".$_SESSION['UserID']."',
                    '".str_replace(',', '', $octubre)."',
                    '".$clave."',
                    '".$type."',
                    '".$transno."',
                    '',
                    '".$ur."',        
                    '".$periodo."',      
                    '".$partida."',        
                    '1',
                    '',
                    NOW(),
                    NOW(),
                    NOW(),
                    '".$tipoMovPre."'
                    )";

                    $periodo = fnGetPeriodSinValidar('15/11/'.$año, $db);
                    $sqlPresupuestoLog .= "
                    , (
                    '".$_SESSION['UserID']."',
                    '".str_replace(',', '', $noviembre)."',
                    '".$clave."',
                    '".$type."',
                    '".$transno."',
                    '',
                    '".$ur."',        
                    '".$periodo."',      
                    '".$partida."',        
                    '1',
                    '',
                    NOW(),
                    NOW(),
                    NOW(),
                    '".$tipoMovPre."'
                    )";

                    $periodo = fnGetPeriodSinValidar('15/12/'.$año, $db);
                    $sqlPresupuestoLog .= "
                    , (
                    '".$_SESSION['UserID']."',
                    '".str_replace(',', '', $diciembre)."',
                    '".$clave."',
                    '".$type."',
                    '".$transno."',
                    '',
                    '".$ur."',        
                    '".$periodo."',      
                    '".$partida."',        
                    '1',
                    '',
                    NOW(),
                    NOW(),
                    NOW(),
                    '".$tipoMovPre."'
                    )";
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
                    nu_tipo_movimiento
                    )
                    VALUES ".$sqlPresupuestoLog;

                    $ErrMsg = "No se agrego log al presupuesto ";
                    $transResult2 = DB_query($SQL, $db, $ErrMsg);
                }
            }


            echo "<BR>PROCESO FINALIZADO<BR>";
        } elseif (isset($_POST ['checkJusR'])) {
            for ($i = 2; $i <= $numRows; $i++) { // $numRows
                $clave = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                $descripcion = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();

                if (!empty($clave)) {
                    $sql = "SELECT * FROM tb_jusr WHERE nu_cat_jusr = '$clave'";
                    $Result = DB_query($sql, $db);

                    if (DB_num_rows($ReFuntion) == 0) {
                        $sql = "INSERT INTO tb_jusr(
                                        nu_cat_jusr,
                                        txt_descripcion,
                                        sn_activo
                                        )
                                        VALUES(
                                        '".($clave)."',
                                        '".($descripcion)."',
                                        '1'
                                        )";
                        $Result = DB_query($sql, $db);

                        if ($Result == false) {
                            prnMsg("Al ingresar ".$clave, "error");
                        }
                    }
                }
            }
        } elseif (isset($_POST ['checkConcR23'])) {
            for ($i = 2; $i <= $numRows; $i++) { // $numRows
                $clave = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                $descripcion = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                $ciclo = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
                $descripcionCiclo = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();

                if (!empty($clave)) {
                    $sql = "SELECT * FROM tb_conc_r23 WHERE nu_r23 = '$clave'";
                    $Result = DB_query($sql, $db);

                    if (DB_num_rows($ReFuntion) == 0) {
                        $sql = "INSERT INTO tb_conc_r23(
                                        nu_r23,
                                        txt_descripcion,
                                        sn_activo,
                                        nu_ciclo,
                                        txt_descripcion_ciclo
                                        )
                                        VALUES(
                                        '".($clave)."',
                                        '".($descripcion)."',
                                        '1',
                                        '".($ciclo)."',
                                        '".($descripcionCiclo)."'
                                        )";
                        $Result = DB_query($sql, $db);

                        if ($Result == false) {
                            prnMsg("Al ingresar ".$clave, "error");
                        }
                    }
                }
            }
        } elseif (isset($_POST ['checkCabm'])) {
            for ($i = 2; $i <= $numRows; $i++) { // $numRows
                $clave = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                $niv = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                $partida = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
                $partidaAnt = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
                $cod = $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();
                $descripcion = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();

                if (!empty($clave)) {
                    $sql = "SELECT * FROM tb_partida_articulo WHERE eq_stockid = '$clave'";
                    $Result = DB_query($sql, $db);

                    if (DB_num_rows($Result) == 0) {
                        $sql = "INSERT INTO `tb_partida_articulo` (`eq_stockid`, `niv`, `partidaEspecifica`, `descPartidaEspecifica`, `sn_partida_ant`, `sn_codigo`)
                        VALUES
                            ('".$clave."', '".$niv."', '".$partida."', '".$descripcion."', '".$partidaAnt."', '".$cod."')
                        ";
                        $Result = DB_query($sql, $db);

                        if ($Result == false) {
                            prnMsg("Al ingresar ".$clave, "error");
                        }
                    }
                }
            }
        } elseif (isset($_POST ['checkInsertCuentas'])) {
            for ($i = 2; $i <= $numRows; $i++) { // $numRows
                $accountcode = $objPHPExcel->getActiveSheet()->getCell('K'.$i)->getCalculatedValue();
                $descripcion = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();
                $identificador = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();

                if (!empty($accountcode)) {
                    $arrayCuenta=explode('.', $accountcode);
                    $num = 1;
                    $groupcode = "";
                    for ($i2=0; $i2<count($arrayCuenta); $i2++) {
                        if (($i2 + 1) < count($arrayCuenta)) {
                            if (empty($groupcode)) {
                                $groupcode .= "".$arrayCuenta[$i2];
                            } else {
                                $groupcode .= ".".$arrayCuenta[$i2];
                            }
                        }

                        $num ++;
                    }

                    $sql = "INSERT INTO `chartmaster` (`accountcode`, `accountname`, `group_`, `naturaleza`, `tipo`, `accountnameing`, `sectionnameing`, `formula`, `groupcode`, `reporte_group`, `ln_clave`, `nu_nivel`)
                    VALUES 
                    ('".$accountcode."', '".$descripcion."', 'ACTIVO', 1, '1', NULL, NULL, NULL, '".$groupcode."', '', '".$identificador."', '".fnNivelesCuentaContableGeneral($db, $accountcode)."')";
                    $Result = DB_query($sql, $db);

                    if ($Result == false) {
                        prnMsg("Al ingresar ".$accountcode." - Linea: ".$i, "error");
                    }
                }
            }
        } elseif (isset($_POST ['checkInsertDevengado'])) {
            for ($i = 2; $i <= $numRows; $i++) { // $numRows
                $partida = $objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue();
                $descripcion = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
                $identificador = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                $tipoGasto = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
                $stockact = $objPHPExcel->getActiveSheet()->getCell('L'.$i)->getCalculatedValue();
                $accountegreso = $objPHPExcel->getActiveSheet()->getCell('M'.$i)->getCalculatedValue();
                $adjglact = $objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue();
                $ln_abono_salida = $objPHPExcel->getActiveSheet()->getCell('K'.$i)->getCalculatedValue();

                $partida = substr($partida, 0, 3);

                $adjglact = "";
                $ln_abono_salida = "";
                $descripcion = "";
                $identificador = "";
                $tipoGasto = 1;

                if (!empty($partida)) {
                    $sql = "INSERT INTO `stockcategory2` (`categoryid`, `categorydescription`, `stocktype`, `stockact`, `adjglact`, `purchpricevaract`, `materialuseagevarac`, `wipact`, `adjglacttransf`, `allowNarrativePOLine`, `margenaut`, `prodLineId`, `redinvoice`, `minimummarginsales`, `idflujo`, `disabledprice`, `internaluse`, `warrantycost`, `cashdiscount`, `stockconsignmentact`, `margenautcost`, `typeoperationdiot`, `deductibleflag`, `u_typeoperation`, `textimage`, `image`, `glcodebydelivery`, `discountInPriceListOnPrice`, `discountInComercialOnPrice`, `cattipodescripcion`, `countword`, `generaPublicacionAutomatica`, `salesplanning`, `ordendesplegar`, `optimo`, `minimo`, `maximo`, `CodigoPanelControl`, `MensajeOC`, `MensajePV`, `stockshipty`, `showmovil`, `factesquemadoancho`, `factesquemadoalto`, `disabledcosto`, `belowcost`, `accounttransfer`, `diascaducidad`, `accountegreso`, `accountingreso`, `nu_tipo_gasto`, `ln_abono_salida`, `ln_clave`)
                    VALUES
                    ('".$partida."', '".$descripcion."', 'F', '".$stockact."', '".$adjglact."', '0', '0', '0', 0, 0, 0, '0', 0, 0, 0, 0, 0, 0, 0, NULL, 0, 0, 0, 0, NULL, NULL, '', 0, 0, NULL, '0', NULL, 0, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, '0', 0, 0, 0, '0', NULL, 0, '".$accountegreso."', NULL, '".$tipoGasto."', '".$ln_abono_salida."', '".$identificador."')";
                    $Result = DB_query($sql, $db);

                    if ($Result == false) {
                        prnMsg("Al ingresar ".$partida." - Linea: ".$i, "error");
                    }
                }
            }
        } elseif (isset($_POST ['checkInsertContribuyente'])) {
            $SQL = "INSERT INTO `debtorsmaster` (`debtorno`, `name`, `name1`, `name2`, `name3`, `curp`, `address1`, `address2`, `address3`, `address4`, `address5`, `address6`, `currcode`, `salestype`, `clientsince`, `holdreason`, `paymentterms`, `discount`, `pymtdiscount`, `lastpaid`, `lastpaiddate`, `creditlimit`, `invaddrbranch`, `discountcode`, `ediinvoices`, `ediorders`, `edireference`, `editransport`, `ediaddress`, `ediserveruser`, `ediserverpwd`, `taxref`, `customerpoline`, `typeid`, `daygrace`, `coments`, `blacklist`, `ruta`, `nameextra`, `fechanacimiento`, `lugarnacimiento`, `telefonocelular`, `ingresosmensuales`, `estadocivil`, `mediocontacto`, `NacionalidadId`, `CapacidadCompraId`, `razoncompra`, `pagpersonal`, `idCapComIngresos`, `companyprospect`, `prospectsince`, `userprospect`, `usoCFDI`, `NumRegIdTrib`, `RIF`, `package_id`, `app`, `tipo`, `regimenFiscal_id`, `numRegistro`, `reqComprobante`, `tipoDir`, `numExt`, `numInt`, `ext`, `direccion`, `distrito`, `activo`)
            VALUES ";

            $SQL2 = "INSERT INTO `custbranch` (`branchcode`, `debtorno`, `brname`, `taxid`, `braddress1`, `braddress2`, `braddress3`, `braddress4`, `braddress5`, `braddress6`, `lat`, `lng`, `estdeliverydays`, `area`, `salesman`, `fwddate`, `phoneno`, `faxno`, `contactname`, `email`, `lineofbusiness`, `flagworkshop`, `defaultlocation`, `taxgroupid`, `defaultshipvia`, `deliverblind`, `disabletrans`, `brpostaddr1`, `brpostaddr2`, `brpostaddr3`, `brpostaddr4`, `brpostaddr5`, `brpostaddr6`, `specialinstructions`, `custbranchcode`, `creditlimit`, `custdata1`, `custdata2`, `custdata3`, `custdata4`, `custdata5`, `custdata6`, `ruta`, `paymentname`, `nocuenta`, `fecha_modificacion`, `namebank`, `brnumint`, `brnumext`, `movilno`, `nextelno`, `logocliente`, `descclientecomercial`, `descclientepropago`, `welcomemail`, `custpais`, `SectComClId`, `NumeAsigCliente`, `descclienteop`, `typeaddenda`, `idprospecmedcontacto`, `idproyecto`, `braddress7`, `DiasRevicion`, `DiasPago`, `prefer`, `discountcard`, `typecomplement`)
            VALUES ";

            $SQL3 = "";

            for ($i = 2; $i <= $numRows; $i++) { // $numRows
                $clave = trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue());
                $tipoPersona = trim($objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue());
                $razonsocial = trim($objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue());
                $materno = trim($objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue());
                $nombre = trim($objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue());
                $rfc = trim($objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue());
                $usocfdi = trim($objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue());
                $numregext = trim($objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue());
                $reqcomprobante = trim($objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue());
                $email = trim($objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue());
                $tipodireccion = trim($objPHPExcel->getActiveSheet()->getCell('K'.$i)->getCalculatedValue());
                $pais = trim($objPHPExcel->getActiveSheet()->getCell('L'.$i)->getCalculatedValue());
                $estado = trim($objPHPExcel->getActiveSheet()->getCell('M'.$i)->getCalculatedValue());
                $region = trim($objPHPExcel->getActiveSheet()->getCell('N'.$i)->getCalculatedValue());
                $distrito = trim($objPHPExcel->getActiveSheet()->getCell('O'.$i)->getCalculatedValue());
                $poblacion = trim($objPHPExcel->getActiveSheet()->getCell('P'.$i)->getCalculatedValue());
                $calle = trim($objPHPExcel->getActiveSheet()->getCell('Q'.$i)->getCalculatedValue());
                $noext = trim($objPHPExcel->getActiveSheet()->getCell('R'.$i)->getCalculatedValue());
                $noint = trim($objPHPExcel->getActiveSheet()->getCell('S'.$i)->getCalculatedValue());
                $cp = trim($objPHPExcel->getActiveSheet()->getCell('T'.$i)->getCalculatedValue());
                $tel = trim($objPHPExcel->getActiveSheet()->getCell('U'.$i)->getCalculatedValue());
                $ext = trim($objPHPExcel->getActiveSheet()->getCell('V'.$i)->getCalculatedValue());
                $telmovil = trim($objPHPExcel->getActiveSheet()->getCell('W'.$i)->getCalculatedValue());
                // $SQL = "INSERT INTO `debtorsmaster` (`debtorno`, `name`, `name1`, `name2`, `name3`, `curp`, `address1`, `address2`, `address3`, `address4`, `address5`, `address6`, `currcode`, `salestype`, `clientsince`, `holdreason`, `paymentterms`, `discount`, `pymtdiscount`, `lastpaid`, `lastpaiddate`, `creditlimit`, `invaddrbranch`, `discountcode`, `ediinvoices`, `ediorders`, `edireference`, `editransport`, `ediaddress`, `ediserveruser`, `ediserverpwd`, `taxref`, `customerpoline`, `typeid`, `daygrace`, `coments`, `blacklist`, `ruta`, `nameextra`, `fechanacimiento`, `lugarnacimiento`, `telefonocelular`, `ingresosmensuales`, `estadocivil`, `mediocontacto`, `NacionalidadId`, `CapacidadCompraId`, `razoncompra`, `pagpersonal`, `idCapComIngresos`, `companyprospect`, `prospectsince`, `userprospect`, `usoCFDI`, `NumRegIdTrib`, `RIF`, `package_id`, `app`, `tipo`, `regimenFiscal_id`, `numRegistro`, `reqComprobante`, `tipoDir`, `numExt`, `numInt`, `ext`, `direccion`, `distrito`, `activo`)
                //             VALUES
                //             ";
                $SQL .= "
                ('".$clave."', '".$razonsocial."', '".$razonsocial."', '".$materno."', '".$nombre."', '', '".$calle."', '".$distrito."', '$poblacion', '".$estado."', '".$cp."', '', 'MXN', 'L1', '0000-00-00 00:00:00', 0, '01', 0, 0, 0, NULL, 1000, 0, '', 0, 0, '', 'jesus', '', '', '', '', 0, 1, '', NULL, 0, 0, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'P01', NULL, NULL, NULL, 0, '".$tipoPersona."', '', '', '1', 'Foránea', '".$noext."', '', '', '$calle $distrito $poblacion $estado $cp', '".$distrito."', '1'), ";

                $SQL2 .= "
                ('".$clave."', '".$clave."', '".$razonsocial."', '".$rfc."', '".$calle."', '".$distrito."', '".$estado."', '".$cp."', '', '".$distrito."', 0.000000, 0.000000, 1, '', '', 0, '0', '', '', '".$email."', '', 0, '', 1, 1, 1, 0, '".$calle."', '".$distrito."', '".$distrito."', '".$estado."', '', '', '0', '', 1000, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, '1900-01-01 00:00:00', NULL, 'B', '".$noext."', '".$telmovil."', NULL, NULL, 0.00, 0.00, '0', '".$pais."', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0), ";

                $SQL3 .= "
                UPDATE debtorsmaster SET tipo = '".$tipoPersona."' WHERE debtorno = '".$clave."'; ";

            }

            echo "<pre>".$SQL3."</pre>";
            // echo "<pre>".$SQL2."</pre>";
        } elseif (isset($_POST ['checkContribuyenteTemporal'])) {
            $SQL = "INSERT INTO `contribuyentesexcel` (`clave`, `persona`, `razon`, `materno`, `nombre`, `rfc`, `cfdi`, `numReg`, `comprobante`, `email`, `tipodir`, `pais`, `estado`, `region`, `distrito`, `poblacion`, `calle`, `exterior`, `interior`, `cp`, `tel`, `ext`, `telmovil`) 
            VALUES ";

            $SQL2 = "";
            $numRegistros = 0;

            for ($i = 2; $i <= $numRows; $i++) { // $numRows
                $clave = trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue());
                $tipoPersona = trim($objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue());
                $razonsocial = trim($objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue());
                $materno = trim($objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue());
                $nombre = trim($objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue());
                $rfc = trim($objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue());
                $usocfdi = trim($objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue());
                $numregext = trim($objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue());
                $reqcomprobante = trim($objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue());
                $email = trim($objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue());
                $tipodireccion = trim($objPHPExcel->getActiveSheet()->getCell('K'.$i)->getCalculatedValue());
                $pais = trim($objPHPExcel->getActiveSheet()->getCell('L'.$i)->getCalculatedValue());
                $estado = trim($objPHPExcel->getActiveSheet()->getCell('M'.$i)->getCalculatedValue());
                $region = trim($objPHPExcel->getActiveSheet()->getCell('N'.$i)->getCalculatedValue());
                $distrito = trim($objPHPExcel->getActiveSheet()->getCell('O'.$i)->getCalculatedValue());
                $poblacion = trim($objPHPExcel->getActiveSheet()->getCell('P'.$i)->getCalculatedValue());
                $calle = trim($objPHPExcel->getActiveSheet()->getCell('Q'.$i)->getCalculatedValue());
                $noext = trim($objPHPExcel->getActiveSheet()->getCell('R'.$i)->getCalculatedValue());
                $noint = trim($objPHPExcel->getActiveSheet()->getCell('S'.$i)->getCalculatedValue());
                $cp = trim($objPHPExcel->getActiveSheet()->getCell('T'.$i)->getCalculatedValue());
                $tel = trim($objPHPExcel->getActiveSheet()->getCell('U'.$i)->getCalculatedValue());
                $ext = trim($objPHPExcel->getActiveSheet()->getCell('V'.$i)->getCalculatedValue());
                $telmovil = trim($objPHPExcel->getActiveSheet()->getCell('W'.$i)->getCalculatedValue());

                if (empty($SQL2)) {
                    $SQL2 .= "
                    ('".$clave."', '".$tipoPersona."', '".$razonsocial."', '".$materno."', '".$nombre."', '".$rfc."', '".$usocfdi."', '".$numregext."', '".$reqcomprobante."', '".$email."', '".$tipodireccion."', '".$pais."', '".$estado."', '".$region."', '".$distrito."', '".$poblacion."', '".$calle."', '".$noext."', '".$noint."', '".$cp."', '".$tel."', '".$ext."', '".$telmovil."')";
                } else {
                    $SQL2 .= "
                    , ('".$clave."', '".$tipoPersona."', '".$razonsocial."', '".$materno."', '".$nombre."', '".$rfc."', '".$usocfdi."', '".$numregext."', '".$reqcomprobante."', '".$email."', '".$tipodireccion."', '".$pais."', '".$estado."', '".$region."', '".$distrito."', '".$poblacion."', '".$calle."', '".$noext."', '".$noint."', '".$cp."', '".$tel."', '".$ext."', '".$telmovil."')";
                }
                
                if ($numRegistros == 500) {
                    $Result = DB_query($SQL.$SQL2, $db);
                    $SQL2 = "";
                    $numRegistros = 0;
                }

                $numRegistros ++;
            }

            if (!empty($SQL2)) {
                $Result = DB_query($SQL.$SQL2, $db);
                $SQL2 = "";
            }

            // echo "<pre>".$SQL3."</pre>";
            // echo "<pre>".$SQL2."</pre>";
        } elseif (isset($_POST ['checkobjconceme'])) {
            $SQL = "INSERT INTO `objcontratoscementerios` (`objcontrato`,`objprincipal`,`denominaobjcontrato`,`noexis`,`intcial`,`ctacontra`,`denomctacontrato`,`descvalcon`,`comentario`,`idfosa`,`panteon`,`seccion`,`tramo`,`fila`,`foslibro`,`nolibro`,`lote`,`opprincipal`,`opparcial`,`fechaini`,`monoto`,`tipodepe`,`inactivo`,`especial`) 
            VALUES ";

            $SQL2 = "";
            $numRegistros = 0;

            for ($i = 2; $i <= $numRows; $i++) { // $numRows
                $objcontrato = trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue());
                $objprincipal = trim($objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue());
                $denominaobjcontrato = trim($objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue());
                $noexis = trim($objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue());
                $intcial = trim($objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue());
                $ctacontra = trim($objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue());
                $denomctacontrato = trim($objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue());
                $descvalcon = trim($objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue());
                $comentario = trim($objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue());
                $idfosa = trim($objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue());
                $panteon = trim($objPHPExcel->getActiveSheet()->getCell('K'.$i)->getCalculatedValue());
                $seccion = trim($objPHPExcel->getActiveSheet()->getCell('L'.$i)->getCalculatedValue());
                $tramo = trim($objPHPExcel->getActiveSheet()->getCell('M'.$i)->getCalculatedValue());
                $fila = trim($objPHPExcel->getActiveSheet()->getCell('N'.$i)->getCalculatedValue());
                $foslibro = trim($objPHPExcel->getActiveSheet()->getCell('O'.$i)->getCalculatedValue());
                $nolibro = trim($objPHPExcel->getActiveSheet()->getCell('P'.$i)->getCalculatedValue());
                $lote = trim($objPHPExcel->getActiveSheet()->getCell('Q'.$i)->getCalculatedValue());
                $opprincipal = trim($objPHPExcel->getActiveSheet()->getCell('R'.$i)->getCalculatedValue());
                $opparcial = trim($objPHPExcel->getActiveSheet()->getCell('S'.$i)->getCalculatedValue());
                $fechaini = trim($objPHPExcel->getActiveSheet()->getCell('T'.$i)->getCalculatedValue());
                $monoto = trim($objPHPExcel->getActiveSheet()->getCell('U'.$i)->getCalculatedValue());
                $tipodepe = trim($objPHPExcel->getActiveSheet()->getCell('V'.$i)->getCalculatedValue());
                $inactivo = trim($objPHPExcel->getActiveSheet()->getCell('W'.$i)->getCalculatedValue());
                $especial = trim($objPHPExcel->getActiveSheet()->getCell('W'.$i)->getCalculatedValue());

                if (empty($SQL2)) {
                    $SQL2 .= "
                    ('".$objcontrato."', '".$objprincipal."', '".$denominaobjcontrato."', '".$noexis."', '".$intcial."', '".$ctacontra."', '".$denomctacontrato."', '".$descvalcon."', '".$comentario."', '".$idfosa."', '".$panteon."', '".$seccion."', '".$tramo."', '".$fila."', '".$foslibro."', '".$nolibro."', '".$lote."', '".$opprincipal."', '".$opparcial."', '".$fechaini."', '".$monoto."', '".$tipodepe."', '".$inactivo."', '".$especial."')";
                } else {
                    $SQL2 .= "
                    , ('".$objcontrato."', '".$objprincipal."', '".$denominaobjcontrato."', '".$noexis."', '".$intcial."', '".$ctacontra."', '".$denomctacontrato."', '".$descvalcon."', '".$comentario."', '".$idfosa."', '".$panteon."', '".$seccion."', '".$tramo."', '".$fila."', '".$foslibro."', '".$nolibro."', '".$lote."', '".$opprincipal."', '".$opparcial."', '".$fechaini."', '".$monoto."', '".$tipodepe."', '".$inactivo."', '".$especial."')";
                }
                
                if ($numRegistros == 100) {
                    $Result = DB_query($SQL.$SQL2, $db);
                    $SQL2 = "";
                    $numRegistros = 0;
                }

                $numRegistros ++;
            }

            if (!empty($SQL2)) {
                $Result = DB_query($SQL.$SQL2, $db);
                $SQL2 = "";
            }
            // echo "<pre>".$SQL3."</pre>";
            // echo "<pre>".$SQL2."</pre>";
        }else if (isset($_POST['checkporcentaje'])){

            for ($i = 2; $i <= $numRows; $i++) { // $numRows
                $objeto = trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue());
                $porcentaje = trim($objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue());
                                
                $clave1 = $objeto.'_02_2020';
                $SQL = "INSERT INTO `tb_cat_meta` ( `loccode`, `nu_mes`, `nu_anio`, `meta`, `userid`, `nu_estatus`, `clave`)
                VALUES( '".$objeto."', '02', 2020, '".$porcentaje."', 'desarrollo5', 1, '".$clave1."')";
                $Result = DB_query($SQL, $db);  

            }
        }else if(isset($_POST['checktotalesingresosxdia'])){



            for ($i = 2; $i <= $numRows; $i++) { // $numRows
                $id = trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue());
                $folio = trim($objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue());
                $clave = trim($objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue());
                $objetoParcial = trim($objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue());
                $moneyonmymind = trim($objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue());
                $caja = trim($objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue());
                $formaPago = trim($objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue());
                $fecha = trim($objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue());
                $estatus = trim($objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue());
                
                $date = explode("/", $fecha);
                $date[0]; // porción1
                $date[1]; // porción2
                $date[2]; // porción2
    
                $newdate = $date[2].'-'.$date[1].'-'.$date[0];

                
                $SQL = "INSERT INTO `tb_ingresos_totxdia` (`clave`, `folio`, `clave_catastral`, `loccode`, `stockid`, `importe`, `obraid`, `forma_pago`, `estatus`, `userid`, `fecha`)
                        VALUES ('".$id."', '".$folio."', '".$clave."', 'PRE', '".$objetoParcial."', '".$moneyonmymind."', '".$caja."', '".$formaPago."', '".$estatus."', 'desarrollo5', '".$newdate."');";
                $Result = DB_query($SQL, $db);

                
  
            }

        
        }else if (isset($_POST['checktotalesingresos'])){

            for ($i = 8; $i <= 39; $i++) { // $numRows
                $objeto = trim($objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue());
                $enero = trim($objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue());
                $febrero = trim($objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue());
                $marzo = trim($objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue());
                $abril = trim($objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue());
                $mayo = trim($objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue());
                $junio = trim($objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue());
                $julio = trim($objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue());
                $agosto = trim($objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue());
                $septiembre = trim($objPHPExcel->getActiveSheet()->getCell('K'.$i)->getCalculatedValue());
                $octubre = trim($objPHPExcel->getActiveSheet()->getCell('L'.$i)->getCalculatedValue());
                $noviembre = trim($objPHPExcel->getActiveSheet()->getCell('M'.$i)->getCalculatedValue());
        
                $clave1 = $objeto.'_01_2019';
                $SQL = " INSERT INTO `tb_ingresos_totxobj_prin` (`clave`, `loccode`, `nu_mes`, `nu_anio`, `precio`, `userid`)
                VALUES ('".$clave1."', '".$objeto."', 01, 2019, '".$enero."', 'desarrollo5');";
                $Result = DB_query($SQL, $db);  
           
                $clave2 = $objeto.'_02_2019';
                $SQL = " INSERT INTO `tb_ingresos_totxobj_prin` (`clave`, `loccode`, `nu_mes`, `nu_anio`, `precio`, `userid`)
                VALUES ('".$clave2."', '".$objeto."', 02, 2019, '".$febrero."', 'desarrollo5');";
                $Result = DB_query($SQL, $db);

                $clave3 = $objeto.'_03_2019';
                $SQL = " INSERT INTO `tb_ingresos_totxobj_prin` (`clave`, `loccode`, `nu_mes`, `nu_anio`, `precio`, `userid`)
                VALUES ('".$$clave3."', '".$objeto."', 03, 2019, '".$marzo."', 'desarrollo5');";
                $Result = DB_query($SQL, $db);
                
                $clave4 = $objeto.'_04_2019';
                $SQL = " INSERT INTO `tb_ingresos_totxobj_prin` (`clave`, `loccode`, `nu_mes`, `nu_anio`, `precio`, `userid`)
                VALUES ('".$clave4."', '".$objeto."', 04, 2019, '".$abril."', 'desarrollo5');";
                $Result = DB_query($SQL, $db); 
                
                $clave5 = $objeto.'_05_2019';
                $SQL = " INSERT INTO `tb_ingresos_totxobj_prin` (`clave`, `loccode`, `nu_mes`, `nu_anio`, `precio`, `userid`)
                VALUES ('".$clave5."', '".$objeto."', 05, 2019, '".$mayo."', 'desarrollo5');";
                $Result = DB_query($SQL, $db); 

                $clave6 = $objeto.'_06_2019';
                $SQL = " INSERT INTO `tb_ingresos_totxobj_prin` (`clave`, `loccode`, `nu_mes`, `nu_anio`, `precio`, `userid`)
                VALUES ('".$clave6."', '".$objeto."', 06, 2019, '".$junio."', 'desarrollo5');";
                $Result = DB_query($SQL, $db); 

                $clave7 = $objeto.'_07_2019';
                $SQL = " INSERT INTO `tb_ingresos_totxobj_prin` (`clave`, `loccode`, `nu_mes`, `nu_anio`, `precio`, `userid`)
                VALUES ('".$clave7."', '".$objeto."', 07, 2019, '".$julio."', 'desarrollo5');";
                $Result = DB_query($SQL, $db);

                $clave8 = $objeto.'_08_2019';
                $SQL = " INSERT INTO `tb_ingresos_totxobj_prin` (`clave`, `loccode`, `nu_mes`, `nu_anio`, `precio`, `userid`)
                VALUES ('".$clave8."', '".$objeto."', 08, 2019, '".$agosto."', 'desarrollo5');";
                $Result = DB_query($SQL, $db);

                $clave9 = $objeto.'_09_2019';
                $SQL = " INSERT INTO `tb_ingresos_totxobj_prin` (`clave`, `loccode`, `nu_mes`, `nu_anio`, `precio`, `userid`)
                VALUES ('".$clave9."', '".$objeto."', 09, 2019, '".$septiembre."', 'desarrollo5');";
                $Result = DB_query($SQL, $db);

                $clave10 = $objeto.'_10_2019';
                $SQL = " INSERT INTO `tb_ingresos_totxobj_prin` (`clave`, `loccode`, `nu_mes`, `nu_anio`, `precio`, `userid`)
                VALUES ('".$clave10."', '".$objeto."', 10, 2019, '".$octubre."', 'desarrollo5');";
                $Result = DB_query($SQL, $db);

                $clave11 = $objeto.'_11_2019';
                $SQL = " INSERT INTO `tb_ingresos_totxobj_prin` (`clave`, `loccode`, `nu_mes`, `nu_anio`, `precio`, `userid`)
                VALUES ('".$clave11."', '".$objeto."', 11, 2019, '".$noviembre."', 'desarrollo5');";
                $Result = DB_query($SQL, $db);

            }
        }else if (isset($_POST['checkContratosDirectos'])){
            
            for ($i = 2; $i <= $numRows; $i++) { // $numRows
                $idAgrupacion = trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue());
                $idConfiguracion = trim($objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue());
                $contribuyente = trim($objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue());
                $tagref = trim($objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue());
                $ln_ue = trim($objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue());
                $ind_activo = trim($objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue());
                $userid = trim($objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue());
                $enum_status = trim($objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue());
                $enum_periodo = trim($objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue());
                $nu_periodicidad = trim($objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue());
                $dtm_fecha_inicio = trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue());
                $dtm_fecha_vigencia = trim($objPHPExcel->getActiveSheet()->getCell('L'.$i)->getCalculatedValue());
                $ln_descripcion = trim($objPHPExcel->getActiveSheet()->getCell('M'.$i)->getCalculatedValue());
                $idPerido = trim($objPHPExcel->getActiveSheet()->getCell('N'.$i)->getCalculatedValue());
                $objetoPricipal = trim($objPHPExcel->getActiveSheet()->getCell('O'.$i)->getCalculatedValue());
                $objetoParcial = trim($objPHPExcel->getActiveSheet()->getCell('P'.$i)->getCalculatedValue());
                $cantidad = trim($objPHPExcel->getActiveSheet()->getCell('Q'.$i)->getCalculatedValue());
                $importe = trim($objPHPExcel->getActiveSheet()->getCell('R'.$i)->getCalculatedValue());
                $total = trim($objPHPExcel->getActiveSheet()->getCell('S'.$i)->getCalculatedValue());
                $dtVencimiento = trim($objPHPExcel->getActiveSheet()->getCell('T'.$i)->getCalculatedValue());
                $dtPago = trim($objPHPExcel->getActiveSheet()->getCell('U'.$i)->getCalculatedValue());
                $estatusAdeudo = trim($objPHPExcel->getActiveSheet()->getCell('V'.$i)->getCalculatedValue());
                $placa = trim($objPHPExcel->getActiveSheet()->getCell('W'.$i)->getCalculatedValue());
                $boleta = trim($objPHPExcel->getActiveSheet()->getCell('X'.$i)->getCalculatedValue());
                
        

                $SQL = "INSERT INTO `tb_contratos` ( `id_confcontratos`, `id_debtorno`, `tagref`, `ln_ue`, `ind_activo`, `userid`, `dtm_fecha_efectiva`,  `enum_status`, `enum_periodo`, `nu_periodicidad`, `dtm_fecha_inicio`, `dtm_fecha_vigencia`, `ln_descripcion`)
                VALUES ('".$idConfiguracion."', '".$contribuyente."', '".$tagref."', '".$ln_ue."', '".$ind_activo."', '".$userid."', '2020-01-01 00:00:00', '".$enum_status."', '".$enum_periodo."', '".$nu_periodicidad."', '".$dtm_fecha_inicio."', '".$dtm_fecha_vigencia."', '".$ln_descripcion."')";
                $Result = DB_query($SQL, $db);
                $idContrato = DB_Last_Insert_ID($db, 'tb_contratos', 'id_contrato');

                
                $idObjeto = "";
                $SQL = "SELECT id_objetos FROM tb_cat_objetos_contrato WHERE id_stock = '$objetoParcial'";
                $TransResult = DB_query($SQL, $db);
                while ($myrow = DB_fetch_array($TransResult)) {
                    $idObjeto = $myrow ['id_objetos'];
                }

                $SQL = "INSERT INTO `tb_contratos_objetos_parciales` ( `id_contrato`, `id_stock`, `id_objetos`, `nu_cantidad`, `amt_total`, `ind_activo`)
                VALUES('".$idContrato."', '".$objetoParcial."', '".$idObjeto."', '".$cantidad."', '".$total."', '".$ind_activo."')";
                $Result = DB_query($SQL, $db);



                $SQL = "INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
                VALUES ('".$idContrato."', '".$idConfiguracion."', 23, '".$placa."', '1')";
                $Result = DB_query($SQL, $db);

                $SQL = "INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
                VALUES ('".$idContrato."', '".$idConfiguracion."', 24, '".$boleta."', '1')";
                $Result = DB_query($SQL, $db);
    
               
                // $SQL = "INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
                // VALUES ('".$idContrato."', '".$idConfiguracion."', 30, '".$infractor."', '1')";
                // $Result = DB_query($SQL, $db);

                // $SQL = "INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
                // VALUES ('".$idContrato."', '".$idConfiguracion."', 31, '".$observacion."', '1')";
                // $Result = DB_query($SQL, $db);


                $SQL = "INSERT INTO `tb_administracion_contratos` (`id_contrato`, `id_contribuyente`, `id_periodo`, `id_objeto_principal`, `id_objeto_parcial`, `nu_cantidad`, `mtn_importe`, `nu_descuento`, `mtn_total`, `dt_vencimineto`, `pase_cobro`, `folio_recibo`, `cajero`, `dt_fechadepago`, `estatus`)
                VALUES ('".$idContrato."', '".$contribuyente."', '".$idPerido."', '".$objetoPricipal."', '".$objetoParcial."', '".$cantidad."', '".$importe."', '0', '".$total."', '".$dtVencimiento."', '', '', '', '0000-00-00', '".$estatusAdeudo."')";
                $Result = DB_query($SQL, $db);

            }
        }
        
        else if(isset($_POST ['checkcontratos'])){

            $idContratoCont="";
            $agrup="";
            for ($i = 2; $i <= $numRows; $i++) { // $numRows
                $idAgrupacion = trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue());
                $idConfiguracion = trim($objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue());
                $contribuyente = trim($objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue());
                $tagref = trim($objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue());
                $ln_ue = trim($objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue());
                $ind_activo = trim($objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue());
                $userid = trim($objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue());
                $enum_status = trim($objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue());
                $enum_periodo = trim($objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue());
                $nu_periodicidad = trim($objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue());
                $dtm_fecha_inicio = trim($objPHPExcel->getActiveSheet()->getCell('K'.$i)->getCalculatedValue());
                $dtm_fecha_vigencia = trim($objPHPExcel->getActiveSheet()->getCell('L'.$i)->getCalculatedValue());
                $ln_descripcion = trim($objPHPExcel->getActiveSheet()->getCell('M'.$i)->getCalculatedValue());
                $idPerido = trim($objPHPExcel->getActiveSheet()->getCell('N'.$i)->getCalculatedValue());
                $objetoPricipal = trim($objPHPExcel->getActiveSheet()->getCell('O'.$i)->getCalculatedValue());
                $objetoParcial = trim($objPHPExcel->getActiveSheet()->getCell('P'.$i)->getCalculatedValue());
                $cantidad = trim($objPHPExcel->getActiveSheet()->getCell('Q'.$i)->getCalculatedValue());
                $importe = trim($objPHPExcel->getActiveSheet()->getCell('R'.$i)->getCalculatedValue());
                $total = trim($objPHPExcel->getActiveSheet()->getCell('S'.$i)->getCalculatedValue());
                $dtVencimiento = trim($objPHPExcel->getActiveSheet()->getCell('T'.$i)->getCalculatedValue());
                $dtPago = trim($objPHPExcel->getActiveSheet()->getCell('U'.$i)->getCalculatedValue());
                $estatusAdeudo = trim($objPHPExcel->getActiveSheet()->getCell('V'.$i)->getCalculatedValue());
                $panteon = trim($objPHPExcel->getActiveSheet()->getCell('W'.$i)->getCalculatedValue());
                $denominacion = trim($objPHPExcel->getActiveSheet()->getCell('X'.$i)->getCalculatedValue());
                $fosa = trim($objPHPExcel->getActiveSheet()->getCell('Y'.$i)->getCalculatedValue());
                $fila = trim($objPHPExcel->getActiveSheet()->getCell('Z'.$i)->getCalculatedValue());
                $tramo = trim($objPHPExcel->getActiveSheet()->getCell('AA'.$i)->getCalculatedValue());
                $lote = trim($objPHPExcel->getActiveSheet()->getCell('AB'.$i)->getCalculatedValue());
                $libro = trim($objPHPExcel->getActiveSheet()->getCell('AC'.$i)->getCalculatedValue());
                $seccion = trim($objPHPExcel->getActiveSheet()->getCell('AD'.$i)->getCalculatedValue());
                $fosalibro = trim($objPHPExcel->getActiveSheet()->getCell('AE'.$i)->getCalculatedValue());
                $comentarios = trim($objPHPExcel->getActiveSheet()->getCell('AF'.$i)->getCalculatedValue());
                
                
                
              
                
                
                if ($idAgrupacion != $agrup ){
                    $SQL = "INSERT INTO `tb_contratos` ( `id_confcontratos`, `id_debtorno`, `tagref`, `ln_ue`, `ind_activo`, `userid`, `dtm_fecha_efectiva`,  `enum_status`, `enum_periodo`, `nu_periodicidad`, `dtm_fecha_inicio`, `dtm_fecha_vigencia`, `ln_descripcion`)
                    VALUES ('".$idConfiguracion."', '".$contribuyente."', '".$tagref."', '".$ln_ue."', '".$ind_activo."', '".$userid."', '2020-01-01 00:00:00', '".$enum_status."', '".$enum_periodo."', '".$nu_periodicidad."', '".$dtm_fecha_inicio."', '".$dtm_fecha_vigencia."', '".$ln_descripcion."')";
                    $Result = DB_query($SQL, $db);
                    $idContrato = DB_Last_Insert_ID($db, 'tb_contratos', 'id_contrato');
                }else{
                    $idContrato = $idContratoCont;
                }
              


               

                
                $idObjeto = "";
                $SQL = "SELECT id_objetos FROM tb_cat_objetos_contrato WHERE id_stock = '$objetoParcial'";
                $TransResult = DB_query($SQL, $db);
                while ($myrow = DB_fetch_array($TransResult)) {
                    $idObjeto = $myrow ['id_objetos'];
                }

                $SQL = "INSERT INTO `tb_contratos_objetos_parciales` ( `id_contrato`, `id_stock`, `id_objetos`, `nu_cantidad`, `amt_total`, `ind_activo`)
                VALUES('".$idContrato."', '".$objetoParcial."', '".$idObjeto."', '".$cantidad."', '".$total."', '".$ind_activo."')";
                $Result = DB_query($SQL, $db);

                // $idEtiqueta = "";
                // $SQL = "SELECT id_atributos FROM tb_cat_atributos_contrato WHERE id_contratos = '$idConfiguracion'";
                // $TransResult = DB_query($SQL, $db, $ErrMsg);
                // while ($myrow = DB_fetch_array($TransResult)) {
                //     $idEtiqueta = $myrow ['id_atributos'];
                // }
                if ($idAgrupacion != $agrup ){
                    


                    $SQL = "INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
                    VALUES ('".$idContrato."', '".$idConfiguracion."', 11, '".$panteon."', '1')";
                    $Result = DB_query($SQL, $db);
    
                    $SQL = "INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
                    VALUES ('".$idContrato."', '".$idConfiguracion."', 12, '".$denominacion."', '1')";
                    $Result = DB_query($SQL, $db);
    
    
                    $SQL = "INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
                    VALUES ('".$idContrato."', '".$idConfiguracion."', 13, '".$fosa."', '1')";
                    $Result = DB_query($SQL, $db);
    
    
                    $SQL = "INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
                    VALUES ('".$idContrato."', '".$idConfiguracion."', 14, '".$fila."', '1')";
                    $Result = DB_query($SQL, $db);

                    $SQL = "INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
                    VALUES ('".$idContrato."', '".$idConfiguracion."', 15, '".$tramo."', '1')";
                    $Result = DB_query($SQL, $db);
    
                    $SQL = "INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
                    VALUES ('".$idContrato."', '".$idConfiguracion."', 37, '".$lote."', '1')";
                    $Result = DB_query($SQL, $db);

                    $SQL = "INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
                    VALUES ('".$idContrato."', '".$idConfiguracion."', 38, '".$libro."', '1')";
                    $Result = DB_query($SQL, $db);

                    $SQL = "INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
                    VALUES ('".$idContrato."', '".$idConfiguracion."', 39, '".$seccion."', '1')";
                    $Result = DB_query($SQL, $db);

                    $SQL = "INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
                    VALUES ('".$idContrato."', '".$idConfiguracion."', 40, '".$fosalibro."', '1')";
                    $Result = DB_query($SQL, $db);

                    $SQL = "INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
                    VALUES ('".$idContrato."', '".$idConfiguracion."', 41, '".$comentarios."', '1')";
                    $Result = DB_query($SQL, $db);
    

               }
               $agrup = $idAgrupacion;
               $idContratoCont = $idContrato;

               
                // $SQL = "INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
                // VALUES ('".$idContrato."', '".$idConfiguracion."', 30, '".$infractor."', '1')";
                // $Result = DB_query($SQL, $db);

                // $SQL = "INSERT INTO `tb_propiedades_atributos` (`id_folio_contrato`, `id_folio_configuracion`, `id_etiqueta_atributo`, `ln_valor`, `nu_activo`)
                // VALUES ('".$idContrato."', '".$idConfiguracion."', 31, '".$observacion."', '1')";
                // $Result = DB_query($SQL, $db);


                $SQL = "INSERT INTO `tb_administracion_contratos` (`id_contrato`, `id_contribuyente`, `id_periodo`, `id_objeto_principal`, `id_objeto_parcial`, `nu_cantidad`, `mtn_importe`, `nu_descuento`, `mtn_total`, `dt_vencimineto`, `pase_cobro`, `folio_recibo`, `cajero`, `dt_fechadepago`, `estatus`)
                VALUES ('".$idContrato."', '".$contribuyente."', '".$idPerido."', '".$objetoPricipal."', '".$objetoParcial."', '".$cantidad."', '".$importe."', '0', '".$total."', '".$dtVencimiento."', '', '', '', '0000-00-00', '".$estatusAdeudo."')";
                $Result = DB_query($SQL, $db);

            }
        }
            // echo "<pre>".$SQL3."</pre>";
            // echo "<pre>".$SQL2."</pre>";
         else if (isset($_POST['checkPreciosVal'])) {
            // CREATE TABLE `prices_validacion` (
            // `stockid` varchar(255) NOT NULL,
            // `price` decimal(20,4) NOT NULL DEFAULT '0.0000',
            // `tipo` varchar(50) DEFAULT NULL,
            // `vsu` varchar(50) DEFAULT NULL
            // ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
            $numRegistros = 0;
            for ($i = 1; $i <= $numRows; $i++) { // $numRows
                $stockid = trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue());
                // $dato2 = trim($objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue());
                $tipo = 1;//trim($objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue());
                $vsu = "";//trim($objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue());
                $price = trim($objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue());

                $price = str_replace(',', '', $price);

                // $stockid = $dato1."_".$dato2;

                $SQL = "INSERT INTO `prices_validacion` (`stockid`,`price`,`tipo`,`vsu`) 
                VALUES ('".$stockid."', '".$price."', '".$tipo."', '".$vsu."')";
                $Result = DB_query($SQL, $db);

                $numRegistros ++;
            }
        } else if (isset($_POST['checkCancelContratos'])) {
            // CREATE TABLE `tb_contratos_excel_cancelar` (
            // `id` int(11) NOT NULL AUTO_INCREMENT,
            // `fecha` text default null,
            // `folio` text default null,
            // `contrato` text default null,
            // `placa` text default null,
            // `motivo` text default null,
            // `reviso` text default null,
            // `autorizo` text default null,
            // `archivo` text default null,
            // PRIMARY KEY (`id`)
            // ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            $numRegistros = 0;
            for ($i = 1; $i <= $numRows; $i++) { // $numRows
                $fecha = trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue());
                $folio = trim($objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue());
                $contrato = trim($objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue());
                $placa = trim($objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue());
                $motivo = trim($objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue());
                $reviso = trim($objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue());
                $autorizo = trim($objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue());

                $SQL = "INSERT INTO tb_contratos_excel_cancelar (fecha, folio, contrato, placa, motivo, reviso, autorizo, archivo) 
                VALUES ('".$fecha."', '".$folio."', '".$contrato."', '".$placa."', '".$motivo."', '".$reviso."', '".$autorizo."', 'Archivo 2020')";
                $Result = DB_query($SQL, $db);

                $numRegistros ++;
            }

            echo "<br>numRegistros: ".$numRegistros;
        } else if (isset($_POST['checkInfoPredial'])) {
            // CREATE TABLE `tb_predial_montos` (
            // `id` int(11) NOT NULL AUTO_INCREMENT,
            // `stockid` varchar(255) DEFAULT NULL COMMENT 'Objeto Parcial',
            // `amt_monto` double NOT NULL DEFAULT 0 COMMENT 'Monto',
            // `dtm_fecha` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            // PRIMARY KEY (`id`)
            // ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
            $numRegistros = 0;
            for ($i = 3; $i <= $numRows; $i++) { // $numRows
                $fecha = trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue());

                if (!empty($fecha)) {
                    $datosFecha = explode('.', $fecha);
                    $fechaRegistro = $datosFecha[2]."-".$datosFecha[1]."-".$datosFecha[0];

                    $PRE_0004 = "PRE_0004";
                    $PRE_0004_amt = trim($objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue());
                    $SQL = "INSERT INTO tb_predial_montos (stockid, amt_monto, dtm_fecha) 
                    VALUES ('".$PRE_0004."', '".$PRE_0004_amt."', '".$fechaRegistro."')";
                    $Result = DB_query($SQL, $db);

                    $PRE_0013 = "PRE_0013";
                    $PRE_0013_amt = trim($objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue());
                    $SQL = "INSERT INTO tb_predial_montos (stockid, amt_monto, dtm_fecha) 
                    VALUES ('".$PRE_0013."', '".$PRE_0013_amt."', '".$fechaRegistro."')";
                    $Result = DB_query($SQL, $db);

                    $PRE_0009 = "PRE_0009";
                    $PRE_0009_amt = trim($objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue());
                    $SQL = "INSERT INTO tb_predial_montos (stockid, amt_monto, dtm_fecha) 
                    VALUES ('".$PRE_0009."', '".$PRE_0009_amt."', '".$fechaRegistro."')";
                    $Result = DB_query($SQL, $db);

                    $PRE_0012 = "PRE_0012";
                    $PRE_0012_amt = trim($objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue());
                    $SQL = "INSERT INTO tb_predial_montos (stockid, amt_monto, dtm_fecha) 
                    VALUES ('".$PRE_0012."', '".$PRE_0012_amt."', '".$fechaRegistro."')";
                    $Result = DB_query($SQL, $db);

                    $PRE_0014 = "PRE_0014";
                    $PRE_0014_amt = trim($objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue());
                    $SQL = "INSERT INTO tb_predial_montos (stockid, amt_monto, dtm_fecha) 
                    VALUES ('".$PRE_0014."', '".$PRE_0014_amt."', '".$fechaRegistro."')";
                    $Result = DB_query($SQL, $db);

                    $PRE_0015 = "PRE_0015";
                    $PRE_0015_amt = trim($objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue());
                    $SQL = "INSERT INTO tb_predial_montos (stockid, amt_monto, dtm_fecha) 
                    VALUES ('".$PRE_0015."', '".$PRE_0015_amt."', '".$fechaRegistro."')";
                    $Result = DB_query($SQL, $db);

                    $PRE_0016 = "PRE_0016";
                    $PRE_0016_amt = trim($objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue());
                    $SQL = "INSERT INTO tb_predial_montos (stockid, amt_monto, dtm_fecha) 
                    VALUES ('".$PRE_0016."', '".$PRE_0016_amt."', '".$fechaRegistro."')";
                    $Result = DB_query($SQL, $db);

                    $PRE_0011 = "PRE_0011";
                    $PRE_0011_amt = trim($objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue());
                    $SQL = "INSERT INTO tb_predial_montos (stockid, amt_monto, dtm_fecha) 
                    VALUES ('".$PRE_0011."', '".$PRE_0011_amt."', '".$fechaRegistro."')";
                    $Result = DB_query($SQL, $db);

                    $PRE_0002 = "PRE_0002";
                    $PRE_0002_amt = trim($objPHPExcel->getActiveSheet()->getCell('L'.$i)->getCalculatedValue());
                    $SQL = "INSERT INTO tb_predial_montos (stockid, amt_monto, dtm_fecha) 
                    VALUES ('".$PRE_0002."', '".$PRE_0002_amt."', '".$fechaRegistro."')";
                    $Result = DB_query($SQL, $db);

                    $PRE_0001 = "PRE_0001";
                    $PRE_0001_amt = trim($objPHPExcel->getActiveSheet()->getCell('M'.$i)->getCalculatedValue());
                    $SQL = "INSERT INTO tb_predial_montos (stockid, amt_monto, dtm_fecha) 
                    VALUES ('".$PRE_0001."', '".$PRE_0001_amt."', '".$fechaRegistro."')";
                    $Result = DB_query($SQL, $db);

                    $PRE_0003 = "PRE_0003";
                    $PRE_0003_amt = trim($objPHPExcel->getActiveSheet()->getCell('R'.$i)->getCalculatedValue());
                    $SQL = "INSERT INTO tb_predial_montos (stockid, amt_monto, dtm_fecha) 
                    VALUES ('".$PRE_0003."', '".$PRE_0003_amt."', '".$fechaRegistro."')";
                    $Result = DB_query($SQL, $db);

                    $PRE_0006 = "PRE_0006";
                    $PRE_0006_amt = trim($objPHPExcel->getActiveSheet()->getCell('S'.$i)->getCalculatedValue());
                    $SQL = "INSERT INTO tb_predial_montos (stockid, amt_monto, dtm_fecha) 
                    VALUES ('".$PRE_0006."', '".$PRE_0006_amt."', '".$fechaRegistro."')";
                    $Result = DB_query($SQL, $db);

                    $PRE_0010 = "PRE_0010";
                    $PRE_0010_amt = trim($objPHPExcel->getActiveSheet()->getCell('T'.$i)->getCalculatedValue());
                    $SQL = "INSERT INTO tb_predial_montos (stockid, amt_monto, dtm_fecha) 
                    VALUES ('".$PRE_0010."', '".$PRE_0010_amt."', '".$fechaRegistro."')";
                    $Result = DB_query($SQL, $db);

                    $PRE_0005 = "PRE_0005";
                    $PRE_0005_amt = trim($objPHPExcel->getActiveSheet()->getCell('U'.$i)->getCalculatedValue());
                    $SQL = "INSERT INTO tb_predial_montos (stockid, amt_monto, dtm_fecha) 
                    VALUES ('".$PRE_0005."', '".$PRE_0005_amt."', '".$fechaRegistro."')";
                    $Result = DB_query($SQL, $db);
                    
                    // echo "<br>fecha: ".$fechaRegistro." - ".$PRE_0004." - ".$PRE_0004_amt;
                    
                    $numRegistros ++;
                }
            }

            echo "<br>numRegistros: ".$numRegistros;
        } else if (isset($_POST['checkInfoGetIcContri'])) {
            echo "<table border='1'>";

            echo "<tr>";
            echo "<td>#</td>";
            echo "<td>Num</td>";
            echo "<td>Ic</td>";
            echo "<td>Nombre</td>";
            echo "<td>Giro</td>";
            echo "<td>Pab</td>";
            echo "<td>Tramos</td>";
            echo "<td>Nombre Negocio</td>";
            echo "<td>Concesion</td>";
            echo "<td>Nombre Alta</td>";
            echo "<td>No Registros Existentes</td>";
            echo "</tr>";

            $numRegistros = 1;
            $numRegistrosMasInfo = 0;
            for ($i = 2; $i <= $numRows; $i++) { // $numRows
                $numero = trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue());
                $nombre = trim($objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue());
                $giro = trim($objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue());
                $pab = trim($objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue());
                $tramos = trim($objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue());
                $nombreNegocio = trim($objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue());
                $concesion = trim($objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue());

                $ic = "";
                $icNew = "";

                $numeroExisten = 0;

                if (!empty($nombre)) {
                    $datos = explode(' ', $nombre);
                    $nombreBusqueda = "";

                    $apellidoPaterno = "";
                    $apellidoMaterno = "";
                    $nombreGuardar = "";

                    if (count($datos) == 3) {
                        $nombreBusqueda = $datos[1]." ".$datos[2]." ".$datos[0];

                        $apellidoPaterno = $datos[1];
                        $apellidoMaterno = $datos[2];
                        $nombreGuardar = $datos[0];
                    } else if (count($datos) == 4) {
                        $nombreBusqueda = $datos[2]." ".$datos[3]." ".$datos[0]." ".$datos[1];

                        $apellidoPaterno = $datos[2];
                        $apellidoMaterno = $datos[3];
                        $nombreGuardar = $datos[0]." ".$datos[1];
                    } else {
                        $nombreBusqueda = $nombre;
                    }

                    $SQL = "SELECT debtorno FROM debtorsmaster WHERE name LIKE '%".$nombreBusqueda."%'";
                    $Result = DB_query($SQL, $db);

                    $numeroExisten = DB_num_rows($Result);

                    if ($numeroExisten == 1) {
                        $myrow = DB_fetch_array($Result);
                        $ic = $myrow['debtorno'];
                    }

                    // obtener ic
                    $SQL = "SELECT MAX(debtorno) + 1 as lastID FROM debtorsmaster WHERE debtorno";
                    $Result = DB_query($SQL, $db);
                    $myrow = DB_fetch_array($Result);
                    $icNew = $myrow['lastID'];

                    // agregar contribuyente
                    $SQL1 = "INSERT INTO `debtorsmaster` (`debtorno`, `name`, `name1`, `name2`, `name3`, `curp`, `address1`, `address2`, `address3`, `address4`, `address5`, `address6`, `currcode`, `salestype`, `clientsince`, `holdreason`, `paymentterms`, `discount`, `pymtdiscount`, `lastpaid`, `lastpaiddate`, `creditlimit`, `invaddrbranch`, `discountcode`, `ediinvoices`, `ediorders`, `edireference`, `editransport`, `ediaddress`, `ediserveruser`, `ediserverpwd`, `taxref`, `customerpoline`, `typeid`, `daygrace`, `coments`, `blacklist`, `ruta`, `nameextra`, `fechanacimiento`, `lugarnacimiento`, `telefonocelular`, `ingresosmensuales`, `estadocivil`, `mediocontacto`, `NacionalidadId`, `CapacidadCompraId`, `razoncompra`, `pagpersonal`, `idCapComIngresos`, `companyprospect`, `prospectsince`, `userprospect`, `usoCFDI`, `NumRegIdTrib`, `RIF`, `package_id`, `app`, `tipo`, `regimenFiscal_id`, `numRegistro`, `reqComprobante`, `tipoDir`, `numExt`, `numInt`, `ext`, `direccion`, `distrito`, `activo`)
                    VALUES
                    ('".$icNew."', '".$nombreBusqueda."', '".$apellidoPaterno."', '".$apellidoMaterno."', '".$nombreGuardar."', '', 'COLON', 'ZONA CENTRO', 'TAMPICO', 'TAMAULIPAS', '89000', '', 'MXN', 'L1', '0000-00-00 00:00:00', 0, '01', 0, 0, 0, NULL, 1000, 0, '', 0, 0, '', '0', '', '', '', '', 0, 1, '', NULL, 0, 0, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'P01', NULL, NULL, NULL, 0, 'Moral', '', '', '1', 'Local', '102', '', '', 'COLON ZONA CENTRO TAMPICO TAMAULIPAS 89000', 'ZONA CENTRO', '1');
                    ";
                    $Result1 = DB_query($SQL1, $db);

                    $SQL2 = "INSERT INTO `custbranch` (`branchcode`, `debtorno`, `brname`, `taxid`, `braddress1`, `braddress2`, `braddress3`, `braddress4`, `braddress5`, `braddress6`, `lat`, `lng`, `estdeliverydays`, `area`, `salesman`, `fwddate`, `phoneno`, `faxno`, `contactname`, `email`, `lineofbusiness`, `flagworkshop`, `defaultlocation`, `taxgroupid`, `defaultshipvia`, `deliverblind`, `disabletrans`, `brpostaddr1`, `brpostaddr2`, `brpostaddr3`, `brpostaddr4`, `brpostaddr5`, `brpostaddr6`, `specialinstructions`, `custbranchcode`, `creditlimit`, `custdata1`, `custdata2`, `custdata3`, `custdata4`, `custdata5`, `custdata6`, `ruta`, `paymentname`, `nocuenta`, `fecha_modificacion`, `namebank`, `brnumint`, `brnumext`, `movilno`, `nextelno`, `logocliente`, `descclientecomercial`, `descclientepropago`, `welcomemail`, `custpais`, `SectComClId`, `NumeAsigCliente`, `descclienteop`, `typeaddenda`, `idprospecmedcontacto`, `idproyecto`, `braddress7`, `DiasRevicion`, `DiasPago`, `prefer`, `discountcard`, `typecomplement`)
                    VALUES
                    ('".$icNew."', '".$icNew."', '".$nombreBusqueda."', 'XXXX010101XXX', 'COLON', 'TAMPICO', 'TAMAULIPAS', '89000', '', 'ZONA CENTRO', 0.000000, 0.000000, 1, '', '', 0, '0', '', '', '0', '', 0, '', 1, 1, 1, 0, 'COLON', 'ZONA CENTRO', 'TAMPICO', 'TAMAULIPAS', '', '', '".$nombreBusqueda."', '', 1000, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, '1900-01-01 00:00:00', NULL, '', '102', '0', NULL, NULL, 0.00, 0.00, '0', 'México', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0);
                    ";
                    $Result2 = DB_query($SQL2, $db);
                }

                echo "<tr>";
                echo "<td>".$i."</td>";
                echo "<td>".$numero."</td>";
                echo "<td>".$icNew."</td>";
                echo "<td>".$nombre."</td>";
                echo "<td>".$giro."</td>";
                echo "<td>".$pab."</td>";
                echo "<td>".$tramos."</td>";
                echo "<td>".$nombreNegocio."</td>";
                echo "<td>".$concesion."</td>";
                echo "<td>".$nombreBusqueda."</td>";
                echo "<td>".$numeroExisten."</td>";
                echo "</tr>";

                $numRegistros ++;
            }

            echo "</table>";

            echo "<br>numRegistros: ".$numRegistros;
            echo "<br>numRegistrosMasInfo: ".$numRegistrosMasInfo;
            exit();
        }
    } else {
        echo "<br>no trae";
    }

    if (isset($_POST['checkNivelCuentas'])) {
        $SQL = "SELECT accountcode FROM chartmaster";
        $Result=  DB_query($SQL, $db);
        while ($myrow = DB_fetch_array($Result)) {
            $SQL = "UPDATE chartmaster SET nu_nivel = '".fnNivelesCuentaContableGeneral($db, $myrow['accountcode'])."' 
            WHERE accountcode = '".$myrow['accountcode']."'";
            $ResultUpdate=  DB_query($SQL, $db);
        }
    }

    if (isset($_POST['checkProcesoMovimientos_ant'])) {
        $SQL = "
        SELECT
        DISTINCT
        audittrail.*
        FROM audittrail
        WHERE 
        transactiondate like '2020-02-10%'
        AND transactiondate between '2020-02-10 06:00:00' and '2020-02-10 14:25:08'
        AND querystring not LIKE '%select%'
        AND querystring not LIKE '%tb_debtortrans_forma_pago%'
        AND (querystring LIKE '%insert%')
        AND 
        (
        querystring LIKE '%salesorders%'
        OR querystring LIKE '%debtortrans%'
        )
        ";
        $Result = DB_query($SQL, $db);
        $numRegistros = 0;
        while ($myrow = DB_fetch_array($Result)) {
            $porciones = explode(" */ ", $myrow['querystring']);
            // echo "<br>porciones: ".count($porciones);
            // echo "<br><pre>".$porciones[1]."</pre>";
            
            $ResultUpdate=  DB_query($porciones[1], $db);
            $numRegistros ++;
        }
        echo "<br>numRegistros: ".DB_num_rows($Result);
    }

    if (isset($_POST['checkProcesoMovimientos'])) {
        $SQL = "
        SELECT
        audittrail.*
        FROM audittrail
        WHERE transactiondate like '2020-02-27%'
        AND transactiondate <= '2020-02-27 10:34:34'
        AND querystring like '%insert%'
        AND querystring like '%salesorderdetails%'
        ";
        $Result = DB_query($SQL, $db);
        $numRegistros = 0;
        while ($myrow = DB_fetch_array($Result)) {
            $porciones = explode(" */ ", $myrow['querystring']);
            // echo "<br>porciones: ".count($porciones);
            $porciones[1] = str_replace('<br>', '', $porciones[1]);
            $porciones[1] = str_replace('&lt;br&gt;', '', $porciones[1]);
            // echo "<br><pre>".$porciones[1]."</pre>";
            
            $ResultUpdate=  DB_query($porciones[1], $db);
            $numRegistros ++;
        }
        echo "<br>numRegistros: ".DB_num_rows($Result);
    }

    if (isset($_POST['checkProcesoMovimientos_ant2'])) {
        $SQL = "
        SELECT salesorderdetails.stkcode, 
        salesorderdetails.unitprice,
        salesorderdetails.quantity,
        (salesorderdetails.unitprice * salesorderdetails.quantity) * salesorderdetails.discountpercent as totalDescuento,
        tb_cat_objeto_detalle.clave_presupuestal AS clavePresupuestal,
        tb_cat_objeto_detalle.cuenta_abono AS cuentaAbono,
        tb_cat_objeto_detalle.cuenta_cargo AS cuentaCargo,
        salesorderdetails.id_administracion_contratos,
        chartdetailsbudgetbytag.tagref as tagrefClave,
        tb_cat_unidades_ejecutoras.ue as ueClave,
        tb_administracion_contratos.id_contrato,
        debtortransRecibo.type,
        debtortransRecibo.transno,
        65 as PeriodNo,
        debtortransRecibo.invtext as referencia
        FROM salesorders
        JOIN salesorderdetails ON salesorderdetails.orderno = salesorders.orderno
        JOIN tb_cat_objeto_detalle ON tb_cat_objeto_detalle.stockid = salesorderdetails.stkcode
        JOIN chartdetailsbudgetbytag ON chartdetailsbudgetbytag.accountcode = tb_cat_objeto_detalle.clave_presupuestal
        JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ln_aux1 = chartdetailsbudgetbytag.ln_aux1
        LEFT JOIN tb_administracion_contratos ON tb_administracion_contratos.id_administracion_contratos = salesorderdetails.id_administracion_contratos
        JOIN debtortrans ON debtortrans.order_ = salesorderdetails.orderno
        JOIN custallocns ON custallocns.transid_allocto = debtortrans.id
        JOIN debtortrans debtortransRecibo ON debtortransRecibo.id = custallocns.transid_allocfrom
        WHERE salesorders.orderno IN ()
        ";
        // 12116, 12117, 12119, 12120, 12122, 12118, 12123, 12124, 12121, 12127, 12126, 12125, 12128, 12132, 12133, 12134, 12130, 12135, 12137, 12138, 12131, 12139, 12129, 12143, 12142, 12146, 12144, 12141, 12147, 12150, 12151, 12153, 12152, 12136, 12140, 12145, 12158, 12155, 12148, 12159, 12160, 12163, 12164, 12165, 12162, 12166, 12149, 12169, 12168, 12170, 12171, 12167, 12173, 12174, 12175, 12179, 12180, 12182, 12181, 12185, 12184, 12186, 12189, 12196, 9146, 9144, 12201, 12226, 12228, 12230, 12214, 12233, 8900, 12161, 8898, 12236, 12237, 12235, 12238, 12239, 12260, 12261, 12243, 12245, 12268, 12265, 12269, 12272, 12271, 12275, 12283, 12284, 12276, 12287, 12288, 12289, 12282, 12303, 11000, 12301, 11004, 11008, 12314, 12316, 11011, 12279, 11018, 12319, 11697, 10059, 12172, 12322, 12323, 12324, 12325, 12327, 12328, 12329, 12176, 11020, 12331, 11025, 12315, 12177, 12332, 12334, 12178, 11029, 12337, 12318, 12338, 11034, 12335, 12187, 12190, 9396, 12188, 9397, 12192, 12193, 10650, 12194, 12344, 10652, 12195, 12346, 12197, 12198, 12347, 12348, 12321, 12350, 12199, 12352, 12200, 12326, 12202, 12354, 12356, 12204, 12361, 12360, 12205, 12359, 12206, 12364, 12336, 12207, 12366, 12208, 12368, 12209, 12210, 12372, 12211, 7294, 12373, 12213, 12375, 12215, 12216, 12376, 12379, 12218, 12380, 12219, 10576, 12330, 12220, 12221, 12333, 12392, 12377, 12397, 12342, 12402, 12351, 12395, 12409, 12410, 3053, 12411, 12223, 12414, 12224, 12418, 12225, 12227, 12420, 12400, 12422, 12229, 12424, 12403, 12405, 12231, 12421, 12246, 12429, 12430, 12434, 12436, 12247, 12248, 12431, 12435, 12438, 12249, 12441, 12250, 12440, 12252, 10049, 12437, 12254, 12425, 12255, 12412, 12426, 12450, 12453, 12455, 12458, 12257, 12443, 12464, 12465, 12259, 12466, 12427, 12417, 12474, 12485, 12459, 12477, 12461, 12479, 12389, 12491, 12495, 12381, 12497, 12498, 12382, 12433, 12501, 12385, 12503, 12387, 12504, 12404, 12506, 12406, 12507, 12508, 12510, 12446, 12513, 12447, 12514, 12515, 12516, 12473, 12462, 12460, 12454, 12449, 12470, 12520, 12522, 12475, 12484, 10749, 12525, 12487, 12527, 12488, 12529, 12530, 12500, 12528, 12531, 12511, 12532, 12505, 11782, 12114, 12535, 12111, 8812, 10192, 10190, 10194, 12543, 12545, 12548, 12544, 12553, 12552, 12560, 12555, 6502, 6499, 12517, 12567, 12570, 12519, 12550, 12575, 12579, 12581, 12557, 12576, 12587, 12589, 12526, 12599, 12598, 12566, 12582, 12604, 12608, 11327, 10370, 12585, 12613, 11210, 12594, 11851, 12600, 12592, 12564, 12621, 12622, 12595, 12624, 12625, 11763, 12626, 12627, 12628, 12629, 12616, 12630, 7928, 12634, 12633, 12620, 12635, 12637, 12611, 12632, 12638, 12639, 12605, 12642, 12643, 12647, 12645, 12648, 12641, 12644, 12650, 12652, 12654, 12623, 12655, 12619, 10282, 12657, 12656, 12658, 12663, 12665, 12667, 12668, 10160, 12669, 12670, 12671, 12673, 11838, 12661, 12660, 12662
        $Result = DB_query($SQL, $db);
        $numRegistros = 0;
        while ($rs = DB_fetch_array($Result)) {
            $totalAmount = ($rs['unitprice'] * $rs['quantity']) - ($rs['totalDescuento']);
            $totalAmount = number_format($totalAmount, 2, '.', '');

            // Log presupuestal, del devengado
            // $agregoLog = fnInsertPresupuestoLog($db, 12, $rs['transno'], $rs['tagrefClave'], $rs['clavePresupuestal'], $rs['PeriodNo'], abs($totalAmount) * -1, 310, "", $rs['referencia'], 1, '', 0, $rs['ueClave']); // Negativo
            // $agregoLog = fnInsertPresupuestoLog($db, 12, $rs['transno'], $rs['tagrefClave'], $rs['clavePresupuestal'], $rs['PeriodNo'], abs($totalAmount), 310, "", $rs['referencia'], 1, '', 0, $rs['ueClave']); // Positivo
            // Log presupuestal, del recaudado
            // $agregoLog = fnInsertPresupuestoLog($db, 12, $rs['transno'], $rs['tagrefClave'], $rs['clavePresupuestal'], $rs['PeriodNo'], abs($totalAmount) * -1, 311, "", $rs['referencia'], 1, '', 0, $rs['ueClave']); // Negativo
        }
        echo "<br>numRegistros: ".DB_num_rows($Result);
    }
}

?>
<form id="form_input" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    <div align="center">
        <h4>Proceso Movimientos</h4>
        <input type='checkbox' name='checkProcesoMovimientos' id='checkProcesoMovimientos' value='1' />
        <br><br>
        <h2>Cagar Catalogos en EXCEL</h2>
        <h4>Catalogo Unidades Responsables</h4>
        <input type='checkbox' name='checkUR' id='checkUR' value='1' />
        <br><br>
        <h4>Catalogo AUX1</h4>
        <input type='checkbox' name='checkUE' id='checkUE' value='1' />
        <br><br>
        <h4>Catalogo Ramo</h4>
        <input type='checkbox' name='checkRamo' id='checkRamo' value='1' />
        <br><br>
        <h4>Catalogo URG</h4>
        <input type='checkbox' name='checkURG' id='checkURG' value='1' />
        <br><br>
        <h4>Presupuesto</h4>
        <input type='checkbox' name='checkPresupuesto' id='checkPresupuesto' value='1' />
        <?php
        echo "<select name='cmbTipoMov' id='cmbTipoMov' class='cmbTipoMov'>";
        $SQL = "SELECT typeid, typename FROM systypescat WHERE nu_estado_presupuesto = 1 ORDER BY typeid";
        $ErrMsg      = _('No transactions were returned by the SQL because');
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            if ($myrow['typeid'] == $tipopoliza) {
                echo "<option selected value='" . $myrow['typeid'] . "'>" . $myrow['typeid'] . ' - ' . $myrow['typename'] . "</option>";
            } else {
                echo "<option value='" . $myrow['typeid'] . "'>" . $myrow['typeid'] . ' - ' . $myrow['typename'] . "</option>";
            }
        }
        echo "</select>";
        ?>
        <br><br>
        <h4>Catalogo JUSR</h4>
        <input type='checkbox' name='checkJusR' id='checkJusR' value='1' />
        <br><br>
        <h4>Catalogo CONC R23</h4>
        <input type='checkbox' name='checkConcR23' id='checkConcR23' value='1' />
        <br><br>
        <h4>Catalogo CABM</h4>
        <input type='checkbox' name='checkCabm' id='checkCabm' value='1' />
        <br><br>
        <h4>Agregar Nivel Cuentas Contables</h4>
        <input type='checkbox' name='checkNivelCuentas' id='checkNivelCuentas' value='1' />
        <br><br>
        <h4>Crear insert Plan de Cuentas</h4>
        <input type='checkbox' name='checkInsertCuentas' id='checkInsertCuentas' value='1' />
        <br><br>
        <h4>Crear insert Devengado</h4>
        <input type='checkbox' name='checkInsertDevengado' id='checkInsertDevengado' value='1' />
        <br><br>
        <h4>Crear insert Contribuyente</h4>
        <input type='checkbox' name='checkInsertContribuyente' id='checkInsertContribuyente' value='1' />
        <br><br>
        <h4>Cargar Contribuyente Tabla temporal</h4>
        <input type='checkbox' name='checkContribuyenteTemporal' id='checkContribuyenteTemporal' value='1' />
        <br><br>
        <h4>objetos contrato cementerios</h4>
        <input type='checkbox' name='checkobjconceme' id='checkobjconceme' value='1' />
        <br><br>
        <h4>Contratos</h4>
        <input type='checkbox' name='checkcontratos' id='checkcontratos' value='1' />
        <br><br>
        <h4>Contratos Directos Uno a Uno</h4>
        <input type='checkbox' name='checkContratosDirectos' id='checkContratosDirectos' value='1' />
        <br><br>
        <h4>Totales Ingresos</h4>
        <input type='checkbox' name='checktotalesingresos' id='checktotalesingresos' value='1' />
        <br><br> 
        <h4>Total de Ingresos por Dia</h4>
        <input type='checkbox' name='checktotalesingresosxdia' id='checktotalesingresosxdia' value='1' />
        <br><br>
        <h4>Porcentajes Meta</h4>
        <input type='checkbox' name='checkporcentaje' id='checkporcentaje' value='1' />
        <br><br>
        <h4>Precios Val</h4>
        <input type='checkbox' name='checkPreciosVal' id='checkPreciosVal' value='1' />
        <br><br>
        <h4>Archivo Contratos Cancelacion</h4>
        <input type='checkbox' name='checkCancelContratos' id='checkCancelContratos' value='1' />
        <br><br>
        <h4>Información Predial Enero y Febrero</h4>
        <input type='checkbox' name='checkInfoPredial' id='checkInfoPredial' value='1' />
        <br><br>
        <h4>Validar ic contribuyente</h4>
        <input type='checkbox' name='checkInfoGetIcContri' id='checkInfoGetIcContri' value='1' />
        <br><br>
        <label>Cargar Archivo: </label>
        <input type="file" id="txtCatalogo" name="txtCatalogo" title="Seleccionar Catalogo" />
        <br><br>
        <button type="submit" name="btnCargar">Cargar</button>
    </div>
</form>

<?php
include 'includes/footer_Index.inc';
