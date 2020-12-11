/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */

// Nombre de la vista para la tabla de Reduccion y Ampliacion
var tablaReducciones = "tablaReducciones";
var idClavePresupuestoReducciones = 0;
var datosReducciones = new Array();
var panelReducciones = 1;
var tituloGeneral = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>'; 

// Filtro Generales
var legalid = "";
var tagref = "";

// Estatus para guardar
var statusGuardar = 1;

// Numero de Linea en Reduccion y Ampliacion
var numLineaReducciones = 1;

$( document ).ready(function() {
	// Datos botones
	fnObtenerBotones('divBotones');

	//Obtener Datos de un No. Captura
	fnObtenerInfoNoCaptura();

	// Deshabilitar pagina
	fnDeshabilitaPagSuficiencia();

	$("#btnBuscarFolioBaja").click(function() {

		if(fnValidacionBusqueda() == false){
			return false;
		}

		// Buscar información de las retenciones
		if (datosReducciones.length > 0) {
			// Si tiene infomacion
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p>¿Desea Cargar la información nuevamente?</p>';
			muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnObtenerActivos()");
		} else {
			// Si no tiene obtener activos
			fnObtenerActivos();
		}
	});

	$("#btnUploadFile").click(function(e) {
        e.preventDefault(); // to stop  load  to form
        fnLoadFiles();
    });
});

// <Archivos>
$(document).on('click','#cargarMultiples',function(){
	$("#tablaDetallesArchivos .newRow").remove();
});

$(document).on('change','#cargarMultiples',function(){
    //var cols = [];
    archivosNopermitidos= new Array();
    archivosNopermitidos=[];
    archivosTotales=0;
    estilo='text-center w100p';

    //var filasArchivos='';

    $("#tablaDetallesArchivos .newRow").remove();
    for(var ad=0; ad< this.files.length; ad++){
        var file = this.files[ad];
        nombre = file.name;
        tamanio = file.size;
        tipo = file.type;
        
        cols = [];
        // filasArchivos+='<tr class="filasArchivos"> <td>'+ nombre+'</td> <td> <b>Tamaño:</b>'+ tamanio+'</td> <td> <b>Tipo:</b> '+ tipo+'</td> <td class="text-center"> <span class="quitarArchivos"><input type="hidden" name="nombrearchivo" value="'+nombre+'" >    <span  class="btn bgc8" style="color:#fff;">    <span class="glyphicon glyphicon-remove"></span></sapn> </span> </td></tr> ';
       
        nombre = generateItem('span', {html:file.name});
        cols.push(generateItem('td', {
            style: estilo
        }, nombre));

        observacion = generateItem('form',{name:"flieCLC"+ad}, generateItem('textarea', {id:'txtObservacion'+ad,name:'txtObservacion'+ad,rows:'1',style:'resize: vertical;',class: 'form-control  selectNewObservacionFile'}));
        cols.push(generateItem('td', {
            style: 'w300p'
        }, observacion));

        quitar = generateItem('span', {class:'quitarArchivos glyphicon glyphicon-remove btn btn-xs bgc8',style:'color:#fff;display:none; '},generateItem('input',{type:'hidden',val:file.name}));
        cols.push(generateItem('td', {
            style: estilo
        }, quitar));
        
        tr = generateItem('tr', {
        class: 'text-center w100p newRow'
        }, cols);

       $("#tablaDetallesArchivos").find('tbody').append(tr);
       archivosTotales++;
    }

    
    $('#muestraAntesdeEnviar').show();
    $('#enviarArchivosMultiples').show();
});

function fnLoadFiles(){
    $("#tipoInputFile").empty();
    var m=$("#esMultiple").val();
    opts={
            type: 'file',
            onpaste: 'return false',
            class: 'btn bgc8 form-control text-center',
            id: 'cargarMultiples',
            name: 'archivos[]',
            style:'display: none'
        };
   
    if(m!=0){
        type="multiple";
        opts['multiple']='multiple';
    }
    
    data= generateItem('input', opts);
    $("#tipoInputFile").append(data);
    
    $("#cargarMultiples").click(); // click to  new  element to trigger finder Dialog to  get files
}

// </Archivos>

function fnEstatusCampos(estatusInput = true, statusCombo='disable'){
	$('#PanelPrincipal input[type="text"], input[type="hidden"], textarea').each(
        function(index){  
            var input = $(this);
            input.prop('disabled', estatusInput);
        }
    );

    $('#PanelPrincipal select').each(
        function(index){  
            var combo = $(this);
            combo.multiselect(statusCombo);
        }
    );
}


