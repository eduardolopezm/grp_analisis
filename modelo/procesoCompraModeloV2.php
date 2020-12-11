<?php
/**
 * Modelo para proceso de compra
 *
 * @category     proceso de compra
 * @package      ap_grp
 * @author       Arturo Lopez Peña <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 12/12/2017
 * Fecha Modificación: 12/12/2017
 */
 // ini_set('display_errors', 1);
 // ini_set('log_errors', 1);
 // error_reporting(E_ALL);
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
$funcion=2291;
require $PathPrefix.'includes/SecurityFunctions.inc';
require $PathPrefix.'includes/SQL_CommonFunctions.inc';
require $PathPrefix . 'includes/DateFunctions.inc';
//require $PathPrefix . 'includes/Subir_Archivos.php';


//$permiso = Havepermission ( $_SESSION ['UserID'], 244, $db ); // tenia 2006


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



switch ($proceso) {
    case 'enviarprovsug':
    
          try{
              $cadenaInsertar='';
                    if(isset($_POST['requis'])){

                    $requisiciones=$_POST['requis'];
                    for($a=0;$a<count($requisiciones);$a++){
                     // echo ($requisiciones[$a]);
                      $text = trim($requisiciones[$a], "'");
                      $text = trim($text, '"');
                      $cadenaInsertar.="('".$text."','".$_SESSION['UserID']."','Proveedores Sugeridos'"."),";

                    }
                    $cadenaInsertar=substr($cadenaInsertar, 0, -1);


                    $SQL= "INSERT INTO  tb_proceso_compra_prov_sugeridos (nu_requi,ln_usuario,ln_nombre_estatus)  VALUES ".$cadenaInsertar;
               
                    $ErrMsg = "No se eliminó ";
                    $TransResult = DB_query($SQL, $db, $ErrMsg);

                    $contenido ='Se enviaron requisiciones seleccionadas a <a href="procesoCompraV2.php" style="color:blue"><u>Proveedores sugeridos</u></a>';
                    $result = true;
                }// fin if

            } catch (Exception $excepcion) {
                    $ErrMsg .= $excepcion->getMessage(); 
                }
         
   break;

   case 'requisProvSug':
        try{        $liga='';
                    $datos=array();
                    $SQL= "SELECT nu_id,nu_requi,dtm_fecharegistro,ln_nombre_estatus FROM tb_proceso_compra_prov_sugeridos ORDER BY nu_id DESC";
                    $ErrMsg = "No se eliminó ";
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                    while ($myrow = DB_fetch_array($TransResult)) {

                        $liga='<u style="color: blue;" >'.$myrow['nu_requi'].' </u>';
                        $datos[] = array('id'=>$myrow['nu_id'],'requi'=>$liga,
                                  'fecha' =>$myrow['dtm_fecharegistro'],
                                  'estatus'=>fnExisteContrato($myrow['nu_requi'],$db ) //$myrow['ln_nombre_estatus']
                                   
                                ); 
                    }
                    $contenido = array('datos' => $datos);

                    $result = true;
       

            } catch (Exception $excepcion) {
                    $ErrMsg .= $excepcion->getMessage(); 
                }
   break;

     case 'getRequi':
      try{
      $info = array();
      $requi = $_POST['requisicion'];

      $SQL="SELECT 
                 purchorderdetails.orderno AS idRequisicion, 
                tb_partida_articulo.partidaEspecifica AS idPartida, 
                tb_cat_partidaspresupuestales_partidaespecifica.descripcion AS descPartida,
                purchorderdetails.itemcode AS idItem, 
                purchorderdetails.itemdescription AS descItem, 
                stockmaster.units AS unidad, 
                stockmaster.mbflag AS tipo,
                purchorderdetails.unitprice AS precio, 
                purchorderdetails.quantityord AS cantidad,
                purchorderdetails.total_quantity AS total, 
                -- if(almacen.existencia = 0,'No Disponible','Disponible') AS existencia,
                almacen.existencia AS existencia,
                purchorderdetails.orderlineno_ AS orden, 
                purchorderdetails.clavepresupuestal AS clavePresupuestal, 
                purchorderdetails.sn_descripcion_larga AS descLarga,
                purchorderdetails.renglon AS renglon, purchorders.tagref
            FROM purchorderdetails 
            INNER JOIN purchorders ON  purchorderdetails.orderno= purchorders.orderno
            JOIN stockmaster on (stockmaster.stockid = purchorderdetails.itemcode )
            JOIN tb_partida_articulo on (tb_partida_articulo.eq_stockid = stockmaster.eq_stockid)
            JOIN tb_cat_partidaspresupuestales_partidaespecifica on (tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = tb_partida_articulo.partidaEspecifica)
            LEFT JOIN (SELECT stockid, SUM(quantity) AS existencia FROM locstock INNER JOIN sec_loccxusser ON locstock.loccode= sec_loccxusser.loccode AND userid='desarrollo'
            GROUP BY stockid) AS almacen ON stockmaster.stockid= almacen.stockid
           /* WHERE purchorderdetails.orderno = '997' AND purchorderdetails.status ='2' */
            where purchorders.requisitionno ='".$requi ."' AND purchorderdetails.status ='2'
            ORDER BY orden;";
       
            $ErrMsg = "No se obtuvo datos";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            while ($myrow = DB_fetch_array($TransResult)) {
                $info[] = array( 

                  'idPartida' => $myrow ['idPartida'],
                  'descPartida' => $myrow ['descPartida'],
                  'idItem' => $myrow ['idItem'], 
                  'descItem' => $myrow ['descItem'],
                  'unidad' => $myrow ['unidad'],
                  'tipo' => $myrow ['tipo'],
                  'precio' => $myrow ['precio'],
                  'cantidad' => $myrow ['cantidad'],
                  'total' => $myrow ['total'],
                  'existencia' => $myrow ['existencia'],
                  'orden' => $myrow ['orden'],
                  'clavePresupuestal'=> $myrow ['clavePresupuestal'],
                  'descLarga' => $myrow ['descLarga'],
                  'renglon' => $myrow ['renglon'] 

                               );
     
               
            }

            $contenido = array('requisicion'=>$info);
            $result = true;
             } catch (Exception $excepcion) {
                    $ErrMsg .= $excepcion->getMessage(); 
                }
    break;

    case 'obtenerProvedoresSugeridos':
            try{
            $tipo= $_POST['tipo'];
            $partidas=$_POST['partidas'];
            $ErrMsg = "No se obtuvo datos";
            $valPartidas='';

            for ($a=0; $a <count($partidas) ; $a++) { 
              $valPartidas.="'".$partidas[$a]."',";

            }
             $valPartidas=substr($valPartidas, 0, -1);

            $SQL="SELECT  ln_partida_especifica,ln_supplierid,ln_suppname, suppliers.email  FROM tb_partidas_proveedores INNER JOIN suppliers 
            ON tb_partidas_proveedores.ln_supplierid = suppliers.supplierid  WHERE   ln_partida_especifica IN ($valPartidas) ORDER BY ln_partida_especifica ASC ; ";
           // exit();
            $TransResult = DB_query($SQL, $db, $ErrMsg);
        
            while ($myrow = DB_fetch_array($TransResult)) {
                $info[] = array( 

                  'partida' => $myrow ['ln_partida_especifica'],
                  'idsup' =>   $myrow ['ln_supplierid'],
                  'nombre' =>  $myrow ['ln_suppname'],
                  'email'=>    $myrow ['email']
                               );
            }

            $contenido = array('datosPro'=>$info);
            $result = true;

            } catch (Exception $excepcion) {
                    $ErrMsg .= $excepcion->getMessage(); 
                }

break;

case 'enviarCotizacionV2':
          try{
            $requisiones= $_POST['requis'];
            $partidas=  array();
            $infoProvSug=array();
            //por cada  requisicion
            for($a=0;$a<count($requisiones);$a++){
              
                /// ir por datos requi
                 $datosRequis =fnGetDatosRequi($requisiones[$a],$db);
           
                  for($d=0;$d<count($datosRequis[$d]);$d++){
                      foreach ($datosRequis[$d] as $key => $value) {
                     if($key=='idPartida'){
                        $partidas[]=$value;
                     }
                  
                    }
                  }
                ///fin  ir por datos  requi
                
                //voy por provsug
                //echo $requisiones[$a];
                $infoProvSug=fnGetProvsugeridos($partidas,$db);
                 //print_r($infoProvSug);
                /*for($ad=0;$ad<count($infoProvSug);$ad++){
                  //fnGenerarExcel($datosprove,$requidatos);
                    for($da=0;$da<count($infoProvSug[$ad]);$da++){
                        print_r($infoProvSug[]);
                    }

                }*/
                //fin voy por provsug
               
               //envio correo
               //
              
            }//fin  de  cada  requisicion
            
        
                    $contenido = array('datosPro'=>$info);
                    $result = true;
           } catch (Exception $excepcion){
            $ErrMsg .= $excepcion->getMessage(); 
          }

break;

case 'guardarDatosCotizacion':

        try{

          $requi=$_POST['requi'];
          $provedor=$_POST['provedor'];
          $partidas=$_POST['partidas'];
          $articulos=$_POST['articulos'];
          $descripciones=$_POST['descripciones'];
          $cotizaciones=$_POST['cotizaciones'];

          $valoresInsertar='';

          for($a=0;$a<count($partidas);$a++){
          $valoresInsertar.="('".$requi."','". $provedor."','". $partidas[$a]."','".$articulos[$a]."','".   $descripciones[$a]."','". $cotizaciones[$a]."'),";

          }
            $valoresInsertar= substr( $valoresInsertar, 0, -1);

            $SQL = "INSERT INTO tb_procesp_compra_cotizaciones (nu_requisicion,ln_provedor,ln_partida,ln_articulo,ln_descrpcion,ln_cotizacion) VALUES ".$valoresInsertar;

            $ErrMsg = "No se guardo información";
            $TransResult = DB_query($SQL, $db, $ErrMsg);


            $SQL2="UPDATE tb_proceso_compra_prov_sugeridos SET ln_nombre_estatus='Cotización Guardada'  WHERE nu_requi = ".$requi; 

            $ErrMsg = "No se guardo información";
            $TransResult = DB_query($SQL2, $db, $ErrMsg);

            $contenido = "Se guardo la cotización cargada.";
            $result=true;

             } catch (Exception $excepcion){
            $ErrMsg .= $excepcion->getMessage(); 
          }

          
break;

    case 'cargaContrato':
 

                   //try{
                    $SQL="UPDATE tb_proceso_compra_prov_sugeridos SET ln_nombre_estatus='Contrato cargado' WHERE nu_requi='". $_POST['requi']."'";
                    $ErrMsg = "Problema al cargar documento";
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                      //print_r($SQL);
                     $contenido = "Se guardo el contrato corretamente.";
                    $result=true;
                  // }catch (Exception $e){
                       // $ErrMsg .= $e->getMessage(); 
                  // }

    break;

case 'traerDatosCuadroComparativo':

    try{
   
          $requisiones= $_POST['requi'];
          $datos=  array();
          $datos1=  array();

         $SQL="SELECT  ln_provedor,nu_requisicion,ln_partida,ln_articulo,ln_descrpcion,ln_cotizacion FROM tb_procesp_compra_cotizaciones WHERE nu_requisicion='". $requisiones."' ORDER BY ln_partida ASC";
            //print($SQL); exit();
   
        $ErrMsg = "No se obtuvieron disponibles";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $datos[] =  array('provedor'=>$myrow ['ln_provedor'],
                              'partida'=>$myrow ['ln_partida'],
                              'articulo'=>$myrow ['ln_articulo'],
                              'descripcion'=>$myrow ['ln_descrpcion'],
                              'cotizacion'=>$myrow ['ln_cotizacion']
                            );
        }

        $SQL="SELECT SUM(ln_cotizacion ) as totalrequi, ln_provedor FROM tb_procesp_compra_cotizaciones WHERE nu_requisicion='". $requisiones."' GROUP BY ln_provedor ORDER BY totalrequi ASC;";
        $ErrMsg = "No se obtuvieron disponibles";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $datos1[] =  array( 'montoTotal'=>$myrow ['totalrequi'],
                                'provedor'=>$myrow ['ln_provedor']
                                
                              
                            );
        }

