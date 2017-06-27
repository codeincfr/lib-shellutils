<?php namespace ShellUtils;

/**
 * A library to send data to the console.
 *
 * @author Joan Fabrégat <joan@joan.pro>
 * @version 3.0
 * @package ShellUtils
 */
class Console {
	// Escape sequences (source : http://ascii-table.com/ansi-escape-sequences-vt-100.php)
	const ESC_CHARACTER_ATTRIBUTES_OFF = 0;
	const ESC_BOLD_MODE = 1;
	const ESC_LOW_INTENSITY_MODE = 2;
	const ESC_UNDERLINE_MODE = 4;
	const ESC_BLINKING_MODE = 5;
	const ESC_REVERSE_VIDEO_ON = 5;
	const ESC_COLOR_WHITE = self::ESC_CHARACTER_ATTRIBUTES_OFF;
	const ESC_COLOR_GREEN = 32;
	const ESC_COLOR_RED = 31;
	const ESC_COLOR_PURPLE = 35;
	const ESC_COLOR_BLACK = 30;
	const ESC_COLOR_BLUE = 34;
	const ESC_COLOR_CYAN = 36;
	const ESC_COLOR_BROWN = 33;
	const ESC_COLOR_LIGHT_GRAY = 30;
	
	// Colors
	const COLOR_WHITE = self::ESC_CHARACTER_ATTRIBUTES_OFF, COLOR_GREEN = self::ESC_COLOR_GREEN, COLOR_RED = self::ESC_COLOR_RED,
		COLOR_PURPLE = self::ESC_COLOR_PURPLE, COLOR_BLACK = self::ESC_COLOR_BLACK, COLOR_BLUE = self::ESC_COLOR_BLUE,
		COLOR_CYAN = self::ESC_COLOR_CYAN, COLOR_BROWN = self::ESC_COLOR_BROWN,
		COLOR_LIGHT_GRAY = self::ESC_COLOR_LIGHT_GRAY;
	
	// Cols for rendering error messages
	const COLS = 80;
	
	/**
	 * Verifies if the quiet mode is enabled.
	 *
	 * @see Console::enableQuietMode()
	 * @see Console::disableQuietMode()
	 * @see Console::isQuietModeEnabled()
	 * @var bool
	 */
	private $quietMode = false;
	
	/**
	 * Verifies if the yes mode (answers "y" to all questions and do not prompt) is enabled.
	 *
	 * @see Console::enableYesMode()
	 * @see Console::disableYesMode()
	 * @see Console::isYesModeEnabled()
	 * @var bool
	 */
	private $yesMode = false;
	
	/**
	 * Sends a message to the console
	 *
	 * @param string $message
	 * @param bool|null $newLine
	 */
	public function send(string $message, bool $newLine = null) {
		if (!$this->isQuietModeEnabled()) {
			echo $message;
			if ($newLine) {
				$this->sendBR();
			}
		}
	}
	
	/**
	 * Sends a line break.
	 *
	 * @param int $count Number of line breaks.
	 */
	public function sendBR(int $count = null) {
		if (!$count) {
			$this->send("\n");
		}
		else {
			$this->send(str_repeat("\n", $count));
		}
	}
	
	/**
	 * Sends a line of "-".
	 */
	public function sendHR() {
		$this->sendLine(str_repeat("-", self::COLS));
	}
	
	/**
	 * Sends a message with a line break.
	 *
	 * @param string $message
	 */
	public function sendLine(string $message) {
		$this->send($message);
		$this->sendBR();
	}
	
	/**
	 * Asks a question an retruns the answer or NULL if no answer is provided.
	 *
	 * @param string $question Question
	 * @param string|null $defaultAnwser Default anwser
	 * @return string|null
	 */
	public function ask(string $question, string $defaultAnwser = null) {
		if (!($resp = readline(trim($question).($defaultAnwser ? " [".trim($defaultAnwser)."]" : "")." "))) {
			return trim($defaultAnwser);
		}
		return $resp;
	}
	
