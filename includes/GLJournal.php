<?php
/**
 * Captura de Póliza Manual
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 06/11/2017
 * Fecha Modificación: 06/11/2017
 * Captura de Póliza Manual
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

include 'includes/DefineJournalClass.php';

$PageSecurity = 10;
include 'includes/session.inc';
$funcion = 371;
//alfredo include "includes/SecurityUrl.php";
$title = traeNombreFuncion($funcion, $db);
include 'includes/header.inc';
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
include "includes/SecurityUrl.php";
//Librerias GRID
include('javascripts/libreriasGrid.inc');

$ErrMsg = _('');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

// Validar Identicador
$validarIdentificador = 1;

if (isset($_POST['txtFechaPoliza'])) {
    $Fecha = $_POST['txtFechaPoliza'];
} else {
    $Fecha = date('d-m-Y');
}

if (isset($_POST['PeriodNo'])) {
    $PeriodNo = $_POST['PeriodNo'];
} else {
    $PeriodNo = "";
}

if (isset($_GET['cmbTipoPoliza'])) {
    // Si elimina o modifica viene el tipo por get
    $_POST['cmbTipoPoliza'] = $_GET['cmbTipoPoliza'];
}

if (isset($_GET ['EditJournal'])) {
}

if (isset($_GET ['NewJournal']) and $_GET ['NewJournal'] == 'Yes' and isset($_SESSION ['JournalDetail'])) {
    unset($_SESSION ['JournalDetail']->GLEntries);
    unset($_SESSION ['JournalDetail']);
    $_SESSION['tipoRegistro'] = 'Cargo';
}

if (isset($_POST['btnActualizar'])) {
    $_POST['CommitBatch'] = 'Actualizar Modificaciones...';
} else if (isset($_POST['btnAgregar'])) {
    $_POST['CommitBatch'] = 'Aceptar y Procesar Nueva Póliza...';
}

if (! isset($_SESSION ['JournalDetail'])) {
    $_SESSION ['JournalDetail'] = new Journal();
    
    $SQL = 'SELECT accountcode FROM bankaccounts';
    $result = DB_query($SQL, $db);
    $i = 0;
    while ($Act = DB_fetch_row($result)) {
        $_SESSION ['JournalDetail']->BankAccounts [$i] = $Act [0];
        $i ++;
    }
    
    if (isset($_GET ['typeno']) and isset($_GET ['type'])) {
        $SQL = "SELECT gltrans.amount,
                                gltrans.narrative, 
                                gltrans.account, 
                                chartmaster.accountname,
                                gltrans.tag,
                                gltrans.type, 
                                gltrans.typeno,
                                gltrans.cat_cuenta,
                                gltrans.rate,
                                gltrans.debtorno,
                                gltrans.branchno,
                                gltrans.stockid,
                                gltrans.qty,
                                gltrans.grns,
                                gltrans.standardcost,
                                gltrans.loccode,
                                gltrans.dateadded, 
                                gltrans.suppno,
                                gltrans.purchno,
                                gltrans.chequeno,
                                gltrans.jobref,
                                gltrans.bancodestino,
                                gltrans.rfcdestino,
                                gltrans.cuentadestino,
                                Date_Format(gltrans.trandate,'%d/%m/%Y') as trandate,
                                tags.legalid,
                                gltrans.posted,
                                gltrans.periodno,
                                tags.tagref,
                                tags.tagname,
                                legalbusinessunit.legalname, gltrans.ln_ue
                FROM gltrans USE INDEX (TypeNo)
                    JOIN chartmaster FORCE INDEX (AccountCode) ON gltrans.account = chartmaster.accountcode
                    JOIN tags ON gltrans.tag = tags.tagref
                    JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
                  WHERE  gltrans.typeno = " . $_GET ['typeno'] . " 
                    AND  gltrans.type   = " . $_GET ['type'] . "
                    AND  gltrans.tag    = '" . $_GET ['tag'] . "'";
        
        $ErrMsg = _('No transactions were returned by the SQL because');


        $TransResult = DB_query($SQL, $db, $ErrMsg);/*cga*/ //echo '<br>2:'.$SQL;
        $k = 1;
        
        $_SESSION ['JournalDetail']->origJnlType = $_GET ['type'];
        $_SESSION ['JournalDetail']->origJnlIndex = $_GET ['typeno'];
        $_SESSION ['JournalDetail']->JnlTag = $_GET ['tagref'];
        
        $ji = 0;
        $legalname = "";
        $tagname = "";
        while ($myrow = DB_fetch_array($TransResult)) {
            $legalname = $myrow ['legalname'];
            $tagname = $myrow ['tagname'];

            $PeriodNo = $myrow ['periodno'];

            $_SESSION ['JournalDetail']->Add_To_GLAnalysis($myrow ['amount'], $myrow ['narrative'], $myrow ['account'], $myrow ['accountname'], $myrow ['tag'], $myrow ['legalid'], $myrow ['rate'], $myrow ['debtorno'], $myrow ['branchno'], $myrow ['stockid'], $myrow ['qty'], $myrow ['grns'], $myrow ['loccode'], $myrow ['standardcost'], $myrow ['suppno'], $myrow ['purchno'], $myrow ['chequeno'], $myrow ['cat_cuenta'], $myrow ['jobref'], $myrow ['bancodestino'], $myrow ['rfcdestino'], $myrow ['cuentadestino'], $myrow ['posted'], $myrow ['ln_ue']);
            
            if ($ji == 0) {
                /* NUEVA FUNCIONALIDAD PARA REGISTRAR TAG Y LEGALID DE ESTA POLIZA EN LA CLASE */
                $_SESSION ['JournalDetail']->JnlTag = $myrow ['tag'];
                $_SESSION ['JournalDetail']->JnlLegalId = $myrow ['legalid'];
                
                // EN LA PRIMERA TRANSACCION DE ESTA POLIZA, IDENTIFICA LA FECHA ORIGINAL DE LA POLIZA
                $_SESSION ['JournalDetail']->origJnlDate = $myrow ['trandate'];
                if (! isset($_POST ['tag'])) {
                    $_POST ['tag'] = $myrow ['tag'];
                    $_POST ['legalid'] = $myrow ['legalid'];
                }
            }
            $ji = $ji + 1;
        }

        //Archivos agregar ------------------------
        // Se almacena XML y PDF en file system
        $carpeta = 'Polizas';
        $dir     = "/var/www/html" . dirname($_SERVER['PHP_SELF']) . "/companies/" . $_SESSION['DatabaseName'] . "/SAT/" . str_replace('.', '', str_replace(' ', '', $legalname)) . "/XML/" . $carpeta . "/" . str_replace('.', '', str_replace(' ', '', $tagname)) . "/";
        $carpetaPoliza = "Poliza_".$_GET ['type']."_".$_GET ['typeno']."_".$_GET ['tag']."/"; // Carpeta de los archivos poliza
        $_SESSION ['JournalDetail']->PolizaRutaArchivos = $dir.$carpetaPoliza; // Ruta completa de la carpeta Archivos
        //Archivos agregar ------------------------
        
        $SQL = "SELECT * FROM systypescat WHERE typeid=" . $_GET ['type'];
        $ErrMsg = _('No transactions were returned by the SQL because');
        $TransResult = DB_query($SQL, $db, $ErrMsg);/*cga*/ //echo '<br>3:'.$SQL;
        
        if ($myrow = DB_fetch_array($TransResult)) {
            $_SESSION ['JournalDetail']->origJnlTypeName = $myrow ['typename'];
        }
    }
}

if (isset($Fecha)) {
    $_SESSION ['JournalDetail']->JnlDate = $Fecha;
    if (! Is_Date($Fecha)) {
        prnMsg(_('La fecha capturada no es valida, favor de capturar una fecha en el formato') . $_SESSION ['DefaultDateFormat'], 'warn');
        $_POST ['CommitBatch'] = 'Do not do it the date is wrong';
    }
    if ($_SESSION ['FutureDate'] == 1) {
        $FechaValida = rtrim($FromDia) . '/' . rtrim($FromMes) . '/' . rtrim($FromYear);
        if (Date1GreaterThanDate2($FechaValida, date("d/m/Y")) == 1 and Havepermission($_SESSION ['UserID'], 410, $db) == 0) {
            prnMsg(_('La fecha es posterior y no cuenta con los permisos para realizar esta operacion.'), 'error');
            echo '<br>';
            $FromYear = date('Y');
            $FromMes = date('m');
            $FromDia = date('d');
            $Fecha = rtrim($FromDia) . '-' . rtrim($FromMes) . '-' . rtrim($FromYear);
            $_SESSION ['JournalDetail']->JnlDate = $Fecha;
            
            $_POST ['CommitBatch'] = 'Do not do it the date is wrong';
        }
    }
}

if (isset($_POST ['JournalType'])) {
    $_SESSION ['JournalDetail']->JournalType = $_POST ['JournalType'];
}
$msg = '';

