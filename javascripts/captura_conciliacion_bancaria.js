/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author
 * @version 1.0
 */ 
//

var dataObjDatosBotones = new Array();
var estatusDiferentes = 0;
var seleccionoCaptura = 0;
var mensajeEstatusDiferentes = "Selecciono Folio con Estatus diferente, el Estatus debe ser igual";
var mensajeSinNoCaptura = "Sin selección de Folio";
var dataFiles = new Array();
var resultDataFiles = new Array();
var urlGeneralFile = '';
var idDocument = 0;

/* ************ */

var dataObjBanksTrans = new Array();
//var OBjBankAccountStatement = new Array();

var OBjBankAccountStatement = {
    'dtaBank' :[]
};

var ObjConciliatedDeposit = {
    'conciliation' :[]
}

var ObjConciliatedRetirement = {
    'Noconciliation' :[]
}
/* ************ */

$(document).ready(function() {

    fnFormatoSelectGeneral(".selectClave");
    load_UR_UE();
   // loadMonths();
    loadYears();
    loadBanks();

    viewSelectElabo();
    viewSelectValid();
    viewSelectAuth();

    $("#btnUploadFile").click(function(e) {
        e.preventDefault(); // to stop  load  to form
        loadFileCB();
    });

    /*
        $(document).on('click','#cargarMultiples',function(){
            $("#tablaDetallesArchivos .newRow").remove();
      });


    /*
        $("#btnEjecutar").click(function(e) {
            e.preventDefault(); // to stop  load  to form
            readFile();
        });

    */


    $('#tipoInput').change( function(event) {

       var filesss =event.target.files[0];

     //  console.log(filesss);

        var datas = new FormData(document.getElementById('fileinfoX'));

        datas.append('inp', filesss);
        datas.append('fileOption','upfiles');

       // console.log(datas);

        $.ajax({
            contentType: false,
            processData: false,
            type: 'POST',
            data: datas,
            url: 'modelo/captura_conciliacion_bancaria_Modelo.php',
            success: function (response) {
                //location.href = 'xxx/Index/'; idDocument
             //  console.log(response);
                idDocument = response;
              //  console.log(idDocument);
            }
        });


        var tmppath = URL.createObjectURL(event.target.files[0]);
            urlGeneralFile = tmppath;

         myFunction(tmppath);
    });

    var date = new Date();
    date.setDate(date.getDate() - 1);

    $('#txtDiaInicio').datetimepicker({
        format: 'DD-MM-YYYY',
      //  minDate: date.setDate(date.getDate())
        // maxDate: ("12/31/"+yearActualAdecuacion)
    }).on('dp.change', function (e) { dateIni(); });

    $('#txtFechaFin').datetimepicker({
        format: 'DD-MM-YYYY',
        //  minDate: date.setDate(date.getDate())
        // maxDate: ("12/31/"+yearActualAdecuacion)
    }).on('dp.change', function (e) { datefin(); });


});

function load_UR_UE(){

    var tagref = "";
    var selectUnidadNegocio = document.getElementById('selectUnidadNegocio');
    for ( var i = 0; i < selectUnidadNegocio.selectedOptions.length; i++) {
        if (i == 0) {
            tagref = "'"+selectUnidadNegocio.selectedOptions[i].value+"'";
        }else{
            tagref = tagref+", '"+selectUnidadNegocio.selectedOptions[i].value+"'";
        }
    }

    var ue = "";
    var selectUnidadEjecutora = document.getElementById('selectUnidadEjecutora');
    for ( var i = 0; i < selectUnidadEjecutora.selectedOptions.length; i++) {
        if (i == 0) {
            ue = "'"+selectUnidadEjecutora.selectedOptions[i].value+"'";
        }else{
            ue = ue+", '"+selectUnidadEjecutora.selectedOptions[i].value+"'";
        }
    }

    $('.selectUnidadEjecutora').multiselect('rebuild');

}

