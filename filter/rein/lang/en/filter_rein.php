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

defined('MOODLE_INTERNAL') || die;

$string['filtername'] = 'REIN Library Javascript';
$string['filterdisabled'] = 'This filter is currently disabled. Before accessing the debug interface, you must enable the filter.';

$string['debug'] = 'Debug Mode';
$string['debug_desc'] = 'Turning this on will include additional files for testing.';

$string['debuginterface'] = 'rein.js Debug Interface';
$string['debugdesc'] = '<p>The REIN widget debug interface window provides a test interface in a new window. This interface can be
used for debugging and theming. <a target="_blank" href="{$a}">Display Debug Interface</a>.';
$string['debugtitle'] = 'rein.js Debug Interface';
$string['debuginstr'] =
'<p>This interface is designed for rein.js widget viewing, theming, and debugging. Please note that content development with rein.js widgets
requires a basic understanding of HTML and CSS. To utilize the interface, follow the instructions below.</p>
<p><b>To learn about widgets:</b>
    <ol>
        <li>Access the debugging interface.</li>
        <li>Browse the interface and interact with the widgets.</li>
        <li>When you find a widget you want to embed in Moodle content, select the <b>View Markup</b> button for the widget.
        A new browser window will open with the markup for the widget. Select all and copy the markup.</li>
        <li>Navigate to your course content, and edit the resource or activity where you want to place the widget.</li>
        <li>Paste the widget markup into the textarea field. (Note that if the TinyMCE text editor is in use, you must select the HTML view
        TinyMCE dialog, then paste your markup.)</li>
        <li>Modify the widget content as appropriate. Use the TinyMCE image dialog to add images using the file picker, then copy the image
        <code>src</code> to the correct location in the widget markup.</li>
        <li>Save your changes and view your content.</li>
    </ol>
</p>
<p><b>To theme widgets:</b>
    <ol>
        <li>Apply your theme to the site.</li>
        <li>Modify your theme to style the rein.js widgets.</li>
        <li>Navigate to <b>Site Administration > Development > Purge All Caches</b> and <b>Purge All Caches</b>.
        <li>Reload the debugging interface to see your changes.</li>
    </ol>
</p>
<p><b>To debug widgets:</b>
    <ol>
        <li>Navigate to <b>Site Administration > Plugins > Filters > REIN Library Javascript</b>.</li>
        <li>Select the <b>Debug Mode</b> checkbox and <b>Save changes</b>.</li>
        <li>Using Chrome or FireFox with the developer javascript console enabled, access the debugging interface.</li>
        <li>The javascript console will display the results of rudimentary functional tests for each widget. Look for red error logs
        in the javascript console, as these would indicate errors.</li>
        <li>Additional images are displayed in the debugging interface. These images present the widgets as they should look when functioning
        correctly. These screenshots will not reflect CSS changes to look and feel made by clients, but they will portray overall look and
        functionality at a single glance, making it easy to tell if the plugin javascript or CSS is not loading.</li>
    </ol>
</p>';

$string['widgetinstr'] = 'Instructions';
$string['widgetview'] = 'Interactive Preview';
$string['widgetshouldlook'] = 'Should Look Like';

$string['accordiontitle'] = 'Accordion';
$string['accordiondesc'] = '<p>The rein.js Accordion Widget presents content in a series of sliding panes.</p>';
$string['accordioninstr'] = '<ul><li>Add as many accordion panes as you want, though stacking several will decrease the view space for your
content.</li>
<li>Each pane contains the following markup: <br />
    <pre>
        &lt;h3 class="accordion-header"&gt;
            &lt;a href="#"&gt;Title pane 3&lt;/a&gt;
        &lt;/h3&gt;
        &lt;div&gt;
            &lt;p&gt;Content pane 3&lt;/p&gt;
        &lt;/div&gt;
    </pre>
