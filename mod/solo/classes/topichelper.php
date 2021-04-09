<?php
/**
 * Created by PhpStorm.
 * User: justin
 * Date: 17/08/29
 * Time: 16:12
 */

namespace mod_solo;


class topichelper
{
    protected $cm;
    protected $context;
    protected $mod;
    protected $topics;

    public function __construct($cm) {
        global $DB;
        $this->cm = $cm;
        $this->mod = $DB->get_record(constants::M_TABLE, ['id' => $cm->instance], '*', MUST_EXIST);
        $this->context = \context_module::instance($cm->id);
    }

    public function fetch_topics()
    {
        global $DB;
        if (!$this->topics) {
            $sql ='SELECT * FROM {' . constants::M_TOPIC_TABLE . '} WHERE moduleid = :moduleid OR ' .
                    ' (courseid = :courseid AND topiclevel=' . constants::M_TOPICLEVEL_COURSE . ') ORDER BY name ASC';
            $this->topics = $DB->get_records_sql($sql, ['moduleid' => $this->mod->id, 'courseid' => $this->mod->course]);
        }
        if($this->topics){
            return $this->topics;
        }else{
            return [];
        }
    }

    public function fetch_topic($topicid)
    {
        global $DB;
        if (!$this->topics) {
            $topics=$this->fetch_topics();
        }
        if($this->topics){
            foreach($this->topics as $topic){
                if($topic->id == $topicid){
                    return $topic;
                }
            }
            return false;
        }else{
            return false;
        }
    }

    public function fetch_selected_topics()
    {
        global $DB;
        $sql ='SELECT tt.* FROM {' . constants::M_TOPIC_TABLE . '} tt INNER JOIN {' . constants::M_SELECTEDTOPIC_TABLE . '} st' .
          ' ON tt.id = st.topicid WHERE st.moduleid =:moduleid';
        $selectedtopics = $DB->get_records_sql($sql, ['moduleid' => $this->mod->id]);
        return $selectedtopics;
    }


    public function fetch_topics_for_js(){

        $topics = $this->fetch_topics();
        return $topics;
    }


}//end of class