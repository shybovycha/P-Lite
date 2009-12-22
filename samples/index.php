<?php
	require_once "../P-Lite/core.php";
	require_once "../P-Lite/groups.php";
	require_once "../P-Lite/posts.php";
	require_once "../P-Lite/topics.php";
	require_once "../P-Lite/users.php";

	userLogIn();

	function handleErrors($res)
	{
		if (is_array($res))
		{
			foreach ($res as $i)
				echo "<b>Error: </b>" . $i . "<br />";

			exit;
		}
	}

	if (isset($_GET["droppost"]))
	{
		$post = postGetById(intval($_GET["post_id"]));
		$user = userGetLoggedIn();

		if ((topicCheckModerator(intval($post["topic_id"]), $user["id"]) || userCheckAdministrator($user["id"])) && postExistsById(intval($_GET["post_id"])))
			postDrop(intval($_GET["post_id"]));
			
		header("location: index.php?topic_id=" . intval($post["topic_id"]));
	}
	
	if (isset($_GET["changepost"]))
	{
		$post = postGetById(intval($_GET["post_id"]));
		
		postSetParams(intval($_GET["post_id"]), array("message" => $_POST["message"]));
		
		header("location: index.php?topic_id=" . intval($post["topic_id"]));
	}

	if (isset($_GET["modusr"]))
	{
		$user = userGetLoggedIn();
		$params = array();

		if (isset($_POST["pass1"]) && isset($_POST["pass2"]) && isset($_POST["password"]) && $_POST["pass1"] == $_POST["pass2"] && md5(stringEncode($_POST["password"])) == $user["password"])
			$params["password"] = $_POST["pass1"];

		if (isset($_POST["nickname"]) && $_POST["nickname"] != stringDecode($user["nickname"]))
			$params["nickname"] = $_POST["nickname"];

		if (isset($_POST["email"]) && $_POST["email"] != stringDecode($user["email"]))
			$params["email"] = $_POST["email"];

		userSetParams($user["id"], $params);

		header("location: index.php?profile");
	}

	if (isset($_GET["login"]))
	{
		$res = userLogIn($_POST["login"], $_POST["password"], isset($_POST["remember"]));

		handleErrors($res);

		header("location: index.php");
	}

	if (isset($_GET["logout"]))
	{
		userLogOut();

		header("location: index.php");
	}

	if (isset($_GET["cap"]))
	{
		$x1 = rand(2, 50);
		$x2 = rand(1, $x1 - 1);

		$rnd = rand(0, 3);

		if ($rnd == 0)
			$operation = " - ";
		elseif ($rnd == 1)
			$operation = " * ";
		else
			$operation = " + ";

		$expression = $x1 . " " . $operation . " " . $x2;
		echo $expression . "<input type=\"hidden\" name=\"code\" value=\"" . stringEncode($expression) . "\" />";

		exit;
	}

	if (isset($_GET["regusr"]))
	{
		$a = explode(" ", stringDecode($_POST["code"]));

		for ($i = 0; $i < count($a); $i++)
			if ($a[$i] == "")
				unset($a[$i]);

		$a = array_values($a);

		$res = intval($a[0]);

		if ($a[1] == "+")
			$res += intval($a[2]);
		elseif ($a[1] == "-")
			$res -= intval($a[2]);
		else
			$res *= intval($a[2]);

		$res = userRegister($_POST["username"], $_POST["password1"], $_POST["password2"], $_POST["email1"], $_POST["email2"], $_POST["captcha_res"], $res, $_POST["nickname"]);

		handleErrors($res);
	}

	if (isset($_GET["reply"]))
	{
		$user = userGetLoggedIn();

		$res = postCreate($_POST["topic"], $user["id"], $_POST["message"]);

		handleErrors($res);

		header("location: index.php?topic_id=" . $_POST["topic"]);
	}

	if (isset($_GET["newtopic"]))
	{
		$user = userGetLoggedIn();

		if (isset($_POST["parent"]))
			$parent = $_POST["parent"]; else
				$parent = -1;

		$res = topicCreate($_POST["title"], $user["id"], $parent);

		handleErrors($res);

		$topic = topicGetByTitle($_POST["title"]);

		$res = postCreate($topic["id"], $user["id"], $_POST["message"]);

		handleErrors($res);

		header("location: index.php?topic_id=" . $topic["id"]);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1251" />

	<style type="text/css">
		@import "stylesheet.css";
	</style>

	<script src="jquery-1.3.2.js"></script>

	<title>SiteName</title>
</head>

<body>

<div id="wrapper">

 <img src="images/header.jpg" alt="header">

	<div class="topbar">
    	<br />Forum sample<br /><br />
    </div>
	<div class="topbar_bottom"></div>


	<div id="menu_container">

		<div class="menu">

			<p class="item_top">Menu</p>
			<ul>
				<a href="index.php"><li>Home</li></a>

				<?php
					$user = userGetLoggedIn();

					if (!userExistsById($user["id"]))
						echo "<a href=\"index.php?regform\"><li>Register</li></a>";
				?>

				<a href="index.php?srchform"><li>Search</li></a>
			</ul>

		</div>

	<div class="menu_bottom"></div>

	<div class="menu">

			<?php

				$user = userGetLoggedIn();

				if (!userExistsById($user["id"]))
				{
					echo "<p class=\"item_top\">Login Form</p>";
					echo "<ul>";
						echo "<form action=\"index.php?login\" method=\"post\">";
							echo "<input type=\"text\" name=\"login\" style=\"width: 105px;\" />";
							echo "<input type=\"password\" name=\"password\" style=\"width: 105px;\" />";
							echo "<input type=\"checkbox\" name=\"remember\">Rememer me</input>";
							echo "<input type=\"submit\" value=\"login\" />";
						echo "</form>";
					echo "</ul>";
				} else
				{
					echo "Welcome back, " . stringDecode($user["nickname"]) . "<br /><br />";
					echo "Your last visit: " . stringDecode($user["last_visit"]) . "<br /><br />";
					echo "<a href=index.php?profile>Profile</a><br />";
					echo "<a href=index.php?logout>Logout</a>";
				}
			?>
		</div>
		<div class="menu_bottom"></div>
	</div>

	<div id="content_container">
		<div class="content">

			<?php
				if (isset($_GET["editpost"]))
				{
					$post = postGetById(intval($_GET["post_id"]));

					if (postExistsById($post["id"]))
					{
						echo "<form action=\"index.php?changepost&post_id=" . $_GET["post_id"] . "\" method=\"post\">";
							echo "Message:<br /><textarea name=\"message\">" . stringDecode($post["message"]) . "</textarea><br /><br />";
							echo "<input type=\"submit\" value=\"Save\" />";
						echo "</form>";
					}
				} else
				if (isset($_GET["find"]))
				{
					$res2 = topicSearchByTitle(stringEncode($_POST["query"]));

					$res4 = postSearchByMessage(stringEncode($_POST["query"]));

					$res5 = userSearchByEmail(stringEncode($_POST["query"]));
					$res6 = userSearchByNickname(stringEncode($_POST["query"]));

					$res7 = groupSearchByName(stringEncode($_POST["query"]));

					$user = userGetLoggedIn();

					echo "<h1>Search results</h1><br />";


					echo "<br /><p class=\"item_top\">Topic search results: " . count($res2) . "</p>";

					if (count($res2) > 0)
						foreach ($res2 as $i)
							if (!topicCheckPrivate($i["id"]))
								echo "<a href=\"index.php?topic_id=" . $i["id"] . "\">" . stringDecode($i["title"]) . "</a><br /><br />";


					echo "<br /><p class=\"item_top\">Post search results: " . count($res4) . "</p>";

					if (count($res4) > 0)
						foreach ($res4 as $i)
							if (!topicCheckPrivate($i["topic_id"]))
								echo "<a href=\"index.php?topic_id=" . $i["topic_id"] . "\">" . stringDecode($i["message"]) . "</a><br /><br />";


					echo "<br /><p class=\"item_top\">Group search results: " . count($res7) . "</p>";

					if (count($res7) > 0)
						foreach ($res7 as $i)
							echo "<a href=\"index.php?group_id=" . $i["id"] . "\">" . stringDecode($i["name"]) . "</a><br /><br />";


					echo "<br /><p class=\"item_top\">User email search results: " . count($res5) . "</p>";

					if (count($res5) > 0)
						foreach ($res5 as $i)
							echo "<a href=\"index.php?user_id=" . $i["id"] . "\">" . stringDecode($i["email"]) . "</a><br /><br />";


					echo "<br /><p class=\"item_top\">User nickname search results: " . count($res6) . "</p>";

					if (count($res6) > 0)
						foreach ($res6 as $i)
							echo "<a href=\"index.php?user_id=" . $i["id"] . "\">" . stringDecode($i["nickname"]) . "</a><br /><br />";
				} else
				if (isset($_GET["srchform"]))
				{
					echo "<form action=\"index.php?find\" method=\"post\">";
						echo "Query: <br /> <input type=\"text\" name=\"query\" /><br />";

						echo "<input type=\"submit\" value=\"search\" />";
					echo "</form>";
				} else
				if (isset($_GET["regform"]))
				{
					echo "<form action=\"index.php?regusr\" method=\"post\">";
						echo "Username (used for logging in):<br /> <input type=\"text\" name=\"username\" /><br />";
						echo "Password:<br /> <input type=\"password\" name=\"password1\" /><br />";
						echo "Password (once more):<br /> <input type=\"password\" name=\"password2\" /><br />";
						echo "Nickname (showed in your profile):<br /> <input type=\"text\" name=\"nickname\" /><br />";
						echo "Your e-mail address:<br /> <input type=\"text\" name=\"email1\" /><br />";
						echo "Repeat your e-mail:<br /> <input type=\"text\" name=\"email2\" /><br />";
						echo "<h1><a id=\"foo\" href=\"javascript: void(0);\" onClick=\"javascript: $('#foo').load('index.php?cap');\">CLICK ME!</a></h1>";
						echo "Result (solve problem upper):<br /> <input type=\"text\" name=\"captcha_res\" /><br />";

						echo "<input type=\"submit\" value=\"register\" />";
					echo "</form>";
				} else
				if (isset($_GET["topic_id"]))
				{
					$user = userGetLoggedIn();

					if (topicExistsById(intval($_GET["topic_id"])) && topicCheckReader($_GET["topic_id"], $user["id"]))
					{
						$topic = topicGetById(intval($_GET["topic_id"]));
						$posts = postGetByTopicId(intval($topic["id"]));

						echo "<h1>" . stringDecode($topic["title"]) . "</h1>";
						
						if (topicCheckModerator($topic["id"], $user["id"]) || userCheckAdministrator($user["id"]))
							echo "<a href=\"index.php?edittopic&topic_id=" . $_GET["topic_id"] . "\">Edit topic</a>";

						$topics = topicGetByIdRange($_GET["topic_id"], 0, 5);

						if (count($topics) > 0)
						{
							echo "<br /> <br /> <p class=\"item_top\"> <b> Sub-topics: </b> </p> <br />";

							for ($i = 0; $i < count($topics); $i++)
								echo "<a href=\"index.php?topic_id=" . intval($topics[$i]["id"]) . "\">" . stringDecode($topics[$i]["title"]) . "</a> <br />";
						}

						echo "<br /> <br /> <p class=\"item_top\"> <b> Topic posts: </b> </p> <br />";

						for ($i = 0; $i < count($posts); $i++)
						{
							$author = userGetById($posts[$i]["author_id"]);

							if (topicCheckModerator($topic["id"], $user["id"]) || userCheckAdministrator($user["id"]) || $posts[$i]["author_id"] == $user["id"])
								$actions = "<a href=\"index.php?editpost&post_id=" . $posts[$i]["id"] . "\">[Edit]</a>  <a href=\"index.php?droppost&post_id=" . $posts[$i]["id"] . "\">[Delete]</a>";

							echo "<p class=\"item_top\">" . stringDecode($posts[$i]["created"]) . ", by <a href=\"index.php?user_id=" . $author["id"] . "\">" . stringDecode($author["nickname"]) . "</a> " . $actions . " </p>";
							
							echo chunk_split(stringDecode($posts[$i]["message"]));
								
							echo "<br /><br />";
						}

						if (userExistsById($user["id"]))
						{
							echo "<p class=\"item_top\">Post reply</p>";
								echo "<form action=\"index.php?reply\" method=\"post\">";
									echo "<textarea name=\"message\"></textarea><br />";
									echo "<input type=\"hidden\" name=\"topic\" value=\"" . intval($_GET["topic_id"]) . "\" />";
									echo "<input type=\"submit\" value=\"post\" />";
								echo "</form>";
							echo "<br /><br />";

							echo "<br /><br />";
							echo "<p class=\"item_top\">Create new sub-topic</p>";
								echo "<form action=\"index.php?newtopic\" method=\"post\">";
									echo "Title:<br /><input type=\"text\" name=\"title\" /><br />";
									echo "Message:<br /><textarea name=\"message\"></textarea><br />";
									echo "<input type=\"hidden\" name=\"parent\" value=\"" . $_GET["topic_id"] . "\" /><br />";
									echo "<input type=\"submit\" value=\"create\" />";
								echo "</form>";
							echo "<br /><br />";
						}
					} else
					{
						echo "Topic not found";

						$user = userGetLoggedIn();

						if (userExistsById($user["id"]))
						{
							echo "<br /><br />";
							echo "<p class=\"item_top\">Create new topic</p>";
								echo "<form action=\"index.php?newtopic\" method=\"post\">";
									echo "Title:<br /><input type=\"text\" name=\"title\" /><br />";
									echo "Message:<br /><textarea name=\"message\"></textarea><br />";
									echo "<input type=\"submit\" value=\"create\" />";
								echo "</form>";
							echo "<br /><br />";
						}
					}
				} else
				if (isset($_GET["profile"]))
				{
					$user = userGetLoggedIn();
					$topics = topicGetByAuthor($user["id"]);

					echo "<h1>" . stringDecode($user["nickname"]) . "</h1><br />";
					echo "<form action=\"index.php?modusr\" method=\"post\">";
						echo "Nickname:<br /><input type=\"text\" name=\"nickname\" value=\"" . stringDecode($user["nickname"]) . "\" /><br /><br />";
						echo "E-mail:<br /><input type=\"text\" name=\"email\" value=\"" . stringDecode($user["email"]) . "\" /><br /><br />";
						echo "Current password:<br /><input type=\"password\" name=\"password\" /><br /><br />";
						echo "New password:<br /><input type=\"password\" name=\"pass1\" /><br /><br />";
						echo "Confirm new password:<br /><input type=\"password\" name=\"pass2\" /><br /><br />";
						echo "<input type=\"submit\" value=\"save\" />";
					echo "</form>";

					if (count($topics) > 0)
					{
						echo "<p class=\"item_top\">Your topics:</p>";

						foreach ($topics as $i)
							echo "<a href=\"index.php?topic_id=" . $i["id"] . "\">" . stringDecode($i["title"]) . "</a><br />";
					}
				} else
				if (isset($_GET["user_id"]))
				{
					if (userExistsById($_GET["user_id"]))
					{
						$user = userGetById($_GET["user_id"]);
						$topics = topicGetByAuthor($user["id"]);

						echo "<h1>" . stringDecode($user["nickname"]) . "</h1><br />";
						echo "E-mail:<br />" . stringDecode($user["email"]) . "<br /><br />";
						echo "Last visit:<br />" . stringDecode($user["last_visit"]) . "<br /><br />";
						echo "Karma:<br />" . intval($user["rating"]) . "<br /><br />";

						if (count($topics) > 0)
						{
							echo "<p class=\"item_top\">User's topics:</p>";

							foreach ($topics as $i)
								if (!topicCheckPrivate($i["id"]))
									echo "<a href=\"index.php?topic_id=" . $i["id"] . "\">" . stringDecode($i["title"]) . "</a><br />";
						}
					} else
					{
						echo "<b>Error:</b> user not found";
					}
				} else
				{
					$topics = topicGetByIdRange(-1, 0, 5);

					for ($i = 0; $i < count($topics); $i++)
						if (!topicCheckPrivate($topics[$i]["id"]) || topicCheckReader($topics[$i]["id"], $user["id"]))
							echo "<a href=\"index.php?topic_id=" . intval($topics[$i]["id"]) . "\">" . stringDecode($topics[$i]["title"]) . "</a> <br />";

					$user = userGetLoggedIn();

					if (userExistsById($user["id"]))
					{
						echo "<br /><br />";
						echo "<p class=\"item_top\">Create new topic</p>";
							echo "<form action=\"index.php?newtopic\" method=\"post\">";
								echo "Title:<br /><input type=\"text\" name=\"title\" /><br />";
								echo "Message:<br /><textarea name=\"message\"></textarea><br />";
								echo "<input type=\"submit\" value=\"create\" />";
							echo "</form>";
						echo "<br /><br />";
					}
				}
			?>

			<br />

		</div>

	<div class="content_bottom"></div>
	<div class="content">
	<br />
	<center>Ads/Content Here</center><br />
	</div>
	<div class="content_bottom"></div>
	<br />
		<!-- START OF ZYMIC.COM COPYRIGHT, DO NOT REMOVE OR EDIT ANYTHING BELOW WITHOUT PAYING LICENSE FEE (ELSE FACE LEGAL ACTION) -->
		<p style="color: #fff; margin: 0; width: auto; text-align: center;">	Copyright &copy; 2007 <a href="http://www.zymic.com/free-templates/">Free Templates</a> by <a href="http://www.zymic.com">Zymic</a> - <a href="http://www.zymic.com/free-web-hosting/">Free Web Hosting</a> - Best Viewed in <a href="http://www.mozillafirefox.us">Mozilla Firefox</a></p>
	    <!-- END ZYMIC.COM COPYRIGHT -->
	</div>
</div>


</body>
</html>
