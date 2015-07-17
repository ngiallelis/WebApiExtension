<?php
use Behat\Gherkin\Node\TableNode;

class StatefulWebApiContext extends \Behat\WebApiExtension\Context\WebApiContext {

	protected $dateFormat = 'Y-m-d';

	/**
	 * @Given the following data:
	 *
	 * Usage:
	 * Given the following data:
	 *	| key      | value        |
	 *	| dataKey1 | sampleValue1 |
	 *	| dataKey2 | sampleValue2 |
	 *
	 *  Stores the data for future use
	 */
	public function setPlaceHolders(TableNode $placeholders)
	{
		foreach ($placeholders->getHash() as $row) {
			$this->setPlaceHolder($row['key'], $row['value']);
		}
	}

	/**
	 * @Given that I store response variable :variable as :name
	 *
	 * Usage:
	 * Given that I store response variable "owner.id" as "ownerID"
	 *
	 */
	public function storeResponseVariable($variable, $name)
	{
		$body = json_decode($this->getResponse()->getBody());
		$vars = explode(".", $variable);
		$value = $body;
		foreach ($vars as $var) {
			$value = $value->$var;
		}
		$this->setPlaceHolder($name, $value);
	}

	/**
	 * @Given that I store response header :responseHeader as :headerName
	 *
	 * Usage:
	 * Given that I store response header "Set-Cookie" as "Cookie"
	 *
	 */
	public function addResponseHeaderInPlaceholders($responseHeader, $headerName)
	{
		$header = $this->getResponse()->getHeader($responseHeader);
		$this->setPlaceHolder($headerName, $header);
	}

	/**
	 * @Given that I set stored request header :headerName
	 *
	 * Usage:
	 * Given that I set stored request header "Cookie"
	 *
	 */
	public function setRequestHeaderFromPlaceholder($headerName)
	{
		$header = $this->getPlaceHolder("<$headerName>");
		$this->addHeader($headerName, $header);
	}

	/**
	 * @Given /^that the date "([^"]*)" is "([^"]*)"$/
	 *
	 * Usage:
	 * Given that the date "sampleDate" is "+1 week"
	 *
	 */
	public function addDateAsPlaceholder($dateName, $value)
	{
		$this->setPlaceHolder($dateName, $this->transformDate($value));
	}

	public function getDateFormat() {
		return $this->dateFormat;
	}

	/**
	 * @Given /^that the date format is "([^"]*)"$/
	 *
	 * Usage:
	 * Given that the date format is "D, d M Y H:i:s"
	 *
	 */
	public function setDateFormat($format) {
		$this->dateFormat = $format;
	}

	/**
	 * Sets place holder for replacement.
	 *
	 * you can specify placeholders, which will
	 * be replaced in URL, request or response body.
	 *
	 * The key will be surrounded by "<" and ">" characters
	 *
	 * @param string $key   token name
	 * @param string $value replace value
	 */
	public function setPlaceHolder($key, $value)
	{
		parent::setPlaceHolder("<$key>", $value);
	}

	private function transformDate($dateStringValue)
	{
		$timestamp = strtotime($dateStringValue);
		if(!$timestamp) {
			throw new \InvalidArgumentException(sprintf(
				"Can't resolve '%s' into a valid datetime value",
				$dateStringValue
			));
		}
		return date($this->dateFormat, $timestamp);
	}
}
