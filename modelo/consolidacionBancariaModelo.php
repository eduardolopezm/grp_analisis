<?php
/**
 * Modelo para consolidacion bancaria
 *
 * @category     modelo 
 * @package      ap_grp
 * @author       Arturo Lopez Peña <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 12/01/2018
 * Fecha Modificación: 15/03/2018
 */
 // ini_set('display_errors', 1);
 // ini_set('log_errors', 1);
 // error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');


function fnGuardarEstadoCuentaBanco($datosContenido,$lineas,$db){
 	$validacion=true;
$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);
 	$data='';
 	$datos='';
 	$separador=',';
 	$mensaje='Estado de cuenta cargado correctamente';
 	$infoConsolidacion='';
 	
 	$tieneerrores = 0;
    $lineatitulo  = 0;
    $mincolumnas  = 4;

    $columnafecha    = 0;
    $columnaconcepto = 1;
    $columnaretiro   = 2;
    $columnadeposito = 3;
    $totalRegistrosInsertados=0;
    $registrosMATCH1=0;
    $registrosMULTIMATCH1=0;
    $registrosNOMATCH1=0;
    $registrosMATCH2=0;
    
    $consolidacionTabla='';
    $cbTabla='';
    
 		 $data.= "<table border='1'>";
          for($f=0;$f<count($datosContenido);$f++) {
	          $data.= "<tr>";
	          for($c=0;$c<count($datosContenido[$f]);$c++) {
	           $data.="<td>".$datosContenido[$f][$c]."</td>";

	          }
	          $data."</tr>";

          }
          $data.= "</table>"; 

 	if (isset($_POST['anio'])) {
	    $ToYear = $_POST['anio'];
	} else {
	    $ToYear = date('Y');
	}

	if (isset($_POST['mes'])) {
	    $ToMes = $_POST['mes'];
	} else {
	    $ToMes = date('m');
	}

	if (isset($_POST['dia'])) {
	    $ToDia = $_POST['dia'];
	} else {
	    $ToDia = date('d');
	}

	$fechafin  = rtrim($ToYear) . '-' . rtrim($ToMes) . '-' . rtrim($ToDia);
	
	$fechafin  = rtrim($ToYear) . '-' . add_ceros(rtrim($ToMes), 2) . '-' . add_ceros(rtrim($ToDia), 1) . ' 23:59:59';
	
	//$fechafinc = mktime(23, 59, 59, rtrim($ToMes), rtrim($ToDia), rtrim($ToYear)); para ser que no se utiliza

	$InputError = 0;

	 foreach ($lineas as $line_num => $line) {
        /**
          * REEMPLAZA CARACTERES NO VALIDOS
        */
        $consolidacionTabla.='<tr>';
        $line = str_replace("'", "", $line); // QUITA COMILLA SIMPLE
        $line = str_replace('"', '', $line); // QUITA DOBLE COMILLA

        $datos         = explode($separador, $line); // Convierte en array cada una de las lineas( asi se obtien las columnas)

        $columnaslinea = count($datos); // Obtiene el numero de columnas de la linea en base al separador

        // if ($columnaslinea < $mincolumnas) {
        //     $tieneerrores = 1;
        //     $error        = 'El número mínimo de columnas requeridas no se cumple en la línea : ' . intval($line_num + 1);
        //     $error .= '<br>' .'La estructura del archivo debe de tener al menos ' . $mincolumnas . ' datos separados por "' . $separador . '"';
        //    $mensaje=$error;
        //     exit();

        // } else {

        // }
        $codigofecha = trim($datos[$columnafecha]);
        if ($codigofecha != '') {
                
          if (SUBSTR($codigofecha, 2, 1) == '/' or SUBSTR($codigofecha, 2, 1) == '-'){
           //if (($ToMes * 1 == SUBSTR($codigofecha, 3, 2) * 1) AND (SUBSTR($codigofecha, 6, 4)==$ToYear) ){ // f2


            $fechatrans = SUBSTR($codigofecha, 6, 4) . '-' . SUBSTR($codigofecha, 3, 2) . '-' . SUBSTR($codigofecha, 0, 2);
            $consolidacionTabla.='<td>'.($fechatrans).'</td>';
             $consolidacionTabla.='<td>'.($datos[$columnaconcepto]) .'</td>';
            $codigoretiro = '';
             
            if (is_numeric(trim($datos[$columnaretiro]))) {
               $codigoretiro = trim($datos[$columnaretiro]) * 1;
               $consolidacionTabla.='<td>'.( $codigoretiro).'</td>';
            } else {
               $codigoretiro = 0;
               $consolidacionTabla.='<td>'.( $codigoretiro).'</td>';
            }

            $codigodeposito = '';

            if (is_numeric(trim($datos[$columnadeposito]))) {
               $codigodeposito = trim($datos[$columnadeposito]) * 1;
               $consolidacionTabla.='<td>'. ($codigodeposito).'</td>';
            } else {
               $codigodeposito = 0;
                $consolidacionTabla.='<td>'. ($codigodeposito).'</td>';
            }

            $SQL = "INSERT INTO estadoscuentabancarios (
                    Fecha, 
                    Concepto, 
                    cuenta, 
                    Retiros, 
                    depositos, 
                    usuario)
                    VALUES(
                    '" . $fechatrans . "',
                    '" . str_replace('"', '', $datos[$columnaconcepto]) . "',
                    '" . $_POST['bank'] . "',
                    '" . $codigoretiro . "',
                    '" . $codigodeposito . "',
                    '" . $_SESSION['UserID'] . "')";

                                //screen_debug($sSQL, $ejecutar_debug, 'string', __LINE__, __FILE__);
             if ((abs($codigoretiro) + abs($codigodeposito)) > 0) {

                $result = DB_query($SQL, $db);
                
                $totalRegistrosInsertados = $totalRegistrosInsertados + 1;

                $esteid = $_SESSION['LastInsertId'];
                 /* AQUI VOY A PONER LA LOGICA PARA QUE AUTOMATICAMENTE CHEQUE MOVIMIENTOS QUE HACEN MATCH UNICO Y EXACTO !!! */
                 //  empieza consiliacion bancaria
                if ($codigoretiro > 0) {
                  $SQL = "SELECT count(*) AS unico,
                              max(banktransid) AS banktransid,
                              max(tagref) AS tagref,
                              transdate
                              FROM banktrans
                              WHERE amount = " . ($codigoretiro * -1) . "
                              AND transdate <= '" . $fechatrans . "'
                              AND bankact='" . $_POST["bank"] . "'
                              AND amountcleared = 0
							  GROUP BY banktransid,tagref,transdate
                              ";
                                //screen_debug($ssql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                  $result = DB_query($SQL, $db);
                    //print_r($SQL);
                  if ($myrow = DB_fetch_array($result)) {
                    if ($myrow['unico'] == 1) {
                   // prnMsg(_('ENCONTRE MATCH UNICO ! ID:'). $myrow['banktransid'] ,'success');
                    //  $consolidacion[]="consolidacion";

                    $registrosMATCH1 = $registrosMATCH1 + 1;
                     $consolidacionTabla.='<td>'.('Conciliado').'</td>';
                    $TransNo = GetNextTransNo(600, $db);
                    // selecciona el periodo al que pertenece la fecha
                    $SQL = "SELECT periodno,
                                  lastdate_in_period
                                  FROM periods
                                  WHERE YEAR(lastdate_in_period) = " . $ToYear . "
                                  AND MONTH(lastdate_in_period) = " . $ToMes;
                                        //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                    $ErrMsg= ('No se puede obtener informacion');
                    $resultCC       = DB_query($SQL, $db, $ErrMsg);
                    $myrowCC        = DB_fetch_array($resultCC);
                    $periododeMatch = $myrowCC[0];
                
                    $SQL = "UPDATE banktrans SET 
                             amountcleared= " . ($codigoretiro * -1) . ",
                             usuario = '" . $_SESSION['UserID'] . "-AUT', 
                             fechacambio = NOW(), 
                             fechabanco='" . $fechatrans . "', 
                             batchconciliacion= " . $TransNo . ", 
                             matchperiodno = " . $periododeMatch . " 
                                WHERE banktransid=" . $myrow['banktransid'];
                                //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                //
                     $ErrMsg   = ('Hubo un error al crear match');
                     $resultCC = DB_query($SQL, $db, $ErrMsg);
                    
                     $SQL = "UPDATE estadoscuentabancarios SET 
                      		 conciliado= " . ($codigoretiro) . ", 
                             usuario = '" . $_SESSION['UserID'] . "-AUT', 
                             fechacambio = NOW(), 
                             tagref = '" . $myrow['tagref'] . "', 
                             fechacontable='" . $myrow['transdate'] . "', 
                             batchconciliacion= " . $TransNo . " 
                             WHERE banktransid=" . $esteid;
                                        //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                     $ErrMsg = ('Hubo un error al crear match.');
                     $resultCC = DB_query($SQL, $db, $ErrMsg);
                        
                    
                     } elseif ($myrow['unico'] > 1) {
                           // si encunetra mas de una coincidencia
                           //prnMsg(_('ENCONTRE MULTIPLES MATCH ! NUMERO:'). $myrow['unico'] ,'warn');

                          /* INTENTA BUSCAR REGISTROS DENTRO DE LOS DIEZ DIAS MAS CERCANOS !! */
                     $consolidacionTabla.='<td>'.('No conciliado').'</td>';
                     $registrosMULTIMATCH1 = $registrosMULTIMATCH1 + 1;

                     $numeroAutorizacion = trim($datos[$columnaconcepto]);
                     $numeroAutorizacion = str_replace(".", " ", $numeroAutorizacion);
                     $numeroAutorizacion = str_replace("-", " ", $numeroAutorizacion);
                     $numeroAutorizacion = str_replace("/", " ", $numeroAutorizacion);
                     $numeroAutorizacion = str_replace("&", " ", $numeroAutorizacion);

                          /* EXPLOTA CADA PARTE SEPARADA POR ESPACIOS EN UN ELEMENTO DEL ARREGLO */
                     // para veirificar si hay alguna concidencia del concepto en el banktrans
                    $arregloDePalabras = explode(" ", $numeroAutorizacion);

                    $likeStr = "";
                    $sientro = 0;
                    for ($rept = 0; $rept < count($arregloDePalabras); $rept++) {
                      if (is_numeric($arregloDePalabras[$rept])) {
                        if (strlen($arregloDePalabras[$rept]) >= 4) {
                          $sientro = $sientro + 1;
                         if ($sientro > 1) {
                          $likeStr = $likeStr . " OR ref like '%" . $arregloDePalabras[$rept] . "%'";
                          } else {
                           $likeStr = $likeStr . "ref like '%" . $arregloDePalabras[$rept] . "%'";
                          }

                         }
                       }
                     }// fin for

                     if ($sientro > 0) {
                        $likeStr = "AND (" . $likeStr;
                        $likeStr = $likeStr . ")";
                        }

                     $SQL = "SELECT count(*) AS unico,
                             max(banktransid) AS banktransid,
                             max(tagref) AS tagref,
                             transdate
                      		FROM banktrans
                      		WHERE amount = " . ($codigoretiro * -1) . "
                            AND transdate <= '" . $fechatrans . "'
                            AND transdate >= '" . $fechatrans . "'
                            AND bankact='" . $_POST["bank"] . "'
                            AND amountcleared = 0 " . $likeStr;
                                        //screen_debug($ssql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                            $result = DB_query($SQL, $db);
                           //print_r($SQL);
                            if ($myrow = DB_fetch_array($result)) {

                             if ($myrow['unico'] == 1) {
                             // prnMsg(_('ENCONTRE MATCH UNICO **** 2 INTENTO ***** ! ID:') . $myrow['banktransid'], 'success');
                             // prnMsg($likeStr, 'success');
                             // prnMsg($line, 'success');

                              $registrosMATCH2      = $registrosMATCH2 + 1;
                              $registrosMULTIMATCH1 = $registrosMULTIMATCH1 - 1;

                              $TransNo              = GetNextTransNo(600, $db);

                              $sql = "SELECT periodno,
                                      lastdate_in_period
                               	      FROM periods
                               	      WHERE YEAR(lastdate_in_period) = " . $ToYear . "
                                     AND MONTH(lastdate_in_period) = " . $ToMes;
                                                //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                       $ErrMsg         = _('Could not retrieve transaction information');
                                       $resultCC       = DB_query($sql, $db, $ErrMsg);
                                       $myrowCC        = DB_fetch_array($resultCC);
                                       $periododeMatch = $myrowCC[0];

                               $sql = "UPDATE banktrans SET 
                                       amountcleared= " . ($codigoretiro * -1) . ", 
                                       usuario = '" . $_SESSION['UserID'] . "-AUT', 
                                       fechacambio = NOW(), 
                                       fechabanco='" . $fechatrans . "', 
                                       batchconciliacion= " . $TransNo . ", 
                                       matchperiodno = " . $periododeMatch . " 
                                       WHERE banktransid=" . $myrow['banktransid'];
                                                //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                     $ErrMsg   = _('Could not match off this payment because');
                                     $resultCC = DB_query($sql, $db, $ErrMsg);

                                    $sql = "UPDATE estadoscuentabancarios SET 
                                            conciliado= " . ($codigoretiro) . ", 
                                            usuario = '" . $_SESSION['UserID'] . "-AUT', 
                                            fechacambio = NOW(), 
                                            tagref = '" . $myrow['tagref'] . "', 
                                            batchconciliacion= " . $TransNo . " 
                                            WHERE banktransid=" . $esteid;
                                                //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                            $ErrMsg = _('Could not match off this payment because');
                                            $resultCC = DB_query($sql, $db, $ErrMsg);
                                            }
                                        }
                                    } else {
                                        //screen_debug($line, $ejecutar_debug, 'string', __LINE__, __FILE__, 'SIN MATCH');
                                        $registrosNOMATCH1 = $registrosNOMATCH1 + 1;
                                         $consolidacionTabla.='<td>'.('No conciliado').'</td>';
                                    }
                   }else{
                       $consolidacionTabla.='<td>'.('No conciliado').'</td>';
                   }// fin primer. busqueda de codifo unico       

                 }// fin codigo retiro
                 if ($codigodeposito > 0) {
                     $ssql = "SELECT count(*) AS unico,
                              max(banktransid) AS banktransid,
                              max(tagref) AS tagref,
                              transdate
                              FROM banktrans
                              WHERE amount = " . ($codigodeposito) . "
                              AND transdate <= '" . $fechatrans . "'
                              AND bankact='" . $_POST["bank"] . "'
                              AND amountcleared = 0
                              GROUP BY banktransid,tagref,transdate";
                                //screen_debug($ssql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                      $result = DB_query($ssql, $db);
                      if ($myrow = DB_fetch_array($result)) {
                       if ($myrow['unico'] == 1) {

                       //prnMsg(_('ENCONTRE MATCH UNICO ! ID:'). $myrow['banktransid'] ,'success');
                                        // prnMsg($line ,'success');

                        $registrosMATCH1 = $registrosMATCH1 + 1;

                        $TransNo = GetNextTransNo(600, $db);

                        $sql = "SELECT periodno,
                                lastdate_in_period
                                FROM periods
                                WHERE YEAR(lastdate_in_period) = " . $ToYear . "
                                AND MONTH(lastdate_in_period) = " . $ToMes;
                                        //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                        $ErrMsg         = _('Could not retrieve transaction information');
                        $resultCC       = DB_query($sql, $db, $ErrMsg);
                        $myrowCC        = DB_fetch_array($resultCC);
                        $periododeMatch = $myrowCC[0];

                        $sql = "UPDATE banktrans
                                SET amountcleared= " . ($codigodeposito) . ",
                                usuario = '" . $_SESSION['UserID'] . "-AUT',
                                fechacambio = NOW(),
                                fechabanco='" . $fechatrans . "',
                                batchconciliacion= " . $TransNo . ",
                                matchperiodno = " . $periododeMatch . "
                                WHERE banktransid=" . $myrow['banktransid'];
                                        //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                        $ErrMsg   = _('Could not match off this payment because');
                        $resultCC = DB_query($sql, $db, $ErrMsg);
                         $consolidacionTabla.='<td>'.('Conciliado').'</td>';
                        $sql = "UPDATE estadoscuentabancarios
                                SET conciliado= " . ($codigodeposito * -1) . ",
                                usuario = '" . $_SESSION['UserID'] . "-AUT', 
                                fechacambio = NOW(),
                                tagref = '" . $myrow['tagref'] . "',
                                fechacontable='" . $myrow['transdate'] . "',
                                batchconciliacion= " . $TransNo . "
                                WHERE banktransid=" . $esteid;
                                $ErrMsg = _('Could not match off this payment because');
                                        //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                                $resultCC = DB_query($sql, $db, $ErrMsg);
                               } elseif ($myrow['unico'] > 1) {
                                $consolidacionTabla.='<td>'.('No conciliado').'</td>';
                                       // prnMsg(_('ENCONTRE MULTIPLES MATCH ! NUMERO:'). $myrow['unico'] ,'warn');
                                        // prnMsg($line ,'warn');
                                        /* CON MULTIPLES MATCH */

                        $registrosMULTIMATCH1 = $registrosMULTIMATCH1 + 1;

                        $ssql = "SELECT count(*) as unico,
                                       max(banktransid) as banktransid,
                                       max(tagref) as tagref,transdate
                                    FROM banktrans
                                    WHERE amount = " . ($codigodeposito) . "
                                    AND transdate <= '" . $fechatrans . "'
                                    AND transdate >= DATE_SUB('" . $fechatrans . "', INTERVAL 10 DAY)
                                    AND bankact='" . $_POST["bank"] . "'
                                    AND  amountcleared = 0";
                                        //screen_debug($ssql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                        $result = DB_query($ssql, $db);
                        if ($myrow = DB_fetch_array($result)) {
                          if ($myrow['unico'] == 1) {
                                               // prnMsg(_('ENCONTRE MATCH UNICO **** 2 INTENTO ***** ! ID:'). $myrow['banktransid'] ,'success');
                                                // prnMsg($line ,'success');

                           $registrosMATCH2      = $registrosMATCH2 + 1;
                           $registrosMULTIMATCH1 = $registrosMULTIMATCH1 - 1;
                           $TransNo              = GetNextTransNo(600, $db);

                           $sql = "SELECT periodno,
                                   lastdate_in_period
                                   FROM periods
                                    WHERE YEAR(lastdate_in_period) = " . $ToYear . "
                                    AND MONTH(lastdate_in_period) = " . $ToMes;
                                                //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                            $ErrMsg         = _('Could not retrieve transaction information');
                            $resultCC       = DB_query($sql, $db, $ErrMsg);
                            $myrowCC        = DB_fetch_array($resultCC);
                            $periododeMatch = $myrowCC[0];

                            $sql = "UPDATE banktrans SET 
                                    amountcleared= " . ($codigodeposito) . ",
                                    usuario = '" . $_SESSION['UserID'] . "-AUT',
                                    fechacambio = NOW(),
                                    batchconciliacion= " . $TransNo . ",
                                    fechabanco='" . $fechatrans . "',
                                    matchperiodno = " . $periododeMatch . "
                                    WHERE banktransid=" . $myrow['banktransid'];
                             $ErrMsg   = _('Could not match off this payment because');
                                                //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                             $resultCC = DB_query($sql, $db, $ErrMsg);

                             $sql = "UPDATE estadoscuentabancarios
                                     SET conciliado= " . ($codigodeposito * -1) . ",
                                     usuario = '" . $_SESSION['UserID'] . "-AUT', 
                                     fechacambio = NOW(),
                                     tagref = '" . $myrow['tagref'] . "',
                                     fechacontable='" . $myrow['transdate'] . "',
                                     batchconciliacion= " . $TransNo . "
                                     WHERE banktransid=" . $esteid;
                                     $ErrMsg = _('Could not match off this payment because');

                                                //screen_debug($sql, $ejecutar_debug, 'string', __LINE__, __FILE__);
                            $resultCC = DB_query($sql, $db, $ErrMsg);
                                            }
                                        }
                                    } else {
                                        //screen_debug($line, $ejecutar_debug, 'string', __LINE__, __FILE__, 'SIN MATCH');
                                      $consolidacionTabla.='<td>'.('No conciliado').'</td>';
                                        $registrosNOMATCH1 = $registrosNOMATCH1 + 1;
                                    }
                                }
                            }
                 // fin empieza consiliacion bancaria
                 // 
             	}// fin deposito + retiro mayor a cero
             	
             
         	//}// fin validacion fecha  f2
           }// fin validacion fecha
                
        }// fin codigo fecha
        $consolidacionTabla.='</tr>';
    }// fin foreach
  $cbTabla=utf8_encode( $consolidacionTabla);
	if($totalRegistrosInsertados>0){

        $infoConsolidacion.="Se cargaron exitosamente al sistema " . $totalRegistrosInsertados . " transacciones bancarias de estado de cuenta<br>";
        $infoConsolidacion.="Registros conciliados en automático  primer intento : <b>" . $registrosMATCH1 . "</b> <br>";
        $infoConsolidacion.="Registros conciliados en automático  segundo intento <b>:" . $registrosMATCH2 . "</b><br>";
        $infoConsolidacion.="Registros con multiples matchs automáticos <b>:" . $registrosMULTIMATCH1. "</b><br>";
        //$infoConsolidacion.="registros sin matchs automáticos:" . $registrosNOMATCH1. "<br>";
            }
	
  //print_r($lineas);
  //$lineas1=$lineas;
  
  //$data
//print_r($cbTabla);
	$datosVal=array('mensaje' =>$mensaje, 'validacion' => $validacion,'infoConsolidacion' =>$infoConsolidacion, 'cbtabla'=>$cbTabla);
 // $datosVal=array('mensaje' =>'Archivo subido correctamente', 'validacion' => true);
//print_r($datosVal);

	return $datosVal;
}