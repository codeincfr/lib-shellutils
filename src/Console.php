<?php namespace ShellUtils;

/**
 * A library to send data to the console.
 *
 * @author Joan Fabrégat <joan@joan.pro>
 * @version 2.0
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
	private static $quietMode = false;
	
	/**
	 * Verifies if the yes mode (answers "y" to all questions and do not prompt) is enabled.
	 *
	 * @see Console::enableYesMode()
	 * @see Console::disableYesMode()
	 * @see Console::isYesModeEnabled()
	 * @var bool
	 */
	private static $yesMode = false;
	
	/**
	 * Sends a message to the console
	 *
	 * @param string $message
	 * @param bool|null $newLine
	 */
	public static function send(string $message, bool $newLine = null) {
		if (!self::isQuietModeEnabled()) {
			echo $message;
			if ($newLine) {
				self::sendBR();
			}
		}
	}
	
	/**
	 * Sends a line break.
	 *
	 * @param int $count Number of line breaks.
	 */
	public static function sendBR(int $count = null) {
		if (!$count) {
			self::send("\n");
		}
		else {
			self::send(str_repeat("\n", $count));
		}
	}
	
	/**
	 * Sends a line of "-".
	 */
	public static function sendHR() {
		self::sendLine(str_repeat("-", self::COLS));
	}
	
	/**
	 * Sends a message with a line break.
	 *
	 * @param string $message
	 */
	public static function sendLine(string $message) {
		self::send($message);
		self::sendBR();
	}
	
	/**
	 * Asks a question an retruns the answer or NULL if no answer is provided.
	 *
	 * @param string $question Question
	 * @param string|null $defaultAnwser Default anwser
	 * @return string|null
	 */
	public static function ask(string $question, string $defaultAnwser = null) {
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
	public static function askBool(string $question, bool $defaultAnwser = null) {
		if (!self::isYesModeEnabled()) {
			if (!($resp = readline(trim($question)." (y/n) ").($defaultAnwser !== null ? "[".($defaultAnwser ? "y" : "n")."]" : "")." ")) {
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
		return $defaultAnwser ?? "y";
	}
	
	/**
	 * Sends a green [done] at the end of a processing line.
	 *
	 * @param string $doneMsg
	 */
	public static function sendDoneFlag(string $doneMsg = null) {
		self::setColorGreen();
		self::sendLine(" [".($doneMsg ?? "done")."]");
		self::removeCharacterAttributes();
	}
	
	/**
	 * Sends a red [error] at the end of a processing line.
	 *
	 * @param string $erorrMsg
	 */
	public static function sendErrorFlag(string $erorrMsg = null) {
		self::setColorRed();
		self::sendLine(" [".($erorrMsg ?? "error")."]");
		self::removeCharacterAttributes();
	}
	
	/**
	 * Send an escape code.
	 *
	 * @see Console::ESC_XXX
	 * @param int $escapeCode
	 */
	public static function sendEscape(int $escapeCode) {
		self::send("\e[{$escapeCode}m");
	}
	
	/**
	 * Enables the bold mode mode.
	 */
	public static function setBold() {
		self::sendEscape(self::ESC_BOLD_MODE);
	}
	
	/**
	 * Enables the low intensity mode.
	 */
	public static function setLowIntesity() {
		self::sendEscape(self::ESC_LOW_INTENSITY_MODE);
	}
	
	/**
	 * Enables the blinking mode.
	 */
	public static function setBlinking() {
		self::sendEscape(self::ESC_BLINKING_MODE);
	}
	
	/**
	 * Enables the underline mode.
	 */
	public static function setUnderline() {
		self::sendEscape(self::ESC_UNDERLINE_MODE);
	}
	
	/**
	 * Goes back to normal text.
	 */
	public static function removeCharacterAttributes() {
		self::sendEscape(self::ESC_CHARACTER_ATTRIBUTES_OFF);
	}
	
	/**
	 * Enables the video reverse.
	 */
	public static function enableReverseVideoMode() {
		self::sendEscape(self::ESC_REVERSE_VIDEO_ON);
	}
	
	/**
	 * Sets the text color to green.
	 */
	public static function setColorGreen() {
		self::sendEscape(self::ESC_COLOR_GREEN);
	}
	
	/**
	 * Sets the text color to red.
	 */
	public static function setColorRed() {
		self::sendEscape(self::ESC_COLOR_RED);
	}
	
	/**
	 * Sets the text color to purple.
	 */
	public static function setColorPurple() {
		self::sendEscape(self::ESC_COLOR_PURPLE);
	}
	
	/**
	 * Sets the text color to black.
	 */
	public static function setColorBalck() {
		self::sendEscape(self::ESC_COLOR_BROWN);
	}
	
	/**
	 * Sets the text color to blue.
	 */
	public static function setColorBlue() {
		self::sendEscape(self::ESC_COLOR_BLUE);
	}
	
	/**
	 * Sets the text color to cyan.
	 */
	public static function setColorCyan() {
		self::sendEscape(self::ESC_COLOR_CYAN);
	}
	
	/**
	 * Sets the text color to brown.
	 */
	public static function setColorBrown() {
		self::sendEscape(self::ESC_COLOR_BROWN);
	}
	
	/**
	 * Sets the text color to light gray.
	 */
	public static function setColorLightGray() {
		self::sendEscape(self::ESC_COLOR_LIGHT_GRAY);
	}
	
	/**
	 * Reports an error. The message can be multi-lines.
	 *
	 * @param string $message
	 */
	public static function reportError(string $message) {
		self::setColorRed();
		self::setBold();
		self::sendHR();
		self::sendLine("—› ERROR:");
		self::removeCharacterAttributes();
		self::setColorRed();
		self::renderErrorMessage($message);
		self::setBold();
		self::sendHR();
		self::removeCharacterAttributes();
	}
	
	/**
	 * Reports an axception.
	 *
	 * @param \Exception $exception
	 */
	public static function reportException(\Exception $exception) {
		self::setColorRed();
		self::setBold();
		self::sendHR();
		self::sendLine("—› EXCEPTION ".self::getExceptionInfos($exception).":");
		self::removeCharacterAttributes();
		self::setColorRed();
		self::renderErrorMessage($exception->getMessage());
		self::renderExceptionTrace($exception);
		
		if ($prevException = $exception->getPrevious()) {
			$i = 0;
			do {
				self::sendBR(2);
				self::setBold();
				self::sendLine("—› PREV EXCEPTION #$i ".self::getExceptionInfos($prevException).":");
				self::removeCharacterAttributes();
				self::setColorRed();
				self::renderErrorMessage($prevException->getMessage());
				self::renderExceptionTrace($prevException);
				
				$i++;
			}
			while ($prevException = $prevException->getPrevious());
		}
		
		self::setBold();
		self::sendHR();
		self::removeCharacterAttributes();
	}
	
	/**
	 * Reoturne les données d'une exception.
	 *
	 * @param \Exception $exception
	 * @return string
	 */
	private static function getExceptionInfos(\Exception $exception):string {
		return "[".get_class($exception)."]".($exception->getCode() ? " (code: ".$exception->getCode().")" : ")");
	}
	
	/**
	 * Renders an exception message.
	 *
	 * @param string $message
	 */
	private static function renderErrorMessage(string $message) {
		foreach (explode("\n", wordwrap($message, self::COLS - 3)) as $line) {
			self::sendLine("   $line");
		}
	}
	
	/**
	 * Renders an exception trace.
	 *
	 * @param \Exception $exception
	 */
	private static function renderExceptionTrace(\Exception $exception) {
		self::sendBR();
		self::send("   ");
		self::setUnderline();
		self::sendLine("Trace:");
		self::removeCharacterAttributes();
		self::setColorRed();
		foreach (explode("\n", wordwrap($exception->getTraceAsString(), self::COLS - 3)) as $line) {
			self::sendLine("   $line");
		}
	}
	
	/**
	 * Enables the "quiet mode". Blocks all outputs except for questions.
	 */
	public static function enableQuietMode() {
		self::$quietMode = true;
	}
	
	/**
	 * Disables the "quiet mode".
	 */
	public static function disableQuietMode() {
		self::$quietMode = true;
	}
	
	/**
	 * Verifies if the "quiet mode" is enabled.
	 *
	 * @return bool
	 */
	public static function isQuietModeEnabled():bool {
		return self::$quietMode;
	}
	
	/**
	 * Enables the "yes mode". Anwsers yes to all bool questions.
	 */
	public static function enableYesMode() {
		self::$yesMode = true;
	}
	
	/**
	 * Disables the "yes mode".
	 */
	public static function disableYesMode() {
		self::$yesMode = true;
	}
	
	/**
	 * Verifies if the "yes mode" is enabled.
	 *
	 * @return bool
	 */
	public static function isYesModeEnabled():bool {
		return self::$yesMode;
	}
	
	/* ----------------------- DEPRECATED METHODS ----------------------- */
	
	/**
	 * Alias of ask().
	 *
	 * @deprecated
	 * @see Console::ask()
	 * @param string $message
	 * @return string
	 */
	public static function prompt(string $message):string {
		return self::ask($message);
	}
	
	/**
	 * Sends a colored message.
	 *
	 * @deprecated
	 * @param string $message
	 * @param string $color
	 * @param bool|null $newLine
	 */
	public static function sendColored(string $message, string $color, bool $newLine = null) {
		self::sendEscape($color);
		self::send($message, $newLine);
		self::removeCharacterAttributes();
	}
	
	/**
	 * Alias of sendDoneFlag().
	 *
	 * @deprecated
	 * @see Console::sendDoneFlag()
	 * @param string $doneMsg
	 */
	public static function done(string $doneMsg = null) {
		self::sendDoneFlag($doneMsg);
	}
	
	/**
	 * Alias of sendErrorFlag().
	 *
	 * @deprecated
	 * @see Console::sendErrorFlag()
	 * @param string $erorrMsg
	 */
	public static function error(string $erorrMsg = null) {
		self::sendErrorFlag($erorrMsg);
	}
	
	/**
	 * Changes the text's color.
	 *
	 * @deprecated
	 * @param int $color
	 * @see Console::sendEscape()
	 */
	public static function setColor(int $color) {
		self::sendEscape($color);
	}
}