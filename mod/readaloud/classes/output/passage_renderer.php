<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/26
 * Time: 13:16
 */

namespace mod_readaloud\output;

use \mod_readaloud\constants;
use \mod_readaloud\utils;

class passage_renderer extends \plugin_renderer_base {

    public function render_attempt_scoresheader($passagehelper,$nograde=false) {
      
      $ret = "<hr/>";
      
      $ret .= $this->render_audioplayer($passagehelper->attemptdetails('audiourl'));

      $ret .= '<div style="margin-top:10px;" class="table-responsive mod_readaloud_scorestable">';
      $ret .= '<table class="table table-condensed table-bordered">';
      $ret .= '<thead><tr>';
      $ret .= '<th>'.get_string('wpm', constants::M_COMPONENT).'</th>';
      $ret .= '<th>'.get_string('accuracy_p', constants::M_COMPONENT).'</th>';
      if(!$nograde) {
          $ret .= '<th>' . get_string('grade_p', constants::M_COMPONENT) . '</th>';
      }
      $ret .= '<th>'.get_string('mistakes', constants::M_COMPONENT).'</th>';
      $ret .= '</tr></thead>';
      $ret .= '<tbody><tr>';
      $ret .= '<td id="'.constants::M_GRADING_WPM_SCORE.'"></td>';
      $ret .= '<td id="'.constants::M_GRADING_ACCURACY_SCORE.'"></td>';
      if(!$nograde) {
            $ret .= '<td id="' . constants::M_GRADING_SESSION_SCORE . '"></td>';
      }
      $ret .= '<td id="'.constants::M_GRADING_ERROR_SCORE.'"></td>';
      $ret .= '</tr></tbody>';


      $ret.="</table>";
      $ret.="</div>";
      
      return $ret;
      
    }

    public function render_gradenow($passagehelper,$language, $collapsespaces=false) {
        $actionheader = $this->render_attempt_scoresheader($passagehelper);
        $ret = $this->render_attempt_header($passagehelper->attemptdetails('userfullname'));
        $ret .= $actionheader;
        $ret .= $this->render_passage($passagehelper->attemptdetails('passagesegments'),$language,false,$collapsespaces);
        $ret .= $this->render_passageactions();

        return $ret;
    }

    public function render_userreview($passagehelper,$language, $collapsespaces=false,$nograde=false) {
        $actionheader = $this->render_attempt_scoresheader($passagehelper,$nograde);
        $ret = $actionheader;

        //we put some CSS at the top of the passage container to control things like padding word separation etc
        $extraclasses = 'reviewmode';
        //for Japanese (and later other languages we collapse spaces)
        if($collapsespaces){
            $extraclasses .= ' collapsespaces';
        }
        $thepassage = $this->render_passage($passagehelper->attemptdetails('passagesegments'),$language, false,$extraclasses);
        $ret .= \html_writer::div($thepassage, constants::M_CLASS . '_postattempt');
        return $ret;
    }

    public function render_machinereview($passagehelper, $language, $debug = false) {
        $actionheader = $this->render_attempt_scoresheader($passagehelper);
        $ret = $this->render_machinegrade_attempt_header($passagehelper->attemptdetails('userfullname'));
        $ret .= $actionheader;
        if ($debug) {
            $passage = $this->render_passage($passagehelper->attemptdetails('passagesegments'),$language, false,false);
            $ret .= \html_writer::tag('span', $passage, array('class' => constants::M_CLASS . '_debug'));
        } else {
            $ret .= $this->render_passage($passagehelper->attemptdetails('passagesegments'),$language,false,false);
        }
        return $ret;
    }

