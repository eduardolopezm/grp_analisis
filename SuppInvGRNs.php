<?php
/**
 * Partidas Factura de Compra
 *
 * @category Pdf
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/09/2017
 * Fecha Modificación: 01/09/2017
 * Selección de partidas para alta de factura de compra
 */

$PageSecurity = 5;
include "includes/SecurityUrl.php";
include('includes/DefineSuppTransClassFacComV1.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/session.inc');
$funcion=2313;
include('includes/SecurityFunctions.inc');
$title = _('Registro de factura');
include('includes/header.inc');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$mensaje_emergente= "";
$procesoterminado= 0;

if (isset($_GET['SupplierID']) and $_GET['SupplierID']!='' and !isset($_GET['GoodRecived'])) {
    if (isset($_SESSION['SuppTrans'])) {
        unset($_SESSION['SuppTrans']->GRNs);
        unset($_SESSION['SuppTrans']->GLCodes);
        unset($_SESSION['SuppTrans']);
    }
    if (isset($_SESSION['SuppTransTmp'])) {
        unset($_SESSION['SuppTransTmp']->GRNs);
        unset($_SESSION['SuppTransTmp']->GLCodes);
        unset($_SESSION['SuppTransTmp']);
    }
    $_SESSION['SuppTrans'] = new SuppTrans;
    
     $sql = "SELECT suppliers.suppname,
            suppliers.supplierid,
            paymentterms.terms,
                        paymentterms.termsindicator,
            paymentterms.daysbeforedue,
            paymentterms.dayinfollowingmonth,
            suppliers.currcode,
            currencies.rate AS exrate,
            suppliers.taxgroupid,
            suppliers.typeid,
            taxgroups.taxgroupdescription
        FROM suppliers,
            taxgroups,
            currencies,
            paymentterms,
            taxauthorities
        WHERE suppliers.taxgroupid=taxgroups.taxgroupid
        AND suppliers.currcode=currencies.currabrev
        AND suppliers.paymentterms=paymentterms.termsindicator
        AND suppliers.supplierid = '" . $_GET['SupplierID'] . "'";
     
    $ErrMsg = _('The supplier record selected') . ': ' . $_GET['SupplierID'] . ' ' ._('cannot be retrieved because');
    $DbgMsg = _('The SQL used to retrieve the supplier details and failed was');
    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
    $myrow = DB_fetch_array($result);
    
    $_SESSION['SuppTrans']->SupplierName = $myrow['suppname'];
    $_SESSION['SuppTrans']->TermsDescription = $myrow['terms'];
    $_SESSION['SuppTrans']->CurrCode = $myrow['currcode'];
    $_SESSION['SuppTrans']->ExRate = $myrow['exrate'];
    if ($_SESSION['SuppTrans']->CurrCode!=$_SESSION['CountryOfOperation']) {
        $_SESSION['SuppTrans']->ExRate=GetCurrencyRateByDate($fechacambio, $_SESSION['SuppTrans']->CurrCode, $db);
    }
    $_SESSION['SuppTrans']->TaxGroup = $myrow['taxgroupid'];
    $_SESSION['SuppTrans']->TaxGroupDescription = $myrow['taxgroupdescription'];
    $_SESSION['SuppTrans']->SupplierID = $myrow['supplierid'];
        $_SESSION['SuppTrans']->DueDate=$myrow['termsindicator'];
    if ($myrow['daysbeforedue'] == 0) {
         $_SESSION['SuppTrans']->Terms = '1' . $myrow['dayinfollowingmonth'];
    } else {
         $_SESSION['SuppTrans']->Terms = '0' . $myrow['daysbeforedue'];
    }
    $_SESSION['SuppTrans']->SupplierID = $_GET['SupplierID'];
    
    $typesupplier=$myrow['typeid'];
    $_SESSION['SuppTrans']->LocalTaxProvince = 1;
    $_SESSION['SuppTrans']->GetTaxesOthers();
    $_SESSION['SuppTrans']->GLLink_Creditors = $_SESSION['CompanyRecord']['gllink_creditors'];
    $_SESSION['SuppTrans']->GRNAct = $_SESSION['CompanyRecord']['grnact'];
    $accountxtype="";//SupplierAccount($typesupplier, 'gl_accountsreceivable', $db);
    $_SESSION['SuppTrans']->CreditorsAct = $accountxtype;
    //$_SESSION['SuppTrans']->CreditorsAct = $_SESSION['CompanyRecord']['creditorsact'];
    $_SESSION['SuppTrans']->InvoiceOrCredit = 'Invoice';
} elseif (!isset($_SESSION['SuppTrans'])) {
    prnMsg(_('Para capturar una factura el proveedor debe de ser seleccionado primero...'), 'warn');
    echo "<br><a href='$rootpath/SelectSupplier.php?" . SID ."'>" . _('Seleccione un proveedor para poder dar de alta una factura') . '</a>';
    include('includes/footer_Index.inc');
    exit();
}