	/**
	 * Asks a boolean question. Return the anwser or NULL if no anwser is provided or if the anwser can no be understood.
	 *
	 * @param string $question Question
	 * @param bool|null $defaultAnwser Default anwser
	 * @return bool|null
	 */
	public function askBool(string $question, bool $defaultAnwser = null) {
		if (!$this->isYesModeEnabled()) {
			if (!($resp = readline(trim($question)." (y/n) ".($defaultAnwser !== null ? "[".($defaultAnwser ? "y" : "n")."]" : "")." "))) {
				return $defaultAnwser ?? false;
			}
			switch (strtolower($resp)) {
				case "y":
				case "yes":
					return true;
				default:
				case "n":
				case "no":
					return false;
			}
		}
		return $defaultAnwser ?? true;
	}
	
	/**
	 * Sends a green [done] at the end of a processing line.
	 *
	 * @param string $doneMsg
	 */
	public function sendDoneFlag(string $doneMsg = null) {
		$this->setColorGreen();
		$this->sendLine(" [".($doneMsg ?? "done")."]");
		$this->removeCharacterAttributes();
	}
	
	/**
	 * Sends a red [error] at the end of a processing line.
	 *
	 * @param string $erorrMsg
	 */
	public function sendErrorFlag(string $erorrMsg = null) {
		$this->setColorRed();
		$this->sendLine(" [".($erorrMsg ?? "error")."]");
		$this->removeCharacterAttributes();
	}
	
	/**
	 * Send an escape code.
	 *
	 * @see Console::ESC_XXX
	 * @param int $escapeCode
	 */
	public function sendEscape(int $escapeCode) {
		$this->send("\e[{$escapeCode}m");
	}
	
	/**
	 * Enables the bold mode mode.
	 */
	public function enableBold() {
		$this->sendEscape(self::ESC_BOLD_MODE);
	}
	
	/**
	 * Enables the low intensity mode.
	 */
	public function enableLowIntesity() {
		$this->sendEscape(self::ESC_LOW_INTENSITY_MODE);
	}
	
	/**
	 * Enables the blinking mode.
	 */
	public function enableBlinking() {
		$this->sendEscape(self::ESC_BLINKING_MODE);
	}
	
	/**
	 * Enables the underline mode.
	 */
	public function enableUnderline() {
		$this->sendEscape(self::ESC_UNDERLINE_MODE);
	}
	
	/**
	 * Goes back to normal text.
	 */
	public function removeCharacterAttributes() {
		$this->sendEscape(self::ESC_CHARACTER_ATTRIBUTES_OFF);
	}
	
	/**
	 * Enables the video reverse.
	 */
	public function enableReverseVideoMode() {
		$this->sendEscape(self::ESC_REVERSE_VIDEO_ON);
	}
	
	/**
	 * Sets the text color to green.
	 */
	public function setColorGreen() {
		$this->sendEscape(self::ESC_COLOR_GREEN);
	}
	
	/**
	 * Sets the text color to red.
	 */
	public function setColorRed() {
		$this->sendEscape(self::ESC_COLOR_RED);
	}
	
	/**
	 * Sets the text color to purple.
	 */
	public function setColorPurple() {
		$this->sendEscape(self::ESC_COLOR_PURPLE);
	}
	
	/**
	 * Sets the text color to black.
	 */
	public function setColorBalck() {
		$this->sendEscape(self::ESC_COLOR_BROWN);
	}
	
	/**
	 * Sets the text color to blue.
	 */
	public function setColorBlue() {
		$this->sendEscape(self::ESC_COLOR_BLUE);
	}
	
	/**
	 * Sets the text color to cyan.
	 */
	public function setColorCyan() {
		$this->sendEscape(self::ESC_COLOR_CYAN);
	}
	
