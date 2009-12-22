<?php
	/**
	 * Database Tables: topics
	 * Table <topics>: id (int, auto_increment),
	 *   parent_id (int), author_id (int),
	 *   title (str), flags (str), created (str),
	 *   viewed (int), rating (int), moderators (str),
		 *   edited (str)
	 */

	require_once 'core.php';
	require_once 'config.php';

	databaseConnect();

	function topicGetByTitle($title)
	{
		global $database_cfg;

		$res = databaseQuery("select * from " . $database_cfg["prefix"] . "topics where title='" . stringEncode($title) . "'");
			
		return $res[0];
	}

	function topicGetById($topic_id)
	{
		global $database_cfg;

		$res = databaseQuery("select * from " . $database_cfg["prefix"] . "topics where id='" . intval($topic_id) . "'", "Can't get topic");
		
		return $res[0];
	}
	
	function topicGetByIdRange($parent_id, $topic_id_min, $topic_id_count)
	{
		global $database_cfg;

		return databaseQuery("select * from " . $database_cfg["prefix"] . "topics where id >= '" . intval($topic_id_min) . "' and parent_id = '" . intval($parent_id) . "' order by id desc limit " . intval($topic_id_count), "Can't get topic list");
	}

	function topicGetByAuthor($author_id)
	{
		global $database_cfg;

		return databaseQuery("select * from " . $database_cfg["prefix"] . "topics where author_id='" . intval($author_id) . "'");
	}

	function topicGetByFlags($flags)
	{
		global $database_cfg;

		return databaseQuery("select * from " . $database_cfg["prefix"] . "topics where flags='" . stringEncode($flags) . "'");
	}

	function topicGetByParent($parent_id)
	{
		global $database_cfg;

		return databaseQuery("select * from " . $database_cfg["prefix"] . "topics where parent_id='" . intval($parent_id) . "'");
	}

	function topicGetByRating($rating)
	{
		global $database_cfg;

		return databaseQuery("select * from " . $database_cfg["prefix"] . "topics where rating='" . intval($rating) . "'");
	}
	
	function topicSearchByTitle($title)
	{
		global $database_cfg;

		$res = databaseQuery("select * from " . $database_cfg["prefix"] . "topics where locate('" . stringEncode($title) . "', title) > 0");
			
		return $res;
	}
	
	function topicSearchByAuthorNickname($nickname)
	{
		global $database_cfg;

		$res = databaseQuery("select * from " . $database_cfg["prefix"] . "topics where author_id in (select id from " . $database_cfg["prefix"] . "users where locate('" . stringEncode($nickname) . "', nickname) > 0)");
			
		return $res;
	}

	function topicSetParams($topic_id, $params)
	{
		$topic = topicGetById($topic_id);

		if (isset($params["title"]))
			$topic["title"] = stringEncode($params["title"]);

		if (isset($params["parent_id"]))
			$topic["parent_id"] = intval($params["parent_id"]);

		databaseQuery("update topics set title='" . $topic["title"] . "', parent_id='" . $topic["parent_id"] . "' where id=" . intval($topic["id"]) . ";", "Failed to update topic");
	}

	function topicExists($title)
	{
		$topic = topicGetByTitle($title);
			
		if (is_array($topic) && isset($topic["id"]))
			return true; else
				return false;
	}

	function topicExistsById($id)
	{
		$topic = topicGetById($id);

		if (is_array($topic) && isset($topic["id"]))
			return true; else
				return false;
	}

	function topicCreate($title, $author_id, $parent_id = -1, $flags = array(), $moderators = array())
	{
		$errors = array();

		if (topicExists($title))
	  		$errors[] = "Topic with this name already exists";

		if (!topicExistsById($parent_id) && $parent_id >= 0)
			$errors[] = "Parent topic not found";

		if (!userExistsById($author_id))
			$errors[] = "Author not found";
		
		$moderators_str = "";
				
		if (count($moderators) > 0)
		{
			foreach ($moderators as $i)
				if (!userExistsById($i))
					$errors[] = "Moderator not found: " . $i; else
						$moderators_str .= $i;
		} else
		{
			$errors[] = "\"Moderators\" is not an array";
		}

		$flags_str = "";
			
		if (count($flags) > 0)
		{
			//-for ($i = 0; $i < count($flags); $i++)
			foreach ($flags as $i)
				if (!in_array($i, $topic_flags))
					$errors[] = "Unknown topic flag: " . $i; else
						$flags_str .= $i;
		} else
		{
			$errors[] = "\"Flags\" is not an array";
		}

		if (count($errors) > 0)
			return $errors;

		databaseQuery("insert into " . $database_cfg["prefix"] . "topics (title, author_id, parent_id, flags, moderators, created) values ('" . stringEncode($title) . "', '" . intval($author_id) . "', '" . intval($parent_id) . "', '" . $flags . "', '" . stringEncode(date("H:i, d.m.Y")) . "')", "Can't create topic");
	}
	
	function topicCreateDiscussion($title, $author_id, $readers)
	{
		$flags = array("private");
		
		foreach ($readers as $i)
			if (is_numeric($i) && userExistsById($i))
				$flags[] = $i;
				
		topicCreate($title, $author_id, -1, $flags);
	}

	function topicDrop($topic_id)
	{
		global $database_cfg;
			
		if (topicExistsById($topic_id))
		   databaseQuery("delete from " . $database_cfg["prefix"] . "topics where id='" . intval($topic_id) . "'", "Can't delete topic");
	}

	function topicGetFlags($topic_id)
	{
		$topic = topicGetById($topic_id);

		return stringTokenize($topic["flags"], "+");
	}

	function topicCheckFlags($topic_id, $flags)
	{
		$topic_flags = topicGetFlags($topic_id);

		return stringCheckForTokens($topic_flags, $flags);
	}

	function topicAddFlags($topic_id, $flags)
	{
		global $database_cfg;

		$topic_flags = topicGetFlags($topic_id);

		$topic_flags = stringAddTokens($topic_flags, $flags);

		databaseQuery("update " . $database_cfg["prefix"] . "topics set flags='" . $topic_flags . "' where id='" . intval($topic_id) . "'", "Can't set topic flags");
	}

	function topicDropFlags($topic_id, $flags)
	{
		global $database_cfg;

		$topic_flags = topicGetFlags($topic_id);

		stringDropTokens($topic_flags, $flags);

		databaseQuery("update " . $database_cfg["prefix"] . "topics set flags='" . $topic_flags . "' where id='" . intval($topic_id) . "'", "Can't set topic flags");
	}

	function topicGetModerators($topic_id)
	{
		$topic = topicGetById($topic_id);

		return stringTokenize($topic["moderators"], "+");
	}
	
	function topicGetModeratorsString($topic_id)
	{
		$topic = topicGetById($topic_id);

		return $topic["moderators"];
	}

	function topicCheckModerators($topic_id, $users)
	{
		$topic_flags = topicGetModerators($topic_id);

		$res = array();
		
		for ($i = 0; $i < count($users); $i++)
			$res[] = in_array($users[$i], $topic_flags);
		
		return $res;
	}

	function topicDropModerators($topic_id, $users)
	{
		global $database_cfg;

		$topic_moders = topicGetModeratorsString($topic_id);

		$topic_moders = stringDropTokens($topic_moders, $users);

		databaseQuery("update " . $database_cfg["prefix"] . "topics set moderators='" . $topic_moders . "' where id='" . intval($topic_id) . "'", "Can't set topic moderators");
	}

	function topicAddModerators($topic_id, $users)
	{
		global $database_cfg;

		$topic_moders = topicGetModeratorsString($topic_id);

		$topic_moders = stringAddTokens($topic_moders, $users);

		databaseQuery("update " . $database_cfg["prefix"] . "topics set moderators='" . $topic_moders . "' where id='" . intval($topic_id) . "'", "Can't set topic moderators");
	}
?>
