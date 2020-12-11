package com.jahepi.activemq.view;

import java.awt.Color;
import java.awt.Font;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Date;

import javax.swing.ImageIcon;
import javax.swing.JButton;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JPanel;
import javax.swing.JTextArea;
import javax.swing.SwingConstants;
import javax.swing.UIManager;
import javax.swing.border.EmptyBorder;
import javax.swing.border.EtchedBorder;
import javax.swing.border.TitledBorder;
import javax.swing.text.BadLocationException;
import javax.swing.text.Document;

import com.jahepi.activemq.loader.Config.ConfigData;
import com.jahepi.activemq.loader.ResourceLoader;

public class ActiveMQFrame extends JFrame implements AppListener {

	private static final long serialVersionUID = 1L;

	private static int STRING_LENGTH = 1000;
	private static String ACTIVO = "ACTIVO";
	private static String INACTIVO = "DESACTIVADO";
	private static String BR = "\n";

	private JPanel contentPane;
	private JLabel lblActivo, lightBulbLabel;
	private ConfigData config;
	private ViewListener listener;
	private JTextArea textArea;

	public ActiveMQFrame(ConfigData config, ViewListener listener) {
		this.config = config;
		setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
		setBounds(100, 100, 660, 446);
		contentPane = new JPanel();
		contentPane.setBackground(UIManager.getColor("Focus.color"));
		contentPane.setBorder(new EmptyBorder(5, 5, 5, 5));
		setContentPane(contentPane);
		contentPane.setLayout(null);
		this.setResizable(false);
		this.listener = listener;

		JLabel lblActiveMq = new JLabel("ACTIVE MQ REPLICADOR "
				+ config.get("version") + " ["
				+ this.config.get("type").toUpperCase() + "]");
		lblActiveMq.setForeground(new Color(255, 215, 0));
		lblActiveMq.setFont(new Font("Lucida Grande", Font.BOLD, 16));
		lblActiveMq.setBounds(80, 6, 435, 16);
		contentPane.add(lblActiveMq);

		JPanel panel = new JPanel();
		panel.setBackground(UIManager
				.getColor("EditorPane.selectionBackground"));
		panel.setBorder(new TitledBorder(null, "Configuracion",
				TitledBorder.LEADING, TitledBorder.TOP, null, null));
		panel.setBounds(80, 23, 574, 86);
		contentPane.add(panel);
		panel.setLayout(null);

		JLabel lblNewLabel = new JLabel("Servidor");
		lblNewLabel.setBounds(17, 18, 61, 16);
		panel.add(lblNewLabel);

		JLabel lblPuerto = new JLabel("Puerto");
		lblPuerto.setBounds(17, 35, 61, 16);
		panel.add(lblPuerto);

		JLabel lblNewLabel_1 = new JLabel("Usuario");
		lblNewLabel_1.setBounds(17, 52, 61, 16);
		panel.add(lblNewLabel_1);

		JLabel lblContrasea = new JLabel("Contrase\u00F1a");
		lblContrasea.setBounds(288, 18, 77, 16);
		panel.add(lblContrasea);

		JLabel lblQueue = new JLabel("Queue");
		lblQueue.setBounds(288, 35, 61, 16);
		panel.add(lblQueue);

		JLabel lblTipo = new JLabel("Tipo");
		lblTipo.setBounds(288, 52, 61, 16);
		panel.add(lblTipo);

		JLabel lblNewLabel_2 = new JLabel(this.config.get("server"));
		lblNewLabel_2.setFont(new Font("Lucida Grande", Font.BOLD, 13));
		lblNewLabel_2.setBounds(84, 18, 131, 16);
		panel.add(lblNewLabel_2);

		JLabel lblNewLabel_3 = new JLabel(this.config.get("port"));
		lblNewLabel_3.setFont(new Font("Lucida Grande", Font.BOLD, 13));
		lblNewLabel_3.setBounds(84, 35, 131, 16);
		panel.add(lblNewLabel_3);

		JLabel lblNewLabel_4 = new JLabel(this.config.get("user"));
		lblNewLabel_4.setFont(new Font("Lucida Grande", Font.BOLD, 13));
		lblNewLabel_4.setBounds(84, 52, 131, 16);
		panel.add(lblNewLabel_4);

		JLabel lblNewLabel_5 = new JLabel(this.config.get("pass"));
		lblNewLabel_5.setFont(new Font("Lucida Grande", Font.BOLD, 13));
		lblNewLabel_5.setBounds(371, 18, 123, 16);
		panel.add(lblNewLabel_5);

		JLabel lblNewLabel_6 = new JLabel(this.config.get("queue"));
		lblNewLabel_6.setFont(new Font("Lucida Grande", Font.BOLD, 13));
		lblNewLabel_6.setBounds(371, 35, 123, 16);
		panel.add(lblNewLabel_6);

		JLabel lblNewLabel_7 = new JLabel(this.config.get("type"));
		lblNewLabel_7.setFont(new Font("Lucida Grande", Font.BOLD, 13));
		lblNewLabel_7.setBounds(371, 52, 121, 16);
		panel.add(lblNewLabel_7);

		JPanel panel_1 = new JPanel();
		panel_1.setBackground(UIManager
				.getColor("EditorPane.selectionBackground"));
		panel_1.setBorder(new TitledBorder(new EtchedBorder(
				EtchedBorder.LOWERED, null, null), "Consola Servidor",
				TitledBorder.LEADING, TitledBorder.TOP, null,
				new Color(0, 0, 0)));
		panel_1.setBounds(6, 180, 648, 205);
		contentPane.add(panel_1);
		panel_1.setLayout(null);

		textArea = new JTextArea();
		textArea.setBounds(17, 26, 614, 162);
		panel_1.add(textArea);

		JPanel panel_2 = new JPanel();
		panel_2.setBackground(UIManager
				.getColor("EditorPane.selectionBackground"));
		panel_2.setBorder(new TitledBorder(null, "Estado Servidor",
				TitledBorder.LEADING, TitledBorder.TOP, null, null));
		panel_2.setBounds(6, 116, 648, 62);
		contentPane.add(panel_2);
		panel_2.setLayout(null);

		lblActivo = new JLabel(INACTIVO);
		lblActivo.setHorizontalAlignment(SwingConstants.CENTER);
		lblActivo.setForeground(Color.BLACK);
		lblActivo.setBackground(new Color(255, 0, 0));
		lblActivo.setFont(new Font("Lucida Grande", Font.PLAIN, 19));
		lblActivo.setBounds(81, 22, 550, 23);
		lblActivo.setOpaque(true);
		panel_2.add(lblActivo);

		lightBulbLabel = new JLabel("");
		lightBulbLabel.setBounds(25, 20, 41, 34);

		lightBulbLabel.setIcon(new ImageIcon(ResourceLoader
				.load("assets/lightbulb_off.png")));
		panel_2.add(lightBulbLabel);

		JButton btnSalir = new JButton("SALIR");
		btnSalir.setBounds(537, 389, 117, 29);
		contentPane.add(btnSalir);

		JLabel lblNewLabel_8 = new JLabel("");
		lblNewLabel_8.setBounds(6, 6, 62, 102);
		lblNewLabel_8.setIcon(new ImageIcon(ResourceLoader
				.load("assets/portalito.png")));
		contentPane.add(lblNewLabel_8);

		JButton btnReiniciar = new JButton("REINICIAR");
		btnReiniciar.setBounds(425, 389, 117, 29);
		contentPane.add(btnReiniciar);

		btnReiniciar.addActionListener(new ActionListener() {
			@Override
			public void actionPerformed(ActionEvent e) {
				ActiveMQFrame.this.addMessageToPane(getCurrentDate()
						+ "> REINICIANDO SERVICIO ...");
				ActiveMQFrame.this.listener.onRestart();
			}
		});

		btnSalir.addActionListener(new ActionListener() {
			@Override
			public void actionPerformed(ActionEvent e) {
				ActiveMQFrame.this.listener.onExit();
			}
		});
	}

