<?php
/**
 * Modelo para almacen
 *
 * @category     Almacen
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
//$permisomostrar=Havepermission($_SESSION['UserID'], 1420, $db);
//$permisomostrar= Havepermission($_SESSION ['UserID'], 2272, $db); // tenia 2006
//$permisomostrar=1;

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";
$requisiciones=[];
$tagref='';
$SQL='';
$type=1000;
header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$proceso = $_POST['proceso'];
$info = array();
function fnChecarDisponibleMultiple($almacen,$articulos,$db,$cantidadesdArray,$clavesArray,$renglonesArray){
    $disponible=array();
    $datos = array();
    $mensaje='';
    //$perfil= fnChecarPerfilPorFuncion($db);
    $SQL="";
    if($perfil=='alm'){
        $SQL="SELECT (quantity) AS disponible, stockid  FROM  locstock WHERE stockid IN (".$articulos.") and loccode ='".$almacen."' ORDER BY stockid ASC";
     } else{
    //print_r($articulos);
    $SQL="SELECT (quantity- ontransit) AS disponible, stockid  FROM  locstock WHERE stockid IN (".$articulos.") and loccode ='".$almacen."' ORDER BY disponible,stockid ASC";
    }
   
    $ErrMsg = "No se obtuvieron disponibles";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
            $datos[] =  array('id'=>$myrow ['stockid'],'disponible'=>$myrow ['disponible']);
            
        }
   // echo $SQL;
    foreach ($datos as $ad) {
              //echo $ad['id'];    
              $ind = array_search($ad['id'],$clavesArray,false);

              if($cantidadesdArray[$ind]>$ad['disponible']){
               $mensaje.= "<br><i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> En el renglon ".$renglonesArray[$ind]. ' el artículo '.$ad['id']." no tiene disponible.";
              }
             
    }// fin primer  foreach
  return $mensaje;
    
}
function fnReemplazar($mes)
{

    $mes= str_replace('October', 'Octubre', $mes);

    return $mes;
}
function fnGetUe($ur,$ue,$db){
    $retorno='';
    //try{
        $SQL="SELECT ue,desc_ue FROM tb_cat_unidades_ejecutoras where ur='".$ur."' and ue='".$ue."'";
            $ErrMsg = "No se obtuvo datos.";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            //$retorno=DB_fetch_array($TransResult);

             while ($myrow = DB_fetch_array($TransResult)) {
                $retorno=$myrow['ue']."-".$myrow['desc_ue'];

                }


       /* } catch (Exception $e) {
        $ErrMsg .= $e->getMessage();
    }*/
    return $retorno;

}
function fnChecarPerfilPorFuncion($db)
{
    $perfil='capt';
    $contador=0;
    //$permisomostrarVal= Havepermission($_SESSION ['UserID'], 2287, $db);
    //$permisomostrarAut= Havepermission($_SESSION ['UserID'], 2288, $db);
    //$permisomostrarAlm= Havepermission($_SESSION ['UserID'], 2293, $db);

    if (Havepermission($_SESSION ['UserID'], 2297, $db)==1) { //validador
        $perfil="val";
        $contador++;
    } elseif (Havepermission($_SESSION ['UserID'], 2290, $db)==1) {//autorizador
        $perfil="aut";
        $contador++;
    } elseif (Havepermission($_SESSION ['UserID'], 2293, $db)==1) {//almacenista
        $contador++;
        $perfil="alm";
    }

    if ($contador>1) {
        $perfil="todos";
    }
    return $perfil;
}
function fnExisteSalida($idsolicitud,$db){


    $SQL = "SELECT COUNT(*)  AS existe FROM tb_salidas_almacen WHERE nu_solicitud ='".$idsolicitud."'";
    $ErrMsg = "No se obtuvo datos.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $filas=DB_fetch_array($TransResult);
    //$filas=$filas['existe'];
    $filas=$filas['existe'];

    return $filas;

}
function fnEstatusParcialesCompleto($idsolicitud,$db){
    $solicitados=0;
    $entregados=0;
    $Mensaje='';

    $SQL="SELECT      SUM(CASE   WHEN ln_arctivo=1 THEN tb_solicitudes_almacen_detalle.nu_cantidad ELSE 0 END) AS solicitados 
        FROM tb_solicitudes_almacen_detalle
        where nu_id_solicitud='".$idsolicitud."'";
    $ErrMsg = "No se obtuvo datos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $solicitados=$myrow['solicitados'];
    }

    $SQL="SELECT   SUM(nu_cantidad_entregada) AS entregada 
        FROM tb_salidas_almacen_detalle 
        INNER JOIN tb_solicitudes_almacen 
        ON tb_salidas_almacen_detalle.nu_id_solicitud=tb_solicitudes_almacen.nu_folio   
        WHERE tb_salidas_almacen_detalle.nu_id_solicitud='".$idsolicitud."'" ;

    $ErrMsg = "No se obtuvo datos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $entregados=$myrow['entregada'];
    }
    if($solicitados==$entregados){
        $Mensaje='Entrega Completa';
    }else{
        $Mensaje='Entrega Parcial';
    }

    $SQL = "UPDATE  tb_solicitudes_almacen SET ln_nombre_estatus='".$Mensaje."' WHERE nu_folio = (".$idsolicitud.") "; // nu_folio='".$solicitud."'";

    $ErrMsg = "No se actualizao el estatus.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);


}
$datos='';
switch ($proceso) {
    case 'datosSelectSolicitud':
        $clave= array();
        $descripcion= array();
        $cams= array();
        $partida= array();
        $almacen=$_POST['almacen'];
        //$SQL = "SELECT DISTINCT stockmaster.stockid AS clave, stockmaster.description AS descripcion FROM stockmaster";
        $SQL="SELECT DISTINCT stockmaster.stockid AS clave, stockmaster.description AS descripcion FROM stockmaster INNER JOIN locstock ON stockmaster.stockid=locstock.stockid  WHERE loccode='".$almacen."' AND stockmaster.mbflag='B' ";
        $ErrMsg = "No se obtuvo datos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $clave[] = array( 'value' => $myrow ['clave'], 'texto' => $myrow ['clave'] );
            $descripcion[] = array( 'value' => $myrow ['descripcion'], 'texto' => $myrow ['descripcion'] );
        }

        //solo partida
        /*$SQL="SELECT  tb_partida_articulo.partidaEspecifica AS partida  FROM  tb_partida_articulo GROUP BY tb_partida_articulo.partidaEspecifica"; */
        /* $SQL="SELECT DISTINCT tb_partida_articulo.partidaEspecifica AS partida  FROM  tb_partida_articulo inner join stockmaster on  tb_partida_articulo.eq_stockid=stockmaster.eq_stockid";
         $ErrMsg = "No se obtuvo datos";
         $TransResult = DB_query($SQL, $db, $ErrMsg);

         while ($myrow = DB_fetch_array($TransResult)) {

         $partida[] = array( 'value' => $myrow ['partida'], 'texto' => $myrow ['partida'] );
         //$cams[] = array( 'value' => $myrow ['cams'], 'texto' => $myrow ['cams'] );


         }

         /*$SQL="SELECT tb_partida_articulo.eq_stockid as cams FROM  tb_partida_articulo GROUP BY tb_partida_articulo.eq_stockid;";
         $ErrMsg = "No se obtuvo datos"; */
        /*$SQL="SELECT DISTINCT stockmaster.eq_stockid as cams FROM stockmaster"; //add
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ($myrow = DB_fetch_array($TransResult)) {

        //$partida[] = array( 'value' => $myrow ['partida'], 'texto' => $myrow ['partida'] );
        $cams[] = array( 'value' => $myrow ['cams'], 'texto' => $myrow ['cams'] );


        }*/

        //$contenido = array('clave' => $clave,'descripcion'=>$descripcion,'cams'=>$cams,'partida'=> $partida);

        //$contenido = array('partida'=> $partida);
       $contenido = array('clave' => $clave,'descripcion'=>$descripcion,'datos1'=>$datos1);
        $result = true;
        break;


    case 'cambioClave':
        $clave=$_POST['clave'];
        $datos= array();

        //$SQL = "SELECT stockmaster.description AS descripcion,stockmaster.eq_stockid AS cams,stockmaster.units as unidadmedida from stockmaster where stockmaster.stockid='".$clave."'";
        $SQL="SELECT stockmaster.description AS descripcion,stockmaster.units as unidad_medida  from stockmaster where stockmaster.stockid='".$clave."'";
        $ErrMsg = "No se obtuvo datos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            //$datos[] = array( 'descripcion' => $myrow ['descripcion'], 'cams' => $myrow ['cams'] );
            $datos[] = array( 'descripcion' => $myrow ['descripcion'], 'unidad_medida' => $myrow ['unidad_medida'] );
        }
        // para obtemer partida
        /*$SQL = "SELECT tb_partida_articulo.partidaEspecifica AS partida FROM stockmaster INNER JOIN tb_partida_articulo ON stockmaster.eq_stockid=tb_partida_articulo.eq_stockid WHERE stockmaster.stockid='".$clave."'";
        $ErrMsg = "No se obtuvo datos";*/

        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $myrow = DB_fetch_array($TransResult);

        //$contenido = array('datos' =>$datos,'partida'=>$myrow['partida']);
        $contenido= array('datos' =>$datos );
        $result = true;
        break;

    case 'cambioDescripcion':
        $clave=$_POST['clave'];
        $datos= array();

        //$SQL = "SELECT stockmaster.stockid AS clave,stockmaster.eq_stockid AS cams ,stockmaster.units as unidad_medida from stockmaster where stockmaster.description='".$clave."'";
        $SQL = "SELECT stockmaster.stockid AS clave,stockmaster.units AS unidad_medida FROM stockmaster WHERE stockmaster.description='".$clave."'";
        //print($SQL);
        $ErrMsg = "No se obtuvo datos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            //  $datos[] = array( 'clave' => $myrow ['clave'], 'cams' => $myrow ['cams'] );
            $datos[] = array( 'clave' => $myrow ['clave'], 'unidad_medida' => $myrow ['unidad_medida'] );
        }

        /*$SQL = "SELECT tb_partida_articulo.partidaEspecifica AS partida FROM stockmaster INNER JOIN tb_partida_articulo ON stockmaster.eq_stockid=tb_partida_articulo.eq_stockid WHERE stockmaster.description='".$clave."'";
        $ErrMsg = "No se obtuvo datos";

        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $myrow = DB_fetch_array($TransResult);*/

        //$contenido = array('datos' =>$datos,'partida'=>$myrow['partida']);
        $contenido = array('datos' =>$datos);
        $result = true;
        break;



    case 'cambioCams':
        $clave=$_POST['clave'];
        $datos= array();

        $SQL = "SELECT stockmaster.stockid AS clave,stockmaster.description AS descripcion FROM stockmaster WHERE eq_stockid='".$clave."'";
        $ErrMsg = "No se obtuvo datos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $datos[] = array( 'clave' => $myrow ['clave'], 'descripcion' => $myrow ['descripcion'] );
        }

        $contenido = array('datos' =>$datos);
        $result = true;
        break;
    case 'solicitudes':
        $articulo=$_POST['articulorequisicion'];
        //$SQL="SELECT  stockid, description FROM stockmaster WHERE `description` like '%".$articulo."%' ORDER BY description";

        /* $SQL=" select
        count(*) as cantidad,
        purchorderdetails.`itemcode`,
        stockmaster.`description`,
        purchorderdetails.`itemdescription`
        from purchorderdetails
        inner join purchorders on   purchorderdetails.`orderno` = purchorders.`orderno`
        inner join stockmaster on stockmaster.`stockid`=purchorderdetails.`itemcode` ,tags,legalbusinessunit,sec_unegsxuser
        where (`itemcode` LIKE '%".$articulo."%' or stockmaster.description LIKE '%".$articulo."%')
        and purchorders.tagref = tags.tagref
        and sec_unegsxuser.tagref = tags.tagref
        and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
        and legalbusinessunit.legalid = tags.legalid

        group by purchorderdetails.`itemcode`
        having count(*)>0
        order by itemdescription
        "; */

        $ErrMsg = "No hay datos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ($myrow = DB_fetch_array($TransResult)) {
            // $dias[] =$myrow['dia'];
            $datos.='<li onClick="fnSelectArticulo(\''.$myrow['description'].'\')">'.$myrow['description'].'</li>';
        }


        $contenido = $datos;
        $result = true;

        break;

    case 'guardarSolicitudAlmacen':
        if (!empty($_POST['datosSolicitud'])) {
            $datos=$_POST['datosSolicitud'];
            $estatus=$_POST['estatus'];
            $nombreEstatus=$_POST['nombreEstatus'];
            $tag=$_POST['tag'];
            $valores='';
            $observaciones=$_POST['observaciones'];
            $unidadEjecutora=$_POST['ue'];
            $transno=0;//$_POST['folio']; //folio del tipo solicitud id 1000
            $almacen=$_POST['almacen'];
           
            $folio=0;
            if(isset($_POST['folio'])){
                $folio=$_POST['folio'];
            }

            if($folio==0){
             $transno = GetNextTransNo($type, $db);
            $SQL = "INSERT INTO tb_solicitudes_almacen (nu_tag,ln_usuario,estatus,nu_folio,txt_observaciones,ln_nombre_estatus,ln_ue,ln_tipo_solicitud) VALUES ('". $tag . "','".$_SESSION ['UserID']."'". ",'".$estatus."','".$transno."','".$observaciones."','".$nombreEstatus."','".$unidadEjecutora."','"."Manual"."')";

            $ErrMsg = "No se agregó agregó solicitud al almacen";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
             }


            if($folio!=0){
            $SQL = "DELETE FROM tb_solicitudes_almacen_detalle WHERE  nu_id_solicitud='".$folio."'";
            $ErrMsg = "No se agregó agregó solicitud al almacen";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            }

            // $folio = DB_Last_Insert_ID($db, 'tb_solicitudes_almacen', 'nu_folio'); // cuando no tenia folio

            /*$articulosClaveOntrasit='';
            $candenaWhenOntrasit='';
            $temp1='';
             $cantidadOntrasit=''; */
            if($folio!=0){
                $transno=$folio;
            }
            for ($a=0; $a<count($datos); $a++) {
                $valores.='('.$transno.",".$datos[$a].",'1'),";
                //print_r($datos[$a]);
                /* PARA ONTRASIT
                $temp1= explode(",", $datos[$a]);
                $articulosClaveOntrasit.=$temp1[1].",";
                $cantidadOntrasit=str_replace("'","",$temp1[0]);
                $candenaWhenOntrasit.=" WHEN stockid=".strtoupper($temp1[1])." THEN (locstock.ontransit + ".$cantidadOntrasit.")"; */
            }
            //$articulosClaveOntrasit=substr( $articulosClaveOntrasit,0,-1);
            $valores=substr($valores, 0, -1);
       
$mensaje=fnChecarDisponibleMultiple($almacen,$_POST['clv'],$db,$_POST['qtA'],$_POST['clvA'],$_POST['renglones']);
            //$SQL = "INSERT INTO tb_solicitudes_almacen_detalle (nu_id_solicitud,nu_cantidad,ln_clave_articulo,ln_cams,ln_partida,txt_descripcion,ln_renglon) VALUES ".$valores;

            $SQL = "INSERT INTO tb_solicitudes_almacen_detalle (nu_id_solicitud,nu_cantidad,ln_clave_articulo,txt_descripcion,ln_unidad_medida,ln_renglon,ln_arctivo) VALUES ".$valores;

            $ErrMsg = "No se agregó agregó solicitud al almacen";
            $TransResult = DB_query($SQL, $db, $ErrMsg);


            /*$SQL=" UPDATE locstock SET   locstock.ontransit = CASE ".  $candenaWhenOntrasit ." END WHERE locstock.stockid IN (".$articulosClaveOntrasit.") AND loccode='"."3"."';";
            $ErrMsg = "No se agregó cambios al almacen";
            $TransResult = DB_query($SQL, $db, $ErrMsg);


            print($SQL); */
            $parte="";
            if($folio!=0){
                $parte= " actualizado ";
            }else{
                $parte= " guardado ";
            }
            $contenido = array('info'=>"<h4>Se ha ".$parte." con éxito la solicitud con folio :<b>".$transno."</b></h4>".$mensaje,'folio'=> $transno);
            $result = true;
        } else {
            $contenido = "Error al crear solicitud inetente más tarde.";
            $result=false;
        }

        break;