//print_r($datos1);
        $contenido = array('datosPorArticulo' => $datos,'montoTotalRequi'=>$datos1);
        $result = true;

             } catch (Exception $excepcion){
            $ErrMsg .= $excepcion->getMessage(); 
          }
break;

}

// function fnGetDatosRequi($requi,$db){
//    $infoDatosRequi=array();
//                             $SQL="SELECT 
//                              purchorderdetails.orderno AS idRequisicion, 
//                             tb_partida_articulo.partidaEspecifica AS idPartida, 
//                             tb_cat_partidaspresupuestales_partidaespecifica.descripcion AS descPartida,
//                             purchorderdetails.itemcode AS idItem, 
//                             purchorderdetails.itemdescription AS descItem, 
//                             stockmaster.units AS unidad, 
//                             stockmaster.mbflag AS tipo,
//                             purchorderdetails.unitprice AS precio, 
//                             purchorderdetails.quantityord AS cantidad,
//                             purchorderdetails.total_quantity AS total, 
//                             -- if(almacen.existencia = 0,'No Disponible','Disponible') AS existencia,
//                             almacen.existencia AS existencia,
//                             purchorderdetails.orderlineno_ AS orden, 
//                             purchorderdetails.clavepresupuestal AS clavePresupuestal, 
//                             purchorderdetails.sn_descripcion_larga AS descLarga,
//                             purchorderdetails.renglon AS renglon, purchorders.tagref
//                         FROM purchorderdetails 
//                         INNER JOIN purchorders ON  purchorderdetails.orderno= purchorders.orderno
//                         JOIN stockmaster on (stockmaster.stockid = purchorderdetails.itemcode )
//                         JOIN tb_partida_articulo on (tb_partida_articulo.eq_stockid = stockmaster.eq_stockid)
//                         JOIN tb_cat_partidaspresupuestales_partidaespecifica on (tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = tb_partida_articulo.partidaEspecifica)
//                         LEFT JOIN (SELECT stockid, SUM(quantity) AS existencia FROM locstock INNER JOIN sec_loccxusser ON locstock.loccode= sec_loccxusser.loccode AND userid='desarrollo'
//                         GROUP BY stockid) AS almacen ON stockmaster.stockid= almacen.stockid
//                        /* WHERE purchorderdetails.orderno = '997' AND purchorderdetails.status ='2' */
//                         where purchorders.requisitionno ='".$requi ."' AND purchorderdetails.status ='2'
//                         ORDER BY orden;";
                   
