<?php
/**
 * Shared toolbar for the full rich-text editors (cohort/event description and
 * the cohort article body). The buttons drive the same `data-command` handling
 * in admin.js that the editor has always used.
 *
 * Expects: $label — accessible name for the toolbar.
 */
$label = $label ?? 'Formatting toolbar';
?>
<div class="wysiwyg-toolbar wysiwyg-toolbar-rich" aria-label="<?= e($label) ?>">
  <button type="button" data-command="formatBlock" data-value="p" title="Paragraph">P</button>
  <button type="button" data-command="formatBlock" data-value="h2" title="Heading 2">H2</button>
  <button type="button" data-command="formatBlock" data-value="h3" title="Heading 3">H3</button>
  <span class="wysiwyg-sep" aria-hidden="true"></span>
  <button type="button" data-command="bold" title="Bold"><strong>B</strong></button>
  <button type="button" data-command="italic" title="Italic"><em>I</em></button>
  <span class="wysiwyg-sep" aria-hidden="true"></span>
  <button type="button" data-command="insertUnorderedList" title="Bullet list">List</button>
  <button type="button" data-command="insertOrderedList" title="Numbered list">1. List</button>
  <button type="button" data-command="formatBlock" data-value="blockquote" title="Quote">Quote</button>
  <span class="wysiwyg-sep" aria-hidden="true"></span>
  <button type="button" data-command="justifyLeft" title="Align left">&#8676;</button>
  <button type="button" data-command="justifyCenter" title="Align centre">&#8596;</button>
  <button type="button" data-command="justifyRight" title="Align right">&#8677;</button>
  <span class="wysiwyg-sep" aria-hidden="true"></span>
  <button type="button" data-command="createLink" title="Add link">Link</button>
  <button type="button" data-command="unlink" title="Remove link">Unlink</button>
  <button type="button" data-command="removeFormat" title="Clear formatting">Clear</button>
</div>
