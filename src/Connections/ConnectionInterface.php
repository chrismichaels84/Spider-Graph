<?php
namespace Spider\Connections;

use Michaels\Manager\Contracts\ManagesItemsInterface;
use Spider\Commands\CommandInterface;
use Spider\Drivers\DriverInterface;
use Spider\Drivers\Response;

/**
 * Facilitates two-way communication with a data-store
 * @package Spider\Test\Unit\Connections
 */
interface ConnectionInterface extends DriverInterface
{
    /**
     * Connects to the database
     */
    public function open();

    /**
     * Closes database connection
     */
    public function close();

    /**
     * Returns the class name of the active driver
     * @return string
     */
    public function getDriverName();

    /**
     * Returns the instance of the driver
     * @return DriverInterface
     */
    public function getDriver();

    /**
     * Updates the driver instance
     *
     * @param DriverInterface $driver
     */
    public function setDriver(DriverInterface $driver);

    /**
     * Passes to driver: executes a Query or read command
     *
     * @param CommandInterface|BaseBuilder $query
     * @return Response
     */
    public function executeReadCommand($query);

    /**
     * Passes to driver: executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param CommandInterface|BaseBuilder $command
     * @return Response
     */
    public function executeWriteCommand($command);

    /**
     * Passes to driver: executes a read command without waiting for a response
     *
     * @param CommandInterface|BaseBuilder $query
     * @return $this
     */
    public function runReadCommand($query);

    /**
     * Passes to driver: executes a write command without waiting for a response
     *
     * @param CommandInterface|BaseBuilder $command
     * @return $this
     */
    public function runWriteCommand($command);
}
