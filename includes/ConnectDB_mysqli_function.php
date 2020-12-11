<?php

define('LIKE', 'LIKE');

require_once $PathPrefix . 'includes/MiscFunctions.php';

//DB wrapper functions to change only once for whole application

function DB_query($SQL,
    &$Conn,
    $ErrorMessage = '',
    $DebugMessage = '',
    $Transaction = false,
    $TrapErrors = false,
    $Line = '') {

    global $debug;
    global $PathPrefix; //
    global $db;

    $Linea = "";
    if ($Line != '') {
        $Linea = "| Linea: " . $Line;
    }
    
    // if (empty($_SESSION['UserID'])){
    //     $_SESSION['UserID']= "admin";
    // }
    
    if (empty($_SESSION['DefaultDateFormat'])){
        $_SESSION['DefaultDateFormat'] = "d/m/Y";
    }

    $SQLSuser                 = $SQL;
    $SQL                      = "/*" . $_SESSION['UserID'] . ":(" . $_SERVER['PHP_SELF'] . ") " . $Linea . " */ " . $SQL;
    $result                   = mysqli_query($Conn, $SQL);
    $_SESSION['LastInsertId'] = mysqli_insert_id($Conn);

    if ($DebugMessage == '') {
        $DebugMessage = _('El SQL que fallo fue');
    }
    $debug = 1;
    if (DB_error_no($Conn) != 0) {
        $SQLerror = $SQL;

        if ($TrapErrors) {
            require_once $PathPrefix . 'includes/header.inc';
        }
        if ($Transaction) {

            $SQL    = 'ROLLBACK';
            $Result = DB_query($SQL, $Conn);

        }

        $AuditSQL = "INSERT INTO auditerrorlog (transactiondate,
                            userid,
                            querystring)
                VALUES('" . Date('Y-m-d H:i:s') . "',
                    '" . trim($_SESSION['UserID']) . "',
                    '" . DB_escape_string($SQLerror, $Conn) . "')";
        $AuditResult = mysqli_query($Conn, $AuditSQL);

    } elseif (isset($_SESSION['MonthsAuditTrail']) and (DB_error_no($Conn) == 0 and $_SESSION['MonthsAuditTrail'] > 0)) {

        $SQLArray = explode(' ', $SQLSuser);

        if (($SQLArray[0] == 'INSERT')
            or ($SQLArray[0] == 'UPDATE')
            or ($SQLArray[0] == 'DELETE')) {

            if ($SQLArray[2] != 'audittrail' and $SQLArray[1] != 'systypescusttrans' and $SQLArray[1] != 'sysDocumentIndex' and $SQLArray[1] != 'systypesinvtrans' and $SQLArray[1] != 'systypesinvoice') {
                // to ensure the auto delete of audit trail history is not logged
                $AuditSQL = "INSERT INTO audittrail (transactiondate,
                                    userid,
                                    querystring)
                        VALUES('" . Date('Y-m-d H:i:s') . "',
                            '" . trim($_SESSION['UserID']) . "',
                            '" . DB_escape_string($SQL, $Conn) . "')";
                if ($_SESSION['UserID'] == "desarrollo") {
                    //echo $AuditSQL;
                }
                $AuditResult = mysqli_query($Conn, $AuditSQL);
            }
            //
            if ($SQLArray[0] == 'INSERT') {
                if (strpos(strtoupper($SQL), 'GLTRANS') > 0) {
                    $AuditSQL = "INSERT INTO gltrans_user (id, userid, thisSQL,origtrandategl)
                                VALUES(" . $_SESSION['LastInsertId'] . ",
                                    '" . trim($_SESSION['UserID']) . "',
                                    '" . DB_escape_string($SQL, $Conn) . "',now())";

                    $AuditResult = mysqli_query($Conn, $AuditSQL);
                } else if (strpos(strtoupper($SQL), 'STOCKMOVES') > 0) {
                    $AuditSQL = "INSERT INTO stockmoves_user (id, userid, thisSQL,origtrandategl)
                                VALUES(" . $_SESSION['LastInsertId'] . ",
                                    '" . trim($_SESSION['UserID']) . "',
                                    '" . DB_escape_string($SQL, $Conn) . "',now())";

                    $AuditResult = mysqli_query($Conn, $AuditSQL);
                }
            }
        }
    }
    return $result;

}

