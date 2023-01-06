<?php
/**
 * Class to handle "My Words" word pool.
 *
 * @package mod_wordcards
 * @author  David Watson - evolutioncode.uk
 */

namespace mod_wordcards;

defined('MOODLE_INTERNAL') || die();

/**
 * Class to handle "My Words" word pool.
 *
 * @package mod_wordcards
 * @author  David Watson - evolutioncode.uk
 */
class my_words_pool {

    private $courseid;
    private $pool;
    private $wordcount;

    public function __construct(int $courseid) {
        $this->courseid = $courseid;
    }

    public function get_words($maxterms = 0):array {
        global $DB, $USER;
        if ($this->pool == null) {
            $this->pool = $DB->get_records_sql(
            "SELECT t.*
                FROM {wordcards_terms} t
                JOIN {wordcards_my_words} m ON m.termid = t.id AND m.courseid = ? AND m.userid = ?
                ORDER BY t.id",
                [$this->courseid, $USER->id],
                0, $maxterms
            );
            if(!empty($this->pool)) {
                $this->pool = \mod_wordcards_module::insert_media_urls($this->pool);
                $this->pool = \mod_wordcards_module::format_defs($this->pool);
            }
        }
        return $this->pool;
    }

    public function word_count() {
        global $DB, $USER;
        if ($this->pool !== null) {
            $this->wordcount = count($this->pool);
        } else if ($this->wordcount == null) {
            $this->wordcount = $DB->get_field('wordcards_my_words', 'COUNT(id)', ['courseid' => $this->courseid, 'userid' => $USER->id]);
        }
        return $this->wordcount;
    }

    public function has_words() {
        global $DB, $USER;
        if ($this->pool !== null) {
            return !empty($this->pool);
        }
        return $DB->record_exists('wordcards_my_words', ['courseid' => $this->courseid, 'userid' => $USER->id]);
    }

    public function has_term(int $id) {
        return isset($this->get_words()[$id]);
    }

    public function add_word(int $termid): bool {
        global $DB, $USER;
        if ($this->has_term($termid)) {
           return true;
        }
        // Validate term exists.
        if (!$DB->record_exists('wordcards_terms', ['id' => $termid])) {
            throw new \invalid_parameter_exception('Invalid term id ' . $termid);
        }
        return (bool) $DB->insert_record(
            'wordcards_my_words',
            (object)[
                'userid' => $USER->id,
                'termid' => $termid,
                'courseid' => $this->courseid,
                'timemodified' => time()
            ]
        );
    }

    public function remove_word(int $termid): bool {
        global $DB, $USER;
        if (!$this->has_term($termid)) {
           return true;
        }
        return $DB->delete_records(
            'wordcards_my_words',
            [
                'userid' => $USER->id,
                'termid' => $termid,
                'courseid' => $this->courseid
            ]
        );
    }
}