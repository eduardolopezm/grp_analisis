<?php
/**
 * Modelo para el ABC de Finalidad
 *
 * @category ABC
 * @package ap_grp
 * @author Luis Aguilar Sandoval <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Se realizan operación pero el Alta, Baja y Modificación. Mediante las validaciones creadas para la operación seleccionada
 */

 //ini_set('display_errors', 1);
 //ini_set('log_errors', 1);
 //error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

session_start();
$PageSecurity = 4;
$PathPrefix = '../';

include($PathPrefix. "includes/SecurityUrl.php");
include($PathPrefix.'abajo.php');
include($PathPrefix.'config.php');
include($PathPrefix.'includes/ConnectDB.inc');

if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion = 2265;
include($PathPrefix.'includes/SecurityFunctionsHeader.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');
include($PathPrefix .'includes/DateFunctions.inc');

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

$option = $_POST['option'];
$proveedor = '111111';
$cvePresupuestal = 'ClavePresupuestal';
$usuarioIniciador = $_SESSION['UserID'];
$usuarioAutorizador = 'Autorizador';
$statusRequisicion = 'Creada';
$periodo = GetPeriod(date('d/m/Y'), $db);
$type = 19;

if($option == 'getFechaServidor'){
    $info[] = array('fechaDMY'=>date("d-m-Y"),'fechaMdy'=>date("m-d-Y"));
    $contenido = array('Fecha'=> $info);
    $result = true;
}

if($option == 'getFechaServidorSiguiente'){
    $ndias=$_POST['numerodias'];
    $fecha= fnFeriadoOfin($ndias,$db);
    $info[] = array('fechaDMY'=>$fecha);
    $contenido = array('Fecha'=> $info);
    $result = true;
    
}

if($option == 'generarUrlEnc'){
    $idRequisicion = $_POST['idRequisicion'];
    $noRequisicion = $_POST['noRequisicion'];
    $enc = new Encryption;
    $url = "&ModifyOrderNumber=>" . $idRequisicion . "&idrequisicion=> ". $noRequisicion;
    $url = $enc->encode($url);
    $liga= "URL=" . $url;
    
    $contenido = $liga;
    $result = true;

}

if ($option == 'loadRequisicion') {
    $req = $_POST['req'];
    $info = array();
    $SQL="SELECT CONCAT(legalbusinessunit.legalid,' - ',legalbusinessunit.legalname) as rs, 
                CONCAT(tags.tagref,' - ',tags.tagdescription) as ue,  
                CONCAT(tb_cat_unidades_ejecutoras.ue,' - ',tb_cat_unidades_ejecutoras.desc_ue) as unidadEjecutora,  
                purchorders.orderno as idr, purchorders.requisitionno as noReq,
                purchorders.validfrom as fechaCreacion, purchorders.deliverydate as fechadelivery, 
                purchorders.comments as comments, purchorders.nu_anexo_tecnico as anexoTec
            FROM purchorders 
            JOIN tb_cat_unidades_ejecutoras on (purchorders.nu_ue = tb_cat_unidades_ejecutoras.ue and purchorders.tagref = tb_cat_unidades_ejecutoras.ur)
            JOIN tags on (purchorders.tagref = tags.tagref) 
            JOIN legalbusinessunit on (legalbusinessunit.legalid = tags.legalid )
            WHERE purchorders.orderno = '$req'";

    $ErrMsg = "No se encontro la Requisición: ".$req;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'rs' => $myrow ['rs'],
            'ue' => $myrow ['ue'],
            'fechaCreacion' => $myrow ['fechaCreacion'],
            'fechadelivery' => $myrow ['fechadelivery'],
            'comments' => $myrow ['comments'],
            'idr' => $myrow ['idr'],
            'noReq' => $myrow ['noReq'],
            'unidadEjecutora' => $myrow ['unidadEjecutora'],
            'anexoTec' => $myrow ['anexoTec']);
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'obtenerPresupuesto') {
    $clave = $_POST['clave'];

    $period = GetPeriod(date('d/m/Y'), $db);

    $nombreMes = "";
    $SQL = "SELECT periods.periodno, periods.lastdate_in_period, cat_Months.mes as mesName
            FROM periods 
            LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
            WHERE periods.lastdate_in_period like '%".$myrow ['anho']."%' AND periods.periodno = '".$period."'
            ORDER BY periods.lastdate_in_period asc";

    $resultPeriods = DB_query($SQL, $db, $ErrMsg);
    while ($rowPeriods = DB_fetch_array($resultPeriods)) {
        $nombreMes = $rowPeriods ['mesName'];
    }

    $res = true;

    $info = fnInfoPresupuesto($db, $clave, $period);

    if (empty($info)) {
        $Mensaje = "No se encontró la información para la Clave Presupuestal ".$clave;
        $res = false;
    }

    $contenido = array('datos' => $info, 'nombreMes' => $nombreMes);
    $result = $res;
}

if($option == 'mostrarPartidaINstrumentales'){

}

if ($option == 'mostrarPartidaCvePpto') {
    $cvePA = $_POST['dato'];
    $tagref = $_POST['datotagref'];
    $ue = $_POST['datoue'];
    $info = array();
   
    $SQL= "SELECT DISTINCT accountcode as cvefrom FROM chartdetailsbudgetbytag WHERE chartdetailsbudgetbytag.tagref='$tagref' AND chartdetailsbudgetbytag.partida_esp= '$cvePA' AND substring(chartdetailsbudgetbytag.ln_aux1,4,2) = '$ue' ";
    $ErrMsg = "No se obtuvo el Articulo";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'cvePresupuestal' => $myrow['cvefrom']
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
}
if($option == 'mostrarCppt'){
    $cvePA = $_POST['dato'];
    $tagref = $_POST['datotagref'];
    $ue = $_POST['datoue'];
    $cvePre = $_POST['clavepresupuestal'];
    $info = array();
   
    $SQL = "SELECT DISTINCT accountcode as cvefrom, tagref, cppt, ln_aux1,substring(ln_aux1,4,2) as ue, concat(tagref,'-',substring(ln_aux1,4,2),'-',cppt) as diferenciador FROM chartdetailsbudgetbytag WHERE chartdetailsbudgetbytag.tagref='$tagref' AND chartdetailsbudgetbytag.partida_esp= '$cvePA' AND accountcode = '".$cvePre."' AND substring(chartdetailsbudgetbytag.ln_aux1,4,2) = '$ue'  ";
    $ErrMsg = "No se obtuvo el Articulo";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'cvePresupuestal' => $myrow['cvefrom'],  
            'tagref' => $myrow['tagref'],
            'cppt' => $myrow['cppt'],
            'ue' => $myrow['ue'],
            'diferenciador' => $myrow['diferenciador']
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarPartidaCvePptoProdct') {
    $datoTagref = $_POST['datoTagref'];
    $datoCvePartida = $_POST['datoCvePartida'];
    $info = array();
    
    $SQL= "SELECT stockmaster.stockid AS idProducto, 
            stockmaster.description AS descripcionProducto, 
            stockmaster.units AS unidad, IFNULL(ultimo_costo.lastcost, 0) AS precioEstimado, 
            tb_partida_articulo.partidaEspecifica AS idPartidaEspecifica,
            tb_cat_partidaspresupuestales_partidaespecifica.descripcion AS descPartidaEspecifica,
            SUM(existencia.quantity) AS existencia
            FROM stockmaster
            INNER JOIN tb_partida_articulo on (stockmaster.eq_stockid = tb_partida_articulo.eq_stockid )
            LEFT JOIN  tb_cat_partidaspresupuestales_partidaespecifica on (tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = tb_partida_articulo.partidaEspecifica)
            LEFT JOIN (SELECT stockcostsxlegal.stockid, stockcostsxlegal.lastcost
                        FROM sec_loccxusser 
                        INNER JOIN locations USING (loccode)
                        INNER JOIN tags ON locations.tagref= tags.tagref
                        INNER JOIN stockcostsxlegal ON tags.legalid= stockcostsxlegal.legalid
                        WHERE sec_loccxusser.userid='".$_SESSION['UserID']."') ultimo_costo ON stockmaster.stockid= ultimo_costo.stockid        
            LEFT JOIN (SELECT locstock.loccode, locstock.stockid, locstock.quantity
                        FROM locstock
                        INNER JOIN sec_loccxusser USING (loccode)
                        WHERE sec_loccxusser.userid= '".$_SESSION['UserID']."') existencia ON stockmaster.stockid= existencia.stockid
            WHERE tb_partida_articulo.partidaEspecifica = '$datoCvePartida' AND stockmaster.discontinued = 0
            GROUP BY stockmaster.stockid, 
                        stockmaster.description , 
                        stockmaster.units, IFNULL(ultimo_costo.lastcost, 0), 
                        tb_partida_articulo.partidaEspecifica,
                        tb_cat_partidaspresupuestales_partidaespecifica.descripcion
            ORDER BY stockmaster.stockid";

    $ErrMsg = "NO ay articulo asignado";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'idProducto' => $myrow ['idProducto'],
            'descripcionProducto' => $myrow ['descripcionProducto'],
            'unidad' => $myrow ['unidad'],
            'precioEstimado' => $myrow ['precioEstimado'],
            'idPartidaEspecifica' => $myrow ['idPartidaEspecifica'],
            'descPartidaEspecifica' => $myrow ['descPartidaEspecifica'],
            'existencia' => $myrow ['existencia'] );
    }

    $contenido = array('datos' => $info);
    $result = true;

}

