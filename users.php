<?php
	/**
	 * Database Tables: users
	 * Table <users>: id (int, auto_increment),
	 *   username (str), password (str), nickname (str),
	 *   email (str), groups (str), last_visit (str), rating (int)
	 */

	require_once 'config.php';
	require_once 'core.php';
	require_once 'groups.php';

	databaseConnect();
	session_start();

	function userGetByName($username)
	{
		global $database_cfg;

		$res = databaseQuery("select * from " . $database_cfg["prefix"] . "users where username='" . stringEncode($username) . "' limit 1");

		return $res[0];
	}

	function userGetByNick($username)
	{
		global $database_cfg;

		$res = databaseQuery("select * from " . $database_cfg["prefix"] . "users where nickname='" . stringEncode($username) . "' limit 1");

		return $res[0];
	}

	function userGetById($user_id)
	{
		global $database_cfg;

		$res = databaseQuery("select * from " . $database_cfg["prefix"] . "users where id='" . intval($user_id) . "' limit 1");

		return $res[0];
	}
	
	function userSearchByNickname($nickname)
	{
		global $database_cfg;

		$res = databaseQuery("select * from " . $database_cfg["prefix"] . "users where locate('" . stringEncode($nickname) . "', nickname) > 0");

		return $res;
	}
	
	function userSearchByEmail($email)
	{
		global $database_cfg;

		$res = databaseQuery("select * from " . $database_cfg["prefix"] . "users where locate('" . stringEncode($email) . "', email) > 0");

		return $res;
	}

	function userExists($username)
	{
		$user = userGetByName($username);

		if (is_array($user) && is_string($user["username"]))
			return true; else
				return false;
	}

	function userExistsByNickname($nick)
	{
		$user = userGetByNick($nick);

		if (is_array($user) && is_string($user["username"]))
			return true; else
				return false;
	}

	function userExistsById($id)
	{
		$user = userGetById($id);

		if (is_array($user) && is_string($user["username"]))
			return true; else
				return false;
	}

	function userGetByIdRange($id_min, $count)
	{
		global $database_cfg;

		return databaseQuery("select * from " . $database_cfg["prefix"] . "users where id >= '" . intval($id_min) . "' order by id desc limit " . intval($count), "Can't get user list");
	}

	function userLogIn($username = "", $password = "", $remember = false)
	{
		global $database_cfg;
		
		if (isset($_COOKIE["tmcms_" . $database_cfg["prefix"] . "remember"]))
		{
			$user = userGetById($_COOKIE["tmcms_" . $database_cfg["prefix"] . "remember"]);
			
			if (userExistsById($user["id"]))
			{
				$_SESSION["tmcms_" . $database_cfg["prefix"] . "user_id"] = intval($user["id"]);
				$_SESSION["tmcms_" . $database_cfg["prefix"] . "user_nickname"] = stringDecode($user["nickname"]);
				
				databaseQuery("update " . $database_cfg["prefix"] . "users set last_visit='" . stringEncode(date("H:i, d.m.Y")) . "' where id='" . intval($user["id"]) . "'");
				
				setcookie("tmcms_" . $database_cfg["prefix"] . "remember", $user["id"], time() + 31104000, '/');
				
				return;
			}
		}
		
		{
			$user = userGetByName($username);
	
			if ($user["password"] != md5(stringEncode($password)))
				return array("Wrong password");

			if (is_array($user) && isset($user["id"]) && userExistsById($user["id"]))
			{
				$_SESSION["tmcms_" . $database_cfg["prefix"] . "user_id"] = intval($user["id"]);
				$_SESSION["tmcms_" . $database_cfg["prefix"] . "user_nickname"] = stringDecode($user["nickname"]);
			} else
			{
				return array("User doesn't exist");
			}
		
			if ($remember == true)
			{
				setcookie("tmcms_" . $database_cfg["prefix"] . "remember", $user["id"], time() + 31104000, '/');
			}
		}
	}

	function userLogOut()
	{
		if (isset($_COOKIE["tmcms_" . $database_cfg["prefix"] . "remember"]))
			setcookie("tmcms_" . $database_cfg["prefix"] . "remember", -1, time() + 31104000, '/');

		session_unset();
		session_destroy();
	}

	function userCheckLoggedIn()
	{
			global $database_cfg;
			
			$prefix = "tmcms_" . $database_cfg["prefix"];
			
			if (isset($_SESSION[$prefix . "user_id"]) && isset($_SESSION[$prefix . "user_nickname"]))
				return true; else
					return false;
	}
	
	function userGetLoggedIn()
	{
		if (isset($_SESSION["tmcms_" . $database_cfg["prefix"] . "user_id"]))
		{
			return userGetById($_SESSION["tmcms_" . $database_cfg["prefix"] . "user_id"]);
		}
	}

	function userCheckFlags($user_id, $flags)
	{
		$user = userGetById(intval($user_id));
		
		if (!userExistsById($user_id) || !is_array($flags))
			return;
		
		$res = stringCheckForTokens($user["flags"], $flags);
		
		$a = userGetGroups($user_id);
		
		for ($i = 0; $i < count($a); $i++)
		{
			$group = groupGetById($a[$i]);
			$res1 = stringCheckForTokens($group["flags"], $flags);
			
			for ($j = 0, $t = 0; $j < count($res1), $t < count($res); $j++, $t++)
				$res[$t] = $res[$t] | $res1[$j];
		}
		
		return $res;
	}

	function userSetFlags($user_id, $flags)
	{
		global $database_cfg;

		$user = userGetById(intval($user_id));
		
		if (!userExistsById($user_id))
			return;

		for ($i = 0; $i < count($flags); $i++)
			$user["flags"] = stringAddToken($user["flags"], $flags[$i]);

		databaseQuery("update " . $database_cfg["prefix"] . "users set flags = '" . $user["flags"] . "' where id = " . intval($user_id), "Failed to set user's flags");
	}

	function userDropFlags($user_id, $flags)
	{
		global $database_cfg;

		$user = userGetById(intval($user_id));
		
		if (!userExistsById($user_id))
			return;

		for ($i = 0; $i < count($flags); $i++)
			$user["flags"] = stringDropToken($user["flags"], $flags[$i]);

		databaseQuery("update " . $database_cfg["prefix"] . "users set flags = '" . $user["flags"] . "' where id = " . intval($user_id), "Failed to set user's flags");
	}

	function userRegister($username, $password1, $password2,
		$email1, $email2, $captcha_code, $captcha_verify,
		$nickname)
	{
		global $database_cfg;

		$errors = array();

		if ($password1 != $password2)
			$errors[] = "Passwords do not match";

		if ($email1 != $email2)
			$errors[] = "E-mail addresses do not match";

		if ($captcha_code != $captcha_verify)
			$errors[] = "Wrong CAPTCHA code";

		if (count(userGetByName($username)) > 0)
			$errors[] = "User with this username already exists";

		if (count(userGetByNick($nickname)) > 0)
			$errors[] = "User with this nickname already exists";

		if (in_array(true, stringCheckForSymbols($email1, "<>,=+:;'\"?/*\|)(&^%$#[{]}!`~")) || !stringCheckForSymbols($email1, "@"))
			$errors[] = "E-mail address is invalid";

		if (strlen($password1) < 6)
			$errors[] = "Password is too short (less than 6 symbols)";

		if (count($errors) > 0)
			return $errors;

		databaseQuery("insert into " . $database_cfg["prefix"] . "users (username, password, nickname, email) values ('" . stringEncode($username) . "', '" . md5(stringEncode($password1)) . "', '" . stringEncode($nickname) . "', '" . stringEncode($email1) . "')", "Failed to create user");
	}

	function userKarmaIncrease($user_id)
	{
		global $database_cfg;

		$user = userGetById($user_id);
		
		if (!userExistsById($user_id))
			return;

		$user["rating"] = intval($user["rating"]) + 1;

		databaseQuery("update " . $database_cfg["prefix"] . "users set rating = '" . intval($user["rating"]) . "' where id = " . intval($user_id), "Failed to raise user's karma");
	}

	function userKarmaDecrease($user_id)
	{
		global $database_cfg;

		$user = userGetById($user_id);
		
		if (!userExistsById($user_id))
			return;

		$user["rating"] = intval($user["rating"]) - 1;

		databaseQuery("update " . $database_cfg["prefix"] . "users set rating = '" . intval($user["rating"]) . "' where id = " . intval($user_id), "Failed to decrease user's karma");
	}

	function userGetGroups($user_id)
	{
		$user = userGetById($user_id);
		
		if (!userExistsById($user_id))
			return;

		return stringTokenize($user["groups"], "+");
	}

	function userCheckGroups($user_id, $groups)
	{
		$user = userGetById($user_id);
		
		if (!userExistsById($user_id))
			return;
		
		$a = stringTokenize($user["groups"], "+");
		$res = array();
		
		for ($i = 0; $i < count($groups); $i++)
		{
			$res[$i] = in_array(strval($groups[$i]), $a);
		}
		
		return $res;
	}

	function userAddGroups($user_id, $groups)
	{
		global $database_cfg;

		$user = userGetById($user_id);

		if (is_array($groups))
		{
			$user["groups"] = stringAddTokens($user["groups"], $groups);
		} else
		{
			$user["groups"] = stringAddToken($user["groups"], $groups);
		}
				
		databaseQuery("update " . $database_cfg["prefix"] . "users set groups='" . $user["groups"] . "' where id='" . intval($user_id) . "'"); //, "Unable to add user to a group");
	}

	function userDropGroups($user_id, $groups)
	{
		global $database_cfg;

		$user = userGetById($user_id);

		$user["groups"] = stringDropTokens($user["groups"], $groups);

		databaseQuery("update " . $database_cfg["prefix"] . "users set groups='" . $user["groups"] . "' where id='" . intval($user_id) . "'", "Unable to add user to a group");
	}

	function userSetParams($user_id, $params)
	{
		global $database_cfg;

		$user = userGetById($user_id);
		
		if (!userExistsById($user_id))
			return;

		foreach ($params as $i => $t)
			if (array_key_exists($i, $user))
				$user[$i] = $t;
				
		databaseQuery("update " . $database_cfg["prefix"] . "users set username='" . stringEncode($user["username"]) . "', " .
			" password='" . md5(stringEncode($user["password"])) . "', email='" . stringEncode($user["email"]) . "', nickname='" . stringEncode($user["nickname"]) . "', " . 
			"last_visit='" . $user["last_visit"] . "' where id='" . intval($user_id) . "'", "Unable to set user params");
	}

	function userDrop($user_id)
	{
		global $database_cfg;

		if (userExistsById($user_id))
			databaseQuery("delete from " . $database_cfg["prefix"] . "users where id='" . intval($post_id) . "'", "Can't delete user");
	}
	
	function userCheckAdministrator($user_id)
	{
		$res = userCheckFlags($user_id, array("admin"));
		
		return $res[0];
	}
?>
