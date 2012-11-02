<?php
namespace Rhubarb\ResultStore;

/**
 * @package     Rhubarb
 * @category    ResultStore
 */
/**
 * @package     Rhubarb
 * @category    ResultStore
 */
abstract class AbstractResultStore implements ResultStoreInterface
{
    protected $resultsExchange = \Rhubarb\Rhubarb::RHUBARB_RESULTS_EXCHANGE_NAME;
    protected $options = array();


    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return AMQP
     */
    abstract public function setOptions(array $options);
}