if ($option == 'mostrarCveArticuloDatos') {
    $cveArt = $_POST['datocveart'];
    $descArt = $_POST['datodescart'];
    $tagref = $_POST['datotagref'];
    $cvepresupuestalArt = $_POST['cvepresupuestalArt'];
    $ordenacion= "idProducto";
    $info = array();

    $SQL= "SELECT stockmaster.stockid AS idProducto, 
            stockmaster.description AS descripcionProducto, 
            stockmaster.units AS unidad,
            IFNULL(ultimo_costo.lastcost, 0) AS precioEstimado, 
            tb_partida_articulo.partidaEspecifica AS idPartidaEspecifica,
            tb_partida_articulo.descPartidaEspecifica AS descPartidaEspecifica, 
            existencia.quantity AS existencia
            FROM stockmaster 
            JOIN tb_partida_articulo ON (stockmaster.eq_stockid =  tb_partida_articulo.eq_stockid)
            LEFT JOIN (SELECT stockcostsxlegal.stockid, stockcostsxlegal.lastcost
            FROM sec_loccxusser 
            INNER JOIN locations USING (loccode)
            INNER JOIN tags ON locations.tagref= tags.tagref
            INNER JOIN stockcostsxlegal ON tags.legalid= stockcostsxlegal.legalid
            WHERE sec_loccxusser.userid='".$_SESSION['UserID']."') ultimo_costo ON stockmaster.stockid= ultimo_costo.stockid
            LEFT JOIN (SELECT locstock.loccode, locstock.stockid, locstock.quantity
            FROM locstock
            INNER JOIN sec_loccxusser USING (loccode)
            WHERE sec_loccxusser.userid= '".$_SESSION['UserID']."') existencia ON stockmaster.stockid= existencia.stockid
            WHERE (stockmaster.stockid= '".$cveArt."' OR stockmaster.description = '".$descArt."') AND stockmaster.discontinued = 0 ORDER BY descripcionProducto";

    $ErrMsg = "No se obtuvo el Articulo";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'idProducto' => $myrow ['idProducto'],
            'descripcionProducto' => $myrow ['descripcionProducto'],
            'unidad' => $myrow ['unidad'],
            'precioEstimado' => $myrow ['precioEstimado'],
            'idPartidaEspecifica' => $myrow ['idPartidaEspecifica'],
            'descPartidaEspecifica' => $myrow ['descPartidaEspecifica'],
            'existencia' => $myrow ['existencia'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarPartidaCveServicio') {
    $cvePA = $_POST['datoserv'];
    $tagref = $_POST['datotagref'];
    $info = array();

    $SQL= "SELECT DISTINCT tb_partida_articulo.partidaEspecifica AS idPartidaEspecifica,
            tb_partida_articulo.descPartidaEspecifica AS descPartidaEspecifica,
            stockmaster.stockid AS idServicio,
            stockmaster.description AS descripcionServicio,
            IF(stockcostsxlegal.lastcost >= 0 ,stockcostsxlegal.lastcost,'0')  AS precioEstimado
            FROM tb_partida_articulo
            INNER JOIN stockmaster ON tb_partida_articulo.eq_stockid= stockmaster.eq_stockid
            LEFT JOIN stockcostsxlegal ON stockmaster.stockid =  stockcostsxlegal.stockid AND stockcostsxlegal.legalid IN(SELECT legalid FROM tags WHERE tagref='".$tagref."')
            WHERE  tb_partida_articulo.partidaEspecifica = '".$cvePA."' AND stockmaster.discontinued = 0
            AND stockmaster.mbflag = 'D' LIMIT 1";

    $ErrMsg = "No se obtuvo el Servicio";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'idServicio' => $myrow ['idServicio'],
            'descripcionServicio' => $myrow ['descripcionServicio'],
            'precioEstimado' => $myrow ['precioEstimado'],
            'idPartidaEspecifica' => $myrow ['idPartidaEspecifica'],
            'descPartidaEspecifica' => $myrow ['descPartidaEspecifica'],
            'cantidad' => 1,
            'cvePresupuestal' => '');
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarCveServicioDatos') {
    $cveServ = $_POST['datocveserv'];
    $descServ = $_POST['datodescserv'];
    $tagref = $_POST['datotagref'];
    $info = array();
    $SQL="SELECT stockmaster.stockid as idServicio, 
            stockmaster.description as descripcionServicio, 
            stockmaster.mbflag as tipo,
            tb_partida_articulo.partidaEspecifica as idPartidaEspecifica, 
            tb_partida_articulo.descPartidaEspecifica as descPartidaEspecifica, 1 as cantidad,
            IF(stockcostsxlegal.lastcost >= 0 ,stockcostsxlegal.lastcost,'0')  as precioEstimado 
        FROM stockmaster 
        JOIN tb_partida_articulo ON (stockmaster.eq_stockid =  tb_partida_articulo.eq_stockid)
        LEFT JOIN stockcostsxlegal ON (stockmaster.stockid =  stockcostsxlegal.stockid)
        WHERE stockmaster.mbflag = 'D' AND stockmaster.stockid = '$cveServ' OR stockmaster.description = '$descServ' AND stockmaster.discontinued = 0
        ORDER BY idPartidaEspecifica, idServicio;";

    $ErrMsg = "No se obtuvo el Articulo";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'idServicio' => $myrow ['idServicio'],
            'descripcionServicio' => $myrow ['descripcionServicio'],
            'precioEstimado' => $myrow ['precioEstimado'],
            'idPartidaEspecifica' => $myrow ['idPartidaEspecifica'],
            'descPartidaEspecifica' => $myrow ['descPartidaEspecifica'],
            'cantidad' => $myrow ['cantidad'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'buscarRequisicion') {
    $requiExistente = $_POST['requi'];
    $info = array();

    $SQL="SELECT purchorders.orderno as orderno , 
                purchorderdetails.deliverydate as fechaEnt,
                MAX(purchorderdetails.orderlineno_ ) as orden
            FROM purchorders
            JOIN purchorderdetails on (purchorders.orderno = purchorderdetails.orderno)
            WHERE purchorders.orderno = '$requiExistente'
            GROUP BY purchorders.orderno, purchorderdetails.deliverydate ";

    $ErrMsg = "No se encontro ninguna Requisición ";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'orderno' => $myrow ['orderno'],
            'orden' => $myrow ['orden']);
    }
    $contenido = array('datos' => $info);

    $result = true;
}

if ($option == 'requisicionExistente') {
    $idRequisicion = $_POST['idRequisicion'];
    $info = array();
    $SQL="SELECT purchorders.orderno as orderno , 
                purchorders.requisitionno as noRequisition,
                (MAX(purchorderdetails.orderlineno_ ) + 1) as orden, 
                purchorderdetails.deliverydate as fechaEnt
            FROM purchorders
            JOIN purchorderdetails on (purchorders.orderno = purchorderdetails.orderno)
            WHERE purchorders.orderno = '$idRequisicion' AND purchorderdetails.status in (0,1,2)
            GROUP BY purchorders.orderno, purchorders.requisitionno, purchorderdetails.deliverydate ";

    $ErrMsg = "No se encontro ninguna Requisición ";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'orderno' => $myrow ['orderno'],
            'noRequisition' => $myrow ['noRequisition'],
            'orden' => $myrow ['orden'],
            'fechaEnt' => $myrow ['fechaEnt']);
    }
    $contenido = array('datos' => $info);

    $result = true;
}

if ($option == 'agregarCapturaRequisicion') {
    $nuevaRequisicion = "0";
    $tagref = $_POST['un'];
    $ue = $_POST['ue'];
    $fElaboracion = date("Y-m-d", strtotime($_POST['fechaElabora']));
    $fentrega = date("Y-m-d", strtotime($_POST['fechaEntrega']));
    $razonS = $_POST['rs'];
    $obs = $_POST['obs'];

    // consultar almacen configurado para el usuario
    $SQLLocstock = "SELECT defaultlocation FROM www_users where userid= '".$_SESSION['UserID']."'";
    $ErrMsgLocstock = "No se obtuvo informacion";
    $TransResultLocstock = DB_query($SQLLocstock, $db, $ErrMsgLocstock);

    while ($myrowLocstock = DB_fetch_array($TransResultLocstock)) {
        $idLocstock = $myrowLocstock['defaultlocation'];
    }

    $info = array();
    $SQL = "INSERT INTO purchorders 
        (
            supplierno,comments,rate,allowprint,initiator,requisitionno,intostocklocation,
            deladd1,deladd2,deladd3,deladd4,deladd5,deladd6,contact,version,realorderno,deliveryby,
            status,stat_comment,tagref,dateprinted,orddate,validfrom,validto,revised,deliverydate,lastUpdated,
            autorizafecha,fecha_modificacion,consignment,autorizausuario,capturausuario,solicitausuario,status_aurora,
            supplierorderno,currcode,wo,foliopurch,telephoneContact,refundpercentpurch,totalrefundpercentpurch,systypeorder,
            noag_ad,servicetype,clavepresupuestal,fileRequisicion,nu_ue
        ) VALUES
        (
            '$proveedor','$obs',1,1,'$usuarioIniciador','0','".$idLocstock."', 'dir1','dir2','dir3','dir4','dir5','dir6','','0.00','','','$statusRequisicion',concat(curdate(),' - Order $statusRequisicion ',curdate(),' - Creada: $usuarioIniciador'),'$tagref','1900-01-01 01:01:01',concat('$fElaboracion',' ',TIME(NOW())),'$fElaboracion','$fentrega','1900-01-01','$fentrega',current_timestamp(),'1900-01-01',current_timestamp(),'0','$usuarioAutorizador','$usuarioIniciador','$usuarioIniciador','','','MXN',0,'$nuevaRequisicion','',0,0,'P','',0,'',0,'$ue'
        )";

    $ErrMsg = "No se agrego la nueva Requisición";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $nuevaRequisicion = DB_Last_Insert_ID($db, 'purchorders', 'OrderNo');
    //$requisitionno = GetNextTransNo(19, $db);

    if ($nuevaRequisicion != 0 && !empty($nuevaRequisicion)) {
            $SQL="UPDATE purchorders set comments = '$obs' WHERE  orderno = '$nuevaRequisicion' ";
            $ErrMsg = "No se pudo modificar la Requisición";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            /*$info[] = array(
                'orderno' => $nuevaRequisicion,
                'requisitionno' => $requisitionno);*/
            $info[] = array(
                'orderno' => $nuevaRequisicion);
            //$contenido = "Se actualizo la Requisición con el número: " + $nuevaRequisicion;
            $contenido = array('datos' => $info);
            $result = true;
    } else {
        $contenido = "Error";
        $result = false;
    }
    /*$info[] = array(
        'orderno' => $nuevaRequisicion,
        'requisitionno' => $requisitionno);*/
    //$contenido = "Se agrego la Requisición con el número: " + $nuevaRequisicion;
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'agregarElementosRequisicion') {
    $numRequisicion = $_POST['idReq'];
    $deliverydate = date("Y-m-d", strtotime($_POST['fecEn']));
    $ordenElemento = $_POST['orden'];
    $info = array();
    if($ordenElemento == 0 || $ordenElemento =='' || $ordenElemento == null || $ordenElemento === 'undefined'){
        $ordenElemento = 1;
    }
    
    $SQL="INSERT INTO purchorderdetails (
    orderno, itemcode,deliverydate,itemdescription,glcode,qtyinvoiced,unitprice,actprice,stdcostunit,quantityord,quantityrecd,
    shiptref,jobref,completed,itemno,uom,subtotal_amount,package,pcunit,nw,suppliers_partno,gw,cuft,total_quantity,total_amount,
    discountpercent1,discountpercent2,discountpercent3,narrative,justification,refundpercent,lastUpdated,totalrefundpercent,
    estimated_cost,orderlineno_,saleorderno_,wo,qtywo,womasterid,wocomponent,idgroup,typegroup,customs,pedimento,
    dateship,datecustoms,fecha_modificacion,inputport,factorconversion,invoice_rate,flagautoemision,clavepresupuestal, sn_descripcion_larga, renglon, status
    ) 
    VALUES 
    (
        '$numRequisicion','no_data','$deliverydate','descripcion','1.1.5.1.1','0','100','100','100','10','0',0,0,0,'','',0,'',0,0,'',0,0,'100','1000',0,0,0,'','',0,current_timestamp(),0,0,'$ordenElemento',0,0,0,'','',0,'','','','1900-01-01','1900-01-01',current_timestamp(),'',1,1,'0','$cvePresupuestal','descLarga','', 0
    )";

    $ErrMsg = "No se agrego el elemento a la Requisición: ".$numRequisicion;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    $contenido = "Se agrego el elemento a la Requisición: ".$numRequisicion;

    $result = true;
}

// metodo para traer todos los datos de la requisicion
if ($option == 'mostrarRequisicion') {
    $requi = $_POST['requi'];
    //$o = $_POST['maxorden'];
    $info = array();

    $SQL = "SELECT 
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
            LEFT JOIN (SELECT stockid, SUM(quantity) AS existencia FROM locstock INNER JOIN sec_loccxusser ON locstock.loccode= sec_loccxusser.loccode AND userid='".$_SESSION['UserID']."'
            GROUP BY stockid) AS almacen ON stockmaster.stockid= almacen.stockid
            WHERE purchorderdetails.orderno = '$requi' AND purchorderdetails.status ='2'
            ORDER BY orden";

    $ErrMsg = "No se encontro la Requisición: ".$requi;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $clavespresupuestales = array();
        $clavespresupuestales[] = array('cvePresupuestal' => $myrow['clavePresupuestal'] );

        if (empty($myrow["clavePresupuestal"])) {
            $clavespresupuestales = array();
            
            $consulta= "SELECT DISTINCT cvefrom FROM chartdetailsbudgetlog 
                        WHERE chartdetailsbudgetlog.tagref='".$myrow["tagref"]."' 
                        AND chartdetailsbudgetlog.partida_esp= '".$myrow["idPartida"]."' 
                        AND chartdetailsbudgetlog.period= '".$periodo."';";

            $ErrMsg = "No se obtuvo el Articulo";
            $resultado = DB_query($consulta, $db, $ErrMsg);

            while ($registro = DB_fetch_array($resultado)) {
                $clavespresupuestales[] = array(
                    'cvePresupuestal' => $registro ['cvefrom'] );
            }
        }

        $info[] = array(
            'idRequisicion' => $myrow ['idRequisicion'],
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
            'clavePresupuestal' => $clavespresupuestales,
            'descLarga' => $myrow ['descLarga'],
            'renglon' => $myrow ['renglon'] );
    }
    $contenido = array('datos' => $info);

    $result = true;
}

