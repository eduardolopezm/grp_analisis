<?php
/**
 * Clase para información de Orden de Compra
 *
 * @category Clase
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/09/2017
 * Fecha Modificación: 01/09/2017
 * Clase para información de Orden de Compra
 */

class PurchOrder
{
    var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
    var $CurrCode;
    var $ExRate;
    var $Initiator;
    var $deliverydate;
    var $RequisitionNo;
    var $DelAdd1;
    var $DelAdd2;
    var $DelAdd3;
    var $DelAdd4;
    var $DelAdd5;
    var $DelAdd6;
    var $Wo=0;
    var $Comments;
    var $Location;
    var $Managed;
    var $SupplierID;
    var $SupplierName;
    var $Orig_OrderDate;
    var $OrderNo; /*Only used for modification of existing orders otherwise only established when order committed */
    var $LinesOnOrder;
    var $PrintedPurchaseOrder;
    var $DatePurchaseOrderPrinted;
    var $total;
    var $GLLink; /*Is the GL link to stock activated only checked when order initiated or reading in for modification */
    var $version;
    var $Stat;
    var $StatComments;
    var $AllowPrintPO;
    var $revised;
    var $deliveryby;
    var $Narrative;
    var $barcode;
    var $Devolucion;
    var $totalpurch;
    var $stockupdate;
    var $Typeorder;
    var $ServiceType;
    var $SupplierOrderNo;
    var $contact;
    var $telephoneContact;
    var $WoDescription;
    var $PorcDevTot;
    var $tag;
    var $tagname;
    var $legalname;
    var $OrderNo2;
    var $totalSuficiencia=0;
    var $suficienciaType=0;
    var $suficienciaTransno=0;
    var $suficienciaEstatus=0;
    var $separarOrdenCompra=0;
    var $unidadEjecutora="";
    var $unidadEjecutoraNombre="";
    var $estatus="";
    
    function __construct()
    {
        /*Constructor function initialises a new purchase order object */
        $this->LineItems = array();
        $this->total=0;
        $this->LinesOnOrder=0;
    }

    function add_to_order(
        $LineNo,
        $StockID,
        $Serialised,
        $Controlled,
        $Qty,
        $ItemDescr,
        $Price,
        $Desc1,
        $Desc2,
        $Desc3,
        $UOM,
        $GLCode,
        $ReqDelDate,
        $ShiptRef,
        $completed,
        $JobRef,
        $QtyInv = 0,
        $QtyRecd = 0,
        $GLActName = '',
        $DecimalPlaces = 2,
        $itemno,
        $uom,
        $suppliers_partno,
        $subtotal_amount,
        $package,
        $pcunit,
        $nw,
        $gw,
        $cuft,
        $total_quantity,
        $total_amount,
        $Narrative = '',
        $Justification,
        $barcode = '',
        $Devolucion = 0,
        $totalpurch = 0,
        $stockupdate = 0,
        $estimated_cost = 0,
        $womasterid = '',
        $wocomponent = '',
        $clavepresupuestal = '',
        $cuentaProveedorRecepcion = '',
        $mbflag= ""
    ) {
        
        if ($Qty!=0 && isset($Qty)) {
            $this->LineItems[$LineNo] = new LineDetails(
                $LineNo,
                $StockID,
                $Serialised,
                $Controlled,
                $Qty,
                $ItemDescr,
                $Price,
                $Desc1,
                $Desc2,
                $Desc3,
                $UOM,
                $GLCode,
                $ReqDelDate,
                $ShiptRef,
                $JobRef,
                0,
                $QtyInv,
                $QtyRecd,
                $GLActName,
                $DecimalPlaces,
                $itemno,
                $uom,
                $suppliers_partno,
                $subtotal_amount,
                $package,
                $pcunit,
                $nw,
                $gw,
                $cuft,
                $total_quantity,
                $total_amount,
                $Narrative,
                $Justification,
                $barcode,
                $Devolucion,
                $totalpurch,
                $stockupdate,
                $estimated_cost,
                $womasterid,
                $wocomponent,
                $clavepresupuestal,
                $cuentaProveedorRecepcion,
                $mbflag
            );

            $this->LinesOnOrder++;
            return 1;
        }
        return 0;
    }

