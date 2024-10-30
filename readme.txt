=== Content Molecules ===
Contributors: marcuspope
Donate link: http://www.marcuspope.com/
Tags: custom content, dynamic content, posts, page, content, cms, dry, reusable, shortcode
Requires at least: 3.2.1
Tested up to: 3.2.1
Stable tag: trunk

Enables the creation of reusable and dynamic content that can be embedded throughout the Wordpress platform via shortcodes.

== Description ==

In the web marketing/publishing industry a content molecule is basically a piece of reusable content that can be placed throughout a website.  Typically isolated in nature, they often appear in sidebars and margins.  Applying this concept to the Wordpress framework, you can create content that can be embedded anywhere shortcodes are processed.  Taking the concept a little further, any number of custom attributes can be added to the usage of the shortcode, and those values can be embedded in the resulting output.

[m id="slug" audience="Student" product="backpack"]

Will translate the following molecule:

Greetings {audience}!
Check out our new {product} today!

into this:

Greetings Student!
Check out our new backpack today!

Obviously a ridiculous example, but the flexibility remains the same regardless of the silly intent.  Stop repeating content throughout your site only to have to re-edit those pieces in every location when you need to make a correction or update.

Molecules are a custom post type.  After the plugin is activated a new section will appear in the left admin menu titled "Molecules."  There you can create and manage reusable pieces of content.  As a bonus I have added the post slug to the list view for easy reference.  If you embed a molecule in a post or page and the slug does not exist yet, it will create  a draft for you to fill in later.  

That's about it for now, any input for future features is welcome.

== Installation ==

1. Upload the plugin contents to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in Wordpress Admin
1. You're done!

== Frequently Asked Questions ==

Nobody has asked me anything so, as soon as they do I'll add some points here!

== Upgrade Notice ==

1. No upgrade notices

== Screenshots ==

1. No screenshots

== Changelog ==

= 1.3 =
* Improved save performance: was calling reset transients twice on save.

= 1.2 =
* Fixed two bugs: invalid var reference
                  forgot to update the version tag for my last update :(
= 1.1 =
* Fixed two bugs: potential overwrite of other custom properties added to view all list.
                  loaded molecule specific filters are all custom post types.
= 1.0 =
* Initial creation of plugin

== Arbitrary section ==