function loadMonths(){

    $.ajaxSetup({async: false, cache:false});
    $.get("modelo/captura_conciliacion_bancaria_Modelo.php",{option:'listMonth'}).then(function(result) {

        var selectData = JSON.parse(result);

        $.each(selectData.meses.month,function(key, registro) {
            $("#selectMonthS").append('<option value='+registro.u_mes+'>'+registro.mes+'</option>');
        });

       // fnFormatoSelectGeneral(".selectMonthS");

    });


}

function loadYears(){
    $.ajaxSetup({async: false, cache:false});
    $.get("modelo/captura_conciliacion_bancaria_Modelo.php",{option:'listAnho'}).then(function(result) {

        var selectData = JSON.parse(result);

        $.each(selectData.year.years,function(key, registro) {
            $("#selectYears").append('<option value='+registro.id+'>'+registro.anho+'</option>');
        });

        fnFormatoSelectGeneral(".selectYears");
    });
}

function loadBanks(){
    $.ajaxSetup({async: false, cache:false});
    $.get("modelo/captura_conciliacion_bancaria_Modelo.php",{option:'listBank'}).then(function(result) {

        var selectData = JSON.parse(result);

        $.each(selectData.banks.dtaBanks,function(key, registro) {
            $("#selectBanco").append('<option value='+registro.id+'>'+registro.name+'</option>');
        });

        fnFormatoSelectGeneral(".selectBanco");
    });
}

function bankAccount(idBanks){

    var idBankAccount = idBanks.value;

    $.ajaxSetup({async: false, cache:false});
    $.get("modelo/captura_conciliacion_bancaria_Modelo.php",{option:'listAccount',idBanks:idBankAccount}).then(function(result) {

        var selectData = JSON.parse(result);

      //  console.log(selectData);
        $('#selectClave').empty();
        $('#selectClave').append("<option value='-1' selected> Sin selección </option>");

        $.each(selectData.accounts.dtaBanksacounts,function(key, registro) {
            $("#selectClave").append('<option value='+registro.id+'>'+registro.number+'</option>');
        });

        fnFormatoSelectGeneral(".selectClave");

        $('.selectClave').multiselect('rebuild');
    });

}

function viewSelectElabo(){

    var unitR = $('#selectUnidadNegocio').val();

    $.ajaxSetup({async: false, cache:false});
    $.get("modelo/captura_conciliacion_bancaria_Modelo.php",{option:'elaborated',urs:unitR}).then(function(result) {

        var selectData = JSON.parse(result);

        $('#selectElabora').empty();
        $('#selectElabora').append("<option value='-1' selected> Sin selección </option>");

        $.each(selectData.elaboro.datos,function(key, registro) {
            $("#selectElabora").append('<option value='+registro.id+'>'+registro.descripcion+'</option>');
        });

        fnFormatoSelectGeneral(".selectElabora");

    });

}

function viewSelectValid(){


    var unitR = $('#selectUnidadNegocio').val();

    $.ajaxSetup({async: false, cache:false});
    $.get("modelo/captura_conciliacion_bancaria_Modelo.php",{option:'valid',urs:unitR}).then(function(result) {

        var selectData = JSON.parse(result);

        $('#selectValido').empty();
        $('#selectValido').append("<option value='-1' selected> Sin selección </option>");

        $.each(selectData.valido.datos,function(key, registro) {
            $("#selectValido").append('<option value='+registro.id+'>'+registro.descripcion+'</option>');
        });

        fnFormatoSelectGeneral(".selectValido");

    });
}

function viewSelectAuth(){

    var unitR = $('#selectUnidadNegocio').val();

    $.ajaxSetup({async: false, cache:false});
    $.get("modelo/captura_conciliacion_bancaria_Modelo.php",{option:'authe',urs:unitR}).then(function(result) {

        var selectData = JSON.parse(result);

        $('#selectAuth').empty();
        $('#selectAuth').append("<option value='-1' selected> Sin selección </option>");

        $.each(selectData.autorizo.datos,function(key, registro) {
            $("#selectAuth").append('<option value='+registro.id+'>'+registro.descripcion+'</option>');
        });

        fnFormatoSelectGeneral(".selectAuth");

    });

}

