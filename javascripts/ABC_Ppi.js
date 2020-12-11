/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */
$( document ).ready(function() {
	//Mostrar Catalogo
	fnMostrarDatos('');
	$("#txtPyin").keyup(function(){
		var upperclave = $("#txtPyin").val();
		upperclave = upperclave.toUpperCase();	
		$("#txtPyin").val(""+upperclave);
	});
	$("#txtCunr").keyup(function(){
		var upperclave = $("#txtCunr").val();
		upperclave = upperclave.toUpperCase();	
		$("#txtCunr").val(""+upperclave);
	});
	// Validación del PPI
	$("#txtPyin").focusout(function(){
		$('#divMensajeOperacion').addClass('hide');
		var	Mensaje = "";
		// Se comenta validacion
		// if($(this).val().length!=11){
		// 	if($(this).val()!=""){
		// 	Mensaje += "<br>El PPI debe ser de 11 caracteres.";
		// 		$('#divMensajeOperacion').removeClass('hide');
		// 		$('#divMensajeOperacion').empty();
		// 		$('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + Mensaje.substring(4) + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
		// 	}
		// 	$("#txtRamo").val("");
		// 	$("#txtCunr").val("");
		// 	$(this).val('');
		// 	return false;
		// }

		var	Validado = true,
			Anio = $(this).val().substr(0,2),
			Ramo = $(this).val().substr(2,2),
			UR = $(this).val().substr(4,3),
			Consecutivo = $(this).val().substr(7,4),
			ValidadoRamo = verificarRamo(Ramo),
			ValidadoUR = verificarUR(UR);

		Validado = ( !isFinite(Anio) ? false : Validado );
		Validado = ( !isFinite(Ramo) ? false : Validado );
		Validado = ( !ValidadoRamo ? false : Validado );
		Validado = ( !ValidadoUR ? false : Validado );
		Validado = ( !isFinite(Consecutivo) ? false : Validado );

		if(!isFinite(Anio)){
			Mensaje += "<br>El Año "+Anio+" no es válido, debe tratarse de caracteres numéricos.";
		}
		if(!ValidadoRamo){
			Mensaje += "<br>El Ramo "+Ramo+" no es válido.";
		}
		if(!ValidadoUR){
			Mensaje += "<br>La UR "+UR+" no es válida.";
		}
		if(!isFinite(Consecutivo)){
			Mensaje += "<br>El Consecutivo "+Consecutivo+" no es válido, debe tratarse de caracteres numéricos.";
		}

		if(Validado){
			$("#txtRamo").val(Ramo);
			$("#txtCunr").val(UR);
		}else{
			Mensaje = "<br>La clave del PPI no es válda."+Mensaje;
			$('#divMensajeOperacion').removeClass('hide');
			$('#divMensajeOperacion').empty();
			$('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + Mensaje.substring(4) + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
			$("#txtRamo").val("");
			$("#txtCunr").val("");
			$(this).val('');
		}
	});
	$('#txtRamo').attr('readonly', true);
	$('#txtCunr').attr('readonly', true);
	// Poner y quitar formato de miles
	$("#txtTotal").focus(function(){
		if($(this).val()!=""){
			$(this).val( $(this).val().replace(/,/g , "") );
		}
	});
	$("#txtTotal").focusout(function(){
		if($(this).val()!=""){
			$(this).val( formatoComas($(this).val()) );
		}
	});
	$("#txtInv_ejercida").focus(function(){
		if($(this).val()!=""){
			$(this).val( $(this).val().replace(/,/g , "") );
		}
	});
	$("#txtInv_ejercida").focusout(function(){
		if($(this).val()!=""){
			$(this).val( formatoComas($(this).val()) );
		}
	});
	/*$("#prueba24").click(function(){

		$('#dateFechaFinal').click(function(){ $('#dateFechaFinal').data("Datetimepicker").update();});
	});

	$('input[name="dateFecha"').datetimepicker({
    format: 'DD-MM-YYYY'
}).on('dp.change', function(e) {
    //alert($('#dateFecha').val());
    
     //$('#dateFechaFinal').datetimepicker().minDate(e.date);
     //$('#dateFechaFinal').datetimepicker('destroy');
     
    
     $('#dateFechaFinal').data("DateTimePicker").minDate($('#dateFecha').val());
      $('#dateFechaFinal').data("Datetimepicker").update();
     $('#dateFechaFinal').datetimepicker({ format: 'DD-MM-YYYY', minDate: (new Date(e.date)) }).update();
*/
    /* $('.componenteCalendarioClase1').datetimepicker({
        
          format: 'DD-MM-YYYY',
        disabledDates:fnDiasFeriados(),
        daysOfWeekDisabled: [0,6],
        minDate: new Date()


    });
    $('.componenteCalendarioClase').datetimepicker('update'); */

    // $("#fijarfechafinal").empty();
  	// $("#fijarfechafinal").append('<component-date-label label="Fecha fin: " id="dateFechaFinal1" name="dateFechaFinal1" placeholder="Fecha final"></component-date-label>');
       
	$('#txtTotal').keyup(function(){
		this.value = this.value.replace(/[^0-9\.]/g,'');
	});

	$('#txtInv_ejercida').keyup(function(){
		this.value = this.value.replace(/[^0-9\.]/g,'');
	});

	$(document).on('focus', ':input', function() {
		$(this).attr('autocomplete', 'off');
	});

	$("#dateFechaFinal").change(function(){
		if( $("#dateFecha").val()!=""||new Date($("#dateFecha").val())>new Date($(this).val()) ){
			$(this).val("");
		}
	});

});


    /*$.datepicker.regional['es'] = {
        closeText: 'Cerrar',
        prevText: '<Ant',
        nextText: 'Sig>',
        currentText: 'Hoy',
        monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
        dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
        dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
        dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
        weekHeader: 'Sm',
        dateFormat: 'DD-MM-YYYY',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
    $.datepicker.setDefaults($.datepicker.regional['es']);

$("#ultimo").datepicker({
onSelect: function(selectDate, inst) {
            $('#ultimof').datepicker('option', 'minDate', selectDate);
            //$('#ultimof').datepicker('update',new Date(dateText));
            $('#ultimof').datepicker().children('input').val($('#ultimo').val());
        } 
	});

$("#ultimof").datepicker();

}); */

var proceso = "";

//console.log("data: "+JSON.stringify(data)); 

function fnMostrarDatos(pyin){
	console.log("fnMostrarDatos");
	dataObj = { 
	        option: 'mostrarCatalogo',
	        pyin: pyin
	      };
	muestraCargandoGeneral();
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Ppi_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    
	    	
	    	 	dataFuncionJason = data.contenido.datos;
			columnasNombres = data.contenido.columnasNombres;
			columnasNombresGrid = data.contenido.columnasNombresGrid;
			var columnasDescartarExportar = [9, 10];
	    	var columnasExcel= [0,1,2,3,4,5,6,7,8];
			var columnasVisuales= [0,1,2,3,4,5,6,7,8,9,10];
			nombreExcel=data.contenido.nombreExcel;

	    	fnLimpiarTabla('divTabla', 'divCatalogo');
	    	fnAgregarGrid_Detalle(dataFuncionJason, columnasNombres, columnasNombresGrid, 'divCatalogo', ' ', 1, columnasExcel, false,false, '', columnasVisuales, nombreExcel);
	    	//fnAgregarGrid_Detalle(dataFuncionJason, columnasNombres, columnasNombresGrid, 'divCatalogo', ' ', 1, columnasDescartarExportar, false);
	    	ocultaCargandoGeneral();
	    }else{
	    	ocultaCargandoGeneral();
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	    ocultaCargandoGeneral();
	});
}

