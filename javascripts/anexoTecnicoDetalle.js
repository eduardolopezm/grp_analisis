$(document).ready(function() {
    console.log('listo detalle anexo');
    // configuraciones y ejecuciones principales
    initAnexo();
    // initTable();
    // ejecucion de colocacion de folio
    if (!$.isEmptyObject(window.folio)) {
        setDataFromFolio(window.folio);
    }
    // comportamiento de todos los botones
    $(document).on('click', 'button', function(e) {
        e.preventDefault();
    });
    // comportamiento del boton de regreso al panel
    $('.regresar').on('click', function() {
        window.location.href = window.url + '/anexoTecnico.php';
    });
    // conportamiento de boton agregar de nuevo anexo
    $('#btn-add').on('click', function() {
        var params = getParams('form-add'),
            campos = ['selectRazonSocial', 'selectUnidadNegocio', 'selectUnidadEjecutora', 'antecedentes'],
            nombreCampo = {
                'selectRazonSocial': 'Dependencia',
                'selectUnidadNegocio': 'UR',
                'selectUnidadEjecutora': 'UE',
                'antecedentes': 'Referencia'
            };
        // si es actualizacionse cambian los datos a comparar contra la forma
        if (validForm(params, campos)) {
            var lineas = getRows('tbl-products'),
                camposLinea = ['partida', 'clave', 'bienServicio', 'descBienServicio', 'cantidad', 'costo', 'garantia'],
                valido = true;
            if (lineas.length != 0) {
                // se agregan los campos necesarios
                params.method = 'store';
                params.rows = [];
                params.rows = lineas;
                // se envia la solicitud de guardado
                $.post(window.url + '/modelo/anexoTecnicoDetalleModelo.php', params)
                    .then(function(res) {
                        var titulo = 'Error de Datos',
                            common = {
                                html: 'Aceptar',
                                class: 'btn btn-danger',
                                'data-dismiss': 'modal'
                            },
                            pie;
                        if (res.success) {
                            titulo = 'Operación exitosa';
                            common['data-dismiss'] = 'modela';
                            common.class = 'btn btn-success btn-green';
                            common.click = function() {
                                window.location.href = window.url + '/anexoTecnicoDetalle.php?URL=' + res.url;
                            };
                            $('#folioLabel').html(res.folio).removeClass('hidden');
                        }
                        var pie = $('<button>', common);
                        muestraModalGeneral(3, titulo, res.msg, pie);
                    });
            } else if (errrorRegistros == 0) {
                muestraModalGeneral(3, 'Error de datos', 'Es necesario que se agrege minimo un Bien / Servicio.');
            }
        } else {
            var camp = '',
                f = 0;
            $.each(params, function(index, val) {
                if (campos.indexOf(index) !== -1) {
                    if ($.isEmptyObject(val)) {
                        camp += (f == 0 ? '' : ',') + ' ' + nombreCampo[index];
                        f++;
                    }
                }
            });
            muestraModalGeneral(3, 'Error de datos', 'Es necesario colocar los datos solicitados en los siguientes campos: <br>' + camp);
        }
    });
    // comportamiento de boton actualizar
    $('#btn-update').on('click', function() {
        var params = getParams('form-add'),
            campos = ['selectRazonSocial', 'selectUnidadNegocio', 'selectUnidadEjecutora', 'antecedentes'],
            nombreCampo = {
                'selectRazonSocial': 'Dependencia',
                'selectUnidadNegocio': 'UR',
                'selectUnidadEjecutora': 'UE',
                'antecedentes': 'Referencia'
            };
        if (validForm(params, campos)) {
            var lineas = getRows('tbl-products'),
                camposLinea = ['partida', 'clave', 'bienServicio', 'descBienServicio', 'cantidad', 'costo', 'garantia'],
                valido = true;
            if (lineas.length != 0) {
                params.method = 'update';
                params.rows = [];
                params.rows = lineas;
                params.identificadores = window.identificadores || [];

                $.post(window.url + '/modelo/anexoTecnicoDetalleModelo.php', params)
                    .then(function(res) {
                        var titulo = 'Error de Datos',
                            common = {
                                html: 'Aceptar',
                                class: 'btn btn-danger',
                                'data-dismiss': 'modal'
                            };
                        if (res.success) {
                            titulo = 'Operación exitosa';
                            common['data-dismiss'] = '';
                            common.class = 'btn btn-success btn-green';
                            common.click = function() {
                                location.reload();
                            };
                        }
                        var pie = $('<button>', common);
                        muestraModalGeneral(3, titulo, res.msg, pie);
                    });
            } else if (errrorRegistros == 0) {
                muestraModalGeneral(3, 'Error de datos', 'Es necesario que se agrege minimo un Bien / Servicio.');
            }
        } else {
            var camp = '',
                f = 0;
            $.each(params, function(index, val) {
                if (campos.indexOf(index) !== -1) {
                    if ($.isEmptyObject(val)) {
                        camp += (f == 0 ? '' : ',') + ' ' + nombreCampo[index];
                        f++;
                    }
                }
            });
            muestraModalGeneral(3, 'Error de datos', 'Es necesario colocar los datos solicitados en los siguientes campos: <br>' + camp);
        }
    });
    // comportamiento del boton de agregar registro Bien/Servicio
    $('#add').on('click', addNewRow);
    // comportamiento de la eliminacion de la lunea o renglon
    $(document).on('click', '.row-delete', function() {
        var $btn = $(this),
            row, id;
        $row = $btn.parents('div[id]').eq(0);
        id = $row.attr('id');
        $row.remove();
        reordena('#tbl-products');
        if (getCountRow('#tbl-products') == 1) {
            $('.selectUnidadEjecutora').multiselect('enable');
        }
    });
});

