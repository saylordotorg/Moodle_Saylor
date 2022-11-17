<div class="loginsection pull-right">
            <?php if(isloggedin()){
               if(isguestuser()){
               ?>
            <a class="login" href="<?php echo new moodle_url('/login/index.php', array('sesskey'=>sesskey())), get_string('login') ?> "> 
            <?php echo get_string('login') ?>
            </a>
            <?php
               }else{
               ?>
          
            <?php
               }
               }else{ ?>   
            <?php
               if(!empty($CFG->registerauth)){
                   $authplugin = get_auth_plugin($CFG->registerauth);
                   if($authplugin->can_signup()){
                     
                     ?>
            <a class="signup" href="<?php echo $CFG->wwwroot.'/login/signup.php' ?>">Register</a>
            <?php
               }
               }
               ?>
            <a class="login" href="<?php echo new moodle_url('/login/index.php', array('sesskey'=>sesskey())), get_string('login') ?> "><?php echo get_string('login') ?>
            </a>
            <?php
               }
               ?>
         </div>

