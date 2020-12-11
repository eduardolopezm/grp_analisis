var datosPresupuestosBusqueda = [];
var tablaReducciones = "tablaReducciones";
var panelReducciones = 1; 
var datosRadicados = new Array();
var idClavePresupuestoReducciones = 0;
var totalReducciones = 0;
var decimales = 2;
var autorizarGeneral = 0;
var tipoSuficiencia = 0;
var nuRequisicionSuf = 0;
var numLineaReducciones = 1;
var blnNuevo = true;
var tituloGeneral = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';


var dataJsonMeses = new Array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

$(document).ready(function (){

	// VALIDACIONES INICIALES
	intGetFolio = $('#txtFolioRadicacion').val();
	intGetIdRadicacion = $('#txtIdRadicacion').val();

	$('#linkPanelAdecuaciones').hide();
	$('#btnUploadFile').hide();

	if(intGetFolio != ''){
		fnCargarRadicacion(intGetFolio);
	}else{
		fnCargarInicio();
        fnObtenerFirmantes();
	}

	$('#selectEstatusRadicacion').multiselect('disable');
	

	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	//!!                Eventos click.                 !!
	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	$("#btnUploadFile").click(function(e) {
        e.preventDefault(); // to stop  load  to form
        fnLoadFiles();
    });

    $('#btnGuardarRadicacion').click(function(e){
		fnGuardarRadicacion(blnNuevo);
    });

    $('#btnCancelar').click(function(){
    	fnLimpiarCampos();
    });

    $('#btnAutorizar').click(function(){
    	fnGuardarRadicacion(false,1);
    	fnAutorizarRadicacion();
    });

    $('#idIconCalenderPago').click(function(){
    	var fechaElaboracion =$('#dateElaboracion').val();
		var arrFecha = fechaElaboracion.split('-');
    	//console.log(arrFecha);
    	$('#datePago').datetimepicker({
    		format: 'DD-MM-YYYY',
            minDate: ''+arrFecha[2]+'-'+arrFecha[1]+'-'+arrFecha[0]
        });

    	$('#datePago').focus();

    });

    $('#btnObtenerClaves').click(function(){

    	if( fnValidarCamposObligatorio()== false){
    
    		return false;
    	}
    	
    	if($("#tablaReducciones").find(".filaPresupuestoSolicitud").length > 0){
    		var Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Se eliminara el contenido actual de la tabla, ¿Desea continuar? </p>';

    		if(intGetIdRadicacion!=""){
    			fnObtenerPresupuestoBusqueda();
    			fnObtenerDetalleRadicacion();
    		}else{
    			muestraModalGeneralConfirmacion(3,tituloGeneral,Mensaje, '','fnObtenerPresupuestoBusqueda()','');
    		}
    		
    	}else{
    		fnObtenerPresupuestoBusqueda();
    	}
    	
    });

    $('#idIconCalender').click(function(){
    	$("#dateAutorizacion").focus();
    });

    $('#selectCapitulos').change(function(){
        var identificador = "";

        $( "#selectCapitulos option:selected" ).each(function() {
            console.log($(this).data('identificador'));
            identificador += ''+$(this).data('identificador')+',';
        });

        identificador = identificador.slice(0,-1);
        
        var arrcapitulos = fnObtenerOption('selectCapitulos');
        var ur = $('#selectUnidadNegocio').val();
        var ue = $('#selectUnidadEjecutora').val();

        if(arrcapitulos =="" || ur =="-1"|| ue =="-1"){
            return false;
        }


        $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType:"json",
            url: "modelo/componentes_modelo.php",
            data:{option:'obtenerClabeConcentradoraRadicado', capitulos:arrcapitulos,ur:ur,ue:ue, identificador: identificador}
        }).done(function(res){
            if(!res.result){return;}
            var options='';
            var selected = "";

            var dataJson = res.contenido;
            if (dataJson.length == 1) {
              selected="selected";
            }

            $('.selectClabeConcentradora').empty();
            //$('.selectClabeConcentradora').multiselect('rebuild');

            $.each(res.contenido,function(index, el) {
                options += '<option value="'+ el.id +'" '+selected+'>'+ el.descripcion +'</option>';
            });
            $('.selectClabeConcentradora').append(options);
            $('.selectClabeConcentradora').multiselect('rebuild');
        }).fail(function(res){
            throw new Error('No se logro cargar la información del beneficiarios');
        });

    });

    fnObtenerBeneficiarios();
    
    if(anoFiscalAnterion =="1"){
        var options = "<option value='12' selected>Diciembre</option>";
        $('#selectMesRadicacion').empty();
        $('#selectMesRadicacion').append(options);
        $('#selectMesRadicacion').multiselect('rebuild');
        $('#selectMesRadicacion').multiselect('disable');
    }

});

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

        // tamanio = generateItem('span', {html:'<b>Tamaño</b>'+file.size});
        // cols.push(generateItem('td', {
        //     style: estilo
        // }, tamanio));


        quitar = generateItem('span', {class:'quitarArchivos glyphicon glyphicon-remove btn btn-xs bgc8',style:'color:#fff;display:none;'},generateItem('input',{type:'hidden',val:file.name}));
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

function fnObtenerBeneficiarios(){
    var selectur = $('#selectUnidadNegocio').val();
    var selectue = $('#selectUnidadEjecutora').val();

    if(selectur == "-1"){
        return false;
    }

    if(selectue == "-1"){
        return false;
    }

    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/componentes_modelo.php",
        data:{option:'obtenerBeneficiarioRadicado',ur:selectur,ue:selectue}
    }).done(function(res){
        if(!res.result){return;}
        var options='';
        var selected = "";

        var dataJson = res.contenido;
        if (dataJson.length == 1) {
          selected="selected";
        }
        $.each(res.contenido,function(index, el) {
            options += '<option value="'+ el.id +'" '+selected+'>'+ el.descripcion +'</option>';
        });

        $('.selectBeneficiario').append(options);
        $('.selectBeneficiario').multiselect('rebuild');

        fnObtenerInfoBeneficiario();

    }).fail(function(res){
        throw new Error('No se logro cargar los beneficiarios');
    });
}

function fnObtenerInfoBeneficiario(){
    var selectBeneficiario = $('#selectBeneficiario').val();

    if(selectBeneficiario =="-1"){
        return false;
    }
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/componentes_modelo.php",
        data:{option:'obtenerInfoBeneficiario', idBeneficiario:selectBeneficiario}
    }).done(function(res){
        if(!res.result){return;}
        $.each(res.contenido,function(index, el) {
            $('#rfcBeneficiario').val(el.rfc);
            $('#clabeBeneficiario').val(el.cuenta);
        });
    }).fail(function(res){
        throw new Error('No se logro cargar la información del beneficiarios');
    });
}

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

