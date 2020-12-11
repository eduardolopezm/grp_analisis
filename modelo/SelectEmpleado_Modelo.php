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
//
// Los campos ln_supplierid deben modificarse por el id de id_nu_empleado que se usará en la tabla donde se guardarán las partidas de empleados

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
require $PathPrefix . 'config.php';
require $PathPrefix . 'includes/ConnectDB.inc';
$funcion=2370;
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
function digitoVerifCLABE($pClabe){
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
   /*$existe=false;
   $claveBank='';
   $msg='';
   $digitoV='';
   $SQL = "SELECT count(*) as total FROM tb_bancos_proveedores WHERE ln_supplierid='".$idProvs."' AND ln_bank_id='".$bank."' AND nu_cuenta='".$cuenta."' OR nu_clabe_interbancaria='".$clabe."'";
      
    $ErrMsg='Error al obtener  informacion de empleados';
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
    */
}
switch ($proceso) {
  case 'mostrarEmpleados': 

    $SQL = 'SELECT * FROM  tb_empleados WHERE 1 = 1';
    if(($_POST['claveempleado'])!='' ){
      $SQL.=" AND LOWER(sn_clave_empleado) like LOWER('%".$_POST['claveempleado']."%')";
    }

    if(($_POST['ur'])!=''&&($_POST['ur'])!='-1'){
      $SQL.=" AND LOWER(tagref) like LOWER('%".$_POST['ur']."%')";
    }
    if(($_POST['ue'])!=''&&($_POST['ue'])!='-1'){
      $SQL.=" AND ue = '".$_POST['ue']."'";
    }

    // Busca en Nombre Completo
    if(($_POST['nombrecompleto'])!='' ){
      $SQL.=" AND ( LOWER(ln_nombre) like LOWER('%".$_POST['nombrecompleto']."%')";
      $SQL.=" OR LOWER(sn_primer_apellido) like LOWER('%".$_POST['nombrecompleto']."%')";
      $SQL.=" OR LOWER(sn_segundo_apellido) like LOWER('%".$_POST['nombrecompleto']."%') )";
    }

    // Busca por separado en Nombre, Apellido Paterno y Apellido Materno
    if(($_POST['nombre'])!='' ){
      $SQL.=" AND LOWER(ln_nombre) like LOWER('%".$_POST['nombre']."%')";
    }
    if(($_POST['apPat'])!='' ){
      $SQL.=" AND LOWER(sn_primer_apellido) like LOWER('%".$_POST['apPat']."%')";
    }
    if(($_POST['apMat'])!='' ){
      $SQL.=" AND LOWER(sn_segundo_apellido) like LOWER('%".$_POST['apMat']."%')";
    }

    if(($_POST['rfc'])!=''){
      $SQL.=" AND LOWER(sn_rfc) like LOWER('%".$_POST['rfc']."%')";
    }

    if(($_POST['curp'])!=''){
      $SQL.=" AND LOWER(sn_curp) like LOWER('%".$_POST['curp']."%')";
    }

    if(($_POST['estatus'])!=''&&($_POST['estatus'])!='-1'){
      $SQL.=" AND ind_activo = '".$_POST['estatus']."'";
    }

    $ErrMsg='Error al obtener información de empleados';
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
 
    while ($myrow = DB_fetch_array($TransResult)){

       $info[]=array(
                  'checkProv'=>false,
                  'claveempleado'=>$myrow['sn_clave_empleado'],
                  'ur'=>$myrow['tagref'],
                  'ue'=>$myrow['ue'],
                  'nombre' =>$myrow['ln_nombre'].( $myrow['sn_primer_apellido'] ? " ".$myrow['sn_primer_apellido'] : "" ).( $myrow['sn_segundo_apellido'] ? " ".$myrow['sn_segundo_apellido'] : "" ),
                  'rfc'=>$myrow['sn_rfc'],
                  'curp'=>$myrow['sn_curp'],
                  'estatus'=>( $myrow['ind_activo']==1 ? "Activo" : "Inactivo" ),
                   'ver'=> '<u id="'.$myrow['id_nu_empleado'].'"><span class="glyphicon glyphicon-eye-open"></span></u>',
                  'Modificar'=> '<u id="'.$myrow['id_nu_empleado'].'"><span class="glyphicon glyphicon-edit"></span></u>',
                  'Eliminar'=>'<u id="'.$myrow['id_nu_empleado'].'"><span class="glyphicon glyphicon-trash"></span></u>',

                  'id' =>$myrow['id_nu_empleado']
                  
                      );
     
     // exit();
              
    }

    $contenido = array('datosEmpleado' => $info, 'sql' => $SQL);
    $result = true;
          
  break;

  case 'empleado':
    $info=array();
    $partidas=array();
    $idEmp=$_POST['idEmp'];
    $SQL = "SELECT * FROM tb_empleados WHERE id_nu_empleado='".$idEmp."'";
    $ErrMsg='Error al obtener  informacion de empleados';
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)){
      $info[]=array(
                'claveempleado'=>$myrow['sn_clave_empleado'],
                'idEmp'=>$myrow['id_nu_empleado'],
                'nombre' =>$myrow['ln_nombre'],
                'apPat' =>$myrow['sn_primer_apellido'],
                'apMat' =>$myrow['sn_segundo_apellido'],
                'rfc' =>$myrow['sn_rfc'],
                'curp' =>$myrow['sn_curp'],
                'puesto' =>$myrow['id_nu_puesto'],
                'ur' =>$myrow['tagref'],
                'ue' =>$myrow['ue'],
                'activo' =>$myrow['ind_activo']
                
                //'tipocambio'=>$myrow['currcode']            
      );
    $idProv = $myrow['sn_clave_empleado'];
      // echo $myrow['id_nu_empleado'];
      // exit();             
    }
    $SQL = "SELECT ln_partida_especifica FROM tb_partidas_proveedores WHERE ln_supplierid='".$idProv."'";
    $ErrMsg='Error al obtener  informacion de proveedores';
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)){
        $partidas[]=$myrow['ln_partida_especifica'];
      }

    $cuenta_contable=0;
    $SQL1 = "SELECT accountcode FROM accountxsupplier WHERE supplierid='".$idProv."'";
    $ErrMsg='Error al obtener  informacion de proveedores';
    $TransResult = DB_query($SQL1, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)){
        $cuenta_contable=$myrow['accountcode'];
    }
    // print_r($SQL1);
    // echo"---";
    $contenido = array('datosEmp' => $info,'partidas'=>$partidas,'cuenta'=>$cuenta_contable);
    $result = true;

    break;


    case 'cuentasProv':
      // A agregar en cuanto sepa donde se guardarán las cuentas de empleado
      /*$info=array();
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
      $result = true;*/
    break;

    case 'eliminarEmp':
      $idEmp = $_POST['idEmp'];
      //$SQL         = "UPDATE   SET activo='N' where cg='".$cg."'"; 
      $SQL         = "UPDATE tb_empleados SET ind_activo = 0 WHERE id_nu_empleado='".$idEmp."'";
      $ErrMsg = "No se eliminó el registro";
      $TransResult = DB_query($SQL, $db, $ErrMsg);
    
      $contenido = "Se eliminó el registro con éxito.";

      $result = true;
    break;



      case 'cuentasContables':
        $info=array();
        $info1=array();
         
       
        $SQL = "SELECT DISTINCT `accountcode` AS valor, `accountname` AS label, `group_` as padre
                FROM `chartmaster`
                INNER JOIN tb_sec_users_ue ON tb_sec_users_ue.userid='".$_SESSION["UserID"]."' 
                WHERE (`accountcode` LIKE '2.1.1.9%' 
                  OR `accountcode` LIKE '2.1'     
                  OR `accountcode` LIKE '2') 
                  AND (chartmaster.ln_clave= tb_sec_users_ue.ue OR `chartmaster`.`nu_nivel` <= 5)
                ORDER BY `group_`, `accountcode` ASC";

        $ErrMsg='Error al obtener  informacion de proveedores';
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $datosCortos = array();
        $datosLargos = array();
      
    
        while ($myrow = DB_fetch_array($TransResult)){
          $veces = substr_count($myrow['valor'],'.');

          if($veces<5){
              $datosCortos[] = [
                  'value' => $myrow['valor'],
                  'text' => $myrow['label']
              ];
          }else{
              $datosLargos[] = [
                  'value' => $myrow['valor'],
                  'text' => $myrow['label']
              ];
          }

          $info[] = array( 'value' => $myrow['valor'], 'texto' => $myrow['valor'] .' | ' . $myrow['label'] );
                  
        }
        $SQL = "SELECT *
          FROM typeoperationdiot
          ORDER BY u_typeoperation";
        $ErrMsg='Error al obtener  informacion de proveedores';
        $TransResult = DB_query($SQL, $db, $ErrMsg);
      
    
        while ($myrow = DB_fetch_array($TransResult)){

          $info1[] = array( 'value' => $myrow['u_typeoperation'], 'texto' => $myrow['typeoperation'] );
                  
        }

        $contenido = array('cuentas' => $info,'diot'=>$info1, 'cuentasMenores' => $datosCortos, 'cuentasMayores' => $datosLargos);
        $result = true;
            
      break;

    case 'countsBanksProv':
      $idSupp=$_POST['idSupp'];
      $SQL = "SELECT sn_clave_empleado FROM tb_empleados WHERE id_nu_empleado = '$idSupp'";
      $ErrMsg = 'Error al obtener  informacion de empleado';
      $TransResult = DB_query($SQL, $db, $ErrMsg);
      $myrow = DB_fetch_array($TransResult);

      $info=array();
      $info1=array();

      if($myrow['sn_clave_empleado']){
        $idSupp=$myrow['sn_clave_empleado'];
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
      }

        $contenido = array('cuentasBancarias' =>$info, 'sql' => $SQL );
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



$dataObj = array('info' =>'', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje, "cuenta"=>$cuenta_contable);
echo json_encode($dataObj);
?>