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
 * @package   block_accredibledashboard
 * @copyright 2019 Saylor Academy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/blocks/accredibledashboard/locallib.php');

class block_accredibledashboard extends block_list {
    public function init() {
        $this->title = get_string('heading', 'block_accredibledashboard');
    }

	public function get_content() {
		global $USER;

		if ($this->content !== null) {
		return $this->content;
		}

		// Get the user's credentials.
		$credentials = accredibledashboard_get_credentials(null, $USER->email);

		if (isset($credentials)) {
			$this->content         = new stdClass;
			$this->content->items  = array();
			$this->content->icons  = array();

			// Until a link to the wallet can be generated, list all the credentials.
			// TODO: Add a limit and only show the top credentials - as many as in the config.
			foreach ($credentials as $credential) {
				$this->content->items[] = '<div class="d-flex flex-row align-items-center py-1">'.html_writer::empty_tag('img', array('src' => "/blocks/accredibledashboard/assets/icon/certificate_seal.png", 'class' => 'icon float-left')).html_writer::tag('a', $credential->name, array('href' => $credential->sso_url, 'target' => '_blank')).'</div>';
				//$this->content->icons[] = html_writer::empty_tag('img', array('src' => $credential->certificate->image->preview, 'class' => 'icon float-left'));
			}
			// Add footer button to credential wallet.
			reset($credentials);
			$credential = current($credentials);
			$this->content->items[] = '<div class="d-flex flex-row align-items-center py-1">'.html_writer::tag('a', get_string('viewall', 'block_accredibledashboard'), array('class' => 'btn btn-primary text-white mx-auto', 'href' => $credential->wallet_url, 'target' => '_blank')).'</div>';
		

			return $this->content;
		}
		else {
			return "";
		}
	 
	}
}