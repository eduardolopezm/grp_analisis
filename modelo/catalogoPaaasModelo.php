<?php
/**
 * Panel para el PAAAS
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link /anexoTecnicoModelo.php
 * Fecha Creación: 29.12.17
 * Se genera el presente programa para la visualización de la información
 * de los anexos técnicos que se generan para las inquisiciones.
 */

    // ini_set('display_errors', 1);
    // ini_set('log_errors', 1);
    // error_reporting(E_ALL);
    // ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
/**/
//
$PageSecurity = 1;
$PathPrefix = '../';
$funcion = 2373;
define('FUNCTIONID', $funcion);

session_start();
include($PathPrefix . 'config.php');
include($PathPrefix . "includes/SecurityUrl.php");
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
include($PathPrefix . 'includes/DateFunctions.inc'); # Include para el periodo


/*************************** EJECUCION DE FUNCIONES ***************************/
$data = call_user_func_array($_POST['method'], [$db]);
/*************************** MODIFICACION DE HEADER ***************************/
header('Content-type:application/json;charset=utf-8');
/**************************** ENVIO DE INFORMACIÓN ****************************/
echo json_encode($data);

/************************** DESARROLLO DE FUNCIONES ***************************/

# DECLARACION DE CONSTANTES PARA EL MODULO
define('CURRENTUSER', $_SESSION['UserID']);

/**
 * Funcion para la consulta de todos los esenarios que se encuentran en la base
 * de datos segun los criterios de filtrado
 * @param  [type] $db [description]
 * @return [type]     [description]
 */
function show($db)
{
    $data = [
        'success' => false,
        'msg'     =>
        'Ocurrio un incidete al momento de consultar la información.
        Favor de contactar al administrador.'
    ];
    # definicion de variables de la funcion
    $enc = new Encryption();
    $info = processingPost();
    $perfil = getPerfil($db);
    $statusTemp = getStatus($db)['content'];
    $rows = array();
    # procesamiento de la información de los esenarios
    $sql = getSqlByData($info);
    //echo $sql;
    $result = DB_query($sql, $db);
    # procesamiento de la información de los esenarios
    while ($rs = DB_fetch_array($result)) {

        $folio  = $rs['folio'];
        $consumido1=buscarTotalGastado($folio,$db);
        $strCod = $enc->encode("&folio=>".$folio."&estatus=>".$rs['estatus']."&inicio=>".$rs['inicio']."&fin=>".$rs['termino']);
        $url    =
            '<a href="paas.php?URL='.$strCod.'" style="color: blue;">
            <u>'.$folio.'</u>
            </a>';
        $status = $statusTemp[ $rs['estatus'] ]['label'];
        $rows[] = [
            'ur'         => utf8_encode($rs['ur']." ".$rs['desUr']),
            'ue'         => utf8_encode($rs['ue']." ".$rs['desUe']),
            'iFolio'     => $folio,
            'folio'      => $url,
            'oficio'     => utf8_encode($rs['oficio']),
            'fecha'      => $rs['captura'],
            'fechaDesde' => $rs['inicio'],
            'fechaHasta' => $rs['termino'],
            'sSta'       => $status,
            'anio'       => $rs['anio'],
            'status'     => $rs['estatus'],
            'gastado' =>$consumido1[0],
            'asignado'=> buscarAsignado($db,$rs['ur'],$rs['ue'])
        ];
    }
    $data['sql'] = $sql; //$sql;
    $data['content'] = $rows;
    # agregado de datos fura de la consulta
    $data['permissions'] = getPermissions($db);
    return $data;
}

// /**
//  * Funcion para la actualizacion prinsipalemente del estatus ya sea que se
//  * abance o se retroceda el esenario
//  * @param  [type] $db [description]
//  * @return [type]     [description]
//  */
// function update($db)
// {
//     // code...
// }
//
// /**
//  * Funcion para la eliminación logica de los esenarios cumpliendo con las
//  * precondiciones que sean definidas
//  * @TODO: Es necesario preguntar hacerca de las pre condionones para la
//  * eliminacion del esenario asi como los mensajes de respuesta
//  * @param  [type] $db [description]
//  * @return [type]     [description]
//  */
// function delete($db)
// {
//     // code...
// }

