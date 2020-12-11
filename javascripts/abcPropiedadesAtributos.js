 /**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jesùs Reyes Santos
 * @version 0.1
 */
var url = "modelo/abcAdministracionContratosModelo.php";
var proceso = "";
var tabla = "tablaReducciones";
var tabla2 = "tablaFilas";
var folio = "";

var panel = 1;
var datos = new Array();
var datos2 = new Array();
var name;

$(document).ready(function() {
	//Mostrar Catalogo
	if($('#isUnique').val() == ''){
		$('#continuar').css('display','none');
		$('#regresar').css('display','none');
	}
	if($('#hiddenBack').val()){
		$('#regresar').css('display','none');
	}
	var folioContrato=document.getElementById("txtFolioContrato").value;
	var folio=document.getElementById("txtFolioConfiguracion").value;

	fnObtenerFilas(folioContrato,tabla2,panel,folio);

	// $('.contribuyente').val($('.contribuyente').val().replace(/&nbsp;/g, ' '));

	name = $('.contribuyente').val();
	
	
	//$('#txtFolioContrato').val(""+folio);
	$('#continuar').on('click',function(){
		// if($('#isUnique').val()== 'true')
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>¿Estas seguro de que quieres avanzar?</p>';
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnAvanzar()");
		// if($('#isUnique').val()== 'true')
			// console.log('loadNext',2);
			// parent.reload(2);
	});
	$('#regresar').on('click',function(){
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>¿Estas seguro de que quieres regresar?</p>';
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "parent.reload(0);");
		// if($('#isUnique').val()== 'true')
			
	});


});

function fnAvanzar(){
	var folioContrato=document.getElementById("txtFolioContrato").value;
	var folio=document.getElementById("txtFolioConfiguracion").value;
	fnObtenerFuncion(folioContrato,folio);
	
}

function fnObtenerFuncion(folioContrato,folio){
	dataObj = { 
		option: 'obtenerDatosAvanzar',
		folioContrato: folioContrato,
		folio: folio
	  };
//Obtener datos de las bahias
$.ajax({
	async:false,
	cache:false,
	method: "POST",
	dataType:"json",
	url: "modelo/abcPropiedadesAtributosModelo.php",
	data:dataObj
  })
.done(function( data ) {
	//console.log("Bien");
	if(data.result){
		//Si trae informacion
		info=data.contenido.datos;
		//console.log("presupuesto: "+JSON.stringify(info));
		// console.log(info[0].Valor);
		if(info[0].Valor == ''){
			fnAlmacenarCaptura();
		}else{
			fnAlmacenarCapturaEditar();	
		}
	}else{

		
	}
})
.fail(function(result) {
	//ocultaCargandoGeneral();
	console.log("ERROR");
	console.log( result );
});
}

/******************************************* traer datos  ********************************************************************************/
function fnObtenerFilas(folioContrato,tabla2,panel,folio) {
	
	dataObj = { 
	        option: 'obtenerDatosactualizar',
			folioContrato: folioContrato,
			folio: folio
	      };
	//Obtener datos de las bahias
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/abcPropiedadesAtributosModelo.php",
		data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datos;
			//console.log("presupuesto: "+JSON.stringify(info));
			// console.log(info[0].Valor);
			if(info[0].Valor == ''){
			var folio=document.getElementById("txtFolioConfiguracion").value;
			fnObtenerPresupuesto(folio,tabla,panel);
			}else{
			fnMostrarFilas(info, tabla2, panel);	
			}
			$('#txtContribuyente').val($('#txtContribuyente').val().replace(/&nbsp;/g, ' '));

			
	    	
	    }else{

	    	
	    }
	})
	.fail(function(result) {
		//ocultaCargandoGeneral();
		console.log("ERROR");
	    console.log( result );
	});

}

