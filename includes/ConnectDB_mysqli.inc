<?php

/* $Revision: 1.20 $ */

/* PeterMoulding.com
20071102 Change from mysql to mysqli;
20071102 Add $db to DB_escape_string();
*/

/* ARCHIVO MODIFICADO POR: Desarrollador*/
/* FECHA DE MODIFICACION: 10-NOV-2009 */
/* CAMBIOS:*/
/* 1.- TRADUCCION A ESPA�OL */
/* FIN DE CAMBIOS*/

define ('LIKE','LIKE');

$db = mysqli_connect($host , $dbuser, $dbpassword, $_SESSION['DatabaseName'], $mysqlport, $dbsocket);
//print_r($_SESSION['DatabaseName']);
/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

if ( !$db ) {
    echo '<BR>' . _('La configuracion en el archivo config.php para la base de datos del usuario y contrase�a no son los adecuados para conectase a el servidor de base de datos');
    exit;
}

if (!mysqli_select_db($db,$_SESSION['DatabaseName'])) {
    echo '<BR>' . _('El nombre de la compa�ia seleccionada no corresponde a ninguna base de datos en el servidor de bases de datos especificado en config.php. Tratar de firmarse con un nombre de empresa diferente'); echo '<BR><A HREF="index.php">' . _('Ir a Pagina Principal') . '</A>';
    echo '<BR><A HREF="index.php">' . _('Ir a Pagina Principal') . '</A>';
    unset ($_SESSION['DatabaseName']);
    exit;
}

require_once ($PathPrefix .'includes/MiscFunctions.php');

//DB wrapper functions to change only once for whole application
function DB_query($SQL, &$Conn, $ErrorMessage = '', $DebugMessage = '', $Transaction = false, $TrapErrors = false, $Line = '') {

    global $debug;
    global $PathPrefix; 
    global $db;
    
    $folioError= 0;
    $cadenaError= "";

    $Linea = "";
    if ($Line != '') {
        $Linea = "| Linea: " . $Line;
    }

    $usuario = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : 'admin';
    $SQLSuser                 = $SQL;
    $SQL                      = "/*" . $usuario . ":(" . $_SERVER['PHP_SELF'] . ") " . $Linea . " */ " . $SQL;
    //print_r($SQL);
    $result                   = mysqli_query($Conn, $SQL);
    $_SESSION['LastInsertId'] = mysqli_insert_id($Conn);

    if ($DebugMessage == '') {
        $DebugMessage = _('El SQL que fallo fue');
    }
    
    $debug = 1;

    if (DB_error_no($Conn) != 0) {
        $SQLerror = $SQL;

        // extrar numero de folio para el manejo de errores
        mysqli_query($db, "LOCK TABLES systypesinvtrans WRITE");
        $SQL = "SELECT typeno FROM systypesinvtrans WHERE typeid = '126'";
        $GetTransNoResult = mysqli_query($db, $SQL);
        $myrow=mysqli_fetch_row($GetTransNoResult);
        $folioError= $myrow[0];
        $SQL = 'UPDATE systypesinvtrans SET typeno = ' . ($myrow[0] + 1) . ' WHERE typeid = "126"';    
        $UpdTransNoResult = mysqli_query($db, $SQL);
        mysqli_query($db, "UNLOCK TABLES");

        if ($TrapErrors) {
            require_once $PathPrefix . 'includes/header.inc';
        }
        
        prnMsg("Error #". $folioError.", Hubo un error en la consulta de informacion, verifique con su administrador del sistema.", 'error', _('Error de Base de Datos'));

        $cadenaError= $ErrorMessage . '<br>' . DB_error_msg($Conn)."<br>".$DebugMessage . "<br>".$SQLerror."<br>";

        if ($debug == 1) {
            //prnMsg($DebugMessage . "<br />$SQLerror<br />", 'error', _('Falla de SQL en Base de Datos'));
        }
        
        if ($Transaction) {
            $SQL    = 'ROLLBACK';
            $Result = DB_query($SQL, $Conn);
            if (DB_error_no($Conn) != 0) {
                prnMsg(_('Error Rolling Back Transaccion'), '', _('Database Rollback Error'));
            }

        }

        $AuditSQL = "INSERT INTO auditerrorlog (transactiondate,
                            userid,
                            querystring, 
                            sn_folio_error)
                VALUES('" . Date('Y-m-d H:i:s') . "',
                    '" . trim($_SESSION['UserID']) . "',
                    '" . DB_escape_string($cadenaError) . "', '".$folioError."')";

        $AuditResult = mysqli_query($Conn, $AuditSQL);

        if ($TrapErrors) {
            include $PathPrefix . 'includes/footer_Index.inc';
            exit;
        }

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
                            '" . DB_escape_string($SQL) . "')";
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
                                    '" . DB_escape_string($SQL) . "',now())";

                    $AuditResult = mysqli_query($Conn, $AuditSQL);
                } else if (strpos(strtoupper($SQL), 'STOCKMOVES') > 0) {
                    $AuditSQL = "INSERT INTO stockmoves_user (id, userid, thisSQL,origtrandategl)
                                VALUES(" . $_SESSION['LastInsertId'] . ",
                                    '" . trim($_SESSION['UserID']) . "',
                                    '" . DB_escape_string($SQL) . "',now())";

                    $AuditResult = mysqli_query($Conn, $AuditSQL);
                }
            }
        }
    }
    return $result;

}


