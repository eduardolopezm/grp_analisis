<?php
/*
MODIFICADO POR: Desarrollador
FECHA: 14/NOV/2011
CAMBIOS:
1.- AGREGUE FUNCION DE DESCUENTO DE VENTAS POR PROMOCIONES 
FIN DE CAMBIOS

MODIFICADO POR: Desarrollador
FECHA: 17/NOV/2011
CAMBIOS:
1.- AGREGUE FUNCION DE DESCUENTO DE VENTAS POR PROMOCIONES CON MAXIMO Y MINIMO POR LISTA DE PRECIO
FIN DE CAMBIOS
*/
function GetPrice ($StockID, $DebtorNo, $BranchCode, $db){
	
	$Price = 0;
	$sql="SELECT prices.price,
		prices.debtorno,
		prices.branchcode
		FROM prices,
			debtorsmaster
		WHERE debtorsmaster.salestype=prices.typeabbrev
		AND debtorsmaster.debtorno='" . $DebtorNo . "'
		AND prices.stockid = '" . $StockID . "'
		AND prices.currabrev = debtorsmaster.currcode
		AND prices.debtorno=debtorsmaster.debtorno
		AND prices.branchcode='" . $BranchCode . "'";

	$ErrMsg =  _('Hay un problema para recuperar la informacion de precios para el producto') . ' ' . $StockID  . ' ' . _('y cliente ') . ' ' . $DebtorNo .  ' ' . _('el mensaje de error de SQL es ');
	$result = DB_query($sql, $db,$ErrMsg);
	if (DB_num_rows($result)==0){

		/* No result from go for customer and branch search try for just a customer match */
		$sql = "SELECT prices.price,
				prices.debtorno,
				prices.branchcode
			FROM prices,
				debtorsmaster
			WHERE debtorsmaster.salestype=prices.typeabbrev
			AND debtorsmaster.debtorno='" . $DebtorNo . "'
			AND prices.stockid = '" . $StockID . "'
			AND prices.currabrev = debtorsmaster.currcode
			AND prices.debtorno=debtorsmaster.debtorno
			AND prices.branchcode=''";


		$result = DB_query($sql,$db,$ErrMsg);
		
		if (DB_num_rows($result)==0){

			/*No special pricing use the customers normal price list */
			$sql = "SELECT prices.price,
					prices.debtorno,
					prices.branchcode
				FROM prices,
					debtorsmaster
				WHERE debtorsmaster.salestype=prices.typeabbrev
				AND debtorsmaster.debtorno='" . $DebtorNo . "'
				AND prices.stockid = '" . $StockID . "'
				AND prices.debtorno='' 
				AND prices.currabrev = debtorsmaster.currcode";
			$result = DB_query($sql,$db,$ErrMsg);
			
			if (DB_num_rows($result)==0){

				/* Now use the default salestype/price list cos all else has failed */
				$sql="SELECT prices.price
					FROM prices,
						debtorsmaster
					WHERE prices.stockid = '" . $StockID . "'
					AND prices.currabrev = debtorsmaster.currcode
					AND debtorsmaster.debtorno='" . $DebtorNo . "'
					AND prices.typeabbrev='" . $_SESSION['DefaultPriceList'] . "'
					AND prices.debtorno=''";
				
				$result = DB_query($sql, $db,$ErrMsg);
				

				if (DB_num_rows($result)==0){
					/*Not even a price set up in the default price list so return 0 */
					prnMsg(_('No hay precios establecidos para') . ' ' . $StockID,'warn');
					Return 0;
				}

			}
		}
	}

	if (DB_num_rows($result)!=0){
		/*There is a price from one of the above so return that */

		$myrow=DB_fetch_row($result);
		Return $myrow[0];
	} else {
		Return 0;
	}

}

// Precio sin mensajes
function GetPriceDOS ($StockID, $DebtorNo, $pricelist, $moneda, $db){
	
	$Price = 0;
	$sql="	SELECT prices.price, prices.bgcolor
		FROM stockmaster left join prices on stockmaster.stockid=prices.stockid ,taxauthrates
		WHERE taxauthrates.taxcatid=stockmaster.taxcatid
		AND prices.stockid = '" . $StockID . "'
		AND prices.debtorno='" . $DebtorNo . "'
		AND prices.currabrev='" . $moneda . "'
		AND prices.typeabbrev='" . $pricelist . "'";				
	$result = DB_query($sql, $db,$ErrMsg);
	if (DB_num_rows($result)!=0){
	} else {
		$sql="  SELECT prices.price,prices.bgcolor
			FROM stockmaster left join prices on stockmaster.stockid=prices.stockid ,taxauthrates
			WHERE taxauthrates.taxcatid=stockmaster.taxcatid
			AND prices.stockid = '" . $StockID . "'
			AND prices.currabrev='" . $moneda . "'
			AND prices.typeabbrev='" . $pricelist . "'";
		$result = DB_query($sql, $db,$ErrMsg);		
		
	}
	
	if (DB_num_rows($result)!=0){
		$myrow=DB_fetch_row($result);
		Return $myrow[0].'|'.$myrow[1];
	} else {
		Return '0'.'|'.' ';
	}

}

