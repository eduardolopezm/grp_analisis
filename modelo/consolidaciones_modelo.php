<?php
/**
 * Modelo para consolidaciones
 *
 * @category     ABC
 * @package      ap_grp
 * @author       Arturo Lopez Peña <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 21/09/2017
 * Fecha Modificación: 05/08/2017
 */
 // ini_set('display_errors', 1);
 // ini_set('log_errors', 1);
 // error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
require $PathPrefix.'abajo.php';
require $PathPrefix . 'config.php';
require $PathPrefix . 'includes/ConnectDB.inc';
if ($abajo) {
    include $PathPrefix . 'includes/LanguageSetup.php';
}
$funcion=2272;
require $PathPrefix.'includes/SecurityFunctions.inc';
require $PathPrefix.'includes/SQL_CommonFunctions.inc';
require $PathPrefix . 'includes/DateFunctions.inc';
$cvePresupuestal='ClavePresupuestal';
//$permiso = Havepermission ( $_SESSION ['UserID'], 244, $db ); // tenia 2006
//$permisomostrar=Havepermission($_SESSION['UserID'], 1420, $db);
$permisomostrar= Havepermission($_SESSION ['UserID'], 2272, $db); // tenia 2006
//$permisomostrar=1;
$statusRequisicion = 'Autorizado';
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
header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$proceso = $_POST['proceso'];
$info = array();