if (isset($_POST['unidadnegocio'])) {
    $unidadnegocio = $_POST['unidadnegocio'];
} else {
    $unidadnegocio = $_GET['unidadnegocio'];
};

if (isset($_POST['legalid'])) {
    $legalid = $_POST['legalid'];
} else {
    $legalid = $_GET['legalid'];
}

if (isset($_POST['dia'])) {
    $dia = $_POST['dia'];
} else {
    $dia = $_GET['dia'];
}
if (isset($_POST['mes'])) {
    $mes = $_POST['mes'];
} else {
    $mes = $_GET['mes'];
}
if (isset($_POST['year'])) {
    $year = $_POST['year'];
} else {
    $year = $_GET['year'];
}

if (isset($_POST['txtFechaDesde'])) {
    $fechaini = date_create($_POST['txtFechaDesde']);
    $fechaini = date_format($fechaini, 'Y-m-d');
} else {
    $fechaini = date('Y-m-d');
}

if (isset($_POST['txtFechaHasta'])) {
    $fechafin = date_create($_POST['txtFechaHasta']);
    $fechafin = date_format($fechafin, 'Y-m-d');
} else {
    $fechafin = date('Y-m-d');
}

$moneda = "MXN";
if ($_GET['moneda']) {
    $moneda = $_GET['moneda'];
} else if ($_POST['moneda']) {
    $moneda=$_POST['moneda'];
}

if (isset($_POST['CurrAbrevHandle'])) {
    $moneda = $_POST['CurrAbrevHandle'];
}

$Complete=false;
if (!isset($_SESSION['SuppTrans'])) {
    prnMsg(_('Seleccionar un Proveedor para realizar proceso'), 'info');
    echo "<br><a href='$rootpath/SelectSupplier.php?" . SID ."'>" . _('Seleccionar Proveedor') . '</a>';
    include('includes/footer_Index.inc');
    exit();
}

if (isset($_POST['AddPOToTrans']) and $_POST['AddPOToTrans']!='') {
    foreach ($_SESSION['SuppTransTmp']->GRNs as $GRNTmp) {
        if ($_POST['AddPOToTrans']==$GRNTmp->PONo) {
            $_SESSION['SuppTrans']->Copy_GRN_To_Trans($GRNTmp);
            $_SESSION['SuppTrans']->GetTaxes($GRNTmp->GRNNo);
            $_SESSION['SuppTransTmp']->Remove_GRN_From_Trans($GRNTmp->GRNNo);
        }
    }
}

if (isset($_POST['AddGRNToTrans'])) {
    foreach ($_SESSION['SuppTransTmp']->GRNs as $GRNTmp) {
        if (isset($_POST['GRNNo_' . $GRNTmp->GRNNo])) {
            $_POST['GRNNo_' . $GRNTmp->GRNNo] = true;
        } else {
            $_POST['GRNNo_' . $GRNTmp->GRNNo] = false;
        }
        $Selected = $_POST['GRNNo_' . $GRNTmp->GRNNo];
        if ($Selected==true) {
            $_SESSION['SuppTrans']->Copy_GRN_To_Trans($GRNTmp);
            $_SESSION['SuppTrans']->GetTaxes($GRNTmp->GRNNo);
            $_SESSION['SuppTransTmp']->Remove_GRN_From_Trans($GRNTmp->GRNNo);
        }
    }
}

