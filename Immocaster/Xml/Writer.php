<?php
 
 /**
 * ImmobilienScout24 PHP-SDK
 * Klasse zum erzeugen eines XML-Body.
 *
 * @package    ImmobilienScout24 PHP-SDK
 * @author     Norman Braun (medienopfer98.de)
 * @link       http://www.immobilienscout24.de
 *
 * ==============================================
 * ACHTUNG
 * ==============================================
 *
 * Diese Klasse wird nicht mehr weiterentwickelt.
 * Mehr dazu unter: https://github.com/ImmobilienScout24/restapi-php-sdk/wiki/Objekte-exportieren
 *
 * ==============================================
 */

class Immocaster_Xml_Writer
{
	
	/**
     * Content der XML-Datei
	 *
     * @var string
     */
	private $_sXML = '';
	
	/**
     * Sortierung der XML für den Service
	 *
     * @var array
     */
	private $_aSort = array();
	
	/**
     * Singleton Instanzen
     *
     * @var array
     */
	static private $_instances = array();
	
    /**
     * Singleton Pattern für die Erstellung
	 * der Instanzen von Immocaster_Xml_Writer.
     *
     * @return Immocaster_Xml_Writer
     */
	static public function getInstance($sName)
	{
		if(!isset(self::$_instances[$sName]))
		{
			self::$_instances[$sName] = new self();
		}
		return self::$_instances[$sName];
	}
	
	/**
     * Formatierung der XML-Datei setzen.
     *
     * @return bool
     */
	public function setFormat($sService='utf8',$aParameter=array())
	{
		switch($sService)
		{
			case 'utf8':
				$this->_sXML .= '<?xml version="1.0" encoding="UTF-8"?>%s';
			break;
			case 'immobilienscout':
				$this->_sXML .= '<?xml version="1.0" encoding="UTF-8"?>';
				if(isset($aParameter['estate_type']))
				{
					if(strtolower($aParameter['estate_type'])=='apartmentrent')
					{
						$sOuterElement = "realestates:apartmentRent";
						$this->_aSort = require_once(dirname(__FILE__).'/Writer/Immobilienscout/Apartmentrent.php');
					}
					if(strtolower($aParameter['estate_type'])=='apartmentbuy')
					{
						$sOuterElement = "realestates:apartmentBuy";
						$this->_aSort = require_once(dirname(__FILE__).'/Writer/Immobilienscout/Apartmentbuy.php');
					}
					if(strtolower($aParameter['estate_type'])=='houserent')
					{
						$sOuterElement = "realestates:houseRent";
						$this->_aSort = require_once(dirname(__FILE__).'/Writer/Immobilienscout/Houserent.php');
					}
					if(strtolower($aParameter['estate_type'])=='housebuy')
					{
						$sOuterElement = "realestates:houseBuy";
						$this->_aSort = require_once(dirname(__FILE__).'/Writer/Immobilienscout/Housebuy.php');
					}
					$this->_sXML .= '<'.$sOuterElement.' xmlns:xlink="http://www.w3.org/1999/xlink" '.
					'xmlns:realestates="http://rest.immobilienscout24.de/schema/offer/realestates/1.0">%s</'.$sOuterElement.'>';
				}
			break;
			default:
				return false;
			break;
		}
		return true;
	}
	
	/**
     * Multidimensionalen Array in den
	 * XML-String integrieren und fertiges
	 * XML zurückliefern.
     *
	 * @param array Array mit Werten für die XML Datei
     * @return void
     */
	public function getXml($aElements)
	{
		function multipushXml($aXml)
		{  
			$xml="";  
			foreach ($aXml as $key=>$val)
			{  
				if(is_array($val))
				{
					$xml.="<$key>".multipushXml($val)."</$key>";  
				}
				else
				{  
					if($val===false)
					{
						$val='false';
					}  
					$xml.="<$key>".$val."</$key>";  
				}  
			}  
			return $xml;  
		}
		return sprintf($this->_sXML,multipushXml($this->xmlArraySort($aElements)));
	}
	
	/**
     * Multidimensionalen Array für den
	 * jeweiligen Service sortieren. Der jeweilige
	 * Service wird in der Funktion setFormat gesetzt.
     *
	 * @param array Array mit Werten für die XML Datei
     * @return array
     */
	private function xmlArraySort($aElements)
	{
		$aXml = array();
		foreach($this->_aSort as $key=>$val)
		{
			$bUseParameter = true;
			if($val['type']=='bool-set' && isset($aElements[$key]))
			{
				if($aElements[$key]===false && !isset($val['values'])){$bUseParameter = false;}
				if($aElements[$key]===true && isset($val['values'])){$aElements[$key] = $val['values'][0];}
				if($aElements[$key]===false && isset($val['values'])){$aElements[$key] = $val['values'][1];}
			}
			if($val['type']=='string-set' && isset($aElements[$key]))
			{
				if(isset($val['values'][$aElements[$key]]))
				{
					$aElements[$key] = $val['values'][$aElements[$key]];
				}
				else
				{
					$aElements[$key] = $val['values']['default'];
				}
			}
			if(isset($aElements[$key]) && $bUseParameter===true)
			{
				switch(count($val['xml']))
				{
					case 1:
						$aXml[$val['xml'][0]] = $aElements[$key];
					break;
					case 2:
						$aXml[$val['xml'][0]][$val['xml'][1]] = $aElements[$key];
					break;
					case 3:
						$aXml[$val['xml'][0]][$val['xml'][1]][$val['xml'][2]] = $aElements[$key];
					break;
				}
			}
		}
		return $aXml;
	}
	
}