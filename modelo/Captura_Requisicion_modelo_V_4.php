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
//////// 
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
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
/////$periodo = GetPeriod(date('d/m/Y'), $db);
$periodo = GetPeriod( ( isset($_SESSION['ejercicioFiscal'])&&$_SESSION['ejercicioFiscal']!=date('Y') ? date('d')."/12/$_SESSION[ejercicioFiscal]" : date('d/m/Y') ), $db);
$type = 19;

function fnObtenerIdentificadorPresupuesto($db, $clave)
{
    // Obtener informacion para identificador Inicio
    $cppt = "";
    $SQL = "SELECT 
    chartdetailsbudgetbytag.tagref,
    tb_cat_unidades_ejecutoras.ue as ue,
    chartdetailsbudgetbytag.cppt
    FROM chartdetailsbudgetbytag 
    JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ln_aux1 = chartdetailsbudgetbytag.ln_aux1
    WHERE chartdetailsbudgetbytag.accountcode = '".$clave."'
    ";
    $ErrMsg = "No se encontro el Identificar de la Clave ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $cppt = $myrow['tagref']."-".$myrow['ue']."-".$myrow['cppt'];
    }
    return $cppt;
    // Obtener informacion para identificador Fin
}
if ($option == 'getFechaServidor') {
    $info[] = array('fechaDMY'=>date("d-m-Y"),'fechaMdy'=>date("m-d-Y"));
    $contenido = array('Fecha'=> $info);
    $result = true;
}

if ($option == 'getFechaServidorSiguiente') {
    $ndias=$_POST['numerodias'];
    $fecha= fnFeriadoOfin($ndias, $db);
    $info[] = array('fechaDMY'=>$fecha);
    $contenido = array('Fecha'=> $info);
    $result = true;
}

if ($option == 'generarUrlEnc') {
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
                CONCAT(tags.tagref,' - ',tags.tagdescription) as ur,  
                CONCAT(tb_cat_unidades_ejecutoras.ue,' - ',tb_cat_unidades_ejecutoras.desc_ue) as unidadEjecutora,  
                purchorders.orderno as idr, purchorders.requisitionno as noReq,
                purchorders.validfrom as fechaCreacion, purchorders.deliverydate as fechadelivery, 
                purchorders.comments as comments, purchorders.nu_anexo_tecnico as anexoTec, purchorders.ln_codigo_expediente, purchorders.ln_UsoCFDI as selectCFDI
            FROM purchorders 
            JOIN tb_cat_unidades_ejecutoras on (purchorders.nu_ue = tb_cat_unidades_ejecutoras.ue and purchorders.tagref = tb_cat_unidades_ejecutoras.ur)
            JOIN tags on (purchorders.tagref = tags.tagref) 
            JOIN legalbusinessunit on (legalbusinessunit.legalid = tags.legalid )
            WHERE purchorders.orderno = '$req'";

    $ErrMsg = "No se encontro la Requisición: ".$req;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        // Obtener Anexo
        $nuAnexoTecnico = 0;
        $SQL = "SELECT nu_anexo FROM tb_cnfg_anexo_tecnico WHERE nu_requisicion = '".$req."' LIMIT 1";
        $ErrMsg = "No se encontro Anexo Técnico de ".$req;
        $TransResult2 = DB_query($SQL, $db, $ErrMsg);
        while ($myrow2 = DB_fetch_array($TransResult2)) {
            $nuAnexoTecnico = $myrow2['nu_anexo'];
        }
        $info[] = array(
            'rs' => $myrow ['rs'],
            'ur' => $myrow ['ur'],
            'fechaCreacion' => $myrow ['fechaCreacion'],
            'fechadelivery' => $myrow ['fechadelivery'],
            'comments' => $myrow ['comments'],
            'idr' => $myrow ['idr'],
            'noReq' => $myrow ['noReq'],
            'unidadEjecutora' => $myrow ['unidadEjecutora'],
            'anexoTec' => $myrow ['anexoTec'],
            'nuAnexoTecnico' => $nuAnexoTecnico,
            'codigoExpediente' => $myrow ['ln_codigo_expediente'],
            'selectCFDI' => $myrow ['selectCFDI']
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'obtenerPresupuesto') {
    $clave = $_POST['clave'];

    $period = GetPeriod(date('d/m/Y'), $db);
    ///// Se le asigna el valor de $periodo a la variable $period, ya que $periodo tenía la misma función que tiene $period en la línea anterior
    $period = $periodo;

    $nombreMes = "";
    $SQL = "SELECT periods.periodno, periods.lastdate_in_period, cat_Months.mes as mesName
            FROM periods 
            LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
            WHERE periods.periodno = '$period'
            ORDER BY periods.lastdate_in_period asc"; ///// ESTA LÍNEA SE REMOVIÓ DEL WHERE periods.lastdate_in_period like '%$myrow[anho]%' AND 

    $resultPeriods = DB_query($SQL, $db, $ErrMsg);
    while ($rowPeriods = DB_fetch_array($resultPeriods)) {
        $nombreMes = $rowPeriods ['mesName'];
    }

    $res = true;

    $info = fnInfoPresupuesto($db, $clave, $period);

    if (empty($info)) {
        $Mensaje = "No existe una clave presupuestal para esta partida";
        $res = false;
    }

    $contenido = array('datos' => $info, 'nombreMes' => $nombreMes);
    $result = $res;
}

if ($option == 'mostrarPartidaInstrumentales') {
    
}

if ($option == 'mostrarPartidaCvePpto') {
    $cvePA = $_POST['dato'];
    $tagref = $_POST['datotagref'];
    $ue = $_POST['datoue'];
    $info = array();
   
    $SQL= "SELECT DISTINCT chartdetailsbudgetbytag.accountcode as cvefrom 
    FROM chartdetailsbudgetbytag 
    JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ln_aux1 = chartdetailsbudgetbytag.ln_aux1
    WHERE 
    chartdetailsbudgetbytag.anho = '$_SESSION[ejercicioFiscal]'
    AND chartdetailsbudgetbytag.tagref='$tagref' 
    AND chartdetailsbudgetbytag.partida_esp= '$cvePA' 
    AND tb_cat_unidades_ejecutoras.ue = '$ue' "; ///// Se reemplaza '".date('Y')."' por '$_SESSION[ejercicioFiscal]'
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
if ($option == 'mostrarCppt') {
    $cvePA = $_POST['dato'];
    $tagref = $_POST['datotagref'];
    $ue = $_POST['datoue'];
    $cvePre = $_POST['clavepresupuestal'];
    $info = array();
   
    $SQL = "SELECT DISTINCT chartdetailsbudgetbytag.accountcode as cvefrom, 
    chartdetailsbudgetbytag.tagref, 
    chartdetailsbudgetbytag.cppt, 
    chartdetailsbudgetbytag.ln_aux1,
    tb_cat_unidades_ejecutoras.ue as ue, 
    concat(chartdetailsbudgetbytag.tagref,'-',tb_cat_unidades_ejecutoras.ue,'-',chartdetailsbudgetbytag.cppt) as diferenciador 
    FROM chartdetailsbudgetbytag 
    JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ln_aux1 = chartdetailsbudgetbytag.ln_aux1
    WHERE chartdetailsbudgetbytag.tagref='$tagref' 
    AND chartdetailsbudgetbytag.partida_esp= '$cvePA' 
    AND chartdetailsbudgetbytag.accountcode = '".$cvePre."' 
    AND tb_cat_unidades_ejecutoras.ue = '$ue' ";
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
if ($option == 'clavexarticulo'){
    $stockid=$_POST['cveprod'];
    $info = array();
    $SQL = "SELECT 
    stockmaster.description,
    stockmaster.units,
    stockcostsxlegal.lastcost
    FROM stockmaster 
    LEFT JOIN stockcostsxlegal ON stockmaster.stockid = stockcostsxlegal.stockid
    WHERE stockmaster.stockid = '".$stockid."' ";
    $ErrMsg = "No se encontro el Identificar de la Clave ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'descripcion' => $myrow['description'],
            'precio' => $myrow['lastcost'],
            'unidades' => $myrow['units'],
        );
    }
            
    $contenido = array('datos' => $info);
    $result = true;
}
if ($option == 'mostrar_estatus') {
    $idReq = $_POST['idReq'];
    $info = array();
    $SQL = "SELECT 
    purchorders.status
    FROM purchorders 
    WHERE purchorders.orderno = '".$idReq."' ";
    $ErrMsg = "No se encontro la requisicion ".$idReq;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'estatus' => $myrow['status']
        );
    }
    $contenido = array('datos' => $info);
    $result = true;

}



if ($option == 'mostrarPartidaCvePptoProdct') {
    $datoTagref = $_POST['datoTagref'];
    $datoUe = $_POST['datoUe'];
    $datoCvePartida = $_POST['datoCvePartida'];
    $info = array();
    
    
    $SQL = "SELECT stockmaster.stockid AS idProducto, 
    stockmaster.description AS descripcionProducto, 
    stockmaster.units AS unidad, 
    tb_partida_articulo.partidaEspecifica AS idPartidaEspecifica,
    tb_cat_partidaspresupuestales_partidaespecifica.descripcion AS descPartidaEspecifica,
    SUM(existencia.quantity) AS existencia
    FROM stockmaster
    INNER JOIN tb_partida_articulo on (stockmaster.eq_stockid = tb_partida_articulo.eq_stockid )
    LEFT JOIN  tb_cat_partidaspresupuestales_partidaespecifica on (tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = tb_partida_articulo.partidaEspecifica)
    LEFT JOIN (
        SELECT locstock.loccode, locstock.stockid, locstock.quantity
        FROM locstock
        INNER JOIN locations ON locations.loccode = locstock.loccode
        INNER JOIN sec_loccxusser 
        WHERE 
        locations.tagref = '".$datoTagref."' 
        AND locations.ln_ue = '".$datoUe."' 
        AND sec_loccxusser.userid = '".$_SESSION['UserID']."'
    ) existencia ON stockmaster.stockid= existencia.stockid
    WHERE tb_partida_articulo.partidaEspecifica = '".$datoCvePartida."' AND stockmaster.discontinued = 0
    GROUP BY stockmaster.stockid, 
    stockmaster.description , 
    stockmaster.units,
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
    $datoUe = $_POST['datoUe'];
    $cvepresupuestalArt = $_POST['cvepresupuestalArt'];
    $ordenacion= "idProducto";
    $info = array();

    $SQL= "SELECT distinct stockmaster.stockid AS idProducto, 
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
            LEFT JOIN (
            SELECT locstock.stockid, SUM(locstock.quantity) as quantity
            FROM locstock
            INNER JOIN locations ON locations.loccode = locstock.loccode
            INNER JOIN sec_loccxusser 
            WHERE 
            locations.tagref = '".$tagref."' 
            AND locations.ln_ue = '".$datoUe."' 
            AND sec_loccxusser.userid= '".$_SESSION['UserID']."'
            GROUP BY stockid
            ) existencia ON stockmaster.stockid= existencia.stockid
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

    $SQL= "SELECT DISTINCT stockmaster.stockid AS idPartidaEspecifica,
            CONCAT(stockmaster.eq_stockid, ' - ', stockmaster.description) AS descPartidaEspecifica,
            stockmaster.stockid AS idServicio,
            stockmaster.description AS descripcionServicio,
            IF(stockcostsxlegal.lastcost >= 0 ,stockcostsxlegal.lastcost,'0')  AS precioEstimado
            FROM tb_partida_articulo
            INNER JOIN stockmaster ON tb_partida_articulo.eq_stockid= stockmaster.eq_stockid
            LEFT JOIN stockcostsxlegal ON stockmaster.stockid =  stockcostsxlegal.stockid AND stockcostsxlegal.legalid IN(SELECT legalid FROM tags WHERE tagref='".$tagref."')
            WHERE  tb_partida_articulo.partidaEspecifica = '".$cvePA."' AND stockmaster.discontinued = 0
            AND stockmaster.mbflag = 'D'";

    $ErrMsg = "No se obtuvo el Servicio";
    $TransResult = DB_query($SQL, $db, $ErrMsg);


    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'idServicio' => $myrow ['idServicio'],
            'descripcionServicio' => $myrow ['descripcionServicio'],
            'precioEstimado' => 0, // $myrow ['precioEstimado'],
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
    purchorders.requisitionno as noRequisition
    FROM purchorders
    WHERE purchorders.orderno = '$idRequisicion'
    GROUP BY purchorders.orderno, purchorders.requisitionno ";

    $ErrMsg = "No se encontro ninguna Requisición ";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $SQL = "SELECT (MAX(purchorderdetails.orderlineno_ ) + 1) as orden, 
        purchorderdetails.deliverydate as fechaEnt
        FROM purchorderdetails
        WHERE 
        purchorderdetails.status in (0,1,2)
        AND purchorderdetails.orderno = '".$idRequisicion."'";
        $TransResult2 = DB_query($SQL, $db, $ErrMsg);
        $myrow2 = DB_fetch_array($TransResult2);
        
        if (empty($myrow2['orden'])) {
            $myrow2['orden'] = 1;
        }

        if (empty($myrow2['fechaEnt'])) {
            $myrow2['fechaEnt'] = date('d-m-Y');
        }

        $info[] = array(
            'orderno' => $myrow ['orderno'],
            'noRequisition' => $myrow ['noRequisition'],
            'orden' => $myrow2 ['orden'],
            'fechaEnt' => $myrow2 ['fechaEnt']);
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
    $codigoExpediente = $_POST['codigoExpediente'];
    $selectCFDI = $_POST['selectCFDI'];

    // consultar almacen configurado para el usuario
    $SQLLocstock = "SELECT defaultlocation FROM www_users where userid= '".$_SESSION['UserID']."'";
    $ErrMsgLocstock = "No se obtuvo informacion";
    $TransResultLocstock = DB_query($SQLLocstock, $db, $ErrMsgLocstock);

    while ($myrowLocstock = DB_fetch_array($TransResultLocstock)) {
        $idLocstock = $myrowLocstock['defaultlocation'];
    }

    // Validar si existe proveedor por dafult para operaciones
    $SQL = "SELECT supplierid FROM suppliers WHERE supplierid = '".$proveedor."'";
    $ErrMsgLocstock = "No se obtuvo informacion";
    $result = DB_query($SQL, $db, $ErrMsgLocstock);
    $existe = 0;
    while ($myrow2 = DB_fetch_array($result)) {
        $existe = 1;
    }
    if ($existe == 0) {
        // Si no existe proveedor agregarlo
        $SQL = "INSERT INTO `suppliers` (`supplierid`, `suppname`, `taxid`, `address1`, `address2`, `address3`, `address4`, `address5`, `address6`, `lat`, `lng`, `currcode`, `suppliersince`, `paymentterms`, `lastpaid`, `lastpaiddate`, `bankact`, `bankref`, `bankpartics`, `remittance`, `taxgroupid`, `factorcompanyid`, `taxref`, `phn`, `port`, `active`, `newcode`, `accion`, `typeid`, `narrative`, `limitcredit`, `supptaxname`, `idspecialty`, `email`, `distancia`, `nombre_movil`, `tipodetercero`, `flagagentaduanal`, `u_typediot`, `u_typeoperation`, `ln_tipoPersona`, `ln_curp`, `ln_representante_legal`, `nu_interior`, `nu_exterior`, `id_nu_entidad_federativa`, `id_nu_municipio`, `nu_tesofe`)
        VALUES
        ('".$proveedor."', 'PROVEEDOR GENERICO', 'XXXX010101XXX', '', '', '', '', '', '', 0.000000, 0.000000, 'MXN', '0000-00-00', '01', 121, '2017-12-29 00:00:00', '0123456789', 'referencia b', 'Banco de pru', 1, 1, 1, '', '', '', 1, '', '', 1, NULL, NULL, NULL, NULL, NULL, 0, '', '4', NULL, '4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0)";
        $result2 = DB_query($SQL, $db, $ErrMsgLocstock);
    }

    $info = array();
    $SQL = "INSERT INTO purchorders 
        (
            supplierno,comments,rate,allowprint,initiator,requisitionno,intostocklocation,
            deladd1,deladd2,deladd3,deladd4,deladd5,deladd6,contact,version,realorderno,deliveryby,
            status,stat_comment,tagref,dateprinted,orddate,validfrom,validto,revised,deliverydate,lastUpdated,
            autorizafecha,fecha_modificacion,consignment,autorizausuario,capturausuario,solicitausuario,status_aurora,
            supplierorderno,currcode,wo,foliopurch,telephoneContact,refundpercentpurch,totalrefundpercentpurch,systypeorder,
            noag_ad,servicetype,clavepresupuestal,fileRequisicion,nu_ue,ln_codigo_expediente, ln_UsoCFDI
        ) VALUES
        (
            '$proveedor','$obs',1,1,'$usuarioIniciador','0','".$idLocstock."', 'dir1','dir2','dir3','dir4','dir5','dir6','','0.00','','','$statusRequisicion',concat(curdate(),' - Order $statusRequisicion ',curdate(),' - Creada: $usuarioIniciador'),'$tagref','1900-01-01 01:01:01',concat('$fElaboracion',' ',TIME(NOW())),'$fElaboracion','$fentrega','1900-01-01','$fentrega',current_timestamp(),'1900-01-01',current_timestamp(),'0','$usuarioAutorizador','$usuarioIniciador','$usuarioIniciador','','','MXN',0,'$nuevaRequisicion','',0,0,'P','',0,'',0,'$ue','$codigoExpediente', '$selectCFDI'
        )";

    $ErrMsg = "No se agrego la nueva Requisición";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if ($TransResult == 1) {
        $nuevaRequisicion = DB_Last_Insert_ID($db, 'purchorders', 'OrderNo');


        if ($nuevaRequisicion != 0 && !empty($nuevaRequisicion)) {
            $SQL = "UPDATE purchorders set comments = '$obs' WHERE  orderno = '$nuevaRequisicion' ";
            $ErrMsg = "No se pudo modificar la Requisición";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            /* $info[] = array(
              'orderno' => $nuevaRequisicion,
              'requisitionno' => $requisitionno); */
            $info[] = array(
                'orderno' => $nuevaRequisicion);
            //$contenido = "Se actualizo la Requisición con el número: " + $nuevaRequisicion;
            $contenido = array('datos' => $info);
            $result = true;
        } else {
            $contenido = "Error";
            $result = false;
        }
    }
    else{
        $contenido = "Error";
        $result = false;
    }


    //$contenido = array('datos' => $info);
    //$result = true;
}

if ($option == 'agregarElementosRequisicion') {
    $numRequisicion = $_POST['idReq'];
    $deliverydate = date("Y-m-d", strtotime($_POST['fecEn']));
    

    $orderliNo = "";
    $SQL="SELECT orderlineno_ 
    FROM  purchorderdetails 
    WHERE orderno = '".$numRequisicion."' ORDER BY orderlineno_ DESC LIMIT 1";
    $ErrMsg = "No se pudo traer información de orderlino: ";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $orderliNo = $myrow['orderlineno_'];
    }

    $ordenElemento = $orderliNo + 1;
    

    $info = array();

    if ($ordenElemento == 0 || $ordenElemento =='' || $ordenElemento == null || $ordenElemento === 'undefined') {
        $ordenElemento = 1;
    }

    // Obtener informacion para identificador Inicio
    $cppt = fnObtenerIdentificadorPresupuesto($db, $cvePresupuestal);
    
    $SQL="INSERT INTO purchorderdetails (
    orderno, itemcode,deliverydate,itemdescription,glcode,qtyinvoiced,unitprice,actprice,stdcostunit,quantityord,quantityrecd,
    shiptref,jobref,completed,itemno,uom,subtotal_amount,package,pcunit,nw,suppliers_partno,gw,cuft,total_quantity,total_amount,
    discountpercent1,discountpercent2,discountpercent3,narrative,justification,refundpercent,lastUpdated,totalrefundpercent,
    estimated_cost,orderlineno_,saleorderno_,wo,qtywo,womasterid,wocomponent,idgroup,typegroup,customs,pedimento,
    dateship,datecustoms,fecha_modificacion,inputport,factorconversion,invoice_rate,flagautoemision,clavepresupuestal, sn_descripcion_larga, renglon, status, ln_clave_iden
    ) 
    VALUES 
    (
        '$numRequisicion','no_data','$deliverydate','descripcion','1.1.5.1.1','0','100','100','100','10','0',0,0,0,'','',0,'',0,0,'',0,0,'100','1000',0,0,0,'','',0,current_timestamp(),0,0,'$ordenElemento',0,0,0,'','',0,'','','','1900-01-01','1900-01-01',current_timestamp(),'',1,1,'0','$cvePresupuestal','descLarga','', 2, '$cppt'
    )";

    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    if($TransResult == 1){
        
        $info[] = array(
            'orderlineno' => $ordenElemento);
        $contenido = array('datos' => $info);
        $result = true;
    }else{
        $ErrMsg = "No se agrego el elemento a la Requisición: ".$numRequisicion;
        $result = false;
        
    }
    
    
}

