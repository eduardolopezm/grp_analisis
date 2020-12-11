<?php
/**
 * Modelo para REPORTE DE PAGOS A PROVEEDORES
 *
 * @category     ABC
 * @package      ap_grp
 * @author       Arturo Lopez Peña <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 10/08/2017
 * Fecha Modificación: 11/08/2017
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
    //$SQL="SELECT  stockid, description FROM stockmaster WHERE `description` like '%".$articulo."%' ORDER BY description";

     $SQL=" select 
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
";

    $ErrMsg = "No hay datos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $datos.='<ul id="articulos-lista-consolida">';
    while ( $myrow = DB_fetch_array($TransResult) ) 
    {

       // $dias[] =$myrow['dia'];
       $datos.='<li onClick="fnSelectArticulo(\''.$myrow['description'].'\')">'.$myrow['description'].'</li>';
       
       
    }
    $target=$myrow['tagref'];
    $datos.='</ul>';
    $contenido = $datos;
    $result = true;

		break;

    case 'buscarArticuloEnRequisisiones':
    $idarticulo=$_POST['idArticulo'];
    //principal
      /*  $SQL="
    select
 purchorders.`requisitionno`  as n_requisision,
 purchorderdetails.`quantityord` as cantidad_articulo, 
 purchorderdetails.`itemcode`,
 purchorderdetails.`itemdescription`,
 purchorderdetails.`orderno` as orden_detalle,
 purchorders.`orderno`as orden_purch
 from purchorderdetails 
 inner join purchorders on   purchorderdetails.`orderno` = purchorders.`orderno`
 inner join stockmaster on stockmaster.`stockid`=purchorderdetails.`itemcode` ,tags,legalbusinessunit,sec_unegsxuser
 where (purchorderdetails.`itemcode` LIKE '%".$idarticulo."%' or stockmaster.description LIKE '%".$idarticulo."%')
 and purchorders.tagref = tags.tagref
 and sec_unegsxuser.tagref = tags.tagref
 and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
 and legalbusinessunit.legalid = tags.legalid

 order by itemdescription, n_requisision
"; */
//mostrar para consolidar
      $SQL="
    SELECT DISTINCT 
 purchorders.orderno AS orden_purch,
 purchorders.requisitionno AS n_requisision,
 purchorderdetails.itemcode,
 purchorderdetails.itemdescription,
 purchorderdetails.orderlineno_,
 purchorderdetails.quantityord AS cantidad_articulo
FROM purchorderdetails
INNER JOIN purchorders ON purchorderdetails.`orderno` = purchorders.`orderno`
 INNER JOIN stockmaster ON stockmaster.`stockid`=purchorderdetails.`itemcode`
 INNER JOIN tags ON purchorders.tagref = tags.tagref 
 INNER JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
 INNER JOIN sec_unegsxuser ON purchorders.tagref=sec_unegsxuser.tagref  AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
INNER JOIN (SELECT
 purchorderdetails.itemcode,
 purchorderdetails.itemdescription,
count(*) AS veces
 FROM purchorderdetails 
 INNER JOIN purchorders ON purchorderdetails.`orderno` = purchorders.`orderno`
 INNER JOIN stockmaster ON stockmaster.`stockid`=purchorderdetails.`itemcode`
 INNER JOIN tags ON purchorders.tagref = tags.tagref 
 INNER JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
 INNER JOIN sec_unegsxuser ON purchorders.tagref=sec_unegsxuser.tagref  AND sec_unegsxuser.userid = 'desarrollo'
 WHERE (purchorderdetails.`itemcode` LIKE '%".$idarticulo."%' OR stockmaster.description LIKE '%".$idarticulo."%')
 AND purchorders.requisitionno!= 0 AND purchorders.requisitionno!= ''
 AND purchorderdetails.quantityord > 0
GROUP BY  purchorderdetails.itemcode,
 purchorderdetails.itemdescription
 HAVING count(*)>1) AS consolidables ON purchorderdetails.itemcode = consolidables.itemcode
WHERE (purchorderdetails.`itemcode` LIKE '%".$idarticulo."%' OR stockmaster.description LIKE '%".$idarticulo."%')
 AND purchorders.requisitionno!= 0 AND purchorders.requisitionno!= ''
