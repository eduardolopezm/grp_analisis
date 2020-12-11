<?php
    $textomail='';
    $mensaje='';
    $cabeceras='';
    $PaperSize = 'A4_Landscape';
    //consulta datos de la orden de compra y los productos que se han de enviar a la orden de compra
    $SQL="Select supplierno,
                deladd1,
	        deladd2,
		deladd3,
		deladd4,
		deladd5,
		deladd6,
                tags.tagref,
                tags.tagname,
                legalbusinessunit.legalname
        FROM  purchorders INNER JOIN tags ON tags.tagref=purchorders.tagref
            INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
        where orderno=".$OrderNoCompra;
    $Resultalmacen = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
    $CompraAlmacenRow = DB_fetch_row($Resultalmacen);
    $Proveedor = $CompraAlmacenRow[0];
    $calle=$CompraAlmacenRow[1];
    $colonia=$CompraAlmacenRow[2];
    $ciudad=$CompraAlmacenRow[3];
    $estado=$CompraAlmacenRow[4];
    $cpcompra=$CompraAlmacenRow[5];
    $nombreunidad=$CompraAlmacenRow[8];
    $legalname=$CompraAlmacenRow[9];
    //$mmail="compras@coinver.com.mx";
    $mmail=$_SESSION['FactoryManagerEmail'];
    $asunto="Orden de Compra para ".$legalname.' en '.$nombreunidad;
    //extrae email de proveedor por categoria de inventario
    $cat='';
    $sql="select distinct categoryid
	from purchorderdetails inner join purchorders on purchorders.orderno=purchorderdetails.orderno
	join stockmaster on purchorderdetails.itemcode=stockmaster.stockid
       where purchorders.supplierorderno =".$_GET['supplierorderno'];
    $Resultalcat = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
    if (DB_num_rows($Resultalcat)>0){
		while ($myrowcat=DB_fetch_array($Resultalcat)) {
		    if($cat!=''){
				$cat=$cat.", '" .$myrowcat['categoryid']."'";
		    }else{
				$cat="'".$myrowcat['categoryid']."'";
		    }
		}
    }	
    if(empty($cat)) {
    	$cat = "''";
    }
    $email='';
    $SQL="select distinct email from suppliercontacts where suppcategoryid in('All',".$cat.") and supplierid='".$Proveedor."'";
    if($_SESSION['UserID'] == "admin"){
    	echo '<pre>'.$SQL;
    }
    $Resultalcat = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
    if (DB_num_rows($Resultalcat)>0){
	while ($myrowcat=DB_fetch_array($Resultalcat)) {
	    if($email!=''){
		$email=$email.", " .$myrowcat['email'];
	    }else{
		$email=$myrowcat['email'];
	    }
	}
    }	
    
    $CompraAlmacenRow = DB_fetch_row($Resultalmacen);
    $email = $CompraAlmacenRow[0];
    
    $emailsql='SELECT realname,email FROM www_users WHERE userid="'.$_SESSION['UserID'].'"';
    $emailresult=DB_query($emailsql, $db);
    $emailrow=DB_fetch_array($emailresult);
    $emailcopia = $emailrow[1];
    $nombre = $emailrow[0];
    $mmail = $emailcopia;
    
    //echo $emailcopia;
    $textomail=$textomail."\n Buen dia \n A traves del presente le notificamos que se ha generado la orden de compra No. ".$OrderNoCompra;
    $textomail=$textomail." en nuestro sistema y le solicitamos de la manera mas atenta nos lo envie a la siguiente direccion";
    $textomail=$textomail."\nCalle: ".$calle.', Col. '.$colonia.' en la ciudad ' .$ciudad.' ,'.$estado.' Cp.'.$cpcompra;
  //  $textomail=$textomail."\n\n La lista de articulos es la siguiente : \n\n ";
    $productos='';
    //consulta los productos que se solicitaran en este pedido de compra
   /* $sql=" Select * from purchorderdetails where orderno =".$OrderNoCompra;
    $Resultprds= DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
    while ($myrowprd = DB_fetch_array($Resultprds)){
        $productos=$productos.'\n'.' Codigo Interno:'.$myrowprd['itemcode'].'-Proveedor:'.$myrowprd['suppliers_partno'].' '.$myrowprd['itemdescription'].' Cant.'.$myrowprd['quantityord'];
    }*/
    $direcciondos="./archivos";//.$_SESSION['DatabaseName'];
    $direcciondos=$direcciondos.'/'.$OrderNoCompra.'.pdf';

   //include('PDFPurchByMail.inc');
   //$len = strlen($pdfcode);
	//$pdf->Output($direcciondos, 'F');
	
	include('PDFPurchByMailNewXSupplier.inc');
	$archivoPDF=$direcciondos;
	

   $nombre_archivo = $archivoPDF;
