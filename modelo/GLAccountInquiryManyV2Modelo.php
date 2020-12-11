<?php

ini_set('memory_limit', '1024M');

/**
 * Modelo para almacen
 *
 * @category     Almacen
 * @package      ap_grp
 * @author       Arturo Lopez Peña <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 25/04/2018
 * Fecha Modificación: 25/04/2018
 */
$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix.'abajo.php');
require $PathPrefix . 'config.php';
require $PathPrefix . 'includes/ConnectDB.inc';
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=503;
require $PathPrefix.'includes/SecurityFunctions.inc';
require $PathPrefix.'includes/SQL_CommonFunctions.inc';
require $PathPrefix . 'includes/DateFunctions.inc';



$contenido = array();
$result = false;
$RootPath = "";
$Mensaje = "";
$SQL='';
$ErrorMsg='';
header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

function fnCrearSelects($SQL, $db, $val, $text, $label = 0, $agregar = array())
{

                $TransResult = DB_query($SQL, $db);
                //DB_Txn_Commit($db);
                $datos = array([ 'label'=>'Seleccionar', 'title'=>'Seleccionar', 'value'=>'-1'
                ]);
    if (count($agregar)>0) {
        $datos[] =$agregar;
    }
                
    while ($myrow = DB_fetch_array($TransResult)) {
        $datos[] = [
            'label'=> utf8_encode((($label==0)? ($myrow [$val]." - ".$myrow [$text]) : $myrow [$text] )),
            'title'=> $myrow [$text] ,
            'value'=> $myrow [$val]
        ];
        //array( 'value' => $myrow [''] );
    }
    return $datos;
}
function fnCrearLista($SQL, $db, $val, $text)
{
    $datosCortos=array();
    $datosLargos=array();
    $datos=array();
    DB_Txn_Begin($db);
    try {
        $TransResult = DB_query($SQL, $db);
        DB_Txn_Commit($db);

        while ($myrow = DB_fetch_array($TransResult)) {
            $veces=substr_count($myrow[$val], '.');
       

            if ($veces<5) {
                 $datosCortos[]=[
                'value'=> $myrow[$val],
                'text'=> utf8_encode($myrow[$text])
                 ];
            } else {
                $datosLargos[] = [
                'value'=> $myrow[$val],
                'text'=> utf8_encode($myrow[$text])
                ];
            }
        }
   
        $datos[]=$datosCortos;
        $datos[]=$datosLargos;
        return $datos;
    } catch (Exception $e) {
        $ErrorMsg= $e->getMessage();
        DB_Txn_Rollback($db);
        $contenido = $ErrorMsg;
    }
}
if ((isset($_POST['proceso'])) && (!empty($_POST['proceso'])) && (!is_null($_POST['proceso']))) {
    $accion =  trim($_POST['proceso']);
    switch ($accion) {
        case 'getSelects':
            DB_Txn_Begin($db);
            try {
                //$SQL="SELECT accountcode,accountname, group_ as padre FROM chartmaster ORDER BY accountcode, accountcode;";
                //$cuentas=fnCrearLista($SQL, $db, 'accountcode', 'accountname'); //fnCrearSelects($SQL, $db, 'accountcode', 'accountname');

                $todos = array('label'=>'Todos', 'title'=>'Todos', 'value'=>'0'
                );
                $SQL1="SELECT tipo,nombreMayor FROM chartTipos ORDER BY tipo";
                
                $tipos=fnCrearSelects($SQL1, $db, 'tipo', 'nombreMayor', '1', $todos);

                $todas = array( 'label'=>'Todas', 'title'=>'Todas', 'value'=>'0'
                );
                $SQL2="SELECT typeid, typename FROM systypescat ORDER BY typeid;";
                $tipoPolizas=fnCrearSelects($SQL2, $db, 'typeid', 'typename', '0', $todas);


                //'cuentasMenores' => $cuentas[0],'cuentasMayores' => $cuentas[1],
                $contenido = array('tipos' => $tipos,'tipoPolizas'=>$tipoPolizas);
                $result = true;
            } catch (Exception $e) {
                $ErrorMsg= $e->getMessage();
                DB_Txn_Rollback($db);

                $contenido = $ErrorMsg;
                $result = true;
            }

            # code...
            break;
        case 'buscar':
            $datos=array();
          
            foreach ($_POST as $ad => $val) {
                if ((!empty($val))&&(!is_null($val))) {
                    //if ($ad!='selectUnidadNegocio') {
                         $val=trim($val);
                         $val=str_replace("'", "", $val);
                         $datos[$ad]=$val;
                    //}
                } else {
                    $datos[$ad]=null;
                }
            }// fin foreach


            DB_Txn_Begin($db);
            try {
                $dateDesde= date("Y-m-d", strtotime($datos['dateDesde']));
                $dateHasta= date("Y-m-d", strtotime($datos['dateHasta']));
            
                $SQL="SELECT gltrans.account,
                chartmaster.accountname, gltrans.type,'' as debtorno, '' as name,suppliers.suppname
                typename,
                gltrans.typeno,
                gltrans.trandate,
                gltrans.chequeno,
                gltrans.narrative as narrativeOrig,
                CASE WHEN gltrans.type in(10,11,12,13,21,70,110) THEN concat(gltrans.narrative,' @ ','')
                ELSE
                CASE WHEN gltrans.type in(20,22) THEN concat(gltrans.narrative,' @ ',suppliers.suppname)
                ELSE 'showorig' END END AS narrative,
                amount,
                periodno,
                tag,'' as folio, '' as order_, chartmaster.naturaleza,
                gltrans.posted,
                systypescat.typename,
                CASE WHEN tb_cat_poliza_visual.ln_nombre IS NULL THEN systypescat.typename ELSE tb_cat_poliza_visual.ln_nombre END as nombreVisual
                FROM gltrans JOIN tags ON gltrans.tag = tags.tagref
                INNER JOIN systypescat on gltrans.type=systypescat.typeid
                LEFT JOIN tb_cat_poliza_visual ON tb_cat_poliza_visual.id = systypescat.nu_poliza_visual
                JOIN chartmaster ON gltrans.account = chartmaster.accountcode
                
                LEFT JOIN supptrans ON gltrans.type = supptrans.type and gltrans.typeno = supptrans.transno
                LEFT JOIN suppliers ON supptrans.supplierno=suppliers.supplierid 
                WHERE 1=1 AND gltrans.narrative NOT LIKE '%POLIZA DE APERTURA%' ";
                /*if(!is_null($datos['Cuentas1'])){
                   // $SQL.=" AND gltrans.account='".$datos['Cuentas1']."'";

                }*/
                // $SQL.=" AND gltrans.account IN ('1.1.1.2', '1.1.2.1') ";
                // $SQL.=" AND gltrans.tag='".$datos['urSel']."' ";
                // $SQL.=" AND gltrans.ln_ue='".$datos['selectUnidadEjecutora']."' ";

                if (!empty($_POST['urSel'])) {

                    $ids = str_replace(",","','",$_POST['urSel']);
                    $SQL.=" AND gltrans.tag IN ( '".$ids."' )";
                }
                
                if (!empty($_POST['selectUnidadEjecutora'])) {

                    $ids = join("','",$_POST['selectUnidadEjecutora']);
                    $SQL.=" AND  gltrans.ln_ue  IN ( '".$ids."' )";
                    $SQL.=" AND  ( `chartmaster`.`ln_clave` IN ( '".$ids."' ) OR LENGTH(`chartmaster`.`ln_clave`) < 2 OR `chartmaster`.`ln_clave` LIKE '%.%' )";
                }

                if (empty($datos['cuentaHasta']) or $datos['cuentaHasta'] =="") {
                    $SQL.=" AND gltrans.account = '".$datos['cuentaDesde']."'";
                } else {
                    $SQL.=" AND gltrans.account between '".$datos['cuentaDesde']."' AND '".$datos['cuentaHasta']."'";
                }
                
                $SQL.=" AND gltrans.trandate >=  STR_TO_DATE('" . $dateDesde . "', '%Y-%m-%d')
                AND gltrans.trandate <= STR_TO_DATE('"  . $dateHasta . "', '%Y-%m-%d') ";
            
                //and supptrans.trandate >=  STR_TO_DATE('" . $dateDesde . "', '%Y-%m-%d')";
            
                $SQL.=" ORDER BY gltrans.account, gltrans.counterindex DESC";
                 // echo "<pre>".$SQL;
                 // exit();

                
                $TransResult = DB_query($SQL, $db);
                DB_Txn_Commit($db);
                $info=array();
            
                while ($myrow = DB_fetch_array($TransResult)) {
                    $abonoAux="";
                    $cargoAux="";
                    
                    if ($myrow['amount']>0) {
                        $cargoAux=$myrow['amount'];
                    } else {
                        $abonoAux=$myrow['amount']*-1;
                    }
                    $info[] = array( "cuenta"=>trim($myrow['account'])." ".trim($myrow['accountname']),
                                "fecha"=>$myrow['trandate'],
                                "concepto"=>$myrow['narrativeOrig'],
                                "tipo"=>$myrow['nombreVisual'],
                                "trans"=>$myrow['typeno'],
                                "cheque"=>$myrow['chequeno'],
                                "cargo"=>$cargoAux,
                                "abono"=> $abonoAux,
                                "account"=>trim($myrow['account']),
                                "tipoMovimiento"=> $myrow['type'],
                                "amount"=> $myrow['amount']
                            );
                }

                $funcion = 503;
                $nombre=traeNombreFuncionGeneral($funcion, $db);
                $nombreExcel = $nombre.'_'.date('dmY');
               
                $contenido = array('datos' => $info,'nombreExcel' => $nombreExcel);
                $result = true;
            } catch (Exception $e) {
                $ErrorMsg= $e->getMessage();
                DB_Txn_Rollback($db);

                $contenido = $ErrorMsg;
                $result = true;
            }

                //        [selectUnidadEjecutora] => 05
                // [Cuentas1] => 1.1.1
                // [cuentaFrom] => 1.1.4.5
                // [cuentaTo] => 1.2.1.3.9
                // [tipos] => 3
                // [tiposPoliza] => 3
                // [dateDesde] => 27-04-2018
                // [dateHasta] => 30-04-2018
                // [proceso] => buscar
                // [urSel] => I6L
                
            
            break;
        case 'cuentasaldoinicial':
            $dateDesde= date("Y-m-d", strtotime($_POST['dateDesde']));
            $dateHasta= date("Y-m-d", strtotime($_POST['dateHasta']));

            $sql="SELECT account,ifnull(sum(amount),0)  AS saldoInicial 
                    FROM gltrans 
                    WHERE trandate < '".$dateDesde."'";
            
            if (!empty($_POST['selectUnidadNegocio']) or $_POST['selectUnidadNegocio']!='-1') {
                $sql.=" AND tag = '".$_POST['selectUnidadNegocio']."'";
            }
            
            if (!empty($_POST['selectUnidadEjecutora']) or $_POST['selectUnidadEjecutora']!='-1') {
                $sql.=" AND ln_ue = '".$_POST['selectUnidadEjecutora']."'";
            }

            $sql.=" AND account IN ";

            $sql.="(SELECT gltrans.account
                                        FROM gltrans 
                                        WHERE 1=1 AND gltrans.narrative NOT LIKE '%POLIZA DE APERTURA%' ";

            if (!empty($_POST['selectUnidadNegocio']) or $_POST['selectUnidadNegocio']!='-1') {
                $sql.=" AND tag = '".$_POST['selectUnidadNegocio']."'";
            }

            if (!empty($_POST['selectUnidadEjecutora']) or $_POST['selectUnidadEjecutora']!='-1') {

                $sql.=" AND ln_ue = '".$_POST['selectUnidadEjecutora']."'";
            }

            if (empty($_POST['cuentaHasta']) or $_POST['cuentaHasta'] =="") {
                $sql.=" AND gltrans.account = '".$_POST['cuentaDesde']."'";
            } else {
                $sql.=" AND gltrans.account between '".$_POST['cuentaDesde']."' AND '".$_POST['cuentaHasta']."'";
            }

            $sql.=" AND gltrans.trandate >=  '".$dateDesde."' AND gltrans.trandate <= '".$dateHasta."' 
            GROUP BY gltrans.account 
            ORDER BY gltrans.account, gltrans.counterindex DESC)";

            $sql.=" GROUP BY gltrans.account;";

            // $sql="SELECT account,ifnull(sum(amount),0)  AS saldoInicial
            //         FROM gltrans
            //         WHERE trandate < '2018-05-23' AND tag ='I6L' and ln_ue='09'
            //         and account in (SELECT gltrans.account
            //                             FROM gltrans
            //                             WHERE 1=1 AND gltrans.narrative NOT LIKE '%POLIZA DE APERTURA%' AND gltrans.tag='I6L' AND gltrans.ln_ue='09' AND gltrans.account between '1'
            //                             and '9.2.1.1.1.09.0001.0001' AND gltrans.trandate >=  '2018-05-23' AND gltrans.trandate <= '2018-06-05'
            //                             GROUP BY gltrans.account
            //                             ORDER BY gltrans.account, gltrans.counterindex DESC)
            //         GROUP BY gltrans.account;";

            

            $TransResult = DB_query($sql, $db);
            $info=array();

            while ($myrow = DB_fetch_array($TransResult)) {
                $info[] = array(
                    "cuenta"=> $myrow['account'],
                    "saldoInicial"=> $myrow['saldoInicial']
                );
            }

            $contenido = array('datos' => $info,'nombreExcel' => $nombreExcel);
            $result = true;
            break;
        case 'saldoInicialAcumulado':
            $dateDesde= date("Y-m-d", strtotime($_POST['dateDesde']));
            $dateHasta= date("Y-m-d", strtotime($_POST['dateHasta']));

            $sql="SELECT ifnull(sum(amount),0)  AS saldoInicial 
                    FROM gltrans 
                    WHERE trandate < '".$dateDesde."'";
            
            if (!empty($_POST['selectUnidadNegocio']) or $_POST['selectUnidadNegocio']!='-1') {
                $sql.=" AND tag = '".$_POST['selectUnidadNegocio']."'";
            }
            
            if (!empty($_POST['selectUnidadEjecutora']) or $_POST['selectUnidadEjecutora']!='-1') {
                $sql.=" AND ln_ue = '".$_POST['selectUnidadEjecutora']."'";
            }

            $TransResult = DB_query($sql, $db);
            $info=array();

            while ($myrow = DB_fetch_array($TransResult)) {
                $info[] = array(
                    "saldoInicialAcumulado"=> $myrow['saldoInicial']
                );
            }

            $contenido = array('datos' => $info,'nombreExcel' => $nombreExcel);
            $result = true;
            break;
        default:
            # code...
            break;
    }
}
$dataObj = array('info' =>'', 'contenido' => $contenido, 'result' => $result);
echo json_encode($dataObj);