///////////////////////////////////////////////////////////////////////////////
//                           FUNCIONES DE UTILERIA                           //
///////////////////////////////////////////////////////////////////////////////

/**
 * Funcio para la comprobación de permisos que se tiene para el sistema y que
 * el usuario logeado puede tener
 * @param  [type] $db [description]
 * @return [type]     [description]
 */
function getPermissions($db)
{
    $data = [];
    // // permisos posibles sobre el modulo
    // $permissionsForModule = [];
    // foreach ($permissionsForModule as $key => $value) {
    //     if(Havepermission())
    // }
    return $data;
}

/**
 * Función para le generación del query para la consulta de la información
 * de los esenarios guardados en base de datos
 * @param  array  $data [description]
 * @return [type]       [description]
 */
function getSqlByData($data=[])
{
    $str = "SELECT
        
        -- CONCAT(tcep.id_nu_ur,' ',ya.desc_ur)AS ur
        -- ,CONCAT( tcep.id_nu_ue,' ',ad.desc_ue) AS ue
        tcep.`id_nu_ur` as ur
        ,tcep.`id_nu_ue` as ue
         -- CONCAT (id_nu_ur,'',(SELECT desc_ur from  tb_cat_unidades_responsables where ur='I6L') ) AS ur
        ,(SELECT desc_ur from  tb_cat_unidades_responsables where ur=tcep.`id_nu_ur`) as desUr
        ,(SELECT desc_ue from  tb_cat_unidades_ejecutoras  where ur=tcep.`id_nu_ur` and ue=tcep.`id_nu_ue` ) as desUe
        
        ,tcep.`id_nu_folio_esenario` AS folio
        ,tcep.`ln_oficio` AS oficio
        ,DATE_FORMAT(tcep.`dtm_fecha_alta`, '%d/%m/%Y') AS captura
        ,DATE_FORMAT(tcep.`dtm_fecha_inicio`, '%d/%m/%Y') AS inicio
        ,DATE_FORMAT(tcep.`dtm_fecha_termino`, '%d/%m/%Y') AS termino
        ,tcep.`ind_estatus` AS estatus
        ,tcep.`nu_anio` as anio
   
        FROM tb_cat_esenario_paaas AS tcep
        JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tcep.id_nu_ur AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
        JOIN `tb_sec_users_ue` ON `tb_sec_users_ue`.`userid` = '".$_SESSION['UserID']."' AND tcep.id_nu_ur = `tb_sec_users_ue`.`tagref` AND  tcep.`id_nu_ue`= `tb_sec_users_ue`.`ue`
    ";
    # condiciones de filtrado
    if (!emptyData($data)) {
        # extraccion de los datos pasados a la funcion
        extract($data);
        # bandera de condicion
        $flag = 0;
        $considicion = " WHERE ";
        $alternativa = " AND ";
        # filtro pro estatus
        if (!empty($data['status'])) {
            $flag = 1;
            $str .= $considicion . " tcep.`ind_estatus` = '$status' ";
        }

        # filtro de dependencia
        // if (!empty($data['selectRazonSocial'])) {
        //     $selectRazonSocial = sprintf("%'02d", $selectRazonSocial);
        //     $str .= ($flag==0? $considicion : $alternativa)
        //         . " tcep.`id_nu_dependencia` = '$selectRazonSocial' ";
        //     $flag = 1;
        // }

        # filtro de unidad responsable
        if (!empty($data['selectUnidadNegocio'])) {
            $str .= ($flag==0? $considicion : $alternativa)
                . " tcep.`id_nu_ur` = '$selectUnidadNegocio' ";
            $flag = 1;
        }

        # filtro de unidad ejecutora
        if (!empty($data['selectUnidadEjecutora'])  &&
            ($data['selectUnidadEjecutora'] != -1
            || $data['selectUnidadEjecutora'] != '-1')
        ) {
            $str .= ($flag==0? $considicion : $alternativa)
                . " tcep.`id_nu_ue` = '$selectUnidadEjecutora' ";
            $flag = 1;
        }else{

             //$str .= ($flag==0? $considicion : $alternativa)
              //  . " ";
            $flag = 1;
        }

        # filtrado por fechas
        if (!empty($data['dateDesde']) && !empty($data['dateHasta'])) {
            # fechas inicio y fin
            $dateDesde = comvertDate($dateDesde);
            $dateHasta = comvertDate($dateHasta);
            $str .= ($flag==0? $considicion : $alternativa)
                . " tcep.`dtm_fecha_alta` >= '$dateDesde 00:00:00' "
                . $alternativa
                . " tcep.`dtm_fecha_alta` <= '$dateHasta 23:59:59' ";
            $flag = 1;
        } elseif (!empty($data['dateDesde']) && empty($data['dateHasta'])) {
            $dateDesde = comvertDate($dateDesde);
            # fechas inicio y !fin
            $str .= ($flag==0? $considicion : $alternativa)
                . " tcep.`dtm_fecha_alta` >= '$dateDesde 00:00:00' ";
            $flag = 1;
        } elseif (empty($data['dateDesde']) && !empty($data['dateHasta'])) {
            $dateHasta = comvertDate($dateHasta);
            # fechas !inicio y fin
            $str .= ($flag==0? $considicion : $alternativa)
                . " tcep.`dtm_fecha_alta` <= '$dateHasta 23:59:59' ";
            $flag = 1;
        }
    }
    # colocacion de los datos de ordenamiento
    $str .= " GROUP BY id_nu_esenario_paaas  ORDER BY
        tcep.`id_nu_folio_esenario`  DESC
        ,tcep.id_nu_ur  DESC
        ,tcep.id_nu_ue   DESC
        ,tcep.`ind_estatus`  DESC
        ,tcep.`dtm_fecha_alta`  DESC
        ,tcep.`dtm_fecha_inicio`  DESC
        ,tcep.`dtm_fecha_termino`  DESC
        -- @TODO: ES NECESARIO COLOCAR EL ORDEN QUE TENDRALA CONSULTA ";
    /*   */
  
    return $str;
}

