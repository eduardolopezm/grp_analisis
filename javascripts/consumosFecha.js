//passs arturo lopez peña
/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña
 * @version 0.1
 */
function fnIniciarVariables() {
    window.hoy = new Date();
    window.cuentaFilas = 1;
    //window.nombreTabla = "tablaBienes";
    //window.tabla = $('#' + nombreTabla);
    window.body;
    //window.tbody = $(tabla.find('tbody'));
    window.modelo = "modelo/consumosFechaModelo.php";
    window.partidas = new Array();
    window.renglon = 1;
    window.titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
    window.elementos = ["numero", "partida", "clave", "descripArt", "um", "cantidadSolicitada"];
    window.anio = hoy.getFullYear();
    window.valores = new Array();
    window.partidas = new Array();
    window.partidasSer = new Array();
    window.clave = new Array();
    window.des = new Array();
    window.units = new Array();
    window.$meses= {'ene':0,'feb':0,'mar':0,'abr':0,'may':0,'jun':0,'jul':0,'ago':0,'sep':0,'oct':0,'nov':0,'dic':0};
    window.selPartidas= new Array();
    window.flagFiltrar=false;
    window.consumeAssetsProductsTotal={};
    window.consumeServicesTotal={};

    window.partidasAux={};
    window.partidaAssets={};
    window.partidaServices={};

    window.allPartidasBien= [];
    window.allClavesBien= [];
    window.allDescriBien= [];
    window.allUnitsBien=[];

    window.allPartidasSer= [];
    window.allClavesSer= [];
    window.allDescriSer= [];
    window.allUnitsSer=[];
    window.claves={};
    window.cops={};
}

function fnsetComponentes() {
    fnenvioDatos('getPartidas','partidas');
    //fnenvioDatos('getPartidasSer','partidasSer'); // active only when use  select
    $("#dateDesde").prop('disabled', true);
    $("#dateDesde").val("01-01-" + anio);


	// se agrega los estilos a las fechas para usar el 100% del contenedor
	// $('.input-group.date.componenteCalendarioClase').css({width:'100%'});
 //    $('.input-group.date.componenteDisableNextYear').css({width:'100%'});
    $("#btnProjected").attr("disabled", 'true');
    $("#btnGuardar").attr("disabled",'true');
    $("#addBien").attr("disabled",'true');
    $("#addServicios").attr("disabled",'true');
    
}

function fnImprimir(data) {

    console.log(data);
}
function setFolio(folio){
     $("#numeroFolio").empty();
     $("#numeroFolio").append('Folio:<span class="folius"> ' + folio + ' </span>');
}
/**
 * [fnAgregarFila description]
 * @param  {[type]}  elementos   ['array`s elements to add in new row']
 * @param  {Number}  paraPartida ['choosed if exit  multiselect to formated']
 * @param  {[type]}  tabla       ['to choosed table assets o services']
 * @param  {Boolean} quePartida  ['to choosed partida between assets or services']
 * @param  {Boolean}  btnEliminar  ['indicate to load delete button']
 * 
 */
function fnAgregarFila(elementos,paraPartida=0,tabla,quePartida=true,btnEliminar=true){
    var cols = [];
    var hide='';

    if(btnEliminar==true){
      eliminar = generateItem('div', {id: ''}, generateItem('div', { class: 'text-center pt15'}, generateItem('span', {class: 'btn btn-danger btn-xs glyphicon glyphicon-remove filaQuitar',title: 'eliminar',id: "btnEliminar" + cuentaFilas,type: "button"})));
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
        if(opts.name=='selEscondida[]' || opts.name=='zorigin[]'){
            hide='display: none;';
        }
        if(opts.name=='clave[]' ||(opts.name=='descripArt[]')){
            estilo='vertical-align:middle; min-width: 100px;max-width:100px; font-size: 12px; '+hide;
        }else{
          estilo='vertical-align:middle; min-width: 100px;max-width:100px;font-size: 12px; '+hide;  
        }
        cols.push(generateItem('td', {
            style: estilo
        }, contenido));


    });

    tr = generateItem('tr', {
        class: 'text-center w100p',
        id: 'fila' + cuentaFilas
    }, cols);
    $(tabla).find('tbody').append(tr);
    // this part  make  forma to multiselect
    if(paraPartida!=0){
         if(quePartida==true){
           // fnFormatoSelect("#partida" + cuentaFilas, partidas);
         }else{
            //fnFormatoSelect("#servicioPar" + cuentaFilas, partidasSer);
         }
         
    }
    // problems if add by array associative, so this a reason for load by code in this part
    
    $("#partida"+cuentaFilas).attr('autocomplete','off'); 
    $("#clave"+cuentaFilas).attr('autocomplete','off'); 
    $("#descripArt"+cuentaFilas).attr('autocomplete','off'); 
    
    if(tabla=="#tablaBienes"){
        //attr('data-info');
        $("#partida"+cuentaFilas).attr('data-ad','assets');
        $("#clave"+cuentaFilas).attr('data-ad','assets');
        $("#descripArt"+cuentaFilas).attr('data-ad','assets');
    }else{
       $("#partida"+cuentaFilas).attr('data-ad','services');
        $("#clave"+cuentaFilas).attr('data-ad','services');
        $("#descripArt"+cuentaFilas).attr('data-ad','services'); 
    }
    
    // end  add autocomplete 
    
    cuentaFilas++;

    // inputs = document.getElementsByTagName('input');
    // for (index = 0; index < inputs.length; ++index) {
    //  inputs[index].attr('autocomplete','off');
    // }

}
/**
 * [fnTabla description]
 * @param  {[type]} valores [description]
 * @return {[type]}         [description]
 */
