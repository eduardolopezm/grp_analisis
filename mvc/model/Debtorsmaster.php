<?php
  
class Debtorsmaster {
	private $debtorno;
    private $name;
    private $name1;
    private $name2;
    private $name3;
    private $curp;
    private $address1;
    private $address2;
    private $address3;
    private $address4;
    private $address5;
    private $address6;
    private $currcode;
    private $salestype;
    private $clientsince;
    private $holdreason;
    private $paymentterms;
    private $discount;
    private $pymtdiscount;
    private $lastpaid;
    private $lastpaiddate;
    private $creditlimit;
    private $invaddrbranch;
    private $discountcode;
    private $ediinvoices;
    private $ediorders;
    private $edireference;
    private $editransport;
    private $ediaddress;
    private $ediserveruser;
    private $ediserverpwd;
    private $taxref;
    private $customerpoline;
    private $typeid;
    private $daygrace;
    private $coments;
    private $blacklist;
    private $ruta;
    private $nameextra;
    private $razoncompra;
    private $mediocontacto;
    private $fechanacimiento;
    private $pagpersonal;
    private $lugarnacimiento;
    private $telefonocelular;
    private $ingresosmensuales;
    private $estadocivil;
    private $NacionalidadId;
    private $CapacidadCompraId;
    private $idCapComIngresos;
    private $companyprospect;
    private $prospectsince;
    private $userprospect;


    

    public function getDebtorno()
    {
        return $this->debtorno;
    }

