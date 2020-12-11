package com.jahepi.activemq.database;

import java.sql.PreparedStatement;
import java.sql.SQLException;

import org.apache.log4j.Logger;

import com.jahepi.activemq.Utils;
import com.jahepi.activemq.database.Database.DBPreparedStatement;
import com.jahepi.activemq.dto.QueueMessageStockMoves;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ConsumerStockmovesDBHelper {
	
	final static Logger logger = Logger.getLogger(ConsumerStockmovesDBHelper.class);
	
	private Database database;
	private ConfigData config;

	public ConsumerStockmovesDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}

	public boolean saveMessage(QueueMessageStockMoves msg) {
		String sql = "", sqlLog = "", stkmoveno = "";
		String finalLogSql = "";
		DBPreparedStatement dbPreparedStatement;
		PreparedStatement ps;
		boolean success = true;
		
		
				
		sql = "INSERT INTO `stockmoves` "
				+ "(`stkmoveno`, "
				+ "`stockid`, "
				+ "`type`, "
				+ "`transno`, "
				+ "`loccode`, "
				+ "`trandate`, "
				+ "`debtorno`, "
				+ "`branchcode`, "
				+ "`price`, "
				+ "`prd`, "
				+ "`reference`, "
				+ "`qty`, "
				+ "`discountpercent`, "
				+ "`standardcost`, "
				+ "`show_on_inv_crds`, "
				+ "`newqoh`, "
				+ "`hidemovt`, "
				+ "`narrative`, "
				+ "`warranty`, "
				+ "`tagref`, "
				+ "`discountpercent1`, "
				+ "`discountpercent2`, "
				+ "`totaldescuento`, "
				+ "`avgcost`, "
				+ "`standardcostv2`, "
				+ "`showdescription`, "
				+ "`refundpercentmv`, "
				+ "`nuevocosto`, "
				+ "`ref?`, "
				+ "`ref2`, "
				+ "`ref3`, "
				+ "`ref4`, "
				+ "`qty2`, "
				+ "`qtyinvoiced`, "
				+ "`qty_sent`, "
				+ "`ratemov`, "
				+ "`useridmov`, "
				+ "`FlagValExistencias`, "
				+ "`stkmovid`, "
				+ "`nomes`, "
				+ "`stockclie`, "
				+ "`localidad`, "
				+ "`qty_excess`, "
				+ "`secondfactorconversion`, "
				+ "`register`, "
				+ "`reasonid`, "
				+ "`serviceid`, "
				+ "`factorprimary`, "
				+ "`factorsecondary`, "
				+ "`factorthree`, "
				+ "`equivalentqty`,"
				+ "`pietablon`, "
				+ "`activemq`) "
				+ "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		
		sqlLog = "INSERT INTO `stockmoves` "
				+ "(`stkmoveno`, "
				+ "`stockid`, "
				+ "`type`, "
				+ "`transno`, "
				+ "`loccode`, "
				+ "`trandate`, "
				+ "`debtorno`, "
				+ "`branchcode`, "
				+ "`price`, "
				+ "`prd`, "
				+ "`reference`, "
				+ "`qty`, "
				+ "`discountpercent`, "
				+ "`standardcost`, "
				+ "`show_on_inv_crds`, "
				+ "`newqoh`, "
				+ "`hidemovt`, "
				+ "`narrative`, "
				+ "`warranty`, "
				+ "`tagref`, "
				+ "`discountpercent1`, "
				+ "`discountpercent2`, "
				+ "`totaldescuento`, "
				+ "`avgcost`, "
				+ "`standardcostv2`, "
				+ "`showdescription`, "
				+ "`refundpercentmv`, "
				+ "`nuevocosto`, "
				+ "`ref'%s'`, "
				+ "`ref2`, "
				+ "`ref3`, "
				+ "`ref4`, "
				+ "`qty2`, "
				+ "`qtyinvoiced`, "
				+ "`qty_sent`, "
				+ "`ratemov`, "
				+ "`useridmov`, "
				+ "`FlagValExistencias`, "
				+ "`stkmovid`, "
				+ "`nomes`, "
				+ "`stockclie`, "
				+ "`localidad`, "
				+ "`qty_excess`, "
				+ "`secondfactorconversion`, "
				+ "`register`, "
				+ "`reasonid`, "
				+ "`serviceid`, "
				+ "`factorprimary`, "
				+ "`factorsecondary`, "
				+ "`factorthree`, "
				+ "`equivalentqty`,"
				+ "`pietablon`, "
				+ "`activemq`) "
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
				+ "'%s', "
				+ "'%s')";

		
		dbPreparedStatement = this.database.getPreparedStatement(sql);
		ps = dbPreparedStatement.getPreparedStatement();
		if (ps != null) {
			try {
				
				ps.setString(1, msg.getStkmoveno());
				ps.setString(2, msg.getStockid());
				ps.setString(3, msg.getType());
				ps.setString(4, msg.getTransno());
				ps.setString(5, msg.getLoccode());
				ps.setString(6, msg.getTrandate());
				ps.setString(7, msg.getDebtorno());
				ps.setString(8, msg.getBranchcode());
				ps.setString(9, msg.getPrice());
				ps.setString(10, msg.getPrd());
				ps.setString(11, msg.getReference());
				ps.setString(12, msg.getQty());
				ps.setString(13, msg.getDiscountpercent());
				ps.setString(14, msg.getStandardcost());
				ps.setString(15, msg.getShow_on_inv_crds());
				ps.setString(16, msg.getNewqoh());
				ps.setString(17, msg.getHidemovt());
				ps.setString(18, msg.getNarrative());
				ps.setString(19, msg.getWarranty());
				ps.setString(20, msg.getTagref());
				ps.setString(21, msg.getDiscountpercent2());
				ps.setString(22, msg.getDiscountpercent2());
				ps.setString(23, msg.getTotaldescuento());
				ps.setString(24, msg.getAvgcost());
				ps.setString(25, msg.getStandardcostv2());
				ps.setString(26, msg.getShowdescription());
				ps.setString(27, msg.getRefundpercentmv());
				ps.setString(28, msg.getNuevocosto());
				ps.setString(29, msg.getRef2());
				ps.setString(30, msg.getRef2());
				ps.setString(31, msg.getRef3());
				ps.setString(32, msg.getRef4());
				ps.setString(33, msg.getQty2());
				ps.setString(34, msg.getQtyinvoiced());
				ps.setString(35, msg.getQty_sent());
				ps.setString(36, msg.getRatemov());
				ps.setString(37, msg.getUseridmov());
				ps.setString(38, msg.getFlagValExistencias());
				ps.setString(39, msg.getStkmovid());
				ps.setString(40, msg.getNomes());
				ps.setString(41, msg.getStockclie());
				ps.setString(42, msg.getLocalidad());
				ps.setString(43, msg.getQty_excess());
				ps.setString(44, msg.getSecondfactorconversion());
				ps.setString(45, msg.getRegister());
				ps.setString(46, msg.getReasonid());
				ps.setString(47, msg.getServiceid());
				ps.setString(48, msg.getFactorprimary());
				ps.setString(49, msg.getFactorsecondary());
				ps.setString(50, msg.getFactorthree());
				ps.setString(51, msg.getEquivalentqty());
				ps.setString(52, msg.getPietablon());
				ps.setString(53, msg.getActivemq());
						
				finalLogSql = String.format(
					sqlLog,
					msg.getStkmoveno(),
					msg.getStockid(),
					msg.getType(),
					msg.getTransno(),
					msg.getLoccode(),
					msg.getTrandate(),
					msg.getDebtorno(),
					msg.getBranchcode(),
					msg.getPrice(),
					msg.getPrd(),
					msg.getReference(),
					msg.getQty(),
					msg.getDiscountpercent(),
					msg.getStandardcost(),
					msg.getShow_on_inv_crds(),
					msg.getNewqoh(),
					msg.getHidemovt(),
					msg.getNarrative(),
					msg.getWarranty(),
					msg.getTagref(),
					msg.getDiscountpercent2(),
					msg.getDiscountpercent2(),
					msg.getTotaldescuento(),
					msg.getAvgcost(),
					msg.getStandardcostv2(),
					msg.getShowdescription(),
					msg.getRefundpercentmv(),
					msg.getNuevocosto(),
					msg.getRef2(),
					msg.getRef2(),
					msg.getRef3(),
					msg.getRef4(),
					msg.getQty2(),
					msg.getQtyinvoiced(),
					msg.getQty_sent(),
					msg.getRatemov(),
					msg.getUseridmov(),
					msg.getFlagValExistencias(),
					msg.getStkmovid(),
					msg.getNomes(),
					msg.getStockclie(),
					msg.getLocalidad(),
					msg.getQty_excess(),
					msg.getSecondfactorconversion(),
					msg.getRegister(),
					msg.getReasonid(),
					msg.getServiceid(),
					msg.getFactorprimary(),
					msg.getFactorsecondary(),
					msg.getFactorthree(),
					msg.getEquivalentqty(),
					msg.getPietablon(),
					msg.getActivemq()
				);
								

				
				logger.debug(finalLogSql);

				ps.executeUpdate();
				
				success = true;
				
				stkmoveno = msg.getStkmoveno();
				
			} catch (SQLException e) {

				success = false;
				if (this.config.get("onErrorSaveFile").equals("1")) {
					success = Utils.saveFile(config, finalLogSql, "stockmoves", stkmoveno, "");
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
