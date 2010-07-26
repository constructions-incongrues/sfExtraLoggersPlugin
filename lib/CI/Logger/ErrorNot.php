<?php
/**
 * Logger that sends messages to an ErrorNot server.
 *
 * @see http://github.com/errornot/ErrorNot
 */
class CI_Logger_ErrorNot extends sfLogger
{
    /**
     * Configured ErrorNot client
     *
     * @var Services_ErrorNot
     * @see http://github.com/francois2metz/php-errornot
     */
    private $client;

    /**
     * Checks that all mandatory parameters are set and instanciates ErrorNot client.
     *
     * (non-PHPdoc)
     * @see log/sfLogger::initialize()
     */
    public function initialize(sfEventDispatcher $dispatcher, array $options)
    {
        // Sanity checks
        $mandatory_options = array('api_key', 'server_url');
        foreach ($mandatory_options as $option_name)
        {
            if (!isset($options[$option_name]))
            {
                throw new sfConfigurationException(sprintf('You must provide a "%s" parameter for this logger', $option_name));
            }
        }

        // Force log level to error as we only take care of those
        $options['level'] = 'err';

        // Instanciate and configure ErrorNot client
        include(dirname(__FILE__).'/../../vendor/php-errornot/errornot.php');
        $this->client = new Services_ErrorNot($options['server_url'], $options['api_key']);

        // Call common initialization code
        return parent::initialize($dispatcher, $options);
    }

    /**
     * Sends notification to ErrorNot server.
     *
     * @param Exception $exception
     *
     * @return null
     *
     * TODO : inject more context data before sending to server
     */
    public function logException($message, $priority)
    {
        $this->client->notifyException($exception);
    }

    /**
     * Listens to "application.log" event.
     *
     * This method is overriden in order to make sure we catch full exception data
     * for sending to ErrorNot server.
     *
     * (non-PHPdoc)
     * @see log/sfLogger::listenToLogEvent()
     */
    public function listenToLogEvent(sfEvent $event)
    {
        // We only want to take care of exceptions
        $subject = $event->getSubject();
        if (is_a($subject, 'Exception'))
        {
            $this->logException($subject);
        }
    }

    /**
     * Unused abstract method implementation.
     *
     * TODO : take care of error messages ?
     *
     * (non-PHPdoc)
     * @see log/sfLogger::doLog()
     */
    public function doLog($message, $priority)
    {

    }
}