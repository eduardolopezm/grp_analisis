<?php
//PROD.

/* $Revision: 4.0 $*******
MODIFICADO POR: CARMEN GARCIA
FECHA: 29/DIC/2009
CAMBIOS:
1.- GENERA CADENA DE FACTURACION
FIN DE CAMBIOS

MODIFICADO POR FRUEBEL CANTERA
FECHA 03/01/2009
CAMBIOS:
DUPLIQUE FUNCION XSAInvoicing, Para utilizacion en recibos como XSAInvoicingRecibo
 */
/*
function DocumentNext ($TransType, $tag, $area,$legaid ,&$db) {

$SQL = "SELECT count(*) FROM sysDocumentIndex
WHERE typeid = " . $TransType . " and tagref in ('" . $tag ."','0')
and areacode in('".$area."','0') and legalid in('".$legaid."','0')";
$result = DB_query($SQL,$db);
$myrow = DB_fetch_row($result);
if ($myrow[0]==0)
{
$SQL = "SELECT typename FROM sysDocumentIndex WHERE typeid = " . $TransType;
$result = DB_query($SQL,$db);
if (DB_num_rows($result)!=0)
{
$myrowType = DB_fetch_row($result);
$typename = $myrowType[0];
}
else
{
$typename = "NOMBRE NO ASIGNADO";
}

$SQL = 'INSERT INTO sysDocumentIndex (`typeid`,`legalid`,`typename`,`areacode`,`tagref`,`loccode`,`typeabbrev`,`typeno`)';
$SQL .= ' VALUES ('.$TransType.',"'.$legaid.'","' .$typename. '","'.$area.'",' .$tag. ',"ANY","AN",0)';
$result = DB_query($SQL,$db);
}

DB_query("LOCK TABLES sysDocumentIndex WRITE",$db);
$SQL = "SELECT typeno,folio FROM sysDocumentIndex
WHERE typeid = " . $TransType . " and tagref in('" . $tag . "',0) and areacode in('" . $area . "','0')
and legalid in('".$legaid."','0')";
$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': <BR>' . _('The next transaction number could not be retrieved from the database because');
$DbgMsg =  _('The following SQL to retrieve the transaction number was used');
$GetTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
$myrow = DB_fetch_row($GetTransNoResult);
$SQL = 'UPDATE sysDocumentIndex SET typeno = ' . ($myrow[0] + 1) . '
WHERE typeid = "' . $TransType . '" and tagref in ("' . $tag .'","0")
and areacode in("'.$area.'","0") and legalid in("'.$legaid.'","0")';
$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The transaction number could not be incremented');
$DbgMsg =  _('The following SQL to increment the transaction number was used');
$UpdTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
DB_query("UNLOCK TABLES",$db);
$valordev=$myrow[0] + 1;
$valordev=$valordev.'|'.$myrow[1];
return $valordev;
}
 */
//FCC
//NUEVA FUNCION DocumentNext, QUE TOMA EN CUENTA COMO PARAMETRO EL DEPARTAMENTO.
// A producción cambios del complemento
//  // DGJ 14/SEP/2018 se cambio la fechad de emision
//  // DGJ 11/DIC/2018 se envian cambios de tipo relacion 08
/*
 DGJ: 04/07/2019 TAREA 77965 a produccion 
 */

function DocumentNext($TransType, $tag, $area, $legaid, &$db)
{
    $dsql    = "SELECT u_department FROM tags WHERE tagref = '" . $tag . "'";
    $dresult = DB_query($dsql, $db);
    if ($dmyrow = DB_fetch_array($dresult, $db)) {
        $u_department = $dmyrow['u_department'];
    } else {
        $u_department = 0;
    }

    if (($TransType == 10) and ($legaid == 4)) {
        $SQL = "SELECT count(*) FROM sysDocumentIndex
		WHERE typeid = " . $TransType . " and tagref in ('" . $tag . "','0')
		and areacode in('" . $area . "','0') and legalid in('" . $legaid . "','0')
		and u_department in ('" . $u_department . "','0')";
    } else {
        $SQL = "SELECT count(*) FROM sysDocumentIndex
		WHERE typeid = " . $TransType . " and tagref in ('" . $tag . "','0')
		and areacode in('" . $area . "','0') and legalid in('" . $legaid . "','0')";
    }

    $result = DB_query($SQL, $db);
    $myrow  = DB_fetch_row($result);
    if ($myrow[0] == 0) {
        $SQL    = "SELECT typename FROM sysDocumentIndex WHERE typeid = " . $TransType;
        $result = DB_query($SQL, $db);
        if (DB_num_rows($result) != 0) {
            $myrowType = DB_fetch_row($result);
            $typename  = $myrowType[0];
        } else {
            $typename = "NOMBRE NO ASIGNADO";
        }

        $SQL = 'INSERT INTO sysDocumentIndex (`typeid`,`legalid`,`typename`,`areacode`,`tagref`,`loccode`,`typeabbrev`,`typeno`)';
        $SQL .= ' VALUES (' . $TransType . ',"' . $legaid . '","' . $typename . '","' . $area . '",' . $tag . ',"ANY","AN",0)';
        $result = DB_query($SQL, $db);
    }

    DB_query("LOCK TABLES sysDocumentIndex WRITE", $db);
    if (($TransType == 10) and ($legaid == 4)) {
        $SQL = "SELECT typeno,folio FROM sysDocumentIndex
		WHERE typeid = " . $TransType . " and tagref in('" . $tag . "',0) and areacode in('" . $area . "','0')
		and legalid in('" . $legaid . "','0')
		and u_department in ('" . $u_department . "','0')";
    } else {
        $SQL = "SELECT typeno,folio FROM sysDocumentIndex
		WHERE typeid = " . $TransType . " and tagref in('" . $tag . "',0) and areacode in('" . $area . "','0')
		and legalid in('" . $legaid . "','0')";
    }

    $ErrMsg           = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': <BR>' . _('The next transaction number could not be retrieved from the database because');
    $DbgMsg           = _('The following SQL to retrieve the transaction number was used');
    $GetTransNoResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
    $myrow            = DB_fetch_row($GetTransNoResult);

    if (($TransType == 10) and ($legaid == 4)) {
        $SQL = "UPDATE sysDocumentIndex SET typeno = " . ($myrow[0] + 1) . "
		WHERE typeid = '" . $TransType . "' and tagref in ('" . $tag . "','0')
		and areacode in('" . $area . "','0') and legalid in('" . $legaid . "','0')
		and u_department in ('" . $u_department . "', '0')";
    } else {
        $SQL = "UPDATE sysDocumentIndex SET typeno = " . ($myrow[0] + 1) . "
		WHERE typeid = '" . $TransType . "' and tagref in ('" . $tag . "','0')
		and areacode in('" . $area . "','0') and legalid in('" . $legaid . "','0')";
    }

    $ErrMsg           = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The transaction number could not be incremented');
    $DbgMsg           = _('The following SQL to increment the transaction number was used');
    $UpdTransNoResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
    DB_query("UNLOCK TABLES", $db);
    $valordev = $myrow[0] + 1;
    $valordev = $valordev . '|' . $myrow[1];
    return $valordev;

}

