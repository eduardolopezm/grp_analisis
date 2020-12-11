// asignación de eventos


var isValidProduct = false;
var contratoID;
var objetoPrincipal;


$(document).ready(function() {
	var uno = 1;
	$("#nuCantidad").val(""+uno);
	// llamado de función inicial
	inicioPanel();
	if($('#isUnique').val() == ''){
		$('#continuar').css('display','none');
	}

	// comportamiento del botón nuevo
	$('#nuevo').on('click',function(){
		fbCamposABloquear(false);
		AlinearSelectsDerecha();
		$('#tituloModal').html('<i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Nuevo Modalidad - Tarifa');
		$('#rangoInicial').attr('readonly', true);
		$('#rangoFinal').attr('readonly', true);
		$('#importe').removeAttr('readonly');
		$('#rangoFinal').val('');
		$('#rangoInicial').val('');
		$("#isRango").attr("checked",false);
		$("#isRango").val('0');
		$("#anio").val('2019');
		$("#anio").multiselect('rebuild');
		$('#modalUsoGeneral').modal('show');
	});
	// comportamiento del botón de guardado
	$('#continuar').on('click',function(){
		
		var valor = $('#total').text();
		console.log(valor);
		if(valor == "$undefined"  || valor == "" || valor == null || valor == 0 || valor == "0" || valor === "undefined"){
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>El importe del contrato es 0 ¿Estas seguro de avanzar?</p>';
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "parent.reload(1);");
		}else{
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>¿Estas Seguro de avanzar?</p>';
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "parent.reload(1);");
		}
		
		// if($('#isUnique').val()== 'true')
			
	});

	
	$('#guardar').on('click',function(){
	var cantidad2 = $('#nuCantidad').val();	
	var valorUnitario = $('#valor').val();	
	var selectObjetosParciales = $('#selectObjetosParciales').val();	
	if( selectObjetosParciales == "" || selectObjetosParciales == null || selectObjetosParciales == 0 || selectObjetosParciales == "0" || selectObjetosParciales === "undefined" || selectObjetosParciales == -1 || cantidad2 == "" || cantidad2 == null || cantidad2 == 0 || cantidad2 == "0" || cantidad2 === "undefined"){ 
		
		if(cantidad2 == "" || cantidad2 == null || cantidad2 == 0 || cantidad2 == "0" || cantidad2 === "undefined"){ 
			// Si no selecciono Unidad Responsable
			ocultaCargandoGeneral();
			// muestraMensaje('Seleccionar UR para continuar con el proceso', 3, 'ModalGeneral_Advertencia', 5000);
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se permite cantidad en O ni vacia</p>';
			muestraModalGeneral(3, titulo, mensaje);
			return true;
		}
		if(valorUnitario == "" || valorUnitario == null || valorUnitario == 0 || valorUnitario == "0" || valorUnitario === "undefined"){ 
			// Si no selecciono Unidad Responsable
			ocultaCargandoGeneral();
			// muestraMensaje('Seleccionar UR para continuar con el proceso', 3, 'ModalGeneral_Advertencia', 5000);
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se permite valor unitario en O ni vacio</p>';
			muestraModalGeneral(3, titulo, mensaje);
			return true;
		}
		if(selectObjetosParciales == "" || selectObjetosParciales == null || selectObjetosParciales == 0 || selectObjetosParciales == "0" || selectObjetosParciales === "undefined" || selectObjetosParciales == -1){ 
			// Si no selecciono Unidad Responsable
			ocultaCargandoGeneral();
			// muestraMensaje('Seleccionar UR para continuar con el proceso', 3, 'ModalGeneral_Advertencia', 5000);
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se ha definido el objeto parcial</p>';
			muestraModalGeneral(3, titulo, mensaje);
			return true;
		}

	}


		var params = getParams(idForma), campos = getMatchets(idForma), msg = '',
		nombreCampo = {
				'importe':'Valor',
				'rangoInicial':'Rango Inicial',
				'rangoFinal':'Rango Final'
		},

			method = params['identificador'] ?'update':'store';
			$.extend(params,{method:method,'valid':1});
			$.each(campos,function(index, el) {	

				////if(el=='ind_activo'&&params[el]!=-1){ return; } // Línea adicional para este módulo
				if($("#"+el).is(":disabled")){ return; }
				if(el=="selectUnidadEjecutora"&&$("#"+el).val()=="00"){ return; } // Línea adicional para este módulo
				if(!params.hasOwnProperty(el)){ return; }
				// if(esepcion.indexOf(el)!==-1){ return; }
				if(params[el]!=0&&params[el]!=-1){ return; }
				if(nombreCampo[el]===undefined){ return; }
				if($("#"+el).val()!= '' && $("#"+el).val()!= 0 ){ return; } // Línea adicional para este módulo
				console.log(parseFloat($("#rangoFinal").val()) < parseFloat($("#rangoInicial").val()) ? "menor" : "mayor");
				if($("#isRango").is(':checked')){
					if([el] == 'importe'){ return;}
				}else{
					if([el] == 'rangoFinal'){ return;}
					if([el] == 'rangoInicial'){ return;}

				}
				msg += 'El campo '+nombreCampo[el]+' no puede ir vacío y debe ser siempre mayor a 0.<br />';

				
			});
	
			
	

		if($("#isRango").is(':checked')){
			if(parseFloat($("#rangoFinal").val()) < parseFloat($("#rangoInicial").val()))
				msg += 'El rango final debe ser mayor al rango inicial.<br />';
		}
		if(msg!=''){ muestraModalGeneral(3,'Error de datos',msg); return;}
		$.post(modelo, params).then(function(res){
			var titulo=res.success?'Operación Exitosa':'Error de Datos';
			if(res.success){
				contratoID = $('#contrato').val();
				fnLimpiarCamposFormConHidden(idForma);
				$('#contrato').val(contratoID);
				$('#selectObjetoPrincipal').val(objetoPrincipal);
				$('#selectObjetoPrincipal').multiselect('rebuild');
				$('#selectObjetoPrincipal').multiselect( "disable");
				fnFinalidadFuncion('');

				cargaInicial(contratoID);
				
			}
			// llenaTabla(res.content);
			muestraModalGeneral(3,titulo,res.msg);


		});

		$('#modalUsoGeneral').modal('hide');
		$('#productDescription').text('Sin Producto/Servicio seleccionado');
		$('#productDescription').css('color', 'black');
		var uno = 1;
		$("#nuCantidad").val(""+uno);
		
	});
		
	// comportamiento de modificación de registro
	$(document).on('cellselect','#tablaGrid',function(e){
		var CamposABloquear = false; // Línea adicional para este módulo
		// declaración de variables
		var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
		// confirmación de evento a lanzar
		if(campo != 'modificar'){ return false; }
		// declaración de variables secundarias para evitar carga innecesaria
		var row = $(this).jqxGrid('getrowdata', index);
		// se extrae la información de los datos a modificar de base de datos
		$.post(modelo, { method:'edit', identificador: row.identificador })
		.then(function(res){
			// declaración de variables
			var titulo = 'Error de Datos', $spanContent = $('<span>');
			// comprobación de éxito
			if(res.success){
				fnLimpiarCamposFormConHidden(idForma);
				
				$.each(res.content, function(index, val) {
					if(index == 'identificador'){
						$('#forma').append('<input type="text" name="identificador" id="identificador" value="'+val+'" class="hidden"/>');
						CamposABloquear = true; // Línea adicional para este módulo
					}else{
						if($('#'+index).is("select")){
							$('#'+index).multiselect('select', val);
							$('#'+index).multiselect('rebuild');
							$('#'+index).trigger('change')

							if(index == 'selectObjetoPrincipal'){
								$('#selectObjetoPrincipal').val(val);
								$('#selectObjetoPrincipal').multiselect('rebuild');
								$('#selectObjetoPrincipal').multiselect( "disable");
								fnFinalidadFuncion('');
							}
							if(index == 'selectObjetoParcial'){
								$('#selectObjetoParcial').val(val);
								$('#selectObjetoParcial').multiselect('rebuild');
								$('#selectObjetoParcial').multiselect( "disable");
							}

							
						}else{
							$('#'+index).val(val);
						}
						if(index == 'selectEstatus'){
							$('#selectEstatus').multiselect( "disable");
						}
						
					}
				});
				fbCamposABloquear(CamposABloquear);
				AlinearSelectsDerecha();
				$('#modalUsoGeneral #tituloModal').html('<i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Edición de Modalidad - Tarifa');
				$('#modalUsoGeneral').modal('show');
				llenaTabla();
				

			}
		});
	})
	// comportamiento de eliminación del registro
	.on('cellselect','#tablaGrid',function(e){
		// declaración de variables
		var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
		// validación de evento a lanzar
		if(campo != 'eliminar'){ return false; }
		// declaración de variables secundarias para evitar carga innecesaria
		var row = $(this).jqxGrid('getrowdata', index), $spanContent = $('<span>'),
			content = '¿Realmente desea eliminar el elemento <strong>'+row.identificador+'</strong>?',
			btnElimina = $('<button>',{ class : 'btn btn-primary btn-sm bgc8', html : 'Aceptar',
				click : function(){
					$.post(modelo, {method:'destroy', identificador:row.identificador})
					.then(function(res){
						var titulo = 'Error de Datos';
						if(res.success){
							titulo = 'Operación Exitosa';
							cargaInicial(contratoID);


						}
						muestraModalGeneral(3, titulo, res.msg);
					});
				}
			});
		// agregado de los botones de acción necesarios
		$spanContent.append(btnElimina).append(btnCancel);
		// ejecución de render de Vue en caso de que se tengan componentes
		muestraModalGeneralConfirmacion(3, 'Confirmación', content, $spanContent);
	});
	// Estilo para que los elementos se desplieguen bien en vista colapsada, y que además los multi select sobresalagan de los modales en la vista completa
	$('html > head').append( '<style type="text/css"> .OverdeSelectsenModales{ overflow-y: auto; overflow-y: auto; } @media (min-width: 992px){ .OverdeSelectsenModales{ overflow-x: visible !important; overflow-y: visible !important; } } </style>' );

	GetObjetosParciales();
	$('#contribuyente').val($('#contribuyente').val().replace(/&nbsp;/g, ' '));

});
/*********************************** FUNCIONES DE EJECUCCIÓN ***********************************/
/**
 * Función de configuración inicial, donde se generan e inician las variables que serán
 * usadas en el programa
 */
