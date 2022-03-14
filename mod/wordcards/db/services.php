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
                'capabilities'=> 'mod/wordcards:manage',
                'type'        => 'write',
                'ajax'        => true,
     ),
    'mod_wordcards_submit_newterm' => array(
        'classname'   => 'mod_wordcards_external',
        'methodname'  => 'submit_newterm',
        'description' => 'saves a new term in the db',
        'capabilities'=> 'mod/wordcards:manage',
        'type'        => 'write',
        'ajax'        => true,
    ),
    'mod_wordcards_search_dictionary' => array(
        'classname'   => 'mod_wordcards_external',
        'methodname'  => 'search_dictionary',
        'description' => 'search dictionary term in the db',
        'capabilities'=> 'mod/wordcards:manage',
        'type'        => 'read',
        'ajax'        => true,
    ),
    'mod_wordcards_set_my_words' => array(
        'classname'   => 'mod_wordcards_external',
        'methodname'  => 'set_my_words',
        'description' => 'Set a word as being in my words or not',
        'capabilities'=> 'mod/wordcards:view',
        'type'        => 'write',
        'ajax'        => true,
    ),
);
