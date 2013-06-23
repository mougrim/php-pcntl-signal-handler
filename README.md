php-pcntl-signal-handler
====================
Библиотка позволяет назначать несколько обработчиков одного и тогоже сигнала. Особенно это актуально для сигнала SIGHUP.
Для версии php меньше чем 5.3
--------------------
Помимо описанного выше класс Mougrim_Pcntl_SignalHandler эмулирует <a href="http://php.net/pcntl_signal_dispatch" target="_blank">pcntl_signal_dispatch()</a>, которая досупна только с версии php 5.3.0
Входной файл signalExmpleRun.php:
```php
<?php
// в начале подключаем SignalHandler, что бы был вызван declare(ticks = 1);
require_once dirname(__FILE__) . "/src/lt5.3/Mougrim/Pcntl/SignalHandler.php";
require_once dirname(__FILE__) . "/SignalExample.php";;
$signalHandler = new Mougrim_Pcntl_SignalHandler();
$signalExample = new SignalExample($signalHandler);
$signalExample->run();
```

Файл SignalExample.php:
```php
<?php
class SignalExample
{
	private $signalHandler;

	public function __construct(Mougrim_Pcntl_SignalHandler $signalHandler)
	{
		$this->signalHandler = $signalHandler;
	}

	public function run()
	{
		// добавляем обработчик сигнала SIGTERM
		$this->signalHandler->addHandler(SIGTERM, array($this, 'terminate'));

		while(true)
		{
			$this->signalHandler->dispatch();

			// итерация цикла
		}
	}

	public function terminate()
	{
		// послать SIGTERM детям
		// ...

		exit(0);
	}
}
```

Для версии php больше либо равно 5.3
--------------------
Входной файл signalExmpleRun.php:
```php
<?php
require_once dirname(__FILE__) . "/SignalExample.php";;
// Подключить SignalHandler можно в любом месте программы, например путем автозагрузки
require_once dirname(__FILE__) . "/src/gte5.3/Mougrim/Pcntl/SignalHandler.php";
$signalHandler = new \Mougrim\Pcntl\SignalHandler();
$signalExample = new SignalExample($signalHandler);
$signalExample->run();
```

Файл SignalExample.php:
```php
<?php
class SignalExample
{
	private $signalHandler;

	public function __construct(\Mougrim\Pcntl\SignalHandler $signalHandler)
	{
		$this->signalHandler = $signalHandler;
	}

	public function run()
	{
		// добавляем обработчик сигнала SIGTERM
		$this->signalHandler->addHandler(SIGTERM, array($this, 'terminate'));

		while(true)
		{
			$this->signalHandler->dispatch();

			// итерация цикла
		}
	}

	public function terminate()
	{
		// послать SIGTERM детям
		// ...

		exit(0);
	}
}
```
