<!-- Componentes VUE -->

<!--Variables Default-->
var defaultName = "ElementoDefault";
var defaultClassText = "form-control";
var defaultClassListado = "form-control campoListado";
var defaultClassTextArea = "form-control btn-block";
var defaultClassButton = "btn btn-default botonVerde";
var defaultOnPaste = "return false";
var defaultLabel = "Sin Texto";
var defaultNumberMin = 0;
var defaultNumberMax = 1000000000000000000;
var defaultButtonType = "button";
var defaultClassLabel = "";
var defaultEstiloGeneral = "width: 100%;";
var defaultClassNumLabelSearch = "form-control num";
var defaultClassTextDisabled = false;

<!-- Inicio component-text -->
var componentInputText = Vue.component('component-text', {
    props: {
        type: {
            type: String,
            required: false,
            default: "text"
        },
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassText
        },
        styleC: {
            type: String,
            required: false,
            default: ""
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: ""
        },
        maxlength: {
            type: Number,
            required: false,
            default: 100
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        },
        disabled:{
            type: String,
            required: false,
            default: defaultClassTextDisabled
        }
    },
    template: '<input :type="this.type" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste" :disabled="this.disabled" />'
});
<!-- Fin component-text -->

<!-- Inicio component-text-label -->
var componentInputTextLabel = Vue.component('component-text-label', {
    props: {
        label: {
            type: String,
            required: false,
            value: defaultLabel
        },
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassText
        },
        styleC: {
            type: String,
            required: false,
            default: defaultEstiloGeneral
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: ""
        },
        maxlength: {
            type: Number,
            required: false,
            default: 100
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        },
        disabled:{
            type: String,
            required: false,
            default: defaultClassTextDisabled
        },
        readonly:{
            type: String,
            required: false,
            default: false
        }
    },
    template: '\
    <div class="form-inline row">\
        <div class="col-md-3 col-xs-12" >\
            <span><label>{{ this.label }}</label></span>\
        </div>\
        <div class="col-md-9 col-xs-12">\
            <input type="text" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste" :disabled="this.disabled" :readonly="this.readonly" />\
        </div>\
    </div><br>\
    '
    // template: '\
    // <div class="input-group">\
    //     <span class="input-group-addon" style="background: none; border: none;">{{ this.label }}</span>\
    //     <input type="text" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste" />\
    // </div>\
    // '
});
<!-- Fin component-text-label -->

<!-- Inicio component-text-save -->
var componentInputTextSave = Vue.component('component-text-save', {
    props: {
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassText
        },
        styleC: {
            type: String,
            required: false,
            default: defaultEstiloGeneral
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: ""
        },
        maxlength: {
            type: Number,
            required: false,
            default: 100
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        },
        onclick: {
            type: String,
            required: false,
            default: ""
        }
    },
    template: '\
    <div class="input-group">\
          <input type="text" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :maxlength="this.maxlength" :onpaste="this.onpaste" />\
          <span class="input-group-btn">\
            <button class="btn btn-default glyphicon glyphicon-floppy-saved" type="button" :onclick="this.onclick"></button>\
          </span>\
    </div>\
    <br>\
    '
    // template: '\
    // <div class="input-group">\
    //     <span class="input-group-addon" style="background: none; border: none;">{{ this.label }}</span>\
    //     <input type="text" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste" />\
    // </div>\
    // '
});
<!-- Fin component-text-save -->

<!-- Inicio component-checkbox -->
var componentInputcheckbox = Vue.component('component-checkbox', {
    props: {
        label: {
            type: String,
            required: false,
            value: defaultLabel
        },
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassText
        },
        styleC: {
            type: String,
            required: false,
            default: "width: 20px; height:20px;"
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: ""
        },
        maxlength: {
            type: Number,
            required: false,
            default: 100
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        }
    },
    template: '\
    <div class="form-inline row">\
        <div class="col-md-8 col-xs-12">\
            <label>{{ this.label }}</label>\
        </div>\
        <div class="col-md-4 col-xs-12">\
            <input type="checkbox" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste" />\
        </div>\
    </div><br>\
    '
    // template: '\
    // <div class="input-group">\
    //     <span class="input-group-addon" style="background: none; border: none;">{{ label }}</span>\
    //     <input type="checkbox" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste" />\
    // </div>\
    // '
});
<!-- Fin component-checkbox -->

