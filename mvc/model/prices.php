<?php
 
class Prices {
	private $stockid;
	private $typeabbrev;
	private $currabrev;
	private $debtorno;
	private $price;
	private $branchcode;
	private $areacode;
	private $bgcolor;
	private $margin;
	private $percentdesc;
	

    public function getStockid()
    {
        return $this->stockid;
    }

    public function setStockid($stockid)
    {
        $this->stockid = $stockid;

        return $this;
    }

    public function getTypeabbrev()
    {
        return $this->typeabbrev;
    }

    public function setTypeabbrev($typeabbrev)
    {
        $this->typeabbrev = $typeabbrev;

        return $this;
    }

    public function getCurrabrev()
    {
        return $this->currabrev;
    }

    public function setCurrabrev($currabrev)
    {
        $this->currabrev = $currabrev;

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

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;

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

    public function getAreacode()
    {
        return $this->areacode;
    }

    public function setAreacode($areacode)
    {
        $this->areacode = $areacode;

        return $this;
    }

    public function getBgcolor()
    {
        return $this->bgcolor;
    }

    public function setBgcolor($bgcolor)
    {
        $this->bgcolor = $bgcolor;

        return $this;
    }

    public function getMargin()
    {
        return $this->margin;
    }

    public function setMargin($margin)
    {
        $this->margin = $margin;

        return $this;
    }

    public function getPercentdesc()
    {
        return $this->percentdesc;
    }

    public function setPercentdesc($percentdesc)
    {
        $this->percentdesc = $percentdesc;

        return $this;
    }
}

?>