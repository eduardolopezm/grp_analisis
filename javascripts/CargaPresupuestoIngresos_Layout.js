/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */

$( document ).ready(function() {
	
});

function fnObtenerConfiguracionClave() {
	dataObj = { 
		option: 'datosConfiguracion',
		idConfig: $("#selectConfiguracionClaveLayout").val()
	};
	
	$.ajax({
		async:false,
        cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/CargaPresupuestoIngresos_Layout_modelo.php",
		data:dataObj
	})
	.done(function( data ) {
		//console.log("Bien");
		if(data.result){
			//Si trae informacion		
			$('#divDatosConfigTitulo').empty();
			$('#divDatosConfigTitulo').append(data.contenido.titulo);

			$('#divDatosConfig').empty();
			$('#divDatosConfig').append(data.contenido.contenido);
		}else{
			var body = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se cargo la configuración de la clave</p>';
			muestraMensaje(body, 3, 'ModalGeneral_Advertencia', 5000);
			//console.log("ERROR Modelo");
			//console.log( JSON.stringify(data) ); 
		}
	})
	.fail(function(result) {
		//console.log("ERROR");
		//console.log( result );
	});
}

function fnCargarLayoutPrueba() {
	var idConfig = $("#selectConfiguracionClaveLayout").val();
	var oficio = $("#txtNoOficio").val();
	var tipo = $("#selectTipoPresupuesto").val();
	var fila = $("#txtFilaInicio").val();

	if (tipo == "-1" || tipo.trim() == '' || tipo == null) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Completar el formulario (Tipo)</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	if (idConfig == "-1" || idConfig.trim() == '' || idConfig == null) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Completar el formulario (Configuración)</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}
	
	// if (oficio.trim() == "") {
	// 	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	// 	var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Completar el formulario (No. Oficio)</p>';
	// 	muestraModalGeneral(3, titulo, mensaje);
	// 	return true;
	// }
	
	if(isNaN(fila) || Number(fila) == 0){
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Completar el formulario (Fila), debe ser un número mayor a 0</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	var btn = document.getElementById('btnCargarSubmit');
    btn.click();
    return true;

	var v=$("#txtCatalogo").val();
    if(v!=''){
    	muestraCargandoGeneral();
        var form_data = new FormData();
        form_data.append("archivos", document.getElementById('txtCatalogo').files[0]);
        form_data.append('option','cargarLayout');
        form_data.append("idConfig", idConfig);
        //form_data.append("fechaPoliza", $("#txtFechaPolizaLayout").val());
        form_data.append("tipoPresupuesto", tipo);
        form_data.append("filaInicio", fila);
        form_data.append("noOficio", oficio);
        
        $.ajax({
            url: "modelo/CargaPresupuestoIngresos_Layout_modelo.php",
            dataType: 'json', //retorno servidor  recuerda que es json
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
        })
        .done(function(data){
            if(data.result){
            	ocultaCargandoGeneral();
                //muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);
                var titulo = '<h3><p><i class="glyphicon glyphicon-list-alt text-success" aria-hidden="true"></i> Información del Proceso</p></h3>';
				muestraModalGeneral(4, titulo, data.contenido);
            }else{
                ocultaCargandoGeneral();
            }
        });
    }else{
        ocultaCargandoGeneral();
        muestraMensaje("Seleccione algún archivo",3, 'divMensajeOperacion', 5000);
    }
}