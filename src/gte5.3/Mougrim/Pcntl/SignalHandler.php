<?php
namespace Mougrim\Pcntl;

/**
 * @package Mougrim\Pcntl
 * @author Mougrim <rinat@mougrim.ru>
 */
class SignalHandler
{
	private $handlersStack = array();
	private $toDispatchStack = array();
	private $stackLevels = array();

	/**
	 * Добавление обработчика сигнала
	 *
	 * @param int       $signalNumber   номер сигнала, например SIGTERM
	 * @param callable  $handler        функция-обработчик игнала $signalNumber
	 * @param bool      $isAdd          если true, то заменить текущие обработчики
	 */
	public function addHandler($signalNumber, $handler, $isAdd = true)
	{
		$isHandlerNotAttached = !array_key_exists($signalNumber, $this->handlersStack);

		$signalStackLevel = $this->getStackLevel($signalNumber);

		if($isAdd)
			$this->handlersStack[$signalNumber][$signalStackLevel][] = $handler;
		else
			$this->handlersStack[$signalNumber][$signalStackLevel] = array($handler);

		if($isHandlerNotAttached && function_exists('pcntl_signal'))
		{
			$this->toDispatchStack[$signalNumber][$signalStackLevel] = false;
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
		if(array_key_exists($signalNumber, $this->handlersStack))
			$this->handlersStack[$signalNumber][$this->getStackLevel($signalNumber)] = array();
	}

	/**
	 * Добавить новый уровен в стек обработчиков сигнала $signalNumber
	 * Может использоваться для обработки SIGTERM в неспольких местах в приложени
	 *
	 * @param int $signalNumber
	 */
	public function nextStackLevel($signalNumber)
	{
		$nextLevel = $this->getStackLevel($signalNumber) + 1;
		$this->setStackLevel($signalNumber, $nextLevel);
		$this->toDispatchStack[$signalNumber][$nextLevel] = false;
	}

	/**
	 * Уничтожить текущий уровень стека обработчиков сигнала $signalNumber и вернуться к предыдущему уровню
	 *
	 * @param int $signalNumber
	 */
	public function previousStackLevel($signalNumber)
	{
		$currentLevel = $this->getStackLevel($signalNumber);
		unset($this->toDispatchStack[$signalNumber][$currentLevel]);
		unset($this->handlersStack[$signalNumber][$currentLevel]);
		$this->setStackLevel($signalNumber, $currentLevel - 1);
	}

	/**
	 * Обработать накопленные сигналы
	 */
	public function dispatch()
	{
		pcntl_signal_dispatch();
		foreach($this->toDispatchStack as $signalNumber => $levels)
		{
			$isNeedDispatch = $levels[$this->getStackLevel($signalNumber)];
			if(!$isNeedDispatch)
				continue;
			$this->toDispatchStack[$signalNumber][$this->getStackLevel($signalNumber)] = false;
			if(array_key_exists($this->getStackLevel($signalNumber), $this->handlersStack[$signalNumber]))
				foreach($this->handlersStack[$signalNumber][$this->getStackLevel($signalNumber)] as $handler)
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
		foreach($this->toDispatchStack[$signalNumber] as &$toDispatch)
			$toDispatch = true;
		unset($toDispatch);
	}

	private function getStackLevel($signalNumber)
	{
		if(array_key_exists($signalNumber, $this->stackLevels))
			return $this->stackLevels[$signalNumber];
		else
			return 0;
	}

	private function setStackLevel($signalNumber, $level)
	{
		if($level < 0)
			throw new SignalHandlerException('Level of stack can`t less than zero for signal #' . $signalNumber);
		$this->stackLevels[$signalNumber] = $level;
	}
}

class SignalHandlerException extends \Exception {}
