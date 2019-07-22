## Sensor

#### NOT PRODUCTION READY.

![R-Xray](https://pbs.twimg.com/media/EAA1ql6XkAAcge_?format=png&name=medium)

Observability for AWS Lambda powered by X-Ray

#### Usage on Laravel

On your Service Provider, define an XRayClient instance.

    $this->app->bind(XRayClient::class, function () {
        return new \Aws\XRay\XRayClient([
            'version' => '2016-04-12',
            'region' => 'eu-west-1',
            'credentials' => [
                'key' => 'your-key',
                'secret' => 'your-secret',
            ]
        ]);
    });
    
Then extend the class you would like to Tap and attach a sensor into it.

    $this->app->extend(LoginController::class, function (LoginController $service) {
        $sensor = $this->app->make(\Deleu\Sensor\Sensor::class);

        $tap = new \Deleu\Sensor\Tap($service, $sensor);

        return $tap->listen(['showLoginForm']);
    });

#### Limitations

It is not possible to tap a `final` class. This is a limitation on [Ocramius/ProxyManager](https://github.com/Ocramius/ProxyManager).

#### Ideals

- Provide an easy profiling tool to help diagnose slow Lambda function execution.
- Hook into the code from outside so that no code change is required inside your core classes.
- This project is not meant to replace a complete profiling tool, 
but rather simply help get confirmation on educated guesses 
on what could be slow on your Lambda Function

#### Help Wanted

I've been spoiled by the amazing tools that Laravel provides for too long.
I don't have any knowledge around Symfony container or how to offer a 
flexible configuration without using `illumiate/config` or static
attributes. If you like the idea and would like to help me bring this
to other communities other than Laravel, I would be very happy. 