/**
 * Funcion para la iniciacion de las variables
 * principales necesarios atrabes del programa
 */
function initAnexo() {
    var url = window.location.href.split('/');
    url.splice(url.length - 1);
    window.url = url.join('/');
    window.renglonRoot = 0;
    window.rowsRoot = [];
    window.deleteRoot = [];
    window.dpPartida = [];
    window.productos = [];
    window.commonMulti = {
        enableFiltering: true,
        filterBehavior: 'text',
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        numberDisplayed: 1,
        includeSelectAllOption: true
    };
    window.permisos = '';
    // se consultan los permisos que tiene el usuuario para poder editar los renglone
    $.post(window.url + '/modelo/anexoTecnicoModelo.php', {
            method: 'getNoPermitidos'
        })
        .then(function(res) {
            window.permisos = res;
        });

    window.clsOpt = {
        numeral: 'w3p fl vam pt15',
        partida: 'w10p fl p8',
        clave: 'w12p fl p8',
        bienServicio: 'w14p fl p8',
        descBienServicio: 'w40p fl p8',
        unidad: 'w5p fl p0 pt5',
        cantidad: 'w7p fl p0 pt5',
        costo: 'w6p fl p0 pt5',
        total: 'w7p fl p0 pt5',
        garantia: 'w6p fl p0 pt5'
    };

    // flag de error al momento de checar los registros de productos
    // 0 = no error, 1 = existe error
    window.errrorRegistros = 0;

    // agregado de estilos de scroll en los multiselect
    $('.multiselect-container').css({
        'max-height': "200px"
    });
    $('.multiselect-container').css({
        'overflow-y': "scroll"
    });
}

/**
 * Funcion para la colocacion de datos segun un folio proporcionado
 * @param {string} folio Identificador del anexo técnico
 */
