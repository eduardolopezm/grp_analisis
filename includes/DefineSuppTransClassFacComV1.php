<?php
/**
 * Clase para recepción de prodcutos
 *
 * @category Clase
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/09/2017
 * Fecha Modificación: 01/09/2017
 * Clases para recepción de productos
 */

class SuppTrans
{
    var $GRNs;
    var $GLCodes;
    var $Shipts;
    var $SupplierID;
    var $SupplierName;
    var $CurrCode;
    var $TermsDescription;
    var $Terms;
    var $GLLink_Creditors;
    var $GRNAct;
    var $CreditorsAct;
    var $InvoiceOrCredit;
    var $ExRate;
    var $Comments;
    var $TranDate;
    var $DueDate;
    var $SuppReference;
    var $SuppReferenceFiscal;
    var $OvAmount;
    var $OvGST;
    var $GLCodesCounter=0;
    var $ShiptsCounter=0;
    var $TaxGroup;
    var $LocalTaxProvince;
    var $TaxGroupDescription;
    var $Taxes;
    var $tagref;
    var $stkmoveno;
    var $location;
    var $invoice;
    var $TaxCategory;
    var $SerieNo;
    var $FlagIETU;
    var $typeIETU;
    var $percentIETU;
    var $barcode;
    var $rategr;
    var $typecostid;
    var $consignment;
    var $unidadEjecutoraGeneral="";

    function __construct()
    {
        $this->GRNs = array();
        $this->GLCodes = array();
        $this->Shipts = array();
        $this->TaxesOthers = array();
    }
    
    function GetTaxes($GRNNo)
    {
        global $db;

        $SQL = "SELECT taxgrouptaxes.calculationorder,
                            taxauthorities.description,
                            taxgrouptaxes.taxauthid,
                            taxauthorities.purchtaxglaccount,
                            taxgrouptaxes.taxontax,
                            taxauthrates.taxrate
            FROM taxauthrates INNER JOIN taxgrouptaxes ON
                taxauthrates.taxauthority=taxgrouptaxes.taxauthid
                INNER JOIN taxauthorities ON
                taxauthrates.taxauthority=taxauthorities.taxid
            WHERE taxgrouptaxes.taxgroupid='" . $this->TaxGroup . "' 
            AND taxauthrates.dispatchtaxprovince='" . $this->LocalTaxProvince . "' 
            AND taxauthrates.taxcatid = '" . $this->GRNs[$GRNNo]->TaxCategory . "'
            ORDER BY taxgrouptaxes.calculationorder";
        if ($_SESSION['UserID'] == "admin") {
            echo '<pre>'.$SQL;
        }
        //echo $SQL.'<br>';
        $ErrMsg = _('The taxes and rates for this item could not be retrieved because');
        $GetTaxRatesResult = DB_query($SQL, $db, $ErrMsg);
        
        while ($myrow = DB_fetch_array($GetTaxRatesResult)) {
            $this->GRNs[$GRNNo]->Taxes[$myrow['calculationorder']] = new Tax(
                $myrow['calculationorder'],
                $myrow['taxauthid'],
                $myrow['description'],
                $myrow['taxrate'],
                $myrow['taxontax'],
                $myrow['purchtaxglaccount']
            );
        }
    }
    