    function update_order_item(
        $LineNo,
        $Qty,
        $Price,
        $Desc1,
        $Desc2,
        $Desc3,
        $ItemDescription,
        $GLCode,
        $GLAccountName,
        $ReqDelDate,
        $ShiptRef,
        $JobRef,
        $itemno,
        $uom,
        $suppliers_partno,
        $subtotal_amount,
        $package,
        $pcunit,
        $nw,
        $gw,
        $cuft,
        $total_quantity,
        $total_amount,
        $Narrative,
        $Justification,
        $estimated_cost = 0,
        $clavepresupuestal = ''
    ) {

            $this->LineItems[$LineNo]->ItemDescription = $ItemDescription;
            $this->LineItems[$LineNo]->Quantity = $Qty;
            $this->LineItems[$LineNo]->Price = $Price;
            $this->LineItems[$LineNo]->Desc1 = $Desc1;
            $this->LineItems[$LineNo]->Desc2 = $Desc2;
            $this->LineItems[$LineNo]->Desc3 = $Desc3;
            $this->LineItems[$LineNo]->GLCode = $GLCode;
            $this->LineItems[$LineNo]->GLAccountName = $GLAccountName;
            $this->LineItems[$LineNo]->ReqDelDate = $ReqDelDate;
            $this->LineItems[$LineNo]->ShiptRef = $ShiptRef;
            $this->LineItems[$LineNo]->JobRef = $JobRef;
            $this->LineItems[$LineNo]->itemno = $itemno;
            $this->LineItems[$LineNo]->uom = $uom;
            $this->LineItems[$LineNo]->suppliers_partno = $suppliers_partno;
            $this->LineItems[$LineNo]->subtotal_amount = $subtotal_amount;
            $this->LineItems[$LineNo]->package = $package;
            $this->LineItems[$LineNo]->pcunit = $pcunit;
            $this->LineItems[$LineNo]->nw = $nw;
            $this->LineItems[$LineNo]->gw = $gw;
            $this->LineItems[$LineNo]->cuft = $cuft;
            $this->LineItems[$LineNo]->total_quantity = $total_quantity;
            $this->LineItems[$LineNo]->total_amount = $total_amount;
            $this->LineItems[$LineNo]->Narrative = $Narrative;
            $this->LineItems[$LineNo]->Justification = $Justification;
            $this->LineItems[$LineNo]->estimated_cost = $estimated_cost;
            $this->LineItems[$LineNo]->clavepresupuestal = $clavepresupuestal;
    }

    function remove_from_order(&$LineNo)
    {
         $this->LineItems[$LineNo]->Deleted = true;
    }


    function Any_Already_Received()
    {
        /* Checks if there have been deliveries or invoiced entered against any of the line items */
        if (count($this->LineItems)>0) {
            foreach ($this->LineItems as $OrderedItems) {
                if ($OrderedItems->QtyReceived !=0 || $OrderedItems->QtyInvoiced !=0) {
                    return 1;
                }
            }
        }
        return 0;
    }

    function Some_Already_Received($LineNo)
    {
        /* Checks if there have been deliveries or amounts invoiced against a specific line item */
        if (count($this->LineItems)>0) {
            if ($this->LineItems[$LineNo]->QtyReceived !=0 || $this->LineItems[$LineNo]->QtyInvoiced !=0) {
                return 1;
            }
        }
        return 0;
    }
    
    function Order_Value()
    {
        $TotalValue=0;
        foreach ($this->LineItems as $OrderedItems) {
            $TotalValue += ($OrderedItems->Price)*($OrderedItems->Quantity);
        }
        return $TotalValue;
    }
} /* end of class defintion */