function DB_queryNoOut($SQL,
    &$Conn,
    $ErrorMessage = '',
    $DebugMessage = '',
    $Transaction = false,
    $TrapErrors = false,
    $Line = '') {

    global $debug;
    global $PathPrefix; //
    global $db;

    $Linea = "";
    if ($Line != '') {
        $Linea = "| Linea: " . $Line;
    }

    $SQLSuser                 = $SQL;
    $SQL                      = "/*" . $_SESSION['UserID'] . ":(" . $_SERVER['PHP_SELF'] . ") " . $Linea . " */ " . $SQL;
    $result                   = mysqli_query($Conn, $SQL);
    $_SESSION['LastInsertId'] = mysqli_insert_id($Conn);

    if ($DebugMessage == '') {
        $DebugMessage = _('El SQL que fallo fue');
    }
    $debug = 1;
    if (DB_error_no($Conn) != 0) {
        $SQLerror = $SQL;
        if ($Transaction) {
            $SQL    = 'ROLLBACK';
            $Result = DB_queryNoOut($SQL, $Conn);
        }
        $AuditSQL = "INSERT INTO auditerrorlog (transactiondate,
                            userid,
                            querystring)
                VALUES('" . Date('Y-m-d H:i:s') . "',
                    '" . trim($_SESSION['UserID']) . "',
                    '" . mysqli_real_escape_string($Conn, htmlspecialchars($SQLerror, ENT_COMPAT, _('iso-8859-1'))) . "')";
        $AuditResult = mysqli_query($Conn, $AuditSQL);

    } elseif (isset($_SESSION['MonthsAuditTrail']) and (DB_error_no($Conn) == 0 and $_SESSION['MonthsAuditTrail'] > 0)) {

        $SQLArray = explode(' ', $SQLSuser);
        //echo var_dump($SQLArray);

        if (($SQLArray[0] == 'INSERT')
            or ($SQLArray[0] == 'UPDATE')
            or ($SQLArray[0] == 'DELETE')) {

            if ($SQLArray[2] != 'audittrail' and $SQLArray[1] != 'systypescusttrans' and $SQLArray[1] != 'sysDocumentIndex' and $SQLArray[1] != 'systypesinvtrans' and $SQLArray[1] != 'systypesinvoice') {
                // to ensure the auto delete of audit trail history is not logged
                $AuditSQL = "INSERT INTO audittrail (transactiondate,
                                    userid,
                                    querystring)
                        VALUES('" . Date('Y-m-d H:i:s') . "',
                            '" . trim($_SESSION['UserID']) . "',
                            '" . mysqli_real_escape_string($Conn, htmlspecialchars($SQL, ENT_COMPAT, _('iso-8859-1'))) . "')";
                if ($_SESSION['UserID'] == "desarrollo") {
                    //echo $AuditSQL;
                }
                $AuditResult = mysqli_query($Conn, $AuditSQL);
            }
            //
            if ($SQLArray[0] == 'INSERT') {
                if (strpos(strtoupper($SQL), 'GLTRANS') > 0) {
                    $AuditSQL = "INSERT INTO gltrans_user (id, userid, thisSQL,origtrandategl)
                                VALUES(" . $_SESSION['LastInsertId'] . ",
                                    '" . trim($_SESSION['UserID']) . "',
                                    '" . mysqli_real_escape_string($Conn, htmlspecialchars($SQL, ENT_COMPAT, _('iso-8859-1'))) . "',now())";

                    $AuditResult = mysqli_query($Conn, $AuditSQL);
                } else if (strpos(strtoupper($SQL), 'STOCKMOVES') > 0) {
                    $AuditSQL = "INSERT INTO stockmoves_user (id, userid, thisSQL,origtrandategl)
                                VALUES(" . $_SESSION['LastInsertId'] . ",
                                    '" . trim($_SESSION['UserID']) . "',
                                    '" . mysqli_real_escape_string($Conn, htmlspecialchars($SQL, ENT_COMPAT, _('iso-8859-1'))) . "',now())";

                    $AuditResult = mysqli_query($Conn, $AuditSQL);
                }
            }
        }
    }
    return $result;

}

function DB_queryNT($SQL,
    &$Conn,
    $ErrorMessage = '',
    $DebugMessage = '',
    $Transaction = false,
    $TrapErrors = true) {

    global $debug;
    global $PathPrefix;
    global $db;
/* PeterMoulding.com
20071102 Change from mysql to mysqli;
$result=mysql_query($SQL,$Conn);
 */

    $result                   = mysqli_query($Conn, $SQL);
    $_SESSION['LastInsertId'] = mysqli_insert_id($Conn);

    if ($DebugMessage == '') {
        $DebugMessage = _('El SQL que fallo fue');
    }

    if (DB_error_no($Conn) != 0 and $TrapErrors == true) {
        $SQLerror = $SQL;

        if ($Transaction) {
            $SQL    = 'ROLLBACK';
            $Result = DB_query($SQL, $Conn);
        }

        /*$AuditSQL = "INSERT INTO auditerrorlog (transactiondate,
                            userid,
                            querystring)
                VALUES('" . Date('Y-m-d H:i:s') . "',
                    '" . trim($_SESSION['UserID']) . "',
                    '" . DB_escape_string($SQLerror) . "')";
        */
        // $AuditResult = mysqli_query($Conn, $AuditSQL);

    }

    return $result;
}

