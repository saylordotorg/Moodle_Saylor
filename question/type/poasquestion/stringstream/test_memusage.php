<?php
/**
 * Memory Usage Test
 * 
 * This tests memory usage of a sample file when used
 * against the "data" built in stream.  
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

$last = memory_get_usage();
echo 'Base: '.$last .'<br />';
$data = file_get_contents('test.csv') or die('failed to open data');
$diff = memory_get_usage() - $last;
$last = memory_get_usage();
echo 'File load: '. $last .'/'. $diff .'<br />';
StringStreamController::createRef('csv',$data);
$diff = memory_get_usage() - $last;
$last = memory_get_usage();
echo 'Reference: '. $last .'/'. $diff .'<br />';
$fdata2 = fopen('string://csv','r') or die('failed to open data2');
$diff = memory_get_usage() - $last;
$last = memory_get_usage();
echo 'String open: '. $last .'/'. $diff .'<br />';
fclose($fdata2);
$diff = memory_get_usage() - $last;
$last = memory_get_usage();
echo 'String close: '. $last .'/'. $diff .'<br />';

$fdata3 = fopen('data://text/plain,'. $data,'r');
$diff = memory_get_usage() - $last;
$last = memory_get_usage();
echo 'Data open: '. $last .'/'. $diff .'<br />';
fclose($fdata3);
$diff = memory_get_usage() - $last;
$last = memory_get_usage();
echo 'Data close: '. $last .'/'. $diff .'<br />';