<!-- Inicio component-textarea-label -->
var componentTextareaLabel = Vue.component('component-textarea-label', {
    props: {
        label:{
            type: String,
            required: false,
            value: defaultLabel
        },
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        rows: {
            type: String,
            required: false,
            default: "5"
        },
        cols: {
            type: String,
            required: false,
            default: "15"
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassTextArea
        },
        styleC: {
            type: String,
            required: false,
            default: "width: 100%;"
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: ""
        },
        maxlength: {
            type: Number,
            required: false,
            default: 100
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        },
        onchange: {
            type: String,
            required: false,
            default: ""
        }
    },
    template: '\
    <div class="form-inline row">\
        <div class="col-md-3 col-xs-12">\
            <label>{{ this.label }}</label>\
        </div>\
        <div class="col-md-9 col-xs-12">\
            <textarea :id="this.id" :name="this.name" :value="this.value" :rows="this.rows" :onchange="this.onchange" :cols="this.cols" :onchange="this.onchange" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste"></textarea>\
        </div>\
    </div><br>\
    '
    // template: '\
    // <div class="input-group">\
    //     <span class="input-group-addon" style="background: none; border: none;">{{ label }}</span>\
    //     <textarea :id="this.id" :name="this.name" :value="this.value" :rows="this.rows" :cols="this.cols" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste"></textarea>\
    // </div>\
    // '
});
<!-- Fin component-textarea-label -->

<!-- Inicio component-textarea -->
var componentTextarea = Vue.component('component-textarea', {
    props: {
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        rows: {
            type: String,
            required: false,
            default: ""
        },
        cols: {
            type: String,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassTextArea
        },
        styleC: {
            type: String,
            required: false,
            default: ""
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: ""
        },
        maxlength: {
            type: Number,
            required: false,
            default: 1000
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        }
    },
    template: '\
    <div class="input-group">\
        <textarea :id="this.id" :name="this.name" :value="this.value" :rows="this.rows" :cols="this.cols" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste"></textarea>\
    </div>\
    '

});
<!-- Fin component-textarea -->

<!-- Inicio component-number -->
var componentInputNumber = Vue.component('component-number', {
    props: {
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassText
        },
        styleC: {
            type: String,
            required: false,
            default: ""
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: "return soloNumeros(event)"
        },
        min: {
            type: Number,
            required: false,
            default: defaultNumberMin
        },
        max: {
            type: Number,
            required: false,
            default: defaultNumberMax
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        },
        disabled: {
            type: String,
            required: false,
            default: defaultClassTextDisabled
        },
        onchange: {
            type: String,
            required: false,
            default: ""
        },
        maxlength: {
            type: Number,
            required: false,
            default: 100
        },
        step: {
            type: Number,
            required: false,
            default: ""
        },
        readonly:{
            type: String,
            required: false,
            default: false
        }
    },
    template: '<input type="number" :id="this.id" :name="this.name" :value="this.value" :onchange="this.onchange" :placeholder="this.placeholder" :maxlength="this.maxlength" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :min="this.min" :max="this.max" :onpaste="this.onpaste" :disabled="this.disabled" :readonly="this.readonly" :step="this.step" />'
});
<!-- Fin component-number -->

<!-- Inicio component-number-label -->
var componentInputNumberLabel = Vue.component('component-number-label', {
    props: {
        label: {
            type: String,
            required: false,
            value: defaultLabel
        },
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: Number,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassText
        },
        styleC: {
            type: String,
            required: false,
            default: defaultEstiloGeneral
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: "return soloNumeros(event)"
        },
        min: {
            type: Number,
            required: false,
            default: defaultNumberMin
        },
        max: {
            type: Number,
            required: false,
            default: defaultNumberMax
        },
        maxlength: {
            type: Number,
            required: false,
            default: 100
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        },
        disabled: {
            type: String,
            required: false,
            default: defaultClassTextDisabled
        },
        onchange: {
            type: String,
            required: false,
            default: ""
        },
        readonly:{
            type: String,
            required: false,
            default: false
        }
    },
    template: '\
    <div class="form-inline row">\
        <div class="col-md-3 col-xs-12">\
            <label>{{ this.label }}</label>\
        </div>\
        <div class="col-md-9 col-xs-12">\
            <input type="text" :id="this.id" :name="this.name" :value="this.value" :onchange="this.onchange" :placeholder="this.placeholder" :title="this.title" :class="classC" :style="styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :min="this.min" :max="this.max" :maxlength="this.maxlength" :onpaste="this.onpaste" :disabled="this.disabled" :readonly="this.readonly" />\
        </div>\
    </div><br>\
    '
    // template: '\
    // <div class="input-group">\
    //     <span class="input-group-addon" style="background: none; border: none;">{{ this.label }}</span>\
    //     <input type="number" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="classC" :style="styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :min="this.min" :max="this.max" :onpaste="this.onpaste" />\
    // </div>\
    // '
});
<!-- Fin component-number-label -->