function fnValidacionBusqueda(){
	var errMensaje="";
	var ur =fnObtenerOption('selectUnidadNegocio',1);
	var ue =fnObtenerOption('selectUnidadEjecutora',1);
	var tipoBien =fnObtenerOption('selectTipo',1);
	
	if(ur == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo UR es obligatorio.</p>';
	}

	if(ue == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo UE es obligatorio.</p>';
	}

	if(tipoBien == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo de Tipo de Bien es obligatorio.</p>';
	}

	if(errMensaje!=""){
		muestraModalGeneral(3,tituloGeneral,errMensaje);
		return false;
	}else{
		return true;
	}
}

function fnObtenerActivos() {
	// funcion para obtener activos
	muestraCargandoGeneral();

	dataObj = { 
	        option: 'obtenerActivos',
	        tagref: $('#selectUnidadNegocio').val(),
			ue: $('#selectUnidadEjecutora').val(),
			selectTipo: $('#selectTipo').val(),
			txtFolioBaja: $('#txtFolioBaja').val(),
			type: type,
			transno: transno
	      };
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/disposicionPatrimonialModelo.php",
		data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datos;
	    	// console.log("info: "+JSON.stringify(info));

	    	datosReducciones = data.contenido.datos;

	    	$('#'+tablaReducciones+' tbody').empty();
	    	idClavePresupuestoReducciones = 0;
			numLineaReducciones = 1;
	    	for (var key in datosReducciones) {
	    		fnMostrarPresupuesto(datosReducciones[key], tablaReducciones, panelReducciones);
	    	}

	    	if (datosReducciones.length > 0) {
	    		$('#selectUnidadNegocio').multiselect('disable');
	    		$('#selectUnidadEjecutora').multiselect('disable');
	    		$('#selectTipo').multiselect('disable');
	    	} else {
	    		$('#selectUnidadNegocio').multiselect('enable');
	    		$('#selectUnidadEjecutora').multiselect('enable');
	    		$('#selectTipo').multiselect('enable');
	    	}
	    	
	    	ocultaCargandoGeneral();
	    }else{
	    	ocultaCargandoGeneral();
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+data.Mensaje+'</p>';
			muestraModalGeneral(3, titulo, mensaje);
	    }
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
		console.log("ERROR");
	    console.log( result );
	});
}

/**
 * Función para cagrar información almacenada de la captura a visualizar
 * @return {[type]} [description]
 */
