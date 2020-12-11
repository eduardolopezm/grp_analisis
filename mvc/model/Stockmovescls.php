<?php
/**/  
class Stockmovescls {
	private $stkmoveno;
	private $stockid;
	private $type;
	private $transno;
	private $loccode;
	private $trandate;
	private $debtorno;
	private $branchcode;
	private $price;
	private $prd;
	private $reference;
	private $qty;
	private $equivalentqty;
	private $discountpercent;
	private $standardcost;
	private $show_on_inv_crds;
	private $newqoh;
	private $hidemovt;
	private $narrative;
	private $warranty;
	private $tagref;
	private $discountpercent1;
	private $discountpercent2;
	private $totaldescuento;
	private $avgcost;
	private $standardcostv2;
	private $nuevocosto;
	private $ref1;
	private $ref2;
	private $ref3;
	private $ref4;
	private $qty2;
	private $showdescription;
	private $refundpercentmv;
	private $qtyinvoiced;
	private $qty_sent;
	private $ratemov;
	private $useridmov;
	private $FlagValExistencias;
	private $stkmovid;
	private $currcode;
	private $nomes;
	private $stockclie;
	private $localidad;
	private $qty_excess;
	private $secondfactorconversion;
	private $register;
	private $pietablon;	


    public function getStkmoveno()
    {
        return $this->stkmoveno;
    }

    public function setStkmoveno($stkmoveno)
    {
        $this->stkmoveno = $stkmoveno;

        return $this;
    }

    public function getStockid()
    {
        return $this->stockid;
    }

