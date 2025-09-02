<?php

namespace App\Controllers;
helper('form');
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\CountryModel;
use App\Models\TimeZoneModel;
use App\Models\SettingsModel;
/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['sidebar'];   
    protected $settings = array(); // Add this property to hold the settings
    protected $deliverySettings = array(); // Add this property to hold the settings
    protected $customerSettings = array(); // Add this property to hold the settings
    protected $country; 
    protected $timeZone; 

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
        $this->loadSettings();
        $this->loadCountrySetting();
        $this->loadTimeZoneSetting();
        $this->loadSettings();
        $this->loadSettingsForDeliveryBoy();
        $this->loadSettingsForCustomer();
    }

    protected function loadCountrySetting()
    {
        $countryModel = new CountryModel();
        $this->country = $countryModel->where('is_active', 1)->first();
    }

    protected function loadTimeZoneSetting()
    {
        $timeZoneModel = new TimeZoneModel();
        $this->timeZone = $timeZoneModel->where('is_active', 1)->first();
    }

    protected function loadSettings()
    {
        $settingsModel = new SettingsModel();
        $settings = $settingsModel->findAll();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key']] = $setting['value'];
        }
        $this->settings = $result;
    }

    protected function loadSettingsForDeliveryBoy()
    {
        $settingsModel = new SettingsModel();
        $settings = $settingsModel->where('for_delivery_boy', 1)->findAll();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key']] = $setting['value'];
        }
        $this->deliverySettings = $result;
    }

    protected function loadSettingsForCustomer()
    {
        $settingsModel = new SettingsModel();
        $settings = $settingsModel->where('for_customer_app', 1)->findAll();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key']] = $setting['value'];
        }
        $this->customerSettings = $result;
    }
}
