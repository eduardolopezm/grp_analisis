package com.jahepi.activemq.dto;

import java.io.Serializable;

public class QueueMessageMaterial implements Serializable {
	
	private static final long serialVersionUID = 1;
	
	private String id;
	private String type;
	private String reference;
	private String entry;
	private String stockid;
	private String quantity;
	private String unit;
	private String deliverydate;
	private String userid;
	private String userregister;
	private String registerdate;
	private String folioconf;
	private String folioconfdate;
	private String erptransno;
	private String iddelivery;
	private String tipo;
	private String queue;
	private String indicadorSAP;
	private String identifieramq;
	private String movementtsapplied;
	
	public String getMovementtsapplied() {
		return movementtsapplied;
	}
	public void setMovementtsapplied(String movementtsapplied) {
		this.movementtsapplied = movementtsapplied;
	}
	public String getUserregister() {
		return userregister;
	}
	public void setUserregister(String userregister) {
		this.userregister = userregister;
	}
	public String getRegisterdate() {
		return registerdate;
	}
	public void setRegisterdate(String registerdate) {
		this.registerdate = registerdate;
	}
	public String getFolioconf() {
		return folioconf;
	}
	public void setFolioconf(String folioconf) {
		this.folioconf = folioconf;
	}
	public String getFolioconfdate() {
		return folioconfdate;
	}
	public void setFolioconfdate(String folioconfdate) {
		this.folioconfdate = folioconfdate;
	}
	public String getErptransno() {
		return erptransno;
	}
	public void setErptransno(String erptransno) {
		this.erptransno = erptransno;
	}
	public String getIdentifieramq() {
		return identifieramq;
	}
	public void setIdentifieramq(String identifieramq) {
		this.identifieramq = identifieramq;
	}
	public String getIndicadorSAP() {
		return indicadorSAP;
	}
	public void setIndicadorSAP(String indicadorSAP) {
		this.indicadorSAP = indicadorSAP;
	}
	public String getQueue() {
		return queue;
	}
	public void setQueue(String queue) {
		this.queue = queue;
	}
	public String getTipo() {
		return tipo;
	}
	public void setTipo(String tipo) {
		this.tipo = tipo;
	}
	public String getId() {
		return id;
	}
	public void setId(String id) {
		this.id = id;
	}
	public String getType() {
		return type;
	}
	public void setType(String type) {
		this.type = type;
	}
	public String getReference() {
		return reference;
	}
	public void setReference(String reference) {
		this.reference = reference;
	}
	public String getEntry() {
		return entry;
	}
	public void setEntry(String entry) {
		this.entry = entry;
	}
	public String getStockid() {
		return stockid;
	}
	public void setStockid(String stockid) {
		this.stockid = stockid;
	}
	public String getQuantity() {
		return quantity;
	}
	public void setQuantity(String quantity) {
		this.quantity = quantity;
	}
	public String getUnit() {
		return unit;
	}
	public void setUnit(String unit) {
		this.unit = unit;
	}
	public String getDeliverydate() {
		return deliverydate;
	}
	public void setDeliverydate(String deliverydate) {
		this.deliverydate = deliverydate;
	}
	public String getUserid() {
		return userid;
	}
	public void setUserid(String userid) {
		this.userid = userid;
	}
	public String getIddelivery() {
		return iddelivery;
	}
	public void setIddelivery(String iddelivery) {
		this.iddelivery = iddelivery;
	}
	
}