if ($option == 'modificarElementosRequisicion') {
    $req = $_POST['req'];
    $itemcode = $_POST['itemcode'];
    $fechent = $_POST['fechent'];
    $itemdesc = $_POST['itemdesc'];
    $price = empty($_POST['price']) ? 0 : $_POST['price'];
    $cantidad = empty($_POST['cantidad']) ? 0 : $_POST['cantidad'];
    $order = empty($_POST['order']) ? 0 : $_POST['order'];
    $cvepre = $_POST['cvepre'];
    $almacen = $_POST['almacen'];
    $comments = $_POST['comments'];
    $longText = $_POST['longText'];
    $renglon = $_POST['renglon'];
    $cppt = $_POST['cppt'];
    $info = array();
    $total_quantity = ($cantidad * $price);
    $total_amount = ($total_quantity * $price);

    if($order == 0 || $order == ''){
        $order = 1;
    }

    $SQLModAnexo="UPDATE tb_cnfg_anexo_tecnico SET ind_status = 3, nu_orden_requisicion = '$order'  WHERE nu_requisicion = '$req' AND nu_orden_requisicion = '$order'";
    $ErrMsgModAnexo = "No se pudo limpiar el anexo técnico de la Requisición: ".$req;
    $TransResultModAnexo = DB_query($SQLModAnexo, $db, $ErrMsgModAnexo);

    $SQL="UPDATE purchorderdetails 
        SET itemcode = '$itemcode', itemdescription= '$itemdesc', unitprice = '$price',actprice = '$price', stdcostunit = '$price', quantityord ='$cantidad', itemno = '$itemcode', total_quantity = '$total_quantity', total_amount ='$total_amount', fecha_modificacion = current_timestamp(), clavepresupuestal = '$cvepre', status = 1, sn_descripcion_larga = '$longText', renglon = '$renglon', ln_clave_iden = '$cppt'
        WHERE orderno = '$req' AND orderlineno_ = '$order' and status = 0 AND orderlineno_ != 0";
    $ErrMsg = "No se pudo modificar la Requisición: ".$req;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $SQLMod="UPDATE purchorderdetails 
        SET itemcode = '$itemcode', itemdescription= '$itemdesc', unitprice = '$price',actprice = '$price',
        stdcostunit = '$price',quantityord ='$cantidad', itemno = '$itemcode', total_quantity = '$total_quantity', total_amount ='$total_amount', fecha_modificacion = current_timestamp(), clavepresupuestal = '$cvepre', sn_descripcion_larga = '$longText', renglon = '$renglon', ln_clave_iden = '$cppt'
        WHERE orderno = '$req' AND orderlineno_ = '$order' and status = 2";

    $ErrMsgMod = "No se pudo modificar la Requisición: ".$req;
    $TransResultMod = DB_query($SQLMod, $db, $ErrMsgMod);
    
    $contenido = "Se Modifico la Requisición: ".$req;

    $result = true;
}

if ($option == 'eliminarElementosRequisicion') {
    $idreq = $_POST['idReq'];
    $noreq = $_POST['noReq'];
    $orden = $_POST['orden'];
    $info = array();

    $SQL = "UPDATE purchorderdetails SET status = 3 WHERE orderno = '$idreq' AND orderlineno_ = '$orden' AND status in (0,2)";
    $ErrMsg = "No se elimino el elemento a la Requisición: ".$idreq;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $SQLDelAnexo = "UPDATE tb_cnfg_anexo_tecnico set ind_status = 3, nu_orden_requisicion = 0 WHERE nu_requisicion = '$idreq' AND nu_orden_requisicion = '$orden'";
    $ErrMsgDelAnexo = "Problemas al quitar la asignacion del elemento del anexo";
    $TransResultDelAnexo = DB_query($SQLDelAnexo, $db, $ErrMsgDelAnexo);

    if($noreq == '' || $noreq == 0 || $noreq === 'undefined' || $noreq == null){
        $SQLDELTrash = "DELETE FROM purchorderdetails WHERE orderno = '$idreq' AND orderlineno_ = '$orden' AND status = 3"; 
        $ErrMsgDELTrash = "No se elimino el elemento a la Requisición";
        $TransResultDELTrash = DB_query($SQLDELTrash, $db, $ErrMsgDELTrash);  
    }
    
    $contenido = "Se elimino el elemento a la Requisición: ".$nreq;

    $result = true;
}

if ($option == 'mostrarCvePresupuestal') {
    $tagrefCveP = $_POST['un'];
    $info = array();
    $SQL="SELECT accountcode,anho,cve_ramo,tagref,ue,cppt,partida_esp FROM chartdetailsbudgetbytag where tgref = '$tagrefCveP'";
    $ErrMsg = "No se obtuvo la Clave Presupuestal";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'accountcode' => $myrow ['accountcode']);
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarUnidadNegocio') {
    $legalid = $_POST['legalid'];

    $sqlWhere = "";
    if ($legalid != 0 && !empty($legalid)) {
        $sqlWhere = " AND t.legalid IN(".$legalid.") ";
    }
    $info = array();
    $SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription 
            FROM sec_unegsxuser u,tags t 
            JOIN areas ON t.areacode = areas.areacode  
            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' " . $sqlWhere . "
            ORDER BY t.tagref";

    $ErrMsg = "No se obtuvieron las URG";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'tagref' => $myrow ['tagref'], 'tagdescription' => $myrow ['tagdescription'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'obtenerBotones') {
    $info = array();
    $SQL = "SELECT tb_botones_status.statusid,
            tb_botones_status.statusname,
            tb_botones_status.namebutton,
            tb_botones_status.functionid,
            tb_botones_status.adecuacionPresupuestal,
            tb_botones_status.clases
            FROM tb_botones_status
            JOIN sec_profilexuser ON sec_profilexuser.userid = '".$_SESSION['UserID']."'
            JOIN sec_funxprofile ON sec_funxprofile.profileid = sec_profilexuser.profileid
            WHERE 
            (tb_botones_status.sn_captura_requisicion = 1)
            AND
            (tb_botones_status.functionid = sec_funxprofile.functionid 
            OR 
            tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid)
            )";

    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'statusid' => $myrow ['statusid'],
            'statusname' => $myrow ['statusname'],
            'namebutton' => $myrow ['namebutton'],
            'functionid' => $myrow ['functionid'],
            'clases' => $myrow ['clases']
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'guardarRequisicionNueva') {
    $req = $_POST['idReq'];
    $status = $_POST['status'];
    $comments = $_POST['comments'];
    $fechaFrom = date("Y-m-d", strtotime($_POST['fechaFrom']));
    $fechaTo = date("Y-m-d", strtotime($_POST['fechaTo']));
    $tagref= $_POST['tagref'];
    $ue= $_POST['ue'];
    $anexoTec = $_POST['anexoTec'];
    $requisitionno = GetNextTransNo(19, $db);
    $info = array();
    $error = 0;

    if($anexoTec == 1){
        $SQLFindAnexo = "SELECT renglon FROM purchorderdetails where orderno = '$req' and renglon <> ''";
        $ErrMsgFindAnexo = "Error al buscar los renglones del anexo tecnico asignados";
        $TransResultFindAnexo = DB_query($SQLFindAnexo, $db, $ErrMsgFindAnexo);
        if(DB_num_rows($TransResultFindAnexo) < 1 ){
            $contenido = "La requisición no esta vinculada a un Anexo Técnico.";
            $error = 1;
        }
    }
    if($error > 0){
        $contenido = $contenido;
        $result = false;
    }else{
        $SQL = "UPDATE purchorders 
                SET status = '$status', requisitionno = '$requisitionno', comments = '$comments',
                validfrom = '$fechaFrom', validto = '$fechaTo', deliverydate = '$fechaTo', 
                fecha_modificacion = current_timestamp(), tagref= '$tagref', nu_ue= '$ue', nu_anexo_tecnico = '$anexoTec'
                WHERE orderno  = '$req'";

        $ErrMsg = "No se obtuvieron los botones para el proceso";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $SQLDEL= "UPDATE purchorderdetails SET fecha_modificacion = current_timestamp(), orderlineno_ = 0 WHERE orderno  = '$req' and status = 3";
        $ErrMsgDEL = "No se pudo eliminar";
        $TransResultDEL = DB_query($SQLDEL, $db, $ErrMsgDEL);

        $SQLPO = "UPDATE purchorderdetails SET fecha_modificacion = current_timestamp(), status = 2 WHERE orderno  = '$req' and status = 1";
        $ErrMsgPO = "No se obtuvieron los botones para el proceso";
        $TransResultPO = DB_query($SQLPO, $db, $ErrMsgPO);
        
        $contenido = $requisitionno;
        $result = true;
    }
}

if ($option == 'guardarRequisicion') {
    $req = $_POST['idReq'];
    $status = $_POST['status'];
    $comments = $_POST['comments'];
    $fechaFrom = date("Y-m-d", strtotime($_POST['fechaFrom']));
    $fechaTo = date("Y-m-d", strtotime($_POST['fechaTo']));
    $tagref= $_POST['tagref'];
    $ue= $_POST['ue'];
    $anexoTec = $_POST['anexoTec'];
    $info = array();
    $error = 0;

    $SQLDEL= "UPDATE purchorderdetails SET fecha_modificacion = current_timestamp(), orderlineno_ = 0, status = 0 WHERE orderno  = '$req' and status = 3";
    $ErrMsgDEL = "No se pudo eliminar";
    $TransResultDEL = DB_query($SQLDEL, $db, $ErrMsgDEL);

    if($status == 'Original' || $status == 'Autorizado' || $status == 'Cancelado'){
        $contenido = "No es posible modificar una requisición con estatus ". $status;
        $result = false;
    }else{

        if($anexoTec == 1){
            $SQLFindAnexo = "SELECT renglon FROM purchorderdetails where orderno = '$req' and renglon <> ''";
            $ErrMsgFindAnexo = "Error al buscar los renglones del anexo tecnico asignados";
            $TransResultFindAnexo = DB_query($SQLFindAnexo, $db, $ErrMsgFindAnexo);
            if(DB_num_rows($TransResultFindAnexo) < 1 ){
                $contenido = "La requisición no esta vinculada a un Anexo Técnico.";
                $error = 1;
            }
        }
        if($error > 0){
            $contenido = $contenido;
            $result = false;
        }else{
            $SQL= "UPDATE purchorders SET status = '$status', comments = '$comments', validfrom = '$fechaFrom', validto = '$fechaTo', deliverydate = '$fechaTo', fecha_modificacion = current_timestamp(), tagref= '$tagref', nu_ue= '$ue', nu_anexo_tecnico = '$anexoTec'
            WHERE orderno  = '$req'";

            $ErrMsg = "Error al guardar";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $SQLPO= "UPDATE purchorderdetails SET fecha_modificacion = current_timestamp(), status = 2 WHERE orderno  = '$req' and status = 1 and orderlineno_ > 0 ";
            $ErrMsgPO = "Error al guardar detalles";
            $TransResultPO = DB_query($SQLPO, $db, $ErrMsgPO);

            $contenido = $requisitionno;
            $result = true;
        }
    }
}

if ($option == 'cancelarRequisicion') {
    $req = $_POST['noReq'];
    $status = $_POST['status'];

    $info = array();
    
    $SQL = "UPDATE purchorderdetails SET status = 3, orderlineno_= 0, fecha_modificacion = current_timestamp() 
            WHERE orderno  = '$req' and status = 1";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $contenido = "Se cancelaron todos los elementos no guardados en la requsisicone ";
    $result = true;
}