function DB_queryNT($SQL, &$Conn, $ErrorMessage = '', $DebugMessage = '', $Transaction = false, $TrapErrors = true) {

    global $debug;
    global $PathPrefix;
    global $db;

    $result                   = mysqli_query($Conn, $SQL);
    $_SESSION['LastInsertId'] = mysqli_insert_id($Conn);

    if ($DebugMessage == '') {
        $DebugMessage = _('El SQL que fallo fue');
    }

    if (DB_error_no($Conn) != 0 and $TrapErrors == true) {
        $SQLerror = $SQL;

        if ($TrapErrors) {
            require_once $PathPrefix . 'includes/header.inc';
        }
        prnMsg($ErrorMessage . '<br />' . DB_error_msg($Conn), 'error', _('Error de Base de Datos'));
        if ($debug == 1) {
            prnMsg($DebugMessage . "<br />$SQL<br />", 'error', _('Falla de SQL en Base de Datos'));
        }
        if ($Transaction) {

            //echo "entra:".DB_error_no($Conn);
            $SQL    = 'ROLLBACK';
            $Result = DB_query($SQL, $Conn);
            if (DB_error_no($Conn) != 0) {
                prnMsg(_('Error Rolling Back Transaccion'), '', _('Database Rollback Error'));
            }

        }

        $AuditSQL = "INSERT INTO auditerrorlog (transactiondate,
                            userid,
                            querystring)
                VALUES('" . Date('Y-m-d H:i:s') . "',
                    '" . trim($_SESSION['UserID']) . "',
                    '" . DB_escape_string($SQLerror) . "')";
        //$AuditResult = mysqli_query($Conn, $AuditSQL);

        if ($TrapErrors) {
            include $PathPrefix . 'includes/footer_Index.inc';
            exit;
        }

    }

    return $result;

}

function DB_fetch_row (&$ResultIndex) {

    $RowPointer=mysqli_fetch_row($ResultIndex);
    Return $RowPointer;
}

function DB_fetch_assoc (&$ResultIndex) {

    $RowPointer=mysqli_fetch_assoc($ResultIndex);
    Return $RowPointer;
}

function DB_fetch_array (&$ResultIndex) {

    $RowPointer=mysqli_fetch_array($ResultIndex);
    Return $RowPointer;
}

function DB_data_seek (&$ResultIndex,$Record) {
    mysqli_data_seek($ResultIndex,$Record);
}

function DB_free_result (&$ResultIndex){
    mysqli_free_result($ResultIndex);
}

function DB_num_rows (&$ResultIndex){
    return mysqli_num_rows($ResultIndex);
}

// Added by MGT
function DB_affected_rows(&$ResultIndex){
/* PeterMoulding.com
20071102 Add $db to DB_escape_string() and other functions;
    return mysqli_affected_rows($ResultIndex);
*/
    global $db;
    return mysqli_affected_rows($db);

}

function DB_error_no (&$Conn){
    return mysqli_errno($Conn);
}

function DB_error_msg(&$Conn){
    return mysqli_error($Conn);
}

function DB_Last_Insert_ID(&$Conn,$table, $fieldname){
//  return mysqli_insert_id($Conn);
    if (isset($_SESSION['LastInsertId'])) {
        $Last_Insert_ID = $_SESSION['LastInsertId'];
    } else {
        $Last_Insert_ID = 0;
    }
//  unset($_SESSION['LastInsertId']);
    return $Last_Insert_ID;
}

function DB_escape_string($String){
    global $db;
    return mysqli_real_escape_string($db, htmlspecialchars($String, ENT_COMPAT, _('iso-8859-1')));
}

function DB_show_tables(&$Conn){
    $Result = DB_query('SHOW TABLES',$Conn);
    Return $Result;
}

function DB_show_fields($TableName, &$Conn){
    $Result = DB_query("DESCRIBE $TableName",$Conn);
    Return $Result;
}

function INTERVAL( $val, $Inter ){
        global $dbtype;
        return "\n".'INTERVAL ' . $val . ' '. $Inter."\n";
}

function DB_Txn_Begin($Conn){
    mysqli_query($Conn,'SET autocommit=0');
    mysqli_query($Conn,'START TRANSACTION');
}

function DB_Txn_Commit($Conn){
    mysqli_query($Conn,'COMMIT');
    mysqli_query($Conn,'SET autocommit=1');
}

function DB_Txn_Rollback($Conn){
    mysqli_query($Conn,'ROLLBACK');
}

?>