function DocumentNextByType($TransType, $tag, $area, $legaid, &$db)
{
    if ($TransType == 10) {
        $tablaconsulta = "sysDocumentIndexInv";
    } elseif ($TransType == 12) {
        $tablaconsulta = "sysDocumentIndexRec";
    } else {
        $tablaconsulta = "sysDocumentIndexOth";
    }

    DB_query("LOCK TABLES " . $tablaconsulta . " WRITE", $db);
    $SQL = "SELECT typeno,folio FROM " . $tablaconsulta . " WHERE typeid = " . $TransType . " and tagref in('" . $tag . "',0) and areacode in('" . $area . "','0') and legalid in('" . $legaid . "','0')";
    //echo $SQL;
    $ErrMsg           = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': <BR>' . _('The next transaction number could not be retrieved from the database because');
    $DbgMsg           = _('The following SQL to retrieve the transaction number was used');
    $GetTransNoResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
    $myrow            = DB_fetch_row($GetTransNoResult);
    $SQL              = "UPDATE " . $tablaconsulta . " SET typeno = " . ($myrow[0] + 1) . "
	WHERE typeid = " . $TransType . " and tagref in(" . $tag . ",0) and areacode in('" . $area . "',0)
	and legalid in(" . $legaid . ",0)";
    $ErrMsg           = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The transaction number could not be incremented');
    $DbgMsg           = _('The following SQL to increment the transaction number was used');
    $UpdTransNoResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
    DB_query("UNLOCK TABLES", $db);
    $valordev = $myrow[0] + 1;
    $valordev = $valordev . '|' . $myrow[1];
    return $valordev;
}

//***************************************************************************************************************************
//********************************************* INICIO DE FUNCION PARA NOTA DE CREDITO DIRECTAS******************************
//***************************************************************************************************************************

