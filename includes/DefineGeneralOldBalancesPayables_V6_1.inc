<?php 
/*
 Creado por: Jesus Guadalupe Vargas Montes
 Fecha Creacion: 30/07/2013
 Descripcion: Clase para el alta de calendario
 *///
Class ReporteClienteFactura{
	var $debtorno;
	var $name;
	var $transno;
	var $typeid;
	var $folio;
	var $fechaemision;
	var $vencimiento;
	var $tc;
	var $saldomxn;
	var $novencidomxn;
	var $vencidomxn;
	var $saldoext;
	var $novencidoext;
	var $vencidoext;
	var $saldototal;
	var $novencidototal;
	var $vencidototal;
	var $NumLine;

function ReporteClienteFactura($debtorno,$name,$transno,$typeid,$folio,$fechaemision,$vencimiento,$tc,$saldomxn,$novencidomxn,$vencidomxn,$saldoext,$novencidoext,$vencidoext,$saldototal,$novencidototal,$vencidototal,$NumLine){
	$this->debtorno = $debtorno;
	$this->name =$name;
	$this->transno = $transno;
	$this->typeid =$typeid;
	$this->folio =$folio;
	$this->fechaemision =$fechaemision;
	$this->vencimiento = $vencimiento;
	$this->tc =$tc;
	$this->saldomxn =$saldomxn;
	$this->novencidomxn =$novencidomxn;
	$this->vencidomxn = $vencidomxn;
	$this->saldoext =$saldoext;
	$this->novencidoext =$novencidoext;
	$this->vencidoext =$vencidoext;
	$this->saldototal = $saldototal;
	$this->novencidototal =$novencidototal;
	$this->vencidototal =$vencidototal;
	$this->NumLine = $NumLine;//
}
}


?>