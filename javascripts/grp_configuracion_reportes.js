function fnCambioRazonSocial() {
	//console.log("fnObtenerUnidadNegocio");
	// Inicio Unidad de Negocio
	legalid = $("#selectRazonSocial").val();
	//Opcion para operacion
	dataObj = { 
	      option: 'mostrarUnidadNegocio',
	      legalid: legalid
	    };
	//Obtener datos
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/imprimirreportesconac_modelo.php",
	    data:dataObj
	})
	.done(function( data ) {
	  
	  if(data.result){
	      //Si trae informacion
	      
	      dataJson = data.contenido.datos;
	      var contenido = "<option value='0'>Seleccionar...</option>";
	      for (var info in dataJson) {
	        contenido += "<option value='"+dataJson[info].tagref+"'>"+dataJson[info].tagdescription+"</option>";
	      }
		$('#selectUnidadNegocio').empty();
		$('#selectUnidadNegocio').append(contenido);
		$('#selectUnidadNegocio').multiselect('rebuild');

	  }else{
	      // console.log("ERROR Modelo");
	      // console.log( JSON.stringify(data) ); 
	  }
	})
	.fail(function(result) {
	  // console.log("ERROR");
	  // console.log( result );
	});
	// Fin Unidad de Negocio
}



function fnCambioUnidadNegocio() {
	//console.log("fnCambioRazonSocial");
	tagref = $("#selectUnidadNegocio").val();
	muestraCargandoGeneral();

	// Datos lista de busqueda
	fnMostrarConfiguracionReportesDesdeBD(tagref);

	ocultaCargandoGeneral();


}
 

 function fnSeleccionarValorDelReporte(aValor, aControl) {


 	if (aValor == null) $("#"+aControl).selectpicker('deselectAll');
 	arrayActual = $("#"+aControl).val();
 	if (arrayActual == null) $("#"+aControl).selectpicker('val',aValor);
 	else {
 	  arrayActual.push(aValor);
    	$("#"+aControl).selectpicker('val',arrayActual);
    }
	$("#"+aControl).multiselect('refresh');
	$("."+aControl).css("display", "none");
	
}


function fnMostrarConfiguracionReportesDesdeBD(aTagRef){
	//contenidoMostrarConfiguracion = "";
	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarConfiguracionReportesDesdeBD',
	        tagref: aTagRef
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/grp_configuracion_reportes_modelo.php",
	      data:dataObj,
	      async: false
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datosCatalogo;

	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraEfectivosyEquivalentes");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraDerechoARecibirEfectivosyEquivalentes");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraDerechosaRecibirBienesoServicios");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraInventarios");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraAlmacenes");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraEstimacionporPerdidaoDeteriorodeActivosCirculantes");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraOtrosActivosCirculantes");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraInversionesFinancierasaLargoPlazo");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraDerechosaRecibirEfectivooEquivalentesaLargoPlazo");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraBienesInmueblesInfraestructurayConstruccionesenProceso");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraBienesMuebles");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraActivosIntangibles");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraDepreciacionDeterioroyAmortizacionAcumuladadeBienes");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraActivosDiferidos");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraEstimacionporPerdidaoDeteriorodeActivosnoCirculantes");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraOtrosActivosnoCirculantes");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraCuentasporPagaraCortoPlazo");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraDocumentosporPagaraCortoPlazo");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo");

	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraTitulosyValoresaCortoPlazo");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraPasivosDiferidosaCortoPlazo");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraProvisionesaCortoPlazo");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraOtrosPasivosaCortoPlazo");


	    		//pasivo no circulante 2.2


	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraCuentasporPagaraLargoPlazo");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraDocumentosporPagaraLargoPlazo");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraDeudaPublicaaLargoPlazo");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraPasivosDiferidosaLargoPlazo");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraProvisionesaLargoPlazo");

	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraAportaciones");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraDonacionesdeCapital");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraActualizaciondelaHaciendaPublicaPatrimonio");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraResultadosdeEjerciciosAhorroDesahorro");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraResultadosdeEjerciciosAnteriores");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraRevaluos");
	    	fnSeleccionarValorDelReporte(null, "selectSituacionFinancieraReservas");


	    	fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesImpuestos");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesCuotasyAportacionesdeSeguridadSocial");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesContribucionesdeMejoras");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesDerechos");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesProductosdeTipoCorriente");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesAprovechamientosdeTipoCorriente");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesIngresosporVentadeBienesyServicios");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesPendientesdeLiquidacionoPago");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesParticipacionesyAportaciones");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesTransferenciaAsignacionesSubsidiosyOtrasAyudas");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesIngresosFinancieros");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesIncrementoporVariaciondeInventarios");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesDisminuciondelExcesodeEstimacionesporPerdidaoDeteriorouObsolescencia");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesDisminuciondelExcesodeProvisiones");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesOtrosIngresosyBeneficiosVarios");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesServiciosPersonales");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesMaterialesySuministros");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesServiciosGenerales");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesTransferenciasInternasyAsignacionesalSectorPublico");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesTransferenciasalRestodelSectorPublico");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesSubsidiosySubvenciones");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesAyudasSociales");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesPensionesyJubilaciones");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesTransferenciasaFideicomisosMandatosyContratosAnalogos");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesTransferenciasalaSeguridadSocial");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesDonativos");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesTransferenciasalExterior");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesParticipaciones");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesAportaciones");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesConvenios");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesInteresesdelaDeudaPublica");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesComisionesdelaDeudaPublica");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesGastosdelaDeudaPublica");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesCostoporCoberturas");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesApoyosFinancieros");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesEstimacionesDepreciacionesDeteriorosObsolescenciayAmortizaciones");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesProvisiones");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesDisminuciondeInventarios");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesAumentoporInsuficienciadeEstimacionesporPerdidaoDeterioroyObsolescencia");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesAumentoporInsuficienciadeProvisiones");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesOtrosGastos");
			fnSeleccionarValorDelReporte(null, "selectEstadoDeActividadesInversionPublicanoCapitalizable");


			fnSeleccionarValorDelReporte(null, "selectflujoefectivoImpuestos");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoCuotasyAportacionesdeSeguridadSocial");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoContribucionesdemejoras");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoDerechos");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoProductosdeTipoCorriente");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoAprovechamientosdeTipoCorriente");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoIngresosporVentadeBienesyServicios");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoPendientesdeLiquidacionoPago");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoParticipacionesyAportaciones");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoTransferenciasAsignacionesySubsidiosyOtrasAyudas");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoOtrosOrigenesdeOperacion");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoServiciosPersonales");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoMaterialesySuministros");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoServiciosGenerales");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoTransferenciasInternasyAsignacionesalSectorPublico");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoTransferenciasalrestodelSectorPublico");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoSubsidiosySubvenciones");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoAyudasSociales");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoPensionesyJubilaciones");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoTransferenciasaFideicomisosMandatosyContratosAnalogos");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoTransferenciasalaSeguridadSocial");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoDonativos");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoTransferenciasalExteriorParticipaciones");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoAportaciones");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoConvenios");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoBienesInmueblesInfraestructurayConstruccionesenProceso");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoBienesMuebles");
			fnSeleccionarValorDelReporte(null, "selectflujoefectivoOtrosOrigenesdeInversion");




	    	for (x in info) { 
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPEfectivoyEquivalentes")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraEfectivosyEquivalentes");
	    			

	    		//selecciona el valor que tiene guardado en la base de datos de cada control
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPDerechoARecibirEfectivoyEquivalentes")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraDerechoARecibirEfectivosyEquivalentes");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPDerechosaRecibirBienesoServicios")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraDerechosaRecibirBienesoServicios");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPInventarios")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraInventarios");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPAlmacenes")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraAlmacenes");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosCirculantes")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraEstimacionporPerdidaoDeteriorodeActivosCirculantes");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPOtrosActivosCirculantes")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraOtrosActivosCirculantes");



	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPInversionesFinancierasaLargoPlazo")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraInversionesFinancierasaLargoPlazo");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPDerechosaRecibirEfectivooEquivalentesaLargoPlazo")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraDerechosaRecibirEfectivooEquivalentesaLargoPlazo");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPBienesInmueblesInfraestructurayConstruccionesenProceso")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraBienesInmueblesInfraestructurayConstruccionesenProceso");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPBienesMuebles")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraBienesMuebles");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPActivosIntangibles")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraActivosIntangibles");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPDepreciacionDeterioroyAmortizacionAcumuladadeBienes")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraDepreciacionDeterioroyAmortizacionAcumuladadeBienes");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPActivosDiferidos")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraActivosDiferidos");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosnoCirculantes")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraEstimacionporPerdidaoDeteriorodeActivosnoCirculantes");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPOtrosActivosnoCirculantes")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraOtrosActivosnoCirculantes");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPCuentasporPagaraCortoPlazo")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraCuentasporPagaraCortoPlazo");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPDocumentosporPagaraCortoPlazo")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraDocumentosporPagaraCortoPlazo");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo");

	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPTitulosyValoresaCortoPlazo")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraTitulosyValoresaCortoPlazo");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPPasivosDiferidosaCortoPlazo")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraPasivosDiferidosaCortoPlazo");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPProvisionesaCortoPlazo")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraProvisionesaCortoPlazo");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPOtrosPasivosaCortoPlazo")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraOtrosPasivosaCortoPlazo");


	    		//pasivo no circulante 2.2


	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPCuentasporPagaraLargoPlazo")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraCuentasporPagaraLargoPlazo");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPDocumentosporPagaraLargoPlazo")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraDocumentosporPagaraLargoPlazo");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPDeudaPublicaaLargoPlazo")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraDeudaPublicaaLargoPlazo");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPPasivosDiferidosaLargoPlazo")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraPasivosDiferidosaLargoPlazo");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPProvisionesaLargoPlazo")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraProvisionesaLargoPlazo");





	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPAportaciones")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraAportaciones");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPDonacionesdeCapital")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraDonacionesdeCapital");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPActualizaciondelaHaciendaPublicaPatrimonio")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraActualizaciondelaHaciendaPublicaPatrimonio");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPResultadosdeEjerciciosAhorroDesahorro")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraResultadosdeEjerciciosAhorroDesahorro");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPResultadosdeEjerciciosAnteriores")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraResultadosdeEjerciciosAnteriores");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPRevaluos")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraRevaluos");
	    		if ((info[x].reporte=="SituacionFinanciera") && (info[x].parametro=="RSituacionFinancieraPReservas")) fnSeleccionarValorDelReporte(info[x].valor, "selectSituacionFinancieraReservas");


	    		if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPImpuestos")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesImpuestos");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPCuotasyAportacionesdeSeguridadSocial")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesCuotasyAportacionesdeSeguridadSocial");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPContribucionesdeMejoras")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesContribucionesdeMejoras");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPDerechos")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesDerechos");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPProductosdeTipoCorriente")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesProductosdeTipoCorriente");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPAprovechamientosdeTipoCorriente")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesAprovechamientosdeTipoCorriente");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPIngresosporVentadeBienesyServicios")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesIngresosporVentadeBienesyServicios");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPPendientesdeLiquidacionoPago")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesPendientesdeLiquidacionoPago");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPParticipacionesyAportaciones")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesParticipacionesyAportaciones");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPTransferenciaAsignacionesSubsidiosyOtrasAyudas")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesTransferenciaAsignacionesSubsidiosyOtrasAyudas");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPIngresosFinancieros")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesIngresosFinancieros");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPIncrementoporVariaciondeInventarios")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesIncrementoporVariaciondeInventarios");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPDisminuciondelExcesodeEstimacionesporPerdidaoDeteriorouObsolescencia")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesDisminuciondelExcesodeEstimacionesporPerdidaoDeteriorouObsolescencia");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPDisminuciondelExcesodeProvisiones")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesDisminuciondelExcesodeProvisiones");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPOtrosIngresosyBeneficiosVarios")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesOtrosIngresosyBeneficiosVarios");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPServiciosPersonales")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesServiciosPersonales");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPMaterialesySuministros")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesMaterialesySuministros");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPServiciosGenerales")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesServiciosGenerales");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPTransferenciasInternasyAsignacionesalSectorPublico")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesTransferenciasInternasyAsignacionesalSectorPublico");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPTransferenciasalRestodelSectorPublico")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesTransferenciasalRestodelSectorPublico");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPSubsidiosySubvenciones")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesSubsidiosySubvenciones");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPAyudasSociales")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesAyudasSociales");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPPensionesyJubilaciones")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesPensionesyJubilaciones");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPTransferenciasaFideicomisosMandatosyContratosAnalogos")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesTransferenciasaFideicomisosMandatosyContratosAnalogos");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPTransferenciasalaSeguridadSocial")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesTransferenciasalaSeguridadSocial");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPDonativos")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesDonativos");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPTransferenciasalExterior")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesTransferenciasalExterior");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPParticipaciones")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesParticipaciones");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPAportaciones")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesAportaciones");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPConvenios")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesConvenios");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPInteresesdelaDeudaPublica")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesInteresesdelaDeudaPublica");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPComisionesdelaDeudaPublica")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesComisionesdelaDeudaPublica");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPGastosdelaDeudaPublica")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesGastosdelaDeudaPublica");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPCostoporCoberturas")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesCostoporCoberturas");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPApoyosFinancieros")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesApoyosFinancieros");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPEstimacionesDepreciacionesDeteriorosObsolescenciayAmortizaciones")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesEstimacionesDepreciacionesDeteriorosObsolescenciayAmortizaciones");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPProvisiones")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesProvisiones");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPDisminuciondeInventarios")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesDisminuciondeInventarios");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPAumentoporInsuficienciadeEstimacionesporPerdidaoDeterioroyObsolescencia")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesAumentoporInsuficienciadeEstimacionesporPerdidaoDeterioroyObsolescencia");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPAumentoporInsuficienciadeProvisiones")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesAumentoporInsuficienciadeProvisiones");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPOtrosGastos")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesOtrosGastos");
				if ((info[x].reporte=="EstadoDeActividades") && (info[x].parametro=="REstadoDeActividadesPInversionPublicanoCapitalizable")) fnSeleccionarValorDelReporte(info[x].valor, "selectEstadoDeActividadesInversionPublicanoCapitalizable");



				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPImpuestos")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoImpuestos");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPCuotasyAportacionesdeSeguridadSocial")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoCuotasyAportacionesdeSeguridadSocial");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPContribucionesdemejoras")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoContribucionesdemejoras");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPDerechos")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoDerechos");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPProductosdeTipoCorriente")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoProductosdeTipoCorriente");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPAprovechamientosdeTipoCorriente")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoAprovechamientosdeTipoCorriente");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPIngresosporVentadeBienesyServicios")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoIngresosporVentadeBienesyServicios");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPPendientesdeLiquidacionoPago")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoPendientesdeLiquidacionoPago");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPParticipacionesyAportaciones")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoParticipacionesyAportaciones");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPTransferenciasAsignacionesySubsidiosyOtrasAyudas")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoTransferenciasAsignacionesySubsidiosyOtrasAyudas");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPOtrosOrigenesdeOperacion")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoOtrosOrigenesdeOperacion");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPServiciosPersonales")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoServiciosPersonales");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPMaterialesySuministros")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoMaterialesySuministros");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPServiciosGenerales")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoServiciosGenerales");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPTransferenciasInternasyAsignacionesalSectorPublico")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoTransferenciasInternasyAsignacionesalSectorPublico");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPTransferenciasalrestodelSectorPublico")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoTransferenciasalrestodelSectorPublico");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPSubsidiosySubvenciones")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoSubsidiosySubvenciones");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPAyudasSociales")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoAyudasSociales");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPPensionesyJubilaciones")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoPensionesyJubilaciones");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPTransferenciasaFideicomisosMandatosyContratosAnalogos")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoTransferenciasaFideicomisosMandatosyContratosAnalogos");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPTransferenciasalaSeguridadSocial")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoTransferenciasalaSeguridadSocial");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPDonativos")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoDonativos");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPTransferenciasalExteriorParticipaciones")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoTransferenciasalExteriorParticipaciones");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPAportaciones")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoAportaciones");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPConvenios")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoConvenios");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPBienesInmueblesInfraestructurayConstruccionesenProceso")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoBienesInmueblesInfraestructurayConstruccionesenProceso");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPBienesMuebles")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoBienesMuebles");
				if ((info[x].reporte=="flujoefectivo") && (info[x].parametro=="RflujoefectivoPOtrosOrigenesdeInversion")) fnSeleccionarValorDelReporte(info[x].valor, "selectflujoefectivoOtrosOrigenesdeInversion");


				if ((info[x].reporte=="analiticoIngresos") && (info[x].parametro=="RanaliticoIngresosPImpuestos")) fnSeleccionarValorDelReporte(info[x].valor, "selectanaliticoIngresosImpuestos");
				if ((info[x].reporte=="analiticoIngresos") && (info[x].parametro=="RanaliticoIngresosPCuotasYAportacionesDeSeguridadSocial")) fnSeleccionarValorDelReporte(info[x].valor, "selectanaliticoIngresosCuotasYAportacionesDeSeguridadSocial");
				if ((info[x].reporte=="analiticoIngresos") && (info[x].parametro=="RanaliticoIngresosPContribucionesYMejoras")) fnSeleccionarValorDelReporte(info[x].valor, "selectanaliticoIngresosContribucionesYMejoras");
				if ((info[x].reporte=="analiticoIngresos") && (info[x].parametro=="RanaliticoIngresosPDrechos")) fnSeleccionarValorDelReporte(info[x].valor, "selectanaliticoIngresosDrechos");
				if ((info[x].reporte=="analiticoIngresos") && (info[x].parametro=="RanaliticoIngresosPProductos")) fnSeleccionarValorDelReporte(info[x].valor, "selectanaliticoIngresosProductos");
				if ((info[x].reporte=="analiticoIngresos") && (info[x].parametro=="RanaliticoIngresosPAprovechamientos")) fnSeleccionarValorDelReporte(info[x].valor, "selectanaliticoIngresosAprovechamientos");
				if ((info[x].reporte=="analiticoIngresos") && (info[x].parametro=="RanaliticoIngresosPIngresosPorVentasdeBienesYServicios")) fnSeleccionarValorDelReporte(info[x].valor, "selectanaliticoIngresosIngresosPorVentasdeBienesYServicios");
				if ((info[x].reporte=="analiticoIngresos") && (info[x].parametro=="RanaliticoIngresosPParticipacionesYAportaciones")) fnSeleccionarValorDelReporte(info[x].valor, "selectanaliticoIngresosParticipacionesYAportaciones");
				if ((info[x].reporte=="analiticoIngresos") && (info[x].parametro=="RanaliticoIngresosPTransferenciasAsignacionesSubsidiosYOtrasAyudas")) fnSeleccionarValorDelReporte(info[x].valor, "selectanaliticoIngresosTransferenciasAsignacionesSubsidiosYOtrasAyudas");
				if ((info[x].reporte=="analiticoIngresos") && (info[x].parametro=="RanaliticoIngresosPIngresosDerivadosDeFinanciamientos")) fnSeleccionarValorDelReporte(info[x].valor, "selectanaliticoIngresosIngresosDerivadosDeFinanciamientos");







			}
 		}	

	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}


