<?php
use Behat\Gherkin\Node\TableNode;

class StatefulWebApiContext extends \Behat\WebApiExtension\Context\WebApiContext {

	protected $dateFormat = 'Y-m-d';

	/**
	 * @Transform table:dateType,date
	 */
	public function castRelativeToAbsoluteDate(TableNode $datesTable) {
		$dates = [];
		foreach ($datesTable->getHash() as $dateHash) {
			$dates[$dateHash['dateType']] = $this->transformDate($dateHash['date']);
		}
		return $dates;
	}

	/**
	 * @Given the following dates:
	 *
	 * Usage:
	 * Given the following dates:
	 *	| dateType | date    |
	 *	| obDate   | +1 day  |
	 *	| ibDate   | +1 week |
	 *
	 * The placeholders <obDate>, <ibDate> will be created
	 */
	public function setPlaceHolders(array $placeholders)
	{
		foreach ($placeholders as $key => $value) {
			$this->setPlaceHolder($key, $value);
		}
	}

	/**
	 * @Given that I store response variable :variable as :name
	 *
	 * Usage:
	 * Given that I store response variable "results.searchID" as "searchID"
	 *
	 * The placeholder <searchID> will be created
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
	 * Given that the date "ibDate" is "+1 week"
	 *
	 */
	public function addDateAsPlaceholder($dateName, $value)
	{
		$this->setPlaceHolder($dateName, $this->transformDate($value));
	}

	public function getDateFormat() {
		return $this->dateFormat;
	}

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
