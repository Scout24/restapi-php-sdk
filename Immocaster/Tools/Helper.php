<?php

/**
 * ImmobilienScout24 PHP-SDK
 * Helferfunktionen für das SDK.
 *
 * @package    ImmobilienScout24 PHP-SDK
 * @author     Norman Braun (medienopfer98.de)
 * @link       http://www.immobilienscout24.de
 */

class Immocaster_Tools_Helper
{
	
	/*
	 * Aus einem String wird ein Array erzeugt.
	 *
	 * @param string String mit (GET-)Variablen, wie z.B. a=123&b=456
	 * @param array
	 * @return mixed
	 */
	public static function makeArrayFromString($sString='',$aReturn=array())
	{
		$res = explode('&',$sString);
		foreach($res as $sVar)
		{
			$aVar = explode('=',$sVar);
			$aReturn[$aVar[0]] = $aVar[1];
		}
		return $aReturn;
	}
	
}