if ($option == 'avanzarRequisicion') {
    $req = $_POST['noReq'];
    $info = array();
    
    $SQL = "SELECT orderno, requisitionno, status, 
            if(sn_estatus_anterior=1,'Capturado',if(sn_estatus_anterior=2,'Validar',if(sn_estatus_anterior=3,'PorAutorizar',if(sn_estatus_anterior=4,'Autorizado',0)))) as sn_estatus_anterior, 
            if(sn_estatus_siguiente=1,'Capturado',if(sn_estatus_siguiente=2,'Validar',if(sn_estatus_siguiente=3,'PorAutorizar',if(sn_estatus_siguiente=4,'Autorizado',0)))) as sn_estatus_siguiente
            FROM purchorders p 
            JOIN tb_botones_status tbs on (p.status = tbs.statusname) 
            WHERE orderno = '$req'";

    $ErrMsg = "No se pudo rechazar la requisición";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $statusNuevo = $myrow['sn_estatus_siguiente'];
        $SQL = "UPDATE purchorders SET status = '$statusNuevo' WHERE orderno = '$req'";
        $ErrMsg = "No se pudo avanzar la requisición";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        //$contenido = array('statusNuevo' => $statusNuevo);
    }
    $contenido = "Se Avanzó la Requisición: ".$req;
    $result = true;
}

if ($option == 'rechazarRequisicion') {
    $req = $_POST['noReq'];
    $info = array();
    $SQL = "SELECT 
    orderno, requisitionno, status, 
    if(sn_estatus_anterior=1,'Capturado',if(sn_estatus_anterior=2,'Validar',if(sn_estatus_anterior=3,'PorAutorizar',if(sn_estatus_anterior=4,'Autorizado',0)))) as sn_estatus_anterior, 
    if(sn_estatus_siguiente=1,'Capturado',if(sn_estatus_siguiente=2,'Validar',if(sn_estatus_siguiente=3,'PorAutorizar',if(sn_estatus_siguiente=4,'Autorizado',0)))) as sn_estatus_siguiente
    FROM purchorders p 
    JOIN tb_botones_status tbs on (p.status = tbs.statusname) 
    WHERE orderno = '$req'";
    $ErrMsg = "No se pudo rechazar la requisición";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $statusNuevo = $myrow['sn_estatus_anterior'];
        //echo '<br>'$statusNuevo;
        /*$info[] = array(
            'orderno' => $myrow ['orderno'], 
            'requisitionno' => $myrow ['requisitionno'], 
            'status' => $myrow ['status'],
            'sn_estatus_anterior' => $myrow ['sn_estatus_anterior'],
            'sn_estatus_siguiente' => $myrow ['sn_estatus_siguiente']
        );*/
        $SQL = "UPDATE purchorders SET status = '$statusNuevo' WHERE orderno = '$req'";
        $ErrMsg = "No se pudo rechazar la requisición";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        //$contenido = array('statusNuevo' => $statusNuevo);
    }
    $contenido = "Se Rechazo la Requisición: ".$req;
    $result = true;
}

if ($option == 'reIndexar') {
    $idReq = $_POST['idReq'];
    $info = array();
    $SQL = "SELECT orderlineno_ FROM purchorderdetails 
    WHERE orderno = '$idReq' AND orderlineno_ <> 0 
    ORDER BY orderlineno_";
    $ErrMsg = "No se pudo reindexar";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $x = 0;
    while ($myrow = DB_fetch_array($TransResult)) {
        $info = array( $myrow ['orderlineno_'] );
        foreach ($info as $key => $value) {
            $x = $x + 1;
            $SQL2 ="UPDATE purchorderdetails SET orderlineno_ = '$x' where orderno = '$idReq' and orderlineno_ = '$value'";
            $ErrMsg2 = "No se pudo reindexar";
            $TransResult2 = DB_query($SQL2, $db, $ErrMsg2);
        }
    }
    $contenido = "Exito";
    $result = true;
}

if ($option == 'limpiar') {
    $idReq = $_POST['idReq'];
    $info = array();
    $SQLSearchAnexo="SELECT orderno FROM purchorders WHERE orderno = '$idReq' AND (nu_anexo_tecnico = 0 OR nu_anexo_tecnico is null)";
    $ErrMsgSearchAnexo = "No se encontro el anexo técnico de la Requisición: ".$req;
    $TransResultSearchAnexo = DB_query($SQLSearchAnexo, $db, $ErrMsgSearchAnexo);
    if(DB_num_rows($TransResultSearchAnexo) == 1){
        $SQLCleanAnexo="UPDATE tb_cnfg_anexo_tecnico SET ind_status = 2, nu_orden_requisicion = 0, nu_requisicion = 0 WHERE nu_requisicion = '$idReq'";
        $ErrMsgCleanAnexo = "No se pudo limpiar el anexo técnico de la Requisición: ".$req;
        $TransResultCleanAnexo = DB_query($SQLCleanAnexo, $db, $ErrMsgCleanAnexo);
    }
    $SQLCleanAnexo="UPDATE tb_cnfg_anexo_tecnico SET ind_status = 3, nu_orden_requisicion = 0 WHERE nu_requisicion = '$idReq' AND ind_status = 10 AND id_anexo > 0";
    $ErrMsgCleanAnexo = "No se pudo limpiar el anexo técnico de la Requisición: ".$req;
    $TransResultCleanAnexo = DB_query($SQLCleanAnexo, $db, $ErrMsgCleanAnexo);
    $SQL = "UPDATE purchorderdetails SET status = 2 WHERE status = 3 AND orderno = '$idReq' and orderlineno_ > 0";
    $ErrMsg = "No se pudo limpiar los registros";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $SQL2 = "UPDATE purchorderdetails SET orderlineno_ = 0, status = 0  WHERE status in (0,1,3) AND orderno = '$idReq' OR itemcode = 'no_data'";
    $ErrMsg2 = "No se pudo limpiar los registros";
    $TransResult2 = DB_query($SQL2, $db, $ErrMsg2);
    $SQL3 = "UPDATE purchorderdetails SET status = 0, orderlineno_ = 0 WHERE orderno = '$idReq' AND orderlineno_ = 0";
    $ErrMsg3 = "No se pudo limpiar los registros";
    $TransResult3 = DB_query($SQL3, $db, $ErrMsg3);
    $contenido = "Exito...";
    $result = true;
}

if ($option == 'buscarStatusReq') {
    $idReq = $_POST['idReq'];
    $statusReq = "";
    $info = array();
    $SQL = "SELECT status FROM purchorders WHERE orderno = '$idReq'";
    $ErrMsg = "No se encontro un status";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if(DB_num_rows($TransResult) == 1){
        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array(
                'status' => $myrow ['status']
            );
        }
        $contenido = array('datos' => $info);
        $result = true;
    }else{
        $contenido = "Error al buscar el estatus de la requisicion";
        $result = false;
    }
    
    
}

if ($option == 'validarRequisicion') {
    $req = $_POST['noReq'];
    $status = $_POST['status'];
    $info = array();
    $SQL = "UPDATE purchorders SET status = '$status', fecha_modificacion = current_timestamp() where orderno  = '$req' and status = 'Capturado'";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $contenido = "Se Valido la Requisición: ".$req;
    $result = true;
}

if ($option == 'autorizarRequisicion') {
    $req = $_POST['noReq'];
    $status = $_POST['status'];
    $tagref= $_POST['tagref'];
    $info = array();

    $SQL = "UPDATE purchorders SET status = '$status', fecha_modificacion = current_timestamp() where orderno  = '$req' and status = 'PorAutorizar'";
    
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    $SQL2 = "SELECT p.orderno as idreq, p.requisitionno as noreq, p.status as statusreq, p.tagref as tagref,  
            pd.clavepresupuestal as cvepresupuestal, pd.itemcode, pd.itemdescription as itemdescription, 
            pd.quantityord as cantidad, pd.unitprice AS precio, cdbt.partida_esp as partida
            FROM purchorders p 
            JOIN purchorderdetails pd on (p.orderno = pd.orderno) 
            JOIN chartdetailsbudgetbytag cdbt on (p.tagref = cdbt.tagref and pd.clavepresupuestal = cdbt.accountcode ) 
            WHERE p.orderno = '$req'";

    $ErrMsg2 = "No se obtuvieron los botones para el proceso";
    $TransResult2 = DB_query($SQL2, $db, $ErrMsg2);

    while ($myrow = DB_fetch_array($TransResult2)) {
        $req = $myrow['idreq'];
        $tagref = $myrow['tagref'];
        $clave = $myrow['cvepresupuestal'];
        $total= $myrow['cantidad']*$myrow['precio'];
        $partida_esp = $myrow['partida'];
        $description = $myrow['itemdescription'];
        
        $validacion = fnInsertPresupuestoLog(
            $db,
            $type,
            $req,
            $tagref,
            $clave,
            $periodo,
            $total*(-1),
            258,
            $partida_esp,
            $description = "insercion presupuesto partida: ".$partida_esp." con producto: ".$description
        );
    }

    $contenido = "Se Autorizo la Requisición: ".$req;
    $result = true;
}

if ($option == 'porAutorizarRequisicion') {
    $req = $_POST['noReq'];
    $status = $_POST['status'];
    $info = array();
    $SQL = "UPDATE purchorders SET status = '$status', fecha_modificacion = current_timestamp() where orderno  = '$req' and status = 'Validar'";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $contenido = "Se actualizo el status de la Requisición: ".$req;
    $result = true;
}

if ($option == 'buscarPerfilUsr') {
    $info = array();
    //$SQL = "SELECT userid FROM sec_profilexuser WHERE profileid IN (9,10,11)";
    $SQL = "SELECT userid, profileid FROM sec_profilexuser WHERE userid = '".$_SESSION['UserID']."'";
    $ErrMsg = "No se encontro un perfil";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'userid' => $myrow ['userid'],
            'profileid' => $myrow ['profileid']
        );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if($option == 'seleccionaAnexo'){
    $idrequi = $_POST['idrequi'];
    $ur = $_POST['ur'];
    $ue = $_POST['ue'];
    $condicion = "";

    $SQLExisteAnexo = "SELECT nu_anexo, nu_tagref, nu_ue, nu_requisicion, ind_status FROM tb_cnfg_anexo_tecnico
            WHERE nu_requisicion = '$idrequi' AND ind_status in (2,3,10) AND nu_type = 51 AND nu_tagref = '$ur' AND nu_ue = '$ue' 
            GROUP BY nu_anexo, nu_tagref, nu_ue, nu_requisicion, ind_status";
    $ErrMsgExisteAnexo = "No se encontro una requisición";
    $TransResultExisteAnexo = DB_query($SQLExisteAnexo, $db, $ErrMsgExisteAnexo);
    $myrowExisteAnexo = DB_fetch_array($TransResultExisteAnexo);
    if(DB_num_rows($TransResultExisteAnexo) >= 1){
        $folioExisteAnexo[] = array(
            'folioAnexo' => $myrowExisteAnexo ['nu_anexo']
        );
    }else{
        $folioExisteAnexo[] = array(
            'folioAnexo' => 0
        );
    }
    
    $SQL="SELECT nu_anexo, nu_tagref, nu_ue, nu_requisicion, ind_status FROM tb_cnfg_anexo_tecnico
            WHERE (nu_requisicion = 0 OR nu_requisicion = '$idrequi') AND ind_status in (2,3,10) AND nu_type = 51 AND nu_tagref = '$ur' AND nu_ue = '$ue' 
            GROUP BY nu_anexo, nu_tagref, nu_ue, nu_requisicion, ind_status";
    $ErrMsg = "No se encontro una requisición";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'folioAnexo' => $myrow ['nu_anexo'],
            'ur' => $myrow ['nu_tagref'],
            'ue' => $myrow ['nu_ue'],
            //'idrequisicion' => ($myrow ['nu_requisicion'] ==  0) ? 0 : $myrow ['nu_requisicion']),
            'idrequisicion' => $myrow ['nu_requisicion'],
            'ind_status' => $myrow ['ind_status']
        );
    }
    $contenido = array('datos' => $info, 'datosFolioExisteAnexo' => $folioExisteAnexo);
    $result = true;
}