//                         $ErrMsg = "No se obtuvo datos";
//                         $TransResult = DB_query($SQL, $db, $ErrMsg);

//                         while ($myrow = DB_fetch_array($TransResult)) {
//                              $infoDatosRequi[] = array( 

//                               'idPartida' => $myrow ['idPartida'],
//                               'descPartida' => $myrow ['descPartida'],
//                               'idItem' => $myrow ['idItem'], 
//                               'descItem' => $myrow ['descItem'],
//                               'unidad' => $myrow ['unidad'],
//                               'tipo' => $myrow ['tipo'],
//                               'precio' => $myrow ['precio'],
//                               'cantidad' => $myrow ['cantidad'],
//                               'total' => $myrow ['total'],
//                               'existencia' => $myrow ['existencia'],
//                               'orden' => $myrow ['orden'],
//                               'clavePresupuestal'=> $myrow ['clavePresupuestal'],
//                               'descLarga' => $myrow ['descLarga'],
//                               'renglon' => $myrow ['renglon'] 

//                                            );
                 
                           
//                         }
//                         return $infoDatosRequi;
// }
// function fnGetProvsugeridos($partidas,$db){

//             //$partidas=$_POST['partidas'];
//             $infoPartidasProv=array();
//             $ErrMsg = "No se obtuvo datos";
//             $valPartidas='';