<!-- Inicio component-decimales -->
var componentInputDecimales = Vue.component('component-decimales', {
    props: {
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassText
        },
        styleC: {
            type: String,
            required: false,
            default: ""
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: "return fnsoloDecimalesGeneral(event, this)"
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        },
        disabled: {
            type: String,
            required: false,
            default: defaultClassTextDisabled
        },
        onchange: {
            type: String,
            required: false,
            default: ""
        },
        maxlength: {
            type: Number,
            required: false,
            default: 100
        }
    },
    template: '<input type="text" :id="this.id" :name="this.name" :value="this.value" :onchange="this.onchange" :placeholder="this.placeholder" :maxlength="this.maxlength" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :onpaste="this.onpaste" :disabled="this.disabled" />'
});
<!-- Fin component-decimales -->

<!-- Inicio component-decimales -->
var componentInputDecimalesLabel = Vue.component('component-decimales-label', {
    props: {
        label: {
            type: String,
            required: false,
            value: defaultLabel
        },
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassText
        },
        styleC: {
            type: String,
            required: false,
            default: defaultEstiloGeneral
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: "return fnsoloDecimalesGeneral(event, this)"
        },
        maxlength: {
            type: Number,
            required: false,
            default: 100
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        },
        disabled:{
            type: String,
            required: false,
            default: defaultClassTextDisabled
        },
        readonly:{
            type: String,
            required: false,
            default: false
        }
    },
    template: '\
    <div class="form-inline row">\
        <div class="col-md-3 col-xs-12" >\
            <span><label>{{ this.label }}</label></span>\
        </div>\
        <div class="col-md-9 col-xs-12">\
            <input type="text" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste" :disabled="this.disabled" :readonly="this.readonly" />\
        </div>\
    </div><br>\
    '
    // template: '\
    // <div class="input-group">\
    //     <span class="input-group-addon" style="background: none; border: none;">{{ this.label }}</span>\
    //     <input type="text" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste" />\
    // </div>\
    // '
});
<!-- Fin component-decimales -->

<!-- Inicio component-number-label-search -->
var componentInputNumberLabelSearch = Vue.component('component-number-label-search', {
    props: {
        label: {
            type: String,
            required: false,
            value: defaultLabel
        },
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassNumLabelSearch
        },
        styleC: {
            type: String,
            required: false,
            default: defaultEstiloGeneral
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: "return soloNumeros(event)"
        },
        onblur: {
            type: String,
            required: false,
            default: "fnCleanN()"
        },
        min: {
            type: Number,
            required: false,
            default: defaultNumberMin
        },
        max: {
            type: Number,
            required: false,
            default: defaultNumberMax
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        },
        onclick: {
            type: String,
            required: false,
            default: ""
        }
    },
    template: '\
    <div class="form-inline row">\
        <div class="col-md-3  col-xs-12">\
            <label>{{ this.label }}</label>\
        </div>\
        <div class="col-md-9 col-xs-12">\
            <div class="input-group">\
                <input type="text" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :onblur="this.onblur" :min="this.min" :maxlength="this.maxlength" :onpaste="this.onpaste" />\
                <span class="input-group-btn">\
                    <button class="btn btn-default glyphicon glyphicon-search" type="button" :onclick="this.onclick"></button>\
                </span>\
            </div>\
        </div>\
    </div><br>\
    '
    // template: '\
    // <div class="input-group">\
    //     <span class="input-group-addon" style="background: none; border: none;">{{ this.label }}</span>\
    //     <input type="number" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="classC" :style="styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :min="this.min" :max="this.max" :onpaste="this.onpaste" />\
    // </div>\
    // '
});
<!-- Fin component-number-label-search -->

<!-- Inicio component-date -->
var componentInputDate = Vue.component('component-date', {
    props: {
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassText
        },
        styleC: {
            type: String,
            required: false,
            default: defaultEstiloGeneral
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: ""
        },
        maxlength: {
            type: Number,
            required: false,
            default: 10
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        },
        disabled:{
            type: String,
            required: false,
            default: defaultClassTextDisabled
        },
        readonly:{
            type: String,
            required: false,
            default: false
        },
        onchange: {
            type: String,
            required: false,
            default: ""
        }
    },
    template: '\
        <div class="input-group date componenteCalendarioClase" data-date-format="dd-mm-yyyy">\
            <input type="text" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste" :disabled="this.disabled" :readonly="this.readonly" :onchange="this.onchange" />\
            <span class="input-group-addon">\
                <span class="glyphicon glyphicon-calendar"></span>\
            </span>\
        </div>'
});
<!-- Fin component-date -->

