@qtype @qtype_algebra
Feature: Test algebra questions in combined question
  In order to evaluate students responses, As a teacher I need to
  create and preview combined (Combined) questions with algebra subquestions.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |

  @javascript
  Scenario: Create, edit and preview a combined question.
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    Then I press "Create a new question ..."
    And I set the field "Combined" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    Then I should see "Adding a combined question"
    And I set the field "Question name" to "Combined 001"
    And I set the field "Question text" to "What is the square of 2xy? [[1:algebra]].<br/> What is the derivative of the function f(x) = x^2 f'(x) = [[2:algebra]]."
    Then I set the field "General feedback" to "The square of 2*x*y is 4*x^2*y^2 and the derivative of x^2 is 2*x."
    And I press "Verify the question text and update the form"

    # Follow sub questions (The order of sub questions comes from the question text).
    Then I follow "'algebra' input '1'"
    And I press "Blanks for 1 More Variables"
    And I set the following fields to these values:
      | id_subqalgebra1defaultmark     | 50%                                     |
      | id_subqalgebra1compareby       | Evaluation                              |
      | id_subqalgebra1variable_0      | x                                       |
      | id_subqalgebra1varmin_0        | -5                                      |
      | id_subqalgebra1varmax_0        | 5                                       |
      | id_subqalgebra1variable_1      | y                                       |
      | id_subqalgebra1varmin_1        | -8                                      |
      | id_subqalgebra1varmax_1        | 8                                       |
      | id_subqalgebra1answer_0        | 4*x^2*y^2                               |
      | id_subqalgebra1generalfeedback  | Your square is incorrect               |

    Then I follow "'algebra' input '2'"
    And I set the following fields to these values:
      | id_subqalgebra2defaultmark     | 50%                                     |
      | id_subqalgebra2compareby       | Evaluation                              |
      | id_subqalgebra2variable_0      | x                                       |
      | id_subqalgebra2varmin_0        | -5                                      |
      | id_subqalgebra2varmax_0        | 5                                       |
      | id_subqalgebra2answer_0        | 2*x                                     |
      | id_subqalgebra2generalfeedback  | Your derivative is incorrect           |

    # Set hints for Multiple tries
    And I follow "Multiple tries"
    And I set the field "Hint 1" to "First hint"
    And I set the field "Hint 2" to "Second hint"

    And I press "id_submitbutton"
    Then I should see "Combined 001"

    # Preview it.
    When I click on "Preview" "link" in the "Combined 001" "table_row"
    And I switch to "questionpreview" window

    # Set display and behaviour options
    And I set the following fields to these values:
      | How questions behave | Interactive with multiple tries |
      | Marked out of        | 3                               |
      | Marks                | Show mark and max               |
      | Specific feedback    | Shown                           |
      | Right answer         | Shown                           |
    And I press "Start again with these options"

    # Attempt the question
    # Test html editor for answer field in Combined MultiResponse.
    And I set the field "Answer 1" to "4*x^2*y^2"
    And I set the field "Answer 2" to "x"
    And I press "Check"
    Then I should see "Your answer is partially correct."
    And I should see "Your derivative is incorrect"
    And I should see "First hint"

    When I press "Try again"
    And I set the following fields to these values:
      | Answer 2 | 2*x |
    Then I press "Check"
    And I should see "Your answer is correct."
    And I should see "The square of 2*x*y is 4*x^2*y^2 and the derivative of x^2 is 2*x."
    And I switch to the main window

    # Backup the course and restore it.
    When I log out
    And I log in as "admin"
    When I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    When I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name | Course 2 |
    Then I should see "Course 2"
    When I navigate to "Question bank" in current page administration
    Then I should see "Combined 001"

    # Edit the copy and verify the form field contents.
    When I click on "Edit" "link" in the "Combined 001" "table_row"
    Then the following fields match these values:
      | Question name   | Combined 001 |
      | Question text   | What is the square of 2xy? [[1:algebra]].<br/> What is the derivative of the function f(x) = x^2 f'(x) = [[2:algebra]]. |

      | id_subqalgebra1defaultmark     | 50%                                     |
      | id_subqalgebra1compareby       | Evaluation                              |
      | id_subqalgebra1variable_0      | x                                       |
      | id_subqalgebra1varmin_0        | -5                                      |
      | id_subqalgebra1varmax_0        | 5                                       |
      | id_subqalgebra1variable_1      | y                                       |
      | id_subqalgebra1varmin_1        | -8                                      |
      | id_subqalgebra1varmax_1        | 8                                       |
      | id_subqalgebra1answer_0        | 4*x^2*y^2                               |
      | id_subqalgebra1generalfeedback  | Your square is incorrect               |

      | id_subqalgebra2defaultmark     | 50%                                     |
      | id_subqalgebra2compareby       | Evaluation                              |
      | id_subqalgebra2variable_0      | x                                       |
      | id_subqalgebra2varmin_0        | -5                                      |
      | id_subqalgebra2varmax_0        | 5                                       |
      | id_subqalgebra2answer_0        | 2*x                                     |
      | id_subqalgebra2generalfeedback  | Your derivative is incorrect           |

      | Hint 1          | First hint                    |
      | Hint 2          | Second hint                   |

    And I set the following fields to these values:
      | Question name | Edited question name |
    And I press "id_submitbutton"
    Then I should see "Edited question name"