	@Override
	public void onConfigError(String message) {
		addMessageToPane(getCurrentDate() + "> ERROR CONFIGURACION: " + message);
	}

	@Override
	public void onSaveXMLError(String file) {
		addMessageToPane(getCurrentDate() + "> ERROR XML: " + file);
	}

	@Override
	public void onExceptionError(String message) {
		addMessageToPane(getCurrentDate() + "> EXCEPCION: " + message);
	}

	@Override
	public void onConfigSuccess() {
		addMessageToPane(getCurrentDate()
				+ "> EXITO: Se ha cargado el archivo de configuracion correctamente");
	}

	@Override
	public void onQueueMessage(String message) {
		addMessageToPane(getCurrentDate() + "> QUEUE: " + message);
	}

	@Override
	public void onDBError() {
		addMessageToPane(getCurrentDate()
				+ "> ERROR: Error al conectarse a la base de datos");
	}

	@Override
	public void onDBSuccess() {
		addMessageToPane(getCurrentDate()
				+ "> EXITO: Conexion exitosa a la base de datos");
	}

	@Override
	public void onQueueConnect() {
		lblActivo.setBackground(new Color(50, 205, 50));
		lblActivo.setText(ACTIVO);
		lightBulbLabel.setIcon(new ImageIcon(ResourceLoader
				.load("assets/lightbulb.png")));
		addMessageToPane(getCurrentDate() + "> EXITO: Conectado al Queue "
				+ config.get("queue") + "");

	}

	@Override
	public void onQueueDisconnet() {
		lblActivo.setBackground(new Color(255, 0, 0));
		lblActivo.setText(INACTIVO);
		lightBulbLabel.setIcon(new ImageIcon(ResourceLoader
				.load("assets/lightbulb_off.png")));
		addMessageToPane(getCurrentDate()
				+ "> ERROR: No se pudo conectar al Queue "
				+ config.get("queue"));

	}

	private void addMessageToPane(String message) {
		try {
			Document doc = textArea.getDocument();
			if (doc.getLength() > STRING_LENGTH) {
				try {
					doc.remove(0, doc.getLength());
				} catch (BadLocationException e) {
					System.out.println(e.getMessage());
				}
			}
			textArea.setText(message + BR + textArea.getText());
		} catch (Exception e) {
			// e.printStackTrace();
		}
	}

	private String getCurrentDate() {
		DateFormat dateFormat = new SimpleDateFormat("yyyy/MM/dd HH:mm:ss");
		Date date = new Date();
		return "[" + dateFormat.format(date) + "]";
	}
}