function setDataFromFolio(folio) {
    // carga de folio
    $('#folioLabel').html(folio).removeClass('hidden');
    // envio de solicitud
    $.post(window.url + '/modelo/anexoTecnicoDetalleModelo.php', {
            method: 'show',
            folio: folio
        })
        .then(function(res) {
            var titulo = 'Error de Datos',
                common = {
                    html: 'Aceptar',
                    class: 'btn btn-danger',
                    'data-dismiss': 'modal',
                    click: function() {
                        window.location.href = window.url;
                    }
                },
                selects = ['selectRazonSocial', 'selectUnidadNegocio', 'selectUnidadEjecutora'],
                trigger = ['selectUnidadNegocio'];
            if (res.success) {
                var content = res.content;
                titulo = 'Operación exitosa';
                common.class = 'btn btn-success btn-green';
                common.click = '';
                // carga de cabecera
                $.each(selects, function(index, val) {
                    var $el = $('#' + val),
                        row = content['head'];
                    $el.val(row[val]);
                    $el.multiselect('refresh');
                    if (trigger.indexOf(val) !== -1) {
                        $el.trigger('change');
                    }
                    if (val == 'selectUnidadEjecutora') {
                        setTimeout(function() {
                            $el.val(row[val]);
                            $el.multiselect('refresh').multiselect('disable');
                        }, 500);
                    }
                });
                // colocación de los antecedentes
                $('#antecedentes').val(content['head']['antecedentes']); // @date: 15.05.18
                // se carga la informacion en la variabe global
                $.each(content['data'], function(index, val) {
                    window.rowsRoot.push(val);
                });
                window.renglonRoot = content['data'].length;
                window.dpPartida = content['partida'];
                window.identificadores = content['identificadores'];
                window.productos = content['productsOfPartida'];
                // se ejecuta el llamado de la renderizacion
                renderTable();
            }
            var pie = $('<button>', common);
        });
}

/**
 * funcion para lacarga inicial de los productos
 */
function initTable() {
    $('#bienServicio').multiselect(window.commonMulti);
    // consulta de los datos para los productos
    $.post(window.url + '/modelo/anexoTecnicoDetalleModelo.php', {
            method: 'getProducts'
        })
        .then(function(res) {
            if (res.success) {
                var content = res.content,
                    options = [{
                        label: 'Seleccionar',
                        title: 'Seleccionar',
                        value: ''
                    }];
                $.each(content, function(index, val) {
                    options.push({
                        label: val.desc,
                        title: val.dec,
                        value: val.id
                    });
                });
                window.productos = content;
                $('#bienServicio').multiselect('dataprovider', options);
                $('.multiselect-container').css({
                    'max-height': "200px"
                });
                $('.multiselect-container').css({
                    'overflow-y': "scroll"
                });
            } else {
                // muestraMensajeTiempo(res.msg,3,'mensaje',2000);
                muestraModalGeneral(3, 'Error de datos', res.msg);
            }
        });
}

