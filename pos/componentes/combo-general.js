var React = require('react');
var Collapse = require('react-bootstrap').Collapse;
var Tooltip = require('react-bootstrap').Tooltip;
var OverlayTrigger = require('react-bootstrap').OverlayTrigger;
var Select = require('react-select');
var axios = require('axios');
//
var ComboGeneral = React.createClass({

	propTypes: {
		selectValue: React.PropTypes.string,
		selectLabel: React.PropTypes.string,
		toolTipText: React.PropTypes.string,
		api: React.PropTypes.object,
	},

	getDefaultProps () {
		return {
			selectValue: '0',
			selectLabel: 'No Configurado',
			toolTipText: 'Button Combo Box',
			api: {
				name: 'No Configurado',
				func: '',
				value: 'value',
				label: 'label',
                selection: 'selection'
			},
		};
	}, 

	getInitialState: function() {
        return {
            seleccionado: true,
            cambiar: false,
            value: this.props.selectValue,
            label: this.props.selectLabel,
            options: []
        };
    },

    componentWillMount: function () {
		this.getOptions();
	},

    // Esta funcion debe venir como propiedad del componente para 
	// que este sea general
	getOptions: function () {
        console.log("ejecuto funcion "+this.props.api.func);
    
        var self = this;
    	var url = './../mvc/api/v1/' + this.props.api.func;

        if (this.props.api.func != "") {

            if (this.props.api.name == "ListaPrecios") {
                url = url + "/" + this.props.branchcode;
            }

            //obtener si existe sesion, si no tiene sesion termina el proceso
            self.props.obtenerSession();
            
            axios.get(url)
            .then(function (response) {
                if(response.data.success) {
                    var i;
                    var arrayObj = response.data.data;
                    //console.log("object: "+JSON.stringify(arrayObj));
                    for(i = 0; i < arrayObj.length; i++){
                        arrayObj[i].value = arrayObj[i][self.props.api.value];
                        arrayObj[i].label = arrayObj[i][self.props.api.label];
                        if (self.props.api.name == "Vendedor") {
                            //Si es vendedor buscar el selecccion
                            arrayObj[i].selection = arrayObj[i][self.props.api.selection];
                        }
                        delete arrayObj[i].tagref;
                        delete arrayObj[i].tagdescriptionc;                                    
                    }

                    if (self.props.api.name == "Vendedor") {                          
                        for (var info in arrayObj) {
                            if (Number(arrayObj[info].selection) == 1) {
                                //Vendedor seleccion
                                self.state.value = arrayObj[info].value;
                                self.state.label = arrayObj[info].label;
                                self.props.seleccionarVendedor(arrayObj[info].value, arrayObj[info].label);
                            }
                        }
                    }
                    self.setState({
                        options: arrayObj
                    });
                } else {
                    var options = [{
                        value: '',
                        label: 'Configurar ' + self.props.api.name
                    }];
                    self.setState({
                        options: options
                    });
                }
            })
            .catch(function (error) {
                console.log(error);
            });
        }
    },

    cambiar: function () {
    	this.setState({
    		seleccionado: !this.state.seleccionado,
            cambiar: !this.state.cambiar,
    	});
    },

    seleccionado: function() {
    	this.setState({
    		seleccionado: !this.state.seleccionado,
            cambiar: !this.state.cambiar,
    	});
    },

    // Esta funcion debe venir como propiedad del componete 
    // para que sea general y asi definir donde guardar el valor seleccionado
    setValue: function(val) {
    	this.setState({ 
    		value: val.value,
    		label: val.label,
    	});
    	this.seleccionado();
        if (this.props.api.name == "Unidades de Negocio") {
            //si cambia de unidad de negocio actualizar variables 
            //console.log("Unidad Negocio Sel: "+val.value+" "+val.label);
            this.props.seleccionarUnidadNegocio(val.value, val.label);
        }
        if (this.props.api.name == "Almacenes") {
            //si cambia de almacenes actualizar variables 
            //console.log("Almacen Sel: "+val.value+" "+val.label);
            this.props.seleccionarAlmacen(val.value, val.label);
        }
        if (this.props.api.name == "Vendedor") {
            //si cambia de unidad de negocio actualizar variables 
            //console.log("Vendedor Sel: "+val.value+" "+val.label);
            this.props.seleccionarVendedor(val.value, val.label);
        }
        if (this.props.api.name == "Tipo de Comprobante") {
            //si cambia de unidad de negocio actualizar variables 
            //console.log("Tipo de Comprobante Sel: "+val.value+" "+val.label);
            this.props.seleccionarTipoComprobante(val.value, val.label);
        }
        if (this.props.api.name == "Uso de CFDI") {
            //si cambia de unidad de negocio actualizar variables 
            //console.log("Uso de CFDI Sel: "+val.value+" "+val.label);
            this.props.seleccionarUsoCFDI(val.value, val.label);
        }
        if (this.props.api.name == "Metodo de Pago") {
            //si cambia de unidad de negocio actualizar variables 
            //console.log("Metodo de Pago Sel: "+val.value+" "+val.label);
            this.props.seleccionarMetodoPago(val.value, val.label);
        }
    },

	render: function() {

		const _options = this.state.options;

		const tooltipText = (
			<Tooltip id='tooltip'>{this.props.toolTipText}</Tooltip>
		);

        var button_label = (this.state.label == '' ? 'Seleccionar ' + this.props.api.name : this.state.label);

		return (
			<div>
				<Collapse in={this.state.seleccionado}>
					<div>
	                    <OverlayTrigger placement="top" overlay={tooltipText}>
	                        <button type="button" className="btn btn-default btn-sm info-button" onClick={this.cambiar} ><i aria-hidden="true" className="fa fa-search"></i> {this.props.selectLabel}</button>
	                    </OverlayTrigger>
                    </div>
                </Collapse>
                <Collapse in={this.state.cambiar}>
                	<div>
                		<Select
                			searchable={true}
                			clearable={false}
						    name="form-field-name"
							value={this.state.value}
						    options={_options}
						    onChange={this.setValue}
						/>
                	</div>
                </Collapse>
			</div>
		);
	}

});

module.exports = ComboGeneral;