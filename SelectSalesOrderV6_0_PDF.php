<?php
// se envía al servidor 16
/*
Itzel: Se envía al servidor
*/

 /*
	Sofia:
	Sen envia a producción, arreglos de visualización
 */

    include('includes/session.inc');
    include('includes/SQL_CommonFunctions.inc');
    $funcion = 602;
    include ('includes/SecurityFunctions.inc');

    $altapedido = Havepermission ( $_SESSION ['UserID'], 171, $db );
    $paginapedidos = HavepermissionURL ( $_SESSION ['UserID'], 4, $db );

    $paginabusquedapedidos = HavepermissionURL ( $_SESSION ['UserID'], 602, $db );
    $oportunidadprospectoPermiso = Havepermission ( $_SESSION ['UserID'], 2210, $db );
    $oportunidadprospecto = HavepermissionURL ( $_SESSION ['UserID'], 2210, $db );
    $permisouser = Havepermission ( $_SESSION ['UserID'], 860, $db );
    $permisoremision = Havepermission ( $_SESSION ['UserID'], 224, $db );
    $permisocancelar = Havepermission ( $_SESSION ['UserID'], 196, $db );
    $enviaventaperdida = Havepermission ( $_SESSION ['UserID'], 203, $db );
    $cancelarfactura = Havepermission ( $_SESSION ['UserID'], 177, $db );
    $modificartrabajadores = Havepermission ( $_SESSION ['UserID'], 302, $db );
    $modificarvendedores = Havepermission ( $_SESSION ['UserID'], 985, $db );
    $permisoaperturacerrado = Havepermission ( $_SESSION ['UserID'], 217, $db );
    $add_datosfactura = Havepermission ( $_SESSION ['UserID'], 3014, $db );
    $mod_datosfactura = Havepermission ( $_SESSION ['UserID'], 3014, $db );
    $permisoimprimeservicio = Havepermission ( $_SESSION ['UserID'], 546, $db );
    $Exportaexcel = Havepermission ( $_SESSION ['UserID'], 1290, $db );
    $permisoCambiarUsuario = Havepermission ( $_SESSION ['UserID'], 1263, $db );
    $permisodesbloqueapedido = Havepermission ( $_SESSION ['UserID'], 1396, $db );
    $permisoCambiarVehicle = Havepermission ( $_SESSION ['UserID'], 1282, $db ); // agregar
    $permiso_ticket_fiscal = Havepermission ( $_SESSION ['UserID'], 224, $db ); // agregar

    $permisovertodasfacturas = Havepermission ( $_SESSION ['UserID'], 1920, $db ); // permite ver todas las facturas que se generan de un solo pedido.

    //mostrar bombeo y proveedor
    $PerMosBombeoProveedor = Havepermission ( $_SESSION ['UserID'], 1963, $db );

    $permisoLogPartida = Havepermission($_SESSION ['UserID'], 1998, $db); // Ver log pedido

    $permisoEnviarCorreo = Havepermission($_SESSION ['UserID'], 2003, $db); // Enviar todas la facturas seleccionadas

    if (isset ( $_GET ['Quotations'] )) {
        $Quotations = $_GET ['Quotations'];
    }else{
        $Quotations = 1;
    }

    if (isset ( $_GET ['usarEstatus'] )) {
        $usarEstatus = $_GET ['usarEstatus'];
    }

    if (isset ( $_GET ['FolioFiscal'] )) {
        $FolioFiscal = $_GET ['FolioFiscal'];
    }else{
        $FolioFiscal = "";
    }

    if (isset ( $_GET ['fechaini'] )) {
        $fechaini = $_GET ['fechaini'];
    }else{
        $fechaini = "";
    }

    if (isset ( $_GET ['fechafin'] )) {
        $fechafin = $_GET ['fechafin'];
    }else{
        $fechafin = "";
    }

    if (isset ( $_GET ['OrderNumber'] )) {
        $OrderNumber = $_GET ['OrderNumber'];
    }else{
        $OrderNumber = "";
    }

    if (isset ( $_GET ['UnidNeg'] )) {
        $UnidNeg = $_GET ['UnidNeg'];
    }else{
        $UnidNeg = "";
    }

    if (isset ( $_GET ['SalesMan'] )) {
        $SalesMan = $_GET ['SalesMan'];
    }else{
        $SalesMan = "";
    }

    if (isset ( $_GET ['UserName'] )) {
        $UserName = $_GET ['UserName'];
    }else{
        $UserName = "";
    }

    if (isset ( $_GET ['nocliente'] )) {
        $nocliente = $_GET ['nocliente'];
    }else{
        $nocliente = "";
    }

    if (isset ( $_GET ['cliente'] )) {
        $cliente = $_GET ['cliente'];
    }else{
        $cliente = "";
    }

    if (isset ( $_GET ['legalbusiness'] )) {
        $legalbusiness = $_GET ['legalbusiness'];
    }else{
        $legalbusiness = "";
    }

    //$fechaini = rtrim ( $FromYear ) . '-' . rtrim ( $FromMes ) . '-' . rtrim ( $FromDia );
    //$fechafin = rtrim ( $ToYear ) . '-' . add_ceros ( rtrim ( $ToMes ), 2 ) . '-' . add_ceros ( rtrim ( $ToDia ), 1 ) . ' 23:59:59';
    $fechainic = mktime ( 0, 0, 0, rtrim ( $FromMes ), rtrim ( $FromDia ), rtrim ( $FromYear ) );
    $fechafinc = mktime ( 0, 0, 0, rtrim ( $ToMes ), rtrim ( $ToDia ), rtrim ( $ToYear ) );
    $InputError = 0;
    if ($fechainic > $fechafinc) {
        $InputError = 1;
        echo '<div class="alert alert-warning" role="alert">La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha</div>';
    }

    if ($InputError == 0) {

        $vrArrayssts=array();

        $sql = "SELECT *
        FROM  salesstatus
        WHERE statusid IN (".$Quotations.")";

       $rstxt = DB_query ( $sql, $db );
       while($reg =DB_fetch_array ( $rstxt )){
            $salesname = $reg ['statusname'];
            $flagCxC = $reg ['invoice'];
            $flagdateinv = $reg ['flagdateinv'];
            $flagventaperdida = $reg ['flagventaperdida'];
            $vrstatusid= $reg ['statusid'];
            array_push($vrArrayssts, array('salesname'=>$salesname, 'flagCxC'=>$flagCxC, 'flagdateinv'=>$flagdateinv, 'flagventaperdida'=>$flagventaperdida, 'statusid'=>$vrstatusid));
	    }

        //Se cambio por lo anterior, para que quede igual a lo que muestra en la busueda
        // $rstxt = DB_query ( $sql, $db );
        // $reg = DB_fetch_array ( $rstxt );
        // $salesname = $reg ['statusname'];
        // $flagCxC = $reg ['invoice'];
        // $flagdateinv = $reg ['flagdateinv'];
        // $flagventaperdida = $reg ['flagventaperdida'];

        
        if (!isset($salesfield)){
            $salesfield="";
        }
        if ($salesfield == '') {
            $salesname = _ ( 'Todos' );
        }

        $vrArrayFlag=array();
        foreach ($vrArrayssts as $value) {

            if (strlen ( $value['flagdateinv'] ) != 0) {
                array_push($vrArrayFlag, $value['statusid']);
            }else{
                array_push($vrArrayFlag, $value['flagdateinv']);
            }
        }

        // echo "<pre>Prueba: ".print_r($vrArrayssts)."</pre>";

        //Se cambio por lo anterior, para que quede igual a lo que muestra en la busueda
        // if (!isset($salesfield)){
        //     $salesfield="";
        // }
        // if ($salesfield == '') {
        //     $salesname = _ ( 'Todos' );
        // }
        // if (strlen ( $flagdateinv ) == 0) {
        //     $flagdateinv = $Quotations;
        // }

        $vrArraysf=array();
        // Trae fechas por status
        $sql = "SELECT *
                FROM  salesfielddate
                WHERE flagupdate=1 AND flagdate=1
                AND statusid IN ( ". implode(',', $vrArrayFlag). ")
                ORDER BY statusid";
        
        $rstxt = DB_query ( $sql, $db );
        while($reg = DB_fetch_array ( $rstxt )){
            $salesfield = $reg ['salesfield'];
            $vrstatusid = $reg ['statusid'];
            if ($salesfield == '') {
                $salesfield = 'fecha_solicitud';
            }
            array_push($vrArraysf, array($salesfield, $vrstatusid));
        }
        
        //Se cambio por lo anterior, para que quede igual a lo que muestra en la busueda
        // // Trae fechas por status
        // $sql = "SELECT *
        //         FROM  salesfielddate
        //         WHERE flagupdate=1 and flagdate=1
        //         AND statusid='" . $flagdateinv . "'
        //                 order by statusid";

        // $rstxt = DB_query ( $sql, $db );
        // $reg = DB_fetch_array ( $rstxt );
        // $salesfield = $reg ['salesfield'];
        // if ($salesfield == '') {
        //     $salesfield = 'fecha_solicitud';
        // }

        $fecha="";
        $vrauxF=false;
        foreach ($vrArraysf as $value) {
            $fecha =$fecha. " WHEN salesdate." . $value[0] . " IS NOT NULL  THEN  salesdate.".$value[0];
            $vrauxF=true;
        }
        if($vrauxF==true){
            $fecha =" ( CASE ". $fecha ." ELSE '' END ) AS fecha,";
        }
        
        if (isset ( $FolioFiscal ) and strlen ( $FolioFiscal ) > 0) {
            $fecha1 = 'fecha_facturado';
        }else{
            $fecha1 = 'fecha_solicitud';
        }

        //Se cambio por lo anterior, para que quede igual a lo que muestra en la busueda
        // if ($Quotations == - 1 or ! isset ( $usarEstatus )) {
        //     $fecha = "salesdate.fecha_solicitud as fecha,";
        // } elseif (isset ( $usarEstatus )) {
        //     echo "AQUI ENTRA";
        //     $fecha = "salesdate." . $salesfield . " as fecha,";
        // }
        
        // if (isset ( $FolioFiscal ) and strlen ( $FolioFiscal ) > 0) {
        //     $fecha1 = 'fecha_facturado';
        // }else{
        //     $fecha1 = 'fecha_solicitud';
        // }

        $SQL = "SELECT distinct salesorders.orderno,
                    debtortrans.folio,
                    debtorsmaster.name, 
                    salesorders.UserRegister,
                    salesman.salesmanname,
                    custbranch.brname,
                    salesorders.customerref,
                    salesorders.orddate,
                    salesorders.deliverydate,
                    salesorders.currcode,
                    salesorders.deliverto,
                    salesorders.quotation as estatus,
                    debtortrans.id,
                    salesorders.printedpackingslip,
                    paymentterms.type,
                    salesorders.debtorno,
                    tagdescription,
                    salesorders.nopedido,
                    salesorders.tagref,
                    salesorders.idprospect,
                    debtortrans.noremisionf,
                    salesstatus.statusname,
                    salesstatus.invoice,
                    " . $fecha . "
                    tags.tagdescription,
                    case when debtortrans.folio is null then '' else folio end as folio,
                    paymentterms.type as tipopago,
                    openfunctionid,
                    salesstatus.invoice,
                    salesstatus.flagopen,
                    salesstatus.templateid,
                    salesstatus.templateidadvance,
                    salesorders.nopedido,
                    salesorders.placa,
                    case when debtortrans.folio is null then (SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2))) + (salesorders.taxtotal +salesorders.totaltaxret) else debtortrans.ovamount+debtortrans.ovgst end AS ordervalue,
                    debtortrans.type,
                    debtortrans.transno ,
                    case when debtortrans.id is null then 0 else  debtortrans.id end as idfactura,
                    tags.legalid,
                    case when debtortrans.id is null then-1 else debtortrans.prd end as prd,
                    cancelfunctionid,
                    cancelextrafunctionid,
                    flagcancel,
                    flagelectronic,
                    legalid,
                    paymentterms.generateCreditNote,
                    debtortrans.transno,
                    tags.typeinvoice,
                    salesorders.discountcard,
                    debtortrans.id,
                    custbranch.email
                FROM salesorders 
                    LEFT JOIN debtortrans ON salesorders.orderno = debtortrans.order_ AND debtortrans.type in (10,110,111,119,410,66,125)
                    LEFT JOIN salesstatus ON salesstatus.statusid=salesorders.quotation
                    LEFT JOIN (
                            SELECT * 
                            FROM salesdate 
                            WHERE ((salesdate.".$fecha1.">= '" . $fechaini . "' and salesdate.".$fecha1."<='" . $fechafin . "')";
            
            foreach ($vrArrayssts as $value) {

                if ($value['statusid'] == 0) {
                    $SQL  = $SQL . 	" OR (salesdate.fecha_cerrado >= '" . $fechaini . "' and salesdate.fecha_cerrado <= '" . $fechafin . "')";
                }
                else if ($value['statusid'] == 1) {
                    $SQL  = $SQL . 	" OR (salesdate.fecha_cotizacion >= '" . $fechaini . "' and salesdate.fecha_cotizacion <= '" . $fechafin . "')";
                }
                else if ($value['statusid'] == 2) {
                    $SQL  = $SQL . 	" OR (salesdate.fecha_abierto >= '" . $fechaini . "' and salesdate.fecha_abierto <= '" . $fechafin . "')";
                }
                else if ($value['statusid'] == 3) {
                    $SQL  = $SQL . 	" OR (salesdate.fecha_cancelado >= '" . $fechaini . "' and salesdate.fecha_cancelado <= '" . $fechafin . "')";
                }
                else if ($value['statusid'] == 4) {
                    $SQL  = $SQL . 	" OR (salesdate.fecha_facturado >= '" . $fechaini . "' and salesdate.fecha_facturado <= '" . $fechafin . "')";
                }
                else if ($value['statusid'] == 5) {
                    $SQL  = $SQL . 	" OR (salesdate.fecha_remisionado >= '" . $fechaini . "' and salesdate.fecha_remisionado <= '" . $fechafin . "')";
                }
                else if ($value['statusid'] == 6) {
                    $SQL  = $SQL . 	" OR (salesdate.fecha_remisionado >= '" . $fechaini . "' and salesdate.fecha_remisionado <= '" . $fechafin . "')";

                }else if ($value['statusid'] == 7 ) {
                    $SQL  = $SQL .  " OR (salesdate.fecha_solicitud >= '" . $fechaini . "' and salesdate.fecha_solicitud <= '" . $fechafin . "') ";
                }else if ($value['statusid'] == 13 ) {
                    $SQL  = $SQL .  " OR (salesdate.fecha_remisionado >= '" . $fechaini . "' and salesdate.fecha_remisionado <= '" . $fechafin . "') ";
                }else{
                    $SQL  = $SQL .  " OR (salesdate.fecha_cotizacion >= '" . $fechaini . "' and salesdate.fecha_cotizacion <= '" . $fechafin . "') ";
                }
            }

            $SQL  = $SQL . 	")";

        // if (isset ($FolioFiscal ) and strlen ( $FolioFiscal ) > 0) {
        //     $SQL  = $SQL .  " OR (salesdate.fecha_remisionado >= '" . $fechaini . "' and salesdate.fecha_remisionado <= '" . $fechafin . "'))";
        // }else{
        //     $SQL  = $SQL .  ")";
        // }

        /*      SE MODIFICA EL WHERE DE TAL FORMA QUE SOLO TRAIGA LAS VENTAS DE LAS UNIDADES DE NEGOCIO A           LAS QUE ESTE ASOCIADO EL USUARIO, INDEPENDIENTEMENTE DE LOS FLITROS..  SQL ANTERIOR  
                                        04/04/2017*/                
        /*
        $SQL  = $SQL . " GROUP BY orderno
                        ) as salesdate ON salesdate.orderno=salesorders.orderno
                    LEFT JOIN salesorderdetails on salesorders.orderno = salesorderdetails.orderno
                    LEFT JOIN locations on salesorderdetails.fromstkloc=locations.loccode
                    LEFT JOIN salesman ON salesman.salesmancode = salesorders.salesman
                    LEFT JOIN tags on tags.tagref=salesorders.tagref
                    LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
                    LEFT JOIN custbranch ON  debtorsmaster.debtorno = custbranch.debtorno AND salesorders.branchcode = custbranch.branchcode
                    LEFT JOIN paymentterms ON paymentterms.termsindicator=salesorders.paytermsindicator
                    LEFT JOIN sec_loccxusser ON salesorderdetails.fromstkloc=sec_loccxusser.loccode and  sec_loccxusser.userid ='" . $_SESSION ['UserID'] . "'
                                AND salesorders.tagref in (select tagref from sec_unegsxuser where userid ='" . $_SESSION ['UserID'] . "')
                WHERE   1=1 ";
        */
        // $SQL  = $SQL . " GROUP BY orderno
        //                 ) as salesdate ON salesdate.orderno=salesorders.orderno
        //             LEFT JOIN salesorderdetails on salesorders.orderno = salesorderdetails.orderno
        //             LEFT JOIN locations on salesorderdetails.fromstkloc=locations.loccode
        //             LEFT JOIN salesman ON salesman.salesmancode = salesorders.salesman
        //             LEFT JOIN tags on tags.tagref=salesorders.tagref
        //             LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
        //             LEFT JOIN custbranch ON  debtorsmaster.debtorno = custbranch.debtorno AND salesorders.branchcode = custbranch.branchcode
        //             LEFT JOIN paymentterms ON paymentterms.termsindicator=salesorders.paytermsindicator
        //             LEFT JOIN sec_loccxusser ON salesorderdetails.fromstkloc=sec_loccxusser.loccode and  sec_loccxusser.userid ='" . $_SESSION ['UserID'] . "'
        //                         AND salesorders.tagref in (select tagref from sec_unegsxuser where userid ='" . $_SESSION ['UserID'] . "')
        //         WHERE   1=1 AND salesorders.tagref in (select tagref from sec_unegsxuser where userid ='" . $_SESSION ['UserID'] . "')";
        
        //Se quito lo anterior, para que quede igual a lo que muestra en la busueda
        $SQL  = $SQL . " GROUP BY orderno
					) as salesdate ON salesdate.orderno=salesorders.orderno
				LEFT JOIN salesorderdetails on salesorders.orderno = salesorderdetails.orderno
				LEFT JOIN locations on salesorderdetails.fromstkloc=locations.loccode
				LEFT JOIN salesman ON salesman.salesmancode = salesorders.salesman
				LEFT JOIN tags on tags.tagref=salesorders.ln_tagref_pase
				LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
				LEFT JOIN custbranch ON  debtorsmaster.debtorno = custbranch.debtorno AND salesorders.branchcode = custbranch.branchcode
				LEFT JOIN paymentterms ON paymentterms.termsindicator=salesorders.paytermsindicator
				LEFT JOIN sec_loccxusser ON salesorderdetails.fromstkloc=sec_loccxusser.loccode and  sec_loccxusser.userid ='" . $_SESSION ['UserID'] . "'							
            WHERE   1=1 AND salesorders.ln_tagref_pase in (select tagref from sec_unegsxuser where userid ='" . $_SESSION ['UserID'] . "')";
            
        // $validafecha = true;
        // //busqueda por folio fiscal CGA 092016
        // if (isset ( $FolioFiscal ) and strlen ( $FolioFiscal ) > 0) {
        //     $SQL = $SQL . " AND (debtortrans.folio like '%" . $FolioFiscal . "%'
        //             OR replace(debtortrans.folio,'|','') like '%" . $FolioFiscal . "%')";
        //     $validafecha = false;
        // }
        
        // if (strlen ( $OrderNumber ) > 0) {
        //     $SQL = $SQL . " AND salesorders.orderno=" . $OrderNumber;
        //     $validafecha = false;
        // }

        //Se quito lo anterior, para que quede igual a lo que muestra en la busueda
        $validafecha = true;
        //busqueda por folio fiscal CGA 092016
        if (isset ( $FolioFiscal ) and strlen ( $FolioFiscal ) > 0) {
            $SQL = $SQL . " AND (debtortrans.folio like '%" . $_REQUEST ['FolioFiscal'] . "%'
                    OR replace(debtortrans.folio,'|','') like '%" . $_REQUEST ['FolioFiscal'] . "%')";
            $validafecha = false;
        }
        
        if (strlen ( $OrderNumber ) > 0) {
            $SQL = $SQL . " AND salesorders.orderno=" . $_REQUEST ['OrderNumber'];
            $validafecha = false;
        }
        
        /*
         if (isset($_POST['FolioFiscal']) and strlen($_POST['FolioFiscal'])>0){
             $SQL = $SQL." AND replace('|','',debtortrans.folio) ='" .$_REQUEST['FolioFiscal'] ."'";
             $validafecha=false;
         }*/
        
        // if ($Quotations == - 1 or ! isset ( $usarEstatus )) {
        //     if ($validafecha == true) {
        //         $SQL .= " AND salesdate.fecha_solicitud>= '" . $fechaini . "' and salesdate.fecha_solicitud<='" . $fechafin . "'";
        //     } else {
        //                 if (isset ( $FolioFiscal ) and strlen ( $FolioFiscal ) > 0) {
                
        //                 }else{
        //                     $SQL = $SQL . " AND salesorders.orddate>= '" . $fechaini . "' and salesorders.orddate<='" . $fechafin . "'";
        //                 }
        //     }
        // } elseif (isset ( $usarEstatus )) {
        //     if ($validafecha == true) {
        //         $SQL .= " AND salesdate." . $salesfield . ">= '" . $fechaini . "' and salesdate." . $salesfield . "<='" . $fechafin . "'";
        //     }
            // if (strlen ( $OrderNumber ) == 0) {
                // $SQL .= " AND salesorders.quotation=" . $Quotations;
            // }
        // }

        $estatus = "-1";
        foreach ($vrArrayssts as $value) {
            $estatus .= ",".$value['statusid']; 
        }
        
        $SQL  = $SQL." AND (";
	    foreach ($vrArrayssts as $value) {
			if ($value['statusid'] == 0) {
				$SQL  = $SQL . 	" (salesdate.fecha_cerrado >= '" . $fechaini . "' and salesdate.fecha_cerrado <= '" . $fechafin . "') OR ";
			}	
		
			else if ($value['statusid'] == 1) {
			$SQL  = $SQL . 	" (salesdate.fecha_cotizacion >= '" . $fechaini . "' and salesdate.fecha_cotizacion <= '" . $fechafin . "') OR ";

			} 
			else if ($value['statusid'] == 2) {
				$SQL  = $SQL . 	" (salesdate.fecha_abierto >= '" . $fechaini . "' and salesdate.fecha_abierto <= '" . $fechafin . "') OR ";
			}
			else if ($value['statusid'] == 3) {
				$SQL  = $SQL . 	" (salesdate.fecha_cancelado >= '" . $fechaini . "' and salesdate.fecha_cancelado <= '" . $fechafin . "') OR ";
			}
			else if ($value['statusid'] == 4) {
				$SQL  = $SQL . 	" (salesdate.fecha_facturado >= '" . $fechaini . "' and salesdate.fecha_facturado <= '" . $fechafin . "') OR ";
			}
			else if ($value['statusid'] == 5) {
				$SQL  = $SQL . 	" (salesdate.fecha_remisionado >= '" . $fechaini . "' and salesdate.fecha_remisionado <= '" . $fechafin . "') OR ";
			}
			else if ($value['statusid'] == 6 ) {
				$SQL  = $SQL . 	" (salesdate.fecha_remisionado >= '" . $fechaini . "' and salesdate.fecha_remisionado <= '" . $fechafin . "') OR ";

			}else if ($value['statusid'] == 7 ) {
                $SQL  = $SQL .  " (salesdate.fecha_solicitud >= '" . $fechaini . "' and salesdate.fecha_solicitud <= '" . $fechafin . "') OR ";
            }else if ($value['statusid'] == 13 ) {
                $SQL  = $SQL .  " (salesdate.fecha_remisionado >= '" . $fechaini . "' and salesdate.fecha_remisionado <= '" . $fechafin . "') OR ";
            }else{
                $SQL  = $SQL .  " (salesdate.fecha_cotizacion >= '" . $fechaini . "' and salesdate.fecha_cotizacion <= '" . $fechafin . "') OR ";
            }	
		}

		$res = substr($SQL, -4); 
		if ($res == " OR ") {
			$SQL = substr($SQL, 0, -4);
		}

		$SQL  = $SQL . 	") ";

        if ($estatus <> '-1' ) {
                $SQL .= " AND salesorders.quotation IN (".$estatus.")";
        }

        if ($UnidNeg > 0) {
            $SQL = $SQL . " AND salesorders.ln_tagref_pase = '" . $UnidNeg . "'";
        }
        
        if (strlen ( $SalesMan ) > 0) {
            $SQL = $SQL . " AND salesorders.salesman = '" . $SalesMan . "'";
        }
        
        if (strlen ( $UserName ) > 0) {
            $SQL = $SQL . " AND salesorders.UserRegister = '" . $UserName . "'";
        }
        
        if (isset ( $nocliente ) and strlen ( $nocliente ) > 0) {
            $SQL = $SQL . " AND salesorders.debtorno like '%" . $nocliente . "%'";
        }
        if (isset ( $cliente ) and strlen ( $cliente ) > 0) {
            $SQL .= " AND custbranch.brname like '%" . $cliente . "%'";
        }
        
        if (isset ( $legalbusiness ) and $legalbusiness != 0) {
            $SQL .= " AND tags.legalid = '" . $legalbusiness . "'";
        }
        $SQL = $SQL . "
                GROUP BY salesorders.orderno,                                
                    debtorsmaster.name,
                    custbranch.brname,
                    salesorders.customerref,
                    salesorders.orddate,
                    salesorders.deliverydate,
                    salesorders.deliverto, 
                    salesorders.printedpackingslip,
                    salesorders.nopedido";
        if($permisovertodasfacturas){
            $SQL = $SQL . ", debtortrans.folio";
        }

        $SQL = $SQL . " ORDER BY salesorders.orderno, tagdescription ";

        // if (strlen ( $UnidNeg ) > 0) {
        //     $SQL = $SQL . " AND salesorders.tagref = '" . $UnidNeg . "'";
        // }
        
        // if (strlen ( $SalesMan ) > 0) {
        //     $SQL = $SQL . " AND salesorders.salesman = '" . $SalesMan . "'";
        // }
        
        // if (strlen ( $UserName ) > 0) {
        //     $SQL = $SQL . " AND salesorders.UserRegister = '" . $UserName . "'";
        // }
        
        // if (isset ( $nocliente ) and strlen ( $nocliente ) > 0) {
        //     $SQL = $SQL . " AND salesorders.debtorno like '%" . $nocliente . "%'";
        // }
        // if (isset ( $cliente ) and strlen ( $cliente ) > 0) {
        //     $SQL .= " AND custbranch.brname like '%" . $cliente . "%'";
        // }
        
        // if (isset ( $legalbusiness ) and $legalbusiness != 0) {
        //     $SQL .= " AND tags.legalid = '" . $legalbusiness . "'";
        // }
        // $SQL = $SQL . "
        //         GROUP BY salesorders.orderno,                                
        //             debtorsmaster.name,
        //             custbranch.brname,
        //             salesorders.customerref,
        //             salesorders.orddate,
        //             salesorders.deliverydate,
        //             salesorders.deliverto, 
        //             salesorders.printedpackingslip,
        //             salesorders.nopedido";
        // if($permisovertodasfacturas){
        //     $SQL = $SQL . ", debtortrans.folio";
        // }

        if($_SESSION['UserID'] == 'desarrollo'){
            // echo '<pre>'.$SQL.'</pre>';
        }

        $SalesOrdersResult = DB_query($SQL, $db);

        if (db_num_rows ( $SalesOrdersResult ) > 0) {
            $htmlPDF = "";

            $htmlPDF .= '<table cellspacing=0 border=1 align="center" cellpadding=0 colspan=7 WIDTH="100%">';
            
            if($permiso_ticket_fiscal==1){
                //$liga_TIcket = "<th class='titulos_principales' nowrap>" . _ ( 'Ticket Fiscal' ) . "</th>";
            }
            
            $tableheader = "<tr>";

            if (isset( $_POST['SendEmailPedido'] )) { //Enviar por correo el pedido
                //$tableheader = $tableheader . "<th class='titulos_principales' nowrap>Envio <br> Email</th>";
            }

            if ($permisoLogPartida == 1) { // Ver log pedido
                //$tableheader = $tableheader . "<th class='titulos_principales' nowrap>Log <br> Pedido</th>";
            }

            //$tableheader = $tableheader . "<th class='titulos_principales' nowrap>" . _ ( 'Sel' ) . "</th>";
            $tableheader = $tableheader . "
                            <th class='titulos_principales' nowrap>" . _ ( 'No' ) . "</th>
                            <th class='titulos_principales' nowrap>" . _ ( 'Fecha' ) . "</th>
                            <th class='titulos_principales' nowrap>" . _ ( 'Unidad<br>Negocio' ) . "</th>
                            <th class='titulos_principales' nowrap>" . _ ( 'Folio' ) . "</th>";
            //$tableheader = $tableheader . "<th class='titulos_principales' nowrap>" . _ ( 'Imprimir' ) . "</th>";
            $tableheader = $tableheader . " ".$liga_TIcket."
                            <th class='titulos_principales' nowrap>" . _ ( 'Termino<br>Pago' ) . "</th>
                            <th class='titulos_principales' nowrap>" . _ ( 'Cliente / Suc.' ) . "</th>
                            <th class='titulos_principales' nowrap>" . _ ( 'O.C. Cliente' ) . " #</th>";
            if ($_SESSION ['LabelText1'] != ""){
                if ($_SESSION ['LabelText1'] == "No proveedor") {
                    $tableheader = $tableheader . "<th class='titulos_principales' nowrap>"._(utf8_decode("Nº Proveedor (Addenda)"))."</th>";
                }else{
                    $tableheader = $tableheader . "<th class='titulos_principales' nowrap>" . $_SESSION ['LabelText1'] . "</th>";
                }
                
            }else{
                $tableheader = $tableheader . "<th class='titulos_principales' nowrap>" . _('Tarjeta<br>Descuento') . "</th>";
            }

            if ($PerMosBombeoProveedor == 1) {
                $tableheader = $tableheader."<th class='titulos_principales' nowrap>"._ ( 'Bombeo' )."</th>
                    <th class='titulos_principales' nowrap>" . _ ( 'Proveedor' ) . "</th>";
            }

            $tableheader = $tableheader . "<th class='titulos_principales' nowrap>" . _ ( 'Usuario' ) . "</th>
                            <th class='titulos_principales' nowrap>" . _ ( 'Vendedor' ) . "</th>
                            <th class='titulos_principales' nowrap>" . _ ( 'Moneda' ) . "</th>";
            //$tableheader = $tableheader . "<th class='titulos_principales' nowrap>" . _ ( 'Pagares' ) . "</th>";
            $tableheader = $tableheader . "<th class='titulos_principales' nowrap>" . _ ( 'Total' ) . "</th>";
            
            // if ($_SESSION ['DatabaseName'] == "gruposervillantas_DES" || $_SESSION ['DatabaseName'] == "gruposervillantas"){
            $tableheader = $tableheader . "<th class='titulos_principales'>" . _ ( 'Estatus' ) . "</th>";
            // }
            
            //$tableheader = $tableheader . "<th nowrap class='titulos_principales'>" . _ ( 'VHO' ) . "</th>";
            
            if ($_SESSION ['ShowProspect'] == 1 and $oportunidadprospectoPermiso == 1) {
                //$tableheader = $tableheader . "<th class='titulos_principales'>" . _ ( 'Oportunidad' ) . "</th>";
            }
            if ($permisocancelar == 1) {
                //$tableheader = $tableheader . "<th class='titulos_principales'>" . _ ( 'Cancelar' ) . "</th>";
            }
            if ($flagventaperdida == 1 and $enviaventaperdida == 1) {
                //$tableheader = $tableheader . "<th class='titulos_principales'>" . _ ( 'Venta <br> Perdida' ) . "</th>";
            }
            if ($modificarvendedores == 1) {
                //$tableheader = $tableheader . "<th class='titulos_principales'>" . _ ( 'Mod<br>Vend' ) . "</th>";
            }
            if ($modificartrabajadores == 1) {
                //$tableheader = $tableheader . "<th class='titulos_principales'>" . _ ( 'Mod<br>Trab' ) . "</th>";
            }
            
            if ($permisoCambiarUsuario == 1) {
                //$tableheader = $tableheader . "<th class='titulos_principales'>" . _ ( 'Mod<br>Usua' ) . "</th>";
            }
            
            if ($Exportaexcel == 1) {
                //$tableheader = $tableheader . "<th class='titulos_principales'>" . _ ( 'Exporta<br>Excel' ) . "</th>";
            }
            
            if ($add_datosfactura == 1) {
                //$tableheader = $tableheader . "<th colspan=3 class='titulos_principales'>" . _ ( 'Mod<br>Inf Factura' ) . "</th>";
            }
            
            if ($permisoimprimeservicio == 1) {
                //$tableheader = $tableheader . "<th class='titulos_principales'>" . _ ( 'Orden Servicio' ) . "</th>";
            }
            
            if ($permisodesbloqueapedido == 1) {
                //$tableheader = $tableheader . "<th class='titulos_principales'>" . _ ( 'Desbloquear' ) . "</th>";
            }
            
            $tableheader = $tableheader . '</tr>';
            $htmlPDF .= $tableheader;
            $j = 1;
            $k = 0; // row colour counter
            $montoTotalUSD = 0;
            $montoTotal = 0;
            $indextable = 0;
            $style = "";
            while ( $myrow = DB_fetch_array ( $SalesOrdersResult ) ) {
                if ($indextable == 0) {
                    $htmlPDF .= '<tr bgcolor="eeeeee">';
                    $indextable = 1;
                } else {
                    $htmlPDF .= '<tr bgcolor="ffffff">';
                    $indextable = 0;
                }

                if (isset( $_POST['SendEmailPedido'] )) { //Enviar por correo el pedido

                    if ($myrow ['estatus'] == 1) {
                        //Enviar cotizacion
                        //$htmlPDF .= "<td class='numero_normal'>--</td>";
                    }else{
                        //Funcion para envio del correo
                        $envio = SendMailFactura($myrow ['id'], $myrow ['email'], $$myrow ['debtorno'], 0, $db);
                        if ($envio['success'] == 1) {
                            //$htmlPDF .= "<td class='numero_normal'>Enviado</td>";
                        }else{
                            //$htmlPDF .= "<td class='numero_normal'>Error Envio</td>";
                        }
                    }
                }

                if ($permisoLogPartida == 1) { // Ver log pedido
                    //$htmlPDF .= "<td class='numero_normal'><button type='button' class='btn btn-xs btn-primary' onclick='mostrar_log(".$myrow ['orderno'].")'><span class='glyphicon glyphicon-plus'></span></button>";

                    //Crear contenido tabla a mostrar log
                    $SQLLog = "SELECT salesorderdetails_log.orderno, www_users.realname, salesorderdetails_log.actualdispatchdate, salesorderdetails_log.stkcode, locations.locationname, 
                            salesorderdetails_log.fromstkloc, salesorderdetails_log.qtyinvoiced, salesorderdetails_log.unitprice, salesorderdetails_log.quantity, 
                            salesorderdetails_log.discountpercent, salesorderdetails_log.discountpercent1, salesorderdetails_log.discountpercent2,
                            salesorderdetails_log.movimiento
                            FROM salesorderdetails_log
                            LEFT JOIN locations ON locations.loccode = salesorderdetails_log.fromstkloc
                            LEFT JOIN www_users ON www_users.userid = salesorderdetails_log.userid
                            WHERE orderno = '".$myrow ['orderno']."'
                            GROUP BY salesorderdetails_log.orderlineno, salesorderdetails_log.movimiento, salesorderdetails_log.stkcode,
                            salesorderdetails_log.unitprice, salesorderdetails_log.quantity, salesorderdetails_log.discountpercent, 
                            salesorderdetails_log.discountpercent1, salesorderdetails_log.discountpercent2
                            ORDER BY actualdispatchdate";
                    $ErrMsg = "No se obtuvieron los registros del log del pedido";
                    $ResultLog = DB_query ( $SQLLog, $db, $ErrMsg, '' );

                    $contenidoTabla = "";
                    $styleTabla = "style='text-align: center;'";
                    $num = 1;
                    if (db_num_rows ( $ResultLog ) > 0) {
                        while ( $rowLog = DB_fetch_array ( $ResultLog ) ) {
                            $contenidoTabla = $contenidoTabla . "<tr><td ".$styleTabla.">".$num."</td><td ".$styleTabla.">".$rowLog['realname']."</td><td ".$styleTabla.">".$rowLog['movimiento']."</td><td ".$styleTabla.">".$rowLog['actualdispatchdate']."</td><td ".$styleTabla.">".$rowLog['stkcode']."</td><td ".$styleTabla.">".$rowLog['locationname']."</td><td ".$styleTabla.">$ ".$rowLog['unitprice']."</td><td ".$styleTabla.">".$rowLog['quantity']."</td><td ".$styleTabla.">".($rowLog['discountpercent'] * 100)."%</td><td ".$styleTabla.">".($rowLog['discountpercent1'] * 100)."%</td><td ".$styleTabla.">".($rowLog['discountpercent2'] * 100)."%</td></tr>";
                            $num ++;
                        }
                    }else{
                        $contenidoTabla = $contenidoTabla . "<tr><td>".$num."</td><td>Sin</td><td>Informacion</td><td>Para</td><td>Mostrar</td><td></td><td></td></tr>";
                    }

                    //$htmlPDF .= "<textarea rows='1' name='txtContenidoTabla_".$myrow ['orderno']."' id='txtContenidoTabla_".$myrow ['orderno']."' style='display: none;'>".$contenidoTabla."</textarea>";
                    //$htmlPDF .= "</td>";
                }
                
                //$htmlPDF .= '<td class="numero_normal"><input type="checkbox" name="selectedorders[]" value="' . $myrow ['orderno'] . '"></td>';

                $ModifyPage = $rootpath . "/" . $paginapedidos . "?" . SID . '&ModifyOrderNumber=' . $myrow ['orderno'];
                // si tiene permiso para abrir pedido y el pedido aun cuenta con atributo de modificacion
                if (Havepermission ( $_SESSION ['UserID'], $myrow ['openfunctionid'], $db ) == 1 and $myrow ['flagopen'] == 1) {
                    //$htmlPDF .= '<td class="numero_normal"><a href=' . $ModifyPage . '> ' . $myrow ['orderno'] . '</a></td>';
                } else {
                    //$htmlPDF .= '<td class="numero_normal">' . $myrow ['orderno'] . '</td>';
                }

                $htmlPDF .= '<td class="numero_normal">' . $myrow ['orderno'] . '</td>';
                $htmlPDF .= '<td class="numero_normal" nowrap>' . $myrow ['fecha'] . '</td>';
                $htmlPDF .= '<td class="texto_normal2">' . $myrow ['tagdescription'] . '</td>';
                $tagref = $myrow ['tagref'];

                if (($myrow ['idfactura']) > 0) {
                    // $email_link ="<a target='_blank' href='SendEmail.php?tagref=$tagref&transno=".$myrow['orderno']."&debtorno=".$myrow['debtorno']."'><img src='part_pics/Mail-Forward.png' border=0></a>";
                    $SendInvoiceByMailFile = "SendInvoiceByMail.php";
                    $EnvioXML = $rootpath . '/' . $SendInvoiceByMailFile . '?id=' . $myrow ['idfactura'];
                    $EnvioXML = "&nbsp;&nbsp;&nbsp;<a target='_blank'  href=" . $EnvioXML . "><img src='part_pics/Mail-Forward.png' alt='Enviar x Mail' border=0></a>";
                    if($_SESSION['ImpTiketPV']==1 and $myrow ['type']==125){
                                        $linkfactura = 'PDFSalesTicket.php';
                                        $linkfactura = $rootpath . '/' . $linkfactura . '?tipodocto=1&OrderNo=' . $myrow ['orderno'] . '&TransNo=' . $myrow ['transno'] . '&Type=' . $myrow ['type'] . '&Tagref=' . $tagref;
                                   
                                    }else{
                    $linkfactura = 'PDFInvoice.php';
                    $linkfactura = $rootpath . '/' . $linkfactura . '?tipodocto=1&OrderNo=' . $myrow ['orderno'] . '&TransNo=' . $myrow ['transno'] . '&Type=' . $myrow ['type'] . '&tagref=' . $tagref;
                                    }
                    //$htmlPDF .= '<td class="numero_normal" nowrap ><a target="_blank"  href=' . $linkfactura . '><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir' ) . '" alt="">' . $myrow ['folio'] . '</a> ' . $EnvioXML . '</td>';
                } else {
                    $email_link = "<a target='_blank' href='SendEmailV2_0.php?tagref=$tagref&transno=" . $myrow ['orderno'] . "&debtorno=" . $myrow ['debtorno'] . "'><img src='part_pics/Mail-Forward.png' border=0></a>";
                    
                    //$htmlPDF .= '<td class="numero_normal">' . $myrow ['folio'] . ' ' . $email_link . '</td>';
                }
                $htmlPDF .= '<td class="numero_normal">' . $myrow ['folio'] . '</td>';
                // Links de impresion////

                //echo "<br>ENTRA: ";
                $urlcotizacion = HavepermissionURLV2($_SESSION['UserID'], '1884', $db);
                //echo "<br>ENTRA2 ";
                if ($urlcotizacion == ""){
                    $urlcotizacion = 'PDFCotizacionTemplateV3.php';
                }

                $liga = $urlcotizacion . '?tipodocto=' . $myrow ['templateid'] . '&';
                $liga2 = $urlcotizacion . '?tipodocto=' . $myrow ['templateidadvance'] . '&';
                
                if ($_SESSION ['TypeQuotation'] == 0) {
                    $PrintQuotation = $rootpath . '/' . $liga . '&' . SID . 'PrintPDF=Yes&TransNo=' . $myrow ['orderno'] . '&Tagref=' . $myrow ['tagref'] . '&legalid=' . $myrow ['legalid'];
                    $PrintDispatchNote = $rootpath . '/' . $liga2 . '&' . SID . 'PrintPDF=Yes&TransNo=' . $myrow ['orderno'] . '&Tagref=' . $myrow ['tagref'] . '&legalid=' . $myrow ['legalid'];
                } else {
                    $liga = GetUrlToPrint2 ( $tagref, 10, $db );
                    $PrintQuotation = $rootpath . '/' . $liga . '&' . SID . 'PrintPDF=Yes&TransNo=' . $myrow ['orderno'] . '&Tagref=' . $myrow ['tagref'] . '&legalid=' . $myrow ['legalid'];
                    $PrintDispatchNote = $rootpath . '/' . $liga . '&' . SID . 'PrintPDF=Yes&TransNo=' . $myrow ['orderno'] . '&Tagref=' . $myrow ['tagref'] . '&legalid=' . $myrow ['legalid'];
                }
                
                if ($myrow ['templateid'] == $myrow ['templateidadvance']) {
                    /*$htmlPDF .= '<td class="numero_normal"><a target="_blank"  href=' . $PrintQuotation . '><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir' ) . '" alt=""></a>' . _ ( 'Imprimir' ) . '</td>';*/
                } else {
                    // if ($_SESSION ['DatabaseName'] <> "erpmservice_DIST" AND $_SESSION ['DatabaseName'] <> "erpmservice_DES" AND $_SESSION ['DatabaseName'] <> "erpmservice" AND $_SESSION ['DatabaseName'] <> "erpmservice_CAPA") {
                    
                    /*$htmlPDF .= '<td><a target="_blank" href=' . $PrintQuotation . '><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir' ) . '" alt="">' . _ ( 'Imprimir (Simple)' ) . '</a>
                            <br><a target="_blank" href=' . $PrintDispatchNote . '><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir' ) . '" alt="">' . _ ( 'Imprimir' ) . '</a></td>';*/
                    /*
                     * }
                     * else{
                     * $liga2 = 'PDFRemisionTemplate.php?';
                     *
                     * $PrintQuotation = $rootpath . '/' . $liga2 . '&' . SID . 'PrintPDF=Yes&OrderNo=' . $myrow['orderno']. '&Tagref=' . $myrow['tagref'] . '&TransNo=' . $InvoiceNo . '&Type=119' ;
                     *
                     * echo '<td><a target="_blank" href='.$PrintQuotation.'><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">'._('Imprimir (Simple)').'</a>
                     * <br><a target="_blank" href='.$PrintDispatchNote.'><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">'._('Imprimir').'</a></td>';
                     * }
                     */
                }
                if($permiso_ticket_fiscal==1){
                    $htmlPDF .= '<td class="numero_normal"><a  target="_blank" href="PDFInvoiceticketV2_0.php?PrintPDF=Yes&OrderNo=' . $myrow ['orderno'] . '&TransNo=' . $myrow ['transno'] . '&Type=' .  $myrow ['type']  . '&Tagref=' . $myrow ['tagref'] . '">' . _ ( 'Ticket Fiscal' ) . ' (' . _ ( 'Termico' ) . ')' . '<br><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir Ticket Fiscal' ) . '" alt=""></a></td>';
                }
            
                            //
                $htmlPDF .= '<td class="numero_normal">' . $myrow ['tipopago'] . '</td>';

                $dato_columna_cliente=$myrow ['debtorno']."-".$myrow ['name'] ;
                if($myrow ['brname']!="" && $myrow ['brname']!=$myrow ['name']){
                    $dato_columna_cliente.= ' / ' . $myrow ['brname'];
                }
                $htmlPDF .= '<td class="texto_normal2">' .  $dato_columna_cliente . '</td>';
                $htmlPDF .= '<td class="numero_normal">' . $myrow ['nopedido'] . '</td>';

                if($_SESSION ['LabelText1'] != '' and (Havepermission ( $_SESSION ['UserID'], 1612, $db ) == 1)  && $_SESSION ['LabelText1'] != 'Placas' ){
                    //$htmlPDF .= '<td class="numero_normal"><input type="text" name=trm'.$myrow ['orderno'] .' value="'. $myrow ['discountcard'] .'"></td>';
                    $htmlPDF .= '<td class="numero_normal">' . $myrow ['discountcard'] . '</td>';
                }elseif ($_SESSION ['LabelText1'] != '' && $_SESSION ['LabelText1'] != 'Placas'){
                    if (Havepermission ( $_SESSION ['UserID'], 1778, $db )==1) {
                        //$htmlPDF .= '<td class="numero_normal"><input type="text" name="discountcard_' . $myrow ['orderno'] . '" value="' . $myrow ['discountcard'] . '" size="20"></td>';
                        $htmlPDF .= '<td class="numero_normal">' . $myrow ['discountcard'] . '</td>';
                    }else{
                        $htmlPDF .= '<td class="numero_normal">' . $myrow ['discountcard'] . '</td>';
                    }
                    
                }else{
                    //echo '<td class="numero_normal">' . $myrow ['placa'] . '</td>';
                    if (Havepermission ( $_SESSION ['UserID'], 1778, $db )==1) {
                        //$htmlPDF .= '<td class="numero_normal"><input type="text" name="discountcard_' . $myrow ['orderno'] . '" value="' . $myrow ['placa'] . '" size="20"></td>';
                        $htmlPDF .= '<td class="numero_normal">' . $myrow ['placa'] . '</td>';
                    }else{
                        $htmlPDF .= '<td class="numero_normal">' . $myrow ['placa'] . '</td>';
                    }

                }

                if ($PerMosBombeoProveedor == 1) {
                    $add_bombeo = "";
                    $add_roveedor = "";

                    $sqlBombeo="SELECT salesstockproperties.valor,
                                   salesstockproperties.InvoiceValue,
                                   salesstockproperties.complemento 
                            FROM salesstockproperties
                            WHERE salesstockproperties.orderno='".$myrow['orderno']."'";
                              //AND salesstockproperties.valor!='NO'"; 
                              //AND orderlineno=".$myrow['linea']. "
                              //AND salesstockproperties.typedocument IN (10,110)
                    $resulBombeo=DB_query($sqlBombeo,$db); 
                    if (DB_num_rows($resulBombeo)>0) {
                        //$add_bombeo = "Si If";
                        while ($rowBombeo=DB_fetch_array($resulBombeo)){
                            if (strlen(trim($rowBombeo['valor'])) > 0) {
                                $add_bombeo = $rowBombeo['InvoiceValue'];

                                switch ($rowBombeo['InvoiceValue']) {
                                    case trim('Interno'):
                                        $sqlbomb = "SELECT class FROM stockclass WHERE idclass = '" . $rowBombeo['complemento'] . "'";
                                        $rsqlbomb = DB_query($sqlbomb, $db);
                                        $rowbom = DB_fetch_array($rsqlbomb);
                                        $add_roveedor = $rowbom['class'];
                                        break;
                                    case trim('Externo'):
                                        $sqlbomb = "  SELECT suppname FROM suppliers WHERE supplierid = '" . $rowBombeo['complemento'] . "'";
                                        $rsqlbomb = DB_query($sqlbomb, $db);
                                        $rowbom = DB_fetch_array($rsqlbomb);
                                        $add_roveedor = $rowbom['suppname'];
                                        break;
                                    default:
                                        
                                        break;
                                }
                            }
                        }
                    }else{
                        //$add_bombeo = "No If";
                    }

                    $htmlPDF .= '<td class="texto_normal2">' .$add_bombeo. '</td>';
                    $htmlPDF .= '<td class="texto_normal2">' . $add_roveedor . '</td>';
                }

                $htmlPDF .= '<td class="texto_normal2">' . $myrow ['UserRegister'] . '</td>';
                $htmlPDF .= '<td class="texto_normal2">' . $myrow ['salesmanname'] . '</td>';
                $htmlPDF .= '<td class="numero_normal">' . $myrow ['currcode'] . '</td>';
                
                /*$htmlPDF .= '<td class="numero_normal">';
                if ($myrow ['generateCreditNote'] != '0' and strlen ( $myrow ['transno'] ) > 0) {
                    if ($myrow ['typeinvoice'] != 0) {
                        $liga = "PDFPagarePage.php?" . SID . "&PrintPDF=Yes&type=10&TransNo=" . $myrow ['transno'];
                        $tienepagares = "<a target='_blank' href='" . $rootpath . "/" . $liga . "'>Imprimir<br>Pagares</a>";
                        $htmlPDF .= $tienepagares;
                    }
                }
                $htmlPDF .= '</td>';*/
                
                $htmlPDF .= '<td class="numero_celda">$' . number_format ( $myrow ['ordervalue'], 2 ) . '</td>';
                
                // if ($_SESSION ['DatabaseName'] == "gruposervillantas_DES" || $_SESSION ['DatabaseName'] == "gruposervillantas"){
                $htmlPDF .= '<td class="texto_normal2">' . $myrow ['statusname'] . '</td>';
                // }

                $cambiarImprimeVehicleLink = "";
                // if ($_POST['Quotations'] == 6 || $_POST['Quotations'] == 9 || $_POST['Quotations'] == 8) {
                $jsWindowOpen = 'var win = window.open("ChangePrintVehicle.php?orderno=' . $myrow ['orderno'] . '", "vehiculo", "menubar=0, scrollbars=1, resizable=0, width=400, height=500"); win.focus();';
                $cambiarImprimeVehicleLink = "<a href='#' onclick='$jsWindowOpen'>" . _ ( "" ) . "<img title='Imprimir Vehiculo' src='images/imgs/proveedores.png' width='25'  height='20' border=0></a><br />";
                //$htmlPDF .= '<td class="numero_normal">' . $cambiarImprimeVehicleLink . '</td>';
                // }
                if ($myrow ['currcode']=="USD"){
                    $montoTotalUSD = $montoTotalUSD + $myrow ['ordervalue'];
                }else{
                    $montoTotal = $montoTotal + $myrow ['ordervalue'];
                }

                $htmlPDF .= '</tr>';
            }

            $htmlPDF .= '<tr>';
            $htmlPDF .= ' <td colspan="11" class="pie_derecha" style="text-align: right; padding-right: 15px;">' . _ ( 'Total MXN:' ) . '</td>';
            $htmlPDF .= ' <td colspan="1" class="pie_derecha">$' . number_format ( $montoTotal, 2 ) . '</td>';
            $htmlPDF .= ' <td colspan="1" class="pie_derecha"></td>';
            $htmlPDF .= '</tr>';
            $htmlPDF .= '<tr>';
            $htmlPDF .= ' <td colspan="11" class="pie_derecha" style="text-align:right; padding-right: 15px;">' . _ ( 'Total USD:' ) . '</td>';
            $htmlPDF .= ' <td colspan="1" class="pie_derecha">$' . number_format ( $montoTotalUSD, 2 ) . '</td>';
            $htmlPDF .= ' <td colspan="1" class="pie_derecha"></td>';
            $htmlPDF .= '</tr>';
            $htmlPDF .= '</table>';

            if (isset($_GET['PDF'])) {

                require_once("lib/dompdf/dompdf_config.inc.php");

                $img = "";
                $UnidNegMsj = "";
                if (!empty($UnidNeg)) {
                    $sqlLogo = "SELECT tags.tagdescription, legalbusinessunit.address1, legalbusinessunit.address2, legalbusinessunit.address3, 
                        legalbusinessunit.address4, legalbusinessunit.address5, legalbusinessunit.telephone, legalbusinessunit.fax, legalbusinessunit.logo
                            FROM tags
                            LEFT JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
                            WHERE tagref = '".$UnidNeg."'";
                    $resultLogo = DB_query ( $sqlLogo, $db, '', '', False, False );
                    $row = DB_fetch_array ( $resultLogo );

                    $UnidNegMsj = $row['tagdescription'];

                    if (file_exists($row['logo'])) {
                        $img = "<img src='".$row["logo"]."' width='130' height='80' style='margin-left: 15px; margin-top: -20px;' title='".$row['logo']."'>";
                    }
                }
                
                $sqlNameUsu = "SELECT realname FROM www_users WHERE userid = '".$_SESSION['UserID']."'";
                $resulNameUsu = DB_query ( $sqlNameUsu, $db, '', '', False, False );
                $rowNameUsu = DB_fetch_array ( $resulNameUsu );

                $style = "style='font-size: 13px;'";

                $Header .= $img."";
                $Header .= "<p style='font-size: 20px; width: 100%; text-align: center; padding-top: -35px;'>Pedidos de Venta</p>";

                $Header .= '<table cellspacing="0" border="0" cellpadding="0" style="padding-top: -14px; width: 100%;">';

                if (!empty($UnidNeg)) {
                    $Header .= '<tr>';
                    $Header .= '<td '.$style.'>'.$row['address1'].'</td>';
                    $Header .= '<td></td>';
                    $Header .= '</tr>';
                    $Header .= '<tr>';
                    $Header .= '<td '.$style.'>'.$row['address2'].'</td>';
                    $Header .= '<td></td>';
                    $Header .= '</tr>';
                    $Header .= '<tr>';
                    $Header .= '<td '.$style.'>'.$row['address3'].'</td>';
                    $Header .= '<td></td>';
                    $Header .= '</tr>';
                    $Header .= '<tr>';
                    $Header .= '<td '.$style.'>'.$row['address4'].'</td>';
                    $Header .= '<td></td>';
                    $Header .= '</tr>';
                    $Header .= '<tr>';
                    $Header .= '<td '.$style.'>'.$row['address5'].'</td>';
                    $Header .= '<td></td>';
                    $Header .= '</tr>';
                    if (!empty($row['telephone'])) {
                        $Header .= '<tr>';
                        $Header .= '<td '.$style.'>Telefono: '.$row['telephone'].'</td>';
                        $Header .= '<td></td>';
                        $Header .= '</tr>';
                    }
                }

                $Header .= '<tr>';
                $Header .= '<td '.$style.'>Del: '.$fechaini.' Al: '.$fechafin.'</td>';
                $Header .= '<td '.$style.'>Usuario: '.$_SESSION['UserID']." - ".$rowNameUsu['realname'].'</td>';
                $Header .= '</tr>';

                $MsjImpreso = "Impreso: ".date('d/m/Y');

                if (!empty($legalbusiness)) {
                    $sql = "SELECT legalname FROM legalbusinessunit WHERE legalid = '".$legalbusiness."'";
                    $result = DB_query ( $sql, $db, '', '', False, False );
                    $row = DB_fetch_array ( $result );

                    $Header .= '<tr>';
                    $Header .= '<td '.$style.'>Raz&oacute;n Social: '.$row['legalname'].'</td>';
                    $Header .= '<td '.$style.'>'.$MsjImpreso.'</td>';
                    $Header .= '</tr>';

                    $MsjImpreso = "";
                }
                
                if (!empty($UnidNeg)) {
                    $Header .= '<tr>';
                    $Header .= '<td '.$style.'>Unidad de Negocio: '.$UnidNegMsj.'</td>';
                    $Header .= '<td '.$style.'>'.$MsjImpreso.'</td>';
                    $Header .= '</tr>';
                }

                /*$Header .= '<tr>';
                $Header .= '<td '.$style.'>'.$UnidNegMsj.'</td>';
                $Header .= '<td '.$style.'>Usuario: '.$_SESSION['UserID']." - ".$rowNameUsu['realname'].'</td>';
                $Header .= '</tr>';

                $Header .= '<tr>';
                $Header .= '<td '.$style.'></td>';
                $Header .= '<td '.$style.'>Impreso: '.date('d/m/Y').'</td>';
                $Header .= '</tr>';*/

                $Header .= '</table>';

                $htmlHeader = "<html style='margin: 1mm 4mm 1mm 4mm;font-size: 10px;' > 
                        <body style='margin: 6mm 6mm 6mm 6mm;font-size: 10px;'>
                           ";
                $htmlFooter = "
                            </body>
                        </html>";

                //$htmlPDF .= "<h1>Mostrar Reporte</h1>";
                //$htmlCompleto = $htmlHeader.$html.$htmlFooter;

                try{
                    $dompdf = new DOMPDF();
                    //$dompdf->set_paper("A4", "portrait");
                    $dompdf->set_paper("A4", "landscape"); 
                    
                    $dompdf->load_html(utf8_decode($htmlHeader.$Header.$htmlPDF.$htmlFooter));
                    ini_set("memory_limit","1024M");
                    //ini_set('max_execution_time', 180);
                    $dompdf->render();

                    //echo $htmlPDF;
                    header('Content-type: application/pdf'); 
                    echo $dompdf->output();

                    //$filename = 'Reporte.pdf';
                    //$dompdf->stream($filename,array("Attachment"=>false));
                    //$dompdf->stream($filename);
                }catch(Exception $e){
                    if ($_SESSION['UserID'] == 'desarrollo') {
                        echo $e;
                    }
                }
            }

            if (isset($_GET['EXCEL'])) {
                header ( "Content-type: application/ms-excel" );
                header ( "Content-Disposition: attachment; filename=PedidosdeVenta.xls" );
                header ( "Pragma: no-cache" );
                header ( "Expires: 0" );

                echo $htmlPDF;
            }
        }else{
            echo '<div class="alert alert-warning" role="alert">No hay resultados para mostrar</div>';
        }
    }

    //Quitar no se ocupa informacion
    /*
    if ($myrow ['idprospect'] > 0) {
        $oportunidad = "<a target='_blank' href='" . $oportunidadprospecto . "?u_movimiento=" . $myrow ['idprospect'] . "'><img title='Ingresar a Oportunidad' src='images/user_24x32.gif' border=0></a>";
    } else {
        $oportunidad = ' ';
    }
    $Cancelar = "&nbsp;&nbsp;";
    if ($_SESSION ['ShowProspect'] == 1 and $oportunidadprospectoPermiso == 1) {
        //$htmlPDF .= '<td style="text-align:center">' . $oportunidad . '</td>';
    }
    // Validaciones para cancelar documento cxc y/o pedidos
    if (($myrow ['idfactura']) > 0) {
        $statusPeriodo = TraestatusPeriod ( $myrow ['legalid'], $myrow ['prd'], $db );
        // Verificar si se utiliza...
        $sql = "select date(fechacorte) as fechacorte
                from usrcortecaja
                where u_status=0 and tag=" . $myrow ['tagref'];
        $Fechacorte = DB_query ( $sql, $db, '', '' );
        while ( $myrowcorte = DB_fetch_array ( $Fechacorte ) ) {
            $fechacorte = $myrowcorte ['fechacorte'];
        }
        
        if ($myrow ['type'] == 10) {
            $XSQL = "SELECT DISTINCT d2.id,custallocns.transid_allocfrom
                    FROM debtortrans INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto
                    INNER JOIN debtortrans d2 ON d2.type=70 and d2.order_=debtortrans.transno
                    LEFT JOIN custallocns C2 ON d2.id=C2.transid_allocto
                    WHERE  debtortrans.id='" . $myrow ['idfactura'] . "'
                            AND (C2.amt  IS NOT NULL or C2.amt<>0)
                            Union
                            SELECT debtortrans.id,custallocns.transid_allocfrom
                            FROM debtortrans INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto and abs(alloc)<>0
                            INNER JOIN debtortrans d2 ON custallocns.transid_allocfrom=d2.id and d2.type<>70
                            
                            WHERE  debtortrans.id='" . $myrow ['idfactura'] . "'";
        } else {
            $XSQL = "SELECT debtortrans.id,custallocns.transid_allocfrom
                    FROM debtortrans INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto
                    and abs(debtortrans.alloc)<>0
                    WHERE  debtortrans.id='" . $myrow ['idfactura'] . "'";
        }
        // echo '<pre>sql1:'.$XSQL;
        $tienerecibos = DB_query ( $XSQL, $db, '', '' );
        if (intval ( DB_num_rows ( $tienerecibos ) ) > intval ( 0 )) {
            $myrowtienerecibos = DB_fetch_row ( $tienerecibos );
            // VALIDA SI NO ES FACTURA DE TICKET
            $sql = "select * from debtortrans where type=112 AND id=" . $myrowtienerecibos [1];
            $Getstatus = DB_query ( $sql, $db, $ErrMsg, $DbgMsg, true );
            if (DB_num_rows ( $Getstatus ) == 1) {
                // $Cancelar="Tiene<br>Correcciones";
                $Cancelar = $rootpath . '/ConfirmCancel_Invoice.php?TransNo=' . $myrow ['transno'] . '&OrderNumber=' . $myrow ['orderno'] . '&debtorno=' . trim ( $myrow ['debtorno'] ) . '&type=' . $myrow ['type'] . '&tagref=' . $myrow ['tagref'] . '&idcorreccion=' . $myrowtienerecibos [1];
                $Cancelar = "<a href=" . $Cancelar . "><img src='part_pics/Delete.png' border=0></a>";
            } else {
                
                $Cancelar = "Tiene<br>Recibos";
            }
        } else {
            $myrowtienerecibos = DB_fetch_row ( $tienerecibos );
            if (Havepermission ( $_SESSION ['UserID'], $myrow ['cancelfunctionid'], $db ) == 1) {
                // $Cancelar = $rootpath . '/SelectSalesOrder.php?orderno='. $myrow['orderno'];
                
                $xflag = ($myrow ['fecha'] >= (date("Y") . "-" .  date("m") . "-" . date("d") . " 00:00:00.000"));
                if ($xflag==""){
                    $xflag = 0;
                }
                
                if ($statusPeriodo == 0) {
                    if ((Havepermission ( $_SESSION ['UserID'], 377, $db ) == 1) or ($xflag)) {
                        $Cancelar = $rootpath . '/ConfirmCancel_Invoice.php?TransNo=' . $myrow ['transno'] . '&OrderNumber=' . $myrow ['orderno'] . '&debtorno=' . trim ( $myrow ['debtorno'] ) . '&type=' . $myrow ['type'] . '&tagref=' . $myrow ['tagref'];
                        $Cancelar = "<a href=" . $Cancelar . "><img src='part_pics/Delete.png' border=0></a>";
                    }else{
                        $Cancelar = '<b>' . _ ( 'Fecha' ) . '<br>' . _ ( 'Anterior' ) . '</b>';
                    }
                } else {
                    $Cancelar = '<b>' . _ ( 'Contabilidad' ) . '<br>' . _ ( 'Cerrada' ) . '</b>';
                }
            } else {
                $Cancelar = "&nbsp;&nbsp;";
            }
        }
    } else {
        if (Havepermission ( $_SESSION ['UserID'], $myrow ['cancelfunctionid'], $db ) == 1) {
            if ($myrow ['flagcancel'] == 0 && Havepermission ( $_SESSION ['UserID'], $myrow ['cancelextrafunctionid'], $db ) == 1) {
                $Cancelar = $rootpath . '/' . $paginabusquedapedidos . '?orderno=' . $myrow ['orderno'];
                $Cancelar = "<a href=" . $Cancelar . "><img src='part_pics/Delete.png' border=0></a>";
            } else {
                $Cancelar = $rootpath . '/' . $paginabusquedapedidos . '?orderno=' . $myrow ['orderno'];
                $Cancelar = "<a href=" . $Cancelar . "><img src='part_pics/Delete.png' border=0></a>";
            }
        }
    }
    
    //$htmlPDF .= '<td style="text-align:center">' . $Cancelar . '</td>';
    
    if ($flagventaperdida == 1 and $enviaventaperdida == 1) {
        $VenPerdida = $rootpath . '/' . $paginabusquedapedidos . '?orderno=' . $myrow ['orderno'] . '&ventaperdida=yes';
        $VenPerdida = "<a href=" . $VenPerdida . "><img src='part_pics/Delete.png' border=0></a>";
        //$htmlPDF .= '<td style="text-align:center">' . $VenPerdida . '</td>';
    }
    
    if (($myrow ['idfactura']) > 0) {
        if (($modificarvendedores == 1)) {
            $link_modVendedores = "<a target='_blank' href='ChangeSalesman.php?orderno=" . $myrow ['orderno'] . "&type=" . $myrow ['type'] . "&folio=" . $myrow ['folio'] . "'
                    title='Modificar Vendedor'><img src='" . $rootpath . "/css/" . $theme . "/images/user.png' TITLE='" . _ ( 'Modificar Vendedores' ) . " ALT='" . _ ( 'Modificar Vendedores' ) . "'></a>";
        } else {
            $link_modVendedores = '';
        }
    } else {
        $link_modVendedores = '';
    }
    if ($modificarvendedores == 1) {
        //$htmlPDF .= '<td style="text-align:center">' . $link_modVendedores . '</td>';
    }
    
    if (($myrow ['idfactura']) > 0) {
        // Modificar trabajadores
        $ssql = "select salesorderdetails.orderno, sum(salesstockproperties.valor IS NULL)  as porcapturar
                from salesorderdetails JOIN stockmaster ON  salesorderdetails.stkcode = stockmaster.stockid
                LEFT JOIN stockcatproperties ON  stockmaster.categoryid =  stockcatproperties.categoryid
                LEFT JOIN salesstockproperties ON salesstockproperties.orderno =  salesorderdetails.orderno
                where salesorderdetails.orderno=" . $myrow ['orderno'] . " and stockcatproperties.reqatsalesorder = 1
                        GROUP BY salesorderdetails.orderno";
        $resultProp = DB_query ( $ssql, $db, '', '' );
        $myrowProp = DB_fetch_row ( $resultProp );
        if (intval ( $myrowProp [1] ) > 0) // si tiene productos con categoorias a los cuales se asigna trabajadores
        {
            $link_modTrabajadores = "<a href='ChangePropertiesInvoice.php?orderno=" . $myrow ['orderno'] . "&folio=" . $myrow ['folio'] . "' title='Modificar Trabajadores'><img src='part_pics/Users-2.png' border=0></a>";
        } elseif ($modificartrabajadores == 1) {
            $link_modTrabajadores = "<a href='ChangePropertiesInvoice.php?orderno=" . $myrow ['orderno'] . "&folio=" . $myrow ['folio'] . "' title='Modificar Trabajadores'><img src='part_pics/Users-2.png' border=0></a>";
        } else {
            $link_modTrabajadores = "&nbsp;";
        }
    } else {
        $link_modTrabajadores = '';
    }
    
    if ($modificartrabajadores == 1) {
        //$htmlPDF .= '<td style="text-align:center">' . $link_modTrabajadores . '</td>';
    }
    
    $cambiarUsuarioLink = "";
    if ($permisoCambiarUsuario == 1) {
        $jsWindowOpen = 'var win = window.open("ChangeOrderUser.php?orderno=' . $myrow ['orderno'] . '", "usuario", "menubar=0, scrollbars=1, resizable=0, width=400, height=500"); win.focus();';
        $cambiarUsuarioLink = "<a href='#' onclick='$jsWindowOpen'>" . '<img width="20" height="20" src="' . $rootpath . '/images/usuario_modif.png" title="' . _ ( 'Modificar Usuario' ) . '" alt="">' . "</a><br>";
        //$htmlPDF .= '<td style="text-align:center">' . $cambiarUsuarioLink . '</td>';
    }
    if(!isset($typedeb)){
        $typedeb="";
    }
    if (Havepermission ( $_SESSION ['UserID'], 3014, $db ) == 1) {
        $modfactr = $rootpath . '/modifyInvoice.php?TransNo=' . $transno . '&OrderNumber=' . $myrow ['orderno'] . '&type=' . $typedeb;
        $modfactr = "<a target='_blank' href='" . $modfactr . "'><img src='images/Edit.ico' border=0></a>";
    } else {
        $modfactr = "&nbsp;";
    }
    
    if (($Exportaexcel == 1)) {
        $link_exportaexcel = "<a target='_blank' href='Z_ExportaPedidoExcel.php?orderno=" . $myrow ['orderno'] . "'
                    title='Exportar a Excel'><img src='images/excel.png' height=15 width=15 TITLE='" . _ ( 'Exportar a Excel' ) . "' ALT='" . _ ( 'Exportar a Excel' ) . "'></a>";
    } else {
        $link_exportaexcel = '';
    }
    //
    if (($myrow ['idfactura']) > 0) {
        $ReenvioXSA = $rootpath . '/' . $paginabusquedapedidos . '?orderno=' . $myrow ['orderno'] . '&tagrefen=' . $myrow ['tagref'] . '&iddocto=' . $myrow ['idfactura'] . '&serie=' . $serie . '&folio=' . $myrow ['folio'] . '&debtorno=' . $myrow ['debtorno'] . '&tipo=' . $myrow ['type'] . '&transno=' . $myrow ['transno'] . // $transno
            '&FromDia=' . $FromDia . '&FromMes=' . $FromMes . '&FromYear=' . $FromYear . '&ToDia=' . $ToDia . '&ToMes=' . $ToMes . '&ToYear=' . $ToYear . '&action=reimpresion&SearchOrders=SearchOrders&FolioFiscal=' . $myrow ['folio'];
        // $ReenvioXSA='';
        if ($myrow ['flagelectronic'] == 1 or $myrow ['type'] == '119') {
            $ReenviarXSA = "&nbsp;&nbsp;&nbsp;<a href='" . $ReenvioXSA . "'><img src='part_pics/Mail-Forward.png' alt='" . _ ( 'Reenviar SAT' ) . "' border=0>" . _ ( 'Reimpresion' ) . "</a>";
        } else {
            $ReenviarXSA = '';
        }
        $RecapturaAddenda = $rootpath . '/RecapturaAddenda.php?' . '&iddocto=' . $myrow ['idfactura'];
        $RecapturaAddenda = "&nbsp;&nbsp;&nbsp;<a href=" . $RecapturaAddenda . "><img src='images/Edit.ico' alt='" . _ ( 'Recaptura datos addenda' ) . "' border=0></a>";
        
        if (($add_datosfactura == 1)) {
            $RecapturaAddenda = $rootpath . '/RecapturaAddenda.php?' . '&iddocto=' . $myrow ['idfactura'];
            $RecapturaAddenda = "&nbsp;&nbsp;&nbsp;<a href=" . $RecapturaAddenda . "><img src='images/Edit.ico' alt='" . _ ( 'Recaptura datos addenda' ) . "' border=0>" . _ ( 'Addenda' ) . "</a>";
        } else {
            $RecapturaAddenda = '';
        }
        
        if ($mod_datosfactura == 1) {
            $modfactr = $rootpath . '/modifyInvoice.php?TransNo=' . $transno . '&OrderNumber=' . $myrow ['orderno'] . '&type=' . $myrow ['type'];
            $modfactr = "<a target='_blank' href='" . $modfactr . "'><img src='images/Edit.ico' border=0>" . _ ( 'Inf. Partidas' ) . "</a>";
        } else {
            $modfactr = "&nbsp;";
        }
    } else {
        $ReenviarXSA = '';
        $RecapturaAddenda = '';
        $modfactr = "&nbsp;";
    } // 77
    
    if ($Exportaexcel == 1) {
        //$htmlPDF .= '<td style="text-align:center">' . $link_exportaexcel . '</td>';
    }
    
    if ($add_datosfactura == 1) {
        //$htmlPDF .= '<td style="text-align:center">' . $ReenviarXSA . '</td><td style="text-align:center"> ' . $RecapturaAddenda . '</td><td style="text-align:center"> ' . $modfactr . '</td>';
    }
    // echo '<td style="text-align:center">'.$RecapturaAddenda.' '.$modfactr.' --- '.$ReenviarXSA.'</td>';
    if ($permisoimprimeservicio == 1) {
        $ligaOrden = 'PDFOrdenservicio.php?orderno=' . $myrow ['orderno'];
        $PrintOrden = $rootpath . '/' . $ligaOrden;
        //$htmlPDF .= '<td style="text-align:center"><a target="_blank"  href=' . $PrintOrden . '><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _ ( 'Imprimir' ) . '" alt=""></a></td>';
    }
    if ($permisodesbloqueapedido == 1) {
        $pagdesloquea = "SelectOrderUnlock.php?orderno=" . $myrow ['orderno'];
        //$htmlPDF .= "<td class='numero_normal'><a target='_blank' href='" . $pagdesloquea . "'><img src='images/desbloquear_20.png' title='" . _ ( 'desbloquear pedido' ) . "'></a></td>";
    }*/
?>