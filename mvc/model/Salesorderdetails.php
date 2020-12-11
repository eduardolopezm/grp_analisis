<?php
   
class Salesorderdetails{
	private $orderlineno;
    private $orderno;
    private $stkcode;
    private $fromstkloc;
    private $qtyinvoiced;
    private $unitprice;
    private $quantity;
    private $alto;
    private $ancho;
    private $calculatepricebysize;
    private $largo;
    private $quantitydispatched;
    private $qtylost;
    private $datelost;
    private $refundpercent;
    private $saletype;
    private $estimate;
    private $discountpercent;
    private $discountpercent1;
    private $discountpercent2;
    private $actualdispatchdate;
    private $completed;
    private $narrative;
    private $itemdue;
    private $poline;
    private $warranty;
    private $salestype;
    private $servicestatus;
    private $pocost;
    private $idtarea;
    private $totalrefundpercent;
    private $showdescrip;
    private $cashdiscount;
    private $readOnlyValues;
    private $modifiedpriceanddiscount;
    private $woline;
    private $stkmovid;
    private $userlost;
    private $ADevengar;
    private $Facturado;
    private $Devengado;
    private $XFacturar;
    private $AFacturar;
    private $XDevengar;
    private $nummes;
    private $localidad;
    private $idcontrato;
    


    public function getOrderlineno()
    {
        return $this->orderlineno;
    }

    public function setOrderlineno($orderlineno)
    {
        $this->orderlineno = $orderlineno;

        return $this;
    }

    public function getOrderno()
    {
        return $this->orderno;
    }

    public function setOrderno($orderno)
    {
        $this->orderno = $orderno;

        return $this;
    }

    public function getStkcode()
    {
        return $this->stkcode;
    }

    public function setStkcode($stkcode)
    {
        $this->stkcode = $stkcode;

        return $this;
    }

    public function getFromstkloc()
    {
        return $this->fromstkloc;
    }

    public function setFromstkloc($fromstkloc)
    {
        $this->fromstkloc = $fromstkloc;

        return $this;
    }

    public function getQtyinvoiced()
    {
        return $this->qtyinvoiced;
    }

    public function setQtyinvoiced($qtyinvoiced)
    {
        $this->qtyinvoiced = $qtyinvoiced;

        return $this;
    }

    public function getUnitprice()
    {
        return $this->unitprice;
    }

    public function setUnitprice($unitprice)
    {
        $this->unitprice = $unitprice;

        return $this;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getAlto()
    {
        return $this->alto;
    }

    public function setAlto($alto)
    {
        $this->alto = $alto;

        return $this;
    }

    public function getAncho()
    {
        return $this->ancho;
    }

    public function setAncho($ancho)
    {
        $this->ancho = $ancho;

        return $this;
    }

    public function getCalculatepricebysize()
    {
        return $this->calculatepricebysize;
    }

    public function setCalculatepricebysize($calculatepricebysize)
    {
        $this->calculatepricebysize = $calculatepricebysize;

        return $this;
    }

    public function getLargo()
    {
        return $this->largo;
    }

    public function setLargo($largo)
    {
        $this->largo = $largo;

        return $this;
    }

    public function getQuantitydispatched()
    {
        return $this->quantitydispatched;
    }

    public function setQuantitydispatched($quantitydispatched)
    {
        $this->quantitydispatched = $quantitydispatched;

        return $this;
    }

    public function getQtylost()
    {
        return $this->qtylost;
    }

    public function setQtylost($qtylost)
    {
        $this->qtylost = $qtylost;

        return $this;
    }

    public function getDatelost()
    {
        return $this->datelost;
    }

    public function setDatelost($datelost)
    {
        $this->datelost = $datelost;

        return $this;
    }

    public function getRefundpercent()
    {
        return $this->refundpercent;
    }

    public function setRefundpercent($refundpercent)
    {
        $this->refundpercent = $refundpercent;

        return $this;
    }

    public function getSaletype()
    {
        return $this->saletype;
    }

    public function setSaletype($saletype)
    {
        $this->saletype = $saletype;

        return $this;
    }

    public function getEstimate()
    {
        return $this->estimate;
    }

    public function setEstimate($estimate)
    {
        $this->estimate = $estimate;

        return $this;
    }

    public function getDiscountpercent()
    {
        return $this->discountpercent;
    }

    public function setDiscountpercent($discountpercent)
    {
        $this->discountpercent = $discountpercent;

        return $this;
    }

    public function getDiscountpercent1()
    {
        return $this->discountpercent1;
    }

    public function setDiscountpercent1($discountpercent1)
    {
        $this->discountpercent1 = $discountpercent1;

        return $this;
    }

    public function getDiscountpercent2()
    {
        return $this->discountpercent2;
    }

    public function setDiscountpercent2($discountpercent2)
    {
        $this->discountpercent2 = $discountpercent2;

        return $this;
    }

    public function getActualdispatchdate()
    {
        return $this->actualdispatchdate;
    }

    public function setActualdispatchdate($actualdispatchdate)
    {
        $this->actualdispatchdate = $actualdispatchdate;

        return $this;
    }

    public function getCompleted()
    {
        return $this->completed;
    }

    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }

