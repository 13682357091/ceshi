<?php

namespace Drupal\custom_service\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "service_rest_resource",
 *   label = @Translation("Service rest resource"),
 *   uri_paths = {
 *     "canonical" = "/rest/service"
 *   }
 * )
 */
class ServiceRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  private $bundle = 'service_instructions';

    private $build = [
        '#cache' => [
            'contexts' => ['url.query_args'],
            //'tags' => ['node:1', 'node_list'],
            'max-age' => 0,
        ],
    ];

  /**
   * Constructs a new ServiceRestResource object.
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
      $container->get('logger.factory')->get('custom_service'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get() {
      header('Access-Control-Allow-Origin:*');
      $cache_metadata = \Drupal\Core\Cache\CacheableMetadata::createFromRenderArray($this->build);
      if (!$this->currentUser->hasPermission('access content')) {
          throw new AccessDeniedHttpException();
      }

      $nid = intval(\Drupal::request()->query->get('nid'), 0);

      $result = array(
          'status' => 200,
          'data' => array()
      );

      $connection = Database::getConnection();

      if ($nid) {
          $query = $connection->select("node__body", "n");
          $query->fields('n', array('body_value'));
          $query->condition("n.entity_id", $nid, "=");
          $query->condition("n.bundle", $this->bundle, "=");
          $data = $query->execute()->fetchField();
          if (strpos($data, 'img') !== false) {
              $data = $this->replaceImgUri($data);
          }
      } else {
          $query = $connection->select("taxonomy_term_field_data", "t");
          $query->fields('t', array('tid', 'name'));
          $query->condition("t.vid", "service_instructions", "=");
          $query->orderBy('t.weight', 'ASC');
          $res = $query->execute();

          $data = array();
          $i = 0;
          while($record = $res->fetchAssoc()) {
              $node = $this->getNode($record['tid']);

              $data[$i]['name'] = $record['name'];
              if (count($node) == 1) {
                  $data[$i]['has_child'] = 0;
                  $data[$i]['nid'] = $node[0]['nid'];
              } else {
                  $data[$i]['has_child'] = 1;
                  $data[$i]['list'] = $node;
              }

              $i++;

          }

      }

      $result['data'] = $data;

      return (new ResourceResponse($result))->addCacheableDependency($cache_metadata);
  }
  
  private function getNode($tid = 0) {
      if (!$tid)  return false;
      $connection = Database::getConnection();
      $query = $connection->select("taxonomy_index", "t");
      $query->leftJoin('node_field_data', 'nfd', 'nfd.nid = t.nid');
      $query->fields('nfd', array('nid', 'title'));
      $query->condition("t.tid", $tid, "=");
      $query->condition("nfd.type", $this->bundle, "=");
      $query->orderBy("nfd.created", "ASC");

      $res = $query->execute();

      $data = array();
      while($record = $res->fetchAssoc()) {
          $data[] = $record;
      }

      return $data;
  }

    /**
     * replace src
     * @param string $str
     * @return mixed|string
     */
    function replaceImgUri($str = '') {
        preg_match_all('/<img[^>]+>/i', $str, $images);
        foreach ($images as $image) {
            $secureImg = str_replace('src="', (isset($_SERVER['HTTPS']) ? "src=\"https" : "src=\"http") . "://$_SERVER[HTTP_HOST]", $image);
            $str = str_replace($image, $secureImg, $str);
        }
        return $str;
    }

}
