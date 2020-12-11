<?php

$id=$_GET['id'];
$ErrMsg='';
$timbrador = "";
	$sqlConf = "SELECT confname, confvalue FROM config";
	$rsConf = DB_query($sqlConf, $db);
	$config = array();
	while($rowConf = DB_fetch_array($rsConf)) {
		$config[$rowConf['confname']] = $rowConf['confvalue'];
	}
	$config['DatabaseName'] = $_SESSION['DatabaseName'];
	
$SQL="SELECT debtortrans.uuid,
			debtortrans.tagref,
			debtortrans.folio,
			debtortrans.id,
			debtortrans.origtrandate,
			debtortrans.transno,
			legalbusinessunit.taxid
	  FROM debtortrans
		INNER JOIN tags ON debtortrans.tagref = tags.tagref
	 	INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
	  WHERE ABS(debtortrans.ovamount) = 0
		AND debtortrans.uuid <> ''
		AND invtext like '%CANCELAD%'";
//
/*
$SQL="SELECT distinct uuid, tags.tagref,taxid,folio,id
FROM back_ncxcancelar18feb as back_retimbrados 
INNER JOIN stockmoves on stockmoves.type=back_retimbrados.type 
and stockmoves.transno=back_retimbrados.transno
inner join tags on tags.tagref=stockmoves.tagref
inner join legalbusinessunit on legalbusinessunit.legalid=tags.legalid";*/

$ResultDatos=DB_query($SQL,$db,$ErrMsg,'',false,true);
$contador=0;
while ($MyrowDatos=DB_fetch_array($ResultDatos)) {
	$TransNo=$MyrowDatos['transno'];
	$UIID=$MyrowDatos['uuid'];
	$tagref=$MyrowDatos['tagref'];
	$foliox=$MyrowDatos['folio'];
	$separa = explode('|',$foliox);
	$serie = $separa[0];
	$folio = $separa[1];
	$idfactura=$MyrowDatos['id'];
	$fechaorigen=$MyrowDatos['origtrandate'];
	$rfc=$MyrowDatos['taxid'];
	//$rfc=$MyrowDatos['taxid'];
	
	
	
		
		include_once 'timbradores/TimbradorFactory.php';
		$timbrador = TimbradorFactory::getTimbrador($config);
		if($timbrador != null) {
			$timbrador->setRfcEmisor($rfc);
			$timbrador->setDb($db);
			$success = $timbrador->cancelarDocumento($UIID);
			foreach($timbrador->getErrores() as $error) {
				prnMsg($error, 'error');
			}
		} else {
			prnMsg(_('No hay un timbrador configurado en el sistema'), 'error');
		}	
		echo '<br><br>'.$MyrowDatos['folio'];
		if($success) {	
			//$XMLElectronico = generaXMLCancelCFDI($UIID, 'ingreso', $tagref, $serie, $folio, $idfactura, 'Facturas', $fechaorigen, $db);
			prnMsg( _('Numero de Factura') . ' ' . $TransNo . ' ' . _('Se Cancelo con Exito'), 'success');
		} else {
			prnMsg( _('Numero de Factura') . ' ' . $TransNo . ' ' . _('no se cancelo'), 'error');
			//prnMsg(_('No fue posible realizar la cancelacion de timbrado del documento, verifique con el administrador; el numero de error es:') . $cfdi, 'error');
		}
		
	
}
		
?>