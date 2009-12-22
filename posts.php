<?php
	/**
	 * Database Tables: posts
	 * Table <posts>: id (int, auto_increment),
	 *   topic_id (int), author_id (int),
	 *   message (text), rating (int), created (str),
	 *   flags (str), edited (str)
	 */

	require_once 'config.php';
		
	require_once 'core.php';
	require_once 'users.php';
	require_once 'topics.php';
	
	databaseConnect();

	function postGetById($id)
	{
		global $database_cfg;

		$res = databaseQuery("select * from " . $database_cfg["prefix"] . "posts where id='" . stringEncode($id) . "'", "Post not found");
		
		return $res[0];
	}
		
	function postGetByAuthorId($author_id)
	{
		global $database_cfg;

		return databaseQuery("select * from " . $database_cfg["prefix"] . "posts where author_id='" . stringEncode($author_id) . "'");
	}
	
	function postGetByTopicId($topic_id)
	{
		global $database_cfg;

		return databaseQuery("select * from " . $database_cfg["prefix"] . "posts where topic_id='" . stringEncode($topic_id) . "'");
	}
		
	function postGetByRating($rating)
	{
		global $database_cfg;

		return databaseQuery("select * from " . $database_cfg["prefix"] . "posts where rating='" . stringEncode($rating) . "'");
	}
	
	function postSearchByMessage($message)
	{
		global $database_cfg;

		$res = databaseQuery("select * from " . $database_cfg["prefix"] . "posts where locate('" . stringEncode($message) . "', message) > 0");
		
		return $res;
	}
	
	function postSearchByAuthorNickname($nickname)
	{
		global $database_cfg;

		$res = databaseQuery("select * from " . $database_cfg["prefix"] . "posts where author_id in (select id from " . $database_cfg["prefix"] . "users where locate('" . stringEncode($nickname) . "', nickname) > 0)");
		
		return $res;
	}

	function postExists($topic_id, $message)
	{
		global $database_cfg;

		$posts = databaseQuery("select * from " . $database_cfg["prefix"] . "posts where message='" . stringEncode($message) . "' and topic_id='" . intval($topic_id) . "'");

		if (is_array($posts) && is_array($posts[0]))
			return true; else
				return false;
	}
	
	function postExistsById($id)
	{
		$post = postGetById($id);
		
		if (is_array($post) && isset($post['id']))
			return true; else
				return false;
	}
	
	function postCreate($topic_id, $author_id, $message, $flags = array())
	{
		global $database_cfg;
			
		$errors = array();
		  
		if (!userExistsById($author_id))
			$errors[] = "User marked as author (" . $author_id . ") not found";

		if (!topicExistsById($topic_id))
			$errors[] = "Topic marked as parent not found";

		if (postExists($topic_id, $message))
			$errors[] = "This post already exists in this topic";

		if (count($errors) > 0)
			return $errors;
			
		$flags_str = "";
		
		if (is_array($flags))
			$flags_str = stringAddTokens($flags_str, $flags);

		databaseQuery("insert into " . $database_cfg["prefix"] . "posts (topic_id, author_id, message, flags, created) values ('" . intval($topic_id) . "', '" . intval($author_id) . "', '" . stringEncode($message) . "', '" . $flags_str . "', '" . stringEncode(date("H:i, d.m.Y")) . "')");
		databaseQuery("update " . $database_cfg["prefix"] . "topics set edited='" . stringEncode(date("H:i, d.m.Y")) . "' where id='" . intval($topic_id) . "'", "Can't update topic");
	}
	
	function postRate($post_id, $points)
	{
		global $database_cfg;
			
		$post = postGetById($post_id);
		$rating = intval($post["rating"]) + intval($points);
			
		databaseQuery("update " . $database_cfg["prefix"] . "posts set rating='" . $rating . "' where id='" . intval($post_id) . "'", "Can't update post");
	}
	
	function postSetParams($post_id, $params)
	{
		global $database_cfg;
		
		if (!is_array($params))
			return "Wrong parameters type";
		
		$post = postGetById($post_id);
		
		if (isset($params["topic"]))
			$post["topic_id"] = intval($params["topic"]);
			
		if (isset($params["message"]))
			$post["message"] = stringEncode($params["message"]);
			
		if (isset($params["flags"]))
			$post["flags"] = $params["flags"];
			
		databaseQuery("update " . $database_cfg["prefix"] . "posts set topic_id='" . $post['topic_id'] . "', edited='" . stringEncode(date("H:i, d.m.Y")) . "', message='" . $post['message'] . "', flags='" . $post['flags'] . "' where id='" . intval($post_id) . "'", "Can't update post");
	}

	function postDrop($post_id)
	{
		global $database_cfg;

		if (postExistsById($post_id))
			databaseQuery("delete from " . $database_cfg["prefix"] . "posts where id='" . intval($post_id) . "'", "Can't delete post");
	}
?>