AND purchorderdetails.quantityord > 0
ORDER BY purchorderdetails.itemdescription, n_requisision
";
    

    $ErrMsg = "No hay datos";
     $sumaTotalArticulos=0;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $i=0;
    while ( $myrow = DB_fetch_array($TransResult) ) 
    {
       // $sumaTotalArticulos+=$myrow ['cantidad_articulo'];
       // 'id'=>'<input type="checkbox" value="'.$myrow['itemcode'].'-'.$myrow['n_requisision'].'-'.$i.'"  name="n_requisision" class="n_requisision">',
       $info[] = array('id1'=>false,
        'id'=>$myrow['itemcode'].'-'.$myrow['n_requisision'].'-'.$i,
        'requi' => $myrow ['n_requisision'],
        'cantidad' => $myrow ['cantidad_articulo'],
        'codigo' => $myrow ['itemcode'],
        'descripcion' => $myrow ['itemdescription'] , 
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
    $columnasNombres .= "{ name: 'descripcion', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid='';
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text:'', datafield:'id1',editable: true,columntype: 'checkbox', width: '5%' ,editoptions:{value:'true:false'}},";
    $columnasNombresGrid .= " { text: '', datafield: 'id', width: '5%', cellsalign: 'center', align: 'center', cellsalign: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Requisicón', datafield: 'requi', width: '10%', cellsalign: 'right', hidden: false ,editable: true},";
    $columnasNombresGrid .= " { text: 'Cantidad', datafield: 'cantidad', width: '10%', cellsalign: 'center', align: 'center', cellsalign: 'right',hidden: false },";
    $columnasNombresGrid .= " { text: 'Código', datafield: 'codigo', width: '10%', cellsalign: 'center', align: 'center', cellsalign: 'center',hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'descripcion', width: '65%', cellsalign: 'center', align: 'center',cellsalign: 'left', hidden: false }";
    $columnasNombresGrid .= "]";

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid);
    $result = true;

    break;


    case 'consolidar':
     $requisiciones=$_POST['requisiciones'];
     $cadena='';

       $requisitionno = GetNextTransNo(19, $db);
  $SQL = "INSERT INTO purchorders 
        (
            supplierno,comments,rate,allowprint,initiator,requisitionno,intostocklocation,
            deladd1,deladd2,deladd3,deladd4,deladd5,deladd6,contact,version,realorderno,deliveryby,
            status,stat_comment,tagref,dateprinted,orddate,validfrom,validto,revised,deliverydate,lastUpdated,
            autorizafecha,fecha_modificacion,consignment,autorizausuario,capturausuario,solicitausuario,status_aurora,
            supplierorderno,currcode,wo,foliopurch,telephoneContact,refundpercentpurch,totalrefundpercentpurch,systypeorder,
            noag_ad,servicetype,clavepresupuestal,fileRequisicion
        ) VALUES"."
        ('111111','consolidada',1,1,'".$_SESSION ['UserID']."','".$requisitionno."',4,'dir1','dir2','dir3','dir4','dir5','dir6','','0.00','','','".$statusRequisicion."',
           concat(curdate(),' - Order  ". $statusRequisicion." ',curdate(),' - Creada: ". $_SESSION ['UserID']."'),
           '100','0000-00-00 00:00:00',
            current_timestamp(),
            current_timestamp(),
            current_timestamp(),'0000-00-00',
            current_timestamp(),
            current_timestamp(),'0000-00-00',
            current_timestamp(),'0',
            '".$_SESSION ['UserID']."','".$_SESSION ['UserID']."','".$_SESSION ['UserID']."','','','MXN',0,'".$nuevaRequisicion."','',0,0,0,'',0,'',0
        )";

          $TransResult = DB_query($SQL, $db, $ErrMsg);
           $nuevaRequisicion = DB_Last_Insert_ID($db, 'purchorders', 'orderno');


     for($a=0;$a<count($requisiciones);$a++){

          $datosRequisicion = explode("-", $requisiciones[$a]);

          $SQL="

SELECT
    purchorders.supplierno,
    tags.tagref,
    purchorderdetails.`itemcode`,
    purchorderdetails.`itemdescription`,
    purchorderdetails.`unitprice`,
    
  sum(purchorderdetails.`quantityord`) AS cantidad_totales
    FROM purchorderdetails 
    INNER JOIN purchorders ON purchorderdetails.`orderno` = purchorders.`orderno`
    INNER JOIN tags on  purchorders.`tagref`=tags.tagref
    WHERE purchorderdetails.`itemcode`='".$datosRequisicion[0]."' 
    AND purchorders.`requisitionno` IN (".$datosRequisicion[1].") 
    GROUP BY purchorderdetails.`itemcode`
          ";
   
/*$SQL="select
    sum(purchorderdetails.`quantityord`) as cantidad_totales,
    purchorderdetails.`itemcode`,
    purchorderdetails.`itemdescription`,
    purchorderdetails.`quantityord`
    from purchorderdetails 
    inner join purchorders on   purchorderdetails.`orderno` = purchorders.`orderno`
    inner join stockmaster on stockmaster.`stockid`=purchorderdetails.`itemcode` ,tags,legalbusinessunit,sec_unegsxuser
    where purchorderdetails.`itemcode`='".$datosRequisicion[0]."' 
    and purchorders.`requisitionno` in (".$datosRequisicion[1].") 
  
    and purchorders.tagref = tags.tagref
    and sec_unegsxuser.tagref = tags.tagref
  
    and sec_unegsxuser.userid = 'desarrollo'
    and legalbusinessunit.legalid = tags.legalid
  
    group by purchorderdetails.`itemcode` 
 
    having count(*)>0
"; */
      /*  $SQL="
    select
    sum(purchorderdetails.`quantityord`) as cantidad_totales,
    purchorders.`requisitionno`  as n_requisision,
    purchorderdetails.`itemcode`,
    purchorderdetails.`itemdescription`,
    purchorderdetails.`quantityord`
    from purchorderdetails 
    inner join purchorders on   purchorderdetails.`orderno` = purchorders.`orderno`
    inner join stockmaster on stockmaster.`stockid`=purchorderdetails.`itemcode` ,tags,legalbusinessunit,sec_unegsxuser
    where purchorders.`requisitionno` in (".$requisiciones.")
    and purchorders.tagref = tags.tagref
    and sec_unegsxuser.tagref = tags.tagref
    and sec_unegsxuser.userid = 'desarrollo'
    and legalbusinessunit.legalid = tags.legalid
    group by purchorderdetails.`itemcode`
    having count(*)>0
"; */


    $ErrMsg = "No hay datos";
     $sumaTotalArticulos=0;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ( $myrow = DB_fetch_array($TransResult) ) 
    {
        
       // $sumaTotalArticulos+=$myrow ['cantidad_articulo'];
       /*$info[] = array('id,9%,,'=>'<input type="hidden" value="'.$myrow['itemcode'].'">',
        'cantidades,20%,Cantidades Totales,' => $myrow ['cantidad_totales'],'itemcode,20%,Código artículo,' => $myrow ['itemcode'], 'descripcion,39%,Descripción,' => $myrow ['itemdescription']  ); */
      


          $SQL="INSERT INTO purchorderdetails (
    orderno, itemcode,deliverydate,itemdescription,glcode,qtyinvoiced,unitprice,actprice,stdcostunit,quantityord,quantityrecd,
    shiptref,jobref,completed,itemno,uom,subtotal_amount,package,pcunit,nw,suppliers_partno,gw,cuft,total_quantity,total_amount,
    discountpercent1,discountpercent2,discountpercent3,narrative,justification,refundpercent,lastUpdated,totalrefundpercent,
    estimated_cost,orderlineno_,saleorderno_,wo,qtywo,womasterid,wocomponent,idgroup,typegroup,customs,pedimento,
    dateship,datecustoms,fecha_modificacion,inputport,factorconversion,invoice_rate,flagautoemision,clavepresupuestal, sn_descripcion_larga, renglon, status
) 
VALUES 
(
    '".$nuevaRequisicion."','".$myrow ['itemcode']."',current_timestamp(),'".$myrow ['itemdescription']."','1.1.5.1.1','0','".$myrow ['unitprice']."','100','100','".$myrow ['cantidad_totales']."',0,0,0,0,'','',0,'',0,0,'',0,0,'100','1000',0,0,0,'','',0,current_timestamp(),0,0,'ordenElemento',0,0,0,'','',0,'','','','0000-00-00','0000-00-00',current_timestamp(),'',1,1,'0','$cvePresupuestal','descLarga','','2'
)";
  $TransResult = DB_query($SQL, $db, $ErrMsg);
       
    }
}
    $contenido ='Consolidación  exitosa';//array('datosConsolidados' => $info);
    $result = true;

    break;
	
	default:
		# code...
		break;
}

$dataObj = array('sql' => $SQL, 'contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);

?>