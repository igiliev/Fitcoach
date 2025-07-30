<?php

if(!defined('ABSPATH')){
    exit;
}

$spro_tour_content = array();

////////////////////////////
// Assistant Tour
////////////////////////////

$spro_tour_content['assistant']['.spro-header-menu'] = array(
    "title" => __("Assistant", "softaculous-pro"),  
    "intro" => __("Assistant adds AI to your site building process and lets you create content with just a few clicks (look for the AI option while editing your posts/pages).", "softaculous-pro")
	.'<br /><br />'.
	__("Assistant also helps you with several aspects of building and maintaning your WordPress website.", "softaculous-pro"),  
    "position" => "bottom", 
);

$spro_tour_content['assistant']['#spro-tours'] = array(
    "title" => __("Tours", "softaculous-pro"),  
    "intro" => __("Tours highlights and explains important options in WordPress that you will need while managing your site.", "softaculous-pro")
	.'<br /><br />'.
	__("You can replay the tours when needed.", "softaculous-pro"),  
    "position" => "right", 
);

$spro_tour_content['assistant']['#spro-features'] = array(
    "title" => __("Recommended Features", "softaculous-pro"),  
    "intro" => __("A recommended list of plugins to improve performace and extend functionalties for your site.", "softaculous-pro"),
    "position" => "right", 
);

$spro_tour_content['assistant']['#spro-quick-links'] = array(
    "title" => __("Quick Links", "softaculous-pro"),  
    "intro" => __("Here you will find important links compiled in a single section which can sometimes be difficult to find in WordPress.", "softaculous-pro"),  
    "position" => "left", 
);

$spro_tour_content['assistant']['#spro-settings'] = array(
    "title" => __("Settings", "softaculous-pro"),  
    "intro" => __("We have simplified complex WordPress settings that you can now manage with just a click via the Assistant.", "softaculous-pro"),  
    "position" => "left", 
);

$spro_tour_content['assistant']['#spro-ai'] = array(
    "title" => __("AI", "softaculous-pro"),  
    "intro" => __("Experience hassle-free site building with inbuilt AI in Assistant.", "softaculous-pro"),
    "position" => "left", 
);

$spro_tour_content['assistant']['#toplevel_page_assistant'] = array(
    "title" => __("Your Assistant", "softaculous-pro"),  
    "intro" => __("Assistant resides here, when you are unable to find something or having a hard time understanding a feature in WordPress we should be able to help you.", "softaculous-pro")
	.'<br /><br />'.
	__("Visit this page anytime.", "softaculous-pro"),  
    "position" => "right", 
);

///////////////////////////////////
// WordPress Sidebar
///////////////////////////////////

$spro_tour_content['sidebar']['#toplevel_page_assistant'] = array(
    "title" => __("Your Assistant", "softaculous-pro"),  
    "intro" => __("Assistant resides here, when you are unable to find something or having a hard time understanding a feature in WordPress we should be able to help you.", "softaculous-pro")
	.'<br /><br />'.
	__("Visit this page anytime.", "softaculous-pro"),  
    "position" => "right", 
);

$spro_tour_content['sidebar']['#wp-admin-bar-view-site'] = array(
    "title" => __("Preview Site", "softaculous-pro"),  
    "intro" => __("You can use this link to preview your site as a visitor.", "softaculous-pro"),  
    "position" => "right",
    "hover" => "true",
    "hover_selector" => "#wp-admin-bar-site-name",
);

$spro_tour_content['sidebar']['#menu-dashboard'] = array(
    "title" => __("Dashboard", "softaculous-pro"),  
    "intro" => __("The Dashboard is your website's command center, providing a concise summary of its activity, such as site health, recent updates, comments, and statistics.", "softaculous-pro")
	.'<br /><br />'.
	__("Several plugins also display quick summary like orders, products, etc within the dashboard.", "softaculous-pro")
	.'<br /><br />'.
	__("You can also check for Updates from the sub-menu.", "softaculous-pro"),  
    "position" => "right", 
);

$spro_tour_content['sidebar']['#menu-posts'] = array(
    "title" => __("Posts", "softaculous-pro"),  
    "intro" => __("Manage your blog posts here.", "softaculous-pro")
	.'<br /><br />'.
	__("Posts are typically utilized for blog posts, news updates, or articles, organized in a chronological order.", "softaculous-pro"),
    "position" => "right", 
); 

