<?php
    $textomail='';
    $mensaje='';
    $cabeceras='';
    //consulta datos de la orden de compra y los productos que se han de enviar a la orden de compra
    $SQL="Select initiator,requisitionno
        FROM  requisitionorderdetails
        where podetailitem=".$NoRequisition;
    //echo $SQL;
    $Resultalmacen = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
    $CompraAlmacenRow = DB_fetch_row($Resultalmacen);
    $Destinatario = $CompraAlmacenRow[0];
    $PedidoVenta = $CompraAlmacenRow[1];
    $mmail="juan.mendoza@tecnoaplicada.com";
    $asunto="Orden de Venta para ".$PedidoVenta.' con numero de Requisicion '.$NoRequisition;
    //extrae email de destinatario
    $SQL="select email from www_users where userid='".$Destinatario."'";
    $Resultalmacen = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
    $CompraAlmacenRow = DB_fetch_row($Resultalmacen);
    $email = $CompraAlmacenRow[0];
    $textomail=$textomail."\n Buen dia \n A traves del presente le notificamos se ha registrado cambios en la requisicion de compra";
    $textomail=$textomail." para el pedido de venta. ".$PedidoVenta." el usuario ".$UsuarioReq." cambio de status a " .$statusReq;
    $textomail=$textomail." con el producto ".$productoReq." con los siguientes comentarios " .$comentariosReq;
    
    $mensaje .= $textomail;
    $cabeceras = "From: $nombre <$mmail>\n";
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
    if(mail($email,$asunto,$cuerpo,$cabeceras)){
        //Se envio el mail con exito
        echo _('Se envio el mail de manera exitosa');
    }else{
        echo $mensaje;
    }
    
//$textomail=$textomail."\n\n Este es un mensaje automatico.Las respuestas a este mensaje no estan monitorizadas ni seran respondidas. \n\n ";

    
    
?>