<?php

/* $Revision: 1.39 $ */
	
	/*
	$currentDate = date('d') . '/' . date('m') . '/' . date('Y');
	$currentDateDBFormat = date('Y') . '/' . date('m') . '/' . date('d');
	
	$rate = GetCurrencyRateByDate($currentDateDBFormat, 'USD', $db);
	if($rate == 0) {
		$rates = GetAllCurrencyRatesFromPeriodDLS($currentDate, $currentDate);
		$rate = number_format(1 / $rates[1]['rate'], 15);
		DB_query("INSERT INTO tipocambio VALUES(NULL, 'USD', '$rate', '$currentDateDBFormat')", $db);
		$rate = 1 / $rate;
		
		$rate = GetCurrencyRateByLastDateWRate($currentDateDBFormat, 'USD', $db);
	}
	*/
	/*if($_SESSION['ShowIndex']!=0){
		echo '  <tr>';
		echo '   <td class="footer_pag"><table align="center" width="100%" border="0" cellpadding="0" cellspacing="0" class="tabla_header">';
		echo '      <tr>';
		echo '        <td>&nbsp;</td>';
		echo '      </tr>';
		echo '      <tr>';
		echo '        <td>&nbsp;</td>'; 
		echo '      </tr>';
		echo '      <tr>';
		echo '        <td><table width="550" border="0" align="left" cellpadding="0" cellspacing="0">';
		echo '          <tr>';
		echo '            <td width="74"><img src="images/imgs/tecnoaplicada.gif" width="64" height="78" /></td>';
		echo '            <td class="textos_tecnoaplicada">Desarrollado por: tecnoaplicada Consulting International S.A. de C.V. Derechos Reservados 2009-2013 <br>
			' . (strftime('%A')).', '.(date($_SESSION['DefaultDateFormat']).' | '.(strftime(($DefaultClock==12) ? '%I:%M %p' : '%H:%M'))).' <br><a href="http://www.tecnoaplicada.com">www.tecnoaplicada.com</a> |  <a href="mailto:galvarez@tecnoaplicada.com">galvarez@tecnoaplicada.com</a></td>';
		echo '          </tr>';
		echo '        </table></td>';
		echo '      </tr>';
		echo '    </table></td>';
		echo '  </tr>';
		echo '  <tr>';
		echo '    <td>&nbsp;</td>';
		echo '  </tr>';
		echo '</table>';
		echo '</body>';
		echo '</html>';
		
	}else{

		$currentDateDBFormat = date('Y') . '/' . date('m') . '/' . date('d');
		$rate = GetCurrencyRateByLastDateWRate($currentDateDBFormat, 'USD', $db);
		
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		 
		if((!isset($_POST['directfunctionid'])) or ($_POST['directfunctionid'] <> '')){
			$_POST['directfunctionid']='0'; 
	        }
	        
		echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'identifier='.$identifier . '" method=post name="accessperfunctionid">';
	        
		echo '<table id="footer">';
		echo '<tr><td style="text-align:left">';
	        echo 'ACCESO POR NUMERO DE FUNCION:';
	        
	        echo '<input type="text" name="directfunctionid" size=4 value='.$_POST['directfunctionid'].'>';
		echo '<input type=submit style="width:50px;" name="ir" size=5 VALUE="' . _('IR') . '">';
	        
		setlocale(LC_ALL,$_SESSION['Language']);
		
		echo setlocale(LC_ALL,$_SESSION['Language']);
		
		echo '<br>bb:'.date();
		echo '</td><td style="text-align:right">Tipo de Cambio: ' . number_format($rate,4) . ' al:'.substr($currentDateDBFormat,0,10).'&nbsp;&nbsp;&nbsp;' . (strftime('%A')).', '.(date($_SESSION['DefaultDateFormat']).' | '.(strftime(($DefaultClock==12) ? '%I:%M %p' : '%H:%M')));
		echo '</td></tr>';
		
		echo '</table>';
	    
	        echo '</form>';

		echo '<table style="margin-left:0;width: 100%;font-family: Verdana, Arial, Helvetica;color: rgb(200,200,200);background-color: #fff5ec;text-align: right;">';
		echo '<tr><td width="20%" style="text-align:left;color:#006697;vertical-align:text-top" >';
	        echo 'Desarrollado por:</td><td width="70%" style="text-align:left;color:#006697;vertical-align:text-top" >tecnoaplicada Consulting International S.A. de C.V. <br>Derechos Reservados 2010<br><br>galvarez@tecnoaplicada.com';
	        echo '</td><td style="text-align:left;color:#006697;vertical-align:text-top"><a href="http://www.tecnoaplicada.com">www.tecnoaplicada.com</a></td></tr>';
		
		echo '<tr>';
		echo '<td colspan=2>';
		echo '</td>';
		echo '<td style="img-align:right">';
	 	echo '<a href="http://www.tecnoaplicada.com" rel="external"><img align=right src="Logotecnoaplicada.gif" alt="tecnoaplicada" title="webERP ' . _('Copyright') . '" /></a>';
		echo '</td></tr>';
		
		echo '</table>';
		 
		echo '</td></tr>';
		//Do the borders 
		echo '</table>';
		
	        

		echo '</body>';
		echo '</html>';
	}*/

?>