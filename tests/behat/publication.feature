@mod @mod_publication
Feature: Create publication instance

  @javascript
  Scenario: Create publication instance in course1
    Given the following "users" exist:
        | username | firstname | lastname | email |
        | teacher1 | Teacher | 1 | teacher1@asd.com |
    And the following "courses" exist:
        | fullname | shortname | category | startdate |
        | Course 1 | C1 | 0 | 1460386247 |
        | Course 2 | C2 | 0 | 1460386247 |
    And the following "course enrolments" exist:
        | user | course | role |
        | teacher1 | C1 | editingteacher |
        | teacher1 | C2 | editingteacher |

    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I add a "Student folder" to section "1" and I fill the form with:
      | Student folder name | Test studentfolder name |
      | Description         | Test description        |
      | ID number           | Test studentfolder name |
    And I am on the "Test studentfolder name" activity page logged in as teacher1
    And I press "Edit/upload files"
    Then I should see "Own files"
