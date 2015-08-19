<?php
namespace Spider\Connections;

use Spider\Base\Collection;
use Spider\Commands\CommandInterface;
use Spider\Drivers\DriverInterface;
use Spider\Drivers\Response;
use Spider\Graphs\Graph;

/**
 * Facilitates two-way communication with a driver store
 * @package Spider\Test\Unit\Connections
 */
class Connection extends Collection implements ConnectionInterface
{
    /** @var  DriverInterface Instance of the driver */
    protected $driver;

    protected $driverAliases = [
        'orientdb' => 'Spider\Drivers\OrientDB\Driver',
        'gremlin' => 'Spider\Drivers\Gremlin\Driver',
        'neo4j' => 'Spider\Drivers\Neo4J\Driver',
    ];

    /**
     * Constructs a new connection with driver and properties
     *
     * @param DriverInterface|string $driver
     * @param array $configuration Credentials and configuration
     */
    public function __construct($driver, array $configuration = [])
    {
        /* I am sure all this could be refactored */

        // Were we passed all the properties through the first argument?
        $config = (is_array($driver) ? $driver : $configuration);
        $this->initManager($config);

        /* Setup the driver */
        if (is_string($driver)) {
            $this->driverFromString($driver);

        } elseif ($driver instanceof DriverInterface) {
            $this->driver = $driver;

        } elseif (isset($config['driver'])) {
            if (is_string($config['driver'])) {
                $this->driverFromString($config['driver']);
            } elseif ($config['driver'] instanceof DriverInterface)
                $this->driver = $config['driver'];
        }
    }

    /**
     * Connects to the database
     */
    public function open()
    {
        $this->driver->setProperties($this->getAll()); // from given properties
        return $this->driver->open();
    }

    /**
     * Closes database connection
     */
    public function close()
    {
        return $this->driver->close();
    }

    /**
     * Passes through to driver
     *
     * @param $name
     * @param $args
     * @return Graph
     */
    public function __call($name, $args)
    {
        return call_user_func_array([$this->driver, $name], $args);
    }

    /**
     * Returns the class name of the active driver
     * @return string
     */
    public function getDriverName()
    {
        return get_class($this->driver);
    }

    /**
     * Returns the instance of the driver
     * @return DriverInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Updates the driver instance
     *
     * @param DriverInterface $driver
     */
    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param $driver
     */
    protected function driverFromString($driver)
    {
        // As an alias
        if (isset($this->driverAliases[$driver])) {
            $driverClass = $this->driverAliases[$driver];
            $this->driver = new $driverClass();

            // As a classname
        } else {
            $this->driver = new $driver();
        }
    }
}