///
    case 'agregarAexistente':
        if (!empty($_POST['datosSolicitud'])) {
            $datos=$_POST['datosSolicitud'];
            $transno=$_POST['folio'];
            //$estatus=$_POST['estatus'];
            //$nombreEstatus=$_POST['nombreEstatus'];
            //$tag=$_POST['tag'];
            $valores='';
            //$observaciones=$_POST['observaciones'];

            /* $transno=$_POST['folio']; //folio del tipo solicitud id 1000
             $transno = GetNextTransNo($type, $db);
             $SQL = "INSERT INTO tb_solicitudes_almacen (nu_tag,ln_usuario,estatus,nu_folio,txt_observaciones,ln_nombre_estatus) VALUES ('". $tag . "','".$_SESSION ['UserID']."'". ",'".$estatus."','".$transno."','".$observaciones."','".$nombreEstatus."')";

             $ErrMsg = "No se agregó agregó solicitud al almacen";
             $TransResult = DB_query($SQL, $db, $ErrMsg);*/

            // $folio = DB_Last_Insert_ID($db, 'tb_solicitudes_almacen', 'nu_folio'); // cuando no tenia folio



            for ($a=0; $a<count($datos); $a++) {
                $valores.='('.$transno.",".$datos[$a]."),";
            }

            $valores=substr($valores, 0, -1);


            //$SQL = "INSERT INTO tb_solicitudes_almacen_detalle (nu_id_solicitud,nu_cantidad,ln_clave_articulo,ln_cams,ln_partida,txt_descripcion,ln_renglon) VALUES ".$valores;

            $SQL = "INSERT INTO tb_solicitudes_almacen_detalle (nu_id_solicitud,nu_cantidad,ln_clave_articulo,txt_descripcion,ln_unidad_medida,ln_renglon) VALUES ".$valores;

            $ErrMsg = "No se agregó agregó solicitud al almacen";
            $TransResult = DB_query($SQL, $db, $ErrMsg);


            $contenido = array('info'=>"<h4>Se agrgegó más artículos a la solicitud :<b>".$transno."</b></h4>",'folio'=> $transno);
            $result = true;
        } else {
            $contenido = "Error al crear solicitud inetente más tarde.";
            $result=false;
        }

        break;