function emptyData($data=[])
{
    $flag = 1;
    if (empty($data)) {
        return $flag;
    }
    foreach ($data as $key => $value) {
        if (!empty($value) && ($value != 0 || $value != '0')) {
            $flag = !1;
        }
    }

    return $flag;
}

/**
 * Funcion para procesar los datos del post como remosion de variables,
 * comprobacion de tipo de datos, parceo de seguridad y codificacion
 * @return [type] [description]
 */
function processingPost()
{
    $data = [];
    $clone = $_POST;
    $files = getDocuments();
    unset($clone['method']);
    # codificacion en dormato UTF-8
    foreach ($clone as $key => $value) {
        if (gettype($value) == 'string') {
            $data[$key] = utf8_decode($value);
        } else {
            $data[$key] = $value;
        }
    }
    # se colocan los archivos enviados en caso de existir
    if (!empty($files)) {
        $data['archivos'] = $files;
    }

    return $data;
}

/**
 * funcion para el procesamiento de los archivos al momento de obtener de hacer
 * una solicitud al servidor en formato POST
 * @param  string $nombreIndice [description]
 * @return [type]               [description]
 */
function getDocuments($nombreIndice='archivos')
{
    # se compureba la existencion de la informacion
    $docs = !empty($_FILES[$nombreIndice])? $_FILES[$nombreIndice] : [];
    $data = [];# creacion contenedor
    if (!empty($docs)) {
        # iteracion por los elementos enviados
        foreach ($docs['name'] as $key => $doc) {
            $data[] = array(
                'error'=>$docs['error'][$key],
                'name'=>$docs['name'][$key],
                'size'=>$docs['size'][$key],
                'temp_name'=>$docs['tmp_name'][$key],
                'type'=>$docs['type'][$key],
            );
        }
    }

    return $data;
}

/**
 * Funcion para la converción de una fecha dada en un formato que sea aseptado
 * por la base de datos
 * @param  [type] $fecha [description]
 * @return [type]        [description]
 */
function comvertDate($fecha)
{
    $exp = explode('-', $fecha);
    return "$exp[2]-$exp[1]-$exp[0]";
}

/**
 * Funcion para la obtencion de los estatus del programa
 * segun una funcion dada
 * @param  [type] $db [description]
 * @return [type]     [description]
 */