function fnAutorizarRadicacion(){

	var errMensaje="";
	var txtFechaAutorizado=$("#dateAutorizacion").val();
	var txtCLC=$("#txtCLCSIAFF").val();
    var numTransferencia = $('#numTransferencia').val();
	var txtFechaPago=$("#datePago").val();
    var firmante=$("#selectFirmaRadicacion").val();
	var numOficio=$("#numOficio").val();

	if(txtFechaPago == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Programación del Pago es obligatorio.</p>';
	}

	if(txtFechaAutorizado == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Fecha Autorización es obligatorio.</p>';
	}

	if(txtCLC == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo CLC SIAFF es obligatorio.</p>';
	}

	// if($("#tablaDetallesArchivos >tbody >tr").length <= 0){
	// 	errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Es necesario adjuntar la documentación de autorizacion.</p>';
	// }

	if(numTransferencia == ""){
        errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Referencia/Transferencia es obligatorio.</p>';
    }

    if(firmante == "-1"){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Firmante es obligatorio.</p>';
	}

	var fechaActual = new Date();
	var mesActual = fechaActual.getMonth();

	// if(parseFloat($('#selectMesRadicacion').val()) < parseFloat(mesActual)){
	// 	errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El mes debe ser posterior o igual al mes actual.</p>';
	// }

	var montoTotalAutorizado=0;
	$("#tablaReducciones").find(".filaPresupuestoAutorizado").each(function(index){
		montoTotalAutorizado =  parseFloat(montoTotalAutorizado ) + parseFloat($(this).val());
	});	

	if(parseFloat(montoTotalAutorizado) <= 0){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El total autorizado de la radicación debe ser mayor a 0.</p>';
	}

    if(fnValidarFirmante() == 1){
        if(numOficio == ""){
            errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Número de Oficio es requerido cuando el firmante no es el titular.</p>';
        } 
    }

    if(fnValidarMesUltimoRadicado()){
        errMensaje += errMensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El mes seleccionado es menor al ultimo mes radicado. Se debera cancelar el folio para liberar recursos en tramite.</p>' ;
    }

	if(errMensaje!=""){
		muestraModalGeneral(3,tituloGeneral,errMensaje);
		return false;
	}

	if(fnValidarSolicitado() == false){
		return false;
	}





	var identificador = "";

    $( "#selectCapitulos option:selected" ).each(function() {
        console.log($(this).data('identificador'));
        identificador += ''+$(this).data('identificador')+',';
    });

    identificador = identificador.slice(0,-1);

	var dataObj = {
                    option: 'autorizarRadicacion',
                    ur: $('#selectUnidadNegocio').val(),
                    ue: $('#selectUnidadEjecutora').val(),
                    intFolio:$('#txtFolioRadicacion').val(),
                    idRadicacion:$('#txtIdRadicacion').val(),
                    mes:$('#selectMesRadicacion').val(),
                    numTransferencia:$('#numTransferencia').val(),
                    capitulos:fnObtenerOption('selectCapitulos'),
                    identificador:identificador,
                };

    $.ajax({
    	async:false,
        method: "POST",
        dataType:"json",
        url: "modelo/solicitudRadicacionModelo.php",
        data:dataObj
    })
    .done(function( data ){
        if(data.result){
            var Mensaje ="";

            Mensaje = data.Mensaje;

            //var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            if(data.Mensaje == "true"){
                Mensaje ="Se autorizo la radicación con folio: "+ $('#txtFolioRadicacion').val() +", correctamente.";
                muestraModalGeneral(3, tituloGeneral,Mensaje);
                fnEstatusCampos(true,'disable');
                fnBloquearDivs("divContenidoRadicacion");
                /*==== Estatus ====*/
                $("#selectEstatusRadicacion").val('5');
                $("#selectEstatusRadicacion").multiselect('rebuild');
                $("#txtIdStatusRadicacion").val('5');
                $('#btnAutorizar').hide();
            }else{
                muestraModalGeneral(3, tituloGeneral,Mensaje);
            }
            
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}

function fnValidarFirmante(){

    var fd = new FormData();
    var titular = 0;
    fd.append("option","firmanteTitular");
    fd.append("idRadicacion",$('#txtIdRadicacion').val());
    fd.append("idRadicacion",$('#txtIdRadicacion').val());

    $.ajax({
        async:false,
        url:"modelo/solicitudRadicacionModelo.php",
        type:'POST',
        data: fd, 
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (data) {
            if(data.result){
                $.each(data.contenido.datosDetalle,function(index, el) {
                    if(el.titular !=""){
                        titular = 1;
                    }
                });
            }
        }
    }); 

    return titular;

}



function fnValidarMesUltimoRadicado(){

    var fd = new FormData();
    var blnValidacionMen = false;
    fd.append("option","obtenerMesesUltimoRadicado");
    fd.append("ur",$('#selectUnidadNegocio').val());
    fd.append("ue",$('#selectUnidadEjecutora').val());

    $.ajax({
        async:false,
        url:"modelo/componentes_modelo.php",
        type:'POST',
        data: fd, 
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (data) {
            if(data.result){
                $.each(data.contenido,function(index, el) {
                    //console.log(parseInt($('#selectMesRadicacion').val()) +' ' +el.mes );
                    if(parseInt($('#selectMesRadicacion').val())  < el.mes){
                        blnValidacionMen = true;
                    }
                  
                });
            }
        }
    }); 

    //console.log(blnValidacionMen);

    return blnValidacionMen;

}

function fnLimpiarCampos(){

    var temporaUR = $("#selectUnidadNegocio").val();
	fnLimpiarCamposForm('PanelBusqueda');
    
    $('#selectUnidadNegocio').val(temporaUR);
    $('#selectUnidadNegocio').multiselect('rebuild');
    $('#selectUnidadNegocio').multiselect('disable');

	var d = new Date();
    var mes =(d.getMonth()+1);

    if(String(mes).length ==1){
    	mes= '0'+String(mes);
    }
    
    $('#dateElaboracion').val(''+d.getDate()+'-'+mes+'-'+d.getFullYear());

    $('#tablaReducciones').empty();
    $('#txtTotalReducciones').text('0.00');

    $('#selectFirmaRadicacion').val('-1');
    $('#selectFirmaRadicacion').multiselect('rebuild');
}

function fnCargarInicio(){
	blnNuevo = true;

	$('#txtCLCSIAFF').prop('disabled', true);
	$('#dateAutorizacion').prop('disabled', true);
	$('#numTransferencia').prop('disabled', true);
	$('#btnAutorizar').hide();
	$('#btnCancelar').show();
	$('#selectEstatusRadicacion').empty();
	$('#selectEstatusRadicacion').append();
	$('#selectEstatusRadicacion').multiselect('rebuild');
}

function fnCargarRadicacion(folio){
	blnNuevo = false;	

	$('#txtNoCaptura').text('');
	$('#txtNoCaptura').text(folio);

	fnObetenerRadicacion();
}

function fnObetenerRadicacion(){

	var folio_Radicacion=$('#txtFolioRadicacion').val();
	var id_Radicacion=$('#txtIdRadicacion').val();

	var fd = new FormData();
	fd.append("option","obtenerRadicacion");
	fd.append("intFolio",folio_Radicacion);
	fd.append("idRadicacion",id_Radicacion);

	$.ajax({
	    async:false,
	    url:"modelo/solicitudRadicacionModelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	if(data.result){

    			var datosEncabezado = data.contenido.datos;
    			var datosDetalle = data.contenido.datalle;
    			var datosDetalleArchivos = data.contenido.detalleArchivos;
    			var statusRadicacion="";
    			for (var index in datosEncabezado) {

    				/*==== UR ====*/
    				strOptionUR = "<option value='"+datosEncabezado[index].UR+"' selected>"+datosEncabezado[index].URDescripcion+"</option>";
    				$('#selectUnidadNegocio').empty();
			        $('#selectUnidadNegocio').append(strOptionUR);
			        $('#selectUnidadNegocio').multiselect('rebuild');

			        /*==== UE ====*/
			        var ues = datosEncabezado[index].UE;
			        var arrUE = ues.split(",");
			        $("#selectUnidadEjecutora").val(arrUE);
			        $('#selectUnidadEjecutora').multiselect('rebuild');

                    /*==== Beneficiario ====*/
                    fnObtenerBeneficiarios();

                    $("#selectBeneficiario").val(datosEncabezado[index].idbeneficiario);
                    $("#selectBeneficiario").multiselect('rebuild');
                    $("#rfcBeneficiario").val(datosEncabezado[index].rfcbeneficiario);
                    $("#clabeBeneficiario").val(datosEncabezado[index].clabebeneficiario);

			        fnObtenerFirmantes();

			        var firmante = datosEncabezado[index].firmante;
                    if(firmante !='0'){
                        $("#selectFirmaRadicacion").val(firmante);
                        $('#selectFirmaRadicacion').multiselect('rebuild');
                    }
			        

			        /*==== CLC ====*/
			        $('#txtCLCSIAFF').val(datosEncabezado[index].clc);

			        /*==== Programa Presupuestal ====*/
			        strOptionPP = "<option value='"+datosEncabezado[index].programa+"' selected>"+datosEncabezado[index].PPDescripcion+"</option>";
			        $('#selectProgramaPresupuestal').empty();
			        $('#selectProgramaPresupuestal').append(strOptionPP);
			        $('#selectProgramaPresupuestal').multiselect('rebuild');

			        /*==== Capitulo ====*/
			        var capitulo = datosEncabezado[index].capitulo;
			        var arrCapitulo = capitulo.split(",");
			       
	                $("#selectCapitulos").val(arrCapitulo);
	                $("#selectCapitulos").multiselect('rebuild');

	                /*==== Mes ====*/
			        $("#selectMesRadicacion").val(datosEncabezado[index].mes_solicitado);
	                $("#selectMesRadicacion").multiselect('rebuild');

	                /*==== Fechas ====*/
			        $('#dateElaboracion').val(datosEncabezado[index].fecha_captura);

                    $('#datePago').val(datosEncabezado[index].fecha_pago);
                    if( datosEncabezado[index].fecha_pago == "0000-00-00"){
                        $('#datePago').val("");
                    }
			        
			        $('#dateAutorizacion').val(datosEncabezado[index].fecha_autorizacion);

	                /*==== Justificacion ====*/
			        $('#txtJustificacion').val(datosEncabezado[index].justificacion);

			        /*==== Estatus ====*/
			        $("#selectEstatusRadicacion").val(datosEncabezado[index].estatus);
	                $("#selectEstatusRadicacion").multiselect('rebuild');
	                $("#txtIdStatusRadicacion").val(datosEncabezado[index].estatus);

	                /*==== Tranferencia ====*/
			        $('#numTransferencia').val(datosEncabezado[index].numTransferencia);

                    /*==== Cuenta Concentradora ====*/
                    strOptionConcentradora = "<option value='"+datosEncabezado[index].idConcentradora+"' selected>"+datosEncabezado[index].cuentaConcentradora+"</option>";
                    $('#selectClabeConcentradora').empty();
                    $('#selectClabeConcentradora').append(strOptionConcentradora);
                    $('#selectClabeConcentradora').multiselect('rebuild');

                    /*==== Oficio ====*/
                    $('#numOficio').val(datosEncabezado[index].ln_oficio);

			        statusRadicacion = datosEncabezado[index].estatus;
					
    			}

    			fnEstatusCampos(true,'disable');
			    fnValidacionEstatus(statusRadicacion);

			    fnObtenerPresupuestoBusqueda(id_Radicacion);
			    
    			var lineOriginal="";

    			for(var index2 in datosDetalle){
    				lineOriginal = $('#'+datosDetalle[index2].presupuesto).data('idline');
    				$("#RenglonTR_"+datosDetalle[index2].presupuesto+'_1').css("background-color", "");

    				$('#'+datosDetalle[index2].presupuesto).val(datosDetalle[index2].solicitado);
    				
    				if(permisoAutorizador == "1" &&  statusRadicacion == "4"){
    					$('#filaAutorizado'+lineOriginal).val(datosDetalle[index2].solicitado);
    				}else{
    					$('#filaAutorizado'+lineOriginal).val(datosDetalle[index2].autorizado);
    				}
    			}

    			var inputCampo="filaPresupuestoSolicitud";
    			if(statusRadicacion == "4"){
    				inputCampo="filaPresupuestoAutorizado";
    			}

    			fnCalcularTotales(inputCampo);

    			var trArchivos="";
    			var cols = [];
    			var estilo='text-center w100p';
    			var archivos=0;
    			var Mensaje = "¿Desea eliminar el archivo?";

    			for(var index3 in datosDetalleArchivos){
    				cols = [];
    				//nombre = generateItem('span', {html:datosDetalleArchivos[index3].nameFile});
    				nombre = generateItem('a', {href:datosDetalleArchivos[index3].urlFile,target:'_blank', html:datosDetalleArchivos[index3].nameFile});
			        cols.push(generateItem('td', {
			            style: estilo
			        }, nombre));

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

function fnMensajeConfirmacion(idArchivo){
	muestraModalGeneralConfirmacion(3,tituloGeneral,'¿Desea eliminar el archivo?','','fnRemoverPartida('+idArchivo+')');
}

function fnRemoverPartida(idArchivo){
	var fd = new FormData();
	fd.append("option","removerArchivo");
	fd.append("idArchivo",idArchivo);

	$.ajax({
	    async:false,
	    url:"modelo/solicitudRadicacionModelo.php",
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

function fnObtenerDetalleRadicacion(){
	var folio_Radicacion=$('#txtFolioRadicacion').val();
	var id_Radicacion=$('#txtIdRadicacion').val();

	var fd = new FormData();
	fd.append("option","obtenerDetalleRadicacion");
	fd.append("intFolio",folio_Radicacion);
	fd.append("idRadicacion",id_Radicacion);

	$.ajax({
	    async:false,
	    url:"modelo/solicitudRadicacionModelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	if(data.result){

    			var lineOriginal="";
    			var datosDetalle = data.contenido.datosDetalle

    			for(var index2 in datosDetalle){
    				lineOriginal = $('#'+datosDetalle[index2].presupuesto).data('idline');
    				$("#RenglonTR_"+datosDetalle[index2].presupuesto+'_1').css("background-color", "");

    				$('#'+datosDetalle[index2].presupuesto).val(datosDetalle[index2].solicitado);
    				$('#filaAutorizado'+lineOriginal).val(datosDetalle[index2].autorizado);
    			}

    			fnCalcularTotales();
	        }
	    }
	}); 	
}

function fnValidacionEstatus(estatus){
	switch (estatus) {
	    case '1':
	    case '2':
	    case '3':
	    	/*== Capturista ==*/
		    $('#txtJustificacion').prop('disabled', false);
		    $('#selectCapitulos').multiselect('enable');
		    $('#btnCancelar').hide();
		    $('#datePago').prop('disabled', false);

		    if((estatus == '2' || estatus == '3'|| estatus == '4'|| estatus == '5'|| estatus == '6') && permisoCapturista == '1'){
		    	fnEstatusCampos(true,'disable');
		    	$('#btnObtenerClaves').hide();
		    	$('#btnGuardarRadicacion').hide();
                fnBloquearDivs("divContenidoRadicacion");
		    }

		    if((estatus == '3'|| estatus == '4'|| estatus == '5'|| estatus == '6') && permisoValidador == '1'){
		    	fnEstatusCampos(true,'disable');
		    	$('#btnObtenerClaves').hide();
		    	$('#btnGuardarRadicacion').hide();
                fnBloquearDivs("divContenidoRadicacion");

		    }
            if(permisoAutorizadorOficinaCentral =='1' ){
                $('#numOficio').prop('disabled', false);    
            }
		    //fnObtenerPresupuestoBusqueda();
	        break;
	    case '4':
	    	/*== Solicitado ==*/
		    
	        if(permisoAutorizadorOficinaCentral =='1' ){
		    	//$('#txtCLCSIAFF').prop('disabled', false);
			    $('#dateAutorizacion').prop('disabled', false);

			    $('#numTransferencia').prop('disabled', false);
			    $('#btnUploadFile').prop('disabled', false);
			    $('#btnUploadFile').show();
			    $('#btnCancelar').hide();
			    $('#btnAutorizar').show();
			    $('#divPanelArchivos').show();
			    $('#btnObtenerClaves').hide();
			    var fechaElaboracion =$('#dateElaboracion').val();
				var arrFecha = fechaElaboracion.split('-');

			    $('#dateAutorizacion').datetimepicker({
			    	format: 'DD-MM-YYYY',
		            minDate: ''+arrFecha[2]+'-'+arrFecha[1]+'-'+arrFecha[0]
		        });
	    		$('#selectFirmaRadicacion').prop('disabled', true);


                $('#numOficio').prop('disabled', false);

		        
		    }else{
	    		$('#selectFirmaRadicacion').prop('disabled', false);

		    	$('#btnGuardarRadicacion').hide();
		    	$('#btnAutorizar').hide();
			    fnBloquearDivs("divContenidoRadicacion");
			    $('#divPanelArchivos').show();
			    $('#btnObtenerClaves').hide();
			    $('#btnUploadFile').hide();
			    
		    }

	    	break;
	    case '5':
	    	/*== Autorizado ==*/
	    	//$('#btnUploadFile').prop('disabled', true);
	    	if(permisoAutorizador =='1' ){
		    	$('#btnGuardarRadicacion').show();
		    	$('#btnCancelar').hide();
		    	$('#btnAutorizar').hide();
		    	$('#divPanelArchivos').show();
		    	fnBloquearDivs("divContenidoRadicacion");
		    	$('#btnUploadFile').prop('disabled', false);
			    $('#btnUploadFile').show();
			    $('#btnObtenerClaves').hide();
                $('#numOficio').prop('disabled', false);
			}
            if(permisoAutorizadorOficinaCentral =='1' ){
                $('#numOficio').prop('disabled', false);    
            }
	    	break;
	    case '6':
	    	/*== Cancelado ==*/
	    	//$('#btnUploadFile').prop('disabled', true);
	    	
	    		$('#selectFirmaRadicacion').prop('disabled', true);
		    	$('#btnGuardarRadicacion').hide();
		    	$('#btnCancelar').hide();
		    	$('#btnAutorizar').hide();
		    	$('#divPanelArchivos').show();
		    	fnBloquearDivs("divContenidoRadicacion");
			    $('#btnUploadFile').hide();
			    $('#btnObtenerClaves').hide();
			
	    	break;
	    default: 
        	break;
	}
}

function fnGuardarRadicacion(accionNuevo,mostrarMensaje=0){
    var errMensaje= "";
	if(fnValidarCamposObligatorioGuardar() == false){
		return  false;
	}

	if(fnValidarSolicitado() == false ){
		return false;
	}

	if(fnValidarCapitulos('selectCapitulos')){
		return false;
	}
    $commsj="";
    if(fnValidarMesUltimoRadicado()){
        if(accionNuevo==false){
            $commsj=" Se debera cancelar el folio para liberar recursos en tramite. ";
        }
        errMensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El mes seleccionado es menor al ultimo mes radicado. '+$commsj+'</p>' ;
    }

    if(errMensaje!=""){
        muestraModalGeneral(3,tituloGeneral,errMensaje);
        return false;
    }



	var fd = new FormData();
	var urlModelo = "modelo/solicitudRadicacionModelo.php";
	var option = "guardarRadicacion";
	var estatus = "1";

	if(accionNuevo==false){
		option="modificarRadicacion";
		fd.append('intFolio', $('#txtFolioRadicacion').val());
		fd.append('intIdRadicacion', $('#txtIdRadicacion').val());
		estatus = $('#txtIdStatusRadicacion').val();
	}

	if(($("#cargarMultiples").length) &&($("#cargarMultiples").val()!='') ){
	    var nFile = document.getElementById('cargarMultiples').files.length;
	     if(nFile>0){
	        for (var x = 0; x < nFile; x++) {
	            fd.append("archivos[]", document.getElementById('cargarMultiples').files[x]);
	        }
	    }
    }

	fd.append('option', option);
	fd.append('ln_ur', $('#selectUnidadNegocio').val());
	fd.append('ln_ue', fnObtenerOption('selectUnidadEjecutora',1));
	fd.append('ln_pp', $('#selectProgramaPresupuestal').val());
	fd.append('ln_capitulo', fnObtenerOption('selectCapitulos',1));
	fd.append('ln_mes', $('#selectMesRadicacion').val());
	fd.append('justificacion', $('#txtJustificacion').val());
	fd.append('fecha_elab', $('#dateElaboracion').val());
	fd.append('fecha_pago', $('#datePago').val());
	fd.append('fecha_autorizacion', $('#dateAutorizacion').val());
	fd.append('estatus',estatus);
	fd.append('numTransferencia', $('#numTransferencia').val());
    fd.append('firmante', $('#selectFirmaRadicacion').val());
    fd.append('idBeneficiario',$('#selectBeneficiario').val());
    fd.append('idConcentradora',$('#selectClabeConcentradora').val());
    fd.append('numOficio',$('#numOficio').val());
	
	var lineOriginal ="";
	var montoAutorizado ="";
	var numOrden ="";
	var contadorRow =0;

	$("#tablaReducciones").find(".filaPresupuestoSolicitud").each(function(index){
		if(parseFloat($(this).val())>0){
			
			var lineOriginal = $(this).data("idline");
			montoAutorizado = $('#filaAutorizado'+lineOriginal).val();
			numOrden = $('#filaOrden'+lineOriginal).val();

			fd.append('filaPresupuesto'+contadorRow,$(this).attr('id'));
			fd.append('filaPresupuestoSolicitado'+contadorRow,$(this).val());
			fd.append('filaPresupuestoAutorizado'+contadorRow,montoAutorizado);
			fd.append('filaOrden'+contadorRow,numOrden);
			contadorRow++;
		}
	});

	fd.append('numFilas',contadorRow);
    
    //Ajax
    $.ajax({
	    async:false,
	    url: urlModelo,
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	if(data.result){
	    		var dataJson= data.contenido;
	    		$('#cargarMultiples').val('');
	    		for( var info in dataJson){
	    			blnNuevo = false;
	    			if(mostrarMensaje ==0){

	    				muestraModalGeneral(3,tituloGeneral,data.Mensaje,'','fnActualizarPantallaRadicacion('+dataJson[info].folio+','+dataJson[info].idRadicacion+','+estatus+')');
	    			}
	    		}

	    		fnCalcularTotales();
	    	}

	    }
	});
    // /Ajax
}

function fnValidarCapitulos(componenteSelect){
	var valores = "";
	var comillas="'";
    var select = document.getElementById(''+componenteSelect);
    var identificacion="";
    var contador = -1;
    var blnDiferencia = false;
    var capitulo = "";
    
	$( "#selectCapitulos option:selected" ).each(function() {
		if(identificacion != $( this ).data('identificador')){
			identificacion = $( this ).data('identificador');
			contador++;
			capitulo = $( this ).text();
			if(contador>0){
				blnDiferencia=true;
			}
		}
    });
 
    if(blnDiferencia){
    	muestraModalGeneral(3,tituloGeneral,'El Capitulo ' + capitulo + ', debe generarse en una ministración independiente.');
    }

    return blnDiferencia;
}


function fnActualizarPantallaRadicacion(folio=0, idRadicacion = 0, estatus='1'){
    //window.location.href=urlEncryption;
    $('#txtNoCaptura').text(folio);
    fnEstatusCampos(true,'disable');

    $('#txtFolioRadicacion').val(folio);
    $('#txtIdRadicacion').val(idRadicacion);
    $('#txtJustificacion').prop('disabled', false);
    $('#selectCapitulos').multiselect('enable');
    $('#btnCancelar').hide();

    fnValidacionEstatus(estatus);
}

function fnEstatusCampos(estatusInput = true, statusCombo='disable'){
	$('#PanelBusqueda input[type="text"], input[type="hidden"], textarea').each(
        function(index){  
            var input = $(this);
            input.prop('disabled', estatusInput);
        }
    );

    $('#PanelBusqueda select').each(
        function(index){  
            var combo = $(this);
            combo.multiselect(statusCombo);
        }
    );
}

function fnObtenerPresupuestoBusqueda(idPRadicacion = "") {
    muestraCargandoGeneral();
	var legalidBus = "";
	var dataJson = new Array();

	var ur = $('#selectUnidadNegocio').val();
	var ue = fnObtenerOption('selectUnidadEjecutora');
	var programaPresupuestal = $('#selectProgramaPresupuestal').val();
	var capitulo = fnObtenerOption('selectCapitulos');

	dataObj = { 
	        option: 'obtenerCapituloPartida',
	        legalid: legalidBus,
	        ur: ur,
	        ue: ue,
	        pp: programaPresupuestal,
			capitulo: capitulo,
			idRadicacion : idPRadicacion
	      };
    $.ajax({
      method: "POST",
      dataType:"json",
      url: "modelo/solicitudRadicacionModelo.php",
      data: dataObj,
      async:false,
      cache:false
    })
    .done(function( data ) {
        if(data.result) {

        	var dataJson = data.contenido.datos;
        	var listClaves ="";

        	for( var info in dataJson){
        		listClaves += "'"+dataJson[info].value+"',";
        	}

        	if(listClaves == ""){
        		muestraModalGeneral(3,tituloGeneral,'No se encontraron registros.');
        	}else{
        		listClaves = listClaves.slice(0, -1);

        		fnObtenerPresupuesto(listClaves, tablaReducciones, panelReducciones, '', 'Nuevo');
        	}
            ocultaCargandoGeneral();
        	
        }else{
            ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
        console.log( result );
        ocultaCargandoGeneral();
    });
    ocultaCargandoGeneral();
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

function fnObtenerPresupuesto(clavePresupuesto, idTabla, panel, tipoAfectacion="", nuevo="",solicitado = "0.00", autorizado="0.00",orden = "0") {

	if(fnValidarCamposObligatorio() == false){
		return false;
	}

	var tipoMovimiento = "";
	tipoMovimiento = "Radicacion";

	var transnoRadicacion = '';
	if($('#txtIdRadicacion').val() != ""){
		transnoRadicacion=$('#txtIdRadicacion').val();
	}

	//Opcion para operacion
	dataObj = { 
	        option: 'obtenerPresupuesto',
	        clave: clavePresupuesto,
			account: '',
			legalid: '',
			datosClave: '1',
			datosClaveAdecuacion: '1',
			tipoAfectacion: tipoAfectacion,
			type: '292',
			transno: transnoRadicacion,
			tipoMovimiento: tipoMovimiento,
			mes: $("#selectMesRadicacion").val()
	      };
	//Obtener datos de las bahias
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/solicitudRadicacionModelo.php",
		data:dataObj
	  })
	.done(function( data ) {
		
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datos;
	    	
    		var validacionClave = false;
	    	var clave = "";
			for (var key in info) {
				clave = info[key].accountcode;
			}
			//console.log(info);
    		fnMostrarPresupuesto(info, idTabla, panel,solicitado,autorizado, orden);
    		fnCalcularTotales();

	    	if (datosRadicados.length > 0 && usuarioOficinaCentral != 1) {
	    		// Si agrego datos deshabilitar UE
	    		$('#selectUnidadEjecutora').multiselect('disable');
	    	}
	    }else{
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+data.Mensaje+'</p>';
			muestraModalGeneral(3, titulo, mensaje);
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}

function fnObtenerArchivosRadicados(){
	
	var fd = new FormData();
	fd.append("option","obtenerArchivosRadicacion");
	fd.append("idRadicacion",$('#txtIdRadicacion').val());

	$.ajax({
	    async:false,
	    url:"modelo/solicitudRadicacionModelo.php",
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
    				//nombre = generateItem('span', {html:datosDetalleArchivos[index3].nameFile});
    				nombre = generateItem('a', {href:datosDetalleArchivos[index3].urlFile,target:'_blank', html:datosDetalleArchivos[index3].nameFile});
			        cols.push(generateItem('td', {
			            style: estilo
			        }, nombre));

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


function fnValidarCamposObligatorio(){

	var errMensaje="";
	var ur =fnObtenerOption('selectUnidadNegocio',1);
	var ue =fnObtenerOption('selectUnidadEjecutora',1);
	var pp =fnObtenerOption('selectProgramaPresupuestal',1);
	var cpt =fnObtenerOption('selectCapitulos',1);
	var mes =fnObtenerOption('selectMesRadicacion',1);

	var fechaActual = new Date();
	var mesActual = fechaActual.getMonth();

	if(ur == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo UR es obligatorio.</p>';
	}

	if(ue == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo UE es obligatorio.</p>';
	}

	if(pp == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Programa Presupuestal es obligatorio.</p>';
	}

	if(cpt == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Capitulo es obligatorio.</p>';
	}

	if(mes == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Mes es obligatorio.</p>';
	}

	// if(parseFloat(mes) < parseFloat(mesActual)){
	// 	errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El mes debe ser posterior o igual al mes actual.</p>';
	// }

	if(errMensaje!=""){
		muestraModalGeneral(3,tituloGeneral,errMensaje);
		return false;
	}else{
		return true;
	}

} 

function fnValidarCamposObligatorioGuardar(){

	var errMensaje="";
	var ur =fnObtenerOption('selectUnidadNegocio',1);
	var ue =fnObtenerOption('selectUnidadEjecutora',1);
	var pp =fnObtenerOption('selectProgramaPresupuestal',1);
	var cpt =fnObtenerOption('selectCapitulos',1);
	var mes =fnObtenerOption('selectMesRadicacion',1);
	var justificacion =$('#txtJustificacion').val();
	// var fechaPago =$('#datePago').val();

	var fechaActual = new Date();
	var mesActual = fechaActual.getMonth();

	if(ur == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo UR es obligatorio.</p>';
	}

	if(ue == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo UE es obligatorio.</p>';
	}

	if(pp == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Programa Presupuestal es obligatorio.</p>';
	}

	if(cpt == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Capitulo es obligatorio.</p>';
	}

	if(mes == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Mes es obligatorio.</p>';
	}

	// if(parseFloat(mes) < parseFloat(mesActual)){
	// 	errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El mes debe ser posterior o igual al mes actual.</p>';
	// }

	if(justificacion == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo de Justificación es obligatorio.</p>';
	}

	// if(fechaPago == ""){
	// 	errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo de Fecha Pago es obligatorio.</p>';
	// }

	if(errMensaje!=""){
		muestraModalGeneral(3,tituloGeneral,errMensaje);
		return false;
	}else{
		return true;
	}

} 

function fnValidarClave(clave, dataJson, panel, mensaje) {
	for (var key in dataJson) {
		for (var key2 in dataJson[key]) {
			var dataJson2 = dataJson[key];
			//console.log("datos: "+JSON.stringify(dataJson2[key2]));
			if (dataJson2[key2].accountcode == clave) {
				muestraMensaje(mensaje, 3, 'divMensajeOperacionReducciones', 5000);
				return false;
			}
		}
	}
	
	return true;
}

function fnMostrarPresupuesto(dataJson, idTabla, panel,solicitado="0.00",autorizado="0.00",orden="0") {
	var encabezado = '';
	var contenido = '';
	var enca = 0;
	var style = 'style="text-align:center;"';
	var styleMeses = 'style="text-align:center;"';
	var nombreSelect = "";
	var tipoAfectacion = "";
	var clavePresupuesto = "";
	var solicitado = "0.00";
    var cssBtonEliminar="";
    var id_radicacion = $('#txtIdRadicacion').val();
    var contenido2 = "";

	idEstatus = $("#txtIdStatusRadicacion").val();

	$('#tablaReducciones >tbody').empty();
	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!                                               !!
    //!!        Validaciones para usuarios.            !!
    //!!                                               !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    if (parseFloat(idEstatus) >= 2 && permisoCapturista ==1) {
        cssBtonEliminar = "style = 'display: none;'";
    }

    if (parseFloat(idEstatus) >= 3 && permisoValidador ==1) {
        cssBtonEliminar = "style = 'display: none;'";
    }

    if (parseFloat(idEstatus) >= 5) {
        cssBtonEliminar = "style = 'display: none;'";
    }

	numLineaReducciones=1;
	for (var key in dataJson) {

		tipoAfectacion = dataJson[key].tipoAfectacion;
		clavePresupuesto = dataJson[key].accountcode;

		var total = 0;
		for (var mes in dataJsonMeses ) {
			// Nombres de los mes
			var nombreMes = dataJsonMeses[mes];
			total = parseFloat(total) + parseFloat(dataJson[key][nombreMes+"Sel"]);
		}

		// if (idClavePresupuestoReducciones != dataJson[key].idClavePresupuesto) {
		// 	idClavePresupuestoReducciones = dataJson[key].idClavePresupuesto;
		// 	enca = 0;
		// }
		totalReducciones += parseFloat(total);

		if (enca == 0) {
			encabezado += '<tr class="header-verde"><td style="text-align:center;">#</td><td '+cssBtonEliminar+'></td>';
		}

		var ordenSel=orden;
		if(orden == 0){
			ordenSel = numLineaReducciones;
		}

		contenido += '<td style="text-align:center;">'+numLineaReducciones;
		contenido += '	<component-number style = "width:60px; display:none;" id="filaOrden'+numLineaReducciones+'" name="filaOrden'+numLineaReducciones+'"  class="filaOrden" value="'+ordenSel+'" readonly></component-number>';
		contenido += '</td>';

		// if (autorizarGeneral == 1 || (tipoSuficiencia != 2 && tipoSuficiencia != 0)) {
		// 	contenido += '<td></td>';
		// }else{
		// 	contenido += '<td><button class="glyphicon glyphicon-remove btn-xs btn-danger" onclick="fnPresupuestoEliminar(this)"></button></td>';

		// }

		if (cssBtonEliminar != "") {
			contenido += '<td '+cssBtonEliminar+'></td>';
		}else{
			contenido += '<td><button class="glyphicon glyphicon-remove btn-xs btn-danger" onclick="fnPresupuestoEliminar(this)"></button></td>';
		}

		var deshabilitarElemento = '';
		if (autorizarGeneral == 1 || (tipoSuficiencia != 2 && tipoSuficiencia != 0)) {
			deshabilitarElemento = ' disabled="true" ';
		}

		//Cargar datos presupuesto
		
		for (var key2 in dataJson[key].datosClave) {
			
			if (enca == 0) {
				encabezado += '<td '+style+'>'+dataJson[key].datosClave[key2].nombre+'</td>';
			}
			
			contenido += '<td '+style+'>'+dataJson[key].datosClave[key2].valor+'</td>';
			
		}


		var nombreInputMeses = dataJson[key].accountcode+"_"+panel+"_"; // No cambiar estructura de nombre o cambiar tambien en fnGuardarSeleccionado()

		
		for (var mes in dataJsonMeses ) {
			// Informacion meses para seleccion
			var nombreMes = dataJsonMeses[mes];
			var nombreMesSel = dataJsonMeses[mes]+"Sel";
			var nombreMesCompra = dataJsonMeses[mes]+"Compra";
			var informacionCancelar = "";
			var styleOcultarMes = 'style="text-align:center;"';
			var styleInputText = ' style="width: 120px; text-align:right; margin-right:-30px;" ';

			if (nuRequisicionSuf != '0') {
				// Si tiene requisicion habilitar el mes de la requisicion
				styleOcultarMes = 'style="text-align:center; display: none;"';
				
				if (Math.abs(dataJson[key][nombreMesCompra]) != '0') {
					styleOcultarMes = 'style="text-align:center; align-content: center;"';
					styleInputText = ' style="width: 100px;" ';
					informacionCancelar += " <br> Debe Ser $ "+Math.abs(dataJson[key][nombreMesCompra]);
				}
			}

			
			if(mes == (parseFloat($('#selectMesRadicacion').val())-1)){
				
				if (enca == 0) {
					// Nombres de los mes para el encabezado
					encabezado += '<td '+styleOcultarMes+'>Liberado</td>';
					encabezado += '<td '+styleOcultarMes+'>Solicitud<br> '+nombreMes+'</td>';
					encabezado += '<td '+styleOcultarMes+'>Autorizado</td>';
				}

				contenido += '<td style="text-align:right;">$ '+fnFormatoNumeroMX(dataJson[key][nombreMes])+' 	<component-decimales  style = "display:none;" class="filaPresupuestoOriginal" name="filaPresupuestoOriginal'+numLineaReducciones+'" id="filaPresupuestoOriginal'+numLineaReducciones+'" value="'+dataJson[key][nombreMes]+'" placeholder="0.00" readonly></component-decimales></td>';

				//Se agrega por default lo que se tiene en el modificado, cuando la solicitud es nueva 
				//al obtener la Radicacion que se consulta se cambian los valores 

				if($('#txtIdRadicacion').val() == ""){
					solicitado =dataJson[key][nombreMes];
				}

				contenido += '<td style="text-align:right;">';
				contenido += '	<component-decimales '+styleInputText+deshabilitarElemento+' class="filaPresupuestoSolicitud" onblur = "fnCalcularTotales(\'filaPresupuestoSolicitud\')" name="'+nombreInputMeses+nombreMes+'" id="'+dataJson[key].accountcode+'" data-idline="'+numLineaReducciones+'" value="'+(solicitado)+'" placeholder="0.00" ></component-decimales>';
				contenido += '</td>';

				if(permisoAutorizador == "1"){
					contenido += '<td style="text-align:right;">';
					contenido += '	<component-decimales '+styleInputText+deshabilitarElemento+' class="filaPresupuestoAutorizado" onblur = "fnCalcularTotales(\'filaPresupuestoAutorizado\')" name="'+nombreInputMeses+nombreMes+'" id="filaAutorizado'+numLineaReducciones+'" data-idline="'+numLineaReducciones+'" value="'+(autorizado)+'" placeholder="0.00" ></component-decimales>';
					contenido += '</td>';
				}else{
					contenido += '<td style="text-align:right;">';
					contenido += '	<component-decimales '+styleInputText+deshabilitarElemento+' class="filaPresupuestoAutorizado" onblur = "fnCalcularTotales(\'filaPresupuestoAutorizado\')" name="'+nombreInputMeses+nombreMes+'" id="filaAutorizado'+numLineaReducciones+'" data-idline="'+numLineaReducciones+'" value="'+(autorizado)+'" placeholder="0.00" readonly ></component-decimales>';
					contenido += '</td>';
				}

				if (enca == 0) {
					encabezado += '</tr>';
				}
			}
		}

		//contenido = '<td id="Renglon_'+dataJson[key].accountcode+'_'+panel+'" name="Renglon_'+dataJson[key].accountcode+'_'+panel+'">'+numLineaReducciones+'</td>' + contenido;
		//contenido = contenido;
		numLineaReducciones = parseFloat(numLineaReducciones) + 1;
		styleRenglonNuevo="";
		if(id_radicacion !=""){
			if(solicitado == "0.00" || solicitado==""){
				//console.log(id_ministracion +' '+solicitado);
				styleRenglonNuevo='style="background-color:#FFFDE7;"';
			}
		}	

		contenido2 +=  '<tr '+styleRenglonNuevo+' class ="classFila" id="RenglonTR_'+dataJson[key].accountcode+'_'+panel+'" name="RenglonTR_'+dataJson[key].accountcode+'_'+panel+'" >' + contenido + '</tr>';
		contenido="";
		//contenido =  '<tr '+styleRenglonNuevo+' class ="classFila" id="RenglonTR_'+dataJson[key].accountcode+'_'+panel+'" name="RenglonTR_'+dataJson[key].accountcode+'_'+panel+'" >' + contenido + '</tr>';

		enca = 1;
	}

	
	$('#'+idTabla+' tbody').append(encabezado+contenido2);

	//fnEjecutarVueGeneral('RenglonTR_'+clavePresupuesto+'_'+panel);
	fnEjecutarVueGeneral('divContenidoRadicacion');

	if (tipoSuficiencia == 2) {
		// Si es manual
		fnDeshabesDesActualRed();
	}
}

function fnPresupuestoEliminar(btnEliminar){
	var trRowPadre = btnEliminar.parentNode.parentNode;
	var trRowIndex = trRowPadre.rowIndex;
	var tabla  = trRowPadre.parentNode; 
	tabla.deleteRow(trRowIndex);

    fnCalcularTotales();
}

function fnCalcularTotales( inpCampoMonto= "filaPresupuestoSolicitud"){
    var total="0.00";

    $("#tablaReducciones").find("." + inpCampoMonto).each(function(index){
        if(!isNaN($(this).val())){
            total =  parseFloat(total) + parseFloat($(this).val());
        }
    });

    fnMostrarTotalAmpRed('txtTotalReducciones', total);
}

function fnMostrarTotalAmpRed(divNombre, total) {
	$('#'+divNombre).empty();
	$('#'+divNombre).html(""+fnFormatoNumeroMX(total));
}

// function fnFormatoNumeroMX(monto){
//     var strMonto="0.00";
	
// 	if(!isNaN(monto)){
// 	    if(parseFloat(monto) == '0' || monto ==""){
// 	        strMonto = "0.00";
// 	    }else{
// 	        strMonto = new Intl.NumberFormat('es-MX').format(parseFloat(monto));
// 	    }
// 	}
	
// 	return strMonto;
// }

function fnFormatoNumeroMX(monto,decimales=2){
    var strMonto="0.00";
	
	if(!isNaN(monto)){
	    if(parseFloat(monto) == '0' || monto ==""){
	        strMonto = "0.00";
	    }else{
	        strMonto = new Intl.NumberFormat('es-MX').format(parseFloat(monto));
	    }
	}

	if(strMonto != "0.00" && strMonto != ""){
		var arrSTRMonto = strMonto.split('.');
		var entero = arrSTRMonto[0];
		var decimal = arrSTRMonto[1];
		var strDecimal = "";

		if(arrSTRMonto[1]){
			if(decimal.length >= decimales){
				strDecimal = decimal.substring(0, decimales);
			}else{
				strDecimal = decimal;
				for (var i = decimal.length; i <= decimales -1; i++) {
					strDecimal = strDecimal +"0";
				}
			}
	    }else{
	    	for (var i = 0; i <= decimales-1; i++) {
				strDecimal = strDecimal +"0";
			}
	    }

	    strMonto = entero+"."+strDecimal;
	}

	return strMonto;
}

function fnDeshabesDesActualRed() {
	// Deshabilita meses despues del actual en Reducciones
	var mensaje = "";
	for (var key in datosRadicados ) {
		for (var key2 in datosRadicados[key]) {
			var dataJsonReducciones = datosRadicados[key];
			var numMes = 1;

			for (var mes in dataJsonMeses ) {
                var nombreMes = dataJsonMeses[mes];
                var nombreMesSel = dataJsonMeses[mes]+"Sel";
                
                if (autorizarGeneral == 1) {
                	// Si se va autorizar o esta autorizado
                	$("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).prop("disabled", true);
                } else if (Number(mesActualAdecuacion) < Number(numMes)) {
                	totalReducciones = parseFloat(totalReducciones) - parseFloat(dataJsonReducciones[key2][nombreMesSel]);
                	fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
					dataJsonReducciones[key2][nombreMesSel] = 0;
					$("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).val("0");
					$("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).prop("disabled", true);
				}else{
					$("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).prop("disabled", false);
				}
				numMes ++;
            }

		}
	}

	return mensaje;
}

function fnValidarSolicitado(){

	var lineOriginal = "";
	var montoOriginal="";
	var montoSolicitado="";
	var montoAutorizado="";
	var errMensaje ="";

	var numError1="";
	var contadorError1=0;
	var numError2="";
	var contadorError2=0;
	var numError3="";
	var contadorError3=0;
	var numError4="";
	var contadorError4=0;

	$("#tablaReducciones").find(".filaPresupuestoSolicitud").each(function(index){	
		lineOriginal = $(this).data("idline");
		montoOriginal = $('#filaPresupuestoOriginal'+lineOriginal).val();
		montoAutorizado = $('#filaAutorizado'+lineOriginal).val();
		montoSolicitado = $(this).val();

		if(parseFloat(montoSolicitado) > parseFloat(montoOriginal)){
			numError1 += (index + 1)+',';
			contadorError1++;
		}

		if(parseFloat(montoSolicitado) > 0){
			contadorError2++;
		}

		if(parseFloat(montoAutorizado) > parseFloat(montoSolicitado)){
			numError3 += (index + 1)+',';
			contadorError3++;
		}

		if(parseFloat(montoAutorizado) < 0){
			numError4 += (index + 1)+',';
			contadorError4++;
		}

	});

	if(numError1 != ""){
		numError1 = numError1.slice(0, -1);
		if(contadorError1 =="1"){
			errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En el renglon '+numError1+', la cantidad a solicitar es mayor a la cantidad disponible.</p>';
		}else{
			errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En los renglones '+numError1+', la cantidad a solicitar es mayor a la cantidad disponible.</p>';
		}
	}

	if(contadorError2 == 0){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No existe ninguna solicitud capturada.</p>';
	}

	if(numError3 != ""){
		numError3 = numError3.slice(0, -1);
		if(contadorError3 =="1"){
			errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En el renglon '+numError3+', la cantidad a autorizar debe ser menor o igual a la cantidad solicitada.</p>';
		}else{
			errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En los renglones '+numError3+', la cantidad a autorizar debe ser menor o igual a la cantidad solicitada.</p>';
		}
	}

	if(numError4 != ""){
		numError4 = numError4.slice(0, -1);
		if(contadorError4 =="1"){
			errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En el renglon '+numError4+', la cantidad a autorizar debe ser mayor o igual a 0.</p>';
		}else{
			errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En los renglones '+numError4+', la cantidad a autorizar debe ser mayor o igual a 0.</p>';
		}
	}

	if(errMensaje!=""){
		muestraModalGeneral(3,tituloGeneral,errMensaje);
		return false;
	}else{
		return true;
	}

}

function fnValidarCamposObligatorio(){

	var errMensaje="";
	var ur =fnObtenerOption('selectUnidadNegocio',1);
	//var ue =fnObtenerOption('selectUnidadEjecutora',1);
	var pp =fnObtenerOption('selectProgramaPresupuestal',1);
	var cpt =fnObtenerOption('selectCapitulos',1);
	var mes =fnObtenerOption('selectMesRadicacion',1);
	

	var fechaActual = new Date();
	var mesActual = fechaActual.getMonth();

	if(ur == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo UR es obligatorio.</p>';
	}

	/*Se comento ya que la ministracion se hace sobre la oficina central*/

	// if(ue == ""){
	// 	errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo UE es obligatorio.</p>';
	// }

	if(pp == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Programa Presupuestal es obligatorio.</p>';
	}

	if(cpt == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Capitulo es obligatorio.</p>';
	}

	if(mes == ""){
		errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Mes es obligatorio.</p>';
	}


	// if(parseFloat(mes) < parseFloat(mesActual)){
	// 	errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El mes debe ser posterior o igual al mes actual.</p>';
	// }

	if(errMensaje!=""){
		muestraModalGeneral(3,tituloGeneral,errMensaje);
		return false;
	}else{
		return true;
	}

} 


function fnObtenerFirmantes(){

    if($("#selectUnidadNegocio").val() == '-1'){
        return false;
    }

    if($("#selectUnidadEjecutora").val() == '-1'){
        return false;
    }

    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/componentes_modelo.php",
        data:{option:'obtenerFirmantesRadicacion',ur:$("#selectUnidadNegocio").val(),ue:$("#selectUnidadEjecutora").val()}
    }).done(function(res){
        if(!res.result){return;}
        var options='';
        var dataJson = res.contenido.datos;
        var selected = '';

        $('#selectFirmaRadicacion').empty();
        
        if (dataJson.length == 1) {
            selected='selected';
        }

        options += "<option value='-1'>Seleccionar...</option>";

        $.each(res.contenido.datos,function(index, el) {
            options += '<option value="'+ el.id +'" '+el.default+' '+selected+'>'+ el.descripcion +'</option>';
        });
        $('#selectFirmaRadicacion').append(options);
        $('#selectFirmaRadicacion').multiselect('rebuild');

    }).fail(function(res){
        throw new Error('No se logro cargar la información del componente de firmas');
    });
}