function inicioPanel() {
	// variables globales del sistema
	this.root = window;
	this.rootFi = 0;
	this.rootFu = 0;
	this.url = getUrl();
	this.modelo = this.url+'/modelo/abcObjetosParcialesContratoModelo.php';
	this.modeloComponentes = this.url+'/modelo/componentes_modelo.php';
	this.idForma = 'forma';
	this.btnCancel = $('<button>',{ class:'btn btn-primary btn-sm bgc8',html:'Cancelar','data-dismiss':'modal' });
	this.baseOption = '<option value="0">Seleccione una opción</option>';
	// funciones principales del sistema
	

	llenaTabla();
	
	// cargaInicial();
	// cargaProductos();
	// colocación de estilos en mensaje de modal
	$('#'+this.idForma).css({ 'max-height':'600px', 'min-height':'200px' });
	// comportamiento de apertura y cierre de la modal de captura
	$('#modalUsoGeneral').on('hidden.bs.modal',function(){
		//var limpiarSelects = ['partida','tg','ff'];
		//$.each(limpiarSelects,function(index,el){ $('#'+el).multiselect('select',0).multiselect('rebuild'); });
		if($('#modalUsoGeneral').find('#identificador').size()){
			fnLimpiarCamposFormConHidden(idForma);
		}
		$('#modalUsoGeneral').find('#identificador').remove();
	});
	// Líneas del archivo original
	cargaSelects();
	contratoID = $('#contrato').val();
	objetoPrincipal = $('#objetoPrincipal').val();
	$('#selectObjetoPrincipal').val(objetoPrincipal);
	$('#selectObjetoPrincipal').multiselect('rebuild');
	$('#selectObjetoPrincipal').multiselect( "disable");
	fnFinalidadFuncion('');
	cargaInicial(contratoID);
	fnTipoTarifasFuncion();
}
/**
 * Función para el llenado de la tabla principal con los datos
 * enviados como parámetro
 * @param {Array} data Contenido que será cargado en la tabla
 */
