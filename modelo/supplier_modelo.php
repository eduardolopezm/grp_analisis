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
$RootPath = "";
$Mensaje = "";
$a=1;
$SQL='';

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$proceso = $_POST['proceso'];
$info = array();


/**
 * Validacion de cuenta clabe
 *
 * @param  string $pClabe  Dato cuenta CLABE para su validacion
 * @param  string $pBanco  Clave del Banco de 3 dìgitos, verificar en catalogo
 * @param  string $pCuenta Numero de cuenta, se valdia que esta coincida con el dato de CLABE
 * @return array          array indicando el estatus de la validacion y un mensaje
 *                        [0] != 0 corresponde a una validacion incorrecta
 */

function validarClabeBancaria($pClabe, $pBanco, $pCuenta)
{
    //definicion de variables
    $result = array();

    // if (strlen($pBanco) == 1) {
    //     $pBanco = llenarConCeros($pBanco, 2);
    // }

    // if (strlen($pBanco) == 2) {
    //     $pBanco = llenarConCeros($pBanco, 1);
    // }
    //validamos la longitud
    if (strlen($pClabe) == 18) {
        //la longitud es correcta
        //ahora se valida que la clave del banco proporcionada corresponda con el dato de la cuenta CLABE

        if ((int) substr($pClabe, 0, 3) == $pBanco) {
            //Se valida que el nùmero de cuenta corresponda con la cta clabe proporcionada

            //extraer el no. de cuenta de la cuenta CLABE
            $cuenta = substr($pClabe, 0, 10);
            if ($cuenta == $pCuenta) {
                if (digitoVerifCLABE($pClabe)) {
                    //$result = array(0 => 0, 1 => "La cuenta CLABE CLABE: [" . $pClabe . "] es correcta."); ad24
                    $result =true;
                } else {
                   // $result = array(0 => 1, 1 => "El d&iacute;gito verificador de la cuenta CLABE: [" . $pClabe . "] no corresponde."); ad24

                    $result =false;
                }
            } else {
                //$result = array(0 => 0, 1 => "La cuenta CLABE CLABE: [" . $pClabe . "] es correcta."); ad24
               $result =true;
            }
            //else {
            //     $result = array(0 => 1, 1 => "Los 11 digitos a partir de la 7a posici&oacute;n de la cuenta CLABE: [" . $pClabe . "] no corresponden al N&uacute;mero de CUENTA [" . $pCuenta . "].");
            // }
             $result =true;
        } else {
            //$result = array(0 => 0, 1 => "La cuenta CLABE CLABE: [" . $pClabe . "] es correcta.");
            $result =true;
        }
        //else {
        //     $result = array(0 => 1, 1 => "Los 3 primeros digitos de la cuenta CLABE: [" . $pClabe . "] no corresponden a la clave del BANCO [" . $pBanco . "].");
        // }
        $result =true;
    } else {
        //$result = array(0 => 1, 1 => "La longitud de la cuenta CLABE es incorrecta [" . $pClabe . "] es de " . strlen($pClabe) . " digitos.");  ad24
        $result =false;
    }

    return $result;
}

/**
 * Validacion para estructura de clabe
 * Se utilza una formula estandar para verificar el digito verificador
 */