function DB_queryNoTrail($SQL,
    &$Conn,
    $ErrorMessage = '',
    $DebugMessage = '',
    $Transaction = false,
    $TrapErrors = true) {

    global $debug;
    global $PathPrefix;

    $result                   = mysql_query($SQL, $Conn);
    $_SESSION['LastInsertId'] = mysql_insert_id($Conn);

    if ($DebugMessage == '') {
        $DebugMessage = _('The SQL that failed was');
    }

    if (DB_error_no($Conn) != 0 and $TrapErrors == true) {

        if ($Transaction) {
            $SQL    = 'rollback';
            $Result = DB_queryNoTrail($SQL, $Conn);
        }
    } elseif (isset($_SESSION['MonthsAuditTrail']) and (DB_error_no($Conn) == 0 and $_SESSION['MonthsAuditTrail'] > 0)) {

        $SQLArray = explode(' ', $SQL);

        /*if (($SQLArray[0] == 'INSERT')
    OR ($SQLArray[0] == 'UPDATE')
    OR ($SQLArray[0] == 'DELETE')) {

    if ($SQLArray[2]!='audittrail' and $SQLArray[1]!='systypes'){ // to ensure the auto delete of audit trail history is not logged
    $AuditSQL = "INSERT INTO audittrail (transactiondate,
    userid,
    querystring)
    VALUES('" . Date('Y-m-d H:i:s') . "',
    '" . trim($_SESSION['UserID']) . "',
    '" . DB_escape_string($SQL) . "')";

    $AuditResult = mysql_query($AuditSQL,$Conn);
    }
    }
     */
    }

    return $result;

}

function DB_fetch_row(&$ResultIndex)
{

    $RowPointer = mysqli_fetch_row($ResultIndex);
    return $RowPointer;
}

function DB_fetch_assoc(&$ResultIndex)
{

    $RowPointer = mysqli_fetch_assoc($ResultIndex);
    return $RowPointer;
}

function DB_fetch_array(&$ResultIndex)
{

    $RowPointer = mysqli_fetch_array($ResultIndex);
    return $RowPointer;
}

function DB_data_seek(&$ResultIndex, $Record)
{
    mysqli_data_seek($ResultIndex, $Record);
}

function DB_free_result(&$ResultIndex)
{
    mysqli_free_result($ResultIndex);
}

function DB_num_rows(&$ResultIndex)
{
    return mysqli_num_rows($ResultIndex);
}
function DB_affected_rows(&$ResultIndex)
{
    global $db;
    return mysqli_affected_rows($db);
}

function DB_error_no(&$Conn)
{
    return mysqli_errno($Conn);
}

function DB_error_msg(&$Conn)
{
    return mysqli_error($Conn);
}

function DB_Last_Insert_ID(&$Conn, $table, $fieldname)
{
    if (isset($_SESSION['LastInsertId'])) {
        $Last_Insert_ID = $_SESSION['LastInsertId'];
    } else {
        $Last_Insert_ID = 0;
    }
    return $Last_Insert_ID;
}

function DB_escape_string($String,$conn = NULL)
{
    if (is_null($conn)){
        global $db;    
    }else{
        $db = $conn;
    }
    
    return mysqli_real_escape_string($db, htmlspecialchars($String, ENT_COMPAT, _('iso-8859-1')));
}

function DB_show_tables(&$Conn)
{
    $Result = DB_query('SHOW TABLES', $Conn);
    return $Result;
}

function DB_show_fields($TableName, &$Conn)
{
    $Result = DB_query("DESCRIBE $TableName", $Conn);
    return $Result;
}

function INTERVAL($val, $Inter)
{
    global $dbtype;
    return "\n" . 'INTERVAL ' . $val . ' ' . $Inter . "\n";
}

function DB_Maintenance($Conn)
{

    //prnMsg(_('El sistema ha ejecutado la tarea regular de administracion y optimizacion de bases de datos.'),'info');

    #$TablesResult = DB_query('SHOW TABLES',$Conn);
    #while ($myrow = DB_fetch_row($TablesResult)){
    #    $Result = DB_query('OPTIMIZE TABLE ' . $myrow[0],$Conn);
    #}

    #$Result = DB_query("UPDATE config
    #            SET confvalue='" . Date('Y-m-d') . "'
    #            WHERE confname='DB_Maintenance_LastRun'",
    #            $Conn);
}

function DB_Txn_Begin($Conn)
{
    mysqli_query($Conn, 'SET autocommit=0');
    mysqli_query($Conn, 'START TRANSACTION');
}

function DB_Txn_Commit($Conn)
{
    mysqli_query($Conn, 'COMMIT');
    mysqli_query($Conn, 'SET autocommit=1');
}

function DB_Txn_Rollback($Conn)
{
    mysqli_query($Conn, 'ROLLBACK');
}

function DB_close($Conn)
{
    mysqli_close($Conn);
}
