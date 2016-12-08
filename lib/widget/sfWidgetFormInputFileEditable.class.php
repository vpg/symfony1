<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormInputFileEditable represents an upload HTML input tag with the possibility
 * to remove a previously uploaded file.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWidgetFormInputFileEditable extends sfWidgetFormInputFile
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * file_src:     The current image web source path (required)
   *  * edit_mode:    A Boolean: true to enabled edit mode, false otherwise
   *  * is_image:     Whether the file is a displayable image
   *  * with_delete:  Whether to add a delete checkbox or not
   *  * delete_label: The delete label used by the template
   *  * template:     The HTML template to use to render this widget when in edit mode
   *                  The available placeholders are:
   *                    * %input% (the image upload widget)
   *                    * %delete% (the delete checkbox)
   *                    * %delete_label% (the delete label text)
   *                    * %file% (the file tag)
   *
   * In edit mode, this widget renders an additional widget named after the
   * file upload widget with a "_delete" suffix. So, when creating a form,
   * don't forget to add a validator for this additional field.
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInputFile
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->setOption('type', 'file');
    $this->setOption('needs_multipart', true);

    $this->addRequiredOption('file_src');
    $this->addOption('is_image', false);
    $this->addOption('edit_mode', true);
    $this->addOption('with_delete', true);
    $this->addOption('with_preview', false);
    $this->addOption('preview', array());
    $this->addOption('delete_label', 'remove the current file');
    $this->addOption('template', '%file%<br />%input%<br />%delete% %delete_label%');
  }

  /**
   * Renders the widget.
   *
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $input = parent::render($name, $value, $attributes, $errors);

    if (!$this->getOption('edit_mode'))
    {
      return $input;
    }

    if ($this->getOption('with_delete'))
    {
      $deleteName = ']' == substr($name, -1) ? substr($name, 0, -1).'_delete]' : $name.'_delete';

      $delete = $this->renderTag('input', array_merge(array('type' => 'checkbox', 'name' => $deleteName), $attributes));
      $deleteLabel = $this->translate($this->getOption('delete_label'));
      $deleteLabel = $this->renderContentTag('label', $deleteLabel, array_merge(array('for' => $this->generateId($deleteName))));
    }
    else
    {
      $delete = '';
      $deleteLabel = '';
    }

    // Preview
    $this->setPreview();

    return strtr($this->getOption('template'), array('%input%' => $input, '%delete%' => $delete, '%delete_label%' => $deleteLabel, '%file%' => $this->getFileAsTag($attributes)));
  }

  protected function getFileAsTag($attributes)
  {
    if ($this->getOption('is_image'))
    {
      return false !== $this->getOption('file_src') ? $this->renderTag('img', array_merge(array('src' => $this->getOption('file_src')), $attributes)) : '';
    }
    else
    {
      return $this->getOption('file_src');
    }
  }

  /**
   * Set image preview
   */
  private function setPreview()
  {
    $previewData = $this->getOption('preview');
    if ($this->getOption('with_preview') && !empty($previewData) && isset($previewData['isNew'])
      && isset($previewData['imgDir']) && isset($previewData['value']) && isset($previewData['objectId']) && isset($previewData['moduleName'])
    )
    {
      if (!$previewData['isNew'] && $previewData['value'])
      {
        $imgPath = '/images/' . sfConfig::get('sf_upload_dir_name') . '/' . $previewData['imgDir'] . '/' . $previewData['value'];
        $deleteButton  = '';
        if ($this->getOption('with_delete'))
        {
          $deleteButton = '<br/><a href="#"
                onclick="
                  if (confirm(\'' . __("Etes-vous sûr ?") . '\')) {
                    $.ajax({
                      url:\'' . url_for($previewData['moduleName'] . '/removeImage?id=' . $previewData['objectId']) . '\',
                      success: function(){
                        $(\'#picture_' . $previewData['objectId'] . '_' . 'image_preview\').remove();
                        $(\'#picture[' . 'image' . ']\').value=\'\';
                      }
                  });
                }">
              <img src="/images/admingen/delete.png" border=0/>&nbsp;' . __("Supprimer l'image") . '</a><br />';
        }
        $this->setOption(
          'template',
          '<div id="picture_' . $previewData['objectId'] . '_' . 'image_preview">
              <a href="' . $imgPath . '" class="lightbox">
                <img align="absbottom" src="' . $imgPath . '" width="100" />
              </a>
                '. $deleteButton .'
           </div> %input% <br />
           %delete% %delete_label%'
        );
      }
    }
  }
}