function renderTable() {
    var $tbody = $('#tbl-products') /*.find('tbody')*/ ,
        styleTr = 'text-center w100p fl form-group',
        clsOpt = window.clsOpt,
        productos = window.productos,
        orden = {
            "numeral": {
                tag: 'span',
                opts: {}
            },
            "partida": {
                tag: 'select',
                opts: {
                    class: 'form-control ',
                    change: changePartida,
                    required: true,
                    size: 1
                }
            },
            "clave": {
                tag: 'select',
                opts: {
                    class: 'form-control ',
                    change: evalProd
                }
            },
            "bienServicio": {
                tag: 'select',
                opts: {
                    class: 'form-control ',
                    change: evalProd
                }
            },
            "descBienServicio": {
                tag: 'textarea',
                opts: {
                    class: 'form-control',
                    rows: 1,
                    placeholder: 'Descripcion del bien o Servicio',
                    onpaste: 'return false',
                    css: {
                        'resize': 'vertical'
                    }
                }
            },
            "unidad": {
                tag: 'input',
                opts: {
                    class: 'form-control',
                    type: 'text',
                    val: 'Unidad',
                    readonly: true
                }
            },
            "cantidad": {
                tag: 'input',
                opts: {
                    type: 'number',
                    onpaste: 'return false',
                    class: 'form-control',
                    onkeypress: 'return soloNumeros(event)',
                    min: 0,
                    max: 1000000000000000000
                }
            },
            "garantia": {
                tag: 'input',
                opts: {
                    type: 'number',
                    class: 'form-control',
                    onkeypress: 'return soloNumeros(event)',
                    onpaste: 'return false',
                    min: 1,
                    max: 1000000000000000000,
                    val: 1
                }
            }
        };
    $.each(window.rowsRoot, function(index, oldrow) {
        var cols = [],
            selects = [];
        // argegado de boton eliminar
        var elimi = '';
        if ($.isEmptyObject(estatusAnexo) || (permisoAutorizador == 1 && estatusAnexo == 6)) {
            var complemento = generateItem('span', {
                class: 'btn btn-danger btn-xs glyphicon glyphicon-remove row-delete',
                title: 'Eliminar'
            });
            elimi = generateItem('div', {
                class: 'w3p fl vam pt15'
            }, complemento);
        }
        cols.push(elimi);
        // iteracion entro las columnas a agregar
        $.each(orden, function(ind, val) {
            var $cont, tag = val.tag,
                opts = val.opts;
            // carga de id y nombre
            opts.id = ind;
            opts.name = ind;
            if (tag == 'select') {
                var rhtml = (ind == 'bienServicio' ? oldrow['bienServicioName'] : oldrow[ind]);
                selects.push(ind);
                // llamada de funcion de genración de opción
                if (ind == 'partida') {
                    $option = generaOptiosSelect(window.dpPartida, oldrow[ind]);
                } else if (ind == 'clave') {
                    $option = generaOptiosSelect(window.productos[oldrow['partida']], oldrow[ind], true);
                } else if (ind == 'bienServicio') {
                    $option = generaOptiosSelect(window.productos[oldrow['partida']], oldrow[ind]);
                } else {
                    $option = generateItem('option', {
                        val: oldrow[ind],
                        html: rhtml
                    });
                }
                $cont = generateItem(tag, opts, $option);
            } else {
                if (ind == 'unidad') {
                    opts.html = oldrow[ind];
                }
                if (ind == 'total') {
                    opts.html = '$' + oldrow[ind];
                }
                opts.val = oldrow[ind];
                if (ind == 'numeral') {
                    opts.html = index + 1;
                    $inputId = generateItem('input', {
                        class: 'hidden',
                        name: 'id',
                        id: 'id',
                        val: oldrow[ind]
                    });
                    $cont = generateItem(tag, opts, $inputId);
                } else {
                    $cont = generateItem(tag, opts);
                }
            }
            cols.push(generateItem('div', {
                class: clsOpt[ind]
            }, $cont));
        });

        // generación de item contenedor
        $tr = generateItem('div', {
            class: styleTr,
            id: index
        }, cols);
        $tbody.append($tr);

        // accion de bloqueo
        if (!$.isEmptyObject(estatusAnexo)) { // IF => A
            if (estatusAnexo == 6 && permisoAutorizador != 0) { // IF => B
                $.each(selects, function(i, val) {
                    fnFormatoSelectGeneral('#' + index + ' #' + val);
                });
            } else { // IF => B
                // bloqueo de selects
                $.each(selects, function(i, val) {
                    $('#' + index + ' #' + val).multiselect('disable');
                });
                // bloqueo de inputs
                $('#' + index).find('input[id], textarea[id]').each(function() {
                    $(this).attr('disabled', true);
                })
                // bloqueo de campo d ereferencia
                $('#antecedentes').attr('disabled', true);
            } // IF => B
        } else { // IF => A
            $.each(selects, function(i, val) {
                fnFormatoSelectGeneral('#' + index + ' #' + val);
            });
        } // IF => A
    }); // FIN FOREACH ROWs
}

