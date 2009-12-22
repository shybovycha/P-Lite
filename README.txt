==== Tiny-Miny CMS ====

Contents

1. Short introduction
2. Few examples
3. FAQ
4. TO-DO
5. Contacts

== 1. Short Introduction ==

 Tiny-Miny CMS (TMCMS) is a PHP and MySQL-based CMS (Content Management System)
aimed to be easy-in-use and easy-in-extension. It provides a middle-level API
ready to use for creating websites. It's named though its simplicity and
lightweight.

 This project currently marked as WIP (work-in-progress) but alpha version
will be avaliable for public soon.


-- Currently supported and avaliable in alpha version features: --

* User management (registration, CAPTCHA, extendable userfield interface,
  private messaging, flag-based options, carma)

* User groups (attaching users to groups, group flags)

* Forum (topic and post management, tree-based system, ratings, topic/post flags,
  viewed/changed counters, topic-based private messaging)

== 2. Few examples ==

 Here are some examples on how to use TMCMS.

 But first of all let me tell you a few words 'bout TMCMS API.

 TMCMS consists of just 6 main files (for now). These files hold all functions
you'll need to create your site (let's call it "Web-application" for now).

 -- They are: --

* config.php - configuration file. It contains database connection options,
  all possible flags and some other information that is not recommended
  to be hold in the database.

* core.php - some utilite functions. Performs string operations and suitable
  database access.

* groups.php - user group management interface.

* posts.php - forum post handling functions. Provides access to database for
  creating and manipulating posts data.

* topics.php - forum topic management interface. Contains functions which
  provide safe and easy manipulation of topic data.

* users.php - user management interface. Contains functions that allow you to
  register and manage users.

 You don't need to include all these files into your application to use few
functions. For example, if you want to use TMCMS registration system, you'll
probably won't include "topics.php" into your source code.

 Now i'll describe TMCMS final data representation. Let's imagine that you've
just created some data unit (user, topic or post). You have database, which
contains all records, including those you've created few minutes ago, your
site's face page (let's name it "index.php") and this API. And you want to
retrieve list of all data units (of the same type as you created before).

 If you have got new topic unit and want to see all topics you have, just call
 @ topicGetById($id); @ function to retrieve concrete topic or
 @ topicGetByIdRange($start, $count); @ function to get list of all $count topics
which have `id` >= $start. The same functions for users, posts and groups exist
(but their suffices will be different: user-, post- and group- accordingly).

 These functions will return two-dimensional array. Array row will represent
index and array column will represent database column, like this:

 $array[$record_number][$database_column];

 But be carefull! If there was an error during function execution, you'll get
zero (or an error array) and wouldn't be able to use the result as a 2D array.
So, please, make sure that function returned array. This can be easily checked
using PHP built-in function @ is_array($variable); @. It will return @ true @
if $variable is an array and @ false @ in other cases. 

 If you're unsure that function returned 2D array, just check if first result
array item is an array too.

 Well, enough of words, here we go!

* Registering new user

                // Try to register a new user using posted data
                $res = userRegister($_POST['username'], $_POST['password1'], $_POST['password2'],
			$_POST['email1'], $_POST['email2'], $_POST['captcha_result'], $_POST['captcha_sample'],
			$_POST['nickname']);

                // If there are any errors - just show 'em up and ask user to register once more
		if (is_array($res))
		{
			for ($i = 0; $i < count($res); $i++)
				echo $res[$i] . "<br />";

                        // Show registration form again
		} else
		{
			echo "Register successful! =)";
		}

* Retrieving paged topic list (parent topic id == -1 which means "root topics")

            // Get all 10 topics, which have topic(-1) parent and id >= 5
            $res = topicGetByIdRange(-1, 5, 10);

            // Show topic list
            // Look! Here we checked for 2D array
            if (is_array($res) && is_array($res[0]))
            {
                for ($i = 0; $i < count($res); $i++)
                    // For safety and flexibility, almoust all big text data
                    // is URL-encoded. Topic titles, post messages, user
                    // nicknames and others are encoded too.
                    // Here we used core utility function @ stringDecode(...); @
                    // to decode topic title and make it look clearly.
                    echo stringDecode($res[$i]["title"]) . '<br />';
            } else
            // If we got 1D array - it is error or info message list
            if (is_array($res) && !is_array($res[0]))
            {
                for ($i = 0; $i < count($res); $i++)
                    echo $res[$i] . '<br />';
            }

 * Creating new topic:

            // Create topic with "Woo" title, user(15) author, root topic parent
            // and "blog" flag
            $res = topicCreate("Woo", 15, -1, "+blog");

            // Handle topic creation errors
            // Look! Here we checked for 1D array, 'cause normally
            // @ topicCreate(...); @ returns zero. In case of errors it returns
            // error list.
            if (is_array($res))
                for ($i = 0; $i < count($res); $i++)
                    echo $res[$i] . '<br />';

 * Displaying topic content:

            // If we were given URL like this: http://localhost/index.php?topic_id=XXX
            // where XXX is some number, we can use it to select concrete
            // topic from database and show its content. @ topic_id @ is
            // given in @ $_GET[] @ array.
            $topic = topicGetById(intval($_GET["topic_id"]));

            if (is_array($topic) && is_array($topic[0]))
            {
                // Here we just show our topic
                echo stringDecode($topic[0]["title"]);

                // Display topic posts
                $posts = postGetByTopicId($_GET['topic_id']);

                if (is_array($posts) && is_array($posts[0]))
                {
                    for ($i = 0; $i < count($posts); $i++)
                    {
                        $author = userGetById($posts[$i]['author_id']);
                        $creation_date = stringDecode($posts[$i]['created']);
                        $message = $posts[$i]['message'];

                        echo $message . '<br /><b>Created on </b>' . $creation_date . ' by ' . $author['name'];
                    }
            } else
            {
                // No topics with given ID exist...
                echo "Topic not found";
            }


 * Adding topic post

        // Simple check, if user have sent message
        if (isset($_GET['post']) && isset($_GET['topic']) && isset($_POST['message']))
        {
            // Try to create post
            $res = postCreate($_GET['topic'], $_POST['author'], $_POST['message'], "");

            // Handle possible errors
            if (is_array($res))
            {
                for ($i = 0; $i < count($res); $i++)
                    echo $res[$i] . '<br />';
            } else
            // Redirecting to index page
            {
                header("location:index.php?topic_id=" . $_GET['topic']);
            }
        }

== 4. TO-DO ==

 * Test "Topics" and "Posts" units
 * Create CAPTCHA generator
 * JavaScript Text Editor + BBCode Parser + Syntax Highlighter
 * User, Topic, Post, Group search