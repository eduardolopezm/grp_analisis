/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */

/**
 * [fnCargarArchivos set  type to load files esMultiple=0 only load  one file esMultiple=1 load many files
 * @return {[type]} [description]
 */
window.cols=[];
window.archivosNopermitidos=[];
window.titulo='<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
window.cuentaFilas = 1;
window.modelo = "modelo/paasModelo.php";
window.assets={};
window.assetsDes={};
window.services={};
window.servicesDes={};
window.geografico=[];
window.units={};
window.dataDetail={};
window.rowsEnable=[];
window.pefPartidas={};
window.valAnteriorPef=0;
window.montoValEs=0;
window.montosValEsti={};
window.inputFocus='';
window.typeProceObj={'LP-Licitación Pública':'LP-Licitación Pública',
            'AD-Adjudicación Directa':'AD-Adjudicación Directa',
            'I3P-Invitación a cuando menos 3 personas':'I3P-Invitación a cuando menos 3 personas',
            'APPC-Concurso Asociaciones Público Privadas':'APPC-Concurso Asociaciones Público Privadas',  
            'APPAD-Adjudicación Directa Asociaciones Público Privadas':'APPAD-Adjudicación Directa Asociaciones Público Privadas', 
            'APPI3P-Invitación a cuando menos 3 Asociaciones Público Privadas':'APPI3P-Invitación a cuando menos 3 Asociaciones Público Privadas' };
window.charProceObj={'N-Nacional':'N-Nacional','I-Internacional bajo TLC':'I-Internacional bajo TLC','A-Internacional Abierto':'A-Internacional Abierto'};
window.archivosNopermitidos=[];
window.valorAnterior=0;
window.valorSiguiente=0;

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
    $("#cargarMultiples").click(); // click to new element to trigger finder Dialog to get files
}

/**
 * [description]
 * @param  {[type]} ){                 action click from class quitarArchivos set function anonymous to handle click event
 * @return {[type]}     [description]
 */
$(document).on('click','.quitarArchivos',function(){
    filas=  $('#tablaDetallesArchivos >tbody >tr').length;
  

    nombre=$(this).find('input').val();
    var archivos= document.getElementById('cargarMultiples').files;

    for(var i=0;i<archivos.length;i++){
        if(archivos[i].name === nombre){
            archivosNopermitidos.push(archivos[i].name);
            $(this).closest('tr').remove();
            break;
        }
    }
    if(filas==1){
        $('#cargarMultiples').val('');
    }
});
$(document).on('change','#cargarMultiples',function(){
        //var cols = [];
        archivosNopermitidos= new Array();
        archivosNopermitidos=[];
        archivosTotales=0;
        estilo='text-center w100p';

        //var filasArchivos='';
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

            tamanio = generateItem('span', {html:'<b>Tamaño</b>'+file.size});
            cols.push(generateItem('td', {
                style: estilo
            }, tamanio));


            quitar = generateItem('span', {class:'quitarArchivos glyphicon glyphicon-remove btn bgc8',style:'color:#fff'},generateItem('input',{type:'hidden',val:file.name}));
            cols.push(generateItem('td', {
                style: estilo
            }, quitar));
            
            tr = generateItem('tr', {
            class: 'text-center w100p'
            }, cols);

           $("#tablaDetallesArchivos").find('tbody').append(tr);
           archivosTotales++;
        }
        
        $('#muestraAntesdeEnviar').show();
        $('#enviarArchivosMultiples').show();
    });

function checkVal(){
     ue=$("#selectUnidadEjecutora").val();
     folio=$("#numberFolio").val();
     begin=$("#dateDesde").val();
     end=$("#dateHasta").val();
     //comments=$("#txtAreaObs").val();
     msg='';
     count=0;
     if(ue==-1){
        msg+="<i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i>  Falta UE<br>";
        count++;
     }
     if(folio==''){
        msg+="<i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> Falta oficio<br>";
        count++;
     }
     if(begin==''){
         msg+="<i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> Falta fecha inicio<br>";
         count++;
     }
     if(end==''){
         msg+="<i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> Falta fecha término<br>";
         count++;
     }
    
    if($("#tablaDetallesArchivos").length){
           filas=  $('#tablaDetallesArchivos >tbody >tr').length;
           if(filas==0){
             msg+="<i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> Es necesario cargar un oficio. <br>";
             count++;
           }
    }
     if(count>0){
        muestraModalGeneral(4,titulo,msg);
     }

    $('div').removeClass("modal-backdrop");
    
     return count;
}
function updateRows(tabla) {
   
    rowCount= 1;
    numbers=[];
    
    $(tabla+" tbody tr").each(function(i) {
      id=$(this).attr('id');
      idAux= id.replace("fila","");
      numbers.push(parseInt(idAux));
    });
  
    numbers.sort( function(a,b) { return a - b; } ); // order  numbers //b-a for des
    
    for(ad in numbers){
        
        $('#fila'+numbers[ad]).find('#0row'+numbers[ad]).html(rowCount);
       
        rowCount++;
    }
   
    
    // if(renglon==1){
    //     $('#selectUnidadEjecutora').multiselect('enable');
    //     $('#selectAlmacen').multiselect('enable');
        
    // }

    $('div').removeClass("modal-backdrop");
}
function fnAgregarFila(elementos,tabla,btnEliminar=true,disable=0){
    var cols = [];
    var hide='';
    if(disable==0){


    if(btnEliminar==true){
      eliminar = generateItem('td', {id: cuentaFilas},generateItem('span', {class: 'btn btn-danger btn-xs glyphicon glyphicon-remove filaQuitar',title: 'eliminar',id: "btnEliminar" + cuentaFilas,type: "button"}));
      cols.push(eliminar);
    }
    
    $.each(elementos, function(i, v) {
        estilo='';
        hide='';
        var clase = '';
        var n = '';
        opts = v.opts;
        opts.id = i + cuentaFilas;
        opts.name = i+"[]";
        //opts.html='';
        tag = v.tag
        contenido = generateItem(tag, opts);
        estilo='vertical-align:middle; min-width: 100px;max-width:100px;font-size: 12px; max-height:10px; ';
        if(opts.name=='descripcion[]'){
            estilo='vertical-align:middle; min-width: 200px;max-width:200px;font-size: 12px; max-height:10px; ';
        }else if(opts.name=='fecharegistro[]'){
            estilo='vertical-align:middle; min-width: 110px;max-width:110px;font-size: 12px; max-height:10px; '
        }else if(opts.name=='entidadfederativa[]'){
            estilo='vertical-align:middle; min-width: 150px;max-width:150px;font-size: 12px; max-height:10px; ';
        }else if(opts.name=='unidadmedida[]'){
            
            estilo='vertical-align:middle; min-width: 200px;max-width:200px;font-size: 12px; max-height:10px; ';
        }else if(opts.name=='tipoprocedimiento[]'){
            estilo='vertical-align:middle; min-width: 300px;max-width:300px;font-size: 12px; max-height:10px; ';
        }else if(opts.name=='intoDetail[]'){
             estilo='display:none;';
        }else if(opts.name=='tipo[]'){
             estilo='display:none;';
        }
        // if(opts.name=='clave[]' ||(opts.name=='descripArt[]')){
        //     estilo='vertical-align:middle; min-width: 100px;max-width:100px; font-size: 12px; '+hide;
        // }else if(opts.name=='orow[]'){
        //   estilo='vertical-align:middle; min-width: 50px;max-width:50px;font-size: 12px; '+hide;  
        // }else {
        //   estilo='vertical-align:middle; min-width: 100px;max-width:100px;font-size: 12px; '+hide;  
        // }
        
        cols.push(generateItem('td', {
            style: estilo
        }, contenido));


    });

    tr = generateItem('tr', {
        class: 'text-center w100p',
        id: 'fila' + cuentaFilas
    }, cols);
    $(tabla).find('tbody').append(tr);
    
    //(disable?window.rowsEnable.push(cuentaFilas): null);
   
    cuentaFilas++;
      }
    

}

