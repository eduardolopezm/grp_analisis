<?php
/** 
 * Modelo para el ABC de Fuente de Financiamiento
 *
 * @category ABC
 * @package ap_grp
 * @author Jesús Reyes Santos <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 07/11/2019
 * Fecha Modificación: 07/11/2019
 * Se realizan operación pero el Alta, Baja y Modificación. Mediante las validaciones creadas para la operación seleccionada
 */


//ini_set('display_errors', 1);
//ini_set('log_errors', 1);
//error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=602;
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

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

if ($option == 'AgregarCatalogo') {
    $orderno = $_POST['orderNo'];
    $comments = $_POST['comentario'];
   
            // Si no hay activo
            $info = array();
            $SQL = "UPDATE salesorders SET comments = '$comments' WHERE orderno = '$orderno'";
            $ErrMsg = "No se agregó la informacion";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido = "Se modificó el comentario con éxito";
			$result = true;
			
			
			 
}

if ($option == 'Cancelacion') {
    $orderno = $_POST['orderNo'];
    $comments = $_POST['comentario'];


        $quotation="0";
        $sqlValidacion = "SELECT 
        salesorders.quotation
        FROM salesorders 
        Where  salesorders.orderno = '".$orderno."'" ;
        $resultSelectVal = DB_query($sqlValidacion, $db);

        while ($myrow = DB_fetch_array($resultSelectVal)) {       
            $quotation = $myrow ['quotation'];

        } 
   
        $ovamount="0";
        $order_="";
        $sqlValidacion2 = "SELECT 
        debtortrans.ovamount,
        debtortrans.order_
        FROM debtortrans 
        Where debtortrans.type = 119 AND debtortrans.order_ = '".$orderno."'" ;
        $resultSelectVal2 = DB_query($sqlValidacion2, $db);

        while ($row = DB_fetch_array($resultSelectVal2)) {
            $ovamount = $row ['ovamount'];
            $order_ = $row ['order_'];

            if($ovamount != "0" && isset($order_)){
                $contenido = "Existe un recibo de pago del pase ". $orderno . ". Primero debe cancelar el recibo en la función 205.";
                $result = true;
                
            }

        } 

        if($quotation == '3'){
            $contenido = "El pase de cobro ". $orderno . " ya se encuentra cancelado.";
            $result = true;
        }else{
            $id_administracion_contratos = '0';
            $id_contrato = '0';
            $SQL = "SELECT DISTINCT
            salesorderdetails.id_administracion_contratos,
            tb_administracion_contratos.id_contrato
            FROM salesorderdetails 
            join tb_administracion_contratos ON tb_administracion_contratos.id_administracion_contratos = salesorderdetails.id_administracion_contratos
            WHERE salesorderdetails.orderno = '$orderno'";
            
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            while ($myrow = DB_fetch_array($TransResult)) {
                    $id_contrato = $myrow ['id_contrato'];
                    $id_administracion_contratos = $myrow ['id_administracion_contratos'];
                

                    if( $id_administracion_contratos != '0'){
                    
                        $SQL = "UPDATE tb_administracion_contratos SET tb_administracion_contratos.pase_cobro = ''
                        WHERE tb_administracion_contratos.id_contrato = '$id_contrato' 
                        AND tb_administracion_contratos.id_administracion_contratos = '$id_administracion_contratos'";
                        $ErrMsg = "No se agrego la informacion de ".$id_contrato." en la actualizacion ";
                        $TransResult2 = DB_query($SQL, $db, $ErrMsg);

                    }
            }

            if($ovamount == '0'){
             

                $SQL = "UPDATE salesorders SET salesorders.comments = CONCAT(`salesorders`.`comments`, ' - Motivo cancelación: $comments'), salesorders.quotation = '3' WHERE salesorders.orderno = '$orderno'";
                $ErrMsg = "No se agregó la informacion";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                $contenido = "Se canceló el pase de cobro ".$orderno;
                $result = true;


            }

        }     
    	 
}


$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
			echo json_encode($dataObj);  