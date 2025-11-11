<?php

namespace App\Services;

use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Configuration;
use XeroAPI\XeroPHP\ApiClient;
use XeroAPI\XeroPHP\Models\Accounting\Contact;

class XeroService
{
    protected $apiInstance;
    protected $xeroTenantId;

    public function __construct()
    {
        $config = Configuration::getDefaultConfiguration()->setAccessToken(env('XERO_ACCESS_TOKEN'));
        $this->apiInstance = new AccountingApi(
            new ApiClient($config)
        );
        $this->xeroTenantId = env('XERO_TENANT_ID');
    }

    public function createContact($data)
    {
        $contact = new Contact([
            'name' => $data['business_name'],
            'email_address' => $data['email'],
            'phones' => [
                ['phone_type' => 'DEFAULT', 'phone_number' => $data['mobile_number']]
            ],
            'addresses' => [
                ['address_type' => 'STREET', 'address_line1' => $data['address_line1']]
            ],
        ]);

        $response = $this->apiInstance->createContacts($this->xeroTenantId, ['Contacts' => [$contact]]);
        return $response->getContacts()[0]->getContactID();
    }
}