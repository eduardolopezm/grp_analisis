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

function fnConsultarMontosDeActuacion($cantidadtotal){

    $SQL="SELECT conceptocalculado AS value,descripcion as texto FROM tb_montos_actuacion WHERE =".$capitulo;
        

        $ErrMsg = "No se obtuvo datos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

    
}
function fnConfiguracionFormularioAdjudicacion($tipo,$db){
     $datos= array();
     $requi="2424";//
     $SQL="SELECT * FROM tb_configuracion_adjudicacion_formulario WHERE ln_id_tipo_adjudicacion=$tipo ORDER BY nu_orden ASC";
        

        $ErrMsg = "No se obtuvo datos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
                       //var infocomponente=["type_grp:"+tipoCampo+"|name_grp:"+nombreCampo+"|max_grp:"+maximo+"|obligatorio_grp:"+obligatorio+"|comment_grp:"+leyenda+"|typeAd_grp:"+tipoad+"|req_grp:"+requisicion+"|val_grp:"+value];
                       //
           $leyenda=str_replace(" ","_", $myrow['ln_leyenda_etiqueta']);
           $infocomponente=$tipo."|".$requi."|".$myrow ['ln_tipo_campo']."|".$myrow ['ln_nombre_del_campo']."|".$myrow['ln_maximo_permitido']."|".$myrow ['ind_obligatorio']."|".$leyenda;
           
       
            $datos[] = array( 'tipo_campo' => $myrow ['ln_tipo_campo'], 'obligatorio' =>$myrow ['ind_obligatorio'],'nombre_campo'=>$myrow['ln_nombre_del_campo'],'leyenda'=>$leyenda,'maximo'=>$myrow['ln_maximo_permitido'],'orden'=>$myrow['nu_orden'],'tipo_adjudicacion' =>$myrow ['ln_id_tipo_adjudicacion'],'info'=> $infocomponente);
                       
            }
 //$datos[] =array( 'tipo_campo'=>$tipo);
    return   $datos;
}

function fnObtenerDatosFormulario($db){
    /*$data =array();
    foreach ($_POST as $param_name => $param_val){
    $data += [$param_name => $param_val];
 
    }
    return $data; */
     if(isset($_FILES['archivos'])) {
            $file_tmp='';
            $funcion='224';
            $tipo='224';
            $trans='224';
            $vallayout='0';
            $cadenaDatosInsertar='';
            //$fileCount = count($_FILES['archivos']);
            //print_r($_FILES);
           
              foreach($_FILES['archivos']['tmp_name'] as $key => $tmp_name){
                 
              
                     $file_name = $key.$_FILES['archivos']['name'][$key];
                     $file_size =$_FILES['archivos']['size'][$key];
                     $file_tmp =$_FILES['archivos']['tmp_name'][$key];
                     $file_type=$_FILES['archivos']['type'][$key];  
                     $file_name=str_replace(" ", "", $file_name); 

                      
                      $cadenaDatosInsertar.="('". $_SESSION['UserID']."','".$file_type."','".$file_name."','archivos/".$file_name."','".$funcion."','1','".$tipo."','".$trans."','".$file_name."','". $vallayout."'),";
                      move_uploaded_file($file_tmp, "/archivos/".$file_name);
              
                  
              }
         


          $cadenaDatosInsertar= substr($cadenaDatosInsertar, 0, -1);
          $SQL = "INSERT INTO tb_archivos (`ln_userid`, `sn_tipo`, `ln_nombre_interno_archivo`,`txt_url`,`nu_funcion`,`ind_active`,`nu_tipo_sys`,`nu_trasnno`,`ln_nombre`,`ind_es_layout`) VALUES ".$cadenaDatosInsertar;
          $ErrMsg = "Problema al cargar documento";
          $TransResult = DB_query($SQL, $db, $ErrMsg);
      }
}

