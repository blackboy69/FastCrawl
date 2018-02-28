<?php

// class to abstract out timing.
// byron whitlock 2 25 2012
class Timer
{
	private $times = array();

	private $key;
	
	public function start()
	{
		self::checkpoint('start');
	}

	public function end()
	{
		unset($this->times['start']);
		unset($this->times['end']);
	}
	
	public function elapsed($key1='start',$key2='end')
	{
		if (isset($this->times[$key][$key2]) && isset($this->times[$key][$key2]))
		{
			$time1 = $this->times[$key1];
			$time2 = $this->times[$key2];		

			return ($time2 - $time1);
		}
	}
	public function checkpoint($checkpoint)
	{
		$start = isset($this->times['start']) ? $this->times['start'] :0;
		$this->times[$checkpoint] = microtime(true) - $start;
	}
	
	public function out()
	{
		$this->end();
		echo "\n";
		foreach($this->times as $name => $time)
		{

			$formated_time = number_format($time*1000,1);

			echo  str_pad($name, 30,"."). str_pad($formated_time,10,".",STR_PAD_LEFT)." ms \n" ;
		}
	}


}
