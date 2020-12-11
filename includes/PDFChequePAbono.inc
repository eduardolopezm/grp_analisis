<?php 
define('FPDF_FONTPATH','./fonts/');
include ('includes/fpdf.php');
include ('Numbers/Words.php');

class PDFCheque extends FPDF {


	function NombreMes($MesId) {
		switch ($MesId) {
			case 1:
				$nmes = 'Enero';
				break;
			case 2:
				$nmes = 'Febrero';
				break;
			case 3:
				$nmes = 'Marzo';
				break;
			case 4:
				$nmes = 'Abril';
				break;
			case 5:
				$nmes = 'Mayo';
				break;
			case 6:
				$nmes = 'Junio';
				break;
			case 7:
				$nmes = 'Julio';
				break;
			case 8:
				$nmes = 'Agosto';
				break;
			case 9:
				$nmes = 'Septiembre';
				break;
			case 10:
				$nmes = 'Octubre';
				break;
			case 11:
				$nmes = 'Noviembre';
				break;
			case 12:
				$nmes = 'Diciembre';
				break;
		}
		return $nmes;
	}
	
	function exportPDF(){
		global $db;

		$this->fpdf('P','mm','Letter');
		$this->SetAutoPageBreak(true,5);
		$this->AliasNbPages();
		
		//for($i=1;$i<=2;$i++){
		//
			$this->AddPage();
			
			$this->SetFillColor(255,255,255);
			$this->SetFont('helvetica','',9);
				
			$type = $_GET['type'];
			$transno = $_GET['TransNo'];
			
			$sql = "Select beneficiary,amount,ref,date_format(transdate ,'%d/%m/%Y') as fecha,currcode,chequeno
					from banktrans
					where type=$type and (transno=$transno  or INSTR(ref,'".$transno."') > 0 or INSTR(beneficiary,'".$transno."') > 0)";
			
			$rs = DB_query($sql,$db);		
			$row = DB_fetch_array($rs);
			
			$fecha = $row['fecha'];
			
			$mes = substr($fecha,3,2);
			$dia = substr($fecha,0,2);
			$anio = substr($fecha,6,4);
			$fecha = "$dia de ".$this->NombreMes($mes)." de $anio";
			
			$arrbeneficiario = explode("@",$row['beneficiary']);
			$beneficiario = str_replace("&amp;","&",$arrbeneficiario[0]);$concepto = $row['ref'];
			$monto = $row['amount'];
			if ($monto < 0)
				$monto*=-1;
			
			$total = $monto;
			
			$moneda=$row['currcode'];
			$chequeno = $row['chequeno'];
			
			$separa=explode(".",str_replace(",","",number_format($total,2)));
			$montoctvs2 = $separa[1];
			if ($montoctvs2=="")
				$montoctvs2="00";
			
			$montoctvs1 = $separa[0];
			if (left($montoctvs2,3)>=995){
				$montoctvs1=$montoctvs1+1;
			
			}
	
			if ($moneda=='USD' ){
				$montoletra=Numbers_Words::toWords($montoctvs1,'en_US');
			}else{
				$montoletra=Numbers_Words::toWords($montoctvs1,'es');
			}
			
			$montocentavos=Numbers_Words::toWords($montoctvs2,'es');
			if ($moneda=='MXN'){
				$montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ." /100 M.N.";
			}
			else
			{
				$montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD";
			}
			
			$this->SetY(8);
			//fecha
			$this->cell(80,4,'',0,0,'R');
			$this->cell(100,4,'=Para Abono en Cuenta del Beneficiario=',0,1,'L');
			
			$this->SetY(12);
			//fecha//
			$this->cell(40,4,'',0,0,'R');
			$this->cell(120,4,$fecha,0,1,'R');
			$this->Ln(10);
			//beneficiariov
			$this->cell(30,4,'',0,0,'R');
			$this->cell(100,4,$beneficiario,0,0,'L');
			//monto numerico
			$this->cell(30,4,number_format($monto,2),0,0,'R');
			
			$this->Ln(11);
			//monto en letras
			$this->cell(30,4,'',0,0,'R');
			$this->cell(190,4,$montoletra,0,1,'L');
			$this->Ln();
				
			$this->cell(30,4,'No Poliza',0,0,'R');
			$this->cell(190,4,$transno,0,1,'L');
			$this->Ln(12);
			//concepto
			//$arrconcepto = explode('-',$concepto);
			//$concepto = $arrconcepto[1]; 
			//$this->cell(190,4,$concepto,0,1,'L');
			//$this->cell(190,4,"Num. Cheque ".$chequeno,0,1,'L');
			
		//}
			
		
		/*
			$this->Ln(4);
			$tama�ofirma = $this->GetStringWidth("FIRMA CHEQUE RECIBO");
			$this->SetX(140);
			$this->Cell($tama�ofirma+3,5,"FIRMA CHEQUE RECIBIDO","T","","L");
				$this->Ln(10);
			$this->Cell(35,5,"CUENTA","TB","","L");
			$this->Cell(110,5,"DESCRIPCION","TB","","L");
			$this->Cell(30,5,"DEBER","TB","","L");
			$this->Cell(20,5,"HABER","TB","","L");
			
			
			$totalAbonos=0;
			$totalCargos=0;
			$this->Ln(16);
			//detalle poliza
			$sql = "select gltrans.account,concat(chartmaster.accountname,'  ',narrative) as texto,amount, www_users.realname
					from gltrans 
					left join chartmaster ON gltrans.account = chartmaster.accountcode
					left join www_users ON www_users.userid = gltrans.userid
					where type=$type and typeno=$transno
					and amount <> 0";
			/*if($_SESSION['UserID'] == "admin"){
				echo '<pre>'.$sql;
			}*/
			/*$res = DB_query($sql,$db);
			while ($rows = DB_fetch_array($res)){
				$realizado = $rows['realname'];
				$nextLine = $this->GetY();
				$this->cell(35,5,$rows['account'],0,0,'C');
				$posY = $this->GetY();
				$this->MultiCell(105,5,$rows['texto'],0,'L');
				if ($posY < $this->GetY()){
					$nextLine = $this->GetY();
					$this->SetXY(140,$posY);
				}	
				
				if ($rows['amount'] > 0){
					$this->cell(30,5,number_format($rows['amount'],2),0,0,'R');
					$this->cell(30,5,'',0,1,'R');
					$totalCargos+=$rows['amount'];
				}
				else{
					$this->cell(30,5,'',0,0,'R');
					$this->cell(30,5,number_format(abs($rows['amount']),2),0,1,'R');
					$totalAbonos+=abs($rows[amount]);
				}
				
				if ($this->GetY() < $nextLine)
					$this->SetY($nextLine);
				
			}
			$sql = "SELECT www_users.realname
					FROM www_users
					WHERE www_users.userid ='".$_SESSION['UserID']."'";
			$result = DB_query($sql,$db);
			while($myrow = DB_fetch_array($result)){
				$realname = $myrow['realname'];
			}
			$this->SetY(215);

			$this->Cell(130,5,"SUMAS IGUALES -->","TB","","L");
			$this->cell(30,5,number_format($totalCargos,2),"TB",0,'R');
			$this->cell(30,5,number_format($totalAbonos,2),"TB",0,'R');
			
			$this->SetY(225);
			$this->Cell(50,4,"Impreso por",0,0,"L");
			$this->Cell(50,4,"Realizado por",0,0,"L");
			$this->Cell(50,4,"Autorizado por",0,0,"L");
			$this->cell(23,4,"Num. Cheque ",0,0,'L');
			$this->Cell(20,4,"Poliza No ",0,0,"L");
			$this->SetY(229);
			$this->Cell(50,4,$realname,0,0,"L");
			$this->Cell(50,4,$realizado,0,0,"L");
			$this->Cell(50,4,"",0,0,"L");
			$this->cell(23,4,$chequeno,0,0,'L');
			$this->Cell(20,4,$transno,0,0,"L");
					
			//mostrar*/
		$this->OutPut('cheque.pdf','D');
		
			
	}


}



?>