if (isset($_POST['ModifyGRN'])) {
    $InputError=false;

    if ($_POST['This_QuantityInv'] >= ($_POST['QtyRecd'] - $_POST['Prev_QuantityInv'])) {
        $Complete = true;
    } else {
        $Complete = false;
    }
    
    if ($_SESSION['Check_Qty_Charged_vs_Del_Qty']==true) {
        if (($_POST['This_QuantityInv']+ $_POST['Prev_QuantityInv'])/($_POST['QtyRecd'] ) > (1+ ($_SESSION['OverChargeProportion'] / 100))) {
            prnMsg(_('La cantidad a facturar es mas que la pendiente'), 'error');
            $InputError = true;
        }
    }
    
    if (!is_numeric($_POST['ChgPrice']) and $_POST['ChgPrice']<0) {
        $InputError = true;
        prnMsg(_('El precio no es numérico o es negativo'), 'error');
    } elseif ($_SESSION['Check_Price_Charged_vs_Order_Price'] == true) {
        if ($_POST['ChgPrice']/$_POST['OrderPrice'] > (1+ ($_SESSION['OverChargeProportion'] / 100))) {
            prnMsg(_('El precio de la factura es mayor que el de la orden de compra y sobre pasa el porcentaje máximo configurado para esta implementación '), 'error');
            $InputError = true;
        }
    }

    if ($_POST['ChgDesc1']>100 || $_POST['ChgDesc1']<0 || $_POST['ChgDesc2']>100 || $_POST['ChgDesc2']<0 || $_POST['ChgDesc3']>100 || $_POST['ChgDesc3']<0) {
        $InputError = true;
        prnMsg(_('El descuento no es un valor valido'), 'error');
    } else if (($_POST['ChgDesc1'] + $_POST['ChgDesc2'] + $_POST['ChgDesc3']) > 100) {
        $InputError = true;
        prnMsg(_('El descuento total no es un valor valido'), 'error');
    }

    if ($InputError==false) {
//        $_SESSION['SuppTrans']->Remove_GRN_From_Trans($_POST['GRNNumber']);
        $_SESSION['SuppTrans']->Modify_GRN_To_Trans(
            $_POST['GRNNumber'],
            $_POST['PODetailItem'],
            $_POST['ItemCode'],
            $_POST['ItemDescription'],
            $_POST['QtyRecd'],
            $_POST['Prev_QuantityInv'],
            $_POST['This_QuantityInv'],
            $_POST['OrderPrice'],
            $_POST['ChgDesc1'],
            $_POST['ChgDesc2'],
            $_POST['ChgDesc3'],
            $_POST['ChgPrice'],
            $Complete,
            $_POST['StdCostUnit'],
            $_POST['ShiptRef'],
            $_POST['JobRef'],
            $_POST['GLCode']
        );
    }
}

if (isset($_GET['Delete'])) {
    $_SESSION['SuppTransTmp']->Copy_GRN_To_Trans($_SESSION['SuppTrans']->GRNs[$_GET['Delete']]);
    //$_SESSION['SuppTrans']->GetTaxes($GRNTmp->GRNNo);
    $_SESSION['SuppTrans']->Remove_GRN_From_Trans($_GET['Delete']);
}

$sql ='select tagdescription FROM tags l where tagref="'.$unidadnegocio.'"';
$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
$myrowloc = DB_fetch_array($result);
$nomUnidNegocio = $myrowloc['tagdescription'];

/*Show all the selected GRNs so far from the SESSION['SuppTrans']->GRNs array */
/*<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">*/
echo "<form action='" . $_SERVER['PHP_SELF'] . "?" . SID . "' method=post id='formSelect' name='formSelect'>";
?>
    <input type=hidden name=unidadnegocio value="<?php echo $unidadnegocio; ?>" />
    <input type=hidden name=legalid value="<?php echo $legalid; ?>" />
    <input type=hidden name=dia value="<?php echo $dia; ?>" />
    <input type=hidden name=mes value="<?php echo $mes; ?>" />
    <input type=hidden name=year value="<?php echo $year; ?>" />
    <input type=hidden name=moneda value='<?php echo $moneda; ?>' />

    <div class="row"></div>
    <div class="col-md-4 col-xs-12" style="display: none;">
        <component-date-label label="Desde:" id="txtFechaDesde" name="txtFechaDesde" placeholder="Desde" title="Desde" value="<?php echo date_format(date_create($fechaini), 'd-m-Y'); ?>"></component-date-label>
    </div>
    <div class="col-md-4 col-xs-12" style="display: none;">
        <component-date-label label="Hasta:" id="txtFechaHasta" name="txtFechaHasta" placeholder="Hasta" title="Hasta" value="<?php echo date_format(date_create($fechafin), 'd-m-Y'); ?>"></component-date-label>
    </div>
    <div class="col-md-4 col-xs-12" style="display: none;">
       <!-- <button type="Submit" id="Generar" name="Generar" class="btn btn-default botonVerde" > 
       <span class="glyphicon glyphicon-search" ></span> Buscar Recepciones
       </button> -->
       <component-button type="submit" id="Generar" name="Generar" class="glyphicon glyphicon-search" value="Buscar Recepciones"></component-button>
    </div>
    <div class="row"></div>
