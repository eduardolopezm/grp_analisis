<?php
if (isset($_POST['AddBatches']) && $_POST['AddBatches']!='') {
    for ($i=0; $i < count($_POST['Bundles']); $i++) {
            list($SerialNo, $Qty, $CostSerialItem,$StockIDParent2) = explode('/|/', $_POST['Bundles'][$i]);
        if ($Qty != 0) {
            $LineItem->SerialItems[$SerialNo] =
                new SerialItem($SerialNo, $Qty*($InOutModifier>0?1:-1), $CostSerialItem, $StockIDParent2);
        }
    }
}
if (isset($_GET['DELETEALL'])) {
        $RemAll = $_GET['DELETEALL'];
} else {
        $RemAll = 'NO';
}

if ($RemAll == 'YES') {
        unset($LineItem->SerialItems);
        $LineItem->SerialItems=array();
    unset($_SESSION['CurImportFile']);
}

if (isset($_GET['Delete'])) {
        unset($LineItem->SerialItems[$_GET['Delete']]);
}


include('includes/InputSerialItemsKeyedParent.php');
$valid = true;