function fnObtenerInfoNoCaptura() {
	dataObj = { 
	        option: 'cargarInfoNoCaptura',
			type: type,
			transno: transno
	      };
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/disposicionPatrimonialModelo.php",
		data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	datosReducciones=data.contenido.datos;
	    	
	    	if (data.contenido.transno != 0) {
	    		$('#txtNoCaptura').empty();
				$('#txtNoCaptura').append(data.contenido.transno);
				statusGuardar = data.contenido.estatus;

				$('#txtEstatus').empty();
				if (data.contenido.statusname != "" || data.contenido.statusname != null) {
					$('#txtEstatus').append(""+data.contenido.statusname);
				}
				
				$('#txtFechaCaptura').val("");
				if (data.contenido.fechaCaptura != "" || data.contenido.fechaCaptura != null) {
					$('#txtFechaCaptura').val(""+data.contenido.fechaCaptura);
				}

				if (data.contenido.selectTipo != "" || data.contenido.selectTipo != null) {
					$('#selectTipo').val(''+data.contenido.selectTipo);
					$('#selectTipo').multiselect('rebuild');

					// Deshabilitar operación
					$('#selectTipo').multiselect('disable');
				}

				$('#txtJustificacion').val(""+data.contenido.justificacion);
				
				if (data.contenido.legalid != "" || data.contenido.legalid != null) {
					legalid = data.contenido.legalid;
					$('#selectRazonSocial').val(''+data.contenido.legalid);
					$('#selectRazonSocial').multiselect('rebuild');
				}
				
				if (data.contenido.tagref != "" || data.contenido.tagref != null) {
					tagref = data.contenido.tagref;
					$('#selectUnidadNegocio').val(''+data.contenido.tagref);
					$('#selectUnidadNegocio').multiselect('rebuild');
				}

				if (data.contenido.ln_ue != "" || data.contenido.ln_ue != null) {
					$('#selectUnidadEjecutora').val(''+data.contenido.ln_ue);
					$('#selectUnidadEjecutora').multiselect('rebuild');
				}

				// Deshabilitar UR y UE
				$('#selectUnidadNegocio').multiselect('disable');
				$('#selectUnidadEjecutora').multiselect('disable');

				if (data.contenido.selectTipoDisposicion != "" || data.contenido.selectTipoDisposicion != null) {
					$('#selectDisposicionFinal').val(''+data.contenido.selectTipoDisposicion);
					$('#selectDisposicionFinal').multiselect('rebuild');
					$('#selectDisposicionFinal').multiselect('disable');
				}

				if (data.contenido.transnoBaja != "" || data.contenido.transnoBaja != null) {
					$('#txtFolioBaja').val(""+data.contenido.transnoBaja);
					$('#txtFolioBaja').prop('disabled',true);
				}

				fnObtenerArchivos(data.contenido.transno);

			}

			// console.log("datosReducciones: "+JSON.stringify(datosReducciones));
			$('#'+tablaReducciones+' tbody').empty();
	    	idClavePresupuestoReducciones = 0;
			numLineaReducciones = 1;
	    	for (var key in datosReducciones ) {
				fnMostrarPresupuesto(datosReducciones[key], tablaReducciones, panelReducciones);
			}

	    	if (datosReducciones.length > 0) {
	    		// Si agrego datos deshabilitar UE
	    		$('#selectUnidadNegocio').multiselect('disable');
				$('#selectUnidadEjecutora').multiselect('disable');
				$('#selectTipo').multiselect('disable');
	    	}

	    	fnDeshabilitaPagSuficiencia();

	    }else{
	    	//muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}

function fnObtenerArchivos(folio){
	
	var fd = new FormData();
	fd.append("option","obtenerArchivos");
	fd.append("funcion",'2487');
	fd.append("type_mov",'1003');
	fd.append("transno_mov",folio);

	$.ajax({
	    async:false,
	    url:"modelo/resguardo_detalles_modelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	if(data.result){
	    		//Mostrar Archivos 
    			var trArchivos="";
    			var cols = [];
    			var estilo='text-center w100p';
    			var archivos=0;
    			var datosDetalleArchivos = data.contenido.datos;
    			
    			$("#tablaDetallesArchivos >tbody").empty();

    			for(var index3 in datosDetalleArchivos){
    				cols = [];

			        nombre = generateItem('a', {href:datosDetalleArchivos[index3].urlFile,target:'_blank', html:datosDetalleArchivos[index3].nameFile});
			        cols.push(generateItem('td', {
			            style: estilo
			        }, nombre));

			        textareaObs='<textarea id="txtObservacion'+index3+'" name="txtObservacion'+index3+'" rows="1" data-idFile="'+datosDetalleArchivos[index3].idFile+'" style="resize: vertical;" class="form-control  selectOldObservacionFile" value="rewerwer" autocomplete="off">'+datosDetalleArchivos[index3].txt_descripcion+'</textarea>';

			        observacion = generateItem('form',{name:"flieCLC"+index3},textareaObs);
			        cols.push(generateItem('td', {
			            style: 'w300p'
			        }, observacion));

			        quitar = generateItem('span', {class:'quitarArchivos glyphicon glyphicon-remove btn btn-xs bgc8',style:'color:#fff', onclick:'fnMensajeConfirmacion('+datosDetalleArchivos[index3].idFile+');'},generateItem('input',{type:'hidden',val:datosDetalleArchivos[index3].idFile}));
			        cols.push(generateItem('td', {
			            style: estilo
			        }, quitar));
			        
			        tr = generateItem('tr', {
			        class: 'text-center w100p',
			        id:'row'+datosDetalleArchivos[index3].idFile
			        }, cols);

			       $("#tablaDetallesArchivos").find('tbody').append(tr);
			       archivos++;

    			}

    			if(archivos >0){
    				$('#muestraAntesdeEnviar').show();
    			}
	        }
	    }
	});
}

/**
 * Función para deshabilitar pagina
 * @return {[type]} [description]
 */
function fnDeshabilitaPagSuficiencia() {
	// Se va autorizar y deshabilitar pagina
	if (autorizarGeneral == 1) {
		fnEstatusCampos();
		$('#selectRazonSocial').multiselect('disable');
		$('#selectUnidadNegocio').multiselect('disable');
		$('#selectUnidadEjecutora').multiselect('disable');

		$('#selectTipo').multiselect('disable');

		$("#txtJustificacion").prop("disabled", true);
		$('#Guardar').remove();
    	$('.btnRemoverRow').prop('disabled', true);
    	$('#Cancelar').remove();
    	fnBloquearDivs("PanelPrincipalDetalle");
	}
}