$datos='';
switch ($proceso){
        //sugerencia
case 'buscar-articulo-requisicion':
    $articulo=$_POST['articulorequisicion'];
    //$SQL="SELECT  stockid, description FROM stockmaster WHERE description like '%".$articulo."%' ORDER BY description";

    /*$SQL=" select 
  count(*) as cantidad,
 purchorderdetails.itemcode,
 stockmaster.description,
 purchorderdetails.itemdescription
 from purchorderdetails 
 inner join purchorders on   purchorderdetails.orderno = purchorders.orderno
 inner join stockmaster on stockmaster.stockid=purchorderdetails.itemcode ,tags,legalbusinessunit,sec_unegsxuser
 where (itemcode LIKE '%".$articulo."%' or stockmaster.description LIKE '%".$articulo."%')
 and purchorders.tagref = tags.tagref
 and sec_unegsxuser.tagref = tags.tagref
 and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
 and legalbusinessunit.legalid = tags.legalid

 group by purchorderdetails.itemcode
 having count(*)>0
 order by itemdescription
"; */
$campo='';
if(is_numeric($articulo)) {
    $campo=" tb_partida_articulo.partidaEspecifica ";
}else{
    $campo= " tb_partida_articulo.descPartidaEspecifica ";
}
$SQL=" SELECT DISTINCT   tb_partida_articulo.descPartidaEspecifica,tb_partida_articulo.partidaEspecifica FROM tb_partida_articulo INNER JOIN stockmaster ON tb_partida_articulo.eq_stockid=stockmaster.eq_stockid WHERE ".$campo. " LIKE '%".$articulo."%'"; //" OR tb_partida_articulo.descPartidaEspecifica  LIKE '%".$idarticulo."%'";

    $ErrMsg = "No hay datos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $datos.='<ul id="articulos-lista-consolida">';
    while ( $myrow = DB_fetch_array($TransResult) ){

        // $dias[] =$myrow['dia'];
        $datos.='<li onClick="fnSelectArticulo(\''.$myrow['descPartidaEspecifica'].'\')">'.$myrow['descPartidaEspecifica']." (".$myrow['partidaEspecifica'].')</li>';
       
       
    }
    $target=$myrow['tagref'];
    $datos.='</ul>';
    $contenido = $datos;
    $result = true;

    break;
case 'partidasAutorizadas':

 $SQL="SELECT DISTINCT 
    tb_partida_articulo.partidaEspecifica as partida,
    tb_cat_partidaspresupuestales_partidaespecifica.descripcion
     FROM purchorderdetails
     INNER JOIN purchorders ON purchorderdetails.orderno = purchorders.orderno
     INNER JOIN stockmaster ON stockmaster.stockid=purchorderdetails.itemcode
     INNER JOIN tags ON purchorders.tagref = tags.tagref 
     INNER JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
     INNER JOIN sec_unegsxuser ON purchorders.tagref=sec_unegsxuser.tagref  AND sec_unegsxuser.userid = '". $_SESSION['UserID'] ."'
    INNER JOIN tb_partida_articulo ON stockmaster.eq_stockid=tb_partida_articulo.eq_stockid
    INNER JOIN tb_cat_partidaspresupuestales_partidaespecifica ON tb_partida_articulo.partidaEspecifica =tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada
    INNER JOIN (SELECT
     purchorderdetails.itemcode,
     purchorderdetails.itemdescription,
     count(*) AS veces
     FROM purchorderdetails 
     INNER JOIN purchorders ON purchorderdetails.orderno = purchorders.orderno
     INNER JOIN stockmaster ON stockmaster.stockid=purchorderdetails.itemcode
     INNER JOIN tags ON purchorders.tagref = tags.tagref 
     INNER JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
     INNER JOIN sec_unegsxuser ON purchorders.tagref=sec_unegsxuser.tagref  AND sec_unegsxuser.userid = '". $_SESSION['UserID'] ."'
     INNER JOIN tb_partida_articulo ON stockmaster.eq_stockid=tb_partida_articulo.eq_stockid
    /* WHERE (tb_partida_articulo.partidaEspecifica  LIKE '%%' OR tb_partida_articulo.descPartidaEspecifica  LIKE '%%' )*/
     WHERE purchorders.requisitionno!= 0 AND purchorders.requisitionno!= ''
     AND purchorderdetails.quantityord > 0
     GROUP BY  purchorderdetails.itemcode,
     purchorderdetails.itemdescription
     HAVING count(*)>1) AS consolidables ON purchorderdetails.itemcode = consolidables.itemcode
     /*WHERE (tb_partida_articulo.partidaEspecifica  LIKE '%%' OR tb_partida_articulo.descPartidaEspecifica  LIKE '%%' )*/
     WHERE purchorders.requisitionno!= 0 AND purchorders.requisitionno!= ''
     AND purchorderdetails.quantityord > 0
     AND purchorderdetails.status=2
     AND purchorderdetails.pedimento!='consolidada' 
     AND purchorders.status='Autorizado'

     ORDER BY partida;";

// DEBE ir  el purchorderdetails.status=4

    $ErrMsg = "No hay datos";
    $sumaTotalArticulos=0;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $datos = array([ 'label'=>'Seleccionar', 'title'=>'Seleccionar', 'value'=>'-1' 
            ]);
    $datos[]=['label'=>'Todas las partidas', 'title'=>'Todas las partidas', 'value'=>'0'];
    while ( $myrow = DB_fetch_array($TransResult) ) {
         $datos[] = [
                    'label'=>$myrow ['partida']." ".$myrow['descripcion'],
                    'title'=>$myrow ['partida']." ".$myrow['descripcion'],
                    'value'=>$myrow ['partida'] 
                ];
    }

    $contenido = $datos;
    $result = true;


break;
case 'buscarArticuloEnRequisisiones':
    
 if(isset($_POST['idArticulo'])){
    $idarticulo=$_POST['idArticulo'];

    if($idarticulo=='0'){
        $idarticulo='';
    }
    $idarticulo=str_replace('`', '', $idarticulo);

    //principal
    /*  $SQL="
    select
    purchorders.requisitionno  as n_requisision,
    purchorderdetails.quantityord as cantidad_articulo, 
    purchorderdetails.itemcode,
    purchorderdetails.itemdescription,
    purchorderdetails.orderno as orden_detalle,
    purchorders.ordernoas orden_purch
    from purchorderdetails 
    inner join purchorders on   purchorderdetails.orderno = purchorders.orderno
    inner join stockmaster on stockmaster.stockid=purchorderdetails.itemcode ,tags,legalbusinessunit,sec_unegsxuser
    where (purchorderdetails.itemcode LIKE '%".$idarticulo."%' or stockmaster.description LIKE '%".$idarticulo."%')
    and purchorders.tagref = tags.tagref
    and sec_unegsxuser.tagref = tags.tagref
    and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
    and legalbusinessunit.legalid = tags.legalid

    order by itemdescription, n_requisision
    "; */
    //mostrar para consolidar
    
    // SQL PARA MOSTRAR POR ARTICULOS
   /* $SQL="
    SELECT DISTINCT 
 purchorders.orderno AS orden_purch,
 purchorders.requisitionno AS n_requisision,
 purchorderdetails.itemcode,
 purchorderdetails.itemdescription,
 purchorderdetails.orderlineno_,
 purchorderdetails.quantityord AS cantidad_articulo,
tb_partida_articulo.partidaEspecifica as partida
FROM purchorderdetails
INNER JOIN purchorders ON purchorderdetails.orderno = purchorders.orderno
 INNER JOIN stockmaster ON stockmaster.stockid=purchorderdetails.itemcode
 INNER JOIN tags ON purchorders.tagref = tags.tagref 
 INNER JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
 INNER JOIN sec_unegsxuser ON purchorders.tagref=sec_unegsxuser.tagref  AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'

INNER JOIN tb_partida_articulo ON stockmaster.eq_stockid=tb_partida_articulo.eq_stockid
INNER JOIN (SELECT
 purchorderdetails.itemcode,
 purchorderdetails.itemdescription,
count(*) AS veces
 FROM purchorderdetails 
 INNER JOIN purchorders ON purchorderdetails.orderno = purchorders.orderno
 INNER JOIN stockmaster ON stockmaster.stockid=purchorderdetails.itemcode
 INNER JOIN tags ON purchorders.tagref = tags.tagref 
 INNER JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
 INNER JOIN sec_unegsxuser ON purchorders.tagref=sec_unegsxuser.tagref  AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
 WHERE (purchorderdetails.itemcode LIKE '%".$idarticulo."%' OR stockmaster.description LIKE '%".$idarticulo."%')
 AND purchorders.requisitionno!= 0 AND purchorders.requisitionno!= ''
 AND purchorderdetails.quantityord > 0
GROUP BY  purchorderdetails.itemcode,
 purchorderdetails.itemdescription
 HAVING count(*)>1) AS consolidables ON purchorderdetails.itemcode = consolidables.itemcode
WHERE (purchorderdetails.itemcode LIKE '%".$idarticulo."%' OR stockmaster.description LIKE '%".$idarticulo."%')
 AND purchorders.requisitionno!= 0 AND purchorders.requisitionno!= ''
AND purchorderdetails.quantityord > 0
and purchorders.status='Autorizado'
ORDER BY purchorderdetails.itemdescription, n_requisision
"; */ //FIN SQL PARA MOSTRAR POR ARTICULOS
   
   $SQL="SELECT DISTINCT 
     purchorders.orderno AS orden_purch,
     purchorders.requisitionno AS n_requisision,
     tb_partida_articulo.partidaEspecifica as partida,
     tb_partida_articulo.descPartidaEspecifica   as partida_descripcion,
     purchorderdetails.itemcode,
     purchorderdetails.itemdescription,
     purchorderdetails.orderlineno_,
     purchorderdetails.quantityord AS cantidad_articulo,
     purchorderdetails.unitprice AS precio,
     purchorderdetails.clavepresupuestal AS clavepre,
     tb_cat_partidaspresupuestales_partidaespecifica.descripcion as descPartida
     FROM purchorderdetails
     INNER JOIN purchorders ON purchorderdetails.orderno = purchorders.orderno
     INNER JOIN stockmaster ON stockmaster.stockid=purchorderdetails.itemcode
     INNER JOIN tags ON purchorders.tagref = tags.tagref 
     INNER JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
     INNER JOIN sec_unegsxuser ON purchorders.tagref=sec_unegsxuser.tagref  AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "' 
    INNER JOIN tb_partida_articulo ON stockmaster.eq_stockid=tb_partida_articulo.eq_stockid
    INNER JOIN tb_cat_partidaspresupuestales_partidaespecifica ON tb_partida_articulo.partidaEspecifica =tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada
    INNER JOIN (SELECT
     purchorderdetails.itemcode,
     purchorderdetails.itemdescription,
     count(*) AS veces
     FROM purchorderdetails 
     INNER JOIN purchorders ON purchorderdetails.orderno = purchorders.orderno
     INNER JOIN stockmaster ON stockmaster.stockid=purchorderdetails.itemcode
     INNER JOIN tags ON purchorders.tagref = tags.tagref 
     INNER JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
     INNER JOIN sec_unegsxuser ON purchorders.tagref=sec_unegsxuser.tagref  AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
     INNER JOIN tb_partida_articulo ON stockmaster.eq_stockid=tb_partida_articulo.eq_stockid
     WHERE (tb_partida_articulo.partidaEspecifica  LIKE '%".$idarticulo."%' OR tb_partida_articulo.descPartidaEspecifica  LIKE '%".$idarticulo."%' )
     AND purchorders.requisitionno!= 0 AND purchorders.requisitionno!= ''
     AND purchorderdetails.quantityord > 0
     GROUP BY  purchorderdetails.itemcode,
     purchorderdetails.itemdescription
     HAVING count(*)>1) AS consolidables ON purchorderdetails.itemcode = consolidables.itemcode
     WHERE (tb_partida_articulo.partidaEspecifica  LIKE '%".$idarticulo."%' OR tb_partida_articulo.descPartidaEspecifica  LIKE '%".$idarticulo."%' )
     AND purchorders.requisitionno!= 0 AND purchorders.requisitionno!= ''
     AND purchorderdetails.quantityord > 0
     AND purchorderdetails.status=2
     AND purchorders.status='Autorizado' 
     AND purchorderdetails.pedimento!='consolidada' 
     ORDER BY purchorderdetails.itemdescription, n_requisision";

// DEBE ir  el purchorderdetails.status=4

    $ErrMsg = "No hay datos";
    $sumaTotalArticulos=0;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $i=0;
    while ( $myrow = DB_fetch_array($TransResult) ) {
        // $sumaTotalArticulos+=$myrow ['cantidad_articulo'];
        // 'id'=>'<input type="checkbox" value="'.$myrow['itemcode'].'-'.$myrow['n_requisision'].'-'.$i.'"  name="n_requisision" class="n_requisision">',
        $info[] = array('id1'=>false,
        
        'id'=>$myrow['partida'].'@'.$myrow['n_requisision'].'@'.$i,
        'requi' => $myrow ['n_requisision'],
        'partida'=>  $myrow ['partida'],
        'descpartida'=>  $myrow ['descPartida'],       
        'cantidad' => $myrow ['cantidad_articulo'],
        'codigo' => $myrow ['itemcode'],
        'descripcion' => $myrow ['itemdescription'] ,
        'precio' => $myrow ['precio'] ,
        'clavepre' => $myrow ['clavepre'],
        'no'=> $myrow['orden_purch'] 
        
         
         );
        $i++;
    }
    $target=$myrow['tagref'];
    /*$contenido = array('datosRequisisionPorarticulo' => $info,'suma'=>$sumaTotalArticulos);
    $result = true; */
    $columnasNombres ='';
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'id1', type: 'bool'},";
    $columnasNombres .= "{ name: 'id', type: 'string' },";
    $columnasNombres .= "{ name: 'requi', type: 'string' },";
  
    $columnasNombres .= "{ name: 'cantidad', type: 'number' },";
    $columnasNombres .= "{ name: 'codigo', type: 'string' },";
    $columnasNombres .= "{ name: 'descripcion', type: 'string' },";
    $columnasNombres .= "{ name: 'partida', type: 'string' },";
    $columnasNombres .= "{ name: 'descpartida', type: 'string' },";
    $columnasNombres .= "{ name: 'precio', type: 'string' },";
    $columnasNombres .= "{ name: 'clavepre', type: 'string' },";
    $columnasNombres .= "{ name: 'no', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid='';
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text:'', datafield:'id1',columntype: 'checkbox', width: '5%',editable: true  },";
    $columnasNombresGrid .= " { text: '', datafield: 'id', width: '5%', cellsalign: 'center', align: 'center', cellsalign: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Requisicón', datafield: 'requi', width: '9%', cellsalign: 'center', align: 'center', hidden: false ,editable: true},";
    
    $columnasNombresGrid .= " { text: 'Cantidad', datafield: 'cantidad', width: '9%', cellsalign: 'center', align: 'center', cellsalign: 'right',hidden: false },";
    $columnasNombresGrid .= " { text: 'Código', datafield: 'codigo', width: '9%', cellsalign: 'center', align: 'center', cellsalign: 'center',hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción producto/servicio', datafield: 'descripcion', width: '30%', cellsalign: 'center', align: 'center',cellsalign: 'left', hidden: false },";
    $columnasNombresGrid .= " { text: 'Partida', datafield: 'partida', width: '9%', cellsalign: 'center', align: 'center',cellsalign: 'center',align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Partida descripción', datafield: 'descpartida', width: '30%', cellsalign: 'center', align: 'center',cellsalign: 'left', hidden: false },";
    $columnasNombresGrid .= " { text: '', datafield: 'precio', width: '4%', cellsalign: 'center', align: 'center',cellsalign: 'left', hidden: true },";
    $columnasNombresGrid .= " { text: '', datafield: 'clavepre', width: '4%', cellsalign: 'center', align: 'center',cellsalign: 'left', hidden: true },";
     $columnasNombresGrid .= " { text: '', datafield: 'no', width: '4%', cellsalign: 'center', align: 'center',cellsalign: 'left', hidden: true }";
    
    $columnasNombresGrid .= "]";

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid);
    $result = true;
       }else{

    $contenido ="Sin datos que mostrar.";
    $result = true;

       }
    break;


