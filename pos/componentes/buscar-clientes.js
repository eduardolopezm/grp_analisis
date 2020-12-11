import Autosuggest from 'react-autosuggest';
import axios from 'axios';
var React = require('react');

const getSuggestionValue = suggestion => suggestion.name;

const renderSuggestion = suggestion => (
    <div>
        {suggestion.branchcode + " - " + suggestion.name}
    </div>
);

class BuscarClientes extends React.Component {
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
        this.props.buscarClienteValor(newValue);
    };

    onSuggestionsFetchRequested = ({ value }) => {
        
        var self = this;

        this.props.buscarClienteValor(value);

        if(value.length > 3 && 1 == 2) {
            //obtener si existe sesion, si no tiene sesion termina el proceso
            self.props.obtenerSession();
            
            axios.get('./../mvc/api/v1/getdebtors/' + value)
            .then(function (response) {
                if(response.data.success) {
                    self.setState({
                        suggestions: response.data.data
                    });        
                } else {
                    self.setState({
                        suggestions: [
                            {
                                debtorno: '',
                                name: 'No existen '+self.props.nomClienteVenta+'s con esos parametros.'
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
        this.props.seleccionarCliente(suggestion);
    }

    onSuggestionsClearRequested = () => {
        this.setState({
            suggestions: []
        });
    };

    render() {

        // const { value, suggestions } = this.state;
        const { value, suggestions } = this.props.parent.state.datosCliBus;

        const inputProps = {
            placeholder: 'Busca por Código o Nombre',
            value,
            onChange: this.onChange,
            className: 'form-control',
            id: 'txtBusqedaCliente', // No cambiar nombre afecta focus de buscarClienteLista
            name: 'txtBusqedaCliente' // No cambiar nombre afecta focus de buscarClienteLista
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

module.exports = BuscarClientes