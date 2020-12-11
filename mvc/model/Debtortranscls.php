<?php

class Debtortranscls {
    private $id;
    private $tagref;
    private $transno;
    private $type;
    private $debtorno;
    private $branchcode;
    private $origtrandate;
    private $trandate;
    private $prd;
    private $settled;
    private $reference;
    private $tpe;
    private $order_;
    private $rate;
    private $ovamount;
    private $ovgst;
    private $taxret;
    private $ovfreight;
    private $ovdiscount;
    private $diffonexch;
    private $alloc;
    private $invtext;
    private $shipvia;
    private $edisent;
    private $consignment;
    private $folio;
    private $ref1;
    private $ref2;
    private $currcode;
    private $cobrador;
    private $interesxdevengar;
    private $taxinteresxdevengar;
    private $interesdevengado;
    private $taxinteresdevengado;
    private $lasttrandate;
    private $sent;
    private $ovamountcancel;
    private $ovgstcancel;
    private $printed;
    private $sello;
    private $cadena;
    private $emails;
    private $nocuenta;
    private $paymentname;
    private $showvehicle;
    private $showcomments;
    private $discountpercentpayment;
    private $userid;
    private $flagdiscount;
    private $duedate;
    private $discountpercent;
    private $canceldate;
    private $idremision;
    private $transactiondate;
    private $idinvoice;
    private $nopedidof;
    private $noentradaf;
    private $noremisionf;
    private $contid;
    private $observf;
    private $noproveedorf;
    private $salesmannc;
    private $status;
    private $bankcheque;
    private $chequeno;
    private $transactiondatems;
    private $noagente;
    private $idorigen;
    private $timbre;
    private $uuid;
    private $fechatimbrado;
    private $cadenatimbre;
    private $flagprovision;
    private $flagsendfiscal;
    private $priority;
    private $activo;
    private $salesmanclient;
    private $flagsinmovseriales;
    private $flagfiscal;
    private $orderfactint;
    private $paymentmethodcode;
    private $c_TipoDeComprobante;
    private $c_UsoCFDI;
    private $c_paymentid;
    private $claveFactura;
    
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

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

    public function getTransno()
    {
        return $this->transno;
    }