function XSAInvoicingCreditdirect($InvoiceNo, $orderno, $debtorno, $TypeInvoice, $tag, $serie, $folio, &$db)
{
    $charelectronic = '01';
    $charelectronic = $charelectronic . '|' . $serie . $folio;
    $serieelect     = $serie;
    $folioelect     = $folio;
    $charelectronic = $charelectronic . '|' . $serieelect;
    $charelectronic = $charelectronic . '|' . $folioelect;
    // consulto datos de la factura
    $SQLInvoice = "SELECT
			replace(origtrandate,'-','/') as origtrandate,
			SUM(abs(debtortransmovs.ovamount)) AS ovamount,
			SUM(abs(debtortransmovs.ovgst)) AS ovgst,
			debtortransmovs.currcode,
			debtortransmovs.rate as cambio,
			debtortransmovs.order_,
			replace(debtortransmovs.trandate,'-','/') as trandate,
			debtortransmovs.debtorno,
			custbranch.taxid as rfc,
			debtorsmaster.name,
			debtorsmaster.address1,
			debtorsmaster.address2,
			debtorsmaster.address3,
			debtorsmaster.address4,
			debtorsmaster.address5,
			debtortransmovs.branchcode,
			custbranch.braddress1,
			custbranch.braddress2,
			custbranch.braddress3,
			custbranch.braddress4,
			custbranch.braddress5,
			www_users.realname,
			debtortransmovs.transno,
			debtortransmovs.reference,
			custbranch.phoneno as telofi,
			custbranch.brpostaddr1,
			custbranch.brpostaddr2,
			custbranch.brpostaddr3,
			custbranch.brpostaddr4,
			custbranch.brpostaddr5,
			custbranch.brpostaddr6,
			custpais as brpostaddr7, custbranch.specialinstructions
			FROM debtortransmovs,debtorsmaster,custbranch, www_users
			WHERE debtortransmovs.type=" . $TypeInvoice . "
			AND debtortransmovs.transno=" . $InvoiceNo . "
			AND debtortransmovs.tagref=" . $tag . "
			AND debtortransmovs.debtorno=debtorsmaster.debtorno
			AND debtortransmovs.debtorno=custbranch.debtorno
			AND debtortransmovs.branchcode=custbranch.branchcode
			AND www_users.userid = debtortransmovs.userid
			GROUP BY debtortransmovs.currcode,
			debtortransmovs.rate,
			debtortransmovs.order_,
			debtortransmovs.debtorno,
			custbranch.taxid,
			debtorsmaster.name,
			debtorsmaster.address1,
			debtorsmaster.address2,
			debtorsmaster.address3,
			debtorsmaster.address4,
			debtorsmaster.address5,
			debtortransmovs.branchcode,
			custbranch.braddress1,
			custbranch.braddress2,
			custbranch.braddress3,
			custbranch.braddress4,
			custbranch.braddress5,custbranch.phoneno,
			www_users.realname";

    $Result = DB_query($SQLInvoice, $db);

    if (DB_num_rows($Result) > 0) {
        {
            $myrow = DB_fetch_array($Result);
            // fecha emision
            $fechainvoice   = $myrow['origtrandate'];
            $UserRegister   = ""; //$myrow['UserRegister'];
            $charelectronic = $charelectronic . '|' . $fechainvoice;
            // subtotal
            $rfccliente = str_replace("-", "", $myrow['rfc']);
            $rfccliente = str_replace(" ", "", $rfccliente);
            $nombre     = $myrow['name'];
            $subtotal   = number_format($myrow['ovamount'], 2, '.', '');
            if ((strlen($rfccliente) < 12) or (strlen($rfccliente) >= 14)) {
                $rfccliente     = "XAXX010101000";
                $nombre         = "Publico en General";
                $subtotal       = number_format($myrow['ovamount'] + $myrow['ovgst'], 2, '.', '');
                $imprimepublico = 1;
            }
            $charelectronic = $charelectronic . '|' . $subtotal;
            // total factura
            $total          = number_format($myrow['ovamount'] + $myrow['ovgst'], 2, '.', '');
            $charelectronic = $charelectronic . '|' . abs($total);
            // total de iva
            $iva = number_format($myrow['ovgst'], 2, '.', '');
            // transladado
            $charelectronic = $charelectronic . '|' . abs($iva);
            // retenido
            $ivaret         = 0;
            $charelectronic = $charelectronic . '|' . abs($ivaret);
            //descuento
            $descuento      = number_format(0, 2, '.', '');
            $charelectronic = $charelectronic . '|' . abs($descuento);
            //motivo descuento
            $charelectronic = $charelectronic . '|';
            // tipo de moneda
            $moneda = "MXN"; //$myrow['currcode'];
            // CANTIDAD CON LETRAS
            $totaletras = abs($myrow['ovamount'] + $myrow['ovgst']);
            $separa     = explode(".", $totaletras);
            $montoctvs2 = $separa[1];
            $montoctvs1 = $separa[0];
            if ($montoctvs2 > 995) {
                $montoctvs1 = $montoctvs1 + 1;
            }
            $montoletra = Numbers_Words::toWords($montoctvs1, 'es');
            $totaletras = number_format($totaletras, 2);
            $separa     = explode(".", $totaletras);
            $montoctvs2 = $separa[1];
            if ($montoctvs2 > 995) {
                $montoctvs2 = 0;
            }
            $montocentavos = Numbers_Words::toWords($montoctvs2, 'es');
            if ($moneda == 'MXN') {
                $montoletra = ucwords($montoletra) . " Pesos " . $montoctvs2 . " /100 M.N.";
            } else {
                $montoletra = ucwords($montoletra) . " Dolares " . $montoctvs2 . "/100 USD";
            }
            $charelectronic = $charelectronic . '|' . $montoletra;
            // tipo moneda
            $charelectronic = $charelectronic . '|' . $moneda;
            // tipo de cambio
            $rate           = number_format($myrow['cambio'], 4, '.', '');
            $charelectronic = $charelectronic . '|' . $rate;
            // numero de orden para referencia
            $ordenref       = $myrow['order_'];
            $charelectronic = $charelectronic . '|' . $ordenref;
            // observaciones 1: vendedores
            $vendedor       = $myrow['realname'];
            $observaciones1 = 'Vendedor: ' . ' ' . $vendedor;
            $charelectronic = $charelectronic . '|' . $observaciones1;
            // observaciones 2
            $SQL = " SELECT l.telephone,l.comments,t.tagdescription
			       FROM legalbusinessunit l, tags t
			       WHERE l.legalid=t.legalid AND tagref='" . $tag . "'";
            $Result = DB_query($SQL, $db);
            if (DB_num_rows($Result) == 1) {
                $myrowtags = DB_fetch_array($Result);
                $telephone = trim($myrowtags['telephone']);
                $comments  = trim($myrowtags['comments']);
            }
            $observaciones2 = " Atencion a clientes " . $telephone;
            $charelectronic = $charelectronic . '|' . $observaciones2;
            // observaciones 3
            $observaciones3 = 'Id Nota :' . $InvoiceNo; //' '.$comments .' usr:'.$UserRegister;
            $TypeInvoice . "
			AND debtortransmovs.transno=" . $InvoiceNo . "
			AND debtortransmovs.tagref=" . $tag .

            $observaciones3 = $observaciones3 . $detallerecibos . "; "; // "Este comprobante es complementario a los expedidos en la fecha y folios descritos en cada partida detallada arriba";

            $charelectronic = $charelectronic . '|' . $observaciones3 . $detallerecibos;

            //SE AGREGA NUEVO CAMPO INFORMACION EXTRA
            $charelectronic = $charelectronic . '|' . '';

            // datos de la forma de pago
            $charelectronic = $charelectronic . '|' . chr(13) . chr(10) . '02';
            $terminospago   = "Una Exhibicion";

            $Tipopago = $Tipopago . ' ' . $terminospago;
            //$Tipopago=$terminospago;
            $charelectronic = $charelectronic . '|' . $Tipopago;
            // condiciones de pago
            $charelectronic = $charelectronic . '|---';
            // metodo de pago
            $metodopago     = 'Varios';
            $charelectronic = $charelectronic . '|' . $metodopago;
            // fecha vencimiento
            $fechavence     = $myrow['trandate'];
            $charelectronic = $charelectronic . '|' . $fechavence;
            // observaciones 4
            $observaciones4 = $observaciones3;
            $charelectronic = $charelectronic . '|' . $observaciones4;
            // datos del cliente
            $charelectronic = $charelectronic . '|' . chr(13) . chr(10) . '03';
            $branch         = $myrow['debtorno'];
            $charelectronic = $charelectronic . '|' . $branch;
            $charelectronic = $charelectronic . '|' . $rfccliente;
            $charelectronic = $charelectronic . '|' . $nombre;
            //$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));
            if (strlen(str_replace('|', '', str_replace('-', '', str_replace(' ', '', str_replace('\r', '', str_replace('\n', '', $myrow['telofi'])))))) > 0) {
                $pais = str_replace('|', '', str_replace('-', '', str_replace(' ', '', str_replace('\r', '', str_replace('\n', '', $myrow['telofi']))))); //"Mexico";//$myrow['name'];
            } else {
                $pais = "Mexico";
            }
            $charelectronic = $charelectronic . '|' . $pais;

        }
        {
            $calle = "";
            if ($imprimepublico == 0) {
                $calle = $myrow['address1'];
            }
            $charelectronic = $charelectronic . '|' . $calle;
            $noext          = "";
            $charelectronic = $charelectronic . '|' . $noext;
            $noint          = "";
            $charelectronic = $charelectronic . '|' . $noint;
            $colonia        = "";
            if ($imprimepublico == 0) {
                $colonia = $myrow['address2'];
            }
            $charelectronic  = $charelectronic . '|' . $colonia;
            $localidad       = "";
            $charelectronic  = $charelectronic . '|' . $localidad;
            $referenciacalle = "";
            $charelectronic  = $charelectronic . '|' . $referenciacalle;
            $municipio       = "";
            if ($imprimepublico == 0) {
                $municipio = $myrow['address3'];
            }
            $charelectronic = $charelectronic . '|' . $municipio;
            $edo            = "";
            if ($imprimepublico == 0) {
                $edo = $myrow['address4'];
            }
            $charelectronic = $charelectronic . '|' . $edo;
            $cp             = "";
            if ($imprimepublico == 0) {
                $cp = $myrow['address5'];
            }
        }
        {
            $charelectronic = $charelectronic . '|' . $cp;
            // datos del custbranch
            $charelectronic = $charelectronic . '|' . chr(13) . chr(10) . '04';
            $branch         = $myrow['branchcode'];
            $charelectronic = $charelectronic . '|' . $branch;
            //$rfc=$myrow['rfc'];
            //$charelectronic=$charelectronic.'|'.$rfccliente;
            $nombre         = $myrow['name'];
            $charelectronic = $charelectronic . '|' . $nombre;
            //$pais=str_replace('|','',str_replace('-','',str_replace(' ','',str_replace('\r','',str_replace('\n','',$myrow['telofi'])))));
            if (strlen(str_replace('|', '', str_replace('-', '', str_replace(' ', '', str_replace('\r', '', str_replace('\n', '', $myrow['telofi'])))))) > 0) {
                $pais = str_replace('|', '', str_replace('-', '', str_replace(' ', '', str_replace('\r', '', str_replace('\n', '', $myrow['telofi']))))); //"Mexico";//$myrow['name'];
            } else {
                $pais = "Mexico";
            }
            $charelectronic  = $charelectronic . '|' . $pais;
            $calle           = $myrow['braddress1'];
            $charelectronic  = $charelectronic . '|' . $calle;
            $noext           = "";
            $charelectronic  = $charelectronic . '|' . $noext;
            $noint           = "";
            $charelectronic  = $charelectronic . '|' . $noint;
            $colonia         = $myrow['braddress2'];
            $charelectronic  = $charelectronic . '|' . $colonia;
            $localidad       = "";
            $charelectronic  = $charelectronic . '|' . $localidad;
            $referenciacalle = "";
            $charelectronic  = $charelectronic . '|' . $referenciacalle;
            $municipio       = $myrow['braddress3'];
            $charelectronic  = $charelectronic . '|' . $municipio;
            $edo             = $myrow['braddress4'];
            $charelectronic  = $charelectronic . '|' . $edo;
            $cp              = $myrow['braddress5'];
            $charelectronic  = $charelectronic . '|' . $cp;

        }
    }
    // cadena para datos de los productos
    // productos vendidos

    $charelectronicdetail = '';
    $charelectronicinidet = '|' . chr(13) . chr(10) . '05';
    // datos de ivas
    $charelectronictaxs    = '';
    $charelectronictaxsini = '|' . chr(13) . chr(10) . '07';
    $stockid               = 'Nota ' . $myrow['transno'];
    $charelectronicdetail  = $charelectronicdetail . $charelectronicinidet . '|' . $stockid;
    $charelectronicdetail  = $charelectronicdetail . '|' . $myrow['transno'];
    $cantidad              = 1;
    $stockcantidad         = number_format($cantidad, 4, '.', '');
    $charelectronicdetail  = $charelectronicdetail . '|' . $stockcantidad;
    $stockdescrip          = $myrow['reference'];
    $charelectronicdetail  = $charelectronicdetail . '|' . $stockdescrip;
    $stockprecio           = number_format(abs($myrow['ovamount']), 2, '.', '');
    $charelectronicdetail  = $charelectronicdetail . '|' . $stockprecio;
    $stockneto             = number_format(abs($myrow['ovamount']), 2, '.', '');
    $charelectronicdetail  = $charelectronicdetail . '|' . $stockneto;
    $stockunits            = '';
    $charelectronicdetail  = $charelectronicdetail . '|' . $stockunits;
    $stockcat              = 'NC';
    $charelectronicdetail  = $charelectronicdetail . '|' . $stockcat;
    $ordencompra           = '';
    $charelectronicdetail  = $charelectronicdetail . '|' . $ordencompra;
    //DESCUENTO 1
    $charelectronicdetail = $charelectronicdetail . '|' . 0;
    //DESCUENTO 2
    $charelectronicdetail = $charelectronicdetail . '|' . 0;
    //DESCUENTO 3
    $charelectronicdetail = $charelectronicdetail . '|' . 0;
    //SUBTOTAL
    $charelectronicdetail = $charelectronicdetail . '|' . number_format(abs($myrow['ovamount']), 2, '.', '');
    //ADUANA
    $charelectronicdetail = $charelectronicdetail . '|' . '';
    //NUMERO DE ADUANA
    $charelectronicdetail = $charelectronicdetail . '|' . '';
    //FECHA DE INGRESO A ADUANA
    $charelectronicdetail = $charelectronicdetail . '|' . '';

    if ($imprimepublico == 0) {
        $impuesto           = "IVA";
        $charelectronictaxs = $charelectronictaxs . $charelectronictaxsini . '|' . $impuesto;
        $totalcred          = abs($myrow['ovamount']) / (abs($myrow['ovgst']) + abs($myrow['ovamount']));
        $totalcred          = 1 - $totalcred;
        $taxrate            = number_format($totalcred * 100, 2, '.', '');
        $charelectronictaxs = $charelectronictaxs . '|' . $taxrate;
        $taxratetotal       = number_format(abs($myrow['ovgst']), 2, '.', '');
        $charelectronictaxs = $charelectronictaxs . '|' . $taxratetotal;
    }

    $charelectronicEmbarque = '|' . chr(13) . chr(10) . '09';
    $charelectronicEmbarque = $charelectronicEmbarque . '|' . $myrow['specialinstructions'];
    $charelectronicEmbarque = $charelectronicEmbarque . '|' . $myrow['brpostaddr1']; //calle
    $charelectronicEmbarque = $charelectronicEmbarque . '|'; //noext
    $charelectronicEmbarque = $charelectronicEmbarque . '|'; //no int

    $charelectronicEmbarque = $charelectronicEmbarque . '|' . $myrow['brpostaddr2']; //colonia
    $charelectronicEmbarque = $charelectronicEmbarque . '|' . $myrow['brpostaddr3']; //municipio
    $charelectronicEmbarque = $charelectronicEmbarque . '|' . $myrow['brpostaddr4']; //cp
    $charelectronicEmbarque = $charelectronicEmbarque . '|' . $myrow['brpostaddr5']; //estado
    $charelectronicEmbarque = $charelectronicEmbarque . '|' . $myrow['brpostaddr6']; //pais
    $charelectronicEmbarque = $charelectronicEmbarque . '|' . $myrow['brpostaddr7'];

    // ivas retenidos
    //$charelectronictaxsret='|'.chr(13).chr(10);//.'07|ISR|0.00';

    if ($charelectronictaxs != "") {
        debug_sql($charelectronic . $charelectronicdetail . $charelectronictaxs . $charelectronictaxsret . $charelectronicEmbarque, __LINE__, true, __FILE__);
        return $charelectronic . $charelectronicdetail . $charelectronictaxs . $charelectronictaxsret . $charelectronicEmbarque;
    } else {
        debug_sql($charelectronic . $charelectronicdetail . $charelectronictaxsret . $charelectronicEmbarque, __LINE__, true, __FILE__);
        return $charelectronic . $charelectronicdetail . $charelectronictaxsret . $charelectronicEmbarque;
    }

}

