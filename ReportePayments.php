<?php
/*
 ARCHIVO MODIFICADO POR: Desarrollador
 FECHA DE MODIFICACION: 14-FEB-2010
 CAMBIOS: 
	1. El movimiento de cancelacion se cambio para que tome periodo y fecha en que se realizo el mov de cheque
 FIN DE CAMBIOS
 
*/

/* $Revision: 1.44 $ */
/*
**CREADO POR FRUEBEL CANTERA
**29-12-2009
**EN ESTE ARCHIVO SE MUESTRA UN LISTADO DE LOS RECIBOS SOLO DEL DIA ACTUAL
**CON LA OPCION DE IMPRIMIR O  CANCELAR EL RECIBO
*/
/*FECHA DE MODIFICACION: 03-FEB-2010
 1.- se modifico la liga para el pagare linea 361 a la 371
 FIN DE CAMBIOS
*/

$funcion=370;
$PageSecurity = 3;
include('includes/session.inc');
$title = _('Busqueda de Cheques');

//LA VARIABLE $ambiente, nos sirvira para para definir en que ambiente se esta ejecutando la pagina
//y asi saber que paginas mandar llamar y que parametros para la facturacion electronica,
//ya sea demo o pagina de facturacion.

if ($_SERVER['SERVER_NAME'] == "erp.servillantas.com"){
	$ambiente = "produccion";
}else{
	$ambiente = "desarrollo";
}


include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');
$msg='';

/*INICIO
**RECUPERO LOS VALORES QUE VIENEN EN EL URL, ESTO SE APLICA CUANDO SE LLAMA
** LA OPCION DE ELIMINAR ALGUN RECIBO
*/
{
	if (isset($_GET['txtfechadesde'])){
		$_POST['txtfechadesde'] = $_GET['txtfechadesde'];
	}
	if (isset($_GET['txtfechahasta'])){
		$_POST['txtfechahasta'] = $_GET['txtfechahasta'];
	}
	if (isset($_GET['txtchequeno'])){
		$_POST['txtchequeno'] = $_GET['txtchequeno'];
	}
	if (isset($_GET['cbounidadnegocio'])){
		$_POST['cbounidadnegocio'] = $_GET['cbounidadnegocio'];
	}
}
/*FIN
*/

if (isset($_POST['txtfechadesde'])){
	$fechadesde = $_POST['txtfechadesde'];	
}else{
	$fechadesde = date('Y') . "/" . date('m') . "/" . date('d');
	//$fechadesde = date('m') . "/" . "23" . "/" . date('Y');
}

if (isset($_POST['txtfechahasta'])){
	$fechahasta = $_POST['txtfechahasta'];	
}else{
	$fechahasta = date('Y') . "/" . date('m') . "/" . date('d');
	//$fechahasta = date('m') . "/" . "23" . "/" . date('Y');
}

if (isset($_POST['txtchequeno'])){
	$recibono = $_POST['txtchequeno'];	
}else{
	$recibono = '';
}
if (isset($_POST['cbounidadnegocio'])){
	$unidadnegocio = $_POST['cbounidadnegocio'];	
}else{
	$unidadnegocio = 0;
}

$serie = $_GET['serie'];
$folio = $_GET['folio'];
$rfc = $_GET['rfc'];
$keyfact = $_GET['keyfact'];
	

if (isset($_POST['btncancela']) && $_POST['btncancela'] != '' && isset($_POST['numerocheque']) && $_POST['numerocheque'] != ''){	
	//echo "cancale...";
	//$numerocheque = "";


	echo "<table width='80%' cellpadding='0' cellspacing='0' border='0'>";

	echo "<tr align='center' width='100%'>";

	echo "<td>";

	echo '<br><br><form method="post" action="' . $_SERVER['PHP_SELF'] . '"><p align="center">';
	echo _('�Desea Cancelar este cheque?') . '<br><br>';
	echo '<input type="hidden" name="ChequeNum" VALUE="' . $_POST['numerocheque'] . '">';
	echo '<input type="hidden" name="movimiento" VALUE="' . $_POST['movimiento'] . '">';
	echo '<input type="submit" name="ChequeCancel" VALUE="' . _('Si / Continua') . '">&nbsp;&nbsp;';
	echo "<input type='hidden' name='tipom' value='".$_POST['tipom']."'>";
	echo '<input type="submit" name="ChequeReturn" VALUE="' . _('No / Regresa Reporte') . '">';
	
	echo '</form>';
	
	echo "</td>";
	
	echo "</tr>";
	
	echo "</table>";

	include('includes/footer.inc');
	exit;
	
}

