php-pcntl-signal-handler
====================
Библиотка позволяет назначать несколько обработчиков одного и тогоже сигнала. Особенно это актуально для сигнала SIGHUP.
Для версии php меньше чем 5.3
--------------------
Помимо описанного выше класс Mougrim_Pcntl_SignalHandler эмулирует <a href="http://php.net/pcntl_signal_dispatch" target="_blank">pcntl_signal_dispatch()</a>, которая досупна только с версии php 5.3.0
Входной файл signalExampleRun.php:
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
		// добавляем обработчик сигнала SIGINT
		$this->signalHandler->addHandler(SIGINT, array($this, 'terminate'));

		while(true)
		{
			$this->signalHandler->dispatch();

			// итерация цикла
			echo "итерация цикла\n";
			usleep(300000);
		}
	}

	public function terminate()
	{
		// послать SIGTERM детям
		// ...
		echo "terminate\n";

		exit(0);
	}
}
```

Для версии php больше либо равно 5.3
--------------------
Входной файл signalExampleRun53.php:
```php
<?php
// в начале подключаем SignalHandler, что бы был вызван declare(ticks = 1);
require_once dirname(__FILE__) . "/src/gte5.3/Mougrim/Pcntl/SignalHandler.php";
require_once dirname(__FILE__) . "/SignalExample53.php";;
$signalHandler = new \Mougrim\Pcntl\SignalHandler();
$signalExample = new SignalExample53($signalHandler);
$signalExample->run();
```

Файл SignalExample53.php:
```php
<?php
class SignalExample53
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
		// добавляем обработчик сигнала SIGINT
		$this->signalHandler->addHandler(SIGINT, array($this, 'terminate'));

		while(true)
		{
			$this->signalHandler->dispatch();

			// итерация цикла
			echo "итерация цикла\n";
			usleep(300000);
		}
	}

	public function terminate()
	{
		// послать SIGTERM детям
		// ...
		echo "terminate\n";

		exit(0);
	}
}
```