class LineDetails
{
/* PurchOrderDetails */
    var $LineNo;
    var $PODetailRec;
    var $StockID;
    var $ItemDescription;
    var $DecimalPlaces;
    var $GLCode;
    var $GLActName;
    var $Quantity;
    var $Price;
    var $Desc1;
    var $Desc2;
    var $Desc3;
    var $Units;
    var $ReqDelDate;
    var $QtyInv;
    var $QtyReceived;
    var $StandardCost;
    var $ShiptRef;
    var $completed;
    var $JobRef;
    var $itemno;
    var $uom;
    var $suppliers_partno;
    var $subtotal_amount;
    var $leadtime;
    var $pcunit;
    var $nw;
    var $gw;
    var $cuft;
    var $total_quantity;
    var $total_amount;
    var $ReceiveQty;
    var $Deleted;
    var $Controlled;
    var $Serialised;
    var $SerialItems;  /*An array holding the batch/serial numbers and quantities in each batch*/
    var $Narrative;
    var $Justification;
    var $barcode;
    var $Devolucion;
    var $totalpurch;
    var $stockupdate;
    var $estimated_cost;
    var $womasterid;
    var $wocomponent;
    var $clavepresupuestal;
    var $cuentaProveedorRecepcion;
    var $mbflag;

    function __construct(
        $LineNo,
        $StockItem,
        $Serialised,
        $Controlled,
        $Qty,
        $ItemDescr,
        $Prc,
        $Desc1,
        $Desc2,
        $Desc3,
        $UOM,
        $GLCode,
        $ReqDelDate,
        $ShiptRef = 0,
        $Completed,
        $JobRef,
        $QtyInv,
        $QtyRecd,
        $GLActName,
        $DecimalPlaces,
        $itemno,
        $uom,
        $suppliers_partno,
        $subtotal_amount,
        $leadtime,
        $pcunit,
        $nw,
        $gw,
        $cuft,
        $total_quantity,
        $total_amount,
        $Narrative,
        $Justification,
        $barcode,
        $Devolucion,
        $totalpurch,
        $stockupdate,
        $estimated_cost = 0,
        $womasterid = '',
        $wocomponent = '',
        $clavepresupuestal = '',
        $cuentaProveedorRecepcion = '',
        $mbflag= ""
    ) {
    

    /* Constructor function to add a new LineDetail object with passed params */
        $this->LineNo = $LineNo;
        $this->StockID =$StockItem;
        $this->Controlled = $Controlled;
        $this->Serialised = $Serialised;
        $this->DecimalPlaces=$DecimalPlaces;
        $this->ItemDescription = $ItemDescr;
        $this->Quantity = $Qty;
        $this->ReqDelDate = $ReqDelDate;
        $this->Price = $Prc;
        /*****************************/
        //campos new
        $this->barcode= $barcode;
        $this->Devolucion=$Devolucion;
        $this->totalpurch=$totalpurch;
        $this->stockupdate=$stockupdate;
        /*****************************/
        $this->Desc1 = $Desc1;
        $this->Desc2 = $Desc2;
        $this->Desc3 = $Desc3;
        
        $this->Units = $UOM;
        $this->QtyReceived = $QtyRecd;
        $this->QtyInv = $QtyInv;
        $this->GLCode = $GLCode;
        $this->JobRef = $JobRef;
        $this->itemno = $itemno;
        $this->uom = $uom;
        $this->suppliers_partno = $suppliers_partno;
        $this->subtotal_amount = $subtotal_amount;
        $this->leadtime = $leadtime;
        $this->pcunit = $pcunit;
        $this->nw = $nw;
        $this->gw = $gw;
        $this->cuft = $cuft;
        $this->total_quantity = $total_quantity;
        $this->total_amount = $total_amount;
        
        $this->womasterid=$womasterid;
        $this->wocomponent=$wocomponent;
        
        $this->clavepresupuestal= $clavepresupuestal;

        $this->cuentaProveedorRecepcion = $cuentaProveedorRecepcion;
                
        if (is_numeric($ShiptRef)) {
            $this->ShiptRef = $ShiptRef;
        } else {
            $this->ShiptRef = 0;
        }
        
        $this->Completed = $Completed;
        $this->GLActName = $GLActName;
        $this->ReceiveQty =0;   /*initialise these last two only */
        $this->StandardCost =0;
        $this->Deleted=false;
        $this->SerialItems = array(); /*if Controlled then need to populate this later */
        $this->SerialItemsValid=false;
        $this->Narrative=$Narrative;
        $this->Justification=$Justification;
        $this->estimated_cost = $estimated_cost;
        $this->PorcDevTot= $PorcDevTot;
        $this->mbflag= $mbflag;
    }
}