function fnFiltrarPorCuenta(aInfo, aFiltro, aControl)
{
	contenido = "";
	for (x in info) 
	{ 
		if (aFiltro == null)
			contenido += '<option value="'+aInfo[x].id_cuenta+'">' +aInfo[x].descripcion +'</option>';
		else
		if (aInfo[x].id_cuenta.startsWith(aFiltro))
	       contenido += '<option value="'+aInfo[x].id_cuenta+'">' +aInfo[x].descripcion +'</option>';
 	} 

 	$(aControl).html(contenido);
		 	$(aControl).multiselect({
		            enableFiltering: true,
		            filterBehavior: 'text',
		            includeSelectAllOption: true


		        });
}
 function fnMostrarDatos(ur){
	

	contenido = "";

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogoDeCuentas',
	        ur: ur
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/grp_configuracion_reportes_modelo.php",
	      data:dataObj,
	      async: false
	  })
	.done(function( data ) {
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datosCatalogo;

	    	//construye todos las opciones del catalogo de cuentas
	    	

 			fnFiltrarPorCuenta(info, '1.1.1', '#selectSituacionFinancieraEfectivosyEquivalentes');
 			fnFiltrarPorCuenta(info, '1.1.2', '#selectSituacionFinancieraDerechoARecibirEfectivosyEquivalentes');
 			fnFiltrarPorCuenta(info, '1.1.3', '#selectSituacionFinancieraDerechosaRecibirBienesoServicios');
 			fnFiltrarPorCuenta(info, '1.1.4', '#selectSituacionFinancieraInventarios');
 			fnFiltrarPorCuenta(info, '1.1.5', '#selectSituacionFinancieraAlmacenes');
 			fnFiltrarPorCuenta(info, '1.1.6', '#selectSituacionFinancieraEstimacionporPerdidaoDeteriorodeActivosCirculantes');
 			fnFiltrarPorCuenta(info, '1.1.9', '#selectSituacionFinancieraOtrosActivosCirculantes');
 			
 			fnFiltrarPorCuenta(info, '1.2.1', '#selectSituacionFinancieraInversionesFinancierasaLargoPlazo');
 			fnFiltrarPorCuenta(info, '1.2.2', '#selectSituacionFinancieraDerechosaRecibirEfectivooEquivalentesaLargoPlazo');
 			fnFiltrarPorCuenta(info, '1.2.3', '#selectSituacionFinancieraBienesInmueblesInfraestructurayConstruccionesenProceso');
 			fnFiltrarPorCuenta(info, '1.2.4', '#selectSituacionFinancieraBienesMuebles');
 			fnFiltrarPorCuenta(info, '1.2.5', '#selectSituacionFinancieraActivosIntangibles');
 			fnFiltrarPorCuenta(info, '1.2.6', '#selectSituacionFinancieraDepreciacionDeterioroyAmortizacionAcumuladadeBienes');
 			fnFiltrarPorCuenta(info, '1.2.7', '#selectSituacionFinancieraActivosDiferidos');
 			fnFiltrarPorCuenta(info, '1.2.8', '#selectSituacionFinancieraEstimacionporPerdidaoDeteriorodeActivosnoCirculantes');
 			fnFiltrarPorCuenta(info, '1.2.9', '#selectSituacionFinancieraOtrosActivosnoCirculantes');


 			fnFiltrarPorCuenta(info, '2.1.1', '#selectSituacionFinancieraCuentasporPagaraCortoPlazo');
 			fnFiltrarPorCuenta(info, '2.1.2', '#selectSituacionFinancieraDocumentosporPagaraCortoPlazo');
 			fnFiltrarPorCuenta(info, '2.1.3', '#selectSituacionFinancieraPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo');
 			fnFiltrarPorCuenta(info, '2.1.4', '#selectSituacionFinancieraTitulosyValoresaCortoPlazo');
 			fnFiltrarPorCuenta(info, '2.1.5', '#selectSituacionFinancieraPasivosDiferidosaCortoPlazo');
 			fnFiltrarPorCuenta(info, '2.1.6', '#selectSituacionFinancieraFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo');
 			fnFiltrarPorCuenta(info, '2.1.7', '#selectSituacionFinancieraProvisionesaCortoPlazo');
 			fnFiltrarPorCuenta(info, '2.1.9', '#selectSituacionFinancieraOtrosPasivosaCortoPlazo');

 			fnFiltrarPorCuenta(info, '2.2.1', '#selectSituacionFinancieraCuentasporPagaraLargoPlazo');
 			fnFiltrarPorCuenta(info, '2.2.2', '#selectSituacionFinancieraDocumentosporPagaraLargoPlazo');
 			fnFiltrarPorCuenta(info, '2.2.3', '#selectSituacionFinancieraDeudaPublicaaLargoPlazo');
 			fnFiltrarPorCuenta(info, '2.2.4', '#selectSituacionFinancieraPasivosDiferidosaLargoPlazo');
 			fnFiltrarPorCuenta(info, '2.2.5', '#selectSituacionFinancieraFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo');
 			fnFiltrarPorCuenta(info, '2.2.6', '#selectSituacionFinancieraProvisionesaLargoPlazo');

 			fnFiltrarPorCuenta(info, '3.1.1', '#selectSituacionFinancieraAportaciones');
 			fnFiltrarPorCuenta(info, '3.1.2', '#selectSituacionFinancieraDonacionesdeCapital');
 			fnFiltrarPorCuenta(info, '3.1.3', '#selectSituacionFinancieraActualizaciondelaHaciendaPublicaPatrimonio');


 			fnFiltrarPorCuenta(info, '3.2.1', '#selectSituacionFinancieraResultadosdeEjerciciosAhorroDesahorro');
 			fnFiltrarPorCuenta(info, '3.2.2', '#selectSituacionFinancieraResultadosdeEjerciciosAnteriores');
 			fnFiltrarPorCuenta(info, '3.2.3', '#selectSituacionFinancieraRevaluos');
 			fnFiltrarPorCuenta(info, '3.2.4', '#selectSituacionFinancieraReservas');

 			//fnFiltrarPorCuenta(info, null, '#selectTodos');

 			
 			fnFiltrarPorCuenta(info, '4.1.1', '#selectEstadoDeActividadesImpuestos');
			fnFiltrarPorCuenta(info, '4.1.2', '#selectEstadoDeActividadesCuotasyAportacionesdeSeguridadSocial');
			fnFiltrarPorCuenta(info, '4.1.3', '#selectEstadoDeActividadesContribucionesdeMejoras');
			fnFiltrarPorCuenta(info, '4.1.4', '#selectEstadoDeActividadesDerechos');
			fnFiltrarPorCuenta(info, '4.1.5', '#selectEstadoDeActividadesProductosdeTipoCorriente');
			fnFiltrarPorCuenta(info, '4.1.6', '#selectEstadoDeActividadesAprovechamientosdeTipoCorriente');
			fnFiltrarPorCuenta(info, '4.1.7', '#selectEstadoDeActividadesIngresosporVentadeBienesyServicios');
			fnFiltrarPorCuenta(info, '4.1.9', '#selectEstadoDeActividadesIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores');
			fnFiltrarPorCuenta(info, '4.1.9', '#selectEstadoDeActividadesPendientesdeLiquidacionoPago');
			fnFiltrarPorCuenta(info, '4.2.1', '#selectEstadoDeActividadesParticipacionesyAportaciones');
			fnFiltrarPorCuenta(info, '4.2.2', '#selectEstadoDeActividadesTransferenciaAsignacionesSubsidiosyOtrasAyudas');
			fnFiltrarPorCuenta(info, '4.3.1', '#selectEstadoDeActividadesIngresosFinancieros');
			fnFiltrarPorCuenta(info, '4.3.2', '#selectEstadoDeActividadesIncrementoporVariaciondeInventarios');
			fnFiltrarPorCuenta(info, '4.3.3', '#selectEstadoDeActividadesDisminuciondelExcesodeEstimacionesporPerdidaoDeteriorouObsolescencia');
			fnFiltrarPorCuenta(info, '4.3.4', '#selectEstadoDeActividadesDisminuciondelExcesodeProvisiones');
			fnFiltrarPorCuenta(info, '4.3.9', '#selectEstadoDeActividadesOtrosIngresosyBeneficiosVarios');
			fnFiltrarPorCuenta(info, '5.1.1', '#selectEstadoDeActividadesServiciosPersonales');
			fnFiltrarPorCuenta(info, '5.1.2', '#selectEstadoDeActividadesMaterialesySuministros');
			fnFiltrarPorCuenta(info, '5.1.3', '#selectEstadoDeActividadesServiciosGenerales');
			fnFiltrarPorCuenta(info, '5.2.1', '#selectEstadoDeActividadesTransferenciasInternasyAsignacionesalSectorPublico');
			fnFiltrarPorCuenta(info, '5.2.2', '#selectEstadoDeActividadesTransferenciasalRestodelSectorPublico');
			fnFiltrarPorCuenta(info, '5.2.3', '#selectEstadoDeActividadesSubsidiosySubvenciones');
			fnFiltrarPorCuenta(info, '5.2.4', '#selectEstadoDeActividadesAyudasSociales');
			fnFiltrarPorCuenta(info, '5.2.5', '#selectEstadoDeActividadesPensionesyJubilaciones');
			fnFiltrarPorCuenta(info, '5.2.6', '#selectEstadoDeActividadesTransferenciasaFideicomisosMandatosyContratosAnalogos');
			fnFiltrarPorCuenta(info, '5.2.7', '#selectEstadoDeActividadesTransferenciasalaSeguridadSocial');
			fnFiltrarPorCuenta(info, '5.2.8', '#selectEstadoDeActividadesDonativos');
			fnFiltrarPorCuenta(info, '5.2.9', '#selectEstadoDeActividadesTransferenciasalExterior');
			fnFiltrarPorCuenta(info, '5.3.1', '#selectEstadoDeActividadesParticipaciones');
			fnFiltrarPorCuenta(info, '5.3.2', '#selectEstadoDeActividadesAportaciones');
			fnFiltrarPorCuenta(info, '5.3.3', '#selectEstadoDeActividadesConvenios');
			fnFiltrarPorCuenta(info, '5.4.1', '#selectEstadoDeActividadesInteresesdelaDeudaPublica');
			fnFiltrarPorCuenta(info, '5.4.2', '#selectEstadoDeActividadesComisionesdelaDeudaPublica');
			fnFiltrarPorCuenta(info, '5.4.3', '#selectEstadoDeActividadesGastosdelaDeudaPublica');
			fnFiltrarPorCuenta(info, '5.4.4', '#selectEstadoDeActividadesCostoporCoberturas');
			fnFiltrarPorCuenta(info, '5.4.5', '#selectEstadoDeActividadesApoyosFinancieros');
			fnFiltrarPorCuenta(info, '5.5.1', '#selectEstadoDeActividadesEstimacionesDepreciacionesDeteriorosObsolescenciayAmortizaciones');
			fnFiltrarPorCuenta(info, '5.5.2', '#selectEstadoDeActividadesProvisiones');
			fnFiltrarPorCuenta(info, '5.5.3', '#selectEstadoDeActividadesDisminuciondeInventarios');
			fnFiltrarPorCuenta(info, '5.5.4', '#selectEstadoDeActividadesAumentoporInsuficienciadeEstimacionesporPerdidaoDeterioroyObsolescencia');
			fnFiltrarPorCuenta(info, '5.5.5', '#selectEstadoDeActividadesAumentoporInsuficienciadeProvisiones');
			fnFiltrarPorCuenta(info, '5.5.9', '#selectEstadoDeActividadesOtrosGastos');
			fnFiltrarPorCuenta(info, '5.6.1', '#selectEstadoDeActividadesInversionPublicanoCapitalizable');


			fnFiltrarPorCuenta(info, '4.1.1', '#selectflujoefectivoImpuestos');
			fnFiltrarPorCuenta(info, '4.1.2', '#selectflujoefectivoCuotasyAportacionesdeSeguridadSocial');
			fnFiltrarPorCuenta(info, '4.1.3', '#selectflujoefectivoContribucionesdemejoras');
			fnFiltrarPorCuenta(info, '4.1.4', '#selectflujoefectivoDerechos');
			fnFiltrarPorCuenta(info, '4.1.5', '#selectflujoefectivoProductosdeTipoCorriente');
			fnFiltrarPorCuenta(info, '4.1.6', '#selectflujoefectivoAprovechamientosdeTipoCorriente');
			fnFiltrarPorCuenta(info, '4.1.7', '#selectflujoefectivoIngresosporVentadeBienesyServicios');
			fnFiltrarPorCuenta(info, '4.1.9', '#selectflujoefectivoIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores');
			fnFiltrarPorCuenta(info, '4.1.9', '#selectflujoefectivoPendientesdeLiquidacionoPago');
			fnFiltrarPorCuenta(info, '4.2.1', '#selectflujoefectivoParticipacionesyAportaciones');
			fnFiltrarPorCuenta(info, '4.2.2', '#selectflujoefectivoTransferenciasAsignacionesySubsidiosyOtrasAyudas');
			fnFiltrarPorCuenta(info, '5.1.1', '#selectflujoefectivoOtrosOrigenesdeOperacion');
			
			fnFiltrarPorCuenta(info, '5.1.1', '#selectflujoefectivoServiciosPersonales');
			fnFiltrarPorCuenta(info, '5.1.2', '#selectflujoefectivoMaterialesySuministros');
			fnFiltrarPorCuenta(info, '5.1.3', '#selectflujoefectivoServiciosGenerales');
			fnFiltrarPorCuenta(info, '5.2.1', '#selectflujoefectivoTransferenciasInternasyAsignacionesalSectorPublico');
			fnFiltrarPorCuenta(info, '5.2.1', '#selectflujoefectivoTransferenciasalrestodelSectorPublico');
			fnFiltrarPorCuenta(info, '5.2.3', '#selectflujoefectivoSubsidiosySubvenciones');
			fnFiltrarPorCuenta(info, '5.2.4', '#selectflujoefectivoAyudasSociales');
			fnFiltrarPorCuenta(info, '5.2.5', '#selectflujoefectivoPensionesyJubilaciones');
			fnFiltrarPorCuenta(info, '5.2.6', '#selectflujoefectivoTransferenciasaFideicomisosMandatosyContratosAnalogos');
			fnFiltrarPorCuenta(info, '5.2.7', '#selectflujoefectivoTransferenciasalaSeguridadSocial');
			fnFiltrarPorCuenta(info, '5.2.8', '#selectflujoefectivoDonativos');
			fnFiltrarPorCuenta(info, '5.3.1', '#selectflujoefectivoTransferenciasalExteriorParticipaciones');
			fnFiltrarPorCuenta(info, '5.3.2', '#selectflujoefectivoAportaciones');
			fnFiltrarPorCuenta(info, '5.3.3', '#selectflujoefectivoConvenios');


			fnFiltrarPorCuenta(info, '1.2.3', '#selectflujoefectivoBienesInmueblesInfraestructurayConstruccionesenProceso');
			fnFiltrarPorCuenta(info, '1.2.4', '#selectflujoefectivoBienesMuebles');
			fnFiltrarPorCuenta(info, '1.1.1', '#selectflujoefectivoOtrosOrigenesdeInversion');



			fnFiltrarPorCuenta(info, '4.1.1', '#selectanaliticoIngresosImpuestos');
			fnFiltrarPorCuenta(info, '4.1.2', '#selectanaliticoIngresosCuotasYAportacionesDeSeguridadSocial');
			fnFiltrarPorCuenta(info, '4.1.3', '#selectanaliticoIngresosContribucionesYMejoras');
			fnFiltrarPorCuenta(info, '4.1.4', '#selectanaliticoIngresosDrechos');
			fnFiltrarPorCuenta(info, '4.1.5', '#selectanaliticoIngresosProductos');
			fnFiltrarPorCuenta(info, '4.1.6', '#selectanaliticoIngresosAprovechamientos');
			fnFiltrarPorCuenta(info, '4.1.7', '#selectanaliticoIngresosIngresosPorVentasdeBienesYServicios');
			fnFiltrarPorCuenta(info, '4.2.1', '#selectanaliticoIngresosParticipacionesYAportaciones');
			fnFiltrarPorCuenta(info, '4.2.2', '#selectanaliticoIngresosTransferenciasAsignacionesSubsidiosYOtrasAyudas');
			fnFiltrarPorCuenta(info, '4.3.1', '#selectanaliticoIngresosIngresosDerivadosDeFinanciamientos');





 			
	    	
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});



	//selectSituacionFinancieraEfectivosyEquivalentes

	

 	//}
 	
}