    function GetTaxesOthers()
    {
        global $db;

        $SQL = "SELECT taxgrouptaxes.calculationorder,
                    taxauthorities.description,
                    taxgrouptaxes.taxauthid,
                    taxauthorities.purchtaxglaccount,
                    taxgrouptaxes.taxontax,
                    taxauthrates.taxrate
            FROM taxauthrates INNER JOIN taxgrouptaxes ON
                taxauthrates.taxauthority=taxgrouptaxes.taxauthid
                INNER JOIN taxauthorities ON
                taxauthrates.taxauthority=taxauthorities.taxid
            WHERE taxgrouptaxes.taxgroupid='" . $this->TaxGroup . "'
            AND taxauthrates.dispatchtaxprovince='" . $this->LocalTaxProvince . "' 
            AND taxauthrates.taxcatid = '" . $_SESSION['DefaultTaxCategory'] . "'
            ORDER BY taxgrouptaxes.calculationorder";
        
        //echo $SQL.'<br>';
        $ErrMsg = _('The taxes and rates for this item could not be retrieved because');
        $GetTaxRatesResult = DB_query($SQL, $db, $ErrMsg);
        
        while ($myrow = DB_fetch_array($GetTaxRatesResult)) {
            $this->Taxes[$myrow['calculationorder']]= new Tax(
                $myrow['calculationorder'],
                $myrow['taxauthid'],
                $myrow['description'],
                $myrow['taxrate'],
                $myrow['taxontax'],
                $myrow['purchtaxglaccount']
            );
        }
    }
    
    function Add_GRN_To_Trans(
        $GRNNo,
        $PODetailItem,
        $ItemCode,
        $ItemDescription,
        $QtyRecd,
        $Prev_QuantityInv,
        $This_QuantityInv,
        $OrderPrice,
        $Desc1,
        $Desc2,
        $Desc3,
        $ChgPrice,
        $Complete,
        $StdCostUnit = 0,
        $ShiptRef = 0,
        $JobRef,
        $GLCode,
        $PONo,
        $tagref = 0,
        $stkmoveno = 0,
        $location = '',
        $invoice,
        $SerieNo,
        $TaxCategory,
        $FlagIETU,
        $typeIETU,
        $percentIETU,
        $barcode,
        $rategr,
        $consigment = '',
        $viajeId = 0,
        $tagname = '',
        $unidadEjecutora = '',
        $unidadEjecutoraName = '',
        $realorderno = 0,
        $deliverydate = '',
        $comments = '',
        $clavepresupuestal = '',
        $ln_clave_iden = ''
    ) {
        // echo '<br>unidadEjecutora 1:'.$unidadEjecutora;
        if ($This_QuantityInv!=0 and isset($This_QuantityInv)) {
            $this->GRNs[$GRNNo] = new GRNs(
                $GRNNo,
                $PODetailItem,
                $ItemCode,
                $ItemDescription,
                $QtyRecd,
                $Prev_QuantityInv,
                $This_QuantityInv,
                $OrderPrice,
                $Desc1,
                $Desc2,
                $Desc3,
                $ChgPrice,
                $Complete,
                $StdCostUnit,
                $ShiptRef,
                $JobRef,
                $GLCode,
                $PONo,
                $tagref,
                $stkmoveno,
                $location,
                $invoice,
                $SerieNo,
                $TaxCategory,
                $FlagIETU,
                $typeIETU,
                $percentIETU,
                $barcode,
                $rategr,
                $consigment,
                $viajeId,
                $tagname,
                $unidadEjecutora,
                $unidadEjecutoraName,
                $realorderno,
                $deliverydate,
                $comments,
                $clavepresupuestal,
                $ln_clave_iden
            );
            return 1;
        }
        return 0;
    }

    function Modify_GRN_To_Trans(
        $GRNNo,
        $PODetailItem,
        $ItemCode,
        $ItemDescription,
        $QtyRecd,
        $Prev_QuantityInv,
        $This_QuantityInv,
        $OrderPrice,
        $Desc1,
        $Desc2,
        $Desc3,
        $ChgPrice,
        $Complete,
        $StdCostUnit,
        $ShiptRef,
        $JobRef,
        $GLCode
    ) {
        if ($This_QuantityInv!=0 && isset($This_QuantityInv)) {
            $this->GRNs[$GRNNo]->Modify(
                $PODetailItem,
                $ItemCode,
                $ItemDescription,
                $QtyRecd,
                $Prev_QuantityInv,
                $This_QuantityInv,
                $OrderPrice,
                $Desc1,
                $Desc2,
                $Desc3,
                $ChgPrice,
                $Complete,
                $StdCostUnit,
                $ShiptRef,
                $JobRef,
                $GLCode
            );
            return 1;
        }
        return 0;
    }