<?php
$SQL = "SELECT grnbatch,
grnno,
purchorderdetails.orderno,
purchorderdetails.unitprice,
grns.itemcode,
DATE_FORMAT(grns.deliverydate, '%d-%m-%Y') as deliverydate,
grns.itemdescription,
grns.qtyrecd,
grns.quantityinv,
grns.stdcostunit,
purchorderdetails.glcode,
purchorderdetails.shiptref,
purchorderdetails.jobref,
purchorderdetails.podetailitem,
purchorderdetails.discountpercent1,
purchorderdetails.discountpercent2,
purchorderdetails.discountpercent3,
stockmoves.stkmoveno,
purchorders.tagref,
purchorders.intostocklocation as location,
CASE WHEN purchorders.requisitionno>0 THEN
CASE WHEN (grns.qtyrecd)=0 THEN 1
ELSE 0 END ELSE 1 END  AS FACT ,
stockmoves.narrative as serie,
case when stockmaster.taxcatid is null then 4 else stockmaster.taxcatid end as taxcatid,
stockmaster.barcode,
grns.rategr, 
tags.tagdescription as tagname,
grns.ln_ue,
tb_cat_unidades_ejecutoras.desc_ue,
purchorders.realorderno,
purchorders.comments,
purchorderdetails.clavepresupuestal,
purchorderdetails.ln_clave_iden
FROM grns 
INNER JOIN purchorderdetails ON  grns.podetailitem=purchorderdetails.podetailitem
JOIN purchorders ON purchorders.orderno = purchorderdetails.orderno
JOIN tags ON purchorders.tagref = tags.tagref
LEFT JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ur = tags.tagref AND tb_cat_unidades_ejecutoras.ue = grns.ln_ue
LEFT JOIN stockmaster ON stockmaster.stockid=purchorderdetails.itemcode
LEFT JOIN stockmoves ON stockmoves.transno=grns.grnbatch and stockmoves.type=25  and stockmoves.stockid=purchorderdetails.itemcode
WHERE grns.supplierid ='" . $_SESSION['SuppTrans']->SupplierID . "'
and tags.legalid =".$legalid."
AND grns.qtyrecd - grns.quantityinv > 0 
AND grns.deliverydate >= '$fechaini' 
AND grns.deliverydate <= '$fechafin 23:59:59'
ORDER BY grns.grnno ";
// salesorderdetails.qtyinvoiced
// LEFT JOIN salesorderdetails ON salesorderdetails.orderno=purchorders.requisitionno and salesorderdetails.stkcode=purchorderdetails.itemcode
// echo "<br><pre>SQL: ".$SQL;
$GRNResults = DB_query($SQL, $db);

if (DB_num_rows($GRNResults)==0) {
    prnMsg(_('No existen productos recibidos pendientes de facturar en '.$moneda.' en <b>').$nomUnidNegocio. '</b> del Proveedor <b>' . $_SESSION['SuppTrans']->SupplierName . '</b><br>' . _('Los productos deben recibirse antes de poder realizar una factura al proveedor'), 'warn');
}

echo "<input type=hidden name=unidadnegocio value=".$unidadnegocio.">";
echo "<input type=hidden name=legalid value=".$legalid.">";
echo "<input type=hidden name=dia value=".$dia.">";
echo "<input type=hidden name=mes value=".$mes.">";
echo "<input type=hidden name=year value=".$year.">";
echo "<input type=hidden name=moneda value='$moneda'>";

if (!isset($_SESSION['SuppTransTmp']) or isset($_POST['Generar'])) {
    $_SESSION['SuppTransTmp'] = new SuppTrans;
    $_SESSION['SuppTransTmp']->TaxGroup=$_SESSION['SuppTrans']->TaxGroup;
    $_SESSION['SuppTransTmp']->LocalTaxProvince = 1;
    while ($myrow=DB_fetch_array($GRNResults)) {
        //echo 'prod:'.$myrow['podetailitem'];
        $GRNAlreadyOnInvoice = false;
        foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN) {
            if ($EnteredGRN->GRNNo == $myrow['grnbatch']) {
                $GRNAlreadyOnInvoice = true;
            }
        }
        if (strlen($myrow['serie'])>0) {
            $myrow['serie']=' Serie: '.$myrow['serie'];
        }
        // bandera si es deducible de ietu o no
        if (strlen($myrow['itemcode'])==0) {
            //$FlagIETU=GetIETUxSupplier($myrow['glcode'], $_SESSION['SuppTrans']->SupplierID, $db);
            //tipo de deduccion
            $FlagIETU = 0;
            $typeIETU = 0;
            $separa = explode('|', $FlagIETU);
            $FlagIETU = $separa[0];
            $typeIETU = $separa[1];
        } else {
            //$FlagIETU=GetIETUxStock($myrow['itemcode'], $db);
            //porcentaje deduccion
            $separa = explode('|', $FlagIETU);
            $FlagIETU = $separa[0];
            $typeIETU = $separa[1];
            $FlagIETU = 0;
            $typeIETU = 0;
        }
        
        //tipo de deduccion
        //$percentIETU=GetPercentxTypeIETU($typeIETU,$db);
        $percentIETU=0;
        
        if ($GRNAlreadyOnInvoice == false) {
            $_SESSION['SuppTransTmp']->Add_GRN_To_Trans(
                $myrow['grnno'],
                $myrow['podetailitem'],
                $myrow['itemcode'],
                $myrow['itemdescription'],
                $myrow['qtyrecd'],
                $myrow['quantityinv'],
                $myrow['qtyrecd'] - $myrow['quantityinv'],
                $myrow['unitprice'],
                $myrow['discountpercent1'],
                $myrow['discountpercent2'],
                $myrow['discountpercent3'],
                $myrow['unitprice'],
                $Complete,
                $myrow['stdcostunit'],
                $myrow['shiptref'],
                $myrow['jobref'],
                $myrow['glcode'],
                $myrow['orderno'],
                $myrow['tagref'],
                $myrow['stkmoveno'],
                $myrow['location'],
                $myrow['FACT'],
                $myrow['serie'],
                $myrow['taxcatid'],
                $FlagIETU,
                $typeIETU,
                $percentIETU,
                $myrow['barcode'],
                $myrow['rategr'],
                '',
                0,
                $myrow['tagname'],
                $myrow['ln_ue'],
                $myrow['desc_ue'],
                $myrow['realorderno'],
                $myrow['deliverydate'],
                $myrow['comments'],
                $myrow['clavepresupuestal'],
                $myrow['ln_clave_iden']
            );
            
            $_SESSION['SuppTransTmp']->GetTaxes($myrow['grnno']);
        }
    }
}

