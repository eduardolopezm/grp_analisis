<?php
  
class Stockmovesdao{
	private $pathprefix = "../.././";

	// funcion para asignar datos al stockmoves
	function Setstockmoves($arrsalesorderdetails, $debtortrans){
		require_once $this->pathprefix . 'model/Stockmovescls.php';
        require_once $this->pathprefix . 'core/ModeloBase.php';

		$modelo = new ModeloBase;

		$arrstockmoves = array();

		for($xx=0; $xx < count($arrsalesorderdetails); $xx++){
			$stockmoves = new Stockmovescls;

			$stockid = $arrsalesorderdetails[$xx]->getStkcode();
			$type = $debtortrans->getType();
			$transno = $debtortrans->getTransno();
			$loccode = $arrsalesorderdetails[$xx]->getFromstkloc();
			$trandate = $debtortrans->getTrandate();
			$debtorno = $debtortrans->getDebtorno();
			$branchcode = $debtortrans->getBranchcode();
			$prd = $debtortrans->getPrd();
			$qty = ($arrsalesorderdetails[$xx]->getQtyinvoiced() * -1);
			$refundpercentmv = 0;
			$show_on_inv_crds = 1;
			$warranty = 0;
			$tagref = $debtortrans->getTagref();
			$ref1 = "";
			$ref2 = "";
			$useridmov = "";
			$ratemov = 1;
			$price = $arrsalesorderdetails[$xx]->getUnitprice();
			$discountpercent = $arrsalesorderdetails[$xx]->getDiscountpercent();
			$discountpercent1 = 0;
			$discountpercent2 = 0;
			$totaldescuento = $arrsalesorderdetails[$xx]->getQtyinvoiced() * ($arrsalesorderdetails[$xx]->getUnitprice() * $arrsalesorderdetails[$xx]->getDiscountpercent());
			$narrative = $arrsalesorderdetails[$xx]->getNarrative();
			$reference = "";
			$showdescription = 1;
			$ref4 = $arrsalesorderdetails[$xx]->getOrderlineno();
			$localidad = "";
			$standardcost= 0;
			$avgcost = 0;

			// En esta seccion se saca el costo de acuerdo a la configuracion
			if ($_SESSION ['TypeCostStock'] == 2 or $_SESSION ['TypeCostStock'] == 3) {
                $SQL = "SELECT stockcostsxlegal.avgcost AS cost,
                            controlled,
                            serialised,
                            mbflag
                        FROM stockcostsxlegal
                        INNER JOIN tags ON stockcostsxlegal.legalid = tags.legalid
                        INNER JOIN stockmaster ON stockcostsxlegal.stockid = stockmaster.stockid
                        WHERE stockcostsxlegal.stockid ='" . $arrsalesorderdetails[$xx]->getStkcode() . "'
                        AND tags.tagref= '".$tagref."'";
            }

            if ($_SESSION ['TypeCostStock'] == 1) {
                $SQL = "SELECT stockcostsxtag.avgcost AS cost,
                            controlled,
                            serialised,
                            mbflag
                        FROM stockcostsxtag
                        INNER JOIN stockmaster ON stockcostsxtag.stockid = stockmaster.stockid
                        WHERE stockcostsxtag.stockid ='" . $arrsalesorderdetails[$xx]->getStkcode() . "'
                        AND stockcostsxtag.tagref = '" . $tagref . "'";

            }
            
            // Need to get the current standard cost for the item being issued
            $resultado = $modelo->ejecutarsql($SQL);
            $IssueItemRow = $resultado[0];
			$standardcost = $IssueItemRow['cost'];

			$stockmoves->setStockid($stockid);
			$stockmoves->setType($type);
			$stockmoves->setTransno($transno);
			$stockmoves->setLoccode($loccode);
			$stockmoves->setTrandate($trandate);
			$stockmoves->setDebtorno($debtorno);
			$stockmoves->setBranchcode($branchcode);
			$stockmoves->setPrd($prd);
			$stockmoves->setQty($qty);
			$stockmoves->setStandardcost($standardcost);
			$stockmoves->setRefundpercentmv($refundpercentmv);
			$stockmoves->setShow_On_Inv_Crds($show_on_inv_crds);
			$stockmoves->setWarranty($warranty);
			$stockmoves->setTagref($tagref);
			$stockmoves->setAvgcost($standardcost);
			$stockmoves->setRef1($ref1);
			$stockmoves->setRef2($ref2);
			$stockmoves->setUseridmov($useridmov);
			$stockmoves->setRatemov($ratemov);
			$stockmoves->setPrice($price);
			$stockmoves->setDiscountpercent($discountpercent);
			$stockmoves->setDiscountpercent1($discountpercent1);
			$stockmoves->setDiscountpercent2($discountpercent2);
			$stockmoves->setTotaldescuento($totaldescuento);
			$stockmoves->setNarrative($narrative);
			$stockmoves->setReference($reference);
			$stockmoves->setShowdescription($showdescription);
			$stockmoves->setRef4($ref4);
			$stockmoves->setLocalidad($localidad);

			$arrstockmoves[] = $stockmoves;
		}
		return $arrstockmoves;
	}