function getStatus($db)
{
    $data = array(
        'success'=>false,
        'msg'=>'No se encontraron datos'
    );
    $functionid = !empty($_POST['functionid'])?$_POST['functionid']:FUNCTIONID;
    $sql = "SELECT DISTINCT statusname, namebutton, sn_orden
        FROM tb_botones_status
        WHERE sn_funcion_id = '".$functionid."'
        AND sn_flag_disponible = 1
        ORDER BY sn_orden";
    $result = DB_query($sql, $db);
    if (DB_num_rows($result)) {
        $rows = array(
            [
                'label' => 'Seleccione una opción',
                'value' => ''
            ]
        );
        while ($rs = DB_fetch_assoc($result)) {
            $rows[$rs['statusname']] = [
                'label' => utf8_encode($rs['namebutton']),
                'value' => $rs['statusname']
            ];
        }
        $data['content'] = $rows;
        $data['success'] = true;
    }
    return $data;
}
function buscarTotalGastado($valor,$db){
$total=0;
$ue=0;
$ur=0;
$SQL=" SELECT SUM(tb_cat_esenario_detalle.nu_valorestimado) AS total,tb_cat_esenario_paaas.id_nu_ur as ur, tb_cat_esenario_paaas.id_nu_ue as ue  FROM tb_cat_esenario_paaas  
  INNER JOIN  tb_cat_esenario_detalle ON tb_cat_esenario_detalle.id_nu_esenario_paaas= tb_cat_esenario_paaas.id_nu_esenario_paaas
  WHERE tb_cat_esenario_paaas.id_nu_folio_esenario='".$valor."'";
  $res = DB_query($SQL, $db);
        while ($fila = DB_fetch_array($res)) {
            $total=$fila['total'];
            $ur=$fila['ur'];
            $ue=$fila['ue'];
        }

  $data=  array( );
  $data[]=round($total,0); //number_format($total, $_SESSION['DecimalPlaces'], '.', '');
  $data[]=$ur;
  $data[]=$ue;
  return $data;

}
function buscarAsignado($db,$ur,$ue){
   


    $total=0;
    $partidasBudget=[];
// AND  chartdetailsbudgetlog.partida_esp NOT LIKE '5%'
    if(($ur!="") && ($ue!="") ){

 
              $sqlBudget="SELECT DISTINCT
                     SUM(chartdetailsbudgetlog.qty) AS total,
                     chartdetailsbudgetlog.partida_esp as partida
                     FROM chartdetailsbudgetlog 
                      WHERE  nu_tipo_movimiento IN (251,253,254)
                      AND  chartdetailsbudgetlog.partida_esp NOT LIKE '1%'
                      AND  chartdetailsbudgetlog.partida_esp NOT LIKE '4%'
                    AND  chartdetailsbudgetlog.partida_esp != '32201'
                    AND  chartdetailsbudgetlog.partida_esp != '37501'
                    AND  chartdetailsbudgetlog.partida_esp != '37504'
                    AND  chartdetailsbudgetlog.partida_esp != '39801'
                    AND  chartdetailsbudgetlog.partida_esp != '39202'
                      AND  cvefrom LIKE '%".$ur.$ue."%'
                    
                        ";
                   //print_r($sqlBudget);     
            
           $res1 = DB_query($sqlBudget, $db);
           
               while ($rows = DB_fetch_array($res1)) {
                 $total=$rows['total'];
                 
          }
     }
//     while ($rows = DB_fetch_array($res1)) {
//          $aux=$rows['partida'];
//          $partidasBudget[$aux]=$rows['total'];
//   }
//   //print_r($partidasBudget);
//   $sqlAssets="SELECT  partidas.partidaEspecifica as partida,stockmaster.stockid as id,stockmaster.description as descri,stockmaster.mbflag as flag,stockmaster.units as unidad,
//          partidas.descPartidaEspecifica as desPartida
//         FROM stockmaster
//         INNER JOIN tb_partida_articulo partidas ON stockmaster.eq_stockid= partidas.eq_stockid
//         WHERE partidas.partidaEspecifica NOT LIKE '5%'
//         ORDER BY  partidas.partidaEspecifica;";


// $res2 = DB_query($sqlAssets, $db);

//   while ($rows = DB_fetch_array($res2)) {
//         $aux= array_key_exists($rows['partida'],$partidasBudget); // see if  exit partida index return true if exists
//         if($aux){
            
//                 $total.=intval(($partidasBudget[$rows['partida']]));
                 
//         }// end if exists partida  in budget
        
//   }
 
  $total= round($total,0); // number_format($total, $_SESSION['DecimalPlaces'], '.', '');
  return $total;
}
/**
 * [changeStatus description]
 * @param  [type] $db [description]
 * @return [type]     [description]
 */