///fin agregarAexisten

    case 'eliminarGuardados':
        if (!empty($_POST['datos'])) {
            $datos=$_POST['datos'];
            $transno=$_POST['folio'];
            $valores='(';



            for ($a=0; $a<count($datos); $a++) {
                $valores.="'".$datos[$a]."',";
            }

            $valores=substr($valores, 0, -1);
            $valores.=')';

            $SQL = "DELETE FROM tb_solicitudes_almacen_detalle WHERE ln_renglon IN".$valores. " AND nu_id_solicitud='".$transno."'";

            $ErrMsg = "No se agregó agregó solicitud al almacen";
            $TransResult = DB_query($SQL, $db, $ErrMsg);


            $contenido = array('info'=>"<h4>Se eliminaron algunos  artículos de la solicitud :<b>".$transno."</b></h4>",'folio'=> $transno);
            $result = true;
        } else {
            $contenido = "Error al crear solicitud inetente más tarde.";
            $result=false;
        }

        break;
    case 'guardartodo':
     $data='';
        $almacen='';
        $mensaje='';
        if(isset($_POST['datos'])){
            $datos=$_POST['datos'];
            $almacen=$_POST['almacen'];
            $retorno=fnRecibirDatos($datos);
            $mensaje=fnChecarDisponibleMultiple($almacen,$retorno[1],$db,$retorno[3],$retorno[2],$retorno[0]);

            //if($mensaje==''){

            $datosInsertar='';
            $numeroEstatus='24';//$_POST['estatus'];
            $nombreEstatus='Guardada'; //$_POST['nombreEstatus'];
            $observaciones=$_POST['obs'];
            $unidadEjecutora=$_POST['ue'];
            $tag=$_POST['tag'];
            $folio=$_POST['folio'];
            $valoresNuevos='';
            //         $retorno[]=$renglonesArray;
            // $retorno[]=$clave;
            // $retorno[]=$clavesArray;
            //}
            $SQL = "DELETE FROM tb_solicitudes_almacen_detalle WHERE  nu_id_solicitud='".$folio."'";
            $ErrMsg = "No se agregó agregó solicitud al almacen";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            
            foreach ($retorno[4] as $ad) {
                $valoresNuevos.="('".$folio."',".$ad.",'1'),";
            }
            $valoresNuevos=substr($valoresNuevos, 0, -1);

            $SQL = "INSERT INTO tb_solicitudes_almacen_detalle (nu_id_solicitud,ln_renglon,ln_partida,ln_clave_articulo,txt_descripcion,ln_unidad_medida,nu_cantidad,ln_arctivo) VALUES ".$valoresNuevos;

            $ErrMsg = "No se agregó agregó solicitud al almacen";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            

            $contenido = array('confirmacion'=>"<h4>Se actualizo con éxito la solicitud con folio :<b>".$folio."</b></h4>",'folio'=>$folio,'mensaje' => $mensaje);
            $result = true;
        }else{
            $contenido = array('mensaje' => $mensaje);
            $result = true; 
        }
        // $datos=$_POST['guardar'];
        // $transno=$_POST['folio'];
        // $observaciones=$_POST['observaciones'];
        // /* $candenaWhenOntrasit='';
        //  $articulosClaveOntrasit=''; */
        // if (count($datos)>0) {
        //     for ($a=0; $a<(count($datos)); $a++) {
        //         $columnas= explode(",", $datos[$a]);

        //         /*$articulosClaveOntrasit.=$columnas[3].",";
        //         $candenaWhenOntrasit.=" WHEN stockid=".strtoupper($columnas[3])." THEN (locstock.ontransit + ".$columnas[2].")"; */

        //         $SQL = "SELECT COUNT(*)  AS existe FROM tb_solicitudes_almacen_detalle WHERE nu_id_solicitud ='".$transno."' AND ln_renglon=".$columnas[0];
        //         $ErrMsg = "No se obtuvo datos.";
        //         $TransResult = DB_query($SQL, $db, $ErrMsg);

        //         $filas=DB_fetch_array($TransResult);

        //         if ($filas['existe']>0) {
        //             //$SQL         = "UPDATE  g_cat_geografico SET activo='N' where cg='".$cg."'";
        //             $SQL = "UPDATE  tb_solicitudes_almacen_detalle SET nu_cantidad=".$columnas[2].",ln_clave_articulo=".$columnas[3].", txt_descripcion=".$columnas[4].",ln_unidad_medida=".$columnas[5].",ln_arctivo=".$columnas[1]."   WHERE nu_id_solicitud ='".$transno."'  AND ln_renglon=".$columnas[0]; //. " AND ln_clave_articulo=".$columnas[3];
        //             $ErrMsg = "No se obtuvo datos.";
        //             $TransResult = DB_query($SQL, $db, $ErrMsg);
        //         } else {
        //             $SQL = "SELECT COUNT(*)  AS existe FROM tb_solicitudes_almacen_detalle WHERE nu_id_solicitud ='".$transno."' AND ln_renglon=".$columnas[0];
        //             $ErrMsg = "No se obtuvo datos.";
        //             $TransResult = DB_query($SQL, $db, $ErrMsg);

        //             $filas=DB_fetch_array($TransResult);

        //             if ($filas['existe']>0) {
        //             } else {
        //                 $SQL = "INSERT INTO  tb_solicitudes_almacen_detalle  (nu_id_solicitud,ln_renglon,ln_arctivo,nu_cantidad,ln_clave_articulo,txt_descripcion,ln_unidad_medida) VALUES('". $transno."',".$datos[$a].") ";
        //                 $ErrMsg = "No se obtuvo datos.";
        //                 //print_r($SQL);
        //                 $TransResult = DB_query($SQL, $db, $ErrMsg);

        //                 /*$articulosClaveOntrasit=substr( $articulosClaveOntrasit,0,-1);

        //                 $SQL=" UPDATE locstock SET   locstock.ontransit = CASE ".  $candenaWhenOntrasit ." END WHERE locstock.stockid IN (".$articulosClaveOntrasit.") AND loccode='"."3"."';";
        //                 $ErrMsg = "No se agregó cambios al almacen";
        //                 $TransResult = DB_query($SQL, $db, $ErrMsg); */
        //             }
        //         }
        //     }
        //     $SQL = "UPDATE  tb_solicitudes_almacen SET txt_observaciones='".$observaciones."' WHERE nu_folio='". $transno."'";
        //     $ErrMsg = "No se obtuvo datos.";
        //     $TransResult = DB_query($SQL, $db, $ErrMsg);
        // }
        // /*Array
        // (
        //     [0] => '9'
        //     [1] => '1'
        //     [2] => '17'
        //     [3] => '21110690'
        //     [4] => 'HOJAS DE COLOR VERDE'
        //     [5] => 'PZA'
        // )*/
        // //print_r($columnas);
        // $contenido = "Se actualizo solicitud con folio:".$transno;
        // $result = true;

        break;
    case 'actualizar':
        if ((isset($_POST['eliminar'])) || (isset($_POST['actualizar'])) ||(isset($_POST['guardar']) )) {
            $guardar=$_POST['guardar'];
            $eliminar=$_POST['eliminar'];
            $actualizar=$_POST['actualizar'];
            $transno=$_POST['folio'];
            $valoresEliminar='(';
            $valoresGuardar='';
            //$valoresGuardar='';
            //print_r($eliminar);
            // print_r($guardar);

            if (count($eliminar)>0) {
                for ($a=0; $a<(count($eliminar)); $a++) {
                    $valoresEliminar.="'".$eliminar[$a]."',";
                }

                $valoresEliminar=substr($valoresEliminar, 0, -1);
                $valoresEliminar.=')';

                $SQL = "DELETE FROM tb_solicitudes_almacen_detalle WHERE ln_renglon IN ".$valoresEliminar. " AND nu_id_solicitud='".$transno."'";


                $ErrMsg = "No se agregó agregó solicitud al almacen";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
            }


            if (count($guardar)>0) {
                for ($a=0; $a<(count($guardar)); $a++) {
                    $valoresGuardar.='('.$transno.",". $guardar[$a]."),";
                }

                $valoresGuardar=substr($valoresGuardar, 0, -1);


                //$SQL = "INSERT INTO tb_solicitudes_almacen_detalle (nu_id_solicitud,nu_cantidad,ln_clave_articulo,ln_cams,ln_partida,txt_descripcion,ln_renglon) VALUES ".$valores;

                $SQL = "INSERT INTO tb_solicitudes_almacen_detalle (nu_id_solicitud,nu_cantidad,ln_clave_articulo,txt_descripcion,ln_unidad_medida,ln_renglon) VALUES ".$valoresGuardar;

                $ErrMsg = "No se agregó agregó solicitud al almacen";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
            }

            if (count($actualizar)>1000) {
                for ($a=0; $a<(count($actualizar)); $a++) {
                    // $valoresGuardar.='('.$transno.",". $guardar[$a]."),";
                    $datosA = explode("-", $requisiciones[$a]);
                }

                $valoresGuardar=substr($valoresGuardar, 0, -1);

                /*$valoresLoc.=" WHEN stockid=".strtoupper($articulos[$a])." THEN (locstock.quantity - ".$cantidades[$a].")";

                 $SQL=" UPDATE locstock SET   locstock.quantity = CASE ". $valoresLoc ." END WHERE locstock.stockid IN (".$articlos1.") AND loccode='".$almacen."';" */


                //$SQL = "INSERT INTO tb_solicitudes_almacen_detalle (nu_id_solicitud,nu_cantidad,ln_clave_articulo,ln_cams,ln_partida,txt_descripcion,ln_renglon) VALUES ".$valores;

                /* $SQL = "INSERT INTO tb_solicitudes_almacen_detalle (nu_id_solicitud,nu_cantidad,ln_clave_articulo,txt_descripcion,ln_unidad_medida,ln_renglon) VALUES ".$valoresGuardar; */

                $ErrMsg = "No se agregó agregó solicitud al almacen";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
            }




            $contenido = array('info'=>"<h4>Se actualizo   solicitud de la solicitud :<b>".$transno."</b></h4>",'folio'=> $transno);
            $result = true;
        } else {
            $contenido = "Error al crear solicitud inetente más tarde.";
            $contenido = $SQL;
            $result=false;
        }

        break;
