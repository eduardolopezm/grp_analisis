<?php

   
class Salesorder{
	
	private $orderno;
    private $debtorno;
    private $branchcode;
    private $customerref;
    private $buyername;
    private $comments;
    private $pagador;
    private $orddate;
    private $ordertype;
    private $shipvia;
    private $deladd1;
    private $deladd2;
    private $deladd3;
    private $deladd4;
    private $deladd5;
    private $deladd6;
    private $contactphone;
    private $contactemail;
    private $deliverto;
    private $deliverblind;
    private $freightcost;
    private $fromstkloc;
    private $deliverydate;
    private $quotedate;
    private $confirmeddate;
    private $printedpackingslip;
    private $datepackingslipprinted;
    private $quotation;
    private $placa;
    private $serie;
    private $kilometraje;
    private $salesman;
    private $tagref;
    private $taxtotal;
    private $totaltaxret;
    private $currcode;
    private $paytermsindicator;
    private $advance;
    private $UserRegister;
    private $refundpercentsale;
    private $vehicleno;
    private $idtarea;
    private $nopedido;
    private $noentrada;
    private $extratext;
    private $noremision;
    private $contract_type;
    private $typeorder;
    private $codigobarras;
    private $contid;
    private $idprospect;
    private $puestaenmarcha;
    private $paymentname;
    private $nocuenta;
    private $deliverytext;
    private $totalrefundpercentsale;
    private $estatusprocesing;
    private $serviceorder;
    private $usetype;
    private $statuscancel;
    private $fromcr;
    private $ordenprioridad;
    private $discountcard;
    private $payreference;
    private $ln_ue;
    private $ln_tagref_pase;
    private $ln_ue_pase;

    public function getOrderno()
    {
        return $this->orderno;
    }