function addNewRow() {
    if ($("#selectUnidadEjecutora").val() != -1) {

        var $tbody = $('#tbl-products'),
            styleTr = ' text-center w100p fl form-group',
            renglon = window.renglonRoot,
            clsOpt = window.clsOpt,
            $delete = generateItem('div', {
                class: 'w3p fl vam pt15'
            }, generateItem('span', {
                class: 'btn btn-danger btn-xs glyphicon glyphicon-remove row-delete',
                title: 'Eliminar'
            })),
            orden = {
                "numeral": {
                    tag: 'span',
                    opts: {
                        html: getCountRow('#tbl-products')
                    }
                },
                "partida": {
                    tag: 'select',
                    opts: {
                        class: 'form-control ',
                        change: changePartida,
                        required: true,
                        size: 1
                    }
                },
                "clave": {
                    tag: 'select',
                    opts: {
                        class: 'form-control ',
                        change: evalProd
                    }
                },
                "bienServicio": {
                    tag: 'select',
                    opts: {
                        class: 'form-control ',
                        change: evalProd
                    }
                },
                "descBienServicio": {
                    tag: 'textarea',
                    opts: {
                        class: 'form-control',
                        rows: 1,
                        placeholder: 'Descripcion del bien o Servicio',
                        onpaste: 'return false',
                        css: {
                            'resize': 'vertical'
                        }
                    }
                },
                "unidad": {
                    tag: 'input',
                    opts: {
                        class: 'form-control',
                        type: 'text',
                        val: 'Unidad',
                        readonly: true
                    }
                },
                "cantidad": {
                    tag: 'input',
                    opts: {
                        type: 'number',
                        onpaste: 'return false',
                        class: 'form-control',
                        onkeypress: 'return soloNumeros(event)',
                        /*onkeyup:'calcTotal(this)',*/
                        min: 0,
                        max: 1000000000000000000
                    }
                },
                "garantia": {
                    tag: 'input',
                    opts: {
                        type: 'number',
                        class: 'form-control',
                        onkeypress: 'return soloNumeros(event)',
                        onpaste: 'return false',
                        min: 1,
                        max: 1000000000000000000,
                        val: 1
                    }
                }
            }
        cols = [], selects = [], $tbody, $tr = '';
        // argegado de boton eliminar
        cols.push($delete);
        // iteracion entro las columnas a agregar
        $.each(orden, function(index, val) {
            var $cont, tag = val.tag,
                opts = val.opts;
            opts.id = index;
            opts.name = index;
            if (tag == 'select') {
                selects.push(index);
            }
            $cont = generateItem(tag, opts);
            cols.push(generateItem('div', {
                class: clsOpt[index]
            }, $cont));
        });
        // se genera la linea
        $tr = generateItem('div', {
            class: styleTr,
            id: renglon
        }, cols);
        // se agrega a la tabla
        $tbody.append($tr);
        // renderisado de multiselect
        $.each(selects, function(index, val) {
            fnFormatoSelectGeneral('#' + renglon + ' #' + val);
        });
        // consulta de los datos iniciales de partidas
        if (renglon == 0 || window.dpPartida.length == 0) {
            $.post('modelo/anexoTecnicoDetalleModelo.php', {
                    method: 'getPartida'
                })
                .then(function(res) {
                    if (res.success) {
                        $('#' + renglon + ' #partida').multiselect('dataprovider', res.content);
                        window.dpPartida = res.content;
                    }
                });
        } else {
            $('#' + renglon + ' #partida').multiselect('dataprovider', window.dpPartida);
        }
        // se suma las lineas nuevas
        window.renglonRoot = renglon + 1;

        if (renglonRoot > 0) {
            $('.selectUnidadEjecutora').multiselect('disable');
        }
    } else {
        muestraModalGeneral(3, 'Error de datos', 'Debe seleccionar una  UE antes de agregar un bien o servicio', '');
    }
}

