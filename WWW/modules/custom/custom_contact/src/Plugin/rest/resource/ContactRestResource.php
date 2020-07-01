<?php

namespace Drupal\custom_contact\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Database\Database;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "contact_rest_resource",
 *   label = @Translation("Contact rest resource"),
 *   uri_paths = {
 *     "canonical" = "/api/contact",
 *     "https://www.drupal.org/link-relations/create" = "/api/contact"
 *   }
 * )
 */

class ContactRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;
    private $build = [
        '#cache' => [
            'contexts' => ['url.query_args'],
            //'tags' => ['node:1', 'node_list'],
            'max-age' => 0,
        ],
    ];

  /**
   * Constructs a new ContactRestResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('custom_contact'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to POST requests.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data) {
      $cache_metadata = \Drupal\Core\Cache\CacheableMetadata::createFromRenderArray($this->build);

      if (!$this->currentUser->hasPermission('access content')) {
          throw new AccessDeniedHttpException();
      }

      $field_arr = array();

      $return_res = array(
          'status' => '200',
      );

      $field_arr['email'] = $data['email'];
      if ($data['type'] == 1) {
          $field_arr['name'] = $data['name'];
          $field_arr['message'] = $data['message'];
          $type = 'contact';
      } elseif ($data['type'] == 2) {
          $field_arr['address_line_1'] = $data['address_line_1'];
          $field_arr['address_line_2'] = $data['address_line_2'];
          $field_arr['country']        = $data['country'];
          $field_arr['issue_details']  = $data['issue_details'];
          $field_arr['name']           = $data['name'];
          $field_arr['order_id']       = $data['order_id'];
          $field_arr['phone_number']   = $data['phone_number'];
          $field_arr['serial_number']  = $data['serial_number'];
          $field_arr['state']          = $data['state'];
          $field_arr['zipcode']        = $data['zipcode'];
          $type = 'yonghufuwu';
      } else {
          $type = 'baoming';
      }

      $connection = Database::getConnection();
      $save_data['webform_id'] = $type;
      $save_data['langcode'] = 'zh-hans';
      $save_data['uri'] = '/form/' . $type;

      $save_time = time();
      $save_data['created']     = $save_time;
      $save_data['completed']   = $save_time;
      $save_data['changed']     = $save_time;
      $save_data['uuid']         = 'niming' . '_' . uniqid();
      $save_data['remote_addr'] = \Drupal::request()->getClientIp();//

      $query = $connection->select('webform_submission', 's');
      $query->addField('s', 'serial');
      $query->condition('webform_id', $type, '=');
      $query->orderby('serial','DESC')->range(0,1);
      $res = $query->execute();

      $save_data['serial'] = intval($res->fetchCol())+1;

      try{
          $id = $connection->insert('webform_submission')
              ->fields($save_data)
              ->execute();
      }catch (\Exception $e) {
          $error = $e->getMessage();

          $return_res = array(
             'status' => '502',
              'msg'   => $error
          );
          return (new ResourceResponse($return_res))->addCacheableDependency($cache_metadata);
      }


      if ($id && $field_arr && is_array($field_arr)) {
          $save_field = array();
          foreach ($field_arr as $k => $val) {
              $save_field['name'] = $k;
              $save_field['value'] = $val;
              $save_field['webform_id'] = $type;
              $save_field['sid'] = $id;

              try{
                  $connection->insert('webform_submission_data')
                      ->fields($save_field)
                      ->execute();
              }catch (\Exception $e) {
                  $error = $e->getMessage();
                  $return_res = array(
                      'status' => '502',
                      'msg'   => $error
                  );
                  return (new ResourceResponse($return_res))->addCacheableDependency($cache_metadata);
              }

          }
      }

      return (new ResourceResponse($return_res))->addCacheableDependency($cache_metadata);
  }

}
