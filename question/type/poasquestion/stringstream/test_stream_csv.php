<?php
/**
 * Stream test using CSV
 * 
 * This file tests the usage of fgetcsv against the stream.
 * fgetcsv was the main reason this was invented. 
 * 
 * PHP4/5
 *  
 * Created on Aug 7, 2008
 * 
 * @package stringstream
 * @author Sam Moffatt <sam.moffatt@toowoombarc.qld.gov.au>
 * @author Toowoomba Regional Council Information Management Branch
 * @license GNU/GPL http://www.gnu.org/licenses/gpl.html
 * @copyright 2008 Toowoomba Regional Council/Sam Moffatt 
 * @version SVN: $Id:$
 */
 
include('stringstream.php');

$data = file_get_contents('test.csv') or die('failed to open data');
StringStreamController::createRef('csv',$data);

$file = fopen('string://csv','r');
while($csv = fgetcsv($file)) {
    print_r($csv); echo '<br />';
}
fclose($file);