function viewResultSearch() {

    var selectUR = $('#selectUnidadNegocio').val();
    var selectUE = $('#selectUnidadEjecutora').val();
    var selectYears = $('#selectYears').val();
    var selectBanco = $('#selectBanco').val();
    var selectClave = $('#selectClave').val();
    var selectMonths = $('#selectMonthS').val();
    var selectFechacaptura = $('#txtFechaCaptura').val();
    var selectDiainicio = $('#txtDiaInicio').val();
    var selectFechafin = $('#txtFechaFin').val();

    var saldoFirco = $('#txtSaldoFirco').val();
    var saldoBanco = $('#txtSaldoBanco').val();

    //  $('#').val();

    var contentTable = $('#tablaContenidoConciliacion');
    contentTable = contentTable.html("");

    var msgArray = [];

    if (selectUR == '-1') {
        msgArray.push('Seleccione una UR');
        //viewModalError(msgArray);
    }

    if (selectUE == '-1') {
        msgArray.push('Seleccione una UE');
        //viewModalError(msgArray);
    }

    if (selectYears == '-1') {
        msgArray.push('Seleccione el año que desea consultar');
        //viewModalError(msgArray);
    }

    if (selectBanco == '-1') {
        msgArray.push('Seleccione un Banco');
        //viewModalError(msgArray);
    }

    if (selectClave == '-1') {
        msgArray.push('Seleccione una cuenta CLABE');
        ///viewModalError(msgArray);
    }

    /*if (selectMonths == '-1') {
          msgArray.push('Seleccione el mes que desea consultar');
        //viewModalError(msgArray);
    }*/


    if (msgArray.length > 0) {

        viewModalError(msgArray);

    }else{

     muestraCargandoGeneral();

    dataObj = {
        option: 'searchDataBanks',
        ur: selectUR,
        ue: selectUE,
        year: selectYears,
        banks: selectBanco,
        account: selectClave,
        mount: selectMonths,
        dateCap: selectFechacaptura,
        dayini: selectDiainicio,
        dayend: selectFechafin,
        sfirco: saldoFirco,
        sbank: saldoBanco

    };

    $.ajax({
        async: false,
        cache: false,
        method: "GET",
        dataType: "json",
        url: "modelo/captura_conciliacion_bancaria_Modelo.php",
        data: dataObj
    }).done(function (data) {

        dataObjBanksTrans = data.dtaTableContent;


        if(data.dtaTableContent.dtaTable.length <= 0){

            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se encontraron resultados de la busqueda </p>';
            muestraModalGeneral(3, titulo, mensaje);

        }else{


            $.each(data.dtaTableContent.dtaTable, function (key, contentBody) {

                //  var formatDate = new Date(contentBody.fecha);

                /*     var date= Date(contentBody.fecha);
                     var day=date.getDate();
                     var month=date.getMonth();
                     var month=month+1;
                     if((String(day)).length==1)
                         day='0'+day;
                     if((String(month)).length==1)
                         month='0'+month;

                    var dateT = day+ '-' + month + '-' + date.getFullYear();

                    //(formatDate.getDate() +1)+ "-" + (formatDate.getMonth() +1) + "-" + formatDate.getFullYear()


                     if(parseFloat(contentBody.importe < 0)){
                         var importe = (parseFloat(contentBody.importe) * -1);
                     }else{
                         var importe = parseFloat(contentBody.importe);
                     }
                 */

                if (contentBody.clave_rastreo == 'undefined' || contentBody.clave_rastreo == null) {
                    var claveRastreo = '';
                } else {
                    var claveRastreo = contentBody.clave_rastreo;
                }

                if (contentBody.referenciaSUPP == 'undefined' || contentBody.referenciaSUPP == null) {
                    var refSupp = '';
                } else {
                    var refSupp = contentBody.referenciaSUPP;
                }


                if (contentBody.folio == 'undefined' || contentBody.folio == null) {
                    var fol = ''
                } else {
                    var fol = contentBody.folio;
                }


                contentTable.append('<tr>' +
                    '<td style="text-align:center;">' + contentBody.fecha + '</td>' +
                    '<td style="text-align:center;">' + contentBody.Ur + '</td>' +
                    '<td style="text-align:center;">' + contentBody.Ue + '</td>' +
                    '<td style="text-align:center;">' + fol + '</td>' +
                    '<td style="text-align:center;">' + contentBody.transaccion + '</td>' +
                    '<td style="text-align:center;">' + contentBody.referencia + '</td>' +
                    '<td style="text-align:center;">' + claveRastreo + '</td>' +
                    '<td style="text-align:center;">' + refSupp + '</td>' +
                    '<td style="text-align:center;">$ ' + formatoComas(redondeaDecimal(parseFloat(contentBody.importe))) + '</td>' +
                    '<td style="text-align:center;"></td>' +
                    '</tr>');
            }); // fin llenado de tabla


        } // fIn de IF con Error

    }).fail(function (error) {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> ' + error + '</p>';
        muestraModalGeneral(3, titulo, mensaje);

    });

    ocultaCargandoGeneral();

  }

}

