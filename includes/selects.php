<?php
header("Content-Type: application/json");
//ini_set('display_errors', 1);
//ini_set('log_errors', 1);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//error_reporting(E_ALL);

$PageSecurity = 1;
$PathPrefix = '../';
include($PathPrefix.'includes/session.inc');
include($PathPrefix.'includes/SecurityFunctionsHeader.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

$ErrMsg = _( '' );
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";

if (!isset($_GET['tabla'])) exit;
$tabla = $_GET['tabla'];

if ($tabla =="suppliers") {
	$campo1 = "supplierid";
	$campo2 = "suppname";
}

$cargarprimeravez = true;

if ($cargarprimeravez) {
   
            $resultado = array();
            $cnx = $SQL;
            try{
            $aSQL= <<<SQL
                select {$campo1}, {$campo2} from $tabla   
SQL;
                $result = DB_query($aSQL, $db, $ErrMsg );
                $i = 0;
                
                while ($row = DB_fetch_array($result, MYSQL_ASSOC)){
                    $row_array[$campo1] = $row[$campo1];
                    $row_array[$campo2] = mb_convert_encoding($row[$campo2], 'ISO-8859-15', 'UTF-8');
                


                    $resultado[] = $row_array;
                    $i++;
                    
                }
            
            } 
            catch (Exception $e) 
            {
                //$resultado = array('error'=>'Error', 'msg'=>'error en try catch '.$e);
            }
          
            echo json_encode($resultado);
          

            }

            



?>