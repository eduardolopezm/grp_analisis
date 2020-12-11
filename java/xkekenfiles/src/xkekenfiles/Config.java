
package xkekenfiles;

import java.io.File;

/**
 *
 * @author rubenmeza
 */
public class Config {
    private String ruta_carpeta_origen;
    private String ruta_carpeta_destino;
    private String archivo_control;

    public Config(String ruta_carpeta_origen, String ruta_carpeta_destino, String archivo_control) {
        this.ruta_carpeta_origen = ruta_carpeta_origen;
        this.ruta_carpeta_destino = ruta_carpeta_destino;
        this.archivo_control = archivo_control;
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
    
    public String getArchivo_control() {
        return archivo_control;
    }

    public void setArchivo_control(String archivo_control) {
        this.archivo_control = archivo_control;
    }
    
    public boolean validaRutas() {
        File origen = new File(this.getRuta_carpeta_origen());
        File destino = new File(this.getRuta_carpeta_destino());
        File control = new File(this.getArchivo_control());
        
        return origen.exists() && destino.exists() && control.exists();
    }
}