function fnMostrarFilas(dataJson, tabla2, panel) {

	var contenido = '';

	var style = 'style="text-align:center;"';
	

	datos2 = dataJson;
	// console.log("fnMostrarPresupuesto folio: "+JSON.stringify(folio));

	// numLineaReducciones
	for (var key in dataJson) {
		Atributos = dataJson[key].Atributos;
		Etiqueta = dataJson[key].Etiqueta;
		Valor = dataJson[key].Valor;

		// contenido = '<input id="ln_valor" name="ln_valor">'+'</input>' + contenido;
	
		// contenido = '<input id="'+dataJson[key].Etiqueta+'" name="'+dataJson[key].Etiqueta+'" value="'+dataJson[key].Etiqueta+'" readonly>'+'</input>' + contenido;
		
		// contenido = '<input id="'+dataJson[key].Atributos+'" name="'+dataJson[key].Atributos+'" value="'+dataJson[key].Atributos+'" readonly>' + contenido + '</input>';
		// contenido = contenido + '<div class="col-md-6">';
		// contenido  =  contenido + '<component-text-label label="'+dataJson[key].Etiqueta+':" id="'+dataJson[key].Atributos+'" name="'+dataJson[key].Atributos+'" placeholder="'+dataJson[key].Etiqueta+'" title="'+dataJson[key].Etiqueta+'" value="'+dataJson[key].Valor+'"></component-text-label>';	
		// contenido = contenido + '</div><div class="row"></div><br>';

		contenido = contenido + '<div class="col-md-3 col-xs-3">'; 
		contenido = contenido + '<span><label>'+dataJson[key].Etiqueta+': </label></span>';
		contenido = contenido + '</div>';

		contenido = contenido + '<div class="col-md-9 col-xs-9">';
		contenido = contenido + '<input id="'+dataJson[key].Atributos+'" class="form-control"  name="'+dataJson[key].Atributos+'" placeholder="'+dataJson[key].Etiqueta+'" value="'+dataJson[key].Valor+'" type="text" style="width: 100%;">';
		contenido = contenido + '</div><div class="row"></div><br>';

		

	}
	

	

	$('#'+tabla2+'').append(contenido);

	
	fnEjecutarVueGeneral(''+tabla2);

	$('.btnNuevo').css('display','none');
	$('.btnEditar').css('display','inline-block');



}

function fnAlmacenarCapturaEditar(tablaReducciones, msjvalidaciones="") {

	muestraCargandoGeneral();
	var datosCaptura = new Array();

	for (var key in datos2) {
			datos2[key].Valor = $('#'+datos2[key].Atributos).val();

			if (datos2[0].Valor == '') {
				// Si no selecciono Unidad Responsable
				ocultaCargandoGeneral();
				// muestraMensaje('Seleccionar UR para continuar con el proceso', 3, 'ModalGeneral_Advertencia', 5000);
				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
				var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El primer campo es obligatorio</p>';
				muestraModalGeneral(3, titulo, mensaje);
				return true;
			}

		}
		// console.log("dataJsonTipoSolicitud: "+JSON.stringify(datos));  
		// return;
		
		
	id_folio_contrato = $('#txtFolioContrato').val();
	id_folio_configuracion = $('#txtFolioConfiguracion').val();
	
	

	
	dataObj = { 
	        option: 'actualizarAtributos',
	        datos2: datos2, //JSON.parse(JSON.stringify(datosCapturaReducciones)), 
			id_folio_contrato: id_folio_contrato,
			id_folio_configuracion: id_folio_configuracion
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/abcPropiedadesAtributosModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
			ocultaCargandoGeneral();
			
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Se actualizó exitosamente los datos en el folio '+ id_folio_contrato +'</p>';
			muestraModalGeneral(3, titulo, mensaje);
			return true;
	    
	    }else{
	    	//Obtener Datos de un No. Captura
			ocultaCargandoGeneral();

			if (msjvalidaciones != "") {
				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> '+tituloModalValidaciones+'</p></h3>';
				muestraModalGeneral(4, titulo, msjvalidaciones);

				muestraMensajeTiempo(data.Mensaje, 3, 'ModalGeneral_Advertencia', 5000);
			}else if (!$('#ModalGeneral').is(':hidden')) {
	    		muestraMensajeTiempo(data.Mensaje, 3, 'ModalGeneral_Advertencia', 5000);
	    	}else{
	    		//muestraMensajeTiempo(data.Mensaje, 3, 'divMensajeOperacion', 5000);
	    		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		    	muestraModalGeneral(3, titulo, data.Mensaje);
	    	}
	    }
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
		console.log("ERROR");
	    console.log( result );
	});
	parent.reload(2);
}

 


function fnObtenerPresupuesto(folio, tabla, panel) {
	
	dataObj = { 
	        option: 'obtenerDatos',
	        folio: folio,
	      };
	//Obtener datos de las bahias
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/abcPropiedadesAtributosModelo.php",
		data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datos;
	    	//console.log("presupuesto: "+JSON.stringify(info));
	    	fnMostrarPresupuesto(info, tabla, panel);

	    }else{
	    	//ocultaCargandoGeneral();
	    	// muestraMensaje(data.Mensaje, 3, 'divMensajeOperacion', 5000);
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+data.Mensaje+'</p>';
			muestraModalGeneral(3, titulo, mensaje);
	    }
	})
	.fail(function(result) {
		//ocultaCargandoGeneral();
		console.log("ERROR");
	    console.log( result );
	});
}