<!-- Inicio component-date-label -->
var componentInputDateLabel = Vue.component('component-date-label', {
    props: {
        label: {
            type: String,
            required: false,
            value: defaultLabel
        },
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassText
        },
        styleC: {
            type: String,
            required: false,
            default: defaultEstiloGeneral
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: ""
        },
        maxlength: {
            type: Number,
            required: false,
            default: 10
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        },
        disabled:{
            type: String,
            required: false,
            default: defaultClassTextDisabled
        },
        readonly:{
            type: String,
            required: false,
            default: false
        },
        onchange: {
            type: String,
            required: false,
            default: ""
        }
    },
    template: '\
    <div class="form-inline row">\
        <div class="col-md-3 col-xs-12">\
            <label>{{ this.label }}</label>\
        </div>\
        <div class="col-md-9 col-xs-12">\
            <div class="input-group date componenteCalendarioClase" data-date-format="dd-mm-yyyy">\
                <input type="text" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste" :disabled="this.disabled" :readonly="this.readonly" :onchange="this.onchange" />\
                <span class="input-group-addon">\
                    <span class="glyphicon glyphicon-calendar"></span>\
                </span>\
            </div>\
        </div>\
    </div><br>\
    '
    // template: '\
    // <div class="input-group">\
    //     <span class="input-group-addon" style="background: none; border: none;">{{ this.label }}</span>\
    //     <div class="input-group date componenteCalendarioClase" data-date-format="dd-mm-yyyy">\
    //         <input type="text" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste" />\
    //         <span class="input-group-addon">\
    //             <span class="glyphicon glyphicon-calendar"></span>\
    //         </span>\
    //     </div>\
    // </div>\
    // '
});
<!-- Fin component-date-label -->

<!-- Inicio component-button -->
var componentInputButton = Vue.component('component-button', {
    props: {
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        type: {
            type: String,
            required: false,
            default: defaultButtonType
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassButton
        },
        styleC: {
            type: String,
            required: false,
            default: "font-weight: bold;"
        },        
        onclick: {
            type: String,
            required: false,
            default: ""
        },
        disabled:{
            type: String,
            required: false,
            default: defaultClassTextDisabled
        }
    },
    template: '<button :id="this.id" :name="this.name" :type="this.type" :title="this.title" :class="this.classC" :style="this.styleC" :onclick="this.onclick" :disabled="this.disabled">&nbsp;{{ this.value }}</button>'
});
<!-- Fin component-button -->

<!-- Inicio component-label-text -->
var componentInputLabelText = Vue.component('component-label-text', {
    props: {
        label: {
            type: String,
            required: false,
            value: defaultLabel
        },
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassLabel
        },
        styleC: {
            type: String,
            required: false,
            default: 'width: 100%; font-weight: normal;'
        }
    },
    template: '\
    <div class="form-inline row">\
        <div class="col-md-3 col-xs-12">\
            <label>{{ this.label }}</label>\
        </div>\
        <div class="col-md-9 col-xs-12">\
            <label :id="this.id" :name="this.name" :class="this.classC" :style="this.styleC">{{ this.value }}</label>\
        </div>\
    </div><br>\
    '
    // template: '\
    // <div class="input-group">\
    //     <span class="input-group-addon" style="background: none; border: none;">{{ this.label }}</span>\
    //     <label :id="this.id" :name="this.name" :class="this.classC" :style="this.styleC">{{ this.value }}</label>\
    // </div>\
    // '
});
<!-- Fin component-label-text -->

<!-- Inicio component-date-feriado Sin Label -->
var componentInputDateFeriado2 = Vue.component('component-date-feriado2', {
    props: {
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassText
        },
        styleC: {
            type: String,
            required: false,
            default: defaultEstiloGeneral
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: ""
        },
        maxlength: {
            type: Number,
            required: false,
            default: 10
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        },
        onchange: {
            type: String,
            required: false,
            default: ""
        }
    },
    template: '\
    <div class="input-group date componenteFeriadoAtras" data-date-format="dd-mm-yyyy">\
        <input type="text" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste" :onchange="this.onchange" />\
        <span class="input-group-addon">\
            <span class="glyphicon glyphicon-calendar"></span>\
        </span>\
    </div><br>\
    '
});
<!-- Fin component-date-feriado Sin Label -->