function fnAlmacenarCaptura(estatus, msjvalidaciones="") {
	// console.log("fnAlmacenarCaptura");

	muestraCargandoGeneral();

	legalid = $('#selectRazonSocial').val();
	tagref = $('#selectUnidadNegocio').val();
	var ue = $('#selectUnidadEjecutora').val();

	if (tagref == '-1') {
		// Si no selecciono Unidad Responsable
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar UR para continuar con el proceso</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	if (ue == '-1') {
		// Si no selecciono Unidad Ejecutro
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar UE para continuar con el proceso</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	if ($('#selectTipo').val() == '-1') {
		// Si no selecciono Unidad Ejecutro
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar tipo de bien para continuar con el proceso</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	if ($('#txtJustificacion').val().trim() == '' || $('#txtJustificacion').val().trim() == null) {
		// Si esta vacío la justificación
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Agregar Justificación para continuar con el proceso</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	if (datosReducciones.length == 0) {
		// Agregar claves presupuestales
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Agregar bienes patrimoniales para continuar con el proceso</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	var  opcion = 'guardarOperacion';

	if(transno!=""){
		opcion='modificarOperacion';
	}

	dataObj = { 
	        option: opcion,
	        datosCaptura: datosReducciones,
			type: '1003',
			tagref: tagref,
			estatus: statusGuardar,
			fechaCaptura: $('#txtFechaCaptura').val(),
			justificacion: $('#txtJustificacion').val(),
			ue: ue,
			selectTipoBien: $('#selectTipo').val(),
			selectDisposicion: $('#selectDisposicionFinal').val(),
			folioBaja: $('#txtFolioBaja').val(),
			folio: transno
	      };
	//Obtener datos de las bahias
	$.ajax({
		  async:false,
		  cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/disposicionPatrimonialModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	ocultaCargandoGeneral();
	    	transno = data.contenido.datos.transno;
	    	type = data.contenido.type;

			$('#txtNoCaptura').empty();
			$('#txtNoCaptura').append(data.contenido.datos.transno);

			$('#txtEstatus').empty();
			if (data.contenido.datos.statusname != "" || data.contenido.datos.statusname != null) {
				$('#txtEstatus').append(""+data.contenido.datos.statusname);
			}

			$('#selectUnidadNegocio').multiselect('disable');
	    	$('#selectUnidadEjecutora').multiselect('disable');
			$('#selectTipo').multiselect('disable');
			fnGuardarArchivos(transno);
			fnObtenerArchivos(transno);
			//$('#cargarMultiples').val('');
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	    	muestraModalGeneral(3, titulo, data.Mensaje);
	    }else{
	    	//Obtener Datos de un No. Captura
			ocultaCargandoGeneral();
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		    muestraModalGeneral(3, titulo, data.Mensaje);
	    }
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
		console.log("ERROR");
	    console.log( result );
	});
}

function fnGuardarArchivos(transno){
	var fd = new FormData();

	fd.append('transno',transno);
	fd.append('option','guardarArchivos');
	fd.append('numObservacionesOldFile',$("#PanelAddarchivo").find(".selectOldObservacionFile").length);

	contadorInput=1;

	$("#PanelAddarchivo").find(".selectNewObservacionFile").each(function(index){
		fd.append('observacionFile'+index,$(this).val());
	});


	$("#PanelAddarchivo").find(".selectOldObservacionFile").each(function(index){
		fd.append('observacionOldFile'+index,$(this).val());
		fd.append('OldIdFile'+index,$(this).data('idfile'));
	});	


	if(($("#cargarMultiples").length) &&($("#cargarMultiples").val()!='') ){
	    var nFile = document.getElementById('cargarMultiples').files.length;
	     if(nFile>0){
	        for (var x = 0; x < nFile; x++) {
	            fd.append("archivos[]", document.getElementById('cargarMultiples').files[x]);
	        }
	    }
    }

	$.ajax({
	    async:false,
	    url:"modelo/disposicionPatrimonialModelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	console.log('wer');
	    	$('#cargarMultiples').val('');
	    	if(data.result){
	    		return true;
	    		
	    	}
	    }
	}); 	
}

function fnMensajeConfirmacion(idArchivo){
	muestraModalGeneralConfirmacion(3,tituloGeneral,'¿Desea eliminar el archivo?','','fnRemoverPartida('+idArchivo+')');
}

function fnRemoverPartida(idArchivo){
	var fd = new FormData();
	fd.append("option","removerArchivo");
	fd.append("idArchivo",idArchivo);

	$.ajax({
	    async:false,
	    url:"modelo/solicitudMinistracionModelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	if(data.result){
	    		var parent = document.getElementById('row'+idArchivo).parentNode;
        		parent.removeChild(document.getElementById('row'+idArchivo));
	    		muestraModalGeneral(3,tituloGeneral,data.Mensaje);
	        }
	    }
	}); 
}

/**
 * Función para confirmar antes de eliminar una clave presupuestal de la captura
 * @param  {[type]} clave           Clave presupuestal
 * @param  {[type]} panel           Panel de la informacion
 * @param  {Number} sinConfirmacion Variable para confirmación
 * @return {[type]}                 [description]
 */
function fnPresupuestoEliminar(clave, panel, sinConfirmacion=0, numLineaEliminar = 0) {
	// console.log("clave: "+clave);
	// console.log("numLineaEliminar: "+numLineaEliminar);
	
	if (sinConfirmacion == 0) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>Se va a eliminar el renglón '+numLineaEliminar+'</p>';
		muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnPresupuestoEliminar(\''+clave+'\',\''+panel+'\',\'1\', \''+numLineaEliminar+'\')');
		return false;
	}

	var numLinea = 1;
	for (var key in datosReducciones ) {
		if (datosReducciones[key].noReg == clave && Number(numLinea) == Number(numLineaEliminar)) {
			datosReducciones.splice(key, 1);
			break;
		}

		numLinea ++;
	}

	fnRecargarDatosPaneles();
}