</li>
<li>Adjust the height, width, and centering of the widget by altering the style attribute in the <code>&lt;div&gt;</code> that is the parent
of the element with class <code>doAccordion</code>.</li>';
$string['accordionviewmarkup'] = 'View Accordion Markup';

$string['tabstitle'] = 'Tabs';
$string['tabsverticaltitle'] = 'Vertical Tabs';
$string['tabstoptitle'] = 'Top Tabs';
$string['tabsarrowtitle'] = 'Arrow Tabs';
$string['tabsdesc'] = '<p>The rein.js Tabs Widget presents content in a series of tabbed panes.</p>';
$string['tabsinstr'] = '<ul><li>Add as many accordion panes as you want, but be careful not to make the widget too wide for its
Moodle content area.</li><li>Adjust the height, width, and centering of the widget by altering the style attributes of the target element.</li>';
$string['tabsviewmarkup'] = 'View Tabs Markup';

$string['equalcolumnstitle'] = 'Equal Columns';
$string['equalcolumnsdesc'] = '<p>The rein.js Equal Columns Widget presents content in an equally spaced columned layout and can use optional responsive layout classes.</p>';
$string['equalcolumnsinstr'] = '<ul>
<li>Any valid HTML will work in equal columns.</li>
<li>Optional responsive classes may be used to drop the columns to a stacked layout at a designated width:
    <ul>
        <li><code>.drop-columns-480</code>: drops the columns when the browser width is < 480px</li>
        <li><code>.drop-columns-640</code>: drops the columns when the browser width is < 640px</li>
        <li><code>.drop-columns-768</code>: drops the columns when the browser width is < 768px</li>
        <li><code>.drop-columns-992</code>: drops the columns when the browser width is < 992px</li>
        <li><code>.drop-columns-1200</code>: drops the columns when the browser width is < 1200px</li>
    </ul>
</li>
<li>Up to 12 columns may be used in one equal columns widget. Each column is sized to have a uniform width.</li>
<li>img, video, object, embed, and iframe elements within a columns are set to size responsively. This can be overridden with inline styles on the element.</li>
<li>Inline widths may be applied to override the equal width of each column. Widths should be in percentages and should add up to 100%.</li>';
$string['equalcolumnsviewmarkup'] = 'View Equal Columns Markup';

$string['modaltitle'] = 'Modal';
$string['modaldesc'] = '<p>The rein.js Modal Widget presents content in an overlay modal window. Images, text, and embedded media can be displayed.</p>';
$string['modalinstr'] = '<ul><li>Any valid HTML will work in the modal, though content wider than 600px is not recommended.</li>
<li>Width and height of the modal can be specified inline, though this will break any default responsive behavior.</li>';
$string['modalviewmarkup'] = 'View Modal Markup';

$string['toggletitle'] = 'Toggle';
$string['toggledesc'] = '<p>The rein.js Toggle Widget expands content hidden somewhere on the page. Images, text, and embedded media can be displayed.</p>';
$string['toggleinstr'] = '<ul><li>Any valid HTML will work in the toggle content.</li>
<li>Width and height of the toggle content can be specified inline.</li>
<li>The toggle content does not need to be next to the toggle button. The only requirement is that the <code>data-target</code> attribute of the button
matches the id of the toggle content.</li>';
$string['toggleviewmarkup'] = 'View Toggle Markup';

$string['flipbooktitle'] = 'Flip Book';
$string['flipbookdesc'] = '<p>The rein.js Flip Book displays content in a book format with flipping pages. Standard HTML can be used. ';
$string['flipbookdesc'] .= 'Embedded media can be used as well but is not recommended as it will continue playing after the page it&apos;s is on is flipped.</p>';
$string['flipbookinstr'] = '<ul><li>Any valid HTML will work in the toggle content.</li>
<li>All pages are automatically sized to whatever the tallest page&apos; height is.</li>
<li>A vertical flip book can be shown by adding a <code>vertical</code> class to the <code>flip-book</code> div.</li>
<li>Horizontal flip books change to a single page view by default at 767 pixels. This can be overidden by adding a <code>data-singlepagewidth</code> attribute with a different number.</li>';
$string['flipbookviewmarkup'] = 'View Flip Book Markup';