function returnData(data, maneja) {
   
    valores = JSON.parse(data);
  
console.log(valores);
    
    switch (maneja) {
        case 'GuardarDatos':

            muestraModalGeneral(4,titulo,valores[0].msg);
            if (!$(".folius").length) {
                if(valores[0].flag==true){
                  $("#btnGuardar").removeAttr("disabled");
                }
            }else{
               //  window.open('catalogoPaaas.php',"_self");
            }
              
                location.reload(); 
            break;
        case 'managerDetails':
        // setTimeout(function() {

        ur=valores[0].ur;
        ue=valores[0].ue;
        //set values
        window.assets=valores[0].assets;
        window.assetsDes=valores[0].assetsDes;
        window.services=valores[0].services;
        window.servicesDes=valores[0].servicesDes;
        window.geografico=valores[0].geo;
        window.units=valores[0].units;
        window.dataDetail=valores[0].dataDetail;
        window.pefPartidas=valores[0].budgetPEF;
        // i=0;
        // for(ya in pefPartidas ){
        //     OriginalP[i]={"idPef":ya,"valPef":pefPartidas[ya]};
        //     i++;
        // }
        //OriginalP=valores[0].budgetPEF;
        // fnCrearSelect(window.assetsDes,"#partidasAssets");
        // fnCrearSelect(window.servicesDes,"#partidasServices");
         if(window.assets.length>0){
            fnCrearSelect(window.assetsDes,"#partidasAssets");
             $("#tabAssets").show();
             $("#bienes").show();
        }
        
        if(window.services.length>0){
            fnCrearSelect(window.servicesDes,"#partidasServices");
            $("#tabServices").show();
            if(window.assets.length==0){
             $("#memoria").removeClass('fade');
             $("#memoria").show();
          }
            // if(window.assets.length==0){
            //     $("#tabServices").addClass('active');
            //    // $("#memoria").removeClass('fade');
            // }
        }

        
        $("#dateDesde").val(valores[0].begin);
        $("#dateHasta").val(valores[0].end);
        $('#numberFolio').val(valores[0].oficio);
        $('#txtAreaObs').val(valores[0].comments);
        
        $('#selectUnidadEjecutora').selectpicker('val',ue);
        $('#selectUnidadEjecutora').multiselect('refresh');
        $('.selectUnidadEjecutora').css("display", "none");
        

        $('#selectAnio').selectpicker('val',valores[0].year);
        $('#selectAnio').multiselect('refresh');
        $('.selectAnio').css("display", "none");
       

        // }, 300);
        if(window.dataDetail.length>0){
            
            setTimeout(function() {
                showDetail(window.dataDetail);
                $("#panelDetailDiv").show();
            }, 300);

        }
        if((window.services.length==0)&&(window.assets.length==0)){
            muestraModalGeneral(4,titulo,"No cuenta con partidas que tengan presupuesto en bienes y servicios");
        }else{
            $("#panelAddPArtidas1").show();
        }
        break;
        
    }

    $('div').removeClass("modal-backdrop");
}
function fnenvioDatos(donde, maneja,datosSend=0 ,form = '', formAccion = '') {

  //  alert(donde);

    if (form != '') {
        valores = $('#' + form).serialize();



        $.ajaxSetup({async: false, cache:false});
        $.post(modelo, {
            getData: donde,
            accion: formAccion,
            valores
        }, function(data) {

         //   console.log("form Lleno",data,maneja);

            returnData(data, maneja);

        });
    } else {

        $.ajaxSetup({async: false, cache:false});
        $.post(modelo, {
            getData: donde,
            datos:datosSend
        }, function(data) {

           // console.log("form vacio",data,maneja);
            returnData(data, maneja);
        });
    }




    $('div').removeClass("modal-backdrop");
}
$(document).on('click', '.filaQuitar', function() {

    var btn = $(this).attr('id');
    var id = btn.replace("btnEliminar", "fila");
    $("#" + id).remove();
    //fnActualizarRenglon(nombreTabla);
    tdid=($(this).parent().attr('id') );

    tabla=$("#fila"+tdid).closest('table').attr('id');
    //console.log(tabla);
    updateRows("#assetsTableDetails"); //tabla);
});
function fnCrearSelect(datos,select) {

    optionsLista = [{
            label: 'Seleccionar',
            title: 'Seleccionar',
            value: -1
        }];
    $.each(datos, function(index, val) {
        optionsLista.push({
            label: index,
            title: index,
            value: index
        });
    });

     $(select).multiselect({
        enableFiltering: true,
        filterBehavior: 'text',
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        numberDisplayed: 1,
        includeSelectAllOption: true
    });
    $(select).multiselect('dataprovider', optionsLista);

    $('.multiselect-container').css({
        'max-height': "220px"
    });
    $('.multiselect-container').css({
        'overflow-y': "scroll"
    });

    $('div').removeClass("modal-backdrop");

    return optionsLista;

}
function getValWhenDetail(searchVal,objecInsearch){
    ret=[];
    if(objecInsearch.length>0){

       aux= objecInsearch.map(function (info) {
          if (info.clavecucop ==searchVal) {
            return info;
          } else {
            return null;
          }
        });

       ret=aux.filter(function(obj) { return obj }); // get object with match and delete null val;
    }
return ret;
}
function getValWhenDetail2(searchVal,objecInsearch){ // obtiene  la inforacion en el objeto cuando coincide con el id 
    ret=[];
    if(objecInsearch.length>0){

       aux= objecInsearch.map(function (info) {
          if (info.id ==searchVal) {
            return info;
          } else {
            return null;
          }
        });

       ret=aux.filter(function(obj) { return obj }); // get object with match and delete null val;
    }
return ret;
}
function getValWhenDetail3(searchVal,searchVal2,objecInsearch){
    ret=[];
    //console.log(objecInsearch);
    if(objecInsearch.length>0){

       aux= objecInsearch.map(function (info) {
          if ((info.partida ==searchVal)&&(info.id==searchVal2) ) {
            return info;
          } else {
            return null;
          }
        });
       
       ret=aux.filter(function(obj) { return obj }); // get object with match and delete null val;
    }
return ret;
}
function buscaLeyenda(searchVal,objecInsearch){
 
     var buscar = searchVal;
            
            var buscarCoicidencia = new RegExp(buscar , "i");
           
            var arr = jQuery.map(objecInsearch, function (value,index) {
                    
                return index.match(buscarCoicidencia) ? index : null;
            });
          
    return arr[0];
}
function showPartidas(value,table,type){
    muestraCargandoGeneral();
    setTimeout(function() {
    partidaSs=0;
    $(table+" tbody").empty();
    data={};
    montoDePartida=0;
  
    if(type=='B'){
        data=window.assets
        clase="valorEstimadoCl";
    }else{
        data=window.services
        clase="valEstiSer";
    }
    flagDisable=0;
    for(ya in value){
        if(value[ya]!=1){
        for(ad in data){
            if(value[ya]==(data[ad].partida)){
                partidaSs=data[ad].partida;
                clave=data[ad].id;
                montoDePartida=data[ad].budget;
                dataAuxDetail=getValWhenDetail(clave,window.dataDetail);
               
                //console.log(dataAuxDetail.length);
                auxDetailaniosplurianuales=0;
                auxDetailcantidad=0;
                auxDetailcaracterprocedimiento=0;
                auxDetailentidadfederativa=0;
                auxDetailfecharegistro=0;
                auxDetailplurianual=0;
                auxDetailporcentaje1ertrim=0;
                auxDetailporcentaje2dotrim=0;
                auxDetailporcentaje3ertrim=0;
                auxDetailporcentaje4totrim=0;
                auxDetailtipo=0;
                auxDetailtipoprocedimiento=0;
                auxDetailunidadmedida=0;
                auxDetailvalorenctlc=0;
                auxDetailvalorestimado=0;
                auxDetailvalormipymes=0;
                auxDetailvalortotalplurianual=0;
                if(dataAuxDetail.length>0){
                flagDisable=1;

                auxDetailaniosplurianuales   =dataAuxDetail[0].aniosplurianuales;
                auxDetailcantidad   =dataAuxDetail[0].cantidad;
                auxDetailcaracterprocedimiento  =dataAuxDetail[0].caracterprocedimiento;
                auxDetailentidadfederativa  =dataAuxDetail[0].entidadfederativa;
                auxDetailfecharegistro  =dataAuxDetail[0].fecharegistro;
                auxDetailplurianual =dataAuxDetail[0].plurianual;
                auxDetailporcentaje1ertrim  =dataAuxDetail[0].porcentaje1ertrim;
                auxDetailporcentaje2dotrim  =dataAuxDetail[0].porcentaje2dotrim;
                auxDetailporcentaje3ertrim  =dataAuxDetail[0].porcentaje3ertrim;
                auxDetailporcentaje4totrim  =dataAuxDetail[0].porcentaje4totrim;
                auxDetailtipo   =dataAuxDetail[0].tipo;
                auxDetailtipoprocedimiento  =dataAuxDetail[0].tipoprocedimiento;
                auxDetailunidadmedida   =dataAuxDetail[0].unidadmedida;
                auxDetailvalorenctlc    =dataAuxDetail[0].valorenctlc;
                auxDetailvalorestimado  =dataAuxDetail[0].valorestimado;
                auxDetailvalormipymes   =dataAuxDetail[0].valormipymes;
                auxDetailvalortotalplurianual   =dataAuxDetail[0].valortotalplurianual;  
               
                }else{
                    flagDisable=0;
                }
                valorEstimado="valorestimado"+type;
                var elementos = { 
                "0row":  {tag: 'span', opts: {html:''}},
                "clavecucop"   :  {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text', val:data[ad].id,readonly: true}},
                "descripcion"  :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text',val:data[ad].descri,readonly: true}},
                // "ur"           :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text',val:ur,readonly: true}},
                // "ue"           :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text',val:ue,readonly: true}},
                //"numerooficio" :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text',val:window.numberScene,readonly: true}},
                "partida"      :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control text-center',type: 'text',val:partidaSs,readonly: true}},
                //"pef"          :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control text-center',type: 'text',val:data[ad].budget,readonly: true}},
                //"asignado":   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers  text-center',type: 'text',readonly: true}},
                //"porasignar":   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers  text-center',type: 'text',readonly: true}},
                
               "valorestimado":   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers '+clase+' valorestimadoclase text-center',type: 'text',val:(auxDetailvalorestimado||''),max:12}},
               "valoriva":   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control soloNumeros  viva text-center',type: 'text',val:'',max:5,onkeypress: 'return fnsoloDecimalesGeneral(event, this)'}},
               "valorbruto":   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers  text-center',type: 'text',val:'',max:12, readonly: true}},
               
                "valormipymes" :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers valorestimadoclase text-center',type: 'text',val:(auxDetailvalormipymes||''),max:12,readonly: true}},
                "valorenctlc"  :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers valorestimadoclase  text-center',type: 'text',val:(auxDetailvalorenctlc||''),max:12,readonly: true}},
                "cantidad"     :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers text-center',type: 'text',val:(auxDetailcantidad||''),max:12}},
                "unidadmedida" :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control sug units text-center',type: 'text',val:(auxDetailunidadmedida ||'')}},
                "caracterprocedimiento"   :{tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control charProce sug text-center',type: 'text',val:(auxDetailcaracterprocedimiento||'')}},
                "entidadfederativa"   : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text',val:($("#selectUnidadEjecutora option:selected").text()) ,max:0,readonly: true}},
                "porcentaje1ertrim"   : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers porcentajes text-center',type: 'text',val:(auxDetailporcentaje1ertrim||''),max:3}},
                "porcentaje2dotrim"   : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers porcentajes text-center',type: 'text',val:(auxDetailporcentaje2dotrim||''),max:3}},
                "porcentaje3ertrim"   : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers porcentajes text-center',type: 'text',val:(auxDetailporcentaje3ertrim||''),max:3}},
                "porcentaje4totrim"   : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers porcentajes text-center',type: 'text',val:(auxDetailporcentaje4totrim|| ''),max:3}},
                "fecharegistro"       : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control text-center',type: 'text',val:(auxDetailfecharegistro ||curday('-'))}},
                "plurianual"          : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers plurianual text-center',type: 'text',val:(auxDetailplurianual||''),max:1}},
                "aniosplurianuales"    : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers text-center',type: 'text',val:(auxDetailaniosplurianuales||'') ,readonly:  true, max:2}},
                "valortotalplurianual": {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers totalpluri text-center',type: 'text',val:(auxDetailvalortotalplurianual||'') ,readonly:  true, max:12}},
                "claveprogramafederal": {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control text-center',type: 'text',val:'NA',readonly: true}},
                "fechainicioobra"    : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text',val:'NA',readonly: true}},
                "fechafinobra"       : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text',val:'NA',readonly: true}},
                "tipoprocedimiento"  : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  typeProce sug text-center',type: 'text',val:(auxDetailtipoprocedimiento||'')}},
                "intoDetail"       : {tag: 'input',opts: {type: 'hidden',val:flagDisable}}
            };
                fnAgregarFila(elementos,table,false,flagDisable);

            }// end if equal partidas
            }// end for to assets
        }// end if to different 1
    }// end for to values
    // deshabilta td indicados
    // for(ad in rowsEnable){
       
    //   $('#fila'+rowsEnable[ad]+' > td').find('input').attr("disabled",'true');
   
    // }
    
    //console.log("prueba","partida sel",partidaSs,"presu",window.pefPartidas[partidaSs]);

    $("#presupuestoPorAsignar").val((window.pefPartidas[partidaSs]));
    $("#presupuestoAsignado").val("0");
    if(type=="B"){
        $("#porAsignarBieneTxt").val(window.pefPartidas[partidaSs]);
        $("#asignadoBienesTxt").val("0");
    }else{
        $("#porAsignarSer").val(window.pefPartidas[partidaSs]);
        $("#asignadoSer").val("0");
    }
    

    //sel2=((type=='B')?"#porAsignarBieneTxt":"#porAsignarServi");
    //$(sel2).val(""+(montoDePartida));
    updateRows(table);
      }, 1000);
    ocultaCargandoGeneral();


    $('div').removeClass("modal-backdrop");
      
}
function showDetail(data){
    //$(table+" tbody").empty();
    //data={};
   //  muestraCargandoGeneral();
    //((type=='B')?data=window.assets:data=window.services);

    for(ad in data){
               clave=data[ad].clavecucop;
               tipo=data[ad].tipo;
               partidaDd=data[ad].partida;
               descripcionDetalle='';
               dataAuxDetail={};
               aux='';
               valDd=window.pefPartidas[partidaDd];
               estimadoDd=data[ad].valorestimado;
               window.pefPartidas[partidaDd]=parseFloat(valDd)-parseFloat(estimadoDd);
               if(tipo=='B'){
                descripcionDetalle=getValWhenDetail2(clave,window.assets);
               }else{
                 descripcionDetalle=getValWhenDetail2(clave,window.services);

               }
               if(descripcionDetalle.length>0){
                
               aux=descripcionDetalle[0].descri;
               
               }
               
               
                // if(data[ad].tipo=="B"){   buscaLeyenda(searchVal,objecInsearch)
                var elementos = { 
                "0row":  {tag: 'span', opts: {html:''}},
                "clavecucop"   :  {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text', val:data[ad].clavecucop,readonly: true}},
                "descripcion"  :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text',val:(aux || 0),readonly: true}},
                // "ur"           :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text',val:ur,readonly: true}},
                // "ue"           :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text',val:ue,readonly: true}},
                //"numerooficio" :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text',val:window.numberScene,readonly: true}},
                "partida"      :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control text-center',type: 'text',val:partidaDd,readonly: true}},
                //"pef"          :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control text-center',type: 'text',val:data[ad].pef,readonly: true}},
                "valorestimado":   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers valorestimadoclase estiDataDetail cambioValDe text-center',type: 'text',val:estimadoDd,max:12}},
                "valoriva":   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control soloNumeros  viva  text-center',type: 'text',val:data[ad].viva,max:5, onkeypress: 'return fnsoloDecimalesGeneral(event, this)'}},
                "valorbruto":   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers  text-center',type: 'text',val:calcularIva(estimadoDd,data[ad].viva),max:12, readonly: true}},
                "valormipymes" :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers valorestimadoclase  text-center',type: 'text',val:data[ad].valormipymes ,max:12}},
                "valorenctlc"  :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers valorestimadoclase  text-center',type: 'text',val:data[ad].valorenctlc  ,max:12}},
                "cantidad"     :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers text-center',type: 'text',val:data[ad].cantidad,max:12}},
                "unidadmedida" :   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control sug units text-center',type: 'text',val:buscaLeyenda(data[ad].unidadmedida+"-",units) }},
                "caracterprocedimiento"   :{tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control charProce sug text-center',type: 'text',val: buscaLeyenda(data[ad].caracterprocedimiento+"-",charProceObj)}},
                "entidadfederativa"   : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text',val:($("#selectUnidadEjecutora option:selected").text()),readonly: true,max:0}},   //data[ad].entidadfederativa
                "porcentaje1ertrim"   : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers porcentajes text-center',type: 'text',val:data[ad].porcentaje1ertrim,max:3}},
                "porcentaje2dotrim"   : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers porcentajes text-center',type: 'text',val:data[ad].porcentaje2dotrim,max:3}},
                "porcentaje3ertrim"   : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers porcentajes text-center',type: 'text',val:data[ad].porcentaje3ertrim,max:3}},
                "porcentaje4totrim"   : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers porcentajes text-center',type: 'text',val:data[ad].porcentaje4totrim,max:3}},
                "fecharegistro"       : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control text-center',type: 'text',val:data[ad].fecharegistro}},
                "plurianual"          : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers plurianual text-center',type: 'text',val:data[ad].plurianual,max:1}},
                "aniosplurianuales"    : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers text-center',type: 'text',val:data[ad].aniosplurianuales ,readonly:  ((data[ad].plurianual==0)?true:null), max:2}},
                "valortotalplurianual": {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control onlyNumbers totalpluri text-center',type: 'text',val:data[ad].valortotalplurianual ,readonly:  ((data[ad].plurianual==0)?true:null), max:12}},
                "claveprogramafederal": {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control text-center',type: 'text',val:'NA',readonly: true}},
                "fechainicioobra"    : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text',val:'NA',readonly: true}},
                "fechafinobra"       : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text',val:'NA',readonly: true}},
                "tipoprocedimiento"  : {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control typeProce sug text-center',type: 'text',val:buscaLeyenda(data[ad].tipoprocedimiento+"-",typeProceObj)}},
                "intoDetail"       : {tag: 'input',opts: {type: 'hidden',val:0}},
                "tipo"       : {tag: 'input',opts: {type: 'hidden',val:data[ad].tipo}}
                };
                fnAgregarFila(elementos,"#assetsTableDetails",true);
            // }else{

            //     fnAgregarFila(elementos,"#servicesTableDetails");

            // }
       
    }// end for to values
    updateRows("#assetsTableDetails");
    //ocultaCargandoGeneral();
    if(window.bye=='24'){
        fnRangeDates();
    }

    $('div').removeClass("modal-backdrop");

}
$(document).on('focus','.sug',function(){
    id=$(this).attr('id');
    val= "";//$(this).val();
    //id= id.replace("partida","");
    //console.log("#sug-"+id);
    // position: absolute;
    // list-style-type: none;
    // margin: 0;
    // padding: 0;
    var clase='';
    //$("#"+id).attr('autocomplete','off'); 
    if(id.indexOf("tipoprocedimiento")==0){
        clase='typeProce';
        $("#"+id).attr('autocomplete','off'); 
       // $("#"+id).attr('autocomplete','false');
    }
    if(id.indexOf("entidadfederativa")==0){
        clase='geo';
        $("#"+id).attr('autocomplete','off'); 
        //$("#"+id).attr('autocomplete','false');
    }
    if(id.indexOf("caracterprocedimiento")==0){
        clase='charProce';
         $("#"+id).attr('autocomplete','off'); 
        //$("#"+id).attr('autocomplete','false');
    }
     if(id.indexOf("unidadmedida")==0){
        clase='units';
         $("#"+id).attr('autocomplete','off');
         //$("#"+id).attr('autocomplete','false'); 
    }
    
    $(this).attr('readonly','true');
    //console.log(id);
    $("#beforeSug"+id).show();
    if($("#beforeSug"+id).length==0){
      
       $(this).after('<div style="position:relative; max-height:5px;width: auto;" id="beforeSug'+id+'" > <input type="text" value="'+val+'" class="form-control '+clase+'" id="'+id+'" onfocus="this.value=\'\'; this.style.color=\'#000000\'" style=" background-color : #f3db63; " placeholder="Buscar" autocomplete="off"><div  id="sug-'+id+'" style="position: relative; display: inline-block; z-index:999;  display: inline-block; font-size: 12px; width: auto;"></div> </div>'); 
       
       $("#beforeSug"+id).focus();
    }
        
   
});
$(document).on('focus','.sug',function(){
      id=$(this).attr('id');
    val= "";//$(this).val();
  
    var clase='';
    //$("#"+id).attr('autocomplete','off'); 
    if(id.indexOf("tipoprocedimiento")==0){
        clase='typeProce';
        $("#"+id).attr('autocomplete','off'); 
       // $("#"+id).attr('autocomplete','false');
    }
    if(id.indexOf("entidadfederativa")==0){
        clase='geo';
        $("#"+id).attr('autocomplete','off'); 
        //$("#"+id).attr('autocomplete','false');
    }
    if(id.indexOf("caracterprocedimiento")==0){
        clase='charProce';
         $("#"+id).attr('autocomplete','off'); 
        //$("#"+id).attr('autocomplete','false');
    }
     if(id.indexOf("unidadmedida")==0){
        clase='units';
         $("#"+id).attr('autocomplete','off');
         //$("#"+id).attr('autocomplete','false'); 
    }

    });
function fnSelectData(valor='',idSugSel,div) {

  $(div).hide();
  $(div).empty();
    
  $(idSugSel).val(""+valor);
  $(idSugSel).val(""+valor);

  auxSugSel=idSugSel.replace("#","");
  $("#beforeSug"+auxSugSel).hide();
  $("#"+auxSugSel).removeAttr("readonly");


  
}
$(document).on('click','.geo',function(){
    id=$(this).attr('id');
     $("#"+id).keyup();
});
$(document).on('keyup','.geo',function(){
        
        id=$(this).attr('id');
        id1="#"+id;
        //id= id.replace("partida","");
        //id2="#hide"+id;
        id ="#sug-"+id;
       //  aux=[];
       // for(ad in geografico){
       //    aux.push(ad);
       // }
       var retorno="<ul id='articulos-lista-consolida'>";

      
      
        //if($(this).val()!=''){
            var buscar = $(this).val();//($(this).val()=='')? $(this).val(' ') :$(this).val();  
            
            var buscarCoicidencia = new RegExp(buscar , "i");
           
            var arr = jQuery.map(geografico, function (index) {
                    
                return index.match(buscarCoicidencia) ? index : null;
            });
            // console.log(buscarCoicidencia);
            // console.log(arr);
                
                 for (a=0; a<arr.length;a++){
                   //val=arr[a];
                  
                  retorno+="<li style='with:auto' onClick='fnSelectData(\""+(arr[a])+"\",\""+id1+"\",\""+id+"\")'><a >  "+(arr[a])+"  </a></li>";
                    
                }
            retorno+="</ul>";
            $(id).show();
            $(id).empty();
            $(id).append(retorno);
            $(id).show();
                
        //}
   });
$(document).on('click','.charProce',function(){
    id=$(this).attr('id');
     $("#"+id).keyup();
});
$(document).on('keyup','.charProce',function(){
        
        id=$(this).attr('id');
        id1="#"+id;
        id ="#sug-"+id;
        //aux=[];

        // aux.push("N");
        // aux.push("I");
        // aux.push("A");
         
       
       var retorno="<ul id='articulos-lista-consolida'>";

      
      
        //if($(this).val()!=''){
            var buscar = $(this).val();//($(this).val()=='')? $(this).val(' ') :$(this).val();  
            
            var buscarCoicidencia = new RegExp(buscar , "i");
           
            var arr = jQuery.map(charProceObj, function (value,index) {
                    
                return index.match(buscarCoicidencia) ? index : null;
            });
            // console.log(buscarCoicidencia);
            // console.log(arr);
                
                for (a=0; a<arr.length;a++){
                  //val=arr[a];
                  
                  retorno+="<li style='with:auto' onClick='fnSelectData(\""+( arr[a])+"\",\""+id1+"\",\""+id+"\")'><a >  "+( arr[a])+"  </a></li>";
                    
                }
            retorno+="</ul>";
            $(id).show();
            $(id).empty();
            $(id).append(retorno);
            $(id).show();
                
        //}
   });
$(document).on('click','.typeProce',function(){
    id=$(this).attr('id');
     $("#"+id).keyup();
});
$(document).on('keyup','.typeProce',function(){
        
        id=$(this).attr('id');
        id1="#"+id;
        id ="#sug-"+id;

       var retorno="<ul id='articulos-lista-consolida'>";

      
      
        //if($(this).val()!=''){
            var buscar = $(this).val();//($(this).val()=='')? $(this).val(' ') :$(this).val();  
            
            var buscarCoicidencia = new RegExp(buscar , "i");
           
            var arr = jQuery.map(typeProceObj, function (index) {
                    
                return index.match(buscarCoicidencia) ? index : null;
            });
            // console.log(buscarCoicidencia);
            // console.log(arr);
                
                for (a=0; a<arr.length;a++){
                  //val=arr[a];
                  
                  retorno+="<li style='with:auto' onClick='fnSelectData(\""+( arr[a])+"\",\""+id1+"\",\""+id+"\")'><a >  "+(arr[a])+"  </a></li>";
                    
                }
            retorno+="</ul>";
            $(id).show();
            $(id).empty();
            $(id).append(retorno);
            $(id).show();
                
        //}
   });
var curday = function(sp){
today = new Date();
var dd = today.getDate();
var mm = today.getMonth()+1; //As January is 0.
var yyyy = today.getFullYear();

if(dd<10) dd='0'+dd;
if(mm<10) mm='0'+mm;
return (mm+sp+dd+sp+yyyy);
};

function makeDelay(ms) {
    var timer = 0;
    return function(callback){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
};
var delay = makeDelay(250);

$(document).on('keyup', '.porcentajes', function(event) {
           actual=$(this).val();
           numero=0;
           id=$(this).attr('id');
           if(id.indexOf("porcentaje1ertrim")==0){
             numero=id.replace("porcentaje1ertrim","");
           }
            if(id.indexOf("porcentaje2dotrim")==0){
                  numero=id.replace("porcentaje2dotrim","");
            }
            if(id.indexOf("porcentaje3ertrim")==0){
                numero=id.replace("porcentaje3ertrim","");
            }
            if(id.indexOf("porcentaje4totrim")==0){
                numero=id.replace("porcentaje4totrim","");
            }
            p1=$("#porcentaje1ertrim"+numero).val();
            p2=$("#porcentaje2dotrim"+numero).val();
            p3=$("#porcentaje3ertrim"+numero).val();
            p4=$("#porcentaje4totrim"+numero).val();

          
            //percents=[];
            if(p1==""){
               // $("#porcentaje1ertrim"+x).val(0);
               p1=0;

            }
            //percents.push(p1);
             if(p2==""){
               // $("#porcentaje2dotrim"+x).val(0);
               p2=0;
            }
            //percents.push(p2);
             if(p3==""){
                //$("#porcentaje3ertrim"+x).val(0);
                p3=0;
            }
            //percents.push(p3);
             if(p4==""){
                //$("#porcentaje4totrim"+x).val(0);
                p4=0;
            }
            // percents.push(p4);
            
            // percents.sort( function(a,b) { return a - b; } );
            // console.log(percents);
            // less=percents[0];
            // higher=percents[3];

            
            // totalPercent=p1+p2+p3+p4;
            // if(totalPercent>limitPercent){
            //     setVal= $(this).val();
            //     //$(this).val((setVal) );
            // }
            // sum
            limitPercent=100;
            totalCurrent=0;
            
            if(id.indexOf("porcentaje1ertrim")==0){
              totalCurrent=parseInt(p2)+parseInt(p3)+parseInt(p4);
               
              limitCurrent=limitPercent-totalCurrent;

              if ( actual>limitCurrent ||(actual>limitPercent)  ){
                    $(this).val(limitCurrent);
              }

           }
            
            if(id.indexOf("porcentaje2dotrim")==0){
               totalCurrent=parseInt(p1)+parseInt(p3)+parseInt(p4);
              
              limitCurrent=limitPercent-totalCurrent;
              
              if ( actual>limitCurrent ||(actual>limitPercent)  ){
                    $(this).val(limitCurrent);
              }
            }
          

            if(id.indexOf("porcentaje3ertrim")==0){
            totalCurrent=parseInt(p1)+parseInt(p2)+parseInt(p4);
            
              limitCurrent=limitPercent-totalCurrent;

              if ( actual>limitCurrent ||(actual>limitPercent)  ){
                    $(this).val(limitCurrent);
              }
          
            }

            if(id.indexOf("porcentaje4totrim")==0){
              totalCurrent=parseInt(p1)+parseInt(p2)+parseInt(p3);
             
              limitCurrent=limitPercent-totalCurrent;

              if ( actual>limitCurrent ||(actual>limitPercent)  ){
                    $(this).val(limitCurrent);
              }
                
            }

});
$(document).on('focus', '.valorestimadoclase', function(event) {
   window.valorAnterior=$(this).val();
});

$(document).on('focusout', '.valorestimadoclase', function(event) {
   window.valorSiguiente=$(this).val();
});
$(document).on('change', '.valorestimadoclase', function(event) {


            if(id.indexOf("valorestimado")==0){
            n=id.replace("valorestimado","");
            partidaValEs=$("#partida"+n).val();
            pef=window.pefPartidas[partidaValEs];
          
            
            valCurrent=parseInt($(this).val());

            partidaValEs=$("#partida"+n).val();  
                 
            // if(valCurrent>pef ){
            
            //     fila=$("#0row"+n).html();
            //     //tablaOrigen=$('#valorestimado'+fila).parents("table:first");
            //     $(this).val(0);
            //     muestraModalGeneral(4,titulo,"Sobrepaso el monto del prepuesto por asignar <b>"+pef+"</b> en el <b>renglón "+fila+"</b>");
               
            //     // window.inputFocus=tablaOrigen+' > valorestimado'+fila;
            //     // console.log("prueba",window.inputFocus);
            //     // $("#btnCerrarModalGeneral").addClass("focusInput");
            //     // $("#btnCerrarIcono").addClass("focusInput");
            //     // 
       

            // }
            
              

            if(valCurrent>(pef) ){

                if(pef>0){


                fila=$("#0row"+n).html();
                //tablaOrigen=$('#valorestimado'+fila).parents("table:first");
                $(this).val(0);
                muestraModalGeneral(4,titulo,"Sobrepaso el monto del prepuesto por asignar <b>"+(pef)+"</b> en el <b>renglón "+fila+"</b>");
               
               
                }else {


                
                    asignadoMontoAux=0;
                   
                    montoDetalle=getDatosPresupuestoDetalle("#assetsTableDetails",partidaValEs)

                    tipo=$("#tipo"+n).val();
                    
                    if(tipo=="B"){
                     
                      montoAgregado=presupuestoGastado("#tablaBienes");
                    }else{
                         
                         montoAgregado=presupuestoGastado("#tablaServicios"); 
                    }

                    asignadoMontoAux=montoDetalle+montoAgregado;
                
                if((pef==0) &&(valCurrent==asignadoMontoAux)) {
                    
                    window.pefPartidas[partidaValEs]=asignadoMontoAux-valCurrent;
                    $(this).val(valCurrent); 
                    //return ;
                }

                   if( (pef==0) && (valCurrent!=asignadoMontoAux)){
                            $(this).val(0);
            
                        }


                 
                
                    }// FI PEF  <=0
                }

            
                  
            
            if((parseFloat($("#valormipymes"+n).val()))>valCurrent){
                $("#valormipymes"+n).val(valCurrent);
            }
             if((parseFloat($("#valorenctlc"+n).val()))>valCurrent){
                $("#valorenctlc"+n).val(valCurrent);
             } 

            if(valCurrent==0||(valCurrent=="")){
             
             $("#valormipymes"+n).attr("readonly",'true');
             $("#valorenctlc"+n).attr("readonly",'true');

              $("#valormipymes"+n).val("0");
              $("#valorenctlc"+n).val("0");

            }else{
                
              $("#valormipymes"+n).removeAttr("readonly");
              $("#valorenctlc"+n).removeAttr("readonly");
            }

            if($("#plurianual"+n).val()==1){
                $("#valortotalplurianual"+n).val(valCurrent+1);
            }
            
        }



        if(id.indexOf("valormipymes")==0){
              n=id.replace("valormipymes","");
              actual=parseInt($(this).val());
              fila=$("#0row"+n).html();
              //pef=parseInt($("#pef"+n).val());
              // this  is to calculated limist between tlc and pymes if exist val in tcl
              /*totalCurrent=parseInt(($("#valorenctlc"+n).val()|| 0) );
              limitCurrent=parseInt(($("#valorestimado"+n).val()|| 0))-totalCurrent;
              */
               ve=parseInt($("#valorestimado"+n).val());
              if ( actual>ve){ //actual>limitCurrent 
                  
                    $(this).val(ve);//limitCurrent)
                     muestraModalGeneral(4,titulo,"Valor MIPyMES no debe ser mayor al valor estimado en el <b>renglón "+fila+"</b>");
               
              }
              //console.log("actual" ,actual,"pef",pef,"estimaod",$("#valorestimado"+n).val(),"limit",limitCurrent);
        }
        if(id.indexOf("valorenctlc")==0){
              n=id.replace("valorenctlc","");
              actual=parseInt($(this).val());
              //pef=parseInt($("#pef"+n).val());
              
              /*totalCurrent=parseInt(($("#valormipymes"+n).val() || 0));
              limitCurrent=parseInt(($("#valorestimado"+n).val()|| 0))-totalCurrent;
              */
              ve=parseInt($("#valorestimado"+n).val());
             
              if (actual>ve ){ //actual>limitCurrent ||
                    muestraModalGeneral(4,titulo,"Valor NCTLC no debe ser mayor al valor estimado en el <b>renglón "+fila+"</b>");
                    $(this).val(ve);//limitCurrent)

              }
        }
    $('div').removeClass("modal-backdrop");

});

$(document).on('change', '.totalpluri', function() {
    n = id.replace("valortotalplurianual", "");
    console.log("prueba","total pluri");
    fila=$("#0row"+n).html();
    tp=parseFloat($(this).val());
    ve=parseFloat($("#valorestimado" + n).val());
    if (tp < ve) {
        $(this).val(parseFloat($("#valorestimado" + n).val()) + 1);
         muestraModalGeneral(4,titulo,"Valor total plurianual  no debe ser menor al valor estimado en el <b>renglón "+fila+"</b>");
    }

    $('div').removeClass("modal-backdrop");

});

$(document).on('keyup', '.onlyNumbers', function(event) {

    valor = $(this).val();
    valorantes = 0;
    max = $(this).attr('max');
    id = $(this).attr('id');

    if (max == 1) { // case when plurianual
        $(this).val($(this).val().replace(/[^0-1\.]/g, ''));
    } else { //default
        $(this).val($(this).val().replace(/[^0-9\.]/g, ''));
    }

    valorantes = valor;
    if ((valor) < 0) {
        $(this).val(0);
    }
    if (valor.length > max) {
        $(this).val(valorantes);

        event.preventDefault();
        $(this).val((valor.slice(0, -1))); // quita el ultimo  numero para que no se bloquee
        return false;
    }
});
//  valor iva
$(document).on('keyup', '.viva', function(event) {

    valor = $(this).val();
    valorantes = 0;
    max = $(this).attr('max');
    id = $(this).attr('id');

    

    valorantes = valor;
    if ((valor) < 0) {
        $(this).val(0);
    }
    if(valor>100){
         $(this).val(100);
    }
    if (valor.length > max) {
        $(this).val(valorantes);

        event.preventDefault();
        $(this).val((valor.slice(0, -1))); // quita el ultimo  numero para que no se bloquee
        return false;
    }
});
function calcularIva(valEstimado,valIva){


    if(valEstimado>=0){
        valEstimado=parseFloat(valEstimado);
    }else{
        valEstimado=0;
    }
    if(valIva>=0){
    valIva=((parseFloat(valIva))/100);
   }else{
        valIva=0;
   }
    aux=valEstimado*valIva
    valorbruto=valEstimado+aux;
    aux2=0;
    if(valorbruto>=0){
        aux2=valorbruto;
    }else{
        valorbruto=0;
    }
    return aux2;
}
$(document).on('keyup', '.viva', function(event) {

   valIva=$(this).val();
   if(valIva>=0){

       id = $(this).attr('id');
       numero= id.replace("valoriva","");
       valEstimado=$("#valorestimado"+numero).val();

       aux2=calcularIva(valEstimado,valIva);
        $("#valorbruto"+numero).val(aux2);
    
    }

});

$(document).on('keyup', '.plurianual', function(event) {
    n = -1;
    id = $(this).attr('id');
    n = id.replace("plurianual", "");
    if (n != -1) {

        if ($(this).val() == 0) {
            $("#aniosplurianuales" + n).attr("readonly", 'true');
            $("#aniosplurianuales" + n).val("NA");
            $("#valortotalplurianual" + n).attr("readonly", 'true');
            $("#valortotalplurianual" + n).val("NA");
        } else {


            $("#aniosplurianuales" + n).removeAttr("readonly");
            $("#aniosplurianuales" + n).val("0");
            $("#valortotalplurianual" + n).removeAttr("readonly");
            $("#valortotalplurianual" + n).val("0");

        }
    }

});

$(document).on('click','.units',function(){
     id=$(this).attr('id');
     $("#"+id).keyup();
});
$(document).on('keyup','.units',function(){
        
        id=$(this).attr('id');
        id1="#"+id;
        //id= id.replace("partida","");
        //id2="#hide"+id;
        id ="#sug-"+id;
       //  aux=[];
       // for(ad in window.units){
       //    aux.push(ad);
       // }
       var retorno="<ul id='articulos-lista-consolida'>";

            var buscar = $(this).val();//($(this).val()=='')? $(this).val(' ') :$(this).val();  
            
            var buscarCoicidencia = new RegExp(buscar , "i");
           
            var arr = jQuery.map(units, function (value,index) {
                    
                return index.match(buscarCoicidencia) ? index : null;
            });
            // console.log(buscarCoicidencia);
            // console.log(arr);
                
                for (a=0; a<arr.length;a++){
                  //val=arr[a];
                  
                  retorno+="<li style='with:auto' onClick='fnSelectData(\""+(arr[a])+"\",\""+id1+"\",\""+id+"\")'><a >  "+(arr[a])+"  </a></li>";
                    
                }
            retorno+="</ul>";
            $(id).show();
            $(id).empty();
            $(id).append(retorno);
            $(id).show();
                
        //}
   });
function getDatos(tablaSel, elementos, tipoPartida = 'B') {
    datosSend = new Array();

    var filas = {};
    var renglones = [];
    var valor;

    $(tablaSel + " tbody tr").each(function() {
        fila = $(this).attr('id');
        fila = fila.replace("fila", "");
        valorestimado = $("#fila" + fila + " #" + (elementos[4] + fila)).val();
        cantidad = $("#fila" + fila + " #" + (elementos[7] + fila)).val();
        intoDetail = $("#fila" + fila + " #" + (elementos[20] + fila)).val();

        $.each(elementos, function(index, v) {


            if ((valorestimado > 0) && (cantidad > 0) && (intoDetail == 0)) {


                //tipo=$("#" + v + fila ).attr('type');

                // if (tipo == "undefined" ||(tipo== null)) {
                //     valor = $("#" + v + fila + " option:selected").text();
                // } else {
                //obtengo fila
                if (index == 0) {
                    ad = $("#" + v + fila).html();
                } else {
                    ad = $("#" + v + fila).val(); // get val
                }
                //fin obtengo fila

                if ((index == 8) || (index == 9) || (index == 10) || (index == 18)) { // discard fecha registro

                    //
                    if (ad.indexOf("-") >= 1) { // if. have "-" //delete "-" where. exits

                        ad = ad.split("-"); // split. by "-"
                        valor = ad[0]; // set only the fisrt element

                    } else {

                        if (index == 0) {
                            valor = $("#" + v + fila).html();
                        } else {
                            valor = $("#" + v + fila).val(); // get val
                        }
                        //valor = $("#" + v + fila).val(); 
                    }

                    //filas[v]=valor; ////assoativo
                    //}
                } else {

                    if (index == 0) {
                            valor = $("#" + v + fila).html();
                        } else {
                            valor = $("#" + v + fila).val(); // get val
                        }
                }
                if ((valor == '') || (valor == null) || (valor == 'NA')) {
                    if ((index == 8) || (index == 9) || (index == 10) || (index == 18)) {
                        valor = "";
                    } else {
                        valor = 0;
                    }
                }


                if (index == 21) {
                    if (valor == 0) {
                        valor = tipoPartida;
                    }
                    //renglones.push(aux2);
//                    console.log("index", index, "queEs", v, "valor", valor);
                }
                if (index != 20) { // quio intoDetail
                    renglones.push(valor);
                }


            } // if  valorestimado and cantidad diffetent to cero all saved

        });
        //renglones.splice(-1,1);//delete into detail
        //renglones.push(tipoPartida);
        if (renglones.length > 0) {
            datosSend.push(renglones);
        }

        //filas=[];//assoativo
        renglones = [];
    });
    //ret=datosSend.filter(function(obj) { return obj }); 
    //adya=datosSend.filter(function(e){ return e === 0 || e });
    //console.log(datosSend);
    return datosSend;

}

function esNumero(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}
function validarPorcentajes(tabla) {
    elementos = ["porcentaje1ertrim",
        "porcentaje2dotrim",
        "porcentaje3ertrim",
        "porcentaje4totrim"
    ];
    count = 0;
    mensaje ='';
    $(tabla + " tbody tr").each(function() {
        fila = $(this).attr('id');
        numero = fila.replace("fila", "");
        totalPorcentaje = 0;
        valor = 0;
        filaMostrar = $("#0row" + numero).html();

        v1 = $("#valorestimado" + numero).val();

        c1 = $("#cantidad" + numero).val();

        if (c1 == "") {
            c1 = 0;
        }
        if (v1 == "") {
            v1 = 0;
        }


        if ((v1 > 0) && (c1 > 0)) {
            $.each(elementos, function(index, value) {

                valor = $("#" + value + numero).val();
                if (valor == "") {
                    valor = 0;
                }
                totalPorcentaje += parseFloat(valor);

            }); // fin recorrer columnas

            if (totalPorcentaje != 100) {

                mensaje += "<br><i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> En el reglón <b>" + filaMostrar + "</b> la <b>suma de los porcentajes </b> no es 100 %";
                count++;
            }
        }
    });
    data = [];
    data.push(count);
    data.push(mensaje);
    return data;
}

function validarVacios(tabla) {

    elementos = ["unidadmedida",
            "caracterprocedimiento",
        "entidadfederativa",
        "tipoprocedimiento",
        "plurianual"
    ];
    leyenda=[
            "unidad de medida",
            "caracter de procedimiento",
             "entidad federativa",
             "tipo de procedimiento",
             "pluri anual"
    ];
    count = 0;
    mensaje = '';
    $(tabla + " tbody tr").each(function() {
        fila = $(this).attr('id');
        numero = fila.replace("fila", "");
        totalPorcentaje = 0;
        valor = 0;
        filaMostrar = $("#0row" + numero).html();

        v1 = $("#valorestimado" + numero).val();

        c1 = $("#cantidad" + numero).val();

        if (c1 == "") {
            c1 = 0;
        }
        if (v1 == "") {
            v1 = 0;
        }


        if ((v1 > 0) && (c1 > 0)) {
            msgAux='';
            msgAux2='';
            count2=0;
            $.each(elementos, function(index, value) {
                if(index!=4){


                    valor = $("#" + value + numero).val();
                if (valor.indexOf("-") >= 1) {

                    ad = valor.split("-"); // split. by "-"
                    valor = ad[0]; // set only the fisrt element

                }

                if(valor==""){
                    msgAux += "<br><i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> El campo <b>"+leyenda[index]+"</b> no tiene un valor valido";
                    count++;
                }
            }
            //     }else{

            //            valor = $("#" + value + numero).val();
                
            //     if(valor=="1"){
            //         count2=0;
            //         aux1=$("#aniosplurianuales"+numero).val();
            //         aux2=$("#valortotalplurianual"+numero).val();

            //         if((aux1=="") || (aux1=="0")){
            //         msgAux2 += "<br><i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> El campo años plurianuales esta vacio no debe estar vacio si se activo el campo <b>"+leyenda[index]+"</b> ";
            //         count2++;
            //         }
            //         if((aux2=="") || (aux2=="0")){
            //         msgAux2 += "<br><i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> El campo  valor total plurianual esta vacio no debe estar vacio si se activo el campo <b>"+leyenda[index]+"</b> ";
            //         count2++;
            //         }
                    
            //     }
            //      if(count2>0){
            //         count++;
            //     }

            // }
           

            }); // fin recorrer columnas
           
            if(count>0){
            mensaje+="En el reglón <b>" + filaMostrar + "</b>"+ msgAux+msgAux2;

            }
        }
        
     
        
    });
    data = [];
    data.push(count);
    data.push(mensaje);
    return data;

}

function presupuestoGastado(tablaSel) {
    datosSend = new Array();

    var renglones = [];
    var valor;
    asignado = 0;
    rows = $(tablaSel + ' >tbody >tr').length;

    if (rows > 0) {

        $(tablaSel + " tbody tr").each(function() {
            fila = $(this).attr('id');
            fila = fila.replace("fila", "");

            valEestimado1 = $("#valorestimado" + fila).val();
            if (valEestimado1 != "") {

                valEestimado1 = parseInt(valEestimado1);
                asignado += valEestimado1;

            }

        });

    }
    return asignado;
}
function getDatosPresupuestoDetalle(tablaDetalle,partidaDetalle) {
    datosSend = new Array();

    var filas = {};
    var renglones = [];
    var valor;
    monto=0;
    auxDeta=0;
    partiEstaEnTablBien=0;
    partiEstaEnTablSer=0;
    
    // partiEstaEnTablBien=$("#partidasAssets").val();
    // partiEstaEnTablSer=$("#partidasServices").val();

    // if((partiEstaEnTablBien>0) && ( partiEstaEnTablSer<=0)){
    //     auxDeta=presupuestoGastado("#tablaBienes");
    // }
    // if((partiEstaEnTablBien==0) && ( partiEstaEnTablSer<=0)){
    //     auxDeta=presupuestoGastado("#tablaServicios");
    // }
    rows = $(tablaDetalle + ' >tbody >tr').length;
    // console.log("en tabala agregar",monto);
    if(rows>0){

        $(tablaDetalle + " tbody tr").each(function() {
        fila = $(this).attr('id');
        fila = fila.replace("fila", "");

        valEestimado1=$("#valorestimado"+fila).val();
        if(valEestimado1!=""){
            axuxPartidaDetalle=$("#partida"+fila).val();
            if(partidaDetalle==axuxPartidaDetalle){
            valEestimado1=parseInt(valEestimado1);
            monto+=valEestimado1;
            }
        }

        });
      
    }

    return monto;
      
}

// funcion que trae los archivos a mostrar en grid
function tablaArchivosPaas(){
    var funcionArchivo=$("#funcionArchivos").val();
    var tipo=$("#tipoArchivo").val();

    dataObj= {
        proceso: 'obtenerDatosArchivos',
        funcion: funcionArchivo,
        tipo: tipo,
        trans: window.numberScene
    };

    $.ajax({
        async: false,
        cache:false,
        method: "post",
        dataType:"json",
        url: "includes/Subir_Archivos.php",
        data: dataObj
    })
    .done(function(data){
        if(data.result){
            datosArchivos=data.contenido.DatosArchivos;
            fnLimpiarTabla('tablaArchivosPaaas', 'divDatosArchivosPaaas');   

            columnasNombres = '';
            columnasNombres += "[";
            //columnasNombres += "{ name: 'id1', type: 'bool'},";
            columnasNombres += "{ name: 'cajacheckbox', type: 'bool' },";
            columnasNombres += "{ name: 'id', type: 'string' },";
            columnasNombres += "{ name: 'tipo', type: 'string' },";
            columnasNombres += "{ name: 'nombre',type:'string'},";
            columnasNombres += "{ name: 'funcion',type:'string'},";
            columnasNombres += "{ name: 'tipo_doc',type:'string'},";
            columnasNombres += "{ name: 'usuario', type: 'string' },";
            columnasNombres += "{ name: 'fecha', type: 'string' }";

            columnasNombres += "]";
            //Columnas para el GRID
            columnasNombresGrid = '';
            columnasNombresGrid += "[";
            columnasNombresGrid += " { text: '', datafield: 'cajacheckbox', width: '4%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
            columnasNombresGrid += " { text: 'id', datafield: 'id', width: '10%', cellsalign: 'center', align: 'center', hidden: true },";
            columnasNombresGrid += " { text: 'Extensión', datafield: 'tipo', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Nombre archivo',datafield: 'nombre', width: '19%', align: 'center',hidden: false,cellsalign: 'left' },";
            columnasNombresGrid += " { text: 'Función',datafield: 'funcion', width: '18%', align: 'center',hidden: false,cellsalign: 'left' },";
            columnasNombresGrid += " { text: 'Tipo documento',datafield: 'tipo_doc', width: '18%', align: 'center',hidden: false,cellsalign: 'left' },";
            columnasNombresGrid += " { text: 'Usuario',datafield: 'usuario', width: '18%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Fecha', datafield: 'fecha', width: '18%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += "]";

            var columnasExcel = [2,3,4,5,6,7];
            var columnasVisuales = [0,2,3,4,5,6,7];
            nombreExcel = data.contenido.nombreExcel;

            fnAgregarGrid_Detalle(data.contenido.DatosArchivos, columnasNombres, columnasNombresGrid, 'divDatosArchivosPaaas', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
        }else{
           ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}

function archivosPaaasGrid() {

    var griddata = $('#tablaArchivosPaaas > #divDatosArchivosPaaas').jqxGrid('getdatainformation');
    var datos = [];
    for (var i = 0; i < griddata.rowscount; i++) {
        caja = $('#tablaArchivosPaaas > #divDatosArchivosPaaas').jqxGrid('getcellvalue', i, 'cajacheckbox');

        if (caja == true) {
            id = $('#tablaArchivosPaaas > #divDatosArchivosPaaas').jqxGrid('getcellvalue', i, 'id');
            datos.push(id);
     
        }
    }
return datos;
}
function fnProcesosArchivosSubidos(tipoproceso,tipo=0){
   
    var datos = [];
    datos=archivosPaaasGrid();
    /*$("input:checkbox[name=datoArchivo]:checked").each(function(){
        datos.push($(this).val());
    }); */


    var dataObj;
    if(datos.length > 0){
        switch(tipoproceso){
            // case 'eliminar':
            // if(tipo!=0 && tipo==19){
            //   idrequisicion=$('#idtxtRequisicion').val();
            //     }else{
            //         idrequisicion='';
            //     }
            //     dataObj = {
            //         proceso: 'eliminarArchivosSubidos',
            //         archivos:datos,
            //         requisicion: idrequisicion
            //     };
            //     break;
            case 'descargar':
                dataObj = {
                    proceso: 'descargarArchivos',
                    archivos:datos
                };
                break;
        }
        $.ajax({
            async: false,
            cache:false,
            method: "POST",
            dataType:"json",
            url: "includes/Subir_Archivos.php",
            data:dataObj
        })
            .done(function( data ){
                if(data.result){
                    info=data.contenido;
                       var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(4, titulo,info);
                    

                
                }else{
                
                    $("#accionesArchivos").hide();
                    $("#subirArchivos").show();
                }

            })
            .fail(function(result) {
                console.log("ERROR");
                console.log( result );
               
                $("#accionesArchivos").hide();
                $("#subirArchivos").show();
            });
    }else{
       // muestraMensaje('Seleccioné un archivo',3, 'mensajeArchivos', 5000);
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(4, window.titulo,'Seleccioné un archivo');
    }

    $('div').removeClass("modal-backdrop");

}

//  $(document).on('focus',".valorEstimadoCl",function(e){

//         e.preventDefault();
//      montoValEs=$(this).val();
// //     if(valAnteriorPef!=valorNeuvo){
//       // $(this).change();
   


//  });

// $(document).on('focusout',".valorEstimadoCl",function(e){
//        e.preventDefault();
//      valorNuevo= $(this).val();
//      idValEs=$(this).attr('id');
//      idValEs=idValEs.replace("valorestimado","");
 
//      if(montoValEs!=valorNuevo){

//           if($(this).val()==''){
//                 montoValEs=0;
//             }else{
//                 montoValEs=parseFloat($(this).val());
//             }
//          calcular(idValEs);
//      }


// });
function calcular(idValEs){
     partidaValEs=$("#partida"+idValEs).val();
    producto=$("#clavecucop"+idValEs).val();
   
    asignado=0;
    porasignar=0;
   
    original=getValWhenDetail3(partidaValEs,producto,assets); //$("#presuBienOriginal").val();
    // console.log(original);
    // console.log("valorActual",idValEs,"partidaValEs",partidaValEs,"producto",producto);
    
    // const size = Object.getOwnPropertyNames(montosValEsti);
 
    // if(size>0){
    //     montosValEsti[producto].valEsti=montoValEs;
    // }

      
    asignado=montoValEs;
    porasignar=0;
 
      for(ya in montosValEsti){
       
        if(partidaValEs==montosValEsti[ya].partida){

        
        asignado+=parseFloat(montosValEsti[ya].valEsti);
         }

      }
    
     
   console.log("asiganado"+asignado);
    window.pefPartidas[partidaValEs]=parseFloat( original[0].budget)-parseFloat(asignado);
    //console.log("Despues",'asignado',asignado,'Porasignar',window.pefPartidas[partidaValEs]);

    montosValEsti[producto]=({'valEsti':montoValEs,'partida':partidaValEs,'asignado':asignado,'porasignar': window.pefPartidas[partidaValEs],'id':idValEs});
 
    $("#porAsignarBieneTxt").val(""+window.pefPartidas[partidaValEs]);
    //console.log("total",pefPartidas[partidaValEs]);
    console.log(montosValEsti);
}
// $(document).on('change','.valorEstimadoCl',function (e){

//      idValEs=$(this).attr('id');
//     idValEs=idValEs.replace("valorestimado","");
//     partidaValEs=$("#partida"+ idValEs).val();
//     $("#pefAnterior").val(window.pefPartidas[partidaValEs]);

// });
$(document).on('change','.valorEstimadoCl',function (e){
    e.preventDefault();
    
     if($(this).val()==''){
        montoValEs=0;
    }else{
        montoValEs=parseFloat($(this).val());
    }
    //console.log("cambio","val",montoValEs);
    idValEs=$(this).attr('id');
    idValEs=idValEs.replace("valorestimado","");

     asignado=presupuestoGastado("#tablaBienes");
   
    partidaValEs=$("#partida"+ idValEs).val();
    producto=$("#clavecucop"+ idValEs).val();
    original= getValWhenDetail3(partidaValEs,producto,assets);
    montoDetalle=getDatosPresupuestoDetalle("#assetsTableDetails",partidaValEs);
    window.pefPartidas[partidaValEs]=parseFloat( original[0].budget)-parseFloat(asignado+montoDetalle);
    $("#porAsignarBieneTxt").val(""+window.pefPartidas[partidaValEs]);
    $("#asignadoBienesTxt").val(asignado+montoDetalle);

    $("#presupuestoAsignado").val(asignado+montoDetalle);
    $("#presupuestoPorAsignar").val(window.pefPartidas[partidaValEs]);

});
// $(document).on('change','.estiDataDetail',function (e){
//     idValEs=$(this).attr('id');
//     idValEs=idValEs.replace("valorestimado","");
//     partidaValEs=$("#partida"+ idValEs).val();
//     // axuAntDeta= parseFloat($("#pefAnterior").val());
//     // if(axuAntDeta==0){
        
//     //     $("#pefAnterior").val(window.pefPartidas[partidaValEs]); 
//     // }else{
//        // $("#pefAnterior").val(window.pefPartidas[partidaValEs]);
//     //}
    
//  }
// );
$(document).on('focus','.cambioValDe',function(){

    idValEs=$(this).attr('id');
    idValEs=idValEs.replace("valorestimado","");
    partidaValEs=$("#partida"+ idValEs).val();
    $("#partidaSelDeta").val(partidaValEs);
    $("#montoDetallePorAsignar").val(window.pefPartidas[partidaValEs]);
    tipo=$("#tipo").val();
    mfocusD=getDatosPresupuestoDetalle("#assetsTableDetails",partidaValEs);
    mfocusDAgre=0;
    if(tipo=="B"){
      
      mfocusDAgre=presupuestoGastado("#tablaBienes");
    }else{
        
         mfocusDAgre=presupuestoGastado("#tablaServicios"); 
    }
    //aux=$("#montoDetallePorAsignar").val();
    $("#montoDetalleAsignado").val(parseFloat(mfocusD+mfocusDAgre) );
    // console.log("prueba",mfocusDAgre);
    // console.log("prueba",mfocusD);
});
$(document).on('focusout','.cambioValDe',function(){

    idValEs=$(this).attr('id');
    idValEs=idValEs.replace("valorestimado","");
    partidaValEs=$("#partida"+ idValEs).val();
    $("#partidaSelDeta").val(partidaValEs);
    $("#montoDetallePorAsignar").val(window.pefPartidas[partidaValEs]);
    tipo=$("#tipo").val();
    mfocusD=getDatosPresupuestoDetalle("#assetsTableDetails",partidaValEs);
    mfocusDAgre=0;
    if(tipo=="B"){
      
      mfocusDAgre=presupuestoGastado("#tablaBienes");
    }else{
        
         mfocusDAgre=presupuestoGastado("#tablaServicios"); 
    }
    //aux=$("#montoDetallePorAsignar").val();
    $("#montoDetalleAsignado").val(parseFloat(mfocusD+mfocusDAgre) );
    // console.log("prueba",mfocusDAgre);
    // console.log("prueba",mfocusD);
});
$(document).on('change','.estiDataDetail',function (e){
    e.preventDefault();
    original=0;
       montoAgregado=0;
     if($(this).val()==''){
        montoValEs=0;
    }else{
        montoValEs=parseFloat($(this).val());
    }
   
    idValEs=$(this).attr('id');
    idValEs=idValEs.replace("valorestimado","");
    partidaValEs=$("#partida"+ idValEs).val();
    producto=$("#clavecucop"+ idValEs).val();
    montoDetalle=getDatosPresupuestoDetalle("#assetsTableDetails",partidaValEs);

    $("#partidaSelDeta").val(""+partidaValEs);
    

    tipo=$("#tipo"+idValEs).val();
   
    producto=$("#clavecucop"+ idValEs).val();
    if(tipo=="B"){
      original=getValWhenDetail3(partidaValEs,producto,assets);  
      montoAgregado=presupuestoGastado("#tablaBienes");
    }else{
        original=getValWhenDetail3(partidaValEs,producto,services); 
         montoAgregado=presupuestoGastado("#tablaServicios"); 
    }
 
    partiEstaEnTablBien=$("#partidasAssets").val();
    partiEstaEnTablSer=$("#partidasServices").val();
    
    $("#montoDetallePorAsignar").val(""+original[0].budget);
    $("#montoDetalleAsignado").val(""+(montoDetalle+montoAgregado));
    window.pefPartidas[partidaValEs]=parseFloat( original[0].budget)-parseFloat(montoDetalle+montoAgregado);


      if((partiEstaEnTablBien==partidaValEs)){
        $("#porAsignarBieneTxt").val(parseFloat( original[0].budget)-parseFloat(montoDetalle+montoAgregado));
        $("#asignadoBienesTxt").val(montoDetalle+montoAgregado);
    }
    if(partiEstaEnTablSer==partidaValEs){
      
        $("#porAsignarSer").val(parseFloat( original[0].budget)-parseFloat(montoDetalle+montoAgregado));
        $("#asignadoSer").val(parseFloat( montoDetalle+montoAgregado));
       
    }
  

});

$(document).on('change','.valEstiSer',function (e){

     idValEs=$(this).attr('id');
    idValEs=idValEs.replace("valorestimado","");
    partidaValEs=$("#partida"+ idValEs).val();

    $("#pefAnterior").val(window.pefPartidas[partidaValEs]);

});

$(document).on('change','.valEstiSer',function (e){
    e.preventDefault();
    
     if($(this).val()==''){
        montoValEs=0;
    }else{
        montoValEs=parseFloat($(this).val());
    }
    console.log("cambio","val",montoValEs);
    idValEs=$(this).attr('id');
    idValEs=idValEs.replace("valorestimado","");

     asignado=presupuestoGastado("#tablaServicios");
   
    partidaValEs=$("#partida"+ idValEs).val();
    producto=$("#clavecucop"+ idValEs).val();
    original= getValWhenDetail3(partidaValEs,producto,services);
    montoDetalle=getDatosPresupuestoDetalle("#assetsTableDetails",partidaValEs);
    window.pefPartidas[partidaValEs]=parseFloat( original[0].budget)-parseFloat(asignado+montoDetalle);
    $("#porAsignarSer").val(""+window.pefPartidas[partidaValEs]);
    $("#asignadoSer").val(asignado+montoDetalle);

    $("#presupuestoAsignado").val(asignado+montoDetalle);
    $("#presupuestoPorAsignar").val(window.pefPartidas[partidaValEs]);

});



// $(document).on('click','#btnCerrarModalGeneral',function(){

//     if (!$(".folius").length) {
//     console.log("cerrar");
//     window.open('catalogoPaaas.php',"_self");
//     }
        

// });

$(document).on('keyup', '.oficio', function(e) {

    valor = $(this).val();
    valorantes = valor;

    max = $(this).attr('max');
    id = $(this).attr('id');

    $(this).val($(this).val().replace(/[^0-9./]/g, ''));

    if ((valor) < 0) {
        $(this).val(0);
    }

    if (valor.length > max) {

        $(this).val(valorantes);

        event.preventDefault();
        $(this).val((valor.slice(0, -1))); // quita el ultimo  numero para que no se bloquee

        return false;
    }

    
});

function regresarPanel() {
    window.open('catalogoPaaas.php', "_self");
}

$(document).on('click', '.cerrarPrimerGuardado', function() {
    regresarPanel();
});

// $(document).on('click', '.focusInput', function() {
//     $("#"+window.inputFocus).focus();
// });

/**
 * [checarPefPartidas verifica  si la todaas las partidas  son iguales a cero o mayores en caso de ser menor a cero indica que alguna partida excedió el presupuesto]
 * @return {[type]} [description]
 */
function checarPefPartidas(){
    var cuenta=0;
    var msj='';
    for(ad in pefPartidas){

        if(pefPartidas[ad]<0){
            msj+="<br><i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> La partida <b>"+ad+"</b> sobrepaso el presupuesto.";
            cuenta++;
        }
    }

    if(cuenta>0){
        muestraModalGeneral(4,titulo,msj);
    }
  return cuenta;
}
$(function() {

            if (window.seeDetail == true) {
                data = [];
                data.push(window.numberScene);
                fnenvioDatos("seeDetailPre", "managerDetails", data);
                setFolio(window.numberScene);

            }
            // trigger function  load  files
            $("#btnUploadFile").click(function(e) {
                e.preventDefault(); // to stop  load  to form
                fnLoadFiles();

            });

            $("#btnGuardar").click(function(e) {

                if (!$(".folius").length) {
                    if (checkVal() == 0) {
                        $("#btnGuardar").attr("disabled", 'true');

                        sendData(e);
                        tablaArchivosPaas();

                    }
                } else if (window.numberScene != 0) {

                    elementos = ["0row",
                        "clavecucop",
                        //"descripcion"         ,
                        // "ur"               ,
                        // "ue"               ,
                        //"numerooficio"      ,
                        "partida",
                        "pef",
                        "valorestimado",
                        "valormipymes",
                        "valorenctlc",
                        "cantidad",
                        "unidadmedida",
                        "caracterprocedimiento",
                        "entidadfederativa",
                        "porcentaje1ertrim",
                        "porcentaje2dotrim",
                        "porcentaje3ertrim",
                        "porcentaje4totrim",

                        "plurianual",
                        "aniosplurianuales",
                        "valortotalplurianual",
                        // "claveprogramafederal",
                        // "fechainicioobra"     ,
                        // "fechafinobra"        ,
                        "tipoprocedimiento",
                        "fecharegistro",
                        "intoDetail",
                        "tipo",
                        "valoriva"
                    ];
                    aux = [];
                    cuenta = 0;
                    auxSer = [];
                    allData = [];
                    mensajeAux='';
                    porcenAux1=[];
                    porcenAux2=[];
                    porcenAux3=[];
                    valVaciosAux1=[];
                    valVaciosAux2=[];
                    valVaciosAux3=[];
                    
                    if (window.dataDetail.length > 0) {
                        auxDetail = getDatos("#assetsTableDetails", elementos); // change to know when is asset and when is services
                        aux = auxDetail;         
                    }

                    if (aux.length > 0) {
                        assetAndDetail = getDatos("#tablaBienes", elementos);
                        aux = aux.concat(assetAndDetail);
                    } else {
                        aux = getDatos("#tablaBienes", elementos);
                    }

                    auxSer = getDatos("#tablaServicios", elementos, "D");
                    allData = aux.concat(auxSer);

                    last = {};
                    last.folio = window.numberScene;
                    last.oficio=$("#numberFolio").val();
                    last.anio  =$("#selectAnio").val();
                    last.obs   =$("#txtAreaObs").val();

                    allData.push(last);

                    //console.log(final);
                    rowsAssets = $('#tablaBienes >tbody >tr').length;
                    rowsServices = $('#tablaServicios >tbody >tr').length;
                    rowDeTails = $('#assetsTableDetails >tbody >tr').length;
                   
                    // if(rowsAssets>0|| rowsServices>0){
                    //$("#btnGuardar").attr("disabled",'true');

                    if (rowDeTails > 0) {
                        cuenta++;
                    } else if (rowsAssets > 0) {
                        cuenta++;
                    } else if (rowsServices > 0) {
                        cuenta++;
                    } else if (allData.length >1) { // mayor a uno por que  si no se agrego nada  al menos pone el folio
                        cuenta++;
                    }
                   
                    if (cuenta > 0) {
                        validarPorcen=0;

                        if(allData.length>1){
                            porcenAux1=validarPorcentajes("#assetsTableDetails");
                            valVaciosAux1=validarVacios("#assetsTableDetails");

                            porcenAux2=validarPorcentajes("#tablaBienes");                      
                            valVaciosAux2=validarVacios("#tablaBienes");
                           
                            porcenAux3=validarPorcentajes("#tablaServicios");
                            valVaciosAux3=validarVacios("#tablaServicios");
                    


                         if((porcenAux1[0]>0) || (valVaciosAux1[0]>0)){

                            mensajeAux+="<br><h5><b>En el panel de detalle</b></h5>";
                            mensajeAux+= valVaciosAux1[1];
                            mensajeAux+=porcenAux1[1];
                           
                            validarPorcen++;
                         }
                        if((porcenAux2[0]>0) || (valVaciosAux2[0]>0)){
                            mensajeAux+="<br><h5><b>En el panel de agregar bienes</b></h5> ";
                            mensajeAux+= valVaciosAux2[1];
                            mensajeAux+=porcenAux2[1];

                            validarPorcen++;
                         }
                        if((porcenAux3[0]>0) || (valVaciosAux3[0]>0)){
                            mensajeAux+="<br><h5><b>En el panel de agregar servicios</b> </h5>";
                            mensajeAux+= valVaciosAux3[1];
                            mensajeAux+=porcenAux3[1];

                            validarPorcen++;
                         }


                        if(validarPorcen==0){

                        if (($("#cargarMultiples").length) && ($("#cargarMultiples").val() != '')) {
                            sendData(e, 1);
                            tablaArchivosPaas();
                        }
                        ya=checarPefPartidas();
                        if(ya==0){
                            fnenvioDatos("GuardarDatos", "GuardarDatos", allData);
                        }
                        

                        }else{
                             muestraModalGeneral(4, titulo,"<div style='max-height: 400px; overflow-y: scroll;'>"+mensajeAux+ "</div>");   
                        }
                      }else{
                          muestraModalGeneral(4, titulo, 'Es necesario agregar algún bien o servicio.');
                      }


                    } else {
                        muestraModalGeneral(4, titulo, 'Es necesario agregar algún bien o servicio.');
                    }

                }

            });

  

    $("#comeBack").click(function(e){
        e.preventDefault();
         regresarPanel();
    });

    // end  trigger  function  load  files
    $("#showAssets").click(function(){
        aux=[];
        aux.push($("#partidasAssets").val()); //when is multiselect is not necessary convert to array

        showPartidas(aux,"#tablaBienes","B");

        $("#assetsDiv").show();
        if(window.dataDetail>0){
            // search 
        }
        //$("#porAsignarBieneTxt").val(""+($("#presupuestoPorAsignar").val() ) );
        
    });
     $("#showServices").click(function(){
        aux=[];
        aux.push($("#partidasServices").val());
        showPartidas(aux,"#tablaServicios","D");
        $("#servicesDiv").show();

      
    });

    tablaArchivosPaas();
    if($(".folius").length){
        $("#descargarMultiples").show();
    }

  $("#montosPorAsignarBtn").click(function(){
    contadorCeros=0;
    msjAux2="Las siguientes partidas tienen por asignar:";


    for(ya in pefPartidas){

        if(pefPartidas[ya]>0){
            contadorCeros++;
            msjAux2+="<br> <b>"+ya+"</b> monto: "+pefPartidas[ya];
        }
      
    }


      if(contadorCeros>0){
        muestraModalGeneral(4, titulo,"<div style='max-height: 400px; overflow-y: scroll;'>"+msjAux2+ "</div>");
      }else{
        muestraModalGeneral(4, titulo,"No hay presupuesto por asignar");
      }
          
  });
  $("#btnChange").click(function(){

     $("#ModalGeneral1").modal('show');
     $("#ModalGeneral1_Mensaje").empty(); 
     $("#ModalGeneral1_Mensaje").append("¿Está seguro de modificar la fechas?");

  });

  $("#confirmacionModalGeneral1").click(function(){
       fnChangeRanges();
        $("#ModalGeneral1").modal('hide');
        $("#btnChange").hide();
        $("#btnNewDate").show();
        $("#numberScene").val(window.numberScene);
         
    });

  $("#btnNewDate").click(function(e){
    verificarFechasCambios();
     if (($("#cargarMultiples").length) && ($("#cargarMultiples").val() != '') &&(contador==0)) {
        sendData(e);
        
    }
  });


    $('div').removeClass("modal-backdrop");

      
});
$(document).on('change','#partidasAssets',function(){

      rowsAssets = $('#tablaBienes >tbody >tr').length;
           if(rowsAssets>0){
            muestraModalGeneral(4,titulo,"Antes de cambiar  de partida  debe guardar.");
           }

    $('div').removeClass("modal-backdrop");

});
$(document).on('change','#partidasServices',function(){

       rowsAssets = $('#tablaServicios >tbody >tr').length;
           if(rowsAssets>0){
            muestraModalGeneral(4,titulo,"Antes de cambiar  de partida  debe guardar.");
           }
    $('div').removeClass("modal-backdrop");
});
function setFolio(folio){
     $("#numeroFolio").empty();
     $("#numeroFolio").append('<div class="text-rigth pr15"> Folio:<span class="folius"> ' + folio + ' </span> </div>');
}

function sendData(e,muestraMensaje=0) {
    e.preventDefault(); // to stop  load  to form

    var dataAd = new FormData();

    if(($("#cargarMultiples").length) &&($("#cargarMultiples").val()!='')){
    var nFile = document.getElementById('cargarMultiples').files.length;
     if(nFile>0){
        for (var x = 0; x < nFile; x++) {
            dataAd.append("archivos[]", document.getElementById('cargarMultiples').files[x]);
        }
       }

    dataAd.append('nopermitidos',archivosNopermitidos);
    dataAd.append('tipo', '2373');
    dataAd.append('ue', $("#selectUnidadEjecutora").val());
    dataAd.append('ur', $("#selectUnidadNegocio").val());
    dataAd.append('legal', $("#selectRazonSocial").val());
   
    dataAd.append('oficio', $("#numberFolio").val());
    dataAd.append('begin', $("#dateDesde").val());
    dataAd.append('end', $("#dateHasta").val());
    dataAd.append('year', $("#selectAnio").val());
    
    dataAd.append('comments',$("#txtAreaObs").val());
    dataAd.append('esmultiple',$("#esMultiple").val());
    dataAd.append('trans',window.numberScene);
    dataAd.append('funcion',$("#funcionArchivos").val());
    dataAd.append('numberScene',$("#numberScene").val());
    $.ajax({
            url: "includes/Subir_Archivos.php",
            dataType: 'json', //retorno servidor  recuerda que es json
            cache: false,
            scriptCharset: "iso-8859-1",
            contentType: false,
            processData: false,
            mimeType: "multipart/form-data",
            async: false,
            data: dataAd,
            type: 'post',
        })
        .done(function(res) {

            if(muestraMensaje==0){// esto es para evitar  que se muestre lo siguiente cuando se envia  partidas y archivos

                ad=res.contenido;
                muestraModalGeneral(4,titulo,ad.msg);
                if(ad.folio!='undefined'){
                setFolio(ad.folio);
                $("#btnCerrarModalGeneral").addClass("cerrarPrimerGuardado");
                $("#btnCerrarIcono").addClass("cerrarPrimerGuardado");
                // escena=$("#numberScene").val();
                // if(escena!=""){
                //     location.reload(); 
                // }

                }else{
                    $("#btnGuardar").removeAttr('disabled');
                }
              }
           
        })
        .fail(function(res) {
            console.log("ERROR");
            console.log(res);

        });
    }else{// end check value from cargarMultiples
        muestraModalGeneral(4, titulo,"<i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> Es necesario cargar un oficio.");
           $("#btnGuardar").removeAttr('disabled');
    }

    $('div').removeClass("modal-backdrop");
}
