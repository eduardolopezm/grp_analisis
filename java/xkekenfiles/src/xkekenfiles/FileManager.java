/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package xkekenfiles;

import java.io.FileReader;
import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.IOException;

/**
 *
 * @author rubenmeza
 */
public class FileManager {
    
    private final File file;
    private FileReader filereader;
    private BufferedReader bufferreader;
    
    public FileManager(String ruta) {
        this.file = new File(ruta);
    }

    public File getFile() {
        return this.file;
    }
    
    public void openFile() throws FileNotFoundException {
        this.filereader = new FileReader(this.getFile());
        this.bufferreader = new BufferedReader(this.filereader);
    }
    
    public void closeFile() throws IOException {
        this.bufferreader.close();
        this.filereader.close();
    }
    
    public String readFile() throws IOException {
        String line = null;
        if ( (line = this.bufferreader.readLine()) != null ) {
            return line;
        }
        else {
            return "-1";
        }
    }
    
    public void moveFile(String destino) {
        this.file.renameTo(new File(destino + this.file.getName()));
    }
    
    // Posibilidad de crear una funcion para el proceso
    public void listFilesForFolder(final File folder) {
        
    }
    
}
