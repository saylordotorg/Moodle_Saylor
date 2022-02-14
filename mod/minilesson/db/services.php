<?php
/**
 * Services definition.
 *
 * @package mod_minilesson
 * @author  Justin Hunt - poodll.com
 */

$functions = array(

        'mod_minilesson_report_step_grade' => array(
                'classname'   => 'mod_minilesson_external',
                'methodname'  => 'report_step_grade',
                'description' => 'Reports the grade of a step',
                'capabilities'=> 'mod/minilesson:view',
                'type'        => 'write',
                'ajax'        => true,
        ),

        'mod_minilesson_check_by_phonetic' => array(
                'classname'   => 'mod_minilesson_external',
                'methodname'  => 'check_by_phonetic',
                'description' => 'compares a spoken phrase to a correct phrase by phoneme' ,
                'capabilities'=> 'mod/minilesson:view',
                'type'        => 'read',
                'ajax'        => true,
        ),

        'mod_minilesson_compare_passage_to_transcript' => array(
            'classname'   => 'mod_minilesson_external',
            'methodname'  => 'compare_passage_to_transcript',
            'description' => 'compares a spoken phrase to a correct phrase' ,
            'capabilities'=> 'mod/minilesson:view',
            'type'        => 'read',
            'ajax'        => true,
        ),

        'mod_minilesson_submit_mform' => array(
                'classname'   => 'mod_minilesson_external',
                'methodname'  => 'submit_mform',
                'description' => 'submits mform.',
                'capabilities'=> 'mod/minilesson:managequestions',
                'type'        => 'write',
                'ajax'        => true,
        ),

        'mod_minilesson_delete_item' => array(
                'classname'   => 'mod_minilesson_external',
                'methodname'  => 'delete_item',
                'description' => 'delete item.',
                'capabilities'=> 'mod/minilesson:managequestions',
                'type'        => 'write',
                'ajax'        => true,
        ),

        'mod_minilesson_move_item' => array(
                'classname'   => 'mod_minilesson_external',
                'methodname'  => 'move_item',
                'description' => 'move item.',
                'capabilities'=> 'mod/minilesson:managequestions',
                'type'        => 'write',
                'ajax'        => true,
        ),

        'mod_minilesson_duplicate_item' => array(
            'classname'   => 'mod_minilesson_external',
            'methodname'  => 'duplicate_item',
            'description' => 'duplicate item.',
            'capabilities'=> 'mod/minilesson:managequestions',
            'type'        => 'write',
            'ajax'        => true,
        )

);