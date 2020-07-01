<?php

namespace Drupal\fakeobjects\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "fakeobjects" plugin.
 *
 * @CKEditorPlugin(
 *   id = "fakeobjects",
 *   label = @Translation("FakeObjects"),
 * )
 */
class FakeObjects extends CKEditorPluginBase {
  /**
   * {@inheritdoc}
   */
  public function getFile() {
      return drupal_get_path('module', 'fakeobjects') . '/js/plugins/fakeobjects/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [];
  }
}