$string['flipcardtitle'] = 'Flipcard';
$string['flipcarddesc'] = '<p>The rein.js Flipcard widget features content with CSS flip animations.';
$string['flipcardinstr'] = '<ul>
    <li>Add content to <code>.flipcard-front</code> and <code>.flipcard-back</code> elements.</li>
    <li>Flipcards will automatically center inside &lt;ul&gt; element. More than one flipcard inside a single &lt;ul&gt; will display side-by-side where parent width allows. When using the side-by-side layout with <code>.flipcard-type-swing</code> elements, set a <code>z-index</code> inline style on each <code>.flipcard-item</code> element, so that <code>z-index</code> descends from left to right (see the second example below).</li>
    <li>Height and width are set with an inline style on the <code>.flipcard-item</code> element.</li>
    <li>Four separate animation styles are available. Add the animation style class to the <code>.flipcard-item</code> element.
      <ul>
        <li>Flip: <code>.flipcard-type-flip</code></li>
        <li>Swing: <code>.flipcard-type-swing</code></li>
        <li>Hinge: <code>.flipcard-type-hinge</code></li>
        <li>Cube: <code>.flipcard-type-cube</code></li>
        <li>Fade: <code>.flipcard-type-fade</code></li>
      </ul>
    </li>
    <li>Class <code>flipcard-3x5</code> added to the <code>flipcard-item</code> element provides ruled notecard classes to the flipcards.</li>
    <li>Class <code>flipcard-round</code> added to the <code>flipcard-item</code> element makes the flipcard circular.</li>
    <li>For keyboard compatibility, add attribute <code>tabindex="n"</code> to the <code>flipcard-item</code> element. n should be a number which places the element in an appropriate place in the tab order. (Normally 0, 1, etc. are reserved for header navigation, so be sure not to test with keyboard so that you do not hijack normal keyobard navigation on the page.)</li>
    <li>The <code>div.flipcard-rollout</code> element is an optional element that can be placed into back cards that do not animate (that is, swing and hinge animations where the back content doesn\'t move around.) Use inline style attributes such as <code>style="height:100px;width:100px;font-size:9px;padding:25px;"</code> to customize the size of the rollout to the content on the <code>flipcard-back</code> element. The dimensions of the rollout should accommodate any content inside the rollout, and should not exceed the size of the containing flipcard.</li>
    <li>When using the <code>.flipcard-type-hinge</code> element near the bottom of the Moodle content area, add content or padding below so that when animated open, the front of the flipcard does not underlay other Moodle elements.</li>
  </ul>';
$string['flipcardviewmarkup'] = 'View Flipcard Markup';

$string['clickhotspottitle'] = 'Click Hotspot';
$string['clickhotspotdesc'] = '<p>Position the click target anywhere within a click stage of set dimensions. Clicks to the stage but not
on the click target result in negative remediation. Clicks on the stage and on the click target result in positive remediation.</p>';
$string['clickhotspotinstr'] = '<ul><li>This is a complex widget. View the markup as rendered in Moodle, using browser developer tools,
to understand the markup better.</li>
<li>Add your own image to the element with class <code>clickStage</code>. Be sure to update the width and height attributes correctly.</li>
<li>The element with class <code>clickStage</code> is where all clicks are recognized and assessed.</li>
<li>Elements with class <code>xcoord</code> and <code>ycoord</code> are where you place numbers telling the library where to position
the target for correct clicks. Numbers correspond to pixels distance from 0,0 which is in the upper left corner of the stage.</li>
<li>Edit positive and negative remediation if you like.</li>
<li>Increase the opacity of the element with class <code>clickTarg</code> while positioning it, so you can see it on the stage. Once
it\'s in the right place, change opacity back to 0.</li>
<li>Test by clicking on the element with class <code>clickStage</code>, both on and off the element with class <code>clickTarg</code>.</li>
<li>Include instructions and title as you see fit.</li></ul>';
$string['clickhotspotviewmarkup'] = 'View Click Hotspot Markup';

$string['sortmultipletitle'] = 'Sort Multiple Lists';
$string['multipledragheader'] = 'Multiple Draggable Example';
$string['singledragheader'] = 'Single Draggable Example';
$string['sortmultipledesc'] = '<p>The Sort Multiple Lists Widget is a complex drag-and-drop exercise. Items can be dragged into two or
three columns. When an assessment button is selected, all items are assessed, and feedback displayed. The widget can be set up to present
several draggables in a vertical stack, or a single draggable with 2 or 3 drop targets.</p>';
$string['sortmultipleviewmarkup'] = 'View Sort Multiple Lists Markup';
$string['sortmultiplesingledragviewmarkup'] = 'View Sort Multiple Lists with Single Draggable Markup';

$string['sortmultipleinstr'] = '<ul><li>CSS default is 3 cols including source col. Adjust the width of the cols on the <code>div</code>s (as
indicated) to accommodate the number of columns you\'re using.</li>
<li>Because this item does assessment using element ids, you should only use one per Web page.</li>
<li>You must adjust assessment by changing the classname <code>belongs-in-[]</code> in each draggable element. If the element is correct in
column with id <code>col2</code> then element should have classname <code>belongs-in-col2</code>.</li>
<li>Pay attention to both target element width and column widths. Column widths must add up to less than target element width. Target element
width must fit inside the Moodle content area.</li>';

