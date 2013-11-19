<?php
declare(ticks = 1);

/**
 * @author Mougrim <rinat@mougrim.ru>
 */
class Mougrim_Pcntl_SignalHandler
{
	/**
	 * @var callable[]
	 */
	private $handlers = array();
	private $toDispatch = array();

	/**
	 * Добавление обработчика сигнала
	 *
	 * @param int       $signalNumber   номер сигнала, например SIGTERM
	 * @param callable  $handler        функция-обработчик игнала $signalNumber
	 * @param bool      $isAdd          если true, то заменить текущие обработчики
	 */
	public function addHandler($signalNumber, $handler, $isAdd = true)
	{
		$isHandlerNotAttached = empty($this->handlers[$signalNumber]);
		if($isAdd)
			$this->handlers[$signalNumber][] = $handler;
		else
			$this->handlers[$signalNumber] = array($handler);

		if($isHandlerNotAttached && function_exists('pcntl_signal'))
		{
			$this->toDispatch[$signalNumber] = false;
			pcntl_signal($signalNumber, array($this, 'handleSignal'));
		}
	}

	/**
	 * Очитска обработчиков для сигнала $signalNumber
	 *
	 * @param int $signalNumber
	 */
	public function clearHandlers($signalNumber)
	{
		$this->handlers[$signalNumber] = array();
	}

	/**
	 * Обработать накопленные сигналы
	 */
	public function dispatch()
	{
		foreach($this->toDispatch as $signalNumber => $isNeedDispatch)
		{
			if(!$isNeedDispatch)
				continue;
			$this->toDispatch[$signalNumber] = false;
			foreach($this->handlers[$signalNumber] as $handler)
				call_user_func($handler, $signalNumber);
		}
	}

	/**
	 * Поставнока обработки сигнала в очередь
	 *
	 * @param int $signalNumber номер сигнала, например SIGTERM
	 */
	public function handleSignal($signalNumber)
	{
		$this->toDispatch[$signalNumber] = true;
	}
}
