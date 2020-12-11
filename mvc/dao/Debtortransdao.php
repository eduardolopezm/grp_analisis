<?php
// 
class Debtortransdao{
	private $pathprefix = "../.././";

	function Setdebtortrans($salesorders, $tipodocto, $paymentmethodcode, $paymentReferencia, $comments, $tipoComprobante="", $usoCFDI="", $metodoPago="", $claveConfirmacion="", $modelo){
		$transactiondate = date("d") . "/" .  date("m") . "/" . date("Y");
		$periodno = $modelo->getperiodnumber($transactiondate, $salesorders->getTagref());

		$transno = $modelo->getdocumentnumber($tipodocto);
		$type = $tipodocto;
		$debtorno = $salesorders->getDebtorno();
		$branchcode = $salesorders->getBranchcode();
		//****$trandate = 
		$prd = $periodno;
		$reference = '';
		$tpe = $salesorders->getOrdertype();
		$order_ = $salesorders->getOrderno();
		$ovamount = 0;
		$ovgst = 0;
		$ovfreight = 0;
		$rate = 1;
		$invtext = $comments;
		$shipvia = "";
		$consignment = "";
		$tagref = $salesorders->getTagref();
		$currcode = $salesorders->getCurrcode();
		$folio = "";
		$origtrandate = "";
		$discountpercentpayment = 0;
		$taxret = "";
		$paymentname = $salesorders->getPaymentname();
		$nocuenta = $paymentReferencia; //$salesorders->getNocuenta();
		$nopedidof = 0;
		$noentradaf = 0;
		$noremisionf = 0;
		$orderfactint = 0;
		$showcomments = 1;

		require_once $this->pathprefix . 'model/Debtortranscls.php';
		$debtortrans = new Debtortranscls;

		$debtortrans->setTransno($transno);
		$debtortrans->setType($type);
		$debtortrans->setDebtorno($debtorno);
		$debtortrans->setBranchcode($branchcode);
		//$debtortrans->setTrandate($trandate);
		$debtortrans->setPrd($prd);
		$debtortrans->setReference($reference);
		$debtortrans->setTpe($tpe);
		$debtortrans->setOrder_($order_);
		$debtortrans->setOvamount($ovamount);
		$debtortrans->setOvgst($ovgst);
		$debtortrans->setOvfreight($ovfreight);
		$debtortrans->setRate($rate);
		$debtortrans->setInvtext($invtext);
		$debtortrans->setShipvia($shipvia);
		$debtortrans->setConsignment($consignment);
		$debtortrans->setTagref($tagref);
		$debtortrans->setCurrcode($currcode);
		$debtortrans->setFolio($folio);
		//$debtortrans->setOrigtrandate($origtrandate);
		$debtortrans->setDiscountpercentpayment($discountpercentpayment);
		$debtortrans->setTaxret($taxret);
		$debtortrans->setPaymentname($paymentname);
		$debtortrans->setNocuenta($nocuenta);
		$debtortrans->setNopedidof($nopedidof);
		$debtortrans->setNoentradaf($noentradaf);
		$debtortrans->setNoremisionf($noremisionf);
		$debtortrans->setOrderfactint($orderfactint);
		$debtortrans->setPaymentMethodCode($paymentmethodcode);
		$debtortrans->setShowcomments($showcomments);

		$debtortrans->setc_TipoDeComprobante($tipoComprobante);
		$debtortrans->setc_UsoCFDI($usoCFDI);
		$debtortrans->setc_paymentid($metodoPago);
		$debtortrans->setclaveFactura($claveConfirmacion);

		return $debtortrans;
	}

	function Insertdebtortrans($debtortrans, $modelo){

		$pathprefix = "../.././";
		//require_once $pathprefix . 'core/ModeloBase.php';
		
		$UserID = $_SESSION['UserID'];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$sql = "";

		//$modelo = new ModeloBase;


		$sql = "INSERT INTO debtortrans (
                            transno,
                            type,
                            debtorno,
                            branchcode,
                            trandate,
                            prd,
                            reference,
                            tpe,
                            order_,
                            ovamount,
                            ovgst,
                            ovfreight,
                            rate,
                            invtext,
                            shipvia,
                            consignment,
                            tagref,
                            currcode,
                            folio,
                            origtrandate,
                            discountpercentpayment,
                            taxret,
                            paymentname,
                            nocuenta,
                            nopedidof,
                            noentradaf,
                            noremisionf,
                            orderfactint,
                            codesat,
                            userid,
                            showcomments,
                            c_TipoDeComprobante,
                            c_UsoCFDI,
                            c_paymentid,
                            claveFactura
                            )
                VALUES (
                            '" . $debtortrans->getTransno() . "',
							'" . $debtortrans->getType() . "',
							'" . $debtortrans->getDebtorno() . "',
							'" . $debtortrans->getBranchcode() . "',
							'" . date("Y-m-d H:i:s") . "',
							'" . $debtortrans->getPrd() . "',
							'" . $debtortrans->getReference() . "',
							'" . $debtortrans->getTpe() . "',
							'" . $debtortrans->getOrder_() . "',
							'" . $debtortrans->getOvamount() . "',
							'" . $debtortrans->getOvgst() . "',
							'" . $debtortrans->getOvfreight() . "',
							'" . $debtortrans->getRate() . "',
							'" . $debtortrans->getInvtext() . "',
							'" . $debtortrans->getShipvia() . "',
							'" . $debtortrans->getConsignment() . "',
							'" . $debtortrans->getTagref() . "',
							'" . $debtortrans->getCurrcode() . "',
							'" . $debtortrans->getFolio() . "',
							'" . date("Y-m-d H:i:s") . "',
							'" . $debtortrans->getDiscountpercentpayment() . "',
							'" . $debtortrans->getTaxret() . "',
							'" . $debtortrans->getPaymentname() . "',
							'" . $debtortrans->getNocuenta() . "',
							'" . $debtortrans->getNopedidof() . "',
							'" . $debtortrans->getNoentradaf() . "',
							'" . $debtortrans->getNoremisionf() . "',
							'" . $debtortrans->getOrderfactint() . "',
							'" . $debtortrans->getPaymentMethodCode() . "',
							'" . $_SESSION['UserID'] . "',
							'" . $debtortrans->getShowcomments() . "',
							'" . $debtortrans->getc_TipoDeComprobante() . "',
							'" . $debtortrans->getc_UsoCFDI() . "',
							'" . $debtortrans->getc_paymentid() . "',
							'" . $debtortrans->getclaveFactura() . "'
                        )";
		
		$resp = $modelo->ejecutarSql($sql);
		$message = "";

		$cadena = explode(' ', trim($sql));
        if ($resp == true and !is_array($resp) and ($cadena[0] != 'INSERT') and ($cadena[0] != 'UPDATE') and ($cadena[0] != 'DELETE')){
			$success = false;
			$message = _('No se inserto registro en tabla de documentos');
		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";
			}else{
				$message = _('Se inserto registro en debtotrans ' . $debtortrans->getType() . " - " . $debtortrans->getTransno());
				
			}
			
		}

		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		$response['error']['codeerror'] = $codeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		
		return $resp;
	}
}


?>