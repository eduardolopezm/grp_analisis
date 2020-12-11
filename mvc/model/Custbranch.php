<?php
  
class Custbranch {

	private $branchcode;
	private $debtorno;
	private $brname;
	private $taxid;
	private $braddress1;
	private $braddress2;
	private $braddress3;
	private $braddress4;
	private $braddress5;
	private $braddress6;
	private $lat;
	private $lng;
	private $estdeliverydays;
	private $area;
	private $salesman;
	private $fwddate;
	private $phoneno;
	private $faxno;
	private $contactname;
	private $email;
	private $lineofbusiness;
	private $flagworkshop;
	private $defaultlocation;
	private $taxgroupid;
	private $defaultshipvia;
	private $deliverblind;
	private $disabletrans;
	private $brpostaddr1;
	private $brpostaddr2;
	private $brpostaddr3;
	private $brpostaddr4;
	private $brpostaddr5;
	private $brpostaddr6;
	private $specialinstructions;
	private $custbranchcode;
	private $creditlimit;
	private $custdata1;
	private $custdata2;
	private $custdata3;
	private $custdata4;
	private $custdata5;
	private $custdata6;
	private $ruta;
	private $brnumint;
	private $brnumext;
	private $paymentname;
	private $nocuenta;
	private $NumeAsigCliente;
	private $descclientecomercial;
	private $descclientepropago;
	private $descclienteop;
	private $typeaddenda;
	private $movilno;
	private $nextelno;
	private $welcomemail;
	private $SectComClId;
	private $custpais;
	private $braddress7;
	private $DiasRevicion;
	private $DiasPago;
	private $fecha_modificacion;
	private $namebank;
	private $logocliente;
	private $idprospecmedcontacto;
	private $idproyecto;
	private $prefer;
	private $discountcard;
	private $typecomplement;

    

    public function getBranchcode()
    {
        return $this->branchcode;
    }

