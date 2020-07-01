<?php

namespace Drupal\custom_dealer\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\chinese_address\chineseAddressHelper;
use Drupal\Core\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "dealer_rest_resource",
 *   label = @Translation("Dealer rest resource"),
 *   uri_paths = {
 *     "canonical" = "/api/dealer"
 *   }
 * )
 */
class DealerRestResource extends ResourceBase {

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
   * Constructs a new DealerRestResource object.
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
      $container->get('logger.factory')->get('custom_dealer'),
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
      $cache_metadata = \Drupal\Core\Cache\CacheableMetadata::createFromRenderArray($this->build);

      if (!$this->currentUser->hasPermission('access content')) {
          throw new AccessDeniedHttpException();
      }

      $province = intval(\Drupal::request()->query->get('province', 0));

      $addressObj = new chineseAddressHelper();

      $connection = Database::getConnection();

      if ($province) {
          $dealer = array();
          //relate tables
          $query = $connection->select("node__field_dizhi", "n");
          $query->leftJoin('node_field_data', 'nfd', 'nfd.nid = n.entity_id');
          $query->leftJoin('node__body', 'nb', 'nb.entity_id = n.entity_id');
          $query->leftJoin('chinese_address', 'ca', 'ca.id = n.field_dizhi_city');

          //query
          $query->addField('n', 'field_dizhi_county', 'county');
          $query->addField('n', 'field_dizhi_city', 'city_id');
          $query->addField('ca', 'name', 'city');
          $query->addField('nfd', 'nid');
          $query->addField('nfd', 'title');
          $query->addField('nb', 'body_value', 'content');

          $query->condition("n.field_dizhi_province", $province, "=");

          $query->orderBy("n.field_dizhi_city", "ASC");

          $res = $query->execute(); // execute the query


          while($record = $res->fetchAssoc()) {
              $record['address'] = $addressObj::_chinese_address_get_name($record['county']);
              unset($record['street']) ;
              $record['content'] = preg_replace("/<p.*?>|<\/p>/is","", $record['content']);
              $dealer[$record['city']][] = $record;
          }

          $dealerList = array();
          $i = 0;
          foreach ($dealer as $key=> $item) {
              $dealerList[$i]['city'] = $key;
              $dealerList[$i]['info'] = $item;
              $i++;
          }

          return (new ResourceResponse($dealerList))->addCacheableDependency($cache_metadata);

      }

      $addressList = $addressObj::chinese_address_get_location();

      $provinces = $connection->select("node__field_dizhi", "n")->fields('n',array('field_dizhi_province') )->groupBy('field_dizhi_province')->execute()->fetchCol();

      $address = array();

      $i = 0;
      foreach ($addressList as $key=> $val) {
          if (in_array($key, $provinces)) {
              $address[$i]['id'] = $key;
              $address[$i]['name'] = $val;
              $i++;
          }
      }

      return (new ResourceResponse($address))->addCacheableDependency($cache_metadata);

  }

}