function loadFileCB(){

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

    dataFiles = generateItem('input', opts);

    $("#tipoInputFile").append(dataFiles);

    $("#cargarMultiples").click(); // click to  new  element to trigger finder Dialog to  get files


}

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
        tipo = file.type

      // console.log(this.files[ad]);

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

function myFunction(urls){
    var x = document.getElementById("tipoInput");
    var txt = "";

    if ('files' in x) {
        if (x.files.length == 0) {
            txt = "Seleccione un Archivo.";
        } else {
            for (var i = 0; i < x.files.length; i++) {
               // txt += "<br><strong>" + (i+1) + ". file</strong><br>";
                var file = x.files[i];
                if ('name' in file) {
                    txt += '<tr class="text-center w100p newRow"><td></td><td>' + file.name + '</td><td></td></tr>';
                }
               /* if ('size' in file) {
                    txt += "size: " + file.size + " bytes <br>";
                }*/


            }// llave for
        }

    }
    else {
        if (x.value == "") {
            //txt += "Select one or more files.";
        } else {
            //txt += "The files property is not supported by your browser!";
            //txt  += "<br>The path of the selected file: " + x.value; // If the browser does not support the files property, it will return the path of the selected file instead.
        }
    }

    document.getElementById("demo").innerHTML = txt;
    document.getElementById("muestraAntesdeEnviar").style.display="block"

    var url = urls;
    var oReq = new XMLHttpRequest();

    muestraCargandoGeneral();

    oReq.open("GET", url, true);
    oReq.responseType = "arraybuffer";

    oReq.onload = function(e) {
        var arraybuffer = oReq.response;
        var data = new Uint8Array(arraybuffer);
        var arr = new Array();
        for (var i = 0; i != data.length; ++i) arr[i] = String.fromCharCode(data[i]);
        var bstr = arr.join("");


        //var workbook = XLSX.read(bstr, {type: "binary"});
        var workbook = XLSX.read(bstr, {type:'binary', cellDates:true, cellNF: false, cellText:false});

        var first_sheet_name = workbook.SheetNames[0];

        var worksheet = workbook.Sheets[first_sheet_name];
         //console.log('General',XLSX.utils.sheet_to_json(worksheet, { raw: true}));
       /* var headerTablebanks = $("#headertable");
            headerTablebanks = headerTablebanks.html("");*/

        var bodytablebanks = $("#contenidoTablaArchivos");
            bodytablebanks = bodytablebanks.html("");

        OBjBankAccountStatement.dtaBank.length = 0;

        var tableData = '';
        var encabezado = '<th></th>';
        var contenido = '';
        var rowItem = 1;
        for(var z = 0; z<=XLSX.utils.sheet_to_json(worksheet, {raw: true}).length; z++){

            // if(z == 10){
            //     $.each(XLSX.utils.sheet_to_json(worksheet, { raw: true})[z],function(key, contentHeader) {
            //           encabezado += '<th style="text-align:center;">'+contentHeader+'</th>';
            //     });
            //     headerTablebanks.append('<tr class="header-verde">'+encabezado+'</tr>');
            // }else {
            //     if(z > 10){
                    bodytablebanks.append('<tr>' +
                                            '<td style="text-align:center;">'+rowItem+'</td>'+
                                            '<td style="text-align:center;" width="200px">'+ XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][0]+'</td>'+
                                            '<td style="text-align:center;" width="200px">'+XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][1]+'</td>'+
                                            '<td style="text-align:center;">'+XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][2]+'</td>'+
                                            '<td style="text-align:center;">'+XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][3]+'</td>'+
                                            '<td style="text-align:center;">'+XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][4]+'</td>'+
                                            '<td style="text-align:center;">'+XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][5]+'</td>'+
                                            '<td style="text-align:center;">'+XLSX.SSF.format('$#,##0.00', XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][6])+'</td>'+
                                            '<td style="text-align:center;">'+XLSX.SSF.format('$#,##0.00', XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][7])+'</td>'+
                                            '<td style="text-align:center;">'+XLSX.SSF.format('$#,##0.00', XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][8])+'</td>'+
                                            '<td style="text-align:center;">'+XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][9]+'</td>'+
                                            '<td style="text-align:center;">'+XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][10]+'</td>'+
                                          '</tr>');
                    
                    console.log("dato: "+JSON.stringify(XLSX.utils.sheet_to_json(worksheet, { header:1, raw:true})[z]));

                    if(XLSX.utils.sheet_to_json(worksheet, { header:1, raw:true})[z][6] == undefined){
                        var dept = 0;
                    }else{
                        var dept = XLSX.utils.sheet_to_json(worksheet, { header:1, raw:true})[z][6];
                    }

                    if(XLSX.utils.sheet_to_json(worksheet, { header:1, raw:true})[z][7] == undefined){
                        var reti = 0;
                    }else{
                        var reti = XLSX.utils.sheet_to_json(worksheet, { header:1, raw:true})[z][7];
                    }
                    // console.log('val',XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true}));

                    // console.log('val',XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[0][0]);

                    // console.log("dato: "+XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][6]);


                    OBjBankAccountStatement.dtaBank.push({
                        "n_row": rowItem,
                       // "fecha_operacion_banco": XLSX.utils.sheet_to_json(worksheet, { dateNF:"DD-MM-YYYY"})[z]['__EMPTY'],
                       "fecha_operacion_banco": XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][0],
                        "fecha_banco": XLSX.utils.sheet_to_json(worksheet, { header:1, raw:true})[z][1],
                        "referencia_banco": XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][2],
                        "descripcion_banco": XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][3],
                        "codigo_transc_banco": XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][4],
                        "sucursal_banco": XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][5],
                        "deposito_banco": dept,
                        "retiro_banco": reti,
                        "saldo_banco": XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z]['__EMPTY_9'],
                        "movimiento_banco": XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][9],
                        "descripcion_detalla_banco": XLSX.utils.sheet_to_json(worksheet, {header:1, raw:true})[z][10],
                        "batchconciliacion":0

                    });

                    rowItem ++;
            //     }
            // }
        }
    }

   console.log("OBjBankAccountStatement"+JSON.stringify(OBjBankAccountStatement));

    ocultaCargandoGeneral();

    oReq.send()
}