    public function setStockid($stockid)
    {
        $this->stockid = $stockid;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getTransno()
    {
        return $this->transno;
    }

    public function setTransno($transno)
    {
        $this->transno = $transno;

        return $this;
    }

    public function getLoccode()
    {
        return $this->loccode;
    }

    public function setLoccode($loccode)
    {
        $this->loccode = $loccode;

        return $this;
    }

    public function getTrandate()
    {
        return $this->trandate;
    }

    public function setTrandate($trandate)
    {
        $this->trandate = $trandate;

        return $this;
    }

    public function getDebtorno()
    {
        return $this->debtorno;
    }

    public function setDebtorno($debtorno)
    {
        $this->debtorno = $debtorno;

        return $this;
    }

    public function getBranchcode()
    {
        return $this->branchcode;
    }

    public function setBranchcode($branchcode)
    {
        $this->branchcode = $branchcode;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    public function getPrd()
    {
        return $this->prd;
    }

    public function setPrd($prd)
    {
        $this->prd = $prd;

        return $this;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    public function getQty()
    {
        return $this->qty;
    }

    public function setQty($qty)
    {
        $this->qty = $qty;

        return $this;
    }

    public function getEquivalentqty()
    {
        return $this->equivalentqty;
    }

    public function setEquivalentqty($equivalentqty)
    {
        $this->equivalentqty = $equivalentqty;

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

    public function getStandardcost()
    {
        return $this->standardcost;
    }

    public function setStandardcost($standardcost)
    {
        $this->standardcost = $standardcost;

        return $this;
    }

    public function getShow_on_inv_crds()
    {
        return $this->show_on_inv_crds;
    }

    public function setShow_on_inv_crds($show_on_inv_crds)
    {
        $this->show_on_inv_crds = $show_on_inv_crds;

        return $this;
    }

    public function getNewqoh()
    {
        return $this->newqoh;
    }

    public function setNewqoh($newqoh)
    {
        $this->newqoh = $newqoh;

        return $this;
    }

    public function getHidemovt()
    {
        return $this->hidemovt;
    }

    public function setHidemovt($hidemovt)
    {
        $this->hidemovt = $hidemovt;

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

    public function getWarranty()
    {
        return $this->warranty;
    }

    public function setWarranty($warranty)
    {
        $this->warranty = $warranty;

        return $this;
    }

    public function getTagref()
    {
        return $this->tagref;
    }

    public function setTagref($tagref)
    {
        $this->tagref = $tagref;

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

    public function getTotaldescuento()
    {
        return $this->totaldescuento;
    }

    public function setTotaldescuento($totaldescuento)
    {
        $this->totaldescuento = $totaldescuento;

        return $this;
    }

    public function getAvgcost()
    {
        return $this->avgcost;
    }

    public function setAvgcost($avgcost)
    {
        $this->avgcost = $avgcost;

        return $this;
    }

    public function getStandardcostv2()
    {
        return $this->standardcostv2;
    }

    public function setStandardcostv2($standardcostv2)
    {
        $this->standardcostv2 = $standardcostv2;

        return $this;
    }

    public function getNuevocosto()
    {
        return $this->nuevocosto;
    }

    public function setNuevocosto($nuevocosto)
    {
        $this->nuevocosto = $nuevocosto;

        return $this;
    }

    public function getRef1()
    {
        return $this->ref1;
    }

    public function setRef1($ref1)
    {
        $this->ref1 = $ref1;

        return $this;
    }

    public function getRef2()
    {
        return $this->ref2;
    }

    public function setRef2($ref2)
    {
        $this->ref2 = $ref2;

        return $this;
    }

    public function getRef3()
    {
        return $this->ref3;
    }

    public function setRef3($ref3)
    {
        $this->ref3 = $ref3;

        return $this;
    }

    public function getRef4()
    {
        return $this->ref4;
    }

    public function setRef4($ref4)
    {
        $this->ref4 = $ref4;

        return $this;
    }

    public function getQty2()
    {
        return $this->qty2;
    }

    public function setQty2($qty2)
    {
        $this->qty2 = $qty2;

        return $this;
    }

    public function getShowdescription()
    {
        return $this->showdescription;
    }

    public function setShowdescription($showdescription)
    {
        $this->showdescription = $showdescription;

        return $this;
    }

    public function getRefundpercentmv()
    {
        return $this->refundpercentmv;
    }

    public function setRefundpercentmv($refundpercentmv)
    {
        $this->refundpercentmv = $refundpercentmv;

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

    public function getQty_sent()
    {
        return $this->qty_sent;
    }

    public function setQty_sent($qty_sent)
    {
        $this->qty_sent = $qty_sent;

        return $this;
    }

    public function getRatemov()
    {
        return $this->ratemov;
    }

    public function setRatemov($ratemov)
    {
        $this->ratemov = $ratemov;

        return $this;
    }

    public function getUseridmov()
    {
        return $this->useridmov;
    }

    public function setUseridmov($useridmov)
    {
        $this->useridmov = $useridmov;

        return $this;
    }

    public function getFlagValExistencias()
    {
        return $this->FlagValExistencias;
    }

    public function setFlagValExistencias($FlagValExistencias)
    {
        $this->FlagValExistencias = $FlagValExistencias;

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

    public function getCurrcode()
    {
        return $this->currcode;
    }

    public function setCurrcode($currcode)
    {
        $this->currcode = $currcode;

        return $this;
    }

    public function getNomes()
    {
        return $this->nomes;
    }

    public function setNomes($nomes)
    {
        $this->nomes = $nomes;

        return $this;
    }

    public function getStockclie()
    {
        return $this->stockclie;
    }

    public function setStockclie($stockclie)
    {
        $this->stockclie = $stockclie;

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

    public function getQty_excess()
    {
        return $this->qty_excess;
    }

    public function setQty_excess($qty_excess)
    {
        $this->qty_excess = $qty_excess;

        return $this;
    }

    public function getSecondfactorconversion()
    {
        return $this->secondfactorconversion;
    }

    public function setSecondfactorconversion($secondfactorconversion)
    {
        $this->secondfactorconversion = $secondfactorconversion;

        return $this;
    }

    public function getRegister()
    {
        return $this->register;
    }

    public function setRegister($register)
    {
        $this->register = $register;

        return $this;
    }

    public function getPietablon()
    {
        return $this->pietablon;
    }

    public function setPietablon($pietablon)
    {
        $this->pietablon = $pietablon;

        return $this;
    }
}

?>