$spro_tour_content['sidebar']['#menu-media'] = array(
    "title" => __("Media Library", "softaculous-pro"),  
    "intro" => __("Manage media files like images, audio, videos, etc. here.", "softaculous-pro")
	.'<br /><br />'.
	__("Media uploaded from anywhere on your site can be managed here.", "softaculous-pro"),  
    "position" => "right", 
);

$spro_tour_content['sidebar']['#menu-pages'] = array(
    "title" => __("Pages", "softaculous-pro"),
    "intro" => __("Pages are static content sections such as Home, About Us, Contact, Services, etc.", "softaculous-pro")
	.'<br /><br />'.
	__("Use this menu to add, edit, delete your pages.", "softaculous-pro"),
    "position" => "right", 
); 

$spro_tour_content['sidebar']['#menu-comments'] = array(
    "title" => __("Comments", "softaculous-pro"),  
    "intro" => __("Here you can moderate comments posted by visitors on your posts.", "softaculous-pro")
	.'<br /><br />'.
	__("You can one click disable comments for your entire site from the Assistant dashboard.", "softaculous-pro"),
    "position" => "right", 
); 

$spro_tour_content['sidebar']['#menu-appearance'] = array(
    "title" => __("Appearance", "softaculous-pro"),
    "intro" => __("Personalize your site's appearance effortlessly.", "softaculous-pro")
	.'<br /><br />'.
	__("Explore themes, customize headers, background, colors, manage widgets and menus to customize your site's look and feel.", "softaculous-pro"),  
    "position" => "right", 
); 

$spro_tour_content['sidebar']['#menu-plugins'] = array(
    "title" => __("Plugins", "softaculous-pro"),
    "intro" => __("Unlock endless possibilities for your website with plugins.", "softaculous-pro")
	.'<br /><br />'.
	__("Easily search, add or delete plugins to enhance your site's functionality.", "softaculous-pro"),  
    "position" => "right", 
); 

$spro_tour_content['sidebar']['#menu-users'] = array(
    "title" => __("Users", "softaculous-pro"),
    "intro" => __("Add or manage users to ensure smooth operation and collaboration.", "softaculous-pro"),  
    "position" => "right", 
);

$spro_tour_content['sidebar']['#menu-tools'] = array(
    "title" => __("Tools", "softaculous-pro"),
    "intro" => __("Import/Export site data, check site health or edit plugin/theme files from the Tools menu.", "softaculous-pro"),  
    "position" => "right", 
);

$spro_tour_content['sidebar']['#menu-settings'] = array(
    "title" => __("Settings", "softaculous-pro"),
    "intro" => __("Advanced settings for your site can be managed here.", "softaculous-pro")
	.'<br /><br />'.
	__("These settings include site title, tagline, site url, writing, reading, discussion, media, permalinks and more.", "softaculous-pro")
	.'<br /><br />'.
	__("Some plugins also add their settings page in this menu.", "softaculous-pro"),
    "position" => "right", 
);

$spro_tour_content['sidebar']['#collapse-menu'] = array(
    "title" => __("Toggle Menu", "softaculous-pro"),
    "intro" => __("Expand or Collapse the sidebar menu using this option.", "softaculous-pro"),
    "position" => "right", 
);

$spro_tour_content['sidebar']['#wp-admin-bar-user-info'] = array(
    "title" => __("Edit Profile", "softaculous-pro"),  
    "intro" => __("Here you can edit your profile information like name, email, password, bio, profile picture and more.", "softaculous-pro"),  
    "position" => "left",
    "hover" => "true",
    "hover_selector" => "#wp-admin-bar-my-account",
);

$spro_tour_content['sidebar']['#wp-admin-bar-logout'] = array(
    "title" => __("Log Out", "softaculous-pro"),  
    "intro" => __("Use this link to securely log out from your site.", "softaculous-pro"),
    "position" => "left",
    "hover" => "true",
    "hover_selector" => "#wp-admin-bar-my-account",
);

////////////////////////////
// Plugins Page
////////////////////////////

$spro_tour_content['plugins']['#menu-plugins'] = array(
    "title" => __("Plugins", "softaculous-pro"),  
    "intro" => __("Click here to add or manage plugins on your site.", "softaculous-pro"),  
    "position" => "right", 
);

$spro_tour_content['plugins']['.page-title-action'] = array(
    "title" => __("Add New Plugin", "softaculous-pro"),  
    "intro" => __("Here you can search wordpress.org plugins library or upload a custom plugin file to install a new plugin on your site.", "softaculous-pro"),
    "position" => "bottom",
);