<!-- Inicio component-date-feriado -->
var componentInputDateFeriado = Vue.component('component-date-feriado', {
    props: {
        label: {
            type: String,
            required: false,
            value: defaultLabel
        },
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassText
        },
        styleC: {
            type: String,
            required: false,
            default: defaultEstiloGeneral
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: ""
        },
        maxlength: {
            type: Number,
            required: false,
            default: 10
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        },
        onchange: {
            type: String,
            required: false,
            default: ""
        }
    },
    template: '\
    <div class="form-inline row">\
        <div >\
            <label>{{ this.label }}</label>\
            <div class="input-group date componenteFeriadoClase" data-date-format="yyyy-mm-dd">\
                <input type="text" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste" :onchange="this.onchange" />\
                <span class="input-group-addon">\
                    <span class="glyphicon glyphicon-calendar"></span>\
                </span>\
            </div>\
        </div>\
    </div><br>\
    '
});
<!-- Fin component-date-feriado -->

<!-- Inicio component-date-feriado-bloqueo-anio-siguiente -->
var componentInputDisableNextYear = Vue.component('component-date-disable-next-year', {
    props: {
        label: {
            type: String,
            required: false,
            value: defaultLabel
        },
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassText
        },
        styleC: {
            type: String,
            required: false,
            default: defaultEstiloGeneral
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: ""
        },
        maxlength: {
            type: Number,
            required: false,
            default: 10
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        },
        onchange: {
            type: String,
            required: false,
            default: ""
        }
    },
    template: '\
    <div class="form-inline row">\
        <div class="col-md-3 col-xs-12">\
            <label>{{ this.label }}</label>\
        </div>\
        <div class="col-md-9 col-xs-12">\
            <div class="input-group date componenteDisableNextYear" data-date-format="dd-mm-yyyy">\
                <input type="text" :id="this.id" :name="this.name" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste" :disabled="this.disabled" :readonly="this.readonly" :onchange="this.onchange" />\
                <span class="input-group-addon">\
                    <span class="glyphicon glyphicon-calendar"></span>\
                </span>\
            </div>\
        </div>\
    </div><br>\
    '
});
<!-- Inicio component-date-feriado-bloqueo-anio-siguiente -->

