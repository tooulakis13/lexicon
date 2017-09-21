=== Plugin Name ===
Author: My Language Skills SLU
Contributors: patmir
Tags: vocabulary, lexicon, language, learning, flashcards
Requires at least: 3.5
Tested up to: 3.9.1
Stable tag: 3.9.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Vocabulary learning platform for students and teachers, based on language modules.

== Description ==

This is the long description.  No limit, and you can use Markdown (as well as in the following sections).

For backwards compatibility, if this section is missing, the full length of the short description will be used, and
Markdown parsed.

A few notes about the sections above:

*   "Contributors" is a comma separated list of wp.org/wp-plugins.org usernames
*   "Tags" is a comma separated list of tags that apply to the plugin
*   "Requires at least" is the lowest version that the plugin will work on
*   "Tested up to" is the highest version that you've *successfully used to test the plugin*. Note that it might work on
higher versions... this is just the highest one you've verified.
*   Stable tag should indicate the Subversion "tag" of the latest stable version, or "trunk," if you use `/trunk/` for
stable.

    Note that the `readme.txt` of the stable tag is the one that is considered the defining one for the plugin, so
if the `/trunk/readme.txt` file says that the stable tag is `4.3`, then it is `/tags/4.3/readme.txt` that'll be used
for displaying information about the plugin.  In this situation, the only thing considered from the trunk `readme.txt`
is the stable tag pointer.  Thus, if you develop in trunk, you can update the trunk `readme.txt` to reflect changes in
your in-development version, without having that information incorrectly disclosed about the current stable version
that lacks those changes -- as long as the trunk's `readme.txt` points to the correct stable tag.

    If no stable tag is provided, it is assumed that trunk is stable, but you should specify "trunk" if that's where
you put the stable version, in order to eliminate any doubt.

== Installation ==

1. Upload mls-lexicon to plugin directory and unzip it. Alternatively, upload the zip file using WordPress "Add new plugin" button in admin dashboard.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. It will create sample page entitled 'MLS Lexicon'. Edit it to suit your needs, or create new with [mls_lexicon] shortcode as its content.
4. To begin using the plugin, upload or create Language Modules using Language Modules menu in MLS Lexicon dashboard menu.
5. To upload or create courses, use Courses menu in MLS Lexicon dashboard.
6. To assign roles to existing users, use Word Press user edition.

= Requirements =
* PHP 5.3.0+
* Fileinfo enabled in PHP.ini

== Frequently Asked Questions ==

= Where can I ask a question? =

E-mail us at contact@on-lingua.com

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets 
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png` 
(or jpg, jpeg, gif).
2. This is the second screen shot

== Changelog ==

= 0.25 =
Upgraded DB to v. 1.3

= 0.21 =
Added admin menu Editors
Added early version of language modules menu.
Upgraded DB to v. 1.2
Fixed bugs in DB update function.

= 0.2 =
Added admin menu Students
Added admin menu Teachers
Introduced MLS_Lexicon_List_Table
Upgraded DB to v. 1.1
Intorduced DB update functionality.
Expanded configurable settings.
General clean-up and commenting of code.

= 0.1 =
Initial revision.

== Arbitrary section ==

You may provide arbitrary sections, in the same format as the ones above.  This may be of use for extremely complicated
plugins where more information needs to be conveyed that doesn't fit into the categories of "description" or
"installation."  Arbitrary sections will be shown below the built-in sections outlined above.

== A brief Markdown Example ==

Ordered list:

1. Some feature
1. Another feature
1. Something else about the plugin

Unordered list:

* something
* something else
* third thing

Here's a link to [WordPress](http://wordpress.org/ "Your favorite software") and one to [Markdown's Syntax Documentation][markdown syntax].
Titles are optional, naturally.

[markdown syntax]: http://daringfireball.net/projects/markdown/syntax
            "Markdown is what the parser uses to process much of the readme file"

Markdown uses email style notation for blockquotes and I've been told:
> Asterisks for *emphasis*. Double it up  for **strong**.

`<?php code(); // goes in backticks ?>`