function changePartida() {
    var $el = $(this),
        $parent, id, partida;
    $parent = $el.parents('div[id]').eq(0);
    id = $parent.attr('id');
    partida = $el.val();
    $.post(window.url + '/modelo/anexoTecnicoDetalleModelo.php', {
            method: 'getProducts',
            partida: partida
        })
        .then(function(res) {
            if (res.success) {
                var content = res.content,
                    optionsBS = [{
                        label: 'Seleccionar',
                        title: 'Seleccionar',
                        value: ''
                    }],
                    optionsCl = [{
                        label: 'Seleccionar',
                        title: 'Seleccionar',
                        value: ''
                    }];
                $.each(content, function(index, val) {
                    optionsBS.push({
                        label: val.desc,
                        title: val.dec,
                        value: val.id
                    });
                });
                $.each(content, function(index, val) {
                    optionsCl.push({
                        label: val.id,
                        title: val.id,
                        value: val.id
                    });
                });
                window.productos[partida] = content;
                $('#' + id + ' #bienServicio').multiselect('dataprovider', optionsBS);
                $('#' + id + ' #clave').multiselect('dataprovider', optionsCl);
                $('.multiselect-container').css({
                    'max-height': "200px"
                });
                $('.multiselect-container').css({
                    'overflow-y': "scroll"
                });
                // llamada de funcion auxiliar
                applyaChange($el);
            } else {
                muestraModalGeneral(3, 'Error de datos', res.msg);
            }
        });
}

function evalProd() {
    var $el = $(this),
        alt = 'bienServicio',
        ind = 'clave',
        productos = window.productos,
        $unidad, $parent, idP, um, partida;
    alt = $el.attr('id') == ind ? alt : ind;
    $parent = $el.parents('div[id]').eq(0);
    idP = $parent.attr('id');
    $unidad = $parent.find('#unidad');
    partida = $parent.find('#partida').val();
    $alt = $('#' + idP + ' #' + alt);
    um = productos[partida][$el.val()].hasOwnProperty('um') ? productos[partida][$el.val()].um : '';
    // se comprueba que el valor no sea vacio
    if ($.isEmptyObject($el.val())) {
        $alt.multiselect('select', '').multiselect('refresh');
        return;
    }
    // se comprueba que se cuente con información en la unidad
    // en caso de no contener unidad se manda mensaje de error
    if ($.isEmptyObject(um)) {
        muestraModalGeneral(3, 'Error de Datos', 'El producto seleccionado no cuenta con una unidad.');
        $el.val('').trigger('change').multiselect('refresh');
        return;
    }
    $unidad.val(um);
    $alt.multiselect('select', $el.val()).multiselect('refresh');
    // llamada de funcion auxiliar
    applyaChange($el);
}

function calcTotal(el) {
    var $el = $(el),
        $total, alter = 'costo',
        $elAlt, val1, val2, idP;
    $parent = $el.parents('div[id]').eq(0);
    idP = $parent.attr('id');
    $total = $parent.find('#total');
    // ejecución
    alter = $el.attr('id') == alter ? 'cantidad' : alter;
    $elAlt = $('#' + idP + ' #' + alter);
    val1 = $el.val();
    val2 = $elAlt.val();
    total = (val1 * val2);
    if (!$.isEmptyObject(val1) && !$.isEmptyObject(val2)) {
        $total.val(total);
    } else {
        $total.val(0);
    }
}

function getRows(tbl) {
    var $tbl = $('#' + tbl),
        rows = [],
        flag = 0,
        campos = getMatchets(tbl + ' #0'),
        msg = '',
        nombreCampo = {
            'partida': 'Partida',
            'clave': 'Clave',
            'bienServicio': 'Bien/Servicio',
            'descBienServicio': 'Descripción',
            'cantidad': 'Cantidad',
            /*'costo':'Costo Unitatio',*/
            'garantia': 'Garantía'
        };
    // reseteo de error
    window.errrorRegistros = 0;
    $.each($tbl.find('div[id]'), function(index, val) {
        var oldIndex = index;
        index = $(val).attr('id');
        var params = getParams(tbl + ' #' + index),
            errCampo = '',
            f = 0;
        $.each(params, function(ind, valor) {
            if (campos.indexOf(ind) !== -1) {
                if ($.isEmptyObject(valor)) {
                    errCampo += (f == 0 ? '' : ',') + ' ' + nombreCampo[ind];
                    f++;
                    flag++;
                }
            }
        });
        msg += (f != 0 ? "<br>Linea #" + ($('#' + index + ' #numeral').html()) + " Campos: " + errCampo : '');
        // asignacion de la informacion en caso de ser correcto
        if (flag == 0) {
            rows[oldIndex] = params;
        }
    });
    if (flag) {
        window.errrorRegistros = 1;
        muestraModalGeneral(3, 'Error de Datos', 'Es necesario colocar los datos que se indican a continuacion:' + msg);
        // colocacion de lineas en cero
        rows = [];
    }
    return rows;
}

