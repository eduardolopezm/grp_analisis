package com.jahepi.activemq.database;

import java.sql.PreparedStatement;
import java.sql.SQLException;

import org.apache.log4j.Logger;

import com.jahepi.activemq.Utils;
import com.jahepi.activemq.database.Database.DBPreparedStatement;
import com.jahepi.activemq.dto.QueueMessageAdjustment;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ConsumerAdjustmentDBHelper {
	
	final static Logger logger = Logger.getLogger(ConsumerAdjustmentDBHelper.class);
	
	private Database database;
	private ConfigData config;
	public ConsumerAdjustmentDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}
	
	public boolean saveMessage(QueueMessageAdjustment msg) {
		String sql = "", sqlLog = "", orden = "", masivo = "";
		String finalLogSql = "";
		DBPreparedStatement dbPreparedStatement;
		PreparedStatement ps;
		boolean success = true;
		
		switch(this.config.get("type")) {
			case "satelite":
				sql = "";
				sqlLog = "";
				break;
			case "central":
				sql = "INSERT INTO `inventoryadjustmentorders` ("
						+ "`descripcion`, "
						+ "`stockid`, "
						+ "`loccode`, "
						+ "`origtrandate`, "
						+ "`trandate`, "
						+ "`fechaconsumo`, "
						+ "`narrative`, "
						+ "`qty`, "
						+ "`quotation`, "
						+ "`url`, "
						+ "`userregister`, "
						+ "`userprocess`, "
						+ "`userauthorized`, "
						+ "`type`, "
						+ "`reasonid`, "
						+ "`service`, "
						+ "`factor`, "
						+ "`massiveadjustment`) "
						+ "VALUES ("
						+ "?, "
						+ "?, "
						+ "?, "
						+ "?, "
						+ "?, "
						+ "?, "
						+ "?, "
						+ "?, "
						+ "?, "
						+ "?, "
						+ "?, "
						+ "?, "
						+ "?, "
						+ "?, "
						+ "?, "
						+ "?, "
						+ "?, "
						+ "?)";
				sqlLog = "INSERT INTO `inventoryadjustmentorders` ("
						+ "`descripcion`, "
						+ "`stockid`, "
						+ "`loccode`, "
						+ "`origtrandate`, "
						+ "`trandate`, "
						+ "`fechaconsumo`, "
						+ "`narrative`, "
						+ "`qty`, "
						+ "`quotation`, "
						+ "`url`, "
						+ "`userregister`, "
						+ "`userprocess`, "
						+ "`userauthorized`, "
						+ "`type`, "
						+ "`reasonid`, "
						+ "`service`, "
						+ "`factor`, "
						+ "`massiveadjustment`) "
						+ "VALUES ("
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s')";
				break;
		}
		
		
		dbPreparedStatement = this.database.getPreparedStatement(sql);
		ps = dbPreparedStatement.getPreparedStatement();
		if (ps != null) {
			try {
				
				switch(this.config.get("type")) {
					case "satelite":
						
						break;
					case "central":
						ps.setString(1, msg.getDescripcion());
						ps.setString(2, msg.getStockid());
						ps.setString(3, msg.getLoccode());
						ps.setString(4, msg.getOrigtrandate());
						ps.setString(5, msg.getTrandate());
						ps.setString(6, msg.getFechaconsumo());
						ps.setString(7, msg.getNarrative());
						ps.setString(8, msg.getQty());
						ps.setString(9, msg.getQuotation());
						ps.setString(10, msg.getUrl());
						ps.setString(11, msg.getUserregister());
						ps.setString(12, msg.getUserprocess());
						ps.setString(13, msg.getUserauthorized());
						ps.setString(14, msg.getType());
						ps.setString(15, msg.getReasonid());
						ps.setString(16, msg.getService());
						ps.setString(17, msg.getFactor());
						ps.setString(18, msg.getMassiveadjustment());
						
						
						
						finalLogSql = String.format(
							sqlLog,
							msg.getDescripcion(),
							msg.getStockid(),
							msg.getLoccode(),
							msg.getOrigtrandate(),
							msg.getTrandate(),
							msg.getFechaconsumo(),
							msg.getNarrative(),
							msg.getQty(),
							msg.getQuotation(),
							msg.getUrl(),
							msg.getUserregister(),
							msg.getUserprocess(),
							msg.getUserauthorized(),
							msg.getType(),
							msg.getReasonid(),
							msg.getService(),
							msg.getFactor(),
							msg.getMassiveadjustment()
						);
						break;
				}				

				
				logger.debug(finalLogSql);

				ps.executeUpdate();
				
				orden = msg.getOrderno();
				masivo = msg.getMassiveadjustment();
				
				success = true;

			} catch (SQLException e) {

				success = false;
				if (this.config.get("onErrorSaveFile").equals("1")) {
					success = Utils.saveFile(config, finalLogSql, "ajustemasivo", masivo, orden);
				}
				
				logger.error("Error de base de datos", e);
			} finally {
				dbPreparedStatement.close();
			}
		}

		return success;
	}
	
	public void disconnect() {
		if(this.database.isConnected()) {
			this.database.disconnect();
		}
	}
	
}