    function Copy_GRN_To_Trans($GRNSrc)
    {
        if ($GRNSrc->This_QuantityInv!=0 && isset($GRNSrc->This_QuantityInv)) {
            $this->GRNs[$GRNSrc->GRNNo] = new GRNs(
                $GRNSrc->GRNNo,
                $GRNSrc->PODetailItem,
                $GRNSrc->ItemCode,
                $GRNSrc->ItemDescription,
                $GRNSrc->QtyRecd,
                $GRNSrc->Prev_QuantityInv,
                $GRNSrc->This_QuantityInv,
                $GRNSrc->OrderPrice,
                $GRNSrc->Desc1,
                $GRNSrc->Desc2,
                $GRNSrc->Desc3,
                $GRNSrc->ChgPrice,
                $GRNSrc->Complete,
                $GRNSrc->StdCostUnit,
                $GRNSrc->ShiptRef,
                $GRNSrc->JobRef,
                $GRNSrc->GLCode,
                $GRNSrc->PONo,
                $GRNSrc->tagref,
                $GRNSrc->stkmoveno,
                $GRNSrc->location,
                $GRNSrc->invoice,
                $GRNSrc->SerieNo,
                $GRNSrc->TaxCategory,
                $GRNSrc->FlagIETU,
                $GRNSrc->typeIETU,
                $GRNSrc->percentIETU,
                $GRNSrc->barcode,
                $GRNSrc->rategr,
                $GRNSrc->consignment,
                $GRNSrc->viajeId,
                $GRNSrc->tagname,
                $GRNSrc->unidadEjecutora,
                $GRNSrc->unidadEjecutoraName,
                $GRNSrc->realorderno,
                $GRNSrc->deliverydate,
                $GRNSrc->comments,
                $GRNSrc->clavepresupuestal,
                $GRNSrc->ln_clave_iden
            );
            return 1;
        }
        return 0;
    }

    function Add_GLCodes_To_Trans(
        $GLCode,
        $GLActName,
        $Amount,
        $JobRef,
        $Narrative,
        $tagref,
        $FlagIETU,
        $typeIETU,
        $percentIETU
    ) {
        if ($Amount!=0 and isset($Amount)) {
            $this->GLCodes[$this->GLCodesCounter] = new GLCodes(
                $this->GLCodesCounter,
                $GLCode,
                $GLActName,
                $Amount,
                $JobRef,
                $Narrative,
                $tagref,
                $FlagIETU,
                $typeIETU,
                $percentIETU
            );
            $this->GLCodesCounter++;
            return 1;
        }
        return 0;
    }

    function Add_Shipt_To_Trans($ShiptRef, $Concepto, $Amount, $tagref, $typecostid = 1, $account)
    {
        //global $db;
        if ($Amount!=0) {
            //obtener cuenta del tipo de costo
            //$qry = "Select * FROM shiptypecost WHERE typecostid = $typecostid";
            //$rs = DB_query($qry, $db);
            //$row = DB_fetch_array($rs);
            
            $this->Shipts[$this->ShiptCounter] = new Shipment(
                $this->ShiptCounter,
                $ShiptRef,
                $Concepto,
                $Amount,
                $tagref,
                $typecostid,
                $account
            );
            $this->ShiptCounter++;
            return 1;
        }
        return 0;
    }

    function Remove_GRN_From_Trans(&$GRNNo)
    {
         unset($this->GRNs[$GRNNo]);
    }
    function Remove_GLCodes_From_Trans(&$GLCodeCounter)
    {
         unset($this->GLCodes[$GLCodeCounter]);
    }
    function Remove_Shipt_From_Trans(&$ShiptCounter)
    {
         unset($this->Shipts[$ShiptCounter]);
    }
}

