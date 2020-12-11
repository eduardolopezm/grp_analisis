<?php
/***/   
class Locstockcls{
    private $loccode;
    private $stockid;
    private $quantity;
    private $quantityprod;
    private $reorderlevel;
    private $ontransit;
    private $quantityv2;
    private $timefactor;
    private $delay;
    private $localidad;
    private $minimumlevel;
    private $qtybysend;
    private $loccode_aux;
    private $safetyWeeks;
    private $DesiredWeeks;
    private $LeadTime;
    private $secondfactorconversion;

    public function getLoccode()
    {
        return $this->loccode;
    }

    public function setLoccode($loccode)
    {
        $this->loccode = $loccode;

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

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getQuantityprod()
    {
        return $this->quantityprod;
    }

    public function setQuantityprod($quantityprod)
    {
        $this->quantityprod = $quantityprod;

        return $this;
    }

    public function getReorderlevel()
    {
        return $this->reorderlevel;
    }

    public function setReorderlevel($reorderlevel)
    {
        $this->reorderlevel = $reorderlevel;

        return $this;
    }

    public function getOntransit()
    {
        return $this->ontransit;
    }

    public function setOntransit($ontransit)
    {
        $this->ontransit = $ontransit;

        return $this;
    }

    public function getQuantityv2()
    {
        return $this->quantityv2;
    }

    public function setQuantityv2($quantityv2)
    {
        $this->quantityv2 = $quantityv2;

        return $this;
    }

    public function getTimefactor()
    {
        return $this->timefactor;
    }

    public function setTimefactor($timefactor)
    {
        $this->timefactor = $timefactor;

        return $this;
    }

    public function getDelay()
    {
        return $this->delay;
    }

    public function setDelay($delay)
    {
        $this->delay = $delay;

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

    public function getMinimumlevel()
    {
        return $this->minimumlevel;
    }

    public function setMinimumlevel($minimumlevel)
    {
        $this->minimumlevel = $minimumlevel;

        return $this;
    }

    public function getQtybysend()
    {
        return $this->qtybysend;
    }

    public function setQtybysend($qtybysend)
    {
        $this->qtybysend = $qtybysend;

        return $this;
    }

    public function getLoccode_aux()
    {
        return $this->loccode_aux;
    }

    public function setLoccode_aux($loccode_aux)
    {
        $this->loccode_aux = $loccode_aux;

        return $this;
    }

    public function getSafetyWeeks()
    {
        return $this->safetyWeeks;
    }

    public function setSafetyWeeks($safetyWeeks)
    {
        $this->safetyWeeks = $safetyWeeks;

        return $this;
    }

    public function getDesiredWeeks()
    {
        return $this->DesiredWeeks;
    }

    public function setDesiredWeeks($DesiredWeeks)
    {
        $this->DesiredWeeks = $DesiredWeeks;

        return $this;
    }

    public function getLeadTime()
    {
        return $this->LeadTime;
    }

    public function setLeadTime($LeadTime)
    {
        $this->LeadTime = $LeadTime;

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
}

?>