    public function getNarrative()
    {
        return $this->narrative;
    }

    public function setNarrative($narrative)
    {
        $this->narrative = $narrative;

        return $this;
    }

    public function getItemdue()
    {
        return $this->itemdue;
    }

    public function setItemdue($itemdue)
    {
        $this->itemdue = $itemdue;

        return $this;
    }

    public function getPoline()
    {
        return $this->poline;
    }

    public function setPoline($poline)
    {
        $this->poline = $poline;

        return $this;
    }

    public function getWarranty()
    {
        return $this->warranty;
    }

    public function setWarranty($warranty)
    {
        $this->warranty = $warranty;

        return $this;
    }

    public function getSalestype()
    {
        return $this->salestype;
    }

    public function setSalestype($salestype)
    {
        $this->salestype = $salestype;

        return $this;
    }

    public function getServicestatus()
    {
        return $this->servicestatus;
    }

    public function setServicestatus($servicestatus)
    {
        $this->servicestatus = $servicestatus;

        return $this;
    }

    public function getPocost()
    {
        return $this->pocost;
    }

    public function setPocost($pocost)
    {
        $this->pocost = $pocost;

        return $this;
    }

    public function getIdtarea()
    {
        return $this->idtarea;
    }

    public function setIdtarea($idtarea)
    {
        $this->idtarea = $idtarea;

        return $this;
    }

    public function getTotalrefundpercent()
    {
        return $this->totalrefundpercent;
    }

    public function setTotalrefundpercent($totalrefundpercent)
    {
        $this->totalrefundpercent = $totalrefundpercent;

        return $this;
    }

    public function getShowdescrip()
    {
        return $this->showdescrip;
    }

    public function setShowdescrip($showdescrip)
    {
        $this->showdescrip = $showdescrip;

        return $this;
    }

    public function getCashdiscount()
    {
        return $this->cashdiscount;
    }

    public function setCashdiscount($cashdiscount)
    {
        $this->cashdiscount = $cashdiscount;

        return $this;
    }

    public function getReadOnlyValues()
    {
        return $this->readOnlyValues;
    }

    public function setReadOnlyValues($readOnlyValues)
    {
        $this->readOnlyValues = $readOnlyValues;

        return $this;
    }

    public function getModifiedpriceanddiscount()
    {
        return $this->modifiedpriceanddiscount;
    }

    public function setModifiedpriceanddiscount($modifiedpriceanddiscount)
    {
        $this->modifiedpriceanddiscount = $modifiedpriceanddiscount;

        return $this;
    }

    public function getWoline()
    {
        return $this->woline;
    }

    public function setWoline($woline)
    {
        $this->woline = $woline;

        return $this;
    }

    public function getStkmovid()
    {
        return $this->stkmovid;
    }

    public function setStkmovid($stkmovid)
    {
        $this->stkmovid = $stkmovid;

        return $this;
    }

    public function getUserlost()
    {
        return $this->userlost;
    }

    public function setUserlost($userlost)
    {
        $this->userlost = $userlost;

        return $this;
    }

    public function getADevengar()
    {
        return $this->ADevengar;
    }

    public function setADevengar($ADevengar)
    {
        $this->ADevengar = $ADevengar;

        return $this;
    }

    public function getFacturado()
    {
        return $this->Facturado;
    }

    public function setFacturado($Facturado)
    {
        $this->Facturado = $Facturado;

        return $this;
    }

    public function getDevengado()
    {
        return $this->Devengado;
    }

    public function setDevengado($Devengado)
    {
        $this->Devengado = $Devengado;

        return $this;
    }

    public function getXFacturar()
    {
        return $this->XFacturar;
    }

    public function setXFacturar($XFacturar)
    {
        $this->XFacturar = $XFacturar;

        return $this;
    }

    public function getAFacturar()
    {
        return $this->AFacturar;
    }

    public function setAFacturar($AFacturar)
    {
        $this->AFacturar = $AFacturar;

        return $this;
    }

    public function getXDevengar()
    {
        return $this->XDevengar;
    }

    public function setXDevengar($XDevengar)
    {
        $this->XDevengar = $XDevengar;

        return $this;
    }

    public function getNummes()
    {
        return $this->nummes;
    }

    public function setNummes($nummes)
    {
        $this->nummes = $nummes;

        return $this;
    }

    public function getLocalidad()
    {
        return $this->localidad;
    }

    public function setLocalidad($localidad)
    {
        $this->localidad = $localidad;

        return $this;
    }
    
    public function getIdContrato()
    {
        return $this->idcontrato;
    }

    public function setIdContrato($idcontrato)
    {
        $this->idcontrato = $idcontrato;

        return $this;
    }
}