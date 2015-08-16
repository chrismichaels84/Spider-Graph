<?php
namespace Spider\Test\Unit\Commands\Builders\Builder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Graphs\ID as TargetID;
use InvalidArgumentException;

class RetrieveTest extends TestSetup
{
    use Specify;

    /* Retrieval Tests */
    public function testSelectAndTarget()
    {
        $this->specify("it returns specified data using a SELECT projections array", function () {
            $actual = $this->builder
                ->select(['price', 'certified'])
                ->record("#12:6767")// byId() alias
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => ['price', 'certified'],
                'target' => new TargetID("#12:6767")
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns specified data using a only", function () {
            $actual = $this->builder
                ->select()
                ->record("#12:6767")// byId() alias
                ->only(['price', 'certified'])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => ['price', 'certified'],
                'target' => new TargetID("#12:6767")
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns records using `from()`", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V"
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testWhereSugars()
    {
        $this->specify("it adds several AND WHERE constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('name', 'michael')
                ->andWhere('last', 'wilson')
                ->andWhere('certified', true)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['last', Bag::COMPARATOR_EQUAL, "wilson", Bag::CONJUNCTION_AND],
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds several OR WHERE constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('name', 'michael')
                ->orWhere('last', 'wilson')
                ->orWhere('certified', true)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['last', Bag::COMPARATOR_EQUAL, "wilson", Bag::CONJUNCTION_OR],
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_OR]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testLimitSugars()
    {
        $this->specify("it gets first records", function () {
            $actual = $this->builder
                ->select()
                ->from('v')
                ->first()
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "v",
                'limit' => 1
            ]);

            $this->assertEquals($expected, $actual, 'failed to return correct command');
        });
    }
}
