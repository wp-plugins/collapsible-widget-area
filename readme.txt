=== Collapsible Widget Area ===
Contributors: cgrymala
Donate link: http://giving.umw.edu/
Tags: tab, accordion, widget, jquery, tabbed
Requires at least: 3.3
Tested up to: 3.5
Stable tag: 0.3.1a

Creates a tabbed or accordion-style widget that can hold other widgets.

== Description ==

This plugin allows you to combine multiple widgets into a single tabbed/accordion-style interface in your theme.

= Usage =

To use this plugin, simply drag widgets into the "Collapsible Widget Area" widget area in Appearance -&gt; Widgets within your admin area. Once you've done that, reload the Appearance -&gt; Widgets page and drag the Collapsible Widget into the sidebar in which you want it to appear.

= Options =

1. Within the Collapsible Widget itself, you can specify whether you want to use a Tab interface or an Accordion interface.
2. Under Settings -> Collapsible Widget Options, you can choose the jQueryUI theme you want to apply to the collapsible widget container. All of the included jQueryUI themes are hosted on Google's CDN. If you already have a jQueryUI theme included in your WordPress theme, you can choose "None" from the option selector, and no extra stylesheet will be included for this widget. If you want to include your own jQueryUI theme, there are two ways you can do so:
    1. Use the collapsible-widget-ui-theme filter to specify the exact URI of the stylesheet you want to include.
    2. Use the collapsible-widget-theme-list filter to add or remove items from the list of available themes. The parameter sent through this filter is an associative array of the available themes. If the theme is hosted on Google's CDN, just the keyword is needed as the array key (for instance, the keyword for the "UI Lightness" theme is "ui-lightness" and the keyword for the "Base" theme is "base"). If the theme is hosted elsewhere, the entire URI to the stylesheet should be used as the array key. The array value should be the human-readable name of the theme.
3. Under Settings -> Collapsible Widget Options, you can specify how many separate Collapsible Widget Areas should be available within Appearance -> Widgets.

== Installation ==

= Automatic Installation =

The easiest way to install this plugin automatically from within your administration area.

1. Go to Plugins -&gt; Add New in your administration area, then search for the plugin "Collapsible Widget Area".
1. Click the "Install" button.
1. Go to the Plugins dashboard and "Activate" the plugin (for MultiSite users, you can safely "Network Activate" this plugin).

= Manual Installation =

If that doesn't work, or if you prefer to install it manually, you have two options.

**Upload the ZIP**

1. Download the ZIP file from the WordPress plugin repository.
1. Go to Plugins -&gt; Add New -&gt; Upload in your administration area.
1. Click the "Browse" (or "Choose File") button and find the ZIP file you downloaded.
1. Click the "Upload" button.
1. Go to the Plugins dashboard and "Activate" the plugin (for MultiSite users, you can safely "Network Activate" this plugin).

**FTP Installation**

1. Download the ZIP file from the WordPress plugin repository.
1. Unzip the file somewhere on your harddrive.
1. FTP into your Web server and navigate to the /wp-content/plugins directory.
1. Upload the collapsible-widget-area folder and all of its contents into your plugins directory.
1. Go to the Plugins dashboard and "Activate" the plugin (for MultiSite users, you can safely "Network Activate" this plugin).

= Must-Use Installation =

If you would like to **force** this plugin to be active (generally only useful for Multi Site installations) without an option to deactivate it, you can upload the contents of the collapsible-widget-area folder to your /wp-content/mu-plugins folder. If that folder does not exist, you can safely create it.

== Frequently Asked Questions ==

= Will this plugin work in versions of WordPress older than 3.3? =

There is limited support for the tabbed interface in WordPress 3.2.x, but the accordion interface does not work.

= My widget title should have a link/image, but it doesn't. Why not? =

In order to create the handles for the accordions/tabs, the titles have to be wrapped in links to those items. Since you can't wrap a link inside of a link, this plugin strips any HTML from the widget title before rendering the collapsible interface.

= Does this work on multisite installations? =

Yes. This plugin is set up to work in any of the following situations:

* Single site installs
* Multisite installs where the plugin is activated on a site-by-site basis
* Multisite installs where the plugin is network activated
* Multi-network installs (some multi-network functionality of this plugin will only work if you have our multi-network function files installed)

= Can I have more than one Collapsible Widget Area? =

Yes you can. Simply go to Settings -> Collapsible Widget Options and specify how many Collapsible Widget Areas you want. Then, when you go back to Appearance -> Widgets, you will see that many Collapsible Widget Areas available on the right side.

