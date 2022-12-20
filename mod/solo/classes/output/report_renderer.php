<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/26
 * Time: 13:16
 */

namespace mod_solo\output;

use \mod_solo\constants;

class report_renderer extends \plugin_renderer_base
{

    public function render_reportmenu($moduleinstance, $cm)
    {

        $basic = new \single_button(
            new \moodle_url(constants::M_URL . '/reports.php',
                    array('report' => 'basic', 'id' => $cm->id, 'n' => $moduleinstance->id)),
            get_string('basicreport', constants::M_COMPONENT), 'get');

        $buttons =[];
        $attempts = new \single_button(
            new \moodle_url(constants::M_URL . '/reports.php',
                    array('report' => 'attempts', 'id' => $cm->id, 'n' => $moduleinstance->id,'format'=>'tabular')),
            get_string('attemptsreport', constants::M_COMPONENT), 'get');
        $buttons[] = $this->render($attempts);

        $detailedattempts = new \single_button(
                new \moodle_url(constants::M_URL . '/reports.php',
                        array('report' => 'detailedattempts', 'id' => $cm->id, 'n' => $moduleinstance->id,'format'=>'tabular')),
                get_string('detailedattemptsreport', constants::M_COMPONENT), 'get');
        $buttons[] = $this->render($detailedattempts);

        $classprogress = new \single_button(
                new \moodle_url(constants::M_URL . '/reports.php',
                        array('report' => 'classprogress', 'id' => $cm->id, 'n' => $moduleinstance->id,'format'=>'linechart')),
                get_string('classprogressreport', constants::M_COMPONENT), 'get');
        $buttons[] = $this->render($classprogress);

        $downloadaudio = new \single_button(
                new \moodle_url(constants::M_URL . '/reports.php',
                        array('report' => 'downloadaudio', 'id' => $cm->id, 'n' => $moduleinstance->id,'format'=>'filedownload')),
                get_string('downloadaudioreport', constants::M_COMPONENT), 'get');
        $buttons[] = $this->render($downloadaudio);
/*
        $downloadaudio = new \single_button(
                new \moodle_url(constants::M_URL . '/reports.php',
                        array('report' => 'downloadaudio', 'id' => $cm->id, 'n' => $moduleinstance->id,'format'=>'tabular')),
                get_string('downloadaudio', constants::M_COMPONENT), 'get');
        $buttons[] = $this->render($downloadaudio);
*/

        $ret = \html_writer::div(  implode("&nbsp;&nbsp;",$buttons),  constants::M_CLASS . '_listbuttons');

        return $ret;
    }

    public function render_menuinstructions(){
        $message = $this->output->heading(get_string('reportmenuinstructions', constants::M_COMPONENT), 4);
        $ret =\html_writer::div( $message,  constants::M_CLASS . '_menuinstructions');
        return $ret;
    }



    public function render_hiddenaudioplayer() {
        $audioplayer = \html_writer::tag('audio', '',
                array('src' => '', 'id' => constants::M_HIDDEN_PLAYER, 'class' => constants::M_HIDDEN_PLAYER));
        return $audioplayer;
    }

    public function render_reporttitle_html($course, $username)
    {
        $ret = $this->output->heading(format_string($course->fullname), 2);
        $ret .= $this->output->heading(get_string('reporttitle', constants::M_COMPONENT, $username), 3);
        return $ret;
    }

    public function render_empty_section_html($sectiontitle)
    {
        global $CFG;
        return $this->output->heading(get_string('nodataavailable', constants::M_COMPONENT), 3);
    }

    public function render_exportbuttons_html($cm, $formdata, $showreport, $currentformat)
    {
        $buttons = [];
        //convert formdata to array
        $formdata = (array)$formdata;
        $formdata['id'] = $cm->id;
        $formdata['report'] = $showreport;

        //CSV Button
        $formdata['format'] = 'csv';
        $excel = new \single_button(
                new \moodle_url(constants::M_URL . '/reports.php', $formdata),
                get_string('exportexcel', constants::M_COMPONENT), 'get');
        $buttons[]=$this->render($excel);

        //tabular if linechart
        if($currentformat=='linechart'){
            $formdata['format'] = 'tabular';
            $tabular = new \single_button(
                    new \moodle_url(constants::M_URL . '/reports.php', $formdata),
                    get_string('tabular', constants::M_COMPONENT), 'get');
            $buttons[]=$this->render($tabular);

        }

        return \html_writer::div(implode("&nbsp;&nbsp;",$buttons), constants::M_CLASS . '_actionbuttons');
    }