    public function setBranchcode($branchcode)
    {
        $this->branchcode = $branchcode;

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

    public function getBrname()
    {
        return $this->brname;
    }

    public function setBrname($brname)
    {
        $this->brname = $brname;

        return $this;
    }

    public function getTaxid()
    {
        return $this->taxid;
    }

    public function setTaxid($taxid)
    {
        $this->taxid = $taxid;

        return $this;
    }

    public function getBraddress1()
    {
        return $this->braddress1;
    }

    public function setBraddress1($braddress1)
    {
        $this->braddress1 = $braddress1;

        return $this;
    }

    public function getBraddress2()
    {
        return $this->braddress2;
    }

    public function setBraddress2($braddress2)
    {
        $this->braddress2 = $braddress2;

        return $this;
    }

    public function getBraddress3()
    {
        return $this->braddress3;
    }

    public function setBraddress3($braddress3)
    {
        $this->braddress3 = $braddress3;

        return $this;
    }

    public function getBraddress4()
    {
        return $this->braddress4;
    }

    public function setBraddress4($braddress4)
    {
        $this->braddress4 = $braddress4;

        return $this;
    }

    public function getBraddress5()
    {
        return $this->braddress5;
    }

    public function setBraddress5($braddress5)
    {
        $this->braddress5 = $braddress5;

        return $this;
    }

    public function getBraddress6()
    {
        return $this->braddress6;
    }

    public function setBraddress6($braddress6)
    {
        $this->braddress6 = $braddress6;

        return $this;
    }

    public function getLat()
    {
        return $this->lat;
    }

    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLng()
    {
        return $this->lng;
    }

    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    public function getEstdeliverydays()
    {
        return $this->estdeliverydays;
    }

    public function setEstdeliverydays($estdeliverydays)
    {
        $this->estdeliverydays = $estdeliverydays;

        return $this;
    }

    public function getArea()
    {
        return $this->area;
    }

    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    public function getSalesman()
    {
        return $this->salesman;
    }

    public function setSalesman($salesman)
    {
        $this->salesman = $salesman;

        return $this;
    }

    public function getFwddate()
    {
        return $this->fwddate;
    }

    public function setFwddate($fwddate)
    {
        $this->fwddate = $fwddate;

        return $this;
    }

    public function getPhoneno()
    {
        return $this->phoneno;
    }

    public function setPhoneno($phoneno)
    {
        $this->phoneno = $phoneno;

        return $this;
    }

    public function getFaxno()
    {
        return $this->faxno;
    }

    public function setFaxno($faxno)
    {
        $this->faxno = $faxno;

        return $this;
    }

    public function getContactname()
    {
        return $this->contactname;
    }

    public function setContactname($contactname)
    {
        $this->contactname = $contactname;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function getLineofbusiness()
    {
        return $this->lineofbusiness;
    }

    public function setLineofbusiness($lineofbusiness)
    {
        $this->lineofbusiness = $lineofbusiness;

        return $this;
    }

    public function getFlagworkshop()
    {
        return $this->flagworkshop;
    }

    public function setFlagworkshop($flagworkshop)
    {
        $this->flagworkshop = $flagworkshop;

        return $this;
    }

    public function getDefaultlocation()
    {
        return $this->defaultlocation;
    }

    public function setDefaultlocation($defaultlocation)
    {
        $this->defaultlocation = $defaultlocation;

        return $this;
    }

    public function getTaxgroupid()
    {
        return $this->taxgroupid;
    }

    public function setTaxgroupid($taxgroupid)
    {
        $this->taxgroupid = $taxgroupid;

        return $this;
    }

    public function getDefaultshipvia()
    {
        return $this->defaultshipvia;
    }

    public function setDefaultshipvia($defaultshipvia)
    {
        $this->defaultshipvia = $defaultshipvia;

        return $this;
    }

    public function getDeliverblind()
    {
        return $this->deliverblind;
    }

    public function setDeliverblind($deliverblind)
    {
        $this->deliverblind = $deliverblind;

        return $this;
    }

    public function getDisabletrans()
    {
        return $this->disabletrans;
    }

    public function setDisabletrans($disabletrans)
    {
        $this->disabletrans = $disabletrans;

        return $this;
    }

    public function getBrpostaddr1()
    {
        return $this->brpostaddr1;
    }

    public function setBrpostaddr1($brpostaddr1)
    {
        $this->brpostaddr1 = $brpostaddr1;

        return $this;
    }

    public function getBrpostaddr2()
    {
        return $this->brpostaddr2;
    }

    public function setBrpostaddr2($brpostaddr2)
    {
        $this->brpostaddr2 = $brpostaddr2;

        return $this;
    }

    public function getBrpostaddr3()
    {
        return $this->brpostaddr3;
    }

    public function setBrpostaddr3($brpostaddr3)
    {
        $this->brpostaddr3 = $brpostaddr3;

        return $this;
    }

    public function getBrpostaddr4()
    {
        return $this->brpostaddr4;
    }

    public function setBrpostaddr4($brpostaddr4)
    {
        $this->brpostaddr4 = $brpostaddr4;

        return $this;
    }

    public function getBrpostaddr5()
    {
        return $this->brpostaddr5;
    }

    public function setBrpostaddr5($brpostaddr5)
    {
        $this->brpostaddr5 = $brpostaddr5;

        return $this;
    }

    public function getBrpostaddr6()
    {
        return $this->brpostaddr6;
    }

    public function setBrpostaddr6($brpostaddr6)
    {
        $this->brpostaddr6 = $brpostaddr6;

        return $this;
    }

    public function getSpecialinstructions()
    {
        return $this->specialinstructions;
    }

    public function setSpecialinstructions($specialinstructions)
    {
        $this->specialinstructions = $specialinstructions;

        return $this;
    }

    public function getCustbranchcode()
    {
        return $this->custbranchcode;
    }

    public function setCustbranchcode($custbranchcode)
    {
        $this->custbranchcode = $custbranchcode;

        return $this;
    }

    public function getCreditlimit()
    {
        return $this->creditlimit;
    }

    public function setCreditlimit($creditlimit)
    {
        $this->creditlimit = $creditlimit;

        return $this;
    }

    public function getCustdata1()
    {
        return $this->custdata1;
    }

    public function setCustdata1($custdata1)
    {
        $this->custdata1 = $custdata1;

        return $this;
    }

    public function getCustdata2()
    {
        return $this->custdata2;
    }

    public function setCustdata2($custdata2)
    {
        $this->custdata2 = $custdata2;

        return $this;
    }

    public function getCustdata3()
    {
        return $this->custdata3;
    }

    public function setCustdata3($custdata3)
    {
        $this->custdata3 = $custdata3;

        return $this;
    }

    public function getCustdata4()
    {
        return $this->custdata4;
    }

    public function setCustdata4($custdata4)
    {
        $this->custdata4 = $custdata4;

        return $this;
    }

    public function getCustdata5()
    {
        return $this->custdata5;
    }

    public function setCustdata5($custdata5)
    {
        $this->custdata5 = $custdata5;

        return $this;
    }

    public function getCustdata6()
    {
        return $this->custdata6;
    }

    public function setCustdata6($custdata6)
    {
        $this->custdata6 = $custdata6;

        return $this;
    }

    public function getRuta()
    {
        return $this->ruta;
    }

    public function setRuta($ruta)
    {
        $this->ruta = $ruta;

        return $this;
    }

    public function getBrnumint()
    {
        return $this->brnumint;
    }

    public function setBrnumint($brnumint)
    {
        $this->brnumint = $brnumint;

        return $this;
    }

    public function getBrnumext()
    {
        return $this->brnumext;
    }

    public function setBrnumext($brnumext)
    {
        $this->brnumext = $brnumext;

        return $this;
    }

    public function getPaymentname()
    {
        return $this->paymentname;
    }

    public function setPaymentname($paymentname)
    {
        $this->paymentname = $paymentname;

        return $this;
    }

    public function getNocuenta()
    {
        return $this->nocuenta;
    }

    public function setNocuenta($nocuenta)
    {
        $this->nocuenta = $nocuenta;

        return $this;
    }

    public function getNumeAsigCliente()
    {
        return $this->NumeAsigCliente;
    }

    public function setNumeAsigCliente($NumeAsigCliente)
    {
        $this->NumeAsigCliente = $NumeAsigCliente;

        return $this;
    }

    public function getDescclientecomercial()
    {
        return $this->descclientecomercial;
    }

    public function setDescclientecomercial($descclientecomercial)
    {
        $this->descclientecomercial = $descclientecomercial;

        return $this;
    }

    public function getDescclientepropago()
    {
        return $this->descclientepropago;
    }

    public function setDescclientepropago($descclientepropago)
    {
        $this->descclientepropago = $descclientepropago;

        return $this;
    }

    public function getDescclienteop()
    {
        return $this->descclienteop;
    }

    public function setDescclienteop($descclienteop)
    {
        $this->descclienteop = $descclienteop;

        return $this;
    }

    public function getTypeaddenda()
    {
        return $this->typeaddenda;
    }

    public function setTypeaddenda($typeaddenda)
    {
        $this->typeaddenda = $typeaddenda;

        return $this;
    }

    public function getMovilno()
    {
        return $this->movilno;
    }

    public function setMovilno($movilno)
    {
        $this->movilno = $movilno;

        return $this;
    }

    public function getNextelno()
    {
        return $this->nextelno;
    }

    public function setNextelno($nextelno)
    {
        $this->nextelno = $nextelno;

        return $this;
    }

    public function getWelcomemail()
    {
        return $this->welcomemail;
    }

    public function setWelcomemail($welcomemail)
    {
        $this->welcomemail = $welcomemail;

        return $this;
    }

    public function getSectComClId()
    {
        return $this->SectComClId;
    }

    public function setSectComClId($SectComClId)
    {
        $this->SectComClId = $SectComClId;

        return $this;
    }

    public function getCustpais()
    {
        return $this->custpais;
    }

    public function setCustpais($custpais)
    {
        $this->custpais = $custpais;

        return $this;
    }

    public function getBraddress7()
    {
        return $this->braddress7;
    }

    public function setBraddress7($braddress7)
    {
        $this->braddress7 = $braddress7;

        return $this;
    }

    public function getDiasRevicion()
    {
        return $this->DiasRevicion;
    }

    public function setDiasRevicion($DiasRevicion)
    {
        $this->DiasRevicion = $DiasRevicion;

        return $this;
    }

    public function getDiasPago()
    {
        return $this->DiasPago;
    }

    public function setDiasPago($DiasPago)
    {
        $this->DiasPago = $DiasPago;

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

    public function getNamebank()
    {
        return $this->namebank;
    }

    public function setNamebank($namebank)
    {
        $this->namebank = $namebank;

        return $this;
    }

    public function getLogocliente()
    {
        return $this->logocliente;
    }

    public function setLogocliente($logocliente)
    {
        $this->logocliente = $logocliente;

        return $this;
    }

    public function getIdprospecmedcontacto()
    {
        return $this->idprospecmedcontacto;
    }

    public function setIdprospecmedcontacto($idprospecmedcontacto)
    {
        $this->idprospecmedcontacto = $idprospecmedcontacto;

        return $this;
    }

    public function getIdproyecto()
    {
        return $this->idproyecto;
    }

    public function setIdproyecto($idproyecto)
    {
        $this->idproyecto = $idproyecto;

        return $this;
    }

    public function getPrefer()
    {
        return $this->prefer;
    }

    public function setPrefer($prefer)
    {
        $this->prefer = $prefer;

        return $this;
    }

    public function getDiscountcard()
    {
        return $this->discountcard;
    }

    public function setDiscountcard($discountcard)
    {
        $this->discountcard = $discountcard;

        return $this;
    }

    public function getTypecomplement()
    {
        return $this->typecomplement;
    }

    public function setTypecomplement($typecomplement)
    {
        $this->typecomplement = $typecomplement;

        return $this;
    }
}

?>