<?php
   defined('MOODLE_INTERNAL') || die();
   
   user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
   require_once($CFG->libdir . '/behat/lib.php');
   //require_once(dirname(__FILE__).'/fonts.php');
   
   
   // General Settings
   
   if (!empty($PAGE->theme->settings->favicon)) {
       $favicon = $PAGE->theme->setting_file_url('favicon', 'favicon');
   } else {
       $favicon = $OUTPUT->image_url('favicon', 'theme');
   }
   
   if (!empty($PAGE->theme->settings->logo)) {
    $logourl = $PAGE->theme->setting_file_url('logo', 'logo');
   }else{ 
   $logourl =$OUTPUT->image_url('/logo', 'theme');
   }
   if (!empty($PAGE->theme->settings->internalbannertagline)) {
     $internalbannertagline = theme_trending_get_setting('internalbannertagline',true);
   }else {
     $internalbannertagline = '';
   }
   // Slider Settings
   
   $displayslidersection = (empty($PAGE->theme->settings->displayslidersection) ||$PAGE->theme->settings->displayslidersection < 1) ? 0 : 1;
   // Slider1
   if (!empty($PAGE->theme->settings->slider1imageurl  )) {
     $slider1imageurl = theme_trending_get_setting('slider1imageurl',true);
   }else {
     $slider1imageurl = '';
   }
   if (!empty($PAGE->theme->settings->slider1caption)) {
     $slider1caption = theme_trending_get_setting('slider1caption',true);
   }else {
     $slider1caption = '';
   }
   if (!empty($PAGE->theme->settings->joinnowbuttontext)) {
     $joinnowbuttontext = theme_trending_get_setting('joinnowbuttontext',true);
   }else {
     $joinnowbuttontext = '';
   }
   if (!empty($PAGE->theme->settings->joinnowbuttonurl)) {
     $joinnowbuttonurl = theme_trending_get_setting('joinnowbuttonurl',true);
   }else {
     $joinnowbuttonurl = '';
   }
   if (!empty($PAGE->theme->settings->readmorebuttontext)) {
     $readmorebuttontext = theme_trending_get_setting('readmorebuttontext',true);
   }else {
     $readmorebuttontext = '';
   }
   if (!empty($PAGE->theme->settings->readmorebuttonurl)) {
     $readmorebuttonurl = theme_trending_get_setting('readmorebuttonurl',true);
   }else {
     $readmorebuttonurl = '';
   }
   // Slider2
   if (!empty($PAGE->theme->settings->slider2imageurl  )) {
     $slider2imageurl = theme_trending_get_setting('slider2imageurl',true);
   }else {
     $slider2imageurl = '';
   }
   if (!empty($PAGE->theme->settings->slider2caption)) {
     $slider2caption = theme_trending_get_setting('slider2caption',true);
   }else {
     $slider2caption = '';
   }
   if (!empty($PAGE->theme->settings->startcoursebuttontext)) {
     $startcoursebuttontext = theme_trending_get_setting('startcoursebuttontext',true);
   }else {
     $startcoursebuttontext = '';
   }
   if (!empty($PAGE->theme->settings->startcoursebuttonurl)) {
     $startcoursebuttonurl = theme_trending_get_setting('startcoursebuttonurl',true);
   }else {
     $startcoursebuttonurl = '';
   }
   if (!empty($PAGE->theme->settings->taketourbuttontext)) {
     $taketourbuttontext = theme_trending_get_setting('taketourbuttontext',true);
   }else {
     $taketourbuttontext = '';
   }
   if (!empty($PAGE->theme->settings->taketourbuttonurl)) {
     $taketourbuttonurl = theme_trending_get_setting('taketourbuttonurl',true);
   }else {
     $taketourbuttonurl = '';
   }
   // Slider3
   if (!empty($PAGE->theme->settings->slider3imageurl  )) {
     $slider3imageurl = theme_trending_get_setting('slider3imageurl',true);
   }else {
     $slider3imageurl = '';
   }
   if (!empty($PAGE->theme->settings->slider3caption)) {
     $slider3caption = theme_trending_get_setting('slider3caption',true);
   }else {
     $slider3caption = '';
   }
   // Slider4
   if (!empty($PAGE->theme->settings->slider4imageurl  )) {
     $slider4imageurl = theme_trending_get_setting('slider4imageurl',true);
   }else {
     $slider4imageurl = '';
   }
   if (!empty($PAGE->theme->settings->slider4caption)) {
     $slider4caption = theme_trending_get_setting('slider4caption',true);
   }else {
     $slider4caption = '';
   }
   if (!empty($PAGE->theme->settings->slider4tagline)) {
     $slider4tagline = theme_trending_get_setting('slider4tagline',true);
   }else {
     $slider4tagline = '';
   }
   // Slider5
   if (!empty($PAGE->theme->settings->slider5imageurl  )) {
     $slider5imageurl = theme_trending_get_setting('slider5imageurl',true);
   }else {
     $slider5imageurl = '';
   }
   if (!empty($PAGE->theme->settings->slider5caption)) {
     $slider5caption = theme_trending_get_setting('slider5caption',true);
   }else {
     $slider5caption = '';
   }
   // Slider6
   if (!empty($PAGE->theme->settings->slider6imageurl  )) {
     $slider6imageurl = theme_trending_get_setting('slider6imageurl',true);
   }else {
     $slider6imageurl = '';
   }
   if (!empty($PAGE->theme->settings->slider6caption)) {
     $slider6caption = theme_trending_get_setting('slider6caption',true);
   }else {
     $slider6caption = '';
   }
   if (!empty($PAGE->theme->settings->slider6tagline)) {
     $slider6tagline = theme_trending_get_setting('slider6tagline',true);
   }else {
     $slider6tagline = '';
   }
   if (!empty($PAGE->theme->settings->slider6buttontext)) {
     $slider6buttontext = theme_trending_get_setting('slider6buttontext',true);
   }else {
     $slider6buttontext = '';
   }
   if (!empty($PAGE->theme->settings->slider6buttonurl)) {
     $slider6buttonurl = theme_trending_get_setting('slider6buttonurl',true);
   }else {
     $slider6buttonurl = '';
   }
   // Frontpage Settings
   
   if (!empty($PAGE->theme->settings->categorytagline)) {
        $categorytagline = theme_trending_get_setting('categorytagline',true);
      }else {
        $categorytagline = '';
      }
   
    if (!empty($PAGE->theme->settings->allcoursestagline)) {
        $allcoursestagline = theme_trending_get_setting('allcoursestagline',true);
      }else {
        $allcoursestagline = '';
      }
   
   if (!empty($PAGE->theme->settings->enrolledcoursestagline)) {
        $enrolledcoursestagline = theme_trending_get_setting('enrolledcoursestagline',true);
      }else {
        $enrolledcoursestagline = '';
      }
   
   if (!empty($PAGE->theme->settings->sitenewstagline)) {
        $sitenewstagline = theme_trending_get_setting('sitenewstagline',true);
      }else {
        $sitenewstagline = '';
      }
   
   if (!empty($PAGE->theme->settings->blockcustombutton)) {
     $blockcustombutton = theme_trending_get_setting('blockcustombutton',true);
   }else {
     $blockcustombutton = '';
   }
   if (!empty($PAGE->theme->settings->blockcustombuttonurl)) {
     $blockcustombuttonurl = theme_trending_get_setting('blockcustombuttonurl',true);
   }else {
     $blockcustombuttonurl = '';
   }
   if (!empty($PAGE->theme->settings->blockheading)) {
     $blockheading = theme_trending_get_setting('blockheading',true);
   }else {
     $blockheading = '';
   }
   if (!empty($PAGE->theme->settings->blocktagline)) {
     $blocktagline = theme_trending_get_setting('blocktagline',true);
   }else {
     $blocktagline = '';
   }
    if (!empty($PAGE->theme->settings->callus)) {
      $callus = theme_trending_get_setting('callus',true);
    }else {
      $callus = '';
    }
    if (!empty($PAGE->theme->settings->callusurl)) {
      $callusurl = theme_trending_get_setting('callusurl',true);
    }else {
      $callusurl = '';
    }
    if (!empty($PAGE->theme->settings->emailus)) {
      $emailus = theme_trending_get_setting('emailus',true);
    }else {
      $emailus = '';
    }
    if (!empty($PAGE->theme->settings->emailusurl)) {
      $emailusurl = theme_trending_get_setting('emailusurl',true);
    }else {
      $emailusurl = '';
    }
    if (!empty($PAGE->theme->settings->livechat)) {
      $livechat = theme_trending_get_setting('livechat',true);
    }else {
      $livechat = '';
    }
    if (!empty($PAGE->theme->settings->livechaturl)) {
      $livechaturl = theme_trending_get_setting('livechaturl',true);
    }else {
      $livechaturl = '';
    }
    // About Us
   
   $displayaboutussection = (empty($PAGE->theme->settings->displayaboutussection) ||$PAGE->theme->settings->displayaboutussection < 1) ? 0 : 1;
   if (!empty($PAGE->theme->settings->aboutusheading)) {
     $aboutusheading = theme_trending_get_setting('aboutusheading',true);
   }else {
     $aboutusheading = '';
   }
   if (!empty($PAGE->theme->settings->aboutustagline)) {
     $aboutustagline = theme_trending_get_setting('aboutustagline',true);
   }else {
     $aboutustagline = '';
   }   
   // aboutusimage
   if (!empty($PAGE->theme->settings->aboutusimage)) {
      $aboutusimage = $PAGE->theme->setting_file_url('aboutusimage', 'aboutusimage');
   } else {
       $aboutusimage = $OUTPUT->image_url('aboutusimage', 'theme');
   }   
   if (!empty($PAGE->theme->settings->aboutusdescription)) {
     $aboutusdescription = theme_trending_get_setting('aboutusdescription',true);
   }else {
     $aboutusdescription = '';
   }
   if (!empty($PAGE->theme->settings->accordionbox1heading)) {
     $accordionbox1heading = theme_trending_get_setting('accordionbox1heading',true);
   }else {
     $accordionbox1heading = '';
   }
   if (!empty($PAGE->theme->settings->accordionbox1description)) {
     $accordionbox1description = theme_trending_get_setting('accordionbox1description','format_html');
   }else {
     $accordionbox1description = '';
   }
   if (!empty($PAGE->theme->settings->accordionbox2heading)) {
     $accordionbox2heading = theme_trending_get_setting('accordionbox2heading',true);
   }else {
     $accordionbox2heading = '';
   }
   if (!empty($PAGE->theme->settings->accordionbox2description)) {
     $accordionbox2description = theme_trending_get_setting('accordionbox2description','format_html');
   }else {
     $accordionbox2description = '';
   }
   
   if (!empty($PAGE->theme->settings->accordionbox3heading)) {
     $accordionbox3heading = theme_trending_get_setting('accordionbox3heading',true);
   }else {
     $accordionbox3heading = '';
   }
   if (!empty($PAGE->theme->settings->accordionbox3description)) {
     $accordionbox3description = theme_trending_get_setting('accordionbox3description','format_html');
   }else {
     $accordionbox3description = '';
   }
   
   if (!empty($PAGE->theme->settings->accordionbox4heading)) {
     $accordionbox4heading = theme_trending_get_setting('accordionbox4heading',true);
   }else {
     $accordionbox4heading = '';
   }
   if (!empty($PAGE->theme->settings->accordionbox4description)) {
     $accordionbox4description = theme_trending_get_setting('accordionbox4description','format_html');
   }else {
     $accordionbox4description = '';
   }
   $displaymarketingsection = (empty($PAGE->theme->settings->displaymarketingsection) ||$PAGE->theme->settings->displaymarketingsection < 1) ? 0 : 1;
   
   if (!empty($PAGE->theme->settings->marketingheading)) {
     $marketingheading = theme_trending_get_setting('marketingheading',true);
   }else {
     $marketingheading = '';
   }
   if (!empty($PAGE->theme->settings->marketingbuttontext)) {
     $marketingbuttontext = theme_trending_get_setting('marketingbuttontext',true);
   }else {
     $marketingbuttontext = '';
   }
   if (!empty($PAGE->theme->settings->marketingbuttonurl)) {
     $marketingbuttonurl = theme_trending_get_setting('marketingbuttonurl',true);
   }else {
     $marketingbuttonurl = '';
   }
   $displaytutorsection = (empty($PAGE->theme->settings->displaytutorsection) ||$PAGE->theme->settings->displaytutorsection < 1) ? 0 : 1;
   
   if (!empty($PAGE->theme->settings->tutorssectionheading )) {
     $tutorssectionheading  = theme_trending_get_setting('tutorssectionheading',true);
   }else {
     $tutorssectionheading  = '';
   }
   if (!empty($PAGE->theme->settings->tutorssectiontagline  )) {
     $tutorssectiontagline   = theme_trending_get_setting('tutorssectiontagline',true);
   }else {
     $tutorssectiontagline   = '';
   }
   $displaytutor1 = (empty($PAGE->theme->settings->displaytutor1) ||$PAGE->theme->settings->displaytutor1 < 1) ? 0 : 1;
   
   if (!empty($PAGE->theme->settings->tutor1image)) {
     $tutor1image = $PAGE->theme->setting_file_url('tutor1image', 'tutor1image');
   } else {
     $tutor1image = $OUTPUT->image_url('tutors/01', 'theme');
   }
   if (!empty($PAGE->theme->settings->tutor1name)) {
     $tutor1name = theme_trending_get_setting('tutor1name',true);
   }else {
     $tutor1name = '';
   }
   if (!empty($PAGE->theme->settings->tutor1url)) {
     $tutor1url = theme_trending_get_setting('tutor1url',true);
   }else {
     $tutor1url = '';
   }
   if (!empty($PAGE->theme->settings->tutor1designation)) {
     $tutor1designation = theme_trending_get_setting('tutor1designation',true);
   }else {
     $tutor1designation = '';
   }
   $displaytutor2 = (empty($PAGE->theme->settings->displaytutor2) ||$PAGE->theme->settings->displaytutor2 < 1) ? 0 : 1;
   
   if (!empty($PAGE->theme->settings->tutor2image)) {
     $tutor2image = $PAGE->theme->setting_file_url('tutor2image', 'tutor2image');
   } else {
     $tutor2image = $OUTPUT->image_url('tutors/02', 'theme');
   }
   if (!empty($PAGE->theme->settings->tutor2name)) {
     $tutor2name = theme_trending_get_setting('tutor2name',true);
   }else {
     $tutor2name = '';
   }
   if (!empty($PAGE->theme->settings->tutor2url)) {
     $tutor2url = theme_trending_get_setting('tutor2url',true);
   }else {
     $tutor2url = '';
   }
   if (!empty($PAGE->theme->settings->tutor2designation)) {
     $tutor2designation = theme_trending_get_setting('tutor2designation',true);
   }else {
     $tutor2designation = '';
   }
   $displaytutor3 = (empty($PAGE->theme->settings->displaytutor3) ||$PAGE->theme->settings->displaytutor3 < 1) ? 0 : 1;
   
   if (!empty($PAGE->theme->settings->tutor3image)) {
     $tutor3image = $PAGE->theme->setting_file_url('tutor3image', 'tutor3image');
   } else {
     $tutor3image = $OUTPUT->image_url('tutors/03', 'theme');
   }
   if (!empty($PAGE->theme->settings->tutor3name)) {
     $tutor3name = theme_trending_get_setting('tutor3name',true);
   }else {
     $tutor3name = '';
   }
   if (!empty($PAGE->theme->settings->tutor3url)) {
     $tutor3url = theme_trending_get_setting('tutor3url',true);
   }else {
     $tutor3url = '';
   }
   if (!empty($PAGE->theme->settings->tutor3designation)) {
     $tutor3designation = theme_trending_get_setting('tutor3designation',true);
   }else {
     $tutor3designation = '';
   }
   $displaytutor4 = (empty($PAGE->theme->settings->displaytutor4) ||$PAGE->theme->settings->displaytutor4 < 1) ? 0 : 1;
   
   if (!empty($PAGE->theme->settings->tutor4image)) {
     $tutor4image = $PAGE->theme->setting_file_url('tutor4image', 'tutor4image');
   } else {
     $tutor4image = $OUTPUT->image_url('tutors/04', 'theme');
   }
   if (!empty($PAGE->theme->settings->tutor4name)) {
     $tutor4name = theme_trending_get_setting('tutor4name',true);
   }else {
     $tutor4name = '';
   }
   if (!empty($PAGE->theme->settings->tutor4url)) {
     $tutor4url = theme_trending_get_setting('tutor4url',true);
   }else {
     $tutor4url = '';
   }
   if (!empty($PAGE->theme->settings->tutor4designation)) {
     $tutor4designation = theme_trending_get_setting('tutor4designation',true);
   }else {
     $tutor4designation = '';
   }
   $displaytutor5 = (empty($PAGE->theme->settings->displaytutor5) ||$PAGE->theme->settings->displaytutor5 < 1) ? 0 : 1;
   
   if (!empty($PAGE->theme->settings->tutor5image)) {
     $tutor5image = $PAGE->theme->setting_file_url('tutor5image', 'tutor5image');
   } else {
     $tutor5image = $OUTPUT->image_url('tutors/05', 'theme');
   }
   if (!empty($PAGE->theme->settings->tutor5name)) {
     $tutor5name = theme_trending_get_setting('tutor5name',true);
   }else {
     $tutor5name = '';
   }
   if (!empty($PAGE->theme->settings->tutor5url)) {
     $tutor5url = theme_trending_get_setting('tutor5url',true);
   }else {
     $tutor5url = '';
   }
   if (!empty($PAGE->theme->settings->tutor5designation)) {
     $tutor5designation = theme_trending_get_setting('tutor5designation',true);
   }else {
     $tutor5designation = '';
   }
   $displaytutor6 = (empty($PAGE->theme->settings->displaytutor6) ||$PAGE->theme->settings->displaytutor6 < 1) ? 0 : 1;
   
   if (!empty($PAGE->theme->settings->tutor6image)) {
     $tutor6image = $PAGE->theme->setting_file_url('tutor6image', 'tutor6image');
   } else {
     $tutor6image = $OUTPUT->image_url('tutors/06', 'theme');
   }
   if (!empty($PAGE->theme->settings->tutor6name)) {
     $tutor6name = theme_trending_get_setting('tutor6name',true);
   }else {
     $tutor6name = '';
   }
   if (!empty($PAGE->theme->settings->tutor6url)) {
     $tutor6url = theme_trending_get_setting('tutor6url',true);
   }else {
     $tutor6url = '';
   }
   if (!empty($PAGE->theme->settings->tutor6designation)) {
     $tutor6designation = theme_trending_get_setting('tutor6designation',true);
   }else {
     $tutor6designation = '';
   }
   $displaytutor7 = (empty($PAGE->theme->settings->displaytutor7) ||$PAGE->theme->settings->displaytutor7 < 1) ? 0 : 1;
   
   if (!empty($PAGE->theme->settings->tutor7image)) {
     $tutor7image = $PAGE->theme->setting_file_url('tutor7image', 'tutor7image');
   } else {
     $tutor7image = $OUTPUT->image_url('tutors/07', 'theme');
   }
   if (!empty($PAGE->theme->settings->tutor7name)) {
     $tutor7name = theme_trending_get_setting('tutor7name',true);
   }else {
     $tutor7name = '';
   }
   if (!empty($PAGE->theme->settings->tutor7url)) {
     $tutor7url = theme_trending_get_setting('tutor7url',true);
   }else {
     $tutor7url = '';
   }
   if (!empty($PAGE->theme->settings->tutor7designation)) {
     $tutor7designation = theme_trending_get_setting('tutor7designation',true);
   }else {
     $tutor7designation = '';
   }
   $displaytutor8 = (empty($PAGE->theme->settings->displaytutor8) ||$PAGE->theme->settings->displaytutor8 < 1) ? 0 : 1;
   
   if (!empty($PAGE->theme->settings->tutor8image)) {
     $tutor8image = $PAGE->theme->setting_file_url('tutor8image', 'tutor8image');
   } else {
     $tutor8image = $OUTPUT->image_url('tutors/08', 'theme');
   }
   if (!empty($PAGE->theme->settings->tutor8name)) {
     $tutor8name = theme_trending_get_setting('tutor8name',true);
   }else {
     $tutor8name = '';
   }
   if (!empty($PAGE->theme->settings->tutor8url)) {
     $tutor8url = theme_trending_get_setting('tutor8url',true);
   }else {
     $tutor8url = '';
   }
   if (!empty($PAGE->theme->settings->tutor8designation)) {
     $tutor8designation = theme_trending_get_setting('tutor8designation',true);
   }else {
     $tutor8designation = '';
   }
   $displaytutor9 = (empty($PAGE->theme->settings->displaytutor9) ||$PAGE->theme->settings->displaytutor9 < 1) ? 0 : 1;
   
   if (!empty($PAGE->theme->settings->tutor9image)) {
     $tutor9image = $PAGE->theme->setting_file_url('tutor9image', 'tutor9image');
   } else {
     $tutor9image = $OUTPUT->image_url('tutors/09', 'theme');
   }
   if (!empty($PAGE->theme->settings->tutor9name)) {
     $tutor9name = theme_trending_get_setting('tutor9name',true);
   }else {
     $tutor9name = '';
   }
   if (!empty($PAGE->theme->settings->tutor9url)) {
     $tutor9url = theme_trending_get_setting('tutor9url',true);
   }else {
     $tutor9url = '';
   }
   if (!empty($PAGE->theme->settings->tutor9designation)) {
     $tutor9designation = theme_trending_get_setting('tutor9designation',true);
   }else {
     $tutor9designation = '';
   }
   // Student Section
   $displaystudentsection = (empty($PAGE->theme->settings->displaystudentsection) ||$PAGE->theme->settings->displaystudentsection < 1) ? 0 : 1;
   if (!empty($PAGE->theme->settings->studentsectionheading)) {
     $studentsectionheading = theme_trending_get_setting('studentsectionheading',true);
   }else {
     $studentsectionheading = '';
   }
   if (!empty($PAGE->theme->settings->studentsectiontagline)) {
     $studentsectiontagline = theme_trending_get_setting('studentsectiontagline',true);
   }else {
     $studentsectiontagline = '';
   }
   if (!empty($PAGE->theme->settings->student1backgroundurl)) {
     $student1backgroundurl = theme_trending_get_setting('student1backgroundurl',true);
   }else {
     $student1backgroundurl = '';
   }
   if (!empty($PAGE->theme->settings->student1name)) {
     $student1name = theme_trending_get_setting('student1name',true);
   }else {
     $student1name = '';
   }
   if (!empty($PAGE->theme->settings->student1description )) {
     $student1description  = theme_trending_get_setting('student1description',true);
   }else {
     $student1description  = '';
   }
   if (!empty($PAGE->theme->settings->student2backgroundurl)) {
     $student2backgroundurl = theme_trending_get_setting('student2backgroundurl',true);
   }else {
     $student2backgroundurl = '';
   }
   if (!empty($PAGE->theme->settings->student2name)) {
     $student2name = theme_trending_get_setting('student2name',true);
   }else {
     $student2name = '';
   }
   if (!empty($PAGE->theme->settings->student2description )) {
     $student2description  = theme_trending_get_setting('student2description',true);
   }else {
     $student2description  = '';
   }
   if (!empty($PAGE->theme->settings->student3backgroundurl)) {
     $student3backgroundurl = theme_trending_get_setting('student3backgroundurl',true);
   }else {
     $student3backgroundurl = '';
   }
   if (!empty($PAGE->theme->settings->student3name)) {
     $student3name = theme_trending_get_setting('student3name',true);
   }else {
     $student3name = '';
   }
   if (!empty($PAGE->theme->settings->student3description )) {
     $student3description  = theme_trending_get_setting('student3description',true);
   }else {
     $student3description  = '';
   }
   if (!empty($PAGE->theme->settings->student4backgroundurl)) {
     $student4backgroundurl = theme_trending_get_setting('student4backgroundurl',true);
   }else {
     $student4backgroundurl = '';
   }
   if (!empty($PAGE->theme->settings->student4name)) {
     $student4name = theme_trending_get_setting('student4name',true);
   }else {
     $student4name = '';
   }
   if (!empty($PAGE->theme->settings->student4description )) {
     $student4description  = theme_trending_get_setting('student4description',true);
   }else {
     $student4description  = '';
   }
   
   if (!empty($PAGE->theme->settings->student5backgroundurl)) {
     $student5backgroundurl = theme_trending_get_setting('student5backgroundurl',true);
   }else {
     $student5backgroundurl = '';
   }
   if (!empty($PAGE->theme->settings->student5name)) {
     $student5name = theme_trending_get_setting('student5name',true);
   }else {
     $student5name = '';
   }
   if (!empty($PAGE->theme->settings->student5description )) {
     $student5description  = theme_trending_get_setting('student5description',true);
   }else {
     $student5description  = '';
   }
   // newsletter
   $displaynewslettersection = (empty($PAGE->theme->settings->displaynewslettersection) ||$PAGE->theme->settings->displaynewslettersection < 1) ? 0 : 1;
   if (!empty($PAGE->theme->settings->newsletterheading)) {
     $newsletterheading = theme_trending_get_setting('newsletterheading',true);
   }else {
     $newsletterheading = '';
   }
   if (!empty($PAGE->theme->settings->newslettertagline)) {
     $newslettertagline = theme_trending_get_setting('newslettertagline',true);
   }else {
     $newslettertagline = '';
   }
   
   // Category Section
   
      if (!empty($PAGE->theme->settings->categorytagline)) {
           $categorytagline = theme_trending_get_setting('categorytagline',true);
         }else {
           $categorytagline = '';
         }
   
         if (!empty($PAGE->theme->settings->enablecategoryimage)) {
           $enablecategoryimage = theme_trending_get_setting('enablecategoryimage',true);
         }else {
           $enablecategoryimage = '';
         }
   
   // Footer Settings
   
   if (!empty($PAGE->theme->settings->footerlogo)) {
     $footerlogo = $PAGE->theme->setting_file_url('footerlogo', 'footerlogo');
   } else {
     $footerlogo = $OUTPUT->image_url('footer-logo', 'theme');
   }
   
   $hasfacebook    = (empty($PAGE->theme->settings->facebook)) ? false : $PAGE->theme->settings->facebook;
   // If any of the above social networks are true, sets this to true.
   $hassocialnetworks = ($hasfacebook) ? true : false;
   
   $hastwitter    = (empty($PAGE->theme->settings->twitter)) ? false : $PAGE->theme->settings->twitter;
   // If any of the above social networks are true, sets this to true.
   $hassocialnetworks = ($hastwitter) ? true : false;
   
   $hasgoogleplus    = (empty($PAGE->theme->settings->googleplus)) ? false : $PAGE->theme->settings->googleplus;
   // If any of the above social networks are true, sets this to true.
   $hassocialnetworks = ($hasgoogleplus) ? true : false;
   
   $haspinterest    = (empty($PAGE->theme->settings->pinterest)) ? false : $PAGE->theme->settings->pinterest;
   // If any of the above social networks are true, sets this to true.
   $hassocialnetworks = ($haspinterest) ? true : false;
   
   $hasinstagram    = (empty($PAGE->theme->settings->instagram)) ? false : $PAGE->theme->settings->instagram;
   // If any of the above social networks are true, sets this to true.
   $hassocialnetworks = ($hasinstagram) ? true : false;
   
   $hasyoutube    = (empty($PAGE->theme->settings->youtube)) ? false : $PAGE->theme->settings->youtube;
   // If any of the above social networks are true, sets this to true.
   $hassocialnetworks = ($hasyoutube) ? true : false;
   
   $hasflickr    = (empty($PAGE->theme->settings->flickr)) ? false : $PAGE->theme->settings->flickr;
   // If any of the above social networks are true, sets this to true.
   $hassocialnetworks = ($hasflickr) ? true : false;
   
   $haswhatsapp    = (empty($PAGE->theme->settings->whatsapp)) ? false : $PAGE->theme->settings->whatsapp;
   // If any of the above social networks are true, sets this to true.
   $hassocialnetworks = ($haswhatsapp) ? true : false;
   
   $hasskype    = (empty($PAGE->theme->settings->skype)) ? false : $PAGE->theme->settings->skype;
   // If any of the above social networks are true, sets this to true.
   $hassocialnetworks = ($hasskype) ? true : false;
   
   $hasgithub    = (empty($PAGE->theme->settings->github)) ? false : $PAGE->theme->settings->github;
   // If any of the above social networks are true, sets this to true.
   $hassocialnetworks = ($hasgithub) ? true : false;
   $copyrightY = date("Y");
   if (!empty($PAGE->theme->settings->copyright)) {
           $hascopyright = theme_trending_get_setting('copyright',true);
    } 
    else {
           $hascopyright = '';
    }
    if (!empty($PAGE->theme->settings->privacypolicy)) {
           $privacypolicy = theme_trending_get_setting('privacypolicy',true);
    } 
    else {
           $privacypolicy = '';
    }
    if (!empty($PAGE->theme->settings->privacypolicyurl)) {
           $privacypolicyurl = $PAGE->theme->settings->privacypolicyurl;
    } 
    else {
           $privacypolicyurl = '#';
    }
         if (!empty($PAGE->theme->settings->backtotop)) {
           $backtotop = theme_trending_get_setting('backtotop',true);
         }else {
           $backtotop = '';
         }
   
   if (!empty($PAGE->theme->settings->googleplayurl)) {
     $googleplayurl = theme_trending_get_setting('googleplayurl',true);
   }else {
     $googleplayurl = '';
   }
         
   
   
   ?>
