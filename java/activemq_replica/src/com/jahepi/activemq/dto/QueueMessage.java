package com.jahepi.activemq.dto;

import java.io.Serializable;

public class QueueMessage implements Serializable {

	private static final long serialVersionUID = 1;
	
	private String reference;
	private String stockid;
	private String shipqty;
	private String recqty;
	private String shipdate;
	private String recdate;
	private String shiploc;
	private String shipsecloc;
	private String recloc;
	private String recsecloc;
	private String comments;
	private String serialno;
	private String userregister;
	private String userrec;
	private String usercancel;
	private String statustransfer;
	private String qtycancel;
	private String transferline;
	private String requisitionno;
	private String debtorno;
	private String branchcode;
	private String cost;
	private String NoContratoConsigAuthCust;
	private String wo;
	private String quantity;
	private String tipoconversion;
	private String identifytransfer;
	private String quantitysend;
	private Integer idestatus;
	private Integer nextstatus;
	private String operacion;
	private String tipo;
	private String queue;
	private String comentGeneral;
	private String canceldate;
	private String requisitiondate;
	private String elaborationdate;
	private String expirationdate;
	private String freezingdate;
	private String qty_excess;
	private String type;
	private String SAPBorrado;
	private String recnowqty;
	private String enviadaSAP;
	private String doctoSAP;
	private String errordescripcion;
	private String codigoMsg;
	private String tagref;
	private String SAPcentro;
	private String SAPActualizado;
	private String qty_missing;
	private String secondfactorconversion;
	private String transferenceshipdate;
	private String idtipotransferencia;
	private String folioentregaSAP;
	private String description;
	private String FolioConfEntrega;
	private String identifieramq;
	private String countline;
	private String initialcost;
	
	
	public String getInitialcost() {
		return initialcost;
	}
	public void setInitialcost(String initialcost) {
		this.initialcost = initialcost;
	}
	public String getCountline() {
		return countline;
	}
	public void setCountline(String countline) {
		this.countline = countline;
	}
	public String getIdentifieramq() {
		return identifieramq;
	}
	public void setIdentifieramq(String identifieramq) {
		this.identifieramq = identifieramq;
	}
	public String getFolioConfEntrega() {
		return FolioConfEntrega;
	}
	public void setFolioConfEntrega(String folioConfEntrega) {
		FolioConfEntrega = folioConfEntrega;
	}
	public String getDescription() {
		return description;
	}
	public void setDescription(String description) {
		this.description = description;
	}
	public String getReference() {
		return reference;
	}
	public void setReference(String reference) {
		this.reference = reference;
	}
	public String getStockid() {
		return stockid;
	}
	public void setStockid(String stockid) {
		this.stockid = stockid;
	}
	public String getShipqty() {
		return shipqty;
	}
	public void setShipqty(String shipqty) {
		this.shipqty = shipqty;
	}
	public String getRecqty() {
		return recqty;
	}
	public void setRecqty(String recqty) {
		this.recqty = recqty;
	}
	public String getShipdate() {
		return shipdate;
	}
	public void setShipdate(String shipdate) {
		this.shipdate = shipdate;
	}
	public String getRecdate() {
		return recdate;
	}
	public void setRecdate(String recdate) {
		this.recdate = recdate;
	}
	public String getShiploc() {
		return shiploc;
	}
	public void setShiploc(String shiploc) {
		this.shiploc = shiploc;
	}
	public String getShipsecloc() {
		return shipsecloc;
	}
	public void setShipsecloc(String shipsecloc) {
		this.shipsecloc = shipsecloc;
	}
	public String getRecloc() {
		return recloc;
	}
	public void setRecloc(String recloc) {
		this.recloc = recloc;
	}
	public String getRecsecloc() {
		return recsecloc;
	}
	public void setRecsecloc(String recsecloc) {
		this.recsecloc = recsecloc;
	}
	public String getComments() {
		return comments;
	}
	public void setComments(String comments) {
		this.comments = comments;
	}
	public String getSerialno() {
		return serialno;
	}
	public void setSerialno(String serialno) {
		this.serialno = serialno;
	}
	public String getUserregister() {
		return userregister;
	}
	public void setUserregister(String userregister) {
		this.userregister = userregister;
	}
	public String getUserrec() {
		return userrec;
	}
	public void setUserrec(String userrec) {
		this.userrec = userrec;
	}
	public String getUsercancel() {
		return usercancel;
	}
	public void setUsercancel(String usercancel) {
		this.usercancel = usercancel;
	}
	public String getStatustransfer() {
		return statustransfer;
	}
	public void setStatustransfer(String statustransfer) {
		this.statustransfer = statustransfer;
	}
	public String getQtycancel() {
		return qtycancel;
	}
	public void setQtycancel(String qtycancel) {
		this.qtycancel = qtycancel;
	}
	public String getTransferline() {
		return transferline;
	}
	public void setTransferline(String transferline) {
		this.transferline = transferline;
	}
	public String getRequisitionno() {
		return requisitionno;
	}
	public void setRequisitionno(String requisitionno) {
		this.requisitionno = requisitionno;
	}
	public String getDebtorno() {
		return debtorno;
	}
	public void setDebtorno(String debtorno) {
		this.debtorno = debtorno;
	}
	public String getBranchcode() {
		return branchcode;
	}
	public void setBranchcode(String branchcode) {
		this.branchcode = branchcode;
	}
	public String getCost() {
		return cost;
	}
	public void setCost(String cost) {
		this.cost = cost;
	}
	public String getNoContratoConsigAuthCust() {
		return NoContratoConsigAuthCust;
	}
	public void setNoContratoConsigAuthCust(String noContratoConsigAuthCust) {
		NoContratoConsigAuthCust = noContratoConsigAuthCust;
	}
	public String getWo() {
		return wo;
	}
	public void setWo(String wo) {
		this.wo = wo;
	}
	public String getQuantity() {
		return quantity;
	}
	public void setQuantity(String quantity) {
		this.quantity = quantity;
	}
	public String getTipoconversion() {
		return tipoconversion;
	}
	public void setTipoconversion(String tipoconversion) {
		this.tipoconversion = tipoconversion;
	}
	public String getIdentifytransfer() {
		return identifytransfer;
	}
	public void setIdentifytransfer(String identifytransfer) {
		this.identifytransfer = identifytransfer;
	}
	public String getQuantitysend() {
		return quantitysend;
	}
	public void setQuantitysend(String quantitysend) {
		this.quantitysend = quantitysend;
	}
	public Integer getIdestatus() {
		return idestatus;
	}
	public void setIdestatus(Integer idestatus) {
		this.idestatus = idestatus;
	}
	public Integer getNextstatus() {
		return nextstatus;
	}
	public void setNextstatus(Integer nextstatus) {
		this.nextstatus = nextstatus;
	}
	public String getOperacion() {
		return operacion;
	}
	public void setOperacion(String operacion) {
		this.operacion = operacion;
	}
	public String getTipo() {
		return tipo;
	}
	public void setTipo(String tipo) {
		this.tipo = tipo;
	}
	public String getQueue() {
		return queue;
	}
	public void setQueue(String queue) {
		this.queue = queue;
	}
	public String getComentGeneral() {
		return comentGeneral;
	}
	public void setComentGeneral(String comentGeneral) {
		this.comentGeneral = comentGeneral;
	}
	public String getCanceldate() {
		return canceldate;
	}
	public void setCanceldate(String canceldate) {
		this.canceldate = canceldate;
	}
	public String getRequisitiondate() {
		return requisitiondate;
	}
	public void setRequisitiondate(String requisitiondate) {
		this.requisitiondate = requisitiondate;
	}
	public String getElaborationdate() {
		return elaborationdate;
	}
	public void setElaborationdate(String elaborationdate) {
		this.elaborationdate = elaborationdate;
	}
	public String getExpirationdate() {
		return expirationdate;
	}
	public void setExpirationdate(String expirationdate) {
		this.expirationdate = expirationdate;
	}
	public String getFreezingdate() {
		return freezingdate;
	}
	public void setFreezingdate(String freezingdate) {
		this.freezingdate = freezingdate;
	}
	public String getQty_excess() {
		return qty_excess;
	}
	public void setQty_excess(String qty_excess) {
		this.qty_excess = qty_excess;
	}
	public String getType() {
		return type;
	}
	public void setType(String type) {
		this.type = type;
	}
	public String getSAPBorrado() {
		return SAPBorrado;
	}
	public void setSAPBorrado(String sAPBorrado) {
		SAPBorrado = sAPBorrado;
	}
	public String getRecnowqty() {
		return recnowqty;
	}
	public void setRecnowqty(String recnowqty) {
		this.recnowqty = recnowqty;
	}
	public String getEnviadaSAP() {
		return enviadaSAP;
	}
	public void setEnviadaSAP(String enviadaSAP) {
		this.enviadaSAP = enviadaSAP;
	}
	public String getDoctoSAP() {
		return doctoSAP;
	}
	public void setDoctoSAP(String doctoSAP) {
		this.doctoSAP = doctoSAP;
	}
	public String getErrordescripcion() {
		return errordescripcion;
	}
	public void setErrordescripcion(String errordescripcion) {
		this.errordescripcion = errordescripcion;
	}
	public String getCodigoMsg() {
		return codigoMsg;
	}
	public void setCodigoMsg(String codigoMsg) {
		this.codigoMsg = codigoMsg;
	}
	public String getTagref() {
		return tagref;
	}
	public void setTagref(String tagref) {
		this.tagref = tagref;
	}
	public String getSAPcentro() {
		return SAPcentro;
	}
	public void setSAPcentro(String sAPcentro) {
		SAPcentro = sAPcentro;
	}
	public String getSAPActualizado() {
		return SAPActualizado;
	}
	public void setSAPActualizado(String sAPActualizado) {
		SAPActualizado = sAPActualizado;
	}
	public String getQty_missing() {
		return qty_missing;
	}
	public void setQty_missing(String qty_missing) {
		this.qty_missing = qty_missing;
	}
	public String getSecondfactorconversion() {
		return secondfactorconversion;
	}
	public void setSecondfactorconversion(String secondfactorconversion) {
		this.secondfactorconversion = secondfactorconversion;
	}
	public String getTransferenceshipdate() {
		return transferenceshipdate;
	}
	public void setTransferenceshipdate(String transferenceshipdate) {
		this.transferenceshipdate = transferenceshipdate;
	}
	public String getIdtipotransferencia() {
		return idtipotransferencia;
	}
	public void setIdtipotransferencia(String idtipotransferencia) {
		this.idtipotransferencia = idtipotransferencia;
	}
	public String getFolioentregaSAP() {
		return folioentregaSAP;
	}
	public void setFolioentregaSAP(String folioentregaSAP) {
		this.folioentregaSAP = folioentregaSAP;
	}
	
}