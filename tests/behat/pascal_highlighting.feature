@filter @filter_ace_inline @javascript @_file_upload
Feature: Visual checks for Pascal syntax highlighting
  In order to display syntax text highlighting
  As a teacher
  I need to be able to create Pascal-highlighted text

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email           |
      | teacher  | Teacher   | 1        | teach1@empl.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course    | role           |
      | teacher  | C1        | editingteacher |
    And I have enabled ace inline filter
    And I am on the "Course 1" "core_question > course question import" page logged in as teacher
    And I upload "filter/ace_inline/tests/fixtures/pascaldemo.xml" file to "Import" filemanager
    And I set the field "id_format_xml" to "1"
    And I press "id_submitbutton"
    Then I press "Continue"

  Scenario: Checks Pascal is highlighted correctly
    When I am on the "pascaldemo" "core_question > preview" page logged in as teacher
    And the programming language is "pascal"
    And I should see "keyword" highlighting on "program"
    And I should see "keyword" highlighting on "uses"
    And I should see "keyword" highlighting on "const"
    And I should see "keyword" highlighting on "var"
    And I should see "string" highlighting on "Demo2"
    And I should see "keyword" highlighting on "end"
    And I should see "keyword" highlighting on "begin"
  
  Scenario: Checks text is not highlighted in other languages
    When I am on the "pascaldemo" "core_question > preview" page logged in as teacher
    And the programming language is "pascal"
    And I should see "identifier" highlighting on "return"
    And I should see "identifier" highlighting on "include"
    And I should see "identifier" highlighting on "stdio"
    And I should see "identifier" highlighting on "int"
    And I should see "identifier" highlighting on "let"
    