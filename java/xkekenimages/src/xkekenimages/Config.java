/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package xkekenimages;

import java.io.File;

/**
 *
 * @author rubenmeza
 */
public class Config {
    private String ruta_carpeta_origen;
    private String ruta_carpeta_destino;

    public Config(String ruta_carpeta_origen, String ruta_carpeta_destino) {
        this.ruta_carpeta_origen = ruta_carpeta_origen;
        this.ruta_carpeta_destino = ruta_carpeta_destino;
    }

    public String getRuta_carpeta_origen() {
        return ruta_carpeta_origen;
    }

    public void setRuta_carpeta_origen(String ruta_carpeta_origen) {
        this.ruta_carpeta_origen = ruta_carpeta_origen;
    }

    public String getRuta_carpeta_destino() {
        return ruta_carpeta_destino;
    }

    public void setRuta_carpeta_destino(String ruta_carpeta_destino) {
        this.ruta_carpeta_destino = ruta_carpeta_destino;
    }
    
    public boolean validaRutas() {
        File origen = new File(this.getRuta_carpeta_origen());
        File destino = new File(this.getRuta_carpeta_destino());
        return origen.exists() && destino.exists();
    }
}