<!-- Inicio component-administrador-Archivos-->
var componentInputAdministrarArchivos = Vue.component('component-administrador-archivos', {
    props: {
        
         idcomponente: {
            type: String,
            required: false,
            default: ""
        },
        funcion: {
            type: String,
            required: false,
            default: ""
        },
        tipo: {
            type: String,
            required: false,
            default: ""
        },
        trans: {
            type: String,
            required: false,
            default: ""
        },
        esmultiple: {
            type: String,
            required: false,
            default: 0
        }
    },
    methods : {
        combina : function(texto,texto1=''){
             return texto+ this.idcomponente + texto1 
            
        },
        funcionParametroId : function(nombreFuncion){
             return nombreFuncion+'('+this.idcomponente + ')' 
            
        }
    },
    template:'\
<div class="cargarArchivosComponente" >\
    <input type="hidden" id="esMultiple" name="esMultiple" :value="this.esmultiple">\
    <input type="hidden" :value="this.idcomponente" :name="combina(\'componente\')" id="componenteArchivos"/>\
    <input type="hidden" :value="this.funcion" :id="combina(\'funcionArchivos\')" :name="combina(\'funcionArchivos\')"/>\
    <input  type="hidden"  :value="this.tipo" id="tipoArchivo"/>\
  <input  type="hidden"  :value="this.trans" id="transnoArchivo"/>\
    <div :id="combina(\'mensajeArchivos\')"> </div>\
    <div  :id="combina(\'subirArchivos\')"  class="col-md-12">\
        <div class="col-md-12" style="color:#fff !important;">\
            <div class="col-md-6">\
            <div id="tipoInputFile"> </div>\
                <!--<input type="file"  class="btn bgc8"  :name="combina(\'archivos\',\'[]\')"  :id="combina(\'cargarMultiples\')"  multiple="multiple"  style="display: none;"/>-->\
                <button  class="btn bgc8" :id="combina(\'cuadroDialogoCarga\')" :onclick="funcionParametroId(\'fnCargarArchivos\')">\
                    <span class="glyphicon glyphicon-file"></span>\
                    Cargar archivo(s)\
                </button >\
                <br>\
                <br/>\
                <button :id="combina(\'enviarArchivosMultiples\')" class="btn bgc8" style="display: none;" >Subir</button>\
                <br/>\
                <br/>\
            </div>\
            <br>\
        </div>\
        <div :id="combina(\'muestraAntesdeEnviar\')" class="col-md-12 col-xs-12"> </div>\
        <br/> <br/>\
    </div>\
        <div :id="combina(\'enlaceDescarga\')" class="col-md-12 col-xs-12"> </div>\
        <div :id="combina(\'accionesArchivos\')"  style="color:#fff !important;display: none;">\
            <div class="col-md-3">\
                <button :id="combina(\'eliminarMultiples\')" class="btn bgc8" onclick="fnBorrarConfirmaArch()" >Eliminar</button>\
                <br/>\
            </div>\
            <div class="col-md-3">\
                <button :id="combina(\'descargarMultiples\')" class="btn bgc8" onclick="fnProcesosArchivosSubidos(\'descargar\')" >Descargar</button>\
                <br/>\
            </div>\
        </div>\
    <div :name="combina(\'divTablaArchivos\')" :id="combina(\'divTablaArchivos\')" class="col-md-12 col-xs-12">\
        <div :name="combina(\'divDatosArchivos\')" class="col-md-12 col-xs-12" :id="combina(\'divDatosArchivos\')"></div>\
    </div>\
    <div class="modal fade" :id="combina(\'ModalBorrarArchivos\')"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel">\
        <div class="modal-dialog" role="document" name="ModalGeneralTam" id="ModalGeneralTam">\
            <div class="modal-content">\
                <div class="navbar navbar-inverse navbar-static-top">\
                    <div class="col-md-lg menu-usuario">\
                        <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>\
                    </div>\
                    <div id="navbar" class="navbar-collapse collapse">\
                        <div class="nav navbar-nav">\
                            <div class="title-header">\
                                <div :id="combina(\'ModalBorrarArchivos\',\'_Titulo\')" ></div>\
                            </div>\
                        </div>\
                    </div>\
                    <div class="linea-verde"></div>\
                </div>\
                <div class="modal-body" :id="combina(\'ModalBorrarArchivos\',\'_Mensaje\')">\
                    <div class="col-md-9" :id="combina(\'listaBorrarArchivos\')" >\
                        <h3>¿Desea borrar los archivos seleccionados?</h3>\
                    </div>\
                </div>\
                <br> <br> <br>\
                <div class="modal-footer">\
                    <div class="col-xs-6 col-md-6 text-right">\
                        <div :id="combina(\'procesandoPagoEspere\')"> </div> <br>\
                        <component-button type="button" :id="combina(\'btnConfirmarEliminar\')"  onclick="fnProcesosArchivosSubidos(\'eliminar\')" value="Borrar">\
                        </component-button><component-button type="button" :id="combina(\'btnCerrarConfirma\')"   data-dismiss="modal" value="Cancelar"></component-button>\
                    </div>\
                </div>\
            </div>\
        </div>\
    </div>\
</div>\
'
});
<!--component-administrador-Archivos-->

<!-- Inicio component-layouts-generados-->
var componentInputLayoutsGenerados = Vue.component('component-layouts-generados', {
    props: {
         id: {
            type: String,
            required: false,
            default: ""
        },
         funcion: {
            type: String,
            required: false,
            default: ""
        },
        trans: {
            type: String,
            required: false,
            default: ""
        },
        tipo: {
            type: String,
            required: false,
            default: ""
        }
    },
    template:'\
    <div :id="this.id" class="layoutsGenerados">\
<div id="enlaceDescargaLayouts" class="col-md-12 col-xs-12"> </div>\
    <div id="accionesLayouts"  style="color:#fff !important;display: none;">\
        <div class="col-md-3">\
            <button id="eliminarLayouts" class="btn bgc8" onclick="fnBorrarConfirmaLayout()" >Eliminar</button>\
            <br/>\
        </div>\
        <div class="col-md-3">\
            <button id="descargarLayouts" class="btn bgc8" onclick="fnProcesosLayouts(\'descargar\')" >Descargar\
            </button>\
            <br/>\
        </div>\
    </div>\
  <input  type="hidden"  :value="this.funcion" id="funcionLayout"/>\
  <input  type="hidden"  :value="this.tipo" id="tipoLayout"/>\
  <input  type="hidden"  :value="this.trans" id="transnoLayout"/>\
   <div name="" id="tablaRecuperarLayouts" class="col-md-12 col-xs-12">\
        <div  class="col-md-12 col-xs-12" id="datosRecuperarLayouts">\
        </div>\
    </div>\
    <div class="modal fade" id="ModalBorrarLayouts"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel">\
    <div class="modal-dialog" role="document" name="ModalGeneralTam" id="ModalGeneralTam">\
        <div class="modal-content">\
            <div class="navbar navbar-inverse navbar-static-top">\
                <div class="col-md-lg menu-usuario">\
                    <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>\
                </div>\
                <div id="navbar" class="navbar-collapse collapse">\
                    <div class="nav navbar-nav">\
                        <div class="title-header">\
                            <div id="ModalBorrarLayouts_Titulo" ></div>\
                        </div>\
                    </div>\
                </div>\
                <div class="linea-verde"></div>\
            </div>\
            <div class="modal-body" id="ModalBorrarLayouts_Mensaje">\
                <div class="col-md-9" id="listaBorrarLayouts" >\
                    <h3>¿Desea borrar los layouts seleccionados?</h3>\
                </div>\
            </div>\
            <br> <br> <br>\
            <div class="modal-footer">\
                <div class="col-xs-6 col-md-6 text-right">\
                    <div id="procesandoPagoEspere"> </div> <br>\
                    <component-button type="button" id="btnConfirmarEliminar"  onclick="fnProcesosLayouts(\'eliminar\')" value="Borrar">\
                    </component-button><component-button type="button" :id="btnCerrarConfirma"   data-dismiss="modal" value="Cancelar"></component-button>\
                </div>\
            </div>\
        </div>\
    </div>\
</div>\
</div>\
'
});
<!--component-layouts-generados-->

