<?php
/**
 * Modelo para kardex o stocks
 * @category     ABC
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

$PageSecurity = 11;
include('includes/session.inc');
$title = _('Bienes y Servicios');
include('includes/header.inc');
$funcion=80;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include "includes/SecurityUrl.php";
?>
<link rel="stylesheet" href="css/listabusqueda.css" />
<?php

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$mensaje_emergente= "";
$procesoterminado= 0;
$bloqueoError=0;
$flag=0;
$n = 0;
$m = 0;

//var_dump($_POST);

if (isset($_GET['StockID'])) {
    $StockID =trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
    $StockID =trim(strtoupper($_POST['StockID']));
} else {
    $StockID = '';
}

if (isset($_POST['BarCode'])) {
    $BarCode = $_POST['BarCode'];
} else {
    $BarCode='';
}

//echo 'prod:'.($_GET['StockID']);
if (isset($_GET['PONumber'])) {
    $PONumber =trim(strtoupper($_GET['PONumber']));
} elseif (isset($_POST['PONumber'])) {
    $PONumber =trim(strtoupper($_POST['PONumber']));
} else {
    $PONumber = 0;
}

if (isset($_GET['frompage'])) {
    $frompage =trim(($_GET['frompage']));
} elseif (isset($_POST['PONumber'])) {
    $frompage =trim(($_POST['frompage']));
} else {
    $frompage = '';
}

if (isset($_GET['SelectBom'])) {
    $SelectBom =trim(($_GET['SelectBom']));
} elseif (isset($_POST['SelectBom'])) {
    $SelectBom =trim(($_POST['SelectBom']));
} else {
    $SelectBom = '';
}

if (isset($_GET['ModifyOrderNumber'])) {
    $ModifyOrderNumber = trim(($_GET['ModifyOrderNumber']));
} elseif (isset($_POST['ModifyOrderNumber'])) {
    $ModifyOrderNumber = trim(($_POST['ModifyOrderNumber']));
} else {
    $ModifyOrderNumber = '';
}

// valida si es que existe el codigo en la base

$flagModifica = false;
if (isset($StockID)) {
    $sql = "SELECT COUNT(stockid) FROM stockmaster WHERE stockid='".$StockID."'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0]==0) { $New=1; }
    else if(!empty($_GET['modificar']) || !empty($_POST['flagModifica'])){
        $flagModifica = !comprubaCambios($db, $StockID);
    }
}

$justfilename="";
if (isset($_FILES['fichatecnica']) and $_FILES['fichatecnica']['name'] !='') {
    $UploadTheFile = 'Yes';
    $filename = $rootpath . '/erpdistribucion/productlist/'.$StockID . '_' .$_FILES['fichatecnica']['name'];
    
    if (strtoupper(substr(trim($_FILES['fichatecnica']['name']), strlen($_FILES['fichatecnica']['name'])-3))!='PDF') {
        prnMsg(_('Solo archivos pdf son soportados para la ficha tecnica '), 'warn');
        $UploadTheFile ='No';
    }
    
    if ($UploadTheFile=='Yes') {
        $result  =  move_uploaded_file($_FILES['fichatecnica']['tmp_name'], $filename);
        $justfilename = $StockID . '_' .$_FILES['fichatecnica']['name'];
    }
}

if (isset($_FILES['ItemPicture']) and $_FILES['ItemPicture']['name'] !='') {
    $result    = $_FILES['ItemPicture']['error'];
    $UploadTheFile = 'Yes';
    $filename = $_SESSION['part_pics_dir'] . '/' . $StockID . '.jpg';
    
    if (strtoupper(substr(trim($_FILES['ItemPicture']['name']), strlen($_FILES['ItemPicture']['name'])-3))!='JPG') {
        prnMsg(_('Solo archivos jpg son soportados - un archivo con terminacion jpg es esperado'), 'warn');
        $UploadTheFile ='No';
    } elseif ($_FILES['ItemPicture']['size'] > ($_SESSION['MaxImageSize']*1024)) {
        prnMsg(_('El tamano de archivo esta sobre el maximo permitido. El tama�o maximo en KB es') . ' ' . $_SESSION['MaxImageSize'], 'warn');
        $UploadTheFile ='No';
    } elseif ($_FILES['ItemPicture']['type'] == "text/plain") {
        prnMsg(_('Solo archivos de tipo graficos pueden ser subidos'), 'warn');
            $UploadTheFile ='No';
    } elseif (file_exists($filename)) {
        prnMsg(_('Intentando sobreescribir una archivo de imagen'), 'warn');
        $result = unlink($filename);
        if (!$result) {
            prnMsg(_('La imagen actual no puede ser reemplazada'), 'error');
            $UploadTheFile ='No';
        }
    }

    if ($UploadTheFile=='Yes') {
        $result  =  move_uploaded_file($_FILES['ItemPicture']['tmp_name'], $filename);
        $message = ($result)?_('File url') ."<a href='". $filename ."'>" .  $filename . '</a>' : _('Ocurrio un error al cargar el archivo.');
    }
}

if (isset($Errors)) {
    unset($Errors);
}
$Errors = array();
$InputError = 0;

// inicia el evento submit
if (isset($_POST['submit'])) {
    $i=1;
    if (strlen($StockID)==0) {
        $InputError = 1;
        //prnMsg(_('Falta proporcionar el código del producto'), 'error');
        $mensaje_emergente.= '<p>Falta proporcionar el Código del producto.</p>';
        $procesoterminado= 3;
        $Errors[$i] = 'StockID';
        $i++;
    }
    if (!isset($_POST['Description']) or strlen($_POST['Description']) > 51 or strlen($_POST['Description'])==0) {
        $InputError = 1;
        //prnMsg(_('La descripción del producto debe ser de nomas de 150 caracteres') . '. ' . _('No puede tener una longitud igual a cero') . ' - ' . _('la descripción es necesaria'), 'error');
        $mensaje_emergente.= '<p>Falta proporcionar la Descripción corta del produto.</p>';
        $procesoterminado= 3;
        $Errors[$i] = 'Description';
        $i++;
    }
    if (strlen($_POST['LongDescription'])==0) {
        $InputError = 1;
        //prnMsg(_('Falta proporcionar la descripción larga del producto'), 'error');
        $mensaje_emergente.= '<p>Falta proporcionar la Descripción larga del producto.</p>';
        $procesoterminado= 33;
        $Errors[$i] = 'LongDescription';
        $i++;
    }
    if (strlen($_POST['LongDescription']) > 251) {
        $InputError = 1;
        //prnMsg(_('Falta proporcionar la descripción larga del producto'), 'error');
        $mensaje_emergente.= '<p>La descripción larga del producto debe ser maximo de 250 caracteres .</p>';
        $procesoterminado= 33;
        $Errors[$i] = 'LongDescription';
        $i++;
    }
    if (!isset($_POST['marca']) or strlen($_POST['marca']) > 50 /*OR strlen($_POST['marca'])==0*/) {
        $InputError = 1;
        //prnMsg(_('La marca del producto debe tener menos de 50 caracteres'), 'error');
        $mensaje_emergente.= '<p>La marca del producto debe tener menos de 50 caracteres.</p>';
        $procesoterminado= 3;
        $Errors[$i] = 'marca';
        $i++;
    }
    if (!isset($_POST['stockautor']) or strlen($_POST['stockautor']) > 100) {
        $InputError = 1;
        prnMsg(_('El autor del producto debe tener menos de 10 caracteres') . '. ' . _('No puede ir vacia'), 'error');
        $Errors[$i] = 'stockautor';
        $i++;
    }
    if (/*strstr($StockID,' ') OR*/ strstr($StockID, "'") or strstr($StockID, '+') or strstr($StockID, "\\") or strstr($StockID, "\"") or strstr($StockID, '&') /*OR strstr($StockID,'.') OR strstr($StockID,'"')*/) {
        $InputError = 1;
        //prnMsg(_('La clave del producto no debe contener carateres como ') . " ' & + \" \\ " . _(''), 'error');
        $mensaje_emergente.= "<p>El código del producto no debe contener carateres como ' & + \" \\ .</p>";
        $procesoterminado= 3;
        $Errors[$i] = 'StockID';
        $i++;
        $StockID='';
    }
    if ($_POST['MBFlag'] == "0") {
        $InputError = 1;
        $mensaje_emergente.= '<p>Falta proporcionar el Tipo del producto.</p>';
        $procesoterminado= 3;
        $Errors[$i] = 'MBFlag';
        $i++;
    }
    if ($_POST['PartidaID'] == "0") {
        $InputError = 1;
        $mensaje_emergente.= '<p>Falta proporcionar la Partida Específica del producto.</p>';
        $procesoterminado= 3;
        $Errors[$i] = 'PartidaID';
        $i++;
    }
    if ($_POST['eq_stockid'] == "0") {
        $InputError = 1;
        $mensaje_emergente.= '<p>Falta proporcionar la Clave CABMS del producto.</p>';
        $procesoterminado= 3;
        $Errors[$i] = 'eq_stockid';
        $i++;
    }
    if ($_POST['MBFlag'] == "B"){
        if (strlen($_POST['Familia']) == 0 || $_POST['Familia'] == '') {
            $InputError = 1;
            $mensaje_emergente.= '<p>Falta proporcionar la Familia del producto.</p>';
            $procesoterminado= 3;
            $Errors[$i] = 'Familia';
            $i++;
        }
    }else{
        $_POST['Familia'] = "0";
    }
    if ($_POST['Units'] == "0") {
        $InputError = 1;
        $mensaje_emergente.= '<p>Falta proporcionar la Unidad de Medida del producto.</p>';
        $procesoterminado= 3;
        $Errors[$i] = 'Units';
        $i++;
    }
    if (strlen($_POST['Units']) >20) {
        $InputError = 1;
        prnMsg(_('La unidad de medida debe ser de menos de 20 caracteres'), 'error');
        $Errors[$i] = 'Units';
        $i++;
    }
    if (strlen($_POST['BarCode']) >50) {
        $InputError = 1;
        prnMsg(_('El código de barras debe ser de menos de 50 caracteres'), 'error');
        $Errors[$i] = 'BarCode';
        $i++;
    }
    if (!is_numeric($_POST['Volume'])) {
        $InputError = 1;
        prnMsg(_('El volumen del producto debe estar definido en metros cubicos y debe ser un numero'), 'error');
        $Errors[$i] = 'Volume';
        $i++;
    }
    if ($_POST['Volume'] <0) {
        $InputError = 1;
        prnMsg(_('El volumen debe ser positivo'), 'error');
        $Errors[$i] = 'Volume';
        $i++;
    }
    if (!is_numeric($_POST['KGS'])) {
        $InputError = 1;
        prnMsg(_('El peso en KGs debe ser un numero'), 'error');
        $Errors[$i] = 'KGS';
        $i++;
    }
    if ($_POST['KGS']<0) {
        $InputError = 1;
        prnMsg(_('El peso del producto debe ser positivo'), 'error');
        $Errors[$i] = 'KGS';
        $i++;
    }
    if (!is_numeric($_POST['EOQ'])) {
        $InputError = 1;
        prnMsg(_('La cantidad de orden economica debe ser un numero'), 'error');
        $Errors[$i] = 'EOQ';
        $i++;
    }
    if ($_POST['EOQ'] <0) {
        $InputError = 1;
        prnMsg(_('La cantidad de orden economica debe ser positiva'), 'error');
        $Errors[$i] = 'EOQ';
        $i++;
    }
    if ($_POST['Controlled']==0 and $_POST['Serialised']==1) {
        $InputError = 1;
        prnMsg(_('Si el producto es serializado tambien debe definirse como serializado') . '. ' . _('un numero de control') . ' - ' . _('mediente un numero de lote/serie/pieza es posible solo cuando el producto es controlado') . '. ' . _('El control serializado requiere de un lote o serie') . '. ' . _('') . ', ' . _('por lo tanto el producto debe estar serializado y controlado'), 'error');
        $Errors[$i] = 'Serialised';
        $i++;
    }
    if ($_POST['NextSerialNo']!=0 and $_POST['Serialised']==0) {
        $InputError = 1;
        prnMsg(_('El producto solo puede generar numero de serie automatico si es serializado'), 'error');
        $Errors[$i] = 'NextSerialNo';
        $i++;
    }
    if ($_POST['NextSerialNo']!=0 and $_POST['MBFlag']!='M') {
        $InputError = 1;
        prnMsg(_('Solo genera numeros de serie automaticos si el producto es ensamblado'), 'error');
        $Errors[$i] = 'NextSerialNo';
        $i++;
    }
    if (($_POST['MBFlag']=='A' or $_POST['MBFlag']=='K' or $_POST['MBFlag']=='D' or $_POST['MBFlag']=='G') and $_POST['Controlled']==1) {
        //$InputError = 1;
        //prnMsg(_('Assembly/Kitset/Phantom/Service/Labour items cannot also be controlled items') . '. ' . _('Assemblies/Dummies/Phantom and Kitsets are not physical items and batch/serial control is therefore not appropriate'),'error');
        //$Errors[$i] = 'Controlled';
        //$i++;
    }
    /*
    se comenta debido a que ya no es util para el sistema
    if (trim($_POST['CategoryID'])=='' or trim($_POST['CategoryID'])=='*****' or trim($_POST['CategoryID'])=='+++++') {
        $InputError = 1;
        //prnMsg(_('No se ha definido una categoría de inventario. Todos los productos deben pertenecer a una categoría de inventario valida,'), 'error');
        $mensaje_emergente.= '<p>No se ha definido una categoría de inventario. Todos los productos deben pertenecer a una categoría de inventario valida.</p>';
        $procesoterminado= 2;
        $Errors[$i] = 'CategoryID';
        $i++;
    }
    
    $sql = "SELECT categoryid,stocktype FROM stockcategory WHERE categoryid = '" . $_POST['CategoryID']."'";
    $ErrMsg = _('Las categorías de las acciones no podran ser recuperados porque');
    $DbgMsg = _('El SQL que fallo para recuperar las categor�as de acciones fue');
    //$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
    $result = DB_query($sql, $db);
    if ($myrow = DB_fetch_row($result)) {
        $_POST['StockType'] = $myrow[1];
    } else {
        $_POST['StockType'] = '';
    }
    */
    /*
    se comenta debido a que ya no es util para el sistema
    if (!is_numeric($_POST['Pansize'])) {
        $InputError = 1;
        prnMsg(_('La cantidad de decimales debe ser numerico'), 'error');
        $Errors[$i] = 'Pansize';
        $i++;
    }*/
    /*
    se comenta debido a que ya no es util para el sistema
    if (!is_numeric($_POST['ShrinkFactor'])) {
        $InputError = 1;
        prnMsg(_('El margen de utilidad debe ser un numero'), 'error');
        $Errors[$i] = 'ShrinkFactor';
        $i++;
    }*/

    if ($InputError !=1) {
        if ($_POST['Serialised']==1) {
            $_POST['DecimalPlaces']=0;
        }
        if (empty($_POST['New']) && empty($New) && $flagModifica) {
            $sql = "SELECT mbflag,
              controlled,
              serialised
          FROM stockmaster WHERE stockid = '$StockID'";
            $MBFlagResult = DB_query($sql, $db);
            $myrow = DB_fetch_row($MBFlagResult);
            $OldMBFlag = $myrow[0];
            $OldControlled = $myrow[1];
            $OldSerialised = $myrow[2];

            $sql = "SELECT SUM(locstock.quantity) FROM locstock WHERE stockid='$StockID'";
            $result = DB_query($sql, $db);
            $stkqtychk = DB_fetch_row($result);

            if ($OldMBFlag != $_POST['MBFlag']) {
                if (($OldMBFlag == 'M' or $OldMBFlag=='B') and ($_POST['MBFlag']=='A' or $_POST['MBFlag']=='K' or $_POST['MBFlag']=='D' or $_POST['MBFlag']=='G')) {
                    if ($stkqtychk[0]!=0 and $OldMBFlag!='G') {
                        $InputError=1;
                        $bloqueoError = 1;
                        //prnMsg(_('No se puede realizar el cambio de tipo de producto') . ' ' . $OldMBFlag . ' ' . _('a') . ' ' . $_POST['MBFlag'] . ' ' . _('cuando existe productos en algun almacén') . '. ' . _('Existen ') . ' ' . $stkqtychk[0] .  ' ' . _('en demanda'), 'errror');
                        $mensaje_emergente.= '<p>No se puede realizar el cambio de Tipo, cuando existen productos en algun almacén.</p>';
                        $procesoterminado= 3;
                    }
                    if ($_POST['Controlled']==1) {
                        $InputError=1;
                        $bloqueoError = 1;
                        //prnMsg(_('No se puede realizar el cambio de tipo de producto') . ' ' . $OldMBFlag . ' ' . _('a') . ' ' . $_POST['MBFlag'] . ' ' . _('cuando existe un lote de control dado de alta') . '. ' . _(''), 'error');
                        $mensaje_emergente.= '<p>No se puede realizar el cambio de tipo cuando existe un lote de control dado de alta.</p>';
                        $procesoterminado= 3;
                    }
                }
                if ($_POST['MBFlag']=='K') {
                    $sql = "SELECT quantity-qtyinvoiced
            FROM salesorderdetails
            WHERE stkcode = '$StockID'
            AND completed=0";

                    $result = DB_query($sql, $db);
                    $ChkSalesOrds = DB_fetch_row($result);
                    if ($ChkSalesOrds[0]!=0) {
                        $InputError = 1;
                        $bloqueoError = 1;
                        //prnMsg(_('no se puede realizar el cambio si existen pedidos de venta pendientes para este producto') . '. ' . _('Existen ') .' ' . $ChkSalesOrds[0] . ' '. _('productos en pedidos de venta sin facturar'), 'error');
                        $mensaje_emergente.= '<p>No se puede realizar el cambio si existen pedidos de venta pendientes para este producto, existen productos en pedidos de venta sin facturar.</p>';
                        $procesoterminado= 3;
                    }
                }
                if ($_POST['MBFlag']=='K' or $_POST['MBFlag']=='A' or $_POST['MBFlag']=='D') {
                    $sql = "SELECT quantityord-quantityrecd
                    FROM purchorderdetails
                    WHERE itemcode = '$StockID'
                    AND completed=0";

                    $result = DB_query($sql, $db);
                    $ChkPurchOrds = DB_fetch_row($result);
                    if ($ChkPurchOrds[0]!=0) {
                        $InputError = 1;
                        $bloqueoError = 1;
                        //prnMsg(_('El tipo de producto no puede cambiar'). ' ' . $_POST['MBFlag'] . ' '. _('cuando existen ordenes de compra pendientes') . '. ' . _('Existe'). ' ' . $ChkPurchOrds[0] . ' '. _('que aun no han sido recibidos'). 'error');
                        $mensaje_emergente.= '<p>El tipo de producto no puede cambiar, cuando existen ordenes de compra pendientes.</p>';
                        $procesoterminado= 3;
                    }
                }
                if (($OldMBFlag=='M' or $OldMBFlag =='K' or $OldMBFlag=='A' or $OldMBFlag=='G') and ($_POST['MBFlag']=='B' or $_POST['MBFlag']=='D')) {
                    $sql = "SELECT COUNT(*) FROM bom WHERE parent = '$StockID'";
                    $result = DB_query($sql, $db);
                    $ChkBOM = DB_fetch_row($result);
                    if ($ChkBOM[0]!=0) {
                        $InputError = 1;
                        $bloqueoError = 1;
                        //prnMsg(_('El cambio del producto no se puede realizar a'). ' ' . $_POST['MBFlag'] . ' '. _('cuando existe en paquete o como parte de ensamble') . '. ' . _(''), 'error');
                        $mensaje_emergente.= '<p>El cambio del producto no se puede realizar, cuando existe en paquete o como parte de ensamble.</p>';
                        $procesoterminado= 3;
                    }
                }
                if (($OldMBFlag=='M' or $OldMBFlag =='B' or $OldMBFlag=='D' or $OldMBFlag=='G') and ($_POST['MBFlag']=='A' or $_POST['MBFlag']=='K')) {
                    $sql = "SELECT COUNT(*) FROM bom WHERE component = '$StockID'";
                    $result = DB_query($sql, $db);
                    $ChkBOM = DB_fetch_row($result);
                    if ($ChkBOM[0]!=0) {
                        $InputError = 1;
                        $bloqueoError = 1;
                        //prnMsg(_('El cambio del producto no se puede realizar a'). ' ' . $_POST['MBFlag'] . ' '. _('cuando existe en paquete o como parte de ensamble') . '. ' . _(''), 'error');
                        $mensaje_emergente.= '<p>El cambio del producto no se puede realizar, cuando existe en paquete o como parte de ensamble.</p>';
                        $procesoterminado= 3;
                    }
                }
            }

            if ($OldControlled != $_POST['Controlled'] and $stkqtychk[0]!=0) {
                $InputError=1;
                //prnMsg(_('No se puede realizar el cambio de controlado a no controlado'), 'error');
                $mensaje_emergente.= '<p>No se puede realizar el cambio de controlado a no controlado.</p>';
                $procesoterminado= 3;
            }
            if ($OldSerialised != $_POST['Serialised'] and $stkqtychk[0]!=0) {
                $InputError=1;
                //prnMsg(_('No se puede realizar el cambio de sereializado a no serializado'), 'error');
                $mensaje_emergente.= '<p>No se puede realizar el cambio de sereializado a no serializado.</p>';
                $procesoterminado= 3;
            }

            // sin errores guarda
            if ($InputError == 0) {
                $sql = "UPDATE stockmaster
                SET longdescription='" . $_POST['LongDescription'] . "',
              description='" . $_POST['Description'] . "',
              manufacturer='" . $_POST['marca'] . "',
              stockautor='" . $_POST['stockautor'] . "',
              discontinued='" . (empty($_POST['Discontinued'])?'0':$_POST['Discontinued']) . "',
              controlled='" . $_POST['Controlled'] . "',
              serialised='" . $_POST['Serialised']."',
              perishable='" . $_POST['Perishable']."',
              categoryid='" . substr($_POST['PartidaID'],0,3) . "',
              percentfactorigi='" . $_POST['percentfactorigi'] . "',
              units='" . $_POST['Units'] . "',
              mbflag='" . $_POST['MBFlag'] . "',
              eoq='" . $_POST['EOQ'] . "',
              volume='" . $_POST['Volume'] . "',
              kgs='" . $_POST['KGS'] . "',
              large='" . $_POST['large'] . "',
              width='" . $_POST['width'] . "',
              height='" . $_POST['height'] . "',
              ";
                if ($justfilename!="") {
                    $sql.="fichatecnica = '".$justfilename."',
                ";
                }
                            
                $sql.=  "barcode='" . $_POST['BarCode'] . "',
              discountcategory='" . $_POST['DiscountCategory'] . "',
              taxcatid='" . $_POST['TaxCat'] . "',
              decimalplaces='" . $_POST['DecimalPlaces'] . "',
              appendfile='" . $_POST['ItemPDF'] . "',
              shrinkfactor='" . (empty($_POST['ShrinkFactor'])?'0':$_POST['ShrinkFactor']) . "',
              pansize='" . (empty($_POST['Pansize'])?'0':$_POST['Pansize']) . "',
              nextserialno='" . $_POST['NextSerialNo'] . "',
              idclassproduct='" . $_POST['IDclassproduct'] . "',
              taxcatidret ='" . $_POST['TaxCatRet'] . "',
              idetapaflujo = '" . $_POST['etapaproduccion'] . "',
              flagadvance='".$_POST['flagadvance']."',
              pkg_type = '" . $_POST['pkg_type'] . "',
              eq_stockid =  '" . $_POST['eq_stockid'] . "',
              eq_conversion_factor =  '" . $_POST['eq_conversion_factor'] . "',
              stockupdate=0,
              stocksupplier = '".$_POST['stocksupplier']."',
              unitequivalent = '".$_POST['unitequivalent']."',
              nu_cve_familia = '".$_POST['Familia']."'
              WHERE stockid='$StockID'";

                $ErrMsg = _('La actualizacion del producto no se realizo');
                $DbgMsg = _('El SQL que fallo fue');
                $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                //echo "<pre>$sql";
                $result = DB_query(
                    "DELETE FROM stockitemproperties
                    WHERE stockid ='" . $StockID . "'",
                    $db
                );

                for ($i=0; $i<$_POST['PropertyCounter']; $i++) {
                    if ($_POST['PropType' . $i] ==2) {
                        if ($_POST['PropValue' . $i]=='on') {
                            $_POST['PropValue' . $i]=1;
                        } else {
                            $_POST['PropValue' . $i]=0;
                        }
                    }
                    $result = DB_query("INSERT INTO stockitemproperties (stockid,
                           stkcatpropid,
                           value)
          VALUES ('" . $StockID . "'," . $_POST['PropID' . $i] . ",'" . $_POST['PropValue' . $i] . "')", $db);
                }
                
                //prnMsg(_('El código de producto') . ' ' . $StockID . ' ' . _('ha sido actualizado'), 'success');
                $mensaje_emergente= '<p>El código de producto '.$StockID.' ha sido actualizado.</p>';
                $procesoterminado= 1;
                
                if ($PONumber>0) {
                    //actualiza las ordenes de compra que tenian el glcode diferente
                    $sql = "SELECT stockmaster.description,
              stockmaster.units,
              stockmaster.netweight,    
              stockmaster.kgs,
              stockmaster.volume,
              stockcategory.stockact,
              stockmaster.manufacturer
            FROM stockmaster inner join stockcategory on stockcategory.categoryid = stockmaster.categoryid
            WHERE stockid='" . $StockID . "'";
                    $ErrMsg = _('El producto no tiene proveedor configurado') . ': ' . $StockID . ' ' . _('no hay resultados');
                    $DbgMsg = _('El SQL utilizado es');
                    $ResultProveedorcompra =DB_query($sql, $db, $ErrMsg, $DbgMsg);
                    $myrow = DB_fetch_row($ResultProveedorcompra);
                    
                    $SQL = "UPDATE purchorderdetails
            SET itemdescription='" . $myrow[0] . "',
              itemno='" . $myrow[1] . "',
              nw ='" . $myrow[2] . "',
              gw='" . $myrow[3] . "',
              cuft='" . $myrow[4] . "',
              glcode='" . $myrow[5] . "'
            WHERE orderno >= " . $PONumber."
              AND itemcode='".$StockID."'";
                    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                    echo $SQL;
                    
                    $optional_params = "";
                    if (empty($ModifyOrderNumber) == false) {
                        $optional_params .= "&ModifyOrderNumber=$ModifyOrderNumber";
                    }
                    
                    //redireccionar a la pagina de recepcion de compras
                    echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/".$frompage."?PONumber=" . $PONumber. $optional_params . "'>";
                    echo '<div class="centre">' . _('Tu deberas automaticamente  ser redireccionado a la pagina para dar de alta una Sucursal del Cliente') .
                    '. ' . _('Si esto no sucede') .' (' . _('Si tu explorador no soporta META Refresh') . ') ' .
                    "<a href='" . $rootpath . '/'.$frompage.'?' . SID . 'PONumber='. $PONumber . '>'._('Regresar a Recepcion').'</a></div>';
                    exit ;
                }
                if (strlen($SelectBom)!=0) {
                    echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/".$frompage."?Select=" . $SelectBom . "'>";
                    echo '<div class="centre">' . _('Tu deberas automaticamente  ser redireccionado a la pagina para dar de alta una Sucursal del Cliente') .
                    '. ' . _('Si esto no sucede') .' (' . _('Si tu explorador no soporta META Refresh') . ') ' .
                    "<a href='" . $rootpath . '/'.$frompage.'?' . SID . 'Select='. $SelectBom . '>'._('Regresar a explosion').'</a></div>';
                    exit ;
                }
            }
        } else {
            $pulgadas = "";
            $pulgadas2 = "'";
            $_POST['Description'] = str_replace($pulgadas, 'in', $_POST['Description']);
            $_POST['Description'] = str_replace($pulgadas2, 'in', $_POST['Description']);
            $_POST['LongDescription'] = str_replace($pulgadas, 'in', $_POST['LongDescription']);
            $_POST['LongDescription'] = str_replace($pulgadas2, 'in', $_POST['LongDescription']);
            $result = DB_query("SELECT stockid FROM stockmaster WHERE stockid='" . $StockID ."'", $db);
            if (DB_num_rows($result)==1) {
                //prnMsg(_('La clave '.$StockID.' ya se encuentra en la base de datos, el sistema no admite codigos duplicados'), 'error');
                $InputError = 1;
                //$mensaje_emergente.= '<p>La clave '.$StockID.' ya se encuentra en la base de datos, el sistema no admite codigos duplicados.</p>';
                $mensaje_emergente.= '<p>El código '.$StockID.' ya está asignado a un producto.</p>';
                $procesoterminado= 3;
                //exit;
            } else {
                $sql = "INSERT INTO stockmaster (
              stockid,
              description,
              longdescription,
              manufacturer,
              stockautor,
              categoryid,
              percentfactorigi,
              units,
              mbflag,
              eoq,
              discontinued,
              controlled,
              serialised,
              perishable,
              volume,
              kgs,
              large,
              width,
              height,
              barcode,
              discountcategory,
              taxcatid,
              decimalplaces,
              appendfile,
              shrinkfactor,
              pansize,
              idclassproduct,
              taxcatidret,
              idetapaflujo,
              flagadvance,
              fichatecnica,
              pkg_type,
              stocksupplier,
              eq_stockid,
              eq_conversion_factor,
              unitequivalent,
              flagcommission,
              nu_cve_familia
              )
            VALUES ('".$StockID."',
              '" . $_POST['Description'] . "',
              '" . $_POST['LongDescription'] . "',
              '" . $_POST['marca'] . "',
              '" . $_POST['stockautor'] . "',
              '" . substr($_POST['PartidaID'],0,3) . "',
              '" . $_POST['percentfactorigi'] . "',
              '" . $_POST['Units'] . "',
              '" . $_POST['MBFlag'] . "',
              '" . $_POST['EOQ'] . "',
              '" . (empty($_POST['Discontinued'])?'0':$_POST['Discontinued']) . "',
              '" . $_POST['Controlled'] . "',
              '" . $_POST['Serialised']. "',
              '" . $_POST['Perishable']. "',
              '" . $_POST['Volume'] . "',
              '" . $_POST['KGS'] . "',
              '" . $_POST['large'] . "',
              '" . $_POST['width'] . "',
              '" . $_POST['height'] . "',
              '" . $_POST['BarCode'] . "',
              '" . $_POST['DiscountCategory'] . "',
              '" . $_POST['TaxCat'] . "',
              '" . $_POST['DecimalPlaces']. "',
              '" . $_POST['ItemPDF']. "',
              '" . (empty($_POST['ShrinkFactor'])?'0':$_POST['ShrinkFactor']) . "',
              '" . (empty($_POST['Pansize'])?'0':$_POST['Pansize']) . "',
              '" . $_POST['IDclassproduct'] . "',
              '" . $_POST['TaxCatRet'] . "',
              '" . $_POST['etapaproduccion'] . "',
              '" . $_POST['flagadvance'] . "',
              '".$justfilename."',
              '" . $_POST['pkg_type'] . "',
              '".$_POST['stocksupplier']."',
              '".$_POST['eq_stockid']."',
              '".$_POST['eq_conversion_factor']."',
              '".$_POST['unitequivalent']."',
              '0',
              '".$_POST['Familia']."'
              )";
                /*if($_POST['Familia'] != ''){
                    $sql = "INSERT INTO stockclass (idclass,class,Ind_activo) VALUES ()";

                }*/
                $ErrMsg =  _('El código de producto no se puede agregar');
                $DbgMsg = _('El SQL utilizado es');
                //echo $sql;
                $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                //echo "<pre>se inserto ...";

                //echo "<pre>entro ...para almacenes";
                $sql = "INSERT INTO locstock (loccode,
                      stockid)
                SELECT locations.loccode,
                '" . $StockID . "'
                FROM locations";

                $ErrMsg =  _('El código') . ' ' . $StockID .  ' ' . _('no se agrego a los almacenes');
                $DbgMsg = _('El SQL utilizado es');
                $InsResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                
                $sql = "INSERT INTO stockcostsxlegal (lastupdatedate, stockid, lastcost, avgcost, legalid, lastpurchaseqty)
                SELECT NOW(), '" . $StockID . "', 0, 0, legalbusinessunit.legalid, 0
                FROM legalbusinessunit";

                $ErrMsg =  _('El código') . ' ' . $StockID .  ' ' . _('no se agrego a tabla de stockcostsxlegal');
                $DbgMsg = _('El SQL utilizado es');
                $InsResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                
                $sql = "INSERT INTO stockcostsxlegalnew (lastupdatedate, stockid, lastcost, avgcost, legalid)
                SELECT NOW(),'" . $StockID . "',0,0,legalbusinessunit.legalid
                FROM legalbusinessunit";
                
                $ErrMsg =  _('El código') . ' ' . $StockID .  ' ' . _('no se agrego a tabla de stockcostsxlegal');
                $DbgMsg = _('El SQL utilizado es');
                $InsResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                
                $sql = "INSERT INTO stockcostsxtag (lastupdatedate, stockid, lastcost, avgcost, tagref)
                SELECT NOW(),'" . $StockID . "',0,0,tags.tagref
                FROM tags";

                $ErrMsg =  _('El código') . ' ' . $StockID .  ' ' . _('no se agrego a tabla de stockcostsxtag');
                $DbgMsg = _('El SQL utilizado es');
                $InsResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

                //prnMsg(_('Se agregó el registro '. $StockID .' con éxito'), 'success');
                $mensaje_emergente= '<p>Se agregó el registro '. $StockID .' con éxito.</p>';
                $procesoterminado= 1;

                unset($_POST['LongDescription']);
                unset($_POST['Description']);
                unset($_POST['marca']);
                unset($_POST['EOQ']);
                unset($_POST['CategoryID']);
                unset($_POST['Units']);
                unset($_POST['MBFlag']);
                unset($_POST['Discontinued']);
                unset($_POST['Controlled']);
                unset($_POST['Serialised']);
                unset($_POST['Perishable']);
                unset($_POST['Volume']);
                unset($_POST['KGS']);
                //unset($_POST['BarCode']);
                unset($_POST['ReorderLevel']);
                unset($_POST['DiscountCategory']);
                // Deja el campo de decimales para la siguiente alta para facilitar captura...
                //unset($_POST['DecimalPlaces']);
                unset($_POST['ItemPDF']);
                unset($_POST['ShrinkFactor']);
                unset($_POST['Pansize']);
                unset($_POST['IDclassproduct']);
                unset($_POST['eq_stockid']);
                unset($_POST['eq_conversion_factor']);
                unset($_POST['unitequivalent']);
                unset($_POST['Familia']);
                unset($StockID);
            }
        }
        // envia al panel 
        //echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=SelectProduct.php'>";
    } else {
        //prnMsg(_($mensaje_emergente), 'error');
    }
} elseif (isset($_POST['delete'])) {
    $CancelDelete = 0;

    $sql= "SELECT COUNT(*) FROM stockmoves WHERE stockid='$StockID'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0]>0) {
        $CancelDelete = 1;
        $bloqueoError = 1;
        $mensaje = '<br>' . _('Hay') . ' ' . $myrow[0] . ' ' . _('movimientos de E/S');
        //prnMsg(_('No puede eliminar este producto debido a que ya tiene movimientos de E/S') . $mensaje, 'warn');
        $mensaje_emergente.= '<p>No puede eliminar este producto debido a que ya tiene movimientos asignados.</p>';
        $procesoterminado= 2;
    } else {
        $sql= "SELECT COUNT(*) FROM bom WHERE component='$StockID'";
        $result = DB_query($sql, $db);
        $myrow = DB_fetch_row($result);
        if ($myrow[0]>0) {
            $CancelDelete = 1;
            $bloqueoError = 1;
            $mensaje = '<br>' . _('Hay') . ' ' . $myrow[0] . ' ' . _(' kits a las que pertenece este producto');
            //prnMsg(_('No puede eliminar este producto debido a que pertenece aun kit o un ensamblado') . $mensaje, 'warn');
            $mensaje_emergente.= '<p>No puede eliminar este producto debido a que pertenece aun kit o un ensamblado.</p>';
            $procesoterminado= 2;
        } else {
            $sql= "SELECT COUNT(*) FROM salesorderdetails WHERE stkcode='$StockID'";
            $result = DB_query($sql, $db);
            $myrow = DB_fetch_row($result);
            if ($myrow[0]>0) {
                $CancelDelete = 1;
                $bloqueoError = 1;
                $mensaje = '<br>' . _('Hay') . ' ' . $myrow[0] . ' ' . _('en pedidos de venta');
                //prnMsg(_('No puede eliminar este producto debido a que esta utilizado en pedidos de venta') . $mensaje, 'warn');
                $mensaje_emergente.= '<p>No puede eliminar este producto debido a que esta utilizado en pedidos de venta.</p>';
                $procesoterminado= 2;
            } else {
                $sql= "SELECT COUNT(*) FROM salesanalysis WHERE stockid='$StockID'";
                $result = DB_query($sql, $db);
                $myrow = DB_fetch_row($result);
                if ($myrow[0]>0) {
                    $CancelDelete = 1;
                    $bloqueoError = 1;
                    $mensaje = '<br>' . _('Hay') . ' ' . $myrow[0] . ' ' . _('registros de analisis de venta');
                    //prnMsg(_('No puede eliminar este producto debido a que existe en analisis de ventas') . $mensaje, 'warn');
                    $mensaje_emergente.= '<p>No puede eliminar este producto debido a que existe en analisis de ventas.</p>';
                    $procesoterminado= 2;
                } else {
                    $sql= "SELECT COUNT(*) FROM purchorderdetails WHERE itemcode='$StockID'";
                    $result = DB_query($sql, $db);
                    $myrow = DB_fetch_row($result);
                    if ($myrow[0]>0) {
                        $CancelDelete = 1;
                        $bloqueoError = 1;
                        $mensaje = '<br>' . _('Hay') . ' ' . $myrow[0] . ' ' . _('ordenes de compra con este producto');
                        //prnMsg(_('No puede eliminar este producto debido a que existe en una orden de compra con este') . $mensaje, 'warn');
                        $mensaje_emergente.= '<p>No puede eliminar este producto debido a que existe en una orden de compra.</p>';
                        $procesoterminado= 2;
                    } else {
                        $sql = "SELECT SUM(quantity) AS qoh FROM locstock WHERE stockid='$StockID'";
                        $result = DB_query($sql, $db);
                        $myrow = DB_fetch_row($result);
                        if ($myrow[0]!=0) {
                            $CancelDelete = 1;
                            $bloqueoError = 1;
                            $mensaje = '<br>' . _('Hay') . ' ' . $myrow[0] . ' ' . _('unidades de este producto');
                            //prnMsg(_('No puede eliminar este producto debido a que tiene existencia en inventario') . $mensaje, 'warn');
                            $mensaje_emergente.= '<p>No puede eliminar este producto debido a que tiene existencia en inventario.</p>';
                            $procesoterminado= 2;
                        } else {
                            $sql = "SELECT count(*) as trans FROM loctransfers WHERE stockid = '$StockID' and shipqty > recqty";
                            $result = DB_query($sql, $db);
                            $myrow = DB_fetch_row($result);
                            if ($myrow[0]!=0) {
                                $CancelDelete = 1;
                                $bloqueoError = 1;
                                $mensaje = '<br>' . _('Hay') . ' ' . $myrow[0] . ' ' . _('traspasos pendientes');
                                //prnMsg(_('No puede eliminar este producto debido a que tiene traspasos pendientes...') . $mensaje, 'warn');
                                $mensaje_emergente.= '<p>No puede eliminar este producto debido a que tiene traspasos pendientes...</p>';
                                $procesoterminado= 2;
                            }
                        }
                    }
                }
            }
        }
    }
    if ($CancelDelete==0) {
        $result = DB_Txn_Begin($db);
        /*Deletes LocStock records*/
        $sql ="DELETE FROM locstock WHERE stockid='$StockID'";
        $result=DB_query($sql, $db, _('No es posible eliminar del almacén'), '', true);
        /*Deletes Price records*/
        $sql ="DELETE FROM prices WHERE stockid='$StockID'";
        $result=DB_query($sql, $db, _('No es posible eliminar el precio asignado'), '', true);
        /*and cascade deletes in PurchData */
        $sql ="DELETE FROM purchdata WHERE stockid='$StockID'";
        $result=DB_query($sql, $db, _('No es posible eliminar la información relacionada a una venta'), '', true);
        /*and cascade delete the bill of material if any */
        $sql = "DELETE FROM bom WHERE parent='$StockID'";
        $result=DB_query($sql, $db, _('No es posible eliminar la información relacionaa a una compra'), '', true);
        $sql="DELETE FROM stockmaster WHERE stockid='$StockID'";
        $result=DB_query($sql, $db, _('No es posible eliminar el bien o servicio'), '', true);

        $result = DB_Txn_Commit($db);
        //prnMsg(_('Se eliminó el registro ') . $StockID . _(' con éxito'), 'success');
        $mensaje_emergente.= '<p>Se eliminó el registro '. $StockID .' con éxito.</p>';
        $procesoterminado= 1;

        unset($_POST['LongDescription']);
        unset($_POST['Description']);
        unset($_POST['marca']);
        unset($_POST['EOQ']);
        unset($_POST['CategoryID']);
        unset($_POST['Units']);
        unset($_POST['MBFlag']);
        unset($_POST['Discontinued']);
        unset($_POST['Controlled']);
        unset($_POST['Serialised']);
        unset($_POST['Perishable']);
        unset($_POST['Volume']);
        unset($_POST['KGS']);
        //unset($_POST['BarCode']);
        unset($_POST['ReorderLevel']);
        unset($_POST['DiscountCategory']);
        unset($_POST['TaxCat']);
        unset($_POST['DecimalPlaces']);
        unset($_POST['ItemPDF']);
        unset($_POST['IDclassproduct']);
        unset($_POST['eq_stockid']);
        unset($_POST['eq_conversion_factor']);
        unset($StockID);
        unset($_SESSION['SelectedStockItem']);
        unset($_POST['unitequivalent']);
        unset($_POST['Familia']);
    }
}

