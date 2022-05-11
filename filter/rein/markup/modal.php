<!--
Modal window link:
data-modaldetail used to specify the id of the modal window.
title used to specify the modal window title (optional).
href can be used as a fallback to a link to embedded content or can simply be #.
-->
Click <a class="do-modal rein-plugin" data-modaldetail="modalID" title="Title Here" href="#">here</a> to open a modal.
<!--
Modal window content:
id must match the data-modaldetail in the modal link.
-->
<div id="modalID" class="modal-detail">
Modal window content here.
</div>
<!-- end modal window content -->