// metodo para traer todos los datos de la requisicion
if ($option == 'mostrarRequisicion') {
    $requi = $_POST['requi'];
    //$o = $_POST['maxorden'];
    $info = array();

    $SQL = "SELECT 
    purchorders.orderno AS idRequisicion, 
    purchorders.status as statusReq,
    tb_partida_articulo.partidaEspecifica AS idPartida, 
    CONCAT(stockmaster.eq_stockid, ' - ', stockmaster.description) AS descPartida,
    -- tb_cat_partidaspresupuestales_partidaespecifica.descripcion AS descPartida,
    purchorderdetails.podetailitem as idItemDetalle,
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
    purchorderdetails.renglon AS renglon, purchorders.tagref,
    purchorderdetails.comments AS cm,
    purchorderdetails.ln_clave_iden AS cppt,
    IFNULL(tb_solicitudes_almacen_detalle.nu_cantidad,0) AS solAlmacenQty,
    IFNULL(tb_solicitudes_almacen_detalle.nu_cantidad,0) + purchorderdetails.quantityord AS realQty
    FROM purchorderdetails 
    INNER JOIN purchorders ON  purchorderdetails.orderno= purchorders.orderno
    JOIN stockmaster on (stockmaster.stockid = purchorderdetails.itemcode )
    JOIN tb_partida_articulo on (tb_partida_articulo.eq_stockid = stockmaster.eq_stockid)
    JOIN tb_cat_partidaspresupuestales_partidaespecifica on (tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = tb_partida_articulo.partidaEspecifica)
    LEFT JOIN (
    SELECT SUM(quantity - ontransit) AS existencia, stockid, locations.loccode, locations.tagref, locations.ln_ue
    FROM locstock 
    INNER JOIN locations ON locations.loccode = locstock.loccode
    INNER JOIN sec_loccxusser ON locstock.loccode= sec_loccxusser.loccode AND userid='desarrollo'
    GROUP BY stockid, loccode, tagref, ln_ue
    ) AS almacen ON stockmaster.stockid = almacen.stockid and purchorders.intostocklocation = almacen.loccode
    LEFT JOIN tb_solicitudes_almacen ON (purchorders.orderno= tb_solicitudes_almacen.nu_id_requisicion)
    LEFT JOIN tb_solicitudes_almacen_detalle ON (tb_solicitudes_almacen_detalle.ln_ontransit = purchorderdetails.podetailitem)
    WHERE purchorderdetails.orderno = '$requi' AND purchorderdetails.status ='2'
    ORDER BY orden";
    // cambios pruebas, se quita join ya que marca error
    // LEFT JOIN tb_solicitudes_almacen_detalle ON (tb_solicitudes_almacen.nu_folio= tb_solicitudes_almacen_detalle.nu_id_solicitud AND tb_solicitudes_almacen_detalle.ln_clave_articulo= purchorderdetails.itemcode AND tb_solicitudes_almacen_detalle.ln_arctivo = 1 AND tb_solicitudes_almacen_detalle.ln_renglon != 0)
    // Validar si es una requisicion separada
    $requisitionno = 0;
    $SQL2 = "SELECT requisitionno FROM purchorders WHERE orderno = '".$requi."'";
    $ErrMsg = "No se encontro la Requisición: ".$requi;
    $TransResult2 = DB_query($SQL2, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult2)) {
        $requisitionno = $myrow['requisitionno'];
    }
    $SQL3 = "SELECT requisitionno FROM purchorders WHERE requisitionno = '".$requisitionno."'";
    $ErrMsg = "No se encontro la Requisición: ".$requi;
    $TransResult = DB_query($SQL3, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 1) {
        // cambios pruebas, se quita join ya que marca error
        // LEFT JOIN tb_solicitudes_almacen_detalle ON (tb_solicitudes_almacen.nu_folio= tb_solicitudes_almacen_detalle.nu_id_solicitud AND tb_solicitudes_almacen_detalle.ln_clave_articulo= purchorderdetails.itemcode AND tb_solicitudes_almacen_detalle.ln_arctivo = 1 AND tb_solicitudes_almacen_detalle.ln_renglon != 0)
        $SQL = "SELECT 
        $requi AS idRequisicion, 
        purchorders.status as statusReq,
        tb_partida_articulo.partidaEspecifica AS idPartida, 
        CONCAT(stockmaster.eq_stockid, ' - ', stockmaster.description) AS descPartida,
        -- tb_cat_partidaspresupuestales_partidaespecifica.descripcion AS descPartida,
        purchorderdetails.podetailitem as idItemDetalle,
        purchorderdetails.itemcode AS idItem, 
        purchorderdetails.itemdescription AS descItem, 
        stockmaster.units AS unidad, 
        stockmaster.mbflag AS tipo,
        purchorderdetails.unitprice AS precio, 
        purchorderdetails.quantityord AS cantidad,
        purchorderdetails.total_quantity AS total,
        -- if(almacen.existencia = 0,'No Disponible','Disponible') AS existencia,
        almacen.existencia AS existencia,
        purchorderdetails.nu_original AS orden, 
        purchorderdetails.clavepresupuestal AS clavePresupuestal, 
        purchorderdetails.sn_descripcion_larga AS descLarga,
        purchorderdetails.renglon AS renglon, purchorders.tagref,
        purchorderdetails.comments AS cm,
        purchorderdetails.ln_clave_iden AS cppt,
        IFNULL(tb_solicitudes_almacen_detalle.nu_cantidad,0) AS solAlmacenQty,
        IFNULL(tb_solicitudes_almacen_detalle.nu_cantidad,0) + purchorderdetails.quantityord AS realQty
        FROM purchorderdetails 
        INNER JOIN purchorders ON  purchorderdetails.orderno= purchorders.orderno
        JOIN stockmaster on (stockmaster.stockid = purchorderdetails.itemcode )
        JOIN tb_partida_articulo on (tb_partida_articulo.eq_stockid = stockmaster.eq_stockid)
        JOIN tb_cat_partidaspresupuestales_partidaespecifica on (tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada = tb_partida_articulo.partidaEspecifica)
        LEFT JOIN (
        SELECT SUM(quantity - ontransit) AS existencia, stockid, locations.loccode, locations.tagref, locations.ln_ue
        FROM locstock 
        INNER JOIN locations ON locations.loccode = locstock.loccode
        INNER JOIN sec_loccxusser ON locstock.loccode= sec_loccxusser.loccode AND userid='desarrollo'
        GROUP BY stockid, loccode, tagref, ln_ue
        ) AS almacen ON stockmaster.stockid = almacen.stockid and purchorders.intostocklocation = almacen.loccode
        LEFT JOIN tb_solicitudes_almacen ON (purchorders.orderno= tb_solicitudes_almacen.nu_id_requisicion)
        LEFT JOIN tb_solicitudes_almacen_detalle ON tb_solicitudes_almacen_detalle.ln_ontransit = purchorderdetails.podetailitem
        WHERE purchorders.requisitionno = '$requisitionno' AND purchorderdetails.status ='2'
        ORDER BY orden";
    }

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
            'statusReq' => $myrow ['statusReq'],
            'idPartida' => $myrow ['idPartida'],
            'descPartida' => $myrow ['descPartida'],
            'idItemDetalle' => $myrow ['idItemDetalle'],
            'idItem' => $myrow ['idItem'],
            'descItem' => $myrow ['descItem'],
            'cm' => $myrow ['cm'],
            'cppt' => $myrow ['cppt'],
            'unidad' => $myrow ['unidad'],
            'tipo' => $myrow ['tipo'],
            'precio' => number_format($myrow ['precio'], $_SESSION['DecimalPlaces'], '.', ''),
            'cantidad' => $myrow ['cantidad'],
            'cantidadReal' => $myrow ['realQty'],
            'cantidadSolALmacen' => $myrow ['solAlmacenQty'],
            'total' => $myrow ['total'],
            'existencia' => $myrow ['existencia'],
            'orden' => $myrow ['orden'],
            'clavePresupuestal' => $myrow ['clavePresupuestal'],
            'descLarga' => $myrow ['descLarga'],
            'renglon' => $myrow ['renglon']);
    }
    $contenido = array('datos' => $info);

    $result = true;
}