if (isset($_POST['ChequeCancel'])){
    
    if (isset($_POST['tipom'] )){
            $type=$_POST['tipom'];
    }

    $SQL = 'BEGIN';
    $result = DB_query($SQL,$db);	

    $DefaultDispatchDate=Date($_SESSION['DefaultDateFormat'],CalcEarliestDispatchDate());
    
    $PeriodNo = GetPeriod($DefaultDispatchDate, $db);

    if ($type==501){
            //buscar el id del documento
            $typeno = $_POST['ChequeNum'];
            $qry = "Select id,tagref FROM supptrans WHERE type = '$type' and transno = '$typeno'";
            $rsid = DB_query($qry,$db);
            $row = DB_fetch_array($rsid);
            $id501 = $row['id'];

            $PeriodNo = GetPeriod($DefaultDispatchDate, $db, $row['tagref']);

            //elimino gltrans del 501
            $qry = "Delete from gltrans WHERE type='$tipo' and typeno='$typeno'";
            $r = DB_query($qry,$db);

            //buscar id de factura
            $qry="Select transid_allocto FROM suppallocs
            WHERE transid_allocfrom = $id501";
            $rsfac = DB_query($qry,$db);
            $row = DB_fetch_array($rsfac);
            $idfac = $row['transid_allocto'];

            //buscar type y transno de la factura
            $qry ="Select type,transno FROM supptrans WHERE id = $idfac";
            $rsf = DB_query($qry,$db);
            $row = Db_fetch_array($rsf);
            $tipofac = $row['type'];
            $transnofac = $row['transno'];

            //elimino gltrans de fact
            $qry = "Delete from gltrans WHERE type='$tipofac' and typeno='$transnofac'";
            $r = DB_query($qry,$db);

            //cancelo fact
            $qry = "UpDate supptrans
            Set transtext = concat('CANCELADA ',transtext),
            ovamount=0,
            ovgst=0,
            alloc=0
            WHERE id = $idfac";
            $r = DB_query($qry,$db);

            //elimino doc 501
            $qry ="Delete from supptrans WHERE id = $id501";
            $r = DB_query($qry,$db);

            //eliminar asociacion
            $qry="Delete FROM suppallocs
            WHERE transid_allocfrom = $id501";
            $r = DB_query($qry,$db);

    }
    else
    if ($_POST['movimiento'] == 1){

    $typeno = $_POST['ChequeNum'];
    $qry = "Select id,tagref,trandate FROM supptrans WHERE type = '$type' and transno = '$typeno'";
    $rsid = DB_query($qry,$db);
    $row = DB_fetch_array($rsid);
    $id501 = $row['id'];

    if (strpos($_SESSION['DefaultDateFormat'],'/')) {
        $flag = "/";
    } elseif (strpos ($_SESSION['DefaultDateFormat'],'-')) {
        $flag = "-";
    } elseif (strpos ($_SESSION['DefaultDateFormat'],'.')) {
        $flag = ".";
    }
    $diafecha = substr($row['trandate'], 8, 2);
    $mesfecha = substr($row['trandate'], 5, 2);
    $aniofecha = substr($row['trandate'], 0, 4);
    $trandatef = $diafecha.$flag.$aniofecha.$flag.$aniofecha;
    $PeriodNo = GetPeriod($trandatef, $db, $row['tagref']);

            $SQL = "UPDATE supptrans
                    SET transtext=concat('CANCELADO',' ',transtext,' ',ovamount,'+',ovgst), ovamount=0, ovgst=0, alloc=0 
                    where transno = " . $_POST['ChequeNum'] . " and type = ".$type;
            //echo $SQL . "<br>";
            $TransResult = DB_query($SQL,$db,$ErrMsg);

            /*$SQL = "delete from gltrans
            where type =" .$type." and typeno = " . $_POST['ChequeNum'];
    */
            $SQL = "insert  gltrans(counterindex, type, typeno, chequeno, trandate, periodno, account, narrative, amount, posted, jobref,tag,lasttrandate, dateadded, userid)
                    select null, ".$type.", ".$_POST['ChequeNum'].", 0, trandate, periodno ,account, CONCAT(narrative,' @ Movimiento Cancelado'),(amount*-1),0,0, tag,now(), NOW(), '".$_SESSION['UserID']."'
                    from gltrans
                    where type=".$type." and typeno=".$_POST['ChequeNum'];
            //echo $SQL . "<br>";
            $TransResult = DB_query($SQL,$db,$ErrMsg);

            $SQL = "UPDATE banktrans
                    SET ref=concat('CANCELADO',' ',ref,' ',amount) ,amount=0
                    where transno = " . $_POST['ChequeNum'] . " and type = ".$type;
            //echo $SQL . "<br>";
            $TransResult = DB_query($SQL,$db,$ErrMsg);
            
//            //CANCELAR NOTAS DE CREDITO
//            // -- Buscar registros a desaplicar
            $SQL_sea_notas="SELECT notatransno FROM suppdocpaydocs WHERE chequetransno='".$typeno."';";
//            echo $SQL_sea_notas . "<br>";
            $result_sea=  DB_query($SQL_sea_notas, $db);
            $docs=array();
//            //En doc se obtinen los transno de las notas de credito
            while($myrowssear=DB_fetch_array($result_sea)){
                $docs[]=$myrowssear['notatransno'];
            }
            $SQL = "insert  gltrans(counterindex, type, typeno, chequeno, trandate, periodno, account, narrative, amount, posted, jobref,tag,lasttrandate, dateadded, userid)
                    select null, type, typeno, 0, trandate, periodno ,account, CONCAT(narrative,' @ Movimiento Cancelado'),(amount*-1),0,0, tag,now(), NOW(), userid
                    from gltrans
                    where type=32 and typeno in (".implode(',',$docs).")";
            $TransResult = DB_query($SQL,$db,$ErrMsg);
//            echo $SQL . "<br>";
//            
            //Poner en cero ovamount y ovgst
            $SQL="UPDATE supptrans set ovamount=0, ovgst=0 ,suppreference='CANCELADO' WHERE type=32 and transno in (".implode(',',$docs).")";
            $TransResult = DB_query($SQL,$db,$ErrMsg);
//            echo $SQL . "<br>";
//            
//            //Eliminar registros de suppalocs
//            // -- Buscar las notas a eliminar
            $SQL_sea_notas="SELECT notaid FROM suppdocpaydocs WHERE chequetransno='".$typeno."';";
//            echo $SQL_sea_notas . "<br>";
//            
            $result_sea=  DB_query($SQL_sea_notas, $db);
            $docs=array();
//            //En doc se obtinen los transno de las notas de credito
            while($myrowssear=DB_fetch_array($result_sea)){
                $docs[]=$myrowssear['notaid'];
            }
            $SQL="DELETE FROM suppallocs WHERE transid_allocfrom IN (".implode(',',$docs).")";
            $TransResult = DB_query($SQL,$db,$ErrMsg);
//            echo $SQL . "<br>";
//          
            
            //CAMBIAR alloc A CERO DE FACTURAS INICIALES
            $SQL_sea_notas="SELECT faciniid FROM suppdocpaydocs WHERE chequetransno='".$typeno."';";
//            echo $SQL_sea_notas . "<br>";
            $result_sea=  DB_query($SQL_sea_notas, $db);
            $docs=array();
//            //En doc se obtinen los transno de las factutras iniciales a modificar
            while($myrowssear=DB_fetch_array($result_sea)){
                $docs[]=$myrowssear['faciniid'];
            }
            $docs=array_unique($docs);
            $SQL="UPDATE supptrans set alloc=0 WHERE type=34 and transno in (".implode(',',$docs).")";
            $TransResult = DB_query($SQL,$db,$ErrMsg);
//            echo $SQL . "<br>";
            
            
            //CANCELAR FACTURA DE PROVEEDOR PUENTE
            $SQL_sea_notas="SELECT facpuentetransno FROM suppdocpaydocs WHERE chequetransno='".$typeno."';";
//            echo $SQL_sea_notas . "<br>";
            $result_sea=  DB_query($SQL_sea_notas, $db);
            $docs=array();
//            //En doc se obtinen los transno de la factura puente
            while($myrowssear=DB_fetch_array($result_sea)){
                $docs[]=$myrowssear['facpuentetransno'];
            }
            $docs=array_unique($docs);
            $SQL = "insert  gltrans(counterindex, type, typeno, chequeno, trandate, periodno, account, narrative, amount, posted, jobref,tag,lasttrandate, dateadded, userid)
                    select null, type, typeno, 0, trandate, periodno ,account, CONCAT(narrative,' @ Movimiento Cancelado'),(amount*-1),0,0, tag,now(), NOW(), userid
                    from gltrans
                    where type=34 and typeno in (".implode(',',$docs).")";
            $TransResult = DB_query($SQL,$db,$ErrMsg);
//            echo $SQL . "<br>";
//            
            //Poner en cero ovamount y ovgst
            $SQL="UPDATE supptrans set ovamount=0, ovgst=0 ,suppreference='CANCELADO' WHERE type=34 and transno in (".implode(',',$docs).")";
            $TransResult = DB_query($SQL,$db,$ErrMsg);
//            echo $SQL . "<br>";
//            
//            //Eliminar registros de suppalocs
//            // -- Buscar la factura a eliminar
            $SQL_sea_notas="SELECT facpuenteid FROM suppdocpaydocs WHERE chequetransno='".$typeno."';";
//            echo $SQL_sea_notas . "<br>";
//            
            $result_sea=  DB_query($SQL_sea_notas, $db);
            $docs=array();
//            //En doc se obtinen los transno de las facturas
            while($myrowssear=DB_fetch_array($result_sea)){
                $docs[]=$myrowssear['facpuenteid'];
            }
            $docs=array_unique($docs);
            $SQL="DELETE FROM suppallocs WHERE transid_allocfrom IN (".implode(',',$docs).")";
            $TransResult = DB_query($SQL,$db,$ErrMsg);
//            echo $SQL . "<br>";
//              sleep(30);
//            die('END');
    }
    else{

            /*$SQL = "delete from gltrans
            where type = 1 and typeno = " . $_POST['ChequeNum'];
    */
            $typeno = $_POST['ChequeNum'];
            $qry = "Select tag,trandate FROM gltrans WHERE type = '$type' and typeno = '$typeno'";
            $rsid = DB_query($qry,$db);
            $row = DB_fetch_array($rsid);
            if (strpos($_SESSION['DefaultDateFormat'],'/')) {
        $flag = "/";
    } elseif (strpos ($_SESSION['DefaultDateFormat'],'-')) {
        $flag = "-";
    } elseif (strpos ($_SESSION['DefaultDateFormat'],'.')) {
        $flag = ".";
    }
    $diafecha = substr($row['trandate'], 8, 2);
    $mesfecha = substr($row['trandate'], 5, 2);
    $aniofecha = substr($row['trandate'], 0, 4);
    $trandatef = $diafecha.$flag.$aniofecha.$flag.$aniofecha;

            $PeriodNo = GetPeriod($trandatef, $db, $row['tag']);

            $SQL = "insert  gltrans(counterindex, type, typeno, chequeno, trandate, periodno, account, narrative, amount, posted, jobref,tag,lasttrandate, dateadded, userid)
                    select null, 1, ".$_POST['ChequeNum'].", 0,trandate,periodno ,account, CONCAT(narrative,' @ Movimiento Cancelado'),(amount*-1),0,0, tag,now(), NOW(), '".$_SESSION['UserID']."'
                    from gltrans
                    where type=1 and typeno=".$_POST['ChequeNum'];
            //echo $SQL . "<br>";
            $TransResult = DB_query($SQL,$db,$ErrMsg);

            $SQL = "delete
            from banktrans
            where transno = " . $_POST['ChequeNum'] . " and type = 1";
            //echo $SQL . "<br>";
            $TransResult = DB_query($SQL,$db,$ErrMsg);
    }

    $SQL = "COMMIT";
    $ErrMsg = _('No pude hacer COMMIT de los cambios porque');
    $DbgMsg = _('El COMMIT a la base de datos fallo');
    $result= DB_query($SQL,$db,$ErrMsg,$DbgMsg);

    if ($result == 1)
        echo "<meta http-equiv='Refresh' content='1; url=" . $rootpath . "/ReportePayments.php'>";
    else
        echo $ErrMsg . '<br>' . $DbgMsg; 
    exit;        
}


