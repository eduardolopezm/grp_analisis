<?php
  
class Tags {
    private $tagref;
    private $tagdescription;
    private $areacode;
    private $legalid;
    private $tagname;
    private $u_department;
    private $address1;
    private $address2;
    private $address3;
    private $address4;
    private $address5;
    private $address6;
    private $cp;
    private $typeinvoice;
    private $datechange;
    private $tagsupplier;
    private $tagdebtorno;
    private $phone;
    private $typepack;
    private $showflujo;
    private $logotag;
    private $allowpartialinvoice;
    private $pofootertext;
    private $email;
    private $typegroup;
    private $lastUpdated;
    private $typetax;
    private $agentextag;
    private $allowpartialnotecredit;
    private $preferential;
    private $tagactive;

    

    public function getTagref()
    {
        return $this->tagref;
    }

    public function setTagref($tagref)
    {
        $this->tagref = $tagref;

        return $this;
    }

    public function getTagdescription()
    {
        return $this->tagdescription;
    }

    public function setTagdescription($tagdescription)
    {
        $this->tagdescription = $tagdescription;

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

    public function getLegalid()
    {
        return $this->legalid;
    }

    public function setLegalid($legalid)
    {
        $this->legalid = $legalid;

        return $this;
    }

    public function getTagname()
    {
        return $this->tagname;
    }

    public function setTagname($tagname)
    {
        $this->tagname = $tagname;

        return $this;
    }

    public function getU_department()
    {
        return $this->u_department;
    }

    public function setU_department($u_department)
    {
        $this->u_department = $u_department;

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

    public function getCp()
    {
        return $this->cp;
    }

    public function setCp($cp)
    {
        $this->cp = $cp;

        return $this;
    }

    public function getTypeinvoice()
    {
        return $this->typeinvoice;
    }

    public function setTypeinvoice($typeinvoice)
    {
        $this->typeinvoice = $typeinvoice;

        return $this;
    }

    public function getDatechange()
    {
        return $this->datechange;
    }

    public function setDatechange($datechange)
    {
        $this->datechange = $datechange;

        return $this;
    }

    public function getTagsupplier()
    {
        return $this->tagsupplier;
    }

    public function setTagsupplier($tagsupplier)
    {
        $this->tagsupplier = $tagsupplier;

        return $this;
    }

    public function getTagdebtorno()
    {
        return $this->tagdebtorno;
    }

    public function setTagdebtorno($tagdebtorno)
    {
        $this->tagdebtorno = $tagdebtorno;

        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    public function getTypepack()
    {
        return $this->typepack;
    }

    public function setTypepack($typepack)
    {
        $this->typepack = $typepack;

        return $this;
    }

    public function getShowflujo()
    {
        return $this->showflujo;
    }

    public function setShowflujo($showflujo)
    {
        $this->showflujo = $showflujo;

        return $this;
    }

    public function getLogotag()
    {
        return $this->logotag;
    }

    public function setLogotag($logotag)
    {
        $this->logotag = $logotag;

        return $this;
    }

    public function getAllowpartialinvoice()
    {
        return $this->allowpartialinvoice;
    }

    public function setAllowpartialinvoice($allowpartialinvoice)
    {
        $this->allowpartialinvoice = $allowpartialinvoice;

        return $this;
    }

    public function getPofootertext()
    {
        return $this->pofootertext;
    }

    public function setPofootertext($pofootertext)
    {
        $this->pofootertext = $pofootertext;

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

    public function getTypegroup()
    {
        return $this->typegroup;
    }

    public function setTypegroup($typegroup)
    {
        $this->typegroup = $typegroup;

        return $this;
    }

    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    public function getTypetax()
    {
        return $this->typetax;
    }

    public function setTypetax($typetax)
    {
        $this->typetax = $typetax;

        return $this;
    }

    public function getAgentextag()
    {
        return $this->agentextag;
    }

    public function setAgentextag($agentextag)
    {
        $this->agentextag = $agentextag;

        return $this;
    }

    public function getAllowpartialnotecredit()
    {
        return $this->allowpartialnotecredit;
    }

    public function setAllowpartialnotecredit($allowpartialnotecredit)
    {
        $this->allowpartialnotecredit = $allowpartialnotecredit;

        return $this;
    }

    public function getPreferential()
    {
        return $this->preferential;
    }

    public function setPreferential($preferential)
    {
        $this->preferential = $preferential;

        return $this;
    }

    public function getTagactive()
    {
        return $this->tagactive;
    }

    public function setTagactive($tagactive)
    {
        $this->tagactive = $tagactive;

        return $this;
    }
}

?>