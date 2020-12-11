<?php
   
class Stockmaster {
	private $stockid;
	private $spes;
	private $categoryid;
	private $description;
	private $longdescription;
	private $manufacturer;
	private $stockautor;
	private $units;
	private $mbflag;
	private $lastcurcostdate;
	private $actualcost;
	private $lastcost;
	private $materialcost;
	private $labourcost;
	private $overheadcost;
	private $lowestlevel;
	private $discontinued;
	private $controlled;
	private $enviarmasisa;
	private $eoq;
	private $volume;
	private $kgs;
	private $barcode;
	private $discountcategory;
	private $taxcatid;
	private $taxcatidret;
	private $serialised;
	private $appendfile;
	private $perishable;
	private $decimalplaces;
	private $nextserialno;
	private $pansize;
	private $shrinkfactor;
	private $netweight;
	private $idclassproduct;
	private $stocksupplier;
	private $securitypoint;
	private $idetapaflujo;
	private $OrigenCountry;
	private $OrigenDate;
	private $stockupdate;
	private $pkg_type;
	private $isbn;
	private $grade;
	private $subject;
	private $deductibleflag;
	private $u_typeoperation;
	private $typeoperationdiot;
	private $height;
	private $width;
	private $large;
	private $fichatecnica;
	private $percentfactorigi;
	private $inpdfgroup;
	private $flagadvance;
	private $eq_conversion_factor;
	private $eq_stockid;
	private $flagcommission;
	private $fijo;
	private $fecha_modificacion;
	private $unitequivalent;
	private $factorconversionpaq;
	private $factorconversionpz;
	private $stockneodata;
	private $purchgroup;
	private $idjerarquia;
	private $addunits;
	private $secuunits;
	private $recipeunits;
	private $factorrecipe;
	private $addcategory;
	private $deliverydays;
	private $tolerancedays;
	private $estatusstock;
	private $eq_conversion_costo;
	private $extracolone;
	private $extracoltwo;
	private $extracolthree;
	private $unitstemporal;
	private $SAPActualiza;
	private $depreciacion;
	private $valor_inicial;
	




    public function getStockid()
    {
        return $this->stockid;
    }

    public function setStockid($stockid)
    {
        $this->stockid = $stockid;

        return $this;
    }

    public function getSpes()
    {
        return $this->spes;
    }

    public function setSpes($spes)
    {
        $this->spes = $spes;

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

    public function getLongdescription()
    {
        return $this->longdescription;
    }

    public function setLongdescription($longdescription)
    {
        $this->longdescription = $longdescription;

        return $this;
    }

    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getStockautor()
    {
        return $this->stockautor;
    }

    public function setStockautor($stockautor)
    {
        $this->stockautor = $stockautor;

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

    public function getLastcurcostdate()
    {
        return $this->lastcurcostdate;
    }

    public function setLastcurcostdate($lastcurcostdate)
    {
        $this->lastcurcostdate = $lastcurcostdate;

        return $this;
    }

    public function getActualcost()
    {
        return $this->actualcost;
    }

    public function setActualcost($actualcost)
    {
        $this->actualcost = $actualcost;

        return $this;
    }

    public function getLastcost()
    {
        return $this->lastcost;
    }

    public function setLastcost($lastcost)
    {
        $this->lastcost = $lastcost;

        return $this;
    }

    public function getMaterialcost()
    {
        return $this->materialcost;
    }

    public function setMaterialcost($materialcost)
    {
        $this->materialcost = $materialcost;

        return $this;
    }

    public function getLabourcost()
    {
        return $this->labourcost;
    }

    public function setLabourcost($labourcost)
    {
        $this->labourcost = $labourcost;

        return $this;
    }

    public function getOverheadcost()
    {
        return $this->overheadcost;
    }

    public function setOverheadcost($overheadcost)
    {
        $this->overheadcost = $overheadcost;

        return $this;
    }

    public function getLowestlevel()
    {
        return $this->lowestlevel;
    }

    public function setLowestlevel($lowestlevel)
    {
        $this->lowestlevel = $lowestlevel;

        return $this;
    }

    public function getDiscontinued()
    {
        return $this->discontinued;
    }

    public function setDiscontinued($discontinued)
    {
        $this->discontinued = $discontinued;

        return $this;
    }

    public function getControlled()
    {
        return $this->controlled;
    }

    public function setControlled($controlled)
    {
        $this->controlled = $controlled;

        return $this;
    }

    public function getEnviarmasisa()
    {
        return $this->enviarmasisa;
    }

    public function setEnviarmasisa($enviarmasisa)
    {
        $this->enviarmasisa = $enviarmasisa;

        return $this;
    }

    public function getEoq()
    {
        return $this->eoq;
    }

    public function setEoq($eoq)
    {
        $this->eoq = $eoq;

        return $this;
    }

    public function getVolume()
    {
        return $this->volume;
    }

    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
    }

    public function getKgs()
    {
        return $this->kgs;
    }

    public function setKgs($kgs)
    {
        $this->kgs = $kgs;

        return $this;
    }

    public function getBarcode()
    {
        return $this->barcode;
    }

    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;

        return $this;
    }

