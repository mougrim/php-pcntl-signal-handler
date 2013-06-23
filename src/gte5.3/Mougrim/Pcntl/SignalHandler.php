<?php
namespace Mougrim\Pcntl;

/**
 * @package Mougrim\Pcntl
 * @author Mougrim <rinat@mougrim.ru>
 */
class SignalHandler
{
	/**
	 * @var callable[]
	 */
	private $handlers = array();

	/**
	 * Добавление обработчика сигнала
	 *
	 * @param int       $signalNumber   номер сигнала, например SIGTERM
	 * @param callable  $handler        функция-обработчик игнала $signalNumber
	 * @param bool      $isAdd          если true, то заменить текущие обработчики
	 */
	public function addHandler($signalNumber, $handler, $isAdd = true)
	{
		if($isAdd)
			$this->handlers[$signalNumber][] = $handler;
		else
			$this->handlers[$signalNumber] = array($handler);

		if(empty($this->handlers[$signalNumber]) && function_exists('pcntl_signal'))
		{
			pcntl_signal($signalNumber, array($this, 'handleSignal'));
		}
	}

	/**
	 * Начать обработку накопленных сигналов
	 */
	public function dispatch()
	{
		pcntl_signal_dispatch();
	}

	/**
	 * Обработка сигнала
	 *
	 * @param int $signalNumber номер сигнала, например SIGTERM
	 */
	private function handleSignal($signalNumber)
	{
		foreach($this->handlers[$signalNumber] as $handler)
			call_user_func($handler, $signalNumber);
	}
}
