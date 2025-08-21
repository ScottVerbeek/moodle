@mod @mod_quiz
Feature: Quiz attempt review in secure layout
  In order to ensure the quiz name is displayed during attempt review
  As a student
  I need to see the quiz name when reviewing an attempt taken in secure mode

  Background:
    Given the following "courses" exist:
      | fullname    | shortname | category |
      | Test course | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "activities" exist:
      | activity | name    | intro              | course | idnumber | securewindow |
      | quiz     | Quiz 1  | Quiz 1 description | C1     | quiz1    | 1            |
    And the following "question categories" exist:
      | contextlevel    | reference | name           |
      | Activity module | quiz1     | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype     | name | questiontext    |
      | Test questions   | truefalse | TF1  | First question  |
    And quiz "Quiz 1" contains the following questions:
      | question | page | maxmark |
      | TF1      | 1    |         |

  @javascript
  Scenario: Student reviews an attempt and can see the quiz name
    Given I log in as "student1"
    And I am on the "Quiz 1" "quiz activity" page
    And I should see "Quiz 1"
    And I should see "Quiz 1 description"
    When I press "Attempt quiz"
    And I set the field "True" to "1"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"

    # Now student reviews the attempt.
    Then I should see "Quiz 1"
    But I should not see "Quiz 1 description"
    Then I should see "Finish review"
