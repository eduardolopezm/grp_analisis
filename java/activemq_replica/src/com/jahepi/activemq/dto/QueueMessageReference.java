package com.jahepi.activemq.dto;

import java.io.Serializable;

public class QueueMessageReference implements Serializable {
	
	private static final long serialVersionUID = 1L;
	
	private String anio;
	private String loccode;
	private String sequence;
	private String type;
	private String activemq;
	
	public String getActivemq() {
		return activemq;
	}
	public void setActivemq(String activemq) {
		this.activemq = activemq;
	}
	public String getAnio() {
		return anio;
	}
	public void setAnio(String anio) {
		this.anio = anio;
	}
	public String getLoccode() {
		return loccode;
	}
	public void setLoccode(String loccode) {
		this.loccode = loccode;
	}
	public String getSequence() {
		return sequence;
	}
	public void setSequence(String sequence) {
		this.sequence = sequence;
	}
	public String getType() {
		return type;
	}
	public void setType(String type) {
		this.type = type;
	}

}