$string['dropbubbletitle'] = 'Drop Bubble';
$string['dropbubbledesc'] = '<p>The drop bubble widget is a simple drag and drop interaction which shows remediation on drop with a color
change.</p>';
$string['dropbubbleinstr'] = '<p><ul><li>Note that this activity is not optimal for users with red/green colorblindness.</li>
<li>Draggables (with class <code>sm-draggable</code>) that also have the class <code>drop-ok</code> are given a green border when dropped
in the element with class <code>big-droppable</code>. All other <code>sm-draggable</code> items will change to red border when dropped
in the <code>big-droppable</code> item. </li>
<li>In order for widget layout to work correctly, the stack of draggables should not be greater in height than the bubble.</li></ol></p>';
$string['dropbubbleviewmarkup'] = 'View Drop Bubble Markup';

$string['stepwisetitle'] = 'Stepwise Process';
$string['stepwisedesc'] = '<p>This activity visually arranges a series of vertical steps, each of which initiates a modal dialog displaying
additional details on click.</p>';
$string['stepwiseinstr'] = '<p><ul><li>This activity visually arranges a series of vertical steps. Each step, upon click, initiates a modal
dialog displaying the element with class <code>step-detail</code>.</li>
<li><code>div</code>s with class <code>arrow</code> are detected and an <code>svg</code> arrow is drawn into them. The arrows
are meant to go between steps.</li></ul></p>';
$string['stepwiseviewmarkup'] = 'View Stepwise Process Markup';

$string['sequentialtitle'] = 'Sequential Appearance';
$string['sequentialdesc'] = '<p>This widget is very similar to the Stepwise Process widget. A series of items are exposed by a vertical
slide animation.</p>';
$string['sequentialinstr'] = '';
$string['sequentialviewmarkup'] = 'View Sequential Appearance Markup';

