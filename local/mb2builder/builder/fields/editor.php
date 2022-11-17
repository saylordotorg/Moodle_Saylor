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
 * @package    local_mb2builder
 * @copyright  2018 - 2020 Mariusz Boloz (https://mb2themes.com/)
 * @license    Commercial https://themeforest.net/licenses
 */

defined('MOODLE_INTERNAL') || die();


class LocalMb2builderEditor
{

	static function local_mb2builder_get_input($key, $attr)
	{


		if (!isset($attr['default']))
		{
	 		$attr['default'] = '';
		}

		if (!isset($attr['desc']))
		{
	 		$attr['desc'] = '';
		}

		if (!isset($attr['showon']))
		{
			$attr['showon'] = '';
		}

		$showon = local_mb2builder_showon_field($attr['showon']);

		$output  = '<div class="form-group  mb2-pb-form-group">';
		$output .= '<label>' . $attr['title'] . '</label>';
		$output .= self::local_mb2builder_get_input_editor();
		$output .= '<textarea class="mb2-pb-editor-input mb2-pb-input mb2-pb-input-' . $key . '"' . $showon .' data-attrname="' . $key . '" rows="4">' . $attr['default'] . '</textarea>';

		if ($attr['desc'])
		{
			$output	.= '<span class="mb2-pb-from-desc">' . $attr['desc'] . '</span>';
		}

		$output .= '</div>';


		return $output;

	}






	static function local_mb2builder_get_input_editor()
	{

		$output = '';

		$output .= '<div class="mb2-pb-editor">';
		$output .= '<select title="Format" data-action="formatBlock">';
		$output .= '<option selected="selected" disabled="disabled">' . get_string('formatblock','editor') . '</option>';
		$output .= '<option value="h1">' . get_string('heading','editor') . ' 1</option>';
		$output .= '<option value="h2">' . get_string('heading','editor') . ' 2</option>';
		$output .= '<option value="h3">' . get_string('heading','editor') . ' 3</option>';
		$output .= '<option value="h4">' . get_string('heading','editor') . ' 4</option>';
		$output .= '<option value="h5">' . get_string('heading','editor') . ' 5</option>';
		$output .= '<option value="h6">' . get_string('heading','editor') . ' 6</option>';
		$output .= '<option value="p">' . get_string('paragraph','local_mb2builder') . '</option>';
		$output .= '</select>';
		$output .= '<div class="divider"></div>';
		$output .= '<button data-action="bold" title="' . get_string('bold','editor') . '"><i class="fa fa-bold"></i></button>';
		$output .= '<button data-action="' . get_string('italic','editor') . '" title="Italic"><i class="fa fa-italic"></i></button>';
		$output .= '<button data-action="underline" title="' . get_string('underline','editor') . '"><i class="fa fa-underline"></i></button>';
		$output .= '<div class="divider"></div>';
		$output .= '<button data-action="justifyLeft" title="' . get_string('justifyleft','editor') . '"><i class="fa fa-align-left"></i></button>';
		$output .= '<button data-action="justifyCenter" title="' . get_string('justifycenter','editor') . '"><i class="fa fa-align-center"></i></button>';
		$output .= '<button data-action="justifyRight" title="' . get_string('justifyright','editor') . '"><i class="fa fa-align-right"></i></button>';
		$output .= '<button data-action="justifyFull" title="' . get_string('justifyfull','editor') . '"><i class="fa fa-align-justify"></i></button>';
		$output .= '<div class="divider"></div>';
		$output .= '<button data-action="insertUnorderedList" title="' . get_string('unorderedlist','editor') . '"><i class="fa fa-list-ul"></i></button>';
		$output .= '<button data-action="insertOrderedList" title="' . get_string('orderedlist','editor') . '"><i class="fa fa-list-ol"></i></button>';
		$output .= '<div class="divider"></div>';
		$output .= '<button class="mbp-pb-editor-helper-image" title="' . get_string('insertimage','editor') . '"><i class="fa fa-image"></i></button>';
		$output .= '<div class="divider"></div>';
		$output .= '<button class="mbp-pb-editor-helper-link" title="' . get_string('createlink','editor') . '"><i class="fa fa-link"></i></button>';
		$output .= '<button data-action="unlink" title="' . get_string('createlink','editor') . '"><i class="fa fa-unlink"></i></button>';
		$output .= '<div class="divider"></div>';
		$output .= '<button data-action="undo" title="' . get_string('undo','editor') . '"><i class="fa fa-undo"></i></button>';
		$output .= '<button data-action="redo" title="' . get_string('redo','editor') . '"><i class="fa fa-repeat"></i></button>';
		$output .= '<button data-action="removeFormat" title="' . get_string('removeformat','local_mb2builder') . '"><i class="fa fa-eraser"></i></button>';
		$output .= '<button data-action="delete" title="' . get_string('delete','editor') . '"><i class="fa fa-trash"></i></button>';
		$output .= '<div class="divider"></div>';
		$output .= '<button class="mbp-pb-editor-htmlmode" title="' . get_string('htmlmode','editor') . '"><i class="fa fa-code"></i></button>';

		//htmlmode


		$output .= '<div class="mb2-pb-editor-helper">';
		$output .= self::local_mb2builder_get_input_editor_helper_image();
		$output .= self::local_mb2builder_get_input_editor_helper_link();
		$output .= '</div>';


		$output .= '</div>'; // end editor

		$output .= '<div class="mb2-pb-editor-document"></div>';


		return $output;



	}




	static function local_mb2builder_get_input_editor_helper_image()
	{

		$output = '';


		$output .= '<div class="mb2-pb-editor-helper-element element-image">';
		$output .= '<p>';
		$output .= '<img class="mb2-pb-preview-image" src="" alt=""/>';
		$output .= '<label>' . get_string('imageurl','editor') . '</label>';
		$output .= '<input type="text" name="mb2_pb_input_image_url" value="" />';
		$output .= ' <a href="#" class="mb2-pb-image-select color-success" data-toggle="modal" data-target="#mb2-pb-modal-images"><strong>' .
		get_string('selectimage','local_mb2builder') . '</strong></a>';
		$output .= '</p>';
		$output .= '<p>';
		$output .= '<label>' . get_string('width','editor') . '</label>';
		$output .= '<input type="text" name="mb2_pb_editor_image_width" value="" />';
		$output .= '</p>';
		$output .= '<p>';
		$output .= '<label>' . get_string('imgdescription','local_mb2builder') . '</label>';
		$output .= '<input type="text" name="mb2_pb_editor_image_desc" value="" />';
		$output .= '</p>';
		$output .= '<p>';
		$output .= '<label>' . get_string('alignment','editor') . '</label>';
		$output .= '<select name="mb2_pb_editor_image_align">';
		$output .= '<option value="">' . get_string('none','core') . '</option>';
		$output .= '<option value="img-align-left">' . get_string('left','editor') . '</option>';
		$output .= '<option value="img-align-right">' . get_string('right','editor') . '</option>';
		$output .= '<option value="img-align-center">' . get_string('center','local_mb2builder') . '</option>';
		$output .= '</select>';
		$output .= '</p>';
		$output .= '<div class="element-image-buttons">';
		$output .= '<a href="#" class="mb2-pb-editor-image-save color-success"><strong>' . get_string('save', 'admin') . '</strong></a> | ';
		$output .= '<a href="#" class="mb2-pb-editor-image-cancel color-danger"><strong>' . get_string('cancel') . '</strong></a>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}




	static function local_mb2builder_get_input_editor_helper_link()
	{

		$output = '';


		$output .= '<div class="mb2-pb-editor-helper-element element-link">';

		$output .= '<p>';
		$output .= '<label>Url</label>';
		$output .= '<input type="text" name="mb2_pb_input_link_url" />';
		$output .= '</p>';
		$output .= '<p>';
		$output .= '<label>Open in a new window</label>';
		$output .= '<input type="checkbox" name="mb2_pb_editor_link_target" />';
		$output .= '</p>';
		$output .= '<div class="element-image-buttons">';
		$output .= '<a href="#" class="mb2-pb-editor-link-save color-success"><strong>' . get_string('save', 'admin') . '</strong></a> | ';
		$output .= '<a href="#" class="mb2-pb-editor-link-cancel color-danger"><strong>' . get_string('cancel') . '</strong></a>';
		$output .= '</div>';
		$output .= '</div>';


		return $output;



	}

}