    public function setOrderno($orderno)
    {
        $this->orderno = $orderno;

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

    public function getCustomerref()
    {
        return $this->customerref;
    }

    public function setCustomerref($customerref)
    {
        $this->customerref = $customerref;

        return $this;
    }

    public function getBuyername()
    {
        return $this->buyername;
    }

    public function setBuyername($buyername)
    {
        $this->buyername = $buyername;

        return $this;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    public function getPagador()
    {
        return $this->pagador;
    }

    public function setPagador($pagador)
    {
        $this->pagador = $pagador;

        return $this;
    }

    public function getOrddate()
    {
        return $this->orddate;
    }

    public function setOrddate($orddate)
    {
        $this->orddate = $orddate;

        return $this;
    }

    public function getOrdertype()
    {
        return $this->ordertype;
    }

    public function setOrdertype($ordertype)
    {
        $this->ordertype = $ordertype;

        return $this;
    }

    public function getShipvia()
    {
        return $this->shipvia;
    }

    public function setShipvia($shipvia)
    {
        $this->shipvia = $shipvia;

        return $this;
    }

    public function getDeladd1()
    {
        return $this->deladd1;
    }

    public function setDeladd1($deladd1)
    {
        $this->deladd1 = $deladd1;

        return $this;
    }

    public function getDeladd2()
    {
        return $this->deladd2;
    }

    public function setDeladd2($deladd2)
    {
        $this->deladd2 = $deladd2;

        return $this;
    }

    public function getDeladd3()
    {
        return $this->deladd3;
    }

    public function setDeladd3($deladd3)
    {
        $this->deladd3 = $deladd3;

        return $this;
    }

    public function getDeladd4()
    {
        return $this->deladd4;
    }

    public function setDeladd4($deladd4)
    {
        $this->deladd4 = $deladd4;

        return $this;
    }

    public function getDeladd5()
    {
        return $this->deladd5;
    }

    public function setDeladd5($deladd5)
    {
        $this->deladd5 = $deladd5;

        return $this;
    }

    public function getDeladd6()
    {
        return $this->deladd6;
    }

    public function setDeladd6($deladd6)
    {
        $this->deladd6 = $deladd6;

        return $this;
    }

    public function getContactphone()
    {
        return $this->contactphone;
    }

    public function setContactphone($contactphone)
    {
        $this->contactphone = $contactphone;

        return $this;
    }

    public function getContactemail()
    {
        return $this->contactemail;
    }

    public function setContactemail($contactemail)
    {
        $this->contactemail = $contactemail;

        return $this;
    }

    public function getDeliverto()
    {
        return $this->deliverto;
    }

    public function setDeliverto($deliverto)
    {
        $this->deliverto = $deliverto;

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

    public function getFreightcost()
    {
        return $this->freightcost;
    }

    public function setFreightcost($freightcost)
    {
        $this->freightcost = $freightcost;

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

    public function getDeliverydate()
    {
        return $this->deliverydate;
    }

    public function setDeliverydate($deliverydate)
    {
        $this->deliverydate = $deliverydate;

        return $this;
    }

    public function getQuotedate()
    {
        return $this->quotedate;
    }

    public function setQuotedate($quotedate)
    {
        $this->quotedate = $quotedate;

        return $this;
    }

    public function getConfirmeddate()
    {
        return $this->confirmeddate;
    }

    public function setConfirmeddate($confirmeddate)
    {
        $this->confirmeddate = $confirmeddate;

        return $this;
    }

    public function getPrintedpackingslip()
    {
        return $this->printedpackingslip;
    }

    public function setPrintedpackingslip($printedpackingslip)
    {
        $this->printedpackingslip = $printedpackingslip;

        return $this;
    }

    public function getDatepackingslipprinted()
    {
        return $this->datepackingslipprinted;
    }

    public function setDatepackingslipprinted($datepackingslipprinted)
    {
        $this->datepackingslipprinted = $datepackingslipprinted;

        return $this;
    }

    public function getQuotation()
    {
        return $this->quotation;
    }

    public function setQuotation($quotation)
    {
        $this->quotation = $quotation;

        return $this;
    }

    public function getPlaca()
    {
        return $this->placa;
    }

    public function setPlaca($placa)
    {
        $this->placa = $placa;

        return $this;
    }

    public function getSerie()
    {
        return $this->serie;
    }

    public function setSerie($serie)
    {
        $this->serie = $serie;

        return $this;
    }

    public function getKilometraje()
    {
        return $this->kilometraje;
    }

    public function setKilometraje($kilometraje)
    {
        $this->kilometraje = $kilometraje;

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

    public function getTagref()
    {
        return $this->tagref;
    }

    public function setTagref($tagref)
    {
        $this->tagref = $tagref;

        return $this;
    }

    public function getTaxtotal()
    {
        return $this->taxtotal;
    }

    public function setTaxtotal($taxtotal)
    {
        $this->taxtotal = $taxtotal;

        return $this;
    }

    public function getTotaltaxret()
    {
        return $this->totaltaxret;
    }

    public function setTotaltaxret($totaltaxret)
    {
        $this->totaltaxret = $totaltaxret;

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

    public function getPaytermsindicator()
    {
        return $this->paytermsindicator;
    }

    public function setPaytermsindicator($paytermsindicator)
    {
        $this->paytermsindicator = $paytermsindicator;

        return $this;
    }

    public function getAdvance()
    {
        return $this->advance;
    }

    public function setAdvance($advance)
    {
        $this->advance = $advance;

        return $this;
    }

    public function getUserRegister()
    {
        return $this->UserRegister;
    }

    public function setUserRegister($UserRegister)
    {
        $this->UserRegister = $UserRegister;

        return $this;
    }

    public function getRefundpercentsale()
    {
        return $this->refundpercentsale;
    }

    public function setRefundpercentsale($refundpercentsale)
    {
        $this->refundpercentsale = $refundpercentsale;

        return $this;
    }

    public function getVehicleno()
    {
        return $this->vehicleno;
    }

    public function setVehicleno($vehicleno)
    {
        $this->vehicleno = $vehicleno;

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

    public function getNopedido()
    {
        return $this->nopedido;
    }

    public function setNopedido($nopedido)
    {
        $this->nopedido = $nopedido;

        return $this;
    }

    public function getNoentrada()
    {
        return $this->noentrada;
    }

    public function setNoentrada($noentrada)
    {
        $this->noentrada = $noentrada;

        return $this;
    }

    public function getExtratext()
    {
        return $this->extratext;
    }

    public function setExtratext($extratext)
    {
        $this->extratext = $extratext;

        return $this;
    }

    public function getNoremision()
    {
        return $this->noremision;
    }

    public function setNoremision($noremision)
    {
        $this->noremision = $noremision;

        return $this;
    }

    public function getContract_type()
    {
        return $this->contract_type;
    }

    public function setContract_type($contract_type)
    {
        $this->contract_type = $contract_type;

        return $this;
    }

    public function getTypeorder()
    {
        return $this->typeorder;
    }

    public function setTypeorder($typeorder)
    {
        $this->typeorder = $typeorder;

        return $this;
    }

    public function getCodigobarras()
    {
        return $this->codigobarras;
    }

    public function setCodigobarras($codigobarras)
    {
        $this->codigobarras = $codigobarras;

        return $this;
    }

    public function getContid()
    {
        return $this->contid;
    }

    public function setContid($contid)
    {
        $this->contid = $contid;

        return $this;
    }

    public function getIdprospect()
    {
        return $this->idprospect;
    }

    public function setIdprospect($idprospect)
    {
        $this->idprospect = $idprospect;

        return $this;
    }

    public function getPuestaenmarcha()
    {
        return $this->puestaenmarcha;
    }

    public function setPuestaenmarcha($puestaenmarcha)
    {
        $this->puestaenmarcha = $puestaenmarcha;

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

    public function getDeliverytext()
    {
        return $this->deliverytext;
    }

    public function setDeliverytext($deliverytext)
    {
        $this->deliverytext = $deliverytext;

        return $this;
    }

    public function getTotalrefundpercentsale()
    {
        return $this->totalrefundpercentsale;
    }

    public function setTotalrefundpercentsale($totalrefundpercentsale)
    {
        $this->totalrefundpercentsale = $totalrefundpercentsale;

        return $this;
    }

    public function getEstatusprocesing()
    {
        return $this->estatusprocesing;
    }

    public function setEstatusprocesing($estatusprocesing)
    {
        $this->estatusprocesing = $estatusprocesing;

        return $this;
    }

    public function getServiceorder()
    {
        return $this->serviceorder;
    }

    public function setServiceorder($serviceorder)
    {
        $this->serviceorder = $serviceorder;

        return $this;
    }

    public function getUsetype()
    {
        return $this->usetype;
    }

    public function setUsetype($usetype)
    {
        $this->usetype = $usetype;

        return $this;
    }

    public function getStatuscancel()
    {
        return $this->statuscancel;
    }

    public function setStatuscancel($statuscancel)
    {
        $this->statuscancel = $statuscancel;

        return $this;
    }

    public function getFromcr()
    {
        return $this->fromcr;
    }

    public function setFromcr($fromcr)
    {
        $this->fromcr = $fromcr;

        return $this;
    }

    public function getOrdenprioridad()
    {
        return $this->ordenprioridad;
    }

    public function setOrdenprioridad($ordenprioridad)
    {
        $this->ordenprioridad = $ordenprioridad;

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

    public function getPayreference()
    {
        return $this->payreference;
    }

    public function setPayreference($payreference)
    {
        $this->payreference = $payreference;

        return $this;
    }
    
    public function getLn_ue()
    {
        return $this->ln_ue;
    }

    public function setLn_ue($ln_ue)
    {
        $this->ln_ue = $ln_ue;

        return $this;
    }

    public function getLn_tagref_pase()
    {
        return $this->ln_tagref_pase;
    }

    public function setLn_tagref_pase($ln_tagref_pase)
    {
        $this->ln_tagref_pase = $ln_tagref_pase;

        return $this;
    }

    public function getLn_ue_pase()
    {
        return $this->ln_ue_pase;
    }

    public function setLn_ue_pase($ln_ue_pase)
    {
        $this->ln_ue_pase = $ln_ue_pase;

        return $this;
    }
}

?>