/**
 * Función para mostrar información de las claves presupuestales
 * @param  {[type]} dataJson Json con informacón
 * @param  {[type]} idTabla  id de la tabla a mostrar información
 * @param  {[type]} panel    Panel a mostrar la información
 * @return {[type]}          [description]
 */
function fnMostrarPresupuesto(dataJson, idTabla, panel) {
	var encabezado = '';
	var contenido = '';
	var enca = 1;
	var style = 'style="text-align:center;"';
	var nombreSelect = "";
	var tipoAfectacion = "";
	var clavePresupuesto = "";
	
	if (idClavePresupuestoReducciones == 0) {
		enca = 0;
	}

	if (enca == 0) {
		encabezado += '<tr class="header-verde"><td></td><td></td>';
	}

	contenido += '<td '+style+'>'+numLineaReducciones+'</td>';

	if (autorizarGeneral == 1) {
		contenido += '<td></td>';
	} else {
		contenido += '<td '+style+'><button class="glyphicon glyphicon-remove btn-xs btn-danger btnRemoverRow" onclick="fnPresupuestoEliminar(\''+dataJson.noReg+'\', \''+panel+'\', \'0\', \''+numLineaReducciones+'\')"></button></td>';
	}

	if (enca == 0) {
		encabezado += '<td '+style+'>UR</td>';
		encabezado += '<td '+style+'>UE</td>';
		encabezado += '<td '+style+'>Descripción</td>';
		encabezado += '<td '+style+'>No. de Inventario</td>';
		encabezado += '<td '+style+'>Tipo de Bien</td>';
		encabezado += '<td '+style+'>Estatus</td>';
		encabezado += '<td '+style+'>Valor</td>';
		encabezado += '</tr>';
	}

	contenido += '<td '+style+'>'+dataJson.ur+'</td>';
	contenido += '<td '+style+'>'+dataJson.ue+'</td>';
	contenido += '<td '+style+'>'+dataJson.descripcion+'</td>';
	contenido += '<td '+style+'>'+dataJson.noInventario+'</td>';
	contenido += '<td '+style+'>'+dataJson.tipoName+'</td>';
	contenido += '<td '+style+'>'+dataJson.estatus+'</td>';
	contenido += '<td '+style+'>$ '+formatoComas( redondeaDecimal( dataJson.costo ) )+'</td>';

	contenido = encabezado + '<tr id="RenglonTR_'+dataJson.noReg+'_'+panel+'_'+numLineaReducciones+'" name="RenglonTR_'+dataJson.noReg+'_'+panel+'_'+numLineaReducciones+'" >' + contenido + '</tr>';

	if (enca == 0) {
		idClavePresupuestoReducciones = 1;
	}

	numLineaReducciones = parseFloat(numLineaReducciones) + 1;

	$('#'+idTabla+' tbody').append(contenido);

	fnEjecutarVueGeneral('RenglonTR_'+dataJson.noReg+'_'+panel+'_'+numLineaReducciones);
}