function changeStatus($db){
    $estatusN=$_POST['status'];
    $folios=$_POST['folios'];
    $mensaje='';
    $data=array();
    $name=$_POST['name'];
      $flagGestionado=false;
    if(($name!='Rechazar') && ($name!='Cancelar')){
    
    if($estatusN!=6){

        // $data = array(
        //     'success'=>false,
        //     'msg'=>'Hecho'
        // );
        $numerosFolios='';
        if(($estatusN==2) || ($estatusN==3) || ($estatusN==4) ){ // hacer  validacion antes de autorizar

           // for ($ya=0;$ya<1;$ya++) {
              
                $consumido=buscarTotalGastado($folios[0],$db); // total del folio
                 if($consumido[1]!=""){// valida  si existe presupuesto
                    
                    $total= buscarAsignado($db,$consumido[1],$consumido[2]);
                       
                      $aux=number_format($total, $_SESSION['DecimalPlaces'], '.', '');
                      $aux1=$consumido[0];
                       if($aux==$aux1){

                         $SQL="UPDATE  tb_cat_esenario_paaas SET ind_estatus='".$estatusN."' WHERE id_nu_folio_esenario  IN(".$folios[0].")";
                           
                        $res = DB_query($SQL, $db);
                       }else{
                         $mensaje="No se puede avanzar no ha consumido el monto total <b> ".$aux." </b>de las partidas en el escenario hasta el momento lleva asignado <b> ".$aux1;
                       }

                 }else{
                        if(($aux==0)){
                            $mensaje="No cuenta con presupuesto asignado para esta UE";
                        }else{
                            $mensaje="No se puede avanzar no ha consumido el monto total <b> ".$aux." </b>de las partidas en el escenario hasta el momento lleva asignado <b> ".$aux1;
                        }
                 }
               
              
            //}

         }//else{// si es el estatus 
       //      foreach ($folios as $ad => $valor) {
       //          $numerosFolios.="'".$valor."',";   
       //      }
       //  }
       //  if($numerosFolios!=''){

       //  $numerosFolios= substr($numerosFolios, 0, -1);
       //  $SQL="UPDATE  tb_cat_esenario_paaas SET ind_estatus='".$estatusN."' WHERE id_nu_folio_esenario  IN(".$numerosFolios.")";
       //   print_r($SQL);
       //   exit();
       //  $res = DB_query($SQL, $db);
       // }
    }else{ // por dictaminar
        //dictaminado
        //estatus dictaminado  4
        
        $count=0;
        $mensaje="La(s) siguientes UE no estan en el estatus <b>'por dictaminar'</b> y  por ello no se puede pasar al estado Dictaminado <br>";
        $SQL="SELECT id_nu_folio_esenario as folio,id_nu_ue AS ue,ind_estatus FROM tb_cat_esenario_paaas"; // FALTA PONER UR Y DEPENDENCIA EN CONSUTLTA
        $res = DB_query($SQL, $db);
        while ($fila = DB_fetch_array($res)) {
           
           if(($fila['ind_estatus']!='4') && ($fila['ind_estatus']!='5')  ){
             $mensaje.="<i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> UE ".$fila['ue']." con folio ".$fila['folio']."<br>";
             $count++;
           }
         
        }
       
      //print_r($count);
        if($count==0){
            $SQL="UPDATE  tb_cat_esenario_paaas SET ind_estatus='".$estatusN."' WHERE ind_estatus='4'";
            $res = DB_query($SQL, $db);
            $mensaje="";
            $mensaje="Se acutalizado los estatus a Dictaminado";
            $flagGestionado=true;
        }
    }
}else{// rechazar o  // cancelar
        // cuando el estatus esta en 2  y es autorizado
        // $perfil=fnPerfil($db); //
        // if($perfil=="aut"){
        //     if($name=="Rechazar"){
                   
        //     }
        // }
      $SQL="UPDATE  tb_cat_esenario_paaas SET ind_estatus='".$estatusN."' WHERE id_nu_folio_esenario  IN(".$folios[0].")";
            $res = DB_query($SQL, $db);
            $mensaje="";
           // print_r($SQL);
}

 $data['content'] = $mensaje;
 $data['success'] = true;
 $data['gestionado'] = $flagGestionado;
 
 return $data;
}
function fnCaractterProce($caracter){
    $retorno='';
    //'N-Nacional':'N-Nacional','I-Internacional bajo TLC':'I-Internacional bajo TLC','A-Internacional Abierto':'A-Internacional Abierto';
    switch ($caracter) {
        case 'N':
            $retorno='N-Nacional';
            break;
         case 'I':
            $retorno='I-Internacional bajo TLC';
            break;
        
         case 'A':
            $retorno='A-Internacional Abierto';
            break;
        default:
            $retorno='No definido';
            break;


    }
return $retorno;
}
function tipoProce($caracter){
    
   $retorno='';
    //'N-Nacional':'N-Nacional','I-Internacional bajo TLC':'I-Internacional bajo TLC','A-Internacional Abierto':'A-Internacional Abierto';
    switch ($caracter) {
        case 'LP':
            $retorno='LP-Licitación Pública';
            break;
         case 'AD':
            $retorno='AD-Adjudicación Directa';
            break;
         case 'I3P':
            $retorno='I3P-Invitación a cuando menos 3 personas';
            break;
         case 'APPC':
            $retorno='APPC-Concurso Asociaciones Público Privadas';
            break;
         case 'APPAD':
            $retorno='APPAD-Adjudicación Directa Asociaciones Público Privadas';
            break;
         case 'APPI3P':
            $retorno='APPI3P-Invitación a cuando menos 3 Asociaciones Público Privadas';
            break;
        default:
            $retorno='No definido';
            break;


    }
return $retorno;
}

