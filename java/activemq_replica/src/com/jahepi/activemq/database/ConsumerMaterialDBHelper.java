package com.jahepi.activemq.database;

import java.sql.PreparedStatement;
import java.sql.SQLException;

import org.apache.log4j.Logger;

import com.jahepi.activemq.Utils;
import com.jahepi.activemq.database.Database.DBPreparedStatement;
import com.jahepi.activemq.dto.QueueMessageMaterial;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ConsumerMaterialDBHelper {
	
	final static Logger logger = Logger.getLogger(ConsumerMaterialDBHelper.class);
	
	private Database database;
	private ConfigData config;

	public ConsumerMaterialDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}

	public boolean saveMessage(QueueMessageMaterial msg) {
		String sql = "", sqlLog = "", reference = "", stockid = "";
		String finalLogSql = "";
		DBPreparedStatement dbPreparedStatement;
		PreparedStatement ps;
		boolean success = true;
		
		switch(this.config.get("type")) {
			case "satelite":
				
				sql = "UPDATE loctransfersmreceived SET "
						+ "`type` = ?, "
						+ "`reference` = ?, "
						+ "`entry` = ?, "
						+ "`stockid` = ?, "
						+ "`quantity` = ?, "
						+ "`unit` = ?, "
						+ "`deliverydate` = ?, "
						+ "`userid` = ?, "
						+ "`userregister` = ?, "
						+ "`registerdate` = ?, "
						+ "`folioconf` = ?, "
						+ "`folioconfdate` = ?, "
						+ "`erptransno` = ?, "
						+ "`iddelivery` = ?, "
						+ "`indicadorSAP` = ?, "
						+ "`movementsapplied` = ?, "
						+ "`identifieramq` = ? "
						+ "WHERE id = ?";
				
				sqlLog = "UPDATE loctransfersmreceived SET "
						+ "`type` = '%s', "
						+ "`reference` = '%s', "
						+ "`entry` = '%s', "
						+ "`stockid` = '%s', "
						+ "`quantity` = '%s', "
						+ "`unit` = '%s', "
						+ "`deliverydate` = '%s', "
						+ "`userid` = '%s', "
						+ "`userregister` = '%s', "
						+ "`registerdate` = '%s', "
						+ "`folioconf` = '%s', "
						+ "`folioconfdate` = '%s', "
						+ "`erptransno` = '%s', "
						+ "`iddelivery` = '%s', "
						+ "`indicadorSAP` = '%s', "
						+ "`movementsapplied` = '%s', "
						+ "`identifieramq` = '%s' "
						+ "WHERE id = '%s'";
				
				break;
			case "central":
				
				sql = "INSERT INTO `loctransfersmreceived` ("
						+ "`type`, "
						+ "`reference`, "
						+ "`entry`, "
						+ "`stockid`, "
						+ "`quantity`, "
						+ "`unit`, "
						+ "`deliverydate`, "
						+ "`userid`, "
						+ "`userregister`, "
						+ "`registerdate`, "
						+ "`folioconf`, "
						+ "`folioconfdate`, "
						+ "`erptransno`, "
						+ "`iddelivery`, "
						+ "`indicadorSAP`, "
						+ "`movementsapplied`, "
						+ "`identifieramq`) "
						+ "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)";
				
				sqlLog = "INSERT INTO `loctransfersmreceived` ("
						+ "`type`, "
						+ "`reference`, "
						+ "`entry`, "
						+ "`stockid`, "
						+ "`quantity`, "
						+ "`unit`, "
						+ "`deliverydate`, "
						+ "`userid`, "
						+ "`userregister`, "
						+ "`registerdate`, "
						+ "`folioconf`, "
						+ "`folioconfdate`, "
						+ "`erptransno`, "
						+ "`iddelivery`, "
						+ "`indicadorSAP`, "
						+ "`movementsapplied`, "
						+ "`identifieramq`) "
						+ "VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', 1, '%s')";
				break;
		}
		
		
		dbPreparedStatement = this.database.getPreparedStatement(sql);
		ps = dbPreparedStatement.getPreparedStatement();
		if (ps != null) {
			try {
				
				switch(this.config.get("type")) {
					case "satelite":
						
						
						ps.setString(1, msg.getType());
						ps.setString(2, msg.getReference());
						ps.setString(3, msg.getEntry());
						ps.setString(4, msg.getStockid());
						ps.setString(5, msg.getQuantity());
						ps.setString(6, msg.getUnit());
						ps.setString(7, msg.getDeliverydate());
						ps.setString(8, msg.getUserid());
						ps.setString(9, msg.getUserregister());
						ps.setString(10, msg.getRegisterdate());
						ps.setString(11, msg.getFolioconf());
						ps.setString(12, msg.getFolioconfdate());
						ps.setString(13, msg.getErptransno());
						ps.setString(14, msg.getIddelivery());
						ps.setString(15, msg.getIndicadorSAP());
						ps.setString(16, msg.getMovementtsapplied());
						ps.setString(17, msg.getIdentifieramq());
						ps.setString(18, msg.getId());
								
								
						finalLogSql = String.format(
							sqlLog,
							msg.getType(),
							msg.getReference(),
							msg.getEntry(),
							msg.getStockid(),
							msg.getQuantity(),
							msg.getUnit(),
							msg.getDeliverydate(),
							msg.getUserid(),
							msg.getUserregister(),
							msg.getRegisterdate(),
							msg.getFolioconf(),
							msg.getFolioconfdate(),
							msg.getErptransno(),
							msg.getIddelivery(),
							msg.getIndicadorSAP(),
							msg.getMovementtsapplied(),
							msg.getIdentifieramq(),
							msg.getId()
						);
						break;
					case "central":
						
						ps.setString(1, msg.getType());
						ps.setString(2, msg.getReference());
						ps.setString(3, msg.getEntry());
						ps.setString(4, msg.getStockid());
						ps.setString(5, msg.getQuantity());
						ps.setString(6, msg.getUnit());
						ps.setString(7, msg.getDeliverydate());
						ps.setString(8, msg.getUserid());
						ps.setString(9, msg.getUserregister());
						ps.setString(10, msg.getRegisterdate());
						ps.setString(11, msg.getFolioconf());
						ps.setString(12, msg.getFolioconfdate());
						ps.setString(13, msg.getErptransno());
						ps.setString(14, msg.getIddelivery());
						ps.setString(15, msg.getIndicadorSAP());
						ps.setString(16, msg.getIdentifieramq());
						
						finalLogSql = String.format(
							sqlLog,
							msg.getType(),
							msg.getReference(),
							msg.getEntry(),
							msg.getStockid(),
							msg.getQuantity(),
							msg.getUnit(),
							msg.getDeliverydate(),
							msg.getUserid(),
							msg.getUserregister(),
							msg.getRegisterdate(),
							msg.getFolioconf(),
							msg.getFolioconfdate(),
							msg.getErptransno(),
							msg.getIddelivery(),
							msg.getIndicadorSAP(),
							msg.getIdentifieramq()
						);
						break;
				}				

				
				logger.debug(finalLogSql);

				ps.executeUpdate();
				
				reference = msg.getReference();
				stockid = msg.getStockid();

				// success = this.database.executeUpdate(msg.getSql());
				success = true;

			} catch (SQLException e) {

				success = false;
				if (this.config.get("onErrorSaveFile").equals("1")) {
					success = Utils.saveFile(config, finalLogSql, "entregamaterial", reference, stockid);
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
