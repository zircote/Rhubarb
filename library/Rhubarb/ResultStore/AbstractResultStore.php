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
    public function setOptions(array $options)
    {
        $merged = array('uri' => isset($options['uri']) ? $options['uri'] : $this->options['uri']);
        $merge['options'] = array_merge($this->options['options'], (array) @$options['options']);
        $this->options = $merged;
        return $this;
    }
}
