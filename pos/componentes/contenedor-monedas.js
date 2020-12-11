var React = require('react');

import axios from 'axios';
import update from 'react-addons-update';
import Moneda from './moneda';

const components = [
	{
		"currencydenomination": "5 Centavos",
		"value": "0.05",
		"image": "Moneda_00_05.jpg",
	}, 
];

var ContenedorMonedas = React.createClass({

	// propTypes: {
	// 	propiedad: React.PropTypes.string
	// },

	// getDefaultProps: function() {
	// 	return {
	// 		propiedad: ''
	// 	};
	// },

	getInitialState: function() {

		return {
			monedas: [],
			amount: []
		};
	},

	componentDidMount: function() { 

		var self = this;

        axios.get('./../mvc/api/v1/getcurrencydenominatios')
        .then(function (response) {

            if(response.data.success) {
                self.setState({
                    monedas: response.data.data
                });
                console.log("Monedas: "+response.data.data);    
                self.props.parent.setState({
		            currencydenominations: response.data.data
		        }); 
            }
        })
        .catch(function (error) {
            // console.log(error); 
        });
	},	

	getPaymentByCurr: function (quantity, value, currencydenomination) {

		var payment = 0;
		// Es pago con tarjeta
		if(Number(value) == 0 ) {

			var p = Number(this.props.totalAPagar) != Number(this.calcPayment()) ? Number(this.calcPayment()) : 0;
			payment = Number(quantity) * ( Number(this.props.totalAPagar) - Number(p) ) ;
		} else {
			payment = Number(quantity) * Number(value);
		}

		var exist = -1;

        var obj = this.state.amount.filter(function(element, index, array) {
            if(element.currencydenomination === currencydenomination) {
                exist = index;
            }
            return element;
        });

		if(exist >= 0) {

            var updatedCurr = update(this.state.amount[exist], {
                quantity: {
                    $set: quantity
                },
                value: {
                    $set: value
                },
                payment: {
                    $set: payment
                },
            }); 

            var newCurrData = update(this.state.amount, {
                $splice: [[exist, 1, updatedCurr]]
            });
            this.setState({
                amount: newCurrData
            }, function() {
            	this.props.getPayment(this.calcPayment());
            });

        } else {

            var objeto = {
            	currencydenomination:currencydenomination,
            	quantity:quantity,
				value:value,
				payment: payment
            }
            
            this.setState({
                amount: update(this.state.amount, {
                    $push: [objeto]
                })
            }, function() {
            	this.props.getPayment(this.calcPayment());
            });
        }
	},

	calcPayment: function() {

		var total = 0;
		for (var amt in this.state.amount) {
            if(this.state.amount.hasOwnProperty(amt)) {
            	console.log(this.state.amount[amt]);
            	total = total + Number(this.state.amount[amt].payment);
            }
        }

       	return total;
	},

	getElementCount: function () {
		return this.state.monedas.length;
	},

	getBootstrapColumns: function () {
		// maximo numero de columnas 5
		// maximo numero de filas 3
		// 1 = 12
		// 2 = 6
		// 3 = 4
		// 4 = 3
		// 5 = 2 con offset 1
		// 
		var col_class = {
			first: 'col-md-2 col-md-offset-1',
			next: 'col-md-2'
		};
		var elements = this.getElementCount();
		var rows = Math.ceil( (elements / 5) );

		if(elements  < 5) {
			switch(elements) {
				case 4:
					col_class = {
						first: 'col-md-3',
						next: 'col-md-3'
					};
					break;
				case 3:
					col_class = {
						first: 'col-md-4',
						next: 'col-md-4'
					};
					break;
				case 2:
					col_class = {
						first: 'col-md-6',
						next: 'col-md-6'
					};
					break;
				case 1:
					col_class = {
						first: 'col-md-12',
						next: 'col-md-12'
					};
					break;
			}
		}

		var i = 1;
		var c_col = 0;
		var c_class = '';
		var btscol = [];
		var btscomp = [];
		for (var comp in this.state.monedas) {
            if(this.state.monedas.hasOwnProperty(comp)) {

            	if(c_col % 5 == 0 && i > 1) {
            		btscomp.push(<div key={i} className="row">{btscol}</div>);
            		btscol = [];
            		c_col = 0;
            	}

            	if(c_col == 0) {
            		c_class = col_class.first;
            	} else {
            		c_class = col_class.next;
            	}

            	btscol.push(<div className={c_class}><Moneda curr={this.state.monedas[comp]} getPaymentByCurr={this.getPaymentByCurr} /></div>);

            	if(i == elements) {
            		btscomp.push(<div key={i} className="row">{btscol}</div>);
            	}

            	c_col++;
            	i++;

            }
        }

        return btscomp;
	},

	render: function() {
		
		var number_components = this.getBootstrapColumns();

		return (
			<div>
				{number_components}
			</div>
		);
	}

});

module.exports = ContenedorMonedas;