/**
 * Función si cambia la dependencia solo cargue las UR de esa dependencia
 * @param  {[type]} nomRazonSocial   id del select de dependencia
 * @param  {[type]} nomUnidadNegocio id del select del ur
 * @return {[type]}                  [description]
 */
function fnCambioRazonSocial(nomRazonSocial, nomUnidadNegocio) {
	//console.log("fnObtenerUnidadNegocio");
	// Inicio Unidad de Negocio
	legalid = $("#"+nomRazonSocial).val();
	//Opcion para operacion
	dataObj = { 
	      option: 'mostrarUnidadNegocio',
	      legalid: legalid
	    };

	fnSelectGeneralDatosAjax('#'+nomUnidadNegocio, dataObj, 'modelo/disposicionPatrimonialModelo.php');
}

/**
 * Función para recargar información de las claves presupuestales
 * @return {[type]} [description]
 */
function fnRecargarDatosPaneles() {
	//console.log("///// fnRecargarDatosPaneles /////");
	$('#'+tablaReducciones+' tbody').empty();

	// Numero de linea
	numLineaReducciones = 1;

	// Id de clave para encabezado
	idClavePresupuestoReducciones = 0;

	for (var key in datosReducciones ) {
		fnMostrarPresupuesto(datosReducciones[key], tablaReducciones, panelReducciones);
	}

	if (datosReducciones.length == 0) {
		$('#selectUnidadNegocio').multiselect('enable');
	    $('#selectUnidadEjecutora').multiselect('enable');
		$('#selectTipo').multiselect('enable');
	}
}

/**
 * Función para cargar información en los botones de las operaciones
 * @param  {[type]} divMostrar id del dov a mostrar la información
 * @return {[type]}            [description]
 */
function fnObtenerBotones(divMostrar) {
	//Opcion para operacion
	var verDatos = 0;
	if (autorizarGeneral == 1 && permisoEditarEstCapturado == 0) {
		verDatos = 1;
	}
	dataObj = { 
	        option: 'obtenerBotones',
	        autorizarGeneral: verDatos,
	        soloActFoliosAutorizada: soloActFoliosAutorizada
	      };
	//Obtener datos de las bahias
	$.ajax({
		  async:false,
          cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/disposicionPatrimonialModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datos;
	    	//console.log("botones: "+JSON.stringify(info));
	    	var contenido = '';
	    	for (var key in info) {
	    		var funciones = '';
	    		if (info[key].statusid == 1) {
	    			statusGuardar = info[key].statusid;
	    			funciones = 'fnAlmacenarCaptura('+statusGuardar+')';
	    		}
	    		if (info[key].statusid == 0) {
	    			statusGuardar = info[key].statusid;
	    			funciones = 'fnRegresarPanel()';
	    		}
	    		contenido += '&nbsp;&nbsp;&nbsp; \
	    		<component-button id="'+info[key].namebutton+'" name="'+info[key].namebutton+'" value="'+info[key].namebutton+'" onclick="'+funciones+'" class="'+info[key].clases+'"></component-button>';
	    	}
	    	$('#'+divMostrar).append(contenido);
	    	fnEjecutarVueGeneral('divBotones');
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}

/**
 * Función para regresar al panel
 * @return {[type]} [description]
 */
function fnRegresarPanel() {
	// Al cancelar regresar al panel
	var Link_Panel = document.getElementById("linkPanelAdecuaciones");
	Link_Panel.click();
}


function fnObtenerOption(componenteSelect, intComillas = 0){
	var valores = "";
	var comillas="'";
    var select = document.getElementById(''+componenteSelect);

    for ( var i = 0; i < select.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (select.selectedOptions[i].value != '-1') {

        	//intComillas = 1 No agregar las comillas
        	if(intComillas == 1){
        		comillas="";
            }

            // Que no se opcion por default
            if (i == 0) {
                valores = ""+comillas+select.selectedOptions[i].value+comillas+"";
            }else{
                valores = valores+","+comillas+select.selectedOptions[i].value+comillas+"";
            }
        }
    }

    return valores;
}

