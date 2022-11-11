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
 * Local class of edwiser_site_monitor
 *
 * @package   block_edwiser_site_monitor
 * @copyright 2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Yogesh Shirsath
 */

namespace block_edwiser_site_monitor;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');

use core_plugin_manager;
use core_component;
use html_writer;
use moodle_url;
use moodle_exception;
use pix_icon;
use stdClass;
use curl;

/**
 * This class implements services for block_edwiser_site_monitor
 *
 * @copyright  2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugins {

    /** @var array edwiserplugins edwiser plugins list */
    public $edwiserplugins = [];

    /** @var array otherplugins other plugins list */
    public $otherplugins = [];

    /** @var array errors list of errors while fetching update or installing update */
    public $errors = [];

    /**
     * Prepare edwiser plugins list and updates
     *
     * @param string $plug plugin component if would like to fetch single plugins details
     *
     * @return bool|string true or error strings while fetching list
     */
    public function prepare_edwiser_plugins_update($plug = null) {
        global $DB;
        $plugins = utility::get_edwiser_plugin_list();
        if (empty($plugins)) {
            return get_string('invalidjsonfile', 'block_edwiser_site_monitor');
        }
        $pluginman = core_plugin_manager::instance();
        $plugininfo = $pluginman->get_plugins();
        foreach ($plugins as $component => $plugin) {

            // Fetch only plugin if $plug is set.
            if (!is_null($plug) && $component != $plug) {
                continue;
            }
            list($plugintype, $pluginname) = core_component::normalize_component($component);

            // Check whether plugin is installed or not.
            if (isset($plugininfo[$plugintype][$pluginname])) {

                // Edwiser plugin which comes along with product.
                if (!isset($plugin->purchaseurl)) {
                    $options = $this->get_edwiser_addon_options($plugin, $component);
                    $this->set_edwiser_plugin($plugintype, $pluginname, $options);
                    continue;
                }

                // Check for plugin license.
                $license = false;
                if (isset($plugin->license)) {
                    $license = $plugin->license;
                } else {
                    $sql = "SELECT value
                              FROM {config_plugins}
                             WHERE plugin = ?
                               AND name LIKE '%license_key%'";
                    $license = $DB->get_field_sql($sql, array($component));
                }

                // Fetch plugin update.
                list(
                    $update,
                    $downloadurl,
                    $changelog
                ) = $this->fetch_edwiser_plugin_update(
                    $pluginman,
                    $license,
                    $plugin->name,
                    $plugininfo[$plugintype][$pluginname]->release,
                    $component
                );
                $options = array(
                    'component'   => $component,
                    'url'         => $plugin->purchaseurl,
                    'download'    => $downloadurl,
                    'msg'         => [],
                    'changelog'   => $changelog
                );

                if (!$license || !$update || !empty($this->errors)) {
                    $options['msg'] = $this->errors;
                    $this->errors = [];
                    $options['update'] = false;
                    $this->set_edwiser_plugin($plugintype, $pluginname, $options);
                    continue;
                }

                // Check does plugin has update.
                $updates = $this->check_edwiser_plugin_update($plugininfo, $plugintype, $pluginname, $update, $options);
                if ($updates !== false) {
                    $options = $updates;
                    $options['update'] = true;
                } else {
                    $options['update'] = false;
                }
                $this->set_edwiser_plugin($plugintype, $pluginname, $options);
            }
        }
        return true;
    }

    /**
     * If plugin is addon with other plugin then get its options
     *
     * @param stdClass $plugin    plugin details object from json
     * @param string   $component component name of plugin
     *
     * @return string options
     */
    public function get_edwiser_addon_options($plugin, $component) {
        $options = array('component'   => $component);
        if (isset($plugin->parent)) {
            $options['parent'] = $plugin->parent;
        }
        return $options;
    }

    /**
     * Checks whether current plugin version is supported
     *
     * @param stdClass $plugin installed plugin details
     * @param string $release plugin release details
     *
     * @return bool true is supported
     */
    public function is_supported_version($plugin, $release) {
        $supported = true;
        $release = explode('.', $release);
        $dbrelease = explode('.', $plugin->release);

        if ($plugin->type == 'theme' && $plugin->name == 'remui') {
            $supported = $dbrelease[0] == $release[0] && $dbrelease[1] == $release[1];
            if (!$supported) {
                return 2;
            }
        }

        foreach ($release as $index => $version) {
            if ($dbrelease[$index] < $version) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check plugin update in fetched plugin details
     *
     * @param core\plugininfo\block $plugininfo currently installed plugin details
     * @param string                $plugintype type of plugin
     * @param string                $pluginname name of plugin
     * @param stdClass              $updates    update found in new version or error string
     * @param array                 $options    options for current update
     *
     * @return array                 $plugin update options
     */
    public function check_edwiser_plugin_update($plugininfo, $plugintype, $pluginname, $release, $options) {

        // There is an error with update method.

        if (empty($release)) {
            return false;
        }
        $status = $this->is_supported_version($plugininfo[$plugintype][$pluginname], $release);
        if ($status === false) {
            return false;
        }
        $options['version'] = $release;
        $options['release'] = $release;
        if ($status === 2) {
            $ex = explode('.', $release);
            $requires = $ex[0] . '.' . $ex[1];
            $options['supportedmoodles'] = [(object)array(
                'version' => $requires
            )];
            $options['msg'][] = get_string('requirehigherversion', 'block_edwiser_site_monitor', $requires);
        }
        return $options;
    }

    /**
     * Fetch plugin update details using curl
     *
     * @param core_plugin_manager $pluginman Plugin manager object
     * @param string              $license   license key
     * @param string              $name      name of plugin
     * @param string              $release   release version of plugin
     * @param string              $component component of plugin
     *
     * @return array (zips|false, zipurl, changelog)
     */
    public function fetch_edwiser_plugin_update($pluginman, $license, $name, $release, $component) {
        global $CFG;
        $changelog = '';
        if (!$license) {
            return array(false, '', $changelog);
        }

        // Create curl edwiser_site_monitor_curl object to initialise curl request.
        $curl = new curl();
        $curl = $curl->post(
            "https://edwiser.org/check-update",
            array(
                'edd_action' => 'get_version',
                'license' => $license,
                'item_name' => urlencode($name),
                'current_version' => $release,
                'url' => urlencode($CFG->wwwroot),
            )
        );
        $response = json_decode($curl);

        // Error while getting server response.
        if ($response == null) {
            $this->errors[] = $curl;
            return array(false, '', $changelog);
        }

        // Invalid license.
        if (isset($response->msg)) {
            $this->errors[] = $response->msg;
            return array(false, '', $changelog);
        }

        $url = $response->download_link;

        $release = $response->new_version;

        // Unserialize and check for changelog of plugin.
        $information = unserialize($response->sections);
        if ($information) {
            $changelog = json_encode(array('changelog' => $information['changelog']));
        }

        return array($release, $url, $changelog);
    }

    /**
     * Get edwiser plugin from edwiserplugins list
     *
     * @param string $type type of plugin
     * @param string $name name of plugin
     *
     * @return stdClass plugin details
     */
    public function get_edwiser_plugin($type, $name) {
        if (isset($this->edwiserplugins[$type]) && isset($this->edwiserplugins[$type][$name])) {
            return $this->edwiserplugins[$type][$name];
        }
        return new stdClass;
    }

    /**
     * Set edwiser plugin details in edwiserplugins list
     *
     * @param string $type    type of plugin
     * @param string $name    name of plugin
     * @param array  $options options to set in plugins list
     *
     * @return void
     */
    public function set_edwiser_plugin($type, $name, $options) {
        if (!isset($this->edwiserplugins[$type])) {
            $this->edwiserplugins[$type] = [];
        }
        $this->edwiserplugins[$type][$name] = $options;
    }

    /**
     * Explain why {@link core_plugin_manager::is_remote_plugin_installable()} returned false.
     *
     * @param string $reason the reason code as returned by the plugin manager
     * @return string
     */
    private function info_remote_plugin_not_installable($reason) {
        global $OUTPUT;
        if ($reason === 'notwritableplugintype' or $reason === 'notwritableplugin') {
            return $OUTPUT->help_icon('notwritable', 'core_plugin', get_string('notwritable', 'core_plugin'));
        }

        if ($reason === 'remoteunavailable') {
            return $OUTPUT->help_icon('notdownloadable', 'core_plugin', get_string('notdownloadable', 'core_plugin'));
        }

        return false;
    }

    /**
     * Get update information ouput for plugin
     *
     * @param object  $pluginman  plugin manager object
     * @param object  $updateinfo update information of plugin
     * @param bool    $edwiser    is current plugin is edwiser or other
     *
     * @return object              html output for update block
     */
    private function plugin_update_info($pluginman, $updateinfo, $edwiser = false) {
        global $OUTPUT, $CFG;
        $status = new stdClass;
        $status->has = false;
        $boxclasses = 'edwiserpluginupdateinfo';
        $info = array();
        if (isset($updateinfo->release)) {
            $info[] = html_writer::div(
                get_string('updateavailable_release', 'core_plugin', $updateinfo->release),
                'info release'
            );
        }

        if (isset($updateinfo->maturity)) {
            $info[] = html_writer::div(
                get_string('maturity'.$updateinfo->maturity, 'core_admin'),
                'info maturity'
            );
            $boxclasses .= ' maturity'.$updateinfo->maturity;
        }

        if (isset($updateinfo->download)) {
            $info[] = html_writer::div(
                html_writer::link(
                    $updateinfo->download,
                    get_string('download'),
                    array('target' => '_blank')
                ),
                'info download'
            );
        }

        if (isset($updateinfo->url)) {
            $info[] = html_writer::div(
                html_writer::link(
                    $updateinfo->url,
                    get_string('updateavailable_moreinfo', 'core_plugin'),
                    array('target' => '_blank')
                ),
                'info more'
            );
        }
        if ($edwiser && isset($updateinfo->changelog)) {
            $info[] = html_writer::div(
                html_writer::link(
                    '#',
                    get_string('changelog', 'block_edwiser_site_monitor'),
                    array(
                        'class' => 'showchangelog',
                        'target' => '_blank',
                        'data-log' => $updateinfo->changelog
                    )
                ),
                'info changelog'
            );
        }
        $box = html_writer::start_div($boxclasses);
        if (isset($updateinfo->version)) {
            $box .= html_writer::div(
                get_string('updateavailable', 'core_plugin', $updateinfo->version),
                'version'
            );
            $box .= html_writer::div(
                implode(html_writer::span(' ', 'separator'), $info),
                'infos'
            );
        }
        if (!$edwiser) {
            $status->has = true;
            $reason = null;
            if ($pluginman->is_remote_plugin_installable($updateinfo->component, $updateinfo->version, $reason)) {
                $button = $OUTPUT->single_button(
                    new moodle_url(
                        $CFG->wwwroot . '/admin/plugins.php',
                        array(
                            'updatesonly' => 1,
                            'contribonly' => 0,
                            'installupdate' => $updateinfo->component,
                            'installupdateversion' => $updateinfo->version
                        )
                    ),
                    get_string('updateavailableinstall', 'core_admin')
                );
                $box .= str_replace('form method="post"', 'form target="_blank" method="post"', $button);
            } else {
                $reasonhelp = $this->info_remote_plugin_not_installable($reason);
                if (isset($reasonhelp)) {
                    $box .= html_writer::div($reasonhelp, 'reasonhelp updateavailableinstall');
                }
            }
        } else {
            if (empty($updateinfo->msg)) {
                if (isset($updateinfo->update) && isset($updateinfo->version)) {
                    $status->has = true;
                    $button = $OUTPUT->single_button(
                        new moodle_url(
                            $CFG->wwwroot . '/blocks/edwiser_site_monitor/plugin.php',
                            array(
                                'installupdate' => $updateinfo->component,
                                'installupdateversion' => $updateinfo->version,
                                'sesskey' => sesskey()
                            )
                        ),
                        get_string('updateavailableinstall', 'core_admin')
                    );
                    $box .= str_replace('form method="post"', 'form target="_blank" method="post"', $button);
                }
            } else {
                $box .= html_writer::start_tag('div', array('class' => 'text-danger'));
                $tag = count($updateinfo->msg) > 1 ? 'ol' : 'ul';
                $box .= html_writer::start_tag($tag);
                foreach ($updateinfo->msg as $msg) {
                    $box .= html_writer::tag('li', $msg);
                }
                $box .= html_writer::end_tag($tag);
                $box .= html_writer::end_tag('div');
                if (isset($updateinfo->update)) {
                    $status->has = true;
                }
            }
        }
        $box .= html_writer::end_div();
        $status->html = $box;
        return $status;
    }

    /**
     * Get plugin obejct to show in the plugins table
     *
     * @param object  $pluginman plugin manager
     * @param object  $pluginfo  plugin information object
     * @param bool    $edwiser   is current plugin is edwiser or other
     *
     * @return stdClass             plugin object ofr mustache
     */
    private function get_plugin_object($pluginman, $pluginfo, $edwiser = false) {
        global $PAGE, $OUTPUT, $CFG;
        $plugin = new stdClass;
        $plugin->type = $pluginfo->type;
        $plugin->name = $pluginfo->name;
        $plugin->component = $pluginfo->type . '_' . $pluginfo->name;
        $plugin->class = 'type-' . $plugin->type . ' name-' . $plugin->type . '_' . $plugin->name;
        $status = $pluginfo->get_status();
        $plugin->class .= ' status-'.$status;
        if ($PAGE->theme->resolve_image_location('icon', $pluginfo->type . '_' . $pluginfo->name, null)) {
            $icon = $OUTPUT->pix_icon('icon', '', $pluginfo->type . '_' . $pluginfo->name, array('class' => 'icon pluginicon'));
        } else {
            $icon = $OUTPUT->spacer();
        }
        $plugin->icon = $icon;

        $actions = [];
        $settingsurl = $pluginfo->get_settings_url();
        if (!is_null($settingsurl)) {
            $actions[] = html_writer::link(
                $settingsurl,
                get_string('settings', 'core_plugin'),
                array('target' => '_blank', 'class' => 'settings')
            );
        }

        if ($uninstallurl = $pluginman->get_uninstall_url($pluginfo->component, $CFG->wwwroot . '/my/')) {
            $actions[] = html_writer::link($uninstallurl, get_string('uninstall', 'core_plugin'), array('target' => '_blank'));
        }

        if (!empty($actions)) {
            $actions = html_writer::div(
                implode(html_writer::span(' ', 'separator'), $actions)
            );
        } else {
            $actions = false;
        }
        $plugin->actions = $actions;

        $plugin->displayname = $pluginfo->displayname;
        $plugin->release = $pluginfo->release;
        $plugin->versiondisk = $pluginfo->versiondisk;
        $plugin->versiondb = $pluginfo->versiondb;
        if ($pluginfo->is_standard()) {
            $source = '';
            $plugin->class .= ' standard';
        } else {
            $source = html_writer::div(get_string('sourceext', 'core_plugin'), 'source label label-info');
            $plugin->class .= ' extension';
        }
        $plugin->source = $source;
        if ($status === core_plugin_manager::PLUGIN_STATUS_MISSING) {
            $msg = html_writer::div(get_string('status_missing', 'core_plugin'), 'statusmsg label label-important');
        } else if ($status === core_plugin_manager::PLUGIN_STATUS_NEW) {
            $msg = html_writer::div(get_string('status_new', 'core_plugin'), 'statusmsg label label-success');
        } else {
            $msg = '';
        }
        $plugin->msg = $msg;
        $requriedby = $pluginman->other_plugins_that_require($plugin->component);
        if ($requriedby) {
            $plugin->requiredby = html_writer::tag(
                'div',
                get_string('requiredby', 'core_plugin', implode(', ', $requriedby)),
                array('class' => 'requiredby')
            );
        }
        $update = (object) [
            'has' => false,
            'html' => ''
        ];
        if (is_array($pluginfo->available_updates())) {
            $update->has = true;
            foreach ($pluginfo->available_updates() as $availableupdate) {
                $updateinfo = $this->plugin_update_info($pluginman, $availableupdate);
                $update->has &= $updateinfo->has;
                $update->html .= $updateinfo->html;
            }
        } else if ($edwiser) {
            $edwiserplugin = (object) $this->get_edwiser_plugin(
                $pluginfo->type,
                $pluginfo->name
            );
            if (isset($edwiserplugin->changelog)) {
                $plugin->changelog = $edwiserplugin->changelog;
            }
            if (isset($edwiserplugin->parent)) {
                $plugin->parent = html_writer::div(
                    get_string('comeswith', 'block_edwiser_site_monitor', $edwiserplugin->parent),
                    'comeswith'
                );
            }
            $update = $this->plugin_update_info(
                $pluginman,
                $edwiserplugin,
                $edwiser
            );
        }

        $plugin->class .= $update->has ? ' update' : '';
        $plugin->update = $update;
        return $plugin;
    }

    /**
     * Check for all installed edwiser plugins
     *
     * @return object plugins list for mustache
     */
    public function get_edwiser_plugins() {
        $plugins = new stdClass;
        $plugins->list = [];
        $pluginman = core_plugin_manager::instance();
        $plugininfo = $pluginman->get_plugins();
        if (empty($this->edwiserplugins) && !empty($this->errors)) {
            $plugins->haserrors = true;
            $plugins->errors = $this->errors;
        }
        foreach ($this->edwiserplugins as $type => $names) {
            foreach (array_keys($names) as $name) {
                if (isset($plugininfo[$type][$name])) {
                    $plugins->list[] = $this->get_plugin_object($pluginman, $plugininfo[$type][$name], true);
                }
            }
        }
        $plugins->hasplugins = !empty($plugins->list);
        return $plugins;
    }

    /**
     * Get header row for plugins type
     *
     * @param core_plugin_manager $pluginman core plugin manager object
     * @param string              $type      type of plugin
     *
     * @return [type]            [description]
     */
    public function get_plugins_type_header($pluginman, $type) {
        global $OUTPUT;
        $header = new stdClass;
        $header->header = true;
        $header->type = $type;
        $header->class = 'plugintypeheader type-' . $type;
        $header->html = $pluginman->plugintype_name_plural($type);
        $pluginclass = core_plugin_manager::resolve_plugininfo_class($type);
        if ($manageurl = $pluginclass::get_manage_url()) {
            $header->html .= $OUTPUT->action_icon(
                $manageurl,
                new pix_icon(
                    'i/settings',
                    get_string('settings', 'core_plugin')
                )
            );
            $header->html = str_replace('<a', '<a target="_blank"', $header->html);
        }
        return $header;
    }

    /**
     * Check for all installed edwiser plugins
     *
     * @return object plugins list for mustache
     */
    public function get_plugins() {
        $plugins = new stdClass;
        $plugins->overviewall = 0;
        $plugins->overviewupdate = 0;
        $hasupdate = false;
        $plugins->list = [];
        $this->prepare_edwiser_plugins_update();
        $checker = \core\update\checker::instance();
        $checker->fetch();
        $pluginman = core_plugin_manager::instance();
        $plugininfo = $pluginman->get_plugins();
        $index = 0;
        foreach ($plugininfo as $type => $plugs) {
            $header = $index++;
            $additional = $update = false;
            $plugins->list[$header] = $this->get_plugins_type_header($pluginman, $type);
            foreach (array_values($plugs) as $pluginfo) {
                if ($pluginfo->is_standard()) {
                    continue;
                }
                $additional = true;
                $plugins->overviewall++;
                $plugin = $this->get_plugin_object(
                    $pluginman,
                    $pluginfo,
                    isset($this->edwiserplugins[$type][$pluginfo->name])
                );
                if ($plugin->update->has == true) {
                    $plugins->overviewupdate++;
                    $hasupdate = $update = true;
                }
                $plugins->list[$index++] = $plugin;
            }
            $plugins->list[$header]->class .= $update ? ' update' : '';
            if (!$additional) {
                $index--;
            }
        }
        $plugins->hasupdate = $hasupdate;
        return $plugins;
    }

    /**
     * Thin wrapper for the core's download_file_content() function.
     *
     * @param string $url    URL to the file
     * @param string $tofile full path to where to store the downloaded file
     *
     * @return bool
     */
    protected function download_file_content($url, $tofile) {

        // Prepare the parameters for the download_file_content() function.
        $headers = null;
        $postdata = null;
        $fullresponse = false;
        $timeout = 300;
        $connecttimeout = 20;
        $skipcertverify = false;
        $tofile = $tofile;
        $calctimeout = false;
        return download_file_content(
            $url,
            $headers,
            $postdata,
            $fullresponse,
            $timeout,
            $connecttimeout,
            $skipcertverify,
            $tofile,
            $calctimeout
        );
    }

    /**
     * Download the ZIP file with the plugin package from the given location
     *
     * @param string $url    URL to the file
     * @param string $tofile full path to where to store the downloaded file
     *
     * @return bool false on error
     */
    protected function download_plugin_zip_file($url, $tofile) {

        $status = $this->download_file_content($url, $tofile);
        if (!$status) {
            debugging(get_string('errorfetching', 'block_edwiser_site_monitor', $url), DEBUG_DEVELOPER);
            @unlink($tofile);
            return false;
        }

        return true;
    }

    /**
     * Obtain the plugin ZIP file from the given URL
     *
     * The caller is supposed to know both downloads URL and the MD5 hash of
     * the ZIP contents in advance, typically by using the API requests against
     * the plugins directory.
     *
     * @param object $pluginman plugin manager object
     * @param string $url       url of plugin file
     * @param string $name      name with component of plugin
     *
     * @return string|bool full path to the file, false on error
     */
    public function get_remote_plugin_zip($pluginman, $url, $name) {
        global $CFG;

        if (!empty($CFG->disableupdateautodeploy)) {
            return false;
        }

        // Sanitize and validate the URL.
        $url = str_replace(array("\r", "\n"), '', $url);

        if (!preg_match('|^https?://|i', $url)) {
            $this->errors[] = 'Error fetching plugin ZIP: unsupported transport protocol: '.$url;
            return false;
        }

        $pluginman->zipdirectory = make_temp_directory('core_plugin/code_manager').'/distfiles/';

        // The cache location for the file.
        $distfile = $pluginman->zipdirectory.$name.'.zip';

        // Download the file into a temporary location.
        $tempdir = make_request_directory();
        $tempfile = $tempdir.'/plugin.zip';
        $result = $this->download_plugin_zip_file($url, $tempfile);

        if (!$result) {
            return false;
        }

        $md5 = md5_file($tempfile);

        // If the file is empty, something went wrong.
        if ($md5 === 'd41d8cd98f00b204e9800998ecf8427e') {
            return false;
        }

        // Store the file in our cache.
        if (!rename($tempfile, $distfile)) {
            return false;
        }

        return $distfile;
    }

    /**
     * Get plugin details from version.php file
     *
     * @param string $path        path of plugin
     * @param array  $zipcontents zip file contents
     *
     * @return stdClass|bool   plugin details
     */
    public function get_plugin_details($path, $zipcontents) {

        foreach ($zipcontents as $file => $status) {
            if (!$status) {
                return false;
            }
        }
        $root = current(array_keys($zipcontents));
        $file = $root . 'version.php';
        if (isset($zipcontents[$file]) && $zipcontents[$file] == 1 && file_exists($path . '/' . $file)) {
            $plugin = new stdClass;
            require_once($path . '/' . $file);
            return $plugin;
        }
        return false;
    }

    /**
     * Unzip zip file of plugin file and return its content
     * @param  object $pluginman Plugin manager
     * @param  string $zip       Zip file path
     * @param  string $temp      Temporary path
     * @param  string $root      Root directory path
     * @return array             Zip file content array
     */
    public function unzip_plugin_file($pluginman, $zip, $temp, $root) {
        ini_set('log_errors', 'Off');
        $contents = $pluginman->unzip_plugin_file($zip, $temp, $root);
        ini_set('log_errors', 'On');
        return $contents;
    }

    /**
     * Verify zip file is valid
     *
     * @param object $pluginman core plugin manager
     * @param string $zip       zip file
     * @param string $temp      temporary directory path
     * @param string $name      name of zip file
     *
     * @return array         True is zip file is valid
     */
    public function verify_zip($pluginman, $zip, $temp, $name) {

        $zipcontents = $this->unzip_plugin_file($pluginman, $zip, $temp, $name);

        if (empty($zipcontents)) {
            $this->errors[] = get_string('invalidzip', 'block_edwiser_site_monitor', $name);
            return false;
        }

        $zipcount = 0;
        // Check all files from zip is ok and has zip inside zip.
        foreach ($zipcontents as $file => $status) {
            if (!$status) {
                $this->errors[] = get_string('invalidzip', 'block_edwiser_site_monitor', $name);
                return false;
            }
            if (stripos($file, ".zip") !== false) {
                $zipcount++;
                continue;
            }
            if (stripos($file, "readme") !== false) {
                unset($zipcontents[$file]);
            }
        }

        // If cound is different means only one plugin file is there.
        // Else zip contains multiple plugins.
        if ($zipcount != count($zipcontents)) {
            $plugin = $this->get_plugin_details($temp, $zipcontents);
            if (!$plugin) {
                $this->errors[] = get_string('unabletoloadplugindetails', 'block_edwiser_site_monitor', $name);
            }
            return $plugin;
        }
        $zipserror = false;
        $zips = $zipcontents;
        foreach (array_keys($zips) as $file) {
            $name1 = str_replace('.zip', '', $file);
            $path = make_request_directory();
            $zipcontents = $this->unzip_plugin_file($pluginman, $temp . '/' . $file, $path, $name1);

            if (empty($zipcontents)) {
                $this->errors[] = get_string('invalidzip', 'block_edwiser_site_monitor', $name . '  ->  ' . $name1);
                return false;
            }

            $plugin = $this->get_plugin_details($path, $zipcontents);
            unset($zips[$file]);
            if (!$plugin) {
                $this->errors[] = get_string('unabletoloadplugindetails', 'block_edwiser_site_monitor', $name . '  ->  ' . $name1);
                $zipserror = true;
            } else {
                $zips[$temp . '/' . $file] = $plugin;
            }
        }
        return $zipserror == true ? false : $zips;
    }

    /**
     * Validate zip file before installing plugin
     *
     * @param core_plugin_manager      $pluginman core plugin manager object
     * @param \core\update\remote_info $plugin    plugin information
     * @param string                   $zipfile   zip file path
     * @param bool                     $silent    true if dont wanna show debugg error
     *
     * @return bool                 validation result
     */
    private function validate_plugin_zip($pluginman, $plugin, $zipfile, $silent) {
        global $CFG, $OUTPUT;

        $ok = get_string('ok', 'core');

        $silent or mtrace(get_string('packagesvalidating', 'core_plugin', $plugin->component), ' ... ');

        list($plugintype, $pluginname) = core_component::normalize_component($plugin->component);

        $tmp = make_request_directory();
        $zipcontents = $this->unzip_plugin_file($pluginman, $zipfile, $tmp, $pluginname);

        if (empty($zipcontents)) {
            $silent or mtrace(get_string('error'));
            $silent or mtrace(get_string('unabletounzip', 'block_edwiser_site_monitor', $zipfile));
            return false;
        }

        $validator = \core\update\validator::instance($tmp, $zipcontents);
        $validator->assert_plugin_type($plugintype);
        $validator->assert_moodle_version($CFG->version);

        // TODO Check for missing dependencies during validation.
        $result = $validator->execute();
        $result ? ($silent or mtrace($ok)) : ($silent or mtrace(get_string('error')));

        if (!$silent) {
            foreach ($validator->get_messages() as $message) {
                if ($message->level === $validator::WARNING || $message->level === $validator::ERROR and !CLI_SCRIPT) {
                    mtrace('  <strong>['.$validator->message_level_name($message->level).']</strong>', ' ');
                } else {
                    mtrace('  ['.$validator->message_level_name($message->level).']', ' ');
                }

                mtrace($validator->message_code_name($message->msgcode), ' ');

                $info = $validator->message_code_info($message->msgcode, $message->addinfo);
                if ($info) {
                    mtrace('['.s($info).']', ' ');
                } else if (is_string($message->addinfo)) {
                    mtrace('['.s($message->addinfo, true).']', ' ');
                } else {
                    mtrace('['.s(json_encode($message->addinfo, true)).']', ' ');
                }

                if ($icon = $validator->message_help_icon($message->msgcode)) {
                    if (CLI_SCRIPT) {
                        mtrace(
                            PHP_EOL.'  ^^^ '.get_string('help').': '. get_string(
                                $icon->identifier.'_help',
                                $icon->component
                            ),
                            ''
                        );
                    } else {
                        mtrace($OUTPUT->render($icon), ' ');
                    }
                }
                mtrace(PHP_EOL, '');
            }
        }
        if (!$result) {
            $silent or mtrace(get_string('packagesvalidatingfailed', 'core_plugin'));
        }
        $silent or mtrace(PHP_EOL, '');
        return $result;
    }

    /**
     * Perform the installation of plugins.
     *
     * If used for installation of remote plugins from the Edwiser Plugins
     * directory, the $plugins must be list of {@link \core\update\remote_info}
     * object that represent installable remote plugins. The caller can use
     * {@link self::filter_installable()} to prepare the list.
     *
     * If used for installation of plugins from locally available ZIP files,
     * the $plugins should be list of objects with properties ->component and
     * ->zipfilepath.
     *
     * The method uses {@link mtrace()} to produce direct output and can be
     * used in both web and cli interfaces.
     *
     * @param  \core\update\remote_info $plugin    list of plugins
     * @param  bool                     $confirmed should the files be really deployed into the dirroot?
     * @param  bool                     $silent    hide debugg errors is set true
     *
     * @return bool                                 true on success
     */
    public function install_plugin(\core\update\remote_info $plugin, $confirmed, $silent) {
        global $CFG;

        $pluginman = core_plugin_manager::instance();
        if (!empty($CFG->disableupdateautodeploy)) {
            return false;
        }

        $ok = get_string('ok', 'core');

        // Let admins know they can expect more verbose output.
        $silent or mtrace(get_string('packagesdebug', 'core_plugin'), PHP_EOL);

        // Download all ZIP packages if we do not have them yet.
        $zip = array();

        $silent or mtrace(get_string('packagesdownloading', 'core_plugin', $plugin->component), ' ... ');

        if (!isset($plugin->version['download']) || trim($plugin->version['download']) == '') {
            $zip = false;
            $errormsg = get_string('cannotdownloadzipfile', 'core_error');
            if (!empty($plugin->version['msg'])) {
                $tag = count($plugin->version['msg']) > 1 ? 'ol' : 'ul';
                $errormsg = html_writer::start_tag($tag);
                foreach ($plugin->version['msg'] as $msg) {
                    $errormsg .= html_writer::tag('li', $msg);
                }
                $errormsg .= html_writer::end_tag($tag);
            }
            $silent or mtrace(PHP_EOL.' <- '. $errormsg . ' ->', '');
        } else {
            $zip = $this->get_remote_plugin_zip(
                $pluginman,
                $plugin->version['download'],
                $plugin->component
            );
        }
        if (!$zip) {
            $silent or mtrace(get_string('error'));
            return false;
        }
        $silent or mtrace($ok);

        $temp = make_request_directory();
        $zips = $this->verify_zip($pluginman, $zip, $temp, $plugin->component);
        $zipfile = $zip;

        if (!$zips) {
            $silent or mtrace(get_string('error'));
            $silent or mtrace(get_string('unabletounzip', 'block_edwiser_site_monitor', $zipfile), PHP_EOL);
            return false;
        }
        $checks = true;
        // Validate all downloaded packages.
        foreach ($zips as $zipfile => $plugin) {
            $checks &= $this->validate_plugin_zip($pluginman, $plugin, $zipfile, $silent);
        }
        if (!$checks) {
            return;
        }
        if (!$confirmed) {
            return true;
        }

        if (!is_array($zips)) {
            $zips = [];
            $zips[$zip] = $plugin->component;
        }

        foreach ($zips as $zipfile => $plugin) {
            // Extract all ZIP packs do the dirroot.
            $silent or mtrace(get_string('packagesextracting', 'core_plugin', $plugin->component), ' ... ');
            list($plugintype, $pluginname) = core_component::normalize_component($plugin->component);

            $target = $pluginman->get_plugintype_root($plugintype);
            $plugininfo = $pluginman->get_plugin_info($plugin->component);
            if (file_exists($target.'/'.$pluginname) && $plugininfo) {
                $pluginman->remove_plugin_folder($plugininfo);
            }
            if (!$this->unzip_plugin_file($pluginman, $zipfile, $target, $pluginname)) {
                $silent or mtrace(get_string('error'));
                $silent or mtrace(get_string('unabletounzip', 'block_edwiser_site_monitor', $zipfile), PHP_EOL);
                if (function_exists('opcache_reset')) {
                    opcache_reset();
                }
                return false;
            }
        }

        $silent or mtrace($ok);
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return true;
    }

    /**
     * Helper procedure/macro for installing remote pluginsat block/edwiser_site_monitor/plugin.php
     *
     * Does not return, always redirects or exits.
     *
     * @param \core\update\remote_info  $installable list of \core\update\remote_info
     * @param bool                      $confirmed   false: display the validation screen, true: proceed installation
     * @param string                    $heading     validation screen heading
     * @param mixed                     $continue    URL to proceed with installation at the validation screen
     * @param mixed                     $return      URL to go back on cancelling at the validation screen
     *
     * @return void
     */
    public function upgrade_install_plugin(
        \core\update\remote_info $installable,
        $confirmed,
        $heading='',
        $continue=null,
        $return=null
    ) {
        global $CFG, $PAGE;

        if (empty($return)) {
            $return = $PAGE->url;
        }

        if (!empty($CFG->disableupdateautodeploy)) {
            redirect($return);
        }

        if (empty($installable)) {
            redirect($return);
        }

        if ($confirmed) {
            // Installation confirmed at the validation results page.
            if (!$this->install_plugin($installable, true, true)) {
                throw new moodle_exception('install_plugins_failed', 'core_plugin', $return);
            }

            // Always redirect to admin/index.php to perform the database upgrade.
            // Do not throw away the existing $PAGE->url parameters such as.
            // confirmupgrade or confirmrelease if $PAGE->url is a superset of the.
            // URL we must go to.
            $mustgoto = new moodle_url('/admin/index.php', array('cache' => 0, 'confirmplugincheck' => 0));
            if ($mustgoto->compare($PAGE->url, URL_MATCH_PARAMS)) {
                redirect($PAGE->url);
            } else {
                redirect($mustgoto);
            }

        } else {
            $output = $PAGE->get_renderer('core', 'admin');
            echo $output->header();
            if ($heading) {
                echo $output->heading($heading, 3);
            }
            echo html_writer::start_tag('pre', array('class' => 'plugin-install-console'));
            $validated = $this->install_plugin($installable, false, false);
            echo html_writer::end_tag('pre');
            if ($validated) {
                echo $output->plugins_management_confirm_buttons($continue, $return);
            } else {
                echo $output->plugins_management_confirm_buttons(null, $return);
            }
            echo $output->footer();
        }
    }
}