function fnAgregarCatalogoModal(){
	$('#identificador').remove();
	$('#divMensajeOperacion').addClass('hide');
	console.log("fnAgregarCatalogoModal");

	proceso = "Agregar";

	$("#txtPyin").prop("readonly", false);
	$('#txtPyin').val("");
	$('#txtNomb').val("");
	$('#txtDescripcion').val("");
	$('#txtRamo').val("");
	$('#txtCunr').val("");
	$('#dateFecha').val("");
	$('#dateFechaFinal').val("");
	$('#txtTotal').val("");
	$('#txtInv_ejercida').val("");
	$('#dateFact').val("");


	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Programa Proyecto de Inversión</h3>';
	$('#ModalPyin_Titulo').empty();
    $('#ModalPyin_Titulo').append(titulo);
	$('#ModalPyin').modal('show');
}

function fnAgregar(){
	var identificador = $("#identificador").val();
	var pyinnuevo = $('#txtPyin').val();
	var nombre= $('#txtNomb').val();
	var descripcion = $('#txtDescripcion').val();
	var ramo = $('#txtRamo').val();
	var cunr = $('#txtCunr').val();
	var fecha = $('#dateFecha').val();
	var fechaFinal= $('#dateFechaFinal').val();
	var total =$('#txtTotal').val().replace(/,/g , "");
	var inv_ejercida=$('#txtInv_ejercida').val().replace(/,/g , "");
	var fact =$('#dateFact').val();
	var msg = "";

//alert(fecha+'--'+fechaFinaltotal+'--'+inve_ejercida+'--'+fact);

	

	if (pyinnuevo == "" || descripcion == "" || fecha=="" ||fechaFinal=="" || fact=="" || total=="" || inv_ejercida=="" || nombre == "" || ramo == "" || cunr == "") {
		if (pyinnuevo == "" ) {
			msg += '<p>Falta capturar la clave PPI</p>';
		}
		if (nombre=="" ) {
			msg += '<p>Falta capturar el Nombre </p>';
		}
		if (descripcion == "" ) {
			msg += '<p>Falta capturar la Descripción</p>';
		}
		if (ramo=="" ) {
			msg += '<p>Falta capturar el Ramo</p>';
		}
		if (cunr=="" ) {
			msg += '<p>Falta capturar la CUNR</p>';
		}
		if (fecha=="") {
			msg += '<p>Falta capturar la Fecha Inicial</p>';	
		}
		if (fechaFinal=="") {
			msg += '<p>Falta capturar la Fecha Final</p>';
		}
		if (total=="" ) {
			msg += '<p>Falta capturar el Total</p>';
		}
		if (inv_ejercida=="" ) {
			msg += '<p>Falta capturar la Inversión Ejercida</p>';
		}
		if (fact=="" ) {
			msg += '<p>Falta capturar la Fecha Activa</p>';
		}
		
		//$('#ModalPyin').modal('hide');
		//muestraMensaje('Faltan datos', 3, 'mensajesValidaciones', 5000);
		//muestraMensaje(msg, 3, 'divMensajeOperacion', 5000);
		$('#divMensajeOperacion').removeClass('hide');
		$('#divMensajeOperacion').empty();
		$('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
		//muestraMensaje('Falata clave PPI mensaje error', 3, 'OperacionMensaje', 5000);
		//return false;
		
	}else{
		$('#ModalPyin').modal('hide');
		dataObj = { 
		        option: 'AgregarCatalogo',
		        identificador: identificador,
		        pyinnuevo: pyinnuevo,
		        descripcion: descripcion,
		        fecha:fecha,
		        nombre:nombre,
		        ramo:ramo,
		        cunr:cunr,
		        fechaFinal:fechaFinal,
		        total:total,
		        inv_ejercida:inv_ejercida,
		        fact:fact,
		        proceso: proceso
		      };
		muestraCargandoGeneral();
		$.ajax({
		      method: "POST",
		      dataType:"json",
		      url: "modelo/ABC_Ppi_modelo.php",
		      data:dataObj
		  })
		.done(function( data ) {
			var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Información</h3>';
		    if(data.result){
		   muestraMensaje(data.contenido, 1, 'OperacionMensaje', 5000);
		   			var datos=data.contenido;
		   			if(datos.includes("Ya existe")){
		    	
		    		}else{
		    		fnLimpiarTabla('divTabla', 'divCatalogo');	
		    		fnMostrarDatos('');
		    		}
		    	
		    	ocultaCargandoGeneral();
		    }else{
		    	ocultaCargandoGeneral();
		    }
		})
		.fail(function(result) {
			console.log("ERROR");
		    console.log( result );
		    ocultaCargandoGeneral();
		});
	}
}

function fnModificar(pyin){
	$('#divMensajeOperacion').addClass('hide');
	//alert(pyin);
	proceso = "Modificar";
	//alert(pyin);
	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        identificador: pyin,
	        proceso:proceso
	      };
	muestraCargandoGeneral();
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Ppi_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datos;
	    	for (key in info){
	    		if($('#ModalPyin_Mensaje').find('#identificador').size()){
	    			$('#identificador').val(info[key].identificador);
	    		}else{
	    			$('#ModalPyin_Mensaje').append('<input type="hidden" name="identificador" id="identificador" value="'+info[key].identificador+'"/>');
	    		}
	    		$("#txtPyin").val(""+info[key].clave);
	    		$("#txtPyin").prop("readonly", true);
				$("#txtNomb").val(""+info[key].nombre);
				$("#txtDescripcion").val(""+info[key].descripcion);	
				$("#txtRamo").val(""+info[key].ramo);
				$("#txtCunr").val(""+info[key].cunr);
				$("#dateFecha").val(""+info[key].fechainicio);
				$("#dateFechaFinal").val(""+info[key].fechafin);
				$("#txtTotal").val(""+info[key].total);
				$("#txtInv_ejercida").val(""+info[key].inversion);
				$("#dateFact").val(""+info[key].fact);
				
				$("#txtTotal").val( formatoComas($("#txtTotal").val()) );
				$("#txtInv_ejercida").val( formatoComas($("#txtInv_ejercida").val()) );

			var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Programa Proyecto de Inversión</h3>';
			$('#ModalPyin_Titulo').empty();
		    $('#ModalPyin_Titulo').append(titulo);
			$('#ModalPyin').modal('show');
	    		}
	    		//muestraMensaje(data.contenido, 1, 'OperacionMensaje', 5000);
	    		ocultaCargandoGeneral();

	    }else{
	    	$('#divMensajeOperacion').removeClass('hide');
	    	ocultaCargandoGeneral();
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	    ocultaCargandoGeneral();
	});
}

