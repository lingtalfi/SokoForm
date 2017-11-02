<?php


namespace SokoForm\ValidationRule;


use Bat\HttpTool;
use SokoForm\Control\SokoControlInterface;
use SokoForm\Exception\SokoFormException;
use SokoForm\Form\SokoFormInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Source: http://www.tvaintracommunautaire.eu/
 */
class SokoTvaIntracomValidationRule extends SokoValidationRule
{

    public static $country2Pattern = [
        'DE' => '!^[0-9]{9}$!',
        'AT' => '!^U[0-9]{8}$!',
        'BE' => '!^0[0-9]{9}$!',
        'BG' => '!^[0-9]{9,10}$!',
        'CY' => '!^[a-zA-Z0-9]{9}$!',
        'HR' => '!^[0-9]{11}$!',
        'DK' => '!^[0-9]{8}$!',
        'ES' => '!^[a-zA-Z0-9][0-9]{7}[a-zA-Z0-9]$!',
        'EE' => '!^[0-9]{9}$!',
        'FI' => '!^[0-9]{8}$!',
//        'FR' => '!^[a-zA-Z0-9][a-zA-Z0-9][0-9]{9}$!',
        'FR' => '!^FR[0-9]{9}$!i', // https://www.agecsa.com/expert_comptable_siret.html?
        'GR' => '!^[0-9]{9}$!',
        'HU' => '!^[0-9]{8}$!',
        'IE' => '!^[0-9]{7}[a-zA-Z][a-zA-Z]?$!',
        'IT' => '!^[0-9]{11}$!',
        'LV' => '!^[0-9]{11}$!',
        'LT' => '!^([0-9]{9}|[0-9]{12})$!',
        'LU' => '!^[0-9]{8}$!',
        'MT' => '!^[0-9]{8}$!',
        'NL' => '!^[0-9]{11}B$!',
        'PL' => '!^[0-9]{10}$!',
        'PT' => '!^[0-9]{9}$!',
        'CZ' => '!^[0-9]{8,10}$!',
        'RO' => '!^[0-9]{2,10}$!',
        'GB' => '!^[0-9]{9}$!',
        'SK' => '!^[0-9]{10}$!',
        'SI' => '!^[0-9]{8}$!',
        'SE' => '!^[0-9]{10}01$!',
    ];

    private $useWebservice;

    public function __construct()
    {
        parent::__construct();
        $this->useWebservice = true;


        $this->setErrorMessage("The TVA intracom number isn't valid for the selected country ({countryLabel})");

        $this->setValidationFunction(function ($value, array &$preferences, &$error = null, SokoFormInterface $form, SokoControlInterface $control) {

            if (true === $this->checkSubmitted($value, $error)) {

                $countryValue = $preferences['countryValue'];
                if (false === $this->match($value, $countryValue)) {
                    $error = $this->getErrorMessage();
                    return false;
                }

            } else {
                return false;
            }
            return true;
        });
    }


    /**
     * @param $value , iso-3166-2 (2 letters code uppercase):
     * @return $this
     * @throws \Exception
     */
    public function setCountry($value, $label)
    {
        if (!array_key_exists($value, self::$country2Pattern)) {
            throw new SokoFormException("This country code is not valid (i.e. not part of the UE): $value");
        }
        $this->preferences["countryValue"] = $value;
        $this->preferences["countryLabel"] = $label;
        return $this;
    }

    public function setUseWebservice($bool)
    {
        $this->useWebservice = $bool;
        return $this;
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    public function match($tvaNumber, $country)
    {
        if (!array_key_exists($country, self::$country2Pattern)) {
            return false;
        }
        $pattern = self::$country2Pattern[$country];
        if (preg_match($pattern, $tvaNumber)) {

            if ('FR' === $country) {

                $cle = (int)substr($tvaNumber, 2, 2);
                $siren = substr($tvaNumber, 4);
                $computedKey = $this->computeKey($siren);
                if ($cle !== $computedKey) {
                    return false;
                }
            }

            if (true === $this->useWebservice) {

                $intracomNumber = $tvaNumber;
                if ('FR' === $intracomNumber) {
                    $intracomNumber = substr($intracomNumber, 2);
                }

                if (false === $this->checkTvaIntracomUsingVies($intracomNumber, $country)) {
                    return false;
                }
            }

            return true;
        }
        return false;
    }


    private function computeKey($siren)
    {
        /**
         * Clé TVA = [12 + 3 × (SIREN modulo 97)] modulo 97
         * Clé TVA = [12 + 3 × (452793177 modulo 97)] modulo 97
         * Clé TVA = [12 + 3 × (87)] modulo 97
         * Clé TVA = [273] modulo 97
         * Clé TVA = 79
         */
        return (12 + 3 * ((int)$siren % 97)) % 97;
    }


    /**
     * @param $intracomNumber
     * @param $country , iso 3166 (2 letters code uppercase)
     * @return bool
     *
     * Example:
     * in France, renault's tva number is:
     * - FR63441639465
     *
     * The intracomNumber is: 63441639465
     * and the country is: FR
     *
     * Every country has its own format.
     *
     *
     *
     */
    private function checkTvaIntracomUsingVies($intracomNumber, $country)
    {
        $uri = "http://ec.europa.eu/taxation_customs/vies/vatResponse.html";
        $html = HttpTool::post($uri, [
            'memberStateCode' => $country,
            'number' => $intracomNumber,
            'traderName' => '',
            'traderStreet' => '',
            'traderPostalCode' => '',
            'traderCity' => '',
            'requesterMemberStateCode' => 'FR',
            'requesterNumber' => "63441639465", // renault
            'action' => 'check',
            'check' => 'Vérifier',
        ]);


        $crawler = new Crawler($html);

        $table = $crawler->filter("#vatResponseFormTable");
        if ('Yes' === substr($table->text(), 0, 3)) {
            return true;
        }
        return false;
    }
}