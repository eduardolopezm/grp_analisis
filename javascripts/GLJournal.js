/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jesus Reyes Santos
 * @version 0.1
 */
window.buscadoresID = new Array();
window.buscadoresTexto = new Array();

window.camposListado = [ "selectGLNarrative" ];

prepararLista();


// Datos de la busqueda en Reduccion y Ampliacion
var datosPresupuestosBusqueda = {};



$( document ).ready(function() {
	
});


/**
 * Función para obtener la claves presupuestales y cargar la la lista en la caja de texto de busqueda
 * @param  {String} panel [description]
 * @return {[type]}       [description]
 */
function fnObtenerPresupuestoBusqueda(panel="") {
	// console.log("fnObtenerPresupuestoBusqueda iskls");
	// Obtener claves de busqueda
	// if (type != '294') {
	// 	// Si no es directo no obtener claves
	// 	return true;
	// }

	var legalidBus = "";
	var tagrefBus = $('#selectUnidadNegocio').val();
	var concR23 = "";
	var dataJson = new Array();

	var ueBus = $('#selectUnidadEjecutora').val();

	dataObj = { 
	        option: 'obtenerPresupuestosBusqueda',
	        legalid: legalidBus,
	        tagref: tagrefBus,
	        ue: ueBus,
	        type: type,
			transno: transno,
	        filtrosClave: dataJson
	      };
    $.ajax({
      method: "POST",
      dataType:"json",
      url: "modelo/pagosModelo.php",
      data: dataObj
    })
    .done(function( data ) {
        //console.log(data);
        if(data.result) {
            //console.log("datosPresupuestosBusqueda: "+JSON.stringify(datosPresupuestosBusqueda));
            datosPresupuestosBusqueda = data.contenido.datos;
            fnBusquedaReduccion(datosPresupuestosBusqueda);
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}

/**
 * Función para cargar los datos de las claves a la caja de texto de busqueda
 * @param  {[type]} jsonData Json con información de las claves
 * @return {[type]}          [description]
 */
function fnBusquedaReduccion(jsonData) {
	$( "#txtBuscarReducciones").autocomplete({
        source: jsonData,
        select: function( event, ui ) {
            
            $( this ).val( ui.item.accountcode + "");
            $( "#txtBuscarReducciones" ).val( ui.item.accountcode );
            // $( "#txtBuscarReducciones" ).val( "" );

            //datosPresupuestosBusqueda = { budgetid: ui.item.budgetid, accountcode: ui.item.accountcode, valorLista: ui.item.valorLista};

            return false;
        }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {

		return $( "<li>" )
		.append( "<a>" + item.valorLista + "</a>" )
		.appendTo( ul );

    };
}

var tipoCargo ="";
$('#tipoCargo').change(function(){

 tipoCargo = $(this).val();

 if(tipoCargo == 'preEgresosCargo'){
 // 	// $('#fechaFinal').hide(); //oculto mediante id
 // 	// $('#fechaInicio').hide();
 document.getElementById('divPreEgresosCargo').style.display = 'block';
 document.getElementById('divPreIngresosCargo').style.display = 'none';
 document.getElementById('divContableCargo').style.display = 'none';
 // $('#txtFechaInicial').val("");
 // $('#txtFechaFinal').val("");	
 }
         
 if(tipoCargo == 'preIngresoCargo'){
 
 document.getElementById('divPreEgresosCargo').style.display = 'none';
 document.getElementById('divPreIngresosCargo').style.display = 'block';
 document.getElementById('divContableCargo').style.display = 'none';
 // $('#txtFechaInicial').val(""+fechaIni);
 // $('#txtFechaFinal').val(""+fechaFin);	
 }
 
 if(tipoCargo == 'contableCargo'){
     
 document.getElementById('divPreEgresosCargo').style.display = 'none';
 document.getElementById('divPreIngresosCargo').style.display = 'none';
 document.getElementById('divContableCargo').style.display = 'block';
 // $('#txtFechaInicial').val(""+fechaIni);
 // $('#txtFechaFinal').val(""+fechaFin);	
 }
 
 if(tipoCargo == 'vacio'){
     
     document.getElementById('divPreEgresosCargo').style.display = 'none';
     document.getElementById('divPreIngresosCargo').style.display = 'none';
     document.getElementById('divContableCargo').style.display = 'none';
     // $('#txtFechaInicial').val(""+fechaIni);
     // $('#txtFechaFinal').val(""+fechaFin);	
 }
 
 
});

/**
 * Si cambia de tipo de poliza para mostrar descripcion por panel
 * @param  object Recibe el select para ver la opción
 * @return {[type]}               [description]
 */
function fnCambioTipoPoliza(cmbTipoPoliza){
    // Cambio el tipo de poliza
    var btnConsultar = document.getElementById('btnCambioTipoPoliza');
    btnConsultar.click();
    return true;
    
    if (cmbTipoPoliza.value == '282') {
        // Poliza extra presupuestal
        document.getElementById('divCuentaCargoDiario').style.display = "none";
        document.getElementById('divCuentaAbonoDiario').style.display = "none";
        document.getElementById('divDescripcionDiario').style.display = "none";

        document.getElementById('divCuentaCargoExtraPre').style.display = "block";
        document.getElementById('divCuentaAbonoExtraPre').style.display = "block";
        document.getElementById('divDescripcionExtraPre').style.display = "block";

        $('#panelTitulo').html("Póliza Extra Presupuestal");
    }else{
        // Poliza normal
        document.getElementById('divCuentaCargoDiario').style.display = "block";
        document.getElementById('divCuentaAbonoDiario').style.display = "block";
        document.getElementById('divDescripcionDiario').style.display = "block";

        document.getElementById('divCuentaCargoExtraPre').style.display = "none";
        document.getElementById('divCuentaAbonoExtraPre').style.display = "none";
        document.getElementById('divDescripcionExtraPre').style.display = "none";

        $('#panelTitulo').html("Póliza Diario");
    }
}

function fnObtenerDatosExtraPres(selectExtraPre){
    // Cambio descripción extra presupuestal
    // console.log("selectExtraPre: "+selectGLNarrative.value);

    dataObj = {
        clave: selectGLNarrative.value,
        option: 'obtenerCuentas'
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/GLJournal_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        if(data.result){
            $("#GLCodeExtraPre").val(""+data.contenido.cuentaCargo);
            $("#GLCodeAbonoExtraPre").val(""+data.contenido.cuentaAbono);
        }else{
            $("#GLCodeExtraPre").val("");
            $("#GLCodeAbonoExtraPre").val("");

            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ocurrio un problema al obtener la información</p>');
        }
    })
    .fail(function(result) {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ocurrio un problema al obtener la información</p>');
    });
}

$( document ).ready(function() {
    if($("#cmbTipoPoliza").val()=='282'){
        fnComprobarDescripcionExtrapresupuestal();
        $('#tag, #selUE, #cmbProgramaPresupuestario').on('change',function(){
            fnComprobarDescripcionExtrapresupuestal();
        });
    }

    if($("#cmbTipoPoliza").val()=='120'){
        // Si es corte de caja, no se puede cambiar
        // $('#tag').multiselect('disable');
        // $('#selUE').multiselect('disable');
        $('#cmbTipoPoliza').multiselect('disable');
    }

    $('#mdlDeleteFile').on('show.bs.modal',function(event){
        button = $(event.relatedTarget); // Button that triggered the modal
        idfile = button.data('id');
        file = button.data('file');

        $("#txtidfile").val('');
        $("#txtidfile").val(idfile);

        $('#namefile').text('');
        $('#namefile').text('Nombre del archivo: '+file);
    });

    $('#btnDeleteFile').click(function (){
        dataObj = {
                idFile: $('#txtidfile').val(),
                option: 'DeleteFile'
            };
            $.ajax({
                method: "POST",
                dataType:"json",
                url: "GLJournalV2_Model.php",
                data:dataObj
            })
            .done(function( data ) {
                // console.log(data);
                if(data.result){
                    $('#trfile_'+$('#txtidfile').val()).remove();
                    alert('Se elimino correctamente');
                    
                }
            })
            .fail(function(result) {
                console.log('fue error:')
            });

    });

    // comportamiento de inputs de búsqueda
    $(".campoListado").focusout(function(event){
        if($('#tag').val()=="-1"||$('#selUE').val()=="-1"||$('#cmbProgramaPresupuestario').val()=="-1"){
            return;
        }
        identificador = $('#tag').val()+"-"+$('#selUE').val()+"-"+$('#cmbProgramaPresupuestario').val();

        id = $(this).attr('id').replace("textoVisible__","");
        if($(this).val()!=$("#"+id).val()){
            var buscarCoicidencia = new RegExp('^'+$(this).val()+'$' , "i"),
                textoCuenta = "";

            var arr = jQuery.map(window.buscadoresTexto[id][identificador], function (value,index) {
                return value.match(buscarCoicidencia) ? index : null;
            });
            textoCuenta = window.buscadoresID[id][identificador][arr[0]];

            if(arr.length){
                $("#"+id).val(textoCuenta);
                $("#textoOculto__"+id).val($(this).val());
                fnObtenerDatosExtraPres();
            }else{
                $("#"+id).val("");
                $("#textoOculto__"+id).val("");
                $(this).val($("#"+id).val());
                $("#GLCodeExtraPre").val("");
                $("#GLCodeAbonoExtraPre").val("");
            }
        }
    });
    $(".campoListado").keyup(function(){
        if($('#tag').val()=="-1"||$('#selUE').val()=="-1"||$('#cmbProgramaPresupuestario').val()=="-1"){
            return;
        }
        identificador = $('#tag').val()+"-"+$('#selUE').val()+"-"+$('#cmbProgramaPresupuestario').val();

        id          = $(this).attr('id');
        idBuscador  = "#"+id;
        id          = id.replace("textoVisible__","");
        idHidden    = "#"+id;
        idDiv       = "#sugerencia-"+id;
        id          = idHidden.replace("#","");

        if($(this).val()!=''){
            var buscar = $(this).val(); 
            var retorno = "<ul id='articulos-lista-consolida'>";
            var buscarCoicidencia = new RegExp('^'+buscar , "i");

            var arr = jQuery.map(window.buscadoresTexto[id][identificador], function (value,index) {
                return value.match(buscarCoicidencia) ? index : null;
            });

            for(a=0;a<arr.length;a++){
                val = arr[a];
                retorno+="<li onClick='fnSelectCuenta(\""+window.buscadoresID[id][identificador][val]+"\",\""+idDiv+"\",\""+idBuscador+"\",\""+idHidden+"\",\""+window.buscadoresTexto[id][identificador][val]+"\")'><a href='#'>"+window.buscadoresTexto[id][identificador][val]+"</a></li>";
            }

            retorno+="</ul>";

            $.each(camposListado,function(index, valor){
                if(idDiv!="#sugerencia-"+valor){
                    $("#sugerencia-"+valor).hide();
                    $("#sugerencia-"+valor).empty();
                }
            });

            $(idDiv).show();
            $(idDiv).empty();
            $(idDiv).append(retorno);
        }else{
            $(idDiv).hide();
            $(idDiv).empty();
        }
    });
    $("body").click(function(evt){
        if(evt.target.id.substr(0,8)=="textoVisible__"||evt.target.id.substr(0,11)=="sugerencia-"){
            var divActivo = "";
            divActivo = ( evt.target.id.substr(0,8)=="textoVisible__" ? evt.target.id.substr(8) : divActivo );
            divActivo = ( evt.target.id.substr(0,11)=="sugerencia-" ? evt.target.id.substr(11) : divActivo );
            $.each(camposListado,function(index, valor){
                if($("#sugerencia-"+valor).is(":visible")&&valor!=divActivo){
                    $("#sugerencia-"+valor).hide();
                    $("#sugerencia-"+valor).empty();
                }
            });
            return;
        }
        $.each(camposListado,function(index, valor){
            if($("#sugerencia-"+valor).is(":visible")){
                $("#sugerencia-"+valor).hide();
                $("#sugerencia-"+valor).empty();
            }
        });
    });
});


 $(document).on('keypress', '.decimalesCl', function(event) {

    var text = $(this).val();
    var cuenta = (text.match(/./g) || []).length;
    //cuenta.match( new RegExp('.','g') ).length;
    //var cuenta = /\.{1}\.+/g.test(text);
    // console.log("->"+cuenta+" ");
    if(text.includes(".")){

      var texto2=[];
      texto2=(text.split("."));
       //despues del punto solo 2 con el length. y con el or includes. la primera parte ya tiene  punto decimal
      if (texto2[1].length >1 || texto2[0].includes(".")) {
              event.preventDefault();
      }
    }    

 });

function fnEliminarArchivoSel(idFile, nameFile) {
    //console.log("idFile: "+idFile+" - nameFile: "+nameFile);
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
    var mensaje = '<h4>Se va a eliminar el Archivo '+nameFile+'</h4>';
    muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnEliminarArchivoSeleccion(\''+idFile+'\')');
}

function fnEliminarArchivoSeleccion(idFile) {
    //console.log("eliminar "+idFile);
    dataObj = {
        idFile: idFile,
        option: 'DeleteFile'
    };
    $.ajax({
        method: "POST",
        dataType:"json",
        url: "modelo/GLJournal_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        // console.log(data);
        if(data.result){
            $('#trfile_'+idFile).remove();
        }
    })
    .fail(function(result) {
        console.log('fue error:')
    });
}

function Abrir_ventana(pagina) {
    var opciones="toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=yes, width=508, height=365, top=85, left=140";
    window.open(pagina,"",opciones);
}

function fnValidarDatosFormulario() {
    //evt.preventDefault();  
    var cuentaCargo = $('#GLCode').val();
    var cuentaAbono = $('#GLCodeAbono').val();
    var debit = $('#Debit').val();
    var credit = $('#Credit').val();
    var mensaje = "";
    var cmbTipoPoliza = $('#cmbTipoPoliza').val();
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

    if ($("#tag").val() == '-1') {
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Es necesario seleccionar una Unidad Responsable</p></h5>';
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if ($("#selUE").val() == '-1') {
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Es necesario seleccionar una Unidad Ejecutora</p></h5>';
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if((typeof cuentaCargo === 'undefined' && typeof cuentaAbono === 'undefined') && cmbTipoPoliza != '282') {
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Es necesario seleccionar una cuenta para continuar con la póliza</p></h5>';
        //alert("Error vacio");
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if ((cuentaCargo == 0 && cuentaAbono == 0) && cmbTipoPoliza != '282') {
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Es necesario seleccionar una cuenta para continuar con la póliza</p></h5>';
        //alert("Error vacio");
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if (cuentaCargo == cuentaAbono && cmbTipoPoliza != '282') {
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Las cuentas no deben ser iguales</p></h5>';
        //alert("Error vacio");
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if (((cuentaCargo != '' || cuentaCargo != 0) && debit == '') && (cuentaAbono == 0 && credit == '')) {
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Es necesario agregar la Cantidad para el Cargo para continuar con la póliza</p></h5>';
        //alert("Error vacio");
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if (((cuentaAbono != '' || cuentaAbono != 0) && credit == '') && (cuentaCargo == 0 && debit == '')){
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Es necesario agregar la Cantidad para el Abono para continuar con la póliza</p></h5>';
        //alert("Error vacio");
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if (((cuentaCargo != '' || cuentaCargo != 0) && debit == '') && (cuentaAbono == 0 && credit > 0)) {
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Es necesario agregar la Cantidad para el Cargo para continuar con la póliza</p></h5>';
        //alert("Error vacio");
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if (((cuentaAbono != '' || cuentaAbono != 0) && credit == '') && (cuentaCargo == 0 && debit > 0)){
        mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Es necesario agregar la Cantidad para el Abono para continuar con la póliza</p></h5>';
        //alert("Error vacio");
        muestraModalGeneral(3, titulo, mensaje);
        return false;
    }else if (cmbTipoPoliza == '282') {
        // Validacion extra presupuestal
        var cuentaCargoExtraPre = $("#GLCodeExtraPre").val();
        var cuentaAbonoExtraPre = $("#GLCodeAbonoExtraPre").val();
        if (!fnValidarSiEsNumero(debit)) {
            // Validar si es numero
            mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Solo números en la Cantidad del Cargo</p></h5>';
            muestraModalGeneral(3, titulo, mensaje);
            return false;
        }else if (!fnValidarSiEsNumero(credit)) {
            // Validar si es numero
            mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Solo números en la Cantidad del Abono</p></h5>';
            muestraModalGeneral(3, titulo, mensaje);
            return false;
        }else if (cuentaCargoExtraPre.trim() == '') {
            // Cuenta Cargo esta vacia
            mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Sin cuenta de Cargo</p></h5>';
            muestraModalGeneral(3, titulo, mensaje);
            return false;
        }else if (cuentaAbonoExtraPre.trim() == '') {
            // Cuenta Cargo esta vacia
            mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Sin cuenta de Abono</p></h5>';
            muestraModalGeneral(3, titulo, mensaje);
            return false;
        }
    }

    if (cmbTipoPoliza != '282' && cuentaCargo != '' && cuentaCargo != 0) {
        // Poliza de Diario Validar que sea en el ultimo nivel
        if (fnValidarCuentaUltimoNivel(cuentaCargo.trim()) == '0') {
            mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> La cuenta '+cuentaCargo+' no es el último nivel</p></h5>';
            muestraModalGeneral(3, titulo, mensaje);
            return false;
        }
    }
    if (cmbTipoPoliza != '282' && cuentaAbono != '' && cuentaAbono != 0) {
        // Poliza de Diario Validar que sea en el ultimo nivel
        if (fnValidarCuentaUltimoNivel(cuentaAbono.trim()) == '0') {
            mensaje = '<h5><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> La cuenta '+cuentaAbono+' no es el último nivel</p></h5>';
            muestraModalGeneral(3, titulo, mensaje);
            return false;
        }
    }

    // Agregar información
    var btnConsultar = document.getElementById('Process');
    btnConsultar.click();
}

/**
 * Función para validar el nivel de la cuenta, si es el ultimo nivel retorna true de lo contrario false
 * @param  string Cuenta Contable
 * @return boolean Si la cuenta es el ultimo nivel muestra true de lo contrario false
 */
function fnValidarCuentaUltimoNivel(cuenta) {
    // Validar nivel
    // console.log("cuenta: "+cuenta);
    var respuesta = 0;
    dataObj = {
        cuenta: cuenta,
        option: 'ultimoNivelCuenta'
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/GLJournal_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        if(data.result){
            // console.log("result: "+data.contenido.ultimoNivel);
            respuesta = data.contenido.ultimoNivel;
        }else{
            respuesta = 0;
        }
    })
    .fail(function(result) {
        respuesta = 0;
    });

    return respuesta;
}

var validarIdentificador = '<?php echo $validarIdentificador; ?>';

function fnCambioDatosIdentificador() {
    if($("#cmbTipoPoliza").val()=="282"){
        return false;
    }
    // Cargar Cuentas para abono y cargo de acuerdo a los filtros de identificador
    var tag = $("#tag").val();
    var selUE = $("#selUE").val();
    var cmbProgramaPresupuestario = $("#cmbProgramaPresupuestario").val();

    var identificador = selUE; //tag+'-'+selUE+'-'+cmbProgramaPresupuestario;
    
    //alert("tag: "+tag+" - selUE: "+selUE+" - cmbProgramaPresupuestario: "+cmbProgramaPresupuestario);
    //if (validarIdentificador == 1 && ($("#tag").val() == '-1' || $("#selUE").val() == '-1' || $("#cmbProgramaPresupuestario").val() == '-1')) { // Validación anterior
    if (validarIdentificador == 1 && $("#selUE").val() == '-1') { // Nueva validación
        // Tiene datos
        identificador = '';
    }
    // if (validarIdentificador == 1) {
    //     // Usar identificador
    //     if (document.querySelector(".cuentasDatos")) {
    //         dataObj = { 
    //             option: 'mostrarIdentificadorCuentas',
    //             identificador: identificador
    //         };
    //         fnSelectGeneralDatosAjax('.cuentasDatos', dataObj, 'modelo/GLJournal_modelo.php');
    //     }
    //     if (document.querySelector(".selectGLNarrative")) {
    //         dataObj = { 
    //             option: 'mostrarIdentificadorExtraPresupuestal',
    //             identificador: identificador
    //         };
    //         fnSelectGeneralDatosAjax('.selectGLNarrative', dataObj, 'modelo/GLJournal_modelo.php');
    //     }
    // }
    
    dataObj = {
        option: 'mostrarIdentificadorCuentasLista',
        identificador: identificador
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/GLJournal_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        if(data.result){
            // console.log("result: "+data.contenido.ultimoNivel);
            window.nivelCargo = 1;
            window.nivelAbono = 1;
            window.cuentas1 = data.contenido.datos1;
            window.cuentas2 = data.contenido.datos2;
            window.cuentas3 = data.contenido.datos3;
            
            fnBusquedaCuentasLista(window.cuentas1, 'GLCode');
            fnBusquedaCuentasLista(window.cuentas1, 'GLCodeAbono');
        }else{
            respuesta = 0;
        }
    })
    .fail(function(result) {
        respuesta = 0;
    });
}

function fnBusquedaCuentasLista(jsonData, inputName = '') {
    // console.log("busqueda Reducciones");
    // console.log("jsonData: "+JSON.stringify(jsonData));
    $( "#"+inputName).autocomplete({
        source: jsonData,
        select: function( event, ui ) {
            $( this ).val( ui.item.value + "");
            //$( "#txtBuscarReducciones" ).val( ui.item.accountcode );
            // $( "#txtBuscarReducciones" ).val( "" );
            //datosPresupuestosBusqueda = { budgetid: ui.item.budgetid, accountcode: ui.item.accountcode, valorLista: ui.item.valorLista};
            return false;
        }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {
        return $( "<li>" )
        .append( "<a>" + item.texto + "</a>" )
        .appendTo( ul );

    };
}

/**
 * Función para validar el tamaño del nivel capturado y así cargar elementos restantes
 * @param  {[type]} input Elemento html
 * @return {[type]}       [description]
 */
function fnValidarCargarCuentas(input) {
    // Funcion para validar tamaño de nivel y cargar caracteres
    var nivel = fnNivelesCuentaContableGeneral(input.value);
    
    if (input.name == 'GLCode') {
        // Si es Cargo
        if (nivel <= 6 && window.nivelCargo != 1) {
            // Nivel 1 - 6
            window.nivelCargo = 1;
            fnBusquedaCuentasLista(window.cuentas1, 'GLCode');
        } else if (nivel >= 7 && nivel <= 9 && window.nivelCargo != 2) {
            // Nivel 7 - 9
            window.nivelCargo = 2;
            fnBusquedaCuentasLista(window.cuentas2, 'GLCode');
        } else if (nivel > 9 && window.nivelCargo != 3) {
            // Nivel 9 en adelante
            window.nivelCargo = 3;
            fnBusquedaCuentasLista(window.cuentas3, 'GLCode');
        }
    } else {
        // Es abono
        if (nivel <= 6 && window.nivelAbono != 1) {
            // Nivel 1 - 6
            window.nivelAbono = 1;
            fnBusquedaCuentasLista(window.cuentas1, 'GLCodeAbono');
        } else if (nivel >= 7 && nivel <= 9 && window.nivelAbono != 2) {
            // Nivel 7 - 9
            window.nivelAbono = 2;
            fnBusquedaCuentasLista(window.cuentas2, 'GLCodeAbono');
        } else if (nivel > 9 && window.nivelAbono != 3) {
            // Nivel 9 en adelante
            window.nivelAbono = 3;
            fnBusquedaCuentasLista(window.cuentas3, 'GLCodeAbono');
        }
    }
}

// Aplicar formato del SELECT
fnFormatoSelectGeneral(".tag");
// fnFormatoSelectGeneral(".cuentasDatos");
// fnFormatoSelectGeneral(".selectGLNarrative");
fnFormatoSelectGeneral(".cmbTipoPoliza");
fnFormatoSelectGeneral(".selectProgramaPresupuestarioLocal");

// if (validarIdentificador == 1) {
    // Cargar Cuentas de acuerdo a filtros por default
    fnCambioDatosIdentificador();
// }
// alert("tag: "+$("#tag").val()+" - ue: "+"<?php echo $_POST['selUE']; ?>");
if ($("#tag").val() != '-1') {
    // Si tiene ur seleccionada
    fnTraeUnidadesEjecutoras($("#tag").val(), 'selUE');

    var uesel = "<?php echo $_POST['selUE']; ?>";
    $('#selUE').val(''+uesel);
    $('#selUE').multiselect('rebuild');
}

function fnComprobarDescripcionExtrapresupuestal(){
    if($('#tag').val()=="-1"||$('#selUE').val()=="-1"||$('#cmbProgramaPresupuestario').val()=="-1"){
        $("#textoVisible__selectGLNarrative").attr('readonly', true);
        $("#textoVisible__selectGLNarrative").attr("placeholder", "Descripción");
    }else{
        $("#textoVisible__selectGLNarrative").attr('readonly', false);
        fnConsultaMatriz();
        $("#textoVisible__selectGLNarrative").trigger('focusout');
    }
}

function fnConsultaMatriz(){
    var identificador = $('#tag').val()+"-"+$('#selUE').val()+"-"+$('#cmbProgramaPresupuestario').val();

    if(window.buscadoresID['selectGLNarrative'][identificador]===undefined){
        dataObj = {
            option: 'consultaMatriz',
            ur:     $('#tag').val(),
            ue:     $('#selUE').val(),
            pe:     $('#cmbProgramaPresupuestario').val()
        };
        $.ajax({
            async:      false,
            cache:      false,
            method:     "POST",
            dataType:   "json",
            url:        "modelo/GLJournal_modelo.php",
            data:       dataObj
        })
        .done(function( data ) {
            if(data.result){
                cargarLista("selectGLNarrative",identificador,data);
            }
        })
        .fail(function(result) {
        });
    }

    if(window.buscadoresID['selectGLNarrative'][identificador].length){
        $("#textoVisible__selectGLNarrative").attr("placeholder", "Descripción");
    }else{
        $("#textoVisible__selectGLNarrative").attr("placeholder", "No hay registros pertenecientes a ese Programa Extrapresupuestal");
        $("#textoVisible__selectGLNarrative").attr('readonly', true);
    }
}

function prepararLista(){
    $.each(window.camposListado,function(index, valor){
        window.buscadoresID[valor] = new Array();
        window.buscadoresTexto[valor] = new Array();
    });
}

function cargarLista(Elemento,identificador,res){
    window.buscadoresID[Elemento][identificador] = new Array();
    window.buscadoresTexto[Elemento][identificador] = new Array();

    valoresDeBDD = res.contenido.datosBusqueda;
    for(ad in valoresDeBDD){
        window.buscadoresID[Elemento][identificador].push(valoresDeBDD[ad].value);
        window.buscadoresTexto[Elemento][identificador].push(valoresDeBDD[ad].text);
    }
}

function fnSelectCuenta(valor='',idDiv,idBuscador,idHidden,valorTexto) {
    $(idDiv).hide();
    $(idDiv).empty();

    $(idBuscador).val(""+valorTexto);
    $(idHidden).val(""+valor);
    $("#textoOculto__"+idHidden).val(""+valorTexto);

    console.log(idDiv,idBuscador," idBuscador ", idHidden,"2");

    fnObtenerDatosExtraPres();
}

if($("#ColumnaDerecha").find("ul[class='multiselect-container dropdown-menu']").size()){
    $("#ColumnaDerecha").find("ul[class='multiselect-container dropdown-menu']").addClass("pull-right");
}
                               