function digitoVerifCLABE($pClabe)
{
    /* ################################################################################################ ##
    ## DIGITO DE CONTROL                                                                                ##
    ## -----------------                                                                                ##
    ## El digito de control se calcula sobre todos los datos de banco, codigo de plaza y                ##
    ## numero de cuenta, utilizando el modulo 10 peso 3, 7, 1 aplicado de izquierda a derecha           ##
    ## de la siguiente manera.                                                                          ##
    ## Ejemplo:                                                                                         ##
    ##    Codigo de banco:   002                                                                        ##
    ##    Codigo de plaza:   115                                                                        ##
    ##    Numero de cuenta:  01600326941                                                                ##
    ##    Llave del codigo cuenta de cliente: 002 115 01600326941                                       ##
    ##                                                                                                  ##
    ## Se multiplica cada una de las columnas por los pesos 3-7-1 haciendo caso omiso de las decenas    ##
    ##                       00211501600326941                                                          ##
    ##                       37137137137137137                                                          ##
    ##                       -----------------                                                          ##
    ##                       00237507600362927                                                          ##
    ## Se suman los resultados en forma horizontal,                                                     ##
    ##                       00237507600362927 = 59                                                     ##
    ## Haciendo caso omiso de las decenas en el resultado de la suma                                    ##
    ##                       9                                                                          ##
    ## este ultimo numero se resta de 10                                                                ##
    ##                       10 - 9 = 1                                                                 ##
    ## El resultado de la resta el el digito de control CCC, si el resultado es 10 el digito es cero    ##
    ##                       DC = 1                                                                     ##
    ## ################################################################################################ */
    //deficion de variables
    $peso7       = 7;
    $peso3       = 3;
    $peso1       = 1;
    $resultado_a = 0;
    $resultado_b = 0;

    $resultado_a       = 0;
    $resultado_b       = 0;
    $digitoVerificador = 0;

    //$pClabe=str_split($pClabe)
    $ultimoDigitoClabe = substr($pClabe, 17, 1);

    for ($i = 0; $i <= 16; $i++) {
        $resultado_a = substr($pClabe, $i, 1);

        if ($i == 0 || $i == 3 || $i == 6 || $i == 9 || $i == 12 || $i == 15) {
            $resultado_a = $resultado_a * $peso3;
        }

        if ($i == 1 || $i == 4 || $i == 7 || $i == 10 || $i == 13 || $i == 16) {
            $resultado_a = $resultado_a * $peso7;
        }

        if ($i == 2 || $i == 5 || $i == 8 || $i == 11 || $i == 14) {
            $resultado_a = $resultado_a * $peso1;
        }

        if (strlen($resultado_a) > 1) {
            $resultado_a = substr($resultado_a, 1, 1);
        }

        $digitoVerificador += $resultado_a;
    }
    //del resultado tomar solo la unidades, descartar las decenas.
    if (strlen($digitoVerificador) > 1) {
        $digitoVerificador = substr($digitoVerificador, 1, 1);
    }

    $digitoVerificador = 10 - $digitoVerificador;

    if (strlen($digitoVerificador) > 1) {
        $digitoVerificador = substr($digitoVerificador, 1, 1);
    }

    //echo $digitoVerificador;
    if ($ultimoDigitoClabe == $digitoVerificador) {
        return true;
    } else {
        return false;
    }
    return false;
}
function fnChecarExistencia($cuentaC,$db){

      $existeMatriz=false;
      $existeStock=false;
      $retorno=array();
        echo (  $existeMatriz);
      $SQL1="SELECT COUNT(*) as existe FROM tb_matriz_pagado WHERE stockact='".$cuentaC."'";

      $ErrMsg='Error al obtener  informacion de proveedores';
      $TransResult = DB_query($SQL1, $db, $ErrMsg);
      
   
      while ($myrow = DB_fetch_array($TransResult)){

              if($myrow['existe']>0){
                 $existeMatriz=true;
              }
      }

     


  $SQL2="SELECT COUNT(*) as existe FROM stockcategory WHERE accountegreso='".$cuentaC."'";


      $ErrMsg='Error al obtener  informacion de proveedores';
      $TransResult = DB_query($SQL2, $db, $ErrMsg);
    
   
    while ($myrow = DB_fetch_array($TransResult)){
    
              if($myrow['existe']>0){
                 $existeStock=true;
              }
      }
      $retorno[]=$existeMatriz;
      $retorno[]=$existeStock;


  return $retorno;
}
switch ($proceso) {
    case 'presuPuestalConcepto':
        $a=1;
        $capitulo=$_POST['capitulo'];
        $capitulo=str_replace("000","",$capitulo);

        /*$SQL="SELECT conceptocalculado AS value,descripcion as texto FROM tb_cat_partidaspresupuestales_concepto WHERE SUBSTRING(conceptocalculado,1,1) =".$capitulo; */

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


    case 'partidagenerica':
        $a=1;
        $capitulo=$_POST['capitulo'];
        $capitulo=str_replace("00","",$capitulo);

        $SQL="SELECT pargcalculado AS value,descripcion as texto FROM tb_cat_partidaspresupuestales_partidagenerica WHERE SUBSTRING(pargcalculado,1,2) =".$capitulo;
        $ErrMsg = "No se obtuvo datos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array( 'value' => $myrow ['value'], 'texto' =>$a." - ". $myrow ['texto'] );
            $a++;
           
        }

        $contenido = array('partidagenerica'=>$info);
        $result = true;
 
        break;

    case 'partidaespecifica':
        $a=1;
        $capitulo=$_POST['capitulo'];
        //$capitulo=str_replace("00","",$capitulo);

        $SQL="SELECT partidacalculada AS value,descripcion as texto FROM tb_cat_partidaspresupuestales_partidaespecifica WHERE SUBSTRING(partidacalculada,1,3) =".$capitulo;
        $ErrMsg = "No se obtuvo datos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array( 'value' => $myrow ['value'], 'texto' =>$a." - ". $myrow ['texto'] );
            $a++;
           
        }

        $contenido = array('partidaespecifica'=>$info);
        $result = true;
 
        break;

    case 'multiplesCapitulos':

        $capitulo='';
        $capitulos=$_POST['capitulos'];
        $capitulosSql='';
        for($d=0;$d<(count($capitulos));$d++){
            $capitulo=str_replace("000","",$capitulos[$d]);
            $capitulosSql.="'".$capitulo."',";
        }
         $capitulosSql=substr($capitulosSql, 0, -1);

     

        /*$SQL="SELECT conceptocalculado AS value,descripcion as texto FROM tb_cat_partidaspresupuestales_concepto WHERE SUBSTRING(conceptocalculado,1,1) =".$capitulo; */

        $SQL="SELECT conceptocalculado AS value,descripcion as texto ,ccap as capitulo, conceptocalculado as concepto FROM tb_cat_partidaspresupuestales_concepto WHERE ccap IN (".$capitulosSql.")";
        

        $ErrMsg = "No se obtuvo datos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['value']." - ".$myrow ['texto']  );
            $a++;
           
        }

        $contenido = array('conceptos'=>$info);
        $result = true;

        

        break; 
      case 'multiplesConceptos':

        $concepto='';
        $conceptos=$_POST['conceptos'];
        $conceptosSql='';
        for($d=0;$d<(count($conceptos));$d++){
            $concepto=str_replace("00","",$conceptos[$d]);
            $conceptosSql.="'".$concepto."',";
        }
         $conceptosSql=substr($conceptosSql, 0, -1);

     

        /*$SQL="SELECT conceptocalculado AS value,descripcion as texto FROM tb_cat_partidaspresupuestales_concepto WHERE SUBSTRING(conceptocalculado,1,1) =".$capitulo; */

       /* $SQL="SELECT pargcalculado AS value,descripcion as texto FROM tb_cat_partidaspresupuestales_partidagenerica WHERE SUBSTRING(pargcalculado,1,2) IN (".$conceptosSql.")" ; */

        $SQL="SELECT partidacalculada AS value,descripcion as texto FROM tb_cat_partidaspresupuestales_partidaespecifica  WHERE SUBSTRING(partidacalculada,1,2) IN  (".$conceptosSql.") ORDER  BY partidacalculada ASC";
        

        $ErrMsg = "No se obtuvo datos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['value']." - " .$myrow ['texto'] );
            $a++;
           
        }

        $contenido = array('especificas'=>$info);
        $result = true;

        

        break;

        case 'modificar':
        
        break;


        case 'guardarInfo':
        $data=array();
       
            foreach ($_POST as $param_name => $param_val){

                if($param_name!='partidapresupuestal' && ($param_name!='presuuestaloncepto')   && ($param_name!='partidagenerica')  ){

                        $data[$param_name]=$param_val;

                   }else{

                    //print_r( $param_val);
                  if($param_name=='partidagenerica'){
                   $partidas=$param_val; //$_POST['partidaespecifica'];
                   $patidasValores='';
                   for($d=0;$d<(count($partidas));$d++){

                    $partidasValores.="('".$partidas[$d]."','".$data['SupplierID']."','". $data['SuppName'] . "','".$_SESSION ['UserID']."'),";

                   }
                    $partidasValores=substr($partidasValores, 0, -1);
                    $sql="INSERT INTO tb_partidas_proveedores (ln_partida_especifica,ln_supplierid,ln_suppname,ln_usuario)
                    VALUES".  $partidasValores;
                    //print_r($sql);

                    $ErrMsg = "Error al guardar partida";
                    $DbgMsg = _('Fallo al al de alta partida con proveedor');
                    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                    }
                }
             
 
            }


                    $rfc='';
                    $curp='';
                    if($data['regimen']=='1'){
                        $rfc=$data['taxid'];
                        $curp=$data['curp'];
                    }else{
                      $rfc=$data['moraltaxid'];

                    }
                  
                    $sql = "INSERT INTO suppliers (supplierid,suppname,address1,address2,address3,address4,address5,address6,suppliersince,bankpartics,bankref,bankact,taxid,email,tipodetercero,typeid,currcode,paymentterms,ln_tipoPersona,ln_curp,ln_representante_legal,nu_interior,nu_exterior,id_nu_entidad_federativa,id_nu_municipio,active )
                     VALUES ('".$data['SupplierID']."',".
                            "'".$data['SuppName']."',".
                            "'".$data['Address1']."',".
                            "'".$data['Address2']."',".
                            "'".$data['municipio2']."',".
                            "'".$data['estado2']."',".
                            "'".$data['Address5']."',".
                            "'".$data['Address6']."',".

                            "'".date("Y-m-d")."',".  //  "'".date("Y-m-d", strtotime($data['desdeprov']))."',".
                            "'".$data['banco']."',".
                            "'".$data['ref']."',".
                            "'".$data['cuenta']."',".
                             "'".$rfc."',".
                            "'".$data['Email']."',".
                            
                            "".$data['tipotercero'].",".
                            "'".$data['Typeid']."',".
                            "'"."MXN"."',".

                            "'"."01"."',".
                            "'".$data['regimen']."',".
                            "'".$curp."',".
                            "'".$data['representanteLegal']."',".
                            "'".$data['interior']."',".
                            "'".$data['exterior']."',".
                            "'".$data['Address4']."',".
                            "'".$data['municipios']."',".
                            "'".$data['activoSupp']."')";
                            


                            //
                            //print_r($sql);
                      
                            
                   $ErrMsg = "Error al guardar partida";
                    $DbgMsg = _('Fallo al al de alta partida con proveedor');
                    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                    
                   
                      $valores="('".$data['cuentasCon']."','".$data['SupplierID']."','"."1"."')";
                    $SQL = "INSERT INTO accountxsupplier (accountcode, supplierid,flagdiot ) VALUES ".$valores;

                    $ErrMsg='Error al obtener  informacion de proveedores';
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                      
                    $contenido ='Proveedor dado de alta exitosamente.';
                    $result = true;
                            

        break;

          case 'modificarInfo':
           $data=array();
       
            foreach ($_POST as $param_name => $param_val){

                if($param_name!='partidapresupuestal' && ($param_name!='presuuestaloncepto')   && ($param_name!='partidagenerica')  ){

                        $data[$param_name]=$param_val;

                   }else{
                    $SQLdel="  DELETE FROM tb_partidas_proveedores WHERE  ln_supplierid='".$data['SupplierID']."'";
                    $ErrMsg = "Error al eliminar partida";
                    $DbgMsg = _('Fallo al al de alta partida con proveedor');
                    $result = DB_query($SQLdel, $db, $ErrMsg, $DbgMsg);
                    //print_r( $param_val);
                  if($param_name=='partidagenerica'){
                   $partidas=$param_val; //$_POST['partidaespecifica'];
                   $patidasValores='';
                   for($d=0;$d<(count($partidas));$d++){

                    $partidasValores.="('".$partidas[$d]."','".$data['SupplierID']."','". $data['SuppName'] . "','".$_SESSION ['UserID']."'),";

                   }
                    $partidasValores=substr($partidasValores, 0, -1);
                    $sql="INSERT INTO tb_partidas_proveedores (ln_partida_especifica,ln_supplierid,ln_suppname,ln_usuario)
                    VALUES".  $partidasValores;
                    //print_r($sql);

                    $ErrMsg = "Error al guardar partida";
                    $DbgMsg = _('Fallo al al de alta partida con proveedor');
                    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                    }
                }
             
 
            }

                    $rfc='';
                    if($data['regimen']=='1'){
                        $rfc=$data['taxid'];
                    }else{
                      $rfc=$data['moraltaxid'];
                    }
                    //  print_r($data['taxid']);
                    $sql = "UPDATE suppliers  SET
                             suppname='".$data['SuppName']."',".
                            "address1='".$data['Address1']."',".
                            "address2='".$data['Address2']."',".
                            "id_nu_municipio='".$data['municipios']."',".
                            "id_nu_entidad_federativa='".$data['Address4']."',".
                            "address5='".$data['Address5']."',".
                            "address6='".$data['Address6']."',".
                            "suppliersince='".date("Y-m-d", strtotime($data['desdeprov']))."',".
                            "bankpartics='".$data['banco']."',".
                            "bankref='".$data['ref']."',".
                            "bankact='".$data['cuenta']."',".
                            "taxid='".$rfc."',".
                            "email='".$data['Email']."',".
                            
                            "Address3='".$data['municipio2']."',".  
                            "Address4='".$data['estado2']."',".

                            "tipodetercero='".$data['tipotercero']."',".
                            "typeid='".$data['Typeid']."',".
                            "ln_tipoPersona='".$data['regimen']."',".
                            "ln_curp='".$data['curp']."',".
                            "ln_representante_legal='".$data['representanteLegal']."',".
                            "nu_interior='".$data['interior']."',".
                            "nu_exterior='".$data['exterior']."',".
                            "active='".$data['activoSupp']."',".
                            "currcode='"."MXN"."',".
                            "paymentterms='"."01"."' WHERE supplierid='".$data['SupplierID']."'";

                            //print_r($sql);
                            
                   $ErrMsg = "Error al guardar partida";
                    $DbgMsg = _('Fallo al al de alta partida con proveedor');
                    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                    //print_r($sql);
                    
                     
                    $SQL = "UPDATE accountxsupplier SET accountcode='".$data['cuentasCon']."' WHERE supplierid='".$data['SupplierID']."'";

                    $ErrMsg='Error al obtener  informacion de proveedores';
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                    $contenido ='Proveedor modificado exitosamente.';
                    $result = true;
                            

        break;


        case 'existeIdSupp':
             $contenido='';
             $idSupp=$_POST['idSupp'];
             $existe=false;
             $SQL1="SELECT COUNT(*) as existe FROM suppliers WHERE supplierid='".$idSupp."'";
              $ErrMsg='Error al obtener  informacion de proveedores';
              $TransResult = DB_query($SQL1, $db, $ErrMsg);
              
           
              while ($myrow = DB_fetch_array($TransResult)){

                      if($myrow['existe']>0){
                         $existe=true;
                      }
              }
         if($existe==true){
        $contenido= true;//'El código de proveedor ya fue asigando eliga otro';
        }else{
         $contenido=false; //'';
        }
        $contenido = $contenido;
        $result = true;

        break;

        case 'getBanco':
    $info=array();
    $tagref=0;
    $supplierno=0;
    $SQL="SELECT * FROM banks order by bank_name ASC";
        
    $TransResult = DB_query($SQL, $db, $ErrMsg);
        
    while ( $myrow = DB_fetch_array($TransResult) ){

        $info[] = array('banco' =>$myrow['bank_name'],'id' =>$myrow['bank_id']);
        
    }



    $contenido = array('DatosBanco' => $info);
    
    $result = true;

    break;

    case 'partidas2':
      $idProv=$_POST['idSupp'];
      $partidas=array();
      $r='';
      $SQL = "SELECT ln_partida_especifica,dtm_fecharegistro,(SELECT descripcion FROM tb_cat_partidaspresupuestales_partidaespecifica where partidacalculada=tb_partidas_proveedores.ln_partida_especifica) as descri,ln_usuario,ln_activo FROM tb_partidas_proveedores WHERE ln_supplierid='".$idProv."' AND ln_activo='1'";
      $ErrMsg='Error al obtener  informacion de proveedores';
      $TransResult = DB_query($SQL, $db, $ErrMsg);
       
      while ($myrow = DB_fetch_array($TransResult)){
         ($myrow['ln_activo']=='1') ? $r="Eliminar" : $r="Activar";
          $partidas[]=array('partida' =>$myrow['ln_partida_especifica'],
            'descri'=>$myrow['descri'],
            'fecha' =>$myrow['dtm_fecharegistro'],
            'usuario'=>$myrow['ln_usuario'],
            'desactivar'=> '<u style="color:blue" class="'.$r.'">'.$r.'</u>'
        );
        }

          $contenido = array('datos' => $partidas);
    
    $result = true;
    break;

    case 'partidas2guardar':

            foreach ($_POST as $param_name => $param_val){


                    //print_r( $param_val);
                  if($param_name=='partidagenerica'){
                   $partidas=$param_val; //$_POST['partidaespecifica'];
                   $patidasValores='';
                   for($d=0;$d<(count($partidas));$d++){

                    $partidasValores.="('".$partidas[$d]."','".$_POST['SupplierID']."','".$_SESSION ['UserID']."','1'),";

                   }
                    $partidasValores=substr($partidasValores, 0, -1);
                    $sql="INSERT INTO tb_partidas_proveedores (ln_partida_especifica,ln_supplierid,ln_usuario,ln_activo)
                    VALUES".  $partidasValores;
                    //print_r($sql);

                    $ErrMsg = "Error al guardar partida";
                    $DbgMsg = _('Fallo al al de alta partida con proveedor');
                    DB_query($sql, $db, $ErrMsg, $DbgMsg);
                    }
                
             
 
            }

             $result = true;
    break;

    case 'checarCuentaContable':
    $Mensaje="";
    if(isset($_POST['cuenta'])){

      $existe=fnChecarExistencia($_POST['cuenta'],$db);
      
      if($existe[0]==false){
        $Mensaje.='<div class="btn btn-danger btn-xs glyphicon glyphicon-remove" style="font-size:8px"></div>La cuenta no esta cargada en la matriz de conversión del pagado.<br>';
      }
       /*if($existe[1]==false){
        $Mensaje.='<div class="btn btn-danger btn-xs glyphicon glyphicon-remove" style="font-size:8px"></div>  matriz de conversion de matriz del 
devengando';
      } */

      $contenido = $Mensaje;
      $result = true;
    }else{
      
      $result = false;
    }
    break;

    case 'existeCuentaBancaria':
       
       $idProvs=$_POST['ids'];
       $bank=$_POST['bank'];
       $ref=$_POST['ref'];
       $cuenta=$_POST['cuenta'];
       $clabe=$_POST['clabe'];
   
       $flag=false;
       $SQL = "SELECT count(*) as total FROM tb_bancos_proveedores WHERE ln_supplierid='".$idProvs."' AND ln_bank_id='".$bank."' AND nu_cuenta='".$cuenta."' OR nu_clabe_interbancaria='".$clabe."'";
      
      $ErrMsg='Error al obtener  informacion de proveedores';
      $TransResult = DB_query($SQL, $db, $ErrMsg);
        
       while ( $myrow = DB_fetch_array($TransResult) ){
        
        if($myrow['total']>=1){
          $flag=true;
        }else{
           $flag=false;
        }

       }

        $contenido =    $flag;
        $result = true;

       

    break;

        case 'existenPartidas':

 
        $partidasValores='';
        $valoresPartidas=array();
        $mensajeVal='';
        $partidas=$_POST['partidas'];
        

                
                   for($d=0;$d<(count($partidas));$d++){

                    $partidasValores.="'".$partidas[$d]."',";
                    $valoresPartidas[]=$partidas[$d];

                   }
                    $partidasValores=substr($partidasValores, 0, -1);
                   
                   

            $sql="SELECT ln_partida_especifica, COUNT(*) as total  FROM tb_partidas_proveedores 
                        WHERE ln_partida_especifica IN(".$partidasValores.") and ln_supplierid='".$_POST['SupplierID']."' GROUP BY ln_partida_especifica order by ln_partida_especifica asc";
           
            $ErrMsg = "Error al guardar partida";
            $TransResult = DB_query($sql, $db, $ErrMsg);
            $i=0;
            
            while ( $myrow = DB_fetch_array($TransResult) ){
                
              if(($valoresPartidas[$i])==($myrow['ln_partida_especifica'])){
                 if($myrow['total']>=1){
                    $mensajeVal.="<br> La partida ".$valoresPartidas[$i]." ya fue dada de alta.";
                 }
              }
            
                 $i++;
            }

             $contenido =    $mensajeVal;
             $result = true;


    break;

    case 'getMunicipios':
    $estado=0;
    if($_POST['estado']!=''){
     $estado=$_POST['estado'];
    }

    $SQL="SELECT ln_nombre as municipio FROM tb_cat_municipio  WHERE id_nu_entidad_federativa=".$estado;
    $a=1;

        $ErrMsg = "No se obtuvo datos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array( 'value' => $a, 'texto' => $a." - ".$myrow ['municipio'] );
            $a++;
           
        }

    $contenido = array('datos'=>$info);
        $result = true;

    break;

    case 'getClabeBanco':


       $bank=$_POST['bank'];
       $clabe='';
   
     
       $SQL = "SELECT bank_clave FROM banks WHERE bank_id='".$bank."'  order by bank_clave ASC LIMIT 1 ";
      
      $ErrMsg='Error al obtener  informacion sobre la clave del banco';
      $TransResult = DB_query($SQL, $db, $ErrMsg);
        
       while ( $myrow = DB_fetch_array($TransResult) ){
        
         $clabe= $myrow['bank_clave'];
        

       }

        $contenido =$clabe;
        $result = true;


    break;

    case 'validarInterbancaria':
    $flag=false;
    $pClabe=  $_POST['clabe'];
    $pBanco=   $_POST['bancoClabe'];
    $pCuenta=  $_POST['nCuenta'];
      
      
      $flag=validarClabeBancaria2($pClabe, $pBanco, $pCuenta);

        $contenido =$flag;
        $result = true;
    break;

    case  'validarExistenciaProveedorOtroProceso':
    $flag=false;
    if(isset($_POST['proveedor'])){
       
        $prov=$_POST['proveedor'];

        $sql = "SELECT * FROM suppliers WHERE supplierno='".$prov."'";
        $result = DB_query($sql, $db);

        if(DB_num_rows($result) == 0){
            $flag=true; // no existe
        }else{
            $flag=false; // existe
        }
        
      }

    $contenido = array('existe'=>$flag);
    $result = true;
    break;

    case'desactivarCuenta':

    $flag=false;
    if(isset($_POST['supp'])){
        $mensaje='';
        $msjAccion='';
        $prov=   $_POST['supp'];
        $cuenta= $_POST['cuenta'];
        $clabe=  $_POST['clabe'];
        $accion= $_POST['accion'];
        
        

        $sql = "SELECT COUNT(*) AS existe
                 FROM  banktrans
                 INNER JOIN supptrans on banktrans.transno  =supptrans.transno 
                 WHERE supplierno='".$prov."'
                 group by supplierno";
        $result = DB_query($sql, $db);
        $activo='';

        if(DB_num_rows($result) == 0){
            //$flag=true; // existe
            
            if($accion=='Eliminar'){
                $activo='0';
                $msjAccion="eliminada";
            }else{
                 $activo='1';
                $msjAccion="activada";
            }
            $sql = "UPDATE tb_bancos_proveedores SET ln_activo='".$activo."' WHERE ln_supplierid='".$prov."' AND nu_cuenta='".$cuenta."'";
            $result = DB_query($sql, $db);
             $mensaje="La cuenta ".$cuenta." fue ".$msjAccion." correctamente.";
           // $flag=false; //no existe
            
        }else{
            // se activa
            $mensaje="La cuenta ".$cuenta." seleccionada no puede eliminarse esta ligada a otro proceso.";
        }
        
      }
    
    $contenido = array('mensaje'=>$mensaje);
    $result = true;

    break;
      case'desactivarPartida':


    $flag=false;
    if(isset($_POST['supp'])){
        $mensaje='';
        $msjAccion='';
        $prov=   $_POST['supp'];
        $cuenta= $_POST['partida'];
        $accion= $_POST['accion'];
        $activo='';
        
        

        $sql = "     SELECT tb_partida_articulo.partidaEspecifica     FROM supptrans  
    INNER JOIN supptransdetails ON supptrans.id= supptransdetails.supptransid  
    INNER JOIN purchorders ON purchorders.orderno=supptransdetails.orderno
    INNER JOIN stockmaster ON  supptransdetails.stockid=stockmaster.stockid
    INNER JOIN tb_partida_articulo ON  stockmaster.eq_stockid =tb_partida_articulo.eq_stockid
    WHERE  supptrans.supplierno='".$prov."' AND partidaEspecifica ='".$cuenta."'";
        $result = DB_query($sql, $db);
        $activo='';

        if(DB_num_rows($result) == 0){
            //$flag=true; // existe
            
            if($accion=='Eliminar'){
                $activo='0';
                $msjAccion="eliminada";
            }else{
                 $activo='1';
                $msjAccion="activada";
            }
            $sql = "UPDATE tb_partidas_proveedores SET ln_activo='".$activo."' WHERE ln_supplierid='".$prov."' AND ln_partida_especifica='".$cuenta."'";
            $result = DB_query($sql, $db);
             $mensaje="La partida ".$cuenta." fue ".$msjAccion." correctamente.";
           // $flag=false; //no existe
            
        }else{
            // se activa
            $mensaje="La partida ".$cuenta." seleccionada no puede eliminarse esta ligada a otro proceso.";
        }
        
      }
    
    $contenido = array('mensaje'=>$mensaje);
    $result = true;

    break;
        //   case 'multiplesGenericas':

        // $generica='';
        // $genericas=$_POST['genericas'];
        // $genericasSql='';
        // for($d=0;$d<(count($genericas));$d++){
        //     $generica=str_replace("00","",$genericas[$d]);
        //     $genericasSql.="'".$generica."',";
        // }
        //  $genericasSql=substr($genericasSql, 0, -1);

     

        // /*$SQL="SELECT conceptocalculado AS value,descripcion as texto FROM tb_cat_partidaspresupuestales_concepto WHERE SUBSTRING(conceptocalculado,1,1) =".$capitulo; */

        //    // $SQL="SELECT partidacalculada AS value,descripcion as texto FROM tb_cat_partidaspresupuestales_partidaespecifica WHERE SUBSTRING(partidacalculada,1,3) IN (".$genericasSql.")";
        // $SQL="SELECT partidacalculada AS value,descripcion as texto FROM tb_cat_partidaspresupuestales_partidaespecifica  WHERE SUBSTRING(partidacalculada,1,2) IN  (".$genericasSql.")";
        // $ErrMsg = "No se obtuvo datos";
        

        // $ErrMsg = "No se obtuvo datos";
        // $TransResult = DB_query($SQL, $db, $ErrMsg);

        // while ($myrow = DB_fetch_array($TransResult)) {
        //     $info[] = array( 'value' => $myrow ['value'], 'texto' =>$myrow ['value']." - " .$myrow ['texto'] );
        //     $a++;
           
        // }

        // $contenido = array('especificas'=>$info);
        // $result = true;

        

        // break;



}


function validarClabeBancaria2($pClabe, $pBanco, $pCuenta)
{
    //definicion de variables
    $result =false;

    $cuenta = substr($pClabe, 0, 10);
    if ($cuenta == $pCuenta) {
        $result=true;
    } else {
              
        $result =false;
    }
            
    return $result;
}
$dataObj = array('info' =>'', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
