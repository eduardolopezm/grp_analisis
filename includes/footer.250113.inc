<?php

/* $Revision: 1.39 $ */

	echo '</td>';
	echo '</tr>';
	echo '</table>';

	echo '</td></tr>';
/*Do the borders */

	echo '</table>';
        if((!isset($_POST['directfunctionid'])) or ($_POST['directfunctionid'] <> '')){
           $_POST['directfunctionid']='0'; 
        }
        echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'identifier='.$identifier . '" method=post name="accessperfunctionid">';
        
	echo '<table style="width:1050px" id="footer">';
	echo '<tr><td style="text-align:left">';
        //echo 'ACCESO POR NUMERO DE FUNCION:';
        
        //echo '<input type="text" name="directfunctionid" size=4 value='.$_POST['directfunctionid'].'>';
		//echo '<input type=submit style="width:50px;" name="ir" size=5 VALUE="' . _('IR') . '">';
        
	setlocale(LC_ALL,$_SESSION['Language']);
	echo '</td><td style="text-align:right">'.(strftime('%A')).', '.(date($_SESSION['DefaultDateFormat']).' | '.(strftime(($DefaultClock==12) ? '%I:%M %p' : '%H:%M')));
	echo '</td></tr>';
	echo '</table>';
    
        echo '</form>';

	echo '<table style="width:1050px" class="footer">';
	echo '<tr>';
	echo '<td width="90%" class="footer">';

 	echo ' ' . _('Version') . ' - ' . $Version;
	echo '<br>webERP ' . _('Copyright') . ' &copy; weberp.org - '.date('Y').'';
	echo '</td>';
	echo '<td class="footer">';
 	echo '<a href="http://www.tecnoaplicada.com" rel="external"><img align=right src="Logotecnoaplicada.gif" alt="tecnoaplicada" title="webERP ' . _('Copyright') . ' &copy; webrp.org - ' . date('Y') . '" /></a>';
	echo '</td></tr>';
	echo '</table>';

	echo '</body>';
	echo '</html>';

?>