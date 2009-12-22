<?php
	require_once "config.php";

	function databaseHandleErrors($message = "")
	{
		if (mysql_errno() > 0)
		{
			if ($message != "")
				echo "<b>MySQL Error: </b>" . $message . "<br />"; else
					echo "<b>MySQL Error: </b>" . mysql_error() . "<br />";
		}
	}

	function databaseConnect()
	{
		global $database_cfg;

		$link = mysql_connect($database_cfg["host"], $database_cfg["user"], $database_cfg["password"]);
                databaseHandleErrors("Could'nt connect to MySQL host");

		mysql_select_db($database_cfg["database"]);
		databaseHandleErrors("Can't select proper database");
	}

	function databaseQuery($sql, $error_message = "")
	{
		$result = mysql_query($sql);

		if ($error_message != "")
			databaseHandleErrors($error_message); else
				databaseHandleErrors("Failed to execure MySQL query " . $sql);

                if (mysql_errno() > 0 || !is_resource($result))
			return;

		$ret = array();

		for ($i = 0; $i < mysql_num_rows($result); $i++)
		{
			$line = mysql_fetch_array($result);

                        foreach ($line as $k => $v)
                            if (is_string($k))
                                 $l[$k] = $v;

                        $ret[] = $l;
		}

		return $ret;
	}

	// -------------------------------------------------------------

	function stringCheckForSymbols($str, $symbols)
	{
		$res = array();

		for ($i = 0; $i < strlen($symbols); $i++)
			if (strpos($str, $symbols[$i]) > 0)
				$res[$i] = true; else
					$res[$i] = false;

		return $res;
	}

	function stringEncode($str)
	{
		return rawurlencode($str);
	}

	function stringDecode($str)
	{
		return rawurldecode($str);
	}

	function stringTokenize($str, $separator = "+")
	{
		$s = explode($separator, $str);
		$res = array();
		
		for ($i = 0; $i < count($s); $i++)
			if ($s[$i])
				$res[] = $s[$i];
				
		return $res;
	}

	function stringTokenExists($str, $token)
	{
		if (strpos($str, $token) > 0)
			return true; else
				return false;
	}

	function stringCheckForTokens($str, $tokens)
	{
		if (!is_array($tokens))
			return;

		$res = array();

		for ($i = 0; $i < count($tokens); $i++)
			$res[] = stringTokenExists($str, $tokens[$i]);

		return $res;
	}

	function stringAddToken($str, $token, $separator = "+")
	{
		if (!in_array($token, stringTokenize($str, $separator)))
            		return $str . $separator . $token; else
                		return $str;
	}

	function stringAddTokens($str, $token_array)
	{
		if (!is_array($token_array))
			return;

		for ($i = 0; $i < count($token_array); $i++)
			$str = stringAddToken($str, $token_array[$i]);

		return $str;
	}

	function stringDropToken($str, $token, $separator = "+")
	{
		return str_replace($separator . $token, "", $str);
	}

	function stringDropTokens($str, $token_array, $separator = "+")
	{
		if (!is_array($token_array))
			return;

		for ($i = 0; $i < count($token_array); $i++)
			$str = stringDropToken($str, $token_array[$i], $separator);

		return $str;
	}
?>
