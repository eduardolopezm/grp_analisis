package com.jahepi.activemq.view;

public interface AppListener {

	public void onDBError();

	public void onDBSuccess();

	public void onSaveXMLError(String file);

	public void onConfigError(String message);

	public void onConfigSuccess();

	public void onQueueConnect();

	public void onQueueDisconnet();

	public void onQueueMessage(String message);

	public void onExceptionError(String message);

}