function fnGrabarConfiguracionAnaliticoDelIngreso()
{
	muestraCargandoGeneral();
	var RanaliticoIngresosPImpuestos="";
	var RanaliticoIngresosPCuotasYAportacionesDeSeguridadSocial="";
	var RanaliticoIngresosPContribucionesYMejoras="";
	var RanaliticoIngresosPDrechos="";
	var RanaliticoIngresosPProductos="";
	var RanaliticoIngresosPAprovechamientos="";
	var RanaliticoIngresosPIngresosPorVentasdeBienesYServicios="";
	var RanaliticoIngresosPParticipacionesYAportaciones="";
	var RanaliticoIngresosPTransferenciasAsignacionesSubsidiosYOtrasAyudas="";
	var RanaliticoIngresosPIngresosDerivadosDeFinanciamientos="";

	tagref = $("#selectUnidadNegocio").val();

	if ($("#selectanaliticoIngresosImpuestos").val() != null) RanaliticoIngresosPImpuestos= $("#selectanaliticoIngresosImpuestos").val();
	if ($("#selectanaliticoIngresosCuotasYAportacionesDeSeguridadSocial").val() != null) RanaliticoIngresosPCuotasYAportacionesDeSeguridadSocial= $("#selectanaliticoIngresosCuotasYAportacionesDeSeguridadSocial").val();
	if ($("#selectanaliticoIngresosContribucionesYMejoras").val() != null) RanaliticoIngresosPContribucionesYMejoras= $("#selectanaliticoIngresosContribucionesYMejoras").val();
	if ($("#selectanaliticoIngresosDrechos").val() != null) RanaliticoIngresosPDrechos= $("#selectanaliticoIngresosDrechos").val();
	if ($("#selectanaliticoIngresosProductos").val() != null) RanaliticoIngresosPProductos= $("#selectanaliticoIngresosProductos").val();
	if ($("#selectanaliticoIngresosAprovechamientos").val() != null) RanaliticoIngresosPAprovechamientos= $("#selectanaliticoIngresosAprovechamientos").val();
	if ($("#selectanaliticoIngresosIngresosPorVentasdeBienesYServicios").val() != null) RanaliticoIngresosPIngresosPorVentasdeBienesYServicios= $("#selectanaliticoIngresosIngresosPorVentasdeBienesYServicios").val();
	if ($("#selectanaliticoIngresosParticipacionesYAportaciones").val() != null) RanaliticoIngresosPParticipacionesYAportaciones= $("#selectanaliticoIngresosParticipacionesYAportaciones").val();
	if ($("#selectanaliticoIngresosTransferenciasAsignacionesSubsidiosYOtrasAyudas").val() != null) RanaliticoIngresosPTransferenciasAsignacionesSubsidiosYOtrasAyudas= $("#selectanaliticoIngresosTransferenciasAsignacionesSubsidiosYOtrasAyudas").val();
	if ($("#selectanaliticoIngresosIngresosDerivadosDeFinanciamientos").val() != null) RanaliticoIngresosPIngresosDerivadosDeFinanciamientos= $("#selectanaliticoIngresosIngresosDerivadosDeFinanciamientos").val();

	if (RanaliticoIngresosPImpuestos == "" ||
	RanaliticoIngresosPCuotasYAportacionesDeSeguridadSocial == "" ||
	RanaliticoIngresosPContribucionesYMejoras == "" ||
	RanaliticoIngresosPDrechos == "" ||
	RanaliticoIngresosPProductos == "" ||
	RanaliticoIngresosPAprovechamientos == "" ||
	RanaliticoIngresosPIngresosPorVentasdeBienesYServicios == "" ||
	RanaliticoIngresosPParticipacionesYAportaciones == "" ||
	RanaliticoIngresosPTransferenciasAsignacionesSubsidiosYOtrasAyudas == "" ||
	RanaliticoIngresosPIngresosDerivadosDeFinanciamientos == ""
	) {
		ocultaCargandoGeneral();
		muestraMensaje('Faltan datos', 3, 'mensajesValidacionesRFlujo', 5000);
		return false;
		

	}

	dataObj = { 
		 option: "grabarConfiguracionReportes", 
		 reporte: "analiticoIngresos",
		 tagref : tagref,
		RanaliticoIngresosPImpuestos: RanaliticoIngresosPImpuestos,
		RanaliticoIngresosPCuotasYAportacionesDeSeguridadSocial: RanaliticoIngresosPCuotasYAportacionesDeSeguridadSocial,
		RanaliticoIngresosPContribucionesYMejoras: RanaliticoIngresosPContribucionesYMejoras,
		RanaliticoIngresosPDrechos: RanaliticoIngresosPDrechos,
		RanaliticoIngresosPProductos: RanaliticoIngresosPProductos,
		RanaliticoIngresosPAprovechamientos: RanaliticoIngresosPAprovechamientos,
		RanaliticoIngresosPIngresosPorVentasdeBienesYServicios: RanaliticoIngresosPIngresosPorVentasdeBienesYServicios,
		RanaliticoIngresosPParticipacionesYAportaciones: RanaliticoIngresosPParticipacionesYAportaciones,
		RanaliticoIngresosPTransferenciasAsignacionesSubsidiosYOtrasAyudas: RanaliticoIngresosPTransferenciasAsignacionesSubsidiosYOtrasAyudas,
		RanaliticoIngresosPIngresosDerivadosDeFinanciamientos: RanaliticoIngresosPIngresosDerivadosDeFinanciamientos,
	}

		$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/grp_configuracion_reportes_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		var titulo = '<h3><i class="fa-exclamation-circle" aria-hidden="true"></i></h3>';
	    if(data.result){
	    	ocultaCargandoGeneral();
	    	muestraMensaje(data.contenido, 1, 'OperacionMensaje', 5000);
	    	ocultaCargandoGeneral();

	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	    ocultaCargandoGeneral();
	});



}