if (isset($_POST ['CommitBatch']) and ($_POST ['CommitBatch'] == _('Aceptar y Procesar Nueva Póliza...') or $_POST ['CommitBatch'] == _('Actualizar Modificaciones...'))) {
    /* Obtiene Periodo y verifica que no este cerrado para esta razon social */
    //if (empty($PeriodNo)) {
    $PeriodNo = GetPeriodXLegal($_SESSION ['JournalDetail']->JnlDate, $_SESSION ['JournalDetail']->JnlLegalId, $db);
    //      echo "<br>PeriodNo: ".$PeriodNo;

    if (isset($_POST['fecha_periodo']) and $_POST['FromMes']==12 && $PeriodNo!= -999) {
        $PeriodNo = $PeriodNo + 0.5;
    }
    //}

    $userorig = "";
    
    $lafecha_separada= explode("-", $_SESSION ['JournalDetail']->JnlDate);
    $fecha_guardar= $lafecha_separada[2]."-".$lafecha_separada[1]."-".$lafecha_separada[0];
    
    // buscar usuario que origino la poliza
    $qry = "SELECT gltrans_user.userid, gltrans.userid as gluserid,  
                gltrans_user.origtrandategl as origtrandategl, gltrans.lasttrandate
            FROM gltrans
                LEFT JOIN gltrans_user ON gltrans.counterindex = gltrans_user.id
            WHERE gltrans.type = " . $_SESSION ['JournalDetail']->origJnlType . "
                and gltrans.typeno = " . $_SESSION ['JournalDetail']->origJnlIndex . "
            LIMIT 1";
     
    $rs = DB_query($qry, $db);/*cga*/ //echo '<br>4:'.$qry;
    $reg = DB_fetch_array($rs);
    
    if (DB_num_rows($rs) > 0) {
        if (empty($reg ['gluserid'])) {
            $userorig = $reg ['userid'];
            $fechaorig = $reg ['lasttrandate'];
            if (empty($userorig)) {
                $qry = "SELECT useridorig, origtrandate
                        FROM logmodificapolizas
                        WHERE type = " . $_SESSION ['JournalDetail']->origJnlType . "
                            AND typeno = " . $_SESSION ['JournalDetail']->origJnlIndex . "
                        LIMIT 1";
                
                $rs = DB_query($qry, $db);/*cga*/ //echo '<br>5:'.$qry;
                
                if ($reg = DB_fetch_array($rs)) {
                    $userorig = $reg ['useridorig'];
                    $fechaorig = $reg ['origtrandate'];
                }
            }
        } else {
            $userorig = $reg ['gluserid'];
            $fechaorig = $reg ['origtrandategl'];
        }
    } else {
        $qry = "SELECT useridorig, origtrandate
                    FROM logmodificapolizas
                    WHERE type = " . $_SESSION ['JournalDetail']->origJnlType . "
                        AND typeno = " . $_SESSION ['JournalDetail']->origJnlIndex . "
                    LIMIT 1";
        
        $rs = DB_query($qry, $db);/*cga*/ //echo '<br>6:'.$qry;
        
        if ($reg = DB_fetch_array($rs)) {
            $userorig = $reg ['useridorig'];
            $fechaorig = $reg ['origtrandate'];
        }
    }
    
    $actualiza="UPDATE gltrans SET userid= '".$userorig."' 
                WHERE type = " . $_SESSION ['JournalDetail']->origJnlType . " 
                    AND typeno = " . $_SESSION ['JournalDetail']->origJnlIndex. " 
                    AND (userid= '' OR userid IS NULL)";

    DB_query($actualiza, $db); /*cga*/ //echo '<br>7:'.$actualiza;// actualizar poliza con el usuario original si no lo tiene
    
    if ($PeriodNo != -999) {
        if ($_POST ['CommitBatch'] == _('Actualizar Modificaciones...')) {
            // GUARDA LOG DE POLIZA ORIGINAL
            $result = DB_Txn_Begin($db);
            $sql = "INSERT INTO logmodificapolizas(type,
                                                    typeno,
                                                    trandate,
                                                    periodno,
                                                    account,
                                                    narrative, 
                                                    amount, 
                                                    tag, 
                                                    userid, 
                                                    origtrandate, 
                                                    comentarios,
                                                    useridorig)
                    SELECT type,
                            typeno,
                            trandate,
                            periodno,
                            account,
                            narrative, 
                            amount, 
                            tag,    
                            '" . $_SESSION ['UserID'] . "',
                            Now(),
                            'Póliza Original',
                            '" . $userorig . "' 
                    FROM gltrans
                    WHERE typeno = " . $_SESSION ['JournalDetail']->origJnlIndex . " 
                        AND type = " . $_SESSION ['JournalDetail']->origJnlType;



            $ErrMsg = _('No pude insertar el movimiento en contabilidad porque');
            $DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
            
            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);/*cga*/ //echo '<br>8:'.$sql;
            
            // SI VAMOS A MODIFICAR ESTA POLIZA, PRIMERO ELIMINAR MOVIMIENTOS ANTERIORES PARA DESPUES PROCESARLA NORMALMENTE
            
            $SQL = "DELETE FROM gltrans
                    WHERE typeno = " . $_SESSION ['JournalDetail']->origJnlIndex . " 
                        AND type = " . $_SESSION ['JournalDetail']->origJnlType . " 
                        AND gltrans.tag='".$_SESSION ['JournalDetail']->JnlTag ."'";
            
            
            $ErrMsg = _('No pude insertar el movimiento en contabilidad porque');
            $DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
            $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);/*cga*/ //echo '<br>9:'.$SQL;

            $SQL = "DELETE FROM banktrans
                    WHERE transno = " . $_SESSION ['JournalDetail']->origJnlIndex . " 
                        AND type = " . $_SESSION ['JournalDetail']->origJnlType . "
                        AND tagref='".$_SESSION ['JournalDetail']->JnlTag."'";
                        
            
            $ErrMsg = _('No pude insertar el movimiento en contabilidad porque');
            $DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
            $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);/*cga*/ //echo '<br>10:'.$SQL;
            $result = DB_Txn_Commit($db);
        }
        
        if ($_POST ['CommitBatch'] == _('Actualizar Modificaciones...')) {
            $TransNo = $_SESSION ['JournalDetail']->origJnlIndex;
            $TipoPoliza = $_SESSION ['JournalDetail']->origJnlType;
        } else {
            $TipoPoliza = $_POST['cmbTipoPoliza'];
            $TransNo = GetNextTransNo($TipoPoliza, $db);
        }
        
        $result = DB_Txn_Begin($db);
        
        foreach ($_SESSION ['JournalDetail']->GLEntries as $JournalItem) {
            if ($_POST ['JournalType'] == _('Reversing')) {
                if ($_POST ['CommitBatch'] == _('Actualizar Modificaciones...')) {
                    $sql = "INSERT INTO logmodificapolizas(type,
                                                            typeno,
                                                            trandate,
                                                            periodno,
                                                            account,
                                                            narrative, 
                                                            amount, 
                                                            tag, 
                                                            userid, 
                                                            origtrandate, 
                                                            comentarios,
                                                            useridorig)
                        VALUES('" . $TipoPoliza . "',
                                '" . $TransNo . "',
                                '" . $fecha_guardar . "',
                                '" . ($PeriodNo) . "',
                                '" . $JournalItem->GLCode . "',
                                'Reversal - " . DB_escape_string($JournalItem->Narrative) . "',
                                '" . - ($JournalItem->Amount) . ",
                                '" . $JournalItem->tag . "',
                                '" . $_SESSION ['UserID'] . "',
                                Now(), 
                                'Póliza Nueva',
                                '" . $userorig . "')";
                    $ErrMsg = _('No pude insertar el movimiento en contabilidad porque');
                    $DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
                    //CGA
                    //echo '<br>q003:'.$sql;

                    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);/*cga*/ //echo '<br>11:'.$sql;
                }
        
                $Narrative = "Reversal - " . DB_escape_string($JournalItem->Narrative) . '@con la póliza: ' . $_SESSION ['JournalDetail']->origJnlIndex . '-' . $_SESSION ['JournalDetail']->origJnlType;
                $ErrMsg = _('No pude insertar el movimiento en contabilidad porque');
                $DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
                
                $SQL = Insert_Gltrans($TipoPoliza, $TransNo, $fecha_guardar, $PeriodNo, $JournalItem->GLCode, $Narrative, $JournalItem->tag, $userorig, $JournalItem->rate, $JournalItem->debtorno, $JournalItem->Branch, $JournalItem->stockid, $JournalItem->qty, $JournalItem->grns, $JournalItem->loccode, $JournalItem->EstimatedAvgCost, $JournalItem->Suppno, $JournalItem->Purchno, - ($JournalItem->Amount), $db, $JournalItem->ChequeNo, $JournalItem->catcuenta, $JournalItem->jobref, $JournalItem->bancodestino, $JournalItem->rfcdestino, $JournalItem->cuentadestino, $JournalItem->posted, $JournalItem->ue);//, "", $JournalItem->posted);
                //CGA
                //echo '<br>398: '.$SQL;

                $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);/*cga*/ //echo '<br>12:'.$SQL;
                
                if (in_array($JournalItem->GLCode, $_SESSION ['JournalDetail']->BankAccounts)) {
                    $SQL = "INSERT INTO banktrans (banktransid, type, transno, bankact, ref, amountcleared, exrate,
                        functionalexrate, transdate, banktranstype, amount, currcode, tagref, beneficiary, chequeno,
                        usuario, fechacambio, batchconciliacion, matchperiodno) ";
                    $SQL = $SQL . "VALUES (NULL,'" . $TipoPoliza . "','" . $TransNo . "','" . $JournalItem->GLCode . "','" . $JournalItem->Narrative . '@con la póliza: ' . $_SESSION ['JournalDetail']->origJnlIndex . '-' . $_SESSION ['JournalDetail']->origJnlType . "','0','1','1','" . $fecha_guardar . "','Póliza Contable','" . - ($JournalItem->Amount) . "','MXN','" . $JournalItem->tag . "', '','','', Now(),'0','0')";
                    
                    // echo "<font color='white'>" . $SQL . "</font>";
                    $ErrMsg = _('No pude insertar el movimiento en contabilidad porque');
                    $DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
                    //CGA
                    //echo '<br>q005:'.$SQL;

                    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);/*cga*/ //echo '<br>13:'.$SQL;
                }
            } else {
                if ($_POST ['CommitBatch'] == _('Actualizar Modificaciones...')) {
                    $sql = "INSERT INTO logmodificapolizas(type,
                                                            typeno,
                                                            trandate,
                                                            periodno,
                                                            account,
                                                            narrative, 
                                                            amount, 
                                                            tag, 
                                                            userid, 
                                                            origtrandate, 
                                                            comentarios,
                                                            useridorig)
                            VALUES ('" . $TipoPoliza . "',
                                    '" . $TransNo . "',
                                    '" . $fecha_guardar . "',
                                    '" . ($PeriodNo) . "',
                                    '" . $JournalItem->GLCode . "',
                                    '" . DB_escape_string($JournalItem->Narrative) . "',
                                    '" . $JournalItem->Amount . "',
                                    '" . $JournalItem->tag . "',
                                    '" . $_SESSION ['UserID'] . "',
                                    Now(), 
                                    'Póliza Nueva',
                                    '" . $userorig . "')";
                    
                    $ErrMsg = _('No pude insertar el movimiento en contabilidad porque');
                    $DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
                    //CGA
                    //echo '<br>q001:'.$sql;

                    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);/*cga*/ //echo '<br>15:'.$sql;
                }
                $ErrMsg = _('No pude insertar el movimiento en contabilidad porque');
                $DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
                $Narrative = DB_escape_string($JournalItem->Narrative) . '@con la póliza: ' . $_SESSION ['JournalDetail']->origJnlIndex . '-' . $_SESSION ['JournalDetail']->origJnlType;
                if (empty(trim($JournalItem->rate))) {
                    $JournalItem->rate = 1;
                }
                if (empty(trim($JournalItem->qty))) {
                    $JournalItem->qty = 0;
                }
                if (empty(trim($JournalItem->grns))) {
                    $JournalItem->grns = 0;
                }
                if (empty(trim($JournalItem->EstimatedAvgCost))) {
                    $JournalItem->EstimatedAvgCost = 0;
                }
                $SQL = Insert_Gltrans($TipoPoliza, $TransNo, $fecha_guardar, $PeriodNo, $JournalItem->GLCode, $Narrative, $JournalItem->tag, $userorig, $JournalItem->rate, $JournalItem->debtorno, $JournalItem->Branch, $JournalItem->stockid, $JournalItem->qty, $JournalItem->grns, $JournalItem->loccode, $JournalItem->EstimatedAvgCost, $JournalItem->Suppno, $JournalItem->Purchno, ($JournalItem->Amount), $db, $JournalItem->ChequeNo, $JournalItem->catcuenta, $JournalItem->jobref, $JournalItem->bancodestino, $JournalItem->rfcdestino, $JournalItem->cuentadestino, $JournalItem->posted, $JournalItem->ue);//, "", $JournalItem->posted);

                $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);/*cga*/ //echo '<br>16:'.$SQL;

                if ($_POST ['CommitBatch'] == _('Actualizar Modificaciones...')) {
                    $usql = "UPDATE gltrans_user
                            LEFT JOIN gltrans  ON gltrans.type = " . $TipoPoliza . "  and gltrans.typeno = " . $TransNo . " and gltrans_user.id =  gltrans.counterindex 
                        SET gltrans_user.userid = '" . $userorig . "', 
                            gltrans_user.origtrandategl = '" . $fechaorig . "'
                        WHERE gltrans.counterindex is not null";
                        
                    $ErrMsg = _('No pude actualizar los datos de la póliza porque');
                    $DbgMsg = _('El SQL que fallo para actualizar datos de la póliza fue');
                    $result = DB_query($usql, $db, $ErrMsg, $DbgMsg, true);/*cga*/ //echo '<br>17:'.$usql;
                }



                /**
                 * SI ES UNA CUENTA DE BANCOS INSERTO UN MOVIMIENTO EN BANKTRANS
                 */
                // echo "<br>1.- " . $JournalItem->GLCode;
                // echo "<br>2.- " . count($_SESSION['JournalDetail']->BankAccounts);
                if (in_array($JournalItem->GLCode, $_SESSION ['JournalDetail']->BankAccounts)) {
                    $SQL = "INSERT INTO banktrans (banktransid, type, transno, bankact, ref, amountcleared, exrate,
                        functionalexrate, transdate, banktranstype, amount, currcode, tagref, beneficiary, chequeno,
                        usuario, fechacambio, batchconciliacion, matchperiodno) ";
                    $SQL = $SQL . "VALUES (NULL,'" . $TipoPoliza . "','" . $TransNo . "','" . $JournalItem->GLCode . "','" . DB_escape_string($JournalItem->Narrative) . '@con la póliza: ' . $_SESSION ['JournalDetail']->origJnlIndex . '-' . $_SESSION ['JournalDetail']->origJnlType . "','0','1','1','" . $fecha_guardar . "','Póliza Contable','" . $JournalItem->Amount . "','MXN','" . $JournalItem->tag . "', '','','', Now(),'0','0')";
                    
                    // echo "<font color='white'>" . $SQL . "</font>";
                    $ErrMsg = _('No pude insertar el movimiento en contabilidad porque');
                    $DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');

                        //CGA

                    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);/*cga*/ //echo '<br>18:'.$SQL;
                }
            
            /**
             * FIN
             * SI ES UNA CUENTA DE BANCOS INSERTO UN MOVIMIENTO EN BANKTRANS
             */
            }
        }

        //Buscar UR y razon social para almacenar archivos
        $sqlDatos = "SELECT tags.tagref, tags.tagname, legalbusinessunit.legalname
                FROM gltrans
                INNER JOIN tags ON gltrans.tag = tags.tagref
                INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
                WHERE typeno='" . $TransNo . "' and type='" . $TipoPoliza . "'";

        $result = DB_query($sqlDatos, $db);
        $row = DB_fetch_array($result);
        $tagref = $row ['tagref'];
        $tagname = $row ['tagname'];
        $legalname = $row ['legalname'];

        //Archivos agregar ------------------------
        // Se almacena XML y PDF en file system
        $carpeta = 'Polizas';
        $dir     = "/var/www/html" . dirname($_SERVER['PHP_SELF']) . "/companies/" . $_SESSION['DatabaseName'] . "/SAT/" . str_replace('.', '', str_replace(' ', '', $legalname)) . "/XML/" . $carpeta . "/" . str_replace('.', '', str_replace(' ', '', $tagname)) . "/";

        //Variable para ruta de archivos, Poliza_type_transno_tagref
        $RutaAnterior = ""; //Si cambiar pasar archivos
        if (empty($_SESSION ['JournalDetail']->PolizaRutaArchivos)) {
            $carpetaPoliza = "Poliza_".$TipoPoliza."_".$TransNo."_".$tagref."/"; // Carpeta de los archivos poliza
            $_SESSION ['JournalDetail']->PolizaRutaArchivos = $dir.$carpetaPoliza; // Ruta completa de la carpeta Archivos
        } else {
            $carpetaPoliza = "Poliza_".$TipoPoliza."_".$TransNo."_".$tagref."/"; // Carpeta de los archivos poliza
            if ($_SESSION ['JournalDetail']->PolizaRutaArchivos != $dir.$carpetaPoliza) {
                //Si es diferente tomar la nueva carpeta y si no dejar la misma
                $RutaAnterior = $_SESSION ['JournalDetail']->PolizaRutaArchivos;
                $_SESSION ['JournalDetail']->PolizaRutaArchivos = $dir.$carpetaPoliza; // Ruta completa de la carpeta Archivos
            }
        }
        //echo "<br>PolizaRutaArchivos: ".$_SESSION ['JournalDetail']->PolizaRutaArchivos."<br>";
        if (!file_exists($_SESSION ['JournalDetail']->PolizaRutaArchivos)) {
            //Si no existe carpeta, crear
            if (!mkdir($_SESSION ['JournalDetail']->PolizaRutaArchivos, 0777, true)) {
                //Si no se crea la carpeta
                prnMsg(_('No se pudo crear la carpeta para almacenar los archivos cargados'), 'error');
            }
        }
        if (file_exists($_SESSION ['JournalDetail']->PolizaRutaArchivos)) {
            //Si existe carpeta, Comparar si adjunto archivos
            $File_Arcchivos = $_FILES['file_mul']['name'][0];
            if ($File_Arcchivos != '') {
                //recorremos los archivos cargados
                foreach ($_FILES['file_mul']['name'] as $i => $name) {
                    if (strlen($_FILES['file_mul']['name'][$i]) > 1) {
                        //Guardamos el archivo en la carpeta, nombre normal
                        if (move_uploaded_file($_FILES['file_mul']['tmp_name'][$i], $_SESSION ['JournalDetail']->PolizaRutaArchivos.$name)) {
                            $datosExt = explode(".", $name);
                            $extencion = $datosExt [1];

                            $ArchivoXmlCfdi = "";
                            if ($extencion == "xml") {
                                //Si es xml obtener informacion
                                $ArchivoXmlCfdi = file_get_contents($_SESSION ['JournalDetail']->PolizaRutaArchivos.$name, true);
                            }

                            $SQL = "INSERT INTO gltrans_files (type, typeno, tagref, ruta, nombre, fecha, userid, xml)
                                    VALUES (".$TipoPoliza.", ".$TransNo.", '".$tagref."', '".$_SESSION ['JournalDetail']->PolizaRutaArchivos."', '".$name."', NOW(), '".$_SESSION['UserID']."', '".$ArchivoXmlCfdi."') ";
                            $ErrMsg = _('No pude insertar el archivo porque');
                            $DbgMsg = _('El SQL que fallo para insertar el archivo fue');
                            $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                        } else {
                            prnMsg(_('No se pudo almacenar '.$name), 'error');
                        }
                    }
                }
            }
        }
        //Archivos agregar ------------------------
        
        $ErrMsg = _('No pude procesar y confirmar los movimientos');
        // $result= DB_Txn_Begin($db);
        $result = DB_Txn_Commit($db);
        
        prnMsg(_('Póliza') . ' ' . $TransNo . ' ' . _('ha sido procesada exitosamente'), 'success');
        $p = $PeriodNo;
        $datejournal = FormatDateForSQL($_SESSION ['JournalDetail']->JnlDate);
        
        unset($Fecha);
        unset($_POST ['JournalType']);
        unset($_SESSION ['JournalDetail']->GLEntries);
        unset($_SESSION ['JournalDetail']);
        
        // javascript:Abrir_ventana('popup.html')
        $datejournal = str_replace("/", "-", $datejournal);

        $url = "&FromCust=>1&ToCust=>1&PrintPDF=>Yes&type=>" . $TipoPoliza . "&TransNo=>$TransNo&periodo=>$p&trandate=>$datejournal";
        $enc = new Encryption;
        $url = $enc->encode($url);
        $liga= "URL=" . $url;
        echo "<br><a href='PrintJournal.php?".$liga."' target='_blank'><b><img src='images/printer.png' title='Póliza' alt=''>&nbsp;&nbsp; Imprime Póliza</b></a>";
        
        // echo "<br><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . "&NewJournal=Yes'>"._('Enter Another General Ledger Journal').'</a>';

        $url = "&NewJournal=>Yes";
        $enc = new Encryption;
        $url = $enc->encode($url);
        $liga= "URL=" . $url;
        echo "<br><br><a href='" . $_SERVER ['PHP_SELF'] . '?' . $liga . "'>" . _('Captura Nueva Póliza') . '</a>';
        include 'includes/footer_Index.inc';
        exit();
    } else {
        prnMsg('La fecha que selecciono corresponde a un período contable cerrado !!, la operacion no se proceso.', "error");
    }
} elseif (isset($_GET ['Delete'])) {
    if ($_POST['cmbTipoPoliza'] == '282') {
        // Si es extrapresupuestal borrar datos
        unset($_SESSION ['JournalDetail']->GLEntries);
    } else {
        $_SESSION ['JournalDetail']->Remove_GLEntry($_GET ['Delete']);
    }
} elseif (isset($_GET ['Modificar'])) {
    $_POST ['tag'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->tag;
    $_POST ['selUE'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->ue;
    //$_POST ['GLManualCode'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->GLCode;
    $_POST ['GLAmount'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->Amount;

    $numBuscar = 0;
    if ($_GET ['Modificar'] == '0') {
        $numBuscar = 1;
    }
    
    if ($_POST ['GLAmount'] >= 0) {
        $_POST ['Debit'] = $_POST ['GLAmount'];
        $_POST ['Credit'] = 0;
        $cuentacargo= $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->GLCode;
        
        if ($_POST['cmbTipoPoliza'] == '282') {
            // Si es poliza extra presupuestal
            $_POST['GLCodeExtraPre'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->GLCode.' - '.$_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->GLActName;

            // Obtener datos del abono
            $_POST['GLCodeAbonoExtraPre'] = $_SESSION ['JournalDetail']->GLEntries [$numBuscar]->GLCode.' - '.$_SESSION ['JournalDetail']->GLEntries [$numBuscar]->GLActName;
            $_POST ['Credit'] = $_SESSION ['JournalDetail']->GLEntries [$numBuscar]->Amount * -1;
        }
    } else {
        $_POST ['Debit'] = 0;
        $_POST ['Credit'] = $_POST ['GLAmount'] * - 1;
        $cuentaabono= $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->GLCode;
        
        if ($_POST['cmbTipoPoliza'] == '282') {
            // Si es poliza extra presupuestal
            $_POST['GLCodeAbonoExtraPre'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->GLCode.' - '.$_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->GLActName;

            // Obtener datos del cargo
            $_POST['GLCodeExtraPre'] = $_SESSION ['JournalDetail']->GLEntries [$numBuscar]->GLCode.' - '.$_SESSION ['JournalDetail']->GLEntries [$numBuscar]->GLActName;
            $_POST ['Debit'] = $_SESSION ['JournalDetail']->GLEntries [$numBuscar]->Amount;
        }
    }

    $_POST ['GLNarrative'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->Narrative;
    $_POST ['rate'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->rate;
    $_POST ['debtorno'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->debtorno;
    $_POST ['Branch'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->Branch;
    $_POST ['stockid'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->stockid;
    $_POST ['qty'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->qty;
    $_POST ['grns'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->grns;
    $_POST ['loccode'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->loccode;
    $_POST ['EstimatedAvgCost'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->EstimatedAvgCost;
    $_POST ['Suppno'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->Suppno;
    $_POST ['Purchno'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->Purchno;
    $_POST ['ChequeNo'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->ChequeNo;
    $_POST ['catcuenta'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->catcuenta;
    $_POST ['jobref'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->jobref;
    $_POST ['bancodestino'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->bancodestino;
    $_POST ['rfcdestino'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->rfcdestino;
    $_POST ['cuentadestino'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->cuentadestino;
    /* ESTO PASA HASTA EL FINAL QUE FIJAMOS LA INFORMACION EN TODOS LOS CAMPOS */
    $_SESSION ['JournalDetail']->Remove_GLEntry($_GET ['Modificar']);


    if ($_POST['cmbTipoPoliza'] == '282') {
        // Si es poliza extra presupuestal
        $_POST['selectGLNarrative'] = $_SESSION ['JournalDetail']->extraPresupuestalCod;

        // Eliminar partidas seleccionadas ya que modifico
        unset($_SESSION ['JournalDetail']->GLEntries);
        unset($_SESSION ['JournalDetail']->extraPresupuestalCod);
        unset($_SESSION ['JournalDetail']->extraPresupuestalNom);
        $_SESSION ['JournalDetail']->GLItemID = 0;
        $_SESSION ['JournalDetail']->GLItemCounter = 0;
        $_SESSION ['JournalDetail']->JournalTotal = 0;
    }
} elseif (isset($_GET ['Copiar'])) {
    $_POST ['tag'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Copiar']]->tag;
    $_POST ['selUE'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Copiar']]->ue;
    $_POST ['GLManualCode'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Copiar']]->GLCode;
    $_POST ['GLAmount'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Copiar']]->Amount;
    
    if ($_POST ['GLAmount'] >= 0) {
        $_POST ['Debit'] = $_POST ['GLAmount'];
        $_POST ['Credit'] = 0;
    } else {
        $_POST ['Debit'] = 0;
        $_POST ['Credit'] = $_POST ['GLAmount'] * - 1;
    }
    
    $_POST ['GLNarrative'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Copiar']]->Narrative;
    $_POST ['rate'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->rate;
    $_POST ['debtorno'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->debtorno;
    $_POST ['Branch'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->Branch;
    $_POST ['stockid'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->stockid;
    $_POST ['qty'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->qty;
    $_POST ['grns'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->grns;
    $_POST ['loccode'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->loccode;
    $_POST ['EstimatedAvgCost'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->EstimatedAvgCost;
    $_POST ['Suppno'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->Suppno;
    $_POST ['Purchno'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->Purchno;
    $_POST ['ChequeNo'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->ChequeNo;
    $_POST ['catcuenta'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->catcuenta;
    $_POST ['jobref'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->jobref;
    $_POST ['bancodestino'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->bancodestino;
    $_POST ['rfcdestino'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->rfcdestino;
    $_POST ['cuentadestino'] = $_SESSION ['JournalDetail']->GLEntries [$_GET ['Modificar']]->cuentadestino;
} elseif (isset($_POST ['Process'])) {
    // and $_POST ['Process'] == _('Aceptar')
    $montocargo= 0;
    $montoabono= 0;
    $nombrecargo= "";
    $nombreabono= "";

    if ($_POST['cmbTipoPoliza'] == '282') {
        // Si es poliza extra presupuestal
        $_POST['GLCode'] = $_POST['GLCodeExtraPre'];
        $_POST['GLCodeAbono'] = $_POST['GLCodeAbonoExtraPre'];
        $_SESSION ['JournalDetail']->extraPresupuestalCod = $_POST['selectGLNarrative'];
        $SQL = "SELECT tb_matriz_extraptal.categorydescription  FROM tb_matriz_extraptal WHERE tb_matriz_extraptal.categoryid = '".$_POST['selectGLNarrative']."'";
        $TransResult = DB_query($SQL, $db);
        $myrow = DB_fetch_array($TransResult);
        $_SESSION ['JournalDetail']->extraPresupuestalNom = $myrow['categorydescription'];
        $_POST['GLNarrative'] = $myrow['categorydescription'];

        unset($_POST['GLCodeExtraPre']);
        unset($_POST['GLCodeAbonoExtraPre']);
        unset($_POST['selectGLNarrative']);
    }

    if ($_POST ['GLCode'] != '') {
        $extract = explode(' - ', $_POST ['GLCode']);
        $_POST ['GLCode'] = $extract [0];
    }

    if ($_POST ['GLCodeAbono'] != '') {
        $extract = explode(' - ', $_POST ['GLCodeAbono']);
        $_POST ['GLCodeAbono'] = $extract [0];
    }

    // echo "entra";
    if ($_POST ['Debit'] > 0) {
        // echo "<br>cargo";
        // Agrego Cargo mostrar Abono
        $_SESSION['tipoRegistro'] = 'Abono';
        $montocargo = $_POST ['Debit'];
    }

    if ($_POST ['Credit'] > 0) {
        // echo "<br>abono";
        // Agrego Abono mostrar Cargo
        $_SESSION['tipoRegistro'] = 'Cargo';
        $montoabono = '-' . $_POST ['Credit'];
    }
    // echo "<br>montocargo: ".$montocargo;
    // echo "<br>montoabono: ".$montoabono;
    // valida cuentas de costos
    $listacostos = array ();
    $SQL = 'select distinct glcode from cogsglpostings';
    // echo $SQL;
    $resultcostos = DB_query($SQL, $db);/*cga*/ //echo '<br>19:'.$SQL;
    $counter = 0;
    while ($ActCostos = DB_fetch_row($resultcostos)) {
        $listacostos [$counter] = $ActCostos [0];
        $counter = $counter + 1;
    }
    
    // valida cuentas de ventas
    $listaventas = array ();
    $SQL = 'select distinct salesglcode from salesglpostings';
    $result = DB_query($SQL, $db);/*cga*/ //echo '<br>20:'.$SQL;
    $counter = 0;
    while ($Act = DB_fetch_row($result)) {
        $listaventas [$counter] = $Act [0];
        $counter = $counter + 1;
    }
    
    // valida cuentas de cheques//
    $ctasbancopermitidas = array ();
    $SQL = 'SELECT bankaccounts.accountcode
        FROM bankaccounts inner join  chartmaster on bankaccounts.accountcode=chartmaster.accountcode
                inner join  tagsxbankaccounts on bankaccounts.accountcode = tagsxbankaccounts.accountcode
        GROUP BY bankaccounts.accountcode';
    $result = DB_query($SQL, $db);/*cga*/ //echo '<br>21:'.$SQL;
    $counter = 0;
    while ($ActCheque = DB_fetch_row($result)) {
        $ctasbancopermitidas [$counter] = $ActCheque [0];
        $counter = $counter + 1;
    }
    
    // valida cuentas de inventarios
    $ctasinventarios = array ();
    $SQL = "SELECT c.accountcode 
        FROM chartmaster c
        WHERE c.group_ = '130000 INVENTARIOS'";
    $result = DB_query($SQL, $db);/*cga*/ //echo '<br>22:'.$SQL;
    $counter = 0;
    while ($ActCheque = DB_fetch_row($result)) {
        $ctasinventarios [$counter] = $ActCheque [0];
        $counter = $counter + 1;
    }
    
    if (!empty($_POST ['GLCode']) and is_numeric($montocargo)) {
        // If a manual code was entered need to check it exists and isnt a bank account
        $AllowThisPosting = true; // by default
                                  // SE CREO LA FUNCION PARA DAR PERMISOS A CIERTOS USUARIOS MODIFICAR CUENTSA DE CLIENTES Y PROVEEDORES
                                  // echo "<br>entra aki";
        if ($_SESSION ['ProhibitJournalsToControlAccounts'] == 1 && 1 == 2) {
            // VALIDACION DE CUENTAS DE CLIENTES
            if ($_SESSION ['CompanyRecord'] ['gllink_debtors'] == '1' and (($_POST ['GLManualCode'] == $_SESSION ['CompanyRecord'] ['debtorsact']) or ($_POST ['GLManualCode'] == $_SESSION ['CompanyRecord'] ['gllink_notesdebtors']) or in_array($_POST ['GLManualCode'], $listaventas))) {
                //echo "<br>entra1";
                if (! Havepermission($_SESSION ['UserID'], 308, $db)) {
                    prnMsg(_('Movimientos contables directos a cuentas de ventas, clientes, documentos x cobrar no pueden ser procesadas. La integracion contable con cuentas por cobrar esta activada y mantiene estas cuentas en automatico. Esta configuracion puede cambiarse en modulo de configuracion'), 'warn');
                    $AllowThisPosting = false;
                }
            }
            // VALIDACION DE CUENTAS DE PROVEEDORES
            if ($_SESSION ['CompanyRecord'] ['gllink_creditors'] == '1' and ($_POST ['GLManualCode'] == $_SESSION ['CompanyRecord'] ['creditorsact'] or in_array($_POST ['GLManualCode'], $listacostos))) {
                //echo "<br>entra2";
                if (! Havepermission($_SESSION ['UserID'], 542, $db)) {
                    prnMsg(_('Movimientos contables directos a cuentas de proveedores y/o a cuentas de costos no pueden ser procesadas. La integracion contable con cuentas por pagar esta activada y mantiene estas cuentas en automatico. Esta configuracion puede cambiarse en modulo de configuracion'), 'warn');
                    $AllowThisPosting = false;
                }
            }
            // VALIDACION DE CTA DE FONDO FIJO
            if (($_POST ['GLManualCode'] == $_SESSION ['CompanyRecord'] ['glfondofijo'])) {
                //echo "<br>entra3";
                if (! Havepermission($_SESSION ['UserID'], 543, $db)) {
                    prnMsg(_('Movimientos contables directos a cuentas de Fondo Fijo  no pueden ser procesadas. La integracion contable esta activada y mantiene estas cuentas en automatico. Esta configuracion puede cambiarse en modulo de configuracion'), 'warn');
                    $AllowThisPosting = false;
                }
            }
            // VALIDACION DE CUENTAS DE BANCOS
            
            if (in_array($_POST ['GLManualCode'], $ctasbancopermitidas)) {
                //echo "<br>entra4";
                if (! Havepermission($_SESSION ['UserID'], 544, $db)) {
                    prnMsg(_('Movimientos contables directos a cuentas de cheques no pueden ser procesadas. La integracion contable esta activada y mantiene estas cuentas en automatico. Esta configuracion puede cambiarse en modulo de configuracion'), 'warn');
                    $AllowThisPosting = false;
                }
            }
            
            // VALIDACION DE CUENTAS DE INVENTARIOS
            if (in_array($_POST ['GLManualCode'], $ctasinventarios)) {
                //echo "<br>entra5";
                if (! Havepermission($_SESSION ['UserID'], 1388, $db)) {
                    prnMsg(_('Movimientos contables directos a cuentas de inventarios no pueden ser procesadas. La integracion contable esta activada y mantiene estas cuentas en automatico. Esta configuracion puede cambiarse en modulo de configuracion'), 'warn');
                    $AllowThisPosting = false;
                }
            }
        }
        // echo "<br>-->" . count($ctasbancopermitidas);
        // echo "<br>-->" . in_array($_POST['GLManualCode'], $ctasbancopermitidas);
        /*
         * if ( (in_array($_POST['GLManualCode'], $_SESSION['JournalDetail']->BankAccounts) and !(in_array($_POST['GLManualCode'], $ctasbancopermitidas))) or (in_array($_POST['GLManualCode'], $listacostos)) or (in_array($_POST['GLManualCode'], $listaventas)) ) { //echo "entra"; if (((!Havepermission($_SESSION['UserID'],329, $db)) and ($FromYear==2010)) or ($FromYear > 2010)){ prnMsg(_('**Movimientos contables a cuentas de bancos/costos/ventas no pueden ser capturados manualmente') . '. ' . _('Movimientos a Bancos deben de ser capturados via un recibo de pago o un cheque'),'info'); $AllowThisPosting = false; } }
         */
        if ($AllowThisPosting) {
            $SQL = "SELECT legalid
                FROM tags
                WHERE tagref='" . $_POST ['tag'] . "'";
            
            $ResultTag = DB_query($SQL, $db);/*cga*/ //echo '<br>23:'.$SQL;
            
            if (DB_num_rows($ResultTag) == 0) {
                // prnMsg(_('La Unidad Responsable no existe en la base de datos') . ' - ' . _('asi que esta partida de la póliza no se pudo procesar'), 'warn');
                unset($_POST ['tag']);
            } else {
                $myrowTag = DB_fetch_array($ResultTag);
                $_SESSION ['JournalDetail']->JnlLegalId = $myrowTag ['legalid'];
            }
            
            $SQL = "SELECT accountname
                FROM chartmaster
                WHERE accountcode= '".$_POST ['GLCode']."'";
            
            $Result = DB_query($SQL, $db);/*cga*/ //echo '<br>24:'.$SQL;

            if ($registro= DB_fetch_array($Result)) {
                $nombrecargo= $registro["accountname"];
            }

            
            
            if (DB_num_rows($Result) == 0) {
                prnMsg(_('El codigo de cuenta no existe en la base de datos') . ' - ' . _('asi que esta partida de la póliza no se pudo procesar'), 'warn');
                unset($_POST ['GLManualCode']);
            } else {
                $myrow = DB_fetch_array($Result);

                $_SESSION ['JournalDetail']->Add_To_GLAnalysis(
                    $montocargo,
                    $_POST ['GLNarrative'],
                    $_POST ['GLCode'],
                    $nombrecargo,
                    $_POST ['tag'],
                    $_SESSION ['JournalDetail']->JnlLegalId,
                    $_POST ['rate'],
                    $_POST ['debtorno'],
                    $_POST ['brachno'],
                    $_POST ['stockid'],
                    $_POST ['qty'],
                    $_POST ['grns'],
                    $_POST ['loccode'],
                    $_POST ['standartcost'],
                    $_POST ['suppno'],
                    $_POST ['purchno'],
                    $_POST ['chequeno'],
                    $_POST ['cat_cuenta'],
                    $_POST ['jobref'],
                    $_POST ['bancodestino'],
                    $_POST ['rfcdestino'],
                    $_POST ['cuentadestino'],
                    0,
                    $_POST["selUE"]
                );
            }
        }
    }

    if (!empty($_POST ['GLCodeAbono']) and is_numeric($montoabono)) {
        $AllowThisPosting = true;

        if ($_SESSION ['ProhibitJournalsToControlAccounts'] == 1 && 1 == 2) {
            if ($_SESSION ['CompanyRecord'] ['gllink_debtors'] == '1' and (($_POST ['GLManualCode'] == $_SESSION ['CompanyRecord'] ['debtorsact']) or ($_POST ['GLManualCode'] == $_SESSION ['CompanyRecord'] ['gllink_notesdebtors']) or in_array($_POST ['GLManualCode'], $listaventas))) {
                if (! Havepermission($_SESSION ['UserID'], 308, $db)) {
                    prnMsg(_('Movimientos contables directos a cuentas de ventas, clientes, documentos x cobrar no pueden ser procesadas. La integracion contable con cuentas por cobrar esta activada y mantiene estas cuentas en automatico. Esta configuracion puede cambiarse en modulo de configuracion'), 'warn');
                    $AllowThisPosting = false;
                }
            }
            // VALIDACION DE CUENTAS DE PROVEEDORES
            if ($_SESSION ['CompanyRecord'] ['gllink_creditors'] == '1' and ($_POST ['GLManualCode'] == $_SESSION ['CompanyRecord'] ['creditorsact'] or in_array($_POST ['GLManualCode'], $listacostos))) {
                if (! Havepermission($_SESSION ['UserID'], 542, $db)) {
                    prnMsg(_('Movimientos contables directos a cuentas de proveedores y/o a cuentas de costos no pueden ser procesadas. La integracion contable con cuentas por pagar esta activada y mantiene estas cuentas en automatico. Esta configuracion puede cambiarse en modulo de configuracion'), 'warn');
                    $AllowThisPosting = false;
                }
            }
            // VALIDACION DE CTA DE FONDO FIJO
            if (($_POST ['GLManualCode'] == $_SESSION ['CompanyRecord'] ['glfondofijo'])) {
                if (! Havepermission($_SESSION ['UserID'], 543, $db)) {
                    prnMsg(_('Movimientos contables directos a cuentas de Fondo Fijo  no pueden ser procesadas. La integracion contable esta activada y mantiene estas cuentas en automatico. Esta configuracion puede cambiarse en modulo de configuracion'), 'warn');
                    $AllowThisPosting = false;
                }
            }
            // VALIDACION DE CUENTAS DE BANCOS
            
            if (in_array($_POST ['GLManualCode'], $ctasbancopermitidas)) {
                if (! Havepermission($_SESSION ['UserID'], 544, $db)) {
                    prnMsg(_('Movimientos contables directos a cuentas de cheques no pueden ser procesadas. La integracion contable esta activada y mantiene estas cuentas en automatico. Esta configuracion puede cambiarse en modulo de configuracion'), 'warn');
                    $AllowThisPosting = false;
                }
            }
            // VALIDACION DE CUENTAS DE INVENTARIOS
            if (in_array($_POST ['GLManualCode'], $ctasinventarios)) {
                if (! Havepermission($_SESSION ['UserID'], 545, $db)) {
                    prnMsg(_('Movimientos contables directos a cuentas de inventarios no pueden ser procesadas. La integracion contable esta activada y mantiene estas cuentas en automatico. Esta configuracion puede cambiarse en modulo de configuracion'), 'warn');
                    $AllowThisPosting = false;
                }
            }
        }
 
        if ($AllowThisPosting) {
            $SQL = "SELECT legalid
                    FROM tags
                    WHERE tagref='" . $_POST ['tag'] . "'";
            
            $ResultTag = DB_query($SQL, $db);/*cga*/ //echo '<br>25:'.$SQL;
            
            if (DB_num_rows($ResultTag) == 0) {
                // prnMsg(_('La Unidad Responsable no existe en la base de datos') . ' - ' . _('asi que esta partida de la póliza no se pudo procesar'), 'warn');
                unset($_POST ['tag']);
            } else {
                $myrowTag = DB_fetch_array($ResultTag);
                $_SESSION ['JournalDetail']->JnlLegalId = $myrowTag ['legalid'];
            }
            
            if (! isset($_POST ['GLAmount'])) {
                $_POST ['GLAmount'] = 0;
            }
            
            $SQL = "SELECT accountname
                FROM chartmaster
                WHERE accountcode= '".$_POST ['GLCodeAbono']."'";
            
            $Result = DB_query($SQL, $db);/*cga*/ //echo '<br>24:'.$SQL;

            if ($registro= DB_fetch_array($Result)) {
                $nombreabono= $registro["accountname"];
            }

            $_SESSION ['JournalDetail']->Add_To_GLAnalysis(
                $montoabono,
                $_POST ['GLNarrative'],
                $_POST ['GLCodeAbono'],
                $nombreabono,
                $_POST ['tag'],
                $_SESSION ['JournalDetail']->JnlLegalId,
                $_POST ['rate'],
                $_POST ['debtorno'],
                $_POST ['brachno'],
                $_POST ['stockid'],
                $_POST ['qty'],
                $_POST ['grns'],
                $_POST ['loccode'],
                $_POST ['standartcost'],
                $_POST ['suppno'],
                $_POST ['purchno'],
                $_POST ['chequeno'],
                $_POST ['cat_cuenta'],
                $_POST ['jobref'],
                $_POST ['bancodestino'],
                $_POST ['rfcdestino'],
                $_POST ['cuentadestino'],
                0,
                $_POST["selUE"]
            );
        }
    }
    
    $Cancel = 1;
    unset($_POST ['Credit']);
    unset($_POST ['Debit']);
    // unset($_POST['tag']);
    unset($_POST ['GLManualCode']);
    // unset($_POST['GLNarrative']);
} elseif (isset($_POST['btnCambioTipoPoliza'])) {
    // Eliminar partidas seleccionadas ya que cambio el tipo de poliza
    unset($_SESSION ['JournalDetail']->GLEntries);
    unset($_SESSION ['JournalDetail']->extraPresupuestalCod);
    unset($_SESSION ['JournalDetail']->extraPresupuestalNom);
    $_SESSION ['JournalDetail']->GLItemID = 0;
    $_SESSION ['JournalDetail']->GLItemCounter = 0;
    $_SESSION ['JournalDetail']->JournalTotal = 0;
}

if (isset($Cancel)) {
    unset($_POST ['Credit']);
    unset($_POST ['Debit']);
    unset($_POST ['GLAmount']);
    unset($_POST ['GLCode']);
    // unset($_POST['tag']);
    unset($_POST ['GLManualCode']);
}
?>
<!--Libeerias Bootstrap y jquery-->
<!-- <script src="lib/jquery/js/2.1.1/jquery.min.js" type="text/javascript"></script>
<link href="lib/bootstrap/css/3.3.6/bootstrap.min.css" rel="stylesheet">
<script src="lib/bootstrap/js/3.3.6/bootstrap.min.js" type="text/javascript"></script> -->

<!-- Archivos para input file, ocupa bootstrap -->
<link href="lib/inputfile_archivos/css/fileinput.css" media="all" rel="stylesheet" type="text/css" />
<script src="lib/inputfile_archivos/js/fileinput.js" type="text/javascript"></script>
<script src="lib/inputfile_archivos/js/fileinput_locale_es.js" type="text/javascript"></script>
<?php
echo '<form action=' . $_SERVER ['PHP_SELF'] . '?' . SID . ' method=post name="form" enctype="multipart/form-data" id="idFormPoliza">';

$MostrarPeriodoCierre = 0;
$CHECH = '';
if (isset($_SESSION ['JournalDetail']->origJnlDate) and Is_Date($_SESSION ['JournalDetail']->origJnlDate)) {
    $_SESSION ['JournalDetail']->JnlDate = $_SESSION ['JournalDetail']->origJnlDate;
    
    $arrfecha = explode("/", $_SESSION ['JournalDetail']->JnlDate);
    $FromDia = $arrfecha [0];
    $FromMes = $arrfecha [1];
    $FromYear = $arrfecha [2];
} else {
    if (! Is_Date($_SESSION ['JournalDetail']->JnlDate)) {
        // Default the date to the last day of the previous month
        $_SESSION ['JournalDetail']->JnlDate = Date($_SESSION ['DefaultDateFormat'], Mktime(0, 0, 0, Date('m'), Date('d'), Date('y')));
    }
    $MostrarPeriodoCierre = 1;
    if (isset($_POST['fecha_periodo'])) {
        $CHECH = 'checked';
    }
}

//**************************************************************************************//
//echo '<h3 align="center"><img src="' . $rootpath . '/css/' . $theme . '/images/maintenance.png" title="' . _('Search') . '" alt="">' . ' ' . $title . '</h3>';
?>
<div class="container-fluid" style="display: none;">
    <div class="panel panel-default">
        <div class="panel-heading">Filtros</div>
        <div class="panel-body">
            <div class="col-md-3">
                <div class="input-group">
                    <?php if ($MostrarPeriodoCierre == 1) : ?>
                        <span class="exampleInputEmail1" id="basic-addon1">Período Cierre: </span>
                        <?php echo '<input type="checkbox" name="fecha_periodo" value="1" '.$CHECH.'>'; ?>
                    <?php endif ?>
                </div>
            </div>
            <div class="col-md-3">
                <!-- <component-date-label label="Fecha Póliza:" id="txtFechaPoliza" name="txtFechaPoliza" placeholder="Fecha Póliza" title="Fecha Póliza" value="<?php echo $Fecha; ?>"></component-date-label> -->
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="exampleInputEmail1" id="basic-addon1">Período de Cierre: </span>
                    <select name="periodocierre" class="form-control">
                        <?php
                            $sql = "SELECT periodno, lastdate_in_period 
                                    FROM periods
                                    WHERE (periodno mod 1) > 0";
                            $rs = DB_query($sql, $db, '', '');/*cga*/ //echo '<br>31:'.$sql;
                        while ($myrowcierre = DB_fetch_array($rs, $db)) {
                            if ($myrowcierre['periodno'] == $_POST['periodocierre']) {
                                echo '<option  VALUE="' . $myrowcierre ['periodno'] . '  " selected>' . $myrowcierre ['lastdate_in_period'] . '</option>';
                            } else {
                                echo '<option  VALUE="' . $myrowcierre ['periodno'] . '" >' . $myrowcierre ['lastdate_in_period'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="exampleInputEmail1" id="basic-addon1">Período Cierre: </span>
                    <?php $msjProceso = ""; ?>
                    <select name=JournalType class="form-control">
                        <?php
                        if ($_SESSION ['JournalDetail']->origJnlType == 0) {
                            if ($_POST ['JournalType'] == 'Reversing') {
                                echo "<option selected value = 'Reversing'>" . _('Procesar Reversa')."</option>";
                                echo "<option value = 'Normal'>" . _('Normal')."</option>";
                            } else {
                                echo "<option value = 'Reversing'>" . _('Procesar Reversa')."</option>";
                                echo "<option selected value = 'Normal'>" . _('Normal')."</option>";
                            }
                            $msjProceso =  '* <br><b>Procesar Reversa</b><br>Genera movimientos contrarios.';
                        } else {
                            echo "<option selected value = 'Normal'>" . _('Normal')."</option>";
                            $msjProceso = '* <br><b>Solo pólizas de diario se pueden reversar...';
                        }
                        ?>
                    </select>
                    <span class="exampleInputEmail1" id="basic-addon1"><?php echo $msjProceso; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
//**************************************************************************************//
$TituloPoliza = "";
if (isset($_SESSION ['JournalDetail']->origJnlDate) and Is_Date($_SESSION ['JournalDetail']->origJnlDate)) {
    $TituloPoliza = 'FOLIO:' . $_SESSION ['JournalDetail']->origJnlIndex . '  TIPO:' . $_SESSION ['JournalDetail']->origJnlType . ' ' . $_SESSION ['JournalDetail']->origJnlTypeName . '';
} else {
    $TituloPoliza = 'Póliza de Diario';
}

if (! isset($_POST ['GLManualCode'])) {
    $_POST ['GLManualCode'] = '';
}

if (! isset($_POST ['GLNarrative'])) {
    $_POST ['GLNarrative'] = '';
}
if (! isset($_POST ['Credit'])) {
    $_POST ['Credit'] = '';
}
if (! isset($_POST ['Debit'])) {
    $_POST ['Debit'] = '';
}

$ocultaCargo = 'style="display: none;"';
$ocultaAbono = 'style="display: none;"';

if ($_SESSION['tipoRegistro'] == 'Cargo') {
    // Mostrar Cargo
    $ocultaCargo = '';
} else if ($_SESSION['tipoRegistro'] == 'Abono') {
    // Mostrar Abono
    $ocultaAbono = '';
}

$ocultaPolizaDiario = '';
$ocultaPolizaExtraPre = 'style="display: none;"';
$tituloPanel = "Póliza Diario";
if ($_POST['cmbTipoPoliza'] == '282') {
    $ocultaPolizaDiario = 'style="display: none;"';
    $ocultaPolizaExtraPre = '';
    $tituloPanel = "Póliza Extra Presupuestal";
}
?>
<div id="divMensajeOperacion" name="divMensajeOperacion"></div>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading h35" role="tab" id="headingOne">
            <h4 class="panel-title">
              <div class="fl text-left">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#idPanelPoliza" aria-expanded="true" aria-controls="collapseOne">
                  <b id="panelTitulo" name="panelTitulo"><?php echo $tituloPanel; ?></b>
                </a>
              </div>
            </h4>
        </div>
        <div id="idPanelPoliza" class="row panel-collapse collapse in ptb5" role="tabpanel" aria-labelledby="headingOne">

            <div class="row p5 m0">
                <div class="col-md-4" align="center" style="display: none;">
                </div>
                <div class="col-md-4" align="center" style="display: none;">
                    <label> Busca Cta x Nombre:</label>
                    <input type="hidden" name="PeriodNo" value="<?php echo $PeriodNo; ?>" />
                </div>
                <div class="col-md-4" align="center" style="display: none;">
                    <div class="input-group">
                        <input type=Text Name="GLManualSearch" class="form-control" Maxlength=40 size=40 VALUE='<?php echo $_POST ['GLManualSearch']; ?>' placeholer="Nombre Cuenta" title="Nombre Cuenta" />   
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-primary" name="SearchAccount" value="Buscar">Buscar</button>
                        </span>
                    </div>
                </div>
            </div>

            <div class="row p5 m0">
                <div class="col-md-4">
                    <div class="form-inline row">
                        <div class="col-md-3">
                            <span><label>UR: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select name="tag" id="tag" class="form-control tag" onchange="fnTraeUnidadesEjecutoras(this.value, 'selUE');">
                                <option value="-1">Seleccionar...</option>
                                <?php
                                    // /Pinta la Unidad Responsable por usuario
                                    $unidadresponsable= "";

                                    $SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription 
                                    FROM sec_unegsxuser u, tags t 
                                    WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' ".$condicion." 
                                    ORDER BY t.tagref ";

                                    $result = DB_query($SQL, $db);/*cga*/ //echo '<br>32:'.$SQL;
                                    // echo '<option value=0>0 - None';
                                while ($myrow = DB_fetch_array($result)) {
                                    $unidadresponsable= $myrow ['tagref'];
                                    if (isset($_POST ['tag']) and $_POST ['tag'] == $myrow ['tagref']) {
                                        echo '<option selected value=' . $myrow ['tagref'] . '>' . $myrow ['tagdescription'] . "</option>";
                                    } else {
                                        echo '<option value=' . $myrow ['tagref'] . '>' . $myrow ['tagdescription'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-inline row">
                        <div class="col-md-3">
                            <span><label>UE: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select name="selUE" id="selUE" class="form-control tag">
                                <option value="-1">Seleccionar...</option>
                                <?php
                                    // Pinta la Unidad Responsable por usuario
                                    $SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription,  tce.ue as ue, CONCAT(tce.ue, ' - ', tce.desc_ue) as uedescription 
                                            FROM sec_unegsxuser u
                                            INNER JOIN tags t on (u.tagref = t.tagref)
                                            INNER JOIN tb_cat_unidades_ejecutoras tce on  (tce.ur = t.tagref)
                                            WHERE tce.active = 1 and u.userid = '" . $_SESSION['UserID'] . "' 
                                            AND (t.tagref='".$unidadresponsable."' OR ''='".$unidadresponsable."')
                                            ORDER BY t.tagref ";

                                    $result = DB_query($SQL, $db);/*cga*/ //echo '<br>32:'.$SQL;
                                    // echo '<option value=0>0 - None';
                                while ($myrow = DB_fetch_array($result)) {
                                    if (isset($_POST ['selUE']) and $_POST ['selUE'] == $myrow ['ue']) {
                                        echo '<option selected value=' . $myrow ['ue'] . '>' . $myrow ['uedescription'] . "</option>";
                                    } else {
                                        echo '<option value=' . $myrow ['ue'] . '>' . $myrow ['uedescription'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-inline row">
                        <div class="col-md-3">
                            <span><label>Programa Presup: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select name="cmbProgramaPresupuestario" id="cmbProgramaPresupuestario" class="form-control select selectProgramaPresupuestarioLocal" onchange="fnCambioDatosIdentificador()">
                                <option value="-1">Seleccionar...</option>
                                <?php
                                $SQL = "SELECT cppt, CONCAT(cppt, ' - ', descripcion) as descripcion FROM tb_cat_programa_presupuestario ORDER BY descripcion ASC";
                                $result = DB_query($SQL, $db);
                                while ($myrow = DB_fetch_array($result)) {
                                    $selected = '';
                                    if ($_POST['cmbProgramaPresupuestario'] == $myrow['cppt']) {
                                        $selected = 'selected';
                                    }
                                    echo '<option value="'.$myrow['cppt'].'" '.$selected.'>'.$myrow['descripcion'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row p5 m0">
                <div class="col-md-6">
                    <div class="form-inline row" id="divCuentaCargoDiario" name="divCuentaCargoDiario" <?php echo $ocultaPolizaDiario; ?> >
                        <div class="col-md-3">
                            <span><label>Cuenta Cargo: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select name="GLCode" id="GLCode" class="form-control cuentasDatos">
                                <option selected value='0'>Sin selección</option>
                                <?php
                                $SQL = "SELECT accountcode, accountname, group_ as padre FROM chartmaster WHERE LENGTH(accountcode)>=7 ORDER BY group_, accountcode";
                                $result = DB_query($SQL, $db);
                                $cambioGrupo = '';
                                while ($myrow = DB_fetch_array($result)) {
                                    if ($cambioGrupo != $myrow ['padre']) {
                                        //echo '<option  value=0>****** ' . $myrow ['padre'] . '</option>';
                                    }
                                    if (!empty($cuentacargo) and $cuentacargo == $myrow ["accountcode"]) {
                                        echo '<option selected value=' . $myrow ['accountcode'] . '>' . $myrow ['accountcode'] . ' - ' . $myrow ['accountname'] . '</option>';
                                    } else {
                                        echo '<option value=' . $myrow ['accountcode'] . '>' . $myrow ['accountcode'] . ' - ' . $myrow ['accountname'] . '</option>';
                                    }
                                    $cambioGrupo = $myrow ['padre'];
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div id="divCuentaCargoExtraPre" name="divCuentaCargoExtraPre" <?php echo $ocultaPolizaExtraPre; ?>>
                        <component-text-label label="Cuenta Cargo:" id="GLCodeExtraPre" name="GLCodeExtraPre" placeholder="Cuenta Cargo" title="Cuenta Cargo" value="<?php echo $_POST ['GLCodeExtraPre']; ?>" readonly="true"></component-text-label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="col-md-3" align="right">
                        <label>Cantidad Cargo:</label>
                    </div>
                    <div class="col-md-4">
                        <component-decimales name="Debit" id="Debit" placeholder="Cargo" title="Cantidad Cargo" value="<?php echo $_POST ['Debit']; ?>" ></component-decimales>
                    </div>

                    <div class="col-md-5">
                        <div class="form-inline row">
                            <div class="col-md-3">
                                <span><label>Tipo Póliza: </label></span>
                            </div>
                            <div class="col-md-9">
                                <select name="cmbTipoPoliza" id="cmbTipoPoliza" class="form-control cmbTipoPoliza" onchange="fnCambioTipoPoliza(this)">
                                    <option value="287" <?php echo ($_POST['cmbTipoPoliza'] == '287' ? 'selected' : ''); ?>>Póliza Diario</option>
                                    <option value="282" <?php echo ($_POST['cmbTipoPoliza'] == '282' ? 'selected' : ''); ?>>Póliza Extra Presupuestal</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row p5 m0">
                <div class="col-md-6">
                    <div class="form-inline row" id="divCuentaAbonoDiario" name="divCuentaAbonoDiario" <?php echo $ocultaPolizaDiario; ?>>
                        <div class="col-md-3">
                            <span><label>Cuenta Abono: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select name="GLCodeAbono" id="GLCodeAbono" class="form-control cuentasDatos">    
                                <option selected value='0'>Sin selección</option>
                                <?php
                                $SQL = "SELECT accountcode, accountname, group_ as padre FROM chartmaster WHERE LENGTH(accountcode)>=7 ORDER BY group_, accountcode";
                                $result = DB_query($SQL, $db);
                                $cambioGrupo = '';
                                while ($myrow = DB_fetch_array($result)) {
                                    if ($cambioGrupo != $myrow ['padre']) {
                                        //echo '<option  value=0>****** ' . $myrow ['padre'] . '</option>';
                                    }
                                    if (!empty($cuentaabono) and $cuentaabono == $myrow ["accountcode"]) {
                                        echo '<option selected value=' . $myrow ['accountcode'] . '>' . $myrow ['accountcode'] . ' - ' . $myrow ['accountname'] . '</option>';
                                    } else {
                                        echo '<option value=' . $myrow ['accountcode'] . '>' . $myrow ['accountcode'] . ' - ' . $myrow ['accountname'] . '</option>';
                                    }
                                    $cambioGrupo = $myrow ['padre'];
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div id="divCuentaAbonoExtraPre" name="divCuentaAbonoExtraPre" <?php echo $ocultaPolizaExtraPre; ?>>
                        <component-text-label label="Cuenta Abono:" id="GLCodeAbonoExtraPre" name="GLCodeAbonoExtraPre" placeholder="Cuenta Abono" title="Cuenta Abono" value="<?php echo $_POST ['GLCodeAbonoExtraPre']; ?>" readonly="true"></component-text-label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="col-md-3" align="right">
                        <label>Cantidad Abono:</label>
                    </div>
                    <div class="col-md-4">
                        <component-decimales name="Credit" id="Credit" placeholder="Abono" title="Cantidad Abono" value="<?php echo $_POST ['Credit']; ?>" ></component-decimales>
                    </div>
                    <div class="col-md-5">
                        <component-date-label label="Fecha Póliza:" id="txtFechaPoliza" name="txtFechaPoliza" placeholder="Fecha Póliza" title="Fecha Póliza" value="<?php echo $Fecha; ?>"></component-date-label>
                    </div>
                </div>
            </div>
            
            <div class="row p5 m0">
                <div class="col-md-12" id="divDescripcionDiario" name="divDescripcionDiario" <?php echo $ocultaPolizaDiario; ?>>
                    <div class="col-md-1" align="left">
                        <label>Descripción:</label>
                    </div>
                    <div class="col-md-11">
                        <component-text label="Descripción:" id="GLNarrative" name="GLNarrative" placeholder="Descripción" title="Descripción" value="<?php echo $_POST['GLNarrative']; ?>" maxlength="200"></component-text>
                    </div>
                </div>
                <div class="col-md-12" id="divDescripcionExtraPre" name="divDescripcionExtraPre" <?php echo $ocultaPolizaExtraPre; ?>>
                    <div class="form-inline row">
                        <div class="col-md-3">
                            <span><label>Descripción: </label></span>
                        </div>
                        <div class="col-md-9">
                            <select name="selectGLNarrative" id="selectGLNarrative" class="form-control selectGLNarrative" 
                            onchange="fnObtenerDatosExtraPres(this)">
                                <option selected value='0'>Sin selección</option>
                                <?php
                                $SQL = "SELECT categoryid, categorydescription FROM tb_matriz_extraptal ORDER BY categorydescription";
                                $result = DB_query($SQL, $db);
                                while ($myrow = DB_fetch_array($result)) {
                                    if ($_POST['selectGLNarrative'] == $myrow ["categoryid"]) {
                                        echo '<option selected value=' . $myrow ['categoryid'] . '>' . $myrow ['categorydescription'] . '</option>';
                                    } else {
                                        echo '<option value=' . $myrow ['categoryid'] . '>' . $myrow ['categorydescription'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div align="center">
                <br>
                <!-- <input type="submit" class="btn btn-default botonVerde" name="Process" value='Aceptar' /> -->
                <?php if (($_POST['cmbTipoPoliza'] != '282') || ($_POST['cmbTipoPoliza'] == '282') && count($_SESSION ['JournalDetail']->GLEntries) < 2) { ?>
                    <component-button type="button" id="btnProcess" name="btnProcess" value="Agregar" onclick="fnValidarDatosFormulario()"></component-button>
                    <component-button type="submit" id="Process" name="Process" value="Agregar2" style="display: none;"></component-button>
                <?php } ?>
                <component-button type="submit" id="btnCambioTipoPoliza" name="btnCambioTipoPoliza" value="Actualizar" style="display: none;"></component-button>
            </div> 
        </div>

    </div>
</div>
<div class="container-fluid">
    <div class="panel panel-default">
        <!-- Default panel contents -->
        <div class="panel-heading h35" role="tab" id="headingTwo">
            <h4 class="panel-title">
              <div class="fl text-left">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#idPanelInfoPoliza" aria-expanded="true" aria-controls="collapseTwo">
                  <b>Información Póliza</b>
                </a>
              </div>
            </h4>
        </div>
        <div id="idPanelInfoPoliza" class="row panel-collapse collapse in p0 m0" role="tabpanel" aria-labelledby="headingTwo">
            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <tr>
                        <td style='text-align:center;'><b>UR</b></td>
                        <td style='text-align:center;'><b>Cuenta</b></td>
                        <td style='text-align:center;'><b>Cargo</b></td>
                        <td style='text-align:center;'><b>Abono</b></td>
                        <td style='text-align:center;'><b>Descripción</b></td>
                        <td style='text-align:center;' colspan="2"><b>Acciones</b></td>
                    </tr>
                    <?php
                        $debittotal = 0;
                        $credittotal = 0;
                        $j = 0;
                        $unidadnegocioanterior = 0;
                        $flagdiferenteunidad = false;
                        $razonsocialanterior = 0;
                        $flagdiferenterazonsocial = false;

                    foreach ($_SESSION ['JournalDetail']->GLEntries as $JournalItem) {
                        if ($unidadnegocioanterior == 0) {
                            $unidadnegocioanterior = $JournalItem->tag;
                        }
                            
                        if ($razonsocialanterior == 0) {
                            $razonsocialanterior = $JournalItem->legalid;
                        }
                            

                        if ($j == 1) {
                            //echo '<tr class="OddTableRows">';
                            $j = 0;
                        } else {
                            //echo '<tr class="EvenTableRows">';
                            $j ++;
                        }
                        echo "<tr>";
                            
                        $sql = 'SELECT tagdescription ' . 'FROM tags ' . 'WHERE tagref="' . $JournalItem->tag . '"';
                            
                        $result = DB_query($sql, $db);
                        $myrow = DB_fetch_row($result);
                        if ($JournalItem->tag == '0') {
                            $tagdescription = 'None';
                        } else {
                            $tagdescription = $myrow [0];
                        }
                            
                        if ($unidadnegocioanterior != $JournalItem->tag) {
                            $flagdiferenteunidad = true;
                            $bgcolor = "#FF0000";
                        } else {
                            $bgcolor = "FFFFFF";
                        }
                            
                            
                        if ($razonsocialanterior != $JournalItem->legalid) {
                            $flagdiferenterazonsocial = true;
                            $bgcolor = "#FF0000";
                        } else {
                            $bgcolor = "FFFFFF";
                        }
                            
                        echo "<td style='text-align:center;' bgcolor='" . $bgcolor . "'>" . $JournalItem->tag . ' - ' . $tagdescription . "</td>";
                            
                        echo "<td style='text-align:center;'>" . $JournalItem->GLCode . ' - ' . $JournalItem->GLActName . "</td>";
                        if ($JournalItem->Amount > 0) {
                            echo "<td style='text-align:center;' class='number'>" . number_format($JournalItem->Amount, 2) . '</td><td></td>';
                            $debittotal = $debittotal + $JournalItem->Amount;
                            // $cuadrexunidad[$JournalItem->tag] = $cuadrexunidad[$JournalItem->tag] + $JournalItem->Amount;
                        } elseif ($JournalItem->Amount < 0) {
                            $credit = (- 1 * $JournalItem->Amount);
                            echo "<td></td>
                                    <td style='text-align:center;' class='number'>" . number_format($credit, 2) . '</td>';
                            $credittotal = $credittotal + $credit;
                            // $cuadrexunidad[$JournalItem->tag] = $cuadrexunidad[$JournalItem->tag] + $credit;
                        }
                            
                        echo "<td style='text-align:center;'>" . $JournalItem->Narrative . "</td>";
                        //echo "<td style='text-align:center;'><a href='" . $_SERVER ['PHP_SELF'] . '?' . SID . '&Modificar=' . $JournalItem->ID . '&FromYear=' . $FromYear . '&FromMes=' . $FromMes . '&FromDia=' . $FromDia . "'>" . _('Modificar') . "</a>&nbsp;/&nbsp;<a href='" . $_SERVER ['PHP_SELF'] . '?' . SID . '&Delete=' . $JournalItem->ID . '&FromYear=' . $FromYear . '&FromMes=' . $FromMes . '&FromDia=' . $FromDia . "'>" . _('Borrar') . "</a>&nbsp;/&nbsp;<a href='" . $_SERVER ['PHP_SELF'] . '?' . SID . '&Copiar=' . $JournalItem->ID . '&FromYear=' . $FromYear . '&FromMes=' . $FromMes . '&FromDia=' . $FromDia . "'>" . _('Copiar') . "</a></td>";
                        
                        // Modificar
                        $enc = new Encryption;
                        $url = "&Modificar=>".$JournalItem->ID."&FromYear=>".$FromYear."&FromMes=>".$FromMes."&FromDia=>".$FromDia."&cmbTipoPoliza=>".$_POST['cmbTipoPoliza'];
                        $url = $enc->encode($url);
                        $liga= "URL=" . $url;
                        echo "<td style='text-align:center;'><a href='" . $_SERVER ['PHP_SELF'] . '?' . $liga . "'>" . _('Modificar') . "</a></td>";

                        // Eliminar
                        $enc = new Encryption;
                        $url = "&Delete=>".$JournalItem->ID."&FromYear=>".$FromYear."&FromMes=>".$FromMes."&FromDia=>".$FromDia."&cmbTipoPoliza=>".$_POST['cmbTipoPoliza'];
                        $url = $enc->encode($url);
                        $liga= "URL=" . $url;
                        echo "<td style='text-align:center;'><a href='" . $_SERVER ['PHP_SELF'] . '?' . $liga . "'>" . _('Eliminar') . "</a></td>";
                        echo "</tr>";
                    }

                    echo '<tr><td></td>
                    <td align=right><b> Total </b></td>
                    <td style="text-align:center;" class="number"><b>' . number_format($debittotal, 2) . '</b></td>
                    <td style="text-align:center;" class="number"><b>' . number_format($credittotal, 2) . '</b></td>';

                    if ($debittotal != $credittotal) {
                        echo '<td align=center style="background-color: #fddbdb"><b>Req. para Balancear - ' . number_format(abs($debittotal - $credittotal), 2);
                    }
                    if ($debittotal > $credittotal) {
                        echo ' Abono';
                    } else if ($debittotal < $credittotal) {
                        echo ' Cargo';
                    }
                        echo '</b></td><td></td><td></td><td></td></tr>';
                    ?>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
    //**************************************************************************************//
// echo "<br>propeois: ".ABS($_SESSION ['JournalDetail']->JournalTotal);

$botonRegresar = '<a id="linkPanelRegresar" name="linkPanelRegresar" href="ReporteGLJournal.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>';
$botonCancelar = '';
if (isset($_SESSION ['JournalDetail']->origJnlDate) and Is_Date($_SESSION ['JournalDetail']->origJnlDate)) {
    // Actualizar
    $url = "&NewJournal=>Yes&typeno=>".$_SESSION ['JournalDetail']->origJnlIndex."&type=>".$_SESSION ['JournalDetail']->origJnlType."&tag=>".$_SESSION ['JournalDetail']->JnlTag;
    $enc = new Encryption;
    $url = $enc->encode($url);
    $liga= "URL=" . $url;
    $botonCancelar = '<a id="linkCancelar" name="linkCancelar" href="GLJournal.php?'.$liga.'" class="btn btn-default botonVerde glyphicon glyphicon-trash"> Cancelar</a>';
} else {
    // Guardar nuevo
    $url = "&NewJournal=>Yes";
    $enc = new Encryption;
    $url = $enc->encode($url);
    $liga= "URL=" . $url;
    $botonCancelar = '<a id="linkCancelar" name="linkCancelar" href="GLJournal.php?'.$liga.'" class="btn btn-default botonVerde glyphicon glyphicon-trash"> Cancelar</a>';
}
// echo "<br>mensaje: ".$_SESSION ['JournalDetail']->JournalTotal;
// echo "<br>mensaje2: ".$_SESSION ['JournalDetail']->GLItemCounter;
if (ABS($_SESSION ['JournalDetail']->JournalTotal) < 0.01 and $_SESSION ['JournalDetail']->GLItemCounter > 0) {
    if (($flagdiferenteunidad == false) or (Havepermission($_SESSION ['UserID'], 44, $db))) {
        if ($flagdiferenterazonsocial == false) {
            $SQL = "SELECT id,nombre FROM gltrans_files WHERE type = '".$_SESSION ['JournalDetail']->origJnlType."' and typeno = '".$_SESSION ['JournalDetail']->origJnlIndex."'";
            $result = DB_query($SQL, $db);
            if (DB_num_rows($result) > 0) {
            ?>
            <br>
            <div class="container-fluid">
                <div class="panel panel-default">
                    <div class="panel-heading" align="center"> Archivos Relacionados a la Póliza</div>
                    <table id="tblFile" class="table table-hover" border="0" >
                        <?php
                        $n = 1;
                        $col = 1;
                        echo "<tbody>";
                        while ($myrow = DB_fetch_array($result)) {
                            echo "<tr id='trfile_".$myrow['id']."'>";
                            echo "<td style='text-align:center'>".$n.".- ".$myrow['nombre']."</td>";
                            echo "<td style='text-align:center'>";
                            //echo "<button class='btn btn-danger btn-xs' data-id='".$myrow['id']."' data-file='".$myrow['nombre']."' data-toggle='modal' data-target='#mdlDeleteFile' type='button'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button>";
                            echo "<button class='btn btn-danger btn-xs' name='btnEliminarAr_".$myrow['id']."' id='btnEliminarAr_".$myrow['id']."' type='button' onclick='fnEliminarArchivoSel(".$myrow['id'].", \"".$myrow['nombre']."\")'><span class='glyphicon glyphicon-trash'></span></button>";
                            echo "</td>";
                            if ($col == 5) {
                                $col = 1;
                            }

                            $n ++;
                            $col ++;
                            echo "</tr>";
                        }
                        echo "</tbody>";
                        ?>
                    </table>
                </div>
            </div>
            <?php } ?>
            <br>
            <div align="center">
                <!-- class="file-loading" -->
                <input type="file" id="file_mul" name="file_mul[]" title="Seleccionar Archivos" multiple="true" />
            </div>
            <script type="text/javascript">
                <?php
                    $ruta = $_SESSION ['JournalDetail']->PolizaRutaArchivos;
                    $images = glob($ruta."*.*");
                ?>
                $("#file_mulFormat").fileinput({
                    language: 'es', //Lenguaje
                    browseLabel: 'Seleccionar &hellip;', //titulo boton seleccionar
                    uploadUrl: "subir_archivos.php", //null quita iconos elimiar y subir
                    uploadAsync: false,
                    maxFileSize: 0,  //tamaño maximo de archivo
                    minFileCount: 1, //numero minimo de archivos
                    //maxFileCount: 5, //numero maximo de archivos
                    //allowedFileExtensions : ['jpg', 'png','gif','pdf','docx'], //extenciones de archivos
                    showPreview: false, //panel de visualizacion
                    browseClass: "btn btn-default btn-md",
                    showRemove: false, //boton eliminar en input
                    showUpload: false, //boton subir en input
                    overwriteInitial: false, //en true oculta los iniciales al cargar mas
                    layoutTemplates: {actionUpload: ''}, //quitar boton subir en la imagen
                    showAjaxErrorDetails: null, //mostrar o quitar errores
                    initialPreviewAsData: true, // identify if you are sending preview data only and not the raw markup
                    initialPreviewFileType: 'pdf', // image is the default and can be overridden in config below
                    purifyHtml: false, // this by default purifies HTML data for preview
                    uploadExtraData: {
                        img_key: "1000",
                        img_keywords: "happy, places",
                    }
                });
            </script>
            <?php
            echo "<br><div align='center'>";
            echo $botonRegresar;
            if (isset($_SESSION ['JournalDetail']->origJnlDate) and Is_Date($_SESSION ['JournalDetail']->origJnlDate)) {
                // Actualizar
                // echo "<input type=submit class='btn btn-default botonVerde' name='CommitBatch' value='" . _('Actualizar Modificaciones...') . "'>";
                echo '<component-button type="submit" id="btnActualizar" name="btnActualizar" class="glyphicon glyphicon-floppy-disk" value="Guardar"></component-button>';
            } else {
                // Guardar nuevo
                // echo "<input type=submit class='btn btn-default botonVerde' name='CommitBatch' value='" . _('Aceptar y Procesar Nueva Póliza...') . "'>";
                echo '<component-button type="submit" id="btnAgregar" name="btnAgregar" class="glyphicon glyphicon-floppy-disk" value="Guardar"></component-button>';
                
                // $url = "&NewJournal=>Yes";
                // $enc = new Encryption;
                // $url = $enc->encode($url);
                // $liga= "URL=" . $url;
                // echo '<a id="linkCancelar" name="linkCancelar" href="GLJournal.php?'.$liga.'" class="btn btn-default botonVerde glyphicon glyphicon-trash"> Cancelar</a>';
            }
            echo $botonCancelar;
            echo "</div>";
        } else {
            prnMsg(_('Los movimientos de la póliza deben ser de una sola Dependencia'), 'warn');
            echo "<div align='center'>";
            echo $botonRegresar;
            echo $botonCancelar;
            echo "</div>";
        }
    } else {
        prnMsg(_('Los movimientos de la póliza deben ser de una sola Unidad Responsable'), 'warn');
        echo "<div align='center'>";
        echo $botonRegresar;
        echo $botonCancelar;
        echo "</div>";
    }
    
    // Accept and Process Journal
} elseif (count($_SESSION ['JournalDetail']->GLEntries) > 0) {
    prnMsg(_('La póliza debe balancear Cargo igual a Abono antes de ser procesada'), 'warn');
    echo "<div align='center'>";
    echo $botonRegresar;
    echo $botonCancelar;
    echo "</div>";
} else {
    echo "<div align='center'>";
    echo $botonRegresar;
    echo $botonCancelar;
    echo "</div>";
}

if (! isset($_GET ['NewJournal']) or $_GET ['NewJournal'] == '') {
    //echo "<script>defaultControl(document.form.GLManualCode);</script>";
} else {
    // echo "<script>defaultControl(document.form.JournalProcessDate);</script>";
    //echo "<script>defaultControl(Fecha);</script>";
}

echo '</form>';
?>

<!-- Modal -->
<div class="modal fade" id="mdlDeleteFile" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><strong> Eliminar archivo relacionado</strong></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <strong> Confirmar Acci&oacute;n</strong>
        <br>
        <br>
        <p id="namefile"></p>
        <input type="hidden" id="txtidfile" name="txtidfile">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" id="btnDeleteFile" class="btn btn-primary" data-dismiss="modal">Confirmar</button>
      </div>
    </div>
  </div>
</div>

<?php
include 'includes/footer_Index.inc';
// echo "<br>numero datos: ".count($_SESSION ['JournalDetail']->GLEntries)."<br>";
// var_dump($_SESSION ['JournalDetail']);
?>

<script language="JavaScript">

/**
 * Si cambia de tipo de poliza para mostrar descripcion por panel
 * @param  object Recibe el select para ver la opción
 * @return {[type]}               [description]
 */
function fnCambioTipoPoliza(cmbTipoPoliza){
    // Cambio el tipo de poliza
    var btnConsultar = document.getElementById('btnCambioTipoPoliza');
    btnConsultar.click();
    return true;
    
    if (cmbTipoPoliza.value == '282') {
        // Poliza extra presupuestal
        document.getElementById('divCuentaCargoDiario').style.display = "none";
        document.getElementById('divCuentaAbonoDiario').style.display = "none";
        document.getElementById('divDescripcionDiario').style.display = "none";

        document.getElementById('divCuentaCargoExtraPre').style.display = "block";
        document.getElementById('divCuentaAbonoExtraPre').style.display = "block";
        document.getElementById('divDescripcionExtraPre').style.display = "block";

        $('#panelTitulo').html("Póliza Extra Presupuestal");
    }else{
        // Poliza normal
        document.getElementById('divCuentaCargoDiario').style.display = "block";
        document.getElementById('divCuentaAbonoDiario').style.display = "block";
        document.getElementById('divDescripcionDiario').style.display = "block";

        document.getElementById('divCuentaCargoExtraPre').style.display = "none";
        document.getElementById('divCuentaAbonoExtraPre').style.display = "none";
        document.getElementById('divDescripcionExtraPre').style.display = "none";

        $('#panelTitulo').html("Póliza Diario");
    }
}

function fnObtenerDatosExtraPres(selectExtraPre){
    // Cambio descripción extra presupuestal
    // console.log("selectExtraPre: "+selectGLNarrative.value);

    dataObj = {
        clave: selectGLNarrative.value,
        option: 'obtenerCuentas'
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/GLJournal_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        if(data.result){
            $("#GLCodeExtraPre").val(""+data.contenido.cuentaCargo);
            $("#GLCodeAbonoExtraPre").val(""+data.contenido.cuentaAbono);
        }else{
            $("#GLCodeExtraPre").val("");
            $("#GLCodeAbonoExtraPre").val("");

            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ocurrio un problema al obtener la información</p>');
        }
    })
    .fail(function(result) {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ocurrio un problema al obtener la información</p>');
    });
}

$( document ).ready(function() {
    $('#mdlDeleteFile').on('show.bs.modal',function(event){
        button = $(event.relatedTarget); // Button that triggered the modal
        idfile = button.data('id');
        file = button.data('file');

        $("#txtidfile").val('');
        $("#txtidfile").val(idfile);

        $('#namefile').text('');
        $('#namefile').text('Nombre del archivo: '+file);
    });

    $('#btnDeleteFile').click(function (){
        dataObj = {
                idFile: $('#txtidfile').val(),
                option: 'DeleteFile'
            };
            $.ajax({
                method: "POST",
                dataType:"json",
                url: "GLJournalV2_Model.php",
                data:dataObj
            })
            .done(function( data ) {
                console.log(data);
                if(data.result){
                    $('#trfile_'+$('#txtidfile').val()).remove();
                    alert('Se elimino correctamente');
                    
                }
            })
            .fail(function(result) {
                console.log('fue error:')
            });

    });
});


 $(document).on('keypress', '.decimalesCl', function(event) {

    var text = $(this).val();
    var cuenta = (text.match(/./g) || []).length;
    //cuenta.match( new RegExp('.','g') ).length;
    //var cuenta = /\.{1}\.+/g.test(text);
    // console.log("->"+cuenta+" ");
    if(text.includes(".")){

      var texto2=[];
      texto2=(text.split("."));
       //despues del punto solo 2 con el length. y con el or includes. la primera parte ya tiene  punto decimal
      if (texto2[1].length >1 || texto2[0].includes(".")) {
              event.preventDefault();
      }
    }    

 });

function fnEliminarArchivoSel(idFile, nameFile) {
    //console.log("idFile: "+idFile+" - nameFile: "+nameFile);
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
    var mensaje = '<h4>Se va a eliminar el Archivo '+nameFile+'</h4>';
    muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnEliminarArchivoSeleccion(\''+idFile+'\')');
}

function fnEliminarArchivoSeleccion(idFile) {
    //console.log("eliminar "+idFile);
    dataObj = {
        idFile: idFile,
        option: 'DeleteFile'
    };
    $.ajax({
        method: "POST",
        dataType:"json",
        url: "modelo/GLJournal_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        console.log(data);
        if(data.result){
            $('#trfile_'+idFile).remove();
        }
    })
    .fail(function(result) {
        console.log('fue error:')
    });
}

function Abrir_ventana(pagina) {
    var opciones="toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=yes, width=508, height=365, top=85, left=140";
    window.open(pagina,"",opciones);
}

function fnValidarDatosFormulario() {
    //evt.preventDefault();  
    var cuentaCargo = $('#GLCode').val();
    var cuentaAbono = $('#GLCodeAbono').val();
    var debit = $('#Debit').val();
    var credit = $('#Credit').val();
    var mensaje = "";
    var cmbTipoPoliza = $('#cmbTipoPoliza').val();
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

    if ($("#tag").val() == '-1') {
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Es necesario seleccionar una Unidad Responsable</p></h5>';
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if ($("#selUE").val() == '-1') {
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Es necesario seleccionar una Unidad Ejecutora</p></h5>';
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if((typeof cuentaCargo === 'undefined' && typeof cuentaAbono === 'undefined') && cmbTipoPoliza != '282') {
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Es necesario seleccionar una cuenta para continuar con la póliza</p></h5>';
        //alert("Error vacio");
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if ((cuentaCargo == 0 && cuentaAbono == 0) && cmbTipoPoliza != '282') {
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Es necesario seleccionar una cuenta para continuar con la póliza</p></h5>';
        //alert("Error vacio");
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if (cuentaCargo == cuentaAbono && cmbTipoPoliza != '282') {
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Las cuentas no deben ser iguales</p></h5>';
        //alert("Error vacio");
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if (((cuentaCargo != '' || cuentaCargo != 0) && debit == '') && (cuentaAbono == 0 && credit == '')) {
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Es necesario agregar la Cantidad para el Cargo para continuar con la póliza</p></h5>';
        //alert("Error vacio");
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if (((cuentaAbono != '' || cuentaAbono != 0) && credit == '') && (cuentaCargo == 0 && debit == '')){
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Es necesario agregar la Cantidad para el Abono para continuar con la póliza</p></h5>';
        //alert("Error vacio");
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if (((cuentaCargo != '' || cuentaCargo != 0) && debit == '') && (cuentaAbono == 0 && credit > 0)) {
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Es necesario agregar la Cantidad para el Cargo para continuar con la póliza</p></h5>';
        //alert("Error vacio");
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if (((cuentaAbono != '' || cuentaAbono != 0) && credit == '') && (cuentaCargo == 0 && debit > 0)){
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Es necesario agregar la Cantidad para el Abono para continuar con la póliza</p></h5>';
        //alert("Error vacio");
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if (cmbTipoPoliza == '282') {
        // Validacion extra presupuestal
        var cuentaCargoExtraPre = $("#GLCodeExtraPre").val();
        var cuentaAbonoExtraPre = $("#GLCodeAbonoExtraPre").val();
        if (!fnValidarSiEsNumero(debit)) {
            // Validar si es numero
            mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Solo números en la Cantidad del Cargo</p></h5>';
            muestraModalGeneral(3, titulo, mensaje);
            return false;
        }else if (!fnValidarSiEsNumero(credit)) {
            // Validar si es numero
            mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Solo números en la Cantidad del Abono</p></h5>';
            muestraModalGeneral(3, titulo, mensaje);
            return false;
        }else if (cuentaCargoExtraPre.trim() == '') {
            // Cuenta Cargo esta vacia
            mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Sin cuenta de Cargo</p></h5>';
            muestraModalGeneral(3, titulo, mensaje);
            return false;
        }else if (cuentaAbonoExtraPre.trim() == '') {
            // Cuenta Cargo esta vacia
            mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Sin cuenta de Abono</p></h5>';
            muestraModalGeneral(3, titulo, mensaje);
            return false;
        }
    }

    if (cmbTipoPoliza != '282' && cuentaCargo != '' && cuentaCargo != 0) {
        // Poliza de Diario Validar que sea en el ultimo nivel
        if (fnValidarCuentaUltimoNivel(cuentaCargo.trim()) == '0') {
            mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> La cuenta '+cuentaCargo+' no es el último nivel</p></h5>';
            muestraModalGeneral(3, titulo, mensaje);
            return false;
        }
    }
    if (cmbTipoPoliza != '282' && cuentaAbono != '' && cuentaAbono != 0) {
        // Poliza de Diario Validar que sea en el ultimo nivel
        if (fnValidarCuentaUltimoNivel(cuentaAbono.trim()) == '0') {
            mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> La cuenta '+cuentaAbono+' no es el último nivel</p></h5>';
            muestraModalGeneral(3, titulo, mensaje);
            return false;
        }
    }

    // Agregar información
    var btnConsultar = document.getElementById('Process');
    btnConsultar.click();
}

/**
 * Función para validar el nivel de la cuenta, si es el ultimo nivel retorna true de lo contrario false
 * @param  string Cuenta Contable
 * @return boolean Si la cuenta es el ultimo nivel muestra true de lo contrario false
 */
function fnValidarCuentaUltimoNivel(cuenta) {
    // Validar nivel
    // console.log("cuenta: "+cuenta);
    var respuesta = 0;
    dataObj = {
        cuenta: cuenta,
        option: 'ultimoNivelCuenta'
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/GLJournal_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        if(data.result){
            console.log("result: "+data.contenido.ultimoNivel);
            respuesta = data.contenido.ultimoNivel;
        }else{
            respuesta = 0;
        }
    })
    .fail(function(result) {
        respuesta = 0;
    });

    return respuesta;
}

var validarIdentificador = '<?php echo $validarIdentificador; ?>';

function fnCambioDatosIdentificador() {
    // Cargar Cuentas para abono y cargo de acuerdo a los filtros de identificador
    var tag = $("#tag").val();
    var selUE = $("#selUE").val();
    var cmbProgramaPresupuestario = $("#cmbProgramaPresupuestario").val();

    var identificador = tag+'-'+selUE+'-'+cmbProgramaPresupuestario;
    
    //alert("tag: "+tag+" - selUE: "+selUE+" - cmbProgramaPresupuestario: "+cmbProgramaPresupuestario);
    if (validarIdentificador == 1 && ($("#tag").val() == '-1' || $("#selUE").val() == '-1' || $("#cmbProgramaPresupuestario").val() == '-1')) {
        // Tiene datos
        identificador = '';
    }
    if (validarIdentificador == 1) {
        // Usar identificador
        if (document.querySelector(".cuentasDatos")) {
            dataObj = { 
                option: 'mostrarIdentificadorCuentas',
                identificador: identificador
            };
            fnSelectGeneralDatosAjax('.cuentasDatos', dataObj, 'modelo/componentes_modelo.php');
        }
        if (document.querySelector(".selectGLNarrative")) {
            dataObj = { 
                option: 'mostrarIdentificadorExtraPresupuestal',
                identificador: identificador
            };
            fnSelectGeneralDatosAjax('.selectGLNarrative', dataObj, 'modelo/componentes_modelo.php');
        }
    }
}

// Aplicar formato del SELECT
fnFormatoSelectGeneral(".tag");
fnFormatoSelectGeneral(".cuentasDatos");
fnFormatoSelectGeneral(".selectGLNarrative");
fnFormatoSelectGeneral(".cmbTipoPoliza");
fnFormatoSelectGeneral(".selectProgramaPresupuestarioLocal");

if (validarIdentificador == 1) {
    // Cargar Cuentas de acuerdo a filtros por default
    fnCambioDatosIdentificador();
}
// alert("tag: "+$("#tag").val()+" - ue: "+"<?php echo $_POST['selUE']; ?>");
if ($("#tag").val() != '-1') {
    // Si tiene ur seleccionada
    fnTraeUnidadesEjecutoras($("#tag").val(), 'selUE');

    var uesel = "<?php echo $_POST['selUE']; ?>";
    $('#selUE').val(''+uesel);
    $('#selUE').multiselect('rebuild');
}
</script>