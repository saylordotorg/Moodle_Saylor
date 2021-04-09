<?php

/**
 * Differencing class for Poodll PChat
 *
 * A class containing functions for computing diffs between reading passage and audio transcript
 * This mght be hard to follow, but its documented as well as I could
 *
 * @package    mod_pchat
 * @copyright  2019 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_poodll;

defined('MOODLE_INTERNAL') || die();


class diff{

    // define the constants
    const MATCHED = 0;
    const UNMATCHED = 1;
    const ALTERNATEMATCH= 1;
    const NOTALTERNATEMATCH= 0;


    /*
 * Split passage of text into an array of words
 *
 */
    public static function fetchWordArray($thetext){

        //tidy up the text so its just lower case words seperated by spaces
        $thetext = self::cleanText($thetext);

        //split on spaces into words
        $textbits = explode(' ',$thetext);

        //remove any empty elements
        $textbits = array_filter($textbits, function($value) { return $value !== ''; });

        //re index array because array_filter converts array to assoc. (ie could have gone from indexes 0,1,2,3,4,5 to 0,1,3,4,5)
        $textbits = array_values($textbits);

        return $textbits;
    }

    /*
    * Clean word of things that might prevent a match
     * i) lowercase it
     * ii) remove html characters
     * iii) replace any line ends with spaces (so we can "split" later)
     * iv) remove punctuation
    *
    */
    public static function cleanText($thetext){
        //lowercaseify
        $thetext=strtolower($thetext);

        //remove any html
        $thetext = strip_tags($thetext);

        //replace all line ends with spaces
        $thetext = preg_replace('#\R+#', ' ', $thetext);

        //remove punctuation
        //see https://stackoverflow.com/questions/5233734/how-to-strip-punctuation-in-php
        // $thetext = preg_replace("#[[:punct:]]#", "", $thetext);
        //https://stackoverflow.com/questions/5689918/php-strip-punctuation
        $thetext = preg_replace("/[[:punct:]]+/", "", $thetext);

        //remove bad chars
        $b_open="“";
        $b_close="”";
        $b_sopen='‘';
        $b_sclose='’';
        $bads= array($b_open,$b_close,$b_sopen,$b_sclose);
        foreach($bads as $bad){
            $thetext=str_replace($bad,'',$thetext);
        }

        //remove double spaces
        //split on spaces into words
        $textbits = explode(' ',$thetext);
        //remove any empty elements
        $textbits = array_filter($textbits, function($value) { return $value !== ''; });
        $thetext = implode(' ',$textbits);
        return $thetext;
    }

    /*
     * This function parses and replaces {{view|alternate}} strings from text passages
     * It is used to prepare for comparison
     *
     * an alternates string like this
     * "cat|cuts|cats is
     * dog|doggies|dogs is"
     *
     * is parsed into an array where:
     * array([words,forwardmatches],[words,forwardmatches])
     * so in this case
     * array([[cat,cut,cats],[cats=>is]] , [[dog,doggies,dogs],[dogs=>is]]
     * When processing, the first item in the word array is matched to the passage word. If it matches, the subsequent items
     * in the word array are matched to the transcript. If we have a transcript match, yay. If we have a transcript match AND
     * it has a forward match. That will be returned so that the next pass of the match loop will accept that forward match
     * as a match on the next passage word. This allows a passage "Dog" to be matched to "Dog's" and not flag the leftover "is" in the passage as incorrect.
     *
     * TO DO: For this whole alternates thing ...optimize so we only parse the passage once when its saved
     *  and store the index of a word with alternates, so we do not need to loop through the alternates array on checking
     *
     */
    public static function fetchAlternativesArray($thealternates)
    {
        //return empty if input data is useless
        if(trim($thealternates)==''){
            return [];
        }
        //regexp from https://stackoverflow.com/questions/7058168/explode-textarea-php-at-new-lines
        $lines = preg_split('/\r\n|[\r\n]/', $thealternates);
        $alternatives = [];

        foreach($lines as $line){
            if(!empty(trim($line))) {
                $set = explode('|', $line);
                switch(count($set)){
                    case 0:
                    case 1:
                        break;
                    case 2:
                    default:
                        //clean each word in set
                        $forwardmatches= [];
                        $words= [];
                        foreach($set as $wordstring){
                            $wordstring = trim($wordstring);
                            if($wordstring==''){continue;}
                            $wordsarray=explode(' ',$wordstring);

                            $word = $wordsarray[0];
                            if($word !='*') {
                                $word = self::cleanText($word);
                            }
                            $words[]=$word;

                            if(count($wordsarray)>1 && $word !='*' && !is_number($word)){
                                $forwardmatches[$word]=self::cleanText($wordsarray[1]);
                            }
                        }
                        $alternatives[] = [$words,$forwardmatches];
                }
            }
        }
        return $alternatives;
    }

    //Do some adhoc match judgement based on common language transcription errors by AI
    public static function generous_match($passageword,$transcriptword,$language){
        $lang = substr($language,0,2);
        switch($lang){
            case 'en':
                if(self::mb_strequals($passageword . 's', $transcriptword)){return true;}
                if(self::mb_strequals($passageword . 'ed', $transcriptword)){return true;}
                break;
            default:
                return false;
        }
        return false;
    }

    //Loop through passage, nest looping through transcript building collections of sequences (passage match records)
    //one sequence = sequence_length[length] + sequence_start(transcript)[tposition] + sequence_start(passage)[pposition]
    //we do not discriminate over length or position of sequence at this stage. All sequences are saved

    //NB The sequence length should be the same in the passage and transcript (because they "matched")
    //But we attempted to have "multiple word alternatives" which could mean that the match length in the transcript
    // would differ from the match length in the passage
    //eg 1989 -> nineteen eighty nine.
    // BUT we cancelled this feature because the code became more complex than wanted to maintain,
    // however still kept the transcript sequence length and passage sequence length code in place in this function
    // so we could have another go at this if needed
    //
    //returns array of sequences
    public static function fetchSequences($passage, $transcript, $alternatives, $language)
    {
        $p_length = count($passage);
        $t_length = count($transcript);
        $sequences = array();
        $t_slength=0; //sequence length (in the transcript)
        $p_slength=0; //sequence length (in the passage)
        $alt_positions=[]; //we record alternate usages in sequence
        $tstart =0; //transcript sequence match search start index
        $forwardmatch=false; //if any alternates declare a forward match we keep that here


        //loop through passage word by word
        for($pstart =0; $pstart < $p_length; $pstart++){
            //loop through transcript finding matches starting from current passage word
            //we step over the length of any sequences we have already found to begin search for next sequence
            while($t_slength + $tstart < $t_length &&
                $p_slength + $pstart < $p_length
            ) {
                //get words to compare
                $passageword= $passage[$p_slength + $pstart];
                $transcriptword =$transcript[$t_slength + $tstart];
                $match=false;

                //check for a forward match
                if($forwardmatch!==false){
                    $match = self::mb_strequals($passageword, $forwardmatch);
                    //we matched a passage word + but did not use the next transcript word, so roll back t_slength
                    if($match) {
                        $t_slength--;
                    }
                }
                $forwardmatch=false;

                //check for a direct match
                if(!$match) {
                    $match =self::mb_strequals( $passageword,$transcriptword);
                }

                //if no direct match is there an alternates match
                if(!$match && $alternatives){
                    $altsearch_result = self::check_alternatives_for_match($passageword,
                        $transcriptword,
                        $alternatives);
                    if($altsearch_result->match){
                        $match= true;
                        $forwardmatch=$altsearch_result->forwardmatch;
                        $alt_positions[]=($p_slength + $pstart);
                    }
                }//end of if no direct match

                //else check for a generous match(eg for english +s and +ed we give it to them)
                if(!$match){
                    $match= self::generous_match($passageword,$transcriptword,$language);
                }

                //if we have a match and the passage and transcript each have another word, we will continue
                //(ie to try to match the next word)
                if ($match &&
                    ($t_slength + $tstart + 1) < $t_length &&
                    ($p_slength + $pstart + 1) < $p_length ) {
                    //continue building sequence
                    $p_slength++;
                    $t_slength++;

                    //We add a provisional match here. This means lots of shorter sequences added to sequences[]
                    // on the way to building the final sequence
                    //this is necessary for an unusual case where two sequences overlap
                    //at the end of one and the beginning of the other.
                    //without a provisional match, the shorter seq. will lose the election and be unselected at fetchDiffs()
                    //and the unoverlapped part will be marked unmatched
                    //this occurs with a combination of wildcards and extraneous words in transcript
                    //eg transcript: home is where the heart resides oligarchy it stomach said ...
                    //passage: home is where the heart resides Aragaki Tsutomu said ...
                    //wildcards on Aragaki and Tsutomu caused this overlap problem
                    $sequence = new \stdClass();
                    $sequence->length = $p_slength;
                    $sequence->tlength = $t_slength;
                    $sequence->tposition = $tstart;
                    $sequence->pposition = $pstart;
                    $sequence->altpositions = $alt_positions;
                    $sequences[] = $sequence;

                    //else: no match or end of transcript/passage,
                } else {
                    //if we have a match here, then its the last word of passage or transcript...
                    //we build our sequence object, store it in $sequences, and return
                    if($match){
                        $p_slength++;
                        $t_slength++;
                        $sequence = new \stdClass();
                        $sequence->length = $p_slength;
                        $sequence->tlength = $t_slength;
                        $sequence->tposition = $tstart;
                        $sequence->pposition = $pstart;
                        $sequence->altpositions = $alt_positions;
                        $sequences[] = $sequence;

                        //we bump tstart, which will end this loop
                        //and we reset our sequence lengths because the outer loop may yet continue
                        $tstart+= $t_slength;
                        $p_slength = 0;
                        $t_slength = 0;
                        $alt_positions =[];

                        //if we never even had a sequence we just move to next word in transcript
                    }elseif ($p_slength == 0) {
                        $tstart++;

                        //if we had a sequence but this is not a match, we build the sequence object, store it in $sequences,
                        //step transcript index and look for next sequence
                    } else {
                        $sequence = new \stdClass();
                        $sequence->length = $p_slength;
                        $sequence->tlength = $t_slength;
                        $sequence->tposition = $tstart;
                        $sequence->pposition = $pstart;
                        $sequence->altpositions = $alt_positions;
                        $sequences[] = $sequence;

                        //re init transcript loop variables for the next pass
                        $tstart+= $t_slength;
                        $p_slength = 0;
                        $t_slength = 0;
                        $alt_positions =[];

                    }//end of "IF slength=0"
                }//end of "IF match"
            }//end of "WHILE Transcript Index < t_length"
            //reset transcript loop variables for each pass of passageword loop
            $tstart=0;

        }//end of "FOR each passage word"

        return $sequences;
    }//end of fetchSequences

    public static function debug_print_sequence($sequence,$passage,$transcript,$tag){
        echo '<br>';
        echo 'THE SEQUENCE: ' . $tag;
        echo '<br>';
        print_r($sequence);
        $printpassage = '<br>PASSAGE: ';
        $printtranscript = '<br>TRANSCRIPT: ';
        for($word=0;$word<$sequence->length;$word++){
            $printpassage  .= ($word . ':' . $passage[$word + $sequence->pposition] . ' ');
            $printtranscript .= ($word . ':' . $transcript[$word + $sequence->tposition] . ' ');
        }
        echo $printpassage;
        echo $printtranscript;
    }

    /*
     * This will run through the list of alternatives for a given passageword
     */
    public static function check_alternatives_for_match($passageword,$transcriptword,$alternatives){
        $ret= new \stdClass();
        $ret->match =false;
        $ret->matchlength=0;
        $ret->forwardmatch=false;

        //loop through all alternatives
        //and then through each alternative->wordset
        foreach($alternatives as $alternateset){
            $wordset=$alternateset[0];
            $forwardmatches=$alternateset[1];
            if(self::mb_strequals($wordset[0],$passageword)){
                for($setindex =1;$setindex<count($wordset);$setindex++) {
                    //we no longer process wildcards while matching (we just reverse errors later)
                    //if ($wordset[$setindex] == $transcriptword || $wordset[$setindex] == '*') {
                    if (self::mb_strequals($wordset[$setindex], $transcriptword)) {
                        $ret->match = true;
                        $ret->matchlength = 1;
                        if(array_key_exists($wordset[$setindex],$forwardmatches)){
                            $ret->forwardmatch=$forwardmatches[$wordset[$setindex]];
                        }
                        break;
                    }
                }
            }//end of if alternatesset[0]
            if($ret->match){break;}
        }//end of for each alternatives
        //we return the matchlength
        return $ret;
    }

    /*
     * This will run through the alternatives and compile the wildcard words
     * We put the passageword as array key , so later we can search for it by array_key_exists .. uurrgh
     */
    public static function fetchWildcardsArray($alternatives){
        $wildcards=array();

        //loop through all alternatives
        //and then through each alternative->wordset
        foreach($alternatives as $alternateset){
            $wordset=$alternateset[0];
            $passageword = $wordset[0];
            for($setindex =1;$setindex<count($wordset);$setindex++) {
                if ($wordset[$setindex] == '*') {
                    $wildcards[$passageword]=true;
                    break;
                }
            }//end of for setindex
        }//end of for each alternatives
        //we return the wildczrds
        return $wildcards;
    }

    public static function mb_strequals($str1, $str2, $encoding = null) {
        return ($str1==$str2);
        /*
        if (null === $encoding) { $encoding = mb_internal_encoding(); }
        if (strcmp(mb_strtoupper($str1, $encoding), mb_strtoupper($str2, $encoding))==0) {
            return true;
        }else{
            return false;
        }
        */
    }

    //for use with PHP usort and arrays of sequences
    //sort array so that long sequences come first.
    //if sequences are of equal length, the one whose transcript index is earlier comes first
    public static function cmp($a, $b)
    {
        if ($a->length == $b->length) {
            if($a->tposition == $b->tposition){
                return 0;
            }else{
                return ($a->tposition< $b->tposition) ? -1 : 1;
            }
        }
        return ($a->length < $b->length) ? 1 : -1;
    }

    //returns an array of "diff" results, one for each word(ie position) in passage
    //i) default all passage positions to unmatched (self::UNMATCHED)
    //ii) sort sequences by length(longer sorts higher), transcript position (earlier sorts higher)
    //iii) for each sequence
    //   a)- check passage match in sequence was not already matched by previous sequence (bust if so)
    //   b)- check transcript match in sequence[tpos -> tpos+length] was not already allocated to another part of passage in previous sequence
    //   c)- check passage match position and transcript position are consistent with previous sequences
    //     inconsistent example: If T position 3 was matched to P position 5, T position 4 could not match with P position 2
    //iv) we do various adhoc checks based on common problems we find in the wild
    //
    //NB aborted supporting "multiple word alternatives" at this point. We know the sequence length in transcript
    //but we can not add a valid tposition for a pposition in the final diff array when the pposition occurs
    // after an alternate match in the same sequence. At that point gave up ... for now. Justin 2018/08
    public static function fetchDiffs($sequences, $passagelength,$transcriptlength, $debug=false){
        //i) default passage positions to unmatched and transcript position -1
        $diffs=array_fill(0, $passagelength, [self::UNMATCHED,-1,self::NOTALTERNATEMATCH]);

        //ii) sort sequences by length, transcript posn
        //long sequences sort higher, and are placed in the diff array first
        usort($sequences, array('\\' . constants::M_COMP . '\diff','cmp'));

        //record prior sequences for iii)
        $priorsequences=array();
        $sequenceindex=0;
        //iii) loop through sequences
        foreach($sequences as $sequence){
            $bust=false;
            $sequenceindex++;

            //iii) a) check passage position not already matched
            //test with these sequences which should both match and not overlap
            //A seq pposition=63 length=18
            //B seq pposition=81 length=42
            //remember that pposition is 0 based and so pposition=0 and length 1, is char 1 only
            for($p=$sequence->pposition; $p < $sequence->pposition + $sequence->length; $p++){
                if($diffs[$p][0] !=self::UNMATCHED){
                    $bust=true;
                    break;
                }
            }
            if(!$bust){
                foreach($priorsequences as $priorsequence){
                    //iii) b) check transcript match was not matched elsewhere in passage
                    if($sequence->tposition >= $priorsequence->tposition &&
                        $sequence->tposition < $priorsequence->tposition + $priorsequence->length){
                        $bust=true;
                        break;
                    }
                    //iii) c) check passsage match and transcript match positions are consistent with prev. sequences
                    if($sequence->tposition <= $priorsequence->tposition &&
                        $sequence->pposition >= $priorsequence->pposition){
                        $bust=true;
                        break;
                    }
                    if($sequence->tposition >= $priorsequence->tposition &&
                        $sequence->pposition <= $priorsequence->pposition){
                        $bust=true;
                        break;
                    }
                }
            }

            //we do a fuzzy check for various anomalies that can occur
            if(!$bust){
                //distance from passage location to transcript location
                $matchdistance =$sequence->pposition - $sequence->tposition;

                //distance between passage location and transcript length
                $enddistance =$sequence->pposition - $transcriptlength;

                //ratio of alternates to full matches
                $altcount = count($sequence->altpositions);
                if($altcount) {
                    $altratio = $sequence->length / $altcount;
                }else{
                    $altratio=0;
                }

                //common is short matches after speaking ends
                //particularly dangerous are wildcards and alternates
                if(($altratio >= 0.5) && $enddistance > 0){
                    $bust=true;
                }elseif($sequence->length < $enddistance){
                    $bust=true;
                }
            }

            if($bust){continue;}

            //record sequence as :
            //i) matched and
            //ii) record transcript position so we can play it back.
            //Then store sequence in prior sequences
            for($p=$sequence->pposition; $p < $sequence->pposition + $sequence->length; $p++){
                //word position in sequence ( 0 = first )
                $wordposition = $p - $sequence->pposition;
                //NB pposition starts from 1. We adjust tposition to match
                $tposition = $sequence->tposition + $wordposition + 1;
                //was this an alternatives match?
                if(in_array($p,$sequence->altpositions)){
                    $altmatch=self::ALTERNATEMATCH;
                }else{
                    $altmatch=self::NOTALTERNATEMATCH;
                }

                $diffs[$p]=[self::MATCHED,$tposition,$altmatch];
            }
            $priorsequences[] = $sequence;
        }

        //we are debugging return an array with some data we can look at
        if($debug){
            return [$diffs,$priorsequences];
        }else{
            return $diffs;
        }
    }

    /*
     * We apply wildcards after all is done.
     * If we do it during the sequence building it can mess things up when a wildcard
     * matches a passage word to a transcript word that should match elsewhere.
     * e.g [passage] The big green butcher
     * [transcript] The green butcher
     * [alternatives] big|*
     * In this case [transcript]green can be matched against [passage]big
     * If the sequence containing this match is selected, then "green" can be marked as missing, and hence an error
     *
     * The sequence loop may or may not select the faulty sequence. Rather than patch this up with forward matches and
     * tricks,  we now leave wildcards out of sequence building and just patch up the diffs array here
     *
     * The same situation might occur with alternatives too, but the missed word is likely similar to the matched word
     * e.g "The artists are this close to us." so we can accept it.
     */
    public static function applyWildcards($diffs,$passagebits,$wildcards){
        $last_tposition=1;
        $last_p=0;

        //we do not want to go more than one beyond the last true matched passage word
        //here we find the last passage match
        for($p=count($diffs)-1;$p>=0;$p--){
            if($diffs[$p][0]==self::MATCHED){
                $last_p=$p;
                break;
            }
        }
        //If there is another passage word after that, it becomes the last possible wildcard match
        if($last_p + 1<count($diffs)){
            $last_p = $last_p + 1;
        }

        //loop through to last acceptable passage word looking for wildcards
        for($p=0;$p<=$last_p;$p++){
            if($diffs[$p][0]==self::UNMATCHED && array_key_exists($passagebits[$p],$wildcards)){
                $diffs[$p]=[self::MATCHED,$last_tposition,self::ALTERNATEMATCH];
            }else if($diffs[$p][0]==self::MATCHED){
                $last_tposition=$diffs[$p][1];
            }
        }
        return $diffs;
    }
}

?>