if (isset($_POST['ChequeReturn'])){
    echo "<meta http-equiv='Refresh' content='1; url=" . $rootpath . "/ReportePayments.php'>";
    exit;
}


echo '<form action=' . $_SERVER['PHP_SELF'] . ' method=post name=form1>';
echo '<p class="page_title_text">' . ' ' . _('BUSQUEDA DE CHEQUES') . '</p>';


echo "<table width='80%' cellpadding='0' cellspacing='0' border='0'>";
echo "<tr>";
echo "<td></td>";
echo "<td style='text-align:right;'><b>" . _('Desde') . ": &nbsp;</b></td>";
echo "<td><input type='text' name='txtfechadesde' value='" . $fechadesde . "' readonly size='10' class='date' alt='Y/m/d'></td>";
echo "<td></td>";
echo "<td style='text-align:right;'><b>" . _('Hasta') . ": &nbsp;</b></td>";
echo "<td><input type='text' name='txtfechahasta' value='" . $fechahasta . "' readonly size='10' class='date' alt='Y/m/d'></td>";
echo "<td></td>";
echo "</tr><tr height=10><td></td></tr><tr>";
echo "<td style='text-align:right;'><b>" . _('Cheque No.') . ": &nbsp;</b></td>";
echo "<td><input type='text' name='txtchequeno' value='" . $chequeno . "' size='10' maxlength='10' class='number'></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td style='text-align:right;'><b>" . _('Unidad Negocio') . ": &nbsp;</b></td>";
echo "<td>";
echo "<select name='cbounidadnegocio'>";
echo "<option value='0'>TODOS</option>";
//$SQL = "SELECT * FROM tags WHERE areacode = '" . $_SESSION['DefaultArea'] . "'";
$SQL = "SELECT  t.tagref, t.tagdescription";//areas.areacode, areas.areadescription";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t join areas ON t.areacode = areas.areacode ";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' 
			ORDER BY areas.areacode";

