<?php
$pdfBytes = "<h1>holis</holis>";
echo $pdfBytes;

header('Content-type: application/pdf');
header('Content-Length: ' . strlen($pdfBytes));
header('Content-Disposition: inline; filename=report.pdf');
?>