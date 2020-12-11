<?php
//echo 'entra cuatrooo';
$template = '
<style type="text/css">
.text {
  mso-number-format:"\@";
}
</style>
';

$template .= "
<table style='font-family: Arial, Georgia, Serif;'>

<tr>
	<td colspan='3' style='text-align:left; background-color:#4A452A; color:#fff'>
		<img src='http://" . $_SERVER['HTTP_HOST'] . "/" . dirname($_SERVER['PHP_SELF']) . "/companies/" . $_SESSION['DatabaseName'] . "/logo.jpg' alt='logo' height='100' width='300' />
		<h3>Solicitud de Cotización Proveedores</h3>
	</td>
</tr>

<tr>
	<td colspan='3'>&nbsp;</td>
</tr>

<tr>
	<td>
		<table style='border: 1px solid #000;' cellspacing='0'>
			<tr>
				<td style='border: 1px solid #000; background-color:#4A452A; color:#fff'>Contacto:</td>
				<td style='border: 1px solid #000'>$contactoRazonXLS</td>
			</tr>
			<tr>
				<td style='border: 1px solid #000; background-color:#4A452A; color:#fff'>Razon Social:</td>
				<td style='border: 1px solid #000'>$razonSocialXLS</td>
			</tr>
			<tr>
				<td style='border: 1px solid #000; background-color:#4A452A; color:#fff'>Domicilio Fiscal:</td>
				<td style='border: 1px solid #000'>$domicilioXLS</td>
			</tr>
			<tr>
				<td style='border: 1px solid #000; background-color:#4A452A; color:#fff'>Colonia:</td>
				<td style='border: 1px solid #000'>$coloniaXLS</td>
			</tr>
			<tr>
				<td style='border: 1px solid #000; background-color:#4A452A; color:#fff'>Ciudad / Estado:</td>
				<td style='border: 1px solid #000'>$ciudadXLS</td>
			</tr>
			<tr>
				<td style='border: 1px solid #000; background-color:#4A452A; color:#fff'>R.F.C:</td>
				<td style='border: 1px solid #000'>$rfcXLS</td>
			</tr>
			<tr>
				<td style='border: 1px solid #000; background-color:#4A452A; color:#fff'>Telefono / Fax:</td>
				<td style='border: 1px solid #000'>$faxXLS</td>
			</tr>
		</table>
	</td>
	<td>
		<table style='border: 1px solid #000;' cellspacing='0' align='left'>
			<tr>
				<td style='border: 1px solid #000; background-color:#4A452A; color:#fff'>Proveedor:</td>
				<td style='border: 1px solid #000'>$proveedorXLS</td>
			</tr>
			<tr>
				<td style='border: 1px solid #000; background-color:#4A452A; color:#fff'>Telefono:</td>
				<td style='border: 1px solid #000'>$telefonoXLS</td>
			</tr>
			<tr>
				<td style='border: 1px solid #000; background-color:#4A452A; color:#fff'>Contacto:</td>
				<td style='border: 1px solid #000'>$contactoXLS</td>
			</tr>
			<tr>
				<td style='border: 1px solid #000; background-color:#4A452A; color:#fff'>Fecha de Solicitud:</td>
				<td style='border: 1px solid #000'>$fechaSolicitudXLS</td>
			</tr>
			<tr>
				<td style='border: 1px solid #000; background-color:#4A452A; color:#fff'>No. de Solicitud:</td>
				<td style='border: 1px solid #000'>$noSolicitudXLS</td>
			</tr>
		</table>
	</td>
	<td>&nbsp;</td>
</tr>

<tr>
	<td colspan='3'>&nbsp;</td>
</tr>

<tr>
	<td colspan='3' style='text-align:left; background-color:#4A452A; color:#fff;'>
		<strong>Favor De Enviar Cotizacion A:</strong> $emailEnviarXLS
	</td>
</tr>

<tr>
	<td colspan='3'>&nbsp;</td>
</tr>

<tr>
	<td colspan='3'>
		<table style='border: 1px solid #000;' cellspacing='0'>
			<tr>
				<th style='border: 1px solid #000; background-color:#4A452A; color:#fff'>Clave</th>
				<th style='border: 1px solid #000; background-color:#4A452A; color:#fff'>Cant.</th>
				<th style='border: 1px solid #000; background-color:#4A452A; color:#fff'>UDM</th>
				<th style='border: 1px solid #000; background-color:#4A452A; color:#fff'>Descripcion</th>
				<th style='border: 1px solid #000; background-color:#4A452A; color:#fff'>Tiempo de Entrega</th>
				<th style='border: 1px solid #000; background-color:#4A452A; color:#fff'>Precio Unitario <br />(SIN I.V.A.)</th>
				<th style='border: 1px solid #000; background-color:#4A452A; color:#fff'>Precio Unitario <br />(CON I.V.A.)</th>
				<th style='border: 1px solid #000; background-color:#4A452A; color:#fff'>* Otros Cargos</th>
				<th style='border: 1px solid #000; background-color:#4A452A; color:#fff'>**Precio Neto</th>
				<th style='border: 1px solid #000; background-color:#4A452A; color:#fff'>**Precio de Lista</th>
			</tr>
";

foreach($productsTemplate as $t) {
	$template .= "	
			<tr>
				<td class='text' style='border: 1px solid #000'>" . $t['claveXLS'] . "</td>
				<td style='border: 1px solid #000'>" . $t['cantXLS'] . "</td>
				<td style='border: 1px solid #000'>" . $t['udmXLS'] . "</td>
				<td style='border: 1px solid #000'>" . $t['descXLS'] . "</td>
				<td style='border: 1px solid #000'>" . $t['tiempoXLS'] . "</td>
				<td style='border: 1px solid #000'>" . $t['precioXLS'] . "</td>
				<td style='border: 1px solid #000'>" . $t['precioIVAXLS'] . "</td>
				<td style='border: 1px solid #000'>" . $t['cargoXLS'] . "</td>
				<td style='border: 1px solid #000'>" . $t['netoXLS'] . "</td>
				<td style='border: 1px solid #000'>" . $t['listaXLS'] . "</td>
			</tr>
	";
}

$template .= "
		</table>
	</td>
</tr>

<tr>
	<td colspan='3'>&nbsp;</td>
</tr>

<tr>
	<td colspan='3' style='text-align:right; background-color:#4A452A; color:#fff;'>
		<strong>Total Precio Neto:</strong> $totalNetoXLS
	</td>
</tr>

<tr>
	<td colspan='3'>&nbsp;</td>
</tr>

<tr>
	<td colspan='3' style='text-align:right; background-color:#4A452A; color:#fff;'>
		<strong>Total Precio Lista:</strong> $totalListaXLS
	</td>
</tr>

<tr>
	<td colspan='3'>&nbsp;</td>
</tr>



<tr>
	<td colspan='3'>&nbsp;</td>
</tr>

<tr>
	<td colspan='3'>&nbsp;</td>
</tr>

<tr>
	<td colspan='3' style='text-align:center;'>
		<hr style='width: 280px' />
		Asistente de Direccion y Compras
	</td>
</tr>

</table>		
";

?>