$ErrMsg = _('No transactions were returned by the SQL because');
$TransResult = DB_query($SQL,$db,$ErrMsg);
while ($myrow=DB_fetch_array($TransResult)) {
	if ($myrow['tagref'] == $unidadnegocio){
		echo "<option selected value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'] . "</option>";	
	}else{
		echo "<option value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'] . "</option>";
	}
}

echo "</select>";
echo "</td>";
echo "<td><input type='submit' name='buscar' value='BUSCAR'></td>";
echo "</tr>";
echo "</tr><tr height=10><td></td></tr><tr>";
echo "</table>";

if (isset($_GET['delete'])){
	echo "OPCION DELETE";
}else{
	echo "<table width='100%' cellpadding='3' cellspacing='3' border='1'>";
	
	
	echo "<tr>";
	echo "<td colspan='10' align='left'><b>" . _('CHEQUES A PROVEEDORES'). "</b></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<th><b>" . _('Unidad Negocio'). "</b></th>";
	echo "<th><b>" . _('Fecha'). "</b></th>";
	echo "<th><b>" . _('Folio ERP'). "</b></th>";
	echo "<th><b>" . _('Cod Cliente'). "</b></th>";
	echo "<th><b>" . _('Cliente'). "</b></th>";
	//echo "<th><b>" . _('Sucursal'). "</b></th>";
	echo "<th><b>" . _('Referencia'). "</b></th>";
	echo "<th><b>" . _('CHEQUE'). "</b></th>";
	echo "<th><b>" . _('Monto'). "</b></th>";
	//echo "<th><b>" . _('IVA'). "</b></th>";
	echo "<th><b>" . _('Total'). "</b></th>";
	echo "<th><b><img src='".$rootpath."/css/".$theme."/images/printer.png' title='" . _('Imprimir ') . "' alt=''></b></th>";
	echo "<th><b><img src='part_pics/Delete.png' border=0 title='" . _('Cancelar Cheque') . "' alt=''></b></th>";
	echo "</tr>";
	
	$SQL = "SELECT supptrans.tagref,
			supptrans.transno,
			supptrans.suppreference,
			supptrans.supplierno,
			supptrans.transtext,
			supptrans.ovamount,
			banktrans.amount,
			supptrans.ovgst,
			supptrans.alloc,
			tags.tagdescription,
			suppliers.supplierid,
			suppliers.suppname,
			supptrans.trandate,
			day(supptrans.trandate) as daytrandate,
			month(supptrans.trandate) as monthtrandate,
			year(supptrans.trandate) as yeartrandate,
			supptrans.folio,
			supptrans.type,
			legalbusinessunit.taxid,
			legalbusinessunit.address5,
			suppliers.currcode,
			banktrans.chequeno,
			case when supptrans.alloc <> 0 then 0 else 1 end as cancelar,
			tags.typeinvoice as tipofacturacion
	FROM  tags, sec_unegsxuser, suppliers, legalbusinessunit, supptrans,banktrans
	WHERE supptrans.type in(22,32,121,501)
	and supptrans.transno=banktrans.transno
and supptrans.type=banktrans.type
	and supptrans.tagref = tags.tagref
	and supptrans.supplierno = suppliers.supplierid
	and supptrans.trandate between  STR_TO_DATE('" . $fechadesde . "', '%Y/%m/%d')
	and STR_TO_DATE('" . $fechahasta . "', '%Y/%m/%d')
	and sec_unegsxuser.tagref = tags.tagref
	and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "' 
	and legalbusinessunit.legalid = tags.legalid";
	
	if ($chequeno != ''){
		$SQL = $SQL . " and supptrans.suppreference = " . $recibono;
	}
	if ($unidadnegocio != 0){
		$SQL = $SQL . " and supptrans.tagref = " . $unidadnegocio;
	}
	
	$SQL = $SQL . " order by supptrans.tagref, supptrans.trandate";
	//echo "<pre>$SQL";
	$ErrMsg = _('No transactions were returned by the SQL because');
	$TransResult = DB_query($SQL,$db,$ErrMsg);
	$k = 1;
	while ($myrow=DB_fetch_array($TransResult)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		$type=$myrow['type'];
		$tipofacturacion=$myrow['tipofacturacion'];
		 
		
		if (($tipofacturacion == 1)){
			$liga = $rootpath . "/PrintCheque.php?ChequeNum=&TransNo=" . $myrow['transno'] . "&Currency=" . $myrow['currcode'] . "&SuppName=" . $myrow['suppname'];
		}else{
			$liga = $rootpath . "/PrintJournal.php?PrintPDF=yes&FromCust=1&ToCust=1&type=".$myrow['type']."&TransNo=" . $myrow['transno'] . "&Tagref=" . $myrow['tagref'] . "&SuppName=" . $myrow['suppname'] ."&trandate=".$myrow['trandate'];//GetUrlToPrint($myrow['tagref'],$type,$db);
			//$liga = $rootpath . "/" . $liga . "&TransNo=" . $myrow['transno'] . "&Tagref=" . $myrow['tagref'] . "&SuppName=" . $myrow['suppname'];
		}
		$liga = $rootpath . "/PrintJournal.php?PrintPDF=yes&FromCust=1&ToCust=1&type=".$myrow['type']."&TransNo=" . $myrow['transno'] . "&Tagref=" . $myrow['tagref'] . "&SuppName=" . $myrow['suppname'] ."&trandate=".$myrow['trandate'];//GetUrlToPrint($myrow['tagref'],$type,$db);
		$reimprimir = "<a TARGET='_blank' href='" . $liga . "'><img src='".$rootpath."/css/".$theme."/images/printer.png' title='" . _('Imprimir Poliza') . "' alt=''></a>";
		
		
		echo "<td style='font-size:7pt;'>" . $myrow['tagdescription'] . "</td>";
		echo "<td style='font-size:7pt;' nowrap>" . $myrow['daytrandate'] . " - " . glsnombremescorto($myrow['monthtrandate']) . " - " . $myrow['yeartrandate'] . "</td>";
		echo "<td style='font-size:7pt; text-align:center;'>" . $myrow['transno'] . "</td>";
		echo "<td style='font-size:7pt; text-align:center;'>" . $myrow['supplierno'] . "</td>";
		echo "<td style='font-size:7pt;'>" . $myrow['suppname'] . "</td>";
		//echo "<td style='font-size:7pt; text-align:left;'>" . $myrow['branchcode'] . "</td>";
		echo "<td style='font-size:7pt;'>" . $myrow['transtext'] . "</td>";
		echo "<td style='font-size:7pt;'>" . $myrow['chequeno'] . "</td>";
		echo "<td style='font-size:7pt; text-align:right;'>" . number_format($myrow['amount'],2) . "</td>";
		//echo "<td style='text-align:right;'>" . number_format($myrow['ovgst'],2) . "</td>";
		echo "<td style='font-size:7pt; text-align:right;'>" . number_format(($myrow['ovamount']  + $myrow['ovgst']),2) . "</td>";
		echo "<td style='font-size:7pt; text-align:center;'>" .  $reimprimir ."</td>";
		echo "<td><b>";
		
                $permiso = Havepermission($_SESSION['UserID'],561, $db);
                if (($myrow['cancelar'] == 1) and ($permiso == 1) and (($myrow['ovamount']  + $myrow['ovgst'])!=0)) {
                        echo "<a href='#'>";
                        echo "<img src='part_pics/Delete.png' onclick='cancela(" .  $myrow['transno'] .",".$type. ",1);' border=0 title='" . _('Cancelar Cheque') . "' alt=''>";
                        echo "</a>";
                }
                
		echo "</b></td>";
	
		echo "</tr>";
	}


	/*Consulta obtengo polizas de cheques*/
	$SQL = "select  gltrans.type
		,gltrans.tag as tagref
	       ,gltrans.trandate

	       ,day(gltrans.trandate) as daytrandate
	       ,month(gltrans.trandate) as monthtrandate
	       ,year(gltrans.trandate) as yeartrandate
	       
	       ,gltrans.amount
	       ,tags.tagdescription
	       ,banktrans.ref
	       ,banktrans.transno
	       ,banktrans.chequeno
	       ,gltrans.account
	       ,'' as codcliente
	       ,'' as cliente
	       ,1 as cancelar
	
	from    gltrans join tags on gltrans.tag = tags.tagref
	        join banktrans on banktrans.transno = gltrans.typeno
	        join sec_unegsxuser on sec_unegsxuser.tagref = tags.tagref
	        and sec_unegsxuser.userid   = '" . $_SESSION['UserID'] . "'
	where   gltrans.type = 1 and banktrans.type = 1
		and gltrans.trandate between  STR_TO_DATE('" . $fechadesde . "', '%Y/%m/%d')
		and STR_TO_DATE('" . $fechahasta . "', '%Y/%m/%d')
		and gltrans.amount < 0
	group by gltrans.type,gltrans.tag,gltrans.trandate,gltrans.amount,tags.tagdescription,gltrans.account
		";
	
	if ($unidadnegocio != 0){
		$SQL = $SQL . " and gltrans.tag = " . $unidadnegocio;
	}

	$SQL = $SQL . " order by  banktrans.transno";
	
	$ErrMsg = _('No transactions were returned by the SQL because');
	$TransResult = DB_query($SQL,$db,$ErrMsg);
	$k = 1;

	echo "<tr>";
	echo "<td colspan='10' align='left'><b>" . _('CHEQUES POLIZA'). "</b></td>";
	echo "</tr>";


	while ($myrow = DB_fetch_array($TransResult)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		$type=$myrow['type'];
		//if (($_SESSION['EnvioXSA'] != 0)){
			//$liga = $rootpath . "/PrintCheque.php?ChequeNum=&TransNo=" . $myrow['transno'] . "&Currency=" . $myrow['currcode'] . "&SuppName=" . $myrow['suppname'];
		//}else{
			$liga = GetUrlToPrint($myrow['tagref'],1,$db);
			$liga = $rootpath . "/" . $liga . "&TransNo=" . $myrow['transno'] . "&Tagref=" . $myrow['tagref'] ;
		//}
		//$liga = $rootpath . "/PrintCheque.php?ChequeNum=&TransNo=" . $myrow['transno'] . "&Currency=" . $myrow['currcode'] . "&SuppName=" . $myrow['suppname'];
                $liga = $rootpath . "/PrintJournal.php?PrintPDF=yes&FromCust=1&ToCust=1&type=".$myrow['type']."&TransNo=" . $myrow['transno'] . "&Tagref=" . $myrow['tagref'] . "&SuppName=" . $myrow['suppname'] ."&trandate=".$myrow['trandate'];//GetUrlToPrint($myrow['tagref'],$type,$db);        
		$reimprimir = "<a TARGET='_blank' href='" . $liga . "'><img src='".$rootpath."/css/".$theme."/images/printer.png' title='" . _('Imprimir Poliza') . "' alt=''></a>";
		echo "<td style='font-size:7pt;'>" . $myrow['tagdescription'] . "</td>";
		echo "<td style='font-size:7pt;' nowrap>" . $myrow['daytrandate'] . " - " . glsnombremescorto($myrow['monthtrandate']) . " - " . $myrow['yeartrandate'] . "</td>";
		echo "<td style='font-size:7pt; text-align:center;'>" . $myrow['transno'] . "</td>";
		echo "<td style='font-size:7pt; text-align:center;'>" . $myrow['codcliente'] . "&nbsp;</td>";
		echo "<td style='font-size:7pt;'>" . $myrow['cliente'] . "&nbsp;</td>";
		//echo "<td style='font-size:7pt; text-align:left;'>" . $myrow['branchcode'] . "</td>";
		echo "<td style='font-size:7pt;'>" . $myrow['ref'] . "</td>";
		echo "<td style='font-size:7pt;'>" . $myrow['chequeno'] . "</td>";
		echo "<td style='font-size:7pt; text-align:right;'>" . number_format($myrow['amount'],2) . "</td>";
		//echo "<td style='text-align:right;'>" . number_format($myrow['ovgst'],2) . "</td>";
		echo "<td style='font-size:7pt; text-align:right;'>" . number_format($myrow['amount'],2) . "</td>";
		echo "<td style='font-size:7pt; text-align:center;'>" .  $reimprimir ."</td>";
		
		echo "<td><b>";
			$permiso = Havepermission($_SESSION['UserID'],561, $db);
			if (($myrow['cancelar'] == 1) and ($permiso == 1)) {
				echo "<a href='#'>";
				echo "<img src='part_pics/Delete.png' onclick='cancela(" .  $myrow['transno'] .",".$type . ",2);' border=0 title='" . _('Cancelar Cheque') . "' alt=''>";
				echo "</a>";
			}
		echo "</b></td>";
		echo "</tr>";
		//$_POST['ovamount']=$myrow['ovamount'];
		///$_POST['ovgst']=$myrow['ovgst'];
		
	}	
	/*************************************/
	echo "</table>";
}
echo "<br><br>";
echo "<input type='hidden' name='btncancela' >";
echo "<input type='hidden' name='numerocheque' value=" . $numerocheque . ">";
echo "<input type='hidden' name='movimiento'>";
echo "<input type='hidden' name='tipom'>";
echo '</form>';

?>


<script>

	function cancela(transno,type,mov){
		document.form1.btncancela.value="1";
		document.form1.numerocheque.value=transno;
		document.form1.movimiento.value=mov;
		document.form1.tipom.value=type;
		document.form1.submit();		
				
	}

</script>


	