if($option == 'saveAnexo'){
    $idReq = $_POST['idReq'];
    $ur = $_POST['ur'];
    $ue = $_POST['ue'];
    $ordenElementoRequi = $_POST['ordenElementoRequi'];
    $folioAnexo = $_POST['folioAnexo'];

    $SQLDelAnexo = "UPDATE tb_cnfg_anexo_tecnico set nu_requisicion = 0, ind_status = 2, nu_orden_requisicion = 0, nu_proceso = 0 WHERE nu_requisicion = '$idReq' AND nu_tagref = '$ur' AND nu_ue = '$ue' ";
    $ErrMsgDelAnexo = "Problemas para modificar el anexo tecnico";
    $TransResultDelAnexo = DB_query($SQLDelAnexo, $db, $ErrMsgDelAnexo);

    $SQL="SELECT nu_anexo FROM tb_cnfg_anexo_tecnico WHERE nu_anexo = '$folioAnexo' and ind_status = 2 AND nu_requisicion = 0 GROUP BY nu_anexo";
    $ErrMsg = "Problemas para modificar el anexo tecnico";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if(DB_num_rows($TransResult) == 1){
        $SQLAddAnexo = "UPDATE tb_cnfg_anexo_tecnico set nu_requisicion = '$idReq', ind_status = 3, nu_tagref = '$ur', nu_ue = '$ue', nu_orden_requisicion = 0 WHERE nu_anexo = '$folioAnexo' ";
        $ErrMsgAddAnexo = "Problemas para guardar el anexo tecnico";
        $TransResultAddAnexo = DB_query($SQLAddAnexo, $db, $ErrMsgAddAnexo);
        $contenido = "Se ha asignado exitosamente el anexo ".$folioAnexo."";
        $result = true;
    }else{
        $contenido = "El anexo técnico ". $folioAnexo ." ya ha sido seleccionado";
        $result = false;
    }
}

if ($option == 'muestraInfoAnexo') {
    $idReq = $_POST['idReq'];
    $ordenAnexo = 0;
    $ur = $_POST['ur'];
    $ue = $_POST['ue'];
    $ordenElementoRequi = $_POST['ordenElementoRequi'];
    $folioAnexo = $_POST['folioAnexo'];
    $arrayValoresRenglonRequi = "";
    //$arrayFoliosAnexo = $_POST['arrayFoliosAnexo'];
    $info = array();
    /*for($a=0;$a<count($arrayFoliosAnexo);$a++){
        $valoresFolioAnexos.=$arrayFoliosAnexo[$a].",";
    }
    $valoresFolioAnexos=substr($valoresFolioAnexos, 0, -1);*/
    
    $SQL = "SELECT id_anexo, nu_anexo, nu_tagref, nu_ue, nu_type, nu_requisicion, sn_area, nu_proceso, nu_orden_requisicion, dt_fecha_creacion, ln_visto_bueno, ln_vobo_requiriente, sn_firma, txt_informacion_creacion, sn_revisado_por, sn_autorizado_por, txt_descripcion_antecedentes, txt_justificacion, ln_viabilidad, txt_bien_serevicio, txt_desc_bien_serevicio, nu_cantidad 
    FROM tb_cnfg_anexo_tecnico ant WHERE nu_anexo = '$folioAnexo' AND nu_requisicion = '$idReq' AND  nu_tagref = '$ur' AND nu_ue = '$ue' AND ind_status in (3,10) AND (nu_orden_requisicion = 0 OR nu_orden_requisicion = '$ordenElementoRequi') ";

    $ErrMsg = "No se encontro un requisicion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {        
        $ordenAnexo++;
        if($myrow ['nu_proceso'] == 0){
            $SQLAnexoID = "UPDATE tb_cnfg_anexo_tecnico set nu_proceso = '$ordenAnexo' WHERE nu_anexo = '$folioAnexo' AND nu_requisicion = '$idReq' AND  nu_tagref = '$ur' AND nu_ue = '$ue' and ind_status = 3 AND id_anexo = ".$myrow ['id_anexo']." ";
            $ErrMsgAnexoID = "Problemas para modificar el ID del anexo tecnico";
            $TransResultAnexoID = DB_query($SQLAnexoID, $db, $ErrMsgAnexoID);
            $myrow ['nu_proceso'] = $ordenAnexo;
        }
        
        $info[] = array(
            'idAnexoElemento' => $myrow ['id_anexo'],
            'idanexo' => $myrow ['nu_anexo'],
            'ur' => $myrow ['nu_tagref'],
            'ue' => $myrow ['nu_ue'],
            'tipo' => $myrow ['nu_type'],
            'idrequisicion' => $myrow ['nu_requisicion'],
            'idpartida' => $myrow ['nu_orden_requisicion'],
            'idproceso' => $myrow ['nu_proceso'],
            'ordenAnexo' => $ordenAnexo,
            'bienServicio' => $myrow ['txt_bien_serevicio'],
            'descripcion_bien_serv' => $myrow ['txt_desc_bien_serevicio'],
            'cantidad' => $myrow ['nu_cantidad'],
            'observaciones' => $myrow['txt_descripcion_antecedentes']
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if($option == 'guardarAnexoTecnico'){
    $idRequisicion = $_POST['idRequisicion'];
    $statusAnexo = $_POST['statusAnexo'];

    if($statusAnexo == 1){
        /*$SQL="UPDATE purchorders SET nu_anexo_tecnico = 1 WHERE orderno = '$idRequisicion' AND status not in ('Creada', 'Creada', 'Autorizado', 'Cancelado') ";
        $ErrMsg = "Problemas para guardar el anexo tecnico";
        $TransResult = DB_query($SQL, $db, $ErrMsg);*/

        $contenido = "Se asigno anexo técnico a la requisicion";
    }else{
        $SQL="UPDATE purchorders SET nu_anexo_tecnico = 0 WHERE orderno = '$idRequisicion' AND status not in ('Creada', 'Creada', 'Autorizado', 'Cancelado') ";
        $ErrMsg = "Problemas para guardar el anexo tecnico";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $SQLPD="UPDATE purchorderdetails SET renglon = '' WHERE orderno = '$idRequisicion'";
        $ErrMsgPD = "Problemas para guardar el anexo tecnico";
        $TransResultPD = DB_query($SQLPD, $db, $ErrMsgPD);

        $SQLDelAnexo= "UPDATE tb_cnfg_anexo_tecnico SET nu_requisicion = 0, ind_status = 2, nu_orden_requisicion = 0 WHERE nu_requisicion = '$idRequisicion' ";
        $ErrMsgDelAnexo = "error al remover el anexo técnico";
        $TransResultDelAnexo = DB_query($SQLDelAnexo, $db, $ErrMsgDelAnexo);

        $contenido = "Se removio el anexo técnico de la requisicion";

    }
    $result = true;
}


if($option == 'asignaElementoAnexo'){
    $idReq = $_POST['idReq'];
    $idAnexo = $_POST['idAnexo'];
    $idAnexoElemento = $_POST['idAnexoElemento'];
    $ordenAnexo = $_POST['ordenAnexo'];
    $ordenElementoRequi = $_POST['ordenElementoRequi'];
    $check = $_POST['check'];
    
    if($check == 0){
        $SQL = "UPDATE tb_cnfg_anexo_tecnico SET nu_orden_requisicion = 0, ind_status = 3 WHERE id_anexo = '$idAnexoElemento' and nu_anexo = '$idAnexo' and nu_requisicion = '$idReq' ";
        $ErrMsg = "Error al remover un elemento del anexo a la reuisicion";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }else{
        $SQL = "UPDATE tb_cnfg_anexo_tecnico SET nu_orden_requisicion = '$ordenElementoRequi' , ind_status = 10 WHERE id_anexo = '$idAnexoElemento' and nu_anexo = '$idAnexo' and nu_requisicion = '$idReq'  AND nu_orden_requisicion = 0 ";
        $ErrMsg = "Error al asignar un elemento del anexo a la reuisicion";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }
    $result = true;

}

if($option == 'removeAnexo'){
    $idRequisicion = $_POST['idReq'];
    $info = array();

    /*$SQLSelAnexo= "SELECT id_anexo, nu_anexo, nu_tagref, nu_ue, nu_requisicion FROM tb_cnfg_anexo_tecnico WHERE nu_requisicion = '$idRequisicion'";
    $ErrMsgMesSelAnexo = "error al buscar el anexo técnico asignado a una requisicion";
    $TransResultSelAnexo = DB_query($SQLSelAnexo, $db, $ErrMsgSelAnexo);
    while ($rowReqAnexo = DB_fetch_array($TransResultSelAnexo)) {
        $idAnexo = $rowReqAnexo ['id_anexo'];
        $nu_anexo = $rowReqAnexo ['nu_anexo'];
        array_push($info, $idAnexo);
    }
    for($a=0;$a<count($info);$a++){
        $valoresFolioAnexos.=$info[$a].",";
    }
    $valoresFolioAnexos=substr($valoresFolioAnexos, 0, -1);*/
    //print_r($info);
    $SQL= "UPDATE tb_cnfg_anexo_tecnico SET nu_requisicion = 0, ind_status = 2, nu_orden_requisicion = 0 WHERE nu_requisicion = '$idRequisicion' ";
    $ErrMsg = "error al remover el anexo técnico";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    $result = true;
}

if ($option == "validarPresupuestoPartida") {
    $info = array();
    $idReq = $_POST['idReq'];
    $noReq = $_POST['noReq'];
    $partEsp = $_POST['partidaEsp'];
    $presupuestoActual = $_POST['presupuestoActual'];
    $orden = $_POST['orden'];
    $cvePresupuestalActual = $_POST['cvePresupuestal'];

    $SQLMes = "SELECT periods.periodno, periods.lastdate_in_period, cat_Months.mes as mesName
                FROM periods 
            LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
            WHERE periods.lastdate_in_period like '%".$myrow ['anho']."%' AND periods.periodno = '".$periodo."'
            ORDER BY periods.lastdate_in_period asc";

    $ErrMsgMes = "No se obtuvo informacion";
    $resultPeriods = DB_query($SQLMes, $db, $ErrMsgMes);

    while ($rowPeriods = DB_fetch_array($resultPeriods)) {
        $nombreMes = $rowPeriods ['mesName'];
    }

    $SQL="SELECT orderno,itemcode,itemdescription, unitprice,quantityord,clavepresupuestal, orderlineno_ 
        FROM purchorderdetails 
        WHERE orderno = '$idReq' and status not in (0,3)";

    $ErrMsg = "No se encontro un requisicion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        /*$info[] = array(
            'idRequisicion' => $myrow ['orderno'],
            'itemcode' => $myrow ['itemcode'],
            'itemdesc' => $myrow ['itemdescription'],
            'precio' => $myrow ['unitprice'],
            'cantidad' => $myrow ['quantityord'],
            'orderlineno' => $myrow ['orderlineno_'],
            'clavepresupuestal' => $myrow ['clavepresupuestal']
        );*/
        $idRequisicion = $myrow ['orderno'];
        $itemcode = $myrow ['itemcode'];
        $itemdesc = $myrow ['itemdescription'];
        $precio = $myrow ['unitprice'];
        $cantidad = $myrow ['quantityord'];
        $ordenRequi = $myrow ['orderlineno_'];
        $clavepresupuestal = $myrow ['clavepresupuestal'];
        $total_amount = $myrow ['quantityord'] * $myrow ['unitprice'];
        $infoPresupuesto = fnInfoPresupuesto($db, $clavepresupuestal, $periodo);
        $presupuestoInicial = $infoPresupuesto[0][$nombreMes];

        if ($clavepresupuestal == $cvePresupuestalActual) {
            $total_gasto += $total_amount;
            
            $presupuestoNuevo = $presupuestoInicial  - $total_gasto;
            ///echo $presupuestoNuevo;
        } else {
            $presupuestoNuevo = $presupuestoInicial;
        }
    }
    //$contenido = array('datos' => $info);
    //$contenido =$presupuestoInicial ." - ". $presupuestoNuevo ." - ". $total_gasto ;
    //$contenido = array('nuevoprsupuesto' =>$presupuestoNuevo,'ordenReq' =>$ordenRequi);
    $contenido= $presupuestoNuevo;
    $result = true;
}