function fnGrabarConfiguracionflujoefectivo()
{
	muestraCargandoGeneral();
	var RflujoefectivoPImpuestos="";
	var RflujoefectivoPCuotasyAportacionesdeSeguridadSocial="";
	var RflujoefectivoPContribucionesdemejoras="";
	var RflujoefectivoPDerechos="";
	var RflujoefectivoPProductosdeTipoCorriente="";
	var RflujoefectivoPAprovechamientosdeTipoCorriente="";
	var RflujoefectivoPIngresosporVentadeBienesyServicios="";
	var RflujoefectivoPIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores="";
	var RflujoefectivoPPendientesdeLiquidacionoPago="";
	var RflujoefectivoPParticipacionesyAportaciones="";
	var RflujoefectivoPTransferenciasAsignacionesySubsidiosyOtrasAyudas="";
	var RflujoefectivoPOtrosOrigenesdeOperacion="";
	var RflujoefectivoPServiciosPersonales="";
	var RflujoefectivoPMaterialesySuministros="";
	var RflujoefectivoPServiciosGenerales="";
	var RflujoefectivoPTransferenciasInternasyAsignacionesalSectorPublico="";
	var RflujoefectivoPTransferenciasalrestodelSectorPublico="";
	var RflujoefectivoPSubsidiosySubvenciones="";
	var RflujoefectivoPAyudasSociales="";
	var RflujoefectivoPPensionesyJubilaciones="";
	var RflujoefectivoPTransferenciasaFideicomisosMandatosyContratosAnalogos="";
	var RflujoefectivoPTransferenciasalaSeguridadSocial="";
	var RflujoefectivoPDonativos="";
	var RflujoefectivoPTransferenciasalExteriorParticipaciones="";
	var RflujoefectivoPAportaciones="";
	var RflujoefectivoPConvenios="";

	var RflujoefectivoPBienesInmueblesInfraestructurayConstruccionesenProceso="";
	var RflujoefectivoPBienesMuebles="";
	var RflujoefectivoPOtrosOrigenesdeInversion="";

	tagref = $("#selectUnidadNegocio").val();

	if ($("#selectflujoefectivoImpuestos").val() != null) RflujoefectivoPImpuestos= $("#selectflujoefectivoImpuestos").val();
	if ($("#selectflujoefectivoCuotasyAportacionesdeSeguridadSocial").val() != null) RflujoefectivoPCuotasyAportacionesdeSeguridadSocial= $("#selectflujoefectivoCuotasyAportacionesdeSeguridadSocial").val();
	if ($("#selectflujoefectivoContribucionesdemejoras").val() != null) RflujoefectivoPContribucionesdemejoras= $("#selectflujoefectivoContribucionesdemejoras").val();
	if ($("#selectflujoefectivoDerechos").val() != null) RflujoefectivoPDerechos= $("#selectflujoefectivoDerechos").val();
	if ($("#selectflujoefectivoProductosdeTipoCorriente").val() != null) RflujoefectivoPProductosdeTipoCorriente= $("#selectflujoefectivoProductosdeTipoCorriente").val();
	if ($("#selectflujoefectivoAprovechamientosdeTipoCorriente").val() != null) RflujoefectivoPAprovechamientosdeTipoCorriente= $("#selectflujoefectivoAprovechamientosdeTipoCorriente").val();
	if ($("#selectflujoefectivoIngresosporVentadeBienesyServicios").val() != null) RflujoefectivoPIngresosporVentadeBienesyServicios= $("#selectflujoefectivoIngresosporVentadeBienesyServicios").val();
	if ($("#selectflujoefectivoIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores").val() != null) RflujoefectivoPIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores= $("#selectflujoefectivoIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores").val();
	if ($("#selectflujoefectivoPendientesdeLiquidacionoPago").val() != null) RflujoefectivoPPendientesdeLiquidacionoPago= $("#selectflujoefectivoPendientesdeLiquidacionoPago").val();
	if ($("#selectflujoefectivoParticipacionesyAportaciones").val() != null) RflujoefectivoPParticipacionesyAportaciones= $("#selectflujoefectivoParticipacionesyAportaciones").val();
	if ($("#selectflujoefectivoTransferenciasAsignacionesySubsidiosyOtrasAyudas").val() != null) RflujoefectivoPTransferenciasAsignacionesySubsidiosyOtrasAyudas= $("#selectflujoefectivoTransferenciasAsignacionesySubsidiosyOtrasAyudas").val();
	if ($("#selectflujoefectivoOtrosOrigenesdeOperacion").val() != null) RflujoefectivoPOtrosOrigenesdeOperacion= $("#selectflujoefectivoOtrosOrigenesdeOperacion").val();
	if ($("#selectflujoefectivoServiciosPersonales").val() != null) RflujoefectivoPServiciosPersonales= $("#selectflujoefectivoServiciosPersonales").val();
	if ($("#selectflujoefectivoMaterialesySuministros").val() != null) RflujoefectivoPMaterialesySuministros= $("#selectflujoefectivoMaterialesySuministros").val();
	if ($("#selectflujoefectivoServiciosGenerales").val() != null) RflujoefectivoPServiciosGenerales= $("#selectflujoefectivoServiciosGenerales").val();
	if ($("#selectflujoefectivoTransferenciasInternasyAsignacionesalSectorPublico").val() != null) RflujoefectivoPTransferenciasInternasyAsignacionesalSectorPublico = $("#selectflujoefectivoTransferenciasInternasyAsignacionesalSectorPublico").val();
	if ($("#selectflujoefectivoTransferenciasalrestodelSectorPublico").val() != null) RflujoefectivoPTransferenciasalrestodelSectorPublico= $("#selectflujoefectivoTransferenciasalrestodelSectorPublico").val();
	if ($("#selectflujoefectivoSubsidiosySubvenciones").val() != null) RflujoefectivoPSubsidiosySubvenciones= $("#selectflujoefectivoSubsidiosySubvenciones").val();
	if ($("#selectflujoefectivoAyudasSociales").val() != null) RflujoefectivoPAyudasSociales= $("#selectflujoefectivoAyudasSociales").val();
	if ($("#selectflujoefectivoPensionesyJubilaciones").val() != null) RflujoefectivoPPensionesyJubilaciones= $("#selectflujoefectivoPensionesyJubilaciones").val();
	if ($("#selectflujoefectivoTransferenciasaFideicomisosMandatosyContratosAnalogos").val() != null) RflujoefectivoPTransferenciasaFideicomisosMandatosyContratosAnalogos= $("#selectflujoefectivoTransferenciasaFideicomisosMandatosyContratosAnalogos").val();
	if ($("#selectflujoefectivoTransferenciasalaSeguridadSocial").val() != null) RflujoefectivoPTransferenciasalaSeguridadSocial= $("#selectflujoefectivoTransferenciasalaSeguridadSocial").val();
	if ($("#selectflujoefectivoDonativos").val() != null) RflujoefectivoPDonativos= $("#selectflujoefectivoDonativos").val();
	if ($("#selectflujoefectivoTransferenciasalExteriorParticipaciones").val() != null) RflujoefectivoPTransferenciasalExteriorParticipaciones= $("#selectflujoefectivoTransferenciasalExteriorParticipaciones").val();
	if ($("#selectflujoefectivoAportaciones").val() != null) RflujoefectivoPAportaciones= $("#selectflujoefectivoAportaciones").val();
	if ($("#selectflujoefectivoConvenios").val() != null) RflujoefectivoPConvenios= $("#selectflujoefectivoConvenios").val();

	if ($("#selectflujoefectivoBienesInmueblesInfraestructurayConstruccionesenProceso").val() != null) RflujoefectivoPBienesInmueblesInfraestructurayConstruccionesenProceso= $("#selectflujoefectivoBienesInmueblesInfraestructurayConstruccionesenProceso").val();
	if ($("#selectflujoefectivoBienesMuebles").val() != null) RflujoefectivoPBienesMuebles= $("#selectflujoefectivoBienesMuebles").val();
	if ($("#selectflujoefectivoOtrosOrigenesdeInversion").val() != null) RflujoefectivoPOtrosOrigenesdeInversion= $("#selectflujoefectivoOtrosOrigenesdeInversion").val();



	if (RflujoefectivoPImpuestos == "" ||
RflujoefectivoPCuotasyAportacionesdeSeguridadSocial == "" ||
RflujoefectivoPContribucionesdemejoras == "" ||
RflujoefectivoPDerechos == "" ||
RflujoefectivoPProductosdeTipoCorriente == "" ||
RflujoefectivoPAprovechamientosdeTipoCorriente == "" ||
RflujoefectivoPIngresosporVentadeBienesyServicios == "" ||
RflujoefectivoPIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores  == "" ||
RflujoefectivoPPendientesdeLiquidacionoPago == "" ||
RflujoefectivoPParticipacionesyAportaciones == "" ||
RflujoefectivoPTransferenciasAsignacionesySubsidiosyOtrasAyudas == "" ||
RflujoefectivoPOtrosOrigenesdeOperacion == "" ||
RflujoefectivoPServiciosPersonales == "" ||
RflujoefectivoPMaterialesySuministros == "" ||
RflujoefectivoPServiciosGenerales == "" ||
RflujoefectivoPTransferenciasInternasyAsignacionesalSectorPublico == "" ||
RflujoefectivoPTransferenciasalrestodelSectorPublico == "" ||
RflujoefectivoPSubsidiosySubvenciones == "" ||
RflujoefectivoPAyudasSociales == "" ||
RflujoefectivoPPensionesyJubilaciones == "" ||
RflujoefectivoPTransferenciasaFideicomisosMandatosyContratosAnalogos == "" ||
RflujoefectivoPTransferenciasalaSeguridadSocial == "" ||
RflujoefectivoPDonativos == "" ||
RflujoefectivoPTransferenciasalExteriorParticipaciones == "" ||
RflujoefectivoPAportaciones == "" ||
RflujoefectivoPConvenios == "" ||
RflujoefectivoPBienesInmueblesInfraestructurayConstruccionesenProceso == "" ||
RflujoefectivoPBienesMuebles == "" ||
RflujoefectivoPOtrosOrigenesdeInversion == "" 
) {
		ocultaCargandoGeneral();
		muestraMensaje('Faltan datos', 3, 'mensajesValidacionesRFlujo', 5000);
		return false;
		

	}



		dataObj = { 
 option: "grabarConfiguracionReportes", 
 reporte: "flujoefectivo",
 tagref: tagref, 
RflujoefectivoPImpuestos: RflujoefectivoPImpuestos,
RflujoefectivoPCuotasyAportacionesdeSeguridadSocial: RflujoefectivoPCuotasyAportacionesdeSeguridadSocial,
RflujoefectivoPContribucionesdemejoras: RflujoefectivoPContribucionesdemejoras,
RflujoefectivoPDerechos: RflujoefectivoPDerechos,
RflujoefectivoPProductosdeTipoCorriente: RflujoefectivoPProductosdeTipoCorriente,
RflujoefectivoPAprovechamientosdeTipoCorriente: RflujoefectivoPAprovechamientosdeTipoCorriente,
RflujoefectivoPIngresosporVentadeBienesyServicios: RflujoefectivoPIngresosporVentadeBienesyServicios,
RflujoefectivoPIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores: RflujoefectivoPIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores,
RflujoefectivoPPendientesdeLiquidacionoPago: RflujoefectivoPPendientesdeLiquidacionoPago,
RflujoefectivoPParticipacionesyAportaciones: RflujoefectivoPParticipacionesyAportaciones,
RflujoefectivoPTransferenciasAsignacionesySubsidiosyOtrasAyudas: RflujoefectivoPTransferenciasAsignacionesySubsidiosyOtrasAyudas,
RflujoefectivoPOtrosOrigenesdeOperacion: RflujoefectivoPOtrosOrigenesdeOperacion,
RflujoefectivoPServiciosPersonales: RflujoefectivoPServiciosPersonales,
RflujoefectivoPMaterialesySuministros: RflujoefectivoPMaterialesySuministros,
RflujoefectivoPServiciosGenerales: RflujoefectivoPServiciosGenerales,
RflujoefectivoPTransferenciasInternasyAsignacionesalSectorPublico: RflujoefectivoPTransferenciasInternasyAsignacionesalSectorPublico,
RflujoefectivoPTransferenciasalrestodelSectorPublico: RflujoefectivoPTransferenciasalrestodelSectorPublico,
RflujoefectivoPSubsidiosySubvenciones: RflujoefectivoPSubsidiosySubvenciones,
RflujoefectivoPAyudasSociales: RflujoefectivoPAyudasSociales,
RflujoefectivoPPensionesyJubilaciones: RflujoefectivoPPensionesyJubilaciones,
RflujoefectivoPTransferenciasaFideicomisosMandatosyContratosAnalogos:  RflujoefectivoPTransferenciasaFideicomisosMandatosyContratosAnalogos,
RflujoefectivoPTransferenciasalaSeguridadSocial: RflujoefectivoPTransferenciasalaSeguridadSocial,
RflujoefectivoPDonativos: RflujoefectivoPDonativos,
RflujoefectivoPTransferenciasalExteriorParticipaciones: RflujoefectivoPTransferenciasalExteriorParticipaciones,
RflujoefectivoPAportaciones: RflujoefectivoPAportaciones,
RflujoefectivoPConvenios: RflujoefectivoPConvenios,
RflujoefectivoPBienesInmueblesInfraestructurayConstruccionesenProceso: RflujoefectivoPBienesInmueblesInfraestructurayConstruccionesenProceso,
RflujoefectivoPBienesMuebles: RflujoefectivoPBienesMuebles,
RflujoefectivoPOtrosOrigenesdeInversion: RflujoefectivoPOtrosOrigenesdeInversion
}


//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/grp_configuracion_reportes_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		var titulo = '<h3><i class="fa-exclamation-circle" aria-hidden="true"></i></h3>';
	    if(data.result){
	    	ocultaCargandoGeneral();

	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	    ocultaCargandoGeneral();
	});


}