$spro_tour_content['plugins']['tr[data-plugin]'] = array(
    "title" => __("Installed Plugins List", "softaculous-pro"),  
    "intro" => __("All your installed plugins active as well as inactive will be listed here.", "softaculous-pro"),  
    "position" => "bottom", 
);

$spro_tour_content['plugins']['td.plugin-title'] = array(
    "title" => __("Plugin Actions", "softaculous-pro"),  
    "intro" => __("You can perform actions for your plugins like Activate, Deactivate, Update, Delete, Plugin Settings and more..", "softaculous-pro")
	.'<br /><br />'.
	__("It is recommended to delete plugins that you do not plan to use and keep all your plugins up to date.", "softaculous-pro"),  
    "position" => "bottom",
);

$spro_tour_content['plugins']['#bulk-action-selector-top'] = array(
    "title" => __("Bulk Actions", "softaculous-pro"),  
    "intro" => __("Choose bulk action for selected plugins: Activate, Deactivate, Update, Delete, toggle auto updates and more", "softaculous-pro"), 
    "position" => "bottom", 
);

$spro_tour_content['plugins']['.subsubsub'] = array(
    "title" => __("Filter Installed Plugins", "softaculous-pro"),  
    "intro" => __("Filter your installed plugins list with Active, Inactive, Auto Updates Enabled or Disabled options", "softaculous-pro"),
    "position" => "bottom",
);

$spro_tour_content['plugins']['#plugin-search-input'] = array(
    "title" => __("Search Installed Plugins", "softaculous-pro"),  
    "intro" => __("Search a plugin from the installed plugins list.", "softaculous-pro"),
    "position" => "bottom",
);

////////////////////////////
// Themes Page
//////////////////////////// 

$spro_tour_content['themes']['#menu-appearance'] = array(
    "title" => __("Themes", "softaculous-pro"),
    "intro" => __("Click here to add or manage themes on your site.", "softaculous-pro"),  
    "position" => "right", 
);

$spro_tour_content['themes']['.page-title-action'] = array(
    "title" => __("Add New Theme", "softaculous-pro"),  
    "intro" => __("Here you can search wordpress.org themes library or upload a custom theme file to install a new theme on your site.", "softaculous-pro"),
    "position" => "bottom", 
);

$spro_tour_content['themes']['.theme-browser'] = array(
    "title" => __("Installed Themes List", "softaculous-pro"),  
    "intro" => __("All your installed themes active as well as inactive will be listed here.", "softaculous-pro"),  
    "position" => "right", 
);

$spro_tour_content['themes']['.theme.active[data-slug]'] = array(
    "title" => __("Active Theme", "softaculous-pro"),  
    "intro" => __("Your active theme will be listed here.", "softaculous-pro"),  
    "position" => "right", 
);

$spro_tour_content['themes']['.theme-actions'] = array(
    "title" => __("Theme Actions", "softaculous-pro"),  
    "intro" => __("You can perform actions for your theme like Activate, Live Preview, Customize and more", "softaculous-pro")
	.'<br /><br />'.
	__("It is recommended to delete themes that you do not plan to use and keep all your themes up to date.", "softaculous-pro"),  
    "position" => "bottom",
);

$spro_tour_content['themes']['.search-box'] = array(
    "title" => __("Search Installed Themes", "softaculous-pro"),  
    "intro" => __("Search a theme from the installed themes list.", "softaculous-pro"),
    "position" => "bottom",
);

////////////////////////////
// WordPress Dashboard Page
////////////////////////////

$spro_tour_content['dashboard']['#menu-dashboard'] = array(
    "title" => __("Dashboard", "softaculous-pro"),  
    "intro" => __("The Dashboard is your website's command center, providing a concise summary of its activity, such as site health, recent updates, comments, and statistics.", "softaculous-pro")
	.'<br /><br />'.
	__("Several plugins also display quick summary like orders, products, etc within the dashboard.", "softaculous-pro"),  
    "position" => "right", 
);

$spro_tour_content['dashboard']['#dashboard_site_health'] = array(
    "title" => __("Site Health", "softaculous-pro"),  
    "intro" => __("Here you can get insights on the overall performance and security of your website.", "softaculous-pro")
	.'<br /><br />'.
	__("It offers recommendations and troubleshooting tools to help you maintain an efficient and secure site.", "softaculous-pro"),  
    "position" => "right", 
);

