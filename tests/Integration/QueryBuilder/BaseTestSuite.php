<?php
namespace Spider\Test\Integration\QueryBuilder;

use Codeception\Specify;
use Spider\Test\Fixtures\Graph;

abstract class BaseTestSuite extends \PHPUnit_Framework_TestCase
{
    use Specify;

    /** @var  \Spider\Commands\Query */
    protected $query;

    public function testBasicSelects()
    {
        $this->specify("it selects all records", function () {
            $response = $this->query
                ->select()
                ->from('person')
                ->all();

            $expected = array_filter(Graph::$data, function ($record) {
                return $record['label'] === 'person';
            });

            $this->assertTrue(is_array($response), 'failed to return an array');
            $this->assertCount(count($expected), $response, 'failed to return the correct number of records');
            $this->assertInstanceOf('Spider\Base\Collection', $response[0], 'failed to return an array of collections');
            $this->assertEquals($expected[0]['name'], $response[0]->name, 'failed to return correct first collection');
        });

        $this->specify("it selects one record with first", function () {
            $response = $this->query
                ->select()
                ->from('person')
                ->first();

            $expected = array_filter(Graph::$data, function ($record) {
                return $record['label'] === 'person';
            });

            $this->assertFalse(is_array($response), 'failed to return a collection array');
            $this->assertInstanceOf('Spider\Base\Collection', $response, 'failed to return an array of collections');
            $this->assertEquals($expected[0]['name'], $response->name, 'failed to return correct first collection');
        });

        $this->specify("it selects one record with one", function () {
            $response = $this->query
                ->select()
                ->from('person')
                ->one();

            $expected = array_filter(Graph::$data, function ($record) {
                return $record['label'] === 'person';
            });

            $this->assertFalse(is_array($response), 'failed to return a collection array');
            $this->assertInstanceOf('Spider\Base\Collection', $response, 'failed to return an array of collections');
            $this->assertEquals($expected[0]['name'], $response->name, 'failed to return correct first collection');
        });

        $this->specify("it selects with constraints", function () {
            $response = $this->query
                ->select()
                ->from('person')
                ->where('name', 'marko')
                ->andWhere('age', 29)
                ->all();
            /* See Issue 40 for a discussion about what all() should return */
            /* Fix this test if decision is made */

            $expected = array_filter(Graph::$data, function ($record) {
                return $record['label'] === 'person'
                    && $record['name'] === 'marko'
                    && $record['age'] === 29;
            });

            $this->assertFalse(is_array($response), 'failed to return an array');
            $this->assertInstanceOf('Spider\Base\Collection', $response, 'failed to return an array of collections');
            $this->assertEquals($expected[0]['name'], $response->name, 'failed to return correct first collection');
        });

        $this->specify("it selects with OR constraints", function () {
            $response = $this->query
                ->select()
                ->from('person')
                ->where('name', 'marko')
                ->orWhere('name', 'peter')
                ->all();
            /* See Issue 40 for a discussion about what all() should return */
            /* Fix this test if decision is made */

            $expected = array_filter(Graph::$data, function ($record) {
                return $record['label'] === 'person'
                && ($record['name'] === 'marko' || $record['name'] === 'peter');
            });

            $expected = array_values($expected);

            $this->assertTrue(is_array($response), 'failed to return an array');
            $this->assertCount(count($expected), $response, 'failed to return the correct number of records');

            $this->assertInstanceOf('Spider\Base\Collection', $response[0], 'failed to return an array of collections');
            $this->assertEquals($expected[0]['name'], $response[0]->name, 'failed to return correct first collection');

            $this->assertInstanceOf('Spider\Base\Collection', $response[1], 'failed to return an array of collections');
            $this->assertEquals($expected[1]['name'], $response[1]->name, 'failed to return correct first collection');
        });

        $this->specify("it selects with limits", function () {
            $response = $this->query
                ->select()
                ->from('person')
                ->limit(3)
                ->get();

            $expected = array_filter(Graph::$data, function ($record) {
                return $record['label'] === 'person';
            });

            $expected = array_slice($expected, 0, 3);

            $this->assertTrue(is_array($response), 'failed to return an array');
            $this->assertCount(3, $response, 'failed to return the correct number of records');

            $this->assertInstanceOf('Spider\Base\Collection', $response[0], 'failed to return an array of collections');
            $this->assertEquals($expected[0]['name'], $response[0]->name, 'failed to return correct first collection');

            $this->assertInstanceOf('Spider\Base\Collection', $response[1], 'failed to return an array of collections');
            $this->assertEquals($expected[1]['name'], $response[1]->name, 'failed to return correct first collection');

            $this->assertInstanceOf('Spider\Base\Collection', $response[2], 'failed to return an array of collections');
            $this->assertEquals($expected[2]['name'], $response[2]->name, 'failed to return correct first collection');
        });

        $this->specify("it selects with limits with `set()`", function () {
            $response = $this->query
                ->select()
                ->from('person')
                ->limit(4)
                ->get();

            $expected = array_filter(Graph::$data, function ($record) {
                return $record['label'] === 'person';
            });

            $expected = array_slice($expected, 0, 4);

            $this->assertTrue(is_array($response), 'failed to return an array');
            $this->assertCount(4, $response, 'failed to return the correct number of records');

            $this->assertInstanceOf('Spider\Base\Collection', $response[0], 'failed to return an array of collections');
            $this->assertEquals($expected[0]['name'], $response[0]->name, 'failed to return correct first collection');

            $this->assertInstanceOf('Spider\Base\Collection', $response[1], 'failed to return an array of collections');
            $this->assertEquals($expected[1]['name'], $response[1]->name, 'failed to return correct first collection');

            $this->assertInstanceOf('Spider\Base\Collection', $response[2], 'failed to return an array of collections');
            $this->assertEquals($expected[2]['name'], $response[2]->name, 'failed to return correct first collection');
        });
    }

