<?php
/*
	16/SEPTIEMBRE/2012  -desarrollo- Creacion de funcion 

*/

/* ELEMENTOS SELECCIONADOS DEL FLUJO NORMAL */
if (isset($_POST['selMovimiento'])) {
    
    for ($i=0;$i<=count($_POST['selMovimiento'])-1; $i++) {
        
        //echo $i."-".$_POST['selMovimiento'][$i]."<br>";
        
        $umovto = $_POST['selMovimiento'][$i];
        
        //String con todas las id seleccionadas para volver a marcarlas como
        //seleccionadas una vez terminadas las operaciones con ellas !!!
        $stringFind = $stringFind . '-' . $umovto;
    
	    if (strlen($_POST['function_name'.$umovto]) > 0 AND strlen($_POST['function_orden'.$umovto]) > 0) {
		$sql = "update sec_functions
                        set shortdescription  = '".str_replace(",", "",CharClean(htmlspecialchars_decode($_POST['function_name'.$umovto],ENT_NOQUOTES)))."',
			    orderno = '".str_replace(",", "",CharClean(htmlspecialchars_decode($_POST['function_orden'.$umovto],ENT_NOQUOTES)))."',
			    categoryid = '".str_replace(",", "",CharClean(htmlspecialchars_decode($_POST['function_catego'.$umovto],ENT_NOQUOTES)))."'
			where functionid = '".$umovto."'";
		
		//echo $sql;
		
		$result = DB_query($sql,$db);
	    }
    }
}

?>