$string['rotatortitle'] = 'Rotator';
$string['rotatordesc'] = '<p>The Rotator Widget is a very simple widget. It presents a series of images, each of which fades into the next. The
fading pauses during a hover event.</p>';
$string['rotatorinstr'] = '<p><ul><li>This activity uses an id for identifier. Use only once per page.</li>
<li>This widget is more for visual impact than educational value. Content constantly fading in and out on a page may be distracting for learners.
Use discretion.</li></ul></p>';
$string['rotatorviewmarkup'] = 'View Rotator Markup';

$string['markittitle'] = 'MarkIt';
$string['markitdesc'] = '<p>The MarkIt Widget is an interactive pane upon which the learner can draw with drag or touch events.</p>';
$string['markitinstr'] = '<p>
<ul>
    <li>MarkIt is a <code>div</code> which is populated with an <code>svg</code> that uses Raphael.js to support live draw.</li>
    <li>To minimize load on the JavaScript engine, only the first MarkIt Widget on a Web page is initialized. Do not attempt to add more than
one to a single page.</li>
    <li>Images, lines, and text can be painted onto the MarkIt surface on page load. These are indicated by content in a <code>div</code> with
class <code>draw-on-load</code>.</li>
    <li>MarkIt cursors are a pen and a yellow highlight.</li>
    <li>MarkIt Undo button removes one drawn path at a time.</li>
    <li>MarkIt Clear button removes all drawn paths.</li>
    <li>Trigger MarkIt object using the markup below:
        <pre>
    &lt;div class="rein-plugin markit" style="width:600px;height:400px;"&gt;
      &lt;div class="draw-on-load">&lt;/div&gt;
      &lt;div class="draw-on-check">&lt;/div&gt;
    &lt;/div&gt;
        </pre>
    </li>
    <li>You can eliminate the child with classes <code>draw-on-load</code> and/or <code>draw-on-check</code> if you do not need any shapes drawn on the MarkIt.</li>
    <li>You determine the width and height of the MarkIt by assigning local width and height styles to that effect.</li>
    <li>General notes on drawn object markup:
        <ul>
            <li>For all positioning (ex: x and y) the stage represents the bottom right of the trigonometric dimensions.
            That is, <code>x=0</code> is absolute left. <code>y=0</code> is absolute top.</li>
            <li>Default values are being set for all optional values. If object is rendering not quite how you wanted it, check your markup
            because the default value might be overriding.</li>
            <li>Objects are drawn one on top of the other, images first, because images can potentially block out other elements.</li>
        </ul>
    </li>
    <li>Add "designermode" as a class to enable the designer mode. This will help to generate paths for more complicated lines.</li>
    <li>When "designermode" is used, the smooth lines button can be used to smooth out paths.</li>
    <li>Drawn element <code>div</code>s have the following classes:
        <ul>
            <li>draw-image
                <ul>
                    <li>Required child <code>div</code>s:
                        <ul>
                            <li>src</li>
                            <li>x</li>
                            <li>y</li>
                            <li>width</li>
                            <li>height</li>
                        </ul>
                    </li>
                    <li>There are no non-required values in this set. But if the image <code>src</code> is not included (extremely important)
                    or does not resolve, you will get an alert.</li>
                </ul>
            </li>
            <li>draw-line
                <ul>
                    <li>required child <code>div</code>s:
                        <ul>
                            <li>startx: x coordinate of start point.</li>
                            <li>starty: y coordinate of start point.</li>
                            <li>stopx: x coordinate of end point.</li>
                            <li>stopy: y coordinate of end point.</li>
                        </ul>
                    </li>
                    <li>Non-required child <code>div</code>s:
                        <ul>
                            <li>stroke: Hex value for line color.</li>
                            <li>stroke-width: Number value for width of path.</li>
                            <li>stroke-opacity: Number from 0 to 1, 0 being transparent, 1 being opaque.</li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li>draw-text
                <ul>
                    <li>Required child <code>div</code>s
                        <ul>
                            <li>x</li>
                            <li>y</li>
                            <li>text: String representing a single line of text. (The widget cannot do line breaks bc some browsers
                            strip out \n. Use a second <code>draw-text</code> positioned below the first.)</li>
                        </ul>
                    </li>
                    <li>Non-required child <code>div</code>s
                        <ul>
                            <li>font-family: Default is "Helvetica, Arial, sans-serif". Because some browsers have limited font support,
                            it\'s best to leave this default.</li>
                            <li>font-size: Raphael.js is not clear on whether this represents pixels or pt. Experiment with it.
                            Default is 20.</li>
                            <li>fill: Hex value for color of text.</li>
                            <li>font-weight: Default is bold. Use any of the standard CSS options for font-weight.</li>
                            <li>opacity: Number from 0 to 1, 0 being transparent, 1 being opaque.</li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li>draw-path
                <ul>
                    <li>Required child <code>div</code>s
                        <ul>
                            <li>path: Path string using standard SVG code. Designer mode will generate the necessary html.</li>
                        </ul>
                    </li>
                    <li>Non-required child <code>div</code>s
                        <ul>
                            <li>stroke: Hex value for line color.</li>
                            <li>stroke-width: Number value for width of path.</li>
                            <li>stroke-opacity: Number from 0 to 1, 0 being transparent, 1 being opaque.</li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>
    </li>