function conciliar(){

  var folioCon = folioConciliacion();
  var ctas = '';
  var URs = '';

  console.log("OBjBankAccountStatement"+JSON.stringify(OBjBankAccountStatement));

  console.log("dataObjBanksTrans"+JSON.stringify(dataObjBanksTrans));


  for(var h=0; h<OBjBankAccountStatement.dtaBank.length; h++){
      var f = 0;

      for(var s=0; s<dataObjBanksTrans.dtaTable.length; s++){
          f=0;
          URs=dataObjBanksTrans.dtaTable[s].Ur;
          ctas=dataObjBanksTrans.dtaTable[s].ctaCont;
          if(OBjBankAccountStatement.dtaBank[h].referencia_banco == dataObjBanksTrans.dtaTable[s].referenciaSUPP){

            //  alert("referencias Iguales"+" "+dataObjBanksTrans.dtaTable[s].referenciaSUPP +"  "+ OBjBankAccountStatement.dtaBank[h].referencia_banco);

              if(OBjBankAccountStatement.dtaBank[h].deposito_banco != 0 && OBjBankAccountStatement.dtaBank[h].retiro_banco == 0){

                  if(OBjBankAccountStatement.dtaBank[h].deposito_banco ==  dataObjBanksTrans.dtaTable[s].importe){

                      ObjConciliatedDeposit.conciliation.push({
                          'legalid': 0,
                          'cuenta': dataObjBanksTrans.dtaTable[s].ctaCont,
                          'fecha': OBjBankAccountStatement.dtaBank[h].fecha_operacion_banco,
                          'Concepto': OBjBankAccountStatement.dtaBank[h].descripcion_detalla_banco,
                          'retiros': 0,
                          'depositos': OBjBankAccountStatement.dtaBank[h].deposito_banco,
                          'conciliado': dataObjBanksTrans.dtaTable[s].importe,
                          'usuario':'',
                          'fechacambio': $('#txtFechaCaptura').val(),
                          'ur': dataObjBanksTrans.dtaTable[s].Ur,
                          'folioConc': folioCon,
                          'fechacontable':$('#txtFechaCaptura').val(),
                          'referencia':OBjBankAccountStatement.dtaBank[h].referencia_banco
                      });

                      f ++;

                      break;

                  }

              }else{

                  if(OBjBankAccountStatement.dtaBank[h].deposito_banco == 0 && OBjBankAccountStatement.dtaBank[h].retiro_banco != 0){

                     if(OBjBankAccountStatement.dtaBank[h].retiro_banco ==  dataObjBanksTrans.dtaTable[s].importe){

                         ObjConciliatedDeposit.conciliation.push({
                             'legalid': 0,
                             'cuenta': dataObjBanksTrans.dtaTable[s].ctaCont,
                             'fecha': OBjBankAccountStatement.dtaBank[h].fecha_operacion_banco,
                             'Concepto': OBjBankAccountStatement.dtaBank[h].descripcion_detalla_banco,
                             'retiros': OBjBankAccountStatement.dtaBank[h].retiro_banco,
                             'depositos': 0,
                             'conciliado': dataObjBanksTrans.dtaTable[s].importe,
                             'usuario':'',
                             'fechacambio':$('#txtFechaCaptura').val(),
                             'ur': dataObjBanksTrans.dtaTable[s].Ur,
                             'folioConc': folioCon,
                             'fechacontable':$('#txtFechaCaptura').val(),
                             'referencia':OBjBankAccountStatement.dtaBank[h].referencia_banco
                         });

                         f ++;
                         break;

                     }
                  }
              }
          }


      }


      if(f == 0){

          ObjConciliatedDeposit.conciliation.push({
              'legalid': 0,
              'cuenta': ctas,
              'fecha': OBjBankAccountStatement.dtaBank[h].fecha_operacion_banco,
              'Concepto': OBjBankAccountStatement.dtaBank[h].descripcion_detalla_banco,
              'retiros': OBjBankAccountStatement.dtaBank[h].retiro_banco,
              'depositos': OBjBankAccountStatement.dtaBank[h].deposito_banco,
              'conciliado': 0,
              'usuario':'',
              'fechacambio':$('#txtFechaCaptura').val(),
              'ur': URs,
              'folioConc': folioCon,
              'fechacontable':$('#txtFechaCaptura').val(),
              'referencia':OBjBankAccountStatement.dtaBank[h].referencia_banco
          });

      }





  }

  //console.log(ObjConciliatedDeposit);


  var numeroConciliados = 0;
  for(var u = 0; u< ObjConciliatedDeposit.conciliation.length; u++){

      if(ObjConciliatedDeposit.conciliation[u].conciliado != 0){
          numeroConciliados = numeroConciliados+1;
      }

  }

 console.log("numeroConciliados: "+numeroConciliados);
 console.log("ObjConciliatedDeposit: "+JSON.stringify(ObjConciliatedDeposit))

  if(numeroConciliados != 0){

      insertConciliacion(ObjConciliatedDeposit,folioCon,dataObjBanksTrans);

  }else{

      var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
      var mensaje ='<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> No se encontraron coincidencias en el estado de cuenta </p>';
      muestraModalGeneral(3, titulo, mensaje,'','loadPagelocal();');

  }

}


