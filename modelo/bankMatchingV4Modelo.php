<?php
/**
 * Modelo para consolidacion bancaria diaria
 *
 * @category     consolidacion bancaria diaria
 * @package      ap_grp
 * @author       Arturo Lopez Peña <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 20/02/2018
 * Fecha Modificación: 20/02/2018
 */
 // ini_set('display_errors', 1);
 // ini_set('log_errors', 1);
 // error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';

session_start();
include($PathPrefix.'abajo.php');
require $PathPrefix . 'config.php';
require $PathPrefix . 'includes/ConnectDB.inc';
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=2291;
require $PathPrefix.'includes/SecurityFunctions.inc';
require $PathPrefix.'includes/SQL_CommonFunctions.inc';
require $PathPrefix . 'includes/DateFunctions.inc';


$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$RootPath = "";
$Mensaje = "";
$a=1;
$SQL='';

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);
$info = array();
$proceso = $_POST['proceso'];
// $datos=array();
$sql='';


switch ($proceso) {

	case 'getBanco':
	$info=array();
    $SQL="SELECT 
           distinct bankaccounts.accountcode as cuenta,
           bankaccountname as banco,
           bankaccounts.currcode as moneda
           FROM bankaccounts, chartmaster, tagsxbankaccounts, sec_unegsxuser
           WHERE bankaccounts.accountcode=chartmaster.accountcode 
           AND bankaccounts.accountcode = tagsxbankaccounts.accountcode 
           AND tagsxbankaccounts.tagref = sec_unegsxuser.tagref 
           AND sec_unegsxuser.userid = '". $_SESSION['UserID'] ."'";


    // if (isset($_POST['legalid']) && $_POST['legalid'] != '-1') {
    //     $sql = $sql . " AND tagsxbankaccounts.tagref in (select tagref from tags where legalid=" . $_POST['legalid'] . " )";
    // }
    $SQL = $SQL . ' GROUP BY bankaccountname,
			bankaccounts.accountcode,
			bankaccounts.currcode';


    $TransResult = DB_query($SQL, $db, $ErrMsg);
        
    while ( $myrow = DB_fetch_array($TransResult) ){

        $info[] = array('banco' =>$myrow['banco'],'cuenta' =>$myrow['cuenta']);
        
                
    }

    $contenido = array('DatosBanco' => $info);
    $result = true;
		break;


    case  'getTipoTransaccion':

      $SQL = 'SELECT banktrans.type, systypescat.typename
      from banktrans LEFT JOIN systypescat ON banktrans.type = systypescat.typeid
      group by banktrans.type,systypescat.typename';

       $TransResult = DB_query($SQL, $db, $ErrMsg);

      while ( $myrow = DB_fetch_array($TransResult) ){

        $info[] = array('tipo' =>$myrow['type'],'nombre' =>$myrow['typename']);
        
                
      }

    $contenido = array('DatosTransaccion' => $info);
    $result = true;

    break;

   case 'buscar':
   $datos='';
    if(isset($_POST['type'])){
      $Type=$_POST['type'];
        if ($Type == 'Payments') {
            $sql = "SELECT estadoscuentabancarios.banktransid,
                  estadoscuentabancarios.Concepto,
                  estadoscuentabancarios.Retiros,
                  estadoscuentabancarios.depositos,
                  estadoscuentabancarios.conciliado,
                  estadoscuentabancarios.Fecha,
                  estadoscuentabancarios.legalid,
                  IFNULL(estadoscuentabancarios.tagref,'') as tagref,
                  estadoscuentabancarios.fechacambio,
                  estadoscuentabancarios.usuario,
                  IFNULL(tags.tagname,'') as tagname
                FROM estadoscuentabancarios LEFT JOIN tags ON estadoscuentabancarios.tagref = tags.tagref ";
            // if ($_POST['legalid'] != -1 and isset($_POST['legalid'])) {
            //     $sql = $sql . " AND tags.legalid=" . $_POST['legalid'];
            // }

            // $sql = $sql . " WHERE Retiros > 0
            //     AND ADDDATE(Fecha," . $_POST["rangodiasBANCO"] . ") >= '" . $SQLAfterDate . "'
            //     AND SUBDATE(Fecha," . $_POST["rangodiasBANCO"] . ") <= '" . $SQLBeforeDate . "'

            //     AND ABS(depositos-Retiros) >= (" . ($_POST['buscamontoBANCO']) . "+(" . ($_POST['rangomontoBANCO'] * -1) . ")) AND ABS(depositos-Retiros) <= (" . ($_POST['buscamontoBANCO']) . "+" . $_POST['rangomontoBANCO'] . ")

            //     AND trim(cuenta)=" . $_POST["BankAccount"] . "
            //     AND (estadoscuentabancarios.tagref = '" . $_POST['tag'] . "' OR IFNULL(estadoscuentabancarios.tagref,'0') = '0'  OR IFNULL(estadoscuentabancarios.tagref,'') = '' OR  '" . $_POST['tag'] . "' = '0')
            //     AND  ABS(depositos-Retiros+conciliado) > 0.009  and batchconciliacion <> -1";

            $manyfinds = array();
            $manyfinds = explode(' o ', $_POST["searchtextBANCO"]);

            if ($_POST["searchtextBANCO"] != '*') {

                $sql      = $sql . " AND (";
                $primcond = 0;
                for ($mfind = 0; $mfind < count($manyfinds); $mfind++) {
                    $primcond = $primcond + 1;
                    if ($primcond == 1) {
                        $sql = $sql . "Concepto like '%" . TRIM($manyfinds[$mfind]) . "%'";
                    } else {
                        $sql = $sql . " OR Concepto like '%" . TRIM($manyfinds[$mfind]) . "%'";
                    }

                }
                $sql = $sql . ")";
            }

            $sql = $sql . " ORDER BY depositos-Retiros,Fecha";
            // echo $sql;
        } elseif ($Type == '*') {
            $sql = "SELECT estadoscuentabancarios.banktransid,
                  estadoscuentabancarios.Concepto,
                  estadoscuentabancarios.Retiros,
                  estadoscuentabancarios.depositos,
                  estadoscuentabancarios.conciliado,
                  estadoscuentabancarios.Fecha,
                  estadoscuentabancarios.legalid,
                  IFNULL(estadoscuentabancarios.tagref,'') as tagref,
                  estadoscuentabancarios.fechacambio,
                  estadoscuentabancarios.usuario,
                  IFNULL(tags.tagname,'') as tagname
                FROM estadoscuentabancarios LEFT JOIN tags ON estadoscuentabancarios.tagref = tags.tagref ";
            // if ($_POST['legalid'] != -1 and isset($_POST['legalid'])) {
            //     $sql = $sql . " AND tags.legalid=" . $_POST['legalid'];
            // }
            // $sql = $sql . " WHERE ADDDATE(Fecha," . $_POST["rangodiasBANCO"] . ") >= '" . $SQLAfterDate . "'
            //     AND SUBDATE(Fecha," . $_POST["rangodiasBANCO"] . ") <= '" . $SQLBeforeDate . "'

            //     AND ABS(depositos-Retiros) >= (" . ($_POST['buscamontoBANCO']) . "+(" . ($_POST['rangomontoBANCO'] * -1) . ")) AND ABS(depositos-Retiros) <= (" . ($_POST['buscamontoBANCO']) . "+" . $_POST['rangomontoBANCO'] . ")

            //     AND trim(cuenta)=" . $_POST["BankAccount"] . "
            //     AND (estadoscuentabancarios.tagref = '" . $_POST['tag'] . "' OR IFNULL(estadoscuentabancarios.tagref,'0') = '0'  OR IFNULL(estadoscuentabancarios.tagref,'') = '' OR  '" . $_POST['tag'] . "' = '0')
            //     AND  ABS(depositos-Retiros+conciliado) > 0.009  and batchconciliacion <> -1";

            $manyfinds = array();
            $manyfinds = explode(' o ', $_POST["searchtextBANCO"]);

            if ($_POST["searchtextBANCO"] != '*') {

                $sql      = $sql . " AND (";
                $primcond = 0;
                for ($mfind = 0; $mfind < count($manyfinds); $mfind++) {
                    $primcond = $primcond + 1;
                    if ($primcond == 1) {
                        $sql = $sql . "Concepto like '%" . TRIM($manyfinds[$mfind]) . "%'";
                    } else {
                        $sql = $sql . " OR Concepto like '%" . TRIM($manyfinds[$mfind]) . "%'";
                    }

                }
                $sql = $sql . ")";
            }

            $sql = $sql . "
                ORDER BY Retiros-depositos,Fecha";
        } else {
            /* Type must == Receipts */
            $sql = "SELECT estadoscuentabancarios.banktransid,
                  estadoscuentabancarios.Concepto,
                  estadoscuentabancarios.Retiros,
                  estadoscuentabancarios.depositos,
                  estadoscuentabancarios.conciliado,
                  estadoscuentabancarios.Fecha,
                  estadoscuentabancarios.legalid,
                  IFNULL(estadoscuentabancarios.tagref,'') as tagref,
                  estadoscuentabancarios.fechacambio,
                  estadoscuentabancarios.usuario,
                 IFNULL(tags.tagname,'') as tagname
                FROM estadoscuentabancarios LEFT JOIN tags ON estadoscuentabancarios.tagref = tags.tagref ";
            // if ($_POST['legalid'] != -1 and isset($_POST['legalid'])) {
            //     $sql = $sql . " AND tags.legalid=" . $_POST['legalid'];
            // }

            // $sql = $sql . "
            //     WHERE depositos > 0
            //     AND ADDDATE(Fecha," . $_POST["rangodiasBANCO"] . ") >= '" . $SQLAfterDate . "'
            //     AND SUBDATE(Fecha," . $_POST["rangodiasBANCO"] . ") <= '" . $SQLBeforeDate . "'

            //     AND ABS(depositos-Retiros) >= (" . ($_POST['buscamontoBANCO']) . "+(" . ($_POST['rangomontoBANCO'] * -1) . ")) AND ABS(depositos-Retiros) <= (" . ($_POST['buscamontoBANCO']) . "+" . $_POST['rangomontoBANCO'] . ")
            //     AND trim(cuenta)=" . $_POST["BankAccount"] . "
            //     AND (estadoscuentabancarios.tagref = '" . $_POST['tag'] . "' OR IFNULL(estadoscuentabancarios.tagref,'0') = '0'  OR IFNULL(estadoscuentabancarios.tagref,'') = '' OR  '" . $_POST['tag'] . "' = '0')
            //     AND  ABS(depositos-Retiros+conciliado) > 0.009  and batchconciliacion <> -1
            //     ";

            $manyfinds = array();
            $manyfinds = explode(' o ', $_POST["searchtextBANCO"]);

            if ($_POST["searchtextBANCO"] != '*') {

                $sql      = $sql . " AND (";
                $primcond = 0;
                for ($mfind = 0; $mfind < count($manyfinds); $mfind++) {
                    $primcond = $primcond + 1;
                    if ($primcond == 1) {
                        $sql = $sql . "Concepto like '%" . TRIM($manyfinds[$mfind]) . "%'";
                    } else {
                        $sql = $sql . " OR Concepto like '%" . TRIM($manyfinds[$mfind]) . "%'";
                    }

                }
                $sql = $sql . ")";
            }

            $sql = $sql . "
                ORDER BY Retiros-depositos,Fecha";
        }

        // echo $sql;
        $ErrMsg ="Error al cargar  informacion"; //_('The payments with the selected criteria could not be retrieved because');
        //debug_sql($sql, __line__, $debug_sql, __FILE__);
        $PaymentsResult = DB_query($sql, $db, $ErrMsg);


        //echo $TableHeader;

        $j        = 1; // page length counter
        $k        = 0; // row colour counter
        $i        = 1; // no of rows counter
        $ingresos = 0;
        $egresos  = 0;

        while ($myrow = DB_fetch_array( $PaymentsResult ) ) { //and (isset($_POST['buscaMovsERP']) or isset($_POST['FindAuto_']))

            $DisplayTranDate = ConvertSQLDate($myrow['transdate']);
            $Outstanding     = $myrow['amt'] - $myrow['amountcleared'];

            if ($myrow['amt'] >= 0) {
                $ingresos += $myrow['amt'];
            } else {
                $egresos += abs($myrow['amt']);
            }
            $FechaContable = "<input type=hidden name='BTContable_" . $i . " VALUE='" . $myrow['transdate'] . "'>";

             if (ABS($Outstanding) < 0.009) {  
                /* the payment is cleared dont show the check box */

                // printf("<tr bgcolor='#DDFFDD'>
                // <td style='font-size:xx-small'>%s</td>
                // <td style='font-size:xx-small'>%s</td>
                // <td style='font-size:xx-small'>%s</td>
                // <td style='font-size:xx-small'>%s</td>
                // <td style='font-size:xx-small'>%s</td>
                // <td style='font-size:xx-small'>%s</td>
                // <td style='font-size:xx-small'>%s</td>
                // <td class=number style='font-size:xx-small'>%s</td>
                // <td class=number style='font-size:xx-small'>%s</td>
                // <td colspan=2 style='font-size:xx-small'>%s<br>%s</td>
                // <td style='font-size:xx-small'><input type='hidden' name='Unclear_%s'><input type=hidden name='BT_%s' VALUE=%s><br>%s</td>
                // </tr>", $myrow['tagname'], $DisplayTranDate, $myrow['transno'], $myrow['beneficiary'], substr($myrow['ref'], 0, 90), $myrow['banktranstype'], $myrow['chequeno'], number_format($myrow['amt'], 2), number_format($Outstanding, 2), _('Unclear') . '(' . $myrow['batchconciliacion'] . ')', $myrow['usuario'], $i, $i, $myrow['banktransid'], $myrow['fechacambio']);

                // $suma3 = $suma3 + $myrow['amt'];

                $datos.='<tr>';
                 $datos.='<td>'.$myrow['tagname']."</td>";
                 //$datos.='<td>'.$DisplayTranDate."</td>";
                 $datos.='<td>'. $myrow['transno']."</td>";
                 $datos.='<td>'. $myrow['beneficiary']."</td>";
                 $datos.='<td>'. substr($myrow['ref'], 0, 90)."</td>";
                 $datos.='<td>'. $myrow['banktranstype']."</td>";
                 $datos.='<td>'. $myrow['chequeno']."</td>";
                 $datos.='<td>'. number_format($myrow['amt'], 2)."</td>";
                 $datos.='<td>'. number_format($Outstanding, 2). 'Unclear' . '(' . $myrow['batchconciliacion'] . ')'."</td>";
                 $datos.='<td>'. $myrow['usuario']."</td>";
                 // $datos.='<td>'. $i."</td>";
                 // $datos.='<td>'. $i."</td>";
                 $datos.='<td>'. $myrow['banktransid']."</td>";
                 $datos.='<td>'.$myrow['fechacambio']."</td>";

                $suma3 = $suma3 + $myrow['amt'];

                $datos.='</tr>';
             } else { 
              $datos.='<tr>';
                /*if ($k == 1) {
                    echo '<tr class="EvenTableRows">';
                    $k = 0;
                } else {
                    echo '<tr class="OddTableRows">';
                    $k = 1;
                } */

                // printf("<td style='font-size:xx-small'>%s</td>
                //       <td style='font-size:xx-small'>%s</td>
                // <td style='font-size:xx-small'>%s</td>
                // <td style='font-size:xx-small'>%s</td>
                // <td style='font-size:xx-small'>%s</td>
                // <td style='font-size:xx-small'>%s</td>
                // <td style='font-size:xx-small'>%s</td>
                // <td style='font-size:xx-small' class=number>%s</td>
                // <td style='font-size:xx-small' class=number>%s</td>
                // <td style='font-size:xx-small'><input type='checkbox' name='Clear_%s'><input type=hidden name='BT_%s' VALUE=%s></td>
                // <td style='font-size:xx-small' colspan=2><input type='text' maxlength=15 size=15 class=number name='AmtClear_%s'></td>
                // </tr>", $myrow['tagname'], $DisplayTranDate, $myrow['transno'], $myrow['beneficiary'], substr($myrow['ref'], 0, 90), $myrow['banktranstype'], $myrow['chequeno'], number_format($myrow['amt'], 2), number_format($Outstanding, 2), $i, $i, $myrow['banktransid'], $i);
                // $suma3 = $suma3 + $myrow['amt'];
                // 
                 $datos.="<td>".$myrow['tagname']."</td>";
                 //$datos.="<td>". $DisplayTranDate."</td>";
                 $datos.="<td>".$myrow['transno']."</td>";
                 $datos.="<td>". $myrow['beneficiary']."</td>";
                 $datos.="<td>".substr($myrow['ref'], 0, 90)."</td>";
                 $datos.="<td>". $myrow['banktranstype']."</td>";
                 $datos.="<td>".$myrow['chequeno']."<td>";
                 $datos.="<td>".number_format($myrow['amt'], 2)."</td>";
                 $datos.="<td>".number_format($Outstanding, 2)."</td>";
                 // $datos.="<td>".$i."</td>"; 
                 // $datos.="<td>".$i."</td>";
                 $datos.="<td>".$myrow['banktransid']."</td>" ;
                 $datos.="<td>".$i."</td>";
                $suma3 = $suma3 + $myrow['amt'];
                 $datos.='<tr>';
             }  //fin

            $j++;
           /* if ($j == 7) {
                $j = 1;
                echo $TableHeader;
            }*/
            // end of page full new headings if
            $i++;
              // print_r($datos);
        }
        // end of while loop

    }
    //print_r($datos);
      $contenido = array('Tabla' => $datos,'suma'=>  $suma3);
      $result=true;
  
   break;

      case 'buscar2':

      $condicion='';
      $tipo=$_POST['type'];
      $dateDesde=date("Y-m-d", strtotime($_POST['desde']));
      $dateHasta=date("Y-m-d", strtotime($_POST['hasta']));
      $banco=$_POST['bank'];
      $datos= array();

      if($dateDesde!=0 && $dateHasta!=0){
            //$condicion .= " AND purchorders.orddate between '".$fechaini." 00:00:00' AND '".$fechafin." 23:59:59' ";
       $condicion .=" WHERE estadoscuentabancarios.Fecha  >='" . $dateDesde . " 00:00:00' and estadoscuentabancarios.fecha <='" . $dateHasta . " 23:59:59' ";
        }else if($dateDesde!=0 && $dateHasta==0 ){
             $condicion .=" WHERE estadoscuentabancarios.Fecha >='" . $dateDesde . " 00:00:00'";
           
        }else if($dateDesde==0 && $dateHasta!=0 ){
            $condicion .=" WHERE estadoscuentabancarios.Fecha <='" . $dateHasta . " 23:59:59'";

        }

      $SQL="SELECT  estadoscuentabancarios.Fecha,concepto,retiros,depositos,if(conciliado != 0,'1','0') as conciliado,banktransid as idEstado, IF (conciliado !=0 ,'Conciliado', 'No conciliado' ) as estatus FROM estadoscuentabancarios ".$condicion." AND cuenta='".$banco."'";





      if($tipo==1){
         $SQL.=" AND conciliado >0 "; //conciliado
      }else if($tipo==2){
        $SQL.=" AND conciliado =0 "; // no conciliado
      }else{
        $SQL.="";
      }


      
      $SQL.=" ORDER BY fecha DESC";


      $TransResult = DB_query($SQL, $db, $ErrMsg);
        $condicion='';
      while ( $myrow = DB_fetch_array($TransResult) ){

        //if($myrow['conciliado']=='0'){
          $datos[]=array( 'checarcc'=>false,
            'fecha'=>$myrow['Fecha'], 
            'concepto'=>$myrow['concepto'], 
            'retiro'=>$myrow['retiros'],
            'deposito'=>$myrow['depositos'],
            'idEstado'=>$myrow['idEstado'],
            'conciliado'=>$myrow['conciliado'],
            'estatus'=>($myrow['conciliado']!=0) ? '<div class="estatusTeso estadoPro"><span>'.$myrow['estatus'].'</span></div>': ' <div class="estatusTeso estadoPen">'.$myrow['estatus']."</span></div>"
  
        );
       // }
        
                
    }

    $datos2= array();

     if($dateDesde!=0 && $dateHasta!=0){
            //$condicion .= " AND purchorders.orddate between '".$fechaini." 00:00:00' AND '".$fechafin." 23:59:59' ";
       $condicion .=" WHERE banktrans.transdate  >='" . $dateDesde . " 00:00:00'  and banktrans.transdate <='" . $dateHasta . " 23:59:59' ";
        }else if($dateDesde!=0 && $dateHasta==0 ){
             $condicion .=" WHERE banktrans.transdate >='" . $dateDesde . " 00:00:00'";
           
        }else if($dateDesde==0 && $dateHasta!=0 ){
            $condicion .=" WHERE banktrans.transdate <='" . $dateHasta . " 23:59:59'";

        }

    $SQL="SELECT  banktranstype,transdate as fecha, amount as  monto, chequeno as folio, ref as concepto, amountcleared as conciliado,banktransid as idBank,IF(amountcleared!=0 ,'Conciliado', 'No conciliado' ) as estatus  from banktrans ".$condicion. "AND bankact='".$banco."'";



     if($tipo==1){
         $SQL.=" AND  amountcleared >0 "; //conciliado
         $SQL.=" OR amountcleared <0 "; //conciliado
         
      }else if($tipo==2){
        $SQL.=" AND amountcleared =0 "; // no conciliado
      }else{
        $SQL.="";
      }

  

    $SQL.=" ORDER BY fecha DESC";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $monto=0;
    
    while ( $myrow = DB_fetch_array($TransResult) ){

      if($myrow['monto']<0){
        $monto=($myrow['monto'])*(-1);
      }else{
        $monto= $myrow['monto'];
      }
        //if($myrow['conciliado']=='0'){
          $datos2[]=array( 'checarcc1'=>false,
            'fecha'=>$myrow['fecha'], 
            'tipo'=>$myrow['banktranstype'],
            'folio'=>$myrow['folio'], 
            'monto'=>$monto, 
            'concepto'=>$myrow['concepto'],
            'idBank'=>$myrow['idBank'],
            'conciliado'=>$myrow['conciliado'],
            'estatus'=>($myrow['conciliado']!=0) ? '<div class="estatusTeso estadoPro"><span>'.$myrow['estatus'].'</span></div>': ' <div class="estatusTeso estadoPen">'.$myrow['estatus']."</span></div>"


        );
        //}
        
                
    }
     $contenido = array('datos' => $datos,'movBan'=>$datos2);
      $result=true;
      break;

      case 'conciliar':

      if(isset($_POST['estado'])&&(isset($_POST['movsBanks']))){
        
        $movs=$_POST['movsBanks'];
        $estado=$_POST['estado'];

        $codigoretiro=12;
        $fechatrans='2018-02-20';
        $periododeMatch='41';
        $tagref='I6L';

  
        for($a=0;$a<count($movs);$a++){
          $TransNo = GetNextTransNo(600, $db);
     

        $SQL = "UPDATE banktrans SET 
                             amountcleared= " . ($codigoretiro * -1) . ",
                             usuario = '" . $_SESSION['UserID'] . "', 
                             fechacambio = NOW(), 
                             fechabanco='" . $fechatrans . "', 
                             batchconciliacion= " . $TransNo . ", 
                             matchperiodno = " . $periododeMatch . " 
                            WHERE banktransid=" . $movs[$a];
                           
                     $ErrMsg   = ('Hubo un error al crear match');
                     $resultCC = DB_query($SQL, $db, $ErrMsg);
           }
                    
                     $SQL = "UPDATE estadoscuentabancarios SET 
                           conciliado= " . ($codigoretiro) . ", 
                             usuario = '" . $_SESSION['UserID'] ."', 
                             fechacambio = NOW(), 
                             tagref = '" . $tagref . "', 
                             fechacontable='" . $fechatrans . "', 
                             batchconciliacion= " . $TransNo . " 
                             WHERE banktransid=" . $estado[0];
                    
                     $ErrMsg = ('Hubo un error al crear match.');
                     $resultCC = DB_query($SQL, $db, $ErrMsg);
       
      }

      $contenido = 'Conciliacion hecha correctamente';
      $result=true;
      break;

}
$dataObj = array('info' =>'', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);