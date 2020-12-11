<?php

if (isset($_POST['AddBatches'])) {
    for ($i=0; $i < 10; $i++) {
        if (strlen($_POST['SerialNo' . $i])>0) {
            $ExistingBundleQty = ValidBundleRef($StockID, $LocationOut, $_POST['SerialNo' . $i]);
            if ($ExistingBundleQty >0) {
                $AddThisBundle = true;
                if ($_POST['Qty' . $i] > $ExistingBundleQty) {
                    if ($LineItem->Serialised ==1) {
                        echo "<BR>" . $_POST['SerialNo' . $i] . " " . _('has already been sold');
                        $AddThisBundle = false;
                    } elseif ($ExistingBundleQty==0) {
                        echo "<BR>There is none of " . $_POST['SerialNo' . $i] . " left.";
                        $AddThisBundle = false;
                    } else {
                        echo '<BR>' . _('There is only') . ' ' . $ExistingBundleQty . ' ' . _('of') . ' ' . $_POST['SerialNo' . $i] . ' ' . _('remaining') . '. ' . _('The entered quantity will be reduced to the remaining amount left of this batch/bundle/roll');
                        $_POST['Qty' . $i] = $ExistingBundleQty;
                        $AddThisBundle = true;
                    }
                }
                if ($AddThisBundle==true) {
                    $LineItem->SerialItems[$_POST['SerialNo' . $i]] = new SerialItem($_POST['SerialNo' . $i], $_POST['Qty' . $i]);
                }
            }
        }
    }

    for ($i=0; $i < count($_POST['Bundles']); $i++) {
        if ($LineItem->Serialised==1) {
            $LineItem->SerialItems[$_POST['Bundles'][$i]] = new SerialItem($_POST['Bundles'][$i], 1);
        }
    }
}

if (isset($_GET['Delete'])) {
    unset($LineItem->SerialItems[$_GET['Delete']]);
}
