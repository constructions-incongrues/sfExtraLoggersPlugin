<?php
/**
 * Logger that sends messages to an ErrorNot server.
 *
 * It must be declared in project's factories.yml :
 * <code>
 * logger:
 *   class:   sfAggregateLogger
 *   param:
 *     level:   info
 *     loggers:
 *       errornot:
 *         class: CI_Logger_ErrorNot
 *         param:
 *           api_key:    "project_api_key"
 *           server_url: "http://errornot.example.com"
 * </code>
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

        // Request HTTP_Request2 php-errornot dependency
        include('HTTP/Request2.php');

        // Instanciate and configure ErrorNot client
        include(dirname(__FILE__).'/../../vendor/php-errornot/errornot.php');
        $this->client = new Services_ErrorNot($options['server_url'], $options['api_key']);

        // Listen for exceptions
        $dispatcher->connect('application.log', array($this, 'listenToLogException'));

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
    public function logException($exception)
    {
        $this->client->notifyException($exception);
    }

    /**
     * Listens to "application.log" event.
     *
     * Make sure we catch full exception data
     * for sending to ErrorNot server.
     *
     * (non-PHPdoc)
     * @see log/sfLogger::listenToLogEvent()
     */
    public function listenToLogException(sfEvent $event)
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