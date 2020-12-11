<?php
  
/*CLASE DE PRODUCTOS*/
class Stockmasterdao{
	private $pathprefix = "../.././";

	function Searchproducts($descrip, $tagref, $loccode, $currency, $typeabbrev){

		require_once $this->pathprefix . 'core/ModeloBase.php';

		$UserID = $_SESSION["UserID"];
		//$tagref = "7";
		//$currency = "MXN";
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		//$response = array();
		$arrproducts = array();

		$iva = '0';
		
		//$loccode = 'PCQ1';
		$modelo = new ModeloBase;

		$sqlListaPrecio = "";
		if (!empty($typeabbrev)) {
			$sqlListaPrecio = " and p.typeabbrev = '" . $typeabbrev . "' ";
		}

		$descrip = str_replace(' ', '%', $descrip);
		$sql = "SELECT s.stockid,  s.categoryid, s.description, s.units, s.mbflag, s.taxcatid, taxauthrates.taxrate ,IFNULL(p.price,0) as price,
						IFNULL(c.avgcost,0) as cost, p.typeabbrev, IFNULL(i.quantity - i.ontransit,0) as available
			FROM stockmaster s
			LEFT JOIN (
						SELECT stockid, typeabbrev, price 
						FROM prices 
						WHERE currabrev = '" . $currency . "'
						GROUP BY stockid
						) as p ON s.stockid = p.stockid " . $sqlListaPrecio . "
			LEFT JOIN stockcostsxlegal c ON s.stockid = c.stockid
			JOIN locstock i ON i.stockid = s.stockid and i.loccode = '" . $loccode . "'
			INNER JOIN legalbusinessunit l ON c.legalid = l.legalid
			INNER JOIN tags t ON l.legalid = t.legalid and t.tagref = '" . $tagref . "'
			LEFT JOIN taxauthrates ON taxauthrates.taxcatid = s.taxcatid
			WHERE s.stockid like '%" . $descrip . "%'
				or s.description like '%" . $descrip . "%'";

		$sql = "SELECT s.stockid,  s.categoryid, s.description, s.units, s.mbflag, s.taxcatid, taxauthrates.taxrate ,IFNULL(p.price,0) as price,
						IFNULL(c.avgcost,0) as cost, p.typeabbrev, 999999999 as available, s.disminuye_ingreso,
						IFNULL(tb_descuentos.nu_porcentaje, 0) as discount,
						tb_descuentos.dtm_fin as vigenciaDiscount
			FROM stockmaster s
			LEFT JOIN (
						SELECT stockid, typeabbrev, price 
						FROM prices 
						WHERE currabrev = '" . $currency . "'
						GROUP BY stockid
						) as p ON s.stockid = p.stockid " . $sqlListaPrecio . "
			LEFT JOIN stockcostsxlegal c ON s.stockid = c.stockid
			JOIN locstock i ON i.stockid = s.stockid and i.loccode = '" . $loccode . "'
			LEFT JOIN legalbusinessunit l ON c.legalid = l.legalid
			LEFT JOIN tags t ON l.legalid = t.legalid and t.tagref = '" . $tagref . "'
			LEFT JOIN taxauthrates ON taxauthrates.taxcatid = s.taxcatid
			LEFT JOIN tb_descuentos on s.stockid = tb_descuentos.id_parcial AND tb_descuentos.dtm_fin >= '".date('Y-m-d')."'
			WHERE s.tipo_dato = 2 
			and (s.stockid like '%" . $descrip . "%' or s.description like '%" . $descrip . "%')";
			// and s.disminuye_ingreso = 0

		$resp = $modelo->ejecutarsql($sql);
		$message = "";

		if ($resp == true and !is_array($resp)){
			$success = false;
			$message = _('No existe registros para la busqueda de ' . $descrip);
		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";
			}else{
				for($xx = 0; $xx < count($resp); $xx++){

					$stockid = html_entity_decode ( ($resp[$xx]['stockid']) );
					$stockid = str_replace("Ã‘", "Ñ", $stockid);
					$stockid= str_replace("ï¿½", "ó", $stockid);

					$description = html_entity_decode ( ($resp[$xx]['description']) );
					$description = str_replace("Ã‘", "Ñ", $description);
					$description= str_replace("ï¿½", "ó", $description);

					//Obtener datos de las listas de precios
					$arrListas = array();
					$sql = "SELECT prices.currabrev, prices.price, salestypes.typeabbrev, salestypes.sales_type, prices.norango_inicial, prices.norango_final, prices.isRango
							FROM salestypes
							LEFT JOIN prices ON prices.typeabbrev = salestypes.typeabbrev and prices.stockid = '".$stockid."'
							GROUP BY salestypes.typeabbrev";
					$resp2 = $modelo->ejecutarsql($sql);
					if ($resp2 == false){
						$msgerror = "ERROR EN LA CONSULTA";
						$typeerror = "MYSQL ERROR";
						$codeerror = "002";
					}else{
						for($yy = 0; $yy < count($resp2); $yy++){
							$datosLista = array(
								"currabrev" => $resp2[$yy]['currabrev'],
								"price" => ($resp2[$yy]['price'] != "" ? $resp2[$yy]['price'] : 0),
								"typeabbrev" => $resp2[$yy]['typeabbrev'],
								"sales_type" => $resp2[$yy]['sales_type'],
								"norango_inicial" => $resp2[$yy]['norango_inicial'],
								"norango_final" => $resp2[$yy]['norango_final'],
								"isRango" => $resp2[$yy]['isRango']
							);

							array_push($arrListas, $datosLista);
						}
					}

					$quantity = 1;

					$iva = $resp[$xx]['taxrate'];

					if (empty($resp[$xx]['disminuye_ingreso'])) {
						$resp[$xx]['disminuye_ingreso'] = 0;
					}

					if ($resp[$xx]['disminuye_ingreso'] == '1') {
						// $quantity = abs($quantity) * -1;
					}
					
					$arrproduct = array(
							"NumRow" => 0,
							"stockid" => $stockid,
							"categoryid" => ( ($resp[$xx]['categoryid']) ),
							"description" => $description,
							"units" => ( ($resp[$xx]['units']) ),
							"mbflag" => ( ($resp[$xx]['mbflag']) ),
							"taxcatid" => ( ($resp[$xx]['taxcatid']) ),
							"quantity" => $quantity,
							"price" => $resp[$xx]['price'], //Precio para realizar operaciones
							"priceFormat" => number_format($resp[$xx]['price'], 2, ',', ' '), //Mostrar precio formateado
							"discount" => date('Y-m-d') <= $resp[$xx]['vigenciaDiscount'] ? $resp[$xx]['discount'] : 0,
							"salestype" => $resp[$xx]['typeabbrev'],
							"cost" => $resp[$xx]['cost'],
							"available" => $resp[$xx]['available'],
							"disminuye_ingreso" => $resp[$xx]['disminuye_ingreso'],
							"taxrate" => $iva,
							"vacio" => "", //seleccionar cotizacion no visualiza datos
							"subtotal" => $quantity * $resp[$xx]['price'],
							"iva" => $iva * $resp[$xx]['price'],
							"total" => ($quantity * $resp[$xx]['price']) + ($iva * $resp[$xx]['price']),
							"idcontrato" => 0,
							"listasPrecio" => $arrListas
						);

					//$response['data'][] = array("0"=>$arrproduct);
					//$arrproducts[] = $arrproduct;
					array_push($arrproducts, $arrproduct);
				}
			}
		}
		
		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		$response['data'] = $arrproducts;
		