function fnGrabarConfiguracionEstadoDeActividades()
{
	muestraCargandoGeneral();
	var REstadoDeActividadesPImpuestos="";
	var REstadoDeActividadesPCuotasyAportacionesdeSeguridadSocial="";
	var REstadoDeActividadesPContribucionesdeMejoras="";
	var REstadoDeActividadesPDerechos="";
	var REstadoDeActividadesPProductosdeTipoCorriente="";
	var REstadoDeActividadesPAprovechamientosdeTipoCorriente="";
	var REstadoDeActividadesPIngresosporVentadeBienesyServicios="";
	var REstadoDeActividadesPIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores="";
	var REstadoDeActividadesPPendientesdeLiquidacionoPago="";
	var REstadoDeActividadesPParticipacionesyAportaciones="";
	var REstadoDeActividadesPTransferenciaAsignacionesSubsidiosyOtrasAyudas="";
	var REstadoDeActividadesPIngresosFinancieros="";
	var REstadoDeActividadesPIncrementoporVariaciondeInventarios="";
	var REstadoDeActividadesPDisminuciondelExcesodeEstimacionesporPerdidaoDeteriorouObsolescencia="";
	var REstadoDeActividadesPDisminuciondelExcesodeProvisiones="";
	var REstadoDeActividadesPOtrosIngresosyBeneficiosVarios="";
	var REstadoDeActividadesPServiciosPersonales="";
	var REstadoDeActividadesPMaterialesySuministros="";
	var REstadoDeActividadesPServiciosGenerales="";
	var REstadoDeActividadesPTransferenciasInternasyAsignacionesalSectorPublico="";
	var REstadoDeActividadesPTransferenciasalRestodelSectorPublico="";
	var REstadoDeActividadesPSubsidiosySubvenciones="";
	var REstadoDeActividadesPAyudasSociales="";
	var REstadoDeActividadesPPensionesyJubilaciones="";
	var REstadoDeActividadesPTransferenciasaFideicomisosMandatosyContratosAnalogos="";
	var REstadoDeActividadesPTransferenciasalaSeguridadSocial="";
	var REstadoDeActividadesPDonativos="";
	var REstadoDeActividadesPTransferenciasalExterior="";
	var REstadoDeActividadesPParticipaciones="";
	var REstadoDeActividadesPAportaciones="";
	var REstadoDeActividadesPConvenios="";
	var REstadoDeActividadesPInteresesdelaDeudaPublica="";
	var REstadoDeActividadesPComisionesdelaDeudaPublica="";
	var REstadoDeActividadesPGastosdelaDeudaPublica="";
	var REstadoDeActividadesPCostoporCoberturas="";
	var REstadoDeActividadesPApoyosFinancieros="";
	var REstadoDeActividadesPEstimacionesDepreciacionesDeteriorosObsolescenciayAmortizaciones="";
	var REstadoDeActividadesPProvisiones="";
	var REstadoDeActividadesPDisminuciondeInventarios="";
	var REstadoDeActividadesPAumentoporInsuficienciadeEstimacionesporPerdidaoDeterioroyObsolescencia="";
	var REstadoDeActividadesPAumentoporInsuficienciadeProvisiones="";
	var REstadoDeActividadesPOtrosGastos="";
	var REstadoDeActividadesPInversionPublicanoCapitalizable="";




	tagref = $("#selectUnidadNegocio").val();


	if ($("#selectEstadoDeActividadesImpuestos").val() != null) REstadoDeActividadesPImpuestos= $("#selectEstadoDeActividadesImpuestos").val();
if ($("#selectEstadoDeActividadesCuotasyAportacionesdeSeguridadSocial").val() != null) REstadoDeActividadesPCuotasyAportacionesdeSeguridadSocial= $("#selectEstadoDeActividadesCuotasyAportacionesdeSeguridadSocial").val();
if ($("#selectEstadoDeActividadesContribucionesdeMejoras").val() != null) REstadoDeActividadesPContribucionesdeMejoras= $("#selectEstadoDeActividadesContribucionesdeMejoras").val();
if ($("#selectEstadoDeActividadesDerechos").val() != null) REstadoDeActividadesPDerechos= $("#selectEstadoDeActividadesDerechos").val();
if ($("#selectEstadoDeActividadesProductosdeTipoCorriente").val() != null) REstadoDeActividadesPProductosdeTipoCorriente= $("#selectEstadoDeActividadesProductosdeTipoCorriente").val();
if ($("#selectEstadoDeActividadesAprovechamientosdeTipoCorriente").val() != null) REstadoDeActividadesPAprovechamientosdeTipoCorriente= $("#selectEstadoDeActividadesAprovechamientosdeTipoCorriente").val();
if ($("#selectEstadoDeActividadesIngresosporVentadeBienesyServicios").val() != null) REstadoDeActividadesPIngresosporVentadeBienesyServicios= $("#selectEstadoDeActividadesIngresosporVentadeBienesyServicios").val();
if ($("#selectEstadoDeActividadesIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores").val() != null) REstadoDeActividadesPIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores= $("#selectEstadoDeActividadesIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores").val();
if ($("#selectEstadoDeActividadesPendientesdeLiquidacionoPago").val() != null) REstadoDeActividadesPPendientesdeLiquidacionoPago= $("#selectEstadoDeActividadesPendientesdeLiquidacionoPago").val();
if ($("#selectEstadoDeActividadesParticipacionesyAportaciones").val() != null) REstadoDeActividadesPParticipacionesyAportaciones= $("#selectEstadoDeActividadesParticipacionesyAportaciones").val();
if ($("#selectEstadoDeActividadesTransferenciaAsignacionesSubsidiosyOtrasAyudas").val() != null) REstadoDeActividadesPTransferenciaAsignacionesSubsidiosyOtrasAyudas= $("#selectEstadoDeActividadesTransferenciaAsignacionesSubsidiosyOtrasAyudas").val();
if ($("#selectEstadoDeActividadesIngresosFinancieros").val() != null) REstadoDeActividadesPIngresosFinancieros= $("#selectEstadoDeActividadesIngresosFinancieros").val();
if ($("#selectEstadoDeActividadesIncrementoporVariaciondeInventarios").val() != null) REstadoDeActividadesPIncrementoporVariaciondeInventarios= $("#selectEstadoDeActividadesIncrementoporVariaciondeInventarios").val();
if ($("#selectEstadoDeActividadesDisminuciondelExcesodeEstimacionesporPerdidaoDeteriorouObsolescencia").val() != null) REstadoDeActividadesPDisminuciondelExcesodeEstimacionesporPerdidaoDeteriorouObsolescencia= $("#selectEstadoDeActividadesDisminuciondelExcesodeEstimacionesporPerdidaoDeteriorouObsolescencia").val();
if ($("#selectEstadoDeActividadesDisminuciondelExcesodeProvisiones").val() != null) REstadoDeActividadesPDisminuciondelExcesodeProvisiones= $("#selectEstadoDeActividadesDisminuciondelExcesodeProvisiones").val();
if ($("#selectEstadoDeActividadesOtrosIngresosyBeneficiosVarios").val() != null) REstadoDeActividadesPOtrosIngresosyBeneficiosVarios= $("#selectEstadoDeActividadesOtrosIngresosyBeneficiosVarios").val();
if ($("#selectEstadoDeActividadesServiciosPersonales").val() != null) REstadoDeActividadesPServiciosPersonales= $("#selectEstadoDeActividadesServiciosPersonales").val();
if ($("#selectEstadoDeActividadesMaterialesySuministros").val() != null) REstadoDeActividadesPMaterialesySuministros= $("#selectEstadoDeActividadesMaterialesySuministros").val();
if ($("#selectEstadoDeActividadesServiciosGenerales").val() != null) REstadoDeActividadesPServiciosGenerales= $("#selectEstadoDeActividadesServiciosGenerales").val();
if ($("#selectEstadoDeActividadesTransferenciasInternasyAsignacionesalSectorPublico").val() != null) REstadoDeActividadesPTransferenciasInternasyAsignacionesalSectorPublico= $("#selectEstadoDeActividadesTransferenciasInternasyAsignacionesalSectorPublico").val();
if ($("#selectEstadoDeActividadesTransferenciasalRestodelSectorPublico").val() != null) REstadoDeActividadesPTransferenciasalRestodelSectorPublico= $("#selectEstadoDeActividadesTransferenciasalRestodelSectorPublico").val();
if ($("#selectEstadoDeActividadesSubsidiosySubvenciones").val() != null) REstadoDeActividadesPSubsidiosySubvenciones= $("#selectEstadoDeActividadesSubsidiosySubvenciones").val();
if ($("#selectEstadoDeActividadesAyudasSociales").val() != null) REstadoDeActividadesPAyudasSociales= $("#selectEstadoDeActividadesAyudasSociales").val();
if ($("#selectEstadoDeActividadesPensionesyJubilaciones").val() != null) REstadoDeActividadesPPensionesyJubilaciones= $("#selectEstadoDeActividadesPensionesyJubilaciones").val();
if ($("#selectEstadoDeActividadesTransferenciasaFideicomisosMandatosyContratosAnalogos").val() != null) REstadoDeActividadesPTransferenciasaFideicomisosMandatosyContratosAnalogos= $("#selectEstadoDeActividadesTransferenciasaFideicomisosMandatosyContratosAnalogos").val();
if ($("#selectEstadoDeActividadesTransferenciasalaSeguridadSocial").val() != null) REstadoDeActividadesPTransferenciasalaSeguridadSocial= $("#selectEstadoDeActividadesTransferenciasalaSeguridadSocial").val();
if ($("#selectEstadoDeActividadesDonativos").val() != null) REstadoDeActividadesPDonativos= $("#selectEstadoDeActividadesDonativos").val();
if ($("#selectEstadoDeActividadesTransferenciasalExterior").val() != null) REstadoDeActividadesPTransferenciasalExterior= $("#selectEstadoDeActividadesTransferenciasalExterior").val();
if ($("#selectEstadoDeActividadesParticipaciones").val() != null) REstadoDeActividadesPParticipaciones= $("#selectEstadoDeActividadesParticipaciones").val();
if ($("#selectEstadoDeActividadesAportaciones").val() != null) REstadoDeActividadesPAportaciones= $("#selectEstadoDeActividadesAportaciones").val();
if ($("#selectEstadoDeActividadesConvenios").val() != null) REstadoDeActividadesPConvenios= $("#selectEstadoDeActividadesConvenios").val();
if ($("#selectEstadoDeActividadesInteresesdelaDeudaPublica").val() != null) REstadoDeActividadesPInteresesdelaDeudaPublica= $("#selectEstadoDeActividadesInteresesdelaDeudaPublica").val();
if ($("#selectEstadoDeActividadesComisionesdelaDeudaPublica").val() != null) REstadoDeActividadesPComisionesdelaDeudaPublica= $("#selectEstadoDeActividadesComisionesdelaDeudaPublica").val();
if ($("#selectEstadoDeActividadesGastosdelaDeudaPublica").val() != null) REstadoDeActividadesPGastosdelaDeudaPublica= $("#selectEstadoDeActividadesGastosdelaDeudaPublica").val();
if ($("#selectEstadoDeActividadesCostoporCoberturas").val() != null) REstadoDeActividadesPCostoporCoberturas= $("#selectEstadoDeActividadesCostoporCoberturas").val();
if ($("#selectEstadoDeActividadesApoyosFinancieros").val() != null) REstadoDeActividadesPApoyosFinancieros= $("#selectEstadoDeActividadesApoyosFinancieros").val();
if ($("#selectEstadoDeActividadesEstimacionesDepreciacionesDeteriorosObsolescenciayAmortizaciones").val() != null) REstadoDeActividadesPEstimacionesDepreciacionesDeteriorosObsolescenciayAmortizaciones= $("#selectEstadoDeActividadesEstimacionesDepreciacionesDeteriorosObsolescenciayAmortizaciones").val();
if ($("#selectEstadoDeActividadesProvisiones").val() != null) REstadoDeActividadesPProvisiones= $("#selectEstadoDeActividadesProvisiones").val();
if ($("#selectEstadoDeActividadesDisminuciondeInventarios").val() != null) REstadoDeActividadesPDisminuciondeInventarios= $("#selectEstadoDeActividadesDisminuciondeInventarios").val();
if ($("#selectEstadoDeActividadesAumentoporInsuficienciadeEstimacionesporPerdidaoDeterioroyObsolescencia").val() != null) REstadoDeActividadesPAumentoporInsuficienciadeEstimacionesporPerdidaoDeterioroyObsolescencia= $("#selectEstadoDeActividadesAumentoporInsuficienciadeEstimacionesporPerdidaoDeterioroyObsolescencia").val();
if ($("#selectEstadoDeActividadesAumentoporInsuficienciadeProvisiones").val() != null) REstadoDeActividadesPAumentoporInsuficienciadeProvisiones= $("#selectEstadoDeActividadesAumentoporInsuficienciadeProvisiones").val();
if ($("#selectEstadoDeActividadesOtrosGastos").val() != null) REstadoDeActividadesPOtrosGastos= $("#selectEstadoDeActividadesOtrosGastos").val();
if ($("#selectEstadoDeActividadesInversionPublicanoCapitalizable").val() != null) REstadoDeActividadesPInversionPublicanoCapitalizable= $("#selectEstadoDeActividadesInversionPublicanoCapitalizable").val();





if (REstadoDeActividadesPImpuestos == "" ||
REstadoDeActividadesPCuotasyAportacionesdeSeguridadSocial == "" ||
REstadoDeActividadesPContribucionesdeMejoras == "" ||
REstadoDeActividadesPDerechos == "" ||
REstadoDeActividadesPProductosdeTipoCorriente == "" ||
REstadoDeActividadesPAprovechamientosdeTipoCorriente == "" ||
REstadoDeActividadesPIngresosporVentadeBienesyServicios == "" ||
REstadoDeActividadesPIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores == "" ||
REstadoDeActividadesPPendientesdeLiquidacionoPago == "" ||
REstadoDeActividadesPParticipacionesyAportaciones == "" ||
REstadoDeActividadesPTransferenciaAsignacionesSubsidiosyOtrasAyudas == "" ||
REstadoDeActividadesPIngresosFinancieros == "" ||
REstadoDeActividadesPIncrementoporVariaciondeInventarios == "" ||
REstadoDeActividadesPDisminuciondelExcesodeEstimacionesporPerdidaoDeteriorouObsolescencia == "" ||
REstadoDeActividadesPDisminuciondelExcesodeProvisiones == "" ||
REstadoDeActividadesPOtrosIngresosyBeneficiosVarios == "" ||
REstadoDeActividadesPServiciosPersonales == "" ||
REstadoDeActividadesPMaterialesySuministros == "" ||
REstadoDeActividadesPServiciosGenerales == "" ||
REstadoDeActividadesPTransferenciasInternasyAsignacionesalSectorPublico == "" ||
REstadoDeActividadesPTransferenciasalRestodelSectorPublico == "" ||
REstadoDeActividadesPSubsidiosySubvenciones == "" ||
REstadoDeActividadesPAyudasSociales == "" ||
REstadoDeActividadesPPensionesyJubilaciones == "" ||
REstadoDeActividadesPTransferenciasaFideicomisosMandatosyContratosAnalogos == "" ||
REstadoDeActividadesPTransferenciasalaSeguridadSocial == "" ||
REstadoDeActividadesPDonativos == "" ||
REstadoDeActividadesPTransferenciasalExterior == "" ||
REstadoDeActividadesPParticipaciones == "" ||
REstadoDeActividadesPAportaciones == "" ||
REstadoDeActividadesPConvenios == "" ||
REstadoDeActividadesPInteresesdelaDeudaPublica == "" ||
REstadoDeActividadesPComisionesdelaDeudaPublica == "" ||
REstadoDeActividadesPGastosdelaDeudaPublica == "" ||
REstadoDeActividadesPCostoporCoberturas == "" ||
REstadoDeActividadesPApoyosFinancieros == "" ||
REstadoDeActividadesPEstimacionesDepreciacionesDeteriorosObsolescenciayAmortizaciones == "" ||
REstadoDeActividadesPProvisiones == "" ||
REstadoDeActividadesPDisminuciondeInventarios == "" ||
REstadoDeActividadesPAumentoporInsuficienciadeEstimacionesporPerdidaoDeterioroyObsolescencia == "" ||
REstadoDeActividadesPAumentoporInsuficienciadeProvisiones == "" ||
REstadoDeActividadesPOtrosGastos == "" ||
REstadoDeActividadesPInversionPublicanoCapitalizable == ""
) {
	    ocultaCargandoGeneral();
		muestraMensaje('Faltan datos', 3, 'mensajesValidacionesCONACR2', 5000);
		return false;
		

	}



	dataObj = { 
 option: "grabarConfiguracionReportes", 
 reporte: "EstadoDeActividades",
 tagref: tagref,
REstadoDeActividadesPImpuestos: REstadoDeActividadesPImpuestos,
REstadoDeActividadesPCuotasyAportacionesdeSeguridadSocial: REstadoDeActividadesPCuotasyAportacionesdeSeguridadSocial,
REstadoDeActividadesPContribucionesdeMejoras: REstadoDeActividadesPContribucionesdeMejoras,
REstadoDeActividadesPDerechos: REstadoDeActividadesPDerechos,
REstadoDeActividadesPProductosdeTipoCorriente: REstadoDeActividadesPProductosdeTipoCorriente,
REstadoDeActividadesPAprovechamientosdeTipoCorriente: REstadoDeActividadesPAprovechamientosdeTipoCorriente,
REstadoDeActividadesPIngresosporVentadeBienesyServicios: REstadoDeActividadesPIngresosporVentadeBienesyServicios,
REstadoDeActividadesPIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores: REstadoDeActividadesPIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores,
REstadoDeActividadesPPendientesdeLiquidacionoPago: REstadoDeActividadesPPendientesdeLiquidacionoPago,
REstadoDeActividadesPParticipacionesyAportaciones: REstadoDeActividadesPParticipacionesyAportaciones,
REstadoDeActividadesPTransferenciaAsignacionesSubsidiosyOtrasAyudas: REstadoDeActividadesPTransferenciaAsignacionesSubsidiosyOtrasAyudas,
REstadoDeActividadesPIngresosFinancieros: REstadoDeActividadesPIngresosFinancieros,
REstadoDeActividadesPIncrementoporVariaciondeInventarios: REstadoDeActividadesPIncrementoporVariaciondeInventarios,
REstadoDeActividadesPDisminuciondelExcesodeEstimacionesporPerdidaoDeteriorouObsolescencia: REstadoDeActividadesPDisminuciondelExcesodeEstimacionesporPerdidaoDeteriorouObsolescencia,
REstadoDeActividadesPDisminuciondelExcesodeProvisiones: REstadoDeActividadesPDisminuciondelExcesodeProvisiones,
REstadoDeActividadesPOtrosIngresosyBeneficiosVarios: REstadoDeActividadesPOtrosIngresosyBeneficiosVarios,
REstadoDeActividadesPServiciosPersonales: REstadoDeActividadesPServiciosPersonales,
REstadoDeActividadesPMaterialesySuministros: REstadoDeActividadesPMaterialesySuministros,
REstadoDeActividadesPServiciosGenerales: REstadoDeActividadesPServiciosGenerales,
REstadoDeActividadesPTransferenciasInternasyAsignacionesalSectorPublico: REstadoDeActividadesPTransferenciasInternasyAsignacionesalSectorPublico,
REstadoDeActividadesPTransferenciasalRestodelSectorPublico: REstadoDeActividadesPTransferenciasalRestodelSectorPublico,
REstadoDeActividadesPSubsidiosySubvenciones: REstadoDeActividadesPSubsidiosySubvenciones,
REstadoDeActividadesPAyudasSociales: REstadoDeActividadesPAyudasSociales,
REstadoDeActividadesPPensionesyJubilaciones: REstadoDeActividadesPPensionesyJubilaciones,
REstadoDeActividadesPTransferenciasaFideicomisosMandatosyContratosAnalogos: REstadoDeActividadesPTransferenciasaFideicomisosMandatosyContratosAnalogos,
REstadoDeActividadesPTransferenciasalaSeguridadSocial: REstadoDeActividadesPTransferenciasalaSeguridadSocial,
REstadoDeActividadesPDonativos: REstadoDeActividadesPDonativos,
REstadoDeActividadesPTransferenciasalExterior: REstadoDeActividadesPTransferenciasalExterior,
REstadoDeActividadesPParticipaciones: REstadoDeActividadesPParticipaciones,
REstadoDeActividadesPAportaciones: REstadoDeActividadesPAportaciones,
REstadoDeActividadesPConvenios: REstadoDeActividadesPConvenios,
REstadoDeActividadesPInteresesdelaDeudaPublica: REstadoDeActividadesPInteresesdelaDeudaPublica,
REstadoDeActividadesPComisionesdelaDeudaPublica: REstadoDeActividadesPComisionesdelaDeudaPublica,
REstadoDeActividadesPGastosdelaDeudaPublica: REstadoDeActividadesPGastosdelaDeudaPublica,
REstadoDeActividadesPCostoporCoberturas: REstadoDeActividadesPCostoporCoberturas,
REstadoDeActividadesPApoyosFinancieros: REstadoDeActividadesPApoyosFinancieros,
REstadoDeActividadesPEstimacionesDepreciacionesDeteriorosObsolescenciayAmortizaciones: REstadoDeActividadesPEstimacionesDepreciacionesDeteriorosObsolescenciayAmortizaciones,
REstadoDeActividadesPProvisiones: REstadoDeActividadesPProvisiones,
REstadoDeActividadesPDisminuciondeInventarios: REstadoDeActividadesPDisminuciondeInventarios,
REstadoDeActividadesPAumentoporInsuficienciadeEstimacionesporPerdidaoDeterioroyObsolescencia: REstadoDeActividadesPAumentoporInsuficienciadeEstimacionesporPerdidaoDeterioroyObsolescencia,
REstadoDeActividadesPAumentoporInsuficienciadeProvisiones: REstadoDeActividadesPAumentoporInsuficienciadeProvisiones,
REstadoDeActividadesPOtrosGastos: REstadoDeActividadesPOtrosGastos,
REstadoDeActividadesPInversionPublicanoCapitalizable: REstadoDeActividadesPInversionPublicanoCapitalizable
}

 //Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/grp_configuracion_reportes_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		var titulo = '<h3><i class="fa-exclamation-circle" aria-hidden="true"></i></h3>';
	    if(data.result){
	    	ocultaCargandoGeneral();
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	    ocultaCargandoGeneral();
	});


}

