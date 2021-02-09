<?php

declare(strict_types = 1);

namespace Drupal\helfi_ahjo\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionLogEntityTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\helfi_api_base\Entity\RemoteEntityBase;
use Drupal\helfi_tpr\Entity\Service;
use Drupal\helfi_tpr\Entity\Unit;
use Hauki\Model\DataSource;

/**
 * Defines the hauki_resource entity class.
 *
 * @ContentEntityType(
 *   id = "hauki_resource",
 *   label = @Translation("Hauki - Opening Hour"),
 *   label_collection = @Translation("Hauki - Opening Hour"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\helfi_api_base\Entity\Access\RemoteEntityAccess",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\helfi_api_base\Entity\Routing\EntityRouteProvider",
 *     }
 *   },
 *   base_table = "hauki_resource",
 *   data_table = "hauki_resource_field_data",
 *   revision_table = "hauki_resource_revision",
 *   revision_data_table = "hauki_resource_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer remote entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "langcode" = "langcode",
 *     "uid" = "uid",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   revision_metadata_keys = {
 *     "revision_created" = "revision_timestamp",
 *     "revision_user" = "revision_user",
 *     "revision_log_message" = "revision_log"
 *   },
 *   links = {
 *     "canonical" = "/hauki-resource/{hauki_resource}",
 *     "edit-form" = "/admin/content/hauki-resource/{hauki_resource}/edit",
 *     "delete-form" = "/admin/content/hauki-resource/{hauki_resource}/delete",
 *     "collection" = "/admin/content/hauki-resource"
 *   },
 *   field_ui_base_route = "hauki_resource.settings"
 * )
 */
final class Resource extends RemoteEntityBase {

  use RevisionLogEntityTrait;

  /**
   * Adds the given data source.
   *
   * @param \Hauki\Model\DataSource $source
   *   The data source.
   *
   * @return $this
   *   The self.
   */
  public function addOrigin(DataSource $source) : self {
    if (!$this->hasOrigin($source)) {
      $this->get('origins')->appendItem($source);
    }
    return $this;
  }

  /**
   * Removes the given source.
   *
   * @param \Hauki\Model\DataSource $source
   *   The data source.
   *
   * @return $this
   *   The self.
   */
  public function removeOrigin(DataSource $source) : self {
    $index = $this->getOriginIndex($source);
    if ($index !== FALSE) {
      $this->get('origins')->offsetUnset($index);
    }
    return $this;
  }

  /**
   * Checks whether the source exists or not.
   *
   * @param \Hauki\Model\DataSource $source
   *   The source.
   *
   * @return bool
   *   Whether we have given source or not.
   */
  public function hasOrigin(DataSource $source) : bool {
    return $this->getOriginIndex($source) !== FALSE;
  }

  /**
   * Gets the index of the given source.
   *
   * @param \Hauki\Model\DataSource $source
   *   The source.
   *
   * @return int|bool
   *   The index of the given source, or FALSE if not found.
   */
  protected function getOriginIndex(DataSource $source) {
    $values = $this->get('origins')->getValue();
    $ids = array_map(function ($value) {
      return $value['value'];
    }, $values);

    return array_search($source->getId(), $ids);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Title'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDefaultValue('')
      ->setCardinality(1)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ]);

    return $fields;
  }

}
