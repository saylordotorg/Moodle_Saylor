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
 *
 * @package    local_mb2slides
 * @copyright  2019 - 2020 Mariusz Boloz (mb2themes.com)
 * @license   Commercial https://themeforest.net/licenses
 *
 */

defined('MOODLE_INTERNAL') || die();

if (!class_exists('admin_setting_configmb2color') && class_exists('admin_setting'))
{

	class admin_setting_configmb2color extends admin_setting
	{



		public $paramtype;
		public $size;




		public function __construct($name, $visiblename, $description, $defaultsetting, $paramtype=PARAM_RAW, $size=null)
		{
			$this->paramtype = $paramtype;
			if (!is_null($size))
			{
				$this->size  = $size;
			} else {
				$this->size  = ($paramtype === PARAM_INT) ? 5 : 30;
			}
			parent::__construct($name, $visiblename, $description, $defaultsetting);
		}






		public function get_force_ltr()
		{
			$forceltr = parent::get_force_ltr();
			if ($forceltr === null) {
				return !is_rtl_compatible($this->paramtype);
			}
			return $forceltr;
		}






		public function get_setting()
		{
			return $this->config_read($this->name);
		}




		public function write_setting($data)
		{
			if ($this->paramtype === PARAM_INT and $data === '') {
			// do not complain if '' used instead of 0
				$data = 0;
			}
			// $data is a string
			$validated = $this->validate($data);
			if ($validated !== true) {
				return $validated;
			}
			return ($this->config_write($this->name, $data) ? '' : get_string('errorsetting', 'admin'));
		}





		public function validate($data)
		{
			// allow paramtype to be a custom regex if it is the form of /pattern/
			if (preg_match('#^/.*/$#', $this->paramtype)) {
				if (preg_match($this->paramtype, $data)) {
					return true;
				} else {
					return get_string('validateerror', 'admin');
				}

			} else if ($this->paramtype === PARAM_RAW) {
				return true;

			} else {
				$cleaned = clean_param($data, $this->paramtype);
				if ("$data" === "$cleaned") { // implicit conversion to string is needed to do exact comparison
					return true;
				} else {
					return get_string('validateerror', 'admin');
				}
			}
		}






		public function output_html($data, $query='')
		{


			global $OUTPUT, $PAGE;

			//$PAGE->requires->js('/theme/mb2mcl2/assets/spectrum/spectrum.js');
			//$PAGE->requires->js('/theme/mb2mcl2/assets/spectrum/spectrum_custom.js');


			$el = '<input class="mb2color" type="text" name="' . $this->get_full_name() . '" id="' . $this->get_id() . '" value="' . $data . '" />';

			return format_admin_setting($this, $this->visiblename, $el, $this->description, true, '', $this->get_defaultsetting(), $query);


		}


	}


}