function GetPriceWTAX ($StockID, $DebtorNo, $pricelist, $moneda, $sucursal="", $db){
	
	$Price = 0;
	$sql="	SELECT prices.price*(1+taxauthrates.taxrate), prices.bgcolor
		FROM stockmaster left join prices on stockmaster.stockid=prices.stockid ,taxauthrates
		WHERE taxauthrates.taxcatid=stockmaster.taxcatid
		AND prices.stockid = '" . $StockID . "'
		AND prices.debtorno='" . $DebtorNo . "'
		AND prices.currabrev='" . $moneda . "'
		AND prices.typeabbrev='" . $pricelist . "'";				
	$result = DB_query($sql, $db,$ErrMsg);
	if (DB_num_rows($result)!=0){
	} else {
		$sql="  SELECT prices.price*(1+taxauthrates.taxrate),prices.bgcolor
			FROM stockmaster left join prices on stockmaster.stockid=prices.stockid ,taxauthrates
			WHERE taxauthrates.taxcatid=stockmaster.taxcatid
			AND prices.stockid = '" . $StockID . "'
			AND prices.currabrev='" . $moneda . "'
			AND prices.areacode='" . $sucursal . "'
			AND prices.typeabbrev='" . $pricelist . "'";
		$result = DB_query($sql, $db,$ErrMsg);
		if (DB_num_rows($result)!=0){
		} else {
			$sql="  SELECT prices.price*(1+taxauthrates.taxrate),prices.bgcolor
				FROM stockmaster left join prices on stockmaster.stockid=prices.stockid ,taxauthrates
				WHERE taxauthrates.taxcatid=stockmaster.taxcatid
				AND prices.stockid = '" . $StockID . "'
				AND prices.currabrev='" . $moneda . "'
				AND prices.typeabbrev='" . $pricelist . "'";
			$result = DB_query($sql, $db,$ErrMsg);		
			
		}
		
		
	}
	
	//echo '<pre>'.$sql;
	if (DB_num_rows($result)!=0){
		$myrow=DB_fetch_row($result);
		Return $myrow[0].'|'.$myrow[1];
	} else {
		Return '0'.'|'.' ';
	}

}

//Precio sin IVA
function GetPriceWTAXSinIVA($StockID, $DebtorNo, $pricelist, $moneda="MXN", $sucursal="", $db){
	
	$Price = 0;
			if (strlen(trim($moneda)) == 0)
				$moneda = 'MXN';
			$sql="	SELECT prices.price, prices.bgcolor
				FROM stockmaster left join prices on stockmaster.stockid=prices.stockid 
				WHERE prices.stockid = '" . $StockID . "'
				AND prices.debtorno=' '
				AND prices.currabrev='" . $moneda . "'
				AND prices.typeabbrev='" . $pricelist . "'";				
			$result = DB_query($sql, $db,$ErrMsg);
			if (DB_num_rows($result)!=0){
			}else {
				$sql="  SELECT prices.price,prices.bgcolor
					FROM stockmaster left join prices on stockmaster.stockid=prices.stockid
					WHERE prices.stockid = '" . $StockID . "'
					AND prices.currabrev='" . $moneda . "'
					AND prices.areacode='" . $sucursal . "'
					AND prices.typeabbrev='" . $pricelist . "'";
				$result = DB_query($sql, $db,$ErrMsg);
				if (DB_num_rows($result)!=0){
				}else {
					$sql="  SELECT prices.price,prices.bgcolor
						FROM stockmaster left join prices on stockmaster.stockid=prices.stockid
						WHERE prices.stockid = '" . $StockID . "'
						AND prices.currabrev='" . $moneda . "'
						AND prices.typeabbrev='" . $pricelist . "'";
					$result = DB_query($sql, $db,$ErrMsg);		
				}
			}
	
	if (DB_num_rows($result)!=0){
		$myrow=DB_fetch_row($result);
		Return $myrow[0].'|'.$myrow[1];
	} else {
		Return '0'.'|'.' ';
	}
}