$spro_tour_content['dashboard']['#dashboard_right_now'] = array(
    "title" => __("Info at a Glance", "softaculous-pro"),  
    "intro" => __("Here you will get the number of posts, pages, comments, current version of WordPress and theme that you are running.", "softaculous-pro"),
    "position" => "right", 
);

$spro_tour_content['dashboard']['#dashboard_activity'] = array(
    "title" => __("Activity", "softaculous-pro"),  
    "intro" => __("Here you will get a quick summary of recent activity like published posts and comments received on your site.", "softaculous-pro"),  
    "position" => "right", 
);


$spro_tour_content['dashboard']['#dashboard_quick_press'] = array(
    "title" => __("Quick Draft", "softaculous-pro"),  
    "intro" => __("Use this section for capturing ideas as they come to you, quickly jot down the ideas for new posts.", "softaculous-pro")
	.'<br /><br />'.
	__("You can later access these drafts from the Posts section and continue editing them in the full post editor.", "softaculous-pro"),
    "position" => "left", 
);


$spro_tour_content['dashboard']['#dashboard_primary'] = array(
    "title" => __("Events and News", "softaculous-pro"),  
    "intro" => __("This widget is a valuable resource for staying informed about the latest happenings in the WordPress community and connecting with other WordPress enthusiasts.", "softaculous-pro")
	.'<br /><br />'.
	__("Here you will find updates on new releases, upcoming features, security updates, and general news about the WordPress community.", "softaculous-pro")
	.'<br /><br />'.
	__("This section also shows upcoming WordPress events such as WordCamps, local meetups, and other community gatherings.", "softaculous-pro"),
    "position" => "left", 
);


$spro_tour_content['dashboard']['#screen-options-link-wrap'] = array(
    "title" => __("Screen Options", "softaculous-pro"),  
    "intro" => __("This useful feature allows you to select the screen elements that you would like to show or hide by using the checkboxes.", "softaculous-pro")
	.'<br /><br />'.
	__("You will find this option on several pages across your site.", "softaculous-pro"),
    "position" => "bottom", 
);

////////////////////////////
// Users Page
////////////////////////////

$spro_tour_content['users']['#menu-users'] = array(
    "title" => __("Users", "softaculous-pro"),  
    "intro" => __("Click here to add or manage users on your site.", "softaculous-pro"),  
    "position" => "right", 
);

$spro_tour_content['users']['.page-title-action'] = array(
    "title" => __("Add New User", "softaculous-pro"),  
    "intro" => __("Add a new user for your site. You can enter user details, password, role, etc.", "softaculous-pro"),
    "position" => "bottom",
);

$spro_tour_content['users']['tbody > tr'] = array(
    "title" => __("Users List", "softaculous-pro"),  
    "intro" => __("All your users with admin role as well as other roles will be listed here.", "softaculous-pro"),  
    "position" => "bottom", 
);

$spro_tour_content['users']['tbody > tr > td'] = array(
	"title" => __("Edit User", "softaculous-pro"),
	"intro" => __("You can edit the user information, change password, change role and much more with the Edit link here.", "softaculous-pro"),
	"position" => "bottom",
	"hover" => "true",
	"hover_selector" => ".row-actions",
	"hover_class" => "visible",
);

$spro_tour_content['users']['#bulk-action-selector-top'] = array(
    "title" => __("Bulk Actions", "softaculous-pro"),  
    "intro" => __("Choose bulk action for selected users: Delete or send password reset link", "softaculous-pro"), 
    "position" => "bottom", 
);

$spro_tour_content['users']['#new_role'] = array(
    "title" => __("Change Role", "softaculous-pro"),  
    "intro" => __("Here you can bulk change role for the selected users", "softaculous-pro"),  
    "position" => "bottom", 
);

$spro_tour_content['users']['.subsubsub'] = array(
    "title" => __("Filter Users", "softaculous-pro"),  
    "intro" => __("Filter your users list with their roles", "softaculous-pro"),
    "position" => "bottom",
);

$spro_tour_content['users']['.search-box'] = array(
    "title" => __("Search Users", "softaculous-pro"),  
    "intro" => __("Search a user from the users list.", "softaculous-pro"),
    "position" => "bottom",
);

////////////////////////////
// Posts Page
////////////////////////////

$spro_tour_content['posts']['#menu-posts'] = array(
    "title" => __("Posts", "softaculous-pro"),  
    "intro" => __("Click here to add or manage blog posts on your site.", "softaculous-pro"),  
    "position" => "right", 
);

