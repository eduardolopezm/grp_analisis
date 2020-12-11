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
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();

require $PathPrefix . 'config.php';
require $PathPrefix . 'includes/ConnectDB.inc';

$funcion=2373;
require $PathPrefix.'includes/SecurityFunctions.inc';
require $PathPrefix.'includes/SQL_CommonFunctions.inc';
require $PathPrefix . 'includes/DateFunctions.inc';
header('Content-type: text/html; charset=ISO-8859-1');
$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);
$data=array();



function fnGetPartidasLista($db){
    
    $retorno = array();
    $assets=array();
    $services=array();
    $unicasSer=array();
    $unicasBien=array();
    //  get partidas  for assets 
    DB_Txn_Begin($db);
    try {
        $SQL="SELECT DISTINCT 
                tb_partida_articulo.partidaEspecifica AS partida,
                stockmaster.stockid,
                stockmaster.description,
                stockmaster.units  
                FROM  tb_partida_articulo 
                INNER JOIN stockmaster ON  tb_partida_articulo.eq_stockid=stockmaster.eq_stockid 
                WHERE partidaEspecifica NOT LIKE '5%'
                AND stockmaster.mbflag='B'
                ORDER BY partidaEspecifica ";
        $TransResult = DB_query($SQL, $db);
        DB_Txn_Commit($db);
        while ($myrow = DB_fetch_array($TransResult)) {
            $assets[] = [
               'partida'=>$myrow ['partida'],
               'clave'=>$myrow ['stockid'] ,
               'descrip'=>$myrow ['description'],
               'unidad'=>$myrow ['units'],
                
            ];
         
        }
    } catch (Exception $e) {
                    $ErrorMsg= $e->getMessage();
                    DB_Txn_Rollback($db);
    }


    //  get partidas  for services 
    DB_Txn_Begin($db);
    try {
        $SQL="SELECT DISTINCT 
                tb_partida_articulo.partidaEspecifica AS partida,
                stockmaster.stockid,
                stockmaster.description,
                stockmaster.units  
                FROM  tb_partida_articulo 
                INNER JOIN stockmaster ON  tb_partida_articulo.eq_stockid=stockmaster.eq_stockid 
                WHERE partidaEspecifica NOT LIKE '5%'
                AND stockmaster.mbflag='D'
                ORDER BY partidaEspecifica ";
        $TransResult = DB_query($SQL, $db);
        DB_Txn_Commit($db);
        while ($myrow = DB_fetch_array($TransResult)) {
            $services[] = [
               'partida'=>$myrow ['partida'],
               'clave'=>$myrow ['stockid'] ,
               'descrip'=>$myrow ['description'],
               'unidad'=>$myrow ['units'],
                
            ];
         
        }
    } catch (Exception $e) {
                    $ErrorMsg= $e->getMessage();
                    DB_Txn_Rollback($db);
    }
    // get partidas  servicios
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
        DB_Txn_Commit($db);
        while ($myrow = DB_fetch_array($TransResult)) {
            $unicasBien[] = $myrow ['partida'];
             
           
         
        }
    } catch (Exception $e) {
                    $ErrorMsg= $e->getMessage();
                    DB_Txn_Rollback($db);
    }
    // get partidas  unicas  ser
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
        DB_Txn_Commit($db);
        while ($myrow = DB_fetch_array($TransResult)) {
            $unicasSer[] = $myrow ['partida'];
            
         
        }
    } catch (Exception $e) {
                    $ErrorMsg= $e->getMessage();
                    DB_Txn_Rollback($db);
    }
    $datos[]=['assets'=>$assets];
    $datos[]=['services'=>$services];
    $datos[]=['unicasBien'=>$unicasBien];
    $datos[]=['unicasSer'=>$unicasSer];
    
    
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


