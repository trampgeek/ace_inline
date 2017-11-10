<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Basic email protection filter.
 *
 * @package    filter
 * @subpackage simplequestion
 * @copyright  2017 Richard Jones (https://richardnz.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */

defined('MOODLE_INTERNAL') || die();

/**
 * This class looks for question tags in Moodle text and
 * replaces them with questions from the question bank.
 * Tags have the format {QUESTION:link text|xxx} where
 *                               link text is the text that links to the question 
 *                               xxx is the id of a question
 * from the bank in the current course.
 */
class filter_simplequestion extends moodle_text_filter {

  function filter($text, array $options = array()) {
  global $PAGE;
    
  // Basic test to avoid work
  if (!is_string($text)) {
    // non string data can not be filtered anyway
    return $text;
  }
  // Might need to change these at some point - eg to double curlies
  // defined in setting.php with default values
  
  $def_config = get_config('filter_simplequestion');
  $starttag = $def_config->starttag;
  $endtag = $def_config->endtag;
  $linktextlimit = $def_config->linklimit;
  $key = $def_config->key;
    
  // Do a quick check to see if we have a tag
  if (strpos($text, $starttag) === false) {
    return $text;
  }
 
  $renderer = $PAGE->get_renderer('filter_simplequestion');
  // Check our context and get the course id
  $coursectx = $this->context->get_course_context(false);
  if (!$coursectx) {
    return $text;
  }
  $courseid = $coursectx->instanceid;
  
  // There may be a question or questions in here somewhere so continue ...
  // Get the question numbers and positions in the text and call the
  // renderer to deal with them
  $text = filter_simplequestion_insert_questions($text, $starttag, $endtag, $linktextlimit, 
                                                 $renderer, $key, $courseid);   
    return $text;
  }

}
/**
*
* function to replace question filter text with actual question
*
* params:  string containing patterns, pattern start, pattern end, renderer
*/
function filter_simplequestion_insert_questions($str, $needle, $limit, $linktextlimit, 
                                                $renderer, $key, $courseid) {
  
  $newstring = $str;
  While (strpos($newstring, $needle) !== false) {
    $initpos = strpos($newstring, $needle);
    if ($initpos !== false) {
       $pos = $initpos + strlen($needle);  //get up to string
       $endpos = strpos($newstring, $limit);
       $data = substr($newstring, $pos, $endpos - $pos); // extract question data
       //  Get the parameters
       $params = explode('|', $data);

       // Run some checks
       $verified = true;
       
       if (count($params) == 3 ) {
       
          $linktext = trim($params[0]);
          $number = $params[1];
          $popup = trim($params[2]);

          // Clean the text strings
          $linktext = filter_var($linktext, FILTER_SANITIZE_STRING);
          $popup = filter_var($popup, FILTER_SANITIZE_STRING);
       
       } else {
       
         // Invalid parameter count
         $question = get_string('param_number_error', 'filter_simplequestion'); 
         $verified = false;
       }

       if ($verified) {     
         // Check the popup 
         if ( ($popup != 'embed') && ($popup != 'popup') ) {
           // Invalid display mode
           $question = get_string('pop_param_error', 'filter_simplequestion'); 
           $verified = false;
         }
       }
              
       if ($verified) {     
         // Check the number (must be integer)
         if (filter_var($number, FILTER_VALIDATE_INT) === false) {
           // Invalid number string
           $question = get_string('link_number_error', 'filter_simplequestion'); 
           $verified = false;
         }
       }
       
       // Check the link text for length
       if ($verified) {
         if (strlen($linktext) > $linktextlimit) {
            $question = $linktext . get_string('link_text_length', 'filter_simplequestion'); 
            $verified = false;
         }
       }
       
       if ($verified) {
        // Render the question link
        // Encrypt question number
        $en = \filter_simplequestion\utility\tools::encrypt($number, $key);
        $question = $renderer->get_question($en, $linktext, $popup, $courseid);
       } else {
        $question = $renderer->get_error($question);   
       }
       // Update the text to replace the filtered string
       $newstring = substr_replace($newstring, $question, $initpos, $endpos - $initpos + 1);
       $initpos = $endpos + 1;
    }
  }
  return $newstring;
}