function cleanForm() {
    var campos = [{
            item: 'bienServicio',
            value: '',
            type: 'select'
        },
        {
            item: 'descBienServicio',
            value: '',
            type: 'input'
        },
        {
            item: 'unidad',
            value: 'Unidad',
            type: 'html'
        },
        {
            item: 'cantidad',
            value: '',
            type: 'input'
        },
        {
            item: 'costo',
            value: '',
            type: 'input'
        },
        {
            item: 'total',
            value: '0',
            type: 'html'
        },
        {
            item: 'garantia',
            value: '',
            type: 'input'
        },
    ];
    $.each(campos, function(index, element) {
        var $el = $('#' + element.item);
        // compueba si es html lo que se tiene que agregar
        if (element.type == 'html') {
            $el.html(element.value);
        } else {
            $el.val(element.value);
        }
        // se compueba si es necesario ejecutar un trigger
        if (element.type == 'select') {
            $el.trigger('change').multiselect('updateButtonText');
        }
    });
}

function removeOnlyHtmlById(id) {
    var $el = $('#' + id);
    $el.remove();
}

function reordena(tabla) {
    var $tbl = $(tabla);
    var lineas = $tbl.find('div[id]');
    lineas.each(function(index, el) {
        $(el).find('#numeral').html(++index);
    });
}

function getCountRow(tabla) {
    return $(tabla).find('div[id]').length + 1;
}

function generaOptiosSelect(datosOrigen, seleccionado, tripped) {
    var opciones = '',
        tripped = tripped || false;
    $.each(datosOrigen, function(index, elemento) {
        if (elemento.hasOwnProperty('value')) {
            if (seleccionado == elemento.value) {
                opciones += `<option value="${elemento.value}" selected="selected">${elemento.label}</option>`;
            } else {
                opciones += `<option value="${elemento.value}">${elemento.label}</option>`;
            }
        } else if (elemento.hasOwnProperty('id') && tripped) {
            if (seleccionado == elemento.id) {
                opciones += `<option value="${elemento.id}" selected="selected">${elemento.id}</option>`;
            } else {
                opciones += `<option value="${elemento.id}">${elemento.id}</option>`;
            }
        } else if (elemento.hasOwnProperty('id') && !tripped) {
            if (seleccionado == elemento.id) {
                opciones += `<option value="${elemento.id}" selected="selected">${elemento.desc}</option>`;
            } else {
                opciones += `<option value="${elemento.id}">${elemento.desc}</option>`;
            }
        }
    });
    return opciones;
}

/**
 * Funcion para la configuración de las acciones de envio de peticiones.
 */
function setAjaxSetup() {
    // se define el comportamiento del evento de inicio y termino de tranzacción de ajax
    $(document).ajaxStart(muestraCargandoGeneral);
    $(document).ajaxStop(ocultaCargandoGeneral);
}

/**
 * Funcion para limpiar el campo de referencia o obsercaciones cuando
 * se realice el cambio de partida, clave o servicio
 * @return {[type]} [description]
 */
function applyaChange($currentElement) {
    var $currentRow = $currentElement.parents('div[id]').eq(0);
    if (!$.isEmptyObject(estatusAnexo) || !$.isEmptyObject(folio)) {
        $currentRow.find('#descBienServicio').val('');
        $currentRow.find('#cantidad').val('');
    }
}