echo '<form id="ItemForm" name="ItemForm" enctype="multipart/form-data" method="post" action="' . $_SERVER['PHP_SELF'] . '?' .SID .'">';
$linkfichaT = '';

if (isset($_POST['Description'])) {
    $Description = $_POST['Description'];
} else {
    $Description ='';
}

if (isset($_POST['LongDescription'])) {
    $LongDescription = ($_POST['LongDescription']);
} else {
    $LongDescription ='';
}

if (!isset($_POST['Discontinued']) or $_POST['Discontinued']=='') {
    $_POST['Discontinued']=0;
}

/*if (!isset($_POST['MBFlag']) or $_POST['MBFlag']=='') {
    $_POST['MBFlag']='B';
}*/
?>
<div align="left">
  <!--Panel Busqueda-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne" style="margin-left: 20px;">
            Información Agregar/Modificar
          </a>
      </h4>
    </div>
    <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <div class="row clearfix">
            <div class="col-md-4 hide">
                <?php
                // cuando es nuevo
                if ($StockID!='') {
                    if($InputError != 1){
                        //muestraDatosProducto($db, $StockID);
                        if($flagModifica){
                            echo '<input type="hidden" name="flagModifica" value="1">';
                            muestraDatosProducto($db, $StockID);
                        }else if(!empty($StockID)){
                            muestraDatosProducto($db, $StockID);
                            $bloqueoError = 1;
                        }
                    }
                }
                ?>
            </div>
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Bien/Servicio: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="MBFlag" name="MBFlag" class="form-control MBFlag" onchange="fnChangeType()">
                            <option value="0">Sin selección...</option>
                            <?php
                            $sql = "SELECT distinct stockflag, stocknameflag FROM stocktypeflag WHERE sn_activo = '1' ORDER BY stocknameflag ASC";
                            $result = DB_query($sql, $db);
                            while ($myrow=DB_fetch_array($result)) {
                                if ($myrow['stockflag']==$_POST['MBFlag']) {
                                    if($myrow['stockflag']=='B'){
                                        $flag = 1;
                                    }
                                    echo '<option selected VALUE="'. $myrow['stockflag'] . '">' . $myrow['stocknameflag'] . '</option>';
                                } else {
                                    echo '<option VALUE="'. $myrow['stockflag'] . '">' . $myrow['stocknameflag'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Partida Específica: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="PartidaID" name="PartidaID" class="form-control PartidaID">
                            <option value="0">Sin selección...</option>
                            <?php
                            if($_POST['MBFlag'] == 'B' || $_POST['MBFlag'] == 'D' ){
                                $sqlWhereMbflag = "";
                                if($_POST['MBFlag'] == 'B'){
                                    $sqlWhereMbflag = "WHERE ccap = 2 OR ccap = 5 ";
                                }
                                if($_POST['MBFlag'] == 'D'){
                                    $sqlWhereMbflag = " WHERE ccap = 3 AND ccon != 7 ";
                                }
                                $sql = "SELECT distinct partidacalculada, CONCAT(partidacalculada, ' - ', descripcion) as descripcionPartidaEsp FROM tb_cat_partidaspresupuestales_partidaespecifica ".$sqlWhereMbflag." GROUP BY partidacalculada, descripcionPartidaEsp  ORDER BY partidacalculada ASC";
                                $result = DB_query($sql, $db);
                                while ($myrow=DB_fetch_array($result)) {
                                    if ($myrow['partidacalculada'] == $_POST['PartidaID']) {
                                        echo '<option selected VALUE="'. $myrow['partidacalculada'] . '">' . $myrow['descripcionPartidaEsp'] . '</option>';
                                    } else {
                                        echo '<option VALUE="'. $myrow['partidacalculada'] . '">' . $myrow['descripcionPartidaEsp'] . '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>CABMS: </label></span>                        
                    </div>
                    <div class="col-md-9">
                        <select id="eq_stockid" name="eq_stockid" class="form-control eq_stockid" >
                            <option value="0">Sin selección...</option>
                            <?php
                                if($_POST['PartidaID'] != null || $_POST['PartidaID'] != '' || $_POST['PartidaID'] !== 'undefined' || $_POST['PartidaID'] != '0' || $_POST['PartidaID'] != 0 || $POST['PartidaID'] != '-1' ){
                                    //$SQL = "SELECT DISTINCT eq_stockid FROM tb_partida_articulo WHERE partidaEspecifica = '".$_POST['PartidaID']."' ORDER BY eq_stockid ASC";
                                    $SQL="SELECT DISTINCT tpa.eq_stockid, CONCAT(tpa.eq_stockid, ' - ',tpa.descPartidaEspecifica) as descPartidaEspecifica,  tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada, tpa.partidaEspecifica, s.mbflag 
                                    FROM stockmaster s 
                                    INNER JOIN tb_partida_articulo tpa on (s.eq_stockid = tpa.eq_stockid)
                                    INNER JOIN tb_cat_partidaspresupuestales_partidaespecifica ON (tpa.partidaEspecifica = tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada) WHERE tpa.partidaEspecifica = '".$_POST['PartidaID']."' 
                                    GROUP BY tpa.eq_stockid, descPartidaEspecifica, tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada, tpa.partidaEspecifica ";
                                    $ErrMsg = "No se obtuvo el COG de Producto";
                                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                                    while ($myrow = DB_fetch_array($TransResult)) {
                                        if ($myrow['eq_stockid']==$_POST['eq_stockid']) {
                                            echo '<option selected VALUE="'. $myrow['eq_stockid'] . '">' .  $myrow['descPartidaEspecifica']. '</option>';
                                        } else {
                                            echo '<option VALUE="'. $myrow['eq_stockid'] . '">' .  $myrow['descPartidaEspecifica'].  '</option>';
                                        }
                                    }
                                } 
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4 hide">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Partida Generica: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="CategoryID" name="CategoryID" class="form-control CategoryID">
                            <option value="0">Sin selección...</option>
                            <?php
                            $sqlWhereMbflag = "";
                            if($_POST['MBFlag'] == 'B'){
                                $sqlWhereMbflag = "WHERE categoryid BETWEEN 200 AND 299 OR categoryid BETWEEN 500 AND 599 ";
                            }
                            if($_POST['MBFlag'] == 'D'){
                                $sqlWhereMbflag = " WHERE categoryid BETWEEN 300 AND 369 OR categoryid BETWEEN 380 AND 399 ";
                            }
                            $sql = "SELECT distinct categoryid, CONCAT(categoryid, ' - ', categorydescription) as categorydescription FROM stockcategory ".$sqlWhereMbflag." ORDER BY categoryid ASC";
                            $result = DB_query($sql, $db);
                            while ($myrow=DB_fetch_array($result)) {
                                if ($myrow['categoryid']==$_POST['CategoryID']) {
                                    echo '<option selected VALUE="'. $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
                                } else {
                                    echo '<option VALUE="'. $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="row clearfix">
            <div class="col-md-4">
                <?php
                // cuando es nuevo
                if ($StockID=='') {
                    $New = true;
                    $n = 1;
                    $m = 0;
                    $update=false;
                    echo '<input type="hidden" name="New" value="1">'. "\n";
                    //echo '<component-text-label label="Código 1:" id="StockID" name="StockID" maxlength="20" placeholder="Código" title="Código" value="'.$StockID.'"></component-text-label>';
                    echo '<component-text-label label="Código:" id="StockID" name="StockID" maxlength="20" placeholder="Código" title="Código" value="'.$StockID.'"></component-text-label>';
                    // cuando si existe y no hubo ningun error
                }else { // cuando existe pero hay un error
                    echo "<input class='form-control mtb10' type='Hidden' name='StockID' value='".$StockID."'>";
                    if($flagModifica){
                        $n = 1;
                        $m = 1;
                        echo '<input type="hidden" name="flagModifica" value="1">';
                        //muestraDatosProducto($db, $StockID);
                    }else if(!empty($StockID)){
                        $n = 0;
                        $m = 0;
                        //muestraDatosProducto($db, $StockID);
                        $bloqueoError = 1;
                    }
                    // fin de modificacion
                    echo '<component-text-label label="Código:" id="StockID" name="StockID" maxlength="20" placeholder="Código" readonly="true" title="Código" value="'.$StockID.'"></component-text-label>';
                }
                ?>
            </div>
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Familias: </label></span>
                    </div>
                    <div class="col-md-9">
                        <?php
                            if($_POST['MBFlag'] == 'D'){
                                if($_POST['Familia']=='0'){
                                    $_POST['Familia'] = '';
                                }
                            }
                            if($_POST['Familia']==''){
                                //echo '<input id="Familia" class="Familia form-control w100p" type="number" maxlength="5">';
                                echo '<input id="Familia" name="Familia" class="Familia form-control w100p" type="text" maxlength = "5" value="'.$_POST['Familia'].'" placeholder="Familia" onkeypress="return soloNumeros(event)">';
                            }else{
                                // fin de modificacion
                                echo '<input id="Familia" name="Familia" class="Familia form-control w100p" type="text" maxlength = "5" value="'.$_POST['Familia'].'" placeholder="Familia" onkeypress="return soloNumeros(event)">';
                            }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Estatus: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="Discontinued" name="Discontinued" class="form-control Discontinued">
                            <?php
                            if ($_POST['Discontinued']==0) {
                                echo '<option selected value=0>' . _('Activo') . '</option>';
                            } else {
                                echo '<option value=0>' . _('Activo') . '</option>';
                            }
                            if ($_POST['Discontinued']==1) {
                                echo '<option selected value=1>' . _('Inactivo') . '</option>';
                            } else {
                                echo '<option value=1>' . _('Inactivo') . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="row clearfix">
            <div class="col-md-4">
                <div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Unidad de Medida: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="Units" name="Units" class="form-control Units">
                            <option value="0">Sin selección...</option>
                            <?php
                            if($_POST['MBFlag'] == 'B' || $_POST['MBFlag'] == 'D' ){
                                $sqlWhereMbflag = "";
                                if($_POST['MBFlag'] == 'B'){
                                    $sqlWhereMbflag = "WHERE mbflag = 'B' ";
                                }
                                if($_POST['MBFlag'] == 'D'){
                                    $sqlWhereMbflag = " WHERE mbflag = 'D' ";
                                }
                                $sql = 'SELECT unitname FROM unitsofmeasure '.$sqlWhereMbflag.' ORDER by unitname';
                                $UOMResult = DB_query($sql, $db);
                                if (!isset($_POST['Units'])) {
                                    $UOMrow['unitname']=_('each');
                                }

                                while ($UOMrow = DB_fetch_array($UOMResult)) {
                                    if (isset($_POST['Units']) and $_POST['Units']==$UOMrow['unitname']) {
                                        echo '<option selected value="' . $UOMrow['unitname'] . '">' . $UOMrow['unitname'] . '</option>';
                                    } else {
                                        echo '<option value="' . $UOMrow['unitname'] . '">' . $UOMrow['unitname']  . '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <component-text-label label="Descripción Corta:" id="Description" name="Description" maxlength="50" placeholder="Descripción Corta" title="Descripción Corta" value="<?php echo $_POST['Description'] ?>"></component-text-label>
            </div>
            <div class="col-md-4">
                <component-textarea-label label="Descripción Larga: " id="LongDescription" name="LongDescription" placeholder="Descripción Larga" title="Descripción Larga" cols="3" rows="4" maxlength= "250" value="<?php echo $_POST['LongDescription'] ?>"></component-textarea-label>
            </div>
        </div>
        <br>
      </div>
    </div>
  </div>
</div>
<?php
echo '<table id="idTableStock"><tr><td><table style="display:none;">'. "\n";

if (isset($_POST['marca'])) {
    $marca = $_POST['marca'];
} else {
    $marca ='';
}

echo '<tr><td><b>Sección 1 </b></td><td><input class="botonVerde" type="button" value="Mostrar" onclick="mostrar1()">';
echo '<input class="botonVerde" type="button" value="Ocultar" onclick="ocultar1()"></tr>';
echo "<tr><td colspan='2'><table id='oculto' style='display:none;'>";
echo '<tr><td><span class="generalSpan mr5">' . _('Categoría Impuestos') . ':</span></td><td><select id="idTaxCat" class="mb10 form-control" name="TaxCat">';
$sql = 'SELECT taxcatid, taxcatname FROM taxcategories ORDER BY taxcatname';
$result = DB_query($sql, $db);

if (!isset($_POST['TaxCat'])) {
    $_POST['TaxCat'] = $_SESSION['DefaultTaxCategory'];
}

while ($myrow = DB_fetch_array($result)) {
    if ($_POST['TaxCat'] == $myrow['taxcatid']) {
        echo '<option selected value=' . $myrow['taxcatid'] . '>' . $myrow['taxcatname'] . '</option>';
    } else {
        echo '<option value=' . $myrow['taxcatid'] . '>' . $myrow['taxcatname'] . '</option>';
    }
}

echo '</select></td></tr>';
echo '<tr><td><span class="generalSpan">' . _('Categoría Impuestos Retenidos') . ':</span</td><td><select id="idTaxCatRet" class="form-control mb10" name="TaxCatRet">';
$sql = 'SELECT taxcatid, taxcatname FROM taxcategories ORDER BY taxcatname';
$result = DB_query($sql, $db);

if (!isset($_POST['TaxCatRet'])) {
    $_POST['TaxCatRet'] = 0;
}
echo '<option selected value=0>'._('Ninguno').'</option>';
while ($myrow = DB_fetch_array($result)) {
    if ($_POST['TaxCatRet'] == $myrow['taxcatid']) {
        echo '<option selected value=' . $myrow['taxcatid'] . '>' . $myrow['taxcatname'] . '</option>';
    } else {
        echo '<option value=' . $myrow['taxcatid'] . '>' . $myrow['taxcatname'] . '</option>';
    }
}
if (!isset($_POST['percentfactorigi'])) {
    $_POST['percentfactorigi']=0;
}
echo '</select></td></tr>';

echo '<tr><td><span class="generalSpan">' . _('Marca') . ':</span></td><td><select id="idMarca" class="form-control mb10" name="marca">';

$sql = 'SELECT *';
$sql .= ' FROM stockmanufacturer ';

$ErrMsg = _('El stock de Marcas no pudo ser recuperada porque');
$DbgMsg = _('El SQL utilizado fue');
$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
while ($myrow=DB_fetch_array($result)) {
    if (!isset($_POST['marca']) or $myrow['manufacturerid']==$_POST['marca']) {
        echo '<option selected VALUE="'. $myrow['manufacturerid'] . '">' . $myrow['manufacturer'];
    } else {
        echo '<option VALUE="'. $myrow['manufacturerid'] . '">' . $myrow['manufacturer'];
    }
}

echo '</td></tr>';
if (isset($_POST['stockautor'])) {
    $stockautor= $_POST['stockautor'];
} else {
    $stockautor ='';
}
echo '<tr><td><span class="generalSpan">' . _('Extra'). ':</span></td><td><input ' . (in_array('stockautor', $Errors) ?  'class="inputerror form-control mb10"' : 'class="form-control mb10"' ) .' type="Text" name="stockautor" size=52 maxlength=50 value="' . htmlentities($stockautor, ENT_QUOTES, _('ISO-8859-1')) . '"></td></tr>'."\n";
function select_files($dir, $label = '', $select_name = 'ItemPDF', $curr_val = '', $char_length = 60)
{
    $teller = 0;
    if (!file_exists($dir)) {
        mkdir($dir);
        chmod($dir, 0777);
    }
    if ($handle = opendir($dir)) {
        $mydir = "<select id='idItemPDF' class='form-control mb10' name=".$select_name.">\n";
        $mydir .= '<option VALUE=0>ninguno';
        if (isset($_POST['ItemPDF'])) {
            $curr_val = $_POST['ItemPDF'];
        } else {
            $curr_val .=  'none';
        }
        while (false !== ($file = readdir($handle))) {
            $files[] = $file;
        }
        closedir($handle);
        sort($files);
        foreach ($files as $val) {
            if (is_file($dir.$val)) {
                $mydir .= '<option VALUE='.$val;
                $mydir .= ($val == $curr_val) ? ' selected>' : '>';
                $mydir .= $val."\n";
                $teller++;
            }
        }
        $mydir .= "";
    }
    return $mydir;
}
if (!isset($_POST['ItemPDF'])) {
    $_POST['ItemPDF'] = '';
}
echo '<tr><td><span class="generalSpan">' . _('Adjuntar PDF') . ':</span>' . "\n</td><td>" . select_files('companies/' . $_SESSION['DatabaseName'] .'/pdf_append//', '', 'ItemPDF', $_POST['ItemPDF'], '60') . '</td></tr>'. "\n";
echo '<tr><td><span class="generalSpan">'. _('Ficha Técnica (.pdf)') . ':</span></td><td><input class="borderN form-control mb10" type="file" id="fichatecnica" name="fichatecnica"> &nbsp;&nbsp; <span>'.$linkfichaT.'</span></td></tr>';

echo '<tr><td><span class="generalSpan">' . _('Clases de Producto') . ':</span></td><td><select id="idIDclassproduct" class="form-control mb10" name="IDclassproduct">';
$sql = 'SELECT * FROM classproduct';
$result = DB_query($sql, $db);
while ($myrow=DB_fetch_array($result)) {
    if (!isset($_POST['IDclassproduct']) or $myrow['idclassproduct']==$_POST['IDclassproduct']) {
        echo '<option selected VALUE="'. $myrow['idclassproduct'] . '">' . $myrow['classdescription'];
    } else {
        echo '<option VALUE="'. $myrow['idclassproduct'] . '">' . $myrow['classdescription'];
    }
}

if (Havepermission($_SESSION['UserID'], 610, $db)==1) {
    echo '</select><a target="_blank" href="'. $rootpath . '/ABCClassproduct.php?' . SID . '">' . _('Agregar o Modificar Clases de Producto') . '</a></td></tr>';
}

if (!isset($_POST['EOQ']) or $_POST['EOQ']=='') {
    $_POST['EOQ']=0;
}

if (!isset($_POST['Volume']) or $_POST['Volume']=='') {
    $_POST['Volume']=0;
}
if (!isset($_POST['KGS']) or $_POST['KGS']=='') {
    $_POST['KGS']=0;
}
if (!isset($_POST['large']) or $_POST['large']=='') {
    $_POST['large']=0;
}
if (!isset($_POST['width']) or $_POST['width']=='') {
    $_POST['width']=0;
}
if (!isset($_POST['height']) or $_POST['height']=='') {
    $_POST['height']=0;
}

if (!isset($_POST['Controlled']) or $_POST['Controlled']=='') {
    $_POST['Controlled']=0;
}
if (!isset($_POST['Serialised']) or $_POST['Serialised']=='' || $_POST['Controlled']==0) {
    $_POST['Serialised']=0;
}
$sqlval = "SELECT *
      FROM stockmaster
      WHERE stockid = '".$StockID."'";
$resultval = DB_query($sqlval, $db);

if (!isset($_POST['DecimalPlaces']) or $_POST['DecimalPlaces']=='' or DB_num_rows($resultval) == 0) {
    if (isset($_POST['Units'])) {
        $sqldeci = "SELECT unitdecimal
          FROM unitsofmeasure
          WHERE unitname = '".$_POST['Units']."'";
        $resultdeci = DB_query($sqldeci, $db);
        $rowsdeci = DB_fetch_array($resultdeci);
        $_POST['DecimalPlaces'] = $rowsdeci['unitdecimal'];
    }
}
if (!isset($_POST['Pansize'])) {
    $_POST['Pansize']=0;
}
if (!isset($_POST['ShrinkFactor'])) {
    $_POST['ShrinkFactor']=0;
}
if (!isset($_POST['NextSerialNo'])) {
    $_POST['NextSerialNo']=0;
}

echo '<tr><td><span class="generalSpan">' . _('Cantidad de orden económico') . ':</span></td><td><input ' . (in_array('EOQ', $Errors) ?  'class="inputerror form-control mb10"' : '' ) .'   type="Text" class="number form-control mb10" name="EOQ" size=12 maxlength=10 value="' . $_POST['EOQ'] . '"></td></tr>';

echo '<tr><td><span class="generalSpan">' . _('Volumen Empaquetado (m3)') . ':</span></td><td><input ' . (in_array('Volume', $Errors) ?  'class="inputerror form-control mb10"' : '' ) .'   type="Text" class="number form-control mb10" name="Volume" size=12 maxlength=10 value="' . $_POST['Volume'] . '"></td></tr>';

echo '<tr><td><span class="generalSpan">' . _('Peso Empaquetado (Kgs)') . ':</span></td><td><input ' . (in_array('KGS', $Errors) ?  'class="inputerror form-control mb10"' : '' ) .'   type="Text" class="form-control number mb10" name="KGS" size=12 maxlength=10 value="' . $_POST['KGS'] . '"></td></tr>';

echo '<tr><td><span class="generalSpan">' . _('Alto') . '("):</span></td><td><input ' . (in_array('height', $Errors) ?  'class="inputerror form-control mb10"' : '' ) .'   type="Text" class="form-control number mb10" name="height" size=12 maxlength=10 value="' . $_POST['height'] . '"></td></tr>';

echo '<tr><td><span class="generalSpan">' . _('Ancho') . '("):</span></td><td><input ' . (in_array('width', $Errors) ?  'class="inputerror form-control mb10"' : '' ) .'   type="Text" class="number form-control mb10" name="width" size=12 maxlength=10 value="' . $_POST['width'] . '"></td></tr>';

echo '<tr><td><span class="generalSpan">' . _('Largo') . '("):</span></td><td><input ' . (in_array('large', $Errors) ?  'class="inputerror form-control mb10"' : '' ) .'   type="Text" class="number form-control mb10" name="large" size=12 maxlength=10 value="' . $_POST['large'] . '"></td></tr>';

echo '</table></td></tr>';
echo '<tr><td><b>Sección 2 </b></td><td><input class="botonVerde" type="button" value="Mostrar" onclick="mostrar2()">';
echo '<input class="botonVerde" type="button" value="Ocultar" onclick="ocultar2()"></tr>';

echo "<tr><td colspan='2'><table id='oculto2' style='display:none;'>";
if (Havepermission($_SESSION['UserID'], 406, $db)==1 or $New==true) {
    echo '<tr><td><span class="generalSpan">' . _('Control de lote, serie o batch') . ':</span></td><td><select id="idControlled" class="form-control mb10" name="Controlled">';
    
    if ($_POST['Controlled']==0) {
        echo '<option selected value=0>' . _('Sin Control') . '</option>';
    } else {
        echo '<option value=0>' . _('Sin Control') . '</option>';
    }
    if ($_POST['Controlled']==1) {
        echo '<option selected value=1>' . _('Controlado'). '</option>';
    } else {
        echo '<option value=1>' . _('Controlado'). '</option>';
    }
    echo '</select></td></tr>';
} else {
    echo '<tr><td colspan=2><input type="hidden" name="Controlled" value="'.$_POST['Controlled'].'"></td></tr> ';
}
if (Havepermission($_SESSION['UserID'], 405, $db)==1 or $New==true) {
    echo '<tr><td><span class="generalSpan">' . _('Serializado') . ':</span></td><td><select id="idSerialised" ' . (in_array('Serialised', $Errors) ?  'class="selecterror mb10 form-control"' : 'class="form-control mb10"' ) .'  name="Serialised">';

    if ($_POST['Serialised']==0) {
        echo '<Option selected value=0>' . _('No'). '</option>';
    } else {
        echo '<option value=0>' . _('No'). '</option>';
    }
    if ($_POST['Serialised']==1) {
        echo '<option selected value=1>' . _('Si') . '</option>';
    } else {
        echo '<option value=1>' . _('Si'). '</option>';
    }
    echo '</select><i>' . _('Nota') . ': ' . _('esto no tiene efecto si el producto no es controlado') . '</i></td></tr>';
} else {
    echo '<tr><td colspan=2><input type="hidden" name="Serialised" value="'.$_POST['Serialised'].'"></td></tr> ';
}

if ($_POST['Serialised']==1 and $_POST['MBFlag']=='M') {
    echo '<tr><td><span class="generalSpan">' . _('Next Serial No (>0 for auto numbering)') . ':</span></td><td><input ' . (in_array('NextSerialNo', $Errors) ?  'class="inputerror form-control mb10"' : 'class="form-control mb10"' ) .' type="text" name="NextSerialNo" size=15 maxlength=15 value="' . $_POST['NextSerialNo'] . '"><td></tr>';
} else {
    echo '<input type="hidden" name="NextSerialNo" value="0">';
}

if ($_POST['Perishable'] == "") {
    $_POST['Perishable'] = 0;
}
echo '<tr>';
echo '<td><span class="generalSpan">'._('Caducidad').':</span></td>';
echo '<td><input class="form-control mb10" type="text" name="Perishable" value="'.$_POST['Perishable'].'"></td>';
echo '</tr>';

if (empty($_POST['DecimalPlaces'])) {
    $_POST['DecimalPlaces']=2;
}
echo '<tr><td><span class="generalSpan">' . _('Despliega Decimales') . ':</span></td><td><input type="text" class="number form-control mb10" name="DecimalPlaces" size=1 maxlength=1 value="' . $_POST['DecimalPlaces'] . '"><td></tr>';

echo '<tr><td><span class="generalSpan">' . _('Código de barras') . ':</span></td><td><input class="form-control mb10" type="Text" name="BarCode" size=50 maxlength=50 value="' .$_POST['BarCode'] . '"></td></tr>';

if (isset($_POST['DiscountCategory'])) {
    $DiscountCategory = $_POST['DiscountCategory'];
} else {
    $DiscountCategory='';
}
echo '<tr><td><span class="generalSpan">' . _('Categoría Descuentos') . ':</span></td><td><input class="form-control mb10" type="Text" name="DiscountCategory" size=2 maxlength=2 value="' . $DiscountCategory . '"></td></tr>';

echo '<tr>
        <td><span class="generalSpan">' . _('Pan Size') . ':</span></td>
      <td><input type="Text" class="form-control number mb10" name="Pansize" size="6" maxlength="6" value=' . $_POST['Pansize'] . '></td>
  </tr> 
     <tr>
        <td><span class="generalSpan">' . _('Margen Utilidad') . ':</span></td>
      <td><input type="Text" class="form-control number mb10" name="ShrinkFactor" size="6" maxlength="6" value=' . $_POST['ShrinkFactor'] . '></td>
  </tr>
  <tr>
       <td><span class="generalSpan">' . _('Porcentaje IGI') . ':</span></td>
      <td><input type="Text" class="form-control number w40p fl mb10" name="percentfactorigi" size="6" maxlength="6" value=' . $_POST['percentfactorigi'] . '><span class="w20p mt5">'._('De 0% a 100%').'</span></td>
  </tr> 
    
    ';
echo '<tr>
    <td><span class="generalSpan">' . _('Fracc. Arancelaria') . ':</span></td>
    <td><input type="Text" class="form-control number mb10" name="stocksupplier" size="15" maxlength="15" value=' . $_POST['stocksupplier'] . '>'._('').'</td>
    </tr>';

$vb=0;
if (isset($_POST['eq_conversion_factor'])) {
    if (empty($_POST['eq_conversion_factor'])) {
        $vb=0;
    } else {
        $vb=$_POST['eq_conversion_factor'];
    }
} else {
    $vb=0;
}
echo '<tr>
    <td><span class="generalSpan">' . _('Factor de Conversión Producto Equivalente') . ':</span></td>
    <td><input type="Text" class="number form-control mb10" name="eq_conversion_factor" size="6" maxlength="6" value=' .$vb. '>'._('').'</td>
    </tr>';

echo '<tr>
    <td><span class="generalSpan">' . _('Unidad medida equivalente') . ':</span></td>
    <td><input class="form-control mb10" type="Text"  name="unitequivalent" size="20" maxlength="20" value=' . $_POST['unitequivalent'] . '>'._('').'</td>
    </tr>';

if (isset($_POST['pkg_type']) and $_POST['pkg_type']==1) {
    $selColor = "selected";
    $selBN = "";
} else {
    $selColor = "";
    $selBN = "selected";
}

if ($_SESSION['ShowColorInStocks'] == 1) {
    echo "<tr><td><span class='generalSpan'>" . _('Tipo') . ":</span></td><td>";
    $sqlcolor = "Select TipoEmpaquetado.pkg_type,
            TipoEmpaquetado.descripcion
         From TipoEmpaquetado";
    $resultcolor = DB_query($sqlcolor, $db);
    echo "<select id='idpkg_type' class='form-control mb10' name='pkg_type'>";
    while ($myrowcolor = DB_fetch_array($resultcolor)) {
        if ($_POST['pkg_type'] == $myrowcolor['pkg_type']) {
            echo '<option selected value="'.$myrowcolor['pkg_type'].'">'.$myrowcolor['descripcion'].'</option>';
        } else {
            if (!isset($_POST['pkg_type']) and $myrowcolor['pkg_type'] == 0) {
                echo '<option selected value="'.$myrowcolor['pkg_type'].'">'.$myrowcolor['descripcion'].'</option>';
            } else {
                echo '<option  value="'.$myrowcolor['pkg_type'].'">'.$myrowcolor['descripcion'].'</option>';
            }
        }
    }
    echo '</select>';
    echo "</td></tr>";
}

echo "<tr>";
echo "<td><span class='generalSpan'>" . _('Etapa Producción') . ":</span></td>";
echo "<td><select id='idEtapaproduccion' class='form-control mb10' name='etapaproduccion'>";
echo "<option value='0'>SIN ETAPA</option>";
$sql = 'SELECT idconcepto, nombre FROM prdconceptos ORDER BY nombre';
$result = DB_query($sql, $db);
while ($myrow = DB_fetch_array($result)) {
    if ($_POST['etapaproduccion'] == $myrow['idconcepto']) {
        echo '<option selected value=' . $myrow['idconcepto'] . '>' . $myrow['nombre'] . '</option>';
    } else {
        echo '<option value=' . $myrow['idconcepto'] . '>' . $myrow['nombre'] . '</option>';
    }
}
echo "</td>";
echo "</tr>";
if (!isset($_POST['flagadvance'])) {
    $_POST['flagadvance']=0;
}
//campo para validacion de si productos se considera como anticipo en ventas
echo '<tr><td><span class="generalSpan">' . _('Anticipo en Ventas') . ':</span></td><td><select id="idFlagadvance" class="form-control mb10" name="flagadvance">';
if ($_POST['flagadvance']==1) {
    echo '<option selected value=1>' . _('SI') . '</option>';
} else {
    echo '<option value=1>' . _('SI') . '</option>';
}
if ($_POST['flagadvance']==0) {
    echo '<option selected value=0>' . _('NO') . '</option>';
} else {
    echo '<option value=0>' . _('NO') . '</option>';
}
echo '</select></td></tr>';
echo '</table></td></tr>';
echo '</table></td><td></td></tr></table><div class="centre">';

if (!isset($_POST['CategoryID'])) {
    $_POST['CategoryID'] = '';
}
echo '<table class="mt10" style="display: none;"><tr><th colspan="2"><span>' . _('Propiedades de Categoría del Producto') . '</span></th></tr>';
$sql = "SELECT stkcatpropid,
        label,
        controltype,
        defaultvalue
    FROM stockcatproperties
    WHERE categoryid ='" . $_POST['CategoryID'] . "'
    AND reqatsalesorder =0
    ORDER BY stkcatpropid";
// $PropertiesResult = DB_query($sql, $db);
$PropertyCounter = 0;
$PropertyWidth = array();
$PropertiesResult = "";
while ($PropertyRow=DB_fetch_array($PropertiesResult)) {
    $PropValResult = DB_query(
        "SELECT value FROM stockitemproperties
        WHERE stockid='" . $StockID . "'
        AND stkcatpropid =" . $PropertyRow['stkcatpropid'],
        $db
    );
    $PropValRow = DB_fetch_row($PropValResult);
    $PropertyValue = $PropValRow[0];

    echo '<input type="hidden" name="PropID' . $PropertyCounter . '" value=' .$PropertyRow['stkcatpropid'] .'>';
    echo '<tr><td>' . $PropertyRow['label'] . '</td><td>';
    switch ($PropertyRow['controltype']) {
        case 0:
            echo '<input type="textbox" name="PropValue' . $PropertyCounter . '" size="20" maxlength="250" value="' . $PropertyValue . '">';
            break;
        case 1:
            $OptionValues = explode(',', $PropertyRow['defaultvalue']);
            echo '<select name="PropValue' . $PropertyCounter . '">';
            foreach ($OptionValues as $PropertyOptionValue) {
                if ($PropertyOptionValue == $PropertyValue) {
                    echo '<option selected value="' . $PropertyOptionValue . '">' . $PropertyOptionValue . '</option>';
                } else {
                    echo '<option value="' . $PropertyOptionValue . '">' . $PropertyOptionValue . '</option>';
                }
            }
            echo '</select>';
            break;
        case 2:
            echo '<input type="checkbox" name="PropValue' . $PropertyCounter . '"';
            if ($PropertyValue==1) {
                echo '"checked"';
            }
            echo '>';
            break;
    }
    echo '<input type="hidden" name="PropType' . $PropertyCounter .'" value=' . $PropertyRow['controltype'] . '>';
    echo '</td></tr>';
    $PropertyCounter++;
}
echo '</table>';
echo '<input type="hidden" name="PropertyCounter" value=' . $PropertyCounter . '>';
?>
<div class="panel panel-default">
    <div class="panel-body" align="center" id="divBotones" name="divBotones">
        <a id="linkPanelAdecuaciones" name="linkPanelAdecuaciones" href="SelectProduct.php" class="btn btn-default botonVerde glyphicon glyphicon-share-alt"> Regresar</a>
        <component-button type="button" id="cancelar" name="cancelar" value="Cancelar" class="glyphicon glyphicon-remove-sign"></component-button>
        <?php if(!$flagModifica && empty($StockID)){ ?>
            <component-button type="submit" id="submit" name="submit" value="Guardar" class="glyphicon glyphicon-floppy-disk"></component-button>
        <?php }elseif(!$flagModifica && !empty($StockID)){ ?>
            <component-button type="submit" id="submit" name="submit" value="Guardar" class="glyphicon glyphicon-floppy-disk"></component-button>
        <?php }elseif($flagModifica && !empty($StockID)){ ?>
            <component-button type="submit" id="submit" name="submit" value="Guardar" class="glyphicon glyphicon-floppy-disk"></component-button>
            <?php if (!isset($New)) { ?>
            <component-button type="button" id="btndelete" name="btndelete" value="Eliminar" class="glyphicon glyphicon-trash"></component-button>
            <component-button type="submit" id="delete" name="delete" value="Eliminar" class="glyphicon glyphicon-trash" style="display: none;"></component-button>
            <?php } ?>
        <?php } ?>
    </div>
</div>
<?php
if (isset($New)) {
    // echo '<div><input class="botonVerde mt10 mr40" type="Submit" name="submit" value="' . _('Insertar nuevo producto') . '"></div>';
    echo '<div><input type="submit" name="UpdateCategories" style="visibility:hidden;width:1px" value="' . _('Categories') . '"></div>';
} else {
    // echo '<input class="botonVerde" type="submit" name="submit" value="' . _('Actualizar') . '">';
    echo '<input type="submit" name="UpdateCategories" style="visibility:hidden;width:1px" value="' . _('Categories') . '">';
    echo '<p>';
    // prnMsg(_('Solo haga clic en el boton Eliminar si esta seguro de que desea borrar el articulo!') .  _('Se realizaran controles para garantizar que no existen movimientos de existencias, los registros de analisis de ventas, articulos de la orden de ventas o posiciones de pedido para el articulo') . '. ' . _('No hay supresiones seran permitidos si existen'), 'warn', _('WARNING'));
    // echo '<p><input class="mt10 botonVerde" type="Submit" name="delete" value="' . _('Eliminar este producto ') . '" onclick="return confirm(\'' . _('Estas seguro?') . '\');">';
}

echo '<input type="hidden" name="PONumber" value=' . $PONumber . '>';
echo '<input type="hidden" name="frompage" value=' . $frompage. '>';
echo '<input type="hidden" name="SelectBom" value=' . $SelectBom. '>';
echo '<input type="hidden" name="ModifyOrderNumber" value=' . $ModifyOrderNumber . '>';

echo '<input type="hidden" name="PropertyCounter" value=' . $PropertyCounter . '>';

echo '</form></div>';
include('includes/footer_Index.inc');
if ($procesoterminado != 0) {
    fnmuestraModalGeneral($procesoterminado, $mensaje_emergente);
}
?>
<script type="text/javascript">
///////////////////////////////////////////
// prevencion de submit en el formulario //
///////////////////////////////////////////
// @date: 19.04.18
// @author: Desarrollo
$('#ItemForm :input:not(textarea)').keydown(function(e){
    if((e.witch || e.keyCode) == 13){
        e.preventDefault();
    }
   return /^[^*|\":<>[\]{}`\\'&]+$/.test(String.fromCharCode(e.keyCode || e.which)); 
});
///////////////////////////////////////////
// prevencion de submit en el formulario //
///////////////////////////////////////////

function mostrar1(){
document.getElementById('oculto').style.display = 'block';}
function ocultar1(){
document.getElementById('oculto').style.display = 'none';}
function mostrar2(){
document.getElementById('oculto2').style.display = 'block';}
function ocultar2(){
document.getElementById('oculto2').style.display = 'none';}
fnFormatoSelectGeneral("#CategoryID");
fnFormatoSelectGeneral("#PartidaID");
fnFormatoSelectGeneral("#Units");
fnFormatoSelectGeneral("#idTaxCat");
fnFormatoSelectGeneral("#idTaxCatRet");
fnFormatoSelectGeneral("#idItemPDF");
fnFormatoSelectGeneral("#idIDclassproduct");
fnFormatoSelectGeneral("#MBFlag");
fnFormatoSelectGeneral("#Discontinued");
fnFormatoSelectGeneral("#idControlled");
fnFormatoSelectGeneral("#idSerialised");
fnFormatoSelectGeneral("#idpkg_type");
fnFormatoSelectGeneral("#idEtapaproduccion");
fnFormatoSelectGeneral("#idFlagadvance");
fnFormatoSelectGeneral("#idMarca");
fnFormatoSelectGeneral("#eq_stockid");
fnFormatoSelectGeneral("#Status");

$( document ).ready(function() {
    var mbflag = $('#MBFlag').val();
    console.log(mbflag);
    $('#Description').on('input', function (e) {
        if (!/^[ a-zA-Z0-9áéíóúüñÁÉÍÓÚÜN]*$/i.test(this.value)) {
            this.value = this.value.replace(/[^ a-z0-9áéíóúüñÁÉÍÓÚÜN]+/ig,"");
        }
    });
    $('#LongDescription').on('keyup', function (e) {
        if (!/^[ a-zA-Z0-9áéíóúüñÁÉÍÓÚÜN]*$/i.test(this.value)) {
            this.value = this.value.replace(/[^ a-z0-9áéíóúüñÁÉÍÓÚÜN]+/ig,"");
        }
    });
    fnBusquedaCABM();

    var error = '<?php echo $InputError; ?>';
    var bloqueo = '<?php echo $bloqueoError; ?>';
    var mensaje = '<?php echo $mensaje_emergente; ?>';
    var flag = '<?php echo $flag; ?>';
    var flagModifica = '<?php echo $flagModifica; ?>';
    var modify = '<?php echo $m; ?>';
    var nuevo = '<?php echo $n; ?>';
    console.log(" este es modifica: %s", flagModifica);
    console.log(" este es modify: %s", modify);
    console.log(" este es nuevo: %s", nuevo);
    console.log(" este es error: %s", error);
    /*if(bloqueo == '1' || bloqueo == 1){
        fnBloquearStock();
    }*/
    if(flagModifica){
        $('#StockID').prop('readonly',true);
    }
    if(mbflag != 'B' ){
        $('#Familia').val("");
        $('#Familia').prop('readonly',true);
    }
    if(flag == 0){
        $('#Familia').prop('readonly',true);
    }
    if(error == 1 && !flagModifica){
        $('#StockID').prop('readonly',false);
       //fnBloqueoForzoso();
       //$("#btnCerrarModalGeneral").addClass('cerrarModalCancelar'); 
    }
    /*
     $("#ItemForm").on('submit', function(evt){
        if(error == 1){
            console.log("in error: %s",error);
            evt.preventDefault();
            console.log(mensaje);
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(3, titulo, mensaje);
            $(this).unbind('submit').submit();
        }
        if(bloqueo == 1){
            console.log("in bloqueo: %s",bloqueo);
            fnBloquearStock();
        } 
     });
    */
    if(error == 0){
        //console.log("cerrar : %s",error);
        $("#btnCerrarModalGeneral").addClass('cerrarModalCancelar');
        $('#LongDescription, #Description').prop('readonly',false);
        $('#Discontinued').multiselect('enable');
        $('#MBFlag').multiselect('enable');
        $('#PartidaID').multiselect('enable');
        $('#CategoryID').multiselect('enable');
        $('#Units').multiselect('enable');
        $('#eq_stockid').multiselect('enable');
    }else{
        $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
    }
    if(bloqueo == 1 ){
        console.log("in bloqueo: %s",bloqueo);
        if(error == 0){
            fnBloquearStock();
        }
        //fnBloqueoForzoso();
    }
    /*if($('#eq_stockid').val() != '' || $('#eq_stockid').val() !== 'undefined' || $('#eq_stockid').val() != null || $('#eq_stockid').val() !== '0'){
        $('#eq_stockid').multiselect('disable');
    }
    if($('#eq_stockid').val() == 0 || $('#eq_stockid').val() == '0'){
        $('#eq_stockid').multiselect('enable');
    }*/
    $("#btndelete").click(function() {
        // Botón de Eliminar
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p>Se va a eliminar '+$("#StockID").val()+' - '+$("#Description").val()+'</p>';
        muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnEliminarProducto()');
    });
    $('#CategoryID').change(function() {
        var categoryID = $('#CategoryID').val();
        var dataCategoryID = "";
        dataObj = { 
            option: 'mostrarCogProducto',
            categoryID: categoryID
          };
        $.ajax({
          async:false,
          cache:false,
          method: "POST",
          dataType:"json",
          url: "modelo/selectProductModelo.php",
          data: dataObj
        })
        .done(function( data ) {
            if(data.result) {
                //fnBusquedaFiltroCABM(data.contenido.datos);
                dataCabms = data.contenido.datos;
                var cabmsID = "";
                var cabmsNew = "";
                for (var info in dataCabms) {
                    cabmsID = dataCabms[info].value;
                    cabmsDesc = dataCabms[info].texto;
                    cabmsNew += "<option value="+cabmsID+">"+cabmsID+ " - "+ cabmsDesc+"</option>";
                }
                $('#eq_stockid').empty();
                $('#eq_stockid').append("<option value='0'>Sin Selección ...</option>" + cabmsNew);
                $('#eq_stockid').multiselect('rebuild');
            }else{
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se cargo Información del Catágo COG</p>');
            }
        })
        .fail(function(result) {
            console.log( result );
        });
    });
    $('#PartidaID').change(function() {
        var partidaID = $('#PartidaID').val();
        var dataPartidaID = "";
        dataObj = { 
            option: 'mostrarCogProducto',
            partidaID: partidaID
          };
        $.ajax({
          async:false,
          cache:false,
          method: "POST",
          dataType:"json",
          url: "modelo/selectProductModelo.php",
          data: dataObj
        })
        .done(function( data ) {
            if(data.result) {
                //fnBusquedaFiltroCABM(data.contenido.datos);
                dataCabms = data.contenido.datos;
                var cabmsID = "";
                var cabmsMbflag = "";
                var cabmsNew = "";
                for (var info in dataCabms) {
                    cabmsID = dataCabms[info].value;
                    cabmsDesc = dataCabms[info].texto;
                    cabmsMbflag = dataCabms[info].mbflag;
                    cabmsNew += "<option value="+cabmsID+">"+cabmsDesc+"</option>";
                }
                console.log(cabmsMbflag);
                if(cabmsMbflag != '' || cabmsMbflag !== 'undefined' || cabmsMbflag != null || cabmsMbflag != '0'){
                    $("#MBFlag option[value='"+cabmsMbflag+"']").attr("selected","selected");
                }
                $('#eq_stockid').empty();
                $('#eq_stockid').append("<option value='0'>Sin Selección ...</option>" + cabmsNew);
                $('#eq_stockid').multiselect('rebuild');
            }else{
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se cargo Información del Catágo COG</p>');
            }
        })
        .fail(function(result) {
            console.log( result );
        });
    });
});

function validate (e) {
  // valores inválidos
  if(/[$%&|<>#]/.test(e.target.value)) {
    e.target.classList.remove('valid');
    e.target.classList.add('invalid');
  } else {
    e.target.classList.remove('invalid');
    e.target.classList.add('valid');
  }
}

function fnEliminarProducto() {
    // Ejecutar boton eliminar
    var btnAccion = document.getElementById("delete");
    btnAccion.click();
}

function fnBusquedaCABM() {
    dataObj = { 
            option: 'mostrarCogProducto'
          };
    $.ajax({
      async:false,
      cache:false,
      method: "POST",
      dataType:"json",
      url: "modelo/selectProductModelo.php",
      data: dataObj
    })
    .done(function( data ) {
        //console.log(data);
        if(data.result) {
            fnBusquedaFiltroCABM(data.contenido.datos);
        }else{
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se cargo Información del Catágo COG</p>');
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}

function fnBusquedaFiltroCABM(jsonData) {
    // console.log("busqueda fnBusquedaCog");
    // console.log("jsonData: "+JSON.stringify(jsonData));
    $( "#eq_stockid").autocomplete({
        source: jsonData,
        select: function( event, ui ) {
            
            $( this ).val( ui.item.value + "");
            $( "#eq_stockid" ).val( ui.item.value );

            return false;
        }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {

        return $( "<li>" )
        .append( "<a>" + item.texto + "</a>" )
        .appendTo( ul );

    };
}

function fnChangeType(){
    muestraCargandoGeneral();
        var mbflag = $('#MBFlag').val();
        var dataNewGenPartida = "";
        var dataNewEspPartida = "";
        var dataUnidad = "";
        var dataCabms = "";
        if(mbflag =="B"){
            $('#Familia').prop('readonly',false);
        }
        if(mbflag =="D"){
            $('#Familia').empty();
            $('#Familia').val("");
            $('#Familia').prop('readonly',true);
        }
        dataObj = { 
          option: 'mostrarInfoTipo',
          mbflag: mbflag
        };
        $.ajax({
            method: "POST",
            dataType:"json",
            url: "modelo/selectProductModelo.php",
            data:dataObj
        })
        .done(function( data ) {
            if(data.result){
                dataNewEspPartida = data.contenido.datosPartidaEsp;
                var partidaID = "";
                var partidaDesc = "";
                var partidaNew = "";
                for (var info in dataNewEspPartida) {
                    partidaID = dataNewEspPartida[info].value;
                    partidaDesc = dataNewEspPartida[info].texto;
                    partidaNew += "<option value="+partidaID+">"+partidaDesc+"</option>";
                }
                $('#PartidaID').empty();
                $('#PartidaID').append("<option value='0'>Sin Selección ...</option>" + partidaNew);
                $('#PartidaID').multiselect('rebuild');
                dataNewGenPartida = data.contenido.datosPartida;
                var categoriaID = "";
                var categoriaDesc = "";
                var categoriaNew = "";
                for (var info in dataNewGenPartida) {
                    categoriaID = dataNewGenPartida[info].value;
                    categoriaDesc = dataNewGenPartida[info].texto;
                    categoriaNew += "<option value="+categoriaID+">"+categoriaDesc+"</option>";
                }
                $('#CategoryID').empty();
                $('#CategoryID').append("<option value='0'>Sin Selección ...</option>" + categoriaNew);
                $('#CategoryID').multiselect('rebuild');
                dataUnidad = data.contenido.datosUnidad;
                var unitsID = "";
                var unitsDesc = "";
                var unitsNew = "";
                for (var info in dataUnidad) {
                    unitsID = dataUnidad[info].value;
                    unitsDesc = dataUnidad[info].texto;
                    unitsNew += "<option value="+unitsDesc+">"+unitsDesc+"</option>";
                }
                $('#Units').empty();
                $('#Units').append("<option value='0'>Sin Selección...</option>" + unitsNew);
                $('#Units').multiselect('rebuild');
                dataCabms = data.contenido.datosCabms;
                var cabmsID = "";
                var cabmsDesc = "";
                var cabmsNew = "";
                for (var info in dataCabms) {
                    cabmsID = dataCabms[info].value;
                    cabmsDesc = dataCabms[info].texto;
                    cabmsNew += "<option value="+cabmsID+">"+cabmsDesc+"</option>";
                }
                $('#eq_stockid').empty();
                $('#eq_stockid').append("<option value='0'>Sin Selección...</option>" + cabmsNew);
                $('#eq_stockid').multiselect('rebuild');
            }
            ocultaCargandoGeneral();
        })
        .fail(function(result) {
            ocultaCargandoGeneral();
            console.log("ERROR");
            console.log( result );
        });
}

function fnBloquearStock(){
    var stockID = $( "#StockID").val();
    dataObj = { 
          option: 'bloquearStock',
          stockID: stockID
        };
        $.ajax({
            method: "POST",
            dataType:"json",
            url: "modelo/selectProductModelo.php",
            data:dataObj
        })
        .done(function( data ) {
            if(data.result){
                console.log(data.contenido);
                if(data.contenido == 1 || data.contenido == '1'){
                    $('#StockID, #LongDescription, #Description, #Familia').prop('readonly',true);
                    $('#Discontinued').multiselect('disable');
                    $('#MBFlag').multiselect('disable');
                    $('#PartidaID').multiselect('disable');
                    $('#CategoryID').multiselect('disable');
                    $('#Units').multiselect('disable');
                    $('#eq_stockid').multiselect('disable');
                    $('#submit').hide();
                }
            }else{
                console.log('Error bloqueo');
                console.log(data.contenido);
            }
        })
        .fail(function(result) {
          console.log("ERROR");
          console.log( result );
        });
}

function fnBloqueoForzoso(){
    $('#StockID, #LongDescription, #Description, #Familia').prop('readonly',true);
    $('#Discontinued').multiselect('disable');
    $('#MBFlag').multiselect('disable');
    $('#PartidaID').multiselect('disable');
    $('#CategoryID').multiselect('disable');
    $('#Units').multiselect('disable');
    $('#eq_stockid').multiselect('disable');
    $('#submit').hide();
}

function fnChangeCABMS(){
    muestraCargandoGeneral();
    var eqStockid = $('#eq_stockid').val();
    var dataGenPartida = "";
    var dataEspPartida = "";
    var dataUnidad = "";
    var dataType = "";
    var datosCabms = "";
    dataObj = { 
      option: 'mostrarInfoCABMS',
      eqStockid: eqStockid
    };
    $.ajax({
        method: "POST",
        dataType:"json",
        url: "modelo/selectProductModelo.php",
        data:dataObj
    })
    .done(function( data ) {
        if(data.result){
            datosCabms = data.contenido.datosCabms;
            var eq_stockid = "";
            var descPartidaEspecifica = "";
            var partidacalculada = "";
            var descripcionPartidaEsp = "";
            var mbflag = "";
            console.log(datosCabms);

            for (var info in datosCabms) {
                eq_stockid = datosCabms[info].eq_stockid;
                descPartidaEspecifica = datosCabms[info].descPartidaEspecifica;
                partidacalculada = datosCabms[info].partidacalculada;
                descripcionPartidaEsp = datosCabms[info].descripcionPartidaEsp;
                mbflag = datosCabms[info].mbflag;   
            }
            console.log(mbflag);
            dataType = data.contenido.datosMbflag;
            var typeID = "";
            var typeDesc = "";
            var typeNew = "";
            for (var info in dataType) {
                typeID = dataType[info].value;
                typeDesc = dataType[info].texto;
                typeNew += "<option value="+typeID+">"+typeDesc+"</option>";
            }
            $('#MBFlag').empty();
            $('#MBFlag').append("<option value='0'>Sin Selección ...</option>" + typeNew);
            $('#MBFlag').multiselect('rebuild');

            dataEspPartida = data.contenido.datosPartidaEsp;
            console.log(dataEspPartida);
            var partidaID = "";
            var partidaDesc = "";
            var partidaNew = "";
            for (var info in dataEspPartida) {
                partidaID = dataEspPartida[info].value;
                partidaDesc = dataEspPartida[info].texto;
                partidaNew += "<option value="+partidaID+">"+partidaDesc+"</option>";
            }
            $('#PartidaID').empty();
            $('#PartidaID').append("<option value='0'>Sin Selección ...</option>" + partidaNew);
            $('#PartidaID').multiselect('rebuild');

            dataGenPartida = data.contenido.datosCat;
            //console.log(dataGenPartida);
            var categoriaID = "";
            var categoriaDesc = "";
            var categoriaNew = "";
            for (var info in dataGenPartida) {
                categoriaID = dataGenPartida[info].value;
                categoriaDesc = dataGenPartida[info].texto;
                categoriaNew += "<option value="+categoriaID+">"+categoriaDesc+"</option>";
            }
            $('#CategoryID').empty();
            $('#CategoryID').append("<option value='0'>Sin Selección ...</option>" + categoriaNew);
            $('#CategoryID').multiselect('rebuild');

            dataUnidad = data.contenido.datosUnidad;
            //console.log(dataUnidad);
            var unitsID = "";
            var unitsDesc = "";
            var unitsNew = "";
            for (var info in dataUnidad) {
                unitsID = dataUnidad[info].value;
                unitsDesc = dataUnidad[info].texto;
                unitsNew += "<option value="+unitsDesc+">"+unitsDesc+"</option>";
            }
            $('#Units').empty();
            $('#Units').append("<option value='0'>Sin Selección...</option>" + unitsNew);
            $('#Units').multiselect('rebuild');
        }
        ocultaCargandoGeneral();
    })
    .fail(function(result) {
        ocultaCargandoGeneral();
        console.log("ERROR");
        console.log( result );
    });
}

$(document).on('click','.cerrarModalCancelar',function(){
    location.replace("./SelectProduct.php");
    //location.reload();
});

$(document).on('click','#cancelar',function(){
    $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
    location.replace("./Stocks.php?");
});
</script>


<?php 
///////////////////////////////
// funciones para el sistema //
///////////////////////////////

/**
 * Funcion para la comprobacion de si se modifica o elimina el producto
 * dando como resultado falso si es que no se encuentra en uso segun las validaciones.
 * @date:10.04.18
 * @param  [type] $db       Interface de base de datos
 * @param  [type] $StockID  Identificador del producto
 * @return [type]           retorno de la compronación
 */
function comprubaCambios($db, $StockID)
{

    $sql= "SELECT COUNT(*) FROM stockmoves WHERE stockid='$StockID'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0]>0) {
        return true;
    }

    $sql= "SELECT COUNT(*) FROM bom WHERE component='$StockID'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0]>0) {
        return true;
    }

    $sql= "SELECT COUNT(*) FROM salesorderdetails WHERE stkcode='$StockID'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0]>0) {
        return true;
    }

    $sql= "SELECT COUNT(*) FROM salesanalysis WHERE stockid='$StockID'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0]>0) {
        return true;
    }

    $sql= "SELECT COUNT(*) FROM purchorderdetails WHERE itemcode='$StockID'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0]>0) {
        return true;
    }

    $sql = "SELECT SUM(quantity) AS qoh FROM locstock WHERE stockid='$StockID'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0]!=0) {
        return true;
    }

    $sql = "SELECT count(*) as trans FROM loctransfers WHERE stockid = '$StockID' and shipqty > recqty";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0]!=0) {
        return true;
    }
    return false;
}
/**
 * Funcion para el muestreo de la inforación en patalla de los datos
 * ya sea una actualizacion o una restriccion de generación
 * @date: 10.04.18
 * @param  [type] $db      Instancia de la base de datos
 * @param  [type] $StockID Identificador del producto
 */
function muestraDatosProducto($db, $StockID)
{
    $sql = "SELECT stockid,
        description,
        longdescription,
        manufacturer,
        categoryid,
        units,
        mbflag,
        discontinued,
        controlled,
        serialised,
        perishable,
        eoq,
        volume,
        kgs,
        large,
        width,
        height,
        barcode,
        discountcategory,
        taxcatid,
        decimalplaces,
        appendfile,
        nextserialno,
        idclassproduct,
        taxcatidRet,
        idetapaflujo,stockautor,fichatecnica,
        percentfactorigi,
        pkg_type,
        stocksupplier,
        flagadvance,
        s.eq_stockid,
        eq_conversion_factor,
        unitequivalent,
        partidacalculada as partidaid,
        nu_cve_familia
    FROM stockmaster s
        INNER JOIN tb_partida_articulo tpa on (s.eq_stockid = tpa.eq_stockid)
        INNER JOIN tb_cat_partidaspresupuestales_partidaespecifica ON (tpa.partidaEspecifica = tb_cat_partidaspresupuestales_partidaespecifica.partidacalculada) 
        WHERE stockid = '$StockID'";
    $result = DB_query($sql, $db);
    if (DB_num_rows($result) > 0) {
        $myrow = DB_fetch_array($result);

        $_POST['LongDescription'] = $myrow['longdescription'];
        $_POST['Description'] = $myrow['description'];
        $_POST['marca'] = $myrow['manufacturer'];
        $_POST['EOQ']  = $myrow['eoq'];
        $_POST['CategoryID']  = $myrow['categoryid'];
        $_POST['PartidaID']  = $myrow['partidaid'];
        $_POST['Units']  = $myrow['units'];
        $_POST['MBFlag']  = $myrow['mbflag'];
        $_POST['Discontinued']  = $myrow['discontinued'];
        $_POST['Controlled']  = $myrow['controlled'];
        $_POST['Serialised']  = $myrow['serialised'];
        $_POST['Perishable']  = $myrow['perishable'];
        $_POST['Volume']  = $myrow['volume'];
        $_POST['KGS']  = $myrow['kgs'];
        $_POST['large']  = $myrow['large'];
        $_POST['width']  = $myrow['width'];
        $_POST['height']  = $myrow['height'];
        if (!isset($_POST['BarCode'])) {
            $_POST['BarCode']  = $myrow['barcode'];
        }
        $_POST['DiscountCategory']  = $myrow['discountcategory'];
        $_POST['TaxCat'] = $myrow['taxcatid'];
        $_POST['TaxCatRet'] = $myrow['taxcatidRet'];
        $_POST['DecimalPlaces'] = $myrow['decimalplaces'];
        $_POST['ItemPDF']  = $myrow['appendfile'];
        $_POST['NextSerialNo'] = $myrow['nextserialno'];
        $_POST['IDclassproduct'] = $myrow['idclassproduct'];
        $_POST['etapaproduccion'] = $myrow['idetapaflujo'];
        $_POST['stockautor'] = $myrow['stockautor'];
        $fichaTecnica = $myrow['fichatecnica'];
        $_POST['percentfactorigi'] = $myrow['percentfactorigi'];
        $_POST['pkg_type'] = $myrow['pkg_type'];
        $_POST['stocksupplier'] = $myrow['stocksupplier'];
        $_POST['flagadvance'] = $myrow['flagadvance'];
        $_POST['eq_stockid'] = $myrow['eq_stockid'];
        $_POST['eq_conversion_factor'] = $myrow['eq_conversion_factor'];
        $_POST['unitequivalent'] = $myrow['unitequivalent'];
        $_POST['Familia'] = $myrow['nu_cve_familia'];
    }
}
?>