class GRNs
{
    var $GRNNo;
    var $PODetailItem;
    var $ItemCode;
    var $ItemDescription;
    var $QtyRecd;
    var $Prev_QuantityInv;
    var $This_QuantityInv;
    var $OrderPrice;
    var $Desc1;
    var $Desc2;
    var $Desc3;
    var $ChgPrice;
    var $Complete;
    var $StdCostUnit;
    var $ShiptRef;
    var $JobRef;
    var $GLCode;
    var $PONo;
    var $tagref;
    var $stkmoveno;
    var $location;
    var $invoice;
    var $TaxCategory;
    var $SerieNo;
    var $FlagIETU;
    var $typeIETU;
    var $percentIETU;
    var $barcode;
    var $rategr;
    var $consignment;
    var $viajeId;
    var $tagname;
    var $unidadEjecutora;
    var $unidadEjecutoraName;
    var $realorderno;
    var $deliverydate;
    var $comments;
    var $clavepresupuestal;
    var $ln_clave_iden;
    function __construct(
        $GRNNo,
        $PODetailItem,
        $ItemCode,
        $ItemDescription,
        $QtyRecd,
        $Prev_QuantityInv,
        $This_QuantityInv,
        $OrderPrice,
        $Desc1,
        $Desc2,
        $Desc3,
        $ChgPrice,
        $Complete,
        $StdCostUnit = 0,
        $ShiptRef,
        $JobRef,
        $GLCode,
        $PONo,
        $tagref = 0,
        $stkmoveno,
        $location,
        $invoice,
        $SerieNo,
        $TaxCategory,
        $FlagIETU,
        $typeIETU,
        $percentIETU,
        $barcode,
        $rategr,
        $consignment = '',
        $viajeId = 0,
        $tagname,
        $unidadEjecutora = '',
        $unidadEjecutoraName = '',
        $realorderno = 0,
        $deliverydate = '',
        $comments = '',
        $clavepresupuestal = '',
        $ln_clave_iden = ''
    ) {
        // echo "<br>unidadEjecutora 2: ".$unidadEjecutora;
        $this->GRNNo = $GRNNo;
        $this->PODetailItem = $PODetailItem;
        $this->ItemCode = $ItemCode;
        $this->ItemDescription = $ItemDescription;
        $this->QtyRecd = $QtyRecd;
        $this->Prev_QuantityInv = $Prev_QuantityInv;
        $this->This_QuantityInv = $This_QuantityInv;
        $this->OrderPrice =$OrderPrice;
        $this->Desc1 =$Desc1;
        $this->Desc2 =$Desc2;
        $this->Desc3 =$Desc3;
        $this->ChgPrice = $ChgPrice;
        $this->Complete = $Complete;
        $this->StdCostUnit = $StdCostUnit;
        $this->ShiptRef = $ShiptRef;
        $this->JobRef = $JobRef;
        $this->GLCode = $GLCode;
        $this->PONo = $PONo;
        $this->tagref = $tagref;
        $this->stkmoveno =$stkmoveno;
        $this->location =$location;
        $this->invoice =$invoice;
        $this->TaxCategory=$TaxCategory;
        $this->Taxes = array();
        $this->TaxCategory;
        $this->SerieNo=$SerieNo;
        $this->FlagIETU=$FlagIETU;
        $this->typeIETU=$typeIETU;
        $this->percentIETU=$percentIETU;
        $this->barcode=$barcode;
        $this->rategr=$rategr;
        $this->consignment = $consignment;
        $this->viajeId = $viajeId;
        $this->tagname= $tagname;
        $this->unidadEjecutora = $unidadEjecutora;
        $this->unidadEjecutoraName = $unidadEjecutoraName;
        $this->realorderno = $realorderno;
        $this->deliverydate = $deliverydate;
        $this->comments = $comments;
        $this->clavepresupuestal = $clavepresupuestal;
        $this->ln_clave_iden = $ln_clave_iden;
    }