    public function setTransno($transno)
    {
        $this->transno = $transno;

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

    public function getOrigtrandate()
    {
        return $this->origtrandate;
    }

    public function setOrigtrandate($origtrandate)
    {
        $this->origtrandate = $origtrandate;

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

    public function getPrd()
    {
        return $this->prd;
    }

    public function setPrd($prd)
    {
        $this->prd = $prd;

        return $this;
    }

    public function getSettled()
    {
        return $this->settled;
    }

    public function setSettled($settled)
    {
        $this->settled = $settled;

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

    public function getTpe()
    {
        return $this->tpe;
    }

    public function setTpe($tpe)
    {
        $this->tpe = $tpe;

        return $this;
    }

    public function getOrder_()
    {
        return $this->order_;
    }

    public function setOrder_($order_)
    {
        $this->order_ = $order_;

        return $this;
    }

    public function getRate()
    {
        return $this->rate;
    }

    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    public function getOvamount()
    {
        return $this->ovamount;
    }

    public function setOvamount($ovamount)
    {
        $this->ovamount = $ovamount;

        return $this;
    }

    public function getOvgst()
    {
        return $this->ovgst;
    }

    public function setOvgst($ovgst)
    {
        $this->ovgst = $ovgst;

        return $this;
    }

    public function getTaxret()
    {
        return $this->taxret;
    }

    public function setTaxret($taxret)
    {
        $this->taxret = $taxret;

        return $this;
    }

    public function getOvfreight()
    {
        return $this->ovfreight;
    }

    public function setOvfreight($ovfreight)
    {
        $this->ovfreight = $ovfreight;

        return $this;
    }

    public function getOvdiscount()
    {
        return $this->ovdiscount;
    }

    public function setOvdiscount($ovdiscount)
    {
        $this->ovdiscount = $ovdiscount;

        return $this;
    }

    public function getDiffonexch()
    {
        return $this->diffonexch;
    }

    public function setDiffonexch($diffonexch)
    {
        $this->diffonexch = $diffonexch;

        return $this;
    }

    public function getAlloc()
    {
        return $this->alloc;
    }

    public function setAlloc($alloc)
    {
        $this->alloc = $alloc;

        return $this;
    }

    public function getInvtext()
    {
        return $this->invtext;
    }

    public function setInvtext($invtext)
    {
        $this->invtext = $invtext;

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

    public function getEdisent()
    {
        return $this->edisent;
    }

    public function setEdisent($edisent)
    {
        $this->edisent = $edisent;

        return $this;
    }

    public function getConsignment()
    {
        return $this->consignment;
    }

    public function setConsignment($consignment)
    {
        $this->consignment = $consignment;

        return $this;
    }

    public function getFolio()
    {
        return $this->folio;
    }

    public function setFolio($folio)
    {
        $this->folio = $folio;

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

    public function getCurrcode()
    {
        return $this->currcode;
    }

    public function setCurrcode($currcode)
    {
        $this->currcode = $currcode;

        return $this;
    }

    public function getCobrador()
    {
        return $this->cobrador;
    }

    public function setCobrador($cobrador)
    {
        $this->cobrador = $cobrador;

        return $this;
    }

    public function getInteresxdevengar()
    {
        return $this->interesxdevengar;
    }

    public function setInteresxdevengar($interesxdevengar)
    {
        $this->interesxdevengar = $interesxdevengar;

        return $this;
    }

    public function getTaxinteresxdevengar()
    {
        return $this->taxinteresxdevengar;
    }

    public function setTaxinteresxdevengar($taxinteresxdevengar)
    {
        $this->taxinteresxdevengar = $taxinteresxdevengar;

        return $this;
    }

    public function getInteresdevengado()
    {
        return $this->interesdevengado;
    }

    public function setInteresdevengado($interesdevengado)
    {
        $this->interesdevengado = $interesdevengado;

        return $this;
    }

    public function getTaxinteresdevengado()
    {
        return $this->taxinteresdevengado;
    }

    public function setTaxinteresdevengado($taxinteresdevengado)
    {
        $this->taxinteresdevengado = $taxinteresdevengado;

        return $this;
    }

    public function getLasttrandate()
    {
        return $this->lasttrandate;
    }

    public function setLasttrandate($lasttrandate)
    {
        $this->lasttrandate = $lasttrandate;

        return $this;
    }

    public function getSent()
    {
        return $this->sent;
    }

    public function setSent($sent)
    {
        $this->sent = $sent;

        return $this;
    }

    public function getOvamountcancel()
    {
        return $this->ovamountcancel;
    }

    public function setOvamountcancel($ovamountcancel)
    {
        $this->ovamountcancel = $ovamountcancel;

        return $this;
    }

    public function getOvgstcancel()
    {
        return $this->ovgstcancel;
    }

    public function setOvgstcancel($ovgstcancel)
    {
        $this->ovgstcancel = $ovgstcancel;

        return $this;
    }

    public function getPrinted()
    {
        return $this->printed;
    }

    public function setPrinted($printed)
    {
        $this->printed = $printed;

        return $this;
    }

    public function getSello()
    {
        return $this->sello;
    }

    public function setSello($sello)
    {
        $this->sello = $sello;

        return $this;
    }

    public function getCadena()
    {
        return $this->cadena;
    }

    public function setCadena($cadena)
    {
        $this->cadena = $cadena;

        return $this;
    }

    public function getEmails()
    {
        return $this->emails;
    }

    public function setEmails($emails)
    {
        $this->emails = $emails;

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

    public function getPaymentname()
    {
        return $this->paymentname;
    }

    public function setPaymentname($paymentname)
    {
        $this->paymentname = $paymentname;

        return $this;
    }

    public function getShowvehicle()
    {
        return $this->showvehicle;
    }

    public function setShowvehicle($showvehicle)
    {
        $this->showvehicle = $showvehicle;

        return $this;
    }

    public function getShowcomments()
    {
        return $this->showcomments;
    }

    public function setShowcomments($showcomments)
    {
        $this->showcomments = $showcomments;

        return $this;
    }

    public function getDiscountpercentpayment()
    {
        return $this->discountpercentpayment;
    }

    public function setDiscountpercentpayment($discountpercentpayment)
    {
        $this->discountpercentpayment = $discountpercentpayment;

        return $this;
    }

    public function getUserid()
    {
        return $this->userid;
    }

    public function setUserid($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    public function getFlagdiscount()
    {
        return $this->flagdiscount;
    }

    public function setFlagdiscount($flagdiscount)
    {
        $this->flagdiscount = $flagdiscount;

        return $this;
    }

    public function getDuedate()
    {
        return $this->duedate;
    }

    public function setDuedate($duedate)
    {
        $this->duedate = $duedate;

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

    public function getCanceldate()
    {
        return $this->canceldate;
    }

    public function setCanceldate($canceldate)
    {
        $this->canceldate = $canceldate;

        return $this;
    }

    public function getIdremision()
    {
        return $this->idremision;
    }

    public function setIdremision($idremision)
    {
        $this->idremision = $idremision;

        return $this;
    }

    public function getTransactiondate()
    {
        return $this->transactiondate;
    }

    public function setTransactiondate($transactiondate)
    {
        $this->transactiondate = $transactiondate;

        return $this;
    }

    public function getIdinvoice()
    {
        return $this->idinvoice;
    }

    public function setIdinvoice($idinvoice)
    {
        $this->idinvoice = $idinvoice;

        return $this;
    }

    public function getNopedidof()
    {
        return $this->nopedidof;
    }

    public function setNopedidof($nopedidof)
    {
        $this->nopedidof = $nopedidof;

        return $this;
    }

    public function getNoentradaf()
    {
        return $this->noentradaf;
    }

    public function setNoentradaf($noentradaf)
    {
        $this->noentradaf = $noentradaf;

        return $this;
    }

    public function getNoremisionf()
    {
        return $this->noremisionf;
    }

    public function setNoremisionf($noremisionf)
    {
        $this->noremisionf = $noremisionf;

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

    public function getObservf()
    {
        return $this->observf;
    }

    public function setObservf($observf)
    {
        $this->observf = $observf;

        return $this;
    }

    public function getNoproveedorf()
    {
        return $this->noproveedorf;
    }

    public function setNoproveedorf($noproveedorf)
    {
        $this->noproveedorf = $noproveedorf;

        return $this;
    }

    public function getSalesmannc()
    {
        return $this->salesmannc;
    }

    public function setSalesmannc($salesmannc)
    {
        $this->salesmannc = $salesmannc;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getBankcheque()
    {
        return $this->bankcheque;
    }

    public function setBankcheque($bankcheque)
    {
        $this->bankcheque = $bankcheque;

        return $this;
    }

    public function getChequeno()
    {
        return $this->chequeno;
    }

    public function setChequeno($chequeno)
    {
        $this->chequeno = $chequeno;

        return $this;
    }

    public function getTransactiondatems()
    {
        return $this->transactiondatems;
    }

    public function setTransactiondatems($transactiondatems)
    {
        $this->transactiondatems = $transactiondatems;

        return $this;
    }

    public function getNoagente()
    {
        return $this->noagente;
    }

    public function setNoagente($noagente)
    {
        $this->noagente = $noagente;

        return $this;
    }

    public function getIdorigen()
    {
        return $this->idorigen;
    }

    public function setIdorigen($idorigen)
    {
        $this->idorigen = $idorigen;

        return $this;
    }

    public function getTimbre()
    {
        return $this->timbre;
    }

    public function setTimbre($timbre)
    {
        $this->timbre = $timbre;

        return $this;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getFechatimbrado()
    {
        return $this->fechatimbrado;
    }

    public function setFechatimbrado($fechatimbrado)
    {
        $this->fechatimbrado = $fechatimbrado;

        return $this;
    }

    public function getCadenatimbre()
    {
        return $this->cadenatimbre;
    }

    public function setCadenatimbre($cadenatimbre)
    {
        $this->cadenatimbre = $cadenatimbre;

        return $this;
    }

    public function getFlagprovision()
    {
        return $this->flagprovision;
    }

    public function setFlagprovision($flagprovision)
    {
        $this->flagprovision = $flagprovision;

        return $this;
    }

    public function getFlagsendfiscal()
    {
        return $this->flagsendfiscal;
    }

    public function setFlagsendfiscal($flagsendfiscal)
    {
        $this->flagsendfiscal = $flagsendfiscal;

        return $this;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    public function getActivo()
    {
        return $this->activo;
    }

    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    public function getSalesmanclient()
    {
        return $this->salesmanclient;
    }

    public function setSalesmanclient($salesmanclient)
    {
        $this->salesmanclient = $salesmanclient;

        return $this;
    }

    public function getFlagsinmovseriales()
    {
        return $this->flagsinmovseriales;
    }

    public function setFlagsinmovseriales($flagsinmovseriales)
    {
        $this->flagsinmovseriales = $flagsinmovseriales;

        return $this;
    }

    public function getFlagfiscal()
    {
        return $this->flagfiscal;
    }

    public function setFlagfiscal($flagfiscal)
    {
        $this->flagfiscal = $flagfiscal;

        return $this;
    }

    public function getOrderfactint()
    {
        return $this->orderfactint;
    }

    public function setOrderfactint($orderfactint)
    {
        $this->orderfactint = $orderfactint;

        return $this;
    }

    public function getPaymentMethodCode()
    {
        return $this->paymentmethodcode;
    }

    public function setPaymentMethodCode($paymentmethodcode)
    {
        $this->paymentmethodcode = $paymentmethodcode;

        return $this;
    }

    public function getc_TipoDeComprobante()
    {
        return $this->c_TipoDeComprobante;
    }

    public function setc_TipoDeComprobante($c_TipoDeComprobante)
    {
        $this->c_TipoDeComprobante = $c_TipoDeComprobante;

        return $this;
    }

    public function getc_UsoCFDI()
    {
        return $this->c_UsoCFDI;
    }

    public function setc_UsoCFDI($c_UsoCFDI)
    {
        $this->c_UsoCFDI = $c_UsoCFDI;

        return $this;
    }

    public function getc_paymentid()
    {
        return $this->c_paymentid;
    }

    public function setc_paymentid($c_paymentid)
    {
        $this->c_paymentid = $c_paymentid;

        return $this;
    }
    
    public function getclaveFactura()
    {
        return $this->claveFactura;
    }

    public function setclaveFactura($claveFactura)
    {
        $this->claveFactura = $claveFactura;

        return $this;
    }
}
?>