    public function testInsertAndDeleteDispatches()
    {
        $this->specify("it inserts a single record", function () {
            $record = [
                'first' => 'first-value',
                'second' => 'second-value'
            ];

            $this->query
                ->into('person')
                ->insert($record);

            $response = $this->query
                ->select()
                ->from('person')
                ->where('first', 'first-value')
                ->one();

            $this->assertInstanceOf('Spider\Base\Collection', $response, "failed to return one record");
            $this->assertEquals("second-value", $response->second, "failed to create record");

            // Clean up
            $this->query
                ->drop($response->id);
        });

//        $this->specify("it inserts multiple records", function () {
//            $records = [
//                ['new' => 'yes', 'first' => 'first-value', 'A', 'a'],
//                ['new' => 'yes', 'second' => 'second-value', 'B', 'b']
//            ];
//
//            $this->query
//                ->into('person')
//                ->multipleInsert($records);
//
//            $response = $this->query
//                ->select()
//                ->from('person')
//                ->where('new', 'yes')
//                ->all();
//
//            $this->assertTrue(is_array($response), "failed to return an array");
//            $this->assertCount(2, $response, "failed to return 2 records");
//
//            $this->assertInstanceOf('Spider\Base\Collection', $response[0], "failed to return one record");
//            $this->assertEquals("first-value", $response[0]->first, "failed to create record");
//
//            $this->assertInstanceOf('Spider\Base\Collection', $response[1], "failed to return one record");
//            $this->assertEquals("second-value", $response[1]->second, "failed to create record");
//
//            // Clean up
//            $this->query
//                ->drop()
//                ->where('new', 'yes')
//                ->all();
//        });
    }

    /*
     * Retrieval methods to test:
     * path(), tree(), scalar(), command(), dispatch()
     */

    /*
     * Create methods to test:
     * insertMultiple()
     * insert()->data()->into()->all();
     */

    /*
     * Drop methods to test:
     * drop()->record(id)->go()
     * drop([id, id, id])
     * drop()->records([id, id, id])->go()
     */

    /*
     * Update methods to test:
     * updateFirst('target')->where()->data()->go()
     * updateFirst('target')->where()->withData()->go()
     * update('key', 'value')->where()->from()->limit()->go()
     * update(['key' => 'value'])->record(id)->go()
     */
}