    public function make_name_safe($name){
        $dangerousCharacters = array(" ", '"', "'", "&", "/", "\\", "?", "#");
        return str_replace($dangerousCharacters, '_', $name);

    }

    public function render_file_download($sectiontitle, $report, $head, $rows, $fields)
    {
        global $CFG;

        $zipname = $this->make_name_safe($sectiontitle) . '.zip';
        $filesdata=Array();
        foreach($rows as $row){
            $file =new \stdClass();
            $name='';
            foreach($fields as $field) {
                if($field=='file'){continue;}
                if(empty($row->{$field})){continue;}
                // every forbidden character is replaced by an underscore
                $safefield = $this->make_name_safe($row->{$field});
                if(!empty($name)){$name .= '_';}
                $name .= $safefield;
            }
            $file->newname=$name;
            $filebits = explode('.', $row->file);
            $file->extension = end($filebits);
            $file->downloadurl=$row->file;
            $filesdata[]=$file;
        }

        # create new zip object
        $zip = new \ZipArchive();

        # create a temp file & open it
        $tmp_file = tempnam($CFG->tempdir , '');
        $zip->open($tmp_file, \ZipArchive::CREATE);

        # loop through each file
        foreach ($filesdata as $filedata) {
            # download file
            $download_file = file_get_contents($filedata->downloadurl);

            #add it to the zip
            $zip->addFromString($filedata->newname . '.' . $filedata->extension, $download_file);
        }

        # close zip
        $zip->close();

        # send the file to the browser as a download
        header('Content-disposition: attachment; filename="'. $zipname . '"');
        header('Content-type: application/zip');
        readfile($tmp_file);
        unlink($tmp_file);
        die;
    }

    public function render_section_csv($sectiontitle, $report, $head, $rows, $fields)
    {

        // Use the sectiontitle as the file name. Clean it and change any non-filename characters to '_'.
        $name = clean_param($sectiontitle, PARAM_FILE);
        $name = preg_replace("/[^A-Z0-9]+/i", "_", trim($name));
        $quote = '"';
        $delim = ",";//"\t";
        $newline = "\r\n";

        header("Content-Disposition: attachment; filename=$name.csv");
        header("Content-Type: text/comma-separated-values");

        //echo header
        $heading = "";
        foreach ($head as $headfield) {
            $heading .= $quote . $headfield . $quote . $delim;
        }
        echo $heading . $newline;


        //echo data rows
        $handle = fopen('php://output', 'w+');
        foreach ($rows as $row) {
            $rowarray=[];
            foreach ($fields as $field) {
                $rowarray[]= $row->{$field};
            }
            fputcsv($handle, $rowarray,$delim,$quote);
        }
        fclose($handle);
        //After file is created, die
        die();
    }