//             for ($a=0; $a <count($partidas) ; $a++) { 
//               $valPartidas.="'".$partidas[$a]."',";

//             }
//              $valPartidas=substr($valPartidas, 0, -1);

//             $SQL="SELECT  ln_partida_especifica,ln_supplierid,ln_suppname, suppliers.email  FROM tb_partidas_proveedores INNER JOIN suppliers 
//             ON tb_partidas_proveedores.ln_supplierid = suppliers.supplierid  WHERE   ln_partida_especifica IN ($valPartidas) ORDER BY ln_partida_especifica ASC ; ";
//            // exit();
//             $TransResult = DB_query($SQL, $db, $ErrMsg);
        
//             while ($myrow = DB_fetch_array($TransResult)) {
//                /* $infoPartidasProv[] = array( 

//                   //'partida' => $myrow ['ln_partida_especifica'],
//                   'idsup' =>   $myrow ['ln_supplierid']
//                   /*'nombre' =>  $myrow ['ln_suppname'],
//                   'email'=>    $myrow ['email'] */
//                              /*  );*/
//                              $infoPartidasProv[]=$myrow ['ln_supplierid'];
//             } 

//             return  $infoPartidasProv;

// }
function fnDescargarArchivo($requi,$db){
    $SQL = "SELECT * FROM tb_archivos WHERE  nu_trasnno='".$requi."' AND nu_tipo_sys='2340'  ORDER BY dtm_fecharegistro DESC LIMIT 1";
    $ErrMsg = "No se obtuvo datos.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $liga='';
    while ($myrow = DB_fetch_array($TransResult)) {
     $liga = $myrow ['txt_url'];
    } 


    return  $liga;
}
function  fnExisteContrato($requi,$db){
    $estatusR='';
    $SQL = "SELECT  CASE  WHEN  ln_nombre_estatus='Contrato cargado' THEN 'Existe'  else ln_nombre_estatus END  AS estatus FROM tb_proceso_compra_prov_sugeridos WHERE nu_requi ='".$requi."'";
    $ErrMsg = "No se obtuvo datos.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $estatus=DB_fetch_array($TransResult);
    //print_r($SQL);
    if ($estatus['estatus']=='Existe') {
        $estatusR=$estatus['estatus'];
        $liga=fnDescargarArchivo($requi,$db);
        $estatusR='<u > <a style="color:blue" href="'.$liga.'">'.'Contrato cargado'.'</a> </u>';
    }else{
        $estatusR=$estatus['estatus'];
      
    }

    return  $estatusR;
}