///

    case 'detalleSolicitud':
        $idsolicitud=$_POST['solicitud'];
        $ur=$_POST['ur'];
        $detalle= array();
        $parcial=false;
        $filas=0;
        //cuando sea almacenista
        if (Havepermission($_SESSION ['UserID'], 2293, $db)==1) {
            $SQL = "SELECT COUNT(*)  AS existe FROM tb_salidas_almacen WHERE nu_solicitud ='".$idsolicitud."'";
            $ErrMsg = "No se obtuvo datos.";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $filas=DB_fetch_array($TransResult);

            if ($filas['existe']>0) {
                //echo$filas['existe'];

                $SQL = "SELECT estatus FROM tb_salidas_almacen WHERE nu_solicitud ='".$idsolicitud."'";
                $ErrMsg = "No se obtuvo datos.";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                $estatus=DB_fetch_array($TransResult);

                if ($estatus['estatus']=='parcial') {
                    //$SQL = "SELECT * FROM tb_salidas_almacen_detalle WHERE nu_id_solicitud='".$idsolicitud."' ORDER BY ln_estatus ASC, nu_cantidad ";

                    /*$SQL = "SELECT nu_id_solicitud,nu_cantidad AS solicitada,nu_cantidad_faltante,ln_clave_articulo,txt_descripcion,ln_cams,ln_partida,ln_estatus, SUM(nu_cantidad_entregada) AS entregada FROM tb_salidas_almacen_detalle WHERE nu_id_solicitud='".$idsolicitud."' GROUP BY ln_clave_articulo ORDER BY ln_estatus;";*/
                    // $SQL="SELECT nu_id_solicitud, ln_clave_articulo, txt_descripcion, ln_cams, ln_partida, round(AVG(nu_cantidad),0) AS solicitada, SUM(nu_cantidad_faltante) AS nu_cantidad_faltante, SUM(nu_cantidad_entregada) AS entregada,ln_unidad_medida,ln_renglon FROM tb_salidas_almacen_detalle WHERE nu_id_solicitud='".$idsolicitud."'  GROUP BY nu_id_solicitud, ln_clave_articulo, txt_descripcion, ln_cams, ln_partida,ln_unidad_medida,ln_renglon ORDER BY ln_clave_articulo";
                    $SQL="SELECT tb_salidas_almacen_detalle.nu_id_solicitud, ln_clave_articulo, txt_descripcion, ln_cams, ln_partida, round(AVG(nu_cantidad),0) AS solicitada, SUM(nu_cantidad_faltante) AS nu_cantidad_faltante, SUM(nu_cantidad_entregada) AS entregada,ln_unidad_medida,ln_renglon, tb_solicitudes_almacen.txt_observaciones as observaciones,tb_solicitudes_almacen.ln_ue FROM tb_salidas_almacen_detalle INNER JOIN tb_solicitudes_almacen ON tb_salidas_almacen_detalle.nu_id_solicitud=tb_solicitudes_almacen.nu_folio   WHERE tb_salidas_almacen_detalle.nu_id_solicitud='".$idsolicitud."'  GROUP BY nu_id_solicitud, ln_clave_articulo, txt_descripcion, ln_cams, ln_partida,ln_unidad_medida,ln_renglon,tb_solicitudes_almacen.txt_observaciones,ln_ue ORDER BY ln_clave_articulo";

                    $ErrMsg = "No se obtuvo datos.";
                    $TransResult = DB_query($SQL, $db, $ErrMsg);

                    while ($myrow = DB_fetch_array($TransResult)) {
                        $detalle[] = array( 'cantidad' => $myrow ['solicitada'],'clave' => $myrow ['ln_clave_articulo'],'descripcion' => $myrow ['txt_descripcion'],'cams' => $myrow ['ln_cams'],'partida' => $myrow ['ln_partida'],'cantidadentregada'=> $myrow ['entregada'],'cantidadfaltante'=>$myrow['nu_cantidad_faltante'],'estatus'=>$myrow['ln_estatus'],'unidad_medida' => $myrow ['ln_unidad_medida'],'renglon' => $myrow ['ln_renglon'],'observaciones'=>$myrow['observaciones'],'ue'=>$myrow['ln_ue']); //fnGetUe($ur,$myrow['ln_ue'],$db));
                    } //


                    $parcial=true;
                } else {
                    //$SQL = "SELECT * FROM tb_solicitudes_almacen_detalle WHERE nu_id_solicitud='".$idsolicitud."' ORDER BY ln_renglon ASC";
                    //$ErrMsg = "No se obtuvo datos.";
                    //$TransResult = DB_query($SQL, $db, $ErrMsg);

                    //while ($myrow = DB_fetch_array($TransResult)){
                    //$detalle[] = array( 'cantidad' => $myrow ['nu_cantidad'],'clave' => $myrow ['ln_clave_articulo'],'descripcion' => $myrow ['txt_descripcion'],'cams' => $myrow ['ln_cams'],'partida' => $myrow ['ln_partida']);
                    //}
                    //$SQL="SELECT nu_id_solicitud, ln_clave_articulo, txt_descripcion, ln_cams, ln_partida, round(AVG(nu_cantidad),0) AS solicitada, SUM(nu_cantidad_faltante) AS nu_cantidad_faltante, SUM(nu_cantidad_entregada) AS entregada,ln_unidad_medida,ln_renglon FROM tb_salidas_almacen_detalle WHERE nu_id_solicitud='".$idsolicitud."' AND ln_arctivo='1' GROUP BY nu_id_solicitud, ln_clave_articulo, txt_descripcion, ln_cams, ln_partida,ln_unidad_medida,ln_renglon ORDER BY ln_clave_articulo";
                    $SQL="SELECT tb_salidas_almacen_detalle.nu_id_solicitud, ln_clave_articulo, txt_descripcion, ln_cams, ln_partida, round(AVG(nu_cantidad),0) AS solicitada, SUM(nu_cantidad_faltante) AS nu_cantidad_faltante, SUM(nu_cantidad_entregada) AS entregada,ln_unidad_medida,ln_renglon, tb_solicitudes_almacen.txt_observaciones as observaciones,tb_solicitudes_almacen.ln_ue FROM tb_salidas_almacen_detalle INNER JOIN tb_solicitudes_almacen ON tb_salidas_almacen_detalle.nu_id_solicitud=tb_solicitudes_almacen.nu_folio   WHERE tb_salidas_almacen_detalle.nu_id_solicitud='".$idsolicitud."'  GROUP BY nu_id_solicitud, ln_clave_articulo, txt_descripcion, ln_cams, ln_partida,ln_unidad_medida,ln_renglon,tb_solicitudes_almacen.txt_observaciones,ln_ue ORDER BY ln_clave_articulo";
                    $ErrMsg = "No se obtuvo datos.";
                    $TransResult = DB_query($SQL, $db, $ErrMsg);

                    while ($myrow = DB_fetch_array($TransResult)) {
                        $detalle[] = array( 'cantidad' => $myrow ['solicitada'],'clave' => $myrow ['ln_clave_articulo'],'descripcion' => $myrow ['txt_descripcion'],'cams' => $myrow ['ln_cams'],'partida' => $myrow ['ln_partida'],'cantidadentregada'=> $myrow ['entregada'],'cantidadfaltante'=>$myrow['nu_cantidad_faltante'],'estatus'=>$myrow['ln_estatus'],'unidad_medida' => $myrow ['ln_unidad_medida'],'renglon' => $myrow ['ln_renglon'],'observaciones'=>$myrow['observaciones'],'ue'=>$myrow['ln_ue']); //fnGetUe($ur,$myrow['ln_ue'],$db));
                    } //
                    $parcial=true;
                }//fin si no es parcial
            } else {// si no existe en salidas pero es almacenista

                // $SQL = "SELECT * FROM tb_solicitudes_almacen_detalle WHERE nu_id_solicitud='".$idsolicitud."' ORDER BY ln_renglon ASC";
                $SQL="SELECT tb_solicitudes_almacen_detalle.nu_id_solicitud,tb_solicitudes_almacen_detalle.nu_cantidad,tb_solicitudes_almacen_detalle.ln_clave_articulo,tb_solicitudes_almacen_detalle.txt_descripcion,tb_solicitudes_almacen_detalle.ln_unidad_medida,tb_solicitudes_almacen_detalle.ln_renglon,tb_solicitudes_almacen.nu_tag,tb_solicitudes_almacen.txt_observaciones, tb_solicitudes_almacen.estatus,tb_solicitudes_almacen_detalle.ln_arctivo,tb_solicitudes_almacen.txt_observaciones AS observaciones,tb_solicitudes_almacen.ln_ue FROM tb_solicitudes_almacen_detalle INNER JOIN tb_solicitudes_almacen ON tb_solicitudes_almacen_detalle.nu_id_solicitud= tb_solicitudes_almacen.nu_folio  WHERE tb_solicitudes_almacen_detalle.nu_id_solicitud='".$idsolicitud."' AND tb_solicitudes_almacen_detalle.ln_arctivo='1' ORDER BY ln_clave_articulo ASC";
                $ErrMsg = "No se obtuvo datos.";
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                while ($myrow = DB_fetch_array($TransResult)) {
                    //$detalle[] = array( 'cantidad' => $myrow ['nu_cantidad'],'clave' => $myrow ['ln_clave_articulo'],'descripcion' => $myrow ['txt_descripcion'],'cams' => $myrow ['ln_cams'],'partida' => $myrow ['ln_partida']);
                    $detalle[] = array( 'cantidad' => $myrow ['nu_cantidad'],'clave' => $myrow ['ln_clave_articulo'],'descripcion' => $myrow ['txt_descripcion'],'unidad_medida' => $myrow ['ln_unidad_medida'],'renglon'=>$myrow ['ln_renglon'],'folio'=>$myrow ['nu_folio'],'observaciones'=>$myrow['observaciones'],'ue'=>$myrow['ln_ue']); //fnGetUe($ur,$myrow['ln_ue'],$db));
                } // no existe en salidas aun no se ha entregado nada
            }//fin si no exite en salida
        } else { // si no es almacenista

            //$SQL = "SELECT * FROM tb_solicitudes_almacen_detalle WHERE nu_id_solicitud='".$idsolicitud."' ORDER BY ln_renglon ASC";
            $SQL="SELECT tb_solicitudes_almacen_detalle.nu_id_solicitud,tb_solicitudes_almacen_detalle.nu_cantidad,tb_solicitudes_almacen_detalle.ln_clave_articulo,tb_solicitudes_almacen_detalle.txt_descripcion,tb_solicitudes_almacen_detalle.ln_unidad_medida,tb_solicitudes_almacen_detalle.ln_renglon,tb_solicitudes_almacen.nu_tag,tb_solicitudes_almacen.txt_observaciones, tb_solicitudes_almacen.estatus,tb_solicitudes_almacen.ln_ue FROM tb_solicitudes_almacen_detalle INNER JOIN tb_solicitudes_almacen ON tb_solicitudes_almacen_detalle.nu_id_solicitud= tb_solicitudes_almacen.nu_folio  WHERE tb_solicitudes_almacen_detalle.nu_id_solicitud='".$idsolicitud."' AND tb_solicitudes_almacen_detalle.ln_arctivo='1'  ORDER BY ln_renglon ASC";

            $ErrMsg = "No se obtuvo datos.";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            while ($myrow = DB_fetch_array($TransResult)) {
                //$detalle[] = array( 'cantidad' => $myrow ['nu_cantidad'],'clave' => $myrow ['ln_clave_articulo'],'descripcion' => $myrow ['txt_descripcion'],'cams' => $myrow ['ln_cams'],'partida' => $myrow ['ln_partida']);
                $detalle[] = array( 'cantidad' => $myrow ['nu_cantidad'],'clave' => $myrow ['ln_clave_articulo'],'descripcion' => $myrow ['txt_descripcion'],'unidad_medida' => $myrow ['ln_unidad_medida'],'renglon' => $myrow ['ln_renglon'],'observaciones'=>$myrow['txt_observaciones'],'estatus'=>$myrow['estatus'],'folio'=>$myrow ['nu_folio'],'ue'=>$myrow['ln_ue']); //fnGetUe($ur,$myrow['ln_ue'],$db));
            }
        }//  si no es almacenista

        $datos1='<span class="multiselect-native-select"><select id="selectCveArticuloX3" name="selectCveArticuloX3" class="claveArticulo" required=""><option value="0">Seleccionar...</option><option value="0500">0500</option><option value="21110251">21110251</option></select><div class="btn-group"><button type="button" class="multiselect dropdown-toggle btn btn-default" data-toggle="dropdown" title="Seleccionar..."><span class="multiselect-selected-text">Seleccionar...</span> <b class="caret"></b></button><ul class="multiselect-container dropdown-menu" style="max-height: 200px; overflow-y: scroll;"><li class="active"><a tabindex="0"><label class="radio"><input type="radio" value="0"> Seleccionar...</label></a></li><li><a tabindex="0"><label class="radio"><input type="radio" value="0500"> 0500</label></a></li><li><a tabindex="0"><label class="radio"><input type="radio" value="21110251"> 21110251</label></a></li></ul></div></span>';
       
        if (count($detalle)>0) {
            $contenido =array('detalle' => $detalle,'parcial'=>$parcial,'datos1'=>$datos1);
        } else {
            $contenido =array('detalle' => "No hay información sobre esta solicitud.");
        }
        $result = true;




        break;
    case 'ver':
        $datos=array();
        //Ur,solicitud,fecha,clave,descripcion,cantidad,,eststus
        //$SQL = "SELECT tb_solicitudes_almacen.nu_folio, tb_solicitudes_almacen.dtm_fecharegistro,tb_solicitudes_almacen.nu_tag,tb_solicitudes_almacen.ln_usuario,tb_solicitudes_almacen.ln_nombre_estatus,tags.tagdescription
        //      FROM tb_solicitudes_almacen INNER JOIN tags ON tags.tagref=tb_solicitudes_almacen.nu_tag";
        $SQL="SELECT DISTINCT tb_solicitudes_almacen.nu_folio, tb_solicitudes_almacen.dtm_fecharegistro,tb_solicitudes_almacen.nu_tag,tb_solicitudes_almacen.ln_usuario,tb_solicitudes_almacen.estatus,tb_solicitudes_almacen.ln_nombre_estatus,tb_solicitudes_almacen.txt_observaciones,tags.tagdescription,
            (SELECT SUM(tb_solicitudes_almacen_detalle.nu_cantidad) FROM tb_solicitudes_almacen_detalle where tb_solicitudes_almacen_detalle.nu_id_solicitud=tb_solicitudes_almacen.nu_folio) as cantidad,
            (SELECT COUNT(*) FROM tb_solicitudes_almacen_detalle where tb_solicitudes_almacen_detalle.nu_id_solicitud=tb_solicitudes_almacen.nu_folio ) as articulos,tb_solicitudes_almacen.ln_tipo_solicitud
            FROM tb_solicitudes_almacen 
            INNER JOIN tb_solicitudes_almacen_detalle ON tb_solicitudes_almacen.nu_folio=tb_solicitudes_almacen_detalle.nu_id_solicitud
            INNER JOIN tags ON tags.tagref=tb_solicitudes_almacen.nu_tag";


        $perfil=fnChecarPerfilPorFuncion($db);
        switch ($perfil) {
            case 'capt':
                $SQL.=" WHERE    tb_solicitudes_almacen.ln_usuario='".$_SESSION ['UserID']."'";
                break;

            case 'val':
                //$SQL.=" WHERE    tb_solicitudes_almacen.estatus='41'";
                //$SQL.=" WHERE    tb_solicitudes_almacen.ln_usuario='".$_SESSION ['UserID']."'";
                $SQL.=" ";
                break;

            case 'aut':
                //$SQL.=" WHERE    tb_solicitudes_almacen.estatus='43'";
                // $SQL.=" WHERE    tb_solicitudes_almacen.ln_usuario='".$_SESSION ['UserID']."'";
                $SQL.=" ";
                break;

            case 'alm': // 45
                $SQL.=" WHERE    tb_solicitudes_almacen.estatus='30' OR tb_solicitudes_almacen.estatus='47'"; //47 cerrada
                break;

            default:
                break;
        }

        // $SQL.= " AND tb_solicitudes_almacen_detalle.ln_arctivo='1' " ;
        $SQL.=" ORDER BY tb_solicitudes_almacen.nu_id_solicitud DESC";

        $ErrMsg = "No se obtuvo datos.";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $liga='';
        $i=0;
        while ($myrow = DB_fetch_array($TransResult)) {
            $liga='';
            if ($myrow['estatus']!=0 && $myrow['estatus']!=47) {
                //$liga='href="javascript:fnCargaDetalle("'.$myrow['nu_folio'].'");';
                $liga="<a id='ligasolicitud".$i."' href='"."javascript:fnCargaDetalle(".$myrow['nu_folio'].",".$myrow['estatus'].");'  style='color: blue;'><u>".$myrow['nu_folio']."</u></a>";
            } else {
                $liga=$myrow['nu_folio'];
            }
            $datos[] = array('id1'=>false,
                // 'idsolicitud' =>"<div class='enlaceDetalle' ><a   style='color: blue;'><u>".$myrow['nu_id_solicitud']."</u></a></div>",
                'idsolicitudsinliga' =>$myrow['nu_folio'],
                //'idsolicitud' =>"<a href='"."javascript:fnCargaDetalle(".$myrow['nu_folio'].");'  style='color: blue;'><u>".$myrow['nu_folio']."</u></a>",  //liga
                'idsolicitud' =>$liga,  //liga
                'tag'=>$myrow['nu_tag'].' '.$myrow['tagdescription'],
                'fecha'=>date("d-m-Y", strtotime($myrow['dtm_fecharegistro'])),
                'numeroart'=>$myrow['articulos'],
                'cantidad'=>$myrow['cantidad'],
                'estatus'=>$myrow['ln_nombre_estatus'],
                'tipoSol'=>$myrow['ln_tipo_solicitud'],
                'numeroestatus'=>$myrow['estatus'],
                'observaciones'=>$myrow['txt_observaciones'],
                'ue'=>$myrow['ln_ue'],
            );

            //'fecha'=>fnReemplazar(date("F j, Y, g:i a", strtotime($myrow['dtm_fecharegistro']))));

            /*$datos.='<tr id="fila'.$myrow['nu_id_solicitud'].'">';
            $datos.='<td>'.$myrow['nu_tag'].' '.$myrow['tagdescription'] . '</td>'.'<td>'.$myrow['dtm_fecharegistro'] . '</td>'.'<td  ><span class="detalleSolicitud"  id="solicitud'.$myrow['nu_id_solicitud'] .'">Detalle</span></td>';
            $datos.="</tr>"; */

            // 'idsolicitud' =>"<button  class='enlaceDetalle'  href='"."javascript:fnCargaDetalle(".$myrow['nu_id_solicitud'].");'  style='color: blue;'><u>".$myrow['nu_id_solicitud']."</u></button>",  //liga
            $i++;
        }


        $funcion = 2291;
        $nombre='Almacen'; //traeNombreFuncionGeneral($funcion, $db);
        $nombre=str_replace(" ", "_", $nombre);
        $nombreExcel = $nombre.'_'.date('dmY');
        //$contenido = array('datos' => $datos, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid,'nombreExcel' => $nombreExcel);
        $contenido = array('datos' => $datos,'nombreExcel' => $nombreExcel);

        $result = true;

        /*if(count($detalle)>0){

        $contenido =array('detalle' => $detalle);
        }else{
        $contenido ="No hay información sobre esta solicitud.";
        } */

        break;

    case 'avanzar':
        $estatus= $_POST['estatus'];
        $solicitudes=$_POST['solicitudes'];
        $nomreEstatus=$_POST['nombreEstatus'];
        $valores='';
        for ($a=0; $a<(count($solicitudes)); $a++) {
            $valores.="'".$solicitudes[$a]."',";
        }
        $valores=substr($valores, 0, -1);

        $SQL = "UPDATE  tb_solicitudes_almacen SET estatus='".$estatus."', ln_nombre_estatus='".$nomreEstatus."' WHERE nu_folio IN (".$valores.") "; // nu_folio='".$solicitud."'";

        $ErrMsg = "No se actualizao el estatus.";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $contenido = "La solicitud fue mandad al siguiente estatus.";
        $result = true;

        if($estatus=='30'){
            $SQL="   SELECT ln_clave_articulo,nu_cantidad FROM tb_solicitudes_almacen_detalle WHERE nu_id_solicitud IN (".$valores.") ORDER BY ln_renglon ASC;";
            $ErrMsg = "No se agregó cambios al almacen";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $articulosClaveOntrasit='';
            while ($myrow = DB_fetch_array($TransResult)) {
                // $detalle[] = array( 'cantidad' => $myrow ['solicitada'],'clave' => $myrow ['ln_clave_articulo'],'descripcion' => $myrow ['txt_descripcion'],'cams' => $myrow ['ln_cams'],'partida' => $myrow ['ln_partida'],'cantidadentregada'=> $myrow ['entregada'],'cantidadfaltante'=>$myrow['nu_cantidad_faltante'],'estatus'=>$myrow['ln_estatus'],'unidad_medida' => $myrow ['ln_unidad_medida'],'renglon' => $myrow ['ln_renglon'],'observaciones'=>$myrow['observaciones']);
                /* PARA ONTRASIT */

                $articulosClaveOntrasit.="'".($myrow['ln_clave_articulo'])."',";
                $candenaWhenOntrasit.=" WHEN stockid='".strtoupper($myrow['ln_clave_articulo'])."' THEN (locstock.ontransit + ".($myrow['nu_cantidad']).")";
            }
            $articulosClaveOntrasit=substr( $articulosClaveOntrasit,0,-1);
            $SQL=" UPDATE locstock SET   locstock.ontransit = CASE ".  $candenaWhenOntrasit ." END WHERE locstock.stockid IN (".$articulosClaveOntrasit.") AND loccode='"."3"."';";
            $ErrMsg = "No se agregó cambios al almacen";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

        }

        break;

    case 'obtenerBotones':
        $datos = array();
        $SQL = "SELECT 
            DISTINCT 
            tb_botones_status.statusid,
            tb_botones_status.statusname,
            tb_botones_status.namebutton,
            tb_botones_status.functionid,
            tb_botones_status.sn_estatus_siguiente,
            tb_botones_status.clases,
            tb_botones_status.sn_orden,
            tb_botones_status.id
               
            FROM tb_botones_status
            JOIN sec_profilexuser ON sec_profilexuser.userid = '".$_SESSION['UserID']."'
            JOIN sec_funxprofile ON sec_funxprofile.profileid = sec_profilexuser.profileid
             WHERE 
            (tb_botones_status.sn_funcion_id=2292)
            AND
            (tb_botones_status.functionid = sec_funxprofile.functionid 
            OR 
            tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid)
            )
           
            ORDER BY tb_botones_status.sn_orden asc
            ";
        $ErrMsg = "No se obtuvieron los botones para el proceso";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $cancelar='0';
        $rechazar='0';
        $avanzar='0';

        $status_cancelar='';
        $status_rechazada='';
        $status_avanzar='';

        $siguiente='';
        $estatusnombre='';
        $perfil=fnChecarPerfilPorFuncion($db);
        // $perfil;
        //exit;
        $perfil1=fnChecarPerfilPorFuncion($db);
        while ($myrow = DB_fetch_array($TransResult)) {
            switch ($perfil) {
                case 'capt':
                    $rechazar='0';
                    $status_cancelar='Cancelada por capturista';
                    $status_avanzar='Avanzada al validador';
                    $avanzar='41';
                    break;

                case 'val':
                    $rechazar="24";
                    $avanzar='43';
                    $status_cancelar='Cancelada por validador';
                    $status_rechazada='Rechazada por validador';
                    $status_avanzar='Avanzada al autorizador';
                    break;

                case 'aut':
                    $rechazar="41";
                    $avanzar='30';//45
                    $status_cancelar=' Cancelada por autorizador';
                    $status_rechazada='Rechazada por autorizador';
                    //$status_avanzar='En almacén.';
                    break;

                case 'alm':
                    //$rechazar="43";
                    $status_cancelar='Cancelada por almacenista';

                    break;

                default:
                    break;
            }
            switch ($myrow ['statusid']) {
                case '27':
                    $siguiente=$avanzar;
                    $estatusnombre= $status_avanzar;
                    break;
                case '30':
                    $siguiente=$avanzar;
                    $estatusnombre='En almacén';
                    break;

                case '31':
                    $siguiente=$rechazar;
                    $estatusnombre= $status_rechazada;
                    break;

                case '0':
                    $siguiente=$cancelar;
                    $estatusnombre=$status_cancelar;
                    break;

                default:
                    # code...
                    break;
            }
            if($myrow ['statusid']=='27'){


                if($perfil1!='aut' && $perfil1!='alm'){
                    $datos[] = array(
                        'id' =>$myrow ['id'],
                        'statusid' =>$myrow ['statusid'],
                        'statusnext'=> $siguiente,
                        'statusname' => $estatusnombre,
                        'namebutton' => $myrow ['namebutton'],
                        'functionid' => $myrow ['functionid'],
                        'clases' => $myrow ['clases']
                    );
                }
            }else if($myrow ['statusid']!='47'){
                $datos[] = array(
                    'id' =>$myrow ['id'],
                    'statusid' =>$myrow ['statusid'],
                    'statusnext'=> $siguiente,
                    'statusname' => $estatusnombre,
                    'namebutton' => $myrow ['namebutton'],
                    'functionid' => $myrow ['functionid'],
                    'clases' => $myrow ['clases']
                );
            }
        }//  fin while

        $contenido = array('datos' => $datos);
        $result = true;

        break;
    case 'checarDisponible': // cuando agrego un artiuclo
        $datos = array();
        $articulosSolicitud=$_POST['articulosSolicitud'];
        $almacen=$_POST['almacen'];
        $perfil= fnChecarPerfilPorFuncion($db);
        $SQL='';
        if($perfil=='alm'){
            $SQL="SELECT (quantity) AS disponible, stockid  FROM  locstock WHERE stockid IN (".$articulosSolicitud.") and loccode ='".$almacen."' ORDER BY stockid ASC";

        } else{
            $SQL="SELECT (quantity- ontransit) AS disponible, stockid  FROM  locstock WHERE stockid IN (".$articulosSolicitud.") and loccode ='".$almacen."' ORDER BY stockid ASC";

        }

        $ErrMsg = "No se obtuvieron disponibles";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $datos[] = $myrow ['disponible'];
        }
        $contenido = array('datos' => $datos);
        $result = true;
        break;

    case 'checarDisponibleMultiple':// cuando se guardar las actualizaciones o   todos los articulos de la solicitud
        $datos = array();
        $articulos=$_POST['articulos'];
        $almacen=$_POST['almacen'];

        $perfil= fnChecarPerfilPorFuncion($db);
        $SQL='';
        if($perfil=='alm'){
            $SQL="SELECT (quantity) AS disponible, stockid  FROM  locstock WHERE stockid IN (".$articulos.") and loccode ='".$almacen."' ORDER BY stockid ASC";

        } else{
            $SQL="SELECT (quantity- ontransit) AS disponible, stockid  FROM  locstock WHERE stockid IN (".$articulos.") and loccode ='".$almacen."' ORDER BY stockid ASC";

        }

        // $SQL="SELECT (quantity- ontransit) AS disponible, stockid  FROM  locstock WHERE stockid IN (".$articulos.") and loccode ='".$almacen."' ORDER BY stockid ASC";
        $ErrMsg = "No se obtuvieron disponibles";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ($myrow = DB_fetch_array($TransResult)) {
            $datos[] =  array('stockid'=>$myrow ['stockid'],'disponible'=>$myrow ['disponible']);
        }

        $contenido = array('articulos' => $datos);
        $result = true;
        break;

    case 'checarDisponibleAntesDeAvanzar':// cuando se guardar las actualizaciones o   todos los articulos de la solicitud
        $datos = array();
        //$articulos=$_POST['articulos'];
        $almacen=$_POST['almacen'];
        $folio=$_POST['solicitud'];


        /*$SQL="SELECT tb_solicitudes_almacen_detalle.ln_renglon,locstock.stockid,tb_solicitudes_almacen_detalle.txt_descripcion,
 CASE
 WHEN tb_solicitudes_almacen_detalle.nu_cantidad> (locstock.quantity- locstock.ontransit)  THEN  'NO DISPONIBLE'
 ELSE  'DISPONIBLE'
 END AS EXISTE_DISPONIBLE
FROM tb_solicitudes_almacen_detalle   INNER JOIN locstock ON tb_solicitudes_almacen_detalle.ln_clave_articulo=  locstock.stockid WHERE stockid IN (SELECT tb_solicitudes_almacen_detalle.ln_clave_articulo  FROM tb_solicitudes_almacen_detalle WHERE tb_solicitudes_almacen_detalle.nu_id_solicitud='219' )
AND tb_solicitudes_almacen_detalle.nu_id_solicitud='219'
AND  tb_solicitudes_almacen_detalle.ln_arctivo='1'
AND loccode ='3' ORDER BY tb_solicitudes_almacen_detalle.ln_renglon ";
*/
        $SQL="SELECT /*tb_solicitudes_almacen_detalle.nu_cantidad AS cantidad ,