    function Modify(
        $PODetailItem,
        $ItemCode,
        $ItemDescription,
        $QtyRecd,
        $Prev_QuantityInv,
        $This_QuantityInv,
        $OrderPrice,
        $Desc1,
        $Desc2,
        $Desc3,
        $ChgPrice,
        $Complete,
        $StdCostUnit,
        $ShiptRef,
        $JobRef,
        $GLCode
    ) {
        $this->PODetailItem = $PODetailItem;
        $this->ItemCode = $ItemCode;
        $this->ItemDescription = $ItemDescription;
        $this->QtyRecd = $QtyRecd;
        $this->Prev_QuantityInv = $Prev_QuantityInv;
        $this->This_QuantityInv = $This_QuantityInv;
        $this->OrderPrice =$OrderPrice;
        $this->Desc1 =$Desc1;
        $this->Desc2 =$Desc2;
        $this->Desc3 =$Desc3;
        $this->ChgPrice = $ChgPrice;
        $this->Complete = $Complete;
        $this->StdCostUnit = $StdCostUnit;
        $this->ShiptRef = $ShiptRef;
        $this->JobRef = $JobRef;
        $this->GLCode = $GLCode;
    }
}


class GLCodes
{
    var $Counter;
    var $GLCode;
    var $GLActName;
    var $Amount;
    var $JobRef;
    var $Narrative;
    var $tagref;
    var $FlagIETU;
    var $typeIETU;
    var $percentIETU;
    function __construct($Counter, $GLCode, $GLActName, $Amount, $JobRef, $Narrative, $tagref, $FlagIETU, $typeIETU, $percentIETU)
    {
        $this->Counter = $Counter;
        $this->GLCode = $GLCode;
        $this->GLActName = $GLActName;
        $this->Amount = $Amount;
        $this->JobRef = $JobRef;
        $this->Narrative= $Narrative;
        $this->tagref= $tagref;
        $this->FlagIETU=$FlagIETU;
        $this->typeIETU=$typeIETU;
        $this->percentIETU=$percentIETU;
    }
}

class Shipment
{
    var $Counter;
    var $ShiptRef;
    var $Concepto;
    var $Amount;
    var $tagref;
    var $typecostid;
    var $account;
    function __construct($Counter, $ShiptRef, $Concepto, $Amount, $tagref, $typecostid, $account)
    {
        $this->Counter = $Counter;
        $this->ShiptRef = $ShiptRef;
        $this->Concepto = $Concepto;
        $this->Amount = $Amount;
        $this->tagref = $tagref;
        $this->typecostid = $typecostid;
        $this->account = $account;
    }
}

class Tax
{
    var $TaxCalculationOrder;
    var $TaxAuthID;
    var $TaxAuthDescription;
    var $TaxRate;
    var $TaxOnTax;
    var $TaxGLCode;
    var $TaxOvAmount;
        
    function __construct(
        $TaxCalculationOrder,
        $TaxAuthID,
        $TaxAuthDescription,
        $TaxRate,
        $TaxOnTax,
        $TaxGLCode
    ) {
        $this->TaxCalculationOrder = $TaxCalculationOrder;
        $this->TaxAuthID = $TaxAuthID;
        $this->TaxAuthDescription = $TaxAuthDescription;
        $this->TaxRate =  $TaxRate;
        $this->TaxOnTax = $TaxOnTax;
        $this->TaxGLCode = $TaxGLCode;
    }
}

class TaxOthers
{
    var $TaxCalculationOrder;
    var $TaxAuthID;
    var $TaxAuthDescription;
    var $TaxRate;
    var $TaxOnTax;
    var $TaxGLCode;
    var $TaxOvAmount;
        
    function __construct(
        $TaxCalculationOrder,
        $TaxAuthID,
        $TaxAuthDescription,
        $TaxRate,
        $TaxOnTax,
        $TaxGLCode
    ) {
        $this->TaxCalculationOrder = $TaxCalculationOrder;
        $this->TaxAuthID = $TaxAuthID;
        $this->TaxAuthDescription = $TaxAuthDescription;
        $this->TaxRate =  $TaxRate;
        $this->TaxOnTax = $TaxOnTax;
        $this->TaxGLCode = $TaxGLCode;
    }
}