</ul>';
$string['markitviewmarkup'] = 'View MarkIt Markup';

$string['usepenbuttonlabel'] = 'Pencil';
$string['usehighlightbuttonlabel'] = 'Highligter';

$string['undobuttonlabel'] = 'Undo';
$string['clearbuttonlabel'] = 'Clear';

$string['smoothlinesbuttonlabel'] = 'Smooth Lines';
$string['getmarkupbuttonlabel'] = 'Get Markup';

$string['getcheckitbuttonlabel'] = 'Check It';

$string['markitmodalmarkuptitle'] = 'Markit Markup';
$string['markitmodalmarkupinstructions'] = 'Copy the source code below in to the appropriate div in the source of the activity. ';
$string['markitmodalmarkupinstructions'] .= 'Paste just below the &lt;div class="draw-on-load"&gt; line to draw these marks when the activity loads. ';
$string['markitmodalmarkupinstructions'] .= 'Paste just below the &lt;div class="draw-on-check"&gt; line to draw these marks when the check button is pressed.';

$string['tooltiptitle'] = 'Tooltip';
$string['tooltipdesc'] = '<p>The Tooltip Widget is a basic tooltip. It is exposed on hover over a target designated in the markup.</p>';
$string['tooltipinstr'] = '
<ul>
    <li>The tooltip can be used with <code>&lt;img&gt;</code>, <code>&lt;span&gt;</code>,
        and <code>&lt;div&gt;</code> tags.</li>
    <li>By default, the tooltip detects <code>&lt;img&gt;</code>, <code>&lt;span&gt;</code>, and
        <code>&lt;div&gt;</code> tags with the class <code>tip-trigger</code>.</li>
    <li>If the trigger is a <code>&lt;span&gt;</code> or <code>&lt;div&gt;</code>, the tooltip
        uses the <code>title</code> attribute of the element as the content of the tooltip.</li>
    <li>If the trigger is an <code>&lt;img&gt;</code>, the tooltip uses the <code>alt</code>
        attribute of the element as the content of the tooltip.</li>
    <li>If you want to add more sophisticated content to your tooltip, do <b>not</b> give your
        <code>tip-trigger</code> element a <code>title</code> attribute. Instead, give your
        give your <code>tip-trigger</code> element element an ID attribute. (Remember that all IDs
        on a given Web page must be distinct.) Then add a <code>&lt;div&gt;</code> to the page with
        the class <code> tip-content</code>. Give the tooltip contents <code>&lt;div&gt;</code> an
        ID attribute of <code>tip-source-[]</code>, where [ ] is the <code>tip-trigger</code> ID
        attribute. So if your <code>tip-trigger</code> ID is <code>tip1</code>, your <code>tip-content</code>
        must have the ID <code>tip-source-tip1</code>.</li>
    <li>It is also possible to implement sophisticated imagemap-type functionality with the tooltip. In this case
        the <code>tip-trigger</code> element is positioned in relation to an image element. View the
        markup sample for more information about how to create this functionality.</li>