<!-- Inicio component-listado -->
var componentInputListado = Vue.component('component-listado', {
    props: {
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassListado
        },
        styleC: {
            type: String,
            required: false,
            default: defaultEstiloGeneral
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: ""
        },
        maxlength: {
            type: Number,
            required: false,
            default: 100
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        },
        disabled:{
            type: String,
            required: false,
            default: defaultClassTextDisabled
        },
        readonly:{
            type: String,
            required: false,
            default: false
        }
    },
    methods : {
        idCompuesto : function(texto){
            return texto+this.id
        }
    },
    template: '\
            <input type="text" :id="idCompuesto(\'textoVisible__\')" :name="idCompuesto(\'textoVisible__\')" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste" :disabled="this.disabled" :readonly="this.readonly" autocomplete="off" />\
            <input type="hidden" :id="this.id" :name="this.name" value="">\
            <input type="hidden" :id="idCompuesto(\'textoOculto__\')" :name="idCompuesto(\'textoOculto__\')" value="">\
            <div :id="idCompuesto(\'sugerencia-\')" style="position:absolute; z-index:999; display:block;"></div>\
    '
});
<!-- Fin component-listado -->

<!-- Inicio component-listado-label -->
var componentInputListadoLabel = Vue.component('component-listado-label', {
    props: {
        label: {
            type: String,
            required: false,
            value: defaultLabel
        },
        id: {
            type: String,
            required: false,
            default: defaultName
        },
        name: {
            type: String,
            required: false,
            default: defaultName
        },
        value: {
            type: String,
            required: false,
            default: ""
        },
        placeholder: {
            type: String,
            required: false,
            default: ""
        },
        title: {
            type: String,
            required: false,
            default: ""
        },
        classC: {
            type: String,
            required: false,
            default: defaultClassListado
        },
        styleC: {
            type: String,
            required: false,
            default: defaultEstiloGeneral
        },
        onkeyup: {
            type: String,
            required: false,
            default: ""
        },
        onkeypress: {
            type: String,
            required: false,
            default: ""
        },
        maxlength: {
            type: Number,
            required: false,
            default: 100
        },
        onpaste: {
            type: String,
            required: false,
            default: defaultOnPaste
        },
        disabled:{
            type: String,
            required: false,
            default: defaultClassTextDisabled
        },
        readonly:{
            type: String,
            required: false,
            default: false
        }
    },
    methods : {
        idCompuesto : function(texto){
            return texto+this.id
        }
    },
    template: '\
    <div class="form-inline row">\
        <div class="col-md-3 col-xs-12" style="vertical-align: middle;" >\
            <span><label>{{ this.label }}</label></span>\
        </div>\
        <div class="col-md-9 col-xs-12">\
            <input type="text" :id="idCompuesto(\'textoVisible__\')" :name="idCompuesto(\'textoVisible__\')" :value="this.value" :placeholder="this.placeholder" :title="this.title" :class="this.classC" :style="this.styleC" :onkeyup="this.onkeyup" :onkeypress="this.onkeypress" :maxlength="this.maxlength" :onpaste="this.onpaste" :disabled="this.disabled" :readonly="this.readonly" autocomplete="off" />\
            <input type="hidden" :id="this.id" :name="this.name" value="">\
            <input type="hidden" :id="idCompuesto(\'textoOculto__\')" :name="idCompuesto(\'textoOculto__\')" value="">\
            <div :id="idCompuesto(\'sugerencia-\')" style="position:absolute; z-index:999; display:block;"></div>\
        </div>\
    </div><br>\
    '
});
<!-- Fin component-listado-label -->