	function Insertstockmoves($stockmoves, $modelo){

		$pathprefix = "../.././";
		
		$UserID = $_SESSION["UserID"];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$sql = "";

		
		$sql = "INSERT INTO stockmoves (
									stockid,
									type,
									transno,
									loccode,
									trandate,
									debtorno,
									branchcode,
									prd,
									qty,
									standardcost,
									refundpercentmv,
									show_on_inv_crds,
									warranty,
									tagref,
									avgcost,
									ref1,
									ref2,
									useridmov,
									ratemov,
									price,
									discountpercent,
									discountpercent1,
									discountpercent2,
									totaldescuento,
									narrative,
									reference,
									showdescription,
									ref4,
                                    localidad
			) VALUES (
						'" . $stockmoves->getStockid() . "',
						'" . $stockmoves->getType() . "',
						'" . $stockmoves->getTransno() . "',
						'" . $stockmoves->getLoccode() . "',
						'" . date("Y-m-d") . "',
						'" . $stockmoves->getDebtorno() . "',
						'" . $stockmoves->getBranchcode() . "',
						'" . $stockmoves->getPrd() . "',
						'" . $stockmoves->getQty() . "',
						'" . $stockmoves->getStandardcost() . "',
						'" . $stockmoves->getRefundpercentmv() . "',
						'" . $stockmoves->getShow_On_Inv_Crds() . "',
						'" . $stockmoves->getWarranty() . "',
						'" . $stockmoves->getTagref() . "',
						'" . $stockmoves->getAvgcost() . "',
						'" . $stockmoves->getRef1() . "',
						'" . $stockmoves->getRef2() . "',
						'" . $stockmoves->getUseridmov() . "',
						'" . $stockmoves->getRatemov() . "',
						'" . $stockmoves->getPrice() . "',
						'" . $stockmoves->getDiscountpercent() . "',
						'" . $stockmoves->getDiscountpercent1() . "',
						'" . $stockmoves->getDiscountpercent2() . "',
						'" . $stockmoves->getTotaldescuento() . "',
						'" . $stockmoves->getNarrative() . "',
						'" . $stockmoves->getReference() . "',
						'" . $stockmoves->getShowdescription() . "',
						'" . $stockmoves->getRef4() . "',
						'" . $stockmoves->getLocalidad() . "')";
               

		$resp = $modelo->ejecutarsql($sql);
		$message = "";

		$cadena = explode(' ', trim($sql));
        if ($resp == true and !is_array($resp) and ($cadena[0] != 'INSERT') and ($cadena[0] != 'UPDATE') and ($cadena[0] != 'DELETE')){
			$success = false;
			$message = _('No se inserto registro en tabla de movimientos');

		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";

			}else{
				$message = _('Se inserto registro en la tabla de movimientos ' . $stockmoves->getType() . " - " . $stockmoves->getTransno());

			}
			
		}

		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		$response['error']['codeerror'] = $codeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		
		//return $response;
        
	}



}


?>