
$( document ).ready(function() {


if ($("#FromPeriod").length) $("#FromPeriod").multiselect('rebuild');
if ($("#ToPeriod").length) $("#ToPeriod").multiselect('rebuild');
if ($("#legalid").length) $("#legalid").multiselect('rebuild');
if ($("#xRegion").length) $("#xRegion").multiselect('rebuild');
if ($("#xDepartamento").length) $("#xDepartamento").multiselect('rebuild');
if ($("#unidadnegocio").length) $("#unidadnegocio").multiselect('rebuild');
if ($("#accounttype").length) $("#accounttype").multiselect('rebuild');

if ($("#FromPeriod").length) $("#noZeroes").multiselect('rebuild');

ocultaCargandoGeneral();


	
});