function obtenerFolios($db){
    $nue=$_POST['ue'];
    $ur= $_POST["ur"];
    $count=0;
    $perfil='';
    $banderaFolio=true;
    $mensaje="La(s) siguientes UE necesitan el estatus Dictaminado  para generar el CSV de compranet <br>";
        
    // FALTA PONER UR Y DEPENDENCIA EN CONSUTLTA
    $SQL="SELECT id_nu_folio_esenario as folio,id_nu_ue AS ue,ind_estatus 
    FROM tb_cat_esenario_paaas WHERE id_nu_ur='".$ur."' AND nu_anio='".date("Y")."'"; 

    $res = DB_query($SQL, $db);

    while ($fila = DB_fetch_array($res)) {
       if(($fila['ind_estatus']!='4') && ($fila['ind_estatus']!='5') && ($fila['ind_estatus']!='6')){
         $mensaje.="<i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> La ".$fila['ue']." con folio ".$fila['folio']."<br>";
         $count++;
       }
    }

    $folios='';
    //$banderaFolio=false;
    // para detalle
    //if($count==0){
    $perfil=fnPerfil($db);
    $SQL1='';
    if($perfil=='aut'){
         $SQL1=" SELECT id_nu_folio_esenario as folios FROM tb_cat_esenario_paaas WHERE (ind_estatus!=5);";
     }else{
        if($nue!="-1"){
            $SQL1=" SELECT id_nu_folio_esenario as folios FROM tb_cat_esenario_paaas WHERE (ind_estatus!=5) AND id_nu_ue='".$nue."';";
        } else {
            $SQL1=" SELECT id_nu_folio_esenario as folios FROM tb_cat_esenario_paaas WHERE (ind_estatus!=5) AND id_nu_ur='".$ur."';";
        }
     }
   
    $res1= DB_query($SQL1, $db);

    while ($fila1 = DB_fetch_array($res1)) {
        $folios.=$fila1['folios'].",";
   
    }

    $folios=substr($folios,  0, -1);
    
    if($count==0){
        $mensaje=$folios;

    }else{
         $banderaFolio=false;
        
    }
   
    $csvLeyendas = array();
    //                          
    $csvLeyendas[] = array(
         'folio'=>'FOLIO' 
        ,'ur'=>'UR'
        ,'ue'=>'UE'
        ,'inicio'=>'FECHA  INICIO'
        ,'fin'=>'FECHA  FIN'
        ,'estatus'=>'ESTATUS'
        ,'oficio'=>'OFICIO'
        ,'comentarios'=>'COMENTARIOS',
        'clave'      =>'CLAVE CUCOP'
        ,'partida'    =>'PARTIDA PRODUCTO'
        ,'descri'     =>'PRODUCTO'
        ,'valesti'    =>'VALOR ESTIMADO'
        ,'valiva'    =>'IVA'
        ,'valbruto'    =>'VALOR BRUTO'
        
        ,'valpyme'    =>'VALOR MIPyMES'
        ,'valtlc'     =>'VALOR EN TLC'
        ,'cantidad'   =>'CANTIDAD'
        ,'unidad'     =>'UNIDAD MEDIDA'
        ,'caracProce' =>'CARACTER PROCEDIMIENTO'
        ,'entidad'    =>'ENTIDAD FEDERATIVA'
        ,'trim1'     =>'PORCENTAJE 1ER TRIMESTRE'
        ,'trim2'     =>'PORCENTAJE 2DO TRIMESTRE'
        ,'trim3'     =>'PORCENTAJE 3ER TRIMESTRE'
        ,'trim4'     =>'PORCENTAJE 4ER TRIMESTRE'
        ,'fecha'     =>'FECHA REGISTRO'
        ,'pluriAnual'=>'PLURIANUAL'
        ,'aniosPlurianuales'   =>'AÑOS PLURIANUALES'
        ,'valortotalPluriAnual'=>'VALOR TOTAL PLURIANUAL'
        ,'clavePrograma'       =>'CLAVE PROGRAMA FEDERAL'
        ,'fechaRegistro'       =>'FECHA INICIO OBRA'
        ,'fechafin'            =>' FECHA FIN OBRA'
        ,'tipoProcedimiento'   =>' TIPO PROCEDIMIENTO '

    );
    if(($folios!='') &&(!is_null($folios)) ){
   
    $SQL=' SELECT 
      id_nu_folio_esenario  
     ,id_nu_ur 
     ,id_nu_ue 
     ,dtm_fecha_inicio
     ,dtm_fecha_termino 
     ,(SELECT namebutton FROM tb_botones_status WHERE statusname=ind_estatus AND sn_funcion_id="2373" ) AS estatus
     ,ln_oficio 
     ,ln_comments AS comentario
     ,ln_usuario
     ,nu_clavecucop
     ,nu_partida
     ,(SELECT description  FROM stockmaster WHERE stockid=escenenario.nu_clavecucop) as descri
     ,nu_valorestimado
     ,nu_valor_iva
     ,((nu_valorestimado*((nu_valor_iva)/100) ) + nu_valorestimado) as valBruto
     ,nu_valormipymes
     ,nu_valorenctlc
     ,nu_cantidad
     ,CONCAT(nu_unidadmedida,"-",(SELECT ln_descripcion  FROM tb_units_twice  WHERE tb_units_twice.ln_claveunidad=escenenario.nu_unidadmedida)) as desciUnidad
     ,nu_caracterprocedimiento
    ,CONCAT(nu_entidadfederativa ,"-",(SELECT descripcion FROM g_cat_geografico WHERE id_nu_geografico=escenenario.nu_entidadfederativa )) as entidadnFede
    ,nu_porcentaje1ertrim
    ,nu_porcentaje2dotrim
    ,nu_porcentaje3ertrim
    ,nu_porcentaje4totrim
    ,dtm_fecharegistro as fecha
    ,nu_plurianual
    ,nu_aniosplurianuales
    ,nu_valortotalplurianual
    ,nu_claveprogramafederal
    ,nu_fechainicioobra
    ,nu_fechafinobra
    ,nu_tipoprocedimiento
      FROM  tb_cat_esenario_detalle as escenenario 
      INNER JOIN tb_cat_esenario_paaas 
      ON   escenenario.id_nu_esenario_paaas= tb_cat_esenario_paaas.id_nu_esenario_paaas
      WHERE 285=285
      AND tb_cat_esenario_paaas.id_nu_folio_esenario in('.$folios.')';

    $res= DB_query($SQL, $db);
    //[{"Id":1,"UserName":"Sam Smith"},{"Id":2,"UserName":"Fred Frankly"},{"Id":1,"UserName":"Zachary Zupers"}]';
    while ($fila = DB_fetch_array($res)) {
        $fecha= date_create($fila['fecha']);
        $datoCampo= date_format($fecha, "d/m/Y");

        $fini=date_create($fila['dtm_fecha_inicio']);
        $auxfini=date_format($fini, "d/m/Y");

        $ffin=date_create($fila['dtm_fecha_termino']);
        $auxffin=date_format($ffin, "d/m/Y");
        
        $csvLeyendas[] = array(
        'folio'=>$fila['id_nu_folio_esenario'] 
        ,'ur'=>$fila['id_nu_ur']
        ,'ue'=>$fila['id_nu_ue']
        ,'inicio'=>$auxfini
        ,'fin'=>$auxffin
        ,'estatus'=>$fila['estatus']
        ,'oficio'=>$fila['ln_oficio']
        ,'comentarios'=>$fila['comentario'],
        'clave' =>utf8_encode($fila['nu_clavecucop']),
        'partida' =>utf8_encode($fila['nu_partida']),
        'descri' =>utf8_encode($fila['descri']),
        'valesti' =>$fila['nu_valorestimado'],
        'valiva' =>$fila['nu_valor_iva'],
        'valbruto' =>$fila['valBruto'],
        'valpyme' =>$fila['nu_valormipymes'],
        'valtlc' =>$fila['nu_valorenctlc'],
        'cantidad' =>$fila['nu_cantidad'],
        'unidad' =>utf8_encode($fila['desciUnidad']),
        'caracProce' =>utf8_encode(fnCaractterProce($fila['nu_caracterprocedimiento'])),
        'entidad' =>utf8_encode($fila['entidadnFede'])
        ,'trim1'=>$fila['nu_porcentaje1ertrim']
        ,'trim2'=>$fila['nu_porcentaje2dotrim']
        ,'trim3'=>$fila['nu_porcentaje3ertrim']
        ,'trim4'=>$fila['nu_porcentaje4totrim']
        ,'fecha'=> $datoCampo
        ,'pluriAnual'=>$fila['nu_plurianual']
        ,'aniosPlurianuales'=>$fila['nu_aniosplurianuales']
        ,'valortotalPluriAnual'=>$fila['nu_valortotalplurianual']
        ,'clavePrograma'=>$fila['nu_claveprogramafederal']
        ,'fechaRegistro'=>$fila['nu_fechainicioobra']
        ,'fechafin'=>$fila['nu_fechafinobra']
        ,'tipoProcedimiento'=>tipoProce($fila['nu_tipoprocedimiento'])
        );
    }
 }// si folio diferente  de  vacio
//}

if($perfil=='aut'){
  $data['content'] = $mensaje;// el mensaje  contiene  los folios si no se tiene problemas
  $data['success'] = true;
  $data['folios']=$banderaFolio;
  $data['csvLeyenda']=$csvLeyendas;
  $data['prueba']=$contador;
}else{
     $data['content'] ="";
     $data['success'] = true;
     $data['folios']=$banderaFolio;
     $data['csvLeyenda']=$csvLeyendas;
}

 return $data;
}

