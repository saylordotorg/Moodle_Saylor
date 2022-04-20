<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 6/4/21
 * Time: 14:24
 */

namespace mod_readaloud;

/**
 * alphabet file safe converter class for Poodll Readaloud
 *
 * The KenLM generated scorers work on files of acceptable characters(alphabet.txt).
 *  Digits and German Eszett (ß) are commonly in passages, but not in alphabet.txt files
 *  So here we perform simple conversionts to ensure this does not trip up matching transcript <--> passage
 *
 * @package    mod_readaloud
 * @copyright  2021 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class alphabetconverter {

    //Russian
    //---------------------------------------------------------------------------------------
    static function numberToWords_ru($num) {
        $num = (int)$num;
        $nul='ноль';
        $ten=array(
            array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
            array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
        );
        $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
        $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
        $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
        $unit=array( // Units
            array('коп.','коп.','коп.',	 1),
            array('руб.','руб.','руб.',	 0),
            array('тыс.','тыс.','тыс.',	 1),
            array('млн.','млн.','млн.',	 0),
            array('млрд.','млрд.','млрд.',	 0),
            array('trilyon','trilyon','trilyon',	 0),
        );
        //
        list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
        $out = array();
        if (intval($rub)>0) {
            foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
                if (!intval($v)) continue;
                $uk = sizeof($unit)-$uk-1; // unit key
                $gender = $unit[$uk][3];
                list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
                else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                // units without rub & kop
                if ($uk>1) $out[]= self::ru_morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
            } //foreach
        }
        else $out[] = $nul;
        $out[] = self::ru_morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
        $out[] = $kop.' '.self::ru_morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
        return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
    }

    static function ru_morph($n, $f1, $f2, $f5) {
        $n = abs(intval($n)) % 100;
        if ($n>10 && $n<20) return $f5;
        if ($n%10>1 && $n%10<5) return $f2;
        if ($n%10==1) return $f1;
        return $f5;
    }

    //Finnish
    //---------------------------------------------------------------------------------------
    static function numberToWords_fi($number) {
        $number = (int)$number;
        $ones = array("", "yksi", "kaksi", "kolme", "neljä", "viisi", "kuusi",
            "seitsemän", "kahdeksan", "yhdeksän");
        $tens = array("", "kymmenen", "kaksikymmentä", "kolmekymmentä",
            "neljäkymmentä", "viisikymmentä", "kuusikymmentä", "seitsemänkymmentä",
            "kahdeksankymmentä", "yhdeksänkymmentä");
        $triplets = array("", "sata", "tuhat");

        // split the number into digits
        $digits = str_split(strrev((string) $number), 1);

        // find out how many digits we have
        $num_digits = count($digits);

        // make sure the number is not bigger than 999 or less than 1
        if ($num_digits > 3 || $number < 1) {
            return false;
        }

        // loop through each digit, starting from the right
        $words = array();
        for ($i = 0; $i < $num_digits; $i++) {

            // if this is the last digit, we need to multiply by 10^i
            if ($i == $num_digits - 1) {
                $multiplier = pow(10, $i);

                // otherwise we need to multiply by 10^(i+1)
            } else {
                $multiplier = pow(10, $i + 1);
            }

            // look up the index in the ones, tens, and triplets arrays
            $index = (int) ($number / $multiplier) % 10;

            // if this is not the last digit, we need to multiply by 10^i
            if ($i == 2) {

                // we can't say something like 'sata kaksisataa', so we need to add 'toista' in front of 2nd triplet
                if ($index == 1) {
                    $words[] = 'toista';

                    // otherwise we just need to look up the triplet index directly and add 'sata' in front of it
                } else {
                    $words[] = $triplets[$index];
                }

                // finally, we need to add 'sata' after the triplet index (if it's not zero that is)
                if ($index != 0) {
                    $words[] = 'sata';
                }

                // otherwise just look up the index directly and add it to the array of words
            } else {
                $words[] = $ones[$index];

                // if this is a tens digit, we need to add the appropriate tens word after it as well (if it's not zero that is)
                if ($index != 0 && $i == 1) {
                    $words[] = $tens[$index];
                }

                // if this is a hundreds digit, we need to add 'sata' after it as well (if it's not zero that is)
                if ($index != 0 && $i == 0) {
                    $words[] = 'sata';
                }
            }

        }

        // now that we have all the words, we need to reverse them and return the string
        return implode(' ', array_reverse($words));
    }


    //Polish
    //---------------------------------------------------------------------------------------
    static function numberToWords_pl($number) {
        $number = (int)$number;
        $words = array();
        $words[0] = 'zero';
        $words[1] = 'jeden';
        $words[2] = 'dwa';
        $words[3] = 'trzy';
        $words[4] = 'cztery';
        $words[5] = 'pięć';
        $words[6] = 'sześć';
        $words[7] = 'siedem';
        $words[8] = 'osiem';
        $words[9] = 'dziewięć';
        $words[10] = 'dziesięć';
        $words[11] = 'jedenaście';
        $words[12] = 'dwanaście';
        $words[13] = 'trzynaście';
        $words[14] = 'czternaście';
        $words[15] = 'piętnaście';
        $words[16] = 'szesnaście';
        $words[17] = 'siedemnaście';
        $words[18] = 'osiemnaście';
        $words[19] = 'dziewiętnaście';
        $words[20] = 'dwadzieścia';
        $words[30] = 'trzydzieści';
        $words[40] = 'czterdzieści';
        $words[50] = 'pięćdziesiąt';
        $words[60] = 'sześćdziesiąt';
        $words[70] = 'siedemdziesiąt';
        $words[80] = 'osiemdziesiąt';
        $words[90] = 'dziewięćdziesiąt';
        $words['hundred'] = 'sto';
        $words['thousand'] = 'tysiąc';

        if ($number == 0) {
            return $words[0];
        } else if ($number == 1000) {
            return "tysiąc";
        } else if ($number < 21) {
            return $words[$number];
        }else if(array_key_exists($number,$words)){
            return $words[$number];
        } else {
            return self::numberToWords_pl(intval($number / 100)) . " " .  $words['hundred'] . " " . self::numberToWords_pl($number % 100);
        }
    }

    //French
    //---------------------------------------------------------------------------------------
    static function numberToWords_fr($number) {
        $number = (int)$number;
        $frenchNumbers = array(
            1 => 'un', 2 => 'deux', 3 => 'trois', 4 => 'quatre', 5 => 'cinq', 6 => 'six', 7 => 'sept', 8 => 'huit', 9 => 'neuf', 10 => 'dix', 11 => 'onze', 12 => 'douze', 13 => 'treize', 14 => 'quatorze', 15 => 'quinze', 16 => 'seize', 17 => 'dix-sept', 18 => 'dix-huit', 19 => 'dix-neuf', 20 => 'vingt', 30 => 'trente', 40 => 'quarante', 50 => 'cinquante', 60 => 'soixante', 70 => 'soixante-dix', 80 => 'quatre-vingts', 90 => 'quatre-vingt-dix'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 1 || (int) $number > 1000) {
            return false;
        }

        if ($number < 21) {
            return $frenchNumbers[$number];
        } elseif ($number < 100) {
            if ($number % 10 === 0) {
                return $frenchNumbers[$number];
            } else {
                return $frenchNumbers[(int) ($number / 10) * 10] . '-' . $frenchNumbers[$number % 10];
            }
        } elseif ($number < 1000) {
            if ($number % 100 === 0) {
                return $frenchNumbers[(int) ($number / 100)] . ' cents';
            } elseif ($number % 100 < 21) {
                return $frenchNumbers[(int) ($number / 100)] . ' cent-' . $frenchNumbers[$number % 100];
            } else {
                return $frenchNumbers[(int) ($number / 100)] . ' cent-' . self::numberToWords_fr($number % 100);
            }
        } elseif ($number === 1000) {
            return "mille";
        }

        return false;
    }
    //Italian
    //---------------------------------------------------------------------------------------
    static function numberToWords_it($number)
    {
        $number = (int)$number;
        $words = array(
            'zero',
            'uno',
            'due',
            'tre',
            'quattro',
            'cinque',
            'sei',
            'sette',
            'otto',
            'nove',
            'dieci',
            'undici',
            'dodici',
            'tredici',
            'quattordici',
            'quindici',
            'sedici',
            'diciassette',
            'diciotto',
            'diciannove',
            'venti',
            30 => 'trenta',
            40 => 'quaranta',
            50 => 'cinquanta',
            60 => 'sessanta',
            70 => 'settanta',
            80 => 'ottanta',
            90 => 'novanta'
        );

        if (!is_numeric($number) || $number < 0 || $number > 1000) {
            return false;
        }

        if ($number < 20) {
            return $words[$number];

        } elseif ($number < 100) {

            $tens = ((int)($number / 10)) * 10;

            return $words[$tens] . '-' . $words[$number % 10];

        } else {

            $hundreds = ((int)($number / 100));

            return $words[$hundreds] . ' cento' . (($hundreds == 1) ? '' : '') . self::numberToWords_it($number % 100);

        }

        return false; // default return value - change it according to your needs.
    }

    //Portuguese
    //---------------------------------------------------------------------------------------
    static function numberToWords_pt($number) {
        $number = (int)$number;
        $dictionary  = array(1 =>'um', 2 =>'dois', 3 =>'três', 4 =>'quatro', 5 =>'cinco', 6 =>'seis', 7 =>'sete', 8 =>'oito', 9 =>'nove', 10 =>'dez',
            11 => 'onze', 12 => 'doze', 13 => 'treze', 14 => 'quatorze', 15 => 'quinze', 16 => 'dezesseis', 17 => 'dezessete', 18 => 'dezoito', 19 => 'dezenove',
            20 => 'vinte', 30 => 'trinta', 40 => 'quarenta', 50 => 'cinquenta', 60 => 'sessenta', 70 => 'setenta', 80 => 'oitenta', 90 => 'noventa', 100 => 'cem');

        if (!is_numeric($number) || $number < 1 || $number > 999) {
            return false;
        }

        if ($number <= 20) {
            return $dictionary[$number];
        } elseif ($number < 100) {
            return $dictionary[10 * floor($number/10)] . (($number % 10 != 0) ? ' e ' : '') . $dictionary[$number % 10];
        } else {
            return $dictionary[floor($number / 100)] . (($number % 100 != 0) ? ' e ' : '') . self::numberToWords_pt($number % 100);
        }
    }

    //Spanish
    //---------------------------------------------------------------------------------------
    static function numberToWords_es($numero) {
        $numero = (int)$numero;
        $matriz = array(1 => 'uno', 2 => 'dos', 3 => 'tres', 4 => 'cuatro', 5 => 'cinco', 6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve', 10 => 'diez', 11 => 'once', 12 => 'doce', 13 => 'trece', 14 => 'catorce', 15 => 'quince', 20 => 'veinte', 30 => 'treinta', 40 => 'cuarenta', 50 => 'cincuenta', 60 => 'sesenta', 70 => 'setenta', 80 => 'ochenta', 90 => 'noventa');
        if ($numero <= 15) {
            return $matriz[$numero];
        } elseif ($numero < 20) {
            return $matriz[15] . self::numberToWords_es($numero - 15);
        } elseif ($numero < 30) {
            return $matriz[20] . self::numberToWords_es($numero - 20);
        } elseif ($numero < 40) {
            return $matriz[30] . self::numberToWords_es($numero - 30);
        } elseif ($numero < 50) {
            return $matriz[40] . self::numberToWords_es($numero - 40);
        } elseif ($numero < 60) {
            return $matriz[50] . self::numberToWords_es($numero - 50);
        } elseif ($numero < 70) {
            return $matriz[60] . self::numberToWords_es($numero - 60);
        } elseif ($numero < 80) {
            return $matriz[70] . self::numberToWords_es($numero - 70);
        } elseif ($numero < 90) {
            return $matriz[80] . self::numberToWords_es($numero - 80);
        } elseif ($numero < 100) {
            return $matriz[90] . self::numberToWords_es($numero - 90);
        } elseif ($numero == 100) {
            return "cien";
        } elseif ($numero < 200) {
            return "ciento " . self::numberToWords_es($numero - 100);
        } elseif ($numero < 300) {
            return "doscientos " . self::numberToWords_es($numero - 200);
        } elseif ($numero < 400) {
            return "trescientos " . self::numberToWords_es($numero - 300);
        } elseif ($numero < 500) {
            return "cuatrocientos " . self::numberToWords_es($numero - 400);
        } elseif ($numero < 600) {
            return "quinientos " . self::numberToWords_es($numero - 500);
        } elseif ($numero < 700) {
            return "seiscientos " . self::numberToWords_es($numero - 600);
        } elseif ($numero < 800) {
            return "setecientos " . self::numberToWords_es($numero - 700);
        } elseif ($numero < 900) {
            return "ochocientos " . self::numberToWords_es($numero - 800);
        } elseif ($numero < 1000) {
            return "novecientos " . self::numberToWords_es($numero - 900);

        } elseif ($numero == 1000) {
            return "mil";
        } else {
            return false;
        }
    }



    //Basque
    //---------------------------------------------------------------------------------------
    static function numberToWords_eu($number) {
        $number = (int)$number;
        $dictionary  = array(0 => 'zero', 1 => 'bat', 2 => 'bi', 3 => 'hiru', 4 => 'lau', 5 => 'bost', 6 => 'sei', 7 => 'zazpi', 8 => 'zortzi', 9 => 'bederatzi', 10 => 'hamar', 11 => 'hamaika', 12 => 'hamabi', 20 => 'hogei', 30 => 'berrogei', 40 => 'laurogei', 50 => 'bostogei', 60 => 'seixogei', 70 => 'zazpirogei', 80 => 'zortzigarogei', 90 => 'bederatzigarogei', 100 => 'ekin');

        if($number == 0) {
            return $dictionary[$number];
        } else if($number < 13) {
            return $dictionary[$number];
        } else if($number < 20) {
            return $dictionary[10] . " " . $dictionary[$number - 10];
        } else if($number < 100) {
            return $dictionary[$number - ($number % 10)] . " " . $dictionary[$number % 10];
        } else if($number < 1000) {
            return $dictionary[$number / 100] . " " . $dictionary[100] . " " . self::numberToWords_eu($number % 100);
        } else {
            return false;
        }
    }

    //Ukranian
    //---------------------------------------------------------------------------------------
    static function numberToWords_uk($number){
        $numbers = ["1" => "один", 2 => "два", "3" => "три", "4" => "чотири", "5" => "п’ять",
            "6" => "шість", "7" => "сім", "8" => "вісім", "9" => "дев’ять", "10" => "десять", "11" => "одинадцять",
            "12" => "дванадцять", "13" => "тринадцять", "14" => "чотирнадцять", "15" => "п’ятнадцять", "16" => "шістнадцять",
            "17" => "сімнадцять", "18" => "вісімнадцять", "19" => "дев’ятнадцять", "20" => "двадцять", "30" => "тридцять", "40" => "сорок",
            "50" => "п’ятдесят", "60" => "шістдесят", "70" => "сімдесят",
            "80" => "вісімдесят", "90" => "дев’яносто", "100" => "сто"];
        if(array_key_exists($number,$numbers)){
            return $numbers[$number];
        }else{
            return false;
        }
    }

    //German
    //---------------------------------------------------------------------------------------
    static function numberToWords_de($number){
        $numbers = ["1"=>"eins","2"=>"zwei","3"=>"drei","4"=>"vier","5"=>"fünf","6"=>"sechs","7"=>"sieben","8"=>"acht","9"=>"neun","10"=>"zehn","11"=>"elf","12"=>"zwölf","13"=>"dreizehn","14"=>"vierzehn","15"=>"fünfzehn","16"=>"sechzehn","17"=>"siebzehn","18"=>"achtzehn","19"=>"neunzehn","20"=>"zwanzig","21"=>"einundzwanzig","22"=>"zweiundzwanzig","23"=>"dreiundzwanzig","24"=>"vierundzwanzig","25"=>"fünfundzwanzig","26"=>"sechsundzwanzig","27"=>"siebenundzwanzig","28"=>"achtundzwanzig","29"=>"neunundzwanzig","30"=>"dreissig","31"=> "einunddreissig", "32" => "zweiunddreißig", "33" => "dreiunddreißig", "34" => "vierunddreißig", "35" => "fünfunddreißig", "36" => "sechsunddreißig", "37" => "siebenunddreißig", "38" => "achtunddreißig", "39" => "neununddreißig", "40" => "vierzig", "41" => "einundvierzig", "42" => "zweiundvierzig", "43" => "dreiundvierzig", "44" => "vierundvierzig", "45" => "fünfundvierzig", "46" => "sechsundvierzig", "47" => "siebenundvierzig", "48" => "achtundvierzig", "49" => "neunundvierzig", "50" => "fünfzig",
            "51"=>"einundfünfzig","52"=>"zweiundfünfzig","53"=>"dreiundfünfzig","54"=>"vierundfünfzig","55"=>"fünfundfünfzig","56"=>"sechsundfünfzig","57"=>"siebenundfünfzig","58"=>"achtundfünfzig","59"=>"neunundfünfzig","60"=>"sechzig","61"=> "einundsechzig", "62" => "zweiundsechzig", "63" => "dreiundsechzig", "64" => "vierundsechzig", "65" => "fünfundsechzig", "66" => "sechsundsechzig", "67" => "siebenundsechzig", "68" => "achtundsechzig", "69" => "neunundsechzig", "70" => "siebzig", "71" => "einundsiebzig", "72" => "zweiundsiebzig", "73" => "dreiundsiebzig", "74" => "vierundsiebzig", "75" => "fünfundsiebzig", "76" => "sechsundsiebzig", "77" => "siebenundsiebzig", "78" => "achtundsiebzig", "79" => "neunundsiebzig",
            "80"=>"achtzig","81"=>"einundachtzig","82"=>"zweiundachtzig","83"=>"dreiundachtzig","84"=>"vierundachtzig","85"=>"fünfundachtzig","86"=>"sechsundachtzig","87"=>"siebenundachtzig","88"=>"achtundachtzig","89"=>"neunundachtzig","90"=>"neunzig","91"=> "einundneunzig",
            "92" => "zweiundneunzig", "93" => "dreiundneunzig", "94" => "vierundneunzig", "95" => "fünfundneunzig", "96" => "sechsundneunzig", "97" => "siebenundneunzig", "98" => "achtundneunzig", "99" => "neunundneunzig", "100" => "ein hundert"];
        if(array_key_exists($number,$numbers)){
            return $numbers[$number];
        }else{
            return false;
        }
    }

    /*
    * This converts any eszetts in the passage, if found in the target, to ss
    *
    *
    * @param string $passage the passage text
    * @param string $target the text to run the conversion on
    * @return string the converted text
    */
    public static function eszett_to_ss_convert($passage,$targettext){
        $passagewords=self::fetchWordArray($passage);
        $conversions = self::fetch_eszett_conversions($passagewords);

        foreach($conversions as $conversion){
            $targettext = str_replace($conversion['eszetts'],$conversion['sss'],$targettext);
        }
        return $targettext;
    }

    /*
    * Fetch any eszett containing words, back to eszett if its ss and in the conveersions array
    *
    * @param string $passage the passage text
    * @param string $target the text to run the conversion on
    * @return string the converted text
    *
    */
    public static function ss_to_eszett_convert($passage,$targettext){
        $passagewords=self::fetchWordArray($passage);
        $conversions = self::fetch_eszett_conversions($passagewords);

        foreach($conversions as $conversion){
            $targettext = str_replace($conversion['sss'],$conversion['eszetts'],$targettext);
        }
        return $targettext;
    }

    /*
     * Fetch array of eszett containing words, and their ss equivalents
     * @param mixed $passagewords the passage text or an array of passage words
     * @return array the eszett_word to ss_word conversions array
     */
    public static function fetch_eszett_conversions($passagewords) {

        //its possible to call this function with just the passage as text,
        // which might be useful for callers who want the conversions array to pass to JS and not to run the conversion
        if (!is_array($passagewords)) {
            $passagewords = self::fetchWordArray($passagewords);
        }

        $conversions = array();
        foreach ($passagewords as $candidate) {
            $eszett_pos =\core_text::strpos($candidate,'ß');
            if($eszett_pos!==false){
                $conversions[] = ['eszetts' => $candidate, 'sss' => str_replace('ß','ss',$candidate)];
            }
        }
        return $conversions;
    }

    /*
     * This converts any number-digits in the passage, if found in the target, to number-words
     *
     *
     * @param string $passage the passage text
     * @param string $target the text to run the conversion on
     * @return string the converted text
     */
    public static function numbers_to_words_convert($passage,$targettext,$shortlang){
        $passagewords=self::fetchWordArray($passage);
        $conversions = self::fetch_number_conversions($passagewords,$shortlang);

        foreach($conversions as $conversion){
            //english returns an array of conversion words for varieties eg 2015 two thousand fifteen, twenty fifteen
            if(is_array($conversion['words'])) {
                foreach ($conversion['words'] as $convset) {
                    $targettext = str_replace($convset['digits'], $convset['words'], $targettext);
                }
            }else{
                $targettext = str_replace($conversion['digits'], $conversion['words'], $targettext);
            }

        }
        return $targettext;
    }

    /*
     * This converts any number-words in the passage to number-digits,
     *
     * @param string $passage the passage text
     * @param string $target the text to run the conversion on
     * @return string the converted text
     *
     */
    public static function words_to_numbers_convert($passage,$targettext,$shortlang){
        $passagewords=self::fetchWordArray($passage);
        $conversions = self::fetch_number_conversions($passagewords,$shortlang);

        foreach($conversions as $conversion){

            //english returns an array of conversion words for varieties eg 2015 two thousand fifteen, twenty fifteen
            if(is_array($conversion['words'])) {
                foreach ($conversion['words'] as $digits => $words) {
                    $targettext = str_replace($words['words'], $words['digits'], $targettext);
                }
            }else{
                $targettext = str_replace($conversion['words'],$conversion['digits'],$targettext);
            }
        }
        return $targettext;
    }

    /*
   * This fetches an array of digits and word number equivalents
     *
   * @param mixed $passagewords the passage text or an array of passage words
   * @return array the digit to word conversions array
   */
    public static function fetch_number_conversions($passagewords,$shortlang)
    {

        //its possible to call this function with just the passage as text,
        // which might be useful for callers who want the conversions array to pass to JS and not to run the conversion
        if (!is_array($passagewords)) {
            $passagewords = self::fetchWordArray($passagewords);
        }

        $conversions =[];
        foreach ($passagewords as $candidate) {
            //plain numbers
            $numberwords=false;
            if (is_numeric($candidate)) {
                switch($shortlang) {
                    case 'en':
                        $numberwords = self::numberToWords_en($candidate);
                        break;
                    case 'uk':
                        $numberwords = self::numberToWords_uk($candidate);
                        break;
                    case 'es':
                        $numberwords = self::numberToWords_es($candidate);
                        break;
                    case 'de':
                        $numberwords = self::numberToWords_de($candidate);
                        break;
                    case 'fr':
                        $numberwords = self::numberToWords_fr($candidate);
                        break;
                    case 'pt':
                        $numberwords = self::numberToWords_pt($candidate);
                        break;
                    case 'pl':
                        $numberwords = self::numberToWords_pl($candidate);
                        break;
                    case 'eu':
                        $numberwords = self::numberToWords_eu($candidate);
                        break;
                    case 'fi':
                        $numberwords = self::numberToWords_fi($candidate);
                        break;
                    case 'ru':
                        $numberwords = self::numberToWords_ru($candidate);
                        break;
                    case 'it':
                        $numberwords = self::numberToWords_it($candidate);
                        break;
                }
                if($numberwords!==false){
                    $conversions[] =['digits'=>$candidate,'words'=>$numberwords];
                }
            }//end of is numeric
        }//end of passagewords loop
        return $conversions;
    }

    /*
     * This is just rule based heuristics, keep adding rules when you need 'em
     * @param mixed $passagewords the passage text or an array of passage words
     * @return array the digit to word conversions array
     */
    public static function numberToWords_en($passagewords){

        //its possible to call this function with just the passage as text,
        // which might be useful for callers who want the conversions array to pass to JS and not to run the conversion
        if(!is_array($passagewords)){
            $passagewords=self::fetchWordArray($passagewords);
        }

        $conversions=array();
        foreach ($passagewords as $candidate){

            //plain numbers
            if(is_numeric($candidate)){
                //get years
                $yearwords = self::convert_years_to_words($candidate);
                if($yearwords){$conversions[] = ['digits'=>$candidate,'words'=>$yearwords];}
                //get regular numerals
                $numberwords = self::convert_numbers_to_words($candidate);
                if($numberwords){
                    $conversions[] = ['digits'=>$candidate,'words'=>$numberwords];
                    //lets also save a version without 'and'
                    $no_and_numberwords = str_replace(' and ', ' ',  $numberwords);
                    $conversions[] = ['digits'=>$candidate,'words'=>$no_and_numberwords];
                }

            //dollar numbers [currently $ is stripped before we get here. sorry. no currencies]
            }elseif(\core_text::strpos($candidate,'$')===0 && \core_text::strlen($candidate)>1){
                if($candidate=='$1'){
                    $conversions[] = ['digits'=>$candidate,'words'=>'one dollar'];
                }else{
                    $afterdollarbit = \core_text::substr($candidate,1);
                    if(is_numeric($afterdollarbit)) {
                        $numberwords = self::convert_numbers_to_words(\core_text::substr($candidate, 1));
                        if($numberwords) {$conversions[] = ['digits' => $candidate, 'words' => $numberwords . ' dollars'];};
                    }
                }

            //eras/decades
            }else{
                $startbit = \core_text::substr($candidate,0,\core_text::strlen($candidate)-1);
                if(is_numeric($startbit) && $startbit .'s' == $candidate){
                    $erawords=false;
                    switch(\core_text::strlen($candidate)){
                        case 3:
                            $erawords = self::get_era_word((int)$startbit);
                            break;
                        case 5:
                            $isera =true;
                            $erawords =  self::convert_years_to_words($startbit, $isera);

                            break;
                        default:
                    }
                    if($erawords){$conversions[] = ['digits'=>$candidate,'words'=>$erawords];}
                }
            }
        }
        return $conversions;
    }

    /*
     * Years are wordi'fied differently to normal 4 digit numbers, e.g 2020 = "twenty twenty" not "two thousand and twenty"
     * Eras are common in passages e.g "during the 1860s women were not free to...."
     */
    public static function convert_years_to_words($num=false,$isera=false){
        $num = str_replace(array(',', ' '), '' , trim($num));
        if(! $num) {
            return false;
        }
        $num = (int) $num;
        //if it does not look like a "year" and with year'y word pattern, just pass it back
        if($num <1000 || $num >2999){
            return false;
        }
        $century = $num / 100;
        $centuryword =  self::convert_numbers_to_words($century);

        $remainder = $num % 100;
        switch($remainder){
            case 0:
                //mess around a little with millennial years
                if($century ==10){$centuryword ='one'; $remainderword='thousand';}
                elseif($century ==20){$centuryword ='two'; $remainderword='thousand';}
                elseif($isera){$remainderword = "hundreds";}
                else{$remainderword = "hundred";}
                break;
            case 1: $remainderword = "oh one"; break;
            case 2: $remainderword = "oh two"; break;
            case 3: $remainderword = "oh three"; break;
            case 4: $remainderword = "oh four"; break;
            case 5: $remainderword = "oh five"; break;
            case 6: $remainderword = "oh six"; break;
            case 7: $remainderword = "oh seven"; break;
            case 8: $remainderword = "oh eight"; break;
            case 9: $remainderword = "oh nine"; break;
            default:
                if($isera){
                    $remainderword = self::get_era_word($remainder);
                    if($remainderword ===false){
                        return false;
                    }
                }else {
                    $remainderword = self::convert_numbers_to_words($remainder);
                }
        }
        $ret = trim($centuryword . ' ' . $remainderword);
        $ret = preg_replace('/\s+/', ' ', $ret);
        return $ret;
    }

    /*
     * Eras are simply decades really e.g "the 1920s"
     */
    public static function get_era_word($twodigitnumber){
        switch($twodigitnumber){
            case 10: $eraword ='tens'; break; //is this a thing?
            case 20: $eraword ='twenties'; break;
            case 30: $eraword  ='thirties'; break;
            case 40: $eraword ='forties'; break;
            case 50: $eraword  ='fifties'; break;
            case 60: $eraword  ='sixties'; break;
            case 70: $eraword  ='seventies'; break;
            case 80: $eraword  ='eighties'; break;
            case 90: $eraword  ='nineties'; break;
            default: $eraword = false;
        }
        return $eraword;
    }

    /*
    * The script "borrowed" from: https://stackoverflow.com/a/30299572
     * and modified to clean up extra spaces and add " and " where needed
    */
    public static function convert_numbers_to_words($num = false)
    {
        $num = str_replace(array(',', ' '), '' , trim($num));
        if(! $num) {
            return false;
        }
        $num = (int) $num;
        $words = array();
        $list1 = array('', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
                'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
        );
        $list2 = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred');
        $list3 = array('', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion',
                'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
                'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
        );
        $num_length = strlen($num);
        $levels = (int) (($num_length + 2) / 3);
        $max_length = $levels * 3;
        $num = substr('00' . $num, -$max_length);
        $num_levels = str_split($num, 3);
        for ($i = 0; $i < count($num_levels); $i++) {
            $levels--;
            $hundreds = (int) ($num_levels[$i] / 100);
            $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ' ' : '');
            $tens = (int) ($num_levels[$i] % 100);
            $singles = '';
            if ( $tens < 20 ) {
                $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '' );
            } else {
                $tens = (int)($tens / 10);
                $tens = ' ' . $list2[$tens] . ' ';
                $singles = (int) ($num_levels[$i] % 10);
                $singles = ' ' . $list1[$singles] . ' ';
            }
            $and = ($hundreds !='' && ($tens !='' || $singles!='')) ? ' and ' : '';
            $words[] = $hundreds . $and .  $tens . $singles . ( ( $levels && ( int ) ( $num_levels[$i] ) ) ? ' ' . $list3[$levels] . ' ' : '' );
        } //end for loop
        $commas = count($words);
        if ($commas > 1) {
            $commas = $commas - 1;
        }
        $ret= trim(implode(' ', $words));
        $ret = preg_replace('/\s+/', ' ', $ret);
        return $ret;
    }

    /*
    * This converts any number-words in the transcript to number-digits, by checking what the number digits are in the passage
    *
    * @param string $passage the passage text
    * @param string $target the text to run the conversion on
    * @return string the converted text
    *
    */
    public static function words_to_suji_convert($passage,$targettext){
        $passagewords=self::fetchWordArray($passage);
        $conversions = self::fetch_suji_conversions($passagewords);

        foreach($conversions as $conversion){
            $targettext = str_replace($conversion['words'],$conversion['digits'],$targettext);
        }
        return $targettext;
    }

    /*
    * This is just rule based heuristics, keep adding rules when you need 'em
    * @param mixed $passagewords the passage text or an array of passage words
    * @return array the digit to word conversions array
    */
    public static function fetch_suji_conversions($passagewords){

        //its possible to call this function with just the passage as text,
        // which might be useful for callers who want the conversions array to pass to JS and not to run the conversion
        if(!is_array($passagewords)){
            $passagewords=self::fetchWordArray($passagewords);
        }

        $conversions=array();
        foreach ($passagewords as $candidate){

            //plain numbers
            if(is_numeric($candidate)){
                //get regular numerals
                $numberwords = self::convert_suji_to_words($candidate);
                if($numberwords){
                    $conversions[] = ['digits'=>$candidate,'words'=>$numberwords];
                }
            }
        }
        return $conversions;
    }

    public static function convert_words_to_suji($words){

            $arr = array();
            $arr[1000000000000] = '兆';
            $arr[100000000] = '億';
            $arr[10000] = '万';
            $arr[1000] = '千';
            $arr[100] = '百';
            $arr[10] = '十';
            $arr[9] = '九';
            $arr[8] = '八';
            $arr[7] = '七';
            $arr[6] = '六';
            $arr[5] = '五';
            $arr[4] = '四';
            $arr[3] = '三';
            $arr[2] = '二';
            $arr[1] = '一';

            $arrayWithNumbers = mb_str_split($words);
            $suji = null;
            foreach($arrayWithNumbers as $jpKanji){
                $keyVal = array_search($jpKanji, $arr);
                if($keyVal===false){continue;}
                if( $suji== null){
                    $suji= $keyVal;
                }else{
                    if($keyVal < 10){
                        $suji = $suji + $keyVal;
                    }else{
                        $suji = $suji * $keyVal;
                    }
                }
            }
            return $suji;
    }

    public static function convert_suji_to_words($suji){

        $arr = array();
        $arr[1000000000000] = '兆';
        $arr[100000000] = '億';
        $arr[10000] = '万';
        $arr[1000] = '千';
        $arr[100] = '百';
        $arr[10] = '十';
        $arr[9] = '九';
        $arr[8] = '八';
        $arr[7] = '七';
        $arr[6] = '六';
        $arr[5] = '五';
        $arr[4] = '四';
        $arr[3] = '三';
        $arr[2] = '二';
        $arr[1] = '一';

        $word='';
        $nowsuji = $suji;
        foreach($arr as $factor=>$factorword){
            if($nowsuji > 10 && $factor > 9) {
                if(phpversion()>=7) {
                    $multiplier = intdiv($nowsuji, $factor);
                }else{
                    $multiplier = floor($nowsuji/$factor);
                }
                if ($multiplier > 0) {
                    $word .= $arr[$multiplier] . $factorword;
                    $nowsuji = $nowsuji - ($multiplier * $factor);
                }
            }else{
                if($nowsuji>0) {
                    $word .= $arr[$nowsuji];
                }
                break;
            }
        }
        return $word;
    }

    /*
   * Convenience function to remove dependency on aigrade and diff
   */

    public static function fetchWordArray($thetext) {

        //tidy up the text so its just lower case words seperated by spaces
        $thetext = self::cleanText($thetext);

        //split on spaces into words
        $textbits = explode(' ', $thetext);

        //remove any empty elements
        $textbits = array_filter($textbits, function($value) {
            return $value !== '';
        });

        //re index array because array_filter converts array to assoc. (ie could have gone from indexes 0,1,2,3,4,5 to 0,1,3,4,5)
        $textbits = array_values($textbits);

        return $textbits;
    }

    /*
     *
     * Convenience function to remove dependency on aigrade and diff
     *
   * Regexp replace with /u will return empty text if not unicodemb4
   * we only really need unicodemb4 for japanese at this stage (2020/09/17)
   * but that means we still need it. This impl is awful. There must be a better way ..
   */
    public static function isUnicodemb4($thetext) {
        //$testtext = "test text: " . "\xf8\xa1\xa1\xa1\xa1"; //this will fail for sure

        $thetext =  \core_text::strtolower($thetext);
        //strip tags is bad for non UTF-8. It might even be the real problem we need to solve here
        //this anecdotally might help: $thetext =utf8_decode($thetext);
        //anyway the unicode problems appear after to combo of strtolower and strip_tags, so we call them first
        $thetext = strip_tags($thetext);
        $testtext = "test text: " . $thetext;

        $test1 = preg_replace('/#\R+#/u', ' ', $testtext);
        if(empty($test1)){return false;}
        $test2 = preg_replace('/\r/u', ' ', $testtext);
        if(empty($test2)){return false;}
        $test3 = preg_replace('/\n/u', ' ', $testtext);
        if(empty($test3)){return false;}
        $test4 = preg_replace("/[[:punct:]]+/u", "", $testtext);
        if(empty($test4)){
            return false;
        }else{
            return true;
        }
    }

    /*
     *
     * Convenience function to remove dependency on aigrade and diff
     *
    * Clean word of things that might prevent a match
     * i) lowercase it
     * ii) remove html characters
     * iii) replace any line ends with spaces (so we can "split" later)
     * iv) remove punctuation
     *
    */
    public static function cleanText($thetext,$unicodemb4=true) {

        //first test its unicodemb4, and then get on with it
        $unicodemb4=self::isUnicodemb4($thetext);

        //lowercaseify
        $thetext = \core_text::strtolower($thetext);

        //remove any html
        $thetext = strip_tags($thetext);

        //replace all line ends with spaces
        if($unicodemb4) {
            $thetext = preg_replace('/#\R+#/u', ' ', $thetext);
            $thetext = preg_replace('/\r/u', ' ', $thetext);
            $thetext = preg_replace('/\n/u', ' ', $thetext);
        }else{
            $thetext = preg_replace('/#\R+#/', ' ', $thetext);
            $thetext = preg_replace('/\r/', ' ', $thetext);
            $thetext = preg_replace('/\n/', ' ', $thetext);
        }

        //remove punctuation. This is where we needed the unicode flag
        //see https://stackoverflow.com/questions/5233734/how-to-strip-punctuation-in-php
        // $thetext = preg_replace("#[[:punct:]]#", "", $thetext);
        //https://stackoverflow.com/questions/5689918/php-strip-punctuation
        if($unicodemb4) {
            $thetext = preg_replace("/[[:punct:]]+/u", "", $thetext);
        }else{
            $thetext = preg_replace("/[[:punct:]]+/", "", $thetext);
        }

        //remove bad chars
        $b_open = "“";
        $b_close = "”";
        $b_sopen = '‘';
        $b_sclose = '’';
        $bads = array($b_open, $b_close, $b_sopen, $b_sclose);
        foreach ($bads as $bad) {
            $thetext = str_replace($bad, '', $thetext);
        }

        //remove double spaces
        //split on spaces into words
        $textbits = explode(' ', $thetext);
        //remove any empty elements
        $textbits = array_filter($textbits, function($value) {
            return $value !== '';
        });
        $thetext = implode(' ', $textbits);
        return $thetext;
    }


}