function XSAInvoicingRecibo($InvoiceNo, $orderno, $debtorno, $TypeInvoice, $tag, $serie, $folio, &$db,$flagfiscal=0)
{
    /*
    $metodoPago = "No Identificado";
    $metodo = 'SELECT paymentname FROM gltrans INNER JOIN paymentmethodssat ON typepaid=paymentid WHERE typeno ="'.$InvoiceNo.'" AND TYPE="'.$TypeInvoice.'"';
    $rmetodo = DB_query($metodo,$db);
    if (DB_num_rows($rmetodo)>0) {
    $row = DB_fetch_array($rmetodo);
    $metodoPago = $row['paymentname'] ;
    }else{
    $metodoPago = "No Identificado";
    }
     */

    //Agregar datos de CFDI 3.3
    $c_TipoDeComprobante = "";
    $c_UsoCFDI = "";
    $c_paymentid = "";
    $claveFactura = "";
    $decimalplaces = "";

    $sql = "SELECT debtortrans.observf,  debtortrans.id, debtortrans.nocuenta, IFNULL(paymentmethods.codesat,'') as codesat, 
            debtortrans.c_TipoDeComprobante,
            debtortrans.c_UsoCFDI,
            debtortrans.c_paymentid,
            debtortrans.claveFactura,
            currencies.decimalplaces
			FROM debtortrans
            LEFT JOIN currencies ON currencies.currabrev = debtortrans.currcode
			LEFT JOIN paymentmethods ON debtortrans.paymentname = paymentmethods.paymentname
			WHERE debtortrans.type = " . $TypeInvoice . "
				AND debtortrans.transno = " . $InvoiceNo;
    $rs = DB_query($sql, $db);
    if (DB_num_rows($rs) > 0) {
        $reg          = DB_fetch_array($rs);
        $terminospago = $reg['observf'];
        $metodoPago   = $reg['codesat'];
        $idfrom       = $reg['id'];
        $c_TipoDeComprobante = $reg['c_TipoDeComprobante'];
        $c_UsoCFDI = $reg['c_UsoCFDI'];
        $c_paymentid = $reg['c_paymentid'];
        $claveFactura = $reg['claveFactura'];
        $decimalplaces = $reg['decimalplaces'];

        if ($metodoPago == "") {
            $metodoPago = "98";
        }

        $nocuenta = $reg['nocuenta'];
        if ($nocuenta == "") {
            $nocuenta = "No Identificado";
        }
    }

    $sql = "SELECT debtortrans.uuid as folio,
					debtortrans.fechatimbrado as fecha ,
					(debtortrans.ovamount + ovgst) as monto
			FROM custallocns
				INNER JOIN debtortrans ON custallocns.transid_allocto = debtortrans.id
			WHERE  transid_allocfrom = '" . $idfrom . "'";
        // se hizo cambio para que se tome el UUID de la factura
    $sql = "SELECT 
            if(debtortrans.type<> '70', debtortrans.uuid, (SELECT uuid 
            FROM debtortrans d2 
            INNER JOIN custallocns c2 ON c2.transid_allocto = d2.id  
            WHERE c2.transid_allocfrom =debtortrans.id )) as folio,
            if(debtortrans.type <>'70',debtortrans.fechatimbrado, (SELECT d2.fechatimbrado 
            FROM debtortrans d2 
            INNER JOIN custallocns c2 ON c2.transid_allocto = d2.id  
            WHERE c2.transid_allocfrom =debtortrans.id )) as fecha , debtortrans.type,
            (debtortrans.ovamount + ovgst) as monto,
            if(debtortrans.type<> '70', substring(debtortrans.cadena, 3, 3) ,(SELECT substring(cadena, 3, 3) as version FROM debtortrans d2 INNER JOIN custallocns c2 ON c2.transid_allocto = d2.id WHERE c2.transid_allocfrom =debtortrans.id )) as version
             FROM custallocns 
            INNER JOIN debtortrans ON custallocns.transid_allocto = debtortrans.id WHERE transid_allocfrom ='".$idfrom."'";

    //UUID RELACIONADO sql  
    //echo "sql: ".  $sql;   
    $sqlRelacionado=$sql;

    $rs = DB_query($sql, $db);
    if (DB_num_rows($rs) > 0) {
        $reg          = DB_fetch_array($rs);
        $folifisorig  = $reg['folio'];
        $fechafisorig = $reg['fecha'];

        $montofisorig = number_format($reg['monto'], 2, '.', '');
    }
    $charelectronic = '01';
    $charelectronic = $charelectronic . '|' . $serie . $folio;
    $serieelect     = $serie;
    $folioelect     = $folio;
    $charelectronic = $charelectronic . '|' . $serieelect;
    $charelectronic = $charelectronic . '|' . $folioelect;

    // consulto datos de la factura****
    $SQLInvoice = "SELECT
				replace(debtortrans.origtrandate,'-','/') as origtrandate,
                replace(debtortransmovs.origtrandate,'-','/') as origtrandate2,
				SUM(abs(debtortransmovs.ovamount)) AS ovamount,
				SUM(abs(debtortransmovs.ovgst)) AS ovgst,
				debtortransmovs.currcode,
				debtortransmovs.rate as cambio,
				debtortransmovs.order_,
				replace(debtortransmovs.trandate,'-','/') as trandate,
				debtortransmovs.debtorno,
				custbranch.taxid as rfc,
				debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				debtorsmaster.address5,
				debtortransmovs.branchcode,
				custbranch.braddress1,
				custbranch.braddress2,
				custbranch.braddress3,
				custbranch.braddress4,
				custbranch.braddress5,
	            custbranch.braddress6,
				www_users.realname,
				custbranch.brpostaddr1,
				custbranch.brpostaddr2,
				custbranch.brpostaddr3,
				custbranch.brpostaddr4,
				custbranch.brpostaddr5,
				custbranch.brpostaddr6,
				custpais as brpostaddr7,
				custbranch.brnumext,
				custbranch.brnumint
			FROM debtortransmovs,debtorsmaster,custbranch, www_users,debtortrans
			WHERE debtortransmovs.type = " . $TypeInvoice . "
				AND debtortransmovs.transno = " . $InvoiceNo . "
				AND debtortransmovs.tagref = '" . $tag . "'
				AND debtortransmovs.debtorno=debtorsmaster.debtorno
				AND debtortransmovs.debtorno=custbranch.debtorno
				AND debtortransmovs.branchcode=custbranch.branchcode
				AND www_users.userid = debtortransmovs.userid
                AND debtortrans.transno = debtortransmovs.transno AND debtortrans.type = debtortransmovs.type
				-- AND debtortransmovs.reference NOT IN ('0','90')
			GROUP BY debtortransmovs.currcode,
				debtortransmovs.rate,
				debtortransmovs.order_,
				debtortransmovs.debtorno,
				custbranch.taxid,
				debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				debtorsmaster.address5,
				debtortransmovs.branchcode,
				custbranch.braddress1,
				custbranch.braddress2,
				custbranch.braddress3,
				custbranch.braddress4,
				custbranch.braddress5,
				custbranch.brnumext,
				custbranch.brnumint,
				www_users.realname";
    //echo __line__.'--><pre>"'.$SQLInvoice.'"'.__FILE__;
    $Result = DB_query($SQLInvoice, $db);

    if (DB_num_rows($Result) > 0) {
        {
            $myrow = DB_fetch_array($Result);

            $charelectronicEmbarque = '|' . chr(13) . chr(10) . '09';
            $charelectronicEmbarque = $charelectronicEmbarque . '|' . $myrow['specialinstructions'];
            $charelectronicEmbarque = $charelectronicEmbarque . '|' . $myrow['brpostaddr1']; //calle
            $charelectronicEmbarque = $charelectronicEmbarque . '|'; //noext
            $charelectronicEmbarque = $charelectronicEmbarque . '|'; //no int

            $charelectronicEmbarque = $charelectronicEmbarque . '|' . $myrow['brpostaddr2']; //colonia
            $charelectronicEmbarque = $charelectronicEmbarque . '|' . $myrow['brpostaddr3']; //municipio
            $charelectronicEmbarque = $charelectronicEmbarque . '|' . $myrow['brpostaddr4']; //cp
            $charelectronicEmbarque = $charelectronicEmbarque . '|' . $myrow['brpostaddr5']; //estado
            $charelectronicEmbarque = $charelectronicEmbarque . '|' . $myrow['brpostaddr6']; //pais
            $charelectronicEmbarque = $charelectronicEmbarque . '|' . $myrow['brpostaddr7'];

            //*****METODO DE PAGOS
            /*
            $metodoPago = trim($myrow['codesat']);

            if($metodoPago == "") {
            $metodoPago = "98";
            }
             */

            // fecha emision//
            if (!strpos("@".$_SESSION["DatabaseName"], "servillantas") AND  !strpos("@".$_SESSION["DatabaseName"], "erptycqsa")) {
                $fechainvoice   = $myrow['origtrandate'];
            }else{
                $fechainvoice   = $myrow['origtrandate2'];
            }
            
            //echo "FECHA INVOICE".$fechainvoice;
            $UserRegister   = ""; //$myrow['UserRegister'];
            $charelectronic = $charelectronic . '|' . $fechainvoice;
            // subtotal
            $rfccliente = str_replace("-", "", $myrow['rfc']);
            $rfccliente = str_replace(" ", "", $rfccliente);
            $nombre     = $myrow['name'];
            $subtotal   = number_format($myrow['ovamount'], 2, '.', '');
            if ((strlen($rfccliente) < 12) or (strlen($rfccliente) >= 14)) {
                $rfccliente     = "XAXX010101000";
                $nombre         = "Publico en General";
                $subtotal       = number_format($myrow['ovamount'] + $myrow['ovgst'], 2, '.', '');
                $imprimepublico = 1;
            }
            $charelectronic = $charelectronic . '|' . $subtotal;
            // total factura
            $total          = number_format($myrow['ovamount'] + $myrow['ovgst'], 2, '.', '');
            $charelectronic = $charelectronic . '|' . $total;
            // total de iva
            $iva = number_format($myrow['ovgst'], 2, '.', '');
            // transladado
            $charelectronic = $charelectronic . '|' . $iva;
            // retenido
            $ivaret         = 0;
            $charelectronic = $charelectronic . '|' . $ivaret;
            //descuento
            $descuento      = number_format(0, 2, '.', '');
            $charelectronic = $charelectronic . '|' . $descuento;
            //motivo descuento
            $charelectronic = $charelectronic . '|';
            // tipo de moneda
            $moneda = $myrow['currcode'];
            // CANTIDAD CON LETRAS
            $totaletras = $myrow['ovamount'] + $myrow['ovgst'];
            $separa     = explode(".", $totaletras);
            $montoctvs2 = $separa[1];
            $montoctvs1 = $separa[0];
            if ($montoctvs2 > 995) {
                $montoctvs1 = $montoctvs1 + 1;
            }
            $montoletra = Numbers_Words::toWords($montoctvs1, 'es');
            $totaletras = number_format($totaletras, 2);
            $separa     = explode(".", $totaletras);
            $montoctvs2 = $separa[1];
            if ($montoctvs2 > 995) {
                $montoctvs2 = 0;
            } //
            $montocentavos = Numbers_Words::toWords($montoctvs2, 'es');
            if ($moneda == 'MXN') {
                $montoletra = ucwords($montoletra) . " Pesos " . $montoctvs2 . " /100 M.N.";
            } else {
                $montoletra = ucwords($montoletra) . " Dolares " . $montoctvs2 . "/100 USD";
            } //
            $charelectronic = $charelectronic . '|' . $montoletra;
            // tipo moneda
            $charelectronic = $charelectronic . '|' . $moneda;
            // tipo de cambio
            $rate           = number_format($myrow['cambio'], 4, '.', '');
            $charelectronic = $charelectronic . '|' . $rate;
            // numero de orden para referencia
            $ordenref       = $myrow['order_'];
            $charelectronic = $charelectronic . '|' . $ordenref;
            // observaciones 1: vendedores
            $vendedor = $myrow['realname'];

            $observaciones1 = 'Vendedor: ' . $myrow['salesman'] . ' ' . $vendedor;
            $charelectronic = $charelectronic . '|' . $observaciones1;
            // observaciones 2
            $SQL = " SELECT l.telephone,l.comments,t.tagdescription
			       FROM legalbusinessunit l, tags t
			       WHERE l.legalid=t.legalid AND tagref='" . $tag . "'";
            $Result = DB_query($SQL, $db);
            if (DB_num_rows($Result) == 1) {
                $myrowtags = DB_fetch_array($Result);
                $telephone = trim($myrowtags['telephone']);
                $comments  = trim($myrowtags['comments']);
            }
            $observaciones2 = " Atencion a clientes " . $telephone;
            $charelectronic = $charelectronic . '|' . $observaciones2;
            // observaciones 3
            $observaciones3 = 'Id Recibo:' . $InvoiceNo; //' '.$comments .' usr:'.$UserRegister;

            $TypeInvoice . "
			AND debtortransmovs.transno=" . $InvoiceNo . "
			AND debtortransmovs.tagref=" . $tag .

            $XSQL = "SELECT gltrans.*
			FROM gltrans, chartmaster, accountgroups,companies
			WHERE gltrans.tag = '" . $tag . "'
			and gltrans.type = " . $TypeInvoice . "
			and gltrans.typeno = " . $InvoiceNo . "
			and gltrans.amount > 0
			and gltrans.account = chartmaster.accountcode
			and chartmaster.group_=accountgroups.groupname AND accountgroups.pandl=0
			and (chartmaster.accountcode = companies.gltempcashpayment or
				chartmaster.accountcode = companies.gltempcheckpayment or
				chartmaster.accountcode = companies.gltempccpayment or
			chartmaster.accountcode = companies.gltemptransferpayment)";

            $resultxsql     = DB_query($XSQL, $db);
            $detallerecibos = "";
            $x              = 1;
            while ($xmyrow = DB_fetch_array($resultxsql)) {
                $x              = $x + 1;
                $detallerecibos = $detallerecibos . " - " . $xmyrow['account'] . "-> " . abs($xmyrow['amount']);
            }

            $observaciones3 = $observaciones3 . $detallerecibos . "; " . "Este comprobante es complementario a los expedidos en la fecha y folios descritos en cada partida detallada arriba";

            $charelectronic = $charelectronic . '|' . $observaciones3 . $detallerecibos;

            //SE AGREGA NUEVO CAMPO INFORMACION EXTRA
            $charelectronic = $charelectronic . '|' . '';

            // datos de la forma de pago
            $charelectronic = $charelectronic . '|' . $folifisorig . '|' . $fechafisorig . '|' . $montofisorig;
            $charelectronic = $charelectronic . '|' . $c_TipoDeComprobante; //Cad 0, pos 22
            $charelectronic = $charelectronic . '|' . $c_UsoCFDI; //Cad 0, pos 23
            $charelectronic = $charelectronic . '|' . $c_paymentid; //Cad 0, pos 24
            $charelectronic = $charelectronic . '|' . $claveFactura; //Cad 0, pos 25
            $charelectronic = $charelectronic . '|' . $decimalplaces; //Cad 0, pos 26

            $charelectronic = $charelectronic . '|' . chr(13) . chr(10) . '02';
            if ($terminospago == "") {
                $terminospago = "Pago en una sola exhibicion";
            }
            //

            $Tipopago = $Tipopago . ' ' . $terminospago;
            //$Tipopago=$terminospago;
            $charelectronic = $charelectronic . '|' . $Tipopago;
            // condiciones de pago
            $charelectronic = $charelectronic . '|---';
            // metodo de pago
            //$metodopago='Varios';
            $charelectronic = $charelectronic . '|' . $metodoPago;
            // fecha vencimiento
            $fechavence     = $myrow['trandate'];
            $charelectronic = $charelectronic . '|' . $fechavence;
            // observaciones 4
            $observaciones4 = $observaciones3;
            $charelectronic = $charelectronic . '|' . $nocuenta;

            //UUID RELACIONADO
            $charelectronicdetuuid= '';
            $charelectroniuid = '|' . chr(13) . chr(10) . '012';



            $rs = DB_query($sqlRelacionado, $db);
            if (DB_num_rows($rs) > 0) {
                while ($reg= DB_fetch_array($rs)) {
                    $folifisorig  = $reg['folio'];
                    if($reg['version'] == '3.2' ){
                        $charelectronicdetuuid = $charelectronicdetuuid . '|' .$charelectroniuid . '|08|' . $folifisorig;
                    }else{
                        $charelectronicdetuuid = $charelectronicdetuuid . '|' .$charelectroniuid . '||' . $folifisorig;
                    }
                    
                }
            }

           /* $strSQLUUID = " SELECT UUID FROM log_cancelacion_sustitucion WHERE transNo='".$InvoiceNo."' AND type ='".$TypeInvoice."'";

            //echo "<br>UUID: ".$strSQLUUID;
                $resultSQLUUID=DB_query($strSQLUUID,$db);
                while ($myrowUUID=DB_fetch_array($resultSQLUUID)) {
                    $charelectroniuid = '|' . chr(13) . chr(10) . '012';
                    $charelectronicdetuuid = "";
                   // $charelectronic=$charelectronic.'|04|'.$myrowUUID['UUID'];
                    $charelectronicdetuuid = $charelectronicdetuuid . '|' .$charelectroniuid . '|04|' . $myrowUUID['UUID'];
                    
                }*/
            $strSQLUUID = " SELECT UUID FROM log_cancelacion_sustitucion WHERE transNo='".$InvoiceNo."' AND type ='".$TypeInvoice."'";
            $charelectronicdetuuid= '';
            echo "<br>UUID: ".$strSQLUUID;
            $resultSQLUUID=DB_query($strSQLUUID,$db);
            if(DB_num_rows($resultSQLUUID)>0){
                while ($myrowUUID=DB_fetch_array($resultSQLUUID)) {
                    $charelectroniuid = '|' . chr(13) . chr(10) . '012';
                    //$charelectronicdetuuid = "";
                   // $charelectronic=$charelectronic.'|04|'.$myrowUUID['UUID'];
                    $charelectronicdetuuid = $charelectronicdetuuid . '|' .$charelectroniuid . '|04|' . $myrowUUID['UUID'];
                    
                }

            }else{
                $strSQLUUID= "SELECT log_complemento_sustitucion.folio, log_complemento_sustitucion.type, 
                log_complemento_sustitucion.uuid, log_complemento_sustitucion.transno
                FROM log_complemento_sustitucion 
                INNER JOIN debtortrans ON log_complemento_sustitucion.sustitucion_from = debtortrans.id
                WHERE debtortrans.transno='".$InvoiceNo."' AND debtortrans.type ='".$TypeInvoice."'";

                $resultSQLUUID=DB_query($strSQLUUID,$db);
                if(DB_num_rows($resultSQLUUID) >0){
                    while ($myrowUUID=DB_fetch_array($resultSQLUUID)) {
                        $charelectroniuid = '|' . chr(13) . chr(10) . '012';
                        //$charelectronicdetuuid = "";
                        // $charelectronic=$charelectronic.'|04|'.$myrowUUID['UUID'];
                        $charelectronicdetuuid = $charelectronicdetuuid . '|' .$charelectroniuid . '|04|' . $myrowUUID['uuid']; 


                    }
                }

            }

                //echo "<br>UUID2: ".$charelectronicdetuuid;

            $charelectronic = $charelectronic . $charelectronicdetuuid;

            // datos del cliente
            $charelectronic = $charelectronic . '|' . chr(13) . chr(10) . '03';
            $branch         = $myrow['debtorno'];
            $charelectronic = $charelectronic . '|' . $branch;
            $charelectronic = $charelectronic . '|' . $rfccliente;
            $charelectronic = $charelectronic . '|' . $nombre;
            $pais           = "Mexico"; //$myrow['name'];
            $charelectronic = $charelectronic . '|' . $pais;

        }
        {
            $calle = "";
            if ($imprimepublico == 0) {
                $calle = $myrow['address1'];
            }
            $charelectronic = $charelectronic . '|' . $calle;
            $noext          = $myrow['brnumext'];
            $charelectronic = $charelectronic . '|' . $noext;
            $noint          = $myrow['brnumint'];
            $charelectronic = $charelectronic . '|' . $noint;
            $colonia        = "";
            if ($imprimepublico == 0) {
                $colonia = $myrow['braddress6'];
            } //
            $charelectronic  = $charelectronic . '|' . $colonia;
            $localidad       = "";
            $charelectronic  = $charelectronic . '|' . $localidad;
            $referenciacalle = "";
            $charelectronic  = $charelectronic . '|' . $referenciacalle;
            $municipio       = "";
            if ($imprimepublico == 0) {
                $municipio = $myrow['braddress2'];
            }
            $charelectronic = $charelectronic . '|' . $municipio;
            $edo            = "";
            if ($imprimepublico == 0) {
                $edo = $myrow['braddress3'];
            }
            $charelectronic = $charelectronic . '|' . $edo;
            $cp             = "";
            if ($imprimepublico == 0) {
                $cp = $myrow['braddress4'];
            } ////
        }
        {
            $charelectronic = $charelectronic . '|' . $cp;
            // datos del custbranch
            $charelectronic = $charelectronic . '|' . chr(13) . chr(10) . '04';
            $branch         = $myrow['branchcode'];
            $charelectronic = $charelectronic . '|' . $branch;
            //$rfc=$myrow['rfc'];
            //$charelectronic=$charelectronic.'|'.$rfccliente;
            $nombre          = $myrow['name'];
            $charelectronic  = $charelectronic . '|' . $nombre;
            $pais            = "Mexico";
            $charelectronic  = $charelectronic . '|' . $pais;
            $calle           = $myrow['braddress1'];
            $charelectronic  = $charelectronic . '|' . $calle;
            $noext           = $myrow['brnumext'];
            $charelectronic  = $charelectronic . '|' . $noext;
            $noint           = $myrow['brnumint'];
            $charelectronic  = $charelectronic . '|' . $noint;
            $colonia         = $myrow['braddress6'];
            $charelectronic  = $charelectronic . '|' . $colonia;
            $localidad       = "";
            $charelectronic  = $charelectronic . '|' . $localidad;
            $referenciacalle = "";
            $charelectronic  = $charelectronic . '|' . $referenciacalle;
            $municipio       = $myrow['braddress2'];
            $charelectronic  = $charelectronic . '|' . $municipio;
            $edo             = $myrow['braddress3'];
            $charelectronic  = $charelectronic . '|' . $edo;
            $cp              = $myrow['braddress4'];
            $charelectronic  = $charelectronic . '|' . $cp;

        }

    }


    // cadena para datos de los productos
    // productos vendidos

    $charelectronicdetail = '';
    $charelectronicinidet = '|' . chr(13) . chr(10) . '05';
    // datos de ivas
    $charelectronictaxs    = '';
    $charelectronictaxsini = '|' . chr(13) . chr(10) . '07';

    /*
    $sqldetails = "SELECT debtortransmovs.id,
    debtortransmovs.reference as identificador,
    1 as cantidad,
    case when debtortransmovs.reference in ('80','90','21')
    then debtortransmovs.invtext
    else concat(debtortransmovs.invtext, ' (Complementario al Folio: ', replace(IFNULL(debtortrans.folio,''),'|','-'), ' con fecha: ') end  as descripcion,
    abs(debtortransmovs.ovamount) as valorunitario,
    abs(debtortransmovs.ovamount) as importe,
    'No Identificado' as unidademedida,
    systypescat.typename as categoria,
    debtortransmovs.order_ as ordencompra,
    abs(debtortransmovs.ovgst) as iva,
    DAY(debtortrans.origtrandate) AS diaorigtrandate,
    MONTH(debtortrans.origtrandate) AS mesorigtrandate,
    YEAR(debtortrans.origtrandate) AS anioorigtrandate,
    debtortransmovs.invtext as descripcion2
    FROM debtortransmovs, systypescat, debtortrans
    WHERE debtortransmovs.type=".$TypeInvoice."
    AND debtortransmovs.transno=" . $InvoiceNo . "
    AND debtortransmovs.tagref=" . $tag . "
    AND (
    (SUBSTRING(debtortransmovs.reference, 1, LOCATE(' - ', debtortransmovs.reference)-1) = systypescat.typeid
    AND SUBSTRING(debtortransmovs.reference, 1, LOCATE(' - ', debtortransmovs.reference)-1) = debtortrans.type
    AND SUBSTRING(debtortransmovs.reference, LOCATE(' - ', debtortransmovs.reference)+3) = debtortrans.transno)
    OR
    (debtortransmovs.reference = systypescat.typeid
    AND debtortransmovs.reference = debtortrans.type
    AND debtortransmovs.transno = debtortrans.transno
    AND debtortransmovs.reference in (80,90,21))
    )";
     */

    $sqldetails = "SELECT d1.id,
			CONCAT(d2.type, ' -  ', d2.transno) as identificador,
			1 as cantidad,
                        case when d2.type in ('80','90','21')
				then d1.invtext
				else concat('AFECTO DOCUMENTO ', d2.transno, ' (Complementario al Folio: ', CASE WHEN d3.folio is null Then replace(IFNULL(d2.folio,''),'|','-') else replace(IFNULL(d3.folio,''),'|','-') end, ' Folio Fiscal UUID: ', case when d2.type=70 then d3.uuid else IFNULL(d2.uuid,'') end , ' con fecha: ') end  as descripcion,


			c.amt / (1+ ((d2.ovgst*1)/d2.ovamount)) as valorunitario,
			c.amt / (1+ ((d2.ovgst*1)/d2.ovamount)) as importe,

			'NA' as unidademedida,
			t.typename as categoria,
			d2.order_  as ordencompra,
			c.amt - (c.amt / (1+ ((d2.ovgst*1)/d2.ovamount))) as iva,
			DAY(d2.origtrandate) AS diaorigtrandate,
			MONTH(d2.origtrandate) AS mesorigtrandate,
			YEAR(d2.origtrandate) AS anioorigtrandate,
			d2.invtext as descripcion2
			FROM debtortrans d1
				left join custallocns c ON d1.id= c.transid_allocfrom
				left join debtortrans d2 ON c.transid_allocto  = d2.id
				left join debtortrans d3 ON d2.order_  = d3.transno and d3.type=10
				left join systypescat t ON d2.type = t.typeid
			WHERE d1.type=" . $TypeInvoice . "
			AND d1.transno=" . $InvoiceNo ;

    if($flagfiscal==1){
        $sqldetails .="  ORDER BY c.amt / (1+ ((d2.ovgst*1)/d2.ovamount)) DESC 
            LIMIT 1";

    }
    ////
    //echo     "<br>" . $sqldetails;
    $resultdetails = DB_query($sqldetails, $db);
    while ($myrow2 = DB_fetch_array($resultdetails)) {
        if ($imprimepublico == 0) {
            $stockid              = $myrow2['id'];
            $charelectronicdetail = $charelectronicdetail . $charelectronicinidet . '|' . $stockid;
            $stockid              = $myrow2['identificador'];
            $charelectronicdetail = $charelectronicdetail . '|' . $stockid;
            $cantidad             = $myrow2['cantidad'];
            $stockcantidad        = number_format($cantidad, 4, '.', '');
            $charelectronicdetail = $charelectronicdetail . '|' . $stockcantidad;

            if (!is_null($myrow2['descripcion'])) {
                $diaorigtrandate  = $myrow2['diaorigtrandate'];
                $mesorigtrandate  = $myrow2['mesorigtrandate'];
                $anioorigtrandate = $myrow2['anioorigtrandate'];
                $stockdescrip     = $myrow2['descripcion'] . " (" . $diaorigtrandate . "-" . nombremescorto($mesorigtrandate) . "-" . $anioorigtrandate . ")";
            } else {
                $stockdescrip = $myrow2['descripcion2'];
            }

            $charelectronicdetail = $charelectronicdetail . '|' . $stockdescrip;
            $stockprecio          = number_format(abs($myrow2['valorunitario']), 2, '.', '');
            $charelectronicdetail = $charelectronicdetail . '|' . $stockprecio;
            $stockneto            = number_format(abs($myrow2['importe']), 2, '.', '');
            $charelectronicdetail = $charelectronicdetail . '|' . $stockneto;
            $stockunits           = $myrow2['unidademedida'];
            $charelectronicdetail = $charelectronicdetail . '|' . $stockunits;
            $stockcat             = $myrow2['categoria'];
            $charelectronicdetail = $charelectronicdetail . '|' . $stockcat;
            $ordencompra          = $myrow2['ordencompra'];
            $charelectronicdetail = $charelectronicdetail . '|' . $ordencompra;
            //DESCUENTO 1
            $charelectronicdetail = $charelectronicdetail . '|' . '0.00';
            //DESCUENTO 2
            $charelectronicdetail = $charelectronicdetail . '|' . '0.00';
            //DESCUENTO 3
            $charelectronicdetail = $charelectronicdetail . '|' . '0.00';
            //SUBTOTAL
            $charelectronicdetail = $charelectronicdetail . '|' . number_format(abs($myrow2['importe']), 2, '.', '');
            //ADUANA
            $charelectronicdetail = $charelectronicdetail . '|' . '';
            //NUMERO DE ADUANA
            $charelectronicdetail = $charelectronicdetail . '|' . '';
            //FECHA DE INGRESO A ADUANA
            $charelectronicdetail = $charelectronicdetail . '|' . '';
            $impuesto             = "IVA";
            $charelectronictaxs   = $charelectronictaxs . $charelectronictaxsini . '|' . $impuesto;
            //$taxrate=number_format(.15*100,2,'.','');
            $taxrate            = number_format((((abs($myrow2['iva']) + abs($myrow2['importe'])) / abs($myrow2['importe'])) - 1), 2);
            $taxrate            = $taxrate * 100;
            $charelectronictaxs = $charelectronictaxs . '|' . $taxrate;
            $taxratetotal       = number_format(abs($myrow2['iva']), 2, '.', '');
            $charelectronictaxs = $charelectronictaxs . '|' . $taxratetotal;
        } else {
            $stockid              = $myrow2['id'];
            $charelectronicdetail = $charelectronicdetail . $charelectronicinidet . '|' . $stockid;
            $stockid              = $myrow2['identificador'];
            $charelectronicdetail = $charelectronicdetail . '|' . $stockid;
            $cantidad             = $myrow2['cantidad'];
            $stockcantidad        = number_format($cantidad, 4, '.', '');
            $charelectronicdetail = $charelectronicdetail . '|' . $stockcantidad;

            if (!is_null($myrow2['descripcion'])) {
                $diaorigtrandate  = $myrow2['diaorigtrandate'];
                $mesorigtrandate  = $myrow2['mesorigtrandate'];
                $anioorigtrandate = $myrow2['anioorigtrandate'];

                $stockdescrip = $myrow2['descripcion'] . $diaorigtrandate . "-" . nombremescorto($mesorigtrandate) . "-" . $anioorigtrandate . ")";
            } else {
                $stockdescrip = $myrow2['descripcion2'];
            }

            $charelectronicdetail = $charelectronicdetail . '|' . $stockdescrip;
            $stockprecio          = number_format(abs($myrow2['valorunitario'] + $myrow2['iva']), 2, '.', '');
            $charelectronicdetail = $charelectronicdetail . '|' . $stockprecio;
            $stockneto            = number_format(($cantidad * abs($myrow2['valorunitario'] + $myrow2['iva'])), 2, '.', '');
            $charelectronicdetail = $charelectronicdetail . '|' . $stockneto;
            $stockunits           = $myrow2['unidademedida'];
            $charelectronicdetail = $charelectronicdetail . '|' . $stockunits;
            $stockcat             = $myrow2['categoria'];
            $charelectronicdetail = $charelectronicdetail . '|' . $stockcat;
            $ordencompra          = $myrow2['ordencompra'];
            $charelectronicdetail = $charelectronicdetail . '|' . $ordencompra;
        }

    }
    
    // ivas retenidos
    //$charelectronictaxsret='|'.chr(13).chr(10);//.'07|ISR|0.00';
   

    if ($charelectronictaxs != "") {
        return $charelectronic . $charelectronicdetail . $charelectronictaxs . $charelectronictaxsret . $charelectronicEmbarque ;
    } else {
        return $charelectronic . $charelectronicdetail . $charelectronictaxsret . $charelectronicEmbarque;
    }

}

function retornarString($cadena)
{
    $login = $cadena;
    $b     = array("�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�", "�");
    $c     = array("a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "A", "E", "I", "O", "U");
    $login = str_replace($b, $c, $login);
    return $login;
}
