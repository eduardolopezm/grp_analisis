/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Desarrollo
 * @version 0.1
 */

$( document ).ready(function() {
	muestraCargandoGeneral();
	
	// $("#xRegion").multiselect('rebuild');
	// $("#xArea").multiselect('rebuild');
	// $("#xDepto").multiselect('rebuild');
	//$("#tag").multiselect('rebuild');
	//$("#ue").multiselect('rebuild');
	//$("#cbotipopoliza").multiselect('rebuild');
	fnFormatoSelectGeneral(".tag");
	fnFormatoSelectGeneral(".ue");
	fnFormatoSelectGeneral(".cbotipopoliza");

	// $("#legalid").multiselect('rebuild');

	ocultaCargandoGeneral();
});