</ul>';

$string['tooltiptitleattribute'] = 'Tooltip Using Title Attribute';
$string['tooltipimgalt'] = 'Tooltip Using Image Alt Attribute';
$string['tooltipcustomcontent'] = 'Tooltip Using Custom Content';
$string['tooltipimgmap'] = 'Tooltip Image Map';
$string['tooltiptitleattribviewmarkup'] = 'View Tooltip Title Attribute Markup';
$string['tooltipdarkviewmarkup'] = 'View Tooltip Using Image Alt Markup';
$string['tooltipcustomcontentmarkup'] = 'View Tooltip Using Custom Content Markup';
$string['tooltipimgmapviewmarkup'] = 'View Tooltip Image Map Markup';

$string['overlaytitle'] = 'Image or Mixed Content Overlay';
$string['overlaydesc'] = '<p>The Overlay Widget is a basic overlay which contains either a single image or mixed content made of text, images and other assets. It is launched from a thumbnail or button which is generated dynamically using an indicated thumbnail ID or link title.</p>';
$string['overlayinstr'] = '
<ul>
    <li>An overlay widget is triggered by an <code>&lt;a&gt;</code> tag with the classes
    <code>rein-plugin overlay</code>.</li>
    <li>Indicate the format of the overlay using the <code>data-format</code> attribute.
    At present, you can use <code>data-format="image"</code> or <code>data-format="mixed"</code>.</li>
    <li>If you wish the overlay anchor to contain a thumbnail, provide the ID of the thumbnail
    using the <code>data-thumbID</code> attribute. Add the image inside the anchor tag using Moodle\'s
    file picker. Add the ID attribute to the image. If the <code>&lt;a&gt;</code> tag contains
    <code>data-thumbID="test"</code>, then REIN will look for the image with the ID <code>test</code>
    within the link.</li>
    <li>If a thumbnail ID is provided, that thumbnail is sized according to the attribute
    <code>data-thumbwidth</code>. Provide the width in pixels to scale the image for the thumbnail.
    The height will be dynamically determined based on the actual height and width ratio of the image
    at full size. If no width is provided, REIN will default to 200px.</li>
    <li>If no thumbnail is provided, REIN assumes that the link should be presented as a button. The
    <code>a</code> tag\'s <code>title</code> attribute will be used as the button text.</li>
    <li>As always, when using IDs, remember that ID attributes must be unique on a give page. Do not assign
    the same ID to multiple images.</li>
    <li>The default maximum thumbnail height is 500px, and the default maximum thumbnail width is 800px. If your thumbnail exceeds those dimensions during the scaling process, it will be restricted to those dimensions. To override those defaults, add the attribute <code>data-thumb-maxheight</code> or the attribute <code>data-thumb-maxwidth</code> or both.</li>
</ul>';

$string['overlaytitleattribute'] = 'Image Overlay';
$string['overlayshouldlooklikes'] = 'Thumbnail and Button Style Links, Image Overlay and Mixed Content Overlay';
$string['overlayimgalt'] = 'Tooltip Using Image Alt Attribute';
$string['overlaycustomcontent'] = 'Tooltip Using Custom Content';
$string['overlayimgmap'] = 'Tooltip Image Map';
$string['overlaytitleattribviewmarkup'] = 'View Tooltip Title Attribute Markup';
$string['overlaylinkdesc'] = 'Overlay thumbnail- and button-style links.';
$string['overlayimagedesc'] = 'Overlay of the image format.';
$string['overlaymixeddesc'] = 'Overlay of mixed format.';
$string['overlayimageviewmarkup'] = 'View single image overlay markup';
$string['overlaymixedviewmarkup'] = 'View mixed format overlay markup';
$string['overlayimagewiththumbnail'] = 'Image overlay with thumbnail link';
$string['overlaymixedwithbutton'] = 'Image overlay with thumbnail link';

