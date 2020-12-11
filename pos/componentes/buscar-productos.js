import Autosuggest from 'react-autosuggest';
import axios from 'axios';
var React = require('react');
////
const getSuggestionValue = suggestion => suggestion.vacio; //seleccionar cotizacion no visualiza datos

const renderSuggestion = suggestion => (
    <div>
        {suggestion.stockid + " " + suggestion.description + " - " + " " + suggestion.units + " - $ " + suggestion.priceFormat + " " }
    </div>
); // + "- $ " + suggestion.price + " "

class BuscarProductos extends React.Component {
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
    };

    onSuggestionsFetchRequested = ({ value }) => {
        
        var self = this;
        console.log("location "+self.props.location);
        if(value.length > 3) {
            if(self.props.locationname == "" || self.props.locationname == ""){
                if (self.props.mensajeUnidadAlmacen == 0) {
                    //mostrar mensaje
                    self.props.parent.setState({
                        mensajeUnidadAlmacen: 1
                    });
                    //si no selecciono unidad de negocio o almacen
                    var title = <p><i className="fa fa-exclamation-circle text-danger" aria-hidden="true"></i> Información</p>;
                    var body = "Seleccionar "+self.props.nomUniNegVenta+" y/o "+self.props.nomAlmacenVenta+" para poder seleccionar un "+self.props.nomArticuloVenta;
                    self.props.abrirAlerta(title, body);
                }
            }else{
                self.props.parent.setState({
                    mensajeUnidadAlmacen: 0
                });

                //obtener si existe sesion, si no tiene sesion termina el proceso
                self.props.obtenerSession();
                
                axios.get('./../mvc/api/v1/getproducts/' + value + '/' + self.props.tagref + '/'+ self.props.location + '/MXN/'+ self.props.typeabbrev)
                .then(function (response) {

                    if(response.data.success) {

                        if (self.props.CotizadorRapido == 0) {
                            self.setState({
                                suggestions: response.data.data
                            });
                        }else{
                            var datos = response.data.data;
                            //console.log( "Datos CotizadorRapido: " + JSON.stringify(datos) );
                            self.props.LimpiarCotizadorRapido(); //Limpiar array
                            for (var info in datos) {
                                self.props.agregarProducto(datos[info], 'productosCotizadorRapido');
                            }
                            self.props.crearTablaCotizadorRapido(); //Crear tabla a mostrar
                        }
                        
                        var datosProducto = response.data.data[0];
                        console.log("*** producto ***");
                        console.log("stockid: "+datosProducto.stockid);
                        console.log("categoryid: "+datosProducto.categoryid);
                        console.log("description: "+datosProducto.description);
                        console.log("quantity: "+datosProducto.quantity);
                        console.log("price: "+datosProducto.price);
                        console.log("available: "+datosProducto.available);
                        console.log("disminuye_ingreso: "+datosProducto.disminuye_ingreso);
                        console.log("location: "+self.props.location);
                    } else {
                        self.setState({
                            suggestions: [
                                {
                                    stockid: '',
                                    description: 'No existe '+self.props.nomArticuloVenta+' con esos parametros.'
                                }
                            ]
                        });
                    }
                })
                .catch(function (error) { 
                    // console.log(error);
                });
            }
        }
    };

    onSuggestionSelected = (event, { suggestion, suggestionValue, sectionIndex, method }) => {

        // Aqui agregar funcion para agregar nuevo producto
        this.props.agregarProducto(suggestion, 'productos');
    }

    onSuggestionsClearRequested = () => {
        this.setState({
            suggestions: []
        });
    };

    render() {

        const { value, suggestions } = this.state;

        const inputProps = {
            placeholder: 'Escanea o Busca un '+this.props.nomArticuloVenta,
            value,
            onChange: this.onChange,
            className: 'form-control',
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

module.exports = BuscarProductos