if ($option == 'validarRequisicionPanel') {
    //setlocale(LC_MONETARY, 'es_MX');
    $req = $_POST['idReq'];
    $dependencia = $_POST['dependencia'];
    $nombreMes = "";
    $contenido = "";
    $result = "";
    $mensajeValidacion = "";
    $info = array();
    $disponiblepartida= array();
    //Regresa el nomre del mes actual para poder calcular el presupuesto actual 
    $SQLMes = "SELECT periods.periodno, periods.lastdate_in_period, cat_Months.mes as mesName
                FROM periods 
            LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
            WHERE periods.lastdate_in_period like '%".$myrow ['anho']."%' AND periods.periodno = '".$periodo."'
            ORDER BY periods.lastdate_in_period asc";

    $ErrMsgMes = "No se obtuvo informacion";
    $resultPeriods = DB_query($SQLMes, $db, $ErrMsgMes);
    while ($rowPeriods = DB_fetch_array($resultPeriods)) {
        $nombreMes = $rowPeriods ['mesName'];
    }

    $SQL = "SELECT p.orderno as idReq, p.requisitionno as noReq, p.status as statusReq, p.tagref as tagref,p.nu_ue as ue , p.comments as comments, p.deliverydate as fdelivery, p.validfrom, p.validto, tb_solicitudes_almacen_detalle.nu_id_detalle as idDetalleSolAlmacen, pd.itemcode as itemcode, pd.itemdescription as itemdescription, pd.unitprice as precio, pd.quantityord as cantidadReq, pd.orderlineno_, pd.clavepresupuestal as clavepre, pd.sn_descripcion_larga as longdesc, pd.status as statusItem, pd.renglon as renglon, sm.mbflag as mbflag, intostocklocation AS almacen, IFNULL(tb_solicitudes_almacen_detalle.nu_cantidad, 0) AS cantidadSolAlmacen, pd.quantityord - IFNULL(tb_solicitudes_almacen_detalle.nu_cantidad, 0) AS qtyDiferencia, locstock.ontransit as ontransit, locstock.quantity as stock, (locstock.quantity - locstock.ontransit) AS qtyDisponible
            FROM purchorders p
            JOIN purchorderdetails pd ON (p.orderno = pd.orderno)
            JOIN stockmaster sm ON ( pd.itemcode = sm.stockid) 
            JOIN locstock ON (locstock.loccode = p.intostocklocation and locstock.stockid = sm.stockid)
            LEFT JOIN tb_solicitudes_almacen ON (p.orderno= tb_solicitudes_almacen.nu_id_requisicion)
            LEFT JOIN tb_solicitudes_almacen_detalle ON (tb_solicitudes_almacen.nu_folio= tb_solicitudes_almacen_detalle.nu_id_solicitud AND tb_solicitudes_almacen_detalle.ln_clave_articulo= pd.itemcode AND ln_arctivo = 1 AND ln_renglon != 0)
            WHERE p.orderno = $req AND pd.status = 2 ";

    $ErrMsg = "No se obtuvo informacion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    while ($myrow = DB_fetch_array($TransResult)) {
        $infopre = fnInfoPresupuesto($db, $myrow ['clavepre'], $periodo);
        $pptoActal = $infopre[0][$nombreMes];
        $tagref = $myrow['tagref'];
        $mbflag = $myrow['mbflag'];
        $itemcode = $myrow['itemcode'];
        $precio = $myrow ['precio'];
        $cantidadReq = $myrow ['cantidadReq'];
        $newQtyReq = $myrow ['cantidadSolAlmacen'] + $myrow ['qtyDiferencia'];
        $qtyDiferencia= ($myrow ['qtyDiferencia'] == 0) ? $myrow ['cantidadSolAlmacen'] : $newQtyReq;  
        $tot = $precio * $cantidad;

        $disponiblepartida[$infopre[0]["partida_esp"]]+= $tot;

        if (empty($infopre[0][$nombreMes])) {
            $SQLPrecomprometidos="SELECT * FROM chartdetailsbudgetlog WHERE tagref = '$ur' AND period = '$periodo' AND nu_tipo_movimiento = 258";
            $ErrMsgPrecomprometidos = "No se obtuvo informacion";
            $TransResultPrecomprometidos = DB_query($SQLPrecomprometidos, $db, $ErrMsgPrecomprometidos);
            if (DB_num_rows($TransResultPrecomprometidos) > 0) {
                $mensajeValidacion= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;La clave presupuestal '.$cvepre.' en el renglón '.$partida_producto.' no tiene recurso disponible en el mes en curso.</p>';
            } else {
                $mensajeValidacion = '<p><i class="glyphicon glyphicon-remove-sign text-success" aria-hidden="true"></i>&nbsp;&nbsp;El importe total en el renglón '.$myrow ['orderlineno_'].' cuenta con disponible suficiente en el mes en curso.</p>';
            }
        } else {
            if ($infopre[0][$nombreMes] >= $disponiblepartida[$infopre[0]["partida_esp"]]) {
                $mensajeValidacion = '<p><i class="glyphicon glyphicon-remove-sign text-success" aria-hidden="true"></i>&nbsp;&nbsp;El importe total en el renglón '.$myrow ['orderlineno_'].' cuenta con disponible suficiente en el mes en curso.</p>';
            } else {
                $mensajeValidacion= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El importe total en el renglón '.$myrow ['orderlineno_'].' excedió el disponible del mes en curso.</p>';
            }
        }
        
        $qtyDisp = $myrow ['qtyDisponible'] - $qtyDiferencia; 
        //$qtyStockDisp = $newQtyReq - $myrow ['qtyDisponible'];
        $qtyStockDisp = $myrow ['qtyDisponible'] - $myrow ['qtyDiferencia'];

        $info[] = array(
            'idrequi' => $myrow ['idReq'],
            'norequi' => $myrow ['noReq'],
            'dependencia' => $dependencia,
            'tagref' => $myrow ['tagref'],
            'ue' => $myrow ['ue'],
            'comments' => $myrow ['comments'],
            'longdesc' => $myrow ['longdesc'],
            'renglon' => $myrow ['renglon'],
            'fdelivery' => $myrow ['fdelivery'],
            'clavepre' => $myrow ['clavepre'],
            'idDetalleSolAlmacen' => $myrow ['idDetalleSolAlmacen'],
            'itemcode' => $myrow ['itemcode'],
            'itemdescription' => $myrow ['itemdescription'],
            'orderlineno_' => $myrow ['orderlineno_'],
            'precio' => $myrow ['precio'],
            'qtyReq' => $myrow ['cantidadReq'],
            'qtySolA' => $myrow ['cantidadSolAlmacen'],
            'qtyDiferencia' => $qtyDiferencia,
            'qtyDisponible' => $myrow ['qtyDisponible'],
            'tot' => ($myrow ['precio'] * $myrow ['cantidadReq']),
            'pptoActual' => $pptoActal,
            'qtyStock' => $myrow ['stock'],
            'qtyStockDisp' => $qtyStockDisp,
            'mbflag' => $mbflag,
            'almacen' => $myrow ['almacen'],
            'mensajeValidacion' => $mensajeValidacion
        );        
    }  
    $contenido = array('datos' => $info);
    $result = true;
}

if($option == 'replicarRequisicion'){
    $arrayDatosRequisicion = array();
    $idReq = $_POST['idrequi'];
    $noReq = $_POST['norequi'];
    $newNoRequi = GetNextTransNo(19, $db);
    $comments = $_POST['comments'];
    $tagref = $_POST['tagref'];
    $ue = $_POST['ue'];
    $fDelivery = date("Y-m-d", strtotime($_POST['fDelivery']));
    $status = "Capturado";
    $genNuevaRequisicion = "0";
    $datosRequisicion = $_POST['datosRequisicion'];
    $ordenElemento = 0;
    $usuarioReplicador = $_SESSION['UserID'];

    if(count($datosRequisicion) == 0 || $datosRequisicion == ''){
        $contenido = "Error no hay No Existencias" ;
        $result = false;

    }else{

        // inserta la requisicion replicada
        $SQL = "INSERT INTO purchorders 
            (
                supplierno,comments,rate,allowprint,initiator,requisitionno,intostocklocation,
                deladd1,deladd2,deladd3,deladd4,deladd5,deladd6,contact,version,realorderno,deliveryby,
                status,stat_comment,tagref,dateprinted,orddate,validfrom,validto,revised,deliverydate,lastUpdated,
                autorizafecha,fecha_modificacion,consignment,autorizausuario,capturausuario,solicitausuario,status_aurora,
                supplierorderno,currcode,wo,foliopurch,telephoneContact,refundpercentpurch,totalrefundpercentpurch,systypeorder,
                noag_ad,servicetype,clavepresupuestal,fileRequisicion,nu_ue
            ) VALUES
            (
                '111111','$comments',1,1,'$usuarioReplicador','$newNoRequi',4,'dir1','dir2','dir3','dir4','dir5','dir6','','0.00','','','$status',concat(curdate(),' - Order Creada ',curdate(),' - Replica: $usuarioReplicador'),'$tagref','1900-01-01 01:01:01',concat(curdate(),' ',TIME(NOW())),current_timestamp(),'$fDelivery','1900-01-01','$fDelivery',current_timestamp(),'1900-01-01',current_timestamp(),'0','$usuarioReplicador','$usuarioReplicador','$usuarioReplicador','','','MXN',0,'','',0,0,0,'',0,'',0,'$ue'
            )";
        $ErrMsg = "No se encontro un requisicion";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $genNuevaRequisicion = DB_Last_Insert_ID($db, 'purchorders', 'OrderNo');

        // CAmbia estatus de la requisición Original
        $SQLOriginal = "UPDATE purchorders SET status = 'Original' where orderno = '$idReq'";
        $ErrMsgOriginal = "No se encontro requisicion";
        $TransResultOriginal = DB_query($SQLOriginal, $db, $ErrMsgOriginal);

        //print_r( $datosRequisicion);
        
                $cadenaRequi='';

                for ($a=0; $a<count($datosRequisicion); $a++) {
                    $ordenElemento++;
                    $orden = $datosRequisicion[$a][0]['orden'];
                    $cvepre = $datosRequisicion[$a][0]['cvepre'];
                    $item = $datosRequisicion[$a][0]['item'];
                    $desc = $datosRequisicion[$a][0]['desc'];
                    $qty = $datosRequisicion[$a][0]['qty'];
                    $precio = $datosRequisicion[$a][0]['precio'];
                    $total_quantity = $qty * $precio;

                    $cadenaFinal ="'".$ordenElemento."','".$fDelivery."','1.1.5.1.1', 0, 0, 0, 0, 0, 0, '', 0, '', 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, current_timestamp(), 0, 0, 0, 0, 0,'', '', 0, '', '', '', '1900-01-01', '1900-01-01', current_timestamp(),'', 1, 1, 0, '', '', 2";
                    $cadenaRequi.="('".$genNuevaRequisicion."',".$cadenaFinal.",'".$cvepre."','".$item."','".$desc."','".$precio."',0.00,0.00,'".$qty."','".$total_quantity."')";
                    $cadenaRequi .= ",";
                }
                $cadenaRequi=substr($cadenaRequi, 0, -1);
                
                $SQLPD="INSERT INTO purchorderdetails (
                    orderno, 
                    orderlineno_,  
                    deliverydate, 
                    glcode,
                    actprice, 
                    stdcostunit, 
                    shiptref, 
                    jobref, 
                    completed, 
                    itemno, 
                    uom, 
                    subtotal_amount, 
                    package, 
                    pcunit, 
                    nw, 
                    suppliers_partno, 
                    gw, 
                    cuft, 
                    total_amount, 
                    discountpercent1, 
                    discountpercent2, 
                    discountpercent3, 
                    narrative, 
                    justification, 
                    refundpercent, 
                    lastUpdated, 
                    totalrefundpercent, 
                    estimated_cost, 
                    saleorderno_, 
                    wo, 
                    qtywo, 
                    womasterid, 
                    wocomponent, 
                    idgroup, 
                    typegroup, 
                    customs, 
                    pedimento, 
                    dateship, 
                    datecustoms, 
                    fecha_modificacion, 
                    inputport, 
                    factorconversion, 
                    invoice_rate, 
                    flagautoemision, 
                    sn_descripcion_larga, 
                    renglon, 
                    status, 
                    clavepresupuestal, itemcode, itemdescription, unitprice, quantityrecd, qtyinvoiced, quantityord, total_quantity) 
                    VALUES 
                        ".$cadenaRequi;
                        $ErrMsgPD = "No se pudo repicar la requisicion" . $noReq;
                        $TransResultPD = DB_query($SQLPD, $db, $ErrMsgPD);
            
            $contenido = array('datoIdReq' =>$genNuevaRequisicion, 'datoNoReq'=> $newNoRequi);
            $result = true;

       
    }
}