$string['nestingerror'] = 'The page markup suggests that you\'ve nested one highly complex or top-level REIN navigation or interaction feature inside another. While we understand that you might be trying to create an extra special interface, but these kinds of combinations can be detrimental to accessibility and ease of navigation. In general, it\'s best to only nest simple REIN widgets inside of complex REIN widgets or navigation elements. Once you resolve this nesting issue the REIN elements on the page will render.';

$string['swipertitle'] = 'Swiper';
$string['swiperdesc'] = 'The Swiper widget uses the <a href="http://idangero.us/swiper/">Swiper</a> library maintained <a href="http://www.idangero.us">iDangero.us</a>. Swiper provides a set of carousel slides, which contain either images or text.';
$string['swiperinstr'] = '<ul>
                            <li>The Swiper widget is triggered by an <code>&lt;a&gt;</code> tag with the classes <code>rein-plugin</code> and <code>swiper-container</code>.</li>
                            <li>This widget is extremely flexible and not prescriptively styled by REIN CSS. Work closely with a theme designer on sites where this widget will be heavily used.</li>
                            <li>The Swiper library has numerous configuration options. REIN\'s implementation allows for the following settings to be set using data attributes:
                                <ul>
                                    <li><code>data-slidesPerView</code> determines the number of slides visible in the pane. Default is 1.</li>
                                    <li><code>data-freemode</code> allows for scrolling of multiple slides at once. Default is false.</li>
                                    <li><code>data-loop</code> determines whether or not the slides loop. Default is false.</li>
                                    <li><code>data-spaceBetween</code> determines the amount of space between slide. Default is 0.</li>
                                    <li><code>data-autoplay</code> can be used to cause the swiper to switch slides automatically. Default is <code>null</code>. To enable autoplay, use a number to indicate the delay between slide animations.</li>
                                    <li><code>data-zoom</code> enables zoom functionality for touch devices. Default is false.</li>
                                    <li><code>data-paginationType</code> determines the appearance of the slide pagination. Options are <code>bullets</code>, <code>fraction</code>, or <code>progress</code>. To add the pagination indicator to the widget you must include the <code>&lt;div class="swiper-pagination"&gt;&lt;/div&gt;</code> element in the widget markup.</li>
                                    <li><code>data-effect</code> determines the animation effect used to switch slides. Default is <code>slide</code>, and options include <code>fade</code>, <code>cube</code>, <code>coverflow</code> or <code>flip</code>.</li>
                                    <li><code>data-speed</code> determines the duration of the slide animation. Default is 300.</li>
                                    <li><code>data-parallax</code> is an advanced option which can be used to enable a parallax effect background to the slides. Default is false. The parallax effect requires an additional element in the <code>swiper-container</code> or <code>swiper-slide</code> element. Refer to the <a href="http://idangero.us/swiper/api/">Swiper documentation</a> for more information about configuring a parallax effect.</li>
                                </ul>
                            </li>
                            <li>When using mixed text and media content inside the swiper slides, add an inner <code>swiper-text-wrapper</code> element to provide additional margins, so that the navigation arrows don\'t overlap the text.</li>
                            <li>When using the swiper with text contents, keep the height of the slides contents as consistent as possible, to avoid obscuring navigation and pagination and confusing the learner.
                            </ul>';
$string['swipertextpreview'] = 'Swiper with mixed text and media contents';
$string['swiperimgpreview'] = 'Swiper with image contents';
$string['swipertextviewmarkup'] = 'View swiper with text slides markup';
$string['swiperimageviewmarkup'] = 'View swiper with image slides markup';
