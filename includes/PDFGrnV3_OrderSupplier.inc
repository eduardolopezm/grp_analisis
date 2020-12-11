<?php 
define('FPDF_FONTPATH','./fonts/');
include ('includes/fpdf.php');
class SuscripcionesEspeciales extends FPDF {
	var $Recorset;
	var $db;
	function setRecordset($rs) {
		$this->recorset = $rs;
	}

	function setdb($db) {
		$this->db = $db;
	}
	function Header(){
		$sql=" SELECT distinct grnbatch,
					legalbusinessunit.address1,
					legalbusinessunit.address2,
					legalbusinessunit.address3,
					legalbusinessunit.address4,
					legalbusinessunit.address5,
					legalbusinessunit.telephone,
					legalbusinessunit.email,
					legalbusinessunit.logo,
					legalbusinessunit.fax,
					purchorders.currcode
				FROM grns,purchorderdetails inner join purchorders on purchorders.orderno=purchorderdetails.orderno
					INNER JOIN tags on tags.tagref=purchorders.tagref
					INNER JOIN legalbusinessunit on legalbusinessunit.legalid=tags.legalid
				WHERE purchorderdetails.orderno='".$_GET['PONo']."'
					AND grns.podetailitem=purchorderdetails.podetailitem
					AND grns.qtyrecd <> 0
					LIMIT 1";
		$result = DB_query($sql, $this->db);
		while($myrow3=DB_fetch_array($result)){
			$this->Image($myrow3['logo'], 5,5,50,40);
			$this->SetFont('helvetica','B',10);
			$this->SetFillColor(255, 255, 255);
			$this->SetXY(60,10);
			$this->cell(60,  4, $myrow3['legalname'], 0, 0, 'L', true);
			$this->SetX(210);
			$this->cell(50,  4, _('Recepcion No.'), 0, 0, 'L', true);
			$this->cell(20,  4, $_GET['PONo'], 0, 0, 'L', true);
			$this->Ln();
			$this->SetX(80);
			$this->cell(60,  4, $myrow3['address1'], 0, 0, 'L', true);
			$this->SetX(210);
			$this->cell(50,  4, _('No. Orden Compra'), 0, 0, 'L', true);
			$this->cell(20,  4, $myrow3['grnbatch'], 0, 0, 'L', true);
			$this->Ln();
			$this->SetX(80);
			$this->cell(20,  4, $myrow3['address2'], 0, 0, 'L', true);
			$this->SetX(210);
			$this->cell(20,  4, _('Impreso'), 0, 0, 'L', true);
			$this->cell(20,  4, Date($_SESSION['DefaultDateFormat']), 0, 0, 'L', true);
			$this->Ln();
			$this->SetX(80);
			$this->cell(20,  4, $myrow3['address3']. ' ' . $myrow3['address4'].' '.$myrow3['address5'], 0, 0, 'L', true);
			$this->Ln();
			$this->SetX(80);
			$this->cell(20,  4,  _('Tel') . ': ' . $myrow3['telephone'] . ' ' . _('Fax'). ': ' . $myrow3['fax'], 0, 0, 'L', true);
			$this->Ln();
			$this->SetX(100);
			$this->cell(20,  4, $myrow3['email'], 0, 0, 'L', true);
		}
		
		$this->Ln(14);
		$this->cell(20,  4, _('Codigo'), 0, 0, 'L', true);
		$this->cell(80,  4, _('Descripcion'), 0, 0, 'L', true);
		$this->cell(50,  4, _('Serie/Lote'), 0, 0, 'L', true);
		$this->cell(40,  4, _('Cantidad'), 0, 0, 'L', true);
		$this->cell(40,  4, _('Costo'), 0, 0, 'L', true);
		$this->cell(40,  4, _('Total'), 0, 0, 'L', true);
		$this->Ln(4);

		$this->Ln(6);
		$this->cell(260,1,"","B",0,"",true);
		$this->Ln(6);
	}
	function exportPDF(){
		$this->fpdf('L','mm','Letter');

		$this->AddPage();
		$this->SetFillColor(255,255,255);
		$this->SetFont('helvetica', '', 6);
		
		$Ruta = 0;
		$CategoriaId = 0;
		$index=0;
		while($myrow2 = DB_fetch_array($this->recorset)) {
			
			$MyCurrencyCode = $myrow2['currcode'];
			$_POST['GRNNo']=$myrow2['grnbatch'];
			$this->SetFillColor(255,255,255);
			$this->SetFont('helvetica', '', 6);
			$sql1="SELECT  distinct grnno, 
									itemcode, 
									deliverydate, 
									itemdescription, 
									sum(qtyrecd) as qtyrecd, 
									supplierid,
									serialno, 
									case when moveqty is null then 0 else moveqty end  as moveqty,
									stdcostunit
					FROM grns
						LEFT JOIN stockmoves ON grns.grnbatch =  stockmoves.transno and grns.itemcode=stockmoves.stockid and stockmoves.type = 25
						LEFT JOIN stockserialmoves ON stockserialmoves.stockmoveno = stockmoves.stkmoveno and stockserialmoves.stockid=stockmoves.stockid
					WHERE grnbatch='".$myrow2['grnbatch']."'
					GROUP BY itemcode
					ORDER BY itemcode,serialno";
			
			$result=DB_query($sql1, $this->db);
			$counter=1;
			$totalunidades=0;//
			$serialnoAnt = " ";
			$StockIDAnt = " ";
			while ($myrow=DB_fetch_array($result)) {

				$StockID=$myrow['itemcode'];
				$Date=$myrow['deliverydate'];
				$Description=$myrow['itemdescription'];
				$Quantity=$myrow['qtyrecd'];
				$SupplierID=$myrow['supplierid'];
				$serialno=$myrow['serialno'];
				$stdcostunit = $myrow['stdcostunit'];
				
				$this->cell(20,  4, $StockID, 0, 0, 'L', true);
				$this->cell(80,  4, $Description, 0, 0, 'L', true);
				$this->cell(50,  4, $serialno, 0, 0, 'L', true);
				if ($myrow['serialno'] == null) {
					$this->cell(40,  4, $Quantity, 0, 0, 'L', true);
					$cant = $Quantity;
				} else {
					$this->cell(40,  4, $myrow['moveqty'], 0, 0, 'L', true);
					$cant = $myrow['moveqty'];
				}
				
			
				
				$this->cell(40,  4, '$' . number_format($stdcostunit,2), 0, 0, 'L', true);
				$this->cell(40,  4, number_format($cant*$stdcostunit,2), 0, 0, 'L', true);
				$this->cell(20,  4, $MyCurrencyCode, 0, 0, 'L', true);
				$this->Ln();
			}
			
			
		}
		$this->Ln(6);
		if($this->GetY() >=250){
			$this->AddPage();
		}
		$sql='select suppname,currcode from suppliers where supplierid="'.$SupplierID.'"';
		$supplierresult=DB_query($sql, $db);
		$suppliermyrow=DB_fetch_array($supplierresult);
		$Supplier=$suppliermyrow[0];
		$Currcode=$suppliermyrow[1];
			
		$sql="SELECT sum(qtyrecd)as cantidadtotal from grns where grnbatch=".$_POST['GRNNo'];
		$cantidadresult=DB_query($sql, $db);
		$cantidadmyrow=DB_fetch_array($cantidadresult);
		$Cantidadtot=$cantidadmyrow[0];
			
		$this->cell(100,  4, _('Proveedor').': '.$Supplier, 0, 0, 'L', true);
		$this->Ln();
		$this->cell(60,  4, _('Fecha de recepcion').': '.$Date, 0, 0, 'L', true);
		$this->cell(60,  4, _('Unidades recibidas').': '.$Cantidadtot, 0, 0, 'L', true);
		$this->Ln();
		$this->cell(100,  4, _('PROVEEDOR, FAVOR DE ENTREGAR ESTE DOCUMENTO CON SU FACTURA PARA QUE CUENTAS POR PAGAR TRAMITE SU PAGO'), 0, 0, 'L', true);
		$this->Ln();
		$this->cell(100,  4, _('Recibido por').' ________________________________________', 0, 0, 'L', true);
		$this->OutPut('','I');
		die();
	}
}
?>