    public function render_machinereview_buttons($passagehelper) {
        $attemptid = $passagehelper->attemptdetails('id');
        $readaloudid = $passagehelper->attemptdetails('readaloudid');
        $url = new \moodle_url(constants::M_URL . '/grading.php',
                array('action' => 'gradenow', 'n' => $readaloudid, 'attemptid' => $attemptid));
        $btn = new \single_button($url, get_string('gradethisattempt', constants::M_COMPONENT), 'post');
        $gradenowbutton = $this->output->render($btn);

        $spotcheckbutton = \html_writer::tag('button',
                get_string('spotcheckbutton', constants::M_COMPONENT),
                array('type' => 'button', 'id' => constants::M_CLASS . '_spotcheckbutton',
                        'class' => constants::M_CLASS . '_spotcheckbutton btn btn-success', 'disabled' => true));

        $transcriptcheckbutton = \html_writer::tag('button',
                get_string('transcriptcheckbutton', constants::M_COMPONENT),
                array('type' => 'button', 'id' => constants::M_CLASS . '_transcriptcheckbutton',
                        'class' => constants::M_CLASS . '_transcriptcheckbutton btn btn-warning'));

        $ret = \html_writer::div($gradenowbutton . $spotcheckbutton . $transcriptcheckbutton,
                constants::M_CLASS . '_grading_passageactions');
        return $ret;
    }

    public function render_debuginfo($debugsequences, $transcript, $fulltranscript) {
        $div_fulltranscript = \html_writer::div('<pre>' . print_r(json_decode($fulltranscript), true) . '</pre>',
                constants::M_CLASS . '_grading_debugfulltranscript');
        //sequences
        $debug_sequences = '';
        foreach ($debugsequences as $sequence) {
            $debug_sequences .= \html_writer::tag('span', '<pre>' . print_r($sequence, true) . '</pre>',
                    array('class' => constants::M_CLASS . '_debugsequence'));
        }
        $div_sequences = \html_writer::div($debug_sequences, constants::M_CLASS . '_grading_debugsequences');

        //transcript words
        $t_words = explode(' ', $transcript);
        $t_usewords = [];
        $t_count = 0;
        foreach ($t_words as $t_word) {
            $t_count++;
            $t_usewords[] = \html_writer::tag('span', $t_word,
                    array('class' => constants::M_CLASS . '_debug_transcriptword', 'data-wordnumber' => $t_count));
        }
        $div_transcript = \html_writer::div(implode(' ', $t_usewords), constants::M_CLASS . '_grading_debugtranscript');

        $h_transcript = $this->output->heading('transcript', 5);
        $h_sequences = $this->output->heading('sequences', 5);
        $h_fulltranscript = $this->output->heading('full transcript', 5);
        $ret = \html_writer::div($h_transcript . $div_transcript
                . $h_sequences . $div_sequences
        //. $h_fulltranscript . $div_fulltranscript,constants::M_CLASS . '_grading_debuginfo'
        );
        return $ret;
    }

    public function render_attempt_header($username) {
        $ret = $this->output->heading(get_string('showingattempt', constants::M_COMPONENT, $username), 4);
        return $ret;
    }

    public function render_machinegrade_attempt_header($username) {
        $ret = $this->output->heading(get_string('showingmachinegradedattempt', constants::M_COMPONENT, $username), 3);
        return $ret;
    }

