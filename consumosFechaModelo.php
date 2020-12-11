<?php

/**
 * Modelo para consumosAUnafecha
 *
 * @category     consumosAUnafecha
 * @package      ap_grp
 * @author       Arturo Lopez Peña <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 12/10/2017
 * Fecha Modificación: 15/10/2017
 */
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

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
$funcion=2373;
require $PathPrefix.'includes/SecurityFunctions.inc';
require $PathPrefix.'includes/SQL_CommonFunctions.inc';
require $PathPrefix . 'includes/DateFunctions.inc';
header('Content-type: text/html; charset=ISO-8859-1');
$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);
$data=array();


function getPrueba()
{
    $arrayName = array('Var' => "Dentro del servidor");
    $data=array('data'=>$arrayName);
    return  $data;
}
function fnGetPartidas($db)
{
  
    $datos = array([ 'label'=>'Seleccionar', 'title'=>'Seleccionar', 'value'=>'-1'
            ]);
    DB_Txn_Begin($db);
    try {
        $SQL="SELECT DISTINCT 
                tb_partida_articulo.partidaEspecifica AS partida  
                FROM  tb_partida_articulo 
                INNER JOIN stockmaster ON  tb_partida_articulo.eq_stockid=stockmaster.eq_stockid 
                WHERE partidaEspecifica NOT LIKE '5%'
                AND stockmaster.mbflag='B'
                ORDER BY partidaEspecifica ";
        $TransResult = DB_query($SQL, $db);

        while ($myrow = DB_fetch_array($TransResult)) {
            $datos[] = [
               'label'=>$myrow ['partida'],
               'title'=>$myrow ['partida'] ,
               'value'=>$myrow ['partida']
            ];
            //array( 'value' => $myrow ['partida'], 'texto' => $myrow ['partida'] );
        }
    } catch (Exception $e) {
                    $ErrorMsg= $e->getMessage();
                    DB_Txn_Rollback($db);
    }
     
        return $datos;
}
function fnGetPartidasSer($db)
{
    $datos = array([ 'label'=>'Seleccionar', 'title'=>'Seleccionar', 'value'=>'-1'
            ]);
    DB_Txn_Begin($db);
    try {
        $SQL="SELECT DISTINCT 
                tb_partida_articulo.partidaEspecifica AS partida  
                FROM  tb_partida_articulo 
                INNER JOIN stockmaster ON  tb_partida_articulo.eq_stockid=stockmaster.eq_stockid 
                WHERE partidaEspecifica NOT LIKE '5%'
                AND stockmaster.mbflag='D'
                ORDER BY partidaEspecifica ";
        $TransResult = DB_query($SQL, $db);

        while ($myrow = DB_fetch_array($TransResult)) {
            $datos[] = [
               'label'=>$myrow ['partida'],
               'title'=>$myrow ['partida'] ,
               'value'=>$myrow ['partida']
            ];
            //array( 'value' => $myrow ['partida'], 'texto' => $myrow ['partida'] );
        }
    } catch (Exception $e) {
                    $ErrorMsg= $e->getMessage();
                    DB_Txn_Rollback($db);
    }
     
        return $datos;
}
function fnCambioPartida($db, $partida)
{
        $datos=  array();
        $clave = array([ 'label'=>'Seleccionar', 'title'=>'Seleccionar', 'value'=>'-1'
            ]);
         $des = array([ 'label'=>'Seleccionar', 'title'=>'Seleccionar', 'value'=>'-1'
            ]);
        $datos=array();
        $units=array();
    
      
        $SQL="SELECT DISTINCT 
                tb_partida_articulo.partidaEspecifica AS partida,
                stockmaster.stockid,
                stockmaster.description,
                stockmaster.units                
                FROM  tb_partida_articulo 
                INNER JOIN stockmaster ON  tb_partida_articulo.eq_stockid=stockmaster.eq_stockid 
                WHERE stockmaster.mbflag='B'
                AND tb_partida_articulo.partidaEspecifica='".$partida."'";

        $TransResult = DB_query($SQL, $db);

    while ($myrow = DB_fetch_array($TransResult)) {
        $clave[] = [
                'label'=>$myrow ['stockid'],
                'title'=>$myrow ['stockid'] ,
                'value'=>$myrow ['stockid']
            ]; //array( 'value' => $myrow ['stockid'], 'texto' => $myrow ['stockid'] );
        $des[]   = [
                'label'=>$myrow ['description'],
                'title'=>$myrow ['description'] ,
                'value'=>$myrow ['stockid']
            ];

        //array( 'value' => $myrow ['stockid'], 'texto' => $myrow ['description'] );
        $units[] = array( 'value' => $myrow ['stockid'], 'texto' => $myrow ['units'] );
    }
       
       
         $datos = array('clave' =>$clave,'des' => $des,'um'=>$units);

         return $datos;
}
function fnCambioPartidaSer($db, $partida)
{
        $datos=  array();
        $clave = array([ 'label'=>'Seleccionar', 'title'=>'Seleccionar', 'value'=>'-1'
            ]);
         $des = array([ 'label'=>'Seleccionar', 'title'=>'Seleccionar', 'value'=>'-1'
            ]);
        $datos=array();
        $units=array();
    
      
        $SQL="SELECT DISTINCT 
                tb_partida_articulo.partidaEspecifica AS partida,
                stockmaster.stockid,
                stockmaster.description,
                stockmaster.units                
                FROM  tb_partida_articulo 
                INNER JOIN stockmaster ON  tb_partida_articulo.eq_stockid=stockmaster.eq_stockid 
                WHERE stockmaster.mbflag='D'
                AND tb_partida_articulo.partidaEspecifica='".$partida."'";

        $TransResult = DB_query($SQL, $db);

    while ($myrow = DB_fetch_array($TransResult)) {
        $clave[] = [
                'label'=>$myrow ['stockid'],
                'title'=>$myrow ['stockid'] ,
                'value'=>$myrow ['stockid']
            ]; //array( 'value' => $myrow ['stockid'], 'texto' => $myrow ['stockid'] );
        $des[]   = [
                'label'=>$myrow ['description'],
                'title'=>$myrow ['description'] ,
                'value'=>$myrow ['stockid']
            ];

        //array( 'value' => $myrow ['stockid'], 'texto' => $myrow ['description'] );
        $units[] = array( 'value' => $myrow ['stockid'], 'texto' => $myrow ['units'] );
    }
       
       
         $datos = array('clave' =>$clave,'des' => $des,'um'=>$units);

         return $datos;
}
function getDatosForm()
{
    $datos=array();
    foreach ($_POST as $ad => $val) {
        if ((!empty($val))&&(!is_null($val))) {
            if ($ad!='get') {
                $val=trim($val);
                $val=str_replace("'", "", $val);
                $datos[$ad]=$val;
            }
        } else {
            $datos[$ad]=null;
        }
    }// fin foreach

    return $datos;
}