    public function setDebtorno($debtorno)
    {
        $this->debtorno = $debtorno;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName1()
    {
        return $this->name1;
    }

    public function setName1($name1)
    {
        $this->name1 = $name1;

        return $this;
    }

    public function getName2()
    {
        return $this->name2;
    }

    public function setName2($name2)
    {
        $this->name2 = $name2;

        return $this;
    }

    public function getName3()
    {
        return $this->name3;
    }

    public function setName3($name3)
    {
        $this->name3 = $name3;

        return $this;
    }

    public function getCurp()
    {
        return $this->curp;
    }

    public function setCurp($curp)
    {
        $this->curp = $curp;

        return $this;
    }

    public function getAddress1()
    {
        return $this->address1;
    }

    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    public function getAddress2()
    {
        return $this->address2;
    }

    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getAddress3()
    {
        return $this->address3;
    }

    public function setAddress3($address3)
    {
        $this->address3 = $address3;

        return $this;
    }

    public function getAddress4()
    {
        return $this->address4;
    }

    public function setAddress4($address4)
    {
        $this->address4 = $address4;

        return $this;
    }

    public function getAddress5()
    {
        return $this->address5;
    }

    public function setAddress5($address5)
    {
        $this->address5 = $address5;

        return $this;
    }

    public function getAddress6()
    {
        return $this->address6;
    }

    public function setAddress6($address6)
    {
        $this->address6 = $address6;

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

    public function getSalestype()
    {
        return $this->salestype;
    }

    public function setSalestype($salestype)
    {
        $this->salestype = $salestype;

        return $this;
    }

    public function getClientsince()
    {
        return $this->clientsince;
    }

    public function setClientsince($clientsince)
    {
        $this->clientsince = $clientsince;

        return $this;
    }

    public function getHoldreason()
    {
        return $this->holdreason;
    }

    public function setHoldreason($holdreason)
    {
        $this->holdreason = $holdreason;

        return $this;
    }

    public function getPaymentterms()
    {
        return $this->paymentterms;
    }

    public function setPaymentterms($paymentterms)
    {
        $this->paymentterms = $paymentterms;

        return $this;
    }

    public function getDiscount()
    {
        return $this->discount;
    }

    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    public function getPymtdiscount()
    {
        return $this->pymtdiscount;
    }

    public function setPymtdiscount($pymtdiscount)
    {
        $this->pymtdiscount = $pymtdiscount;

        return $this;
    }

    public function getLastpaid()
    {
        return $this->lastpaid;
    }

    public function setLastpaid($lastpaid)
    {
        $this->lastpaid = $lastpaid;

        return $this;
    }

    public function getLastpaiddate()
    {
        return $this->lastpaiddate;
    }

    public function setLastpaiddate($lastpaiddate)
    {
        $this->lastpaiddate = $lastpaiddate;

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

    public function getInvaddrbranch()
    {
        return $this->invaddrbranch;
    }

    public function setInvaddrbranch($invaddrbranch)
    {
        $this->invaddrbranch = $invaddrbranch;

        return $this;
    }

    public function getDiscountcode()
    {
        return $this->discountcode;
    }

    public function setDiscountcode($discountcode)
    {
        $this->discountcode = $discountcode;

        return $this;
    }

    public function getEdiinvoices()
    {
        return $this->ediinvoices;
    }

    public function setEdiinvoices($ediinvoices)
    {
        $this->ediinvoices = $ediinvoices;

        return $this;
    }

    public function getEdiorders()
    {
        return $this->ediorders;
    }

    public function setEdiorders($ediorders)
    {
        $this->ediorders = $ediorders;

        return $this;
    }

    public function getEdireference()
    {
        return $this->edireference;
    }

    public function setEdireference($edireference)
    {
        $this->edireference = $edireference;

        return $this;
    }

    public function getEditransport()
    {
        return $this->editransport;
    }

    public function setEditransport($editransport)
    {
        $this->editransport = $editransport;

        return $this;
    }

    public function getEdiaddress()
    {
        return $this->ediaddress;
    }

    public function setEdiaddress($ediaddress)
    {
        $this->ediaddress = $ediaddress;

        return $this;
    }

    public function getEdiserveruser()
    {
        return $this->ediserveruser;
    }

    public function setEdiserveruser($ediserveruser)
    {
        $this->ediserveruser = $ediserveruser;

        return $this;
    }

    public function getEdiserverpwd()
    {
        return $this->ediserverpwd;
    }

    public function setEdiserverpwd($ediserverpwd)
    {
        $this->ediserverpwd = $ediserverpwd;

        return $this;
    }

    public function getTaxref()
    {
        return $this->taxref;
    }

    public function setTaxref($taxref)
    {
        $this->taxref = $taxref;

        return $this;
    }

    public function getCustomerpoline()
    {
        return $this->customerpoline;
    }

    public function setCustomerpoline($customerpoline)
    {
        $this->customerpoline = $customerpoline;

        return $this;
    }

    public function getTypeid()
    {
        return $this->typeid;
    }

    public function setTypeid($typeid)
    {
        $this->typeid = $typeid;

        return $this;
    }

    public function getDaygrace()
    {
        return $this->daygrace;
    }

    public function setDaygrace($daygrace)
    {
        $this->daygrace = $daygrace;

        return $this;
    }

    public function getComents()
    {
        return $this->coments;
    }

    public function setComents($coments)
    {
        $this->coments = $coments;

        return $this;
    }

    public function getBlacklist()
    {
        return $this->blacklist;
    }

    public function setBlacklist($blacklist)
    {
        $this->blacklist = $blacklist;

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

    public function getNameextra()
    {
        return $this->nameextra;
    }

    public function setNameextra($nameextra)
    {
        $this->nameextra = $nameextra;

        return $this;
    }

    public function getRazoncompra()
    {
        return $this->razoncompra;
    }

    public function setRazoncompra($razoncompra)
    {
        $this->razoncompra = $razoncompra;

        return $this;
    }

    public function getMediocontacto()
    {
        return $this->mediocontacto;
    }

    public function setMediocontacto($mediocontacto)
    {
        $this->mediocontacto = $mediocontacto;

        return $this;
    }

    public function getFechanacimiento()
    {
        return $this->fechanacimiento;
    }

    public function setFechanacimiento($fechanacimiento)
    {
        $this->fechanacimiento = $fechanacimiento;

        return $this;
    }

    public function getPagpersonal()
    {
        return $this->pagpersonal;
    }

    public function setPagpersonal($pagpersonal)
    {
        $this->pagpersonal = $pagpersonal;

        return $this;
    }

    public function getLugarnacimiento()
    {
        return $this->lugarnacimiento;
    }

    public function setLugarnacimiento($lugarnacimiento)
    {
        $this->lugarnacimiento = $lugarnacimiento;

        return $this;
    }

    public function getTelefonocelular()
    {
        return $this->telefonocelular;
    }

    public function setTelefonocelular($telefonocelular)
    {
        $this->telefonocelular = $telefonocelular;

        return $this;
    }

    public function getIngresosmensuales()
    {
        return $this->ingresosmensuales;
    }

    public function setIngresosmensuales($ingresosmensuales)
    {
        $this->ingresosmensuales = $ingresosmensuales;

        return $this;
    }

    public function getEstadocivil()
    {
        return $this->estadocivil;
    }

    public function setEstadocivil($estadocivil)
    {
        $this->estadocivil = $estadocivil;

        return $this;
    }

    public function getNacionalidadId()
    {
        return $this->NacionalidadId;
    }

    public function setNacionalidadId($NacionalidadId)
    {
        $this->NacionalidadId = $NacionalidadId;

        return $this;
    }

    public function getCapacidadCompraId()
    {
        return $this->CapacidadCompraId;
    }

    public function setCapacidadCompraId($CapacidadCompraId)
    {
        $this->CapacidadCompraId = $CapacidadCompraId;

        return $this;
    }

    public function getIdCapComIngresos()
    {
        return $this->idCapComIngresos;
    }

    public function setIdCapComIngresos($idCapComIngresos)
    {
        $this->idCapComIngresos = $idCapComIngresos;

        return $this;
    }

    public function getCompanyprospect()
    {
        return $this->companyprospect;
    }

    public function setCompanyprospect($companyprospect)
    {
        $this->companyprospect = $companyprospect;

        return $this;
    }

    public function getProspectsince()
    {
        return $this->prospectsince;
    }

    public function setProspectsince($prospectsince)
    {
        $this->prospectsince = $prospectsince;

        return $this;
    }

    public function getUserprospect()
    {
        return $this->userprospect;
    }

    public function setUserprospect($userprospect)
    {
        $this->userprospect = $userprospect;

        return $this;
    }
}