    public function render_passage($passage,$language,  $containerclass=false, $extraclasses=false) {

        // load the HTML document
        $doc = new \DOMDocument;
        // it will assume ISO-8859-1  encoding, so we need to hint it:
        //see: http://stackoverflow.com/questions/8218230/php-domdocument-loadhtml-not-encoding-utf-8-correctly
        @$doc->loadHTML(mb_convert_encoding($passage, 'HTML-ENTITIES', 'UTF-8'));


        // select all the text nodes
        $xpath = new \DOMXPath($doc);
        $nodes = $xpath->query('//text()');
        //init the text count
        $wordcount = 0;

        foreach ($nodes as $node) {

            //if its empty space, move on
            $trimmednode = trim($node->nodeValue);
            if (empty($trimmednode)) {
                continue;
            }

            //explode missed new lines that had been copied and pasted. eg A[newline]B was not split and was one word
            //This resulted in ai selected error words, having different index to their passage text counterpart
            $seperator = ' ';
            //$words = explode($seperator, $node->nodeValue);

            $nodevalue = utils::lines_to_brs($node->nodeValue, $seperator);
            //split each node(line) on words. preg_split messed up with double byte characters
            //$words = preg_split('/\s+/', $nodevalue);
            //so we use mb_split
            $words = mb_split('\s+', $nodevalue);

            foreach ($words as $word) {
                //if its a new line character from lines_to_brs we add it, but not as a word
                if ($word == '<br>') {
                    $newnode = $doc->createElement('br', $word);
                    $node->parentNode->appendChild($newnode);
                    continue;
                }


                $newnode = $doc->createElement('span', $word);
                $spacenode = $doc->createElement('span', $seperator);

                //if its a non word character eg : in 'chapter one : beginning'
                if(\mod_readaloud\diff::cleanText($word)==='') {
                    $node->parentNode->appendChild($newnode);
                    $node->parentNode->appendChild($spacenode);
                    $newnode = $doc->createElement('span', $word);
                    continue;
                }


                //$newnode->appendChild($spacenode);
                //print_r($newnode);
                $wordcount++;
                $newnode->setAttribute('id', constants::M_CLASS_PASSAGEWORD . '_' . $wordcount);
                $newnode->setAttribute('data-wordnumber', $wordcount);
                $newnode->setAttribute('class', constants::M_CLASS_PASSAGEWORD);
                $spacenode->setAttribute('class', constants::M_CLASS_PASSAGESPACE);
                $spacenode->setAttribute('data-wordnumber', $wordcount);
                $spacenode->setAttribute('id', constants::M_CLASS_PASSAGESPACE . '_' . $wordcount);


                $node->parentNode->appendChild($newnode);
                $node->parentNode->appendChild($spacenode);
                $newnode = $doc->createElement('span', $word);
            }
            $node->nodeValue = "";
        }

        $usepassage = $doc->saveHTML($doc->documentElement);

        //for some languages we do not want spaces. Japanese, Chinese. For now this is manual
        //TODO auto determine when to use collapsespaces
        $extraclasses = $extraclasses ?  ' ' . $extraclasses : '';
        //there should no longer be different container classes. remove this soon: Justin 2020-5-31
        if($containerclass) {
            $ret = \html_writer::div($usepassage, $containerclass . $extraclasses);
        }else{
            $ret = \html_writer::div($usepassage,  constants::M_PASSAGE_CONTAINER . $extraclasses);
        }
        return $ret;
    }

    public function render_passageactions() {

        $gradingbutton = \html_writer::tag('button',
                get_string('gradingbutton', constants::M_COMPONENT),
                array('type' => 'button', 'id' => constants::M_CLASS . '_gradingbutton',
                        'class' => constants::M_CLASS . '_gradingbutton btn btn-primary', 'disabled' => true));

        $spotcheckbutton = \html_writer::tag('button',
                get_string('spotcheckbutton', constants::M_COMPONENT),
                array('type' => 'button', 'id' => constants::M_CLASS . '_spotcheckbutton',
                        'class' => constants::M_CLASS . '_spotcheckbutton btn btn-success'));

        $transcriptcheckbutton = \html_writer::tag('button',
                get_string('transcriptcheckbutton', constants::M_COMPONENT),
                array('type' => 'button', 'id' => constants::M_CLASS . '_transcriptcheckbutton',
                        'class' => constants::M_CLASS . '_transcriptcheckbutton btn btn-warning'));

        $clearbutton = \html_writer::tag('button',
                get_string('doclear', constants::M_COMPONENT),
                array('type' => 'button', 'id' => constants::M_CLASS . '_clearbutton',
                        'class' => constants::M_CLASS . '_clearbutton btn btn-link'));

        $buttons = $gradingbutton . $spotcheckbutton . $transcriptcheckbutton . $clearbutton;

        $container = \html_writer::div($buttons, constants::M_CLASS . '_grading_passageactions');
        return $container;
    }

    public function render_audioplayer($audiourl) {
        $audioplayer = \html_writer::tag('audio', '',
                array('controls' => '', 'src' => $audiourl, 'id' => constants::M_GRADING_PLAYER));
        $ret = \html_writer::div($audioplayer, constants::M_GRADING_PLAYER_CONTAINER,
                array('id' => constants::M_GRADING_PLAYER_CONTAINER));
        return $ret;
    }
    
}