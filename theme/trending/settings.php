<?php
defined('MOODLE_INTERNAL') || die;
$ADMIN->add('themes', new admin_category('theme_trending', 'trending'));
$settings = new theme_boost_admin_settingspage_tabs('themesettingtrending', get_string('configtitle', 'theme_trending'));
$page = new admin_settingpage('theme_trending_general', get_string('generalsettings', 'theme_trending'));
// favicon.
$name = 'theme_trending/favicon';
$title = get_string('favicon', 'theme_trending');
$description = get_string('favicondesc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'favicon');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// Logo file setting.
$name = 'theme_trending/logo';
$title = get_string('logo','theme_trending');
$description = get_string('logo_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

//  internalbannerimage setting.
$name = 'theme_trending/internalbannerimage';
$title = get_string('internalbannerimage','theme_trending');
$description = get_string('internalbannerimage_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'internalbannerimage');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// internalbannertagline .
$name = 'theme_trending/internalbannertagline';
$title = get_string('internalbannertagline', 'theme_trending');
$description = get_string('internalbannertagline_desc', 'theme_trending');
$default = 'Build your academic knowledge';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_trending/sitebluecolor';
$title = get_string('sitebluecolor', 'theme_trending');
$description = get_string('sitebluecolor_desc', 'theme_trending');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '#0482D2');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// siteyellowcolor.
$name = 'theme_trending/siteyellowcolor';
$title = get_string('siteyellowcolor', 'theme_trending');
$description = get_string('siteyellowcolor_desc', 'theme_trending');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '#ffaa30');
$page->add($setting);
// sitevioletcolor.
$name = 'theme_trending/sitevioletcolor';
$title = get_string('sitevioletcolor', 'theme_trending');
$description = get_string('sitevioletcolor_desc', 'theme_trending');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '#3c4e8e');
$page->add($setting);
//  loginbackground setting.
$name = 'theme_trending/loginbackground';
$title = get_string('loginbackground','theme_trending');
$description = get_string('loginbackground_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'loginbackground');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Must add the page after definiting all the settings!
$settings->add($page);
$page = new admin_settingpage('theme_trending_slider', get_string('slidersettings', 'theme_trending'));
// displayslidersection setting.
$name = 'theme_trending/displayslidersection';
$title = get_string('displayslidersection','theme_trending');
$description = get_string('displayslidersection_desc', 'theme_trending');
$default = 1;
$choices = array(0=>'No', 1=>'Yes');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);
// slider1imageurl.
$name = 'theme_trending/slider1imageurl';
$title = get_string('slider1imageurl', 'theme_trending');
$description = get_string('slider1imageurl_desc', 'theme_trending');
$default =$CFG->wwwroot.'/'.'theme'.'/'.'trending'.'/'.'pix'.'/'.'slides'.'/'.'slider1.jpg';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// slider1caption.
$name = 'theme_trending/slider1caption';
$title = get_string('slider1caption', 'theme_trending');
$description = get_string('slider1caption_desc', 'theme_trending');
$default = 'Start Your Classes.';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// joinnowbuttontext.
$name = 'theme_trending/joinnowbuttontext';
$title = get_string('joinnowbuttontext', 'theme_trending');
$description = get_string('joinnowbuttontext_desc', 'theme_trending');
$default = 'Join Now';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// joinnowbuttonurl.
$name = 'theme_trending/joinnowbuttonurl';
$title = get_string('joinnowbuttonurl', 'theme_trending');
$description = get_string('joinnowbuttonurl_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// readmorebuttontext.
$name = 'theme_trending/readmorebuttontext';
$title = get_string('readmorebuttontext', 'theme_trending');
$description = get_string('readmorebuttontext_desc', 'theme_trending');
$default = 'Read More';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// readmorebuttonurl.
$name = 'theme_trending/readmorebuttonurl';
$title = get_string('readmorebuttonurl', 'theme_trending');
$description = get_string('readmorebuttonurl_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// slider2imageurl.
$name = 'theme_trending/slider2imageurl';
$title = get_string('slider2imageurl', 'theme_trending');
$description = get_string('slider2imageurl_desc', 'theme_trending');
$default = $CFG->wwwroot.'/'.'theme'.'/'.'trending'.'/'.'pix'.'/'.'slides'.'/'.'slider2.jpg';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// slider2caption.
$name = 'theme_trending/slider2caption';
$title = get_string('slider2caption', 'theme_trending');
$description = get_string('slider2caption_desc', 'theme_trending');
$default = 'Bring Tutoring Right to Your Home.';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// startcoursebuttontext.
$name = 'theme_trending/startcoursebuttontext';
$title = get_string('startcoursebuttontext', 'theme_trending');
$description = get_string('startcoursebuttontext_desc', 'theme_trending');
$default = 'Start A Course';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// startcoursebuttonurl.
$name = 'theme_trending/startcoursebuttonurl';
$title = get_string('startcoursebuttonurl', 'theme_trending');
$description = get_string('startcoursebuttonurl_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// taketourbuttontext.
$name = 'theme_trending/taketourbuttontext';
$title = get_string('taketourbuttontext', 'theme_trending');
$description = get_string('taketourbuttontext_desc', 'theme_trending');
$default = 'Take A Tour';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// taketourbuttonurl.
$name = 'theme_trending/taketourbuttonurl';
$title = get_string('taketourbuttonurl', 'theme_trending');
$description = get_string('taketourbuttonurl_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// slider3imageurl.
$name = 'theme_trending/slider3imageurl';
$title = get_string('slider3imageurl', 'theme_trending');
$description = get_string('slider3imageurl_desc', 'theme_trending');
$default = $CFG->wwwroot.'/'.'theme'.'/'.'trending'.'/'.'pix'.'/'.'slides'.'/'.'slider3.jpg';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// slider3caption.
$name = 'theme_trending/slider3caption';
$title = get_string('slider3caption', 'theme_trending');
$description = get_string('slider3caption_desc', 'theme_trending');
$default = 'Take the world best courses, online.';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// slider4imageurl.
$name = 'theme_trending/slider4imageurl';
$title = get_string('slider4imageurl', 'theme_trending');
$description = get_string('slider4imageurl_desc', 'theme_trending');
$default = $CFG->wwwroot.'/'.'theme'.'/'.'trending'.'/'.'pix'.'/'.'slides'.'/'.'slider4.jpg';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// slider4caption.
$name = 'theme_trending/slider4caption';
$title = get_string('slider4caption', 'theme_trending');
$description = get_string('slider4caption_desc', 'theme_trending');
$default = 'Learning & Fun For Everyone.';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// slider4tagline.
$name = 'theme_trending/slider4tagline';
$title = get_string('slider4tagline', 'theme_trending');
$description = get_string('slider4tagline_desc', 'theme_trending');
$default = 'Lorem ipsum gravida nibh vel velit auctor aliquetnean sollicitudin, lorem quis bibendum.';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// slider5imageurl.
$name = 'theme_trending/slider5imageurl';
$title = get_string('slider5imageurl', 'theme_trending');
$description = get_string('slider5imageurl_desc', 'theme_trending');
$default = $CFG->wwwroot.'/'.'theme'.'/'.'trending'.'/'.'pix'.'/'.'slides'.'/'.'slider5.jpg';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// slider5caption.
$name = 'theme_trending/slider5caption';
$title = get_string('slider5caption', 'theme_trending');
$description = get_string('slider5caption_desc', 'theme_trending');
$default = 'Accelerate Your Career.';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// slider6imageurl.
$name = 'theme_trending/slider6imageurl';
$title = get_string('slider6imageurl', 'theme_trending');
$description = get_string('slider6imageurl_desc', 'theme_trending');
$default = $CFG->wwwroot.'/'.'theme'.'/'.'trending'.'/'.'pix'.'/'.'slides'.'/'.'slider6.jpg';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// slider6caption.
$name = 'theme_trending/slider6caption';
$title = get_string('slider6caption', 'theme_trending');
$description = get_string('slider6caption_desc', 'theme_trending');
$default = 'Contemporary Ideas.';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// slider6tagline.
$name = 'theme_trending/slider6tagline';
$title = get_string('slider6tagline', 'theme_trending');
$description = get_string('slider6tagline_desc', 'theme_trending');
$default = 'Contrary to popular belief, Lorem Ipsum is not simply random text.';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// slider6buttontext.
$name = 'theme_trending/slider6buttontext';
$title = get_string('slider6buttontext', 'theme_trending');
$description = get_string('slider6buttontext_desc', 'theme_trending');
$default = 'Take A Tour';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// slider6buttonurl.
$name = 'theme_trending/slider6buttonurl';
$title = get_string('slider6buttonurl', 'theme_trending');
$description = get_string('slider6buttonurl_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
$settings->add($page);
// Frontpage setting.
$page = new admin_settingpage('theme_trending_frontpage',  get_string('frontpagesettings', 'theme_trending'));

// allcoursestagline .
$name = 'theme_trending/allcoursestagline';
$title = get_string('allcoursestagline', 'theme_trending');
$description = get_string('allcoursestagline_desc', 'theme_trending');
$default = 'All Our Available Courses Listed Below.';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// enrolledcoursestagline .
$name = 'theme_trending/enrolledcoursestagline';
$title = get_string('enrolledcoursestagline', 'theme_trending');
$description = get_string('enrolledcoursestagline_desc', 'theme_trending');
$default = 'You Can Enroll Wide Range Of Courses In This Canvas To Full Fill Your Dreams.';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// sitenewstagline .
$name = 'theme_trending/sitenewstagline';
$title = get_string('sitenewstagline', 'theme_trending');
$description = get_string('sitenewstagline_desc', 'theme_trending');
$default = 'See All Site News Here.';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// blockcustombutton.
$name = 'theme_trending/blockcustombutton';
$title = get_string('blockcustombutton', 'theme_trending');
$description = get_string('blockcustombutton_desc', 'theme_trending');
$default = 'Buy Now';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// blockcustombuttonurl.
$name = 'theme_trending/blockcustombuttonurl';
$title = get_string('blockcustombuttonurl', 'theme_trending');
$description = get_string('blockcustombuttonurl_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// blockheading.
$name = 'theme_trending/blockheading';
$title = get_string('blockheading', 'theme_trending');
$description = get_string('blockheading_desc', 'theme_trending');
$default = 'Our Blocks';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// blocktagline.
$name = 'theme_trending/blocktagline';
$title = get_string('blocktagline', 'theme_trending');
$description = get_string('blocktagline_desc', 'theme_trending');
$default = 'You can see list of blocks here.';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// callus .
$name = 'theme_trending/callus';
$title = get_string('callus', 'theme_trending');
$description = get_string('callus_desc', 'theme_trending');
$default = 'Call Us';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// callusurl .
$name = 'theme_trending/callusurl';
$title = get_string('callusurl', 'theme_trending');
$description = get_string('callusurl_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// emailus .
$name = 'theme_trending/emailus';
$title = get_string('emailus', 'theme_trending');
$description = get_string('emailus_desc', 'theme_trending');
$default = 'Email Us';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// emailusurl .
$name = 'theme_trending/emailusurl';
$title = get_string('emailusurl', 'theme_trending');
$description = get_string('emailusurl_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// livechat .
$name = 'theme_trending/livechat';
$title = get_string('livechat', 'theme_trending');
$description = get_string('livechat_desc', 'theme_trending');
$default = 'Live Chat';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// livechaturl .
$name = 'theme_trending/livechaturl';
$title = get_string('livechaturl', 'theme_trending');
$description = get_string('livechaturl_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
//  allcoursesbg setting.
$name = 'theme_trending/allcoursesbg';
$title = get_string('allcoursesbg','theme_trending');
$description = get_string('allcoursesbg_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'allcoursesbg');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// displayaboutussection setting.
$name = 'theme_trending/displayaboutussection';
$title = get_string('displayaboutussection','theme_trending');
$description = get_string('displayaboutussection_desc', 'theme_trending');
$default = 1;
$choices = array(0=>'No', 1=>'Yes');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);

// aboutusheading .
$name = 'theme_trending/aboutusheading';
$title = get_string('aboutusheading', 'theme_trending');
$description = get_string('aboutusheading_desc', 'theme_trending');
$default = 'About Us';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// aboutustagline .
$name = 'theme_trending/aboutustagline';
$title = get_string('aboutustagline', 'theme_trending');
$description = get_string('aboutustagline_desc', 'theme_trending');
$default = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// aboutusimage setting.
$name = 'theme_trending/aboutusimage';
$title = get_string('aboutusimage','theme_trending');
$description = get_string('aboutusimage_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'aboutusimage');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// aboutusdescription .
$name = 'theme_trending/aboutusdescription';
$title = get_string('aboutusdescription', 'theme_trending');
$description = get_string('aboutusdescription_desc', 'theme_trending');
$default = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Magni autem minus sint, commodi.';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// accordionbox1heading .
$name = 'theme_trending/accordionbox1heading';
$title = get_string('accordionbox1heading', 'theme_trending');
$description = get_string('accordionbox1heading_desc', 'theme_trending');
$default = 'Web Design';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// accordionbox1description .
$name = 'theme_trending/accordionbox1description';
$title = get_string('accordionbox1description', 'theme_trending');
$description = get_string('accordionbox1description_desc', 'theme_trending');
$default = 'Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor.';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// accordionbox2heading .
$name = 'theme_trending/accordionbox2heading';
$title = get_string('accordionbox2heading', 'theme_trending');
$description = get_string('accordionbox2heading_desc', 'theme_trending');
$default = 'Lorem ipsum dolor sit amet';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// accordionbox2description .
$name = 'theme_trending/accordionbox2description';
$title = get_string('accordionbox2description', 'theme_trending');
$description = get_string('accordionbox2description_desc', 'theme_trending');
$default = 'Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor.';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// accordionbox3heading .
$name = 'theme_trending/accordionbox3heading';
$title = get_string('accordionbox3heading', 'theme_trending');
$description = get_string('accordionbox3heading_desc', 'theme_trending');
$default = 'Lorem ipsum dolor sit amet';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// accordionbox3description .
$name = 'theme_trending/accordionbox3description';
$title = get_string('accordionbox3description', 'theme_trending');
$description = get_string('accordionbox3description_desc', 'theme_trending');
$default = 'Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor.';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// accordionbox4heading .
$name = 'theme_trending/accordionbox4heading';
$title = get_string('accordionbox4heading', 'theme_trending');
$description = get_string('accordionbox4heading_desc', 'theme_trending');
$default = 'Lorem ipsum dolor sit amet';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// accordionbox4description .
$name = 'theme_trending/accordionbox4description';
$title = get_string('accordionbox4description', 'theme_trending');
$description = get_string('accordionbox4description_desc', 'theme_trending');
$default = 'Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor.';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

//  enrolledcoursesbg setting.
$name = 'theme_trending/enrolledcoursesbg';
$title = get_string('enrolledcoursesbg','theme_trending');
$description = get_string('enrolledcoursesbg_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'enrolledcoursesbg');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// displaymarketingsection setting.
$name = 'theme_trending/displaymarketingsection';
$title = get_string('displaymarketingsection','theme_trending');
$description = get_string('displaymarketingsection_desc', 'theme_trending');
$default = 1;
$choices = array(0=>'No', 1=>'Yes');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);
// marketingheading.
$name = 'theme_trending/marketingheading';
$title = get_string('marketingheading', 'theme_trending');
$description = get_string('marketingheading_desc', 'theme_trending');
$default = 'GET AN AWESOME START!';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// marketingbuttontext.
$name = 'theme_trending/marketingbuttontext';
$title = get_string('marketingbuttontext', 'theme_trending');
$description = get_string('marketingbuttontext_desc', 'theme_trending');
$default = 'BUY NOW';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// marketingbuttonurl.
$name = 'theme_trending/marketingbuttonurl';
$title = get_string('marketingbuttonurl', 'theme_trending');
$description = get_string('marketingbuttonurl_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// displaytutorsection setting.
$name = 'theme_trending/displaytutorsection';
$title = get_string('displaytutorsection','theme_trending');
$description = get_string('displaytutorsection_desc', 'theme_trending');
$default = 1;
$choices = array(0=>'No', 1=>'Yes');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);
//  tutorsbg setting.
$name = 'theme_trending/tutorsbg';
$title = get_string('tutorsbg','theme_trending');
$description = get_string('tutorsbg_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'tutorsbg');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutorssectionheading .
$name = 'theme_trending/tutorssectionheading';
$title = get_string('tutorssectionheading', 'theme_trending');
$description = get_string('tutorssectionheading_desc', 'theme_trending');
$default = 'Top Tutors in Every Subject.';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutorssectiontagline  .
$name = 'theme_trending/tutorssectiontagline';
$title = get_string('tutorssectiontagline', 'theme_trending');
$description = get_string('tutorssectiontagline_desc', 'theme_trending');
$default = 'Lorem ipsum gravida nibh vel velit auctor aliquetnean sollicitudin,lorem quis bibendum.';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// displaytutor1 setting.
$name = 'theme_trending/displaytutor1';
$title = get_string('displaytutor1','theme_trending');
$description = get_string('displaytutor1_desc', 'theme_trending');
$default = 1;
$choices = array(0=>'No', 1=>'Yes');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);
// tutor1image.
$name = 'theme_trending/tutor1image';
$title = get_string('tutor1image', 'theme_trending');
$description = get_string('tutor1image_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'tutor1image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor1name.
$name = 'theme_trending/tutor1name';
$title = get_string('tutor1name', 'theme_trending');
$description = get_string('tutor1name_desc', 'theme_trending');
$default = 'Doris Wilson';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor1url.
$name = 'theme_trending/tutor1url';
$title = get_string('tutor1url', 'theme_trending');
$description = get_string('tutor1url_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor1designation.
$name = 'theme_trending/tutor1designation';
$title = get_string('tutor1designation', 'theme_trending');
$description = get_string('tutor1designation_desc', 'theme_trending');
$default = 'Phd, Master';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// displaytutor2 setting.
$name = 'theme_trending/displaytutor2';
$title = get_string('displaytutor2','theme_trending');
$description = get_string('displaytutor2_desc', 'theme_trending');
$default = 1;
$choices = array(0=>'No', 1=>'Yes');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);
// tutor2image.
$name = 'theme_trending/tutor2image';
$title = get_string('tutor2image', 'theme_trending');
$description = get_string('tutor2image_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'tutor2image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor2name.
$name = 'theme_trending/tutor2name';
$title = get_string('tutor2name', 'theme_trending');
$description = get_string('tutor2name_desc', 'theme_trending');
$default = 'A. T. Whitecotton';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor2url.
$name = 'theme_trending/tutor2url';
$title = get_string('tutor2url', 'theme_trending');
$description = get_string('tutor2url_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor2designation.
$name = 'theme_trending/tutor2designation';
$title = get_string('tutor2designation', 'theme_trending');
$description = get_string('tutor2designation_desc', 'theme_trending');
$default = 'Phd, History';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// displaytutor3 setting.
$name = 'theme_trending/displaytutor3';
$title = get_string('displaytutor3','theme_trending');
$description = get_string('displaytutor3_desc', 'theme_trending');
$default = 1;
$choices = array(0=>'No', 1=>'Yes');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);
// tutor3image.
$name = 'theme_trending/tutor3image';
$title = get_string('tutor3image', 'theme_trending');
$description = get_string('tutor3image_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'tutor3image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor3name.
$name = 'theme_trending/tutor3name';
$title = get_string('tutor3name', 'theme_trending');
$description = get_string('tutor3name_desc', 'theme_trending');
$default = 'Sarah Norris';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor3url.
$name = 'theme_trending/tutor3url';
$title = get_string('tutor3url', 'theme_trending');
$description = get_string('tutor3url_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor3designation.
$name = 'theme_trending/tutor3designation';
$title = get_string('tutor3designation', 'theme_trending');
$description = get_string('tutor3designation_desc', 'theme_trending');
$default = 'Phd, Science';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// displaytutor4 setting.
$name = 'theme_trending/displaytutor4';
$title = get_string('displaytutor4','theme_trending');
$description = get_string('displaytutor4_desc', 'theme_trending');
$default = 1;
$choices = array(0=>'No', 1=>'Yes');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);
// tutor4image.
$name = 'theme_trending/tutor4image';
$title = get_string('tutor4image', 'theme_trending');
$description = get_string('tutor4image_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'tutor4image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor4name.
$name = 'theme_trending/tutor4name';
$title = get_string('tutor4name', 'theme_trending');
$description = get_string('tutor4name_desc', 'theme_trending');
$default = 'Mary Belle Greenwell';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor4url.
$name = 'theme_trending/tutor4url';
$title = get_string('tutor4url', 'theme_trending');
$description = get_string('tutor4url_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor4designation.
$name = 'theme_trending/tutor4designation';
$title = get_string('tutor4designation', 'theme_trending');
$description = get_string('tutor4designation_desc', 'theme_trending');
$default = 'Biology Instructor';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// displaytutor5 setting.
$name = 'theme_trending/displaytutor5';
$title = get_string('displaytutor5','theme_trending');
$description = get_string('displaytutor5_desc', 'theme_trending');
$default = 1;
$choices = array(0=>'No', 1=>'Yes');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);
// tutor5image.
$name = 'theme_trending/tutor5image';
$title = get_string('tutor5image', 'theme_trending');
$description = get_string('tutor5image_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'tutor5image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor5name.
$name = 'theme_trending/tutor5name';
$title = get_string('tutor5name', 'theme_trending');
$description = get_string('tutor5name_desc', 'theme_trending');
$default = 'Elizabeth Vaughn';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor5url.
$name = 'theme_trending/tutor5url';
$title = get_string('tutor5url', 'theme_trending');
$description = get_string('tutor5url_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor5designation.
$name = 'theme_trending/tutor5designation';
$title = get_string('tutor5designation', 'theme_trending');
$description = get_string('tutor5designation_desc', 'theme_trending');
$default = 'Phd, Master';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// displaytutor6 setting.
$name = 'theme_trending/displaytutor6';
$title = get_string('displaytutor6','theme_trending');
$description = get_string('displaytutor6_desc', 'theme_trending');
$default = 1;
$choices = array(0=>'No', 1=>'Yes');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);
// tutor6image.
$name = 'theme_trending/tutor6image';
$title = get_string('tutor6image', 'theme_trending');
$description = get_string('tutor6image_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'tutor6image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor6name.
$name = 'theme_trending/tutor6name';
$title = get_string('tutor6name', 'theme_trending');
$description = get_string('tutor6name_desc', 'theme_trending');
$default = 'Helen Levings';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor6url.
$name = 'theme_trending/tutor6url';
$title = get_string('tutor6url', 'theme_trending');
$description = get_string('tutor6url_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor6designation.
$name = 'theme_trending/tutor6designation';
$title = get_string('tutor6designation', 'theme_trending');
$description = get_string('tutor6designation_desc', 'theme_trending');
$default = 'Phd, English';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// displaytutor7 setting.
$name = 'theme_trending/displaytutor7';
$title = get_string('displaytutor7','theme_trending');
$description = get_string('displaytutor7_desc', 'theme_trending');
$default = 1;
$choices = array(0=>'No', 1=>'Yes');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);
// tutor7image.
$name = 'theme_trending/tutor7image';
$title = get_string('tutor7image', 'theme_trending');
$description = get_string('tutor7image_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'tutor7image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor7name.
$name = 'theme_trending/tutor7name';
$title = get_string('tutor7name', 'theme_trending');
$description = get_string('tutor7name_desc', 'theme_trending');
$default = 'Martha Flowers';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor7url.
$name = 'theme_trending/tutor7url';
$title = get_string('tutor7url', 'theme_trending');
$description = get_string('tutor7url_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor7designation.
$name = 'theme_trending/tutor7designation';
$title = get_string('tutor7designation', 'theme_trending');
$description = get_string('tutor7designation_desc', 'theme_trending');
$default = 'Phd, Master';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// displaytutor8 setting.
$name = 'theme_trending/displaytutor8';
$title = get_string('displaytutor8','theme_trending');
$description = get_string('displaytutor8_desc', 'theme_trending');
$default = 1;
$choices = array(0=>'No', 1=>'Yes');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);
// tutor8image.
$name = 'theme_trending/tutor8image';
$title = get_string('tutor8image', 'theme_trending');
$description = get_string('tutor8image_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'tutor8image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor8name.
$name = 'theme_trending/tutor8name';
$title = get_string('tutor8name', 'theme_trending');
$description = get_string('tutor8name_desc', 'theme_trending');
$default = 'Ruth Louise Williams';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor8url.
$name = 'theme_trending/tutor8url';
$title = get_string('tutor8url', 'theme_trending');
$description = get_string('tutor8url_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor8designation.
$name = 'theme_trending/tutor8designation';
$title = get_string('tutor8designation', 'theme_trending');
$description = get_string('tutor8designation_desc', 'theme_trending');
$default = 'Biology Instructor';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// displaytutor9 setting.
$name = 'theme_trending/displaytutor9';
$title = get_string('displaytutor9','theme_trending');
$description = get_string('displaytutor9_desc', 'theme_trending');
$default = 1;
$choices = array(0=>'No', 1=>'Yes');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);
// tutor9image.
$name = 'theme_trending/tutor9image';
$title = get_string('tutor9image', 'theme_trending');
$description = get_string('tutor9image_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'tutor9image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor9name.
$name = 'theme_trending/tutor9name';
$title = get_string('tutor9name', 'theme_trending');
$description = get_string('tutor9name_desc', 'theme_trending');
$default = 'Lucy Harshbarger';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor9url.
$name = 'theme_trending/tutor9url';
$title = get_string('tutor9url', 'theme_trending');
$description = get_string('tutor9url_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// tutor9designation.
$name = 'theme_trending/tutor9designation';
$title = get_string('tutor9designation', 'theme_trending');
$description = get_string('tutor9designation_desc', 'theme_trending');
$default = 'Phd, English';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// displaystudentsection setting.
$name = 'theme_trending/displaystudentsection';
$title = get_string('displaystudentsection','theme_trending');
$description = get_string('displaystudentsection_desc', 'theme_trending');
$default = 1;
$choices = array(0=>'No', 1=>'Yes');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);
// studentsectionheading.
$name = 'theme_trending/studentsectionheading';
$title = get_string('studentsectionheading', 'theme_trending');
$description = get_string('studentsectionheading_desc', 'theme_trending');
$default = 'Our Students Views';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// studentsectiontagline.
$name = 'theme_trending/studentsectiontagline';
$title = get_string('studentsectiontagline', 'theme_trending');
$description = get_string('studentsectiontagline_desc', 'theme_trending');
$default = 'Contrary to popular belief, Lorem Ipsum is not simply random text.';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student1backgroundurl.
$name = 'theme_trending/student1backgroundurl';
$title = get_string('student1backgroundurl', 'theme_trending');
$description = get_string('student1backgroundurl_desc', 'theme_trending');
$default = $CFG->wwwroot.'/'.'theme'.'/'.'trending'.'/'.'pix'.'/'.'student-bg-01.jpg';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student1image.
$name = 'theme_trending/student1image';
$title = get_string('student1image', 'theme_trending');
$description = get_string('student1image_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'student1image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student1name.
$name = 'theme_trending/student1name';
$title = get_string('student1name', 'theme_trending');
$description = get_string('student1name_desc', 'theme_trending');
$default = 'Ricky Martin - M.Tech';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student1description .
$name = 'theme_trending/student1description';
$title = get_string('student1description', 'theme_trending');
$description = get_string('student1description_desc', 'theme_trending');
$default = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student2backgroundurl.
$name = 'theme_trending/student2backgroundurl';
$title = get_string('student2backgroundurl', 'theme_trending');
$description = get_string('student2backgroundurl_desc', 'theme_trending');
$default = $CFG->wwwroot.'/'.'theme'.'/'.'trending'.'/'.'pix'.'/'.'student-bg-02.jpg';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student2image.
$name = 'theme_trending/student2image';
$title = get_string('student2image', 'theme_trending');
$description = get_string('student2image_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'student2image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student2name.
$name = 'theme_trending/student2name';
$title = get_string('student2name', 'theme_trending');
$description = get_string('student2name_desc', 'theme_trending');
$default = 'Noah Joel-  Ph.D';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student2description .
$name = 'theme_trending/student2description';
$title = get_string('student2description', 'theme_trending');
$description = get_string('student2description_desc', 'theme_trending');
$default = 'Phasellus porttitor a ipsum sit amet posuere.';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student3backgroundurl.
$name = 'theme_trending/student3backgroundurl';
$title = get_string('student3backgroundurl', 'theme_trending');
$description = get_string('student3backgroundurl_desc', 'theme_trending');
$default = $CFG->wwwroot.'/'.'theme'.'/'.'trending'.'/'.'pix'.'/'.'student-bg-03.jpg';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student3image.
$name = 'theme_trending/student3image';
$title = get_string('student3image', 'theme_trending');
$description = get_string('student3image_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'student3image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student3name.
$name = 'theme_trending/student3name';
$title = get_string('student3name', 'theme_trending');
$description = get_string('student3name_desc', 'theme_trending');
$default = 'Michael Frank- M.Tech';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student3description .
$name = 'theme_trending/student3description';
$title = get_string('student3description', 'theme_trending');
$description = get_string('student3description_desc', 'theme_trending');
$default = 'Aliquam sodales viverra interdum. In sapien dolor, convallis vel neque in, egestas scelerisque nibh.';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student4backgroundurl.
$name = 'theme_trending/student4backgroundurl';
$title = get_string('student4backgroundurl', 'theme_trending');
$description = get_string('student4backgroundurl_desc', 'theme_trending');
$default = $CFG->wwwroot.'/'.'theme'.'/'.'trending'.'/'.'pix'.'/'.'student-bg-04.jpg';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student4image.
$name = 'theme_trending/student4image';
$title = get_string('student4image', 'theme_trending');
$description = get_string('student4image_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'student4image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student4name.
$name = 'theme_trending/student4name';
$title = get_string('student4name', 'theme_trending');
$description = get_string('student4name_desc', 'theme_trending');
$default = 'Ethan Libus - Business';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student4description .
$name = 'theme_trending/student4description';
$title = get_string('student4description', 'theme_trending');
$description = get_string('student4description_desc', 'theme_trending');
$default = 'Nulla condimentum eros nec erat faucibus, et ultricies ipsum porttitor. Mauris vel cursus metus, eget imperdiet magna.';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student5backgroundurl.
$name = 'theme_trending/student5backgroundurl';
$title = get_string('student5backgroundurl', 'theme_trending');
$description = get_string('student5backgroundurl_desc', 'theme_trending');
$default = $CFG->wwwroot.'/'.'theme'.'/'.'trending'.'/'.'pix'.'/'.'student-bg-05.jpg';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student5image.
$name = 'theme_trending/student5image';
$title = get_string('student5image', 'theme_trending');
$description = get_string('student5image_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'student5image');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student5name.
$name = 'theme_trending/student5name';
$title = get_string('student5name', 'theme_trending');
$description = get_string('student5name_desc', 'theme_trending');
$default = 'Sini Mahiwal - Content Writer';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// student5description .
$name = 'theme_trending/student5description';
$title = get_string('student5description', 'theme_trending');
$description = get_string('student5description_desc', 'theme_trending');
$default = 'Nullam ac lectus gravida erat placerat semper. Cum sociis natoque penatibus.';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// displaynewslettersection setting.
$name = 'theme_trending/displaynewslettersection';
$title = get_string('displaynewslettersection','theme_trending');
$description = get_string('displaynewslettersection_desc', 'theme_trending');
$default = 1;
$choices = array(0=>'No', 1=>'Yes');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);
// newsletterheading .
$name = 'theme_trending/newsletterheading';
$title = get_string('newsletterheading', 'theme_trending');
$description = get_string('newsletterheading_desc', 'theme_trending');
$default = 'NEWSLETTER';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// newslettertagline .
$name = 'theme_trending/newslettertagline';
$title = get_string('newslettertagline', 'theme_trending');
$description = get_string('newslettertagline_desc', 'theme_trending');
$default = 'Subscribe Trending to receive useful information';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
$settings->add($page);
// Category setting page.
$page = new admin_settingpage('theme_trending_category', get_string('categorysettings', 'theme_trending'));
// categorytagline .
$name = 'theme_trending/categorytagline';
$title = get_string('categorytagline', 'theme_trending');
$description = get_string('categorytagline_desc', 'theme_trending');
$default = 'All Our Available Categories Listed Below.';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// Category Image.
$name = 'theme_trending/enablecategoryimage';
$title = get_string('enablecategoryimage', 'theme_trending');
$description = get_string('enablecategoryimage_desc', 'theme_trending');
$default = true;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// We only want to output category Image options if the parent setting is enabled.
if (get_config('theme_trending', 'enablecategoryimage')) {
// Default image Selector.
$name = 'theme_trending/defaultcategoryimage';
$title = get_string('defaultcategoryimage', 'theme_trending');
$description = get_string('defaultcategoryimage_desc', 'theme_trending');
//$default = '';
$setting = new admin_setting_configstoredfile($name, $title, $description, 'defaultcategoryimage');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// enablecustomcategoryimage.
$name = 'theme_trending/enablecustomcategoryimage';
$title = get_string('enablecustomcategoryimage', 'theme_trending');
$description = get_string('enablecustomcategoryimage_desc', 'theme_trending');
$setting = new admin_setting_configcheckbox($name, $title, $description, 0);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
if (get_config('theme_trending', 'enablecustomcategoryimage')) { 
// This is the descriptor for Custom Category image.
$name = 'theme_trending/categoryimageinfo';
$heading = get_string('categoryimageinfo', 'theme_trending');
$information = get_string('categoryimageinfo_desc', 'theme_trending');
$setting = new admin_setting_heading($name, $heading, $information);
$page->add($setting);
// Get the default category icon.
$defaultcategoryimage = get_config('theme_trending', 'defaultcategoryimage');
if (empty($defaultcategoryimage)) {
$defaultcategoryimage = '';
}
// Get all category IDs and their pretty names.
//require_once($CFG->libdir . '/coursecatlib.php');
$coursecats = core_course_category::make_categories_list();
//echo '<pre>';print_r($coursecats); exit;
// Go through all categories and create the necessary settings.
foreach ($coursecats as $key => $value) {
$category = core_course_category::get($key);
// Category image for each category.
$name = 'theme_trending/categoryimage';
$title = $value;
$description = get_string('categoryimagecategory', 'theme_trending', array('category' => $value));
//$default = $defaultcategoryimage;
if($category->parent==0){
$setting = new admin_setting_configstoredfile($name . $key, $title, $description, 'categoryimage'.$key);
}
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
}
unset($coursecats);
}
}
$settings->add($page);
// Footer settings page.
$page = new admin_settingpage('theme_trending_footer',  get_string('footersettings', 'theme_trending'));
//  footerlogo setting.
$name = 'theme_trending/footerlogo';
$title = get_string('footerlogo','theme_trending');
$description = get_string('footerlogo_desc', 'theme_trending');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'footerlogo');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// facebook url setting.
$name = 'theme_trending/facebook';
$title = get_string('facebook', 'theme_trending');
$description = get_string('facebook_desc', 'theme_trending');
$default = 'http://www.facebook.com/mycollege';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// twitter url setting.
$name = 'theme_trending/twitter';
$title = get_string('twitter', 'theme_trending');
$description = get_string('twitter_desc', 'theme_trending');
$default = 'http://www.twitter.com/mycollege';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// googleplus url setting.
$name = 'theme_trending/googleplus';
$title = get_string('googleplus', 'theme_trending');
$description = get_string('googleplus_desc', 'theme_trending');
$default = 'http://www.googleplus.com/mycollege';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// pinterest url setting.
$name = 'theme_trending/pinterest';
$title = get_string('pinterest', 'theme_trending');
$description = get_string('pinterest_desc', 'theme_trending');
$default = 'http://www.pinterest.com/mycollege';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// instagram url setting.
$name = 'theme_trending/instagram';
$title = get_string('instagram', 'theme_trending');
$description = get_string('instagram_desc', 'theme_trending');
$default = 'http://www.instagram.com/mycollege';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// youtube url setting.
$name = 'theme_trending/youtube';
$title = get_string('youtube', 'theme_trending');
$description = get_string('youtube_desc', 'theme_trending');
$default = 'http://www.youtube.com/mycollege';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// flickr url setting.
$name = 'theme_trending/flickr';
$title = get_string('flickr', 'theme_trending');
$description = get_string('flickr_desc', 'theme_trending');
$default = 'http://www.flickr.com/mycollege';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// whatsapp url setting.
$name = 'theme_trending/whatsapp';
$title = get_string('whatsapp', 'theme_trending');
$description = get_string('whatsapp_desc', 'theme_trending');
$default = 'http://www.whatsapp.com/mycollege';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// skype url setting.
$name = 'theme_trending/skype';
$title = get_string('skype', 'theme_trending');
$description = get_string('skype_desc', 'theme_trending');
$default = 'http://www.skype.com/mycollege';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// github url setting.
$name = 'theme_trending/github';
$title = get_string('github', 'theme_trending');
$description = get_string('github_desc', 'theme_trending');
$default = 'http://www.github.com/mycollege';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// Copyright setting.
$name = 'theme_trending/copyright';
$title = get_string('copyright', 'theme_trending');
$description = get_string('copyright_desc', 'theme_trending');
$default = 'cmsbrand';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);   
// Privacy Policy.
$name = 'theme_trending/privacypolicy';
$title = get_string('privacypolicy', 'theme_trending');
$description = get_string('privacypolicy_desc', 'theme_trending');
$default = 'Privacy Policy';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// Privacy Policy URL.
$name = 'theme_trending/privacypolicyurl';
$title = get_string('privacypolicyurl', 'theme_trending');
$description = get_string('privacypolicyurl_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// Back to top button.
$name = 'theme_trending/backtotop';
$title = get_string('backtotop', 'theme_trending');
$description = get_string('backtotop_desc', 'theme_trending');
$default = '1';
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);
// googleplayurl .
$name = 'theme_trending/googleplayurl';
$title = get_string('googleplayurl', 'theme_trending');
$description = get_string('googleplayurl_desc', 'theme_trending');
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
$settings->add($page);

// Advanced settings.
$page = new admin_settingpage('theme_trending_advanced', get_string('advancedsettings', 'theme_trending'));
// Raw SCSS to include before the content.
$setting = new admin_setting_scsscode('theme_trending/scsspre',
get_string('rawscsspre', 'theme_boost'), get_string('rawscsspre_desc', 'theme_boost'), '', PARAM_RAW);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
// Raw SCSS to include after the content.
$setting = new admin_setting_scsscode('theme_trending/scss', get_string('rawscss', 'theme_boost'),
get_string('rawscss_desc', 'theme_boost'), '', PARAM_RAW);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);
$settings->add($page);