//if (isset($_POST['GRNNo']) AND $_POST['GRNNo']!=''){
if (isset($_GET['Modify'])) {
    $GRNNo = $_GET['Modify'];
    $GRNTmp = $_SESSION['SuppTrans']->GRNs[$GRNNo];
    echo '<p><div class="centre"><font size=2 color=Darkblue><b>' . _('Producto Seleccionado') . '</font></b></div>';
    echo "<table>
		<tr bgcolor=#800000>
			<th>#</th>
			<th>" . _('Producto') . "</th>
			<th>" . _('Pendientes') . "</th>
			<th>" . _('Facturados') . "</th>
			<th>" . _('Precio en Orden') . ' ' .  $_SESSION['SuppTrans']->CurrCode . "</th>
			<th>" . _('Precio Actual') . ' ' .  $_SESSION['SuppTrans']->CurrCode . "</th>
			<th>" . _('Desc1 %') . "</th>
			<th>" . _('Desc2 %') . "</th>
			<th>" . _('Desc3 %') . "</th>
		</tr>";
    echo '<tr>
		<td>' . $GRNTmp->GRNNo . '</td>
		<td>' . $GRNTmp->ItemCode . ' ' . $GRNTmp->ItemDescription . '</td>
		<td align=right>' . number_format($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv, 2) . "</td>
		<td><input type=Text class='number' Name='This_QuantityInv' Value=" . $GRNTmp->This_QuantityInv . ' size=11 maxlength=10></td>
		<td align=right>' . $GRNTmp->OrderPrice . '</td>
		<td><input type=Text class="number" Name="ChgPrice" Value=' . $GRNTmp->ChgPrice . ' size=6 maxlength=10></td>
		<td><input type=Text class="number" Name="ChgDesc1" Value=' . $GRNTmp->Desc1 . ' size=6 maxlength=5></td>
		<td><input type=Text class="number" Name="ChgDesc2" Value=' . $GRNTmp->Desc2 . ' size=6 maxlength=5></td>
		<td><input type=Text class="number" Name="ChgDesc3" Value=' . $GRNTmp->Desc3 . ' size=6 maxlength=5></td>
		
	</tr>';
    echo '</table>';

    echo "<input type=hidden name='ShiptRef' Value='" . $GRNTmp->ShiptRef . "'>";
    echo "<div class='centre'><p><input type=Submit Name='ModifyGRN' Value='" . _('Modificar') . "'></div>";
    echo "<input type=hidden name='GRNNumber' VALUE=" . $GRNTmp->GRNNo . '>';
    echo "<input type=hidden name='ItemCode' VALUE='" . $GRNTmp->ItemCode . "'>";
    echo "<input type=hidden name='ItemDescription' VALUE='" . $GRNTmp->ItemDescription . "'>";
    echo "<input type=hidden name='QtyRecd' VALUE=" . $GRNTmp->QtyRecd . ">";
    echo "<input type=hidden name='Prev_QuantityInv' VALUE=" . $GRNTmp->Prev_QuantityInv . '>';
    echo "<input type=hidden name='OrderPrice' VALUE=" . $GRNTmp->OrderPrice . '>';
    echo "<input type=hidden name='Desc1' VALUE=" . $GRNTmp->Desc1 . '>';
    echo "<input type=hidden name='Desc2' VALUE=" . $GRNTmp->Desc2 . '>';
    echo "<input type=hidden name='Desc3' VALUE=" . $GRNTmp->Desc3 . '>';
    echo "<input type=hidden name='StdCostUnit' VALUE=" . $GRNTmp->StdCostUnit . '>';
    echo "<input type=text name='rategr' VALUE=" . $GRNTmp->rategr . '>';
    echo "<input type=hidden name='JobRef' Value='" . $GRNTmp->JobRef . "'>";
    echo "<input type=hidden name='GLCode' Value='" . $GRNTmp->GLCode . "'>";
    echo "<input type=hidden name='PODetailItem' Value='" . $GRNTmp->PODetailItem . "'>";
    echo "<input type=hidden name='legalid' Value='" .$legalid . "'>";
} else {
    if (count($_SESSION['SuppTransTmp']->GRNs)>0) {
        // echo '<div class="centre"><h4>' . _('Productos ya recibidos para ser facturados de') . ' ' . $_SESSION['SuppTrans']->SupplierName.'</h4></div>';
    
        echo '<div class="centre"><h5>' . _('UR:') . ' ' . ($nomUnidNegocio) .'</h5></div>';
        ?>
        <script type="text/javascript">
            function selAll(obj){
                //console.log("checked: "+obj.checked);
                var I = document.getElementById('appVue').value;
                //alert("valor de :" + I);
                for (i=0;i<document.formSelect.elements.length;i++){
                    if(document.formSelect.elements[i].type == "checkbox"){
                        if (document.formSelect.elements[i].checked) {
                            document.formSelect.elements[i].checked = 0;
                        }else{
                            document.formSelect.elements[i].checked = 1;
                        }
                        //document.formSelect.elements[i].checked=1;
                    }
                }
            }
        </script>
        <?php
        echo '<br>';
        echo "<table class='table table-bordered' cellpadding='1' colspan='7'>";

        // <th>" . _('Desc 1') . "</th>
        // <th>" . _('Desc 2') . "</th>
        // <th>" . _('Desc 3') . "</th>
        // <th>" . _('Serie') . '</th>
        // <th>" . _('Recibidos') . "</th>
        // <th>" . _('Facturados') . "</th>
        // <th>" . _('Por Facturar') . "</th>
        
        $tableheader = "<tr class='header-verde'><th style='text-align: center;'>" . _('Seleccionar') . " <br> <input type=checkbox name='All' onclick='javascript:selAll(this);' /> </th>
				<th style='text-align: center;'>UR</th>
                <th style='text-align: center;'>UE</th>
				<th style='text-align: center;'>" . _('Orden Compra') . "</th>
				<th style='text-align: center;'>" . _('Código') . "</th>
				<th style='text-align: center;'>" . _('Descripción') . "</th>
                <th style='text-align: center;'>" . _('Recepción') . "</th>
				<th style='text-align: center;'>" . _('Precio U.') . ' ' . $_SESSION['SuppTrans']->CurrCode . "</th>
				<th style='text-align: center;'>" . _('Sub Total') . ' ' . $_SESSION['SuppTrans']->CurrCode . "</th>
				</tr>";

        $i = 0;
        $POs = array();
        $totalGeneral = 0;
        //	echo '<pre>'.var_dump($_SESSION['SuppTransTmp']->GRNs);
        foreach ($_SESSION['SuppTransTmp']->GRNs as $GRNTmp) {
            $_SESSION['SuppTransTmp']->GRNs[$GRNTmp->GRNNo]->This_QuantityInv = $GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv;
            if (isset($POs[$GRNTmp->PONo]) and $POs[$GRNTmp->PONo] != $GRNTmp->PONo) {
                $POs[$GRNTmp->PONo] = $GRNTmp->PONo;
                echo "<tr><td><input type=Submit Name='AddPOToTrans' Value='" . $GRNTmp->PONo . "'></td><td colspan=3>" . _('Agregar Proveedor a la Facturar') . '</td></tr>';
                $i = 0;
            }
            if ($i == 0) {
                echo $tableheader;
            }
            if ($GRNTmp->invoice == 0) {
                $bgcolor="bgcolor=red ";
            } else {
                $bgcolor=" ";
            }
            //echo '<br>invoice:'. $GRNTmp->invoice;
            //if ($GRNTmp->invoice==1){
            if (isset($_POST['SelectAll'])) {
                echo "<tr><td style='text-align: center;'><input type=checkbox checked name='GRNNo_" . $GRNTmp->GRNNo . "'></td>";
            } else {
                echo "<tr><td style='text-align: center;'><input type=checkbox name='GRNNo_" . $GRNTmp->GRNNo . "'></td>";
            }
            //}else{
                //echo "<tr bgcolor=red ><td>". $GRNTmp->GRNNo." </td>";
                
            //}
            
            // <td class=number>' . $GRNTmp->Desc1 . '%</td>
            // <td class=number>' . $GRNTmp->Desc2 . '%</td>
            // <td class=number>' . $GRNTmp->Desc3 . '%</td>
            // <td class=number>' . $GRNTmp->SerieNo . '</td>
            // <td class=number>' . $GRNTmp->QtyRecd . '</td>
            // <td class=number>' . $GRNTmp->Prev_QuantityInv . '</td>
            // <td class=number>' . ($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv) . '</td>
            
            $totalGeneral += ($GRNTmp->OrderPrice * ($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv)) * (1-($GRNTmp->Desc1/100)) * (1-($GRNTmp->Desc2/100)) * (1-($GRNTmp->Desc3/100));
            
            $SQL = "SELECT realorderno FROM purchorders WHERE orderno='".$GRNTmp->PONo."'";
            $resultRealOrder = DB_query($SQL, $db);
            $myrowRealOrder=DB_fetch_array($resultRealOrder);
            echo "<td>" . $GRNTmp->tagref . ' - ' . $GRNTmp->tagname . '</td>
            <td>' . $GRNTmp->unidadEjecutora . ' - ' . $GRNTmp->unidadEjecutoraName . '</td>
			<td style="text-align: center;">' . $myrowRealOrder['realorderno'] . '</td>
			<td style="text-align: center;">' . $GRNTmp->ItemCode . '</td>
			<td>' . $GRNTmp->ItemDescription . '</td>
            <td style="text-align: center;">' . $GRNTmp->deliverydate . '</td>
			<td class=number style="text-align: right;">$ ' . number_format($GRNTmp->OrderPrice, 2) . '</td>
			<td class=number style="text-align: right;">$ ' . number_format(($GRNTmp->OrderPrice * ($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv)) * (1-($GRNTmp->Desc1/100)) * (1-($GRNTmp->Desc2/100)) * (1-($GRNTmp->Desc3/100)), 2) . '</td>
			</tr>';
        
            $i++;
            if ($i>15) {
                $i=0;
            }
        }
        echo '<tr>';
        echo '<td colspan="8" style="text-align: right;"><b>Total:</b> </td><td style="text-align: right;">$ '.number_format($totalGeneral, 2).'</td>';
        echo '</tr>';
        echo '</table>';
        echo '<table width=80%><tr><td>';
        //echo "<input style='font-size:11px;' type=Submit Name='SelectAll' Value='" . _('Seleccionar Todos') . "'>";
        //echo '<component-button type="submit" id="SelectAll" name="SelectAll" value="Seleccionar Todos"></component-button>';
        //echo "<input style='font-size:11px;' type=Submit Name='DeSelectAll' Value='" . _('Quitar Selección a todos') . "'>";
        //echo '<component-button type="submit" id="DeSelectAll" name="DeSelectAll" value="Quitar Selección a todos"></component-button>';
        echo'</td><td style="text-align:right;">';
        //echo "<input type=Submit Name='AddGRNToTrans' Value='" . _('Agregar para Facturar') . "' style='font-weight:bold;'>";
        echo '<component-button type="submit" id="AddGRNToTrans" name="AddGRNToTrans" class="glyphicon glyphicon-plus" value="Agregar"></component-button>';
        echo "</td></tr></table>";
    }
}
echo '</form>';

