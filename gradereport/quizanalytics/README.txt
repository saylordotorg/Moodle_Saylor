Gradereport Quiz Analytics

Overview:

The gradebook interface for all Users, showing graphical analysis of their Quiz attempts
for a course.

Go to any course page which has at least one Quiz, choose the "Quiz Analytics" after 
clicking on "Grades" link in the course tree of "NAVIGATION" block.
Then we can see Quiz-name, the No of attempts of logged in user is appears as a table format.
A"View Analytics" link is present there which upon clicking will display the graphical depiction.

In the graphical analysis it has four types of graphs:-
(1)Attempts Summary/Last Attempts Summary, (2)My Progress and Predictions,
(3)Question Categories' Analysis and (4)Scores' & Questions' Stats.

(1) Attempt Summary/Last Attempt Summary - Attempt Summary for single attempt and Last
    Attempt Summary for multiple attempt.
    It shows Number of Question Attempted, Right Answer, Partially Correct answers in the last attempt
    and also shows the Accuracy Rate.

(2) My Progress and Predictions - It shows three types of graph.
    2.1) For Multiple attempt it shows:
        Improvement Curve - This graph shows how you improved over all your attempts and 
        the dark block represents the no of average attempts required to reach the score
        set as cut off (by site admin).

        For Single attempt (Attempts allowed of that quiz is one) it shows:
        Peer Performance - This graph shows how your peers have scored in comparison with you.

    2.2) Hardest Question - This graph represnts top ten hardest questions depending on how
        many times the quiz was attempted and the times that particular question is left
        unattempted or incorrectly attempted. Clicking on the bars dedicated to each
        question will show the question itself along with explanation and correct answer.

    2.3) Attempt Snapshot - This section is like a recap, displaying the key figures of all
        your previous attempt.

(3) Question Categories' Analysis - It shows three types of graph.
    3.1) Question Per Category - This graph tells you the number of questions present in the
        quiz from each category.

    3.2) Challenging Categories (Across All Users) - This section reports on the basis of
        wrong and not answered cases, the top ten categories that turned out to be most
        challenging across all the users who took the quiz.

    3.3) Challenging Categories for me - This graph shows the top ten categories that turned
        out to be most challenging for the logged-in user.

(4) Scores' & Questions' Stats - It shows two types of graph.
    4.1) Scores by Percentage (All Users) - It shows Number of users in each percentage
        (score percentage) group.

    4.2) Question Analysis - The curves here depict how the users fared in each question.
        Clicking on the circles dedicated to each question will show the question itself
        along with explanation and correct answer.

Settings Instructions:

Go to Site administration -> Grades -> Report settings -> Quiz Analytics
here you can Set Cut Off for all the quizes of the course, can set Grade Boundary and
add Facebook App ID, Facebook API version and Title to share the graph on Facebook.

To Create Facebook App ID:

1. Login to Facebook.
2. Click on Manage Apps from left side bar.
3. Add an App by clicking on the "Add a New App" button.
    You can see API Version, App ID in Dashboard page.
4. Click on Settings link from left side bar then fill up all the required fields and
    add one platform by clicking on the "Add Platform" choose "Website" After that
    click on the "Save Changes" button.
5. Click on App Review from left side bar, click on the space to the left side of "No"
    to make your app public.

Installation:

Installing directly from the Moodle plugins directory:

1.Login as an admin and go to Site administration > Plugins > Install plugins.
 (If you can't find this location, then plugin installation is prevented on your site.)
2.Click the button 'Install plugins from Moodle plugins directory'.
3.Search for a plugin with an Install button, click the Install button then click Continue.
4.Confirm the installation request.
5.Check the plugin validation report.

Installing via uploaded ZIP file:

1.Go to the Moodle plugins directory, select your current Moodle version,
 then choose a plugin with a Download button and download the ZIP file.
2.Login to your Moodle site as an admin and go to Administration > Site administration > Plugins > Install plugins.
3.Upload the ZIP file. You should only be prompted to add extra details
 (in the Show more section) if your plugin is not automatically detected.
4.If your target directory is not writeable, you will see a warning message.
5.Check the plugin validation report

Installing manually:

1.Go to the Moodle plugins directory; select your current Moodle version,
 then choose a plugin with a Download button and download the ZIP file.
2.Upload or copy it to "your-Moodle-directory"/grade/report folder then Unzip it.
3.In your Moodle site (as admin) go to Settings > Site administration > Notifications.
4.Then Install it.