function fnGuardarEscenario($db,$data){
          
    //DB_Txn_Begin($db);
    try {
        
     $valoresInsertar='';
     $end= end($data); // get the laste element from array
     $folio=0;
     $sqlfindfolio="  SELECT id_nu_esenario_paaas FROM tb_cat_esenario_paaas WHERE id_nu_folio_esenario='".$end['folio']."';";
     $res = DB_query($sqlfindfolio, $db);
     while ($rows = DB_fetch_array($res)) {
        $folio=$rows['id_nu_esenario_paaas'];
      }
        $SQL="DELETE FROM tb_cat_esenario_detalle WHERE id_nu_esenario_paaas='".$folio."'";
        $res = DB_query($SQL, $db);
        array_pop($data); // deleted the last element from array
        foreach ($data as $ad) {
            $valoresFila="('".$folio."',"; //'".$ultimo['programa']."',";
            $i=0;
            foreach ($ad as $ya ) {
              //date("Y-m-d H:i:s", strtotime($ultimo['dateDesde']));
              if($i==19){
              $x= str_replace("-", "/", $ya);
              $date=date("Y-m-d H:i:s", strtotime($x));
            
                  $valoresFila.="'".$date."',";
                }else{
                  
                  $valoresFila.="'".$ya."',";
                 
                }
                $i++;
                
            }
                
            $valoresFila=substr($valoresFila, 0, -1);
            $valoresFila.=")";
            $valoresInsertar.=$valoresFila.",";
            $valoresFila='';

        }
       
         $valoresInsertar=substr($valoresInsertar, 0, -1);
       
        

    } catch (Exception $e) {
                    $ErrorMsg= $e->getMessage();
                   // DB_Txn_Rollback($db);
    }


    DB_Txn_Begin($db);
    $flag=false;
    try {
        
        // $SQL = "INSERT INTO tb_cat_esenario_detalle(id_nu_esenario_paaas,id_nu_partida,id_nu_clave,amt_total,amt_ultimo_costo,ind_enero,ind_febrero,ind_marzo,ind_abril,ind_mayo,ind_junio,ind_julio,ind_agosto,ind_septiembre,ind_octubre,ind_noviembre,ind_diciembre,sn_origen,sn_tipo) VALUES ".$valoresInsertar."";
        
        $SQL="INSERT INTO  tb_cat_esenario_detalle
              (id_nu_esenario_paaas   
              ,nu_row        
              ,nu_clavecucop         
              ,nu_partida          
              ,nu_pef                 
              ,nu_valorestimado      
              ,nu_valormipymes        
              ,nu_valorenctlc        
              ,nu_cantidad            
              ,nu_unidadmedida        
              ,nu_caracterprocedimiento
              ,nu_entidadfederativa    
              ,nu_porcentaje1ertrim  
              ,nu_porcentaje2dotrim   
              ,nu_porcentaje3ertrim  
              ,nu_porcentaje4totrim       
              ,nu_plurianual          
              ,nu_aniosplurianuales   
              ,nu_valortotalplurianual
              ,nu_tipoprocedimiento
              ,dtm_fecharegistro
              ,sn_tipo
              ,nu_valor_iva) VALUES ".$valoresInsertar;
        
        $TransResult2 = DB_query($SQL, $db);
        //print_r($SQL);
            if($TransResult2 == true){
              $flag=true;
             DB_Txn_Commit($db);
            }else{
                DB_Txn_Rollback($db);
            }
        } catch (Exception $e) {

            $ErrorMsg= $e->getMessage();
            DB_Txn_Rollback($db);
        }
  $comentarios=str_replace(",", "|@", $end['obs']);
  $SQL="UPDATE  tb_cat_esenario_paaas SET ln_oficio='".$end['oficio']."', ln_comments='".$comentarios."' , nu_anio='".$end['anio']."' WHERE id_nu_folio_esenario='".$end['folio']."'";
  $res = DB_query($SQL, $db);

   $retorno[]=array('msg'=>'Se guardo con éxito','flag'=>$flag);
return   $retorno;
  

}
$partidasBudget=array();
function getDetailsScenePre($db,$data){
 $ur='';
 $ue='';
 $folio='2416';
 $alta='';
 $inicio='';
 $termino='';
 $comments='';
 $year='';
 $oficio='';
 $geo=array();
 $units=array();
 
 $assets=array();
 $services=array();

 $assetsDes=array();
 $servicesDes=array();
 $idScene=0;
 //$presupuestoPef=array();
 $SQL="SELECT id_nu_folio_esenario as folio,id_nu_ur as ur,id_nu_ue as ue,id_nu_esenario_paaas as id, dtm_fecha_alta as alta,DATE_FORMAT(dtm_fecha_inicio,\"%d-%m-%Y\") as inicio, DATE_FORMAT(dtm_fecha_termino,\"%d-%m-%Y\")  as termino,ln_comments as comments,nu_anio as year, ln_oficio as oficio FROM tb_cat_esenario_paaas WHERE id_nu_folio_esenario='".$data[0]."'";
 $res = DB_query($SQL, $db);

  while ($rows = DB_fetch_array($res)) {
          $idScene=$rows['id'];
          $id=$rows['id'];
          $ur=$rows['ur'];
          $ue=$rows['ue'];
          $folio=$rows['folio'];
          $alta=$rows['alta'];
          $inicio=$rows['inicio'];
          $termino=$rows['termino'];
          $comments=str_replace("|@", ",", $rows['comments']); //$rows['comments'];
          $year=$rows['year'];
          $oficio=$rows['oficio'];
          
          
  }

  $sqlBudget="SELECT DISTINCT
             SUM(chartdetailsbudgetlog.qty) AS total,
             chartdetailsbudgetlog.partida_esp as partida
             FROM chartdetailsbudgetlog 
              WHERE  nu_tipo_movimiento IN (251,253,254)
              AND  cvefrom LIKE '%".$ur.$ue."%'
              AND  chartdetailsbudgetlog.partida_esp NOT LIKE '1%'
              AND  chartdetailsbudgetlog.partida_esp NOT LIKE '4%'
              AND  chartdetailsbudgetlog.partida_esp != '32201'
              AND  chartdetailsbudgetlog.partida_esp != '37501'
              AND  chartdetailsbudgetlog.partida_esp != '37504'
              AND  chartdetailsbudgetlog.partida_esp != '39801'
              AND  chartdetailsbudgetlog.partida_esp != '39202'
              GROUP  BY chartdetailsbudgetlog.partida_esp";

  //    AND  chartdetailsbudgetlog.partida_esp NOT LIKE '5%'

   $res1 = DB_query($sqlBudget, $db);
   
    while ($rows = DB_fetch_array($res1)) {
         $aux=$rows['partida'];
         $total=$rows['total'];
         if($total>0){
          $partidasBudget[$aux]=round($total,0);
         }
         
  }
  //print_r($partidasBudget);
  $sqlAssets="SELECT  partidas.partidaEspecifica as partida,stockmaster.stockid as id,stockmaster.description as descri,stockmaster.mbflag as flag,stockmaster.units as unidad,
         partidas.descPartidaEspecifica as desPartida
        FROM stockmaster
        INNER JOIN tb_partida_articulo partidas ON stockmaster.eq_stockid= partidas.eq_stockid
        WHERE  partidas.partidaEspecifica NOT LIKE '1%'
       AND partidas.partidaEspecifica NOT LIKE '4%'
       AND  partidas.partidaEspecifica != '32201'
              AND  partidas.partidaEspecifica!= '37501'
              AND  partidas.partidaEspecifica != '37504'
              AND  partidas.partidaEspecifica != '39801'
              AND  partidas.partidaEspecifica != '39202'
        ORDER BY  partidas.partidaEspecifica ";
//partidas.partidaEspecifica NOT LIKE '5%'

$res2 = DB_query($sqlAssets, $db);
//round($total,2);
  while ($rows = DB_fetch_array($res2)) {
        $aux= array_key_exists($rows['partida'],$partidasBudget); // see if  exit partida index return true if exists
        if($aux){
            if($rows['flag']=='B'){
               $assets[]=array(
                'partida'=>$rows['partida'],
                'id'=>$rows['id'],
                'descri'=>$rows['descri'],
                'flag'=>$rows['flag'],
                'unidad'=>$rows['unidad'],
                'budget'=>round($partidasBudget[$rows['partida']],0)
                  
            ); 
               $assetsDes[$rows['partida']]= $rows['desPartida'];
           }else{
            $services[]=array(
                'partida'=>$rows['partida'],
                'id'=>$rows['id'],
                'descri'=>$rows['descri'],
                'flag'=>$rows['flag'],
                'unidad'=>$rows['unidad'],
                'budget'=>round($partidasBudget[$rows['partida']],0)
                  
            );
            $servicesDes[$rows['partida']]= $rows['desPartida'];
           }
        }// end if exists partida  in budget
        
  }
  $sqlGeo="SELECT cg, descripcion FROM g_cat_geografico WHERE activo='S'";
  $res3 = DB_query($sqlGeo, $db);
   while ($rows = DB_fetch_array($res3)) {
        // $geo[]=array(
        //     'id'=>$rows['cg'],
        //     'dec'=>$rows['descripcion']
        
        $v=$rows['cg']."-".$rows['descripcion'];
        $geo[$v]=$v;

   }

   $sqlUnits="SELECT ln_claveunidad as id,ln_descripcion  as descri FROM tb_units_twice ORDER BY ln_descripcion ASC";
   $res4 = DB_query($sqlUnits, $db);
   while ($rows = DB_fetch_array($res4)) {
        $v=$rows['id']."-".$rows['descri'];
        $units[$v]=$v;

   }

//array_search //devuelve la primera clave correspondiente en caso de éxito
  $dataDetail=getDataDetail($idScene,$db);

 $retorno[]=array('ur'=>$ur,'ue'=>$ue,'folio'=>$folio,'created'=>$alta,'begin'=>$inicio,'end'=>$termino,'comments'=>$comments,'year'=>$year,'oficio'=>$oficio,'assets'=>$assets,'assetsDes'=>$assetsDes,'servicesDes'=>$servicesDes,'services'=>$services,'geo'=>$geo,'units'=>$units,'dataDetail'=>$dataDetail,'budgetPEF'=>$partidasBudget);
 return $retorno;
  
}
function getDataDetail($id,$db){
$data=array();
$SQL="SELECT * FROM tb_cat_esenario_detalle WHERE id_nu_esenario_paaas='".$id."'";

  $res = DB_query($SQL, $db);
  while ($rows = DB_fetch_array($res)) {
   // $val=intval($partidasBudget[$rows['partida']]);
   // $partidasBudget[$rows['partida']]=($val+intval($rows['nu_valorestimado']));
   //print_r($partidasBudget[$rows['partida']]);

  $data[]=array(  
  "0row"=>$rows['nu_row']         
  ,"clavecucop"=>$rows['nu_clavecucop']         
  ,"partida"=>$rows['nu_partida']          
  ,"pef"=>$rows['nu_pef']                 
  ,"valorestimado"=>$rows['nu_valorestimado']      
  ,"valormipymes"=>$rows['nu_valormipymes']        
  ,"valorenctlc"=>$rows['nu_valorenctlc']       
  ,"cantidad"=>$rows['nu_cantidad']            
  ,"unidadmedida"=>$rows['nu_unidadmedida']        
  ,"caracterprocedimiento"=>$rows['nu_caracterprocedimiento']
  ,"entidadfederativa"=>$rows['nu_entidadfederativa']    
  ,"porcentaje1ertrim"=>$rows['nu_porcentaje1ertrim']  
  ,"porcentaje2dotrim"=>$rows['nu_porcentaje2dotrim']   
  ,"porcentaje3ertrim"=>$rows['nu_porcentaje3ertrim']  
  ,"porcentaje4totrim"=>$rows['nu_porcentaje4totrim']       
  ,"plurianual"=>$rows['nu_plurianual']          
  ,"aniosplurianuales"=>$rows['nu_aniosplurianuales']   
  ,"valortotalplurianual"=>$rows['nu_valortotalplurianual']
  ,"tipoprocedimiento"=>$rows['nu_tipoprocedimiento']
  ,"fecharegistro"=>$rows['dtm_fecharegistro'] 
  ,"tipo"=>$rows['sn_tipo']
  ,"viva"=>$rows['nu_valor_iva']);

  }
  return $data;
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
                $data=fnGetPartidasLista($db);
            break;
        case 'GuardarDatos':
               if (isset($_POST['datos'])){
                
                 $data=fnGuardarEscenario($db,$_POST['datos']);
               }
            break;
         case 'seeDetailPre':
               if (isset($_POST['datos'])){
                
                 $data=getDetailsScenePre($db,$_POST['datos']);
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

    echo json_encode($data);
} else {
    echo json_encode('Falla al conectarse con el servidor');
}
