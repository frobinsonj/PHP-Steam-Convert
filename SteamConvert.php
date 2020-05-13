<?php

class SteamConvert
{
	public $communityID = null;
	public $steamID = null;

	public function __construct($steam = null)
	{
		if (self::isSteamID($steam))
		{
			$this->communityID	= self::toCommunityID($steam);
			$this->steamID		= self::toSteamID($steam);
		}
	}

	public static function isProfileLink(string $url): bool
	{
		$result = false;
		if (filter_var($url, FILTER_VALIDATE_URL))
		{
			$profile = json_decode(json_encode(simplexml_load_string(file_get_contents($url."?xml=1"))),true);
			if (!isset($profile['error']))
			{
				$result = true;
			}
		}

		return $result;
	}

	public static function isCommunityID(string $id): bool
	{
		$profile = json_decode(json_encode(simplexml_load_string(file_get_contents("http://www.steamcommunity.com/profiles/$id?xml=1"))),true);

		return !isset($profile['error']);
	}

	public static function isSteamID(string $id): bool
	{
		return preg_match("/^STEAM_[0-5]:[01]:\d+$/", $id);
	}

	public static function isValid(string $steam): bool
	{
		return self::isSteamID($steam) || self::isProfileLink($steam) || self::isCommunityID($steam);
	}

	public static function toCommunityID(string $id)
	{
		if (self::isSteamID($id))
		{
			$parts = explode(':', $id);
			$steamID = bcadd(bcadd(bcmul($parts[2], '2'), '76561197960265728'), $parts[1]);
		}
		else if (is_numeric($id) && strlen($id) < 16)
		{
			$steamID = bcadd($id, '76561197960265728');
		}
		else if (self::isProfileLink($id))
		{
			$profile = json_decode(json_encode(simplexml_load_string(file_get_contents($id."?xml=1"))),true);
			$steamID = (int)$profile['steamID64'];
		}
		else
		{
			$steamID = $id;
		}

		return $steamID;
	}

	public static function toSteamID($id): ?string
	{
		if (is_numeric($id) && strlen($id) >= 16)
		{
			$z = bcdiv(bcsub($id, '76561197960265728'), '2');
		}
		else if (is_numeric($id))
		{
			$z = bcdiv($id, '2');
		}
		else
		{
			return $id;
		}
		$y = bcmod($id, '2');
		return 'STEAM_0:' . $y . ':' . floor($z);
	}
}