function fnMostrarPresupuesto(dataJson, tabla, panel) {

	var contenido = '';

	var style = 'style="text-align:center;"';
	

	datos = dataJson;
	// console.log("fnMostrarPresupuesto folio: "+JSON.stringify(folio));

	// numLineaReducciones
	for (var key in dataJson) {
		Atributos = dataJson[key].Atributos;
		Etiqueta = dataJson[key].Etiqueta;
		Valor = dataJson[key].Valor;

		// contenido = '<input id="ln_valor" name="ln_valor">'+'</input>' + contenido;
	
		// contenido = '<input id="'+dataJson[key].Etiqueta+'" name="'+dataJson[key].Etiqueta+'" value="'+dataJson[key].Etiqueta+'" readonly>'+'</input>' + contenido;
		
		// contenido = '<input id="'+dataJson[key].Atributos+'" name="'+dataJson[key].Atributos+'" value="'+dataJson[key].Atributos+'" readonly>' + contenido + '</input>';
		
		// contenido = contenido + '<div class="col-md-6">';
		// contenido  =  contenido + '<component-text-label label="'+dataJson[key].Etiqueta+':" id="'+dataJson[key].Atributos+'" name="'+dataJson[key].Atributos+'" placeholder="'+dataJson[key].Etiqueta+'" title="'+dataJson[key].Etiqueta+'" value="'+dataJson[key].Valor+'"></component-text-label>';	
		// contenido = contenido + '</div><div class="row"></div><br>';


		contenido = contenido + '<div class="col-md-3 col-xs-3">'; 
		contenido = contenido + '<span><label>'+dataJson[key].Etiqueta+': </label></span>';
		contenido = contenido + '</div>';

		contenido = contenido + '<div class="col-md-9 col-xs-9">';
		contenido = contenido + '<input id="'+dataJson[key].Atributos+'" class="form-control"  name="'+dataJson[key].Atributos+'" placeholder="'+dataJson[key].Etiqueta+'" value="'+dataJson[key].Valor+'" type="text" style="width: 100%;">';
		contenido = contenido + '</div><div class="row"></div><br>';

		
	}

	

	$('#'+tabla+'').append(contenido);

	
	fnEjecutarVueGeneral(''+tabla);

	$('·btnNuevo').css('display','inline-block');
	$('.btnEditar').css('display','none');
	// $('.contribuyente').val($('.contribuyente').val().replace(/&nbsp;/g, ' '));

}


function fnAlmacenarCaptura(tablaReducciones, msjvalidaciones="") {
	// console.log("fnPresupuestoCaptura");
	// console.log("datosReducciones: "+JSON.stringify(datosReducciones));
	$('.btnNuevo').prop( "disabled", true );
	
	muestraCargandoGeneral();
	var datosCaptura = new Array();

	for (var key in datos) {
			datos[key].Valor = $('#'+datos[key].Atributos).val();

			if (datos[0].Valor == '') {
				// Si no selecciono Unidad Responsable
				ocultaCargandoGeneral();
				// muestraMensaje('Seleccionar UR para continuar con el proceso', 3, 'ModalGeneral_Advertencia', 5000);
				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
				var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El primer campo es obligatorio</p>';
				muestraModalGeneral(3, titulo, mensaje);
				return true;
			}

		}
		// console.log("dataJsonTipoSolicitud: "+JSON.stringify(datos));  
		// return;
		
		
	id_folio_contrato = $('#txtFolioContrato').val();
	id_folio_configuracion = $('#txtFolioConfiguracion').val();
	
	

	
	dataObj = { 
	        option: 'guardarSuficiencia',
	        datos: datos, //JSON.parse(JSON.stringify(datosCapturaReducciones)), 
			id_folio_contrato: id_folio_contrato,
			id_folio_configuracion: id_folio_configuracion
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/abcPropiedadesAtributosModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
			ocultaCargandoGeneral();
			
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Se agregaron exitosamente los datos en el folio '+ id_folio_contrato +'</p>';
			muestraModalGeneral(3, titulo, mensaje);
			return true;
	    
	    }else{
	    	//Obtener Datos de un No. Captura
			ocultaCargandoGeneral();

			if (msjvalidaciones != "") {
				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> '+tituloModalValidaciones+'</p></h3>';
				muestraModalGeneral(4, titulo, msjvalidaciones);

				muestraMensajeTiempo(data.Mensaje, 3, 'ModalGeneral_Advertencia', 5000);
			}else if (!$('#ModalGeneral').is(':hidden')) {
	    		muestraMensajeTiempo(data.Mensaje, 3, 'ModalGeneral_Advertencia', 5000);
	    	}else{
	    		//muestraMensajeTiempo(data.Mensaje, 3, 'divMensajeOperacion', 5000);
	    		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		    	muestraModalGeneral(3, titulo, data.Mensaje);
	    	}
	    }
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
		console.log("ERROR");
	    console.log( result );
	});
	parent.reload(2);


}

/******************************************* stocks  ********************************************************************************/