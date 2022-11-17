<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines forms.
 *
 * @package    local_mb2notices
 * @copyright  2019 - 2020 Mariusz Boloz (mb2themes.com)
 * @license    Commercial https://themeforest.net/licenses
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/api.php');

if ( ! class_exists( 'Mb2noticesHelper' ) )
{
    class Mb2noticesHelper
    {


        public static function get_image_url($itemid)
        {
            global $CFG;

            require_once( $CFG->libdir . '/filelib.php' );
            $context = context_system::instance();
            $url = '';
            $fs = get_file_storage();
            $files = $fs->get_area_files( $context->id, 'local_mb2notices', 'attachment', $itemid );

            foreach ($files as $f)
            {
                $url = moodle_url::make_pluginfile_url($f->get_contextid(), $f->get_component(), $f->get_filearea(), $f->get_itemid(), $f->get_filepath(), $f->get_filename(), false);
            }

            return $url;

        }






        /**
         *
         * Method to update get settings.
         *
         */
        public static function get_param($itemid, $param)
        {

            $opt = get_config('local_mb2notices');
            $paramname = $param;
            $item = Mb2noticesApi::get_record($itemid);
            $attribsobj = json_decode($item->attribs);
            $attribs = json_decode($item->attribs, true);
            $isattribs = array_key_exists($param, $attribs);

            if ($isattribs)
            {
                $item = $attribsobj;
            }

            if ( isset( $item->$param ) )
            {
                // Intiger values
                // '999' is a global value
                if ( is_numeric( $item->$param ) && $item->$param != 999 )
                {
                    return $item->$param;
                }

                // String values
                elseif ( ! is_numeric( $item->$param ) && $item->$param !== '' )
                {
                    return $item->$param;
                }

                else
                {
                    // Return global option
                    return $opt->$param;
                }
            }
            else
            {
               return $opt->$param;
            }

        }





        /**
         *
         * Method to update get settings.
         *
         */
        public static function get_item_content($item)
        {
            $context = context_system::instance();

            if ( strip_tags( $item->content ) === '' )
            {
                return;
            }

            $text = file_rewrite_pluginfile_urls($item->content, 'pluginfile.php', $context->id, 'local_mb2notices', 'content', $item->id);
            $text = format_text($text);

            return $text;

        }





        /*
         *
         * Method to get user roles
         *
         */
        public static function get_roles()
        {
            return role_fix_names( get_all_roles() );
        }





        /*
         *
         * Method to get user roles to selct from field
         *
         */
        public static function get_roles_to_select()
        {
            $select_roles = array();
            $roles = self::get_roles();

            foreach($roles as $role)
            {
                $select_roles[$role->shortname] = $role->localname;
            }

            // Sort array by role name
            asort( $select_roles );

            return $select_roles;

        }



        /*
         *
         * Method to get user roles id by roleshortname
         *
         */
        public static function get_role_id( $rolename )
        {

            $roles = self::get_roles();

            foreach( $roles as $role )
            {
                if ( $role->shortname === $rolename )
                {
                    return $role->id;
                }
            }

            return 0;

        }




        /*
         *
         * Method to get user
         *
         */
        public static function get_user($id)
        {
            global $DB;

            if (!$id)
            {
                return;
            }

            return $DB->get_record('user', array('id'=>$id));
        }








        /*
         *
         * Method to get item languages array
         *
         */
        public static function get_languages($item)
        {

            if (!$item)
            {
                return array();
            }

            // Get json languages
            $languages = json_decode($item->language, true);

            // Remove empty values
            $languages = array_filter($languages);

            return $languages;

        }




        /*
         *
         * Method to check if user can see item
         *
         */
        public static function can_see( $item )
        {

            // Check if item is published
            if ( ! $item->enable )
            {
                return false;
            }

            // Chech item date status
            if ( self::date_status( $item ) < 0 )
            {
                return false;
            }

            // Check where notice appears
            if ( ! self::showon_status( $item ) )
            {
                return false;
            }

            // Check who can see notice
            if ( ! self::access_status( $item ) )
            {
                return false;
            }

            // Check language
            $itemlanguages = self::get_languages( $item );

            if ( count( $itemlanguages ) && ! in_array( current_language(), $itemlanguages ) )
            {
                return false;
            }

            return true;

        }




        /**
         *
         * Method to check array value (positive or negative).
         *
         */
        public static function array_positive( $array = array() )
        {

            if ( count( $array ) == 0)
            {
                return;
            }

            foreach ( $array as $v )
            {
                if ( $v < 0 )
                {
                    return false;
                }
            }

            return true;

        }





        /**
         *
         * Method to check where notice appears.
         *
         */
        public static function showon_status( $item )
        {

            global $COURSE, $PAGE;
            $attribs = json_decode( $item->attribs );

            // Notice on front page
            if ( $attribs->showon == 1 && $PAGE->pagetype !== 'site-index' )
            {
                return false;
            }

            // Notice on course page
            elseif ( $attribs->showon == 2 )
            {
                $iscourse = ( $COURSE->id > 1 );

                // Hide notices if there is no course
                if ( ! $iscourse )
                {
                    return false;
                }

                // Get course IDs array
                $coursesids = explode( ',', $attribs->courseids );
                $coursesids = array_map( 'trim', $coursesids );

                if ( $coursesids[0] )
                {
                    // Include course IDs
                    if ( self::array_positive( $coursesids ) && ! in_array( $COURSE->id, $coursesids ) )
                    {
                        return false;
                    }

                    // Exclude course IDs
                    elseif ( ! self::array_positive( $coursesids ) && in_array( $COURSE->id * -1, $coursesids ) )
                    {
                        return false;
                    }
                }
            }

            // Notice on dashboard page
            elseif ( $attribs->showon == 3 &&  $PAGE->pagetype !== 'my-index' )
            {
                return false;
            }

            // Notice on login page
            elseif ( $attribs->showon == 4 && ! preg_match( '@login@', $PAGE->pagetype )  )
            {
                return false;
            }

            // Notice on calendar page
            elseif ( $attribs->showon == 5 && ! preg_match( '@calendar@', $PAGE->pagetype ) )
            {
                return false;
            }

            return true;

        }





        /**
         *
         * Method to check who can see notice.
         *
         */
        public static function access_status( $item )
        {

            global $USER, $PAGE;
            $attribs = json_decode( $item->attribs );
            $opt = get_config( 'local_mb2notices' );

            // Notice for users
            if ( $attribs->cansee == 1 )
            {
                if ( ! isloggedin() || isguestuser() )
                {
                    return false;
                }

                // Get user IDs array
                $userids = explode( ',', $attribs->userids );
                $userids = array_map( 'trim', $userids );

                if ( $userids[0] )
                {
                    // Include course IDs
                    if ( self::array_positive( $userids ) && ! in_array( $USER->id, $userids ) )
                    {
                        return false;
                    }

                    // Exclude course IDs
                    elseif ( ! self::array_positive( $userids ) && in_array( $USER->id * -1, $userids ) )
                    {
                        return false;
                    }
                }
            }

            // Notice for students
            elseif ( $attribs->cansee == 3 )
            {
                $studetroleid = self::get_role_id( $opt->rolestudent );

                if ( ! user_has_role_assignment( $USER->id, $studetroleid ) )
                {
                    return false;
                }
            }

            // Notice for teachers
            elseif ( $attribs->cansee == 4 )
            {
                $teacherroleid = self::get_role_id( $opt->roleteacher );

                if ( ! user_has_role_assignment( $USER->id, $teacherroleid ) )
                {
                    return false;
                }
            }

            // Notice for customrole 1
            elseif ( $attribs->cansee == 5 )
            {
                $rolecustom1id = self::get_role_id( $opt->rolecustom1 );

                if ( ! user_has_role_assignment( $USER->id, $rolecustom1id ) )
                {
                    return false;
                }
            }

            // Notice for customrole 2
            elseif ( $attribs->cansee == 6 )
            {
                $rolecustom2id = self::get_role_id( $opt->rolecustom2 );

                if ( ! user_has_role_assignment( $USER->id, $rolecustom2id ) )
                {
                    return false;
                }
            }

            // Notice for customrole 3
            elseif ( $attribs->cansee == 5 )
            {
                $rolecustom3id = self::get_role_id( $opt->rolecustom3 );

                if ( ! user_has_role_assignment( $USER->id, $rolecustom3id ) )
                {
                    return false;
                }
            }

            // Notice for guests
            elseif ( $attribs->cansee == 2 && ( isloggedin() && !isguestuser() ) )
            {
                return false;
            }

            return true;

        }




        /**
         *
         * Method to check item date status.
         *
         */
        public static function date_status( $item )
        {

            $usertime = self::get_user_date();

            if ( $item->timestart >= $usertime )
            {
                return -1;
            }
            elseif ( $item->timeend > 0 && $item->timeend < $usertime )
            {
                return -2;
            }

            return 1;

        }





        /**
         *
         * Method to check ite date status.
         *
         */
        public static function get_user_date()
        {

            $date = new DateTime( 'now', core_date::get_user_timezone_object() );
            $time = $date->getTimestamp();
            return $time;

        }




    }

}
