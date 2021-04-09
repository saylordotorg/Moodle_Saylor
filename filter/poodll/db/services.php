<?php
/**
 * Services definition.
 *
 * @package mod_poodlltime
 * @author  Justin Hunt - poodll.com
 */

$functions = array(


        'filter_poodll_check_by_phonetic' => array(
                'classname'   => 'filter_poodll_external',
                'methodname'  => 'check_by_phonetic',
                'description' => 'compares a spoken phrase to a correct phrase by phoneme' ,
                'capabilities'=> 'filter/poodll:comparetext',
                'type'        => 'read',
                'ajax'        => true,
        ),

        'filter_poodll_compare_passage_to_transcript' => array(
        'classname'   => 'filter_poodll_external',
        'methodname'  => 'compare_passage_to_transcript',
        'description' => 'compares a spoken phrase to a correct phrase' ,
        'capabilities'=> 'filter/poodll:comparetext',
        'type'        => 'read',
        'ajax'        => true,
        )
);