function llenaTabla(data) {
	// declaración de variables principales


	var data = data||[], el = 'contenedorTabla', 
			tabla = 'tablaGrid', 
				nameExcel = 'Almacenes'
		, tblObj, tblTitulo, tblExcel=[0,1,2,3,4,5,6,], tblVisual=[0,1,2,3,4,5,6,7,8];
	tblObj = [
		{ name: 'objetoParcial', type: 'string'},// 3
		{ name: 'cantidad', type: 'string'},// 5
		{ name: 'valor', type: 'string'},// 5
		{ name: 'total', type: 'string'},// 5
		{ name: 'modificar', type: 'string'},// 7
		{ name: 'eliminar', type: 'string'},// 8
		{ name: 'identificador', type: 'string'},// 9
	]; 
	tblTitulo = [
		{ text: 'Objeto Parcial', datafield: 'objetoParcial', editable: false, width: '50%', cellsalign: 'center', align: 'center' },// 0
		{ text: 'Cantidad', datafield: 'cantidad', editable: false, width: '20%', cellsalign: 'center', align: 'center' },// 4
		{ text: 'Valor', datafield: 'valor', editable: false, width: '20%', cellsalign: 'center', align: 'center' },// 4
		{ text: 'Total', datafield: 'total', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 7
		{ text: 'Modificar', datafield: 'modificar', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 8
		{ text: 'Eliminar', datafield: 'eliminar', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 8
	];
	// llamado de limpieza de la tabla
	fnLimpiarTabla(el,tabla);
	// render de la tabla
	fnAgregarGrid_Detalle_nostring(data, tblObj, tblTitulo, tabla, ' ', 1, tblExcel, false, true, "", tblVisual, nameExcel);
}
/**
 * Función de búsqueda de datos inicial
 */
function cargaInicial(contratoID) {
	// solicitud al servidor de la información para la configuración
	$.post(this.modelo, {method:'show', contrato: contratoID}).then(function(res){
		llenaTabla(res.content);
		$('#total').text("$"+res.total);
	});
}
/************************* HELPERS *************************/
/**
 * Función para obtener la base de la url sin importar
 * en donde se encuentra el sistema
 * @return {String} Url encontrada según el proceso de filtrado
 */
function getUrl() {
	// declaración de variables principales
	var url = this.location.href.split('/');
	url.splice(url.length - 1);
	// retorno de información
	return url.join('/');
}

/* Funciones del archivo original */
function fbCamposABloquear(tipoAccion){
	/*
			'selectUnidadNegocio':'UR',
			'selectUnidadEjecutora':'UE',
	*/
	var	CamposABloquear = [{
			'LocCode':'Código de Almacén'
		}];

	$.each(CamposABloquear[0],function(index, el) {
		if($('#'+index).is("select")){
			$('#'+index).
			multiselect( tipoAccion ? "disable" : "enable" );
		}else{
			//$('#'+index).prop('readonly', tipoAccion);
			$('#'+index).attr('readonly', tipoAccion);
			if(!tipoAccion){
				$('#'+index).removeAttr("readonly");
			}
		}
	});
}

$('#btnBuscar').click(function(){
	fnMostrarDatos();
});

function fnMostrarDatos(){
	$.post(this.modelo, {method:'show', info: getParams('frmFiltroActivos')}).then(function(res){
		llenaTabla(res.content);
	});
}

function cargaProductos(){
	$.post(this.modelo, {method:'cargaProductos'}).then(function(res){

            console.log(res.content);
            if(res.success)
            {
               
                $( "#txtNombre").autocomplete({
                    source: res.content,
                    select: function( event, ui ) {
                        
                        $( this ).val( ui.item.value + " (" + ui.item.label + ")");
                        $( "#stockID" ).val( ui.item.value );
                        //console.log(item);

                        return false;
                    }
                })
                .autocomplete( "instance" )._renderItem = function( ul, item ) {

					return $( "<li>" )
					.append( "<a>" + item.value + " | "+ item.label+"</a>" )
					.appendTo( ul );

                };  
            }
           
       
	});
}

function cargaSelects(){
	var Elemento = "";
	$.post(this.modelo, {method:'datosselectImpuestos'}).then(function(res){
		$.each(res.content, function(index, val) {
			$('#TaxProvince').append(new Option(val.label, val.value));
		});
		fnFormatoSelectGeneral(".TaxProvince");
	});
}



function AlinearSelectsDerecha(){
	if($("#ColumnaDerecha").find("ul[class='multiselect-container dropdown-menu']").size()){
		$("#ColumnaDerecha").find("ul[class='multiselect-container dropdown-menu']").addClass("pull-right");
	}
}

function fnValidaDatos() {
	var mensaje= "";
	var notifica= false;

	if ($("#CategoryID").val() == "" || $("#CategoryID").val() == "0") {
		mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp; Debe capturar código de categoria para poder continuar.</p>'; 

		notifica= true;
	}

	if ($("#CategoryDescription").val() == "" || $("#CategoryDescription").val() == "0") {
		mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp; Debe capturar descripción de categoria para poder continuar.</p>'; 
		notifica= true;
	}

	if ($("#StockAct").val() == $("#accountegreso").val()) {
		mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp; Las cuentas de cargo y abono no pueden ser iguales.</p>'; 

		notifica= true;
	}

	if (notifica) {
		muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', mensaje);	

		return false;
	} else {
		return true;
	}
}


function fnTraeInformacionProducto() {
	// Obtiene la descripcion de la partida generica
	var idProducto = $('#txtNombre').val();
	var	descripcionPartida = $('#productDescription');
	dataObj = { 
		option: 'muestraDescripcionProducto',
		idProducto: idProducto
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/componentes_modelo.php",
		data:dataObj
	})
	.done(function( data ) {
		if(data.result){
			$('#productDescription').css('color', 'black');
			descripcionPartida.text( data.contenido);
			isValidProduct = true;
			
		}else{
			descripcionPartida.text('Producto o Servicio no valido');
			isValidProduct = false;
		}
	})
	.fail(function(result) {
		//console.log("ERROR");
		//console.log( result );
	});

}

function fnFinalidadFuncion (type) {
	var id_identificacion = $("#selectObjetoPrincipal"+type).val();
	dataObj = {
		option: 'mostrarObjetoParcial',
		id_identificacion: id_identificacion
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType: "json",
		url: "modelo/componentes_modelo.php",
		data: dataObj
	}).done(function(data) {
		if (data.result) {
			dataJson = data.contenido.datos;
			console.log(dataJson);
			var contenido = "";
			for (var info in dataJson) {
				contenido += "<option value='" + dataJson[info].id_fuente + "'>" + dataJson[info].fuentedescription + "</option>";
			}
			$('#selectObjetoParcial'+type).empty();
			$('#selectObjetoParcial'+type).append('<option value="-1">Sin Selección...</option>'+contenido);
			$('#selectObjetoParcial'+type).multiselect('rebuild');
		} else {
			console.log("ERROR Modelo");
			console.log( JSON.stringify(data) ); 
		}
	}).fail(function(result) {
		console.log("ERROR");
		console.log( result );
	});
 }

 function fnTipoTarifasFuncion () {
	var anio = $("#anio").val();
	

	dataObj = {
		option: 'mostrarTipoTarifas',
		anio: anio
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType: "json",
		url: "modelo/componentes_modelo.php",
		data: dataObj
	}).done(function(data) {
		if (data.result) {
			dataJson = data.contenido.datos;
			console.log(dataJson);

			var contenido = "";
			for (var info in dataJson) {
				contenido += "<option value='" + dataJson[info].clave + "'>" + dataJson[info].nombre + "</option>";
			}
			$('.selectTipoTarifas').empty();
			$('.selectTipoTarifas').append('<option value="-1">Sin selección...</option>'+contenido);
			$('.selectTipoTarifas').multiselect('rebuild');
		} else {
			console.log("ERROR Modelo");
			console.log( JSON.stringify(data) ); 
		}
	}).fail(function(result) {
		console.log("ERROR");
		console.log( result );
	});
 }

 function GetObjetosParciales(){
	var idConfigContratos = $('#idconfContrato').val();	

	dataObj = {
		option: 'mostrarObjetosParcialesContrato',
		id_configContratos: idConfigContratos
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType: "json",
		url: "modelo/componentes_modelo.php",
		data: dataObj
	}).done(function(data) {
		if (data.result) {
			dataJson = data.contenido.datos;
			var contenido = "";

			for (var info in dataJson) {
				contenido += "<option value='" + dataJson[info].clave + "'>" + dataJson[info].nombre + "</option>";
			}
			$('#selectObjetosParciales').empty();
			$('#selectObjetosParciales').append(contenido);
			$('#selectObjetosParciales').multiselect('rebuild');
		} else {
			console.log("ERROR Modelo");
			console.log( JSON.stringify(data) ); 
		}
	}).fail(function(result) {
		console.log("ERROR");
		console.log( result );
	});
	ChangeObjetosParciales();
 }


 function ChangeObjetosParciales(){
	var objetosID = $('#selectObjetosParciales').val();	

	dataObj = {
		option: 'mostrarValoresObjetosParcialesContrato',
		objeto_id: objetosID
	};

	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType: "json",
		url: "modelo/componentes_modelo.php",
		data: dataObj
	}).done(function(data) {
		if (data.result) {
			dataJson = data.contenido.datos;
			for (var info in dataJson) {
				$('#metros').val(dataJson[info].variable);
				$('#valor').val(dataJson[info].valor);

			}

			$('#metros').attr('readonly', true);
			// $('#valor').attr('readonly', true);

			
		} else {
			console.log("ERROR Modelo");
			console.log( JSON.stringify(data) ); 
		}
	}).fail(function(result) {
		console.log("ERROR");
		console.log( result );
	});
 }