switch ($proceso) {
    case 'obtenerconfiguracion':
        $monto=$_POST['TotalRequi'];
        $SQL="SELECT nu_rango_inicial,nu_rango_tope,ln_tipo_adjudicacion,txt_descripcion_adjudicacion from tb_configuracion_adjudicacion WHERE ".$monto.">=nu_rango_inicial AND ".$monto."<=nu_rango_tope GROUP BY ln_tipo_adjudicacion ;
    ";
        

        $ErrMsg = "No se obtuvo datos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        
        if ( (DB_num_rows ( $TransResult) > 0) && (DB_num_rows ( $TransResult)== 1) ){  // sin subtipos  de adjudicacion 

                    while ($myrow = DB_fetch_array($TransResult)) {
                     $formulario=   fnConfiguracionFormularioAdjudicacion($myrow ['ln_tipo_adjudicacion'],$db); //formulario de configuracion
                     $info[] = array( 'value' => $myrow ['ln_tipo_adjudicacion'], 'texto' =>$myrow ['txt_descripcion_adjudicacion'],'formulario'=>$formulario );
                       
                    }

        }elseif((DB_num_rows ( $TransResult)> 1)){ //indica  que hay un subtipo  como por ejemplo licitacion publica o nacional  si hay subtipo
               while ($myrow = DB_fetch_array($TransResult)) {
                        $info[] = array( 'value' => $myrow ['ln_tipo_adjudicacion'], 'texto' =>$myrow ['txt_descripcion_adjudicacion'] );  
                    }

        }else{

                $info[] = array( 'value' =>'nada', 'texto' =>'No existe un tipo de adjudicación para este monto' );
              }

        $contenido = array('configuracion'=>$info);
        $result = true;
 
        break;
        //  fin configuracion para datos de formulario
    case 'guardardatosformulariopaso1':
    fnObtenerDatosFormulario($db);
     //antes de meter files
      // $info=$_POST['datosarreglo'];
      // $valores=$_POST['valores'];
      // $datosinsertar='';
      // //date,prueba1,100,1,Cabio val 08-12-2017
      // //1     2        3  4  5     6   7    
      // //$columnas= explode("|",  $valores);
      // //000|2424|text|nombreempresa|2|0|Nombre_Empresa
      // //1    2    3     4           5 6   7
      // //
      //       for ($a=0; $a<count($info); $a++) {
      //         if($info[$a]!='save'){
      //             //0     1    2      3                 4 5  6
      //             //1000|2424|number|cantidadsolicitada|3|0|
      //             //1000|2424|number|cantidadsolicitada|7|0|Cantidad_solicitada
      //             //0      1    2      3                4 5 6
      //         $datosSeparados=explode("|", $info[$a]);
      //         $datosinsertar.="('".$valores[$a]."','".$datosSeparados[0]."','".$datosSeparados[1]."','".$_SESSION ['UserID']."','".$datosSeparados[2]."','".$datosSeparados[3]."','".$datosSeparados[4]."','".$datosSeparados[5]."','".$datosSeparados[6]."'";
      //           $datosinsertar.='),';
      //         }
              
      //       }
      //$data += [$param_name => $param_val];
      $datosinsertar='';
      $valores=$_POST['valores'];
      $info=$_POST['data-info'];
        // foreach ($_POST as $param_name => $param_val){
        //     if( ($param_name!="nombrearchivoCLS") AND ($param_name!="funcionArchivosCLS") AND ($param_name!="tipoArchivoCLS") AND ($param_name!="transnoArchivoCLS")  AND ($param_name!="esMultipleCLS") AND ($param_name!="data-info")){
        //  // echo ($param_name."->".$param_val." ");
        //   } 

        //  if($param_name=="data-info"){

            //$info=$param_val;

            for ($a=0; $a<count($info); $a++) {
            if($info[$a]!='save'){
              
              $datosSeparados=explode("|", $info[$a]);
              $datosinsertar.="('".$valores[$a]."','".$datosSeparados[0]."','".$datosSeparados[1]."','".$_SESSION ['UserID']."','".$datosSeparados[2]."','".$datosSeparados[3]."','".$datosSeparados[4]."','".$datosSeparados[5]."','".$datosSeparados[6]."'";
                $datosinsertar.='),';
              }

            } // fin for

          //}
          // $datosinsertar.="('".$param_va."','".$datosSeparados[0]."','".$datosSeparados[1]."','".$_SESSION ['UserID']."','".$datosSeparados[2]."','".$datosSeparados[3]."','".$datosSeparados[4]."','".$datosSeparados[5]."','".$datosSeparados[6]."'";
          //       $datosinsertar.='),'; 
        //}
        // datos formulario
      
         $datosinsertar=substr($datosinsertar, 0, -1);
           
           

      $values="ln_valor_del_campo,ln_id_tipo_adjudicacion,ln_requisicion,ln_usuario,ln_tipo_campo,ln_nombre_del_campo,ln_maximo_permitido,ind_obligatorio,ln_leyenda_etiqueta";

        //,ln_id_tipo_adjudicacion,ln_requisicion,ln_folio_proceso_de_compra,ln_usuario

       $SQL = "INSERT INTO tb_configuracion_adjudicacion_datos(".$values.") VALUES ". $datosinsertar;
        // print_r($SQL);
        // exit(); 

       $ErrMsg = "No se agregó agregó solicitud al almacen";
       $TransResult = DB_query($SQL, $db, $ErrMsg);
      

       $contenido = array('datos'=>"<h4>Se ha guardado con éxito los datos con folio para proceso de compra :<b>".''."</b></h4>",'folio'=>'');
       $result = true; 

      break;
    case 'porrenglon':

    break;
  case 'requisiciontotal':
    $a=1;
    $capitulo=$_POST['capitulo'];
    $capitulo=str_replace("000","",$capitulo);

        $SQL="SELECT conceptocalculado AS value,descripcion as texto FROM tb_cat_partidaspresupuestales_concepto WHERE ccap=".$capitulo;
    

        $ErrMsg = "No se obtuvo datos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array( 'value' => $myrow ['value'], 'texto' => $a." - ".$myrow ['texto'] );
            $a++;
           
        }

    $contenido = array('presuuestaloncepto'=>$info);
        $result = true;
 
    break;
    case 'porrenglon':

    break;


    case 'getRequi':
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


            /*  IdRequisicion
idPartida
descPartida
idItem
descItem
Unidad
tipo
precio
cantidad 
total 
existencia
orden
clavePresupuestal
descLarga
renglon
 */
       
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
    break;



case 'obtenerProvedoresSugeridos':

            $tipo= $_POST['tipo'];
            $partidas=$_POST['partidas'];
            $ErrMsg = "No se obtuvo datos";
            $valPartidas='';

            for ($a=0; $a <count($partidas) ; $a++) { 
              $valPartidas.="'".$partidas[$a]."',";

            }
             $valPartidas=substr($valPartidas, 0, -1);

            $SQL="SELECT  ln_partida_especifica,ln_supplierid,ln_suppname  FROM tb_partidas_proveedores  WHERE   ln_partida_especifica IN ($valPartidas); ";
           // exit();
            $TransResult = DB_query($SQL, $db, $ErrMsg);
        
            while ($myrow = DB_fetch_array($TransResult)) {
                $info[] = array( 

                  'partida' => $myrow ['ln_partida_especifica'],
                  'idsup' => $myrow ['ln_supplierid'],
                  'nombre' => $myrow ['ln_suppname']
                               );
     
               
            }

            $contenido = array('datosPro'=>$info);
            $result = true;



break;


}



$dataObj = array('info' =>'', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
