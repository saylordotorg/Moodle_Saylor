<?php
/**
 * Test for the String Stream Controller
 * 
 * 
 * PHP/5
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
echo "<pre>"; 
$mystring = "Joe is good";
$ref = '';
echo "My String: $mystring\n";
echo "Ref: $ref\n";
echo "\n\n";

StringStreamController::createRef('mystring',$mystring);
$ref =& StringStreamController::getRef('mystring');
echo "My String: $mystring\n";
echo "Ref: $ref\n";
echo "\n\n";

$mystring = "Pie";
echo "My String: $mystring\n";
echo "Ref: $ref\n";
echo "\n\n";