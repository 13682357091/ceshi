<?php

namespace Drupal\custom_rest\Plugin\rest\resource;

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
 *   id = "custom_rest_resource",
 *   label = @Translation("Custom rest resource"),
 *   uri_paths = {
 *     "canonical" = "/restful/api"
 *   }
 * )
 */
class CustomRestResource extends ResourceBase {

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

  private $fieldTag;

  /**
   * Constructs a new CustomRestResource object.
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

  protected function getTitleTag($vid = '')
  {
      $tags = array();
      $connection = Database::getConnection();

      //relate tables
      $query = $connection->select("taxonomy_term_field_data", "td");
      $query->fields('td', array('tid'));
      if ($vid) $query->condition("td.vid ", $vid, "=");
      $query->orderBy('td.tid', 'ASC');
      $tags = $query->execute()->fetchCol('tid');
      $this->fieldTag = $tags;
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
      $container->get('logger.factory')->get('custom_rest'),
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
      $content =  \Drupal::request()->query->get('content');

      $has_total = intval(\Drupal::request()->query->get('has_total'));

      $title = \Drupal::request()->query->get('title');
      $page = intval(\Drupal::request()->query->get('page', 1));
      $nid = intval(\Drupal::request()->query->get('nid'), 0);
      $isPromote = isset ($_GET['is_promote']) ? intval(\Drupal::request()->query->get('is_promote'), 0) : null;
      $type = \Drupal::request()->query->get('type');
      $pageSize = intval(\Drupal::request()->query->get('page_size', 8));

      $pageOffset = ($page-1)*$pageSize;

      $connection = Database::getConnection();

      $articles = array();

      //relate tables
      $query = $connection->select("node", "n");
      $query->leftJoin('node_field_data', 'nfd', 'nfd.nid = n.nid');
      $query->leftJoin('node__body', 'nb', 'nb.entity_id = n.nid');
      $query->leftJoin('users_field_data', 'ufd', 'ufd.uid = nfd.uid');
      $query->leftJoin('node__field_original_author', 'nfo', 'nfo.entity_id = n.nid');
      $query->leftJoin('node__field_source_url', 'nfs', 'nfs.entity_id = n.nid');
      $query->leftJoin('node__field_tags', 'nft', 'nft.entity_id = n.nid');

      //query
      $query->addField('n', 'nid');
      $query->addField('nfd', 'title');
      $query->addField('nfd', 'created', 'add_time');
      $query->addField('nft', 'field_tags_target_id', 'tag_id');
      $query->addField('nfs', 'field_source_url_value', 'source_url');

      if ($nid) {
          $query->addField('nb', 'body_value', 'content');
          $query->addField('nfo', 'field_original_author_value', 'author');
          $query->addField('ufd', 'name', 'operator');
      }

      //query condition

      if ($type && $type == 'en') {
          $query->leftJoin('node__field_is_video', 'nv', 'nv.entity_id = n.nid');
          $query->leftJoin('node__field_is_review', 'nr', 'nr.entity_id = n.nid');

          $query->condition("nfd.type", "english_article", "=")
              ->condition("n.type", "english_article", "=");

          $query->addField('nv', 'field_is_video_value', 'is_video');
          $query->addField('nr', 'field_is_review_value', 'is_review');
      } else {
          $query->condition("nfd.type", "article", "=")
              ->condition("n.type", "article", "=");
      }

      $query->condition("nb.deleted", "0", "=") // $nid
      ->condition("nfd.status", "1", "=");

      if ($isPromote) {
          $query->isNotNull("nft.field_tags_target_id");
      } else {
          if (!is_null($isPromote)){
              $query->isNull("nft.field_tags_target_id");
          }
      }

      //search condition
      if ($content) $query->condition('nb.body_value', '%' . db_like($content) . '%', 'LIKE');
      if ($title)   $query->condition('nfd.title', '%' . db_like($title) . '%', 'LIKE');
      if ($nid)     $query->condition("n.nid", $nid, "=");

      //order
      if ($isPromote) {
          $query->orderBy("nft.field_tags_target_id", "ASC")->orderBy("nfd.created", "DESC");
      } else {
          $query->orderBy("nfd.created", "DESC");
      }


      $total = $query->countQuery()->execute()->fetchField();

      $pageCount = ceil($total/$pageSize);

      if ($page>$pageCount) {
          $data = array(
              'error' => 'no data'
          );
      } else {
          if(!$isPromote) $query->range($pageOffset, $pageSize);
          $res = $query->execute(); // execute the query

          if ($isPromote) $this->getTitleTag('meitibaodao');  //tags

          $has_main_promote = 0;
          while($record = $res->fetchAssoc()) {
              $record['img'] = $this->getNodeImage($record['nid']);

              $record['add_time_en'] = date('F jS  Y', $record['add_time']);

              $record['add_time'] = date('Y-m-d H:i:s', $record['add_time']);

              if (isset($record['content']) && strpos($record['content'], 'img') !== false) {
                  $record['content'] = $this->replaceImgUri($record['content']);
              }

              //promote article
              if ($isPromote) {
                  $record['promote'] = 1;
                  if (!$has_main_promote && isset($this->fieldTag[0]) && $record['tag_id'] == $this->fieldTag[0]) {
                      $record['is_main_promote'] = '1';
                      $has_main_promote = 1;
                  } else {
                      $record['is_main_promote'] = '0';
                  }
              }

              unset($record['tag_id']);
              $articles[] = $record;
          }

          if ($isPromote) {
              usort($articles, function($a, $b) {
                  return $b['add_time'] <=> $a['add_time'];
              });

              $articles = array_slice($articles,0, $pageSize);

          }

          if ($has_total) {
              $data['list'] = $articles;
              $data['page_total'] = $pageCount;
          } else {
              $data = $articles;
          }

      }

      return (new ResourceResponse($data))->addCacheableDependency($cache_metadata);
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

    /**
     * get article images
     * @param int $nodeId
     * @return array
     */
  protected function getNodeImage($nodeId = 0) {
      $imgUrls = array();

      if (!$nodeId) return $imgUrls;

      $connection = Database::getConnection();

      //relate tables
      $query = $connection->select("node__field_image", "nfi");
      $query->leftJoin('file_managed', 'fm', 'fm.fid = nfi.field_image_target_id');//
      $query->addField('nfi', 'field_image_target_id', 'img_url');
      $query->condition("nfi.entity_id ", $nodeId, "=");
      $res = $query->execute();
      while($imgUrl = $res->fetchField()) {
          $image_file = \Drupal\file\Entity\File::load($imgUrl);
          $uri = $image_file->uri;
          $url = $uri->value;

          if (is_string($url)) {
              $imgUrls[]= file_create_url($url);
          }
      }

      return $imgUrls;
  }

}
