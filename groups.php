<?php
	/**
	 * Database Tables: groups
	 * Table <groups>: id (int, auto_increment),
	 *   name (str), flags (str)
	 */

	require_once 'core.php';
	require_once 'config.php';

	databaseConnect();

	function groupGetByName($name)
	{
		global $database_cfg;

		return databaseQuery("select * from " . $database_cfg["prefix"] . "groups where name='" . stringEncode($name) . "'", "Group not found");
	}

	function groupGetById($id)
	{
		global $database_cfg;

		$res = databaseQuery("select * from " . $database_cfg["prefix"] . "groups where id='" . stringEncode($id) . "' limit 1", "Group not found");
			
		return $res[0];
	}
	
	function groupGetByIdRange($id_start, $count)
	{
		global $database_cfg;

		return databaseQuery("select * from " . $database_cfg["prefix"] . "groups where id>='" . stringEncode($id_start) . "' limit " . intval($count), "No groups found");
	}
	
	function groupSearchByName($name)
	{
		global $database_cfg;

		return databaseQuery("select * from " . $database_cfg["prefix"] . "groups where locate('" . stringEncode($name) . "', name) > 0");
	}

	function groupCreate($name, $flags)
	{
		global $database_cfg;

		$errors = array();

		$group = groupGetByName($name);
			
		if (is_array($group) && is_string($group[0]["name"]))
			$errors[] = "Group with this name already exists";

		if (count($errors) > 0)
			return $errors;
				
		$new_flags = "";
			
		$new_flags = stringAddTokens($new_flags, $flags);

		databaseQuery("insert into " . $database_cfg["prefix"] . "groups (name, flags) values ('" . stringEncode($name) . "', '" . $new_flags . "')", "Can not create group");
	}

	function groupDrop($id)
	{
		global $database_cfg;

		$errors = array();

		if (!is_array(groupGetById($id)))
			$errors[] = "Group with given id doesn't exists";

		if (count($errors) > 0)
			return $errors;

		databaseQuery("delete from " . $database_cfg["prefix"] . "groups where id='" . intval($id) . "')", "Can not delete group");
	}

	function groupGetFlags($id)
	{
		$group = groupGetById($id);

		if (is_array($group) && is_string($group["name"]))
			return $group["flags"];
	}

	function groupCheckFlags($id, $flags)
	{
		$group = groupGetById($id);

		if (is_array($group))
			return stringCheckForTokens($group["flags"], $flags);
	}

	function groupAddFlags($id, $flags)
	{
		$group = groupGetById($id);

		if (is_array($group))
		{
			$flags1 = $group["flags"];
			$flags1 = stringAddTokens($flags1, $flags);

			global $database_cfg;
				
			databaseQuery("update " . $database_cfg["prefix"] . "groups set (flags='" . $flags1 . "'", "Can not add group flags");
		} else
		{
			return "Group with given id doesn't exists";
		}
	}

	function groupDropFlags($id, $flags)
	{
		$group = groupGetById($id);

		if (is_array($group))
		{
			$flags1 = $group["flags"];
			$flags1 = stringDropTokens($flags1, $flags);

			global $database_cfg;

			databaseQuery("update " . $database_cfg["prefix"] . "groups set (flags='" . $flags1 . "'", "Can not add group flags");
		} else
		{
			return "Group with given id doesn't exists";
		}
	}
?>
