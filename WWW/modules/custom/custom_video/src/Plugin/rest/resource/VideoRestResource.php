<?php

namespace Drupal\custom_video\Plugin\rest\resource;

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
 *   id = "video_rest_resource",
 *   label = @Translation("Video rest resource"),
 *   uri_paths = {
 *     "canonical" = "/api/video"
 *   }
 * )
 */
class VideoRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
    protected $currentUser;
    protected $page_size = 10;
    private $bundle = 'shipinfenlei';
    private $build = [
        '#cache' => [
            'contexts' => ['url.query_args'],
            //'tags' => ['node:1', 'node_list'],
            'max-age' => 0,
        ],
    ];

  /**
   * Constructs a new VideoRestResource object.
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
      $container->get('logger.factory')->get('custom_video'),
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

        $nid = intval(\Drupal::request()->query->get('nid'), 0);
        $page = intval(\Drupal::request()->query->get('page', 1));
        $tid = intval(\Drupal::request()->query->get('tid', 0));
        $pageSize = intval(\Drupal::request()->query->get('page_size', 10));

        $data = array();

        $connection = Database::getConnection();

        if ($nid) {
            $res = $this->statistics($nid);
            $data = $this->getNode(0, $nid);
        } else {
            if ($tid) {
                $pageOffset = ($page-1)*$pageSize;
                $data = $this->getNode($tid, 0, $pageOffset);
            } else {
                $query = $connection->select("taxonomy_term_field_data", "t");
                $query->fields('t', array('tid', 'name'));
                $query->condition("t.vid", $this->bundle, "=");
                $query->orderBy('t.weight', 'ASC');
                $res = $query->execute();


                $i = 0;
                while($record = $res->fetchAssoc()) {
                    if (!$i) {
                        $record['list'] = $this->getNode($record['tid']);
                    }
                    $data[$i] = $record;
                    $i++;
                }
            }

        }

        return (new ResourceResponse($data))->addCacheableDependency($cache_metadata);
    }

    private function getNode($tid = 0, $nid = 0, $pageOffset = 0, $pageSize = 10) {
        if (!$tid && !$nid)  return false;
        $connection = Database::getConnection();
        $query = $connection->select("taxonomy_index", "t");
        $query->leftJoin('node_field_data', 'nfd', 'nfd.nid = t.nid');
        $query->leftJoin('node__body', 'nb', 'nb.entity_id = t.nid');
        $query->leftJoin('node__field_beijingtu', 'nfb', 'nfb.entity_id = t.nid');
        $query->leftJoin('node__field_bofangliang', 'nfbl', 'nfbl.entity_id = t.nid');

        $query->addField('t', 'nid');
        $query->addField('nfd', 'title');
        $query->addField('nb', 'body_value', 'video');
        $query->addField('nfbl', 'field_bofangliang_value', 'total');
        $query->addField('nfb', 'field_beijingtu_target_id', 'img_url');
        if ($tid) $query->condition("t.tid", $tid, "=");
        if ($nid) $query->condition("t.nid", $nid, "=");
        $query->orderBy("nfd.created", "ASC");
        $query->range($pageOffset, $pageSize);

        $res = $query->execute();

        $data = array();
        while($record = $res->fetchAssoc()) {
            $record['total'] = intval($record['total']);
            $image_file = \Drupal\file\Entity\File::load($record['img_url']);
            $uri = $image_file->uri;
            $url = $uri->value;

            if (is_string($url)) {
                $record['img_url'] = file_create_url($url);
            }

            $data[] = $record;
        }
        return $data;
    }

    protected function statistics($nid){
        $connection = Database::getConnection();
        $query = $connection->select("node__field_bofangliang", "t");
        $query->addField('t', 'field_bofangliang_value');
        $res = $query->execute()->fetchField();

        $total = 1;
        if ($res) {
            $total = intval($res) + 1;
        }

        $res = db_merge('node__field_bofangliang')
            ->key(array('entity_id' => $nid))
            ->fields(array(
                'bundle' => 'shipinzhongxin',
                'revision_id' => 0,
                'langcode' => 'zh-hans',
                'delta' => 0,
                'field_bofangliang_value' => $total
            ))->execute();

        return $res;
    }

}