function fnGenerarExcel($datosprove,$requidatos,$db){
   /* $datosprove=$_POST['proveedores'];
            $requidatos=$_POST['datosrequi'];
          */
            $val='';
            //print_r($requidatos);
            for($a=0;$a<count($datosprove);$a++){
                $val.="'".$datosprove[$a]."',";
            }
            $val=substr($val, 0, -1);

         $SQL="SELECT email,suppname,address1,address2,address3,address4,address5,address6,supplierid as provedor FROM  suppliers  where supplierid in(".$val.")";


            $ErrMsg = "No hay  archivos";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            while ($myrow = DB_fetch_array($TransResult)){
             $info[]= array('email' =>$myrow['email'],
                    'nombre' =>$myrow['suppname'],
                    'ad1' =>$myrow['address1'],
                    'ad2' =>$myrow['address2'],
                    'ad3' =>$myrow['address3'],
                    'ad4' =>$myrow['address4'],
                    'ad5' =>$myrow['address5'],
                    'ad6' =>$myrow['address6'],
                    'provedor' =>$myrow['provedor']
                    
                  );
                $nombre=str_replace(' ',"_",$myrow['suppname'] ); 
               
                //fnCrearExcelConCeldasBloqueadas($myrow['email'],$nombre);
            }

            $cadenaInsertar=  fnCrearExcelConCeldasBloqueadas($info,$requidatos,$db);

            $SQL="INSERT INTO tb_archivos (ln_userid,ln_nombre_interno_archivo,txt_url,nu_funcion,ind_active,nu_tipo_sys,nu_trasnno,ln_nombre,ind_es_layout) VALUES ".$cadenaInsertar;
            //print_r($SQL);
            $ErrMsg = "Problema al cargar documento";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
}
$dataObj = array('info' =>'', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
