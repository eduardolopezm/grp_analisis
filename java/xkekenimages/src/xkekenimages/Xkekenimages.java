/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package xkekenimages;

import com.drew.imaging.ImageMetadataReader;
import com.drew.imaging.ImageProcessingException;
import com.drew.metadata.Metadata;
import com.drew.metadata.exif.ExifSubIFDDescriptor;
import com.drew.metadata.exif.ExifSubIFDDirectory;

import java.io.File;
import java.io.IOException;
/**
 *
 * @author rubenmeza
 */
public class Xkekenimages {

    /**
     * @param args the command line arguments
     */
    public static void main(String[] args) {
        Config config = null;
        
        if(args.length == 2) {
            config = new Config(args[0], args[1]);
        }
        else {
            System.out.println("Este programa necesita dos parametros para ser ejecutado.");
            System.out.println("Ej. java -jar xkekenimages.jar '/ruta/carpeta/in' '/ruta/carpeta/out'");
            System.exit(-1);
        }
        
        if(config.validaRutas()) {
            File folderorigen = new File(config.getRuta_carpeta_origen());
            for (final File fileEntry : folderorigen.listFiles()) {
                if (!fileEntry.isDirectory()) {
                    try {
                        
                        Metadata metadata = ImageMetadataReader.readMetadata(fileEntry);
                        ExifSubIFDDirectory exifDirectory = metadata.getFirstDirectoryOfType(ExifSubIFDDirectory.class);
                        ExifSubIFDDescriptor descriptor = new ExifSubIFDDescriptor(exifDirectory);
                        String comment = descriptor.getUserCommentDescription();
                        if(!comment.isEmpty()) {
                            String newdestino = config.getRuta_carpeta_destino() + comment + "_" + fileEntry.getName();
                            boolean success = fileEntry.renameTo(new File(newdestino));
                            if(success) {
                                System.out.println("El archivo: " + fileEntry.getName() + " se movio con el nombre: " + newdestino);
                            }
                            else {
                                System.out.println("El archivo: " + fileEntry.getName() + " no se pudo renombrar verifica tus permisos.");
                            }
                        }
                    } catch (ImageProcessingException e) {
                        System.out.println("El formato del archivo: " + fileEntry.getName() + " no es soportado.");
                    } catch (IOException e) {
                        System.out.println("El archivo: " + fileEntry.getName() + " esta corrupto no puede ser leido.");
                    } catch (NullPointerException e) {
                        System.out.println("El archivo: " + fileEntry.getName() + " no contiene el metadato User Comment.");
                    }
                }
            }
        }
        else {
            System.out.println("Alguna de las rutas pasadas en los parametros es invalida o no existe.");
            System.exit(-1);
        }
    }
    
}