function folioConciliacion(){

    var nextFolio = 0;

    $.ajaxSetup({async: false, cache:false});
    $.get("modelo/captura_conciliacion_bancaria_Modelo.php",{option:'secuenceFolioConciliation'}).then(function(result) {

            var resultQuery = JSON.parse(result);
                 nextFolio = resultQuery.folio;
    });

   return nextFolio;

}

function insertConciliacion(arrayData,folios,arrayDB){


    objDatas={
        datafileBanks:arrayData,
        opt:'storeAccount',
        fol:folios,
        dataBanks:arrayDB,
        files:$('#tipoInput').val(),
        urlgfiles:urlGeneralFile,
        DocumentID:idDocument,
        ln_month: $('#selectMonthS').val(),
        ln_anhos: $('#selectYears').val(),
        stardates: $('#txtDiaInicio').val(),
        enddates: $('#txtFechaFin').val(),
        elaboroCon: $('#selectElabora').val(),
        validoCon: $('#selectValido').val(),
        autorizoCon: $('#selectAuth').val()
    }

   //console.log(objDatas);
    //muestraCargandoGeneral();
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/captura_conciliacion_bancaria_Modelo.php",
        data:objDatas,
    }).done(function (result){

       //uploadFile();

        if(result.tipo == 'error'){
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+result.message+'</p>';
            muestraModalGeneral(3, titulo, mensaje);

        }else{

         //   uploadFile();

          //  ocultaCargandoGeneral();
            if(result.tipo == 'success'){
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                var mensaje ='<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i>   ' + result.message + '</p>';
                muestraModalGeneral(3, titulo, mensaje,'','loadPage();');
            }
        }


    }).fail(function (error){

        if(error.tipo == 'error'){

            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+error.message+'</p>';
            muestraModalGeneral(3, titulo, mensaje);

        }


    });



}