(locstock.quantity- locstock.ontransit)  AS disponible,*/
tb_solicitudes_almacen_detalle.ln_renglon,
locstock.stockid,
tb_solicitudes_almacen_detalle.txt_descripcion,
 CASE WHEN tb_solicitudes_almacen_detalle.nu_cantidad> (locstock.quantity- locstock.ontransit)  THEN  'NO DISPONIBLE' 
 ELSE  'DISPONIBLE' 
 END AS EXISTE_DISPONIBLE
FROM tb_solicitudes_almacen_detalle   
INNER JOIN locstock ON tb_solicitudes_almacen_detalle.ln_clave_articulo=  locstock.stockid AND locstock.loccode= 3
WHERE tb_solicitudes_almacen_detalle.nu_id_solicitud='".$folio."'
AND  tb_solicitudes_almacen_detalle.ln_arctivo='1'
AND loccode ='3'";
        $ErrMsg = "No se obtuvieron disponibles";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ($myrow = DB_fetch_array($TransResult)) {
            $datos[] =  array('renglon'=>$myrow ['ln_renglon'],'descripcion'=>$myrow ['txt_descripcion'],'clave'=>$myrow ['stockid'],'disponible'=>$myrow ['EXISTE_DISPONIBLE']);
        }

        $contenido = array('articulos' => $datos);
        $result = true;
        break;

    case 'salidafolio':
        $transno = GetNextTransNo(($type+1), $db); //1001 salida de almacen
        $contenido =$transno;
        $result = true;
        break;

    case 'existeSalida':
        $datos=array();
        $idsolicitud=$_POST['idsolicitud'];
        /*$SQL = "SELECT COUNT(*)  AS existe FROM tb_salidas_almacen WHERE nu_solicitud ='".$idsolicitud."'";
        $ErrMsg = "No se obtuvo datos.";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $filas=DB_fetch_array($TransResult); */
        $filas= fnExisteSalida($idsolicitud,$db);

        if ($filas>0) {
            $SQL = "SELECT * FROM tb_salidas_almacen WHERE nu_solicitud='".$idsolicitud."' ORDER BY dtm_fecharegistro DESC";
            $ErrMsg = "No se obtuvo datos.";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            while ($myrow = DB_fetch_array($TransResult)) {
                $datos[] =  array(

                    'idsolicitudsinliga' =>$myrow['nu_folio'],
                    'idsolicitud' =>"<a href='"."javascript:fnImprimirSalida(".$myrow['nu_folio'].",".$myrow['nu_solicitud'].");'  style='color: blue;'><u>".$myrow['nu_folio']."</u></a>", //liga
                    'tag'=>$myrow['nu_tag'],
                    'fecha'=>date("d-m-Y", strtotime($myrow['dtm_fecharegistro'])));
            }
            $funcion = 2291;
            $nombre='Salidas'; //traeNombreFuncionGeneral($funcion, $db);
            $nombre=str_replace(" ", "_", $nombre);
            $nombreExcel = $nombre.'_'.date('dmY');
            $contenido = array('salidas' => $datos,'nombreExcel'=>$nombreExcel);
        } else {
            $contenido = array('salidas' =>"No existen salidas para poder imprimir formato.");
        }

        $result = true;

        break;

    case 'salidas':
        $datos=$_POST['datos'];
        $idsolicitud=$_POST['idsolicitud'];
        $estatus=$_POST['estatus'];
        $salidaFolio=$_POST['folio'];
        $tag=$_POST['ur'];
        $almacen=$_POST['almacen'];
        $cerrar=$_POST['cerrar'];

        $SQL = "INSERT INTO tb_salidas_almacen (nu_solicitud,nu_tag,ln_usuario,estatus,nu_folio) VALUES ('".$idsolicitud."','". $tag . "','".$_SESSION ['UserID']."'". ",'".$estatus."','".$salidaFolio."')";

        $ErrMsg = "No se agregó agregó solicitud al almacen";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $SQL = "INSERT INTO tb_salidas_almacen_detalle (nu_id_solicitud,nu_cantidad,nu_cantidad_entregada,nu_cantidad_faltante,ln_clave_articulo,txt_descripcion,ln_cams,ln_partida,ln_estatus,nu_folio,ln_unidad_medida,ln_renglon) VALUES ".$datos;
        $ErrMsg = "No se agregó agregó solicitud al almacen";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        //stockmoves y locstock
        $valores='';
        $valoresOntrasit='';
        $valoresGltransCargo='';
        $valoresGltransAbono='';
        $valoresOntrasit='';
        $diaP= date('d');
        $mesP= date('m');
        $anioP= date('Y');
        $PeriodNo ='';
        $transno='';
        $PeriodNo = GetPeriod($diaP.'/'.$mesP.'/'.$anioP, $db);
        $transno = GetNextTransNo($type, $db);
        $articulos= explode(',', $_POST['articulos']);
        $articlos1=$_POST['articulos'];
        $cantidades= explode(',', $_POST['cantidades']);
        $ur=$_POST['ur'];

        $cargo='';
        $abono='';

        $estavgcostXlegal=0;
        $cantidagtrans=1;
        for ($a=0; $a<count($articulos); $a++) {
            $estavgcostXlegal=StockAvgcostXLegal(str_replace("'", "", $articulos[$a]), $ur, $db);
            $x=str_replace("'", "", $cantidades[$a]);
            $valores.="(".strtoupper($articulos[$a]).",'".($type+1)."','".$salidaFolio."',"."'".$almacen."'".",'".$anioP.'-'.$mesP.'-'.$diaP."','".$PeriodNo."','ALMACÉN GENERAL','".($x*-1)."',"."'0',"."'".$estavgcostXlegal."',"."'".$estavgcostXlegal."',"."'".$ur."','00'),";

            $valoresLoc.=" WHEN stockid=".strtoupper($articulos[$a])." THEN (locstock.quantity - ".$cantidades[$a].")";
            $valoresOntrasit.=" WHEN stockid=".strtoupper($articulos[$a])." THEN (locstock.ontransit - ".$cantidades[$a].")";

                //adjglact  //stockact
            $SQL="SELECT DISTINCT stockcategory.adjglact,stockcategory.ln_abono_salida FROM stockmaster INNER JOIN stockcategory  ON stockmaster.categoryid=stockcategory.categoryid WHERE stockid =".$articulos[$a];
            $ErrMsg = "No se obtuvo datos.";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            while ($myrow = DB_fetch_array($TransResult)) {
                $cargo=$myrow ['adjglact'];
                $abono=$myrow['ln_abono_salida'];
            }

            $cantidagtrans=str_replace("'", "", $cantidades[$a]);
            $cantidagtrans=(float)$cantidagtrans;

            $cantidagtrans=($cantidagtrans*$estavgcostXlegal);
            //los articulos ya viene con '
            $valoresGltransCargo.="('".($type+1)."','".$salidaFolio."','".$anioP.'-'.$mesP.'-'.$diaP."','".$PeriodNo."','".$cargo."','"."narritiva cargo"."','".(1*($cantidagtrans))."','".$ur."','".$_SESSION ['UserID']."',".strtoupper($articulos[$a]).",'1','00'),";
            $valoresGltransAbono.="('".($type+1)."','".$salidaFolio."','".$anioP.'-'.$mesP.'-'.$diaP."','".$PeriodNo."','".$abono."','"."narritiva abono"."','".(-1*($cantidagtrans))."','".$ur."','".$_SESSION ['UserID']."',".strtoupper($articulos[$a]).",'1','00'),";

            //type,typeno,trandate,periodno,account,narrative,amount,tag,userid,stockid
        }
        $valores=substr($valores, 0, -1); //para quitar la ultima coma
        $valoresGltransCargo=substr($valoresGltransCargo, 0, -1);
        $valoresGltransAbono=substr($valoresGltransAbono, 0, -1);

        //falta  ue
        $SQL = "INSERT INTO stockmoves ( stockid, type, transno, loccode, trandate, prd,reference, qty,newqoh, standardcost,avgcost,tagref,ln_ue) VALUES ".$valores;
        $ErrMsg = "No se agregó  movimiento al ahora de surtir";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $SQL=" UPDATE locstock SET   locstock.quantity = CASE ". $valoresLoc ." END WHERE locstock.stockid IN (".$articlos1.") AND loccode='".$almacen."';";
        $ErrMsg = "No se agregó cambios al almacen";
        $TransResult = DB_query($SQL, $db, $ErrMsg);


        $SQL=" UPDATE locstock SET   locstock.ontransit = CASE ".$valoresOntrasit ." END WHERE locstock.stockid IN (".$articlos1.") AND loccode='".$almacen."';";
        $ErrMsg = "No se agregó cambios al almacen";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        //posted en 1
        $SQL="INSERT INTO  gltrans(type,typeno,trandate,periodno,account,narrative,amount,tag,userid,stockid,posted,ln_ue) VALUES ".$valoresGltransCargo;
        $ErrMsg = "No se agregó cambios al almacen";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $SQL="INSERT INTO  gltrans(type,typeno,trandate,periodno,account,narrative,amount,tag,userid,stockid,posted,ln_ue) VALUES ".$valoresGltransAbono;
        $ErrMsg = "No se agregó cambios al almacen";
        $TransResult = DB_query($SQL, $db, $ErrMsg);


        /*$SQL = "UPDATE locstock SET locstock.quantity = locstock.quantity - " . $TrfLine->Quantity . " WHERE stockid='" . strtoupper($TrfLine->StockID) . "'AND loccode='3'"*/

        //stock moves
        /*$SQL = "INSERT INTO stockmoves ( stockid, type, transno, loccode, trandate, prd,reference, qty,newqoh, standardcost,avgcost,tagref)".
        " VALUES ('" . strtoupper($TrfLine->StockID) . "',
                 '16',
                            " . $_SESSION['Transfer']->TrfID . ",
                            '" . $_SESSION['Transfer']->StockLocationTo . "',
                            '" . $SQLTransferDate . "'," . $PeriodNo . ",
                            '" . _('From') . ' ' . $_SESSION['Transfer']->StockLocationFromName ."',
                            " . $TrfLine->Quantity . ", " . ($QtyOnHandPrior + $TrfLine->Quantity) . ",
                            " . $standardcost . ",
                            " . $EstimatedAvgCost. ",
                            " . $unidaddenegocio. "
                            )"; */

        if ($cerrar==1) {
            //$estatus= $_POST['estatus'];
            $solicitud=$idsolicitud;
            // $nomreEstatus=$_POST['nombreEstatus'];
            $SQL = "UPDATE  tb_solicitudes_almacen SET estatus='47', ln_nombre_estatus='"."Cerrada por almacenista."."' WHERE nu_folio='".$solicitud."'";

            $ErrMsg = "No se actualizao el estatus.";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
        }

        fnEstatusParcialesCompleto($idsolicitud,$db);
        $contenido = "Se envió con éxito la solicitud al almacen.";
        $result = true;



        break;

    case 'datosReporte':
        $datos=array();
        $solicitud=$_POST['solicitud'];
        $filas=fnExisteSalida($solicitud,$db);

        if($filas>0){
            $SQL="SELECT dtm_fecharegistro AS fecha,ln_usuario AS usuario FROM tb_solicitudes_almacen WHERE nu_folio='".$solicitud."'";

            $ErrMsg = "No se obtuvieron datos.";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            while ($myrow = DB_fetch_array($TransResult)) {
                $datos[] = array(
                    'fecha' => $myrow ['fecha'],
                    'usuario'=> $myrow['usuario']
                );
            }
            //$myrow['usuario']

            $contenido = array('datos' => $datos);
        }else{
            $datos[] = array('fecha' => 'No existen salidas para poder imprimir formato.' );
            $contenido = array('datos' => $datos );
        }

        $result = true;
        break;

        break;

    case 'filtrado':
        $anioActual=date('Y');
        $mesActual=date('m');
        $dependencia = $_POST['dependencia'];
        $unidadresp= $_POST["unidadresp"];
        $unidadeje= $_POST["unidadeje"];
        $solicitud= $_POST["solicitud"];
        $estatus= $_POST["estatus"];
        $dateDesde='';
        $dateHasta='';
        $condicion= "";

        // separar la seleccion multiple de la dependencia
        $datosDependencia = "";
        foreach ($dependencia as $key) {
            if (empty($datosDependencia)) {
                $datosDependencia .= "'".$key."'";
            } else {
                $datosDependencia .= ", '".$key."'";
            }
        }

        $datosUR = "";
        foreach ($unidadresp as $key) {
            if (empty($datosUR)) {
                $datosUR .= "'".$key."'";
            } else {
                $datosUR .= ", '".$key."'";
            }
        }

        if (!empty($datosDependencia)) {
            $condicion .= " AND tags.legalid IN (".$datosDependencia.") ";
        }

        if (!empty($datosUR)) {
            $condicion.= " AND tb_solicitudes_almacen.nu_tag IN (".$datosUR.") ";
        }

        if (is_array($estatus)) {
            $condicion.= " AND tb_solicitudes_almacen.estatus IN (".implode(",", $estatus).") ";
        }

        if (is_array($unidadeje)) {
            $condicion.= " AND tb_solicitudes_almacen.ln_ue IN (".implode(",", $unidadeje).") ";
        } elseif ($unidadeje != -1) {
            $condicion.= " AND tb_solicitudes_almacen.ln_ue= '".$unidadeje."' ";
        }  //LALO

        if (!empty($solicitud)) {
            $condicion.= " AND tb_solicitudes_almacen.nu_folio= '".$solicitud."' ";
        }

        if (!empty($_POST['dateDesde'])) {
            $dateDesde= date("Y-m-d", strtotime($_POST['dateDesde']));
        } else {
            $dateDesde=0;//$anioActual.'-'.$mesActual.'-'.'01';
        }

        if (!empty($_POST['dateHasta'])) {
            $dateHasta= date("Y-m-d", strtotime($_POST['dateHasta']));
        } else {
            $dateHasta=0;//$anioActual.'-'.$mesActual.'-'.'31';
        }

        // $condicion .= " AND tb_solicitudes_almacen.dtm_fecharegistro >= '".$dateDesde." 00:00:00' AND tb_solicitudes_almacen.dtm_fecharegistro <='".$dateHasta." 23:59:59' ";
        if($dateDesde!=0 && $dateHasta!=0){
            //$condicion .= " AND purchorders.orddate between '".$fechaini." 00:00:00' AND '".$fechafin." 23:59:59' ";
       $condicion .=" AND tb_solicitudes_almacen.dtm_fecharegistro  >='" . $dateDesde . " 00:00:00' 
           and tb_solicitudes_almacen.dtm_fecharegistro <='" . $dateHasta . " 23:59:59'";
        }else if($dateDesde!=0 && $dateHasta==0 ){
             $condicion .=" AND tb_solicitudes_almacen.dtm_fecharegistro >='" . $dateDesde . " 00:00:00'";
           

        }else if($dateDesde==0 && $dateHasta!=0 ){
            $condicion .=" AND tb_solicitudes_almacen.dtm_fecharegistro <='" . $dateHasta . " 23:59:59'";

        }
           


        $datos=array();
       // SUM(tb_solicitudes_almacen_detalle.nu_cantidad) AS cantidad,
        //    COUNT(tb_solicitudes_almacen_detalle.ln_clave_articulo) AS articulos,
        //Ur,solicitud,fecha,clave,descripcion,cantidad,,eststus
        //$SQL = "SELECT tb_solicitudes_almacen.nu_folio, tb_solicitudes_almacen.dtm_fecharegistro,tb_solicitudes_almacen.nu_tag,tb_solicitudes_almacen.ln_usuario,tb_solicitudes_almacen.ln_nombre_estatus,tags.tagdescription
        //      FROM tb_solicitudes_almacen INNER JOIN tags ON tags.tagref=tb_solicitudes_almacen.nu_tag";
        $SQL="SELECT tb_solicitudes_almacen.nu_folio, tb_solicitudes_almacen.dtm_fecharegistro, tb_solicitudes_almacen.nu_tag,tb_solicitudes_almacen.ln_usuario,tb_solicitudes_almacen.estatus, tb_solicitudes_almacen.ln_nombre_estatus, 
            tags.tagdescription, tb_solicitudes_almacen.txt_observaciones, 
            SUM(CASE   WHEN ln_arctivo=1 THEN tb_solicitudes_almacen_detalle.nu_cantidad ELSE 0 END) AS cantidad,
            COUNT(CASE  WHEN ln_arctivo=1 THEN tb_solicitudes_almacen_detalle.ln_clave_articulo END) AS articulos,
            tb_solicitudes_almacen.ln_tipo_solicitud,ln_ue
            FROM tb_solicitudes_almacen 
            INNER JOIN tb_solicitudes_almacen_detalle ON tb_solicitudes_almacen.nu_folio=tb_solicitudes_almacen_detalle.nu_id_solicitud
            INNER JOIN tags ON tags.tagref=tb_solicitudes_almacen.nu_tag
            WHERE 1 = 1 ".$condicion;

        $perfil= fnChecarPerfilPorFuncion($db);

        switch ($perfil) {
            case 'capt':
                $SQL.=" AND tb_solicitudes_almacen.ln_usuario='".$_SESSION ['UserID']."' ";
                break;

            case 'val':
                //$SQL.=" WHERE    tb_solicitudes_almacen.estatus='41'";
                //$SQL.=" WHERE    tb_solicitudes_almacen.ln_usuario='".$_SESSION ['UserID']."'";
                $SQL.=" ";
                break;

            case 'aut':
                //$SQL.=" WHERE    tb_solicitudes_almacen.estatus='43'";
                // $SQL.=" WHERE    tb_solicitudes_almacen.ln_usuario='".$_SESSION ['UserID']."'";
                $SQL.=" ";
                break;

            case 'alm': //45
                $SQL.=" AND tb_solicitudes_almacen.estatus='30' OR tb_solicitudes_almacen.estatus='47' "; //47 cerrada
                break;

            default:
                break;
        }

        $SQL.=" GROUP BY tb_solicitudes_almacen.nu_folio, tb_solicitudes_almacen.dtm_fecharegistro,            tb_solicitudes_almacen.nu_tag,tb_solicitudes_almacen.ln_usuario,tb_solicitudes_almacen.estatus, tb_solicitudes_almacen.ln_nombre_estatus, 
            tags.tagdescription, tb_solicitudes_almacen.txt_observaciones,
            tb_solicitudes_almacen.nu_id_solicitud ,ln_ue
            ORDER BY tb_solicitudes_almacen.nu_id_solicitud DESC";

        $ErrMsg = "No se obtuvo datos.";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $liga='';
        $i=0;

        while ($myrow = DB_fetch_array($TransResult)) {
            $liga='';

            if ($myrow['estatus']!=0 && $myrow['estatus']!=47) {
                //$liga='href="javascript:fnCargaDetalle("'.$myrow['nu_folio'].'");';
                $liga="<a id='ligasolicitud".$i."' href='"."javascript:fnCargaDetalle(".$myrow['nu_folio'].");'  style='color: blue;'><u>".$myrow['nu_folio']."</u></a>";
            } else {
                $liga=$myrow['nu_folio'];
            }
            //print_r($myrow['cantidad']);
            if($myrow['cantidad']>0 && ($myrow['articulos']>0)){


            $datos[] = array('id1'=>false,
                // 'idsolicitud' =>"<div class='enlaceDetalle' ><a   style='color: blue;'><u>".$myrow['nu_id_solicitud']."</u></a></div>",
                'idsolicitudsinliga' =>$myrow['nu_folio'],
                //'idsolicitud' =>"<a href='"."javascript:fnCargaDetalle(".$myrow['nu_folio'].");'  style='color: blue;'><u>".$myrow['nu_folio']."</u></a>",  //liga
                'idsolicitud' =>$liga,  //liga
                'tag'=>$myrow['nu_tag'],//.' '.$myrow['tagdescription'],
                'fecha'=>date("d-m-Y", strtotime($myrow['dtm_fecharegistro'])),
                'numeroart'=>$myrow['articulos'],
                'cantidad'=>$myrow['cantidad'],
                'estatus'=>$myrow['ln_nombre_estatus'],
                'tipoSol'=>$myrow['ln_tipo_solicitud'],
                'numeroestatus'=>$myrow['estatus'],
                'observaciones'=>$myrow['txt_observaciones']);

            //'fecha'=>fnReemplazar(date("F j, Y, g:i a", strtotime($myrow['dtm_fecharegistro']))));

            /*$datos.='<tr id="fila'.$myrow['nu_id_solicitud'].'">';
            $datos.='<td>'.$myrow['nu_tag'].' '.$myrow['tagdescription'] . '</td>'.'<td>'.$myrow['dtm_fecharegistro'] . '</td>'.'<td  ><span class="detalleSolicitud"  id="solicitud'.$myrow['nu_id_solicitud'] .'">Detalle</span></td>';
            $datos.="</tr>"; */

            // 'idsolicitud' =>"<button  class='enlaceDetalle'  href='"."javascript:fnCargaDetalle(".$myrow['nu_id_solicitud'].");'  style='color: blue;'><u>".$myrow['nu_id_solicitud']."</u></button>",  //liga
            }
            $i++;
        }

        $funcion = 2291;
        $nombre='Almacen'; //traeNombreFuncionGeneral($funcion, $db);
        $nombre=str_replace(" ", "_", $nombre);
        $nombreExcel = $nombre.'_'.date('dmY');
        //$contenido = array('datos' => $datos, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid,'nombreExcel' => $nombreExcel);
        $contenido = array('datos' => $datos,'nombreExcel' => $nombreExcel);

        $result = true;

        /*if(count($detalle)>0){
        $contenido =array('detalle' => $detalle);
        }else{
        $contenido ="No hay información sobre esta solicitud.";
        } */

        break;
    default:
        break;
}

$dataObj = array('info' =>'', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
