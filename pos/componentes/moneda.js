var React = require('react');

var Moneda = React.createClass({

	// propTypes: {
	// 	img: React.PropTypes.string
	// },

	// getDefaultProps: function() {
	// 	return {
	// 		img: ''
	// 	};
	// },

	getInitialState: function() {
		return {
			contador: 0
		};
	},

	sumar: function () {
		var cont = Number(this.state.contador) + 1;

		// Es pago con tarjeta, solo se puede colocar una
		if(Number(this.props.curr.value) == 0 && cont > 1) {
			cont = 1;
		}
		this.setState({
			contador: cont
		});
		this.props.getPaymentByCurr(cont, this.props.curr.value, this.props.curr.currencydenomination);
	},

	restar: function() {
		var cont = Number(this.state.contador) - 1;
		if(cont < 0) {
			cont = 0;
		}
		this.setState({
			contador: cont
		});
		this.props.getPaymentByCurr(cont, this.props.curr.value, this.props.curr.currencydenomination);
	},

	handleChange(event) { 
		this.setState({
			contador: event.target.value
		});
		this.props.getPaymentByCurr(event.target.value, this.props.curr.value, this.props.curr.currencydenomination);
	},

	render: function() {
		return ( 
			<div>
				<div className="thumbnail">
					<img src={'.././images/currencydenominations/' + this.props.curr.image} alt="" className="center-block img-responsive img-denominacion" />
					<div className="caption">
						<div className="input-group">
							<span className="input-group-btn">
		        				<button className="btn btn-default btn-sm" type="button" onClick={this.restar}><i className="fa fa-minus text-danger" aria-hidden="true"></i></button>
		      				</span>
		      				<input type="text" className="form-control input-sm moneda-input-contador" value={this.state.contador} onChange={this.handleChange} />
		      				<span className="input-group-btn">
		        				<button className="btn btn-default btn-sm" type="button" onClick={this.sumar}><i className="fa fa-plus text-primary" aria-hidden="true"></i></button>
		      				</span>
		    			</div>
		    		</div>
	    		</div>
			</div>
		);
	}

});

module.exports = Moneda;