function uploadFile(){

}

function loadPage(){
    // funciona como si dieras clic en un enlace

    window.location.href = "panel_conciliacion_bancaria.php";
}

function loadPagelocal(){
    // funciona como si dieras clic en un enlace

    window.location.reload();
}


function viewModalError(msg){

    //console.log(msg);

    if(msg.length > 1){

        var msgs = '';

        $.each(msg,function(key,value) {
            msgs += value+ "<br/>";
        })

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+msgs+'</p>';
        muestraModalGeneral(3, titulo, mensaje);

    }else{

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+msg+'</p>';
        muestraModalGeneral(3, titulo, mensaje);

    }



}

function mounthORdate(elemt){

    var typesMonth = elemt.value;

    var inicio = $('#txtDiaInicio').val();
    var fin = $('#txtFechaFin').val();

    if(typesMonth != '-1'){

        if(inicio != '' || inicio != null){
            $('#txtDiaInicio').val("");
        }

        if(fin != '' || fin != null){
            $('#txtFechaFin').val("");
        }

    }

}

function dateIni(){

    var inicio = $('#txtDiaInicio').val();

    $('#selectMonthS').val($('#selectMonthS option').eq(0).val());

    //alert( $('#selectMonthS').val());
  /*  $('#conte').empty();


    $('#conte').append(
        '<select id="selectMonthS" name="selectMonthS" class="form-control selectMonthS selectMeses" onchange="mounthORdate(this);">'+
        ' <option value="-1"> Sin selección </option>'+
        ' </select>);');

*/

}

function datefin(){

    var fin = $('#txtFechaFin').val();

    $('#selectMonthS').val($('#selectMonthS option').eq(0).val());

    //alert( $('#selectMonthS').val());
}