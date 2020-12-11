<?php
/**
 * Captura de Póliza Manual
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 06/11/2017
 * Fecha Modificación: 06/11/2017
 * Captura de Póliza Manual
 */

class Journal
{
    var $GLEntries;
    var $JnlDate;
    var $JournalType;
    var $GLItemCounter;
    var $GLItemID;
    var $JournalTotal;
    var $BankAccounts;
    var $origJnlDate;
    var $origJnlType;
    var $origJnlIndex;
    var $origJnlTypeName;
    var $JnlTag;
    var $JnlLegalId;
    var $PolizaRutaArchivos;
    var $extraPresupuestalCod;
    var $extraPresupuestalNom;

    function Journal()
    {
        $this->GLEntries = array();
        $this->GLItemCounter=0;
        $this->JournalTotal=0;
        $this->GLItemID=0;
        $this->BankAccounts = array();
        $this->origJnlType = 0;
        $this->origJnlIndex = 0;
        $this->JnlTag = 0;
        $this->JnlLegalId = 0;
        $this->PolizaRutaArchivos = "";
        ;
    }

    function Add_To_GLAnalysis(
        $Amount,
        $Narrative,
        $GLCode,
        $GLActName,
        $tag,
        $legalid = 0,
        $rate,
        $debtorno,
        $Branch,
        $stockid,
        $qty,
        $grns,
        $loccode,
        $EstimatedAvgCost,
        $Suppno,
        $Purchno,
        $ChequeNo,
        $catcuenta,
        $jobref,
        $bancodestino,
        $rfcdestino,
        $cuentadestino,
        $posted = 0,
        $ue = '',
        $ueDescripcion = '',
        $tipoPoliza = 287
    ) {
        if (isset($GLCode) and $Amount!=0) {
            $this->GLEntries[$this->GLItemID] = new JournalGLAnalysis(
                $Amount,
                $Narrative,
                $this->GLItemID,
                $GLCode,
                $GLActName,
                $tag,
                $legalid,
                $rate,
                $debtorno,
                $Branch,
                $stockid,
                $qty,
                $grns,
                $loccode,
                $EstimatedAvgCost,
                $Suppno,
                $Purchno,
                $ChequeNo,
                $catcuenta,
                $jobref,
                $bancodestino,
                $rfcdestino,
                $cuentadestino,
                $posted,
                $ue,
                $ueDescripcion,
                $tipoPoliza
            );
            $this->GLItemCounter++;
            $this->GLItemID++;
            $this->JournalTotal += $Amount;
            return 1;
        }
        return 0;
    }

    function remove_GLEntry($GL_ID)
    {
        $this->JournalTotal -= $this->GLEntries[$GL_ID]->Amount;
        unset($this->GLEntries[$GL_ID]);
        $this->GLItemCounter--;
    }
}

class JournalGLAnalysis
{
    var $Amount;
    var $Narrative;
    var $GLCode;
    var $GLActName;
    var $ID;
    var $tag;
    var $legalid;
    var $rate;
    var $debtorno;
    var $Branch;
    var $stockid;
    var $qty;
    var $grns;
    var $loccode;
    var $EstimatedAvgCost;
    var $Suppno;
    var $Purchno;
    var $ChequeNo;
    var $catcuenta;
    var $jobref;
    var $bancodestino;
    var $rfcdestino;
    var $cuentadestino;
    var $posted;
    var $ue;
    var $ueDescripcion;
    var $tipoPoliza;

    function JournalGLAnalysis(
        $Amt,
        $Narr,
        $id,
        $GLCode,
        $GLActName,
        $tag,
        $legalid = 0,
        $rate,
        $debtorno,
        $Branch,
        $stockid,
        $qty,
        $grns,
        $loccode,
        $EstimatedAvgCost,
        $Suppno,
        $Purchno,
        $ChequeNo,
        $catcuenta,
        $jobref,
        $bancodestino,
        $rfcdestino,
        $cuentadestino,
        $posted = 0,
        $ue = '',
        $ueDescripcion,
        $tipoPoliza
    ) {
        $this->Amount =$Amt;
        $this->Narrative = $Narr;
        $this->GLCode = $GLCode;
        $this->GLActName = $GLActName;
        $this->ID = $id;
        $this->tag = $tag;
        $this->legalid = $legalid;
        $this->rate = $rate;
        $this->debtorno = $debtorno;
        $this->Branch = $Branch;
        $this->stockid = $stockid;
        $this->qty = $qty;
        $this->grns = $grns;
        $this->loccode = $loccode;
        $this->EstimatedAvgCost = $EstimatedAvgCost;
        $this->Suppno = $Suppno;
        $this->Purchno = $Purchno;
        $this->ChequeNo = $ChequeNo;
        $this->catcuenta = $catcuenta;
        $this->jobref = $jobref;
        $this->bancodestino = $bancodestino;
        $this->rfcdestino = $rfcdestino;
        $this->cuentadestino = $cuentadestino;
        $this->posted = $posted;
        $this->ue = $ue;
        $this->ueDescripcion = $ueDescripcion;
        $this->tipoPoliza = $tipoPoliza;
    }
}