    public function render_section_html($sectiontitle, $report, $head, $rows, $fields)
    {
        global $CFG;
        if (empty($rows)) {
            return $this->render_empty_section_html($sectiontitle);
        }

        //set up our table
        $tableattributes = array('class' => 'generaltable ' . constants::M_CLASS . '_table');

        $htmltable = new \html_table();
        $tableid = \html_writer::random_id(constants::M_COMPONENT);
        $htmltable->id = $tableid;
        $htmltable->attributes = $tableattributes;

        $headcells=[];
        foreach ($head as $headcell) {
            $headcells[] = new \html_table_cell($headcell);
        }
        $htmltable->head = $head;

        //set up our table and head attributes
        /*
        $tableattributes = array('class' => 'generaltable ' . constants::M_CLASS . '_table');
        $headrow_attributes = array('class' => constants::M_CLASS . '_headrow');
        $htmltable = new \html_table();
        $htmltable->attributes = $tableattributes;



        $htr = new \html_table_row();
        $htr->attributes = $headrow_attributes;
        foreach ($head as $headcell) {
            $htr->cells[] = new \html_table_cell($headcell);
        }
        $htmltable->data[] = $htr;
          */

        foreach ($rows as $row) {
            $htr = new \html_table_row();
            //set up descrption cell
            $cells = array();
            foreach ($fields as $field) {
                $cell = new \html_table_cell($row->{$field});
                $cell->attributes = array('class' => constants::M_CLASS . '_cell_' . $report . '_' . $field);
                $htr->cells[] = $cell;
            }

            $htmltable->data[] = $htr;
        }
        $html = $this->output->heading($sectiontitle, 4);
        $html .= \html_writer::table($htmltable);

        //if datatables set up datatables
        if(constants::M_USE_DATATABLES) {
            $tableprops = [];
            $tableprops['paging']=true;
            $tableprops['pageLength']=50;
/*
            $columns=[];
            $columns[0]=array('orderable'=>false);
            $columns[1]=array('orderable'=>false);
            $columns[2]=array('orderable'=>false);
            $columns[3]=array('orderable'=>false);
            $columns[4]=array('orderable'=>false);
            $columns[5]=array('orderable'=>false);
            $tableprops['columns']=$columns;


            //default ordering
            $order = array();
            $order[0] =array(0, "asc");
            $tableprops['order']=$order;
*/


            $opts = Array();
            $opts['tableid'] = $tableid;
            $opts['tableprops'] = $tableprops;
            $this->page->requires->js_call_amd(constants::M_COMPONENT . "/datatables", 'init', array($opts));
        }

        return $html;

    }

    public function render_linechart($chartdata){
        global $CFG;
        //if no chart data or lower than Moodle 3.2 we do not shopw the chart
        if(!$chartdata || $CFG->version < 2016120500 ){return '';}

        $chart = new \core\chart_line();
        foreach($chartdata->series as $series){
            $chart->add_series($series);
        }
        $chart->set_labels($chartdata->labels);
        $renderedchart= $this->output->render($chart);

        $html = \html_writer::div($renderedchart,
                constants::M_CLASS . '_center ' . constants::M_CLASS . '_progresschart');


        return $html;
    }



    function show_reports_footer($moduleinstance, $cm, $formdata, $showreport,$currentformat, $showexport=true)
    {
        // print's a popup link to your custom page
        $link = new \moodle_url(constants::M_URL . '/reports.php', array('report' => 'menu', 'id' => $cm->id, 'n' => $moduleinstance->id));
        $ret = \html_writer::link($link, get_string('returntoreports', constants::M_COMPONENT));
        if($showexport) {
            $ret .= $this->render_exportbuttons_html($cm, $formdata, $showreport, $currentformat);
        }
        return $ret;
    }

    function show_perpage_selector($url, $paging)
    {
        $options = array('5' => 5, '10' => 10, '20' => 20, '40' => 40, '80' => 80, '150' => 150);
        $selector = new \single_select($url, 'perpage', $options, $paging->perpage);
        $selector->set_label(get_string('attemptsperpage', constants::M_COMPONENT));
        return $this->render($selector);
    }

    /**
     * Returns HTML to display a single paging bar to provide access to other pages  (usually in a search)
     * @param int $totalcount The total number of entries available to be paged through
     * @param stdclass $paging an object containting sort/perpage/pageno fields. Created in reports.php and grading.php
     * @param string|moodle_url $baseurl url of the current page, the $pagevar parameter is added
     * @return string the HTML to output.
     */
    function show_paging_bar($totalcount, $paging, $baseurl)
    {
        $pagevar = "pageno";
        //add paging params to url (NOT pageno)
        $baseurl->params(array('perpage' => $paging->perpage, 'sort' => $paging->sort));
        return $this->output->paging_bar($totalcount, $paging->pageno, $paging->perpage, $baseurl, $pagevar);
    }

}