case 'consolidar':
    $requisiciones=$_POST['requisiciones'];
    $codigos=$_POST['codigos'];
    $valoresInsertar='';
    $valoresConso='';
    $cadena='';
    $update='';
    $leyenda='Requisicón Consolidada a partir de las requisiciones:';
    $requisitionno = GetNextTransNo(19, $db);
    $SQL1 = "INSERT INTO purchorders
        (
            supplierno,comments,rate,allowprint,initiator,requisitionno,intostocklocation,
            deladd1,deladd2,deladd3,deladd4,deladd5,deladd6,contact,version,realorderno,deliveryby,
            status,stat_comment,tagref,dateprinted,orddate,validfrom,validto,revised,deliverydate,lastUpdated,
            autorizafecha,fecha_modificacion,consignment,autorizausuario,capturausuario,solicitausuario,status_aurora,
            supplierorderno,currcode,wo,foliopurch,telephoneContact,refundpercentpurch,totalrefundpercentpurch,systypeorder,
            noag_ad,servicetype,clavepresupuestal,fileRequisicion,nu_ue
        ) VALUES"."
        ('111111','Requisicón Consolidada  ',1,1,'".$_SESSION ['UserID']."','".$requisitionno."',4,'dir1','dir2','dir3','dir4','dir5','dir6','','0.00','','','".$statusRequisicion."',
           concat(curdate(),' - Order  ". $statusRequisicion." ',curdate(),' - Creada: ". $_SESSION ['UserID']."'),
           'I6L','0000-00-00 00:00:00',
            current_timestamp(),
            current_timestamp(),
            current_timestamp(),'0000-00-00',
            current_timestamp(),
            current_timestamp(),'0000-00-00',
            current_timestamp(),'0',
            '".$_SESSION ['UserID']."','".$_SESSION ['UserID']."','".$_SESSION ['UserID']."','','','MXN',0,'".''."','',0,0,0,'',0,'',0,'09'
        )";

    $TransResult = DB_query($SQL1, $db, $ErrMsg);
    $orderno = DB_Last_Insert_ID($db, 'purchorders', 'orderno');

    //$codigos
    $SQL2='';
    //print_r($codigos);
    $j=1;
    for($a=0;$a<count($codigos);$a++){
   
    $datosRequisicion = explode("@", $codigos[$a]);
  
        // while ( $myrow = DB_fetch_array($TransResult) ) 
        // {
    $leyenda=$datosRequisicion[0].",";
    $valoresInsertar.="('".$orderno."','".$datosRequisicion[2]."',current_timestamp(),'".$datosRequisicion[3]."','1.1.5.1.1','0','".$datosRequisicion[4]."','100','100','".$datosRequisicion[1]."',0,0,0,0,'','',0,'',0,0,'',0,0,'100','1000',0,0,0,'','',0,current_timestamp(),0,0,'".($j)."',0,0,0,'','',0,'','','consolidada','0000-00-00','0000-00-00',current_timestamp(),'',1,1,'0','".$datosRequisicion[5]."','descLarga','','2'),";
    $valoresConso.="('".$requisitionno."','" .$datosRequisicion[0]."','".$datosRequisicion[1]."','".$datosRequisicion[2]."','".$datosRequisicion[5]."','".$_SESSION ['UserID']."'),";
  

   
    $update.= "UPDATE purchorderdetails SET status='4' ";
    $update .= " WHERE ";
    $update .= " orderno='" . $datosRequisicion[6] . "' AND itemcode= '" . $datosRequisicion[2] . "'; ";  
       
      // }  //fin while
      DB_query($update, $db, $ErrMsg);
      $update='';
        $j++;
    }
    // renglones  de la nueva requi(consolidacion)
    $valoresInsertar=substr($valoresInsertar, 0, -1);
    $SQL2="INSERT INTO purchorderdetails (
    orderno, itemcode,deliverydate,itemdescription,glcode,qtyinvoiced,unitprice,actprice,stdcostunit,quantityord,quantityrecd,
    shiptref,jobref,completed,itemno,uom,subtotal_amount,package,pcunit,nw,suppliers_partno,gw,cuft,total_quantity,total_amount,
    discountpercent1,discountpercent2,discountpercent3,narrative,justification,refundpercent,lastUpdated,totalrefundpercent,
    estimated_cost,orderlineno_,saleorderno_,wo,qtywo,womasterid,wocomponent,idgroup,typegroup,customs,pedimento,
    dateship,datecustoms,fecha_modificacion,inputport,factorconversion,invoice_rate,flagautoemision,clavepresupuestal, sn_descripcion_larga, renglon, status
    ) 
    VALUES ".$valoresInsertar;
    $TransResult = DB_query($SQL2, $db, $ErrMsg);


    $valoresConso=substr($valoresConso, 0, -1);
    $SQL3="INSERT INTO tb_consolidaciones  (nu_requi_consolidada,nu_requi_origen,nu_cantidad,ln_codigo,ln_clave_pre,ln_usuario ) VALUES ".$valoresConso;
    $TransResult = DB_query($SQL3, $db, $ErrMsg);

    $SQL4="UPDATE purchorders  SET comments='".$leyenda."' where requisitionno='".$requisitionno."'";
    $TransResult = DB_query($SQL4, $db, $ErrMsg);


    //print_r($update);
    //$TransResult = DB_query($update, $db, $ErrMsg);


    $contenido ='Se creo consolidación con exito y con número de requisición:<b>'.$requisitionno.'</b>';//array('datosConsolidados' => $info);
    $result = true;

    break;

    case 'consolidacionesHechas':


    $anioActual=date('Y');
    $mesActual=date('m');
    $dateDesde='';
    $dateHasta='';
    $partida=$_POST['partida'];
    if(!empty($_POST['dateDesde'])) {
        $dateDesde= date("Y-m-d", strtotime($_POST['dateDesde']));
    }
    else{
        $dateDesde=$anioActual.'-'.$mesActual.'-'.'01';
    }
    
    if(!empty($_POST['dateHasta'])) {
        $dateHasta= date("Y-m-d", strtotime($_POST['dateHasta']));
    }
    else{
        $dateHasta=$anioActual.'-'.$mesActual.'-'.'31';
    }
    $SQL="SELECT * FROM  tb_consolidaciones WHERE tb_consolidaciones.deliverydate  between  STR_TO_DATE('" . $dateDesde . "', '%Y-%m-%d')
    AND STR_TO_DATE('" . $dateHasta . "', '%Y-%m-%d')";

    $ErrMsg = "No hay datos";
    $sumaTotalArticulos=0;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $i=0;
    while ( $myrow = DB_fetch_array($TransResult) ) 
    {
       
        $info[] = array(
        'requi' => $myrow ['requisitionno'],
        'coment'=>  $myrow ['comments'],
        'user'=>  $myrow ['initiator'],       
         );
        $i++;
    }


    $columnasNombres ='';
    $columnasNombres .= "[";

    $columnasNombres .= "{ name: 'requi', type: 'number' },";
    $columnasNombres .= "{ name: 'coment', type: 'string' },";
  
    $columnasNombres .= "{ name: 'user', type: 'number' },";

    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid='';
    $columnasNombresGrid .= "[";
  
   
    $columnasNombresGrid .= " { text: 'Requisicón', datafield: 'requi', width: '10%', cellsalign: 'right', hidden: false ,editable: true},";
    
    $columnasNombresGrid .= " { text: 'Comentarios', datafield: 'coment', width: '80%', cellsalign: 'center', align: 'center', cellsalign: 'left',hidden: false },";
    $columnasNombresGrid .= " { text: 'Usuario', datafield: 'user', width: '10%', cellsalign: 'center', align: 'center', cellsalign: 'center',hidden: false },";
    
    $columnasNombresGrid .= "]";

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid);
    $result = true;



    break;

    case'requisConsolidar':
    if(isset($_POST['datos'])){
     $info=array();
     $requis='';
     $datos =$_POST['datos'];

         if(count($datos)>0){

             foreach ($datos as $ad) {
                 $requis.="'".$ad."',";
             }
             $requis=  substr($requis, 0, -1);
             $SQL="   SELECT 
             purchorders.requisitionno as n_requisision,
             purchorderdetails.itemcode,
             purchorderdetails.itemdescription,
             purchorderdetails.quantityord as cantidad_articulo,
             purchorderdetails.unitprice as precio,  
             tb_partida_articulo.partidaEspecifica as partida,
             tb_cat_partidaspresupuestales_partidaespecifica.descripcion as descPartida,
             purchorderdetails.clavepresupuestal AS clavepre,
             purchorderdetails.orderlineno_,
             purchorders.orderno as orden_purch
             FROM purchorderdetails  
             INNER JOIN purchorders ON purchorderdetails.orderno = purchorders.orderno
             INNER JOIN stockmaster ON stockmaster.stockid=purchorderdetails.itemcode
             INNER JOIN tb_partida_articulo ON stockmaster.eq_stockid=tb_partida_articulo.eq_stockid
             INNER JOIN tb_cat_partidaspresupuestales_partidaespecifica ON tb_partida_articulo.partidaEspecifica =tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada
             WHERE purchorders.requisitionno IN(".$requis.")
             AND purchorderdetails.itemcode!='no_data'
             AND purchorderdetails.status='2';";

             $TransResult = DB_query($SQL, $db, $ErrMsg);
              while ( $myrow = DB_fetch_array($TransResult) ) {
              
                $info[] = array('id1'=>false,
                
                'id'=>$myrow['partida'].'@'.$myrow['n_requisision'].'@'.$i,
                'requi' => $myrow ['n_requisision'],
                'partida'=>  $myrow ['partida'],
                'descpartida'=>  $myrow ['descPartida'],       
                'cantidad' => $myrow ['cantidad_articulo'],
                'codigo' => $myrow ['itemcode'],
                'descripcion' => $myrow ['itemdescription'] ,
                'precio' => $myrow ['precio'] ,
                'clavepre' => $myrow ['clavepre'],
                'no'=> $myrow['orden_purch'] 
                
                 
                 );
                 $i++;
            }

            $columnasNombres ='';
            $columnasNombres .= "[";
            $columnasNombres .= "{ name: 'id1', type: 'bool'},";
            $columnasNombres .= "{ name: 'id', type: 'string' },";
            $columnasNombres .= "{ name: 'requi', type: 'string' },";
            
            $columnasNombres .= "{ name: 'partida', type: 'string' },";
            $columnasNombres .= "{ name: 'descpartida', type: 'string' },";
            $columnasNombres .= "{ name: 'codigo', type: 'string' },";
            $columnasNombres .= "{ name: 'descripcion', type: 'string' },";
            $columnasNombres .= "{ name: 'cantidad', type: 'number' },";
    
            $columnasNombres .= "{ name: 'precio', type: 'string' },";
            $columnasNombres .= "{ name: 'clavepre', type: 'string' },";
            $columnasNombres .= "{ name: 'no', type: 'string' }";
            $columnasNombres .= "]";
            // Columnas para el GRID
            $columnasNombresGrid='';
            $columnasNombresGrid .= "[";
            $columnasNombresGrid .= " { text:'', datafield:'id1',columntype: 'checkbox', width: '5%',editable: true  },";
            $columnasNombresGrid .= " { text: '', datafield: 'id', width: '5%', cellsalign: 'center', align: 'center', cellsalign: 'center', hidden: true },";
            $columnasNombresGrid .= " { text: 'Requisicón', datafield: 'requi', width: '9%', cellsalign: 'center', align: 'center', hidden: false ,editable: true},";

             $columnasNombresGrid .= " { text: 'Partida', datafield: 'partida', width: '9%', cellsalign: 'center', align: 'center',cellsalign: 'center',align: 'center', hidden: false },";
            $columnasNombresGrid .= " { text: 'Partida descripción', datafield: 'descpartida', width: '30%', cellsalign: 'center', align: 'center',cellsalign: 'left', hidden: false },";
            $columnasNombresGrid .= " { text: 'Código', datafield: 'codigo', width: '9%', cellsalign: 'center', align: 'center', cellsalign: 'center',hidden: false },";
            $columnasNombresGrid .= " { text: 'Descripción producto/servicio', datafield: 'descripcion', width: '30%', cellsalign: 'center', align: 'center',cellsalign: 'left', hidden: false },";
           
            $columnasNombresGrid .= " { text: 'Cantidad', datafield: 'cantidad', width: '9%', cellsalign: 'center', align: 'center', cellsalign: 'right',hidden: false },";
            
            $columnasNombresGrid .= " { text: '', datafield: 'precio', width: '4%', cellsalign: 'center', align: 'center',cellsalign: 'left', hidden: true },";
            $columnasNombresGrid .= " { text: '', datafield: 'clavepre', width: '4%', cellsalign: 'center', align: 'center',cellsalign: 'left', hidden: true },";
             $columnasNombresGrid .= " { text: '', datafield: 'no', width: '4%', cellsalign: 'center', align: 'center',cellsalign: 'left', hidden: true }";
            
            $columnasNombresGrid .= "]";

            $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid);
            $result = true;
        }
    }else{


    }
    break;
    
default:
    // code...
    break;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);

?>