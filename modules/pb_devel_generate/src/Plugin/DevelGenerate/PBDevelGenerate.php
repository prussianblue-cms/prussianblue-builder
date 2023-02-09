<?php

namespace Drupal\pb_devel_generate\Plugin\DevelGenerate;

use Drupal\devel_generate\DevelGenerateBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides a PrussianBlue DevelGenerate plugin.
 *
 * @DevelGenerate(
 *   id = "pb_content",
 *   label = @Translation("PrussianBlue content"),
 *   description = @Translation("Generate content to test PrussianBlue layouts and paragraphs."),
 *   url = "pb_content",
 *   permission = "administer devel_generate",
 *   settings = {
 *   }
 * )
 */

class PBDevelGenerate extends DevelGenerateBase implements ContainerFactoryPluginInterface {

  /**
   * The Messenger service
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The Module Handler
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The constructor
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->messenger = \Drupal::messenger();
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $generators = $this->moduleHandler->invokeAll('pb_get_demo_generators');

    $options = [];
    foreach($generators as $generator_id => $generator_definition) {
      $options[$generator_id] = ['generator' => $generator_definition['label']];
    }

    $header = [
      'generator' => $this->t('Generator')
    ];

    $form['generators'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
    ];

    $form['title_length'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum number of words in titles'),
      '#default_value' => 4,
      '#required' => TRUE,
      '#min' => 1,
      '#max' => 255,
    ];

    $form['width'] = [
      '#type' => 'select',
      '#title' => $this->t('Content and background width'),
      '#options' => [
        'all' => $this->t('All variations'),
        'wide' => $this->t('Wide'),
        'wide--full-background' => $this->t('Wide - full background'),
        'narrow' => $this->t('Narrow'),
        'narrow--full-background' => $this->t('Narrow - full background')
      ],
      '#default_value' => 'wide--full-background',
    ];

    $form['balance'] = [
      '#type' => 'select',
      '#title' => $this->t('Balance'),
      '#description' => $this->t('Column that should be wider, for layouts with more than one column.'),
      '#options' => [
        'width-equal' => $this->t('Equal width'),
        'width-a-wider' => $this->t('Column a wider'),
      ],
      '#default_value' => 'width-equal',
    ];

    // Turn this into a select or radio to make it
    // possible to test the intro field
    $form['target_field'] = [
      '#type' => 'value',
      '#value' => 'field_pb_paragraphs_content',
    ];

    $form['kill'] = [
      '#type' => 'markup',
      '#markup' => $this->t('<strong>There is no automated way to delete the content generated.</strong> The generated content is meant to be disposable, for development purposes and not coexist with other content. To get rid of it, just reinstall the site.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsFormValidate(array $form, FormStateInterface $form_state) {
    $generators = array_filter($form_state->getValue('generators'));
    if(!array_filter($form_state->getValue('generators'))) {
      $form_state->setErrorByName('generators', $this->t('Please select at least one generator.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function generateElements(array $values) {
    // This method merely computes the variations neeeded and calls
    // the generator method of the classes that actually generate elements

    // TODO
    // HACK ALERT: it would be better if the width variations were an array or read form
    // the layouts yaml instead of hardcoding them twice in this file
    $width_variations = ['wide', 'wide--full-background', 'narrow', 'narrow--full-background'];

    $variations = [];
    $widths = ($values['width'] == 'all') ? $width_variations : [$values['width']];

    foreach($widths as $width) {
      $variations[] = [
        'width' => $width,
        'balance' => $values['balance']
      ];
    }

    // For each of the demos to be generated, iterate over the variations map, calling it once for each variation
    $generators = $this->moduleHandler->invokeAll('pb_get_demo_generators');
    foreach($variations as $current_variation) {
      foreach($generators as $generator_id => $generator_definition) {
        if($values['generators'][$generator_id]) {
          $generator_class = $generator_definition['class'];
          $generator_class::generate($current_variation, $values);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateDrushParams(array $args, array $options = []) {
    $this->setMessage('you have not validated drush params, therefore they are not valid');
    return [];
  }
}