function fnGrabarConfiguracionSituacionFinanciera ()
{
	muestraCargandoGeneral();
	var RSituacionFinancieraPEfectivoyEquivalentes = "";
	var RSituacionFinancieraPDerechoARecibirEfectivoyEquivalentes = "";
	var RSituacionFinancieraPDerechosaRecibirBienesoServicios = "";
	var RSituacionFinancieraPInventarios= "";
	var RSituacionFinancieraPAlmacenes = "";
	var RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosCirculantes = "";
	var RSituacionFinancieraPOtrosActivosCirculantes = "";
	var RSituacionFinancieraPInversionesFinancierasaLargoPlazo = "";
	var RSituacionFinancieraPDerechosaRecibirEfectivooEquivalentesaLargoPlazo = "";
	var RSituacionFinancieraPBienesInmueblesInfraestructurayConstruccionesenProceso = "";
	var RSituacionFinancieraPBienesMuebles = "";
	var RSituacionFinancieraPActivosIntangibles = "";
	var RSituacionFinancieraPDepreciacionDeterioroyAmortizacionAcumuladadeBienes = "";
	var RSituacionFinancieraPActivosDiferidos = "";
	var RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosnoCirculantes = ""; 
	var RSituacionFinancieraPOtrosActivosnoCirculantes = "";
	var RSituacionFinancieraPCuentasporPagaraCortoPlazo = "";
	var RSituacionFinancieraPDocumentosporPagaraCortoPlazo = "";
	var RSituacionFinancieraPPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo = "";
	var RSituacionFinancieraPTitulosyValoresaCortoPlazo = "";
	var RSituacionFinancieraPPasivosDiferidosaCortoPlazo = "";
	var RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo = "";
	var RSituacionFinancieraPProvisionesaCortoPlazo = "";
	var RSituacionFinancieraPOtrosPasivosaCortoPlazo = "";
	var RSituacionFinancieraPCuentasporPagaraLargoPlazo = "";
	var RSituacionFinancieraPDocumentosporPagaraLargoPlazo = "";
	var RSituacionFinancieraPDeudaPublicaaLargoPlazo = "";
	var RSituacionFinancieraPPasivosDiferidosaLargoPlazo = "";
	var RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo = "";
	var RSituacionFinancieraPProvisionesaLargoPlazo = "";
	var RSituacionFinancieraPAportaciones = "";
	var RSituacionFinancieraPDonacionesdeCapital = "";
	var RSituacionFinancieraPActualizaciondelaHaciendaPublicaPatrimonio = "";
	var RSituacionFinancieraPResultadosdeEjerciciosAhorroDesahorro = "";
	var RSituacionFinancieraPResultadosdeEjerciciosAnteriores = "";
	var RSituacionFinancieraPRevaluos = "";
	var RSituacionFinancieraPReservas = "";

		tagref = $("#selectUnidadNegocio").val();



	




	//var capitulo = $('#txtCapitulo').val();
	if ($("#selectSituacionFinancieraEfectivosyEquivalentes").val() != null) RSituacionFinancieraPEfectivoyEquivalentes = $("#selectSituacionFinancieraEfectivosyEquivalentes").val(); 
	if ($("#selectSituacionFinancieraDerechoARecibirEfectivosyEquivalentes").val() != null) RSituacionFinancieraPDerechoARecibirEfectivoyEquivalentes = $('#selectSituacionFinancieraDerechoARecibirEfectivosyEquivalentes').val(); 
	if ($("#selectSituacionFinancieraDerechosaRecibirBienesoServicios").val() != null) RSituacionFinancieraPDerechosaRecibirBienesoServicios = $('#selectSituacionFinancieraDerechosaRecibirBienesoServicios').val(); 
	if ($("#selectSituacionFinancieraInventarios").val() != null) RSituacionFinancieraPInventarios = $("#selectSituacionFinancieraInventarios").val(); 
	if ($("#selectSituacionFinancieraAlmacenes").val() != null) RSituacionFinancieraPAlmacenes = $('#selectSituacionFinancieraAlmacenes').val(); 
	if ($("#selectSituacionFinancieraEstimacionporPerdidaoDeteriorodeActivosCirculantes").val() != null) RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosCirculantes = $('#selectSituacionFinancieraEstimacionporPerdidaoDeteriorodeActivosCirculantes').val(); 
	if ($("#selectSituacionFinancieraOtrosActivosCirculantes").val() != null) RSituacionFinancieraPOtrosActivosCirculantes = $('#selectSituacionFinancieraOtrosActivosCirculantes').val(); 


	if ($("#selectSituacionFinancieraInversionesFinancierasaLargoPlazo").val() != null) RSituacionFinancieraPInversionesFinancierasaLargoPlazo = $('#selectSituacionFinancieraInversionesFinancierasaLargoPlazo').val();
 	if ($("#selectSituacionFinancieraDerechosaRecibirEfectivooEquivalentesaLargoPlazo").val() != null) RSituacionFinancieraPDerechosaRecibirEfectivooEquivalentesaLargoPlazo = $('#selectSituacionFinancieraDerechosaRecibirEfectivooEquivalentesaLargoPlazo').val();
 	if ($("#selectSituacionFinancieraBienesInmueblesInfraestructurayConstruccionesenProceso").val() != null) RSituacionFinancieraPBienesInmueblesInfraestructurayConstruccionesenProceso = $('#selectSituacionFinancieraBienesInmueblesInfraestructurayConstruccionesenProceso').val();
 	if ($("#selectSituacionFinancieraBienesMuebles").val() != null) RSituacionFinancieraPBienesMuebles = $('#selectSituacionFinancieraBienesMuebles').val();
 	if ($("#selectSituacionFinancieraActivosIntangibles").val() != null) RSituacionFinancieraPActivosIntangibles = $('#selectSituacionFinancieraActivosIntangibles').val();
 	if ($("#selectSituacionFinancieraDepreciacionDeterioroyAmortizacionAcumuladadeBienes").val() != null) RSituacionFinancieraPDepreciacionDeterioroyAmortizacionAcumuladadeBienes = $('#selectSituacionFinancieraDepreciacionDeterioroyAmortizacionAcumuladadeBienes').val();
 	if ($("#selectSituacionFinancieraActivosDiferidos").val() != null) RSituacionFinancieraPActivosDiferidos = $('#selectSituacionFinancieraActivosDiferidos').val();
 	if ($("#selectSituacionFinancieraEstimacionporPerdidaoDeteriorodeActivosnoCirculantes").val() != null) RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosnoCirculantes = $('#selectSituacionFinancieraEstimacionporPerdidaoDeteriorodeActivosnoCirculantes').val();
 	if ($("#selectSituacionFinancieraOtrosActivosnoCirculantes").val() != null) RSituacionFinancieraPOtrosActivosnoCirculantes = $('#selectSituacionFinancieraOtrosActivosnoCirculantes').val();

 	




 	if ($("#selectSituacionFinancieraCuentasporPagaraCortoPlazo").val() != null) RSituacionFinancieraPCuentasporPagaraCortoPlazo = $('#selectSituacionFinancieraCuentasporPagaraCortoPlazo').val();
 	if ($("#selectSituacionFinancieraDocumentosporPagaraCortoPlazo").val() != null) RSituacionFinancieraPDocumentosporPagaraCortoPlazo = $('#selectSituacionFinancieraDocumentosporPagaraCortoPlazo').val();
 	if ($("#selectSituacionFinancieraPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo").val() != null) RSituacionFinancieraPPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo = $('#selectSituacionFinancieraPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo').val();
 	if ($("#selectSituacionFinancieraTitulosyValoresaCortoPlazo").val() != null) RSituacionFinancieraPTitulosyValoresaCortoPlazo = $('#selectSituacionFinancieraTitulosyValoresaCortoPlazo').val();
 	if ($("#selectSituacionFinancieraPasivosDiferidosaCortoPlazo").val() != null) RSituacionFinancieraPPasivosDiferidosaCortoPlazo = $('#selectSituacionFinancieraPasivosDiferidosaCortoPlazo').val();
 	if ($("#selectSituacionFinancieraFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo").val() != null) RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo = $('#selectSituacionFinancieraFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo').val();
 	if ($("#selectSituacionFinancieraProvisionesaCortoPlazo").val() != null) RSituacionFinancieraPProvisionesaCortoPlazo = $('#selectSituacionFinancieraProvisionesaCortoPlazo').val();
 	if ($("#selectSituacionFinancieraOtrosPasivosaCortoPlazo").val() != null) RSituacionFinancieraPOtrosPasivosaCortoPlazo = $('#selectSituacionFinancieraOtrosPasivosaCortoPlazo').val();

 	//Pasivo no Circulante
 	if ($("#selectSituacionFinancieraCuentasporPagaraLargoPlazo").val() != null) RSituacionFinancieraPCuentasporPagaraLargoPlazo = $('#selectSituacionFinancieraCuentasporPagaraLargoPlazo').val();
 	if ($("#selectSituacionFinancieraDocumentosporPagaraLargoPlazo").val() != null) RSituacionFinancieraPDocumentosporPagaraLargoPlazo = $('#selectSituacionFinancieraDocumentosporPagaraLargoPlazo').val();
 	if ($("#selectSituacionFinancieraDeudaPublicaaLargoPlazo").val() != null) RSituacionFinancieraPDeudaPublicaaLargoPlazo = $('#selectSituacionFinancieraDeudaPublicaaLargoPlazo').val();
 	if ($("#selectSituacionFinancieraPasivosDiferidosaLargoPlazo").val() != null) RSituacionFinancieraPPasivosDiferidosaLargoPlazo = $('#selectSituacionFinancieraPasivosDiferidosaLargoPlazo').val();
 	if ($("#selectSituacionFinancieraFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo").val() != null) RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo = $('#selectSituacionFinancieraFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo').val();
 	if ($("#selectSituacionFinancieraProvisionesaLargoPlazo").val() != null) RSituacionFinancieraPProvisionesaLargoPlazo = $('#selectSituacionFinancieraProvisionesaLargoPlazo').val();



 	if ($("#selectSituacionFinancieraAportaciones").val() != null) RSituacionFinancieraPAportaciones = $('#selectSituacionFinancieraAportaciones').val();
 	if ($("#selectSituacionFinancieraDonacionesdeCapital").val() != null) RSituacionFinancieraPDonacionesdeCapital = $('#selectSituacionFinancieraDonacionesdeCapital').val();
 	if ($("#selectSituacionFinancieraActualizaciondelaHaciendaPublicaPatrimonio").val() != null) RSituacionFinancieraPActualizaciondelaHaciendaPublicaPatrimonio = $('#selectSituacionFinancieraActualizaciondelaHaciendaPublicaPatrimonio').val();


 	if ($("#selectSituacionFinancieraResultadosdeEjerciciosAhorroDesahorro").val() != null) RSituacionFinancieraPResultadosdeEjerciciosAhorroDesahorro = $('#selectSituacionFinancieraResultadosdeEjerciciosAhorroDesahorro').val();
 	if ($("#selectSituacionFinancieraResultadosdeEjerciciosAnteriores").val() != null) RSituacionFinancieraPResultadosdeEjerciciosAnteriores = $('#selectSituacionFinancieraResultadosdeEjerciciosAnteriores').val();
 	if ($("#selectSituacionFinancieraRevaluos").val() != null) RSituacionFinancieraPRevaluos = $('#selectSituacionFinancieraRevaluos').val();
 	if ($("#selectSituacionFinancieraReservas").val() != null) RSituacionFinancieraPReservas = $('#selectSituacionFinancieraReservas').val();


	if (RSituacionFinancieraPEfectivoyEquivalentes == "" || 
		RSituacionFinancieraPDerechoARecibirEfectivoyEquivalentes == "" || 
		RSituacionFinancieraPDerechosaRecibirBienesoServicios == "" || 
		RSituacionFinancieraPInventarios == "" || 
		RSituacionFinancieraPAlmacenes == "" || 
		RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosCirculantes == "" || 
		RSituacionFinancieraPOtrosActivosCirculantes == ""  ||
		RSituacionFinancieraPInversionesFinancierasaLargoPlazo == "" || 
		RSituacionFinancieraPBienesInmueblesInfraestructurayConstruccionesenProceso == "" || 
		RSituacionFinancieraPBienesMuebles == "" || 
		RSituacionFinancieraPActivosIntangibles == "" || 
		RSituacionFinancieraPDepreciacionDeterioroyAmortizacionAcumuladadeBienes == "" || 
		RSituacionFinancieraPActivosDiferidos == "" || 
		RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosnoCirculantes == "" || 
		RSituacionFinancieraPOtrosActivosnoCirculantes == "" || 
		RSituacionFinancieraPCuentasporPagaraCortoPlazo == "" || 
		RSituacionFinancieraPDocumentosporPagaraCortoPlazo == "" || 
		RSituacionFinancieraPPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo == "" || 
		RSituacionFinancieraPTitulosyValoresaCortoPlazo == "" || 
		RSituacionFinancieraPPasivosDiferidosaCortoPlazo == "" || 
		RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo == "" || 
		RSituacionFinancieraPProvisionesaCortoPlazo == "" || 
		RSituacionFinancieraPOtrosPasivosaCortoPlazo == "" || 
		RSituacionFinancieraPCuentasporPagaraLargoPlazo == "" || 
		RSituacionFinancieraPDocumentosporPagaraLargoPlazo == "" || 
		RSituacionFinancieraPDeudaPublicaaLargoPlazo == "" || 
		RSituacionFinancieraPPasivosDiferidosaLargoPlazo == "" || 
		RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo == "" || 
		RSituacionFinancieraPProvisionesaLargoPlazo == "" || 


		RSituacionFinancieraPAportaciones == "" || 
		RSituacionFinancieraPDonacionesdeCapital == "" || 
		RSituacionFinancieraPActualizaciondelaHaciendaPublicaPatrimonio == "" || 
		RSituacionFinancieraPResultadosdeEjerciciosAhorroDesahorro == "" || 
		RSituacionFinancieraPResultadosdeEjerciciosAnteriores == "" || 
		RSituacionFinancieraPRevaluos == "" || 
		RSituacionFinancieraPReservas == "" ) {
		ocultaCargandoGeneral();
		muestraMensaje('Faltan datos', 3, 'mensajesValidacionesR1', 5000);
		return false;
	}

	$('#ModalUR').modal('hide');

	//Opcion para operacion
	dataObj = { 
	        option: 'grabarConfiguracionReportes',
	        reporte: 'SituacionFinanciera',
	        tagref: tagref,
	        RSituacionFinancieraPEfectivoyEquivalentes: RSituacionFinancieraPEfectivoyEquivalentes,
	        RSituacionFinancieraPDerechoARecibirEfectivoyEquivalentes: RSituacionFinancieraPDerechoARecibirEfectivoyEquivalentes,
	        RSituacionFinancieraPDerechosaRecibirBienesoServicios: RSituacionFinancieraPDerechosaRecibirBienesoServicios,
	        RSituacionFinancieraPInventarios: RSituacionFinancieraPInventarios,
	        RSituacionFinancieraPAlmacenes: RSituacionFinancieraPAlmacenes,
	        RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosCirculantes : RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosCirculantes,
	        RSituacionFinancieraPOtrosActivosCirculantes: RSituacionFinancieraPOtrosActivosCirculantes,
	        RSituacionFinancieraPInversionesFinancierasaLargoPlazo:	RSituacionFinancieraPInversionesFinancierasaLargoPlazo,
	        RSituacionFinancieraPDerechosaRecibirEfectivooEquivalentesaLargoPlazo: RSituacionFinancieraPDerechosaRecibirEfectivooEquivalentesaLargoPlazo,
			RSituacionFinancieraPBienesInmueblesInfraestructurayConstruccionesenProceso: RSituacionFinancieraPBienesInmueblesInfraestructurayConstruccionesenProceso,
			RSituacionFinancieraPBienesMuebles: RSituacionFinancieraPBienesMuebles,
			RSituacionFinancieraPActivosIntangibles: RSituacionFinancieraPActivosIntangibles,
			RSituacionFinancieraPDepreciacionDeterioroyAmortizacionAcumuladadeBienes: RSituacionFinancieraPDepreciacionDeterioroyAmortizacionAcumuladadeBienes,
			RSituacionFinancieraPActivosDiferidos: RSituacionFinancieraPActivosDiferidos,
			RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosnoCirculantes: RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosnoCirculantes,
			RSituacionFinancieraPOtrosActivosnoCirculantes: RSituacionFinancieraPOtrosActivosnoCirculantes,
			RSituacionFinancieraPCuentasporPagaraCortoPlazo: RSituacionFinancieraPCuentasporPagaraCortoPlazo,
			RSituacionFinancieraPDocumentosporPagaraCortoPlazo: RSituacionFinancieraPDocumentosporPagaraCortoPlazo,
			RSituacionFinancieraPPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo: RSituacionFinancieraPPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo,
			RSituacionFinancieraPTitulosyValoresaCortoPlazo: RSituacionFinancieraPTitulosyValoresaCortoPlazo,
			RSituacionFinancieraPPasivosDiferidosaCortoPlazo: RSituacionFinancieraPPasivosDiferidosaCortoPlazo,
			RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo: RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo,
			RSituacionFinancieraPProvisionesaCortoPlazo: RSituacionFinancieraPProvisionesaCortoPlazo,
			RSituacionFinancieraPOtrosPasivosaCortoPlazo: RSituacionFinancieraPOtrosPasivosaCortoPlazo,
			RSituacionFinancieraPCuentasporPagaraLargoPlazo : RSituacionFinancieraPCuentasporPagaraLargoPlazo,
			RSituacionFinancieraPDocumentosporPagaraLargoPlazo : RSituacionFinancieraPDocumentosporPagaraLargoPlazo,
			RSituacionFinancieraPDeudaPublicaaLargoPlazo : RSituacionFinancieraPDeudaPublicaaLargoPlazo,
			RSituacionFinancieraPPasivosDiferidosaLargoPlazo : RSituacionFinancieraPPasivosDiferidosaLargoPlazo,
			RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo : RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo,
			RSituacionFinancieraPProvisionesaLargoPlazo : RSituacionFinancieraPProvisionesaLargoPlazo,
			RSituacionFinancieraPAportaciones: RSituacionFinancieraPAportaciones,
			RSituacionFinancieraPDonacionesdeCapital: RSituacionFinancieraPDonacionesdeCapital,
			RSituacionFinancieraPActualizaciondelaHaciendaPublicaPatrimonio: RSituacionFinancieraPActualizaciondelaHaciendaPublicaPatrimonio,
			RSituacionFinancieraPResultadosdeEjerciciosAhorroDesahorro: RSituacionFinancieraPResultadosdeEjerciciosAhorroDesahorro,
			RSituacionFinancieraPResultadosdeEjerciciosAnteriores: RSituacionFinancieraPResultadosdeEjerciciosAnteriores,
			RSituacionFinancieraPRevaluos: RSituacionFinancieraPRevaluos,
			RSituacionFinancieraPReservas: RSituacionFinancieraPReservas,

	        proceso: ""
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/grp_configuracion_reportes_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		var titulo = '<h3><i class="fa-exclamation-circle" aria-hidden="true"></i></h3>';
	    if(data.result){
	    	ocultaCargandoGeneral();
	    	
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	    ocultaCargandoGeneral();
	});
}


$( document ).ready(function() {
	//Mostrar Catalogo

	

	$("ul.nav-tabs a").click(function (e) {
  		e.preventDefault();  
    	$(this).tab('show');
	});

	fnMostrarDatos('');

	fnCambioUnidadNegocio();

	
});