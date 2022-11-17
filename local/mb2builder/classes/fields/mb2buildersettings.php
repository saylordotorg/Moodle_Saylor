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

if (!class_exists('admin_setting_configmb2buildersettings') && class_exists('admin_setting'))
{

	class admin_setting_configmb2buildersettings extends admin_setting
	{



		public $paramtype;
		public $size;




		public function __construct($name, $visiblename, $description, $defaultsetting, $paramtype=PARAM_RAW, $size=null)
		{
			$this->paramtype = $paramtype;
			if (!is_null($size))
			{
				$this->size  = $size;
			}
			else
			{
				$this->size  = ($paramtype === PARAM_INT) ? 5 : 30;
			}
			parent::__construct($name, $visiblename, $description, $defaultsetting);
		}






		public function get_force_ltr()
		{
			$forceltr = parent::get_force_ltr();
			if ($forceltr === null)
			{
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
			if ($this->paramtype === PARAM_INT and $data === '')
			{
			// do not complain if '' used instead of 0
				$data = 0;
			}
			// $data is a string
			$validated = $this->validate($data);
			if ($validated !== true)
			{
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



		public function output_html($data, $query = '')
		{

			global $OUTPUT, $PAGE, $CFG, $USER;

			$section_config = unserialize(LOCAL_MB2BUILDER_SETTINGS_SECTION);
			$row_config = unserialize(LOCAL_MB2BUILDER_SETTINGS_ROW);
			$col_config = unserialize(LOCAL_MB2BUILDER_SETTINGS_COL);

			$output = '';
			//$output .= '</div></div>';
			$output .= '<input type="hidden" name="' . $this->get_full_name() . '" id="' . $this->get_id() . '" value="' . $data . '">';
			$output .= '<div class="mb2-pb-settings mb2-pb-settings-section" data-baseurl="' . $CFG->wwwroot . '" data-sesskey="' . $USER->sesskey . '">';
			$output .= '<div class="hidden">';
			$output .= $this->local_mb2builder_layout_builder_languege_strings();
			$output .= '<textarea id="builderfptextimport"></textarea>';
			$output .= '<textarea id="builderfootertextimport"></textarea>';
			$output .= '<div class="template-settings-section">';
			$output .= $this->local_mb2builder_settings_get_settings_template($section_config, 'settings-section');
			$output .= $this->local_mb2builder_settings_get_settings_template($row_config, 'settings-row');
			$output .= $this->local_mb2builder_settings_get_settings_template($col_config, 'settings-col');
			$output .= $this->local_mb2builder_settings_get_settings_template_elements();
			$output .= '</div>';
			$output .= '</div>';
			$output .= $this->local_mb2builder_settings_get_modal('import-export-fp');
			//$output .= $this->local_mb2builder_settings_get_modal('import-export-footer');
			$output .= $this->local_mb2builder_settings_get_modal('settings-section');
			$output .= $this->local_mb2builder_settings_get_modal('settings-row');
			$output .= $this->local_mb2builder_settings_get_modal('settings-col');
			$output .= $this->local_mb2builder_settings_get_modal('settings-element');
			$output .= $this->local_mb2builder_settings_get_modal('settings-subelement');
			$output .= $this->local_mb2builder_settings_get_modal('row-layout');
			$output .= $this->local_mb2builder_settings_get_modal('elements');
			$output .= $this->local_mb2builder_settings_get_modal('images');
			$output .= $this->local_mb2builder_settings_get_modal('file-manager');
			$output .= $this->local_mb2builder_settings_get_modal('font-icons');
			$output .= '</div>';
			//$output .= '<div><div>';

			return $output;

		}




		public function local_mb2builder_settings_export_page ($id = 1)
		{

			$fs = get_file_storage();
			$db_files = local_mb2builder_get_db_pages();
			$context = \context_system::instance();
			$plugin_settings = get_config('local_mb2builder');
			$page_data = '';
			$filename = 'frontpage.json';

			if ($id == 1 && isset($plugin_settings->builderfptext))
			{
				$page_data = $plugin_settings->builderfptext;
			}
			elseif ($id == 2 && isset($plugin_settings->builderfootertext))
			{
				$page_data = $plugin_settings->builderfootertext;
				$filename = 'footer.json';
			}


			// Create new file with page layout
			$opt = array(
				'contextid' => $context->id,
				'component' => 'local_mb2builder',
				'filearea' => 'pagesexport',
				'itemid' => 0,
				'filepath' => '/',
				'filename' => $filename
			);

			// Get old file and remove it
			$file = $fs->get_file($opt['contextid'], $opt['component'], $opt['filearea'],$opt['itemid'], $opt['filepath'], $opt['filename']);
			if (!is_bool($file))
			{
				$file->delete();
			}

			// Create new file with builder content
			$fs->create_file_from_string($opt, $page_data);
			$file = $fs->get_file($opt['contextid'], $opt['component'], $opt['filearea'],$opt['itemid'], $opt['filepath'], $opt['filename']);

			return moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), NULL, $file->get_filepath(), $file->get_filename());

		}




		public function local_mb2builder_settings_get_input_items ($key, $attr)
		{
			 return call_user_func(array( 'LocalMb2builder' . ucfirst( $attr['type'] ), 'local_mb2builder_get_input'), $key, $attr );
		}




		public function local_mb2builder_settings_get_settings_template_elements()
		{

			$output = '';

			foreach (local_mb2builder_get_elements() as $element)
			{

				$cons_fields_name = 'LOCAL_MB2BUILDER_SETTINGS_' . strtoupper($element);

				$config_fields = unserialize(constant($cons_fields_name));
				$type = 'settings-element-' . $element;

				$output .= $this->local_mb2builder_settings_get_settings_template($config_fields, $type);

				if (isset($config_fields['subelement']))
				{
					$type = 'settings-subelement-' . $element;
					$output .= $this->local_mb2builder_settings_get_settings_template($config_fields['subelement'], $type);
				}


			}

			return $output;

		}






		public function local_mb2builder_settings_get_settings_template ($config_fields, $type)
		{

			$output = '';

			$output .= '<div id="tab-' . $type . '" class="theme-tabs tabs top">';
			$output .= '<ul class="nav nav-tabs">';

			$config_tabs = $config_fields['tabs'];

			foreach ($config_tabs as $tab=>$tname)
			{
				$isactive = $tab === 'general' ? ' active': '';
				$output .= '<li class="nav-item' . $isactive . '"><a class="nav-link' . $isactive . '" data-toggle="tab" href="#' . $type . '-' . $tab . '">' . $tname . '</a></li>';
			}

			$output .= '</ul>';


			$output .= '<div class="tab-content">';

			foreach ($config_tabs as $tab=>$tname)
			{

				$isactive = $tab === 'general' ? ' in active': '';
				$output .= '<div id="' . $type . '-' . $tab . '" class="tab-pane fade' . $isactive . '">';

				foreach ($config_fields['attr'] as $fname=>$attr)
				{
					if ($attr['section'] === $tab)
					{
						$output .= $this->local_mb2builder_settings_get_input_items($fname, $attr);
					}
				}

				$output .= '</div>';
			}

			$output .= '</div>';
			$output .= '</div>';

			return $output;

		}



		public function local_mb2builder_settings_get_modal ($type)
		{

			global $CFG;

			$output = '';

			$modal_cls = '';

			if ($type === 'file-manager')
			{
				$modal_cls = ' modal-lg';
			}

			if ($type === 'images' || $type === 'font-icons' || $type === 'elements')
			{
				$modal_cls = ' modal-md';
			}

			$images_data = $type === 'images' ? ' data-images_baseurl="' . local_mb2builder_get_images_base_url() . '"' : '';

			$modal_title = 'Modal';

			if ($type === 'row-layout')
			{
				$modal_title = get_string('columns', 'local_mb2builder');
			}
			elseif ($type === 'font-icons')
			{
				$modal_title = get_string('icons', 'local_mb2builder');
			}
			elseif ($type ===  'images')
			{
				$modal_title = get_string('selectimage', 'local_mb2builder');
			}
			elseif ($type === 'file-manager')
			{
				$modal_title = get_string('uploadimages', 'local_mb2builder');
			}
			elseif ($type === 'elements')
			{
				$modal_title = get_string('addelement', 'local_mb2builder');
			}
			elseif ($type === 'import-export-fp')
			{
				$modal_title = get_string('importexport', 'local_mb2builder');
			}

			$output .= '<div id="mb2-pb-modal-' . $type . '" class="modal fade" role="dialog"' . $images_data . '>';
			$output .= '<div class="modal-dialog' . $modal_cls . '" role="document">';
			$output .= '<div class="modal-content">';
			$output .= '<div class="modal-header">';
			$output .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
			$output .= '<h4 class="modal-title">' . $modal_title . '</h4>';
			$output .= '</div>';
			$output .= '<div class="modal-body">';
			$output .= $type === 'row-layout' ? $this->local_mb2builder_row_layout() : '';
			$output .= $type === 'elements' ? $this->local_mb2builder_elements_layout() : '';
			$output .= $type === 'images' ? $this->local_mb2builder_images() : '';
			$output .= $type === 'font-icons' ? $this->local_mb2builder_font_icons() : '';
			$output .= $type === 'import-export-fp' ? $this->local_mb2builder_import_export(1) : '';
			//$output .= $type === 'import-export-footer' ? $this->local_mb2builder_import_export(2) : '';
			$output .= $type === 'file-manager' ? $this->local_mb2builder_file_manager_iframe() : '';
			$output .= $type === 'file-manager' ? '<div class="mb2-pb-overlay"></div>' : '';
			$output .= '</div>';

			if ($type !== 'row-layout' && $type !== 'elements')
			{
				$output .= '<div class="modal-footer">';

				$save_btn = 1;
				$dismiss = ' data-dismiss="modal"';
				$btn_id = 'save-' . $type;
				$cancel_text = get_string('cancel');

				if ($type === 'file-manager')
				{
					$dismiss = '';
					$btn_id = 'applay-' . $type;
					$cancel_text = get_string('close','local_mb2builder');
				}

				if ($type === 'images')
				{
					$save_btn = 0;
					$output .= '<button class="mb2-pb-upload-images btn btn-success btn-sm" data-toggle="modal" data-target="#mb2-pb-modal-file-manager">' .
					get_string('uploadimages','local_mb2builder') . '</button>';
				}

				if ($type === 'font-icons')
				{
					$save_btn = 0;
				}

				if ($type === 'import-export-fp' || $type === 'import-export-footer')
				{
					$save_btn = 0;
				}

				$output .= $save_btn ? '<button type="button" id="' . $btn_id . '" class="btn btn-sm btn-success"' . $dismiss . '>' . get_string('save', 'admin') . '</button>' : '';
				$output .= '<button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">' . $cancel_text . '</button>';
				$output .= '</div>';
			}


			$output .= '</div>';
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}



		public function local_mb2builder_layout_builder_languege_strings()
		{

			$output = '';

			$output .= '<span id="mb2-pb-lang"';
			$output .= ' data-addrow="' . get_string('addrow','local_mb2builder') . '"';
			$output .= ' data-remove="' . get_string('remove','local_mb2builder') . '"';
			$output .= ' data-settings="' . get_string('settings','local_mb2builder') . '"';
			$output .= ' data-section="' . get_string('section','local_mb2builder') . '"';
			$output .= ' data-row="' . get_string('row','local_mb2builder') . '"';
			$output .= ' data-duplicate="' . get_string('duplicate','local_mb2builder') . '"';
			$output .= ' data-col="' . get_string('col','local_mb2builder') . '"';
			$output .= ' data-columns="' . get_string('columns','local_mb2builder') . '"';
			$output .= ' data-addelement="' . get_string('addelement','local_mb2builder') . '"';
			$output .= ' data-element="' . get_string('element','local_mb2builder') . '"';
			$output .= ' data-copy="' . get_string('copy','local_mb2builder') . '"';
			$output .= ' data-item="' . get_string('item','local_mb2builder') . '"';
			$output .= ' data-importtextempty="' . get_string('importtextempty','local_mb2builder') . '"';
			$output .= ' data-importtextnotvalidjson="' . get_string('importtextnotvalidjson','local_mb2builder') . '"';
			$output .= ' data-importsuccess="' . get_string('importsuccess','local_mb2builder') . '"';
			$output .= '></span>';

			return $output;


		}



		public function local_mb2builder_row_layout()
		{

			$output = '';

			$layout_arr = array(
				'12',
				'6,6',
				'4,4,4',
				'3,3,3,3',
				'3,6,3',
				'9,3',
				'8,4',
				'7,5'
			);

			$output .= '<div class="mb2-pb-row-variants">';

			foreach ($layout_arr as $l)
			{
				$output .= '<a href="#" class="mb2-pb-row-variant row-' . str_replace(',', '', $l) . '" data-row_variant="' . $l . '" title="' .
				str_replace(',', '-', $l) . '" data-dismiss="modal">';

				$el_arr = explode(',', $l);
				foreach ($el_arr as $e)
				{
					$output .= '<span class="rowel-' . $e . '">' . $e . '</span>';
				}

				$output .= '</a>';
			}

			$output .= '</div>';

			return $output;

		}






		public function local_mb2builder_elements_layout()
		{

			$output = '';
			$elements = local_mb2builder_get_elements();

			$output .= '<div class="mb2-pb-elements">';

			foreach ($elements as $element)
			{

				$cons_fields_name = 'LOCAL_MB2BUILDER_SETTINGS_' . strtoupper($element);
				$config_fields = unserialize(constant($cons_fields_name));
				$subel = isset($config_fields['subelement']) ? 1 : 0;
				$default = $config_fields['attr']['admin_label']['default'];

				$output .= '<a href="#" class="mb2-pb-modal-el ' . $config_fields['id'] . '" data-id="' . $config_fields['id'] . '" data-label="' .
				$default . '" data-subelement="' . $subel . '" data-subelement_name="' . $config_fields['subid'] . '" data-dismiss="modal">';
				$output .= '<i class="' . $config_fields['icon'] . '"></i>';
				$output .= '<span>' . $config_fields['title'] . '</span>';
				$output .= '</a>';

			}

			$output .= '</div>';

			return $output;

		}





		public function local_mb2builder_images()
		{

			$output = '';

			$output .= '<div class="mb2-pb-images">';
			$output .= '</div>';

			return $output;

		}




		public function local_mb2builder_file_manager_iframe()
		{

			global $CFG;
			$output = '';

			$output .= '<iframe id="mb2-pb-images-iframe"';
			$output .= ' src="' . new moodle_url($CFG->wwwroot . '/admin/settings.php?section=local_mb2builder_images',array()) . '">';
			$output .= '</iframe>';

			return $output;

		}




		public function local_mb2builder_font_icons()
		{

			$output = '';
			$icons_lineicons = array();
			$path_fa = LOCAL_MB2BUILDER_PATH_THEME_ASSETS . '/font-awesome/css/font-awesome.css';
			$path_glyphicons = LOCAL_MB2BUILDER_PATH_THEME_ASSETS . '/bootstrap/css/glyphicons.css';
			$path_7stroke = LOCAL_MB2BUILDER_PATH_THEME_ASSETS . '/pe-icon-7-stroke/css/pe-icon-7-stroke.css';
			$path_lineicons = LOCAL_MB2BUILDER_PATH_THEME_ASSETS . '/LineIcons/LineIcons.css';


			$icons_fa = local_mb2builder_get_icons_arr($path_fa);
			$icons_glyphicons = local_mb2builder_get_icons_arr($path_glyphicons, 'glyphicon-');
			$icons_7stroke = local_mb2builder_get_icons_arr($path_7stroke, '');

			if (file_exists( $path_lineicons ))
			{
				$icons_lineicons = local_mb2builder_get_icons_arr($path_lineicons, '');
			}
			
			$output .= '<div id="tab-font-icons" class="theme-tabs tabs top">';
			$output .= '<ul class="nav nav-tabs">';
			$output .= file_exists($path_fa) ? '<li class="nav-item active"><a class="nav-link active show" data-toggle="tab" href="#tab-font-icons-fa">Font Awesome</a></li>' : '';
			$output .= file_exists($path_glyphicons) ? '<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-font-icons-glyph">Glyphicons</a></li>' : '';
			$output .= file_exists($path_7stroke) ? '<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-font-icons-7stroke">7 Stroke</a></li>' : '';
			$output .= file_exists($path_lineicons) ? '<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-font-icons-lineicons">Line Icons</a></li>' : '';


			$output .= '</ul>';

			$output .= '<div class="tab-content">';

			if (file_exists( $path_fa ) && count($icons_fa))
			{
				$output .= '<div id="tab-font-icons-fa" class="tab-pane fade in active">';
				//$output .= '<input type="search" class="mb2-pb-search-icon" />';

				foreach ($icons_fa as $k=>$v)
				{
					$output .= '<a href="#" class="mb2-pb-choose-icon" data-iconname="fa ' . $k . '" title="' . $k . '" data-dismiss="modal"><i class="fa ' . $k . '"></i></a>';
				}

				$output .= '</div>';
			}


			if (file_exists( $path_glyphicons ) && count($icons_glyphicons))
			{
				$output .= '<div id="tab-font-icons-glyph" class="tab-pane fade">';

				foreach ($icons_glyphicons as $k=>$v)
				{
					$output .= '<a href="#" class="mb2-pb-choose-icon" data-iconname="glyphicon ' . $k . '" title="' . $k . '" data-dismiss="modal"><i class="glyphicon ' . $k . '"></i></a>';
				}

				$output .= '</div>';
			}


			if (file_exists( $path_7stroke ) && count($icons_7stroke))
			{
				$output .= '<div id="tab-font-icons-7stroke" class="tab-pane fade">';

				foreach ($icons_7stroke as $k=>$v)
				{
					$output .= '<a href="#" class="mb2-pb-choose-icon" data-iconname="' . $k . '" title="' . $k . '" data-dismiss="modal"><i class=" ' . $k . '"></i></a>';
				}

				$output .= '</div>';
			}


			if (file_exists( $path_lineicons ) && count($icons_lineicons))
			{
				$output .= '<div id="tab-font-icons-lineicons" class="tab-pane fade">';

				foreach ($icons_lineicons as $k=>$v)
				{
					$output .= '<a href="#" class="mb2-pb-choose-icon" data-iconname="' . $k . '" title="' . $k . '" data-dismiss="modal"><i class=" ' . $k . '"></i></a>';
				}

				$output .= '</div>';
			}

			$output .= '</div>';
			$output .= '</div>';


			return $output;

		}





		public function local_mb2builder_import_export($id)
		{

			$output = '';

			$output .= '<div id="tab-import-export' . $id . '" class="theme-tabs tabs top">';
			$output .= '<ul class="nav nav-tabs">';
			$output .= '<li class="nav-item active"><a class="nav-link active show" data-toggle="tab" href="#tab-import' . $id . '">' . get_string('import','local_mb2builder') . '</a></li>';
			$output .= '<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-export' . $id . '">' . get_string('export','local_mb2builder') . '</a></li>';
			$output .= '</ul>';

			$output .= '<div class="tab-content">';

			$output .= '<div id="tab-import' . $id . '" class="tab-pane fade in active">';
			$output .= '<div class="form-group  mb2-pb-form-group">';
			$output .= '<label>' . get_string('importlabel','local_mb2builder') . '</label>';
			$output .= '<textarea id="mb2-pb-import-json' . $id . '" class="form-control mb2-pb-mb2color mb2-pb-input"></textarea>';
			$output .= '<a href="#" id="mb2-pb-import-btn' . $id . '">' . get_string('import','local_mb2builder') . '</a>';
			$output .= '</div>';
			$output .= '</div>';

			$output .= '<div id="tab-export' . $id . '" class="tab-pane fade">';
			$output .= '<a href="' . $this->local_mb2builder_settings_export_page($id) . '" class="btn btn-primary isicon" download><span class="btn-icon"><i class="fa fa-download"></i></span><span>' .
			get_string('downloadcontent' . $id,'local_mb2builder') . '</span></a>';
			$output .= '</div>';

			$output .= '</div>';
			$output .= '</div>';

			return $output;

		}


	}


}