//Leemos el archivo xml para incluirlo en email como adjunto 
$archivo= fopen("$nombre_archivo", "r");
$contenido = fread($archivo, filesize("$nombre_archivo"));
$encoded_attach = chunk_split(base64_encode($contenido));
fclose($archivo);
    
    $cuerpo = $body_top.$mensaje;
$nombref= $nombre_archivo;
$direcciondosPFD=$archivodos;
set_time_limit(600);
$archivo= $archivoPDF;
$buf_type= 'application/pdf';//obtener_extencion_stream_archivo($archivoPDF); //obtenemos tipo archivo
$fp= fopen( $archivoPDF, "r" ); //abrimos archivo
$buf= fread( $fp, filesize($archivo) ); //leemos archivo completamente
fclose($fp); //cerramos apuntador;

if ($emailProveedor == 1){
	
	$qry = "select email from suppliers where supplierid = '$Proveedor'";

	$rsmail = DB_query($qry,$db);
	$rowmail = DB_fetch_array($rsmail);
	
	if (IsEmailAddress($rowmail['email']))
		$email = $rowmail['email'];//
	else 
		prnMsg('El proveedor '.$Proveedor.' no tiene mail valido configurado');
	//
}
else{
	$email=$mmail;
}

    $mensaje .= $textomail.' '.$productos;
    $cabeceras = "From: $nombre <$mmail>\n";
    $cabeceras .= "Bcc:" .$emailcopia; 
    $cabeceras .= "Reply-To: $mmail\n";
    $cabeceras .= "MIME-version: 1.0\n";
    $cabeceras .= "Content-type: multipart/mixed; ";
    $cabeceras .= "boundary=\"Message-Boundary\"\n";
    $cabeceras .= "Content-transfer-encoding: 7BIT\n";
    
    $body_top = "--Message-Boundary\n";
    $body_top .= "Content-type: text/plain; charset=US-ASCII\n";
    $body_top .= "Content-transfer-encoding: 7BIT\n";
    $body_top .= "Content-description: Mail message body\n\n";
     // aqui unimos las varibles $mensaje y $body_top en una sola 
    $cuerpo = $body_top.$mensaje;
//Archivo PDF
$archivoPDFEnvio=$OrderNoCompra.".pdf";
$cuerpo .= "\n\n--Message-Boundary\n";
$cuerpo .= "Content-Type: ". $buf_type. "; name=\"". $archivoPDFEnvio. "\"\r\n"; //envio directo de datos
$cuerpo .= "Content-Transfer-Encoding: base64\r\n";
$cuerpo .= "Content-Disposition: attachment; filename=\"". $archivoPDFEnvio. "\"\r\n\r\n";
$cuerpo .= base64_encode($buf). "\r\n\r\n";
$cuerpo .= "--Message-Boundary--\n";
    unlink($archivoPDF);
  	
    
    if ($email != ""){
    
	    if(mail($email,$asunto,$cuerpo,$cabeceras)){
	        //Se envio el mail con exito
	    	if($emailProveedor == 1)
	       		 echo _('<br><b>Se envio el mail de manera exitosa a '.$email);
	    }
    }
    
//$textomail=$textomail."\n\n Este es un mensaje automatico.Las respuestas a este mensaje no estan monitorizadas ni seran respondidas. \n\n ";

    
    
?>