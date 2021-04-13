<?php
/**
 * Services definition.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */

$functions = array(

    'mod_wordcards_mark_as_seen' => array(
        'classname'   => 'mod_wordcards_external',
        'methodname'  => 'mark_as_seen',
        'description' => 'Mark a term as seen.',
        'capabilities'=> 'mod/wordcards:view',
        'type'        => 'write',
        'ajax'        => true,
    ),

    'mod_wordcards_report_successful_association' => array(
        'classname'   => 'mod_wordcards_external',
        'methodname'  => 'report_successful_association',
        'description' => 'Reports a successful association of terms.',
        'capabilities'=> 'mod/wordcards:view',
        'type'        => 'write',
        'ajax'        => true,
    ),

    'mod_wordcards_report_failed_association' => array(
        'classname'   => 'mod_wordcards_external',
        'methodname'  => 'report_failed_association',
        'description' => 'Reports a failed association of terms.',
        'capabilities'=> 'mod/wordcards:view',
        'type'        => 'write',
        'ajax'        => true,
    ),

    'mod_wordcards_check_by_phonetic' => array(
            'classname'   => 'mod_wordcards_external',
            'methodname'  => 'check_by_phonetic',
            'description' => 'compares a spoken phrase to a correct phrase by phoneme' ,
            'capabilities'=> 'mod/wordcards:view',
            'type'        => 'read',
            'ajax'        => true,
    ),

    'mod_wordcards_report_step_grade' => array(
            'classname'   => 'mod_wordcards_external',
            'methodname'  => 'report_step_grade',
            'description' => 'Reports the grade of a step',
            'capabilities'=> 'mod/wordcards:view',
            'type'        => 'write',
            'ajax'        => true,
    ),
    'mod_wordcards_submit_mform' => array(
                'classname'   => 'mod_wordcards_external',
                'methodname'  => 'submit_mform',
                'description' => 'saves or edits term/def form',
                'capabilities'=> 'mod/wordcards:view',
                'type'        => 'write',
                'ajax'        => true,
     ),
);
