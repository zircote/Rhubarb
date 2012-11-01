<?php
namespace Rhubarb\Broker;

/**
 * @package     Rhubarb
 * @category    Broker
 */
/**
 * @package     Rhubarb
 * @category    Broker
 */
abstract class AbstractBroker implements BrokerInterface
{
    protected $exchange = \Rhubarb\Rhubarb::RHUBARB_DEFAULT_EXCHANGE_NAME;
    protected $options = array();
    protected $message
        = array(
            'body'             => null,
            'headers'          => array(),
            'content-type'     => \Rhubarb\Rhubarb::RHUBARB_CONTENT_TYPE,
            'properties'       => array(
                'exclusive'         => null,
                'name'              => \Rhubarb\Rhubarb::RHUBARB_DEFAULT_TASK_QUEUE,
                'body_encoding'     => \Rhubarb\Rhubarb::RHUBARB_DEFAULT_BODY_ENCODING,
                'delivery_info'     => array(
                    'priority'    => 0,
                    'routing_key' => \Rhubarb\Rhubarb::RHUBARB_TASK_ROUTING_KEY,
                    'exchange'    => \Rhubarb\Rhubarb::RHUBARB_DEFAULT_EXCHANGE_NAME
                ),
                'durable'           => true,
                'delivery_mode'     => 2,
                'no_ack'            => null,
                'alias'             => null,
                'queue_arguments'   => null,
                'binding_arguments' => null,
                'delivery_tag'      => null,
                'auto_delete'       => null,
            ),
            'content-encoding' => \Rhubarb\Rhubarb::RHUBARB_DEFAULT_CONTENT_ENCODING
        );

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
