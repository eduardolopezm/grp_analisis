/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package xkekenfiles;

import java.io.File;
import java.io.IOException;
import java.io.UnsupportedEncodingException;
import java.net.URISyntaxException;
import java.net.URL;
import java.net.URLDecoder;
import java.nio.file.Files;
import java.nio.file.attribute.BasicFileAttributes;
import java.text.DateFormat;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.Locale;
import java.util.TimeZone;

/**
 *
 * @author rubenmeza
 */
public class Xkekenfiles {
    
    public static Date convertStringToDate(String dateString) throws ParseException
    {   
        String OLD_FORMAT = "yyyyMMdd'T'HH:mm:ss";
        String NEW_FORMAT = "yyyy-MM-dd HH:mm:ss";

        DateFormat formatter = new SimpleDateFormat(OLD_FORMAT);
        Date d = formatter.parse(dateString);
        ((SimpleDateFormat) formatter).applyPattern(NEW_FORMAT);
        return formatter.parse(formatter.format(d));
    }
    
    private static Date convertTimestampToDate(long input) throws ParseException{
        Date date = new Date(input);
        Calendar cal = new GregorianCalendar(TimeZone.getTimeZone("GMT+6"));
        SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd hh:mm:ss");
        sdf.setCalendar(cal);
        cal.setTime(date);
        return sdf.parse(sdf.format(date));
    }
    
    /**
     * @param args the command line arguments
     * @throws java.io.IOException
     * @throws java.net.URISyntaxException
     */
    public static void main(String[] args) throws IOException, URISyntaxException {
        
        Config config = null;
        
        if(args.length == 3) {
            config = new Config(args[0], args[1], args[2]);
        }
        else {
            URL url = Xkekenfiles.class.getProtectionDomain().getCodeSource().getLocation();
            String jarPath = null;
            try {
                jarPath = URLDecoder.decode(url.getFile(), "UTF-8");
            } catch (UnsupportedEncodingException e) {
                System.out.println(e);
            }

            String parentPath = new File(jarPath).getParentFile().getPath();
            parentPath = parentPath + File.separator;
       
            FileManager mconf = new FileManager(parentPath + "/configuracion.conf");
            mconf.openFile();
            String conf[] = new String[4];
            String linea = null;
            int i = 0;
            while( !"-1".equals(linea = mconf.readFile())  ) {
                conf[i] = linea;
                i++;
            }
            mconf.closeFile();
            config = new Config(conf[0], conf[1], conf[2]);
        }
        
        try {
            Date date1 = null;
            Date date2 = null;
            DateFormat df = DateFormat.getDateInstance();
            if(config.validaRutas()) {
                FileManager manager = new FileManager(config.getArchivo_control());
                manager.openFile();
                String linea = null;
                while(!"-1".equals(linea = manager.readFile())) {
                    if(linea.matches("([0-9]*[a-zA-Z]*)+,\\d{8}T\\d{2}:\\d{2}:\\d{2}")) {
                        String[] parts = linea.split(",");
                        Date cdate = convertStringToDate(parts[1]);
                        date1 = date2;
                        date2 = cdate;
                        if(date1 != null) {
                            File folderorigen = new File(config.getRuta_carpeta_origen());
                            for (final File fileEntry : folderorigen.listFiles()) {
                                if (!fileEntry.isDirectory()) {
                                    long timestamp = fileEntry.lastModified();
                                    Date fileDate = convertTimestampToDate(timestamp);
                                    System.out.println("Archivo: " + fileEntry.getName());
                                    if( (fileDate.after(date1) && fileDate.before(date2)) ) {
                                        FileManager filetomove = new FileManager(fileEntry.getPath());
                                        filetomove.moveFile(config.getRuta_carpeta_destino() + "/" + parts[0] + "_");
                                    }
                                }
                            }
                        }
                    }
                    else {
                        System.out.println("Formato de la linea invalido.");
                        System.out.println("Se recibio: " + linea);
                        System.out.println("Se espera (regExp): ([0-9]*[a-zA-Z]*)*,\\d{8}T\\d{2}:\\d{2}:\\d{2} ");
                        System.out.println("Ejemplos:");
                        System.out.println("99999,20150514T13:49:00");
                        System.out.println("abc88888,20150514T13:50:00");
                        System.out.println("abc88888AVB,20150514T13:50:00");
                        System.out.println("HO88888AVB,20150514T13:50:00");
                        System.out.println("88888AVB,20150514T13:50:00");
                    }
                }
            }
            else {
                System.out.println("Alguna de las rutas no es valida.");
            }
        }
        catch (IOException | ParseException e) {
            System.out.println(e);
        }
    }
    
}