function getDatosFormS()
{
    $datos=array();
    parse_str($_POST['valores'], $datos);

    return $datos;
}
function fnBuscarMeses($buscar, $articulos, $cantidad, $meses)
{
    $meses1= array('01' =>0,'02'=>0,'03'=>0,'04'=>0,'05'=>0,'06'=>0,'07'=>0,'08'=>0,'09'=>0,'10'=>0,'11'=>0,'12'=>0 );
    $meses2= array('ene' =>0,'feb'=>0,'mar'=>0,'abr'=>0,'may'=>0,'jun'=>0,'jul'=>0,'ago'=>0,'sep'=>0,'oct'=>0,'nov'=>0,'dic'=>0 );

    for ($a=0; $a<count($articulos); $a++) {
        if ($buscar==$articulos[$a]) {
            $mes=$meses[$a];
            $mes=explode("-", $mes);
            $mes=$mes[1];

        switch($mes){

        case '01':
        $mes='ene';
        break;
        
        case '02':
        $mes='feb';
        break;
        
        case '03':
        $mes='mar';
        break;

        case '04':
        $mes='abr';
        break;

        case '05':
        $mes='may';
        break;

        case '06':
        $mes='jun';
        break;
        
        case '07':
        $mes='jul';
        break;

        case '08':
        $mes='ago';
        break;

        case '09':
        $mes='sep';
        break;
        
        case '10':
        $mes='oct';
        break;

        case '11':
        $mes='nov';
        break;

        case '12':
        $mes='dic';
        break;
        
        
    }


            $meses2[$mes]=$cantidad[$a];
        }
    }

    return $meses2;
}
function fnFiltrado($db, $data)
{
    $data1=array();
    $ultimoCosto=0;
    $condicion='';
    $condicionSer ='';
    $datos = array();
    //DB_Txn_Begin($db);

    try {
        //print_r($data);
        if ($data['selectUnidadEjecutora']!="-1") {
            $condicion.=" AND stockmoves.ln_ue='".$data['selectUnidadEjecutora'][0]."'";
            $condicionSer.=" AND supptrans.ln_ue='".$data['selectUnidadEjecutora'][0]."'";
        }


        if (!empty($data['dateDesde2'])) {
            $dateDesde= date("Y-m-d", strtotime($data['dateDesde2']));
        } else {
            $dateDesde=0;
        }

        if (!empty($data['dateHasta'])) {
            $dateHasta= date("Y-m-d", strtotime($data['dateHasta']));
        } else {
            $dateHasta=0;
        }
        if($dateDesde!=0 && $dateHasta!=0){
         
        $condicion .="AND stockmoves.trandate  >='" . $dateDesde . " 00:00:00' 
           AND stockmoves.trandate <='" . $dateHasta . " 23:59:59'";

        $condicionSer.="AND supptrans.trandate  >='" . $dateDesde . " 00:00:00' 
           AND supptrans.trandate <='" . $dateHasta . " 23:59:59'";

        }else if($dateDesde!=0 && $dateHasta==0 ){
             $condicion .=" AND stockmoves.trandate >='" . $dateDesde . " 00:00:00'";
             $condicionSer .=" AND supptrans.trandate >='" . $dateDesde . " 00:00:00'";

        }else if($dateDesde==0 && $dateHasta!=0 ){
            $condicion .=" AND stockmoves.trandate <='" . $dateHasta . " 23:59:59'";
            $condicionSer.=" AND supptrans.trandate <='" . $dateHasta . " 23:59:59'";


        }


         $SQLmeses="SELECT  tb_partida_articulo.partidaEspecifica,
                    stockmaster.stockid,SUM(stockmoves.qty)as cantidad,

                    DATE_FORMAT(stockmoves.trandate,\"%Y-%m\") as mes
                    FROM stockmoves
                    INNER JOIN stockmaster ON stockmaster.stockid=stockmoves.stockid
                    INNER JOIN tb_partida_articulo ON tb_partida_articulo.eq_stockid=stockmaster.eq_stockid
                    WHERE type='1001' ".$condicion ."
                     AND stockmoves.tagref='".$data['selectUnidadNegocio2']."' 
                     AND SUBSTRING(tb_partida_articulo.partidaEspecifica,1,1)='2'
                    GROUP BY tb_partida_articulo.partidaEspecifica,stockmaster.stockid,mes ASC;";
                               
         $TransResultmeses = DB_query($SQLmeses, $db);

         $articulos=array();
         $cantidad=array();
         $meses=array();
        while ($myrow1 = DB_fetch_array($TransResultmeses)) {
            $articulos[]=$myrow1['stockid'];
            $cantidad[]=$myrow1['cantidad']*-1;
            $meses[]=$myrow1['mes'];
        }
       // print_r($SQLmeses);
        $SQL="SELECT  tb_partida_articulo.partidaEspecifica,
                stockmaster.stockid,stockmaster.description,stockmoves.ln_ue,SUM(stockmoves.qty)as cantidad,
                (SELECT stdcostunit  FROM grns WHERE itemcode=stockmaster.stockid order by grnno DESC ,deliverydate DESC  limit 1 ) as ultimoCostoGrns,
                (SELECT lastcost  FROM stockcostsxlegal WHERE stockid=stockmaster.stockid order by id DESC limit 1 ) as ultimoCostoStock,
                stockmaster.units,
                stockmoves.tagref
                FROM stockmoves
                INNER JOIN stockmaster ON stockmaster.stockid=stockmoves.stockid
                INNER JOIN tb_partida_articulo ON tb_partida_articulo.eq_stockid=stockmaster.eq_stockid
                WHERE type='1001'
                AND stockmoves.tagref='".$data['selectUnidadNegocio2']."' 
                AND SUBSTRING(tb_partida_articulo.partidaEspecifica,1,1)='2'".$condicion ." GROUP BY tb_partida_articulo.partidaEspecifica,stockmaster.stockid ASC";
                      
        $TransResult = DB_query($SQL, $db);
         // print_r($SQL);
                       
         //print_r($meses);

        while ($myrow = DB_fetch_array($TransResult)) {
            if (!is_null($myrow['ultimoCostoGrns'])) {
                $ultimoCosto=$myrow['ultimoCostoGrns'];
            } else {
                $ultimoCosto=$myrow['ultimoCostoStock'];
            }
            $meses1=fnBuscarMeses($myrow['stockid'], $articulos, $cantidad, $meses);
            $datos[] = array(
               'ur'=>$myrow['tagref'],
               'ue'=>$myrow['ln_ue'],
               'partida' =>$myrow['partidaEspecifica'],
               'id'=>$myrow['stockid'],
               'des'=>$myrow['description'],
                                
               //'fecha'=>date("d-m-Y", strtotime($myrow['trandate'])),
               'unidad'=>$myrow['units'],
               'ultimoCosto'=>$ultimoCosto,
               'cantidad'=>(($myrow['cantidad']<0) ? ($myrow['cantidad']*-1) : ($myrow['cantidad'])),
               'meses'=>$meses1
            );
        }
        $funcion = 2291;
        $nombre='Almacen'; //traeNombreFuncionGeneral($funcion, $db);
        $nombre=str_replace(" ", "_", $nombre);
        $nombreExcel = $nombre.'_'.date('dmY');



        // $sqlServicioMeses="
        //                 SELECT tb_partida_articulo.partidaEspecifica,stockmaster.stockid ,
        //                 stockmaster.description,SUM(supptransdetails.qty) as cantidad,
        //                 (SELECT stdcostunit  FROM grns WHERE itemcode=stockmaster.stockid order by grnno DESC ,deliverydate DESC  limit 1 ) as ultimoCostoGrns,
        //                 (SELECT lastcost  FROM stockcostsxlegal WHERE stockid=stockmaster.stockid order by id DESC limit 1 ) as ultimoCostoStock,
        //                 /*(SELECT trandate FROM supptrans WHERE id=supptransdetails.supptransid) as trandatef */
        //                 DATE_FORMAT((SELECT trandate FROM supptrans WHERE id=supptransdetails.supptransid) ,\"%Y-%m\") as mes
        //                 FROM supptransdetails
        //                 INNER JOIN stockmaster  ON supptransdetails.stockid= stockmaster.stockid
        //                 INNER JOIN tb_partida_articulo ON tb_partida_articulo.eq_stockid=stockmaster.eq_stockid
        //                 LEFT JOIN stockmoves ON stockmoves.stockid=supptransdetails.stockid
        //                 WHERE type='25'
        //                 AND SUBSTRING(tb_partida_articulo.partidaEspecifica,1,1)='3'
        //                 GROUP BY tb_partida_articulo.partidaEspecifica,stockmaster.stockid,stockmaster.stockid,mes  ASC";
        $sqlServicioMeses="SELECT supptransdetails.stockid, tb_partida_articulo.partidaEspecifica, stockmaster.description, 
                SUM(supptransdetails.price), SUM(supptransdetails.qty) AS cantidad, 
                (SELECT stdcostunit FROM grns WHERE itemcode=supptransdetails.stockid ORDER BY grnno DESC LIMIT 1) AS ultimoCostoGrns,
                (SELECT lastcost FROM stockcostsxlegal WHERE stockid=stockmaster.stockid ORDER BY id DESC LIMIT 1 ) AS ultimoCostoStock,
                (SELECT ln_ue FROM supptrans where id=supptransid) as ue,
                (SELECT tagref FROM supptrans where id=supptransid) as ur,
                DATE_FORMAT((SELECT trandate FROM supptrans WHERE id=supptransdetails.supptransid) ,\"%Y-%m\") as mes
                FROM supptransdetails 
                INNER JOIN supptrans ON supptransdetails.supptransid =supptrans.id
                INNER JOIN stockmaster  ON supptransdetails.stockid= stockmaster.stockid
                INNER JOIN (SELECT DISTINCT eq_stockid, partidaEspecifica FROM tb_partida_articulo WHERE SUBSTRING(tb_partida_articulo.partidaEspecifica,1,1)='3') AS tb_partida_articulo ON stockmaster.eq_stockid = tb_partida_articulo.eq_stockid
                WHERE SUBSTRING(tb_partida_articulo.partidaEspecifica,1,1)='3'
                ".$condicionSer."
                GROUP BY supptransdetails.stockid, tb_partida_articulo.partidaEspecifica, stockmaster.description,mes";
               // print_r($sqlServicioMeses);
         $resultServicioMeses = DB_query($sqlServicioMeses, $db);
         $articulos1=array();
         $cantidad1=array();
         $meses2=array();

        while ($myrow3 = DB_fetch_array($resultServicioMeses)) {
            $articulos[]=$myrow3['stockid'];
            $cantidad[]=$myrow3['cantidad']*-1;
            $meses2[]=$myrow3['mes'];
        }


        $datos2=array();
        // $SQLser="SELECT tb_partida_articulo.partidaEspecifica,stockmaster.stockid ,
        //         stockmaster.description,SUM(supptransdetails.qty) as cantidad,
        //         (SELECT stdcostunit  FROM grns WHERE itemcode=stockmaster.stockid order by grnno DESC ,deliverydate DESC  limit 1 ) as ultimoCostoGrns,
        //         (SELECT lastcost  FROM stockcostsxlegal WHERE stockid=stockmaster.stockid order by id DESC limit 1 ) as ultimoCostoStock

        //         FROM supptransdetails
        //         INNER JOIN stockmaster  ON supptransdetails.stockid= stockmaster.stockid
        //         INNER JOIN tb_partida_articulo ON tb_partida_articulo.eq_stockid=stockmaster.eq_stockid
        //         LEFT JOIN stockmoves ON stockmoves.stockid=supptransdetails.stockid
        //         WHERE type='25'
        //         AND SUBSTRING(tb_partida_articulo.partidaEspecifica,1,1)='3'
        //         GROUP BY tb_partida_articulo.partidaEspecifica,stockmaster.stockid,stockmaster.stockid ASC;";
        $SQLser="SELECT supptransdetails.stockid, tb_partida_articulo.partidaEspecifica, stockmaster.description, 
                SUM(supptransdetails.price), SUM(supptransdetails.qty) AS cantidad, 
                (SELECT stdcostunit FROM grns WHERE itemcode=supptransdetails.stockid ORDER BY grnno DESC LIMIT 1) AS ultimoCostoGrns,
                (SELECT lastcost FROM stockcostsxlegal WHERE stockid=stockmaster.stockid ORDER BY id DESC LIMIT 1 ) AS ultimoCostoStock,
                (SELECT ln_ue FROM supptrans where id=supptransid) as ue,
                (SELECT tagref FROM supptrans where id=supptransid) as ur
                FROM supptransdetails 
                INNER JOIN supptrans ON supptransdetails.supptransid =supptrans.id
                INNER JOIN stockmaster  ON supptransdetails.stockid= stockmaster.stockid
                INNER JOIN (SELECT DISTINCT eq_stockid, partidaEspecifica FROM tb_partida_articulo WHERE SUBSTRING(tb_partida_articulo.partidaEspecifica,1,1)='3') AS tb_partida_articulo ON stockmaster.eq_stockid = tb_partida_articulo.eq_stockid
                WHERE SUBSTRING(tb_partida_articulo.partidaEspecifica,1,1)='3' ".$condicionSer."
                GROUP BY supptransdetails.stockid, tb_partida_articulo.partidaEspecifica, stockmaster.description";
               // print_r($SQLser);
        $TransResultser = DB_query($SQLser, $db);

        while ($myrow2 = DB_fetch_array($TransResultser)) {
            if (!is_null($myrow2['ultimoCostoGrns'])) {
                $ultimoCosto=$myrow2['ultimoCostoGrns'];
            } else {
                $ultimoCosto=$myrow2['ultimoCostoStock'];
            }
            $meses3=fnBuscarMeses($myrow['stockid'], $articulos1, $cantidad1, $meses2);
            $datos2[] = array(
               'partida' =>$myrow2['partidaEspecifica'],
               'id'=>$myrow2['stockid'],
               'des'=>$myrow2['description'],
               'ue'=>$myrow2['ue'],
               'ur'=>$myrow2['ur'],
               //'fecha'=>date("d-m-Y", strtotime($myrow['trandate'])),
               //'unidad'=>$myrow['units'],
               'ultimoCosto'=>$ultimoCosto,
               'cantidad'=>(($myrow2['cantidad']<0) ? ($myrow2['cantidad']*-1) : ($myrow2['cantidad'])),
               'meses'=>$meses3
            );
        }


        $data1 = array('data' => $datos,'nombreExcel' => $nombreExcel,'data2'=>$datos2);
        //print_r($datos1);
    } catch (Exception $e) {
        $ErrorMsg= $e->getMessage();
        //DB_Txn_Rollback($db);
    }
    return  $data1;
}
function fnGuardarEscenario($db,$data){
    $transno = GetNextTransNo('285', $db);
    $ultimo= end($data);
    $dateDesde= date("Y-m-d H:i:s", strtotime($ultimo['dateDesde']));
    $dateHasta= date("Y-m-d H:i:s", strtotime($ultimo['dateHasta']));
               //"','".$_SESSION ['UserID']."'"
    DB_Txn_Begin($db);

    try {
        
        $SQL = "INSERT INTO tb_cat_esenario_paaas(id_nu_folio_esenario,id_nu_ur,id_nu_ue,dtm_fecha_inicio,dtm_fecha_termino,ind_activo) 
            VALUES ('". $transno ."','".$ultimo['ur']."','".$ultimo['ue']."','".$dateDesde."','".$dateHasta."','1')";

        $TransResult = DB_query($SQL, $db);

        if($TransResult == true){
            DB_Txn_Commit($db);
          
        }else{
            DB_Txn_Rollback($db);
        }
   
        array_pop($data);
        
        $valoresInsertar='';
       
   
        foreach ($data as $ad) {
             $valoresFila="('".$transno."','".$ultimo['programa']."',";
            foreach ($ad as $ya ) {
             
                $valoresFila.="'".$ya."',";

            }
                
            $valoresFila=substr($valoresFila, 0, -1);
            $valoresFila.=")";
            $valoresInsertar.=$valoresFila.",";
            $valoresFila='';

        }
       
         $valoresInsertar=substr($valoresInsertar, 0, -1);
      

         $SQL = "INSERT INTO tb_cat_esenario_detalle(id_nu_esenario_paaas,id_nu_programa,
         id_nu_partida,id_nu_clave,amt_total,amt_ultimo_costo,ind_enero,ind_febrero,ind_marzo,ind_abril,ind_mayo,ind_junio,ind_julio,ind_agosto,ind_septiembre,ind_octubre,ind_noviembre,ind_diciembre,sn_tipo) VALUES ".$valoresInsertar."";

        $TransResult2 = DB_query($SQL, $db);


        if($TransResult2 == true){
            DB_Txn_Commit($db);
          
        }else{
            DB_Txn_Rollback($db);
        }


    } catch (Exception $e) {
                    $ErrorMsg= $e->getMessage();
                    DB_Txn_Rollback($db);
    }
return 'Se guardo con éxito con folio: <b>'. $transno."</b>";
  

}
function fnAccionesForm($db, $accion, $data)
{
    
    $data1=array();

    if (!empty($accion)) {
        switch ($accion) {
            case 'filtrado':
                  $data1=  fnFiltrado($db, $data);

                break;
           

            default:
                # code...
                break;
        }
    }

    return $data1;
}
function fnControladorGet($db, $get)
{
    //faltar incluir  acciones de seguridad
    $data='';
    $dataAux=array();
    switch ($get) {
        case 'formulario':
            $accion=$_POST['accion'];
            $dataAux=getDatosFormS();

            if (isset($accion)) {
                $data=fnAccionesForm($db, $accion, $dataAux);
            }

            break;
        case 'prueba':
                $data=getPrueba();
            break;
        case 'getPartidas':
                $data=fnGetPartidas($db);
            break;
        case 'cambioPartida':
                $data=fnCambioPartida($db, $_POST['datos']);
            break;
        case 'getPartidasSer':
                $data=fnGetPartidasSer($db);
            break;
        case 'cambioPartidaSer':
                $data=fnCambioPartidaSer($db, $_POST['datos']);
            break;
        case 'GuardarDatos':
               if (isset($_POST['datos'])){
                
                 $data=fnGuardarEscenario($db,$_POST['datos']);
               }
            break;

        default:
            # code...
            break;
    }
    return $data;
}


if (isset($_POST['getData']) && (!empty($_POST['getData']))&& (!is_null($_POST['getData']))) {
    $get=trim($_POST['getData']);
    $data=fnControladorGet($db, $get);

    /* MODIFICACION DE HEADER */
    //header('Content-type:application/json;charset=utf-8');// para  usar este necesito utf8_encode() para el regreso de  datos  y quitar el header de arriba
    /* ENVIO DE INFORMACIÓN */

    echo json_encode($data);
} else {
    echo json_encode('Falla al conectarse con el servidor');
}
