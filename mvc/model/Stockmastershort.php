<?php 
 
class Stockmastershort {
	private $stockid;
	private $categoryid;
    private $description;
    private $units;
    private $mbflag;
    private $taxcatid; 
    private $stockact;
    private $discountglcode;
    private $salesglcode;
    private $glcode;
    private $taxrate;
    private $taxglcode;
    private $taxglcodePaid;
    private $taxamount;



    public function getStockid()
    {
        return $this->stockid;
    }

    public function setStockid($stockid)
    {
        $this->stockid = $stockid;

        return $this;
    }

    public function getCategoryid()
    {
        return $this->categoryid;
    }

    public function setCategoryid($categoryid)
    {
        $this->categoryid = $categoryid;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getUnits()
    {
        return $this->units;
    }

    public function setUnits($units)
    {
        $this->units = $units;

        return $this;
    }

    public function getMbflag()
    {
        return $this->mbflag;
    }

    public function setMbflag($mbflag)
    {
        $this->mbflag = $mbflag;

        return $this;
    }

    public function getTaxcatid()
    {
        return $this->taxcatid;
    }

    public function setTaxcatid($taxcatid)
    {
        $this->taxcatid = $taxcatid;

        return $this;
    }

    public function getStockact()
    {
        return $this->stockact;
    }

    public function setStockact($stockact)
    {
        $this->stockact = $stockact;

        return $this;
    }

    public function getDiscountglcode()
    {
        return $this->discountglcode;
    }

    public function setDiscountglcode($discountglcode)
    {
        $this->discountglcode = $discountglcode;

        return $this;
    }

    public function getSalesglcode()
    {
        return $this->salesglcode;
    }

    public function setSalesglcode($salesglcode)
    {
        $this->salesglcode = $salesglcode;

        return $this;
    }

    public function getGlcode()
    {
        return $this->glcode;
    }

    public function setGlcode($glcode)
    {
        $this->glcode = $glcode;

        return $this;
    }
    
    public function getTaxrate()
    {
        return $this->taxrate;
    }

    public function setTaxrate($taxrate)
    {
        $this->taxrate = $taxrate;

        return $this;
    }

    

    public function getTaxglcode()
    {
        return $this->taxglcode;
    }

    public function setTaxglcode($taxglcode)
    {
        $this->taxglcode = $taxglcode;

        return $this;
    }

    public function getTaxglcodePaid()
    {
        return $this->taxglcodePaid;
    }

    public function setTaxglcodePaid($taxglcodePaid)
    {
        $this->taxglcodePaid = $taxglcodePaid;

        return $this;
    }

    public function getTaxamount()
    {
        return $this->taxamount;
    }

    public function setTaxamount($taxamount)
    {
        $this->taxamount = $taxamount;

        return $this;
    }
}

?>