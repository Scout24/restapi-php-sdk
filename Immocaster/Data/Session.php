<?php

/**
 * Immocaster SDK
 * Datenspeicherung per Session
 * innerhalb von PHP Anwendungen.
 *
 * @package    Immocaster SDK
 * @author     Norman Braun (medienopfer98.de)
 * @link       http://www.immocaster.com
 * @version    1.1.18
 */

class Immocaster_Data_Session
{
	
	/**
     * Namespace für die Variablen des 
	 * Immocaster SDK innerhalb der Session.
	 *
     * @var string
     */
	 private $_sNamespace = 'Immocaster';
	
    /**
     * Singleton Pattern für die Erstellung
	 * der Instanz von Immocaster_Data_Session.
     *
     * @return Immocaster_Data_Session
     */
	static private $instance = null; 
	static public function getInstance($sNamespace=null) 
	{ 
		if (!isset(self::$instance)) 
		{ 
			self::$instance = new self($sNamespace); 
		} 
		return self::$instance; 
	}
	
	/**
     * Session starten, falls Session
	 * noch nicht läuft und Namespace
	 * setzen, falls dieser gesetzt ist.
     *
	 * @param string Alternativer Namespace für Variablen innerhalb der Session
     * @return void
     */
	public function __construct($sNamespace)
	{
		if(!isset($_SESSION))
		{
			session_start();
		}
		if($sNamespace!=null)
		{
			$this->_sNamespace = $sNamespace;
		}
	}
	
	/**
     * Variable innerhalb einer
	 * Session speichern.
     *
	 * @param string Name der Variablen
	 * @param string Inhalt der Variablen
     * @return void
     */
	public function setVar($sName,$sValue)
	{
		$_SESSION[$this->_sNamespace][$sName] = $sValue;
	}
	
	/**
     * Variable aus einer
	 * Session auslesen.
     *
	 * @param string Name der Variablen
     * @return string
     */
	public function getVar($sName)
	{
		return $_SESSION[$this->_sNamespace][$sName];
	}
	
	/**
     * Kompletten Namespace von Immocaster
	 * in der Session löschen.
     *
     * @return void
     */
	public function unsetNamespace()
	{
		unset($_SESSION[$this->_sNamespace]);
	}
	
}