	/**
	 * Sets the text color to brown.
	 */
	public function setColorBrown() {
		$this->sendEscape(self::ESC_COLOR_BROWN);
	}
	
	/**
	 * Sets the text color to light gray.
	 */
	public function setColorLightGray() {
		$this->sendEscape(self::ESC_COLOR_LIGHT_GRAY);
	}
	
	/**
	 * Reports an error. The message can be multi-lines.
	 *
	 * @param string $message
	 */
	public function reportError(string $message) {
		$this->setColorRed();
		$this->enableBold();
		$this->sendHR();
		$this->sendLine("—› ERROR:");
		$this->removeCharacterAttributes();
		$this->setColorRed();
		$this->renderErrorMessage($message);
		$this->enableBold();
		$this->sendHR();
		$this->removeCharacterAttributes();
	}
	
	/**
	 * Reports an axception.
	 *
	 * @param \Exception $exception
	 */
	public function reportException(\Exception $exception) {
		$this->setColorRed();
		$this->enableBold();
		$this->sendHR();
		$this->sendLine("—› EXCEPTION ".$this->getExceptionInfos($exception).":");
		$this->removeCharacterAttributes();
		$this->setColorRed();
		$this->renderErrorMessage($exception->getMessage());
		$this->renderExceptionTrace($exception);
		
		if ($prevException = $exception->getPrevious()) {
			$i = 0;
			do {
				$this->sendBR(2);
				$this->enableBold();
				$this->sendLine("—› PREV EXCEPTION #$i ".$this->getExceptionInfos($prevException).":");
				$this->removeCharacterAttributes();
				$this->setColorRed();
				$this->renderErrorMessage($prevException->getMessage());
				$this->renderExceptionTrace($prevException);
				
				$i++;
			}
			while ($prevException = $prevException->getPrevious());
		}
		
		$this->enableBold();
		$this->sendHR();
		$this->removeCharacterAttributes();
	}
	
	/**
	 * Reoturne les données d'une exception.
	 *
	 * @param \Exception $exception
	 * @return string
	 */
	private function getExceptionInfos(\Exception $exception):string {
		return "[".get_class($exception)."]".($exception->getCode() ? " (code: ".$exception->getCode().")" : ")");
	}
	
	/**
	 * Renders an exception message.
	 *
	 * @param string $message
	 */
	private function renderErrorMessage(string $message) {
		foreach (explode("\n", wordwrap($message, self::COLS - 3)) as $line) {
			$this->sendLine("   $line");
		}
	}
	
	/**
	 * Renders an exception trace.
	 *
	 * @param \Exception $exception
	 */
	private function renderExceptionTrace(\Exception $exception) {
		$this->sendBR();
		$this->send("   ");
		$this->enableUnderline();
		$this->sendLine("Trace:");
		$this->removeCharacterAttributes();
		$this->setColorRed();
		foreach (explode("\n", wordwrap($exception->getTraceAsString(), self::COLS - 3)) as $line) {
			$this->sendLine("   $line");
		}
	}
	
	/**
	 * Enables the "quiet mode". Blocks all outputs except for questions.
	 */
	public function enableQuietMode() {
		$this->quietMode = true;
	}
	
	/**
	 * Disables the "quiet mode".
	 */
	public function disableQuietMode() {
		$this->quietMode = true;
	}
	
	/**
	 * Verifies if the "quiet mode" is enabled.
	 *
	 * @return bool
	 */
	public function isQuietModeEnabled():bool {
		return $this->quietMode;
	}
	
	/**
	 * Enables the "yes mode". Anwsers yes to all bool questions.
	 */
	public function enableYesMode() {
		$this->yesMode = true;
	}
	
	/**
	 * Disables the "yes mode".
	 */
	public function disableYesMode() {
		$this->yesMode = true;
	}
	
	/**
	 * Verifies if the "yes mode" is enabled.
	 *
	 * @return bool
	 */
	public function isYesModeEnabled():bool {
		return $this->yesMode;
	}
}