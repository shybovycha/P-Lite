==== P-Lite ====

Contents

1. Short introduction
2. Few examples
3. FAQ
4. TO-DO
5. Contacts

== 1. Short Introduction ==

 P-Lite is a PHP and MySQL-based CMS (Content Management System)
framework, aimed to be easy-in-use and easy-in-extension. It provides a middle-level API
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

 Here are some examples on how to use P-Lite.

 But first of all let me tell you a few words 'bout P-Lite API.

 P-Lite consists of just 6 main files (for now). These files hold all functions
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
functions. For example, if you want to use P-Lite registration system, you'll
probably won't include "topics.php" into your source code.

 Now i'll describe P-Lite final data representation. Let's imagine that you've
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

 Forum sample you can find in the samples/ directory. I have taken one of Zymic 
free Web Templates and just pasted some PHP code using P-Lite in the "index-blank.php"
(I've changed extension from .html to .php).

== 4. TO-DO ==

 * Create CAPTCHA generator
 * JavaScript Text Editor + BBCode Parser + Syntax Highlighter
 * Plugin system