//echo '<div align="center"><h4>' . _('Productos Agregados para Facturar'). '</h4></div>';

echo '<br>';
echo '<table class="table table-bordered" cellpadding=1>';

// <th>" . _('Desc 1') . "</th>
// <th>" . _('Desc 1') . "</th>
// <th>" . _('Desc 1') . "</th>
// <th>" . _('Serie') .$dia. '</th>
// <th>#</th>

$tableheader = "<tr class='header-verde'>
            <th style='text-align: center;'>" . _('UE') . "</th>
            <th style='text-align: center;'>" . _('Código') . "</th>
            <th style='text-align: center;'>" . _('Descripción') . "</th>
            <th style='text-align: center;'>" . _('Cantidad Cargada') . "</th>
            <th style='text-align: center;'>" . _('Precio U.') . ' ' . $_SESSION['SuppTrans']->CurrCode . "</th>
            <th style='text-align: center;'>" . _('Total') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>';

if (Havepermission($_SESSION['UserID'], 483, $db)==1) {
    $tableheader .= '<th></th>';
}

$tableheader .='<th></th>
            </tr>';

echo $tableheader;

$TotalValueCharged=0;
$unidadEjecutoraAnt = "";
$unidadEjecutoraNameAnt = "";
$unidadEjecutoraSeleccionadas = "";
foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN) {
    if ($EnteredGRN->GRNNo<>'') {
        // <td style="text-align:right;">' . $EnteredGRN->Desc1 . ' %</td>
        // <td style="text-align:right;">' . $EnteredGRN->Desc2 . ' %</td>
        // <td style="text-align:right;">' . $EnteredGRN->Desc3 . ' %</td>
        // <td style="text-align:right;">' . $EnteredGRN->SerieNo . " </td>"
        
        if ($unidadEjecutoraAnt == '') {
            $unidadEjecutoraSeleccionadas .= $EnteredGRN->unidadEjecutora;
            $unidadEjecutoraAnt = $EnteredGRN->unidadEjecutora;
            $unidadEjecutoraNameAnt = $EnteredGRN->unidadEjecutoraName;
            $mensaje_emergente .= '<h4>Unidad Ejecutora Seleccionada</h4>';
            $mensaje_emergente .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$EnteredGRN->unidadEjecutora.' - '.$EnteredGRN->unidadEjecutoraName.'</p>';
        } else if ($unidadEjecutoraAnt != '' && $unidadEjecutoraAnt != $EnteredGRN->unidadEjecutora) {
            if (strpos($unidadEjecutoraSeleccionadas, $EnteredGRN->unidadEjecutora) === false) {
                $mensaje_emergente .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '.$EnteredGRN->unidadEjecutora.' - '.$EnteredGRN->unidadEjecutoraName.'</p>';
            }
            $unidadEjecutoraSeleccionadas .= " , ".$EnteredGRN->unidadEjecutora;
            $procesoterminado = 2;
        }

        // <td>' . $EnteredGRN->GRNNo . '</td>
        echo '<tr>
        <td>' . $EnteredGRN->unidadEjecutora . ' - ' . $EnteredGRN->unidadEjecutoraName . '</td>
        <td style="text-align: center;">' . $EnteredGRN->ItemCode . '</td>
        <td>' . $EnteredGRN->ItemDescription . ' - ' . $EnteredGRN->unidadEjecutora . '</td>
        <td style="text-align: center;">' . ($EnteredGRN->This_QuantityInv) . '</td>
        <td style="text-align:right;">$ ' . number_format($EnteredGRN->ChgPrice, 2) . '</td>
        <td style="text-align:right;">$ ' . number_format(($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv) * (1-($EnteredGRN->Desc1/100)) * (1-($EnteredGRN->Desc2/100)) * (1-($EnteredGRN->Desc3/100)), 2) . '</td>';
        if (Havepermission($_SESSION['UserID'], 483, $db)==1) {
            $enc = new Encryption;
            $url = "&Modify=>".$EnteredGRN->GRNNo."&legalid=>".$legalid
            ."&unidadnegocio=>".$unidadnegocio."&dia=>".$dia."&mes=>".$mes."&year=>".$year;
            $url = $enc->encode($url);
            $liga= "URL=" . $url;

            echo "<td><a href='SuppInvGRNs.php?" . $liga ."'>". _('Modificar') . "</a></td>";
        }

        $enc = new Encryption;
        $url = "&moneda=>".$moneda."&Delete=>".$EnteredGRN->GRNNo."&legalid=>".$legalid
        ."&unidadnegocio=>".$unidadnegocio."&dia=>".$dia."&mes=>".$mes."&year=>".$year;
        $url = $enc->encode($url);
        $liga= "URL=" . $url;

        echo "<td><a href='SuppInvGRNs.php?" . $liga . "'>" . _('Quitar') . "</a></td>
        </tr>";
    }

    $TotalValueCharged = $TotalValueCharged + (($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv) * (1-($EnteredGRN->Desc1/100)) * (1-($EnteredGRN->Desc2/100)) * (1-($EnteredGRN->Desc3/100)));

    $i++;
    if ($i>15) {
        $i=0;
        echo $tableheader;
    }
}

