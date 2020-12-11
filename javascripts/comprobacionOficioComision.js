$(document).ready(function() {
	$('#observacionesHTML').css({ resize:'none','width': '100%'}).attr('readonly', true);;
    $('#observacionesHTML').parent('div').removeClass('input-group');

	// llamada de las funciones de inicio del programa
	fnIniciarVariables();
	fnObtenerInformacionGeneral(window.folio);

	// comportamiento de la pre carga de archivos
	$('#inpt-upload').on('change',fnMuestraArchivosFiscales);
	$('#inpt-no-fiscal').on('change',fnMuestraArchivosNoFiscales);

	// comportamiento de la seleccion de archivos
	$('#label-upload').on('click',cleanTblFiles);
	$('#label-not-upload').on('click',cleanTblFilesNoFiscal);

	// comportamiento de carga final de archivos
	$('#btn-upload').on('click',fnSubirArchivosFiscales);
	$('#btn-no-upload').on('click',fnPrevioNoFiscal);

	// comportamiento del boton de regreso al panel
    $('#regresar').on('click', function() { window.location.href = window.url+'/viaticos.php'; });

    // comportamiento de comentarios
    $(document).on('cellselect','#files-fiscales, #files-no-fiscales',function(e){
    	var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
    	var tipo = currentTarget.id == 'files-fiscales'?1:2;
    	// si no pertenece a la columna de observaciones no se realiza acción
    	if(campo != 'observacionesHTML'){ return; }
    	// comprobacion de premisos
    	if(!mia){ return; }
    	// se colecta la información
    	var linea = $(this).jqxGrid('getrowdata', index), forma='', pie='';
    	forma += '<component-textarea-label label="Comentario:" name="coment" id="'+index+'" row="3"></component-textarea-label>';
    	pie = $('<button>',{
    		html:'Aceptar', class:'btn botonVerde', 'data-dismiss':'modal',
    		click : function(){ guardarComentario(linea, getParams('ModalGeneral_Mensaje'),tipo); }
    	});
    	muestraModalGeneralConfirmacion(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Agregar comentario</p></h3>', forma, pie);
    	fnEjecutarVueGeneral('ModalGeneral_Mensaje');
    })
    // comportamiento de captura de monto comprobado
    .on('cellselect','#files-fiscales, #files-no-fiscales',function(e){
    	var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
    	var tipo = currentTarget.id == 'files-fiscales'?1:2;
    	// si no pertenece a la columna de observaciones no se realiza acción
    	if(campo != 'montoComp'){ return; }
    	// comprobacion de premisos
    	if(!mia){ return; }
    	// se colecta la información
    	var linea = $(this).jqxGrid('getrowdata', index), forma='', $pie='', datafield = tipo==1?'montoFac':'montoCap';
    	forma += '<component-decimales-label label="Monto:" name="monto" id="'+index+'" value="'+linea.montoComp+'"></component-decimales-label>';
    	forma += '<div class="hidden"><input type="text" id="realvalue" name="realvalue" value="'+linea[datafield]+'" /></div>';
    	forma += '<div class="hidden"><input type="text" id="prevValue" name="prevValue" value="'+linea.montoComp+'" /></div>';
    	$pie = $('<button>',{
    		html:'Aceptar', class:'btn botonVerde', 'data-dismiss':'modal',
    		click : function(){
    			var params = getParams('ModalGeneral_Mensaje');
    			var diferenciaPrevAct = parseFloat(params.monto)-parseFloat(params.prevValue);
    			fnComprobarPorcentaje(diferenciaPrevAct.toString());
    			if(parseFloat(params.monto) > parseFloat(params.realvalue)){
    				muestraModalGeneral(3,window.tituloGeneral,'La cantidad a comprobar no puede ser mayor que el monto capturado inicialmente ($'+params.realvalue+").");
    				return false;
    			}
    			//// Se elimina comprobación que evita que se exceda el monto total de la Comisión
    			/*if(resultadoComprobacion.excede){
    				muestraModalGeneral(3,window.tituloGeneral,resultadoComprobacion.mensaje);
    				return false;
    			}*/
    			guardaMontoComprobado(linea, params,tipo);
    		}
    	});
    	muestraModalGeneralConfirmacion(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Comprobación de Monto</p></h3>', forma, $pie);
    	fnEjecutarVueGeneral('ModalGeneral_Mensaje');
    })
    // comportamiento de generación y descarga de XML
    .on('cellselect','#files-fiscales',function(e){
    	var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
    	if(campo != 'xml'){ return false; }
    	var row = $(this).jqxGrid('getrowdata', index);
		dataObj = { 
			method: 'descargaXML',
			idDocumento: row.id
		};
		$.ajaxSetup({async: false});
		$.post(modelo, dataObj)
		.then(function(res){
			var titulo = window.tituloGeneral;
			if(res.success){
				var blob = new Blob([res.xml], { type: 'application/xml; charset=utf-8' });
				var link = document.createElement('a');
				link.href = window.URL.createObjectURL(blob);
				link.download = res.archivo;

				document.body.appendChild(link);

				link.click();

				document.body.removeChild(link);
			}else{
				muestraModalGeneral(3,titulo,res.msg);
				$('.modal-backdrop.fade').removeClass('modal-backdrop in');
			}
		});
    })
    // comportamiento de eliminación de registros
    .on('cellselect','#files-fiscales, #files-no-fiscales',function(e){
    	var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
    	if(campo != 'eliminar'){ return false; }
    	var row = $(this).jqxGrid('getrowdata', index),
    		tipoDelete = "";
    	tipoDelete = ( this.id=="files-fiscales" ? "eliminaFiscales" : tipoDelete );
    	tipoDelete = ( this.id=="files-no-fiscales" ? "eliminaNoFiscales" : tipoDelete );
    	if(tipoDelete==""){ return false; }
    	var	factura = ( row.factura ? " "+row.factura+" -" : "" )+" "+row.concepto;
    	if(row.montoComp>0){
    		muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', "La factura <strong>"+factura+"</strong> no puede eliminarse mientras el Monto Comprobado sea distinto a cero.");
    	}else{
    		muestraModalGeneralConfirmacion(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', "¿Está seguro de que desea eliminar la factura <strong>"+factura+"</strong>?",'','fnEliminarDocumentos','\''+row.id+'\',\''+tipoDelete+'\'');
    	}
    })
	// comportamiento de la eliminación de un archivo para su carga
	.on('click','.rm-file',function(){
		var $linea = $(this).parents('tr[id]').eq(0), $spanContent = $('<span>'), $tabla = $(this).parents('table[id]'),
			$btnCancel = $('<button>',{ class:'btn botonVerde',html:'Cancelar','data-dismiss':'modal' });
		var $pie = $('<button>',{ class:'btn botonVerde',html:'Aceptar','data-dismiss':'modal',click:function(){
				var id = $linea.attr('id'), idTabla = $tabla.attr('id');
				$linea.remove();
				if(idTabla == 'tbl-filesToUp'){ delete(window.filesRoot[id]); }
				else if(idTabla == 'tbl-filesToUpNoFiscal'){ delete(window.filesNoFiscalesRoot[id]); }
			}});
		$spanContent.append($pie).append($btnCancel);
		muestraModalGeneralConfirmacion(3,window.tituloGeneral,'¿Realmente desea retirar el documento?',$spanContent);
	});

	window.porcentajeAComprobar = 0.9;
	window.resultadoComprobacion = { esValido: false, excede: false, mensaje: "" };

    $('#observaciones').css({
        resize: 'none',
        'width': '100%'
    });
    $('#observaciones').parent('div').removeClass('input-group');
    fnAgregarItineario();
    $("#observaciones").attr('readonly', true);
});

function fnEliminarDocumentos(parametros){
    //Opcion para operacion
	dataObj = { 
		method: "eliminarFacturas",
		idDocumento: parametros[0],
		tipoDelete: parametros[1]
	};
	$.ajaxSetup({async: false});
	$.post(modelo, dataObj)
	.then(function(res){
		var titulo = window.tituloGeneral;
		if(res.success){
			titulo = 'Operación exitosa';
			fnObtenerInformacionGeneral(folio);
		}
		muestraModalGeneral(3,titulo,res.msg);
		$('.modal-backdrop.fade').removeClass('modal-backdrop in');
	});
    /*dataObj = {
        option: 'cambiarEstatusCuenta',
        tipoAlta: parametros[0],
        cuenta: parametros[1],
        multiNivel: parametros[2],
        tipoAccion: 0
    };
    $.ajax({
        async: false,
        cache: false,
        method: "POST",
        dataType: "json",
        url: modelo,
        data: dataObj
    })
    .done(function (data) {
        //console.log("Bien");
        $("#ModalGeneralTam").width('600');
        muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', data.Mensaje);
    })
    .fail(function (result) {
        // console.log("ERROR");
        // console.log( result );
    });*/
}

// sobre escritura
function fnCambiarEstatus() {}

function fnIniciarVariables() {
	console.log('listo comprobación');
	window.modelo = 'modelo/comprobacionOficioComisionModelo.php';
	window.panel = 'viaticos.php';
	window.camposFormaInfo = ["empleado","destino","observaciones","dateDesde","dateHasta",'montoTotal','montoComp'];
	window.filesRoot = [];
	window.filesNoFiscalesRoot = [];
	window.noPermitido = [];
	window.itinerario = [];
	window.mia = false;
	window.tituloGeneral = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

	var url = window.location.href.split('/');
    url.splice(url.length - 1);
    window.url = url.join('/');

	// datos de los botones
	// fnObtenerBotones_Funcion('areaBotones',nuFuncion);
 //    $(document).on('click','#Rechazar, #Avanzar, #Autorizar, #Cancelar',changeNewStatus);
    // se modifica el tamaño de la modal para que siga jalando
    $('#ModalGeneral_Mensaje').css({ 'max-height':'500px', 'overflow-y': 'auto' });

	window.propiedadesResize = {
									widthsEspecificos: {
										xml:		"3%",
										pdf:		"3%",
										eliminar:	"7%"
									},
									encabezadosADosRenglones: {
										fecha:			"Fecha<br />Emisión",
										montoFac:		"Monto<br />Facturado",
										montoCap:		"Monto<br />Capturado",
										montoComp:		"Monto<br />Comprobado"
									},
									camposConWidthAdicional: [
										"concepto",
										"observacionesHTML"
									],
									camposConWidthAIgualar: {
										observaciones:	"observacionesHTML"
									}
								};
}

function fnObtenerInformacionGeneral(folio) {
	if(!$.isEmptyObject(identificador)){
		// if(!$.isEmptyObject(folio) && !$.isEmptyObject(ur) && !$.isEmptyObject(identificador)){
		var parametros = {
			method:'obtenInformacionGeneral',
			identificador:identificador
			// folio:folio,
			// ur:ur,
		};
		// envio de solicitud a servidor
		$.ajaxSetup({async: false});
		$.post(modelo, parametros)
		.then(function(res){
			if(res.success){
				var content = res.content;
				$.each(camposFormaInfo, function(index, val) {
					if(content.hasOwnProperty(val)){ $('#'+val).val(content[val]); }
				});
				// llenado de las diferentes tablas
				fnLlenaTablaFiscales('files-fiscales',res.fiscales);
				fnLlenaTablaNoFiscales('files-no-fiscales', res.nofiscales);
			}
			window.perfil = res.perfil;
			window.noPermitido = res.noPermitido;
			window.itinerario = res.itinerario;
			window.mia = res.mia?true:
				(perfil==11)?true
				:(perfil==10 && (estatusSolicitud == 9||estatusSolicitud == 5))?true
				:(perfil==9 && estatusSolicitud == 5)?true:false;
			// comrpobacion de permisos y pertenecia sobre la solicitud
			if(!mia){
				$('.contenedorBotonesCarga').each(function(index, el) { $(el).addClass('hidden'); });
			}

			if(noPermitido.indexOf(estatusSolicitud)===-1){
				$('#areaBotones').addClass('hidden');
			}
		})
		.fail(function() {
			var pie = $('<button>',{class:'btn btn-danger', html:'Aceptar', click:function(){ window.location.href = window.panel; }});
			muestraModalGeneral(3,window.tituloGeneral,'Ocurrio un incidente inesperado.',pie);
		});

	}
	else{
		var pie = $('<button>',{class:'btn btn-danger', html:'Aceptar', click:function(){ window.location.href = window.panel; }});
		muestraModalGeneral(3,window.tituloGeneral,'Ocurrió un incidente inesperado.',pie);
	}
}

function fnLlenaTablaFiscales(el,datos=[]) {
	var content = 'content-files', visules=[0,1,2,5,7,8,9], excel=[0,1,2,5,7,8,9], nombreExcel='Documentos Fiscales '+window.folio, calculoCapturado = {};
	// contenedor de datos
	datafields = [
		{name:'id',type:'number'}, // 0
		{name:'factura',type:'string'}, // 2
		{name:'fecha',type:'string'}, // 3
		{name:'concepto',type:'string'}, // 4
		{name:'xml',type:'string'}, // 5
		{name:'pdf',type:'string'}, // 6
		{name:'estatus',type:'string'}, // 7
		{name:'observaciones',type:'string'}, // 8
		{name:'montoFac',type:'number'}, // 9
		{name:'montoComp',type:'number'}, // 10
		{name:'acciones',type:'string'}, // 11
		{name:'observacionesHTML',type:'string'}, // 12
		{name:'eliminar',type:'string'}, // 13
	];
	// definicion de calculos
	calculoCapturado = { '<b>Total</b>' : function (aggregatedValue, currentValue) {
		var total = currentValue;
		return aggregatedValue + total;
		}
	};
	// titulos y estilos de las columnas
	columns = [
		//{ text: 'Nº', datafield: 'id', editable: false, width: '5%', cellsalign: 'center', align: 'center' },
		{ text: 'Fecha Emisión', datafield: 'fecha', editable: false, width: '15%', cellsalign: 'center', align: 'center' },
		{ text: 'Factura', datafield: 'factura', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
		{ text: 'Concepto', datafield: 'concepto', editable: false, width: '30%', cellsalign: 'center', align: 'center' },
		{ text: 'XML', datafield: 'xml', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
		{ text: 'PDF', datafield: 'pdf', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
		{ text: 'Estatus', datafield: 'estatus', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
		{ text: 'Observaciones', datafield: 'observacionesHTML', editable: false, width: '30%', cellsalign: 'center', align: 'center' },
		{ text: 'Observaciones', datafield: 'observaciones', editable: false, width: '30%', cellsalign: 'center', align: 'center', hidden: true },
		{ text: 'Monto Factura', datafield: 'montoFac', editable: false, width: '15%', cellsalign: 'center', align: 'center', cellsformat: 'C2', aggregates: [calculoCapturado] },
		{ text: 'Monto Comprobado', datafield: 'montoComp', editable: false, width: '15%', cellsalign: 'center', align: 'center', cellsformat: 'C2', aggregates: [calculoCapturado] },
		{ text: 'Eliminar', datafield: 'eliminar', editable: false, width: '7%', cellsalign: 'center', align: 'center' },
		// { text: 'Acciones', datafield: 'acciones', editable: false, width: '5%', cellsalign: 'center', align: 'center' },
	];
	// llamado de limpiesa de la tabla
	fnLimpiarTabla(content,el);
	// renderisado de la tabla
	fnAgregarGrid_Detalle_nostring(datos, datafields, columns, el, ' ', 1, excel, false, true, "", visules, nombreExcel);

	// resize del ancho de las columnas del Grid cada vez que hay cambio de página
	$("#"+el).bind("pagechanged", function (event) {
		if(typeof window.propiedadesResize != "undefined"){
			fnGridResize(el,window.propiedadesResize);
		}
	});
	if(typeof window.propiedadesResize != "undefined"){
		fnGridResize(el,window.propiedadesResize);
	}
}

function fnLlenaTablaNoFiscales(el,datos=[]) {
	var content = 'content-noFiscales', visules=[0,1,2,4,6,7], excel=[0,1,2,4,6,7], nombreExcel='Documentos No Fiscales'+window.folio, calculoCapturado = {}, calculoComprobado = {};
	// contenedor de datos
	datafields = [
		{name:'id',type:'number'}, // 0
		{name:'fecha',type:'string'}, // 3
		{name:'concepto',type:'string'}, // 4
		{name:'estatus',type:'string'}, // 7
		{name:'observaciones',type:'string'}, // 8
		{name:'archivos',type:'string'}, // 8
		{name:'montoCap',type:'number'}, // 9
		{name:'montoComp',type:'number'}, // 10
		{name:'acciones',type:'string'}, // 11
		{name:'observacionesHTML',type:'string'}, // 12
		{name:'eliminar',type:'string'}, // 13
	];

	// definicion de calculos
	calculoCapturado = { '<b>Total</b>' : function (aggregatedValue, currentValue) {
		var total = currentValue;
		return aggregatedValue + total;
		}
	};
	// calculoComprobado = { '<b>Total</b>' : function (aggregatedValue, currentValue) {
	// 	var total = currentValue;
	// 	return aggregatedValue + total;
	// 	}
	// };

	// titulos y estilos de las columnas
	columns = [
		//{ text: 'Nº', datafield: 'id', editable: false, width: '5%', cellsalign: 'center', align: 'center' },
		{ text: 'Fecha Emisión', datafield: 'fecha', editable: false, width: '15%', cellsalign: 'center', align: 'center' },
		{ text: 'Concepto', datafield: 'concepto', editable: false, width: '30%', cellsalign: 'center', align: 'center' },
		{ text: 'Estatus', datafield: 'estatus', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
		{ text: 'Observaciones', datafield: 'observacionesHTML', editable: false, width: '30%', cellsalign: 'center', align: 'center' },
		{ text: 'Observaciones', datafield: 'observaciones', editable: false, width: '30%', cellsalign: 'center', align: 'center', hidden: true },
		{ text: 'Archivos', datafield: 'archivos', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
		{ text: 'Monto Capturado', datafield: 'montoCap', editable: false, width: '15%', cellsalign: 'center', align: 'center', cellsformat: 'C2', aggregates: [calculoCapturado] },
		{ text: 'Monto Comprobado', datafield: 'montoComp', editable: false, width: '15%', cellsalign: 'center', align: 'center', cellsformat: 'C2', aggregates: [calculoCapturado] },
		{ text: 'Eliminar', datafield: 'eliminar', editable: false, width: '7%', cellsalign: 'center', align: 'center' },
		// { text: 'Acciones', datafield: 'acciones', editable: false, width: '6%', cellsalign: 'center', align: 'center' },
	];
	// llamado de limpiesa de la tabla
	fnLimpiarTabla(content,el);
	// renderisado de la tabla
	fnAgregarGrid_Detalle_nostring(datos, datafields, columns, el, ' ', 1, excel, false, true, "", visules, nombreExcel);

	// resize del ancho de las columnas del Grid cada vez que hay cambio de página
	$("#"+el).bind("pagechanged", function (event) {
		if(typeof window.propiedadesResize != "undefined"){
			fnGridResize(el,window.propiedadesResize);
		}
	});
	if(typeof window.propiedadesResize != "undefined"){
		fnGridResize(el,window.propiedadesResize);
	}
}

function guardarComentario(linea, params, tipo=1) {
	if(params.coment == ''){
		muestraModalGeneral(3,window.tituloGeneral,'Es necesario colocar un comentario');
		return false;
	}
	muestraCargandoGeneral();
	params.method = 'guardarComentario';
	params.solicitud = identificador;
	params.identificador = linea.id;
	params.tipo = tipo;
	$.ajaxSetup({async: false});
	$.post(modelo, params)
	.then(function(res){
		ocultaCargandoGeneral();
		var titulo = window.tituloGeneral;
		if(res.success){
			titulo = 'Operación exitosa';
			// llena la tabla correspondiente reflejando el nuevo comentario
			if(tipo==1){ fnLlenaTablaFiscales('files-fiscales',res.content); }
			else{ fnLlenaTablaNoFiscales('files-no-fiscales',res.content); }
		}
		muestraModalGeneral(3,window.tituloGeneral,res.msg);
		$('.modal-backdrop.fade').removeClass('modal-backdrop in');
	});
}

function guardaMontoComprobado(linea, params, tipo) {
	if(params.monto == ''){
		muestraModalGeneral(3,window.tituloGeneral,'Es necesario colocar un monto, como mínimo 0');
		return false;
	}
	muestraCargandoGeneral();
	params.method = 'guardarMontoComprobado';
	params.solicitud = identificador;
	params.identificador = linea.id;
	params.tipo = tipo;
	$.ajaxSetup({async: false});
	$.post(modelo, params)
	.then(function(res){
		ocultaCargandoGeneral();
		var titulo = window.tituloGeneral;
		if(res.success){
			titulo = 'Operación exitosa';
			// llena la tabla correspondiente reflejando el nuevo comentario
			if(tipo==1){ fnLlenaTablaFiscales('files-fiscales',res.content); }
			else{ fnLlenaTablaNoFiscales('files-no-fiscales',res.content); }
			$('#montoComp').val(res.montoComp);
			fnComprobarPorcentaje();
		}
		muestraModalGeneral(3,window.tituloGeneral,res.msg+( resultadoComprobacion.mensaje ? "<br>"+resultadoComprobacion.mensaje : "" ));
		$('.modal-backdrop.fade').removeClass('modal-backdrop in');
	});
}

/**************************************** FUNCIONES DE ARCHIVOS FISCALES ****************************************/

function fnMuestraArchivosFiscales() {
	var $el = $(this), $tbl = $('#tbl-filesToUp'), len = 0, files = [], $tbody;
		files = $el.prop('files');
		len = files.length;
		$tbody = $tbl.find('tbody');
	$('#showFiles').removeClass('hidden');
	if(len){
		// agregado a variable global
		var flag = 0, msg = 'Los siguientes documentos no cumplen con el formato. <ul>';
		$.each(files, function(index, val) {
			var aceptedType = ['text/xml', 'application/zip', 'application/x-zip', 'application/octet-stream', 'application/x-zip-compressed'], type = val.type;
			// if(aceptedType.indexOf(val.type)!==-1){ type = 'xml'; }
			if(aceptedType.indexOf(val.type)===-1){
				flag++;
				msg += '<li>'+val.name+'</li>';
			}else{
				filesRoot[index] = val;
				var row = [
					generateItem('td',{html:val.name}),
					generateItem('td',{html:val.size}),
					generateItem('td',{html:type}),
					generateItem('span',{class:'btn btn-sm btn-primary bgc8 rm-file'},generateItem('span',{class:'glyphicon glyphicon-remove'}))
				];
				$tbody.append(generateItem('tr',{id:index},row));
			}
		});
		msg += '</ul>';
		if(flag!=0){
			muestraModalGeneral(3,window.tituloGeneral,msg);
		}
	}
}

function cleanTblFiles(){
	var $tbl = $('#tbl-filesToUp'), $tbody;
	$tbody = $tbl.find('tbody');
	$tbody.html('');
	window.filesRoot = [];
}

function fnSubirArchivosFiscales() {
	var $imptFiles = $('#inpt-upload'), forma = new FormData(), files;
	// files  = $imptFiles.prop('files');
	files  = window.filesRoot;
	if(!files.length){
		muestraModalGeneral(3,window.tituloGeneral,'Es necesario selección algún documento fiscal para realizar la carga.');
		return;
	}
	muestraCargandoGeneral();
	// carga de la informacion de la solictud
	forma.append('identificador',identificador);
	forma.append('folio', folio);
	forma.append('method', 'updateFilesFiscales');
	// carga de archivos
	$.each(files, function(index, val) { forma.append('archivos[]',val); });
	$.ajax({
		url: modelo,
		dataType: 'json',
		cache: false,
		contentType: false,
		processData: false,
		data: forma,
		type: 'post',
		async: false
	})
	.then(function(res){
		ocultaCargandoGeneral();
		var titulo = window.tituloGeneral;
		if(res.success){
			titulo = 'Operación exitosa';
			fnLlenaTablaFiscales('files-fiscales', res.content);
			cleanTblFiles();
			fnObtenerInformacionGeneral(folio);
		}
		$('.modal-backdrop.fade').removeClass('modal-backdrop in');
		muestraModalGeneral(3,window.tituloGeneral,res.msg);
		$('.modal-backdrop.fade').removeClass('modal-backdrop in');
	});
}

/**************************************** FUNCIONES DE ARCHIVOS NO FISCALES ****************************************/

function fnMuestraArchivosNoFiscales() {
	var $el = $(this), $tbl = $('#tbl-filesToUpNoFiscal'), len = 0, files = [], $tbody;
		files = $el.prop('files');
		len = files.length;
		$tbody = $tbl.find('tbody');
	$('#showFilesNoFiscal').removeClass('hidden');
	if(len){
		// agregado a variable global
		var flag = 0, msg = 'Los siguientes documentos no cumplen con el formato. <ul>';
		$.each(files, function(index, val) {
			var aceptedType = ['application/pdf'], type = val.type;
			// if(aceptedType.indexOf(val.type)!==-1){ type = 'xlsx'; }
			if(aceptedType.indexOf(val.type)===-1){
				flag++;
				msg += '<li>'+val.name+'</li>';
			}else{
				// filesNoFiscalesRoot.push(val);
				filesNoFiscalesRoot[index] = val;
				var row = [
					generateItem('td',{html:val.name}),
					generateItem('td',{html:val.size}),
					generateItem('td',{html:type}),
					generateItem('span',{class:'btn btn-sm btn-primary bgc8 rm-file'},generateItem('span',{class:'glyphicon glyphicon-remove'}))
				];
				$tbody.append(generateItem('tr',{id:index},row));
			}
		});
		msg += '</ul>';
		if(flag!=0){
			muestraModalGeneral(3,window.tituloGeneral,msg);
		}
	}
}

function cleanTblFilesNoFiscal(){
	var $tbl = $('#tbl-filesToUpNoFiscal'), $tbody;
	$tbody = $tbl.find('tbody');
	$tbody.html('');
	window.filesNoFiscalesRoot = [];
}

function fnPrevioNoFiscal() {
	var pie = '<button class="btn botonVerde" data-dismiss="modal" onClick="fnValidaComprobante(getParams(&apos;form-nofiscal&apos;))">Aceptar</button>',forma='';
	forma += '<div class="row">'+
	        '<div class="col-sm-12">'+
	            '<form class="form-inline" id="form-nofiscal">'+
	            	'<div class="form-inline row">'+
                        '<div class="col-md-3" style="vertical-align: middle;">'+
                            '<span><label>Fecha Emisión: </label></span>'+
                        '</div>'+
                        '<div class="col-md-9">'+
	            			'<component-date-feriado2 label="Fecha:" id="fecha" name="fecha"  title="Fecha" class="w100p"></component-date-feriado2>'+
                        '</div>'+
                    '</div>'+
	            	'<br />'+
					'<component-text-label label="Tipo Documento:" id="tipoDoc" name="tipoDoc"></component-text-label>'+
	            	'<br />'+
					'<component-text-label label="Concepto:" id="concepto" name="concepto"></component-text-label>'+
	            	'<br />'+
	            	'<component-decimales-label label="Monto:" name="monto" id="monto"></component-decimales-label>'+
	            	'<br />'+
					'<div class="hidden">'+
                        '<input type="text" name="id_nu_solicitud" id="id_nu_viaticos" value="'+window.identificador+'">'+
	                '</div>'+
	            '</form>'+
	        '</div>'+
	    '</div>';
	muestraModalGeneral(3,'Datos del Comprobante',forma,pie);
	fnEjecutarVueGeneral('form-nofiscal');
}

function fnValidaComprobante(datos) {
	var campos = {'fecha':'Fecha','tipoDoc':'Tipo Documento','concepto':'Concepto','monto':'Monto'},
		msg = 'Se encontraron la siguientes inconsistencias: <br />', flag=0;
	// comprobación de datos
	$.each(datos, function(index, val) {
		if($.isEmptyObject(val)){
			msg += 'El campo: '+campos[index]+', se encuentra vacío.<br />';
			flag++;
		}
	});
	// envío de mensaje de error
	if(flag!=0){ muestraModalGeneral(3,window.tituloGeneral,msg); }
	// envío de datos
	else{
		$('.modal-backdrop.fade').removeClass('modal-backdrop in');
		fnSubirArchivosNoFiscales(datos);
	}
}

function fnSubirArchivosNoFiscales(datos='') {
	var forma = new FormData(), files;

	files  = window.filesNoFiscalesRoot;
	if(!files.length){
		muestraModalGeneral(3,window.tituloGeneral,'Es necesario seleccionar algún documento para realizar la carga.');
		return;
	}
	muestraCargandoGeneral();
	// carga de la informacion de la solictud
	forma.append('identificador',identificador);
	forma.append('datosComprobante',JSON.stringify(datos));
	forma.append('folio', folio);
	forma.append('method', 'updateFilesNotFiscales');
	// carga de archivos
	$.each(files, function(index, val) { forma.append('archivos[]',val); });
	$.ajax({
		url: modelo,
		dataType: 'json',
		cache: false,
		contentType: false,
		processData: false,
		data: forma,
		type: 'post',
		async: false
	})
	.then(function(res){
		ocultaCargandoGeneral();
		var titulo = window.tituloGeneral;
		if(res.success){
			titulo = 'Operación exitosa';
			cleanTblFilesNoFiscal();
			fnLlenaTablaNoFiscales('files-no-fiscales',res.content);
			fnObtenerInformacionGeneral(folio);
		}
		$('.modal-backdrop.fade').removeClass('modal-backdrop in');
		muestraModalGeneral(3,window.tituloGeneral,res.msg);
		$('.modal-backdrop.fade').removeClass('modal-backdrop in');
	});
}

function fnComprobarPorcentaje(importeAdicional=""){
	var	montoComp = Number( $('#montoComp').val().split(',').join('') ),
		montoTotal = Number( $('#montoTotal').val().split(',').join('') );

	resultadoComprobacion = { esValido: false, excede: false, mensaje: "" };
	importeAdicional = Number( importeAdicional.split(',').join('') );
	montoComp += importeAdicional;

	if($('#montoTotal').val()&&montoTotal>0){
		var porcentajeActual = (montoComp/montoTotal);
		resultadoComprobacion.esValido = ( porcentajeActual>=window.porcentajeAComprobar&&!(porcentajeActual>1) ? true : false );
		resultadoComprobacion.mensaje = "El monto comprobado aún no alcanza el porcentaje mínimo necesario ("+(porcentajeActual*100).toFixed(2)+"% de "+(window.porcentajeAComprobar*100)+"%) .";
		if(porcentajeActual>=window.porcentajeAComprobar){
			resultadoComprobacion.mensaje = ( porcentajeActual>1 ? "El monto comprobado excede el 100% del monto a comprobar." : "La comprobación de viáticos está lista para enviarse." );
			resultadoComprobacion.excede = ( porcentajeActual>1 ? true : resultadoComprobacion.excede );
		}
		if(importeAdicional!=""&&resultadoComprobacion.excede){
			//// Se elimina comprobación que evita que se exceda el monto total de la Comisión
        	//resultadoComprobacion.mensaje = "La cantidad a comprobar excede el monto total a comprobar.";
        }
    }
}

function fnAgregarItineario(){
	if(window.itinerario.length){
		var importeTotal = 0
			destino = "";

		if(window.itinerario[0].tipoCom==1){
	        $('#tipoInterArea').addClass('hidden');
	        $('#tipoNacionalArea').removeClass('hidden');
	        destino = window.itinerario[0].entidad+( window.itinerario[0].municipio ? " - " + window.itinerario[0].municipio : "" );
		}else{
	        $('#tipoNacionalArea').addClass('hidden');
	        $('#tipoInterArea').removeClass('hidden');
	        destino = window.itinerario[0].pais;
		}
		for(var c=0;c<window.itinerario.length;c++){
			var contenTipoSol = '';

			if(window.itinerario[c].tipoCom==1){
				contenTipoSol =
					'<div class="w25p fl vam h35">' + window.itinerario[c].entidad + '</div>' +
					'<div class="w25p fl vam h35">' + window.itinerario[c].municipio + '</div>';
			}else{
				contenTipoSol =
					'<div class="w50p fl vam h35">' + window.itinerario[c].pais + '</div>';
			}

		    importeLocal = Number(window.itinerario[c].importe);
		    importeLocal = importeLocal.toLocaleString(undefined, {
		        minimumFractionDigits: 2,
		        maximumFractionDigits: 2
		    });
		    importeLocal = ( importeLocal.substr(importeLocal.length-3,1)=="," ? importeLocal.split(".").join(";").split(",").join(":").split(":").join(".").split(";").join(",") : importeLocal );

			template =
				'<div class="row w100p borderGray">' +
					'<div class="col-lg-12 col-md-12 col-sm-12 p0 m0">' +
						'<div class="w3p fl vam h35 pt10 renglon">' + (c+1) + '</div>' +
						contenTipoSol +
						'<div class="w12p fl vam h35">' + window.itinerario[c].fechaIni + '</div>' +
						'<div class="w12p fl vam h35">' + window.itinerario[c].fechaFin + '</div>' +
						'<div class="w6p fl vam h35">' + window.itinerario[c].dias + '</div>' +
						'<div class="w6p fl vam h35">' + window.itinerario[c].noches + '</div>' +
						'<div class="w11p fl vam h35">' + importeLocal + '</div>' +
					'</div>' +
				'</div>';

			$('#tbl-itinerario').append(template);
			importeTotal += Number(window.itinerario[c].importe);
		}
	    importeTotal = importeTotal.toLocaleString(undefined, {
	        minimumFractionDigits: 2,
	        maximumFractionDigits: 2
	    });
	    importeTotal = ( importeTotal.substr(importeTotal.length-3,1)=="," ? importeTotal.split(".").join(";").split(",").join(":").split(":").join(".").split(";").join(",") : importeTotal );
	    $("#TotalComision").html(importeTotal);
	    $('#destino').val(destino);
	}
}

/*********************************** FUNCIONES DE CAMBIO DE ESTATUS ***********************************/
// function changeNewStatus() {
//     var el = $(this);
//     if(el.attr('id') == 'Autorizar'){
//         var noChange = [6,7];
//         if(noChange.indexOf(estatusSolicitud) === -1){
//             var titulo = 'Confirmación', mensaje = '¿Estas seguro de autorizar la solicitud '+folio+'?';
//             muestraModalGeneralConfirmacion(3,titulo,mensaje,'','autorizaGeneral()');
//         }
//     }
//     // cancelacion
//     else if(el.attr('id') == 'Cancelar'){
//         var titulo = 'Confirmación', mensaje = '¿Estas seguro de cancelar la solicitud '+folio+'?';
//         muestraModalGeneralConfirmacion(3,titulo,mensaje,'','cancelaGeneral()');
//     }
//     // rechazo
//     else if(el.attr('id') == 'Rechazar'){
//         var noChange = [5,6,7];
//         if(noChange.indexOf(estatusSolicitud) === -1){
//             var titulo = 'Confirmación', mensaje = '¿Estas seguro de rechazar la solicitud '+folio+'?';
//             muestraModalGeneralConfirmacion(3,titulo,mensaje,'','rechazaGeneral()');
//         }
//     }
//     // avnazar
//     else if(el.attr('id') == 'Avanzar'){
//         var noChange = [6,7];
//         if(noChange.indexOf(estatusSolicitud) === -1){
//             var titulo = 'Confirmación', mensaje = '¿Estas seguro de avanzar la solicitud '+folio+'?';
//             muestraModalGeneralConfirmacion(3,titulo,mensaje,'','avanzarGeneral()');
//         }
//     }
// }

// function avanzarGeneral(argument) {
// 	muestraCargandoGeneral();
// 	var estotusPerfil = perfil==9?9:(perfil==10?10:(perfil==11?6:6));
//     $.post(modelo, {method:'updateStatus', identificador : identificador, type : estotusPerfil })
//     .then(function(res){
//     	var titulo = window.tituloGeneral, mensaje = res.msg;
//         ocultaCargandoGeneral();
//         if(res.success){
//         	titulo = 'Operación Exitosa';
//         	mensaje = 'Se avanzo con éxito la solicitud '+folio+' '+(profile==9?'al Validador.':profile==10?'al Autorizador':'');
//         	$('#areaBotones').addClass('hidden');
//         }
//         muestraModalGeneral(3,titulo,mensaje,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
//         $('.modal-backdrop.fade').removeClass('modal-backdrop in');
//     });
// }

// function rechazaGeneral(argument) {
// 	muestraCargandoGeneral();
// 	if(perfil == 9){
// 		ocultaCargandoGeneral();
// 		muestraModalGeneral(3,window.tituloGeneral,'No cuenta con los permisos para rechazar al solicitud '+folio,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
//         $('.modal-backdrop.fade').removeClass('modal-backdrop in');
//         return;
// 	}
// 	var estotusPerfil = perfil==11?9:(perfil==10?5:5);
//     $.post(modelo, {method:'updateStatus', identificador : identificador, type : estotusPerfil })
//     .then(function(res){
//     	var titulo = window.tituloGeneral, mensaje = res.msg;
//         ocultaCargandoGeneral();
//         if(res.success){
//         	titulo = 'Operación Exitosa';
//         	mensaje = 'Se rechazo con éxito la solicitud '+folio+' '+(profile==9?'al Validador.':profile==10?'al Autorizador':'');
//         }
//         muestraModalGeneral(3,titulo,mensaje,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
//         $('.modal-backdrop.fade').removeClass('modal-backdrop in');
//     });
// }

// function autorizaGeneral(argument) {
// 	muestraCargandoGeneral();
//     $.post(modelo, {method:'updateStatus', identificador : identificador, type : 6 })
//     .then(function(res){
//     	var titulo = window.tituloGeneral, mensaje = res.msg;
//         ocultaCargandoGeneral();
//         if(res.success){
//         	titulo = 'Operación Exitosa';
//         	mensaje = 'Se autorizo con éxito la solicitud '+folio;
//         	$('#areaBotones').addClass('hidden');
//         }
//         muestraModalGeneral(3,titulo,mensaje,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
//         $('.modal-backdrop.fade').removeClass('modal-backdrop in');
//     });
// }

// function cancelaGeneral(argument) {
// 	muestraCargandoGeneral();
//     $.post(modelo, {method:'updateStatus', identificador : identificador, type : 7 })
//     .then(function(res){
//     	var titulo = window.tituloGeneral, mensaje = res.msg;
//         ocultaCargandoGeneral();
//         if(res.success){
//         	titulo = 'Operación Exitosa';
//         	mensaje = 'Se cancelo con éxito la solicitud '+folio;
//         	$('#areaBotones').addClass('hidden');
//         }
//         muestraModalGeneral(3,titulo,res.msg,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
//         $('.modal-backdrop.fade').removeClass('modal-backdrop in');
//     });
// }