if ($option == 'modificarElementosRequisicion') {

    $datosArticulo = $_POST['datosCompraArt'];
    $datosServicio = $_POST['datosCompraServ'];
        foreach ($datosArticulo as $valor) {
            
            $req = $valor['req'];
            echo "valor". $req ;
            $itemcode = $valor['itemcode'];
            $fechent = $valor['fechent'];
            $itemdesc = $valor['itemdesc'];
            $price = empty($valor['price']) ? 0 : $valor['price'];
            $cantidad = empty($valor['cantidad']) ? 0 : $valor['cantidad'];
            $order = empty($valor['order']) ? 0 : $valor['order'];
            $cvepre = $valor['cvepre'];
            $almacen = $valor['almacen'];
            $comments = $_POST['comments'];
            $longText = $_POST['longText'];
            $renglon = $valor['renglon'];
            $cppt = $valor['cppt'];
            $cm = $valor['cm'];
            $info = array();
            $total_quantity = $cantidad;
            $total_amount = ($total_quantity * $price);
        
            if ($order == 0 || $order == '' || $order === 'undefined') {
                $order = 1;
            }
        
            $SQLModAnexo="UPDATE tb_cnfg_anexo_tecnico SET ind_status = 3, nu_orden_requisicion = '$order'  WHERE nu_requisicion = '$req' AND nu_orden_requisicion = '$order'";
            $ErrMsgModAnexo = "No se pudo limpiar el anexo técnico de la Requisición: ".$req;
            $TransResultModAnexo = DB_query($SQLModAnexo, $db, $ErrMsgModAnexo);
        
            // Obtener informacion para identificador Inicio
            $cppt = fnObtenerIdentificadorPresupuesto($db, $cvepre);
        
            $SQL="UPDATE purchorderdetails 
                SET comments = '$cm', itemcode = '$itemcode', itemdescription= '$itemdesc', unitprice = '$price',actprice = '$price', stdcostunit = '$price', quantityord ='$cantidad', itemno = '$itemcode', total_quantity = '$total_quantity', total_amount ='$total_amount', fecha_modificacion = current_timestamp(), clavepresupuestal = '$cvepre', status = 1, sn_descripcion_larga = '$longText', renglon = '$renglon', ln_clave_iden = '$cppt'
                WHERE orderno = '$req' AND orderlineno_ = '$order' and status = 0 ";
            $ErrMsg = "No se pudo modificar la Requisición: ".$req;
            $TransResult = DB_query($SQL, $db, $ErrMsg);
        
            $SQLMod="UPDATE purchorderdetails 
                SET  comments = '$cm', itemcode = '$itemcode', itemdescription= '$itemdesc', unitprice = '$price',actprice = '$price',
                stdcostunit = '$price',quantityord ='$cantidad', itemno = '$itemcode', total_quantity = '$total_quantity', total_amount ='$total_amount', fecha_modificacion = current_timestamp(), clavepresupuestal = '$cvepre', sn_descripcion_larga = '$longText', renglon = '$renglon', ln_clave_iden = '$cppt'
                WHERE orderno = '$req' AND orderlineno_ = '$order' and status = 2";
        
            $ErrMsgMod = "No se pudo modificar la Requisición: ".$req;
            $TransResultMod = DB_query($SQLMod, $db, $ErrMsgMod);
               
        }
        foreach ($datosServicio as $valor) {
        $req = $valor['req'];
        $itemcode = $valor['itemcode'];
        $fechent = $valor['fechent'];
        $itemdesc = $valor['itemdesc'];
        $price = empty($valor['price']) ? 0 : $valor['price'];
        $cantidad = empty($valor['cantidad']) ? 0 : $valor['cantidad'];
        $order = empty($valor['order']) ? 0 : $valor['order'];
        $cvepre = $valor['cvepre'];
        $almacen = $valor['almacen'];
        $comments = $_POST['comments'];
        $longText = $valor['longText'];
        $renglon = $valor['renglon'];
        $cppt = $valor['cppt'];
        $cm = $_POST['cm'];
        $info = array();
        $total_quantity = $cantidad;
        $total_amount = ($total_quantity * $price);

        if ($order == 0 || $order == '' || $order === 'undefined') {
            $order = 1;
        }

        $SQLModAnexo="UPDATE tb_cnfg_anexo_tecnico SET ind_status = 3, nu_orden_requisicion = '$order'  WHERE nu_requisicion = '$req' AND nu_orden_requisicion = '$order'";
        $ErrMsgModAnexo = "No se pudo limpiar el anexo técnico de la Requisición: ".$req;
        $TransResultModAnexo = DB_query($SQLModAnexo, $db, $ErrMsgModAnexo);

        // Obtener informacion para identificador Inicio
        $cppt = fnObtenerIdentificadorPresupuesto($db, $cvepre);

        $SQL="UPDATE purchorderdetails 
            SET comments = '$cm', itemcode = '$itemcode', itemdescription= '$itemdesc', unitprice = '$price',actprice = '$price', stdcostunit = '$price', quantityord ='$cantidad', itemno = '$itemcode', total_quantity = '$total_quantity', total_amount ='$total_amount', fecha_modificacion = current_timestamp(), clavepresupuestal = '$cvepre', status = 1, sn_descripcion_larga = '$longText', renglon = '$renglon', ln_clave_iden = '$cppt'
            WHERE orderno = '$req' AND orderlineno_ = '$order' and status = 0 ";
        $ErrMsg = "No se pudo modificar la Requisición: ".$req;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $SQLMod="UPDATE purchorderdetails 
            SET  comments = '$cm', itemcode = '$itemcode', itemdescription= '$itemdesc', unitprice = '$price',actprice = '$price',
            stdcostunit = '$price',quantityord ='$cantidad', itemno = '$itemcode', total_quantity = '$total_quantity', total_amount ='$total_amount', fecha_modificacion = current_timestamp(), clavepresupuestal = '$cvepre', sn_descripcion_larga = '$longText', renglon = '$renglon', ln_clave_iden = '$cppt'
            WHERE orderno = '$req' AND orderlineno_ = '$order' and status = 2";

        $ErrMsgMod = "No se pudo modificar la Requisición: ".$req;
        $TransResultMod = DB_query($SQLMod, $db, $ErrMsgMod);
    }


    $contenido = "Se Modifico la Requisición: ".$req;

    $result = true;
}

if($option == 'duplicarElementosRequisicion'){
    $result = false;
    $idreq = $_POST['idReq'];
    $noreq = $_POST['noReq'];
    $orden = $_POST['orden'];
    $info = array();

    $orderliNo = "";
    $SQL="SELECT orderlineno_ 
    FROM  purchorderdetails 
    WHERE orderno = '".$idreq."' ORDER BY orderlineno_ DESC LIMIT 1";
    $ErrMsg = "No se pudo traer información de orderlino: ";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $orderliNo = $myrow['orderlineno_'];
    }

    $SQL="SELECT 
    podetailitem,
    orderno,
    itemcode,
    deliverydate,
    itemdescription,
    glcode,
    qtyinvoiced,
    unitprice,
    actprice,
    stdcostunit,
    quantityord,
    quantityrecd,
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
    total_quantity,
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
    orderlineno_,
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
    clavepresupuestal,
    sn_descripcion_larga,
    renglon,
    status,
    ln_clave_iden,
    nu_original,
    comments
    FROM purchorderdetails 
    WHERE orderno = '".$idreq."' AND orderlineno_ = '".$orden."'";
    $ErrMsg = "No se pudo traer información: ";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
    $orderliNo =  $orderliNo+1;
    $SQL2="INSERT INTO `purchorderdetails` (`orderno`, `itemcode`, `deliverydate`, `itemdescription`, `glcode`, `qtyinvoiced`, `unitprice`, `actprice`, `stdcostunit`, `quantityord`, `quantityrecd`, `shiptref`, `jobref`, `completed`, `itemno`, `uom`, `subtotal_amount`, `package`, `pcunit`, `nw`, `suppliers_partno`, `gw`, `cuft`, `total_quantity`, `total_amount`, `discountpercent1`, `discountpercent2`, `discountpercent3`, `narrative`, `justification`, `refundpercent`, `lastUpdated`, `totalrefundpercent`, `estimated_cost`, `orderlineno_`, `saleorderno_`, `wo`, `qtywo`, `womasterid`, `wocomponent`, `idgroup`, `typegroup`, `customs`, `pedimento`, `dateship`, `datecustoms`, `fecha_modificacion`, `inputport`, `factorconversion`, `invoice_rate`, `flagautoemision`, `clavepresupuestal`, `sn_descripcion_larga`, `renglon`, `status`, `ln_clave_iden`, `nu_original`, `comments`)
          VALUES( 
            '". $myrow['orderno'] . "', 
            '". $myrow['itemcode'] . "', 
            '". $myrow['deliverydate'] . "', 
            '". $myrow['itemdescription'] . "', 
            '". $myrow['glcode'] . "',
            '". $myrow['qtyinvoiced'] . "', 
            '". $myrow['unitprice'] . "',
            '". $myrow['actprice'] . "',
            '". $myrow['stdcostunit'] . "',
            '". $myrow['quantityord'] . "',
            '". $myrow['quantityrecd'] . "',
            '". $myrow['shiptref'] . "',
            '". $myrow['jobref'] . "',
            '". $myrow['completed'] . "',
            '". $myrow['itemno'] . "', 
            '". $myrow['uom'] . "',
            '". $myrow['subtotal_amount'] . "',
            '". $myrow['package'] . "',
            '". $myrow['pcunit'] . "',
            '". $myrow['nw'] . "',
            '". $myrow['suppliers_partno'] . "', 
            '". $myrow['gw'] . "', 
            '". $myrow['cuft'] . "',
            '". $myrow['total_quantity'] . "',
            '". $myrow['total_amount'] . "', 
            '". $myrow['discountpercent1'] . "', 
            '". $myrow['discountpercent2'] . "', 
            '". $myrow['discountpercent3'] . "', 
            '". $myrow['narrative'] . "', 
            '". $myrow['justification'] . "', 
            '". $myrow['refundpercent'] . "', 
            '". $myrow['lastUpdated'] . "', 
            '". $myrow['totalrefundpercent'] . "', 
            '". $myrow['estimated_cost'] . "', 
            '". $orderliNo . "', 
            '". $myrow['saleorderno_'] . "', 
            '". $myrow['wo'] . "', 
            '". $myrow['qtywo'] . "', 
            '". $myrow['womasterid'] . "', 
            '". $myrow['wocomponent'] . "', 
            '". $myrow['idgroup'] . "',
            '". $myrow['typegroup'] . "', 
            '". $myrow['customs'] . "',
            '". $myrow['pedimento'] . "', 
            '". $myrow['dateship'] . "', 
            '". $myrow['datecustoms'] . "', 
            '". $myrow['fecha_modificacion'] . "', 
            '". $myrow['inputport'] . "', 
            '". $myrow['factorconversion'] . "', 
            '". $myrow['invoice_rate'] . "', 
            '". $myrow['flagautoemision'] . "', 
            '". $myrow['clavepresupuestal'] . "', 
            '". $myrow['sn_descripcion_larga'] . "', 
            '". $myrow['renglon'] . "', 
            '". $myrow['status'] . "', 
            '". $myrow['ln_clave_iden'] . "', 
            '". $myrow['nu_original'] . "', 
            '". $myrow['comments'] . "')";
    $TransResult2 = DB_query($SQL2, $db, $ErrMsg);
    }
    $result = true;
    $contenido = "Se duplico el elemento a la Requisición: ".$idreq;  
}

if ($option == 'eliminarElementosRequisicion') {
    $result = false;
    $idreq = $_POST['idReq'];
    $noreq = $_POST['noReq'];
    $orden = $_POST['orden'];
    $info = array();

    $SQLDelAnexo = "UPDATE tb_cnfg_anexo_tecnico set ind_status = 3, nu_orden_requisicion = 0 WHERE nu_requisicion = '$idreq' AND nu_orden_requisicion = '$orden'";
    $ErrMsgDelAnexo = "Problemas al quitar la asignacion del elemento del anexo";
    $TransResultDelAnexo = DB_query($SQLDelAnexo, $db, $ErrMsgDelAnexo);

    if ($noreq == '' || $noreq == 0 || $noreq === 'undefined' || $noreq == null) {
        $SQLDELTrash = "DELETE FROM purchorderdetails WHERE orderno = '$idreq' AND orderlineno_ = '$orden'";
        $ErrMsgDELTrash = "No se elimino el elemento a la Requisición";
        $TransResultDELTrash = DB_query($SQLDELTrash, $db, $ErrMsgDELTrash);
        if($TransResultDELTrash == 1){
            $result = true;
            $contenido = "Se elimino el elemento a la Requisición: ".$nreq;
        }
    } else {
        $SQL = "DELETE FROM purchorderdetails WHERE orderno = '$idreq' AND orderlineno_ = '$orden'";
        $ErrMsg = "No se elimino el elemento a la Requisición: ".$idreq;
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if($TransResult == 1){
            $result = true;
            $contenido = "Se actualizo el elemento a la Requisición: ".$nreq;
        }
    }
    
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
    $error = 0;

    $status = "";
    $SQL = "SELECT 
    purchorders.status,
    tb_botones_status.statusname 
    FROM purchorders 
    JOIN tb_botones_status ON purchorders.status = tb_botones_status.statusname
    WHERE orderno = '$req' AND  tb_botones_status.sn_funcion_id = '2265'";
    $ErrMsg = "No se encontro un status";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $status = $myrow['statusname'];
    }
   
    if($status == "Cancelado" || $status == "Autorizado"){
        $contenido = "No se pueden modificar requisiciones autorizadas o canceladas.";
        $result = false;
        $error = 1;
    }

    $status = $_POST['status'];
    $comments = $_POST['comments'];
    $fechaFrom = date("Y-m-d", strtotime($_POST['fechaFrom']));
    $fechaTo = date("Y-m-d", strtotime($_POST['fechaTo']));
    $tagref= $_POST['tagref'];
    $ue= $_POST['ue'];
    $anexoTec = $_POST['anexoTec'];
    $requisitionno = GetNextTransNo(19, $db);
    $info = array();
   

    if ($anexoTec == 1) {
        $SQLFindAnexo = "SELECT renglon FROM purchorderdetails where orderno = '$req' and renglon <> ''";
        $ErrMsgFindAnexo = "Error al buscar los renglones del anexo tecnico asignados";
        $TransResultFindAnexo = DB_query($SQLFindAnexo, $db, $ErrMsgFindAnexo);
        if (DB_num_rows($TransResultFindAnexo) < 1) {
            $contenido = "La requisición no esta vinculada a un Anexo Técnico.";
            $error = 1;
        }
    }
    if ($error > 0) {
        $contenido = $contenido;
        $result = false;
    } else {
        $SQL = "UPDATE purchorders 
                SET status = '$status', requisitionno = '$requisitionno', comments = '$comments',
                validfrom = '$fechaFrom', validto = '$fechaTo', deliverydate = '$fechaTo', 
                fecha_modificacion = current_timestamp(), tagref= '$tagref', nu_ue= '$ue', nu_anexo_tecnico = '$anexoTec'
                WHERE orderno  = '$req'";

        $ErrMsg = "No se obtuvieron los botones para el proceso";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        
        if($TransResult == 1){
            $SQLDEL = "UPDATE purchorderdetails SET fecha_modificacion = current_timestamp(), orderlineno_ = 0 WHERE orderno  = '$req' and status = 3";
            $ErrMsgDEL = "No se pudo eliminar";
            $TransResultDEL = DB_query($SQLDEL, $db, $ErrMsgDEL);
            
            if($TransResultDEL == 1){
                $SQLPO = "UPDATE purchorderdetails SET fecha_modificacion = current_timestamp(), status = 2 WHERE orderno  = '$req' and status = 1";
                $ErrMsgPO = "No se obtuvieron los botones para el proceso";
                $TransResultPO = DB_query($SQLPO, $db, $ErrMsgPO);
            }else{
                $contenido = "No se pudo actualizar el renglón de la requisición";
                $result = false;
            }
            
            if($TransResultPO == 1){
                $contenido = $requisitionno;
                $result = true;
            }else{
                $contenido = "No se pudo actualizar el estatus de la requisición";
                $result = false;
            }
            
        }else{
            $contenido = "No se pudo guardar la requisición";
            $result = false;
        }

        
    }
}