    public function getDiscountcategory()
    {
        return $this->discountcategory;
    }

    public function setDiscountcategory($discountcategory)
    {
        $this->discountcategory = $discountcategory;

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

    public function getTaxcatidret()
    {
        return $this->taxcatidret;
    }

    public function setTaxcatidret($taxcatidret)
    {
        $this->taxcatidret = $taxcatidret;

        return $this;
    }

    public function getSerialised()
    {
        return $this->serialised;
    }

    public function setSerialised($serialised)
    {
        $this->serialised = $serialised;

        return $this;
    }

    public function getAppendfile()
    {
        return $this->appendfile;
    }

    public function setAppendfile($appendfile)
    {
        $this->appendfile = $appendfile;

        return $this;
    }

    public function getPerishable()
    {
        return $this->perishable;
    }

    public function setPerishable($perishable)
    {
        $this->perishable = $perishable;

        return $this;
    }

    public function getDecimalplaces()
    {
        return $this->decimalplaces;
    }

    public function setDecimalplaces($decimalplaces)
    {
        $this->decimalplaces = $decimalplaces;

        return $this;
    }

    public function getNextserialno()
    {
        return $this->nextserialno;
    }

    public function setNextserialno($nextserialno)
    {
        $this->nextserialno = $nextserialno;

        return $this;
    }

    public function getPansize()
    {
        return $this->pansize;
    }

    public function setPansize($pansize)
    {
        $this->pansize = $pansize;

        return $this;
    }

    public function getShrinkfactor()
    {
        return $this->shrinkfactor;
    }

    public function setShrinkfactor($shrinkfactor)
    {
        $this->shrinkfactor = $shrinkfactor;

        return $this;
    }

    public function getNetweight()
    {
        return $this->netweight;
    }

    public function setNetweight($netweight)
    {
        $this->netweight = $netweight;

        return $this;
    }

    public function getIdclassproduct()
    {
        return $this->idclassproduct;
    }

    public function setIdclassproduct($idclassproduct)
    {
        $this->idclassproduct = $idclassproduct;

        return $this;
    }

    public function getStocksupplier()
    {
        return $this->stocksupplier;
    }

    public function setStocksupplier($stocksupplier)
    {
        $this->stocksupplier = $stocksupplier;

        return $this;
    }

    public function getSecuritypoint()
    {
        return $this->securitypoint;
    }

    public function setSecuritypoint($securitypoint)
    {
        $this->securitypoint = $securitypoint;

        return $this;
    }

    public function getIdetapaflujo()
    {
        return $this->idetapaflujo;
    }

    public function setIdetapaflujo($idetapaflujo)
    {
        $this->idetapaflujo = $idetapaflujo;

        return $this;
    }

    public function getOrigenCountry()
    {
        return $this->OrigenCountry;
    }

    public function setOrigenCountry($OrigenCountry)
    {
        $this->OrigenCountry = $OrigenCountry;

        return $this;
    }

    public function getOrigenDate()
    {
        return $this->OrigenDate;
    }

    public function setOrigenDate($OrigenDate)
    {
        $this->OrigenDate = $OrigenDate;

        return $this;
    }

    public function getStockupdate()
    {
        return $this->stockupdate;
    }

    public function setStockupdate($stockupdate)
    {
        $this->stockupdate = $stockupdate;

        return $this;
    }

    public function getPkg_type()
    {
        return $this->pkg_type;
    }

    public function setPkg_type($pkg_type)
    {
        $this->pkg_type = $pkg_type;

        return $this;
    }

    public function getIsbn()
    {
        return $this->isbn;
    }

    public function setIsbn($isbn)
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getGrade()
    {
        return $this->grade;
    }

    public function setGrade($grade)
    {
        $this->grade = $grade;

        return $this;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function getDeductibleflag()
    {
        return $this->deductibleflag;
    }

    public function setDeductibleflag($deductibleflag)
    {
        $this->deductibleflag = $deductibleflag;

        return $this;
    }

    public function getU_typeoperation()
    {
        return $this->u_typeoperation;
    }

    public function setU_typeoperation($u_typeoperation)
    {
        $this->u_typeoperation = $u_typeoperation;

        return $this;
    }

    public function getTypeoperationdiot()
    {
        return $this->typeoperationdiot;
    }

    public function setTypeoperationdiot($typeoperationdiot)
    {
        $this->typeoperationdiot = $typeoperationdiot;

        return $this;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    public function getLarge()
    {
        return $this->large;
    }

    public function setLarge($large)
    {
        $this->large = $large;

        return $this;
    }

    public function getFichatecnica()
    {
        return $this->fichatecnica;
    }

    public function setFichatecnica($fichatecnica)
    {
        $this->fichatecnica = $fichatecnica;

        return $this;
    }

    public function getPercentfactorigi()
    {
        return $this->percentfactorigi;
    }

    public function setPercentfactorigi($percentfactorigi)
    {
        $this->percentfactorigi = $percentfactorigi;

        return $this;
    }

    public function getInpdfgroup()
    {
        return $this->inpdfgroup;
    }

    public function setInpdfgroup($inpdfgroup)
    {
        $this->inpdfgroup = $inpdfgroup;

        return $this;
    }

    public function getFlagadvance()
    {
        return $this->flagadvance;
    }

    public function setFlagadvance($flagadvance)
    {
        $this->flagadvance = $flagadvance;

        return $this;
    }

    public function getEq_conversion_factor()
    {
        return $this->eq_conversion_factor;
    }

    public function setEq_conversion_factor($eq_conversion_factor)
    {
        $this->eq_conversion_factor = $eq_conversion_factor;

        return $this;
    }

    public function getEq_stockid()
    {
        return $this->eq_stockid;
    }

    public function setEq_stockid($eq_stockid)
    {
        $this->eq_stockid = $eq_stockid;

        return $this;
    }

    public function getFlagcommission()
    {
        return $this->flagcommission;
    }

    public function setFlagcommission($flagcommission)
    {
        $this->flagcommission = $flagcommission;

        return $this;
    }

    public function getFijo()
    {
        return $this->fijo;
    }

    public function setFijo($fijo)
    {
        $this->fijo = $fijo;

        return $this;
    }

    public function getFecha_modificacion()
    {
        return $this->fecha_modificacion;
    }

    public function setFecha_modificacion($fecha_modificacion)
    {
        $this->fecha_modificacion = $fecha_modificacion;

        return $this;
    }

    public function getUnitequivalent()
    {
        return $this->unitequivalent;
    }

    public function setUnitequivalent($unitequivalent)
    {
        $this->unitequivalent = $unitequivalent;

        return $this;
    }

    public function getFactorconversionpaq()
    {
        return $this->factorconversionpaq;
    }

    public function setFactorconversionpaq($factorconversionpaq)
    {
        $this->factorconversionpaq = $factorconversionpaq;

        return $this;
    }

    public function getFactorconversionpz()
    {
        return $this->factorconversionpz;
    }

    public function setFactorconversionpz($factorconversionpz)
    {
        $this->factorconversionpz = $factorconversionpz;

        return $this;
    }

    public function getStockneodata()
    {
        return $this->stockneodata;
    }

    public function setStockneodata($stockneodata)
    {
        $this->stockneodata = $stockneodata;

        return $this;
    }

    public function getPurchgroup()
    {
        return $this->purchgroup;
    }

    public function setPurchgroup($purchgroup)
    {
        $this->purchgroup = $purchgroup;

        return $this;
    }

    public function getIdjerarquia()
    {
        return $this->idjerarquia;
    }

    public function setIdjerarquia($idjerarquia)
    {
        $this->idjerarquia = $idjerarquia;

        return $this;
    }

    public function getAddunits()
    {
        return $this->addunits;
    }

    public function setAddunits($addunits)
    {
        $this->addunits = $addunits;

        return $this;
    }

    public function getSecuunits()
    {
        return $this->secuunits;
    }

    public function setSecuunits($secuunits)
    {
        $this->secuunits = $secuunits;

        return $this;
    }

    public function getRecipeunits()
    {
        return $this->recipeunits;
    }

    public function setRecipeunits($recipeunits)
    {
        $this->recipeunits = $recipeunits;

        return $this;
    }

    public function getFactorrecipe()
    {
        return $this->factorrecipe;
    }

    public function setFactorrecipe($factorrecipe)
    {
        $this->factorrecipe = $factorrecipe;

        return $this;
    }

    public function getAddcategory()
    {
        return $this->addcategory;
    }

    public function setAddcategory($addcategory)
    {
        $this->addcategory = $addcategory;

        return $this;
    }

    public function getDeliverydays()
    {
        return $this->deliverydays;
    }

    public function setDeliverydays($deliverydays)
    {
        $this->deliverydays = $deliverydays;

        return $this;
    }

    public function getTolerancedays()
    {
        return $this->tolerancedays;
    }

    public function setTolerancedays($tolerancedays)
    {
        $this->tolerancedays = $tolerancedays;

        return $this;
    }

    public function getEstatusstock()
    {
        return $this->estatusstock;
    }

    public function setEstatusstock($estatusstock)
    {
        $this->estatusstock = $estatusstock;

        return $this;
    }

    public function getEq_conversion_costo()
    {
        return $this->eq_conversion_costo;
    }

    public function setEq_conversion_costo($eq_conversion_costo)
    {
        $this->eq_conversion_costo = $eq_conversion_costo;

        return $this;
    }

    public function getExtracolone()
    {
        return $this->extracolone;
    }

    public function setExtracolone($extracolone)
    {
        $this->extracolone = $extracolone;

        return $this;
    }

    public function getExtracoltwo()
    {
        return $this->extracoltwo;
    }

    public function setExtracoltwo($extracoltwo)
    {
        $this->extracoltwo = $extracoltwo;

        return $this;
    }

    public function getExtracolthree()
    {
        return $this->extracolthree;
    }

    public function setExtracolthree($extracolthree)
    {
        $this->extracolthree = $extracolthree;

        return $this;
    }

    public function getUnitstemporal()
    {
        return $this->unitstemporal;
    }

    public function setUnitstemporal($unitstemporal)
    {
        $this->unitstemporal = $unitstemporal;

        return $this;
    }

    public function getSAPActualiza()
    {
        return $this->SAPActualiza;
    }

    public function setSAPActualiza($SAPActualiza)
    {
        $this->SAPActualiza = $SAPActualiza;

        return $this;
    }

    public function getDepreciacion()
    {
        return $this->depreciacion;
    }

    public function setDepreciacion($depreciacion)
    {
        $this->depreciacion = $depreciacion;

        return $this;
    }

    public function getValor_inicial()
    {
        return $this->valor_inicial;
    }

    public function setValor_inicial($valor_inicial)
    {
        $this->valor_inicial = $valor_inicial;

        return $this;
    }
}

?>