function fnTabla(valores,zorigin="new") {  
    
    datos = valores.data; //bienes
    totalData= datos.length;
    
    if(totalData){

    consumesAssets=0;
    consumeTotalAssets=0;
    lastCostTotalAssets=0;
    totalAmountAssets=0;
    totalMonthsAssets={'zene':0,'zfeb':0,'zmar':0,'zabr':0,'zmay':0,'zjun':0,'zjul':0,'zago':0,'zsep':0,'zoct':0,'znov':0,'zdic':0};

    consumeServices=0;
    consumeTotalServices=0;
    lastCostTotalServices=0;
    totalAmountServices=0;
    totalMonthsServices={'zene':0,'zfeb':0,'zmar':0,'zabr':0,'zmay':0,'zjun':0,'zjul':0,'zago':0,'zsep':0,'zoct':0,'znov':0,'zdic':0};

    for (ad in datos) {
        product=datos[ad].id;
         var elementos = { 
        "ur":  {tag: 'span', opts: {html: (datos[ad].ur|| '-')}},
        "ue": {tag: 'span',opts: {html: (datos[ad].ue|| '-')}},
        "partida":   {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control text-center',type: 'text', val: (datos[ad].partida|| '-'),readonly: true}},
        "clave": {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control text-center',type: 'text', val:( product || ''),readonly: true}},
        "descripArt":       {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control text-center',type: 'text',val: (datos[ad].des|| ''),readonly: true}},
        "um":      {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control text-center',type: 'text',val: (datos[ad].unidad|| ''),readonly: true}},
        "cantidad": {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control text-right',type: 'text',val: (datos[ad].cantidad|| ''),readonly: true}},
        "ultimoCosto": {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control text-right',type: 'text',val: (datos[ad].ultimoCosto|| ''),readonly: true}},
        "importe":   {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control text-right',type: 'text',val: (datos[ad].importe|| ''),readonly: true}},
        };
        consumeTotalAssets+=parseFloat(datos[ad].cantidad);
        lastCostTotalAssets+= parseFloat(datos[ad].ultimoCosto);
        totalAmountAssets+=parseFloat(datos[ad].importe);

        //consumed by month
        aux = datos[ad].meses;
        consumesAssets=0;
        for(ad in aux){
            mes="z"+ad;
            elementos[mes]=( {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-right',type: 'text',val:(aux[ad]|| ''),readonly: true, onkeypress: 'return soloNumeros(event)'}});
            consumesAssets+=(parseFloat(aux[ad]) || 0); // add  month consume to sum consume total
            //totalMonthsAssets[mes]+=parseFloat(aux[ad]);//total global by month
        }
        
        endDate=$("#dateHasta").val();
        endDate= endDate.split("-");
        endDate= endDate[1];
        avgConusme=consumesAssets/(parseFloat(endDate));
        missingMonths= 12 -parseInt(endDate);
        avgMissingMonths=parseFloat(avgConusme)* parseFloat(missingMonths);
        consumeAssetsProductsTotal[('fila'+cuentaFilas)]=({sumTotal:consumesAssets, avg:(avgConusme), avgNextMonths:(avgMissingMonths) }); //consumesAssets;
        elementos['zorigin']= {tag: 'input', opts: {type:'hidden',val:((zorigin=="new")? "new":"cop")}};
        //console.log(elementos);
        //(elementos,paraPartida=0,tabla,quePartida=true,btnEliminar=true)
        fnAgregarFila(elementos,0,"#tablaBienes");
        
    }
    console.log(consumeAssetsProductsTotal);
    // // add global total  in last  row ASSETS
    // rowsAssets = $('#tablaBienes >tbody >tr').length;
    // if(rowsAssets>0){
    //  var total = { 
    //     "ur":   {tag: 'span', opts: {html: (' ')}},
    //     "ue":  {tag: 'span', opts: {html: (' ')}},
    //     "partida":   {tag: 'span', opts: {html: (' ')}},
    //     "clave": {tag: 'span', opts: {html: (' ')}},
    //     "descripArt":    {tag: 'span', opts: {html: (' ')}},
    //     "um":     {tag: 'span', opts: {html: ( 'Totales'),style:"color:#fff; font-size:14px"}},
    //     "cantidad": {tag: 'input',opts: {class: 'form-control text-right',type: 'text',val: (consumeTotalAssets|| ''),readonly: true}},
    //     "ultimoCosto": {tag: 'input',opts: {class: 'form-control text-right',type: 'text',val: (lastCostTotalAssets|| ''),readonly: true}},
    //     "importe": {tag: 'input',opts: {class: 'form-control text-right',type: 'text',val: ( totalAmountAssets),readonly: true}},

        
    //     };


    // // i have to removed  amounts total  beacuse  unit´s   measurement  are  different  in some  case
    // // for(ad in totalMonthsAssets){
    // //     total[ad]=( {tag: 'input',opts: {class: 'form-control  text-center',type: 'text',val:(totalMonthsAssets[ad]|| ''),readonly: true}});
    // // }
   
    // fnAgregarFila(total,0,"#tablaBienes",true,false);
    // $("#tablaBienes tr").last().addClass("bgc8");
   
    // }// end  add global total  in last  row  ASSETS

   // begin services  save into array for draw
    datos1 = valores.data2; //services
   
    for (ad in datos1) {
            var elementos = { 
        "ur":  {tag: 'span', opts: {html: (datos1[ad].ur|| '')}}, //datos[ad].ur||
        "ue": {tag: 'span',opts: {html: (datos1[ad].ue|| '')}}, //datos[ad].ur||
        "servicioPar":   {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control text-center',type: 'text', val: (datos1[ad].partida|| ''),readonly: true}},
        "clave": {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control text-center',type: 'text', val:(datos1[ad].id || ''),readonly: true}},
        "des":       {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control text-center',type: 'text',val: (datos1[ad].des|| ''),readonly: true}},
        "um":         {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control text-center',type: 'text',val: (datos1[ad].unidad|| ''),readonly: true}},
        "cantidad":   {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control text-right',type: 'text',val: (datos1[ad].cantidad|| ''),readonly: true}},
        "ultimoCosto":   {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control text-right',type: 'text',val: (datos1[ad].ultimoCosto|| ''),readonly: true}},
        "importe":   {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control text-right',type: 'text',val: (datos1[ad].importe|| ''),readonly: true}},
            
    
        };
        consumeTotalServices+=parseFloat(datos1[ad].cantidad);
        lastCostTotalServices+= parseFloat(datos1[ad].ultimoCosto);
        totalAmountServices+= parseFloat(datos1[ad].importe);
        
        //consumed by month
        aux1 = datos1[ad].meses;
       
         for(ad in aux1){
            mes="z"+ad;
            elementos[mes]=( {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control text-right',type: 'text',val:(aux1[ad]|| ''),readonly: true,onkeypress: 'return soloNumeros(event)'}});
            totalMonthsServices[mes]+=parseFloat(aux1[ad]);//total global by month

            consumeServices+=(parseFloat(aux1[ad]) || 0);
        }

        endDate=$("#dateHasta").val();
        endDate= endDate.split("-");
        endDate= endDate[1];
        avgConusmeSer=consumeServices/(parseFloat(endDate));
        missingMonthsSer= 12 -parseInt(endDate);
        avgMissingMonthsSer=parseFloat(avgConusmeSer)* parseFloat(missingMonthsSer);
        consumeServicesTotal[('fila'+cuentaFilas)]=({sumTotal:consumeServices, avg:(avgConusmeSer), avgNextMonths:(avgMissingMonthsSer) }); //consumesAssets;

        console.log(consumeServicesTotal);
         
        fnAgregarFila(elementos,0,"#tablaServicios");
       }
    //  // add global total  in last  row services
    // rowsServices = $('#tablaServicios >tbody >tr').length;
    // if(rowsServices>0){

    //  var totalServices = { 
    //     "ur":   {tag: 'span', opts: {html: (' ')}},
    //     "ue":  {tag: 'span', opts: {html: (' ')}},
    //     "partida":   {tag: 'span', opts: {html: (' ')}},
    //     "clave": {tag: 'span', opts: {html: (' ')}},
    //     "descripArt":    {tag: 'span', opts: {html: (' ')}},
    //     "um":     {tag: 'span', opts: {html: ( 'Totales' ),style:"color:#fff; font-size:14px"}},
    //     "cantidad": {tag: 'input',opts: {class: 'form-control text-right',type: 'text',val: ( ''),readonly: true}},
    //     "ultimoCosto": {tag: 'input',opts: {class: 'form-control text-right',type: 'text',val: (''),readonly: true}},
    //     "importe": {tag: 'input',opts: {class: 'form-control text-right',type: 'text',val: (''),readonly: true}},


    //     };
    // // i have to removed  amounts total  beacuse  unit´s   measurement  are  different  in some  case
    // // for(ad in totalMonthsServices){
    // //     totalServices[ad]=( {tag: 'input',opts: {class: 'form-control  text-center',type: 'text',val:(totalMonthsServices[ad]|| ''),readonly: true}});
    // // }

    // fnAgregarFila(totalServices,0,"#tablaServicios",true,false);
    // $("#tablaServicios tr").last().addClass("bgc8");
   
    // }// end  add global total  in last  row  services
     //$("#tablaBienes > thead > tr >th").addClass("bgc8");
    $("#tablaBienes > thead > tr >th").attr('style', 'background-color:#1B693F; vertical-align:middle; text-align:center;');
    $("#tablaServicios > thead > tr >th").attr('style', 'background-color:#1B693F; vertical-align:middle; text-align:center;');
    $("#btnProjected").removeAttr("disabled");
    
    }else{
        muestraModalGeneral(4,titulo,'No existe consumos en la en los  rangos de  fecha seleccionados.');
        $("#btnGuardar").removeAttr("disabled");
        $("#addBien").removeAttr("disabled");
        $("#addServicios").removeAttr("disabled");
    }
}

/**
 * [fnenvioDatos description]
 * @param  {[type]} donde      [description]
 * @param  {[type]} maneja     [description]
 * @param  {Number} datosSend  [description]
 * @param  {String} form       [description]
 * @param  {String} formAccion [description]
 * @return {[type]}            [description]
 */
function fnenvioDatos(donde, maneja,datosSend=0 ,form = '', formAccion = '') {

    if (form != '') {
        valores = $('#' + form).serialize();
        $.post(modelo, {
            getData: donde,
            accion: formAccion,
            valores
        }, function(data) {

            returnData(data, maneja);

        });
    } else {
       
       
        $.post(modelo, {
            getData: donde,
            datos:datosSend
        }, function(data) {
            returnData(data, maneja);
        });
    }


}
function getIndexforDetail(buscar,array){
    var buscarCoicidencia = new RegExp(buscar , "i");
             //((from=='assets')?window.allClavesBien:window.allClavesSer)
    var arr1 = $.map(array, function (value,index) {
                 
                return value.match(buscarCoicidencia)? index: null;
    });
    return arr1;
 }
/**
 * [returnData description]
 * @param  {[type]} data   [description]
 * @param  {[type]} maneja [description]
 * @return {[type]}        [description]
 */
function returnData(data, maneja) {
   
    valores = JSON.parse(data);
   
    switch (maneja) {
        case 'imprimir':
            fnImprimir(valores);
            break;
        case 'partidas':
            // only use with select
            // window.partidas=valores;
            
            // window.partidas.forEach(function(e) {
            //    //console.log(e.toString());
            //     window.selPartidas.push(e);
            // });
            // end only use with select
           
            window.partidasAux=valores;
            window.partidaAssets=partidasAux[0].assets;
            window.partidaServices=partidasAux[1].services;

            aux= partidasAux[2].unicasBien;
            aux.forEach(function(e) {
                  window.partidas.push(e);
            });

            aux1= partidasAux[3].unicasSer;
            aux1.forEach(function(e) {
                  window.partidasSer.push(e);
            });
            partidaAssets.forEach(function(e){
                allPartidasBien.push(e.partida);
                allClavesBien.push(e.clave);
                allDescriBien.push(e.descrip);
                allUnitsBien.push(e.unidad);
            });

              partidaServices.forEach(function(e){
                allPartidasSer.push(e.partida);
                allClavesSer.push(e.clave);
                allDescriSer.push(e.descrip);
                allUnitsSer.push(e.unidad);
            });
            break;
        case 'filtrado':
                
                fnTabla(valores,"cop");
            
            break;
          case 'cambioPartida':

            clave = valores.clave;
            des = valores.des;
            units = valores.um;

            break;
        case 'partidasSer':

           // window.partidasSer=valores;
           // aux=window.partidasSer;
           // aux.shift();
           //  aux.forEach(function(e) {
           //      //console.log(e);
           //      window.selPartidas.push(e);
           //  });
  
           //  fnFormatoSelect("#selPartida", window.selPartidas);

            break;
        case 'GuardarDatos':
           
            muestraModalGeneral(4,titulo,valores[0].msg);
            if (!$(".folius").length) {
                setFolio(valores[0].folio);
            }else{
                setFolio(valores[0].folio);
            }
            break;
        case 'managerDetails':
        $("#filtrar").attr("disabled",'true');

        data=valores[0].data;
        months=valores[0].meses;
        ue=valores[0].ue;
        ur=valores[0].ur;
        cop=valores[0].cop;
        $("#dateHasta").val(cop);
        $("#dateHasta").attr("disabled",'true');
        fnSeleccionarDatosSelect("selectUnidadEjecutora",ue);
        $('#selectUnidadEjecutora').multiselect('disable');
        //fnSeleccionarDatosSelect("selectUnidadNegocio",ur);
        
        cop=cop.split("-");
        cop=cop[1];
        cops={};
        for (ad in data){

        claves[ad]=({clave:data[ad].clave,tipo:data[ad].tipo,fila:cuentaFilas});
        if(data[ad].origin!='new'){
             cops[ad]=({fila:cuentaFilas,cop:data[ad].origin,tipo:data[ad].tipo,dateCop:cop});
        }
       
           var elementos = { 
            "ur": {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text',val:ur,readonly: true}},
            "ue": {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-center',type: 'text',val:ue,readonly: true}},
            
            "partida":   {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control partida sug partidaSug text-center',val:data[ad].partida}},
            "clave":   {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control clave sug claveSug text-center',val:data[ad].clave}},
            "descripArt":   {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control descripArt sug descripArtSug text-center',val:''}}, // doesnt work put getIndexforDetail(data[ad].clave,data[ad].tipo) in val in this time
            
            "um":       {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-center',type: 'text',val:'',readonly: true}},
            "cantidad": {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-center',type: 'text',val:data[ad].cantidad,onkeypress: 'return soloNumeros(event)'}},
            "ultimoCosto": {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-center',type: 'text',val:data[ad].ultimoCosto,onkeypress: 'return soloNumeros(event)'}},
            "importe": {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-center',type: 'text',val:(data[ad].ultimoCosto*data[ad].cantidad),onkeypress: 'return soloNumeros(event)'}},
            
            };
            $datosMeses= months[ad];
            for(ya in $datosMeses){
                
                 elementos[ya]=( {tag: 'input',opts: {class: 'form-control ',type: 'text',val:($datosMeses[ya]),onkeypress: 'return soloNumeros(event)'}});
            
            }
            //  elementos.selEscondida =({ tag: 'select',opts: { class: 'selEsc hide' }});
            elementos['zorigin']=({tag: 'input', opts: {type:'hidden',val:data[ad].origin}});
            if(data[ad].tipo=='B'){
                 fnAgregarFila(elementos,1,"#tablaBienes");
            }else{
                fnAgregarFila(elementos,1,"#tablaServicios",false);
            }
            
        }
         setTimeout(function() {
            auxDesp={};
            auxUm={};
        for(ad in window.claves){
           
            index='';
            desp='';
            um='';
            if(window.claves[ad].tipo=="B"){
                index=getIndexforDetail(claves[ad].clave,window.allClavesBien);
                desp=window.allDescriBien[index[0]];
                um=window.allUnitsBien[index[0]];
            }else{
                index=getIndexforDetail(claves[ad].clave,window.allClavesSer);
                desp=window.allDescriSer[index[0]];
                um=window.allUnitsSer[index[0]];
            }
           
            aux="#descripArt"+window.claves[ad].fila
            auxDesp[aux]=({des:desp});

            aux1="#um"+window.claves[ad].fila
            auxUm[aux1]=({um:um});
               
        }
        
        for(ad in auxDesp){
            $(ad).val(auxDesp[ad].des);
           // console.log(ad," ",auxDesp[ad].des);
        }
        for(ya in auxUm){
            $(ya).val(auxUm[ya].um);
           // console.log(ya," ",auxUm[ya].um);
        }

        // set rows´s consume and rpojected
        var beforeColums=10;
        var totalMonths=12;// 
        for(ad=(parseInt(cops[0].dateCop)-1);ad<(totalMonths-1);ad++){
                //$('#'+ad+' >td:eq('+(x+ beforeColums)+')' ).find('input').val(consumeAssetsProductsTotal[ad].avgNextMonths);
            for(ya in cops){
                x=ad;
                $('#fila'+cops[ya].fila+' >td:eq('+(x+ beforeColums)+')' ).attr('style', 'background-color:#778899; min-width: 100px;max-width:100px; vertical-align:middle; text-align:center;');
                //$('#fila'+cops[ya].fila+' >td:eq('+((beforeColums+parseInt(cops[0].dateCop))-(1+x))+')').find('input').attr("disabled",'true');;
                }
        }
          // disable inputs´s consume
        for(adY in window.cops){
                for(ad1=0;ad1<(beforeColums+parseInt(cops[0].dateCop))-1;ad1++){
                
               $('#fila'+cops[adY].fila+' >td:eq('+(ad1)+')').find('input').attr("disabled",'true');;
            }
        }
        setFolio(window.numberScene);
        $("#btnGuardar").removeAttr('disabled');
        }, 1000);
        
            break;
        
    }
}
/**
 * [fnFormatoSelect description]
 * @param  {[type]} fila    [description]
 * @param  {[type]} options [description]
 * @param  {String} clase   [description]
 * @return {[type]}         [description]
 */
function fnFormatoSelect(fila, options,clase='') {
    $(fila).multiselect({
        enableFiltering: true,
        filterBehavior: 'text',
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        numberDisplayed: 1,
        includeSelectAllOption: true
    });
   
    $(fila).multiselect('dataprovider', options);

    $('.multiselect-container').css({
        'max-height': "220px"
    });
    $('.multiselect-container').css({
        'overflow-y': "scroll"
    });
    if(clase!=''){

      $(clase).css({'display':'none'});
    }
   
}

/**
 * [cambioSel description]
 * @return {[type]} [description]
 */
function cambioSel() {

    var selects = ["servicioPar","partida", "clave", "descripArt"];
    var id = $(this).attr('id');
    //console.log(id);
    n = id;

    var sel = '';
    $.each(selects, function(i, v) {

        rgxp = new RegExp(v, "g");
        c = id.match(rgxp);
        //console.log(c);
        if (c != null) {
            sel = c[0];
        }

    });
    //console.log(sel);
    n = n.replace(sel, "");
    proceso = 'cambio' + sel;
    var seleccionado = $(this).val();

    if (seleccionado != -1) {
        muestraCargandoGeneral();

        setTimeout(function() {

            switch (sel) {

                case 'partida':
                 

                    $.post(modelo, {
                                 getData: 'cambioPartida',
                                  datos:seleccionado
                        }, function(data) {

                            data= JSON.parse(data);    
                            fnFormatoSelect("#clave" + n, data.clave,'.partida');
                            fnFormatoSelect("#descripArt" + n, data.des,'.descripArt');
                            datos = fnCrearSelectNormal(data.um);
                            $('#selEscondida' + n).empty();
                            $('#selEscondida' + n).append(datos);
                            $('#selEscondida' + n).prev().css("class", "hide");

                        });

                    break;
                    case 'servicioPar':
                 

                    $.post(modelo, {
                                 getData: 'cambioPartidaSer',
                                  datos:seleccionado
                        }, function(data) {

                            data= JSON.parse(data);    
                            fnFormatoSelect("#clave" + n, data.clave,'.partida');
                            fnFormatoSelect("#descripArt" + n, data.des,'.descripArt');
                             datos = fnCrearSelectNormal(data.um);
                            $('#selEscondida' + n).empty();
                            $('#selEscondida' + n).append(datos);
                            $('#selEscondida' + n).prev().css("class", "hide");

                        });


                    break;

                case 'clave':
                    index = -1;
                    $('#clave' + n + ' option').each(function(i, v) {
                        if (this.selected) index = i;

                    });
                    existe = fnVerificarNoexista(seleccionado, sel);
                    if (existe) {
                        muestraModalGeneral(4, titulo, 'Ya se seleccionó la clave ' + seleccionado);
                        //$("#fila"+n).remove();

                    } else {

                        $('#descripArt' + n).selectpicker('val', seleccionado);
                        $('#descripArt' + n).multiselect('refresh');
                        $('.descripArt').css({'display':'none'});
                        $('#selEscondida' + n).val(seleccionado);
                        $('#um' + n).val($('#selEscondida' + n + ' option:selected').text());
                    }
                    break;

                case 'descripArt':
                    existe = fnVerificarNoexista(seleccionado, sel);
                    if (existe) {
                        muestraModalGeneral(4, titulo, 'Ya se seleccionó el artículo ' + seleccionado);
                        $("#fila" + n).remove();

                    } else {
                        $('#clave' + n).selectpicker('val', seleccionado);
                        $('#clave' + n).multiselect('refresh');
                        $('.clave').hide();
                        $('#selEscondida' + n).val(seleccionado);
                        $('#um' + n).val($('#selEscondida' + n + ' option:selected').text());

                    }


                    break;

            }



            retorno = [];
            ocultaCargandoGeneral();

        }, 10);
    } else {
        ocultaCargandoGeneral();
        setTimeout(function() {
            muestraModalGeneral(4, titulo, 'No puede dejar sin selección el renglon');
        }, 50);
    }

}

function fnVerificarNoexista(val, donde) {
    // var valor = '',
    //     count = 0,
    //     flag = false;
    // $("#" + nombreTabla + " tbody tr").each(function() {
    //     fila = $(this).attr('id');
    //     fila = fila.replace("fila", "");

    //     valor = $("#" + donde + fila).val();
    //     if (valor == val) {
    //         count++;
    //     }

    // });
    // if (count > 1) {
    //     flag = true;
    // }

    // return flag;
}
/**
 * [fnVerificaCampos description]
 * @param  {[type]} nombreTabla [description]
 * @return {[type]}             [description]
 */
function fnVerificaCampos(nombreTabla) {
    var valor = '',
        count = 0,
        countMeses=0,
        flag = false,
        msg = '';
    $("#" + nombreTabla + " tbody tr").each(function() {
        fila = $(this).attr('id');
        fila = fila.replace("fila", "");


        partidaVal = $("#partida" + fila).val();
        claveVal = $("#clave" + fila).val();
        desVal = $("#descripArt" + fila).val();

        if ((partidaVal == -1) || (claveVal == -1) || (desVal == -1)) {
            count++;
            //En  el renglón " + $("#numero" + fila).html() + 
            msg += "<br><i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i>  No tiene Partida y/o artículo seleccionado.";

        }

      

    });
    if (count >= 1) {
        flag = true;

        muestraModalGeneral(4, titulo, msg);
    } else {
        flag = false;
    }

    return flag;
}
/**
 * [fnCrearSelectNormal description]
 * @param  {[type]} datos [description]
 * @return {[type]}       [description]
 */
function fnCrearSelectNormal(datos) {

    var lista = '';
    $.each(datos, function(i, v) {
        lista += '<option  value="' + v.value + '">' + v.texto + '</option>';
    });
    return lista;
}
$(document).on('click', '.filaQuitar', function() {

    var btn = $(this).attr('id');
    var id = btn.replace("btnEliminar", "fila");
    $("#" + id).remove();
    //fnActualizarRenglon(nombreTabla);
});
$('#home1').click(function() {
        window.open("catalogoPaaas.php", "_self");
    });
/**
 * [getDatos description]
 * @param  {[type]} tablaSel  [description]
 * @param  {[type]} elementos [description]
 * @return {[type]}           [description]
 */
function getDatos(tablaSel, elementos,tipoPartida='B') {
    datosSend = new Array();

    var filas = {};
    var renglones = [];
    var valor;

    $("#" + tablaSel + " tbody tr").each(function() {
        fila = $(this).attr('id');
        fila = fila.replace("fila", "");
        $.each(elementos, function(index, v) {

            //tipo=$("#" + v + fila ).attr('type');

            // if (tipo == "undefined" ||(tipo== null)) {
            //     valor = $("#" + v + fila + " option:selected").text();
            // } else {
                //valor=    $("#"+fila).find("#"+v).val();
                valor = $("#" + v + fila).val();
                //filas[v]=valor; ////assoativo
            //}
            if((valor=='') ||(valor==null) ){
                valor=0;
            }
            renglones.push(valor);
            
        });
     
        renglones.push(tipoPartida);
        datosSend.push(renglones);
        //filas=[];//assoativo
        renglones = [];
    });
  
        return datosSend;

}
/**
 * [fnProjected this function handled view projecteds cells in consume to date in Paas]
 * @return {[type]} [not return]
 */
function fnProjected(){
    var beforeColums=10;
    var totalMonths=12;// 
  
    // var beginDate= $("#dateDesde").val();
    // beginDate=  beginDate.split("-");
    // beginDate=beginDate[1];

    var endDate=$("#dateHasta").val();
    endDate= endDate.split("-");
    endDate= endDate[1];
    endDate=parseInt(endDate)+1; // plus  1 to not take actual month
   

    //diff=totalMonths- parseInt(endDate);  //parseInt(endDate)- parseInt(beginDate);
    //   for(ad=beforeColums;ad<=totalMonths;ad++){

    //     $('#tablaBienes tr > td:nth-child('+(ad)+'), #tablaBienes tr > th:nth-child('+(ad)+')').removeAttr( 'style' );
       
    // }
    
    rowsAssets = $('#tablaBienes >tbody >tr').length;
    rowsServices = $('#tablaServicios >tbody >tr').length;

    for(ad=endDate;ad<=totalMonths;ad++){
       $('#tablaBienes tr > td:nth-child('+(ad+ beforeColums)+'), #tablaBienes tr > th:nth-child('+(ad+ beforeColums)+')').attr('style', 'background-color:#778899; min-width: 100px;max-width:100px; vertical-align:middle; text-align:center;');
       $('#tablaBienes tr > td:nth-child('+(ad+ beforeColums)+'), #tablaBienes tr > th:nth-child('+(ad+ beforeColums)+')').find('input').removeAttr('readonly'); // disable enable readonly
       
       $('#tablaServicios tr > td:nth-child('+(ad+ beforeColums)+'), #tablaServicios tr > th:nth-child('+(ad+ beforeColums)+')').attr('style', 'background-color:#778899; min-width: 100px;max-width:100px; vertical-align:middle; text-align:center;');
       $('#tablaServicios tr > td:nth-child('+(ad+ beforeColums)+'), #tablaServicios tr > th:nth-child('+(ad+ beforeColums)+')').find('input').removeAttr('readonly');
    }
    // put  values  average Assets
    
    if (rowsAssets>0){
        
        for (ad in consumeAssetsProductsTotal){
           
            for(ya=(endDate-2);ya<(totalMonths-1);ya++){////ya =minus 2 because eq begin in zero and no take the actual month  //totalMonths minus 1 for no take the td origin
                //console.log(endDate);
                x=ya;
            $('#'+ad+' >td:eq('+(x+ beforeColums)+')' ).find('input').val(consumeAssetsProductsTotal[ad].avgNextMonths);
            //console.log('#'+ad+' >td:eq('+(x+ beforeColums)+')' );
            }

        }
    }
    // add  average into  services
    if(rowsServices>0){
         
         for (ad in consumeServicesTotal){
            for(ya=(endDate-2);ya<(totalMonths-1);ya++){
                 x=ya;
            $('#'+ad+' >td:eq('+(x+ beforeColums)+')' ).find('input').val(consumeServicesTotal[ad].avgNextMonths);
            }
        }
    }
   
    consumeAssetsProductsTotal={}; // empty object
    consumeServicesTotal={};
  
    if ((rowsAssets>0) || (rowsServices>0)){
       muestraModalGeneral(4,titulo,"Proyección lista."); 
         
     }
    $("#btnGuardar").removeAttr("disabled");
    $("#addBien").removeAttr("disabled");
    $("#addServicios").removeAttr("disabled");
   // }else if((rowsAssets>0) &&(rowsServices<0)){
   //   muestraModalGeneral(4,titulo,"Proyección lista solo para  bienes."); 
   //    console.log("bienes");
   // }else if((rowsAssets<0) &&(rowsServices>0)){
   //   muestraModalGeneral(4,titulo,"Proyección lista solo para  memorias de cálculo."); 
   //    console.log("serv");
   // }else if((rowsAssets<0) &&(rowsServices<0)){
   //       muestraModalGeneral(4,titulo,"No hay proyecciones."); 
   //        console.log("sin");
   // }
    
}
/**
 * document  ready  from jquery
 * @param  {[type]} ) {               fnIniciarVariables();    fnsetComponentes();                                        $("#filtrar").click(function(event) {               event.preventDefault();        if(($("#selectUnidadNegocio").val()! [description]
 * @return {[type]}   [description]
 */
$(function() {

    fnIniciarVariables();
    fnsetComponentes();
    
    //  $('.filaQuitar').on('click', function() {

    // var btn = $(this).attr('id');
    // var id = btn.replace("btnEliminar", "fila");
    // $("#" + id).remove();
    // console.log("del");
    // //fnActualizarRenglon(nombreTabla);
    // //btnProjected
    // });
    
    /**
     * [description]
     * @param  {[type]} event) {                          event.preventDefault();        if(($("#selectUnidadNegocio").val()! [description]
     * @return {[type]}        [description]
     */
    $("#filtrar").click(function(event) {
       
        event.preventDefault();
        if(($("#selectUnidadNegocio").val()!=-1) && ($("#selectUnidadNegocio").val()!=null)){
            if(($("#dateHasta").val()!=null) &&($("#dateHasta").val()!='')){
                //selectUnidadEjecutora
                if(($("#selectUnidadEjecutora").val()!=null) &&($("#selectUnidadEjecutora").val()!=-1)){
                     $("#btnGuardar").attr("disabled",'true');
                    $("#addBien").attr("disabled",'true');
                    $("#addServicios").attr("disabled",'true');
                    $("#btnProjected").attr("disabled", 'true');

                    $("#tablaBienes").find('tbody').empty();
                    $("#tablaServicios").find('tbody').empty();
                    $("#selectUnidadNegocio2").val($("#selectUnidadNegocio").val());
                    $("#dateDesde2").val($("#dateDesde").val());
                    $("#selectRazonSocial2").val($("#selectRazonSocial").val());
                    fnenvioDatos('formulario','filtrado',0,'criteriosFrm', 'filtrado');
                }else{ // end validated  UnidadEjecutora
                     muestraModalGeneral(4,titulo,"Seleccione una UE");
                }
               
            }else{// end validated dateEnd 
            muestraModalGeneral(4,titulo,"Seleccione fecha hasta");
            }
        }else{ // end validated UnidadNegocio
            muestraModalGeneral(4,titulo,"Seleccione una UR");
        }
       
    });
    /**
     * [description]
     * @param  {[type]} event){                                                                                                            event.preventDefault();                                                                                                                 if(($("#selectUnidadNegocio").val()! [description]
     * @param  {[type]} "ue":          {tag:        'input',opts:  {class: 'form-control           text-center',type:   'text',val:''}} [description]
     * @param  {[type]} "partida":                                  {tag:   'select',opts: {class: 'form-control        partida           text-center',change:    cambioSel}} [description]
     * @param  {[type]} "clave":       {tag:        'select',opts: {class: 'form-control  clave    text-center',change: cambioSel}}     [description]
     * @param  {[type]} "descripArt":                                                                                     {tag:            'select',opts:          {class:      'form-control descripArt text-center',change: cambioSel}}           [description]
     * @param  {[type]} "um":                                                                                             {tag:            'input',opts:           {class:      'form-control            text-center',type:   'text',val:'',readonly: true}}      [description]
     * @param  {[type]} "cantidad":    {tag:        'input',opts:  {class: 'form-control           text-center',type:   'text',val:''}} [description]
     * @param  {[type]} "ultimoCosto": {tag:        'input',opts:  {class: 'form-control           text-center',type:   'text',val:''}} [description]
     * @param  {[type]} };                                                                                                                                                                                for(ad               in                      $meses        [description]
     * @return {[type]}                [description]
     */
    $("#addBien").click(function(event){
        event.preventDefault();
        if(($("#selectUnidadNegocio").val()!=-1) && ($("#selectUnidadNegocio").val()!=null)){
        var flag=false;

        filas = $('#tablaBienes' + ' >tbody >tr').length;
        if(filas>0){
             flag=fnVerificaCampos("tablaBienes");
        }
       
        if(!flag){

            var elementos = { 
            "ur": {tag: 'input',opts: {style:'font-size:12px !important;', class: 'form-control  text-center',type: 'text',val:$("#selectUnidadNegocio").val(),readonly: true}},
            "ue": {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-center',type: 'text',val:$("#selectUnidadEjecutora").val(),readonly: true}},
            //"partida":   {tag: 'select',opts: {style:'font-size:12px !important;',class: 'form-control partida text-center',change: cambioSelVl}},
            //"clave": {tag: 'select',opts: {style:'font-size:12px !important;',class: 'form-control clave text-center',change: cambioSelVl}},
            //"descripArt":       {tag: 'select',opts: {style:'font-size:12px !important;',class: 'form-control descripArt text-center',change: cambioSelVl}},
            
            "partida":   {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control partida sug partidaSug text-center'}},
            "clave":   {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control clave sug claveSug text-center',readonly: true}},
            "descripArt":   {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control descripArt sug descripArtSug text-center',readonly: true}},
            
            "um":       {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-center',type: 'text',val:'',readonly: true}},
            "cantidad": {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-center',type: 'text',val:'',onkeypress: 'return soloNumeros(event)'}},
            "ultimoCosto": {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-center',type: 'text',val:'',onkeypress: 'return soloNumeros(event)'}},
            "importe": {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-center',type: 'text',val:'',onkeypress: 'return soloNumeros(event)'}},
          
            };
            for(ad in $meses){
                mes="z"+ad;
                 elementos[mes]=( {tag: 'input',opts: {class: 'form-control ',type: 'text',val:'',onkeypress: 'return soloNumeros(event)'}});
            
            }
             elementos.selEscondida =({ tag: 'select',opts: { class: 'selEsc hide' }});
             elementos['zorigin']= {tag: 'input', opts: {type:'hidden',val:'new'}};
            fnAgregarFila(elementos,1,"#tablaBienes");
        }
        }else{
            muestraModalGeneral(4,titulo,"Escoja una UR");
        }

    });
        /**
         * [description]
         * @param  {[type]} event){                                                                                                            event.preventDefault();                                                                                                                 if(($("#selectUnidadNegocio").val()! [description]
         * @param  {[type]} "ue":          {tag:        'input',opts:  {class: 'form-control           text-center',type:   'text',val:''}} [description]
         * @param  {[type]} "servicioPar":                              {tag:   'select',opts: {class: 'form-control        servicioPar       text-center',change:    cambioSel}} [description]
         * @param  {[type]} "clave":       {tag:        'select',opts: {class: 'form-control  clave    text-center',change: cambioSel}}     [description]
         * @param  {[type]} "descripArt":                                                                                     {tag:            'select',opts:          {class:      'form-control descripArt text-center',change: cambioSel}}           [description]
         * @param  {[type]} "um":                                                                                             {tag:            'input',opts:           {class:      'form-control            text-center',type:   'text',val:'',readonly: true}}      [description]
         * @param  {[type]} "cantidad":    {tag:        'input',opts:  {class: 'form-control           text-center',type:   'text',val:''}} [description]
         * @param  {[type]} "ultimoCosto": {tag:        'input',opts:  {class: 'form-control           text-center',type:   'text',val:''}} [description]
         * @param  {[type]} };                                                                                                                                                                                for(ad               in                      $meses        [description]
         * @return {[type]}                [description]
         */
       $("#addServicios").click(function(event){
        event.preventDefault();
        if(($("#selectUnidadNegocio").val()!=-1) && ($("#selectUnidadNegocio").val()!=null)){
        var flag=false;

        filas = $('#tablaServicios' + ' >tbody >tr').length;
        if(filas>0){
             flag=fnVerificaCampos("tablaServicios");
        }
       
        if(!flag){
       
            var elementos = { 
            "ur": {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-center',type: 'text',val:$("#selectUnidadNegocio").val(),readonly: true}},
            "ue": {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-center',type: 'text',val:$("#selectUnidadEjecutora").val(),readonly: true}},
            
            // "servicioPar":   {tag: 'select',opts: {style:'font-size:12px !important;',class: 'form-control servicioPar text-center'}},
            // "clave": {tag: 'select',opts: {style:'font-size:12px !important;',class: 'form-control clave text-center'}},
            // "descripArt":       {tag: 'select',opts: {style:'font-size:12px !important;',class: 'form-control descripArt text-center'}},
            
            "partida":   {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control partida sug partidaSug text-center'}},
            "clave":   {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control clave sug claveSug text-center',readonly: true}},
            "descripArt":   {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control descripArt sug descripArtSug text-center',readonly: true}},
            
            "um":       {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-center',type: 'text',val:'',readonly: true}},
            "cantidad": {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-center',type: 'text',val:'',onkeypress: 'return soloNumeros(event)'}},
            "ultimoCosto": {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-center',type: 'text',val:'',onkeypress: 'return soloNumeros(event)'}},
            "importe": {tag: 'input',opts: {style:'font-size:12px !important;',class: 'form-control  text-center',type: 'text',val:'',onkeypress: 'return soloNumeros(event)'}},
            
            
            };
            for(ad in $meses){
                mes="z"+ad;
                 elementos[mes]=( {tag: 'input',opts: {class: 'form-control ',type: 'text',val:'',onkeypress: 'return soloNumeros(event)'}});
            
            }
            elementos.selEscondida =({ tag: 'select',opts: { class: 'selEsc hide' }});
            elementos['zorigin']= {tag: 'input', opts: {type:'hidden',val:'new'}};
            fnAgregarFila(elementos,1,"#tablaServicios",false);
        }
         }else{
            muestraModalGeneral(4,titulo,"Escoja una UR");
        }
    });
       /**
        * [description]
        * @param  {[type]} ){                              rowCount [description]
        * @return {[type]}     [description]
        */
       $("#btnGuardar").click(function(){
         
        rowCount = $('#tablaBienes >tbody >tr').length;
        rowCount1 = $('#tablaServicios >tbody >tr').length;
        // if(rowCount>0 &&(rowCount1>0)){
         
        elementos = ["partida", "clave", "cantidad","ultimoCosto","zene","zfeb","zmar","zabr","zmay","zjun","zjul","zago","zsep","zoct","znov","zdic","zorigin"];
        aux=[];

        aux=getDatos("tablaBienes", elementos);

        //elementos1 = ["partida", "clave", "cantidad","ultimoCosto","zene","zfeb","zmar","zabr","zmay","zjun","zjul","zago","zsep","zoct","znov","zdic"];
        aux1=[];

        aux1=getDatos("tablaServicios", elementos,'D');
       
        extra = {};
        extra.ur=$("#selectUnidadNegocio2").val();
        extra.ue=$("#selectUnidadEjecutora").val();
        extra.dateDesde=$("#dateDesde2").val();
        extra.dateHasta=$("#dateHasta").val();
        //extra.programa=$("#selectProgramaPresupuestario").val();
        extra.dependencia=$("#selectRazonSocial2").val();
        aux1.push(extra);
         
        x=[];
        x= aux.concat(aux1);
         
         fnenvioDatos("GuardarDatos","GuardarDatos",x);
        // }else{
        //     muestraModalGeneral(4,titulo,"Es necesario agregar un renglón");
        // }
        
       });
       /**
        * [description]
        * @param  {[type]} e){                       e.stopPropagation();          e.preventDefault();          fnProjected();          $("#btnProjected").attr("disabled", 'true');                 } [description]
        * @return {[type]}      [description]
        */
       $("#btnProjected").click(function(e){
          e.stopPropagation();
          e.preventDefault();
          rowsAssets = $('#tablaBienes >tbody >tr').length;
          rowsServices = $('#tablaServicios >tbody >tr').length;
          if((rowsAssets>0) ||(rowsServices>0)){
               fnProjected();
          }else{

           muestraModalGeneral(4,titulo,"No existe consumos ni en bienes o servicios no se puede hacer la proyección."); 
          }
       
          $("#btnProjected").attr("disabled", 'true');
          
       });
   
   

   if(window.seeDetail==true){
        data=[];
        data.push(window.numberScene);
        fnenvioDatos("seeDetail","managerDetails",data);

     }


});

 
 /// ALL CODE  FOR LIST´S for autocompleted

$(document).on('click','.sug',function(){
    id=$(this).attr('id');
    
    //id= id.replace("partida","");
    //console.log("#sug-"+id);
    
    if($("#beforeSug"+id).length==0){
       $(this).after('<div style="z-index:auto" id="beforeSug'+id+'"> <div  id="sug-'+id+'" style="position:absolute; z-index:999; display:block; min-width: 100px;max-width:100px; font-size: 12px; "></div> </div>'); 
    
    }
        
   
});

function fnSelectData(valor='',id,div) {

  $(div).hide();
  $(div).empty();
    
  $(id).val(""+valor);
  $(id).val(""+valor);
  idAux=id.replace("#partida","");
  $("#descripArt"+idAux).removeAttr('readonly');
  $("#clave"+idAux).removeAttr('readonly');

  $("#descripArt"+idAux).val("");
  $("#clave"+idAux).val("");
  
}
function fnSelectDataCl(valor='',id,div,index,from) {

  $(div).hide();
  $(div).empty();
    
  $(id).val(""+valor);
  $(id).val(""+valor);
  $(id).val();
  idAux= id.replace("#clave","");
  //console.log("#descripArt"+idAux," " ,allDescriBien[index]);
  //$("#descripArt"+idAux).val(""); // ADD  FOR  USER  SEE CHANGE´s VISUAL
  
 
  $("#descripArt"+idAux).val( ((from=='assets')?window.allDescriBien[index]:window.allDescriSer[index])); //allDescriBien[index]

  $("#sug-descripArt"+idAux).hide();
  $(div).empty();

  $("#um"+idAux).val(((from=='assets')?window.allUnitsBien[index]:window.allUnitsSer[index]));//allUnitsBien[index]
  
}

function fnSelectDataDes(valor='',id,div,index,from) {

  $(div).hide();
  $(div).empty();
    
  $(id).val(""+valor);
  $(id).val(""+valor);
  $(id).val();
  idAux= id.replace("#descripArt","");

  $("#clave"+idAux).val(((from=='assets')?window.allClavesBien[index]:window.allClavesSer[index]) ); //allClavesBien
  $("#sug-clave"+idAux).hide();
  $("#um"+idAux).val(((from=='assets')?window.allUnitsBien[index]:window.allUnitsSer[index])); // allUnitsBien[index]
  
}
$(document).on('click',".partidaSug",function(){
//     // var attr = $(this).attr('autocomplete"');
    
//     // //console.log("click partida disable ATTR ",attr);
//     // if (typeof attr == typeof undefined || attr == false) {
//     //        $("#"+id).attr('autocomplete','off'); 
//     // }
//     //$("#"+id+" input").trigger('keyup');
    //if($(this).val()==""){
     id=$(this).attr('id');
     $("#"+id).keyup();
    //}

    
 });
$(document).on('keyup','.partidaSug',function(){
        
        id=$(this).attr('id');
        id1="#"+id;
        //id= id.replace("partida","");
        //id2="#hide"+id;
        id ="#sug-"+id;
        from=$(this).attr('data-ad');
        //if($(this).val()!=''){
            var buscar = $(this).val();//($(this).val()=='')? $(this).val(' ') :$(this).val();  
            var retorno="<ul id='articulos-lista-consolida'>";
            var buscarCoicidencia = new RegExp(buscar , "i");
             
            var arr = jQuery.map(((from=='assets')?window.partidas:window.partidasSer), function (value,index) {
                    
                return value.match(buscarCoicidencia) ? index : null;
            });
                
                for (a=0; a<arr.length;a++){
                  val=arr[a];
                  
                  retorno+="<li style='with:100%' onClick='fnSelectData(\""+((from=='assets')?window.partidas[val]:window.partidasSer[val])+"\",\""+id1+"\",\""+id+"\")'><a >  "+((from=='assets')?window.partidas[val]:window.partidasSer[val])+"  </a></li>";
                    
                }
            retorno+="</ul>";
            $(id).show();
            $(id).empty();
            $(id).append(retorno);
                
        //}
   });

$(document).on('click',".claveSug",function(){
    //if($(this).val()==""){
     id=$(this).attr('id');
     $("#"+id).keyup();
  //}
 });
$(document).on('keyup','.claveSug',function(){
        
        id=$(this).attr('id');
        id1="#"+id;
        idAux= id.replace("clave","");
        from=$(this).attr('data-ad');
        //id2="#hide"+id;
        valPartida=$("#partida"+idAux).val();
        //console.log("partida para clave",valPartida);
        id ="#sug-"+id;
        retornoDesBien='';
        //if($(this).val()!=''){
            var buscar = $(this).val(); 
            var retorno="<ul id='articulos-lista-consolida'>";
            var buscarCoicidencia = new RegExp(buscar , "i");
             //((from=='assets')?window.allClavesBien:window.allClavesSer)
            var arr = jQuery.map(((from=='assets')?window.allClavesBien:window.allClavesSer), function (value,index) {
                    
                return value.match(buscarCoicidencia) ? index : null;
            });
                
                for (a=0; a<arr.length;a++){
                  val=arr[a]; // get index
                  if(valPartida==((from=='assets')?window.allPartidasBien[val]:window.allPartidasSer[val]) ){ 
                  retorno+="<li style='with:100%' onClick='fnSelectDataCl(\""+((from=='assets')?window.allClavesBien[val]:window.allClavesSer[val])+"\",\""+id1+"\",\""+id+"\",\""+val+"\",\""+from+"\")'><a >  "+((from=='assets')?window.allClavesBien[val]:window.allClavesSer[val])+"  </a></li>";
                  //retornoDesBien+="<li style='with:100%' onClick='fnSelectData(\""+window.allDescriBien[val]+"\",\""+("#descripArt"+idAux)+"\",\""+("#sug-descripArt"+idAux)+"\")'><a >  "+window.allDescriBien[val]+"  </a></li>";
                    }
                }
            retorno+="</ul>";
            $(id).show();
            $(id).empty();
            $(id).append(retorno);
                
        //}
   });


$(document).on('click',".descripArtSug",function(){
    //if($(this).val()==""){
     id=$(this).attr('id');
     $("#"+id).keyup();
   // }
 });
$(document).on('keyup','.descripArtSug',function(){
        
        id=$(this).attr('id');
        id1="#"+id;
        idAux= id.replace("descripArt","");
        //id2="#hide"+id;
        valPartida=$("#partida"+idAux).val();
       // console.log("partida para descripArt",idAux," ",valPartida);
        id ="#sug-"+id;
        from=$(this).attr('data-ad');
        //if($(this).val()!=''){
           
            var buscar =$(this).val(); //($(this).val()=='')? $(this).val(' ') :$(this).val(); 
            var retorno="<ul id='articulos-lista-consolida'>";
            var buscarCoicidencia = new RegExp(buscar , "i");
             
            var arr = jQuery.map(((from=='assets')?window.allDescriBien:window.allDescriSer), function (value,index) {
                    
                return value.match(buscarCoicidencia) ? index : null;
            });
                
                for (a=0; a<arr.length;a++){
                  val=arr[a]; // get index
                  if(valPartida==((from=='assets')?window.allPartidasBien[val]:window.allPartidasSer[val])){
                  retorno+="<li style='with:100%' onClick='fnSelectDataDes(\""+((from=='assets')?window.allDescriBien[val]:window.allDescriSer[val])+"\",\""+id1+"\",\""+id+"\",\""+val+"\",\""+from+"\")'><a >  "+((from=='assets')?window.allDescriBien[val]:window.allDescriSer[val])+"  </a></li>";
                    }
                }
            retorno+="</ul>";
           
            $(id).show();
            $(id).empty();
            $(id).append(retorno);
                
        //}
   });


