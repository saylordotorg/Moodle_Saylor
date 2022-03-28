<?php
/**
 * Helper.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */

use mod_wordcards\utils;
use mod_wordcards\constants;

/**
 * Helper class.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */
class mod_wordcards_helper {

    public static function get_tabs(mod_wordcards_module $mod, $current) {
        global $CFG;

        $config = get_config(constants::M_COMPONENT);
        $cmid = $mod->get_cmid();
        $canmanage = $mod->can_manage();
        $canviewreports = $mod->can_viewreports();
        $inactives = array_diff(mod_wordcards_module::get_all_states(), $mod->get_allowed_states());

        $tablabel = utils::fetch_activity_tablabel($mod->get_practicetype(mod_wordcards_module::STATE_STEP1));
        $tabicon = utils::fetch_activity_tabicon($mod->get_practicetype(mod_wordcards_module::STATE_STEP1));

        //show different tabs for free and steps mode (free = admin only) (steps = practice steps + admin)
        $isfreemode= $mod->get_mod()->journeymode==constants::MODE_FREE;
        if($isfreemode) {
            $tabs = [
                new tabobject(mod_wordcards_module::STATE_TERMS,
                    new moodle_url('/mod/wordcards/freemode.php', ['id' => $cmid]),
                    get_string('freemode', 'mod_wordcards'), 'fa-dot-circle-o', true),

            ];
        }else{
            $tabs = [
                new tabobject(mod_wordcards_module::STATE_TERMS,
                    new moodle_url('/mod/wordcards/view.php', ['id' => $cmid]),
                    get_string('tabdefinitions', 'mod_wordcards'), 'fa-dot-circle-o', true),

                new tabobject(mod_wordcards_module::STATE_STEP1,
                    new moodle_url('/mod/wordcards/activity.php', ['id' => $cmid, 'nextstep' => mod_wordcards_module::STATE_STEP1]),
                    $tablabel, $tabicon, true)
            ];
        }

        //in free mode dont show practice steps
        if(!$isfreemode) {
            if ($mod->get_mod()->{mod_wordcards_module::STATE_STEP2} != mod_wordcards_module::PRACTICETYPE_NONE) {
                $practicetype = $mod->get_practicetype(mod_wordcards_module::STATE_STEP2);
                $tablabel = utils::fetch_activity_tablabel($practicetype);
                $tabicon = utils::fetch_activity_tabicon($practicetype);
                $tabs[] = new tabobject(mod_wordcards_module::STATE_STEP2,
                    new moodle_url('/mod/wordcards/activity.php',
                        ['id' => $cmid, 'nextstep' => mod_wordcards_module::STATE_STEP2]),
                    $tablabel, $tabicon, true);
            }

            if ($mod->get_mod()->{mod_wordcards_module::STATE_STEP3} != mod_wordcards_module::PRACTICETYPE_NONE) {
                $practicetype = $mod->get_practicetype(mod_wordcards_module::STATE_STEP3);
                $tablabel = utils::fetch_activity_tablabel($practicetype);
                $tabicon = utils::fetch_activity_tabicon($practicetype);
                $tabs[] = new tabobject(mod_wordcards_module::STATE_STEP3,
                    new moodle_url('/mod/wordcards/activity.php',
                        ['id' => $cmid, 'nextstep' => mod_wordcards_module::STATE_STEP3]),
                    $tablabel, $tabicon, true);

            }


            if ($mod->get_mod()->{mod_wordcards_module::STATE_STEP4} != mod_wordcards_module::PRACTICETYPE_NONE) {
                $practicetype = $mod->get_practicetype(mod_wordcards_module::STATE_STEP4);
                $tablabel = utils::fetch_activity_tablabel($practicetype);
                $tabicon = utils::fetch_activity_tabicon($practicetype);
                $tabs[] = new tabobject(mod_wordcards_module::STATE_STEP4,
                    new moodle_url('/mod/wordcards/activity.php',
                        ['id' => $cmid, 'nextstep' => mod_wordcards_module::STATE_STEP4]),
                    $tablabel, $tabicon, true);
            }

            if ($mod->get_mod()->{mod_wordcards_module::STATE_STEP5} != mod_wordcards_module::PRACTICETYPE_NONE) {
                $practicetype = $mod->get_practicetype(mod_wordcards_module::STATE_STEP5);
                $tablabel = utils::fetch_activity_tablabel($practicetype);
                $tabicon = utils::fetch_activity_tabicon($practicetype);
                $tabs[] = new tabobject(mod_wordcards_module::STATE_STEP5,
                    new moodle_url('/mod/wordcards/activity.php',
                        ['id' => $cmid, 'nextstep' => mod_wordcards_module::STATE_STEP5]),
                    $tablabel, $tabicon, true);

            }
        }


        if($canmanage && $config->enablesetuptab){
            $tabs[] = new tabobject('setup',
                    new moodle_url('/mod/wordcards/setup.php', ['id' => $cmid]),
                    get_string('tabsetup', constants::M_COMPONENT), '', true);
        }

        if($canviewreports){
            $tabs[] = new tabobject('reports',
                    new moodle_url('/mod/wordcards/reports.php', ['id' => $cmid]),
                    get_string('tabreports', constants::M_COMPONENT), '', true);
        }
        if ($canmanage) {
            $tabs[] = new tabobject('managewords',
                new moodle_url('/mod/wordcards/managewords.php', ['id' => $cmid]),
                get_string('tabmanagewords', constants::M_COMPONENT), '', true);

            $tabs[] = new tabobject('import',
                new moodle_url('/mod/wordcards/import.php', ['id' => $cmid]),
                get_string('tabimport', constants::M_COMPONENT), '', true);

                $tabs[] = new tabobject('wordwizard',
                    new moodle_url('/mod/wordcards/wordwizard.php', ['id' => $cmid]),
                    get_string('wordwizard', constants::M_COMPONENT), '', true);
        }


        return new tabtree($tabs, $current, $inactives);
    }

}
