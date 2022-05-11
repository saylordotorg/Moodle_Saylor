<div class="sortMultipleLists rein-plugin">
    <div class="instructions">
        <p>Drag item below into the correct category. Then select the button to check your answer.</p>
    </div>
    <!-- Begin sortable columns -->
    <div class="sortableCols uniform-height">
        <ul id="col1" class="sortable md-col ui-state-highlight">Drag from here.
            <li class="belongs-in-col3 ui-state-default ui-corner-all"><span class="ui-icon ui-icon-arrow-4">text</span>
                <div><img src="imgpath/kiwi_question.png" width="80" height="80" alt="test image"></div>
            </li>
        </ul>
        <!-- Begin sortable column 2 -->
        <ul id="col2" class="sortable md-col ui-state-highlight">Incorrect</ul>
        <!-- Begin sortable column 3 -->
        <ul id="col3" class="sortable md-col ui-state-highlight">Correct</ul>
        <!-- Begin sortable column 4 -->
        <ul id="col4" class="sortable md-col ui-state-highlight">Incorrect</ul>
    </div>
    <!-- Button to trigger checking of answers -->
    <div class="checkAnswParent"><button type="button" class="checkAnswers doButton ui-state-default ui-corner-all"> Check Answers </button></div>
    <!-- Dialog to give remediation and allow learner to make a choice between seeing correct answers and trying again -->
    <div class="remediation-cont">
        <p class="remediation positive">Yes, that's correct!</p>
        <p class="remediation negative">Sorry, that's not correct.</p>
    </div>
</div>