<?php
//session_start();
$funcion = 141;
include('includes/session.inc');
include ('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

$option = $_POST['option'];
$ErrMsg = _( 'No transactions were returned by the SQL because' );
$contenido = array();
$result = false;
$SQL = '';
$BatchNo = "";

if($option == 'allunitsofmeasure')
{
    $info = array();
    $SQL = "SELECT  c_ClaveUnidad, Nombre, Simbolo FROM sat_unitsofmeasure ORDER BY Nombre asc";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
        if(!empty($myrow ['c_ClaveUnidad']) )
        {
            $Simbolo = $myrow ['Simbolo'];
            if (empty($Simbolo)) {
                $Simbolo = "";
            }

            $info[] = array( 'value' => ($myrow ['Nombre']), 'c_ClaveUnidad' => $myrow ['c_ClaveUnidad'], 'Nombre' => utf8_encode($myrow ['Nombre']), 'Simbolo' => utf8_encode($Simbolo) );
        }
            
    }
    $contenido = array('infounitsofmeasure' => $info);
    //echo "<PRE>".var_dump($contenido);
    $result = true;
}

$dataObj = array('sql' => $SQL, 'contenido' => $contenido, 'orderno' => $BatchNo, 'result' => $result);
echo json_encode($dataObj);

?>