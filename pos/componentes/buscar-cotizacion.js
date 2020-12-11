import Autosuggest from 'react-autosuggest';
import axios from 'axios';
var React = require('react');

const getSuggestionValue = suggestion => suggestion.vacio; //seleccionar cotizacion no visualiza datos

const renderSuggestion = suggestion => (
    <div>
        {suggestion.orderno + " " + suggestion.debtorno + " - " + suggestion.namedebtor + " " + suggestion.orddate}
    </div>
);

class BuscarCotizaciones extends React.Component {

    constructor() {
        super();

        this.state = {
            value: '',
            suggestions: []
        };
    };

    onChange = (event, { newValue }) => {
        this.setState({
            value: newValue
        });

        // console.log("onChange");
        // console.log("newValue: "+newValue);
        this.props.buscarCotizacionValor(newValue);
    };

    onSuggestionsFetchRequested = ({ value }) => {
        
        var self = this;

        //console.log("value: "+value);
        this.props.buscarCotizacionValor(value);

        if(value.length > 3 && 1 == 2) {
            // No realizar busqueda, solo por boton
            this.props.buscarCotizacionLista();
        }

        if(value.length > 3 && 1 == 2) {
            //obtener si existe sesion, si no tiene sesion termina el proceso
            //this.props.parent.state.noCotizacionBuscar = 0;
            self.props.obtenerSession();

            //obtener cotizaciones
            axios.get('./../mvc/api/v1/getquotationbydesc/' + value)
            .then(function (response) {
                if(response.data.success) {
                    self.setState({
                        suggestions: response.data.data
                    });
                } else {
                    self.setState({
                        suggestions: [
                            {
                                orderno: '',
                                namedebtor: 'No existe '+self.props.nomCotizacionVenta+' con esos parametros.',
                                orddate: ''
                            }
                        ]
                    });
                }
            })
            .catch(function (error) {
                // console.log(error);
            });
        }
    };

    onSuggestionSelected = (event, { suggestion, suggestionValue, sectionIndex, method }) => {
        this.props.seleccionarCotizaciones(suggestion);
    }

    onSuggestionsClearRequested = () => {
        this.setState({
            suggestions: []
        });
    };

    render() {

        const { value, suggestions } = this.props.parent.state.datosCotBus;

        const inputProps = {
            placeholder: 'Escribe el número de '+this.props.nomCotizacionVenta+' a cargar',
            value,
            onChange: this.onChange,
            className: 'form-control',
            id: 'txtBusqedaCotizacion', // No cambiar nombre afecta focus de buscarCotizacionLista
            name: 'txtBusqedaCotizacion' // No cambiar nombre afecta focus de buscarCotizacionLista
        };

        return (
            <Autosuggest
                suggestions={suggestions}
                onSuggestionsFetchRequested={this.onSuggestionsFetchRequested}
                onSuggestionsClearRequested={this.onSuggestionsClearRequested}
                onSuggestionSelected={this.onSuggestionSelected}
                getSuggestionValue={getSuggestionValue}
                renderSuggestion={renderSuggestion}
                inputProps={inputProps}
            />
        );
    }
}

module.exports = BuscarCotizaciones


/*
this.nameInput.focus();

<input 
ref={(input) => { this.nameInput = input; }} 
defaultValue="will focus"
/>
 */