$mensajeUR = "";
if ($procesoterminado != 0) {
    $mensajeUR = "Recepciones con Unidad Ejecutora diferentes. No se puede realizar la Factura";
}

echo '<tr>
    <td colspan="5" style="color: red;">'.$mensajeUR.'</td>
    <td style="text-align:right;"><font size=2><b>$ ' . number_format($TotalValueCharged, 2) . '</U></font></td>
    </tr>';
echo "</table>";

$enc = new Encryption;
$url = "&unidadnegocio=>".$unidadnegocio."&legalid=>".$legalid."&dia=>".$dia."&mes=>".$mes."&year=>".$year;
$url = $enc->encode($url);
$liga= "URL=" . $url;

// Div botones
echo "<div align='center'>";
echo '<component-button type="submit" id="btnRegresar" name="btnRegresar" class="glyphicon glyphicon-share-alt" value="Regresar" onclick="fnRegresarPanel(); return false;"></component-button>';
if (count($_SESSION['SuppTrans']->GRNs) > 0) {
    echo "<a href='$rootpath/SupplierInvoice.php?" . $liga ."' class='btn btn-default botonVerde glyphicon glyphicon-plus'>" . _(' Procesar Factura') . '</a><br><br>';
}
echo "</div>";

include('includes/footer_Index.inc');

if ($procesoterminado != 0) {
    ?>
    <script type="text/javascript">
        var mensajeMod = '<?php echo $mensaje_emergente; ?>';
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, mensajeMod);
    </script>
    <?php
}

//var_dump($_SESSION['SuppTransTmp']);
?>
<script type="text/javascript">
    /**
    * Función para regresar al panel
    * @return {[type]} [description]
    */
    function fnRegresarPanel () {
        window.open("panel_recepcion_compra.php", "_self");
    }
</script>