		//header('Content-type: application/json; charset=utf-8');
  		//return json_encode($response, JSON_FORCE_OBJECT);
  		
  		return $response;
	}

	function desproteger_cadena( $cadena )
	{
		$out=$cadena;
		$out= html_entity_decode( $out, ENT_QUOTES );

		if( strchr( $out, "<" ) )
		$out= str_replace( "<", htmlentities("<", ENT_QUOTES), $out );
		if( strchr( $out, ">" ) )
		$out= str_replace( ">", htmlentities(">", ENT_QUOTES), $out );

		if( strchr( $out, "\n" ) )
		$out= str_replace( "\n", "<br>", $out );
		if( strchr( $out, "\t" ) )
		$out= str_replace( "\t", "&nbsp;&nbsp;&nbsp;", $out );

		return $out;
	}

	function Getstockbylocation($stockid, $location, $exis){
		
        require_once $this->pathprefix . 'core/ModeloBase.php';

        $UserID = $_SESSION["UserID"];
        $success = true;
        $msgerror = "";
        $typeerror = "";
        $codeerror = 0;
        $arrstocks = array();
        $solodisponible= "";

        $modelo = new ModeloBase();
        
        if ($exis == 1){
            $solodisponible= " AND l.quantity!=0 ";
        }

        $sql = "SELECT l.stockid, l.loccode, a.locationname, a.tagref, t.tagdescription, (l.quantity - l.ontransit) as stock
                        FROM locstock l 
                                INNER JOIN locations a ON l.loccode = a.loccode
                                INNER JOIN tags t ON a.tagref = t.tagref 
                        WHERE l.stockid = '" . $stockid . "'
                                AND l.loccode <> '" . $location . "'".$solodisponible;

        $resp = $modelo->ejecutarsql($sql);
        $message = "";

        if ($resp == true and !is_array($resp)){
            $success = false;
            $message = _('No existe registros para la busqueda de ' . $stockid);
        }else{
            if ($resp == false){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "001";

            }else{
                for($xx = 0; $xx < count($resp); $xx++){

                        $arrstock = array(
                                        "stockid" => $resp[$xx]['stockid'],
                                        "loccode" => $resp[$xx]['loccode'],
                                        "locationname" => $resp[$xx]['locationname'],
                                        "tagref" => $resp[$xx]['tagref'],
                                        "tagdescription" => $resp[$xx]['tagdescription'],
                                        "stock" => $resp[$xx]['stock'],
                                        "request" => "0"
                                );
                        array_push($arrstocks, $arrstock);

                }
            }

        }

        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        $response['error']['codeerror'] = $sql;
        $response['data']['message'] = $message;
        $response['data'] = $arrstocks;

        //header('Content-type: application/json; charset=utf-8');
        //return json_encode($response, JSON_FORCE_OBJECT);

        return $response;
	}
     
	function Stockbyid($id){

		require_once $this->pathprefix . 'core/ModeloBase.php';
		require_once $this->pathprefix . 'model/Stockmaster.php';

		$UserID = $_SESSION["UserID"];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$sql = "";

		$modelo = new ModeloBase;

		$sql = "SELECT stockid,
						spes,
						categoryid,
						description,
						longdescription,
						manufacturer,
						stockautor,
						units,
						mbflag,
						lastcurcostdate,
						actualcost,
						lastcost,
						materialcost,
						labourcost,
						overheadcost,
						lowestlevel,
						discontinued,
						controlled,
						enviarmasisa,
						eoq,
						volume,
						kgs,
						barcode,
						discountcategory,
						taxcatid,
						taxcatidret,
						serialised,
						appendfile,
						perishable,
						decimalplaces,
						nextserialno,
						pansize,
						shrinkfactor,
						netweight,
						idclassproduct,
						stocksupplier,
						securitypoint,
						idetapaflujo,
						OrigenCountry,
						OrigenDate,
						stockupdate,
						pkg_type,
						isbn,
						grade,
						subject,
						deductibleflag,
						u_typeoperation,
						typeoperationdiot,
						height,
						width,
						large,
						fichatecnica,
						percentfactorigi,
						inpdfgroup,
						flagadvance,
						eq_conversion_factor,
						eq_stockid,
						flagcommission,
						fijo,
						fecha_modificacion,
						unitequivalent,
						factorconversionpaq,
						factorconversionpz,
						stockneodata,
						purchgroup,
						idjerarquia,
						addunits,
						secuunits,
						recipeunits,
						factorrecipe,
						addcategory,
						deliverydays,
						tolerancedays,
						estatusstock,
						eq_conversion_costo,
						extracolone,
						extracoltwo,
						extracolthree,
						unitstemporal,
						SAPActualiza,
						depreciacion,
						valor_inicial
					FROM stockmaster
					WHERE stockid = '" . $id . "'";
        
        $resp = $modelo->ejecutarsql($sql);
		$message = "";

		if ($resp == true and !is_array($resp)){
			$success = false;
			$message = _('No existe registros para la busqueda de ' . $id);

		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";

			}else{
				
				$stockmaster = new Stockmaster;
				for($xx = 0; $xx < count($resp); $xx++){
					$stockmaster->setStockid($resp[$xx]['stockid']);
					$stockmaster->setSpes($resp[$xx]['spes']);
					$stockmaster->setCategoryid($resp[$xx]['categoryid']);
					$stockmaster->setDescription($resp[$xx]['description']);
					$stockmaster->setLongdescription($resp[$xx]['longdescription']);
					$stockmaster->setManufacturer($resp[$xx]['manufacturer']);
					$stockmaster->setStockautor($resp[$xx]['stockautor']);
					$stockmaster->setUnits($resp[$xx]['units']);
					$stockmaster->setMbflag($resp[$xx]['mbflag']);
					$stockmaster->setLastcurcostdate($resp[$xx]['lastcurcostdate']);
					$stockmaster->setActualcost($resp[$xx]['actualcost']);
					$stockmaster->setLastcost($resp[$xx]['lastcost']);
					$stockmaster->setMaterialcost($resp[$xx]['materialcost']);
					$stockmaster->setLabourcost($resp[$xx]['labourcost']);
					$stockmaster->setOverheadcost($resp[$xx]['overheadcost']);
					$stockmaster->setLowestlevel($resp[$xx]['lowestlevel']);
					$stockmaster->setDiscontinued($resp[$xx]['discontinued']);
					$stockmaster->setControlled($resp[$xx]['controlled']);
					$stockmaster->setEnviarmasisa($resp[$xx]['enviarmasisa']);
					$stockmaster->setEoq($resp[$xx]['eoq']);
					$stockmaster->setVolume($resp[$xx]['volume']);
					$stockmaster->setKgs($resp[$xx]['kgs']);
					$stockmaster->setBarcode($resp[$xx]['barcode']);
					$stockmaster->setDiscountcategory($resp[$xx]['discountcategory']);
					$stockmaster->setTaxcatid($resp[$xx]['taxcatid']);
					$stockmaster->setTaxcatidret($resp[$xx]['taxcatidret']);
					$stockmaster->setSerialised($resp[$xx]['serialised']);
					$stockmaster->setAppendfile($resp[$xx]['appendfile']);
					$stockmaster->setPerishable($resp[$xx]['perishable']);
					$stockmaster->setDecimalplaces($resp[$xx]['decimalplaces']);
					$stockmaster->setNextserialno($resp[$xx]['nextserialno']);
					$stockmaster->setPansize($resp[$xx]['pansize']);
					$stockmaster->setShrinkfactor($resp[$xx]['shrinkfactor']);
					$stockmaster->setNetweight($resp[$xx]['netweight']);
					$stockmaster->setIdclassproduct($resp[$xx]['idclassproduct']);
					$stockmaster->setStocksupplier($resp[$xx]['stocksupplier']);
					$stockmaster->setSecuritypoint($resp[$xx]['securitypoint']);
					$stockmaster->setIdetapaflujo($resp[$xx]['idetapaflujo']);
					$stockmaster->setOrigencountry($resp[$xx]['OrigenCountry']);
					$stockmaster->setOrigendate($resp[$xx]['OrigenDate']);
					$stockmaster->setStockupdate($resp[$xx]['stockupdate']);
					$stockmaster->setPkg_Type($resp[$xx]['pkg_type']);
					$stockmaster->setIsbn($resp[$xx]['isbn']);
					$stockmaster->setGrade($resp[$xx]['grade']);
					$stockmaster->setSubject($resp[$xx]['subject']);
					$stockmaster->setDeductibleflag($resp[$xx]['deductibleflag']);
					$stockmaster->setU_Typeoperation($resp[$xx]['u_typeoperation']);
					$stockmaster->setTypeoperationdiot($resp[$xx]['typeoperationdiot']);
					$stockmaster->setHeight($resp[$xx]['height']);
					$stockmaster->setWidth($resp[$xx]['width']);
					$stockmaster->setLarge($resp[$xx]['large']);
					$stockmaster->setFichatecnica($resp[$xx]['fichatecnica']);
					$stockmaster->setPercentfactorigi($resp[$xx]['percentfactorigi']);
					$stockmaster->setInpdfgroup($resp[$xx]['inpdfgroup']);
					$stockmaster->setFlagadvance($resp[$xx]['flagadvance']);
					$stockmaster->setEq_Conversion_Factor($resp[$xx]['eq_conversion_factor']);
					$stockmaster->setEq_Stockid($resp[$xx]['eq_stockid']);
					$stockmaster->setFlagcommission($resp[$xx]['flagcommission']);
					$stockmaster->setFijo($resp[$xx]['fijo']);
					$stockmaster->setFecha_Modificacion($resp[$xx]['fecha_modificacion']);
					$stockmaster->setUnitequivalent($resp[$xx]['unitequivalent']);
					$stockmaster->setFactorconversionpaq($resp[$xx]['factorconversionpaq']);
					$stockmaster->setFactorconversionpz($resp[$xx]['factorconversionpz']);
					$stockmaster->setStockneodata($resp[$xx]['stockneodata']);
					$stockmaster->setPurchgroup($resp[$xx]['purchgroup']);
					$stockmaster->setIdjerarquia($resp[$xx]['idjerarquia']);
					$stockmaster->setAddunits($resp[$xx]['addunits']);
					$stockmaster->setSecuunits($resp[$xx]['secuunits']);
					$stockmaster->setRecipeunits($resp[$xx]['recipeunits']);
					$stockmaster->setFactorrecipe($resp[$xx]['factorrecipe']);
					$stockmaster->setAddcategory($resp[$xx]['addcategory']);
					$stockmaster->setDeliverydays($resp[$xx]['deliverydays']);
					$stockmaster->setTolerancedays($resp[$xx]['tolerancedays']);
					$stockmaster->setEstatusstock($resp[$xx]['estatusstock']);
					$stockmaster->setEq_Conversion_Costo($resp[$xx]['eq_conversion_costo']);
					$stockmaster->setExtracolone($resp[$xx]['extracolone']);
					$stockmaster->setExtracoltwo($resp[$xx]['extracoltwo']);
					$stockmaster->setExtracolthree($resp[$xx]['extracolthree']);
					$stockmaster->setUnitstemporal($resp[$xx]['unitstemporal']);
					$stockmaster->setSapactualiza($resp[$xx]['SAPActualiza']);
					$stockmaster->setDepreciacion($resp[$xx]['depreciacion']);
					$stockmaster->setValor_Inicial($resp[$xx]['valor_inicial']);
					
					//$response['data'][] = $stockmaster;
				}
			}
			
		}
		
		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		$response['error']['codeerror'] = $codeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		$response['data']['object'] = (array)$stockmaster;
		
		//header('Content-type: application/json; charset=utf-8');
  		//echo json_encode($response, JSON_FORCE_OBJECT);
  		
  		return $response;

	}

	function Getstockbyid($stockid, $modelo){

		require_once $this->pathprefix . 'model/Stockmastershort.php';

		$UserID = $_SESSION["UserID"];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$sql = "";
				
		$sql = "SELECT s.stockid, 
						s.categoryid, 
						s.description,
						s.units,
						s.mbflag,
						s.taxcatid, 
						c.stockact,
						g.discountglcode,
						g.salesglcode,
						p.glcode,   
						t.taxrate,
						a.taxglcode,
						a.taxglcodePaid
					FROM stockmaster s
						INNER JOIN stockcategory c ON s.categoryid = c.categoryid
						LEFT JOIN salesglpostings g ON c.categoryid = g.stkcat
						LEFT JOIN cogsglpostings p ON c.categoryid = p.stkcat
						LEFT JOIN taxauthrates t ON s.taxcatid = t.taxcatid and taxauthority = 1
						LEFT JOIN taxauthorities a ON t.taxauthority = a.taxid 
					WHERE stockid = '" . $stockid . "'
					LIMIT 1";
        
        $resp = $modelo->ejecutarsql($sql);
		$message = "";

		$stockmastershort = new Stockmastershort;

		if ($resp == true and !is_array($resp)){
			$success = false;
			$message = _('No existe registros para la busqueda de ' . $stockid);

		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";

			}else{
				
				for($xx = 0; $xx < count($resp); $xx++){
					$stockmastershort->setStockid($resp[$xx]['stockid']);
					$stockmastershort->setCategoryid($resp[$xx]['categoryid']);
					$stockmastershort->setDescription($resp[$xx]['description']);
					$stockmastershort->setUnits($resp[$xx]['units']);
					$stockmastershort->setMbflag($resp[$xx]['mbflag']);
					$stockmastershort->setTaxcatid($resp[$xx]['taxcatid']);
					$stockmastershort->setStockact($resp[$xx]['stockact']);
					$stockmastershort->setDiscountglcode($resp[$xx]['discountglcode']);
					$stockmastershort->setSalesglcode($resp[$xx]['salesglcode']);
					$stockmastershort->setGlcode($resp[$xx]['glcode']);
					$stockmastershort->setTaxrate($resp[$xx]['taxrate']);
					$stockmastershort->setTaxglcode($resp[$xx]['taxglcode']);
					$stockmastershort->setTaxglcodePaid($resp[$xx]['taxglcodePaid']);

				}
			}
			
		}
		
		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		$response['error']['codeerror'] = $codeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		//$response['data']['object'] = (array)$stockmaster;
		
		return $stockmastershort;

	}
}
?>