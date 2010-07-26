# sfExtraLoggersPlugin

Additional symfony loggers.

## CI_Logger_ErrorNot
ErrorNot (github.com/errornot/ErrorNot) logger. Configure as such in project's factories.yml :
 
    logger:
      class:   sfAggregateLogger
      param:
        level:   info
        loggers:
          errornot:
            class: CI_Logger_ErrorNot
            param:
              api_key: "project_api_key"
              url:     "http://errornot.example.com"