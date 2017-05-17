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
	 * Returns a parameter's value using its number or FALSE if the script does'nt have such a parameter.
	 *
	 * @param int $number
	 * @return string|bool
	 */
	public function getParameterValue(int $number) {
		if (array_key_exists($number, $this->parameters)) {
			return $this->parameters[$number];
		}
		return false;
	}
	
	/**
	 * Returns a parameter's number in the scripts parameters or FALSE if the script does'nt have such a parameter.
	 *
	 * @param string $parameter
	 * @return int|false
	 */
	public function getParameterNumber(string $parameter) {
		return array_search($parameter, $this->parameters);
	}
	
	/**
	 * Verifies if the script has a parameter.
	 *
	 * @param string $parameter
	 * @return bool
	 */
	public function hasParameter(string $parameter):bool {
		return in_array($parameter, $this->parameters);
	}
	
	/**
	 * Verifies if the script has an argument.
	 *
	 * @param string|array $argument The name (string) or the names (array) of the argument
	 * @return bool
	 */
	public function hasArgument($argument):bool {
		if (is_array($argument)) {
			foreach ($argument as $value) {
				if ($this->hasArgument($value)) {
					return true;
				}
			}
			return false;
		}
		else {
			return array_key_exists($argument, $this->arguments);
		}
	}
	
	/**
	 * Returns the scripts argument's value, NULL if the argument has no value or FALSE of the script
	 * does'nt have this argument.
	 *
	 * @param string|array $argument The name (string) or the names (array) of the argument
	 * @return string|null|false
	 */
	public function getArgumentValue($argument) {
		if (is_array($argument)) {
			foreach ($argument as $value) {
				if (array_key_exists($value, $this->arguments)) {
					return $this->arguments[$value];
				}
			}
		}
		elseif ($this->hasArgument($argument)) {
			return $this->arguments[$argument];
		}
		return false;
	}
}