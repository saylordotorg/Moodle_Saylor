<?php
/**
 * String Stream Test
 * 
 * This file tests that the string stream operators
 * appropriately with the correct values returned
 * and no corruption 
 * 
 * PHP5
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

$data2 = file_get_contents('string://csv') or die('failed to open data2');

print_r(array_diff(explode("\n", $data), explode("\n", $data2)));
