<?php namespace ShellUtils;

/**
 * Class Arguments
 *
 * @package ShellUtils
 */
class Arguments {
	/**
	 * RAW script arguments.
	 *
	 * @var array
	 */
	private $rawInput;
	
	/**
	 * Parsed arguments.
	 *
	 * @var array
	 */
	private $arguments = [];
	
	/**
	 * Parsed parameters.
	 *
	 * @var array
	 */
	private $parameters = [];
	
	/**
	 * Arguments constructor.
	 *
	 * @throws ShellUtilsException
	 */
	public function __construct() {
		if (php_sapi_name() != 'cli') {
			throw new ShellUtilsException("The ".get_called_class()." can only be used in CLI mode");
		}
		$this->rawInput = $GLOBALS['argv'];
		$this->parseRawInput();
	}
	
	/**
	 * Parses the arguments
	 */
	private function parseRawInput() {
		$continueNext = false;
		foreach ($this->rawInput as $key => $entry) {
			if ($key > 0) {
				if ($continueNext) {
					$continueNext = false;
					continue;
				}
				
				if (preg_match('/^--([a-z0-9_]+)(=(.+))?$/ui', $entry, $matches)) {
					$this->arguments[$matches[1]] = $matches[3] ?? null;
				}
				else if (preg_match('/^-([a-z0-9_]{1})$/ui', $entry, $matches)) {
					if (isset($this->rawInput[$key + 1]) && !preg_match('/^-.+/ui', $this->rawInput[$key + 1])) {
						$this->arguments[$matches[1]] = $this->rawInput[$key + 1];
						$continueNext = true;
					}
					else {
						$this->arguments[$matches[1]] = null;
					}
				}
				else if (preg_match('/^-([a-z0-9_]+)$/ui', $entry, $matches)) {
					for ($i = 0; $i < strlen($matches[1]); $i++) {
						$this->arguments[$matches[1][$i]] = null;
					}
				}
				else {
					$this->parameters[] = $entry;
				}
			}
		}
	}
	
	/**
	 * Returns the list of parameters.
	 *
	 * @return array
	 */
	public function getParameters():array {
		return $this->parameters;
	}
	
	/**
	 * Count parameters.
	 *
	 * @return int
	 */
	public function countParameters():int {
		return count($this->parameters);
	}
	
	/**
	 * Verifies if the script has been called with a least one parameter.
	 *
	 * @return bool
	 */
	public function hasParameters():bool {
		return !empty($this->parameters);
	}
	
	/**
	 * Returns the parsed arguments in an assoc array with their values.
	 *
	 * @return array
	 */
	public function getArguments():array {
		return $this->arguments;
	}
	
	/**
	 * Count arguments.
	 *
	 * @return int
	 */
	public function countArguments():int {
		return count($this->arguments);
	}
	
	/**
	 * Verifies if the script has been called with a least one argument.
	 *
	 * @return bool
	 */
	public function hasArguments():bool {
		return !empty($this->arguments);
	}

	/**
	 * Returns the raw input.
	 *
	 * @return array
	 */
	public function getRawInput():array {
		return $this->rawInput;
	}
	
	/**
	 * Verifies if the script has an argument.
	 *
	 * @param string $argument
	 * @return bool
	 */
	public function hasArgument(string $argument):bool {
		return array_key_exists($argument, $this->arguments);
	}
	
	/**
	 * Returns the scripts argument's value, NULL if the argument has no value or FALSE of the script
	 * does'nt have this argument.
	 *
	 * @param string $argument
	 * @return string|null|false
	 */
	public function getArgumentValue(string $argument) {
		return $this->hasArgument($argument) ? $this->arguments[$argument] : false;
	}
}