= Can I insert the Collapsible Widget Area into a post or page, instead of using it in a widgetized area? =

Yes, you can use the shortcode `[collapsible-widget id=#]` (where you replace the # with the ID of the Collapsible Widget Area you want to render). The following options are available for the shortcode:

* id (int) - the ID of the Collapsible Widget Area to be rendered. Defaults to 1
* show_what (accordion|tabbed) - specifies which type of interface to use. Defaults to "tabbed"
* collapsible (bool) - if the type is set to accordion, this option specifies whether or not the entire accordion can be closed, or if one item always has to be open. Defaults to false
* closed (bool) - if the type is set to accordion, and the collapsible option is set to true, this option specifies whether or not to start with the accordion completely collapsed. Defaults to false
* cookie (bool) - if the type is set to tabbed, this option specifies whether to save which tab is active so that it will be the active tab the next time the person returns to the page. Defaults to false

= Can I use the Collapsible Widget Area in my theme, rather than using it in a widgetized area? =

Again, theoretically, yes. You should be able to use `the_widget()` to insert this directly into your theme, but that has not been tested, and it would probably take some effort to get it working correctly.

= How does the plugin create the tabs/accordions? =

This plugin uses jQueryUI (either the Tabs feature or the Accordion feature) to implement the tabs/accordions. A little extra JavaScript is thrown in just to make sure the elements are in the correct places before trying to apply the tab/accordion interface.

= How does the plugin style the tabs/accordions? =

Since this plugin uses jQueryUI, it uses the same style definitions that any other jQueryUI accordion/tab interface would use. You can choose to use any of the existing jQueryUI themes (all hosted on Google's CDN), you can leave out custom styling altogether (for instance, if you already have a jQueryUI theme pulled into your theme, or if you have specific style definitions for jQueryUI widgets baked into your theme's stylesheet), or you can apply a custom theme by hooking into the `collapsible-widget-ui-theme` filter and returning the URI of the CSS file you want to use.

= What filters does this plugin introduce? =

* `collapsible-widget-ui-theme` - the parameters sent to this filter are: 1) the URI of the CSS file being used (or a blank string if "none" was selected from the options) and 2) the theme option that was selected in the plugin's settings
* `collapsible-widget-area-args` - the arguments sent to the `register_sidebar()` function when registering the Collapsible Widget Area sidebar
* `collapsible-widget-theme-list` - an associative array of the jQueryUI themes available for use with this plugin: The keyword used in the Google CDN URI (or, the full URI to a custom theme) is used as the key, and the human-friendly name of the theme is used as the value for each array item.

== Screenshots ==

1. An accordion-style interface in the TwentyEleven WordPress theme's Main Sidebar, with the "UI Lightness" jQueryUI theme applied
2. An accordion-style interface in the TwentyEleven WordPress theme's Main Sidebar, with the "UI Darkness" jQueryUI theme applied
3. A tabbed interface in the TwentyEleven Footer Area One, with the "Base" jQueryUI theme applied
4. A tabbed interface in the TwentyEleven Footer Area One, with the "Cupertino" jQueryUI theme applied

== Changelog ==

= 0.4a =
* Implement ability to use more than one collapsible widget area
* Implement error handling for situations where a collapsible widget is dragged into a collapsible widget area
* Begin implementing shortcode

= 0.3.1a =
* Remove extraneous debug info that threw PHP error about headers already being sent

= 0.3a =
* Fix errors in CSS. With the bump to 1.9 in jQueryUI, the UI theme stylesheets that were being included also got bumped up to 1.9, while the version of jQueryUI that's included with WP 3.1-3.4.2 is 1.8, causing some issues with layout. The plugin has been updated to be more specific about which stylesheet is included, using 1.7 for versions of WP earlier than 3.1, using 1.8 for versions between 3.1 and 3.5 (not including 3.5) and using 1.9 for 3.5 and above.

= 0.2a =
* Modified JavaScript to strip HTML from widget titles before rendering collapsible interface
* Modified JavaScript to add `first-tab` and `last-tab` classes to first and last items in collapsible area

= 0.1a =
This is the first version of this plugin

== Upgrade Notice ==
= 0.4 =
Adds ability to use more than one collapsible widget area and implements shortcode

= 0.3.1a =
Remove extraneous debug info that caused PHP error

= 0.3a =
Fixes issue with CSS for accordion and tab styles