$spro_tour_content['posts']['.page-title-action'] = array(
    "title" => __("Add New Post", "softaculous-pro"),  
    "intro" => __("Start writing a new blog post for your site.", "softaculous-pro"),
    "position" => "bottom",
);

$spro_tour_content['posts']['tbody > tr'] = array(
    "title" => __("Posts List", "softaculous-pro"),  
    "intro" => __("All the posts owned by all the users on your site will be listed here.", "softaculous-pro"),  
    "position" => "bottom", 
);

$spro_tour_content['posts']['tbody > tr > td'] = array(
    "title" => __("Manage Post", "softaculous-pro"),  
    "intro" => __("You can view, edit or delete the posts with the links here.", "softaculous-pro"),
    "position" => "bottom",
    "hover" => "true",
    "hover_selector" => ".row-actions",
    "hover_class" => "visible"
);

$spro_tour_content['posts']['#bulk-action-selector-top'] = array(
    "title" => __("Bulk Actions", "softaculous-pro"),  
    "intro" => __("Choose bulk action for selected posts: Quick Edit or Move to Trash.", "softaculous-pro"), 
    "position" => "bottom", 
);

$spro_tour_content['posts']['#filter-by-date'] = array(
    "title" => __("Filter Posts by Date", "softaculous-pro"),  
    "intro" => __("Here you can filter the posts by date.", "softaculous-pro"),  
    "position" => "bottom", 
);

$spro_tour_content['posts']['#cat'] = array(
    "title" => __("Filter Posts by Category", "softaculous-pro"),  
    "intro" => __("Here you can filter the posts by category.", "softaculous-pro"),  
    "position" => "bottom", 
);

$spro_tour_content['posts']['.subsubsub'] = array(
    "title" => __("Filter Posts", "softaculous-pro"),  
    "intro" => __("Filter your posts list by their status like published, drafts, trash, etc.", "softaculous-pro"),
    "position" => "bottom",
);

$spro_tour_content['posts']['.search-box'] = array(
    "title" => __("Search Posts", "softaculous-pro"),  
    "intro" => __("Search a post from the posts list.", "softaculous-pro"),
    "position" => "bottom",
);

////////////////////////////
// Pages Page
////////////////////////////

$spro_tour_content['pages']['#menu-pages'] = array(
    "title" => __("Pages", "softaculous-pro"),  
    "intro" => __("Click here to add or manage pages on your site.", "softaculous-pro"),  
    "position" => "right", 
);

$spro_tour_content['pages']['.page-title-action'] = array(
    "title" => __("Add New Page", "softaculous-pro"),  
    "intro" => __("Start creating a new page for your site.", "softaculous-pro"),
    "position" => "bottom",
);

$spro_tour_content['pages']['tbody > tr'] = array(
    "title" => __("Pages List", "softaculous-pro"),  
    "intro" => __("All the pages on your site will be listed here.", "softaculous-pro"),
    "position" => "bottom", 
);

$spro_tour_content['pages']['tbody > tr > td'] = array(
    "title" => __("Manage Page", "softaculous-pro"),  
    "intro" => __("You can view, edit or delete the pages with the links here.", "softaculous-pro"),
    "position" => "bottom",
    "hover" => "true",
    "hover_selector" => ".row-actions",
    "hover_class" => "visible"
);

$spro_tour_content['pages']['#bulk-action-selector-top'] = array(
    "title" => __("Bulk Actions", "softaculous-pro"),  
    "intro" => __("Choose bulk action for selected pages: Quick Edit or Move to Trash.", "softaculous-pro"), 
    "position" => "bottom", 
);

$spro_tour_content['pages']['#filter-by-date'] = array(
    "title" => __("Filter Pages by Date", "softaculous-pro"),  
    "intro" => __("Here you can filter the pages by date.", "softaculous-pro"),  
    "position" => "bottom", 
);

$spro_tour_content['pages']['.subsubsub'] = array(
    "title" => __("Filter Pages", "softaculous-pro"),  
    "intro" => __("Filter your pages list by their status like published, drafts, trash, etc.", "softaculous-pro"),
    "position" => "bottom",
);

$spro_tour_content['pages']['.search-box'] = array(
    "title" => __("Search Pages", "softaculous-pro"),  
    "intro" => __("Search a page from the pages list.", "softaculous-pro"),
    "position" => "bottom",
);

