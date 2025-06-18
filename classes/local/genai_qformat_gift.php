<?php



namespace qbank_genai\local;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/format.php');
require_once($CFG->dirroot . '/question/format/gift/format.php');
class genai_qformat_gift extends \qformat_gift {
 
    public function set_context(context $context) {
        $this->importcontext = $context;
    }
}