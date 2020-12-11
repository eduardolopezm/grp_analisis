package com.jahepi.activemq.database;

import java.sql.PreparedStatement;
import java.sql.ResultSet;

import org.apache.log4j.Logger;

import com.jahepi.activemq.database.Database.DBPreparedStatement;
import com.jahepi.activemq.database.Database.DBResultSet;
import com.jahepi.activemq.dto.QueueMessageAMQStatus;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ConsumerConnStatusDBHelper {
	
	final static Logger logger = Logger.getLogger(ConsumerConnStatusDBHelper.class);
	
	private Database database;
	private ConfigData config;
	private DBPreparedStatement dbPreparedStatement;

	public ConsumerConnStatusDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}
	
	public boolean saveMessage(QueueMessageAMQStatus msg) {
		String sqlstatus = "", sql = "", sqlLog = "";
		String finalLogSql = "";
		int activemqstatus = 0;
		PreparedStatement ps;
		boolean success = true;
		try {
			
			if(this.config.get("type").equals("central")) {
				sql = "UPDATE activemqstatus SET status = 0, message = ?, msgok = 1, msgreceivedat = NOW(), lastupdate = NOW() WHERE tagref = ?";
				sqlLog = "UPDATE activemqstatus SET status = 0, message = '%s', msgok = 1, msgreceivedat = NOW(), lastupdate = NOW() WHERE tagref = '%s'";
			} else if(this.config.get("type").equals("satelite")) {
				
				sqlstatus = "SELECT count(*) as ok FROM activemqstatus WHERE tagref = '" + this.config.get("unidad") + "' AND message = '" + msg.getMessage() + "'";
				DBResultSet result = this.database.executeQuery(sqlstatus);
				ResultSet rs = result.getResultSet();
				
				if(rs.next()) {
					do {
						activemqstatus  = rs.getInt("ok");
					} while (rs.next());
				}
				sql = "UPDATE activemqstatus SET status = 0, message = ?, msgok = " + activemqstatus + ", msgreceivedat = NOW(), lastupdate = NOW() WHERE tagref = ?";
				sqlLog = "UPDATE activemqstatus SET status = 0, message = '%s', msgok = " + activemqstatus + ", msgreceivedat = NOW(), lastupdate = NOW() WHERE tagref = '%s'";
			}
			
			
			
			dbPreparedStatement = this.database.getPreparedStatement(sql);
			ps = dbPreparedStatement.getPreparedStatement();
			if (ps != null) {

				ps.setInt(1, msg.getMessage());
				ps.setString(2, msg.getTagref());
				finalLogSql = String.format(
						sqlLog,
						msg.getMessage(),
						msg.getTagref()
				);
				
				logger.debug(finalLogSql);

				ps.executeUpdate();
				success = true;

			}
		}catch (Exception e) {
			success = false;

			logger.error("Error Desconocido", e);
		} finally {
			this.dbPreparedStatement.close();
		}


		return success;
	}
	

	public void disconnect() {
		if(this.database.isConnected()) {
			this.database.disconnect();
		}
	}
}
