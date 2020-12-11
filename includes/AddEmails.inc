<?php

$emails = $_POST['emailscliente'];

if (strpos($emails, ';') == true) {

    $separamail = explode(';', $emails);
    $cuenta     = count($separamail);

    for ($xmail = 0; $xmail < $cuenta; $xmail++) {

        $email = $separamail[$xmail];

        if (IsEmailAddress($email)) {

            $SQL = "SELECT * FROM custmails
        WHERE debtorno='" . $_SESSION['Items' . $identifier]->DebtorNo . "'
        AND branchcode='" . $_SESSION['Items' . $identifier]->Branch . "'
                AND email='" . $email . "'";
            $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('No se obtuvieron los datos del email');
            $DbgMsg = _('El siguiente SQL se utilizo');
            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

            if (DB_num_rows($Result) == 0) {
                $SQL = "INSERT INTO custmails(debtorno,branchcode,email,trandate,active)
                      VALUES('" . $_SESSION['Items' . $identifier]->DebtorNo . "','" . $_SESSION['Items' . $identifier]->Branch . "','" . $email . "',now(),1)";
                $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la insercion del email');
                $DbgMsg = _('El siguiente SQL se utilizo');
                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
            }

            if ($xmail == 0) {
                $emails = $email . ';';
            } else {
                $emails = $emails . ';' . $email;
            }
        }
    }
} elseif (strlen(trim($emails)) > 0) {

    if (!IsEmailAddress($emails)) {
        $emails = "";
    } else {

        $SQL = "SELECT * FROM custmails
        WHERE debtorno='" . $_SESSION['Items' . $identifier]->DebtorNo . "'
        AND branchcode='" . $_SESSION['Items' . $identifier]->Branch . "'
                AND email='" . $emails . "'";
        $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('No se obtuvieron los datos del email');
        $DbgMsg = _('El siguiente SQL se utilizo');
        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

        if (DB_num_rows($Result) == 0) {
            $SQL = "INSERT INTO custmails(debtorno,branchcode,email,trandate,active)
                  VALUES('" . $_SESSION['Items' . $identifier]->DebtorNo . "','" . $_SESSION['Items' . $identifier]->Branch . "','" . $emails . "',now(),1)";
            $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la insercion del email');
            $DbgMsg = _('El siguiente SQL se utilizo');
            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
        }
    }
}

$SQL = "SELECT *
        FROM custmails
    WHERE debtorno='" . $_SESSION['Items' . $identifier]->DebtorNo . "'
        AND branchcode='" . $_SESSION['Items' . $identifier]->Branch . "'
        ORDER BY idemail
        LIMIT 8";
$ErrMsg   = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('No se obtuvieron los datos del email');
$DbgMsg   = _('El siguiente SQL se utilizo');
$Result   = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
$pasamail = 0;
while ($myrowmail = DB_fetch_array($Result)) {
    $email = 'email' . $myrowmail['idemail'];
    if ($_POST[$email] == true) {
        if (strlen($emails) == 0 and $pasamail == 0) {
            $emails = $myrowmail['email'] . ';';
        } else {
            $emails = $emails . ';' . $myrowmail['email'];
        }
        $pasamail = $pasamail + 1;
    }

}

if (strlen(trim($emails)) > 0 and strlen(trim($InvoiceNoTAG)) > 0) {

    $SQL = "UPDATE debtortrans
          SET emails='" . $emails . "'
          where id=" . $DebtorTransID;
    $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion del email para la factura');
    $DbgMsg = _('El siguiente SQL se utilizo');
    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
} elseif (strlen(trim($InvoiceNoTAG)) > 0) {

    prnMsg(_('El email proporcionado no es valido el envio de la factura no sera posible'), 'warn');
}