if ($option == 'guardarRequisicion') {
    $req = $_POST['idReq'];
    $noReq = $_POST['noReq'];
    $status = $_POST['status'];
    $comments = $_POST['comments'];
    $codigoExpediente = $_POST['codigoExpediente'];
    $selectCFDI = $_POST['selectCFDI'];
    $fechaFrom = date("Y-m-d", strtotime($_POST['fechaFrom']));
    $fechaTo = date("Y-m-d", strtotime($_POST['fechaTo']));
    $tagref= $_POST['tagref'];
    $ue= $_POST['ue'];
    $anexoTec = $_POST['anexoTec'];
    $info = array();
    $error = 0;

    $estatus = "";
    $statusPurch = "";
    $SQL = "SELECT 
    purchorders.status,
    tb_botones_status.statusname 
    FROM purchorders 
    JOIN tb_botones_status ON purchorders.status = tb_botones_status.statusname
    WHERE orderno = '$req' AND  tb_botones_status.sn_funcion_id = '2265'";
    $ErrMsg = "No se encontro un status";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $estatus = $myrow['statusname'];
        $statusPurch = $myrow['status'];
    }
   
    $SQLDEL= "UPDATE purchorderdetails SET fecha_modificacion = current_timestamp(), orderlineno_ = 0, status = 0 WHERE orderno  = '$req' and status = 3";
    $ErrMsgDEL = "No se pudo eliminar";
    $TransResultDEL = DB_query($SQLDEL, $db, $ErrMsgDEL);

    if ($estatus == 'Original' || $estatus == "Cancelado" || $estatus == "Autorizado") {
        $contenido = "No se pueden modificar una requisición con estatus ".$estatus;
        $result = false;
        $error = 1;
    } else {
        if ($anexoTec == 1) {
            $SQLFindAnexo = "SELECT renglon FROM purchorderdetails where orderno = '$req' and renglon <> ''";
            $ErrMsgFindAnexo = "Error al buscar los renglones del anexo tecnico asignados";
            $TransResultFindAnexo = DB_query($SQLFindAnexo, $db, $ErrMsgFindAnexo);
            if (DB_num_rows($TransResultFindAnexo) < 1) {
                $contenido = "La requisición no esta vinculada a un Anexo Técnico.";
                $error = 1;
            }
        }
        if ($error > 0) {
            $contenido = $contenido;
            $result = false;
        } else {
            $SQL= "UPDATE purchorders SET status = '$statusPurch', comments = '$comments', validfrom = '$fechaFrom', validto = '$fechaTo', deliverydate = '$fechaTo', fecha_modificacion = current_timestamp(), tagref= '$tagref', nu_ue= '$ue', nu_anexo_tecnico = '$anexoTec', ln_codigo_expediente = '$codigoExpediente', ln_UsoCFDI = '$selectCFDI'
            WHERE orderno  = '$req'";
            $ErrMsg = "No se actualizó información de la requisición";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            
            if($TransResult == 1){
                $SQLPO= "UPDATE purchorderdetails SET fecha_modificacion = current_timestamp(), status = 2 WHERE orderno  = '$req' and status = 1 and orderlineno_ > 0 ";
                $ErrMsgPO = "Error al guardar detalles";
                $TransResultPO = DB_query($SQLPO, $db, $ErrMsgPO);
            }else{
                $contenido = "No se actualizó información de la requisición";
                $result = false;
            }
            
            if($TransResultPO == 1){
                // Actualizar descripción de la no existencia
                $SQL = "UPDATE tb_no_existencias SET txt_observaciones = '".$comments."' WHERE nu_id_requisicion = '".$noReq."'";
                $ErrMsg = "No se actualizó información de la no existencia";
                $TransResultDos = DB_query($SQL, $db, $ErrMsg);
            }else{
                $contenido = "Error al guardar detalles de la requisición";
                $result = false;
            }
            
            if($TransResultDos == 1){
                // Actualizar descripción de la solicitud al almacén
                $SQL = "UPDATE tb_solicitudes_almacen SET txt_observaciones = '".$comments."' WHERE nu_id_requisicion = '".$req."'";
                $ErrMsg = "No se actualizó información de la solicitud al almacén";
                $TransResultTres = DB_query($SQL, $db, $ErrMsg);
            }else{
                $contenido = "No se actualizó información de la no existencia";
                $result = false;
            }

            
            if($TransResult == 1 && $TransResultPO == 1 && $TransResultDos == 1 && $TransResultTres == 1){
                $contenido = $requisitionno;
                $result = true;
            }
            
        }
    }
}

if ($option == 'cancelarRequisicion') {
    $req = $_POST['noReq'];
    $status = $_POST['status'];
    
    $info = array();
    
    $SQL = "UPDATE purchorderdetails SET status = 3, orderlineno_= 0, fecha_modificacion = current_timestamp() 
            WHERE orderno  = '$req' and status = 2";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $SQL = "UPDATE purchorders SET status = '".$status."', fecha_modificacion = current_timestamp() 
    WHERE orderno  = '$req'";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);        

    $contenido = "Se cancelaron todos los elementos no guardados en la requsisicone ";
    $result = true;
}