if($option == 'validarNoExistencia'){
    $idReq = $_POST['idrequi'];
    $SQL = "SELECT nu_tag, nu_ue, nu_id_requisicion, nu_id_no_existencia, status FROM tb_no_existencias WHERE nu_id_no_existencia != '' AND nu_id_requisicion = '$idReq' AND status = 1 ";
    $ErrMsg = "No se pudo repicar la requisicion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($row = DB_fetch_array($TransResult)) {
        $id_no_existencia = $row ['nu_id_no_existencia'];
    }
    $contenido = array('datos' => $id_no_existencia);
    $result = true;
}

if($option == 'validarSolAlmacen'){
    $idReq = $_POST['idrequi'];
    $SQL = "SELECT nu_id_solicitud ,nu_tag, nu_folio,ln_ue, nu_id_requisicion FROM tb_solicitudes_almacen WHERE nu_id_requisicion = '$idReq'";
    $ErrMsg = "No se pudo repicar la requisicion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($row = DB_fetch_array($TransResult)) {
        $folio = $row ['nu_folio'];
    }
    $contenido = array('datos' => $folio);
    $result = true;
}

if ($option == 'generarNoExistencia') {
    $arrayDatosNoExistvalores = array();
    $idReq = $_POST['idrequi'];
    $noReq = $_POST['norequi'];
    $norequi = GetNextTransNo(19, $db);
    $folioNoExistencia = GetNextTransNo(1004, $db);
    $comments = $_POST['comments'];
    $dependencia = $_POST['dependencia'];
    $tagref = $_POST['tagref'];
    $ue = $_POST['ue'];
    $fDelivery = date("Y-m-d", strtotime($_POST['fDelivery']));
    $generaNoExistencia = "0";
    $datosNoExistvalores = $_POST['noExistvalores'];
    $ordenElemento = 0;
    $usuarioNoExistencia = $_SESSION['UserID'];

    if (count($datosNoExistvalores) == 0 || $datosNoExistvalores == '') {
        $contenido = "Error no hay No Existencias" ;
        $result = false;
    } else {
        $SQLNE="INSERT INTO tb_no_existencias (nu_id_no_existencia, nu_id_requisicion, dtm_fecharegistro, nu_tag, nu_ue, ln_usuario, status, txt_observaciones, nu_dependencia) VALUES (".$folioNoExistencia.", ".$idReq.", current_timestamp(), '$tagref','$ue', '$usuarioNoExistencia', '1', '$comments','$dependencia')";
        $ErrMsgNE = "No se pudo repicar la requisicion";
        $TransResultNE = DB_query($SQLNE, $db, $ErrMsgNE);
        $generaNoExistencia = DB_Last_Insert_ID($db, 'tb_no_existencias', 'nu_id_no_existencia');
        $cadenaNoExist='';
        for ($a=1; $a<count($datosNoExistvalores); $a++) {
            $ordenElemento++;
            $orden = $datosNoExistvalores[$a][0]['orden'];
            $cvepre = $datosNoExistvalores[$a][0]['cvepre'];
            $item = $datosNoExistvalores[$a][0]['item'];
            $desc = $datosNoExistvalores[$a][0]['desc'];
            $qty = $datosNoExistvalores[$a][0]['qty'];

            $cadenaNoExist.= "('".$idReq."','".$generaNoExistencia."','PZA','',1,'".$orden."','".$cvepre."','".$item."','".$desc."','".$qty."')";
            $cadenaNoExist.= ",";
        }
        $cadenaNoExist=substr($cadenaNoExist, 0, -1);

        $SQLNED="INSERT INTO tb_no_existencia_detalle (nu_id_requisicion, nu_id_no_existencia, ln_unidad_medida, ln_cams, ln_activo, ln_renglon, ln_partida_esp, ln_item_code, txt_item_descripcion, nu_cantidad ) VALUES 
                ".$cadenaNoExist;
        $ErrMsgNED = "No se pudo repicar la requisicion";
        $TransResultNED = DB_query($SQLNED, $db, $ErrMsgNED);

        $contenido = array('datos' => $folioNoExistencia);
        $result = true;
    }
}

if($option == 'actualizarNoExistencia'){
    $idReq = $_POST['idrequi'];
    $folioNoE = $_POST['folioNoE'];
    $datosNoExistvalores = $_POST['noExistvalores'];
    $ordenElemento = 0;
    $info = array();
    $valoresTabla = array();
    $valoresBase = array();
    $valoresAgregar = array();
    $valoresEliminar = array();

    $SQLFindNoE = "SELECT tb_no_existencias.nu_id_no_existencia AS nu_id_no_existencia, ln_item_code FROM tb_no_existencias
    INNER JOIN tb_no_existencia_detalle on (tb_no_existencias.nu_id_no_existencia = tb_no_existencia_detalle.nu_id_no_existencia )
    WHERE tb_no_existencias.nu_id_requisicion = '$idReq' AND status = 1 AND ln_renglon > 0 AND ln_activo = 1";
    $ErrMsgFindNoE= "Error al actualizar la solicitud";
    $TransResultFindNoE = DB_query($SQLFindNoE, $db, $ErrMsgFindNoE);
    
    while ($myrow = DB_fetch_array($TransResultFindNoE)) {
        $info[] = array(
            'nu_id_no_existencia' => $myrow ['nu_id_no_existencia'],
            'itemcode' => $myrow ['ln_item_code']
        );
    }
    $maxOrdenNoE = count($info);
    for ($b=0; $b<count($info); $b++) {
        $itemcode = $info[$b]['itemcode'];
        if($itemcode != ''){
            array_push($valoresBase, $itemcode); 
        }
    }
    for ($a=0; $a<count($datosNoExistvalores); $a++) {
        $ordenNE = $datosNoExistvalores[$ordenElemento][0]['ordenNE'];
        $cvepreNE = $datosNoExistvalores[$ordenElemento][0]['cvepreNE'];
        $itemNE = $datosNoExistvalores[$ordenElemento][0]['itemNE'];
        $descNE = $datosNoExistvalores[$ordenElemento][0]['descNE'];
        $qtyNE = $datosNoExistvalores[$ordenElemento][0]['qtyNE'];
        
        if($itemNE != ''){
            array_push($valoresTabla, $itemNE);
            if($valoresBase != ''){
                if(in_array($itemNE, $valoresBase)) {
                    $SQL = "UPDATE tb_no_existencia_detalle SET nu_cantidad = '$qtyNE' WHERE nu_id_no_existencia = '$folioNoE' AND nu_id_requisicion = '$idReq' AND ln_item_code = '$itemNE' AND ln_activo = 1 AND nu_id_no_existencia_detalle != 0";
                    $ErrMsg = "Error al actualizar la no existencia";
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                    
                }else{
                    $valoresAgregar[] = array(
                        'item' =>  $itemNE,
                        'itemdesc' =>  $descNE,
                        'qty' => $qtyNE,
                        'cvepre' =>  $cvepreNE,
                        'orden' =>  $ordenNE
                    );
                }
            }
        }
        $ordenElemento++;
    }

    foreach($valoresBase as $elimina){ 
        if(in_array($elimina, $valoresTabla)) {
        }else{
            array_push($valoresEliminar, $elimina);
        }
    }
    fnEliminarItem($idReq, $folioNoE, $valoresEliminar, 'tb_no_existencia_detalle', 'nu_id_no_existencia', 'ln_item_code', 'ln_activo', 'nu_id_no_existencia_detalle', 'ln_renglon', 'nu_cantidad','', $db);
    fnAgregarItemNoExistencia($idReq, $folioNoE, $valoresAgregar, $maxOrdenNoE , $db);

    fnIndexarItem($idReq, $folioNoE, 'tb_no_existencia_detalle', 'ln_renglon', 'nu_id_no_existencia', 'ln_activo', 'nu_id_no_existencia_detalle', 'ln_item_code', $db);
    $enc = new Encryption;
    $url = "&idNoExistencia=> ". $folioNoE;
    $url = $enc->encode($url); 
    $liga= "URL=" . $url;
    $link ="<b>Se ha actualizado exitosamente la no existencia con el folio: </b><a target='_blank' href='./panel_no_existencias.php' style='color: blue; '><u>".$folioNoE."</u></a>";
    $contenido = $link;
    $result = true;
}

if ($option == 'generarSolAlmacen') {
    $arrayDatosRequisicion = array();
    $idReq = $_POST['idrequi'];
    $noReq = $_POST['norequi'];
    $comments = $_POST['comments'] ;
    $dependencia = $_POST['dependencia'];
    $tagref = $_POST['tagref'];
    $ue = $_POST['ue'];
    $fDelivery = date("Y-m-d", strtotime($_POST['fDelivery']));
    $statusNewRequi = 'Capturado';
    $usuarioSolAlmacen = $_SESSION ['UserID'];
    $generaSolAlmacen = "0";
    $datosSolAlmacen = $_POST['solAlmacen'];
    $almacen= $_POST['almacen'];
    $nombreEstatus = "Avanzada al autorizador";
    $status = 65;
    $ordenElemento = 0;
    $transno = GetNextTransNo(1000, $db);
    

    if (count($datosSolAlmacen) == 0 || $datosSolAlmacen == '' || $datosSolAlmacen == null) {
        $contenido = "Error no se puede hacer una solictud automatica" ;
        $result = false;
    } else {
        $SQL = "INSERT INTO tb_solicitudes_almacen (nu_tag,ln_usuario,estatus,nu_folio,txt_observaciones,ln_nombre_estatus,ln_ue,ln_tipo_solicitud, nu_id_requisicion, ln_almacen) VALUES ('". $tagref . "','".$_SESSION ['UserID']."','".$status."','".$transno."','".$comments."Solicitud generada para la requisición :".$noReq."','".$nombreEstatus."','".$ue."','Automática', ".$idReq.", '".$almacen."')";
        $ErrMsg = "No se encontro una requisicion";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $cadenaSolAlmacen='';

        for ($a=1; $a<count($datosSolAlmacen); $a++) {
            $ordenElemento++;
            $item = $datosSolAlmacen[$a][0]['item'];
            $desc = $datosSolAlmacen[$a][0]['desc'];
            $qty = $datosSolAlmacen[$a][0]['qty'];
            $precio = $datosSolAlmacen[$a][0]['precio'];
            $total_quantity = $qty * $precio;
            
            $cadenaSolAlmacen.= "('".$transno."','PZA','".$ordenElemento."',1,'".$item."','".$desc."','".$qty."')";
            $cadenaSolAlmacen.= ",";

            fnOnTranist($idReq, $item, $qty, $almacen, $db);
        }

        $cadenaSolAlmacen=substr($cadenaSolAlmacen, 0, -1);

        $SQLSA = "INSERT INTO tb_solicitudes_almacen_detalle (nu_id_solicitud,ln_unidad_medida,ln_renglon,ln_arctivo,ln_clave_articulo,txt_descripcion,nu_cantidad) VALUES ".$cadenaSolAlmacen;
        $ErrMsgSA = "Fallo la solicitud automatica";
        $TransResultSA = DB_query($SQLSA, $db, $ErrMsgSA);

        $contenido = array('datos' => $transno);
        $result = true;
    }
}

