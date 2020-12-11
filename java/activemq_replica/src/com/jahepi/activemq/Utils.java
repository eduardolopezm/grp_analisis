package com.jahepi.activemq;

import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.io.PrintWriter;
import java.io.UnsupportedEncodingException;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.EmptyStackException;

import org.apache.log4j.Logger;

import com.jahepi.activemq.database.Database;
import com.jahepi.activemq.database.Database.DBPreparedStatement;
import com.jahepi.activemq.database.Database.DBResultSet;
import com.jahepi.activemq.loader.Config.ConfigData;

import sun.misc.BASE64Decoder;
import sun.misc.BASE64Encoder;

public class Utils {
	
	final static Logger logger = Logger.getLogger(Utils.class);
	
   public static final String DEFAULT_ENCODING="UTF-8"; 
   static BASE64Encoder enc=new BASE64Encoder();
   static BASE64Decoder dec=new BASE64Decoder();

   public static String base64encode(String text){
      try {
         String rez = enc.encode( text.getBytes( DEFAULT_ENCODING ) );
         return rez;         
      }
      catch ( UnsupportedEncodingException e ) {
    	  logger.error("Error de codificacion", e);
         return null;
      }
   }

   public static String base64decode(String text){

         try {
            return new String(dec.decodeBuffer( text ),DEFAULT_ENCODING);
         }
         catch ( IOException e ) {
        	 logger.error("Error Entrada Salida", e);
        	 return null;
         }

    }

	public static String getXMLPath(ConfigData config) {
		String path = config.get("xmlSavePath");
		SimpleDateFormat df = new SimpleDateFormat("dd-MM-yyyy");
		Date dateobj = new Date();
		String folder = df.format(dateobj);
		path = path + folder + File.separator;
		return path;
	}
	
	
	public static boolean saveFile(ConfigData config, String message, String type, String registro, String partida) {
		try {
			String path  =  config.get("msgtxtPath");
			SimpleDateFormat df = new SimpleDateFormat("dd-MM-yyyy");
			Date dateobj = new Date();
			String name = config.get("unidad") + "_" + type + "_" + registro + "_" + partida + "_" +  df.format(dateobj) + ".txt";
			df = new SimpleDateFormat("dd-MM-yyyy HH:mm:ss");
			// String date = "[" + df.format(dateobj) + "] > ";
			PrintWriter out = new PrintWriter(new BufferedWriter(
					new FileWriter(path + name, true)));
			String encodedmsg = base64encode(message);
			out.println( encodedmsg + System.getProperty("line.separator"));
			out.close();
			
			return true;
		} catch (IOException e) {
			logger.error("Error Entrada Salida", e);
			return false;
		}
	}

	public static int convertToInt(String str) {
		int n = 0;
		try {
			n = Integer.parseInt(str);
		} catch (NumberFormatException e) {
			e.printStackTrace();
		}
		return n;
	}
	
	public static void setReplica(ConfigData config, Database database, String process) {
		
		String sql = "";
		String sqllog = "";
		String finalLogSql = "";
		int rowCount = 0;
		
		
		sql = "SELECT count(*) FROM tablero_replica WHERE AppId = '" + config.get("appid") + "'";
		DBResultSet result = database.executeQuery(sql);
		ResultSet rs = result.getResultSet();
		try {
			
			rs.next();
		    rowCount = rs.getInt(1);
		    // System.out.println(rowCount);
		    
		} catch (Exception e) {
			logger.error("Error desconocido", e);
		}
		try {
			if(rowCount == 0) {
				
				sql = "INSERT INTO tablero_replica (AppId, SiteId, SiteName, Seq_IN, Date_IN, Delay_IN, Seq_OUT, Date_OUT, Delay_OUT) VALUES (?, ?, ?, 1, now(), '00:00:00', 1, now(), '00:00:00')";
				sqllog = "INSERT INTO tablero_replica (AppId, SiteId, SiteName, Seq_IN, Date_IN, Delay_IN, Seq_OUT, Date_OUT, Delay_OUT) VALUES ('%s', '%s', '%s', 1, now(), '00:00:00', 1, now(), '00:00:00')";
				finalLogSql = String.format(sqllog,
						config.get("appid"), config.get("siteid"), config.get("sitename"));
				
				DBPreparedStatement dbPreparedStatement = database.getPreparedStatement(sql);
				PreparedStatement ps = dbPreparedStatement.getPreparedStatement();
				if (ps != null) {
					ps.setString(1, config.get("appid"));
					ps.setString(2, config.get("siteid"));
					ps.setString(3, config.get("sitename"));
				}
				
				ps.executeUpdate();
				
				dbPreparedStatement.close();
				
				
				logger.debug(finalLogSql);

			
			}
			else {
				
				// Se agrega ya que la nueva version de java no permite usar String como parametro de un switch
				// Esto solo para evitar cambiar todas las llamadas existentes a esta funcion
				int opcion = 0;
				if(process.equals("consumer")) {
					opcion = 1;
				}
				
				if(process.equals("producer")) {
					opcion = 2;
				}
				
				switch(opcion) {
					case 1:
						sql = "UPDATE tablero_replica SET Seq_IN = Seq_IN + 1, Delay_IN = timediff(now(), Date_IN), Date_IN = now() WHERE AppId = ?";
						sqllog = "UPDATE tablero_replica SET Seq_IN = Seq_IN + 1, Delay_IN = timediff(now(), Date_IN), Date_IN = now() WHERE WHERE AppId = '%s'";
						break;
					case 2:
						sql = "UPDATE tablero_replica SET Seq_OUT = Seq_OUT + 1, Delay_OUT = timediff(now(), Date_OUT), Date_OUT = now() WHERE AppId = ?";
						sqllog = "UPDATE tablero_replica SET Seq_OUT = Seq_OUT + 1, Delay_OUT = timediff(now(), Date_OUT), Date_OUT = now() WHERE AppId = '%s'";
						break;
					default: 
						throw new EmptyStackException();
				}
				
				DBPreparedStatement dbPreparedStatement = database.getPreparedStatement(sql);
				PreparedStatement ps = dbPreparedStatement.getPreparedStatement();
				if (ps != null) {
					ps.setString(1, config.get("appid"));
				}
				
				finalLogSql = String.format(sqllog, config.get("appid"));
				
				ps.executeUpdate();
				
				dbPreparedStatement.close();
				
				logger.debug(finalLogSql);

				
			}
		}
		catch (Exception e) {
			logger.error("Error desconocido", e);
		}
	}
}
