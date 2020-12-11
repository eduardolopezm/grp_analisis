<?php
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
$sql                   = "SELECT suppliers.suppname,
	 		suppliers.supplierid,
	 		paymentterms.terms,
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
	 	AND suppliers.supplierid = '" . $_SESSION['PO']->SupplierID . "'";

$ErrMsg                                     = _('The supplier record selected') . ': ' . $_SESSION['PO']->SupplierID . ' ' . _('cannot be retrieved because');
$DbgMsg                                     = _('The SQL used to retrieve the supplier details and failed was');
$result                                     = DB_query($sql, $db, $ErrMsg, $DbgMsg);
$myrow                                      = DB_fetch_array($result);
$_SESSION['SuppTrans']->SupplierName        = $myrow['suppname'];
$_SESSION['SuppTrans']->TermsDescription    = $myrow['terms'];
$_SESSION['SuppTrans']->CurrCode            = $myrow['currcode'];
$_SESSION['SuppTrans']->ExRate              = $myrow['exrate'];
$_SESSION['SuppTrans']->TaxGroup            = $myrow['taxgroupid'];
$_SESSION['SuppTrans']->TaxGroupDescription = $myrow['taxgroupdescription'];
$_SESSION['SuppTrans']->SupplierID          = $myrow['supplierid'];
if ($myrow['daysbeforedue'] == 0) {
    $_SESSION['SuppTrans']->Terms = '1' . $myrow['dayinfollowingmonth'];
} else {
    $_SESSION['SuppTrans']->Terms = '0' . $myrow['daysbeforedue'];
}
$_SESSION['SuppTrans']->SupplierID = $_SESSION['PO']->SupplierID;

$typesupplier = $myrow['typeid'];
if (!isset($_SESSION['DefaultTaxCategory'])) {
    $_SESSION['DefaultTaxCategory'] = 4;
}
$_SESSION['SuppTrans']->LocalTaxProvince = 1;
$_SESSION['SuppTrans']->GetTaxesOthers();
$_SESSION['SuppTrans']->GLLink_Creditors = $_SESSION['CompanyRecord']['gllink_creditors'];
$_SESSION['SuppTrans']->GRNAct           = $_SESSION['CompanyRecord']['grnact'];
$accountxtype                            = SupplierAccount($typesupplier, 'gl_accountsreceivable', $db);
$_SESSION['SuppTrans']->CreditorsAct     = $accountxtype;

$_SESSION['SuppTrans']->InvoiceOrCredit = 'Invoice';

$SQL = "SELECT grnbatch,
		grnno,
		purchorderdetails.orderno,
		purchorderdetails.unitprice,
		grns.itemcode,
		grns.deliverydate,
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
		CASE WHEN (grns.qtyrecd-salesorderdetails.qtyinvoiced)=0 THEN 1
		ELSE 0 END ELSE 1 END  AS FACT ,
		stockmoves.narrative as serie,
		case when stockmaster.taxcatid is null then 4 else stockmaster.taxcatid end as taxcatid,
		stockmaster.barcode,
		grns.rategr,
		purchorders.currcode
	FROM grns INNER JOIN purchorderdetails
		ON  grns.podetailitem=purchorderdetails.podetailitem
		LEFT JOIN stockmaster ON stockmaster.stockid=purchorderdetails.itemcode
		LEFT JOIN stockmoves ON stockmoves.transno=grns.grnbatch and stockmoves.`type`=25  and stockmoves.stockid=purchorderdetails.itemcode
		INNER JOIN purchorders ON purchorders.orderno = purchorderdetails.orderno
		LEFT JOIN salesorderdetails ON salesorderdetails.orderno=purchorders.requisitionno and salesorderdetails.stkcode=purchorderdetails.itemcode
	WHERE grns.grnbatch ='" . $GRN . "'
	ORDER BY grns.grnno";

$GRNResults = DB_query($SQL, $db);

$_SESSION['SuppTransTmp'] = new SuppTrans;
while ($myrow = DB_fetch_array($GRNResults)) {

    $GRNAlreadyOnInvoice                = false;
    $_SESSION['SuppTransTmp']->TaxGroup = $_SESSION['SuppTrans']->TaxGroup;

    $_SESSION['SuppTrans']->CurrCode = $myrow['currcode'];
    $_SESSION['SuppTrans']->ExRate   = $myrow['rategr'];

    $_SESSION['SuppTransTmp']->LocalTaxProvince = 1;
    foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN) {
        if ($EnteredGRN->GRNNo == $myrow['grnbatch']) {
            $GRNAlreadyOnInvoice = true;
        }
    }
    if (strlen($myrow['serie']) > 0) {
        $myrow['serie'] = ' Serie: ' . $myrow['serie'];
    }

    if (strlen($myrow['itemcode']) == 0) {

        $FlagIETU = 0;
        $typeIETU = 0;

    } else {

        $separa   = explode('|', $FlagIETU);
        $FlagIETU = $separa[0];
        $typeIETU = $separa[1];
        $FlagIETU = 0;
        $typeIETU = 0;

    }

    $percentIETU = 0;
    if ($GRNAlreadyOnInvoice == false) {

        $_SESSION['SuppTransTmp']->Add_GRN_To_Trans($myrow['grnno'],
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
            $myrow['rategr']

        );

        $_SESSION['SuppTransTmp']->GetTaxes($myrow['grnno']);

    }

}

foreach ($_SESSION['SuppTransTmp']->GRNs as $GRNTmp) {
    $_SESSION['SuppTrans']->Copy_GRN_To_Trans($GRNTmp);
    $_SESSION['SuppTrans']->GetTaxes($GRNTmp->GRNNo);
    $_SESSION['SuppTransTmp']->Remove_GRN_From_Trans($GRNTmp->GRNNo);
}