if($option == 'actualizarSolAlmacen'){
    $idReq = $_POST['idrequi'];
    $noReq = $_POST['norequi'];
    $folioSolA = $_POST['folioSolA'];
    $datosSolAlmacen = $_POST['solAlmacen'];
    $almacen= $_POST["almacen"];
    $ordenElemento = 0;
    $renglonElemento = 0;
    $info = array();
    $infoDisponible = array();
    $valoresTabla = array();
    $valoresBase = array();
    $valoresAgregar = array();
    $valoresEliminar = array();
    $valoresDatos = array();
    $diferencia = 0;
    $SQLFindSolA = "SELECT nu_id_detalle, nu_folio, ln_clave_articulo, nu_cantidad 
    FROM tb_solicitudes_almacen 
    INNER JOIN tb_solicitudes_almacen_detalle ON (tb_solicitudes_almacen.nu_folio = tb_solicitudes_almacen_detalle.nu_id_solicitud) 
    WHERE nu_folio = '".$folioSolA."' AND  nu_id_requisicion = '".$idReq."' AND ln_renglon > 0 AND ln_arctivo = 1";
    $ErrMsgFindSolA = "Error al actualizar la solicitud";
    $TransResultFindSolA = DB_query($SQLFindSolA, $db, $ErrMsgFindSolA);
    while ($myrow = DB_fetch_array($TransResultFindSolA)) {
        $info[] = array(
            'nu_id_detalle' => $myrow ['nu_id_detalle'],
            'nu_folio' => $myrow ['nu_folio'],
            'itemcode' => $myrow ['ln_clave_articulo'],
            'qtySolAlmacen' => $myrow ['nu_cantidad']
        );
    }
    $maxOrdenSolA = count($info);
    for ($b=0; $b<count($info); $b++) {
        $itemcode = $info[$b]['itemcode'];
        $qtySolAlmacen = $info[$b]['qtySolAlmacen'];
        if($itemcode != ''){
            array_push($valoresBase, $itemcode);
        }
    }
    for ($a=0; $a<count($datosSolAlmacen); $a++) {
        $renglonElemento++;
        $item = $datosSolAlmacen[$ordenElemento][0]['item'];
        $desc = $datosSolAlmacen[$ordenElemento][0]['desc'];
        $qty = $datosSolAlmacen[$ordenElemento][0]['qty'];
        $precio = $datosSolAlmacen[$ordenElemento][0]['precio'];
        if($item != ''){ 
            for ($b=0; $b<count($info); $b++) {
                $itemAlmacen = $info[$b]['itemcode'];
                if($itemAlmacen != ''){
                    if($item == $itemAlmacen){
                        $qtyAlmacen = $info[$b]['qtySolAlmacen'];
                    }
                }
            }
            if($qtyAlmacen == '' || $qtyAlmacen == null || $qtyAlmacen === 'undefined' ){
                $qtyAlmacen = 0;
            }
            $diferencia = $qty - $qtyAlmacen;
            $onTranistQty = $diferencia + $qtyAlmacen;
            if($onTranistQty > 0){
                fnOnTranist($idReq, $item, $onTranistQty, $almacen, $db);   
            }
            array_push($valoresTabla, $item);
            if(in_array($item, $valoresBase)) {
                $SQL = "UPDATE tb_solicitudes_almacen_detalle SET nu_cantidad = '$qty' WHERE nu_id_solicitud = '$folioSolA' AND ln_clave_articulo = '$item' AND ln_arctivo = 1 AND ln_renglon != 0 AND nu_id_detalle != 0 ";
                $ErrMsg = "Error al actualizar la solicitud";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
            }else{
                $valoresAgregar[] = array(
                    'item' =>  $item,
                    'itemdesc' =>  $desc,
                    'qty' => $qty,
                    'qtyOnTranist' => $onTranistQty,
                    'cvepre' =>  $cvepre
                );
            }
        }
        $ordenElemento++;
    }
    foreach($valoresBase as $elimina){ 
        if(in_array($elimina, $valoresTabla)) {
        }else{
            array_push($valoresEliminar, $elimina);
        }
    }   
    fnAgregarItemSolAlmacen($idReq, $folioSolA, $valoresAgregar, $maxOrdenSolA , $almacen, $db);
    fnEliminarItem($idReq, $folioSolA, $valoresEliminar, 'tb_solicitudes_almacen_detalle', 'nu_id_solicitud', 'ln_clave_articulo', 'ln_arctivo', 'nu_id_detalle','ln_renglon', 'nu_cantidad', $almacen, $db);
    fnIndexarItem($idReq, $folioSolA, 'tb_solicitudes_almacen_detalle', 'ln_renglon', 'nu_id_solicitud', 'ln_arctivo', 'nu_id_detalle', 'ln_clave_articulo', $db);
    $enc2 = new Encryption;
    $url2 = "&idNoExistencia=> ". $folioSolA;
    $url2 = $enc2->encode($url2); 
    $liga2= "URL=" . $url2;
    $link ="<b>Se ha actualizado exitosamente la solictud al almacén con folio: </b><a target='_blank' href='./almacen.php?' style='color: blue; '><u>".$folioSolA."</u></a>";
    $contenido = $link;
    $result = true;
}

function fnAgregarItemNoExistencia($idReq, $folioNoE, $arregloAgregar, $maxOrdenNoE, $db){
    $info = array();
    for ($i=0; $i < count($arregloAgregar); $i++) { 
        $maxOrdenNoE++;
        $item = $arregloAgregar[$i]['item'];
        $itemdesc = $arregloAgregar[$i]['itemdesc'];
        $qty = $arregloAgregar[$i]['qty'];
        $cvepre = $arregloAgregar[$i]['cvepre'];
        $orden = $arregloAgregar[$i]['cvepre'];

        $cadenaNoExist = "(".$idReq.",".$folioNoE.",'PZA',1,".$maxOrdenNoE.",'".$item."','".$itemdesc."',".$qty.",'".$cvepre."','".$cvepre."')";

        $SQLInsrt="INSERT INTO tb_no_existencia_detalle (nu_id_requisicion, nu_id_no_existencia, ln_unidad_medida, ln_activo, ln_renglon, ln_item_code, txt_item_descripcion, nu_cantidad, ln_partida_esp, clavepresupuestal ) VALUES ".$cadenaNoExist;
        $ErrMsgInsrt = "Error al agregar un item a la solicitud";
        $TransResultInsrt = DB_query($SQLInsrt, $db, $ErrMsgInsrt);
    }
}

function fnAgregarItemSolAlmacen($idReq, $folioSolA, $arregloAgregar, $maxOrdenSolA, $almacen, $db){
    $info = array();
    for ($i=0; $i < count($arregloAgregar); $i++) {
        $maxOrdenSolA++;
        $item = $arregloAgregar[$i]['item'];
        $itemdesc = $arregloAgregar[$i]['itemdesc'];
        $qty = $arregloAgregar[$i]['qty'];
        $qtyOnTrst = $arregloAgregar[$i]['qtyOnTranist'];
        $cvepre = $arregloAgregar[$i]['cvepre']; 

        $cadenaSolAlmacen = "(".$folioSolA.",'PZA',".$maxOrdenSolA.",1,'".$item."','".$itemdesc."',".$qty.")";
        //print_r($cadenaSolAlmacen);
        $SQLInsrt="INSERT INTO tb_solicitudes_almacen_detalle (nu_id_solicitud, ln_unidad_medida, ln_renglon, ln_arctivo, ln_clave_articulo, txt_descripcion, nu_cantidad) VALUES ".$cadenaSolAlmacen;
        $ErrMsgInsrt = "Error al agregar un item a la solicitud";
        $TransResultInsrt = DB_query($SQLInsrt, $db, $ErrMsgInsrt);

        fnOnTranist ($idReq, $item, $qty, $almacen, $db);
    }
}

function fnEliminarItem($idReq, $folio, $arregloEliminar, $tabla, $campoBusquedaFolio, $campoItem, $campoStatus, $campoIdTabla, $campoRenglon, $campoCantidad, $almacen, $db){
    foreach($arregloEliminar as $item){ 
        $SQL="UPDATE ".$tabla." SET ".$campoStatus." = 0, ".$campoRenglon." = 0 WHERE ".$campoBusquedaFolio." = '".$folio."' AND ".$campoItem." = '".$item."' AND ".$campoIdTabla." > 0  ";
        $ErrMsg = "Error al eliminar un item ";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if($almacen != ''){
            fnOnTranist ($idReq, $item, 0, $db, $almacen);
        }
    }
}

function fnIndexarItem($idReq, $folio, $tabla, $campoIndexar, $campoBusquedaFolio, $campoStatus, $campoIdTabla, $campoItem, $db){
    $indiceElemento = 0;
    $SQL="SELECT ".$campoItem." as campoitem FROM ".$tabla." WHERE  ".$campoBusquedaFolio." = ".$folio." AND ". $campoStatus ." = 1 AND ". $campoIndexar." > 0 ";
    $ErrMsg = "Error al eliminar un item a la solicitud";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($row = DB_fetch_array($TransResult)) {
        $indiceElemento++;
        $item = $row['campoitem'];
        $SQLUpdt="UPDATE ".$tabla." SET ".$campoIndexar." = ".$indiceElemento." WHERE ".$campoBusquedaFolio." = '".$folio."' AND ".$campoItem." = '".$item."' AND ".$campoStatus." = 1 AND ".$campoIdTabla." > 0 AND ".$campoIndexar." > 0";
        $ErrMsgUpdt = "Error al indexar un item";
        $TransResultUpdt = DB_query($SQLUpdt, $db, $ErrMsgUpdt);
    } 
}

// funcion que actualiza la cantidad en apartado de almacen
function fnOnTranist ($idRequ, $item, $qtyItem, $idalmacen, $db) {
    $info = array();
    $SQLOntransit = "UPDATE locstock SET ontransit = '".$qtyItem."' WHERE stockid = '".$item."' and loccode ='".$idalmacen."'";
    $ErrMsgOntransit = "No se obtuvo informacion";
    $TransResultOntransit = DB_query($SQLOntransit, $db, $ErrMsgOntransit);
}

function fnFeriadoOfin($ndias,$db){
    $fecha= date("Y-m-d" , strtotime(' +'.$ndias.' day'))." "."00:00:00";
    $SQL="  SELECT FinDeSemana,Feriado FROM  DWH_Tiempo where Fecha='".$fecha."' ORDER BY FinDeSemana DESC LIMIT 1 ";
    $ErrMsg = "No se obtuvo datos.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $feriado='';
    $fin='';
    while ($myrow = DB_fetch_array($TransResult)) {
                $feriado=$myrow['Feriado'];
                $fin=$myrow['FinDeSemana'];
    }
     if( ($feriado=='1') || ($fin=='1') ){
        $ndias=$ndias+1;
         return  (fnFeriadoOfin($ndias,$db));
    }else{
     $fecha=   date("d-m-Y" , strtotime(' +'.$ndias.' day'));
      return $fecha;

    }
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);

echo json_encode($dataObj);