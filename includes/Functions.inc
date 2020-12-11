<?php



function GeneraCuentaReferenciada($db, $string,$bank=1) {
	$arr_string = str_split($string);
	for($i=0;$i<count($arr_string);$i++)
	{
		$sql = "SELECT valor ";
		$sql .= " FROM conf_cuentasreferenciadas";
		$sql .= " WHERE bank=".$bank." and letra='" . $arr_string[$i] . "'";
		$result = DB_query($sql ,$db);
		if (DB_num_rows($result)!=0)
		{
			$myrow = DB_fetch_row($result);
			$arr_valores[$i] = $myrow[0];
		}
		else
		{
			$arr_valores[$i] = $arr_string[$i];
		}
	}
	
	$j=1;
	$valorfinal = 0;
	
	for($i=count($arr_valores)-1;$i>=0;$i--)
	{
		
		$j=$j+1;
		
		#echo '<br>'.$arr_string[$i];
		#echo '->'.$arr_valores[$i];
		
		if ($j==2)
		{
			$arr_valores[$i] = $arr_valores[$i]*2;
			$j=0;
		}
		
		#echo '-'.$arr_valores[$i];
		if (strlen($arr_valores[$i])==2)
		{
			$arr_valores[$i] = intval(substr($arr_valores[$i],0,1)) + intval(substr($arr_valores[$i],1,1));
		}
		
		#echo '->'.$arr_valores[$i];
		
		$valorfinal = $valorfinal + $arr_valores[$i];
	}
	
	#echo '<br>valor final =' . $valorfinal;
	$decena_superior =  $valorfinal + (10 - intval(substr($valorfinal,1,1)));
	
	$DigitoVerificador =  $decena_superior - $valorfinal;
	
	if ($DigitoVerificador==10)
	{
	 $DigitoVerificador=0;
	}
	
	//echo '<br>DigitoVerificador =' . $DigitoVerificador;
	return $DigitoVerificador;
}

function GeneraCuentaReferenciadaDos($db, $string) {
	$arr_string = str_split($string);
	for($i=0;$i<count($arr_string);$i++)
	{
		$sql = "SELECT valor ";
		$sql .= " FROM conf_cuentasreferenciadas";
		$sql .= " WHERE bank=2 and letra='" . $arr_string[$i] . "'";
		$result = DB_query($sql ,$db);
		if (DB_num_rows($result)!=0)
		{
			$myrow = DB_fetch_row($result);
			$arr_valores[$i] = $myrow[0];
		}
		else
		{
			$arr_valores[$i] = $arr_string[$i];
		}
	}
	$j=1;
	$cad=1;
	$valorfinal = 0;
	// calcula cadena de dos
	$cadenafinal='';
	for($i=count($arr_valores)-1;$i>=0;$i--){
		if ($cad==1){
			$arr_cadena[$i] = 2;
			$cad=2;
		}elseif($cad==2){
			$arr_cadena[$i] = 1;
			$cad=1;
		}
		$cadenafinal .= $arr_cadena[$i];
	}
	
	//echo 'cadena fin:'.$cadenafinal.'<br>';
	//echo 'cadena letras'.$string.'<br>';
	for($i=count($arr_valores)-1;$i>=0;$i--)
	{
		$j=$j+1;
		
		$arr_valores[$i] = intval($arr_valores[$i]) * intval($arr_cadena[$i]);
		
		if (strlen($arr_valores[$i])==2){
			$arr_valores[$i]=intval(substr($arr_valores[$i],0,1)) + intval(substr($arr_valores[$i],1,1));
		}
		
		$valorfinal = $valorfinal + $arr_valores[$i];
	}
	
	#echo '<br>valor final =' . $valorfinal;
	$decena_superior =  $valorfinal + (10 - intval(substr($valorfinal,1,1)));
	
	$DigitoVerificador =  $decena_superior - $valorfinal;
	
	if ($DigitoVerificador==10)
	{
	 $DigitoVerificador=0;
	}
	
	//echo '<br>DigitoVerificador =' . $DigitoVerificador;
	return $DigitoVerificador;
}


function InsertaCuentaBank($cuentaref,$idtrans,$bank,$db) {
	
	$SQL="insert into referenciasbancarias (transid,bancoid,referencia)
	      values(".$idtrans.",".$bank.",'".$cuentaref."')";
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
}

?>