<?php
/**
 * ABC proveedores
 *
 * @category ABC
 * @package ap_grp
 * @author Arturo Lopez Peña  <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/01/2018
 * Fecha Modificación: 02/01/2018
 * Se realizan operación pero el Alta, Baja y Modificación.Conforme a las validaciones creadas para la operación seleccionada
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
$funcion=30;
require $PathPrefix.'includes/SecurityFunctions.inc';
require $PathPrefix.'includes/SQL_CommonFunctions.inc';

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

$proceso = $_POST['proceso'];
$info=array();
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

    // echo $digitoVerificador;
    //  echo $ultimoDigitoClabe;

    if ($ultimoDigitoClabe == $digitoVerificador) {
        return true;
    } else {
        return false;
    }
    return false;
}
function fnValidarCuentaBancaria($idProvs,$bank,$cuenta,$clabe,$db){
   $existe=false;
   $claveBank='';
   $msg='';
   $digitoV='';
   $SQL = "SELECT count(*) as total FROM tb_bancos_proveedores WHERE ln_supplierid='".$idProvs."' AND ln_bank_id='".$bank."' AND nu_cuenta='".$cuenta."' OR nu_clabe_interbancaria='".$clabe."'";
      
    $ErrMsg='Error al obtener  informacion de proveedores';
    $TransResult = DB_query($SQL, $db, $ErrMsg);
     
    while ( $myrow = DB_fetch_array($TransResult) ){
        
        if($myrow['total']>=1){
            $existe=true;
        }else{
          $existe=false;
        }
    }// fin while

    if($existe==false){ // si no existe la cuenta 

      $SQL = "SELECT bank_clave FROM banks WHERE bank_id='".$bank."'  order by bank_clave ASC LIMIT 1 ";
      
      $ErrMsg='Error al obtener  informacion sobre la clave del banco';
      $TransResult = DB_query($SQL, $db, $ErrMsg);
        
      while ( $myrow = DB_fetch_array($TransResult) ){
         $claveBank= $myrow['bank_clave'];
       }

      if($claveBank!=''){
         
         $claveComparar=substr($clabe,0,3);
         
         if($claveComparar==$claveBank){
          $plaza= substr($clabe,3,3);
          
          $SQL = "SELECT sn_plaza_bancaria FROM tb_cat_plaza_bancaria WHERE sn_plaza_bancaria='".$plaza."'";
      
          $ErrMsg='Error al obtener  informacion sobre la clave del banco';
          $TransResult = DB_query($SQL, $db, $ErrMsg);
           
           $n=DB_num_rows($TransResult);
           if($n>0){
              
              $digitoV=digitoVerifCLABE($clabe);
              // echo $digitoV;

           }else{
             $msg="<br>La CLABE no cuenta con digitos de plaza  validos.";
           }

         }else{
           $msg="<br>La CLABE no  es valida.";
         }// fin if comparar claves

      }else{
        $msg='<br>No se puede  validar los datos de banco proporcionados.';
      }
    }else{// fin si no existe

        $msg="<br>Ya existe la cuenta proporcionada.";
    }

    $datos = array();
    $datos[]=$msg;
    $datos[]= $digitoV;
    $datos[]=$existe;
    return $datos;

}
    switch ($proceso) {
    case 'mostrarProveedores': 
      
      $SQL = "SELECT `suppliers`.*, `supplierstype`.`typename`

              FROM `suppliers`
              LEFT JOIN `supplierstype` ON `supplierstype`.`typeid` = `suppliers`.`typeid`

              WHERE 1=1 AND `supplierid` != '111111'
              AND `id_nu_tipo` = '1'";
      
      if(($_POST['codigo'])!='' ){
        $SQL.=" AND supplierid like '%".$_POST['codigo']."%'";
      }
      if(($_POST['regimen'])!=''&&$_POST['regimen']!="0" ){
        $SQL.=" AND ln_tipoPersona='".$_POST['regimen']."'";
      }

      if(($_POST['rfc'])!='' ){
        $SQL.=" AND taxid like '%".$_POST['rfc']."%'";
      }

      if (!empty($_POST["descripcion"])){
        $SQL.=" AND suppname LIKE '%".$_POST['descripcion']."%'"; 
      }

      $ErrMsg='Error al obtener informacion de proveedores';
      $TransResult = DB_query($SQL, $db, $ErrMsg);

      $tiposPersona[1] = "Física";
      $tiposPersona[2] = "Moral";
      
      while ($myrow = DB_fetch_array($TransResult)){
         $info[]=array(
                    'checkProv'=>false,
                    'idSupp'=>$myrow['supplierid'],
                    'email' =>$myrow['email'],
                    'nombre' =>$myrow['suppname'],
                    'tipoPersona' => ( array_key_exists($myrow['ln_tipoPersona'], $tiposPersona) ? $tiposPersona[$myrow['ln_tipoPersona']] : "" ),
                    'ad1' =>$myrow['address1'],
                    'ad2' =>$myrow['address2'],
                    'ad3' =>$myrow['address3'],
                    'ad4' =>$myrow['address4'],
                    'ad5' =>$myrow['address5'],
                    'ad6' =>$myrow['address6'],
                    //'desdeSupp'=> $myrow['suppliersince'],
                    //'Cuentas'=> '<u style="color:blue" id="'.$myrow['supplierid'].'">Cuentas</u>',
                    'rfc'=>$myrow['taxid'],
                    'fecha'=>$myrow['suppliersince'],
                    'ver'=> '<a href="#" id="'.$myrow['supplierid'].'"><span class="glyphicon glyphicon-eye-open"></span></a>',
                    'Modificar'=> '<a href="#" id="'.$myrow['supplierid'].'"><span class="glyphicon glyphicon-edit"></span></a>',
                    'Eliminar'=>'<a href="#" id="'.$myrow['supplierid'].'"><span class="glyphicon glyphicon-trash"></span></a>',
                    'id' =>$myrow['supplierid'],
                    'tipoid'=>$myrow['typeid'],
                    'nombretipo'=>$myrow['typename'],
                    'estatus'=> $myrow['active'] == 1 ? "Activo" : "Inactivo"
                  );
      }

    $contenido = array('datosProveedor' => $info);
    $result = true;
            
    break;

    case 'proveedor':
      $info=array();
      $partidas=array();
      $idProv=$_POST['idSupp'];
      $SQL = "SELECT * FROM  suppliers WHERE supplierid='".$idProv."'";
      $ErrMsg='Error al obtener  informacion de proveedores';
      $TransResult = DB_query($SQL, $db, $ErrMsg);
      while ($myrow = DB_fetch_array($TransResult)){
         $info[]=array('idSupp'=>$myrow['supplierid'],
                    'email' =>$myrow['email'],
                    'nombre' =>$myrow['suppname'],
                    'rfc' =>$myrow['taxid'],
                    'ad1' =>$myrow['address1'],
                    'ad2' =>$myrow['address2'],
                    'ad3' =>$myrow['id_nu_municipio'],
                    'ad4' =>$myrow['id_nu_entidad_federativa'],
                    'ad5' =>$myrow['address5'],
                    'ad6' =>$myrow['address6'],
                    'desdeSupp'=> date("d-m-Y", strtotime($myrow['suppliersince'])),
                    'banco'=>$myrow['bankpartics'],
                    'ref'=>$myrow['bankref'],
                    'cuenta'=>$myrow['bankact'],
                    'tercero'=>$myrow['tipodetercero'],
                    'tipoid'=>$myrow['typeid'],
                    'terminos'=>$myrow['paymentterms'],
                    'tipoPersona'=>$myrow['ln_tipoPersona'],
                    'represnetante'=>$myrow['ln_representante_legal'],
                    'curp'=>$myrow['ln_curp'],
                    'exterior'=>$myrow['nu_exterior'],
                    'interior'=>$myrow['nu_interior'],
                    'activo'=>$myrow['active'],
                    'tesofe'=>$myrow['nu_tesofe']
                  );
          // echo $myrow['supplierid'];
          // exit();
      }

      $SQL = "SELECT ln_partida_especifica FROM tb_partidas_proveedores WHERE ln_supplierid='".$idProv."'";
      $ErrMsg='Error al obtener  informacion de proveedores';
      $TransResult = DB_query($SQL, $db, $ErrMsg);
      while ($myrow = DB_fetch_array($TransResult)){
        $partidas[]=$myrow['ln_partida_especifica'];
      }

      $cuenta='';
      $SQL1 = "SELECT accountcode FROM accountxsupplier WHERE supplierid='".$idProv."'";
      $ErrMsg='Error al obtener  informacion de proveedores';
      $TransResult = DB_query($SQL1, $db, $ErrMsg);
      while ($myrow = DB_fetch_array($TransResult)){
        $cuenta=$myrow['accountcode'];
      }

      // Retenciones
      $retenciones=array();
      $SQL = "SELECT idret FROM tb_suppliersRetencion WHERE supplierid='".$idProv."'";
      $ErrMsg='Error al obtener las retenciones del proveedor';
      $TransResult = DB_query($SQL, $db, $ErrMsg);
      while ($myrow = DB_fetch_array($TransResult)){
        // $retenciones[]=$myrow['idret'];
        $retenciones[] = array( 'retencion' => $myrow ['idret'] );
      }

      // print_r($SQL1);
      // echo"---";
      $contenido = array('datosProv' => $info,'partidas'=>$partidas,'cuenta'=>$cuenta, 'retenciones' => $retenciones);
      $result = true;
    break;


    case 'cuentasProv':
        $info=array();
      $idProv=$_POST['idSupp'];
      $SQL = "SELECT * FROM accountxsupplier WHERE supplierid='".$idProv."'";
      $ErrMsg='Error al obtener  informacion de proveedores';
      $TransResult = DB_query($SQL, $db, $ErrMsg);
    
  
      while ($myrow = DB_fetch_array($TransResult)){

         $info[]=array('idSupp'=>$myrow['supplierid'],
                    'cuenta' =>$myrow['accountcode'],
                    'concepto' =>$myrow['concepto'],
                    'diot' =>$myrow['u_typeoperation'],
                    'modificar'=>'<a href="#" style="color:blue; cursor:pointer; " id="'.$myrow['supplierid'].'">Modificar</a>'
                    
                        );
    
                
      }

    $contenido = array('cuentasProv' => $info);
    $result = true;
    break;
    //eliminarProv'
    case 'eliminarProv':
    $idProv = $_POST['idSupp'];
    //$SQL         = "UPDATE   SET activo='N' where cg='".$cg."'"; 
    $SQL         = "DELETE FROM suppliers WHERE supplierid='".$idProv."'";
    $ErrMsg = "No se eliminó el registro";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
  
    $contenido = "Se eliminó el registro  con éxito.";

    $result = true;
    break;

    case 'cuentasContables':
      $info=array();
      $info1=array();
       
      $SQL = "SELECT DISTINCT `accountcode` AS `valor`, `accountname` AS `label`, `group_` as `padre`
              FROM `chartmaster` AS `cm`
              JOIN `tb_sec_users_ue` AS `usue` ON `usue`.`userid` = '$_SESSION[UserID]'

              WHERE (`accountcode` LIKE '2.1.1%'
              OR `accountcode` LIKE '2.1'
              OR `accountcode` LIKE '2')
              AND (`usue`.ue = `cm`.`ln_clave`
              OR `cm`.`nu_nivel` <= 5)
              ORDER BY `group_`, `accountcode`";

      $ErrMsg='Error al obtener  informacion de proveedores';
      $TransResult = DB_query($SQL, $db, $ErrMsg);

      $datosCortos = array();
      $datosLargos = array();

      while ($rs = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $rs['valor'], 'texto' => $rs['valor'] .' | ' . $rs['label'] );
        $veces = substr_count($rs['valor'],'.');

        if($veces<5){
          $datosCortos[] = [
            'value' => $rs['valor'],
            'texto' => $rs['label']
          ];
        }else{
          $datosLargos[] = [
            'value' => $rs['valor'],
            'texto' => $rs['label']
          ];
        }
      }

      /*$SQL = "SELECT accountcode, accountname, group_ as padre
        FROM chartmaster
        WHERE accountcode LIKE '2.1%'
        ORDER BY group_, accountcode";
      $ErrMsg='Error al obtener  informacion de proveedores';
      $TransResult = DB_query($SQL, $db, $ErrMsg);
    
  
      while ($myrow = DB_fetch_array($TransResult)){

        $info[] = array( 'value' => $myrow['accountcode'], 'texto' => $myrow['accountcode'] .' | ' . $myrow['accountname'] );
                
      }*/
      $SQL = "SELECT *
        FROM typeoperationdiot
        ORDER BY u_typeoperation";

      $ErrMsg='Error al obtener  informacion de proveedores';
      $TransResult = DB_query($SQL, $db, $ErrMsg);
  
      while ($myrow = DB_fetch_array($TransResult)){
        $info1[] = array( 'value' => $myrow['u_typeoperation'], 'texto' => $myrow['typeoperation'] );
      }

      $contenido = array('cuentas' => $info, 'diot'=>$info1, 'cuentasMenores' => $datosCortos, 'cuentasMayores' => $datosLargos);
      $result = true;
            
      break;

    case 'countsBanksProv':
    $idSupp=$_POST['idSupp'];

     $info=array();
      $info1=array();
       
     
      $SQL = "SELECT * FROM tb_bancos_proveedores WHERE ln_supplierid= '". $idSupp."'";//"' and ln_activo='1'";
      $TransResult = DB_query($SQL, $db, $ErrMsg);
    
      $r=0;
      while ($myrow = DB_fetch_array($TransResult)){
        ($myrow['ln_activo']=='1') ? $r="Activo" : $r="Inactivo";
        $info[] = array( 'idSupp'=> $myrow ['ln_supplierid'],
                          'idRef'=> $myrow ['ln_referencia'],
                          //'idBank'=> $myrow ['ln_bank_id'],
                          //'fecha'=> $myrow ['dtm_fecharegistro'],
                          'cuenta'=> $myrow ['nu_cuenta'],
                          'clabe'=> $myrow ['nu_clabe_interbancaria'],
                          //'modificar'=> '<u style="color:blue" >Modificar</u>',
                           

                          'desactivar'=> '<a href="#" style="color:blue" class="'.$r.'">'.$r.'</a>');
                
      }

    $contenido = array('cuentasBancarias' =>$info );
    $result = true;

    break;


  //   case 'guardarCC':
  //   $idProvs=$_POST['ids'];
  //   $diotProv=$_POST['diot'];
  //   $cuentaCon=$_POST['cuenta'];
  //   $conceptoCon=$_POST['concepto'];
   
  //   $existe=fnChecarExistencia($cuentaCon,$db);

  //   if($existe[0]==true && ($existe[1]==true)){

  //   $valores='';

  //      //for ($a=0; $a<count($idProvs); $a++) {
  //               $valores.="('".$cuentaCon."','".$idProvs."','".$conceptoCon."','1','".$diotProv."'),";
  //          // }
  //           $valores=substr($valores, 0, -1);

  //    $SQL = "INSERT INTO accountxsupplier (accountcode, supplierid, concepto,flagdiot,u_typeoperation ) VALUES ".$valores;


  //   $ErrMsg='Error al obtener  informacion de proveedores';
  //   $TransResult = DB_query($SQL, $db, $ErrMsg);
    
  //   $contenido = 'Se agregó con éxito la cuenta <b>'.$cuentaCon.'</b>';
  //   $result = true;
  //   } else{

  //   $contenido = 'La cuenta contable no esta dada de alta';
  //   $result = true;
  // }
                               

  //   break;

    case 'saveCountBank':


    $idProvs=$_POST['ids'];
    $bank=$_POST['bank'];
    $ref=$_POST['ref'];
    $cuenta=$_POST['cuenta'];
    $clabe=$_POST['clabe'];


    // ids:$('#SupplierID').val(), //ids,
    //         bank:$("#bank2").val(),
    //         ref:$("#ref").val(),
    //         cuenta: $('#cuenta').val(),
    //         clabe:$('#clabe').val()
   
    // $existe=fnChecarExistencia($cuentaCon,$db);

    // if($existe[0]==true && ($existe[1]==true)){
    $datos=fnValidarCuentaBancaria($idProvs,$bank,$cuenta,$clabe,$db);
    // if($datos[0]==''){

    $valores='';
    if($datos[2]==false){
    for ($a=0; $a<count($idProvs); $a++) {

      $valores.="('".$idProvs."','".$bank."','".$ref."','".$cuenta."','".$clabe."','".$_SESSION ['UserID']."','1'),";
    }

     $valores=substr($valores, 0, -1);

     $SQL = "INSERT INTO tb_bancos_proveedores (ln_supplierid,ln_bank_id,ln_referencia,nu_cuenta,nu_clabe_interbancaria,ln_usuario,ln_activo) VALUES ".$valores;
     $ErrMsg='Error al obtener  informacion de proveedores';
     $TransResult = DB_query($SQL, $db, $ErrMsg);

     $aux='';
     if($datos[1]=='1'){

      }else{
        $aux="<br> El dígito verificador(último dígito de CLABE ) no es correcto consulte con su banco";
      }

       $contenido = 'Se agregó con éxito los datos asociados a la CLABE <b>'.$clabe.'</b>'.$aux.$datos[0];
       $result = true;
      }else{
        $contenido = 'Ya existe la cuenta';
        $result = true;
      }
    
    //  }else{

    //     $contenido = $datos[0];
    //     $result = true;
    // }
    
    
  //   } else{

  //   $contenido = 'La cuenta contable no esta dada de alta';
  //   $result = true;
  // }


    break;


 
        
    default:
        // code...
        break;
    }


$dataObj = array('info' =>'', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
?>