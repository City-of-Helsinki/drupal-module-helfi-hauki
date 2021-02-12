<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_hauki\Kernel;

use Drupal\helfi_hauki\Entity\Resource;
use Drupal\Tests\helfi_api_base\Kernel\MigrationTestBase;
use GuzzleHttp\Psr7\Response;

/**
 * Tests hauki migrations.
 *
 * @group helfi_hauki
 */
class MigrationTest extends MigrationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'link',
    'address',
    'text',
    'key_value_field',
    'helfi_hauki',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    parent::setUp();

    $this->installEntitySchema('hauki_resource');
    $this->installConfig(['helfi_hauki']);
  }

  /**
   * Create the resource migration.
   *
   * @return array
   *   List of entities.
   */
  protected function createResourceMigration() : array {
    $responses = [
      new Response(200, [], json_encode(['count' => 20, 'results' => []])),
    ];

    $id = 1;
    for ($page = 1; $page <= 2; $page++) {
      $response = [
        'count' => 20,
        'results' => [],
      ];

      for ($i = 1; $i <= 10; $i++) {
        $response['results'][] = [
          'id' => $id,
          'name' => [
            'fi' => 'Name fi ' . $id,
            'en' => 'Name en ' . $id,
            'sv' => 'Name sv ' . $id,
          ],
          'origins' => [
            [
              'data_source' => [
                'id' => 'tprek',
              ],
              'origin_id' => 'miscinfo-' . $id * 10,
            ],
          ],
        ];
        $id++;
      }
      $responses[] = new Response(200, [], json_encode($response));
    }


    $this->container->set('http_client', $this->createMockHttpClient($responses));
    $this->executeMigration('hauki_resource', [
      'source' => [
        'url' => 'https://hauki-test.oc.hel.ninja/v1/resource/?origin_id_exists=true&page_size=10',
      ],
    ]);
    return Resource::loadMultiple();
  }

  /**
   * Tests service migration.
   */
  public function testServiceMigration() : void {
    $entities = $this->createResourceMigration();
    $this->assertCount(20, $entities);

    foreach (['en', 'sv', 'fi'] as $langcode) {
      foreach ($entities as $entity) {
        $translation = $entity->getTranslation($langcode);
        $this->assertEquals($langcode, $translation->language()->getId());
        $this->assertEquals(sprintf('Name %s %s', $langcode, $translation->id()), $translation->label());
        $this->assertCount(1, $translation->getOrigins());
      }
    }
  }

}
