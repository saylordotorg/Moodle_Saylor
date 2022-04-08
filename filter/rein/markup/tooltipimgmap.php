<!-- Parent element has relative positioning and same width and height as image. -->
<div style="position: relative; width: 200px; height: 150px;" class="tooltip-imgmap">
    <!-- Image width, height, absolute position, and top and left 0 are set. -->
    <img src="imgpath/kiwi_question.png" width="200" height="150" />
    <!-- Tip trigger is on same level as image. Give it the width and height appropriate to your needs, as well as a position absolute and top and left as needed to place it where you need over the image. -->
    <div id="imgtip" class="tip-trigger rein-plugin" style="width: 60px; height: 60px; position: absolute; left: 100px; top: 70px;"></div>
    <!-- Tip content comes immediately after trigger. -->
    <div id="tip-source-imgtip" class="tip-content ">img tip content</div>
</div>