/**
 * Funcion para la obtencion del perfil del usuario actual
 * @param  [type] $db [description]
 * @return [type]     [description]
 */
function getPerfil($db)
{
	$userid = $_SESSION['UserID'];
	$sql = "SELECT profileid FROM sec_profilexuser WHERE userid = '$userid'";
	$resl = DB_query($sql, $db);
	$p = DB_fetch_assoc($resl);
	return $p['profileid'];
}
/**
 * [fnPerfil obtiene el perfil segun el permiso la  funcion  getperfil obtiene el id del perfil pero no es funcional  si se resetea]
 * @param  [type] $db [description]
 * @return [type]     [description]
 */
function fnPerfil($db){

$perfil='';
$contador=0;
$estatus=0;
$inicio='';
$fin='';
   if(Havepermission($_SESSION ['UserID'], 2378, $db)==1) { //validador
        $perfil="capt";
       $contador++;
    }elseif(Havepermission($_SESSION ['UserID'], 2380, $db)==1) {//autorizador
        $perfil="val";
        $contador++;
    }elseif(Havepermission($_SESSION ['UserID'], 2389, $db)==1) {//almacenista
        $contador++;
        $perfil="aut";
    }
 

return $perfil;
}

function  searchConsume(){
     $enc = new Encryption;
     $liga = $enc->encode("&soloConsumos=>true");

    return "consumosFecha.php?URL=".$liga;
}



 
