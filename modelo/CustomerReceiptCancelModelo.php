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


// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
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
$funcion=205;
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');
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

if ($option == 'cancelarRecibo') {
    $transno = $_POST['transno'];
    $comments = $_POST['comentario'];
    $typeNuevo = 14;
    $sqlValidacion2 = "SELECT 
	debtortrans.nu_foliocorte,
    debtortrans.tagref,
    debtortrans.nu_ue,
    debtortrans.type,
    debtortrans.id,
    debtortrans.ovamountcancel,
    debtortrans.ovamount,
    debtortrans.ovgstcancel,
    debtortrans.ovgst,
    custallocns.transid_allocto,
    debtortransFactura.order_
	FROM debtortrans 
    LEFT JOIN custallocns ON  debtortrans.id = custallocns.transid_allocfrom
    LEFT JOIN debtortrans debtortransFactura ON debtortransFactura.id = custallocns.transid_allocto
    Where debtortrans.type = 12 AND debtortrans.transno = '".$transno."'" ;
    $resultSelectVal2 = DB_query($sqlValidacion2, $db);
    
	while ($row = DB_fetch_array($resultSelectVal2)) {

            
			if($row['nu_foliocorte'] != '0'){
				$contenido = "No se puede cancelar ya que esta dento del conte de caja ".$row['nu_foliocorte'];
                $result = true;
                
			}else{
                if($row['transid_allocto'] != null){

                        $transnoNuevo = GetNextTransNo ( $typeNuevo, $db );

                        $folioPolizaUe = fnObtenerFolioUeGeneral($db, $row['tagref'], $row['nu_ue'], $typeNuevo);
                        

                        fnInsertPresupuestoLogMovContrarios($db, $row['type'], $transno, $typeNuevo, $transnoNuevo);

                        fnInsertPolizaMovContrarios($db, $row['type'], $transno, $typeNuevo, $transnoNuevo, $folioPolizaUe);



                        $SQL = "UPDATE debtortrans SET debtortrans.invtext = CONCAT(`debtortrans`.`invtext`, ' - Motivo cancelación: $comments'), debtortrans.ovamountcancel = '$row[ovamount]', debtortrans.ovgstcancel = '$row[ovgst]', debtortrans.ovamount = '0', debtortrans.ovgst = '0', debtortrans.alloc = '0'  
                        WHERE  debtortrans.type = 12 AND debtortrans.transno = '$transno'";
                        $ErrMsg = "No se agrego la informacion de ".$transno." en la actualizacion ";
                        $TransResult = DB_query($SQL, $db, $ErrMsg);

                        $SQL = "UPDATE debtortransmovs SET debtortransmovs.ovamount = '0', debtortransmovs.ovgst = '0', debtortransmovs.alloc = '0'
                        WHERE  debtortransmovs.type = 12 AND debtortransmovs.transno = '$transno'";
                        $ErrMsg = "No se agrego la informacion de ".$transno." en la actualizacion ";
                        $TransResult = DB_query($SQL, $db, $ErrMsg);

        
                        $SQL = "UPDATE debtortrans SET debtortrans.ovamountcancel = '$row[ovamount]', debtortrans.ovgstcancel = '$row[ovgst]', debtortrans.ovamount = '0', debtortrans.ovgst = '0', debtortrans.alloc = '0'  
                        WHERE  debtortrans.id = '$row[transid_allocto]'";
                        $ErrMsg = "No se agrego la informacion de ".$transno." en la actualizacion ";
                        $TransResult = DB_query($SQL, $db, $ErrMsg);


                        $SQL = "UPDATE salesorders SET salesorders.quotation = 1  WHERE salesorders.orderno = '$row[order_]'";
                        $ErrMsg = "No se agrego la informacion de ".$transno." en la actualizacion ";
                        $TransResult = DB_query($SQL, $db, $ErrMsg);


                        $SQL = "DELETE FROM custallocns  WHERE transid_allocfrom = '$row[id]'";
                        $ErrMsg = "No se agrego la informacion de ".$transno." en la actualizacion ";
                        $TransResult = DB_query($SQL, $db, $ErrMsg);
                        
                        $id_administracion_contratos = '0';
                        $id_contrato = '0';
                        $SQL = "SELECT DISTINCT
                        salesorderdetails.id_administracion_contratos,
                        tb_administracion_contratos.id_contrato
                        FROM salesorderdetails 
                        join tb_administracion_contratos ON tb_administracion_contratos.id_administracion_contratos = salesorderdetails.id_administracion_contratos
                        WHERE salesorderdetails.orderno = '$row[order_]'";
                        
                        $TransResult = DB_query($SQL, $db, $ErrMsg);
                        while ($myrow = DB_fetch_array($TransResult)) {
                            $id_contrato = $myrow ['id_contrato'];
                            $id_administracion_contratos = $myrow ['id_administracion_contratos'];
                        
 
                        if( $id_administracion_contratos != '0'){
                        
                            $SQL = "UPDATE tb_administracion_contratos SET tb_administracion_contratos.pase_cobro = '', tb_administracion_contratos.folio_recibo = '', tb_administracion_contratos.cajero = '', tb_administracion_contratos.dt_fechadepago = '0000-00-00', tb_administracion_contratos.estatus = 'En Proceso' 
                            WHERE tb_administracion_contratos.id_contrato = '$id_contrato' 
                            AND tb_administracion_contratos.id_administracion_contratos = '$id_administracion_contratos'";
                            $ErrMsg = "No se agrego la informacion de ".$id_contrato." en la actualizacion ";
                            $TransResult2 = DB_query($SQL, $db, $ErrMsg);

                        }

                    }

                    if(!empty($id_contrato)){
                        $SQL = "UPDATE tb_contratos SET tb_contratos.enum_status = 'Pendiente'  WHERE tb_contratos.id_contrato = '$id_contrato'";
                        $ErrMsg = "No se agrego la informacion de ".$id_contrato." en la actualizacion ";
                        $TransResult2 = DB_query($SQL, $db, $ErrMsg);
                    }
                    
            
                        $contenido = "Se cancelo el recibo de pago, se reversaron las polizas y presupuesto con número ". $transnoNuevo;
                        $result = true;

                  
    
                }else{

                    $contenido = "El recibo de pago " . $transno . " ya se encuentra cancelado";
                    $result = true;

                }

        }
	}
			
			
			 
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
			echo json_encode($dataObj);  