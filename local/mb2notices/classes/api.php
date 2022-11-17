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



if ( ! class_exists( 'Mb2noticesApi' ) )
{
    class Mb2noticesApi
    {



        /**
         *
         * Method to get a list of all services.
         *
         */
        public static function get_list_records($limitfrom = 0, $limitnum = 0)
        {
            global $DB;

            $records = $DB->get_records('local_mb2notices_items', null, 'id', '*', $limitfrom, $limitnum);

            return $records;

        }




        /**
         *
         * Method to get sindle record.
         *
         */
        public static function get_record( $itemid = 0 )
        {
            global $DB;

            $record = $DB->get_record( 'local_mb2notices_items', array( 'id' => $itemid ), '*', MUST_EXIST );

            return $record;

        }




        /**
         *
         * Method to update the prev or next record
         *
         */
        public static function get_record_near($id, $type = 'prev')
        {

            $items = self::get_list_records();
            $newitems = self::get_sortorder_items();
            $nearitem = 0;

            $sortorder = $items[$id]->sortorder;

            // Get preview item
            if ($type === 'prev' && isset($newitems[$sortorder-1]))
            {
                $nearitem = $newitems[$sortorder-1];
            }

            // Get next item
            if ($type === 'next' && isset($newitems[$sortorder+1]))
            {
                $nearitem = $newitems[$sortorder+1];
            }

            return $nearitem;


        }




        /**
         *
         * Method to update the prev or next record
         *
         */
        public static function get_sortorder_items()
        {

            $newitems = array();
            $items = self::get_list_records();

            // Create new array of items
            // Set 'sortorder' as a key of array's values
            foreach ($items as $item)
            {
                $newitems[$item->sortorder] = $item->id;
            }

            // Sort new array by sortorder
            ksort($newitems);

            return $newitems;

        }





        /**
         *
         * Method to add new record.
         *
         */
        public static function add_record($data)
        {
            global $DB;

            $items = self::get_list_records();

            $data->id = $DB->insert_record('local_mb2notices_items', array('sortorder' => count($items) + 1, 'attribs' => '' ));

            return self::update_record_data($data, true);

        }




        /**
         *
         * Method to set editor options.
         *
         */
        public static function text_editor_options()
        {
            global $CFG;
            require_once($CFG->libdir.'/formslib.php');
            $options = array();

            $options['subdirs'] = false;
            $options['maxfiles'] = -1;
            $options['context'] = context_system::instance();

            return $options;

        }





        /**
         *
         * Method to set editor options.
         *
         */
        public static function file_area_options()
        {
            global $CFG;
            require_once($CFG->libdir.'/formslib.php');
            $options = array();

            $options['subdirs'] = false;
            $options['maxfiles'] = 1;
            $options['context'] = context_system::instance();

            return $options;

        }




        /**
         *
         * Method to update the record in the database.
         *
         */
        public static function update_record_data( $data, $editpage = false )
        {
            global $DB;
            $context = context_system::instance();

            if ( $editpage )
            {
                // Update editor content
                file_postupdate_standard_editor($data, 'content', self::text_editor_options(), $context, 'local_mb2notices', 'content', $data->id);

                if ( isset( $data->attachments ) )
                {
                    // Update file area
                    file_save_draft_area_files($data->attachments, $context->id, 'local_mb2notices', 'attachment', $data->id, self::file_area_options());
                }

                // Convert php arrays to json array
                $data->attribs = json_encode( $data->attribs );
                $data->language = json_encode( $data->language );
            }

            // Update existing item
            $DB->update_record('local_mb2notices_items', $data);

        }




        /**
         *
         * Method to check if user can delete item.
         *
         */
        public static function can_delete()
        {
            return has_capability('local/mb2notices:manageitems', context_system::instance());
        }




        /**
         *
         * Method to delete item.
         *
         */
        public static function delete($itemid)
        {
            global $DB;

            if (!self::can_delete())
            {
                return;
            }

            $DB->delete_records('local_mb2notices_items', array('id' => $itemid));

            self::update_sortorder($itemid);

        }




        /**
         *
         * Method to update sortorder after delet item.
         *
         */
        public static function update_sortorder($itemid = 0)
        {
            $items = self::get_list_records();
            $sortorder_items = self::get_sortorder_items();
            $sortorder_items = array_diff($sortorder_items, array($itemid));
            $i = 0;

            foreach ( $sortorder_items as $item )
            {
                $i++;
                $callbacksorted = $items[$item];
                $callbacksorted->sortorder = $i;
                self::update_record_data($callbacksorted);
            }

        }





        /**
         *
         * Method to change item status.
         *
         */
        public static function switch_status($itemid = 0)
        {

            $items = self::get_list_records();
            $item = $items[$itemid];
            $item->enable = !$item->enable;
            self::update_record_data( $item );

        }







        /**
         *
         * Method to get form data.
         *
         */
        public static function get_form_data ($form, $itemid)
        {
            global $CFG, $USER;
            require_once($CFG->libdir . '/formslib.php');
            $data = new stdClass();
            $context = context_system::instance();

            if ( empty( $itemid ) )
            {
                $data->id = null;
                $data->contentformat = FORMAT_HTML;
                $data->timecreated = null;
                $data->createdby = null;
                $data->attribs = array();

                // Set default values for attribs. This is require because Moodle form.
                // Moodle form always returns default value instead of value from databse.
                // This problem appears in INT form field type.
                $data->attribs['showtitle'] = 999;
                $data->attribs['canclose'] = 999;
                $data->attribs['noticetype'] = 999;
                $data->attribs['position'] = 999;
            }
            else
            {
                $data = self::get_record($itemid);
                $data->contentformat = FORMAT_HTML;

                // Make an arrays from attributes and languages
                // and fill in notice form
                $data->attribs = json_decode( $data->attribs, true );
                $data->language = json_decode( $data->language, true );
            }

            // Set date created and modified
            $data->timecreated = $data->timecreated ? $data->timecreated : time();
            $data->timemodified = $data->timecreated < time() ? time() : 0;

            // Set create and modifier
            $data->createdby = $data->createdby ? $data->createdby : $USER->id;
            $data->modifiedby = $data->timecreated == time() ? 0 : $USER->id;

            // Prepare editor content
            file_prepare_standard_editor($data, 'content', self::text_editor_options(), $context, 'local_mb2notices', 'content', $data->id);

            if ( isset( $data->attachments ) )
            {
                // Prepare file area
                $draftitemid = file_get_submitted_draft_itemid('attachments');
               	file_prepare_draft_area($draftitemid, $context->id, 'local_mb2notices', 'attachment', $data->id, self::file_area_options());
              	$data->attachments = $draftitemid;
            }

            $form->set_data($data);

            return $form->get_data();

        }






        /**
         *
         * Method to move up item.
         *
         */
        public static function move_up ($itemid = 0)
        {

            $items = self::get_list_records();
            $previtem = self::get_record_near($itemid, 'prev');

            if ($previtem)
            {
                // Move down prev item
                $itemprev = $items[$previtem];
                $itemprev->sortorder = $itemprev->sortorder + 1;
                self::update_record_data($itemprev);

                // Move up current item
                $currentitem = $items[$itemid];
                $currentitem->sortorder = $currentitem->sortorder - 1;
                self::update_record_data($currentitem);
            }

        }






        /**
         *
         * Method to move down item.
         *
         */
        public static function move_down ($itemid = 0)
        {

            $items = self::get_list_records();
            $nextitem = self::get_record_near($itemid, 'next');

            if ($nextitem)
            {
                // Move up next item
                $itemnext = $items[$nextitem];
                $itemnext->sortorder = $itemnext->sortorder - 1;
                self::update_record_data($itemnext);

                // Move down current item
                $currentitem = $items[$itemid];
                $currentitem->sortorder = $currentitem->sortorder + 1;
                self::update_record_data($currentitem);
            }

        }




        /**
         *
         * Method to validate notice timestart and timeend.
         *
         */
        public static function notice_validate_dates( $data )
        {

            if ( $data['timeend'] > 0 && $data['timeend'] <= $data['timestart'] )
            {
                return 'enddatebeforestartdate';
            }

            return false;
        }



    }
}
