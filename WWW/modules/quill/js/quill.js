(function ($, Drupal) {
  'use strict';

  Drupal.editors.quill = {
    attach: function (element, format) {
      var $element = $('#' + element.id);
      var settings = format.editorSettings;


      $element.wrap('<div class="ql-wrapper" />');
      var $parent = $element.parent();
      $parent.prepend('<div class="ql-editable ql-' + element.id + '">' + $element.val() +'</div>');
      $element.addClass('ql-field');
      $element.hide();

      var quill = new Quill('.ql-' + element.id, {
        placeholder: settings.placeholder,
        theme: settings.theme
      });
    },

    detach: function (element, format, trigger) {
      var $element = $('#' + element.id);
      // Do not destroy the editor if we are updating the underlying element.
      if (trigger === 'serialize') {
        return;
      }

      // Otherwise, remove the Quill editor elements.
      $element.show();
      $element.siblings('.ql-editable').unwrap();
      $element.siblings('.ql-editable').remove();
      $element.siblings('.ql-tooltip').remove();
      $element.siblings('.ql-toolbar').remove();
      $element.siblings('.ql-editor').remove();
    },

    onChange: function (element, callback) {
      // Update textarea value when the editor div is loaded or changed.
      $('.ql-wrapper').each(function(){
        var quillText = $(this).find('.ql-editor').html();
        var textArea = $(this).find('textarea');
        textArea.attr('data-editor-value-is-changed', 'true');
        textArea.val(quillText);
      });

      // Update the value of the textarea when the ql-editor DOM subtree is
      // modified. This is triggered when the user types into the editor div.
      $('.ql-editor').off().on('DOMSubtreeModified', function() {
        var $textarea = $(this).closest('.ql-wrapper').find('textarea');
        $textarea.val($(this).html());
        $textarea.attr( 'data-editor-value-is-changed', 'true' );
      });
    }
  };

})(jQuery, Drupal);
