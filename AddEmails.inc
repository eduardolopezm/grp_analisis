<?php
//recupero valor de la caja de texto
$emails=$_POST['emailscliente'];
//valido si caja de texto tiene email
if (strpos($emails,';')==true){
    //separo en array los emails
    $separamail = explode(';',$emails);
    $cuenta=count($separamail);
   // echo "cuenta reg:".$cuenta."|";
    for($xmail=0;$xmail<$cuenta;$xmail++){
        //extraigo el email
        $email=$separamail[$xmail];
        //valido que sea un email valido
        if (IsEmailAddress($email)){
            //consulto si existe el email
            $SQL = "SELECT * FROM custmails
		WHERE debtorno='".$_SESSION['Items'.$identifier]->DebtorNo ."'
		AND branchcode='".$_SESSION['Items'.$identifier]->Branch."'
                AND email='".$email."'";
            $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('No se obtuvieron los datos del email');
            $DbgMsg = _('El siguiente SQL se utilizo');
            $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
            //en caso de que no exista se inserta
            if(DB_num_rows($Result)==0){
                $SQL="INSERT INTO custmails(debtorno,branchcode,email,trandate,active)
                      VALUES('".$_SESSION['Items'.$identifier]->DebtorNo."','".$_SESSION['Items'.$identifier]->Branch."','".$email."',now(),1)";
                $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la insercion del email');
                $DbgMsg = _('El siguiente SQL se utilizo');
                $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
            }
            //concateno para actualizar datos de debtortrans
            if ($xmail==0){
                $emails=$email.';';
            }else{
                $emails=$emails.';'.$email;
            }
        }
    }
}elseif(strlen(trim($emails))>0){// si la longitud es mayor a cero
    //valido que el email proporcionado sea valido
    if (!IsEmailAddress($emails)){
        $emails="";
    }else{
        //consulto si el email existe en la base de datos
        $SQL = "SELECT * FROM custmails
		WHERE debtorno='".$_SESSION['Items'.$identifier]->DebtorNo ."'
		AND branchcode='".$_SESSION['Items'.$identifier]->Branch."'
                AND email='".$emails."'";
        $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('No se obtuvieron los datos del email');
        $DbgMsg = _('El siguiente SQL se utilizo');
        $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
        //en caso de que no exista se inserta
        if(DB_num_rows($Result)==0){
            $SQL="INSERT INTO custmails(debtorno,branchcode,email,trandate,active)
                  VALUES('".$_SESSION['Items'.$identifier]->DebtorNo."','".$_SESSION['Items'.$identifier]->Branch."','".$emails."',now(),1)";
            $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la insercion del email');
            $DbgMsg = _('El siguiente SQL se utilizo');
            $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
        }
    }
}
// concateno los emails de los checkbox
$SQL = "SELECT *
        FROM custmails
	WHERE debtorno='".$_SESSION['Items'.$identifier]->DebtorNo ."'
	    AND branchcode='".$_SESSION['Items'.$identifier]->Branch."'
        ORDER BY idemail
        LIMIT 8";
        $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('No se obtuvieron los datos del email');
        $DbgMsg = _('El siguiente SQL se utilizo');
        $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
$pasamail=0;
while ($myrowmail=DB_fetch_array($Result)){
    $email='email'.$myrowmail['idemail'];
    if($_POST[$email]==true){
        if (strlen($emails)==0 and $pasamail==0){
            $emails=$myrowmail['email'].';';
        }else{
            $emails=$emails.';'.$myrowmail['email'];
        }
        $pasamail=$pasamail+1;
    }
    
}
// si la longitud es mayor a cero        
if(strlen(trim($emails))>0 and strlen(trim($InvoiceNoTAG))>0){
    // se actualiza el campo de emails a enviar en debtortrans
    $SQL="UPDATE debtortrans
          SET emails='".$emails."'
          where id=".$DebtorTransID;
    $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion del email para la factura');
    $DbgMsg = _('El siguiente SQL se utilizo');
    $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
}elseif(strlen(trim($InvoiceNoTAG))>0){
    // si no es valido se envia mensaje de que no se realizara el envio del email
    prnMsg(_('El email proporcionado no es valido el envio de la factura no sera posible'),'warn');
}
?>