if ($option == 'avanzarRequisicion') {
    $req = $_POST['noReq'];
    $info = array();
    
    $SQL = "SELECT orderno, requisitionno, status, 
            if(sn_estatus_anterior=1,'Capturado',if(sn_estatus_anterior=2,'Validar',if(sn_estatus_anterior=3,'Por Autorizar',if(sn_estatus_anterior=4,'Autorizado',0)))) as sn_estatus_anterior, 
            if(sn_estatus_siguiente=1,'Capturado',if(sn_estatus_siguiente=2,'Validar',if(sn_estatus_siguiente=3,'Por Autorizar',if(sn_estatus_siguiente=4,'Autorizado',0)))) as sn_estatus_siguiente
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
    WHERE orderno = '$idReq' AND orderlineno_ != 0 
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
    if (DB_num_rows($TransResultSearchAnexo) == 1) {
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
    if (DB_num_rows($TransResult) == 1) {
        while ($myrow = DB_fetch_array($TransResult)) {
            // Validar si el estatus es el de la compra
            $SQL = "SELECT sn_mensaje_opcional FROM tb_botones_status WHERE sn_funcion_id = '".$funcion."' AND statusname = '".$myrow['status']."'";
            $resultEstaus = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($resultEstaus) > 0) {
                $myrow2 = DB_fetch_array($resultEstaus);
                $myrow['sn_mensaje_opcional'] = $myrow2['sn_mensaje_opcional'];
            }

            $SQL = "SELECT sn_funcion_id FROM tb_botones_status WHERE sn_funcion_id = '1371' AND statusname = '".$myrow['status']."'";
            $resultEstaus = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($resultEstaus) > 0) {
                $myrow['status'] = 'Autorizado';
                $myrow['sn_mensaje_opcional'] = 'Autorizado';
            }

            if ($myrow ['status'] == 'ProceCompra') {
                // Si esta en proceso de comrpa
                $myrow['status'] = 'Autorizado';
                $myrow['sn_mensaje_opcional'] = 'Autorizado';
            }

            $info[] = array(
                'status' => $myrow ['status'],
                'statusVisual' => $myrow ['sn_mensaje_opcional']
            );
        }
        $contenido = array('datos' => $info);
        $result = true;
    } else {
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
    // Perfiles
    // 9 - Capturista
    // 10 - Validador
    // 11 - Autorizador
    /* Se comenta pruebas perfiles,
    $SQL = "SELECT userid, profileid FROM sec_profilexuser WHERE userid = '".$_SESSION['UserID']."'";
    $ErrMsg = "No se encontro un perfil";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'userid' => $myrow ['userid'],
            'profileid' => $myrow ['profileid']
        );
    }*/

    if (HavepermissionHeader($_SESSION ['UserID'], 2383, $db) == 1) {
        // 9 - Capturista
        $info[] = array(
            'userid' => $_SESSION ['UserID'],
            'profileid' => 9
        );
    } else if (HavepermissionHeader($_SESSION ['UserID'], 2384, $db) == 1) {
        // 10 - Validador
        $info[] = array(
            'userid' => $_SESSION ['UserID'],
            'profileid' => 10
        );
    } else if (HavepermissionHeader($_SESSION ['UserID'], 2272, $db) == 1) {
        // 11 - Autorizador
        $info[] = array(
            'userid' => $_SESSION ['UserID'],
            'profileid' => 11
        );
    } else {
        // 9 - Capturista, perfil por default
        $info[] = array(
            'userid' => $_SESSION ['UserID'],
            'profileid' => 9
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'seleccionaAnexo') {
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
    if (DB_num_rows($TransResultExisteAnexo) >= 1) {
        $folioExisteAnexo[] = array(
            'folioAnexo' => $myrowExisteAnexo ['nu_anexo']
        );
    } else {
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

if ($option == 'saveAnexo') {
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
    if (DB_num_rows($TransResult) == 1) {
        $SQLAddAnexo = "UPDATE tb_cnfg_anexo_tecnico set nu_requisicion = '$idReq', ind_status = 3, nu_tagref = '$ur', nu_ue = '$ue', nu_orden_requisicion = 0 WHERE nu_anexo = '$folioAnexo' ";
        $ErrMsgAddAnexo = "Problemas para guardar el anexo tecnico";
        $TransResultAddAnexo = DB_query($SQLAddAnexo, $db, $ErrMsgAddAnexo);
        $contenido = "Se ha asignado exitosamente el anexo ".$folioAnexo."";
        $result = true;
    } else {
        $contenido = "El anexo técnico ". $folioAnexo ." ya ha sido seleccionado";
        $result = false;
    }
}

if ($option == 'validarSeleccionAnexo') {
    $idReq = $_POST['idReq'];
    $idAnexo = $_POST['idAnexo'];
    
    $SQL = "SELECT 
    tb_cnfg_anexo_tecnico.nu_orden_requisicion
    FROM tb_cnfg_anexo_tecnico
    WHERE tb_cnfg_anexo_tecnico.nu_anexo = '".$idAnexo."'
    AND tb_cnfg_anexo_tecnico.nu_requisicion = '".$idReq."'
    AND tb_cnfg_anexo_tecnico.nu_orden_requisicion = 0";
    $ErrMsg = "No se encontro un requisicion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) == 0) {
        $contenido = 0;
    } else {
        $contenido = 1;
    }
    $result = true;
}

if ($option == 'muestraInfoAnexo') {
    $idReq = $_POST['idReq'];
    $ordenAnexo = 0;
    $ur = $_POST['ur'];
    $ue = $_POST['ue'];
    $ordenElementoRequi = $_POST['ordenElementoRequi'];
    $folioAnexo = $_POST['folioAnexo'];

    $partida = $_POST['partida'];
    $stockid = $_POST['stockid'];

    $arrayValoresRenglonRequi = "";
    //$arrayFoliosAnexo = $_POST['arrayFoliosAnexo'];
    $info = array();
    /*for($a=0;$a<count($arrayFoliosAnexo);$a++){
        $valoresFolioAnexos.=$arrayFoliosAnexo[$a].",";
    }
    $valoresFolioAnexos=substr($valoresFolioAnexos, 0, -1);*/

    $sqlWhere = '';
    if (trim($partida) != '' || !empty($partida)) {
        // Viene partida
        $sqlWhere .= " AND nu_partida = '".$partida."' ";
    }

    if (trim($stockid) != '' || !empty($stockid)) {
        // Viene bien/servicio
        $sqlWhere .= " AND txt_bien_serevicio = '".$stockid."' ";
    }
    
    $SQL = "SELECT id_anexo, nu_anexo, nu_tagref, nu_ue, nu_type, nu_requisicion, sn_area, nu_proceso, nu_orden_requisicion, dt_fecha_creacion, ln_visto_bueno, ln_vobo_requiriente, sn_firma, txt_informacion_creacion, sn_revisado_por, sn_autorizado_por, txt_descripcion_antecedentes, txt_justificacion, ln_viabilidad, txt_bien_serevicio, txt_desc_bien_serevicio, nu_cantidad 
    FROM tb_cnfg_anexo_tecnico ant WHERE nu_anexo = '$folioAnexo' AND nu_requisicion = '$idReq' AND  nu_tagref = '$ur' AND nu_ue = '$ue' 
    -- AND ind_status in (3,10) AND (nu_orden_requisicion = 0 OR nu_orden_requisicion = '$ordenElementoRequi') 
    ".$sqlWhere;

    $ErrMsg = "No se encontro un requisicion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $ordenAnexo++;
        if ($myrow ['nu_proceso'] == 0) {
            $SQLAnexoID = "UPDATE tb_cnfg_anexo_tecnico set nu_proceso = '$ordenAnexo' WHERE nu_anexo = '$folioAnexo' AND nu_requisicion = '$idReq' AND  nu_tagref = '$ur' AND nu_ue = '$ue' and ind_status = 3 AND id_anexo = ".$myrow ['id_anexo']." ";
            $ErrMsgAnexoID = "Problemas para modificar el ID del anexo tecnico";
            $TransResultAnexoID = DB_query($SQLAnexoID, $db, $ErrMsgAnexoID);
            $myrow ['nu_proceso'] = $ordenAnexo;
        }

        // Cambios pruebas, se agrega consulta para obtener numero de renglon
        $SQL = "SELECT COUNT(id_anexo) as num
        FROM tb_cnfg_anexo_tecnico ant WHERE nu_anexo = '$folioAnexo' AND nu_requisicion = '$idReq' AND  nu_tagref = '$ur' AND nu_ue = '$ue' 
        AND id_anexo <= '".$myrow ['id_anexo']."'";
        $ErrMsg = "No se obtuvo número de renglon del anexo";
        $TransResult2 = DB_query($SQL, $db, $ErrMsg);
        while ($myrow2 = DB_fetch_array($TransResult2)) {
            $myrow ['nu_proceso'] = $myrow2 ['num'];
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

if ($option == 'guardarAnexoTecnico') {
    $idRequisicion = $_POST['idRequisicion'];
    $statusAnexo = $_POST['statusAnexo'];

    if ($statusAnexo == 1) {
        /*$SQL="UPDATE purchorders SET nu_anexo_tecnico = 1 WHERE orderno = '$idRequisicion' AND status not in ('Creada', 'Creada', 'Autorizado', 'Cancelado') ";
        $ErrMsg = "Problemas para guardar el anexo tecnico";
        $TransResult = DB_query($SQL, $db, $ErrMsg);*/

        $contenido = "Se asigno anexo técnico a la requisicion";
    } else {
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


if ($option == 'asignaElementoAnexo') {
    $idReq = $_POST['idReq'];
    $idAnexo = $_POST['idAnexo'];
    $idAnexoElemento = $_POST['idAnexoElemento'];
    $ordenAnexo = $_POST['ordenAnexo'];
    $ordenElementoRequi = $_POST['ordenElementoRequi'];
    $check = $_POST['check'];
    
    if ($check == 0) {
        $SQL = "UPDATE tb_cnfg_anexo_tecnico SET nu_orden_requisicion = 0, ind_status = 3 WHERE id_anexo = '$idAnexoElemento' and nu_anexo = '$idAnexo' and nu_requisicion = '$idReq' ";
        $ErrMsg = "Error al remover un elemento del anexo a la reuisicion";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    } else {
        $SQL = "UPDATE tb_cnfg_anexo_tecnico SET nu_orden_requisicion = '$ordenElementoRequi' , ind_status = 10 WHERE id_anexo = '$idAnexoElemento' and nu_anexo = '$idAnexo' and nu_requisicion = '$idReq'  AND nu_orden_requisicion = 0 ";
        $ErrMsg = "Error al asignar un elemento del anexo a la reuisicion";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }
    $result = true;
}

if ($option == 'removeAnexo') {
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
            WHERE periods.periodno = '$periodo'
            ORDER BY periods.lastdate_in_period asc"; ///// ESTA LÍNEA SE REMOVIÓ DEL WHERE periods.lastdate_in_period like '%$myrow[anho]%' AND 

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
        $presupuestoInicial = $infoPresupuesto[0][$nombreMes.'']; // Acomulado

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

// Validar requisicion de acuerdo a la existencia en BD
if ($option == 'validarRequisicionPanel') {
    //setlocale(LC_MONETARY, 'es_MX');
    $req = $_POST['idReq'];
    $ur = $_POST['ur'];
    $ue = $_POST['ue'];
    $dependencia = $_POST['dependencia'];
    $idrequisicion= $_POST['idrequisicion'];
    $nombreMes = "";
    $contenido = "";
    $result = "";
    $mensajeValidacion = "";
    $info = array();
    $itemStock = array();
    $disponiblepartida= array();

    //Regresa el nomre del mes actual para poder calcular el presupuesto actual
    $SQLMes = "SELECT periods.periodno, periods.lastdate_in_period, cat_Months.mes as mesName
                FROM periods 
            LEFT JOIN cat_Months ON cat_Months.u_mes = DATE_FORMAT(periods.lastdate_in_period, '%m')
            WHERE periods.periodno = '$periodo'
            ORDER BY periods.lastdate_in_period asc"; ///// ESTA LÍNEA SE REMOVIÓ DEL WHERE periods.lastdate_in_period like '%$myrow[anho]%' AND 

    $ErrMsgMes = "No se obtuvo informacion";
    $resultPeriods = DB_query($SQLMes, $db, $ErrMsgMes);

    while ($rowPeriods = DB_fetch_array($resultPeriods)) {
        $nombreMes = $rowPeriods ['mesName'];
    }

    // consultar solicitud al almacen que ya no sea parte de la requisicion
    $consulta= "SELECT purchorders.intostocklocation, tb_solicitudes_almacen_detalle.nu_id_solicitud, 
                    IFNULL(nu_cantidad, 0) AS nu_cantidad, purchorderdetails.podetailitem, 
                    purchorderdetails.itemcode
                FROM purchorders
                LEFT JOIN purchorderdetails ON purchorders.orderno= purchorderdetails.orderno
                LEFT JOIN tb_solicitudes_almacen_detalle ON purchorderdetails.podetailitem= tb_solicitudes_almacen_detalle.ln_ontransit AND tb_solicitudes_almacen_detalle.ln_arctivo=1
                WHERE purchorders.requisitionno= '".$idrequisicion."'
                AND purchorderdetails.status= 0";

    $resultado= DB_query($consulta, $db, $ErrMsg);

    while ($registro = DB_fetch_array($resultado)) {
        $SQLOntransit = "UPDATE locstock 
                        SET ontransit = ontransit - '".$registro["nu_cantidad"]."' 
                        WHERE stockid = '".$registro["itemcode"]."' and loccode ='".$registro["intostocklocation"]."'";

        $ErrMsgOntransit = "No se obtuvo informacion";
        $TransResultOntransit = DB_query($SQLOntransit, $db, $ErrMsgOntransit);

        // Inhabilitar registro eliminado en la solicitud al almacen
        $SQL="UPDATE tb_solicitudes_almacen_detalle
            SET ln_arctivo = 0, ln_renglon = 0 
            WHERE ln_ontransit= '".$registro["podetailitem"]."'";

        DB_query($SQL, $db, $ErrMsgOntransit);

        // Inhabilitar registro eliminado en la No Existencia
        $SQL="UPDATE tb_no_existencia_detalle
            SET ln_activo = 0, ln_renglon = 0 
            WHERE podetailitem= '".$registro["podetailitem"]."'";

        DB_query($SQL, $db, $ErrMsgOntransit);
    }


    $SQL = "SELECT p.orderno as idReq, p.requisitionno as noReq, p.status as statusReq, p.tagref as tagref,p.nu_ue as ue , p.comments as comments, p.deliverydate as fdelivery, p.validfrom, p.validto, tb_solicitudes_almacen_detalle.nu_id_detalle as idDetalleSolAlmacen, pd.itemcode as itemcode, pd.itemdescription as itemdescription, pd.unitprice as precio, pd.quantityord as cantidadReq, pd.orderlineno_, pd.clavepresupuestal as clavepre, pd.sn_descripcion_larga as longdesc, pd.status as statusItem, pd.renglon as renglon, sm.mbflag as mbflag, intostocklocation AS almacen, IFNULL(tb_solicitudes_almacen_detalle.nu_cantidad, 0) AS cantidadSolAlmacen, pd.quantityord - IFNULL(tb_solicitudes_almacen_detalle.nu_cantidad, 0) AS qtyDiferencia, locstock.ontransit as ontransit, locstock.quantity as stock, (locstock.quantity - locstock.ontransit) AS qtyDisponible, pd.podetailitem as idItemDetalle
    FROM purchorders p
    JOIN purchorderdetails pd ON (p.orderno = pd.orderno)
    JOIN stockmaster sm ON ( pd.itemcode = sm.stockid) 
    LEFT JOIN locstock ON (locstock.loccode = p.intostocklocation and locstock.stockid = sm.stockid)
    LEFT JOIN tb_solicitudes_almacen ON (p.orderno= tb_solicitudes_almacen.nu_id_requisicion)
    LEFT JOIN tb_solicitudes_almacen_detalle ON tb_solicitudes_almacen_detalle.ln_ontransit = pd.podetailitem
    WHERE p.orderno = $req AND pd.status = 2 order by pd.orderlineno_ asc";
    // cambios pruebas, se quita join ya que marca error
    // LEFT JOIN tb_solicitudes_almacen_detalle ON (tb_solicitudes_almacen.nu_folio= tb_solicitudes_almacen_detalle.nu_id_solicitud AND tb_solicitudes_almacen_detalle.ln_clave_articulo= pd.itemcode AND ln_arctivo = 1 AND ln_renglon != 0)
    $ErrMsg = "No se obtuvo informacion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $infoSolicitud = array();
    $disponibleSaldo= array();

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
        $tot = $precio * $cantidadReq;
        // cambios pruebas, almacenar cantidad por renglon
        // $disponiblepartida[$infopre[0]["partida_esp"]]+= $tot;
        $disponiblepartida[$myrow ['clavepre']]+= $tot;
        $disponibleExistencia= 0;

        // Validar y comparar clave presupuestal para determinar
        // suficiencia presupuestal
        if ($_SESSION['UserID'] == 'desarrollo') {
            // echo "\n stockid: ".$itemcode;
            // echo "\n dispo: ".$infopre[0][$nombreMes];
            // echo "\n dispo acomulado: ".$infopre[0][$nombreMes.'Acomulado'];
            // echo "\n clave: ".$myrow ['clavepre'];
            // echo "\n precio: ".$precio;
            // echo "\n cantidadReq: ".$cantidadReq;
            // echo "\n prueba vali: ".$disponiblepartida[$myrow ['clavepre']];
            // print_r($disponiblepartida);
            // echo "\n ";
        }

        if (empty($myrow ['clavepre'])) {
            // Si no tiene clave presupuestal seleccionada
            if ($myrow['mbflag'] == 'B') {
                $mensajeValidacion= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;Este artículo no cuenta con clave presupuestal.</p>';
            } else {
                $mensajeValidacion= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;Este servicio no cuenta con clave presupuestal.</p>';
            }
        } else {
            // cambios pruebas, validar disponible
            // Acomulado
            if ($infopre[0][$nombreMes.''] >= $disponiblepartida[$myrow ['clavepre']]) {
                $mensajeValidacion = '<p><i class="glyphicon glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i>&nbsp;&nbsp;El importe total en el renglón '.$myrow ['orderlineno_'].' cuenta con disponible suficiente en el mes en curso.</p>';
            } else {
                $mensajeValidacion= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El importe total en el renglón '.$myrow ['orderlineno_'].' excedió el disponible del mes en curso.</p>';
            }

            // cambios pruebas, se comenta proceso anterior
            // if (empty($infopre[0][$nombreMes])) {
            //     $SQLPrecomprometidos="SELECT * FROM chartdetailsbudgetlog WHERE tagref = '$ur' AND period = '$periodo' AND nu_tipo_movimiento = 258";
            //     $ErrMsgPrecomprometidos = "No se obtuvo informacion";
            //     $TransResultPrecomprometidos = DB_query($SQLPrecomprometidos, $db, $ErrMsgPrecomprometidos);
            //     if (DB_num_rows($TransResultPrecomprometidos) > 0) {
            //         $mensajeValidacion= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;La clave presupuestal '.$cvepre.' en el renglón '.$partida_producto.' no tiene recurso disponible en el mes en curso.</p>';
            //     } else {
            //         $mensajeValidacion = '<p><i class="glyphicon glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i>&nbsp;&nbsp;El importe total en el renglón '.$myrow ['orderlineno_'].' cuenta con disponible suficiente en el mes en curso.</p>';
            //     }
            // } else {
            //     if ($infopre[0][$nombreMes] >= $disponiblepartida[$infopre[0]["partida_esp"]]) {
            //         $mensajeValidacion = '<p><i class="glyphicon glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i>&nbsp;&nbsp;El importe total en el renglón '.$myrow ['orderlineno_'].' cuenta con disponible suficiente en el mes en curso.</p>';
            //     } else {
            //         $mensajeValidacion= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El importe total en el renglón '.$myrow ['orderlineno_'].' excedió el disponible del mes en curso.</p>';
            //     }
            // }
        }

        // echo "\n ***********";

        if (is_null(($disponibleSaldo[$myrow['itemcode']]))) {
            $disponibleSaldo[$myrow['itemcode']]= $myrow['qtyDisponible'] + $myrow['cantidadSolAlmacen'];
        } else {
            $disponibleSaldo[$myrow['itemcode']] += $myrow['cantidadSolAlmacen'];
        }
        // echo "\n saldo: ".$disponibleSaldo[$myrow['itemcode']];
        if ($disponibleSaldo[$myrow['itemcode']] > 0) {
            if ($disponibleSaldo[$myrow['itemcode']] >= $myrow ['cantidadReq']) {
                $myrow ['cantidadSolAlmacen'] = $myrow ['cantidadReq'];
                $disponibleSaldo[$myrow['itemcode']] -= $myrow ['cantidadReq'];
                $qtyDiferencia = 0;
            } else {
                $myrow ['cantidadSolAlmacen'] = $disponibleSaldo[$myrow['itemcode']];
                $qtyDiferencia = $myrow ['cantidadReq'] - $disponibleSaldo[$myrow['itemcode']];
                $disponibleSaldo[$myrow['itemcode']] = 0;
                $qtyDiferencia *= -1;
            }
        } else {
            $myrow ['cantidadSolAlmacen'] = 0;
            $qtyDiferencia = $myrow ['cantidadReq'] * -1;
        }

        // if (is_null(($disponibleSaldo[$myrow['itemcode']]))) {
        //     $disponibleSaldo[$myrow['itemcode']]= $myrow['qtyDisponible'];   // saldo inicial existencia
        // } else if ($disponibleSaldo[$myrow['itemcode']] < 0) {
        //     $disponibleSaldo[$myrow['itemcode']]= 0;
        // }

        // echo "\n saldo acomulado 1: ".$disponibleSaldo[$myrow['itemcode']];

        // $disponibleSaldo[$myrow['itemcode']]-= $myrow['qtyDiferencia'];
        // $disponibleExistencia= $disponibleSaldo[$myrow['itemcode']];
        // $qtyDiferencia= $myrow['qtyDiferencia'];

        // echo "\n disponibleExistencia 1: ".$disponibleExistencia;
        // echo "\n saldo acomulado 2: ".$disponibleSaldo[$myrow['itemcode']];

        // if ($disponibleSaldo[$myrow['itemcode']] < 0) {
        //     echo "\n entra if 1";
        //     $qtyDiferencia= $disponibleSaldo[$myrow['itemcode']];
        // } else {
        //     echo "\n entra else 1";
        //     $disponibleExistencia= $myrow['qtyDiferencia'];
        // }
        // echo "\n disponibleExistencia 2: ".$disponibleExistencia;
        // if (empty($myrow['cantidadSolAlmacen'])) {
        //     echo "\n entra if 2";
        //     $myrow['cantidadSolAlmacen']= $disponibleExistencia;
        // } else {
        //     echo "\n entra else 2";
        //     echo "\n operacion if ".($myrow['qtyDiferencia'] + $qtyDiferencia);
        //     if (($myrow['qtyDiferencia'] + $qtyDiferencia) != 0) {
        //         echo "\n entra if 3";
        //         $myrow['cantidadSolAlmacen']= $qtyDiferencia + $myrow['cantidadSolAlmacen'];

        //         if ($disponibleSaldo[$myrow['itemcode']] > $qtyDiferencia) {
        //             $qtyDiferencia = 0;
        //         }
        //     }
        // }

        $qtyStockDisp = $disponibleSaldo[$myrow['itemcode']];
        $qtyStock = $disponibleSaldo[$myrow['itemcode']];

        // echo "\n itemcode: ".$myrow ['itemcode'];
        // echo "\n qtySolA: ".$myrow ['cantidadSolAlmacen'];
        // echo "\n qtyDiferencia consulta: ".$myrow ['qtyDiferencia'];
        // echo "\n qtyDiferencia: ".$qtyDiferencia;
        // echo "\n saldo acomulado: ".$disponibleSaldo[$myrow['itemcode']];


        // Almacenar solicitudes por si es el mismo producto con diferente clave
        /*if ($infoSolicitud[''.$itemcode] != 0) {
            // Al disponible restar lo que tiene
            $myrow ['qtyDisponible'] = abs($myrow ['qtyDisponible']) - abs($infoSolicitud[''.$itemcode]);
        }
        
        $qtyDisp = $myrow ['qtyDisponible'] - $qtyDiferencia;
        //$qtyStockDisp = $newQtyReq - $myrow ['qtyDisponible'];*/
        /*
        // Cambios pruebas
        $myrow ['qtyDisponible'] = $myrow ['qtyDisponible'] + $myrow ['cantidadSolAlmacen'];
        // $qtyStock = ($myrow ['qtyDisponible'] + $myrow ['cantidadSolAlmacen']) - $myrow ['cantidadReq'];
        if ($myrow ['cantidadReq'] > $myrow ['qtyDisponible']) {
            $myrow ['cantidadSolAlmacen'] = $myrow ['qtyDisponible'];
        } else {
            $myrow ['cantidadSolAlmacen'] = $myrow ['cantidadReq'];
        }

        // Almacenar solicitudes por si es el mismo producto con diferente clave
        if ($infoSolicitud[''.$itemcode] == '') {
            // La primera ves solo tomar la cantidad al almacen
            $infoSolicitud[''.$itemcode] = 0;
        }

        $infoSolicitud[''.$itemcode] = abs($infoSolicitud[''.$itemcode]) + abs($myrow ['cantidadSolAlmacen']);
        */

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
            'idItemDetalle' => $myrow ['idItemDetalle'],
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
            'qtyStock' => $qtyStock,
            'qtyontransit' => $myrow ['ontransit'],
            'qtyStockDisp' => $qtyStockDisp,
            'mbflag' => $mbflag,
            'almacen' => $myrow ['almacen'],
            'mensajeValidacion' => $mensajeValidacion,
            'mensajePrueba' => $itemcode." - ".$infoSolicitud[''+$itemcode]
        );
    }

    // exit();
    
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'replicarRequisicion') {
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

    if (count($datosRequisicion) == 0 || $datosRequisicion == '') {
        $contenido = "Error no hay No Existencias" ;
        $result = false;
    } else {
        // inserta la requisicion replicada
        // Validar si existe proveedor por dafult para operaciones
        $SQL = "SELECT supplierid FROM suppliers WHERE supplierid = '111111'";
        $ErrMsgLocstock = "No se obtuvo informacion";
        $result = DB_query($SQL, $db, $ErrMsgLocstock);
        $existe = 0;
        while ($myrow2 = DB_fetch_array($result)) {
            $existe = 1;
        }
        if ($existe == 0) {
            // Si no existe proveedor agregarlo
            $SQL = "INSERT INTO `suppliers` (`supplierid`, `suppname`, `taxid`, `address1`, `address2`, `address3`, `address4`, `address5`, `address6`, `lat`, `lng`, `currcode`, `suppliersince`, `paymentterms`, `lastpaid`, `lastpaiddate`, `bankact`, `bankref`, `bankpartics`, `remittance`, `taxgroupid`, `factorcompanyid`, `taxref`, `phn`, `port`, `active`, `newcode`, `accion`, `typeid`, `narrative`, `limitcredit`, `supptaxname`, `idspecialty`, `email`, `distancia`, `nombre_movil`, `tipodetercero`, `flagagentaduanal`, `u_typediot`, `u_typeoperation`, `ln_tipoPersona`, `ln_curp`, `ln_representante_legal`, `nu_interior`, `nu_exterior`, `id_nu_entidad_federativa`, `id_nu_municipio`, `nu_tesofe`)
            VALUES
            ('111111', 'PROVEEDOR GENERICO', 'XXXX010101XXX', '', '', '', '', '', '', 0.000000, 0.000000, 'MXN', '0000-00-00', '01', 121, '2017-12-29 00:00:00', '0123456789', 'referencia b', 'Banco de pru', 1, 1, 1, '', '', '', 1, '', '', 1, NULL, NULL, NULL, NULL, NULL, 0, '', '4', NULL, '4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0)";
            $result2 = DB_query($SQL, $db, $ErrMsgLocstock);
        }

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
            // Obtener informacion para identificador Inicio
            $cppt = fnObtenerIdentificadorPresupuesto($db, $cvepre);

            $cadenaFinal ="'".$ordenElemento."','".$fDelivery."','1.1.5.1.1', 0, 0, 0, 0, 0, 0, '', 0, '', 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, current_timestamp(), 0, 0, 0, 0, 0,'', '', 0, '', '', '', '1900-01-01', '1900-01-01', current_timestamp(),'', 1, 1, 0, '', '', 2";
            $cadenaRequi.="('".$genNuevaRequisicion."',".$cadenaFinal.",'".$cvepre."','".$item."','".$desc."','".$precio."',0.00,0.00,'".$qty."','".$total_quantity."', '".$cppt."')";
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
                    clavepresupuestal, itemcode, itemdescription, unitprice, quantityrecd, qtyinvoiced, quantityord, total_quantity,
                    ln_clave_iden) 
                    VALUES 
                        ".$cadenaRequi;
                        $ErrMsgPD = "No se pudo repicar la requisicion" . $noReq;
                        $TransResultPD = DB_query($SQLPD, $db, $ErrMsgPD);
            
            $contenido = array('datoIdReq' =>$genNuevaRequisicion, 'datoNoReq'=> $newNoRequi);
            $result = true;
    }
}

if ($option == 'validarNoExistencia') {
    $idReq = $_POST['idrequi'];
    // Obtener Requisicion
    $requisitionno = 0;
    $id_no_existencia = 0;
    $SQL = "SELECT requisitionno FROM purchorders WHERE orderno = '".$idReq."'";
    $ErrMsg = "No se pudo repicar la requisicion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($row = DB_fetch_array($TransResult)) {
        $requisitionno = $row ['requisitionno'];
    }
    $SQL = "SELECT nu_tag, nu_ue, nu_id_requisicion, nu_id_no_existencia, status FROM tb_no_existencias WHERE nu_id_no_existencia != '' AND nu_id_requisicion = '$requisitionno' AND status = 1 ";
    $ErrMsg = "No se pudo repicar la requisicion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($row = DB_fetch_array($TransResult)) {
        $id_no_existencia = $row ['nu_id_no_existencia'];
    }
    $contenido = array('datos' => $id_no_existencia);
    $result = true;
}

if ($option == 'validarSolAlmacen') {
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

//opcion para generar los nuevos registros de la No Existencia
if ($option == 'generarNoExistencia') {
    $arrayDatosNoExistvalores = array();
    $idReq = $_POST['idrequi'];
    $noReq = $_POST['norequi'];
    //$norequi = GetNextTransNo(19, $db);
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
    $iditem= 0;

    if (count($datosNoExistvalores) == 0 || $datosNoExistvalores == '') {
        $contenido = "Error no hay No Existencias" ;
        $result = false;
    } else {
        // Cambios pruebas, obtener descripción de la requisición
        $comments = "";
        $SQL = "SELECT comments FROM purchorders WHERE orderno = '".$idReq."'";
        $ErrMsg = "Obtener Comentarios de la Requisición";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $comments = $myrow ['comments'];
        }
        
        $SQLNE="INSERT INTO tb_no_existencias (nu_id_no_existencia, nu_id_requisicion, dtm_fecharegistro, 
                    nu_tag, nu_ue, ln_usuario, status, txt_observaciones, nu_dependencia) 
                VALUES (".$folioNoExistencia.", ".$noReq.", current_timestamp(), '$tagref','$ue', '$usuarioNoExistencia', '1', '$comments','$dependencia')";
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
            $iditem = $datosNoExistvalores[$a][0]['iditem'];

            $cadenaNoExist.= "('".$noReq."','".$generaNoExistencia."','PZA','',1,'".$orden."','".$cvepre."','".$item."','".$desc."','".$qty."', '".$iditem."')";
            $cadenaNoExist.= ",";
        }

        $cadenaNoExist=substr($cadenaNoExist, 0, -1);

        $SQLNED= "INSERT INTO tb_no_existencia_detalle (nu_id_requisicion, nu_id_no_existencia, ln_unidad_medida, ln_cams, ln_activo, ln_renglon, ln_partida_esp, ln_item_code, txt_item_descripcion, nu_cantidad, podetailitem ) VALUES 
                ".$cadenaNoExist;

        $ErrMsgNED = "No se pudo repicar la requisicion";
        $TransResultNED = DB_query($SQLNED, $db, $ErrMsgNED);

        $contenido = array('datos' => $folioNoExistencia);
        $result = true;
    }
}

// Opcion para actualizar los datos en la tabla de No Existencia considerando los cambios 
// efectuados en requisicion
if ($option == 'actualizarNoExistencia') {
    $idReq = $_POST['idrequi'];
    $noReq = $_POST['norequi'];
    $folioNoE = $_POST['folioNoE'];
    $datosNoExistvalores = $_POST['noExistvalores'];
    $ordenElemento = 0;
    $info = array();
    $valoresTabla = array();
    $valoresBase = array();
    $valoresAgregar = array();
    $valoresEliminar = array();
    $iditem= 0;
    $itemcode= array();
    $ordenElemento= 0;

    // consultar detalle de No Existencia
    $SQLFindNoE = "SELECT tb_no_existencias.nu_id_no_existencia AS nu_id_no_existencia, ln_item_code, ln_renglon, 
                    tb_no_existencia_detalle.podetailitem 
                    FROM tb_no_existencias
                    INNER JOIN tb_no_existencia_detalle ON (tb_no_existencias.nu_id_no_existencia = tb_no_existencia_detalle.nu_id_no_existencia )
                    WHERE tb_no_existencias.nu_id_requisicion = '$noReq' AND status = 1 AND ln_renglon != 0 AND ln_activo = 1";
    
    $ErrMsgFindNoE= "Error al actualizar la solicitud";
    $TransResultFindNoE = DB_query($SQLFindNoE, $db, $ErrMsgFindNoE);
    
    while ($myrow = DB_fetch_array($TransResultFindNoE)) {
        $info[] = array(
            'nu_id_no_existencia' => $myrow ['nu_id_no_existencia'],
            'itemcode' => $myrow ['ln_item_code'],
            'ln_renglon' => $myrow ['ln_renglon'],
            'iditem' => $myrow ['podetailitem']
        );
    }

    $maxOrdenNoE = count($info);
    
    for ($b=0; $b<count($info); $b++) {
        $itemcode["itemcode"] = $info[$b]['itemcode'];
        $itemcode["iditem"] = $info[$b]['iditem'];

        if ($info[$b]['itemcode'] != '') {
            array_push($valoresBase, $itemcode);
        }
    }

    // Ciclo que recorre los datos mostrados en pantalla
    for ($a=0; $a<count($datosNoExistvalores); $a++) {
        $ordenNE = $datosNoExistvalores[$ordenElemento][0]['ordenNE'];
        $cvepreNE = $datosNoExistvalores[$ordenElemento][0]['cvepreNE'];
        $itemNE = $datosNoExistvalores[$ordenElemento][0]['itemNE'];
        $descNE = $datosNoExistvalores[$ordenElemento][0]['descNE'];
        $qtyNE = $datosNoExistvalores[$ordenElemento][0]['qtyNE'];
        $iditem = $datosNoExistvalores[$ordenElemento][0]['iditem'];

        if ($itemNE != '') {
            $ErrMsg = "Error al actualizar la no existencia";

            $consulta= "SELECT podetailitem FROM tb_no_existencia_detalle  
                        WHERE ln_activo = 1 AND podetailitem = '".$iditem."'";

            $resultado = DB_query($consulta, $db, $ErrMsg);

            if (DB_fetch_array($resultado)) {
                $SQL = "UPDATE tb_no_existencia_detalle 
                        SET nu_cantidad = '$qtyNE', ln_renglon = '$ordenNE' 
                        WHERE ln_activo = 1 AND podetailitem = '".$iditem."'";

                $TransResult = DB_query($SQL, $db, $ErrMsg);
            } else {
                $valoresAgregar[] = array(
                    'item' =>  $itemNE,
                    'itemdesc' =>  $descNE,
                    'qty' => $qtyNE,
                    'cvepre' =>  $cvepreNE,
                    'orden' =>  $ordenNE,
                    'iditem' => $iditem
                );
            }

            $itemNEVisual["itemcode"]= $itemNE;
            $itemNEVisual["iditem"]= $iditem;

            array_push($valoresTabla, $itemNEVisual);
        }

        $ordenElemento++;
    }

    // comparar ambos arreglos para encontrar los elementos a eliminar
    foreach ($valoresBase as $elimina) {
        $existe=false;

        foreach ($valoresTabla as $elementovisual) {
            if ($elimina["itemcode"] == $elementovisual["itemcode"] && $elimina["iditem"] == $elementovisual["iditem"]) {
                $existe= true;
                break;
            }
        }

        if (!$existe) {
            array_push($valoresEliminar, $elimina);
        }
    }

    // Eliminar partidas que ya no son parte de la No Existencia
    fnEliminarItem($idReq, $folioNoE, $valoresEliminar, 'tb_no_existencia_detalle', 'nu_id_no_existencia', 'ln_item_code', 'ln_activo', 'nu_id_no_existencia_detalle', 'podetailitem', 'nu_cantidad', '', '', $db);

    // Agregar partidas a la No Existencia
    fnAgregarItemNoExistencia($noReq, $folioNoE, $valoresAgregar, $maxOrdenNoE, $db);

    //fnIndexarItem($idReq, $folioNoE, 'tb_no_existencia_detalle', 'ln_renglon', 'nu_id_no_existencia', 'ln_activo', 'nu_id_no_existencia_detalle', 'ln_item_code', $db);
    $enc = new Encryption;
    $url = "&idNoExistencia=> ". $folioNoE;
    $url = $enc->encode($url);
    $liga= "URL=" . $url;
    $link ="<b>Se ha actualizado exitosamente la no existencia con el folio: </b><a target='_blank' href='./panel_no_existencias.php' style='color: blue; '><u>".$folioNoE."</u></a>";
    $contenido = $link;
    $result = true;
}

/*if ($option == 'generarSolAlmacen') {
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
            $orden = $datosSolAlmacen[$a][0]['orden'];
            $itemOntrst = $datosSolAlmacen[$a][0]['itemOntrst'];
            $total_quantity = $qty * $precio;
            
            $cadenaSolAlmacen.= "('".$transno."','PZA','".$orden."',1,'".$item."','".$desc."','".$qty."')";
            $cadenaSolAlmacen.= ",";

            fnOnTranist($idReq, $transno, $item, $qty, $almacen, $itemOntrst, $db);
        }

        $cadenaSolAlmacen=substr($cadenaSolAlmacen, 0, -1);

        $SQLSA = "INSERT INTO tb_solicitudes_almacen_detalle (nu_id_solicitud, ln_unidad_medida, ln_renglon, ln_arctivo, ln_clave_articulo, txt_descripcion, nu_cantidad) VALUES ".$cadenaSolAlmacen;
        $ErrMsgSA = "Fallo la solicitud automatica";
        $TransResultSA = DB_query($SQLSA, $db, $ErrMsgSA);

        $contenido = array('datos' => $transno);
        $result = true;
    }
}*/

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
    $nombreEstatus = "Por Autorizar";
    $status = 65;
    $ordenElemento = 0;
    $transno = GetNextTransNo(1000, $db);
    
    // condicion
    if (count($datosSolAlmacen) == 0 || $datosSolAlmacen == '' || $datosSolAlmacen == null) {
        $Mensaje = '<div><div class="fl mt30"><b>No hay elementos para generar una solicitud al almacén</b></div></div>';
        $result = false;
    } else {
        // Cambios pruebas, obtener descripción de la requisición
        $comments = "";
        $SQL = "SELECT comments FROM purchorders WHERE orderno = '".$idReq."'";
        $ErrMsg = "Obtener Comentarios de la Requisición";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        while ($myrow = DB_fetch_array($TransResult)) {
            $comments = $myrow ['comments'];
        }

        $SQL = "INSERT INTO tb_solicitudes_almacen (nu_tag,ln_usuario,estatus,nu_folio,txt_observaciones,ln_nombre_estatus,ln_ue,ln_tipo_solicitud, nu_id_requisicion, ln_almacen) VALUES ('". $tagref . "','".$_SESSION ['UserID']."','".$status."','".$transno."','".$comments."','".$nombreEstatus."','".$ue."','Automática', ".$idReq.", '".$almacen."')";

        $ErrMsg = "No se encontro una requisicion";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $cadenaSolAlmacen='';
        // Cambios pruebas, declaracion de variable
        $difDatos = 0;
        for ($a=1; $a<count($datosSolAlmacen); $a++) {
            $ordenElemento++;
            $item = $datosSolAlmacen[$a][0]['item'];
            $desc = $datosSolAlmacen[$a][0]['desc'];
            $qty = $datosSolAlmacen[$a][0]['qty'];
            $precio = $datosSolAlmacen[$a][0]['precio'];
            $orden = $datosSolAlmacen[$a][0]['orden'];
            $itemOntrst = $datosSolAlmacen[$a][0]['itemOntrst']; // Cambios pruebas
            $total_quantity = $qty * $precio;

            // Cambios pruebas, agregar variable
            $qtyAct = fnOnTranist($idReq, $transno, $item, $qty, $almacen, $itemOntrst, $db);
            if ($qtyAct != $qty) {
                // Si no alcanzo solicitud
                $difDatos = 1;
            }
            $qty = $qtyAct;

            // Cambios pruebas, se obtiene partida específica
            $ln_partida = '';
            $SQLSA = "SELECT clavepresupuestal FROM purchorderdetails WHERE podetailitem = '".$itemOntrst."'";
            $ErrMsgSA = "No se obtuvo la clavepresupuestal";
            $TransResultSA = DB_query($SQLSA, $db, $ErrMsgSA);
            if ($myrow = DB_fetch_array($TransResultSA)) {
                $ln_partida = fnObtenerPartidaClavePresupuestal($db, $myrow['clavepresupuestal']);
            }

            $cadenaSolAlmacen = "('".$transno."','PZA','".$orden."',1,'".$item."','".$desc."','".$qty."', '".$itemOntrst."', '".$ln_partida."')";

            // Cambios pruebas, se cambio el insert abajo de la actualizacion
            $SQLSA = "INSERT INTO tb_solicitudes_almacen_detalle (nu_id_solicitud, ln_unidad_medida, ln_renglon, ln_arctivo, ln_clave_articulo, txt_descripcion, nu_cantidad, ln_ontransit, ln_partida) VALUES ".$cadenaSolAlmacen;
            $ErrMsgSA = "Fallo la solicitud automatica";
            $TransResultSA = DB_query($SQLSA, $db, $ErrMsgSA);
        }

        // Cambios pruebas, validacion para datos
        if ($difDatos == 1) {
            // Encontro diferencias en insert
            $Mensaje = '<div><div class="fl mt30"><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i><b>Solicitud incompleta volver a Guardar</b></div></div>&nbsp;&nbsp;&nbsp;';
            $result = false;
        } else {
            $result = true;
        }
        $contenido = array('datos' => $transno);
    }
}

/*if($option == 'actualizarSolAlmacen'){
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
    $SQLFindSolA = "SELECT nu_id_detalle, nu_folio, ln_clave_articulo, nu_cantidad ,ln_renglon, ln_ontransit
    FROM tb_solicitudes_almacen 
    INNER JOIN tb_solicitudes_almacen_detalle ON (tb_solicitudes_almacen.nu_folio = tb_solicitudes_almacen_detalle.nu_id_solicitud) 
    WHERE nu_folio = '".$folioSolA."' AND  nu_id_requisicion = '".$idReq."' AND ln_renglon != 0 AND ln_arctivo = 1";
    $ErrMsgFindSolA = "Error al actualizar la solicitud";
    $TransResultFindSolA = DB_query($SQLFindSolA, $db, $ErrMsgFindSolA);
    while ($myrow = DB_fetch_array($TransResultFindSolA)) {
        $info[] = array(
            'nu_id_detalle' => $myrow ['nu_id_detalle'],
            'nu_folio' => $myrow ['nu_folio'],
            'itemcode' => $myrow ['ln_clave_articulo'],
            'qtySolAlmacen' => $myrow ['nu_cantidad'],
            'ln_renglon' => $myrow ['ln_renglon'],
            'ln_ontransit' => $myrow ['ln_ontransit']
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
        $orden = $datosSolAlmacen[$a][0]['orden'];
        $itemOntrst = $datosSolAlmacen[$a][0]['itemOntrst'];
        if($item != ''){
            for ($b=0; $b<count($info); $b++) {
                $itemAlmacen = $info[$b]['itemcode'];
                if($itemAlmacen != ''){
                    if($item == $itemAlmacen){
                        $qtyAlmacen = $info[$b]['qtySolAlmacen'];
                        $renglon = $info[$b]['ln_renglon'];
                    }
                }
            }
            if($qtyAlmacen == '' || $qtyAlmacen == null || $qtyAlmacen === 'undefined' ){
                $qtyAlmacen = 0;
            }
            $diferencia = $qty - $qtyAlmacen;
            $onTranistQty = $diferencia + $qtyAlmacen;
            if($onTranistQty > 0){
                fnOnTranist($idReq, $folioSolA, $item, $onTranistQty, $almacen, $itemOntrst, $db);   
            }
            $SQL = "UPDATE tb_solicitudes_almacen_detalle SET nu_cantidad = '$qty', ln_renglon = '$orden' WHERE nu_id_solicitud = '$folioSolA' AND ln_clave_articulo = '$item' AND ln_arctivo = 1 AND ln_renglon = '$renglon' AND nu_id_detalle != 0 ";
            $ErrMsg = "Error al actualizar la solicitud";
            $TransResult = DB_query($SQL, $db, $ErrMsg); 
            array_push($valoresTabla, $item);
            if(in_array($item, $valoresBase)) {
            }else{
                $valoresAgregar[] = array(
                    'item' =>  $item,
                    'itemdesc' =>  $desc,
                    'qty' => $qty,
                    'qtyOnTranist' => $onTranistQty,
                    'cvepre' =>  $cvepre,
                    'orden' =>  $orden,
                    'itemOntrst' => $itemOntrst
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
    fnEliminarItem($idReq, $folioSolA, $valoresEliminar, 'tb_solicitudes_almacen_detalle', 'nu_id_solicitud', 'ln_clave_articulo', 'ln_arctivo', 'nu_id_detalle','ln_renglon', 'nu_cantidad', $almacen, $itemOntrst, $db);
    //fnIndexarItem($idReq, $folioSolA, 'tb_solicitudes_almacen_detalle', 'ln_renglon', 'nu_id_solicitud', 'ln_arctivo', 'nu_id_detalle', 'ln_clave_articulo', $db);
    $enc2 = new Encryption;
    $url2 = "&idNoExistencia=> ". $folioSolA;
    $url2 = $enc2->encode($url2); 
    $liga2= "URL=" . $url2;
    $link ="<b>Se ha actualizado exitosamente la solictud al almacén con folio: </b><a target='_blank' href='./almacen.php?' style='color: blue; '><u>".$folioSolA."</u></a>";
    $contenido = $link;
    $result = true;
}*/

if ($option == 'actualizarSolAlmacen') {
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

    $SQLFindSolA = "SELECT nu_id_detalle, nu_folio, ln_clave_articulo, nu_cantidad ,ln_renglon, ln_ontransit
                    FROM tb_solicitudes_almacen 
                    INNER JOIN tb_solicitudes_almacen_detalle ON (tb_solicitudes_almacen.nu_folio = tb_solicitudes_almacen_detalle.nu_id_solicitud) 
                    WHERE nu_folio = '".$folioSolA."' AND  nu_id_requisicion = '".$idReq."' AND ln_renglon != 0 
                    AND ln_arctivo = 1";
    
    $ErrMsgFindSolA = "Error al actualizar la solicitud";
    $TransResultFindSolA = DB_query($SQLFindSolA, $db, $ErrMsgFindSolA);

    while ($myrow = DB_fetch_array($TransResultFindSolA)) {
        $info[] = array(
            'nu_id_detalle' => $myrow ['nu_id_detalle'],
            'nu_folio' => $myrow ['nu_folio'],
            'itemcode' => $myrow ['ln_clave_articulo'],
            'qtySolAlmacen' => $myrow ['nu_cantidad'],
            'ln_renglon' => $myrow ['ln_renglon'],
            'ln_ontransit' => $myrow ['ln_ontransit']
        );
    }

    $maxOrdenSolA = count($info);

    for ($b=0; $b<count($info); $b++) {
        $itemcode = $info[$b]['itemcode']."@".$info[$b]['ln_ontransit'];
        $qtySolAlmacen = $info[$b]['qtySolAlmacen'];

        if ($itemcode != '') {
            array_push($valoresBase, $itemcode);
        }
    }

    // Cambios pruebas, declaracion de variable
    $difDatos = 0;

    for ($a=0; $a<count($datosSolAlmacen); $a++) {
        $renglonElemento++;
        $item = $datosSolAlmacen[$ordenElemento][0]['item'];
        $desc = $datosSolAlmacen[$ordenElemento][0]['desc'];
        $qty = $datosSolAlmacen[$ordenElemento][0]['qty'];
        $precio = $datosSolAlmacen[$ordenElemento][0]['precio'];
        $orden = $datosSolAlmacen[$a][0]['orden'];
        $itemOntrst = $datosSolAlmacen[$a][0]['itemOntrst'];

        if ($item != '') {
            for ($b=0; $b<count($info); $b++) {
                $itemAlmacen = $info[$b]['itemcode'];
                if ($itemAlmacen != '') {
                    if ($item == $itemAlmacen && $orden == $info[$b]['itemOntrst']) {
                        $qtyAlmacen = $info[$b]['qtySolAlmacen'];
                        $renglon = $info[$b]['ln_renglon'];
                    }
                }
            }

            if ($qtyAlmacen == '' || $qtyAlmacen == null || $qtyAlmacen === 'undefined') {
                $qtyAlmacen = 0;
            }

            $diferencia = $qty - $qtyAlmacen;
            $onTranistQty = $diferencia + $qtyAlmacen;

            $onTranistQty = $qty;
            
            if (in_array($item."@".$itemOntrst, $valoresBase)) {
                // Cambios pruebas, estaba afuera del if
                if ($onTranistQty > 0) {
                    // Cambios pruebas, agregar variable
                    $qtyAct = fnOnTranist($idReq, $folioSolA, $item, $onTranistQty, $almacen, $itemOntrst, $db);
                    if ($qtyAct != $qty) {
                        // Si no alcanzo solicitud
                        $difDatos = 1;
                    }
                    $qty = $qtyAct;
                }

                // Cambios pruebas, se obtiene partida específica
                $ln_partida = '';
                $SQLSA = "SELECT clavepresupuestal FROM purchorderdetails WHERE podetailitem = '".$itemOntrst."'";
                $ErrMsgSA = "No se obtuvo la clavepresupuestal";
                $TransResultSA = DB_query($SQLSA, $db, $ErrMsgSA);
                if ($myrow = DB_fetch_array($TransResultSA)) {
                    $ln_partida = fnObtenerPartidaClavePresupuestal($db, $myrow['clavepresupuestal']);
                }

                $SQL = "UPDATE tb_solicitudes_almacen_detalle 
                        SET nu_cantidad = '$qty', ln_renglon = '$orden', ln_partida = '".$ln_partida."' 
                        WHERE nu_id_solicitud = '$folioSolA' AND ln_clave_articulo = '$item' 
                        AND ln_arctivo = 1 AND ln_ontransit = '".$itemOntrst."' AND nu_id_detalle != 0 ";

                $ErrMsg = "Error al actualizar la solicitud";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                
                array_push($valoresTabla, $item."@".$itemOntrst);
            } else {
                $valoresAgregar[] = array(
                    'item' =>  $item,
                    'itemdesc' =>  $desc,
                    'qty' => $qty,
                    'qtyOnTranist' => $onTranistQty,
                    'cvepre' =>  $cvepre,
                    'orden' =>  $orden,
                    'itemOntrst' => $itemOntrst
                );
            }
        }
        $ordenElemento++;
    }
    
    $separa= array();

    foreach ($valoresBase as $elimina) {
        if (in_array($elimina, $valoresTabla)) {
        } else {
            $datos= array();
            $separa= explode("@", $elimina);
            $datos= array("itemcode" => $separa[0], "iditem" => $separa[1]);
            
            array_push($valoresEliminar, $datos);
        }
    }

    $difDatos2 = fnAgregarItemSolAlmacen($idReq, $folioSolA, $valoresAgregar, $maxOrdenSolA, $almacen, $db);
    
    // Cambios pruebas, comentar
    fnEliminarItem($idReq, $folioSolA, $valoresEliminar, 'tb_solicitudes_almacen_detalle', 'nu_id_solicitud', 'ln_clave_articulo', 'ln_activo', 'nu_id_detalle', 'ln_renglon', 'nu_cantidad', $almacen, $itemOntrst, $db);
    //fnIndexarItem($idReq, $folioSolA, 'tb_solicitudes_almacen_detalle', 'ln_renglon', 'nu_id_solicitud', 'ln_arctivo', 'nu_id_detalle', 'ln_clave_articulo', $db);
    $enc2 = new Encryption;
    $url2 = "&idNoExistencia=> ". $folioSolA;
    $url2 = $enc2->encode($url2);
    $liga2= "URL=" . $url2;
    $link ="<b>Se ha actualizado exitosamente la solictud al almacén con folio: </b><a target='_blank' href='./almacen.php?' style='color: blue; '><u>".$folioSolA."</u></a>";
    $contenido = $link;

    // Cambios pruebas, validacion para datos
    if ($difDatos == 1 || $difDatos2 == 1) {
        // Encontro diferencias en insert
        $contenido = '<div><div class="fl mt30"><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i><b>Solicitud incompleta volver a Guardar</b></div></div>&nbsp;&nbsp;&nbsp;'.$contenido;
    }

    $result = true;
}

/**
 * If para retirar la informacion del anexo ligado con la requisicion
 * una vez agreagda limpiando los datos en la tabla de anexo
 * @date: 30.04.18
 * @author Desarrollo
 */
if ($option == 'retiraAnexo') {
    $info = $_POST;
    unset($info['option']);
    unset($SQL);
    unset($contenido);
    unset($result);
    unset($RootPath);
    unset($ErrMsg);
    unset($Mensaje);
    $SQL = '';
    $contenido = '';
    $result = false;
    $RootPath = '';
    $ErrMsg = 'Ocurrió un error al momento de generar la información';
    $Mensaje = 'Ocurrió un error al momento de generar la información';
    // ue, tagref, dependencia, idRequisicion, nuAnexoTecnico
    $sqlType = "SELECT `typeid` as tipo FROM `systypescat` WHERE  `typename` = 'Anexo Tecnico' ";
    $resultType = DB_query($sqlType, $db);
    $tipoTransaccion = DB_fetch_array($resultType)['tipo'];
    DB_Txn_Begin($db);
    try {
        $sql = "UPDATE `tb_cnfg_anexo_tecnico` SET `nu_requisicion` = 0, `ind_status` = 2, nu_orden_requisicion = 0 WHERE `nu_type` = '".$tipoTransaccion."' 
            AND `nu_tagref` = '".$info['tagref']."' AND `nu_ue` = '".$info['ue']."' 
            AND `nu_requisicion` = '".$info['idRequisicion']."' AND `nu_anexo` = '".$info['nuAnexoTecnico']."' ";
        $result = DB_query($sql, $db);
        $SQL = $sql;
        if ($result == true) {
            DB_Txn_Commit($db);
            $Mensaje = 'Se retiro con éxito el anexo técnico #'.$info['nuAnexoTecnico'];
            $result = true;
        } else {
            DB_Txn_Rollback($db);
        }
    } catch (Exception $e) {
        $Mensaje .= '<br> Error: ' . $e->getMessage();
        $ErrMsg .= '<br> Error: ' . $e->getMessage();
        DB_Txn_Rollback($db);
    }
}

function fnAgregarItemNoExistencia($idReq, $folioNoE, $arregloAgregar, $maxOrdenNoE, $db)
{
    $info = array();

    for ($i=0; $i < count($arregloAgregar); $i++) {
        $maxOrdenNoE++;
        $item = $arregloAgregar[$i]['item'];
        $itemdesc = $arregloAgregar[$i]['itemdesc'];
        $qty = $arregloAgregar[$i]['qty'];
        $cvepre = $arregloAgregar[$i]['cvepre'];
        $orden = $arregloAgregar[$i]['orden'];
        $iditem = $arregloAgregar[$i]['iditem'];

        $cadenaNoExist = "(".$idReq.",".$folioNoE.",'PZA',1,".$orden.",'".$item."','".$itemdesc."',".$qty.",'".$cvepre."','".$cvepre."', '".$iditem."')";

        $SQLInsrt="INSERT INTO tb_no_existencia_detalle (nu_id_requisicion, nu_id_no_existencia, ln_unidad_medida, ln_activo, ln_renglon, ln_item_code, txt_item_descripcion, nu_cantidad, ln_partida_esp, clavepresupuestal, podetailitem) VALUES ".$cadenaNoExist;

        $ErrMsgInsrt = "Error al agregar un item a la solicitud";
        $TransResultInsrt = DB_query($SQLInsrt, $db, $ErrMsgInsrt);
    }
}

function fnAgregarItemSolAlmacen($idReq, $folioSolA, $arregloAgregar, $maxOrdenSolA, $almacen, $db)
{
    $info = array();
    $difDatos = 0;
    for ($i=0; $i < count($arregloAgregar); $i++) {
        $maxOrdenSolA++;
        $item = $arregloAgregar[$i]['item'];
        $itemdesc = $arregloAgregar[$i]['itemdesc'];
        $qty = $arregloAgregar[$i]['qty'];
        $qtyOnTrst = $arregloAgregar[$i]['qtyOnTranist'];
        $cvepre = $arregloAgregar[$i]['cvepre'];
        $orden = $arregloAgregar[$i]['orden'];
        $itemOntrst = $arregloAgregar[$i]['itemOntrst'];

        //print_r($cadenaSolAlmacen);

        // Cambios pruebas, agregar variable
        $qtyAct = fnOnTranist($idReq, $folioSolA, $item, $qty, $almacen, $itemOntrst, $db);
        if ($qtyAct != $qty) {
            // Si no alcanzo solicitud
            $difDatos = 1;
        }
        $qty = $qtyAct;

        // Cambios pruebas, se obtiene partida específica
        $ln_partida = '';
        $SQLSA = "SELECT clavepresupuestal FROM purchorderdetails WHERE podetailitem = '".$itemOntrst."'";
        $ErrMsgSA = "No se obtuvo la clavepresupuestal";
        $TransResultSA = DB_query($SQLSA, $db, $ErrMsgSA);
        if ($myrow = DB_fetch_array($TransResultSA)) {
            $ln_partida = fnObtenerPartidaClavePresupuestal($db, $myrow['clavepresupuestal']);
        }

        $cadenaSolAlmacen = "(".$folioSolA.",'PZA',".$orden.",1,'".$item."','".$itemdesc."',".$qty.", '".$itemOntrst."', '".$ln_partida."')";

        // Cambios pruebas, se cambio el insert abajo de la actualizacion
        $SQLInsrt="INSERT INTO tb_solicitudes_almacen_detalle (nu_id_solicitud, ln_unidad_medida, ln_renglon, ln_arctivo, ln_clave_articulo, txt_descripcion, nu_cantidad, ln_ontransit, ln_partida) VALUES ".$cadenaSolAlmacen;
        $ErrMsgInsrt = "Error al agregar un item a la solicitud";
        $TransResultInsrt = DB_query($SQLInsrt, $db, $ErrMsgInsrt);
    }
    // Cambios pruebas, regresar informacion
    return $difDatos;
}

// funcion que elimina registros consideran la tabla y datos como parametros
// esta funcion es general para cualquier opcion de eliminar basado en el ID de la tabla
function fnEliminarItem($idReq, $folio, $arregloEliminar, $tabla, $campoBusquedaFolio, $campoItem, $campoStatus, $campoIdTabla, $campoRenglon, $campoCantidad, $almacen, $itemOntrst, $db)
{
    // Recorrer registros a eliminar
    foreach ($arregloEliminar as $item) {
        $cantidadOntransit = 0;

        // validar que se va a eliminar el detalle de la No Existencia y modificar lo que esta en transito
        if ($tabla == 'tb_solicitudes_almacen_detalle') {
            // Obtener la cantidad a regresar en ontransit
            $SQL = "SELECT IFNULL(nu_cantidad, 0) as nu_cantidad 
                    FROM tb_solicitudes_almacen_detalle 
                    WHERE ln_ontransit= '".$item["iditem"]."' AND ln_arctivo=1";

            $ErrMsg = "Error al obtener cantidad de un item ";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            if (DB_num_rows($TransResult) > 0) {
                while ($myrow = DB_fetch_array($TransResult)) {
                    $cantidadOntransit = $myrow['nu_cantidad'];
                }

                // Actualizar la cantidad solicitiada
                $SQLOntransit = "UPDATE locstock 
                                SET ontransit = ontransit - '".($cantidadOntransit)."' 
                                WHERE stockid = '".$item["itemcode"]."' and loccode ='".$almacen."'";

                $ErrMsgOntransit = "No se obtuvo informacion";
                $TransResultOntransit = DB_query($SQLOntransit, $db, $ErrMsgOntransit);
            }

            $SQL="UPDATE ".$tabla." 
                SET ln_arctivo = 0, ".$campoRenglon." = 0 
                WHERE ln_ontransit= '".$item["iditem"]."'";
        } else {
            $SQL="UPDATE ".$tabla." 
                SET ".$campoStatus." = 0, ".$campoRenglon." = 0 
                WHERE ".$campoBusquedaFolio." = '".$folio."' AND ".$campoItem." = '".$item["itemcode"]."' 
                AND ".$campoIdTabla." != 0 AND ".$campoRenglon."= '".$item["idtem"]."'";

            $SQL="UPDATE ".$tabla." 
                SET ln_activo = 0, ln_renglon = 0 
                WHERE podetailitem= '".$item["iditem"]."'";
        }

        $ErrMsg = "Error al eliminar un item ";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        if ($almacen != '') {
            //fnOnTranist ($idRequ, $folioSolA, $item, $qtyItem, $idalmacen, $itemOntrst, $db)
        }
    }
}

function fnIndexarItem($idReq, $folio, $tabla, $campoIndexar, $campoBusquedaFolio, $campoStatus, $campoIdTabla, $campoItem, $db)
{
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

/**
 * Obtiene el disponible de un articulo en almacen
 * @param  [type] $db       Basde de datos
 * @param  [type] $stockid  Código de Articulo
 * @param  [type] $loccode  Código de Almacén
 * @return [type] $cantidad Cantidad Disponible
 */
function fnDisponibleArticulo($db, $stockid = "", $almacen = "")
{
    $cantidad = 0;

    $SQL="SELECT (quantity - ontransit) AS disponible, stockid 
    FROM locstock WHERE stockid = '".$stockid."' AND loccode ='".$almacen."' ORDER BY stockid ASC";
    $error = "No se obtuvo disponible del articulo ".$stockid;
    $result = DB_query($SQL, $db, $error);
    while ($myrow = DB_fetch_array($result)) {
        $cantidad = $myrow['disponible'];
    }

    return $cantidad;
}

// funcion que actualiza la cantidad en apartado de almacen
function fnOnTranist($idRequ, $folioSolA, $item, $qtyItem, $idalmacen, $itemOntrst, $db)
{
    $info = array();
    /*$SQLitemOntrst = "SELECT ontransit ,quantity, (quantity - ontransit) AS stockDisponible FROM locstock WHERE stockid = '".$item."' and loccode ='".$idalmacen."'  ";
    $ErrMsgitemOntrst = "No se obtuvo informacion";
    $TransResultitemOntrst = DB_query($SQLitemOntrst, $db, $ErrMsgitemOntrst);
    while ($myrow = DB_fetch_array($TransResultitemOntrst)) {
        $qtyStck = $myrow['quantity'];
        $qtyOnT = $myrow['ontransit'];
        $qtyStockDisponible = $myrow['stockDisponible'];
    }*/
    $SQLitemOntrst = "SELECT nu_folio, nu_id_requisicion, ln_clave_articulo, nu_cantidad, ln_ontransit, ontransit ,quantity, (quantity - ontransit) AS stockDisponible, purchorderdetails.quantityord
    FROM tb_solicitudes_almacen 
    INNER JOIN tb_solicitudes_almacen_detalle ON (tb_solicitudes_almacen.nu_folio = tb_solicitudes_almacen_detalle.nu_id_solicitud AND ln_arctivo = 1) 
    INNER JOIN locstock ON (tb_solicitudes_almacen.ln_almacen = locstock.loccode AND ln_clave_articulo = locstock.stockid AND loccode ='".$idalmacen."' ) 
    JOIN purchorderdetails ON purchorderdetails.podetailitem = tb_solicitudes_almacen_detalle.ln_ontransit
    WHERE tb_solicitudes_almacen.nu_id_requisicion = ".$idRequ." AND nu_folio = ".$folioSolA." AND stockid = '".$item."' AND tb_solicitudes_almacen_detalle.ln_ontransit = '".$itemOntrst."' AND ln_arctivo = 1";
    $ErrMsgitemOntrst = "No se obtuvo informacion";
    $TransResultitemOntrst = DB_query($SQLitemOntrst, $db, $ErrMsgitemOntrst);
    if (DB_num_rows($TransResultitemOntrst) > 0) {
        while ($myrow = DB_fetch_array($TransResultitemOntrst)) {
            $qtySolAl = $myrow['nu_cantidad'];
            $onTrnst = $myrow['ontransit'];
            $itemOntransit = $myrow['ln_ontransit'];
            $qtyOnT = $myrow['quantity'];
            $qtyStockDisponible = $myrow['stockDisponible'];

            // echo "\n qtySolAl: \n".$qtySolAl."\n"; // Anterior
            // echo "\n onTrnst: \n".$onTrnst."\n";
            // echo "\n qtyOnT: \n".$qtyOnT."\n";
            
            // Cambios pruebas
            $disponible = fnDisponibleArticulo($db, $item, $idalmacen);
            if (($disponible + $qtySolAl) < $qtyItem) {
                // Obtener solicitud de lo disponible
                $qtyItem = ($disponible + $qtySolAl);
            }
            // Se regresa lo apartado enteriormente
            $SQLOntransit = "UPDATE locstock SET ontransit = ontransit - '".($qtySolAl)."' WHERE stockid = '".$item."' and loccode ='".$idalmacen."'";
            $ErrMsgOntransit = "No se obtuvo informacion";
            $TransResultOntransit = DB_query($SQLOntransit, $db, $ErrMsgOntransit);
            // Se regresa lo apartado enteriormente
            $SQLOntransit = "UPDATE locstock SET ontransit = ontransit + '".($qtyItem)."' WHERE stockid = '".$item."' and loccode ='".$idalmacen."'";
            $ErrMsgOntransit = "No se obtuvo informacion";
            $TransResultOntransit = DB_query($SQLOntransit, $db, $ErrMsgOntransit);
        }
    } else {
        // Cambios pruebas
        $disponible = fnDisponibleArticulo($db, $item, $idalmacen);
        if (($disponible) < $qtyItem) {
            // Obtener solicitud de lo disponible
            $qtyItem = ($disponible);
        }
        // Se regresa lo apartado enteriormente
        $SQLOntransit = "UPDATE locstock SET ontransit = ontransit + '".($qtyItem)."' WHERE stockid = '".$item."' and loccode ='".$idalmacen."'";
        $ErrMsgOntransit = "No se obtuvo informacion";
        $TransResultOntransit = DB_query($SQLOntransit, $db, $ErrMsgOntransit);
    }
    return $qtyItem;
    // if($qtyStockDisponible > 0){
    //         if($itemOntrst == $itemOntransit){
    //             $qtyItem = $qtyItem - $qtySolAl;
    //         }
            
    //         $qtyItem= $qtyItem + $onTrnst;
            
    //         $SQLOntransit = "UPDATE locstock SET ontransit = '".$qtyItem."' WHERE stockid = '".$item."' and loccode ='".$idalmacen."'";
    //         $ErrMsgOntransit = "No se obtuvo informacion";
    //         $TransResultOntransit = DB_query($SQLOntransit, $db, $ErrMsgOntransit);
    //         $SQL = "UPDATE tb_solicitudes_almacen_detalle SET ln_ontransit = '".$itemOntrst."' WHERE nu_id_solicitud = '$folioSolA' AND ln_clave_articulo = '".$item."' AND ln_arctivo = 1 AND nu_id_detalle != 0";
    //         $ErrMsg = "No se obtuvo informacion";
    //         $TransResult = DB_query($SQL, $db, $ErrMsg);
    // }
}

function fnFeriadoOfin($ndias, $db)
{
    $fecha= date("Y-m-d", strtotime(' +'.$ndias.' day'))." "."00:00:00";
    $SQL="  SELECT FinDeSemana,Feriado FROM  DWH_Tiempo where Fecha='".$fecha."' ORDER BY FinDeSemana DESC LIMIT 1 ";
    $ErrMsg = "No se obtuvo datos.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $feriado='';
    $fin='';
    while ($myrow = DB_fetch_array($TransResult)) {
                $feriado=$myrow['Feriado'];
                $fin=$myrow['FinDeSemana'];
    }
    if (($feriado=='1') || ($fin=='1')) {
        $ndias=$ndias+1;
        return  (fnFeriadoOfin($ndias, $db));
    } else {
        $fecha=   date("d-m-Y", strtotime(' +'.$ndias.' day'));
        return $fecha;
    }
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);

echo json_encode($dataObj);