function fnEliminar(id,pyin){
	var	mensaje = '',
		verificarIR = verificarIntegridadReferencial(id);

	$("#txtPyinEliminar").val(""+id);
	if(verificarIR){
		$("#btnEliminar").hide();
		mensaje = "El Programa Proyecto de Inversión <b> '"+pyin+"'</b> no puede eliminarse porque ya está en uso.";
	}else{
		$("#btnEliminar").show()
		mensaje = "Desea eliminar el elemento Programa Proyecto de Inversión <b> '"+pyin+"'</b>.";
	}

	$('#ModalPyinEliminar_Mensaje').empty();
    $('#ModalPyinEliminar_Mensaje').append(mensaje);
	$('#ModalPyinEliminar').modal('show');
	
}

function fnEliminarEjecuta(){
	var pyin = $('#txtPyinEliminar').val();

	$('#ModalPyinEliminar').modal('hide');
	
	//Opcion para operacion
	dataObj = { 
	        option: 'eliminarpyin',
	        pyin: pyin
	      };

	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Ppi_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Información</h3>';
			muestraMensaje(data.contenido, 1, 'OperacionMensaje', 5000);

			fnLimpiarTabla('divTabla', 'divCatalogo');
	    	fnMostrarDatos('');
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}

// Función para verificar que el Ramo sea válido
function verificarRamo(Ramo){
	var	Verificado = false;
	dataObj = {
		option: 'verificarRamo',
		Ramo: Ramo
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType: "json",
		url: "modelo/ABC_Ppi_modelo.php",
		data: dataObj
	})
	.done(function(data){
		Verificado = data.contenido;
	})
	.fail(function(result){
		console.log("ERROR");
		console.log(result);
	});

	return Verificado;
}

// Función para verificar que la UR sea válida
function verificarUR(UR){
	var	Verificado = false;
	dataObj = {
		option: 'verificarUR',
		UR: UR
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType: "json",
		url: "modelo/ABC_Ppi_modelo.php",
		data: dataObj
	})
	.done(function(data){
		Verificado = data.contenido;
	})
	.fail(function(result){
		console.log("ERROR");
		console.log(result);
	});

	return Verificado;
}

// Función para verificar que la UR sea válida
function verificarIntegridadReferencial(pyin){
	var	Verificado = false;
	dataObj = {
		option: 'verificarIntegridadReferencial',
		pyin: pyin
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType: "json",
		url: "modelo/ABC_Ppi_modelo.php",
		data: dataObj
	})
	.done(function(data){
		Verificado = data.contenido;
	})
	.fail(function(result){
		console.log("ERROR");
		console.log(result);
	});

	return Verificado;
}
