package com.jahepi.activemq.dto;

import java.io.Serializable;

public class QueueMessageAMQStatus implements Serializable {
	
	private static final long serialVersionUID = 1;
	
	private int id;
	private int message;
	private String tagref;
	
	public int getId() {
		return id;
	}
	public void setId(int id) {
		this.id = id;
	}
	public int getMessage() {
		return message;
	}
	public void setMessage(int message) {
		this.message = message;
	}
	public String getTagref() {
		return tagref;
	}
	public void setTagref(String tagref) {
		this.tagref = tagref;
	}

}