function GetPriceWNoTAX ($StockID, $DebtorNo, $pricelist, $moneda="MXN", $sucursal="", $db){
	
	$Price = 0;
	$sql="	SELECT prices.price, prices.bgcolor
		FROM stockmaster left join prices on stockmaster.stockid=prices.stockid ,taxauthrates
		WHERE taxauthrates.taxcatid=stockmaster.taxcatid
		AND prices.stockid = '" . $StockID . "'
		AND prices.debtorno='" . $DebtorNo . "'
		AND prices.currabrev='" . $moneda . "'
		AND prices.typeabbrev='" . $pricelist . "'";				
	$result = DB_query($sql, $db,$ErrMsg);
	if (DB_num_rows($result)!=0){
	} else {
		$sql="  SELECT prices.price,prices.bgcolor
			FROM stockmaster left join prices on stockmaster.stockid=prices.stockid ,taxauthrates
			WHERE taxauthrates.taxcatid=stockmaster.taxcatid
			AND prices.stockid = '" . $StockID . "'
			AND prices.currabrev='" . $moneda . "'
			AND prices.areacode='" . $sucursal . "'
			AND prices.typeabbrev='" . $pricelist . "'";
		$result = DB_query($sql, $db,$ErrMsg);
		if (DB_num_rows($result)!=0){
		} else {
			$sql="  SELECT prices.price,prices.bgcolor
				FROM stockmaster left join prices on stockmaster.stockid=prices.stockid ,taxauthrates
				WHERE taxauthrates.taxcatid=stockmaster.taxcatid
				AND prices.stockid = '" . $StockID . "'
				AND prices.currabrev='" . $moneda . "'
				AND prices.typeabbrev='" . $pricelist . "'";
			$result = DB_query($sql, $db,$ErrMsg);		
			
		}
		
	}
	
	if (DB_num_rows($result)!=0){
		$myrow=DB_fetch_row($result);
		Return $myrow[0].'|'.$myrow[1];
	} else {
		Return '0'.'|'.' ';
	}

}

function GetListPrice ($StockID, $TypeDebtorNo, $NewItemQty, $ListaANT, $db){
	
	//$Price = 0;
	$sql="	SELECT typeabbrev
		FROM saleschangesprices
		WHERE stockid = '" . $StockID . "'
		AND clienttype='" . $TypeDebtorNo . "'
		AND '" . $NewItemQty . "' between unitmin and unitmax ";				
	$result = DB_query($sql, $db,$ErrMsg);
	if (DB_num_rows($result)!=0){
		$myrow=DB_fetch_row($result);
		return $myrow[0];
	} else {
		return $ListaANT;
	}

}		


function GetListPricexDiscounts ($StockID, $db){

	$sql="	SELECT discountpercent/100
		FROM salesxdiscounts
		WHERE stockid = '" . $StockID . "'
		AND '" . Date('Y-m-d') . " 23:59:59' between effectiveafter and effectiveto and active=1 ";				
	$result = DB_query($sql, $db,$ErrMsg);
	if (DB_num_rows($result)!=0){
		$myrow=DB_fetch_row($result);
		return $myrow[0];
	} else {
		return 0;
	}

}	


function GetListPricexDiscountsMin ($StockID,$ListaANT, $db){
	
	$sql="	SELECT discountpercent/100
		FROM salesxdiscounts
		WHERE stockid = '" . $StockID . "'
		AND pricelistdis like '%".$ListaANT."%'
		AND '" . Date('Y-m-d') . " 23:59:59' between effectiveafter and effectiveto and active=1 ";
	
	$result = DB_query($sql, $db,$ErrMsg);
	if (DB_num_rows($result)!=0){
		$myrow=DB_fetch_row($result);
		return $myrow[0];
	} else {
		return 0;
	}

}	

function GetListPricexDiscountsMax ($StockID,$ListaANT, $db){
	
	$sql="	SELECT discountpercentmax/100
		FROM salesxdiscounts
		WHERE stockid = '" . $StockID . "'
		AND pricelistdis like '%".$ListaANT."%'
		AND '" . Date('Y-m-d') . " 23:59:59' between effectiveafter and effectiveto and active=1 ";
	$result = DB_query($sql, $db,$ErrMsg);
	if (DB_num_rows($result)!=0){
		$myrow=DB_fetch_row($result);
		return $myrow[0];
	} else {
		return 0;
	}

}	

?>