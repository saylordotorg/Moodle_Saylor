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
 * @package   theme_mb2cg2
 * @copyright 2017 Mariusz Boloz (http://marbol2.com)
 * @license   Commercial https://themeforest.net/licenses
 *
 */

defined('MOODLE_INTERNAL') || die();

if (!class_exists('admin_setting_configmb2builder') && class_exists('admin_setting'))
{

	class admin_setting_configmb2builder extends admin_setting
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
				if ("$data" === "$cleaned")
				{ // implicit conversion to string is needed to do exact comparison
					return true;
				}
				else
				{
					return get_string('validateerror', 'admin');
				}
			}
		}






		public function output_html( $data, $query = '' )
		{

			global $OUTPUT, $PAGE;

			$builder_id = str_replace('id_s_local_mb2builder_', '', $this->get_id());
			$output = '';
			$output .= '<div class="mb2-pb-container mb2-pb-builder-' . $builder_id . '" style="display:none;" data-pageid="' . $builder_id . '">';
			$output .= $this->local_mb2builder_layout_builder($this->get_id());
			$output .= '<input type="hidden" name="' . $this->get_full_name() . '" id="' . $this->get_id() . '" value="' . $data . '">';
			$output .= '</div>';

			return $output;

		}




		public function local_mb2builder_layout_builder ($id)
		{


			$output = '';
			$pageid = str_replace('id_s_local_mb2builder_', '', $id);
			$page_ID = str_replace('id_s_local_mb2builder_builder', '', $id);
			$config = get_config('local_mb2builder');
			$layout_data = '';

			if ($page_ID === 'fp' && isset($config->builderfptext))
			{
				$layout_data = json_decode($config->builderfptext, true);
			}
			elseif ($page_ID === 'footer' && isset($config->builderfootertext))
			{
				$layout_data = json_decode($config->builderfootertext, true);
			}

			// $toggle_title = get_string('builderfp', 'local_mb2builder');
			// if ($page_ID === 'footer')
			// {
			// 	$toggle_title = get_string('builderfooter', 'local_mb2builder');
			// }
			//
			// $output .= '<div class="mb2-pb-builder-toggle">';
			// $output .= '<strong>' . $toggle_title . '</strong>';
			// $output .= '</div>';

			$output .= '<div class="mb2-pb-builder-toggle-content">';
			$output .= $this->local_mb2builder_layout_builder_toolbar($page_ID);


			$output .= '<div class="mb2-pb-sortable-sections clearfix">';


			if (!$layout_data)
			{
				$layout_data = array();
			}


			foreach ($layout_data as $el)
			{
				if ($el['settings']['pageid'] === $pageid)
				{
					foreach ($el['attr'] as $section)
					{
						$output .= $this->local_mb2builder_layout_builder_section($section);
					}
				}
			}


			$output .= '</div>';
			$output .= '</div>';


			return $output;


		}

		public function local_mb2builder_layout_builder_access_icons($access)
		{
			$output = '';

			if ($access == 1)
			{
				$output = '<i class="fa fa-lock"></i>';
			}
			elseif ($access == 2)
			{
				$output = '<i class="fa fa-unlock"></i>';
			}

			return $output;

		}


		public function local_mb2builder_layout_builder_section($section)
		{

			$output = '';

			$section_title = isset($section['settings']['admin_label']) ? $section['settings']['admin_label'] : get_string('section','local_mb2builder');
			$section_lang = isset($section['settings']['sectionlang']) && $section['settings']['sectionlang'] ? '(' . $section['settings']['sectionlang'] . ')' : '';
			$section_access = isset($section['settings']['sectionaccess']) ? $section['settings']['sectionaccess'] : 0;
			$section_hidden_cls = (isset($section['settings']['sectionhidden']) && $section['settings']['sectionhidden']) ? ' hiddenel' : '';

			$output .= '<div class="mb2-pb-section' . $section_hidden_cls . '" ' . $this->local_mb2builder_layout_builder_get_settings($section['settings']) . '>';
			$output .= '<div class="mb2-pb-section-header clearfix">';
			$output .= '<span class="mb2-pb-title">' . $section_title . '</span>';
			$output .= '<span class="mb2-pb-language">' . $section_lang . '</span>';
			$output .= '<span class="mb2-pb-access">' . $this->local_mb2builder_layout_builder_access_icons($section_access) . '</span>';
			$output .= '<div class="mb2-pb-actions">';
			$output .= '<a href="#" class="settings-section" title="' .
			get_string('settings','local_mb2builder') . '" data-toggle="modal" data-target="#mb2-pb-modal-settings-section">';
			$output .= '<i class="fa fa-cog"></i> ';
			$output .= get_string('settings','local_mb2builder');
			$output .= '</a>';
			$output .= '<a href="#" class="duplicate-section" title="' . get_string('duplicate','local_mb2builder') . '">';
			$output .= '<i class="fa fa-clone"></i> ';
			$output .= get_string('duplicate','local_mb2builder');
			$output .= '</a>';
			$output .= '<a href="#" class="remove-section" title="' . get_string('remove','local_mb2builder') . '">';
			$output .= '<i class="fa fa-trash"></i> ';
			$output .= get_string('remove','local_mb2builder');
			$output .= '</a>';
			$output .= '</div>';
			$output .= '</div>';


			$output .= '<div class="mb2-pb-sortable-rows">';

			foreach ($section['attr'] as $row)
			{
				$output .= $this->local_mb2builder_layout_builder_row($row);
			}

			$output .= '</div>';

			$output .= '<div class="mb2-pb-addrow">';
			$output .= '<a href="#" class="btn btn-sm btn-success mb2-pb-row-toggle" data-toggle="modal" data-target="#mb2-pb-modal-row-layout">' .
			get_string('addrow','local_mb2builder') . '</a>';
			$output .= '</div>';

			$output .= '</div>';

			return $output;

		}



		public function local_mb2builder_layout_builder_row($row)
		{

			$output = '';

			$row_title = isset($row['settings']['admin_label']) ? $row['settings']['admin_label'] : get_string('row','local_mb2builder');
			$row_lang = isset($row['settings']['rowlang']) && $row['settings']['rowlang'] ? '(' . $row['settings']['rowlang'] . ')' : '';
			$row_access = isset($row['settings']['rowaccess']) ? $row['settings']['rowaccess'] : 0;
			$row_hidden_cls = (isset($row['settings']['rowhidden']) && $row['settings']['rowhidden']) ? ' hiddenel' : '';

			$output .= '<div class="mb2-pb-row' . $row_hidden_cls . '" ' . $this->local_mb2builder_layout_builder_get_settings($row['settings']) . '>';
			$output .= '<div class="mb2-pb-row-header clearfix">';
			$output .= '<span class="mb2-pb-title">' . $row_title . '</span>';
			$output .= '<span class="mb2-pb-language">' . $row_lang . '</span>';
			$output .= '<span class="mb2-pb-access">' . $this->local_mb2builder_layout_builder_access_icons($row_access) . '</span>';
			$output .= '<div class="mb2-pb-actions">';
			$output .= '<a href="#" class="settings-row" title="' .
			get_string('settings','local_mb2builder') . '" data-toggle="modal" data-target="#mb2-pb-modal-settings-row">';
			$output .= '<i class="fa fa-cog"></i> ';
			$output .= get_string('settings','local_mb2builder');
			$output .= '</a>';
			$output .= '<a href="#" class="layout-row" title="' .
			get_string('columns','local_mb2builder') . '" data-toggle="modal" data-target="#mb2-pb-modal-row-layout">';
			$output .= '<i class="fa fa-columns"></i> ';
			$output .= get_string('columns','local_mb2builder');
			$output .= '</a>';
			$output .= '<a href="#" class="duplicate-row" title="' . get_string('duplicate','local_mb2builder') . '">';
			$output .= '<i class="fa fa-clone"></i> ';
			$output .= get_string('duplicate','local_mb2builder');
			$output .= '</a>';
			$output .= '<a href="#" class="remove-row" title="' . get_string('remove','local_mb2builder') . '">';
			$output .= '<i class="fa fa-trash"></i> ';
			$output .= get_string('remove','local_mb2builder');
			$output .= '</a>';
			$output .= '</div>';
			$output .= '</div>';
			$output .= '<div class="mb2-pb-sortable-cols clearfix">';

			if (isset($row['attr']))
			{
				foreach ($row['attr'] as $col)
				{
					$output .= $this->local_mb2builder_layout_builder_col($col);
				}
			}

			$output .= '</div>';
			$output .= '</div>';

			return $output;

		}




		public function local_mb2builder_layout_builder_col($col)
		{

			$output = '';
			$col_title = isset($col['settings']['admin_label']) ? $col['settings']['admin_label'] : get_string('col','local_mb2builder');


			$col_num = $col['settings']['col'];
			$output .= '<div class="mb2-pb-col col-' . $col_num . '" data-col="' . $col_num . '" ' . $this->local_mb2builder_layout_builder_get_settings($col['settings']) . '>';
			$output .= '<div class="mb2-pb-col-header clearfix">';
			$output .= '<span class="mb2-pb-title">' . $col_title . '</span>';
			$output .= '<div class="mb2-pb-actions">';
			$output .= '<a href="#" class="settings-col" title="' .
			get_string('settings','local_mb2builder') . '" data-toggle="modal" data-target="#mb2-pb-modal-settings-col">';
			$output .= '<i class="fa fa-cog"></i> ';
			$output .= get_string('settings','local_mb2builder');
			$output .= '</a>';
			$output .= '</div>';
			$output .= '</div>';
			$output .= '<div class="mb2-pb-sortable-elements clearfix">';

			if (isset($col['attr']))
			{
				foreach ($col['attr'] as $el)
				{
					$output .= $this->local_mb2builder_layout_builder_el($el);
				}
			}

			$output .= '</div>';
			$output .= '<div class="mb2-pb-col-footer">';
			$output .= '<a href="#" class="mb2-pb-add-element" title="' .
			get_string('addelement','local_mb2builder') . '" data-toggle="modal" data-target="#mb2-pb-modal-elements">&plus; ' . get_string('addelement','local_mb2builder') . '</a>';
			$output .= '</div>';
			$output .= '</div>';

			return $output;


		}




		public function local_mb2builder_layout_builder_el ($el)
		{

			$output = '';

			$col_title = isset($el['settings']['admin_label']) ? $el['settings']['admin_label'] : get_string('element','local_mb2builder');

			$output .= '<div class="mb2-pb-element" ' . $this->local_mb2builder_layout_builder_get_settings($el['settings']) . '>';
			$output .= '<div class="mb2-pb-element-header clearfix">';
			$output .= '<span class="mb2-pb-title">' . $col_title . '</span>';
			$output .= '<div class="mb2-pb-actions">';
			$output .= '<a href="#" class="settings-element" title="' .
			get_string('settings','local_mb2builder') . '" data-toggle="modal" data-target="#mb2-pb-modal-settings-element">';
			$output .= '<i class="fa fa-cog"></i> ';
			//$output .= get_string('settings','local_mb2builder');
			$output .= '</a>';
			$output .= '</a>';
			$output .= '<a href="#" class="duplicate-element" title="' . get_string('duplicate','local_mb2builder') . '">';
			$output .= '<i class="fa fa-clone"></i> ';
			//$output .= get_string('duplicate','local_mb2builder');
			$output .= '</a>';
			$output .= '<a href="#" class="remove-element" title="' . get_string('remove','local_mb2builder') . '">';
			$output .= '<i class="fa fa-trash"></i> ';
			//$output .= get_string('remove','local_mb2builder');
			$output .= '</a>';
			$output .= '</div>';
			$output .= '</div>';

			if ($el['settings']['subelement'])
			{
				$output .= '<div class="mb2-pb-sortable-subelements clearfix">';

				foreach ($el['attr'] as $subel)
				{
					$output .= $this->local_mb2builder_layout_builder_subel($subel);
				}

				$output .= '</div>';

				$output .= '<div class="mb2-pb-element-footer">';
				$output .= '<a href="#" class="mb2-pb-add-subelement" title="' . get_string('addelement','local_mb2builder') . '" data-subelement_name="">&plus; ' . get_string('addelement','local_mb2builder') . '</a>';
				$output .= '</div>';
			}


			$output .= '</div>';




			return $output;

		}




		public function local_mb2builder_layout_builder_subel ($subel)
		{

			$output = '';

			$col_title = isset($subel['settings']['admin_label']) ? $subel['settings']['admin_label'] : get_string('element','local_mb2builder');

			$output .= '<div class="mb2-pb-subelement" ' . $this->local_mb2builder_layout_builder_get_settings($subel['settings']) . '>';
			$output .= '<div class="mb2-pb-subelement-header clearfix">';
			$output .= '<span class="mb2-pb-title">' . $col_title . '</span>';
			$output .= '<div class="mb2-pb-actions">';
			$output .= '<a href="#" class="settings-subelement" title="' .
			get_string('settings','local_mb2builder') . '" data-toggle="modal" data-target="#mb2-pb-modal-settings-subelement">';
			$output .= '<i class="fa fa-cog"></i> ';
			//$output .= get_string('settings','local_mb2builder');
			$output .= '</a>';
			$output .= '</a>';
			$output .= '<a href="#" class="duplicate-subelement" title="' . get_string('duplicate','local_mb2builder') . '">';
			$output .= '<i class="fa fa-clone"></i> ';
			//$output .= get_string('duplicate','local_mb2builder');
			$output .= '</a>';
			$output .= '<a href="#" class="remove-subelement" title="' . get_string('remove','local_mb2builder') . '">';
			$output .= '<i class="fa fa-trash"></i> ';
			//$output .= get_string('remove','local_mb2builder');
			$output .= '</a>';
			$output .= '</div>';
			$output .= '</div>';
			$output .= '</div>';

			return $output;

		}





		public function local_mb2builder_layout_builder_toolbar($id)
		{

			$output = '';

			$output .= '<div class="mb2-pb-toolbar clearfix">';
			$output .= '<a href="#" class="mb2-pb-importexportbtn" data-toggle="modal" data-target="#mb2-pb-modal-import-export-' . $id . '">' . get_string('importexport','local_mb2builder') . '</a>';
			$output .= '<a href="#" class="btn btn-success btn-sm mb2-pb-addsection">' . get_string('addsection','local_mb2builder') . '</a>';
			$output .= '</div>';

			return $output;


		}



		public static function local_mb2builder_layout_builder_get_settings($item, $attribs = array())
		{

			$output = '';

			foreach ($item as $k=>$v)
			{
				$output .= (isset($attribs['exclude']) && in_array($k,$attribs['exclude'])) ? '' : ' data-' . $k . '="' . htmlentities($v) . '"';
			}

			return $output;

		}


	}


}
