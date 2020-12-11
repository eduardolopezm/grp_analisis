
import React from 'react';
import Select from 'react-select';
import axios from 'axios';

var MultiSelectField = React.createClass({

	getInitialState: function () {
		return {
			disabled: false,
			crazy: false,
			options: [],
			value: [],
		};
	},

	componentDidMount: function() {
		var self = this;
 
        axios.get('./../mvc/api/v1/paymentmethods')
        .then(function (response) {
            if(response.data.success) {
                var i;
                var arrayObj = response.data.data;
                for(i = 0; i < arrayObj.length; i++){
                    arrayObj[i].value = arrayObj[i]['paymentid'];
                    arrayObj[i].label = arrayObj[i]['paymentname'];
                    //delete arrayObj[i].tagref;
                    //delete arrayObj[i].tagdescriptionc;
                }

                self.setState({
                    options: arrayObj
                });
            } else {
                var options = [{
                    value: '01',
                    label: 'Error al cargar Forma de Pago'
                }];
                self.setState({
                    options: options
                });
            }
        })
        .catch(function (error) {
            console.log(error);
        });
	},

	handleSelectChange (value) {
		console.log('Metodos multi-select:', value);
		this.setState({ value });
		var name = "";

		var codes = value.split(",");

		var obj = this.state.options.filter(function(element, index, array) {
            if(element.paymentid === codes[0]) {
                name = element.paymentname;
            }
            return element;
        });
		

		this.props.seleccionarMetodosPago(value, name);
	},

	render () {
		return (
			<div className="section">
				<Select multi simpleValue value={this.state.value} placeholder="Seleccionar Forma de Pago" options={this.state.options} onChange={this.handleSelectChange} />
			</div>
		);
	}
});

module.exports = MultiSelectField;

//opciones para el select
/*
const WHY_WOULD_YOU = [
	{ label: 'Chocolate (are you crazy?)', value: 'chocolate', disabled: true },
].concat(FLAVOURS.slice(1));

toggleDisabled (e) {
	this.setState({ disabled: e.target.checked });
},

toggleChocolate (e) {
	let crazy = e.target.checked;
	this.setState({
		crazy: crazy,
		options: crazy ? WHY_WOULD_YOU : FLAVOURS,
	});
},
<div className="section">
	<h3 className="section-heading">{this.props.label}</h3>
	<Select multi simpleValue disabled={this.state.disabled} value={this.state.value} placeholder="Select your favourite(s)" options={this.state.options} onChange={this.handleSelectChange} />
</div>
<div className="checkbox-list">
	<label className="checkbox">
		<input type="checkbox" className="checkbox-control" checked={this.state.disabled} onChange={this.toggleDisabled} />
		<span className="checkbox-label">Disable the control</span>
	</label>
	<label className="checkbox">
		<input type="checkbox" className="checkbox-control" checked={this.state.crazy} onChange={this.toggleChocolate} />
		<span className="checkbox-label">I don't like Chocolate (disabled the option)</span>
	</label>
</div>
*/