function fnRenderAdministradorArchivos(divRenderizar='appVue') {
new Vue({
        el: '#'+divRenderizar,
        data: {
            msg: ""
        },
        components: {
          
            'component-administrador-archivos':componentInputAdministrarArchivos
               
        }
    }).$mount('#'+divRenderizar);
}

function fnDiasFeriados(){
   var diasFeriados= new Array();
    dataObj = { 
            option: 'diasFeriados',
     
          };
    
    $.ajax({
          method: "POST",
          dataType:"json",
          url: "modelo/componentes_modelo.php",
          async: false,
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){
            //Si trae informacion
            diasFeriados=data.contenido.diasFeriados;
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
    //alert(diasFeriados);
    return diasFeriados;
}

function fnEjecutarVueGeneral(divRenderizar='appVue') {
    new Vue({
        el: '#'+divRenderizar,
        data: {
            msg: ""
        },
        components: {
            'component-text': componentInputText,
            'component-text-label': componentInputTextLabel,
            'component-text-save': componentInputTextSave,
            'component-checkbox': componentInputcheckbox,
            'component-textarea-label': componentTextareaLabel,
            'component-textarea': componentTextarea,
            'component-number': componentInputNumber,
            'component-number-label': componentInputNumberLabel,
            'component-decimales': componentInputDecimales,
            'component-decimales-label': componentInputDecimalesLabel,
            'component-number-label-search': componentInputNumberLabelSearch,
            'component-date': componentInputDate,
            'component-date-label': componentInputDateLabel,
            'component-button': componentInputButton,
            'component-label-text': componentInputLabelText,
            'component-date-feriado':componentInputDateFeriado,
            'component-date-disable-next-year':componentInputDisableNextYear,
            'component-date-feriado2':componentInputDateFeriado2,
            'component-administrador-archivos':componentInputAdministrarArchivos,
            'component-layouts-generados':componentInputLayoutsGenerados,
            'component-listado': componentInputListado,
            'component-listado-label': componentInputListadoLabel
            
               
        }
    }).$mount('#'+divRenderizar);

    // Validat si existe el elemento para dar formato
    var componenteCalendarioClase = 0;
    $('#'+divRenderizar).find('.componenteCalendarioClase').each(function(index, el) {
        componenteCalendarioClase = 1;
    });
    if(componenteCalendarioClase == 1) {
        // Configuración Calendario
        $('.componenteCalendarioClase').datetimepicker({
            format: 'DD-MM-YYYY'
        });
    }

    // Validat si existe el elemento para dar formato
    var componenteFeriadoClase = 0;
    $('#'+divRenderizar).find('.componenteFeriadoClase').each(function(index, el) {
        componenteFeriadoClase = 1;
    });
    if(componenteFeriadoClase == 1) {
        // Configuración Feriados
        $('.componenteFeriadoClase').datetimepicker({
            format: 'DD-MM-YYYY',
            disabledDates:fnDiasFeriados(),
            daysOfWeekDisabled: [0,6],
            minDate: new Date()
            //$('.date_field').datepicker("widget").css({"z-index":100});
        });
    }
    //
    var componenteDisableAnio = 0;
    $('#'+divRenderizar).find('.componenteDisableNextYear').each(function(index, el) {
        componenteDisableAnio = 1;
    });
    if(componenteDisableAnio == 1) {
        // Configuración Feriados
        hoy = new Date();
        anio = hoy.getFullYear();
        minFecha="01-01-"+anio;
        maxFecha="12-31-"+anio;
        
        $('.componenteDisableNextYear').datetimepicker({
            format: 'DD-MM-YYYY',
           
            minDate: new Date(minFecha),
            maxDate: new Date(maxFecha)
            //$('.date_field').datepicker("widget").css({"z-index":100});
        });
    }
    // Validat si existe el elemento para dar formato
    var componenteFeriadoAtras = 0;
    $('#'+divRenderizar).find('.componenteFeriadoAtras').each(function(index, el) {
        componenteFeriadoAtras = 1;
    });
    if(componenteFeriadoAtras == 1) {
        // $('.ui-datepicker').css('z-index', 99999999999999 !important);
        $('.componenteFeriadoAtras').datetimepicker({
            format: 'DD-MM-YYYY',
            disabledDates:fnDiasFeriados(),
            daysOfWeekDisabled: [0,6]
        });
    }
}

fnEjecutarVueGeneral();
